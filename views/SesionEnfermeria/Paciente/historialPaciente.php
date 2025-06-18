<?php
// Incluir el encabezado del enfermero
include_once "../../include/header.enfermeria.php";

// Conexión a la base de datos
require_once "../../../models/Conexion.php";
$conexion = new Conexion();
$pdo = $conexion->getConexion();

// Obtener ID del doctor actual de la sesión
$idDoctorActual = $_SESSION['usuario']['idcolaborador'] ?? 0;

// Parámetros de filtrado y paginación
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$registrosPorPagina = 10;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

// Construir la consulta base
$whereConditions = [];
$params = [];

// Solo agregar filtro de fechas si están presentes
if (!empty($fechaInicio) && !empty($fechaFin)) {
    $whereConditions[] = "c.fecha BETWEEN :fecha_inicio AND :fecha_fin";
    $params[':fecha_inicio'] = $fechaInicio;
    $params[':fecha_fin'] = $fechaFin;
} elseif (!empty($fechaInicio)) {
    $whereConditions[] = "c.fecha >= :fecha_inicio";
    $params[':fecha_inicio'] = $fechaInicio;
} elseif (!empty($fechaFin)) {
    $whereConditions[] = "c.fecha <= :fecha_fin";
    $params[':fecha_fin'] = $fechaFin;
}

// Agregar condición de búsqueda si existe
if (!empty($busqueda)) {
    $whereConditions[] = "(p.nombres LIKE :busqueda OR p.apellidos LIKE :busqueda OR p.nrodoc LIKE :busqueda)";
    $params[':busqueda'] = '%' . $busqueda . '%';
}

// Primero verificamos la estructura de las tablas para adaptar las consultas
try {
    // Verificar estructura de tabla consultas
    $checkConsultas = $pdo->query("DESCRIBE consultas");
    $consultasColumns = $checkConsultas->fetchAll(PDO::FETCH_COLUMN);
    
    // Verificar estructura de tabla triajes  
    $checkTriajes = $pdo->query("DESCRIBE triajes");
    $triajesColumns = $checkTriajes->fetchAll(PDO::FETCH_COLUMN);
    
    // Determinar cómo obtener la especialidad y enfermero
    $hasIdColaborador = in_array('idcolaborador', $consultasColumns);
    $hasIdDoctor = in_array('iddoctor', $consultasColumns);
    $hasIdEnfermero = in_array('idenfermero', $triajesColumns);
    $hasIdEnfermeroUsuario = in_array('idusuario_enfermero', $triajesColumns);
    
} catch (Exception $e) {
    // Si hay error, usar consulta básica
    $hasIdColaborador = false;
    $hasIdDoctor = false;
    $hasIdEnfermero = false;
    $hasIdEnfermeroUsuario = false;
}

// Construir consulta adaptada según la estructura INCLUYENDO HISTORIA CLÍNICA
if ($hasIdColaborador) {
    // Si tiene idcolaborador
    $joinEspecialidad = "LEFT JOIN colaboradores col ON c.idcolaborador = col.idcolaborador
                        LEFT JOIN especialidades e ON col.idespecialidad = e.idespecialidad
                        LEFT JOIN personas p_doc ON col.idpersona = p_doc.idpersona";
    $filtroDoctor = "AND c.idcolaborador = :iddoctor";
} elseif ($hasIdDoctor) {
    // Si tiene iddoctor
    $joinEspecialidad = "LEFT JOIN colaboradores col ON c.iddoctor = col.idcolaborador
                        LEFT JOIN especialidades e ON col.idespecialidad = e.idespecialidad  
                        LEFT JOIN personas p_doc ON col.idpersona = p_doc.idpersona";
    $filtroDoctor = "AND c.iddoctor = :iddoctor";
} else {
    // Sin especialidad disponible
    $joinEspecialidad = "";
    $filtroDoctor = "";
}

if ($hasIdEnfermero) {
    // Si tiene idenfermero directo
    $joinEnfermero = "LEFT JOIN personas p_enf ON t.idenfermero = p_enf.idpersona";
} elseif ($hasIdEnfermeroUsuario) {
    // Si tiene idusuario_enfermero
    $joinEnfermero = "LEFT JOIN usuarios u_enf ON t.idusuario_enfermero = u_enf.idusuario
                     LEFT JOIN colaboradores col_enf ON u_enf.idcolaborador = col_enf.idcolaborador
                     LEFT JOIN personas p_enf ON col_enf.idpersona = p_enf.idpersona";
} else {
    // Sin enfermero disponible
    $joinEnfermero = "";
}

