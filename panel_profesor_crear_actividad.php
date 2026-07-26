<?php
require_once 'config.php';
$titulo = 'Crear Actividad - TEC AZUAY';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'profesor') {
    header('Location: index.php');
    exit();
}

$error = '';
$exito = '';

// Obtener cursos
$stmt = $pdo->query("SELECT id, nombre FROM cursos WHERE activo = TRUE ORDER BY nombre");
$cursos = $stmt->fetchAll();

// Procesar creación
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $curso_id = (int)$_POST['curso_id'];
    $titulo_act = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $fecha_entrega = $_POST['fecha_entrega'];
    
    if ($curso_id > 0 && !empty($titulo_act) && !empty($fecha_entrega)) {
        $stmt = $pdo->prepare("INSERT INTO actividades (curso_id, titulo, descripcion, fecha_entrega) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$curso_id, $titulo_act, $descripcion, $fecha_entrega])) {
            // Notificar a estudiantes del curso
            $stmt = $pdo->prepare("SELECT estudiante_id FROM inscripciones WHERE curso_id = ?");
            $stmt->execute([$curso_id]);
            $estudiantes = $stmt->fetchAll();
            foreach ($estudiantes as $est) {
                $stmt = $pdo->prepare("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $est['estudiante_id'],
                    '📝 Nueva actividad',
                    'Se ha publicado una nueva actividad: "' . $titulo_act . '"',
                    'tarea'
                ]);
            }
            header('Location: panel_profesor_mis_actividades.php?exito=1');
            exit();
        } else {
            $error = '❌ Error al crear la actividad';
        }
    } else {
        $error = '❌ Curso, título y fecha de entrega son obligatorios';
    }
}
?>
<style>
    .form-actividad { background: white; border-radius: 16px; padding: 30px; max-width: 700px; margin: 0 auto; box-shadow: 0 4px 20px rgba(26,35,126,0.08); }
    .form-actividad h2 { color: #1a237e; margin-bottom: 20px; font-size: 22px; }
    .form-actividad label { display: block; color: #1a237e; font-weight: 600; margin-bottom: 6px; font-size: 13px; }
    .form-actividad input, .form-actividad select, .form-actividad textarea { width: 100%; padding: 12px 14px; border: 2px solid #e8ecf5; border-radius: 10px; font-size: 14px; margin-bottom: 16px; transition: 0.3s; font-family: 'Inter', sans-serif; }
    .form-actividad input:focus, .form-actividad select:focus, .form-actividad textarea:focus { border-color: #1a237e; outline: none; box-shadow: 0 0 0 4px rgba(26,35,126,0.08); }
    .form-actividad textarea { min-height: 120px; resize: vertical; }
    .btn-guardar { background: #4caf50; color: white; padding: 12px 30px; border: none; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; transition: 0.3s; }
    .btn-guardar:hover { background: #388e3c; transform: translateY(-2px); }
    .btn-volver { background: #e8eaf6; color: #1a237e; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px; display: inline-block; margin-left: 10px; transition: 0.3s; }
    .btn-volver:hover { background: #d5d9e8; }
    .mensaje-error { background: #ffebee; color: #c62828; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-weight: 600; border-left: 4px solid #c62828; }
</style>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
    <h2 style="color:#1a237e;">📝 Crear Nueva Actividad</h2>
    <a href="panel_profesor_mis_actividades.php" class="btn-volver">← Ver mis actividades</a>
</div>

<?php if ($error): ?>
    <div class="mensaje-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="form-actividad">
    <form method="POST">
        <label for="curso_id">Curso:</label>
        <select id="curso_id" name="curso_id" required>
            <option value="">Selecciona un curso</option>
            <?php foreach($cursos as $curso): ?>
                <option value="<?php echo $curso['id']; ?>"><?php echo htmlspecialchars($curso['nombre']); ?></option>
            <?php endforeach; ?>
        </select>
        
        <label for="titulo">Título de la actividad:</label>
        <input type="text" id="titulo" name="titulo" required placeholder="Ej: Guía Práctica Unidad 3">
        
        <label for="descripcion">Descripción / Instrucciones:</label>
        <textarea id="descripcion" name="descripcion" placeholder="Describe lo que deben hacer los estudiantes..."></textarea>
        
        <label for="fecha_entrega">Fecha y hora de entrega:</label>
        <input type="datetime-local" id="fecha_entrega" name="fecha_entrega" required>
        
        <button type="submit" class="btn-guardar">💾 Crear Actividad</button>
        <a href="dashboard_profesor.php" class="btn-volver">Cancelar</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
