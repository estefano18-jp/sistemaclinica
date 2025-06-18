<?php /*RUTA: sistemaclinica/controllers/diagnostico.controller.php*/?>
<?php
session_start();

require_once '../models/Diagnostico.php';

class DiagnosticoController
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new Diagnostico();
    }

    /**
     * Método para procesar la solicitud según la operación especificada
     */
    public function procesarSolicitud()
    {
        $operacion = isset($_GET['op']) ? $_GET['op'] : '';

        switch ($operacion) {
            case 'listar':
                $this->listarDiagnosticos();
                break;
            case 'buscar':
                $this->buscarDiagnosticos();
                break;
            case 'obtener':
                $this->obtenerDiagnosticoPorId();
                break;
            case 'verificar_sistema':
                $this->verificarSistema();
                break;
            default:
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Operación no válida'
                ]);
                break;
        }
    }

    /**
     * Lista todos los diagnósticos disponibles
     */
    private function listarDiagnosticos()
    {
        // Obtener diagnósticos
        $diagnosticos = $this->modelo->listar();

        echo json_encode([
            'status' => true,
            'data' => $diagnosticos
        ]);
    }

    /**
     * Busca diagnósticos por nombre o código
     */
    private function buscarDiagnosticos()
    {
        // Verificar que se reciba el método GET
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Validar parámetros
        if (!isset($_GET['busqueda'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Término de búsqueda no especificado'
            ]);
            return;
        }

        $busqueda = $_GET['busqueda'];

        // Buscar diagnósticos
        $diagnosticos = $this->modelo->buscar($busqueda);

        echo json_encode([
            'status' => true,
            'data' => $diagnosticos
        ]);
    }

    /**
     * Obtiene un diagnóstico por su ID
     */
    private function obtenerDiagnosticoPorId()
    {
        // Verificar que se reciba el método GET
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Validar parámetros
        if (!isset($_GET['iddiagnostico'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de diagnóstico no especificado'
            ]);
            return;
        }

        $iddiagnostico = intval($_GET['iddiagnostico']);

        // Obtener diagnóstico
        $diagnostico = $this->modelo->obtenerPorId($iddiagnostico);

        if ($diagnostico) {
            echo json_encode([
                'status' => true,
                'data' => $diagnostico
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'No se encontró el diagnóstico'
            ]);
        }
    }
    /**
     * Verifica el estado del sistema y la base de datos
     */
    private function verificarSistema()
    {
        // Ejecutar diagnóstico del sistema
        $resultado = $this->modelo->verificarSistema();
        
        // Retornar resultado
        echo json_encode($resultado);
    }
}

// Iniciar controlador y procesar solicitud
$controller = new DiagnosticoController();
$controller->procesarSolicitud();
?>