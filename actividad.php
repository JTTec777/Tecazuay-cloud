<?php
require_once 'config.php';
require_once 'languages.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'estudiante') {
    header('Location: index.php');
    exit();
}

$nombre_usuario = strtoupper($_SESSION['user_nombre']);
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$actividades = [
    1 => [
        'titulo' => 'Inventario, Clasificación de Activos y Protección de Datos Personales',
        'curso' => 'CIBERSEGURIDAD EN LA NUBE',
        'maximo_datos' => 'Maximo de datos',
        'opened' => 'Tuesday, 16 June 2024, 12:00 AM',
        'due' => 'Tuesday, 22 June 2024, 12:00 AM',
        'objetivo' => 'Aplicar los conceptos de Gestión de Activos de Información y Protección de Datos Personales mediante la identificación, clasificación y valoración de activos institucionales, utilizando criterios de Confidencialidad, Integridad y Disponibilidad (CID), así como los principios establecidos en el Esquema Gubernamental de Seguridad de la Información (EGSI v3) y la Ley Orgánica de Protección de Datos Personales (LOPDP).',
        'instrucciones' => [
            'Identifique al menos diez (10) activos de información que existen en la empresa.',
            'Considere activos de los siguientes tipos: Tecnológicos, Información, Físicos, Administrativos.',
            'De los cuales debe realizar el: Inventario de Activos de Información, Clasificación y Valoración de Activos, Identificación de Datos Personales, Análisis de Criterios, Reflexión Final.'
        ],
        'max_file_size' => '512 MB',
        'max_files' => 20
    ],
    2 => [
        'titulo' => 'GUÍA PRÁCTICA UNIDAD 3',
        'curso' => 'HACKEO ETICO LABORATORIO',
        'maximo_datos' => '',
        'opened' => 'Wednesday, 10 June 2026, 12:00 AM',
        'due' => 'Thursday, 2 July 2026, 11:59 PM',
        'objetivo' => 'Resolver la guía práctica adjunta y subir el informe en un archivo pdf.',
        'instrucciones' => [
            'Resolver la guía práctica adjunta y subir el informe en un archivo pdf.',
            'HACKE_V3A_GUIA2.pdf - 10 June 2026, 10:25 AM'
        ],
        'max_file_size' => '512 MB',
        'max_files' => 1,
        'archivo_adjunto' => 'HACKE_V3A_GUIA2.pdf'
    ]
];

if (!isset($actividades[$id])) {
    header('Location: calendar.php');
    exit();
}

$actividad = $actividades[$id];
$mensaje = '';
$error = '';

if (isset($_GET['exito'])) {
    $mensaje = '✅ ' . $_GET['exito'];
}
if (isset($_GET['error'])) {
    $error = '❌ ' . $_GET['error'];
}

