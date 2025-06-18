<?php /*RUTA: sistemaclinica/controllers/paciente.controller.php*/?>
<?php
session_start();

require_once '../models/Paciente.php';

/**
 * Controlador para gestionar operaciones relacionadas con pacientes
 */
class PacienteController
{
    private $modelo;

    /**
     * Constructor que inicializa el modelo de pacientes
     */
    public function __construct()
    {
        $this->modelo = new Paciente();
    }

    /**
     * Método para procesar la solicitud según la operación especificada
     */
    public function procesarSolicitud()
    {
        // Operaciones por GET
        if (isset($_GET['operacion'])) {
            switch ($_GET['operacion']) {
                case 'listar':
                    $this->listarPacientes();
                    break;
                case 'obtener':
                    $this->obtenerPacientePorId();
                    break;
                case 'verificar_documento':
                    $this->verificarDocumento();
                    break;
                case 'buscar_por_documento':
                    $this->buscarPorDocumento();
                    break;
                case 'eliminar':
                    $this->eliminarPaciente();
                    break;
            }
        }
        // Si hay un parámetro 'genero' sin operación específica, asumimos que es para listar
        elseif (isset($_GET['genero'])) {
            $this->listarPacientes();
        }

        // Operaciones por POST
        if (isset($_POST['operacion'])) {
            switch ($_POST['operacion']) {
                case 'registrar':
                    $this->registrarPaciente();
                    break;
                case 'actualizar':
                    $this->actualizarPaciente();
                    break;
                case 'eliminar':
                    $this->eliminarPaciente();
                    break;
            }
        }
    }

    /**
     * Método para listar pacientes con filtros opcionales
     */
    private function listarPacientes()
    {
        $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : null;
        $estado = isset($_GET['estado']) ? $_GET['estado'] : null;
        $genero = isset($_GET['genero']) ? $_GET['genero'] : null;

        try {
            $pacientes = $this->modelo->listar($busqueda, $estado, $genero);

            // Añadir depuración
            error_log("Pacientes obtenidos: " . json_encode($pacientes));

            // Respuesta correctamente formateada
            echo json_encode([
                'status' => true,
                'data' => $pacientes, // Usar 'data' para mantener consistencia con el código JavaScript
                'pacientes' => $pacientes, // También enviar como 'pacientes' para compatibilidad
                'mensaje' => 'Pacientes cargados correctamente'
            ]);
        } catch (Exception $e) {
            // Registrar el error para depuración
            error_log("Error al listar pacientes: " . $e->getMessage());

            // Respuesta de error
            echo json_encode([
                'status' => false,
                'mensaje' => 'Error al listar pacientes: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * Método para obtener datos de un paciente por su ID
     */
    private function obtenerPacientePorId()
    {
        if (isset($_GET['idpaciente'])) {
            $paciente = $this->modelo->obtenerPorId($_GET['idpaciente']);

            if (!empty($paciente)) {
                echo json_encode([
                    'status' => true,
                    'paciente' => $paciente
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Paciente no encontrado'
                ]);
            }
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de paciente no especificado'
            ]);
        }
    }

    /**
     * Método para verificar si un documento ya está registrado como paciente
     */
    private function verificarDocumento()
    {
        if (isset($_GET['nrodoc'])) {
            $existe = $this->modelo->verificarDocumentoPaciente($_GET['nrodoc']);

            echo json_encode([
                'status' => true,
                'existe' => $existe,
                'mensaje' => $existe ? 'El documento ya está registrado como paciente' : 'Documento disponible'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Número de documento no especificado'
            ]);
        }
    }

    /**
     * Método para buscar un paciente por su número de documento
     */
    private function buscarPorDocumento()
    {
        if (isset($_GET['nrodoc'])) {
            $paciente = $this->modelo->buscarPorDocumento($_GET['nrodoc']);

            if (!empty($paciente)) {
                echo json_encode([
                    'status' => true,
                    'paciente' => $paciente
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'No se encontró ningún paciente con ese documento'
                ]);
            }
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Número de documento no especificado'
            ]);
        }
    }

    /**
     * Método para registrar un nuevo paciente
     */
    private function registrarPaciente()
    {
        $datosObligatorios = [
            'apellidos',
            'nombres',
            'tipodoc',
            'nrodoc',
            'telefono',
            'fechanacimiento',
            'genero'
        ];

        // Debug: Ver qué datos están llegando
        error_log("Datos recibidos para registro de paciente: " . print_r($_POST, true));

        $faltanDatos = false;
        $camposFaltantes = [];
        foreach ($datosObligatorios as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                $faltanDatos = true;
                $camposFaltantes[] = $campo;
            }
        }

