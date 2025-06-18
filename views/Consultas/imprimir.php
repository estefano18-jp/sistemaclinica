<?php
// Verificar que se recibió el ID de consulta
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<div class="alert alert-danger">ID de consulta no proporcionado</div>';
    exit;
}

// Incluir modelo de consulta
require_once '../../models/Consulta.php';

// Obtener datos de la consulta
$idconsulta = intval($_GET['id']);
$modelo = new Consulta();
$consulta = $modelo->obtenerConsultaPorId($idconsulta);

// Verificar si se encontró la consulta
if (!$consulta) {
    echo '<div class="alert alert-danger">Consulta no encontrada</div>';
    exit;
}

// Formatear fecha y hora
$fechaFormateada = date('d/m/Y', strtotime($consulta['fecha']));
$horaFormateada = $consulta['horaprogramada'] ? date('H:i', strtotime($consulta['horaprogramada'])) : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta Médica #<?= $idconsulta ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #fff;
            margin: 0;
            padding: 20px;
            font-size: 12pt;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .header img {
            max-width: 200px;
            height: auto;
        }
        
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .header p {
            font-size: 10pt;
            margin: 5px 0;
        }
        
        .consulta-info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .consulta-info p {
            margin: 5px 0;
        }
        
        .section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        
        .diagnostico {
            padding: 10px;
            background-color: #e9f7ef;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .receta {
            padding: 10px;
            background-color: #eaf2f8;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .triaje {
            padding: 10px;
            background-color: #fef9e7;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .table th {
            background-color: #f8f9fa;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9pt;
            color: #777;
        }
        
        @media print {
            body {
                padding: 0;
                font-size: 10pt;
            }
            
            .no-print {
                display: none !important;
            }
            
            .consulta-info, .diagnostico, .receta, .triaje {
                break-inside: avoid;
            }
            
            a {
                text-decoration: none;
                color: #333;
            }
            
            .table {
                font-size: 9pt;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Botón de impresión (sólo visible en pantalla) -->
        <div class="row mb-3 no-print">
            <div class="col-12 text-end">
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Imprimir
                </button>
                <button type="button" class="btn btn-secondary" onclick="window.close()">
                    <i class="fas fa-times me-2"></i>Cerrar
                </button>
            </div>
        </div>
        
        <!-- Encabezado -->
        <div class="header">
            <img src="../../../img/logo.jpg" alt="Logo Clínica">
            <h1>CONSULTA MÉDICA</h1>
            <p>Av. Principal 123, Chincha Alta, Ica, Perú</p>
            <p>Teléfono: (056) 123456 | Email: info@clinica.com</p>
        </div>
        
        <!-- Información General -->
        <div class="consulta-info">
            <div class="row">
                <div class="col-6">
                    <p><strong>Consulta Nº:</strong> <?= $idconsulta ?></p>
                    <p><strong>Fecha:</strong> <?= $fechaFormateada ?></p>
                    <p><strong>Hora:</strong> <?= $horaFormateada ?></p>
                </div>
                <div class="col-6">
                    <p><strong>Especialidad:</strong> <?= $consulta['especialidad'] ?></p>
                    <p><strong>Doctor:</strong> <?= $consulta['doctor_nombres'] . ' ' . $consulta['doctor_apellidos'] ?></p>
                </div>
            </div>
        </div>
        
        <!-- Datos del Paciente -->
        <div class="section">
            <h2 class="section-title">DATOS DEL PACIENTE</h2>
            <div class="row">
                <div class="col-6">
                    <p><strong>Nombre:</strong> <?= $consulta['paciente_nombres'] . ' ' . $consulta['paciente_apellidos'] ?></p>
                    <p><strong>Documento:</strong> <?= $consulta['paciente_tipodoc'] . ' - ' . $consulta['paciente_nrodoc'] ?></p>
                </div>
                <div class="col-6">
                    <p><strong>Edad:</strong> <?= $consulta['edad'] ?? 'No registrada' ?> años</p>
                    <p><strong>Género:</strong> <?= $consulta['genero'] === 'M' ? 'Masculino' : ($consulta['genero'] === 'F' ? 'Femenino' : 'Otro') ?></p>
                </div>
            </div>
        </div>
        
        <!-- Triaje si existe -->
        <?php if ($consulta['triaje']): ?>
        <div class="section">
            <h2 class="section-title">TRIAJE</h2>
            <div class="triaje">
                <div class="row">
                    <div class="col-4">
                        <p><strong>Temperatura:</strong> <?= $consulta['triaje']['temperatura'] ?? 'No registrada' ?> °C</p>
                        <p><strong>Presión Arterial:</strong> <?= $consulta['triaje']['presionarterial'] ?? 'No registrada' ?></p>
                    </div>
                    <div class="col-4">
                        <p><strong>Frecuencia Cardíaca:</strong> <?= $consulta['triaje']['frecuenciacardiaca'] ?? 'No registrada' ?> lpm</p>
                        <p><strong>Saturación O2:</strong> <?= $consulta['triaje']['saturacionoxigeno'] ?? 'No registrada' ?> %</p>
                    </div>
                    <div class="col-4">
                        <p><strong>Peso:</strong> <?= $consulta['triaje']['peso'] ?? 'No registrado' ?> kg</p>
                        <p><strong>Estatura:</strong> <?= $consulta['triaje']['estatura'] ?? 'No registrada' ?> cm</p>
                        <?php if (isset($consulta['triaje']['imc'])): ?>
                        <p><strong>IMC:</strong> <?= $consulta['triaje']['imc'] ?> (<?= $consulta['triaje']['clasificacion_imc'] ?>)</p>
                        <?php endif; ?>
                    </div>
                </div>
                <p><strong>Realizado por:</strong> <?= $consulta['triaje']['enfermera_nombre'] ?? 'No registrado' ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Diagnóstico si existe -->
        <?php if ($consulta['iddiagnostico']): ?>
        <div class="section">
            <h2 class="section-title">DIAGNÓSTICO</h2>
            <div class="diagnostico">
                <p><strong><?= $consulta['diagnostico'] ?></strong> (<?= $consulta['diagnostico_codigo'] ?>)</p>
                <?php if ($consulta['diagnostico_descripcion']): ?>
                <p><?= $consulta['diagnostico_descripcion'] ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Receta si existe -->
        <?php if ($consulta['receta']): ?>
        <div class="section">
            <h2 class="section-title">RECETA MÉDICA</h2>
            <div class="receta">
                <p><strong>Medicación:</strong> <?= $consulta['receta']['medicacion'] ?></p>
                <p><strong>Cantidad:</strong> <?= $consulta['receta']['cantidad'] ?></p>
                <p><strong>Frecuencia:</strong> <?= $consulta['receta']['frecuencia'] ?></p>
                
                <?php if ($consulta['tratamiento']): ?>
                <div class="mt-3">
                    <h5>Tratamiento</h5>
                    <p><strong>Medicación:</strong> <?= $consulta['tratamiento']['medicacion'] ?></p>
                    <p><strong>Dosis:</strong> <?= $consulta['tratamiento']['dosis'] ?></p>
                    <p><strong>Frecuencia:</strong> <?= $consulta['tratamiento']['frecuencia'] ?></p>
                    <p><strong>Duración:</strong> <?= $consulta['tratamiento']['duracion'] ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Servicios solicitados si existen -->
        <?php if (!empty($consulta['servicios'])): ?>
        <div class="section">
            <h2 class="section-title">SERVICIOS SOLICITADOS</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Servicio</th>
                        <th>Solicitud</th>
                        <th>Fecha Entrega</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($consulta['servicios'] as $servicio): ?>
                    <tr>
                        <td><?= $servicio['tiposervicio'] ?></td>
                        <td><?= $servicio['servicio'] ?></td>
                        <td><?= $servicio['solicitud'] ?? 'No especificada' ?></td>
                        <td><?= $servicio['fechaentrega'] ? date('d/m/Y', strtotime($servicio['fechaentrega'])) : 'No especificada' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Espacio para firma -->
        <div class="section mt-5">
            <div class="row">
                <div class="col-6 text-center">
                    <div style="border-top: 1px solid #333; display: inline-block; width: 200px; margin-top: 50px;">
                        <p>Firma del Médico</p>
                    </div>
                </div>
                <div class="col-6 text-center">
                    <div style="border-top: 1px solid #333; display: inline-block; width: 200px; margin-top: 50px;">
                        <p>Firma del Paciente</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pie de página -->
        <div class="footer">
            <p>Este documento es una constancia de la atención médica recibida y no representa un certificado médico oficial.</p>
            <p>Fecha de impresión: <?= date('d/m/Y H:i:s') ?></p>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>