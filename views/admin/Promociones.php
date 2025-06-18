<?php 
require_once '../include/header.administrador.php';
require_once '../../models/Conexion.php';

// Creación de conexión
$conexion = new Conexion();
$conn = $conexion->getConexion();

// Variable para almacenar el ID de la promoción seleccionada
$selectedPromoId = null;
$selectedPromoTitulo = '';
$selectedPromoDescripcion = '';
$selectedPromoImagen = '';
$selectedPrecioRegular = '';
$selectedPrecioOferta = '';
$selectedPorcentajeDescuento = '';
$selectedTextoBoton = 'Reservar Cita';
$selectedEnlaceBoton = 'reservar.php';
$selectedEstado = 1;
$errorMessage = null;
$successMessage = null;

// Verificar si existe el directorio para cargas
$uploadDir = '../../img/promociones/';
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

// Eliminar promoción si se solicita
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $idEliminar = $_GET['delete'];
    
    try {
        // Primero obtener la imagen
        $stmt = $conn->prepare("SELECT imagen FROM promociones WHERE id = ?");
        $stmt->execute([$idEliminar]);
        $imagenEliminar = $stmt->fetchColumn();
        
        // Eliminar de la base de datos
        $stmt = $conn->prepare("DELETE FROM promociones WHERE id = ?");
        $stmt->execute([$idEliminar]);
        
        if ($stmt->rowCount() > 0) {
            // Intentar eliminar el archivo físico si existe
            $rutaImagen = $uploadDir . $imagenEliminar;
            if (file_exists($rutaImagen)) {
                unlink($rutaImagen);
            }
            
            $successMessage = "Promoción eliminada correctamente.";
        } else {
            $errorMessage = "No se pudo eliminar la promoción.";
        }
    } catch (PDOException $e) {
        $errorMessage = "Error al eliminar: " . $e->getMessage();
    }
}

