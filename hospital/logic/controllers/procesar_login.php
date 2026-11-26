<?php

session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/views/login.php');
    exit;
}

$tipoAcceso    = trim($_POST['tipo_acceso'] ?? 'correo');
$identificador = trim($_POST['identificador'] ?? '');
$passwordPlano = $_POST['password'] ?? '';

if (!in_array($tipoAcceso, ['correo', 'cedula'], true)) {
    $_SESSION['login_error'] = 'Tipo de acceso no válido.';
    header('Location: ' . BASE_URL . '/views/login.php');
    exit;
}

if ($identificador === '' || $passwordPlano === '') {
    $_SESSION['login_error'] = 'Ingresa tu identificador y contraseña.';
    header('Location: ' . BASE_URL . '/views/login.php');
    exit;
}

try {
    $modelo = new UsuarioModel();
    $usuario = $modelo->autenticar($tipoAcceso, $identificador, $passwordPlano);

    if (!$usuario) {
        $_SESSION['login_error'] = 'Credenciales incorrectas o cuenta inactiva.';
        header('Location: ' . BASE_URL . '/views/login.php');
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['usuario'] = [
        'id_usuario'       => (int) $usuario['id_usuario'],
        'id_tipo_rol'      => (int) $usuario['id_tipo_rol'],
        'rol'              => $usuario['nombre_rol'],
        'nombre'           => $usuario['nombre'],
        'ape_paterno'      => $usuario['ape_paterno'],
        'ape_materno'      => $usuario['ape_materno'],
        'correo'           => $usuario['correo'],
        'id_doctor'        => $usuario['id_doctor'] ?? null,
        'cedula'           => $usuario['cedula_profesional'] ?? null,
        'id_paciente'      => $usuario['id_paciente'] ?? null,
        'id_admin'         => $usuario['id_admin'] ?? null,
        'id_recepcionista' => $usuario['id_recepcionista'] ?? null,
    ];

    // Redirección según rol dentro de /views
    switch ($usuario['nombre_rol']) {
        case 'Administrador':
            header('Location: ' . BASE_URL . '/views/admin_dashboard.php');
            break;
        case 'Doctor':
            header('Location: ' . BASE_URL . '/views/doctor_dashboard.php');
            break;
        case 'Recepcionista':
            header('Location: ' . BASE_URL . '/views/recepcion_dashboard.php');
            break;
        case 'Paciente':
        default:
            header('Location: ' . BASE_URL . '/views/perfil.php');
            break;
    }
    exit;
} catch (Throwable $e) {
    $_SESSION['login_error'] = 'Error interno al intentar iniciar sesión.';
    header('Location: ' . BASE_URL . '/views/login.php');
    exit;
}