<?php /*RUTA: sistemasclinica/views/GestionarDevolucion/HistorialDevolucion/historialDevolucion.php*/ ?>
<?php
require_once '../../include/header.administrador.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historial de Devoluciones</title>

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

        /* Estilo para los badges de método de devolución */
        .badge-metodo {
            padding: 6px 10px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-efectivo {
            background-color: #198754;
            color: white;
        }

        .badge-yape {
            background-color: #6610f2;
            color: white;
        }

        .badge-plin {
            background-color: #0d6efd;
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

        /* Eliminar altura fija y barra de desplazamiento */
        #tabla-devoluciones {
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

        /* Estilos para montos */
        .monto-devolucion {
            font-weight: bold;
            color: #28a745;
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Historial de Devoluciones</h2>
                        <a href="../../GestionarDevolucion/ProcesoDevolucion/procesoDevolucion.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Registrar Nueva Devolución
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Filtros en una sola fila -->
                        <div class="filtros-container">
                            <div class="filtro-item">
                                <label for="documento-filter" class="form-label">Buscar por N° Documento</label>
                                <input type="text" class="form-control" id="documento-filter" placeholder="Buscar por documento">
                            </div>
                            <div class="filtro-item">
                                <label for="fecha-inicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fecha-inicio">
                            </div>
                            <div class="filtro-item">
                                <label for="fecha-fin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fecha-fin">
                            </div>
                            <div class="filtro-item">
                                <label for="metodo-filter" class="form-label">Método de Devolución</label>
                                <select class="form-select" id="metodo-filter">
                                    <option value="">Todos los métodos</option>
                                    <option value="EFECTIVO">Efectivo</option>
                                    <option value="YAPE">Yape</option>
                                    <option value="PLIN">Plin</option>
                                </select>
                            </div>
                            <div class="filtro-item">
                                <label for="motivo-filter" class="form-label">Motivo</label>
                                <select class="form-select" id="motivo-filter">
                                    <option value="">Todos los motivos</option>
                                    <option value="SOLICITUD_PACIENTE">Solicitud del paciente</option>
                                    <option value="EMERGENCIA_MEDICA">Emergencia médica</option>
                                    <option value="PROBLEMA_HORARIO">Problema de horario</option>
                                    <option value="CANCELACION_DOCTOR">Cancelación por parte del doctor</option>
                                    <option value="OTRO">Otro motivo</option>
                                </select>
                            </div>
                        </div>

                        <!-- Botones de búsqueda y limpieza -->
                        <div class="d-flex justify-content-end mt-3 mb-3">
                            <button id="btn-buscar-filtros" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Buscar
                            </button>
                            <button id="btn-limpiar-filtros" class="btn btn-secondary">
                                <i class="fas fa-eraser me-1"></i>Limpiar filtros
                            </button>
                        </div>

                        <!-- Barra de herramientas -->
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
                                <p class="mt-2">Cargando historial de devoluciones...</p>
                            </div>

                            <!-- La tabla se generará dinámicamente vía JavaScript -->
                        </div>
                    </div>
                </div>
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

    <!-- Script principal para gestión de devoluciones -->
    <script>
        // Esperar a que el DOM esté completamente cargado
        $(document).ready(function() {
            // Variables globales
            let tablaDevoluciones = null;

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

            // Función para crear badge de método de devolución
            function getBadgeMetodo(metodo) {
                if (!metodo) return '<span class="badge badge-metodo bg-secondary">No especificado</span>';

                let badgeClass = '';
                let badgeText = metodo.charAt(0).toUpperCase() + metodo.slice(1).toLowerCase();

                switch (metodo.toUpperCase()) {
                    case 'EFECTIVO':
                        badgeClass = 'badge-efectivo';
                        break;
                    case 'YAPE':
                        badgeClass = 'badge-yape';
                        break;
                    case 'PLIN':
                        badgeClass = 'badge-plin';
                        break;
                    default:
                        badgeClass = 'bg-secondary';
                }

                return `<span class="badge badge-metodo ${badgeClass}">${badgeText}</span>`;
            }

            // Función para formatear fecha (yyyy-mm-dd a dd/mm/yyyy)
            function formatearFecha(fechaStr) {
                if (!fechaStr) return '';

                try {
                    // Manejar diferentes formatos de fecha
                    // Si la fecha incluye hora (formato MySQL: "2023-06-15 14:30:00")
                    if (fechaStr.includes(' ')) {
                        fechaStr = fechaStr.split(' ')[0]; // Tomar solo la parte de la fecha
                    }

                    const partes = fechaStr.split('-');
                    if (partes.length !== 3) return fechaStr;

                    return `${partes[2]}/${partes[1]}/${partes[0]}`;
                } catch (e) {
                    console.error("Error al formatear fecha:", e);
                    return fechaStr;
                }
            }

            // Función para formatear montos con dos decimales
            function formatearMonto(monto) {
                if (monto === undefined || monto === null) return 'S/. 0.00';

                try {
                    const montoNumerico = parseFloat(monto);
                    return `S/. ${montoNumerico.toFixed(2)}`;
                } catch (e) {
                    console.error("Error al formatear monto:", e);
                    return 'S/. 0.00';
                }
            }

            // Función para formatear motivo de devolución
            function formatearMotivo(motivo) {
                if (!motivo) return 'No especificado';

                const motivosMap = {
                    'SOLICITUD_PACIENTE': 'Solicitud del paciente',
                    'EMERGENCIA_MEDICA': 'Emergencia médica',
                    'PROBLEMA_HORARIO': 'Problema de horario',
                    'CANCELACION_DOCTOR': 'Cancelación por parte del doctor',
                    'OTRO': 'Otro motivo'
                };

                return motivosMap[motivo] || motivo;
            }

            // Generación eficiente de la tabla
            function crearEstructuraTabla() {
                const contenedorTabla = document.getElementById('contenedor-tabla');

                // Limpiar contenedor
                contenedorTabla.innerHTML = `
                <table id="tabla-devoluciones" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Comprobante</th>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>Documento</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Motivo</th>
                            <th>Autorizado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            `;

                return document.getElementById('tabla-devoluciones');
            }

            // Inicialización optimizada de DataTables
            function inicializarTablaDevoluciones() {
                try {
                    console.log("Inicializando tabla de devoluciones...");

                    // Crear estructura de tabla
                    const tablaElement = crearEstructuraTabla();

                    // Vincular el buscador global
                    $('#buscar-global').on('keyup', function() {
                        if (tablaDevoluciones) {
                            tablaDevoluciones.search(this.value).draw();
                        }
                    });

                    // Configurar selector de registros por página
                    $('#registros-por-pagina').on('change', function() {
                        const valor = parseInt($(this).val());
                        if (tablaDevoluciones && !isNaN(valor)) {
                            tablaDevoluciones.page.len(valor).draw();
                        }
                    });

                    // Configuración para mostrar tabla normal sin scroll
                    tablaDevoluciones = $(tablaElement).DataTable({
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
                        scrollY: false,
                        scrollCollapse: false,
                        rowCallback: function(row, data, index) {
                            return row;
                        },
                        autoWidth: false,
                        responsive: true,
                        paging: true
                    });

                    console.log("Tabla inicializada correctamente");

                    // Cargar datos inmediatamente
                    cargarDevolucionesTabla();

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

            // FUNCIÓN MODIFICADA: Cargar las devoluciones en la tabla
            function cargarDevolucionesTabla() {
                console.log("Cargando devoluciones...");

                // Ocultar loader inicial después de un breve tiempo
                setTimeout(() => {
                    const loaderInicial = document.getElementById('loader-inicial');
                    if (loaderInicial) {
                        loaderInicial.style.display = 'none';
                    }
                }, 400);

                // Mostrar mensaje de carga discreto en la tabla
                tablaDevoluciones.clear().draw();

                // Obtener valores de los filtros
                const documento = $('#documento-filter').val() || '';
                const fechaInicio = $('#fecha-inicio').val() || '';
                const fechaFin = $('#fecha-fin').val() || '';
                const metodo = $('#metodo-filter').val() || '';
                const motivo = $('#motivo-filter').val() || '';

                // Resaltar visualmente filtros aplicados
                resaltarFiltrosAplicados(documento, fechaInicio, fechaFin, metodo, motivo);

                // Construir URL con todos los parámetros
                let params = '?op=listar';

                // Añadir todos los filtros a la URL
                if (documento) {
                    params += `&documento=${encodeURIComponent(documento)}`;
                }

                if (fechaInicio) {
                    params += `&fecha_inicio=${encodeURIComponent(fechaInicio)}`;
                }

                if (fechaFin) {
                    params += `&fecha_fin=${encodeURIComponent(fechaFin)}`;
                }

                if (metodo) {
                    params += `&metodo=${encodeURIComponent(metodo)}`;
                }

                if (motivo) {
                    params += `&motivo=${encodeURIComponent(motivo)}`;
                }

                // URL del controlador
                const url = `../../../controllers/devolucion.controller.php${params}`;
                console.log("URL de solicitud:", url);

                // Mensaje de carga simple en la tabla
                $('#tabla-devoluciones tbody').html(`
        <tr>
            <td colspan="10" class="text-center py-3">
                <div class="spinner-border text-primary spinner-border-sm" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <span class="ms-2">Cargando datos...</span>
            </td>
        </tr>
    `);

                // Realizar la solicitud con timeout
                const fetchTimeout = new Promise((resolve, reject) => {
                    const timeoutId = setTimeout(() => {
                        reject(new Error("Tiempo de espera agotado"));
                    }, 15000); // 15 segundos máximo

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
                            tablaDevoluciones.clear();

                            // Verificar si hay datos válidos
                            if (data && data.status === true && Array.isArray(data.data)) {
                                if (data.data.length > 0) {
                                    console.log(`Devoluciones encontradas: ${data.data.length}`);

                                    // OPTIMIZADO: Preparar todas las filas antes de añadir
                                    const filas = [];

                                    // Usar un bucle tradicional para mejor rendimiento
                                    for (let i = 0; i < data.data.length; i++) {
                                        try {
                                            const devolucion = data.data[i];

                                            // CORRECCIÓN IMPORTANTE: Ya no necesitamos filtrar en el cliente
                                            // porque ahora los filtros se aplican en el servidor

                                            // Formatear fecha
                                            const fechaFormateada = formatearFecha(devolucion.fecha_devolucion);

                                            // Formatear monto
                                            const montoFormateado = formatearMonto(devolucion.monto);

                                            // Badge para método de devolución
                                            const metodoBadge = getBadgeMetodo(devolucion.metodo);

                                            // Formatear motivo
                                            const motivoFormateado = formatearMotivo(devolucion.motivo);

                                            // Extracción correcta del usuario que autorizó
                                            let nombreUsuario = '';

                                            // Datos del objeto usuario si viene como estructura completa
                                            if (devolucion.usuario) {
                                                if (typeof devolucion.usuario === 'object') {
                                                    if (devolucion.usuario.apellidos && devolucion.usuario.nombres) {
                                                        nombreUsuario = `${devolucion.usuario.apellidos}, ${devolucion.usuario.nombres}`;
                                                    } else if (devolucion.usuario.nomuser) {
                                                        nombreUsuario = devolucion.usuario.nomuser;
                                                    }
                                                } else {
                                                    nombreUsuario = devolucion.usuario;
                                                }
                                            }
                                            // Buscar en diferentes propiedades donde podría estar el nombre del usuario
                                            else if (devolucion.usuario_nombre_apellido) {
                                                nombreUsuario = devolucion.usuario_nombre_apellido;
                                            } else if (devolucion.usuario_apellidos && devolucion.usuario_nombres) {
                                                nombreUsuario = `${devolucion.usuario_apellidos}, ${devolucion.usuario_nombres}`;
                                            } else if (devolucion.usuario_registro) {
                                                nombreUsuario = devolucion.usuario_registro;
                                            } else if (devolucion.idusuario) {
                                                // Si solo tenemos ID, mostrar algo mejor que "No especificado"
                                                nombreUsuario = `Usuario ID: ${devolucion.idusuario}`;
                                            } else {
                                                // Si no hay ningún dato de usuario
                                                nombreUsuario = 'No especificado';
                                            }

                                            // Crear botones de acción
                                            const botonesAccion = `
                                    <div class="btn-group" role="group">
                                        <a href="verComprobanteDevolucion.php?id=${devolucion.iddevolucion || ''}" 
                                           class="btn btn-info btn-sm btn-action" 
                                           data-bs-toggle="tooltip" title="Ver comprobante">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                    </div>
                                `;

                                            // Añadir fila al array
                                            filas.push([
                                                devolucion.iddevolucion || '',
                                                devolucion.numero_comprobante || '',
                                                fechaFormateada,
                                                devolucion.nombre_paciente || '',
                                                devolucion.documento_paciente || '',
                                                `<span class="monto-devolucion">${montoFormateado}</span>`,
                                                metodoBadge,
                                                motivoFormateado,
                                                nombreUsuario,
                                                botonesAccion
                                            ]);
                                        } catch (rowError) {
                                            console.error(`Error al procesar fila:`, rowError);
                                        }
                                    }

                                    // Añadir todas las filas de una vez
                                    tablaDevoluciones.rows.add(filas);
                                    tablaDevoluciones.draw();

                                    // Inicializar tooltips
                                    initTooltips();

                                    console.log("Tabla actualizada con éxito");
                                } else {
                                    // No hay resultados
                                    tablaDevoluciones.clear().draw();
                                    $('#tabla-devoluciones tbody').html(`
                            <tr>
                                <td colspan="10" class="text-center">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No se encontraron devoluciones que coincidan con los criterios de búsqueda
                                    </div>
                                </td>
                            </tr>
                        `);
                                }
                            } else {
                                // Formato de respuesta incorrecto
                                console.warn("Formato incorrecto en la respuesta:", data);

                                tablaDevoluciones.clear().draw();
                                $('#tabla-devoluciones tbody').html(`
                        <tr>
                            <td colspan="10" class="text-center">
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

                            tablaDevoluciones.clear().draw();
                            $('#tabla-devoluciones tbody').html(`
                    <tr>
                        <td colspan="10" class="text-center">
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
                        console.error("Error al cargar devoluciones:", error);

                        tablaDevoluciones.clear().draw();
                        $('#tabla-devoluciones tbody').html(`
                <tr>
                    <td colspan="10" class="text-center">
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error al cargar las devoluciones: ${error.message}
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

            // FUNCIONES PARA LOS FILTROS

            // Verificar si hay filtros aplicados
            function hayFiltrosAplicados() {
                return (
                    $('#documento-filter').val() ||
                    $('#fecha-inicio').val() ||
                    $('#fecha-fin').val() ||
                    $('#metodo-filter').val() ||
                    $('#motivo-filter').val()
                );
            }

            // Resaltar visualmente los filtros aplicados
            function resaltarFiltrosAplicados(documento, fechaInicio, fechaFin, metodo, motivo) {
                // Resetear todos los estilos primero
                $('.form-control, .form-select').removeClass('border-primary');

                // Añadir clase de resaltado a los filtros que tienen valor
                if (documento) $('#documento-filter').addClass('border-primary');
                if (fechaInicio) $('#fecha-inicio').addClass('border-primary');
                if (fechaFin) $('#fecha-fin').addClass('border-primary');
                if (metodo) $('#metodo-filter').addClass('border-primary');
                if (motivo) $('#motivo-filter').addClass('border-primary');
            }

            // Limpiar filtros
            function limpiarFiltros() {
                console.log("Limpiando todos los filtros...");

                // Limpiar campos de texto
                $('#documento-filter').val('');

                // Limpiar fechas
                $('#fecha-inicio').val('');
                $('#fecha-fin').val('');

                // Resetear selects a primera opción
                $('#metodo-filter').prop('selectedIndex', 0);
                $('#motivo-filter').prop('selectedIndex', 0);

                // Limpiar búsqueda global
                $('#buscar-global').val('');
                if (tablaDevoluciones) {
                    tablaDevoluciones.search('').draw();
                }

                // Cargar todos los datos sin alerta
                cargarDevolucionesTabla();
            }

            // EVENTOS

            // Función para configurar eventos
            function configurarEventos() {
                console.log("Configurando eventos...");

                // Botón de buscar con filtros
                $('#btn-buscar-filtros').on('click', function() {
                    console.log("Aplicando filtros de búsqueda...");
                    cargarDevolucionesTabla();
                });

                // Botón de limpiar filtros
                $('#btn-limpiar-filtros').on('click', function() {
                    console.log("Limpiando filtros...");
                    limpiarFiltros();
                });

                // Evento para Enter en campos de texto
                $('#documento-filter').on('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        $('#btn-buscar-filtros').click();
                    }
                });

                // También permitir Enter en los campos de fecha
                $('#fecha-inicio, #fecha-fin').on('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        $('#btn-buscar-filtros').click();
                    }
                });

                // CORRECCIÓN: Añadir cambio automático en fecha fin cuando se selecciona fecha inicio
                $('#fecha-inicio').on('change', function() {
                    const fechaInicio = $(this).val();
                    const fechaFin = $('#fecha-fin').val();

                    // Si la fecha fin no está establecida o es anterior a la fecha inicio, actualizarla
                    if (!fechaFin || fechaFin < fechaInicio) {
                        $('#fecha-fin').val(fechaInicio);
                    }
                });

                // Eventos para los botones de exportación
                $('#btn-excel').on('click', function() {
                    console.log("Exportando a Excel...");
                    if (tablaDevoluciones) {
                        // Crear y disparar un enlace de descarga
                        const link = document.createElement('a');
                        link.href = '#';
                        link.download = 'historial_devoluciones.xlsx';
                        link.click();

                        // Alternativa: Implementar exportación real si DataTable buttons está disponible
                        if ($.fn.DataTable.Buttons) {
                            new $.fn.DataTable.Buttons(tablaDevoluciones, {
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
                    if (tablaDevoluciones) {
                        // Implementación básica
                        const link = document.createElement('a');
                        link.href = '#';
                        link.download = 'historial_devoluciones.pdf';
                        link.click();
                    }
                });

                $('#btn-print').on('click', function() {
                    console.log("Imprimiendo...");
                    if (tablaDevoluciones) {
                        window.print();
                    }
                });

                console.log("Eventos configurados correctamente");
            }

            // INICIALIZACIÓN PRINCIPAL

            // Función de inicialización global
            function inicializar() {
                console.log("Iniciando aplicación...");

                try {
                    // 1. Configurar eventos (no depende de datos)
                    configurarEventos();

                    // 2. Inicializar la tabla con un ligero retraso para permitir renderizado UI
                    setTimeout(() => {
                        inicializarTablaDevoluciones();
                        console.log("Aplicación iniciada correctamente");
                    }, 50);

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