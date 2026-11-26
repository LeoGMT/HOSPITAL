<?php

require_once __DIR__ . '/config.php';

$dsn = DB_DRIVER . ":Server=" . DB_HOST . ";Database=" . DB_NAME;

$usuarioDb = defined('DB_USER') ? DB_USER : '';
$claveDb   = defined('DB_PASS') ? DB_PASS : '';

$conexion = new PDO(
    $dsn,
    $usuarioDb !== '' ? $usuarioDb : null,
    $claveDb !== '' ? $claveDb : null,
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

function obtenerConexion(): PDO
{
    global $conexion;
    return $conexion;
}