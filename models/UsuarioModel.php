<?php
require_once __DIR__ . "/../config/conexion.php";

class UsuarioModel {
    private $conexion;
    
    function __construct() {
        $this->conexion = new Conexion();
        $this->conexion = $this->conexion->connect();
        
        if (!$this->conexion) {
            throw new Exception("Error al conectar con la base de datos");
        }
    }

    // Login de usuario
    public function login($username, $password) {
        try {
            $consulta = "SELECT id, username, password, nombre, rol FROM usuarios WHERE username = ? AND estado = 1 LIMIT 1";
            $stmt = $this->conexion->prepare($consulta);
            
            if (!$stmt) {
                error_log("Error en prepare: " . $this->conexion->error);
                return false;
            }
            
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    return $user;
                }
            }
            return false;
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            return false;
        }
    }

    // Obtener usuario por ID
    public function getUsuario($id) {
        try {
            $consulta = "SELECT id, username, email, nombre, rol, fecha_creacion, ultimo_login FROM usuarios WHERE id = ?";
            $stmt = $this->conexion->prepare($consulta);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error obteniendo usuario: " . $e->getMessage());
            return null;
        }
    }

    // Actualizar último login
    public function actualizarUltimoLogin($id) {
        try {
            $consulta = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?";
            $stmt = $this->conexion->prepare($consulta);
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error actualizando login: " . $e->getMessage());
            return false;
        }
    }

    // Verificar si usuario existe
    public function usuarioExiste($username) {
        try {
            $consulta = "SELECT id FROM usuarios WHERE username = ?";
            $stmt = $this->conexion->prepare($consulta);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->num_rows > 0;
        } catch (Exception $e) {
            error_log("Error verificando usuario: " . $e->getMessage());
            return false;
        }
    }
}
?>