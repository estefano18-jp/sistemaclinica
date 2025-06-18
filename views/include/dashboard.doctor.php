<?php
// Incluir el encabezado del doctor que ya tiene la verificación de sesión
include_once "../include/header.doctor.php";

// Conexión a la base de datos
require_once "../../models/Conexion.php";
$conexion = new Conexion();
$pdo = $conexion->getConexion();

// Fecha actual
$fechaHoy = date('Y-m-d');
$inicioMes = date('Y-m-01');
$finMes = date('Y-m-t');
$idcolaborador = $_SESSION['usuario']['idcolaborador'];

// CONSULTAS PARA EL DASHBOARD DE DOCTORES

// Total de citas para hoy de este doctor
$queryTotalCitasHoy = "
SELECT COUNT(*) as total 
FROM citas c
INNER JOIN consultas con ON c.fecha = con.fecha AND c.hora = con.horaprogramada
INNER JOIN horarios h ON con.idhorario = h.idhorario
INNER JOIN atenciones a ON h.idatencion = a.idatencion
INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
WHERE c.fecha = :fecha 
AND c.estado = 'PROGRAMADA' 
AND ct.idcolaborador = :idcolaborador
";
$stmtCitasHoy = $pdo->prepare($queryTotalCitasHoy);
$stmtCitasHoy->bindParam(':fecha', $fechaHoy);
$stmtCitasHoy->bindParam(':idcolaborador', $idcolaborador);
$stmtCitasHoy->execute();
$totalCitasHoy = $stmtCitasHoy->fetch(PDO::FETCH_ASSOC)['total'];

// Total de pacientes atendidos por este doctor
$queryTotalPacientes = "
SELECT COUNT(DISTINCT con.idpaciente) as total 
FROM consultas con
INNER JOIN horarios h ON con.idhorario = h.idhorario
INNER JOIN atenciones a ON h.idatencion = a.idatencion
INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
WHERE ct.idcolaborador = :idcolaborador
";
$stmtTotalPacientes = $pdo->prepare($queryTotalPacientes);
$stmtTotalPacientes->bindParam(':idcolaborador', $idcolaborador);
$stmtTotalPacientes->execute();
$totalPacientes = $stmtTotalPacientes->fetch(PDO::FETCH_ASSOC)['total'];

// Consultas de este mes para este doctor
$queryConsultasMes = "
SELECT COUNT(*) as total 
FROM consultas con
INNER JOIN horarios h ON con.idhorario = h.idhorario
INNER JOIN atenciones a ON h.idatencion = a.idatencion
INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
WHERE con.fecha BETWEEN :inicio AND :fin 
AND ct.idcolaborador = :idcolaborador
";
$stmtConsultasMes = $pdo->prepare($queryConsultasMes);
$stmtConsultasMes->bindParam(':inicio', $inicioMes);
$stmtConsultasMes->bindParam(':fin', $finMes);
$stmtConsultasMes->bindParam(':idcolaborador', $idcolaborador);
$stmtConsultasMes->execute();
$totalConsultasMes = $stmtConsultasMes->fetch(PDO::FETCH_ASSOC)['total'];

// Próximas citas para hoy
$queryCitasProximas = "
SELECT c.idcita, c.fecha, c.hora, c.estado, c.idpersona,
       p.nombres, p.apellidos 
FROM citas c
INNER JOIN personas p ON c.idpersona = p.idpersona
INNER JOIN consultas con ON c.fecha = con.fecha AND c.hora = con.horaprogramada
INNER JOIN horarios h ON con.idhorario = h.idhorario
INNER JOIN atenciones a ON h.idatencion = a.idatencion
INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
WHERE c.fecha = :fecha 
AND c.estado = 'PROGRAMADA' 
AND ct.idcolaborador = :idcolaborador
ORDER BY c.hora ASC
LIMIT 5
";
$stmtCitasProximas = $pdo->prepare($queryCitasProximas);
$stmtCitasProximas->bindParam(':fecha', $fechaHoy);
$stmtCitasProximas->bindParam(':idcolaborador', $idcolaborador);
$stmtCitasProximas->execute();
$citasProximas = $stmtCitasProximas->fetchAll(PDO::FETCH_ASSOC);

// Últimos diagnósticos realizados por este doctor
$queryUltimosDiagnosticos = "
SELECT con.idconsulta, con.fecha, d.nombre as diagnostico, 
       p.nombres, p.apellidos
