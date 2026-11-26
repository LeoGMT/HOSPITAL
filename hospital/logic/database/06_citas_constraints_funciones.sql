USE HOSPITAL;
GO

-- Evita duplicar exactamente la misma fecha y hora para el mismo doctor en citas activas
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'uq_cita_doctor_fecha_hora_activa')
BEGIN
    CREATE UNIQUE INDEX uq_cita_doctor_fecha_hora_activa
        ON cita (id_doctor, fecha_cita, hora_cita)
        WHERE estado IN ('Programada', 'Confirmada');
END
GO

-- Función: calcula el porcentaje de devolución según horas de anticipación a la cita
CREATE OR ALTER FUNCTION dbo.fn_calcular_reembolso_cancelacion (
    @fecha_cita DATE,
    @hora_cita  TIME(0)
)
RETURNS INT
AS
BEGIN
    DECLARE @fecha_hora_cita DATETIME = DATETIMEFROMPARTS(
        YEAR(@fecha_cita), MONTH(@fecha_cita), DAY(@fecha_cita),
        DATEPART(HOUR, @hora_cita), DATEPART(MINUTE, @hora_cita), DATEPART(SECOND, @hora_cita), 0
    );

    DECLARE @horas_anticipacion INT = DATEDIFF(HOUR, GETDATE(), @fecha_hora_cita);

    IF @horas_anticipacion >= 48
        RETURN 100;
    IF @horas_anticipacion >= 24
        RETURN 50;

    RETURN 0;
END;
GO

-- Función: verifica si el paciente ya tiene una cita pendiente/activa con ese doctor
CREATE OR ALTER FUNCTION dbo.fn_paciente_tiene_cita_pendiente_doctor (
    @id_paciente INT,
    @id_doctor   INT
)
RETURNS BIT
AS
BEGIN
    IF EXISTS (
        SELECT 1
        FROM cita
        WHERE id_paciente = @id_paciente
          AND id_doctor = @id_doctor
          AND estado IN ('Programada', 'Confirmada')
    )
        RETURN 1;

    RETURN 0;
END;
GO