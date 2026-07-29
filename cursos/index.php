<?php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$rol = strtolower(trim($_SESSION['user_rol'] ?? ''));
$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->query("SELECT id, nombre, descripcion, activo FROM cursos ORDER BY id");
    $cursos = $stmt->fetchAll();
} catch (Exception $e) {
    $cursos = [];
    $error = 'No se pudieron cargar los cursos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cursos - TEC AZUAY</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background:#f0f2f5; font-family:'Inter',sans-serif; }
        .container { max-width:1100px; margin:0 auto; padding:20px; }
        .header { background:white; padding:20px 30px; border-radius:16px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 20px rgba(26,35,126,0.08); margin-bottom:25px; border-left:4px solid #dcc97a; flex-wrap:wrap; gap:15px; }
        .header h1 { color:#1a237e; font-size:22px; font-weight:800; }
        .btn-volver { background:#1a237e; color:white; padding:8px 20px; border-radius:10px; text-decoration:none; font-weight:600; font-size:13px; }
        .btn-volver:hover { background:#0d1457; }
        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:15px; }
        .card { background:white; border-radius:16px; padding:20px; box-shadow:0 4px 20px rgba(26,35,126,0.06); border-left:4px solid #1a237e; }
        .card h3 { color:#1a237e; font-size:16px; margin-bottom:8px; }
        .card p { color:#666; font-size:13px; margin-bottom:12px; }
        .badge { display:inline-block; padding:4px 12px; border-radius:12px; font-size:11px; font-weight:700; }
        .badge-activo { background:#e8f5e9; color:#2e7d32; }
        .badge-inactivo { background:#ffebee; color:#c62828; }
        .vacio { text-align:center; color:#888; padding:40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Cursos Disponibles</h1>
            <a href="../dashboard_<?php echo $rol; ?>.php" class="btn-volver">← Volver al Dashboard</a>
        </div>
        <?php if (!empty($error)): ?>
            <div style="background:#ffebee;color:#c62828;padding:12px 20px;border-radius:10px;margin-bottom:20px;font-weight:600;"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (empty($cursos)): ?>
            <div class="vacio">No hay cursos disponibles.</div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($cursos as $c): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($c['nombre']); ?></h3>
                    <p><?php echo htmlspecialchars($c['descripcion'] ?? ''); ?></p>
                    <span class="badge <?php echo ($c['activo'] ?? 0) ? 'badge-activo' : 'badge-inactivo'; ?>">
                        <?php echo ($c['activo'] ?? 0) ? '✅ Activo' : '❌ Inactivo'; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
