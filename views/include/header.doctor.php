<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario']) || !$_SESSION['usuario']['autenticado']) {
    header('Location: ../../login.php');
    exit();
}

// Verificar si el usuario es doctor
if (!isset($_SESSION['usuario']['rol']) || $_SESSION['usuario']['rol'] !== 'DOCTOR') {
    header('Location: ../include/dashboard.administrador.php');
    exit();
}

$host = "http://localhost/sistemaclinica";
$idcolaborador = $_SESSION['usuario']['idcolaborador'];
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
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="<?= $host ?>/views/include/dashboard.doctor.php">Sistema Clínica</a>
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
                        <a class="nav-link" href="<?= $host ?>/views/include/dashboard.doctor.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Panel de Control
                        </a>
                        
                        <!-- MENÚ PARA DOCTORES -->
                        <div class="sb-sidenav-menu-heading">Gestión Médica</div>
                        
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCitasDoctor" aria-expanded="false" aria-controls="collapseCitasDoctor">
                            <div class="sb-nav-link-icon"><i class="fas fa-calendar-check"></i></div>
                            Citas Médicas
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseCitasDoctor" aria-labelledby="headingCitas" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="<?= $host ?>/views/SesionDoctor/CitasMedicas/realizarCita.php">Realizar Cita</a>
                                <a class="nav-link" href="<?= $host ?>/views/SesionDoctor/CitasMedicas/verCita.php">Ver Citas</a>
                            </nav>
                        </div>
                        
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePacientesDoctor" aria-expanded="false" aria-controls="collapsePacientesDoctor">
                            <div class="sb-nav-link-icon"><i class="fas fa-user-injured"></i></div>
                            Pacientes
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapsePacientesDoctor" aria-labelledby="headingPacientes" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="<?= $host ?>/views/SesionDoctor/Pacientes/listaPaciente.php">Listado de Pacientes</a>
                                <a class="nav-link" href="<?= $host ?>/views/SesionDoctor/Pacientes/historiaClinica.php">Historias Clínicas</a>
                            </nav>
                        </div>
                        
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseConsultasDoctor" aria-expanded="false" aria-controls="collapseConsultasDoctor">
                            <div class="sb-nav-link-icon"><i class="fas fa-stethoscope"></i></div>
                            Consultas
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseConsultasDoctor" aria-labelledby="headingConsultas" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="<?= $host ?>/views/SesionDoctor/Consultas/realizarConsulta.php">Realizar Consulta</a>
                                <a class="nav-link" href="<?= $host ?>/views/SesionDoctor/Consultas/historialConsulta.php">Historial de Consultas</a>
                            </nav>
                        </div>
                        
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseServiciosDoctor" aria-expanded="false" aria-controls="collapseServiciosDoctor">
                            <div class="sb-nav-link-icon"><i class="fas fa-flask"></i></div>
                            Servicios Médicos
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseServiciosDoctor" aria-labelledby="headingServicios" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="<?= $host ?>/views/Servicios/catalogoservicios.php">Catálogo de Servicios</a>
                                <a class="nav-link" href="<?= $host ?>/views/servicios/resultados.php">Resultados</a>
                            </nav>
                        </div>
                        
                        <div class="sb-sidenav-menu-heading">Mi Perfil</div>
                        <a class="nav-link" href="<?= $host ?>/views/doctor/mi-perfil.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-user-md"></i></div>
                            Información Personal
                        </a>
                        <a class="nav-link" href="<?= $host ?>/views/doctor/horarios.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-clock"></i></div>
                            Horarios de Atención
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Conectado como:</div>
                    <?= $_SESSION['usuario']['nombres'] . ' ' . $_SESSION['usuario']['apellidos'] ?>
                    <div class="small">
                        Doctor - <?= $_SESSION['usuario']['especialidad'] ?>
                    </div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>