// MODIFICACIÓN PRINCIPAL: Agregar JOIN con historia clínica
$joinHistoriaClinica = "INNER JOIN historiaclinica hc ON c.idconsulta = hc.idconsulta";

// Actualizar condiciones WHERE para incluir filtro de doctor Y historia clínica
$whereConditions[] = "hc.idconsulta IS NOT NULL"; // Asegurar que tiene historia clínica

// Agregar filtro de doctor si está disponible
if (!empty($filtroDoctor) && $idDoctorActual > 0) {
    $whereConditions[] = str_replace('AND ', '', $filtroDoctor);
    $params[':iddoctor'] = $idDoctorActual;
}

// Si no hay condiciones WHERE, agregar una condición verdadera
if (empty($whereConditions)) {
    $whereConditions[] = "1=1";
}

$whereClause = implode(' AND ', $whereConditions);

// Consulta para obtener el total de registros CON HISTORIA CLÍNICA
$queryTotal = "SELECT COUNT(*) as total 
               FROM triajes t
               INNER JOIN consultas c ON t.idconsulta = c.idconsulta
               $joinHistoriaClinica
               INNER JOIN pacientes pac ON c.idpaciente = pac.idpaciente
               INNER JOIN personas p ON pac.idpersona = p.idpersona
               $joinEspecialidad
               $joinEnfermero
               WHERE $whereClause";

$stmtTotal = $pdo->prepare($queryTotal);
$stmtTotal->execute($params);
$totalRegistros = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Construir SELECT incluyendo datos de historia clínica
$selectEspecialidad = $joinEspecialidad ? 
    "COALESCE(e.especialidad, 'General') as especialidad_nombre,
     COALESCE(e.idespecialidad, 0) as idespecialidad,
     COALESCE(CONCAT(p_doc.nombres, ' ', p_doc.apellidos), 'No asignado') as doctor_nombre," : 
    "'General' as especialidad_nombre,
     0 as idespecialidad,
     'No asignado' as doctor_nombre,";

$selectEnfermero = $joinEnfermero ?
    "COALESCE(CONCAT(p_enf.nombres, ' ', p_enf.apellidos), 'No registrado') as enfermero_nombre,
     COALESCE(p_enf.idpersona, 0) as idenfermero," :
    "'No registrado' as enfermero_nombre,
     0 as idenfermero,";

// Consulta principal para obtener los triajes CON HISTORIA CLÍNICA
$queryTriajes = "SELECT 
                    t.idtriaje,
                    t.hora,
                    c.fecha,
                    c.idconsulta,
                    p.nombres,
                    p.apellidos,
                    p.nrodoc,
                    CONCAT(p.nombres, ' ', p.apellidos) as nombre_completo,
                    t.temperatura,
                    t.presionarterial,
                    t.frecuenciacardiaca,
                    t.saturacionoxigeno,
                    hc.idhistoriaclinica,
                    hc.enfermedadactual,
                    hc.examenfisico,
                    hc.evolucion,
                    hc.observaciones,
                    hc.altamedica,
                    $selectEspecialidad
                    $selectEnfermero
                    'COMPLETADO' as estado_triaje
                FROM triajes t
                INNER JOIN consultas c ON t.idconsulta = c.idconsulta
                $joinHistoriaClinica
                INNER JOIN pacientes pac ON c.idpaciente = pac.idpaciente
                INNER JOIN personas p ON pac.idpersona = p.idpersona
                $joinEspecialidad
                $joinEnfermero
                WHERE $whereClause
                ORDER BY c.fecha DESC, t.hora DESC
                LIMIT :limit OFFSET :offset";

$stmtTriajes = $pdo->prepare($queryTriajes);
foreach ($params as $key => $value) {
    $stmtTriajes->bindValue($key, $value);
}
$stmtTriajes->bindValue(':limit', $registrosPorPagina, PDO::PARAM_INT);
$stmtTriajes->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtTriajes->execute();
$triajes = $stmtTriajes->fetchAll(PDO::FETCH_ASSOC);

