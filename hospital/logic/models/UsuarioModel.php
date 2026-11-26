<?php

require_once __DIR__ . '/../config/conexion.php';

class UsuarioModel
{
    private PDO $db;

    public function __construct(?PDO $conexion = null)
    {
        $this->db = $conexion ?? obtenerConexion();
    }

    /**
     * Registra un usuario ejecutando el procedimiento almacenado dbo.sp_registrar_usuario.
     *
     * @param array $datos
     * @return int ID del usuario creado
     */
    public function registrar(array $datos): int
    {
        $hashPassword = password_hash($datos['password'], PASSWORD_BCRYPT);

        if ($hashPassword === false) {
            throw new RuntimeException('No fue posible generar el hash de la contraseña.');
        }

        $sql = "EXEC dbo.sp_registrar_usuario
                    @nombre_rol          = :nombre_rol,
                    @nombre              = :nombre,
                    @ape_paterno         = :ape_paterno,
                    @ape_materno         = :ape_materno,
                    @telefono            = :telefono,
                    @correo              = :correo,
                    @contrasena_hash     = :contrasena_hash,
                    @horario_lab         = :horario_lab,
                    @nivel_acceso        = :nivel_acceso,
                    @id_especialidad     = :id_especialidad,
                    @cedula_profesional  = :cedula_profesional,
                    @consultorio         = :consultorio,
                    @fecha_nacimiento    = :fecha_nacimiento,
                    @sexo                = :sexo,
                    @tipo_sangre         = :tipo_sangre,
                    @telefono_emergencia = :telefono_emergencia,
                    @contacto_emergencia = :contacto_emergencia";

        $stmt = $this->db->prepare($sql);

        $idEspecialidad  = !empty($datos['id_especialidad']) ? (int) $datos['id_especialidad'] : null;
        $fechaNacimiento = !empty($datos['fecha_nacimiento']) ? $datos['fecha_nacimiento'] : null;

        $stmt->bindValue(':nombre_rol', $datos['rol'], PDO::PARAM_STR);
        $stmt->bindValue(':nombre', $datos['nombre'], PDO::PARAM_STR);
        $stmt->bindValue(':ape_paterno', $datos['ape_paterno'], PDO::PARAM_STR);
        $stmt->bindValue(':ape_materno', $datos['ape_materno'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':telefono', $datos['telefono'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':correo', $datos['correo'], PDO::PARAM_STR);
        $stmt->bindValue(':contrasena_hash', $hashPassword, PDO::PARAM_STR);

        $stmt->bindValue(':horario_lab', $datos['horario_lab'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':nivel_acceso', $datos['nivel_acceso'] ?? null, PDO::PARAM_STR);

        $stmt->bindValue(':id_especialidad', $idEspecialidad, $idEspecialidad !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':cedula_profesional', $datos['cedula_profesional'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':consultorio', $datos['consultorio'] ?? null, PDO::PARAM_STR);

        $stmt->bindValue(':fecha_nacimiento', $fechaNacimiento, $fechaNacimiento !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':sexo', $datos['sexo'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':tipo_sangre', $datos['tipo_sangre'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':telefono_emergencia', $datos['telefono_emergencia'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':contacto_emergencia', $datos['contacto_emergencia'] ?? null, PDO::PARAM_STR);

        $stmt->execute();
        $fila = $stmt->fetch();

        return isset($fila['id_usuario_creado']) ? (int) $fila['id_usuario_creado'] : 0;
    }

    /**
     * Busca usuario para inicio de sesión por correo o cédula profesional y verifica la contraseña.
     *
     * @param string $tipoAcceso 'correo' | 'cedula'
     * @param string $identificador
     * @param string $passwordPlano
     * @return array|null
     */
    public function autenticar(string $tipoAcceso, string $identificador, string $passwordPlano): ?array
    {
        $sql = "EXEC dbo.sp_obtener_usuario_login
                    @tipo_acceso = :tipo_acceso,
                    @identificador = :identificador";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tipo_acceso', $tipoAcceso, PDO::PARAM_STR);
        $stmt->bindValue(':identificador', $identificador, PDO::PARAM_STR);
        $stmt->execute();

        $usuario = $stmt->fetch();

        if (!$usuario) {
            return null;
        }

        if ($usuario['estado_usuario'] !== 'Activo') {
            return null;
        }

        if ($tipoAcceso === 'cedula' && $usuario['nombre_rol'] !== 'Doctor') {
            return null;
        }

        if (!password_verify($passwordPlano, $usuario['contrasena_hash'])) {
            return null;
        }

        unset($usuario['contrasena_hash']);
        return $usuario;
    }
}