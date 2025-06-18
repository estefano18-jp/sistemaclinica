<?php /*RUTA: sistemaclinica/controllers/historialclinica.controller.php*/?>
<?php
session_start();

require_once '../models/HistoriaClinica.php';

class HistoriaClinicaController
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new HistoriaClinica();
    }

    /**
     * Método para procesar la solicitud según la operación especificada
     */
    public function procesarSolicitud()
    {
        $operacion = isset($_GET['op']) ? $_GET['op'] : '';

        switch ($operacion) {
            case 'registrar_consulta_historia':
                $this->registrarConsultaHistoria();
                break;
            case 'obtener_por_consulta':
                $this->obtenerHistoriaPorConsulta();
                break;
            case 'obtener_historial_paciente':
                $this->obtenerHistorialPaciente();
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
     * Registra una historia clínica para una consulta
     */
    private function registrarConsultaHistoria()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Validar campos obligatorios
        $camposRequeridos = ['idconsulta', 'enfermedadactual', 'examenfisico', 'evolucion', 'iddiagnostico'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => "Campo requerido: {$campo}"
                ]);
                return;
            }
        }

        // Preparar datos
        $datos = [
            'idconsulta' => intval($_POST['idconsulta']),
            'enfermedadactual' => $_POST['enfermedadactual'],
            'examenfisico' => $_POST['examenfisico'],
            'evolucion' => $_POST['evolucion'],
            'iddiagnostico' => intval($_POST['iddiagnostico']),
            'altamedica' => isset($_POST['altamedica']) ? $_POST['altamedica'] : '0'
        ];

        // Registrar historia
        $resultado = $this->modelo->registrarConsultaHistoria($datos);

        // Retornar resultado
        echo json_encode($resultado);
    }

    /**
     * Obtiene la historia clínica para una consulta específica
     */
    private function obtenerHistoriaPorConsulta()
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
        if (!isset($_GET['idconsulta'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de consulta no especificado'
            ]);
            return;
        }

        $idconsulta = intval($_GET['idconsulta']);

        // Obtener historia clínica
        $historia = $this->modelo->obtenerHistoriaPorConsulta($idconsulta);

        if ($historia) {
            echo json_encode([
                'status' => true,
                'data' => $historia
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'No se encontró la historia clínica para esta consulta'
            ]);
        }
    }

    /**
     * Obtiene el historial médico completo de un paciente
     */
    private function obtenerHistorialPaciente()
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
        if (!isset($_GET['idpaciente'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de paciente no especificado'
            ]);
            return;
        }

        $idpaciente = intval($_GET['idpaciente']);

        // Obtener historial
        $historial = $this->modelo->obtenerHistorialPaciente($idpaciente);

        echo json_encode([
            'status' => true,
            'data' => $historial
        ]);
    }
}

// Iniciar controlador y procesar solicitud
$controller = new HistoriaClinicaController();
$controller->procesarSolicitud();
?>