<?php
// Incluir el encabezado del doctor que ya tiene la verificación de sesión
include_once "../../include/header.doctor.php";

// Obtener idcolaborador (doctor) de la sesión
$idDoctor = $_SESSION['usuario']['idcolaborador'];
$fechaHoy = date('Y-m-d');
$fechaInicio = date('Y-m-01'); // Primer día del mes actual
$fechaFin = date('Y-m-t');     // Último día del mes actual
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Ver Citas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= $host ?>/views/include/dashboard.doctor.php">Panel de Control</a></li>
        <li class="breadcrumb-item">Citas Médicas</li>
        <li class="breadcrumb-item active">Ver Citas</li>
    </ol>

    <!-- Sección de filtros -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-filter me-1"></i> Filtros
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="fecha-inicio" class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fecha-inicio" value="<?= $fechaInicio ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="fecha-fin" class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" id="fecha-fin" value="<?= $fechaFin ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado">
                            <option value="">Todos</option>
                            <option value="PROGRAMADA">Programada</option>
                            <option value="REALIZADA">Realizada</option>
                            <option value="CANCELADA">Cancelada</option>
                            <option value="NO ASISTIO">No Asistió</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="busqueda" class="form-label">Búsqueda</label>
                        <input type="text" class="form-control" id="busqueda" placeholder="Documento o Nombre">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-end">
                    <button class="btn btn-primary" id="btn-buscar">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                    <button class="btn btn-secondary" id="btn-limpiar">
                        <i class="fas fa-broom me-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4 shadow">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                    <div>
                        <div class="small">Total Citas</div>
                        <div class="h3 mb-0 fw-bold" id="total-citas">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4 shadow">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <div class="small">Realizadas</div>
                        <div class="h3 mb-0 fw-bold" id="total-realizadas">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4 shadow">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-user-clock fa-2x"></i>
                    </div>
                    <div>
                        <div class="small">Programadas</div>
                        <div class="h3 mb-0 fw-bold" id="total-programadas">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4 shadow">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-user-times fa-2x"></i>
                    </div>
                    <div>
                        <div class="small">No Asistieron</div>
                        <div class="h3 mb-0 fw-bold" id="total-no-asistio">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de citas -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-info text-white">
            <i class="fas fa-list me-1"></i> Listado de Citas
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tabla-citas">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Paciente</th>
                            <th>Documento</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los datos se cargarán dinámicamente -->
                    </tbody>
                </table>
            </div>
            <div id="sin-citas" class="text-center p-4 d-none">
                <img src="<?= $host ?>/assets/img/no-appointments.svg" alt="No hay citas" class="img-fluid mb-3" style="max-height: 150px;">
                <h5 class="text-muted">No se encontraron citas con los filtros seleccionados</h5>
            </div>
        </div>
    </div>
</div>

