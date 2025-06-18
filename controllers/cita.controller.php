<?php /*RUTA: sistemaclinica/controllers/cita.controller.php*/ ?>
<?php
session_start();

require_once '../models/Cita.php';

class CitaController
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new Cita();
    }

    public function procesarSolicitud()
    {
        $operacion = isset($_GET['op']) ? $_GET['op'] : '';

        switch ($operacion) {
            case 'calendario':
                $this->obtenerCitasCalendario();
                break;
            case 'horas_disponibles':
                $this->obtenerHorasDisponibles();
                break;
            case 'registrar':
                $this->registrarCita();
                break;
            case 'listar':
                $this->listarCitas();
                break;
            case 'obtener':
                $this->obtenerCita();
                break;
            case 'actualizar':
                $this->actualizarCita();
                break;
            case 'cancelar':
                $this->cancelarCita();
                break;
            case 'listar_por_dia':
                $this->listarCitasPorDia();
                break;
            case 'buscar_con_filtros':
                $this->buscarCitasConFiltros();
                break;
            case 'actualizar_estado':
                $this->actualizarEstadoCita();
                break;
            case 'debug_calendario':
                $this->debugCalendario();
                break;
            case 'actualizar_citas_no_asistidas':
                $this->actualizarCitasNoAsistidas();
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
     * Lista las citas con filtros opcionales
     */
    private function listarCitas()
    {
        // Obtener parámetros de filtro con mejor manejo de valores vacíos
        $especialidad = isset($_GET['especialidad']) && $_GET['especialidad'] !== '' ? intval($_GET['especialidad']) : null;
        $doctor = isset($_GET['doctor']) && $_GET['doctor'] !== '' ? intval($_GET['doctor']) : null;
        $estado = isset($_GET['estado']) && $_GET['estado'] !== '' ? $_GET['estado'] : null;
        $fechaInicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? $_GET['fecha_inicio'] : date('Y-m-d', strtotime('-30 days'));
        $fechaFin = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? $_GET['fecha_fin'] : date('Y-m-d');
        $fecha = isset($_GET['fecha']) && $_GET['fecha'] !== '' ? $_GET['fecha'] : null;

        // AÑADIDO: Obtener parámetros de nombre/apellido y documento
        $paciente = isset($_GET['paciente']) && $_GET['paciente'] !== '' ? $_GET['paciente'] : null;
        $nrodoc = isset($_GET['nrodoc']) && $_GET['nrodoc'] !== '' ? $_GET['nrodoc'] : null;

        // Si se proporciona una fecha específica, usarla para ambos límites
        if ($fecha) {
            $fechaInicio = $fecha;
            $fechaFin = $fecha;
        }

        // Depuración - Registra los parámetros de filtro
        error_log("Filtros de listado: especialidad=$especialidad, doctor=$doctor, estado=$estado, fechaInicio=$fechaInicio, fechaFin=$fechaFin, fecha=$fecha, paciente=$paciente, nrodoc=$nrodoc");

        // Obtener citas filtradas con los nuevos parámetros
        $citas = $this->modelo->listarCitas($fechaInicio, $fechaFin, $doctor, $especialidad, $estado, $paciente, $nrodoc);

        // Registrar respuesta para depuración
        error_log("Número de citas encontradas para listado: " . count($citas));
        if (count($citas) > 0) {
            error_log("Primera cita encontrada: " . json_encode($citas[0]));
        }

        // Respuesta siempre con formato JSON correcto
        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'data' => $citas
        ]);
    }

    /**
     * Obtiene una cita específica por su ID
     */
    private function obtenerCita()
    {
        // Verificar que exista el ID
        if (!isset($_GET['id'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Falta el ID de la cita'
            ]);
            return;
        }

        $idCita = intval($_GET['id']);

        // Log para depuración
        error_log("Solicitando información de cita ID: $idCita");

        // Obtener la cita
        $cita = $this->modelo->obtenerCita($idCita);

        if ($cita) {
            // Log para depuración
            error_log("Cita encontrada: " . json_encode($cita));

            echo json_encode([
                'status' => true,
                'data' => $cita
            ]);
        } else {
            // Log para depuración
            error_log("Cita no encontrada para ID: $idCita");

            echo json_encode([
                'status' => false,
                'mensaje' => 'Cita no encontrada'
            ]);
        }
    }



    /**
     * Actualiza una cita existente
     */
    private function actualizarCita()
    {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Validar campos requeridos
        $camposRequeridos = ['idcita', 'estado', 'fecha', 'hora'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => "Falta el campo requerido: {$campo}"
                ]);
                return;
            }
        }

        // Preparar datos
        $datos = [
            'idcita' => intval($_POST['idcita']),
            'estado' => $_POST['estado'],
            'fecha' => $_POST['fecha'],
            'hora' => $_POST['hora'],
            'observaciones' => isset($_POST['observaciones']) ? $_POST['observaciones'] : ''
        ];

        // Actualizar cita
        $resultado = $this->modelo->actualizarCita($datos);

        // Retornar resultado
        echo json_encode($resultado);
    }

    /**
     * Cancela una cita existente
     */
    private function cancelarCita()
    {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Validar campo requerido
        if (!isset($_POST['idcita']) || empty($_POST['idcita'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Falta el ID de la cita'
            ]);
            return;
        }

        $idCita = intval($_POST['idcita']);

        // Cancelar cita
        $resultado = $this->modelo->cancelarCita($idCita);

        // Retornar resultado
        echo json_encode($resultado);
    }

    /**
     * Obtiene las citas para mostrar en el calendario
     */
    private function obtenerCitasCalendario()
    {
        // Obtener fechas y filtros
        $fechaInicio = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d');
        $fechaFin = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d', strtotime('+30 days'));
        $iddoctor = isset($_GET['iddoctor']) && $_GET['iddoctor'] ? intval($_GET['iddoctor']) : null;
        $idespecialidad = isset($_GET['idespecialidad']) && $_GET['idespecialidad'] ? intval($_GET['idespecialidad']) : null;

        // Log para depuración
        error_log("Solicitando citas de calendario desde $fechaInicio hasta $fechaFin, doctor: $iddoctor, especialidad: $idespecialidad");

        // Verificar si los valores pasados son "Seleccione Doctor" o "Seleccione Especialidad"
        if ($iddoctor === 0 || $iddoctor === "0" || $iddoctor === null || $iddoctor === "Seleccione Doctor") {
            $iddoctor = null;
        }

        if ($idespecialidad === 0 || $idespecialidad === "0" || $idespecialidad === null || $idespecialidad === "Seleccione Especialidad") {
            $idespecialidad = null;
        }

        // Obtener citas
        $citas = $this->modelo->obtenerCitasCalendario($fechaInicio, $fechaFin, $iddoctor, $idespecialidad);

        // Log para depuración
        error_log("Se encontraron " . count($citas) . " citas para el calendario");

        // Logear los primeros 3 eventos si existen, para depuración
        if (!empty($citas) && count($citas) > 0) {
            $muestra = array_slice($citas, 0, min(3, count($citas)));
            error_log("Muestra de eventos: " . json_encode($muestra));
        }

        // Retornar citas en formato JSON
        header('Content-Type: application/json');
        echo json_encode($citas);
    }

    /**
     * Función de depuración para el calendario
     */
    private function debugCalendario()
    {
        // Obtener todas las citas sin filtros para un rango amplio
        $fechaInicio = date('Y-m-d', strtotime('-3 months'));
        $fechaFin = date('Y-m-d', strtotime('+3 months'));

        error_log("DEBUG: Obteniendo todas las citas desde $fechaInicio hasta $fechaFin");

        $citas = $this->modelo->obtenerCitasCalendario($fechaInicio, $fechaFin, null, null);

        error_log("DEBUG: Total de citas encontradas: " . count($citas));

        // Verificar si hay citas y mostrar información detallada
        if (count($citas) > 0) {
            foreach ($citas as $index => $cita) {
                error_log("DEBUG: Cita #$index - ID: {$cita['id']}, Título: {$cita['title']}, Inicio: {$cita['start']}");
            }
        } else {
            // Si no hay citas, verificar si hay datos en la tabla de citas
            $citasBasicas = $this->modelo->listarCitas($fechaInicio, $fechaFin, null, null, null);
            error_log("DEBUG: Total de citas en listado básico: " . count($citasBasicas));

            if (count($citasBasicas) > 0) {
                error_log("DEBUG: Hay citas en la tabla pero no se están formateando correctamente para el calendario");
            } else {
                error_log("DEBUG: No hay citas en la base de datos para el período especificado");
            }
        }

        echo json_encode([
            'status' => true,
            'debug' => true,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'totalCitas' => count($citas),
            'citas' => $citas
        ]);
    }
    /**
     * Obtiene las citas para un día específico
     */
    private function listarCitasPorDia()
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
        if (!isset($_GET['fecha'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Fecha no especificada'
            ]);
            return;
        }

        // Validar formato de fecha
        $fecha = $_GET['fecha'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Formato de fecha inválido'
            ]);
            return;
        }

        // Capturar la hora actual si se proporciona
        $horaActual = isset($_GET['hora_actual']) ? $_GET['hora_actual'] : date('H:i:s');

        // Obtener ID del doctor si existe en la sesión
        $iddoctor = isset($_SESSION['usuario']['idcolaborador']) ? $_SESSION['usuario']['idcolaborador'] : null;

        // Parámetro de estado opcional
        $estado = isset($_GET['estado']) ? $_GET['estado'] : null;

        // Log para depuración
        error_log("Listando citas por día: fecha=$fecha, hora=$horaActual, doctor=$iddoctor, estado=$estado");

        // Obtener citas (pasar hora actual al modelo)
        $citas = $this->modelo->obtenerCitasPorDia($fecha, $horaActual, $iddoctor, $estado);

        // Retornar resultado
        echo json_encode([
            'status' => true,
            'data' => $citas
        ]);
    }

    /**
     * Busca citas con múltiples filtros
     */
    private function buscarCitasConFiltros()
    {
        // Verificar que se reciba el método GET
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Construir filtros
        $filtros = [];

        // Fecha
        if (isset($_GET['fecha']) && !empty($_GET['fecha'])) {
            $filtros['fecha'] = $_GET['fecha'];
        }

        // Estado
        if (isset($_GET['estado']) && !empty($_GET['estado'])) {
            $filtros['estado'] = $_GET['estado'];
        }

        // Tipo de documento
        if (isset($_GET['tipodoc']) && !empty($_GET['tipodoc'])) {
            $filtros['tipodoc'] = $_GET['tipodoc'];
        }

        // Número de documento
        if (isset($_GET['nrodoc']) && !empty($_GET['nrodoc'])) {
            $filtros['nrodoc'] = $_GET['nrodoc'];
        }

        // Paciente (nombre o apellido)
        if (isset($_GET['paciente']) && !empty($_GET['paciente'])) {
            $filtros['paciente'] = $_GET['paciente'];
        }

        // Obtener ID del doctor si existe en la sesión
        $iddoctor = isset($_SESSION['usuario']['idcolaborador']) ? $_SESSION['usuario']['idcolaborador'] : null;

        // Obtener citas
        $citas = $this->modelo->buscarCitasConFiltros($filtros, $iddoctor);

        // Retornar resultado
        echo json_encode([
            'status' => true,
            'data' => $citas
        ]);
    }

    /**
     * Actualiza el estado de una cita
     */
    private function actualizarEstadoCita()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Validar parámetros
        if (!isset($_POST['idcita']) || !isset($_POST['estado'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Faltan parámetros obligatorios'
            ]);
            return;
        }

        $idcita = intval($_POST['idcita']);
        $estado = $_POST['estado'];

        // Actualizar estado
        $resultado = $this->modelo->actualizarEstadoCita($idcita, $estado);

        // Retornar resultado
        echo json_encode([
            'status' => $resultado,
            'mensaje' => $resultado
                ? 'Estado de cita actualizado correctamente'
                : 'Error al actualizar el estado de la cita'
        ]);
    }
    /**
     * Obtiene las horas disponibles para un doctor en una fecha
     */
    private function obtenerHorasDisponibles()
    {
        // Validar parámetros requeridos
        if (!isset($_GET['iddoctor']) || !isset($_GET['fecha']) || !isset($_GET['idespecialidad'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Faltan parámetros requeridos'
            ]);
            return;
        }

        $iddoctor = intval($_GET['iddoctor']);
        $fecha = $_GET['fecha'];
        $idespecialidad = intval($_GET['idespecialidad']);

        // Validar formato de fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Formato de fecha inválido'
            ]);
            return;
        }

        // Obtener horas disponibles
        $horasDisponibles = $this->modelo->obtenerHorasDisponibles($iddoctor, $fecha, $idespecialidad);

        echo json_encode([
            'status' => true,
            'data' => $horasDisponibles
        ]);
    }

    /**
     * Registra una nueva cita
     */
    private function registrarCita()
    {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Validar campos requeridos
        $camposRequeridos = ['iddoctor', 'idespecialidad', 'fecha', 'hora', 'idpaciente'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => "Falta el campo requerido: {$campo}"
                ]);
                return;
            }
        }

        // Preparar datos
        $datos = [
            'iddoctor' => intval($_POST['iddoctor']),
            'idespecialidad' => intval($_POST['idespecialidad']),
            'fecha' => $_POST['fecha'],
            'hora' => $_POST['hora'],
            'idpaciente' => intval($_POST['idpaciente']),
            'observaciones' => isset($_POST['observaciones']) ? $_POST['observaciones'] : '',
            'condicionpaciente' => isset($_POST['condicionpaciente']) ? $_POST['condicionpaciente'] : 'NUEVO'
        ];

        // Registrar cita
        $resultado = $this->modelo->registrarCita($datos);

        // Log para depuración
        error_log("Resultado del registro de cita: " . json_encode($resultado));

        // Retornar resultado
        echo json_encode($resultado);
    }

    /**
     * Actualiza automáticamente el estado de citas no asistidas
     */
    private function actualizarCitasNoAsistidas()
    {
        // Obtener fecha y hora actuales o las proporcionadas en la URL
        $fechaHoy = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
        $horaActual = isset($_GET['hora']) ? $_GET['hora'] : date('H:i:s');

        // Registrar valores para depuración
        error_log("Actualizando citas no asistidas: fecha=$fechaHoy, hora=$horaActual");

        // Actualizar citas que ya pasaron
        $citasActualizadas = $this->modelo->actualizarCitasNoAsistidas($fechaHoy, $horaActual);

        // Devolver número de filas afectadas
        echo json_encode([
            'status' => true,
            'actualizadas' => $citasActualizadas
        ]);
    }
}

// Inicializar controlador y procesar solicitud
$controller = new CitaController();
$controller->procesarSolicitud();