// Estadísticas del período seleccionado (adaptadas para triajes completados)
$selectEstadisticasEspecialidad = $joinEspecialidad ? "COUNT(DISTINCT e.idespecialidad) as total_especialidades," : "0 as total_especialidades,";
$selectEstadisticasEnfermero = $joinEnfermero ? "COUNT(DISTINCT p_enf.idpersona) as total_enfermeros," : "0 as total_enfermeros,";

$queryEstadisticas = "SELECT 
                        COUNT(*) as total_triajes,
                        AVG(t.temperatura) as temp_promedio,
                        AVG(t.frecuenciacardiaca) as fc_promedio,
                        AVG(t.saturacionoxigeno) as spo2_promedio,
                        SUM(CASE WHEN hc.altamedica = 1 THEN 1 ELSE 0 END) as total_altas,
                        $selectEstadisticasEspecialidad
                        $selectEstadisticasEnfermero
                        COUNT(DISTINCT hc.idhistoriaclinica) as total_historias_completas
                    FROM triajes t
                    INNER JOIN consultas c ON t.idconsulta = c.idconsulta
                    $joinHistoriaClinica
                    INNER JOIN pacientes pac ON c.idpaciente = pac.idpaciente
                    INNER JOIN personas p ON pac.idpersona = p.idpersona
                    $joinEspecialidad
                    $joinEnfermero
                    WHERE $whereClause";

$stmtEstadisticas = $pdo->prepare($queryEstadisticas);
$stmtEstadisticas->execute($params);
$estadisticas = $stmtEstadisticas->fetch(PDO::FETCH_ASSOC);

// Debug: Mostrar información sobre estructura de tablas (solo en desarrollo)
$showDebug = isset($_GET['debug']) && $_GET['debug'] == '1';
if ($showDebug) {
    echo "<div class='alert alert-info'>";
    echo "<h5>Debug - Estructura de Tablas:</h5>";
    echo "<strong>Consultas:</strong> " . implode(', ', $consultasColumns ?? []) . "<br>";
    echo "<strong>Triajes:</strong> " . implode(', ', $triajesColumns ?? []) . "<br>";
    echo "<strong>Doctor actual:</strong> " . $idDoctorActual . "<br>";
    echo "<strong>Usando idcolaborador:</strong> " . ($hasIdColaborador ? 'Sí' : 'No') . "<br>";
    echo "<strong>Usando iddoctor:</strong> " . ($hasIdDoctor ? 'Sí' : 'No') . "<br>";
    echo "<strong>Usando idenfermero:</strong> " . ($hasIdEnfermero ? 'Sí' : 'No') . "<br>";
    echo "<strong>Usando idusuario_enfermero:</strong> " . ($hasIdEnfermeroUsuario ? 'Sí' : 'No') . "<br>";
    echo "<strong>Total triajes con historia:</strong> " . $totalRegistros . "<br>";
    echo "</div>";
}

// Función para obtener el color de la especialidad
function getEspecialidadColor($especialidad) {
    $colors = [
        'General' => 'info',
        'Cardiología' => 'danger',
        'Neurología' => 'primary',
        'Pediatría' => 'success',
        'Ginecología' => 'warning',
        'Traumatología' => 'secondary',
        'Dermatología' => 'light',
        'Psiquiatría' => 'dark'
    ];
    
    return $colors[$especialidad] ?? 'info';
}