// Manejo de promoción enviada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si es una actualización o inserción
    $isUpdate = isset($_POST['promocionId']) && !empty($_POST['promocionId']);
    $titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
    $precioRegular = isset($_POST['precio_regular']) ? str_replace(',', '.', $_POST['precio_regular']) : 0;
    $precioOferta = isset($_POST['precio_oferta']) ? str_replace(',', '.', $_POST['precio_oferta']) : 0;
    $porcentajeDescuento = isset($_POST['porcentaje_descuento']) ? $_POST['porcentaje_descuento'] : 0;
    $textoBoton = isset($_POST['texto_boton']) ? $_POST['texto_boton'] : 'Reservar Cita';
    $enlaceBoton = isset($_POST['enlace_boton']) ? $_POST['enlace_boton'] : 'reservar.php';
    $estado = isset($_POST['estado']) ? 1 : 0;
    
    // Procesar la imagen si se ha seleccionado
    $hasNewImage = isset($_FILES['imagenPromocion']) && $_FILES['imagenPromocion']['error'] == 0;
    
    if ($isUpdate) {
        // CASO DE ACTUALIZACIÓN
        $idPromocion = $_POST['promocionId'];
        
        try {
            // Obtenemos el nombre de archivo actual desde la base de datos
            $stmt = $conn->prepare("SELECT imagen FROM promociones WHERE id = ?");
            $stmt->execute([$idPromocion]);
            $imagenActual = $stmt->fetchColumn();
            
            if ($hasNewImage) {
                // Con nueva imagen
                $fileTmpName = $_FILES['imagenPromocion']['tmp_name'];
                $fileType = $_FILES['imagenPromocion']['type'];
                $fileExt = pathinfo($_FILES['imagenPromocion']['name'], PATHINFO_EXTENSION);
                
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                
                if (!in_array($fileType, $allowedTypes)) {
                    $errorMessage = "Error: solo se permiten imágenes JPEG, PNG o GIF.";
                } else {
                    // Generar un nombre único para la imagen
                    $nombreImagen = 'promocion-' . time() . '.' . $fileExt;
                    $uploadFile = $uploadDir . $nombreImagen;
                    
                    // Subir la nueva imagen
                    if (move_uploaded_file($fileTmpName, $uploadFile)) {
                        // Eliminar la imagen anterior si existe
                        if ($imagenActual && file_exists($uploadDir . $imagenActual)) {
                            unlink($uploadDir . $imagenActual);
                        }
                        
                        // Actualizamos los datos en la base de datos con la nueva imagen
                        $stmt = $conn->prepare("UPDATE promociones SET titulo = ?, descripcion = ?, imagen = ?, precio_regular = ?, precio_oferta = ?, porcentaje_descuento = ?, texto_boton = ?, enlace_boton = ?, estado = ? WHERE id = ?");
                        $stmt->execute([$titulo, $descripcion, $nombreImagen, $precioRegular, $precioOferta, $porcentajeDescuento, $textoBoton, $enlaceBoton, $estado, $idPromocion]);
                        
                        $successMessage = "Promoción actualizada correctamente con nueva imagen.";
                    } else {
                        $errorMessage = "Error al subir la imagen. " . getFileUploadErrorMessage($_FILES['imagenPromocion']['error']);
                    }
                }
            } else {
                // Sin nueva imagen: actualizar solo datos
                $stmt = $conn->prepare("UPDATE promociones SET titulo = ?, descripcion = ?, precio_regular = ?, precio_oferta = ?, porcentaje_descuento = ?, texto_boton = ?, enlace_boton = ?, estado = ? WHERE id = ?");
                $stmt->execute([$titulo, $descripcion, $precioRegular, $precioOferta, $porcentajeDescuento, $textoBoton, $enlaceBoton, $estado, $idPromocion]);
                
                if ($stmt->rowCount() > 0) {
                    $successMessage = "Promoción actualizada correctamente.";
                } else {
                    $errorMessage = "No se pudo actualizar la promoción o no se realizaron cambios.";
                }
            }
        } catch (PDOException $e) {
            $errorMessage = "Error en la base de datos: " . $e->getMessage();
        }
    } else {
        // CASO DE INSERCIÓN
        if ($hasNewImage) {
            $fileTmpName = $_FILES['imagenPromocion']['tmp_name'];
            $fileType = $_FILES['imagenPromocion']['type'];
            $fileExt = pathinfo($_FILES['imagenPromocion']['name'], PATHINFO_EXTENSION);
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!in_array($fileType, $allowedTypes)) {
                $errorMessage = "Error: solo se permiten imágenes JPEG, PNG o GIF.";
            } else {
                try {
                    // Generar un nombre único para la imagen
                    $nombreImagen = 'promocion-' . time() . '.' . $fileExt;
                    $uploadFile = $uploadDir . $nombreImagen;
                    
                    if (move_uploaded_file($fileTmpName, $uploadFile)) {
                        // Guardamos en la base de datos
                        $stmt = $conn->prepare("INSERT INTO promociones (titulo, descripcion, imagen, precio_regular, precio_oferta, porcentaje_descuento, texto_boton, enlace_boton, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$titulo, $descripcion, $nombreImagen, $precioRegular, $precioOferta, $porcentajeDescuento, $textoBoton, $enlaceBoton, $estado]);
                        
                        if ($stmt->rowCount() > 0) {
                            $successMessage = "Promoción agregada correctamente.";
                        } else {
                            $errorMessage = "No se pudo registrar la promoción en la base de datos.";
                        }
                    } else {
                        $errorMessage = "Error al subir la imagen. " . getFileUploadErrorMessage($_FILES['imagenPromocion']['error']);
                    }
                } catch (PDOException $e) {
                    $errorMessage = "Error en la base de datos: " . $e->getMessage();
                }
            }
        } else {
            $errorMessage = "No se seleccionó ninguna imagen para la promoción.";
        }
    }
}

// Si se selecciona una promoción para editar
if (isset($_GET['id'])) {
    $selectedPromoId = $_GET['id'];
    try {
        $stmt = $conn->prepare("SELECT * FROM promociones WHERE id = ?");
        $stmt->execute([$selectedPromoId]);
        $selectedPromo = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($selectedPromo) {
            $selectedPromoTitulo = $selectedPromo['titulo'];
            $selectedPromoDescripcion = $selectedPromo['descripcion'];
            $selectedPromoImagen = $selectedPromo['imagen'];
            $selectedPrecioRegular = $selectedPromo['precio_regular'];
            $selectedPrecioOferta = $selectedPromo['precio_oferta'];
            $selectedPorcentajeDescuento = $selectedPromo['porcentaje_descuento'];
            $selectedTextoBoton = $selectedPromo['texto_boton'];
            $selectedEnlaceBoton = $selectedPromo['enlace_boton'];
            $selectedEstado = $selectedPromo['estado'];
        } else {
            $errorMessage = "No se encontró la promoción seleccionada.";
        }
    } catch (PDOException $e) {
        $errorMessage = "Error al obtener datos de la promoción: " . $e->getMessage();
    }
}

