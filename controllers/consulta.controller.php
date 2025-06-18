<?php /*RUTA: sistemaclinica/controllers/consulta.controller.php*/?>
<?php

require_once '../models/Consulta.php';

class ConsultaController 
{
    private $modelo;
    
    public function __construct() 
    {
        $this->modelo = new Consulta();
    }
    
    /**
     * Procesa todas las solicitudes entrantes
     */
    public function procesarSolicitud() 
    {
        // Verificar si hay una operación especificada
        $operacion = isset($_GET['op']) ? $_GET['op'] : '';
        
        switch ($operacion) {
            // Operaciones GET
            case 'listar':
                $this->listarConsultas();
                break;
                
            case 'obtener':
                $this->obtenerConsulta();
                break;
                
            case 'historial_paciente':
                $this->historialPaciente();
                break;
                
            case 'estadisticas':
                $this->obtenerEstadisticas();
                break;
                
            case 'consultas_paciente':
                $this->consultasPorPaciente();
                break;
                
            // Operaciones POST
            case 'registrar':
                $this->registrarConsulta();
                break;
                
            case 'actualizar':
                $this->actualizarConsulta();
                break;
                
            case 'registrar_triaje':
                $this->registrarTriaje();
                break;
                
            case 'gestionar_receta':
                $this->gestionarReceta();
                break;
                
            case 'asignar_diagnostico':
                $this->asignarDiagnostico();
                break;
                
            case 'solicitar_servicio':
                $this->solicitarServicio();
                break;
                
            case 'registrar_resultado':
                $this->registrarResultadoServicio();
                break;
                
            case 'actualizar_alta_medica':
                $this->actualizarAltaMedica();
                break;
                
            case 'gestionar_historia_clinica':
                $this->gestionarHistoriaClinica();
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
     * Lista las consultas con filtros opcionales
     */
    private function listarConsultas() 
    {
        // Obtener filtros de la solicitud
        $filtros = [];
        
        if (isset($_GET['fecha'])) {
            $filtros['fecha'] = $_GET['fecha'];
        }
        
        if (isset($_GET['fecha_inicio'])) {
            $filtros['fecha_inicio'] = $_GET['fecha_inicio'];
        }
        
        if (isset($_GET['fecha_fin'])) {
            $filtros['fecha_fin'] = $_GET['fecha_fin'];
        }
        
        if (isset($_GET['idespecialidad'])) {
            $filtros['idespecialidad'] = $_GET['idespecialidad'];
        }
        
        if (isset($_GET['iddoctor'])) {
            $filtros['iddoctor'] = $_GET['iddoctor'];
        }
        
        if (isset($_GET['idpaciente'])) {
            $filtros['idpaciente'] = $_GET['idpaciente'];
        }
        
        if (isset($_GET['documento_paciente'])) {
            $filtros['documento_paciente'] = $_GET['documento_paciente'];
        }
        
        if (isset($_GET['nombre_paciente'])) {
            $filtros['nombre_paciente'] = $_GET['nombre_paciente'];
        }
        
        // Paginación
        if (isset($_GET['limite'])) {
            $filtros['limite'] = intval($_GET['limite']);
        }
        
        if (isset($_GET['offset'])) {
            $filtros['offset'] = intval($_GET['offset']);
        }
        
        // Obtener consultas
        $consultas = $this->modelo->listarConsultas($filtros);
        
        echo json_encode([
            'status' => true,
            'data' => $consultas
        ]);
    }
    
    /**
     * Obtiene los detalles de una consulta específica
     */
    private function obtenerConsulta() 
    {
        // Validar que se recibió el ID de consulta
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de consulta no proporcionado'
            ]);
            return;
        }
        
        $idconsulta = intval($_GET['id']);
        $consulta = $this->modelo->obtenerConsultaPorId($idconsulta);
        
