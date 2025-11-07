<?php
// Verificar si la sesión ya está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de la base de datos
define('BD_HOST', 'localhost');
define('BD_NAME', 'token');
define('BD_USER', 'root');
define('BD_PASSWORD', 'root');
define('BD_CHARSET', 'utf8');

// Configuración de la aplicación
define('BASE_URL', 'http://localhost:8888/token/');
define('APP_NAME', 'Sistema de Gestión de Tokens API');
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos

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
        if (!self::isAuthenticated()) {
            http_response_code(401);
            echo json_encode(array("success" => false, "message" => "No autenticado"));
            exit();
        }
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