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
        .api-config-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn-api {
            margin-right: 10px;
            margin-bottom: 10px;
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
                            <a class="nav-link active" href="#" onclick="cargarDashboard(); return false;">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="cargarGestionTokens(); return false;">
                                <i class="fas fa-key me-2"></i>
                                Gestión de Tokens
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="cargarConsultarAPI(); return false;">
                                <i class="fas fa-exchange-alt me-2"></i>
                                Consultar API
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="cargarPerfil(); return false;">
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
        // Variables globales para configuración de API
        let apiUrl = 'http://localhost:8888/api_empresas/';
        let apiToken = 'tok_e2356634bb700782b9e4588bb8b6e526';
        let consultaTipo = 'Listar todas las empresas';

        // Cargar dashboard al iniciar
        document.addEventListener('DOMContentLoaded', function() {
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
                                <i class="fas fa-exchange-alt fa-2x text-info mb-3"></i>
                                <h3>API Empresas</h3>
                                <p class="text-muted">Consultar API</p>
                            </div>
                        </div>
                    </div>
                `;
            } catch (error) {
                console.error('Error cargando dashboard:', error);
            }
        }

        async function cargarGestionTokens() {
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

        function cargarConsultarAPI() {
            document.getElementById('content').innerHTML = `
                <div class="row">
                   
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="api-config-card">
                            <h5 class="mb-3">Configuración de la API</h5>
                            <div class="mb-3">
                                <label for="apiUrl" class="form-label">URL de la API:</label>
                                <input type="text" class="form-control" id="apiUrl" value="${apiUrl}" placeholder="Asegúrate de que la URL termine con /">
                            </div>
                            <div class="mb-3">
                                <label for="apiToken" class="form-label">Token de autenticación:</label>
                                <input type="text" class="form-control" id="apiToken" value="${apiToken}">
                            </div>
                            <div class="mb-3">
                                <label for="consultaTipo" class="form-label">Tipo de consulta:</label>
                                <select class="form-control" id="consultaTipo">
                                    <option value="Listar todas las empresas" ${consultaTipo === 'Listar todas las empresas' ? 'selected' : ''}>Listar todas las empresas</option>
                                    <option value="Buscar por ID" ${consultaTipo === 'Buscar por ID' ? 'selected' : ''}>Buscar por ID</option>
                                    <option value="Buscar por RUC" ${consultaTipo === 'Buscar por RUC' ? 'selected' : ''}>Buscar por RUC</option>
                                </select>
                            </div>
                            <div class="d-flex flex-wrap">
                                <button class="btn btn-primary btn-api" onclick="consultarEmpresas()">
                                    <i class="fas fa-search me-2"></i>Consultar Empresas
                                </button>
                                <button class="btn btn-secondary btn-api" onclick="limpiarResultados()">
                                    <i class="fas fa-broom me-2"></i>Limpiar Resultados
                                </button>
                                <button class="btn btn-info btn-api" onclick="probarConexion()">
                                    <i class="fas fa-plug me-2"></i>Probar Conexión
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="api-config-card">
                            <h5 class="mb-3">Resultados de la Consulta</h5>
                            <div id="resultadosApi" class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Nro</th>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>RUC</th>
                                            <th>Dirección</th>
                                            <th>Teléfono</th>
                                            <th>Email</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaEmpresas">
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                Realice una consulta para ver los resultados
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
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
                    <h2>Gestion de Usuarios</h2>
                    <button class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nuevo Usuario
                    </button>
                </div>
                <div class="alert alert-info">
                    Esta funcionalidad estará disponible pronto. Solo para administradores.
                </div>
            `;
        }

        // Funciones para la consulta de API
        function actualizarConfiguracion() {
            apiUrl = document.getElementById('apiUrl').value;
            apiToken = document.getElementById('apiToken').value;
            consultaTipo = document.getElementById('consultaTipo').value;
        }

        async function consultarEmpresas() {
            actualizarConfiguracion();
            
            if (!apiUrl || !apiToken) {
                showAlert('Por favor, completa la URL y el token de la API', 'warning');
                return;
            }
            
            try {
                showAlert('Consultando empresas...', 'info');
                
                // Simulación de consulta a la API
                // En una implementación real, aquí harías la llamada a la API
                setTimeout(() => {
                    // Datos de ejemplo para simular la respuesta
                    const empresas = [
                        { id: 1, nombre: 'Empresa Ejemplo 1', ruc: '20100066601', direccion: 'Av. Ejemplo 123', telefono: '987654321', email: 'contacto@empresa1.com' },
                        { id: 2, nombre: 'Empresa Ejemplo 2', ruc: '20100066602', direccion: 'Av. Demo 456', telefono: '987654322', email: 'info@empresa2.com' },
                        { id: 3, nombre: 'Empresa Ejemplo 3', ruc: '20100066603', direccion: 'Calle Test 789', telefono: '987654323', email: 'ventas@empresa3.com' }
                    ];
                    
                    mostrarResultados(empresas);
                    showAlert('Consulta completada correctamente', 'success');
                }, 1500);
                
            } catch (error) {
                console.error('Error consultando API:', error);
                showAlert('Error al consultar la API: ' + error.message, 'danger');
            }
        }

        function mostrarResultados(empresas) {
            const tabla = document.getElementById('tablaEmpresas');
            
            if (!empresas || empresas.length === 0) {
                tabla.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No se encontraron empresas
                        </td>
                    </tr>
                `;
                return;
            }
            
            let html = '';
            empresas.forEach((empresa, index) => {
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${empresa.id}</td>
                        <td>${empresa.nombre}</td>
                        <td>${empresa.ruc}</td>
                        <td>${empresa.direccion}</td>
                        <td>${empresa.telefono}</td>
                        <td>${empresa.email}</td>
                    </tr>
                `;
            });
            
            tabla.innerHTML = html;
        }

        function limpiarResultados() {
            document.getElementById('tablaEmpresas').innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Realice una consulta para ver los resultados
                    </td>
                </tr>
            `;
        }

        function probarConexion() {
            actualizarConfiguracion();
            
            if (!apiUrl || !apiToken) {
                showAlert('Por favor, completa la URL y el token de la API', 'warning');
                return;
            }
            
            // Simulación de prueba de conexión
            showAlert('Probando conexión...', 'info');
            
            setTimeout(() => {
                // En una implementación real, aquí verificarías la conexión a la API
                showAlert('Conexión exitosa a la API', 'success');
            }, 1000);
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