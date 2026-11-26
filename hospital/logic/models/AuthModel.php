<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

class AuthModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = obtenerConexion();
    }

    public function obtenerUsuarioParaLogin(string $tipoAcceso, string $identificador): ?array
    {
        $stmt = $this->db->prepare("EXEC sp_obtener_credencial_login @tipo_acceso = :tipo_acceso, @identificador = :identificador");
        $stmt->bindValue(':tipo_acceso', $tipoAcceso, PDO::PARAM_STR);
        $stmt->bindValue(':identificador', $identificador, PDO::PARAM_STR);
        $stmt->execute();

        $fila = $stmt->fetch();
        return $fila ?: null;
    }
}