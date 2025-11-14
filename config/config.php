<?php
// Verificar si la sesión ya está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de la base de datos
define('BD_HOST', 'localhost');
define('BD_NAME', 'cwefycom_empresa_consumer');
define('BD_USER', 'cwefycom_empresa_consumer_user');
define('BD_PASSWORD', '123empresa123@');
define('BD_CHARSET', 'utf8');

// Configuración de la aplicación
define('BASE_URL', 'https://clienteempresas.cwefy.com/');
define('APP_NAME', 'Sistema de Gestión de Tokens API');
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos

// Configuración de API Externa
// Ajusta el puerto según tu configuración de XAMPP (80 o 8888)
define('API_EMPRESAS_URL', 'https://empresas.cwefy.com/empresas.php');

class Config {
    public static function init() {
        // Solo configurar headers si es una petición de API
        if (strpos($_SERVER['REQUEST_URI'], 'controllers/') !== false || 
            strpos($_SERVER['PHP_SELF'], 'controllers/') !== false) {
            
            // Limpiar cualquier salida previa
            if (ob_get_level()) {
                ob_clean();
            }
            
            // Headers para API
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json; charset=UTF-8");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
            header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        }
        
        // Verificar timeout de sesión
        self::checkSessionTimeout();
    }
    
    private static function checkSessionTimeout() {
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
            session_unset();
            session_destroy();
        }
        $_SESSION['LAST_ACTIVITY'] = time();
    }
    
    public static function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    public static function requireAuth() {
        if (self::isAuthenticated()) {
            return true;
        }

        // Intentar autenticación por token (Authorization: Bearer <token> o ?token=...)
        $token = null;
        // Cabecera Authorization
        $headers = null;
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        }
        if ($headers && isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                $token = trim($matches[1]);
            }
        } elseif ($headers && isset($headers['authorization'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers['authorization'], $matches)) {
                $token = trim($matches[1]);
            }
        }

        // Parámetro URL
        if (empty($token) && isset($_GET['token'])) {
            $token = $_GET['token'];
        }
        if (empty($token) && isset($_GET['access_token'])) {
            $token = $_GET['access_token'];
        }

        if (!empty($token)) {
            // Validar token en la BD
            require_once __DIR__ . "/../models/TokenModel.php";
            try {
                $tm = new TokenModel();
                $tokenRow = $tm->getByToken($token);
                if ($tokenRow && intval($tokenRow['estado']) === 1) {
                    // Autenticar como el usuario propietario del token para esta petición
                    $_SESSION['user_id'] = $tokenRow['usuario_id'];
                    // opcional: cargar info de usuario mínima
                    $_SESSION['auth_via_token'] = true;
                    return true;
                }
            } catch (Exception $e) {
                // no hacer crash, seguir al 401
            }
        }

        http_response_code(401);
        echo json_encode(array("success" => false, "message" => "No autenticado"));
        exit();
    }
    
    public static function requireAdmin() {
        self::requireAuth();
        if ($_SESSION['rol'] !== 'admin') {
            http_response_code(403);
            echo json_encode(array("success" => false, "message" => "Acceso denegado. Se requiere rol de administrador."));
            exit();
        }
    }
}
?>