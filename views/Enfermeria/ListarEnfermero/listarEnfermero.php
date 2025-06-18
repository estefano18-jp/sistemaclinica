<?php
require_once '../../include/header.administrador.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Enfermeros</title>
    <link rel="stylesheet" href="../../../css/listarEnfermero.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0"><i class="fas fa-user-nurse me-2"></i>Listado de Enfermeros</h2>
                        <a href="../RegistrarEnfermero/registrarEnfermero.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Registrar Nuevo Enfermero
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="filtroNombre" class="form-label">Nombre o Apellido</label>
                                <input type="text" id="filtroNombre" class="form-control" placeholder="Buscar por nombre o apellido">
                            </div>
                            <div class="col-md-3">
                                <label for="filtroDocumento" class="form-label">Documento</label>
                                <input type="text" id="filtroDocumento" class="form-control" placeholder="Buscar por número de documento">
                            </div>
                            <div class="col-md-3">
                                <label for="filtroEstado" class="form-label">Estado</label>
                                <select id="filtroEstado" class="form-select">
                                    <option value="">Todos los Estados</option>
                                    <option value="ACTIVO">Activo</option>
                                    <option value="INACTIVO">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtroFechaRegistro" class="form-label">Fecha de Registro</label>
                                <input type="date" id="filtroFechaRegistro" class="form-control" placeholder="Filtrar por fecha">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tablaEnfermeros" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tipo Doc</th>
                                        <th>N° Documento</th>
                                        <th>Apellidos</th>
                                        <th>Nombres</th>
                                        <th>Género</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th>Estado</th>
                                        <th>Cambiar Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoTablaEnfermeros">
                                    <!-- Los datos se cargarán dinámicamente con JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para seleccionar opción de edición -->
    <div class="modal fade" id="modalSeleccionEdicion" tabindex="-1" aria-labelledby="modalSeleccionEdicionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalSeleccionEdicionLabel">Seleccione opción de edición</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <input type="hidden" id="idEnfermeroSeleccionado">
                    <div class="d-grid gap-3">
                        <button id="btnInfoPersonal" class="btn btn-success btn-lg">
                            <i class="fas fa-user me-2"></i> Información Personal
                        </button>
                        <button id="btnCredenciales" class="btn btn-info btn-lg">
                            <i class="fas fa-key me-2"></i> Credenciales de Acceso
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar información del enfermero -->
    <div class="modal fade" id="modalEditarEnfermero" tabindex="-1" aria-labelledby="modalEditarEnfermeroLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalEditarEnfermeroLabel"><i class="fas fa-user-edit me-2"></i> Editar Información del Enfermero</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="contenidoEditarEnfermero">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando información del enfermero...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar credenciales del enfermero -->
    <div class="modal fade" id="modalEditarCredenciales" tabindex="-1" aria-labelledby="modalEditarCredencialesLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalEditarCredencialesLabel"><i class="fas fa-key me-2"></i> Editar Credenciales de Acceso</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="contenidoEditarCredenciales">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando credenciales...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar el PDF -->
    <div class="modal fade" id="modalPDF" tabindex="-1" aria-labelledby="modalPDFLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title" id="modalPDFLabel"><i class="fas fa-file-pdf me-2"></i> Vista previa de PDF</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="height: 70vh;">
                    <iframe id="pdfFrame" src="" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a id="descargarPDFBtn" href="" target="_blank" class="btn btn-primary">
                        <i class="fas fa-download me-1"></i> Descargar PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            let tablaEnfermeros;

            // Función para cargar enfermeros
            function cargarEnfermeros() {
                // Capturar los valores de los filtros
                const filtroNombre = $('#filtroNombre').val();
                const filtroDocumento = $('#filtroDocumento').val();
                const filtroEstado = $('#filtroEstado').val();
                const filtroFechaRegistro = $('#filtroFechaRegistro').val();

                // Mostrar un indicador de carga
                $('#cuerpoTablaEnfermeros').html(`
                    <tr>
                        <td colspan="11" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando listado de enfermeros...</p>
                        </td>
                    </tr>
                `);

                // Construir la URL con los parámetros de filtro
                let url = '../../../controllers/enfermeria.controller.php?op=listar';

                if (filtroNombre) {
                    url += '&busqueda=' + encodeURIComponent(filtroNombre);
                }

                if (filtroEstado) {
                    url += '&estado=' + encodeURIComponent(filtroEstado);
                }

                if (filtroFechaRegistro) {
                    url += '&fechaRegistro=' + encodeURIComponent(filtroFechaRegistro);
                }

                console.log("URL de búsqueda:", url); // Log para depuración

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("Respuesta recibida:", response); // Depuración

                        if (response && response.status) {
                            // Destruir la tabla existente si ya está inicializada
                            if (tablaEnfermeros) {
                                tablaEnfermeros.destroy();
                            }

                            // Verificar si hay datos
                            if (!response.data || response.data.length === 0) {
                                $('#cuerpoTablaEnfermeros').html(`
                                    <tr>
                                        <td colspan="11" class="text-center">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>No hay enfermeros registrados
                                            </div>
                                        </td>
                                    </tr>
                                `);
                                return;
                            }

                            // Inicializar la tabla
                            tablaEnfermeros = $('#tablaEnfermeros').DataTable({
                                responsive: true,
                                language: {
                                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                                },
                                data: response.data,
                                columns: [
                                    { data: 'idcolaborador' },
                                    { data: 'tipodoc' },
                                    { data: 'nrodoc' },
                                    { data: 'apellidos' },
                                    { data: 'nombres' },
                                    { 
                                        data: 'genero',
                                        render: function(data) {
                                            if (data === 'M') return 'Masculino';
                                            if (data === 'F') return 'Femenino';
                                            return data || 'No especificado';
                                        }
                                    },
                                    { 
                                        data: 'telefono',
                                        render: function(data) {
                                            return data || 'Sin teléfono';
                                        }
                                    },
                                    { data: 'email' },
                                    { 
                                        data: 'estado',
                                        render: function(data) {
                                            if (data === 'ACTIVO') {
                                                return '<span class="badge bg-success">Activo</span>';
                                            } else {
                                                return '<span class="badge bg-danger">Inactivo</span>';
                                            }
                                        }
                                    },
                                    {
                                        data: 'estado',
                                        render: function(data, type, row) {
                                            if (data === 'ACTIVO') {
                                                return `<button class="btn btn-danger btn-sm btnCambiarEstado" data-nrodoc="${row.nrodoc}" data-estado="${data}">
                                                    <i class="fas fa-toggle-off me-1"></i> Desactivar
                                                </button>`;
                                            } else {
                                                return `<button class="btn btn-success btn-sm btnCambiarEstado" data-nrodoc="${row.nrodoc}" data-estado="${data}">
                                                    <i class="fas fa-toggle-on me-1"></i> Activar
                                                </button>`;
                                            }
                                        }
                                    },
                                    {
                                        data: null,
                                        render: function(data, type, row) {
                                            return `
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-info btn-sm btnVer" 
                                                        data-id="${row.idcolaborador}"
                                                        data-bs-toggle="tooltip" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-warning btn-sm btnEditar" 
                                                        data-id="${row.idcolaborador}"
                                                        data-bs-toggle="tooltip" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm btnEliminar" 
                                                        data-nrodoc="${row.nrodoc}"
                                                        data-bs-toggle="tooltip" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>`;
                                        }
                                    }
                                ],
                                columnDefs: [
                                    { width: "5%", targets: 0 },
                                    { width: "7%", targets: [1, 2] },
                                    { width: "10%", targets: [3, 4] },
                                    { width: "7%", targets: [5, 6] },
                                    { width: "15%", targets: 7 },
                                    { width: "7%", targets: 8 },
                                    { width: "10%", targets: 9 },
                                    { width: "12%", targets: 10, className: 'text-center' }
                                ],
                                order: [[0, 'desc']] // Ordenar por ID descendente por defecto
                            });

                            // Inicializar tooltips
                            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                                return new bootstrap.Tooltip(tooltipTriggerEl);
                            });

                            // Aplicar filtros adicionales (documento)
                            aplicarFiltrosAdicionales();

                            // Añadir event listeners a los filtros
                            $('#filtroNombre, #filtroDocumento, #filtroEstado, #filtroFechaRegistro').on('change keyup', function() {
                                aplicarFiltros();
                            });
                        } else {
                            console.error("Error en la respuesta:", response); // Depuración

                            $('#cuerpoTablaEnfermeros').html(`
                                <tr>
                                    <td colspan="11" class="text-center">
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            ${response?.mensaje || 'Error al cargar los enfermeros'}
                                        </div>
                                    </td>
                                </tr>
                            `);

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response?.mensaje || 'No se pudieron cargar los enfermeros'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error AJAX:", status, error); // Depuración
                        console.error("Respuesta:", xhr.responseText); // Depuración

                        $('#cuerpoTablaEnfermeros').html(`
                            <tr>
                                <td colspan="11" class="text-center">
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Error de conexión: ${status} - ${error}
                                    </div>
                                </td>
                            </tr>
                        `);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: 'No se pudo conectar con el servidor. Detalles: ' + error
                        });
                    }
                });
            }

            // Función para aplicar filtros
            function aplicarFiltros() {
                const filtroNombre = $('#filtroNombre').val().toLowerCase();
                const filtroDocumento = $('#filtroDocumento').val().toLowerCase();
                const filtroEstado = $('#filtroEstado').val();
                const filtroFechaRegistro = $('#filtroFechaRegistro').val();

                // Limpiar filtros existentes
                $.fn.dataTable.ext.search.pop();

                // Filtro combinado para nombre y apellido (columnas 3 y 4)
                if (filtroNombre) {
                    $.fn.dataTable.ext.search.push(
                        function(settings, data, dataIndex) {
                            const apellido = data[3].toLowerCase();
                            const nombre = data[4].toLowerCase();

                            if (!apellido.includes(filtroNombre) && !nombre.includes(filtroNombre)) {
                                return false;
                            }

                            return true;
                        }
                    );
                }

                // Aplicar filtro de documento
                if (filtroDocumento) {
                    tablaEnfermeros.columns(2).search(filtroDocumento);
                } else {
                    tablaEnfermeros.columns(2).search('');
                }

                // Aplicar filtro de estado
                if (filtroEstado) {
                    if (filtroEstado === 'ACTIVO') {
                        tablaEnfermeros.columns(8).search('Activo');
                    } else if (filtroEstado === 'INACTIVO') {
                        tablaEnfermeros.columns(8).search('Inactivo');
                    }
                } else {
                    tablaEnfermeros.columns(8).search('');
                }

                // Filtro de fecha de registro
                if (filtroFechaRegistro) {
                    $.fn.dataTable.ext.search.push(
                        function(settings, data, dataIndex) {
                            const filaFechaRegistro = tablaEnfermeros.row(dataIndex).data().fecha_registro;
                            if (!filaFechaRegistro) return true;
                            
                            const fechaRegistroFormato = new Date(filaFechaRegistro).toISOString().split('T')[0];
                            return fechaRegistroFormato === filtroFechaRegistro;
                        }
                    );
                }

                // Aplicar los filtros a la tabla
                tablaEnfermeros.draw();

                // Limpiar filtros personalizados después de aplicarlos
                $.fn.dataTable.ext.search.pop();
                if (filtroNombre) {
                    $.fn.dataTable.ext.search.pop();
                }
                if (filtroFechaRegistro) {
                    $.fn.dataTable.ext.search.pop();
                }
            }

            // Función para aplicar filtros adicionales
            function aplicarFiltrosAdicionales() {
                // Aplicar filtros iniciales si hay valores en los campos
                if ($('#filtroNombre').val() || $('#filtroDocumento').val() || $('#filtroEstado').val() || $('#filtroFechaRegistro').val()) {
                    aplicarFiltros();
                }
            }

            // Cargar enfermeros al iniciar
            cargarEnfermeros();

            // Evento para Ver detalles
            $(document).on('click', '.btnVer', function() {
                const idEnfermero = $(this).data('id');
                mostrarPDF(idEnfermero);
            });

            // Evento para Editar - Ahora abre el modal de selección
            $(document).on('click', '.btnEditar', function() {
                const idEnfermero = $(this).data('id');
                
                // Guardar el ID del enfermero seleccionado
                $('#idEnfermeroSeleccionado').val(idEnfermero);
                
                // Mostrar el modal de selección
                const modalSeleccion = new bootstrap.Modal(document.getElementById('modalSeleccionEdicion'));
                modalSeleccion.show();
            });

            // Configurar botones del modal de selección
            $('#btnInfoPersonal').on('click', function() {
                const idEnfermero = $('#idEnfermeroSeleccionado').val();
                
                // Cerrar modal de selección
                bootstrap.Modal.getInstance(document.getElementById('modalSeleccionEdicion')).hide();
                
                // Mostrar el modal con spinner de carga
                const modalEditarEnfermero = new bootstrap.Modal(document.getElementById('modalEditarEnfermero'));
                modalEditarEnfermero.show();
                
                // Cargar contenido desde el endpoint
                $.ajax({
                    url: `editarEnfermero.php?id=${idEnfermero}`,
                    type: 'GET',
                    success: function(response) {
                        $('#contenidoEditarEnfermero').html(response);
                        inicializarFormularioEdicion();
                    },
                    error: function() {
                        $('#contenidoEditarEnfermero').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error al cargar la información del enfermero.
                            </div>
                        `);
                    }
                });
            });
            
            $('#btnCredenciales').on('click', function() {
                const idEnfermero = $('#idEnfermeroSeleccionado').val();
                
                // Cerrar modal de selección
                bootstrap.Modal.getInstance(document.getElementById('modalSeleccionEdicion')).hide();
                
                // Mostrar el modal con spinner de carga
                const modalEditarCredenciales = new bootstrap.Modal(document.getElementById('modalEditarCredenciales'));
                modalEditarCredenciales.show();
                
                // Cargar contenido desde el endpoint
                $.ajax({
                    url: `editarCredenciales.php?id=${idEnfermero}`,
                    type: 'GET',
                    success: function(response) {
                        $('#contenidoEditarCredenciales').html(response);
                        inicializarFormularioCredenciales();
                    },
                    error: function() {
                        $('#contenidoEditarCredenciales').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error al cargar las credenciales del enfermero.
                            </div>
                        `);
                    }
                });
            });

            // Evento para Cambiar Estado
            $(document).on('click', '.btnCambiarEstado', function() {
                const nrodoc = $(this).data('nrodoc');
                const estadoActual = $(this).data('estado');

                Swal.fire({
                    title: "¿Cambiar estado?",
                    text: `¿Desea cambiar el estado del enfermero de "${estadoActual}" a "${estadoActual === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO'}"?`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Sí, cambiar estado",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        cambiarEstadoEnfermero(nrodoc);
                    }
                });
            });

            // Evento para Eliminar
            $(document).on('click', '.btnEliminar', function() {
                const nrodoc = $(this).data('nrodoc');
                eliminarEnfermeroConfirm(nrodoc);
            });

            // Función para cambiar estado del enfermero
            function cambiarEstadoEnfermero(nrodoc) {
                // Mostrar loading
                Swal.fire({
                    title: 'Procesando',
                    text: 'Cambiando estado del enfermero...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '../../../controllers/enfermeria.controller.php?op=cambiar_estado&nrodoc=' + nrodoc,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        Swal.close();

                        if (data.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Estado Actualizado',
                                text: data.mensaje,
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                cargarEnfermeros(); // Recargar tabla
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.mensaje,
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo cambiar el estado del enfermero',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            }

            // Función para mostrar el PDF en el modal
            function mostrarPDF(idEnfermero) {
                // Configurar la URL del PDF
                const pdfUrl = `generarPDF.php?id=${idEnfermero}`;

                // Actualizar el iframe y el botón de descarga
                $('#pdfFrame').attr('src', pdfUrl);
                $('#descargarPDFBtn').attr('href', pdfUrl + '&download=1');

                // Mostrar el modal
                const modalPDF = new bootstrap.Modal(document.getElementById('modalPDF'));
                modalPDF.show();
            }

            // Función para confirmar eliminación de enfermero
            function eliminarEnfermeroConfirm(nrodoc) {
                Swal.fire({
                    title: "¿Está seguro?",
                    text: "Esta acción eliminará al enfermero de forma permanente",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Sí, eliminar",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        eliminarEnfermero(nrodoc);
                    }
                });
            }

            // Función para eliminar enfermero
            function eliminarEnfermero(nrodoc) {
                // Mostrar loading
                Swal.fire({
                    title: 'Procesando',
                    text: 'Eliminando enfermero...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '../../../controllers/enfermeria.controller.php?op=eliminar&nrodoc=' + nrodoc,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        Swal.close();

                        if (data.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: data.mensaje,
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                cargarEnfermeros(); // Recargar tabla
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.mensaje,
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo eliminar el enfermero',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            }

            // Inicializar validación del formulario de edición
            function inicializarFormularioEdicion() {
                const form = document.getElementById('editarEnfermeroForm');
                if (!form) {
                    console.warn('No se encontró el formulario de edición de enfermero');
                    return;
                }

                // Configuración para cada tipo de documento
                const documentoConfig = {
                    'DNI': {
                        pattern: /^\d{8}$/,
                        message: 'El DNI debe tener 8 dígitos numéricos',
                        maxLength: 8,
                        onlyNumbers: true
                    },
                    'PASAPORTE': {
                        pattern: /^[A-Z0-9]{6,12}$/,
                        message: 'El pasaporte debe tener entre 6 y 12 caracteres alfanuméricos',
                        maxLength: 12,
                        onlyNumbers: false
                    },
                    'CARNET DE EXTRANJERIA': {
                        pattern: /^[A-Z0-9]{9}$/,
                        message: 'El carnet de extranjería debe tener 9 caracteres alfanuméricos',
                        maxLength: 9,
                        onlyNumbers: false
                    },
                    'OTRO': {
                        pattern: /^.{1,15}$/,
                        message: 'El documento puede tener hasta 15 caracteres',
                        maxLength: 15,
                        onlyNumbers: false
                    }
                };

                // Obtener referencias a los elementos
                const tipodocSelect = document.getElementById('tipodoc');
                const nrodocInput = document.getElementById('nrodoc');

                if (tipodocSelect && nrodocInput) {
                    // Configurar maxlength inicial según el tipo de documento actual
                    const tipodocActual = tipodocSelect.value;
                    if (tipodocActual && documentoConfig[tipodocActual]) {
                        nrodocInput.setAttribute('maxlength', documentoConfig[tipodocActual].maxLength);
                        nrodocInput.pattern = documentoConfig[tipodocActual].pattern.source;
                        nrodocInput.title = documentoConfig[tipodocActual].message;
                    }

                    // Actualizar validación cuando cambia el tipo de documento
                    tipodocSelect.addEventListener('change', function() {
                        const tipodoc = this.value;

                        // Configurar maxlength y otras restricciones
                        if (tipodoc && documentoConfig[tipodoc]) {
                            const config = documentoConfig[tipodoc];
                            nrodocInput.setAttribute('maxlength', config.maxLength);

                            // Limpiar el valor si el tipo cambia
                            nrodocInput.value = '';

                            // Establecer patrón y mensaje
                            nrodocInput.pattern = config.pattern.source;
                            nrodocInput.title = config.message;
                        }
                    });

                    // Validar en tiempo real
                    nrodocInput.addEventListener('input', function() {
                        const tipodoc = tipodocSelect.value;

                        if (tipodoc && documentoConfig[tipodoc]) {
                            const config = documentoConfig[tipodoc];

                            // Si solo se permiten números, eliminar otros caracteres
                            if (config.onlyNumbers) {
                                this.value = this.value.replace(/\D/g, '');
                            }

                            // Convertir a mayúsculas para documentos que lo requieran
                            if (tipodoc === 'PASAPORTE' || tipodoc === 'CARNET DE EXTRANJERIA') {
                                this.value = this.value.toUpperCase();
                            }

                            // Limitar a la longitud máxima
                            if (this.value.length > config.maxLength) {
                                this.value = this.value.substring(0, config.maxLength);
                            }

                            // Validar longitud y patrón
                            if (this.value && config.pattern.test(this.value)) {
                                this.classList.add('is-valid');
                                this.classList.remove('is-invalid');
                            } else if (this.value) {
                                this.classList.add('is-invalid');
                                this.classList.remove('is-valid');
                            } else {
                                this.classList.remove('is-valid');
                                this.classList.remove('is-invalid');
                            }
                        }
                    });
                }

                // Agregar evento de envío de formulario
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Validar formulario
                    if (!validarFormularioEdicion()) return;

                    // Mostrar loading
                    Swal.fire({
                        title: 'Procesando',
                        text: 'Guardando información...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Enviar formulario vía AJAX
                    const formData = new FormData(form);

                    $.ajax({
                        url: 'procesarEdicion.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function(data) {
                            Swal.close();

                            if (data.status) {
                                // Cerrar el modal
                                bootstrap.Modal.getInstance(document.getElementById('modalEditarEnfermero')).hide();

                                // Mostrar mensaje de éxito
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Actualización exitosa',
                                    text: data.mensaje || 'Información actualizada correctamente',
                                    confirmButtonColor: '#3085d6'
                                }).then(() => {
                                    // Recargar la tabla
                                    cargarEnfermeros();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.mensaje || "Error al actualizar la información.",
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        },
                        error: function() {
                            Swal.close();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: "Error al procesar la solicitud.",
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    });
                });
            }

            // Inicializar validación del formulario de credenciales
            function inicializarFormularioCredenciales() {
                const form = document.getElementById('editarCredencialesForm');
                if (!form) {
                    console.warn('No se encontró el formulario de edición de credenciales');
                    return;
                }

                // Validar contraseñas en tiempo real
                const passwordInput = document.getElementById('nuevaPassword');
                const confirmPasswordInput = document.getElementById('confirmarPassword');

                if (passwordInput && confirmPasswordInput) {
                    // Validar contraseña
                    passwordInput.addEventListener('input', function() {
                        if (this.value.length >= 6) {
                            this.classList.add('is-valid');
                            this.classList.remove('is-invalid');
                        } else if (this.value.length > 0) {
                            this.classList.add('is-invalid');
                            this.classList.remove('is-valid');
                        } else {
                            this.classList.remove('is-valid');
                            this.classList.remove('is-invalid');
                        }

                        // Verificar coincidencia si se ha ingresado una confirmación
                        if (confirmPasswordInput.value) {
                            if (this.value === confirmPasswordInput.value) {
                                confirmPasswordInput.classList.add('is-valid');
                                confirmPasswordInput.classList.remove('is-invalid');
                            } else {
                                confirmPasswordInput.classList.add('is-invalid');
                                confirmPasswordInput.classList.remove('is-valid');
                            }
                        }
                    });

                    // Validar confirmación de contraseña
                    confirmPasswordInput.addEventListener('input', function() {
                        if (this.value && this.value === passwordInput.value) {
                            this.classList.add('is-valid');
                            this.classList.remove('is-invalid');
                        } else if (this.value) {
                            this.classList.add('is-invalid');
                            this.classList.remove('is-valid');
                        } else {
                            this.classList.remove('is-valid');
                            this.classList.remove('is-invalid');
                        }
                    });
                }

                // Agregar evento de envío de formulario
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Validar formulario
                    if (!validarFormularioCredenciales()) return;

                    // Mostrar loading
                    Swal.fire({
                        title: 'Procesando',
                        text: 'Actualizando credenciales...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Enviar formulario vía AJAX
                    const formData = new FormData(form);

                    $.ajax({
                        url: 'procesarCredenciales.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function(data) {
                            Swal.close();

                            if (data.status) {
                                // Cerrar el modal
                                bootstrap.Modal.getInstance(document.getElementById('modalEditarCredenciales')).hide();

                                // Mostrar mensaje de éxito
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Actualización exitosa',
                                    text: data.mensaje || 'Credenciales actualizadas correctamente',
                                    confirmButtonColor: '#3085d6'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.mensaje || "Error al actualizar las credenciales.",
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        },
                        error: function() {
                            Swal.close();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: "Error al procesar la solicitud.",
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    });
                });
            }

            function validarFormularioEdicion() {
                // Verificamos que los campos requeridos tengan valor
                const form = document.getElementById('editarEnfermeroForm');
                if (!form) return false;

                const campos = ['apellidos', 'nombres', 'tipodoc', 'nrodoc', 'genero', 'telefono', 'email'];
                let valido = true;

                campos.forEach(campo => {
                    const elemento = form.elements[campo];
                    if (elemento && !elemento.value.trim()) {
                        elemento.classList.add('is-invalid');
                        valido = false;
                    } else if (elemento) {
                        elemento.classList.remove('is-invalid');
                        elemento.classList.add('is-valid');
                    }
                });

                // Validación especial para email
                const emailInput = form.elements['email'];
                if (emailInput && emailInput.value.trim()) {
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailPattern.test(emailInput.value.trim())) {
                        emailInput.classList.add('is-invalid');
                        valido = false;
                    }
                }

                if (!valido) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Campos incompletos',
                        text: "Por favor complete todos los campos obligatorios correctamente.",
                        confirmButtonColor: '#3085d6'
                    });
                }

                return valido;
            }

            function validarFormularioCredenciales() {
                const form = document.getElementById('editarCredencialesForm');
                if (!form) return false;

                let valido = true;
                
                // Validar si se ha ingresado una nueva contraseña
                const passwordInput = form.elements['nuevaPassword'];
                const confirmPasswordInput = form.elements['confirmarPassword'];
                
                if (passwordInput && passwordInput.value.trim()) {
                    // Si se ingresó una contraseña, verificar longitud mínima
                    if (passwordInput.value.length < 6) {
                        passwordInput.classList.add('is-invalid');
                        valido = false;
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Contraseña inválida',
                            text: "La contraseña debe tener al menos 6 caracteres.",
                            confirmButtonColor: '#3085d6'
                        });
                        
                        return false;
                    }
                    
                    // Verificar que las contraseñas coincidan
                    if (passwordInput.value !== confirmPasswordInput.value) {
                        confirmPasswordInput.classList.add('is-invalid');
                        valido = false;
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Las contraseñas no coinciden',
                            text: "La contraseña y su confirmación deben ser iguales.",
                            confirmButtonColor: '#3085d6'
                        });
                        
                        return false;
                    }
                }
                
                return valido;
            }
        });
    </script>

    <style>
        /* Estilos para la tabla y botones */
        .table thead th {
            background-color: #f8f9fa;
            font-weight: bold;
            vertical-align: middle;
        }

        .table tbody td {
            vertical-align: middle;
        }

        .btn-group .btn {
            margin: 0 2px;
            width: 38px;
            height: 38px;
            padding: 6px 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Estilos para los botones de cambio de estado */
        .btnCambiarEstado {
            width: auto !important;
            height: auto !important;
            padding: 6px 12px !important;
            margin: 0;
        }

        /* Estilos para los modales */
        .modal-lg {
            max-width: 800px;
        }

        .modal-header {
            padding: 12px 16px;
        }

        .modal-body {
            padding: 20px;
        }

        /* Estilos para los botones de selección en el modal */
        #btnInfoPersonal,
        #btnCredenciales {
            padding: 15px;
            font-size: 18px;
            transition: all 0.3s;
        }

        #btnInfoPersonal:hover,
        #btnCredenciales:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Estilos para validación de formularios */
        .is-invalid {
            border-color: #dc3545 !important;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .is-valid {
            border-color: #198754 !important;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        /* Estilos para el contenedor principal y tarjeta */
        .container-fluid {
            padding-left: 30px;
            padding-right: 30px;
        }

        .card {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        /* Estilos para los filtros */
        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .form-control,
        .form-select {
            border-radius: 6px;
            padding: 0.6rem 0.75rem;
            border: 1px solid #ced4da;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Mejoras para DataTables */
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 6px;
            padding: 0.4rem 0.75rem;
            margin-left: 0.5rem;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 6px;
            padding: 0.375rem 1.75rem 0.375rem 0.75rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 6px;
            margin: 0 2px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #0d6efd;
            border-color: #0d6efd;
            color: white !important;
        }

        /* Mejoras para móviles */
        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 15px;
                padding-right: 15px;
            }

            .btn-group .btn {
                width: 35px;
                height: 35px;
            }

            .form-label {
                margin-top: 10px;
            }
        }
    </style>
</body>

</html>