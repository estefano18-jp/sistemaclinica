<?php /*RUTA: sistemasclinica/views/include/dashboard.paciente.php*/?>
<?php
// Incluir el encabezado del paciente que ya tiene la verificación de sesión
require_once 'header.paciente.php';

// Conexión a la base de datos
require_once "../../models/Conexion.php";
$conexion = new Conexion();
$pdo = $conexion->getConexion();

// Fecha actual
$fechaHoy = date('Y-m-d');
$inicioMes = date('Y-m-01');
$finMes = date('Y-m-t');

// Obtener ID del paciente desde la sesión
$idPaciente = $_SESSION['usuario']['idpaciente'] ?? 0;

// CONSULTAS PARA EL DASHBOARD DE PACIENTES

// Total de citas programadas pendientes del paciente
$queryTotalCitasPendientes = "SELECT COUNT(*) as total FROM citas WHERE idpersona = (SELECT idpersona FROM pacientes WHERE idpaciente = :idpaciente) AND estado = 'PROGRAMADA'";
$stmtCitasPendientes = $pdo->prepare($queryTotalCitasPendientes);
$stmtCitasPendientes->bindParam(':idpaciente', $idPaciente);
$stmtCitasPendientes->execute();
$totalCitasPendientes = $stmtCitasPendientes->fetch(PDO::FETCH_ASSOC)['total'];

// Total de citas realizadas del paciente
$queryTotalCitasRealizadas = "SELECT COUNT(*) as total FROM citas WHERE idpersona = (SELECT idpersona FROM pacientes WHERE idpaciente = :idpaciente) AND estado = 'REALIZADA'";
$stmtCitasRealizadas = $pdo->prepare($queryTotalCitasRealizadas);
$stmtCitasRealizadas->bindParam(':idpaciente', $idPaciente);
$stmtCitasRealizadas->execute();
$totalCitasRealizadas = $stmtCitasRealizadas->fetch(PDO::FETCH_ASSOC)['total'];

// Total de consultas del paciente
$queryTotalConsultas = "SELECT COUNT(*) as total FROM consultas WHERE idpaciente = :idpaciente";
$stmtTotalConsultas = $pdo->prepare($queryTotalConsultas);
$stmtTotalConsultas->bindParam(':idpaciente', $idPaciente);
$stmtTotalConsultas->execute();
$totalConsultas = $stmtTotalConsultas->fetch(PDO::FETCH_ASSOC)['total'];

// Total de servicios realizados al paciente
$queryServiciosPaciente = "SELECT COUNT(*) as total 
                          FROM serviciosrequeridos sr
                          INNER JOIN consultas c ON sr.idconsulta = c.idconsulta
                          WHERE c.idpaciente = :idpaciente";
$stmtServiciosPaciente = $pdo->prepare($queryServiciosPaciente);
$stmtServiciosPaciente->bindParam(':idpaciente', $idPaciente);
$stmtServiciosPaciente->execute();
$totalServiciosPaciente = $stmtServiciosPaciente->fetch(PDO::FETCH_ASSOC)['total'];

// Obtener las próximas citas del paciente - CONSULTA CORREGIDA
$queryCitasProximas = "SELECT c.fecha, c.hora, c.estado, c.idcita
                      FROM citas c
                      INNER JOIN personas pr ON c.idpersona = pr.idpersona
                      INNER JOIN pacientes pa ON pr.idpersona = pa.idpersona
                      WHERE pa.idpaciente = :idpaciente AND c.estado = 'PROGRAMADA'
                      ORDER BY c.fecha ASC, c.hora ASC
                      LIMIT 5";
$stmtCitasProximas = $pdo->prepare($queryCitasProximas);
$stmtCitasProximas->bindParam(':idpaciente', $idPaciente);
$stmtCitasProximas->execute();
$citasProximas = $stmtCitasProximas->fetchAll(PDO::FETCH_ASSOC);

