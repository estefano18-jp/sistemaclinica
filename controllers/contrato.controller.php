<?php /*RUTA: sistemaclinica/controllers/contrato.controller.php*/?>
<?php

require_once '../models/Contrato.php';

class ContratoController {
    private $modelo;

    public function __construct() {
        $this->modelo = new Contrato();
    }

    /**
     * Método para procesar la solicitud según la operación especificada
     */
    public function procesarSolicitud() {
        // Operaciones por GET
        if (isset($_GET['op'])) {
            switch ($_GET['op']) {
                case 'registrar':
                    $this->registrarContrato();
                    break;
                case 'listar':
                case 'obtener_por_colaborador':
                    $this->obtenerContratosPorColaborador();
                    break;
                case 'obtener':
                    $this->obtenerContrato();
                    break;
                case 'obtener_activo':
                    $this->obtenerContratoActivo();
                    break;
                case 'actualizar':
                    $this->actualizarContrato();
                    break;
                default:
                    echo json_encode([
                        'status' => false,
                        'mensaje' => 'Operación no válida'
                    ]);
                    break;
            }
        }
        
        // Operaciones por POST
        if (isset($_POST['op'])) {
            switch ($_POST['op']) {
                case 'registrar':
                    $this->registrarContrato();
                    break;
                case 'actualizar':
                    $this->actualizarContrato();
                    break;
                default:
                    echo json_encode([
                        'status' => false,
                        'mensaje' => 'Operación no válida'
                    ]);
                    break;
            }
        }
    }

    /**
     * Registra un nuevo contrato para un colaborador
     */
    public function registrarContrato() {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Capturar datos del formulario
            $datos = [
                'idcolaborador' => isset($_POST['idcolaborador']) ? intval($_POST['idcolaborador']) : 0,
                'tipocontrato' => isset($_POST['tipocontrato']) ? $_POST['tipocontrato'] : '',
                'fechainicio' => isset($_POST['fechainicio']) ? $_POST['fechainicio'] : '',
                'fechafin' => isset($_POST['fechafin']) && !empty($_POST['fechafin']) ? $_POST['fechafin'] : null
            ];
            
            // Validar campos obligatorios
            if ($datos['idcolaborador'] <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'El ID de colaborador es requerido o no válido'
                ]);
                return;
            }
            
            if (empty($datos['tipocontrato'])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'El tipo de contrato es requerido'
                ]);
                return;
            }
            
            if (empty($datos['fechainicio'])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'La fecha de inicio es requerida'
                ]);
                return;
            }
            
            // Registrar contrato
            $resultado = $this->modelo->registrarContrato($datos);
            
            echo json_encode($resultado);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método de solicitud no válido'
            ]);
        }
    }

    /**
     * Obtiene los contratos de un colaborador específico
     */
    public function obtenerContratosPorColaborador() {
        // Verificar que se reciba el método GET
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // Capturar ID del colaborador
            $idColaborador = isset($_GET['idcolaborador']) ? intval($_GET['idcolaborador']) : 0;
            
            if ($idColaborador <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de colaborador no válido'
                ]);
                return;
            }
            
            // Capturar filtros adicionales
            $filtros = [];
            if (isset($_GET['estado'])) {
                $filtros['estado'] = $_GET['estado'];
            }
            
            // Obtener contratos
            $contratos = $this->modelo->obtenerContratosPorColaborador($idColaborador);
            
            echo json_encode([
                'status' => true,
                'data' => $contratos
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método de solicitud no válido'
            ]);
        }
    }

    /**
     * Obtiene información de un contrato específico
     */
    public function obtenerContrato() {
        // Verificar que se reciba el método GET
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // Capturar ID del contrato
            $idContrato = isset($_GET['id']) ? intval($_GET['id']) : 0;
            
            if ($idContrato <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de contrato no válido'
                ]);
                return;
            }
            
            // Obtener datos del contrato
            $contrato = $this->modelo->obtenerContratoPorId($idContrato);
            
            if ($contrato) {
                echo json_encode([
                    'status' => true,
                    'data' => $contrato
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Contrato no encontrado'
                ]);
            }
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método de solicitud no válido'
            ]);
        }
    }
    
    /**
     * Obtiene el contrato activo de un colaborador
     */
    public function obtenerContratoActivo() {
        // Verificar que se reciba el método GET
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // Capturar ID del colaborador
            $idColaborador = isset($_GET['idcolaborador']) ? intval($_GET['idcolaborador']) : 0;
            
            if ($idColaborador <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de colaborador no válido'
                ]);
                return;
            }
            
            // Obtener contrato activo
            $contrato = $this->modelo->obtenerContratoActivo($idColaborador);
            
            if ($contrato) {
                echo json_encode([
                    'status' => true,
                    'data' => $contrato
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'No se encontró un contrato activo para este colaborador'
                ]);
            }
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método de solicitud no válido'
            ]);
        }
    }
    
    /**
     * Actualiza un contrato existente
     */
    public function actualizarContrato() {
        // Capturar método de la solicitud
        $metodo = $_SERVER['REQUEST_METHOD'];
        $datos = [];
        
        if ($metodo == 'POST') {
            // Capturar datos desde POST
            $datos = [
                'idcontrato' => isset($_POST['idcontrato']) ? intval($_POST['idcontrato']) : 0,
                'tipocontrato' => isset($_POST['tipocontrato']) ? $_POST['tipocontrato'] : null,
                'fechainicio' => isset($_POST['fechainicio']) ? $_POST['fechainicio'] : null,
                'fechafin' => isset($_POST['fechafin']) ? $_POST['fechafin'] : null
            ];
        } else if ($metodo == 'GET') {
            // Capturar datos desde GET
            $datos = [
                'idcontrato' => isset($_GET['idcontrato']) ? intval($_GET['idcontrato']) : 0,
                'tipocontrato' => isset($_GET['tipocontrato']) ? $_GET['tipocontrato'] : null,
                'fechainicio' => isset($_GET['fechainicio']) ? $_GET['fechainicio'] : null,
                'fechafin' => isset($_GET['fechafin']) ? $_GET['fechafin'] : null
            ];
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método de solicitud no válido'
            ]);
            return;
        }
        
        // Validar ID de contrato
        if ($datos['idcontrato'] <= 0) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de contrato no válido'
            ]);
            return;
        }
        
        // Actualizar contrato
        $resultado = $this->modelo->actualizarContrato($datos);
        
        echo json_encode($resultado);
    }
}

// Iniciar el controlador y procesar la solicitud
$controller = new ContratoController();
$controller->procesarSolicitud();
?>