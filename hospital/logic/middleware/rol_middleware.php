<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/sesion_functions.php';
require_once __DIR__ . '/auth_middleware.php';

/**
 * Verifica que el usuario esté autenticado y pertenezca a alguno de los roles permitidos.
 * Si no tiene permiso, lo redirige a su propio dashboard con aviso.
 *
 * @param string[] $rolesPermitidos
 */
function requerirRoles(array $rolesPermitidos): void
{
    requerirAutenticacion();

    if (!tieneRol($rolesPermitidos)) {
        $rolActual = obtenerRolActual();
        setFlash('acceso_denegado', 'No tienes permisos para acceder a ese módulo.');
        header('Location: ' . obtenerRutaDashboardPorRol($rolActual));
        exit;
    }
}

function requerirPaciente(): void
{
    requerirRoles(['Paciente']);
}

function requerirDoctor(): void
{
    requerirRoles(['Doctor']);
}

function requerirAdministrador(): void
{
    requerirRoles(['Administrador']);
}

function requerirRecepcionista(): void
{
    requerirRoles(['Recepcionista']);
}