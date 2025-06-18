<?php /*RUTA: sistemasclinica/views/Doctor/ListarDoctor/listarDoctor.php*/ ?>
<?php
require_once '../../include/header.administrador.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Doctores</title>
    <link rel="stylesheet" href="../../../css/listarDoctor.css">
    <link rel="stylesheet" href="../../../css/doctorDetalles.css">
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
                        <h2 class="mb-0"><i class="fas fa-user-md me-2"></i>Listado de Doctores</h2>
                        <a href="../RegistrarDoctor/registrarDoctor.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Registrar Nuevo Doctor
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Búsqueda directa por número de documento -->
                        <div class="row mb-4">
                            <div class="col-md-6 offset-md-3">
                                <div class="input-group">
                                    <input type="text" id="buscarNroDoc" class="form-control" placeholder="Ingrese número de documento para búsqueda rápida">
                                    <button class="btn btn-primary" type="button" id="btnBuscarNroDoc">
                                        <i class="fas fa-search me-1"></i> Buscar Doctor
                                    </button>
                                </div>
                                <div class="form-text text-center">Puede buscar directamente por número de documento</div>
                            </div>
                        </div>

                        <hr>
                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="filtroNombre" class="form-label">Nombre</label>
                                <input type="text" id="filtroNombre" class="form-control" placeholder="Buscar por nombre">
                            </div>
                            <div class="col-md-3">
                                <label for="filtroEspecialidad" class="form-label">Especialidad</label>
                                <select id="filtroEspecialidad" class="form-select">
                                    <option value="">Todas las Especialidades</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtroGenero" class="form-label">Género</label>
                                <select id="filtroGenero" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtroEstado" class="form-label">Estado</label>
                                <select id="filtroEstado" class="form-select">
                                    <option value="">Todos los Estados</option>
                                    <option value="ACTIVO">Activo</option>
                                    <option value="INACTIVO">Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tablaDoctores" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Tipo Doc</th>
                                        <th>N° Documento</th>
                                        <th>Nombres y Apellidos</th>
                                        <th>Especialidad</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Género</th>
                                        <th>Estado</th>
                                        <th>Cambiar Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoTablaDoctores">
                                    <!-- Los datos se cargarán dinámicamente con JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Detalles -->
    <div class="modal fade" id="modalDetallesDoctor" tabindex="-1" aria-labelledby="modalDetallesDoctorLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalDetallesDoctorLabel">
                        <i class="fas fa-user-md me-2"></i> Detalles del Doctor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenidoDetallesDoctor">
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando información del doctor...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-info" id="btnImprimirDoctor">
                        <i class="fas fa-print me-2"></i>Imprimir Datos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para seleccionar sección de edición -->
    <div class="modal fade" id="modalSeleccionEdicion" tabindex="-1" aria-labelledby="modalSeleccionEdicionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalSeleccionEdicionLabel">Seleccione sección a editar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <input type="hidden" id="nroDocSeleccionado">
                    <div class="d-grid gap-3">
                        <button id="btnInfoDoctor" class="btn btn-info btn-lg">
                            <i class="fas fa-user-md me-2"></i> Información del Doctor
                        </button>
                        <button id="btnDatosProfesionales" class="btn btn-success btn-lg">
                            <i class="fas fa-briefcase me-2"></i> Datos Profesionales
                        </button>
                        <!-- Botón de información de contrato eliminado -->
                        <button id="btnHorarioAtencion" class="btn btn-danger btn-lg">
                            <i class="fas fa-clock me-2"></i> Horario de Atención
                        </button>
                        <button id="btnCredenciales" class="btn btn-dark btn-lg">
                            <i class="fas fa-key me-2"></i> Credenciales de Acceso
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Información del Doctor -->
    <div class="modal fade" id="modalInfoDoctor" tabindex="-1" aria-labelledby="modalInfoDoctorLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalInfoDoctorLabel"><i class="fas fa-user-md me-2"></i> Información del Doctor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="contenidoInfoDoctor">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando información del doctor...</p>
                        </div>
                    </div>
                </div>
                <!-- Los botones de este modal se incluyen dentro del formulario cargado dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Modal para Datos Profesionales -->
    <div class="modal fade" id="modalDatosProfesionales" tabindex="-1" aria-labelledby="modalDatosProfesionalesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalDatosProfesionalesLabel"><i class="fas fa-briefcase me-2"></i> Datos Profesionales</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="contenidoDatosProfesionales">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando datos profesionales...</p>
                        </div>
                    </div>
                </div>
                <!-- Los botones de este modal se incluyen dentro del formulario cargado dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Modal para Información de Contrato -->
    <div class="modal fade" id="modalInfoContrato" tabindex="-1" aria-labelledby="modalInfoContratoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="modalInfoContratoLabel"><i class="fas fa-file-contract me-2"></i> Información de Contrato</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="contenidoInfoContrato">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando información de contrato...</p>
                        </div>
                    </div>
                </div>
                <!-- Los botones de este modal se incluyen dentro del formulario cargado dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Modal para Horario de Atención -->
    <div class="modal fade" id="modalHorarioAtencion" tabindex="-1" aria-labelledby="modalHorarioAtencionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalHorarioAtencionLabel"><i class="fas fa-clock me-2"></i> Horario de Atención</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="contenidoHorarioAtencion">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando horarios de atención...</p>
                        </div>
                    </div>
                </div>
                <!-- Los botones de este modal se incluyen dentro del formulario cargado dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Modal para Credenciales -->
    <div class="modal fade" id="modalCredenciales" tabindex="-1" aria-labelledby="modalCredencialesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalCredencialesLabel"><i class="fas fa-key me-2"></i> Credenciales de Acceso</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="contenidoCredenciales">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando credenciales de acceso...</p>
                        </div>
                    </div>
                </div>
                <!-- Los botones de este modal se incluyen dentro del formulario cargado dinámicamente -->
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
            let tablaDoctores;

            // Función para cargar especialidades en el filtro
            function cargarEspecialidades() {
                $.ajax({
                    url: '../../../controllers/especialidad.controller.php?op=listar',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            const selectEspecialidad = $('#filtroEspecialidad');
                            selectEspecialidad.empty();
                            selectEspecialidad.append('<option value="">Todas las Especialidades</option>');

                            response.data.forEach(especialidad => {
                                selectEspecialidad.append(`
                                <option value="${especialidad.especialidad}">
                                    ${especialidad.especialidad}
                                </option>
                            `);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.mensaje || 'No se pudieron cargar las especialidades'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: 'No se pudo conectar con el servidor para cargar especialidades'
                        });
                    }
                });
            }

            // Función para cargar doctores
            function cargarDoctores() {
                $('#cuerpoTablaDoctores').html(`
                <tr>
                    <td colspan="11" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando listado de doctores...</p>
                    </td>
                </tr>
            `);

                $.ajax({
                    url: '../../../controllers/doctor.controller.php?op=listar',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            if (tablaDoctores) {
                                tablaDoctores.destroy();
                            }

                            if (!response.data || response.data.length === 0) {
                                $('#cuerpoTablaDoctores').html(`
                                <tr>
                                    <td colspan="11" class="text-center">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>No hay doctores registrados
                                        </div>
                                    </td>
                                </tr>
                            `);
                                return;
                            }

                            tablaDoctores = $('#tablaDoctores').DataTable({
                                responsive: true,
                                language: {
                                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                                },
                                data: response.data,
                                columns: [{
                                        data: null,
                                        render: function(data, type, row, meta) {
                                            return meta.row + 1;
                                        }
                                    },
                                    {
                                        data: 'tipodoc'
                                    },
                                    {
                                        data: 'nrodoc'
                                    },
                                    {
                                        data: null,
                                        render: function(data, type, row) {
                                            return `${row.nombres} ${row.apellidos}`;
                                        }
                                    },
                                    {
                                        data: 'especialidad'
                                    },
                                    {
                                        data: 'email',
                                        render: function(data) {
                                            return data || 'Sin correo electrónico';
                                        }
                                    },
                                    {
                                        data: 'telefono'
                                    },
                                    {
                                        data: 'genero',
                                        render: function(data) {
                                            if (data === 'M') return 'Masculino';
                                            if (data === 'F') return 'Femenino';
                                            return data || 'No especificado';
                                        }
                                    },
                                    {
                                        data: 'estado',
                                        render: function(data) {
                                            return data === 'ACTIVO' ?
                                                '<span class="badge bg-success">Activo</span>' :
                                                '<span class="badge bg-danger">Inactivo</span>';
                                        }
                                    },
                                    {
                                        data: null,
                                        render: function(data, type, row) {
                                            const isActive = row.estado === 'ACTIVO';
                                            const buttonClass = isActive ? 'btn-danger' : 'btn-success';
                                            const buttonText = isActive ? 'Desactivar' : 'Activar';
                                            const buttonIcon = isActive ? 'fa-toggle-off' : 'fa-toggle-on';

                                            return `
                                        <button type="button" class="btn ${buttonClass} btn-sm btnCambiarEstado" 
                                            data-nrodoc="${row.nrodoc}" 
                                            data-estado="${row.estado}">
                                            <i class="fas ${buttonIcon}"></i> ${buttonText}
                                        </button>`;
                                        }
                                    },
                                    {
                                        data: null,
                                        render: function(data, type, row) {
                                            return `
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-info btn-sm btnVerDetalles"
                                                data-nrodoc="${row.nrodoc}" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning btn-sm btnEditar" 
                                                data-nrodoc="${row.nrodoc}" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm btnEliminar" 
                                                data-nrodoc="${row.nrodoc}" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>`;
                                        }
                                    }
                                ],
                                columnDefs: [{
                                        width: "4%",
                                        targets: 0
                                    },
                                    {
                                        width: "7%",
                                        targets: [1, 2]
                                    },
                                    {
                                        width: "13%",
                                        targets: 3
                                    },
                                    {
                                        width: "10%",
                                        targets: 4
                                    },
                                    {
                                        width: "10%",
                                        targets: 5
                                    },
                                    {
                                        width: "7%",
                                        targets: 6
                                    },
                                    {
                                        width: "7%",
                                        targets: 7
                                    },
                                    {
                                        width: "7%",
                                        targets: 8
                                    },
                                    {
                                        width: "10%",
                                        targets: 9
                                    },
                                    {
                                        width: "10%",
                                        targets: 10
                                    }
                                ]
                            });

                            $('[data-bs-toggle="tooltip"], [title]').tooltip();

                            aplicarFiltros();
                        } else {
                            $('#cuerpoTablaDoctores').html(`
                            <tr>
                                <td colspan="11" class="text-center">
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle me-2"></i>${response.mensaje || 'Error al cargar los doctores'}
                                    </div>
                                </td>
                            </tr>
                        `);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.mensaje || 'No se pudieron cargar los doctores'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#cuerpoTablaDoctores').html(`
                        <tr>
                            <td colspan="11" class="text-center">
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Error de conexión: ${status} - ${error}
                                </div>
                            </td>
                        </tr>
                    `);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: 'No se pudo conectar con el servidor'
                        });
                    }
                });
            }

            // Función para buscar doctor por número de documento
            function buscarDoctorPorDocumento() {
                const nroDoc = $('#buscarNroDoc').val().trim();

                if (!nroDoc) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campo vacío',
                        text: 'Por favor, ingrese un número de documento para buscar'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Buscando...',
                    text: 'Buscando doctor por número de documento',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '../../../controllers/doctor.controller.php?op=buscar',
                    type: 'POST',
                    data: {
                        nrodoc: nroDoc
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();

                        if (response.status && response.data) {
                            if (tablaDoctores) {
                                tablaDoctores.search('').columns().search('').draw();
                                tablaDoctores.column(2).search('^' + nroDoc + '$', true, false).draw();

                                if (tablaDoctores.rows({
                                        filter: 'applied'
                                    }).count() === 0) {
                                    tablaDoctores.search(nroDoc).draw();
                                }
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Doctor encontrado',
                                text: 'Se ha encontrado el doctor con documento ' + nroDoc
                            });

                            setTimeout(function() {
                                $('.dataTable tbody tr').each(function() {
                                    const cellText = $(this).find('td:eq(2)').text();
                                    if (cellText === nroDoc) {
                                        $(this).addClass('highlight-row');
                                        $(this).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
                                    }
                                });
                            }, 500);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Doctor no encontrado',
                                text: 'No se encontró ningún doctor con el número de documento: ' + nroDoc
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: 'No se pudo conectar con el servidor para realizar la búsqueda. Por favor, intente nuevamente.'
                        });
                    }
                });
            }

            // Función para aplicar filtros
            function aplicarFiltros() {
                if (!tablaDoctores) return;

                const filtroNombre = $('#filtroNombre').val().toLowerCase();
                const filtroEspecialidad = $('#filtroEspecialidad').val();
                const filtroGenero = $('#filtroGenero').val();
                const filtroEstado = $('#filtroEstado').val();

                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        const nombreCompleto = data[3].toLowerCase();
                        if (filtroNombre && !nombreCompleto.includes(filtroNombre)) {
                            return false;
                        }
                        return true;
                    }
                );

                tablaDoctores.columns(4).search(filtroEspecialidad ? filtroEspecialidad : '');

                if (filtroGenero) {
                    tablaDoctores.columns(7).search(filtroGenero === 'M' ? 'Masculino' : (filtroGenero === 'F' ? 'Femenino' : 'Otro'));
                } else {
                    tablaDoctores.columns(7).search('');
                }

                tablaDoctores.columns(8).search(filtroEstado ? filtroEstado : '');

                tablaDoctores.draw();

                // Limpiar filtro personalizado después de aplicarlo
                $.fn.dataTable.ext.search.pop();
            }

            // Botón para búsqueda directa por documento
            $('#btnBuscarNroDoc').on('click', function() {
                buscarDoctorPorDocumento();
            });

            // Tecla Enter en el campo de búsqueda
            $('#buscarNroDoc').on('keypress', function(e) {
                if (e.which === 13) { // Tecla Enter
                    e.preventDefault();
                    buscarDoctorPorDocumento();
                }
            });

            // Eventos para los filtros
            $('#filtroNombre, #filtroEspecialidad, #filtroGenero, #filtroEstado').on('change keyup', function() {
                aplicarFiltros();
            });

            // Cargar especialidades
            cargarEspecialidades();

            // Cargar doctores al iniciar
            cargarDoctores();

            // Mejora para la gestión de modales y los botones de cierre
            $(document).on('click', '[data-bs-dismiss="modal"]', function() {
                // Obtener el ID del modal que se debe cerrar
                const modalId = $(this).closest('.modal').attr('id');
                if (modalId) {
                    // Cerrar el modal usando Bootstrap
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById(modalId));
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }
            });

            // Asegurar que el botón cancelar cierre el modal correctamente
            $(document).on('click', '#btnCancelar', function(e) {
                e.preventDefault();
                // Cerrar el modal actual
                const modalEl = $(this).closest('.modal')[0];
                if (modalEl) {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }
            });

            // ===== MANEJADOR ÚNICO PARA FORMULARIOS QUE NO SON DE CREDENCIALES =====
            // CORREGIDO: Excluir correctamente el formulario de credenciales
            $(document).on('submit', 'form:not(#editarCredencialesFormDoctor)', function(e) {
                e.preventDefault();

                // Verificar que no sea el formulario de credenciales
                const formId = $(this).attr('id');
                if (formId === 'editarCredencialesFormDoctor') {
                    return; // No procesar este formulario aquí
                }

                // Mostrar indicador de carga
                Swal.fire({
                    title: 'Guardando...',
                    text: 'Por favor espere mientras se guardan los cambios',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Crear un objeto FormData con los datos del formulario
                const formData = new FormData(this);

                // Determinar la URL adecuada según el tipo de formulario
                let url = '../../../controllers/doctor.controller.php?op=actualizar';

                if (formId === 'formEditarDatosProfesionales') {
                    url = '../../../controllers/doctor.controller.php?op=actualizar';
                } else if (formId === 'formEditarInfoContrato') {
                    url = '../../../controllers/contrato.controller.php?op=registrar';
                } else if (formId === 'formHorarios' || (formId && formId.includes('Horario'))) {
                    url = '../../../controllers/horario.controller.php?op=registrar';
                } else if (formId === 'editarInformacionPersonalForm') {
                    url = '../../../controllers/doctor.controller.php?op=actualizar';
                }

                // Realizar la solicitud AJAX
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();

                        if (response.status) {
                            // Cerrar todos los modales activos
                            $('.modal').modal('hide');

                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.mensaje || 'Datos guardados correctamente'
                            }).then(() => {
                                // Recargar la tabla para mostrar los cambios
                                cargarDoctores();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.mensaje || 'No se pudieron guardar los cambios'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error en la solicitud:", xhr.responseText);

                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo conectar con el servidor. Por favor, intente nuevamente.'
                        });
                    }
                });
            });

            // Script para el manejo del formulario de contrato
            document.addEventListener('DOMContentLoaded', function() {
                // Verificar si estamos en la página de edición de contrato
                const formEditarInfoContrato = document.getElementById('formEditarInfoContrato');

                if (formEditarInfoContrato) {
                    // Obtener los elementos del formulario de estado
                    const estadoActivo = document.getElementById('estadoActivo');
                    const estadoInactivo = document.getElementById('estadoInactivo');

                    // Agregar advertencia si se cambia el estado
                    const estadoInputs = document.querySelectorAll('input[name="estado"]');
                    let estadoInicial = estadoActivo.checked ? 'ACTIVO' : 'INACTIVO';

                    estadoInputs.forEach(input => {
                        input.addEventListener('change', function() {
                            const nuevoEstado = this.value;

                            if (nuevoEstado !== estadoInicial) {
                                const alertaEstado = document.getElementById('alertaCambioEstado');

                                if (!alertaEstado) {
                                    const alerta = document.createElement('div');
                                    alerta.id = 'alertaCambioEstado';
                                    alerta.className = 'alert alert-warning mt-2';
                                    alerta.innerHTML = `
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>¡Atención!</strong> Cambiar el estado del contrato a ${nuevoEstado} también 
                            actualizará el estado general del doctor. Esto afectará su capacidad de atender pacientes.
                        `;

                                    // Insertar la alerta después del grupo de radio buttons
                                    estadoInactivo.closest('.row').appendChild(alerta);
                                }
                            } else {
                                // Si volvemos al estado inicial, quitar la alerta
                                const alertaEstado = document.getElementById('alertaCambioEstado');
                                if (alertaEstado) {
                                    alertaEstado.remove();
                                }
                            }
                        });
                    });

                    // Manejar el envío del formulario
                    formEditarInfoContrato.addEventListener('submit', function(e) {
                        e.preventDefault();

                        // Verificar cambio de estado para mostrar confirmación adicional
                        const nuevoEstado = document.querySelector('input[name="estado"]:checked').value;

                        if (nuevoEstado !== estadoInicial) {
                            Swal.fire({
                                title: '¿Cambiar estado?',
                                text: `Esta acción cambiará el estado del contrato y del doctor a ${nuevoEstado}. ${nuevoEstado === 'INACTIVO' ? 'El doctor no podrá atender pacientes.' : ''}`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Sí, cambiar',
                                cancelButtonText: 'Cancelar'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    enviarFormulario();
                                }
                            });
                        } else {
                            enviarFormulario();
                        }

                        function enviarFormulario() {
                            // Mostrar loader
                            Swal.fire({
                                title: 'Guardando...',
                                text: 'Por favor espere mientras se actualizan los datos del contrato.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            // Preparar datos del formulario
                            const formData = new FormData(formEditarInfoContrato);

                            // Si es contrato indefinido, asegurar que fechafin está vacío
                            if (document.getElementById('tipocontrato').value === 'INDEFINIDO') {
                                formData.set('fechafin', '');
                            }

                            // Enviar formulario a través de AJAX
                            fetch('../../../controllers/contrato.controller.php?op=registrar', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    Swal.close();

                                    if (data.status) {
                                        // Cerrar modal
                                        if (window.parent && window.parent.bootstrap) {
                                            const modal = window.parent.bootstrap.Modal.getInstance(window.parent.document.getElementById('modalInfoContrato'));
                                            if (modal) {
                                                modal.hide();
                                            }
                                        }

                                        // Mostrar mensaje de éxito
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Contrato actualizado',
                                            text: data.mensaje
                                        }).then(() => {
                                            // Recargar datos
                                            if (window.parent && typeof window.parent.cargarDoctores === 'function') {
                                                window.parent.cargarDoctores();
                                            } else {
                                                window.parent.location.reload();
                                            }
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: data.mensaje || 'No se pudieron guardar los cambios en el contrato.'
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    Swal.close();
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error de conexión',
                                        text: 'No se pudo conectar con el servidor: ' + error.message
                                    });
                                });
                        }
                    });
                }
            });

            // Mejorar la carga del contenido del modal de credenciales
            $('#btnCredenciales').on('click', function() {
                const nroDoc = $('#nroDocSeleccionado').val();

                // Cerrar modal de selección
                const modalSeleccion = bootstrap.Modal.getInstance(document.getElementById('modalSeleccionEdicion'));
                if (modalSeleccion) modalSeleccion.hide();

                // Mostrar indicador de carga en el contenido
                $('#contenidoCredenciales').html(`
        <div class="text-center p-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando credenciales de acceso...</p>
        </div>
    `);

                // Mostrar modal de credenciales
                const modalCredenciales = new bootstrap.Modal(document.getElementById('modalCredenciales'));
                modalCredenciales.show();

                // Cargar contenido desde el endpoint
                $.ajax({
                    url: `editarCredenciales.php?nrodoc=${nroDoc}`,
                    type: 'GET',
                    success: function(response) {
                        $('#contenidoCredenciales').html(response);
                    },
                    error: function(xhr, status, error) {
                        $('#contenidoCredenciales').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error al cargar las credenciales: ${error}
                </div>
            `);
                    }
                });
            });

            // Evento para Cambiar Estado del doctor desde la tabla principal
            $(document).on('click', '.btnCambiarEstado', function() {
                const nroDoc = $(this).data('nrodoc');
                const estadoActual = $(this).data('estado');
                const nuevoEstado = estadoActual === 'ACTIVO' ? 'Desactivar' : 'Activar';

                Swal.fire({
                    title: `¿${nuevoEstado} doctor?`,
                    text: `Esta acción cambiará el estado del doctor a ${estadoActual === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO'} y actualizará también sus contratos.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: `Sí, ${nuevoEstado.toLowerCase()}`,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Procesando',
                            text: 'Cambiando estado del doctor y sus contratos...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            willOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: '../../../controllers/doctor.controller.php?op=cambiar_estado',
                            type: 'POST',
                            data: {
                                nrodoc: nroDoc
                            },
                            dataType: 'json',
                            success: function(response) {
                                Swal.close();

                                if (response.status) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Éxito',
                                        text: response.mensaje || 'Estado del doctor y sus contratos actualizados correctamente'
                                    });
                                    cargarDoctores();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.mensaje || 'No se pudo actualizar el estado del doctor'
                                    });
                                }
                            },
                            error: function() {
                                Swal.close();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error de Conexión',
                                    text: 'No se pudo conectar con el servidor'
                                });
                            }
                        });
                    }
                });
            });

            // Evento para Ver Detalles
            $(document).on('click', '.btnVerDetalles', function() {
                const nroDoc = $(this).data('nrodoc');

                $('#contenidoDetallesDoctor').html(`
        <div class="text-center p-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando información del doctor...</p>
        </div>
    `);

                const modalDetalles = new bootstrap.Modal(document.getElementById('modalDetallesDoctor'));
                modalDetalles.show();

                $.ajax({
                    url: `VerDetalles.php?nrodoc=${nroDoc}`,
                    type: 'GET',
                    success: function(response) {
                        $('#contenidoDetallesDoctor').html(response);
                    },
                    error: function(xhr, status, error) {
                        $('#contenidoDetallesDoctor').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error al cargar los datos del doctor: ${xhr.status} ${xhr.statusText}
                </div>
            `);
                    }
                });
            });


            // Evento para Editar
            $(document).on('click', '.btnEditar', function() {
                const nroDoc = $(this).data('nrodoc');

                // Guardar el número de documento seleccionado
                $('#nroDocSeleccionado').val(nroDoc);

                // Mostrar el modal de selección
                const modalSeleccion = new bootstrap.Modal(document.getElementById('modalSeleccionEdicion'));
                modalSeleccion.show();
            });

            // Configurar botones de edición
            $('#btnInfoDoctor').on('click', function() {
                const nroDoc = $('#nroDocSeleccionado').val();

                // Cerrar modal de selección
                const modalSeleccion = bootstrap.Modal.getInstance(document.getElementById('modalSeleccionEdicion'));
                if (modalSeleccion) modalSeleccion.hide();

                // Mostrar modal de información del doctor
                const modalInfoDoctor = new bootstrap.Modal(document.getElementById('modalInfoDoctor'));
                modalInfoDoctor.show();

                // Cargar contenido desde el endpoint
                $.ajax({
                    url: `editarInfoDoctor.php?nrodoc=${nroDoc}`,
                    type: 'GET',
                    success: function(response) {
                        $('#contenidoInfoDoctor').html(response);
                    },
                    error: function(xhr, status, error) {
                        $('#contenidoInfoDoctor').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error al cargar la información del doctor: ${error}
                </div>
            `);
                    }
                });
            });

            $('#btnDatosProfesionales').on('click', function() {
                const nroDoc = $('#nroDocSeleccionado').val();

                // Cerrar modal de selección
                const modalSeleccion = bootstrap.Modal.getInstance(document.getElementById('modalSeleccionEdicion'));
                if (modalSeleccion) modalSeleccion.hide();

                // Mostrar modal de datos profesionales
                const modalDatosProfesionales = new bootstrap.Modal(document.getElementById('modalDatosProfesionales'));
                modalDatosProfesionales.show();

                // Cargar contenido desde el endpoint
                $.ajax({
                    url: `editarDatosProfesionales.php?nrodoc=${nroDoc}`,
                    type: 'GET',
                    success: function(response) {
                        $('#contenidoDatosProfesionales').html(response);
                    },
                    error: function(xhr, status, error) {
                        $('#contenidoDatosProfesionales').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error al cargar los datos profesionales: ${error}
                </div>
            `);
                    }
                });
            });

            $('#btnInfoContrato').on('click', function() {
                const nroDoc = $('#nroDocSeleccionado').val();

                // Cerrar modal de selección
                const modalSeleccion = bootstrap.Modal.getInstance(document.getElementById('modalSeleccionEdicion'));
                if (modalSeleccion) modalSeleccion.hide();

                // Mostrar modal de información de contrato
                const modalInfoContrato = new bootstrap.Modal(document.getElementById('modalInfoContrato'));
                modalInfoContrato.show();

                // Cargar contenido desde el endpoint
                $.ajax({
                    url: `editarInfoContrato.php?nrodoc=${nroDoc}`,
                    type: 'GET',
                    success: function(response) {
                        $('#contenidoInfoContrato').html(response);
                    },
                    error: function(xhr, status, error) {
                        $('#contenidoInfoContrato').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error al cargar la información de contrato: ${error}
                </div>
            `);
                    }
                });
            });

            $('#btnHorarioAtencion').on('click', function() {
                const nroDoc = $('#nroDocSeleccionado').val();

                // Cerrar modal de selección
                const modalSeleccion = bootstrap.Modal.getInstance(document.getElementById('modalSeleccionEdicion'));
                if (modalSeleccion) modalSeleccion.hide();

                // Mostrar modal de horario de atención
                const modalHorarioAtencion = new bootstrap.Modal(document.getElementById('modalHorarioAtencion'));
                modalHorarioAtencion.show();

                // Cargar contenido desde el endpoint
                $.ajax({
                    url: `editarHorarioAtencion.php?nrodoc=${nroDoc}`,
                    type: 'GET',
                    success: function(response) {
                        $('#contenidoHorarioAtencion').html(response);
                    },
                    error: function(xhr, status, error) {
                        $('#contenidoHorarioAtencion').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error al cargar los horarios de atención: ${error}
                </div>
            `);
                    }
                });
            });

            // Botón para imprimir los datos del doctor
            $('#btnImprimirDoctor').on('click', function() {
                const contenido = $('#contenidoDetallesDoctor').html();
                const ventanaImpresion = window.open('', '_blank');

                ventanaImpresion.document.write(`
        <html>
        <head>
            <title>Datos del Doctor</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
            <link rel="stylesheet" href="../../../css/doctorDetalles.css">
            <style>
                body { padding: 20px; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Datos del Doctor</h3>
                    <div class="text-end no-print">
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="fas fa-print me-2"></i>Imprimir
                        </button>
                    </div>
                </div>
                <hr>
                ${contenido}
            </div>
        </body>
        </html>
    `);

                ventanaImpresion.document.close();
            });

            // Evento para Eliminar
            $(document).on('click', '.btnEliminar', function() {
                const nroDoc = $(this).data('nrodoc');

                Swal.fire({
                    title: '¿Está seguro de que desea eliminar este doctor?',
                    text: "Esta acción no se puede deshacer.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Procesando',
                            text: 'Eliminando doctor...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            willOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: '../../../controllers/doctor.controller.php?op=eliminar&nrodoc=' + nroDoc,
                            type: 'GET',
                            dataType: 'json',
                            success: function(response) {
                                Swal.close();

                                if (response.status) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Éxito',
                                        text: response.mensaje || 'Doctor eliminado correctamente'
                                    });
                                    cargarDoctores();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.mensaje || 'No se pudo eliminar el doctor'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.close();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error de Conexión',
                                    text: 'No se pudo conectar con el servidor. Detalles: ' + error
                                });
                            }
                        });
                    }
                });
            });

            // Hacer disponible la función cargarDoctores globalmente para que pueda ser llamada desde los iframes
            window.cargarDoctores = cargarDoctores;
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

        /* Badge de estado */
        .badge {
            padding: 0.5em 0.8em;
            font-weight: 500;
            font-size: 85%;
        }

        /* Botón de cambio de estado */
        .btnCambiarEstado {
            white-space: nowrap;
            width: auto !important;
            height: auto !important;
            padding: 0.25rem 0.5rem !important;
        }

        /* Estilos para los modales */
        .modal-lg {
            max-width: 800px;
        }

        .modal-xl {
            max-width: 1140px;
        }

        .modal-header {
            padding: 12px 16px;
        }

        .modal-body {
            padding: 20px;
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

        /* Estilos para los botones de selección */
        #btnInfoDoctor,
        #btnDatosProfesionales,
        #btnInfoContrato,
        #btnHorarioAtencion,
        #btnCredenciales {
            padding: 15px;
            font-size: 18px;
            transition: all 0.3s;
        }

        #btnInfoDoctor:hover,
        #btnDatosProfesionales:hover,
        #btnInfoContrato:hover,
        #btnHorarioAtencion:hover,
        #btnCredenciales:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Resaltado para búsqueda */
        .highlight-row {
            background-color: rgba(255, 243, 205, 0.7) !important;
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
