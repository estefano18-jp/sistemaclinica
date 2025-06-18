<?php /*RUTA: sistemasclinica/views/GestionarDevolucion/ProcesoDevolucion/procesoDevolucion.php*/ ?>
<?php require_once '../../include/header.administrador.php'; ?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Proceso de Devolución</title>

    <!-- CSS de Bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            background-color: #f4f6f9;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }

        .process-step {
            position: relative;
            padding: 20px 15px;
            border-left: 3px solid #3498db;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .process-step h5 {
            color: #3498db;
            margin-bottom: 10px;
        }

        .step-number {
            position: absolute;
            top: -15px;
            left: -15px;
            width: 30px;
            height: 30px;
            background-color: #3498db;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .badge-estado {
            padding: 6px 10px;
            border-radius: 20px;
            font-weight: 500;
        }

        .badge-programada {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-realizada {
            background-color: #198754;
            color: white;
        }

        .badge-cancelada {
            background-color: #dc3545;
            color: white;
        }

        .badge-no-asistio {
            background-color: #6c757d;
            color: white;
        }

        .form-label {
            font-weight: 500;
        }

        .monto-devolucion {
            font-size: 1.8rem;
            font-weight: bold;
            color: #28a745;
        }

        .info-panel {
            background-color: #e8f4ff;
            border-left: 5px solid #3498db;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .search-container {
            position: relative;
        }

        .search-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #3498db;
        }

        /* Estilo para el recibo */
        .receipt-container {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            background-color: #fff;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
        }

        .receipt-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .receipt-table td {
            padding: 5px 0;
        }

        .receipt-table .receipt-label {
            font-weight: 500;
            color: #666;
            width: 40%;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #777;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }

        /* Animación para carga */
        .spinner-grow {
            width: 1rem;
            height: 1rem;
        }

        /* Estilos adicionales para modales de SweetAlert */
        .swal2-container {
            z-index: 9999 !important;
        }

        /* Corrección para evitar desplazamiento cuando se abren modales */
        body.swal2-shown {
            padding-right: 0 !important;
        }

        /* Estilo para tarjetas de cita seleccionable */
        .cita-card {
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .cita-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Fix para conflictos modal-backdrop */
        .modal-backdrop {
            z-index: 1040 !important;
        }
        
        /* Indicador sutil de procesamiento */
        #subtle-indicator {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 9999;
            display: none;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 4px;
            padding: 5px 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Proceso de Devolución</h2>
                        <a href="../../Citas/GestionarCita/gestionarCita.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Listado
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Contenedor principal del proceso -->
                        <div id="main-container">
                            <!-- Paso 1: Búsqueda de Cita/Paciente -->
                            <div class="process-step">
                                <div class="step-number">1</div>
                                <h5>Identificación de la Cita</h5>
                                <p class="text-muted">Busque la cita por número de documento del paciente o por ID de cita.</p>

                                <!-- Formulario de búsqueda -->
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nro-documento" class="form-label">Número de Documento</label>
                                        <div class="search-container">
                                            <input type="text" class="form-control" id="nro-documento" placeholder="Ingrese DNI, pasaporte, etc.">
                                            <span class="search-icon" id="buscar-documento"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="id-cita" class="form-label">ID de Cita</label>
                                        <div class="search-container">
                                            <input type="text" class="form-control" id="id-cita" placeholder="Ingrese ID de cita" value="<?php echo isset($_GET['idcita']) ? htmlspecialchars($_GET['idcita']) : ''; ?>">
                                            <span class="search-icon" id="buscar-cita"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Paso 2: Información de la Cita (inicialmente oculto) -->
                            <div class="process-step d-none" id="info-cita-container">
                                <div class="step-number">2</div>
                                <h5>Información de la Cita</h5>
                                <p class="text-muted">Detalles de la cita a cancelar y el pago a devolver.</p>

                                <div class="row mb-4" id="cita-details">
                                    <!-- Los detalles se cargarán dinámicamente -->
                                </div>
                            </div>

                            <!-- Paso 3: Proceso de Devolución (inicialmente oculto) -->
                            <div class="process-step d-none" id="devolucion-container">
                                <div class="step-number">3</div>
                                <h5>Proceso de Devolución</h5>
                                <p class="text-muted">Complete los datos para procesar la devolución.</p>

                                <form id="form-devolucion">
                                    <input type="hidden" id="idcita-devolucion" name="idcita">
                                    <input type="hidden" id="idventa-devolucion" name="idventa">

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="motivo-devolucion" class="form-label">Motivo de Cancelación</label>
                                            <select class="form-select" id="motivo-devolucion" name="motivo" required>
                                                <option value="">Seleccione un motivo</option>
                                                <option value="SOLICITUD_PACIENTE">Solicitud del paciente</option>
                                                <option value="EMERGENCIA_MEDICA">Emergencia médica</option>
                                                <option value="PROBLEMA_HORARIO">Problema de horario</option>
                                                <option value="CANCELACION_DOCTOR">Cancelación por parte del doctor</option>
                                                <option value="OTRO">Otro motivo</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="monto-devolucion" class="form-label">Monto a Devolver</label>
                                            <div class="input-group">
                                                <span class="input-group-text">S/.</span>
                                                <input type="number" class="form-control" id="monto-devolucion" name="monto" step="0.01" readonly required>
                                            </div>
                                            <div class="form-text">Se devolverá el monto total pagado por la cita.</div>
                                        </div>
                                        <div class="col-12">
                                            <label for="observaciones-devolucion" class="form-label">Observaciones</label>
                                            <textarea class="form-control" id="observaciones-devolucion" name="observaciones" rows="3" placeholder="Ingrese observaciones adicionales sobre la devolución"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="metodo-devolucion" class="form-label">Método de Devolución</label>
                                            <select class="form-select" id="metodo-devolucion" name="metodo" required>
                                                <option value="">Seleccione un método</option>
                                                <option value="EFECTIVO">Efectivo</option>
                                                <option value="YAPE">Yape</option>
                                                <option value="PLIN">Plin</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="usuario-autoriza" class="form-label">Usuario que Autoriza</label>
                                            <input type="text" class="form-control" id="usuario-autoriza" name="usuario" readonly required>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="button" class="btn btn-secondary me-2" id="btn-cancelar-proceso">Cancelar</button>
                                        <button type="submit" class="btn btn-primary" id="btn-procesar-devolucion">
                                            <i class="fas fa-check-circle me-2"></i>Procesar Devolución
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Paso 4: Confirmación (inicialmente oculto) -->
                            <div class="process-step d-none" id="confirmacion-container">
                                <div class="step-number">4</div>
                                <h5>Confirmación de Devolución</h5>
                                <p class="text-muted">La devolución ha sido procesada correctamente.</p>

                                <div class="receipt-container">
                                    <div class="receipt-header">
                                        <h4>Comprobante de Devolución</h4>
                                        <p class="mb-0">Clínica Médica</p>
                                        <p id="fecha-hora-comprobante"></p>
                                    </div>

                                    <div class="receipt-body">
                                        <table class="receipt-table">
                                            <tr>
                                                <td class="receipt-label">N° de Comprobante:</td>
                                                <td id="comprobante-id"></td>
                                            </tr>
                                            <tr>
                                                <td class="receipt-label">Paciente:</td>
                                                <td id="comprobante-paciente"></td>
                                            </tr>
                                            <tr>
                                                <td class="receipt-label">Documento:</td>
                                                <td id="comprobante-documento"></td>
                                            </tr>
                                            <tr>
                                                <td class="receipt-label">Cita Cancelada:</td>
                                                <td id="comprobante-cita"></td>
                                            </tr>
                                            <tr>
                                                <td class="receipt-label">Monto Devuelto:</td>
                                                <td id="comprobante-monto" class="fw-bold"></td>
                                            </tr>
                                            <tr>
                                                <td class="receipt-label">Método de Devolución:</td>
                                                <td id="comprobante-metodo"></td>
                                            </tr>
                                            <tr>
                                                <td class="receipt-label">Motivo:</td>
                                                <td id="comprobante-motivo"></td>
                                            </tr>
                                            <tr>
                                                <td class="receipt-label">Autorizado por:</td>
                                                <td id="comprobante-autorizado"></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div class="receipt-footer">
                                        <p>Gracias por su comprensión</p>
                                        <p class="mb-0">Este comprobante es un documento válido de devolución</p>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-center mt-4">
                                    <button type="button" class="btn btn-outline-secondary me-2" id="btn-imprimir-comprobante">
                                        <i class="fas fa-print me-2"></i>Imprimir
                                    </button>
                                    <a href="../../Citas/GestionarCita/gestionarCita.php" class="btn btn-primary">
                                        <i class="fas fa-list me-2"></i>Volver al Listado
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicador sutil de procesamiento -->
    <div id="subtle-indicator">
        <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
        <span class="ms-1 small">Procesando...</span>
    </div>

    <!-- Scripts - ORDEN CRÍTICO PARA EVITAR CONFLICTOS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- ⚠️ IMPORTANTE: Primero jQuery, luego SweetAlert, y finalmente Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Ajustes preventivos para evitar conflictos
            $.fn.modal.Constructor.Default.backdrop = 'static';
            $.fn.modal.Constructor.Default.keyboard = false;

            // Variables globales
            let citaActual = null;
            let ventaActual = null;
            let isProcessing = false; // Flag para evitar operaciones concurrentes

            // Establecer el usuario actual en el campo "usuario que autoriza"
            const nombreUsuario = "<?php
                                    if (isset($_SESSION['usuario']['apellidos']) && isset($_SESSION['usuario']['nombres'])) {
                                        echo addslashes($_SESSION['usuario']['apellidos'] . ', ' . $_SESSION['usuario']['nombres']);
                                    } else {
                                        echo addslashes(isset($_SESSION['usuario']['nomuser']) ? $_SESSION['usuario']['nomuser'] : 'Administrador');
                                    }
                                    ?>";
            $('#usuario-autoriza').val(nombreUsuario);

            // ===== FUNCIÓN CRÍTICA OPTIMIZADA: Mostrar selección de citas =====
            // Esta función ha sido completamente reescrita para evitar el congelamiento
            function mostrarSeleccionCitas(citas) {
                // IMPORTANTE: Detener cualquier procesamiento previo
                if (isProcessing) {
                    console.warn("Otra operación está en curso, deteniendo procesos...");
                    hideLoading();
                    isProcessing = false;
                }

                console.log("Mostrando selección de citas:", citas.length);

                // Preparar el contenido HTML del diálogo
                let contenidoHTML = `
                <div class="container-fluid p-0">
                    <div class="row g-3">
                `;

                // Generar tarjetas de citas de forma más eficiente
                citas.forEach((cita, index) => {
                    const fechaFormateada = formatearFecha(cita.fecha);
                    const horaFormateada = formatearHora(cita.hora);

                    // Priorizar campos correctos para doctor y especialidad
                    const nombreDoctor = (cita.doctor_nombre && cita.doctor_apellido) ?
                        `${cita.doctor_apellido}, ${cita.doctor_nombre}` :
                        (cita.doctor_apellido) ?
                        cita.doctor_apellido :
                        (cita.nombre_doctor || cita.doctor || 'Doctor no especificado');

                    const nombreEspecialidad = cita.especialidad || 'Especialidad no especificada';

                    // Crear tarjeta con ID único
                    contenidoHTML += `
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 cita-card" id="cita-card-${index}" data-id="${cita.idcita || cita.id}">
                            <div class="card-body">
                                <h5 class="card-title">${nombreDoctor}</h5>
                                <h6 class="card-subtitle mb-2 text-muted">${nombreEspecialidad}</h6>
                                <p class="card-text">
                                    <strong>Fecha:</strong> ${fechaFormateada}<br>
                                    <strong>Hora:</strong> ${horaFormateada}<br>
                                    <strong>Estado:</strong> ${getBadgeEstado(cita.estado)}
                                </p>
                                <div class="text-center">
                                    <button class="btn btn-primary btn-seleccionar" data-index="${index}">
                                        Seleccionar esta cita
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;
                });

                contenidoHTML += `
                    </div>
                </div>
                `;

                // Usar la configuración optimizada de SweetAlert2
                Swal.fire({
                    title: 'Seleccione una cita',
                    html: contenidoHTML,
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: '850px',
                    customClass: {
                        container: 'swal-container-custom',
                        popup: 'swal-popup-custom',
                        content: 'swal-content-custom'
                    },
                    allowOutsideClick: false,
                    allowEscapeKey: true,
                    willOpen: () => {
                        // Asegurarnos que cualquier modal previo se cierre
                        hideLoading();
                    },
                    didOpen: () => {
                        // SOLUCIÓN CRÍTICA: Usar evento delegado único en vez de múltiples listeners
                        $(document).off('click', '.btn-seleccionar').on('click', '.btn-seleccionar', function(e) {
                            e.preventDefault();
                            e.stopPropagation();

                            const index = $(this).data('index');
                            const idCita = citas[index].idcita || citas[index].id;

                            console.log("Cita seleccionada:", idCita, "Índice:", index);

                            // Cerrar SweetAlert correctamente primero
                            Swal.close();

                            // SOLUCIÓN CRÍTICA: Usar un timeout para evitar conflictos de renderizado
                            setTimeout(() => {
                                // Limpiar cualquier evento residual
                                $(document).off('click', '.btn-seleccionar');

                                // Realizar la búsqueda de cita
                                buscarCitaPorId(idCita);
                            }, 300);
                        });

                        // También hacer clic en la tarjeta completa
                        $(document).off('click', '.cita-card').on('click', '.cita-card', function(e) {
                            if (!$(e.target).hasClass('btn-seleccionar')) {
                                $(this).find('.btn-seleccionar').click();
                            }
                        });
                    },
                    willClose: () => {
                        console.log("Cerrando modal de selección de citas");
                        // Eliminar todos los event listeners para evitar duplicación
                        $(document).off('click', '.btn-seleccionar');
                        $(document).off('click', '.cita-card');
                    }
                }).then(() => {
                    // Garantizar que se limpien los listeners cuando se cierra el modal
                    $(document).off('click', '.btn-seleccionar');
                    $(document).off('click', '.cita-card');
                });
            }

            // Función modificada para eliminar el modal de carga
            function showLoading(message = "Procesando solicitud...", submessage = "Por favor espere mientras se completa la operación.") {
                // Mantener solo el estado de procesamiento sin mostrar el modal
                isProcessing = true;
                
                // Mostrar indicador sutil en la esquina
                $('#subtle-indicator').fadeIn(200);
                
                console.log("Procesando: " + message);
            }

            // Función modificada para eliminar el modal de carga
            function hideLoading() {
                // Restablecer el estado de procesamiento
                isProcessing = false;
                
                // Ocultar el indicador sutil
                $('#subtle-indicator').fadeOut(200);
                
                console.log("Procesamiento completado");
                
                // Asegurarse de que cualquier modal de Bootstrap se cierre correctamente
                try {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');
                } catch (e) {
                    console.log("Error al limpiar estados de modal:", e);
                }
            }

            // Configurar sistema de vigilancia contra bloqueos
            function setupPerfMonitor() {
                // Contador para detectar operaciones largas
                let operationStart = null;
                let monitorInterval = null;

                // Interceptar el método showLoading
                const originalShowLoading = window.showLoading;
                window.showLoading = function() {
                    operationStart = performance.now();
                    isProcessing = true;

                    // Iniciar monitoreo solo si no está activo
                    if (!monitorInterval) {
                        monitorInterval = setInterval(checkPerformance, 1000);
                    }

                    // Llamar a la función original
                    return originalShowLoading.apply(this, arguments);
                };

                // Interceptar el método hideLoading
                const originalHideLoading = window.hideLoading;
                window.hideLoading = function() {
                    operationStart = null;
                    isProcessing = false;

                    // Detener monitoreo
                    if (monitorInterval) {
                        clearInterval(monitorInterval);
                        monitorInterval = null;
                    }

                    // Llamar a la función original
                    return originalHideLoading.apply(this, arguments);
                };

                // Función para verificar rendimiento
                function checkPerformance() {
                    if (operationStart && isProcessing) {
                        const elapsed = performance.now() - operationStart;

                        // Si han pasado más de 5 segundos en una operación, mostrar advertencia
                        if (elapsed > 5000) {
                            console.warn(`⚠️ Operación larga detectada: ${(elapsed/1000).toFixed(1)}s`);
                        }

                        // Si han pasado más de 15 segundos, intervenir automáticamente
                        if (elapsed > 15000) {
                            console.error(`🛑 Operación bloqueada detectada: ${(elapsed/1000).toFixed(1)}s - Interviniendo...`);
                            clearInterval(monitorInterval);
                            monitorInterval = null;
                            operationStart = null;
                            isProcessing = false;

                            // Restablecer el estado sin mostrar modales
                            hideLoading();

                            // Notificar al usuario de manera no intrusiva
                            Swal.fire({
                                icon: 'warning',
                                title: 'Operación interrumpida',
                                text: 'Una operación estaba tardando demasiado y ha sido detenida automáticamente.',
                                confirmButtonText: 'Entendido'
                            });
                        }
                    }
                }

                // También monitorear renderizado de la página
                let longFrameCount = 0;
                let lastFrameTime = performance.now();

                // Usar requestAnimationFrame para detectar frames largos
                function checkFrame() {
                    const now = performance.now();
                    const frameDuration = now - lastFrameTime;
                    lastFrameTime = now;

                    // Un frame mayor a 100ms indica posible bloqueo
                    if (frameDuration > 100) {
                        longFrameCount++;
                        console.warn(`Frame largo detectado: ${frameDuration.toFixed(1)}ms`);

                        // Si hay 5 frames largos consecutivos, posible bloqueo de UI
                        if (longFrameCount >= 5 && isProcessing) {
                            console.error("Posible bloqueo de UI detectado - Liberando recursos");
                            hideLoading();
                            isProcessing = false;
                        }
                    } else {
                        // Resetear contador si los frames son normales
                        longFrameCount = 0;
                    }

                    requestAnimationFrame(checkFrame);
                }

                // Iniciar monitoreo de frames
                requestAnimationFrame(checkFrame);

                console.log("Sistema de monitoreo de rendimiento activado");
            }

            // Función para formatear fecha (yyyy-mm-dd a dd/mm/yyyy)
            function formatearFecha(fechaStr) {
                if (!fechaStr) return '';

                try {
                    const partes = fechaStr.split('-');
                    if (partes.length !== 3) return fechaStr;

                    return `${partes[2]}/${partes[1]}/${partes[0]}`;
                } catch (e) {
                    console.error("Error al formatear fecha:", e);
                    return fechaStr;
                }
            }

            // Función para formatear hora (HH:MM:SS a HH:MM AM/PM)
            function formatearHora(horaStr) {
                if (!horaStr) return '';

                try {
                    const partes = horaStr.split(':');
                    if (partes.length < 2) return horaStr;

                    let horas = parseInt(partes[0], 10);
                    const minutos = partes[1].padStart(2, '0');

                    const periodo = horas >= 12 ? 'PM' : 'AM';

                    if (horas > 12) {
                        horas -= 12;
                    } else if (horas === 0) {
                        horas = 12;
                    }

                    return `${horas}:${minutos} ${periodo}`;
                } catch (e) {
                    console.error("Error al formatear hora:", e);
                    return horaStr;
                }
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
                    case 'REALIZADA':
                    case 'COMPLETADA':
                        badgeClass = 'badge-realizada';
                        break;
                    case 'CANCELADA':
                        badgeClass = 'badge-cancelada';
                        break;
                    case 'NO ASISTIO':
                        badgeClass = 'badge-no-asistio';
                        break;
                    default:
                        badgeClass = 'badge-pendiente';
                }

                return `<span class="badge badge-estado ${badgeClass}">${badgeText}</span>`;
            }

            // FUNCIÓN OPTIMIZADA: buscarCitaPorId
            function buscarCitaPorId(idCita) {
                // Validar que no esté procesando otra solicitud
                if (isProcessing) {
                    console.warn("Hay otra operación en curso, esperando...");
                    setTimeout(() => buscarCitaPorId(idCita), 500);
                    return;
                }

                isProcessing = true;

                // Ocultar cualquier modal previo
                hideLoading();

                // Mostrar indicador de carga ligero
                showLoading("Buscando cita...", "Consultando información de la cita");

                // Limpiar contenedores
                $('#cita-details').empty();
                $('#info-cita-container').addClass('d-none');
                $('#devolucion-container').addClass('d-none');

                // Resetear variables globales
                citaActual = null;
                ventaActual = null;

                // Realizar la solicitud con manejo mejorado de errores
                $.ajax({
                    url: '../../../controllers/cita.controller.php',
                    type: 'GET',
                    data: {
                        op: 'obtener',
                        id: idCita
                    },
                    dataType: 'json',
                    timeout: 15000, // 15 segundos máximo
                    success: function(response) {
                        console.log("Respuesta de buscar cita:", response);

                        // FORZAR cierre del modal de carga ANTES de continuar
                        hideLoading();

                        if (response && response.status === true && response.data) {
                            // Guardar información de la cita
                            citaActual = response.data;

                            // Buscar información de venta/comprobante
                            // SOLUCIÓN CRÍTICA: usar setTimeout para evitar conflictos de renderizado
                            setTimeout(() => {
                                buscarComprobantePorCita(idCita);
                            }, 200);
                        } else {
                            isProcessing = false;
                            Swal.fire({
                                icon: 'error',
                                title: 'Cita no encontrada',
                                text: 'No se encontró información para la cita especificada.',
                                confirmButtonText: 'Entendido'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Garantizar que se oculte el loader y se limpie el estado
                        hideLoading();
                        isProcessing = false;

                        console.error("Error al buscar cita:", error);
                        console.error("Respuesta del servidor:", xhr.responseText);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al buscar la cita. Por favor, intente nuevamente.',
                            confirmButtonText: 'Entendido'
                        });
                    },
                    complete: function() {
                        // Siempre ocultar loader y limpiar estado al terminar
                        hideLoading();
                    }
                });
            }

            // FUNCIÓN OPTIMIZADA: buscarComprobantePorCita
            function buscarComprobantePorCita(idCita) {
                if (!citaActual) {
                    console.error("No hay cita actual para buscar comprobante");
                    return;
                }

                console.log("Buscando comprobante para cita ID:", idCita);

                // Mostrar indicador de carga ligero
                showLoading("Buscando información de pago...", "Consultando detalles del comprobante");

                // Realizar la solicitud con manejo mejorado de errores
                $.ajax({
                    url: '../../../controllers/venta.controller.php',
                    type: 'GET',
                    data: {
                        op: 'comprobante_por_cita',
                        idcita: idCita
                    },
                    dataType: 'json',
                    timeout: 15000, // 15 segundos máximo
                    success: function(response) {
                        // SIEMPRE ocultar el modal ANTES de cualquier procesamiento
                        hideLoading();

                        console.log("Respuesta de comprobante:", response);

                        if (response && response.status && response.data) {
                            ventaActual = response.data;
                            console.log("Comprobante encontrado:", ventaActual);
                        } else {
                            console.log("No se encontró comprobante para esta cita");
                        }

                        // SOLUCIÓN CRÍTICA: pequeño retraso para evitar conflictos de renderizado
                        setTimeout(() => {
                            mostrarInformacionCita();
                        }, 100);
                    },
                    error: function(xhr, status, error) {
                        // Garantizar que se oculte el loader
                        hideLoading();

                        console.error("Error al buscar comprobante:", error);
                        console.error("Respuesta del servidor:", xhr.responseText);

                        // Continuar con la información de la cita aunque haya error
                        setTimeout(() => {
                            mostrarInformacionCita();
                        }, 100);
                    },
                    complete: function() {
                        // Siempre ocultar loader al terminar
                        hideLoading();
                        isProcessing = false;
                    }
                });
            }

            // FUNCIÓN OPTIMIZADA: buscarCitasPorDocumento
            function buscarCitasPorDocumento(nroDocumento) {
                // Validar que no esté procesando otra solicitud
                if (isProcessing) {
                    console.warn("Hay otra operación en curso, deteniendo...");
                    hideLoading();
                    isProcessing = false;
                }

                isProcessing = true;

                // Limpiar estado previo
                hideLoading();
                showLoading("Buscando paciente...", "Consultando citas asociadas al documento");

                // Limpiar contenedores
                $('#cita-details').empty();
                $('#info-cita-container').addClass('d-none');
                $('#devolucion-container').addClass('d-none');

                // Resetear variables globales
                citaActual = null;
                ventaActual = null;

                // Realizar la solicitud con manejo mejorado de errores
                $.ajax({
                    url: '../../../controllers/cita.controller.php',
                    type: 'GET',
                    data: {
                        op: 'buscar_con_filtros',
                        nrodoc: nroDocumento
                    },
                    dataType: 'json',
                    timeout: 15000, // 15 segundos máximo
                    success: function(response) {
                        // SIEMPRE ocultar el modal de carga ANTES de cualquier procesamiento
                        hideLoading();

                        console.log("Respuesta de búsqueda por documento:", response);

                        if (response && response.status && response.data && response.data.length > 0) {
                            // Filtrar citas programadas (no canceladas ni completadas)
                            const citasProgramadas = response.data.filter(cita =>
                                cita.estado === 'PROGRAMADA' || cita.estado === 'PENDIENTE' || cita.estado === 'CONFIRMADA'
                            );

                            if (citasProgramadas.length > 0) {
                                // Si hay varias citas, mostrar selección
                                if (citasProgramadas.length > 1) {
                                    // Pequeño retraso antes de mostrar selección para evitar conflictos
                                    setTimeout(() => {
                                        mostrarSeleccionCitas(citasProgramadas);
                                    }, 100);
                                } else {
                                    // Si solo hay una cita, usarla directamente después de un breve retraso
                                    setTimeout(() => {
                                        buscarCitaPorId(citasProgramadas[0].idcita);
                                    }, 100);
                                }
                            } else {
                                isProcessing = false;
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Sin citas activas',
                                    text: 'El paciente no tiene citas programadas pendientes.'
                                });
                            }
                        } else {
                            isProcessing = false;
                            Swal.fire({
                                icon: 'error',
                                title: 'Paciente no encontrado',
                                text: 'No se encontraron citas asociadas al número de documento proporcionado.'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Garantizar que se oculte el loader y se limpie el estado
                        hideLoading();
                        isProcessing = false;

                        console.error("Error al buscar citas por documento:", error);
                        console.error("Respuesta del servidor:", xhr.responseText);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al buscar las citas. Por favor, intente nuevamente.'
                        });
                    },
                    complete: function() {
                        // Siempre ocultar loader y limpiar estado al terminar
                        hideLoading();
                    }
                });
            }

            // FUNCIÓN OPTIMIZADA: mostrarInformacionCita
            function mostrarInformacionCita() {
                console.log("Mostrando información de cita:", citaActual);
                console.log("Información de venta disponible:", ventaActual ? "Sí" : "No");

                if (!citaActual) {
                    console.error("No hay información de cita para mostrar");
                    isProcessing = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No hay información de cita disponible para mostrar.'
                    });
                    return;
                }

                try {
                    // Formatear fecha y hora
                    const fechaFormateada = formatearFecha(citaActual.fecha);
                    const horaFormateada = formatearHora(citaActual.hora || citaActual.horaprogramada);

                    // Formatear datos del paciente
                    const nombrePaciente = citaActual.paciente_nombre && citaActual.paciente_apellido ?
                        `${citaActual.paciente_apellido}, ${citaActual.paciente_nombre}` :
                        (citaActual.paciente || 'No especificado');

                    const doctorNombre = citaActual.doctor_nombre && citaActual.doctor_apellido ?
                        `${citaActual.doctor_apellido}, ${citaActual.doctor_nombre}` :
                        (citaActual.doctor || 'No especificado');

                    const documentoPaciente = `${citaActual.documento_tipo || citaActual.tipodoc || 'DOC'}: ${citaActual.documento_numero || citaActual.nrodoc || 'No especificado'}`;

                    // Formatear monto a devolver
                    const montoPagado = ventaActual ?
                        parseFloat(ventaActual.precio || ventaActual.montopagado || 0).toFixed(2) :
                        parseFloat(citaActual.monto_pagado || 0).toFixed(2);

                    // Construir HTML con la información
                    let html = `
                    <div class="col-md-6">
                        <div class="info-panel">
                            <h6 class="text-primary mb-3">Información del Paciente</h6>
                            <p><strong>Nombre:</strong> ${nombrePaciente}</p>
                            <p><strong>Documento:</strong> ${documentoPaciente}</p>
                            <p><strong>Teléfono:</strong> ${citaActual.telefono || 'No especificado'}</p>
                            <p><strong>Email:</strong> ${citaActual.email || 'No especificado'}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-panel">
                            <h6 class="text-primary mb-3">Información de la Cita</h6>
                            <p><strong>ID de Cita:</strong> ${citaActual.idcita || citaActual.id || 'No especificado'}</p>
                            <p><strong>Doctor:</strong> ${doctorNombre}</p>
                            <p><strong>Especialidad:</strong> ${citaActual.especialidad || 'No especificada'}</p>
                            <p><strong>Fecha y Hora:</strong> ${fechaFormateada} - ${horaFormateada}</p>
                            <p><strong>Estado:</strong> ${getBadgeEstado(citaActual.estado)}</p>
                        </div>
                    </div>
                    `;

                    // Añadir información de pago si existe
                    if (ventaActual) {
                        html += `
                        <div class="col-12 mt-3">
                            <div class="info-panel">
                                <h6 class="text-primary mb-3">Información de Pago</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Tipo de Comprobante:</strong> ${ventaActual.tipodoc || 'No especificado'}</p>
                                        <p><strong>Número:</strong> ${ventaActual.nrodocumento || 'No especificado'}</p>
                                        <p><strong>Fecha de Emisión:</strong> ${formatearFecha(ventaActual.fechaemision?.split(' ')[0] || '')}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Tipo de Pago:</strong> ${ventaActual.tipopago || 'No especificado'}</p>
                                        <p><strong>Monto Pagado:</strong> <span class="monto-devolucion">S/. ${montoPagado}</span></p>
                                        <p><strong>ID de Venta:</strong> ${ventaActual.idventa || 'No especificado'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `;
                    } else {
                        // Si no hay información de pago, mostrar alerta
                        html += `
                        <div class="col-12 mt-3">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Nota:</strong> No se encontró información de pago para esta cita. 
                                Se puede proceder con la cancelación de la cita, pero no será posible realizar una devolución.
                            </div>
                        </div>
                        
                        <input type="hidden" id="monto-pagado-provisional" value="0.00">
                        `;
                    }

                    // Añadir botón para continuar
                    html += `
                    <div class="col-12 mt-3 text-end">
                        <button class="btn btn-primary" id="btn-continuar-devolucion">
                            <i class="fas fa-arrow-right me-2"></i>Continuar con la ${ventaActual ? 'Devolución' : 'Cancelación'}
                        </button>
                    </div>
                    `;

                    // Actualizar contenido y mostrar - SOLUCIÓN CLAVE
                    // Retrasar ligeramente la actualización del DOM para evitar bloqueos
                    setTimeout(() => {
                        $('#cita-details').html(html);
                        $('#info-cita-container').removeClass('d-none');

                        // Limpiar y asignar evento para continuar
                        $(document).off('click', '#btn-continuar-devolucion');
                        $(document).on('click', '#btn-continuar-devolucion', function() {
                            iniciarProcesoDevolucion();
                        });

                        // Completar proceso
                        isProcessing = false;
                    }, 100);

                } catch (error) {
                    console.error("Error al mostrar información de cita:", error);
                    isProcessing = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al mostrar la información de la cita: ' + error.message
                    });
                }
            }

            // FUNCIÓN OPTIMIZADA: iniciarProcesoDevolucion
            function iniciarProcesoDevolucion() {
                // Validar que no esté procesando otra solicitud
                if (isProcessing) {
                    console.warn("Hay otra operación en curso, espera...");
                    return;
                }

                isProcessing = true;

                // Validar que tengamos la información necesaria
                if (!citaActual) {
                    isProcessing = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Información incompleta',
                        text: 'No se puede procesar la devolución sin información de la cita.'
                    });
                    return;
                }

                // Preparar formulario de devolución
                $('#idcita-devolucion').val(citaActual.idcita || citaActual.id);

                // Obtener el monto a devolver
                let montoPagado = 0;
                if (ventaActual) {
                    $('#idventa-devolucion').val(ventaActual.idventa);
                    montoPagado = parseFloat(ventaActual.precio || ventaActual.montopagado || 0).toFixed(2);
                } else {
                    $('#idventa-devolucion').val('');
                    // Intentar obtener el monto desde el campo provisional o usar 0
                    montoPagado = parseFloat($('#monto-pagado-provisional').val() || 0).toFixed(2);
                }

                // Establecer el monto a devolver (readonly y valor fijo)
                $('#monto-devolucion').val(montoPagado);

                // Verificar si hay monto a devolver
                if (parseFloat(montoPagado) <= 0) {
                    // Si no hay monto a devolver, mostrar un mensaje informativo
                    Swal.fire({
                        icon: 'info',
                        title: 'Sin monto a devolver',
                        text: 'No hay un monto pagado para esta cita. Se procederá solo con la cancelación de la cita.',
                        confirmButtonText: 'Entendido'
                    });
                }

                // Actualizar el texto del formulario según si hay pago o no
                if (parseFloat(montoPagado) <= 0) {
                    $('#devolucion-container h5').text('Proceso de Cancelación');
                    $('#devolucion-container p.text-muted').text('Complete los datos para procesar la cancelación de la cita.');
                    $('#btn-procesar-devolucion').html('<i class="fas fa-times-circle me-2"></i>Cancelar Cita');
                } else {
                    $('#devolucion-container h5').text('Proceso de Devolución');
                    $('#devolucion-container p.text-muted').text('Complete los datos para procesar la devolución.');
                    $('#btn-procesar-devolucion').html('<i class="fas fa-check-circle me-2"></i>Procesar Devolución');
                }

                // Mostrar contenedor de devolución - SOLUCIÓN CLAVE
                // Usar setTimeout para evitar bloqueo de renderizado
                setTimeout(() => {
                    $('#devolucion-container').removeClass('d-none');

                    // Hacer scroll hacia el contenedor
                    $('html, body').animate({
                        scrollTop: $("#devolucion-container").offset().top - 100
                    }, 300);

                    isProcessing = false;
                }, 100);
            }

            // FUNCIÓN CORREGIDA: Procesar la devolución con el nombre correcto del método
            function procesarDevolucion(formData) {
                if (isProcessing) {
                    console.warn("Hay otra operación en curso, espera...");
                    return;
                }

                isProcessing = true;
                showLoading("Procesando devolución...", "Por favor espere mientras se completa la operación");

                // Convertir FormData a objeto para debugging
                const formDataObj = {};
                formData.forEach((value, key) => {
                    formDataObj[key] = value;
                });

                console.log("Datos del formulario a enviar:", formDataObj);

                // CORREGIDO: Mejorar manejo de parámetros y validaciones
                $.ajax({
                    url: '../../../controllers/devolucion.controller.php',
                    type: 'POST',
                    data: {
                        op: 'registrar', // Parámetro crucial: define la operación a realizar
                        idcita: formDataObj.idcita,
                        idventa: formDataObj.idventa || '',
                        monto: formDataObj.monto,
                        motivo: formDataObj.motivo,
                        observaciones: formDataObj.observaciones || '',
                        metodo: formDataObj.metodo,
                        usuario: formDataObj.usuario
                    },
                    dataType: 'json',
                    timeout: 30000, // 30 segundos máximo
                    success: function(response) {
                        // SIEMPRE ocultar el indicador de carga ANTES de cualquier procesamiento
                        hideLoading();

                        console.log("Respuesta de procesar devolución:", response);

                        if (response && response.status) {
                            // Guardar el número de comprobante para mostrarlo después
                            const numeroComprobante = response.numero_comprobante || response.iddevolucion || '';

                            // Ocultar pasos anteriores
                            $('#info-cita-container').addClass('d-none');
                            $('#devolucion-container').addClass('d-none');

                            // Preparar comprobante de devolución con el número real
                            prepararComprobante(formData, numeroComprobante);

                            // IMPORTANTE: Usar setTimeout para evitar bloqueos de UI
                            setTimeout(() => {
                                $('#confirmacion-container').removeClass('d-none');

                                // Hacer scroll hacia la confirmación
                                $('html, body').animate({
                                    scrollTop: $("#confirmacion-container").offset().top - 100
                                }, 300);

                                // Notificar éxito
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Devolución procesada',
                                    text: 'La devolución se ha procesado correctamente y la cita ha sido cancelada.',
                                    confirmButtonText: 'Entendido'
                                });

                                isProcessing = false;
                            }, 100);
                        } else {
                            // Mostrar error con mensaje específico
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response ? (response.mensaje || 'Ocurrió un error al procesar la devolución.') : 'No se recibió respuesta del servidor.',
                                confirmButtonText: 'Entendido'
                            });

                            isProcessing = false;
                        }
                    },
                    error: function(xhr, status, error) {
                        // Garantizar que se oculte el loader y se limpie el estado
                        hideLoading();
                        isProcessing = false;

                        console.error("Error al procesar devolución:", error);
                        console.error("Estado:", status);
                        console.error("Respuesta del servidor:", xhr.responseText);

                        let errorMessage = 'Ocurrió un error al comunicarse con el servidor.';

                        // Intentar extraer el mensaje de error del servidor si existe
                        try {
                            const responseData = JSON.parse(xhr.responseText);
                            if (responseData && responseData.mensaje) {
                                errorMessage = responseData.mensaje;
                            }
                        } catch (e) {
                            // Si no podemos parsear la respuesta, usar el mensaje genérico
                            if (xhr.responseText) {
                                errorMessage += ' Detalles: ' + xhr.responseText.substring(0, 100);
                            }
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                            confirmButtonText: 'Entendido'
                        });
                    },
                    complete: function() {
                        // CRÍTICO: Garantizar que el loader se oculte siempre
                        hideLoading();
                    }
                });
            }

            // FUNCIÓN MODIFICADA: Preparar el comprobante con el número real
            function prepararComprobante(formData, numeroComprobante) {
                // Fecha y hora actual
                const ahora = new Date();
                const fechaHoraStr = ahora.toLocaleString('es-ES');

                // Nombre del paciente
                const nombrePaciente = citaActual.paciente_nombre && citaActual.paciente_apellido ?
                    `${citaActual.paciente_apellido}, ${citaActual.paciente_nombre}` :
                    (citaActual.paciente || 'No especificado');

                // Documento del paciente
                const documentoPaciente = `${citaActual.documento_tipo || citaActual.tipodoc || 'DOC'}: ${citaActual.documento_numero || citaActual.nrodoc || 'No especificado'}`;

                // Información de la cita
                const fechaCita = formatearFecha(citaActual.fecha);
                const horaCita = formatearHora(citaActual.hora || citaActual.horaprogramada);
                const especialidad = citaActual.especialidad || 'No especificada';

                // Mapeo de motivos
                const motivosMap = {
                    'SOLICITUD_PACIENTE': 'Solicitud del paciente',
                    'EMERGENCIA_MEDICA': 'Emergencia médica',
                    'PROBLEMA_HORARIO': 'Problema de horario',
                    'CANCELACION_DOCTOR': 'Cancelación por parte del doctor',
                    'OTRO': 'Otro motivo'
                };

                // Actualizar comprobante
                $('#fecha-hora-comprobante').text(fechaHoraStr);

                // Usar el número de comprobante real generado por el servidor si está disponible
                if (numeroComprobante) {
                    $('#comprobante-id').text(numeroComprobante);
                } else {
                    // Usar un número generado localmente como respaldo
                    $('#comprobante-id').text(`DEV-${Math.floor(Math.random() * 10000).toString().padStart(4, '0')}`);
                }

                $('#comprobante-paciente').text(nombrePaciente);
                $('#comprobante-documento').text(documentoPaciente);
                $('#comprobante-cita').text(`${fechaCita} - ${horaCita} (${especialidad})`);
                $('#comprobante-monto').text(`S/. ${formData.get('monto')}`);
                $('#comprobante-metodo').text(formData.get('metodo'));
                $('#comprobante-motivo').text(motivosMap[formData.get('motivo')] || formData.get('motivo'));
                $('#comprobante-autorizado').text(formData.get('usuario'));
            }

            // ===== CONFIGURACIÓN DE EVENTOS =====

            // Evento para buscar por documento
            $(document).off('click', '#buscar-documento').on('click', '#buscar-documento', function() {
                const nroDocumento = $('#nro-documento').val().trim();

                if (nroDocumento) {
                    buscarCitasPorDocumento(nroDocumento);
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campo vacío',
                        text: 'Por favor, ingrese un número de documento.'
                    });
                }
            });

            // También permitir Enter en el campo de documento
            $('#nro-documento').off('keypress').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#buscar-documento').click();
                }
            });

            // Evento para buscar por ID de cita
            $(document).off('click', '#buscar-cita').on('click', '#buscar-cita', function() {
                const idCita = $('#id-cita').val().trim();

                if (idCita) {
                    buscarCitaPorId(idCita);
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campo vacío',
                        text: 'Por favor, ingrese un ID de cita.'
                    });
                }
            });

            // También permitir Enter en el campo de ID
            $('#id-cita').off('keypress').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#buscar-cita').click();
                }
            });

            // Evento para cancelar el proceso
            $(document).off('click', '#btn-cancelar-proceso').on('click', '#btn-cancelar-proceso', function() {
                Swal.fire({
                    title: '¿Cancelar proceso?',
                    text: "¿Está seguro que desea cancelar el proceso de devolución?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, cancelar proceso',
                    cancelButtonText: 'No, continuar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redireccionar al listado de citas
                        window.location.href = '../../Citas/GestionarCita/gestionarCita.php';
                    }
                });
            });

            // Evento para procesar la devolución
            $(document).off('submit', '#form-devolucion').on('submit', '#form-devolucion', function(e) {
                e.preventDefault();

                if (isProcessing) {
                    console.warn("Hay otra operación en curso, espera...");
                    return;
                }

                // NUEVO: Verificar que el formulario tenga los campos correctos
                const idcita = $('#idcita-devolucion').val();
                const motivo = $('#motivo-devolucion').val();
                const monto = $('#monto-devolucion').val();
                const metodo = $('#metodo-devolucion').val();

                // Validación adicional para evitar envíos con datos incompletos
                if (!idcita || !motivo || !monto || !metodo) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Datos incompletos',
                        text: 'Por favor complete todos los campos requeridos.'
                    });
                    return;
                }

                // Validar que todos los campos requeridos estén completos
                if (this.checkValidity()) {
                    // Obtener datos del formulario
                    const formData = new FormData(this);

                    // Log para depuración
                    console.log("Validación del formulario exitosa, datos a enviar:");
                    formData.forEach((value, key) => {
                        console.log(`${key}: ${value}`);
                    });

                    // Confirmar antes de procesar
                    Swal.fire({
                        title: 'Confirmar devolución',
                        text: `¿Está seguro que desea procesar la devolución por S/. ${formData.get('monto')}?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, procesar devolución',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            procesarDevolucion(formData);
                        }
                    });
                } else {
                    // Mostrar validación nativa del navegador
                    this.reportValidity();
                }
            });

            // Evento para imprimir comprobante
            $(document).off('click', '#btn-imprimir-comprobante').on('click', '#btn-imprimir-comprobante', function() {
                window.print();
            });

            // ===== INICIALIZACIÓN =====

            // Activar el sistema de monitoreo de rendimiento
            setupPerfMonitor();

            // Si hay un ID de cita en la URL, buscar automáticamente
            const urlParams = new URLSearchParams(window.location.search);
            const idCitaParam = urlParams.get('idcita');

            if (idCitaParam) {
                // Pequeño retraso para asegurar que la página se haya cargado completamente
                setTimeout(function() {
                    $('#id-cita').val(idCitaParam);
                    $('#buscar-cita').click();
                }, 500);
            }

            // Prevenir bloqueos por eventos
            $(document).on('keydown', function(e) {
                // Si ESC se presiona y hay procesamiento activo, cancelar
                if (e.key === 'Escape' && isProcessing) {
                    console.log("ESC presionado durante procesamiento - Cancelando operaciones");
                    hideLoading();
                    isProcessing = false;
                }
            });

            console.log("Inicialización completa - Sistema listo para operar");
        });
    </script>
</body>

</html>