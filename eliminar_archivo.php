<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'estudiante') {
    header('Location: index.php');
    exit();
}

$actividad_id = isset($_GET['actividad_id']) ? (int)$_GET['actividad_id'] : 0;
$estudiante_id = $_SESSION['user_id'];

if ($actividad_id > 0) {
    // Obtener la entrega
    $stmt = $pdo->prepare("SELECT id, ruta_archivo FROM entregas WHERE actividad_id = ? AND estudiante_id = ?");
    $stmt->execute([$actividad_id, $estudiante_id]);
    $entrega = $stmt->fetch();
    
    if ($entrega) {
        // PRIMERO: Borrar calificación asociada (si existe)
        // Esto evita el error de foreign key
        $stmt_cal = $pdo->prepare("DELETE FROM calificaciones WHERE entrega_id = ?");
        $stmt_cal->execute([$entrega['id']]);
        
        // SEGUNDO: Borrar archivo de Supabase Storage
        $nombre_archivo = basename($entrega['ruta_archivo']);
        supabaseDelete($nombre_archivo);
        
        // TERCERO: Borrar de PostgreSQL
        $stmt2 = $pdo->prepare("DELETE FROM entregas WHERE id = ?");
        $stmt2->execute([$entrega['id']]);
        
        header('Location: actividad.php?id=' . $actividad_id . '&exito=Archivo eliminado correctamente');
    } else {
        header('Location: actividad.php?id=' . $actividad_id . '&error=No se encontró el archivo');
    }
} else {
    header('Location: calendar.php');
}
exit();
?>
