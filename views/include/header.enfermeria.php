<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario']) || !$_SESSION['usuario']['autenticado']) {
    header('Location: ../../login.php');
    exit();
}

// Verificar si el usuario es enfermero
if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] !== 'ENFERMERO') {
    if ($_SESSION['usuario']['rol'] === 'ADMINISTRADOR') {
        header('Location: ../include/dashboard.administrador.php');
    } elseif ($_SESSION['usuario']['rol'] === 'DOCTOR') {
        header('Location: ../include/dashboard.doctor.php');
    }
    exit();
}

$host = "http://localhost/sistemaclinica";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Sistema de gestión clínica" />
    <meta name="author" content="" />
    <title>Sistema Clínica - Enfermería</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="<?= $host ?>/css/estiloDashboard.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="<?= $host ?>/views/include/dashboard.enfermeria.php">
            <i class="fas fa-user-nurse me-2"></i> Sistema Clínica
        </a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
            <i class="fas fa-bars"></i>
        </button>
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="input-group">
                <input class="form-control" type="text" placeholder="Buscar..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
            </div>
        </form>
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i> <?= $_SESSION['usuario']['nombres'] ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#!">Mi Perfil</a></li>
                    <li><a class="dropdown-item" href="#!">Cambiar Contraseña</a></li>
                    <li><hr class="dropdown-divider" /></li>
                    <li><a class="dropdown-item" href="<?= $host ?>/controllers/usuario.controller.php?operacion=cerrar_sesion">Cerrar Sesión</a></li>
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
                        <a class="nav-link" href="<?= $host ?>/views/include/dashboard.enfermeria.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Panel de Control
                        </a>

                        <!-- MENÚ PARA ENFERMEROS -->
                        <div class="sb-sidenav-menu-heading">Gestión de Pacientes</div>
                        
                        <a class="nav-link" href="<?= $host ?>/views/SesionEnfermeria/Triaje/registrarTriaje.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div>
                            Triaje de Pacientes
                        </a>
                        
                        <a class="nav-link" href="<?= $host ?>/views/SesionEnfermeria/HistorialTriaje/historialTriaje.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-history"></i></div>
                            Historial de Triajes
                        </a>
                        
                        <a class="nav-link" href="<?= $host ?>/views/SesionEnfermeria/Paciente/listarPaciente.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Listado de Pacientes
                        </a>

                        <div class="sb-sidenav-menu-heading">Consultas</div>
                        <a class="nav-link" href="<?= $host ?>/views/SesionEnfermeria/ConsultasPendientes/consultasPendientes.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-calendar-check"></i></div>
                            Consultas Pendientes
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Conectado como:</div>
                    <?= $_SESSION['usuario']['nombres'] . ' ' . $_SESSION['usuario']['apellidos'] ?>
                    <div class="small">
                        Enfermero/a
                    </div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>