<?php
include_once "../../include/header.doctor.php";

// Verificar que el usuario sea doctor
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'DOCTOR') {
    header('Location: ../../include/401.php');
    exit();
}

$idcolaborador = $_SESSION['usuario']['idcolaborador'];
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Listado de Pacientes</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= $host ?>/views/include/dashboard.doctor.php">Panel de Control</a></li>
        <li class="breadcrumb-item">Pacientes</li>
        <li class="breadcrumb-item active">Listado de Pacientes</li>
    </ol>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-filter me-1"></i> Filtros de Búsqueda
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="tipodoc" class="form-label">Tipo de Documento:</label>
                        <select class="form-select" id="tipodoc">
                            <option value="">Todos</option>
                            <option value="DNI">DNI</option>
                            <option value="PASAPORTE">Pasaporte</option>
                            <option value="CARNET DE EXTRANJERIA">Carnet de Extranjería</option>
                            <option value="OTRO">Otro</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="busqueda" class="form-label">Buscar:</label>
                        <input type="text" class="form-control" id="busqueda" placeholder="Nombre, apellido o documento">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="genero" class="form-label">Género:</label>
                        <select class="form-select" id="genero">
                            <option value="">Todos</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="OTRO">Otro</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button id="btnFiltrar" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de pacientes -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-users me-1"></i> Mis Pacientes
            </div>
            <div>
                <button id="btnReporteExcel" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </button>
                <button id="btnReportePDF" class="btn btn-sm btn-danger ms-2">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="tablaPacientes">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Paciente</th>
                            <th>Tipo Doc.</th>
                            <th>Documento</th>
                            <th>Teléfono</th>
                            <th>Edad</th>
                            <th>Género</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyPacientes">
                        <!-- Pacientes se cargarán aquí dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles del paciente -->
    <div class="modal fade" id="modalDetallePaciente" tabindex="-1" aria-labelledby="modalDetallePacienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalDetallePacienteLabel">Detalle del Paciente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="datos-tab" data-bs-toggle="tab" data-bs-target="#datos-tab-pane" type="button" role="tab">
                                <i class="fas fa-user me-1"></i> Datos Personales
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="alergias-tab" data-bs-toggle="tab" data-bs-target="#alergias-tab-pane" type="button" role="tab">
                                <i class="fas fa-allergies me-1"></i> Alergias
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial-tab-pane" type="button" role="tab">
                                <i class="fas fa-history me-1"></i> Historial de Citas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="recetas-tab" data-bs-toggle="tab" data-bs-target="#recetas-tab-pane" type="button" role="tab">
                                <i class="fas fa-prescription me-1"></i> Recetas
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content pt-3" id="myTabContent">
                        <!-- Tab de Datos Personales -->
                        <div class="tab-pane fade show active" id="datos-tab-pane" role="tabpanel" aria-labelledby="datos-tab" tabindex="0">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title mb-3">Información Personal</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <th style="width: 150px;">Nombre:</th>
                                                    <td id="detalleNombre"></td>
                                                </tr>
                                                <tr>
                                                    <th>Tipo Documento:</th>
                                                    <td id="detalleTipoDoc"></td>
                                                </tr>
                                                <tr>
                                                    <th>Documento:</th>
                                                    <td id="detalleNroDoc"></td>
                                                </tr>
                                                <tr>
                                                    <th>Fecha Nacimiento:</th>
                                                    <td id="detalleFechaNacimiento"></td>
                                                </tr>
                                                <tr>
                                                    <th>Edad:</th>
                                                    <td id="detalleEdad"></td>
                                                </tr>
                                                <tr>
                                                    <th>Género:</th>
                                                    <td id="detalleGenero"></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title mb-3">Información de Contacto</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <th style="width: 150px;">Teléfono:</th>
                                                    <td id="detalleTelefono"></td>
                                                </tr>
                                                <tr>
                                                    <th>Email:</th>
                                                    <td id="detalleEmail"></td>
                                                </tr>
                                                <tr>
                                                    <th>Dirección:</th>
                                                    <td id="detalleDireccion"></td>
                                                </tr>
                                                <tr>
                                                    <th>Fecha Registro:</th>
                                                    <td id="detalleFechaRegistro"></td>
                                                </tr>
                                                <tr>
                                                    <th>Estado:</th>
                                                    <td id="detalleEstado"></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab de Alergias -->
                        <div class="tab-pane fade" id="alergias-tab-pane" role="tabpanel" aria-labelledby="alergias-tab" tabindex="0">
                            <div id="listaAlergias" class="row">
                                <!-- Alergias se cargarán aquí dinámicamente -->
                            </div>
                        </div>
                        
                        <!-- Tab de Historial de Citas -->
                        <div class="tab-pane fade" id="historial-tab-pane" role="tabpanel" aria-labelledby="historial-tab" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Hora</th>
                                            <th>Especialidad</th>
                                            <th>Estado</th>
                                            <th>Diagnóstico</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaCitas">
                                        <!-- Citas se cargarán aquí dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Tab de Recetas -->
                        <div class="tab-pane fade" id="recetas-tab-pane" role="tabpanel" aria-labelledby="recetas-tab" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Especialidad</th>
                                            <th>Doctor</th>
                                            <th>Medicación</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaRecetas">
                                        <!-- Recetas se cargarán aquí dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btnImprimirHistoria">
                        <i class="fas fa-print me-1"></i> Imprimir Historia Clínica
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales
let idcolaborador = <?= $idcolaborador ?>;
let pacientesData = [];
let pacienteSeleccionado = null;

