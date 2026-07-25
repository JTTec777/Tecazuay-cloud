<?php
require_once 'config.php';
$titulo = 'Calendario Académico - TEC AZUAY';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$rol = $_SESSION['user_rol'];

// Fecha actual
$hoy = new DateTime();
$nombre_mes = [
    'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
    'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
    'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
    'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
];
$dia_semana = [
    'Sunday' => 'Domingo', 'Monday' => 'Lunes', 'Tuesday' => 'Martes',
    'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado'
];

$fecha_texto = $dia_semana[$hoy->format('l')] . ', ' . $hoy->format('j') . ' de ' . $nombre_mes[$hoy->format('F')] . ' de ' . $hoy->format('Y');

// Calendario del mes actual
$anio = (int)$hoy->format('Y');
$mes = (int)$hoy->format('n');
$primer_dia = new DateTime("$anio-$mes-01");
$dias_en_mes = (int)$hoy->format('t');
$dia_inicio_semana = (int)$primer_dia->format('w'); // 0=Domingo
$hoy_numero = (int)$hoy->format('j');

$nombres_meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
?>
<style>
    .fecha-hoy { background: linear-gradient(135deg, #1a237e 0%, #0d1457 100%); color: white; padding: 25px 30px; border-radius: 16px; text-align: center; margin-bottom: 25px; box-shadow: 0 8px 30px rgba(26,35,126,0.25); }
    .fecha-hoy .dia-nombre { font-size: 14px; text-transform: uppercase; letter-spacing: 3px; opacity: 0.8; margin-bottom: 8px; }
    .fecha-hoy .dia-numero { font-size: 56px; font-weight: 800; line-height: 1; margin-bottom: 5px; }
    .fecha-hoy .mes-anio { font-size: 16px; opacity: 0.9; }
    .calendario { background: white; border-radius: 16px; padding: 25px; box-shadow: 0 4px 20px rgba(26,35,126,0.06); max-width: 500px; margin: 0 auto; }
    .calendario-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .calendario-header h3 { color: #1a237e; font-size: 20px; }
    .dias-semana { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; margin-bottom: 10px; }
    .dia-label { text-align: center; font-size: 12px; font-weight: 700; color: #888; text-transform: uppercase; }
    .dias-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }
    .dia-celda { aspect-ratio: 1; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 14px; font-weight: 600; color: #333; background: #f8f9ff; }
    .dia-celda.hoy { background: #1a237e; color: white; box-shadow: 0 4px 12px rgba(26,35,126,0.3); }
    .dia-celda.vacio { background: transparent; }
</style>

<!-- Fecha de hoy -->
<div class="fecha-hoy">
    <div class="dia-nombre"><?php echo $dia_semana[$hoy->format('l')]; ?></div>
    <div class="dia-numero"><?php echo $hoy->format('j'); ?></div>
    <div class="mes-anio"><?php echo $nombres_meses[$mes] . ' ' . $anio; ?></div>
</div>

<!-- Calendario mensual -->
<div class="calendario">
    <div class="calendario-header">
        <h3>📅 <?php echo $nombres_meses[$mes] . ' ' . $anio; ?></h3>
    </div>
    <div class="dias-semana">
        <div class="dia-label">Dom</div>
        <div class="dia-label">Lun</div>
        <div class="dia-label">Mar</div>
        <div class="dia-label">Mié</div>
        <div class="dia-label">Jue</div>
        <div class="dia-label">Vie</div>
        <div class="dia-label">Sáb</div>
    </div>
    <div class="dias-grid">
        <?php
        // Espacios vacíos antes del día 1
        for ($i = 0; $i < $dia_inicio_semana; $i++) {
            echo '<div class="dia-celda vacio"></div>';
        }
        // Días del mes
        for ($d = 1; $d <= $dias_en_mes; $d++) {
            $clase = ($d == $hoy_numero) ? 'dia-celda hoy' : 'dia-celda';
            echo "<div class=\"$clase\">$d</div>";
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