// Obtener todas las promociones
try {
    $stmt = $conn->prepare("SELECT * FROM promociones ORDER BY id DESC");
    $stmt->execute();
    $promociones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Error al obtener promociones: " . $e->getMessage();
    $promociones = [];
}

// Generar un código único para prevenir el cache de imágenes
$cacheBuster = time();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Promociones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .avatar-container {
            position: relative;
            margin-bottom: 15px;
            display: inline-block;
        }
        .avatar-container img {
            width: 100%;
            max-width: 300px;
            height: auto;
            border-radius: 8px;
            border: 2px solid #ddd;
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
        }
        .selected-promo {
            border: 3px solid #198754 !important;
            box-shadow: 0 0 10px rgba(25, 135, 84, 0.5);
        }
        .promo-card {
            transition: all 0.3s ease;
        }
        .promo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .badge-discount {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
            padding: 6px 12px;
            font-size: 14px;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1;
        }
        .action-buttons {
            position: absolute;
            bottom: 10px;
            right: 10px;
            z-index: 2;
        }
        .price-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .regular-price {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 0.9rem;
        }
        .offer-price {
            font-weight: bold;
            color: #0d6efd;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestionar Promociones</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <?php if ($selectedPromoId): ?>
                            <i class="fas fa-edit me-2"></i> Editar Promoción
                        <?php else: ?>
                            <i class="fas fa-plus-circle me-2"></i> Nueva Promoción
                        <?php endif; ?>
                    </h5>
                    <?php if ($selectedPromoId): ?>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus"></i> Nueva Promoción
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                        <input type="hidden" id="promocionId" name="promocionId" value="<?php echo $selectedPromoId; ?>">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="avatar-container">
                                    <?php if ($selectedPromoId && !empty($selectedPromoImagen)): ?>
                                        <img id="avatarPreview" src="../../img/promociones/<?php echo htmlspecialchars($selectedPromoImagen); ?>?v=<?php echo $cacheBuster; ?>" alt="Imagen de Promoción" onerror="this.src='https://via.placeholder.com/300x200?text=No+disponible'">
                                    <?php else: ?>
                                        <img id="avatarPreview" src="https://via.placeholder.com/300x200?text=Seleccionar+imagen" alt="Imagen de Promoción">
                                    <?php endif; ?>
                                    <div class="edit-icon">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                </div>
                                <div class="file-input-container">
                                    <button type="button" class="btn btn-outline-primary w-100" onclick="document.getElementById('imagenPromocion').click()">
                                        <i class="fas fa-upload me-2"></i> Seleccionar imagen
                                    </button>
                                    <input type="file" id="imagenPromocion" name="imagenPromocion" accept="image/*" onchange="previewImage(event)" style="display: none;">
                                </div>
                                <small class="text-muted mt-2 d-block">Tamaño recomendado: 800x600px</small>
                            </div>
                            <div class="col-md-9">
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título de la promoción</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($selectedPromoTitulo); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?php echo htmlspecialchars($selectedPromoDescripcion); ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="precio_regular" class="form-label">Precio Regular (S/)</label>
                                            <input type="number" step="0.01" class="form-control" id="precio_regular" name="precio_regular" value="<?php echo htmlspecialchars($selectedPrecioRegular); ?>" required oninput="calcularDescuento()">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="precio_oferta" class="form-label">Precio Oferta (S/)</label>
                                            <input type="number" step="0.01" class="form-control" id="precio_oferta" name="precio_oferta" value="<?php echo htmlspecialchars($selectedPrecioOferta); ?>" required oninput="calcularDescuento()">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="porcentaje_descuento" class="form-label">Descuento (%)</label>
                                            <input type="number" class="form-control" id="porcentaje_descuento" name="porcentaje_descuento" value="<?php echo htmlspecialchars($selectedPorcentajeDescuento); ?>" oninput="calcularPrecioOferta()">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="texto_boton" class="form-label">Texto del botón</label>
                                            <input type="text" class="form-control" id="texto_boton" name="texto_boton" value="<?php echo htmlspecialchars($selectedTextoBoton); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="enlace_boton" class="form-label">Enlace del botón</label>
                                            <input type="text" class="form-control" id="enlace_boton" name="enlace_boton" value="<?php echo htmlspecialchars($selectedEnlaceBoton); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="estado" name="estado" <?php echo $selectedEstado ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="estado">
                                        Promoción activa
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <?php if ($selectedPromoId): ?>
                                        <i class="fas fa-save me-2"></i> Actualizar Promoción
                                    <?php else: ?>
                                        <i class="fas fa-save me-2"></i> Guardar Promoción
                                    <?php endif; ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tag me-2"></i> Promociones Disponibles</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if (count($promociones) > 0): ?>
                            <?php foreach ($promociones as $promo): ?>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card promo-card h-100 <?php echo ($selectedPromoId == $promo['id']) ? 'selected-promo' : ''; ?>">
                                        <div class="position-relative">
                                            <span class="badge badge-discount bg-primary">
                                                <?php echo htmlspecialchars($promo['porcentaje_descuento']); ?>% DESCUENTO
                                            </span>
                                            <span class="badge status-badge <?php echo $promo['estado'] ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $promo['estado'] ? 'Activa' : 'Inactiva'; ?>
                                            </span>
                                            <img src="../../img/promociones/<?php echo htmlspecialchars($promo['imagen']); ?>?v=<?php echo $cacheBuster; ?>" class="card-img-top" alt="Imagen promoción" onerror="this.src='https://via.placeholder.com/300x200?text=No+disponible'" style="height: 200px; object-fit: cover;">
                                            <div class="action-buttons">
                                                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $promo['id']; ?>" class="btn btn-sm btn-warning me-1">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="#" class="btn btn-sm btn-danger" onclick="eliminarPromocion(<?php echo $promo['id']; ?>, '<?php echo addslashes($promo['titulo']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($promo['titulo']); ?></h5>
                                            <p class="card-text small"><?php echo htmlspecialchars(mb_substr($promo['descripcion'], 0, 120)) . (mb_strlen($promo['descripcion']) > 120 ? '...' : ''); ?></p>
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <div class="price-container">
                                                    <span class="regular-price">S/<?php echo number_format($promo['precio_regular'], 2); ?></span>
                                                    <span class="offer-price">S/<?php echo number_format($promo['precio_oferta'], 2); ?></span>
                                                </div>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($promo['texto_boton']); ?></span>
                                            </div>
                                        </div>
                                        <div class="card-footer text-muted small">
                                            Enlace: <?php echo htmlspecialchars($promo['enlace_boton']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5">
                                <i class="fas fa-tag fa-3x text-muted mb-3"></i>
                                <p>No hay promociones disponibles. ¡Agrega una nueva promoción!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Inclusión del script de alertas -->
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
    
    function calcularDescuento() {
        const precioRegular = parseFloat(document.getElementById('precio_regular').value) || 0;
        const precioOferta = parseFloat(document.getElementById('precio_oferta').value) || 0;
        
        if (precioRegular > 0 && precioOferta > 0 && precioOferta < precioRegular) {
            const descuento = Math.round(((precioRegular - precioOferta) / precioRegular) * 100);
            document.getElementById('porcentaje_descuento').value = descuento;
        }
    }
    
    function calcularPrecioOferta() {
        const precioRegular = parseFloat(document.getElementById('precio_regular').value) || 0;
        const descuento = parseInt(document.getElementById('porcentaje_descuento').value) || 0;
        
        if (precioRegular > 0 && descuento > 0 && descuento <= 100) {
            const precioOferta = precioRegular * (1 - (descuento / 100));
            document.getElementById('precio_oferta').value = precioOferta.toFixed(2);
        }
    }
    
    function eliminarPromocion(id, titulo) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Deseas eliminar la promoción "' + titulo + '"?',
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
    
    // Mostrar alertas con SweetAlert2 si hay mensajes
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