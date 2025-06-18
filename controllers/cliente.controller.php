<?php /*RUTA: sistemaclinica/controllers/cliente.controller.php*/ ?>
<?php
session_start();

require_once '../models/Cliente.php';

class ClienteController
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new Cliente();
    }

    /**
     * Método para procesar la solicitud según la operación especificada
     */

    public function procesarSolicitud()
    {
        $operacion = isset($_GET['op']) ? $_GET['op'] : '';

        switch ($operacion) {
            case 'buscar':
                $this->buscarCliente();
                break;
            case 'registrar':
                $this->registrarCliente();
                break;
            case 'registrar_empresa':
                $this->registrarClienteEmpresa();
                break;
            case 'buscar_por_persona':
                $this->buscarPorPersona();
                break;
            case 'buscar_por_empresa':
                $this->buscarPorEmpresa();
                break;
            case 'registrar_existente':
                $this->registrarExistente();
                break;
            case 'actualizar_persona':
                $this->actualizarClientePersona();
                break;
            case 'actualizar_empresa': // Nueva operación
                $this->actualizarClienteEmpresa();
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
     * Busca un cliente por su documento
     */
    private function buscarCliente()
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
        if (!isset($_GET['tipodoc']) || !isset($_GET['nrodoc'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Faltan parámetros requeridos'
            ]);
            return;
        }

        $tipoDoc = $_GET['tipodoc'];
        $nroDoc = $_GET['nrodoc'];

        // Buscar cliente
        $cliente = $this->modelo->buscarPorDocumento($tipoDoc, $nroDoc);

        if ($cliente) {
            echo json_encode([
                'status' => true,
                'data' => $cliente
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Cliente no encontrado'
            ]);
        }
    }

    /**
     * Registra un nuevo cliente
     * Versión completamente reescrita para mayor robustez
     */
    private function registrarCliente()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // Depuración - mostrar todos los datos recibidos
        error_log("Datos recibidos para registrar cliente: " . print_r($_POST, true));

        // Validar campos requeridos con nombres exactos
        $camposRequeridos = ['nombres', 'apellidos', 'tipodoc', 'nrodoc'];

        // CORRECCIÓN: Verificar que todos los campos requeridos existan y no estén vacíos
        foreach ($camposRequeridos as $campo) {
            if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
                echo json_encode([
                    'status' => false,
                    'mensaje' => "Falta el campo requerido o está vacío: {$campo}"
                ]);
                error_log("Campo requerido faltante o vacío: {$campo}");
                return;
            }
        }

        // CORRECCIÓN: Sanitizar y normalizar los datos recibidos
        $datos = [
            'nombres' => trim($_POST['nombres']),
            'apellidos' => trim($_POST['apellidos']),
            'tipodoc' => strtoupper(trim($_POST['tipodoc'])),
            'nrodoc' => trim($_POST['nrodoc'])
        ];

        // Validación adicional después de procesar
        if (
            empty($datos['nombres']) || empty($datos['apellidos']) ||
            empty($datos['tipodoc']) || empty($datos['nrodoc'])
        ) {
            echo json_encode([
                'status' => false,
                'mensaje' => "Datos inválidos después de procesar"
            ]);
            error_log("Datos inválidos después de procesar: " . print_r($datos, true));
            return;
        }

        // CORRECCIÓN: Establecer explícitamente el tipo de cliente
        if (isset($_POST['tipocliente']) && !empty($_POST['tipocliente'])) {
            $datos['tipocliente'] = strtoupper(trim($_POST['tipocliente']));
        } else {
            $datos['tipocliente'] = 'NATURAL'; // Valor por defecto
        }

        // Agregar campos opcionales solo si existen y no están vacíos
        $camposOpcionales = ['telefono', 'fechanacimiento', 'genero', 'direccion', 'email'];
        foreach ($camposOpcionales as $campo) {
            if (isset($_POST[$campo]) && trim($_POST[$campo]) !== '') {
                $datos[$campo] = trim($_POST[$campo]);
            }
        }

        // Logging detallado antes de registrar
        error_log("Datos procesados para registrar cliente: " . print_r($datos, true));

        // Registrar cliente con manejo de errores mejorado
        try {
            $resultado = $this->modelo->registrar($datos);

            // Verificar el resultado
            if (!$resultado['status']) {
                error_log("Error al registrar cliente desde controlador: " . ($resultado['mensaje'] ?? "Error desconocido"));
            } else {
                error_log("Cliente registrado exitosamente con ID: " . ($resultado['idcliente'] ?? "ID no disponible"));
            }

            // Retornar resultado
            echo json_encode($resultado);
        } catch (Exception $e) {
            error_log("Excepción al registrar cliente desde controlador: " . $e->getMessage());
            echo json_encode([
                'status' => false,
                'mensaje' => 'Error al registrar cliente: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Registra un nuevo cliente empresa
     */
    private function registrarClienteEmpresa()
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
        $camposRequeridos = ['razonsocial', 'ruc', 'direccion', 'telefono', 'email'];
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
            'razonsocial' => $_POST['razonsocial'],
            'ruc' => $_POST['ruc'],
            'direccion' => $_POST['direccion'],
            'nombrecomercial' => isset($_POST['nombrecomercial']) ? $_POST['nombrecomercial'] : $_POST['razonsocial'],
            'telefono' => $_POST['telefono'],
            'email' => $_POST['email']
        ];

        // Registrar cliente empresa
        $resultado = $this->modelo->registrarEmpresa($datos);

        // Retornar resultado
        echo json_encode($resultado);
    }

    /**
     * Busca un cliente por ID de persona
     */
    public function buscarPorPersona()
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
        if (!isset($_GET['idpersona'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Falta el ID de persona'
            ]);
            return;
        }

        $idpersona = intval($_GET['idpersona']);

        // Buscar cliente por ID de persona
        $cliente = $this->modelo->buscarPorPersona($idpersona);

        if ($cliente) {
            echo json_encode([
                'status' => true,
                'data' => $cliente
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Cliente no encontrado'
            ]);
        }
    }

    /**
     * Registra un cliente utilizando una persona o empresa existente
     * SOLUCIÓN: Corregido para aceptar idpersona o idempresa
     */
    private function registrarExistente()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método no válido'
            ]);
            return;
        }

        // CORRECCIÓN: Mensajes de depuración en PHP
        error_log("Datos recibidos para registrar cliente existente: " . print_r($_POST, true));

        // SOLUCIÓN CLAVE: Validar que haya al menos uno de los IDs necesarios
        if ((!isset($_POST['idempresa']) || empty($_POST['idempresa'])) &&
            (!isset($_POST['idpersona']) || empty($_POST['idpersona']))
        ) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Se requiere ID de persona o ID de empresa'
            ]);
            return;
        }

        // CORRECCIÓN: Determinar tipo de cliente basado en los datos proporcionados
        if (!isset($_POST['tipocliente']) || empty($_POST['tipocliente'])) {
            $_POST['tipocliente'] = isset($_POST['idpersona']) ? 'NATURAL' : 'EMPRESA';
        }

        // CORRECCIÓN: Preparar datos correctamente según lo que nos envíen
        $datos = [
            'tipocliente' => $_POST['tipocliente']
        ];

        // Agregar idempresa solo si está presente
        if (isset($_POST['idempresa']) && !empty($_POST['idempresa'])) {
            $datos['idempresa'] = intval($_POST['idempresa']);
        }

        // Agregar idpersona solo si está presente
        if (isset($_POST['idpersona']) && !empty($_POST['idpersona'])) {
            $datos['idpersona'] = intval($_POST['idpersona']);
        }

        // Registrar cliente con persona o empresa existente
        $resultado = $this->modelo->registrarConPersonaExistente($datos);

        echo json_encode($resultado);
    }
    /**
     * Actualiza un cliente para asociarlo con una empresa
     */
    private function actualizarClienteEmpresa()
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
        if (!isset($_POST['idcliente']) || !isset($_POST['idempresa'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Faltan datos requeridos'
            ]);
            return;
        }

        $idcliente = intval($_POST['idcliente']);
        $idempresa = intval($_POST['idempresa']);

        // Actualizar cliente
        $resultado = $this->modelo->actualizarClienteEmpresa($idcliente, $idempresa);

        // Retornar resultado
        echo json_encode($resultado);
    }
    /**
     * Busca un cliente por ID de empresa
     */
    public function buscarPorEmpresa()
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
        if (!isset($_GET['idempresa'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Falta el ID de empresa'
            ]);
            return;
        }

        $idempresa = intval($_GET['idempresa']);

        // Buscar cliente por ID de empresa
        $cliente = $this->modelo->buscarPorEmpresa($idempresa);

        if ($cliente) {
            echo json_encode([
                'status' => true,
                'data' => $cliente
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Cliente no encontrado'
            ]);
        }
    }
    /**
     * Método para actualizar un cliente asociándolo con una persona
     * Agregar este método completo a la clase ClienteController
     */
    private function actualizarClientePersona()
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
        if (!isset($_POST['idcliente']) || !isset($_POST['idpersona'])) {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Faltan datos requeridos'
            ]);
            return;
        }

        $idcliente = intval($_POST['idcliente']);
        $idpersona = intval($_POST['idpersona']);

        // Actualizar cliente
        $resultado = $this->modelo->actualizarClientePersona($idcliente, $idpersona);

        // Retornar resultado
        echo json_encode($resultado);
    }
}

// Iniciar controlador y procesar solicitud
$controller = new ClienteController();
$controller->procesarSolicitud();
