<?php
session_start();
require_once __DIR__ . '/../logic/config/config.php';

$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);

$exito = $_SESSION['registro_exito'] ?? null;
unset($_SESSION['registro_exito']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Iniciar Sesión - <?= htmlspecialchars(APP_NAME) ?></title>
</head>
<body class="body-login">

    <div class="wrapper">
        <div class="login_box2">
            <div class="login_header2">
                <img src="../assets/img/logo.png" alt="SaludEnTurno">
            </div>

            <?php if ($exito): ?>
                <div class="alert-success" style="margin-bottom: 15px;">
                    <?= htmlspecialchars($exito) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert-error" style="margin-bottom: 15px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="registro">
                <form action="../logic/controllers/procesar_login.php" method="POST">
                    <div class="registro-col">
                        <div class="agpform-group" style="margin-bottom: 20px;">
                            <label for="tipo_acceso" class="agplabel">Iniciar sesión con</label>
                            <div class="agpinput-group">
                                <div class="select">
                                    <select name="tipo_acceso" id="tipo_acceso" required>
                                        <option value="correo">Correo electrónico (Pacientes / Personal)</option>
                                        <option value="cedula">Cédula profesional (Doctores)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="input_box">
                            <input type="text" id="identificador" class="input-field" name="identificador" required>
                            <label for="identificador" id="lbl_identificador" class="label">Correo Electrónico</label>
                            <i class='bx bx-envelope icon' id="ico_identificador"></i>
                        </div>

                        <div class="input_box">
                            <input type="password" id="password" class="input-field" name="password" required>
                            <label for="password" class="label">Contraseña</label>
                            <i class="bx bx-lock-alt icon"></i>
                        </div>
                    </div>

                    <div class="input_box">
                        <input type="submit" class="input-submit" value="Ingresar">
                    </div>
                </form>

                <div class="register">
                    <span>¿No tienes una cuenta?... <a href="registro.php">Registrarse</a></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        const selectAcceso = document.getElementById('tipo_acceso');
        const lblIdentificador = document.getElementById('lbl_identificador');
        const inputIdentificador = document.getElementById('identificador');
        const icoIdentificador = document.getElementById('ico_identificador');

        function actualizarModoLogin() {
            if (selectAcceso.value === 'cedula') {
                lblIdentificador.textContent = 'Cédula Profesional';
                inputIdentificador.type = 'text';
                icoIdentificador.className = 'bx bx-id-card icon';
            } else {
                lblIdentificador.textContent = 'Correo Electrónico';
                inputIdentificador.type = 'email';
                icoIdentificador.className = 'bx bx-envelope icon';
            }
        }

        selectAcceso.addEventListener('change', actualizarModoLogin);
        actualizarModoLogin();
    </script>
</body>
</html>