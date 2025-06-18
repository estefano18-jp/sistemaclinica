<?php 
require_once '../include/header.administrador.php';
require_once '../../models/Conexion.php';

// Creación de conexión
$conexion = new Conexion();
$conn = $conexion->getConexion();

// Variable para almacenar el ID de la imagen seleccionada
$selectedImageId = null;
$selectedImageDescripcion = '';
$selectedImageTitulo = '';
$selectedImageTexto = '';
$selectedImageBotonTexto = '';
$selectedImageBotonEnlace = '';
$errorMessage = null;
$successMessage = null;

// Verificar si existe el directorio para cargas
$uploadDir = '../../img/carrusel/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Crear directorio con permisos
}

if (!is_writable($uploadDir)) {
    $errorMessage = "Error: El directorio de carga no tiene permisos de escritura.";
    error_log("Directory not writable: " . $uploadDir);
}

// Definir nombres fijos para las imágenes del carrusel
$imagenesCarrusel = [
    'imagenCarousel01.jpg',
    'imagenCarousel02.jpg',
    'imagenCarousel03.jpg'
];

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

// Contar el número total de imágenes en el carrusel
try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM carrusel");
    $stmt->execute();
    $totalImagenes = $stmt->fetchColumn();
} catch (PDOException $e) {
    $errorMessage = "Error al contar imágenes: " . $e->getMessage();
    $totalImagenes = 0;
}

// Manejo de imagen subida
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si es una actualización o inserción
    $isUpdate = isset($_POST['imagenId']) && !empty($_POST['imagenId']);
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
    $titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
    $texto = isset($_POST['texto']) ? $_POST['texto'] : '';
    $botonTexto = isset($_POST['boton_texto']) ? $_POST['boton_texto'] : '';
    $botonEnlace = isset($_POST['boton_enlace']) ? $_POST['boton_enlace'] : '';
    
    // Procesar la imagen si se ha seleccionado
    $hasNewImage = isset($_FILES['imagenCarrusel']) && $_FILES['imagenCarrusel']['error'] == 0;
    
    if ($isUpdate) {
        // CASO DE ACTUALIZACIÓN
        $idImagen = $_POST['imagenId'];
        
        try {
            // Obtenemos el nombre de archivo actual desde la base de datos
            $stmt = $conn->prepare("SELECT imagen FROM carrusel WHERE id = ?");
            $stmt->execute([$idImagen]);
            $imagenActual = $stmt->fetchColumn();
            
            if ($imagenActual) {
                if ($hasNewImage) {
                    // Con nueva imagen: actualizar imagen manteniendo el mismo nombre
                    $fileTmpName = $_FILES['imagenCarrusel']['tmp_name'];
                    $fileType = $_FILES['imagenCarrusel']['type'];
                    
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    
                    if (!in_array($fileType, $allowedTypes)) {
                        $errorMessage = "Error: solo se permiten imágenes JPEG, PNG o GIF.";
                    } else {
                        // Conservamos el mismo nombre para mantener la compatibilidad
                        $uploadFile = $uploadDir . $imagenActual;
                        
                        // Reemplazar el archivo físico
                        if (move_uploaded_file($fileTmpName, $uploadFile)) {
                            // Actualizamos los datos en la base de datos
                            $stmt = $conn->prepare("UPDATE carrusel SET descripcion = ?, titulo = ?, texto = ?, boton_texto = ?, boton_enlace = ? WHERE id = ?");
                            $stmt->execute([$descripcion, $titulo, $texto, $botonTexto, $botonEnlace, $idImagen]);
                            
                            $successMessage = "Imagen y datos actualizados correctamente.";
                        } else {
                            $errorMessage = "Error al reemplazar la imagen. Verifica los permisos de escritura.";
                        }
                    }
                } else {
                    // Sin nueva imagen: actualizar solo textos
                    $stmt = $conn->prepare("UPDATE carrusel SET descripcion = ?, titulo = ?, texto = ?, boton_texto = ?, boton_enlace = ? WHERE id = ?");
                    $stmt->execute([$descripcion, $titulo, $texto, $botonTexto, $botonEnlace, $idImagen]);
                    
                    if ($stmt->rowCount() > 0) {
                        $successMessage = "Información actualizada correctamente.";
                    } else {
                        $errorMessage = "No se pudo actualizar la información o no se realizaron cambios.";
                    }
                }
            } else {
                $errorMessage = "No se encontró la imagen con el ID proporcionado.";
            }
        } catch (PDOException $e) {
            $errorMessage = "Error en la base de datos: " . $e->getMessage();
        }
    } else {
        // CASO DE INSERCIÓN
        if ($hasNewImage) {
            $fileTmpName = $_FILES['imagenCarrusel']['tmp_name'];
            $fileType = $_FILES['imagenCarrusel']['type'];
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!in_array($fileType, $allowedTypes)) {
                $errorMessage = "Error: solo se permiten imágenes JPEG, PNG o GIF.";
            } else {
                try {
                    // Verificamos si hay espacio para una nueva imagen
                    if ($totalImagenes < 3) {
                        // Determinamos qué nombre de archivo usar según la posición
                        $posicion = $totalImagenes; // 0, 1 o 2
                        $nombreImagen = $imagenesCarrusel[$posicion]; // Usamos el nombre predefinido correspondiente
                        $uploadFile = $uploadDir . $nombreImagen;
                        
                        if (move_uploaded_file($fileTmpName, $uploadFile)) {
                            // Guardamos el nombre predefinido en la base de datos
                            $stmt = $conn->prepare("INSERT INTO carrusel (imagen, descripcion, titulo, texto, boton_texto, boton_enlace) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$nombreImagen, $descripcion, $titulo, $texto, $botonTexto, $botonEnlace]);
                            
                            if ($stmt->rowCount() > 0) {
                                $successMessage = "Imagen subida correctamente.";
                            } else {
                                $errorMessage = "No se pudo registrar la imagen en la base de datos.";
                            }
                        } else {
                            $errorMessage = "Error al subir la imagen. Verifica los permisos de escritura.";
                        }
                    } else {
                        $errorMessage = "Ya existen 3 imágenes en el carrusel. Para agregar una nueva, primero reemplace una existente.";
                    }
                } catch (PDOException $e) {
                    $errorMessage = "Error en la base de datos: " . $e->getMessage();
                }
            }
        } else {
            $errorMessage = "No se seleccionó ninguna imagen para subir.";
        }
    }
    
    // Refrescar el conteo de imágenes después de operaciones
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM carrusel");
        $stmt->execute();
        $totalImagenes = $stmt->fetchColumn();
    } catch (PDOException $e) {
        // No es crítico, podemos omitir el manejo aquí
    }
}