<!-- Modal para reportar inasistencia -->
<div class="modal fade" id="modalInasistencia" tabindex="-1" aria-labelledby="modalInasistenciaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalInasistenciaLabel">Reportar Inasistencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>El paciente no asistió a su cita programada. Por favor, proporcione un motivo para la solicitud de reprogramación.</p>
                
                <form id="form-inasistencia">
                    <input type="hidden" id="idcita-inasistencia" name="idcita">
                    
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo de Inasistencia:</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="solicitar-reprogramacion" name="reprogramar" value="1">
                        <label class="form-check-label" for="solicitar-reprogramacion">
                            Solicitar reprogramación
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btn-confirmar-inasistencia">
                    <i class="fas fa-save me-1"></i> Registrar Inasistencia
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalles del paciente -->
<div class="modal fade" id="modalPaciente" tabindex="-1" aria-labelledby="modalPacienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalPacienteLabel">Información del Paciente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">Datos Personales</h6>
                        <div class="mb-3">
                            <label class="fw-bold">Nombre:</label>
                            <p id="paciente-nombre" class="mb-1"></p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Documento:</label>
                            <p id="paciente-documento" class="mb-1"></p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Teléfono:</label>
                            <p id="paciente-telefono" class="mb-1"></p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Email:</label>
                            <p id="paciente-email" class="mb-1"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">Cita Actual</h6>
                        <div class="mb-3">
                            <label class="fw-bold">Fecha:</label>
                            <p id="cita-fecha" class="mb-1"></p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Hora:</label>
                            <p id="cita-hora" class="mb-1"></p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Estado:</label>
                            <p id="cita-estado" class="mb-1"></p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Observaciones:</label>
                            <p id="cita-observaciones" class="mb-1"></p>
                        </div>
                    </div>
                </div>
                
                <!-- Alergias -->
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Alergias</h6>
                        <div id="alergias-container">
                            <div class="alert alert-warning d-none" id="alergias-mensaje">
                                No se encontraron alergias registradas para este paciente.
                            </div>
                            <div id="alergias-lista"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Historial de Recetas -->
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Historial de Recetas</h6>
                        <div id="recetas-container">
                            <div class="alert alert-info d-none" id="recetas-mensaje">
                                No se encontraron recetas para este paciente.
                            </div>
                            <div id="recetas-lista" class="table-responsive"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-ver-historia">
                    <i class="fas fa-clipboard-list me-1"></i> Ver Historia Clínica
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const fechaInicio = document.getElementById('fecha-inicio');
    const fechaFin = document.getElementById('fecha-fin');
    const estadoSelect = document.getElementById('estado');
    const busquedaInput = document.getElementById('busqueda');
    const btnBuscar = document.getElementById('btn-buscar');
    const btnLimpiar = document.getElementById('btn-limpiar');
    
    const totalCitas = document.getElementById('total-citas');
    const totalRealizadas = document.getElementById('total-realizadas');
    const totalProgramadas = document.getElementById('total-programadas');
    const totalNoAsistio = document.getElementById('total-no-asistio');
    
    const tablaCitas = document.getElementById('tabla-citas');
    const tablaCitasBody = tablaCitas.querySelector('tbody');
    const sinCitas = document.getElementById('sin-citas');
    
    const modalInasistencia = new bootstrap.Modal(document.getElementById('modalInasistencia'));
    const formInasistencia = document.getElementById('form-inasistencia');
    const idcitaInasistencia = document.getElementById('idcita-inasistencia');
    const motivo = document.getElementById('motivo');
    const solicitarReprogramacion = document.getElementById('solicitar-reprogramacion');
    const btnConfirmarInasistencia = document.getElementById('btn-confirmar-inasistencia');
    
    const modalPaciente = new bootstrap.Modal(document.getElementById('modalPaciente'));
    const pacienteNombre = document.getElementById('paciente-nombre');
    const pacienteDocumento = document.getElementById('paciente-documento');
    const pacienteTelefono = document.getElementById('paciente-telefono');
    const pacienteEmail = document.getElementById('paciente-email');
    const citaFecha = document.getElementById('cita-fecha');
    const citaHora = document.getElementById('cita-hora');
    const citaEstado = document.getElementById('cita-estado');
    const citaObservaciones = document.getElementById('cita-observaciones');
    const alergiasContainer = document.getElementById('alergias-container');
    const alergiasMensaje = document.getElementById('alergias-mensaje');
    const alergiasLista = document.getElementById('alergias-lista');
    const recetasContainer = document.getElementById('recetas-container');
    const recetasMensaje = document.getElementById('recetas-mensaje');
    const recetasLista = document.getElementById('recetas-lista');
    const btnVerHistoria = document.getElementById('btn-ver-historia');
    
    // Variables globales
    let idPacienteActual = null;
    
    // Cargar citas iniciales
    cargarCitas();
    
    // Event Listeners
    btnBuscar.addEventListener('click', function() {
        cargarCitas();
    });
    
    btnLimpiar.addEventListener('click', function() {
        fechaInicio.value = '<?= $fechaInicio ?>';
        fechaFin.value = '<?= $fechaFin ?>';
        estadoSelect.value = '';
        busquedaInput.value = '';
        cargarCitas();
    });
    
    btnConfirmarInasistencia.addEventListener('click', function() {
        if (!formInasistencia.checkValidity()) {
            formInasistencia.reportValidity();
            return;
        }
        
        registrarInasistencia();
    });
    
    // Funciones
    function cargarCitas() {
        mostrarCargando();
        
        const params = new URLSearchParams();
        params.append('op', 'listar_todas');
        
        if (fechaInicio.value) {
            params.append('fecha_inicio', fechaInicio.value);
        }
        
        if (fechaFin.value) {
            params.append('fecha_fin', fechaFin.value);
        }
        
        if (estadoSelect.value) {
            params.append('estado', estadoSelect.value);
        }
        
        let url = `<?= $host ?>/controllers/cita.controller.php?${params.toString()}`;
        
        if (busquedaInput.value) {
            // Verificar si parece un número de documento
            if (/^\d+$/.test(busquedaInput.value)) {
                url = `<?= $host ?>/controllers/cita.controller.php?op=buscar_por_documento&nrodoc=${busquedaInput.value}`;
            } else {
                url = `<?= $host ?>/controllers/cita.controller.php?op=buscar_por_nombre&busqueda=${encodeURIComponent(busquedaInput.value)}`;
            }
        }
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                ocultarCargando();
                
                if (data.status && data.data && data.data.length > 0) {
                    mostrarCitas(data.data);
                    actualizarEstadisticas(data.data);
                } else {
                    mostrarSinCitas();
                    reiniciarEstadisticas();
                }
            })
            .catch(error => {
                ocultarCargando();
                console.error('Error al cargar citas:', error);
                mostrarSinCitas();
                reiniciarEstadisticas();
            });
    }
    
    function mostrarCitas(citas) {
        tablaCitasBody.innerHTML = '';
        sinCitas.classList.add('d-none');
        
        citas.forEach(cita => {
            const row = document.createElement('tr');
            
            // Definir el color de fondo según el estado
            if (cita.estado === 'REALIZADA') {
                row.classList.add('table-success');
            } else if (cita.estado === 'NO ASISTIO') {
                row.classList.add('table-warning');
            } else if (cita.estado === 'CANCELADA') {
                row.classList.add('table-danger');
            }
            
            const fechaFormateada = new Date(cita.fecha).toLocaleDateString('es-ES');
            const horaFormateada = new Date(`2000-01-01T${cita.hora}`).toLocaleTimeString('es-ES', {
                hour: '2-digit', 
                minute: '2-digit'
            });
            
            row.innerHTML = `
                <td class="text-nowrap">${fechaFormateada}</td>
                <td class="text-nowrap">${horaFormateada}</td>
                <td>${cita.nombre_paciente}</td>
                <td>${cita.tipodoc}: ${cita.nrodoc}</td>
                <td>${cita.telefono || 'No registrado'}</td>
                <td>
                    <span class="badge ${getEstadoBadgeClass(cita.estado)}">${cita.estado}</span>
                </td>
                <td class="text-center">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info btn-ver-paciente" 
                                data-idcita="${cita.idcita}"
                                data-idpaciente="${cita.idpaciente}">
                            <i class="fas fa-user me-1"></i> Datos
                        </button>
                        
                        ${cita.estado === 'PROGRAMADA' ? `
                            <button class="btn btn-sm btn-success btn-realizar-consulta" 
                                    data-idcita="${cita.idcita}" 
                                    data-idconsulta="${cita.idconsulta}" 
                                    data-idpaciente="${cita.idpaciente}">
                                <i class="fas fa-stethoscope me-1"></i> Realizar
                            </button>
                            <button class="btn btn-sm btn-warning btn-inasistencia" 
                                    data-idcita="${cita.idcita}">
                                <i class="fas fa-user-times me-1"></i> No Asistió
                            </button>
                        ` : `
                            <button class="btn btn-sm btn-primary btn-ver-consulta" 
                                    data-idconsulta="${cita.idconsulta}">
                                <i class="fas fa-eye me-1"></i> Ver
                            </button>
                        `}
                    </div>
                </td>
            `;
            
            tablaCitasBody.appendChild(row);
        });
        
        // Agregar event listeners a los botones de acción
        document.querySelectorAll('.btn-realizar-consulta').forEach(btn => {
            btn.addEventListener('click', function() {
                const idcita = this.getAttribute('data-idcita');
                const idconsulta = this.getAttribute('data-idconsulta');
                const idpaciente = this.getAttribute('data-idpaciente');
                
                window.location.href = `<?= $host ?>/views/SesionDoctor/Consultas/realizarConsulta.php?idcita=${idcita}&idconsulta=${idconsulta}&idpaciente=${idpaciente}`;
            });
        });
        
        document.querySelectorAll('.btn-inasistencia').forEach(btn => {
            btn.addEventListener('click', function() {
                const idcita = this.getAttribute('data-idcita');
                abrirModalInasistencia(idcita);
            });
        });
        
        document.querySelectorAll('.btn-ver-paciente').forEach(btn => {
            btn.addEventListener('click', function() {
                const idcita = this.getAttribute('data-idcita');
                const idpaciente = this.getAttribute('data-idpaciente');
                abrirModalPaciente(idcita, idpaciente);
            });
        });
        
        document.querySelectorAll('.btn-ver-consulta').forEach(btn => {
            btn.addEventListener('click', function() {
                const idconsulta = this.getAttribute('data-idconsulta');
                window.location.href = `<?= $host ?>/views/SesionDoctor/Consultas/historialConsulta.php?idconsulta=${idconsulta}`;
            });
        });
    }
    
    function mostrarSinCitas() {
        tablaCitasBody.innerHTML = '';
        sinCitas.classList.remove('d-none');
    }
    
    function getEstadoBadgeClass(estado) {
        switch (estado) {
            case 'PROGRAMADA': return 'bg-primary';
            case 'REALIZADA': return 'bg-success';
            case 'CANCELADA': return 'bg-danger';
            case 'NO ASISTIO': return 'bg-warning';
            default: return 'bg-secondary';
        }
    }
    
    function actualizarEstadisticas(citas) {
        const total = citas.length;
        const realizadas = citas.filter(cita => cita.estado === 'REALIZADA').length;
        const programadas = citas.filter(cita => cita.estado === 'PROGRAMADA').length;
        const noAsistio = citas.filter(cita => cita.estado === 'NO ASISTIO').length;
        
        totalCitas.textContent = total;
        totalRealizadas.textContent = realizadas;
        totalProgramadas.textContent = programadas;
        totalNoAsistio.textContent = noAsistio;
    }
    
    function reiniciarEstadisticas() {
        totalCitas.textContent = '0';
        totalRealizadas.textContent = '0';
        totalProgramadas.textContent = '0';
        totalNoAsistio.textContent = '0';
    }
    
    function abrirModalInasistencia(idcita) {
        // Limpiar formulario
        formInasistencia.reset();
        
        // Asignar el ID de la cita
        idcitaInasistencia.value = idcita;
        
        // Mostrar modal
        modalInasistencia.show();
    }
    
    function registrarInasistencia() {
        const formData = new FormData();
        formData.append('idcita', idcitaInasistencia.value);
        formData.append('estado', 'NO ASISTIO');
        formData.append('observaciones', motivo.value);
        
        mostrarCargando();
        
        // 1. Actualizar el estado de la cita
        fetch('<?= $host ?>/controllers/cita.controller.php?op=actualizar_estado', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                // 2. Si se solicita reprogramación, enviar notificación
                if (solicitarReprogramacion.checked) {
                    const notificacionData = new FormData();
                    notificacionData.append('idcita', idcitaInasistencia.value);
                    notificacionData.append('motivo', motivo.value);
                    
                    return fetch('<?= $host ?>/controllers/cita.controller.php?op=notificar_reprogramacion', {
                        method: 'POST',
                        body: notificacionData
                    });
                } else {
                    return {status: true};
                }
            } else {
                throw new Error(data.mensaje || 'Error al actualizar estado de la cita');
            }
        })
        .then(response => {
            if (response.status) {
                ocultarCargando();
                modalInasistencia.hide();
                
                Swal.fire({
                    title: 'Inasistencia Registrada',
                    text: solicitarReprogramacion.checked ? 
                          'Se ha registrado la inasistencia y se ha enviado la solicitud de reprogramación' :
                          'Se ha registrado la inasistencia del paciente',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    cargarCitas();
                });
            }
        })
        .catch(error => {
            ocultarCargando();
            console.error('Error al registrar inasistencia:', error);
            
            Swal.fire({
                title: 'Error',
                text: error.message || 'Ha ocurrido un error al registrar la inasistencia',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        });
    }
    
    function abrirModalPaciente(idcita, idpaciente) {
        idPacienteActual = idpaciente;
        
        // Limpiar datos anteriores
        pacienteNombre.textContent = '';
        pacienteDocumento.textContent = '';
        pacienteTelefono.textContent = '';
        pacienteEmail.textContent = '';
        citaFecha.textContent = '';
        citaHora.textContent = '';
        citaEstado.textContent = '';
        citaObservaciones.textContent = '';
        alergiasLista.innerHTML = '';
        alergiasMensaje.classList.add('d-none');
        recetasLista.innerHTML = '';
        recetasMensaje.classList.add('d-none');
        
        mostrarCargando();
        
        // 1. Cargar detalles de la cita
        fetch(`<?= $host ?>/controllers/cita.controller.php?op=obtener_detalles&idcita=${idcita}`)
            .then(response => response.json())
            .then(data => {
                if (data.status && data.data) {
                    const cita = data.data;
                    
                    // Datos del paciente
                    pacienteNombre.textContent = cita.nombre_paciente;
                    pacienteDocumento.textContent = `${cita.tipodoc}: ${cita.nrodoc}`;
                    pacienteTelefono.textContent = cita.telefono || 'No registrado';
                    pacienteEmail.textContent = cita.email || 'No registrado';
                    
                    // Datos de la cita
                    const fechaFormateada = new Date(cita.fecha).toLocaleDateString('es-ES');
                    const horaFormateada = new Date(`2000-01-01T${cita.hora}`).toLocaleTimeString('es-ES', {
                        hour: '2-digit', 
                        minute: '2-digit'
                    });
                    
                    citaFecha.textContent = fechaFormateada;
                    citaHora.textContent = horaFormateada;
                    citaEstado.innerHTML = `<span class="badge ${getEstadoBadgeClass(cita.estado)}">${cita.estado}</span>`;
                    citaObservaciones.textContent = cita.observaciones || 'Sin observaciones';
                    
                    btnVerHistoria.onclick = function() {
                        window.location.href = `<?= $host ?>/views/SesionDoctor/Pacientes/historiaClinica.php?idpaciente=${idpaciente}`;
                    };
                    
                    // 2. Cargar alergias del paciente
                    return fetch(`<?= $host ?>/controllers/alergia.controller.php?operacion=listar_paciente&idpersona=${idpaciente}`);
                } else {
                    throw new Error('No se pudo obtener la información de la cita');
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status && data.alergias && data.alergias.length > 0) {
                    // Mostrar alergias
                    alergiasMensaje.classList.add('d-none');
                    
                    let alergiasHtml = '<div class="table-responsive"><table class="table table-sm table-bordered">';
                    alergiasHtml += '<thead><tr><th>Tipo</th><th>Alergia</th><th>Gravedad</th></tr></thead><tbody>';
                    
                    data.alergias.forEach(alergia => {
                        const gravedadClass = alergia.gravedad === 'GRAVE' ? 'text-danger' : 
                                             (alergia.gravedad === 'MODERADA' ? 'text-warning' : 'text-success');
                        
                        alergiasHtml += `
                            <tr>
                                <td>${alergia.tipoalergia}</td>
                                <td>${alergia.alergia}</td>
                                <td class="${gravedadClass}">${alergia.gravedad}</td>
                            </tr>
                        `;
                    });
                    
                    alergiasHtml += '</tbody></table></div>';
                    alergiasLista.innerHTML = alergiasHtml;
                } else {
                    // No hay alergias
                    alergiasMensaje.classList.remove('d-none');
                    alergiasLista.innerHTML = '';
                }
                
                // 3. Cargar recetas del paciente
                return fetch(`<?= $host ?>/controllers/receta.controller.php?op=obtener_por_paciente&idpaciente=${idpaciente}`);
            })
            .then(response => response.json())
            .then(data => {
                ocultarCargando();
                
                if (data.status && data.data && data.data.length > 0) {
                    // Mostrar recetas
                    recetasMensaje.classList.add('d-none');
                    
                    let recetasHtml = '<table class="table table-sm table-bordered">';
                    recetasHtml += '<thead><tr><th>Fecha</th><th>Medicación</th><th>Cantidad</th><th>Frecuencia</th><th>Doctor</th></tr></thead><tbody>';
                    
                    data.data.forEach(receta => {
                        const fecha = new Date(receta.fecha_consulta).toLocaleDateString('es-ES');
                        
                        recetasHtml += `
                            <tr>
                                <td>${fecha}</td>
                                <td>${receta.medicacion}</td>
                                <td>${receta.cantidad}</td>
                                <td>${receta.frecuencia}</td>
                                <td>${receta.doctor}</td>
                            </tr>
                        `;
                    });
                    
                    recetasHtml += '</tbody></table>';
                    recetasLista.innerHTML = recetasHtml;
                } else {
                    // No hay recetas
                    recetasMensaje.classList.remove('d-none');
                    recetasLista.innerHTML = '';
                }
                
                // Mostrar modal
                modalPaciente.show();
            })
            .catch(error => {
                ocultarCargando();
                console.error('Error al cargar información del paciente:', error);
                
                Swal.fire({
                    title: 'Error',
                    text: error.message || 'Ha ocurrido un error al cargar la información',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            });
    }
    
    // Funciones auxiliares
    function mostrarCargando() {
        if (!document.getElementById('overlay-loading')) {
            const overlay = document.createElement('div');
            overlay.id = 'overlay-loading';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255,255,255,0.7);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            `;
            
            const spinner = document.createElement('div');
            spinner.className = 'spinner-border text-primary';
            spinner.setAttribute('role', 'status');
            
            const span = document.createElement('span');
            span.className = 'visually-hidden';
            span.textContent = 'Cargando...';
            
            spinner.appendChild(span);
            overlay.appendChild(spinner);
            document.body.appendChild(overlay);
        }
    }
    
    function ocultarCargando() {
        const overlay = document.getElementById('overlay-loading');
        if (overlay) {
            overlay.remove();
        }
    }
});
</script>

<?php
// Incluir el footer
include_once "../../include/footer.php";
?>