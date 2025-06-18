<?php
// Incluir archivo de conexión
require_once 'models/Conexion.php';

// Creación de conexión
$conexion = new Conexion();
$conn = $conexion->getConexion();

// Obtener las imágenes del carrusel
try {
  $stmt = $conn->prepare("SELECT * FROM carrusel ORDER BY id ASC LIMIT 3");
  $stmt->execute();
  $carruselItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  error_log("Error al obtener imágenes del carrusel: " . $e->getMessage());
  $carruselItems = [];
}

// Obtener promociones visibles inicialmente (primeras 2)
try {
  $stmt = $conn->prepare("SELECT * FROM promociones WHERE estado = 1 ORDER BY id DESC LIMIT 2");
  $stmt->execute();
  $promocionesIniciales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  error_log("Error al obtener promociones iniciales: " . $e->getMessage());
  $promocionesIniciales = [];
}

// Obtener todas las demás promociones (a partir de la 3ra)
try {
  $stmt = $conn->prepare("SELECT * FROM promociones WHERE estado = 1 ORDER BY id DESC LIMIT 100 OFFSET 2");
  $stmt->execute();
  $promocionesAdicionales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  error_log("Error al obtener promociones adicionales: " . $e->getMessage());
  $promocionesAdicionales = [];
}

// Obtener las especialidades para mostrar en la página principal (primeras 4)
try {
  $stmt = $conn->prepare("SELECT * FROM web_especialidades WHERE estado = 1 ORDER BY orden ASC LIMIT 4");
  $stmt->execute();
  $especialidadesDestacadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  error_log("Error al obtener especialidades destacadas: " . $e->getMessage());
  $especialidadesDestacadas = [];
}

// Obtener todas las especialidades para el modal
try {
  $stmt = $conn->prepare("SELECT * FROM web_especialidades WHERE estado = 1 ORDER BY orden ASC");
  $stmt->execute();
  $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  error_log("Error al obtener todas las especialidades: " . $e->getMessage());
  $especialidades = [];
}

// Generar un código único para prevenir el cache de imágenes
$cacheBuster = time();

// Función para obtener la última modificación de especialidades.php
function getEspecialidadesLastModified()
{
  $filename = 'especialidades.php';
  if (file_exists($filename)) {
    return filemtime($filename);
  }
  return time();
}

