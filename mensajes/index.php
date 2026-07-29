<?php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$rol = strtolower(trim($_SESSION['user_rol'] ?? ''));
$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT m.*, u.nombre as remitente_nombre 
        FROM mensajes m 
        LEFT JOIN usuarios u ON m.remitente_id = u.id 
        WHERE m.destinatario_id = ? 
        ORDER BY m.fecha DESC
    ");
    $stmt->execute([$user_id]);
    $mensajes = $stmt->fetchAll();
} catch (Exception $e) {
    $mensajes = [];
    $error = 'No se pudieron cargar los mensajes.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mensajes - TEC AZUAY</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background:#f0f2f5; font-family:'Inter',sans-serif; }
        .container { max-width:900px; margin:0 auto; padding:20px; }
        .header { background:white; padding:20px 30px; border-radius:16px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 20px rgba(26,35,126,0.08); margin-bottom:25px; border-left:4px solid #dcc97a; flex-wrap:wrap; gap:15px; }
        .header h1 { color:#1a237e; font-size:22px; font-weight:800; }
        .btn-volver { background:#1a237e; color:white; padding:8px 20px; border-radius:10px; text-decoration:none; font-weight:600; font-size:13px; }
        .msg-card { background:white; border-radius:16px; padding:18px 22px; margin-bottom:12px; box-shadow:0 4px 20px rgba(26,35,126,0.06); border-left:4px solid #2196f3; }
        .msg-header { display:flex; justify-content:space-between; margin-bottom:8px; }
        .msg-from { font-weight:700; color:#1a237e; font-size:14px; }
        .msg-date { color:#888; font-size:12px; }
        .msg-subject { font-weight:600; color:#333; margin-bottom:6px; }
        .msg-body { color:#666; font-size:13px; line-height:1.5; }
        .no-leido { border-left-color:#e91e63; background:#fff8fb; }
        .vacio { text-align:center; color:#888; padding:40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💬 Mensajes</h1>
            <a href="../dashboard_<?php echo $rol; ?>.php" class="btn-volver">← Volver al Dashboard</a>
        </div>
        <?php if (!empty($error)): ?>
            <div style="background:#ffebee;color:#c62828;padding:12px 20px;border-radius:10px;margin-bottom:20px;font-weight:600;"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (empty($mensajes)): ?>
            <div class="vacio">No tienes mensajes.</div>
        <?php else: ?>
            <?php foreach ($mensajes as $m): ?>
            <div class="msg-card <?php echo empty($m['leido']) ? 'no-leido' : ''; ?>">
                <div class="msg-header">
                    <span class="msg-from">👤 <?php echo htmlspecialchars($m['remitente_nombre'] ?? 'Sistema'); ?></span>
                    <span class="msg-date"><?php echo date('d/m/Y H:i', strtotime($m['fecha'])); ?></span>
                </div>
                <div class="msg-subject"><?php echo htmlspecialchars($m['asunto'] ?? 'Sin asunto'); ?></div>
                <div class="msg-body"><?php echo nl2br(htmlspecialchars($m['contenido'] ?? '')); ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
