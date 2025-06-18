<?php
// Incluir el encabezado del enfermero
include_once "../include/header.enfermeria.php";

// Conexión a la base de datos
require_once "../../models/Conexion.php";
$conexion = new Conexion();
$pdo = $conexion->getConexion();

// Fecha actual
$fechaHoy = date('Y-m-d');
$inicioMes = date('Y-m-01');
$finMes = date('Y-m-t');

// CONSULTAS PARA EL DASHBOARD DE ENFERMERÍA

// Total de pacientes registrados
$queryTotalPacientes = "SELECT COUNT(*) as total FROM pacientes";
$stmtTotalPacientes = $pdo->prepare($queryTotalPacientes);
$stmtTotalPacientes->execute();
$totalPacientes = $stmtTotalPacientes->fetch(PDO::FETCH_ASSOC)['total'];

// Total de consultas pendientes para hoy
$queryConsultasPendientes = "SELECT COUNT(*) as total 
                            FROM consultas c
                            LEFT JOIN triajes t ON c.idconsulta = t.idconsulta
                            WHERE c.fecha = :fecha AND t.idtriaje IS NULL";
$stmtConsultasPendientes = $pdo->prepare($queryConsultasPendientes);
$stmtConsultasPendientes->bindParam(':fecha', $fechaHoy);
$stmtConsultasPendientes->execute();
$totalConsultasPendientes = $stmtConsultasPendientes->fetch(PDO::FETCH_ASSOC)['total'];

// Total de triajes realizados hoy
$queryTriajesHoy = "SELECT COUNT(*) as total 
                    FROM triajes t
                    INNER JOIN consultas c ON t.idconsulta = c.idconsulta
                    WHERE c.fecha = :fecha";
$stmtTriajesHoy = $pdo->prepare($queryTriajesHoy);
$stmtTriajesHoy->bindParam(':fecha', $fechaHoy);
$stmtTriajesHoy->execute();
$totalTriajesHoy = $stmtTriajesHoy->fetch(PDO::FETCH_ASSOC)['total'];

// Total de triajes realizados en el mes
$queryTriajesMes = "SELECT COUNT(*) as total 
                    FROM triajes t
                    INNER JOIN consultas c ON t.idconsulta = c.idconsulta
                    WHERE c.fecha BETWEEN :inicio AND :fin";
$stmtTriajesMes = $pdo->prepare($queryTriajesMes);
$stmtTriajesMes->bindParam(':inicio', $inicioMes);
$stmtTriajesMes->bindParam(':fin', $finMes);
$stmtTriajesMes->execute();
$totalTriajesMes = $stmtTriajesMes->fetch(PDO::FETCH_ASSOC)['total'];

// Próximas consultas pendientes de triaje
$queryProximasConsultas = "SELECT 
                            c.idconsulta, 
                            c.fecha, 
                            c.horaprogramada, 
                            p.nombres, 
                            p.apellidos,
                            CONCAT(p.nombres, ' ', p.apellidos) as nombre_completo,
                            p.nrodoc,
                            e.especialidad,
                            doc.nombres as doctor_nombre,
                            doc.apellidos as doctor_apellido
                        FROM consultas c
                        INNER JOIN pacientes pac ON c.idpaciente = pac.idpaciente
                        INNER JOIN personas p ON pac.idpersona = p.idpersona
                        INNER JOIN horarios h ON c.idhorario = h.idhorario
                        INNER JOIN atenciones a ON h.idatencion = a.idatencion
                        INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
                        INNER JOIN colaboradores col ON ct.idcolaborador = col.idcolaborador
                        INNER JOIN personas doc ON col.idpersona = doc.idpersona
                        INNER JOIN especialidades e ON col.idespecialidad = e.idespecialidad
                        LEFT JOIN triajes t ON c.idconsulta = t.idconsulta
                        WHERE c.fecha = :fecha AND t.idtriaje IS NULL
                        ORDER BY c.horaprogramada ASC
                        LIMIT 5";
$stmtProximasConsultas = $pdo->prepare($queryProximasConsultas);
$stmtProximasConsultas->bindParam(':fecha', $fechaHoy);
$stmtProximasConsultas->execute();
$proximasConsultas = $stmtProximasConsultas->fetchAll(PDO::FETCH_ASSOC);

// Últimos triajes realizados
$queryUltimosTriajes = "SELECT 
                        t.idtriaje,
                        t.hora,
                        c.fecha,
                        p.nombres,
                        p.apellidos,
                        p.nrodoc,
                        t.temperatura,
                        t.presionarterial,
                        t.frecuenciacardiaca,
                        t.saturacionoxigeno
                    FROM triajes t
                    INNER JOIN consultas c ON t.idconsulta = c.idconsulta
                    INNER JOIN pacientes pac ON c.idpaciente = pac.idpaciente
                    INNER JOIN personas p ON pac.idpersona = p.idpersona
                    ORDER BY c.fecha DESC, t.hora DESC
                    LIMIT 5";
