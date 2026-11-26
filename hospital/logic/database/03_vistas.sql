USE HOSPITAL;
GO

-- Vista para inicio de sesión por correo (Pacientes, Administradores, Recepcionistas y Doctores si usan correo)
CREATE OR ALTER VIEW dbo.vw_auth_por_correo
AS
SELECT
    u.id_usuario,
    u.id_tipo_rol,
    r.nombre_rol,
    u.nombre,
    u.ape_paterno,
    u.ape_materno,
    u.telefono,
    u.correo,
    u.contrasena_hash,
    u.estado AS estado_usuario,
    d.id_doctor,
    d.cedula_profesional,
    d.consultorio,
    d.id_especialidad,
    p.id_paciente,
    a.id_admin,
    a.nivel_acceso,
    rec.id_recepcionista,
    rec.estado AS estado_recepcionista
FROM usuario u
INNER JOIN Tipo_rol r ON u.id_tipo_rol = r.id_tipo_rol
LEFT JOIN doctor d ON u.id_usuario = d.id_usuario
LEFT JOIN paciente p ON u.id_usuario = p.id_usuario
LEFT JOIN admin a ON u.id_usuario = a.id_usuario
LEFT JOIN recepcionista rec ON u.id_usuario = rec.id_usuario;
GO

-- Vista exclusiva para inicio de sesión de doctores mediante cédula profesional
CREATE OR ALTER VIEW dbo.vw_auth_doctor_cedula
AS
SELECT
    u.id_usuario,
    u.id_tipo_rol,
    r.nombre_rol,
    u.nombre,
    u.ape_paterno,
    u.ape_materno,
    u.telefono,
    u.correo,
    u.contrasena_hash,
    u.estado AS estado_usuario,
    d.id_doctor,
    d.num_empleado,
    d.cedula_profesional,
    d.consultorio,
    d.id_especialidad,
    e.nombre_especialidad
FROM doctor d
INNER JOIN usuario u ON d.id_usuario = u.id_usuario
INNER JOIN Tipo_rol r ON u.id_tipo_rol = r.id_tipo_rol
INNER JOIN especialidad e ON d.id_especialidad = e.id_especialidad;
GO