// Función para cargar los pacientes
function cargarPacientes() {
    const tipodoc = document.getElementById('tipodoc').value;
    const busqueda = document.getElementById('busqueda').value;
    const genero = document.getElementById('genero').value;
    
    // Mostrar loading
    document.getElementById('tbodyPacientes').innerHTML = '<tr><td colspan="8" class="text-center"><i class="fas fa-spinner fa-spin me-2"></i>Cargando pacientes...</td></tr>';
    
    // Construir URL
    let url = `<?= $host ?>/controllers/paciente.controller.php?operacion=listar_por_doctor&idcolaborador=${idcolaborador}`;
    
    if (tipodoc) {
        url += `&tipodoc=${encodeURIComponent(tipodoc)}`;
    }
    
    if (busqueda) {
        url += `&busqueda=${encodeURIComponent(busqueda)}`;
    }
    
    if (genero) {
        url += `&genero=${genero}`;
    }
    
    // Realizar petición
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.status && data.data && data.data.length > 0) {
                pacientesData = data.data;
                mostrarPacientesEnTabla(pacientesData);
            } else {
                document.getElementById('tbodyPacientes').innerHTML = '<tr><td colspan="8" class="text-center">No se encontraron pacientes con los filtros seleccionados</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error al cargar pacientes:', error);
            document.getElementById('tbodyPacientes').innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error al cargar los pacientes</td></tr>';
        });
}

