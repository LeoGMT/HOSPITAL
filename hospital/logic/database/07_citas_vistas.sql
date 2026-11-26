USE HOSPITAL;
GO

-- Vista de especialidades activas
CREATE OR ALTER VIEW dbo.vw_especialidades_activas
AS
SELECT
    id_especialidad,
    nombre_especialidad,
    descripcion
FROM especialidad
WHERE activa = 1;
GO

-- Vista de doctores con su especialidad y usuario
CREATE OR ALTER VIEW dbo.vw_doctores_por_especialidad
AS
SELECT
    d.id_doctor,
    d.id_usuario,
    d.id_especialidad,
    e.nombre_especialidad,
    d.cedula_profesional,
    d.consultorio,
    emp.horario_lab,
    u.nombre,
    u.ape_paterno,
    u.ape_materno,
    u.estado AS estado_usuario
FROM doctor d
INNER JOIN especialidad e ON d.id_especialidad = e.id_especialidad
INNER JOIN usuario u ON d.id_usuario = u.id_usuario
LEFT JOIN empleados emp ON d.num_empleado = emp.num_empleado
WHERE u.estado = 'Activo'
  AND e.activa = 1;
GO

-- Vista detallada de citas para el paciente
CREATE OR ALTER VIEW dbo.vw_citas_paciente
AS
SELECT
    c.id_cita,
    c.id_paciente,
    c.id_doctor,
    c.id_servicio,
    c.fecha_cita,
    c.hora_cita,
    c.fecha_solicitud,
    c.motivo_consulta,
    c.fecha_cancelacion,
    c.estado,
    c.estado_pago,
    d.cedula_profesional,
    d.consultorio,
    e.nombre_especialidad,
    ud.nombre AS doctor_nombre,
    ud.ape_paterno AS doctor_ape_paterno,
    ud.ape_materno AS doctor_ape_materno,
    ISNULL(s.nombre, 'Consulta General') AS nombre_servicio,
    ISNULL(s.precio, 0) AS precio_servicio,
    dbo.fn_calcular_reembolso_cancelacion(c.fecha_cita, c.hora_cita) AS porcentaje_reembolso_estimado,
    DATEADD(HOUR, 8, c.fecha_solicitud) AS limite_pago_prepago
FROM cita c
INNER JOIN doctor d ON c.id_doctor = d.id_doctor
INNER JOIN usuario ud ON d.id_usuario = ud.id_usuario
INNER JOIN especialidad e ON d.id_especialidad = e.id_especialidad
LEFT JOIN servicios s ON c.id_servicio = s.id_servicio;
GO