        if ($consulta) {
            echo json_encode([
                'status' => true,
                'data' => $consulta
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Consulta no encontrada'
            ]);
        }
    }
    
    /**
     * Obtiene el historial médico completo de un paciente
     */
    private function historialPaciente() 
    {
        // Validar que se recibió el ID de paciente
        if (!isset($_GET['idpaciente']) || empty($_GET['idpaciente'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de paciente no proporcionado'
            ]);
            return;
        }
        
        $idpaciente = intval($_GET['idpaciente']);
        $historial = $this->modelo->obtenerHistorialMedico($idpaciente);
        
        if ($historial) {
            echo json_encode([
                'status' => true,
                'data' => $historial
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'No se encontró el historial médico para este paciente'
            ]);
        }
    }
    
    /**
     * Obtiene estadísticas de consultas
     */
    private function obtenerEstadisticas() 
    {
        // Obtener filtros para las estadísticas
        $filtros = [];
        
        if (isset($_GET['fecha_inicio'])) {
            $filtros['fecha_inicio'] = $_GET['fecha_inicio'];
        }
        
        if (isset($_GET['fecha_fin'])) {
            $filtros['fecha_fin'] = $_GET['fecha_fin'];
        }
        
        // Obtener estadísticas
        $estadisticas = $this->modelo->estadisticasConsultas($filtros);
        
        echo json_encode([
            'status' => true,
            'data' => $estadisticas
        ]);
    }
    
    /**
     * Obtiene las consultas de un paciente específico
     */
    private function consultasPorPaciente() 
    {
        // Validar que se recibió el ID de paciente
        if (!isset($_GET['idpaciente']) || empty($_GET['idpaciente'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de paciente no proporcionado'
            ]);
            return;
        }
        
        $idpaciente = intval($_GET['idpaciente']);
        $consultas = $this->modelo->consultasPorPaciente($idpaciente);
        
        echo json_encode([
            'status' => true,
            'data' => $consultas
        ]);
    }
    
    /**
     * Registra una nueva consulta médica
     */
    private function registrarConsulta() 
    {
        // Verificar método de solicitud
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener datos del formulario
        $datos = $_POST;
        
        // Validar datos mínimos requeridos
        if (empty($datos['fecha']) || empty($datos['idhorario']) || 
            empty($datos['horaprogramada']) || empty($datos['idpaciente'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Faltan datos obligatorios para registrar la consulta'
            ]);
            return;
        }
        
        // Procesar triaje si existe
        if (isset($datos['triaje']) && is_string($datos['triaje'])) {
            $datos['triaje'] = json_decode($datos['triaje'], true);
        }
        
        // Procesar receta si existe
        if (isset($datos['receta']) && is_string($datos['receta'])) {
            $datos['receta'] = json_decode($datos['receta'], true);
        }
        
        // Procesar tratamiento si existe
        if (isset($datos['tratamiento']) && is_string($datos['tratamiento'])) {
            $datos['tratamiento'] = json_decode($datos['tratamiento'], true);
        }
        
        // Procesar servicios si existen
        if (isset($datos['servicios']) && is_string($datos['servicios'])) {
            $datos['servicios'] = json_decode($datos['servicios'], true);
        }
        
        // Registrar consulta
        $resultado = $this->modelo->registrarConsulta($datos);
        
        echo json_encode($resultado);
    }
    
    /**
     * Actualiza los datos de una consulta existente
     */
    private function actualizarConsulta() 
    {
        // Verificar método de solicitud
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener datos del formulario
        $datos = $_POST;
        
        // Validar que se proporcionó el ID de la consulta
        if (empty($datos['idconsulta'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de consulta no proporcionado'
            ]);
            return;
        }
        
        // Procesar triaje si existe
        if (isset($datos['triaje']) && is_string($datos['triaje'])) {
            $datos['triaje'] = json_decode($datos['triaje'], true);
        }
        
        // Procesar receta si existe
        if (isset($datos['receta']) && is_string($datos['receta'])) {
            $datos['receta'] = json_decode($datos['receta'], true);
        }
        
        // Procesar tratamiento si existe
        if (isset($datos['tratamiento']) && is_string($datos['tratamiento'])) {
            $datos['tratamiento'] = json_decode($datos['tratamiento'], true);
        }
        
        // Actualizar consulta
        $resultado = $this->modelo->actualizarConsulta($datos);
        
        echo json_encode($resultado);
    }
    
    /**
     * Registra un triaje para una consulta
     */
    private function registrarTriaje() 
    {
        // Verificar método de solicitud
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener datos del formulario
        $datos = $_POST;
        
        // Validar datos mínimos requeridos
        if (empty($datos['idconsulta']) || empty($datos['idenfermera']) || empty($datos['hora'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Faltan datos obligatorios para registrar el triaje'
            ]);
            return;
        }
        
        // Registrar triaje
        $resultado = $this->modelo->registrarTriaje($datos);
        
        echo json_encode($resultado);
    }
    
    /**
     * Gestiona la receta de una consulta (crea o actualiza)
     */
    private function gestionarReceta() 
    {
        // Verificar método de solicitud
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener datos del formulario
        $datos = $_POST;
        
        // Validar datos mínimos requeridos
        if (empty($datos['idconsulta']) || empty($datos['medicacion'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Faltan datos obligatorios para gestionar la receta'
            ]);
            return;
        }
        
        // Procesar tratamiento si existe
        if (isset($datos['tratamiento']) && is_string($datos['tratamiento'])) {
            $datos['tratamiento'] = json_decode($datos['tratamiento'], true);
        }
        
        // Gestionar receta
        $resultado = $this->modelo->gestionarReceta($datos);
        
        echo json_encode($resultado);
    }
    
    /**
     * Asigna un diagnóstico a una consulta
     */
    private function asignarDiagnostico() 
    {
        // Verificar método de solicitud
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener datos del formulario
        $datos = $_POST;
        
        // Validar datos mínimos requeridos
        if (empty($datos['idconsulta']) || empty($datos['iddiagnostico'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Faltan datos obligatorios para asignar el diagnóstico'
            ]);
            return;
        }
        
        // Asignar diagnóstico
        $resultado = $this->modelo->asignarDiagnostico($datos);
        
        echo json_encode($resultado);
    }
    
    /**
     * Solicita un servicio para una consulta
     */
    private function solicitarServicio() 
    {
        // Verificar método de solicitud
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener datos del formulario
        $datos = $_POST;
        
        // Validar datos mínimos requeridos
        if (empty($datos['idconsulta']) || empty($datos['idtiposervicio'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Faltan datos obligatorios para solicitar el servicio'
            ]);
            return;
        }
        
        // Solicitar servicio
        $resultado = $this->modelo->solicitarServicio($datos);
        
        echo json_encode($resultado);
    }
    
    /**
     * Registra el resultado de un servicio
     */
    private function registrarResultadoServicio() 
    {
        // Verificar método de solicitud
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener datos del formulario
        $datos = $_POST;
        
        // Validar datos mínimos requeridos
        if (empty($datos['idserviciorequerido']) || empty($datos['caracteristicaevaluada'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Faltan datos obligatorios para registrar el resultado'
            ]);
            return;
        }
        
        // Procesar el caso de subida de imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            // Directorio donde se guardarán las imágenes
            $directorio = "../../../uploads/resultados/";
            
            // Crear el directorio si no existe
            if (!file_exists($directorio)) {
                mkdir($directorio, 0777, true);
            }
            
            // Generar un nombre único para la imagen
            $nombre_archivo = 'resultado_' . time() . '_' . $_FILES['imagen']['name'];
            $ruta_archivo = $directorio . $nombre_archivo;
            
            // Mover el archivo subido al directorio deseado
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_archivo)) {
                // Ruta relativa para la base de datos
                $datos['rutaimagen'] = 'uploads/resultados/' . $nombre_archivo;
            }
        }
        
        // Registrar resultado
        $resultado = $this->modelo->registrarResultadoServicio($datos);
        
        echo json_encode($resultado);
    }
    
    /**
     * Actualiza el estado de alta médica de un paciente
     */
    private function actualizarAltaMedica() 
    {
        // Verificar método de solicitud
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener datos del formulario
        $idhistoriaclinica = isset($_POST['idhistoriaclinica']) ? intval($_POST['idhistoriaclinica']) : 0;
        $altamedica = isset($_POST['altamedica']) ? filter_var($_POST['altamedica'], FILTER_VALIDATE_BOOLEAN) : false;
        
        if ($idhistoriaclinica <= 0) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de historia clínica no válido'
            ]);
            return;
        }
        
        // Actualizar estado de alta médica
        $resultado = $this->modelo->actualizarAltaMedica($idhistoriaclinica, $altamedica);
        
        echo json_encode($resultado);
    }
    
    /**
     * Gestiona una historia clínica (crea o actualiza)
     */
    private function gestionarHistoriaClinica() 
    {
        // Verificar método de solicitud
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no permitido'
            ]);
            return;
        }
        
        // Obtener datos del formulario
        $datos = $_POST;
        
        // Validar datos mínimos requeridos
        if (empty($datos['idpaciente'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'ID de paciente no proporcionado'
            ]);
            return;
        }
        
        // Gestionar historia clínica
        $resultado = $this->modelo->gestionarHistoriaClinica($datos);
        
        echo json_encode($resultado);
    }
}

// Procesar la solicitud
$controller = new ConsultaController();
$controller->procesarSolicitud();