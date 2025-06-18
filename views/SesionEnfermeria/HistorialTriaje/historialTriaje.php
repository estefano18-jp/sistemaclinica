<?php
// Incluir el encabezado del enfermero
include_once "../../include/header.enfermeria.php";

// Conexión a la base de datos
require_once "../../../models/Conexion.php";
$conexion = new Conexion();
$pdo = $conexion->getConexion();

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

// Si no hay condiciones WHERE, agregar una condición verdadera
if (empty($whereConditions)) {
    $whereConditions[] = "1=1";
}

$whereClause = implode(' AND ', $whereConditions);

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

// Construir consulta adaptada según la estructura
if ($hasIdColaborador) {
    // Si tiene idcolaborador
    $joinEspecialidad = "LEFT JOIN colaboradores col ON c.idcolaborador = col.idcolaborador
                        LEFT JOIN especialidades e ON col.idespecialidad = e.idespecialidad
                        LEFT JOIN personas p_doc ON col.idpersona = p_doc.idpersona";
} elseif ($hasIdDoctor) {
    // Si tiene iddoctor
    $joinEspecialidad = "LEFT JOIN colaboradores col ON c.iddoctor = col.idcolaborador
                        LEFT JOIN especialidades e ON col.idespecialidad = e.idespecialidad  
                        LEFT JOIN personas p_doc ON col.idpersona = p_doc.idpersona";
} else {
    // Sin especialidad disponible
    $joinEspecialidad = "";
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

// Consulta para obtener el total de registros
$queryTotal = "SELECT COUNT(*) as total 
               FROM triajes t
               INNER JOIN consultas c ON t.idconsulta = c.idconsulta
               INNER JOIN pacientes pac ON c.idpaciente = pac.idpaciente
               INNER JOIN personas p ON pac.idpersona = p.idpersona
               $joinEspecialidad
               $joinEnfermero
               WHERE $whereClause";

$stmtTotal = $pdo->prepare($queryTotal);
$stmtTotal->execute($params);
$totalRegistros = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Construir SELECT según campos disponibles
$selectEspecialidad = $joinEspecialidad ? 
    "COALESCE(e.especialidad, 'General') as especialidad_nombre,
     COALESCE(e.idespecialidad, 0) as idespecialidad,
     COALESCE(CONCAT(p_doc.nombres, ' ', p_doc.apellidos), 'No asignado') as doctor_nombre," : 
    "'General' as especialidad_nombre,
     0 as idespecialidad,
     'No asignado' as doctor_nombre,";

$selectEnfermero = $joinEnfermero ?
    "COALESCE(CONCAT(p_enf.nombres, ' ', p_enf.apellidos), 'No registrado') as enfermero_nombre,
     COALESCE(p_enf.idpersona, 0) as idenfermero" :
    "'No registrado' as enfermero_nombre,
     0 as idenfermero";

// Consulta principal para obtener los triajes
$queryTriajes = "SELECT 
                    t.idtriaje,
                    t.hora,
                    c.fecha,
                    p.nombres,
                    p.apellidos,
                    p.nrodoc,
                    CONCAT(p.nombres, ' ', p.apellidos) as nombre_completo,
                    t.temperatura,
                    t.presionarterial,
                    t.frecuenciacardiaca,
                    t.saturacionoxigeno,
                    $selectEspecialidad
                    $selectEnfermero
                FROM triajes t
                INNER JOIN consultas c ON t.idconsulta = c.idconsulta
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

// Estadísticas del período seleccionado (adaptadas)
$selectEstadisticasEspecialidad = $joinEspecialidad ? "COUNT(DISTINCT e.idespecialidad) as total_especialidades," : "0 as total_especialidades,";
$selectEstadisticasEnfermero = $joinEnfermero ? "COUNT(DISTINCT p_enf.idpersona) as total_enfermeros" : "0 as total_enfermeros";

$queryEstadisticas = "SELECT 
                        COUNT(*) as total_triajes,
                        AVG(t.temperatura) as temp_promedio,
                        AVG(t.frecuenciacardiaca) as fc_promedio,
                        AVG(t.saturacionoxigeno) as spo2_promedio,
                        $selectEstadisticasEspecialidad
                        $selectEstadisticasEnfermero
                    FROM triajes t
                    INNER JOIN consultas c ON t.idconsulta = c.idconsulta
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
    echo "<strong>Usando idcolaborador:</strong> " . ($hasIdColaborador ? 'Sí' : 'No') . "<br>";
    echo "<strong>Usando iddoctor:</strong> " . ($hasIdDoctor ? 'Sí' : 'No') . "<br>";
    echo "<strong>Usando idenfermero:</strong> " . ($hasIdEnfermero ? 'Sí' : 'No') . "<br>";
    echo "<strong>Usando idusuario_enfermero:</strong> " . ($hasIdEnfermeroUsuario ? 'Sí' : 'No') . "<br>";
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
    <h1 class="mt-4">Historial de Triajes</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= $host ?>/views/Enfermeria/dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Historial de Triajes</li>
    </ol>

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
                                <small>Total Triajes</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-warning text-white p-3 rounded text-center">
                                <h4 class="mb-1"><?= number_format($estadisticas['temp_promedio'], 1) ?>°C</h4>
                                <small>Temp. Promedio</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-success text-white p-3 rounded text-center">
                                <h4 class="mb-1"><?= round($estadisticas['fc_promedio']) ?> lpm</h4>
                                <small>FC Promedio</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-info text-white p-3 rounded text-center">
                                <h4 class="mb-1"><?= round($estadisticas['spo2_promedio']) ?>%</h4>
                                <small>SpO2 Promedio</small>
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
                        <h5 class="mb-0"><i class="fas fa-heartbeat me-2"></i>Triajes Realizados</h5>
                        <div>
                            <a href="<?= $host ?>/views/Enfermeria/Triaje/registrarTriaje.php" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i>Nuevo Triaje
                            </a>
                            <button class="btn btn-light btn-sm" onclick="exportarExcel()">
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
                                        <th class="text-center"><i class="fas fa-thermometer-half me-1"></i>Temp</th>
                                        <th class="text-center"><i class="fas fa-heartbeat me-1"></i>P.A.</th>
                                        <th class="text-center"><i class="fas fa-heart me-1"></i>FC</th>
                                        <th class="text-center"><i class="fas fa-lungs me-1"></i>SpO2</th>
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
                                                <span class="badge <?= $triaje['temperatura'] > 37.5 ? 'bg-danger' : ($triaje['temperatura'] < 36 ? 'bg-warning' : 'bg-success') ?>">
                                                    <?= number_format($triaje['temperatura'], 1) ?>°C
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold"><?= $triaje['presionarterial'] ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?= $triaje['frecuenciacardiaca'] > 100 ? 'bg-warning' : ($triaje['frecuenciacardiaca'] < 60 ? 'bg-info' : 'bg-success') ?>">
                                                    <?= $triaje['frecuenciacardiaca'] ?> lpm
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?= $triaje['saturacionoxigeno'] < 95 ? 'bg-danger' : 'bg-success' ?>">
                                                    <?= $triaje['saturacionoxigeno'] ?>%
                                                </span>
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
                                                       class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-success" 
                                                            onclick="imprimirTriaje(<?= $triaje['idtriaje'] ?>)" title="Imprimir">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                    <?php if ($triaje['enfermero_nombre'] !== 'No registrado' && $triaje['idenfermero'] > 0): ?>
                                                        <button class="btn btn-sm btn-outline-info" 
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
                            <h5 class="text-muted">No se encontraron triajes</h5>
                            <p class="text-muted">No hay triajes registrados para los filtros seleccionados</p>
                            <a href="<?= $host ?>/views/Enfermeria/Triaje/registrarTriaje.php" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Registrar Primer Triaje
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
<script>
function imprimirTriaje(idTriaje) {
    window.open('<?= $host ?>/views/Enfermeria/HistorialTriaje/imprimirTriaje.php?id=' + idTriaje, '_blank');
}

function exportarExcel() {
    const params = new URLSearchParams(window.location.search);
    params.set('exportar', 'excel');
    window.location.href = '<?= $host ?>/views/Enfermeria/HistorialTriaje/exportarTriajes.php?' + params.toString();
}

function verDetallesEnfermero(idEnfermero) {
    if (idEnfermero && idEnfermero > 0) {
        // Abrir modal o nueva ventana con detalles del enfermero
        window.open('<?= $host ?>/views/Enfermeria/VerEnfermero/verEnfermero.php?id=' + idEnfermero, '_blank');
    }
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
</style>

<?php
// Incluir el footer
include_once "../../include/footer.php";

/* 
NOTA PARA DEBUG:
Si sigues teniendo problemas, agrega ?debug=1 al final de la URL para ver la estructura de las tablas.
Ejemplo: historialTriaje.php?debug=1

Esto te mostrará:
- Qué columnas existen en las tablas 'consultas' y 'triajes'
- Qué JOINs está intentando usar el código
- Te ayudará a identificar los nombres correctos de las columnas

Posibles nombres de columnas que podrían existir:
- En consultas: idcolaborador, iddoctor, idmedico, idespecialista
- En triajes: idenfermero, idusuario_enfermero, idcolaborador_enfermero, created_by
*/
?>