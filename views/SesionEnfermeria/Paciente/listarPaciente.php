<?php
// Incluir el encabezado del doctor que ya tiene la verificación de sesión
include_once "../../include/header.enfermeria.php";
date_default_timezone_set('America/Lima');
// Obtener idcolaborador (doctor) de la sesión
$idDoctor = $_SESSION['usuario']['idcolaborador'];
$fechaHoy = date('Y-m-d');
$horaActual = date('H:i:s');
?>

<div class="container-fluid px-4" id="main-container">
    <!-- Sección de título y breadcrumb -->
    <div id="page-header-section">
        <h1 class="mt-4">
            <i class="fas fa-calendar-check me-2 text-primary"></i>
            Pacientes con Citas para Hoy
        </h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item">
                <a href="<?= $host ?>/views/include/dashboard.enfermeria.php">
                    <i class="fas fa-home me-1"></i>Panel de Control
                </a>
            </li>
            <li class="breadcrumb-item active">
                <i class="fas fa-users me-1"></i>Pacientes - <?= date('d/m/Y') ?>
            </li>
        </ol>
    </div>

    <!-- Card principal de gestión -->
    <div id="lista-pacientes-container">
        <div class="card mb-4 shadow-lg border-0">
            <div class="card-header bg-gradient-primary text-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-users me-2"></i>
                        Pacientes con Citas - <?= date('d/m/Y', strtotime($fechaHoy)) ?>
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <input type="date" id="fecha-busqueda" class="form-control form-control-sm" 
                               style="width: auto;" value="<?= $fechaHoy ?>">
                        <button class="btn btn-light btn-sm" id="btn-actualizar-pacientes">
                            <i class="fas fa-sync-alt me-1"></i>Actualizar
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Panel de filtros mejorado -->
                <div class="filters-panel mb-4 p-3 bg-light rounded">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-id-card text-primary me-1"></i>
                                Número de Documento
                            </label>
                            <input type="text" class="form-control" id="filtro-documento" 
                                   placeholder="Buscar por número de documento">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user text-primary me-1"></i>
                                Nombre o Apellido
                            </label>
                            <input type="text" class="form-control" id="filtro-paciente" 
                                   placeholder="Buscar por nombre o apellido">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="w-100">
                                <button class="btn btn-primary me-2" id="btn-buscar">
                                    <i class="fas fa-search me-1"></i>Buscar
                                </button>
                                <button class="btn btn-outline-secondary" id="btn-limpiar">
                                    <i class="fas fa-eraser me-1"></i>Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barra de información -->
                <div class="info-bar d-flex justify-content-between align-items-center mb-3 p-3 bg-info-subtle rounded">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-success px-3 py-2" id="total-pacientes">
                            <i class="fas fa-user-check me-1"></i>
                            0 pacientes con citas
                        </span>
                        <div id="loading-indicator" class="d-none">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <span class="text-muted">Actualizando...</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-muted me-2">Registros por página:</label>
                        <select id="registros-por-pagina" class="form-select form-select-sm" style="width: auto;">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <!-- Tabla de pacientes -->
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0" id="tabla-pacientes">
                            <thead class="table-dark">
                                <tr>
                                    <th>
                                        <i class="fas fa-user me-1"></i>Paciente
                                    </th>
                                    <th>
                                        <i class="fas fa-id-card me-1"></i>Documento
                                    </th>
                                    <th>
                                        <i class="fas fa-phone me-1"></i>Teléfono
                                    </th>
                                    <th>
                                        <i class="fas fa-clock me-1"></i>Hora Cita
                                    </th>
                                    <th>
                                        <i class="fas fa-info-circle me-1"></i>Estado
                                    </th>
                                    <th>
                                        <i class="fas fa-user-md me-1"></i>Doctor
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-cogs me-1"></i>Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mensaje cuando no hay pacientes -->
                <div id="sin-pacientes" class="text-center py-5 d-none">
                    <div class="empty-state">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted mb-2">No se encontraron pacientes con citas</h4>
                        <p class="text-muted mb-4">
                            No hay pacientes programados para la fecha seleccionada
                        </p>
                        <button class="btn btn-primary" onclick="location.reload()">
                            <i class="fas fa-refresh me-1"></i>Recargar Página
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Historia Clínica - SISTEMA COMPLETO -->
    <div id="historia-container" class="d-none w-100 mt-4 mb-5">
        <div class="historia-modal-wrapper pt-3 px-3 pb-4">
            <div class="card shadow historia-card">
                <div class="card-header bg-gradient-primary-to-secondary text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="modal-title mb-0 ps-2">
                        <i class="fas fa-file-medical me-2"></i>Nueva Historia Clínica del Paciente
                    </h5>
                    <button type="button" class="btn-close btn-close-white me-2" id="btn-cerrar-historia" aria-label="Close"></button>
                </div>
                <div class="card-body p-0">
                    <input type="hidden" id="idpaciente-historia" name="idpaciente">
                    <input type="hidden" id="idconsulta-historia" name="idconsulta">
                    <input type="hidden" id="idcita-historia" name="idcita">
                    <input type="hidden" id="iddoctor-cita" name="iddoctor">

                    <!-- Cabecera con datos del paciente -->
                    <div class="paciente-info-header p-4 mb-2 bg-gradient-light-to-secondary border-bottom">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center mb-3 mb-md-0">
                                <div class="avatar-circle">
                                    <i class="fas fa-user-circle fa-4x"></i>
                                </div>
                            </div>
                            <div class="col-md-10">
                                <h4 id="paciente-nombre-historia" class="mb-2 text-primary fw-bold"></h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><i class="fas fa-id-card text-secondary me-2"></i><span id="paciente-documento-historia"></span></p>
                                        <p class="mb-1"><i class="fas fa-phone text-secondary me-2"></i><span id="paciente-telefono-historia"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <div id="alergias-container-historia">
                                            <div id="alergias-lista-historia" class="alert alert-warning d-none mb-0">
                                                <i class="fas fa-exclamation-triangle me-2"></i>No se encontraron alergias registradas
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Indicador de progreso -->
                    <div class="px-4 py-3 bg-light border-bottom">
                        <div class="progress-wizard">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="progress-steps">
                                <div class="step active" data-step="1">
                                    <div class="step-circle">1</div>
                                    <div class="step-text">Revisar Historial</div>
                                </div>
                                <div class="step" data-step="2">
                                    <div class="step-circle">2</div>
                                    <div class="step-text">Nueva Historia</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de pasos -->
                    <div class="step-panels">
                        <!-- Paso 1: Revisar Historial -->
                        <div class="step-panel active" id="step-1-historia">
                            <div class="p-4">
                                <div class="documento-like">
                                    <div class="doc-header">
                                        <div class="doc-title">
                                            <h1>Historial Clínico del Paciente</h1>
                                        </div>
                                        <div class="doc-toolbar">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-imprimir-historial">
                                                <i class="fas fa-print"></i> Imprimir
                                            </button>
                                        </div>
                                    </div>

                                    <div class="doc-content">
                                        <div id="historial-vacio" class="doc-section text-center p-5 d-none">
                                            <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                                            <h5>Este paciente no tiene historial médico previo</h5>
                                            <p class="text-muted">Esta será la primera consulta registrada para el paciente.</p>
                                        </div>

                                        <div id="historial-contenido" class="d-none">
                                            <!-- Esta sección se llenará dinámicamente con el historial -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 2: Nueva Historia -->
                        <div class="step-panel" id="step-2-historia">
                            <div class="p-4">
                                <div class="documento-like">
                                    <div class="doc-header">
                                        <div class="doc-title">
                                            <h1>Nueva Historia Clínica</h1>
                                        </div>
                                        <div class="doc-date">
                                            Fecha: <span id="fecha-actual-historia"><?= date('d/m/Y') ?></span>
                                        </div>
                                    </div>

                                    <div class="doc-content">
                                        <form id="form-historia-nueva" class="needs-validation" novalidate>
                                            <div class="doc-section mb-4">
                                                <h2>Enfermedad Actual</h2>
                                                <div class="form-group">
                                                    <textarea class="form-control doc-control"
                                                        id="enfermedadactual-historia"
                                                        name="enfermedadactual"
                                                        rows="4"
                                                        required
                                                        placeholder="Describa los síntomas y molestias actuales del paciente..."></textarea>
                                                    <div class="invalid-feedback">Por favor, describa la enfermedad actual.</div>
                                                </div>
                                            </div>

                                            <div class="doc-section mb-4">
                                                <h2>Examen Físico</h2>
                                                <div class="form-group">
                                                    <textarea class="form-control doc-control"
                                                        id="examenfisico-historia"
                                                        name="examenfisico"
                                                        rows="4"
                                                        required
                                                        placeholder="Registre los hallazgos del examen físico realizado..."></textarea>
                                                    <div class="invalid-feedback">Por favor, complete el examen físico.</div>
                                                </div>
                                            </div>

                                            <div class="doc-section mb-4">
                                                <h2>Evolución</h2>
                                                <div class="form-group">
                                                    <textarea class="form-control doc-control"
                                                        id="evolucion-historia"
                                                        name="evolucion"
                                                        rows="4"
                                                        required
                                                        placeholder="Describa la evolución del paciente y el plan de tratamiento..."></textarea>
                                                    <div class="invalid-feedback">Por favor, complete la evolución.</div>
                                                </div>
                                            </div>

                                            <div class="doc-section mb-4">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <h2>Diagnóstico</h2>
                                                        <div class="input-group mb-3">
                                                            <select class="form-select doc-control" id="iddiagnostico-historia" name="iddiagnostico" required>
                                                                <option value="">Seleccione diagnóstico</option>
                                                                <!-- Opciones cargadas desde la base de datos -->
                                                            </select>
                                                            <button class="btn btn-success" type="button" id="btn-nuevo-diagnostico-historia" title="Agregar nuevo diagnóstico">
                                                                <i class="fas fa-plus"></i> Nuevo Diagnóstico
                                                            </button>
                                                            <div class="invalid-feedback">Por favor, seleccione un diagnóstico.</div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-check mt-4">
                                                            <input class="form-check-input" type="checkbox" id="altamedica-historia" name="altamedica" value="1">
                                                            <label class="form-check-label fw-bold" for="altamedica-historia">
                                                                <i class="fas fa-check-circle me-1 text-success"></i>Alta Médica
                                                            </label>
                                                            <small class="form-text text-muted d-block">Marque si el paciente recibe alta médica</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Sección adicional para observaciones -->
                                            <div class="doc-section mb-4">
                                                <h2>Observaciones Adicionales</h2>
                                                <div class="form-group">
                                                    <textarea class="form-control doc-control"
                                                        id="observaciones-historia"
                                                        name="observaciones"
                                                        rows="3"
                                                        placeholder="Observaciones adicionales (opcional)..."></textarea>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-3">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-6 text-start">
                                <button type="button" class="btn btn-secondary" id="wizard-btn-anterior">
                                    <i class="fas fa-chevron-left me-1"></i>Anterior
                                </button>
                            </div>
                            <div class="col-6 text-end">
                                <button type="button" class="btn btn-primary" id="wizard-btn-siguiente">
                                    Siguiente<i class="fas fa-chevron-right ms-1"></i>
                                </button>
                                <button type="button" class="btn btn-success btn-lg d-none" id="btn-finalizar-historia">
                                    <i class="fas fa-save me-1"></i>Guardar Historia Clínica
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para nuevo diagnóstico -->
<div class="modal fade" id="modalNuevoDiagnosticoHistoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-warm-to-orange text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>Agregar Nuevo Diagnóstico
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Complete la información del nuevo diagnóstico. Una vez guardado, se seleccionará automáticamente en la historia clínica.
                </div>

                <form id="form-diagnostico-historia" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="diag-nombre-historia" class="form-label">
                                    <i class="fas fa-stethoscope text-primary me-1"></i>
                                    Nombre del diagnóstico <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="diag-nombre-historia" required
                                    placeholder="Ej: Hipertensión arterial primaria">
                                <div class="invalid-feedback">Por favor, ingrese el nombre del diagnóstico.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="diag-codigo-historia" class="form-label">
                                    <i class="fas fa-barcode text-primary me-1"></i>
                                    Código CIE-10 <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control text-uppercase" id="diag-codigo-historia" required
                                    placeholder="Ej: J11.1" style="font-family: monospace;">
                                <div class="invalid-feedback">Por favor, ingrese el código del diagnóstico.</div>
                                <div class="form-text">
                                    Código según CIE-10. Ejemplos: J11.1 (gripe), I10 (hipertensión), K59.0 (estreñimiento)
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="diag-descripcion-historia" class="form-label">
                            <i class="fas fa-file-alt text-primary me-1"></i>
                            Descripción detallada
                        </label>
                        <textarea class="form-control" id="diag-descripcion-historia" rows="4"
                            placeholder="Descripción detallada del diagnóstico (opcional)"></textarea>
                    </div>

                    <!-- Previsualización -->
                    <div class="border rounded p-3 bg-light">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-eye me-1"></i>Previsualización:
                        </h6>
                        <div id="preview-diagnostico">
                            <strong>Código:</strong> <span id="preview-codigo">-</span><br>
                            <strong>Nombre:</strong> <span id="preview-nombre">-</span><br>
                            <strong>Descripción:</strong> <span id="preview-descripcion">Sin descripción</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btn-guardar-diagnostico-historia">
                    <i class="fas fa-save me-1"></i>Guardar Diagnóstico
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;"></div>

