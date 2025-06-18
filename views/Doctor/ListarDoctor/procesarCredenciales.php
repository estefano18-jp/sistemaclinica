<?php /*RUTA: sistemasclinica/views/Doctor/ListarDoctor/procesarCredenciales.php*/ ?>

<?php
// Verificar que se reciba una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => false,
        'mensaje' => 'Método de solicitud no válido'
    ]);
    exit;
}

// Verificar campos requeridos
$camposRequeridos = ['idcolaborador', 'nrodoc', 'email'];
foreach ($camposRequeridos as $campo) {
    if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
        echo json_encode([
            'status' => false,
            'mensaje' => "El campo $campo es requerido"
        ]);
        exit;
    }
}

// Validar formato de email
$email = trim($_POST['email']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => false,
        'mensaje' => 'El formato del correo electrónico no es válido'
    ]);
    exit;
}

// Si se envió una nueva contraseña, verificar que tenga al menos 6 caracteres
$nuevaPassword = null;
if (isset($_POST['nuevaPassword']) && !empty($_POST['nuevaPassword'])) {
    $nuevaPassword = $_POST['nuevaPassword'];
    $confirmarPassword = $_POST['confirmarPassword'] ?? '';
    
    if (strlen($nuevaPassword) < 6) {
        echo json_encode([
            'status' => false,
            'mensaje' => 'La contraseña debe tener al menos 6 caracteres'
        ]);
        exit;
    }
    
    if ($nuevaPassword !== $confirmarPassword) {
        echo json_encode([
            'status' => false,
            'mensaje' => 'Las contraseñas no coinciden'
        ]);
        exit;
    }
}

// Preparar los datos para actualizar
$datos = [
    'idcolaborador' => intval($_POST['idcolaborador']),
    'idusuario' => isset($_POST['idusuario']) ? intval($_POST['idusuario']) : 0,
    'emailusuario' => $email,
    'nrodoc' => trim($_POST['nrodoc'])
];

// Agregar la contraseña si se especificó
if ($nuevaPassword) {
    $datos['contrasena'] = $nuevaPassword;
}

try {
    // Incluir el controlador de credenciales
    require_once '../../../controllers/credencial.controller.php';
    
    // Crear instancia del controlador
    $controller = new CredencialController();
    
    // Simular el $_POST para el controlador
    $_POST = $datos;
    
    // Capturar la salida del controlador
    ob_start();
    $controller->actualizarCredenciales();
    $resultado = ob_get_clean();
    
    // Decodificar el resultado JSON
    $response = json_decode($resultado, true);
    
    if ($response && isset($response['status'])) {
        echo json_encode($response);
    } else {
        echo json_encode([
            'status' => false,
            'mensaje' => 'Error al procesar la respuesta del servidor'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'mensaje' => 'Error al actualizar las credenciales: ' . $e->getMessage()
    ]);
}
?>