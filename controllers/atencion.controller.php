
<?php /*RUTA: sistemaclinica/controllers/atencion.controller.php*/?>
<?php
// atencion.controller.php
require_once '../models/Atencion.php';

class AtencionController {
    private $modelo;

    public function __construct() {
        $this->modelo = new Atencion();
    }

    /**
     * Registra una nueva atención
     */
    public function registrarAtencion() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'idcontrato' => isset($_POST['idcontrato']) ? intval($_POST['idcontrato']) : 0,
                'diasemana' => isset($_POST['diasemana']) ? $_POST['diasemana'] : ''
            ];
            
            if ($datos['idcontrato'] <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'El ID de contrato es requerido'
                ]);
                return;
            }
            
            if (empty($datos['diasemana'])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'El día de semana es requerido'
                ]);
                return;
            }
            
            $resultado = $this->modelo->registrarAtencion($datos);
            echo json_encode($resultado);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método de solicitud no válido'
            ]);
        }
    }
}

// Procesar la solicitud
if (isset($_GET['op'])) {
    $controller = new AtencionController();
    
    switch ($_GET['op']) {
        case 'registrar':
            $controller->registrarAtencion();
            break;
        default:
            echo json_encode([
                'status' => false,
                'mensaje' => 'Operación no válida'
            ]);
            break;
    }
}
?>