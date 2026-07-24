<?php
require_once '../config.php';
$titulo = 'Leer Mensaje - TEC AZUAY';
$base_path = '..';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener mensaje
$stmt = $pdo->prepare("
    SELECT m.*, u.nombre as remitente_nombre, u2.nombre as destinatario_nombre
    FROM mensajes m
    JOIN usuarios u ON m.remitente_id = u.id
    JOIN usuarios u2 ON m.destinatario_id = u2.id
    WHERE m.id = ? AND (m.destinatario_id = ? OR m.remitente_id = ?)
");
$stmt->execute([$id, $user_id, $user_id]);
$mensaje = $stmt->fetch();

if (!$mensaje) {
    header('Location: index.php');
    exit();
}

// Marcar como leído si el usuario es destinatario
if ($mensaje['destinatario_id'] == $user_id && $mensaje['leido'] == 0) {
    $stmt = $pdo->prepare("UPDATE mensajes SET leido = 1 WHERE id = ?");
    $stmt->execute([$id]);
}
?>
<style>
    .mensaje-detalle { background: white; border-radius: 16px; padding: 25px; max-width: 700px; margin: 0 auto; box-shadow: 0 4px 20px rgba(26,35,126,0.06); }
    .mensaje-detalle .asunto { color: #1a237e; font-size: 22px; font-weight: 700; margin-bottom: 8px; }
    .mensaje-detalle .meta { color: #666; font-size: 14px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 2px solid #e8eaf6; }
    .mensaje-detalle .contenido { color: #333; font-size: 15px; line-height: 1.8; padding: 15px 0; white-space: pre-wrap; }
    .acciones { margin-top: 20px; padding-top: 15px; border-top: 1px solid #e8eaf6; display: flex; gap: 10px; flex-wrap: wrap; }
</style>

<div class="mensaje-detalle">
    <div class="asunto">📩 <?php echo htmlspecialchars($mensaje['asunto']); ?></div>
    <div class="meta">
        <div>👤 De: <strong><?php echo htmlspecialchars($mensaje['remitente_nombre']); ?></strong></div>
        <div>👤 Para: <?php echo htmlspecialchars($mensaje['destinatario_nombre']); ?></div>
        <div>📅 <?php echo $mensaje['fecha_envio']; ?></div>
    </div>
    <div class="contenido"><?php echo nl2br(htmlspecialchars($mensaje['mensaje'])); ?></div>
    <div class="acciones">
        <a href="index.php" class="btn-primary">📋 Volver a mensajes</a>
        <?php if ($mensaje['remitente_id'] != $user_id): ?>
            <a href="enviar.php?para=<?php echo $mensaje['remitente_id']; ?>" class="btn-warning">✉️ Responder</a>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
