<?php
require_once 'config.php';
$titulo = 'Tareas Entregadas - TEC AZUAY';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'profesor') {
    header('Location: index.php');
    exit();
}

// Ahora los profesores ven TODAS las entregas de TODOS los cursos
$stmt = $pdo->query("
    SELECT e.id, e.nombre_archivo, e.ruta_archivo, e.fecha_entrega,
           a.titulo as actividad_titulo, a.id as actividad_id,
           u.nombre as estudiante_nombre, u.id as estudiante_id,
           c.nombre as curso_nombre,
           cal.calificacion, cal.comentario
    FROM entregas e
    JOIN actividades a ON e.actividad_id = a.id
    JOIN cursos c ON a.curso_id = c.id
    JOIN usuarios u ON e.estudiante_id = u.id
    LEFT JOIN calificaciones cal ON cal.entrega_id = e.id
    ORDER BY e.fecha_entrega DESC
");
$entregas = $stmt->fetchAll();

// Contar cursos totales
$stmt = $pdo->query("SELECT COUNT(*) FROM cursos WHERE activo = TRUE");
$total_cursos = $stmt->fetchColumn();
?>
<style>
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
    .stat-card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); text-align: center; border-left: 4px solid #dcc97a; }
    .stat-card h3 { color: #1a237e; font-size: 28px; margin-bottom: 4px; }
    .stat-card p { color: #888; font-size: 13px; font-weight: 600; }
    .entregas-table { width: 100%; border-collapse: collapse; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(26,35,126,0.06); }
    .entregas-table th { background: #1a237e; color: white; padding: 14px; text-align: left; font-size: 13px; font-weight: 600; }
    .entregas-table td { padding: 14px; border-bottom: 1px solid #f0f2f5; font-size: 13px; color: #333; }
    .entregas-table tr:hover { background: #f8f9ff; }
    .entregas-table tr:last-child td { border-bottom: none; }
    .badge-calificado { background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 700; }
    .badge-pendiente { background: #fff3e0; color: #e65100; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 700; }
    .btn-ver { background: #1a237e; color: white; padding: 6px 16px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 600; display: inline-block; transition: 0.3s; }
    .btn-ver:hover { background: #0d1457; transform: translateY(-2px); }
    .btn-doc { background: #dcc97a; color: #1a237e; padding: 6px 16px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 600; display: inline-block; transition: 0.3s; margin-right: 6px; }
    .btn-doc:hover { background: #c4b15a; transform: translateY(-2px); }
    .empty-state { text-align: center; padding: 60px 20px; color: #999; }
    .empty-state h3 { color: #1a237e; margin-bottom: 8px; }
</style>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <h3><?php echo $total_cursos; ?></h3>
        <p>CURSOS TOTALES</p>
    </div>
    <div class="stat-card">
        <h3><?php echo count($entregas); ?></h3>
        <p>ENTREGAS TOTALES</p>
    </div>
    <div class="stat-card">
        <h3><?php echo count(array_filter($entregas, fn($e) => !empty($e['calificacion']))); ?></h3>
        <p>CALIFICADAS</p>
    </div>
    <div class="stat-card">
        <h3><?php echo count(array_filter($entregas, fn($e) => empty($e['calificacion']))); ?></h3>
        <p>PENDIENTES</p>
    </div>
</div>

<!-- Título -->
<h2 style="color:#1a237e; margin-bottom:20px;">📋 Entregas de Estudiantes</h2>

<!-- Tabla -->
<?php if (count($entregas) == 0): ?>
    <div class="empty-state">
        <h3>📭 No hay entregas aún</h3>
        <p>Los estudiantes aún no han subido archivos.</p>
    </div>
<?php else: ?>
    <table class="entregas-table">
        <thead>
            <tr>
                <th>Estudiante</th>
                <th>Curso</th>
                <th>Actividad</th>
                <th>Archivo</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Nota</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($entregas as $entrega): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($entrega['estudiante_nombre']); ?></strong></td>
                    <td><?php echo htmlspecialchars($entrega['curso_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($entrega['actividad_titulo']); ?></td>
                    <td>
                        <?php if (!empty($entrega['ruta_archivo'])): ?>
                            <a href="<?php echo htmlspecialchars($entrega['ruta_archivo']); ?>" target="_blank" class="btn-doc">📄 Ver</a>
                        <?php else: ?>
                            <span style="color:#999;">Sin archivo</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($entrega['fecha_entrega'])); ?></td>
                    <td>
                        <?php if (!empty($entrega['calificacion'])): ?>
                            <span class="badge-calificado">✅ Calificado</span>
                        <?php else: ?>
                            <span class="badge-pendiente">⏳ Pendiente</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($entrega['calificacion'])): ?>
                            <strong style="color:#1a237e;"><?php echo number_format($entrega['calificacion'], 2); ?>/20</strong>
                        <?php else: ?>
                            <span style="color:#999;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="calificaciones/calificar.php?entrega_id=<?php echo $entrega['id']; ?>" class="btn-ver">
                            <?php echo !empty($entrega['calificacion']) ? '✏️ Editar' : '📝 Calificar'; ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
