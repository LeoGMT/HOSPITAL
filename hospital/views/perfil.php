<?php
require_once __DIR__ . '/../logic/middleware/rol_middleware.php';
requerirPaciente();

$usuario = obtenerUsuarioSesion();
$avisoAcceso = getFlash('acceso_denegado');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/ADAM-STYLES.css">
    <link rel="stylesheet" href="../assets/css/nav.css">
    <link flex href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <script src="../assets/js/script.js" defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Perfil</title>
</head>
<?php require_once __DIR__ . '/navbar_paciente.php'; ?>
<br><br>
    <section class="PERFIL-section">
        <div class="PERFIL-CAJA">
            <div class="PERFIL-CAJA-CONTEN">
                <div class="PERFIL-txt-header">
                    <h3>PERFIL</h3>
                </div>
                <div class="PERFIL-CAJA-FOTO">
                    <div class="PERFIL-FOTO">
                        <img src="../assets/img/anonimo.png" alt="" width="100px" height="100px">
                    </div>
                </div>
                <form action="logica/editar_personal.php" method="POST">
                    <div class="PERFIL-CAJA-INFO">
                        <input class="PERFIL-INFO" readonly type="text" value="<?= htmlspecialchars($usuario['nombre']) ?>" name="NOMBRE" disabled>
                    </div>
                    <div class="PERFIL-CAJA-INFO">
                        <input class="PERFIL-INFO" readonly type="text" value="<?= htmlspecialchars($usuario['ape_paterno']) ?>" name="APELLIDO PATERNO" disabled>
                    </div>
                    <div class="PERFIL-CAJA-INFO">
                        <input class="PERFIL-INFO" readonly type="text" value="$usuario['ape_marterno']) ?>>" name="APELLIDO MATERNO" disabled>
                    </div>
                    <div class="PERFIL-CAJA-INFO">
                        <input class="PERFIL-INFO" readonly type="text" value="<?= htmlspecialchars((string) ($usuario['id_paciente'] ?? 'N/A')) ?>" name="NUMERO-DE-PACIENTE" disabled>
                    </div>
                    <!--
                    <div class="PERFIL-CAJA-INFO">
                        <input class="PERFIL-INFO" readonly type="text" value="<?php echo $user_cedula?>" name="cedula" disabled>
                    </div>
                    -->
                    <div class="PERFIL-CAJA-INFO">
                        <input class="PERFIL-INFO" type="text" required value="<?= htmlspecialchars($usuario['correo']) ?>" name="correo" disabled>
                    </div>
                    <!--
                    <div class="PERFIL-CAJA-INFO">
                        <input class="PERFIL-INFO" type="text" required value="<?php echo $user_cel?>" name="celular" disabled>
                    </div>
                    -->
                    <div class="PERFIL-CAJA-INFO">
                        <input class="PERFIL-INFO" readonly type="text" value="<?= htmlspecialchars($usuario['rol']) ?>" name="ROL" disabled>
                    </div>
                    <div class="PERFIL-CAJA-INFO">
                        <input class="PERFIL-INFO" type="password" required placeholder="<?php echo "Contrasena actual: ", $user_pass?>" name="password" disabled>
                    </div>
                    <div class="PERFIL-CAJA-INFO">
                        <input class="PERFIL-INFO" type="password" required placeholder="Confirmar contrasena" name="confirm_password" disabled>
                    </div>
                    <div class="PERFIL-CAJA-BOTONES">
                        <div class="PERFIL-CAJA-BOTONES">
                            <button type="button" class="PERFIL-boton" onclick="editInfo(this)">
                                <p>EDITAR</p>
                            </button>
                        </div>
                        <div class="PERFIL-CAJA-BOTONES">
                            <input type="submit" class="PERFIL-submit" value="ACTUALIZAR">
                        </div>
                    </div>
                </form>

            </div>
        </div>    
    </section>

   <script>

        function editInfo(button) {
            const inputs = document.querySelectorAll('.PERFIL-INFO');
            const isDisabled = inputs[0].disabled;

            inputs.forEach(input => input.disabled =!isDisabled);
            button.textContent = isDisabled? 'EDITAR' : 'EDITAR';
        }
    </script>
</body>
</html>