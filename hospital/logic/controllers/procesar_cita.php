<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/sesion_functions.php';
require_once __DIR__ . '/../middleware/rol_middleware.php';
require_once __DIR__ . '/../models/CitaModel.php';

requerirPaciente();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/views/agendar_cita.php');
    exit;
}

$usuario = obtenerUsuarioSesion();
$idPaciente = (int) ($usuario['id_paciente'] ?? 0);

if ($idPaciente <= 0) {
    setFlash('cita_error', 'Tu cuenta no tiene un perfil de paciente asociado.');
    header('Location: ' . BASE_URL . '/views/agendar_cita.php');
    exit;
}

// Plantilla de lectura desde $_POST (ajusta las claves si cambias el "name" en el formulario)
$datosCita = [
    'id_paciente'     => $idPaciente,
    'id_especialidad' => trim($_POST['id_especialidad'] ?? ''),
    'id_doctor'       => trim($_POST['id_doctor'] ?? ''),
    'id_servicio'     => trim($_POST['id_servicio'] ?? ''),
    'fecha_cita'      => trim($_POST['fecha_cita'] ?? ''),
    'hora_cita'       => trim($_POST['hora_cita'] ?? ''),
    'motivo_consulta' => trim($_POST['motivo_consulta'] ?? ''),
];

if (
    $datosCita['id_especialidad'] === '' ||
    $datosCita['id_doctor'] === '' ||
    $datosCita['fecha_cita'] === '' ||
    $datosCita['hora_cita'] === ''
) {
    setFlash('cita_error', 'Selecciona especialidad, doctor, fecha y horario de 30 minutos.');
    header('Location: ' . BASE_URL . '/views/agendar_cita.php');
    exit;
}

// Normaliza HH:MM a HH:MM:00 si viene sin segundos
if (preg_match('/^\d{2}:\d{2}$/', $datosCita['hora_cita'])) {
    $datosCita['hora_cita'] .= ':00';
}

try {
    $modelo = new CitaModel();
    $idCita = $modelo->crearCita($datosCita);

    setFlash(
        'cita_exito',
        "Cita #{$idCita} registrada en estado Programada (prepago pendiente). Tienes 8 horas para confirmar tu pago."
    );
    header('Location: ' . BASE_URL . '/views/mis_citas.php');
    exit;
} catch (PDOException $e) {
    $mensaje = $e->getMessage();
    if (preg_match('/\[SQL Server\](.+?)(?:\s*\(severity|\s*\[|$)/', $mensaje, $match)) {
        setFlash('cita_error', trim($match[1]));
    } else {
        setFlash('cita_error', 'No fue posible registrar la cita. Verifica las políticas y disponibilidad.');
    }
    header('Location: ' . BASE_URL . '/views/agendar_cita.php');
    exit;
} catch (Throwable $e) {
    setFlash('cita_error', 'Error inesperado al crear la cita.');
    header('Location: ' . BASE_URL . '/views/agendar_cita.php');
    exit;
}