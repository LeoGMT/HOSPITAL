
<?php

echo "<h2>Diagnóstico SQL Server</h2>";

echo "<p>PHP: " . PHP_VERSION . "</p>";

echo "<p>Controlador PDO disponibles:</p>";

echo "<pre>";
print_r(PDO::getAvailableDrivers());
echo "</pre>";

try {

    require_once __DIR__ . '/logic/config/conexion.php';

    $consulta = $conexion->query("
        SELECT
            DB_NAME() AS BaseDatos,
            SUSER_SNAME() AS UsuarioSQL
    ");

    $datos = $consulta->fetch();

    echo "<h3>Conexion exitosa</h3>";

    echo "<p>Base de datos: " .
         htmlspecialchars($datos['BaseDatos']) .
         "</p>";

    echo "<p>Usuario SQL: " .
         htmlspecialchars($datos['UsuarioSQL']) .
         "</p>";

} catch (Throwable $e) {

    echo "<h3>Error detectado</h3>";

    echo "<pre>";
    echo htmlspecialchars($e->getMessage());
    echo "</pre>";

}
?>
<p><a href="./views/login.php">login</a></p>
