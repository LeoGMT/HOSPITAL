<?php

require_once __DIR__ .
    "/../middleware/verificarSesion.php";

include "nav.php";

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inicio</title>
</head>

<body>

<h1>
    Bienvenido
    <?= htmlspecialchars($_SESSION["nombre"]) ?>
</h1>

<p>
    Tipo de usuario:
    <?= htmlspecialchars($_SESSION["rol"]) ?>
</p>

<a href="../controllers/LogoutController.php">
    Cerrar sesión
</a>

</body>
</html>