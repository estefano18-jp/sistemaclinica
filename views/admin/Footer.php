<?php 
require_once '../include/header.administrador.php';
require_once '../../models/Conexion.php';

// Creación de conexión
$conexion = new Conexion();
$conn = $conexion->getConexion();

$errorMessage = null;
$successMessage = null;

// Verificar si existe el directorio para cargas
$uploadDir = '../../img/sitio/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
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

// Crear tabla si no existe
try {
    $sql = "CREATE TABLE IF NOT EXISTS configuracion_sitio (
        id INT AUTO_INCREMENT PRIMARY KEY,
        clave VARCHAR(100) NOT NULL UNIQUE,
        valor TEXT,
        tipo ENUM('texto', 'imagen', 'url', 'email', 'telefono') DEFAULT 'texto',
        descripcion VARCHAR(255),
        actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    
    // Insertar valores por defecto si no existen
    $defaultConfigs = [
        ['logo_principal', 'iconoClinica.jpg', 'imagen', 'Logo principal del sitio'],
        ['nombre_empresa', 'Clínica Médica', 'texto', 'Nombre de la empresa'],
        ['direccion', 'Av. Principal 123, Ciudad', 'texto', 'Dirección de la clínica'],
        ['telefono', '+12 345 6789', 'telefono', 'Teléfono principal'],
        ['email', 'contacto@clinica.com', 'email', 'Email de contacto'],
        ['horario_semana', 'Lunes - Viernes: 08:00 - 18:00', 'texto', 'Horario de lunes a viernes'],
        ['horario_sabado', 'Sábado: 09:00 - 14:00', 'texto', 'Horario de sábado'],
        ['horario_domingo', 'Domingo y feriados: Cerrado', 'texto', 'Horario de domingo'],
        ['facebook_url', 'https://facebook.com/clinica', 'url', 'URL de Facebook'],
        ['twitter_url', 'https://twitter.com/clinica', 'url', 'URL de Twitter'],
        ['instagram_url', 'https://instagram.com/clinica', 'url', 'URL de Instagram'],
        ['youtube_url', 'https://youtube.com/clinica', 'url', 'URL de YouTube'],
        ['whatsapp_numero', '123456789', 'telefono', 'Número de WhatsApp'],
        ['descripcion_empresa', 'Somos una clínica comprometida con su salud y bienestar. Contamos con los mejores especialistas médicos y la tecnología más avanzada para brindarle una atención personalizada y de calidad.', 'texto', 'Descripción de la empresa']
    ];
    
    foreach ($defaultConfigs as $config) {
        $stmt = $conn->prepare("INSERT IGNORE INTO configuracion_sitio (clave, valor, tipo, descripcion) VALUES (?, ?, ?, ?)");
        $stmt->execute($config);
    }
    
} catch (PDOException $e) {
    $errorMessage = "Error al crear la tabla: " . $e->getMessage();
}

// Manejo de formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        foreach ($_POST as $clave => $valor) {
            if ($clave !== 'submit') {
                // Verificar si es un campo de imagen
                $stmt = $conn->prepare("SELECT tipo FROM configuracion_sitio WHERE clave = ?");
                $stmt->execute([$clave]);
                $tipo = $stmt->fetchColumn();
                
                if ($tipo === 'imagen' && isset($_FILES[$clave]) && $_FILES[$clave]['error'] == 0) {
                    // Procesar imagen
                    $fileTmpName = $_FILES[$clave]['tmp_name'];
                    $fileType = $_FILES[$clave]['type'];
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    
                    if (in_array($fileType, $allowedTypes)) {
                        $extension = pathinfo($_FILES[$clave]['name'], PATHINFO_EXTENSION);
                        $nombreArchivo = $clave . '.' . $extension;
                        $uploadFile = $uploadDir . $nombreArchivo;
                        
                        if (move_uploaded_file($fileTmpName, $uploadFile)) {
                            $stmt = $conn->prepare("UPDATE configuracion_sitio SET valor = ? WHERE clave = ?");
                            $stmt->execute([$nombreArchivo, $clave]);
                        }
                    }
                } else {
                    // Actualizar campo de texto
                    $stmt = $conn->prepare("UPDATE configuracion_sitio SET valor = ? WHERE clave = ?");
                    $stmt->execute([$valor, $clave]);
                }
            }
        }
        
        $conn->commit();
        $successMessage = "Configuración actualizada correctamente.";
        
    } catch (PDOException $e) {
        $conn->rollBack();
        $errorMessage = "Error al actualizar la configuración: " . $e->getMessage();
    }
}

