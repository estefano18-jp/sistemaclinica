<?php 
require_once '../include/header.administrador.php';
require_once '../../models/Conexion.php';

// Creación de conexión
$conexion = new Conexion();
$conn = $conexion->getConexion();

// Variable para almacenar el ID de la especialidad seleccionada
$selectedEspecialidadId = null;
$selectedEspecialidadNombre = '';
$selectedEspecialidadIcono = '';
$selectedEspecialidadDescCorta = '';
$selectedEspecialidadDescLarga = '';
$selectedEspecialidadImagen = '';
$selectedEspecialidadEstado = 1;
$selectedEspecialidadOrden = 0;
$selectedEspecialidadSistemaId = null; // Nueva variable para la especialidad del sistema
$selectedServicios = [];
$errorMessage = null;
$successMessage = null;

// Verificar si existe el directorio para cargas
$uploadDir = '../../img/especialidades/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Crear directorio con permisos
}

if (!is_writable($uploadDir)) {
    $errorMessage = "Error: El directorio de carga no tiene permisos de escritura.";
    error_log("Directory not writable: " . $uploadDir);
}

// Función para obtener mensajes de error de carga de archivos
function getFileUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return "El archivo subido excede la directiva upload_max_filesize en php.ini.";
        case UPLOAD_ERR_FORM_SIZE:
            return "El archivo subido excede el tamaño máximo permitido por el formulario.";
        case UPLOAD_ERR_PARTIAL:
            return "El archivo se subió parcialmente.";
        case UPLOAD_ERR_NO_FILE:
            return "No se seleccionó ningún archivo para subir.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Falta una carpeta temporal.";
        case UPLOAD_ERR_CANT_WRITE:
            return "No se pudo escribir el archivo en el disco.";
        case UPLOAD_ERR_EXTENSION:
            return "La carga de archivos fue detenida por una extensión PHP.";
        default:
            return "Error desconocido al subir el archivo.";
    }
}

