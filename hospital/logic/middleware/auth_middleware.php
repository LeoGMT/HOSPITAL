<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/sesion_functions.php';

/**
 * Bloquea el acceso a rutas protegidas si el usuario no ha iniciado sesión.
 */
function requerirAutenticacion(): void
{
    if (!estaAutenticado()) {
        setFlash('login_error', 'Debes iniciar sesión para acceder a esta sección.');
        header('Location: ' . BASE_URL . '/views/login.php');
        exit;
    }
}