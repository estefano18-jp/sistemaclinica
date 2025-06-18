<?php /*RUTA: sistemaclinica/controllers/persona.controller.php*/?>
<?php

require_once '../models/Persona.php';

class PersonaController {
    private $modelo;

    public function __construct() {
        $this->modelo = new Persona();
    }

    /**
     * Busca una persona por número de documento
     */
    public function buscarPorDocumento() {
        // Verificar que sea método GET
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // Capturar el número de documento
            $nrodoc = isset($_GET['nrodoc']) ? $_GET['nrodoc'] : '';
            
            if (empty($nrodoc)) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'El número de documento es requerido'
                ]);
                return;
            }
            
            // Buscar la persona en la base de datos
            $persona = $this->modelo->buscarPersonaPorDocumento($nrodoc);
            
            if ($persona) {
                echo json_encode([
                    'status' => true,
                    'persona' => $persona
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'No se encontró ninguna persona con el documento proporcionado'
                ]);
            }
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
    $controller = new PersonaController();
    
    switch ($_GET['op']) {
        case 'buscar_por_documento':
            $controller->buscarPorDocumento();
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