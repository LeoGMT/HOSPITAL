<?php
session_start();
require_once __DIR__ . '/../logic/config/config.php';

$error = $_SESSION['registro_error'] ?? null;
unset($_SESSION['registro_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Registro - <?= htmlspecialchars(APP_NAME) ?></title>
</head>
<body class="body-login">

    <div class="wrapper">
        <div class="login_box2">
            <div class="login_header2">
                <img src="../assets/img/logo.png" alt="SaludEnTurno">
            </div>

            <?php if ($error): ?>
                <div class="alert-error" style="margin-bottom: 15px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="registro">
                <form action="../logic/controllers/procesar_registro.php" method="POST">
                    <div class="registro-col">
                        <div class="input_box">
                            <input type="text" id="apep" class="input-field" name="apep" required>
                            <label for="apep" class="label">Apellido Paterno</label>
                            <i class="bx bx-user icon"></i>
                        </div>
                        <div class="input_box">
                            <input type="text" id="apem" class="input-field" name="apem">
                            <label for="apem" class="label">Apellido Materno</label>
                            <i class="bx bx-user icon"></i>
                        </div>
                        <div class="input_box">
                            <input type="text" id="name" class="input-field" name="nombre" required>
                            <label for="name" class="label">Nombre (s)</label>
                            <i class="bx bx-user icon"></i>
                        </div>

                        <div class="input_box">
                            <input type="text" id="tel" class="input-field" name="celular" required>
                            <label for="tel" class="label">Teléfono</label>
                            <i class='bx bx-phone icon'></i>
                        </div>

                        <div class="agpform-group" style="margin-bottom: 20px;">
                            <label for="rol" class="agplabel">Rol</label>
                            <div class="agpinput-group">
                                <div class="select">
                                    <select name="rol" id="rol" required>
                                        <option value="Administrador">Administrador</option>
                                        <option value="Paciente">Paciente</option>
                                        <option value="Doctor">Doctor</option>
                                        <option value="Recepcionista">Recepcionista</option>
                                    </select>
                                </div>
                                <i class="fas fa-users"></i>
                            </div>
                        </div>

                        <!-- Campos exclusivos de Doctor -->
                        <div class="agpform-group">
                            <div id="DivEspe" class="select" style="display: none;">
                                <label for="espe" class="agplabel">Especialidad</label>
                                <div class="agpinput-group" style="margin-top: 10px;">
                                    <select name="espe" id="espe">
                                        <option value="1">Medicina General</option>
                                        <option value="2">Pediatría</option>
                                        <option value="3">Cardiología</option>
                                        <option value="4">Ginecología</option>
                                    </select>
                                </div>
                            </div>
                            <i class="fas fa-users"></i>
                        </div>

                        <div id="divDoc" class="input_box" style="display: none;">
                            <input type="text" id="cedula" class="input-field" name="cedula">
                            <label for="cedula" class="label">Cedula Profesional</label>
                            <i class='bx bx-id-card icon'></i>
                        </div>

                        <div id="divConsultorio" class="input_box" style="display: none;">
                            <input type="text" id="consultorio" class="input-field" name="consultorio">
                            <label for="consultorio" class="label">Consultorio</label>
                            <i class='bx bx-building icon'></i>
                        </div>

                        <!-- Campos para empleados (Doctor, Administrador, Recepcionista) -->
                        <div id="divHorario" class="input_box" style="display: none;">
                            <input type="text" id="horario" class="input-field" name="horario">
                            <label for="horario" class="label">Horario Laboral</label>
                            <i class='bx bx-time icon'></i>
                        </div>

                        <div id="divNivelAdmin" class="input_box" style="display: none;">
                            <input type="text" id="nivel_acceso" class="input-field" name="nivel_acceso">
                            <label for="nivel_acceso" class="label">Nivel de Acceso</label>
                            <i class='bx bx-shield icon'></i>
                        </div>

                        <!-- Campos exclusivos de Paciente -->
                        <div class="agpform-group">
                            <div id="divSexo" class="select" style="display: none;">
                                <label for="sexo" class="agplabel">Sexo</label>
                                <div class="agpinput-group" style="margin-top: 10px;">
                                    <select name="sexo" id="sexo">
                                        <option value="Masculino">Masculino</option>
                                        <option value="Femenino">Femenino</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                            <i class="fas fa-users"></i>
                        </div>

                        <div class="agpform-group">
                            <div id="divSangre" class="select" style="display: none;">
                                <label for="sangre" class="agplabel">Tipo de Sangre</label>
                                <div class="agpinput-group" style="margin-top: 10px;">
                                    <select name="sangre" id="sangre">
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>
                            </div>
                            <i class="fas fa-users"></i>
                        </div>

                        <div id="divEmergencia" class="input_box" style="display: none;">
                            <input type="text" id="emergencia" class="input-field" name="emergencia">
                            <label for="emergencia" class="label">Teléfono de Emergencia</label>
                            <i class='bx bx-phone icon'></i>
                        </div>

                        <div id="divContacto" class="input_box" style="display: none;">
                            <input type="text" id="contacto" class="input-field" name="contacto">
                            <label for="contacto" class="label">Nombre de un Contacto de Emergencia</label>
                            <i class="bx bx-user icon"></i>
                        </div>

                        <div id="divNacimiento" class="input_box" style="display: none;">
                            <input type="date" id="nacimiento" class="input-field" name="nacimiento">
                            <label for="nacimiento" class="label">Fecha de Nacimiento</label>
                            <i class="bx bx-calendar icon"></i>
                        </div>

                        <script>
                            function mostrarCampo(idSelect, idDiv, idInput, rolesMostrar) {
                                const sele = document.getElementById(idSelect);
                                const campoExtra = document.getElementById(idDiv);
                                const inputE = document.getElementById(idInput);

                                function actualizar() {
                                    const mostrar = rolesMostrar.includes(sele.value);
                                    campoExtra.style.display = mostrar ? "block" : "none";
                                    if (inputE.tagName === "INPUT" && inputE.type !== "date") {
                                        inputE.required = mostrar;
                                    }
                                    if (!mostrar && inputE.tagName === "INPUT") {
                                        inputE.value = "";
                                    }
                                }

                                sele.addEventListener("change", actualizar);
                                actualizar();
                            }

                            mostrarCampo("rol", "divEmergencia", "emergencia", ["Paciente"]);
                            mostrarCampo("rol", "divSexo", "sexo", ["Paciente"]);
                            mostrarCampo("rol", "divSangre", "sangre", ["Paciente"]);
                            mostrarCampo("rol", "divContacto", "contacto", ["Paciente"]);
                            mostrarCampo("rol", "divNacimiento", "nacimiento", ["Paciente"]);

                            mostrarCampo("rol", "divDoc", "cedula", ["Doctor"]);
                            mostrarCampo("rol", "DivEspe", "espe", ["Doctor"]);
                            mostrarCampo("rol", "divConsultorio", "consultorio", ["Doctor"]);

                            mostrarCampo("rol", "divHorario", "horario", ["Doctor", "Administrador", "Recepcionista"]);
                            mostrarCampo("rol", "divNivelAdmin", "nivel_acceso", ["Administrador"]);
                        </script>

                        <div class="input_box">
                            <input type="email" id="mail" class="input-field" name="correo" required>
                            <label for="mail" class="label">Correo Electronico</label>
                            <i class='bx bx-envelope icon'></i>
                        </div>

                        <div class="input_box">
                            <input type="password" id="pass" class="input-field" name="password" required>
                            <label for="pass" class="label">Contraseña</label>
                            <i class="bx bx-lock-alt icon"></i>
                        </div>
                        <div class="input_box">
                            <input type="password" id="pass_conf" class="input-field" name="passwordconf" required>
                            <label for="pass_conf" class="label">Confirmar contraseña</label>
                            <i class="bx bx-lock-alt icon"></i>
                        </div>
                    </div>
                    <div class="input_box">
                        <input type="submit" class="input-submit" value="Registrarse">
                    </div>
                </form>
                <div class="register">
                    <span>Ya tiene una cuenta?... <a href="login.php">Iniciar</a></span>
                </div>
            </div>
        </div>
    </div>

</body>
</html>