// Función para obtener iniciales del enfermero
function getEnfermeroInitials($nombre) {
    if ($nombre === 'No registrado') return 'NR';
    $parts = explode(' ', $nombre);
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    return $initials;
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Triajes Completados - Dr. <?= $_SESSION['usuario']['nombres'] ?? 'Usuario' ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= $host ?>/views/Enfermeria/dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Triajes Completados</li>
    </ol>

    <!-- Alerta informativa -->
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Información:</strong> Solo se muestran triajes que han sido completados con historia clínica y están asignados a usted.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <!-- Filtros y estadísticas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros y Estadísticas</h5>
                </div>
                <div class="card-body">
                    <!-- Formulario de filtros -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= $fechaInicio ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= $fechaFin ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="busqueda" class="form-label">Buscar Paciente</label>
                            <input type="text" class="form-control" id="busqueda" name="busqueda" value="<?= $busqueda ?>" placeholder="Nombre, apellido o documento">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>Filtrar
                            </button>
                        </div>
                    </form>

                    <!-- Estadísticas mejoradas -->
                    <div class="row">
                        <div class="col-md-2">
                            <div class="bg-primary text-white p-3 rounded text-center">
                                <h4 class="mb-1"><?= $estadisticas['total_triajes'] ?></h4>
                                <small>Triajes Completados</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-success text-white p-3 rounded text-center">
                                <h4 class="mb-1"><?= $estadisticas['total_altas'] ?? 0 ?></h4>
                                <small>Altas Médicas</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-warning text-white p-3 rounded text-center">
                                <h4 class="mb-1"><?= number_format($estadisticas['temp_promedio'], 1) ?>°C</h4>
                                <small>Temp. Promedio</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-info text-white p-3 rounded text-center">
                                <h4 class="mb-1"><?= round($estadisticas['fc_promedio']) ?> lpm</h4>
                                <small>FC Promedio</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-secondary text-white p-3 rounded text-center">
                                <h4 class="mb-1"><?= $estadisticas['total_especialidades'] ?? 0 ?></h4>
                                <small>Especialidades</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-dark text-white p-3 rounded text-center">
                                <h4 class="mb-1"><?= $estadisticas['total_enfermeros'] ?? 0 ?></h4>
                                <small>Enfermeros(as)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de triajes -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-heartbeat me-2"></i>Triajes Completados con Historia Clínica</h5>
                        <div>
                            <a href="<?= $host ?>/views/Enfermeria/Triaje/registrarTriaje.php" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i>Nuevo Triaje
                            </a>
                            <button class="btn btn-light btn-sm" onclick="exportarExcelCompletados()">
                                <i class="fas fa-file-excel me-1"></i>Exportar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (count($triajes) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-nowrap"><i class="far fa-calendar-alt me-1"></i>Fecha/Hora</th>
                                        <th><i class="fas fa-user me-1"></i>Paciente</th>
                                        <th><i class="fas fa-stethoscope me-1"></i>Especialidad</th>
                                        <th class="text-center"><i class="fas fa-thermometer-half me-1"></i>Signos Vitales</th>
                                        <th><i class="fas fa-file-medical me-1"></i>Historia Clínica</th>
                                        <th class="text-center"><i class="fas fa-user-nurse me-1"></i>Enfermero(a)</th>
                                        <th class="text-center"><i class="fas fa-cog me-1"></i>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($triajes as $triaje): ?>
                                        <tr>
                                            <td class="text-nowrap">
                                                <div class="fw-bold"><?= date('d/m/Y', strtotime($triaje['fecha'])) ?></div>
                                                <small class="text-muted"><?= date('H:i', strtotime($triaje['hora'])) ?></small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2 bg-primary rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                        <?= strtoupper(substr($triaje['nombres'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold"><?= $triaje['nombre_completo'] ?></div>
                                                        <small class="text-muted"><?= $triaje['nrodoc'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= getEspecialidadColor($triaje['especialidad_nombre']) ?> rounded-pill mb-1">
                                                    <?= $triaje['especialidad_nombre'] ?>
                                                </span>
                                                <div>
                                                    <small class="text-muted">
                                                        <?php if ($triaje['doctor_nombre'] !== 'No asignado'): ?>
                                                            Dr. <?= $triaje['doctor_nombre'] ?>
                                                        <?php else: ?>
                                                            No asignado
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex flex-column align-items-center">
                                                    <span class="badge <?= $triaje['temperatura'] > 37.5 ? 'bg-danger' : ($triaje['temperatura'] < 36 ? 'bg-warning' : 'bg-success') ?> mb-1">
                                                        <?= number_format($triaje['temperatura'], 1) ?>°C
                                                    </span>
                                                    <span class="fw-bold mb-1"><?= $triaje['presionarterial'] ?></span>
                                                    <span class="badge <?= $triaje['frecuenciacardiaca'] > 100 ? 'bg-warning' : ($triaje['frecuenciacardiaca'] < 60 ? 'bg-info' : 'bg-success') ?> mb-1">
                                                        <?= $triaje['frecuenciacardiaca'] ?> lpm
                                                    </span>
                                                    <span class="badge <?= $triaje['saturacionoxigeno'] < 95 ? 'bg-danger' : 'bg-success' ?>">
                                                        <?= $triaje['saturacionoxigeno'] ?>%
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-success me-2">
                                                        <i class="fas fa-check-circle"></i> Completada
                                                    </span>
                                                    <?php if ($triaje['altamedica'] == 1): ?>
                                                        <span class="badge bg-info">
                                                            <i class="fas fa-hospital-user"></i> Alta
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-clock"></i> En tratamiento
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="mt-1">
                                                    <small class="text-muted">
                                                        ID HC: <?= $triaje['idhistoriaclinica'] ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($triaje['enfermero_nombre'] !== 'No registrado'): ?>
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <div class="avatar avatar-xs me-1 bg-success rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 25px; height: 25px; font-size: 10px;">
                                                            <?= getEnfermeroInitials($triaje['enfermero_nombre']) ?>
                                                        </div>
                                                        <small class="text-muted"><?= $triaje['enfermero_nombre'] ?></small>
                                                    </div>
                                                <?php else: ?>
                                                    <small class="text-muted">No registrado</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="<?= $host ?>/views/Enfermeria/HistorialTriaje/verTriaje.php?id=<?= $triaje['idtriaje'] ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Ver triaje">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= $host ?>/views/HistoriaClinica/verHistoria.php?id=<?= $triaje['idhistoriaclinica'] ?>" 
                                                       class="btn btn-sm btn-outline-success" title="Ver historia clínica">
                                                        <i class="fas fa-file-medical"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-info" 
                                                            onclick="enviarADoctor(<?= $triaje['idconsulta'] ?>, '<?= $triaje['nombre_completo'] ?>')" 
                                                            title="Enviar a doctor">
                                                        <i class="fas fa-user-md"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning" 
                                                            onclick="imprimirTriaje(<?= $triaje['idtriaje'] ?>)" title="Imprimir">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                    <?php if ($triaje['enfermero_nombre'] !== 'No registrado' && $triaje['idenfermero'] > 0): ?>
                                                        <button class="btn btn-sm btn-outline-dark" 
                                                                onclick="verDetallesEnfermero(<?= $triaje['idenfermero'] ?>)" title="Ver enfermero">
                                                            <i class="fas fa-user-nurse"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <img src="<?= $host ?>/assets/img/no-data.svg" alt="No hay triajes" class="img-fluid mb-3" style="max-height: 200px;">
                            <h5 class="text-muted">No se encontraron triajes completados</h5>
                            <p class="text-muted">No hay triajes con historia clínica completada para los filtros seleccionados</p>
                            <a href="<?= $host ?>/views/Enfermeria/Triaje/registrarTriaje.php" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Registrar Nuevo Triaje
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Paginación -->
                <?php if ($totalPaginas > 1): ?>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Mostrando <?= ($offset + 1) ?> a <?= min($offset + $registrosPorPagina, $totalRegistros) ?> de <?= $totalRegistros ?> registros
                            </small>
                            <nav aria-label="Paginación de triajes">
                                <ul class="pagination pagination-sm mb-0">
                                    <!-- Página anterior -->
                                    <?php if ($paginaActual > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $paginaActual - 1])) ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <!-- Números de página -->
                                    <?php
                                    $inicio = max(1, $paginaActual - 2);
                                    $fin = min($totalPaginas, $paginaActual + 2);
                                    
                                    for ($i = $inicio; $i <= $fin; $i++): ?>
                                        <li class="page-item <?= $i == $paginaActual ? 'active' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Página siguiente -->
                                    <?php if ($paginaActual < $totalPaginas): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $paginaActual + 1])) ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function imprimirTriaje(idTriaje) {
    window.open('<?= $host ?>/views/Enfermeria/HistorialTriaje/imprimirTriaje.php?id=' + idTriaje, '_blank');
}

function exportarExcelCompletados() {
    const params = new URLSearchParams(window.location.search);
    params.set('exportar', 'excel');
    params.set('solo_completados', '1'); // Nuevo parámetro para indicar solo triajes completados
    window.location.href = '<?= $host ?>/views/Enfermeria/HistorialTriaje/exportarTriajes.php?' + params.toString();
}

function exportarExcel() {
    exportarExcelCompletados();
}

function verDetallesEnfermero(idEnfermero) {
    if (idEnfermero && idEnfermero > 0) {
        // Abrir modal o nueva ventana con detalles del enfermero
        window.open('<?= $host ?>/views/Enfermeria/VerEnfermero/verEnfermero.php?id=' + idEnfermero, '_blank');
    }
}

function verDetallesHistoria(idHistoriaClinica) {
    // Abrir la historia clínica en una nueva ventana
    window.open('<?= $host ?>/views/HistoriaClinica/verHistoria.php?id=' + idHistoriaClinica, '_blank');
}

function enviarADoctor(idConsulta, nombrePaciente) {
    Swal.fire({
        title: '¿Enviar consulta al doctor?',
        html: `
            <div class="text-start">
                <p>Se enviará la consulta completada al doctor correspondiente:</p>
                <ul>
                    <li><strong>Paciente:</strong> ${nombrePaciente}</li>
                    <li><strong>Consulta ID:</strong> ${idConsulta}</li>
                    <li><strong>Estado:</strong> Triaje completado con historia clínica</li>
                </ul>
                <p class="text-muted mt-3">El doctor recibirá una notificación y podrá ver toda la información del triaje y la historia clínica.</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Enviando...',
                text: 'Notificando al doctor',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Enviar la notificación
            fetch('<?= $host ?>/controllers/consulta.controller.php?op=enviar_a_doctor', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `idconsulta=${idConsulta}&accion=notificar_doctor`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    Swal.fire({
                        title: '¡Enviado!',
                        text: 'La consulta ha sido enviada al doctor correctamente',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    });
                    
                    // Opcional: Recargar la página para actualizar estados
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                } else {
                    throw new Error(data.mensaje || 'Error al enviar la consulta');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo enviar la consulta al doctor: ' + error.message,
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
                console.error('Error:', error);
            });
        }
    });
}

function actualizarEstadoConsulta(idConsulta, nuevoEstado) {
    fetch('<?= $host ?>/controllers/consulta.controller.php?op=actualizar_estado', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `idconsulta=${idConsulta}&estado=${nuevoEstado}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            console.log('Estado actualizado correctamente');
        } else {
            console.error('Error al actualizar estado:', data.mensaje);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Auto-envío del formulario al cambiar las fechas
document.getElementById('fecha_inicio').addEventListener('change', function() {
    if (document.getElementById('fecha_fin').value) {
        document.querySelector('form').submit();
    }
});

document.getElementById('fecha_fin').addEventListener('change', function() {
    if (document.getElementById('fecha_inicio').value) {
        document.querySelector('form').submit();
    }
});

// Tooltips para información adicional
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap si están disponibles
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    if (typeof bootstrap !== 'undefined') {
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Agregar indicador visual para triajes completados
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        // Agregar clase especial para triajes completados
        row.classList.add('triaje-completado');
        
        // Agregar efecto hover mejorado
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(40, 167, 69, 0.1)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
    
    // Mostrar notificación de bienvenida
    if (document.querySelectorAll('tbody tr').length > 0) {
        setTimeout(() => {
            Swal.fire({
                title: 'Triajes Completados',
                text: 'Mostrando solo triajes con historia clínica completada asignados a usted',
                icon: 'info',
                timer: 3000,
                timerProgressBar: true,
                toast: true,
                position: 'top-end',
                showConfirmButton: false
            });
        }, 1000);
    }
});
</script>

<style>
.avatar {
    font-size: 14px;
    font-weight: bold;
}

.avatar-xs {
    font-size: 10px;
    font-weight: bold;
}

.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
    font-size: 13px;
}

.table td {
    vertical-align: middle;
    font-size: 13px;
}

.badge {
    font-size: 11px;
}

.btn-group .btn {
    border-radius: 0.25rem;
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.pagination-sm .page-link {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Mejoras visuales para especialidades */
.badge.bg-info { background-color: #0dcaf0 !important; }
.badge.bg-danger { background-color: #dc3545 !important; }
.badge.bg-success { background-color: #198754 !important; }
.badge.bg-warning { background-color: #ffc107 !important; color: #000; }
.badge.bg-secondary { background-color: #6c757d !important; }
.badge.bg-primary { background-color: #0d6efd !important; }
.badge.bg-light { background-color: #f8f9fa !important; color: #000; border: 1px solid #dee2e6; }
.badge.bg-dark { background-color: #212529 !important; }

/* Estilo para enfermeros */
.avatar.bg-success { background-color: #198754 !important; }

/* Efecto hover en filas */
.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.025);
}

/* Estilos para triajes completados */
.triaje-completado {
    border-left: 3px solid #28a745;
}

/* Gradiente para el header */
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}
</style>

<?php
// Incluir el footer
include_once "../../include/footer.php";
?>