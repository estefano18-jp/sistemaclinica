<?php
require_once '../../../models/Paciente.php';
// Asegúrate de incluir cualquier librería para generar PDFs que estés usando, por ejemplo FPDF o MPDF
// require_once '../../../libraries/fpdf/fpdf.php';

// Verificar que se haya proporcionado un ID de paciente
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo 'ID de paciente no proporcionado.';
    exit;
}

$idPaciente = $_GET['id'];
$paciente = new Paciente();

// Obtener información del paciente
$infoPaciente = $paciente->obtenerPacientePorId($idPaciente);

if (!$infoPaciente) {
    echo 'Paciente no encontrado.';
    exit;
}

// Obtener las alergias del paciente (si es necesario)
$alergias = $paciente->obtenerAlergiasPorId($idPaciente);

// Determinar si es descarga o vista previa
$isDownload = isset($_GET['download']) && $_GET['download'] == 1;

// Generar PDF usando alguna librería como FPDF
// Este es un ejemplo básico usando FPDF (debes adaptarlo a tu librería)
/*
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Ficha de Paciente', 0, 1, 'C');
$pdf->Ln(10);

// Información del paciente
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Información Personal', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 8, 'Apellidos:', 0);
$pdf->Cell(0, 8, $infoPaciente['apellidos'], 0, 1);
$pdf->Cell(40, 8, 'Nombres:', 0);
$pdf->Cell(0, 8, $infoPaciente['nombres'], 0, 1);
$pdf->Cell(40, 8, 'Documento:', 0);
$pdf->Cell(0, 8, $infoPaciente['tipodoc'] . ': ' . $infoPaciente['nrodoc'], 0, 1);
// ... más campos ...

// Alergias
if (!empty($alergias)) {
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Alergias', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    
    foreach ($alergias as $alergia) {
        $pdf->Cell(40, 8, 'Tipo:', 0);
        $pdf->Cell(60, 8, $alergia['tipoalergia'], 0);
        $pdf->Cell(30, 8, 'Gravedad:', 0);
        $pdf->Cell(0, 8, $alergia['gravedad'], 0, 1);
        $pdf->Cell(40, 8, 'Alergia:', 0);
        $pdf->Cell(0, 8, $alergia['alergia'], 0, 1);
        $pdf->Ln(2);
    }
}

// Salida del PDF
if ($isDownload) {
    // Si es descarga, enviar como archivo adjunto
    $pdf->Output('D', 'Paciente_' . $infoPaciente['apellidos'] . '_' . $infoPaciente['nombres'] . '.pdf');
} else {
    // Si es vista previa, mostrar en el navegador
    $pdf->Output('I', 'Paciente.pdf');
}
*/

// NOTA: Este es un ejemplo simplificado. 
// Si no tienes una librería PDF instalada, puedes mostrar un HTML formateado como PDF para esta demo.
// A continuación hay un ejemplo alternativo usando HTML:

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Paciente</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4a4a4a;
            padding-bottom: 10px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .info-row {
            display: flex;
            border-bottom: 1px solid #eee;
            padding: 8px 0;
        }
        .info-label {
            font-weight: bold;
            min-width: 150px;
        }
        .info-value {
            flex-grow: 1;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: white;
        }
        .badge-leve {
            background-color: #28a745;
        }
        .badge-moderada {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-grave {
            background-color: #dc3545;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ficha de Paciente</h1>
    </div>
    
    <div class="section">
        <h2 class="section-title">Información Personal</h2>
        
        <div class="info-row">
            <div class="info-label">Apellidos:</div>
            <div class="info-value"><?= htmlspecialchars($infoPaciente['apellidos']) ?></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Nombres:</div>
            <div class="info-value"><?= htmlspecialchars($infoPaciente['nombres']) ?></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Documento:</div>
            <div class="info-value">
                <?= htmlspecialchars($infoPaciente['tipodoc']) ?>: 
                <?= htmlspecialchars($infoPaciente['nrodoc']) ?>
            </div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Fecha de Nacimiento:</div>
            <div class="info-value">
                <?= date("d/m/Y", strtotime($infoPaciente['fechanacimiento'])) ?>
                (<?= htmlspecialchars($infoPaciente['edad'] ?? 'N/A') ?> años)
            </div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Género:</div>
            <div class="info-value">
                <?= ($infoPaciente['genero'] == 'M') ? 'Masculino' : 
                   (($infoPaciente['genero'] == 'F') ? 'Femenino' : 'Otro') ?>
            </div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Teléfono:</div>
            <div class="info-value"><?= htmlspecialchars($infoPaciente['telefono']) ?></div>
        </div>
        
        <?php if (!empty($infoPaciente['email'])): ?>
        <div class="info-row">
            <div class="info-label">Email:</div>
            <div class="info-value"><?= htmlspecialchars($infoPaciente['email']) ?></div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($infoPaciente['direccion'])): ?>
        <div class="info-row">
            <div class="info-label">Dirección:</div>
            <div class="info-value"><?= htmlspecialchars($infoPaciente['direccion']) ?></div>
        </div>
        <?php endif; ?>
        
        <div class="info-row">
            <div class="info-label">Fecha de Registro:</div>
            <div class="info-value">
                <?= date("d/m/Y", strtotime($infoPaciente['fecharegistro'])) ?>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2 class="section-title">Alergias</h2>
        
        <?php if (empty($alergias)): ?>
            <p>No se han registrado alergias para este paciente.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Alergia</th>
                        <th>Gravedad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alergias as $alergia): ?>
                        <tr>
                            <td><?= ucfirst(strtolower(htmlspecialchars($alergia['tipoalergia']))) ?></td>
                            <td><?= htmlspecialchars($alergia['alergia']) ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower($alergia['gravedad']) ?>">
                                    <?= htmlspecialchars($alergia['gravedad']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <div class="no-print">
        <p>
            <small>Este documento es una vista previa. Para obtener una copia física, use la función de impresión del navegador.</small>
        </p>
    </div>
    
    <script>
        // Imprimir automáticamente si se solicita la descarga
        <?php if ($isDownload): ?>
        window.onload = function() {
            window.print();
        };
        <?php endif; ?>
    </script>
</body>
</html>
<?php
exit;
?>