<?php

require_once __DIR__ . '/../config/config.php';

/**
 * Inicia la sesión de forma segura si aún no está activa.
 */
function iniciarSesionSegura(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Indica si existe un usuario autenticado en la sesión actual.
 */
function estaAutenticado(): bool
{
    iniciarSesionSegura();
    return !empty($_SESSION['usuario']) && !empty($_SESSION['usuario']['id_usuario']);
}

/**
 * Devuelve el arreglo de datos del usuario en sesión o null si no ha iniciado sesión.
 */
function obtenerUsuarioSesion(): ?array
{
    iniciarSesionSegura();
    return $_SESSION['usuario'] ?? null;
}

/**
 * Devuelve el rol del usuario actual (ej. 'Paciente', 'Doctor', 'Administrador', 'Recepcionista').
 */
function obtenerRolActual(): ?string
{
    $usuario = obtenerUsuarioSesion();
    return $usuario['rol'] ?? null;
}

/**
 * Verifica si el usuario actual tiene alguno de los roles indicados.
 */
function tieneRol(array $rolesPermitidos): bool
{
    $rolActual = obtenerRolActual();
    if ($rolActual === null) {
        return false;
    }

    return in_array($rolActual, $rolesPermitidos, true);
}

/**
 * Devuelve la ruta del dashboard correspondiente a un rol.
 */
function obtenerRutaDashboardPorRol(?string $rol): string
{
    return match ($rol) {
        'Administrador' => BASE_URL . '/views/admin_dashboard.php',
        'Doctor'        => BASE_URL . '/views/doctor_dashboard.php',
        'Recepcionista' => BASE_URL . '/views/recepcion_dashboard.php',
        'Paciente'      => BASE_URL . '/views/perfil.php',
        default         => BASE_URL . '/views/login.php',
    };
}

/**
 * Guarda un mensaje flash en sesión.
 */
function setFlash(string $clave, string $mensaje): void
{
    iniciarSesionSegura();
    $_SESSION['flash'][$clave] = $mensaje;
}

/**
 * Obtiene y limpia un mensaje flash de sesión.
 */
function getFlash(string $clave): ?string
{
    iniciarSesionSegura();
    if (empty($_SESSION['flash'][$clave])) {
        return null;
    }

    $mensaje = $_SESSION['flash'][$clave];
    unset($_SESSION['flash'][$clave]);
    return $mensaje;
}

/**
 * Cierra la sesión por completo y limpia cookies de sesión si existen.
 */
function cerrarSesionUsuario(): void
{
    iniciarSesionSegura();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            (bool) $params['secure'],
            (bool) $params['httponly']
        );
    }

    session_destroy();
}