// Obtener timestamp de última modificación
$lastModified = getEspecialidadesLastModified();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
  <title>Clínica Médica - Cuidamos de su Salud</title>

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
    /* Mejoras para el carrusel sin cambiar su apariencia */
    .carousel-item img {
      width: 100%;
      height: auto;
      object-fit: cover;
    }

    /* Aseguramos que los controles del carrusel sean visibles */
    .carousel-control-prev,
    .carousel-control-next {
      opacity: 0.7;
    }

    .carousel-control-prev:hover,
    .carousel-control-next:hover {
      opacity: 1;
    }

    /* Aseguramos que los indicadores del carrusel sean visibles */
    .carousel-indicators button {
      background-color: rgba(255, 255, 255, 0.5);
      border: 1px solid rgba(0, 0, 0, 0.2);
    }

    .carousel-indicators .active {
      background-color: #fff;
    }

    /* Estilos para las promociones adicionales (inicialmente ocultas) */
    #promocionesAdicionales {
      display: none;
      animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Efecto hover para las tarjetas de promoción */
    .promotion-item {
      transition: all 0.3s ease;
    }

    .promotion-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15) !important;
    }

    /* Estilo para el botón Ver más/menos */
    .btn-ver-mas {
      transition: all 0.3s ease;
    }

    .btn-ver-mas:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Animación para el icono */
    .btn-ver-mas i {
      transition: transform 0.3s ease;
    }

    .btn-ver-mas.active i {
      transform: rotate(180deg);
    }

    /* Estilos para el modal de especialidades */
    .modal-content {
      border: none;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
      border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    }

    .modal-footer {
      border-top: 1px solid #e9ecef;
    }

    /* Estilos para los items de especialidades en el modal */
    .specialty-icon-sm {
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f8f9fa;
      border-radius: 50%;
      font-size: 18px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .specialty-item {
      padding: 15px;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    .specialty-item:hover {
      background-color: #f8f9fa;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }

    /* Animación suave para el modal */
    .modal.fade .modal-dialog {
      transition: transform 0.3s ease-out;
      transform: translate(0, -50px);
    }

    .modal.show .modal-dialog {
      transform: none;
    }

    /* Estilo para el botón de cerrar en la cabecera */
    .btn-close-white {
      filter: brightness(0) invert(1);
    }

    /* Estilo para el icono grande en modal de especialidad individual */
    .specialty-icon-modal {
      width: 80px;
      height: 80px;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f8f9fa;
      border-radius: 50%;
      color: #0d6efd;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      margin: 0 auto;
    }

    /* Estilo para la lista de servicios en modal */
    .specialty-services {
      list-style: none;
      padding-left: 0;
    }

    .specialty-services li {
      padding: 6px 0;
      border-bottom: 1px solid #f0f0f0;
    }

    .specialty-services li:last-child {
      border-bottom: none;
    }

    /* Estilo para cuando hay error al cargar imagen */
    .modal img[src='img/default-specialty.jpg'] {
      max-height: 200px;
      object-fit: contain;
      background-color: #f8f9fa;
      padding: 20px;
    }

    /* Transición suave para la imagen */
    .modal img {
      transition: transform 0.3s ease;
    }

    .modal img:hover {
      transform: scale(1.02);
    }

    /* Estilo para el botón de WhatsApp */
    .whatsapp-contact-wrapper {
      transition: all 0.3s ease;
    }

    .whatsapp-contact-wrapper:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .btn-success {
      transition: all 0.3s ease;
    }

    .btn-success:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }

    /* Animación para el mapa */
    .map-wrapper {
      transition: all 0.3s ease;
      overflow: hidden;
    }

    .map-wrapper:hover {
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .map-wrapper iframe {
      transition: all 0.5s ease;
    }

    .map-wrapper:hover iframe {
      transform: scale(1.02);
    }
    
    /* Mejoras de espaciado para la sección de especialidades */
    #especialidades, .specialties-section {
      padding-top: 80px;
      padding-bottom: 70px;
      background-color: #f9f9f9;
      margin-top: 20px;
      margin-bottom: 20px;
    }

    /* Título de la sección */
    .section-title {
      margin-bottom: 50px;
      padding-top: 15px;
    }

    .title-bold {
      font-weight: 700;
      margin-bottom: 15px;
      position: relative;
      padding-bottom: 15px;
    }

    .title-bold:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 50px;
      height: 3px;
      background-color: #0d6efd;
    }

    .title-desc {
      font-size: 1.1rem;
      color: #6c757d;
      margin-top: 15px;
      margin-bottom: 30px;
    }

    /* Espacio entre las filas */
    .specialties-section .row {
      margin-bottom: 30px;
    }

    /* Tarjetas de especialidades */
    .specialty-card {
      background-color: #fff;
      padding: 30px 25px;
      margin-bottom: 30px;
      height: 100%;
      transition: all 0.3s ease;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      text-align: center;
      position: relative;
    }

    .specialty-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    .specialty-card h4 {
      margin-top: 20px;
      margin-bottom: 15px;
      font-size: 1.4rem;
      font-weight: 600;
      color: #333;
    }

    .specialty-card p {
      margin-bottom: 20px;
      color: #6c757d;
      font-size: 0.95rem;
      line-height: 1.6;
    }

    /* Icono de especialidad */
    .specialty-icon {
      width: 70px;
      height: 70px;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #0d6efd;
      color: white;
      border-radius: 50%;
      margin: 0 auto 20px;
      font-size: 25px;
      box-shadow: 0 5px 15px rgba(13, 110, 253, 0.2);
    }

    /* Botones de más información */
    .btn-outline-primary {
      border-color: #0d6efd;
      color: #0d6efd;
      padding: 6px 15px;
      font-size: 0.85rem;
      border-radius: 20px;
      transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
      background-color: #0d6efd;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2);
    }

    /* Botón de ver todas las especialidades */
    .specialties-section .mt-4 {
      margin-top: 40px !important;
    }

    .specialties-section .btn-primary {
      padding: 10px 20px;
      font-size: 1rem;
      border-radius: 5px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2);
    }

    .specialties-section .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(13, 110, 253, 0.3);
    }

    /* Espacio para la sección de promociones */
    #promociones {
      padding-top: 80px;
    }

    /* Mejoras para dispositivos móviles */
    @media (max-width: 768px) {
      #especialidades, .specialties-section {
        padding-top: 60px;
        padding-bottom: 50px;
      }
      
      .specialty-card {
        padding: 20px 15px;
      }
      
      .specialty-icon {
        width: 60px;
        height: 60px;
        font-size: 20px;
      }
      
      .specialty-card h4 {
        font-size: 1.2rem;
      }
    }
  </style>
