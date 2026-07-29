<?php
require_once __DIR__ . '/config.php';

echo "<h1>🐛 DEBUG - Estado de la Sesión</h1>";
echo "<pre style='font-size:16px;background:#f4f4f4;padding:20px;border-radius:10px;'>";

echo "<strong>user_id:</strong>     " . (isset($_SESSION['user_id'])     ? $_SESSION['user_id']     : '❌ NO DEFINIDO') . "\n";
echo "<strong>user_rol:</strong>    " . (isset($_SESSION['user_rol'])    ? "'" . $_SESSION['user_rol'] . "'"    : '❌ NO DEFINIDO') . "\n";
echo "<strong>user_nombre:</strong>  " . (isset($_SESSION['user_nombre']) ? $_SESSION['user_nombre'] : '❌ NO DEFINIDO') . "\n";
echo "<strong>totp_enabled:</strong> " . (isset($_SESSION['totp_enabled'])? ($_SESSION['totp_enabled']?'SI':'NO') : 'NO DEFINIDO') . "\n";
echo "\n";
echo "<strong>SCRIPT_NAME:</strong>  " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "<strong>REQUEST_URI:</strong>  " . $_SERVER['REQUEST_URI'] . "\n";

echo "</pre>";