$stmtUltimosTriajes = $pdo->prepare($queryUltimosTriajes);
$stmtUltimosTriajes->execute();
$ultimosTriajes = $stmtUltimosTriajes->fetchAll(PDO::FETCH_ASSOC);

// Obtener datos para el gráfico de triajes por día (últimos 7 días)
$fechaInicio = date('Y-m-d', strtotime('-6 days'));
$queryTriajesPorDia = "SELECT 
                        c.fecha, 
                        COUNT(*) as total
                    FROM triajes t
                    INNER JOIN consultas c ON t.idconsulta = c.idconsulta
                    WHERE c.fecha BETWEEN :inicio AND :fin
                    GROUP BY c.fecha
                    ORDER BY c.fecha";
$stmtTriajesPorDia = $pdo->prepare($queryTriajesPorDia);
$stmtTriajesPorDia->bindParam(':inicio', $fechaInicio);
$stmtTriajesPorDia->bindParam(':fin', $fechaHoy);
$stmtTriajesPorDia->execute();
$triajesPorDia = $stmtTriajesPorDia->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para el gráfico de triajes por día
$fechasTriaje = [];
$totalesTriaje = [];

// Inicializar el array con los últimos 7 días
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $fechasTriaje[] = date('d/m', strtotime($fecha));
    $totalesTriaje[$fecha] = 0;
}

// Llenar con datos reales
foreach ($triajesPorDia as $triaje) {
    $fechaFormateada = $triaje['fecha'];
    $totalesTriaje[$fechaFormateada] = $triaje['total'];
}

