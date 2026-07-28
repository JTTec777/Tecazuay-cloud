<?php
require_once 'languages.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('guest_title'); ?> - TEC AZUAY</title>
    <link rel="stylesheet" href="style.css">
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
                <span class="user-name">👤 <?php echo t('guest_welcome'); ?></span>
                <a href="index.php" class="btn-logout"><?php echo t('logout'); ?></a>
            </div>
        </header>

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h2>👋 <?php echo t('guest_welcome'); ?>!</h2>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="timeline-section">
                <div class="section-header">
                    <h3><?php echo t('assignments'); ?></h3>
                    <div class="filter-options">
                        <span class="active"><?php echo t('all'); ?></span>
                    </div>
                </div>

                <!-- Tarea 1 -->
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

                <!-- Tarea 2 -->
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
            window.location.href = 'guest.php?lang=' + lang;
        }
    </script>
</body>
</html>
