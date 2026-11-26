<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../controllers/RegistroController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../registro.html');
    exit;
}

$controller = new RegistroController();
$resultado = $controller->registrar($_POST);

if ($resultado['ok']) {
    $_SESSION['registro_exito'] = $resultado['mensaje'];
    header('Location: .../views/login.php');
    exit;
}

$_SESSION['registro_error'] = $resultado['mensaje'];
header('Location: .../views/registro.php');
exit;