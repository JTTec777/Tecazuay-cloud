<?php
require_once 'config.php';
$titulo = 'Mis Actividades - TEC AZUAY';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'profesor') {
    header('Location: index.php');
    exit();
}

// Eliminar actividad
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    // Primero borrar entregas y calificaciones asociadas
    $stmt = $pdo->prepare("SELECT id FROM entregas WHERE actividad_id = ?");
    $stmt->execute([$id]);
    $entregas = $stmt->fetchAll();
    foreach ($entregas as $e) {
        $pdo->prepare("DELETE FROM calificaciones WHERE entrega_id = ?")->execute([$e['id']]);
    }
    $pdo->prepare("DELETE FROM entregas WHERE actividad_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM actividades WHERE id = ?")->execute([$id]);
    header('Location: panel_profesor_mis_actividades.php?exito=eliminada');
    exit();
}

// Obtener actividades con info de curso y conteo de entregas
$stmt = $pdo->query("
    SELECT a.*, c.nombre as curso_nombre,
           (SELECT COUNT(*) FROM entregas WHERE actividad_id = a.id) as total_entregas,
           (SELECT COUNT(*) FROM entregas e LEFT JOIN calificaciones cal ON cal.entrega_id = e.id WHERE actividad_id = a.id AND cal.id IS NOT NULL) as entregas_calificadas
    FROM actividades a
    JOIN cursos c ON a.curso_id = c.id
    ORDER BY a.fecha_entrega DESC
");
$actividades = $stmt->fetchAll();

$exito = isset($_GET['exito']) ? $_GET['exito'] : '';
?>
<style>
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
    .stat-card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); text-align: center; border-left: 4px solid #dcc97a; }
    .stat-card h3 { color: #1a237e; font-size: 28px; margin-bottom: 4px; }
    .stat-card p { color: #888; font-size: 13px; font-weight: 600; }
    .actividad-card { background: white; border-radius: 16px; padding: 20px; margin-bottom: 15px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); border-left: 4px solid #1a237e; }
    .actividad-card h3 { color: #1a237e; font-size: 16px; margin-bottom: 6px; }
    .actividad-meta { color: #666; font-size: 13px; margin-bottom: 10px; }
    .actividad-meta strong { color: #1a237e; }
    .actividad-desc { color: #555; font-size: 14px; line-height: 1.5; margin-bottom: 12px; }
    .badge-pendiente { background: #fff3e0; color: #e65100; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 700; margin-right: 8px; }
    .badge-vencida { background: #ffebee; color: #c62828; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 700; margin-right: 8px; }
    .badge-activa { background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 700; margin-right: 8px; }
    .btn-accion { display: inline-block; padding: 6px 16px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.3s; margin-right: 6px; }
    .btn-ver { background: #1a237e; color: white; }
    .btn-ver:hover { background: #0d1457; }
    .btn-eliminar { background: #dc3545; color: white; }
    .btn-eliminar:hover { background: #c82333; }
    .btn-crear { display: inline-block; background: #4caf50; color: white; padding: 12px 28px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 14px; transition: 0.3s; margin-bottom: 20px; }
    .btn-crear:hover { background: #388e3c; transform: translateY(-2px); }
    .mensaje-exito { background: #e8f5e9; color: #2e7d32; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; border-left: 4px solid #4caf50; }
    .empty-state { text-align: center; padding: 60px 20px; color: #999; }
    .empty-state h3 { color: #1a237e; margin-bottom: 8px; }
</style>

<h2 style="color:#1a237e; margin-bottom:15px;">📋 Mis Actividades</h2>

<?php if ($exito == 'eliminada'): ?>
    <div class="mensaje-exito">✅ Actividad eliminada correctamente</div>
<?php elseif ($exito == '1'): ?>
    <div class="mensaje-exito">✅ Actividad creada correctamente</div>
<?php endif; ?>

<a href="panel_profesor_crear_actividad.php" class="btn-crear">➕ Crear nueva actividad</a>

<div class="stats-grid">
    <div class="stat-card">
        <h3><?php echo count($actividades); ?></h3>
        <p>TOTAL ACTIVIDADES</p>
    </div>
    <div class="stat-card">
        <h3><?php echo count(array_filter($actividades, fn($a) => $a['fecha_entrega'] > date('Y-m-d H:i:s'))); ?></h3>
        <p>ACTIVAS</p>
    </div>
    <div class="stat-card">
        <h3><?php echo array_sum(array_column($actividades, 'total_entregas')); ?></h3>
        <p>ENTREGAS RECIBIDAS</p>
    </div>
</div>

<?php if (count($actividades) == 0): ?>
    <div class="empty-state">
        <h3>📭 No has creado actividades</h3>
        <p>Crea tu primera actividad con el botón verde de arriba.</p>
    </div>
<?php else: ?>
    <?php foreach($actividades as $act): 
        $vencida = ($act['fecha_entrega'] && $act['fecha_entrega'] < date('Y-m-d H:i:s'));
    ?>
        <div class="actividad-card">
            <h3><?php echo htmlspecialchars($act['titulo']); ?></h3>
            <div class="actividad-meta">
                📚 <strong><?php echo htmlspecialchars($act['curso_nombre']); ?></strong> &nbsp;|&nbsp;
                📅 Entrega: <?php echo $act['fecha_entrega'] ? date('d/m/Y H:i', strtotime($act['fecha_entrega'])) : 'Sin fecha'; ?> &nbsp;|&nbsp;
                📤 <?php echo $act['total_entregas']; ?> entregas &nbsp;|&nbsp;
                ✅ <?php echo $act['entregas_calificadas']; ?> calificadas
            </div>
            <?php if (!empty($act['descripcion'])): ?>
                <div class="actividad-desc"><?php echo htmlspecialchars($act['descripcion']); ?></div>
            <?php endif; ?>
            <div style="display:flex; align-items:center; flex-wrap:wrap; gap:8px;">
                <?php if ($vencida): ?>
                    <span class="badge-vencida">❌ Vencida</span>
                <?php else: ?>
                    <span class="badge-activa">✅ Activa</span>
                <?php endif; ?>
                <a href="panel_profesor_tareas.php" class="btn-accion btn-ver">📋 Ver entregas</a>
                <a href="panel_profesor_mis_actividades.php?eliminar=<?php echo $act['id']; ?>" class="btn-accion btn-eliminar" onclick="return confirm('¿Eliminar esta actividad y TODAS sus entregas? Esta acción no se puede deshacer.')">🗑️ Eliminar</a>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
