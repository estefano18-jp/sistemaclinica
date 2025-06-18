<?php /*RUTA: sistemaclinica/controllers/receta.controller.php*/?>
<?php
session_start();

require_once '../models/Receta.php';

class RecetaController
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new Receta();
    }

    /**
     * Método para procesar la solicitud según la operación especificada
     */
    public function procesarSolicitud()
    {
        $operacion = isset($_GET['op']) ? $_GET['op'] : '';

        switch ($operacion) {
            case 'registrar':
                $this->registrarReceta();
                break;
            case 'obtener_por_paciente':
                $this->obtenerRecetasPorPaciente();
                break;
            case 'obtener_por_consulta':
                $this->obtenerRecetaPorConsulta();
                break;
            case 'verificar_alergia':
                $this->verificarAlergiaMedicamento();
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
     * Registra una nueva receta médica
     */
    private function registrarReceta()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Validar campos obligatorios
        $camposRequeridos = ['idconsulta', 'medicacion', 'cantidad', 'frecuencia'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => "Campo requerido: {$campo}"
                ]);
                return;
            }
        }

        // Preparar datos básicos
        $datos = [
            'idconsulta' => intval($_POST['idconsulta']),
            'medicacion' => $_POST['medicacion'],
            'cantidad' => $_POST['cantidad'],
            'frecuencia' => $_POST['frecuencia'],
            'observaciones' => isset($_POST['observaciones']) ? $_POST['observaciones'] : ''
        ];

        // Procesar tratamientos adicionales
        if (isset($_POST['tratamientos_json']) && !empty($_POST['tratamientos_json'])) {
            $tratamientos = json_decode($_POST['tratamientos_json'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $datos['tratamientos'] = $tratamientos;
            }
        }

        // Registrar receta
        $resultado = $this->modelo->registrar($datos);

        // Retornar resultado
        echo json_encode($resultado);
    }

    /**
     * Obtiene las recetas por ID de paciente
     */
    private function obtenerRecetasPorPaciente()
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
        if (!isset($_GET['idpaciente'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de paciente no especificado'
            ]);
            return;
        }

        $idpaciente = intval($_GET['idpaciente']);

        // Obtener recetas
        $recetas = $this->modelo->obtenerPorPaciente($idpaciente);

        echo json_encode([
            'status' => true,
            'data' => $recetas
        ]);
    }

    /**
     * Obtiene la receta por ID de consulta
     */
    private function obtenerRecetaPorConsulta()
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
        if (!isset($_GET['idconsulta'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de consulta no especificado'
            ]);
            return;
        }

        $idconsulta = intval($_GET['idconsulta']);

        // Obtener receta
        $receta = $this->modelo->obtenerPorConsulta($idconsulta);

        if ($receta) {
            echo json_encode([
                'status' => true,
                'data' => $receta
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'No se encontró la receta para esta consulta'
            ]);
        }
    }

    /**
     * Verifica si un medicamento está relacionado con alergias del paciente
     */
    private function verificarAlergiaMedicamento()
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
        if (!isset($_GET['idpaciente']) || !isset($_GET['medicamento'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Faltan parámetros requeridos'
            ]);
            return;
        }

        $idpaciente = intval($_GET['idpaciente']);
        $medicamento = $_GET['medicamento'];

        // Verificar alergias
        $resultado = $this->modelo->verificarAlergiaMedicamento($idpaciente, $medicamento);

        echo json_encode([
            'status' => true,
            'esAlergico' => $resultado['esAlergico'],
            'alergias' => $resultado['alergias']
        ]);
    }
}

// Iniciar controlador y procesar solicitud
$controller = new RecetaController();
$controller->procesarSolicitud();
?>