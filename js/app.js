const API_URL = 'api/';

document.addEventListener('DOMContentLoaded', function() {
    cargarDashboard();
    cargarInfoUsuario();
});

function cargarInfoUsuario() {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (user.nombre) {
        document.getElementById('userName').textContent = user.nombre;
    }
}

async function cargarDashboard() {
    const content = `
        <div class="row">
            <div class="col-12">
                <h2>Dashboard</h2>
                <p>Bienvenido al sistema de gestión de tokens API.</p>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Tokens Activos</h5>
                        <h2 id="tokensActivos">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Tokens</h5>
                        <h2 id="totalTokens">0</h2>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.getElementById('content').innerHTML = content;
    
    // Cargar estadísticas
    await cargarEstadisticas();
}

async function cargarVista(vista) {
    switch(vista) {
        case 'tokens':
            await cargarGestionTokens();
            break;
        case 'profile':
            cargarPerfil();
            break;
        default:
            cargarDashboard();
    }
}

async function cargarGestionTokens() {
    try {
        const response = await fetch(API_URL + 'tokens.php?action=getAll');
        const result = await response.json();
        
        let tokensHTML = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Gestión de Tokens</h2>
                <button class="btn btn-primary" onclick="mostrarModalNuevoToken()">
                    <i class="fas fa-plus me-2"></i>Nuevo Token
                </button>
            </div>
        `;
        
        if (result.success && result.data.length > 0) {
            tokensHTML += '<div class="row" id="tokens-container">';
            
            result.data.forEach(token => {
                tokensHTML += `
                    <div class="col-md-6 mb-3">
                        <div class="card token-card ${token.estado == 1 ? 'border-success' : 'border-danger'}">
                            <div class="card-body">
                                <h5 class="card-title">Token ID: ${token.id}</h5>
                                <p class="card-text">
                                    <strong>Cliente API:</strong> ${token.id_client_api}<br>
                                    <strong>Token:</strong> 
                                    <span class="token-preview">${token.token.substring(0, 50)}...</span><br>
                                    <strong>Registro:</strong> ${new Date(token.fecha_registro).toLocaleString()}<br>
                                    <strong>Estado:</strong> 
                                    <span class="badge ${token.estado == 1 ? 'bg-success' : 'bg-danger'}">
                                        ${token.estado == 1 ? 'Activo' : 'Inactivo'}
                                    </span>
                                </p>
                                <button class="btn btn-warning btn-sm me-2" onclick="openEditModal(${token.id})">
                                    <i class="fas fa-edit me-1"></i>Editar
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="eliminarToken(${token.id})">
                                    <i class="fas fa-trash me-1"></i>Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            tokensHTML += '</div>';
        } else {
            tokensHTML += '<div class="alert alert-info">No hay tokens registrados.</div>';
        }
        
        document.getElementById('content').innerHTML = tokensHTML;
    } catch (error) {
        console.error('Error cargando tokens:', error);
        document.getElementById('content').innerHTML = '<div class="alert alert-danger">Error al cargar los tokens</div>';
    }
}

async function openEditModal(id) {
    try {
        const response = await fetch(API_URL + 'tokens.php?action=get&id=' + id);
        const result = await response.json();
        
        if (result.success) {
            const token = result.data;
            document.getElementById('edit_id').value = token.id;
            document.getElementById('edit_id_client_api').value = token.id_client_api;
            document.getElementById('edit_token').value = token.token;
            document.getElementById('edit_estado').value = token.estado;

            const modal = new bootstrap.Modal(document.getElementById('editTokenModal'));
            modal.show();
        }
    } catch (error) {
        console.error('Error cargando token:', error);
        alert('Error al cargar el token');
    }
}

async function updateToken() {
    const tokenData = {
        id: document.getElementById('edit_id').value,
        id_client_api: document.getElementById('edit_id_client_api').value,
        token: document.getElementById('edit_token').value,
        estado: document.getElementById('edit_estado').value
    };

    try {
        const response = await fetch(API_URL + 'tokens.php?action=update', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(tokenData)
        });

        const result = await response.json();
        alert(result.message);

        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('editTokenModal'));
            modal.hide();
            cargarGestionTokens();
        }
    } catch (error) {
        console.error('Error actualizando token:', error);
        alert('Error al actualizar el token');
    }
}

async function logout() {
    try {
        const response = await fetch(API_URL + 'auth.php?action=logout');
        const result = await response.json();
        
        if (result.success) {
            localStorage.removeItem('user');
            window.location.href = 'login.php';
        }
    } catch (error) {
        console.error('Error en logout:', error);
        window.location.href = 'login.php';
    }
}

async function cargarEstadisticas() {
    try {
        const response = await fetch(API_URL + 'tokens.php?action=getAll');
        const result = await response.json();
        
        if (result.success) {
            const tokens = result.data;
            const tokensActivos = tokens.filter(token => token.estado == 1).length;
            const totalTokens = tokens.length;
            
            document.getElementById('tokensActivos').textContent = tokensActivos;
            document.getElementById('totalTokens').textContent = totalTokens;
        }
    } catch (error) {
        console.error('Error cargando estadísticas:', error);
    }
}

function cargarPerfil() {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    const content = `
        <div class="row">
            <div class="col-12">
                <h2>Perfil de Usuario</h2>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Información del Usuario</h5>
                        <p><strong>Nombre:</strong> ${user.nombre || 'N/A'}</p>
                        <p><strong>Usuario:</strong> ${user.username || 'N/A'}</p>
                        <p><strong>Rol:</strong> ${user.rol || 'N/A'}</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.getElementById('content').innerHTML = content;
}