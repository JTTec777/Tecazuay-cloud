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
    
    // Subir a Supabase Storage
    $ruta_publica = supabaseUpload($archivo['tmp_name'], $nombre_unico, $archivo['type']);
    
    if (!$ruta_publica) {
        $debug = isset($_SESSION['upload_error']) ? $_SESSION['upload_error'] : 'Error desconocido de Supabase';
        header('Location: actividad.php?id=' . $actividad_id . '&error=' . urlencode('Error al subir archivo: ' . $debug));
        exit();
    }
    
    // Guardar en PostgreSQL con manejo de errores
    try {
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
        
    } catch (PDOException $e) {
        // Borrar archivo de Supabase si falló la BD
        supabaseDelete($nombre_unico);
        header('Location: actividad.php?id=' . $actividad_id . '&error=' . urlencode('Error de base de datos: ' . $e->getMessage()));
        exit();
    }
}
?>
