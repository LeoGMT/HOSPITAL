
<?php

require_once __DIR__ . '/logic/config/conexion.php';

try {

    $consulta = $conexion->query("
        SELECT
            DB_NAME() AS BaseDatos,
            SUSER_SNAME() AS UsuarioWindows,
            GETDATE() AS FechaServidor
    ");

    $resultado = $consulta->fetch();

    echo "<h1>Conexión exitosa</h1>";

    echo "<p>Base de datos: " .
         htmlspecialchars($resultado['BaseDatos']) .
         "</p>";

    echo "<p>Usuario: " .
         htmlspecialchars($resultado['UsuarioWindows']) .
         "</p>";

    echo "<p>Fecha: " .
         htmlspecialchars($resultado['FechaServidor']->format('Y-m-d H:i:s')) .
         "</p>";

} catch (PDOException $e) {

    echo "Error en la consulta: " .
         htmlspecialchars($e->getMessage());

}

?>
