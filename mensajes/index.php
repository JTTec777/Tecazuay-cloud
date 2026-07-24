<?php
require_once '../config.php';
$titulo = 'Mensajes - TEC AZUAY';
$base_path = '..';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$rol = $_SESSION['user_rol'];

// Marcar mensaje como leído
if (isset($_GET['leer']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE mensajes SET leido = 1 WHERE id = ? AND destinatario_id = ?");
    $stmt->execute([$id, $user_id]);
}

// Obtener mensajes recibidos
$stmt = $pdo->prepare("
    SELECT m.*, u.nombre as remitente_nombre
    FROM mensajes m
    JOIN usuarios u ON m.remitente_id = u.id
    WHERE m.destinatario_id = ?
    ORDER BY m.fecha_envio DESC
");
$stmt->execute([$user_id]);
$recibidos = $stmt->fetchAll();

// Obtener mensajes enviados
$stmt = $pdo->prepare("
    SELECT m.*, u.nombre as destinatario_nombre
    FROM mensajes m
    JOIN usuarios u ON m.destinatario_id = u.id
    WHERE m.remitente_id = ?
    ORDER BY m.fecha_envio DESC
");
$stmt->execute([$user_id]);
$enviados = $stmt->fetchAll();
?>
<style>
    .tabs { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
    .tab-btn { padding: 8px 24px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.3s; font-size: 14px; }
    .tab-btn.active { background: #1a237e; color: white; }
    .tab-btn.inactive { background: #e8eaf6; color: #1a237e; }
    .tab-btn.inactive:hover { background: #d5d9e8; }
    .mensaje-item { padding: 12px 16px; border-bottom: 1px solid #f0f2f5; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
    .mensaje-item:last-child { border-bottom: none; }
    .mensaje-item .asunto { font-weight: 600; color: #1a237e; }
    .mensaje-item .de { color: #666; font-size: 13px; }
    .mensaje-item .fecha { color: #999; font-size: 12px; }
    .mensaje-item .no-leido { background: #e3f2fd; color: #0d47a1; padding: 2px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    .btn-mensaje { background: #1a237e; color: white; padding: 4px 14px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.3s; }
    .btn-mensaje:hover { background: #0d1457; }
    .btn-enviar { display: inline-block; background: #dcc97a; color: #1a237e; padding: 8px 24px; border-radius: 10px; text-decoration: none; font-weight: 700; margin-bottom: 15px; transition: 0.3s; }
    .btn-enviar:hover { background: #c4b15a; transform: translateY(-2px); }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .empty-message { color: #999; text-align: center; padding: 30px; font-size: 14px; }
</style>

<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
    <h2 style="color:#1a237e;">💬 Mensajes</h2>
    <a href="enviar.php" class="btn-enviar">✉️ Nuevo Mensaje</a>
</div>

<div class="tabs">
    <button class="tab-btn active" onclick="mostrarTab('recibidos')">📥 Recibidos (<?php echo count($recibidos); ?>)</button>
    <button class="tab-btn inactive" onclick="mostrarTab('enviados')">📤 Enviados (<?php echo count($enviados); ?>)</button>
</div>

<!-- Recibidos -->
<div id="tab-recibidos" class="tab-content active">
    <?php if (count($recibidos) == 0): ?>
        <div class="empty-message">📭 No tienes mensajes recibidos</div>
    <?php else: ?>
        <?php foreach($recibidos as $msg): ?>
            <div class="mensaje-item">
                <div>
                    <div class="asunto"><?php echo htmlspecialchars($msg['asunto']); ?></div>
                    <div class="de">👤 De: <?php echo htmlspecialchars($msg['remitente_nombre']); ?></div>
                    <div class="fecha">📅 <?php echo $msg['fecha_envio']; ?></div>
                </div>
                <div>
                    <?php if ($msg['leido'] == 0): ?>
                        <span class="no-leido">🔵 Nuevo</span>
                    <?php endif; ?>
                    <a href="leer.php?id=<?php echo $msg['id']; ?>" class="btn-mensaje">Ver</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Enviados -->
<div id="tab-enviados" class="tab-content">
    <?php if (count($enviados) == 0): ?>
        <div class="empty-message">📤 No has enviado mensajes</div>
    <?php else: ?>
        <?php foreach($enviados as $msg): ?>
            <div class="mensaje-item">
                <div>
                    <div class="asunto">📤 <?php echo htmlspecialchars($msg['asunto']); ?></div>
                    <div class="de">👤 Para: <?php echo htmlspecialchars($msg['destinatario_nombre']); ?></div>
                    <div class="fecha">📅 <?php echo $msg['fecha_envio']; ?></div>
                </div>
                <div>
                    <a href="leer.php?id=<?php echo $msg['id']; ?>" class="btn-mensaje">Ver</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function mostrarTab(tab) {
    // Ocultar todos los tabs
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.add('inactive'));
    
    // Mostrar el tab seleccionado
    document.getElementById('tab-' + tab).classList.add('active');
    // Marcar botón como activo
    const btns = document.querySelectorAll('.tab-btn');
    if (tab === 'recibidos') {
        btns[0].classList.remove('inactive');
        btns[0].classList.add('active');
    } else {
        btns[1].classList.remove('inactive');
        btns[1].classList.add('active');
    }
}
</script>

<?php include '../includes/footer.php'; ?>
