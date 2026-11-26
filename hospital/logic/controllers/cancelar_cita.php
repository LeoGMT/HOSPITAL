<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/sesion_functions.php';
require_once __DIR__ . '/../middleware/rol_middleware.php';
require_once __DIR__ . '/../models/CitaModel.php';

requerirPaciente();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/views/mis_citas.php');
    exit;
}

$usuario = obtenerUsuarioSesion();
$idPaciente = (int) ($usuario['id_paciente'] ?? 0);
$idCita     = isset($_POST['id_cita']) ? (int) $_POST['id_cita'] : 0;

if ($idPaciente <= 0 || $idCita <= 0) {
    setFlash('cita_error', 'Solicitud de cancelación no válida.');
    header('Location: ' . BASE_URL . '/views/mis_citas.php');
    exit;
}

try {
    $modelo = new CitaModel();
    $porcentaje = $modelo->cancelarCitaPaciente($idCita, $idPaciente);

    setFlash(
        'cita_exito',
        "Cita #{$idCita} cancelada. Reembolso aplicable según política de cancelación: {$porcentaje}%."
    );
    header('Location: ' . BASE_URL . '/views/mis_citas.php');
    exit;
} catch (PDOException $e) {
    $mensaje = $e->getMessage();
    if (preg_match('/\[SQL Server\](.+?)(?:\s*\(severity|\s*\[|$)/', $mensaje, $match)) {
        setFlash('cita_error', trim($match[1]));
    } else {
        setFlash('cita_error', 'No fue posible cancelar la cita.');
    }
    header('Location: ' . BASE_URL . '/views/mis_citas.php');
    exit;
} catch (Throwable $e) {
    setFlash('cita_error', 'Error inesperado al cancelar la cita.');
    header('Location: ' . BASE_URL . '/views/mis_citas.php');
    exit;
}