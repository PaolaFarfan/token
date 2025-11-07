const API_URL = 'controllers/';

document.addEventListener('DOMContentLoaded', function() {
    // Verificar si ya está autenticado
    checkAuth();
    
    // Manejar login
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        login();
    });
});

async function checkAuth() {
    try {
    const response = await fetch(API_URL + 'AuthController.php?action=check');
        const result = await response.json();
        
        if (result.authenticated) {
            window.location.href = 'dashboard.php';
        }
    } catch (error) {
        console.error('Error verificando autenticación:', error);
    }
}

async function login() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    try {
    const response = await fetch(API_URL + 'AuthController.php?action=login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username, password })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Login exitoso! Redirigiendo...', 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 1000);
        } else {
            showAlert(result.message, 'danger');
        }
    } catch (error) {
        console.error('Error en login:', error);
        showAlert('Error de conexión', 'danger');
    }
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    alertContainer.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}