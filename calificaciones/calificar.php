<?php
require_once '../config.php';
$titulo = 'Calificar Entregas - TEC AZUAY';
$base_path = '..';
include '../includes/header.php';

if ($_SESSION['user_rol'] != 'profesor') {
    header('Location: ../index.php');
    exit();
}

$exito = isset($_GET['exito']) ? $_GET['exito'] : '';
$error = '';

// Procesar calificación
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['calificar'])) {
    $entrega_id = (int)$_POST['entrega_id'];
    $calificacion = (float)$_POST['calificacion'];
    $comentario = $_POST['comentario'];
    $profesor_id = $_SESSION['user_id'];
    
    if ($calificacion >= 0 && $calificacion <= 20) {
        $stmt = $pdo->prepare("INSERT INTO calificaciones (entrega_id, calificacion, comentario, calificado_por) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$entrega_id, $calificacion, $comentario, $profesor_id])) {
            // Notificar al estudiante
            $stmt = $pdo->prepare("SELECT estudiante_id FROM entregas WHERE id = ?");
            $stmt->execute([$entrega_id]);
            $estudiante_id = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $estudiante_id,
                '📊 Nueva calificación',
                'Tu entrega ha sido calificada con ' . $calificacion . '/20',
                'calificacion'
            ]);
            
            header('Location: calificar.php?exito=Calificación guardada correctamente');
            exit();
        } else {
            $error = '❌ Error al guardar la calificación';
        }
    } else {
        $error = '❌ La calificación debe estar entre 0 y 20';
    }
}

// Obtener entregas sin calificar
$stmt = $pdo->prepare("
    SELECT e.*, u.nombre as estudiante_nombre, u.usuario
    FROM entregas e
    JOIN usuarios u ON e.estudiante_id = u.id
    WHERE e.id NOT IN (SELECT entrega_id FROM calificaciones)
    ORDER BY e.fecha_entrega DESC
");
$stmt->execute();
$entregas_sin_calificar = $stmt->fetchAll();

// Obtener entregas ya calificadas
$stmt = $pdo->prepare("
    SELECT e.*, u.nombre as estudiante_nombre, u.usuario,
           c.calificacion, c.comentario, c.fecha_calificacion
    FROM entregas e
    JOIN usuarios u ON e.estudiante_id = u.id
    JOIN calificaciones c ON e.id = c.entrega_id
    ORDER BY c.fecha_calificacion DESC
    LIMIT 20
");
$stmt->execute();
$entregas_calificadas = $stmt->fetchAll();
?>
<style>
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); border: 1px solid rgba(26,35,126,0.04); }
    .card h3 { color: #1a237e; font-size: 16px; border-bottom: 2px solid #e8eaf6; padding-bottom: 10px; margin-bottom: 15px; }
    .entrega-item { padding: 10px 0; border-bottom: 1px solid #f0f2f5; }
    .entrega-item:last-child { border-bottom: none; }
    .entrega-item .nombre { font-weight: 600; color: #1a237e; font-size: 14px; }
    .entrega-item .archivo { font-size: 13px; color: #666; }
    .entrega-item .fecha { font-size: 12px; color: #999; }
    .form-calificar { margin-top: 8px; display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .form-calificar input[type="number"] { width: 70px; padding: 5px 8px; border: 2px solid #e8ecf5; border-radius: 8px; font-size: 13px; }
    .form-calificar input[type="text"] { flex: 1; min-width: 120px; padding: 5px 10px; border: 2px solid #e8ecf5; border-radius: 8px; font-size: 13px; }
    .form-calificar button { background: #1a237e; color: white; padding: 5px 16px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 13px; transition: 0.3s; }
    .form-calificar button:hover { background: #0d1457; }
    .badge-calificada { background: #e8f5e9; color: #2e7d32; padding: 2px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
    .calificacion { font-weight: 700; color: #1a237e; font-size: 18px; }
    .comentario { color: #666; font-size: 13px; font-style: italic; }
    .empty-message { color: #999; text-align: center; padding: 20px; font-size: 14px; }
    .mensaje-flotante { padding: 10px 16px; border-radius: 10px; margin-bottom: 15px; font-weight: 600; }
    .mensaje-exito { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
    .mensaje-error { background: #ffebee; color: #c62828; border-left: 4px solid #c62828; }
    @media (max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }
</style>

<?php if ($exito): ?>
    <div class="mensaje-flotante mensaje-exito">✅ <?php echo $exito; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="mensaje-flotante mensaje-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="grid-2">
    <!-- Entregas sin calificar -->
    <div class="card">
        <h3>📤 Pendientes de calificar</h3>
        <?php if (count($entregas_sin_calificar) == 0): ?>
            <div class="empty-message">✅ No hay entregas pendientes</div>
        <?php else: ?>
            <?php foreach($entregas_sin_calificar as $entrega): ?>
                <div class="entrega-item">
                    <div class="nombre">👤 <?php echo htmlspecialchars($entrega['estudiante_nombre']); ?></div>
                    <div class="archivo">📄 <?php echo htmlspecialchars($entrega['nombre_archivo']); ?></div>
                    <div class="fecha">📅 <?php echo $entrega['fecha_entrega']; ?></div>
                    <form method="POST" class="form-calificar">
                        <input type="hidden" name="entrega_id" value="<?php echo $entrega['id']; ?>">
                        <input type="number" name="calificacion" min="0" max="20" step="0.5" placeholder="Nota" required>
                        <input type="text" name="comentario" placeholder="Comentario">
                        <button type="submit" name="calificar">Calificar</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Entregas ya calificadas -->
    <div class="card">
        <h3>✅ Ya calificadas</h3>
        <?php if (count($entregas_calificadas) == 0): ?>
            <div class="empty-message">📭 No hay entregas calificadas aún</div>
        <?php else: ?>
            <?php foreach($entregas_calificadas as $entrega): ?>
                <div class="entrega-item">
                    <div class="nombre">👤 <?php echo htmlspecialchars($entrega['estudiante_nombre']); ?></div>
                    <div class="archivo">📄 <?php echo htmlspecialchars($entrega['nombre_archivo']); ?></div>
                    <div>
                        <span class="calificacion">⭐ <?php echo $entrega['calificacion']; ?>/20</span>
                        <span class="badge-calificada">✅ Calificada</span>
                    </div>
                    <?php if ($entrega['comentario']): ?>
                        <div class="comentario">💬 <?php echo htmlspecialchars($entrega['comentario']); ?></div>
                    <?php endif; ?>
                    <div class="fecha">📅 <?php echo $entrega['fecha_calificacion']; ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