// Función para mostrar pacientes en la tabla
function mostrarPacientesEnTabla(pacientes) {
    const tbody = document.getElementById('tbodyPacientes');
    tbody.innerHTML = '';
    
    pacientes.forEach((paciente, index) => {
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${paciente.apellidos}, ${paciente.nombres}</td>
            <td>${paciente.tipodoc}</td>
            <td>${paciente.nrodoc}</td>
            <td>${paciente.telefono || 'N/A'}</td>
            <td>${paciente.edad || 'N/A'}</td>
            <td>${formatearGenero(paciente.genero)}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-info" onclick="verDetallePaciente(${paciente.idpaciente})">
                    <i class="fas fa-eye me-1"></i> Ver
                </button>
                <button class="btn btn-sm btn-primary ms-1" onclick="agendarCita(${paciente.idpaciente})">
                    <i class="fas fa-calendar-plus me-1"></i> Agendar
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

// Función para formatear el género
function formatearGenero(genero) {
    switch (genero) {
        case 'M': return 'Masculino';
        case 'F': return 'Femenino';
        case 'OTRO': return 'Otro';
        default: return genero || 'No especificado';
    }
}

// Función para ver detalle de un paciente
function verDetallePaciente(idpaciente) {
    // Buscar el paciente en los datos
    pacienteSeleccionado = pacientesData.find(p => p.idpaciente == idpaciente);
    
    if (!pacienteSeleccionado) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró la información del paciente'
        });
        return;
    }
    
    // Rellenar datos personales
    document.getElementById('detalleNombre').textContent = `${pacienteSeleccionado.apellidos}, ${pacienteSeleccionado.nombres}`;
    document.getElementById('detalleTipoDoc').textContent = pacienteSeleccionado.tipodoc || 'No especificado';
    document.getElementById('detalleNroDoc').textContent = pacienteSeleccionado.nrodoc || 'No especificado';
    document.getElementById('detalleFechaNacimiento').textContent = formatearFecha(pacienteSeleccionado.fechanacimiento) || 'No especificado';
    document.getElementById('detalleEdad').textContent = pacienteSeleccionado.edad ? `${pacienteSeleccionado.edad} años` : 'No especificado';
    document.getElementById('detalleGenero').textContent = formatearGenero(pacienteSeleccionado.genero);
    
    // Rellenar datos de contacto
    document.getElementById('detalleTelefono').textContent = pacienteSeleccionado.telefono || 'No especificado';
    document.getElementById('detalleEmail').textContent = pacienteSeleccionado.email || 'No especificado';
    document.getElementById('detalleDireccion').textContent = pacienteSeleccionado.direccion || 'No especificado';
    document.getElementById('detalleFechaRegistro').textContent = formatearFecha(pacienteSeleccionado.fecharegistro) || 'No especificado';
    document.getElementById('detalleEstado').innerHTML = pacienteSeleccionado.estado_medico ? 
        `<span class="badge ${pacienteSeleccionado.estado_medico === 'Alta médica' ? 'bg-success' : 'bg-warning'}">${pacienteSeleccionado.estado_medico}</span>` : 
        '<span class="badge bg-secondary">Sin historia clínica</span>';
    
    // Cargar alergias
    cargarAlergiasDelPaciente(pacienteSeleccionado.idpersona);
    
    // Cargar historial de citas
    cargarHistorialCitas(idpaciente);
    
    // Cargar recetas
    cargarRecetas(idpaciente);
    
    // Configurar botón de impresión de historia
    document.getElementById('btnImprimirHistoria').onclick = function() {
        imprimirHistoriaClinica(idpaciente);
    };
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalDetallePaciente'));
    modal.show();
}

