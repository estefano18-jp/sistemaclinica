<?php
// Incluir el encabezado de enfermería que ya tiene la verificación de sesión
include_once "../../include/header.enfermeria.php";
date_default_timezone_set('America/Lima');
// Obtener idcolaborador (enfermera) de la sesión
$idEnfermera = $_SESSION['usuario']['idcolaborador'];
$fechaHoy = date('Y-m-d');
$horaActual = date('H:i:s');
?>

<div class="container-fluid px-4" id="main-container">
    <!-- Sección de título y breadcrumb -->
    <div id="page-header-section">
        <h1 class="mt-4">Registro de Triaje</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="<?= $host ?>/views/include/dashboard.enfermeria.php">Panel de Control</a></li>
            <li class="breadcrumb-item active">Triaje de Pacientes</li>
        </ol>
    </div>

    <!-- Sección de listado de pacientes para triaje -->
    <div id="lista-pacientes-triaje-container">
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-heartbeat me-2"></i>Pacientes Pendientes de Triaje
                </h5>
                <div>
                    <button class="btn btn-sm btn-light" id="btn-actualizar-pacientes-triaje">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <!-- Filtros de búsqueda -->
                <div class="p-3 bg-light border-bottom">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="filtro-fecha-triaje" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="filtro-fecha-triaje" value="<?= $fechaHoy ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="filtro-paciente-triaje" class="form-label">Buscar Paciente</label>
                            <input type="text" class="form-control" id="filtro-paciente-triaje" placeholder="Nombre o documento">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-primary me-2" id="btn-buscar-pacientes-triaje">
                                <i class="fas fa-search me-1"></i> Buscar
                            </button>
                            <button class="btn btn-secondary" id="btn-limpiar-filtros-triaje">
                                <i class="fas fa-broom me-1"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Información de estado -->
                <div class="px-3 py-2 bg-info-subtle border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary me-2"><?= date('d/m/Y') ?></span>
                        <span class="badge bg-info-subtle text-info" id="total-pacientes-triaje">0 pacientes</span>
                    </div>
                    <div class="text-muted">
                        <i class="fas fa-clock me-1"></i>Última actualización: <span id="ultima-actualizacion"><?= date('H:i') ?></span>
                    </div>
                </div>

                <!-- Tabla de pacientes -->
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tabla-pacientes-triaje">
                        <thead class="table-light">
                            <tr>
                                <th><i class="fas fa-user me-1"></i>Paciente</th>
                                <th><i class="fas fa-id-card me-1"></i>Documento</th>
                                <th><i class="fas fa-clock me-1"></i>Hora Programada</th>
                                <th><i class="fas fa-user-md me-1"></i>Doctor</th>
                                <th><i class="fas fa-stethoscope me-1"></i>Especialidad</th>
                                <th><i class="fas fa-heartbeat me-1"></i>Estado Triaje</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los datos se cargarán dinámicamente -->
                        </tbody>
                    </table>
                </div>

                <div id="sin-pacientes-triaje" class="text-center p-4 d-none">
                    <i class="fas fa-heartbeat fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay pacientes pendientes de triaje</h5>
                    <p class="text-muted">Todos los pacientes han sido evaluados o no hay citas programadas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de registro de triaje - Inicialmente oculta -->
    <div id="triaje-container" class="d-none w-100 mt-4 mb-5">
        <div class="card shadow">
            <div class="card-header bg-gradient-success text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0">
                    <i class="fas fa-heartbeat me-2"></i>Registro de Triaje - Signos Vitales
                </h5>
                <button type="button" class="btn-close btn-close-white" id="btn-cerrar-triaje" aria-label="Close"></button>
            </div>
            
            <div class="card-body p-0">
                <input type="hidden" id="idconsulta-triaje" name="idconsulta">
                <input type="hidden" id="idpaciente-triaje" name="idpaciente">

                <!-- Información del paciente -->
                <div class="paciente-info-triaje p-4 bg-light border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <div class="avatar-triaje">
                                <i class="fas fa-user-circle fa-4x text-success"></i>
                            </div>
                        </div>
                        <div class="col-md-10">
                            <h4 id="paciente-nombre-triaje" class="mb-2 text-success fw-bold"></h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <p class="mb-1"><i class="fas fa-id-card text-secondary me-2"></i><span id="paciente-documento-triaje"></span></p>
                                    <p class="mb-1"><i class="fas fa-birthday-cake text-secondary me-2"></i><span id="paciente-edad-triaje"></span></p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1"><i class="fas fa-venus-mars text-secondary me-2"></i><span id="paciente-genero-triaje"></span></p>
                                    <p class="mb-1"><i class="fas fa-phone text-secondary me-2"></i><span id="paciente-telefono-triaje"></span></p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1"><i class="fas fa-user-md text-secondary me-2"></i><span id="consulta-doctor-triaje"></span></p>
                                    <p class="mb-1"><i class="fas fa-clock text-secondary me-2"></i><span id="consulta-hora-triaje"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de triaje -->
                <div class="p-4">
                    <form id="form-triaje" class="needs-validation" novalidate>
                        <div class="row">
                            <!-- Signos vitales principales -->
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="fas fa-heartbeat me-2"></i>Signos Vitales</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="temperatura" class="form-label">
                                                    <i class="fas fa-thermometer-half text-danger me-1"></i>Temperatura (°C)
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" step="0.1" min="35" max="45" class="form-control" 
                                                           id="temperatura" name="temperatura" placeholder="36.5">
                                                    <span class="input-group-text">°C</span>
                                                </div>
                                                <div class="form-text">Normal: 36.1 - 37.2°C</div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="frecuenciacardiaca" class="form-label">
                                                    <i class="fas fa-heart text-danger me-1"></i>Frecuencia Cardíaca
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" min="40" max="200" class="form-control" 
                                                           id="frecuenciacardiaca" name="frecuenciacardiaca" placeholder="80">
                                                    <span class="input-group-text">lpm</span>
                                                </div>
                                                <div class="form-text">Normal: 60 - 100 lpm</div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <label for="presionarterial" class="form-label">
                                                    <i class="fas fa-tint text-primary me-1"></i>Presión Arterial
                                                </label>
                                                <input type="text" class="form-control" id="presionarterial" 
                                                       name="presionarterial" placeholder="120/80" 
                                                       pattern="[0-9]{2,3}/[0-9]{2,3}">
                                                <div class="form-text">Formato: sistólica/diastólica (ej: 120/80)</div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <label for="saturacionoxigeno" class="form-label">
                                                    <i class="fas fa-lungs text-info me-1"></i>Saturación de Oxígeno
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" min="70" max="100" class="form-control" 
                                                           id="saturacionoxigeno" name="saturacionoxigeno" placeholder="98">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                                <div class="form-text">Normal: ≥ 95%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Medidas antropométricas -->
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fas fa-weight me-2"></i>Medidas Antropométricas</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="peso" class="form-label">
                                                    <i class="fas fa-weight text-success me-1"></i>Peso
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" step="0.1" min="1" max="300" class="form-control" 
                                                           id="peso" name="peso" placeholder="70.5">
                                                    <span class="input-group-text">kg</span>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="estatura" class="form-label">
                                                    <i class="fas fa-ruler-vertical text-success me-1"></i>Estatura
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" min="0.5" max="2.5" class="form-control" 
                                                           id="estatura" name="estatura" placeholder="1.70">
                                                    <span class="input-group-text">m</span>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="card bg-light">
                                                    <div class="card-body p-3">
                                                        <h6 class="card-title mb-2">
                                                            <i class="fas fa-calculator text-warning me-1"></i>Índice de Masa Corporal (IMC)
                                                        </h6>
                                                        <div class="row text-center">
                                                            <div class="col-6">
                                                                <span class="h4 text-primary" id="imc-valor">--</span>
                                                                <div class="small text-muted">kg/m²</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <span class="badge bg-secondary" id="imc-categoria">Pendiente</span>
                                                                <div class="small text-muted">Categoría</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones adicionales -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="fas fa-notes-medical me-2"></i>Observaciones y Evaluación Inicial</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="observaciones-triaje" class="form-label">Observaciones Generales</label>
                                            <textarea class="form-control" id="observaciones-triaje" name="observaciones" 
                                                     rows="3" placeholder="Registre cualquier observación relevante sobre el estado del paciente..."></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="prioridad-triaje" class="form-label">Nivel de Prioridad</label>
                                                <select class="form-select" id="prioridad-triaje" name="prioridad">
                                                    <option value="BAJA" selected>🟢 Baja - No urgente</option>
                                                    <option value="MEDIA">🟡 Media - Atención en orden</option>
                                                    <option value="ALTA">🟠 Alta - Atención prioritaria</option>
                                                    <option value="CRITICA">🔴 Crítica - Atención inmediata</option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="motivo-consulta" class="form-label">Motivo Principal de Consulta</label>
                                                <input type="text" class="form-control" id="motivo-consulta" 
                                                       name="motivo_consulta" placeholder="Ej: dolor abdominal, fiebre, control...">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-footer bg-light py-3">
                <div class="row">
                    <div class="col-6">
                        <button type="button" class="btn btn-secondary" id="btn-volver-lista-triaje">
                            <i class="fas fa-arrow-left me-1"></i>Volver a la Lista
                        </button>
                    </div>
                    <div class="col-6 text-end">
                        <button type="button" class="btn btn-success btn-lg" id="btn-guardar-triaje">
                            <i class="fas fa-save me-1"></i>Guardar Triaje
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    
    .bg-info-subtle {
        background-color: rgba(13, 202, 240, 0.1);
    }
    
    .avatar-triaje {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        border-radius: 50%;
        background-color: rgba(40, 167, 69, 0.1);
        border: 2px solid #28a745;
    }
    
    .card {
        transition: transform 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    .input-group-text {
        font-weight: 500;
    }
    
    .form-text {
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .badge {
        font-size: 0.875rem;
    }
    
    #imc-valor {
        font-weight: bold;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.075);
    }
    
    .triaje-mode #page-header-section {
        display: none !important;
    }
    
    .btn-group .btn {
        margin-right: 0.25rem;
    }
    
    .estado-triaje {
        font-weight: 500;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }
    
    .estado-pendiente {
        background-color: #fff3cd;
        color: #664d03;
        border: 1px solid #ffecb5;
    }
    
    .estado-completado {
        background-color: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let pacientesTriaje = [];
    
    // Referencias a elementos del DOM
    const mainContainer = document.getElementById('main-container');
    const listaPacientesTriajeContainer = document.getElementById('lista-pacientes-triaje-container');
    const triajeContainer = document.getElementById('triaje-container');
    
    // Elementos de filtro y búsqueda
    const filtroFechaTriaje = document.getElementById('filtro-fecha-triaje');
    const filtroPacienteTriaje = document.getElementById('filtro-paciente-triaje');
    const btnBuscarPacientesTriaje = document.getElementById('btn-buscar-pacientes-triaje');
    const btnLimpiarFiltrosTriaje = document.getElementById('btn-limpiar-filtros-triaje');
    const btnActualizarPacientesTriaje = document.getElementById('btn-actualizar-pacientes-triaje');
    
    // Elementos de la tabla
    const tablaPacientesTriaje = document.getElementById('tabla-pacientes-triaje');
    const tablaPacientesTriajeBody = tablaPacientesTriaje.querySelector('tbody');
    const sinPacientesTriaje = document.getElementById('sin-pacientes-triaje');
    const totalPacientesTriajeSpan = document.getElementById('total-pacientes-triaje');
    const ultimaActualizacionSpan = document.getElementById('ultima-actualizacion');
    
    // Elementos del formulario de triaje
    const formTriaje = document.getElementById('form-triaje');
    const btnCerrarTriaje = document.getElementById('btn-cerrar-triaje');
    const btnVolverListaTriaje = document.getElementById('btn-volver-lista-triaje');
    const btnGuardarTriaje = document.getElementById('btn-guardar-triaje');
    
    // Campos del formulario
    const pesoInput = document.getElementById('peso');
    const estaturaInput = document.getElementById('estatura');
    const imcValor = document.getElementById('imc-valor');
    const imcCategoria = document.getElementById('imc-categoria');
    
    // Cargar datos iniciales
    cargarPacientesTriaje();
    
    // Event listeners
    btnActualizarPacientesTriaje.addEventListener('click', cargarPacientesTriaje);
    btnBuscarPacientesTriaje.addEventListener('click', buscarPacientesTriaje);
    btnLimpiarFiltrosTriaje.addEventListener('click', limpiarFiltrosTriaje);
    btnCerrarTriaje.addEventListener('click', volverAListado);
    btnVolverListaTriaje.addEventListener('click', volverAListado);
    btnGuardarTriaje.addEventListener('click', guardarTriaje);
    
    // Calcular IMC automáticamente
    pesoInput.addEventListener('input', calcularIMC);
    estaturaInput.addEventListener('input', calcularIMC);
    
    // Funciones principales
    function cargarPacientesTriaje() {
        mostrarCargandoTabla();
        
        const fecha = filtroFechaTriaje.value || '<?= $fechaHoy ?>';
        
        fetch(`<?= $host ?>/controllers/consulta.controller.php?op=listar_pacientes_triaje&fecha=${fecha}`)
            .then(response => response.json())
            .then(data => {
                if (data.status && data.data) {
                    pacientesTriaje = data.data;
                    mostrarPacientesTriaje(pacientesTriaje);
                } else {
                    mostrarSinPacientesTriaje();
                }
                actualizarHoraActualizacion();
            })
            .catch(error => {
                console.error('Error al cargar pacientes:', error);
                mostrarSinPacientesTriaje();
            });
    }
    
    function mostrarCargandoTabla() {
        tablaPacientesTriajeBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <span>Cargando pacientes...</span>
                    </div>
                </td>
            </tr>
        `;
    }
    
    function mostrarPacientesTriaje(pacientes) {
        tablaPacientesTriajeBody.innerHTML = '';
        sinPacientesTriaje.classList.add('d-none');
        tablaPacientesTriaje.classList.remove('d-none');
        
        if (pacientes.length === 0) {
            mostrarSinPacientesTriaje();
            return;
        }
        
        pacientes.forEach(paciente => {
            const row = document.createElement('tr');
            
            // Determinar estado del triaje
            const estadoTriaje = paciente.triaje_completado ? 'completado' : 'pendiente';
            const estadoTexto = paciente.triaje_completado ? 
                '<span class="estado-triaje estado-completado">✓ Completado</span>' : 
                '<span class="estado-triaje estado-pendiente">⏳ Pendiente</span>';
            
            row.innerHTML = `
                <td>
                    <div class="d-flex align-items-center">
                        <div class="me-2">
                            <i class="fas fa-user-circle fa-2x text-primary"></i>
                        </div>
                        <div>
                            <div class="fw-bold">${paciente.paciente_nombres} ${paciente.paciente_apellidos}</div>
                            <div class="text-muted small">${calcularEdad(paciente.fecha_nacimiento)} años</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div>${paciente.tipo_documento}</div>
                    <div class="text-muted">${paciente.numero_documento}</div>
                </td>
                <td>
                    <div class="fw-bold">${formatearHora(paciente.hora_programada)}</div>
                    <div class="text-muted small">${formatearFecha(paciente.fecha)}</div>
                </td>
                <td>
                    <div class="fw-bold">${paciente.doctor_nombre}</div>
                </td>
                <td>
                    <span class="badge bg-primary">${paciente.especialidad}</span>
                </td>
                <td>${estadoTexto}</td>
                <td class="text-center">
                    <div class="btn-group">
                        ${!paciente.triaje_completado ? `
                            <button class="btn btn-success btn-sm btn-iniciar-triaje" 
                                    data-idconsulta="${paciente.idconsulta}"
                                    data-idpaciente="${paciente.idpaciente}"
                                    title="Realizar Triaje">
                                <i class="fas fa-heartbeat"></i> Triaje
                            </button>
                        ` : `
                            <button class="btn btn-info btn-sm btn-ver-triaje" 
                                    data-idconsulta="${paciente.idconsulta}"
                                    title="Ver Triaje">
                                <i class="fas fa-eye"></i> Ver
                            </button>
                            <button class="btn btn-warning btn-sm btn-editar-triaje" 
                                    data-idconsulta="${paciente.idconsulta}"
                                    data-idpaciente="${paciente.idpaciente}"
                                    title="Editar Triaje">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        `}
                    </div>
                </td>
            `;
            
            tablaPacientesTriajeBody.appendChild(row);
        });
        
        totalPacientesTriajeSpan.textContent = `${pacientes.length} pacientes`;
        
        // Agregar event listeners a los botones
        document.querySelectorAll('.btn-iniciar-triaje, .btn-editar-triaje').forEach(btn => {
            btn.addEventListener('click', function() {
                const idconsulta = this.getAttribute('data-idconsulta');
                const idpaciente = this.getAttribute('data-idpaciente');
                iniciarTriaje(idconsulta, idpaciente);
            });
        });
        
        document.querySelectorAll('.btn-ver-triaje').forEach(btn => {
            btn.addEventListener('click', function() {
                const idconsulta = this.getAttribute('data-idconsulta');
                verTriaje(idconsulta);
            });
        });
    }
    
    function mostrarSinPacientesTriaje() {
        tablaPacientesTriajeBody.innerHTML = '';
        sinPacientesTriaje.classList.remove('d-none');
        totalPacientesTriajeSpan.textContent = '0 pacientes';
    }
    
    function buscarPacientesTriaje() {
        const fecha = filtroFechaTriaje.value;
        const busqueda = filtroPacienteTriaje.value.trim();
        
        let pacientesFiltrados = [...pacientesTriaje];
        
        if (busqueda) {
            pacientesFiltrados = pacientesFiltrados.filter(paciente => 
                paciente.paciente_nombres.toLowerCase().includes(busqueda.toLowerCase()) ||
                paciente.paciente_apellidos.toLowerCase().includes(busqueda.toLowerCase()) ||
                paciente.numero_documento.includes(busqueda)
            );
        }
        
        mostrarPacientesTriaje(pacientesFiltrados);
    }
    
    function limpiarFiltrosTriaje() {
        filtroFechaTriaje.value = '<?= $fechaHoy ?>';
        filtroPacienteTriaje.value = '';
        cargarPacientesTriaje();
    }
    
    function iniciarTriaje(idconsulta, idpaciente) {
        // Buscar los datos del paciente
        const paciente = pacientesTriaje.find(p => p.idconsulta == idconsulta);
        if (!paciente) {
            Swal.fire('Error', 'No se encontraron los datos del paciente', 'error');
            return;
        }
        
        // Llenar los datos del paciente
        document.getElementById('idconsulta-triaje').value = idconsulta;
        document.getElementById('idpaciente-triaje').value = idpaciente;
        document.getElementById('paciente-nombre-triaje').textContent = `${paciente.paciente_nombres} ${paciente.paciente_apellidos}`;
        document.getElementById('paciente-documento-triaje').textContent = `${paciente.tipo_documento}: ${paciente.numero_documento}`;
        document.getElementById('paciente-edad-triaje').textContent = `${calcularEdad(paciente.fecha_nacimiento)} años`;
        document.getElementById('paciente-genero-triaje').textContent = paciente.genero || 'No especificado';
        document.getElementById('paciente-telefono-triaje').textContent = paciente.telefono || 'No registrado';
        document.getElementById('consulta-doctor-triaje').textContent = paciente.doctor_nombre;
        document.getElementById('consulta-hora-triaje').textContent = `${formatearHora(paciente.hora_programada)} - ${formatearFecha(paciente.fecha)}`;
        
        // Si es edición, cargar datos existentes
        if (paciente.triaje_completado) {
            cargarDatosTriajeExistente(idconsulta);
        } else {
            // Limpiar formulario para nuevo triaje
            formTriaje.reset();
            resetearIMC();
        }
        
        // Mostrar el formulario de triaje
        mainContainer.classList.add('triaje-mode');
        listaPacientesTriajeContainer.style.display = 'none';
        triajeContainer.classList.remove('d-none');
    }
    
    function cargarDatosTriajeExistente(idconsulta) {
        fetch(`<?= $host ?>/controllers/consulta.controller.php?op=obtener_triaje&idconsulta=${idconsulta}`)
            .then(response => response.json())
            .then(data => {
                if (data.status && data.triaje) {
                    const triaje = data.triaje;
                    
                    // Llenar los campos con los datos existentes
                    document.getElementById('temperatura').value = triaje.temperatura || '';
                    document.getElementById('frecuenciacardiaca').value = triaje.frecuenciacardiaca || '';
                    document.getElementById('presionarterial').value = triaje.presionarterial || '';
                    document.getElementById('saturacionoxigeno').value = triaje.saturacionoxigeno || '';
                    document.getElementById('peso').value = triaje.peso || '';
                    document.getElementById('estatura').value = triaje.estatura || '';
                    document.getElementById('observaciones-triaje').value = triaje.observaciones || '';
                    document.getElementById('motivo-consulta').value = triaje.motivo_consulta || '';
                    document.getElementById('prioridad-triaje').value = triaje.prioridad || 'BAJA';
                    
                    // Calcular IMC si hay peso y estatura
                    calcularIMC();
                }
            })
            .catch(error => {
                console.error('Error al cargar datos del triaje:', error);
            });
    }
    
    function verTriaje(idconsulta) {
        // Aquí podrías abrir un modal o redirigir a una página de detalle
        window.open(`<?= $host ?>/views/SesionEnfermeria/Triaje/verTriaje.php?idconsulta=${idconsulta}`, '_blank');
    }
    
    function volverAListado() {
        mainContainer.classList.remove('triaje-mode');
        triajeContainer.classList.add('d-none');
        listaPacientesTriajeContainer.style.display = 'block';
        
        // Recargar la lista para actualizar estados
        cargarPacientesTriaje();
    }
    
    function guardarTriaje() {
        if (!formTriaje.checkValidity()) {
            formTriaje.classList.add('was-validated');
            Swal.fire({
                title: 'Campos incompletos',
                text: 'Por favor, complete al menos los signos vitales básicos',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        Swal.fire({
            title: '¿Guardar Triaje?',
            text: 'Se registrarán los signos vitales del paciente',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                procesarGuardadoTriaje();
            }
        });
    }
    
    function procesarGuardadoTriaje() {
        btnGuardarTriaje.disabled = true;
        btnGuardarTriaje.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';
        
        const formData = new FormData();
        formData.append('idconsulta', document.getElementById('idconsulta-triaje').value);
        formData.append('idenfermera', <?= $idEnfermera ?>);
        formData.append('hora', '<?= $horaActual ?>');
        formData.append('temperatura', document.getElementById('temperatura').value);
        formData.append('frecuenciacardiaca', document.getElementById('frecuenciacardiaca').value);
        formData.append('presionarterial', document.getElementById('presionarterial').value);
        formData.append('saturacionoxigeno', document.getElementById('saturacionoxigeno').value);
        formData.append('peso', document.getElementById('peso').value);
        formData.append('estatura', document.getElementById('estatura').value);
        formData.append('observaciones', document.getElementById('observaciones-triaje').value);
        formData.append('motivo_consulta', document.getElementById('motivo-consulta').value);
        formData.append('prioridad', document.getElementById('prioridad-triaje').value);
        
        fetch('<?= $host ?>/controllers/consulta.controller.php?op=registrar_triaje', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            btnGuardarTriaje.disabled = false;
            btnGuardarTriaje.innerHTML = '<i class="fas fa-save me-1"></i>Guardar Triaje';
            
            if (data.status) {
                Swal.fire({
                    title: 'Triaje Guardado',
                    text: 'Los signos vitales han sido registrados correctamente',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    volverAListado();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.mensaje || 'Error al guardar el triaje',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        })
        .catch(error => {
            btnGuardarTriaje.disabled = false;
            btnGuardarTriaje.innerHTML = '<i class="fas fa-save me-1"></i>Guardar Triaje';
            
            console.error('Error al guardar triaje:', error);
            Swal.fire({
                title: 'Error',
                text: 'Ha ocurrido un error al guardar el triaje',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        });
    }
    
    function calcularIMC() {
        const peso = parseFloat(pesoInput.value);
        const estatura = parseFloat(estaturaInput.value);
        
        if (peso && estatura && peso > 0 && estatura > 0) {
            const imc = peso / (estatura * estatura);
            imcValor.textContent = imc.toFixed(1);
            
            // Determinar categoría
            let categoria = '';
            let colorClass = '';
            
            if (imc < 18.5) {
                categoria = 'Bajo peso';
                colorClass = 'bg-info';
            } else if (imc < 25) {
                categoria = 'Normal';
                colorClass = 'bg-success';
            } else if (imc < 30) {
                categoria = 'Sobrepeso';
                colorClass = 'bg-warning';
            } else {
                categoria = 'Obesidad';
                colorClass = 'bg-danger';
            }
            
            imcCategoria.textContent = categoria;
            imcCategoria.className = `badge ${colorClass}`;
        } else {
            resetearIMC();
        }
    }
    
    function resetearIMC() {
        imcValor.textContent = '--';
        imcCategoria.textContent = 'Pendiente';
        imcCategoria.className = 'badge bg-secondary';
    }
    
    // Funciones auxiliares
    function calcularEdad(fechaNacimiento) {
        const hoy = new Date();
        const nacimiento = new Date(fechaNacimiento);
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();
        
        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--;
        }
        
        return edad;
    }
    
    function formatearHora(hora) {
        return hora ? hora.substring(0, 5) : '--:--';
    }
    
    function formatearFecha(fecha) {
        const date = new Date(fecha);
        return date.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
    
    function actualizarHoraActualizacion() {
        const ahora = new Date();
        ultimaActualizacionSpan.textContent = ahora.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }
});
</script>

<?php
// Incluir el footer
include_once "../../include/footer.php";
?>