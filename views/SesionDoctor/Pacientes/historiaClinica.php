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
    <h1 class="mt-4">Historias Clínicas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= $host ?>/views/include/dashboard.doctor.php">Panel de Control</a></li>
        <li class="breadcrumb-item">Pacientes</li>
        <li class="breadcrumb-item active">Historias Clínicas</li>
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
                        <label for="estado" class="form-label">Estado Médico:</label>
                        <select class="form-select" id="estado">
                            <option value="">Todos</option>
                            <option value="alta">Alta Médica</option>
                            <option value="tratamiento">En Tratamiento</option>
                            <option value="sin_historia">Sin Historia</option>
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

    <!-- Lista de historias clínicas -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-file-medical me-1"></i> Historias Clínicas
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
                <table class="table table-hover table-striped" id="tablaHistorias">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Paciente</th>
                            <th>Documento</th>
                            <th>Edad</th>
                            <th>Diagnóstico</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyHistorias">
                        <!-- Historias se cargarán aquí dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalle de la historia clínica -->
    <div class="modal fade" id="modalDetalleHistoria" tabindex="-1" aria-labelledby="modalDetalleHistoriaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalDetalleHistoriaLabel">Historia Clínica</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Información del paciente -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Información del Paciente</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th style="width: 150px;">Nombre:</th>
                                            <td id="detalleNombre"></td>
                                        </tr>
                                        <tr>
                                            <th>Documento:</th>
                                            <td id="detalleDocumento"></td>
                                        </tr>
                                        <tr>
                                            <th>Fecha Nacimiento:</th>
                                            <td id="detalleFechaNacimiento"></td>
                                        </tr>
                                        <tr>
                                            <th>Edad:</th>
                                            <td id="detalleEdad"></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
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
                                            <th>Estado:</th>
                                            <td id="detalleEstado"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Diagnósticos y evolución -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Diagnóstico y Evolución</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Enfermedad Actual:</label>
                                        <div id="detalleEnfermedadActual" class="p-2 border rounded"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Examen Físico:</label>
                                        <div id="detalleExamenFisico" class="p-2 border rounded"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Diagnóstico:</label>
                                        <div id="detalleDiagnostico" class="p-2 border rounded"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Evolución:</label>
                                        <div id="detalleEvolucion" class="p-2 border rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alergias -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Alergias del Paciente</h6>
                        </div>
                        <div class="card-body">
                            <div id="detalleAlergias" class="row">
                                <!-- Alergias se cargarán aquí dinámicamente -->
                            </div>
                        </div>
                    </div>

                    <!-- Historial de diagnósticos -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Historial de Diagnósticos</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Diagnóstico</th>
                                            <th>Código</th>
                                            <th>Doctor</th>
                                            <th>Especialidad</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detalleDiagnosticos">
                                        <!-- Diagnósticos se cargarán aquí dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Historial de recetas -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Historial de Recetas</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Medicación</th>
                                            <th>Cantidad</th>
                                            <th>Frecuencia</th>
                                            <th>Doctor</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detalleRecetas">
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
let historiasData = [];

// Función para cargar las historias clínicas
function cargarHistoriasClinicas() {
    const tipodoc = document.getElementById('tipodoc').value;
    const busqueda = document.getElementById('busqueda').value;
    const estado = document.getElementById('estado').value;
    
    // Mostrar loading
    document.getElementById('tbodyHistorias').innerHTML = '<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin me-2"></i>Cargando historias clínicas...</td></tr>';
    
    // Construir URL
    let url = `<?= $host ?>/controllers/historiaclinica.controller.php?op=listar`;
    
    // Para filtrar solo por pacientes atendidos por este doctor
    url += `&idcolaborador=${idcolaborador}`;
    
    if (tipodoc) {
        url += `&tipodoc=${encodeURIComponent(tipodoc)}`;
    }
    
    if (busqueda) {
        url += `&busqueda=${encodeURIComponent(busqueda)}`;
    }
    
    if (estado) {
        url += `&estado=${estado}`;
    }
    
    // Realizar petición
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.status && data.data && data.data.length > 0) {
                historiasData = data.data;
                mostrarHistoriasEnTabla(historiasData);
            } else {
                document.getElementById('tbodyHistorias').innerHTML = '<tr><td colspan="7" class="text-center">No se encontraron historias clínicas con los filtros seleccionados</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error al cargar historias clínicas:', error);
            document.getElementById('tbodyHistorias').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error al cargar las historias clínicas</td></tr>';
        });
}

