<?php
require_once '../config.php';
$titulo = 'Notificaciones - TEC AZUAY';
$base_path = '..';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Marcar todas como leídas
if (isset($_GET['marcar_todas'])) {
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = TRUE WHERE usuario_id = ?");
    $stmt->execute([$user_id]);
    header('Location: index.php');
    exit();
}

// Marcar una como leída
if (isset($_GET['leer']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = TRUE WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $user_id]);
    header('Location: index.php');
    exit();
}

// Obtener notificaciones
$stmt = $pdo->prepare("SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY fecha_creacion DESC");
$stmt->execute([$user_id]);
$notificaciones = $stmt->fetchAll();

$no_leidas = 0;
foreach ($notificaciones as $n) {
    if (!$n['leida'] || $n['leida'] === 'f' || $n['leida'] == 0) $no_leidas++;
}
?>
<style>
    .notificacion-item { padding: 12px 16px; border-bottom: 1px solid #f0f2f5; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
    .notificacion-item:last-child { border-bottom: none; }
    .notificacion-item.no-leida { background: #f8f9ff; border-left: 4px solid #1a237e; }
    .notificacion-item .titulo { font-weight: 600; color: #1a237e; }
    .notificacion-item .mensaje { color: #444; font-size: 14px; }
    .notificacion-item .fecha { color: #999; font-size: 12px; }
    .notificacion-item .tipo { display: inline-block; padding: 2px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; margin-left: 10px; }
    .tipo-tarea { background: #e3f2fd; color: #0d47a1; }
    .tipo-recordatorio { background: #fff3e0; color: #e65100; }
    .tipo-mensaje { background: #e8f5e9; color: #2e7d32; }
    .tipo-anuncio { background: #fce4ec; color: #c62828; }
    .tipo-calificacion { background: #f3e5f5; color: #6a1b9a; }
    .btn-notificacion { background: #1a237e; color: white; padding: 4px 14px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.3s; }
    .btn-notificacion:hover { background: #0d1457; }
    .btn-marcar-todas { background: #dcc97a; color: #1a237e; padding: 6px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; font-size: 13px; }
    .btn-marcar-todas:hover { background: #c4b15a; }
    .empty-message { color: #999; text-align: center; padding: 40px; font-size: 16px; }
</style>

<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
    <h2 style="color:#1a237e;">🔔 Notificaciones <?php if ($no_leidas > 0): ?><span style="background:#dc3545; color:white; font-size:12px; padding:2px 12px; border-radius:12px; margin-left:8px;"><?php echo $no_leidas; ?> nuevas</span><?php endif; ?></h2>
    <?php if ($no_leidas > 0): ?>
        <form method="GET" style="display:inline;">
            <button type="submit" name="marcar_todas" class="btn-marcar-todas">✅ Marcar todas como leídas</button>
        </form>
    <?php endif; ?>
</div>

<?php if (count($notificaciones) == 0): ?>
    <div class="empty-message">📭 No tienes notificaciones</div>
<?php else: ?>
    <?php foreach($notificaciones as $notif): ?>
        <div class="notificacion-item <?php if (!$notif['leida'] || $notif['leida'] === 'f' || $notif['leida'] == 0) echo 'no-leida'; ?>">
            <div>
                <div class="titulo">
                    <?php echo htmlspecialchars($notif['titulo']); ?>
                    <span class="tipo tipo-<?php echo $notif['tipo']; ?>"><?php echo $notif['tipo']; ?></span>
                </div>
                <div class="mensaje"><?php echo htmlspecialchars($notif['mensaje']); ?></div>
                <div class="fecha">📅 <?php echo $notif['fecha_creacion']; ?></div>
            </div>
            <div>
                <?php if (!$notif['leida'] || $notif['leida'] === 'f' || $notif['leida'] == 0): ?>
                    <a href="index.php?leer=1&id=<?php echo $notif['id']; ?>" class="btn-notificacion">Marcar leída</a>
                <?php else: ?>
                    <span style="color:#999; font-size:12px;">✅ Leída</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
