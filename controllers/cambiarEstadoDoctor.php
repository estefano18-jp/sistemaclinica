<?php
require_once '../models/Doctor.php';

// Verificar que se reciba el método POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capturar el número de documento
    $nrodoc = isset($_POST['nrodoc']) ? $_POST['nrodoc'] : '';
    
    if (empty($nrodoc)) {
        echo json_encode([
            'status' => false,
            'mensaje' => 'El número de documento es requerido'
        ]);
        exit;
    }
    
    // Cambiar el estado del doctor
    $doctor = new Doctor();
    $resultado = $doctor->cambiarEstadoDoctor($nrodoc);
    
    echo json_encode($resultado);
} else {
    echo json_encode([
        'status' => false,
        'mensaje' => 'Método de solicitud no válido'
    ]);
}