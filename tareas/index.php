<?php
require_once '../config.php';
$titulo = 'Mis Tareas - TEC AZUAY';
$base_path = '..';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'estudiante') {
    header('Location: ../index.php');
    exit();
}

$estudiante_id = $_SESSION['user_id'];

// Obtener cursos del estudiante
$stmt = $pdo->prepare("
    SELECT c.id, c.nombre 
    FROM cursos c 
    JOIN inscripciones i ON c.id = i.curso_id 
    WHERE i.estudiante_id = ? AND c.activo = TRUE
");
$stmt->execute([$estudiante_id]);
$cursos = $stmt->fetchAll();

// Obtener actividades de esos cursos
$actividades = [];
if (count($cursos) > 0) {
    $curso_ids = array_column($cursos, 'id');
    $placeholders = implode(',', array_fill(0, count($curso_ids), '?'));
    
    $stmt = $pdo->prepare("
        SELECT a.*, c.nombre as curso_nombre,
               e.id as entrega_id, e.nombre_archivo, e.fecha_entrega as fecha_subida,
               cal.calificacion, cal.comentario
        FROM actividades a
        JOIN cursos c ON a.curso_id = c.id
        LEFT JOIN entregas e ON e.actividad_id = a.id AND e.estudiante_id = ?
        LEFT JOIN calificaciones cal ON cal.entrega_id = e.id
        WHERE a.curso_id IN ($placeholders)
        ORDER BY a.fecha_entrega ASC
    ");
    $stmt->execute(array_merge([$estudiante_id], $curso_ids));
    $actividades = $stmt->fetchAll();
}

$hoy = date('Y-m-d H:i:s');
?>
<style>
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px; }
    .stat-card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); text-align: center; border-left: 4px solid #dcc97a; }
    .stat-card h3 { color: #1a237e; font-size: 28px; margin-bottom: 4px; }
    .stat-card p { color: #888; font-size: 13px; font-weight: 600; }
    .tarea-card { background: white; border-radius: 16px; padding: 20px; margin-bottom: 15px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); border-left: 4px solid #1a237e; }
    .tarea-card.vencida { border-left-color: #dc3545; opacity: 0.85; }
    .tarea-card.entregada { border-left-color: #4caf50; }
    .tarea-card h3 { color: #1a237e; font-size: 16px; margin-bottom: 6px; }
    .tarea-meta { color: #666; font-size: 13px; margin-bottom: 10px; }
    .tarea-meta strong { color: #1a237e; }
    .tarea-desc { color: #555; font-size: 14px; line-height: 1.5; margin-bottom: 12px; }
    .badge-estado { display: inline-block; padding: 4px 14px; border-radius: 12px; font-size: 11px; font-weight: 700; margin-right: 8px; }
    .badge-pendiente { background: #fff3e0; color: #e65100; }
    .badge-entregada { background: #e8f5e9; color: #2e7d32; }
    .badge-vencida { background: #ffebee; color: #c62828; }
    .badge-calificada { background: #e3f2fd; color: #0d47a1; }
    .btn-gestionar { background: #1a237e; color: white; padding: 6px 18px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 600; display: inline-block; transition: 0.3s; margin-right: 8px; }
    .btn-gestionar:hover { background: #0d1457; transform: translateY(-2px); }
    .nota-tag { background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 8px; font-size: 13px; font-weight: 700; }
    .empty-state { text-align: center; padding: 60px 20px; color: #999; }
    .empty-state h3 { color: #1a237e; margin-bottom: 8px; }
</style>

<h2 style="color:#1a237e; margin-bottom:20px;">📋 Mis Tareas y Actividades</h2>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <h3><?php echo count($actividades); ?></h3>
        <p>TOTAL TAREAS</p>
    </div>
    <div class="stat-card">
        <h3><?php echo count(array_filter($actividades, fn($a) => empty($a['entrega_id']) && $a['fecha_entrega'] >= $hoy)); ?></h3>
        <p>PENDIENTES</p>
    </div>
    <div class="stat-card">
        <h3><?php echo count(array_filter($actividades, fn($a) => !empty($a['entrega_id']))); ?></h3>
        <p>ENTREGADAS</p>
    </div>
    <div class="stat-card">
        <h3><?php echo count(array_filter($actividades, fn($a) => !empty($a['calificacion']))); ?></h3>
        <p>CALIFICADAS</p>
    </div>
</div>

<?php if (count($actividades) == 0): ?>
    <div class="empty-state">
        <h3>📭 No tienes tareas asignadas</h3>
        <p>Aún no hay actividades en tus cursos inscritos.</p>
    </div>
<?php else: ?>
    <?php foreach($actividades as $act): 
        $vencida = ($act['fecha_entrega'] && $act['fecha_entrega'] < $hoy);
        $entregada = !empty($act['entrega_id']);
        $calificada = !empty($act['calificacion']);
        
        $clase_card = 'tarea-card';
        if ($calificada) $clase_card .= ' entregada';
        elseif ($entregada) $clase_card .= ' entregada';
        elseif ($vencida) $clase_card .= ' vencida';
    ?>
        <div class="<?php echo $clase_card; ?>">
            <h3><?php echo htmlspecialchars($act['titulo']); ?></h3>
            <div class="tarea-meta">
                📚 <strong><?php echo htmlspecialchars($act['curso_nombre']); ?></strong> &nbsp;|&nbsp;
                📅 Entrega: <?php echo $act['fecha_entrega'] ? date('d/m/Y H:i', strtotime($act['fecha_entrega'])) : 'Sin fecha'; ?>
            </div>
            <?php if (!empty($act['descripcion'])): ?>
                <div class="tarea-desc"><?php echo htmlspecialchars($act['descripcion']); ?></div>
            <?php endif; ?>
            <div style="display:flex; align-items:center; flex-wrap:wrap; gap:10px;">
                <?php if ($calificada): ?>
                    <span class="badge-estado badge-calificada">✅ Calificado</span>
                    <span class="nota-tag">⭐ <?php echo number_format($act['calificacion'], 2); ?>/20</span>
                    <?php if (!empty($act['comentario'])): ?>
                        <span style="color:#666; font-size:13px;">💬 <?php echo htmlspecialchars($act['comentario']); ?></span>
                    <?php endif; ?>
                <?php elseif ($entregada): ?>
                    <span class="badge-estado badge-entregada">📤 Entregado</span>
                    <span style="color:#888; font-size:13px;">Esperando calificación...</span>
                <?php elseif ($vencida): ?>
                    <span class="badge-estado badge-vencida">❌ Vencida</span>
                <?php else: ?>
                    <span class="badge-estado badge-pendiente">⏳ Pendiente</span>
                <?php endif; ?>
                
                <!-- SIEMPRE hay botón para entrar a gestionar -->
                <a href="../actividad.php?id=<?php echo $act['id']; ?>" class="btn-gestionar">
                    <?php echo $entregada ? '🔧 Gestionar entrega' : '📤 Entregar ahora'; ?>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
