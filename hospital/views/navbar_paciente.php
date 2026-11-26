<?php
require_once __DIR__ . '/../logic/config/config.php';
require_once __DIR__ . '/../logic/functions/sesion_functions.php';
require_once __DIR__ . '/../logic/middleware/rol_middleware.php';

requerirPaciente();

$usuario = obtenerUsuarioSesion();
$nombreCompleto = trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['ape_paterno'] ?? ''));
?>
<div>
<nav class="sidebar locked">
  <div class="logo_items flex">
    <span class="nav_image">
      <img src="../assets/img/logo-blanco.png" alt="logo_img" />
    </span>
    <span class="logo_name"><?= htmlspecialchars(APP_NAME) ?></span>
    <i class="bx bx-lock-alt" id="lock-icon" title="Unlock Sidebar"></i>
    <i class="bx bx-x" id="sidebar-close"></i>
  </div>

  <div class="menu_container">
    <div class="menu_items">
      <ul class="menu_item">
        <div class="menu_title flex">
          <span class="title">Citas Médicas</span>
          <span class="line"></span>
        </div>
        <li class="item">
          <a href="perfil.php" class="link flex">
            <i class='bx bx-home-alt'></i>
            <span>Perfil</span>
          </a>
        </li>
        <li class="item">
          <a href="agendar_cita.php" class="link flex">
            <i class='bx bx-calendar-plus'></i>
            <span>Agendar Cita</span>
          </a>
        </li>
        <li class="item">
          <a href="mis_citas.php" class="link flex">
            <i class='bx bx-calendar-check'></i>
            <span>Mis Citas</span>
          </a>
        </li>
      </ul>

      <ul class="menu_item">
        <div class="menu_title flex">
          <span class="title">Salud y Pagos</span>
          <span class="line"></span>
        </div>
        <li class="item">
          <a href="mis_recetas.php" class="link flex">
            <i class='bx bx-file-medical'></i>
            <span>Mis Recetas</span>
          </a>
        </li>
        <li class="item">
          <a href="mis_pagos.php" class="link flex">
            <i class='bx bx-receipt'></i>
            <span>Mis Pagos</span>
          </a>
        </li>
        <li class="item">
          <a href="servicios_disponibles.php" class="link flex">
            <i class='bx bx-clinic'></i>
            <span>Servicios</span>
          </a>
        </li>
      </ul>

      <ul class="menu_item">
        <div class="menu_title flex">
          <span class="title">Cuenta</span>
          <span class="line"></span>
        </div>
        <li class="item">
          <a href="perfil_paciente.php" class="link flex">
            <i class='bx bx-face'></i>
            <span>Mi Perfil</span>
          </a>
        </li>
      </ul>
    </div>

    <div class="sidebar_profile flex">
      <span class="nav_image">
        <img src="../assets/img/perfil.png" alt="logo_img" />
      </span>
      <div class="data_text">
        <span class="name"><?= htmlspecialchars($nombreCompleto) ?></span>
        <br>
        <span class="cerrar"><a href="../logic/controllers/cerrar_sesion.php">Cerrar sesion</a></span>
      </div>
    </div>
  </div>
</nav>

<nav class="navbar flex">
  <i class="bx bx-menu" id="sidebar-open"></i>
  <h1 class="search_box"><?= htmlspecialchars(APP_NAME) ?></h1>
  <span class="nav_image">
    <img src="../assets/img/logo-blanco.png" alt="logo_img" />
  </span>
</nav>
</div>
<br><br><br>