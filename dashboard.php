<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config/config.php";

if (!Config::isAuthenticated()) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Tokens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .token-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .token-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .token-preview {
            font-family: monospace;
            font-size: 0.9em;
            background: #f8f9fa;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4">
                        <i class="fas fa-key fa-2x mb-2"></i>
                        <h5>Sistema de Tokens</h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" id="nav-dashboard" href="#" onclick="cargarDashboard(); return false;">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="nav-tokens" href="#" onclick="cargarGestionTokens(); return false;">
                                <i class="fas fa-key me-2"></i>
                                Gestión de Tokens
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="nav-empresas" href="#" onclick="cargarEmpresas(); return false;">
                                <i class="fas fa-building me-2"></i>
                                Empresas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="nav-perfil" href="#" onclick="cargarPerfil(); return false;">
                                <i class="fas fa-user me-2"></i>
                                Mi Perfil
                            </a>
                        </li>
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="cargarUsuarios(); return false;">
                                <i class="fas fa-users me-2"></i>
                                Usuarios
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="#" onclick="logout(); return false;">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                    <div class="container-fluid">
                        <span class="navbar-brand">Bienvenido, <span id="userName"><?php echo $_SESSION['nombre']; ?></span></span>
                        <div class="navbar-nav ms-auto">
                            <div class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle me-1"></i>
                                    <?php echo $_SESSION['username']; ?>
                                    <span class="badge bg-<?php echo $_SESSION['rol'] === 'admin' ? 'danger' : 'primary'; ?> ms-1">
                                        <?php echo ucfirst($_SESSION['rol']); ?>
                                    </span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="cargarPerfil(); return false;">
                                        <i class="fas fa-user me-2"></i>Mi Perfil
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="logout(); return false;">
                                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Content -->
                <div id="content" class="py-4"></div>
            </main>
        </div>
    </div>

    <!-- Modal Nuevo Token -->
    <div class="modal fade" id="nuevoTokenModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Token</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoToken">
                        <div class="mb-3">
                            <label for="nuevo_id_client_api" class="form-label">ID Cliente API</label>
                            <input type="number" class="form-control" id="nuevo_id_client_api" required>
                        </div>
                        <div class="mb-3">
                            <label for="nuevo_token" class="form-label">Token</label>
                            <textarea class="form-control" id="nuevo_token" rows="3" required></textarea>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="generarToken()">
                                <i class="fas fa-random me-1"></i>Generar Token Aleatorio
                            </button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="crearToken()">Crear Token</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Token -->
    <div class="modal fade" id="editarTokenModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Token</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarToken">
                        <input type="hidden" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_id_client_api" class="form-label">ID Cliente API</label>
                            <input type="number" class="form-control" id="edit_id_client_api" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_token" class="form-label">Token</label>
                            <textarea class="form-control" id="edit_token" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_estado" class="form-label">Estado</label>
                            <select class="form-control" id="edit_estado" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="actualizarToken()">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para establecer el enlace activo en el sidebar
        function setActiveNav(navId) {
            // Remover active de todos los enlaces
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.classList.remove('active');
            });
            // Agregar active al enlace seleccionado
            const activeLink = document.getElementById(navId);
            if (activeLink) {
                activeLink.classList.add('active');
            }
        }

        // Cargar dashboard al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            setActiveNav('nav-dashboard');
            cargarDashboard();
        });

        // Reutilizable: mostrar alertas dentro del contenido principal
        function showAlert(message, type = 'info', timeout = 5000) {
            const container = document.getElementById('content');
            const id = 'alert-' + Date.now();
            const html = `
                <div id="${id}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            // Insertar al inicio del contenido
            container.insertAdjacentHTML('afterbegin', html);
            setTimeout(() => {
                const el = document.getElementById(id);
                if (el) {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
                    bsAlert.close();
                }
            }, timeout);
        }

        async function cargarDashboard() {
            setActiveNav('nav-dashboard');
            try {
                const response = await fetch('controllers/TokenController.php?action=stats', { credentials: 'same-origin' });
                const result = await response.json();
                
                let stats = { total: 0, activos: 0, inactivos: 0 };
                if (result.success) {
                    stats = result.data;
                }

                document.getElementById('content').innerHTML = `
                    <div class="row">
                        <div class="col-12">
                            <h2>Dashboard</h2>
                            <p class="text-muted">Bienvenido al sistema de gestión de tokens API</p>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="stats-card text-center">
                                <i class="fas fa-key fa-2x text-primary mb-3"></i>
                                <h3>${stats.total}</h3>
                                <p class="text-muted">Total Tokens</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card text-center">
                                <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                                <h3>${stats.activos}</h3>
                                <p class="text-muted">Tokens Activos</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card text-center">
                                <i class="fas fa-times-circle fa-2x text-danger mb-3"></i>
                                <h3>${stats.inactivos}</h3>
                                <p class="text-muted">Tokens Inactivos</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card text-center">
                                <i class="fas fa-user fa-2x text-info mb-3"></i>
                                <h3><?php echo $_SESSION['nombre']; ?></h3>
                                <p class="text-muted">Usuario Actual</p>
                            </div>
                        </div>
                    </div>
                `;
            } catch (error) {
                console.error('Error cargando dashboard:', error);
            }
        }

        async function cargarGestionTokens() {
            setActiveNav('nav-tokens');
            try {
                const response = await fetch('controllers/TokenController.php?action=getAll', { credentials: 'same-origin' });
                const result = await response.json();
                
                let html = `
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Gestión de Tokens</h2>
                        <button class="btn btn-primary" onclick="mostrarModalNuevoToken()">
                            <i class="fas fa-plus me-2"></i>Nuevo Token
                        </button>
                    </div>
                `;
                
                if (result.success && result.data.length > 0) {
                    html += '<div class="row">';
                    result.data.forEach(token => {
                        html += `
                            <div class="col-md-6">
                                <div class="card token-card border-${token.estado == 1 ? 'success' : 'danger'}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h5 class="card-title">Token #${token.id}</h5>
                                            <span class="badge bg-${token.estado == 1 ? 'success' : 'danger'}">
                                                ${token.estado == 1 ? 'Activo' : 'Inactivo'}
                                            </span>
                                        </div>
                                        <p class="card-text">
                                            <strong>Cliente API ID:</strong> ${token.id_client_api}<br>
                                            <strong>Token:</strong><br>
                                            <span class="token-preview">${token.token.substring(0, 60)}...</span><br>
                                            <strong>Creado:</strong> ${new Date(token.fecha_registro).toLocaleString('es-PE')}<br>
                                            <strong>Usuario:</strong> ${token.usuario_nombre || 'N/A'}
                                        </p>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-warning" onclick="editarToken(${token.id})">
                                                <i class="fas fa-edit me-1"></i>Editar
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="confirmarEliminar(${token.id})">
                                                <i class="fas fa-trash me-1"></i>Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                } else {
                    html += '<div class="alert alert-info">No hay tokens registrados. Crea tu primer token.</div>';
                }
                
                document.getElementById('content').innerHTML = html;
            } catch (error) {
                console.error('Error cargando tokens:', error);
                document.getElementById('content').innerHTML = '<div class="alert alert-danger">Error al cargar tokens</div>';
            }
        }

        function mostrarModalNuevoToken() {
            const modal = new bootstrap.Modal(document.getElementById('nuevoTokenModal'));
            document.getElementById('formNuevoToken').reset();
            modal.show();
        }

        function generarToken() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let token = '';
            for (let i = 0; i < 64; i++) {
                token += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('nuevo_token').value = token;
        }

        async function crearToken() {
            const id_client_api = document.getElementById('nuevo_id_client_api').value;
            const token = document.getElementById('nuevo_token').value;

            if (!id_client_api || !token) {
                alert('Por favor completa todos los campos');
                return;
            }

            try {
                const response = await fetch('controllers/TokenController.php?action=create', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_client_api, token })
                });

                const result = await response.json();
                alert(result.message);

                if (result.success) {
                    const modalElement = document.getElementById('nuevoTokenModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalElement);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    cargarGestionTokens();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al crear el token');
            }
        }

        async function editarToken(id) {
            try {
                const response = await fetch(`controllers/TokenController.php?action=get&id=${id}`, { credentials: 'same-origin' });
                const result = await response.json();

                if (result.success) {
                    document.getElementById('edit_id').value = result.data.id;
                    document.getElementById('edit_id_client_api').value = result.data.id_client_api;
                    document.getElementById('edit_token').value = result.data.token;
                    document.getElementById('edit_estado').value = result.data.estado;

                    const modal = new bootstrap.Modal(document.getElementById('editarTokenModal'));
                    modal.show();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cargar el token');
            }
        }

        async function actualizarToken() {
            const tokenData = {
                id: document.getElementById('edit_id').value,
                id_client_api: document.getElementById('edit_id_client_api').value,
                token: document.getElementById('edit_token').value,
                estado: document.getElementById('edit_estado').value
            };

            try {
                const response = await fetch('controllers/TokenController.php?action=update', {
                    method: 'PUT',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(tokenData)
                });

                const result = await response.json();
                alert(result.message);

                if (result.success) {
                    const modalElement = document.getElementById('editarTokenModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalElement);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    cargarGestionTokens();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al actualizar el token');
            }
        }

        function confirmarEliminar(id) {
            if (confirm('¿Estás seguro de eliminar este token? Esta acción no se puede deshacer.')) {
                eliminarToken(id);
            }
        }

        async function eliminarToken(id) {
            try {
                const response = await fetch(`controllers/TokenController.php?action=delete&id=${id}`, {
                    method: 'GET',
                    credentials: 'same-origin'
                });

                const result = await response.json();
                alert(result.message);

                if (result.success) {
                    cargarGestionTokens();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al eliminar el token');
            }
        }

        function cargarPerfil() {
            setActiveNav('nav-perfil');
            document.getElementById('content').innerHTML = `
                <div class="row">
                    <div class="col-12"><h2>Mi Perfil</h2></div>
                </div>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Información del Usuario</h5>
                                <p><strong>Nombre:</strong> <?php echo $_SESSION['nombre']; ?></p>
                                <p><strong>Usuario:</strong> <?php echo $_SESSION['username']; ?></p>
                                <p><strong>Rol:</strong> <?php echo ucfirst($_SESSION['rol']); ?></p>
                                <p><strong>Último acceso:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function cargarUsuarios() {
            document.getElementById('content').innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Gestión de Usuarios</h2>
                    <button class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nuevo Usuario
                    </button>
                </div>
                <div class="alert alert-info">
                    Esta funcionalidad estará disponible pronto. Solo para administradores.
                </div>
            `;
        }

        async function cargarEmpresas() {
            setActiveNav('nav-empresas');
            try {
                // Mostrar loading
                document.getElementById('content').innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-3">Cargando empresas...</p>
                    </div>
                `;

                const response = await fetch('controllers/EmpresaController.php?action=getAll', { credentials: 'same-origin' });
                const result = await response.json();
                
                let html = `
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-building me-2"></i>Empresas</h2>
                        <div class="input-group" style="max-width: 400px;">
                            <input type="text" class="form-control" id="buscarEmpresa" placeholder="Buscar por nombre, RUC, email...">
                            <button class="btn btn-outline-secondary" type="button" onclick="buscarEmpresas()">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-primary" type="button" onclick="cargarEmpresas()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                if (result.success && result.data && result.data.length > 0) {
                    html += `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Total de empresas: <strong>${result.total}</strong>
                        </div>
                        <div class="row">
                    `;
                    
                    result.data.forEach(empresa => {
                        html += `
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card token-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h5 class="card-title">
                                                <i class="fas fa-building text-primary me-2"></i>
                                                ${empresa.nombre || 'Sin nombre'}
                                            </h5>
                                        </div>
                                        <p class="card-text">
                                            <strong><i class="fas fa-id-card me-2 text-muted"></i>RUC:</strong> 
                                            ${empresa.ruc || 'N/A'}<br>
                                            <strong><i class="fas fa-map-marker-alt me-2 text-muted"></i>Dirección:</strong> 
                                            ${empresa.direccion || 'N/A'}<br>
                                            <strong><i class="fas fa-phone me-2 text-muted"></i>Teléfono:</strong> 
                                            ${empresa.telefono || 'N/A'}<br>
                                            <strong><i class="fas fa-envelope me-2 text-muted"></i>Email:</strong> 
                                            ${empresa.email || 'N/A'}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                } else {
                    html += `
                        <div class="alert alert-${result.success ? 'info' : 'warning'}">
                            <i class="fas fa-${result.success ? 'info-circle' : 'exclamation-triangle'} me-2"></i>
                            ${result.message || 'No hay empresas registradas.'}
                        </div>
                    `;
                }
                
                document.getElementById('content').innerHTML = html;

                // Agregar evento Enter en el campo de búsqueda
                const buscarInput = document.getElementById('buscarEmpresa');
                if (buscarInput) {
                    buscarInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            buscarEmpresas();
                        }
                    });
                }
            } catch (error) {
                console.error('Error cargando empresas:', error);
                document.getElementById('content').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error al cargar empresas. Por favor, intenta nuevamente.
                    </div>
                `;
            }
        }

        async function buscarEmpresas() {
            const valor = document.getElementById('buscarEmpresa').value.trim();
            
            if (!valor) {
                cargarEmpresas();
                return;
            }

            try {
                // Mostrar loading
                document.getElementById('content').innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Buscando...</span>
                        </div>
                        <p class="mt-3">Buscando empresas...</p>
                    </div>
                `;

                const response = await fetch(`controllers/EmpresaController.php?action=search&campo=nombre&valor=${encodeURIComponent(valor)}`, { 
                    credentials: 'same-origin' 
                });
                const result = await response.json();
                
                let html = `
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-building me-2"></i>Empresas</h2>
                        <div class="input-group" style="max-width: 400px;">
                            <input type="text" class="form-control" id="buscarEmpresa" value="${valor}" placeholder="Buscar por nombre, RUC, email...">
                            <button class="btn btn-outline-secondary" type="button" onclick="buscarEmpresas()">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-outline-primary" type="button" onclick="cargarEmpresas()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                if (result.success && result.data && result.data.length > 0) {
                    html += `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Se encontraron <strong>${result.total}</strong> empresa(s) con el término "${valor}"
                        </div>
                        <div class="row">
                    `;
                    
                    result.data.forEach(empresa => {
                        html += `
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card token-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h5 class="card-title">
                                                <i class="fas fa-building text-primary me-2"></i>
                                                ${empresa.nombre || 'Sin nombre'}
                                            </h5>
                                        </div>
                                        <p class="card-text">
                                            <strong><i class="fas fa-id-card me-2 text-muted"></i>RUC:</strong> 
                                            ${empresa.ruc || 'N/A'}<br>
                                            <strong><i class="fas fa-map-marker-alt me-2 text-muted"></i>Dirección:</strong> 
                                            ${empresa.direccion || 'N/A'}<br>
                                            <strong><i class="fas fa-phone me-2 text-muted"></i>Teléfono:</strong> 
                                            ${empresa.telefono || 'N/A'}<br>
                                            <strong><i class="fas fa-envelope me-2 text-muted"></i>Email:</strong> 
                                            ${empresa.email || 'N/A'}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                } else {
                    html += `
                        <div class="alert alert-warning">
                            <i class="fas fa-search me-2"></i>
                            No se encontraron empresas con el término "${valor}"
                        </div>
                    `;
                }
                
                document.getElementById('content').innerHTML = html;

                // Agregar evento Enter en el campo de búsqueda
                const buscarInput = document.getElementById('buscarEmpresa');
                if (buscarInput) {
                    buscarInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            buscarEmpresas();
                        }
                    });
                }
            } catch (error) {
                console.error('Error buscando empresas:', error);
                document.getElementById('content').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error al buscar empresas. Por favor, intenta nuevamente.
                    </div>
                `;
            }
        }

        async function logout() {
            try {
                const response = await fetch('controllers/AuthController.php?action=logout', { credentials: 'same-origin' });
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = 'index.php';
                }
            } catch (error) {
                console.error('Error en logout:', error);
                window.location.href = 'index.php';
            }
        }

        // Limpiar modales al cerrar
        document.addEventListener('hidden.bs.modal', function (event) {
            const modal = event.target;
            const form = modal.querySelector('form');
            if (form) {
                form.reset();
            }
        });

        // Prevenir errores de backdrop
        document.addEventListener('shown.bs.modal', function (event) {
            document.body.classList.add('modal-open');
        });
    </script>
</body>
</html>