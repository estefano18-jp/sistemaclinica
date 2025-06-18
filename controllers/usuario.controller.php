<?php /*RUTA: sistemaclinica/controllers/usuario.controller.php*/?>
<?php
session_start();

require_once '../models/Usuario.php';

/**
 * Controlador para gestionar operaciones relacionadas con usuarios
 */
class UsuarioController
{

    private $modelo;

    /**
     * Constructor que inicializa el modelo de usuario
     */
    public function __construct()
    {
        $this->modelo = new Usuario();
    }

    /**
     * Método para procesar la solicitud según la operación especificada
     */
    public function procesarSolicitud()
    {
        // Operaciones por GET
        if (isset($_GET['operacion'])) {
            switch ($_GET['operacion']) {
                case 'login':
                    $this->login();
                    break;
                case 'login_enfermero':
                    $this->loginEnfermero();
                    break;
                case 'login_paciente':
                    $this->loginPaciente();
                    break;
                case 'cerrar_sesion':
                    $this->cerrarSesion();
                    break;
                case 'obtener_datos_usuario':
                    $this->obtenerDatosUsuario();
                    break;
                case 'verificar_nombre_usuario':
                    $this->verificarNombreUsuario();
                    break;
            }
        }

        // Operaciones por POST
        if (isset($_POST['operacion'])) {
            switch ($_POST['operacion']) {
                case 'registrar_administrador':
                    $this->registrarAdministrador();
                    break;
                case 'registrar_paciente':
                    $this->registrarPaciente();
                    break;
            }
        }
    }

    /**
     * Método para manejar el login de administradores
     */
    private function login()
    {
        $resultado = [
            "autenticado" => false,
            "apellidos" => "",
            "nombres" => "",
            "idusuario" => "",
            "mensaje" => "",
            "rol" => ""
        ];

        if (isset($_GET['nomuser']) && isset($_GET['passuser'])) {
            $nomuser = $_GET['nomuser'];
            $passuser = $_GET['passuser'];

            // Intentar iniciar sesión como administrador
            $datosAdmin = $this->modelo->loginAdministrador([
                'nomuser' => $nomuser,
                'passuser' => $passuser
            ]);

            if (count($datosAdmin) > 0) {
                // Verificar contraseña del administrador
                $claveEncriptada = $datosAdmin[0]['passuser'];

                if (password_verify($passuser, $claveEncriptada)) {
                    $resultado["autenticado"] = true;
                    $resultado["apellidos"] = $datosAdmin[0]["apellidos"];
                    $resultado["nombres"] = $datosAdmin[0]["nombres"];
                    $resultado["idusuario"] = $datosAdmin[0]["idusuario"];
                    $resultado["rol"] = "ADMINISTRADOR";

                    // Guardar en sesión
                    $_SESSION['usuario'] = $resultado;
                } else {
                    $resultado["mensaje"] = "Contraseña incorrecta";
                }
            } else {
                // Intentar iniciar sesión como doctor usando el email
                $datosDoctor = $this->modelo->loginDoctor($nomuser);

                if (count($datosDoctor) > 0) {
                    // Verificar contraseña del doctor
                    $claveEncriptada = $datosDoctor[0]['passuser'];

                    if (password_verify($passuser, $claveEncriptada)) {
                        $resultado["autenticado"] = true;
                        $resultado["apellidos"] = $datosDoctor[0]["apellidos"];
                        $resultado["nombres"] = $datosDoctor[0]["nombres"];
                        $resultado["idusuario"] = $datosDoctor[0]["idusuario"];
                        $resultado["idcolaborador"] = $datosDoctor[0]["idcolaborador"];
                        $resultado["idespecialidad"] = $datosDoctor[0]["idespecialidad"];
                        $resultado["especialidad"] = $datosDoctor[0]["especialidad"];
                        $resultado["rol"] = "DOCTOR";

                        // Guardar en sesión
                        $_SESSION['usuario'] = $resultado;
                    } else {
                        $resultado["mensaje"] = "Contraseña incorrecta";
                    }
                } else {
                    $resultado["mensaje"] = "El usuario no existe";
                }
            }
        } else {
            $resultado["mensaje"] = "Faltan parámetros para iniciar sesión";
        }

        // Devolver resultado como JSON
        echo json_encode($resultado);
    }

    /**
     * Método para cerrar la sesión del usuario
     */
    private function cerrarSesion()
    {
        session_unset();
        session_destroy();
        header('Location: ../login.php');
        exit();
    }
    /**
     * Método para manejar el login de enfermeros
     */
    private function loginEnfermero()
    {
        $resultado = [
            "autenticado" => false,
            "apellidos" => "",
            "nombres" => "",
            "idusuario" => "",
            "mensaje" => ""
        ];

        if (isset($_GET['email']) && isset($_GET['passuser'])) {
            $datos = $this->modelo->loginEnfermero([
                'email' => $_GET['email']
            ]);

            if (count($datos) == 0) {
                $resultado["mensaje"] = "El usuario no existe o no es un enfermero";
            } else {
                $claveEncriptada = $datos[0]['passuser'];
                $claveIngresada = $_GET['passuser'];

                if (password_verify($claveIngresada, $claveEncriptada)) {
                    $resultado["autenticado"] = true;
                    $resultado["apellidos"] = $datos[0]["apellidos"];
                    $resultado["nombres"] = $datos[0]["nombres"];
                    $resultado["idusuario"] = $datos[0]["idusuario"];
                    $resultado["idcolaborador"] = $datos[0]["idcolaborador"];
                    $resultado["rol"] = $datos[0]["rol"];

                    // Guardar en sesión
                    $_SESSION['usuario'] = $resultado;
                } else {
                    $resultado["mensaje"] = "Contraseña incorrecta";
                }
            }
        } else {
            $resultado["mensaje"] = "Faltan parámetros para iniciar sesión";
        }

        // Devolver resultado como JSON
        echo json_encode($resultado);
    }

