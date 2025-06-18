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
$camposRequeridos = ['idcolaborador', 'idusuario'];
foreach ($camposRequeridos as $campo) {
    if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
        echo json_encode([
            'status' => false,
            'mensaje' => "El campo $campo es requerido"
        ]);
        exit;
    }
}

// Si se envió una nueva contraseña, verificar que tenga al menos 6 caracteres
if (isset($_POST['nuevaPassword']) && !empty($_POST['nuevaPassword'])) {
    $nuevaPassword = $_POST['nuevaPassword'];
    $confirmarPassword = $_POST['confirmarPassword'];
    
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
    'idusuario' => intval($_POST['idusuario']),
    'idcolaborador' => intval($_POST['idcolaborador'])
];

// Agregar la contraseña si se especificó
if (isset($_POST['nuevaPassword']) && !empty($_POST['nuevaPassword'])) {
    $datos['nuevaPassword'] = $_POST['nuevaPassword'];
}

// Incluir el modelo de Enfermería
require_once '../../../models/Enfermeria.php';

// Realizar la actualización
$enfermeria = new Enfermeria();

// Si hay nueva contraseña, actualizar credenciales
if (isset($datos['nuevaPassword'])) {
    $resultado = $enfermeria->actualizarCredenciales($datos);
} else {
    // Si no hay nueva contraseña, retornar mensaje informativo
    $resultado = [
        'status' => true,
        'mensaje' => 'No se realizaron cambios en las credenciales'
    ];
}

// Devolver respuesta en formato JSON
echo json_encode($resultado);
?>