</head>

<body id="inicio">
  <!-- Barra de navegación -->
  <nav class="navbar navbar-expand-md navbar-light bg-light sticky-top">
    <div class="container">
      <!-- Logo y nombre a la izquierda con mejoras de visibilidad -->
      <a class="navbar-brand d-flex align-items-center" href="#inicio">
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
            <a class="nav-link nav-item-custom active" href="#inicio">
              <i class="fas fa-home nav-icon"></i> INICIO
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-item-custom" href="#especialidades">
              <i class="fas fa-stethoscope nav-icon"></i> ESPECIALIDADES
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-item-custom" href="#promociones">
              <i class="fas fa-tag nav-icon"></i> PROMOCIONES
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-item-custom" href="#contacto">
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

  <!-- Carrusel -->
  <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
      <?php foreach ($carruselItems as $key => $item): ?>
        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="<?php echo $key; ?>"
          class="<?php echo ($key == 0) ? 'active' : ''; ?>"
          aria-current="<?php echo ($key == 0) ? 'true' : 'false'; ?>"
          aria-label="Slide <?php echo $key + 1; ?>"></button>
      <?php endforeach; ?>
    </div>
    <div class="carousel-inner">
      <?php foreach ($carruselItems as $key => $item): ?>
        <div class="carousel-item <?php echo ($key == 0) ? 'active' : ''; ?>">
          <img src="img/carrusel/<?php echo htmlspecialchars($item['imagen']); ?>?v=<?php echo $cacheBuster; ?>"
            class="d-block w-100 carousel-image"
            data-src="img/carrusel/<?php echo htmlspecialchars($item['imagen']); ?>"
            alt="<?php echo htmlspecialchars($item['descripcion']); ?>">
          <div class="carousel-caption">
            <h3 class="animate__animated animate__fadeInDown"><?php echo htmlspecialchars($item['titulo']); ?></h3>
            <p class="animate__animated animate__fadeInUp"><?php echo htmlspecialchars($item['texto']); ?></p>
            <a href="<?php echo htmlspecialchars($item['boton_enlace']); ?>"
              class="btn btn-primary animate__animated animate__fadeInUp">
              <?php echo htmlspecialchars($item['boton_texto']); ?>
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
      data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
      data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Siguiente</span>
    </button>
  </div>

  <!-- Sección de especialidades -->
  <section id="especialidades" class="specialties-section">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <div class="section-title text-center mb-5 pt-4">
            <h2 class="title-bold">NUESTRAS ESPECIALIDADES</h2>
            <p class="title-desc">Ofrecemos atención especializada en diversas áreas de la medicina</p>
          </div>
        </div>
      </div>

      <div class="row">
        <?php if (count($especialidadesDestacadas) > 0): ?>
          <?php foreach ($especialidadesDestacadas as $especialidad): ?>
            <div class="col-lg-3 col-md-6 mb-4 d-flex">
              <div class="specialty-card w-100">
                <div class="specialty-icon">
                  <i class="<?php echo htmlspecialchars($especialidad['icono']); ?>"></i>
                </div>
                <h4><?php echo htmlspecialchars($especialidad['nombre']); ?></h4>
                <p><?php echo htmlspecialchars($especialidad['descripcion_corta']); ?></p>
                <button class="btn btn-sm btn-outline-primary mt-2"
                  data-bs-toggle="modal"
                  data-bs-target="#especialidadModal<?php echo $especialidad['id']; ?>">
                  Más información
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12 text-center">
            <p>No hay especialidades disponibles en este momento.</p>
          </div>
        <?php endif; ?>
      </div>

      <div class="row mt-5">
        <div class="col-md-12 text-center">
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#especialidadesModal">
            Ver todas las especialidades <i class="fas fa-list-ul ms-2"></i>
          </button>
        </div>
      </div>
    </div>
  </section>

  <!-- Sección de promociones -->
  <section id="promociones" class="promotion-area py-5">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <div class="section-title text-center mb-5">
            <h2 class="title-bold">NUESTRAS PROMOCIONES</h2>
            <p class="title-desc">Cuidamos de su salud con servicios de calidad a precios accesibles</p>
          </div>
        </div>
      </div>

      <!-- Promociones iniciales -->
      <div class="row">
        <?php if (count($promocionesIniciales) > 0): ?>
          <?php foreach ($promocionesIniciales as $promo): ?>
            <div class="col-lg-6 col-md-6 mb-4">
              <div class="card promotion-item border-0 rounded shadow-sm h-100">
                <div class="position-relative">
                  <img src="img/promociones/<?php echo htmlspecialchars($promo['imagen']); ?>?v=<?php echo $cacheBuster; ?>"
                    class="card-img-top" alt="<?php echo htmlspecialchars($promo['titulo']); ?>">
                  <!-- El badge de descuento ha sido eliminado -->
                </div>
                <div class="card-body">
                  <h4 class="card-title text-primary"><?php echo htmlspecialchars($promo['titulo']); ?></h4>
                  <p class="card-text"><?php echo htmlspecialchars($promo['descripcion']); ?></p>
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <p class="mb-0 text-decoration-line-through text-muted">S/<?php echo number_format($promo['precio_regular'], 2); ?></p>
                      <p class="text-primary fw-bold fs-4 mb-0">S/<?php echo number_format($promo['precio_oferta'], 2); ?></p>
                    </div>
                    <!-- El botón "Reservar cita" ha sido eliminado -->
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <!-- Si no hay promociones iniciales -->
          <div class="col-12 text-center mb-4">
            <p>No hay promociones disponibles en este momento.</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Promociones adicionales (inicialmente ocultas) -->
      <div id="promocionesAdicionales">
        <div class="row">
          <?php if (count($promocionesAdicionales) > 0): ?>
            <?php foreach ($promocionesAdicionales as $promo): ?>
              <div class="col-lg-6 col-md-6 mb-4">
                <div class="card promotion-item border-0 rounded shadow-sm h-100">
                  <div class="position-relative">
                    <img src="img/promociones/<?php echo htmlspecialchars($promo['imagen']); ?>?v=<?php echo $cacheBuster; ?>"
                      class="card-img-top" alt="<?php echo htmlspecialchars($promo['titulo']); ?>">
                    <!-- El badge de descuento ha sido eliminado -->
                  </div>
                  <div class="card-body">
                    <h4 class="card-title text-primary"><?php echo htmlspecialchars($promo['titulo']); ?></h4>
                    <p class="card-text"><?php echo htmlspecialchars($promo['descripcion']); ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <p class="mb-0 text-decoration-line-through text-muted">S/<?php echo number_format($promo['precio_regular'], 2); ?></p>
                        <p class="text-primary fw-bold fs-4 mb-0">S/<?php echo number_format($promo['precio_oferta'], 2); ?></p>
                      </div>
                      <!-- El botón "Reservar cita" ha sido eliminado -->
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Botón para ver más promociones -->
      <?php if (count($promocionesAdicionales) > 0): ?>
        <div class="row mt-4">
          <div class="col-md-12 text-center">
            <button id="btnVerMasPromociones" class="btn btn-primary btn-ver-mas">
              <span id="btnTexto">Ver Todas las Promociones</span> <i class="fas fa-chevron-down ms-2"></i>
            </button>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- La sección de testimonios ha sido eliminada -->

  <!-- Sección de Contacto con WhatsApp -->
  <section id="contacto" class="contact-section py-5">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <div class="section-title text-center mb-5">
            <h2 class="title-bold">CONTACTO</h2>
            <p class="title-desc">Estamos a su disposición para resolver cualquier consulta</p>
          </div>
        </div>
      </div>

      <div class="row justify-content-center">
        <!-- WhatsApp Contacto -->
        <div class="col-lg-8 mb-5">
          <div class="whatsapp-contact-wrapper text-center p-4 bg-white rounded shadow-sm">
            <h4 class="mb-3"><i class="fab fa-whatsapp text-success me-2"></i> Contacto directo por WhatsApp</h4>
            <p class="mb-4">Para una atención más rápida y personalizada, contáctenos directamente por WhatsApp</p>
            <a href="https://wa.me/123456789" class="btn btn-success btn-lg d-flex align-items-center justify-content-center mx-auto" style="max-width: 300px;" target="_blank">
              <i class="fab fa-whatsapp me-2 fa-lg"></i> Hablar con un asesor
            </a>
            <p class="small text-muted mt-3">Respuesta rápida de lunes a viernes de 8:00 a 18:00</p>
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
                <script>
                  document.write(new Date().getFullYear())
                </script> Clínica. Todos los derechos reservados.
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

  <!-- Modal de Especialidades -->
  <div class="modal fade" id="especialidadesModal" tabindex="-1" aria-labelledby="especialidadesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="especialidadesModalLabel">Todas Nuestras Especialidades</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <!-- Primera columna de especialidades -->
            <div class="col-md-6">
              <?php
              $mitad = ceil(count($especialidades) / 2);
              foreach (array_slice($especialidades, 0, $mitad) as $especialidad):
              ?>
                <div class="specialty-item mb-4">
                  <div class="d-flex align-items-center mb-2">
                    <div class="specialty-icon-sm me-3">
                      <i class="<?php echo htmlspecialchars($especialidad['icono']); ?> text-primary"></i>
                    </div>
                    <h5 class="mb-0"><?php echo htmlspecialchars($especialidad['nombre']); ?></h5>
                  </div>
                  <p><?php echo htmlspecialchars($especialidad['descripcion_corta']); ?></p>
                  <?php if (in_array($especialidad['id'], array_column($especialidadesDestacadas, 'id'))): ?>
                    <!-- Si está en las destacadas, enlazar a su modal específico -->
                    <button class="btn btn-sm btn-outline-primary"
                      data-bs-toggle="modal"
                      data-bs-target="#especialidadModal<?php echo $especialidad['id']; ?>"
                      data-bs-dismiss="modal">
                      Ver detalles
                    </button>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Segunda columna de especialidades -->
            <div class="col-md-6">
              <?php foreach (array_slice($especialidades, $mitad) as $especialidad): ?>
                <div class="specialty-item mb-4">
                  <div class="d-flex align-items-center mb-2">
                    <div class="specialty-icon-sm me-3">
                      <i class="<?php echo htmlspecialchars($especialidad['icono']); ?> text-primary"></i>
                    </div>
                    <h5 class="mb-0"><?php echo htmlspecialchars($especialidad['nombre']); ?></h5>
                  </div>
                  <p><?php echo htmlspecialchars($especialidad['descripcion_corta']); ?></p>
                  <?php if (in_array($especialidad['id'], array_column($especialidadesDestacadas, 'id'))): ?>
                    <!-- Si está en las destacadas, enlazar a su modal específico -->
                    <button class="btn btn-sm btn-outline-primary"
                      data-bs-toggle="modal"
                      data-bs-target="#especialidadModal<?php echo $especialidad['id']; ?>"
                      data-bs-dismiss="modal">
                      Ver detalles
                    </button>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <a href="especialidades.php?t=<?php echo $lastModified; ?>" class="btn btn-primary">Ver detalles completos</a>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modales individuales para cada especialidad -->
  <?php foreach ($especialidadesDestacadas as $especialidad): ?>
    <div class="modal fade" id="especialidadModal<?php echo $especialidad['id']; ?>" tabindex="-1" aria-labelledby="especialidadModalLabel<?php echo $especialidad['id']; ?>" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="especialidadModalLabel<?php echo $especialidad['id']; ?>">
              <?php echo htmlspecialchars($especialidad['nombre']); ?>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="text-center mb-3">
              <div class="specialty-icon-modal mx-auto mb-3">
                <i class="<?php echo htmlspecialchars($especialidad['icono']); ?> fa-2x"></i>
              </div>
              <img src="img/especialidades/<?php echo htmlspecialchars($especialidad['imagen']); ?>?v=<?php echo $cacheBuster; ?>"
                class="img-fluid rounded mb-3"
                alt="<?php echo htmlspecialchars($especialidad['nombre']); ?>"
                onerror="this.src='img/default-specialty.jpg'">
            </div>

            <p><?php echo htmlspecialchars($especialidad['descripcion_larga'] ?? $especialidad['descripcion_corta']); ?></p>

            <?php
            // Obtener servicios para esta especialidad
            try {
              $stmt = $conn->prepare("SELECT servicio FROM web_especialidades_servicios WHERE web_especialidad_id = ? ORDER BY id ASC");
              $stmt->execute([$especialidad['id']]);
              $servicios = $stmt->fetchAll(PDO::FETCH_COLUMN);
              if (count($servicios) > 0):
            ?>
                <h6 class="mt-3 mb-2">Servicios que ofrecemos:</h6>
                <ul class="specialty-services">
                  <?php foreach ($servicios as $servicio): ?>
                    <li><i class="fas fa-check-circle text-primary me-2"></i> <?php echo htmlspecialchars($servicio); ?></li>
                  <?php endforeach; ?>
                </ul>
            <?php
              endif;
            } catch (PDOException $e) {
              error_log("Error al obtener servicios para modal: " . $e->getMessage());
            }
            ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <a href="especialidades.php#esp<?php echo $especialidad['id']; ?>" class="btn btn-primary">Más detalles</a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <!-- El modal para dejar testimonio ha sido eliminado -->

  <!-- Scripts de Bootstrap y FontAwesome -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

  <!-- Script para actualizar automáticamente las imágenes del carrusel y otras funcionalidades -->
  <script>
    // Función para actualizar las imágenes con un nuevo parámetro de timestamp
    function refreshCarouselImages() {
      const carouselImages = document.querySelectorAll('.carousel-image');
      const timestamp = new Date().getTime(); // Obtener un timestamp único

      carouselImages.forEach(function(img) {
        const originalSrc = img.getAttribute('data-src');
        if (originalSrc) {
          img.src = originalSrc + '?v=' + timestamp;
        }
      });

      // También actualizar las animaciones del carrusel
      const captions = document.querySelectorAll('.carousel-caption h3, .carousel-caption p, .carousel-caption a');
      captions.forEach(function(element) {
        // Reiniciar animaciones eliminando y volviendo a agregar las clases de animación
        const animationClass = Array.from(element.classList).find(c => c.startsWith('animate__'));
        if (animationClass) {
          element.classList.remove(animationClass);
          // Forzar un reflow
          void element.offsetWidth;
          // Agregar de nuevo la clase para reiniciar la animación
          element.classList.add(animationClass);
        }
      });
    }

    // Actualizar las imágenes del carrusel cada 30 segundos
    setInterval(refreshCarouselImages, 30000);

    // También actualizar las imágenes al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
      refreshCarouselImages();

      // Actualizar también cuando se cambia de slide en el carrusel
      const carousel = document.getElementById('carouselExampleIndicators');
      if (carousel) {
        carousel.addEventListener('slide.bs.carousel', function() {
          refreshCarouselImages();
        });
      }

      // Gestionar el botón "Ver Todas las Promociones"
      const btnVerMas = document.getElementById('btnVerMasPromociones');
      const promocionesAdicionales = document.getElementById('promocionesAdicionales');
      const btnTexto = document.getElementById('btnTexto');

      if (btnVerMas && promocionesAdicionales) {
        btnVerMas.addEventListener('click', function() {
          if (promocionesAdicionales.style.display === 'block') {
            // Ocultar promociones adicionales
            promocionesAdicionales.style.display = 'none';
            btnTexto.textContent = 'Ver Todas las Promociones';
            btnVerMas.classList.remove('active');

            // Hacer scroll hacia la sección de promociones
            document.getElementById('promociones').scrollIntoView({
              behavior: 'smooth'
            });
          } else {
            // Mostrar promociones adicionales
            promocionesAdicionales.style.display = 'block';
            btnTexto.textContent = 'Ver Menos Promociones';
            btnVerMas.classList.add('active');

            // Hacer scroll hacia la primera promoción adicional
            promocionesAdicionales.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          }
        });
      }

      // Configurar el evento para animar los elementos del modal cuando se muestra
      const especialidadesModal = document.getElementById('especialidadesModal');
      if (especialidadesModal) {
        especialidadesModal.addEventListener('shown.bs.modal', function() {
          // Animar las entradas de especialidades con un pequeño retraso entre cada una
          const specialtyItems = document.querySelectorAll('.specialty-item');
          specialtyItems.forEach((item, index) => {
            setTimeout(() => {
              item.style.opacity = '0';
              item.style.transform = 'translateY(20px)';

              // Forzar un reflow
              void item.offsetWidth;

              // Añadir transición
              item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
              item.style.opacity = '1';
              item.style.transform = 'translateY(0)';
            }, index * 100); // 100ms de retraso entre cada animación
          });
        });

        // Reiniciar las animaciones cuando se cierra el modal
        especialidadesModal.addEventListener('hidden.bs.modal', function() {
          const specialtyItems = document.querySelectorAll('.specialty-item');
          specialtyItems.forEach(item => {
            item.style.opacity = '';
            item.style.transform = '';
            item.style.transition = '';
          });
        });
      }

      // Función para verificar si hay actualizaciones en especialidades.php
      function checkForUpdates() {
        const lastKnownModified = <?php echo $lastModified; ?>;

        fetch('especialidades.php?check=' + Date.now())
          .then(response => {
            const lastModified = new Date(response.headers.get('Last-Modified')).getTime() / 1000;
            if (lastModified > lastKnownModified) {
              // Si hay una actualización, refrescar el enlace al archivo especialidades.php
              const especialidadesLink = document.querySelector('.modal-footer .btn-primary');
              if (especialidadesLink) {
                especialidadesLink.href = 'especialidades.php?t=' + lastModified;
              }

              // También podríamos mostrar una notificación al usuario
              console.log('Se han detectado actualizaciones en las especialidades');
            }
          })
          .catch(error => {
            console.error('Error al verificar actualizaciones:', error);
          });
      }

      // Verificar actualizaciones cada 5 minutos (300000 ms)
      setInterval(checkForUpdates, 300000);

      // Función para verificar si hay cambios en las especialidades
      function checkForEspecialidadesChanges() {
        // Obtener timestamp actual
        const timestamp = new Date().getTime();

        // Realizar solicitud AJAX para verificar cambios
        fetch('check_updates.php?module=especialidades&t=' + timestamp)
          .then(response => response.json())
          .then(data => {
            if (data.updated) {
              console.log('Se han detectado cambios en las especialidades');

              // Si hay cambios, recargar la sección de especialidades
              fetch('get_especialidades.php?t=' + timestamp)
                .then(response => response.text())
                .then(html => {
                  // Actualizar la sección de especialidades
                  const especialidadesSection = document.getElementById('especialidades');
                  if (especialidadesSection) {
                    especialidadesSection.innerHTML = html;

                    // Reactivar los eventos
                    const btns = especialidadesSection.querySelectorAll('[data-bs-toggle="modal"]');
                    btns.forEach(btn => {
                      btn.addEventListener('click', function() {
                        const target = this.getAttribute('data-bs-target');
                        const modal = new bootstrap.Modal(document.querySelector(target));
                        modal.show();
                      });
                    });
                  }

                  // Actualizar el modal general de especialidades
                  fetch('get_especialidades_modal.php?t=' + timestamp)
                    .then(response => response.text())
                    .then(modalHtml => {
                      const modalBody = document.querySelector('#especialidadesModal .modal-body');
                      if (modalBody) {
                        modalBody.innerHTML = modalHtml;
                      }
                    })
                    .catch(error => console.error('Error al actualizar modal:', error));
                })
                .catch(error => console.error('Error al obtener especialidades:', error));
            }
          })
          .catch(error => console.error('Error al verificar actualizaciones:', error));
      }

      // Verificar cambios cada 5 minutos
      setInterval(checkForEspecialidadesChanges, 300000);
    });
  </script>
</body>

</html>