// Obtener configuración actual
try {
    $stmt = $conn->prepare("SELECT * FROM configuracion_sitio ORDER BY clave");
    $stmt->execute();
    $configuraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convertir a array asociativo para fácil acceso
    $config = [];
    foreach ($configuraciones as $item) {
        $config[$item['clave']] = $item;
    }
    
} catch (PDOException $e) {
    $errorMessage = "Error al obtener la configuración: " . $e->getMessage();
    $config = [];
}

$cacheBuster = time();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración del Sitio Web</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .config-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }
        .config-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 20px;
            border-bottom: none;
        }
        .config-body {
            padding: 30px;
        }
        .logo-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid #0d6efd;
            object-fit: cover;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .logo-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .btn-upload {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            transition: all 0.3s ease;
        }
        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.2);
        }
        .social-input {
            position: relative;
        }
        .social-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            text-align: center;
        }
        .social-input input {
            padding-left: 40px;
        }
        .preview-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #0d6efd;
        }
        .config-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
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
                    <li class="breadcrumb-item active" aria-current="page">Configuración del Sitio</li>
                </ol>
            </nav>

            <form method="POST" enctype="multipart/form-data">
                <!-- Sección Logo y Información Básica -->
                <div class="config-section">
                    <div class="config-header">
                        <h4 class="mb-0"><i class="fas fa-building me-2"></i> Información de la Empresa</h4>
                    </div>
                    <div class="config-body">
                        <div class="row">
                            <div class="col-md-3 text-center mb-4">
                                <label class="form-label">Logo Principal</label>
                                <div class="mb-3">
                                    <?php if (isset($config['logo_principal'])): ?>
                                        <img id="logoPreview" 
                                             src="../../img/sitio/<?php echo htmlspecialchars($config['logo_principal']['valor']); ?>?v=<?php echo $cacheBuster; ?>" 
                                             class="logo-preview" 
                                             alt="Logo" 
                                             onerror="this.src='https://via.placeholder.com/120x120?text=Logo'">
                                    <?php else: ?>
                                        <img id="logoPreview" 
                                             src="https://via.placeholder.com/120x120?text=Logo" 
                                             class="logo-preview" 
                                             alt="Logo">
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn btn-upload text-white w-100" onclick="document.getElementById('logo_principal').click()">
                                    <i class="fas fa-camera me-2"></i> Cambiar Logo
                                </button>
                                <input type="file" id="logo_principal" name="logo_principal" accept="image/*" onchange="previewLogo(event)" style="display: none;">
                                <small class="text-muted d-block mt-2">JPG, PNG, GIF (máx. 2MB)<br>Recomendado: 200x200px</small>
                            </div>
                            <div class="col-md-9">
                                <div class="config-grid">
                                    <div>
                                        <label for="nombre_empresa" class="form-label">
                                            <i class="fas fa-hospital text-primary me-1"></i> Nombre de la Empresa
                                        </label>
                                        <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa" 
                                               value="<?php echo htmlspecialchars($config['nombre_empresa']['valor'] ?? ''); ?>" required>
                                    </div>
                                    <div>
                                        <label for="descripcion_empresa" class="form-label">
                                            <i class="fas fa-info-circle text-primary me-1"></i> Descripción
                                        </label>
                                        <textarea class="form-control" id="descripcion_empresa" name="descripcion_empresa" rows="3"><?php echo htmlspecialchars($config['descripcion_empresa']['valor'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección Información de Contacto -->
                <div class="config-section">
                    <div class="config-header">
                        <h4 class="mb-0"><i class="fas fa-address-book me-2"></i> Información de Contacto</h4>
                    </div>
                    <div class="config-body">
                        <div class="config-grid">
                            <div>
                                <label for="direccion" class="form-label">
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i> Dirección
                                </label>
                                <input type="text" class="form-control" id="direccion" name="direccion" 
                                       value="<?php echo htmlspecialchars($config['direccion']['valor'] ?? ''); ?>">
                            </div>
                            <div>
                                <label for="telefono" class="form-label">
                                    <i class="fas fa-phone text-success me-1"></i> Teléfono
                                </label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       value="<?php echo htmlspecialchars($config['telefono']['valor'] ?? ''); ?>">
                            </div>
                            <div>
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope text-info me-1"></i> Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($config['email']['valor'] ?? ''); ?>">
                            </div>
                            <div>
                                <label for="whatsapp_numero" class="form-label">
                                    <i class="fab fa-whatsapp text-success me-1"></i> WhatsApp
                                </label>
                                <input type="tel" class="form-control" id="whatsapp_numero" name="whatsapp_numero" 
                                       value="<?php echo htmlspecialchars($config['whatsapp_numero']['valor'] ?? ''); ?>"
                                       placeholder="123456789">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección Horarios -->
                <div class="config-section">
                    <div class="config-header">
                        <h4 class="mb-0"><i class="fas fa-clock me-2"></i> Horarios de Atención</h4>
                    </div>
                    <div class="config-body">
                        <div class="config-grid">
                            <div>
                                <label for="horario_semana" class="form-label">
                                    <i class="fas fa-calendar-week text-primary me-1"></i> Lunes - Viernes
                                </label>
                                <input type="text" class="form-control" id="horario_semana" name="horario_semana" 
                                       value="<?php echo htmlspecialchars($config['horario_semana']['valor'] ?? ''); ?>">
                            </div>
                            <div>
                                <label for="horario_sabado" class="form-label">
                                    <i class="fas fa-calendar-day text-warning me-1"></i> Sábado
                                </label>
                                <input type="text" class="form-control" id="horario_sabado" name="horario_sabado" 
                                       value="<?php echo htmlspecialchars($config['horario_sabado']['valor'] ?? ''); ?>">
                            </div>
                            <div>
                                <label for="horario_domingo" class="form-label">
                                    <i class="fas fa-calendar-times text-danger me-1"></i> Domingo y Feriados
                                </label>
                                <input type="text" class="form-control" id="horario_domingo" name="horario_domingo" 
                                       value="<?php echo htmlspecialchars($config['horario_domingo']['valor'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección Redes Sociales -->
                <div class="config-section">
                    <div class="config-header">
                        <h4 class="mb-0"><i class="fas fa-share-alt me-2"></i> Redes Sociales</h4>
                    </div>
                    <div class="config-body">
                        <div class="config-grid">
                            <div class="social-input">
                                <label for="facebook_url" class="form-label">Facebook</label>
                                <i class="fab fa-facebook-f social-icon text-primary"></i>
                                <input type="url" class="form-control" id="facebook_url" name="facebook_url" 
                                       value="<?php echo htmlspecialchars($config['facebook_url']['valor'] ?? ''); ?>">
                            </div>
                            <div class="social-input">
                                <label for="twitter_url" class="form-label">Twitter</label>
                                <i class="fab fa-twitter social-icon text-info"></i>
                                <input type="url" class="form-control" id="twitter_url" name="twitter_url" 
                                       value="<?php echo htmlspecialchars($config['twitter_url']['valor'] ?? ''); ?>">
                            </div>
                            <div class="social-input">
                                <label for="instagram_url" class="form-label">Instagram</label>
                                <i class="fab fa-instagram social-icon text-danger"></i>
                                <input type="url" class="form-control" id="instagram_url" name="instagram_url" 
                                       value="<?php echo htmlspecialchars($config['instagram_url']['valor'] ?? ''); ?>">
                            </div>
                            <div class="social-input">
                                <label for="youtube_url" class="form-label">YouTube</label>
                                <i class="fab fa-youtube social-icon text-danger"></i>
                                <input type="url" class="form-control" id="youtube_url" name="youtube_url" 
                                       value="<?php echo htmlspecialchars($config['youtube_url']['valor'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vista Previa -->
                <div class="config-section">
                    <div class="config-header">
                        <h4 class="mb-0"><i class="fas fa-eye me-2"></i> Vista Previa del Footer</h4>
                    </div>
                    <div class="config-body">
                        <div class="preview-card">
                            <div class="row">
                                <div class="col-md-4">
                                    <h5>Sobre Nosotros</h5>
                                    <img id="logoPreviewFooter" 
                                         src="../../img/sitio/<?php echo htmlspecialchars($config['logo_principal']['valor'] ?? 'placeholder.jpg'); ?>?v=<?php echo $cacheBuster; ?>" 
                                         alt="Logo" 
                                         style="max-height: 80px; border-radius: 5px; margin-bottom: 15px;"
                                         onerror="this.src='https://via.placeholder.com/80x80?text=Logo'">
                                    <p id="descripcionPreview"><?php echo htmlspecialchars($config['descripcion_empresa']['valor'] ?? ''); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <h5>Contáctenos</h5>
                                    <p><i class="fas fa-map-marker-alt me-2"></i> <span id="direccionPreview"><?php echo htmlspecialchars($config['direccion']['valor'] ?? ''); ?></span></p>
                                    <p><i class="fas fa-phone-alt me-2"></i> <span id="telefonoPreview"><?php echo htmlspecialchars($config['telefono']['valor'] ?? ''); ?></span></p>
                                    <p><i class="fas fa-envelope me-2"></i> <span id="emailPreview"><?php echo htmlspecialchars($config['email']['valor'] ?? ''); ?></span></p>
                                    <p><i class="fas fa-clock me-2"></i> <span id="horarioPreview"><?php echo htmlspecialchars($config['horario_semana']['valor'] ?? ''); ?></span></p>
                                </div>
                                <div class="col-md-4">
                                    <h5>Síguenos</h5>
                                    <div class="d-flex gap-2">
                                        <a href="<?php echo htmlspecialchars($config['facebook_url']['valor'] ?? '#'); ?>" class="btn btn-outline-primary btn-sm"><i class="fab fa-facebook-f"></i></a>
                                        <a href="<?php echo htmlspecialchars($config['twitter_url']['valor'] ?? '#'); ?>" class="btn btn-outline-info btn-sm"><i class="fab fa-twitter"></i></a>
                                        <a href="<?php echo htmlspecialchars($config['instagram_url']['valor'] ?? '#'); ?>" class="btn btn-outline-danger btn-sm"><i class="fab fa-instagram"></i></a>
                                        <a href="<?php echo htmlspecialchars($config['youtube_url']['valor'] ?? '#'); ?>" class="btn btn-outline-danger btn-sm"><i class="fab fa-youtube"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón Guardar -->
                <div class="text-center">
                    <button type="submit" name="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-save me-2"></i> Guardar Configuración
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../js/alertas.js"></script>
<script>
    function previewLogo(event) {
        if (event.target.files && event.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('logoPreview').src = e.target.result;
                document.getElementById('logoPreviewFooter').src = e.target.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    }

    // Actualizar vista previa en tiempo real
    document.addEventListener('DOMContentLoaded', function() {
        // Actualizar descripción
        document.getElementById('descripcion_empresa').addEventListener('input', function() {
            document.getElementById('descripcionPreview').textContent = this.value;
        });

        // Actualizar dirección
        document.getElementById('direccion').addEventListener('input', function() {
            document.getElementById('direccionPreview').textContent = this.value;
        });

        // Actualizar teléfono
        document.getElementById('telefono').addEventListener('input', function() {
            document.getElementById('telefonoPreview').textContent = this.value;
        });

        // Actualizar email
        document.getElementById('email').addEventListener('input', function() {
            document.getElementById('emailPreview').textContent = this.value;
        });

        // Actualizar horario
        document.getElementById('horario_semana').addEventListener('input', function() {
            document.getElementById('horarioPreview').textContent = this.value;
        });

        // Mostrar alertas
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