FROM consultas con
INNER JOIN diagnosticos d ON con.iddiagnostico = d.iddiagnostico
INNER JOIN pacientes pac ON con.idpaciente = pac.idpaciente
INNER JOIN personas p ON pac.idpersona = p.idpersona
INNER JOIN horarios h ON con.idhorario = h.idhorario
INNER JOIN atenciones a ON h.idatencion = a.idatencion
INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
WHERE ct.idcolaborador = :idcolaborador
ORDER BY con.fecha DESC, con.horaprogramada DESC
LIMIT 5
";
$stmtUltimosDiagnosticos = $pdo->prepare($queryUltimosDiagnosticos);
$stmtUltimosDiagnosticos->bindParam(':idcolaborador', $idcolaborador);
$stmtUltimosDiagnosticos->execute();
$ultimosDiagnosticos = $stmtUltimosDiagnosticos->fetchAll(PDO::FETCH_ASSOC);

// Datos para el gráfico de evolución de consultas del doctor (últimos 7 días)
$fechaInicio = date('Y-m-d', strtotime('-6 days'));
$queryEvolucionConsultas = "
    SELECT fecha, COUNT(*) as total 
    FROM consultas c
    INNER JOIN horarios h ON c.idhorario = h.idhorario
    INNER JOIN atenciones a ON h.idatencion = a.idatencion
    INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
    WHERE fecha BETWEEN :inicio AND :fin 
    AND ct.idcolaborador = :idcolaborador
    GROUP BY fecha 
    ORDER BY fecha
";
$stmtEvolucionConsultas = $pdo->prepare($queryEvolucionConsultas);
$stmtEvolucionConsultas->bindParam(':inicio', $fechaInicio);
$stmtEvolucionConsultas->bindParam(':fin', $fechaHoy);
$stmtEvolucionConsultas->bindParam(':idcolaborador', $idcolaborador);
$stmtEvolucionConsultas->execute();
$evolucionConsultas = $stmtEvolucionConsultas->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para el gráfico de evolución
$fechasEvolucion = [];
$totalesEvolucion = [];

// Inicializar el array con los últimos 7 días
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $fechasEvolucion[] = date('d/m', strtotime($fecha));
    $totalesEvolucion[$fecha] = 0;
}

// Llenar con datos reales
foreach ($evolucionConsultas as $consulta) {
    $fechaFormateada = $consulta['fecha'];
    $totalesEvolucion[$fechaFormateada] = $consulta['total'];
}

