<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'estudiante') {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo'])) {
    $actividad_id = (int)$_POST['actividad_id'];
    $estudiante_id = $_SESSION['user_id'];
    $archivo = $_FILES['archivo'];
    
    // Validar tipo
    $tipos_permitidos = ['doc', 'docx', 'pdf'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $tipos_permitidos)) {
        header('Location: actividad.php?id=' . $actividad_id . '&error=Tipo de archivo no permitido');
        exit();
    }
    
    if ($archivo['size'] > 512 * 1024 * 1024) {
        header('Location: actividad.php?id=' . $actividad_id . '&error=El archivo excede el tamaño máximo');
        exit();
    }
    
    $nombre_original = basename($archivo['name']);
    $nombre_unico = time() . '_' . $estudiante_id . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $nombre_original);
    
    // DEBUG: Intentar subir a Supabase
    $ruta_publica = supabaseUpload($archivo['tmp_name'], $nombre_unico, $archivo['type']);
    
    if (!$ruta_publica) {
        $error_msg = 'Error al subir archivo a la nube';
        if (isset($_SESSION['upload_error'])) {
            $error_msg .= ' | DEBUG: ' . $_SESSION['upload_error'];
            unset($_SESSION['upload_error']);
        }
        header('Location: actividad.php?id=' . $actividad_id . '&error=' . urlencode($error_msg));
        exit();
    }
    
    // Verificar si ya existe entrega
    $stmt = $pdo->prepare("SELECT id, ruta_archivo FROM entregas WHERE actividad_id = ? AND estudiante_id = ?");
    $stmt->execute([$actividad_id, $estudiante_id]);
    $existente = $stmt->fetch();
    
    if ($existente) {
        $nombre_anterior = basename($existente['ruta_archivo']);
        supabaseDelete($nombre_anterior);
        $stmt3 = $pdo->prepare("UPDATE entregas SET nombre_archivo = ?, ruta_archivo = ?, fecha_entrega = NOW() WHERE id = ?");
        $stmt3->execute([$nombre_original, $ruta_publica, $existente['id']]);
    } else {
        $stmt4 = $pdo->prepare("INSERT INTO entregas (actividad_id, estudiante_id, nombre_archivo, ruta_archivo, fecha_entrega) VALUES (?, ?, ?, ?, NOW())");
        $stmt4->execute([$actividad_id, $estudiante_id, $nombre_original, $ruta_publica]);
    }
    
    header('Location: actividad.php?id=' . $actividad_id . '&exito=Archivo subido correctamente');
    exit();
}
?>
