<?php
require_once '../config.php';
$titulo = 'Cursos - TEC AZUAY';
$base_path = '..';
include '../includes/header.php';

$rol = $_SESSION['user_rol'];
$user_id = $_SESSION['user_id'];

if ($rol == 'estudiante') {
    // Ver cursos del estudiante
    $stmt = $pdo->prepare("
        SELECT c.*, u.nombre as profesor_nombre
        FROM cursos c
        JOIN inscripciones i ON c.id = i.curso_id
        JOIN usuarios u ON c.profesor_id = u.id
        WHERE i.estudiante_id = ?
        ORDER BY c.nombre
    ");
    $stmt->execute([$user_id]);
} else {
    // Ver todos los cursos (profesor)
    $stmt = $pdo->prepare("
        SELECT c.*, u.nombre as profesor_nombre,
        (SELECT COUNT(*) FROM inscripciones WHERE curso_id = c.id) as estudiantes_count
        FROM cursos c
        JOIN usuarios u ON c.profesor_id = u.id
        ORDER BY c.nombre
    ");
    $stmt->execute();
}
$cursos = $stmt->fetchAll();
?>
<style>
    .cursos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
    .curso-card { background: white; border-radius: 16px; padding: 22px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); border: 1px solid rgba(26,35,126,0.04); transition: 0.3s; }
    .curso-card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(26,35,126,0.12); }
    .curso-card .curso-nombre { color: #1a237e; font-size: 17px; font-weight: 700; margin-bottom: 6px; }
    .curso-card .curso-profesor { color: #666; font-size: 13px; margin-bottom: 8px; }
    .curso-card .curso-desc { color: #444; font-size: 13px; line-height: 1.5; margin-bottom: 12px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .curso-card .curso-meta { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e8eaf6; padding-top: 10px; }
    .curso-card .curso-meta .estudiantes { color: #666; font-size: 12px; }
    .btn-curso { background: #1a237e; color: white; padding: 6px 16px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.3s; }
    .btn-curso:hover { background: #0d1457; transform: scale(1.05); }
    .btn-crear { display: inline-block; background: #dcc97a; color: #1a237e; padding: 8px 24px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 14px; transition: 0.3s; }
    .btn-crear:hover { background: #c4b15a; transform: translateY(-2px); }
    .empty-message { color: #999; text-align: center; padding: 40px; font-size: 16px; }
</style>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
    <h2 style="color:#1a237e;">📚 Mis Cursos</h2>
    <?php if ($rol == 'profesor'): ?>
        <a href="crear.php" class="btn-crear">➕ Crear Curso</a>
    <?php endif; ?>
</div>

<?php if (count($cursos) == 0): ?>
    <div class="empty-message">
        <?php if ($rol == 'estudiante'): ?>
            📭 No estás inscrito en ningún curso aún. 
            <br><small style="color:#aaa;">Pide a tu profesor que te inscriba o busca cursos disponibles.</small>
        <?php else: ?>
            📭 No has creado ningún curso aún.
            <br><small style="color:#aaa;">Haz clic en "Crear Curso" para comenzar.</small>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="cursos-grid">
        <?php foreach($cursos as $curso): ?>
            <div class="curso-card">
                <div class="curso-nombre">📘 <?php echo htmlspecialchars($curso['nombre']); ?></div>
                <div class="curso-profesor">👨‍🏫 <?php echo htmlspecialchars($curso['profesor_nombre']); ?></div>
                <div class="curso-desc"><?php echo htmlspecialchars($curso['descripcion'] ?: 'Sin descripción'); ?></div>
                <div class="curso-meta">
                    <span class="estudiantes">👥 <?php echo isset($curso['estudiantes_count']) ? $curso['estudiantes_count'] : '0'; ?> estudiantes</span>
                    <a href="detalle.php?id=<?php echo $curso['id']; ?>" class="btn-curso">Ver Curso</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
