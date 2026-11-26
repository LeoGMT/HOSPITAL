USE HOSPITAL;
GO

------------------ validar formato de correo y tel no vaciio-----------------------------

IF NOT EXISTS (SELECT 1 FROM sys.check_constraints WHERE name = 'chk_usuario_correo_formato')
BEGIN
    ALTER TABLE usuario
    ADD CONSTRAINT chk_usuario_correo_formato
        CHECK (correo LIKE '%_@_%._%');
END
GO

--------------------VISTA para consultar usuarios con su rol------------------------

CREATE OR ALTER VIEW vw_usuarios_detalle
AS
SELECT
    u.id_usuario,
    u.id_tipo_rol,
    tr.nombre_rol,
    u.nombre,
    u.ape_paterno,
    u.ape_materno,
    u.telefono,
    u.correo,
    u.contrasena_hash,
    u.estado,
    u.fecha_registro
FROM usuario u
INNER JOIN Tipo_rol tr ON u.id_tipo_rol = tr.id_tipo_rol;
GO

--------------------- FUNCIoN escalar para verificar si un correo ya existe------------------------------

CREATE OR ALTER FUNCTION fn_correo_existe (@correo VARCHAR(100))
RETURNS BIT
AS
BEGIN
    DECLARE @existe BIT = 0;
    IF EXISTS (SELECT 1 FROM usuario WHERE LOWER(LTRIM(RTRIM(correo))) = LOWER(LTRIM(RTRIM(@correo))))
        SET @existe = 1;
    RETURN @existe;
END;
GO

--------------------- TRIGGER AFTER INSERT en usuario para crear registro base en empleados si el rol es empleado--------------------

CREATE OR ALTER TRIGGER trg_usuario_after_insert_empleado
ON usuario
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;

    INSERT INTO empleados (id_usuario, horario_lab, tipo_empleado)
    SELECT
        i.id_usuario,
        'Por asignar',
        tr.nombre_rol
    FROM inserted i
    INNER JOIN Tipo_rol tr ON i.id_tipo_rol = tr.id_tipo_rol
    WHERE tr.nombre_rol IN ('Administrador', 'Doctor', 'Recepcionista')
      AND NOT EXISTS (
          SELECT 1 FROM empleados e WHERE e.id_usuario = i.id_usuario
      );
END;
GO

--------- PROCEDIMIENTO transaccional para registro completo segun rol------------------

