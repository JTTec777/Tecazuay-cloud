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
        SELECT * FROM notificaciones 
        WHERE usuario_id = ? 
        ORDER BY fecha DESC
    ");
    $stmt->execute([$user_id]);
    $notificaciones = $stmt->fetchAll();
    
    // Marcar como leídas al entrar
    $pdo->prepare("UPDATE notificaciones SET leida = TRUE WHERE usuario_id = ? AND leida = FALSE")->execute([$user_id]);
} catch (Exception $e) {
    $notificaciones = [];
    $error = 'No se pudieron cargar las notificaciones.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones - TEC AZUAY</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background:#f0f2f5; font-family:'Inter',sans-serif; }
        .container { max-width:900px; margin:0 auto; padding:20px; }
        .header { background:white; padding:20px 30px; border-radius:16px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 20px rgba(26,35,126,0.08); margin-bottom:25px; border-left:4px solid #dcc97a; flex-wrap:wrap; gap:15px; }
        .header h1 { color:#1a237e; font-size:22px; font-weight:800; }
        .btn-volver { background:#1a237e; color:white; padding:8px 20px; border-radius:10px; text-decoration:none; font-weight:600; font-size:13px; }
        .notif-card { background:white; border-radius:16px; padding:18px 22px; margin-bottom:12px; box-shadow:0 4px 20px rgba(26,35,126,0.06); border-left:4px solid #9c27b0; }
        .notif-title { font-weight:700; color:#1a237e; font-size:15px; margin-bottom:6px; }
        .notif-body { color:#666; font-size:13px; line-height:1.5; margin-bottom:8px; }
        .notif-date { color:#888; font-size:12px; }
        .vacio { text-align:center; color:#888; padding:40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 Notificaciones</h1>
            <a href="../dashboard_<?php echo $rol; ?>.php" class="btn-volver">← Volver al Dashboard</a>
        </div>
        <?php if (!empty($error)): ?>
            <div style="background:#ffebee;color:#c62828;padding:12px 20px;border-radius:10px;margin-bottom:20px;font-weight:600;"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (empty($notificaciones)): ?>
            <div class="vacio">No tienes notificaciones.</div>
        <?php else: ?>
            <?php foreach ($notificaciones as $n): ?>
            <div class="notif-card">
                <div class="notif-title"><?php echo htmlspecialchars($n['titulo'] ?? 'Notificación'); ?></div>
                <div class="notif-body"><?php echo nl2br(htmlspecialchars($n['mensaje'] ?? '')); ?></div>
                <div class="notif-date"><?php echo date('d/m/Y H:i', strtotime($n['fecha'])); ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
