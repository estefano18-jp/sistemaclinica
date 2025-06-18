<?php /*RUTA: sistemaclinica/controllers/devolucion.controller.php*/?>
<?php
session_start();

require_once '../models/Devolucion.php';
require_once '../models/Cita.php';

class DevolucionController
{
    private $modeloDevolucion;
    private $modeloCita;

    public function __construct()
    {
        $this->modeloDevolucion = new Devolucion();
        $this->modeloCita = new Cita();
    }

    /**
     * Método para procesar la solicitud según la operación especificada
     */
    public function procesarSolicitud()
    {
        // Mejorado: Log para depuración de todas las solicitudes entrantes
        error_log("DevolucionController: Solicitud recibida - GET: " . json_encode($_GET) . ", POST: " . json_encode($_POST));

        // CORREGIDO: Detectar operación tanto de GET como de POST
        $operacion = isset($_GET['op']) ? $_GET['op'] : '';
        if (empty($operacion) && isset($_POST['op'])) {
            $operacion = $_POST['op'];
        }

        error_log("DevolucionController: Operación a ejecutar: " . $operacion);

        switch ($operacion) {
            case 'registrar':
                $this->registrarDevolucion();
                break;
            case 'obtener':
                $this->obtenerDevolucion();
                break;
            case 'listar':
                $this->listarDevoluciones();
                break;
            default:
                error_log("DevolucionController: Operación no válida: " . $operacion);
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Operación no válida: ' . $operacion
                ]);
                break;
        }
    }

    /**
     * Registra una nueva devolución
     */
    private function registrarDevolucion()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Log para depuración
        error_log("DevolucionController: Datos recibidos para registrar devolución: " . json_encode($_POST));

        // Validar campos requeridos
        $camposRequeridos = ['idcita', 'monto', 'motivo', 'metodo'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                error_log("DevolucionController: Falta campo requerido: " . $campo);
                echo json_encode([
                    'status' => false,
                    'mensaje' => "Falta el campo requerido: {$campo}"
                ]);
                return;
            }
        }

        // CORREGIDO: Asegurar que idventa sea NULL si está vacío
        $idventa = isset($_POST['idventa']) && !empty($_POST['idventa']) ? intval($_POST['idventa']) : null;

        // Preparar datos
        $datos = [
            'idcita' => intval($_POST['idcita']),
            'idventa' => $idventa,
            'monto' => floatval($_POST['monto']),
            'motivo' => $_POST['motivo'],
            'observaciones' => isset($_POST['observaciones']) ? $_POST['observaciones'] : '',
            'metodo' => $_POST['metodo'],
            'idusuario' => isset($_SESSION['usuario']['idusuario']) ? $_SESSION['usuario']['idusuario'] : 1
        ];

        // Log para depuración
        error_log("DevolucionController: Datos preparados para modelo: " . json_encode($datos));

        // Iniciar transacción
        try {
            // 1. Registrar la devolución
            $resultadoDevolucion = $this->modeloDevolucion->registrar($datos);

            error_log("DevolucionController: Resultado de registro de devolución: " . json_encode($resultadoDevolucion));

            if (!$resultadoDevolucion['status']) {
                throw new Exception($resultadoDevolucion['mensaje']);
            }

            // 2. Cancelar la cita
            $resultadoCancelacion = $this->modeloCita->cancelarCita($datos['idcita']);

            error_log("DevolucionController: Resultado de cancelación de cita: " . json_encode($resultadoCancelacion));

            if (!$resultadoCancelacion['status']) {
                throw new Exception($resultadoCancelacion['mensaje']);
            }

            // Si todo salió bien, retornar éxito
            $respuesta = [
                'status' => true,
                'mensaje' => 'Devolución registrada y cita cancelada correctamente',
                'iddevolucion' => $resultadoDevolucion['iddevolucion'] ?? null,
                'numero_comprobante' => $resultadoDevolucion['numero_comprobante'] ?? null
            ];

            error_log("DevolucionController: Respuesta exitosa: " . json_encode($respuesta));
            echo json_encode($respuesta);
        } catch (Exception $e) {
            // En caso de error, devolver mensaje
            error_log("DevolucionController: Error en procesamiento: " . $e->getMessage());
            error_log("DevolucionController: Traza de error: " . $e->getTraceAsString());

            echo json_encode([
                'status' => false,
                'mensaje' => 'Error al procesar la devolución: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtiene los datos de una devolución específica
     */
    private function obtenerDevolucion()
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
        if (!isset($_GET['id'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Falta el ID de devolución'
            ]);
            return;
        }

        $idDevolucion = intval($_GET['id']);

        // Obtener datos de la devolución
        $devolucion = $this->modeloDevolucion->obtenerPorId($idDevolucion);

        if ($devolucion) {
            echo json_encode([
                'status' => true,
                'data' => $devolucion
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Devolución no encontrada'
            ]);
        }
    }

    /**
     * Lista las devoluciones con filtros opcionales
     */
    private function listarDevoluciones()
    {
        // Mejorado: Log para depuración de todos los parámetros recibidos
        error_log("DevolucionController->listarDevoluciones: Parámetros recibidos - " . json_encode($_GET));

        // Obtener parámetros de filtro con mejor manejo de valores por defecto
        $fechaInicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? $_GET['fecha_inicio'] : date('Y-m-d', strtotime('-30 days'));
        $fechaFin = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? $_GET['fecha_fin'] : date('Y-m-d');
        $documento = isset($_GET['documento']) && $_GET['documento'] !== '' ? $_GET['documento'] : null;
        $metodo = isset($_GET['metodo']) && $_GET['metodo'] !== '' ? $_GET['metodo'] : null;
        $motivo = isset($_GET['motivo']) && $_GET['motivo'] !== '' ? $_GET['motivo'] : null;

        // Log para depuración de los valores procesados
        error_log("DevolucionController->listarDevoluciones: Valores procesados - fechaInicio: $fechaInicio, fechaFin: $fechaFin, documento: $documento, metodo: $metodo, motivo: $motivo");

        // Obtener lista de devoluciones con todos los filtros
        $devoluciones = $this->modeloDevolucion->listar($fechaInicio, $fechaFin, $documento, $metodo, $motivo);

        // Log del resultado
        error_log("DevolucionController->listarDevoluciones: Devoluciones encontradas - " . count($devoluciones));

        echo json_encode([
            'status' => true,
            'data' => $devoluciones
        ]);
    }
}

// Iniciar controlador y procesar solicitud
$controller = new DevolucionController();
$controller->procesarSolicitud();
