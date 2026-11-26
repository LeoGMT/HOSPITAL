USE HOSPITAL;
GO

-- Trigger: normaliza correo y limpia espacios al insertar o actualizar usuario
CREATE OR ALTER TRIGGER dbo.trg_usuario_normalizar
ON usuario
AFTER INSERT, UPDATE
AS
BEGIN
    SET NOCOUNT ON;

    IF TRIGGER_NESTLEVEL() > 1
        RETURN;

    UPDATE u
    SET
        u.correo = LOWER(LTRIM(RTRIM(i.correo))),
        u.nombre = LTRIM(RTRIM(i.nombre)),
        u.ape_paterno = LTRIM(RTRIM(i.ape_paterno)),
        u.ape_materno = CASE
            WHEN i.ape_materno IS NULL THEN NULL
            ELSE NULLIF(LTRIM(RTRIM(i.ape_materno)), '')
        END
    FROM usuario u
    INNER JOIN inserted i ON u.id_usuario = i.id_usuario;
END;
GO