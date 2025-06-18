<?php
// Incluir el encabezado con verificación de sesión
include_once "../include/header.administrador.php";

// Conexión a la base de datos
require_once "../../models/Conexion.php";
$conexion = new Conexion();
$pdo = $conexion->getConexion();

// Configuración de fechas (producción: usar date('Y-m-d'))
$fechaHoy = '2025-06-13'; // Cambiar a date('Y-m-d') en producción
$inicioMes = '2025-06-01';
$finMes = '2025-06-30';

// Consultas optimizadas para el dashboard
// Citas programadas hoy
$stmtCitasHoy = $pdo->prepare("SELECT COUNT(*) as total FROM citas WHERE fecha = :fecha AND estado = 'PROGRAMADA'");
$stmtCitasHoy->bindParam(':fecha', $fechaHoy);
$stmtCitasHoy->execute();
$totalCitasHoy = $stmtCitasHoy->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Total de pacientes
$stmtTotalPacientes = $pdo->query("SELECT COUNT(*) as total FROM pacientes");
$totalPacientes = $stmtTotalPacientes->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Total de doctores activos
$stmtTotalDoctores = $pdo->query("SELECT COUNT(*) as total FROM colaboradores c 
    INNER JOIN personas p ON c.idpersona = p.idpersona 
    INNER JOIN especialidades e ON c.idespecialidad = e.idespecialidad 
    WHERE e.estado = 'ACTIVO'");
$totalDoctores = $stmtTotalDoctores->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Total de enfermeros
$stmtTotalEnfermeros = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'ENFERMERO'");
$totalEnfermeros = $stmtTotalEnfermeros->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Próximas citas de hoy
$stmtCitasProximas = $pdo->prepare("SELECT c.idcita, c.fecha, c.hora, p.nombres, p.apellidos, c.estado, c.idpersona 
    FROM citas c INNER JOIN personas p ON c.idpersona = p.idpersona 
    WHERE c.fecha = :fecha AND c.estado = 'PROGRAMADA' 
    ORDER BY c.hora ASC LIMIT 5");
$stmtCitasProximas->bindParam(':fecha', $fechaHoy);
$stmtCitasProximas->execute();
$citasProximas = $stmtCitasProximas->fetchAll(PDO::FETCH_ASSOC) ?: [
    ['idcita' => 1, 'fecha' => '2025-06-13', 'hora' => '10:00:00', 'nombres' => 'Juan', 'apellidos' => 'Pérez', 'estado' => 'PROGRAMADA', 'idpersona' => 1],
    ['idcita' => 2, 'fecha' => '2025-06-13', 'hora' => '11:30:00', 'nombres' => 'María', 'apellidos' => 'Gómez', 'estado' => 'PROGRAMADA', 'idpersona' => 2]
];

// Últimos diagnósticos
$stmtUltimosDiagnosticos = $pdo->query("SELECT d.nombre as diagnostico, p.nombres, p.apellidos, c.fecha 
    FROM consultas c INNER JOIN diagnosticos d ON c.iddiagnostico = d.iddiagnostico 
    INNER JOIN personas p ON c.idpaciente = p.idpersona 
    ORDER BY c.fecha DESC, c.horaatencion DESC LIMIT 5");
$ultimosDiagnosticos = $stmtUltimosDiagnosticos->fetchAll(PDO::FETCH_ASSOC) ?: [
    ['diagnostico' => 'Gripe', 'nombres' => 'Ana', 'apellidos' => 'Martínez', 'fecha' => '2025-06-12'],
    ['diagnostico' => 'Hipertensión', 'nombres' => 'Carlos', 'apellidos' => 'López', 'fecha' => '2025-06-11']
];

// Consultas por especialidad (junio 2025)
$stmtConsultasPorEspecialidad = $pdo->prepare("SELECT e.especialidad, COUNT(*) as total 
    FROM consultas c INNER JOIN horarios h ON c.idhorario = h.idhorario 
    INNER JOIN atenciones a ON h.idatencion = a.idatencion 
    INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato 
    INNER JOIN colaboradores cl ON ct.idcolaborador = cl.idcolaborador 
    INNER JOIN especialidades e ON cl.idespecialidad = e.idespecialidad 
    WHERE c.fecha BETWEEN :inicio AND :fin 
    GROUP BY e.especialidad");
$stmtConsultasPorEspecialidad->bindParam(':inicio', $inicioMes);
$stmtConsultasPorEspecialidad->bindParam(':fin', $finMes);
$stmtConsultasPorEspecialidad->execute();
$consultasPorEspecialidad = $stmtConsultasPorEspecialidad->fetchAll(PDO::FETCH_ASSOC) ?: [
    ['especialidad' => 'Cardiología', 'total' => 15],
    ['especialidad' => 'Pediatría', 'total' => 20],
    ['especialidad' => 'Dermatología', 'total' => 10]
];

// Evolución de consultas (últimos 7 días)
$fechaInicio = '2025-06-07';
$stmtEvolucionConsultas = $pdo->prepare("SELECT fecha, COUNT(*) as total 
    FROM consultas WHERE fecha BETWEEN :inicio AND :fin 
    GROUP BY fecha ORDER BY fecha");
$stmtEvolucionConsultas->bindParam(':inicio', $fechaInicio);
$stmtEvolucionConsultas->bindParam(':fin', $fechaHoy);
$stmtEvolucionConsultas->execute();
$evolucionConsultas = $stmtEvolucionConsultas->fetchAll(PDO::FETCH_ASSOC) ?: [
    ['fecha' => '2025-06-07', 'total' => 5], ['fecha' => '2025-06-08', 'total' => 8], 
    ['fecha' => '2025-06-09', 'total' => 10], ['fecha' => '2025-06-10', 'total' => 7],
    ['fecha' => '2025-06-11', 'total' => 12], ['fecha' => '2025-06-12', 'total' => 9],
    ['fecha' => '2025-06-13', 'total' => 11]
];

// Datos para gráficos
$especialidades = array_column($consultasPorEspecialidad, 'especialidad');
$totalConsultas = array_column($consultasPorEspecialidad, 'total');
$fechasEvolucion = [];
$totalesEvolucion = array_fill_keys(range(0, 6), 0);
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("$fechaHoy -$i days"));
    $fechasEvolucion[] = date('d/m', strtotime($fecha));
    foreach ($evolucionConsultas as $consulta) {
        if ($consulta['fecha'] === $fecha) $totalesEvolucion[6 - $i] = $consulta['total'];
    }
}

// JSON para gráficos
$especialidadesJSON = json_encode($especialidades);
$totalConsultasJSON = json_encode($totalConsultas);
$fechasEvolucionJSON = json_encode($fechasEvolucion);
$totalesEvolucionJSON = json_encode(array_values($totalesEvolucion));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - Sistema Clínico</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(120deg, #1e3c72 0%, #2a5298 100%);
            font-family: 'Poppins', sans-serif;
            color: #333;
            overflow-x: hidden;
            animation: gradientBG 15s ease infinite;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .container-fluid {
            padding: 40px;
        }
        .card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-10px) rotateX(5deg);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }
        .card-header {
            border-radius: 20px 20px 0 0;
            padding: 20px;
            font-weight: 600;
        }
        .chart-container {
            height: 400px;
            padding: 20px;
        }
        .table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(45deg, #ff6b6b, #ff8e53);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
        }
        .notification-bell {
            position: relative;
        }
        .notification-bell .badge {
            position: absolute;
            top: -5px;
            right: -5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="display-4 fw-bold text-white">Panel de Control Clínico</h1>
        </div>

        <!-- Resumen en tarjetas -->
        <div class="row g-4 mb-5">
            <?php foreach ([
                ['title' => 'Citas Hoy', 'value' => $totalCitasHoy, 'icon' => 'fa-calendar-check', 'color' => 'primary', 'link' => '/views/Citas/GestionarCita/gestionarCita.php'],
                ['title' => 'Pacientes', 'value' => $totalPacientes, 'icon' => 'fa-user-injured', 'color' => 'success', 'link' => '/views/Paciente/ListarPaciente/listarPaciente.php'],
                ['title' => 'Doctores', 'value' => $totalDoctores, 'icon' => 'fa-user-md', 'color' => 'warning', 'link' => '/views/Doctor/ListarDoctor/listarDoctor.php'],
                ['title' => 'Enfermeros', 'value' => $totalEnfermeros, 'icon' => 'fa-user-nurse', 'color' => 'danger', 'link' => '/views/Enfermeria/ListarEnfermero/listarEnfermero.php']
            ] as $card): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card bg-<?= $card['color'] ?> text-white">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas <?= $card['icon'] ?> fa-3x me-3 opacity-75"></i>
                            <div>
                                <h2 class="display-5 fw-bold mb-0"><?= $card['value'] ?></h2>
                                <p class="mb-0"><?= $card['title'] ?></p>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-end">
                            <a href="<?= $host . $card['link'] ?>" class="text-white small">Detalles <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Gráficos avanzados -->
        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span>Consultas por Especialidad</span>
                        <select class="form-select w-25" id="filterEspecialidad">
                            <option value="mes">Mes Actual</option>
                            <option value="semana">Semana Actual</option>
                            <option value="año">Año Actual</option>
                        </select>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="consultasPorEspecialidad"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-info text-white">Evolución de Consultas</div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="evolucionConsultas"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tablas interactivas -->
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <span>Próximas Citas (<?= date('d/m/Y', strtotime($fechaHoy)) ?>)</span>
                        <a href="<?= $host ?>/views/citas/nueva.php" class="btn btn-light btn-sm"><i class="fas fa-plus"></i> Nueva</a>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Paciente</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($citasProximas as $cita): ?>
                                    <tr>
                                        <td><?= date('H:i', strtotime($cita['hora'])) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-2"><?= strtoupper(substr($cita['nombres'], 0, 1)) ?></div>
                                                <?= $cita['nombres'] . ' ' . $cita['apellidos'] ?>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-success"><?= $cita['estado'] ?></span></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?= $host ?>/views/consultas/registro.php?idcita=<?= $cita['idcita'] ?>&idpersona=<?= $cita['idpersona'] ?>" class="btn btn-sm btn-success"><i class="fas fa-notes-medical"></i></a>
                                                <a href="<?= $host ?>/views/citas/editar.php?id=<?= $cita['idcita'] ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                                <button class="btn btn-sm btn-danger cancelar-cita" data-id="<?= $cita['idcita'] ?>"><i class="fas fa-times"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-warning text-white">Últimos Diagnósticos</div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Paciente</th>
                                    <th>Diagnóstico</th>
                                    <th>Detalles</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimosDiagnosticos as $diagnostico): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($diagnostico['fecha'])) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-2"><?= strtoupper(substr($diagnostico['nombres'], 0, 1)) ?></div>
                                                <?= $diagnostico['nombres'] . ' ' . $diagnostico['apellidos'] ?>
                                            </div>
                                        </td>
                                        <td><?= $diagnostico['diagnostico'] ?></td>
                                        <td><a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Gráfico de especialidades
            new Chart(document.getElementById('consultasPorEspecialidad'), {
                type: 'bar',
                data: {
                    labels: <?= $especialidadesJSON ?>,
                    datasets: [{
                        label: 'Consultas',
                        data: <?= $totalConsultasJSON ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderRadius: 10,
                        barThickness: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });

            // Gráfico de evolución
            new Chart(document.getElementById('evolucionConsultas'), {
                type: 'line',
                data: {
                    labels: <?= $fechasEvolucionJSON ?>,
                    datasets: [{
                        label: 'Consultas',
                        data: <?= $totalesEvolucionJSON ?>,
                        borderColor: '#00c4cc',
                        backgroundColor: 'rgba(0, 196, 204, 0.2)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                }
            });

            // Cancelar cita
            document.querySelectorAll('.cancelar-cita').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (confirm('¿Cancelar esta cita?')) {
                        console.log('Cita cancelada: ' + btn.dataset.id);
                        // Agregar lógica AJAX aquí
                    }
                });
            });
        });
    </script>
</body>
</html>