// Obtener los últimos diagnósticos del paciente
$queryUltimosDiagnosticos = "SELECT d.nombre as diagnostico, c.fecha, c.idconsulta,
                           CONCAT(p.nombres, ' ', p.apellidos) as doctor, e.especialidad
                           FROM consultas c
                           INNER JOIN diagnosticos d ON c.iddiagnostico = d.iddiagnostico
                           INNER JOIN horarios h ON c.idhorario = h.idhorario
                           INNER JOIN atenciones a ON h.idatencion = a.idatencion
                           INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
                           INNER JOIN colaboradores cl ON ct.idcolaborador = cl.idcolaborador
                           INNER JOIN personas p ON cl.idpersona = p.idpersona
                           INNER JOIN especialidades e ON cl.idespecialidad = e.idespecialidad
                           WHERE c.idpaciente = :idpaciente
                           ORDER BY c.fecha DESC, c.horaatencion DESC
                           LIMIT 5";
$stmtUltimosDiagnosticos = $pdo->prepare($queryUltimosDiagnosticos);
$stmtUltimosDiagnosticos->bindParam(':idpaciente', $idPaciente);
$stmtUltimosDiagnosticos->execute();
$ultimosDiagnosticos = $stmtUltimosDiagnosticos->fetchAll(PDO::FETCH_ASSOC);

// Obtener los datos para el gráfico de consultas por especialidad del paciente
$queryConsultasPorEspecialidad = "SELECT e.especialidad, COUNT(*) as total
                                FROM consultas c
                                INNER JOIN horarios h ON c.idhorario = h.idhorario
                                INNER JOIN atenciones a ON h.idatencion = a.idatencion
                                INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
                                INNER JOIN colaboradores cl ON ct.idcolaborador = cl.idcolaborador
                                INNER JOIN especialidades e ON cl.idespecialidad = e.idespecialidad
                                WHERE c.idpaciente = :idpaciente
                                GROUP BY e.especialidad";
$stmtConsultasPorEspecialidad = $pdo->prepare($queryConsultasPorEspecialidad);
$stmtConsultasPorEspecialidad->bindParam(':idpaciente', $idPaciente);
$stmtConsultasPorEspecialidad->execute();
$consultasPorEspecialidad = $stmtConsultasPorEspecialidad->fetchAll(PDO::FETCH_ASSOC);

// Obtener datos para el gráfico de evolución de consultas del paciente (últimos 6 meses)
$fechaInicio = date('Y-m-d', strtotime('-6 months'));
$queryEvolucionConsultas = "SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, COUNT(*) as total 
                           FROM consultas 
                           WHERE idpaciente = :idpaciente 
                           AND fecha BETWEEN :inicio AND :fin 
                           GROUP BY DATE_FORMAT(fecha, '%Y-%m') 
                           ORDER BY DATE_FORMAT(fecha, '%Y-%m')";
$stmtEvolucionConsultas = $pdo->prepare($queryEvolucionConsultas);
$stmtEvolucionConsultas->bindParam(':idpaciente', $idPaciente);
$stmtEvolucionConsultas->bindParam(':inicio', $fechaInicio);
$stmtEvolucionConsultas->bindParam(':fin', $fechaHoy);
$stmtEvolucionConsultas->execute();
$evolucionConsultas = $stmtEvolucionConsultas->fetchAll(PDO::FETCH_ASSOC);

// Formatear los datos para los gráficos
$especialidades = [];
$totalConsultas = [];

foreach ($consultasPorEspecialidad as $consulta) {
    $especialidades[] = $consulta['especialidad'];
    $totalConsultas[] = $consulta['total'];
}

// Preparar datos para el gráfico de evolución
$fechasEvolucion = [];
$totalesEvolucion = [];

// Últimos 6 meses
for ($i = 5; $i >= 0; $i--) {
    $mes = date('Y-m', strtotime("-$i months"));
    $etiquetaMes = date('M Y', strtotime("-$i months")); // Formato abreviado del mes y año
    $fechasEvolucion[] = $etiquetaMes;
    $totalesEvolucion[$mes] = 0;
}

// Llenar con datos reales
foreach ($evolucionConsultas as $consulta) {
    $mes = $consulta['mes'];
    if (isset($totalesEvolucion[$mes])) {
        $totalesEvolucion[$mes] = intval($consulta['total']);
    }
}