    /**
     * Método para manejar el login de pacientes
     */
    private function loginPaciente()
    {
        $resultado = [
            "autenticado" => false,
            "apellidos" => "",
            "nombres" => "",
            "idusuario" => "",
            "mensaje" => "",
            "rol" => ""
        ];

        if (isset($_GET['nrodoc']) && isset($_GET['passuser'])) {
            $datos = $this->modelo->loginPaciente($_GET['nrodoc']);

            if (count($datos) == 0) {
                $resultado["mensaje"] = "El usuario no existe o no es un paciente";
            } else {
                $claveEncriptada = $datos[0]['passuser'];
                $claveIngresada = $_GET['passuser'];

                if (password_verify($claveIngresada, $claveEncriptada)) {
                    $resultado["autenticado"] = true;
                    $resultado["apellidos"] = $datos[0]["apellidos"];
                    $resultado["nombres"] = $datos[0]["nombres"];
                    $resultado["idusuario"] = $datos[0]["idusuario"];
                    $resultado["idpaciente"] = $datos[0]["idpaciente"];
                    $resultado["rol"] = $datos[0]["rol"];

                    // Guardar en sesión
                    $_SESSION['usuario'] = $resultado;
                } else {
                    $resultado["mensaje"] = "Contraseña incorrecta";
                }
            }
        } else {
            $resultado["mensaje"] = "Faltan parámetros para iniciar sesión";
        }

        // Devolver resultado como JSON
        echo json_encode($resultado);
    }

    /**
     * Método para obtener los datos de un usuario
     */
    private function obtenerDatosUsuario()
    {
        if (isset($_GET['idusuario'])) {
            $idusuario = $_GET['idusuario'];
            $datosUsuario = $this->modelo->obtenerDatosUsuario($idusuario);
            if ($datosUsuario) {
                echo json_encode($datosUsuario);
            } else {
                echo json_encode(['error' => 'Datos de usuario no encontrados']);
            }
        } else {
            echo json_encode(['error' => 'ID de usuario no proporcionado']);
        }
    }

    /**
     * Método para verificar si un nombre de usuario ya existe
     */
    private function verificarNombreUsuario()
    {
        if (isset($_GET['nomuser'])) {
            $nomuser = $_GET['nomuser'];
            $existe = $this->modelo->nombreUsuarioExiste($nomuser);

            if ($existe) {
                echo json_encode(["disponible" => false, "mensaje" => "El nombre de usuario ya está en uso"]);
            } else {
                echo json_encode(["disponible" => true]);
            }
        } else {
            echo json_encode(["error" => "Nombre de usuario no proporcionado"]);
        }
    }

    /**
     * Método para registrar un nuevo administrador
     */
    private function registrarAdministrador()
    {
        $resultado = ["exito" => false, "mensaje" => ""];

        // Verificar que se reciban todos los parámetros necesarios
        if (
            isset($_POST['idpersona']) && isset($_POST['nomuser']) &&
            isset($_POST['passuser']) && isset($_POST['estado'])
        ) {

            // Verificar si el nombre de usuario ya existe
            if ($this->modelo->nombreUsuarioExiste($_POST['nomuser'])) {
                $resultado["mensaje"] = "El nombre de usuario ya está en uso";
                echo json_encode($resultado);
                return;
            }

            // Preparar los datos para el registro
            $datos = [
                'idpersona' => $_POST['idpersona'],
                'nomuser'   => $_POST['nomuser'],
                'passuser'  => $_POST['passuser'],
                'estado'    => $_POST['estado']
            ];

            // Registrar el administrador
            $idusuario = $this->modelo->registrarAdministrador($datos);

            if ($idusuario > 0) {
                $resultado["exito"] = true;
                $resultado["mensaje"] = "Administrador registrado correctamente";
                $resultado["idusuario"] = $idusuario;
            } else {
                $resultado["mensaje"] = "Error al registrar el administrador";
            }
        } else {
            $resultado["mensaje"] = "Faltan datos para el registro";
        }

        echo json_encode($resultado);
    }

    /**
     * Método para registrar un nuevo paciente como usuario
     */
    private function registrarPaciente()
    {
        $resultado = ["exito" => false, "mensaje" => ""];

        // Verificar que se reciban todos los parámetros necesarios
        if (isset($_POST['idpaciente']) && isset($_POST['passuser'])) {
            // Preparar los datos para el registro
            $datos = [
                'idpaciente' => $_POST['idpaciente'],
                'passuser'  => $_POST['passuser']
            ];

            // Registrar el paciente
            $idusuario = $this->modelo->registrarPaciente($datos);

            if ($idusuario > 0) {
                $resultado["exito"] = true;
                $resultado["mensaje"] = "Paciente registrado correctamente como usuario";
                $resultado["idusuario"] = $idusuario;
            } else {
                $resultado["mensaje"] = "Error al registrar el usuario paciente";
            }
        } else {
            $resultado["mensaje"] = "Faltan datos para el registro";
        }

        echo json_encode($resultado);
    }
}

// Iniciar el controlador y procesar la solicitud
$controller = new UsuarioController();
$controller->procesarSolicitud();