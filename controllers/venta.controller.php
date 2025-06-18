<?php /*RUTA: sistemaclinica/controllers/venta.controller.php*/ ?>
<?php
session_start();

require_once '../models/Venta.php';

class VentaController
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new Venta();
    }

    /**
     * Método para procesar la solicitud según la operación especificada
     */
    public function procesarSolicitud()
    {
        $operacion = isset($_GET['op']) ? $_GET['op'] : '';

        switch ($operacion) {
            case 'registrar':
                $this->registrarVenta();
                break;
            case 'comprobante':
                $this->obtenerComprobante();
                break;
            case 'comprobante_por_cita':
                $this->obtenerComprobantePorCita();
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
     * Registra una nueva venta
     */
    private function registrarVenta()
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
        $camposRequeridos = ['idcliente', 'idconsulta', 'precio', 'tipocomprobante', 'tipopago'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => "Falta el campo requerido: {$campo}"
                ]);
                return;
            }
        }

        // CORRECCIÓN CRÍTICA: Validar y normalizar el tipo de pago
        $tipopago = isset($_POST['tipopago']) && !empty($_POST['tipopago'])
            ? strtoupper(trim($_POST['tipopago']))
            : 'EFECTIVO';

        // MODIFICADO: Añadir 'YAPE' y 'PLIN' a los valores permitidos
        $valoresPermitidos = ['EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'YAPE', 'PLIN'];
        if (!in_array($tipopago, $valoresPermitidos)) {
            $tipopago = 'EFECTIVO';
        }

        // Preparar datos
        $datos = [
            'idcliente' => intval($_POST['idcliente']),
            'idconsulta' => intval($_POST['idconsulta']),
            'precio' => floatval($_POST['precio']),
            'tipocomprobante' => $_POST['tipocomprobante'],
            'tipopago' => $tipopago, // Usar el valor normalizado
            'montopagado' => isset($_POST['montopagado']) ? floatval($_POST['montopagado']) : 0,
            'idusuariocaja' => isset($_SESSION['usuario']['idusuario']) ? $_SESSION['usuario']['idusuario'] : 1
        ];

        // Registrar venta
        $resultado = $this->modelo->registrarVenta($datos);

        // Retornar resultado
        echo json_encode($resultado);
    }

    /**
     * Obtiene los datos del comprobante
     */
    private function obtenerComprobante()
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
        if (!isset($_GET['idventa'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Falta el ID de venta'
            ]);
            return;
        }

        $idventa = intval($_GET['idventa']);

        // Obtener datos del comprobante
        $comprobante = $this->modelo->obtenerDatosComprobante($idventa);

        if ($comprobante) {
            echo json_encode([
                'status' => true,
                'data' => $comprobante
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Comprobante no encontrado'
            ]);
        }
    }

    /**
     * Obtiene los datos del comprobante a partir del ID de cita
     */
    private function obtenerComprobantePorCita()
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
        if (!isset($_GET['idcita'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Falta el ID de cita'
            ]);
            return;
        }

        $idcita = intval($_GET['idcita']);

        // Validar que el ID sea válido
        if ($idcita <= 0) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de cita inválido'
            ]);
            return;
        }

        // Registrar para debug
        error_log("Buscando comprobante para cita ID: $idcita");

        // Obtener datos del comprobante
        $comprobante = $this->modelo->obtenerDatosComprobantePorCita($idcita);

        if ($comprobante) {
            error_log("Comprobante encontrado para cita ID: $idcita");
            echo json_encode([
                'status' => true,
                'data' => $comprobante
            ]);
        } else {
            error_log("Comprobante no encontrado para cita ID: $idcita");
            echo json_encode([
                'status' => false,
                'mensaje' => 'No se encontró información de pago para esta cita',
                'idcita' => $idcita
            ]);
        }
    }
}

// Iniciar controlador y procesar solicitud
$controller = new VentaController();
$controller->procesarSolicitud();
