<?php
// Incluir el encabezado del doctor que ya tiene la verificación de sesión
include_once "../../include/header.doctor.php";
date_default_timezone_set('America/Lima');
// Obtener idcolaborador (doctor) de la sesión
$idDoctor = $_SESSION['usuario']['idcolaborador'];
$fechaHoy = date('Y-m-d');
$horaActual = date('H:i:s');
?>

<div class="container-fluid px-4" id="main-container">
    <!-- Sección de título y breadcrumb que se ocultará durante la consulta -->
    <div id="page-header-section">
        <h1 class="mt-4">Realizar Cita</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="<?= $host ?>/views/include/dashboard.doctor.php">Panel de Control</a></li>
            <li class="breadcrumb-item active">Realizar Cita</li>
        </ol>
    </div>

    <!-- Sección de filtros y tabla de citas - UNIFICADO EN UN SOLO CARD -->
    <div id="lista-citas-container">
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <!-- Pestañas de navegación - MODIFICADO: Citas Programadas y Citas No Atendidas -->
                <ul class="nav nav-tabs card-header-tabs border-0 mb-0">
                    <li class="nav-item">
                        <a class="nav-link active text-white bg-primary-dark" id="tab-programadas" href="#programadas" data-bs-toggle="tab">
                            <i class="fas fa-calendar-check me-1"></i> <strong>Citas Programadas</strong>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" id="tab-no-atendidas" href="#no-atendidas" data-bs-toggle="tab">
                            <i class="fas fa-calendar-times me-1"></i> <strong>Citas No Atendidas</strong>
                        </a>
                    </li>
                </ul>

                <div>
                    <button class="btn btn-sm btn-light" id="btn-actualizar">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <!-- Contenido de las pestañas -->
                <div class="tab-content">
                    <!-- Pestaña de Citas Programadas -->
                    <div class="tab-pane fade show active" id="programadas">
                        <!-- Sección de filtros -->
                        <div class="p-3 bg-light border-bottom">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="mb-0">
                                        <label for="tipodoc" class="form-label">Tipo Documento</label>
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
                                    <div class="mb-0">
                                        <label for="nrodoc" class="form-label">Nro. Documento</label>
                                        <input type="text" class="form-control" id="nrodoc" placeholder="Ingrese documento">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-0">
                                        <label for="paciente" class="form-label">Nombre/Apellido</label>
                                        <input type="text" class="form-control" id="paciente" placeholder="Buscar paciente">
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <div class="mb-0 w-100 text-end">
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

                        <!-- Información de citas y controles -->
                        <div class="px-3 py-2 bg-success-subtle border-bottom d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-secondary me-2"><?= date('d/m/Y') ?></span>
                                <span class="badge bg-primary-subtle text-primary" id="total-citas-programadas">0 citas</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <label class="me-2">Mostrar</label>
                                <select id="registros-por-pagina" class="form-select form-select-sm" style="width: auto;">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="ms-2">registros</span>
                            </div>
                        </div>

                        <!-- Tabla de citas programadas -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0" id="tabla-citas-programadas">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hora</th>
                                        <th>Paciente</th>
                                        <th>Tipo Doc</th>
                                        <th>Número Documento</th>
                                        <th>Estado</th>
                                        <th>Receta</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos se cargarán dinámicamente -->
                                </tbody>
                            </table>
                        </div>

                        <div id="sin-citas-programadas" class="text-center p-4 d-none">
                            <img src="<?= $host ?>/assets/img/no-appointments.svg" alt="No hay citas" class="img-fluid mb-3" style="max-height: 150px;">
                            <h5 class="text-muted">No hay citas programadas para este día o filtro</h5>
                        </div>

                        <!-- Controles de paginación para citas programadas -->
                        <div class="pagination-container d-flex justify-content-between align-items-center p-3 bg-light">
                            <div id="pagination-info-programadas">Mostrando 1 a 10 de 0 registros</div>
                            <div class="pagination-controls">
                                <ul class="pagination mb-0" id="paginacion-programadas">
                                    <li class="page-item disabled" id="paginacion-anterior-programadas">
                                        <a class="page-link" href="#" aria-label="Anterior">
                                            <span aria-hidden="true">Anterior</span>
                                        </a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#" data-page="1">1</a></li>
                                    <!-- Más páginas se generarán dinámicamente -->
                                    <li class="page-item disabled" id="paginacion-siguiente-programadas">
                                        <a class="page-link" href="#" aria-label="Siguiente">
                                            <span aria-hidden="true">Siguiente</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Pestaña de Citas No Atendidas -->
                    <div class="tab-pane fade" id="no-atendidas">
                        <!-- Sección de filtros para no atendidas -->
                        <div class="p-3 bg-light border-bottom">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="mb-0">
                                        <label for="tipodoc-no-atendidas" class="form-label">Tipo Documento</label>
                                        <select class="form-select" id="tipodoc-no-atendidas">
                                            <option value="">Todos</option>
                                            <option value="DNI">DNI</option>
                                            <option value="PASAPORTE">Pasaporte</option>
                                            <option value="CARNET DE EXTRANJERIA">Carnet de Extranjería</option>
                                            <option value="OTRO">Otro</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-0">
                                        <label for="nrodoc-no-atendidas" class="form-label">Nro. Documento</label>
                                        <input type="text" class="form-control" id="nrodoc-no-atendidas" placeholder="Ingrese documento">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-0">
                                        <label for="paciente-no-atendidas" class="form-label">Nombre/Apellido</label>
                                        <input type="text" class="form-control" id="paciente-no-atendidas" placeholder="Buscar paciente">
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <div class="mb-0 w-100 text-end">
                                        <button class="btn btn-primary" id="btn-buscar-no-atendidas">
                                            <i class="fas fa-search me-1"></i> Buscar
                                        </button>
                                        <button class="btn btn-secondary" id="btn-limpiar-no-atendidas">
                                            <i class="fas fa-broom me-1"></i> Limpiar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información de citas no atendidas y controles -->
                        <div class="px-3 py-2 bg-warning-subtle border-bottom d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-secondary me-2"><?= date('d/m/Y') ?></span>
                                <span class="badge bg-warning-subtle text-warning" id="total-citas-no-atendidas">0 citas</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <label class="me-2">Mostrar</label>
                                <select id="registros-por-pagina-no-atendidas" class="form-select form-select-sm" style="width: auto;">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="ms-2">registros</span>
                            </div>
                        </div>

                        <!-- Tabla de citas no atendidas -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0" id="tabla-citas-no-atendidas">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hora</th>
                                        <th>Paciente</th>
                                        <th>Tipo Doc</th>
                                        <th>Número Documento</th>
                                        <th>Estado</th>
                                        <th>Receta</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos se cargarán dinámicamente -->
                                </tbody>
                            </table>
                        </div>

                        <div id="sin-citas-no-atendidas" class="text-center p-4 d-none">
                            <img src="<?= $host ?>/assets/img/no-appointments.svg" alt="No hay citas" class="img-fluid mb-3" style="max-height: 150px;">
                            <h5 class="text-muted">No hay citas no atendidas para este día o filtro</h5>
                        </div>

                        <!-- Controles de paginación para citas no atendidas -->
                        <div class="pagination-container d-flex justify-content-between align-items-center p-3 bg-light">
                            <div id="pagination-info-no-atendidas">Mostrando 1 a 10 de 0 registros</div>
                            <div class="pagination-controls">
                                <ul class="pagination mb-0" id="paginacion-no-atendidas">
                                    <li class="page-item disabled" id="paginacion-anterior-no-atendidas">
                                        <a class="page-link" href="#" aria-label="Anterior">
                                            <span aria-hidden="true">Anterior</span>
                                        </a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#" data-page="1">1</a></li>
                                    <!-- Más páginas se generarán dinámicamente -->
                                    <li class="page-item disabled" id="paginacion-siguiente-no-atendidas">
                                        <a class="page-link" href="#" aria-label="Siguiente">
                                            <span aria-hidden="true">Siguiente</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección para realizar consulta - Inicialmente oculta - MODIFICADO: añadido margen inferior -->
    <div id="consulta-container" class="d-none w-100 mt-4 mb-5">
        <div class="consulta-modal-wrapper pt-3 px-3 pb-4">
            <div class="card shadow consulta-card">
                <div class="card-header bg-gradient-primary-to-secondary text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="modal-title mb-0 ps-2">
                        <i class="fas fa-stethoscope me-2"></i>Realizar Consulta Cita
                    </h5>
                    <button type="button" class="btn-close btn-close-white me-2" id="btn-cerrar-consulta" aria-label="Close"></button>
                </div>
                <div class="card-body p-0">
                    <input type="hidden" id="idconsulta" name="idconsulta">
                    <input type="hidden" id="idcita" name="idcita">
                    <input type="hidden" id="idpaciente" name="idpaciente">

                    <!-- Cabecera con datos del paciente -->
                    <div class="paciente-info-header p-4 mb-2 bg-gradient-light-to-secondary border-bottom">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center mb-3 mb-md-0">
                                <div class="avatar-circle">
                                    <i class="fas fa-user-circle fa-4x text-primary"></i>
                                </div>
                            </div>
                            <div class="col-md-10">
                                <h4 id="paciente-nombre" class="mb-2 text-primary fw-bold"></h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><i class="fas fa-id-card text-secondary me-2"></i><span id="paciente-documento"></span></p>
                                        <p class="mb-1"><i class="fas fa-phone text-secondary me-2"></i><span id="paciente-telefono"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <div id="alergias-container">
                                            <div id="alergias-lista" class="alert alert-warning d-none mb-0">
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
                                <div class="progress-bar" role="progressbar" style="width: 33%;" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="progress-steps">
                                <div class="step active" data-step="1">
                                    <div class="step-circle">1</div>
                                    <div class="step-text">Revisar Historial</div>
                                </div>
                                <div class="step" data-step="2">
                                    <div class="step-circle">2</div>
                                    <div class="step-text">Nueva Consulta</div>
                                </div>
                                <div class="step" data-step="3">
                                    <div class="step-circle">3</div>
                                    <div class="step-text">Receta/Tratamiento</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de pasos -->
                    <div class="step-panels">
                        <!-- Paso 1: Revisar Historial -->
                        <div class="step-panel active" id="step-1">
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

                        <!-- Paso 2: Nueva Consulta -->
                        <div class="step-panel" id="step-2">
                            <div class="p-4">
                                <div class="documento-like">
                                    <div class="doc-header">
                                        <div class="doc-title">
                                            <h1>Nueva Historia Clínica</h1>
                                        </div>
                                        <div class="doc-date">
                                            Fecha: <span id="fecha-actual"><?= date('d/m/Y') ?></span>
                                        </div>
                                    </div>

                                    <div class="doc-content">
                                        <form id="form-historia" class="needs-validation" novalidate>
                                            <div class="doc-section mb-4">
                                                <h2>Enfermedad Actual</h2>
                                                <div class="form-group">
                                                    <textarea class="form-control doc-control" id="enfermedadactual" name="enfermedadactual" rows="4" required></textarea>
                                                    <div class="invalid-feedback">Por favor, describa la enfermedad actual.</div>
                                                </div>
                                            </div>

                                            <div class="doc-section mb-4">
                                                <h2>Examen Físico</h2>
                                                <div class="form-group">
                                                    <textarea class="form-control doc-control" id="examenfisico" name="examenfisico" rows="4" required></textarea>
                                                    <div class="invalid-feedback">Por favor, complete el examen físico.</div>
                                                </div>
                                            </div>

                                            <div class="doc-section mb-4">
                                                <h2>Evolución</h2>
                                                <div class="form-group">
                                                    <textarea class="form-control doc-control" id="evolucion" name="evolucion" rows="4" required></textarea>
                                                    <div class="invalid-feedback">Por favor, complete la evolución.</div>
                                                </div>
                                            </div>

                                            <div class="doc-section mb-4">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <h2>Diagnóstico</h2>
                                                        <div class="input-group mb-3">
                                                            <select class="form-select doc-control" id="iddiagnostico" name="iddiagnostico" required>
                                                                <option value="">Seleccione diagnóstico</option>
                                                                <!-- Opciones cargadas desde la base de datos -->
                                                            </select>
                                                            <button class="btn btn-primary" type="button" id="btn-nuevo-diagnostico">
                                                                <i class="fas fa-plus"></i> Nuevo
                                                            </button>
                                                            <div class="invalid-feedback">Por favor, seleccione un diagnóstico.</div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-check mt-4">
                                                            <input class="form-check-input" type="checkbox" id="altamedica" name="altamedica" value="1">
                                                            <label class="form-check-label fw-bold" for="altamedica">
                                                                Alta Médica
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 3: Receta/Tratamiento -->
                        <div class="step-panel" id="step-3">
                            <div class="p-4">
                                <div class="documento-like">
                                    <div class="doc-header">
                                        <div class="doc-title">
                                            <h1>Receta Médica</h1>
                                        </div>
                                        <div class="doc-date">
                                            Fecha: <span id="fecha-receta"><?= date('d/m/Y') ?></span>
                                        </div>
                                    </div>

                                    <div class="doc-content">
                                        <form id="form-receta" class="needs-validation" novalidate>
                                            <div class="doc-section mb-4">
                                                <h2>Información del Médico</h2>
                                                <p><strong>Dr./Dra.:</strong> <?= $_SESSION['usuario']['nombres'] . ' ' . $_SESSION['usuario']['apellidos'] ?></p>
                                                <p><strong>Especialidad:</strong> <?= $_SESSION['usuario']['especialidad'] ?? 'No especificada' ?></p>
                                            </div>

                                            <div class="doc-section mb-4">
                                                <h2>Medicamentos</h2>

                                                <div class="med-add-form mb-3">
                                                    <div class="row g-3 align-items-end">
                                                        <div class="col-md-5">
                                                            <label for="medicacion" class="form-label">Medicamento</label>
                                                            <input type="text" class="form-control doc-control" id="medicacion" name="medicacion">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="cantidad" class="form-label">Cantidad</label>
                                                            <input type="text" class="form-control doc-control" id="cantidad" name="cantidad">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="frecuencia" class="form-label">Frecuencia</label>
                                                            <input type="text" class="form-control doc-control" id="frecuencia" name="frecuencia">
                                                        </div>
                                                        <div class="col-md-1">
                                                            <button type="button" class="btn btn-success w-100" id="btn-agregar-medicamento">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="medicamentos-lista mb-4">
                                                    <div class="alert alert-info" id="medicamentos-vacio">
                                                        <i class="fas fa-info-circle me-2"></i>No se han agregado medicamentos aún.
                                                    </div>
                                                    <div class="table-responsive d-none" id="tabla-medicamentos-container">
                                                        <table class="table table-hover" id="tabla-medicamentos">
                                                            <thead>
                                                                <tr>
                                                                    <th>Medicamento</th>
                                                                    <th>Cantidad</th>
                                                                    <th>Frecuencia</th>
                                                                    <th class="text-center">Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="lista-medicamentos">
                                                                <!-- Aquí se agregará dinámicamente los medicamentos -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="doc-section mb-4">
                                                <h2>Tratamiento Adicional</h2>

                                                <div class="trat-add-form mb-3">
                                                    <div class="row g-3 align-items-end">
                                                        <div class="col-md-4">
                                                            <label for="trat-medicacion" class="form-label">Medicamento</label>
                                                            <input type="text" class="form-control doc-control" id="trat-medicacion" name="trat-medicacion">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="trat-dosis" class="form-label">Dosis</label>
                                                            <input type="text" class="form-control doc-control" id="trat-dosis" name="trat-dosis">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label for="trat-frecuencia" class="form-label">Frecuencia</label>
                                                            <input type="text" class="form-control doc-control" id="trat-frecuencia" name="trat-frecuencia">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label for="trat-duracion" class="form-label">Duración</label>
                                                            <input type="text" class="form-control doc-control" id="trat-duracion" name="trat-duracion">
                                                        </div>
                                                        <div class="col-md-1">
                                                            <button type="button" class="btn btn-primary w-100" id="btn-agregar-tratamiento">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="tratamientos-lista mb-4">
                                                    <div class="alert alert-info" id="tratamientos-vacio">
                                                        <i class="fas fa-info-circle me-2"></i>No se han agregado tratamientos adicionales.
                                                    </div>
                                                    <div class="table-responsive d-none" id="tabla-tratamientos-container">
                                                        <table class="table table-hover" id="tabla-tratamientos">
                                                            <thead>
                                                                <tr>
                                                                    <th>Medicamento</th>
                                                                    <th>Dosis</th>
                                                                    <th>Frecuencia</th>
                                                                    <th>Duración</th>
                                                                    <th class="text-center">Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="lista-tratamientos">
                                                                <!-- Aquí se agregará dinámicamente los tratamientos -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="doc-section mb-4">
                                                <h2>Observaciones</h2>
                                                <div class="form-group">
                                                    <textarea class="form-control doc-control" id="observaciones" name="observaciones" rows="3"></textarea>
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
                                <button type="button" class="btn btn-success d-none" id="btn-finalizar">
                                    <i class="fas fa-save me-1"></i>Finalizar Consulta
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
<div class="modal fade" id="modalNuevoDiagnostico" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-warm-to-orange text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>Nuevo Diagnóstico
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-diagnostico" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="diag-nombre" class="form-label">Nombre del diagnóstico</label>
                        <input type="text" class="form-control" id="diag-nombre" required>
                        <div class="invalid-feedback">Por favor, ingrese el nombre del diagnóstico.</div>
                    </div>
                    <div class="mb-3">
                        <label for="diag-codigo" class="form-label">Código</label>
                        <input type="text" class="form-control" id="diag-codigo" required>
                        <div class="invalid-feedback">Por favor, ingrese el código del diagnóstico.</div>
                        <div class="form-text">Ejemplo: A00.1, B27, F32.9</div>
                    </div>
                    <div class="mb-3">
                        <label for="diag-descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="diag-descripcion" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-guardar-diagnostico">
                    <i class="fas fa-save me-1"></i>Guardar Diagnóstico
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de alerta de alergia -->
<div class="modal fade" id="modalAlergiaAlerta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Alerta de Alergia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-allergies fa-3x text-danger mb-3"></i>
                    <h4 class="mb-3">El paciente puede ser alérgico a este medicamento</h4>
                    <p class="mb-0">Se ha detectado que el paciente tiene una alergia que podría estar relacionada con este medicamento:</p>
                </div>

                <div class="alert alert-danger" id="alergia-detalles">
                    <!-- Contenido dinámico de la alergia -->
                </div>

                <p>¿Está seguro de que desea agregar este medicamento a la receta?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-alergia">
                    <i class="fas fa-exclamation-circle me-1"></i>Agregar de todos modos
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para reprogramar cita -->
<div class="modal fade" id="modalReprogramarCita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-alt me-2"></i>Reprogramar Cita
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-reprogramar" class="needs-validation" novalidate>
                    <input type="hidden" id="reprogramar-idcita" name="idcita">
                    <div class="mb-3">
                        <label for="reprogramar-fecha" class="form-label">Nueva Fecha</label>
                        <input type="date" class="form-control" id="reprogramar-fecha" name="fecha" required>
                        <div class="invalid-feedback">Por favor, seleccione una fecha.</div>
                    </div>
                    <div class="mb-3">
                        <label for="reprogramar-hora" class="form-label">Nueva Hora</label>
                        <select class="form-select" id="reprogramar-hora" name="hora" required>
                            <option value="">Seleccione una hora</option>
                            <!-- Las horas disponibles se cargarán dinámicamente -->
                        </select>
                        <div class="invalid-feedback">Por favor, seleccione una hora.</div>
                    </div>
                    <div class="mb-3">
                        <label for="reprogramar-observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="reprogramar-observaciones" name="observaciones" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-guardar-reprogramacion">
                    <i class="fas fa-save me-1"></i>Guardar Reprogramación
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Gradientes personalizados */
    .bg-gradient-primary-to-secondary {
        background: linear-gradient(135deg, #2c3e50 0%, #4ca1af 100%);
    }

    .bg-gradient-warm-to-orange {
        background: linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%);
    }

    .bg-gradient-cool-to-warm {
        background: linear-gradient(135deg, #5c258d 0%, #4389a2 100%);
    }

    .bg-gradient-blue-to-purple {
        background: linear-gradient(135deg, #373b44 0%, #4286f4 100%);
    }

    .bg-gradient-purple-to-blue {
        background: linear-gradient(135deg, #654ea3 0%, #eaafc8 100%);
    }

    .bg-gradient-light-to-secondary {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    /* Estilos mejorados para pestañas en el encabezado */
    .card-header-tabs .nav-link {
        padding: 0.75rem 1.25rem;
        margin-right: 0.25rem;
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
        transition: all 0.2s ease;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.7) !important;
    }

    .card-header-tabs .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.3);
        color: #ffffff !important;
    }

    .card-header-tabs .nav-link.active {
        background-color: rgba(0, 0, 0, 0.4) !important;
        color: #ffffff !important;
        box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
    }

    .bg-primary-dark {
        background-color: rgba(0, 0, 0, 0.4) !important;
    }

    .bg-primary-subtle {
        background-color: rgba(13, 110, 253, 0.1);
    }

    .bg-success-subtle {
        background-color: rgba(25, 135, 84, 0.1);
    }

    .bg-warning-subtle {
        background-color: rgba(255, 193, 7, 0.1);
    }

    /* Contenedor de consulta modal - NUEVO */
    .consulta-modal-wrapper {
        max-width: 95%;
        margin: 0 auto;
        position: relative;
    }

    .consulta-card {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    /* Cabecera de paciente mejorada - MODIFICADO */
    .paciente-info-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        border-radius: 0;
    }

    /* Avatar circular */
    .avatar-circle {
        width: 85px;
        height: 85px;
        border-radius: 50%;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        border: 2px solid #e9ecef;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Estilo para el documento tipo Word */
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
        /* Asegurar altura mínima */
    }

    /* Tablas */
    .table thead th {
        font-size: 0.9rem;
        font-weight: 600;
        background-color: #f8f9fa;
    }

    .table-medicamentos tbody tr,
    .table-tratamientos tbody tr {
        transition: all 0.2s ease;
    }

    .table-medicamentos tbody tr:hover,
    .table-tratamientos tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Animaciones */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .highlight-warning {
        animation: pulse 0.5s ease-in-out;
        background-color: #fff3cd !important;
    }

    .highlight-success {
        animation: pulse 0.5s ease-in-out;
        background-color: #d1e7dd !important;
    }

    /* Estilos para el historial médico */
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

    /* Estilos para mensajes de guardado exitoso */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }

    .toast {
        min-width: 300px;
        background-color: rgba(255, 255, 255, 0.95);
        border-left: 4px solid #28a745;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }

    /* Animación para la transición entre listas de citas y consulta */
    .fade-transition {
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .fade-out {
        opacity: 0;
        transform: translateY(-10px);
    }

    .fade-in {
        opacity: 1;
        transform: translateY(0);
    }

    /* Aseguramos que el consulta-container ocupe todo el espacio disponible */
    #consulta-container {
        width: 100%;
        max-width: 100%;
    }

    /* Mejorar la responsividad */
    @media (max-width: 992px) {
        .doc-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .doc-date {
            margin-top: 0.5rem;
        }

        .progress-steps {
            padding: 0 1rem;
        }

        .step-text {
            font-size: 0.7rem;
        }
    }

    /* Mejorar el escalado en dispositivos móviles */
    @media (max-width: 768px) {
        .card-body {
            padding: 0.5rem;
        }

        .documento-like {
            padding: 1rem;
        }

        .step-circle {
            width: 2rem;
            height: 2rem;
        }

        .consulta-modal-wrapper {
            max-width: 100%;
            padding: 0.5rem;
        }
    }

    /* Añadir estilo para el botón de actualizar */
    .btn-refreshing {
        pointer-events: none;
        opacity: 0.8;
    }

    /* Indicador de carga más sutil para tablas */
    .loading-indicator {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 1rem;
        background-color: rgba(255, 255, 255, 0.7);
        border-radius: 0.25rem;
    }

    /* Animación para actualización suave */
    @keyframes tableRefresh {
        0% {
            opacity: 0.7;
        }

        50% {
            opacity: 0.9;
        }

        100% {
            opacity: 1;
        }
    }

    .table-refreshing {
        animation: tableRefresh 0.5s ease;
    }

    /* Estilos para la paginación */
    .pagination-container {
        font-size: 0.9rem;
    }

    .pagination-info {
        color: #6c757d;
    }

    .pagination .page-link {
        color: #4ca1af;
        background-color: #fff;
        border-color: #dee2e6;
        padding: 0.375rem 0.75rem;
    }

    .pagination .page-item.active .page-link {
        background-color: #4ca1af;
        border-color: #4ca1af;
        color: white;
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
        border-color: #dee2e6;
    }

    /* Estilos para el selector de registros por página */
    #registros-por-pagina,
    #registros-por-pagina-no-atendidas {
        min-width: 70px;
    }

    /* Ocultar elementos del header en modo de consulta */
    .consulta-mode #page-header-section {
        display: none !important;
    }

    /* Estilos mejorados para botones en el listado de citas no atendidas */
    .btn-group {
        display: flex;
        gap: 5px;
    }

    .btn-realizar-consulta-tardio {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-realizar-consulta-tardio:hover {
        background-color: #0069d9;
        border-color: #0062cc;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Referencias a elementos del DOM - Sección de citas programadas
        const tipodocSelect = document.getElementById('tipodoc');
        const nrodocInput = document.getElementById('nrodoc');
        const pacienteInput = document.getElementById('paciente');
        const btnBuscar = document.getElementById('btn-buscar');
        const btnLimpiar = document.getElementById('btn-limpiar');
        const tablaCitasProgramadas = document.getElementById('tabla-citas-programadas');
        const tablaCitasProgramadasBody = tablaCitasProgramadas.querySelector('tbody');
        const sinCitasProgramadas = document.getElementById('sin-citas-programadas');
        const totalCitasProgramadas = document.getElementById('total-citas-programadas');

        // Referencias a elementos del DOM - Sección de citas no atendidas
        const tipodocNoAtendidasSelect = document.getElementById('tipodoc-no-atendidas');
        const nrodocNoAtendidasInput = document.getElementById('nrodoc-no-atendidas');
        const pacienteNoAtendidasInput = document.getElementById('paciente-no-atendidas');
        const btnBuscarNoAtendidas = document.getElementById('btn-buscar-no-atendidas');
        const btnLimpiarNoAtendidas = document.getElementById('btn-limpiar-no-atendidas');
        const tablaCitasNoAtendidas = document.getElementById('tabla-citas-no-atendidas');
        const tablaCitasNoAtendidasBody = tablaCitasNoAtendidas.querySelector('tbody');
        const sinCitasNoAtendidas = document.getElementById('sin-citas-no-atendidas');
        const totalCitasNoAtendidas = document.getElementById('total-citas-no-atendidas');

        // Referencias para paginación - Citas programadas
        const registrosPorPaginaSelect = document.getElementById('registros-por-pagina');
        const paginationInfoProgramadas = document.getElementById('pagination-info-programadas');
        const paginacionProgramadas = document.getElementById('paginacion-programadas');

        // Referencias para paginación - Citas no atendidas
        const registrosPorPaginaNoAtendidasSelect = document.getElementById('registros-por-pagina-no-atendidas');
        const paginationInfoNoAtendidas = document.getElementById('pagination-info-no-atendidas');
        const paginacionNoAtendidas = document.getElementById('paginacion-no-atendidas');

        // Referencias para pestañas
        const tabProgramadas = document.getElementById('tab-programadas');
        const tabNoAtendidas = document.getElementById('tab-no-atendidas');

        // Botón común de actualización
        const btnActualizar = document.getElementById('btn-actualizar');

        // Variables de paginación - Citas programadas
        let paginaActualProgramadas = 1;
        let registrosPorPaginaProgramadas = 10;
        let totalRegistrosProgramadas = 0;
        let totalPaginasProgramadas = 0;
        let citasCompletasProgramadas = [];

        // Variables de paginación - Citas no atendidas
        let paginaActualNoAtendidas = 1;
        let registrosPorPaginaNoAtendidas = 10;
        let totalRegistrosNoAtendidas = 0;
        let totalPaginasNoAtendidas = 0;
        let citasCompletasNoAtendidas = [];

        // Contenedores principales
        const mainContainer = document.getElementById('main-container');
        const pageHeaderSection = document.getElementById('page-header-section');
        const listaCitasContainer = document.getElementById('lista-citas-container');
        const consultaContainer = document.getElementById('consulta-container');
        const btnCerrarConsulta = document.getElementById('btn-cerrar-consulta');

        // Elementos de consulta
        const idconsultaInput = document.getElementById('idconsulta');
        const idcitaInput = document.getElementById('idcita');
        const idpacienteInput = document.getElementById('idpaciente');
        const pacienteNombre = document.getElementById('paciente-nombre');
        const pacienteDocumento = document.getElementById('paciente-documento');
        const pacienteTelefono = document.getElementById('paciente-telefono');
        const alergiasLista = document.getElementById('alergias-lista');

        // Navegación por pasos
        const progressBar = document.querySelector('.progress-bar');
        const steps = document.querySelectorAll('.step');
        const stepPanels = document.querySelectorAll('.step-panel');
        const wizardBtnAnterior = document.getElementById('wizard-btn-anterior');
        const wizardBtnSiguiente = document.getElementById('wizard-btn-siguiente');
        const btnFinalizar = document.getElementById('btn-finalizar');

        // Historial
        const historialVacio = document.getElementById('historial-vacio');
        const historialContenido = document.getElementById('historial-contenido');

        // Nueva historia
        const formHistoria = document.getElementById('form-historia');
        const enfermedadActual = document.getElementById('enfermedadactual');
        const examenFisico = document.getElementById('examenfisico');
        const evolucion = document.getElementById('evolucion');
        const iddiagnostico = document.getElementById('iddiagnostico');
        const altamedica = document.getElementById('altamedica');

        // Modal nuevo diagnóstico
        const btnNuevoDiagnostico = document.getElementById('btn-nuevo-diagnostico');
        const modalNuevoDiagnostico = new bootstrap.Modal(document.getElementById('modalNuevoDiagnostico'));
        const formDiagnostico = document.getElementById('form-diagnostico');
        const diagNombre = document.getElementById('diag-nombre');
        const diagCodigo = document.getElementById('diag-codigo');
        const diagDescripcion = document.getElementById('diag-descripcion');
        const btnGuardarDiagnostico = document.getElementById('btn-guardar-diagnostico');

        // Receta médica
        const formReceta = document.getElementById('form-receta');
        const medicacion = document.getElementById('medicacion');
        const cantidad = document.getElementById('cantidad');
        const frecuencia = document.getElementById('frecuencia');
        const observaciones = document.getElementById('observaciones');
        const btnAgregarMedicamento = document.getElementById('btn-agregar-medicamento');
        const medicamentosVacio = document.getElementById('medicamentos-vacio');
        const tablaMedicamentosContainer = document.getElementById('tabla-medicamentos-container');
        const listaMedicamentos = document.getElementById('lista-medicamentos');

        // Tratamiento
        const tratMedicacion = document.getElementById('trat-medicacion');
        const tratDosis = document.getElementById('trat-dosis');
        const tratFrecuencia = document.getElementById('trat-frecuencia');
        const tratDuracion = document.getElementById('trat-duracion');
        const btnAgregarTratamiento = document.getElementById('btn-agregar-tratamiento');
        const tratamientosVacio = document.getElementById('tratamientos-vacio');
        const tablaTratamientosContainer = document.getElementById('tabla-tratamientos-container');
        const listaTratamientos = document.getElementById('lista-tratamientos');

        // Modal de alerta de alergia
        const modalAlergiaAlerta = new bootstrap.Modal(document.getElementById('modalAlergiaAlerta'));
        const alergiaDetalles = document.getElementById('alergia-detalles');
        const btnConfirmarAlergia = document.getElementById('btn-confirmar-alergia');

        // Modal de reprogramar cita
        const modalReprogramarCita = new bootstrap.Modal(document.getElementById('modalReprogramarCita'));
        const formReprogramar = document.getElementById('form-reprogramar');
        const reprogramarIdCita = document.getElementById('reprogramar-idcita');
        const reprogramarFecha = document.getElementById('reprogramar-fecha');
        const reprogramarHora = document.getElementById('reprogramar-hora');
        const reprogramarObservaciones = document.getElementById('reprogramar-observaciones');
        const btnGuardarReprogramacion = document.getElementById('btn-guardar-reprogramacion');

        // Variables para almacenar datos
        let currentStep = 1;
        let alergiasPaciente = [];
        let medicamentosAgregados = [];
        let tratamientosAgregados = [];
        let medicamentoEnAlerta = null;
        let tratamientoEnAlerta = null;

        // Obtener fecha actual en formato YYYY-MM-DD
        const fechaHoy = '<?= $fechaHoy ?>';
        const horaActual = '<?= $horaActual ?>';

        // Eventos para cambiar registros por página - Citas programadas
        registrosPorPaginaSelect.addEventListener('change', function() {
            registrosPorPaginaProgramadas = parseInt(this.value);
            paginaActualProgramadas = 1; // Reiniciar a primera página

            // Recalcular el total de páginas
            totalPaginasProgramadas = Math.ceil(totalRegistrosProgramadas / registrosPorPaginaProgramadas);

            if (citasCompletasProgramadas.length > 0) {
                mostrarCitasProgramadasPaginadas();
            } else {
                cargarCitasPorFecha(fechaHoy, true);
            }
        });

        // Eventos para cambiar registros por página - Citas no atendidas
        registrosPorPaginaNoAtendidasSelect.addEventListener('change', function() {
            registrosPorPaginaNoAtendidas = parseInt(this.value);
            paginaActualNoAtendidas = 1; // Reiniciar a primera página

            // Recalcular el total de páginas
            totalPaginasNoAtendidas = Math.ceil(totalRegistrosNoAtendidas / registrosPorPaginaNoAtendidas);

            if (citasCompletasNoAtendidas.length > 0) {
                mostrarCitasNoAtendidasPaginadas();
            } else {
                cargarCitasNoAtendidas(fechaHoy, true);
            }
        });

        // Eventos para navegación de paginación - Citas programadas
        paginacionProgramadas.addEventListener('click', function(e) {
            e.preventDefault();

            const target = e.target.closest('a.page-link');
            if (!target) return;

            if (target.hasAttribute('data-page')) {
                const pagina = parseInt(target.getAttribute('data-page'));
                if (pagina !== paginaActualProgramadas) {
                    paginaActualProgramadas = pagina;
                    mostrarCitasProgramadasPaginadas();
                }
            } else if (target.closest('.page-item#paginacion-anterior-programadas') && !target.closest('.page-item').classList.contains('disabled')) {
                paginaActualProgramadas--;
                mostrarCitasProgramadasPaginadas();
            } else if (target.closest('.page-item#paginacion-siguiente-programadas') && !target.closest('.page-item').classList.contains('disabled')) {
                paginaActualProgramadas++;
                mostrarCitasProgramadasPaginadas();
            }
        });

        // Eventos para navegación de paginación - Citas no atendidas
        paginacionNoAtendidas.addEventListener('click', function(e) {
            e.preventDefault();

            const target = e.target.closest('a.page-link');
            if (!target) return;

            if (target.hasAttribute('data-page')) {
                const pagina = parseInt(target.getAttribute('data-page'));
                if (pagina !== paginaActualNoAtendidas) {
                    paginaActualNoAtendidas = pagina;
                    mostrarCitasNoAtendidasPaginadas();
                }
            } else if (target.closest('.page-item#paginacion-anterior-no-atendidas') && !target.closest('.page-item').classList.contains('disabled')) {
                paginaActualNoAtendidas--;
                mostrarCitasNoAtendidasPaginadas();
            } else if (target.closest('.page-item#paginacion-siguiente-no-atendidas') && !target.closest('.page-item').classList.contains('disabled')) {
                paginaActualNoAtendidas++;
                mostrarCitasNoAtendidasPaginadas();
            }
        });

        // Eventos para cambiar de pestañas
        tabProgramadas.addEventListener('click', function(e) {
            e.preventDefault();

            // Activar la pestaña de citas programadas
            tabProgramadas.classList.add('active', 'bg-primary-dark');
            tabNoAtendidas.classList.remove('active', 'bg-primary-dark');

            // Verificar citas no asistidas antes de cargar la lista
            verificarYActualizarCitasNoAsistidas();

            // Recalcular paginación si es necesario
            if (citasCompletasProgramadas.length > 0) {
                mostrarCitasProgramadasPaginadas();
            } else {
                cargarCitasPorFecha(fechaHoy, true);
            }
        });

        tabNoAtendidas.addEventListener('click', function(e) {
            e.preventDefault();

            // Activar la pestaña de citas no atendidas
            tabNoAtendidas.classList.add('active', 'bg-primary-dark');
            tabProgramadas.classList.remove('active', 'bg-primary-dark');

            // Verificar citas no asistidas antes de cargar la lista
            verificarYActualizarCitasNoAsistidas();

            // Recalcular paginación si es necesario
            if (citasCompletasNoAtendidas.length > 0) {
                mostrarCitasNoAtendidasPaginadas();
            } else {
                cargarCitasNoAtendidas(fechaHoy, true);
            }
        });

        // Cargar diagnósticos
        cargarDiagnosticos();

        // Cargar citas del día actual al iniciar
        cargarCitasPorFecha(fechaHoy, true);
        cargarCitasNoAtendidas(fechaHoy, false);

        // Iniciar verificación automática de citas no asistidas
        verificarYActualizarCitasNoAsistidas();

        // Programar verificación cada 1 minuto (60000 ms) en lugar de cada 5 minutos
        setInterval(verificarYActualizarCitasNoAsistidas, 60000);

        // Event Listeners para citas programadas
        btnBuscar.addEventListener('click', () => {
            const tipodoc = tipodocSelect.value;
            const nrodoc = nrodocInput.value;
            const paciente = pacienteInput.value;

            // Construir filtros
            let filtros = {};
            filtros.fecha = fechaHoy; // Siempre usar la fecha actual
            if (tipodoc) filtros.tipodoc = tipodoc;
            if (nrodoc) filtros.nrodoc = nrodoc;
            if (paciente) filtros.paciente = paciente;
            filtros.estado = 'PROGRAMADA'; // Solo mostrar citas programadas

            // Reiniciar paginación
            paginaActualProgramadas = 1;

            // Solo mostrar cargando si es una búsqueda compleja
            const esBusquedaCompleja = nrodoc || paciente;
            buscarCitasConFiltros(filtros, esBusquedaCompleja, false);
        });

        btnLimpiar.addEventListener('click', () => {
            tipodocSelect.value = '';
            nrodocInput.value = '';
            pacienteInput.value = '';
            paginaActualProgramadas = 1; // Reiniciar paginación
            cargarCitasPorFecha(fechaHoy, false);
        });

        // Event Listeners para citas no atendidas
        btnBuscarNoAtendidas.addEventListener('click', () => {
            const tipodoc = tipodocNoAtendidasSelect.value;
            const nrodoc = nrodocNoAtendidasInput.value;
            const paciente = pacienteNoAtendidasInput.value;

            // Construir filtros
            let filtros = {};
            filtros.fecha = fechaHoy; // Siempre usar la fecha actual
            if (tipodoc) filtros.tipodoc = tipodoc;
            if (nrodoc) filtros.nrodoc = nrodoc;
            if (paciente) filtros.paciente = paciente;
            filtros.estado = 'NO ASISTIO'; // Solo mostrar citas no asistidas

            // Reiniciar paginación
            paginaActualNoAtendidas = 1;

            // Solo mostrar cargando si es una búsqueda compleja
            const esBusquedaCompleja = nrodoc || paciente;
            buscarCitasConFiltros(filtros, esBusquedaCompleja, true);
        });

        btnLimpiarNoAtendidas.addEventListener('click', () => {
            tipodocNoAtendidasSelect.value = '';
            nrodocNoAtendidasInput.value = '';
            pacienteNoAtendidasInput.value = '';
            paginaActualNoAtendidas = 1; // Reiniciar paginación
            cargarCitasNoAtendidas(fechaHoy, false);
        });

        btnActualizar.addEventListener('click', () => {
            // Añadir clase de animación al botón para indicar que se está actualizando
            btnActualizar.classList.add('btn-refreshing');

            // Cambiar el icono temporalmente
            const originalIcon = btnActualizar.innerHTML;
            btnActualizar.innerHTML = '<i class="fas fa-sync-alt fa-spin me-1"></i> Actualizando...';

            // Actualizar citas y verificar no asistidas
            verificarYActualizarCitasNoAsistidas();

            // Actualizar según la pestaña activa
            if (tabProgramadas.classList.contains('active')) {
                paginaActualProgramadas = 1; // Reiniciar paginación
                cargarCitasPorFecha(fechaHoy, true);
            } else {
                paginaActualNoAtendidas = 1; // Reiniciar paginación
                cargarCitasNoAtendidas(fechaHoy, true);
            }

            // Restaurar el botón después de un breve retraso
            setTimeout(() => {
                btnActualizar.innerHTML = originalIcon;
                btnActualizar.classList.remove('btn-refreshing');
            }, 1500);
        });

        // Cerrar la consulta y volver al listado de citas
        btnCerrarConsulta.addEventListener('click', volverAListadoCitas);

        // Event listeners para navegación por pasos
        wizardBtnAnterior.addEventListener('click', irPasoAnterior);
        wizardBtnSiguiente.addEventListener('click', irPasoSiguiente);
        btnFinalizar.addEventListener('click', finalizarConsulta);

        // Event listener para manejo de nuevo diagnóstico
        btnNuevoDiagnostico.addEventListener('click', () => {
            formDiagnostico.reset(); // Limpiar formulario
            modalNuevoDiagnostico.show();
        });

        btnGuardarDiagnostico.addEventListener('click', guardarNuevoDiagnostico);

        // Event listeners para agregar medicamentos y tratamientos
        btnAgregarMedicamento.addEventListener('click', () => {
            if (validarFormularioMedicamento()) {
                verificarAlergiaMedicamento(medicacion.value);
            }
        });

        btnAgregarTratamiento.addEventListener('click', () => {
            if (validarFormularioTratamiento()) {
                verificarAlergiaMedicamento(tratMedicacion.value, true);
            }
        });

        // Event listener para confirmar alergia
        btnConfirmarAlergia.addEventListener('click', () => {
            modalAlergiaAlerta.hide();

            if (medicamentoEnAlerta) {
                agregarMedicamento(medicamentoEnAlerta.medicacion, medicamentoEnAlerta.cantidad, medicamentoEnAlerta.frecuencia);
                medicamentoEnAlerta = null;
            } else if (tratamientoEnAlerta) {
                agregarTratamiento(
                    tratamientoEnAlerta.medicacion,
                    tratamientoEnAlerta.dosis,
                    tratamientoEnAlerta.frecuencia,
                    tratamientoEnAlerta.duracion
                );
                tratamientoEnAlerta = null;
            }
        });

        // Event listener para guardar reprogramación
        btnGuardarReprogramacion.addEventListener('click', guardarReprogramacionCita);

        // Event listeners para eliminar medicamentos y tratamientos
        listaMedicamentos.addEventListener('click', (e) => {
            const btnEliminar = e.target.closest('.btn-eliminar-medicamento');
            if (btnEliminar) {
                const index = btnEliminar.dataset.index;
                eliminarMedicamento(index);
            }
        });

        listaTratamientos.addEventListener('click', (e) => {
            const btnEliminar = e.target.closest('.btn-eliminar-tratamiento');
            if (btnEliminar) {
                const index = btnEliminar.dataset.index;
                eliminarTratamiento(index);
            }
        });

        // Establecer fecha mínima para reprogramación (hoy)
        reprogramarFecha.min = fechaHoy;

        // Event listener para cambio de fecha en reprogramación
        reprogramarFecha.addEventListener('change', cargarHorasDisponiblesReprogramacion);

        // Funciones

        // Función para cargar citas programadas por fecha
        function cargarCitasPorFecha(fecha, mostrarIndicadorCarga = false) {
            // Usar indicador de carga suave en la tabla en lugar del overlay completo
            if (mostrarIndicadorCarga) {
                // Mostrar spinner dentro de la tabla en lugar de pantalla completa
                tablaCitasProgramadasBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <span>Actualizando listado...</span>
                        </div>
                    </td>
                </tr>
            `;
            }

            // Añadir parámetros de hora actual y estado=PROGRAMADA
            fetch(`<?= $host ?>/controllers/cita.controller.php?op=listar_por_dia&fecha=${fecha}&hora_actual=${horaActual}&estado=PROGRAMADA`)
                .then(response => response.json())
                .then(data => {
                    if (data.status && data.data && data.data.length > 0) {
                        citasCompletasProgramadas = data.data; // Guardar todas las citas
                        totalRegistrosProgramadas = citasCompletasProgramadas.length;

                        // Recalcular correctamente el total de páginas
                        totalPaginasProgramadas = Math.ceil(totalRegistrosProgramadas / registrosPorPaginaProgramadas);

                        // Asegurar que la página actual sea válida
                        if (paginaActualProgramadas > totalPaginasProgramadas) {
                            paginaActualProgramadas = totalPaginasProgramadas;
                        }

                        mostrarCitasProgramadasPaginadas();
                    } else {
                        mostrarSinCitasProgramadas();
                    }
                })
                .catch(error => {
                    console.error('Error al cargar citas programadas:', error);
                    mostrarSinCitasProgramadas();
                });
        }

        // Función para cargar citas no atendidas por fecha
        function cargarCitasNoAtendidas(fecha, mostrarIndicadorCarga = false) {
            // Usar indicador de carga suave en la tabla
            if (mostrarIndicadorCarga) {
                // Mostrar spinner dentro de la tabla
                tablaCitasNoAtendidasBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <span>Actualizando listado...</span>
                    </div>
                </td>
            </tr>
        `;
            }

            // Añadir parámetros de hora actual y estado=NO ASISTIO
            fetch(`<?= $host ?>/controllers/cita.controller.php?op=listar_por_dia&fecha=${fecha}&hora_actual=${horaActual}&estado=NO ASISTIO`)
                .then(response => response.json())
                .then(data => {
                    if (data.status && data.data && data.data.length > 0) {
                        citasCompletasNoAtendidas = data.data; // Guardar todas las citas
                        totalRegistrosNoAtendidas = citasCompletasNoAtendidas.length;

                        // Recalcular correctamente el total de páginas
                        totalPaginasNoAtendidas = Math.ceil(totalRegistrosNoAtendidas / registrosPorPaginaNoAtendidas);

                        // Asegurar que la página actual sea válida
                        if (paginaActualNoAtendidas > totalPaginasNoAtendidas) {
                            paginaActualNoAtendidas = totalPaginasNoAtendidas;
                        }

                        mostrarCitasNoAtendidasPaginadas();
                    } else {
                        mostrarSinCitasNoAtendidas();
                    }
                })
                .catch(error => {
                    console.error('Error al cargar citas no atendidas:', error);
                    mostrarSinCitasNoAtendidas();
                });
        }

        // Función para buscar citas con filtros
        function buscarCitasConFiltros(filtros, mostrarIndicadorCarga = false, esNoAtendidas = false) {
            // Determinar qué elementos usar según el tipo de cita
            const tablaCitasBody = esNoAtendidas ? tablaCitasNoAtendidasBody : tablaCitasProgramadasBody;

            // Usar indicador de carga suave en la tabla
            if (mostrarIndicadorCarga) {
                // Mostrar spinner dentro de la tabla
                tablaCitasBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <span>Buscando pacientes...</span>
                        </div>
                    </td>
                </tr>
            `;
            }

            // Construir URL con parámetros de filtro
            let url = `<?= $host ?>/controllers/cita.controller.php?op=buscar_con_filtros`;
            const params = new URLSearchParams();

            // Añadir cada filtro a los parámetros
            for (const [key, value] of Object.entries(filtros)) {
                params.append(key, value);
            }

            // Añadir los parámetros a la URL
            url += '&' + params.toString();

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.status && data.data && data.data.length > 0) {
                        if (esNoAtendidas) {
                            citasCompletasNoAtendidas = data.data; // Guardar todas las citas
                            totalRegistrosNoAtendidas = citasCompletasNoAtendidas.length;

                            // Recalcular correctamente el total de páginas
                            totalPaginasNoAtendidas = Math.ceil(totalRegistrosNoAtendidas / registrosPorPaginaNoAtendidas);

                            // Asegurar que la página actual sea válida
                            if (paginaActualNoAtendidas > totalPaginasNoAtendidas) {
                                paginaActualNoAtendidas = totalPaginasNoAtendidas;
                            }

                            mostrarCitasNoAtendidasPaginadas();
                        } else {
                            citasCompletasProgramadas = data.data; // Guardar todas las citas
                            totalRegistrosProgramadas = citasCompletasProgramadas.length;

                            // Recalcular correctamente el total de páginas
                            totalPaginasProgramadas = Math.ceil(totalRegistrosProgramadas / registrosPorPaginaProgramadas);

                            // Asegurar que la página actual sea válida
                            if (paginaActualProgramadas > totalPaginasProgramadas) {
                                paginaActualProgramadas = totalPaginasProgramadas;
                            }

                            mostrarCitasProgramadasPaginadas();
                        }
                    } else {
                        if (esNoAtendidas) {
                            mostrarSinCitasNoAtendidas();
                        } else {
                            mostrarSinCitasProgramadas();
                        }
                    }
                })
                .catch(error => {
                    console.error(`Error al buscar citas ${esNoAtendidas ? 'no atendidas' : 'programadas'} con filtros:`, error);
                    if (esNoAtendidas) {
                        mostrarSinCitasNoAtendidas();
                    } else {
                        mostrarSinCitasProgramadas();
                    }
                });
        }

        // Función para verificar y actualizar citas no asistidas
        function verificarYActualizarCitasNoAsistidas() {
            // Obtener la fecha y hora actuales del servidor para evitar problemas con el reloj del cliente
            const fechaHoy = '<?= $fechaHoy ?>';
            const horaActual = '<?= date("H:i:s") ?>'; // Usar la hora actual del servidor

            console.log(`Verificando citas no asistidas a las ${horaActual}`);

            // Hacer la petición al servidor para actualizar citas
            fetch(`<?= $host ?>/controllers/cita.controller.php?op=actualizar_citas_no_asistidas&fecha=${fechaHoy}&hora=${horaActual}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        console.log(`Citas actualizadas a NO ASISTIO: ${data.actualizadas}`);

                        // Si se actualizaron citas, recargar las listas
                        if (data.actualizadas > 0) {
                            // Recargar ambas listas para mostrar los cambios
                            cargarCitasPorFecha(fechaHoy, false);
                            cargarCitasNoAtendidas(fechaHoy, false);

                            // Notificar al usuario de manera visible
                            const message = data.actualizadas == 1 ?
                                "1 cita ha sido marcada como NO ASISTIO automáticamente" :
                                `${data.actualizadas} citas han sido marcadas como NO ASISTIO automáticamente`;

                            mostrarNotificacion(message, 'warning');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error al actualizar citas no asistidas:', error);
                });
        }

        // Función para mostrar las citas programadas paginadas
        function mostrarCitasProgramadasPaginadas() {
            // Verificar que haya citas para mostrar
            if (citasCompletasProgramadas.length === 0) {
                mostrarSinCitasProgramadas();
                return;
            }

            // Verificar que la página actual sea válida
            if (paginaActualProgramadas < 1) paginaActualProgramadas = 1;
            if (paginaActualProgramadas > totalPaginasProgramadas) paginaActualProgramadas = totalPaginasProgramadas;

            // Calcular índices para la paginación
            const inicio = (paginaActualProgramadas - 1) * registrosPorPaginaProgramadas;
            const fin = Math.min(inicio + registrosPorPaginaProgramadas, totalRegistrosProgramadas);

            // Asegurarse de que hay elementos para mostrar
            if (fin <= inicio) {
                mostrarSinCitasProgramadas();
                return;
            }

            // Obtener citas de la página actual
            const citasPagina = citasCompletasProgramadas.slice(inicio, fin);

            // Verificar que haya citas en la página actual
            if (citasPagina.length === 0) {
                paginaActualProgramadas = 1; // Si no hay citas en esta página, regresar a la primera
                mostrarCitasProgramadasPaginadas(); // Volver a llamar con paginaActual=1
                return;
            }

            // Mostrar las citas de la página actual
            mostrarCitasProgramadas(citasPagina);

            // Actualizar información de paginación
            actualizarPaginacionProgramadas();
        }

        // Función para mostrar las citas no atendidas paginadas
        function mostrarCitasNoAtendidasPaginadas() {
            // Verificar que haya citas para mostrar
            if (citasCompletasNoAtendidas.length === 0) {
                mostrarSinCitasNoAtendidas();
                return;
            }

            // Verificar que la página actual sea válida
            if (paginaActualNoAtendidas < 1) paginaActualNoAtendidas = 1;
            if (paginaActualNoAtendidas > totalPaginasNoAtendidas) paginaActualNoAtendidas = totalPaginasNoAtendidas;

            // Calcular índices para la paginación
            const inicio = (paginaActualNoAtendidas - 1) * registrosPorPaginaNoAtendidas;
            const fin = Math.min(inicio + registrosPorPaginaNoAtendidas, totalRegistrosNoAtendidas);

            // Asegurarse de que hay elementos para mostrar
            if (fin <= inicio) {
                mostrarSinCitasNoAtendidas();
                return;
            }

            // Obtener citas de la página actual
            const citasPagina = citasCompletasNoAtendidas.slice(inicio, fin);

            // Verificar que haya citas en la página actual
            if (citasPagina.length === 0) {
                paginaActualNoAtendidas = 1; // Si no hay citas en esta página, regresar a la primera
                mostrarCitasNoAtendidasPaginadas(); // Volver a llamar con paginaActual=1
                return;
            }

            // Mostrar las citas de la página actual
            mostrarCitasNoAtendidas(citasPagina);

            // Actualizar información de paginación
            actualizarPaginacionNoAtendidas();
        }

        // Función para actualizar los controles de paginación de citas programadas
        function actualizarPaginacionProgramadas() {
            // Verificar si hay registros
            if (totalRegistrosProgramadas === 0) {
                paginationInfoProgramadas.textContent = `Mostrando 0 a 0 de 0 registros`;
                paginacionProgramadas.innerHTML = `
                <li class="page-item disabled" id="paginacion-anterior-programadas">
                    <a class="page-link" href="#" aria-label="Anterior">
                        <span aria-hidden="true">Anterior</span>
                    </a>
                </li>
                <li class="page-item active"><a class="page-link" href="#" data-page="1">1</a></li>
                <li class="page-item disabled" id="paginacion-siguiente-programadas">
                    <a class="page-link" href="#" aria-label="Siguiente">
                        <span aria-hidden="true">Siguiente</span>
                    </a>
                </li>
            `;
                return;
            }

            // Actualizar texto de información
            const inicio = (paginaActualProgramadas - 1) * registrosPorPaginaProgramadas + 1;
            const fin = Math.min(paginaActualProgramadas * registrosPorPaginaProgramadas, totalRegistrosProgramadas);
            paginationInfoProgramadas.textContent = `Mostrando ${inicio} a ${fin} de ${totalRegistrosProgramadas} registros`;

            // Generar botones de página
            let paginacionHTML = '';

            // Crear botón "Anterior" correctamente
            if (paginaActualProgramadas === 1) {
                paginacionHTML += `
                <li class="page-item disabled" id="paginacion-anterior-programadas">
                    <a class="page-link" href="#" aria-label="Anterior">
                        <span aria-hidden="true">Anterior</span>
                    </a>
                </li>
            `;
            } else {
                paginacionHTML += `
                <li class="page-item" id="paginacion-anterior-programadas">
                    <a class="page-link" href="#" aria-label="Anterior">
                        <span aria-hidden="true">Anterior</span>
                    </a>
                </li>
            `;
            }

            // Si totalPaginas es 0, mostramos al menos la página 1
            if (totalPaginasProgramadas === 0) totalPaginasProgramadas = 1;

            // Decidir qué páginas mostrar
            let startPage = Math.max(1, paginaActualProgramadas - 2);
            let endPage = Math.min(totalPaginasProgramadas, startPage + 4);

            // Asegurar que siempre mostramos 5 páginas si es posible
            if (endPage - startPage < 4 && totalPaginasProgramadas > 4) {
                startPage = Math.max(1, endPage - 4);
            }

            // Generar botones de número de página
            for (let i = startPage; i <= endPage; i++) {
                if (i === paginaActualProgramadas) {
                    paginacionHTML += `<li class="page-item active"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                } else {
                    paginacionHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                }
            }

            // Crear botón "Siguiente" correctamente
            if (paginaActualProgramadas === totalPaginasProgramadas) {
                paginacionHTML += `
                <li class="page-item disabled" id="paginacion-siguiente-programadas">
                    <a class="page-link" href="#" aria-label="Siguiente">
                        <span aria-hidden="true">Siguiente</span>
                    </a>
                </li>
            `;
            } else {
                paginacionHTML += `
                <li class="page-item" id="paginacion-siguiente-programadas">
                    <a class="page-link" href="#" aria-label="Siguiente">
                        <span aria-hidden="true">Siguiente</span>
                    </a>
                </li>
            `;
            }

            // Actualizar la paginación en el DOM
            paginacionProgramadas.innerHTML = paginacionHTML;
        }

        // Función para actualizar los controles de paginación de citas no atendidas
        function actualizarPaginacionNoAtendidas() {
            // Verificar si hay registros
            if (totalRegistrosNoAtendidas === 0) {
                paginationInfoNoAtendidas.textContent = `Mostrando 0 a 0 de 0 registros`;
                paginacionNoAtendidas.innerHTML = `
                <li class="page-item disabled" id="paginacion-anterior-no-atendidas">
                    <a class="page-link" href="#" aria-label="Anterior">
                        <span aria-hidden="true">Anterior</span>
                    </a>
                </li>
                <li class="page-item active"><a class="page-link" href="#" data-page="1">1</a></li>
                <li class="page-item disabled" id="paginacion-siguiente-no-atendidas">
                    <a class="page-link" href="#" aria-label="Siguiente">
                        <span aria-hidden="true">Siguiente</span>
                    </a>
                </li>
            `;
                return;
            }

            // Actualizar texto de información
            const inicio = (paginaActualNoAtendidas - 1) * registrosPorPaginaNoAtendidas + 1;
            const fin = Math.min(paginaActualNoAtendidas * registrosPorPaginaNoAtendidas, totalRegistrosNoAtendidas);
            paginationInfoNoAtendidas.textContent = `Mostrando ${inicio} a ${fin} de ${totalRegistrosNoAtendidas} registros`;

            // Generar botones de página
            let paginacionHTML = '';

            // Crear botón "Anterior" correctamente
            if (paginaActualNoAtendidas === 1) {
                paginacionHTML += `
                <li class="page-item disabled" id="paginacion-anterior-no-atendidas">
                    <a class="page-link" href="#" aria-label="Anterior">
                        <span aria-hidden="true">Anterior</span>
                    </a>
                </li>
            `;
            } else {
                paginacionHTML += `
                <li class="page-item" id="paginacion-anterior-no-atendidas">
                    <a class="page-link" href="#" aria-label="Anterior">
                        <span aria-hidden="true">Anterior</span>
                    </a>
                </li>
            `;
            }

            // Si totalPaginas es 0, mostramos al menos la página 1
            if (totalPaginasNoAtendidas === 0) totalPaginasNoAtendidas = 1;

            // Decidir qué páginas mostrar
            let startPage = Math.max(1, paginaActualNoAtendidas - 2);
            let endPage = Math.min(totalPaginasNoAtendidas, startPage + 4);

            // Asegurar que siempre mostramos 5 páginas si es posible
            if (endPage - startPage < 4 && totalPaginasNoAtendidas > 4) {
                startPage = Math.max(1, endPage - 4);
            }

            // Generar botones de número de página
            for (let i = startPage; i <= endPage; i++) {
                if (i === paginaActualNoAtendidas) {
                    paginacionHTML += `<li class="page-item active"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                } else {
                    paginacionHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                }
            }

            // Crear botón "Siguiente" correctamente
            if (paginaActualNoAtendidas === totalPaginasNoAtendidas) {
                paginacionHTML += `
                <li class="page-item disabled" id="paginacion-siguiente-no-atendidas">
                    <a class="page-link" href="#" aria-label="Siguiente">
                        <span aria-hidden="true">Siguiente</span>
                    </a>
                </li>
            `;
            } else {
                paginacionHTML += `
                <li class="page-item" id="paginacion-siguiente-no-atendidas">
                    <a class="page-link" href="#" aria-label="Siguiente">
                        <span aria-hidden="true">Siguiente</span>
                    </a>
                </li>
            `;
            }

            // Actualizar la paginación en el DOM
            paginacionNoAtendidas.innerHTML = paginacionHTML;
        }

        function mostrarNotificacion(mensaje, tipo = 'success') {
            // Verificar si existe el contenedor de notificaciones
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container';
                document.body.appendChild(toastContainer);
            }

            // Crear elemento toast
            const toastElement = document.createElement('div');
            toastElement.className = 'toast show';
            toastElement.role = 'alert';
            toastElement.ariaLive = 'assertive';
            toastElement.ariaAtomic = 'true';

            // Seleccionar icono y color según el tipo
            let iconClass = 'fas fa-check-circle text-success';
            let title = 'Éxito';

            if (tipo === 'info') {
                iconClass = 'fas fa-info-circle text-info';
                title = 'Información';
            } else if (tipo === 'warning') {
                iconClass = 'fas fa-exclamation-triangle text-warning';
                title = 'Advertencia';
            } else if (tipo === 'error') {
                iconClass = 'fas fa-exclamation-circle text-danger';
                title = 'Error';
            }

            // Contenido del toast
            toastElement.innerHTML = `
            <div class="toast-header">
                <i class="${iconClass} me-2"></i>
                <strong class="me-auto">${title}</strong>
                <small>Ahora</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${mensaje}
            </div>
        `;

            // Añadir al contenedor
            toastContainer.appendChild(toastElement);

            // Configurar auto-cierre
            setTimeout(() => {
                toastElement.classList.remove('show');
                setTimeout(() => {
                    toastElement.remove();
                }, 300);
            }, 5000);
        }

        function mostrarCitasProgramadas(citas) {
            tablaCitasProgramadasBody.innerHTML = '';
            sinCitasProgramadas.classList.add('d-none');
            tablaCitasProgramadas.classList.remove('d-none');

            // Agregar clase para animación sutil al actualizar
            tablaCitasProgramadas.classList.add('table-refreshing');

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

                const horaFormateada = new Date(`2000-01-01T${cita.hora}`).toLocaleTimeString('es-ES', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                row.innerHTML = `
                <td class="text-nowrap">${horaFormateada}</td>
                <td>${cita.nombre_paciente}</td>
                <td>${cita.tipodoc}</td>
                <td>${cita.nrodoc}</td>
                <td>
                    <span class="badge ${getEstadoBadgeClass(cita.estado)}">${cita.estado}</span>
                </td>
                <td>
                    ${cita.tiene_receta ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-muted"></i>'}
                </td>
                <td class="text-center">
                    ${cita.estado === 'PROGRAMADA' ? `
                        <button class="btn btn-primary btn-realizar-consulta" 
                                data-idcita="${cita.idcita}" 
                                data-idconsulta="${cita.idconsulta}" 
                                data-idpaciente="${cita.idpaciente}">
                            <i class="fas fa-stethoscope me-1"></i> Realizar Consulta Cita
                        </button>
                    ` : `
                        <button class="btn btn-info btn-ver-consulta" 
                                data-idconsulta="${cita.idconsulta}">
                            <i class="fas fa-eye me-1"></i> Ver Consulta
                        </button>
                    `}
                </td>
            `;

                tablaCitasProgramadasBody.appendChild(row);
            });

            // Actualizar contador
            totalCitasProgramadas.textContent = `${totalRegistrosProgramadas} citas`;

            // Agregar event listeners a los botones de acción
            document.querySelectorAll('.btn-realizar-consulta').forEach(btn => {
                btn.addEventListener('click', function() {
                    const idcita = this.getAttribute('data-idcita');
                    const idconsulta = this.getAttribute('data-idconsulta');
                    const idpaciente = this.getAttribute('data-idpaciente');
                    iniciarConsultaCita(idcita, idconsulta, idpaciente);
                });
            });

            document.querySelectorAll('.btn-ver-consulta').forEach(btn => {
                btn.addEventListener('click', function() {
                    const idconsulta = this.getAttribute('data-idconsulta');
                    window.location.href = `<?= $host ?>/views/SesionDoctor/Consultas/historialConsulta.php?idconsulta=${idconsulta}`;
                });
            });

            // Quitar la clase de animación después de un tiempo
            setTimeout(() => {
                tablaCitasProgramadas.classList.remove('table-refreshing');
            }, 500);
        }

        function mostrarCitasNoAtendidas(citas) {
            tablaCitasNoAtendidasBody.innerHTML = '';
            sinCitasNoAtendidas.classList.add('d-none');
            tablaCitasNoAtendidas.classList.remove('d-none');

            // Agregar clase para animación sutil al actualizar
            tablaCitasNoAtendidas.classList.add('table-refreshing');

            citas.forEach(cita => {
                const row = document.createElement('tr');

                // Definir el color de fondo para citas no atendidas (siempre es table-warning)
                row.classList.add('table-warning');

                const horaFormateada = new Date(`2000-01-01T${cita.hora}`).toLocaleTimeString('es-ES', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                row.innerHTML = `
            <td class="text-nowrap">${horaFormateada}</td>
            <td>${cita.nombre_paciente}</td>
            <td>${cita.tipodoc}</td>
            <td>${cita.nrodoc}</td>
            <td>
                <span class="badge ${getEstadoBadgeClass(cita.estado)}">${cita.estado}</span>
            </td>
            <td>
                ${cita.tiene_receta ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-muted"></i>'}
            </td>
            <td class="text-center">
                <div class="btn-group">
                    <button class="btn btn-warning btn-reprogramar-cita" 
                            data-idcita="${cita.idcita}"
                            data-idpaciente="${cita.idpaciente}">
                        <i class="fas fa-calendar-alt me-1"></i> Reprogramar
                    </button>
                    <button class="btn btn-primary btn-realizar-consulta-tardio" 
                            data-idcita="${cita.idcita}" 
                            data-idconsulta="${cita.idconsulta}" 
                            data-idpaciente="${cita.idpaciente}">
                        <i class="fas fa-stethoscope me-1"></i> Continuar Consulta
                    </button>
                </div>
            </td>
        `;

                tablaCitasNoAtendidasBody.appendChild(row);
            });

            // Actualizar contador
            totalCitasNoAtendidas.textContent = `${totalRegistrosNoAtendidas} citas`;

            // Agregar event listeners a los botones de acción
            document.querySelectorAll('.btn-reprogramar-cita').forEach(btn => {
                btn.addEventListener('click', function() {
                    const idcita = this.getAttribute('data-idcita');
                    const idpaciente = this.getAttribute('data-idpaciente');
                    mostrarModalReprogramarCita(idcita, idpaciente);
                });
            });

            document.querySelectorAll('.btn-realizar-consulta-tardio').forEach(btn => {
                btn.addEventListener('click', function() {
                    const idcita = this.getAttribute('data-idcita');
                    const idconsulta = this.getAttribute('data-idconsulta');
                    const idpaciente = this.getAttribute('data-idpaciente');

                    // Primero preguntar si desea continuar con una cita tardía
                    Swal.fire({
                        title: 'Atención',
                        text: 'Esta cita está marcada como no asistida. ¿Desea continuar con la consulta?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, continuar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Cambiar el estado de la cita a PROGRAMADA
                            actualizarEstadoCita(idcita, 'PROGRAMADA', () => {
                                // Iniciar la consulta después de actualizar el estado
                                iniciarConsultaCita(idcita, idconsulta, idpaciente);
                            });
                        }
                    });
                });
            });

            // Quitar la clase de animación después de un tiempo
            setTimeout(() => {
                tablaCitasNoAtendidas.classList.remove('table-refreshing');
            }, 500);
        }

        function mostrarSinCitasProgramadas() {
            tablaCitasProgramadasBody.innerHTML = '';
            sinCitasProgramadas.classList.remove('d-none');
            totalCitasProgramadas.textContent = '0 citas';
            totalRegistrosProgramadas = 0;
            totalPaginasProgramadas = 0;
            actualizarPaginacionProgramadas();
        }

        function mostrarSinCitasNoAtendidas() {
            tablaCitasNoAtendidasBody.innerHTML = '';
            sinCitasNoAtendidas.classList.remove('d-none');
            totalCitasNoAtendidas.textContent = '0 citas';
            totalRegistrosNoAtendidas = 0;
            totalPaginasNoAtendidas = 0;
            actualizarPaginacionNoAtendidas();
        }

        function getEstadoBadgeClass(estado) {
            switch (estado) {
                case 'PROGRAMADA':
                    return 'bg-primary';
                case 'REALIZADA':
                    return 'bg-success';
                case 'CANCELADA':
                    return 'bg-danger';
                case 'NO ASISTIO':
                    return 'bg-warning';
                default:
                    return 'bg-secondary';
            }
        }

        // Nueva función para actualizar el estado de una cita
        function actualizarEstadoCita(idcita, estado, callback) {
            // Crear objeto FormData para enviar los datos
            const formData = new FormData();
            formData.append('idcita', idcita);
            formData.append('estado', estado);

            // Enviar la solicitud al servidor
            fetch(`<?= $host ?>/controllers/cita.controller.php?op=actualizar_estado`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        console.log(`Estado de cita ${idcita} actualizado a ${estado}`);

                        // Si hay una función de callback, ejecutarla
                        if (typeof callback === 'function') {
                            callback();
                        } else {
                            // Recargar las listas de citas
                            cargarCitasPorFecha(fechaHoy, false);
                            cargarCitasNoAtendidas(fechaHoy, false);
                        }
                    } else {
                        console.error('Error al actualizar estado de cita:', data.mensaje);
                        mostrarNotificacion('Error al actualizar estado de cita', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error al actualizar estado de cita:', error);
                    mostrarNotificacion('Error al actualizar estado de cita', 'error');
                });
        }

        function cargarDiagnosticos() {
            fetch(`<?= $host ?>/controllers/diagnostico.controller.php?op=listar`)
                .then(response => response.json())
                .then(data => {
                    if (data.status && data.data) {
                        const select = document.getElementById('iddiagnostico');
                        select.innerHTML = '<option value="">Seleccione diagnóstico</option>';

                        data.data.forEach(diagnostico => {
                            const option = document.createElement('option');
                            option.value = diagnostico.iddiagnostico;
                            option.textContent = `${diagnostico.codigo} - ${diagnostico.nombre}`;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error al cargar diagnósticos:', error));
        }

        function guardarNuevoDiagnostico() {
            // Validar formulario
            if (!formDiagnostico.checkValidity()) {
                formDiagnostico.classList.add('was-validated');
                return;
            }

            // Preparar datos
            const datos = {
                nombre: diagNombre.value,
                codigo: diagCodigo.value,
                descripcion: diagDescripcion.value || ''
            };

            // Mostrar un indicador sutil de "guardando..."
            const btnOriginalText = btnGuardarDiagnostico.innerHTML;
            btnGuardarDiagnostico.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
            btnGuardarDiagnostico.disabled = true;

            // Enviar petición
            fetch(`<?= $host ?>/controllers/diagnostico.controller.php?op=registrar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(datos)
                })
                .then(response => response.json())
                .then(data => {
                    // Restaurar botón
                    btnGuardarDiagnostico.innerHTML = btnOriginalText;
                    btnGuardarDiagnostico.disabled = false;

                    if (data.status) {
                        // Mostrar mensaje de éxito
                        mostrarNotificacionExito('Diagnóstico registrado correctamente');

                        // Cerrar modal
                        modalNuevoDiagnostico.hide();

                        // Recargar diagnósticos
                        cargarDiagnosticos().then(() => {
                            // Seleccionar el diagnóstico recién creado
                            setTimeout(() => {
                                iddiagnostico.value = data.iddiagnostico;
                            }, 500);
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.mensaje || 'Error al registrar el diagnóstico',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                })
                .catch(error => {
                    btnGuardarDiagnostico.innerHTML = btnOriginalText;
                    btnGuardarDiagnostico.disabled = false;

                    console.error('Error al guardar diagnóstico:', error);

                    Swal.fire({
                        title: 'Error',
                        text: 'Error al registrar el diagnóstico. Inténtelo de nuevo.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                });
        }

        function mostrarModalReprogramarCita(idcita, idpaciente) {
            // Limpiar formulario
            formReprogramar.reset();

            // Establecer el ID de la cita
            reprogramarIdCita.value = idcita;

            // Establecer fecha mínima (hoy)
            reprogramarFecha.min = fechaHoy;
            reprogramarFecha.value = ""; // Limpiar fecha

            // Limpiar selector de horas
            reprogramarHora.innerHTML = '<option value="">Seleccione una hora</option>';

            // Mostrar modal
            modalReprogramarCita.show();
        }

        function cargarHorasDisponiblesReprogramacion() {
            const fecha = reprogramarFecha.value;
            const idcita = reprogramarIdCita.value;

            if (!fecha) return;

            // Obtener idpaciente y doctor de la cita
            fetch(`<?= $host ?>/controllers/cita.controller.php?op=obtener&id=${idcita}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status && data.data) {
                        const iddoctor = data.data.iddoctor;
                        const idespecialidad = data.data.idespecialidad;

                        // Cargar horas disponibles
                        fetch(`<?= $host ?>/controllers/cita.controller.php?op=horas_disponibles&iddoctor=${iddoctor}&fecha=${fecha}&idespecialidad=${idespecialidad}`)
                            .then(response => response.json())
                            .then(dataHoras => {
                                reprogramarHora.innerHTML = '<option value="">Seleccione una hora</option>';

                                if (dataHoras.status && dataHoras.data && dataHoras.data.length > 0) {
                                    dataHoras.data.forEach(hora => {
                                        const option = document.createElement('option');
                                        option.value = hora.hora;
                                        option.textContent = `${hora.hora.substring(0, 5)} - ${hora.horaFin.substring(0, 5)}`;
                                        reprogramarHora.appendChild(option);
                                    });
                                } else {
                                    const option = document.createElement('option');
                                    option.value = "";
                                    option.textContent = "No hay horas disponibles para esta fecha";
                                    option.disabled = true;
                                    reprogramarHora.appendChild(option);
                                }
                            })
                            .catch(error => {
                                console.error('Error al cargar horas disponibles:', error);

                                reprogramarHora.innerHTML = '<option value="">Error al cargar horas</option>';
                            });
                    }
                })
                .catch(error => {
                    console.error('Error al obtener datos de la cita:', error);
                });
        }

        function guardarReprogramacionCita() {
            // Validar formulario
            if (!formReprogramar.checkValidity()) {
                formReprogramar.classList.add('was-validated');
                return;
            }

            // Preparar datos
            const datos = {
                idcita: reprogramarIdCita.value,
                fecha: reprogramarFecha.value,
                hora: reprogramarHora.value,
                estado: 'PROGRAMADA',
                observaciones: reprogramarObservaciones.value || 'Cita reprogramada'
            };

            // Mostrar un indicador sutil de "guardando..."
            const btnOriginalText = btnGuardarReprogramacion.innerHTML;
            btnGuardarReprogramacion.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
            btnGuardarReprogramacion.disabled = true;

            // Enviar petición
            fetch(`<?= $host ?>/controllers/cita.controller.php?op=actualizar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(datos)
                })
                .then(response => response.json())
                .then(data => {
                    // Restaurar botón
                    btnGuardarReprogramacion.innerHTML = btnOriginalText;
                    btnGuardarReprogramacion.disabled = false;

                    if (data.status) {
                        // Mostrar mensaje de éxito
                        mostrarNotificacion('Cita reprogramada correctamente', 'success');

                        // Cerrar modal
                        modalReprogramarCita.hide();

                        // Actualizar listas de citas
                        cargarCitasPorFecha(fechaHoy, false);
                        cargarCitasNoAtendidas(fechaHoy, false);
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.mensaje || 'Error al reprogramar la cita',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                })
                .catch(error => {
                    btnGuardarReprogramacion.innerHTML = btnOriginalText;
                    btnGuardarReprogramacion.disabled = false;

                    console.error('Error al reprogramar cita:', error);

                    Swal.fire({
                        title: 'Error',
                        text: 'Error al reprogramar la cita. Inténtelo de nuevo.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                });
        }

        function mostrarNotificacionExito(mensaje) {
            // Verificar si existe el contenedor de notificaciones, si no, crearlo
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container';
                document.body.appendChild(toastContainer);
            }

            // Crear elemento toast
            const toastElement = document.createElement('div');
            toastElement.className = 'toast show';
            toastElement.role = 'alert';
            toastElement.ariaLive = 'assertive';
            toastElement.ariaAtomic = 'true';

            // Contenido del toast
            toastElement.innerHTML = `
            <div class="toast-header">
                <i class="fas fa-check-circle text-success me-2"></i>
                <strong class="me-auto">Éxito</strong>
                <small>Ahora</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${mensaje}
            </div>
        `;

            // Añadir al contenedor
            toastContainer.appendChild(toastElement);

            // Configurar auto-cierre
            setTimeout(() => {
                toastElement.classList.remove('show');
                setTimeout(() => {
                    toastElement.remove();
                }, 300);
            }, 5000);
        }

        function iniciarConsultaCita(idcita, idconsulta, idpaciente) {
            // Reiniciar el proceso
            reiniciarProceso();

            // Asignar valores a los campos ocultos
            idcitaInput.value = idcita;
            idconsultaInput.value = idconsulta;
            idpacienteInput.value = idpaciente;

            // Mostrar un indicador de carga más sutil
            tablaCitasProgramadasBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-primary me-2" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <span>Preparando consulta...</span>
                    </div>
                </td>
            </tr>
        `;

            // Ocultar el título y la barra de navegación
            mainContainer.classList.add('consulta-mode');

            // Realizar todas las peticiones en paralelo
            Promise.all([
                    cargarDatosPaciente(idpaciente),
                    cargarAlergiasPaciente(idpaciente),
                    cargarHistorialPaciente(idpaciente),
                    cargarHistoriaClinica(idconsulta)
                ])
                .then(([datosPaciente, alergias, historial, historiaClinica]) => {
                    // Mostrar la información del paciente
                    if (datosPaciente) {
                        pacienteNombre.textContent = datosPaciente.nombre_completo ||
                            `${datosPaciente.nombres} ${datosPaciente.apellidos}`;
                        pacienteDocumento.textContent = `${datosPaciente.tipodoc}: ${datosPaciente.nrodoc}`;
                        pacienteTelefono.textContent = datosPaciente.telefono || 'No registrado';
                    }

                    // Guardar alergias para validaciones posteriores
                    alergiasPaciente = alergias || [];

                    // Mostrar alergias
                    if (alergias && alergias.length > 0) {
                        alergiasLista.classList.remove('d-none');
                        alergiasLista.classList.remove('alert-warning');
                        alergiasLista.classList.add('alert-danger');

                        let alergiasHTML = '<i class="fas fa-exclamation-triangle me-2"></i><strong>ALERGIAS REGISTRADAS:</strong><ul class="mb-0">';
                        alergias.forEach(alergia => {
                            alergiasHTML += `<li>${alergia.tipoalergia}: ${alergia.alergia} (${alergia.gravedad})</li>`;
                        });
                        alergiasHTML += '</ul>';

                        alergiasLista.innerHTML = alergiasHTML;
                    } else {
                        alergiasLista.classList.remove('d-none');
                        alergiasLista.classList.remove('alert-danger');
                        alergiasLista.classList.add('alert-warning');
                        alergiasLista.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>No se encontraron alergias registradas';
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

                    // Si ya existe una historia clínica para esta consulta
                    if (historiaClinica) {
                        console.log('Historia clínica existente:', historiaClinica);
                        // Podemos mostrarla en el paso 2
                        enfermedadActual.value = historiaClinica.enfermedadactual || '';
                        examenFisico.value = historiaClinica.examenfisico || '';
                        evolucion.value = historiaClinica.evolucion || '';

                        if (historiaClinica.iddiagnostico) {
                            iddiagnostico.value = historiaClinica.iddiagnostico;
                        }

                        altamedica.checked = historiaClinica.altamedica ? true : false;
                    }

                    // Ocultar completamente el contenedor de listado
                    listaCitasContainer.style.display = 'none';

                    // Mostrar el contenedor de consulta con el ancho completo
                    consultaContainer.classList.remove('d-none');
                    consultaContainer.style.width = '100%';

                    // Añadir delay para permitir renderizado
                    setTimeout(() => {
                        // Forzar un redimensionado en caso de tablas o elementos responsivos
                        window.dispatchEvent(new Event('resize'));
                    }, 100);
                })
                .catch(error => {
                    console.error('Error al cargar datos para la consulta:', error);

                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudieron cargar los datos para la consulta',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });

                    // Quitar clase de modo consulta para mostrar el título
                    mainContainer.classList.remove('consulta-mode');

                    // Recargar la lista de citas en caso de error
                    cargarCitasPorFecha(fechaHoy, false);
                });
        }

        async function cargarDatosPaciente(idpaciente) {
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

        async function cargarAlergiasPaciente(idpaciente) {
            try {
                const response = await fetch(`<?= $host ?>/controllers/alergia.controller.php?operacion=listar_paciente&idpersona=${idpaciente}`);
                const data = await response.json();

                if (data.status && data.alergias) {
                    return data.alergias;
                }
                return [];
            } catch (error) {
                console.error('Error al cargar alergias del paciente:', error);
                return [];
            }
        }

        async function cargarHistorialPaciente(idpaciente) {
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

        async function cargarHistoriaClinica(idconsulta) {
            try {
                const response = await fetch(`<?= $host ?>/controllers/historiaclinica.controller.php?op=obtener_por_consulta&idconsulta=${idconsulta}`);
                const data = await response.json();

                if (data.status && data.data) {
                    return data.data;
                }
                return null;
            } catch (error) {
                console.error('Error al cargar historia clínica:', error);
                return null;
            }
        }

        function reiniciarProceso() {
            // Reiniciar paso actual
            currentStep = 1;
            actualizarProgresoWizard(currentStep);

            // Limpiar formularios
            formHistoria.reset();
            formReceta.reset();

            // Limpiar arrays
            medicamentosAgregados = [];
            tratamientosAgregados = [];

            // Limpiar tablas
            listaMedicamentos.innerHTML = '';
            listaTratamientos.innerHTML = '';

            // Mostrar/ocultar contenedores
            medicamentosVacio.classList.remove('d-none');
            tablaMedicamentosContainer.classList.add('d-none');
            tratamientosVacio.classList.remove('d-none');
            tablaTratamientosContainer.classList.add('d-none');
        }

        function irPasoAnterior() {
            if (currentStep > 1) {
                // Si no estamos en el primer paso, retrocedemos un paso
                currentStep--;
                actualizarProgresoWizard(currentStep);
            } else {
                // Si estamos en el primer paso, volvemos al listado de citas
                volverAListadoCitas();
            }
        }

        function irPasoSiguiente() {
            // Validar formulario del paso actual antes de avanzar
            if (currentStep === 1) {
                // El paso 1 no requiere validación, es solo visualización
                currentStep++;
                actualizarProgresoWizard(currentStep);
            } else if (currentStep === 2) {
                // Validar formulario de historia clínica
                if (formHistoria.checkValidity()) {
                    currentStep++;
                    actualizarProgresoWizard(currentStep);
                } else {
                    // Mostrar errores de validación
                    formHistoria.classList.add('was-validated');
                }
            }
        }

        function actualizarProgresoWizard(step) {
            // Actualizar barra de progreso
            const progressPercentage = (step / 3) * 100;
            progressBar.style.width = `${progressPercentage}%`;
            progressBar.setAttribute('aria-valuenow', progressPercentage);

            // Actualizar estado de los pasos
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

            // Mostrar panel activo y ocultar los demás
            stepPanels.forEach((panel, index) => {
                const panelNumber = index + 1;

                if (panelNumber === step) {
                    panel.classList.add('active');
                } else {
                    panel.classList.remove('active');
                }
            });

            // Nunca deshabilitamos el botón Anterior
            wizardBtnAnterior.disabled = false;

            if (step === 3) {
                wizardBtnSiguiente.classList.add('d-none');
                btnFinalizar.classList.remove('d-none');
            } else {
                wizardBtnSiguiente.classList.remove('d-none');
                btnFinalizar.classList.add('d-none');
            }
        }

        function volverAListadoCitas() {
            // Remover clase de modo consulta para mostrar el título nuevamente
            mainContainer.classList.remove('consulta-mode');

            // Ocultar el contenedor de consulta
            consultaContainer.classList.add('d-none');

            // Mostrar nuevamente el listado
            listaCitasContainer.style.display = 'block';

            // Recargar las citas de manera más sutil
            tablaCitasProgramadasBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <span>Cargando citas...</span>
                    </div>
                </td>
            </tr>
        `;

            // Cargar citas sin indicador de carga completo
            cargarCitasPorFecha(fechaHoy, false);
            cargarCitasNoAtendidas(fechaHoy, false);

            // Añadir delay para permitir renderizado
            setTimeout(() => {
                // Forzar un redimensionado en caso de tablas o elementos responsivos
                window.dispatchEvent(new Event('resize'));
            }, 100);
        }

        function verificarAlergiaMedicamento(nombreMedicamento, esTratamiento = false) {
            // Verificar si el paciente tiene alergias
            if (alergiasPaciente.length === 0) {
                if (esTratamiento) {
                    agregarTratamiento(
                        tratMedicacion.value,
                        tratDosis.value,
                        tratFrecuencia.value,
                        tratDuracion.value
                    );
                } else {
                    agregarMedicamento(
                        medicacion.value,
                        cantidad.value,
                        frecuencia.value
                    );
                }
                return;
            }

            // Verificar si alguna alergia coincide con el medicamento
            const alergiasPosibles = alergiasPaciente.filter(alergia => {
                // Convertir a minúsculas para comparación no sensible a mayúsculas
                const alergiaLower = alergia.alergia.toLowerCase();
                const medicamentoLower = nombreMedicamento.toLowerCase();

                // Buscar coincidencias parciales (si el medicamento contiene la alergia o viceversa)
                return alergiaLower.includes(medicamentoLower) || medicamentoLower.includes(alergiaLower);
            });

            if (alergiasPosibles.length > 0) {
                // Guardar medicamento actual para usarlo después de la confirmación
                if (esTratamiento) {
                    tratamientoEnAlerta = {
                        medicacion: tratMedicacion.value,
                        dosis: tratDosis.value,
                        frecuencia: tratFrecuencia.value,
                        duracion: tratDuracion.value
                    };
                    medicamentoEnAlerta = null;
                } else {
                    medicamentoEnAlerta = {
                        medicacion: medicacion.value,
                        cantidad: cantidad.value,
                        frecuencia: frecuencia.value
                    };
                    tratamientoEnAlerta = null;
                }

                // Mostrar alerta
                let detallesHTML = '<ul class="mb-0">';
                alergiasPosibles.forEach(alergia => {
                    detallesHTML += `<li><strong>${alergia.tipoalergia}:</strong> ${alergia.alergia} (${alergia.gravedad})</li>`;
                });
                detallesHTML += '</ul>';

                alergiaDetalles.innerHTML = detallesHTML;
                modalAlergiaAlerta.show();
            } else {
                // No hay alergias que coincidan, agregar directamente
                if (esTratamiento) {
                    agregarTratamiento(
                        tratMedicacion.value,
                        tratDosis.value,
                        tratFrecuencia.value,
                        tratDuracion.value
                    );
                } else {
                    agregarMedicamento(
                        medicacion.value,
                        cantidad.value,
                        frecuencia.value
                    );
                }
            }
        }

        function validarFormularioMedicamento() {
            // Verificar que los campos tengan datos
            if (!medicacion.value.trim()) {
                medicacion.focus();
                return false;
            }

            if (!cantidad.value.trim()) {
                cantidad.focus();
                return false;
            }

            if (!frecuencia.value.trim()) {
                frecuencia.focus();
                return false;
            }

            return true;
        }

        function validarFormularioTratamiento() {
            // Verificar que los campos tengan datos
            if (!tratMedicacion.value.trim()) {
                tratMedicacion.focus();
                return false;
            }

            if (!tratDosis.value.trim()) {
                tratDosis.focus();
                return false;
            }

            if (!tratFrecuencia.value.trim()) {
                tratFrecuencia.focus();
                return false;
            }

            if (!tratDuracion.value.trim()) {
                tratDuracion.focus();
                return false;
            }

            return true;
        }

        function agregarMedicamento(nombre, cantidad, frecuencia) {
            // Crear objeto para el nuevo medicamento
            const nuevoMedicamento = {
                medicacion: nombre,
                cantidad: cantidad,
                frecuencia: frecuencia
            };

            // Agregar a la lista
            medicamentosAgregados.push(nuevoMedicamento);

            // Actualizar la tabla
            actualizarTablaMedicamentos();

            // Limpiar campos
            medicacion.value = '';
            document.getElementById('cantidad').value = '';
            document.getElementById('frecuencia').value = '';

            // Enfocar campo de medicación
            medicacion.focus();
        }

        function agregarTratamiento(nombre, dosis, frecuencia, duracion) {
            // Crear objeto para el nuevo tratamiento
            const nuevoTratamiento = {
                medicacion: nombre,
                dosis: dosis,
                frecuencia: frecuencia,
                duracion: duracion
            };

            // Agregar a la lista
            tratamientosAgregados.push(nuevoTratamiento);

            // Actualizar la tabla
            actualizarTablaTratamientos();

            // Limpiar campos
            tratMedicacion.value = '';
            tratDosis.value = '';
            tratFrecuencia.value = '';
            tratDuracion.value = '';

            // Enfocar campo de medicación
            tratMedicacion.focus();
        }

        function eliminarMedicamento(index) {
            // Remover elemento del array
            medicamentosAgregados.splice(index, 1);

            // Actualizar la tabla
            actualizarTablaMedicamentos();
        }

        function eliminarTratamiento(index) {
            // Remover elemento del array
            tratamientosAgregados.splice(index, 1);

            // Actualizar la tabla
            actualizarTablaTratamientos();
        }

        function actualizarTablaMedicamentos() {
            if (medicamentosAgregados.length === 0) {
                medicamentosVacio.classList.remove('d-none');
                tablaMedicamentosContainer.classList.add('d-none');
                return;
            }

            // Mostrar tabla, ocultar mensaje de vacío
            medicamentosVacio.classList.add('d-none');
            tablaMedicamentosContainer.classList.remove('d-none');

            // Limpiar tabla
            listaMedicamentos.innerHTML = '';

            // Generar filas
            medicamentosAgregados.forEach((med, index) => {
                const row = document.createElement('tr');

                // Para destacar la fila recién agregada
                if (index === medicamentosAgregados.length - 1) {
                    row.classList.add('highlight-success');
                }

                row.innerHTML = `
                <td>${med.medicacion}</td>
                <td>${med.cantidad}</td>
                <td>${med.frecuencia}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-eliminar-medicamento" data-index="${index}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;

                listaMedicamentos.appendChild(row);
            });
        }

        function actualizarTablaTratamientos() {
            if (tratamientosAgregados.length === 0) {
                tratamientosVacio.classList.remove('d-none');
                tablaTratamientosContainer.classList.add('d-none');
                return;
            }

            // Mostrar tabla, ocultar mensaje de vacío
            tratamientosVacio.classList.add('d-none');
            tablaTratamientosContainer.classList.remove('d-none');

            // Limpiar tabla
            listaTratamientos.innerHTML = '';

            // Generar filas
            tratamientosAgregados.forEach((trat, index) => {
                const row = document.createElement('tr');

                // Para destacar la fila recién agregada
                if (index === tratamientosAgregados.length - 1) {
                    row.classList.add('highlight-success');
                }

                row.innerHTML = `
                <td>${trat.medicacion}</td>
                <td>${trat.dosis}</td>
                <td>${trat.frecuencia}</td>
                <td>${trat.duracion}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-eliminar-tratamiento" data-index="${index}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;

                listaTratamientos.appendChild(row);
            });
        }

        function finalizarConsulta() {
            // Validar que al menos se haya agregado un medicamento
            if (medicamentosAgregados.length === 0) {
                Swal.fire({
                    title: 'Faltan medicamentos',
                    text: 'Debe agregar al menos un medicamento a la receta',
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            // Solicitar confirmación
            Swal.fire({
                title: '¿Finalizar Consulta?',
                text: 'Se registrará la historia clínica, la receta y los tratamientos',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, finalizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    procesarRegistroConsulta();
                }
            });
        }

        function procesarRegistroConsulta() {
            // Indicador sutil del proceso
            btnFinalizar.disabled = true;
            btnFinalizar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';

            // 1. Registrar historia clínica
            const formHistoriaData = new FormData();
            formHistoriaData.append('idconsulta', idconsultaInput.value);
            formHistoriaData.append('enfermedadactual', enfermedadActual.value);
            formHistoriaData.append('examenfisico', examenFisico.value);
            formHistoriaData.append('evolucion', evolucion.value);
            formHistoriaData.append('iddiagnostico', iddiagnostico.value);
            formHistoriaData.append('altamedica', altamedica.checked ? '1' : '0');

            fetch('<?= $host ?>/controllers/historiaclinica.controller.php?op=registrar_consulta_historia', {
                    method: 'POST',
                    body: formHistoriaData
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.status) {
                        throw new Error(data.mensaje || 'Error al registrar la historia clínica');
                    }

                    // 2. Registrar la receta principal
                    const recetaPromises = [];

                    // Registrar todos los medicamentos
                    medicamentosAgregados.forEach((med, index) => {
                        const recetaData = new FormData();
                        recetaData.append('idconsulta', idconsultaInput.value);
                        recetaData.append('medicacion', med.medicacion);
                        recetaData.append('cantidad', med.cantidad);
                        recetaData.append('frecuencia', med.frecuencia);

                        // Solo agregar observaciones al primer medicamento
                        if (index === 0) {
                            recetaData.append('observaciones', observaciones.value);
                        }

                        recetaPromises.push(
                            fetch('<?= $host ?>/controllers/receta.controller.php?op=registrar', {
                                method: 'POST',
                                body: recetaData
                            }).then(response => response.json())
                        );
                    });

                    return Promise.all(recetaPromises);
                })
                .then(recetasResults => {
                    // 3. Registrar tratamientos si hay
                    const tratamientoPromises = [];

                    if (tratamientosAgregados.length > 0) {
                        // Convertir a JSON para enviar
                        const tratamientosJson = JSON.stringify(tratamientosAgregados);

                        const tratamientoData = new FormData();
                        tratamientoData.append('idconsulta', idconsultaInput.value);
                        tratamientoData.append('tratamientos_json', tratamientosJson);

                        tratamientoPromises.push(
                            fetch('<?= $host ?>/controllers/tratamiento.controller.php?op=registrar_multiple', {
                                method: 'POST',
                                body: tratamientoData
                            }).then(response => response.json())
                        );
                    }

                    return Promise.all(tratamientoPromises);
                })
                .then(() => {
                    // 4. Actualizar estado de la cita a REALIZADA
                    const citaData = new FormData();
                    citaData.append('idcita', idcitaInput.value);
                    citaData.append('estado', 'REALIZADA');

                    return fetch('<?= $host ?>/controllers/cita.controller.php?op=actualizar_estado', {
                        method: 'POST',
                        body: citaData
                    });
                })
                .then(response => response.json())
                .then(data => {
                    // Restaurar botón
                    btnFinalizar.disabled = false;
                    btnFinalizar.innerHTML = '<i class="fas fa-save me-1"></i>Finalizar Consulta';

                    // Mensaje de éxito
                    Swal.fire({
                        title: 'Consulta Completada',
                        text: 'La consulta ha sido registrada correctamente',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Volver al listado de citas
                        volverAListadoCitas();
                    });
                })
                .catch(error => {
                    // Restaurar botón
                    btnFinalizar.disabled = false;
                    btnFinalizar.innerHTML = '<i class="fas fa-save me-1"></i>Finalizar Consulta';

                    console.error('Error al procesar la consulta:', error);

                    Swal.fire({
                        title: 'Error',
                        text: error.message || 'Ha ocurrido un error al procesar la consulta',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                });
        }
    });
</script>

<?php
// Incluir el footer
include_once "../../include/footer.php";
?>