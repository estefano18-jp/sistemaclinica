<?php /*RUTA: sistemasclinica/views/MovimientoCaja/movimientoCaja.php*/ ?>
<?php
require_once '../include/header.administrador.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Arqueos de Caja</title>
    <link rel="stylesheet" href="../../../css/listarMovimientoCaja.css">
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
                        <h2 class="mb-0"><i class="fas fa-cash-register me-2"></i>Listado de Arqueos de Caja</h2>
                        <div>
                            <a href="../ArqueoCaja/arqueoCaja.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Nuevo Arqueo
                            </a>
                            <button class="btn btn-info btn-sm" id="btnActualizar">
                                <i class="fas fa-sync"></i> Actualizar
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="filtroFechaDesde" class="form-label">Fecha Desde</label>
                                <input type="date" id="filtroFechaDesde" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="filtroFechaHasta" class="form-label">Fecha Hasta</label>
                                <input type="date" id="filtroFechaHasta" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="filtroEstado" class="form-label">Estado</label>
                                <select id="filtroEstado" class="form-select">
                                    <option value="">Todos los Estados</option>
                                    <option value="ABIERTO">Abierto</option>
                                    <option value="CERRADO">Cerrado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtroUsuario" class="form-label">Usuario</label>
                                <select id="filtroUsuario" class="form-select">
                                    <option value="">Todos los Usuarios</option>
                                </select>
                            </div>
                        </div>

                        <!-- Resumen de totales -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-warning text-white shadow">
                                    <div class="card-body">
                                        <div class="text-center">
                                            <h5 class="card-title">
                                                <i class="fas fa-calendar-check"></i> Total Arqueos
                                            </h5>
                                            <h3 id="totalArqueos">0</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white shadow">
                                    <div class="card-body">
                                        <div class="text-center">
                                            <h5 class="card-title">
                                                <i class="fas fa-check-circle"></i> Cerrados
                                            </h5>
                                            <h3 id="totalCerrados">0</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white shadow">
                                    <div class="card-body">
                                        <div class="text-center">
                                            <h5 class="card-title">
                                                <i class="fas fa-unlock"></i> Abiertos
                                            </h5>
                                            <h3 id="totalAbiertos">0</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-primary text-white shadow">
                                    <div class="card-body">
                                        <div class="text-center">
                                            <h5 class="card-title">
                                                <i class="fas fa-coins"></i> Total Saldo
                                            </h5>
                                            <h3 id="totalSaldo">S/ 0.00</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tablaArqueos" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha Apertura</th>
                                        <th>Hora Apertura</th>
                                        <th>Fecha Cierre</th>
                                        <th>Hora Cierre</th>
                                        <th>Monto Inicial</th>
                                        <th>Saldo Real</th>
                                        <th>Saldo a Dejar</th>
                                        <th>Estado</th>
                                        <th>Usuario</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoTablaArqueos">
                                    <!-- Los datos se cargarán dinámicamente con JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles del arqueo -->
    <div class="modal fade" id="modalDetallesArqueo" tabindex="-1" aria-labelledby="modalDetallesArqueoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalDetallesArqueoLabel">
                        <i class="fas fa-eye me-2"></i> Detalles del Arqueo de Caja
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenidoDetallesArqueo">
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando detalles del arqueo...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-info" id="btnImprimirArqueo">
                        <i class="fas fa-print me-2"></i>Imprimir Reporte
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cerrar arqueo -->
    <div class="modal fade" id="modalCerrarArqueo" tabindex="-1" aria-labelledby="modalCerrarArqueoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalCerrarArqueoLabel">
                        <i class="fas fa-lock me-2"></i> Cerrar Arqueo de Caja
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCerrarArqueo">
                        <input type="hidden" id="idArqueoCerrar" name="idarqueo">
                        
                        <div class="mb-3">
                            <label for="horaCierre" class="form-label">Hora de Cierre</label>
                            <input type="time" class="form-control" id="horaCierre" name="hora_cierre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="saldoReal" class="form-label">Saldo Real en Caja (S/.)</label>
                            <input type="number" class="form-control" id="saldoReal" name="saldo_real" step="0.01" min="0" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="saldoDejar" class="form-label">Saldo a Dejar para Mañana (S/.)</label>
                            <input type="number" class="form-control" id="saldoDejar" name="saldo_a_dejar" step="0.01" min="0" value="0">
                        </div>
                        
                        <div class="mb-3">
                            <label for="observacionesCierre" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observacionesCierre" name="observaciones" rows="3" placeholder="Observaciones adicionales sobre el cierre..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarCierre">
                        <i class="fas fa-lock me-2"></i>Cerrar Caja
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar observaciones -->
    <div class="modal fade" id="modalEditarObservaciones" tabindex="-1" aria-labelledby="modalEditarObservacionesLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="modalEditarObservacionesLabel">
                        <i class="fas fa-edit me-2"></i> Editar Observaciones
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarObservaciones">
                        <input type="hidden" id="idArqueoEditar" name="idarqueo">
                        
                        <div class="mb-3">
                            <label for="observacionesEditar" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observacionesEditar" name="observaciones" rows="4" placeholder="Escriba las observaciones..."></textarea>
                            <small class="text-muted">Máximo 500 caracteres</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="btnGuardarObservaciones">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
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
            let tablaArqueos;

            // Función para cargar usuarios en el filtro
            function cargarUsuarios() {
                $.ajax({
                    url: '../../controllers/usuario.controller.php?op=listar',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status && response.data) {
                            const selectUsuario = $('#filtroUsuario');
                            selectUsuario.empty();
                            selectUsuario.append('<option value="">Todos los Usuarios</option>');

                            response.data.forEach(usuario => {
                                selectUsuario.append(`
                                    <option value="${usuario.idusuario}">
                                        ${usuario.nombres} ${usuario.apellidos}
                                    </option>
                                `);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar usuarios:', error);
                        // No mostrar error si no existe el controlador de usuarios
                    }
                });
            }

            // Función para cargar arqueos
            function cargarArqueos() {
                // Capturar los valores de los filtros
                const filtroFechaDesde = $('#filtroFechaDesde').val();
                const filtroFechaHasta = $('#filtroFechaHasta').val();
                const filtroEstado = $('#filtroEstado').val();
                const filtroUsuario = $('#filtroUsuario').val();

                // Mostrar indicador de carga
                $('#cuerpoTablaArqueos').html(`
                    <tr>
                        <td colspan="11" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando listado de arqueos...</p>
                        </td>
                    </tr>
                `);

                // RUTA CORREGIDA: De ../../../ a ../../
                let url = '../../controllers/arqueocaja.controller.php?op=listar';

                if (filtroFechaDesde) {
                    url += '&fecha_desde=' + encodeURIComponent(filtroFechaDesde);
                }

                if (filtroFechaHasta) {
                    url += '&fecha_hasta=' + encodeURIComponent(filtroFechaHasta);
                }

                if (filtroEstado) {
                    url += '&estado=' + encodeURIComponent(filtroEstado);
                }

                if (filtroUsuario) {
                    url += '&idusuario=' + encodeURIComponent(filtroUsuario);
                }

                console.log("URL de búsqueda:", url);

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("Respuesta recibida:", response);

                        if (response && response.status) {
                            // Destruir la tabla existente si ya está inicializada
                            if (tablaArqueos) {
                                tablaArqueos.destroy();
                            }

                            // Verificar si hay datos
                            if (!response.data || response.data.length === 0) {
                                $('#cuerpoTablaArqueos').html(`
                                    <tr>
                                        <td colspan="11" class="text-center">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>No hay arqueos registrados
                                            </div>
                                        </td>
                                    </tr>
                                `);
                                
                                // Resetear resumen
                                actualizarResumen({
                                    total_arqueos: 0,
                                    total_cerrados: 0,
                                    total_abiertos: 0,
                                    total_saldo: 0
                                });
                                return;
                            }

                            // Inicializar la tabla
                            tablaArqueos = $('#tablaArqueos').DataTable({
                                responsive: true,
                                language: {
                                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                                },
                                data: response.data,
                                columns: [
                                    { data: 'idarqueo' },
                                    { 
                                        data: 'fecha_apertura',
                                        render: function(data) {
                                            if (!data) return 'Sin fecha';
                                            return formatearFecha(data);
                                        }
                                    },
                                    { 
                                        data: 'hora_apertura',
                                        render: function(data) {
                                            if (!data) return 'Sin hora';
                                            return formatearHora(data);
                                        }
                                    },
                                    { 
                                        data: 'fecha_cierre',
                                        render: function(data) {
                                            return data ? formatearFecha(data) : '<span class="text-muted">Pendiente</span>';
                                        }
                                    },
                                    { 
                                        data: 'hora_cierre',
                                        render: function(data) {
                                            return data ? formatearHora(data) : '<span class="text-muted">Pendiente</span>';
                                        }
                                    },
                                    { 
                                        data: 'monto_inicial',
                                        render: function(data) {
                                            return formatearMonto(data || 0);
                                        }
                                    },
                                    { 
                                        data: 'saldo_real',
                                        render: function(data) {
                                            return data !== null ? formatearMonto(data) : '<span class="text-muted">Pendiente</span>';
                                        }
                                    },
                                    { 
                                        data: 'saldo_a_dejar',
                                        render: function(data) {
                                            return formatearMonto(data || 0);
                                        }
                                    },
                                    {
                                        data: 'estado',
                                        render: function(data) {
                                            if (data === 'ABIERTO') {
                                                return '<span class="badge bg-success">Abierto</span>';
                                            } else {
                                                return '<span class="badge bg-danger">Cerrado</span>';
                                            }
                                        }
                                    },
                                    { 
                                        data: 'usuario_nombre',
                                        render: function(data, type, row) {
                                            return data || 'Usuario desconocido';
                                        }
                                    },
                                    {
                                        data: null,
                                        render: function(data, type, row) {
                                            let acciones = `
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-info btn-sm btnVerDetalles" 
                                                        data-id="${row.idarqueo}"
                                                        data-bs-toggle="tooltip" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-warning btn-sm btnEditarObservaciones" 
                                                        data-id="${row.idarqueo}"
                                                        data-observaciones="${row.observaciones || ''}"
                                                        data-bs-toggle="tooltip" title="Editar observaciones">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                            `;

                                            if (row.estado === 'ABIERTO') {
                                                acciones += `
                                                    <button type="button" class="btn btn-danger btn-sm btnCerrarArqueo" 
                                                        data-id="${row.idarqueo}"
                                                        data-bs-toggle="tooltip" title="Cerrar arqueo">
                                                        <i class="fas fa-lock"></i>
                                                    </button>
                                                `;
                                            }

                                            acciones += `</div>`;
                                            return acciones;
                                        }
                                    }
                                ],
                                columnDefs: [
                                    { width: "5%", targets: 0 },
                                    { width: "8%", targets: [1, 2, 3, 4] },
                                    { width: "10%", targets: [5, 6, 7] },
                                    { width: "8%", targets: 8 },
                                    { width: "12%", targets: 9 },
                                    { width: "15%", targets: 10, className: 'text-center' }
                                ],
                                order: [[0, 'desc']] // Ordenar por ID descendente
                            });

                            // Inicializar tooltips
                            $('[data-bs-toggle="tooltip"]').tooltip();

                            // Actualizar resumen
                            actualizarResumen(response.resumen);

                        } else {
                            console.error("Error en la respuesta:", response);

                            $('#cuerpoTablaArqueos').html(`
                                <tr>
                                    <td colspan="11" class="text-center">
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            ${response?.mensaje || 'Error al cargar los arqueos'}
                                        </div>
                                    </td>
                                </tr>
                            `);

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response?.mensaje || 'No se pudieron cargar los arqueos'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error AJAX:", status, error);
                        console.error("Respuesta:", xhr.responseText);

                        $('#cuerpoTablaArqueos').html(`
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

            // Función para actualizar resumen
            function actualizarResumen(resumen) {
                if (!resumen) {
                    resumen = {
                        total_arqueos: 0,
                        total_cerrados: 0,
                        total_abiertos: 0,
                        total_saldo: 0
                    };
                }

                $('#totalArqueos').text(resumen.total_arqueos || 0);
                $('#totalCerrados').text(resumen.total_cerrados || 0);
                $('#totalAbiertos').text(resumen.total_abiertos || 0);
                $('#totalSaldo').text(formatearMonto(resumen.total_saldo || 0));
            }

            // Función para formatear fecha
            function formatearFecha(fecha) {
                if (!fecha) return '';
                const partesFecha = fecha.split('-');
                if (partesFecha.length === 3) {
                    return `${partesFecha[2]}/${partesFecha[1]}/${partesFecha[0]}`;
                }
                return fecha;
            }

            // Función para formatear hora
            function formatearHora(hora) {
                if (!hora) return '';
                try {
                    const partes = hora.split(':');
                    if (partes.length >= 2) {
                        let horas = parseInt(partes[0], 10);
                        const minutos = partes[1].padStart(2, '0');
                        const periodo = horas >= 12 ? 'PM' : 'AM';
                        
                        if (horas > 12) {
                            horas -= 12;
                        } else if (horas === 0) {
                            horas = 12;
                        }
                        
                        return `${horas.toString().padStart(2, '0')}:${minutos} ${periodo}`;
                    }
                } catch (error) {
                    console.error("Error al formatear hora:", error);
                }
                return hora;
            }

            // Función para formatear monto
            function formatearMonto(monto) {
                return `S/ ${parseFloat(monto || 0).toFixed(2)}`;
            }

            // Establecer fechas por defecto (último mes)
            function establecerFechasDefecto() {
                const hoy = new Date();
                const haceUnMes = new Date();
                haceUnMes.setMonth(haceUnMes.getMonth() - 1);
                
                $('#filtroFechaHasta').val(formatearFechaInput(hoy));
                $('#filtroFechaDesde').val(formatearFechaInput(haceUnMes));
            }

            // Función para formatear fecha para input
            function formatearFechaInput(fecha) {
                const año = fecha.getFullYear();
                const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                const dia = String(fecha.getDate()).padStart(2, '0');
                return `${año}-${mes}-${dia}`;
            }

            // Añadir event listeners a los filtros
            $('#filtroFechaDesde, #filtroFechaHasta, #filtroEstado, #filtroUsuario').on('change', function() {
                cargarArqueos();
            });

            // Evento para actualizar
            $('#btnActualizar').on('click', function() {
                cargarArqueos();
            });

            // Evento para ver detalles
            $(document).on('click', '.btnVerDetalles', function() {
                const idArqueo = $(this).data('id');
                
                $('#contenidoDetallesArqueo').html(`
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando detalles del arqueo...</p>
                    </div>
                `);

                const modalDetalles = new bootstrap.Modal(document.getElementById('modalDetallesArqueo'));
                modalDetalles.show();

                // RUTA CORREGIDA: De ../../../ a ../../
                $.ajax({
                    url: `../../controllers/arqueocaja.controller.php?op=obtener_detalles&idarqueo=${idArqueo}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status && response.data) {
                            const arqueo = response.data;
                            const movimientos = response.movimientos || [];
                            
                            let html = `
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="text-primary">Información General</h5>
                                        <table class="table table-borderless">
                                            <tr>
                                                <td class="fw-bold">ID Arqueo:</td>
                                                <td>${arqueo.idarqueo}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Fecha Apertura:</td>
                                                <td>${formatearFecha(arqueo.fecha_apertura)}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Hora Apertura:</td>
                                                <td>${formatearHora(arqueo.hora_apertura)}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Fecha Cierre:</td>
                                                <td>${arqueo.fecha_cierre ? formatearFecha(arqueo.fecha_cierre) : '<span class="text-muted">Pendiente</span>'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Hora Cierre:</td>
                                                <td>${arqueo.hora_cierre ? formatearHora(arqueo.hora_cierre) : '<span class="text-muted">Pendiente</span>'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Usuario:</td>
                                                <td>${arqueo.usuario_nombre || 'Usuario desconocido'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Estado:</td>
                                                <td>
                                                    <span class="badge ${arqueo.estado === 'ABIERTO' ? 'bg-success' : 'bg-danger'}">
                                                        ${arqueo.estado}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="text-success">Información Financiera</h5>
                                        <table class="table table-borderless">
                                            <tr>
                                                <td class="fw-bold">Monto Inicial:</td>
                                                <td class="text-info">${formatearMonto(arqueo.monto_inicial)}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Saldo Anterior:</td>
                                                <td class="text-warning">${formatearMonto(arqueo.saldo_anterior)}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Saldo Real:</td>
                                                <td class="text-success">${arqueo.saldo_real !== null ? formatearMonto(arqueo.saldo_real) : '<span class="text-muted">Pendiente</span>'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Saldo a Dejar:</td>
                                                <td class="text-primary">${formatearMonto(arqueo.saldo_a_dejar)}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Diferencia:</td>
                                                <td class="${(arqueo.diferencia || 0) >= 0 ? 'text-success' : 'text-danger'}">
                                                    ${arqueo.diferencia !== null ? formatearMonto(arqueo.diferencia) : '<span class="text-muted">Pendiente</span>'}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            `;

                            if (arqueo.observaciones) {
                                html += `
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h5 class="text-secondary">Observaciones</h5>
                                            <div class="alert alert-light">
                                                ${arqueo.observaciones}
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }

                            if (movimientos.length > 0) {
                                html += `
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h5 class="text-info">Resumen de Movimientos</h5>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-striped">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th>Tipo</th>
                                                            <th>Forma de Pago</th>
                                                            <th class="text-end">Monto</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                `;

                                movimientos.forEach(mov => {
                                    html += `
                                        <tr>
                                            <td>
                                                <span class="badge ${mov.tipo === 'INGRESO' ? 'bg-success' : 'bg-danger'}">
                                                    ${mov.tipo}
                                                </span>
                                            </td>
                                            <td>${mov.forma_pago}</td>
                                            <td class="text-end ${mov.tipo === 'INGRESO' ? 'text-success' : 'text-danger'}">
                                                ${formatearMonto(mov.monto)}
                                            </td>
                                        </tr>
                                    `;
                                });

                                html += `
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }

                            $('#contenidoDetallesArqueo').html(html);
                        } else {
                            $('#contenidoDetallesArqueo').html(`
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    ${response?.mensaje || 'Error al cargar los detalles del arqueo'}
                                </div>
                            `);
                        }
                    },
                    error: function() {
                        $('#contenidoDetallesArqueo').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error al cargar los detalles del arqueo
                            </div>
                        `);
                    }
                });
            });

            // Evento para cerrar arqueo
            $(document).on('click', '.btnCerrarArqueo', function() {
                const idArqueo = $(this).data('id');
                
                $('#idArqueoCerrar').val(idArqueo);
                
                // Establecer hora actual
                const now = new Date();
                const horaActual = now.getHours().toString().padStart(2, '0') + ':' +
                                 now.getMinutes().toString().padStart(2, '0');
                $('#horaCierre').val(horaActual);
                
                const modalCerrar = new bootstrap.Modal(document.getElementById('modalCerrarArqueo'));
                modalCerrar.show();
            });

            // Evento para confirmar cierre
            $('#btnConfirmarCierre').on('click', function() {
                const form = document.getElementById('formCerrarArqueo');
                
                // Validar formulario
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                // Mostrar loading
                Swal.fire({
                    title: 'Procesando',
                    text: 'Cerrando arqueo de caja...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Preparar datos
                const formData = new FormData(form);
                formData.append('fecha_cierre', new Date().toISOString().split('T')[0]);

                // Calcular diferencia
                const saldoReal = parseFloat(formData.get('saldo_real') || 0);
                const saldoDejar = parseFloat(formData.get('saldo_a_dejar') || 0);
                formData.append('diferencia', (saldoReal - saldoDejar).toFixed(2));

                // RUTA CORREGIDA: De ../../../ a ../../
                $.ajax({
                    url: '../../controllers/arqueocaja.controller.php?op=cerrar_caja',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();

                        if (response.status) {
                            // Cerrar modal
                            bootstrap.Modal.getInstance(document.getElementById('modalCerrarArqueo')).hide();
                            
                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: 'Arqueo cerrado',
                                text: response.mensaje || 'El arqueo ha sido cerrado correctamente',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                // Recargar tabla
                                cargarArqueos();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.mensaje || 'No se pudo cerrar el arqueo',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo procesar la solicitud',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            });

            // Evento para editar observaciones
            $(document).on('click', '.btnEditarObservaciones', function() {
                const idArqueo = $(this).data('id');
                const observaciones = $(this).data('observaciones');
                
                $('#idArqueoEditar').val(idArqueo);
                $('#observacionesEditar').val(observaciones);
                
                const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarObservaciones'));
                modalEditar.show();
            });

            // Evento para guardar observaciones
            $('#btnGuardarObservaciones').on('click', function() {
                const form = document.getElementById('formEditarObservaciones');
                const formData = new FormData(form);

                // Mostrar loading
                Swal.fire({
                    title: 'Procesando',
                    text: 'Guardando observaciones...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // RUTA CORREGIDA: De ../../../ a ../../
                $.ajax({
                    url: '../../controllers/arqueocaja.controller.php?op=actualizar_observaciones',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();

                        if (response.status) {
                            // Cerrar modal
                            bootstrap.Modal.getInstance(document.getElementById('modalEditarObservaciones')).hide();
                            
                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: 'Observaciones actualizadas',
                                text: response.mensaje || 'Las observaciones han sido actualizadas correctamente',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                // Recargar tabla
                                cargarArqueos();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.mensaje || 'No se pudieron actualizar las observaciones',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo procesar la solicitud',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            });

            // Evento para imprimir reporte
            $('#btnImprimirArqueo').on('click', function() {
                const contenido = $('#contenidoDetallesArqueo').html();
                const ventanaImpresion = window.open('', '_blank');

                ventanaImpresion.document.write(`
                    <html>
                    <head>
                        <title>Reporte de Arqueo de Caja</title>
                        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
                                <h3>Reporte de Arqueo de Caja</h3>
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

            // Inicialización
            establecerFechasDefecto();
            cargarUsuarios();
            cargarArqueos();
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

        .modal-xl {
            max-width: 1140px;
        }

        .modal-header {
            padding: 12px 16px;
        }

        .modal-body {
            padding: 20px;
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

        /* Estilos para validación */
        .is-invalid {
            border-color: #dc3545 !important;
        }

        .is-valid {
            border-color: #198754 !important;
        }
    </style>
</body>

</html>