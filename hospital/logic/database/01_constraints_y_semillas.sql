USE HOSPITAL;
GO

-- Constraints adicionales para integridad de datos de registro y acceso
IF NOT EXISTS (SELECT 1 FROM sys.check_constraints WHERE name = 'chk_usuario_correo_formato')
BEGIN
    ALTER TABLE usuario
    ADD CONSTRAINT chk_usuario_correo_formato
        CHECK (correo LIKE '%_@_%._%');
END
GO

IF NOT EXISTS (SELECT 1 FROM sys.check_constraints WHERE name = 'chk_admin_nivel_acceso')
BEGIN
    ALTER TABLE admin
    ADD CONSTRAINT chk_admin_nivel_acceso
        CHECK (LEN(LTRIM(RTRIM(nivel_acceso))) > 0);
END
GO

-- Roles base del sistema
IF NOT EXISTS (SELECT 1 FROM Tipo_rol WHERE nombre_rol = 'Administrador')
    INSERT INTO Tipo_rol (nombre_rol, descripcion) VALUES ('Administrador', 'Administración general del sistema hospitalario');

IF NOT EXISTS (SELECT 1 FROM Tipo_rol WHERE nombre_rol = 'Paciente')
    INSERT INTO Tipo_rol (nombre_rol, descripcion) VALUES ('Paciente', 'Usuario paciente para citas, recetas y pagos');

IF NOT EXISTS (SELECT 1 FROM Tipo_rol WHERE nombre_rol = 'Doctor')
    INSERT INTO Tipo_rol (nombre_rol, descripcion) VALUES ('Doctor', 'Médico especialista con cédula profesional');

IF NOT EXISTS (SELECT 1 FROM Tipo_rol WHERE nombre_rol = 'Recepcionista')
    INSERT INTO Tipo_rol (nombre_rol, descripcion) VALUES ('Recepcionista', 'Personal de recepción, agenda y caja');
GO

-- Especialidades base (coincidentes con el select de registro.php)
IF NOT EXISTS (SELECT 1 FROM especialidad WHERE nombre_especialidad = 'Medicina General')
    INSERT INTO especialidad (nombre_especialidad, descripcion) VALUES ('Medicina General', 'Atención médica primaria');

IF NOT EXISTS (SELECT 1 FROM especialidad WHERE nombre_especialidad = 'Pediatría')
    INSERT INTO especialidad (nombre_especialidad, descripcion) VALUES ('Pediatría', 'Atención médica infantil');

IF NOT EXISTS (SELECT 1 FROM especialidad WHERE nombre_especialidad = 'Cardiología')
    INSERT INTO especialidad (nombre_especialidad, descripcion) VALUES ('Cardiología', 'Diagnóstico y tratamiento cardiovascular');

IF NOT EXISTS (SELECT 1 FROM especialidad WHERE nombre_especialidad = 'Ginecología')
    INSERT INTO especialidad (nombre_especialidad, descripcion) VALUES ('Ginecología', 'Salud femenina y obstetricia');
GO