        if ($faltanDatos) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Faltan datos obligatorios para el registro: ' . implode(', ', $camposFaltantes)
            ]);
            return;
        }

        // Verificar que el documento no esté registrado ya como paciente
        if ($this->modelo->verificarDocumentoPaciente($_POST['nrodoc'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'El documento ya está registrado como paciente'
            ]);
            return;
        }

        $params = [
            'apellidos' => $_POST['apellidos'],
            'nombres' => $_POST['nombres'],
            'tipodoc' => $_POST['tipodoc'],
            'nrodoc' => $_POST['nrodoc'],
            'telefono' => $_POST['telefono'],
            'fechanacimiento' => $_POST['fechanacimiento'],
            'genero' => $_POST['genero'],
            'direccion' => isset($_POST['direccion']) ? $_POST['direccion'] : '',
            'email' => isset($_POST['email']) ? $_POST['email'] : ''
        ];

        $resultado = $this->modelo->registrar($params);

        echo json_encode([
            'status' => $resultado['resultado'] == 1,
            'mensaje' => $resultado['mensaje']
        ]);
    }

    /**
     * Método para actualizar datos de un paciente
     */
    private function actualizarPaciente()
    {
        $datosObligatorios = [
            'idpaciente',
            'apellidos',
            'nombres',
            'tipodoc',
            'nrodoc',
            'telefono',
            'fechanacimiento',
            'genero'
        ];

        $faltanDatos = false;
        foreach ($datosObligatorios as $campo) {
            if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                $faltanDatos = true;
                break;
            }
        }

        if ($faltanDatos) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Faltan datos obligatorios para la actualización'
            ]);
            return;
        }

        $params = [
            'idpaciente' => $_POST['idpaciente'],
            'apellidos' => $_POST['apellidos'],
            'nombres' => $_POST['nombres'],
            'tipodoc' => $_POST['tipodoc'],
            'nrodoc' => $_POST['nrodoc'],
            'telefono' => $_POST['telefono'],
            'fechanacimiento' => $_POST['fechanacimiento'],
            'genero' => $_POST['genero'],
            'direccion' => isset($_POST['direccion']) ? $_POST['direccion'] : '',
            'email' => isset($_POST['email']) ? $_POST['email'] : ''
        ];

        $resultado = $this->modelo->actualizar($params);

        echo json_encode([
            'status' => $resultado['resultado'] == 1,
            'mensaje' => $resultado['mensaje']
        ]);
    }

    /**
     * Método para eliminar un paciente y sus alergias asociadas
     */
    private function eliminarPaciente()
    {
        header('Content-Type: application/json');

        try {
            if (isset($_GET['idpaciente']) || isset($_POST['idpaciente'])) {
                $idpaciente = isset($_GET['idpaciente']) ? $_GET['idpaciente'] : $_POST['idpaciente'];

                error_log("Controlador: Recibida solicitud para eliminar paciente ID: $idpaciente");

                // Validar que el ID sea numérico
                if (!is_numeric($idpaciente)) {
                    error_log("Controlador: ID de paciente inválido: $idpaciente");
                    echo json_encode([
                        'status' => false,
                        'mensaje' => 'ID de paciente inválido'
                    ]);
                    return;
                }

                // Obtener el resultado de la operación
                $resultado = $this->modelo->eliminar($idpaciente);

                error_log("Controlador: Resultado de eliminación: " . json_encode($resultado));

                // Asegurarse de que el resultado tiene el formato esperado
                $status = isset($resultado['resultado']) && intval($resultado['resultado']) == 1;
                $mensaje = isset($resultado['mensaje']) ? $resultado['mensaje'] : 'Error al eliminar el paciente';

                echo json_encode([
                    'status' => $status,
                    'mensaje' => $mensaje
                ]);
            } else {
                error_log("Controlador: No se proporcionó ID de paciente");
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de paciente no especificado'
                ]);
            }
        } catch (Exception $e) {
            error_log("Controlador: Error no controlado: " . $e->getMessage());
            echo json_encode([
                'status' => false,
                'mensaje' => 'Error inesperado: ' . $e->getMessage()
            ]);
        }
    }
}

// Iniciar el controlador y procesar la solicitud
$controller = new PacienteController();
$controller->procesarSolicitud();