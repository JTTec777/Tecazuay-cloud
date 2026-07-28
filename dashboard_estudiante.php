<?php
require_once 'config.php';
require_once 'languages.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'estudiante') {
    header('Location: index.php');
    exit();
}

$nombre_usuario = strtoupper($_SESSION['user_nombre']);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Estudiante - TEC AZUAY</title>
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
        .btn-tareas { display: inline-block; background: #ff9800; color: white; padding: 12px 28px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 14px; transition: 0.3s; box-shadow: 0 4px 16px rgba(255,152,0,0.3); }
        .btn-tareas:hover { background: #f57c00; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(255,152,0,0.5); }
        .btn-calendar { display: inline-block; background: #1a237e; color: white; padding: 12px 28px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 14px; transition: 0.3s; box-shadow: 0 4px 16px rgba(26,35,126,0.25); }
        .btn-calendar:hover { background: #0d1457; transform: translateY(-3px); }
        .btn-cursos { display: inline-block; background: #4caf50; color: white; padding: 12px 28px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 14px; transition: 0.3s; box-shadow: 0 4px 16px rgba(76,175,80,0.3); }
        .btn-cursos:hover { background: #388e3c; transform: translateY(-3px); }
        .btn-mensajes { display: inline-block; background: #2196f3; color: white; padding: 12px 28px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 14px; transition: 0.3s; box-shadow: 0 4px 16px rgba(33,150,243,0.3); }
        .btn-mensajes:hover { background: #1976d2; transform: translateY(-3px); }
        .btn-notificaciones { display: inline-block; background: #9c27b0; color: white; padding: 12px 28px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 14px; transition: 0.3s; box-shadow: 0 4px 16px rgba(156,39,176,0.3); }
        .btn-notificaciones:hover { background: #7b1fa2; transform: translateY(-3px); }
        .btn-anuncios { display: inline-block; background: #e91e63; color: white; padding: 12px 28px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 14px; transition: 0.3s; box-shadow: 0 4px 16px rgba(233,30,99,0.3); }
        .btn-anuncios:hover { background: #c2185b; transform: translateY(-3px); }
        .btn-seguridad { display: inline-block; background: #607d8b; color: white; padding: 12px 28px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 14px; transition: 0.3s; box-shadow: 0 4px 16px rgba(96,125,139,0.3); }
        .btn-seguridad:hover { background: #455a64; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(96,125,139,0.5); }
	.welcome-banner {
            background: linear-gradient(135deg, #1a237e 0%, #0d1457 100%);
            color: white; padding: 25px 30px; border-radius: 16px;
            margin-bottom: 25px; box-shadow: 0 8px 30px rgba(26,35,126,0.25);
        }
        .welcome-banner h2 { font-size: 22px; font-weight: 700; }
        .horario-container { background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); margin-bottom: 25px; }
        .horario-container h2 { color: #1a237e; margin-bottom: 20px; font-size: 20px; }
        .horario-tabla { width: 100%; border-collapse: separate; border-spacing: 4px; font-size: 11px; }
        .horario-tabla th { background: #1a237e; color: white; padding: 10px 6px; text-align: center; font-weight: 600; border-radius: 8px; }
        .horario-tabla td { border: 1px solid #e8eaf6; padding: 8px 4px; text-align: center; vertical-align: middle; height: 80px; border-radius: 8px; }
        .dia-label { background: #f8f9ff; font-weight: 800; color: #1a237e; font-size: 13px; width: 70px; }
        .hora-header { background: #e8eaf6; color: #1a237e; font-weight: 700; font-size: 10px; }
        .materia { border-radius: 8px; padding: 6px 4px; font-weight: 700; font-size: 10px; line-height: 1.3; height: 100%; display: flex; flex-direction: column; justify-content: center; }
        .materia small { display: block; font-size: 9px; opacity: 0.95; margin-top: 4px; font-weight: 500; }
        .m-hackeo { background: #1a237e; color: white; }
        .m-nube { background: #ff5252; color: white; }
        .m-ciber { background: #448aff; color: white; }
        .m-continuidad { background: #40c4ff; color: #1a237e; }
        .m-legislacion { background: #e040fb; color: white; }
        .login-footer { text-align: center; margin-top: 30px; padding: 20px; color: #888; font-size: 12px; }
        .language-select { padding: 8px 14px; border-radius: 8px; border: 1px solid #ddd; font-family: 'Inter', sans-serif; font-size: 13px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="header-left">
                <h1>TEC AZUAY</h1>
                <span class="subtitle">INSTITUTO UNIVERSITARIO</span>
            </div>
            <div class="header-right">
                <span class="user-name">👨‍🎓 <?php echo $nombre_usuario; ?></span>
                <a href="logout.php?csrf_token=<?php echo generarTokenCSRF(); ?>" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>

	<div class="botones-container">
            <a href="tareas/" class="btn-tareas">📋 Mis Tareas</a>
            <a href="calendar.php" class="btn-calendar">📅 Calendario</a>
            <a href="cursos/" class="btn-cursos">📚 Cursos</a>
            <a href="mensajes/" class="btn-mensajes">💬 Mensajes</a>
            <a href="notificaciones/" class="btn-notificaciones">🔔 Notificaciones</a>
            <a href="anuncios/" class="btn-anuncios">📢 Anuncios</a>
            <a href="seguridad.php" class="btn-seguridad">🔐 Seguridad</a>
        </div>

        <div class="welcome-banner">
            <h2>¡Bienvenido, <?php echo $nombre_usuario; ?>! 📚</h2>
        </div>

        <!-- HORARIO DE CLASES CORREGIDO -->
        <div class="horario-container">
            <h2>📅 Horario de Clases</h2>
            <table class="horario-tabla">
                <thead>
                    <tr>
                        <th></th>
                        <th class="hora-header">17:00 - 18:00</th>
                        <th class="hora-header">18:00 - 19:00</th>
                        <th class="hora-header">19:00 - 20:00</th>
                        <th class="hora-header">20:00 - 21:00</th>
                        <th class="hora-header">21:00 - 22:00</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- LUNES -->
                    <tr>
                        <td class="dia-label">LUNES</td>
                        <td><div class="materia m-hackeo">TSCSSS - HACKEO ETICO LABORATORIO<small>BORIS SQUILANDA<br>LAB3</small></div></td>
                        <td><div class="materia m-hackeo">TSCSSS - HACKEO ETICO LABORATORIO<small>BORIS SQUILANDA<br>LAB3</small></div></td>
                        <td><div class="materia m-nube">TSCS - CIBERSEGURIDAD EN LA NUBE<small>LUIS PORTOCARRERO<br>LAB3</small></div></td>
                        <td><div class="materia m-nube">TSCS - CIBERSEGURIDAD EN LA NUBE<small>LUIS PORTOCARRERO<br>LAB3</small></div></td>
                        <td><div class="materia m-ciber">TSCS - CIBERSEGURIDAD EN TECNOLOGIAS Y SISTEMAS DE INFORMACION<small>SHIRLEY TORRES<br>LAB3</small></div></td>
                    </tr>
                    <!-- MARTES -->
                    <tr>
                        <td class="dia-label">MARTES</td>
                        <td><div class="materia m-ciber">TSCS - CIBERSEGURIDAD EN TECNOLOGIAS Y SISTEMAS DE INFORMACION<small>SHIRLEY TORRES<br>LAB6</small></div></td>
                        <td><div class="materia m-hackeo">TSCSSS - HACKEO ETICO LABORATORIO<small>BORIS SQUILANDA<br>LAB3</small></div></td>
                        <td><div class="materia m-ciber">TSCS - CIBERSEGURIDAD EN TECNOLOGIAS Y SISTEMAS DE INFORMACION<small>SHIRLEY TORRES<br>LAB6</small></div></td>
                        <td><div class="materia m-continuidad">TSCS - CONTINUIDAD DEL NEGOCIO<small>SHIRLEY TORRES</small></div></td>
                        <td><div class="materia m-continuidad">TSCS - CONTINUIDAD DEL NEGOCIO<small>SHIRLEY TORRES</small></div></td>
                    </tr>
                    <!-- MIÉRCOLES -->
                    <tr>
                        <td class="dia-label">MIÉRCOLES</td>
                        <td><div class="materia m-nube">TSCS - CIBERSEGURIDAD EN LA NUBE<small>LUIS PORTOCARRERO<br>LAB3</small></div></td>
                        <td><div class="materia m-nube">TSCS - CIBERSEGURIDAD EN LA NUBE<small>LUIS PORTOCARRERO<br>LAB3</small></div></td>
                        <td><div class="materia m-ciber">TSCS - CIBERSEGURIDAD EN TECNOLOGIAS Y SISTEMAS DE INFORMACION<small>SHIRLEY TORRES<br>LAB6</small></div></td>
                        <td><div class="materia m-hackeo">TSCSSS - HACKEO ETICO LABORATORIO<small>BORIS SQUILANDA<br>LAB3</small></div></td>
                        <td><div class="materia m-hackeo">TSCSSS - HACKEO ETICO LABORATORIO<small>BORIS SQUILANDA<br>LAB3</small></div></td>
                    </tr>
                    <!-- JUEVES -->
                    <tr>
                        <td class="dia-label">JUEVES</td>
                        <td><div class="materia m-legislacion">TSCS - LEGISLACION INFORMATICA<small>LADY SANGACHA<br>LAB3</small></div></td>
                        <td><div class="materia m-legislacion">TSCS - LEGISLACION INFORMATICA<small>LADY SANGACHA<br>LAB3</small></div></td>
                        <td><div class="materia m-continuidad">TSCS - CONTINUIDAD DEL NEGOCIO<small>SHIRLEY TORRES<br>LAB3</small></div></td>
                        <td><div class="materia m-hackeo">TSCSSS - HACKEO ETICO LABORATORIO<small>BORIS SQUILANDA<br>LAB3</small></div></td>
                        <td><div class="materia m-hackeo">TSCSSS - HACKEO ETICO LABORATORIO<small>BORIS SQUILANDA<br>LAB3</small></div></td>
                    </tr>
                    <!-- VIERNES -->
                    <tr>
                        <td class="dia-label">VIERNES</td>
                        <td><div class="materia m-continuidad">TSCS - CONTINUIDAD DEL NEGOCIO<small>SHIRLEY TORRES<br>LAB3</small></div></td>
                        <td><div class="materia m-continuidad">TSCS - CONTINUIDAD DEL NEGOCIO<small>SHIRLEY TORRES<br>LAB3</small></div></td>
                        <td><div class="materia m-nube">TSCS - CIBERSEGURIDAD EN LA NUBE<small>LUIS PORTOCARRERO<br>LAB3</small></div></td>
                        <td><div class="materia m-nube">TSCS - CIBERSEGURIDAD EN LA NUBE<small>LUIS PORTOCARRERO<br>LAB3</small></div></td>
                        <td><div class="materia m-nube">TSCS - CIBERSEGURIDAD EN LA NUBE<small>LUIS PORTOCARRERO<br>LAB3</small></div></td>
                    </tr>
                </tbody>
            </table>
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
            window.location.href = 'dashboard_estudiante.php?lang=' + lang;
        }
    </script>
</body>
</html>
