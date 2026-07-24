<?php
require_once 'config.php';

// ============================================
// CERRAR SESIÓN (VERSIÓN SIMPLE Y SEGURA)
// ============================================

// Registrar actividad (opcional)
if (function_exists('logActividad')) {
    logActividad('Logout', 'Usuario: ' . ($_SESSION['user_nombre'] ?? 'Desconocido'));
}

// Cerrar sesión
session_unset();
session_destroy();

// Redirigir al login
header('Location: index.php');
exit();
?>
