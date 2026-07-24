<?php
require_once '../config.php';
$titulo = 'Crear Curso - TEC AZUAY';
$base_path = '..';
include '../includes/header.php';

if ($_SESSION['user_rol'] != 'profesor') {
    header('Location: ../index.php');
    exit();
}

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $profesor_id = $_SESSION['user_id'];
    
    if (!empty($nombre)) {
        $stmt = $pdo->prepare("INSERT INTO cursos (nombre, descripcion, profesor_id) VALUES (?, ?, ?)");
        if ($stmt->execute([$nombre, $descripcion, $profesor_id])) {
            $mensaje = '✅ Curso creado correctamente';
            
            // Notificar a todos los estudiantes
            $stmt = $pdo->query("SELECT id FROM usuarios WHERE rol_id = 1");
            $estudiantes = $stmt->fetchAll();
            foreach ($estudiantes as $est) {
                $stmt2 = $pdo->prepare("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES (?, ?, ?, ?)");
                $stmt2->execute([
                    $est['id'],
                    '📚 Nuevo curso disponible',
                    'Se ha creado un nuevo curso: ' . $nombre,
                    'tarea'
                ]);
            }
        } else {
            $mensaje = '❌ Error al crear el curso';
        }
    } else {
        $mensaje = '❌ El nombre del curso es obligatorio';
    }
}
?>
<style>
    .form-curso { background: white; border-radius: 16px; padding: 25px; max-width: 550px; margin: 0 auto; box-shadow: 0 4px 20px rgba(26,35,126,0.06); }
    .form-curso label { display: block; color: #1a237e; font-weight: 600; margin-bottom: 4px; font-size: 13px; }
    .form-curso input, .form-curso textarea { width: 100%; padding: 10px 14px; border: 2px solid #e8ecf5; border-radius: 10px; font-size: 14px; margin-bottom: 14px; transition: 0.3s; font-family: 'Inter', sans-serif; }
    .form-curso input:focus, .form-curso textarea:focus { border-color: #1a237e; outline: none; box-shadow: 0 0 0 4px rgba(26,35,126,0.08); }
    .form-curso textarea { min-height: 100px; resize: vertical; }
    .mensaje-flotante { padding: 10px 14px; border-radius: 10px; margin-bottom: 14px; font-weight: 600; }
    .mensaje-exito { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
    .mensaje-error { background: #ffebee; color: #c62828; border-left: 4px solid #c62828; }
</style>

<div class="form-curso">
    <h2 style="color:#1a237e; margin-bottom:16px;">➕ Crear Nuevo Curso</h2>
    
    <?php if ($mensaje): ?>
        <div class="mensaje-flotante <?php echo strpos($mensaje, '✅') !== false ? 'mensaje-exito' : 'mensaje-error'; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <label for="nombre">Nombre del curso *</label>
        <input type="text" id="nombre" name="nombre" required placeholder="Ej: Introducción a la Programación">
        
        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" placeholder="Describe el contenido del curso..."></textarea>
        
        <button type="submit" class="btn-primary">Crear Curso</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