// Convertir a formato JSON para usar en JavaScript
$fechasTriajeJSON = json_encode(array_values($fechasTriaje));
$totalesTriajeJSON = json_encode(array_values($totalesTriaje));
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Panel de Control de Enfermería</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>

    <!-- DASHBOARD PARA ENFERMEROS -->
    <!-- Tarjetas de resumen -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-4 fw-bold"><?= $totalConsultasPendientes ?></h3>
                            <div>Consultas Pendientes Hoy</div>
                        </div>
                        <i class="fas fa-clipboard-list fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= $host ?>/views/Enfermeria/ConsultasPendientes/consultasPendientes.php">Ver Pendientes</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-4 fw-bold"><?= $totalTriajesHoy ?></h3>
                            <div>Triajes Realizados Hoy</div>
                        </div>
                        <i class="fas fa-heartbeat fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= $host ?>/views/Enfermeria/HistorialTriaje/historialTriaje.php">Ver Historial</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-4 fw-bold"><?= $totalTriajesMes ?></h3>
                            <div>Triajes este Mes</div>
                        </div>
                        <i class="fas fa-calendar-alt fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= $host ?>/views/Enfermeria/HistorialTriaje/historialTriaje.php">Ver Estadísticas</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-4 fw-bold"><?= $totalPacientes ?></h3>
                            <div>Pacientes Registrados</div>
                        </div>
                        <i class="fas fa-user-injured fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= $host ?>/views/Paciente/ListarPaciente/listarPaciente.php">Ver Pacientes</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico de Triajes por Día -->
        <div class="col-xl-6">
            <div class="card mb-4 shadow">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-chart-line me-1"></i> Triajes por Día (Últimos 7 días)</span>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" type="button" id="dropdownTriajeBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownTriajeBtn">
                                <li><a class="dropdown-item" href="#" id="refreshTriajeChart"><i class="fas fa-sync-alt me-2"></i>Actualizar</a></li>
                                <li><a class="dropdown-item" href="<?= $host ?>/views/Enfermeria/HistorialTriaje/historialTriaje.php"><i class="fas fa-history me-2"></i>Ver Historial</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="triajesPorDia"></canvas>
                    </div>
                    <?php if (count($triajesPorDia) == 0): ?>
                        <div class="text-center mt-4 text-muted">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p>No hay datos disponibles para los últimos 7 días</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tabla de próximas consultas pendientes -->
        <div class="col-xl-6">
            <div class="card mb-4 shadow">
                <div class="card-header bg-gradient-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-clipboard-list me-1"></i> Próximas Consultas Pendientes de Triaje</span>
                        <a href="<?= $host ?>/views/Enfermeria/Triaje/registrarTriaje.php" class="btn btn-sm btn-light">
                            <i class="fas fa-plus"></i> Nuevo Triaje
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($proximasConsultas) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover border-top-0">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap"><i class="far fa-clock me-2"></i>Hora</th>
                                        <th><i class="fas fa-user me-2"></i>Paciente</th>
                                        <th><i class="fas fa-stethoscope me-2"></i>Especialidad</th>
                                        <th class="text-center"><i class="fas fa-cog me-2"></i>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($proximasConsultas as $consulta): ?>
                                        <tr>
                                            <td class="fw-bold"><?= date('H:i', strtotime($consulta['horaprogramada'])) ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2 bg-primary rounded-circle text-white">
                                                        <?= strtoupper(substr($consulta['nombres'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <?= $consulta['nombre_completo'] ?>
                                                        <small class="d-block text-muted"><?= $consulta['nrodoc'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info rounded-pill">
                                                    <?= $consulta['especialidad'] ?>
                                                </span>
                                                <small class="d-block text-muted">Dr. <?= $consulta['doctor_nombre'] . ' ' . $consulta['doctor_apellido'] ?></small>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= $host ?>/views/Enfermeria/Triaje/registrarTriaje.php?idconsulta=<?= $consulta['idconsulta'] ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-heartbeat me-1"></i> Realizar Triaje
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <img src="<?= $host ?>/assets/img/no-appointments.svg" alt="No hay consultas pendientes" class="img-fluid mb-3" style="max-height: 150px;">
                            <h5 class="text-muted">No hay consultas pendientes de triaje</h5>
                            <p class="text-muted">Todas las consultas programadas para hoy ya tienen triaje registrado.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Mostrando las próximas 5 consultas</small>
                        <a href="<?= $host ?>/views/Enfermeria/ConsultasPendientes/consultasPendientes.php" class="btn btn-sm btn-outline-primary">Ver todas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de últimos triajes -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow">
                <div class="card-header bg-gradient-warning text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-heartbeat me-1"></i> Últimos Triajes Realizados</span>
                        <a href="<?= $host ?>/views/Enfermeria/HistorialTriaje/historialTriaje.php" class="btn btn-sm btn-light">
                            <i class="fas fa-history"></i> Ver Historial
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($ultimosTriajes) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover border-top-0">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap"><i class="far fa-calendar-alt me-2"></i>Fecha y Hora</th>
                                        <th><i class="fas fa-user me-2"></i>Paciente</th>
                                        <th><i class="fas fa-thermometer-half me-2"></i>Temperatura</th>
                                        <th><i class="fas fa-heartbeat me-2"></i>Presión Arterial</th>
                                        <th><i class="fas fa-heart me-2"></i>Frec. Cardíaca</th>
                                        <th><i class="fas fa-lungs me-2"></i>Sat. O2</th>
                                        <th class="text-center"><i class="fas fa-file-medical me-2"></i>Detalles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimosTriajes as $triaje): ?>
                                        <tr>
                                            <td class="text-nowrap fw-bold">
                                                <?= date('d/m/Y', strtotime($triaje['fecha'])) ?> 
                                                <span class="text-muted"><?= date('H:i', strtotime($triaje['hora'])) ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2 bg-warning rounded-circle text-white">
                                                        <?= strtoupper(substr($triaje['nombres'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <?= $triaje['nombres'] . ' ' . $triaje['apellidos'] ?>
                                                        <small class="d-block text-muted"><?= $triaje['nrodoc'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?= $triaje['temperatura'] > 37.5 ? 'bg-danger' : 'bg-success' ?>">
                                                    <?= number_format($triaje['temperatura'], 1) ?> °C
                                                </span>
                                            </td>
                                            <td><?= $triaje['presionarterial'] ?></td>
                                            <td><?= $triaje['frecuenciacardiaca'] ?> lpm</td>
                                            <td><?= $triaje['saturacionoxigeno'] ?> %</td>
                                            <td class="text-center">
                                                <a href="<?= $host ?>/views/Enfermeria/HistorialTriaje/verTriaje.php?id=<?= $triaje['idtriaje'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <img src="<?= $host ?>/assets/img/no-data.svg" alt="No hay triajes" class="img-fluid mb-3" style="max-height: 150px;">
                            <h5 class="text-muted">No hay triajes registrados</h5>
                            <p class="text-muted">Los triajes que realices aparecerán aquí</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Mostrando los últimos 5 triajes realizados</small>
                        <a href="<?= $host ?>/views/Enfermeria/HistorialTriaje/historialTriaje.php" class="btn btn-sm btn-outline-primary">Ver historial completo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para los gráficos -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de triajes por día
        var ctxTriajes = document.getElementById("triajesPorDia");
        if (ctxTriajes) {
            var chartTriajes = new Chart(ctxTriajes, {
                type: "line",
                data: {
                    labels: <?= $fechasTriajeJSON ?>,
                    datasets: [{
                        label: "Triajes",
                        backgroundColor: "rgba(75, 192, 192, 0.2)",
                        borderColor: "rgba(75, 192, 192, 1)",
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        data: <?= $totalesTriajeJSON ?>
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    });
</script>

<?php
// Incluir el footer
include_once "../include/footer.php";
?>