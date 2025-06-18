<?php
// Incluir archivo de conexión
require_once 'models/Conexion.php';

// Creación de conexión
$conexion = new Conexion();
$conn = $conexion->getConexion();

// Obtener todas las especialidades activas
try {
    $stmt = $conn->prepare("
        SELECT we.*, 
               e.estado AS sistema_estado,
               (SELECT COUNT(*) FROM colaboradores c WHERE c.idespecialidad = we.idespecialidad AND c.estado = 'ACTIVO') AS total_doctores
        FROM web_especialidades we
        LEFT JOIN especialidades e ON we.idespecialidad = e.idespecialidad
        WHERE we.estado = 1
        ORDER BY we.orden ASC, we.id DESC
    ");
    $stmt->execute();
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al obtener especialidades: " . $e->getMessage());
    $especialidades = [];
}

// Obtener servicios para cada especialidad
if (count($especialidades) > 0) {
    foreach ($especialidades as $key => $especialidad) {
        try {
            $stmt = $conn->prepare("SELECT servicio FROM web_especialidades_servicios WHERE web_especialidad_id = ? ORDER BY id ASC");
            $stmt->execute([$especialidad['id']]);
            $servicios = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $especialidades[$key]['servicios'] = $servicios;
        } catch (PDOException $e) {
            error_log("Error al obtener servicios para la especialidad ID " . $especialidad['id'] . ": " . $e->getMessage());
            $especialidades[$key]['servicios'] = [];
        }
    }
}

// Generar un código único para prevenir el cache de imágenes
$cacheBuster = time();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
  <title>Especialidades - Clínica Médica</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Animate.css para animaciones -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

  <!-- CSS personalizado -->
  <link rel="stylesheet" href="css/web.css">
  
  <!-- FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  
  <style>
    /* Estilos específicos para la página de especialidades */
    .page-banner {
      background: linear-gradient(rgba(0, 123, 255, 0.7), rgba(0, 123, 255, 0.9)), url('img/banner-especialidades.jpg');
      background-size: cover;
      background-position: center;
      padding: 80px 0;
      color: white;
      position: relative;
    }
    
    .specialty-detail-card {
      border-radius: 10px;
      overflow: hidden;
      transition: all 0.3s ease;
      margin-bottom: 30px;
      border: none;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .specialty-detail-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }
    
    .specialty-detail-card .card-img-top {
      height: 200px;
      object-fit: cover;
    }
    
    .specialty-detail-card .card-header {
      background-color: #f8f9fa;
      border-bottom: 2px solid #e9ecef;
      padding: 15px 20px;
    }
    
    .specialty-detail-card .card-title {
      display: flex;
      align-items: center;
      margin-bottom: 0;
    }
    
    .specialty-detail-card .specialty-icon {
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #0d6efd;
      color: white;
      border-radius: 50%;
      margin-right: 15px;
      font-size: 20px;
    }
    
    .specialty-detail-card .card-body {
      padding: 20px;
    }
    
    .specialty-services {
      list-style: none;
      padding-left: 0;
    }
    
    .specialty-services li {
      padding: 8px 0;
      border-bottom: 1px solid #f0f0f0;
      display: flex;
      align-items: center;
    }
    
    .specialty-services li:last-child {
      border-bottom: none;
    }
    
    .specialty-services li i {
      color: #0d6efd;
      margin-right: 10px;
    }
    
    /* Animación para los cards al cargar */
    .animate-card {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.5s ease, transform 0.5s ease;
    }
    
    .animate-card.show {
      opacity: 1;
      transform: translateY(0);
    }
    
    /* Estilos para el botón de volver */
    .btn-back {
      margin-bottom: 20px;
      padding: 10px 20px;
      font-weight: 500;
    }
    
    /* Breadcrumb personalizado */
    .custom-breadcrumb {
      background-color: transparent;
      padding: 10px 0;
    }
    
    .breadcrumb-item a {
      color: #6c757d;
      text-decoration: none;
    }
    
    .breadcrumb-item a:hover {
      color: #0d6efd;
    }
    
    .breadcrumb-item.active {
      color: #0d6efd;
    }
    
    /* Nuevos estilos para indicadores de disponibilidad */
    .availability-indicator {
      margin-top: 15px;
      padding: 8px 10px;
      border-radius: 5px;
      font-size: 0.85rem;
    }
    
    .coming-soon {
      background-color: #fff3cd;
      color: #856404;
      border-left: 4px solid #ffc107;
    }
    
    .doctors-available {
      background-color: #d4edda;
      color: #155724;
      border-left: 4px solid #28a745;
    }
  </style>
</head>

<body>
  <!-- Barra de navegación -->
  <nav class="navbar navbar-expand-md navbar-light bg-light sticky-top">
    <div class="container">
      <!-- Logo y nombre a la izquierda con mejoras de visibilidad -->
      <a class="navbar-brand d-flex align-items-center" href="index.php#inicio">
        <div class="logo-container"
          style="width: 50px; height: 50px; overflow: hidden; border-radius: 50%; border: 2px solid #0d6efd; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
          <img src="img/iconoClinica.jpg" alt="Logo Clínica" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <span class="ms-3 fw-bold text-primary">Clínica Médica</span>
      </a>

      <!-- Botón de hamburguesa para móviles -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Los elementos de navegación -->
      <div class="collapse navbar-collapse" id="navbarNav">
        <!-- Elementos de navegación en el centro -->
        <ul class="navbar-nav me-auto ms-auto">
          <li class="nav-item">
            <a class="nav-link nav-item-custom" href="index.php#inicio">
              <i class="fas fa-home nav-icon"></i> INICIO
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-item-custom active" href="especialidades.php">
              <i class="fas fa-stethoscope nav-icon"></i> ESPECIALIDADES
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-item-custom" href="index.php#promociones">
              <i class="fas fa-tag nav-icon"></i> PROMOCIONES
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-item-custom" href="index.php#testimonios">
              <i class="fas fa-comment-alt nav-icon"></i> TESTIMONIOS
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-item-custom" href="index.php#contacto">
              <i class="fas fa-phone-alt nav-icon"></i> CONTACTO
            </a>
          </li>
        </ul>
      </div>

      <!-- Botón "INICIAR SESIÓN" completamente a la derecha, fuera del collapse -->
      <a class="btn btn-primary login-btn ms-2 fw-bold" href="login.php" style="box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <i class="fas fa-user-circle me-1"></i> INICIAR SESIÓN
      </a>
    </div>
  </nav>

  <!-- Banner de la página -->
  <div class="page-banner text-center">
    <div class="container">
      <h1 class="animate__animated animate__fadeInDown">Nuestras Especialidades</h1>
      <p class="animate__animated animate__fadeInUp">Cuidamos su salud con atención especializada y personalizada</p>
    </div>
  </div>

  <!-- Breadcrumb -->
  <div class="container mt-3">
    <nav aria-label="breadcrumb" class="custom-breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
        <li class="breadcrumb-item active" aria-current="page">Especialidades</li>
      </ol>
    </nav>
  </div>

  <!-- Contenido principal -->
  <section class="py-5">
    <div class="container">
      <!-- Botón para volver al inicio -->
      <div class="row mb-4">
        <div class="col-md-12">
          <a href="index.php" class="btn btn-outline-primary btn-back">
            <i class="fas fa-arrow-left me-2"></i> Volver al inicio
          </a>
        </div>
      </div>
      
      <!-- Texto introductorio -->
      <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
          <h2 class="section-title mb-3">Atención Médica Especializada</h2>
          <p class="lead">En nuestra clínica contamos con médicos especialistas altamente calificados y con amplia experiencia, que utilizan tecnología de punta para brindarle la mejor atención.</p>
          <p>Ofrecemos una amplia gama de especialidades médicas para cubrir todas sus necesidades de salud bajo un mismo techo. Conozca nuestros servicios especializados:</p>
        </div>
      </div>
      
      <!-- Listado de especialidades -->
      <div class="row">
        <?php foreach($especialidades as $index => $especialidad): ?>
          <div class="col-lg-6 mb-4">
            <div class="card specialty-detail-card animate-card" data-delay="<?php echo $index * 100; ?>" id="esp<?php echo $especialidad['id']; ?>">
              <div class="card-header">
                <h3 class="card-title">
                  <div class="specialty-icon">
                    <i class="<?php echo $especialidad['icono']; ?>"></i>
                  </div>
                  <?php echo htmlspecialchars($especialidad['nombre']); ?>
                </h3>
              </div>
              <img src="img/especialidades/<?php echo htmlspecialchars($especialidad['imagen']); ?>?v=<?php echo $cacheBuster; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($especialidad['nombre']); ?>" onerror="this.src='img/default-specialty.jpg'">
              <div class="card-body">
                <p class="card-text"><?php echo htmlspecialchars($especialidad['descripcion_larga'] ?? $especialidad['descripcion_corta']); ?></p>
                
                <?php if (!empty($especialidad['servicios'])): ?>
                <h5 class="mt-4 mb-3">Servicios que ofrecemos:</h5>
                <ul class="specialty-services">
                  <?php foreach($especialidad['servicios'] as $servicio): ?>
                    <li>
                      <i class="fas fa-check-circle"></i>
                      <?php echo htmlspecialchars($servicio); ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                
                <?php if (empty($especialidad['idespecialidad']) || $especialidad['sistema_estado'] != 'ACTIVO' || $especialidad['total_doctores'] == 0): ?>
                    <div class="availability-indicator coming-soon">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Nueva especialidad:</strong> Próximamente disponible
                    </div>
                <?php else: ?>
                    <div class="availability-indicator doctors-available">
                        <i class="fas fa-user-md me-2"></i>
                        <strong><?php echo $especialidad['total_doctores']; ?> médicos disponibles</strong> para atenderle
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <?php if (empty($especialidad['idespecialidad']) || $especialidad['sistema_estado'] != 'ACTIVO' || $especialidad['total_doctores'] == 0): ?>
                        <button class="btn btn-secondary" disabled>
                            <i class="fas fa-info-circle me-2"></i> Próximamente disponible
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-user-circle me-2"></i> Inicie sesión para agendar citas
                        </a>
                    <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      
      <!-- Sección de información adicional -->
      <div class="row mt-5">
        <div class="col-lg-8 mx-auto">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
              <h4 class="mb-3 text-center text-primary">¿Por qué elegirnos?</h4>
              <div class="row">
                <div class="col-md-6">
                  <div class="d-flex mb-3">
                    <div class="me-3 text-primary">
                      <i class="fas fa-user-md fa-2x"></i>
                    </div>
                    <div>
                      <h5>Médicos Especializados</h5>
                      <p class="text-muted mb-0">Nuestro equipo está formado por profesionales con amplia experiencia y capacitación constante.</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="d-flex mb-3">
                    <div class="me-3 text-primary">
                      <i class="fas fa-hospital fa-2x"></i>
                    </div>
                    <div>
                      <h5>Instalaciones Modernas</h5>
                      <p class="text-muted mb-0">Contamos con la mejor infraestructura y tecnología para su diagnóstico y tratamiento.</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="d-flex mb-3">
                    <div class="me-3 text-primary">
                      <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <div>
                      <h5>Atención Oportuna</h5>
                      <p class="text-muted mb-0">Horarios flexibles y sistema de citas eficiente para evitar largas esperas.</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="d-flex mb-3">
                    <div class="me-3 text-primary">
                      <i class="fas fa-heart fa-2x"></i>
                    </div>
                    <div>
                      <h5>Atención Humana</h5>
                      <p class="text-muted mb-0">Nos preocupamos por el bienestar integral de cada paciente, brindando un trato cálido y personalizado.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer mejorado -->
  <footer id="footer" class="footer bg-dark text-white pt-5 pb-3">
    <div class="footer-main">
      <div class="container">
        <div class="row justify-content-between">
          <!-- Sobre la Clínica -->
          <div class="col-lg-4 col-md-6 footer-widget footer-about mb-4">
            <h3 class="widget-title border-bottom pb-2 mb-3">Sobre Nosotros</h3>
            <img loading="lazy" class="footer-logo mb-3" src="img/iconoClinica.jpg" alt="Clínica"
              style="max-height: 80px; border-radius: 5px;">
            <p>Somos una clínica comprometida con su salud y bienestar. Contamos con los mejores especialistas médicos y
              la tecnología más avanzada para brindarle una atención personalizada y de calidad.</p>
            <div class="footer-social mt-3">
              <ul class="list-inline">
                <li class="list-inline-item"><a href="https://facebook.com/clinica" class="btn btn-outline-light btn-sm"
                    aria-label="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                <li class="list-inline-item"><a href="https://twitter.com/clinica" class="btn btn-outline-light btn-sm"
                    aria-label="Twitter"><i class="fab fa-twitter"></i></a></li>
                <li class="list-inline-item"><a href="https://instagram.com/clinica"
                    class="btn btn-outline-light btn-sm" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </li>
                <li class="list-inline-item"><a href="https://youtube.com/clinica" class="btn btn-outline-light btn-sm"
                    aria-label="YouTube"><i class="fab fa-youtube"></i></a></li>
              </ul>
            </div>
          </div>

          <!-- Información de Contacto -->
          <div class="col-lg-4 col-md-6 footer-widget mt-md-0 mb-4">
            <h3 class="widget-title border-bottom pb-2 mb-3">Contáctenos</h3>
            <div class="contact-info">
              <p><i class="fas fa-map-marker-alt me-2"></i> Av. Principal 123, Ciudad</p>
              <p><i class="fas fa-phone-alt me-2"></i> Teléfono: <a href="tel:+123456789" class="text-white">+12 345
                  6789</a></p>
              <p><i class="fas fa-envelope me-2"></i> Email: <a href="mailto:contacto@clinica.com"
                  class="text-white">contacto@clinica.com</a></p>
              <p><i class="fas fa-clock me-2"></i> Lunes - Viernes: 08:00 - 18:00</p>
              <p class="ms-4">Sábado: 09:00 - 14:00</p>
              <p class="ms-4">Domingo y feriados: Cerrado</p>
            </div>
          </div>

          <!-- Servicios y Enlaces Rápidos -->
          <div class="col-lg-3 col-md-6 mt-lg-0 mb-4 footer-widget">
            <h3 class="widget-title border-bottom pb-2 mb-3">Servicios</h3>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="#consultas" class="text-white text-decoration-none"><i
                    class="fas fa-angle-right me-2"></i>Consultas Médicas</a></li>
              <li class="mb-2"><a href="#emergencias" class="text-white text-decoration-none"><i
                    class="fas fa-angle-right me-2"></i>Emergencias 24/7</a></li>
              <li class="mb-2"><a href="#laboratorio" class="text-white text-decoration-none"><i
                    class="fas fa-angle-right me-2"></i>Laboratorio Clínico</a></li>
              <li class="mb-2"><a href="#cirugia" class="text-white text-decoration-none"><i
                    class="fas fa-angle-right me-2"></i>Cirugías Especializadas</a></li>
              <li class="mb-2"><a href="#rehabilitacion" class="text-white text-decoration-none"><i
                    class="fas fa-angle-right me-2"></i>Rehabilitación</a></li>
              <li class="mb-2"><a href="#imagenologia" class="text-white text-decoration-none"><i
                    class="fas fa-angle-right me-2"></i>Diagnóstico por Imagen</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Copyright y enlaces secundarios -->
    <div class="copyright mt-4 pt-3 border-top border-secondary">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-6 text-center text-md-start">
            <div class="copyright-info">
              <span>&copy;
                <script>document.write(new Date().getFullYear())</script> Clínica. Todos los derechos reservados.
              </span>
            </div>
          </div>

          <div class="col-md-6 text-center text-md-end">
            <div class="footer-menu">
              <ul class="list-inline mb-0">
                <li class="list-inline-item"><a href="#" class="text-white text-decoration-none">Política de
                    Privacidad</a></li>
                <li class="list-inline-item"><a href="#" class="text-white text-decoration-none">Términos de Uso</a>
                </li>
                <li class="list-inline-item"><a href="#" class="text-white text-decoration-none">Accesibilidad</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts de Bootstrap y FontAwesome -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
  
  <!-- Script para animaciones y funcionalidades -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Animación para los cards de especialidades
      const animateCards = () => {
        const cards = document.querySelectorAll('.animate-card');
        
        cards.forEach(card => {
          const delay = card.getAttribute('data-delay') || 0;
          
          setTimeout(() => {
            card.classList.add('show');
          }, delay);
        });
      };
      
      // Ejecutar la animación al cargar la página
      animateCards();
      
      // Verificar si hay un ancla en la URL
      const hash = window.location.hash;
      if (hash) {
        // Si hay un ancla, hacer scroll hacia el elemento correspondiente
        const targetElement = document.querySelector(hash);
        if (targetElement) {
          setTimeout(() => {
            window.scrollTo({
              top: targetElement.offsetTop - 100,
              behavior: 'smooth'
            });
          }, 500); // Pequeño retraso para asegurar que el DOM está listo
        }
      }
    });
  </script>
</body>

</html>