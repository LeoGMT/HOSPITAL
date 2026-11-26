<?php
require_once __DIR__ . '/../logic/middleware/rol_middleware.php';
require_once __DIR__ . '/../logic/models/CitaModel.php';

requerirPaciente();

$modelo = new CitaModel();
$especialidades = $modelo->obtenerEspecialidades();
$servicios = $modelo->obtenerServiciosActivos();

// Agrupa doctores por especialidad para filtrar sin recargar o vía selección
$doctoresPorEspecialidad = [];
foreach ($especialidades as $esp) {
    $idEsp = (int) $esp['id_especialidad'];
    $doctoresPorEspecialidad[$idEsp] = $modelo->obtenerDoctoresPorEspecialidad($idEsp);
}

$errorFlash = getFlash('cita_error');
$exitoFlash = getFlash('cita_exito');

// Límites de política: mínimo +48 horas, máximo +3 meses
$fechaMinStr = date('Y-m-d', strtotime('+48 hours'));
$fechaMaxStr = date('Y-m-d', strtotime('+3 months'));
$fechaMaxExclusivaCal = date('Y-m-d', strtotime('+3 months +1 day'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/nav.css">
    <script src="../assets/js/script.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <title>Agendar Cita - <?= htmlspecialchars(APP_NAME) ?></title>
</head>
<body class="MPbody">
    <?php require_once __DIR__ . '/navbar_paciente.php'; ?>

    <main class="cita-main">
        <h1 class="cita-titulo">Agendar Nueva Cita</h1>
        <p class="cita-politica-nota">
            <strong>Política de citas:</strong> Anticipación mínima de <strong>48 horas</strong> y máxima de <strong>3 meses</strong>.
            Duración por cita: <strong>30 minutos</strong>. Cuentas con <strong>8 horas</strong> para confirmar tu pago.
            No se permiten reagendaciones ni citas pendientes duplicadas con el mismo doctor.
        </p>

        <?php if ($errorFlash): ?>
            <div class="cita-alerta cita-alerta-error"><?= htmlspecialchars($errorFlash) ?></div>
        <?php endif; ?>

        <?php if ($exitoFlash): ?>
            <div class="cita-alerta cita-alerta-exito"><?= htmlspecialchars($exitoFlash) ?></div>
        <?php endif; ?>

        <form action="../logic/controllers/procesar_cita.php" method="POST" id="formAgendarCita" class="cita-card">
            <div class="cita-grid-2">
                <!-- Paso 1: Especialidad -->
                <div class="cita-campo">
                    <label for="id_especialidad">1. Selecciona una especialidad</label>
                    <select name="id_especialidad" id="id_especialidad" required>
                        <option value="">-- Elige especialidad --</option>
                        <?php foreach ($especialidades as $esp): ?>
                            <option value="<?= (int) $esp['id_especialidad'] ?>">
                                <?= htmlspecialchars($esp['nombre_especialidad']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Paso 2: Doctor -->
                <div class="cita-campo">
                    <label for="id_doctor">2. Selecciona un doctor</label>
                    <select name="id_doctor" id="id_doctor" required disabled>
                        <option value="">-- Primero elige una especialidad --</option>
                    </select>
                </div>
            </div>

            <div class="cita-grid-2">
                <div class="cita-campo">
                    <label for="id_servicio">Servicio (opcional)</label>
                    <select name="id_servicio" id="id_servicio">
                        <option value="">-- Consulta General --</option>
                        <?php foreach ($servicios as $srv): ?>
                            <option value="<?= (int) $srv['id_servicio'] ?>">
                                <?= htmlspecialchars($srv['nombre']) ?> ($<?= number_format((float) $srv['precio'], 2) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="cita-campo">
                    <label for="motivo_consulta">Motivo de consulta</label>
                    <input type="text" name="motivo_consulta" id="motivo_consulta" maxlength="255" placeholder="Describe brevemente el motivo">
                </div>
            </div>

            <!-- Paso 3: Calendario interactivo -->
            <div class="cita-campo">
                <label>3. Selecciona fecha en el calendario (días bloqueados fuera de política)</label>
                <div id="calendarioCitas"></div>
            </div>

            <!-- Paso 4: Slots de 30 minutos -->
            <div class="cita-campo">
                <label>4. Selecciona un horario disponible (bloques de 30 min)</label>
                <div id="contenedorSlots" class="slots-grid">
                    <span class="slots-vacio">Selecciona un doctor y una fecha válida en el calendario.</span>
                </div>
            </div>

            <input type="hidden" name="fecha_cita" id="fecha_cita" required>
            <input type="hidden" name="hora_cita" id="hora_cita" required>

            <div class="cita-resumen" id="resumenSeleccion">
                Fecha seleccionada: <strong>Ninguna</strong> | Hora: <strong>Ninguna</strong>
            </div>

            <button type="submit" class="cita-btn-submit" id="btnConfirmarCita" disabled>
                Confirmar y Agendar Cita
            </button>
        </form>
    </main>

    <script>
        const doctoresPorEspecialidad = <?= json_encode($doctoresPorEspecialidad, JSON_UNESCAPED_UNICODE) ?>;
        const fechaMinStr = <?= json_encode($fechaMinStr) ?>;
        const fechaMaxStr = <?= json_encode($fechaMaxStr) ?>;
        const fechaMaxExclusiva = <?= json_encode($fechaMaxExclusivaCal) ?>;
        const endpointOcupados = <?= json_encode(BASE_URL . '/logic/controllers/obtener_horarios_ocupados.php') ?>;

        const selectEspecialidad = document.getElementById('id_especialidad');
        const selectDoctor = document.getElementById('id_doctor');
        const contenedorSlots = document.getElementById('contenedorSlots');
        const inputFecha = document.getElementById('fecha_cita');
        const inputHora = document.getElementById('hora_cita');
        const resumenSeleccion = document.getElementById('resumenSeleccion');
        const btnConfirmar = document.getElementById('btnConfirmarCita');

        let horariosOcupadosMapa = {}; // { 'YYYY-MM-DD': Set(['09:00', '09:30']) }
        let fechaActualSeleccionada = '';

        // Genera slots cada 30 minutos de 08:00 a 19:30
        function generarSlotsDia() {
            const slots = [];
            for (let h = 8; h <= 19; h++) {
                for (let m of [0, 30]) {
                    const hh = String(h).padStart(2, '0');
                    const mm = String(m).padStart(2, '0');
                    slots.push(`${hh}:${mm}`);
                }
            }
            return slots;
        }

        function actualizarResumen() {
            const fText = inputFecha.value || 'Ninguna';
            const hText = inputHora.value || 'Ninguna';
            resumenSeleccion.innerHTML = `Fecha seleccionada: <strong>${fText}</strong> | Hora: <strong>${hText}</strong>`;
            btnConfirmar.disabled = !(selectDoctor.value && inputFecha.value && inputHora.value);
        }

        function renderizarSlots(fechaStr) {
            contenedorSlots.innerHTML = '';
            inputHora.value = '';
            actualizarResumen();

            if (!selectDoctor.value || !fechaStr) {
                contenedorSlots.innerHTML = '<span class="slots-vacio">Selecciona un doctor y una fecha válida.</span>';
                return;
            }

            if (fechaStr < fechaMinStr || fechaStr > fechaMaxStr) {
                contenedorSlots.innerHTML = '<span class="slots-vacio">Esa fecha está fuera de la política (mínimo 48h, máximo 3 meses).</span>';
                return;
            }

            const ocupadosSet = horariosOcupadosMapa[fechaStr] || new Set();
            const slots = generarSlotsDia();

            slots.forEach(hora => {
                const ocupado = ocupadosSet.has(hora);
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'slot-btn' + (ocupado ? ' slot-ocupado' : '');
                btn.textContent = ocupado ? `${hora} (Ocupado)` : hora;
                btn.disabled = ocupado;

                if (!ocupado) {
                    btn.addEventListener('click', () => {
                        document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('slot-activo'));
                        btn.classList.add('slot-activo');
                        inputHora.value = hora;
                        actualizarResumen();
                    });
                }

                contenedorSlots.appendChild(btn);
            });
        }

        async function cargarOcupadosDoctor(idDoctor) {
            horariosOcupadosMapa = {};
            inputFecha.value = '';
            inputHora.value = '';
            actualizarResumen();

            if (!idDoctor) {
                contenedorSlots.innerHTML = '<span class="slots-vacio">Selecciona un doctor y una fecha válida.</span>';
                return;
            }

            const url = `${endpointOcupados}?id_doctor=${encodeURIComponent(idDoctor)}&start=${encodeURIComponent(fechaMinStr)}&end=${encodeURIComponent(fechaMaxStr)}`;
            const resp = await fetch(url);
            if (resp.ok) {
                const data = await resp.json();
                (data.ocupados || []).forEach(item => {
                    if (!horariosOcupadosMapa[item.fecha_cita]) {
                        horariosOcupadosMapa[item.fecha_cita] = new Set();
                    }
                    horariosOcupadosMapa[item.fecha_cita].add(item.hora_cita);
                });
            }

            if (fechaActualSeleccionada) {
                renderizarSlots(fechaActualSeleccionada);
            }
        }

        selectEspecialidad.addEventListener('change', () => {
            const idEsp = selectEspecialidad.value;
            selectDoctor.innerHTML = '';
            inputFecha.value = '';
            inputHora.value = '';
            actualizarResumen();

            if (!idEsp || !doctoresPorEspecialidad[idEsp] || doctoresPorEspecialidad[idEsp].length === 0) {
                selectDoctor.disabled = true;
                selectDoctor.innerHTML = '<option value="">-- No hay doctores en esta especialidad --</option>';
                return;
            }

            selectDoctor.disabled = false;
            selectDoctor.innerHTML = '<option value="">-- Elige un doctor --</option>';
            doctoresPorEspecialidad[idEsp].forEach(doc => {
                const opt = document.createElement('option');
                opt.value = doc.id_doctor;
                const nombre = `${doc.nombre} ${doc.ape_paterno} ${doc.ape_materno || ''}`.trim();
                opt.textContent = `Dr(a). ${nombre} — Cons. ${doc.consultorio} (Céd. ${doc.cedula_profesional})`;
                selectDoctor.appendChild(opt);
            });
        });

        selectDoctor.addEventListener('change', () => {
            cargarOcupadosDoctor(selectDoctor.value);
        });

        document.addEventListener('DOMContentLoaded', () => {
            const calEl = document.getElementById('calendarioCitas');
            const calendar = new FullCalendar.Calendar(calEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                validRange: {
                    start: fechaMinStr,
                    end: fechaMaxExclusiva
                },
                dateClick(info) {
                    if (!selectDoctor.value) {
                        alert('Primero selecciona un doctor.');
                        return;
                    }
                    if (info.dateStr < fechaMinStr || info.dateStr > fechaMaxStr) {
                        return;
                    }
                    document.querySelectorAll('.fc-daygrid-day.cita-dia-seleccionado').forEach(el => {
                        el.classList.remove('cita-dia-seleccionado');
                    });
                    info.dayEl.classList.add('cita-dia-seleccionado');

                    fechaActualSeleccionada = info.dateStr;
                    inputFecha.value = info.dateStr;
                    renderizarSlots(info.dateStr);
                }
            });
            calendar.render();
        });
    </script>

    <style>
        .cita-main { max-width: 960px; margin: 20px auto; padding: 0 16px; font-family: 'Roboto', sans-serif; }
        .cita-titulo { margin-bottom: 8px; }
        .cita-politica-nota { background: #eef6ff; border-left: 4px solid #1d6fd8; padding: 12px 14px; border-radius: 6px; margin-bottom: 18px; font-size: 0.92rem; }
        .cita-alerta { padding: 12px 14px; border-radius: 6px; margin-bottom: 16px; font-weight: 500; }
        .cita-alerta-error { background: #fde8e8; color: #9b1c1c; border: 1px solid #f8b4b4; }
        .cita-alerta-exito { background: #def7ec; color: #03543f; border: 1px solid #84e1bc; }
        .cita-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .cita-grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 16px; }
        .cita-campo { margin-bottom: 18px; }
        .cita-campo label { display: block; font-weight: 700; margin-bottom: 6px; }
        .cita-campo select, .cita-campo input[type="text"] { width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; }
        .slots-grid { display: flex; flex-wrap: wrap; gap: 8px; }
        .slot-btn { padding: 8px 12px; border: 1px solid #1d6fd8; background: #fff; color: #1d6fd8; border-radius: 6px; cursor: pointer; font-size: 0.88rem; }
        .slot-btn:hover:not(:disabled) { background: #1d6fd8; color: #fff; }
        .slot-activo { background: #1d6fd8 !important; color: #fff !important; font-weight: 700; }
        .slot-ocupado { background: #f1f5f9; color: #94a3b8; border-color: #cbd5e1; cursor: not-allowed; }
        .slots-vacio { color: #64748b; font-size: 0.92rem; }
        .fc-daygrid-day.cita-dia-seleccionado { background-color: #dbeafe !important; }
        .cita-resumen { margin: 14px 0; padding: 10px 12px; background: #f8fafc; border-radius: 6px; }
        .cita-btn-submit { background: #1d6fd8; color: #fff; border: none; padding: 11px 20px; border-radius: 6px; font-weight: 700; cursor: pointer; }
        .cita-btn-submit:disabled { background: #94a3b8; cursor: not-allowed; }
    </style>
</body>
</html>