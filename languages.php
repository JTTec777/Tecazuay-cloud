<?php
// Iniciar sesión para guardar el idioma
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Si se recibe un idioma por GET, guardarlo en sesión
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Si no hay idioma en sesión, usar español por defecto
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'es';
}

// Definir las traducciones
$lang = $_SESSION['lang'];

// Array de traducciones
$translations = [
    'es' => [
        'title' => 'TEC AZUAY - Instituto Universitario',
        'institute' => 'INSTITUTO UNIVERSITARIO',
        'login_title' => 'Iniciar sesión (ingresar)',
        'username' => 'Usuario',
        'password' => 'Contraseña',
        'login_btn' => 'Iniciar sesión (ingresar)',
        'forgot_password' => '¿Ha extraviado la contraseña?',
        'forgot_message' => 'Ni modo si te olvidaste ahí queda',
        'guest_info' => 'Algunos cursos pueden permitir el acceso de invitados',
        'guest_login' => 'Ingresar como invitado',
        'cookie_notice' => 'Aviso sobre cookies',
        'language' => 'Español (México) [es_mx]',
        'welcome_back' => '¡Bienvenido de nuevo',
        'welcome_professor' => '¡Bienvenido, Profesor',
        'timeline' => 'Línea de Tiempo',
        'all' => 'Todos',
        'search' => 'Buscar por tipo de actividad o nombre',
        'online_users' => 'Usuarios en línea',
        'online_count' => 'usuarios en línea (últimos 5 minutos)',
        'logout' => 'Cerrar Sesión',
        'professors_list' => 'Lista de Profesores',
        'students_list' => 'Lista de Estudiantes',
        'password' => 'Contraseña',
        'change_password' => 'Cambiar Contraseña',
        'new_password' => 'Nueva contraseña',
        'change' => 'Cambiar',
        'password_updated' => '✅ Contraseña actualizada correctamente',
        'password_error' => '❌ Error al actualizar la contraseña',
        'password_empty' => '❌ La contraseña no puede estar vacía',
        'guest_title' => 'Panel de Invitado',
        'guest_welcome' => 'Bienvenido, Invitado',
        'assignments' => 'Tareas de Materias',
        'course' => 'Curso',
        'assignment' => 'Tarea',
        'date' => 'Fecha',
        'due_date' => 'Fecha de entrega',
    ],
    'en' => [
        'title' => 'TEC AZUAY - University Institute',
        'institute' => 'UNIVERSITY INSTITUTE',
        'login_title' => 'Login (sign in)',
        'username' => 'Username',
        'password' => 'Password',
        'login_btn' => 'Sign in (login)',
        'forgot_password' => 'Forgot your password?',
        'forgot_message' => 'Too bad, if you forgot, that\'s it',
        'guest_info' => 'Some courses may allow guest access',
        'guest_login' => 'Login as guest',
        'cookie_notice' => 'Cookie notice',
        'language' => 'English [en]',
        'welcome_back' => 'Welcome back',
        'welcome_professor' => 'Welcome, Professor',
        'timeline' => 'Timeline',
        'all' => 'All',
        'search' => 'Search by activity type or name',
        'online_users' => 'Online users',
        'online_count' => 'online users (last 5 minutes)',
        'logout' => 'Logout',
        'professors_list' => 'Professors List',
        'students_list' => 'Students List',
        'password' => 'Password',
        'change_password' => 'Change Password',
        'new_password' => 'New password',
        'change' => 'Change',
        'password_updated' => '✅ Password updated successfully',
        'password_error' => '❌ Error updating password',
        'password_empty' => '❌ Password cannot be empty',
        'guest_title' => 'Guest Panel',
        'guest_welcome' => 'Welcome, Guest',
        'assignments' => 'Course Assignments',
        'course' => 'Course',
        'assignment' => 'Assignment',
        'date' => 'Date',
        'due_date' => 'Due Date',
    ]
];

// Función para obtener traducción
function t($key) {
    global $translations, $lang;
    return isset($translations[$lang][$key]) ? $translations[$lang][$key] : $key;
}
?>