CREATE OR ALTER PROCEDURE sp_registrar_usuario
    @nombre_rol           VARCHAR(50),
    @nombre               VARCHAR(50),
    @ape_paterno          VARCHAR(50),
    @ape_materno          VARCHAR(50) = NULL,
    @telefono             VARCHAR(30) = NULL,
    @correo               VARCHAR(100),
    @contrasena_hash      VARCHAR(255),
    -- Campos para Paciente
    @fecha_nacimiento     DATE = NULL,
    @sexo                 VARCHAR(20) = NULL,
    @tipo_sangre          VARCHAR(10) = NULL,
    @telefono_emergencia  VARCHAR(30) = NULL,
    @contacto_emergencia  VARCHAR(100) = NULL,
    -- Campos para Empleado / Doctor / Admin / Recepcionista
    @horario_lab          VARCHAR(100) = 'Por asignar',
    @id_especialidad      INT = NULL,
    @cedula_profesional   VARCHAR(50) = NULL,
    @consultorio          VARCHAR(50) = NULL,
    @nivel_acceso         VARCHAR(50) = 'General',
    -- Salida
    @nuevo_id_usuario     INT OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    BEGIN TRY
        BEGIN TRANSACTION;

        DECLARE @id_tipo_rol INT;
        SELECT @id_tipo_rol = id_tipo_rol
        FROM Tipo_rol
        WHERE nombre_rol = @nombre_rol AND activo = 1;

        IF @id_tipo_rol IS NULL
            THROW 50001, 'El rol seleccionado no existe o está inactivo.', 1;

        IF dbo.fn_correo_existe(@correo) = 1
            THROW 50002, 'El correo electrónico ya está registrado.', 1;

        ----- Insertaa en usuario-----

        INSERT INTO usuario (
            id_tipo_rol, nombre, ape_paterno, ape_materno,
            telefono, correo, contrasena_hash, estado
        )
        VALUES (
            @id_tipo_rol,
            LTRIM(RTRIM(@nombre)),
            LTRIM(RTRIM(@ape_paterno)),
            NULLIF(LTRIM(RTRIM(@ape_materno)), ''),
            NULLIF(LTRIM(RTRIM(@telefono)), ''),
            LOWER(LTRIM(RTRIM(@correo))),
            @contrasena_hash,
            'Activo'
        );

        SET @nuevo_id_usuario = SCOPE_IDENTITY();

        DECLARE @num_empleado INT;
        SELECT @num_empleado = num_empleado
        FROM empleados
        WHERE id_usuario = @nuevo_id_usuario;

        IF @nombre_rol = 'Paciente'
        BEGIN
            IF @fecha_nacimiento IS NULL OR @sexo IS NULL
                THROW 50003, 'Faltan datos obligatorios del paciente (fecha de nacimiento o sexo).', 1;

            INSERT INTO paciente (
                id_usuario, fecha_nacimiento, sexo, tipo_sangre,
                telefono_emergencia, contacto_emergencia
            )
            VALUES (
                @nuevo_id_usuario,
                @fecha_nacimiento,
                @sexo,
                NULLIF(LTRIM(RTRIM(@tipo_sangre)), ''),
                NULLIF(LTRIM(RTRIM(@telefono_emergencia)), ''),
                NULLIF(LTRIM(RTRIM(@contacto_emergencia)), '')
            );
        END
        ELSE IF @nombre_rol = 'Doctor'
        BEGIN
            IF @num_empleado IS NULL OR @id_especialidad IS NULL OR @cedula_profesional IS NULL OR @consultorio IS NULL
                THROW 50004, 'Faltan datos obligatorios del doctor.', 1;

            UPDATE empleados
            SET horario_lab = COALESCE(NULLIF(@horario_lab, ''), 'Por asignar'),
                tipo_empleado = 'Doctor'
            WHERE num_empleado = @num_empleado;

            INSERT INTO doctor (
                id_usuario, num_empleado, cedula_profesional, especialidad, consultorio, id_especialidad
            )
            SELECT
                @nuevo_id_usuario,
                @num_empleado,
                LTRIM(RTRIM(@cedula_profesional)),
                e.nombre_especialidad,
                LTRIM(RTRIM(@consultorio)),
                @id_especialidad
            FROM especialidad e
            WHERE e.id_especialidad = @id_especialidad;
        END
        ELSE IF @nombre_rol = 'Administrador'
        BEGIN
            IF @num_empleado IS NULL
                THROW 50005, 'No se pudo generar el número de empleado para administrador.', 1;

            UPDATE empleados
            SET horario_lab = COALESCE(NULLIF(@horario_lab, ''), 'Por asignar'),
                tipo_empleado = 'Administrador'
            WHERE num_empleado = @num_empleado;

            INSERT INTO admin (id_usuario, num_empleado, nivel_acceso)
            VALUES (@nuevo_id_usuario, @num_empleado, COALESCE(NULLIF(@nivel_acceso, ''), 'General'));
        END
        ELSE IF @nombre_rol = 'Recepcionista'
        BEGIN
            IF @num_empleado IS NULL
                THROW 50006, 'No se pudo generar el número de empleado para recepcionista.', 1;

            UPDATE empleados
            SET horario_lab = COALESCE(NULLIF(@horario_lab, ''), 'Por asignar'),
                tipo_empleado = 'Recepcionista'
            WHERE num_empleado = @num_empleado;

            INSERT INTO recepcionista (id_usuario, num_empleado, estado)
            VALUES (@nuevo_id_usuario, @num_empleado, 'Activo');
        END

        COMMIT TRANSACTION;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;
        THROW;
    END CATCH
END;
GO

--------- VISTA de login-------------------------

CREATE OR ALTER VIEW vw_login_credenciales
AS
SELECT
    u.id_usuario,
    u.id_tipo_rol,
    tr.nombre_rol,
    u.nombre,
    u.ape_paterno,
    u.ape_materno,
    u.correo,
    u.contrasena_hash,
    u.estado AS estado_usuario,
    d.id_doctor,
    d.cedula_profesional,
    d.especialidad,
    d.consultorio
FROM usuario u
INNER JOIN Tipo_rol tr ON u.id_tipo_rol = tr.id_tipo_rol
LEFT JOIN doctor d ON d.id_usuario = u.id_usuario;
GO

-------fUNCION para validar si una cedula profesional existe en doctores------------------

CREATE OR ALTER FUNCTION fn_cedula_existe (@cedula VARCHAR(50))
RETURNS BIT
AS
BEGIN
    DECLARE @existe BIT = 0;
    IF EXISTS (
        SELECT 1
        FROM doctor
        WHERE UPPER(LTRIM(RTRIM(cedula_profesional))) = UPPER(LTRIM(RTRIM(@cedula)))
    )
        SET @existe = 1;
    RETURN @existe;
END;
GO

------------------- PROCEDIMIENTO para obtener datos de login por correo o cedula-------------------

CREATE OR ALTER PROCEDURE sp_obtener_credencial_login
    @tipo_acceso VARCHAR(20), -- 'CORREO' o 'CEDULA'
    @identificador VARCHAR(100)
AS
BEGIN
    SET NOCOUNT ON;

    IF @tipo_acceso = 'CEDULA'
    BEGIN
        SELECT TOP 1 *
        FROM vw_login_credenciales
        WHERE UPPER(LTRIM(RTRIM(cedula_profesional))) = UPPER(LTRIM(RTRIM(@identificador)))
          AND nombre_rol = 'Doctor';
    END
    ELSE
    BEGIN
        SELECT TOP 1 *
        FROM vw_login_credenciales
        WHERE LOWER(LTRIM(RTRIM(correo))) = LOWER(LTRIM(RTRIM(@identificador)));
    END
END;
GO