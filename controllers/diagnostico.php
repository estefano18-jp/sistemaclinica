<?php
// diagnostico.php
require_once '../models/Conexion.php';

class Diagnostico {
    private $conexion;
    
    public function __construct() {
        $this->conexion = new Conexion();
    }
    
    public function verificarSistema() {
        try {
            $pdo = $this->conexion->getConexion();
            $resultado = [
                'status' => true,
                'conexion' => true,
                'tablas_ok' => true,
                'tablas_faltantes' => [],
                'recomendacion' => 'El sistema parece estar correctamente configurado.'
            ];
            
            // Lista de tablas necesarias
            $tablas = [
                'personas', 'colaboradores', 'especialidades', 'contratos', 
                'atenciones', 'horarios', 'usuarios'
            ];
            
            // Verificar cada tabla
            foreach ($tablas as $tabla) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
                if ($stmt->rowCount() == 0) {
                    $resultado['tablas_ok'] = false;
                    $resultado['tablas_faltantes'][] = $tabla;
                }
            }
            
            // Generar recomendación
            if (!$resultado['tablas_ok']) {
                $resultado['recomendacion'] = 'Se han detectado tablas faltantes. Por favor, ejecute el script de base de datos para crear las tablas necesarias.';
            }
            
            return $resultado;
        } catch (Exception $e) {
            return [
                'status' => false,
                'conexion' => false,
                'mensaje' => 'Error de conexión: ' . $e->getMessage(),
                'recomendacion' => 'Verifique la configuración de conexión a la base de datos en el archivo Conexion.php'
            ];
        }
    }
}

// Ejecutar diagnóstico
$diagnostico = new Diagnostico();
echo json_encode($diagnostico->verificarSistema());
?>