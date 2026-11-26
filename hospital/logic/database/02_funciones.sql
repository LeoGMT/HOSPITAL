USE HOSPITAL;
GO

-- Función: obtiene id_tipo_rol a partir del nombre del rol (devuelve NULL si no existe o está inactivo)
CREATE OR ALTER FUNCTION dbo.fn_obtener_id_rol (@nombre_rol VARCHAR(50))
RETURNS INT
AS
BEGIN
    DECLARE @id_rol INT;

    SELECT @id_rol = id_tipo_rol
    FROM Tipo_rol
    WHERE LOWER(LTRIM(RTRIM(nombre_rol))) = LOWER(LTRIM(RTRIM(@nombre_rol)))
      AND activo = 1;

    RETURN @id_rol;
END;
GO

-- Función: verifica si un correo ya está registrado (1 = existe, 0 = disponible)
CREATE OR ALTER FUNCTION dbo.fn_correo_existe (@correo VARCHAR(100))
RETURNS BIT
AS
BEGIN
    IF EXISTS (
        SELECT 1
        FROM usuario
        WHERE LOWER(LTRIM(RTRIM(correo))) = LOWER(LTRIM(RTRIM(@correo)))
    )
        RETURN 1;

    RETURN 0;
END;
GO

-- Función: verifica si una cédula profesional ya está registrada (1 = existe, 0 = disponible)
CREATE OR ALTER FUNCTION dbo.fn_cedula_existe (@cedula VARCHAR(50))
RETURNS BIT
AS
BEGIN
    IF EXISTS (
        SELECT 1
        FROM doctor
        WHERE UPPER(LTRIM(RTRIM(cedula_profesional))) = UPPER(LTRIM(RTRIM(@cedula)))
    )
        RETURN 1;

    RETURN 0;
END;
GO