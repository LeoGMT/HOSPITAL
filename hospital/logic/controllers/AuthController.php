<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/AuthModel.php';

class AuthController
{
    private AuthModel $authModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
    }

    public function iniciarSesion(array $post): array
    {
        $tipoInput     = strtolower(trim((string)($post['tipo_acceso'] ?? 'correo')));
        $identificador = trim((string)($post['identificador'] ?? ''));
        $password      = (string)($post['password'] ?? '');

        if ($identificador === '' || $password === '') {
            return ['ok' => false, 'mensaje' => 'Ingresa tu identificador y contraseña.'];
        }

        $tipoSql = ($tipoInput === 'cedula') ? 'CEDULA' : 'CORREO';

        if ($tipoSql === 'CORREO' && !filter_var($identificador, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'mensaje' => 'El formato del correo electrónico no es válido.'];
        }

        $usuario = $this->authModel->obtenerUsuarioParaLogin($tipoSql, $identificador);

        if (!$usuario) {
            return [
                'ok'      => false,
                'mensaje' => $tipoSql === 'CEDULA'
                    ? 'No se encontró un doctor con esa cédula profesional.'
                    : 'No se encontró una cuenta con ese correo electrónico.',
            ];
        }

        if (($usuario['estado_usuario'] ?? '') !== 'Activo') {
            return ['ok' => false, 'mensaje' => 'La cuenta no está activa. Contacta al administrador.'];
        }

        if (!password_verify($password, (string)$usuario['contrasena_hash'])) {
            return ['ok' => false, 'mensaje' => 'Contraseña incorrecta.'];
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_regenerate_id(true);
        $_SESSION['usuario_id'] = (int)$usuario['id_usuario'];
        $_SESSION['nombre']     = $usuario['nombre'] . ' ' . $usuario['ape_paterno'];
        $_SESSION['rol']        = $usuario['nombre_rol'];
        $_SESSION['correo']     = $usuario['correo'];
        $_SESSION['cedula']     = $usuario['cedula_profesional'] ?? null;

        return [
            'ok'      => true,
            'rol'     => $usuario['nombre_rol'],
            'mensaje' => 'Bienvenido, ' . $usuario['nombre'] . '.',
        ];
    }
}