USE HOSPITAL;
GO

CREATE OR ALTER PROCEDURE dbo.sp_registrar_usuario
    @nombre_rol           VARCHAR(50),
    @nombre               VARCHAR(50),
    @ape_paterno          VARCHAR(50),
    @ape_materno          VARCHAR(50)  = NULL,
    @telefono             VARCHAR(30)  = NULL,
    @correo               VARCHAR(100),
    @contrasena_hash      VARCHAR(255),
    -- Campos de empleado (Doctor, Administrador, Recepcionista)
    @horario_lab          VARCHAR(100) = NULL,
    -- Campos de Administrador
    @nivel_acceso         VARCHAR(50)  = NULL,
    -- Campos de Doctor
    @id_especialidad      INT          = NULL,
    @cedula_profesional   VARCHAR(50)  = NULL,
    @consultorio          VARCHAR(50)  = NULL,
    -- Campos de Paciente
    @fecha_nacimiento     DATE         = NULL,
    @sexo                 VARCHAR(20)  = NULL,
    @tipo_sangre          VARCHAR(10)  = NULL,
    @telefono_emergencia  VARCHAR(30)  = NULL,
    @contacto_emergencia  VARCHAR(100) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    DECLARE @id_rol INT;
    DECLARE @num_empleado INT;
    DECLARE @id_usuario_creado INT;
    DECLARE @rol_limpio VARCHAR(50) = LTRIM(RTRIM(@nombre_rol));

    BEGIN TRY
        BEGIN TRANSACTION;

        SET @id_rol = dbo.fn_obtener_id_rol(@rol_limpio);
        IF @id_rol IS NULL
            THROW 51001, 'El rol seleccionado no es válido o está inactivo.', 1;

        IF dbo.fn_correo_existe(@correo) = 1
            THROW 51002, 'El correo electrónico ya se encuentra registrado.', 1;

        INSERT INTO usuario (
            id_tipo_rol, nombre, ape_paterno, ape_materno,
            telefono, correo, contrasena_hash, estado
        )
        VALUES (
            @id_rol,
            LTRIM(RTRIM(@nombre)),
            LTRIM(RTRIM(@ape_paterno)),
            NULLIF(LTRIM(RTRIM(@ape_materno)), ''),
            NULLIF(LTRIM(RTRIM(@telefono)), ''),
            LOWER(LTRIM(RTRIM(@correo))),
            @contrasena_hash,
            'Activo'
        );

        SET @id_usuario_creado = SCOPE_IDENTITY();

        IF @rol_limpio = 'Paciente'
        BEGIN
            IF @fecha_nacimiento IS NULL OR @sexo IS NULL OR LTRIM(RTRIM(@sexo)) = ''
                THROW 51003, 'Faltan datos obligatorios del paciente (fecha de nacimiento o sexo).', 1;

            INSERT INTO paciente (
                id_usuario, fecha_nacimiento, sexo, tipo_sangre,
                telefono_emergencia, contacto_emergencia
            )
            VALUES (
                @id_usuario_creado,
                @fecha_nacimiento,
                LTRIM(RTRIM(@sexo)),
                NULLIF(LTRIM(RTRIM(@tipo_sangre)), ''),
                NULLIF(LTRIM(RTRIM(@telefono_emergencia)), ''),
                NULLIF(LTRIM(RTRIM(@contacto_emergencia)), '')
            );
        END
        ELSE IF @rol_limpio IN ('Doctor', 'Administrador', 'Recepcionista')
        BEGIN
            INSERT INTO empleados (id_usuario, horario_lab, tipo_empleado)
            VALUES (
                @id_usuario_creado,
                NULLIF(LTRIM(RTRIM(@horario_lab)), ''),
                @rol_limpio
            );

            SET @num_empleado = SCOPE_IDENTITY();

            IF @rol_limpio = 'Doctor'
            BEGIN
                IF @id_especialidad IS NULL
                   OR @cedula_profesional IS NULL OR LTRIM(RTRIM(@cedula_profesional)) = ''
                   OR @consultorio IS NULL OR LTRIM(RTRIM(@consultorio)) = ''
                    THROW 51004, 'Faltan datos obligatorios del doctor (especialidad, cédula o consultorio).', 1;

                IF dbo.fn_cedula_existe(@cedula_profesional) = 1
                    THROW 51005, 'La cédula profesional ya está registrada.', 1;

                INSERT INTO doctor (
                    id_usuario, num_empleado, cedula_profesional,
                    especialidad, consultorio, id_especialidad
                )
                SELECT
                    @id_usuario_creado,
                    @num_empleado,
                    UPPER(LTRIM(RTRIM(@cedula_profesional))),
                    e.nombre_especialidad,
                    LTRIM(RTRIM(@consultorio)),
                    e.id_especialidad
                FROM especialidad e
                WHERE e.id_especialidad = @id_especialidad;

                IF @@ROWCOUNT = 0
                    THROW 51006, 'La especialidad seleccionada no existe.', 1;
            END
            ELSE IF @rol_limpio = 'Administrador'
            BEGIN
                IF @nivel_acceso IS NULL OR LTRIM(RTRIM(@nivel_acceso)) = ''
                    THROW 51007, 'El nivel de acceso es obligatorio para el rol Administrador.', 1;

                INSERT INTO admin (id_usuario, num_empleado, nivel_acceso)
                VALUES (
                    @id_usuario_creado,
                    @num_empleado,
                    LTRIM(RTRIM(@nivel_acceso))
                );
            END
            ELSE IF @rol_limpio = 'Recepcionista'
            BEGIN
                INSERT INTO recepcionista (id_usuario, num_empleado, estado)
                VALUES (
                    @id_usuario_creado,
                    @num_empleado,
                    'Activo'
                );
            END
        END
        ELSE
        BEGIN
            THROW 51008, 'Rol no soportado en el registro.', 1;
        END

        COMMIT TRANSACTION;

        SELECT @id_usuario_creado AS id_usuario_creado;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;
        THROW;
    END CATCH
END;
GO

CREATE OR ALTER PROCEDURE dbo.sp_obtener_usuario_login
    @tipo_acceso   VARCHAR(20),
    @identificador VARCHAR(100)
AS
BEGIN
    SET NOCOUNT ON;

    IF @tipo_acceso = 'cedula'
    BEGIN
        SELECT TOP 1
            id_usuario,
            id_tipo_rol,
            nombre_rol,
            nombre,
            ape_paterno,
            ape_materno,
            correo,
            contrasena_hash,
            estado_usuario,
            id_doctor,
            cedula_profesional,
            consultorio,
            id_especialidad,
            nombre_especialidad,
            NULL AS id_paciente,
            NULL AS id_admin,
            NULL AS nivel_acceso,
            NULL AS id_recepcionista
        FROM dbo.vw_auth_doctor_cedula
        WHERE UPPER(LTRIM(RTRIM(cedula_profesional))) = UPPER(LTRIM(RTRIM(@identificador)));
    END
    ELSE
    BEGIN
        SELECT TOP 1
            id_usuario,
            id_tipo_rol,
            nombre_rol,
            nombre,
            ape_paterno,
            ape_materno,
            correo,
            contrasena_hash,
            estado_usuario,
            id_doctor,
            cedula_profesional,
            consultorio,
            id_especialidad,
            NULL AS nombre_especialidad,
            id_paciente,
            id_admin,
            nivel_acceso,
            id_recepcionista
        FROM dbo.vw_auth_por_correo
        WHERE LOWER(LTRIM(RTRIM(correo))) = LOWER(LTRIM(RTRIM(@identificador)));
    END
END;
GO