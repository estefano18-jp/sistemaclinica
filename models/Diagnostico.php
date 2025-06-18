<?php /*RUTA: sistemaclinica/models/Diagnostico.php*/?>
<?php

require_once 'Conexion.php';

class Diagnostico
{
    private $pdo;
    private $conexion;

    public function __CONSTRUCT()
    {
        $this->conexion = new Conexion();
        $this->pdo = $this->conexion->getConexion();
    }

    /**
     * Lista todos los diagnósticos disponibles
     * @return array Lista de diagnósticos
     */
    public function listar()
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    iddiagnostico,
                    nombre,
                    descripcion,
                    codigo
                FROM 
                    diagnosticos
                ORDER BY 
                    nombre ASC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al listar diagnósticos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca diagnósticos por nombre o código
     * @param string $busqueda Término de búsqueda
     * @return array Lista de diagnósticos que coinciden con la búsqueda
     */
    public function buscar($busqueda)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    iddiagnostico,
                    nombre,
                    descripcion,
                    codigo
                FROM 
                    diagnosticos
                WHERE 
                    nombre LIKE ? OR
                    codigo LIKE ?
                ORDER BY 
                    nombre ASC
            ");
            
            $parametro = "%{$busqueda}%";
            $stmt->execute([$parametro, $parametro]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al buscar diagnósticos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un diagnóstico por su ID
     * @param int $iddiagnostico ID del diagnóstico
     * @return array|null Datos del diagnóstico o null si no existe
     */
    public function obtenerPorId($iddiagnostico)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    iddiagnostico,
                    nombre,
                    descripcion,
                    codigo
                FROM 
                    diagnosticos
                WHERE 
                    iddiagnostico = ?
            ");
            
            $stmt->execute([$iddiagnostico]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener diagnóstico por ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verifica el estado del sistema y la base de datos
     * @return array Resultado del diagnóstico del sistema
     */
    public function verificarSistema() 
    {
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
?>