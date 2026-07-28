<?php
require_once 'config.php';

// ============================================
// SI YA ESTÁ LOGUEADO, REDIRIGIR A SU DASHBOARD
// ============================================
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_rol'] == 'estudiante') {
        header('Location: dashboard_estudiante.php');
        exit();
    } elseif ($_SESSION['user_rol'] == 'profesor') {
        header('Location: dashboard_profesor.php');
        exit();
    }
}

require_once 'languages.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('title'); ?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0d1445 0%, #1a237e 40%, #283593 100%);
            padding: 16px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(ellipse at 20% 50%, rgba(220, 201, 122, 0.08) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 50%, rgba(220, 201, 122, 0.05) 0%, transparent 60%);
            pointer-events: none;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.03;
            pointer-events: none;
        }
        .shape-1 { width: 400px; height: 400px; background: #dcc97a; top: -150px; right: -150px; }
        .shape-2 { width: 300px; height: 300px; background: #dcc97a; bottom: -100px; left: -100px; }
        .shape-3 { width: 150px; height: 150px; background: #dcc97a; top: 50%; left: 50%; transform: translate(-50%, -50%); }

        .login-wrapper {
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 32px 32px 28px;
            box-shadow: 
                0 25px 60px rgba(0, 0, 0, 0.30),
                0 0 0 1px rgba(255, 255, 255, 0.06) inset;
            border: 1px solid rgba(255, 255, 255, 0.12);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35);
        }

        .login-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #1a237e, #283593);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            box-shadow: 0 6px 24px rgba(26, 35, 126, 0.25);
            position: relative;
        }

        .logo-icon::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 18px;
            background: linear-gradient(135deg, #dcc97a, rgba(220, 201, 122, 0.3));
            z-index: -1;
            opacity: 0.4;
        }

        .logo-icon svg {
            width: 40px;
            height: 40px;
        }

        .login-header h1 {
            color: #1a237e;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .login-header .subtitle {
            color: #6b7a9f;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .login-header .divider {
            width: 32px;
            height: 3px;
            background: linear-gradient(90deg, #dcc97a, #1a237e);
            border-radius: 4px;
            margin: 10px auto 0;
        }

        .login-box h2 {
            color: #1a237e;
            font-size: 17px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 20px;
            letter-spacing: -0.3px;
        }

        .input-group {
            margin-bottom: 14px;
            position: relative;
        }

        .input-group label {
            display: block;
            color: #1a237e;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
            letter-spacing: 0.3px;
        }

        .input-group .input-icon {
            position: absolute;
            left: 14px;
            top: 36px;
            color: #9aa8c7;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .input-group input {
            width: 100%;
            padding: 11px 14px 11px 42px;
            border: 2px solid #e8ecf5;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #1a237e;
            background: #f8faff;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            outline: none;
        }

        .input-group input::placeholder {
            color: #b0bdd8;
            font-weight: 400;
        }

        .input-group input:focus {
            border-color: #1a237e;
            background: white;
            box-shadow: 0 0 0 4px rgba(26, 35, 126, 0.08);
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #1a237e, #283593);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.3px;
            margin-top: 4px;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(26, 35, 126, 0.30);
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        .error-msg {
            background: #fef2f2;
            color: #dc2626;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            border-left: 4px solid #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .login-links {
            margin-top: 20px;
            text-align: center;
        }

        .forgot-pass {
            color: #6b7a9f;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: color 0.3s ease;
            cursor: pointer;
            display: inline-block;
        }

        .forgot-pass:hover {
            color: #1a237e;
        }

        .forgot-message {
            background: #fefce8;
            color: #92400e;
            padding: 10px 14px;
            border-radius: 8px;
            margin: 10px 0;
            font-size: 13px;
            font-weight: 500;
            border-left: 4px solid #dcc97a;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .guest-info {
            color: #9aa8c7;
            font-size: 12px;
            margin: 12px 0 10px;
            font-weight: 400;
        }

        .guest-login {
            display: inline-block;
            color: #1a237e;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 24px;
            border: 2px solid #dcc97a;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: transparent;
        }

        .guest-login:hover {
            background: #dcc97a;
            color: #1a237e;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 201, 122, 0.3);
        }

        .login-footer {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid #e8ecf5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .language-select {
            padding: 6px 12px;
            border: 2px solid #e8ecf5;
            border-radius: 8px;
            background: white;
            color: #1a237e;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: border-color 0.3s ease;
            font-family: 'Inter', sans-serif;
            outline: none;
        }

        .language-select:hover,
        .language-select:focus {
            border-color: #1a237e;
        }

        .cookie-notice {
            color: #9aa8c7;
            font-size: 11px;
            font-weight: 400;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 24px 20px 20px;
                border-radius: 16px;
            }
            .login-header h1 {
                font-size: 20px;
            }
            .logo-icon {
                width: 56px;
                height: 56px;
            }
            .logo-icon svg {
                width: 34px;
                height: 34px;
            }
            .login-footer {
                flex-direction: column;
                text-align: center;
            }
            .btn-login {
                padding: 12px;
                font-size: 14px;
            }
            .input-group input {
                padding: 10px 12px 10px 38px;
                font-size: 13px;
            }
            .input-group .input-icon {
                top: 34px;
                left: 12px;
                font-size: 14px;
            }
        }

        @media (max-width: 360px) {
            .login-card {
                padding: 18px 14px 16px;
            }
            .login-header h1 {
                font-size: 18px;
            }
            .logo-icon {
                width: 48px;
                height: 48px;
            }
            .logo-icon svg {
                width: 28px;
                height: 28px;
            }
            .login-box h2 {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <!-- Logo -->
            <div class="login-header">
                <div class="logo-icon">
                    <svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M30 4C20 4 12 12 12 22V40C12 50 20 58 30 58C40 58 48 50 48 40V22C48 12 40 4 30 4Z" 
                              stroke="white" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M30 4C25 4 21 8 21 14V36C21 42 25 46 30 46C35 46 39 42 39 36V14C39 8 35 4 30 4Z" 
                              fill="rgba(255,255,255,0.15)" stroke="white" stroke-width="3"/>
                        <path d="M30 14V36" stroke="#dcc97a" stroke-width="3" stroke-linecap="round"/>
                        <circle cx="30" cy="44" r="4" fill="#dcc97a" opacity="0.6"/>
                    </svg>
                </div>
                <h1>TEC AZUAY</h1>
                <p class="subtitle"><?php echo t('institute'); ?></p>
                <div class="divider"></div>
            </div>

            <!-- Form -->
            <div class="login-box">
                <h2><?php echo t('login_title'); ?></h2>

                <?php if(isset($_GET['error'])): ?>
                    <div class="error-msg">
                        <span>⚠️</span> 
                        <?php 
                        if ($_GET['error'] == 'demasiados_intentos') {
                            echo 'Demasiados intentos fallidos. Espera 15 minutos.';
                        } elseif ($_GET['error'] == 'token_invalido') {
                            echo 'Error de seguridad. Intenta nuevamente.';
                        } elseif ($_GET['error'] == 'sesion_expirada') {
                            echo 'Tu sesión ha expirado. Inicia sesión nuevamente.';
                        } else {
                            echo t('login_error');
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <!-- ============================================ -->
                    <!-- TOKEN CSRF - PROTECCIÓN CONTRA ATAQUES CSRF -->
                    <!-- ============================================ -->
                    <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                    
                    <div class="input-group">
                        <label for="usuario"><?php echo t('username'); ?></label>
                        <input type="text" id="usuario" name="usuario" placeholder="<?php echo t('username'); ?>" required>
                        <span class="input-icon">👤</span>
                    </div>

                    <div class="input-group">
                        <label for="contrasena"><?php echo t('password'); ?></label>
                        <input type="password" id="contrasena" name="contrasena" placeholder="<?php echo t('password'); ?>" required>
                        <span class="input-icon">🔒</span>
                    </div>

                    <button type="submit" class="btn-login"><?php echo t('login_btn'); ?></button>
                </form>

                <div class="login-links">
                    <a href="#" class="forgot-pass" onclick="showForgotMessage()"><?php echo t('forgot_password'); ?></a>
                    <div id="forgot-message" class="forgot-message" style="display:none;">
                        <?php echo t('forgot_message'); ?>
                    </div>
                    <p class="guest-info"><?php echo t('guest_info'); ?></p>
                    <a href="guest.php" class="guest-login"><?php echo t('guest_login'); ?></a>
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
    </div>

    <script>
        function changeLanguage(lang) {
            window.location.href = 'index.php?lang=' + lang;
        }

        function showForgotMessage() {
            var msg = document.getElementById('forgot-message');
            if (msg.style.display === 'none') {
                msg.style.display = 'block';
            } else {
                msg.style.display = 'none';
            }
        }
    </script>
</body>
</html>
