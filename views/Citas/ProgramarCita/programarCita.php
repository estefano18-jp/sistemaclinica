<?php /*RUTA: sistemaclinica/views/Citas/ProgramarCita/programarCita.php*/ ?>
<?php
require_once '../../include/header.administrador.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calendario y Reservación de Citas</title>

    <!-- CSS de AdminLTE y Bootstrap -->
    <link rel="stylesheet" href="../../../css/calendario/adminlte.min.css">
    <link rel="stylesheet" href="../../../css/calendario/main.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">

    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css' rel='stylesheet' />

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            overflow-x: hidden;
        }

        .container-fluid {
            padding: 20px;
        }

        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border-radius: 8px;
        }

        #calendar {
            padding: 10px;
        }

        .form-control,
        .form-select {
            margin-bottom: 15px;
        }

        .search-container {
            display: flex;
            margin-bottom: 15px;
            align-items: center;
        }

        .search-btn {
            height: 38px;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        /* Modificación para que el campo de documento tenga el borde derecho redondeado removido */
        #numero-documento {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .top-filters {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .top-filters>div {
            flex: 1;
            min-width: 180px;
        }

        .top-filters .form-select,
        .top-filters .form-control {
            margin-bottom: 0;
        }

        .card.reservation-card {
            /* Corregido: se elimina height: 100% para evitar que se estire */
            height: auto;
            margin-top: 49px;
        }

        .card.calendar-card {
            /* Corregido: se elimina height: 100% para evitar que se estire */
            height: auto;
            display: flex;
            flex-direction: column;
        }

        .card.calendar-card .card-body {
            flex: 1;
        }

        .calendar-container {
            display: flex;
            flex-direction: column;
            /* Corregido: se cambia la altura calculada a automática */
            height: auto;
        }

        .example-days {
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            font-size: 14px;
            margin: 15px 0;
            border-left: 4px solid #0d6efd;
        }

        /* Para ocultar sidebar y elementos no deseados */
        .sidebar-mini .wrapper .sidebar,
        .main-header,
        .main-sidebar {
            display: none !important;
        }

        /* Estilos para que coincida con un diseño profesional */
        .card-header {
            padding: 12px 15px;
            border-radius: 7px 7px 0 0;
        }

        .card-header.bg-primary {
            background-color: #0d6efd !important;
        }

        .btn-primary {
            background-color: #0d6efd;
        }

        .btn-success {
            background-color: #198754;
        }

        .fc .fc-toolbar-title {
            font-size: 1.5em;
            text-align: center;
            width: 100%;
            margin-bottom: 10px;
        }

        .fc .fc-toolbar.fc-header-toolbar {
            flex-wrap: wrap;
            margin-bottom: 1.5em;
        }

        .fc-header-toolbar .fc-toolbar-chunk:nth-child(2) {
            width: 100%;
            text-align: center;
            order: -1;
            margin-bottom: 10px;
        }

        .fc-daygrid-day-number {
            font-size: 1rem;
        }

        .reservation-title {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .patient-info {
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid #198754;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 48px;
            /* Reducido de 20px a 10px */
            justify-content: center;
        }

        .action-buttons .btn {
            flex: 1;
            max-width: 200px;
            padding: 10px;
        }

        .date-time-selector {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            /* Tres columnas: fecha, botón, horario */
            gap: 10px;
            margin-bottom: 15px;
            align-items: end;
            /* Alinea los elementos en la parte inferior */
        }

        .date-time-selector .btn {
            margin-top: 1.5rem;
            /* Alinea el botón con los inputs */
        }

        .date-time-selector .form-control {
            margin-bottom: 0;
        }

        .modal-body .btn-group-time {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 8px;
        }

        h5.section-title {
            margin-top: 10px;
            margin-bottom: 15px;
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
            font-size: 1rem;
        }

        /* Asegura alineación y altura perfecta */
        .form-container {
            display: flex;
            flex-direction: column;
            /* Corregido: se cambia height: 100% a height: auto */
            height: auto;
        }

        .row {
            min-height: initial;
        }

        @media (max-width: 992px) {
            .card.reservation-card {
                margin-top: 20px !important;
            }
        }

        /* Estilo para la estructura de búsqueda de documento */
        .documento-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            /* Divide el espacio en dos columnas exactamente iguales */
            margin-bottom: 5px;
            gap: 10px;
            /* Espacio entre el tipo de documento y el número */
        }

        .documento-container>div {
            width: 100%;
        }

        .documento-container .form-select {
            margin-bottom: 0;
            width: 100%;
        }

        .documento-container .input-group {
            width: 100%;
        }

        /* Estilos para indicar carga */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            display: none;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        /* Estilos adicionales para las etiquetas de los campos */
        .form-label {
            font-weight: 500;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
            color: #495057;
        }

        /* Estilos optimizados para modales */
        .modal-open {
            overflow: hidden;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            display: none;
            width: 100%;
            height: 100%;
            overflow: hidden;
            outline: 0;
        }

        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
            transform: translate(0, -50px);
        }

        .modal.show .modal-dialog {
            transform: none;
        }

        .modal.show {
            display: block;
            opacity: 1;
        }

        /* Backdrop menos oscuro para todos los modales */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            width: 100vw;
            height: 100vh;
            background-color: #000;
        }

        .modal-backdrop.fade {
            opacity: 0;
        }

        .modal-backdrop.show {
            opacity: 0.3;
            /* Cambiado de 0.5 a 0.3 para hacer el fondo menos oscuro */
        }

        /* Estilos mejorados para el centrado de modales */
        .modal-dialog {
            position: relative;
            width: auto;
            margin: 1.75rem auto;
            /* Ajustado para mejor centrado vertical */
            pointer-events: none;
            display: flex;
            align-items: center;
            min-height: calc(100% - 3.5rem);
        }

        .modal-content {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            pointer-events: auto;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 0.3rem;
            outline: 0;
        }

        /* Posicionamiento específico para el modal de horarios - en la parte superior */
        #modalHorarios .modal-dialog.modal-dialog-centered {
            align-items: flex-start;
            margin-top: 5vh;
            /* Ajusta esto según necesites para bajar más o menos desde la parte superior */
        }

        /* Clase especial para modales que necesiten estar en la parte superior */
        .modal-dialog.modal-dialog-top-center {
            display: flex;
            align-items: flex-start;
            margin-top: 5vh;
            min-height: calc(100% - 3.5rem);
        }

        /* Ajustes específicos para diferentes tamaños de pantalla */
        @media (min-width: 576px) {
            .modal-dialog {
                max-width: 500px;
                margin: 1.75rem auto;
            }

            .modal-dialog-centered {
                min-height: calc(100% - 3.5rem);
            }

            .modal-lg,
            .modal-xl {
                max-width: 800px;
            }
        }

        @media (min-width: 992px) {
            .modal-xl {
                max-width: 1140px;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Columna izquierda: Filtros y Calendario -->
            <div class="col-lg-8">
                <!-- Filtros superiores encima del calendario -->
                <h5 class="section-title mb-3">Filtros de búsqueda</h5>
                <div class="top-filters mb-4">
                    <div>
                        <label for="especialidad-top" class="form-label">Especialidad</label>
                        <select class="form-select" id="especialidad-top">
                            <option selected disabled>Seleccione Especialidad</option>
                        </select>
                    </div>

                    <div>
                        <label for="doctor-top" class="form-label">Doctor</label>
                        <select class="form-select" id="doctor-top">
                            <option selected disabled>Seleccione Doctor</option>
                        </select>
                    </div>

                    <div>
                        <label for="fecha-top" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="fecha-top">
                    </div>
                </div>

                <div class="calendar-container">
                    <!-- Calendario -->
                    <div class="card calendar-card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title">Calendario</h3>
                        </div>
                        <div class="card-body p-0">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de reserva (lado derecho) -->
            <div class="col-lg-4">
                <div class="card reservation-card" style="margin-top: 49px;">
                    <!-- CAMBIO AQUÍ: Se modificó el encabezado para incluir el botón "Ver Citas" en amarillo y más a la derecha -->
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Reservación de cita</h3>
                        <a href="../GestionarCita/gestionarCita.php" class="btn btn-warning rounded px-3 fw-semibold ms-auto" style="color: #000000 !important;">Ver Citas</a>
                    </div>
                    <div class="card-body">
                        <div id="formContainer" class="form-container">
                            <!-- Selección de especialidad y doctor -->
                            <h5 class="section-title">Información de la cita</h5>

                            <label for="especialidad" class="form-label">Especialidad</label>
                            <select class="form-select" id="especialidad">
                                <option selected disabled>Seleccione Especialidad</option>
                            </select>

                            <label for="doctor" class="form-label">Doctor</label>
                            <select class="form-select" id="doctor">
                                <option selected disabled>Seleccione Doctor</option>
                            </select>

                            <!-- Ejemplo de días de atención -->
                            <div class="example-days">
                                <p class="mb-1"><strong>Días de atención:</strong></p>
                                <p class="mb-0 ps-3">Seleccione un doctor para ver sus días de atención</p>
                            </div>

                            <!-- Selección de fecha, botón para agregar horario y campo de horario -->
                            <div class="date-time-selector">
                                <div>
                                    <label for="fecha-reserva" class="form-label">Fecha</label>
                                    <input type="date" class="form-control" id="fecha-reserva">
                                </div>
                                <button class="btn btn-primary" id="btn-agregar-horario">Ver horarios</button>
                                <div>
                                    <label for="horario-seleccionado" class="form-label">Horario</label>
                                    <input type="text" class="form-control" id="horario-seleccionado" placeholder="Horario" readonly>
                                </div>
                            </div>

                            <hr>

                            <!-- Datos del paciente -->
                            <h5 class="section-title">Datos del paciente</h5>

                            <!-- Estructura modificada: ahora el botón de búsqueda está pegado al campo de documento con espacio entre tipo y número -->
                            <div class="documento-container">
                                <div>
                                    <label for="tipo-documento" class="form-label">Tipo de Documento</label>
                                    <select class="form-select" id="tipo-documento">
                                        <option disabled>Seleccione...</option>
                                        <option value="DNI" selected>DNI</option>
                                        <option value="PASAPORTE">Pasaporte</option>
                                        <option value="CARNET DE EXTRANJERIA">Carnet de Extranjería</option>
                                        <option value="OTRO">Otro</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="numero-documento" class="form-label">Número de Documento</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="numero-documento" placeholder="N° documento">
                                        <button class="btn btn-primary search-btn" id="btn-buscar">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Añadir contador para la validación de documento -->
                            <div class="documento-contador text-muted mb-3" style="font-size: 0.8rem; text-align: right;">
                                <span id="contador-documento">0/8</span>
                            </div>

                            <!-- Información del paciente con etiquetas ya existentes -->
                            <div class="patient-info">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" readonly>
                                </div>
                                <div class="mb-0">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" readonly>
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="action-buttons">
                                <button class="btn btn-success" id="btn-proceder-pago" disabled>Proceder con el pago</button>
                                <button class="btn btn-warning d-none" id="btn-registrar-paciente">Registrar paciente</button>
                            </div>

                            <!-- Campos ocultos -->
                            <input type="hidden" id="idpaciente" value="">
                            <input type="hidden" id="hora-hidden" value="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor donde se cargará el modal desde ventaCita.php -->
    <div id="modal-container"></div>

    <!-- Modal para seleccionar horario -->
    <div class="modal fade" id="modalHorarios" tabindex="-1" role="dialog" aria-labelledby="modalHorariosLabel" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalHorariosLabel">Seleccione un horario disponible</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="btn-group-time">
                        <!-- Los horarios se cargarán dinámicamente -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-confirmar-horario">Confirmar horario</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar detalles de cita existente -->
    <div class="modal fade" id="modalDetallesCita" tabindex="-1" aria-labelledby="modalDetallesCitaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" id="contenidoModalDetallesCita">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>

    <!-- Modal para mostrar comprobante -->
    <div class="modal fade" id="modalComprobante" tabindex="-1" aria-labelledby="modalComprobanteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" id="contenidoModalComprobante">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>

    <!-- Indicador de carga -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../../../js/calendario/jquery.min.js"></script>
    <script src="../../../js/calendario/bootstrap.bundle.min.js"></script>
    <script src="../../../js/calendario/adminlte.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

    <!-- FullCalendar Scripts -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/locales-all.min.js'></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts personalizados en orden correcto -->
    <script src="../../../js/reservacioncitas/utils.js"></script>
    <script src="../../../js/reservacioncitas/especialidad.js"></script>
    <script src="../../../js/reservacioncitas/doctor.js"></script>
    <script src="../../../js/reservacioncitas/horario.js"></script>
    <script src="../../../js/reservacioncitas/paciente.js"></script>
    <script src="../../../js/reservacioncitas/cliente.js"></script>
    <script src="../../../js/reservacioncitas/empresa.js"></script>
    <script src="../../../js/reservacioncitas/venta.js"></script>
    <script src="../../../js/reservacioncitas/cita.js"></script>
    <script src="../../../js/reservacioncitas/procesarPago.js"></script>
    <script src="../../../js/reservacioncitas/main.js"></script>

    <!-- Script principal optimizado -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Inicializando calendario...');
            var calendarEl = document.getElementById('calendar');

            if (calendarEl) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    height: 'auto',
                    contentHeight: 'auto',
                    aspectRatio: 1.5, // Mejorado para mejor visualización
                    initialView: 'dayGridMonth',
                    locale: 'es',
                    fixedWeekCount: false, // AÑADIDO: Mostrar solo las semanas del mes actual
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,dayGridWeek,timeGridDay'
                    },
                    buttonText: {
                        today: 'Hoy',
                        month: 'Mes',
                        week: 'Semana',
                        day: 'Día'
                    },
                    dayHeaderFormat: {
                        weekday: 'short'
                    },
                    // NUEVO: Permitir mostrar más eventos antes de "+más"
                    dayMaxEvents: 2,

                    // NUEVO: Configuración mejorada para eventos
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    },

                    // NUEVO: Mejorar aspecto visual de los eventos
                    eventBackgroundColor: '#0d6efd',
                    eventBorderColor: '#0a58ca',
                    eventTextColor: 'white',

                    // NUEVO: Configurar visualización de eventos
                    eventDisplay: 'block', // 'auto', 'block', o 'list'

                    // NUEVO: Callback cuando se hace clic en un evento
                    eventClick: function(info) {
                        // Usar la función de mostrar detalles de cita
                        if (typeof mostrarDetallesCita === 'function') {
                            mostrarDetallesCita(info.event);
                        } else {
                            // Fallback si la función no está disponible
                            Swal.fire({
                                title: info.event.title,
                                html: `
                <p><strong>Paciente:</strong> ${info.event.extendedProps.paciente || 'N/A'}</p>
                <p><strong>Doctor:</strong> ${info.event.extendedProps.doctor || 'N/A'}</p>
                <p><strong>Especialidad:</strong> ${info.event.extendedProps.especialidad || 'N/A'}</p>
                <p><strong>Fecha:</strong> ${formatearFecha(info.event.start)}</p>
                <p><strong>Hora:</strong> ${formatearHora(info.event.start)}</p>
            `
                            });
                        }
                    },

                    // NUEVO: Callback cuando se hace clic en un día (vista mensual o semanal)
                    dateClick: function(info) {
                        if (calendar.view.type === 'dayGridMonth' || calendar.view.type === 'dayGridWeek') {
                            console.log('Click en día:', info.dateStr);

                            // Mostrar citas del día
                            if (typeof mostrarCitasPorDiaDoctor === 'function') {
                                mostrarCitasPorDiaDoctor(new Date(info.dateStr));
                            }
                        }
                    },

                    // NUEVO: Mejorar la visualización cuando no hay eventos
                    noEventsContent: function() {
                        return {
                            html: `<div style="padding: 8px; color: #666;">No hay citas programadas</div>`
                        };
                    },

                    // Se cargarán dinámicamente
                    events: []
                });

                calendar.render();
                window.calendario = calendar;
                console.log('Calendario inicializado correctamente');

                // CAMBIO PRINCIPAL: Una sola carga inicial optimizada - SIN DUPLICADOS
                setTimeout(function() {
                    if (typeof cargarCitasCalendario === 'function') {
                        cargarCitasCalendario(calendar)
                            .then(() => {
                                console.log('Citas iniciales cargadas (OPTIMIZADO)');
                                // Ya no es necesario llamar a mejorarVisualizacionCalendario por separado
                            })
                            .catch(error => console.error('Error al cargar citas iniciales:', error));
                    }
                }, 500);
            } else {
                console.error('No se encontró el elemento del calendario');
            }

            // Para cargar y mostrar el modal de pago 
            const btnProcederPago = document.getElementById('btn-proceder-pago');
            const modalContainer = document.getElementById('modal-container');


            // Función para cargar el contenido del modal desde la ruta especificada
            function cargarModalPago() {
                console.log("Iniciando carga del modal de pago...");

                // CAMBIO IMPORTANTE: NO usar el overlay global, crear uno específico para el modal
                const modalLoadingOverlay = document.createElement('div');
                modalLoadingOverlay.id = 'modal-loading-overlay';
                modalLoadingOverlay.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        background-color: rgba(255, 255, 255, 0.9);
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    `;
                modalLoadingOverlay.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <div class="mt-2">Cargando formulario de pago...</div>
        </div>
    `;
                document.body.appendChild(modalLoadingOverlay);

                // Realizar una petición AJAX para obtener el contenido del modal
                fetch('../../PagoCita/ventaCita.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('No se pudo cargar el modal de pago');
                        }
                        return response.text();
                    })
                    .then(html => {
                        // Insertar el contenido en el contenedor del modal
                        const modalContainer = document.getElementById('modal-container');
                        modalContainer.innerHTML = html;

                        console.log("Contenido del modal cargado correctamente");

                        // Ocultar y eliminar el overlay específico del modal
                        if (modalLoadingOverlay && modalLoadingOverlay.parentNode) {
                            modalLoadingOverlay.remove();
                        }

                        // Cargar datos del paciente en el modal
                        cargarDatosPacienteEnModal();

                        // Configurar eventos del modal de pago
                        if (typeof configurarEventosModalPago === 'function') {
                            console.log('Configurando eventos del modal de pago...');
                            configurarEventosModalPago();
                        } else {
                            console.error('La función configurarEventosModalPago no está definida');
                        }

                        // MODIFICACIÓN: Configurar el modal con backdrop static
                        const modalPagoCitaElement = document.getElementById('modalPagoCita');

                        // Establecer atributos para backdrop static y keyboard false
                        modalPagoCitaElement.setAttribute('data-bs-backdrop', 'static');
                        modalPagoCitaElement.setAttribute('data-bs-keyboard', 'false');

                        // Mostrar el modal con opciones específicas
                        console.log("Mostrando el modal de pago con backdrop static...");
                        const modalPagoCita = new bootstrap.Modal(modalPagoCitaElement, {
                            backdrop: 'static',
                            keyboard: false
                        });

                        modalPagoCita.show();
                    })
                    .catch(error => {
                        console.error('Error al cargar el modal:', error);

                        // Eliminar el overlay específico del modal
                        if (modalLoadingOverlay && modalLoadingOverlay.parentNode) {
                            modalLoadingOverlay.remove();
                        }

                        // Mostrar error al usuario
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo cargar el módulo de pago. Por favor, inténtelo de nuevo.',
                        });
                    });
            }
            // Función para cargar los datos del paciente en el modal de pago
            function cargarDatosPacienteEnModal() {
                console.log("Cargando datos del paciente en el modal de pago...");

                // Verificar si el modal y sus elementos existen
                const tipoPaciente = document.getElementById('tipoPaciente');
                const documentoPaciente = document.getElementById('documentoPaciente');
                const nombrePaciente = document.getElementById('nombrePaciente');
                const apellidoPaciente = document.getElementById('apellidoPaciente');
                const especialidadConsulta = document.getElementById('especialidadConsulta');
                const precioConsulta = document.getElementById('precioConsulta');
                const precioTotal = document.getElementById('precioTotal');

                if (!tipoPaciente || !documentoPaciente || !nombrePaciente || !apellidoPaciente) {
                    console.error("No se encontraron los elementos del modal de pago");
                    return;
                }

                // Obtener datos del formulario principal
                const tipoDocumento = document.getElementById('tipo-documento').value;
                const numeroDocumento = document.getElementById('numero-documento').value;
                const nombre = document.getElementById('nombre').value;
                const apellido = document.getElementById('apellido').value;

                // Mapear tipo de documento
                if (tipoDocumento === 'DNI') tipoPaciente.value = 'dni';
                else if (tipoDocumento === 'PASAPORTE') tipoPaciente.value = 'pasaporte';
                else if (tipoDocumento === 'CARNET DE EXTRANJERIA') tipoPaciente.value = 'carnet';
                else tipoPaciente.value = 'otro';

                // Asignar datos del paciente
                documentoPaciente.value = numeroDocumento;
                nombrePaciente.value = nombre;
                apellidoPaciente.value = apellido;

                // CORRECCIÓN IMPORTANTE: Obtener el precio de la especialidad seleccionada
                if (especialidadConsulta || precioConsulta || precioTotal) {
                    const especialidadSelect = document.getElementById('especialidad');
                    if (especialidadSelect && especialidadSelect.selectedIndex >= 0) {
                        const selectedOption = especialidadSelect.options[especialidadSelect.selectedIndex];
                        const especialidadText = selectedOption.text || 'Consulta Médica';

                        // Aquí está la corrección principal - Obtener el precio directamente
                        // y asegurarse de que sea un número válido
                        let precio = '0.00';

                        if (selectedOption.dataset && selectedOption.dataset.precio) {
                            precio = selectedOption.dataset.precio;
                            console.log("Precio obtenido del dataset:", precio);
                        } else {
                            // Intentar obtener el precio de otra manera si no está en el dataset
                            try {
                                // Obtener precio de la API si es necesario
                                console.log("Intentando obtener precio desde la API para idespecialidad:", selectedOption.value);
                                precio = obtenerPrecioEspecialidadDirecto(selectedOption.value) || '0.00';
                            } catch (e) {
                                console.error("Error al obtener precio alternativo:", e);
                            }
                        }

                        // Asegurar que el precio es un número válido para evitar NaN
                        const precioNumerico = parseFloat(precio) || 0;
                        console.log("Precio numérico final:", precioNumerico);

                        // Actualizar detalles del servicio con el precio correcto
                        if (especialidadConsulta) especialidadConsulta.textContent = especialidadText;
                        if (precioConsulta) precioConsulta.textContent = `S/. ${precioNumerico.toFixed(2)}`;
                        if (precioTotal) precioTotal.textContent = `S/. ${precioNumerico.toFixed(2)}`;

                        // Registrar en consola para depuración
                        console.log("Precio actualizado en modal:", precioNumerico.toFixed(2));
                    } else {
                        console.warn("No se pudo encontrar la especialidad seleccionada");
                    }
                } else {
                    console.warn("No se encontraron los elementos de precio en el modal");
                }
            }
            // Función auxiliar para obtener el precio directamente de la API si es necesario
            async function obtenerPrecioEspecialidadDirecto(idEspecialidad) {
                try {
                    if (!idEspecialidad) return '0.00';

                    const response = await fetch(`../../../controllers/especialidad.controller.php?op=obtener&id=${idEspecialidad}`);
                    const data = await response.json();

                    if (data.status && data.data && data.data.precioatencion) {
                        return data.data.precioatencion;
                    }

                    return '0.00';
                } catch (error) {
                    console.error("Error al obtener precio directo:", error);
                    return '0.00';
                }
            }
            // Agregar evento click al botón "Proceder con el pago"
            if (btnProcederPago) {
                btnProcederPago.addEventListener('click', cargarModalPago);
            }

            // Solución para los modales de horarios y otros modales nativos
            function configurarCierreModalesNativos() {
                // Configurar modales existentes
                document.querySelectorAll('.modal').forEach(configurarModal);

                // Observar cambios en el DOM para configurar nuevos modales
                const observer = new MutationObserver(mutations => {
                    mutations.forEach(mutation => {
                        if (mutation.type === 'childList') {
                            mutation.addedNodes.forEach(node => {
                                if (node.nodeType === 1) {
                                    // Si el nodo es un modal
                                    if (node.classList && node.classList.contains('modal')) {
                                        configurarModal(node);
                                    }

                                    // O si contiene modales
                                    const modales = node.querySelectorAll ? node.querySelectorAll('.modal') : [];
                                    modales.forEach(configurarModal);
                                }
                            });
                        }
                    });
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }

            // Función para configurar un modal individual
            function configurarModal(modal) {
                if (!modal || modal._configurado) return; // Evitar configurar dos veces

                // Marcar como configurado
                modal._configurado = true;

                // Asegurarse de que el modal tenga la clase modal-dialog-centered
                const modalDialog = modal.querySelector('.modal-dialog');
                if (modalDialog && !modalDialog.classList.contains('modal-dialog-centered')) {
                    modalDialog.classList.add('modal-dialog-centered');
                }

                // Eliminar aria-hidden y prevenir que se vuelva a aplicar
                if (modal.getAttribute('aria-hidden') === 'true') {
                    modal.removeAttribute('aria-hidden');
                }

                // Configurar botones de cierre
                const closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"]');
                closeButtons.forEach(button => {
                    // Eliminar manejadores existentes para evitar duplicados
                    button.removeEventListener('click', handleClose);

                    // Agregar nuevo manejador
                    button.addEventListener('click', handleClose);

                    function handleClose(e) {
                        e.preventDefault();

                        try {
                            // Intentar usar la API de Bootstrap 5
                            const modalInstance = bootstrap.Modal.getInstance(modal);
                            if (modalInstance) {
                                modalInstance.hide();
                            } else if (typeof $ !== 'undefined') {
                                // Fallback a jQuery si está disponible
                                $(modal).modal('hide');
                            } else {
                                // Fallback manual
                                cerrarModalManualmente(modal);
                            }
                        } catch (error) {
                            console.error('Error al cerrar modal:', error);
                            cerrarModalManualmente(modal);
                        }
                    }
                });

                // Eventos del ciclo de vida del modal
                modal.addEventListener('show.bs.modal', function() {
                    // Antes de mostrar, eliminar aria-hidden
                    this.removeAttribute('aria-hidden');

                    // Y de elementos internos
                    this.querySelectorAll('[aria-hidden]').forEach(el => {
                        el.removeAttribute('aria-hidden');
                    });
                });

                modal.addEventListener('shown.bs.modal', function() {
                    // Después de mostrar, volver a eliminar aria-hidden por si acaso
                    if (this.getAttribute('aria-hidden')) {
                        this.removeAttribute('aria-hidden');
                    }
                });

                modal.addEventListener('hidden.bs.modal', function() {
                    // Limpieza después de cerrar
                    cerrarModalManualmente(this);
                });
            }

            // Función auxiliar para cerrar un modal manualmente
            function cerrarModalManualmente(modal) {
                if (!modal) return;

                // Ocultar el modal
                modal.style.display = 'none';
                modal.classList.remove('show');
                modal.removeAttribute('aria-modal');
                modal.removeAttribute('role');
                modal.removeAttribute('aria-hidden');

                // Restaurar el body
                if (!document.querySelector('.modal.show')) {
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }

                // Eliminar backdrop
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }

            // Parche específico para el modal de horarios
            const btnAgregarHorario = document.getElementById('btn-agregar-horario');
            if (btnAgregarHorario) {
                btnAgregarHorario.addEventListener('click', function() {
                    // Configurar el modal de horarios antes de mostrarlo
                    setTimeout(() => {
                        const modalHorarios = document.getElementById('modalHorarios');
                        if (modalHorarios) {
                            // Asegurarse de que el modal tenga la clase modal-dialog-centered
                            const modalDialog = modalHorarios.querySelector('.modal-dialog');
                            if (modalDialog && !modalDialog.classList.contains('modal-dialog-centered')) {
                                modalDialog.classList.add('modal-dialog-centered');
                            }

                            // Eliminar aria-hidden
                            modalHorarios.removeAttribute('aria-hidden');
                        }
                    }, 10);
                });
            }

            // Iniciar configuración de modales
            configurarCierreModalesNativos();

            // Función para abortar el establecimiento de aria-hidden
            function detenerAriaHidden() {
                // 1. Sobrescribir setAttribute para elementos modal
                const originalSetAttribute = Element.prototype.setAttribute;
                Element.prototype.setAttribute = function(name, value) {
                    // Si es aria-hidden en un modal o su ancestro, no hacer nada
                    if (name === 'aria-hidden' &&
                        (this.classList?.contains('modal') || this.closest?.('.modal'))) {
                        return;
                    }
                    return originalSetAttribute.call(this, name, value);
                };

                // 2. Usar MutationObserver para detectar y eliminar aria-hidden
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'attributes' &&
                            mutation.attributeName === 'aria-hidden') {
                            const element = mutation.target;

                            // Si es un modal o contiene elementos con foco, eliminar aria-hidden
                            if (element.classList?.contains('modal') ||
                                element.querySelector?.('button:focus, [tabindex]:focus')) {
                                element.removeAttribute('aria-hidden');
                            }

                            // Verificar si contiene botones u otros elementos interactivos
                            const interactivos = element.querySelectorAll?.('button, a, input, select, textarea, [tabindex]');
                            if (interactivos?.length > 0) {
                                // Si este elemento con aria-hidden contiene elementos interactivos, quitar atributo
                                element.removeAttribute('aria-hidden');
                            }
                        }
                    });
                });

                // Observar todo el documento
                observer.observe(document.body, {
                    attributes: true,
                    attributeFilter: ['aria-hidden'],
                    subtree: true
                });
            }

            // Iniciar solución contra aria-hidden
            detenerAriaHidden();
        });
    </script>
</body>

</html>