<?php
require_once 'config.php';

$usuarios = [
    'Sami' => 'sami1234',
    'Timo' => 'timo1234',
    'Edi' => 'edi1234',
    'Mayte' => 'mayte1234',
    'Luis' => 'ingeluis',
    'Boris' => 'borisbros'
];

echo "Actualizando contraseñas...\n";

foreach ($usuarios as $usuario => $pass) {
    $hash = hashPassword($pass);
    $stmt = $pdo->prepare("UPDATE usuarios SET contrasena = ? WHERE usuario = ?");
    if ($stmt->execute([$hash, $usuario])) {
        echo "✅ Usuario $usuario actualizado\n";
    } else {
        echo "❌ Error al actualizar $usuario\n";
    }
}

echo "\n✅ Todas las contraseñas han sido actualizadas.\n";
echo "Usa las credenciales originales para probar:\n";
foreach ($usuarios as $usuario => $pass) {
    echo "   $usuario / $pass\n";
}
?>
