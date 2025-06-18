<?php /*RUTA: sistemaclinica/views/Paciente/ListarPaciente/listarPaciente.php*/ ?>
<?php
require_once '../../include/header.administrador.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Pacientes</title>
    <link rel="stylesheet" href="../../../css/listarPaciente.css">
    <link rel="stylesheet" href="../../../css/pacienteDetalles.css">
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
                        <h2 class="mb-0"><i class="fas fa-users me-2"></i>Listado de Pacientes</h2>
                        <a href="../RegistrarPaciente/registrarPaciente.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Registrar Nuevo Paciente
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
                                <label for="filtroGenero" class="form-label">Género</label>
                                <select id="filtroGenero" class="form-select">
                                    <option value="">Todos los Géneros</option>
                                    <option value="MASCULINO">Masculino</option>
                                    <option value="FEMENINO">Femenino</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtroFechaRegistro" class="form-label">Fecha de Registro</label>
                                <input type="date" id="filtroFechaRegistro" class="form-control" placeholder="Seleccione fecha">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tablaPacientes" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tipo Doc</th>
                                        <th>N° Documento</th>
                                        <th>Apellidos</th>
                                        <th>Nombres</th>
                                        <th>Fecha Nacimiento</th>
                                        <th>Género</th>
                                        <th>Teléfono</th>
                                        <th>Fecha Registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoTablaPacientes">
                                    <!-- Los datos se cargarán dinámicamente con JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para gestionar alergias -->
    <div class="modal fade" id="modalAlergias" tabindex="-1" aria-labelledby="modalAlergiasLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalAlergiasLabel"><i class="fas fa-allergies me-2"></i> Gestión de Alergias</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="contenidoAlergias">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando alergias del paciente...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar alergia -->
    <div class="modal fade" id="agregarAlergiaModal" tabindex="-1" aria-labelledby="agregarAlergiaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="agregarAlergiaModalLabel"><i class="fas fa-plus-circle me-2"></i> Agregar Alergia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAgregarAlergia">
                        <input type="hidden" name="operacion" value="registrar_paciente">
                        <input type="hidden" name="idpersona" id="idpersonaAlergia">

                        <div class="mb-3">
                            <label for="tipoalergia" class="form-label">Tipo de Alergia</label>
                            <select class="form-select" id="tipoalergia" name="tipoalergia" required>
                                <option value="">Seleccione...</option>
                                <option value="MEDICAMENTO">Medicamento</option>
                                <option value="ALIMENTO">Alimento</option>
                                <option value="AMBIENTE">Ambiental</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="alergia" class="form-label">Alergia</label>
                            <input type="text" class="form-control" id="alergia" name="alergia" required>
                        </div>
                        <div class="mb-3">
                            <label for="gravedad" class="form-label">Gravedad</label>
                            <select class="form-select" id="gravedad" name="gravedad" required>
                                <option value="">Seleccione...</option>
                                <option value="LEVE">Leve</option>
                                <option value="MODERADA">Moderada</option>
                                <option value="GRAVE">Grave</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarAlergia()">Agregar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para editar alergia -->
    <div class="modal fade" id="editarAlergiaModal" tabindex="-1" aria-labelledby="editarAlergiaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="editarAlergiaModalLabel"><i class="fas fa-edit me-2"></i> Editar Alergia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarAlergia">
                        <input type="hidden" id="editIdAlergia" name="idlistaalergia">
                        <div class="mb-3">
                            <label for="editTipoAlergia" class="form-label">Tipo de Alergia</label>
                            <select class="form-select" id="editTipoAlergia" name="tipoalergia" disabled>
                                <option value="">Seleccione...</option>
                                <option value="MEDICAMENTO">Medicamento</option>
                                <option value="ALIMENTO">Alimento</option>
                                <option value="AMBIENTE">Ambiental</option>
                                <option value="OTRO">Otro</option>
                            </select>
                            <small class="text-muted">El tipo de alergia no se puede modificar</small>
                        </div>
                        <div class="mb-3">
                            <label for="editAlergia" class="form-label">Alergia</label>
                            <input type="text" class="form-control" id="editAlergia" name="alergia" disabled>
                            <small class="text-muted">El nombre de la alergia no se puede modificar</small>
                        </div>
                        <div class="mb-3">
                            <label for="editGravedad" class="form-label">Gravedad</label>
                            <select class="form-select" id="editGravedad" name="gravedad" required>
                                <option value="">Seleccione...</option>
                                <option value="LEVE">Leve</option>
                                <option value="MODERADA">Moderada</option>
                                <option value="GRAVE">Grave</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" onclick="guardarEdicionAlergia()">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para seleccionar acción de edición -->
    <div class="modal fade" id="modalSeleccionEdicion" tabindex="-1" aria-labelledby="modalSeleccionEdicionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalSeleccionEdicionLabel">Seleccione opción de edición</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <input type="hidden" id="idPacienteSeleccionado">
                    <div class="d-grid gap-3">
                        <button id="btnInfoPersonal" class="btn btn-success btn-lg">
                            <i class="fas fa-user me-2"></i> Información Personal
                        </button>
                        <button id="btnAlergias" class="btn btn-danger btn-lg">
                            <i class="fas fa-allergies me-2"></i> Alergias
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar información personal -->
    <div class="modal fade" id="modalInfoPersonal" tabindex="-1" aria-labelledby="modalInfoPersonalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalInfoPersonalLabel"><i class="fas fa-user me-2"></i> Editar Información Personal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="contenidoInfoPersonal">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando información del paciente...</p>
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
            let tablaPacientes;

            // Función para cargar pacientes
            function cargarPacientes() {
                // Capturar los valores de los filtros
                const filtroNombre = $('#filtroNombre').val();
                const filtroDocumento = $('#filtroDocumento').val();
                const filtroGenero = $('#filtroGenero').val();
                const filtroFechaRegistro = $('#filtroFechaRegistro').val();

                // Mostrar un indicador de carga
                $('#cuerpoTablaPacientes').html(`
        <tr>
            <td colspan="10" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">Cargando listado de pacientes...</p>
            </td>
        </tr>
    `);

                // Construir la URL con los parámetros de filtro
                let url = '../../../controllers/paciente.controller.php?operacion=listar';

                if (filtroNombre) {
                    url += '&busqueda=' + encodeURIComponent(filtroNombre);
                }

                if (filtroGenero) {
                    url += '&genero=' + encodeURIComponent(filtroGenero);
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
                            if (tablaPacientes) {
                                tablaPacientes.destroy();
                            }

                            // Verificar si hay datos
                            if (!response.data && response.pacientes) {
                                // Ajuste para compatibilidad si la respuesta usa 'pacientes' en lugar de 'data'
                                response.data = response.pacientes;
                            }

                            // Verificar nuevamente si hay datos válidos
                            if (!response.data || response.data.length === 0) {
                                $('#cuerpoTablaPacientes').html(`
                        <tr>
                            <td colspan="10" class="text-center">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>No hay pacientes registrados
                                </div>
                            </td>
                        </tr>
                    `);
                                return;
                            }

                            // Inicializar la tabla
                            tablaPacientes = $('#tablaPacientes').DataTable({
                                responsive: true,
                                language: {
                                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                                },
                                data: response.data,
                                columns: [{
                                        data: 'idpaciente'
                                    },
                                    {
                                        data: 'tipodoc'
                                    },
                                    {
                                        data: 'nrodoc'
                                    },
                                    {
                                        data: 'apellidos'
                                    },
                                    {
                                        data: 'nombres'
                                    },
                                    {
                                        data: 'fechanacimiento',
                                        render: function(data) {
                                            if (!data) return 'Sin fecha';
                                            // Manejo correcto de la fecha para evitar desplazamiento
                                            const partesFecha = data.split('-');
                                            if (partesFecha.length === 3) {
                                                // Formato YYYY-MM-DD a DD/MM/YYYY
                                                return `${partesFecha[2]}/${partesFecha[1]}/${partesFecha[0]}`;
                                            }
                                            return data;
                                        }
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
                                        data: 'telefono',
                                        render: function(data) {
                                            return data || 'Sin teléfono';
                                        }
                                    },
                                    {
                                        data: 'fecharegistro',
                                        render: function(data) {
                                            if (!data) return 'Sin fecha';
                                            // Manejo correcto de la fecha para evitar desplazamiento
                                            const partesFecha = data.split('-');
                                            if (partesFecha.length === 3) {
                                                // Formato YYYY-MM-DD a DD/MM/YYYY
                                                return `${partesFecha[2]}/${partesFecha[1]}/${partesFecha[0]}`;
                                            }
                                            return data;
                                        }
                                    },
                                    {
                                        data: null,
                                        render: function(data, type, row) {
                                            return `
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-warning btn-sm btnEditar" 
                        data-id="${row.idpaciente}"
                        data-bs-toggle="tooltip" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm btnEliminar" 
                        data-id="${row.idpaciente}"
                        data-bs-toggle="tooltip" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm btnPDF" 
                        data-id="${row.idpaciente}"
                        data-bs-toggle="tooltip" title="Ver PDF">
                        <i class="fas fa-file-pdf"></i>
                    </button>
                </div>`;
                                        }
                                    }
                                ],
                                columnDefs: [{
                                        width: "5%",
                                        targets: 0
                                    },
                                    {
                                        width: "8%",
                                        targets: [1, 2]
                                    },
                                    {
                                        width: "10%",
                                        targets: [3, 4]
                                    },
                                    {
                                        width: "10%",
                                        targets: [5, 6, 7, 8]
                                    },
                                    {
                                        width: "15%",
                                        targets: 9,
                                        className: 'text-center'
                                    }
                                ],
                                order: [
                                    [0, 'asc']
                                ] // Ordenar por ID por defecto
                            });

                            // Inicializar tooltips
                            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                                return new bootstrap.Tooltip(tooltipTriggerEl);
                            });

                            // Aplicar filtros adicionales (documento, fecha)
                            aplicarFiltrosAdicionales();

                            // Añadir event listeners a los filtros
                            $('#filtroNombre, #filtroDocumento, #filtroGenero, #filtroFechaRegistro').on('change keyup', function() {
                                aplicarFiltros();
                            });
                        } else {
                            console.error("Error en la respuesta:", response); // Depuración

                            $('#cuerpoTablaPacientes').html(`
                    <tr>
                        <td colspan="10" class="text-center">
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                ${response?.mensaje || 'Error al cargar los pacientes'}
                            </div>
                        </td>
                    </tr>
                `);

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response?.mensaje || 'No se pudieron cargar los pacientes'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error AJAX:", status, error); // Depuración
                        console.error("Respuesta:", xhr.responseText); // Depuración

                        $('#cuerpoTablaPacientes').html(`
                <tr>
                    <td colspan="10" class="text-center">
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
                const filtroGenero = $('#filtroGenero').val();
                const filtroFechaRegistro = $('#filtroFechaRegistro').val();

                // Limpiar filtros existentes
                $.fn.dataTable.ext.search.pop();

                // Filtro personalizado para fechas de registro
                if (filtroFechaRegistro) {
                    $.fn.dataTable.ext.search.push(
                        function(settings, data, dataIndex) {
                            // Obtener la fecha de registro de la fila (columna 8 en la visualización)
                            const fechaRegistroRow = data[8];
                            if (!fechaRegistroRow || fechaRegistroRow === 'Sin fecha') return true;

                            // Convertir formato DD/MM/YYYY a YYYY-MM-DD para comparar
                            const partes = fechaRegistroRow.split('/');
                            if (partes.length !== 3) return true;

                            const fechaFormateada = `${partes[2]}-${partes[1]}-${partes[0]}`;

                            // Comparar con la fecha seleccionada
                            return fechaFormateada === filtroFechaRegistro;
                        }
                    );
                }

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
                    tablaPacientes.columns(2).search(filtroDocumento);
                } else {
                    tablaPacientes.columns(2).search('');
                }

                // Aplicar filtro de género - MODIFICACIÓN PRINCIPAL
                if (filtroGenero) {
                    // Formato correcto para filtrar por género
                    let valorBusqueda = '';
                    // Convertir valor del filtro a lo que muestra la tabla
                    switch (filtroGenero) {
                        case 'MASCULINO':
                            valorBusqueda = 'Masculino';
                            break;
                        case 'FEMENINO':
                            valorBusqueda = 'Femenino';
                            break;
                        case 'OTRO':
                            valorBusqueda = 'Otro';
                            break;
                        default:
                            valorBusqueda = '';
                    }
                    tablaPacientes.columns(6).search(valorBusqueda);
                } else {
                    tablaPacientes.columns(6).search('');
                }

                // Aplicar los filtros a la tabla
                tablaPacientes.draw();

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
                if ($('#filtroNombre').val() || $('#filtroDocumento').val() ||
                    $('#filtroGenero').val() || $('#filtroFechaRegistro').val()) {
                    aplicarFiltros();
                }
            }

            // Cargar pacientes al iniciar
            cargarPacientes();

            // Evento para Ver Detalles
            $(document).on('click', '.btnVerDetalles', function() {
                const idPaciente = $(this).data('id');

                // Mostrar el spinner de carga
                $('#contenidoDetallesPaciente').html(`
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando información del paciente...</p>
                </div>
            `);

                // Mostrar el modal
                const modalDetalles = new bootstrap.Modal(document.getElementById('modalDetallesPaciente'));
                modalDetalles.show();

                // Cargar el contenido desde el archivo externo (suponiendo que existe un endpoint similar)
                $.ajax({
                    url: `../VerDetalles/verDetallesPaciente.php?id=${idPaciente}`,
                    type: 'GET',
                    success: function(response) {
                        $('#contenidoDetallesPaciente').html(response);
                    },
                    error: function(xhr, status, error) {
                        $('#contenidoDetallesPaciente').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error al cargar los datos del paciente: ${xhr.status} ${xhr.statusText}
                        </div>
                    `);
                    }
                });
            });

            // Evento para imprimir los datos del paciente
            $('#btnImprimirPaciente').on('click', function() {
                const contenido = $('#contenidoDetallesPaciente').html();
                const ventanaImpresion = window.open('', '_blank');

                ventanaImpresion.document.write(`
                <html>
                <head>
                    <title>Datos del Paciente</title>
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
                    <link rel="stylesheet" href="../../../css/pacienteDetalles.css">
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
                            <h3>Datos del Paciente</h3>
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

            // Evento para Editar
            $(document).on('click', '.btnEditar', function() {
                const idPaciente = $(this).data('id');

                // Guardar el ID del paciente seleccionado
                $('#idPacienteSeleccionado').val(idPaciente);

                // Mostrar el modal de selección
                const modalSeleccion = new bootstrap.Modal(document.getElementById('modalSeleccionEdicion'));
                modalSeleccion.show();
            }); // Configurar botones de edición
            $('#btnInfoPersonal').on('click', function() {
                const idPaciente = $('#idPacienteSeleccionado').val();

                // Cerrar modal de selección
                bootstrap.Modal.getInstance(document.getElementById('modalSeleccionEdicion')).hide();

                // Mostrar el modal con spinner de carga
                const modalInfoPersonal = new bootstrap.Modal(document.getElementById('modalInfoPersonal'));
                modalInfoPersonal.show();

                // Cargar contenido desde el endpoint
                $.ajax({
                    url: `obtenerInfoPersonal.php?id=${idPaciente}`,
                    type: 'GET',
                    success: function(response) {
                        $('#contenidoInfoPersonal').html(response);
                        inicializarFormularioInfoPersonal();
                    },
                    error: function() {
                        $('#contenidoInfoPersonal').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error al cargar la información del paciente.
                        </div>
                    `);
                    }
                });
            });

            $('#btnAlergias').on('click', function() {
                const idPaciente = $('#idPacienteSeleccionado').val();

                // Cerrar modal de selección
                bootstrap.Modal.getInstance(document.getElementById('modalSeleccionEdicion')).hide();

                // Mostrar el modal con spinner de carga
                const modalAlergias = new bootstrap.Modal(document.getElementById('modalAlergias'));
                modalAlergias.show();

                // Cargar contenido desde el endpoint
                $.ajax({
                    url: `obtenerAlergias.php?id=${idPaciente}`,
                    type: 'GET',
                    success: function(response) {
                        $('#contenidoAlergias').html(response);
                        inicializarGestionAlergias();
                    },
                    error: function() {
                        $('#contenidoAlergias').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error al cargar las alergias del paciente.
                        </div>
                    `);
                    }
                });
            });

            // Evento para mostrar PDF
            $(document).on('click', '.btnPDF', function() {
                const idPaciente = $(this).data('id');
                mostrarPDF(idPaciente);
            });

            // Evento para Eliminar
            $(document).on('click', '.btnEliminar', function() {
                const idPaciente = $(this).data('id');
                eliminarPacienteConfirm(idPaciente);
            });

            // Función para mostrar el PDF en el modal
            function mostrarPDF(idPaciente) {
                // Configurar la URL del PDF
                const pdfUrl = `generarPDF.php?id=${idPaciente}`;

                // Actualizar el iframe y el botón de descarga
                $('#pdfFrame').attr('src', pdfUrl);
                $('#descargarPDFBtn').attr('href', pdfUrl + '&download=1');

                // Mostrar el modal
                const modalPDF = new bootstrap.Modal(document.getElementById('modalPDF'));
                modalPDF.show();
            }

            // Función para confirmar eliminación de paciente
            function eliminarPacienteConfirm(idPaciente) {
                Swal.fire({
                    title: "¿Está seguro?",
                    text: "¿Realmente desea eliminar este paciente y todas sus alergias asociadas?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Sí, eliminar",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        eliminarPaciente(idPaciente);
                    }
                });
            }

            function eliminarPaciente(idPaciente) {
                // Mostrar loading
                Swal.fire({
                    title: 'Procesando',
                    text: 'Eliminando paciente y sus datos asociados...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                console.log("Intentando eliminar paciente ID:", idPaciente);

                // Primera opción: Usar XMLHttpRequest para mejor control de los errores
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '../../../controllers/paciente.controller.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        console.log("Status de respuesta:", xhr.status);
                        console.log("Texto de respuesta:", xhr.responseText);

                        Swal.close();

                        let data;
                        try {
                            data = JSON.parse(xhr.responseText);
                            console.log("Datos parseados:", data);
                        } catch (e) {
                            console.error("Error al parsear respuesta:", e);
                            console.log("Respuesta no válida:", xhr.responseText);

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Respuesta del servidor no válida',
                                confirmButtonColor: '#3085d6'
                            });
                            return;
                        }

                        if (data && data.status === true) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: data.mensaje || 'Paciente eliminado correctamente',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                cargarPacientes();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: (data && data.mensaje) ? data.mensaje : 'No se pudo eliminar el paciente.',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    }
                };

                xhr.onerror = function() {
                    console.error("Error de red en la solicitud");
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor',
                        confirmButtonColor: '#3085d6'
                    });
                };

                const params = `operacion=eliminar&idpaciente=${idPaciente}`;
                console.log("Enviando parámetros:", params);
                xhr.send(params);
            }

            // Inicializar funciones de gestión de alergias
            function inicializarGestionAlergias() {
                // Inicializar tooltips en el contenido cargado dinámicamente
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            // Inicializar validación del formulario de información personal
            function inicializarFormularioInfoPersonal() {
                const form = document.getElementById('editarInformacionPersonalForm');
                if (!form) {
                    console.warn('No se encontró el formulario de edición de información personal');
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
                    if (!validarFormularioInfoPersonal()) return;

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
                        url: 'procesarEdicionPersonal.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function(data) {
                            Swal.close();

                            if (data.status) {
                                // Cerrar el modal
                                bootstrap.Modal.getInstance(document.getElementById('modalInfoPersonal')).hide();

                                // Mostrar mensaje de éxito
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Actualización exitosa',
                                    text: data.mensaje || 'Información actualizada correctamente',
                                    confirmButtonColor: '#3085d6'
                                }).then(() => {
                                    // Recargar la tabla
                                    cargarPacientes();
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

            function validarFormularioInfoPersonal() {
                // Verificamos que los campos requeridos tengan valor
                const form = document.getElementById('editarInformacionPersonalForm');
                if (!form) return false;

                const campos = ['apellidos', 'nombres', 'tipodoc', 'nrodoc', 'fechanacimiento', 'genero', 'telefono'];
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

                if (!valido) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Campos incompletos',
                        text: "Por favor complete todos los campos obligatorios.",
                        confirmButtonColor: '#3085d6'
                    });
                }

                return valido;
            }
            // Funciones globales para gestión de alergias
            window.abrirModalAgregarAlergia = function() {
                const form = document.getElementById('formAgregarAlergia');
                if (!form) {
                    console.error('Error: formAgregarAlergia no encontrado');
                    return;
                }

                // Reiniciar formulario
                form.reset();

                // Obtener el id del paciente
                const idPaciente = document.getElementById('idPacienteSeleccionado').value;

                // Obtener el idpersona del paciente
                $.ajax({
                    url: `../../../controllers/paciente.controller.php?operacion=obtener&idpaciente=${idPaciente}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.status && data.paciente && data.paciente.idpersona) {
                            // Establecer el idpersona en el formulario
                            document.getElementById('idpersonaAlergia').value = data.paciente.idpersona;

                            // Abrir modal
                            const agregarAlergiaModal = new bootstrap.Modal(document.getElementById('agregarAlergiaModal'));
                            agregarAlergiaModal.show();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'No se pudo obtener la información del paciente.',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo obtener la información del paciente.',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            }

            window.guardarAlergia = function() {
                const form = document.getElementById('formAgregarAlergia');

                // Validar campos requeridos
                if (!form.tipoalergia.value || !form.alergia.value || !form.gravedad.value) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Campos incompletos',
                        text: 'Por favor complete todos los campos requeridos.',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                // Mostrar loading
                Swal.fire({
                    title: 'Procesando',
                    text: 'Verificando alergia...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Obtener el ID del paciente desde el campo oculto
                const idPaciente = document.getElementById('idPacienteSeleccionado').value;

                // Obtener el ID de la persona desde el formulario
                const idPersona = document.getElementById('idpersonaAlergia').value;

                // Construir consulta para verificar si la alergia ya existe para este paciente
                const tipoAlergia = encodeURIComponent(form.tipoalergia.value);
                const nombreAlergia = encodeURIComponent(form.alergia.value);

                // Consultar si la alergia ya existe para este paciente
                $.ajax({
                    url: `../../../controllers/alergia.controller.php?operacion=verificar_existe_alergia&idpersona=${idPersona}&tipoalergia=${tipoAlergia}&alergia=${nombreAlergia}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        // Si la alergia ya existe para este paciente, mostrar mensaje de error
                        if (data.existe) {
                            Swal.close();
                            Swal.fire({
                                icon: 'error',
                                title: 'Alergia duplicada',
                                text: 'Esta alergia ya está registrada para este paciente.',
                                confirmButtonColor: '#3085d6'
                            });
                            return;
                        }

                        // Si no existe, continuar con el registro
                        Swal.update({
                            title: 'Procesando',
                            text: 'Registrando alergia...'
                        });

                        // Primero, registrar la alergia si no existe
                        $.ajax({
                            url: '../../../controllers/alergia.controller.php',
                            type: 'POST',
                            data: {
                                operacion: 'registrar',
                                tipoalergia: form.tipoalergia.value,
                                alergia: form.alergia.value
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (data.status) {
                                    // Luego, asociar la alergia al paciente
                                    $.ajax({
                                        url: '../../../controllers/alergia.controller.php',
                                        type: 'POST',
                                        data: {
                                            operacion: 'registrar_paciente',
                                            idpersona: idPersona,
                                            idalergia: data.idalergia,
                                            gravedad: form.gravedad.value
                                        },
                                        dataType: 'json',
                                        success: function(data) {
                                            Swal.close();

                                            if (data.status) {
                                                // Cerrar modal
                                                const modal = bootstrap.Modal.getInstance(document.getElementById('agregarAlergiaModal'));
                                                if (modal) {
                                                    modal.hide();
                                                }

                                                // Recargar contenido
                                                recargarContenidoAlergias(idPaciente);

                                                // Mostrar mensaje de éxito
                                                Swal.fire({
                                                    icon: 'success',
                                                    title: 'Alergia registrada',
                                                    text: 'La alergia se ha registrado correctamente.',
                                                    confirmButtonColor: '#3085d6'
                                                });
                                            } else {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Error',
                                                    text: data.mensaje || 'Error al asociar la alergia al paciente',
                                                    confirmButtonColor: '#3085d6'
                                                });
                                            }
                                        },
                                        error: function() {
                                            Swal.close();
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: 'No se pudo registrar la alergia del paciente.',
                                                confirmButtonColor: '#3085d6'
                                            });
                                        }
                                    });
                                } else {
                                    Swal.close();
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.mensaje || 'Error al registrar la alergia',
                                        confirmButtonColor: '#3085d6'
                                    });
                                }
                            },
                            error: function() {
                                Swal.close();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudo registrar la alergia.',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        });
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo verificar la alergia.',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            }

            window.editarAlergia = function(idAlergia, tipoAlergia, nombreAlergia, gravedad) {
                // Llenar el formulario con los datos actuales
                document.getElementById('editIdAlergia').value = idAlergia;
                document.getElementById('editTipoAlergia').value = tipoAlergia;
                document.getElementById('editAlergia').value = nombreAlergia;
                document.getElementById('editGravedad').value = gravedad;

                // Abrir modal
                const editarAlergiaModal = new bootstrap.Modal(document.getElementById('editarAlergiaModal'));
                editarAlergiaModal.show();
            }

            window.guardarEdicionAlergia = function() {
                const form = document.getElementById('formEditarAlergia');

                // Validar que todos los campos requeridos estén completos
                if (!form.gravedad.value) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Campos incompletos',
                        text: 'Por favor seleccione la gravedad de la alergia.',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                // Mostrar loading
                Swal.fire({
                    title: 'Procesando',
                    text: 'Actualizando alergia...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Enviar solicitud AJAX
                $.ajax({
                    url: '../../../controllers/alergia.controller.php',
                    type: 'POST',
                    data: {
                        operacion: 'actualizar_gravedad',
                        idlistaalergia: form.idlistaalergia.value,
                        gravedad: form.gravedad.value
                    },
                    dataType: 'json',
                    success: function(data) {
                        Swal.close();

                        if (data.status) {
                            // Cerrar modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('editarAlergiaModal'));
                            if (modal) {
                                modal.hide();
                            }

                            // Recargar contenido
                            const idPaciente = document.getElementById('idPacienteSeleccionado').value;
                            recargarContenidoAlergias(idPaciente);

                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: 'Actualización exitosa',
                                text: 'La alergia se ha actualizado correctamente.',
                                confirmButtonColor: '#3085d6'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.mensaje || 'No se pudo actualizar la alergia.',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo actualizar la alergia.',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            }

            window.eliminarAlergiaConfirm = function(idAlergia) {
                Swal.fire({
                    title: "¿Está seguro?",
                    text: "¿Realmente desea eliminar esta alergia?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Sí, eliminar",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        eliminarAlergia(idAlergia);
                    }
                });
            }

            window.eliminarAlergia = function(idAlergia) {
                // Obtener el ID del paciente seleccionado
                const idPaciente = document.getElementById('idPacienteSeleccionado').value;

                // Mostrar loading
                Swal.fire({
                    title: 'Procesando',
                    text: 'Eliminando alergia...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Enviar solicitud usando POST
                $.ajax({
                    url: '../../../controllers/alergia.controller.php',
                    type: 'POST',
                    data: {
                        operacion: 'eliminar_paciente',
                        idlistaalergia: idAlergia
                    },
                    dataType: 'json',
                    success: function(data) {
                        Swal.close();

                        if (data.status) {
                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: data.mensaje || 'Alergia eliminada correctamente',
                                confirmButtonColor: '#3085d6'
                            });

                            // Recargar contenido
                            recargarContenidoAlergias(idPaciente);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.mensaje || 'No se pudo eliminar la alergia.',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    },
                    error: function() {
                        Swal.close();

                        // Intentar con GET como método alternativo
                        $.ajax({
                            url: `../../../controllers/alergia.controller.php?operacion=eliminar_paciente&idlistaalergia=${idAlergia}`,
                            type: 'GET',
                            dataType: 'json',
                            success: function(data) {
                                if (data.status) {
                                    // Mostrar mensaje de éxito
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Eliminado',
                                        text: data.mensaje || 'Alergia eliminada correctamente',
                                        confirmButtonColor: '#3085d6'
                                    });

                                    // Recargar contenido
                                    recargarContenidoAlergias(idPaciente);
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.mensaje || 'No se pudo eliminar la alergia.',
                                        confirmButtonColor: '#3085d6'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudo eliminar la alergia. Por favor, inténtelo de nuevo.',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        });
                    }
                });
            }

            // Función para recargar la lista de alergias del paciente
            function recargarContenidoAlergias(idPaciente) {
                if (!idPaciente) {
                    idPaciente = document.getElementById('idPacienteSeleccionado').value;
                }

                $.ajax({
                    url: `obtenerAlergias.php?id=${idPaciente}`,
                    type: 'GET',
                    success: function(html) {
                        // Reemplazar todo el contenido del contenedor con el nuevo HTML
                        document.getElementById('contenidoAlergias').innerHTML = html;

                        // Reinicializar los tooltips después de actualizar el contenido
                        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl);
                        });
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo actualizar la lista de alergias.',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
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

        /* Estilos para los botones de selección */
        #btnInfoPersonal,
        #btnAlergias {
            padding: 15px;
            font-size: 18px;
            transition: all 0.3s;
        }

        #btnInfoPersonal:hover,
        #btnAlergias:hover {
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

        /* Badegss para género */
        .badge-genero-masculino {
            background-color: #0d6efd;
            color: white;
        }

        .badge-genero-femenino {
            background-color: #d63384;
            color: white;
        }

        .badge-genero-otro {
            background-color: #6c757d;
            color: white;
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
