<?php
require_once 'config.php';
require_once 'languages.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$rol = $_SESSION['user_rol'];
$nombre_usuario = strtoupper($_SESSION['user_nombre']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario Académico - TEC AZUAY</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard-header {
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

        .header-left h1 {
            color: #1a237e;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .header-left .subtitle {
            color: #888;
            font-size: 12px;
            letter-spacing: 3px;
            margin-left: 12px;
            font-weight: 600;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-name {
            color: #1a237e;
            font-weight: 700;
            font-size: 15px;
        }

        .btn-logout {
            background: #dc3545;
            color: white;
            padding: 8px 22px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-logout:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .btn-back {
            background: #6c757d;
            color: white;
            padding: 8px 22px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-back:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .calendar-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .calendar-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 25px rgba(26, 35, 126, 0.06);
            border: 1px solid rgba(26, 35, 126, 0.04);
        }

        .calendar-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e8eaf6;
        }

        .calendar-card .card-header h3 {
            color: #1a237e;
            font-size: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .calendar-card .card-header .badge {
            background: #dcc97a;
            color: #1a237e;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }

        .calendar-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .calendar-nav h2 {
            color: #1a237e;
            font-size: 22px;
            font-weight: 700;
            min-width: 180px;
            text-align: center;
        }

        .calendar-nav .nav-btn {
            background: #e8eaf6;
            color: #1a237e;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 700;
            transition: 0.3s;
        }

        .calendar-nav .nav-btn:hover {
            background: #1a237e;
            color: white;
            transform: scale(1.05);
        }

        .calendar-table {
            width: 100%;
            border-collapse: collapse;
        }

        .calendar-table th {
            padding: 12px 8px;
            background: #f8f9ff;
            color: #1a237e;
            font-weight: 700;
            font-size: 13px;
            text-align: center;
            border-radius: 8px;
        }

        .calendar-table td {
            padding: 12px 8px;
            text-align: center;
            border: 1px solid #edf0f7;
            height: 65px;
            vertical-align: top;
            font-size: 15px;
            font-weight: 500;
            color: #1a237e;
            border-radius: 4px;
            transition: 0.2s;
            cursor: default;
        }

        .calendar-table td.empty {
            background: #fafbfc;
            border-color: #f0f2f7;
        }

        .calendar-table td.today {
            background: #e8eaf6;
            font-weight: 700;
            color: #1a237e;
            border-color: #1a237e;
            border-width: 2px;
        }

        .calendar-table td.has-event {
            background: #fff8f0;
            border-color: #dcc97a;
            cursor: pointer;
            font-weight: 700;
            color: #1a237e;
        }

        .calendar-table td.has-event:hover {
            background: #dcc97a;
            color: #1a237e;
            transform: scale(1.02);
        }

        .calendar-table td .event-dot {
            display: block;
            width: 8px;
            height: 8px;
            background: #dcc97a;
            border-radius: 50%;
            margin: 4px auto 0;
            box-shadow: 0 2px 6px rgba(220, 201, 122, 0.5);
        }

        .calendar-table td .day-number {
            display: block;
            font-size: 15px;
            font-weight: 600;
        }

        .event-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-height: 600px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .event-list::-webkit-scrollbar {
            width: 6px;
        }

        .event-list::-webkit-scrollbar-track {
            background: #f0f2f5;
            border-radius: 10px;
        }

        .event-list::-webkit-scrollbar-thumb {
            background: #dcc97a;
            border-radius: 10px;
        }

        .event-item {
            background: #fafbff;
            border-left: 5px solid #1a237e;
            padding: 20px 22px;
            border-radius: 12px;
            transition: 0.3s;
            border: 1px solid #edf0f7;
        }

        .event-item:hover {
            background: #f5f7ff;
            transform: translateX(6px);
            border-color: #dcc97a;
        }

        .event-item .event-type {
            display: inline-block;
            background: #dcc97a;
            color: #1a237e;
            padding: 2px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .event-item .event-title {
            font-weight: 700;
            color: #1a237e;
            font-size: 17px;
            margin-bottom: 4px;
        }

        .event-item .event-course {
            color: #666;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .event-item .event-date {
            color: #dcc97a;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .event-item .event-desc {
            color: #444;
            font-size: 14px;
            line-height: 1.7;
            margin-top: 6px;
            padding: 12px 16px;
            background: white;
            border-radius: 10px;
            border: 1px solid #edf0f7;
        }

        .event-item .event-instrucciones {
            background: #f8f9ff;
            padding: 14px 18px;
            border-radius: 10px;
            margin-top: 10px;
            font-size: 13px;
            line-height: 1.8;
            color: #333;
            border: 1px solid #e8eaf6;
            white-space: pre-line;
        }

        .event-item .event-instrucciones strong {
            color: #1a237e;
        }

        .btn-go {
            display: inline-block;
            background: #1a237e;
            color: white;
            padding: 8px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            margin-top: 12px;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-go:hover {
            background: #0d1457;
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(26, 35, 126, 0.3);
        }

        @media (max-width: 1024px) {
            .calendar-wrapper {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
                padding: 18px;
            }

            .header-right {
                flex-wrap: wrap;
                justify-content: center;
            }

            .calendar-card {
                padding: 18px;
            }

            .calendar-nav h2 {
                font-size: 18px;
                min-width: 120px;
            }

            .calendar-table td {
                height: 50px;
                font-size: 13px;
                padding: 6px 4px;
            }

            .calendar-table th {
                font-size: 11px;
                padding: 8px 4px;
            }

            .event-item {
                padding: 16px;
            }
        }

        @media (max-width: 480px) {
            .dashboard-container {
                padding: 10px;
            }

            .calendar-table td {
                height: 40px;
                font-size: 11px;
                padding: 4px 2px;
            }

            .calendar-table th {
                font-size: 10px;
                padding: 6px 2px;
            }

            .calendar-nav .nav-btn {
                padding: 6px 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- HEADER -->
        <header class="dashboard-header">
            <div class="header-left">
                <h1>TEC AZUAY <span class="subtitle">INSTITUTO UNIVERSITARIO</span></h1>
            </div>
            <div class="header-right">
                <span class="user-name">👋 <?php echo $nombre_usuario; ?></span>
                <a href="<?php echo ($rol == 'estudiante') ? 'dashboard_estudiante.php' : 'dashboard_profesor.php'; ?>" class="btn-back">← Volver</a>
                <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>

        <!-- CALENDARIO -->
        <div class="calendar-wrapper">
            <!-- CALENDARIO VISUAL -->
            <div class="calendar-card">
                <div class="card-header">
                    <h3>📅 Calendario Académico</h3>
                    <span class="badge" id="yearBadge">2026</span>
                </div>

                <div class="calendar-nav">
                    <button class="nav-btn" onclick="cambiarMes(-1)">◀</button>
                    <h2 id="mesTitulo">Junio 2026</h2>
                    <button class="nav-btn" onclick="cambiarMes(1)">▶</button>
                </div>

                <table class="calendar-table">
                    <thead>
                        <tr>
                            <th>Dom</th>
                            <th>Lun</th>
                            <th>Mar</th>
                            <th>Mié</th>
                            <th>Jue</th>
                            <th>Vie</th>
                            <th>Sáb</th>
                        </tr>
                    </thead>
                    <tbody id="calendarioBody">
                        <!-- Generado por JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- PRÓXIMOS EVENTOS -->
            <div class="calendar-card">
                <div class="card-header">
                    <h3>📋 Próximos Eventos</h3>
                    <span class="badge" id="eventCount">2 eventos</span>
                </div>

                <div class="event-list" id="eventList">
                    <!-- Generado por JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Datos de eventos con IDs
        const eventos = {
            '2026-06-23': {
                id: 1,
                titulo: 'Inventario, Clasificación de Activos y Protección de Datos Personales',
                curso: 'CIBERSEGURIDAD EN LA NUBE',
                fecha: 'Tuesday, 23 June, 12:00 AM',
                tipo: 'Activity event',
                descripcion: 'Objetivo: Aplicar los conceptos de Gestión de Activos de Información y Protección de Datos Personales mediante la identificación, clasificación y valoración de activos institucionales, utilizando criterios de Confidencialidad, Integridad y Disponibilidad (CID), así como los principios establecidos en el Esquema Gubernamental de Seguridad de la Información (EGSI v3) y la Ley Orgánica de Protección de Datos Personales (LOPDP).',
                instrucciones: 'Identifique al menos diez (10) activos de información que existen en la empresa.\nConsidere activos de los siguientes tipos: Tecnológicos, Información, Físicos, Administrativos.\nDe los cuales debe realizar el: Inventario de Activos de Información, Clasificación y Valoración de Activos, Identificación de Datos Personales, Análisis de Criterios, Reflexión Final.'
            },
            '2026-07-02': {
                id: 2,
                titulo: 'GUÍA PRÁCTICA UNIDAD 3',
                curso: 'HACKEO ETICO LABORATORIO',
                fecha: 'Thursday, 2 July, 11:59 PM',
                tipo: 'Activity event',
                descripcion: 'Resolver la guía práctica adjunta y subir el informe en un archivo pdf.'
            }
        };

        // Estado actual
        let mesActual = 6;
        let anoActual = 2026;

        // Nombres
        const nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        const nombresDias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

        function generarCalendario(mes, ano) {
            const primerDia = new Date(ano, mes - 1, 1);
            const ultimoDia = new Date(ano, mes, 0);
            const diasEnMes = ultimoDia.getDate();
            const diaSemanaInicio = primerDia.getDay();

            let html = '<tr>';
            for (let i = 0; i < diaSemanaInicio; i++) {
                html += '<td class="empty"></td>';
            }

            const hoy = new Date();
            const hoyStr = hoy.getFullYear() + '-' + String(hoy.getMonth() + 1).padStart(2, '0') + '-' + String(hoy.getDate()).padStart(2, '0');

            for (let dia = 1; dia <= diasEnMes; dia++) {
                const fechaStr = ano + '-' + String(mes).padStart(2, '0') + '-' + String(dia).padStart(2, '0');
                const esHoy = fechaStr === hoyStr;
                const tieneEvento = eventos[fechaStr] !== undefined;

                let clase = '';
                if (esHoy) clase = 'today';
                if (tieneEvento) clase += ' has-event';

                html += '<td class="' + clase.trim() + '" onclick="' + (tieneEvento ? 'scrollToEvent(\'' + fechaStr + '\')' : '') + '">';
                html += '<span class="day-number">' + dia + '</span>';
                if (tieneEvento) {
                    html += '<span class="event-dot"></span>';
                }
                html += '</td>';

                if ((diaSemanaInicio + dia) % 7 === 0 || dia === diasEnMes) {
                    html += '</tr>';
                    if (dia < diasEnMes) {
                        html += '<tr>';
                    }
                }
            }

            // Rellenar última fila si es necesario
            const celdasRestantes = (7 - ((diaSemanaInicio + diasEnMes) % 7)) % 7;
            for (let i = 0; i < celdasRestantes; i++) {
                html += '<td class="empty"></td>';
            }

            document.getElementById('calendarioBody').innerHTML = html;
            document.getElementById('mesTitulo').textContent = nombresMeses[mes - 1] + ' ' + ano;
            document.getElementById('yearBadge').textContent = ano;
        }

        function generarEventos() {
            let html = '';
            let contador = 0;

            // Ordenar eventos por fecha
            const fechasOrdenadas = Object.keys(eventos).sort();

            for (const fecha of fechasOrdenadas) {
                const ev = eventos[fecha];
                contador++;
                html += `
                    <div class="event-item" id="event-${fecha}">
                        <span class="event-type">${ev.tipo}</span>
                        <div class="event-title">${ev.titulo}</div>
                        <div class="event-course">📘 ${ev.curso}</div>
                        <div class="event-date">📅 ${ev.fecha}</div>
                        <div class="event-desc">${ev.descripcion.replace(/\n/g, '<br>')}</div>
                        ${ev.instrucciones ? `<div class="event-instrucciones"><strong>📌 Instrucciones:</strong><br>${ev.instrucciones.replace(/\n/g, '<br>')}</div>` : ''}
                        <a href="actividad.php?id=${ev.id}" class="btn-go">Ver actividad →</a>
                    </div>
                `;
            }

            document.getElementById('eventList').innerHTML = html;
            document.getElementById('eventCount').textContent = contador + ' eventos';
        }

        function cambiarMes(delta) {
            mesActual += delta;
            if (mesActual < 1) {
                mesActual = 12;
                anoActual--;
            }
            if (mesActual > 12) {
                mesActual = 1;
                anoActual++;
            }
            generarCalendario(mesActual, anoActual);
        }

        function scrollToEvent(fecha) {
            const elemento = document.getElementById('event-' + fecha);
            if (elemento) {
                elemento.scrollIntoView({ behavior: 'smooth', block: 'center' });
                elemento.style.background = '#fff8e1';
                setTimeout(() => {
                    elemento.style.background = '';
                }, 2000);
            }
        }

        // Inicializar
        generarCalendario(mesActual, anoActual);
        generarEventos();
    </script>
</body>
</html>

