<?php /*RUTA: sistemaclinica/controllers/alergia.controller.php*/?>
<?php
session_start();

require_once '../models/Alergia.php';

/**
 * Controlador para gestionar operaciones relacionadas con alergias
 */
class AlergiaController {
    
    private $modelo;
    
    /**
     * Constructor que inicializa el modelo de alergias
     */
    public function __construct() {
        $this->modelo = new Alergia();
    }
    
    /**
     * Método para procesar la solicitud según la operación especificada
     */
    public function procesarSolicitud() {
        // Operaciones por GET
        if (isset($_GET['operacion'])) {
            switch ($_GET['operacion']) {
                case 'listar':
                    $this->listarAlergias();
                    break;
                case 'listar_por_tipo':
                    $this->listarAlergiasPorTipo();
                    break;
                case 'listar_paciente':
                    $this->listarAlergiasPaciente();
                    break;
                case 'obtener':
                    $this->obtenerAlergiaPorId();
                    break;
                case 'buscar':
                    $this->buscarAlergias();
                    break;
                case 'eliminar':
                    $this->eliminarAlergia();
                    break;
                case 'eliminar_paciente':
                    $this->eliminarAlergiaPaciente();
                    break;
                case 'verificar_existe_alergia':
                    $this->verificarExisteAlergia();
                    break;
                case 'eliminar_por_persona':
                    $this->eliminarAlergiasPorPersona();
                    break;
                case 'eliminar_todas':
                    $this->eliminarTodasAlergiasDePersona();
                    break;
                case 'limpiar_huerfanas':
                    $this->limpiarAlergiasHuerfanas();
                    break;
                case 'eliminar_completa':
                    $this->eliminarAlergiaCompleta();
                    break;
            }
        }
        
        // Operaciones por POST
        if (isset($_POST['operacion'])) {
            switch ($_POST['operacion']) {
                case 'registrar':
                    $this->registrarAlergia();
                    break;
                case 'registrar_paciente':
                    $this->registrarAlergiaAPaciente();
                    break;
                case 'actualizar':
                    $this->actualizarAlergia();
                    break;
                case 'actualizar_gravedad':
                    $this->actualizarGravedad();
                    break;
                case 'eliminar':
                    $this->eliminarAlergia();
                    break;
                case 'eliminar_paciente':
                    $this->eliminarAlergiaPaciente();
                    break;
            }
        }
    }

