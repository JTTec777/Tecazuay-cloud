k<?php
require_once '../config.php';

$user_id = $_SESSION['user_id'];
$mensaje = '';
$error = '';

// OBTENER DESTINATARIO POR DEFECTO (si viene por GET)
$destinatario_id = isset($_GET['para']) ? (int)$_GET['para'] : 0;
$destinatario_nombre = '';
if ($destinatario_id > 0) {
    $stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmt->execute([$destinatario_id]);
    $row = $stmt->fetch();
    if ($row) {
        $destinatario_nombre = $row['nombre'];
    }
}

// OBTENER LISTA DE USUARIOS
$stmt = $pdo->query("SELECT id, nombre, rol_id FROM usuarios WHERE id != $user_id ORDER BY nombre");
$usuarios = $stmt->fetchAll();

// ============================================
// PROCESAR ENVÍO PRIMERO (antes de cualquier output HTML)
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $destinatario = (int)$_POST['destinatario'];
    $asunto = trim($_POST['asunto']);
    $contenido = trim($_POST['contenido']);
    
    if ($destinatario > 0 && !empty($asunto) && !empty($contenido)) {
        $stmt = $pdo->prepare("INSERT INTO mensajes (remitente_id, destinatario_id, asunto, mensaje) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $destinatario, $asunto, $contenido])) {
            // Notificar al destinatario
            $stmt = $pdo->prepare("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $destinatario,
                '✉️ Nuevo mensaje',
                'Has recibido un mensaje de ' . $_SESSION['user_nombre'] . ': ' . $asunto,
                'mensaje'
            ]);
            header('Location: index.php?enviado=1');
            exit();
        } else {
            $error = '❌ Error al enviar el mensaje';
        }
    } else {
        $error = '❌ Todos los campos son obligatorios';
    }
}

// ============================================
// AHORA SÍ SE INCLUYE EL HEADER (después de toda la lógica PHP)
// ============================================
$titulo = 'Enviar Mensaje - TEC AZUAY';
$base_path = '..';
include '../includes/header.php';
?>
<style>
    .form-mensaje { background: white; border-radius: 16px; padding: 25px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 20px rgba(26,35,126,0.06); }
    .form-mensaje label { display: block; color: #1a237e; font-weight: 600; margin-bottom: 4px; font-size: 13px; }
    .form-mensaje input, .form-mensaje select, .form-mensaje textarea { width: 100%; padding: 10px 14px; border: 2px solid #e8ecf5; border-radius: 10px; font-size: 14px; margin-bottom: 14px; transition: 0.3s; font-family: 'Inter', sans-serif; }
    .form-mensaje input:focus, .form-mensaje select:focus, .form-mensaje textarea:focus { border-color: #1a237e; outline: none; box-shadow: 0 0 0 4px rgba(26,35,126,0.08); }
    .form-mensaje textarea { min-height: 120px; resize: vertical; }
    .mensaje-flotante { padding: 10px 14px; border-radius: 10px; margin-bottom: 14px; font-weight: 600; }
    .mensaje-error { background: #ffebee; color: #c62828; border-left: 4px solid #c62828; }
</style>

<div class="form-mensaje">
    <h2 style="color:#1a237e; margin-bottom:16px;">✉️ Enviar Mensaje</h2>
    
    <?php if ($error): ?>
        <div class="mensaje-flotante mensaje-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <label for="destinatario">Para:</label>
        <select id="destinatario" name="destinatario" required>
            <option value="">Selecciona un destinatario</option>
            <?php foreach($usuarios as $user): ?>
                <option value="<?php echo $user['id']; ?>" <?php echo ($user['id'] == $destinatario_id) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($user['nombre']); ?> 
                    (<?php echo $user['rol_id'] == 1 ? 'Estudiante' : 'Profesor'; ?>)
                </option>
            <?php endforeach; ?>
        </select>
        
        <label for="asunto">Asunto:</label>
        <input type="text" id="asunto" name="asunto" required placeholder="Asunto del mensaje">
        
        <label for="contenido">Mensaje:</label>
        <textarea id="contenido" name="contenido" required placeholder="Escribe tu mensaje aquí..."></textarea>
        
        <button type="submit" class="btn-primary">📤 Enviar Mensaje</button>
        <a href="index.php" class="btn-secondary" style="margin-left:10px; background:#e8eaf6; color:#1a237e; padding:8px 20px; border-radius:8px; text-decoration:none; font-weight:600; font-size:13px;">Cancelar</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
