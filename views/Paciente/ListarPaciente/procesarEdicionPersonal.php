<?php
require_once '../../../models/Paciente.php';

// Comprobar que es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => false,
        'mensaje' => 'Método no permitido'
    ]);
    exit;
}

// Verificar operación solicitada
if (!isset($_POST['operacion']) || $_POST['operacion'] !== 'actualizar') {
    echo json_encode([
        'status' => false,
        'mensaje' => 'Operación no válida'
    ]);
    exit;
}

// Verificar que se ha proporcionado un ID de paciente
if (!isset($_POST['idpaciente']) || empty($_POST['idpaciente'])) {
    echo json_encode([
        'status' => false,
        'mensaje' => 'ID de paciente no proporcionado'
    ]);
    exit;
}

// Comprobar campos obligatorios
$camposRequeridos = ['apellidos', 'nombres', 'tipodoc', 'nrodoc', 'telefono', 'fechanacimiento', 'genero', 'direccion'];
foreach ($camposRequeridos as $campo) {
    if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
        echo json_encode([
            'status' => false,
            'mensaje' => 'El campo ' . $campo . ' es obligatorio'
        ]);
        exit;
    }
}

// Preparar los datos para actualizar
$params = [
    'idpaciente' => $_POST['idpaciente'],
    'apellidos' => $_POST['apellidos'],
    'nombres' => $_POST['nombres'],
    'tipodoc' => $_POST['tipodoc'],
    'nrodoc' => $_POST['nrodoc'],
    'telefono' => $_POST['telefono'],
    'fechanacimiento' => $_POST['fechanacimiento'],
    'genero' => $_POST['genero'],
    'direccion' => $_POST['direccion'],
    'email' => isset($_POST['email']) ? $_POST['email'] : ''
];

// Actualizar los datos del paciente
$paciente = new Paciente();
$resultado = $paciente->actualizar($params);

// Devolver respuesta en formato JSON
echo json_encode([
    'status' => isset($resultado['resultado']) && $resultado['resultado'] == 1,
    'mensaje' => isset($resultado['mensaje']) ? $resultado['mensaje'] : 'La operación se completó satisfactoriamente'
]);