<?php /*RUTA: sistemaclinica/views/Citas/GestionarCita/gestionarCita.php*/ ?>
<?php
require_once '../../include/header.administrador.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Listado de Citas Médicas</title>

    <!-- CSS de Bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            background-color: #f4f6f9;
        }

        .card {
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            padding: 12px 20px;
            background-color: #fff;
            border-bottom: 1px solid #eee;
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .form-control,
        .form-select {
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding: 8px 12px;
        }

        /* Formato adaptado para los filtros */
        .filtros-container {
            display: flex;
            flex-wrap: nowrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .filtro-item {
            flex: 1;
            min-width: 0;
        }

        /* Estilo para los badges de estado */
        .badge-estado {
            padding: 6px 10px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-programada {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-confirmada {
            background-color: #0d6efd;
            color: white;
        }

        .badge-realizada {
            background-color: #198754;
            color: white;
        }

        .badge-cancelada {
            background-color: #dc3545;
            color: white;
        }

        .spinner-border {
            width: 2rem;
            height: 2rem;
        }

        /* Ajustes a DataTables */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 0.5rem;
        }

        /* CORRECCIÓN: Eliminar altura fija y barra de desplazamiento */
        #tabla-citas {
            width: 100% !important;
        }

        /* Barra de herramientas */
        .toolbar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .registros-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .export-buttons {
            display: flex;
            gap: 5px;
        }

        .export-buttons .btn {
            padding: 4px 10px;
        }

        /* Buscador estilo */
        .search-container {
            display: flex;
            align-items: center;
        }

        .search-container label {
            margin-right: 10px;
            margin-bottom: 0;
        }

        /* Estilos para las acciones */
        .btn-action {
            width: 30px;
            height: 30px;
            padding: 4px 0;
            margin: 0 2px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Responsive para móviles */
        @media (max-width: 767px) {
            .filtros-container {
                flex-wrap: wrap;
            }

            .filtro-item {
                flex: 100%;
            }

            .toolbar-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .search-container {
                width: 100%;
            }
        }

        /* Tabla con estilo limpio */
        .table thead th {
            background-color: #f8f9fa;
            padding: 10px;
        }

        /* Mensaje de carga con estilo más discreto */
        .loading-message {
            padding: 15px;
            text-align: center;
            font-size: 0.9rem;
        }

        .loading-message .spinner-border {
            margin-bottom: 8px;
        }

        /* Estilos para filtros aplicados */
        .form-control.border-primary,
        .form-select.border-primary {
            border-width: 2px;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.15);
        }

        /* Estilo para botones de acción */
        #btn-buscar-filtros,
        #btn-limpiar-filtros {
            transition: all 0.3s;
        }

        #btn-buscar-filtros:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        #btn-limpiar-filtros:hover {
            background-color: #5c636a;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Listado de Citas Médicas</h2>
                        <a href="../ProgramarCita/programarCita.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Registrar Nueva Cita
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Filtros en una sola fila (similar a la imagen 2) -->
                        <div class="filtros-container">
                            <div class="filtro-item">
                                <label for="nombre-apellido-filter" class="form-label">Nombre o Apellido</label>
                                <input type="text" class="form-control" id="nombre-apellido-filter" placeholder="Buscar por nombre o apellido">
                            </div>
                            <div class="filtro-item">
                                <label for="documento-filter" class="form-label">Documento</label>
                                <input type="text" class="form-control" id="documento-filter" placeholder="Buscar por documento">
                            </div>
                            <div class="filtro-item">
                                <label for="especialidad-filter" class="form-label">Especialidad</label>
                                <select class="form-select" id="especialidad-filter">
                                    <option value="">Todas las especialidades</option>
                                </select>
                            </div>
                            <div class="filtro-item">
                                <label for="doctor-filter" class="form-label">Doctor</label>
                                <select class="form-select" id="doctor-filter">
                                    <option value="">Todos los doctores</option>
                                </select>
                            </div>
                            <div class="filtro-item">
                                <label for="estado-filter" class="form-label">Estado</label>
                                <select class="form-select" id="estado-filter">
                                    <option value="">Todos los estados</option>
                                    <option value="PROGRAMADA">Programada</option>
                                    <option value="CONFIRMADA">Confirmada</option>
                                    <option value="REALIZADA">Realizada</option>
                                    <option value="CANCELADA">Cancelada</option>
                                    <option value="NO ASISTIO">No Asistió</option>
                                </select>
                            </div>
                            <div class="filtro-item">
                                <label for="fecha-registro" class="form-label">Fecha Citas</label>
                                <input type="date" class="form-control" id="fecha-registro">
                            </div>
                        </div>

                        <!-- Botones de búsqueda y limpieza (NUEVO) -->
                        <div class="d-flex justify-content-end mt-3 mb-3">
                            <button id="btn-buscar-filtros" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Buscar
                            </button>
                            <button id="btn-limpiar-filtros" class="btn btn-secondary">
                                <i class="fas fa-eraser me-1"></i>Limpiar filtros
                            </button>
                        </div>

                        <!-- Barra de herramientas (similar a imagen 2) -->
                        <div class="toolbar-container">
                            <div class="registros-container">
                                <label>Mostrar</label>
                                <select id="registros-por-pagina" class="form-select form-select-sm" style="width: auto;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="-1">Todos</option>
                                </select>
                                <label>registros</label>

                                <div class="export-buttons ms-3">
                                    <button class="btn btn-success btn-sm" id="btn-excel">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </button>
                                    <button class="btn btn-danger btn-sm" id="btn-pdf">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </button>
                                    <button class="btn btn-info btn-sm text-white" id="btn-print">
                                        <i class="fas fa-print"></i> Imprimir
                                    </button>
                                </div>
                            </div>

                            <div class="search-container">
                                <label for="buscar-global">Buscar:</label>
                                <input type="text" id="buscar-global" class="form-control form-control-sm">
                            </div>
                        </div>

                        <!-- Contenedor de tabla con indicador de carga optimizado -->
                        <div id="contenedor-tabla" class="table-responsive">
                            <div class="loading-message text-center py-3" id="loader-inicial">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2">Cargando listado de citas...</p>
                            </div>

                            <!-- La tabla se generará dinámicamente vía JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles de cita -->
    <div class="modal fade" id="modalDetallesCita" tabindex="-1" aria-labelledby="modalDetallesCitaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" id="contenidoModalDetallesCita">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>

    <!-- Scripts - ORDEN ESPECÍFICO PARA COMPATIBILIDAD -->
    <!-- jQuery primero, luego Bootstrap, y finalmente DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables Scripts - Versiones específicas compatibles -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    <!-- Font Awesome (debe ir después de Bootstrap) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script principal para gestión de citas - OPTIMIZADO PARA CARGA RÁPIDA -->
    <script>
        // Esperar a que el DOM esté completamente cargado
        $(document).ready(function() {
            // Variables globales
            let tablaCitas = null;

            // Inicializar tooltips de Bootstrap
            let tooltipList = [];

            function initTooltips() {
                // Destruir tooltips anteriores para evitar duplicados
                tooltipList.forEach(tooltip => {
                    if (tooltip && tooltip._element) {
                        tooltip.dispose();
                    }
                });

                tooltipList = [];

                // Inicializar nuevos tooltips
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    tooltipList.push(new bootstrap.Tooltip(el));
                });
            }

            // Función para crear badge de estado
            function getBadgeEstado(estado) {
                if (!estado) return '<span class="badge badge-estado badge-pendiente">Pendiente</span>';

                let badgeClass = '';
                let badgeText = estado.charAt(0).toUpperCase() + estado.slice(1).toLowerCase();

                switch (estado.toUpperCase()) {
                    case 'PROGRAMADA':
                    case 'PENDIENTE':
                        badgeClass = 'badge-programada';
                        break;
                    case 'CONFIRMADA':
                        badgeClass = 'badge-confirmada';
                        break;
                    case 'REALIZADA':
                    case 'COMPLETADA':
                        badgeClass = 'badge-realizada';
                        break;
                    case 'CANCELADA':
                    case 'NO ASISTIO':
                        badgeClass = 'badge-cancelada';
                        break;
                    default:
                        badgeClass = 'badge-pendiente';
                }

                return `<span class="badge badge-estado ${badgeClass}">${badgeText}</span>`;
            }

            // NUEVA FUNCIÓN: Convertir formato de hora de 24h a AM/PM
            function formatearHoraAmPm(hora) {
                // Si no hay hora, devolver texto vacío
                if (!hora) return '';

                try {
                    // Extraer las partes de la hora (puede venir en formatos como "14:00" o "14:00:00")
                    const partes = hora.split(':');
                    if (partes.length < 2) return hora; // Si no tiene el formato esperado, devolver la original

                    // Obtener hora y minutos
                    let horas = parseInt(partes[0], 10);
                    const minutos = partes[1].padStart(2, '0');

                    // Determinar si es AM o PM
                    const periodo = horas >= 12 ? 'PM' : 'AM';

                    // Convertir a formato 12 horas
                    if (horas > 12) {
                        horas -= 12;
                    } else if (horas === 0) {
                        horas = 12;
                    }

                    // Devolver el formato AM/PM completo
                    return `${horas.toString().padStart(2, '0')}:${minutos} ${periodo}`;
                } catch (error) {
                    console.error("Error al formatear hora:", error, "Hora original:", hora);
                    return hora; // En caso de error, devolver la hora original
                }
            }

            // ================ FUNCIONES OPTIMIZADAS PARA CARGA RÁPIDA ================ //

            // Generación eficiente de la tabla
            function crearEstructuraTabla() {
                const contenedorTabla = document.getElementById('contenedor-tabla');

                // Limpiar contenedor
                contenedorTabla.innerHTML = `
            <table id="tabla-citas" class="table table-striped table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Paciente</th>
                        <th>Tipo Documento</th>
                        <th>N° Documento</th>
                        <th>Doctor</th>
                        <th>Especialidad</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado</th>
                        <th>Pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        `;

                return document.getElementById('tabla-citas');
            }

            // Inicialización optimizada de DataTables (más rápida)
            function inicializarTablaCitas() {
                try {
                    console.log("Inicializando tabla de citas...");

                    // Crear estructura de tabla
                    const tablaElement = crearEstructuraTabla();

                    // Vincular el buscador global
                    $('#buscar-global').on('keyup', function() {
                        if (tablaCitas) {
                            tablaCitas.search(this.value).draw();
                        }
                    });

                    // Configurar selector de registros por página
                    $('#registros-por-pagina').on('change', function() {
                        const valor = parseInt($(this).val());
                        if (tablaCitas && !isNaN(valor)) {
                            tablaCitas.page.len(valor).draw();
                        }
                    });

                    // CORRECCIÓN: Configuración para mostrar tabla normal sin scroll
                    tablaCitas = $(tablaElement).DataTable({
                        dom: 'rt<"bottom"ip>', // Ocultar controles nativos
                        pageLength: 10,
                        ordering: true,
                        info: true,
                        searching: true,
                        lengthChange: false, // Usamos nuestro propio selector
                        language: {
                            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                        },
                        order: [
                            [0, 'desc']
                        ],
                        processing: false,
                        deferRender: true,
                        // CORRECCIÓN: Quitar scroll vertical
                        scrollY: false,
                        scrollCollapse: false,
                        // Mejorar renderizado
                        rowCallback: function(row, data, index) {
                            return row;
                        },
                        autoWidth: false,
                        responsive: true,
                        paging: true
                    });

                    console.log("Tabla inicializada correctamente");

                    // Cargar datos inmediatamente
                    cargarCitasTabla();

                    return true;
                } catch (error) {
                    console.error("Error al inicializar tabla:", error);

                    // Mostrar mensaje amigable al usuario
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al inicializar la tabla',
                        text: 'Detalles: ' + error.message,
                        confirmButtonText: 'Intentar nuevamente',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });

                    return false;
                }
            }

            // Carga simultánea de datos complementarios
            function cargarDatosComplementarios() {
                // Ejecutar ambas cargas en paralelo
                return Promise.all([
                    cargarEspecialidades(),
                    cargarDoctores()
                ]);
            }

            // Función mejorada para cargar especialidades (con Promise)
            function cargarEspecialidades() {
                console.log("Cargando especialidades...");

                return fetch('../../../controllers/especialidad.controller.php?op=listar')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Error HTTP: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log("Respuesta de especialidades:", data);

                        if (data && data.status && data.data && Array.isArray(data.data)) {
                            const selectEspecialidadFilter = document.getElementById('especialidad-filter');

                            // Limpiar opciones existentes excepto la primera
                            while (selectEspecialidadFilter.options.length > 1) {
                                selectEspecialidadFilter.remove(1);
                            }

                            // Añadir nuevas opciones
                            data.data.forEach(especialidad => {
                                const option = document.createElement('option');
                                option.value = especialidad.idespecialidad;
                                option.textContent = especialidad.especialidad || especialidad.nombre || "Especialidad " + especialidad.idespecialidad;
                                selectEspecialidadFilter.appendChild(option);
                            });

                            console.log(`Especialidades cargadas: ${data.data.length}`);
                        } else {
                            console.warn("Formato incorrecto en la respuesta de especialidades");
                        }
                        return data;
                    })
                    .catch(error => {
                        console.error("Error al cargar especialidades:", error);
                        return null;
                    });
            }

            // Función mejorada para cargar doctores (con Promise)
            function cargarDoctores() {
                console.log("Cargando doctores...");

                return fetch('../../../controllers/doctor.controller.php?op=listar')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Error HTTP: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log("Respuesta de doctores:", data);

                        if (data && data.status && data.data && Array.isArray(data.data)) {
                            const selectDoctorFilter = document.getElementById('doctor-filter');

                            // Limpiar opciones existentes excepto la primera
                            while (selectDoctorFilter.options.length > 1) {
                                selectDoctorFilter.remove(1);
                            }

                            // Añadir nuevas opciones
                            data.data.forEach(doctor => {
                                const option = document.createElement('option');
                                option.value = doctor.iddoctor || doctor.idcolaborador;

                                // Manejar diferentes formatos de datos
                                const nombre = doctor.nombre || doctor.nombres || "";
                                const apellido = doctor.apellido || doctor.apellidos || "";

                                option.textContent = apellido ? `${apellido}, ${nombre}` : nombre || "Doctor " + option.value;
                                selectDoctorFilter.appendChild(option);
                            });

                            console.log(`Doctores cargados: ${data.data.length}`);
                        } else {
                            console.warn("Formato incorrecto en la respuesta de doctores");
                        }
                        return data;
                    })
                    .catch(error => {
                        console.error("Error al cargar doctores:", error);
                        return null;
                    });
            }

            // OPTIMIZADO Y CORREGIDO: Función de carga de citas
            function cargarCitasTabla() {
                console.log("Cargando citas...");

                // Ocultar loader inicial después de un breve tiempo
                setTimeout(() => {
                    const loaderInicial = document.getElementById('loader-inicial');
                    if (loaderInicial) {
                        loaderInicial.style.display = 'none';
                    }
                }, 400);

                // Mostrar mensaje de carga discreto en la tabla
                tablaCitas.clear().draw();

                // Obtener valores de los filtros
                const especialidadId = $('#especialidad-filter').val() || '';
                const doctorId = $('#doctor-filter').val() || '';
                const estado = $('#estado-filter').val() || '';
                const fechaRegistro = $('#fecha-registro').val() || '';
                // CORRECCIÓN: Capturar correctamente los valores de filtro de nombre/apellido y documento
                const nombreApellido = $('#nombre-apellido-filter').val() || '';
                const documento = $('#documento-filter').val() || '';

                // Resaltar visualmente filtros aplicados (para mejor UX)
                resaltarFiltrosAplicados(nombreApellido, documento, especialidadId, doctorId, estado, fechaRegistro);

                // Construir URL con parámetros - CORREGIDO para asegurar que los parámetros se envían correctamente
                let params = '?op=listar';
                if (especialidadId) params += `&especialidad=${especialidadId}`;
                if (doctorId) params += `&doctor=${doctorId}`;
                if (estado) params += `&estado=${estado}`;
                // CORRECCIÓN: Añadir parámetros para nombre/apellido y documento
                if (nombreApellido) params += `&paciente=${encodeURIComponent(nombreApellido)}`;
                if (documento) params += `&nrodoc=${encodeURIComponent(documento)}`;

                // Si se especifica una fecha de registro, usarla como filtro
                if (fechaRegistro) {
                    params += `&fecha=${fechaRegistro}`;
                    console.log("Aplicando filtro de fecha: " + fechaRegistro);
                } else {
                    // Si no hay fecha específica, usar un rango amplio
                    params += `&fecha_inicio=2020-01-01&fecha_fin=2030-12-31`;
                }

                const url = `../../../controllers/cita.controller.php${params}`;
                console.log("URL de solicitud:", url);

                // Mensaje de carga simple en la tabla
                $('#tabla-citas tbody').html(`
            <tr>
                <td colspan="11" class="text-center py-3">
                    <div class="spinner-border text-primary spinner-border-sm" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <span class="ms-2">Cargando datos...</span>
                </td>
            </tr>
        `);

                // OPTIMIZADO: Usar un timeout para limitar el tiempo de espera
                const fetchTimeout = new Promise((resolve, reject) => {
                    const timeoutId = setTimeout(() => {
                        reject(new Error("Tiempo de espera agotado"));
                    }, 5000); // 5 segundos máximo

                    fetch(url)
                        .then(response => {
                            clearTimeout(timeoutId);
                            if (!response.ok) {
                                throw new Error(`Error HTTP: ${response.status}`);
                            }
                            return response.text();
                        })
                        .then(resolve)
                        .catch(reject);
                });

                // Realizar la solicitud con timeout
                fetchTimeout
                    .then(rawText => {
                        try {
                            // Intentar parsear la respuesta como JSON
                            const data = JSON.parse(rawText);
                            console.log("Datos recibidos:", data);

                            // Limpiar tabla
                            tablaCitas.clear();

                            // Verificar si hay datos válidos
                            if (data && data.status === true && Array.isArray(data.data)) {
                                if (data.data.length > 0) {
                                    console.log(`Citas encontradas: ${data.data.length}`);

                                    // OPTIMIZADO: Preparar todas las filas antes de añadir
                                    const filas = [];

                                    // OPTIMIZADO: Usar un bucle tradicional para mejor rendimiento
                                    for (let i = 0; i < data.data.length; i++) {
                                        try {
                                            const cita = data.data[i];

                                            // CORRECCIÓN: Formatear fecha correctamente
                                            let fechaFormateada = '';
                                            if (cita.fecha) {
                                                try {
                                                    // Dividir la fecha en sus componentes (asumiendo formato YYYY-MM-DD del servidor)
                                                    const partesFecha = cita.fecha.split('-');
                                                    if (partesFecha.length === 3) {
                                                        // Extraer año, mes y día
                                                        const año = partesFecha[0];
                                                        const mes = partesFecha[1].padStart(2, '0'); // Asegurar 2 dígitos con cero inicial
                                                        const dia = partesFecha[2].padStart(2, '0'); // Asegurar 2 dígitos con cero inicial

                                                        // Crear el formato deseado: DD/MM/YYYY
                                                        fechaFormateada = `${dia}/${mes}/${año}`;
                                                    } else {
                                                        // Si el formato no es el esperado, usar el valor original
                                                        fechaFormateada = cita.fecha;
                                                    }
                                                } catch (e) {
                                                    console.error("Error al formatear fecha:", e, "Fecha original:", cita.fecha);
                                                    fechaFormateada = cita.fecha;
                                                }
                                            }

                                            // MODIFICACIÓN: Formatear hora en formato AM/PM
                                            const horaOriginal = cita.hora || cita.horaprogramada || '';
                                            const horaFormateada = formatearHoraAmPm(horaOriginal);

                                            // Crear etiqueta de estado
                                            const estadoBadge = getBadgeEstado(cita.estado);

                                            // Crear botones de acción - MODIFICADO: Ahora usa enlace para Ver Comprobante
                                            const botonesAccion = `
                                        <div class="btn-group" role="group">
                                            <a href="verComprobante.php?id=${cita.idcita || cita.id || ''}" 
                                               class="btn btn-info btn-sm btn-action" 
                                               data-bs-toggle="tooltip" title="Ver comprobante">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                            <button class="btn btn-danger btn-sm btn-action btn-cancelar" 
                                                data-id="${cita.idcita || cita.id || ''}" 
                                                ${(cita.estado === 'CANCELADA' || cita.estado === 'REALIZADA') ? 'disabled' : ''}
                                                data-bs-toggle="tooltip" title="Cancelar cita">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    `;

                                            // Añadir fila al array
                                            filas.push([
                                                cita.idcita || cita.id || '',
                                                // Paciente
                                                (cita.paciente_nombre && cita.paciente_apellido) ?
                                                `${cita.paciente_apellido} ${cita.paciente_nombre}` :
                                                (cita.nombre_paciente || 'No disponible'),
                                                // Tipo Documento
                                                (cita.documento_tipo) ? cita.documento_tipo :
                                                (cita.tipodoc ? cita.tipodoc : 'No disponible'),
                                                // N° Documento
                                                (cita.documento_numero) ? cita.documento_numero :
                                                (cita.nrodoc ? cita.nrodoc : 'No disponible'),
                                                // Doctor
                                                (cita.doctor_nombre && cita.doctor_apellido) ?
                                                `${cita.doctor_apellido} ${cita.doctor_nombre}` :
                                                (cita.nombre_doctor || 'No disponible'),
                                                // Especialidad
                                                cita.especialidad || '',
                                                // Fecha
                                                fechaFormateada,
                                                // Hora - MODIFICADO: Ahora usa la hora formateada en AM/PM
                                                horaFormateada,
                                                // Estado
                                                estadoBadge,
                                                // Pago
                                                `S/. ${parseFloat(cita.monto_pagado || cita.precio || 0).toFixed(2)}`,
                                                // Acciones
                                                botonesAccion
                                            ]);
                                        } catch (rowError) {
                                            console.error(`Error al procesar fila:`, rowError);
                                        }
                                    }

                                    // OPTIMIZADO: Añadir todas las filas de una vez
                                    tablaCitas.rows.add(filas);
                                    tablaCitas.draw();

                                    // Inicializar tooltips
                                    initTooltips();

                                    console.log("Tabla actualizada con éxito");
                                } else {
                                    // No hay resultados
                                    tablaCitas.clear().draw();
                                    $('#tabla-citas tbody').html(`
                                <tr>
                                    <td colspan="11" class="text-center">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle me-2"></i>
                                            No se encontraron citas que coincidan con los criterios de búsqueda
                                        </div>
                                    </td>
                                </tr>
                            `);
                                }
                            } else {
                                // Formato de respuesta incorrecto
                                console.warn("Formato incorrecto en la respuesta:", data);

                                tablaCitas.clear().draw();
                                $('#tabla-citas tbody').html(`
                            <tr>
                                <td colspan="11" class="text-center">
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        La respuesta del servidor no tiene el formato esperado
                                    </div>
                                </td>
                            </tr>
                        `);
                            }
                        } catch (e) {
                            // Error al parsear JSON
                            console.error("Error al parsear JSON:", e);
                            console.log("Texto recibido:", rawText.substring(0, 500) + "...");

                            tablaCitas.clear().draw();
                            $('#tabla-citas tbody').html(`
                        <tr>
                            <td colspan="11" class="text-center">
                                <div class="alert alert-danger mb-0">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Error al procesar la respuesta del servidor
                                </div>
                            </td>
                        </tr>
                    `);
                        }
                    })
                    .catch(error => {
                        console.error("Error al cargar citas:", error);

                        tablaCitas.clear().draw();
                        $('#tabla-citas tbody').html(`
                    <tr>
                        <td colspan="11" class="text-center">
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error al cargar las citas: ${error.message}
                                <div class="mt-2">
                                    <button class="btn btn-primary btn-sm" onclick="location.reload()">
                                        <i class="fas fa-sync-alt me-1"></i> Recargar página
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                `);
                    });
            }

            // ================ FUNCIONES DE ACCIÓN ================ //

            // Función para cancelar cita
            function cancelarCita(idCita) {
                console.log("Solicitando cancelación de cita ID:", idCita);

                Swal.fire({
                    title: '¿Está seguro?',
                    text: "La cita será cancelada y se procederá con la devolución.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, cancelar cita',
                    cancelButtonText: 'No, mantener cita'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // CORREGIDO: Redirigir directamente a la página de devolución con el ID correcto
                        window.location.href = "../../GestionarDevolucion/ProcesoDevolucion/procesoDevolucion.php?idcita=" + idCita;
                    }
                });
            }

            // ================ FUNCIONES PARA LOS FILTROS (NUEVAS) ================ //

            // NUEVA FUNCIÓN: Verificar si hay filtros aplicados
            function hayFiltrosAplicados() {
                return (
                    $('#nombre-apellido-filter').val() ||
                    $('#documento-filter').val() ||
                    $('#especialidad-filter').val() ||
                    $('#doctor-filter').val() ||
                    $('#estado-filter').val() ||
                    $('#fecha-registro').val()
                );
            }

            // NUEVA FUNCIÓN: Resaltar visualmente los filtros aplicados
            function resaltarFiltrosAplicados(nombre, documento, especialidad, doctor, estado, fecha) {
                // Resetear todos los estilos primero
                $('.form-control, .form-select').removeClass('border-primary');

                // Añadir clase de resaltado a los filtros que tienen valor
                if (nombre) $('#nombre-apellido-filter').addClass('border-primary');
                if (documento) $('#documento-filter').addClass('border-primary');
                if (especialidad) $('#especialidad-filter').addClass('border-primary');
                if (doctor) $('#doctor-filter').addClass('border-primary');
                if (estado) $('#estado-filter').addClass('border-primary');
                if (fecha) $('#fecha-registro').addClass('border-primary');
            }

            // NUEVA FUNCIÓN: Limpiar filtros
            function limpiarFiltros() {
                console.log("Limpiando todos los filtros...");

                // Limpiar campos de texto
                $('#nombre-apellido-filter').val('');
                $('#documento-filter').val('');

                // Resetear selects a primera opción
                $('#especialidad-filter').prop('selectedIndex', 0);
                $('#doctor-filter').prop('selectedIndex', 0);
                $('#estado-filter').prop('selectedIndex', 0);

                // Limpiar fecha
                $('#fecha-registro').val('');

                // Limpiar búsqueda global
                $('#buscar-global').val('');
                if (tablaCitas) {
                    tablaCitas.search('').draw();
                }

                // Cargar todos los datos sin alerta
                cargarCitasTabla();
            }

            // ================ EVENTOS - MODIFICADOS ================ //

            // Función para configurar eventos - MODIFICADA
            function configurarEventos() {
                console.log("Configurando eventos...");

                // MODIFICADO: Quitar eventos automáticos para filtros y añadir eventos para botones

                // Botón de buscar con filtros
                $('#btn-buscar-filtros').on('click', function() {
                    console.log("Aplicando filtros de búsqueda...");
                    cargarCitasTabla();
                });

                // Botón de limpiar filtros
                $('#btn-limpiar-filtros').on('click', function() {
                    console.log("Limpiando filtros...");
                    limpiarFiltros();
                });

                // Evento para Enter en campos de texto
                const camposTexto = ['nombre-apellido-filter', 'documento-filter'];
                camposTexto.forEach(id => {
                    const elemento = document.getElementById(id);
                    if (elemento) {
                        elemento.addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                $('#btn-buscar-filtros').click();
                            }
                        });
                    }
                });

                // También permitir Enter en el campo de fecha
                $('#fecha-registro').on('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        $('#btn-buscar-filtros').click();
                    }
                });

                // Solo mantener el evento para cancelar cita
                $(document).on('click', '.btn-cancelar', function() {
                    // Solo si no está deshabilitado
                    if (!$(this).prop('disabled')) {
                        const idCita = $(this).data('id');
                        if (idCita) {
                            cancelarCita(idCita);
                        } else {
                            console.error("ID de cita no encontrado");
                        }
                    }
                });

                // Eventos para los botones de exportación
                $('#btn-excel').on('click', function() {
                    console.log("Exportando a Excel...");
                    if (tablaCitas) {
                        // Crear y disparar un enlace de descarga
                        const link = document.createElement('a');
                        link.href = '#';
                        link.download = 'citas_medicas.xlsx';
                        link.click();

                        // Alternativa: Implementar exportación real si DataTable buttons está disponible
                        if ($.fn.DataTable.Buttons) {
                            new $.fn.DataTable.Buttons(tablaCitas, {
                                buttons: [{
                                    extend: 'excel',
                                    text: 'Excel',
                                    exportOptions: {
                                        columns: ':not(:last-child)' // Exporta todas excepto la última columna (Acciones)
                                    }
                                }]
                            }).container().appendTo('#wrapper');
                            $('div.dt-buttons .buttons-excel').click();
                            $('#wrapper').remove();
                        } else {
                            alert("Exportación a Excel no disponible. Por favor recargue la página e intente de nuevo.");
                        }
                    }
                });

                $('#btn-pdf').on('click', function() {
                    console.log("Exportando a PDF...");
                    if (tablaCitas) {
                        // Implementación básica
                        const link = document.createElement('a');
                        link.href = '#';
                        link.download = 'citas_medicas.pdf';
                        link.click();
                    }
                });

                $('#btn-print').on('click', function() {
                    console.log("Imprimiendo...");
                    if (tablaCitas) {
                        window.print();
                    }
                });

                console.log("Eventos configurados correctamente");
            }

            // ================ INICIALIZACIÓN PRINCIPAL OPTIMIZADA ================ //

            // Función de inicialización global
            function inicializar() {
                console.log("Iniciando aplicación...");

                try {
                    // 1. Configurar eventos (no depende de datos)
                    configurarEventos();

                    // 2. Cargar datos complementarios y tabla en paralelo
                    cargarDatosComplementarios().then(() => {
                        // 3. Inicializar la tabla con un ligero retraso para permitir renderizado UI
                        setTimeout(() => {
                            inicializarTablaCitas();
                            console.log("Aplicación iniciada correctamente");
                        }, 50);
                    });

                } catch (error) {
                    console.error("Error grave durante la inicialización:", error);

                    // Mostrar mensaje amigable al usuario
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de inicialización',
                        text: 'Ocurrió un error al iniciar la aplicación: ' + error.message,
                        footer: 'Intente recargar la página'
                    });
                }
            }

            // Iniciar la aplicación cuando el documento esté listo
            inicializar();
        });
    </script>
</body>

</html>