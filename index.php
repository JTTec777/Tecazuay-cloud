<?php
require_once 'config.php';

// ============================================
// REDIRECCIÓN SI YA ESTÁ LOGUEADO
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

// ============================================
// MOSTRAR ERRORES DEL LOGIN
// ============================================
$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case '1':
            $error = '❌ Usuario o contraseña incorrectos';
            break;
        case 'token_invalido':
            $error = '❌ Error de seguridad, intenta de nuevo';
            break;
        case 'demasiados_intentos':
            $error = '⛔ Demasiados intentos fallidos. Espera 15 minutos.';
            break;
        case 'sesion_expirada':
            $error = '⏰ Tu sesión ha expirado. Vuelve a iniciar sesión.';
            break;
        case 'sesion_invalida':
            $error = '⚠️ Sesión inválida. Inicia sesión nuevamente.';
            break;
        default:
            $error = '❌ Error desconocido, intenta de nuevo';
    }
}

// Generar token CSRF
$csrf_token = generarTokenCSRF();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TEC AZUAY</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(135deg, #1a237e 0%, #0d1457 100%);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 24px;
            padding: 40px 35px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #1a237e;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .login-header .subtitle {
            color: #888;
            font-size: 12px;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 4px;
        }
        .login-header .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .login-error {
            background: #ffebee;
            color: #c62828;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
            text-align: center;
        }
        .login-form label {
            display: block;
            color: #1a237e;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 6px;
        }
        .login-form input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e8ecf5;
            border-radius: 12px;
            font-size: 15px;
            margin-bottom: 18px;
            transition: 0.3s;
            font-family: 'Inter', sans-serif;
            background: #f8f9ff;
        }
        .login-form input:focus {
            border-color: #1a237e;
            outline: none;
            box-shadow: 0 0 0 4px rgba(26,35,126,0.08);
            background: white;
        }
        .login-form .btn-login {
            width: 100%;
            padding: 14px;
            background: #1a237e;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            font-family: 'Inter', sans-serif;
        }
        .login-form .btn-login:hover {
            background: #0d1457;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26,35,126,0.3);
        }
        .login-footer {
            text-align: center;
            margin-top: 25px;
            color: #888;
            font-size: 12px;
            font-weight: 500;
        }
        .login-footer a {
            color: #1a237e;
            text-decoration: none;
            font-weight: 600;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
        .login-credenciales {
            background: #f8f9ff;
            border-radius: 12px;
            padding: 12px 16px;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
            text-align: center;
            border: 1px dashed #c5cae9;
        }
        .login-credenciales strong {
            color: #1a237e;
        }
        @media (max-width: 480px) {
            .login-container { padding: 30px 20px; }
            .login-header h1 { font-size: 24px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="icon">🎓</div>
            <h1>TEC AZUAY</h1>
            <div class="subtitle">Instituto Universitario</div>
        </div>

        <?php if ($error): ?>
            <div class="login-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="login.php">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <label for="usuario">👤 Usuario</label>
            <input type="text" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required>
            
            <label for="contrasena">🔒 Contraseña</label>
            <input type="password" id="contrasena" name="contrasena" placeholder="Ingresa tu contraseña" required>
            
            <button type="submit" class="btn-login">🚀 Iniciar Sesión</button>
        </form>

        <div class="login-credenciales">
            <strong>Estudiantes:</strong> Sami / sami1234 &nbsp;|&nbsp; <strong>Profesores:</strong> Luis / ingeluis
        </div>

        <div class="login-footer">
            &copy; <?php echo date('Y'); ?> TEC AZUAY - Todos los derechos reservados
        </div>
    </div>
</body>
</html>