// Si se selecciona una imagen para editar
if (isset($_GET['id'])) {
    $selectedImageId = $_GET['id'];
    try {
        $stmt = $conn->prepare("SELECT * FROM carrusel WHERE id = ?");
        $stmt->execute([$selectedImageId]);
        $selectedImage = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($selectedImage) {
            $selectedImageDescripcion = $selectedImage['descripcion'];
            $selectedImageTitulo = $selectedImage['titulo'];
            $selectedImageTexto = $selectedImage['texto'];
            $selectedImageBotonTexto = $selectedImage['boton_texto'];
            $selectedImageBotonEnlace = $selectedImage['boton_enlace'];
        } else {
            $errorMessage = "No se encontró la imagen seleccionada.";
        }
    } catch (PDOException $e) {
        $errorMessage = "Error al obtener datos de la imagen: " . $e->getMessage();
    }
}

// Obtener las últimas 3 imágenes del carrusel
try {
    $stmt = $conn->prepare("SELECT * FROM carrusel ORDER BY id DESC LIMIT 3");
    $stmt->execute();
    $imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Error al obtener las imágenes: " . $e->getMessage();
    $imagenes = [];
}

// Generar un código único para prevenir el cache de imágenes
$cacheBuster = time();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Banner de Carrusel</title>
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
        .selected-image {
            border: 3px solid #198754 !important;
            box-shadow: 0 0 15px rgba(25, 135, 84, 0.5) !important;
        }
        .carousel-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        .carousel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .carousel-card .card-body {
            padding: 1.5rem;
        }
        .carousel-card img {
            height: 200px;
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
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestionar Banner de Carrusel</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="mb-0 section-title">
                        <?php if ($selectedImageId): ?>
                            <i class="fas fa-edit me-2 text-warning"></i> Reemplazar Imagen del Carrusel
                        <?php else: ?>
                            <?php if ($totalImagenes >= 3): ?>
                                <i class="fas fa-exclamation-triangle me-2 text-warning"></i> Límite de imágenes alcanzado
                            <?php else: ?>
                                <i class="fas fa-image me-2 text-primary"></i> Subir Nueva Imagen del Carrusel
                            <?php endif; ?>
                        <?php endif; ?>
                    </h5>
                    <?php if ($selectedImageId): ?>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-plus"></i> Nueva Imagen
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($totalImagenes >= 3 && !$selectedImageId): ?>
                        <div class="alert alert-warning border-0 shadow-sm">
                            <i class="fas fa-exclamation-circle me-2"></i> Ya existen 3 imágenes en el carrusel. Para agregar una nueva, primero debe reemplazar una existente.
                        </div>
                    <?php else: ?>
                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                            <input type="hidden" id="imagenId" name="imagenId" value="<?php echo $selectedImageId; ?>">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <div class="avatar-container">
                                        <?php if ($selectedImageId && isset($selectedImage)): ?>
                                            <img id="avatarPreview" src="../../img/carrusel/<?php echo htmlspecialchars($selectedImage['imagen']); ?>?v=<?php echo $cacheBuster; ?>" alt="Imagen de Carrusel" onerror="this.src='https://via.placeholder.com/300x200?text=Sin+Imagen'">
                                        <?php else: ?>
                                            <img id="avatarPreview" src="../../img/carrusel/placeholder.jpg" alt="Imagen de Carrusel" onerror="this.src='https://via.placeholder.com/300x200?text=Vista+Previa'">
                                        <?php endif; ?>
                                        <div class="edit-icon">
                                            <i class="fas fa-camera"></i>
                                        </div>
                                    </div>
                                    <div class="file-input-container mt-3">
                                        <button type="button" class="btn btn-outline-primary w-100 custom-file-button" onclick="document.getElementById('imagenCarrusel').click()">
                                            <i class="fas fa-upload me-2"></i> Seleccionar imagen
                                        </button>
                                        <input type="file" id="imagenCarrusel" name="imagenCarrusel" accept="image/*" onchange="previewImage(event)" style="display: none;">
                                    </div>
                                    <div class="mt-2 text-muted small">
                                        <p>Formatos permitidos: JPG, PNG, GIF</p>
                                        <p>Dimensiones recomendadas: 1200 x 600 px</p>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="mb-3">
                                        <label for="descripcion" class="form-label">Descripción administrativa (opcional)</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2" placeholder="Esta descripción es solo para referencia y no aparecerá en la web"><?php echo htmlspecialchars($selectedImageDescripcion); ?></textarea>
                                        <small class="text-muted">Esta descripción es solo para referencia administrativa y no se muestra en la web.</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="titulo" class="form-label">Título (se muestra en el carrusel)</label>
                                        <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($selectedImageTitulo); ?>" placeholder="Ej: Atención médica de primera calidad" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="texto" class="form-label">Texto descriptivo</label>
                                        <textarea class="form-control" id="texto" name="texto" rows="2" placeholder="Breve descripción que aparecerá bajo el título" required><?php echo htmlspecialchars($selectedImageTexto); ?></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="boton_texto" class="form-label">Texto del botón</label>
                                                <input type="text" class="form-control" id="boton_texto" name="boton_texto" value="<?php echo htmlspecialchars($selectedImageBotonTexto); ?>" placeholder="Ej: Ver servicios" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="boton_enlace" class="form-label">Enlace del botón</label>
                                                <input type="text" class="form-control" id="boton_enlace" name="boton_enlace" value="<?php echo htmlspecialchars($selectedImageBotonEnlace); ?>" placeholder="Ej: #servicios" required>
                                                <small class="text-muted">Por ejemplo: #servicios, #tecnologia, #equipo</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100 submit-btn">
                                        <?php if ($selectedImageId): ?>
                                            <i class="fas fa-save me-2"></i> Guardar Cambios
                                        <?php else: ?>
                                            <i class="fas fa-save me-2"></i> Guardar Nueva Imagen
                                        <?php endif; ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 section-title"><i class="fas fa-images me-2 text-primary"></i> Imágenes del Carrusel</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if (count($imagenes) > 0): ?>
                            <?php foreach ($imagenes as $row): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="carousel-card card <?php echo ($selectedImageId == $row['id']) ? 'selected-image' : ''; ?>">
                                        <div class="position-relative">
                                            <img src="../../img/carrusel/<?php echo htmlspecialchars($row['imagen']); ?>?v=<?php echo $cacheBuster; ?>" class="card-img-top" alt="Imagen carrusel" onerror="this.src='https://via.placeholder.com/300x200?text=Sin+Imagen'">
                                            <div class="position-absolute top-0 end-0 p-2">
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($row['imagen']); ?></span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title text-primary"><?php echo htmlspecialchars($row['titulo']); ?></h5>
                                            <p class="card-text"><?php echo htmlspecialchars($row['texto']); ?></p>
                                            
                                            <table class="info-table">
                                                <tr>
                                                    <td>Botón:</td>
                                                    <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($row['boton_texto']); ?></span></td>
                                                </tr>
                                                <tr>
                                                    <td>Enlace:</td>
                                                    <td><code><?php echo htmlspecialchars($row['boton_enlace']); ?></code></td>
                                                </tr>
                                                <?php if (!empty($row['descripcion'])): ?>
                                                <tr>
                                                    <td>Descripción:</td>
                                                    <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                            </table>
                                            
                                            <a href="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $row['id']; ?>" class="btn btn-edit w-100 mt-3">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> No hay imágenes en el carrusel. Utilice el formulario superior para agregar una nueva imagen.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
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

    // Mostrar alertas usando SweetAlert si hay mensajes
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($errorMessage): ?>
            AlertaSweetAlert('error', 'Error', '<?php echo addslashes($errorMessage); ?>', '');
        <?php endif; ?>
        
        <?php if ($successMessage): ?>
            AlertaSweetAlert('success', 'Correcto', '<?php echo addslashes($successMessage); ?>', '');
        <?php endif; ?>
    });
</script>

</body>
</html>

<?php require_once '../include/footer.php'; ?>