// Función para mostrar historias en la tabla
function mostrarHistoriasEnTabla(historias) {
    const tbody = document.getElementById('tbodyHistorias');
    tbody.innerHTML = '';
    
    historias.forEach((historia, index) => {
        // Determinar clase según estado
        let rowClass = '';
        if (historia.altamedica == 1) {
            rowClass = 'table-success';
        } else if (historia.altamedica == 0) {
            rowClass = 'table-warning';
        }
        
        const row = document.createElement('tr');
        row.className = rowClass;
        
        // Preparar estado para mostrar
        let estadoHtml;
        if (historia.altamedica == 1) {
            estadoHtml = '<span class="badge bg-success">Alta Médica</span>';
        } else if (historia.altamedica == 0) {
            estadoHtml = '<span class="badge bg-warning">En Tratamiento</span>';
        } else {
            estadoHtml = '<span class="badge bg-secondary">Sin Historia</span>';
        }
        
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${historia.apellidos}, ${historia.nombres}</td>
            <td>${historia.tipodoc}: ${historia.nrodoc}</td>
            <td>${historia.edad || 'N/A'}</td>
            <td>${historia.diagnostico || 'No especificado'}</td>
            <td>${estadoHtml}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-info" onclick="verDetalleHistoria(${historia.idhistoriaclinica})">
                    <i class="fas fa-eye me-1"></i> Ver
                </button>
                <button class="btn btn-sm btn-primary ms-1" onclick="imprimirHistoria(${historia.idhistoriaclinica})">
                    <i class="fas fa-print me-1"></i> Imprimir
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

// Función para ver detalle de la historia clínica
function verDetalleHistoria(idpaciente) {
    // Obtener datos completos de la historia clínica
    fetch(`<?= $host ?>/controllers/historiaclinica.controller.php?op=impresion&idpaciente=${idpaciente}`)
        .then(response => response.json())
        .then(data => {
            if (data.status && data.data) {
                const historiaData = data.data;
                const paciente = historiaData.paciente;
                const historia = historiaData.historia;
                
                // Información del paciente
                document.getElementById('detalleNombre').textContent = `${paciente.apellidos}, ${paciente.nombres}`;
                document.getElementById('detalleDocumento').textContent = `${paciente.tipodoc}: ${paciente.nrodoc}`;
                document.getElementById('detalleFechaNacimiento').textContent = formatearFecha(paciente.fechanacimiento) || 'No especificado';
                document.getElementById('detalleEdad').textContent = paciente.edad ? `${paciente.edad} años` : 'No especificado';
                document.getElementById('detalleTelefono').textContent = paciente.telefono || 'No especificado';
                document.getElementById('detalleEmail').textContent = paciente.email || 'No especificado';
                document.getElementById('detalleDireccion').textContent = paciente.direccion || 'No especificado';
                
                // Estado médico
                let estadoHtml = '';
                if (historia && historia.altamedica == 1) {
                    estadoHtml = '<span class="badge bg-success">Alta Médica</span>';
                } else if (historia && historia.altamedica == 0) {
                    estadoHtml = '<span class="badge bg-warning">En Tratamiento</span>';
                } else {
                    estadoHtml = '<span class="badge bg-secondary">Sin Historia</span>';
                }
                document.getElementById('detalleEstado').innerHTML = estadoHtml;
                
                // Diagnóstico y evolución
                document.getElementById('detalleEnfermedadActual').textContent = historia ? historia.enfermedadactual : 'No hay información';
                document.getElementById('detalleExamenFisico').textContent = historia ? historia.examenfisico : 'No hay información';
                
                // Obtener el último diagnóstico
                let diagnosticoTexto = 'No hay diagnóstico registrado';
                if (historiaData.diagnosticos && historiaData.diagnosticos.length > 0) {
                    diagnosticoTexto = historiaData.diagnosticos[0].diagnostico + 
                                      (historiaData.diagnosticos[0].detalle_diagnostico ? 
                                      ' - ' + historiaData.diagnosticos[0].detalle_diagnostico : '');
                }
                document.getElementById('detalleDiagnostico').textContent = diagnosticoTexto;
                
                document.getElementById('detalleEvolucion').textContent = historia ? historia.evolucion : 'No hay información';
                
                // Alergias
                const detalleAlergias = document.getElementById('detalleAlergias');
                if (historiaData.alergias && historiaData.alergias.length > 0) {
                    let alergiasHtml = '';
                    
                    historiaData.alergias.forEach(alergia => {
                        const badgeClass = alergia.gravedad === 'GRAVE' ? 'bg-danger' : 
                                          (alergia.gravedad === 'MODERADA' ? 'bg-warning' : 'bg-info');
                        
                        alergiasHtml += `
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
                    
                    detalleAlergias.innerHTML = alergiasHtml;
                } else {
                    detalleAlergias.innerHTML = '<div class="col-12 text-center">El paciente no tiene alergias registradas</div>';
                }
                
                // Historial de diagnósticos
                const detalleDiagnosticos = document.getElementById('detalleDiagnosticos');
                if (historiaData.diagnosticos && historiaData.diagnosticos.length > 0) {
                    let diagnosticosHtml = '';
                    
                    historiaData.diagnosticos.forEach(diagnostico => {
                        diagnosticosHtml += `
                            <tr>
                                <td>${formatearFecha(diagnostico.fecha)}</td>
                                <td>${diagnostico.diagnostico}</td>
                                <td>${diagnostico.codigo_diagnostico || 'N/A'}</td>
                                <td>${diagnostico.doctor}</td>
                                <td>${diagnostico.especialidad}</td>
                            </tr>
                        `;
                    });
                    
                    detalleDiagnosticos.innerHTML = diagnosticosHtml;
                } else {
                    detalleDiagnosticos.innerHTML = '<tr><td colspan="5" class="text-center">No hay diagnósticos registrados</td></tr>';
                }
                
                // Historial de recetas
                const detalleRecetas = document.getElementById('detalleRecetas');
                if (historiaData.recetas && historiaData.recetas.length > 0) {
                    let recetasHtml = '';
                    
                    historiaData.recetas.forEach(receta => {
                        recetasHtml += `
                            <tr>
                                <td>${formatearFecha(receta.fecha)}</td>
                                <td>${receta.medicacion}</td>
                                <td>${receta.cantidad || 'N/A'}</td>
                                <td>${receta.frecuencia || 'N/A'}</td>
                                <td>${receta.doctor}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="imprimirReceta(${receta.idreceta})">
                                        <i class="fas fa-print me-1"></i> Imprimir
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    detalleRecetas.innerHTML = recetasHtml;
                } else {
                    detalleRecetas.innerHTML = '<tr><td colspan="6" class="text-center">No hay recetas registradas</td></tr>';
                }
                
                // Configurar botón de impresión
                document.getElementById('btnImprimirHistoria').onclick = function() {
                    imprimirHistoria(idpaciente);
                };
                
                // Mostrar modal
                const modal = new bootstrap.Modal(document.getElementById('modalDetalleHistoria'));
                modal.show();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la historia clínica'
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar detalle de la historia:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar los detalles de la historia clínica'
            });
        });
}

// Función para imprimir la historia clínica
function imprimirHistoria(idpaciente) {
    // Abrir página de impresión en nueva ventana
    window.open(`<?= $host ?>/views/SesionDoctor/Pacientes/imprimir_historia.php?idpaciente=${idpaciente}`, '_blank');
}

// Función para imprimir una receta
function imprimirReceta(idreceta) {
    // Abrir página de impresión en nueva ventana
    window.open(`<?= $host ?>/views/SesionDoctor/CitasMedicas/imprimir_receta.php?idreceta=${idreceta}`, '_blank');
}

// Función para exportar a Excel
function exportarExcel() {
    const tipodoc = document.getElementById('tipodoc').value;
    const busqueda = document.getElementById('busqueda').value;
    const estado = document.getElementById('estado').value;
    
    // Construir URL
    let url = `<?= $host ?>/controllers/reporte.controller.php?op=historias_excel&idcolaborador=${idcolaborador}`;
    
    if (tipodoc) {
        url += `&tipodoc=${encodeURIComponent(tipodoc)}`;
    }
    
    if (busqueda) {
        url += `&busqueda=${encodeURIComponent(busqueda)}`;
    }
    
    if (estado) {
        url += `&estado=${estado}`;
    }
    
    // Abrir en nueva ventana
    window.open(url, '_blank');
}

// Función para exportar a PDF
function exportarPDF() {
    const tipodoc = document.getElementById('tipodoc').value;
    const busqueda = document.getElementById('busqueda').value;
    const estado = document.getElementById('estado').value;
    
    // Construir URL
    let url = `<?= $host ?>/controllers/reporte.controller.php?op=historias_pdf&idcolaborador=${idcolaborador}`;
    
    if (tipodoc) {
        url += `&tipodoc=${encodeURIComponent(tipodoc)}`;
    }
    
    if (busqueda) {
        url += `&busqueda=${encodeURIComponent(busqueda)}`;
    }
    
    if (estado) {
        url += `&estado=${estado}`;
    }
    
    // Abrir en nueva ventana
    window.open(url, '_blank');
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
    // Cargar historias iniciales
    cargarHistoriasClinicas();
    
    // Evento para filtrar historias
    document.getElementById('btnFiltrar').addEventListener('click', cargarHistoriasClinicas);
    
    // Evento para exportar a Excel
    document.getElementById('btnReporteExcel').addEventListener('click', exportarExcel);
    
    // Evento para exportar a PDF
    document.getElementById('btnReportePDF').addEventListener('click', exportarPDF);
});
</script>

<?php include_once "../../include/footer.php"; ?>