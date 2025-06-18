<?php
require_once '../include/header.administrador.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Consultas Médicas</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <style>
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
        }
        
        .card-title {
            margin-bottom: 0;
            font-weight: 600;
        }
        
        .btn-circle {
            width: 36px;
            height: 36px;
            padding: 0;
            border-radius: 50%;
            text-align: center;
            line-height: 36px;
        }
        
        .status-badge {
            font-size: 0.8rem;
            padding: 5px 8px;
        }
        
        .form-label {
            font-weight: 500;
        }
        
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
        }
        
        .nav-pills .nav-link {
            color: #495057;
        }
        
        .nav-pills .nav-link.active {
            color: #fff;
        }
        
        .alert-banner {
            border-left: 4px solid;
            background-color: #f8f9fa;
            padding: 12px;
            margin-bottom: 20px;
        }
        
        .alert-banner.info {
            border-left-color: #0d6efd;
        }
        
        .alert-banner.warning {
            border-left-color: #ffc107;
        }
        
        .alert-banner.danger {
            border-left-color: #dc3545;
        }
        
        .alert-banner.success {
            border-left-color: #198754;
        }
        
        /* Loading spinner */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            display: none;
        }
        
        .file-upload-wrapper {
            position: relative;
            width: 100%;
            height: 60px;
        }
        
        .file-upload-wrapper:after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            background: #fff;
            padding: 10px 15px;
            display: block;
            width: calc(100% - 40px);
            pointer-events: none;
            z-index: 20;
            height: 40px;
            line-height: 20px;
            border: 1px solid #ced4da;
            border-radius: 5px 0 0 5px;
            color: #6c757d;
        }
        
        .file-upload-wrapper:before {
            content: 'Seleccionar';
            position: absolute;
            top: 0;
            right: 0;
            display: inline-block;
            height: 40px;
            background: #007bff;
            color: #fff;
            font-weight: 700;
            z-index: 25;
            padding: 10px 15px;
            text-align: center;
            line-height: 20px;
            border-radius: 0 5px 5px 0;
            pointer-events: none;
        }
        
        .file-upload-wrapper input {
            opacity: 0;
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 99;
            height: 40px;
            margin: 0;
            padding: 0;
            display: block;
            cursor: pointer;
            width: 100%;
        }
        
        /* Estilos para las pestañas */
        .custom-tab-content {
            padding: 20px;
            border: 1px solid #dee2e6;
            border-top: 0;
            border-radius: 0 0 8px 8px;
        }
        
        /* Estilos para los campos de formulario readonly */
        .form-control[readonly]:not(.date-picker) {
            background-color: #f8f9fa;
            opacity: 1;
        }
        
        /* Estilos para las tablas dentro de los modales */
        .table-responsive.modal-table {
            max-height: 300px;
            overflow-y: auto;
        }
        
        /* Estilos para los tooltips */
        .tooltip-inner {
            max-width: 200px;
            padding: 6px 10px;
            color: #fff;
            text-align: center;
            background-color: #000;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title"><i class="fas fa-stethoscope me-2"></i>Gestión de Consultas Médicas</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaConsulta">
                            <i class="fas fa-plus me-2"></i>Nueva Consulta
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Filtros de búsqueda -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <label for="filtroFechaInicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="filtroFechaInicio">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="filtroFechaFin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="filtroFechaFin">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="filtroEspecialidad" class="form-label">Especialidad</label>
                                <select class="form-select" id="filtroEspecialidad">
                                    <option value="">Todas</option>
                                    <!-- Se cargará dinámicamente -->
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="filtroDoctor" class="form-label">Doctor</label>
                                <select class="form-select" id="filtroDoctor">
                                    <option value="">Todos</option>
                                    <!-- Se cargará dinámicamente -->
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="filtroPaciente" class="form-label">Nro. Documento Paciente</label>
                                <input type="text" class="form-control" id="filtroPaciente" placeholder="Buscar por documento">
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button type="button" class="btn btn-primary w-100" id="btnFiltrar">
                                    <i class="fas fa-search me-2"></i>Filtrar
                                </button>
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button type="button" class="btn btn-secondary w-100" id="btnLimpiarFiltros">
                                    <i class="fas fa-eraser me-2"></i>Limpiar Filtros
                                </button>
                            </div>
                        </div>
                        
                        <!-- Tabla de consultas -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tablaConsultas">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Paciente</th>
                                        <th>Doctor</th>
                                        <th>Especialidad</th>
                                        <th>Diagnóstico</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Se cargará dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva Consulta -->
    <div class="modal fade" id="modalNuevaConsulta" tabindex="-1" aria-labelledby="modalNuevaConsultaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalNuevaConsultaLabel">Registrar Nueva Consulta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevaConsulta">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nuevaConsultaEspecialidad" class="form-label">Especialidad</label>
                                <select class="form-select" id="nuevaConsultaEspecialidad" name="idespecialidad" required>
                                    <option value="">Seleccione Especialidad</option>
                                    <!-- Se cargará dinámicamente -->
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nuevaConsultaDoctor" class="form-label">Doctor</label>
                                <select class="form-select" id="nuevaConsultaDoctor" name="iddoctor" required>
                                    <option value="">Seleccione Doctor</option>
                                    <!-- Se cargará dinámicamente -->
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nuevaConsultaFecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="nuevaConsultaFecha" name="fecha" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nuevaConsultaHorario" class="form-label">Horario</label>
                                <select class="form-select" id="nuevaConsultaHorario" name="idhorario" required>
                                    <option value="">Seleccione Horario</option>
                                    <!-- Se cargará dinámicamente -->
                                </select>
                            </div>
                        </div>
                        
                        <h6 class="mb-3 mt-4">Datos del Paciente</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nuevaConsultaDocumentoPaciente" class="form-label">Documento del Paciente</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="nuevaConsultaDocumentoPaciente" placeholder="Nro. Documento" required>
                                    <button class="btn btn-outline-secondary" type="button" id="btnBuscarPaciente">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nuevaConsultaNombrePaciente" class="form-label">Nombre del Paciente</label>
                                <input type="text" class="form-control" id="nuevaConsultaNombrePaciente" readonly>
                                <input type="hidden" id="nuevaConsultaIdPaciente" name="idpaciente">
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3" role="alert">
                            <i class="fas fa-info-circle me-2"></i>Si el paciente no está registrado, primero debe registrarlo en el módulo de Pacientes.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarNuevaConsulta">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Ver/Editar Consulta -->
    <div class="modal fade" id="modalDetalleConsulta" tabindex="-1" aria-labelledby="modalDetalleConsultaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalDetalleConsultaLabel">Detalles de la Consulta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Pestañas para organizar la información -->
                    <ul class="nav nav-tabs nav-pills mb-3" id="consultaTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="info" aria-selected="true">Información General</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="triaje-tab" data-bs-toggle="tab" data-bs-target="#triaje" type="button" role="tab" aria-controls="triaje" aria-selected="false">Triaje</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="diagnostico-tab" data-bs-toggle="tab" data-bs-target="#diagnostico" type="button" role="tab" aria-controls="diagnostico" aria-selected="false">Diagnóstico</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="receta-tab" data-bs-toggle="tab" data-bs-target="#receta" type="button" role="tab" aria-controls="receta" aria-selected="false">Receta</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="servicios-tab" data-bs-toggle="tab" data-bs-target="#servicios" type="button" role="tab" aria-controls="servicios" aria-selected="false">Servicios</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="historiaclinica-tab" data-bs-toggle="tab" data-bs-target="#historiaclinica" type="button" role="tab" aria-controls="historiaclinica" aria-selected="false">Historia Clínica</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content custom-tab-content" id="consultaTabsContent">
                        <!-- Pestaña de Información General -->
                        <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                            <h6 class="mb-3">Información de la Consulta</h6>
                            <form id="formInfoConsulta">
                                <input type="hidden" id="detalleConsultaId" name="idconsulta">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleConsultaFecha" class="form-label">Fecha</label>
                                        <input type="date" class="form-control" id="detalleConsultaFecha" name="fecha" readonly>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleConsultaHoraProgramada" class="form-label">Hora Programada</label>
                                        <input type="time" class="form-control" id="detalleConsultaHoraProgramada" readonly>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleConsultaHoraAtencion" class="form-label">Hora de Atención</label>
                                        <input type="time" class="form-control" id="detalleConsultaHoraAtencion" name="horaatencion">
                                    </div>
                                </div>
                                
                                <h6 class="mb-3 mt-4">Datos del Paciente</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleConsultaPacienteDoc" class="form-label">Documento</label>
                                        <input type="text" class="form-control" id="detalleConsultaPacienteDoc" readonly>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleConsultaPacienteNombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="detalleConsultaPacienteNombre" readonly>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleConsultaPacienteEdad" class="form-label">Edad</label>
                                        <input type="text" class="form-control" id="detalleConsultaPacienteEdad" readonly>
                                    </div>
                                </div>
                                
                                <h6 class="mb-3 mt-4">Datos del Doctor</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleConsultaDoctorNombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="detalleConsultaDoctorNombre" readonly>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleConsultaEspecialidad" class="form-label">Especialidad</label>
                                        <input type="text" class="form-control" id="detalleConsultaEspecialidad" readonly>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleConsultaCondicionPaciente" class="form-label">Condición del Paciente</label>
                                        <select class="form-select" id="detalleConsultaCondicionPaciente" name="condicionpaciente">
                                            <option value="ESTABLE">Estable</option>
                                            <option value="REGULAR">Regular</option>
                                            <option value="GRAVE">Grave</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-primary" id="btnActualizarInfoConsulta">
                                        <i class="fas fa-save me-2"></i>Actualizar Información
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Pestaña de Triaje -->
                        <div class="tab-pane fade" id="triaje" role="tabpanel" aria-labelledby="triaje-tab">
                            <form id="formTriaje">
                                <input type="hidden" id="detalleTriajeIdConsulta" name="idconsulta">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleTriajeEnfermera" class="form-label">Enfermera</label>
                                        <select class="form-select" id="detalleTriajeEnfermera" name="idenfermera" required>
                                            <option value="">Seleccione Enfermera</option>
                                            <!-- Se cargará dinámicamente -->
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleTriajeHora" class="form-label">Hora</label>
                                        <input type="time" class="form-control" id="detalleTriajeHora" name="hora" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleTriajeTemperatura" class="form-label">Temperatura (°C)</label>
                                        <input type="number" step="0.1" class="form-control" id="detalleTriajeTemperatura" name="temperatura" placeholder="36.5">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleTriajePresion" class="form-label">Presión Arterial</label>
                                        <input type="text" class="form-control" id="detalleTriajePresion" name="presionarterial" placeholder="120/80">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleTriajeFrecuenciaCardiaca" class="form-label">Frecuencia Cardíaca</label>
                                        <input type="number" class="form-control" id="detalleTriajeFrecuenciaCardiaca" name="frecuenciacardiaca" placeholder="80">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleTriajeSaturacion" class="form-label">Saturación O2 (%)</label>
                                        <input type="number" class="form-control" id="detalleTriajeSaturacion" name="saturacionoxigeno" placeholder="98">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleTriajePeso" class="form-label">Peso (kg)</label>
                                        <input type="number" step="0.1" class="form-control" id="detalleTriajePeso" name="peso" placeholder="70.5">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleTriajeEstatura" class="form-label">Estatura (cm)</label>
                                        <input type="number" step="0.1" class="form-control" id="detalleTriajeEstatura" name="estatura" placeholder="170">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="detalleTriajeIMC" class="form-label">IMC</label>
                                        <input type="text" class="form-control" id="detalleTriajeIMC" readonly>
                                        <small id="detalleTriajeIMCClasificacion" class="form-text text-muted"></small>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-primary" id="btnGuardarTriaje">
                                        <i class="fas fa-save me-2"></i>Guardar Triaje
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Pestaña de Diagnóstico -->
                        <div class="tab-pane fade" id="diagnostico" role="tabpanel" aria-labelledby="diagnostico-tab">
                            <form id="formDiagnostico">
                                <input type="hidden" id="detalleDiagnosticoIdConsulta" name="idconsulta">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="detalleDiagnosticoSelect" class="form-label">Diagnóstico</label>
                                        <select class="form-select" id="detalleDiagnosticoSelect" name="iddiagnostico" required>
                                            <option value="">Seleccione Diagnóstico</option>
                                            <!-- Se cargará dinámicamente -->
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label for="detalleDiagnosticoDescripcion" class="form-label">Descripción</label>
                                        <textarea class="form-control" id="detalleDiagnosticoDescripcion" rows="3" readonly></textarea>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-primary" id="btnGuardarDiagnostico">
                                        <i class="fas fa-save me-2"></i>Guardar Diagnóstico
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Pestaña de Receta -->
                        <div class="tab-pane fade" id="receta" role="tabpanel" aria-labelledby="receta-tab">
                            <form id="formReceta">
                                <input type="hidden" id="detalleRecetaIdConsulta" name="idconsulta">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="detalleRecetaMedicacion" class="form-label">Medicación</label>
                                        <textarea class="form-control" id="detalleRecetaMedicacion" name="medicacion" rows="3" placeholder="Detalle de la medicación" required></textarea>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="detalleRecetaCantidad" class="form-label">Cantidad</label>
                                        <input type="text" class="form-control" id="detalleRecetaCantidad" name="cantidad" placeholder="Ej: 30 tabletas" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="detalleRecetaFrecuencia" class="form-label">Frecuencia</label>
                                        <input type="text" class="form-control" id="detalleRecetaFrecuencia" name="frecuencia" placeholder="Ej: Cada 8 horas" required>
                                    </div>
                                </div>
                                
                                <h6 class="mb-3 mt-4">Tratamiento</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="detalleTratamientoMedicacion" class="form-label">Medicación</label>
                                        <input type="text" class="form-control" id="detalleTratamientoMedicacion" name="tratamiento[medicacion]" placeholder="Nombre del medicamento">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="detalleTratamientoDosis" class="form-label">Dosis</label>
                                        <input type="text" class="form-control" id="detalleTratamientoDosis" name="tratamiento[dosis]" placeholder="Ej: 500mg">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="detalleTratamientoFrecuencia" class="form-label">Frecuencia</label>
                                        <input type="text" class="form-control" id="detalleTratamientoFrecuencia" name="tratamiento[frecuencia]" placeholder="Ej: Cada 8 horas">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="detalleTratamientoDuracion" class="form-label">Duración</label>
                                        <input type="text" class="form-control" id="detalleTratamientoDuracion" name="tratamiento[duracion]" placeholder="Ej: 7 días">
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-primary" id="btnGuardarReceta">
                                        <i class="fas fa-save me-2"></i>Guardar Receta
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Pestaña de Servicios -->
                        <div class="tab-pane fade" id="servicios" role="tabpanel" aria-labelledby="servicios-tab">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-primary" id="btnNuevoServicio">
                                    <i class="fas fa-plus me-2"></i>Solicitar Nuevo Servicio
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped" id="tablaServicios">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tipo de Servicio</th>
                                            <th>Servicio</th>
                                            <th>Solicitud</th>
                                            <th>Fecha Entrega</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Se cargará dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Pestaña de Historia Clínica -->
                        <div class="tab-pane fade" id="historiaclinica" role="tabpanel" aria-labelledby="historiaclinica-tab">
                            <form id="formHistoriaClinica">
                                <input type="hidden" id="detalleHistoriaIdPaciente" name="idpaciente">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="detalleHistoriaAntecedentes" class="form-label">Antecedentes Personales</label>
                                        <textarea class="form-control" id="detalleHistoriaAntecedentes" name="antecedentepersonales" rows="3"></textarea>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="detalleHistoriaEnfermedad" class="form-label">Enfermedad Actual</label>
                                        <textarea class="form-control" id="detalleHistoriaEnfermedad" name="enfermedadactual" rows="3"></textarea>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="detalleHistoriaExamen" class="form-label">Examen Físico</label>
                                        <textarea class="form-control" id="detalleHistoriaExamen" name="examenfisico" rows="3"></textarea>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="detalleHistoriaEvolucion" class="form-label">Evolución</label>
                                        <textarea class="form-control" id="detalleHistoriaEvolucion" name="evolucion" rows="3"></textarea>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="detalleHistoriaAlta" name="altamedica" value="1">
                                            <label class="form-check-label" for="detalleHistoriaAlta">
                                                Alta Médica
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-primary" id="btnGuardarHistoria">
                                        <i class="fas fa-save me-2"></i>Guardar Historia Clínica
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" id="btnImprimirConsulta">
                        <i class="fas fa-print me-2"></i>Imprimir Consulta
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Solicitar Servicio -->
    <div class="modal fade" id="modalNuevoServicio" tabindex="-1" aria-labelledby="modalNuevoServicioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalNuevoServicioLabel">Solicitar Nuevo Servicio</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoServicio">
                        <input type="hidden" id="nuevoServicioIdConsulta" name="idconsulta">
                        <div class="mb-3">
                            <label for="nuevoServicioTipo" class="form-label">Tipo de Servicio</label>
                            <select class="form-select" id="nuevoServicioTipo" name="idtiposervicio" required>
                                <option value="">Seleccione Tipo de Servicio</option>
                                <!-- Se cargará dinámicamente -->
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nuevoServicioSolicitud" class="form-label">Solicitud</label>
                            <textarea class="form-control" id="nuevoServicioSolicitud" name="solicitud" rows="3" placeholder="Detalles de la solicitud" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nuevoServicioFechaAnalisis" class="form-label">Fecha de Análisis</label>
                                <input type="date" class="form-control" id="nuevoServicioFechaAnalisis" name="fechaanalisis">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nuevoServicioFechaEntrega" class="form-label">Fecha de Entrega</label>
                                <input type="date" class="form-control" id="nuevoServicioFechaEntrega" name="fechaentrega">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarServicio">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Registrar Resultado de Servicio -->
    <div class="modal fade" id="modalResultadoServicio" tabindex="-1" aria-labelledby="modalResultadoServicioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalResultadoServicioLabel">Registrar Resultado de Servicio</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formResultadoServicio">
                        <input type="hidden" id="resultadoServicioId" name="idserviciorequerido">
                        <div class="mb-3">
                            <label for="resultadoServicioCaracteristica" class="form-label">Característica Evaluada</label>
                            <input type="text" class="form-control" id="resultadoServicioCaracteristica" name="caracteristicaevaluada" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="resultadoServicioCondicion" class="form-label">Condición</label>
                            <textarea class="form-control" id="resultadoServicioCondicion" name="condicion" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="resultadoServicioImagen" class="form-label">Imagen (opcional)</label>
                            <div class="file-upload-wrapper" data-text="Seleccionar archivo">
                                <input type="file" class="file-upload-field" id="resultadoServicioImagen" name="imagen" accept="image/*">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarResultado">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Resultados de Servicio -->
    <div class="modal fade" id="modalVerResultados" tabindex="-1" aria-labelledby="modalVerResultadosLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalVerResultadosLabel">Resultados del Servicio</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-4" id="verResultadosInfo">
                        <strong>Servicio:</strong> <span id="verResultadosServicio"></span><br>
                        <strong>Solicitud:</strong> <span id="verResultadosSolicitud"></span>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped" id="tablaResultados">
                            <thead>
                                <tr>
                                    <th>Característica</th>
                                    <th>Condición</th>
                                    <th>Imagen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se cargará dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btnNuevoResultado">
                        <i class="fas fa-plus me-2"></i>Agregar Resultado
                    </button>
                    <button type="button" class="btn btn-success" id="btnImprimirResultados">
                        <i class="fas fa-print me-2"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts necesarios -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.min.js"></script>
    
    <!-- Script para el módulo de consultas -->
    <script src="../../js/consultas.js"></script>
</body>
</html>