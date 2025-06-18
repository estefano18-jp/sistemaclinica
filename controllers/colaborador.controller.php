
<?php /*RUTA: sistemaclinica/controllers/colaborador.controller.php*/?>
<?php

require_once '../models/Colaborador.php';

class ColaboradorController
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new Colaborador();
    }

    /**
     * Registra la información profesional de un doctor/colaborador
     */
    public function registrarColaboradorProfesional()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Capturar datos del formulario
            $datos = [
                'idpersona' => isset($_POST['idpersona']) ? intval($_POST['idpersona']) : 0,
                'idespecialidad' => isset($_POST['idespecialidad']) ? intval($_POST['idespecialidad']) : 0,
                'precioatencion' => isset($_POST['precioatencion']) ? floatval($_POST['precioatencion']) : 0
            ];

            // Validar campos obligatorios
            if ($datos['idpersona'] <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'El ID de persona es requerido o no válido'
                ]);
                return;
            }

            if ($datos['idespecialidad'] <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'La especialidad es requerida'
                ]);
                return;
            }

            if ($datos['precioatencion'] <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'El precio de atención debe ser mayor a cero'
                ]);
                return;
            }

            // Registrar colaborador
            $resultado = $this->modelo->registrarColaboradorProfesional($datos);

            echo json_encode($resultado);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método de solicitud no válido'
            ]);
        }
    }

    /**
     * Obtiene las especialidades disponibles
     */
    public function obtenerEspecialidades()
    {
        // Verificar que se reciba el método GET
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // Obtener especialidades
            $especialidades = $this->modelo->obtenerEspecialidades();

            echo json_encode([
                'status' => true,
                'data' => $especialidades
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método de solicitud no válido'
            ]);
        }
    }

    /**
     * Obtiene información de un colaborador específico
     */
    public function obtenerColaborador()
    {
        // Verificar que se reciba el método GET
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // Capturar ID del colaborador
            $idColaborador = isset($_GET['id']) ? intval($_GET['id']) : 0;

            if ($idColaborador <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de colaborador no válido'
                ]);
                return;
            }

            // Obtener datos del colaborador
            $colaborador = $this->modelo->obtenerColaboradorPorId($idColaborador);

            if ($colaborador) {
                echo json_encode([
                    'status' => true,
                    'data' => $colaborador
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Colaborador no encontrado'
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
    $controller = new ColaboradorController();

    switch ($_GET['op']) {
        case 'registrar':
            $controller->registrarColaboradorProfesional();
            break;
        case 'especialidades':
            $controller->obtenerEspecialidades();
            break;
        case 'obtener':
            $controller->obtenerColaborador();
            break;
        default:
            echo json_encode([
                'status' => false,
                'mensaje' => 'Operación no válida'
            ]);
            break;
    }
}
