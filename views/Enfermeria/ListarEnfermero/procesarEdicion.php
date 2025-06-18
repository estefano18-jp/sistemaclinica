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
$camposRequeridos = ['idcolaborador', 'idpersona', 'apellidos', 'nombres', 'tipodoc', 'nrodoc', 'genero', 'telefono', 'email'];
foreach ($camposRequeridos as $campo) {
    if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
        echo json_encode([
            'status' => false,
            'mensaje' => "El campo $campo es requerido"
        ]);
        exit;
    }
}

// Validar formato de correo electrónico
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => false,
        'mensaje' => 'El formato de correo electrónico no es válido'
    ]);
    exit;
}

// Incluir el modelo de Enfermería
require_once '../../../models/Enfermeria.php';

// Preparar los datos para actualizar
$datos = [
    'idcolaborador' => intval($_POST['idcolaborador']),
    'idpersona' => intval($_POST['idpersona']),
    'apellidos' => $_POST['apellidos'],
    'nombres' => $_POST['nombres'],
    'tipodoc' => $_POST['tipodoc'],
    'nrodoc' => $_POST['nrodoc'],
    'genero' => $_POST['genero'],
    'telefono' => $_POST['telefono'],
    'email' => $_POST['email'],
    'fechanacimiento' => isset($_POST['fechanacimiento']) && !empty($_POST['fechanacimiento']) ? $_POST['fechanacimiento'] : null,
    'direccion' => isset($_POST['direccion']) ? $_POST['direccion'] : null
];

// Realizar la actualización
$enfermeria = new Enfermeria();
$resultado = $enfermeria->actualizarEnfermero($datos);

// Registrar información de depuración en logs (opcional)
error_log("Datos recibidos: " . print_r($_POST, true));
error_log("Resultado de la actualización: " . print_r($resultado, true));

// Devolver respuesta en formato JSON
echo json_encode($resultado);