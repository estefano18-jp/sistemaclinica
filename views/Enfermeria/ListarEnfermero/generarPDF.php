<?php
// Verificar que se reciba el ID del enfermero
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo 'ID de enfermero no proporcionado.';
    exit;
}

$idEnfermero = intval($_GET['id']);
$esDescarga = isset($_GET['download']) && $_GET['download'] == 1;

// Incluir el modelo de enfermería
require_once '../../../models/Enfermeria.php';

// Obtener los datos del enfermero
$enfermeria = new Enfermeria();
$resultado = $enfermeria->obtenerEnfermeroPorId($idEnfermero);

if (!$resultado['status']) {
    echo $resultado['mensaje'];
    exit;
}

$enfermero = $resultado['data'];

// Configurar cabeceras para HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Enfermero</title>
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
        .badge-activo {
            background-color: #28a745;
        }
        .badge-inactivo {
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
        .print-button {
            padding: 8px 15px; 
            background-color: #007bff; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer;
            margin-top: 20px;
        }
        .print-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ficha de Enfermero</h1>
    </div>
    
    <div class="section">
        <h2 class="section-title">Información Personal</h2>
        
        <div class="info-row">
            <div class="info-label">ID Colaborador:</div>
            <div class="info-value"><?= htmlspecialchars($enfermero['idcolaborador']) ?></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Apellidos:</div>
            <div class="info-value"><?= htmlspecialchars($enfermero['apellidos']) ?></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Nombres:</div>
            <div class="info-value"><?= htmlspecialchars($enfermero['nombres']) ?></div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Documento:</div>
            <div class="info-value">
                <?= htmlspecialchars($enfermero['tipodoc']) ?>: 
                <?= htmlspecialchars($enfermero['nrodoc']) ?>
            </div>
        </div>
        
        <?php if (!empty($enfermero['fechanacimiento'])): ?>
        <div class="info-row">
            <div class="info-label">Fecha de Nacimiento:</div>
            <div class="info-value">
                <?= date("d/m/Y", strtotime($enfermero['fechanacimiento'])) ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="info-row">
            <div class="info-label">Género:</div>
            <div class="info-value">
                <?= ($enfermero['genero'] == 'M') ? 'Masculino' : 
                   (($enfermero['genero'] == 'F') ? 'Femenino' : 'Otro') ?>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2 class="section-title">Información de Contacto</h2>
        
        <div class="info-row">
            <div class="info-label">Teléfono:</div>
            <div class="info-value"><?= htmlspecialchars($enfermero['telefono']) ?></div>
        </div>
        
        <?php if (!empty($enfermero['email'])): ?>
        <div class="info-row">
            <div class="info-label">Email:</div>
            <div class="info-value"><?= htmlspecialchars($enfermero['email']) ?></div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($enfermero['direccion'])): ?>
        <div class="info-row">
            <div class="info-label">Dirección:</div>
            <div class="info-value"><?= htmlspecialchars($enfermero['direccion']) ?></div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2 class="section-title">Información Laboral</h2>
        
        <div class="info-row">
            <div class="info-label">Estado:</div>
            <div class="info-value">
                <span class="badge badge-<?= strtolower($enfermero['estado'] == 'ACTIVO' ? 'activo' : 'inactivo') ?>">
                    <?= $enfermero['estado'] == 'ACTIVO' ? 'Activo' : 'Inactivo' ?>
                </span>
            </div>
        </div>
    </div>
    
    <div class="no-print">
        <p>
            <small>Este documento es una vista previa. Para obtener una copia física, use el botón de impresión a continuación o la función de impresión de su navegador.</small>
        </p>
        <button type="button" onclick="window.print();" class="print-button">
            <i class="fas fa-print"></i> Imprimir Documento
        </button>
    </div>
    
    <script>
        // Imprimir automáticamente si se solicita la descarga
        <?php if ($esDescarga): ?>
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