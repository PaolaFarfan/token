<?php
require_once __DIR__ . "/../config/conexion.php";

class TokenModel {
    private $conexion;
    
    function __construct() {
        $this->conexion = new Conexion();
        $this->conexion = $this->conexion->connect();
    }

    // Obtener todos los tokens
    public function getTokens() {
        $tokens = array();
        $consulta = "SELECT t.*, u.nombre as usuario_nombre 
                    FROM token_api t 
                    LEFT JOIN usuarios u ON t.usuario_id = u.id 
                    ORDER BY t.fecha_registro DESC";
        
        $result = $this->conexion->query($consulta);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $tokens[] = $row;
            }
        }
        return $tokens;
    }

    // Obtener token por ID
    public function getToken($id) {
        $consulta = "SELECT t.*, u.nombre as usuario_nombre 
                    FROM token_api t 
                    LEFT JOIN usuarios u ON t.usuario_id = u.id 
                    WHERE t.id = ? LIMIT 1";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Actualizar token
    public function actualizarToken($id, $id_client_api, $token, $estado) {
        $consulta = "UPDATE token_api SET id_client_api = ?, token = ?, estado = ? WHERE id = ?";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bind_param("isii", $id_client_api, $token, $estado, $id);
        return $stmt->execute();
    }

    // Crear nuevo token
    public function crearToken($id_client_api, $token, $usuario_id) {
        $consulta = "INSERT INTO token_api (id_client_api, token, usuario_id, estado, fecha_registro) VALUES (?, ?, ?, 1, NOW())";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bind_param("isi", $id_client_api, $token, $usuario_id);
        
        if ($stmt->execute()) {
            return $this->conexion->insert_id;
        }
        return false;
    }

    // Eliminar token
    public function eliminarToken($id) {
        $consulta = "DELETE FROM token_api WHERE id = ?";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Verificar si token existe
    public function tokenExiste($token) {
        $consulta = "SELECT id FROM token_api WHERE token = ?";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
}
?>