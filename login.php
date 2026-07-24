<?php
require_once 'config.php';

// ============================================
// VERIFICAR CSRF - SOLO SI HAY POST
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
        // Regenerar token y redirigir con error
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: index.php?error=token_invalido');
        exit();
    }

    // ============================================
    // LÍMITE DE INTENTOS (5 en 15 min)
    // ============================================
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // PostgreSQL: NOW() - INTERVAL '15 minutes' (en vez de DATE_SUB de MySQL)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM intentos_login WHERE ip = ? AND fecha > NOW() - INTERVAL '15 minutes'");
    $stmt->execute([$ip]);
    $intentos = $stmt->fetchColumn();

    if ($intentos >= 5) {
        header('Location: index.php?error=demasiados_intentos');
        exit();
    }

    // ============================================
    // PROCESAR LOGIN
    // ============================================
    $usuario = sanitizar($_POST['usuario']);
    $contrasena = $_POST['contrasena'];

    $stmt = $pdo->prepare("SELECT u.*, r.nombre as rol_nombre 
                           FROM usuarios u 
                           JOIN roles r ON u.rol_id = r.id 
                           WHERE u.usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    if ($user && verificarPassword($contrasena, $user['contrasena'])) {
        // Login exitoso
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nombre'] = $user['nombre'];
        $_SESSION['user_rol'] = $user['rol_nombre'];
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Regenerar token

        $stmt = $pdo->prepare("DELETE FROM intentos_login WHERE ip = ?");
        $stmt->execute([$ip]);

        if ($user['rol_nombre'] == 'estudiante') {
            header('Location: dashboard_estudiante.php');
        } else {
            header('Location: dashboard_profesor.php');
        }
        exit();
    } else {
        // Login fallido
        $stmt = $pdo->prepare("INSERT INTO intentos_login (ip, usuario, fecha) VALUES (?, ?, NOW())");
        $stmt->execute([$ip, $usuario]);
        
        // Regenerar token CSRF para evitar error
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        header('Location: index.php?error=1');
        exit();
    }
}

// Si alguien accede directamente
header('Location: index.php');
exit();
?>