    /**
     * Método para eliminar una alergia completamente
     */
    private function eliminarAlergiaCompleta()
    {
        if (isset($_GET['idlistaalergia'])) {
            $idListaAlergia = filter_var($_GET['idlistaalergia'], FILTER_VALIDATE_INT);
            if ($idListaAlergia === false) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de lista de alergia inválido'
                ]);
                return;
            }
            
            $resultado = $this->modelo->eliminarAlergiaCompleta($idListaAlergia);
            echo json_encode([
                'status' => $resultado['eliminado'] == 1,
                'mensaje' => $resultado['mensaje'],
                'idalergia' => $resultado['idalergia'] ?? null
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de lista de alergia no especificado'
            ]);
        }
    }

    /**
     * Método para limpiar alergias huérfanas (que no están asociadas a ningún paciente)
     */
    private function limpiarAlergiasHuerfanas()
    {
        $resultado = $this->modelo->limpiarAlergiasHuerfanas();
        echo json_encode($resultado);
    }

    /**
     * Método para verificar si una alergia ya existe para un paciente
     */
    private function verificarExisteAlergia()
    {
        if (isset($_GET['idpersona']) && isset($_GET['tipoalergia']) && isset($_GET['alergia'])) {
            // Validar y sanitizar entradas
            $idPersona = filter_var($_GET['idpersona'], FILTER_VALIDATE_INT);
            if ($idPersona === false) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de persona inválido'
                ]);
                return;
            }
            
            // Decodificar los parámetros en caso de que vengan URL-encoded
            $tipoalergia = urldecode(trim($_GET['tipoalergia']));
            $alergia = urldecode(trim($_GET['alergia']));
            
            // Validar que no estén vacíos
            if (empty($tipoalergia) || empty($alergia)) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Tipo de alergia o nombre de alergia vacío'
                ]);
                return;
            }
            
            $existe = $this->modelo->verificarAlergiaExistente(
                $idPersona,
                $tipoalergia,
                $alergia
            );

            echo json_encode([
                'status' => true,
                'existe' => $existe,
                'mensaje' => $existe ? 'La alergia ya está registrada para este paciente' : 'Alergia disponible'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Faltan parámetros requeridos'
            ]);
        }
    }

    /**
     * Método para eliminar todas las alergias de una persona específica
     */
    private function eliminarTodasAlergiasDePersona()
    {
        if (isset($_GET['idpersona'])) {
            $idPersona = filter_var($_GET['idpersona'], FILTER_VALIDATE_INT);
            if ($idPersona === false) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de persona inválido'
                ]);
                return;
            }
            
            $resultado = $this->modelo->eliminarTodasAlergiasPaciente($idPersona);

            echo json_encode([
                'status' => $resultado['status'] ?? false,
                'eliminados' => $resultado['eliminados'] ?? 0,
                'mensaje' => $resultado['mensaje'] ?? 'Alergias del paciente eliminadas correctamente'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de persona no especificado'
            ]);
        }
    }

    /**
     * Método para eliminar todas las alergias de una persona
     */
    private function eliminarAlergiasPorPersona()
    {
        if (isset($_GET['idpersona'])) {
            $idPersona = filter_var($_GET['idpersona'], FILTER_VALIDATE_INT);
            if ($idPersona === false) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de persona inválido'
                ]);
                return;
            }
            
            $eliminados = $this->modelo->eliminarAlergiasPorPersona($idPersona);

            echo json_encode([
                'status' => true,
                'eliminados' => $eliminados,
                'mensaje' => $eliminados > 0 ? 'Alergias eliminadas correctamente' : 'No se encontraron alergias para eliminar'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de persona no especificado'
            ]);
        }
    }
    
    /**
     * Método para listar todas las alergias
     */
    private function listarAlergias() {
        $alergias = $this->modelo->listar();
        echo json_encode([
            'status' => true,
            'alergias' => $alergias
        ]);
    }
    
    /**
     * Método para listar alergias por tipo
     */
    private function listarAlergiasPorTipo() {
        if (isset($_GET['tipoalergia'])) {
            $tipoAlergia = trim($_GET['tipoalergia']);
            if (empty($tipoAlergia)) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Tipo de alergia vacío'
                ]);
                return;
            }
            
            $alergias = $this->modelo->listarPorTipo($tipoAlergia);
            echo json_encode([
                'status' => true,
                'alergias' => $alergias
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Tipo de alergia no especificado'
            ]);
        }
    }
    
    /**
     * Método para listar alergias de un paciente específico
     */
    private function listarAlergiasPaciente() {
        if (isset($_GET['idpersona'])) {
            $idPersona = filter_var($_GET['idpersona'], FILTER_VALIDATE_INT);
            if ($idPersona === false) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de persona inválido'
                ]);
                return;
            }
            
            $alergias = $this->modelo->listarAlergiasPaciente($idPersona);
            echo json_encode([
                'status' => true,
                'alergias' => $alergias
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de persona no especificado'
            ]);
        }
    }
    
    /**
     * Método para obtener una alergia por su ID
     */
    private function obtenerAlergiaPorId() {
        if (isset($_GET['idalergia'])) {
            $idAlergia = filter_var($_GET['idalergia'], FILTER_VALIDATE_INT);
            if ($idAlergia === false) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de alergia inválido'
                ]);
                return;
            }
            
            $alergia = $this->modelo->obtenerPorId($idAlergia);
            if (!empty($alergia)) {
                echo json_encode([
                    'status' => true,
                    'alergia' => $alergia
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Alergia no encontrada'
                ]);
            }
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de alergia no especificado'
            ]);
        }
    }
    
    /**
     * Método para buscar alergias por nombre o tipo
     */
    private function buscarAlergias() {
        if (isset($_GET['busqueda'])) {
            $busqueda = trim($_GET['busqueda']);
            $alergias = $this->modelo->buscar($busqueda);
            echo json_encode([
                'status' => true,
                'alergias' => $alergias
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Término de búsqueda no especificado'
            ]);
        }
    }
    
    /**
     * Método para registrar una nueva alergia
     */
    private function registrarAlergia() {
        $resultado = [
            'status' => false,
            'mensaje' => ''
        ];
        
        if (isset($_POST['tipoalergia']) && isset($_POST['alergia'])) {
            $tipoAlergia = trim($_POST['tipoalergia']);
            $alergia = trim($_POST['alergia']);
            
            if (empty($tipoAlergia) || empty($alergia)) {
                $resultado['mensaje'] = 'Tipo de alergia o nombre de alergia vacío';
                echo json_encode($resultado);
                return;
            }
            
            $datos = [
                'tipoalergia' => $tipoAlergia,
                'alergia' => $alergia
            ];
            
            $idalergia = $this->modelo->registrar($datos);
            
            if ($idalergia > 0) {
                $resultado['status'] = true;
                $resultado['mensaje'] = 'Alergia registrada correctamente';
                $resultado['idalergia'] = $idalergia;
            } else {
                $resultado['mensaje'] = 'Error al registrar la alergia';
            }
        } else {
            $resultado['mensaje'] = 'Faltan datos obligatorios';
        }
        
        echo json_encode($resultado);
    }
    
    /**
     * Método para registrar una alergia a un paciente
     */
    private function registrarAlergiaAPaciente() {
        $resultado = [
            'status' => false,
            'mensaje' => ''
        ];
        
        if (isset($_POST['idpersona']) && isset($_POST['idalergia']) && isset($_POST['gravedad'])) {
            $idPersona = filter_var($_POST['idpersona'], FILTER_VALIDATE_INT);
            $idAlergia = filter_var($_POST['idalergia'], FILTER_VALIDATE_INT);
            $gravedad = trim($_POST['gravedad']);
            
            if ($idPersona === false || $idAlergia === false || empty($gravedad)) {
                $resultado['mensaje'] = 'Datos inválidos o incompletos';
                echo json_encode($resultado);
                return;
            }
            
            $datos = [
                'idpersona' => $idPersona,
                'idalergia' => $idAlergia,
                'gravedad' => $gravedad
            ];
            
            $idlistaalergia = $this->modelo->registrarAlergiaAPaciente($datos);
            
            if ($idlistaalergia > 0) {
                $resultado['status'] = true;
                $resultado['mensaje'] = 'Alergia asignada al paciente correctamente';
                $resultado['idlistaalergia'] = $idlistaalergia;
            } else if ($idlistaalergia == -2) {
                $resultado['mensaje'] = 'Esta alergia ya está registrada para este paciente';
            } else {
                $resultado['mensaje'] = 'Error al asignar la alergia al paciente';
            }
        } else {
            $resultado['mensaje'] = 'Faltan datos obligatorios';
        }
        
        echo json_encode($resultado);
    }
    
    /**
     * Método para actualizar una alergia existente
     */
    private function actualizarAlergia() {
        $resultado = [
            'status' => false,
            'mensaje' => ''
        ];
        
        if (isset($_POST['idalergia']) && isset($_POST['tipoalergia']) && isset($_POST['alergia'])) {
            $idAlergia = filter_var($_POST['idalergia'], FILTER_VALIDATE_INT);
            $tipoAlergia = trim($_POST['tipoalergia']);
            $alergia = trim($_POST['alergia']);
            
            if ($idAlergia === false || empty($tipoAlergia) || empty($alergia)) {
                $resultado['mensaje'] = 'Datos inválidos o incompletos';
                echo json_encode($resultado);
                return;
            }
            
            $datos = [
                'idalergia' => $idAlergia,
                'tipoalergia' => $tipoAlergia,
                'alergia' => $alergia
            ];
            
            $idalergia = $this->modelo->actualizar($datos);
            
            if ($idalergia > 0) {
                $resultado['status'] = true;
                $resultado['mensaje'] = 'Alergia actualizada correctamente';
                $resultado['idalergia'] = $idalergia;
            } else {
                $resultado['mensaje'] = 'Error al actualizar la alergia';
            }
        } else {
            $resultado['mensaje'] = 'Faltan datos obligatorios';
        }
        
        echo json_encode($resultado);
    }
    
    /**
     * Método para actualizar la gravedad de una alergia de un paciente
     */
    private function actualizarGravedad() {
        $resultado = [
            'status' => false,
            'mensaje' => ''
        ];
        
        if (isset($_POST['idlistaalergia']) && isset($_POST['gravedad'])) {
            $idListaAlergia = filter_var($_POST['idlistaalergia'], FILTER_VALIDATE_INT);
            $gravedad = trim($_POST['gravedad']);
            
            if ($idListaAlergia === false || empty($gravedad)) {
                $resultado['mensaje'] = 'Datos inválidos o incompletos';
                echo json_encode($resultado);
                return;
            }
            
            $idlistaalergia = $this->modelo->actualizarGravedad(
                $idListaAlergia,
                $gravedad
            );
            
            if ($idlistaalergia > 0) {
                $resultado['status'] = true;
                $resultado['mensaje'] = 'Gravedad de la alergia actualizada correctamente';
                $resultado['idlistaalergia'] = $idlistaalergia;
            } else {
                $resultado['mensaje'] = 'Error al actualizar la gravedad de la alergia';
            }
        } else {
            $resultado['mensaje'] = 'Faltan datos obligatorios';
        }
        
        echo json_encode($resultado);
    }
    
    /**
     * Método para eliminar una alergia
     */
    private function eliminarAlergia() {
        if (isset($_GET['idalergia']) || isset($_POST['idalergia'])) {
            $idalergia = isset($_GET['idalergia']) ? 
                filter_var($_GET['idalergia'], FILTER_VALIDATE_INT) : 
                filter_var($_POST['idalergia'], FILTER_VALIDATE_INT);
            
            if ($idalergia === false) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de alergia inválido'
                ]);
                return;
            }
            
            $resultado = $this->modelo->eliminar($idalergia);
            echo json_encode([
                'status' => $resultado['eliminado'] == 1,
                'mensaje' => $resultado['mensaje']
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de alergia no especificado'
            ]);
        }
    }
    
    /**
     * Método para eliminar una alergia de un paciente
     */
    private function eliminarAlergiaPaciente() {
        if (isset($_GET['idlistaalergia']) || isset($_POST['idlistaalergia'])) {
            $idlistaalergia = isset($_GET['idlistaalergia']) ? 
                filter_var($_GET['idlistaalergia'], FILTER_VALIDATE_INT) : 
                filter_var($_POST['idlistaalergia'], FILTER_VALIDATE_INT);
            
            if ($idlistaalergia === false) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de lista de alergia inválido'
                ]);
                return;
            }
            
            $resultado = $this->modelo->eliminarAlergiaPaciente($idlistaalergia);
            echo json_encode([
                'status' => $resultado['eliminado'] == 1,
                'mensaje' => $resultado['mensaje']
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de lista de alergia no especificado'
            ]);
        }
    }
}

// Iniciar el controlador y procesar la solicitud
$controller = new AlergiaController();
$controller->procesarSolicitud();
?>