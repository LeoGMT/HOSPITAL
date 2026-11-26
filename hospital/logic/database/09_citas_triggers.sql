USE HOSPITAL;
GO

-- Trigger: impide modificar fecha/hora de una cita existente (no se permiten reagendaciones)
CREATE OR ALTER TRIGGER dbo.trg_cita_no_reagendar
ON cita
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;

    IF TRIGGER_NESTLEVEL() > 1
        RETURN;

    IF EXISTS (
        SELECT 1
        FROM inserted i
        INNER JOIN deleted d ON i.id_cita = d.id_cita
        WHERE i.fecha_cita <> d.fecha_cita
           OR i.hora_cita <> d.hora_cita
    )
    BEGIN
        ROLLBACK TRANSACTION;
        THROW 52012, 'No se pueden reagendar citas; debes cancelar la actual y solicitar una nueva.', 1;
    END
END;
GO