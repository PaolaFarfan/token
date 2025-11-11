<?php
// Solo iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config/config.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestión de Tokens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            background: white;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .credentiales {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <i class="fas fa-key"></i>
                <h2>Sistema de Tokens API</h2>
                <p class="text-muted">Gestión segura de tokens de acceso</p>
            </div>
            
            <form id="loginForm">
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user me-2"></i>Usuario
                    </label>
                    <input type="text" class="form-control" id="username" placeholder="Ingresa tu usuario" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Contraseña
                    </label>
                    <input type="password" class="form-control" id="password" placeholder="Ingresa tu contraseña" required>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                    </button>
                </div>
            </form>
            
            <div id="alertContainer" class="mt-3"></div>
            
            <div class="credentiales">
                <h6><i class="fas fa-info-circle me-2"></i>Credenciales de prueba:</h6>
                <div class="row">
                    <div class="col-6">
                        <strong>Administrador:</strong><br>
                        Usuario: <code>admin</code><br>
                        Contraseña: <code>admin123</code>
                    </div>
                    <div class="col-6">
                        <strong>Usuario normal:</strong><br>
                        Usuario: <code>usuario1</code><br>
                        Contraseña: <code>admin123</code>
                    </div>
                </div>
                <small class="text-muted">(Las credenciales pueden cambiarse en la base de datos. Estos valores son solo de ejemplo.)</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_URL = 'controllers/AuthController.php';

        // Verificar si ya está autenticado
        document.addEventListener('DOMContentLoaded', function() {
            checkAuth();
        });

        async function checkAuth() {
            try {
                const response = await fetch(API_URL + '?action=check');
                const result = await response.json();
                
                if (result.authenticated) {
                    window.location.href = 'dashboard.php';
                }
            } catch (error) {
                console.error('Error verificando autenticación:', error);
            }
        }

        // Manejar login
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            // Mostrar loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verificando...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch(API_URL + '?action=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ username, password })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('✅ ' + result.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1000);
                } else {
                    showAlert('❌ ' + result.message, 'danger');
                }
            } catch (error) {
                console.error('Error en login:', error);
                showAlert('⚠️ Error de conexión con el servidor', 'danger');
            } finally {
                // Restaurar botón
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Auto-cerrar alerta después de 5 segundos
            setTimeout(() => {
                const alert = bootstrap.Alert.getOrCreateInstance(alertContainer.querySelector('.alert'));
                alert.close();
            }, 5000);
        }
    </script>
</body>
</html>