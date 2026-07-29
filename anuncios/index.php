<?php
require_once '../config.php';
$titulo = 'Anuncios - TEC AZUAY';
$base_path = '..';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Obtener anuncios activos
$stmt = $pdo->query("
    SELECT a.*, u.nombre as autor_nombre 
    FROM anuncios a 
    JOIN usuarios u ON a.creado_por = u.id 
    WHERE a.activo = TRUE 
    ORDER BY a.fecha_publicacion DESC
");
$anuncios = $stmt->fetchAll();
?>
<style>
    .anuncio-card { background: white; border-radius: 16px; padding: 20px; margin-bottom: 15px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); border-left: 4px solid #1a237e; }
    .anuncio-card h3 { color: #1a237e; margin-bottom: 8px; font-size: 18px; }
    .anuncio-card p { color: #444; font-size: 14px; line-height: 1.6; margin-bottom: 10px; }
    .anuncio-meta { color: #888; font-size: 12px; font-weight: 600; }
    .empty-state { text-align: center; padding: 60px 20px; color: #999; }
    .empty-state h3 { color: #1a237e; margin-bottom: 8px; }
</style>

<h2 style="color:#1a237e; margin-bottom:20px;">📢 Anuncios Institucionales</h2>

<?php if (count($anuncios) == 0): ?>
    <div class="empty-state">
        <h3>📭 No hay anuncios</h3>
        <p>Aún no se han publicado anuncios.</p>
    </div>
<?php else: ?>
    <?php foreach($anuncios as $anuncio): ?>
        <div class="anuncio-card">
            <h3><?php echo htmlspecialchars($anuncio['titulo']); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($anuncio['contenido'])); ?></p>
            <div class="anuncio-meta">
                👤 <?php echo htmlspecialchars($anuncio['autor_nombre']); ?> &nbsp;|&nbsp; 
                📅 <?php echo date('d/m/Y H:i', strtotime($anuncio['fecha_publicacion'])); ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
