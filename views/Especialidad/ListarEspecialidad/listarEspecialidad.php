<?php
require_once '../../include/header.administrador.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Especialidades</title>
    <link rel="stylesheet" href="../../../css/listarEspecialidad.css">
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
                        <h2 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Listado de Especialidades</h2>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrarEspecialidad">
                            <i class="fas fa-plus me-2"></i>Registrar Nueva Especialidad
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="filtroNombre" class="form-label">Nombre de Especialidad</label>
                                <input type="text" id="filtroNombre" class="form-control" placeholder="Buscar por nombre de especialidad">
                            </div>
                            <div class="col-md-4">
                                <label for="filtroPrecio" class="form-label">Precio de Atención</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/.</span>
                                    <input type="number" id="filtroPrecio" class="form-control" placeholder="Buscar por precio" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="filtroEstado" class="form-label">Estado</label>
                                <select id="filtroEstado" class="form-select">
                                    <option value="">Todos los estados</option>
                                    <option value="ACTIVO">Activo</option>
                                    <option value="INACTIVO">Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tablaEspecialidades" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Especialidad</th>
                                        <th>Precio Atención (S/.)</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoTablaEspecialidades">
                                    <!-- Los datos se cargarán dinámicamente con JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para registrar especialidad -->
    <div class="modal fade" id="modalRegistrarEspecialidad" tabindex="-1" aria-labelledby="modalRegistrarEspecialidadLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalRegistrarEspecialidadLabel">
                        <i class="fas fa-plus-circle me-2"></i> Registrar Nueva Especialidad
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formRegistrarEspecialidad">
                        <div class="mb-3">
                            <label for="especialidad" class="form-label">Nombre de Especialidad</label>
                            <input type="text" class="form-control" id="especialidad" name="especialidad" required>
                            <div class="invalid-feedback">
                                Por favor ingrese el nombre de la especialidad.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="precioatencion" class="form-label">Precio de Atención (S/.)</label>
                            <div class="input-group">
                                <span class="input-group-text">S/.</span>
                                <input type="number" class="form-control" id="precioatencion" name="precioatencion" step="0.01" min="0" required>
                                <div class="invalid-feedback">
                                    Por favor ingrese un precio válido.
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarEspecialidad">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar precio de especialidad -->
    <div class="modal fade" id="modalEditarPrecio" tabindex="-1" aria-labelledby="modalEditarPrecioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="modalEditarPrecioLabel">
                        <i class="fas fa-edit me-2"></i> Editar Precio de Atención
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarPrecio">
                        <input type="hidden" id="idespecialidad" name="idespecialidad">
                        <div class="mb-3">
                            <label for="nombreEspecialidad" class="form-label">Especialidad</label>
                            <input type="text" class="form-control" id="nombreEspecialidad" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="nuevoPrecio" class="form-label">Nuevo Precio de Atención (S/.)</label>
                            <div class="input-group">
                                <span class="input-group-text">S/.</span>
                                <input type="number" class="form-control" id="nuevoPrecio" name="precioatencion" step="0.01" min="0" required>
                                <div class="invalid-feedback">
                                    Por favor ingrese un precio válido.
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="btnActualizarPrecio">
                        <i class="fas fa-save me-2"></i>Actualizar Precio
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-labelledby="modalConfirmarEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalConfirmarEliminarLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i> Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar la especialidad <strong id="especialidadEliminar"></strong>?</p>
                    <p class="text-danger">Esta acción no se puede deshacer. Solo se puede eliminar si no está asignada a ningún colaborador.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">
                        <i class="fas fa-trash me-2"></i>Eliminar
                    </button>
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
            let tablaEspecialidades;
            let especialidadIdEliminar = null;

            // Función para cargar especialidades
            function cargarEspecialidades() {
                // Capturar los valores de los filtros
                const filtroNombre = $('#filtroNombre').val();
                const filtroPrecio = $('#filtroPrecio').val();

                // Mostrar un indicador de carga
                $('#cuerpoTablaEspecialidades').html(`
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando listado de especialidades...</p>
                        </td>
                    </tr>
                `);

                // Construir la URL con los parámetros
                let url = '../../../controllers/especialidad.controller.php?op=listar';

                if (filtroNombre) {
                    url += '&busqueda=' + encodeURIComponent(filtroNombre);
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
                            if (tablaEspecialidades) {
                                tablaEspecialidades.destroy();
                            }

                            // Verificar si hay datos
                            if (!response.data || response.data.length === 0) {
                                $('#cuerpoTablaEspecialidades').html(`
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>No hay especialidades registradas
                                            </div>
                                        </td>
                                    </tr>
                                `);
                                return;
                            }

                            // Inicializar la tabla
                            tablaEspecialidades = $('#tablaEspecialidades').DataTable({
                                responsive: true,
                                language: {
                                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                                },
                                data: response.data,
                                columns: [
                                    { data: 'idespecialidad' },
                                    { data: 'especialidad' },
                                    { 
                                        data: 'precioatencion',
                                        render: function(data) {
                                            // Formatear el precio con 2 decimales
                                            return parseFloat(data).toFixed(2);
                                        }
                                    },
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
                                        data: null,
                                        render: function(data, type, row) {
                                            let btnEstado = '';
                                            
                                            if (row.estado === 'ACTIVO') {
                                                btnEstado = `<button type="button" class="btn btn-danger btn-sm btnCambiarEstado" 
                                                    data-id="${row.idespecialidad}"
                                                    data-nombre="${row.especialidad}"
                                                    data-estado="INACTIVO"
                                                    data-bs-toggle="tooltip" title="Inactivar">
                                                    <i class="fas fa-ban"></i>
                                                </button>`;
                                            } else {
                                                btnEstado = `<button type="button" class="btn btn-success btn-sm btnCambiarEstado" 
                                                    data-id="${row.idespecialidad}"
                                                    data-nombre="${row.especialidad}"
                                                    data-estado="ACTIVO"
                                                    data-bs-toggle="tooltip" title="Activar">
                                                    <i class="fas fa-check"></i>
                                                </button>`;
                                            }
                                            
                                            return `
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-warning btn-sm btnEditar" 
                                                        data-id="${row.idespecialidad}"
                                                        data-nombre="${row.especialidad}"
                                                        data-precio="${row.precioatencion}"
                                                        data-bs-toggle="tooltip" title="Editar Precio">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm btnEliminar" 
                                                        data-id="${row.idespecialidad}"
                                                        data-nombre="${row.especialidad}"
                                                        data-bs-toggle="tooltip" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    ${btnEstado}
                                                </div>`;
                                        }
                                    }
                                ],
                                columnDefs: [
                                    { width: "8%", targets: 0 },
                                    { width: "40%", targets: 1 },
                                    { width: "15%", targets: 2 },
                                    { width: "12%", targets: 3 },
                                    { 
                                        width: "25%", 
                                        targets: 4,
                                        className: 'text-center' 
                                    }
                                ],
                                order: [[0, 'asc']] // Ordenar por ID por defecto
                            });

                            // Inicializar tooltips
                            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                                return new bootstrap.Tooltip(tooltipTriggerEl);
                            });

                            // Aplicar filtros adicionales
                            aplicarFiltrosAdicionales();

                            // Añadir event listeners a los filtros
                            $('#filtroNombre, #filtroPrecio, #filtroEstado').on('change keyup', function() {
                                aplicarFiltros();
                            });
                        } else {
                            console.error("Error en la respuesta:", response); // Depuración

                            $('#cuerpoTablaEspecialidades').html(`
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            ${response?.mensaje || 'Error al cargar las especialidades'}
                                        </div>
                                    </td>
                                </tr>
                            `);

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response?.mensaje || 'No se pudieron cargar las especialidades'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error AJAX:", status, error); // Depuración
                        console.error("Respuesta:", xhr.responseText); // Depuración

                        $('#cuerpoTablaEspecialidades').html(`
                            <tr>
                                <td colspan="5" class="text-center">
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
                const filtroPrecio = $('#filtroPrecio').val();
                const filtroEstado = $('#filtroEstado').val();

                // Limpiar filtros existentes
                $.fn.dataTable.ext.search.pop();

                // Filtro para nombre de especialidad
                if (filtroNombre) {
                    $.fn.dataTable.ext.search.push(
                        function(settings, data, dataIndex) {
                            const nombre = data[1].toLowerCase(); // Columna de nombre de especialidad
                            return nombre.includes(filtroNombre);
                        }
                    );
                }

                // Filtro para precio
                if (filtroPrecio) {
                    $.fn.dataTable.ext.search.push(
                        function(settings, data, dataIndex) {
                            const precio = parseFloat(data[2]); // Columna de precio
                            const filtroPrecioNum = parseFloat(filtroPrecio);
                            return precio === filtroPrecioNum;
                        }
                    );
                }
                
                // Filtro para estado
                if (filtroEstado) {
                    $.fn.dataTable.ext.search.push(
                        function(settings, data, dataIndex) {
                            const estado = $(data[3]).text().trim(); // Columna de estado (extrae texto del badge)
                            return estado.toLowerCase() === filtroEstado.toLowerCase();
                        }
                    );
                }

                // Aplicar los filtros a la tabla
                tablaEspecialidades.draw();

                // Limpiar filtros personalizados después de aplicarlos
                $.fn.dataTable.ext.search.pop();
                if (filtroNombre) {
                    $.fn.dataTable.ext.search.pop();
                }
                if (filtroPrecio) {
                    $.fn.dataTable.ext.search.pop();
                }
                if (filtroEstado) {
                    $.fn.dataTable.ext.search.pop();
                }
            }

            // Función para aplicar filtros adicionales
            function aplicarFiltrosAdicionales() {
                // Aplicar filtros iniciales si hay valores en los campos
                if ($('#filtroNombre').val() || $('#filtroPrecio').val() || $('#filtroEstado').val()) {
                    aplicarFiltros();
                }
            }

            // Cargar especialidades al iniciar
            cargarEspecialidades();

            // Evento para guardar nueva especialidad
            $('#btnGuardarEspecialidad').on('click', function() {
                const form = document.getElementById('formRegistrarEspecialidad');

                // Validar formulario
                if (!validarFormulario(form)) {
                    return;
                }

                // Recoger datos del formulario
                const especialidad = $('#especialidad').val();
                const precioatencion = $('#precioatencion').val();

                // Mostrar spinner de carga
                Swal.fire({
                    title: 'Procesando',
                    text: 'Registrando especialidad...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Enviar solicitud AJAX
                $.ajax({
                    url: '../../../controllers/especialidad.controller.php?op=registrar',
                    type: 'POST',
                    data: {
                        especialidad: especialidad,
                        precioatencion: precioatencion
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();

                        if (response.status) {
                            // Cerrar modal
                            $('#modalRegistrarEspecialidad').modal('hide');
                            
                            // Limpiar formulario
                            form.reset();
                            
                            // Recargar tabla
                            cargarEspecialidades();
                            
                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.mensaje
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.mensaje
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: 'No se pudo registrar la especialidad. Detalles: ' + error
                        });
                    }
                });
            });

            // Evento para abrir modal de edición de precio
            $(document).on('click', '.btnEditar', function() {
                const id = $(this).data('id');
                const nombre = $(this).data('nombre');
                const precio = $(this).data('precio');

                // Rellenar formulario
                $('#idespecialidad').val(id);
                $('#nombreEspecialidad').val(nombre);
                $('#nuevoPrecio').val(precio);

                // Abrir modal
                $('#modalEditarPrecio').modal('show');
            });

            // Evento para actualizar precio
            $('#btnActualizarPrecio').on('click', function() {
                const form = document.getElementById('formEditarPrecio');

                // Validar formulario
                if (!validarFormulario(form)) {
                    return;
                }

                // Recoger datos del formulario
                const idespecialidad = $('#idespecialidad').val();
                const precioatencion = $('#nuevoPrecio').val();

                // Mostrar spinner de carga
                Swal.fire({
                    title: 'Procesando',
                    text: 'Actualizando precio...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Enviar solicitud AJAX
                $.ajax({
                    url: '../../../controllers/especialidad.controller.php?op=actualizar_precio',
                    type: 'POST',
                    data: {
                        idespecialidad: idespecialidad,
                        precioatencion: precioatencion
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();

                        if (response.status) {
                            // Cerrar modal
                            $('#modalEditarPrecio').modal('hide');
                            
                            // Recargar tabla
                            cargarEspecialidades();
                            
                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.mensaje
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.mensaje
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: 'No se pudo actualizar el precio. Detalles: ' + error
                        });
                    }
                });
            });

            // Evento para abrir modal de confirmación de eliminación
            $(document).on('click', '.btnEliminar', function() {
                const id = $(this).data('id');
                const nombre = $(this).data('nombre');

                // Guardar id para uso posterior
                especialidadIdEliminar = id;
                
                // Mostrar nombre en el modal
                $('#especialidadEliminar').text(nombre);
                
                // Abrir modal
                $('#modalConfirmarEliminar').modal('show');
            });

            // Evento para confirmar eliminación
            $('#btnConfirmarEliminar').on('click', function() {
                if (!especialidadIdEliminar) {
                    return;
                }

                // Mostrar spinner de carga
                Swal.fire({
                    title: 'Procesando',
                    text: 'Eliminando especialidad...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Enviar solicitud AJAX
                $.ajax({
                    url: '../../../controllers/especialidad.controller.php?op=eliminar',
                    type: 'POST',
                    data: {
                        idespecialidad: especialidadIdEliminar
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        $('#modalConfirmarEliminar').modal('hide');

                        if (response.eliminado) {
                            // Recargar tabla
                            cargarEspecialidades();
                            
                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: response.mensaje
                            });
                        } else {
                            // Mostrar mensaje de error
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.mensaje || 'No se pudo eliminar la especialidad'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        $('#modalConfirmarEliminar').modal('hide');
                        
                        // Intentar parsear la respuesta en caso de error
                        let errorMsg = 'No se pudo eliminar la especialidad';
                        
                        try {
                            // Intentar parsear la respuesta como JSON
                            const respuesta = JSON.parse(xhr.responseText);
                            if (respuesta && respuesta.mensaje) {
                                errorMsg = respuesta.mensaje;
                            }
                        } catch (e) {
                            console.error("Error al parsear respuesta:", e);
                            errorMsg += '. Detalles: ' + error;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: errorMsg
                        });
                    }
                });
            });
            
            // Función para inactivar una especialidad
            function inactivarEspecialidad(idespecialidad) {
                // Mostrar spinner de carga
                Swal.fire({
                    title: 'Procesando',
                    text: 'Inactivando especialidad...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Enviar solicitud AJAX
                $.ajax({
                    url: '../../../controllers/especialidad.controller.php?op=cambiar_estado',
                    type: 'POST',
                    data: {
                        idespecialidad: idespecialidad,
                        estado: 'INACTIVO'
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        
                        if (response.status) {
                            // Recargar tabla
                            cargarEspecialidades();
                            
                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: 'Especialidad Inactivada',
                                text: 'La especialidad ha sido inactivada correctamente'
                            });
                        } else {
                            // Si la especialidad está en uso, mostrar mensaje específico
                            if (response.en_uso) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'No se puede inactivar',
                                    text: 'No se puede inactivar la especialidad porque está asignada a uno o más colaboradores.',
                                    footer: 'Debes reasignar a todos los colaboradores antes de inactivar esta especialidad.'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.mensaje || 'Error al inactivar la especialidad'
                                });
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: 'No se pudo inactivar la especialidad. Detalles: ' + error
                        });
                    }
                });
            }

            // Modal para cambiar estado de especialidad
            $(document).on('click', '.btnCambiarEstado', function() {
                const id = $(this).data('id');
                const nombre = $(this).data('nombre');
                const nuevoEstado = $(this).data('estado');
                
                const accion = nuevoEstado === 'ACTIVO' ? 'activar' : 'inactivar';
                const titulo = nuevoEstado === 'ACTIVO' ? 'Activar Especialidad' : 'Inactivar Especialidad';
                
                Swal.fire({
                    title: titulo,
                    text: `¿Está seguro que desea ${accion} la especialidad "${nombre}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: nuevoEstado === 'ACTIVO' ? '#28a745' : '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: nuevoEstado === 'ACTIVO' ? 'Activar' : 'Inactivar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        cambiarEstadoEspecialidad(id, nuevoEstado);
                    }
                });
            });
            
            // Función para cambiar el estado de una especialidad
            function cambiarEstadoEspecialidad(idespecialidad, estado) {
                // Mostrar spinner de carga
                Swal.fire({
                    title: 'Procesando',
                    text: `${estado === 'ACTIVO' ? 'Activando' : 'Inactivando'} especialidad...`,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Enviar solicitud AJAX
                $.ajax({
                    url: '../../../controllers/especialidad.controller.php?op=cambiar_estado',
                    type: 'POST',
                    data: {
                        idespecialidad: idespecialidad,
                        estado: estado
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        
                        if (response.status) {
                            // Recargar tabla
                            cargarEspecialidades();
                            
                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: 'Estado Actualizado',
                                text: response.mensaje || `La especialidad ha sido ${estado === 'ACTIVO' ? 'activada' : 'inactivada'} correctamente`
                            });
                        } else {
                            // Si la especialidad está en uso, mostrar mensaje específico
                            if (response.en_uso) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'No se puede inactivar',
                                    text: 'No se puede inactivar la especialidad porque está asignada a uno o más colaboradores.',
                                    footer: 'Debes reasignar a todos los colaboradores antes de inactivar esta especialidad.'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.mensaje || `Error al ${estado === 'ACTIVO' ? 'activar' : 'inactivar'} la especialidad`
                                });
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: `No se pudo ${estado === 'ACTIVO' ? 'activar' : 'inactivar'} la especialidad. Detalles: ` + error
                        });
                    }
                });
            }

            // Función para validar formularios
            function validarFormulario(form) {
                let valido = true;
                
                // Validar campos obligatorios
                $(form).find('[required]').each(function() {
                    if (!$(this).val()) {
                        $(this).addClass('is-invalid');
                        valido = false;
                    } else {
                        $(this).removeClass('is-invalid').addClass('is-valid');
                    }
                });

                // Validar precio
                const campoPrecio = $(form).find('[name="precioatencion"]');
                if (campoPrecio.length && campoPrecio.val()) {
                    const precio = parseFloat(campoPrecio.val());
                    if (isNaN(precio) || precio <= 0) {
                        campoPrecio.addClass('is-invalid');
                        valido = false;
                    } else {
                        campoPrecio.removeClass('is-invalid').addClass('is-valid');
                    }
                }

                // Mostrar alerta si no es válido
                if (!valido) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Campos Incompletos',
                        text: 'Por favor complete todos los campos requeridos correctamente.'
                    });
                }

                return valido;
            }

            // Evento para limpiar clases de validación al cambiar input
            $(document).on('input', '.form-control', function() {
                $(this).removeClass('is-invalid');
                if ($(this).val()) {
                    $(this).addClass('is-valid');
                } else {
                    $(this).removeClass('is-valid');
                }
            });

            // Limpiar formulario al cerrar modal
            $('#modalRegistrarEspecialidad').on('hidden.bs.modal', function() {
                const form = document.getElementById('formRegistrarEspecialidad');
                form.reset();
                $(form).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            });

            $('#modalEditarPrecio').on('hidden.bs.modal', function() {
                const form = document.getElementById('formEditarPrecio');
                $(form).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            });
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

        /* Estilos para los modales */
        .modal-header {
            padding: 12px 16px;
        }

        .modal-body {
            padding: 20px;
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