// Convertir a formato JSON para usar en JavaScript
$especialidadesJSON = json_encode($especialidades);
$totalConsultasJSON = json_encode(array_values($totalConsultas));
$fechasEvolucionJSON = json_encode(array_values($fechasEvolucion));
$totalesEvolucionJSON = json_encode(array_values($totalesEvolucion));
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Mi Panel de Control</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>

    <!-- DASHBOARD PARA PACIENTES -->
    <!-- Tarjetas de resumen -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-4 fw-bold"><?= $totalCitasPendientes ?></h3>
                            <div>Citas Pendientes</div>
                        </div>
                        <i class="fas fa-calendar-check fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= $host ?>/views/citas/misCitas.php">Ver Detalles</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-4 fw-bold"><?= $totalCitasRealizadas ?></h3>
                            <div>Citas Realizadas</div>
                        </div>
                        <i class="fas fa-clipboard-check fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= $host ?>/views/citas/historialCitas.php">Ver Historial</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-4 fw-bold"><?= $totalConsultas ?></h3>
                            <div>Consultas Médicas</div>
                        </div>
                        <i class="fas fa-stethoscope fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= $host ?>/views/paciente/historiaClinica.php">Ver Historia Clínica</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="display-4 fw-bold"><?= $totalServiciosPaciente ?></h3>
                            <div>Servicios Realizados</div>
                        </div>
                        <i class="fas fa-flask fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="<?= $host ?>/views/paciente/misResultados.php">Ver Resultados</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos y Tablas - Sección Mejorada -->
    <div class="row">
        <!-- Gráfico de Consultas por Especialidad -->
        <div class="col-xl-6">
            <div class="card mb-4 shadow">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-chart-bar me-1"></i> Mis Consultas por Especialidad</span>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" type="button" id="dropdownEspecialidadBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownEspecialidadBtn">
                                <li><a class="dropdown-item" href="#" id="downloadEspPDF"><i class="fas fa-file-pdf me-2"></i>Exportar PDF</a></li>
                                <li><a class="dropdown-item" href="#" id="refreshEspChart"><i class="fas fa-sync-alt me-2"></i>Actualizar</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="consultasPorEspecialidad"></canvas>
                    </div>
                    <?php if (count($consultasPorEspecialidad) == 0): ?>
                        <div class="text-center mt-4 text-muted">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p>No hay datos disponibles de consultas médicas</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Gráfico de Evolución de Consultas -->
        <div class="col-xl-6">
            <div class="card mb-4 shadow">
                <div class="card-header bg-gradient-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-chart-line me-1"></i> Evolución de Mis Consultas (Últimos 6 Meses)</span>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" type="button" id="dropdownEvolucionBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownEvolucionBtn">
                                <li><a class="dropdown-item" href="#" id="downloadEvoPDF"><i class="fas fa-file-pdf me-2"></i>Exportar PDF</a></li>
                                <li><a class="dropdown-item" href="#" id="refreshEvoChart"><i class="fas fa-sync-alt me-2"></i>Actualizar</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="evolucionConsultas"></canvas>
                    </div>
                    <?php if (count($evolucionConsultas) == 0): ?>
                        <div class="text-center mt-4 text-muted">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p>No hay datos disponibles para los últimos 6 meses</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Tabla de Próximas Citas -->
        <div class="col-xl-6">
            <div class="card mb-4 shadow">
                <div class="card-header bg-gradient-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-calendar-day me-1"></i> Mis Próximas Citas</span>
                        <a href="<?= $host ?>/views/Citas/ProgramarCita/programarCita.php" class="btn btn-sm btn-light">
                            <i class="fas fa-plus"></i> Nueva Cita
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($citasProximas) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover border-top-0">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap"><i class="far fa-calendar-alt me-2"></i>Fecha</th>
                                        <th class="text-nowrap"><i class="far fa-clock me-2"></i>Hora</th>
                                        <th><i class="fas fa-tag me-2"></i>Estado</th>
                                        <th class="text-center"><i class="fas fa-cog me-2"></i>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($citasProximas as $cita): ?>
                                        <tr>
                                            <td class="fw-bold"><?= date('d/m/Y', strtotime($cita['fecha'])) ?></td>
                                            <td><?= date('H:i', strtotime($cita['hora'])) ?></td>
                                            <td>
                                                <span class="badge bg-primary rounded-pill">
                                                    <i class="fas fa-calendar-check me-1"></i> <?= $cita['estado'] ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="<?= $host ?>/views/citas/detalle.php?id=<?= $cita['idcita'] ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Ver Detalles">
                                                        <i class="fas fa-eye"></i>
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
                            <h5 class="text-muted">No tienes citas programadas</h5>
                            <p class="text-muted">Puedes agendar una cita médica haciendo clic en el botón superior</p>
                            <a href="<?= $host ?>/views/Citas/ProgramarCita/programarCita.php" class="btn btn-primary mt-3">
                                <i class="fas fa-plus-circle me-2"></i>Agendar Nueva Cita
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Mostrando tus próximas citas</small>
                        <a href="<?= $host ?>/views/citas/misCitas.php" class="btn btn-sm btn-outline-primary">Ver todas</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Últimos Diagnósticos -->
        <div class="col-xl-6">
            <div class="card mb-4 shadow">
                <div class="card-header bg-gradient-warning text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-clipboard-list me-1"></i> Mis Últimos Diagnósticos</span>
                        <a href="<?= $host ?>/views/paciente/historiaClinica.php" class="btn btn-sm btn-light">
                            <i class="fas fa-file-medical"></i> Historia Clínica
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($ultimosDiagnosticos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover border-top-0">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap"><i class="far fa-calendar-alt me-2"></i>Fecha</th>
                                        <th><i class="fas fa-user-md me-2"></i>Doctor</th>
                                        <th><i class="fas fa-stethoscope me-2"></i>Especialidad</th>
                                        <th><i class="fas fa-heartbeat me-2"></i>Diagnóstico</th>
                                        <th class="text-center"><i class="fas fa-file-medical me-2"></i>Detalles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimosDiagnosticos as $diagnostico): ?>
                                        <tr>
                                            <td class="text-nowrap fw-bold"><?= date('d/m/Y', strtotime($diagnostico['fecha'])) ?></td>
                                            <td><?= $diagnostico['doctor'] ?></td>
                                            <td><?= $diagnostico['especialidad'] ?></td>
                                            <td>
                                                <span class="text-truncate d-inline-block" style="max-width: 150px;" data-bs-toggle="tooltip" title="<?= $diagnostico['diagnostico'] ?>">
                                                    <?= $diagnostico['diagnostico'] ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= $host ?>/views/paciente/verConsulta.php?id=<?= $diagnostico['idconsulta'] ?>" class="btn btn-sm btn-outline-primary">
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
                            <h5 class="text-muted">No tienes diagnósticos registrados</h5>
                            <p class="text-muted">Los diagnósticos aparecerán aquí después de tus consultas médicas</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Mostrando tus últimos diagnósticos</small>
                        <a href="<?= $host ?>/views/paciente/historiaClinica.php" class="btn btn-sm btn-outline-primary">Ver historial completo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para los gráficos -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráficos para pacientes
        var ctxEspecialidades = document.getElementById("consultasPorEspecialidad");
        if (ctxEspecialidades) {
            var chartEspecialidades = new Chart(ctxEspecialidades, {
                type: "bar",
                data: {
                    labels: <?= $especialidadesJSON ?>,
                    datasets: [{
                        label: "Consultas",
                        backgroundColor: "rgba(54, 162, 235, 0.7)",
                        borderColor: "rgba(54, 162, 235, 1)",
                        borderWidth: 1,
                        data: <?= $totalConsultasJSON ?>
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

        var ctxEvolucion = document.getElementById("evolucionConsultas");
        if (ctxEvolucion) {
            var chartEvolucion = new Chart(ctxEvolucion, {
                type: "line",
                data: {
                    labels: <?= $fechasEvolucionJSON ?>,
                    datasets: [{
                        label: "Consultas",
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
                if (confirm('¿Estás seguro de que deseas cancelar esta cita?')) {
                    // Aquí tu lógica para cancelar la cita
                    console.log('Cancelando cita:', idCita);
                    // Hacer la solicitud AJAX para cancelar
                    fetch(`${host}/controllers/cita.controller.php?operacion=cancelar&idcita=${idCita}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            alert('Cita cancelada correctamente');
                            window.location.reload();
                        } else {
                            alert('Error al cancelar la cita: ' + data.mensaje);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al procesar la solicitud');
                    });
                }
            });
        });
    });
</script>

<?php
// Incluir el footer
require_once "footer.php";
?>