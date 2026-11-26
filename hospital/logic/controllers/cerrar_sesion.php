<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/sesion_functions.php';

cerrarSesionUsuario();
setFlash('login_exito', 'Has cerrado sesión correctamente.');

header('Location: ' . BASE_URL . '/views/login.php');
exit;