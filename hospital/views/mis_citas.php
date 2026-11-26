<?php
require_once __DIR__ . '/../logic/middleware/rol_middleware.php';
require_once __DIR__ . '/../logic/models/CitaModel.php';

requerirPaciente();

$usuario = obtenerUsuarioSesion();
$idPaciente = (int) ($usuario['id_paciente'] ?? 0);

$modelo = new CitaModel();
$citas = $idPaciente > 0 ? $modelo->obtenerCitasPaciente($idPaciente) : [];

$errorFlash = getFlash('cita_error');
$exitoFlash = getFlash('cita_exito');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Citas</title>
    <link rel="stylesheet" href="../assets/css/MostrarP.css">
    <link rel="stylesheet" href="../assets/css/nav.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <script src="../assets/js/script.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="MPbody">

<?php require_once __DIR__ . '/navbar_paciente.php'; ?>
<br><br>

    <main class="MPmain-content">
        <h1 class="MPh1">Mis Citas Registradas</h1>

        <?php if ($errorFlash): ?>
            <div class="MC-alerta MC-error"><?= htmlspecialchars($errorFlash) ?></div>
        <?php endif; ?>

        <?php if ($exitoFlash): ?>
            <div class="MC-alerta MC-exito"><?= htmlspecialchars($exitoFlash) ?></div>
        <?php endif; ?>

        <div class="MPpersonal-list">
            <?php if (empty($citas)): ?>
                <p>No tienes citas registradas todavía. <a href="agendar_cita.php">Agendar una cita</a>.</p>
            <?php else: ?>
                <?php foreach ($citas as $cita):
                    $doctorNombre = trim($cita['doctor_nombre'] . ' ' . $cita['doctor_ape_paterno'] . ' ' . ($cita['doctor_ape_materno'] ?? ''));
                    $fechaFmt = htmlspecialchars((string) $cita['fecha_cita']);
                    $horaFmt  = htmlspecialchars(substr((string) $cita['hora_cita'], 0, 5));
                    $puedeCancelar = in_array($cita['estado'], ['Programada', 'Confirmada'], true);
                    $reembolsoEst = (int) ($cita['porcentaje_reembolso_estimado'] ?? 0);
                ?>
                    <div class="MPpersonal-item">
                        <div class="MPpersonal-header" onclick="toggleInfo(this)">
                            <span class="personal-name">
                                <?= $fechaFmt ?> <?= $horaFmt ?> — Dr(a). <?= htmlspecialchars($doctorNombre) ?> (<?= htmlspecialchars($cita['estado']) ?>)
                            </span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="MPpersonal-info">
                            <p><strong>Especialidad:</strong> <?= htmlspecialchars($cita['nombre_especialidad']) ?></p>
                            <p><strong>Consultorio:</strong> <?= htmlspecialchars($cita['consultorio']) ?></p>
                            <p><strong>Servicio:</strong> <?= htmlspecialchars($cita['nombre_servicio']) ?> ($<?= number_format((float) $cita['precio_servicio'], 2) ?>)</p>
                            <p><strong>Estado de la cita:</strong> <?= htmlspecialchars($cita['estado']) ?></p>
                            <p><strong>Estado de pago:</strong> <?= htmlspecialchars($cita['estado_pago']) ?></p>
                            <p><strong>Motivo:</strong> <?= htmlspecialchars((string) ($cita['motivo_consulta'] ?? 'No especificado')) ?></p>
                            <p><strong>Reembolso estimado al cancelar hoy:</strong> <?= $reembolsoEst ?>% (48h+ = 100%, 24h–47h = 50%, &lt;24h = 0%)</p>

                            <?php if ($puedeCancelar): ?>
                                <form action="../logic/controllers/cancelar_cita.php" method="POST" onsubmit="return confirm('¿Seguro que deseas cancelar esta cita? Aplica devolución estimada del <?= $reembolsoEst ?>%.');">
                                    <input type="hidden" name="id_cita" value="<?= (int) $cita['id_cita'] ?>">
                                    <button type="submit" class="MPdelete-btn">Cancelar cita</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function toggleInfo(element) {
            const info = element.nextElementSibling;
            const estaAbierto = info.style.display === 'block';
            info.style.display = estaAbierto ? 'none' : 'block';
            const icono = element.querySelector('i');
            if (icono) {
                icono.classList.toggle('fa-chevron-down');
                icono.classList.toggle('fa-chevron-up');
            }
        }
    </script>

    <style>
        .MPpersonal-info { display: none; }
        .MC-alerta { padding: 12px 14px; border-radius: 6px; margin-bottom: 14px; font-weight: 500; }
        .MC-error { background: #fde8e8; color: #9b1c1c; border: 1px solid #f8b4b4; }
        .MC-exito { background: #def7ec; color: #03543f; border: 1px solid #84e1bc; }
    </style>
</body>
</html>