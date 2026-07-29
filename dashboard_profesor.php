<?php
require_once 'config.php';
require_once 'languages.php';

// Verificación de rol (case-insensitive para evitar problemas)
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_rol']) !== 'profesor') {
    header('Location: index.php');
    exit();
}

// Obtener todos los cursos
$stmt_cursos = $pdo->query("SELECT id, nombre, descripcion, activo FROM cursos ORDER BY id");
$cursos = $stmt_cursos->fetchAll();

// Obtener todos los estudiantes
$stmt = $pdo->query("SELECT id, nombre, usuario, contrasena FROM usuarios WHERE rol_id = 1 ORDER BY nombre");
$estudiantes = $stmt->fetchAll();

// Obtener profesores
$stmt2 = $pdo->query("SELECT nombre, usuario FROM usuarios WHERE rol_id = 2 ORDER BY nombre");
$profesores = $stmt2->fetchAll();

// Contar entregas pendientes
$stmt_pendientes = $pdo->query("
    SELECT COUNT(*) FROM entregas e
    LEFT JOIN calificaciones c ON c.entrega_id = e.id
    WHERE c.id IS NULL
");
$pendientes = $stmt_pendientes->fetchColumn();

// Procesar cambio de contraseña
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_pass'])) {
    $user_id = (int)$_POST['user_id'];
    $nueva_pass = $_POST['nueva_contrasena'];
    
    if (!empty($nueva_pass)) {
        $hash = password_hash($nueva_pass, PASSWORD_DEFAULT, ['cost' => 12]);
        $stmt3 = $pdo->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ? AND rol_id = 1");
        if ($stmt3->execute([$hash, $user_id])) {
            $mensaje = t('password_updated');
            $stmt = $pdo->query("SELECT id, nombre, usuario, contrasena FROM usuarios WHERE rol_id = 1 ORDER BY nombre");
            $estudiantes = $stmt->fetchAll();
        } else {
            $mensaje = t('password_error');
        }
    } else {
        $mensaje = t('password_empty');
    }
}

