<?php
session_start();
require_once __DIR__ . '/../logic/config/config.php';

if (empty($_SESSION['usuario'])) {
    header('Location: ' . BASE_URL . '/views/login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Panel del Paciente - <?= htmlspecialchars(APP_NAME) ?></title>
</head>
<body>
    <main class="dashboard-container">
        <h1>Bienvenido, <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['ape_paterno']) ?></h1>
        <p><strong>Rol:</strong> <?= htmlspecialchars($usuario['rol']) ?></p>
        <p><strong>Correo:</strong> <?= htmlspecialchars($usuario['correo']) ?></p>
        <p><strong>ID Paciente:</strong> <?= htmlspecialchars((string) ($usuario['id_paciente'] ?? 'N/A')) ?></p>

        <nav>
            <a href="<?= htmlspecialchars(BASE_URL) ?>/views/login.php">Volver al login</a>
        </nav>
    </main>
</body>
</html>