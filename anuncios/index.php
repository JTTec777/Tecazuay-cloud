<?php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$rol = strtolower(trim($_SESSION['user_rol'] ?? ''));
$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->query("
        SELECT a.*, u.nombre as autor_nombre 
        FROM anuncios a 
        LEFT JOIN usuarios u ON a.autor_id = u.id 
        ORDER BY a.fecha DESC
    ");
    $anuncios = $stmt->fetchAll();
} catch (Exception $e) {
    $anuncios = [];
    $error = 'No se pudieron cargar los anuncios.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Anuncios - TEC AZUAY</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background:#f0f2f5; font-family:'Inter',sans-serif; }
        .container { max-width:900px; margin:0 auto; padding:20px; }
        .header { background:white; padding:20px 30px; border-radius:16px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 20px rgba(26,35,126,0.08); margin-bottom:25px; border-left:4px solid #dcc97a; flex-wrap:wrap; gap:15px; }
        .header h1 { color:#1a237e; font-size:22px; font-weight:800; }
        .btn-volver { background:#1a237e; color:white; padding:8px 20px; border-radius:10px; text-decoration:none; font-weight:600; font-size:13px; }
        .anuncio-card { background:white; border-radius:16px; padding:20px; margin-bottom:15px; box-shadow:0 4px 20px rgba(26,35,126,0.06); border-left:4px solid #e91e63; }
        .anuncio-title { font-weight:700; color:#1a237e; font-size:16px; margin-bottom:8px; }
        .anuncio-meta { color:#888; font-size:12px; margin-bottom:10px; }
        .anuncio-body { color:#444; font-size:14px; line-height:1.6; }
        .vacio { text-align:center; color:#888; padding:40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📢 Anuncios</h1>
            <a href="../dashboard_<?php echo $rol; ?>.php" class="btn-volver">← Volver al Dashboard</a>
        </div>
        <?php if (!empty($error)): ?>
            <div style="background:#ffebee;color:#c62828;padding:12px 20px;border-radius:10px;margin-bottom:20px;font-weight:600;"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (empty($anuncios)): ?>
            <div class="vacio">No hay anuncios publicados.</div>
        <?php else: ?>
            <?php foreach ($anuncios as $a): ?>
            <div class="anuncio-card">
                <div class="anuncio-title"><?php echo htmlspecialchars($a['titulo'] ?? 'Sin título'); ?></div>
                <div class="anuncio-meta">
                    👤 <?php echo htmlspecialchars($a['autor_nombre'] ?? 'Administración'); ?> &nbsp;|&nbsp; 
                    📅 <?php echo date('d/m/Y H:i', strtotime($a['fecha'])); ?>
                </div>
                <div class="anuncio-body"><?php echo nl2br(htmlspecialchars($a['contenido'] ?? '')); ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