// Obtener entrega del estudiante
$stmt = $pdo->prepare("SELECT * FROM entregas WHERE actividad_id = ? AND estudiante_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$entrega = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $actividad['titulo']; ?> - TEC AZUAY</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }

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

        .card { background: white; border-radius: 20px; padding: 35px; box-shadow: 0 4px 25px rgba(26, 35, 126, 0.06); border: 1px solid rgba(26, 35, 126, 0.04); margin-bottom: 25px; }
        .card h2 { color: #1a237e; font-size: 24px; font-weight: 700; margin-bottom: 5px; }
        .card .curso { color: #666; font-size: 16px; font-weight: 600; margin-bottom: 20px; }
        .card .maximo-datos { color: #1a237e; font-weight: 700; font-size: 14px; margin-bottom: 15px; background: #e8eaf6; padding: 6px 16px; border-radius: 20px; display: inline-block; }
        .fechas { display: flex; gap: 30px; margin-bottom: 25px; flex-wrap: wrap; }
        .fechas .fecha-item { font-size: 14px; }
        .fechas .fecha-item strong { color: #1a237e; }
        .objetivo { background: #f8f9ff; padding: 20px 24px; border-radius: 12px; border-left: 4px solid #1a237e; margin-bottom: 25px; }
        .objetivo h4 { color: #1a237e; font-size: 16px; margin-bottom: 8px; }
        .objetivo p { color: #444; font-size: 15px; line-height: 1.7; }
        .instrucciones { margin-bottom: 25px; }
        .instrucciones h4 { color: #1a237e; font-size: 16px; margin-bottom: 10px; }
        .instrucciones ul { list-style: none; padding: 0; }
        .instrucciones ul li { padding: 8px 0 8px 24px; position: relative; color: #444; font-size: 15px; line-height: 1.6; }
        .instrucciones ul li::before { content: "•"; color: #dcc97a; font-size: 20px; position: absolute; left: 0; top: 6px; }

        .mensaje { padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; }
        .mensaje-exito { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
        .mensaje-error { background: #ffebee; color: #c62828; border-left: 4px solid #c62828; }

        .submission-area { border-top: 2px solid #e8eaf6; padding-top: 25px; margin-top: 10px; }
        .submission-area h3 { color: #1a237e; font-size: 18px; margin-bottom: 15px; }
        .submission-area .file-info { color: #666; font-size: 14px; margin-bottom: 5px; }

        .drop-zone {
            border: 3px dashed #dcc97a;
            border-radius: 16px;
            padding: 40px 20px;
            text-align: center;
            background: #fafbff;
            margin-bottom: 20px;
            transition: 0.3s;
            cursor: pointer;
        }
        .drop-zone:hover { background: #f5f7ff; border-color: #1a237e; }
        .drop-zone.dragover { background: #e8eaf6; border-color: #1a237e; }
        .drop-zone p { color: #666; font-size: 16px; }
        .drop-zone .file-types { color: #999; font-size: 13px; margin-top: 8px; }
        .drop-zone input[type="file"] { display: none; }

        .btn-group { display: flex; gap: 15px; margin-top: 20px; flex-wrap: wrap; }
        .btn-save { background: #1a237e; color: white; padding: 12px 40px; border: none; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-save:hover { background: #0d1457; transform: translateY(-2px); }
        .btn-cancel { background: #e8eaf6; color: #1a237e; padding: 12px 40px; border: none; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-cancel:hover { background: #d5d9e8; }

        .archivo-subido {
            background: #e8f5e9;
            border: 2px solid #4caf50;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .archivo-subido .check { font-size: 28px; }
        .archivo-subido .info { flex: 1; min-width: 150px; }
        .archivo-subido .info .nombre { font-weight: 600; color: #1a237e; }
        .archivo-subido .info .fecha { font-size: 13px; color: #666; }
        .archivo-subido .btn-descargar { background: #1a237e; color: white; padding: 6px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; transition: 0.3s; }
        .archivo-subido .btn-descargar:hover { background: #0d1457; }
        .archivo-subido .btn-eliminar { background: #dc3545; color: white; padding: 6px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; transition: 0.3s; border: none; cursor: pointer; }
        .archivo-subido .btn-eliminar:hover { background: #c82333; transform: scale(1.05); }

        .subido-badge {
            display: inline-block;
            background: #4caf50;
            color: white;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            margin-top: 10px;
        }

        .archivo-adjunto {
            background: #f5f7ff;
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid #e8eaf6;
        }
        .archivo-adjunto a {
            color: #1a237e;
            font-weight: 600;
            text-decoration: none;
        }
        .archivo-adjunto a:hover {
            text-decoration: underline;
        }

        /* Modal de confirmación */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal-box {
            background: white;
            border-radius: 16px;
            padding: 30px 35px;
            max-width: 420px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalFadeIn 0.3s ease;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95) translateY(-10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .modal-box h3 { color: #1a237e; font-size: 20px; margin-bottom: 10px; }
        .modal-box p { color: #666; font-size: 15px; margin-bottom: 20px; }
        .modal-box .btn-modal-eliminar { background: #dc3545; color: white; padding: 10px 30px; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: 0.3s; margin-right: 10px; }
        .modal-box .btn-modal-eliminar:hover { background: #c82333; transform: scale(1.05); }
        .modal-box .btn-modal-cancelar { background: #e8eaf6; color: #1a237e; padding: 10px 30px; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .modal-box .btn-modal-cancelar:hover { background: #d5d9e8; }

        @media (max-width: 768px) {
            .header { flex-direction: column; gap: 15px; text-align: center; padding: 18px; }
            .header-right { justify-content: center; }
            .card { padding: 20px; }
            .fechas { flex-direction: column; gap: 10px; }
            .btn-group { flex-direction: column; }
            .btn-save, .btn-cancel { width: 100%; text-align: center; }
            .archivo-subido { flex-direction: column; text-align: center; }
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
                <span class="user-name">👋 <?php echo $nombre_usuario; ?></span>
                <a href="calendar.php" class="btn-back">← Volver</a>
                <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>

        <?php if ($mensaje): ?>
            <div class="mensaje mensaje-exito"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mensaje mensaje-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <h2><?php echo $actividad['titulo']; ?></h2>
            <div class="curso"><?php echo $actividad['curso']; ?></div>

            <?php if (!empty($actividad['maximo_datos'])): ?>
                <div class="maximo-datos"><?php echo $actividad['maximo_datos']; ?></div>
            <?php endif; ?>

            <div class="fechas">
                <div class="fecha-item"><strong>Opened:</strong> <?php echo $actividad['opened']; ?></div>
                <div class="fecha-item"><strong>Due:</strong> <?php echo $actividad['due']; ?></div>
            </div>

            <div class="objetivo">
                <h4>Objetivo:</h4>
                <p><?php echo $actividad['objetivo']; ?></p>
            </div>

            <div class="instrucciones">
                <h4>Instrucciones:</h4>
                <ul>
                    <?php foreach($actividad['instrucciones'] as $inst): ?>
                        <li><?php echo $inst; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php if (isset($actividad['archivo_adjunto'])): ?>
                <div class="archivo-adjunto">
                    📎 <a href="#"><?php echo $actividad['archivo_adjunto']; ?></a>
                    <span style="color:#999; font-size:13px; margin-left:10px;">10 June 2026, 10:25 AM</span>
                </div>
            <?php endif; ?>

            <!-- ARCHIVO YA SUBIDO -->
            <?php if ($entrega): ?>
                <div class="archivo-subido">
                    <span class="check">✅</span>
                    <div class="info">
                        <div class="nombre">📄 <?php echo $entrega['nombre_archivo']; ?></div>
                        <div class="fecha">📅 Entregado: <?php echo $entrega['fecha_entrega']; ?></div>
                        <span class="subido-badge">✅ Entregado</span>
                    </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <a href="<?php echo $entrega['ruta_archivo']; ?>" class="btn-descargar" download target="_blank">📥 Descargar</a>
                        <button onclick="confirmarEliminar(<?php echo $id; ?>)" class="btn-eliminar">🗑️ Eliminar</button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- SUBIR ARCHIVO (siempre visible) -->
            <div class="submission-area">
                <h3>Add submission</h3>
                <p class="file-info">File submissions</p>
                <p class="file-info">Maximum file size: <?php echo $actividad['max_file_size']; ?>, maximum number of files: <?php echo $actividad['max_files']; ?></p>

                <form action="subir_archivo.php" method="POST" enctype="multipart/form-data" id="formSubir">
                    <input type="hidden" name="actividad_id" value="<?php echo $id; ?>">
                    <div class="drop-zone" id="dropZone">
                        <p>📁 Arrastra y suelta tu archivo aquí</p>
                        <p style="font-size:14px; color:#999;">o haz clic para seleccionar</p>
                        <p class="file-types">Tipos permitidos: .doc, .docx, .pdf</p>
                        <input type="file" name="archivo" id="archivoInput" accept=".doc,.docx,.pdf" required>
                    </div>
                    <div class="btn-group">
                        <button type="submit" class="btn-save">Guardar cambios</button>
                        <button type="reset" class="btn-cancel">Cancelar</button>
                    </div>
                </form>
                <?php if ($entrega): ?>
                    <p style="color:#999; font-size:13px; margin-top:10px;">💡 Si subes un nuevo archivo, reemplazará al anterior automáticamente.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div class="modal-overlay" id="modalEliminar">
        <div class="modal-box">
            <h3>⚠️ ¿Eliminar archivo?</h3>
            <p>¿Estás seguro de que deseas eliminar este archivo? Esta acción no se puede deshacer.</p>
            <div>
                <button class="btn-modal-eliminar" id="btnConfirmarEliminar">Sí, eliminar</button>
                <button class="btn-modal-cancelar" onclick="cerrarModal()">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('archivoInput');

        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                const p = dropZone.querySelector('p');
                p.textContent = '📄 ' + files[0].name;
            }
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                const p = dropZone.querySelector('p');
                p.textContent = '📄 ' + fileInput.files[0].name;
            }
        });

        // Modal de confirmación para eliminar
        let actividadIdEliminar = 0;

        function confirmarEliminar(actividadId) {
            actividadIdEliminar = actividadId;
            document.getElementById('modalEliminar').classList.add('active');
        }

        function cerrarModal() {
            document.getElementById('modalEliminar').classList.remove('active');
            actividadIdEliminar = 0;
        }

        document.getElementById('btnConfirmarEliminar').addEventListener('click', function() {
            if (actividadIdEliminar > 0) {
                window.location.href = 'eliminar_archivo.php?actividad_id=' + actividadIdEliminar;
            }
        });

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalEliminar').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });
    </script>
</body>
</html>