$nombre_usuario = strtoupper($_SESSION['user_nombre']);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Profesor - TEC AZUAY</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-container { max-width: 1300px; margin: 0 auto; padding: 20px; }
        .dashboard-header {
            background: white; padding: 20px 30px; border-radius: 16px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 4px 20px rgba(26,35,126,0.08); margin-bottom: 25px;
            border-left: 4px solid #dcc97a; flex-wrap: wrap; gap: 15px;
        }
        .header-left h1 { color: #1a237e; font-size: 24px; font-weight: 800; }
        .header-left .subtitle { color: #888; font-size: 11px; letter-spacing: 2px; font-weight: 600; }
        .header-right { display: flex; align-items: center; gap: 15px; }
        .user-name { color: #1a237e; font-weight: 700; font-size: 14px; }
        .btn-logout { background: #dc3545; color: white; padding: 8px 20px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 13px; transition: 0.3s; }
        .btn-logout:hover { background: #c82333; transform: translateY(-2px); }
        .botones-container { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 25px; }
        .btn-calendar { display: inline-block; background: #1a237e; color: white; padding: 12px 32px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 15px; transition: 0.3s; box-shadow: 0 4px 16px rgba(26,35,126,0.25); margin-bottom: 25px; }
        .btn-calendar:hover { background: #0d1457; transform: translateY(-3px); }
        .btn-entregas { display: inline-block; background: #dcc97a; color: #1a237e; padding: 12px 32px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 15px; transition: 0.3s; box-shadow: 0 4px 16px rgba(220,201,122,0.3); margin-bottom: 25px; }
        .btn-entregas:hover { background: #c4b15a; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(220,201,122,0.5); }
        .btn-actividades { display: inline-block; background: #4caf50; color: white; padding: 12px 32px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 15px; transition: 0.3s; box-shadow: 0 4px 16px rgba(76,175,80,0.3); margin-bottom: 25px; }
        .btn-actividades:hover { background: #388e3c; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(76,175,80,0.5); }
        .btn-crear { display: inline-block; background: #ff9800; color: white; padding: 12px 32px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 15px; transition: 0.3s; box-shadow: 0 4px 16px rgba(255,152,0,0.3); margin-bottom: 25px; }
        .btn-crear:hover { background: #f57c00; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(255,152,0,0.5); }
        .btn-cursos { display: inline-block; background: #2196f3; color: white; padding: 12px 32px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 15px; transition: 0.3s; box-shadow: 0 4px 16px rgba(33,150,243,0.3); margin-bottom: 25px; }
        .btn-cursos:hover { background: #1976d2; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(33,150,243,0.5); }
        .btn-mensajes { display: inline-block; background: #9c27b0; color: white; padding: 12px 32px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 15px; transition: 0.3s; box-shadow: 0 4px 16px rgba(156,39,176,0.3); margin-bottom: 25px; }
        .btn-mensajes:hover { background: #7b1fa2; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(156,39,176,0.5); }
        .btn-notificaciones { display: inline-block; background: #e91e63; color: white; padding: 12px 32px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 15px; transition: 0.3s; box-shadow: 0 4px 16px rgba(233,30,99,0.3); margin-bottom: 25px; }
        .btn-notificaciones:hover { background: #c2185b; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(233,30,99,0.5); }
        .btn-anuncios { display: inline-block; background: #00bcd4; color: white; padding: 12px 32px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 15px; transition: 0.3s; box-shadow: 0 4px 16px rgba(0,188,212,0.3); margin-bottom: 25px; }
        .btn-anuncios:hover { background: #0097a7; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,188,212,0.5); }
        .btn-seguridad { display: inline-block; background: #607d8b; color: white; padding: 12px 32px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 15px; transition: 0.3s; box-shadow: 0 4px 16px rgba(96,125,139,0.3); margin-bottom: 25px; }
        .btn-seguridad:hover { background: #455a64; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(96,125,139,0.5); }
        .welcome-banner {
            background: linear-gradient(135deg, #1a237e 0%, #0d1457 100%);
            color: white; padding: 25px 30px; border-radius: 16px;
            margin-bottom: 25px; box-shadow: 0 8px 30px rgba(26,35,126,0.25);
        }
        .welcome-banner h2 { font-size: 22px; font-weight: 700; }
        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px; margin-bottom: 25px;
        }
        .stat-card {
            background: white; border-radius: 16px; padding: 20px;
            box-shadow: 0 4px 20px rgba(26,35,126,0.06); text-align: center;
            border-left: 4px solid #dcc97a;
        }
        .stat-card h3 { color: #1a237e; font-size: 28px; margin-bottom: 4px; }
        .stat-card p { color: #888; font-size: 13px; font-weight: 600; }
        .cursos-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px; margin-bottom: 25px;
        }
        .curso-card {
            background: white; border-radius: 16px; padding: 20px;
            box-shadow: 0 4px 20px rgba(26,35,126,0.06); border-left: 4px solid #1a237e;
        }
        .curso-card h3 { color: #1a237e; font-size: 16px; margin-bottom: 8px; }
        .curso-card p { color: #666; font-size: 13px; margin-bottom: 12px; }
        .curso-card .badge {
            display: inline-block; padding: 4px 12px; border-radius: 12px;
            font-size: 11px; font-weight: 700;
        }
        .badge-activo { background: #e8f5e9; color: #2e7d32; }
        .badge-inactivo { background: #ffebee; color: #c62828; }
        .card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #f0f2f5; font-size: 13px; }
        th { color: #1a237e; font-weight: 700; background: #f8f9ff; }
        .form-cambiar-pass { display: flex; gap: 8px; align-items: center; }
        .input-pass { padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; }
        .btn-cambiar { background: #1a237e; color: white; padding: 6px 14px; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; }
        .mensaje-flotante { background: #e8f5e9; color: #2e7d32; padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; }
        .login-footer { text-align: center; margin-top: 30px; padding: 20px; color: #888; font-size: 12px; }
        .language-select { padding: 8px 14px; border-radius: 8px; border: 1px solid #ddd; font-family: 'Inter', sans-serif; font-size: 13px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="header-left">
                <h1>TEC AZUAY</h1>
                <span class="subtitle"><?php echo t('institute'); ?></span>
            </div>
            <div class="header-right">
                <span class="user-name">👨🏫 <?php echo $nombre_usuario; ?></span>
                <a href="logout.php?csrf_token=<?php echo generarTokenCSRF(); ?>" class="btn-logout"><?php echo t('logout'); ?></a>
            </div>
        </header>

        <div class="botones-container">
            <a href="calendar.php" class="btn-calendar">📅 Calendario</a>
            <a href="panel_profesor_tareas.php" class="btn-entregas">📋 Entregas</a>
            <a href="panel_profesor_mis_actividades.php" class="btn-actividades">📝 Mis Actividades</a>
            <a href="panel_profesor_crear_actividad.php" class="btn-crear">➕ Crear Tarea</a>
            <a href="cursos/" class="btn-cursos">📚 Cursos</a>
            <a href="mensajes/" class="btn-mensajes">💬 Mensajes</a>
            <a href="notificaciones/" class="btn-notificaciones">🔔 Notificaciones</a>
            <a href="anuncios/" class="btn-anuncios">📢 Anuncios</a>
            <a href="seguridad.php" class="btn-seguridad">🔐 Seguridad</a>
        </div>

        <div class="welcome-banner">
            <h2><?php echo t('welcome_professor'); ?> <?php echo $nombre_usuario; ?>! 📚</h2>
        </div>

        <?php if($mensaje): ?>
            <div class="mensaje-flotante"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo count($cursos); ?></h3>
                <p>CURSOS TOTALES</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($estudiantes); ?></h3>
                <p>ESTUDIANTES</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $pendientes; ?></h3>
                <p>ENTREGAS PENDIENTES</p>
            </div>
        </div>

        <h2 style="color:#1a237e; margin-bottom:15px;">📚 Cursos Disponibles</h2>
        <div class="cursos-grid">
            <?php foreach($cursos as $curso): ?>
                <div class="curso-card">
                    <h3><?php echo htmlspecialchars($curso['nombre']); ?></h3>
                    <p><?php echo htmlspecialchars($curso['descripcion']); ?></p>
                    <span class="badge <?php echo $curso['activo'] ? 'badge-activo' : 'badge-inactivo'; ?>">
                        <?php echo $curso['activo'] ? '✅ Activo' : '❌ Inactivo'; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="main-content">
            <div class="card">
                <h2>👨🏫 <?php echo t('professors_list'); ?></h2>
                <table>
                    <thead><tr><th><?php echo t('username'); ?></th></tr></thead>
                    <tbody>
                        <?php foreach($profesores as $prof): ?>
                        <tr><td><?php echo $prof['nombre']; ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h2>📚 <?php echo t('students_list'); ?></h2>
                <table>
                    <thead>
                        <tr>
                            <th><?php echo t('username'); ?></th>
                            <th><?php echo t('password'); ?></th>
                            <th><?php echo t('change_password'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($estudiantes as $est): ?>
                        <tr>
                            <td><?php echo $est['nombre']; ?></td>
                            <td><strong><?php echo $est['contrasena']; ?></strong></td>
                            <td>
                                <form method="POST" class="form-cambiar-pass">
                                    <input type="hidden" name="user_id" value="<?php echo $est['id']; ?>">
                                    <input type="text" name="nueva_contrasena" placeholder="<?php echo t('new_password'); ?>" required class="input-pass">
                                    <button type="submit" name="cambiar_pass" class="btn-cambiar"><?php echo t('change'); ?></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="login-footer">
            <select class="language-select" onchange="changeLanguage(this.value)">
                <option value="es" <?php echo ($lang == 'es') ? 'selected' : ''; ?>>Español (México) [es_mx]</option>
                <option value="en" <?php echo ($lang == 'en') ? 'selected' : ''; ?>>English [en]</option>
            </select>
            <p class="cookie-notice"><?php echo t('cookie_notice'); ?></p>
        </div>
    </div>

    <script>
        function changeLanguage(lang) {
            window.location.href = 'dashboard_profesor.php?lang=' + lang;
        }
    </script>
</body>
</html>
