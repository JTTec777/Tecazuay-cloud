<?php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$rol = strtolower(trim($_SESSION['user_rol'] ?? ''));
$user_id = $_SESSION['user_id'];

// Si es profesor, redirigir a su panel de tareas
if ($rol === 'profesor') {
    header('Location: ../panel_profesor_tareas.php');
    exit();
}

// Si no es estudiante, fuera
if ($rol !== 'estudiante') {
    header('Location: ../index.php');
    exit();
}

try {
    // Actividades de cursos donde el estudiante está inscrito
    $stmt = $pdo->prepare("
        SELECT a.id, a.titulo, a.descripcion, a.fecha_entrega, c.nombre as curso_nombre
        FROM actividades a
        INNER JOIN cursos c ON a.curso_id = c.id
        INNER JOIN inscripciones i ON c.id = i.curso_id
        WHERE i.estudiante_id = ?
        ORDER BY a.fecha_entrega ASC
    ");
    $stmt->execute([$user_id]);
    $tareas = $stmt->fetchAll();
} catch (Exception $e) {
    $tareas = [];
    $error = 'No se pudieron cargar las tareas.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Tareas - TEC AZUAY</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background:#f0f2f5; font-family:'Inter',sans-serif; }
        .container { max-width:900px; margin:0 auto; padding:20px; }
        .header { background:white; padding:20px 30px; border-radius:16px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 20px rgba(26,35,126,0.08); margin-bottom:25px; border-left:4px solid #dcc97a; flex-wrap:wrap; gap:15px; }
        .header h1 { color:#1a237e; font-size:22px; font-weight:800; }
        .btn-volver { background:#1a237e; color:white; padding:8px 20px; border-radius:10px; text-decoration:none; font-weight:600; font-size:13px; }
        .tarea-card { background:white; border-radius:16px; padding:18px 22px; margin-bottom:12px; box-shadow:0 4px 20px rgba(26,35,126,0.06); border-left:4px solid #ff9800; }
        .tarea-title { font-weight:700; color:#1a237e; font-size:15px; margin-bottom:4px; }
        .tarea-curso { color:#4caf50; font-size:12px; font-weight:600; margin-bottom:8px; }
        .tarea-desc { color:#666; font-size:13px; line-height:1.5; margin-bottom:8px; }
        .tarea-deadline { color:#e65100; font-size:12px; font-weight:600; background:#fff3e0; padding:4px 10px; border-radius:8px; display:inline-block; }
        .vacio { text-align:center; color:#888; padding:40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Mis Tareas</h1>
            <a href="../dashboard_estudiante.php" class="btn-volver">← Volver al Dashboard</a>
        </div>
        <?php if (!empty($error)): ?>
            <div style="background:#ffebee;color:#c62828;padding:12px 20px;border-radius:10px;margin-bottom:20px;font-weight:600;"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (empty($tareas)): ?>
            <div class="vacio">No tienes tareas pendientes. 🎉</div>
        <?php else: ?>
            <?php foreach ($tareas as $t): ?>
            <div class="tarea-card">
                <div class="tarea-title"><?php echo htmlspecialchars($t['titulo']); ?></div>
                <div class="tarea-curso">📚 <?php echo htmlspecialchars($t['curso_nombre']); ?></div>
                <div class="tarea-desc"><?php echo nl2br(htmlspecialchars($t['descripcion'] ?? '')); ?></div>
                <div class="tarea-deadline">⏰ Entrega: <?php echo date('d/m/Y H:i', strtotime($t['fecha_entrega'])); ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
