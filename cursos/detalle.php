<?php
require_once '../config.php';
$titulo = 'Detalle Curso - TEC AZUAY';
$base_path = '..';
include '../includes/header.php';

$curso_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$rol = $_SESSION['user_rol'];
$user_id = $_SESSION['user_id'];
$mensaje = '';

// Procesar inscripción
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['inscribirse'])) {
    $curso_id_post = (int)$_POST['curso_id'];
    if ($rol == 'estudiante') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM inscripciones WHERE estudiante_id = ? AND curso_id = ?");
        $stmt->execute([$user_id, $curso_id_post]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO inscripciones (estudiante_id, curso_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $curso_id_post]);
            
            $stmt = $pdo->prepare("SELECT profesor_id FROM cursos WHERE id = ?");
            $stmt->execute([$curso_id_post]);
            $profesor_id = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $profesor_id,
                '📚 Nuevo estudiante inscrito',
                $_SESSION['user_nombre'] . ' se ha inscrito en tu curso',
                'anuncio'
            ]);
            $mensaje = '✅ Te has inscrito correctamente al curso.';
        } else {
            $mensaje = 'ℹ️ Ya estás inscrito en este curso.';
        }
    }
}

// Obtener datos del curso
$stmt = $pdo->prepare("
    SELECT c.*, u.nombre as profesor_nombre
    FROM cursos c
    JOIN usuarios u ON c.profesor_id = u.id
    WHERE c.id = ?
");
$stmt->execute([$curso_id]);
$curso = $stmt->fetch();

if (!$curso) {
    header('Location: index.php');
    exit();
}

// Obtener estudiantes del curso
$stmt = $pdo->prepare("
    SELECT u.id, u.nombre, u.usuario
    FROM inscripciones i
    JOIN usuarios u ON i.estudiante_id = u.id
    WHERE i.curso_id = ?
    ORDER BY u.nombre
");
$stmt->execute([$curso_id]);
$estudiantes = $stmt->fetchAll();

// Verificar si el estudiante está inscrito
$inscrito = false;
if ($rol == 'estudiante') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM inscripciones WHERE estudiante_id = ? AND curso_id = ?");
    $stmt->execute([$user_id, $curso_id]);
    $inscrito = $stmt->fetchColumn() > 0;
}

// Obtener actividades del curso
$stmt = $pdo->prepare("
    SELECT * FROM actividades 
    WHERE curso_id = ? 
    ORDER BY fecha_entrega ASC
");
$stmt->execute([$curso_id]);
$actividades = $stmt->fetchAll();
?>
<style>
    .curso-header { background: white; border-radius: 16px; padding: 25px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); }
    .curso-header h2 { color: #1a237e; font-size: 24px; margin-bottom: 6px; }
    .curso-header .profesor { color: #666; font-size: 14px; }
    .curso-header .descripcion { color: #444; font-size: 14px; line-height: 1.7; margin-top: 12px; padding: 12px 16px; background: #f8f9ff; border-radius: 10px; border-left: 4px solid #dcc97a; }
    .btn-inscribir { background: #dcc97a; color: #1a237e; padding: 8px 24px; border: none; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; transition: 0.3s; }
    .btn-inscribir:hover { background: #c4b15a; transform: translateY(-2px); }
    .btn-inscrito { background: #e8eaf6; color: #1a237e; padding: 8px 24px; border: none; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: default; }
    .estudiantes-list { background: white; border-radius: 16px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); }
    .estudiantes-list h3 { color: #1a237e; font-size: 16px; margin-bottom: 12px; border-bottom: 2px solid #e8eaf6; padding-bottom: 8px; }
    .estudiantes-list ul { list-style: none; padding: 0; }
    .estudiantes-list ul li { padding: 6px 0; border-bottom: 1px solid #f0f2f5; color: #333; font-size: 14px; }
    .actividades-list { background: white; border-radius: 16px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); }
    .actividades-list h3 { color: #1a237e; font-size: 16px; margin-bottom: 12px; border-bottom: 2px solid #e8eaf6; padding-bottom: 8px; }
    .actividad-item { padding: 8px 0; border-bottom: 1px solid #f0f2f5; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
    .actividad-item:last-child { border-bottom: none; }
    .actividad-item .act-nombre { font-weight: 600; color: #1a237e; font-size: 14px; }
    .actividad-item .act-fecha { color: #666; font-size: 12px; }
    .mensaje-flotante { padding: 10px 16px; border-radius: 10px; margin-bottom: 15px; font-weight: 600; }
    .mensaje-exito { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
    .mensaje-info { background: #e3f2fd; color: #0d47a1; border-left: 4px solid #0d47a1; }

    /* ============================================
       INFORMACIÓN ADICIONAL DEL CURSO
       ============================================ */
    .info-adicional {
        background: #f8f9ff;
        border-radius: 16px;
        padding: 25px;
        margin-top: 20px;
        border-left: 4px solid #dcc97a;
    }
    .info-adicional h2 {
        color: #1a237e;
        font-size: 24px;
        margin-bottom: 10px;
    }
    .info-adicional h3 {
        color: #1a237e;
        font-size: 18px;
        margin-top: 20px;
        margin-bottom: 10px;
        border-bottom: 2px solid #dcc97a;
        padding-bottom: 8px;
    }
    .info-adicional p {
        color: #333;
        font-size: 15px;
        line-height: 1.7;
        margin-bottom: 10px;
    }
    .info-adicional ul {
        list-style: none;
        padding: 0;
        margin: 10px 0;
    }
    .info-adicional ul li {
        padding: 6px 0 6px 28px;
        position: relative;
        color: #444;
        font-size: 14px;
        border-bottom: 1px solid #f0f2f5;
    }
    .info-adicional ul li:last-child {
        border-bottom: none;
    }
    .info-adicional ul li::before {
        content: "▸";
        color: #dcc97a;
        font-weight: 700;
        position: absolute;
        left: 0;
    }
    .info-adicional hr {
        border: none;
        border-top: 2px solid #e8eaf6;
        margin: 20px 0;
    }
    .info-adicional strong {
        color: #1a237e;
    }
    .info-adicional .badge-profesor {
        display: inline-block;
        background: #1a237e;
        color: white;
        padding: 4px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        margin-top: 10px;
    }
</style>

<?php if ($mensaje): ?>
    <div class="mensaje-flotante <?php echo strpos($mensaje, '✅') !== false ? 'mensaje-exito' : 'mensaje-info'; ?>">
        <?php echo $mensaje; ?>
    </div>
<?php endif; ?>

<div class="curso-header">
    <h2>📘 <?php echo htmlspecialchars($curso['nombre']); ?></h2>
    <div class="profesor">👨‍🏫 Prof. <?php echo htmlspecialchars($curso['profesor_nombre']); ?></div>
    <div class="descripcion"><?php echo nl2br(htmlspecialchars($curso['descripcion'] ?: 'Sin descripción')); ?></div>
</div>

<?php if ($rol == 'estudiante'): ?>
    <form method="POST" style="margin-bottom:20px;">
        <input type="hidden" name="curso_id" value="<?php echo $curso_id; ?>">
        <?php if (!$inscrito): ?>
            <button type="submit" name="inscribirse" class="btn-inscribir">➕ Inscribirme en este curso</button>
        <?php else: ?>
            <span class="btn-inscrito">✅ Ya estás inscrito</span>
        <?php endif; ?>
    </form>
<?php endif; ?>

<?php if ($rol == 'profesor'): ?>
    <div class="estudiantes-list">
        <h3>👥 Estudiantes inscritos (<?php echo count($estudiantes); ?>)</h3>
        <?php if (count($estudiantes) == 0): ?>
            <p style="color:#999;">No hay estudiantes inscritos.</p>
        <?php else: ?>
            <ul>
                <?php foreach($estudiantes as $est): ?>
                    <li>👤 <?php echo htmlspecialchars($est['nombre']); ?> (@<?php echo htmlspecialchars($est['usuario']); ?>)</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Actividades del curso -->
<div class="actividades-list">
    <h3>📋 Actividades del Curso</h3>
    <?php if (count($actividades) == 0): ?>
        <p style="color:#999;">No hay actividades publicadas en este curso.</p>
    <?php else: ?>
        <?php foreach($actividades as $act): ?>
            <div class="actividad-item">
                <span class="act-nombre">📝 <?php echo htmlspecialchars($act['titulo']); ?></span>
                <span class="act-fecha">📅 Entrega: <?php echo date('d/m/Y H:i', strtotime($act['fecha_entrega'])); ?></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ============================================ -->
<!-- INFORMACIÓN ADICIONAL DEL CURSO -->
<!-- ============================================ -->
<?php if (!empty($curso['info_adicional'])): ?>
    <div class="info-adicional">
        <?php echo $curso['info_adicional']; ?>
    </div>
<?php else: ?>
    <div style="background: #fff3e0; border-radius: 12px; padding: 15px; margin-top: 15px; border-left: 4px solid #ff9800;">
        <p style="color: #e65100; margin: 0;">📝 No hay información adicional disponible para este curso.</p>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
