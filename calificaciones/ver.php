<?php
require_once '../config.php';
$titulo = 'Mis Calificaciones - TEC AZUAY';
$base_path = '..';
include '../includes/header.php';

if ($_SESSION['user_rol'] != 'estudiante') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener todas las calificaciones del estudiante
$stmt = $pdo->prepare("
    SELECT 
        e.nombre_archivo,
        e.fecha_entrega,
        c.calificacion,
        c.comentario,
        c.fecha_calificacion
    FROM calificaciones c
    JOIN entregas e ON c.entrega_id = e.id
    WHERE e.estudiante_id = ?
    ORDER BY c.fecha_calificacion DESC
");
$stmt->execute([$user_id]);
$calificaciones = $stmt->fetchAll();

// Calcular promedio (sobre 10)
$total = 0;
$count = count($calificaciones);
foreach ($calificaciones as $cal) {
    $total += $cal['calificacion'];
}
$promedio = $count > 0 ? round(($total / $count) / 2, 2) : 0;
?>
<style>
    .calificacion-card { background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); border: 1px solid rgba(26,35,126,0.04); }
    .promedio-box { background: linear-gradient(135deg, #1a237e, #283593); color: white; padding: 20px; border-radius: 16px; text-align: center; margin-bottom: 20px; }
    .promedio-box .numero { font-size: 48px; font-weight: 800; }
    .promedio-box .label { font-size: 14px; opacity: 0.8; }
    .table-calificaciones { width: 100%; border-collapse: collapse; }
    .table-calificaciones th { background: #1a237e; color: white; padding: 10px 14px; text-align: left; font-size: 13px; }
    .table-calificaciones td { padding: 10px 14px; border-bottom: 1px solid #f0f2f5; font-size: 14px; }
    .table-calificaciones tr:hover { background: #f8f9ff; }
    .nota-aprobada { color: #4caf50; font-weight: 700; }
    .nota-reprobada { color: #f44336; font-weight: 700; }
    .empty-message { color: #999; text-align: center; padding: 40px; font-size: 16px; }
</style>

<div class="calificacion-card">
    <h2 style="color:#1a237e; margin-bottom:20px;">📊 Mis Calificaciones</h2>
    
    <?php if ($count > 0): ?>
        <div class="promedio-box">
            <div class="numero"><?php echo $promedio; ?></div>
            <div class="label">Promedio general sobre 10</div>
        </div>
        
        <table class="table-calificaciones">
            <thead>
                <tr>
                    <th>Archivo entregado</th>
                    <th>Fecha de entrega</th>
                    <th>Calificación</th>
                    <th>Comentario del profesor</th>
                    <th>Fecha de calificación</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($calificaciones as $cal): ?>
                    <tr>
                        <td><strong>📄 <?php echo escapar($cal['nombre_archivo']); ?></strong></td>
                        <td><?php echo $cal['fecha_entrega']; ?></td>
                        <td class="<?php echo $cal['calificacion'] >= 7 ? 'nota-aprobada' : 'nota-reprobada'; ?>">
                            ⭐ <?php echo $cal['calificacion']; ?>/10
                        </td>
                        <td><?php echo $cal['comentario'] ? escapar($cal['comentario']) : '—'; ?></td>
                        <td><?php echo $cal['fecha_calificacion']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-message">
            📭 No tienes calificaciones aún.
            <br><small style="color:#aaa;">Cuando un profesor califique tus entregas, aparecerán aquí.</small>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
