<?php
// Aseguramos que se envíe el código de estado HTTP correcto
http_response_code(404);

// Definir la ruta base absoluta para todos los recursos
$baseURL = '/sistemaclinica/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Página no encontrada - Sistema Clínica</title>
  <!-- Bootstrap -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
    crossorigin="anonymous" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- Estilos propios -->
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Poppins', sans-serif;
    }
    .error-container {
      max-width: 800px;
    }
    .error-code {
      font-size: 120px;
      font-weight: 700;
      color: #0d6efd;
      text-shadow: 2px 2px 5px rgba(0,0,0,0.1);
    }
    .error-divider {
      width: 60px;
      height: 4px;
      background-color: #0d6efd;
      margin: 1.5rem auto;
    }
  </style>
</head>
<body>
  <section class="vh-100 d-flex align-items-center justify-content-center">
    <div class="container error-container">
      <div class="row justify-content-center">
        <div class="col-12 text-center">
          <div class="error-code mb-3">404</div>
          <div class="error-divider"></div>
          <h1 class="mt-4 h2">
            Página no <span class="fw-bolder text-primary">encontrada</span>
          </h1>
          <p class="lead my-4">
            Lo sentimos, la página que estás buscando no existe o ha sido movida.
          </p>
          <div class="mt-5">
            <a href="<?php echo $baseURL; ?>index.php" class="btn btn-primary btn-lg d-inline-flex align-items-center me-3 mb-3">
              <i class="fas fa-home me-2"></i> Volver al inicio
            </a>
            <?php if(isset($_SERVER['HTTP_REFERER'])): ?>
              <a href="<?php echo htmlspecialchars($_SERVER['HTTP_REFERER']); ?>" class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center mb-3">
                <i class="fas fa-arrow-left me-2"></i> Regresar a la página anterior
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer class="bg-white shadow-sm p-3 text-center fixed-bottom">
    <div class="container">
      <p class="mb-0">© <?php echo date('Y'); ?> Sistema Clínica. Todos los derechos reservados.</p>
    </div>
  </footer>

  <!-- Core JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" 
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" 
    crossorigin="anonymous"></script>
</body>
</html>