// Convertir a formato JSON para usar en JavaScript
$fechasEvolucionJSON = json_encode(array_values($fechasEvolucion));
$totalesEvolucionJSON = json_encode(array_values($totalesEvolucion));
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Panel de Control</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>

    <!-- DASHBOARD PARA DOCTORES -->
    <div class="row">
        <!-- Tarjetas de resumen para doctores -->
        <div class="col-xl-4 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-4 fw-bold"><?= $totalCitasHoy ?></h3>
                            <div>Citas para Hoy</div>
                        </div>
                        <i class="fas fa-calendar-check fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= $host ?>/views/citas/listado.php">Ver Detalles</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-4 fw-bold"><?= $totalPacientes ?></h3>
                            <div>Pacientes Atendidos</div>
                        </div>
                        <i class="fas fa-user-injured fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= $host ?>/views/Paciente/ListarPaciente/listarPaciente.php">Ver Detalles</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-4 fw-bold"><?= $totalConsultasMes ?></h3>
                            <div>Consultas este Mes</div>
                        </div>
                        <i class="fas fa-stethoscope fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= $host ?>/views/consultas/listado.php">Ver Detalles</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Tabla de Citas para Hoy (Doctor) -->
        <div class="col-xl-6">
            <div class="card mb-4 shadow">
                <div class="card-header bg-gradient-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-calendar-day me-1"></i> Mis Citas para Hoy (<?= date('d/m/Y') ?>)</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($citasProximas) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover border-top-0">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap"><i class="far fa-clock me-2"></i>Hora</th>
                                        <th><i class="fas fa-user me-2"></i>Paciente</th>
                                        <th><i class="fas fa-tag me-2"></i>Estado</th>
                                        <th class="text-center"><i class="fas fa-cog me-2"></i>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($citasProximas as $cita): ?>
                                        <tr>
                                            <td class="fw-bold"><?= date('H:i', strtotime($cita['hora'])) ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2 bg-primary rounded-circle text-white">
                                                        <?= strtoupper(substr($cita['nombres'], 0, 1)) ?>
                                                    </div>
                                                    <div><?= $cita['nombres'] . ' ' . $cita['apellidos'] ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary rounded-pill">
                                                    <i class="fas fa-calendar-check me-1"></i> <?= $cita['estado'] ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="<?= $host ?>/views/consultas/registro.php?idcita=<?= $cita['idcita'] ?>&idpersona=<?= $cita['idpersona'] ?>" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Atender">
                                                        <i class="fas fa-notes-medical"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger cancelar-cita" data-id="<?= $cita['idcita'] ?>" data-bs-toggle="tooltip" title="Cancelar">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <img src="<?= $host ?>/assets/img/no-appointments.svg" alt="No hay citas" class="img-fluid mb-3" style="max-height: 150px;">
                            <h5 class="text-muted">No hay citas programadas para hoy</h5>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Mostrando las próximas 5 citas</small>
                        <a href="<?= $host ?>/views/citas/listado.php" class="btn btn-sm btn-outline-primary">Ver todas</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Últimos Diagnósticos (Doctor) -->
        <div class="col-xl-6">
            <div class="card mb-4 shadow">
                <div class="card-header bg-gradient-warning text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-clipboard-list me-1"></i> Mis Últimos Diagnósticos</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($ultimosDiagnosticos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover border-top-0">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap"><i class="far fa-calendar-alt me-2"></i>Fecha</th>
                                        <th><i class="fas fa-user me-2"></i>Paciente</th>
                                        <th><i class="fas fa-stethoscope me-2"></i>Diagnóstico</th>
                                        <th class="text-center"><i class="fas fa-file-medical me-2"></i>Detalles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimosDiagnosticos as $diagnostico): ?>
                                        <tr>
                                            <td class="text-nowrap fw-bold"><?= date('d/m/Y', strtotime($diagnostico['fecha'])) ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2 bg-warning rounded-circle text-white">
                                                        <?= strtoupper(substr($diagnostico['nombres'], 0, 1)) ?>
                                                    </div>
                                                    <div><?= $diagnostico['nombres'] . ' ' . $diagnostico['apellidos'] ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" data-bs-toggle="tooltip" title="<?= $diagnostico['diagnostico'] ?>">
                                                    <?= $diagnostico['diagnostico'] ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= $host ?>/views/consultas/detalle.php?id=<?= $diagnostico['idconsulta'] ?? 0 ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <img src="<?= $host ?>/assets/img/no-diagnostics.svg" alt="No hay diagnósticos" class="img-fluid mb-3" style="max-height: 150px;">
                            <h5 class="text-muted">No hay diagnósticos recientes</h5>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Mostrando los últimos 5 diagnósticos</small>
                        <a href="<?= $host ?>/views/consultas/listado.php" class="btn btn-sm btn-outline-primary">Ver historial completo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de evolución de consultas del doctor -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow">
                <div class="card-header bg-gradient-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-chart-line me-1"></i> Evolución de mis Consultas (Últimos 7 días)</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="evolucionConsultas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para los gráficos -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de evolución de consultas para doctores
        var ctxEvolucion = document.getElementById("evolucionConsultas");
        if (ctxEvolucion) {
            var chartEvolucion = new Chart(ctxEvolucion, {
                type: "line",
                data: {
                    labels: <?= $fechasEvolucionJSON ?>,
                    datasets: [{
                        label: "Mis Consultas",
                        backgroundColor: "rgba(75, 192, 192, 0.2)",
                        borderColor: "rgba(75, 192, 192, 1)",
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        data: <?= $totalesEvolucionJSON ?>
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

        // Funcionalidad para cancelar citas
        const botonesCancelar = document.querySelectorAll('.cancelar-cita');
        botonesCancelar.forEach(boton => {
            boton.addEventListener('click', function() {
                const idCita = this.dataset.id;
                if (confirm('¿Está seguro de cancelar esta cita?')) {
                    // Aquí tu lógica para cancelar la cita
                    console.log('Cancelando cita:', idCita);
                    // Hacer la solicitud AJAX para cancelar
                    // Ejemplo: fetch(`${host}/controllers/cita.controller.php?op=cancelar&idcita=${idCita}`)
                }
            });
        });
    });
</script>

<?php
// Incluir el footer
include_once "../include/footer.php";
?>