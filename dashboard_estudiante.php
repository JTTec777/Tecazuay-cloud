<?php
require_once 'config.php';
require_once 'languages.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'estudiante') {
    header('Location: index.php');
    exit();
}

$stmt = $pdo->query("SELECT nombre, usuario FROM usuarios WHERE rol_id = 1 ORDER BY nombre");
$estudiantes = $stmt->fetchAll();

$nombre_usuario = strtoupper($_SESSION['user_nombre']);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Estudiante - TEC AZUAY</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .btn-calendar {
            display: inline-block;
            background: #1a237e;
            color: white;
            padding: 12px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            transition: 0.3s;
            box-shadow: 0 4px 16px rgba(26, 35, 126, 0.25);
            margin-bottom: 25px;
            margin-right: 15px;
        }
        .btn-calendar:hover {
            background: #0d1457;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(26, 35, 126, 0.35);
        }
        .btn-cursos {
            display: inline-block;
            background: #dcc97a;
            color: #1a237e;
            padding: 12px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            transition: 0.3s;
            box-shadow: 0 4px 16px rgba(220, 201, 122, 0.3);
            margin-bottom: 25px;
            margin-right: 15px;
        }
        .btn-cursos:hover {
            background: #c4b15a;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(220, 201, 122, 0.5);
        }
        .btn-calificaciones {
            display: inline-block;
            background: #4caf50;
            color: white;
            padding: 12px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            transition: 0.3s;
            box-shadow: 0 4px 16px rgba(76, 175, 80, 0.3);
            margin-bottom: 25px;
            margin-right: 15px;
        }
        .btn-calificaciones:hover {
            background: #388e3c;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.5);
        }
        .btn-mensajes {
            display: inline-block;
            background: #ff9800;
            color: white;
            padding: 12px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            transition: 0.3s;
            box-shadow: 0 4px 16px rgba(255, 152, 0, 0.3);
            margin-bottom: 25px;
            margin-right: 15px;
        }
        .btn-mensajes:hover {
            background: #f57c00;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 152, 0, 0.5);
        }
        .btn-notificaciones {
            display: inline-block;
            background: #2196f3;
            color: white;
            padding: 12px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            transition: 0.3s;
            box-shadow: 0 4px 16px rgba(33, 150, 243, 0.3);
            margin-bottom: 25px;
            margin-right: 15px;
        }
        .btn-notificaciones:hover {
            background: #1976d2;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(33, 150, 243, 0.5);
        }
        .btn-anuncios {
            display: inline-block;
            background: #9c27b0;
            color: white;
            padding: 12px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            transition: 0.3s;
            box-shadow: 0 4px 16px rgba(156, 39, 176, 0.3);
            margin-bottom: 25px;
        }
        .btn-anuncios:hover {
            background: #7b1fa2;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(156, 39, 176, 0.5);
        }
        .botones-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }
        .badge-notificacion {
            background: #dc3545;
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 50%;
            margin-left: 6px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="header-left">
                <h1>TEC AZUAY</h1>
                <span class="subtitle"><?php echo t('institute'); ?></span>
            </div>
            <div class="header-right">
                <span class="user-name">👋 <?php echo $nombre_usuario; ?></span>
                <a href="logout.php" class="btn-logout"><?php echo t('logout'); ?></a>
            </div>
        </header>

        <!-- Botones de navegación -->
        <div class="botones-container">
            <a href="calendar.php" class="btn-calendar">📅 Ver Calendario Académico</a>
            <a href="cursos/" class="btn-cursos">📚 Mis Cursos</a>
            <a href="calificaciones/ver.php" class="btn-calificaciones">📊 Mis Calificaciones</a>
            <a href="mensajes/" class="btn-mensajes">💬 Mensajes</a>
            <a href="notificaciones/" class="btn-notificaciones">🔔 Notificaciones</a>
            <a href="anuncios/" class="btn-anuncios">📢 Anuncios</a>
        </div>

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h2><?php echo t('welcome_back'); ?>, <?php echo $nombre_usuario; ?>! 🎉</h2>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Timeline Section -->
            <div class="timeline-section">
                <div class="section-header">
                    <h3><?php echo t('timeline'); ?></h3>
                    <div class="filter-options">
                        <span class="active"><?php echo t('all'); ?></span>
                    </div>
                </div>

                <!-- Assignment 1 -->
                <div class="timeline-item">
                    <div class="course-info">
                        <h4>CONTINUIDAD DEL NEGOCIO</h4>
                        <p class="date"><?php echo ($lang == 'es') ? 'Martes, 16 de Junio de 2026' : 'Tuesday, June 16, 2026'; ?></p>
                    </div>
                    <div class="assignment-info">
                        <span class="time">23:00</span>
                        <p class="assignment-title">IDENTIFICACIÓN DE PRODUCTOS Y SERVICIOS</p>
                    </div>
                </div>

                <!-- Assignment 2 -->
                <div class="timeline-item">
                    <div class="course-info">
                        <h4>HACKEO ÉTICO LABORATORIO</h4>
                        <p class="date"><?php echo ($lang == 'es') ? 'Jueves, 2 de Julio de 2026' : 'Thursday, July 2, 2026'; ?></p>
                    </div>
                    <div class="assignment-info">
                        <span class="time">23:59</span>
                        <p class="assignment-title">GUÍA PRÁCTICA UNIDAD 3</p>
                    </div>
                </div>

                <!-- Search -->
                <div class="search-box">
                    <input type="text" placeholder="<?php echo t('search'); ?>">
                </div>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-card">
                    <h4>👥 Usuarios</h4>
                    <ul class="user-list">
                        <?php foreach($estudiantes as $est): ?>
                            <li><?php echo strtoupper($est['nombre']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer -->
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
            window.location.href = 'dashboard_estudiante.php?lang=' + lang;
        }
    </script>
</body>
</html>