// Crear especialidad web desde una especialidad del sistema
if (isset($_GET['crear_especialidad_web']) && !empty($_GET['crear_especialidad_web'])) {
    $idEspecialidadSistema = intval($_GET['crear_especialidad_web']);
    
    try {
        // Obtener datos de la especialidad del sistema
        $stmt = $conn->prepare("SELECT * FROM especialidades WHERE idespecialidad = ?");
        $stmt->execute([$idEspecialidadSistema]);
        $especialidadSistema = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($especialidadSistema) {
            // Verificar que no exista ya en web_especialidades
            $stmt = $conn->prepare("SELECT COUNT(*) FROM web_especialidades WHERE idespecialidad = ?");
            $stmt->execute([$idEspecialidadSistema]);
            $existe = $stmt->fetchColumn();
            
            if ($existe == 0) {
                // Determinar el ícono basado en el nombre de la especialidad
                $icono = 'fas fa-stethoscope'; // Icono predeterminado
                
                // Asignar icono según palabras clave en el nombre
                $nombre = strtolower($especialidadSistema['especialidad']);
                if (strpos($nombre, 'cardio') !== false) {
                    $icono = 'fas fa-heartbeat';
                } elseif (strpos($nombre, 'neuro') !== false) {
                    $icono = 'fas fa-brain';
                } elseif (strpos($nombre, 'pedia') !== false || strpos($nombre, 'niño') !== false) {
                    $icono = 'fas fa-baby';
                } elseif (strpos($nombre, 'odonto') !== false || strpos($nombre, 'dental') !== false) {
                    $icono = 'fas fa-tooth';
                } elseif (strpos($nombre, 'trauma') !== false || strpos($nombre, 'ortoped') !== false) {
                    $icono = 'fas fa-bone';
                } elseif (strpos($nombre, 'gine') !== false || strpos($nombre, 'obste') !== false) {
                    $icono = 'fas fa-venus';
                } elseif (strpos($nombre, 'derma') !== false || strpos($nombre, 'piel') !== false) {
                    $icono = 'fas fa-allergies';
                } elseif (strpos($nombre, 'oftalmo') !== false || strpos($nombre, 'ojo') !== false) {
                    $icono = 'fas fa-eye';
                } elseif (strpos($nombre, 'psico') !== false || strpos($nombre, 'psiqui') !== false) {
                    $icono = 'fas fa-brain';
                } elseif (strpos($nombre, 'nutri') !== false) {
                    $icono = 'fas fa-apple-alt';
                }
                
                // Crear la especialidad web
                $stmt = $conn->prepare("
                    INSERT INTO web_especialidades 
                    (nombre, icono, descripcion_corta, descripcion_larga, imagen, estado, orden, idespecialidad) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $estadoWeb = ($especialidadSistema['estado'] == 'ACTIVO') ? 1 : 0;
                $descripcionCorta = 'Especialidad médica ' . $especialidadSistema['especialidad'] . ' con atención profesional y personalizada.';
                $descripcionLarga = 'El servicio de ' . $especialidadSistema['especialidad'] . ' de nuestra clínica ofrece diagnóstico y tratamiento de alta calidad por especialistas calificados, con tecnología moderna y atención personalizada para cada paciente.';
                
                $stmt->execute([
                    $especialidadSistema['especialidad'],
                    $icono,
                    $descripcionCorta,
                    $descripcionLarga,
                    'default-specialty.jpg',
                    $estadoWeb,
                    999, // Último orden
                    $idEspecialidadSistema
                ]);
                
                $nuevaEspecialidadId = $conn->lastInsertId();
                
                // Agregar servicios genéricos
                $stmt = $conn->prepare("INSERT INTO web_especialidades_servicios (web_especialidad_id, servicio) VALUES (?, ?)");
                $serviciosGenericos = [
                    'Consulta médica especializada',
                    'Diagnóstico y evaluación',
                    'Seguimiento de tratamientos',
                    'Procedimientos específicos de la especialidad'
                ];
                
                foreach ($serviciosGenericos as $servicio) {
                    $stmt->execute([$nuevaEspecialidadId, $servicio]);
                }
                
                $successMessage = "Especialidad '" . $especialidadSistema['especialidad'] . "' creada en la web correctamente.";
            } else {
                $errorMessage = "La especialidad ya existe en la web.";
            }
        } else {
            $errorMessage = "No se encontró la especialidad del sistema.";
        }
    } catch (PDOException $e) {
        $errorMessage = "Error al crear especialidad: " . $e->getMessage();
    }
}

// Sincronizar todas las especialidades (crear web_especialidades para todas las especialidades del sistema)
if (isset($_GET['sincronizar_todas'])) {
    try {
        // Obtener especialidades del sistema que no están vinculadas a web_especialidades
        $stmt = $conn->prepare("
            SELECT e.* 
            FROM especialidades e
            LEFT JOIN web_especialidades we ON e.idespecialidad = we.idespecialidad
            WHERE we.id IS NULL
            ORDER BY e.especialidad
        ");
        $stmt->execute();
        $especialidadesSinVincular = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $contadorCreadas = 0;
        
        foreach ($especialidadesSinVincular as $esp) {
            // Determinar el ícono basado en el nombre de la especialidad
            $icono = 'fas fa-stethoscope'; // Icono predeterminado
            
            // Asignar icono según palabras clave en el nombre
            $nombre = strtolower($esp['especialidad']);
            if (strpos($nombre, 'cardio') !== false) {
                $icono = 'fas fa-heartbeat';
            } elseif (strpos($nombre, 'neuro') !== false) {
                $icono = 'fas fa-brain';
            } elseif (strpos($nombre, 'pedia') !== false || strpos($nombre, 'niño') !== false) {
                $icono = 'fas fa-baby';
            } elseif (strpos($nombre, 'odonto') !== false || strpos($nombre, 'dental') !== false) {
                $icono = 'fas fa-tooth';
            } elseif (strpos($nombre, 'trauma') !== false || strpos($nombre, 'ortoped') !== false) {
                $icono = 'fas fa-bone';
            } elseif (strpos($nombre, 'gine') !== false || strpos($nombre, 'obste') !== false) {
                $icono = 'fas fa-venus';
            } elseif (strpos($nombre, 'derma') !== false || strpos($nombre, 'piel') !== false) {
                $icono = 'fas fa-allergies';
            } elseif (strpos($nombre, 'oftalmo') !== false || strpos($nombre, 'ojo') !== false) {
                $icono = 'fas fa-eye';
            } elseif (strpos($nombre, 'psico') !== false || strpos($nombre, 'psiqui') !== false) {
                $icono = 'fas fa-brain';
            } elseif (strpos($nombre, 'nutri') !== false) {
                $icono = 'fas fa-apple-alt';
            }
            
            // Crear la especialidad web
            $stmt = $conn->prepare("
                INSERT INTO web_especialidades 
                (nombre, icono, descripcion_corta, descripcion_larga, imagen, estado, orden, idespecialidad) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $estadoWeb = ($esp['estado'] == 'ACTIVO') ? 1 : 0;
            $descripcionCorta = 'Especialidad médica ' . $esp['especialidad'] . ' con atención profesional y personalizada.';
            $descripcionLarga = 'El servicio de ' . $esp['especialidad'] . ' de nuestra clínica ofrece diagnóstico y tratamiento de alta calidad por especialistas calificados, con tecnología moderna y atención personalizada para cada paciente.';
            
            $stmt->execute([
                $esp['especialidad'],
                $icono,
                $descripcionCorta,
                $descripcionLarga,
                'default-specialty.jpg',
                $estadoWeb,
                999, // Último orden
                $esp['idespecialidad']
            ]);
            
            $nuevaEspecialidadId = $conn->lastInsertId();
            
            // Agregar servicios genéricos
            $stmt = $conn->prepare("INSERT INTO web_especialidades_servicios (web_especialidad_id, servicio) VALUES (?, ?)");
            $serviciosGenericos = [
                'Consulta médica especializada',
                'Diagnóstico y evaluación',
                'Seguimiento de tratamientos',
                'Procedimientos específicos de la especialidad'
            ];
            
            foreach ($serviciosGenericos as $servicio) {
                $stmt->execute([$nuevaEspecialidadId, $servicio]);
            }
            
            $contadorCreadas++;
        }
        
        if ($contadorCreadas > 0) {
            $successMessage = "Se han creado $contadorCreadas especialidades web automáticamente.";
        } else {
            $successMessage = "Todas las especialidades ya están sincronizadas.";
        }
    } catch (PDOException $e) {
        $errorMessage = "Error al sincronizar especialidades: " . $e->getMessage();
    }
}

// Vincular especialidades automáticamente si se solicita
if (isset($_GET['vincular_especialidades'])) {
    try {
        $stmt = $conn->prepare("CALL VincularEspecialidadesWeb()");
        $stmt->execute();
        $successMessage = "Especialidades vinculadas automáticamente con éxito.";
    } catch (PDOException $e) {
        $errorMessage = "Error al vincular especialidades: " . $e->getMessage();
    }
}

// Eliminar especialidad si se solicita
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $idEliminar = $_GET['delete'];
    
    try {
        // Primero obtener la imagen
        $stmt = $conn->prepare("SELECT imagen FROM web_especialidades WHERE id = ?");
        $stmt->execute([$idEliminar]);
        $imagenEliminar = $stmt->fetchColumn();
        
        // Eliminar de la base de datos (los servicios se eliminarán automáticamente por ON DELETE CASCADE)
        $stmt = $conn->prepare("DELETE FROM web_especialidades WHERE id = ?");
        $stmt->execute([$idEliminar]);
        
        if ($stmt->rowCount() > 0) {
            // Intentar eliminar el archivo físico si existe
            $rutaImagen = $uploadDir . $imagenEliminar;
            if ($imagenEliminar && $imagenEliminar != 'default-specialty.jpg' && file_exists($rutaImagen)) {
                unlink($rutaImagen);
            }
            
            $successMessage = "Especialidad eliminada correctamente.";
        } else {
            $errorMessage = "No se pudo eliminar la especialidad.";
        }
    } catch (PDOException $e) {
        $errorMessage = "Error al eliminar: " . $e->getMessage();
    }
}

// Manejo de formulario enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si es una actualización o inserción
    $isUpdate = isset($_POST['especialidadId']) && !empty($_POST['especialidadId']);
    $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
    $icono = isset($_POST['icono']) ? $_POST['icono'] : '';
    $descripcion_corta = isset($_POST['descripcion_corta']) ? $_POST['descripcion_corta'] : '';
    $descripcion_larga = isset($_POST['descripcion_larga']) ? $_POST['descripcion_larga'] : '';
    $orden = isset($_POST['orden']) ? intval($_POST['orden']) : 0;
    $estado = isset($_POST['estado']) ? 1 : 0;
    $idespecialidad = isset($_POST['idespecialidad']) && !empty($_POST['idespecialidad']) ? $_POST['idespecialidad'] : null;
    $servicios = isset($_POST['servicios']) ? $_POST['servicios'] : [];
    
    // Eliminar servicios vacíos
    $servicios = array_filter($servicios, function($servicio) {
        return !empty(trim($servicio));
    });
    
    // Procesar la imagen si se ha seleccionado
    $hasNewImage = isset($_FILES['imagenEspecialidad']) && $_FILES['imagenEspecialidad']['error'] == 0;
    
    if ($isUpdate) {
        // CASO DE ACTUALIZACIÓN
        $idEspecialidad = $_POST['especialidadId'];
        
        try {
            // Obtenemos el nombre de archivo actual desde la base de datos
            $stmt = $conn->prepare("SELECT imagen FROM web_especialidades WHERE id = ?");
            $stmt->execute([$idEspecialidad]);
            $imagenActual = $stmt->fetchColumn();
            
            if ($hasNewImage) {
                // Con nueva imagen
                $fileTmpName = $_FILES['imagenEspecialidad']['tmp_name'];
                $fileType = $_FILES['imagenEspecialidad']['type'];
                $fileExt = pathinfo($_FILES['imagenEspecialidad']['name'], PATHINFO_EXTENSION);
                
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                
                if (!in_array($fileType, $allowedTypes)) {
                    $errorMessage = "Error: solo se permiten imágenes JPEG, PNG o GIF.";
                } else {
                    // Generar un nombre único para la imagen
                    $nombreImagen = 'especialidad-' . time() . '.' . $fileExt;
                    $uploadFile = $uploadDir . $nombreImagen;
                    
                    // Subir la nueva imagen
                    if (move_uploaded_file($fileTmpName, $uploadFile)) {
                        // Eliminar la imagen anterior si existe y no es la predeterminada
                        if ($imagenActual && $imagenActual != 'default-specialty.jpg' && file_exists($uploadDir . $imagenActual)) {
                            unlink($uploadDir . $imagenActual);
                        }
                        
                        // Actualizamos los datos en la base de datos con la nueva imagen
                        $stmt = $conn->prepare("UPDATE web_especialidades SET nombre = ?, icono = ?, descripcion_corta = ?, descripcion_larga = ?, imagen = ?, estado = ?, orden = ?, idespecialidad = ? WHERE id = ?");
                        $stmt->execute([$nombre, $icono, $descripcion_corta, $descripcion_larga, $nombreImagen, $estado, $orden, $idespecialidad, $idEspecialidad]);
                        
                        $successMessage = "Especialidad actualizada correctamente con nueva imagen.";
                    } else {
                        $errorMessage = "Error al subir la imagen. " . getFileUploadErrorMessage($_FILES['imagenEspecialidad']['error']);
                    }
                }
            } else {
                // Sin nueva imagen: actualizar solo datos
                $stmt = $conn->prepare("UPDATE web_especialidades SET nombre = ?, icono = ?, descripcion_corta = ?, descripcion_larga = ?, estado = ?, orden = ?, idespecialidad = ? WHERE id = ?");
                $stmt->execute([$nombre, $icono, $descripcion_corta, $descripcion_larga, $estado, $orden, $idespecialidad, $idEspecialidad]);
                
                if ($stmt->rowCount() >= 0) {
                    $successMessage = "Especialidad actualizada correctamente.";
                } else {
                    $errorMessage = "No se pudo actualizar la especialidad o no se realizaron cambios.";
                }
            }
            
            // Actualizar servicios (eliminar y crear nuevos)
            if (!$errorMessage) {
                // Primero, eliminar todos los servicios existentes
                $stmt = $conn->prepare("DELETE FROM web_especialidades_servicios WHERE web_especialidad_id = ?");
                $stmt->execute([$idEspecialidad]);
                
                // Luego, insertar los nuevos servicios
                if (!empty($servicios)) {
                    $stmt = $conn->prepare("INSERT INTO web_especialidades_servicios (web_especialidad_id, servicio) VALUES (?, ?)");
                    foreach ($servicios as $servicio) {
                        if (!empty(trim($servicio))) {
                            $stmt->execute([$idEspecialidad, trim($servicio)]);
                        }
                    }
                }
                
                if (!$successMessage) {
                    $successMessage = "Servicios actualizados correctamente.";
                }
            }
        } catch (PDOException $e) {
            $errorMessage = "Error en la base de datos: " . $e->getMessage();
        }
    } else {
        // CASO DE INSERCIÓN
        try {
            $nombreImagen = 'default-specialty.jpg'; // Imagen predeterminada
            
            if ($hasNewImage) {
                $fileTmpName = $_FILES['imagenEspecialidad']['tmp_name'];
                $fileType = $_FILES['imagenEspecialidad']['type'];
                $fileExt = pathinfo($_FILES['imagenEspecialidad']['name'], PATHINFO_EXTENSION);
                
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                
                if (!in_array($fileType, $allowedTypes)) {
                    $errorMessage = "Error: solo se permiten imágenes JPEG, PNG o GIF.";
                } else {
                    // Generar un nombre único para la imagen
                    $nombreImagen = 'especialidad-' . time() . '.' . $fileExt;
                    $uploadFile = $uploadDir . $nombreImagen;
                    
                    if (!move_uploaded_file($fileTmpName, $uploadFile)) {
                        $errorMessage = "Error al subir la imagen. " . getFileUploadErrorMessage($_FILES['imagenEspecialidad']['error']);
                        $nombreImagen = 'default-specialty.jpg'; // Usar imagen predeterminada
                    }
                }
            }
            
            if (!$errorMessage) {
                // Insertar especialidad
                $stmt = $conn->prepare("INSERT INTO web_especialidades (nombre, icono, descripcion_corta, descripcion_larga, imagen, estado, orden, idespecialidad) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $icono, $descripcion_corta, $descripcion_larga, $nombreImagen, $estado, $orden, $idespecialidad]);
                
                if ($stmt->rowCount() > 0) {
                    $nuevaEspecialidadId = $conn->lastInsertId();
                    
                    // Insertar servicios
                    if (!empty($servicios)) {
                        $stmt = $conn->prepare("INSERT INTO web_especialidades_servicios (web_especialidad_id, servicio) VALUES (?, ?)");
                        foreach ($servicios as $servicio) {
                            if (!empty(trim($servicio))) {
                                $stmt->execute([$nuevaEspecialidadId, trim($servicio)]);
                            }
                        }
                    }
                    
                    $successMessage = "Especialidad agregada correctamente.";
                } else {
                    $errorMessage = "No se pudo registrar la especialidad en la base de datos.";
                }
            }
        } catch (PDOException $e) {
            $errorMessage = "Error en la base de datos: " . $e->getMessage();
        }
    }
}

// Si se selecciona una especialidad para editar
if (isset($_GET['id'])) {
    $selectedEspecialidadId = $_GET['id'];
    try {
        // Obtener datos de la especialidad
        $stmt = $conn->prepare("SELECT * FROM web_especialidades WHERE id = ?");
        $stmt->execute([$selectedEspecialidadId]);
        $selectedEspecialidad = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($selectedEspecialidad) {
            $selectedEspecialidadNombre = $selectedEspecialidad['nombre'];
            $selectedEspecialidadIcono = $selectedEspecialidad['icono'];
            $selectedEspecialidadDescCorta = $selectedEspecialidad['descripcion_corta'];
            $selectedEspecialidadDescLarga = $selectedEspecialidad['descripcion_larga'];
            $selectedEspecialidadImagen = $selectedEspecialidad['imagen'];
            $selectedEspecialidadEstado = $selectedEspecialidad['estado'];
            $selectedEspecialidadOrden = $selectedEspecialidad['orden'];
            $selectedEspecialidadSistemaId = $selectedEspecialidad['idespecialidad']; // Obtener el ID de la especialidad del sistema
            
            // Obtener servicios de la especialidad
            $stmt = $conn->prepare("SELECT servicio FROM web_especialidades_servicios WHERE web_especialidad_id = ?");
            $stmt->execute([$selectedEspecialidadId]);
            $selectedServicios = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $errorMessage = "No se encontró la especialidad seleccionada.";
        }
    } catch (PDOException $e) {
        $errorMessage = "Error al obtener datos de la especialidad: " . $e->getMessage();
    }
}

// Obtener todas las especialidades
try {
    $stmt = $conn->prepare("SELECT e.*, (SELECT COUNT(*) FROM web_especialidades_servicios WHERE web_especialidad_id = e.id) AS total_servicios FROM web_especialidades e ORDER BY e.orden ASC, e.id DESC");
    $stmt->execute();
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Error al obtener especialidades: " . $e->getMessage();
    $especialidades = [];
}

// Generar un código único para prevenir el cache de imágenes
$cacheBuster = time();

// Lista de iconos de FontAwesome para especialidades médicas
$iconosList = [
    'fas fa-heartbeat' => 'Corazón (heartbeat)',
    'fas fa-heart' => 'Corazón (heart)',
    'fas fa-brain' => 'Cerebro (brain)',
    'fas fa-lungs' => 'Pulmones (lungs)',
    'fas fa-eye' => 'Ojo (eye)',
    'fas fa-tooth' => 'Diente (tooth)',
    'fas fa-bone' => 'Hueso (bone)',
    'fas fa-baby' => 'Bebé (baby)',
    'fas fa-child' => 'Niño (child)',
    'fas fa-user-md' => 'Doctor (user-md)',
    'fas fa-stethoscope' => 'Estetoscopio (stethoscope)',
    'fas fa-wheelchair' => 'Silla de ruedas (wheelchair)',
    'fas fa-procedures' => 'Procedimientos (procedures)',
    'fas fa-notes-medical' => 'Notas médicas (notes-medical)',
    'fas fa-pills' => 'Píldoras (pills)',
    'fas fa-capsules' => 'Cápsulas (capsules)',
    'fas fa-prescription-bottle-alt' => 'Medicinas (prescription-bottle-alt)',
    'fas fa-dna' => 'ADN (dna)',
    'fas fa-microscope' => 'Microscopio (microscope)',
    'fas fa-virus' => 'Virus (virus)',
    'fas fa-bacteria' => 'Bacteria (bacteria)',
    'fas fa-allergies' => 'Alergias (allergies)',
    'fas fa-venus' => 'Venus/Mujer (venus)',
    'fas fa-mars' => 'Marte/Hombre (mars)',
    'fas fa-x-ray' => 'Rayos X (x-ray)',
    'fas fa-briefcase-medical' => 'Maletín médico (briefcase-medical)',
    'fas fa-hospital' => 'Hospital (hospital)',
    'fas fa-diagnoses' => 'Diagnóstico (diagnoses)',
    'fas fa-crutch' => 'Muleta (crutch)',
    'fas fa-weight' => 'Peso (weight)',
    'fas fa-apple-alt' => 'Manzana/Nutrición (apple-alt)'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Especialidades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .avatar-container {
            position: relative;
            margin-bottom: 15px;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .avatar-container img {
            width: 100%;
            max-width: 300px;
            height: auto;
            border-radius: 8px;
            border: 2px solid #ddd;
            transition: all 0.3s ease;
        }
        .avatar-container:hover img {
            transform: scale(1.02);
        }
        .edit-icon {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: rgba(0,0,0,0.6);
            color: white;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .edit-icon:hover {
            background-color: rgba(0,0,0,0.8);
            transform: scale(1.1);
        }
        .selected-especialidad {
            border: 3px solid #198754 !important;
            box-shadow: 0 0 15px rgba(25, 135, 84, 0.5) !important;
        }
        .especialidad-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        .especialidad-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .especialidad-card .card-body {
            padding: 1.5rem;
        }
        .especialidad-card img {
            height: 180px;
            object-fit: cover;
            width: 100%;
        }
        .info-table {
            width: 100%;
            margin-bottom: 1rem;
        }
        .info-table td {
            padding: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 30%;
        }
        .btn-edit {
            background-color: #f0ad4e;
            border-color: #f0ad4e;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-edit:hover {
            background-color: #ec971f;
            border-color: #ec971f;
            color: white;
            transform: scale(1.05);
        }
        .section-title {
            position: relative;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: #0d6efd;
        }
        .form-label {
            font-weight: 500;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .custom-file-button {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .custom-file-button:hover {
            transform: translateY(-2px);
        }
        .submit-btn {
            transition: all 0.3s ease;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .service-item {
            position: relative;
            padding-right: 40px;
            margin-bottom: 10px;
        }
        .remove-service {
            position: absolute;
            right: 0;
            top: 0;
            color: #dc3545;
            background: none;
            border: none;
            cursor: pointer;
        }
        .remove-service:hover {
            color: #bd2130;
        }
        .add-service-btn {
            margin-top: 10px;
        }
        .badge-counter {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 2;
        }
        .icon-preview {
            font-size: 2rem;
            margin: 10px 0;
            text-align: center;
            color: #0d6efd;
        }
        .badge-sistema {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 2;
            background-color: #17a2b8;
        }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestionar Especialidades</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="mb-0 section-title">
                        <?php if ($selectedEspecialidadId): ?>
                            <i class="fas fa-edit me-2 text-warning"></i> Editar Especialidad
                        <?php else: ?>
                            <i class="fas fa-plus-circle me-2 text-primary"></i> Nueva Especialidad
                        <?php endif; ?>
                    </h5>
                    <div>
                        <?php if ($selectedEspecialidadId): ?>
                            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-sm btn-outline-primary me-2">
                                <i class="fas fa-plus"></i> Nueva Especialidad
                            </a>
                        <?php endif; ?>
                        <a href="?vincular_especialidades=1" class="btn btn-sm btn-warning me-2">
                            <i class="fas fa-link"></i> Vincular especialidades automáticamente
                        </a>
                        <a href="?sincronizar_todas=1" class="btn btn-sm btn-info">
                            <i class="fas fa-sync"></i> Sincronizar todas las especialidades
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                        <input type="hidden" id="especialidadId" name="especialidadId" value="<?php echo $selectedEspecialidadId; ?>">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="avatar-container">
                                    <?php if ($selectedEspecialidadId && !empty($selectedEspecialidadImagen)): ?>
                                        <img id="avatarPreview" src="../../img/especialidades/<?php echo htmlspecialchars($selectedEspecialidadImagen); ?>?v=<?php echo $cacheBuster; ?>" alt="Imagen de Especialidad" onerror="this.src='../../img/especialidades/default-specialty.jpg'">
                                    <?php else: ?>
                                        <img id="avatarPreview" src="../../img/especialidades/default-specialty.jpg" alt="Imagen de Especialidad">
                                    <?php endif; ?>
                                    <div class="edit-icon">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                </div>
                                <div class="file-input-container mt-3">
                                    <button type="button" class="btn btn-outline-primary w-100 custom-file-button" onclick="document.getElementById('imagenEspecialidad').click()">
                                        <i class="fas fa-upload me-2"></i> Seleccionar imagen
                                    </button>
                                    <input type="file" id="imagenEspecialidad" name="imagenEspecialidad" accept="image/*" onchange="previewImage(event)" style="display: none;">
                                </div>
                                <div class="mt-2 text-muted small">
                                    <p>Formatos permitidos: JPG, PNG, GIF</p>
                                    <p>Dimensiones recomendadas: 800 x 600 px</p>
                                </div>
                                
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="estado" name="estado" <?php echo $selectedEspecialidadEstado ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="estado">
                                        Especialidad activa
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre de la especialidad</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($selectedEspecialidadNombre); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="icono" class="form-label">Ícono</label>
                                    <select class="form-select" id="icono" name="icono" onchange="actualizarIconoPreview()">
                                        <?php foreach ($iconosList as $iconoValue => $iconoName): ?>
                                            <option value="<?php echo $iconoValue; ?>" <?php echo ($selectedEspecialidadIcono == $iconoValue) ? 'selected' : ''; ?>>
                                                <?php echo $iconoName; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="icon-preview mt-2">
                                        <i id="iconoPreview" class="<?php echo htmlspecialchars($selectedEspecialidadIcono ?: 'fas fa-heartbeat'); ?>"></i>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="orden" class="form-label">Orden (menor número = mayor prioridad)</label>
                                    <input type="number" class="form-control" id="orden" name="orden" min="0" value="<?php echo htmlspecialchars($selectedEspecialidadOrden); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="idespecialidad" class="form-label">Vincular con especialidad del sistema</label>
                                    <select class="form-select" id="idespecialidad" name="idespecialidad">
                                        <option value="">-- Sin vincular --</option>
                                        <?php
                                        try {
                                            $stmtEspecialidades = $conn->prepare("SELECT idespecialidad, especialidad, estado FROM especialidades ORDER BY especialidad");
                                            $stmtEspecialidades->execute();
                                            $especialidadesSistema = $stmtEspecialidades->fetchAll(PDO::FETCH_ASSOC);
                                            
                                            foreach ($especialidadesSistema as $esp) {
                                                $selected = ($selectedEspecialidadSistemaId && $esp['idespecialidad'] == $selectedEspecialidadSistemaId) ? 'selected' : '';
                                                $estadoText = $esp['estado'] == 'ACTIVO' ? '✓' : '✗';
                                                echo '<option value="' . $esp['idespecialidad'] . '" ' . $selected . '>' . 
                                                    htmlspecialchars($esp['especialidad']) . ' (' . $estadoText . ')</option>';
                                            }
                                        } catch (PDOException $e) {
                                            echo '<option value="">Error al cargar especialidades del sistema</option>';
                                        }
                                        ?>
                                    </select>
                                    <div class="form-text text-muted">
                                        Selecciona la especialidad del sistema a la que corresponde esta especialidad web.
                                        Esto permitirá que los médicos de esta especialidad aparezcan correctamente en la web.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="descripcion_corta" class="form-label">Descripción corta (para listados)</label>
                                    <textarea class="form-control" id="descripcion_corta" name="descripcion_corta" rows="2" required><?php echo htmlspecialchars($selectedEspecialidadDescCorta); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="descripcion_larga" class="form-label">Descripción larga (detallada)</label>
                                    <textarea class="form-control" id="descripcion_larga" name="descripcion_larga" rows="4"><?php echo htmlspecialchars($selectedEspecialidadDescLarga); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Servicios ofrecidos</label>
                                    <div id="serviciosContainer">
                                        <?php if (empty($selectedServicios)): ?>
                                            <div class="service-item">
                                                <input type="text" class="form-control" name="servicios[]" placeholder="Nombre del servicio">
                                                <button type="button" class="remove-service" onclick="removeService(this)">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($selectedServicios as $servicio): ?>
                                                <div class="service-item">
                                                    <input type="text" class="form-control" name="servicios[]" value="<?php echo htmlspecialchars($servicio); ?>" placeholder="Nombre del servicio">
                                                    <button type="button" class="remove-service" onclick="removeService(this)">
                                                        <i class="fas fa-times-circle"></i>
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary add-service-btn" onclick="addService()">
                                        <i class="fas fa-plus"></i> Agregar servicio
                                    </button>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 submit-btn">
                                    <?php if ($selectedEspecialidadId): ?>
                                        <i class="fas fa-save me-2"></i> Guardar Cambios
                                    <?php else: ?>
                                        <i class="fas fa-save me-2"></i> Guardar Especialidad
                                    <?php endif; ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 section-title"><i class="fas fa-list me-2 text-primary"></i> Especialidades Disponibles</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if (count($especialidades) > 0): ?>
                            <?php foreach ($especialidades as $especialidad): ?>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="especialidad-card card <?php echo ($selectedEspecialidadId == $especialidad['id']) ? 'selected-especialidad' : ''; ?>">
                                        <div class="position-relative">
                                            <span class="badge badge-counter bg-primary">
                                                <?php echo $especialidad['total_servicios']; ?> servicios
                                            </span>
                                            
                                            <?php if (!empty($especialidad['idespecialidad'])): ?>
                                                <span class="badge badge-sistema bg-info">
                                                    <i class="fas fa-link"></i> Vinculada
                                                </span>
                                            <?php endif; ?>
                                            
                                            <img src="../../img/especialidades/<?php echo htmlspecialchars($especialidad['imagen']); ?>?v=<?php echo $cacheBuster; ?>" class="card-img-top" alt="Imagen especialidad" onerror="this.src='../../img/especialidades/default-specialty.jpg'">
                                            <div class="card-img-overlay d-flex align-items-start justify-content-between">
                                                <span class="badge <?php echo $especialidad['estado'] ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo $especialidad['estado'] ? 'Activa' : 'Inactiva'; ?>
                                                </span>
                                                <div>
                                                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $especialidad['id']; ?>" class="btn btn-sm btn-warning me-1">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-danger" onclick="eliminarEspecialidad(<?php echo $especialidad['id']; ?>, '<?php echo addslashes($especialidad['nombre']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="<?php echo htmlspecialchars($especialidad['icono']); ?> text-primary me-2"></i>
                                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($especialidad['nombre']); ?></h5>
                                            </div>
                                            <p class="card-text small"><?php echo htmlspecialchars($especialidad['descripcion_corta']); ?></p>
                                            
                                            <?php if (!empty($especialidad['idespecialidad'])): ?>
                                                <?php
                                                try {
                                                    $stmtEspecialidadSistema = $conn->prepare("SELECT e.especialidad, e.estado, 
                                                        (SELECT COUNT(*) FROM colaboradores c WHERE c.idespecialidad = e.idespecialidad AND c.estado = 'ACTIVO') AS total_doctores
                                                        FROM especialidades e WHERE e.idespecialidad = ?");
                                                    $stmtEspecialidadSistema->execute([$especialidad['idespecialidad']]);
                                                    $especialidadSistema = $stmtEspecialidadSistema->fetch(PDO::FETCH_ASSOC);
                                                    
                                                    if ($especialidadSistema) {
                                                        echo '<div class="alert alert-info py-1 px-2 mt-2 mb-2 small">';
                                                        echo '<strong>Vinculada a:</strong> ' . htmlspecialchars($especialidadSistema['especialidad']);
                                                        echo ' <span class="badge ' . ($especialidadSistema['estado'] == 'ACTIVO' ? 'bg-success' : 'bg-danger') . '">';
                                                        echo $especialidadSistema['estado'] . '</span>';
                                                        echo '</div>';
                                                        
                                                        // Mostrar información de doctores
                                                        if ($especialidadSistema['total_doctores'] > 0) {
                                                            echo '<div class="alert alert-success py-1 px-2 mt-1 mb-2 small">';
                                                            echo '<i class="fas fa-user-md me-1"></i> ' . $especialidadSistema['total_doctores'] . ' médicos disponibles';
                                                            echo '</div>';
                                                        } else {
                                                            echo '<div class="alert alert-warning py-1 px-2 mt-1 mb-2 small">';
                                                            echo '<i class="fas fa-exclamation-triangle me-1"></i> Sin médicos asignados';
                                                            echo '</div>';
                                                        }
                                                    }
                                                } catch (PDOException $e) {
                                                    // Silenciar error
                                                }
                                                ?>
                                            <?php else: ?>
                                                <div class="alert alert-warning py-1 px-2 mt-2 mb-2 small">
                                                    <i class="fas fa-exclamation-triangle me-1"></i> No vinculada al sistema
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($especialidad['total_servicios'] > 0): ?>
                                                <div class="mt-3">
                                                    <strong>Servicios:</strong>
                                                    <ul class="small mt-1 ps-3">
                                                        <?php
                                                        try {
                                                            $stmtServicios = $conn->prepare("SELECT servicio FROM web_especialidades_servicios WHERE web_especialidad_id = ? LIMIT 4");
                                                            $stmtServicios->execute([$especialidad['id']]);
                                                            $servicios = $stmtServicios->fetchAll(PDO::FETCH_COLUMN);
                                                            
                                                            foreach ($servicios as $index => $servicio) {
                                                                echo '<li>' . htmlspecialchars($servicio) . '</li>';
                                                            }
                                                            
                                                            if ($especialidad['total_servicios'] > 4) {
                                                                echo '<li>Y ' . ($especialidad['total_servicios'] - 4) . ' más...</li>';
                                                            }
                                                        } catch (PDOException $e) {
                                                            echo '<li class="text-danger">Error al cargar servicios</li>';
                                                        }
                                                        ?>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer text-center">
                                            <small class="text-muted">Orden: <?php echo $especialidad['orden']; ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> No hay especialidades registradas. Utilice el formulario superior para agregar una nueva especialidad.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 section-title">
                        <i class="fas fa-exclamation-triangle me-2 text-warning"></i> Especialidades del sistema no vinculadas
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    try {
                        // Obtener especialidades del sistema que no están vinculadas a web_especialidades
                        $stmt = $conn->prepare("
                            SELECT e.*, 
                            (SELECT COUNT(*) FROM colaboradores c WHERE c.idespecialidad = e.idespecialidad AND c.estado = 'ACTIVO') AS total_doctores
                            FROM especialidades e
                            LEFT JOIN web_especialidades we ON e.idespecialidad = we.idespecialidad
                            WHERE we.id IS NULL
                            ORDER BY e.especialidad
                        ");
                        $stmt->execute();
                        $especialidadesSinVincular = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($especialidadesSinVincular) > 0) {
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-hover">';
                            echo '<thead><tr><th>Especialidad</th><th>Estado</th><th>Médicos</th><th>Acciones</th></tr></thead>';
                            echo '<tbody>';
                            
                            foreach ($especialidadesSinVincular as $esp) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($esp['especialidad']) . '</td>';
                                echo '<td><span class="badge ' . ($esp['estado'] == 'ACTIVO' ? 'bg-success' : 'bg-danger') . '">' . $esp['estado'] . '</span></td>';
                                echo '<td>' . $esp['total_doctores'] . ' médicos</td>';
                                echo '<td>';
                                echo '<a href="?crear_especialidad_web=' . $esp['idespecialidad'] . '" class="btn btn-sm btn-primary">';
                                echo '<i class="fas fa-plus-circle me-1"></i> Crear en Web';
                                echo '</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                            
                            echo '</tbody></table>';
                            echo '</div>';
                        } else {
                            echo '<div class="alert alert-success">';
                            echo '<i class="fas fa-check-circle me-2"></i> Todas las especialidades del sistema están vinculadas con la web.';
                            echo '</div>';
                        }
                    } catch (PDOException $e) {
                        echo '<div class="alert alert-danger">';
                        echo '<i class="fas fa-exclamation-circle me-2"></i> Error al verificar especialidades: ' . $e->getMessage();
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Script para SweetAlert -->
<script src="../../js/alertas.js"></script>

<script>
    function previewImage(event) {
        if (event.target.files && event.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    }
    
    function actualizarIconoPreview() {
        const iconoSelect = document.getElementById('icono');
        const iconoPreview = document.getElementById('iconoPreview');
        
        // Eliminar todas las clases existentes
        iconoPreview.className = '';
        
        // Agregar la clase seleccionada
        iconoPreview.className = iconoSelect.value;
    }
    
    function addService() {
        const container = document.getElementById('serviciosContainer');
        const serviceDiv = document.createElement('div');
        serviceDiv.className = 'service-item';
        serviceDiv.innerHTML = `
            <input type="text" class="form-control" name="servicios[]" placeholder="Nombre del servicio">
            <button type="button" class="remove-service" onclick="removeService(this)">
                <i class="fas fa-times-circle"></i>
            </button>
        `;
        container.appendChild(serviceDiv);
    }
    
    function removeService(button) {
        const container = document.getElementById('serviciosContainer');
        const serviceItem = button.parentNode;
        
        // Asegurarse de que al menos quede un servicio
        if (container.children.length > 1) {
            container.removeChild(serviceItem);
        } else {
            // Si es el último, solo limpiamos su valor
            serviceItem.querySelector('input').value = '';
        }
    }
    
    function eliminarEspecialidad(id, nombre) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Deseas eliminar la especialidad "' + nombre + '"?\nEsta acción también eliminará todos los servicios asociados.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>?delete=' + id;
            }
        });
    }

    // Mostrar alertas usando SweetAlert si hay mensajes
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($errorMessage): ?>
            AlertaSweetAlert('error', 'Error', '<?php echo addslashes($errorMessage); ?>', '');
        <?php endif; ?>
        
        <?php if ($successMessage): ?>
            AlertaSweetAlert('success', 'Correcto', '<?php echo addslashes($successMessage); ?>', '');
        <?php endif; ?>
        
        // Inicializar la vista previa del ícono
        actualizarIconoPreview();
    });
</script>

</body>
</html>

<?php require_once '../include/footer.php'; ?>