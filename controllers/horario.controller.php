<?php /*RUTA: sistemaclinica/controllers/horario.controller.php*/ ?>
<?php

require_once '../models/Horario.php';

class HorarioController
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new Horario();
    }

    /**
     * Registra un nuevo horario para un colaborador
     */
    public function registrarHorario()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Verificar qué tipo de datos estamos recibiendo
            if (isset($_POST['idatencion']) && intval($_POST['idatencion']) > 0) {
                // Formato original con idatencion
                $datos = [
                    'idatencion' => isset($_POST['idatencion']) ? intval($_POST['idatencion']) : 0,
                    'horainicio' => isset($_POST['horainicio']) ? $_POST['horainicio'] : '',
                    'horafin' => isset($_POST['horafin']) ? $_POST['horafin'] : ''
                ];

                // Validar campos obligatorios
                if ($datos['idatencion'] <= 0) {
                    echo json_encode([
                        'status' => false,
                        'mensaje' => 'El ID de atención es requerido o no válido'
                    ]);
                    return;
                }

                if (empty($datos['horainicio'])) {
                    echo json_encode([
                        'status' => false,
                        'mensaje' => 'La hora de inicio es requerida'
                    ]);
                    return;
                }

                if (empty($datos['horafin'])) {
                    echo json_encode([
                        'status' => false,
                        'mensaje' => 'La hora de fin es requerida'
                    ]);
                    return;
                }

                // Registrar horario - Se maneja internamente en el modelo
                $resultado = $this->modelo->registrarHorarioSimple($datos);
            } else {
                // Formato alternativo con idcolaborador, idcontrato, diasemana
                $datos = [
                    'idcolaborador' => isset($_POST['idcolaborador']) ? intval($_POST['idcolaborador']) : 0,
                    'idcontrato' => isset($_POST['idcontrato']) ? intval($_POST['idcontrato']) : 0,
                    'dia' => isset($_POST['diasemana']) ? $_POST['diasemana'] : '',
                    'horainicio' => isset($_POST['horainicio']) ? $_POST['horainicio'] : '',
                    'horafin' => isset($_POST['horafin']) ? $_POST['horafin'] : ''
                ];

                // Validar campos obligatorios para el formato alternativo
                if ($datos['idcolaborador'] <= 0) {
                    echo json_encode([
                        'status' => false,
                        'mensaje' => 'El ID de colaborador es requerido o no válido'
                    ]);
                    return;
                }

                if (empty($datos['dia'])) {
                    echo json_encode([
                        'status' => false,
                        'mensaje' => 'El día de atención es requerido'
                    ]);
                    return;
                }

                if (empty($datos['horainicio'])) {
                    echo json_encode([
                        'status' => false,
                        'mensaje' => 'La hora de inicio es requerida'
                    ]);
                    return;
                }

                if (empty($datos['horafin'])) {
                    echo json_encode([
                        'status' => false,
                        'mensaje' => 'La hora de fin es requerida'
                    ]);
                    return;
                }

                // Registrar horario usando el método alternativo
                $resultado = $this->modelo->registrarHorarioAlternativo($datos);
            }

            echo json_encode($resultado);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método de solicitud no válido'
            ]);
        }
    }
    /**
     * Obtiene los horarios de un colaborador específico
     */
    public function obtenerHorariosPorColaborador()
    {
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

            // Obtener horarios
            $horarios = $this->modelo->obtenerHorariosPorColaborador($idColaborador);

            echo json_encode([
                'status' => true,
                'data' => $horarios
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método de solicitud no válido'
            ]);
        }
    }

    /**
     * Obtiene información de un horario específico
     */
    public function obtenerHorario()
    {
        // Verificar que se reciba el método GET
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // Capturar ID del horario
            $idHorario = isset($_GET['id']) ? intval($_GET['id']) : 0;

            if ($idHorario <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de horario no válido'
                ]);
                return;
            }

            // Obtener datos del horario
            $horario = $this->modelo->obtenerHorarioPorId($idHorario);

            if ($horario) {
                echo json_encode([
                    'status' => true,
                    'data' => $horario
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Horario no encontrado'
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
     * Actualiza los horarios de atención de un colaborador
     */
    public function actualizarHorarios()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Verificar que tengamos el ID del colaborador
            $idColaborador = isset($_POST['idcolaborador']) ? intval($_POST['idcolaborador']) : 0;

            if ($idColaborador <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de colaborador no válido'
                ]);
                return;
            }

            // Verificar si hay datos de horarios activos
            if (!isset($_POST['horarios_activos'])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'No se recibieron datos de horarios'
                ]);
                return;
            }

            // Decodificar los horarios activos
            $horariosActivos = json_decode($_POST['horarios_activos'], true);

            if (empty($horariosActivos)) {
                // Si no hay horarios activos, es válido pero devolvemos un aviso
                echo json_encode([
                    'status' => true,
                    'mensaje' => 'No hay horarios configurados para el doctor'
                ]);
                return;
            }

            // Procesar cada horario activo
            $resultados = [];
            $errores = [];

            foreach ($horariosActivos as $dia => $datosHorario) {
                // Preparar datos para el modelo
                $datos = [
                    'idcolaborador' => $idColaborador,
                    'dia' => $dia,
                    'horainicio' => $datosHorario['horainicio'],
                    'horafin' => $datosHorario['horafin'],
                    'intervalo' => $datosHorario['intervalo'] ?? '30'
                ];

                // Validar datos básicos
                if (empty($datos['horainicio']) || empty($datos['horafin'])) {
                    $errores[] = "Horario incompleto para el día " . $dia;
                    continue;
                }

                // Validar que la hora de fin sea posterior a la hora de inicio
                if ($datos['horafin'] <= $datos['horainicio']) {
                    $errores[] = "La hora de fin debe ser posterior a la hora de inicio en el día " . $dia;
                    continue;
                }

                // Registrar el horario
                $resultado = $this->modelo->registrarHorario($datos);

                if ($resultado['status']) {
                    $resultados[] = $resultado;
                } else {
                    $errores[] = $resultado['mensaje'] . " (Día " . $dia . ")";
                }
            }

            // Determinar resultado general
            if (empty($errores)) {
                echo json_encode([
                    'status' => true,
                    'mensaje' => 'Horarios actualizados correctamente',
                    'detalles' => $resultados
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Se encontraron errores al actualizar los horarios: ' . implode(', ', $errores),
                    'detalles' => $resultados
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
     * Obtiene los días y horarios de atención de un doctor
     */
    public function obtenerHorariosPorDoctor()
    {
        // Verificar que se reciba el método GET
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // Capturar ID del doctor
            $idColaborador = isset($_GET['idcolaborador']) ? intval($_GET['idcolaborador']) : 0;

            if ($idColaborador <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de doctor no válido'
                ]);
                return;
            }

            // Obtener horarios
            $horarios = $this->modelo->obtenerHorariosPorDoctor($idColaborador);

            echo json_encode([
                'status' => true,
                'data' => $horarios
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método de solicitud no válido'
            ]);
        }
    }

    /**
     * Obtiene las horas disponibles para un doctor en una fecha específica
     */
    public function obtenerHorasDisponiblesPorFecha()
    {
        // Verificar que se reciba el método GET
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // Capturar parámetros
            $idColaborador = isset($_GET['idcolaborador']) ? intval($_GET['idcolaborador']) : 0;
            $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';

            if ($idColaborador <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de doctor no válido'
                ]);
                return;
            }

            if (empty($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Fecha no válida. Use el formato YYYY-MM-DD'
                ]);
                return;
            }

            // Verificar que la fecha no sea anterior a hoy
            $hoy = date('Y-m-d');
            if ($fecha < $hoy) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'No se pueden consultar fechas pasadas'
                ]);
                return;
            }

            // Obtener horarios disponibles
            $horarios = $this->modelo->obtenerHorasDisponiblesPorFecha($idColaborador, $fecha);

            echo json_encode([
                'status' => true,
                'data' => $horarios
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método de solicitud no válido'
            ]);
        }
    }
    /**
     * Actualiza un horario existente
     */
    public function actualizarHorario()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Capturar datos del formulario
            $datos = [
                'idhorario' => isset($_POST['idhorario']) ? intval($_POST['idhorario']) : 0,
                'idatencion' => isset($_POST['idatencion']) ? intval($_POST['idatencion']) : 0,
                'diasemana' => isset($_POST['diasemana']) ? $_POST['diasemana'] : '',
                'horainicio' => isset($_POST['horainicio']) ? $_POST['horainicio'] : '',
                'horafin' => isset($_POST['horafin']) ? $_POST['horafin'] : ''
            ];

            // Validar campos obligatorios
            if ($datos['idhorario'] <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de horario no válido'
                ]);
                return;
            }

            if (empty($datos['horainicio']) || empty($datos['horafin'])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Las horas de inicio y fin son requeridas'
                ]);
                return;
            }

            // Actualizar horario
            $resultado = $this->modelo->actualizarHorario($datos);

            echo json_encode($resultado);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método de solicitud no válido'
            ]);
        }
    }

    /**
     * Elimina un horario existente
     */
    public function eliminarHorario()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Capturar ID del horario
            $idHorario = isset($_POST['idhorario']) ? intval($_POST['idhorario']) : 0;

            if ($idHorario <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de horario no válido'
                ]);
                return;
            }

            // Eliminar horario
            $resultado = $this->modelo->eliminarHorario($idHorario);

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
// Procesar la solicitud
if (isset($_GET['op'])) {
    $controller = new HorarioController();

    switch ($_GET['op']) {
        case 'registrar':
            $controller->registrarHorario();
            break;
        case 'listar':
            $controller->obtenerHorariosPorColaborador();
            break;
        case 'obtener':
            $controller->obtenerHorario();
            break;
        case 'actualizar':
            $controller->actualizarHorario();
            break;
        case 'eliminar':
            $controller->eliminarHorario();
            break;
        case 'horarios_doctor':
            $controller->obtenerHorariosPorDoctor();
            break;
        case 'horarios_disponibles':
            $controller->obtenerHorasDisponiblesPorFecha();
            break;
        default:
            echo json_encode([
                'status' => false,
                'mensaje' => 'Operación no válida'
            ]);
            break;
    }
}
