<?php /*RUTA: sistemasclinica/controllers/credencial.controller.php*/ ?>
<?php

require_once '../models/Credencial.php';

class CredencialController
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new Credencial();
    }

    /**
     * Registra credenciales de acceso para un usuario
     */
    public function registrarCredenciales()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Capturar datos del formulario
            $datos = [
                'idcontrato' => isset($_POST['idcontrato']) ? intval($_POST['idcontrato']) : 0,
                'nomuser' => isset($_POST['nomuser']) ? $_POST['nomuser'] : '',
                'passuser' => isset($_POST['passuser']) ? $_POST['passuser'] : '',
                'rol' => isset($_POST['rol']) ? $_POST['rol'] : 'DOCTOR' // Por defecto DOCTOR
            ];

            // Validar campos obligatorios
            if ($datos['idcontrato'] <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'El ID de contrato es requerido o no válido'
                ]);
                return;
            }

            if (empty($datos['nomuser'])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'El correo electrónico es requerido'
                ]);
                return;
            }

            // Validar formato de correo electrónico
            if (!filter_var($datos['nomuser'], FILTER_VALIDATE_EMAIL)) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'El formato del correo electrónico no es válido'
                ]);
                return;
            }

            if (empty($datos['passuser'])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'La contraseña es requerida'
                ]);
                return;
            }

            // Verificar si el correo electrónico ya existe como usuario
            if ($this->modelo->verificarUsuarioExistente($datos['nomuser'])) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'El correo electrónico ya está en uso'
                ]);
                return;
            }

            // Registrar credenciales
            $resultado = $this->modelo->registrarCredenciales($datos);

            echo json_encode($resultado);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método de solicitud no válido'
            ]);
        }
    }

    /**
     * Actualiza las credenciales de un usuario existente
     */
    public function actualizarCredenciales()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                // Capturar datos del formulario con validación mejorada
                $datos = [
                    'idcolaborador' => isset($_POST['idcolaborador']) ? intval($_POST['idcolaborador']) : 0,
                    'idusuario' => isset($_POST['idusuario']) ? intval($_POST['idusuario']) : 0,
                    'emailusuario' => $this->obtenerEmail($_POST),
                    'contrasena' => $this->obtenerPassword($_POST),
                    'nrodoc' => isset($_POST['nrodoc']) ? trim($_POST['nrodoc']) : ''
                ];

                // Log para debugging
                error_log("Datos recibidos en actualizarCredenciales: " . json_encode($datos));

                // Validar datos obligatorios
                if (empty($datos['emailusuario'])) {
                    echo json_encode([
                        'status' => false,
                        'mensaje' => 'El correo electrónico es obligatorio'
                    ]);
                    return;
                }

                // Validar formato de correo electrónico
                if (!filter_var($datos['emailusuario'], FILTER_VALIDATE_EMAIL)) {
                    echo json_encode([
                        'status' => false,
                        'mensaje' => 'El formato del correo electrónico no es válido'
                    ]);
                    return;
                }

                // Validar longitud de contraseña solo si se proporcionó una nueva
                if (!empty($datos['contrasena']) && strlen($datos['contrasena']) < 6) {
                    echo json_encode([
                        'status' => false,
                        'mensaje' => 'La contraseña debe tener al menos 6 caracteres'
                    ]);
                    return;
                }

                // Verificar si el correo ya está en uso por otro usuario (solo si se cambió el email)
                if ($datos['idusuario'] > 0) {
                    if ($this->modelo->verificarCorreoDuplicado($datos['emailusuario'], $datos['idusuario'])) {
                        echo json_encode([
                            'status' => false,
                            'mensaje' => 'El correo electrónico ya está en uso por otro usuario'
                        ]);
                        return;
                    }
                } else {
                    // Si no hay usuario existente, verificar que el email no esté en uso
                    if ($this->modelo->verificarUsuarioExistente($datos['emailusuario'])) {
                        echo json_encode([
                            'status' => false,
                            'mensaje' => 'El correo electrónico ya está en uso'
                        ]);
                        return;
                    }
                }

                // Actualizar credenciales
                $resultado = $this->modelo->actualizarCredenciales($datos);

                // Log del resultado
                error_log("Resultado de actualizarCredenciales: " . json_encode($resultado));

                echo json_encode($resultado);
                
            } catch (Exception $e) {
                error_log("Error en actualizarCredenciales: " . $e->getMessage());
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Error interno del servidor: ' . $e->getMessage()
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
     * Obtiene el email de diferentes posibles campos
     */
    private function obtenerEmail($post)
    {
        $campos = ['emailusuario', 'email', 'nomuser'];
        foreach ($campos as $campo) {
            if (isset($post[$campo]) && !empty(trim($post[$campo]))) {
                return trim($post[$campo]);
            }
        }
        return '';
    }

    /**
     * Obtiene la contraseña de diferentes posibles campos
     */
    private function obtenerPassword($post)
    {
        $campos = ['contrasena', 'nuevaPassword', 'passuser', 'password'];
        foreach ($campos as $campo) {
            if (isset($post[$campo]) && !empty(trim($post[$campo]))) {
                return trim($post[$campo]);
            }
        }
        return '';
    }

    /**
     * Verifica si un correo electrónico ya está en uso como usuario
     */
    public function verificarUsuario()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Capturar email a verificar
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';

            if (empty($email)) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'El correo electrónico es requerido',
                    'disponible' => false
                ]);
                return;
            }

            // Validar formato de correo electrónico
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'El formato del correo electrónico no es válido',
                    'disponible' => false
                ]);
                return;
            }

            // Verificar si el correo ya existe
            $existe = $this->modelo->verificarUsuarioExistente($email);

            echo json_encode([
                'status' => true,
                'disponible' => !$existe,
                'mensaje' => $existe ? 'El correo electrónico ya está en uso' : 'Correo electrónico disponible'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método de solicitud no válido',
                'disponible' => false
            ]);
        }
    }

    /**
     * Obtiene la lista de usuarios por contrato
     */
    public function obtenerUsuariosPorContrato()
    {
        // Verificar que se reciba el método GET
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // Capturar ID del contrato
            $idContrato = isset($_GET['idcontrato']) ? intval($_GET['idcontrato']) : 0;

            if ($idContrato <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de contrato no válido'
                ]);
                return;
            }

            // Obtener usuarios
            $usuarios = $this->modelo->obtenerUsuariosPorContrato($idContrato);

            echo json_encode([
                'status' => true,
                'data' => $usuarios
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'mensaje' => 'Método de solicitud no válido'
            ]);
        }
    }

    /**
     * Obtiene información de un usuario específico
     */
    public function obtenerUsuario()
    {
        // Verificar que se reciba el método GET
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // Capturar ID del usuario
            $idUsuario = isset($_GET['id']) ? intval($_GET['id']) : 0;

            if ($idUsuario <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de usuario no válido'
                ]);
                return;
            }

            // Obtener datos del usuario
            $usuario = $this->modelo->obtenerUsuarioPorId($idUsuario);

            if ($usuario) {
                echo json_encode([
                    'status' => true,
                    'data' => $usuario
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Usuario no encontrado'
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
     * Cambia el estado de un usuario (activar/desactivar)
     */
    public function cambiarEstadoUsuario()
    {
        // Verificar que se reciba el método POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Capturar datos
            $idUsuario = isset($_POST['idusuario']) ? intval($_POST['idusuario']) : 0;
            $estado = isset($_POST['estado']) ? boolval($_POST['estado']) : null;

            if ($idUsuario <= 0) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'ID de usuario no válido'
                ]);
                return;
            }

            if ($estado === null) {
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'El estado es requerido'
                ]);
                return;
            }

            // Cambiar estado
            $resultado = $this->modelo->cambiarEstadoUsuario($idUsuario, $estado);

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
if (isset($_GET['op'])) {
    try {
        $controller = new CredencialController();

        switch ($_GET['op']) {
            case 'registrar':
                $controller->registrarCredenciales();
                break;
            case 'actualizar_credenciales':
                $controller->actualizarCredenciales();
                break;
            case 'verificar_usuario':
                $controller->verificarUsuario();
                break;
            case 'listar':
                $controller->obtenerUsuariosPorContrato();
                break;
            case 'obtener':
                $controller->obtenerUsuario();
                break;
            case 'cambiarestado':
                $controller->cambiarEstadoUsuario();
                break;
            default:
                echo json_encode([
                    'status' => false,
                    'mensaje' => 'Operación no válida: ' . $_GET['op']
                ]);
                break;
        }
    } catch (Exception $e) {
        error_log("Error en credencial.controller.php: " . $e->getMessage());
        echo json_encode([
            'status' => false,
            'mensaje' => 'Error interno del servidor'
        ]);
    }
} else {
    echo json_encode([
        'status' => false,
        'mensaje' => 'No se especificó ninguna operación'
    ]);
}