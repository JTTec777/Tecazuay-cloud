<?php
require_once '../config.php';
$titulo = 'Anuncios - TEC AZUAY';
$base_path = '..';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$rol = $_SESSION['user_rol'];

// Crear anuncio (solo profesor)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $rol == 'profesor') {
    $titulo_anuncio = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    
    if (!empty($titulo_anuncio) && !empty($contenido)) {
        $stmt = $pdo->prepare("INSERT INTO anuncios (titulo, contenido, creado_por) VALUES (?, ?, ?)");
        $stmt->execute([$titulo_anuncio, $contenido, $user_id]);
        
        // Notificar a todos los estudiantes
        $stmt = $pdo->query("SELECT id FROM usuarios WHERE rol_id = 1");
        $estudiantes = $stmt->fetchAll();
        foreach ($estudiantes as $est) {
            $stmt2 = $pdo->prepare("INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo) VALUES (?, ?, ?, ?)");
            $stmt2->execute([
                $est['id'],
                '📢 Nuevo anuncio',
                $titulo_anuncio,
                'anuncio'
            ]);
        }
        header('Location: index.php?creado=1');
        exit();
    }
}

// Obtener anuncios
$stmt = $pdo->prepare("
    SELECT a.*, u.nombre as autor_nombre
    FROM anuncios a
    JOIN usuarios u ON a.creado_por = u.id
    WHERE a.activo = 1
    ORDER BY a.fecha_publicacion DESC
");
$stmt->execute();
$anuncios = $stmt->fetchAll();

$creado = isset($_GET['creado']);
?>
<style>
    .anuncio-item { background: white; border-radius: 16px; padding: 20px; margin-bottom: 15px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); border: 1px solid rgba(26,35,126,0.04); border-left: 4px solid #dcc97a; }
    .anuncio-item .titulo { color: #1a237e; font-size: 18px; font-weight: 700; }
    .anuncio-item .autor { color: #666; font-size: 13px; }
    .anuncio-item .fecha { color: #999; font-size: 12px; }
    .anuncio-item .contenido { color: #444; font-size: 14px; line-height: 1.7; margin-top: 10px; padding-top: 10px; border-top: 1px solid #f0f2f5; white-space: pre-wrap; }
    .form-anuncio { background: white; border-radius: 16px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); border: 1px solid rgba(26,35,126,0.04); }
    .form-anuncio h3 { color: #1a237e; margin-bottom: 12px; }
    .form-anuncio input, .form-anuncio textarea { width: 100%; padding: 10px 14px; border: 2px solid #e8ecf5; border-radius: 10px; font-size: 14px; margin-bottom: 10px; font-family: 'Inter', sans-serif; }
    .form-anuncio input:focus, .form-anuncio textarea:focus { border-color: #1a237e; outline: none; box-shadow: 0 0 0 4px rgba(26,35,126,0.08); }
    .form-anuncio textarea { min-height: 80px; resize: vertical; }
    .empty-message { color: #999; text-align: center; padding: 40px; font-size: 16px; }
    .mensaje-flotante { background: #e8f5e9; color: #2e7d32; padding: 10px 16px; border-radius: 10px; margin-bottom: 15px; font-weight: 600; border-left: 4px solid #2e7d32; }
</style>

<?php if ($creado): ?>
    <div class="mensaje-flotante">✅ Anuncio creado correctamente</div>
<?php endif; ?>

<?php if ($rol == 'profesor'): ?>
    <div class="form-anuncio">
        <h3>📢 Crear Anuncio</h3>
        <form method="POST">
            <input type="text" name="titulo" placeholder="Título del anuncio" required>
            <textarea name="contenido" placeholder="Contenido del anuncio..." required></textarea>
            <button type="submit" class="btn-primary">Publicar Anuncio</button>
        </form>
    </div>
<?php endif; ?>

<h2 style="color:#1a237e; margin-bottom:15px;">📢 Anuncios</h2>

<?php if (count($anuncios) == 0): ?>
    <div class="empty-message">📭 No hay anuncios publicados</div>
<?php else: ?>
    <?php foreach($anuncios as $anuncio): ?>
        <div class="anuncio-item">
            <div class="titulo">📢 <?php echo htmlspecialchars($anuncio['titulo']); ?></div>
            <div class="autor">👤 Por: <?php echo htmlspecialchars($anuncio['autor_nombre']); ?></div>
            <div class="fecha">📅 <?php echo $anuncio['fecha_publicacion']; ?></div>
            <div class="contenido"><?php echo nl2br(htmlspecialchars($anuncio['contenido'])); ?></div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
