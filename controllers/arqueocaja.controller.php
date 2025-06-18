<?php
session_start();

require_once '../models/ArqueoCaja.php';

class ArqueoCajaController
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new ArqueoCaja();
    }

    public function procesarSolicitud()
    {
        $operacion = isset($_GET['op']) ? $_GET['op'] : '';

        switch ($operacion) {
            case 'verificar_estado':
                $this->verificarEstado();
                break;
            case 'obtener_ultimo_saldo':
                $this->obtenerUltimoSaldo();
                break;
            case 'obtener_hora_ultimo_cierre':
                $this->obtenerHoraUltimoCierre();
                break;
            case 'abrir_caja':
                $this->abrirCaja();
                break;
            case 'actualizar_arqueo':
                $this->actualizarArqueo();
                break;
            case 'cerrar_caja':
                $this->cerrarCaja();
                break;
            case 'listar_ingresos_reservaciones':
                $this->listarIngresosReservaciones();
                break;
            case 'listar_egresos_devoluciones':
                $this->listarEgresosDevoluciones();
                break;
            case 'exportar_pdf':
                $this->exportarPDF();
                break;
            case 'generar_pdf_temp':
                $this->generarPDFTemp();
                break;
            // NUEVAS OPERACIONES PARA EL LISTADO
            case 'listar':
                $this->listar();
                break;
            case 'obtener_detalles':
                $this->obtenerDetalles();
                break;
            case 'actualizar_observaciones':
                $this->actualizarObservaciones();
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
     * Verifica si hay un arqueo abierto
     */
    private function verificarEstado()
    {
        $resultado = $this->modelo->verificarEstado();

        // Registrar para depuración
        error_log("Verificar estado resultado: " . json_encode($resultado));

        echo json_encode($resultado);
    }

    /**
     * Obtiene el saldo del último arqueo
     */
    private function obtenerUltimoSaldo()
    {
        $resultado = $this->modelo->obtenerUltimoSaldo();

        // Registrar para depuración
        error_log("Obtener último saldo resultado: " . json_encode($resultado));

        echo json_encode($resultado);
    }

    /**
     * Obtiene la hora del último cierre de caja
     */
    private function obtenerHoraUltimoCierre()
    {
        $horaCierre = $this->modelo->obtenerHoraUltimoCierre();

        // Registrar para depuración
        error_log("Obtener hora último cierre resultado: " . ($horaCierre ?? 'null'));

        echo json_encode([
            'status' => true,
            'hora_cierre' => $horaCierre
        ]);
    }

    /**
     * FUNCIÓN CORREGIDA: Abre un nuevo arqueo de caja
     */
    private function abrirCaja()
    {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Obtener datos del cuerpo de la solicitud
        $datos = json_decode(file_get_contents('php://input'), true);

        // Validar campos requeridos
        $camposRequeridos = ['fecha_apertura', 'hora_apertura'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo]) || empty($datos[$campo])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => "Falta el campo requerido: {$campo}"
                ]);
                return;
            }
        }

        // CORREGIDO: El modelo se encargará de establecer el monto inicial correcto
        $datos['observaciones'] = isset($datos['observaciones']) ? $datos['observaciones'] : '';

        // Verificar límite de 3 arqueos por día
        $arqueosPorDia = $this->modelo->contarArqueosPorDia($datos['fecha_apertura']);

        if ($arqueosPorDia >= 3) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'No se pueden realizar más de 3 arqueos por día. Límite alcanzado.'
            ]);
            return;
        }

        // Registrar para depuración
        error_log("Datos para abrir caja: " . json_encode($datos));

        // Abrir caja
        $resultado = $this->modelo->abrirCaja($datos);

        // Registrar para depuración
        error_log("Resultado abrir caja: " . json_encode($resultado));

        echo json_encode($resultado);
    }

    /**
     * Actualiza un arqueo existente
     */
    private function actualizarArqueo()
    {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Obtener datos del cuerpo de la solicitud
        $datos = json_decode(file_get_contents('php://input'), true);

        // Validar campos requeridos
        if (!isset($datos['idarqueo']) || empty($datos['idarqueo'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Falta el ID del arqueo'
            ]);
            return;
        }

        // Valores por defecto
        $datos['saldo_a_dejar'] = isset($datos['saldo_a_dejar']) ? floatval($datos['saldo_a_dejar']) : 0;
        $datos['observaciones'] = isset($datos['observaciones']) ? $datos['observaciones'] : '';

        // Registrar para depuración
        error_log("Datos para actualizar arqueo: " . json_encode($datos));

        // Actualizar arqueo
        $resultado = $this->modelo->actualizarArqueo($datos);

        // Registrar para depuración
        error_log("Resultado actualizar arqueo: " . json_encode($resultado));

        echo json_encode($resultado);
    }

    /**
     * Cierra un arqueo existente
     */
    private function cerrarCaja()
    {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Obtener datos del cuerpo de la solicitud
        $datos = json_decode(file_get_contents('php://input'), true);

        // Si no hay datos JSON, intentar obtener de FormData
        if (!$datos) {
            $datos = [];
            foreach ($_POST as $key => $value) {
                $datos[$key] = $value;
            }
        }

        // Registrar datos recibidos para depuración
        error_log("Datos recibidos para cerrar caja: " . json_encode($datos));

        // Validar campos requeridos
        $camposRequeridos = ['idarqueo', 'fecha_cierre', 'hora_cierre', 'saldo_real'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => "Falta el campo requerido: {$campo}"
                ]);
                return;
            }
        }

        // CORREGIDO: Manejar correctamente saldo_a_dejar
        $datos['saldo_a_dejar'] = isset($datos['saldo_a_dejar']) ? floatval($datos['saldo_a_dejar']) : 0;
        $datos['diferencia'] = isset($datos['diferencia']) ? floatval($datos['diferencia']) : 0;
        $datos['observaciones'] = isset($datos['observaciones']) ? $datos['observaciones'] : '';

        // NUEVO: Calcular diferencia automáticamente si no se proporciona
        if ($datos['diferencia'] == 0) {
            $datos['diferencia'] = floatval($datos['saldo_real']) - floatval($datos['saldo_a_dejar']);
        }

        // Registrar para depuración
        error_log("Datos procesados para cerrar caja: " . json_encode($datos));

        // Cerrar caja
        $resultado = $this->modelo->cerrarCaja($datos);

        // Registrar para depuración
        error_log("Resultado cerrar caja: " . json_encode($resultado));

        echo json_encode($resultado);
    }

    /**
     * FUNCIÓN CORREGIDA: Lista los ingresos por reservaciones
     */
    private function listarIngresosReservaciones()
    {
        // Verificar parámetros
        $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
        $idarqueo = isset($_GET['idarqueo']) ? intval($_GET['idarqueo']) : null;
        $horaInicio = isset($_GET['hora_inicio']) ? $_GET['hora_inicio'] : null;

        // CORREGIDO: Validar el formato de hora
        if ($horaInicio && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $horaInicio)) {
            error_log("Formato de hora inválido: $horaInicio");
            $horaInicio = null;
        }

        // CORREGIDO: Si hay idarqueo, NO usar la hora del último cierre
        // porque el modelo se encargará de usar la hora correcta del arqueo específico
        if (!$idarqueo && !$horaInicio) {
            $horaInicio = $this->modelo->obtenerHoraUltimoCierre();
            error_log("No hay arqueo específico ni hora, usando hora del último cierre: " . ($horaInicio ?? 'null'));
        }

        // Registrar para depuración
        error_log("Parámetros listar ingresos: fecha=$fecha, idarqueo=$idarqueo, horaInicio=$horaInicio");

        // IMPORTANTE: El modelo decidirá la hora correcta basado en el idarqueo
        $resultado = $this->modelo->listarIngresosReservaciones($fecha, $idarqueo, $horaInicio);

        // Registrar para depuración
        error_log("Resultado listar ingresos: " . json_encode($resultado));

        echo json_encode($resultado);
    }

    /**
     * FUNCIÓN CORREGIDA: Lista los egresos por devoluciones
     */
    private function listarEgresosDevoluciones()
    {
        // Verificar parámetros
        $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
        $idarqueo = isset($_GET['idarqueo']) ? intval($_GET['idarqueo']) : null;
        $horaInicio = isset($_GET['hora_inicio']) ? $_GET['hora_inicio'] : null;

        // CORREGIDO: Validar el formato de hora
        if ($horaInicio && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $horaInicio)) {
            error_log("Formato de hora inválido: $horaInicio");
            $horaInicio = null;
        }

        // CORREGIDO: Si hay idarqueo, NO usar la hora del último cierre
        // porque el modelo se encargará de usar la hora correcta del arqueo específico
        if (!$idarqueo && !$horaInicio) {
            $horaInicio = $this->modelo->obtenerHoraUltimoCierre();
            error_log("No hay arqueo específico ni hora, usando hora del último cierre: " . ($horaInicio ?? 'null'));
        }

        // Registrar para depuración
        error_log("Parámetros listar egresos: fecha=$fecha, idarqueo=$idarqueo, horaInicio=$horaInicio");

        // IMPORTANTE: El modelo decidirá la hora correcta basado en el idarqueo
        $resultado = $this->modelo->listarEgresosDevoluciones($fecha, $idarqueo, $horaInicio);

        // Registrar para depuración
        error_log("Resultado listar egresos: " . json_encode($resultado));

        echo json_encode($resultado);
    }

    /**
     * NUEVA FUNCIÓN: Lista todos los arqueos con filtros
     */
    private function listar()
    {
        try {
            // Obtener parámetros de filtro
            $fechaDesde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null;
            $fechaHasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null;
            $estado = isset($_GET['estado']) ? $_GET['estado'] : null;
            $idusuario = isset($_GET['idusuario']) ? intval($_GET['idusuario']) : null;

            // Registrar para depuración
            error_log("Parámetros de listado: fechaDesde=$fechaDesde, fechaHasta=$fechaHasta, estado=$estado, idusuario=$idusuario");

            // Obtener datos del modelo
            $resultado = $this->modelo->listarArqueos($fechaDesde, $fechaHasta, $estado, $idusuario);

            // Registrar para depuración
            error_log("Resultado listado arqueos: " . json_encode($resultado));

            echo json_encode($resultado);
        } catch (Exception $e) {
            error_log("Error en listar arqueos: " . $e->getMessage());
            echo json_encode([
                'status' => false,
                'mensaje' => 'Error al listar arqueos: ' . $e->getMessage(),
                'data' => [],
                'resumen' => [
                    'total_arqueos' => 0,
                    'total_cerrados' => 0,
                    'total_abiertos' => 0,
                    'total_saldo' => 0
                ]
            ]);
        }
    }

    /**
     * NUEVA FUNCIÓN: Obtiene detalles completos de un arqueo
     */
    private function obtenerDetalles()
    {
        try {
            // Verificar parámetro requerido
            if (!isset($_GET['idarqueo'])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Falta el ID del arqueo'
                ]);
                return;
            }

            $idarqueo = intval($_GET['idarqueo']);

            // Registrar para depuración
            error_log("Obteniendo detalles para arqueo ID: $idarqueo");

            // Obtener datos del modelo
            $resultado = $this->modelo->obtenerDatosArqueo($idarqueo);

            // Registrar para depuración
            error_log("Resultado obtener detalles: " . json_encode($resultado));

            echo json_encode($resultado);
        } catch (Exception $e) {
            error_log("Error en obtener detalles: " . $e->getMessage());
            echo json_encode([
                'status' => false,
                'mensaje' => 'Error al obtener detalles: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * NUEVA FUNCIÓN: Actualiza las observaciones de un arqueo
     */
    private function actualizarObservaciones()
    {
        try {
            // Verificar método POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Método no válido'
                ]);
                return;
            }

            // Obtener datos (puede ser FormData o JSON)
            $datos = [];
            if (isset($_POST['idarqueo'])) {
                // FormData
                $datos['idarqueo'] = $_POST['idarqueo'];
                $datos['observaciones'] = $_POST['observaciones'] ?? '';
            } else {
                // JSON
                $input = json_decode(file_get_contents('php://input'), true);
                if ($input) {
                    $datos = $input;
                }
            }

            // Validar campos requeridos
            if (!isset($datos['idarqueo']) || empty($datos['idarqueo'])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Falta el ID del arqueo'
                ]);
                return;
            }

            $datos['observaciones'] = isset($datos['observaciones']) ? $datos['observaciones'] : '';

            // Registrar para depuración
            error_log("Datos para actualizar observaciones: " . json_encode($datos));

            // Actualizar en el modelo
            $resultado = $this->modelo->actualizarObservaciones($datos);

            // Registrar para depuración
            error_log("Resultado actualizar observaciones: " . json_encode($resultado));

            echo json_encode($resultado);
        } catch (Exception $e) {
            error_log("Error en actualizar observaciones: " . $e->getMessage());
            echo json_encode([
                'status' => false,
                'mensaje' => 'Error al actualizar observaciones: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Exporta el arqueo a PDF
     */
    private function exportarPDF()
    {
        // Verificar parámetros
        if (!isset($_GET['idarqueo'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Falta el ID del arqueo'
            ]);
            return;
        }

        $idarqueo = intval($_GET['idarqueo']);

        // Registrar para depuración
        error_log("Exportando PDF para arqueo ID: $idarqueo");

        // Obtener datos del arqueo
        $datos = $this->modelo->obtenerDatosArqueo($idarqueo);
        if (!$datos['status']) {
            echo json_encode($datos);
            return;
        }

        // Configurar respuesta como PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="arqueo_' . $idarqueo . '.pdf"');

        // Aquí se implementaría la generación del PDF con una librería como FPDF
        // Por ahora, simulamos la generación devolviendo un JSON
        echo json_encode([
            'status' => true,
            'mensaje' => 'PDF generado correctamente',
            'data' => $datos
        ]);
    }

    /**
     * Genera un PDF temporal sin guardar el arqueo
     */
    private function generarPDFTemp()
    {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Obtener datos del cuerpo de la solicitud
        $datos = json_decode(file_get_contents('php://input'), true);

        // Validar campos requeridos
        $camposRequeridos = ['fecha', 'ingresos', 'egresos'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => "Falta el campo requerido: {$campo}"
                ]);
                return;
            }
        }

        // Registrar para depuración
        error_log("Generando PDF temporal con datos: " . json_encode($datos));

        // Configurar respuesta como PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="arqueo_temp.pdf"');

        // Aquí se implementaría la generación del PDF con una librería como FPDF
        // Por ahora, simulamos la generación devolviendo un JSON
        echo json_encode([
            'status' => true,
            'mensaje' => 'PDF temporal generado correctamente',
            'data' => $datos
        ]);
    }
}

// Iniciar controlador y procesar solicitud
$controller = new ArqueoCajaController();
$controller->procesarSolicitud();