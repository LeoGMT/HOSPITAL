USE HOSPITAL;
GO

-- Procedimiento: actualiza estados automáticos de política (prepago vencido > 8h y no asistencia)
CREATE OR ALTER PROCEDURE dbo.sp_aplicar_politicas_automaticas_citas
AS
BEGIN
    SET NOCOUNT ON;

    -- 1. Cita prepago sin confirmar tras 8 horas -> Cancelada
    UPDATE cita
    SET
        estado = 'Cancelada',
        estado_pago = 'Cancelado',
        fecha_cancelacion = GETDATE()
    WHERE estado = 'Programada'
      AND estado_pago = 'Pendiente'
      AND DATEDIFF(HOUR, fecha_solicitud, GETDATE()) >= 8;

    -- 2. Cita confirmada que ya pasó su hora programada y no fue completada -> No asistio
    UPDATE cita
    SET
        estado = 'No asistio'
    WHERE estado = 'Confirmada'
      AND DATETIMEFROMPARTS(
            YEAR(fecha_cita), MONTH(fecha_cita), DAY(fecha_cita),
            DATEPART(HOUR, hora_cita), DATEPART(MINUTE, hora_cita), DATEPART(SECOND, hora_cita), 0
          ) < GETDATE();
END;
GO

-- Procedimiento: crea una nueva cita validando todas las reglas de negocio
CREATE OR ALTER PROCEDURE dbo.sp_crear_cita
    @id_paciente     INT,
    @id_doctor       INT,
    @id_servicio     INT          = NULL,
    @fecha_cita      DATE,
    @hora_cita       TIME(0),
    @motivo_consulta VARCHAR(255) = NULL,
    @id_cita_creada  INT OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    DECLARE @fecha_hora_cita DATETIME;
    DECLARE @ahora DATETIME = GETDATE();
    DECLARE @min_permitido DATETIME = DATEADD(HOUR, 48, @ahora);
    DECLARE @max_permitido DATETIME = DATEADD(MONTH, 3, @ahora);
    DECLARE @minutos INT;

    BEGIN TRY
        BEGIN TRANSACTION;

        EXEC dbo.sp_aplicar_politicas_automaticas_citas;

        SET @fecha_hora_cita = DATETIMEFROMPARTS(
            YEAR(@fecha_cita), MONTH(@fecha_cita), DAY(@fecha_cita),
            DATEPART(HOUR, @hora_cita), DATEPART(MINUTE, @hora_cita), DATEPART(SECOND, @hora_cita), 0
        );

        -- No fecha/hora pasada
        IF @fecha_hora_cita <= @ahora
            THROW 52001, 'No se puede solicitar una cita con fecha u hora pasada.', 1;

        -- Mínimo 48 horas de anticipación (no hay citas de hoy para hoy)
        IF @fecha_hora_cita < @min_permitido
            THROW 52002, 'La cita debe agendarse con al menos 48 horas de anticipación.', 1;

        -- Máximo 3 meses de anticipación
        IF @fecha_hora_cita > @max_permitido
            THROW 52003, 'La cita no puede agendarse con más de 3 meses de anticipación.', 1;

        -- Bloques de 30 minutos (:00 o :30)
        SET @minutos = DATEPART(MINUTE, @hora_cita);
        IF @minutos NOT IN (0, 30) OR DATEPART(SECOND, @hora_cita) <> 0
            THROW 52004, 'Cada cita tiene duración de 30 minutos; selecciona un horario en intervalos de media hora.', 1;

        -- Verificar que el paciente exista
        IF NOT EXISTS (SELECT 1 FROM paciente WHERE id_paciente = @id_paciente)
            THROW 52005, 'El paciente indicado no existe.', 1;

        -- Verificar que el doctor exista y esté activo
        IF NOT EXISTS (
            SELECT 1
            FROM doctor d
            INNER JOIN usuario u ON d.id_usuario = u.id_usuario
            WHERE d.id_doctor = @id_doctor AND u.estado = 'Activo'
        )
            THROW 52006, 'El doctor seleccionado no está disponible.', 1;

        -- No se puede agendar si ya tiene cita pendiente/activa con el mismo doctor
        IF dbo.fn_paciente_tiene_cita_pendiente_doctor(@id_paciente, @id_doctor) = 1
            THROW 52007, 'Ya cuentas con una cita pendiente con este doctor. Debes concluirla o cancelarla antes de solicitar otra.', 1;

        -- Verificar disponibilidad del doctor en esa fecha y hora
        IF EXISTS (
            SELECT 1
            FROM cita
            WHERE id_doctor = @id_doctor
              AND fecha_cita = @fecha_cita
              AND hora_cita = @hora_cita
              AND estado IN ('Programada', 'Confirmada')
        )
            THROW 52008, 'Ese horario ya está ocupado para el doctor seleccionado.', 1;

        -- Validar servicio si se envía
        IF @id_servicio IS NOT NULL
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM servicios WHERE id_servicio = @id_servicio AND estado = 'Activo')
                THROW 52009, 'El servicio seleccionado no existe o está inactivo.', 1;
        END

        INSERT INTO cita (
            id_paciente, id_doctor, id_servicio, fecha_cita, hora_cita,
            fecha_solicitud, motivo_consulta, estado, estado_pago
        )
        VALUES (
            @id_paciente,
            @id_doctor,
            @id_servicio,
            @fecha_cita,
            @hora_cita,
            @ahora,
            NULLIF(LTRIM(RTRIM(@motivo_consulta)), ''),
            'Programada',
            'Pendiente'
        );

        SET @id_cita_creada = SCOPE_IDENTITY();

        COMMIT TRANSACTION;

        SELECT @id_cita_creada AS id_cita_creada;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;
        THROW;
    END CATCH
END;
GO

-- Procedimiento: cancela una cita del paciente y calcula el porcentaje de devolución
CREATE OR ALTER PROCEDURE dbo.sp_cancelar_cita_paciente
    @id_cita              INT,
    @id_paciente          INT,
    @porcentaje_reembolso INT OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    DECLARE @estado_actual VARCHAR(30);
    DECLARE @estado_pago_actual VARCHAR(30);
    DECLARE @fecha_cita DATE;
    DECLARE @hora_cita TIME(0);

    BEGIN TRY
        BEGIN TRANSACTION;

        EXEC dbo.sp_aplicar_politicas_automaticas_citas;

        SELECT
            @estado_actual = estado,
            @estado_pago_actual = estado_pago,
            @fecha_cita = fecha_cita,
            @hora_cita = hora_cita
        FROM cita
        WHERE id_cita = @id_cita
          AND id_paciente = @id_paciente;

        IF @estado_actual IS NULL
            THROW 52010, 'La cita no existe o no pertenece a tu cuenta.', 1;

        IF @estado_actual NOT IN ('Programada', 'Confirmada')
            THROW 52011, 'Solo puedes cancelar citas programadas o confirmadas.', 1;

        SET @porcentaje_reembolso = dbo.fn_calcular_reembolso_cancelacion(@fecha_cita, @hora_cita);

        UPDATE cita
        SET
            estado = 'Cancelada',
            fecha_cancelacion = GETDATE(),
            estado_pago = CASE
                WHEN @estado_pago_actual = 'Pagado' AND @porcentaje_reembolso > 0 THEN 'Reembolsado'
                WHEN @estado_pago_actual = 'Pendiente' THEN 'Cancelado'
                ELSE @estado_pago_actual
            END
        WHERE id_cita = @id_cita;

        COMMIT TRANSACTION;

        SELECT @porcentaje_reembolso AS porcentaje_reembolso;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;
        THROW;
    END CATCH
END;
GO