<?php

require_once __DIR__ . '/../config/conexion.php';

class CitaModel
{
    private PDO $db;

    public function __construct(?PDO $conexion = null)
    {
        $this->db = $conexion ?? obtenerConexion();
    }

    public function obtenerEspecialidades(): array
    {
        $stmt = $this->db->query("SELECT id_especialidad, nombre_especialidad, descripcion FROM dbo.vw_especialidades_activas ORDER BY nombre_especialidad");
        return $stmt->fetchAll();
    }

    public function obtenerDoctoresPorEspecialidad(int $idEspecialidad): array
    {
        $sql = "SELECT
                    id_doctor,
                    id_especialidad,
                    nombre_especialidad,
                    cedula_profesional,
                    consultorio,
                    horario_lab,
                    nombre,
                    ape_paterno,
                    ape_materno
                FROM dbo.vw_doctores_por_especialidad
                WHERE id_especialidad = :id_especialidad
                ORDER BY ape_paterno, nombre";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_especialidad', $idEspecialidad, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function obtenerServiciosActivos(): array
    {
        $stmt = $this->db->query("SELECT id_servicio, nombre, descripcion, precio FROM servicios WHERE estado = 'Activo' ORDER BY nombre");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene las horas ocupadas (bloques de 30 min) para un doctor en un rango de fechas.
     */
    public function obtenerHorariosOcupados(int $idDoctor, string $fechaInicio, string $fechaFin): array
    {
        $this->db->exec("EXEC dbo.sp_aplicar_politicas_automaticas_citas");

        $sql = "SELECT
                    CONVERT(varchar(10), fecha_cita, 23) AS fecha_cita,
                    CONVERT(varchar(5), hora_cita, 108) AS hora_cita
                FROM cita
                WHERE id_doctor = :id_doctor
                  AND fecha_cita BETWEEN :fecha_inicio AND :fecha_fin
                  AND estado IN ('Programada', 'Confirmada')
                ORDER BY fecha_cita, hora_cita";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_doctor', $idDoctor, PDO::PARAM_INT);
        $stmt->bindValue(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
        $stmt->bindValue(':fecha_fin', $fechaFin, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function crearCita(array $datos): int
    {
        $sql = "EXEC dbo.sp_crear_cita
                    @id_paciente     = :id_paciente,
                    @id_doctor       = :id_doctor,
                    @id_servicio     = :id_servicio,
                    @fecha_cita      = :fecha_cita,
                    @hora_cita       = :hora_cita,
                    @motivo_consulta = :motivo_consulta";

        $stmt = $this->db->prepare($sql);

        $idServicio = !empty($datos['id_servicio']) ? (int) $datos['id_servicio'] : null;
        $motivo     = trim((string) ($datos['motivo_consulta'] ?? ''));

        $stmt->bindValue(':id_paciente', (int) $datos['id_paciente'], PDO::PARAM_INT);
        $stmt->bindValue(':id_doctor', (int) $datos['id_doctor'], PDO::PARAM_INT);
        $stmt->bindValue(':id_servicio', $idServicio, $idServicio !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':fecha_cita', $datos['fecha_cita'], PDO::PARAM_STR);
        $stmt->bindValue(':hora_cita', $datos['hora_cita'], PDO::PARAM_STR);
        $stmt->bindValue(':motivo_consulta', $motivo !== '' ? $motivo : null, PDO::PARAM_STR);

        $stmt->execute();
        $fila = $stmt->fetch();

        return isset($fila['id_cita_creada']) ? (int) $fila['id_cita_creada'] : 0;
    }

    public function obtenerCitasPaciente(int $idPaciente): array
    {
        $this->db->exec("EXEC dbo.sp_aplicar_politicas_automaticas_citas");

        $sql = "SELECT *
                FROM dbo.vw_citas_paciente
                WHERE id_paciente = :id_paciente
                ORDER BY fecha_cita DESC, hora_cita DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_paciente', $idPaciente, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function cancelarCitaPaciente(int $idCita, int $idPaciente): int
    {
        $sql = "EXEC dbo.sp_cancelar_cita_paciente
                    @id_cita     = :id_cita,
                    @id_paciente = :id_paciente";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_cita', $idCita, PDO::PARAM_INT);
        $stmt->bindValue(':id_paciente', $idPaciente, PDO::PARAM_INT);
        $stmt->execute();

        $fila = $stmt->fetch();
        return isset($fila['porcentaje_reembolso']) ? (int) $fila['porcentaje_reembolso'] : 0;
    }
}