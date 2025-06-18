<?php /*RUTA: sistemaclinica/controllers/enfermeria.controller.php*/?>
<?php

require_once '../models/Enfermeria.php';

class EnfermeriaController {
    private $modelo;

    public function __construct() {
        $this->modelo = new Enfermeria();
    }

    /**
     * Procesa las operaciones según la solicitud
     */
    public function procesarOperacion() {
        // Operaciones por GET
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['op'])) {
            switch ($_GET['op']) {
                case 'buscar_por_documento':
                    $this->buscarPorDocumento();
                    break;
                case 'listar':
                    $this->listarEnfermeros();
                    break;
                case 'cambiar_estado':
                    $this->cambiarEstado();
                    break;
                case 'eliminar':
                    $this->eliminarEnfermero();
                    break;
                case 'obtener':
                    $this->obtenerEnfermero();
                    break;
                default:
                    $this->responderError('Operación no válida');
                    break;
            }
        }
        // Operaciones por POST
        else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['op'])) {
            switch ($_GET['op']) {
                case 'registrar_enfermero':
                    $this->registrarEnfermero();
                    break;
                default:
                    $this->responderError('Operación no válida');
                    break;
            }
        } else {
            $this->responderError('Método no permitido o falta el parámetro de operación');
        }
    }

    /**
     * Busca un enfermero por su número de documento
     */
    private function buscarPorDocumento() {
        if (!isset($_GET['nrodoc']) || empty($_GET['nrodoc'])) {
            $this->responderError('El número de documento es requerido');
            return;
        }

        $resultado = $this->modelo->buscarEnfermeroPorDocumento($_GET['nrodoc']);
        echo json_encode($resultado);
    }

    /**
     * Registra un nuevo enfermero en el sistema
     */
    private function registrarEnfermero() {
        // Verificar campos requeridos
        $camposRequeridos = ['apellidos', 'nombres', 'tipodoc', 'nrodoc', 'telefono', 'email', 'passuser'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                $this->responderError("El campo $campo es requerido");
                return;
            }
        }

        // Validar formato de correo electrónico
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $this->responderError('El formato de correo electrónico no es válido');
            return;
        }

        // Validar longitud de contraseña
        if (strlen($_POST['passuser']) < 6) {
            $this->responderError('La contraseña debe tener al menos 6 caracteres');
            return;
        }

        // Verificar que las contraseñas coincidan si se proporcionó confirmarpassuser
        if (isset($_POST['confirmarpassuser']) && $_POST['passuser'] !== $_POST['confirmarpassuser']) {
            $this->responderError('Las contraseñas no coinciden');
            return;
        }

        // Preparar datos para el registro
        $datos = [
            'apellidos' => $_POST['apellidos'],
            'nombres' => $_POST['nombres'],
            'tipodoc' => $_POST['tipodoc'],
            'nrodoc' => $_POST['nrodoc'],
            'telefono' => $_POST['telefono'],
            'fechanacimiento' => isset($_POST['fechanacimiento']) ? $_POST['fechanacimiento'] : null,
            'genero' => isset($_POST['genero']) ? $_POST['genero'] : null,
            'direccion' => isset($_POST['direccion']) ? $_POST['direccion'] : null,
            'email' => $_POST['email'],
            'passuser' => $_POST['passuser']
        ];

        // Registrar enfermero
        $resultado = $this->modelo->registrarEnfermero($datos);
        echo json_encode($resultado);
    }

    /**
     * Lista todos los enfermeros registrados
     */
    private function listarEnfermeros() {
        $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : null;
        $estado = isset($_GET['estado']) ? $_GET['estado'] : null;
        $fechaRegistro = isset($_GET['fechaRegistro']) ? $_GET['fechaRegistro'] : null;

        $resultado = $this->modelo->listarEnfermeros($busqueda, $estado, $fechaRegistro);
        echo json_encode($resultado);
    }

    /**
     * Cambia el estado de un enfermero (ACTIVO/INACTIVO)
     */
    private function cambiarEstado() {
        if (!isset($_GET['nrodoc']) || empty($_GET['nrodoc'])) {
            $this->responderError('El número de documento es requerido');
            return;
        }

        $resultado = $this->modelo->cambiarEstadoEnfermero($_GET['nrodoc']);
        echo json_encode($resultado);
    }

    /**
     * Elimina un enfermero del sistema
     */
    private function eliminarEnfermero() {
        if (!isset($_GET['nrodoc']) || empty($_GET['nrodoc'])) {
            $this->responderError('El número de documento es requerido');
            return;
        }

        $resultado = $this->modelo->eliminarEnfermero($_GET['nrodoc']);
        echo json_encode($resultado);
    }

    /**
     * Obtiene la información detallada de un enfermero
     */
    private function obtenerEnfermero() {
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            $this->responderError('El ID del enfermero es requerido');
            return;
        }

        $resultado = $this->modelo->obtenerEnfermeroPorId($_GET['id']);
        echo json_encode($resultado);
    }

    /**
     * Responde con un mensaje de error en formato JSON
     */
    private function responderError($mensaje) {
        echo json_encode([
            'status' => false,
            'mensaje' => $mensaje
        ]);
    }
}

// Inicializar el controlador y procesar la operación
$controller = new EnfermeriaController();
$controller->procesarOperacion();
?>