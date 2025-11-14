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

    // Crear token con metadatos (nombre, descripcion, auth_url, expires_at)
    public function crearTokenConMeta($id_client_api, $token, $usuario_id, $nombre = null, $descripcion = null, $auth_url = null, $expires_at = null) {
        $consulta = "INSERT INTO token_api (id_client_api, token, usuario_id, nombre, descripcion, auth_url, expires_at, estado, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bind_param("issssss", $id_client_api, $token, $usuario_id, $nombre, $descripcion, $auth_url, $expires_at);
        if ($stmt->execute()) {
            return $this->conexion->insert_id;
        }
        return false;
    }

    // Buscar tokens por token, nombre o descripcion
    public function searchTokens($q) {
        $like = '%' . $q . '%';

        // Determinar columnas disponibles
        $fields = ['t.token'];
        if ($this->columnExists('nombre')) {
            $fields[] = 't.nombre';
        }
        if ($this->columnExists('descripcion')) {
            $fields[] = 't.descripcion';
        }

        // Construir WHERE dinámico
        $whereParts = [];
        foreach ($fields as $f) {
            $whereParts[] = "$f LIKE ?";
        }

        $where = implode(' OR ', $whereParts);
        $consulta = "SELECT t.*, u.nombre as usuario_nombre FROM token_api t LEFT JOIN usuarios u ON t.usuario_id = u.id WHERE $where ORDER BY t.fecha_registro DESC";

        $stmt = $this->conexion->prepare($consulta);
        if (!$stmt) {
            return [];
        }

        // bind dinámico
        $types = str_repeat('s', count($fields));
        $params = array_fill(0, count($fields), $like);

        // bind_param requires references
        $bind_names[] = $types;
        for ($i=0; $i<count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }

        call_user_func_array(array($stmt, 'bind_param'), $bind_names);
        $stmt->execute();
        $result = $stmt->get_result();
        $tokens = [];
        while ($row = $result->fetch_assoc()) {
            $tokens[] = $row;
        }
        return $tokens;
    }

    // Verificar si una columna existe en la tabla token_api
    private function columnExists($column) {
        $col = $this->conexion->real_escape_string($column);
        $consulta = "SHOW COLUMNS FROM token_api LIKE '" . $col . "'";
        $res = $this->conexion->query($consulta);
        return ($res && $res->num_rows > 0);
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

    // Obtener token por valor
    public function getByToken($token) {
        $consulta = "SELECT * FROM token_api WHERE token = ? LIMIT 1";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Generar token aleatorio seguro
    public static function generateToken($length = 64) {
        try {
            return bin2hex(random_bytes(intval($length/2)));
        } catch (Exception $e) {
            // fallback
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $token = '';
            for ($i = 0; $i < $length; $i++) {
                $token .= $chars[random_int(0, strlen($chars)-1)];
            }
            return $token;
        }
    }
}
?>