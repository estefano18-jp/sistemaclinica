<?php
// ARCHIVO: diagnostico_sistema.php
// Coloca este archivo en la raíz de tu proyecto y ejecutalo para diagnosticar problemas

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Diagnóstico del Sistema de Historia Clínica</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: #28a745; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .warning { color: #ffc107; font-weight: bold; }
    .info { color: #17a2b8; font-weight: bold; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

// 1. Verificar archivos del sistema
echo "<div class='section'>";
echo "<h3>📁 Verificación de Archivos</h3>";

$archivos_necesarios = [
    'models/HistoriaClinica.php',
    'controllers/historialclinica.controller.php',
    'models/Conexion.php'
];

foreach ($archivos_necesarios as $archivo) {
    if (file_exists($archivo)) {
        echo "<div class='success'>✅ $archivo - Existe</div>";
        echo "<div class='info'>   Tamaño: " . number_format(filesize($archivo)) . " bytes</div>";
        echo "<div class='info'>   Modificado: " . date('Y-m-d H:i:s', filemtime($archivo)) . "</div>";
    } else {
        echo "<div class='error'>❌ $archivo - NO ENCONTRADO</div>";
    }
}
echo "</div>";

// 2. Verificar conexión a base de datos
echo "<div class='section'>";
echo "<h3>🗄️ Verificación de Base de Datos</h3>";

try {
    if (file_exists('models/Conexion.php')) {
        require_once 'models/Conexion.php';
        $conexion = new Conexion();
        $pdo = $conexion->getConexion();
        echo "<div class='success'>✅ Conexión a base de datos - OK</div>";
        
        // Verificar estructura de tabla historiaclinica
        try {
            $stmt = $pdo->query("DESCRIBE historiaclinica");
            $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<div class='success'>✅ Tabla historiaclinica existe</div>";
            echo "<h4>Estructura actual de la tabla:</h4>";
            echo "<pre>";
            foreach ($columnas as $columna) {
                echo sprintf("%-20s %-20s %-10s %-10s %-10s %-20s\n", 
                    $columna['Field'], 
                    $columna['Type'], 
                    $columna['Null'], 
                    $columna['Key'], 
                    $columna['Default'], 
                    $columna['Extra']
                );
            }
            echo "</pre>";
            
            // Verificar columnas específicas
            $columnas_requeridas = ['idhistoriaclinica', 'idconsulta', 'enfermedadactual', 'examenfisico', 'evolucion', 'iddiagnostico', 'altamedica'];
            $columnas_existentes = array_column($columnas, 'Field');
            
            foreach ($columnas_requeridas as $col) {
                if (in_array($col, $columnas_existentes)) {
                    echo "<div class='success'>✅ Columna '$col' existe</div>";
                } else {
                    echo "<div class='error'>❌ Columna '$col' FALTA</div>";
                }
            }
            
            // Verificar columna observaciones (opcional)
            if (in_array('observaciones', $columnas_existentes)) {
                echo "<div class='success'>✅ Columna 'observaciones' existe</div>";
            } else {
                echo "<div class='warning'>⚠️ Columna 'observaciones' falta (opcional pero recomendada)</div>";
                echo "<div class='info'>   SQL para agregar: ALTER TABLE historiaclinica ADD COLUMN observaciones TEXT AFTER evolucion;</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error al verificar tabla historiaclinica: " . $e->getMessage() . "</div>";
        }
        
        // Verificar tablas relacionadas
        $tablas_relacionadas = ['consultas', 'diagnosticos', 'pacientes'];
        foreach ($tablas_relacionadas as $tabla) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabla");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<div class='success'>✅ Tabla '$tabla' existe con {$result['total']} registros</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ Tabla '$tabla' no accesible: " . $e->getMessage() . "</div>";
            }
        }
        
    } else {
        echo "<div class='error'>❌ Archivo de conexión no encontrado</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
}
echo "</div>";

// 3. Verificar controlador
echo "<div class='section'>";
echo "<h3>🎛️ Verificación del Controlador</h3>";

if (file_exists('controllers/historialclinica.controller.php')) {
    $contenido_controlador = file_get_contents('controllers/historialclinica.controller.php');
    
    // Verificar elementos críticos
    $verificaciones = [
        'header(\'Content-Type: application/json' => 'Headers JSON',
        'registrar_consulta_historia' => 'Operación registrar_consulta_historia',
        'class HistoriaClinicaController' => 'Clase del controlador',
        'require_once \'../models/HistoriaClinica.php\'' => 'Include del modelo',
        'procesarSolicitud()' => 'Método procesarSolicitud',
        '$_POST[\'observaciones\']' => 'Manejo del campo observaciones'
    ];
    
    foreach ($verificaciones as $buscar => $descripcion) {
        if (strpos($contenido_controlador, $buscar) !== false) {
            echo "<div class='success'>✅ $descripcion - Encontrado</div>";
        } else {
            echo "<div class='warning'>⚠️ $descripcion - No encontrado o modificado</div>";
        }
    }
    
    // Verificar sintaxis del archivo
    $output = [];
    $return_var = 0;
    exec("php -l controllers/historialclinica.controller.php 2>&1", $output, $return_var);
    
    if ($return_var === 0) {
        echo "<div class='success'>✅ Sintaxis del controlador - OK</div>";
    } else {
        echo "<div class='error'>❌ Error de sintaxis en controlador:</div>";
        echo "<pre>" . implode("\n", $output) . "</pre>";
    }
} else {
    echo "<div class='error'>❌ Controlador no encontrado</div>";
}
echo "</div>";

// 4. Verificar modelo
echo "<div class='section'>";
echo "<h3>🏗️ Verificación del Modelo</h3>";

if (file_exists('models/HistoriaClinica.php')) {
    $contenido_modelo = file_get_contents('models/HistoriaClinica.php');
    
    // Verificar elementos críticos
    $verificaciones_modelo = [
        'class HistoriaClinica' => 'Clase del modelo',
        'registrarConsultaHistoria' => 'Método registrarConsultaHistoria',
        'observaciones' => 'Manejo del campo observaciones',
        'beginTransaction()' => 'Uso de transacciones',
        'error_log(' => 'Logging de errores'
    ];
    
    foreach ($verificaciones_modelo as $buscar => $descripcion) {
        if (strpos($contenido_modelo, $buscar) !== false) {
            echo "<div class='success'>✅ $descripcion - Encontrado</div>";
        } else {
            echo "<div class='warning'>⚠️ $descripcion - No encontrado</div>";
        }
    }
    
    // Verificar sintaxis del archivo
    $output = [];
    $return_var = 0;
    exec("php -l models/HistoriaClinica.php 2>&1", $output, $return_var);
    
    if ($return_var === 0) {
        echo "<div class='success'>✅ Sintaxis del modelo - OK</div>";
    } else {
        echo "<div class='error'>❌ Error de sintaxis en modelo:</div>";
        echo "<pre>" . implode("\n", $output) . "</pre>";
    }
} else {
    echo "<div class='error'>❌ Modelo no encontrado</div>";
}
echo "</div>";

// 5. Prueba de endpoint
echo "<div class='section'>";
echo "<h3>🌐 Prueba de Endpoint</h3>";

$url_test = 'controllers/historialclinica.controller.php?op=test';
if (file_exists($url_test)) {
    echo "<div class='info'>📡 Probando endpoint: $url_test</div>";
    
    // Simular una solicitud GET
    $_GET['op'] = 'test';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    ob_start();
    try {
        include $url_test;
        $response = ob_get_contents();
        ob_end_clean();
        
        echo "<div class='success'>✅ Endpoint responde</div>";
        echo "<div class='info'>Respuesta:</div>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        // Verificar si es JSON válido
        $json_data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<div class='success'>✅ Respuesta es JSON válido</div>";
        } else {
            echo "<div class='warning'>⚠️ Respuesta no es JSON válido: " . json_last_error_msg() . "</div>";
        }
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "<div class='error'>❌ Error al ejecutar endpoint: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='error'>❌ No se puede acceder al endpoint</div>";
}
echo "</div>";

// 6. Verificar logs de errores
echo "<div class='section'>";
echo "<h3>📊 Logs de Errores Recientes</h3>";

$log_files = [
    ini_get('error_log'),
    '/var/log/apache2/error.log',
    '/var/log/nginx/error.log',
    './error.log',
    '../error.log'
];

$found_logs = false;
foreach ($log_files as $log_file) {
    if ($log_file && file_exists($log_file) && is_readable($log_file)) {
        echo "<div class='info'>📁 Revisando: $log_file</div>";
        $found_logs = true;
        
        // Leer las últimas 20 líneas
        $lines = file($log_file);
        $recent_lines = array_slice($lines, -20);
        
        $historia_errors = array_filter($recent_lines, function($line) {
            return stripos($line, 'historia') !== false || 
                   stripos($line, 'consulta') !== false ||
                   stripos($line, 'diagnostico') !== false;
        });
        
        if (!empty($historia_errors)) {
            echo "<div class='warning'>⚠️ Errores relacionados encontrados:</div>";
            echo "<pre>" . htmlspecialchars(implode('', $historia_errors)) . "</pre>";
        } else {
            echo "<div class='success'>✅ No se encontraron errores relacionados</div>";
        }
    }
}

if (!$found_logs) {
    echo "<div class='info'>ℹ️ No se encontraron archivos de log accesibles</div>";
}
echo "</div>";

// 7. Resumen y recomendaciones
echo "<div class='section'>";
echo "<h3>📋 Resumen y Recomendaciones</h3>";

echo "<h4>Pasos para solucionar problemas:</h4>";
echo "<ol>";
echo "<li><strong>Estructura de Base de Datos:</strong> Ejecuta el SQL de estructura de tabla que proporcioné</li>";
echo "<li><strong>Archivos del Sistema:</strong> Reemplaza los archivos del modelo y controlador con las versiones corregidas</li>";
echo "<li><strong>JavaScript:</strong> Actualiza las funciones de JavaScript con el código de debugging mejorado</li>";
echo "<li><strong>Permisos:</strong> Verifica que los archivos tengan permisos de lectura y ejecución</li>";
echo "<li><strong>Logs:</strong> Habilita el logging de errores en PHP (error_reporting = E_ALL)</li>";
echo "</ol>";

echo "<h4>Para debugging en tiempo real:</h4>";
echo "<ul>";
echo "<li>Abre las herramientas de desarrollador del navegador (F12)</li>";
echo "<li>Ve a la pestaña 'Network' antes de intentar guardar</li>";
echo "<li>Revisa la respuesta del servidor en la pestaña 'Console'</li>";
echo "<li>Usa Ctrl+Shift+D en la página para mostrar información de debug</li>";
echo "</ul>";

echo "</div>";

echo "<div style='margin-top: 30px; padding: 15px; background-color: #e7f3ff; border-left: 4px solid #2196F3;'>";
echo "<h4>🔧 Siguiente paso:</h4>";
echo "<p>1. Ejecuta el SQL de estructura de tabla<br>";
echo "2. Reemplaza los archivos PHP con las versiones corregidas<br>";
echo "3. Actualiza el JavaScript en tu página<br>";
echo "4. Prueba nuevamente el guardado de historia clínica</p>";
echo "</div>";
?>