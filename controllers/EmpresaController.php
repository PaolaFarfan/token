<?php
require_once "../config/config.php";
require_once "../models/TokenModel.php";

// Inicializar configuración
Config::init();

class EmpresaController {
    private $tokenModel;

    public function __construct() {
        $this->tokenModel = new TokenModel();
    }

    /**
     * Obtener el token con ID 1 de la base de datos del sistema token
     */
    private function obtenerTokenId1() {
        try {
            $token = $this->tokenModel->getToken(1);
            
            if ($token && isset($token['token'])) {
                return $token['token'];
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error obteniendo token ID 1: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Consumir el endpoint de empresas del sistema api_empresas
     */
    public function getEmpresas() {
        Config::requireAuth();

        try {
            // Obtener el token con ID 1 de la base de datos del sistema token
            $token = $this->obtenerTokenId1();
            
            if (!$token) {
                echo json_encode([
                    "success" => false,
                    "message" => "No se encontró el token con ID 1 en la base de datos del sistema token. Por favor, asegúrate de que existe un token con ID 1."
                ]);
                return;
            }

            // Construir URL del endpoint
            $url = API_EMPRESAS_URL . "?token=" . urlencode($token) . "&accion=listar";
            
            // Realizar petición cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                echo json_encode([
                    "success" => false,
                    "message" => "Error de conexión: " . $curlError
                ]);
                return;
            }

            if ($httpCode !== 200) {
                $errorData = json_decode($response, true);
                echo json_encode([
                    "success" => false,
                    "message" => $errorData['message'] ?? "Error al obtener empresas (Código: $httpCode)"
                ]);
                return;
            }

            $data = json_decode($response, true);
            
            if ($data && isset($data['success']) && $data['success']) {
                echo json_encode([
                    "success" => true,
                    "data" => $data['data'] ?? [],
                    "total" => $data['total'] ?? 0
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => $data['message'] ?? "Error al obtener empresas"
                ]);
            }

        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Buscar empresas por campo y valor
     */
    public function buscarEmpresas() {
        Config::requireAuth();

        try {
            $campo = $_GET['campo'] ?? 'nombre';
            $valor = $_GET['valor'] ?? '';

            if (empty($valor)) {
                echo json_encode([
                    "success" => false,
                    "message" => "Valor de búsqueda requerido"
                ]);
                return;
            }

            // Obtener el token con ID 1 de la base de datos del sistema token
            $token = $this->obtenerTokenId1();
            
            if (!$token) {
                echo json_encode([
                    "success" => false,
                    "message" => "No se encontró el token con ID 1 en la base de datos del sistema token. Por favor, asegúrate de que existe un token con ID 1."
                ]);
                return;
            }

            // Construir URL del endpoint
            $url = API_EMPRESAS_URL . "?token=" . urlencode($token) . "&accion=buscar&campo=" . urlencode($campo) . "&valor=" . urlencode($valor);
            
            // Realizar petición cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                echo json_encode([
                    "success" => false,
                    "message" => "Error de conexión: " . $curlError
                ]);
                return;
            }

            if ($httpCode !== 200) {
                $errorData = json_decode($response, true);
                echo json_encode([
                    "success" => false,
                    "message" => $errorData['message'] ?? "Error al buscar empresas"
                ]);
                return;
            }

            $data = json_decode($response, true);
            
            if ($data && isset($data['success']) && $data['success']) {
                echo json_encode([
                    "success" => true,
                    "data" => $data['data'] ?? [],
                    "total" => $data['total'] ?? 0
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => $data['message'] ?? "Error al buscar empresas"
                ]);
            }

        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
}

// ------------ Routing: instanciar controlador y ejecutar acción ------------
$action = $_GET['action'] ?? '';
$empresaController = new EmpresaController();

switch($action) {
    case 'getAll':
        $empresaController->getEmpresas();
        break;
    case 'search':
        $empresaController->buscarEmpresas();
        break;
    default:
        echo json_encode(["success" => false, "message" => "Acción no válida"]);
        break;
}
?>

