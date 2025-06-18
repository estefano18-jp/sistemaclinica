<?php /*RUTA: sistemaclinica/controllers/empresa.controller.php*/?>
<?php
session_start();

require_once '../models/Empresa.php';

class EmpresaController
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new Empresa();
    }

    /**
     * Método para procesar la solicitud según la operación especificada
     */
    public function procesarSolicitud() {
        $operacion = isset($_GET['op']) ? $_GET['op'] : '';
    
        switch ($operacion) {
            case 'buscar':
                $this->buscarEmpresa();
                break;
            case 'registrar':
                $this->registrarEmpresa();
                break;
            case 'actualizar':
                $this->actualizarEmpresa();
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
     * Busca una empresa por su RUC
     */
    private function buscarEmpresa()
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
        if (!isset($_GET['ruc'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Falta el RUC'
            ]);
            return;
        }

        $ruc = $_GET['ruc'];

        // Buscar empresa
        $empresa = $this->modelo->buscarPorRuc($ruc);

        if ($empresa) {
            echo json_encode([
                'status' => true,
                'data' => $empresa
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Empresa no encontrada'
            ]);
        }
    }

    /**
     * Registra una nueva empresa
     */
    private function registrarEmpresa()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Validar campos requeridos
        $camposObligatorios = ['ruc', 'razonsocial'];
        foreach ($camposObligatorios as $campo) {
            if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => "Falta el campo requerido: {$campo}"
                ]);
                return;
            }
        }

        // Registrar datos recibidos para depuración
        error_log("Datos recibidos para registro de empresa: " . print_r($_POST, true));

        // Preparar solo los datos proporcionados
        $datos = [
            'razonsocial' => $_POST['razonsocial'],
            'ruc' => $_POST['ruc']
        ];

        // Agregar campos opcionales solo si están definidos y no están vacíos
        if (isset($_POST['direccion'])) {
            $datos['direccion'] = $_POST['direccion'];
        }

        if (isset($_POST['nombrecomercial']) && trim($_POST['nombrecomercial']) !== '') {
            $datos['nombrecomercial'] = $_POST['nombrecomercial'];
        }

        if (isset($_POST['telefono']) && trim($_POST['telefono']) !== '') {
            $datos['telefono'] = $_POST['telefono'];
        }

        if (isset($_POST['email']) && trim($_POST['email']) !== '') {
            $datos['email'] = $_POST['email'];
        }

        // Registrar los datos que se enviarán al modelo
        error_log("Datos preparados para el modelo: " . print_r($datos, true));

        // Registrar empresa
        $resultado = $this->modelo->registrar($datos);

        // Retornar resultado
        echo json_encode($resultado);
    }
    /**
     * Actualiza una empresa existente
     */
    private function actualizarEmpresa()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Validar que se proporcione el ID de la empresa
        if (!isset($_POST['idempresa']) || empty($_POST['idempresa'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Falta el ID de la empresa'
            ]);
            return;
        }

        $idempresa = intval($_POST['idempresa']);

        // Preparar los datos para actualizar
        $datos = [];

        // Agregar solo los campos proporcionados
        if (isset($_POST['razonsocial'])) {
            $datos['razonsocial'] = $_POST['razonsocial'];
        }

        if (isset($_POST['direccion'])) {
            $datos['direccion'] = $_POST['direccion'];
        }

        if (isset($_POST['nombrecomercial'])) {
            $datos['nombrecomercial'] = $_POST['nombrecomercial'];
        }

        if (isset($_POST['telefono'])) {
            $datos['telefono'] = $_POST['telefono'];
        }

        if (isset($_POST['email'])) {
            $datos['email'] = $_POST['email'];
        }

        // Registrar para depuración
        error_log("Datos para actualizar empresa: " . print_r($datos, true));

        // Actualizar empresa
        $resultado = $this->modelo->actualizar($idempresa, $datos);

        // Retornar resultado
        echo json_encode($resultado);
    }
}

// Iniciar controlador y procesar solicitud
$controller = new EmpresaController();
$controller->procesarSolicitud();