<style>
/* Estilos para alergias */
.alert-success {
    background-color: rgba(25, 135, 84, 0.1);
    border-color: rgba(25, 135, 84, 0.2);
    color: #0f5132;
}

.alert-info {
    background-color: rgba(13, 202, 240, 0.1);
    border-color: rgba(13, 202, 240, 0.2);
    color: #055160;
}

.alert ul {
    padding-left: 1.5rem;
    margin-bottom: 0;
}

.alert ul li {
    margin-bottom: 0.25rem;
}

.badge.bg-warning.text-dark {
    background-color: #ffc107 !important;
    color: #000 !important;
    font-size: 0.7rem;
}

/* Estilos personalizados */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-primary-to-secondary {
    background: linear-gradient(135deg, #2c3e50 0%, #4ca1af 100%);
}

.bg-gradient-warm-to-orange {
    background: linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%);
}

.bg-gradient-light-to-secondary {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.bg-info-subtle {
    background-color: rgba(13, 202, 240, 0.1);
}

.filters-panel {
    border: 1px solid #e9ecef;
}

.empty-state {
    max-width: 400px;
    margin: 0 auto;
}

.badge-estado {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-programada {
    background-color: #ffc107;
    color: #000;
}

.badge-confirmada {
    background-color: #0d6efd;
    color: #fff;
}

.badge-realizada {
    background-color: #198754;
    color: #fff;
}

.badge-cancelada {
    background-color: #dc3545;
    color: #fff;
}

.btn-accion {
    width: 32px;
    height: 32px;
    padding: 0;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Historia clínica */
.historia-modal-wrapper {
    max-width: 95%;
    margin: 0 auto;
    position: relative;
}

.historia-card {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.paciente-info-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    border-radius: 0;
}

.avatar-circle {
    width: 85px;
    height: 85px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    border: 3px solid #fff;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    color: white;
}

.avatar-circle i {
    color: white !important;
}

.paciente-info-header h4 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.paciente-info-header p {
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.paciente-info-header p i {
    width: 18px;
    text-align: center;
}

.documento-like {
    background: white;
    border: 1px solid #dee2e6;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    padding: 2rem;
    min-height: 400px;
    position: relative;
    width: 100%;
    max-width: 100%;
}

.doc-header {
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.doc-title h1 {
    font-size: 1.75rem;
    margin: 0;
    color: #2c3e50;
}

.doc-date {
    font-size: 0.9rem;
    color: #6c757d;
}

.doc-content {
    font-size: 1rem;
    line-height: 1.5;
    color: #212529;
}

.doc-section {
    margin-bottom: 1.5rem;
}

.doc-section h2 {
    font-size: 1.25rem;
    color: #4ca1af;
    margin-bottom: 0.75rem;
    border-bottom: 1px solid #f8f9fa;
    padding-bottom: 0.5rem;
}

.doc-control {
    border: 1px solid #e9ecef;
    padding: 0.625rem;
    border-radius: 0.25rem;
    background-color: #f8f9fa;
    transition: all 0.2s ease-in-out;
}

.doc-control:focus {
    background-color: #fff;
    border-color: #4ca1af;
    box-shadow: 0 0 0 0.25rem rgba(76, 161, 175, 0.25);
}

/* Indicador de progreso */
.progress-wizard {
    padding: 1rem 0 0.5rem;
}

.progress {
    height: 0.5rem;
    margin-bottom: 1.5rem;
    background-color: #e9ecef;
}

.progress-bar {
    background-color: #4ca1af;
    transition: width 0.3s ease;
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-top: -2.25rem;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
    flex: 1;
    transition: all 0.3s ease;
    opacity: 0.5;
}

.step.active {
    opacity: 1;
}

.step.completed .step-circle {
    background-color: #4ca1af;
    color: white;
}

.step-circle {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background-color: white;
    border: 2px solid #4ca1af;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4ca1af;
    font-weight: bold;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.step.active .step-circle {
    transform: scale(1.1);
    box-shadow: 0 0 0 5px rgba(76, 161, 175, 0.2);
}

.step-text {
    font-size: 0.8rem;
    color: #6c757d;
    text-align: center;
    max-width: 120px;
}

.step.active .step-text {
    color: #2c3e50;
    font-weight: 500;
}

/* Paneles de pasos */
.step-panel {
    display: none;
}

.step-panel.active {
    display: block;
    animation: fadeIn 0.5s ease-in-out;
    min-height: 400px;
}

/* Historial */
.historial-consulta {
    margin-bottom: 2rem;
    border-left: 3px solid #4ca1af;
    padding-left: 1rem;
}

.historial-consulta-fecha {
    font-size: 1rem;
    color: #4ca1af;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.historial-consulta-contenido {
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
}

.historial-seccion {
    margin-bottom: 1rem;
}

.historial-seccion h3 {
    font-size: 1rem;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.historial-seccion p {
    margin-bottom: 0.5rem;
    color: #495057;
}

.historial-diagnostico {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    background-color: #e9ecef;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.historial-alta {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    background-color: #d1e7dd;
    color: #0f5132;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    margin-left: 0.5rem;
}

/* Estilos mejorados para alergias críticas */
.alert-danger {
    background-color: rgba(220, 53, 69, 0.15) !important;
    border: 2px solid rgba(220, 53, 69, 0.4) !important;
    border-left: 6px solid #dc3545 !important;
    color: #721c24 !important;
    animation: alertPulse 3s infinite;
}

.alert-danger strong {
    color: #dc3545 !important;
    font-size: 1.1em;
}

.alert-danger .badge {
    animation: badgePulse 2s infinite;
    font-weight: bold;
}

.alert-danger ul li {
    padding: 6px 0;
    font-weight: 500;
    border-bottom: 1px solid rgba(220, 53, 69, 0.1);
}

.alert-danger ul li:last-child {
    border-bottom: none;
}

@keyframes alertPulse {
    0%, 100% { 
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
    }
    50% { 
        box-shadow: 0 0 0 8px rgba(220, 53, 69, 0.1);
    }
}

@keyframes badgePulse {
    0%, 100% { 
        opacity: 1; 
        transform: scale(1);
    }
    50% { 
        opacity: 0.8; 
        transform: scale(1.05);
    }
}

/* Animaciones */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.5s ease-out;
}

/* Ocultar elementos del header en modo de historia */
.historia-mode #page-header-section {
    display: none !important;
}

/* Responsive */
@media (max-width: 768px) {
    .filters-panel .row > div {
        margin-bottom: 1rem;
    }
    
    .historia-modal-wrapper {
        max-width: 100%;
        padding: 0.5rem;
    }
    
    .documento-like {
        padding: 1rem;
    }
    
    .step-circle {
        width: 2rem;
        height: 2rem;
    }
}
</style>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Variables globales
    let tablaPacientes = null;
    const doctorId = <?= $idDoctor ?>;
    let pacienteActual = null;
    let currentStep = 1;
    let alergiasPacienteHistoria = [];
    
    console.log('🏥 Sistema iniciado - Doctor ID:', doctorId);

    // Referencias DOM - Lista
    const mainContainer = document.getElementById('main-container');
    const listaContainer = document.getElementById('lista-pacientes-container');
    const historiaContainer = document.getElementById('historia-container');
    
    // Referencias DOM - Historia
    const btnCerrarHistoria = document.getElementById('btn-cerrar-historia');
    const wizardBtnAnterior = document.getElementById('wizard-btn-anterior');
    const wizardBtnSiguiente = document.getElementById('wizard-btn-siguiente');
    const btnFinalizarHistoria = document.getElementById('btn-finalizar-historia');
    
    // Elementos del wizard
    const progressBar = document.querySelector('.progress-bar');
    const steps = document.querySelectorAll('.step');
    const stepPanels = document.querySelectorAll('.step-panel');
    
    // Elementos del paciente
    const idpacienteHistoriaInput = document.getElementById('idpaciente-historia');
    const idconsultaHistoriaInput = document.getElementById('idconsulta-historia');
    const idcitaHistoriaInput = document.getElementById('idcita-historia');
    const iddoctorCitaInput = document.getElementById('iddoctor-cita');
    const pacienteNombreHistoria = document.getElementById('paciente-nombre-historia');
    const pacienteDocumentoHistoria = document.getElementById('paciente-documento-historia');
    const pacienteTelefonoHistoria = document.getElementById('paciente-telefono-historia');
    const alergiasListaHistoria = document.getElementById('alergias-lista-historia');
    
    // Elementos del historial
    const historialVacio = document.getElementById('historial-vacio');
    const historialContenido = document.getElementById('historial-contenido');
    
    // Elementos del formulario
    const formHistoriaNueva = document.getElementById('form-historia-nueva');
    const enfermedadActualHistoria = document.getElementById('enfermedadactual-historia');
    const examenFisicoHistoria = document.getElementById('examenfisico-historia');
    const evolucionHistoria = document.getElementById('evolucion-historia');
    const observacionesHistoria = document.getElementById('observaciones-historia');
    const iddiagnosticoHistoria = document.getElementById('iddiagnostico-historia');
    const altamedicaHistoria = document.getElementById('altamedica-historia');
    
    // Modal diagnóstico
    const btnNuevoDiagnosticoHistoria = document.getElementById('btn-nuevo-diagnostico-historia');
    const modalNuevoDiagnosticoHistoria = new bootstrap.Modal(document.getElementById('modalNuevoDiagnosticoHistoria'));
    const formDiagnosticoHistoria = document.getElementById('form-diagnostico-historia');
    const diagNombreHistoria = document.getElementById('diag-nombre-historia');
    const diagCodigoHistoria = document.getElementById('diag-codigo-historia');
    const diagDescripcionHistoria = document.getElementById('diag-descripcion-historia');
    const previewCodigo = document.getElementById('preview-codigo');
    const previewNombre = document.getElementById('preview-nombre');
    const previewDescripcion = document.getElementById('preview-descripcion');
    const btnGuardarDiagnosticoHistoria = document.getElementById('btn-guardar-diagnostico-historia');

    // Función para mostrar carga
    function mostrarCarga(mostrar = true) {
        if (mostrar) {
            $('#loading-indicator').removeClass('d-none');
            $('#btn-actualizar-pacientes').prop('disabled', true);
        } else {
            $('#loading-indicator').addClass('d-none');
            $('#btn-actualizar-pacientes').prop('disabled', false);
        }
    }

    // Inicializar DataTable
    function inicializarTabla() {
        try {
            tablaPacientes = $('#tabla-pacientes').DataTable({
                dom: 'rt<"bottom"ip>',
                pageLength: 10,
                ordering: true,
                info: true,
                searching: false,
                lengthChange: false,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                order: [[3, 'asc']], // Ordenar por hora
                columnDefs: [
                    { targets: -1, orderable: false }
                ]
            });

            $('#registros-por-pagina').on('change', function() {
                const valor = parseInt($(this).val());
                if (tablaPacientes && !isNaN(valor)) {
                    tablaPacientes.page.len(valor).draw();
                }
            });

            console.log('✅ DataTable inicializado correctamente');
        } catch (error) {
            console.error('❌ Error al inicializar DataTable:', error);
            mostrarToast('Error al inicializar la tabla', 'error');
        }
    }

    // Cargar pacientes con citas
    function cargarPacientesCitas() {
        const fecha = $('#fecha-busqueda').val();
        const filtroDoc = $('#filtro-documento').val();
        const filtroNombre = $('#filtro-paciente').val();
        
        console.log('📅 Cargando citas para fecha:', fecha);
        
        mostrarCarga(true);
        
        // Mostrar loading en tabla
        if (tablaPacientes) {
            tablaPacientes.clear();
        }
        $('#tabla-pacientes tbody').html(`
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <span>Cargando pacientes...</span>
                    </div>
                </td>
            </tr>
        `);

        // Construir URL
        let url = `<?= $host ?>/controllers/cita.controller.php?op=listar&fecha=${fecha}`;
        if (filtroDoc) url += `&nrodoc=${filtroDoc}`;
        if (filtroNombre) url += `&paciente=${filtroNombre}`;

        console.log('📡 URL:', url);

        // Llamar al controlador
        fetch(url)
            .then(response => {
                console.log('📡 Respuesta:', response.status);
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('📊 Datos:', data);
                
                if (tablaPacientes) {
                    tablaPacientes.clear();
                }
                
                if (data.status && data.data && data.data.length > 0) {
                    const filas = [];
                    
                    data.data.forEach(cita => {
                        // Badge de estado
                        let badgeEstado = '';
                        const estado = (cita.estado || 'PROGRAMADA').toUpperCase();
                        
                        switch(estado) {
                            case 'PROGRAMADA':
                                badgeEstado = '<span class="badge badge-estado badge-programada">Programada</span>';
                                break;
                            case 'CONFIRMADA':
                                badgeEstado = '<span class="badge badge-estado badge-confirmada">Confirmada</span>';
                                break;
                            case 'REALIZADA':
                                badgeEstado = '<span class="badge badge-estado badge-realizada">Realizada</span>';
                                break;
                            case 'CANCELADA':
                                badgeEstado = '<span class="badge badge-estado badge-cancelada">Cancelada</span>';
                                break;
                            default:
                                badgeEstado = '<span class="badge badge-estado badge-programada">Pendiente</span>';
                        }

                        // Nombre del paciente
                        const nombrePaciente = (cita.paciente_apellido && cita.paciente_nombre) ? 
                            `${cita.paciente_apellido}, ${cita.paciente_nombre}` : 
                            (cita.nombre_paciente || 'No disponible');
                        
                        // Documento
                        const documento = (cita.documento_tipo && cita.documento_numero) ?
                            `${cita.documento_tipo}: ${cita.documento_numero}` :
                            'No disponible';
                        
                        // Doctor
                        const doctor = (cita.doctor_apellido && cita.doctor_nombre) ?
                            `Dr. ${cita.doctor_apellido}, ${cita.doctor_nombre}` :
                            (cita.nombre_doctor || 'No asignado');

                        // Botones de acción mejorados
                        const btnHistorial = `<button class="btn btn-info btn-sm btn-accion btn-ver-historial" 
                                data-idpaciente="${cita.idpaciente}" 
                                title="Ver Historial Completo">
                                <i class="fas fa-eye"></i>
                            </button>`;
                            
                        const btnCrearHistoria = estado !== 'CANCELADA' && estado !== 'REALIZADA' ? 
                            `<button class="btn btn-success btn-sm btn-accion btn-crear-historia ms-1" 
                                data-idpaciente="${cita.idpaciente}" 
                                data-idcita="${cita.idcita}"
                                data-iddoctor="${cita.iddoctor || cita.idcolaborador}"
                                data-nombre="${nombrePaciente}"
                                data-documento="${documento}"
                                data-hora="${cita.hora || cita.horaprogramada}"
                                title="Crear Historia Clínica">
                                <i class="fas fa-file-medical"></i>
                            </button>` : 
                            `<button class="btn btn-secondary btn-sm btn-accion" disabled title="Cita finalizada">
                                <i class="fas fa-ban"></i>
                            </button>`;
                            
                        const botonesAccion = `
                            <div class="btn-group" role="group">
                                ${btnHistorial}
                                ${btnCrearHistoria}
                            </div>`;

                        filas.push([
                            `<div class="d-flex align-items-center">
                                <div class="avatar-sm me-2">
                                    <i class="fas fa-user-circle fa-2x text-muted"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">${nombrePaciente}</div>
                                    <small class="text-muted">${cita.email || 'Sin email'}</small>
                                </div>
                            </div>`,
                            documento,
                            cita.telefono_paciente || cita.telefono || 'No disponible',
                            `<span class="badge bg-info">
                                <i class="fas fa-clock me-1"></i>
                                ${cita.hora || cita.horaprogramada || 'Sin hora'}
                            </span>`,
                            badgeEstado,
                            `<small class="text-muted">${doctor}</small>`,
                            `<div class="text-center">${botonesAccion}</div>`
                        ]);
                    });

                    if (tablaPacientes) {
                        tablaPacientes.rows.add(filas).draw();
                    }
                    $('#total-pacientes').html(`<i class="fas fa-user-check me-1"></i>${data.data.length} pacientes con citas`);
                    $('#sin-pacientes').addClass('d-none');
                    
                    console.log(`✅ ${data.data.length} pacientes cargados`);
                    mostrarToast(`Se encontraron ${data.data.length} pacientes con citas`);
                    
                } else {
                    $('#sin-pacientes').removeClass('d-none');
                    $('#total-pacientes').text('0 pacientes con citas');
                    console.log('ℹ️ No hay citas para la fecha');
                    mostrarToast('No se encontraron pacientes con citas', 'info');
                }
            })
            .catch(error => {
                console.error('❌ Error:', error);
                $('#sin-pacientes').removeClass('d-none');
                mostrarToast('Error al cargar pacientes', 'error');
            })
            .finally(() => {
                mostrarCarga(false);
            });
    }

    // Cargar diagnósticos
    function cargarDiagnosticos() {
        console.log('🩺 Cargando diagnósticos disponibles...');
        
        fetch(`<?= $host ?>/controllers/diagnostico.controller.php?op=listar`)
            .then(response => {
                console.log('📡 Respuesta diagnósticos:', response.status);
                if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('📊 Diagnósticos recibidos:', data);
                
                if (data.status && data.data) {
                    const select = iddiagnosticoHistoria;
                    select.innerHTML = '<option value="">Seleccione un diagnóstico</option>';
                    
                    // Ordenar diagnósticos por código
                    const diagnosticosOrdenados = data.data.sort((a, b) => a.codigo.localeCompare(b.codigo));
                    
                    diagnosticosOrdenados.forEach(diag => {
                        const option = document.createElement('option');
                        option.value = diag.iddiagnostico;
                        option.textContent = `${diag.codigo} - ${diag.nombre}`;
                        option.title = diag.descripcion || diag.nombre;
                        select.appendChild(option);
                    });
                    
                    console.log(`✅ Cargados ${diagnosticosOrdenados.length} diagnósticos`);
                } else {
                    console.warn('⚠️ No se pudieron cargar los diagnósticos');
                    iddiagnosticoHistoria.innerHTML = '<option value="">Error al cargar diagnósticos</option>';
                }
            })
            .catch(error => {
                console.error('❌ Error al cargar diagnósticos:', error);
                iddiagnosticoHistoria.innerHTML = '<option value="">Error al cargar diagnósticos</option>';
            });
    }

    // Función OPTIMIZADA para cargar alergias del paciente
    async function cargarAlergiasPacienteHistoria(idpaciente) {
        try {
            console.log('🔍 Cargando alergias para paciente ID:', idpaciente);
            
            // ENDPOINT PRINCIPAL - Usar el nuevo método del controlador de pacientes
            const url = `<?= $host ?>/controllers/paciente.controller.php?operacion=obtener_alergias&idpaciente=${idpaciente}`;
            console.log('📡 URL alergias:', url);
            
            const response = await fetch(url);
            console.log('📡 Status respuesta alergias:', response.status);
            
            if (!response.ok) {
                console.log('❌ Error en respuesta de alergias, status:', response.status);
                return [];
            }
            
            const responseText = await response.text();
            console.log('📄 Respuesta RAW alergias (primeros 200 chars):', responseText.substring(0, 200));
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.log('❌ Error parsing JSON alergias:', e.message);
                return [];
            }
            
            console.log('📊 Datos procesados alergias:', data);
            
            // Buscar alergias en la respuesta
            let alergias = [];
            if (data.status === true || data.status === 'true') {
                if (data.alergias && Array.isArray(data.alergias)) {
                    alergias = data.alergias;
                } else if (data.data && Array.isArray(data.data)) {
                    alergias = data.data;
                }
            }
            
            console.log(`🎯 Alergias encontradas: ${alergias.length}`, alergias);
            return alergias || [];
            
        } catch (error) {
            console.error('❌ Error al cargar alergias:', error);
            return [];
        }
    }

    // Función para iniciar historia clínica
    function iniciarHistoriaClinica(datos) {
        console.log('📋 Iniciando historia clínica para:', datos);
        
        // Guardar datos del paciente
        pacienteActual = datos;
        
        // Reiniciar proceso
        reiniciarProcesoHistoria();
        
        // Llenar información en los campos ocultos
        idpacienteHistoriaInput.value = datos.idpaciente;
        idcitaHistoriaInput.value = datos.idcita;
        iddoctorCitaInput.value = datos.iddoctor;
        
        // Mostrar datos básicos del paciente inmediatamente
        pacienteNombreHistoria.textContent = datos.nombre || 'Paciente';
        pacienteDocumentoHistoria.textContent = datos.documento || 'Sin documento';
        pacienteTelefonoHistoria.textContent = 'Cargando...';
        
        // Mostrar mensaje de carga para alergias
        alergiasListaHistoria.classList.remove('d-none');
        alergiasListaHistoria.classList.add('alert-info');
        alergiasListaHistoria.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verificando alergias del paciente...';
        
        // Añadir clase historia-mode para ocultar header
        mainContainer.classList.add('historia-mode');
        
        // Realizar peticiones en paralelo
        Promise.all([
            cargarDatosPacienteHistoria(datos.idpaciente),
            cargarAlergiasPacienteHistoria(datos.idpaciente),
            cargarHistorialPacienteHistoria(datos.idpaciente)
        ]).then(([datosPaciente, alergias, historial]) => {
            console.log('📊 Datos cargados para historia clínica:', { datosPaciente, alergias, historial });
            
            // Actualizar información del paciente con datos completos
            if (datosPaciente) {
                const nombreCompleto = `${datosPaciente.nombres || ''} ${datosPaciente.apellidos || ''}`.trim();
                const documento = `${datosPaciente.tipodoc || 'DNI'}: ${datosPaciente.nrodoc || 'Sin documento'}`;
                const telefono = datosPaciente.telefono || 'No registrado';
                
                pacienteNombreHistoria.textContent = nombreCompleto || datos.nombre || 'Paciente';
                pacienteDocumentoHistoria.textContent = documento;
                pacienteTelefonoHistoria.textContent = telefono;
                
                console.log('✅ Datos del paciente actualizados:', { nombreCompleto, documento, telefono });
            } else {
                pacienteTelefonoHistoria.textContent = 'No disponible';
                console.log('⚠️ No se pudieron cargar datos completos del paciente');
            }
            
            // Guardar alergias para validaciones posteriores
            alergiasPacienteHistoria = alergias || [];
            
            // Mostrar alergias
            console.log('🔍 Procesando alergias recibidas:', alergias);
            alergiasListaHistoria.classList.remove('alert-info');
            
            if (alergias && Array.isArray(alergias) && alergias.length > 0) {
                console.log(`⚠️ ALERTA CRÍTICA: Paciente tiene ${alergias.length} alergias registradas:`, alergias);
                
                alergiasListaHistoria.classList.remove('d-none', 'alert-warning', 'alert-success');
                alergiasListaHistoria.classList.add('alert-danger');
                
                let alergiasHTML = '<i class="fas fa-exclamation-triangle me-2"></i><strong>⚠️ ALERGIAS REGISTRADAS:</strong><ul class="mb-0 mt-2">';
                
                alergias.forEach((alergia, index) => {
                    const tipo = alergia.tipoalergia || alergia.tipo_alergia || alergia.tipo || 'Alergia';
                    const nombre = alergia.alergia || alergia.nombre || alergia.nombre_alergia || 'No especificada';
                    const gravedad = alergia.gravedad || alergia.severidad || alergia.nivel || 'No especificada';
                    
                    alergiasHTML += `<li><strong>${tipo}:</strong> ${nombre} <span class="badge bg-warning text-dark">${gravedad}</span></li>`;
                });
                
                alergiasHTML += '</ul>';
                alergiasListaHistoria.innerHTML = alergiasHTML;
                
                // Mostrar alerta urgente
                setTimeout(() => {
                    Swal.fire({
                        title: '⚠️ ATENCIÓN: Paciente con Alergias',
                        html: `
                            <div class="text-start">
                                <p class="text-danger fw-bold mb-3">Este paciente tiene ${alergias.length} alergia(s) registrada(s):</p>
                                <div class="alert alert-danger text-start">
                                    <ul class="mb-0">
                                        ${alergias.map(a => {
                                            const tipo = a.tipoalergia || a.tipo || 'Alergia';
                                            const nombre = a.alergia || a.nombre || 'No especificada';
                                            const gravedad = a.gravedad || 'No especificada';
                                            return `<li><strong>${tipo}:</strong> ${nombre} <span class="badge bg-warning text-dark ms-1">${gravedad}</span></li>`;
                                        }).join('')}
                                    </ul>
                                </div>
                                <p class="text-warning mt-2 mb-0"><small><i class="fas fa-exclamation-triangle me-1"></i><strong>Tenga especial cuidado durante la atención médica</strong></small></p>
                            </div>
                        `,
                        icon: 'warning',
                        confirmButtonText: 'Entendido - Proceder con Cuidado',
                        confirmButtonColor: '#dc3545',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        timer: 12000,
                        timerProgressBar: true,
                        width: '600px'
                    });
                }, 1000);
                
            } else {
                console.log('ℹ️ No hay alergias registradas para este paciente');
                
                alergiasListaHistoria.classList.remove('d-none', 'alert-danger', 'alert-warning');
                alergiasListaHistoria.classList.add('alert-success');
                alergiasListaHistoria.innerHTML = '<i class="fas fa-check-circle me-2"></i><strong>✅ No se encontraron alergias registradas</strong>';
            }
            
            // Mostrar historial
            if (historial && historial.length > 0) {
                historialVacio.classList.add('d-none');
                historialContenido.classList.remove('d-none');
                
                let historialHTML = '';
                
                historial.forEach(consulta => {
                    const fecha = new Date(consulta.fecha).toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                    
                    historialHTML += `
                        <div class="historial-consulta">
                            <div class="historial-consulta-fecha">
                                <i class="fas fa-calendar-day me-2"></i>${fecha}
                            </div>
                            <div class="historial-consulta-contenido">
                                <div class="historial-seccion">
                                    <h3>Enfermedad Actual</h3>
                                    <p>${consulta.enfermedadactual || 'No registrada'}</p>
                                </div>
                                <div class="historial-seccion">
                                    <h3>Examen Físico</h3>
                                    <p>${consulta.examenfisico || 'No registrado'}</p>
                                </div>
                                <div class="historial-seccion">
                                    <h3>Evolución</h3>
                                    <p>${consulta.evolucion || 'No registrada'}</p>
                                </div>
                                <div class="historial-seccion">
                                    <h3>Diagnóstico</h3>
                                    <div>
                                        <span class="historial-diagnostico">${consulta.diagnostico_nombre || 'No registrado'}</span>
                                        ${consulta.altamedica ? '<span class="historial-alta">Alta médica</span>' : ''}
                                    </div>
                                </div>
                                <div class="historial-seccion">
                                    <h3>Médico</h3>
                                    <p>Dr. ${consulta.doctor_nombre || 'No registrado'} - ${consulta.especialidad || 'No registrada'}</p>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                historialContenido.innerHTML = historialHTML;
            } else {
                historialVacio.classList.remove('d-none');
                historialContenido.classList.add('d-none');
            }
            
            // Crear consulta temporal
            crearConsultaHistoria(datos.idpaciente, datos.iddoctor);
            
            // Transición suave
            listaContainer.style.display = 'none';
            historiaContainer.classList.remove('d-none');
            historiaContainer.style.width = '100%';
            
            console.log('✅ Historia clínica iniciada correctamente');
            mostrarToast('Historia clínica iniciada correctamente');
            
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 100);
            
        }).catch(error => {
            console.error('❌ Error al cargar datos para historia clínica:', error);
            mostrarToast(`Error al cargar datos: ${error.message}`, 'error');
            volverAListadoHistoria();
        });
    }

    // Funciones auxiliares de carga de datos
    async function cargarDatosPacienteHistoria(idpaciente) {
        try {
            const response = await fetch(`<?= $host ?>/controllers/paciente.controller.php?operacion=obtener&idpaciente=${idpaciente}`);
            const data = await response.json();
            
            if (data.status && data.paciente) {
                return data.paciente;
            }
            return null;
        } catch (error) {
            console.error('Error al cargar datos del paciente:', error);
            return null;
        }
    }

    async function cargarHistorialPacienteHistoria(idpaciente) {
        try {
            const response = await fetch(`<?= $host ?>/controllers/historiaclinica.controller.php?op=obtener_historial_paciente&idpaciente=${idpaciente}`);
            const data = await response.json();
            
            if (data.status && data.data) {
                return data.data;
            }
            return [];
        } catch (error) {
            console.error('Error al cargar historial del paciente:', error);
            return [];
        }
    }

    // Función para crear consulta
    function crearConsultaHistoria(idpaciente, iddoctor) {
        console.log('🏥 Creando consulta para paciente:', idpaciente);
        
        const formData = new FormData();
        formData.append('idpaciente', idpaciente);
        formData.append('iddoctor', iddoctor || doctorId);
        formData.append('fecha', $('#fecha-busqueda').val());
        formData.append('hora', new Date().toTimeString().split(' ')[0]);
        formData.append('tipo', 'CITA');

        fetch(`<?= $host ?>/controllers/consulta.controller.php?op=registrar`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status && data.idconsulta) {
                idconsultaHistoriaInput.value = data.idconsulta;
                console.log('✅ Consulta creada con ID:', data.idconsulta);
            } else {
                throw new Error('No se pudo crear la consulta');
            }
        })
        .catch(error => {
            console.error('❌ Error al crear consulta:', error);
            const tempId = 'temp_' + Date.now();
            idconsultaHistoriaInput.value = tempId;
            console.log('⚠️ Usando ID temporal:', tempId);
        });
    }

    // Funciones del wizard
    function reiniciarProcesoHistoria() {
        currentStep = 1;
        actualizarProgresoWizardHistoria(currentStep);
        
        formHistoriaNueva.reset();
        formHistoriaNueva.classList.remove('was-validated');
        
        enfermedadActualHistoria.value = '';
        examenFisicoHistoria.value = '';
        evolucionHistoria.value = '';
        observacionesHistoria.value = '';
        iddiagnosticoHistoria.value = '';
        altamedicaHistoria.checked = false;
    }

    function irPasoAnteriorHistoria() {
        if (currentStep > 1) {
            currentStep--;
            actualizarProgresoWizardHistoria(currentStep);
        } else {
            volverAListadoHistoria();
        }
    }

    function irPasoSiguienteHistoria() {
        if (currentStep === 1) {
            currentStep++;
            actualizarProgresoWizardHistoria(currentStep);
        }
    }

    function actualizarProgresoWizardHistoria(step) {
        const progressPercentage = (step / 2) * 100;
        progressBar.style.width = `${progressPercentage}%`;
        progressBar.setAttribute('aria-valuenow', progressPercentage);

        steps.forEach((stepElement, index) => {
            const stepNumber = index + 1;

            if (stepNumber < step) {
                stepElement.classList.add('completed');
                stepElement.classList.remove('active');
            } else if (stepNumber === step) {
                stepElement.classList.add('active');
                stepElement.classList.remove('completed');
            } else {
                stepElement.classList.remove('active', 'completed');
            }
        });

        stepPanels.forEach((panel, index) => {
            const panelNumber = index + 1;

            if (panelNumber === step) {
                panel.classList.add('active');
            } else {
                panel.classList.remove('active');
            }
        });

        wizardBtnAnterior.disabled = false;

        if (step === 2) {
            wizardBtnSiguiente.classList.add('d-none');
            btnFinalizarHistoria.classList.remove('d-none');
        } else {
            wizardBtnSiguiente.classList.remove('d-none');
            btnFinalizarHistoria.classList.add('d-none');
        }
    }

    function volverAListadoHistoria() {
        const hayCambios = enfermedadActualHistoria.value.trim() ||
                          examenFisicoHistoria.value.trim() ||
                          evolucionHistoria.value.trim() ||
                          observacionesHistoria.value.trim() ||
                          iddiagnosticoHistoria.value;

        if (hayCambios) {
            Swal.fire({
                title: '¿Descartar cambios?',
                text: 'Hay datos no guardados. ¿Está seguro de que desea volver al listado?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, descartar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarVolverAListadoHistoria();
                }
            });
        } else {
            ejecutarVolverAListadoHistoria();
        }
    }

    function ejecutarVolverAListadoHistoria() {
        console.log('⬅️ Volviendo al listado de pacientes');
        
        mainContainer.classList.remove('historia-mode');
        historiaContainer.classList.add('d-none');
        listaContainer.style.display = 'block';
        
        cargarPacientesCitas();
        
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 100);
    }

    function finalizarHistoriaClinicaCompleta() {
        console.log('💾 Iniciando proceso de guardar historia clínica');

        if (!formHistoriaNueva.checkValidity()) {
            formHistoriaNueva.classList.add('was-validated');

            const campoInvalido = formHistoriaNueva.querySelector(':invalid');
            if (campoInvalido) {
                campoInvalido.focus();
                campoInvalido.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            console.log('❌ Campos obligatorios incompletos');
            Swal.fire({
                title: 'Campos incompletos',
                text: 'Por favor, complete todos los campos obligatorios marcados en rojo',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        Swal.fire({
            title: '¿Guardar Historia Clínica?',
            html: `
                <div class="text-start">
                    <p>Se registrará la historia clínica con la siguiente información:</p>
                    <ul>
                        <li><strong>Paciente:</strong> ${pacienteNombreHistoria.textContent}</li>
                        <li><strong>Diagnóstico:</strong> ${iddiagnosticoHistoria.options[iddiagnosticoHistoria.selectedIndex]?.text || 'Sin diagnóstico'}</li>
                        <li><strong>Alta médica:</strong> ${altamedicaHistoria.checked ? 'Sí' : 'No'}</li>
                    </ul>
                    <p class="text-muted"><small>La historia se enviará al doctor que atiende la cita.</small></p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed) {
                procesarRegistroHistoriaClinicaCompleta();
            }
        });
    }

    function procesarRegistroHistoriaClinicaCompleta() {
        console.log('📤 Enviando datos de historia clínica al servidor');

        btnFinalizarHistoria.disabled = true;
        btnFinalizarHistoria.classList.add('btn-loading');
        btnFinalizarHistoria.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';

        const formHistoriaData = new FormData();
        formHistoriaData.append('idconsulta', idconsultaHistoriaInput.value);
        formHistoriaData.append('enfermedadactual', enfermedadActualHistoria.value);
        formHistoriaData.append('examenfisico', examenFisicoHistoria.value);
        formHistoriaData.append('evolucion', evolucionHistoria.value);
        formHistoriaData.append('observaciones', observacionesHistoria.value);
        formHistoriaData.append('iddiagnostico', iddiagnosticoHistoria.value);
        formHistoriaData.append('altamedica', altamedicaHistoria.checked ? '1' : '0');
        formHistoriaData.append('iddoctor', iddoctorCitaInput.value || doctorId);

        console.log('📊 Datos a enviar:');
        for (let [key, value] of formHistoriaData.entries()) {
            console.log(`${key}: ${value}`);
        }

        fetch(`<?= $host ?>/controllers/historiaclinica.controller.php?op=registrar_consulta_historia`, {
                method: 'POST',
                body: formHistoriaData
            })
            .then(response => {
                console.log('📡 Respuesta del servidor:', response.status);
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('📊 Respuesta procesada:', data);

                if (data.status) {
                    console.log('✅ Historia clínica guardada exitosamente');

                    Swal.fire({
                        title: '¡Historia Clínica Guardada!',
                        text: 'La historia clínica ha sido registrada correctamente y se ha enviado al doctor de la cita',
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        ejecutarVolverAListadoHistoria();
                    });

                    mostrarToast('Historia clínica guardada exitosamente');
                } else {
                    throw new Error(data.mensaje || 'Error al registrar la historia clínica');
                }
            })
            .catch(error => {
                console.error('❌ Error al procesar historia clínica:', error);

                Swal.fire({
                    title: 'Error',
                    text: error.message || 'Ha ocurrido un error al procesar la historia clínica',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });

                mostrarToast('Error al guardar la historia clínica', 'error');
            })
            .finally(() => {
                btnFinalizarHistoria.disabled = false;
                btnFinalizarHistoria.classList.remove('btn-loading');
                btnFinalizarHistoria.innerHTML = '<i class="fas fa-save me-1"></i>Guardar Historia Clínica';
            });
    }

    // Funciones para diagnósticos
    function abrirModalNuevoDiagnosticoHistoria() {
        console.log('🆕 Abriendo modal para nuevo diagnóstico');
        
        formDiagnosticoHistoria.reset();
        formDiagnosticoHistoria.classList.remove('was-validated');
        
        previewCodigo.textContent = '-';
        previewNombre.textContent = '-';
        previewDescripcion.textContent = 'Sin descripción';
        
        modalNuevoDiagnosticoHistoria.show();
        
        setTimeout(() => {
            diagNombreHistoria.focus();
        }, 500);
    }

    function actualizarPreviewHistoria() {
        previewCodigo.textContent = diagCodigoHistoria.value || '-';
        previewNombre.textContent = diagNombreHistoria.value || '-';
        previewDescripcion.textContent = diagDescripcionHistoria.value || 'Sin descripción';
    }

    function guardarNuevoDiagnosticoHistoria() {
        console.log('💾 Iniciando guardado de nuevo diagnóstico');

        if (!formDiagnosticoHistoria.checkValidity()) {
            formDiagnosticoHistoria.classList.add('was-validated');

            const campoInvalido = formDiagnosticoHistoria.querySelector(':invalid');
            if (campoInvalido) {
                campoInvalido.focus();
            }

            console.log('❌ Formulario de diagnóstico inválido');
            mostrarToast('Por favor, complete todos los campos obligatorios', 'warning');
            return;
        }

        const codigo = diagCodigoHistoria.value.trim().toUpperCase();
        const nombre = diagNombreHistoria.value.trim();
        const descripcion = diagDescripcionHistoria.value.trim();

        if (nombre.length < 3) {
            diagNombreHistoria.focus();
            console.log('❌ Nombre del diagnóstico muy corto');
            mostrarToast('El nombre debe tener al menos 3 caracteres', 'warning');
            return;
        }

        if (codigo.length < 2) {
            diagCodigoHistoria.focus();
            console.log('❌ Código del diagnóstico muy corto');
            mostrarToast('El código debe tener al menos 2 caracteres', 'warning');
            return;
        }

        const datos = {
            nombre: nombre,
            codigo: codigo,
            descripcion: descripcion || ''
        };

        console.log('📊 Datos del diagnóstico a enviar:', datos);

        const btnOriginalText = btnGuardarDiagnosticoHistoria.innerHTML;
        btnGuardarDiagnosticoHistoria.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
        btnGuardarDiagnosticoHistoria.disabled = true;

        const formData = new FormData();
        formData.append('nombre', datos.nombre);
        formData.append('codigo', datos.codigo);
        formData.append('descripcion', datos.descripcion);

        const url = `<?= $host ?>/controllers/diagnostico.controller.php?op=registrar`;
        console.log(`📡 URL guardar diagnóstico: ${url}`);

        fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log(`📡 Respuesta guardar diagnóstico: ${response.status}`);

                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }

                return response.text();
            })
            .then(responseText => {
                console.log(`📄 Respuesta RAW guardar diagnóstico:`, responseText.substring(0, 500));

                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error(`❌ Error parsing JSON respuesta: ${e.message}`);
                    throw new Error('Respuesta del servidor no válida');
                }

                console.log('📊 Datos respuesta guardado:', data);

                if (data.status === true || data.status === 'true' || data.success === true || data.estado === 'exito') {
                    console.log('✅ Diagnóstico guardado exitosamente');
                    mostrarToast('¡Diagnóstico registrado correctamente!', 'success');
                    modalNuevoDiagnosticoHistoria.hide();

                    setTimeout(() => {
                        cargarDiagnosticos();
                    }, 500);

                    const idNuevo = data.iddiagnostico || data.id || data.insertId || data.nuevo_id;
                    if (idNuevo) {
                        console.log(`🎯 Intentando seleccionar diagnóstico con ID: ${idNuevo}`);
                        setTimeout(() => {
                            setTimeout(() => {
                                for (let i = 0; i < iddiagnosticoHistoria.options.length; i++) {
                                    if (iddiagnosticoHistoria.options[i].value == idNuevo ||
                                        iddiagnosticoHistoria.options[i].text.includes(datos.codigo)) {
                                        iddiagnosticoHistoria.selectedIndex = i;
                                        iddiagnosticoHistoria.classList.add('is-valid');
                                        console.log(`✅ Diagnóstico seleccionado automáticamente`);

                                        setTimeout(() => {
                                            iddiagnosticoHistoria.classList.remove('is-valid');
                                        }, 2000);
                                        break;
                                    }
                                }
                            }, 1000);
                        }, 1500);
                    }

                    Swal.fire({
                        title: '¡Éxito!',
                        text: `Diagnóstico "${datos.codigo} - ${datos.nombre}" registrado correctamente`,
                        icon: 'success',
                        timer: 3000,
                        timerProgressBar: true,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false
                    });
                } else {
                    const errorMessage = data.mensaje || data.message || data.error || data.msg || 'Error desconocido al registrar el diagnóstico';
                    throw new Error(errorMessage);
                }
            })
            .catch(error => {
                console.error(`❌ Error completo al guardar diagnóstico: ${error.message}`);

                let mensajeError = 'Error al registrar el diagnóstico: ';

                if (error.message.includes('duplicado') || error.message.includes('existe') ||
                    error.message.includes('duplicate') || error.message.includes('UNIQUE')) {
                    mensajeError += 'El código ya existe. Use un código diferente.';
                } else if (error.message.includes('HTTP') || error.message.includes('network')) {
                    mensajeError += 'Error de conexión con el servidor.';
                } else if (error.message.includes('JSON') || error.message.includes('válida')) {
                    mensajeError += 'Error en la respuesta del servidor.';
                } else {
                    mensajeError += error.message || 'Error desconocido. Inténtelo de nuevo.';
                }

                Swal.fire({
                    title: 'Error al Guardar Diagnóstico',
                    text: mensajeError,
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });

                mostrarToast(mensajeError, 'error');
            })
            .finally(() => {
                btnGuardarDiagnosticoHistoria.innerHTML = btnOriginalText;
                btnGuardarDiagnosticoHistoria.disabled = false;
            });
    }

    // Función para mostrar toast
    function mostrarToast(mensaje, tipo = 'success') {
        const iconos = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        const colores = {
            success: 'success',
            error: 'danger',
            warning: 'warning',
            info: 'info'
        };

        const toastHtml = `
            <div class="toast show align-items-center text-white bg-${colores[tipo]} border-0" role="alert" style="min-width: 350px;">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="${iconos[tipo]} me-2"></i>${mensaje}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="$(this).closest('.toast').remove()"></button>
                </div>
            </div>
        `;
        
        $('.toast-container').append(toastHtml);
        
        setTimeout(() => {
            $('.toast-container .toast').first().remove();
        }, 5000);
    }

    // Event Listeners principales
    $('#btn-actualizar-pacientes').on('click', cargarPacientesCitas);
    $('#btn-buscar').on('click', cargarPacientesCitas);
    
    $('#btn-limpiar').on('click', function() {
        console.log('🧹 Limpiando filtros');
        $('#filtro-documento').val('');
        $('#filtro-paciente').val('');
        cargarPacientesCitas();
    });

    $('#fecha-busqueda').on('change', cargarPacientesCitas);

    $('#filtro-documento, #filtro-paciente').on('keypress', function(e) {
        if (e.which === 13) {
            cargarPacientesCitas();
        }
    });

    $(document).on('click', '.btn-ver-historial', function() {
        const idpaciente = $(this).data('idpaciente');
        if (idpaciente) {
            console.log('👁️ Redirigiendo a historial del paciente:', idpaciente);
            window.location.href = `<?= $host ?>/views/SesionEnfermeria/Paciente/historialPaciente.php?idpaciente=${idpaciente}`;
        }
    });

    $(document).on('click', '.btn-crear-historia', function() {
        const $fila = $(this).closest('tr');
        
        const nombreCompleto = $fila.find('td:first .fw-semibold').text().trim();
        const documento = $fila.find('td:eq(1)').text().trim();
        const telefono = $fila.find('td:eq(2)').text().trim();
        
        const datos = {
            idpaciente: $(this).data('idpaciente'),
            idcita: $(this).data('idcita'),
            iddoctor: $(this).data('iddoctor'),
            nombre: nombreCompleto || $(this).data('nombre'),
            documento: documento,
            telefono: telefono !== 'No disponible' ? telefono : null,
            hora: $(this).data('hora')
        };
        
        console.log('📋 Datos extraídos para historia clínica:', datos);
        iniciarHistoriaClinica(datos);
    });

    // Event listeners para navegación de historia clínica
    btnCerrarHistoria.addEventListener('click', volverAListadoHistoria);
    wizardBtnAnterior.addEventListener('click', irPasoAnteriorHistoria);
    wizardBtnSiguiente.addEventListener('click', irPasoSiguienteHistoria);
    btnFinalizarHistoria.addEventListener('click', finalizarHistoriaClinicaCompleta);

    // Event listeners para diagnósticos
    if (btnNuevoDiagnosticoHistoria) {
        btnNuevoDiagnosticoHistoria.addEventListener('click', abrirModalNuevoDiagnosticoHistoria);
    }

    if (btnGuardarDiagnosticoHistoria) {
        btnGuardarDiagnosticoHistoria.addEventListener('click', guardarNuevoDiagnosticoHistoria);
    }

    // Event listeners para previsualización en tiempo real
    diagNombreHistoria.addEventListener('input', actualizarPreviewHistoria);
    diagCodigoHistoria.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
        actualizarPreviewHistoria();
    });
    diagDescripcionHistoria.addEventListener('input', actualizarPreviewHistoria);

    // Validación en tiempo real
    $('#form-historia-nueva input, #form-historia-nueva textarea, #form-historia-nueva select').on('input change', function() {
        if (this.checkValidity()) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });

    // Inicialización
    console.log('🚀 Inicializando sistema...');
    inicializarTabla();
    cargarPacientesCitas();
    cargarDiagnosticos();
    
    console.log('🎉 Sistema completamente cargado y listo');
    
    // FUNCIONES DE DEBUG - Para testing y verificación
    
    // Función para probar la carga de alergias
    window.debugAlergias = function(idpaciente) {
        console.log('🔧 DEBUG: Testing alergias para paciente:', idpaciente);
        
        const url = `<?= $host ?>/controllers/paciente.controller.php?operacion=obtener_alergias&idpaciente=${idpaciente}`;
        console.log('📡 URL alergias:', url);
        
        fetch(url)
            .then(response => {
                console.log('📡 Status alergias:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('📄 Raw response alergias:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('📊 Parsed alergias:', data);
                    
                    if (data.status && data.alergias && data.alergias.length > 0) {
                        console.log('✅ Alergias encontradas:', data.alergias);
                        alert(`Paciente tiene ${data.alergias.length} alergias registradas`);
                    } else {
                        console.log('ℹ️ No hay alergias para este paciente');
                        alert('No hay alergias registradas para este paciente');
                    }
                } catch (e) {
                    console.error('❌ Error parsing JSON alergias:', e);
                    alert('Error en la respuesta del servidor');
                }
            })
            .catch(error => {
                console.error('❌ Error loading alergias:', error);
                alert('Error al cargar alergias: ' + error.message);
            });
    };
    
    // Función para probar la carga del historial
    window.debugHistorial = function(idpaciente) {
        console.log('🔧 DEBUG: Testing historial para paciente:', idpaciente);
        
        const url = `<?= $host ?>/controllers/historiaclinica.controller.php?op=obtener_historial_paciente&idpaciente=${idpaciente}`;
        console.log('📡 URL historial:', url);
        
        fetch(url)
            .then(response => {
                console.log('📡 Status historial:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('📄 Raw response historial:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('📊 Parsed historial:', data);
                    
                    if (data.status && data.data && data.data.length > 0) {
                        console.log('✅ Historial encontrado:', data.data.length, 'registros');
                        alert(`Paciente tiene ${data.data.length} consultas en su historial`);
                        console.table(data.data);
                    } else {
                        console.log('ℹ️ No hay historial para este paciente');
                        alert('No hay historial médico previo para este paciente');
                    }
                } catch (e) {
                    console.error('❌ Error parsing JSON historial:', e);
                    alert('Error en la respuesta del servidor');
                }
            })
            .catch(error => {
                console.error('❌ Error loading historial:', error);
                alert('Error al cargar historial: ' + error.message);
            });
    };
    
    // Función para probar ambos al mismo tiempo
    window.debugPacienteCompleto = function(idpaciente) {
        console.log('🔧 DEBUG COMPLETO para paciente:', idpaciente);
        debugAlergias(idpaciente);
        setTimeout(() => debugHistorial(idpaciente), 2000);
    };
    
    // Función para simular alergias de prueba
    window.simularAlergias = function() {
        console.log('🧪 Simulando alergias para testing...');
        
        const alergiasFalsas = [
            {
                tipoalergia: 'Medicamento',
                alergia: 'Penicilina',
                gravedad: 'Grave'
            },
            {
                tipoalergia: 'Alimento',
                alergia: 'Mariscos',
                gravedad: 'Moderada'
            },
            {
                tipoalergia: 'Medicamento',
                alergia: 'Aspirina',
                gravedad: 'Leve'
            }
        ];
        
        // Simular mostrar alergias
        alergiasListaHistoria.classList.remove('alert-info', 'alert-success');
        alergiasListaHistoria.classList.add('alert-danger', 'd-block');
        alergiasListaHistoria.classList.remove('d-none');
        
        let alergiasHTML = '<i class="fas fa-exclamation-triangle me-2"></i><strong>⚠️ ALERGIAS DE PRUEBA:</strong><ul class="mb-0 mt-2">';
        
        alergiasFalsas.forEach(alergia => {
            alergiasHTML += `<li><strong>${alergia.tipoalergia}:</strong> ${alergia.alergia} <span class="badge bg-warning text-dark">${alergia.gravedad}</span></li>`;
        });
        
        alergiasHTML += '</ul>';
        alergiasListaHistoria.innerHTML = alergiasHTML;
        
        console.log('✅ Alergias de prueba mostradas');
        
        // Mostrar alerta también
        setTimeout(() => {
            Swal.fire({
                title: '⚠️ ATENCIÓN: Alergias de Prueba',
                html: `
                    <div class="text-start">
                        <p class="text-danger fw-bold mb-3">Mostrando ${alergiasFalsas.length} alergias de prueba:</p>
                        <div class="alert alert-danger text-start">
                            <ul class="mb-0">
                                ${alergiasFalsas.map(a => `<li><strong>${a.tipoalergia}:</strong> ${a.alergia} <span class="badge bg-warning text-dark ms-1">${a.gravedad}</span></li>`).join('')}
                            </ul>
                        </div>
                        <p class="text-info mt-2 mb-0"><small><i class="fas fa-info-circle me-1"></i>Esta es una simulación para testing</small></p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#dc3545',
                width: '600px'
            });
        }, 500);
    };
    
    console.log('🔧 Funciones de debug disponibles:');
    console.log('- debugAlergias(idpaciente) - Probar carga de alergias');
    console.log('- debugHistorial(idpaciente) - Probar carga de historial');
    console.log('- debugPacienteCompleto(idpaciente) - Probar todo');
    console.log('- simularAlergias() - Mostrar alergias de prueba');
    console.log('🎯 Ejemplo de uso: debugPacienteCompleto(1)');
});
</script>

<?php
include_once "../../include/footer.php";
?>