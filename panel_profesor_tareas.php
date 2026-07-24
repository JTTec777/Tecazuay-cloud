<?php
require_once 'config.php';
require_once 'languages.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'profesor') {
    header('Location: index.php');
    exit();
}

$nombre_usuario = strtoupper($_SESSION['user_nombre']);

// Obtener todos los estudiantes
$stmt_estudiantes = $pdo->query("SELECT id, nombre, usuario FROM usuarios WHERE rol_id = 1 ORDER BY nombre");
$todos_estudiantes = $stmt_estudiantes->fetchAll();

// Obtener todas las entregas
$stmt_entregas = $pdo->query("
    SELECT 
        e.id,
        e.actividad_id,
        e.nombre_archivo,
        e.ruta_archivo,
        e.fecha_entrega,
        e.estudiante_id
    FROM entregas e
    ORDER BY e.fecha_entrega DESC
");
$entregas = $stmt_entregas->fetchAll();

// Agrupar entregas por estudiante y actividad
$entregas_por_estudiante = [];
foreach ($entregas as $entrega) {
    $key = $entrega['estudiante_id'] . '_' . $entrega['actividad_id'];
    $entregas_por_estudiante[$key] = $entrega;
}

// Actividades disponibles
$actividades = [
    1 => 'Inventario, Clasificación de Activos y Protección de Datos Personales',
    2 => 'GUÍA PRÁCTICA UNIDAD 3'
];

// Obtener actividad_id de la URL (para filtrar)
$actividad_filtro = isset($_GET['actividad']) ? (int)$_GET['actividad'] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Tareas - Profesor</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { max-width: 1300px; margin: 0 auto; padding: 20px; }

        .header {
            background: white;
            padding: 18px 32px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(26, 35, 126, 0.08);
            margin-bottom: 30px;
            border-left: 4px solid #dcc97a;
        }
        .header-left h1 { color: #1a237e; font-size: 24px; font-weight: 800; }
        .header-left .subtitle { color: #888; font-size: 12px; letter-spacing: 3px; margin-left: 12px; font-weight: 600; }
        .header-right { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
        .user-name { color: #1a237e; font-weight: 700; font-size: 15px; }
        .btn-logout { background: #dc3545; color: white; padding: 8px 22px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.3s; }
        .btn-logout:hover { background: #c82333; transform: translateY(-2px); }
        .btn-back { background: #6c757d; color: white; padding: 8px 22px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.3s; }
        .btn-back:hover { background: #5a6268; transform: translateY(-2px); }

        .card { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 4px 25px rgba(26, 35, 126, 0.06); border: 1px solid rgba(26, 35, 126, 0.04); margin-bottom: 25px; }
        .card h2 { color: #1a237e; font-size: 22px; font-weight: 700; border-bottom: 2px solid #e8eaf6; padding-bottom: 12px; margin-bottom: 20px; }

        .filtros {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filtros a {
            background: #e8eaf6;
            color: #1a237e;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
        }
        .filtros a:hover {
            background: #1a237e;
            color: white;
        }
        .filtros a.activo {
            background: #1a237e;
            color: white;
        }

        table { width: 100%; border-collapse: collapse; }
        thead { background: #1a237e; color: white; }
        thead th { padding: 12px 16px; text-align: left; font-size: 14px; }
        tbody td { padding: 12px 16px; border-bottom: 1px solid #edf0f7; font-size: 14px; }
        tbody tr:hover { background: #f8f9ff; }

        .badge-entregado {
            display: inline-block;
            background: #4caf50;
            color: white;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        .badge-no-entregado {
            display: inline-block;
            background: #f44336;
            color: white;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        .btn-descargar {
            background: #1a237e;
            color: white;
            padding: 4px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-descargar:hover { background: #0d1457; }

        .texto-no-entregado {
            color: #f44336;
            font-weight: 600;
        }

        .resumen {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .resumen-item {
            background: #f8f9ff;
            padding: 12px 24px;
            border-radius: 12px;
            border-left: 4px solid #1a237e;
        }
        .resumen-item .numero {
            font-size: 24px;
            font-weight: 700;
            color: #1a237e;
        }
        .resumen-item .label {
            color: #666;
            font-size: 14px;
        }
        .resumen-item.entregados .numero { color: #4caf50; }
        .resumen-item.pendientes .numero { color: #f44336; }

        @media (max-width: 768px) {
            .header { flex-direction: column; gap: 15px; text-align: center; padding: 18px; }
            .header-right { justify-content: center; }
            table { font-size: 12px; }
            thead th, tbody td { padding: 8px 10px; }
            .resumen { gap: 15px; }
            .resumen-item { padding: 10px 16px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <h1>TEC AZUAY <span class="subtitle">INSTITUTO UNIVERSITARIO</span></h1>
            </div>
            <div class="header-right">
                <span class="user-name">👨‍🏫 <?php echo $nombre_usuario; ?></span>
                <a href="dashboard_profesor.php" class="btn-back">← Volver</a>
                <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>

        <div class="card">
            <h2>📋 Entregas de Tareas</h2>

            <!-- Filtros por actividad -->
            <div class="filtros">
                <a href="panel_profesor_tareas.php" class="<?php echo ($actividad_filtro == 0) ? 'activo' : ''; ?>">📚 Todas</a>
                <a href="panel_profesor_tareas.php?actividad=1" class="<?php echo ($actividad_filtro == 1) ? 'activo' : ''; ?>">📘 <?php echo $actividades[1]; ?></a>
                <a href="panel_profesor_tareas.php?actividad=2" class="<?php echo ($actividad_filtro == 2) ? 'activo' : ''; ?>">📗 <?php echo $actividades[2]; ?></a>
            </div>

            <?php
            // Calcular estadísticas
            $total_estudiantes = count($todos_estudiantes);
            $total_entregas = count($entregas);
            $estudiantes_con_entrega = [];

            foreach ($entregas as $e) {
                $estudiantes_con_entrega[$e['estudiante_id']] = true;
            }
            $total_entregaron = count($estudiantes_con_entrega);
            $total_no_entregaron = $total_estudiantes - $total_entregaron;
            ?>

            <div class="resumen">
                <div class="resumen-item">
                    <div class="numero"><?php echo $total_estudiantes; ?></div>
                    <div class="label">👥 Total estudiantes</div>
                </div>
                <div class="resumen-item entregados">
                    <div class="numero">✅ <?php echo $total_entregaron; ?></div>
                    <div class="label">Entregaron al menos una tarea</div>
                </div>
                <div class="resumen-item pendientes">
                    <div class="numero">❌ <?php echo $total_no_entregaron; ?></div>
                    <div class="label">No han entregado ninguna</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Actividad</th>
                        <th>Archivo</th>
                        <th>Fecha de entrega</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $mostrar_actividad = $actividad_filtro;
                    $hay_filas = false;

                    foreach ($todos_estudiantes as $estudiante):
                        // Para cada actividad
                        foreach ($actividades as $act_id => $act_nombre):
                            if ($mostrar_actividad != 0 && $mostrar_actividad != $act_id) {
                                continue;
                            }
                            $hay_filas = true;
                            $key = $estudiante['id'] . '_' . $act_id;
                            $entrega = isset($entregas_por_estudiante[$key]) ? $entregas_por_estudiante[$key] : null;
                            $entregado = ($entrega !== null);
                    ?>
                        <tr>
                            <td><strong><?php echo $estudiante['nombre']; ?></strong></td>
                            <td><?php echo $act_nombre; ?></td>
                            <td>
                                <?php if ($entregado): ?>
                                    <?php echo $entrega['nombre_archivo']; ?>
                                <?php else: ?>
                                    <span class="texto-no-entregado">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($entregado): ?>
                                    <?php echo $entrega['fecha_entrega']; ?>
                                <?php else: ?>
                                    <span class="texto-no-entregado">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($entregado): ?>
                                    <span class="badge-entregado">✅ Entregado</span>
                                <?php else: ?>
                                    <span class="badge-no-entregado">❌ Sin entregar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($entregado): ?>
                                    <a href="<?php echo $entrega['ruta_archivo']; ?>" class="btn-descargar" download target="_blank">📥 Ver</a>
                                <?php else: ?>
                                    <span style="color:#999; font-size:13px;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php
                        endforeach;
                    endforeach;
                    if (!$hay_filas):
                    ?>
                        <tr>
                            <td colspan="6" style="text-align:center; color:#999; padding:30px;">
                                📭 No hay estudiantes registrados para esta actividad.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
