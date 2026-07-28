<?php
require_once 'config.php';
$titulo = 'Seguridad - TEC AZUAY';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$usuario = $_SESSION['user_nombre'];
$error = '';
$exito = '';

// Obtener estado actual
$stmt = $pdo->prepare("SELECT totp_enabled, totp_secret FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// ============================================
// DESACTIVAR 2FA
// ============================================
if (isset($_POST['desactivar']) && $user['totp_enabled']) {
    $codigo = preg_replace('/\D/', '', $_POST['codigo_desactivar']);
    if (verifyTOTPCode($user['totp_secret'], $codigo)) {
        $stmt = $pdo->prepare("UPDATE usuarios SET totp_enabled = FALSE, totp_secret = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
        $exito = '✅ 2FA desactivado correctamente.';
        $user['totp_enabled'] = false;
        $user['totp_secret'] = null;
    } else {
        $error = '❌ Código incorrecto. No se desactivó.';
    }
}

// ============================================
// ACTIVAR 2FA - PASO 2: Verificar código de prueba
// ============================================
if (isset($_POST['activar_paso2']) && !empty($_SESSION['temp_totp_secret'])) {
    $codigo = preg_replace('/\D/', '', $_POST['codigo_activar']);
    $secret = $_SESSION['temp_totp_secret'];
    
    if (verifyTOTPCode($secret, $codigo)) {
        // Guardar en BD
        $stmt = $pdo->prepare("UPDATE usuarios SET totp_secret = ?, totp_enabled = TRUE WHERE id = ?");
        $stmt->execute([$secret, $user_id]);
        $exito = '✅ 2FA activado correctamente. Desde ahora deberás ingresar el código en cada login.';
        $user['totp_enabled'] = true;
        $user['totp_secret'] = $secret;
        unset($_SESSION['temp_totp_secret']);
    } else {
        $error = '❌ Código incorrecto. Intenta escanear el QR de nuevo.';
    }
}

// ============================================
// ACTIVAR 2FA - PASO 1: Generar QR (solo si no tiene 2FA)
// ============================================
$qr_url = '';
$secret_nuevo = '';
if (!$user['totp_enabled']) {
    if (!empty($_SESSION['temp_totp_secret'])) {
        $secret_nuevo = $_SESSION['temp_totp_secret'];
    } else {
        $secret_nuevo = generateTOTPSecret(16);
        $_SESSION['temp_totp_secret'] = $secret_nuevo;
    }
    $qr_url = getQRCodeUrl($usuario, $secret_nuevo, 'TEC AZUAY');
}
?>
<style>
    .seguridad-container { max-width: 600px; margin: 0 auto; }
    .card-seguridad { background: white; border-radius: 16px; padding: 30px; box-shadow: 0 4px 20px rgba(26,35,126,0.08); margin-bottom: 20px; text-align: center; }
    .card-seguridad h2 { color: #1a237e; margin-bottom: 10px; font-size: 22px; }
    .card-seguridad p { color: #666; font-size: 14px; margin-bottom: 20px; line-height: 1.6; }
    .qr-box { background: #f8f9ff; border-radius: 16px; padding: 20px; margin: 20px 0; display: inline-block; }
    .qr-box img { border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
    .secret-code { background: #1a237e; color: #dcc97a; padding: 12px 20px; border-radius: 10px; font-family: monospace; font-size: 18px; letter-spacing: 4px; margin: 15px 0; display: inline-block; font-weight: 700; }
    .form-codigo { margin-top: 20px; }
    .form-codigo label { display: block; color: #1a237e; font-weight: 600; margin-bottom: 8px; font-size: 13px; text-align: left; }
    .form-codigo input { width: 100%; padding: 14px; font-size: 20px; text-align: center; letter-spacing: 6px; border: 2px solid #e8ecf5; border-radius: 10px; margin-bottom: 16px; font-weight: 700; color: #1a237e; outline: none; }
    .form-codigo input:focus { border-color: #1a237e; box-shadow: 0 0 0 4px rgba(26,35,126,0.08); }
    .btn-verificar { background: #4caf50; color: white; padding: 12px 30px; border: none; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; transition: 0.3s; width: 100%; }
    .btn-verificar:hover { background: #388e3c; transform: translateY(-2px); }
    .btn-desactivar { background: #dc3545; color: white; padding: 12px 30px; border: none; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; transition: 0.3s; width: 100%; }
    .btn-desactivar:hover { background: #c82333; transform: translateY(-2px); }
    .estado-activo { background: #e8f5e9; color: #2e7d32; padding: 12px 20px; border-radius: 10px; font-weight: 600; margin-bottom: 20px; display: inline-block; }
    .estado-inactivo { background: #fff3e0; color: #e65100; padding: 12px 20px; border-radius: 10px; font-weight: 600; margin-bottom: 20px; display: inline-block; }
    .mensaje-error { background: #ffebee; color: #c62828; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-weight: 600; border-left: 4px solid #c62828; text-align: left; }
    .mensaje-exito { background: #e8f5e9; color: #2e7d32; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-weight: 600; border-left: 4px solid #4caf50; text-align: left; }
    .instrucciones { text-align: left; background: #f8f9ff; padding: 20px; border-radius: 12px; margin-bottom: 20px; }
    .instrucciones ol { margin: 0; padding-left: 20px; color: #444; font-size: 14px; line-height: 1.8; }
    .instrucciones li { margin-bottom: 6px; }
</style>

<div class="seguridad-container">
    <h2 style="color:#1a237e; margin-bottom:20px; text-align:center;">🔐 Seguridad de la Cuenta</h2>

    <?php if ($error): ?>
        <div class="mensaje-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($exito): ?>
        <div class="mensaje-exito"><?php echo $exito; ?></div>
    <?php endif; ?>

    <?php if ($user['totp_enabled']): ?>
        <!-- 2FA ACTIVADO -->
        <div class="card-seguridad">
            <div class="estado-activo">✅ Autenticación en dos pasos ACTIVADA</div>
            <p>Tu cuenta está protegida. Cada vez que inicies sesión deberás ingresar el código de 6 dígitos de tu app autenticadora.</p>
            
            <div style="border-top: 1px solid #f0f2f5; padding-top: 25px; margin-top: 25px;">
                <p style="color:#c62828; font-weight:600; margin-bottom:15px;">⚠️ ¿Quieres desactivarla?</p>
                <form method="POST" class="form-codigo">
                    <label>Ingresa un código actual de tu app para confirmar:</label>
                    <input type="text" name="codigo_desactivar" maxlength="6" placeholder="000000" pattern="\d{6}" required>
                    <button type="submit" name="desactivar" class="btn-desactivar">Desactivar 2FA</button>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- 2FA NO ACTIVADO -->
        <div class="card-seguridad">
            <div class="estado-inactivo">⚠️ Autenticación en dos pasos DESACTIVADA</div>
            <p>Activa el 2FA para proteger tu cuenta con una capa extra de seguridad.</p>
            
            <div class="instrucciones">
                <strong style="color:#1a237e;">Pasos para activar:</strong>
                <ol>
                    <li>Descarga <strong>Google Authenticator</strong>, <strong>Microsoft Authenticator</strong> o <strong>Authy</strong> en tu celular.</li>
                    <li>Escanea el código QR de abajo con la app.</li>
                    <li>Ingresa el código de 6 dígitos que aparece en la app para verificar.</li>
                </ol>
            </div>

            <div class="qr-box">
                <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="QR 2FA" width="200" height="200">
            </div>

            <div>
                <p style="font-size:12px; color:#888; margin-bottom:5px;">Si no puedes escanear, ingresa este código manualmente:</p>
                <div class="secret-code"><?php echo chunk_split($secret_nuevo, 4, ' '); ?></div>
            </div>

            <form method="POST" class="form-codigo" style="margin-top:25px;">
                <input type="hidden" name="activar_paso2" value="1">
                <label>Ingresa el código de 6 dígitos de tu app:</label>
                <input type="text" name="codigo_activar" maxlength="6" placeholder="000000" pattern="\d{6}" required autofocus>
                <button type="submit" class="btn-verificar">✅ Activar 2FA</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
