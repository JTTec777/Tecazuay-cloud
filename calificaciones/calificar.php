<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'profesor') {
    header('Location: ../index.php');
    exit();
}

$profesor_id = $_SESSION['user_id'];
$error = '';
$exito = '';

// Obtener entrega
$entrega_id = isset($_GET['entrega_id']) ? (int)$_GET['entrega_id'] : 0;

$stmt = $pdo->prepare("
    SELECT e.id, e.nombre_archivo, e.ruta_archivo, e.fecha_entrega,
           a.titulo as actividad_titulo, a.descripcion as actividad_desc,
           u.nombre as estudiante_nombre,
           c.nombre as curso_nombre,
           cal.calificacion, cal.comentario, cal.fecha_calificacion
    FROM entregas e
    JOIN actividades a ON e.actividad_id = a.id
    JOIN cursos c ON a.curso_id = c.id
    JOIN usuarios u ON e.estudiante_id = u.id
    LEFT JOIN calificaciones cal ON cal.entrega_id = e.id
    WHERE e.id = ? AND c.profesor_id = ?
");
$stmt->execute([$entrega_id, $profesor_id]);
$entrega = $stmt->fetch();

if (!$entrega) {
    header('Location: ../panel_profesor_tareas.php');
    exit();
}

// Procesar calificación
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nota = (float)str_replace(',', '.', $_POST['calificacion']);
    $comentario = trim($_POST['comentario'] ?? '');
    
    if ($nota >= 0 && $nota <= 20) {
        // Verificar si ya existe calificación
        $stmt = $pdo->prepare("SELECT id FROM calificaciones WHERE entrega_id = ?");
        $stmt->execute([$entrega_id]);
        $existente = $stmt->fetch();
        
        if ($existente) {
            $stmt = $pdo->prepare("UPDATE calificaciones SET calificacion = ?, comentario = ?, calificado_por = ?, fecha_calificacion = NOW() WHERE entrega_id = ?");
            $stmt->execute([$nota, $comentario, $profesor_id, $entrega_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO calificaciones (entrega_id, calificacion, comentario, calificado_por, fecha_calificacion) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$entrega_id, $nota, $comentario, $profesor_id]);
            
            // Notificar al estudiante
            $stmt = $pdo->prepare("SELECT estudiante_id FROM entregas WHERE id = ?");
            $stmt->execute([$entrega_id]);
            $est = $stmt->fetch();
            if ($est) {
                $stmt = $pdo->prepare("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $est['estudiante_id'],
                    '📊 Nueva calificación',
                    'Tu entrega de "' . $entrega['actividad_titulo'] . '" ha sido calificada con ' . number_format($nota, 2) . '/20',
                    'calificacion'
                ]);
            }
        }
        
        header('Location: ../panel_profesor_tareas.php?exito=1');
        exit();
    } else {
        $error = '❌ La calificación debe estar entre 0 y 20';
    }
}

