<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'estudiante') {
    header('Location: index.php');
    exit();
}

$estudiante_id = $_SESSION['user_id'];
$actividad_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener actividad
$stmt = $pdo->prepare("
    SELECT a.*, c.nombre as curso_nombre 
    FROM actividades a 
    JOIN cursos c ON a.curso_id = c.id 
    WHERE a.id = ?
");
$stmt->execute([$actividad_id]);
$actividad = $stmt->fetch();

if (!$actividad) {
    header('Location: tareas/');
    exit();
}

// Verificar inscripción
$stmt = $pdo->prepare("SELECT 1 FROM inscripciones WHERE estudiante_id = ? AND curso_id = ?");
$stmt->execute([$estudiante_id, $actividad['curso_id']]);
if (!$stmt->fetch()) {
    header('Location: tareas/');
    exit();
}

// Obtener entrega existente
$stmt = $pdo->prepare("SELECT * FROM entregas WHERE actividad_id = ? AND estudiante_id = ?");
$stmt->execute([$actividad_id, $estudiante_id]);
$entrega = $stmt->fetch();

// Procesar eliminación
if (isset($_GET['eliminar']) && $entrega) {
    // 1. Primero borrar calificación (si existe) para evitar foreign key violation
    $stmt = $pdo->prepare("DELETE FROM calificaciones WHERE entrega_id = ?");
    $stmt->execute([$entrega['id']]);
    
    // 2. Borrar archivo de Supabase Storage
    $nombre_archivo = basename($entrega['ruta_archivo']);
    supabaseDelete($nombre_archivo);
    
    // 3. Finalmente borrar la entrega
    $stmt = $pdo->prepare("DELETE FROM entregas WHERE id = ?");
    $stmt->execute([$entrega['id']]);
    
    header('Location: actividad.php?id=' . $actividad_id . '&exito=Entrega eliminada correctamente');
    exit();
}

$mensaje = '';
$tipo_mensaje = 'exito';
if (isset($_GET['exito'])) { $mensaje = $_GET['exito']; }
elseif (isset($_GET['error'])) { $mensaje = $_GET['error']; $tipo_mensaje = 'error'; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($actividad['titulo']); ?> - TEC AZUAY</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .actividad-container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .btn-volver { display: inline-block; background: #e8eaf6; color: #1a237e; padding: 10px 22px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px; margin-bottom: 20px; transition: 0.3s; }
        .btn-volver:hover { background: #d5d9e8; transform: translateY(-2px); }
        .actividad-card { background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 20px rgba(26,35,126,0.08); margin-bottom: 20px; }
        .actividad-card h2 { color: #1a237e; margin-bottom: 10px; font-size: 22px; }
        .meta { color: #666; font-size: 14px; margin-bottom: 15px; font-weight: 500; }
        .desc { color: #444; line-height: 1.7; margin-bottom: 10px; padding: 18px; background: #f8f9ff; border-radius: 12px; font-size: 14px; }
        .mensaje { padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 14px; }
        .mensaje-exito { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #4caf50; }
        .mensaje-error { background: #ffebee; color: #c62828; border-left: 4px solid #c62828; }
        .entrega-box { background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%); border: 2px solid #4caf50; border-radius: 16px; padding: 25px; margin-bottom: 20px; }
        .entrega-box h3 { color: #2e7d32; margin-bottom: 12px; font-size: 18px; }
        .entrega-box p { color: #333; font-size: 14px; margin-bottom: 8px; }
        .entrega-box a { display: inline-block; background: #1a237e; color: white; padding: 8px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; margin-top: 5px; transition: 0.3s; }
        .entrega-box a:hover { background: #0d1457; transform: translateY(-2px); }
        .btn-eliminar { display: inline-block; background: #dc3545; color: white; padding: 8px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; margin-left: 10px; transition: 0.3s; border: none; cursor: pointer; }
        .btn-eliminar:hover { background: #c82333; transform: translateY(-2px); }
        .form-subir { background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); }
        .form-subir h3 { color: #1a237e; margin-bottom: 15px; font-size: 18px; }
        .form-subir input[type="file"] { width: 100%; padding: 12px; border: 2px dashed #c5cae9; border-radius: 10px; margin-bottom: 12px; cursor: pointer; }
        .form-subir input[type="file"]:hover { border-color: #1a237e; }
        .info-archivo { color: #666; font-size: 12px; margin-bottom: 15px; }
        .btn-subir { background: #1a237e; color: white; padding: 12px 28px; border: none; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; transition: 0.3s; }
        .btn-subir:hover { background: #0d1457; transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="actividad-container">
        <a href="tareas/" class="btn-volver">← Volver a mis tareas</a>
        
        <?php if ($mensaje): ?>
            <div class="mensaje mensaje-<?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        
        <div class="actividad-card">
            <h2>📝 <?php echo htmlspecialchars($actividad['titulo']); ?></h2>
            <div class="meta">📚 <?php echo htmlspecialchars($actividad['curso_nombre']); ?> &nbsp;|&nbsp; 📅 Fecha límite: <?php echo $actividad['fecha_entrega'] ? date('d/m/Y H:i', strtotime($actividad['fecha_entrega'])) : 'Sin fecha'; ?></div>
            <?php if (!empty($actividad['descripcion'])): ?>
                <div class="desc"><?php echo nl2br(htmlspecialchars($actividad['descripcion'])); ?></div>
            <?php endif; ?>
        </div>
        
        <?php if ($entrega): ?>
            <div class="entrega-box">
                <h3>✅ Ya has entregado esta actividad</h3>
                <p><strong>📄 Archivo:</strong> <?php echo htmlspecialchars($entrega['nombre_archivo']); ?></p>
                <p><strong>📅 Fecha de entrega:</strong> <?php echo date('d/m/Y H:i', strtotime($entrega['fecha_entrega'])); ?></p>
                <div style="margin-top: 15px;">
                    <a href="<?php echo htmlspecialchars($entrega['ruta_archivo']); ?>" target="_blank">👁️ Ver mi archivo</a>
                    <a href="actividad.php?id=<?php echo $actividad_id; ?>&eliminar=1" class="btn-eliminar" onclick="return confirm('¿Estás seguro de eliminar tu entrega? Podrás subir otra después.')">🗑️ Eliminar entrega</a>
                </div>
                <p style="color: #666; font-size: 12px; margin-top: 15px;">💡 Puedes subir un nuevo archivo abajo para reemplazar el actual.</p>
            </div>
        <?php endif; ?>
        
        <div class="form-subir">
            <h3><?php echo $entrega ? '🔄 Subir nuevo archivo (reemplaza el anterior)' : '📤 Entregar archivo'; ?></h3>
            <form method="POST" action="subir_archivo.php" enctype="multipart/form-data">
                <input type="hidden" name="actividad_id" value="<?php echo $actividad_id; ?>">
                <input type="file" name="archivo" accept=".pdf,.doc,.docx" required>
                <div class="info-archivo">Formatos permitidos: PDF, DOC, DOCX. Tamaño máximo: 512 MB.</div>
                <button type="submit" class="btn-subir"><?php echo $entrega ? 'Reemplazar archivo' : 'Entregar ahora'; ?></button>
            </form>
        </div>
    </div>
</body>
</html>
