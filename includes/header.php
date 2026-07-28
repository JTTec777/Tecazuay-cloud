<?php
require_once dirname(__DIR__) . '/config.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . (dirname($_SERVER['SCRIPT_NAME']) === '/' ? '' : '..') . '/index.php');
    exit();
}

// Asegurar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$rol = $_SESSION['user_rol'];
$nombre_usuario = strtoupper($_SESSION['user_nombre']);
$user_id = $_SESSION['user_id'];

// Contar notificaciones no leídas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND leida = FALSE");
$stmt->execute([$user_id]);
$notificaciones_no_leidas = $stmt->fetchColumn();

// Contar mensajes no leídos
$stmt = $pdo->prepare("SELECT COUNT(*) FROM mensajes WHERE destinatario_id = ? AND leido = FALSE");
$stmt->execute([$user_id]);
$mensajes_no_leidos = $stmt->fetchColumn();

// Determinar ruta base
$base_path = '';
if (strpos($_SERVER['SCRIPT_NAME'], '/cursos/') !== false) $base_path = '..';
elseif (strpos($_SERVER['SCRIPT_NAME'], '/calificaciones/') !== false) $base_path = '..';
elseif (strpos($_SERVER['SCRIPT_NAME'], '/mensajes/') !== false) $base_path = '..';
elseif (strpos($_SERVER['SCRIPT_NAME'], '/notificaciones/') !== false) $base_path = '..';
elseif (strpos($_SERVER['SCRIPT_NAME'], '/anuncios/') !== false) $base_path = '..';
elseif (strpos($_SERVER['SCRIPT_NAME'], '/tareas/') !== false) $base_path = '..';
elseif (strpos($_SERVER['SCRIPT_NAME'], '/includes/') !== false) $base_path = '..';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo) ? $titulo : 'TEC AZUAY'; ?></title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Inter', sans-serif; }

        .header {
            background: white;
            padding: 12px 24px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(26, 35, 126, 0.08);
            margin-bottom: 25px;
            border-left: 4px solid #dcc97a;
            flex-wrap: wrap;
            gap: 10px;
        }
        .header-left h1 { color: #1a237e; font-size: 20px; font-weight: 800; }
        .header-left .subtitle { color: #888; font-size: 10px; letter-spacing: 2px; margin-left: 8px; font-weight: 600; }
        .header-right { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .user-name { color: #1a237e; font-weight: 700; font-size: 12px; }
        .header-link { color: #1a237e; text-decoration: none; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 8px; transition: 0.3s; position: relative; }
        .header-link:hover { background: #e8eaf6; }
        .header-link .badge { position: absolute; top: -4px; right: -4px; background: #dc3545; color: white; font-size: 9px; font-weight: 700; padding: 2px 5px; border-radius: 50%; min-width: 16px; text-align: center; }
        .btn-logout { background: #dc3545; color: white; padding: 4px 14px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 600; transition: 0.3s; }
        .btn-logout:hover { background: #c82333; transform: translateY(-2px); }

        .container { max-width: 1300px; margin: 0 auto; padding: 15px; }

        .btn-primary { background: #1a237e; color: white; padding: 8px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; font-size: 13px; }
        .btn-primary:hover { background: #0d1457; transform: translateY(-2px); }

        .btn-secondary { background: #e8eaf6; color: #1a237e; padding: 8px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; font-size: 13px; }
        .btn-secondary:hover { background: #d5d9e8; transform: translateY(-2px); }

        .btn-success { background: #4caf50; color: white; padding: 8px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; font-size: 13px; }
        .btn-success:hover { background: #388e3c; transform: translateY(-2px); }

        .btn-warning { background: #dcc97a; color: #1a237e; padding: 8px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; font-size: 13px; }
        .btn-warning:hover { background: #c4b15a; transform: translateY(-2px); }

        .btn-danger { background: #dc3545; color: white; padding: 8px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; font-size: 13px; }
        .btn-danger:hover { background: #c82333; transform: translateY(-2px); }

        .card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); border: 1px solid rgba(26,35,126,0.04); margin-bottom: 15px; }

        @media (max-width: 768px) {
            .header { flex-direction: column; text-align: center; padding: 12px 14px; }
            .header-right { justify-content: center; }
            .container { padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <h1>TEC AZUAY <span class="subtitle">INSTITUTO UNIVERSITARIO</span></h1>
            </div>
            <div class="header-right">
                <span class="user-name">👋 <?php echo $nombre_usuario; ?></span>
                <a href="<?php echo $base_path; ?>/cursos/" class="header-link">📚 Cursos</a>
                <?php if ($rol == 'estudiante'): ?>
                    <a href="<?php echo $base_path; ?>/tareas/" class="header-link">📋 Tareas</a>
                    <a href="<?php echo $base_path; ?>/calificaciones/ver.php" class="header-link">📊 Calificaciones</a>
                <?php endif; ?>               
                <?php if ($rol == 'profesor'): ?>
                    <a href="<?php echo $base_path; ?>/panel_profesor_tareas.php" class="header-link">📊 Calificar</a>
                <?php endif; ?>
                <a href="<?php echo $base_path; ?>/mensajes/" class="header-link">
                    💬 Mensajes
                    <?php if ($mensajes_no_leidos > 0): ?>
                        <span class="badge"><?php echo $mensajes_no_leidos; ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo $base_path; ?>/notificaciones/" class="header-link">
                    🔔 Notificaciones
                    <?php if ($notificaciones_no_leidas > 0): ?>
                        <span class="badge"><?php echo $notificaciones_no_leidas; ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo $base_path; ?>/seguridad.php" class="header-link">🔐 Seguridad</a>
                <a href="<?php echo $base_path; ?>/anuncios/" class="header-link">📢 Anuncios</a>
                <a href="<?php echo $base_path; ?>/dashboard_<?php echo $rol; ?>.php" class="header-link">🏠 Inicio</a>
                <a href="<?php echo $base_path; ?>/logout.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>
    <!-- NO CERRAMOS container, body ni html aquí. Se cierra en footer.php -->
