<?php
// Limpiar cualquier salida previa
if (ob_get_level()) {
    ob_clean();
}

// Iniciar buffer de salida
ob_start();

require_once "../config/config.php";
require_once "../models/UsuarioModel.php";

// Inicializar configuración (se puede llamar antes del routing)
Config::init();

class AuthController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    public function login() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents("php://input"), true);

                if (!empty($data['username']) && !empty($data['password'])) {
                    $user = $this->usuarioModel->login($data['username'], $data['password']);

                    if ($user) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['nombre'] = $user['nombre'];
                        $_SESSION['rol'] = $user['rol'];

                        // Actualizar último login
                        $this->usuarioModel->actualizarUltimoLogin($user['id']);

                        $response = [
                            "success" => true,
                            "message" => "Login exitoso",
                            "user" => [
                                "id" => $user['id'],
                                "username" => $user['username'],
                                "nombre" => $user['nombre'],
                                "rol" => $user['rol']
                            ]
                        ];
                    } else {
                        $response = [
                            "success" => false,
                            "message" => "Credenciales incorrectas"
                        ];
                    }
                } else {
                    $response = [
                        "success" => false,
                        "message" => "Datos incompletos"
                    ];
                }
            } else {
                $response = [
                    "success" => false,
                    "message" => "Método no permitido"
                ];
            }

            header('Content-Type: application/json');
            echo json_encode($response);
            exit();

        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                "success" => false,
                "message" => "Error en el servidor: " . $e->getMessage()
            ]);
            exit();
        }
    }

    public function logout() {
        session_destroy();
        header('Content-Type: application/json');
        echo json_encode([
            "success" => true,
            "message" => "Logout exitoso"
        ]);
        exit();
    }

    public function checkAuth() {
        if (Config::isAuthenticated()) {
            $user = $this->usuarioModel->getUsuario($_SESSION['user_id']);
            $response = [
                "authenticated" => true,
                "user" => $user
            ];
        } else {
            $response = [
                "authenticated" => false
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}

// ------------ Routing: instanciar controlador y ejecutar acción ------------
$action = $_GET['action'] ?? '';

$authController = new AuthController();

switch($action) {
    case 'login':
        $authController->login();
        break;
    case 'logout':
        $authController->logout();
        break;
    case 'check':
        $authController->checkAuth();
        break;
    default:
        echo json_encode(["success" => false, "message" => "Acción no válida"]);
        break;
}

// Limpiar el buffer y enviar solo la respuesta JSON
ob_end_clean();
?>