<?php

session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/views/registro.php');
    exit;
}

// Plantilla de lectura desde $_POST (ajusta las claves si cambias el atributo "name" en registro.php)
$datosFormulario = [
    'ape_paterno'         => trim($_POST['apep'] ?? ''),
    'ape_materno'         => trim($_POST['apem'] ?? ''),
    'nombre'              => trim($_POST['nombre'] ?? ''),
    'telefono'            => trim($_POST['celular'] ?? ''),
    'rol'                 => trim($_POST['rol'] ?? ''),
    'id_especialidad'     => trim($_POST['espe'] ?? ''),
    'cedula_profesional'  => trim($_POST['cedula'] ?? ''),
    'consultorio'         => trim($_POST['consultorio'] ?? ''),
    'horario_lab'         => trim($_POST['horario'] ?? ''),
    'nivel_acceso'        => trim($_POST['nivel_acceso'] ?? ''),
    'sexo'                => trim($_POST['sexo'] ?? ''),
    'tipo_sangre'         => trim($_POST['sangre'] ?? ''),
    'telefono_emergencia' => trim($_POST['emergencia'] ?? ''),
    'contacto_emergencia' => trim($_POST['contacto'] ?? ''),
    'fecha_nacimiento'    => trim($_POST['nacimiento'] ?? ''),
    'correo'              => trim($_POST['correo'] ?? ''),
    'password'            => $_POST['password'] ?? '',
    'password_confirm'    => $_POST['passwordconf'] ?? '',
];

if (
    $datosFormulario['nombre'] === '' ||
    $datosFormulario['ape_paterno'] === '' ||
    $datosFormulario['rol'] === '' ||
    $datosFormulario['correo'] === '' ||
    $datosFormulario['password'] === '' ||
    $datosFormulario['password_confirm'] === ''
) {
    $_SESSION['registro_error'] = 'Por favor completa todos los campos obligatorios.';
    header('Location: ' . BASE_URL . '/views/registro.php');
    exit;
}

if (!filter_var($datosFormulario['correo'], FILTER_VALIDATE_EMAIL)) {
    $_SESSION['registro_error'] = 'El formato del correo electrónico no es válido.';
    header('Location: ' . BASE_URL . '/views/registro.php');
    exit;
}

if ($datosFormulario['password'] !== $datosFormulario['password_confirm']) {
    $_SESSION['registro_error'] = 'Las contraseñas no coinciden.';
    header('Location: ' . BASE_URL . '/views/registro.php');
    exit;
}

if (strlen($datosFormulario['password']) < 6) {
    $_SESSION['registro_error'] = 'La contraseña debe tener al menos 6 caracteres.';
    header('Location: ' . BASE_URL . '/views/registro.php');
    exit;
}

try {
    $modelo = new UsuarioModel();
    $idCreado = $modelo->registrar($datosFormulario);

    $_SESSION['registro_exito'] = 'Registro completado correctamente. Ya puedes iniciar sesión.';
    $_SESSION['nuevo_usuario_id'] = $idCreado;

    header('Location: ' . BASE_URL . '/views/login.php');
    exit;
} catch (PDOException $e) {
    // Extrae mensaje legible si viene de THROW en SQL Server o constraint
    $mensajeSql = $e->getMessage();
    if (preg_match('/\[SQL Server\](.+?)(?:\s*\(severity|\s*\[|$)/', $mensajeSql, $coincidencia)) {
        $_SESSION['registro_error'] = trim($coincidencia[1]);
    } else {
        $_SESSION['registro_error'] = 'No se pudo completar el registro. Verifica que el correo o cédula no estén duplicados.';
    }

    header('Location: ' . BASE_URL . '/views/registro.php');
    exit;
} catch (Throwable $e) {
    $_SESSION['registro_error'] = $e->getMessage();
    header('Location: ' . BASE_URL . '/views/registro.php');
    exit;
}