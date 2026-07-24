<?php
// ============================================
// CONFIGURACIÓN PARA RENDER / SUPABASE / POSTGRESQL
// ============================================

$dbUrl = getenv('DATABASE_URL') ?: 'postgresql://postgres:localhost@localhost:5432/postgres';
$jwtSecret = getenv('JWT_SECRET') ?: 'default_secret_change_me';
$supabaseUrl = getenv('SUPABASE_URL') ?: '';
$supabaseKey = getenv('SUPABASE_KEY') ?: '';

// ============================================
// SESIONES (PostgreSQL compatible con PaaS)
// ============================================
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

// ============================================
// TIEMPO DE INACTIVIDAD (30 min)
// ============================================
$tiempo_inactividad = 1800;

if (isset($_SESSION['ultima_actividad']) && (time() - $_SESSION['ultima_actividad'] > $tiempo_inactividad)) {
    session_unset();
    session_destroy();
    header('Location: index.php?error=sesion_expirada');
    exit();
}
$_SESSION['ultima_actividad'] = time();

// ============================================
// CONEXIÓN POSTGRESQL (Supabase)
// ============================================
$parsedDb = parse_url($dbUrl);
$host = $parsedDb['host'] ?? 'localhost';
$port = $parsedDb['port'] ?? '5432';
$user = $parsedDb['user'] ?? 'postgres';
$pass = $parsedDb['pass'] ?? '';
$dbname = ltrim($parsedDb['path'] ?? '/postgres', '/');

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// ============================================
// SUPABASE STORAGE (para archivos)
// ============================================

function supabaseUpload($filePath, $fileName, $mimeType = 'application/octet-stream') {
    global $supabaseUrl, $supabaseKey;
    $bucket = 'entregas';
    $url = "$supabaseUrl/storage/v1/object/$bucket/$fileName";
    
    $data = file_get_contents($filePath);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $supabaseKey",
        "Content-Type: $mimeType",
        "x-upsert: true"
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 || $httpCode == 201) {
        return "$supabaseUrl/storage/v1/object/public/$bucket/$fileName";
    }
    return false;
}

function supabaseDelete($fileName) {
    global $supabaseUrl, $supabaseKey;
    $bucket = 'entregas';
    $url = "$supabaseUrl/storage/v1/object/$bucket/$fileName";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $supabaseKey"
    ]);
    
    curl_exec($ch);
    curl_close($ch);
}

// ============================================
// FUNCIONES DE SEGURIDAD
// ============================================
function sanitizar($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function escapar($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

function generarTokenCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verificarTokenCSRF($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
}

function verificarPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generarTokenRecuperacion() {
    return bin2hex(random_bytes(32));
}

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validarDominioEmail($email) {
    $dominio = substr(strrchr($email, "@"), 1);
    return checkdnsrr($dominio, 'MX');
}

function registrarIntentoFallido($usuario) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    try {
        $stmt = $pdo->prepare("INSERT INTO intentos_login (ip, usuario, fecha) VALUES (?, ?, NOW())");
        $stmt->execute([$ip, $usuario]);
    } catch (Exception $e) {
        // Silenciar si tabla no existe
    }
}

function logActividad($accion, $detalle = '') {
    global $pdo;
    $user_id = $_SESSION['user_id'] ?? '0';
    $usuario = $_SESSION['user_nombre'] ?? 'Anonimo';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $fecha = date('Y-m-d H:i:s');
    
    try {
        $stmt = $pdo->prepare("INSERT INTO logs_actividad (user_id, usuario, ip, accion, detalle, fecha) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $usuario, $ip, $accion, $detalle, $fecha]);
    } catch (Exception $e) {
        // Silenciar si tabla no existe
    }
}

function verificarSesion() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../index.php');
        exit();
    }
    
    $ip_actual = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (isset($_SESSION['ip']) && $_SESSION['ip'] !== $ip_actual) {
        session_destroy();
        header('Location: ../index.php?error=sesion_invalida');
        exit();
    }
    
    $ua_actual = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $ua_actual) {
        session_destroy();
        header('Location: ../index.php?error=sesion_invalida');
        exit();
    }
}

function mostrarSeguro($texto) {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
?>
