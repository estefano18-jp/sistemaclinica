<?php /*RUTA: sistemasclinica/views/include/header.administrador.php*/ ?>
<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario']) || !$_SESSION['usuario']['autenticado']) {
    header('Location: ../../login.php');
    exit();
}

// Verificar si el usuario es administrador
if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'DOCTOR') {
    header('Location: ../include/dashboard.doctor.php');
    exit();
}

$host = "http://localhost/sistemaclinica";

// Obtener la URL actual para determinar qué menús deben estar activos
$currentUrl = $_SERVER['REQUEST_URI'];

// Determinar qué menú debe estar abierto basado en la URL
$isPersonasActive = (strpos($currentUrl, '/views/Paciente/') !== false ||
    strpos($currentUrl, '/views/Doctor/') !== false ||
    strpos($currentUrl, '/views/Enfermeria/') !== false);

$isCitasActive = strpos($currentUrl, '/views/Citas/') !== false;

$isEspecialidadesActive = strpos($currentUrl, '/views/Especialidad/') !== false;

$isAdminActive = strpos($currentUrl, '/views/admin/') !== false;

$isDashboardActive = (strpos($currentUrl, '/views/include/dashboard.administrador.php') !== false ||
    $currentUrl === '/sistemaclinica/' ||
    $currentUrl === '/sistemaclinica');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Sistema de gestión clínica" />
    <meta name="author" content="" />
    <title>Sistema Clínica</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="<?= $host ?>/css/estiloDashboard.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        /* Estilos mínimos para resaltar enlaces activos */
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff !important;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="<?= $host ?>/views/include/dashboard.administrador.php">Sistema Clínica</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
            <i class="fas fa-bars"></i>
        </button>
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="input-group">
                <input class="form-control" type="text" placeholder="Buscar..." aria-label="Search for..."
                    aria-describedby="btnNavbarSearch" />
                <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i
                        class="fas fa-search"></i></button>
            </div>
        </form>
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="javascript:void(0)" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i> <?= $_SESSION['usuario']['nombres'] ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#!">Mi Perfil</a></li>
                    <li><a class="dropdown-item" href="#!">Cambiar Contraseña</a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item"
                            href="<?= $host ?>/controllers/usuario.controller.php?operacion=cerrar_sesion">Cerrar
                            Sesión</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Inicio</div>
                        <a class="nav-link <?= $isDashboardActive ? 'active' : '' ?>" href="<?= $host ?>/views/include/dashboard.administrador.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Panel de Control
                        </a>

                        <!-- MENÚ PARA ADMINISTRADORES -->
                        <div class="sb-sidenav-menu-heading">Gestión Clínica</div>
                        <a class="nav-link <?= $isPersonasActive ? 'active' : 'collapsed' ?>" href="#" data-bs-toggle="collapse"
                            data-bs-target="#collapsePersonas" aria-expanded="<?= $isPersonasActive ? 'true' : 'false' ?>" aria-controls="collapsePersonas">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Personas
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse <?= $isPersonasActive ? 'show' : '' ?>" id="collapsePersonas" aria-labelledby="headingOne">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?= strpos($currentUrl, '/views/Paciente/RegistrarPaciente/') !== false ? 'active' : '' ?>"
                                    href="<?= $host ?>/views/Paciente/RegistrarPaciente/registrarPaciente.php">Registro
                                    Pacientes</a>
                                <a class="nav-link <?= strpos($currentUrl, '/views/Paciente/ListarPaciente/') !== false ? 'active' : '' ?>"
                                    href="<?= $host ?>/views/Paciente/ListarPaciente/listarPaciente.php">Lista
                                    Pacientes</a>
                                <a class="nav-link <?= strpos($currentUrl, '/views/Doctor/RegistrarDoctor/') !== false ? 'active' : '' ?>"
                                    href="<?= $host ?>/views/Doctor/RegistrarDoctor/registrarDoctor.php">Registro
                                    Doctores</a>
                                <a class="nav-link <?= strpos($currentUrl, '/views/Doctor/listarDoctor/') !== false ? 'active' : '' ?>"
                                    href="<?= $host ?>/views/Doctor/listarDoctor/listarDoctor.php">Lista
                                    Doctores</a>
                                <a class="nav-link <?= strpos($currentUrl, '/views/Enfermeria/RegistrarEnfermero/') !== false ? 'active' : '' ?>"
                                    href="<?= $host ?>/views/Enfermeria/RegistrarEnfermero/registrarEnfermero.php">Registro
                                    Enfermeros</a>
                                <a class="nav-link <?= strpos($currentUrl, '/views/Enfermeria/ListarEnfermero/') !== false ? 'active' : '' ?>"
                                    href="<?= $host ?>/views/Enfermeria/ListarEnfermero/listarEnfermero.php">Lista
                                    Enfermeros</a>
                            </nav>
                        </div>

                        <a class="nav-link <?= $isCitasActive ? 'active' : 'collapsed' ?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCitas"
                            aria-expanded="<?= $isCitasActive ? 'true' : 'false' ?>" aria-controls="collapseCitas">
                            <div class="sb-nav-link-icon"><i class="fas fa-calendar-check"></i></div>
                            Citas
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse <?= $isCitasActive ? 'show' : '' ?>" id="collapseCitas" aria-labelledby="headingThree">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?= strpos($currentUrl, '/views/Citas/ProgramarCita/') !== false ? 'active' : '' ?>"
                                    href="<?= $host ?>/views/Citas/ProgramarCita/programarCita.php">Programar Citas</a>
                                <a class="nav-link <?= strpos($currentUrl, '/views/Citas/GestionarCita/') !== false ? 'active' : '' ?>"
                                    href="<?= $host ?>/views/Citas/GestionarCita/gestionarCita.php">Gestionar Citas</a>
                            </nav>
                        </div>

                        <a class="nav-link <?= $isEspecialidadesActive ? 'active' : '' ?>"
                            href="<?= $host ?>/views/Especialidad/ListarEspecialidad/listarEspecialidad.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-stethoscope"></i></div>
                            Especialidades
                        </a>

                        <div class="sb-sidenav-menu-heading">Ventas y Facturación</div>
                        <a class="nav-link <?= strpos($currentUrl, '/views/GestionarDevolucion/ProcesoDevolucion/') !== false ? 'active' : '' ?>"
                            href="<?= $host ?>/views/GestionarDevolucion/ProcesoDevolucion/procesoDevolucion.php">
                            <i class="fas fa-undo me-2"></i>Gestionar Devolución
                        </a>
                        <a class="nav-link <?= strpos($currentUrl, '/views/GestionarDevolucion/HistorialDevolucion/') !== false ? 'active' : '' ?>"
                            href="<?= $host ?>/views/GestionarDevolucion/HistorialDevolucion/historialDevolucion.php">
                            <i class="fas fa-history me-2"></i>Historial Devoluciones
                        </a>
                        <a class="nav-link <?= strpos($currentUrl, '/views/ArqueoCaja/arqueoCaja.php') !== false ? 'active' : '' ?>"
                            href="<?= $host ?>/views/ArqueoCaja/arqueoCaja.php">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Arqueo de Caja
                        </a>
                        <a class="nav-link <?= strpos($currentUrl, '/views/MovimientoCaja/movimientoCaja.php') !== false ? 'active' : '' ?>"
                            href="<?= $host ?>/views/MovimientoCaja/movimientoCaja.php">
                            <i class="fas fa-exchange-alt me-2"></i>Movimiento Caja
                        </a>

                        <div class="sb-sidenav-menu-heading">Configuración</div>
                        <a class="nav-link <?= $isAdminActive ? 'active' : 'collapsed' ?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapseAdmin"
                            aria-expanded="<?= $isAdminActive ? 'true' : 'false' ?>" aria-controls="collapseAdmin">
                            <div class="sb-nav-link-icon"><i class="fas fa-cogs"></i></div>
                            Administración
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse <?= $isAdminActive ? 'show' : '' ?>" id="collapseAdmin" aria-labelledby="headingSix">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?= strpos($currentUrl, '/views/admin/usuarios.php') !== false ? 'active' : '' ?>"
                                    href="<?= $host ?>/views/admin/usuarios.php">Gestión de Usuarios</a>
                                <a class="nav-link <?= strpos($currentUrl, '/views/admin/Footer.php ') !== false ? 'active' : '' ?>"
                                    href="<?= $host ?>/views/admin/Footer.php">Fotter</a>
                                <a class="nav-link <?= strpos($currentUrl, '/views/admin/especialidades.php') !== false ? 'active' : '' ?>"
                                    href="<?= $host ?>/views/admin/especialidades.php">Especialidades</a>
                                <a class="nav-link <?= strpos($currentUrl, '/views/admin/Carruzel.php') !== false ? 'active' : '' ?>"
                                    href="<?= $host ?>/views/admin/Carruzel.php">Carrusel</a>
                                <a class="nav-link <?= strpos($currentUrl, '/views/admin/Promociones.php') !== false ? 'active' : '' ?>"
                                    href="<?= $host ?>/views/admin/Promociones.php">promociones</a>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Conectado como:</div>
                    <?= $_SESSION['usuario']['nombres'] . ' ' . $_SESSION['usuario']['apellidos'] ?>
                    <div class="small">
                        Administrador
                    </div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <!-- Aquí empieza el contenido principal -->