// Función para cargar las alergias del paciente
function cargarAlergiasDelPaciente(idpersona) {
    const listaAlergias = document.getElementById('listaAlergias');
    listaAlergias.innerHTML = '<div class="col-12 text-center"><i class="fas fa-spinner fa-spin me-2"></i>Cargando alergias...</div>';
    
    fetch(`<?= $host ?>/controllers/alergia.controller.php?operacion=listar_paciente&idpersona=${idpersona}`)
        .then(response => response.json())
        .then(data => {
            if (data.status && data.alergias && data.alergias.length > 0) {
                let html = '';
                
                data.alergias.forEach(alergia => {
                    const badgeClass = alergia.gravedad === 'GRAVE' ? 'bg-danger' : 
                                      (alergia.gravedad === 'MODERADA' ? 'bg-warning' : 'bg-info');
                    
                    html += `
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">${alergia.tipoalergia}</h6>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">${alergia.alergia}</p>
                                    <span class="badge ${badgeClass}">${alergia.gravedad}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                listaAlergias.innerHTML = html;
            } else {
                listaAlergias.innerHTML = '<div class="col-12 text-center">El paciente no tiene alergias registradas</div>';
            }
        })
        .catch(error => {
            console.error('Error al cargar alergias:', error);
            listaAlergias.innerHTML = '<div class="col-12 text-center text-danger">Error al cargar las alergias</div>';
        });
}

// Función para cargar el historial de citas
function cargarHistorialCitas(idpaciente) {
    const tablaCitas = document.getElementById('tablaCitas');
    tablaCitas.innerHTML = '<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin me-2"></i>Cargando historial...</td></tr>';
    
    fetch(`<?= $host ?>/controllers/cita.controller.php?op=historial_paciente&idpaciente=${idpaciente}`)
        .then(response => response.json())
        .then(data => {
            if (data.status && data.data && data.data.length > 0) {
                let html = '';
                
                data.data.forEach(cita => {
                    const badgeClass = getBadgeClass(cita.estado);
                    
                    html += `
                        <tr>
                            <td>${formatearFecha(cita.fecha)}</td>
                            <td>${formatearHora(cita.hora)}</td>
                            <td>${cita.especialidad}</td>
                            <td><span class="badge ${badgeClass}">${cita.estado}</span></td>
                            <td>${cita.diagnostico || 'No especificado'}</td>
                            <td>
                                ${getAccionesCita(cita)}
                            </td>
                        </tr>
                    `;
                });
                
                tablaCitas.innerHTML = html;
            } else {
                tablaCitas.innerHTML = '<tr><td colspan="6" class="text-center">El paciente no tiene citas registradas</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error al cargar historial de citas:', error);
            tablaCitas.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error al cargar el historial</td></tr>';
        });
}

// Función para cargar las recetas
function cargarRecetas(idpaciente) {
    const tablaRecetas = document.getElementById('tablaRecetas');
    tablaRecetas.innerHTML = '<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin me-2"></i>Cargando recetas...</td></tr>';
    
    fetch(`<?= $host ?>/controllers/receta.controller.php?op=listar_por_paciente&idpaciente=${idpaciente}`)
        .then(response => response.json())
        .then(data => {
            if (data.status && data.data && data.data.length > 0) {
                let html = '';
                
                data.data.forEach(receta => {
                    html += `
                        <tr>
                            <td>${formatearFecha(receta.fecha)}</td>
                            <td>${receta.especialidad}</td>
                            <td>${receta.doctor}</td>
                            <td>${receta.medicacion}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="imprimirReceta(${receta.idreceta})">
                                    <i class="fas fa-print me-1"></i> Imprimir
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                tablaRecetas.innerHTML = html;
            } else {
                tablaRecetas.innerHTML = '<tr><td colspan="5" class="text-center">El paciente no tiene recetas registradas</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error al cargar recetas:', error);
            tablaRecetas.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error al cargar las recetas</td></tr>';
        });
}

// Función para obtener acciones disponibles según el estado de la cita
function getAccionesCita(cita) {
    if (cita.estado === 'REALIZADA' && cita.idconsulta) {
        return `
            <button class="btn btn-sm btn-info" onclick="verDetalleCita(${cita.idcita})">
                <i class="fas fa-eye me-1"></i> Ver
            </button>
            <button class="btn btn-sm btn-primary ms-1" onclick="imprimirRecetaConsulta(${cita.idconsulta})">
                <i class="fas fa-print me-1"></i> Receta
            </button>
        `;
    } else if (cita.estado === 'PROGRAMADA') {
        return `
            <a href="<?= $host ?>/views/SesionDoctor/CitasMedicas/realizarCita.php?idcita=${cita.idcita}" class="btn btn-sm btn-success">
                <i class="fas fa-stethoscope me-1"></i> Atender
            </a>
        `;
    } else {
        return `
            <button class="btn btn-sm btn-secondary" disabled>
                <i class="fas fa-ban me-1"></i> No disponible
            </button>
        `;
    }
}

// Función para obtener la clase del badge según estado
function getBadgeClass(estado) {
    switch (estado) {
        case 'PROGRAMADA': return 'bg-primary';
        case 'REALIZADA': return 'bg-success';
        case 'CANCELADA': return 'bg-danger';
        case 'NO ASISTIO': return 'bg-warning';
        default: return 'bg-secondary';
    }
}

// Función para agendar una cita para el paciente
function agendarCita(idpaciente) {
    // Redirigir a la página de citas
    window.location.href = `<?= $host ?>/views/SesionDoctor/CitasMedicas/agendar_cita.php?idpaciente=${idpaciente}`;
}

// Función para ver detalle de una cita
function verDetalleCita(idcita) {
    // Redirigir a la página de detalles de cita
    window.location.href = `<?= $host ?>/views/SesionDoctor/CitasMedicas/verCita.php?idcita=${idcita}`;
}

// Función para imprimir receta por ID de receta
function imprimirReceta(idreceta) {
    // Abrir página de impresión en nueva ventana
    window.open(`<?= $host ?>/views/SesionDoctor/CitasMedicas/imprimir_receta.php?idreceta=${idreceta}`, '_blank');
}

// Función para imprimir receta por ID de consulta
function imprimirRecetaConsulta(idconsulta) {
    // Abrir página de impresión en nueva ventana
    window.open(`<?= $host ?>/views/SesionDoctor/CitasMedicas/imprimir_receta.php?idconsulta=${idconsulta}`, '_blank');
}

// Función para imprimir la historia clínica
function imprimirHistoriaClinica(idpaciente) {
    // Abrir página de impresión en nueva ventana
    window.open(`<?= $host ?>/views/SesionDoctor/Pacientes/imprimir_historia.php?idpaciente=${idpaciente}`, '_blank');
}

// Función para exportar a Excel
function exportarExcel() {
    const tipodoc = document.getElementById('tipodoc').value;
    const busqueda = document.getElementById('busqueda').value;
    const genero = document.getElementById('genero').value;
    
    // Construir URL
    let url = `<?= $host ?>/controllers/reporte.controller.php?op=pacientes_excel&idcolaborador=${idcolaborador}`;
    
    if (tipodoc) {
        url += `&tipodoc=${encodeURIComponent(tipodoc)}`;
    }
    
    if (busqueda) {
        url += `&busqueda=${encodeURIComponent(busqueda)}`;
    }
    
    if (genero) {
        url += `&genero=${genero}`;
    }
    
    // Abrir en nueva ventana
    window.open(url, '_blank');
}

// Función para exportar a PDF
function exportarPDF() {
    const tipodoc = document.getElementById('tipodoc').value;
    const busqueda = document.getElementById('busqueda').value;
    const genero = document.getElementById('genero').value;
    
    // Construir URL
    let url = `<?= $host ?>/controllers/reporte.controller.php?op=pacientes_pdf&idcolaborador=${idcolaborador}`;
    
    if (tipodoc) {
        url += `&tipodoc=${encodeURIComponent(tipodoc)}`;
    }
    
    if (busqueda) {
        url += `&busqueda=${encodeURIComponent(busqueda)}`;
    }
    
    if (genero) {
        url += `&genero=${genero}`;
    }
    
    // Abrir en nueva ventana
    window.open(url, '_blank');
}

// Formatear hora (HH:MM)
function formatearHora(hora) {
    if (!hora) return '';
    
    // Extraer solo HH:MM
    const partes = hora.split(':');
    if (partes.length >= 2) {
        return `${partes[0]}:${partes[1]}`;
    }
    
    return hora;
}

// Formatear fecha (DD/MM/YYYY)
function formatearFecha(fecha) {
    if (!fecha) return '';
    
    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const anio = date.getFullYear();
    
    return `${dia}/${mes}/${anio}`;
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Cargar pacientes iniciales
    cargarPacientes();
    
    // Evento para filtrar pacientes
    document.getElementById('btnFiltrar').addEventListener('click', cargarPacientes);
    
    // Evento para exportar a Excel
    document.getElementById('btnReporteExcel').addEventListener('click', exportarExcel);
    
    // Evento para exportar a PDF
    document.getElementById('btnReportePDF').addEventListener('click', exportarPDF);
});
</script>

<?php include_once "../../include/footer.php"; ?>