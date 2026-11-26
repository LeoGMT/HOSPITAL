<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/sesion_functions.php';
require_once __DIR__ . '/../middleware/rol_middleware.php';
require_once __DIR__ . '/../models/CitaModel.php';

requerirPaciente();

header('Content-Type: application/json; charset=utf-8');

$idDoctor = isset($_GET['id_doctor']) ? (int) $_GET['id_doctor'] : 0;
$start    = substr((string) ($_GET['start'] ?? date('Y-m-d')), 0, 10);
$end      = substr((string) ($_GET['end'] ?? date('Y-m-d', strtotime('+3 months'))), 0, 10);

if ($idDoctor <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Doctor no válido']);
    exit;
}

try {
    $modelo = new CitaModel();
    $ocupados = $modelo->obtenerHorariosOcupados($idDoctor, $start, $end);
    echo json_encode(['ocupados' => $ocupados]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'No fue posible cargar la disponibilidad del doctor']);
}