$titulo = 'Calificar Entrega - TEC AZUAY';
$base_path = '..';
include '../includes/header.php';
?>
<style>
    .calificar-container { max-width: 900px; margin: 0 auto; }
    .info-card { background: white; border-radius: 16px; padding: 25px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); }
    .info-card h3 { color: #1a237e; margin-bottom: 12px; font-size: 18px; }
    .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f2f5; font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-row strong { color: #1a237e; }
    .doc-preview { background: #f8f9ff; border: 2px dashed #1a237e; border-radius: 12px; padding: 30px; text-align: center; margin: 15px 0; }
    .doc-preview a { display: inline-block; background: #1a237e; color: white; padding: 12px 30px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.3s; }
    .doc-preview a:hover { background: #0d1457; transform: translateY(-2px); }
    .form-calificar { background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); }
    .form-calificar label { display: block; color: #1a237e; font-weight: 600; margin-bottom: 6px; font-size: 13px; }
    .form-calificar input, .form-calificar textarea { width: 100%; padding: 12px 14px; border: 2px solid #e8ecf5; border-radius: 10px; font-size: 14px; margin-bottom: 16px; transition: 0.3s; font-family: 'Inter', sans-serif; }
    .form-calificar input:focus, .form-calificar textarea:focus { border-color: #1a237e; outline: none; box-shadow: 0 0 0 4px rgba(26,35,126,0.08); }
    .form-calificar input { max-width: 120px; text-align: center; font-size: 24px; font-weight: 700; color: #1a237e; }
    .nota-escala { color: #888; font-size: 12px; margin-top: -12px; margin-bottom: 16px; }
    .mensaje-error { background: #ffebee; color: #c62828; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-weight: 600; border-left: 4px solid #c62828; }
    .btn-guardar { background: #4caf50; color: white; padding: 12px 30px; border: none; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; transition: 0.3s; }
    .btn-guardar:hover { background: #388e3c; transform: translateY(-2px); }
    .btn-volver { background: #e8eaf6; color: #1a237e; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px; display: inline-block; margin-left: 10px; transition: 0.3s; }
    .btn-volver:hover { background: #d5d9e8; }
    .nota-actual { background: #e8f5e9; color: #2e7d32; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; }
    .nota-actual strong { font-size: 24px; }
</style>

<div class="calificar-container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <h2 style="color:#1a237e;">📝 Calificar Entrega</h2>
        <a href="../panel_profesor_tareas.php" class="btn-volver">← Volver a tareas</a>
    </div>

    <?php if ($error): ?>
        <div class="mensaje-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Información de la entrega -->
    <div class="info-card">
        <h3>📋 Información de la Entrega</h3>
        <div class="info-row">
            <span><strong>👤 Estudiante:</strong> <?php echo htmlspecialchars($entrega['estudiante_nombre']); ?></span>
            <span><strong>📚 Curso:</strong> <?php echo htmlspecialchars($entrega['curso_nombre']); ?></span>
        </div>
        <div class="info-row">
            <span><strong>📝 Actividad:</strong> <?php echo htmlspecialchars($entrega['actividad_titulo']); ?></span>
            <span><strong>📅 Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($entrega['fecha_entrega'])); ?></span>
        </div>
    </div>

    <!-- Documento del estudiante -->
    <div class="info-card">
        <h3>📄 Documento Entregado</h3>
        <?php if (!empty($entrega['ruta_archivo'])): ?>
            <div class="doc-preview">
                <div style="font-size: 48px; margin-bottom: 10px;">📄</div>
                <div style="color: #1a237e; font-weight: 600; margin-bottom: 15px;"><?php echo htmlspecialchars($entrega['nombre_archivo']); ?></div>
                <a href="<?php echo htmlspecialchars($entrega['ruta_archivo']); ?>" target="_blank">👁️ Ver / Descargar Documento</a>
            </div>
        <?php else: ?>
            <p style="color:#999; text-align:center; padding:20px;">No hay archivo adjunto.</p>
        <?php endif; ?>
    </div>

    <!-- Calificación actual -->
    <?php if (!empty($entrega['calificacion'])): ?>
        <div class="nota-actual">
            ✅ <strong>Calificación actual:</strong> <strong><?php echo number_format($entrega['calificacion'], 2); ?>/20</strong>
            <?php if (!empty($entrega['comentario'])): ?>
                <br><span style="color:#555;">💬 <?php echo htmlspecialchars($entrega['comentario']); ?></span>
            <?php endif; ?>
            <br><span style="font-size:12px; color:#888;">🕐 <?php echo date('d/m/Y H:i', strtotime($entrega['fecha_calificacion'])); ?></span>
        </div>
    <?php endif; ?>

    <!-- Formulario de calificación -->
    <div class="form-calificar">
        <h3 style="color:#1a237e; margin-bottom:16px;"><?php echo !empty($entrega['calificacion']) ? '✏️ Editar Calificación' : '⭐ Nueva Calificación'; ?></h3>
        <form method="POST">
            <label for="calificacion">Calificación (0 - 20):</label>
            <input type="number" id="calificacion" name="calificacion" step="0.01" min="0" max="20" required
                   value="<?php echo !empty($entrega['calificacion']) ? number_format($entrega['calificacion'], 2) : ''; ?>">
            <div class="nota-escala">Escala: 0.00 a 20.00 (usa punto o coma para decimales)</div>
            
            <label for="comentario">Comentario / Retroalimentación:</label>
            <textarea id="comentario" name="comentario" rows="4" placeholder="Escribe un comentario para el estudiante..."><?php echo !empty($entrega['comentario']) ? htmlspecialchars($entrega['comentario']) : ''; ?></textarea>
            
            <button type="submit" class="btn-guardar">💾 Guardar Calificación</button>
            <a href="../panel_profesor_tareas.php" class="btn-volver">Cancelar</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
