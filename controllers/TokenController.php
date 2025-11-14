<?php
require_once "../config/config.php";
require_once "../models/TokenModel.php";

// Inicializar configuración
Config::init();

class TokenController {
    private $tokenModel;

    public function __construct() {
        $this->tokenModel = new TokenModel();
    }

    public function getTokens() {
        Config::requireAuth();

        $tokens = $this->tokenModel->getTokens();
        echo json_encode([
            "success" => true,
            "data" => $tokens
        ]);
    }

    public function getToken($id) {
        Config::requireAuth();

        $token = $this->tokenModel->getToken($id);
        if ($token) {
            echo json_encode([
                "success" => true,
                "data" => $token
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Token no encontrado"
            ]);
        }
    }

    public function updateToken() {
        Config::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!empty($data['id']) && !empty($data['id_client_api']) && 
                !empty($data['token']) && isset($data['estado'])) {

                $success = $this->tokenModel->actualizarToken(
                    $data['id'],
                    $data['id_client_api'],
                    $data['token'],
                    $data['estado']
                );

                if ($success) {
                    echo json_encode([
                        "success" => true,
                        "message" => "Token actualizado correctamente"
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "message" => "Error al actualizar token"
                    ]);
                }
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Datos incompletos"
                ]);
            }
        }
    }

    public function createToken() {
        Config::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!empty($data['id_client_api'])) {
                // Si no se envía token, generar uno seguro
                if (empty($data['token'])) {
                    $data['token'] = TokenModel::generateToken(64);
                }

                // Verificar si el token ya existe
                if ($this->tokenModel->tokenExiste($data['token'])) {
                    echo json_encode([
                        "success" => false,
                        "message" => "El token ya existe"
                    ]);
                    return;
                }

                // atributos opcionales
                $nombre = $data['nombre'] ?? null;
                $descripcion = $data['descripcion'] ?? null;
                $expires_at = $data['expires_at'] ?? null; // formato YYYY-MM-DD HH:MM:SS o null

                $auth_url = $data['auth_url'] ?? null;

                $tokenId = $this->tokenModel->crearTokenConMeta(
                    $data['id_client_api'],
                    $data['token'],
                    $_SESSION['user_id'],
                    $nombre,
                    $descripcion,
                    $auth_url,
                    $expires_at
                );

                if ($tokenId) {
                    // Construir URL de autenticación con token
                    $authUrl = BASE_URL . "?token=" . urlencode($data['token']);
                    echo json_encode([
                        "success" => true,
                        "message" => "Token creado correctamente",
                        "id" => $tokenId,
                        "token" => $data['token'],
                        "auth_url" => $authUrl
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "message" => "Error al crear token"
                    ]);
                }
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Datos incompletos: se requiere id_client_api"
                ]);
            }
        }
    }

    public function search() {
        Config::requireAuth();
        $q = $_GET['q'] ?? '';
        if (empty($q)) {
            echo json_encode(["success" => false, "message" => "Parámetro q vacío"]);
            return;
        }

        $results = $this->tokenModel->searchTokens($q);
        echo json_encode(["success" => true, "data" => $results]);
    }

    public function deleteToken($id) {
        Config::requireAuth();

        if (!empty($id)) {
            $success = $this->tokenModel->eliminarToken($id);

            if ($success) {
                echo json_encode([
                    "success" => true,
                    "message" => "Token eliminado correctamente"
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Error al eliminar token"
                ]);
            }
        } else {
            echo json_encode([
                "success" => false,
                "message" => "ID no válido"
            ]);
        }
    }

    public function getEstadisticas() {
        Config::requireAuth();

        $tokens = $this->tokenModel->getTokens();
        $activos = array_filter($tokens, function($token) {
            return $token['estado'] == 1;
        });

        echo json_encode([
            "success" => true,
            "data" => [
                "total" => count($tokens),
                "activos" => count($activos),
                "inactivos" => count($tokens) - count($activos)
            ]
        ]);
    }
}

// ------------ Routing: instanciar controlador y ejecutar acción ------------
$action = $_GET['action'] ?? '';
$tokenController = new TokenController();

switch($action) {
    case 'getAll':
        $tokenController->getTokens();
        break;
    case 'get':
        $id = $_GET['id'] ?? 0;
        $tokenController->getToken($id);
        break;
    case 'create':
        $tokenController->createToken();
        break;
    case 'update':
        $tokenController->updateToken();
        break;
    case 'delete':
        $id = $_GET['id'] ?? 0;
        $tokenController->deleteToken($id);
        break;
    case 'stats':
        $tokenController->getEstadisticas();
        break;
    case 'search':
        $tokenController->search();
        break;
    default:
        echo json_encode(["success" => false, "message" => "Acción no válida"]);
        break;
}

// (Sin buffering) Fin del enrutador
?>