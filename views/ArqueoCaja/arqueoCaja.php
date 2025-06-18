<?php /*RUTA: sistemasclinica/views/ArqueoCaja/arqueoCaja.php*/ ?>
<?php
require_once '../include/header.administrador.php';
?>

<div class="container mt-4">
    <!-- Tarjeta principal -->
    <div class="card shadow mb-4">
        <!-- Header sin botón PDF -->
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h4 class="m-0 font-weight-bold text-primary" style="font-size: 28px; letter-spacing: 0.5px; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);">
                <i class="fas fa-cash-register mr-2"></i>Arqueo de Caja
            </h4>
        </div>
        <div class="card-body">
            <!-- Información general -->
            <div class="row my-4">
                <div class="col-md-12 mb-3">
                    <h5 class="text-info">
                        <i class="fas fa-info-circle"></i> Información general
                    </h5>
                </div>
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="font-weight-bold">
                                <i class="fas fa-user"></i> Presentado por
                            </label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" id="presentadoPor"
                                value="<?php echo isset($_SESSION['usuario']['nombres']) ? $_SESSION['usuario']['nombres'] . ' ' . $_SESSION['usuario']['apellidos'] : ''; ?>" readonly style="background-color: #f2f2f2;">
                        </div>
                    </div>
                    <!-- Campo hora inicio oculto inicialmente y sin botón toggle -->
                    <div class="row mb-3" id="horaInicioContainer" style="display: none;">
                        <div class="col-md-4">
                            <label class="font-weight-bold">
                                <i class="fas fa-clock"></i> Hora inicio
                            </label>
                        </div>
                        <div class="col-md-8">
                            <input type="time" class="form-control" id="horaInicio" value="08:00" readonly style="background-color: #f2f2f2;">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- CORREGIDO: Campo fecha con espaciado ampliado -->
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label class="font-weight-bold">
                                <i class="fas fa-calendar-alt"></i> Fecha
                            </label>
                        </div>
                        <div class="col-md-8">
                            <!-- CORREGIDO: Cambiado de col-md-4 a col-md-6 para ocupar más espacio -->
                            <input type="date" class="form-control" id="fecha" readonly style="background-color: #f2f2f2;">
                        </div>
                    </div>
                    <div class="row mb-3" id="horaCierreContainer" style="display:none;">
                        <div class="col-md-4">
                            <label class="font-weight-bold">
                                <i class="fas fa-clock"></i> Hora cierre
                            </label>
                        </div>
                        <div class="col-md-8">
                            <input type="time" class="form-control" id="horaCierre" value="18:00">
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label class="font-weight-bold">
                                <i class="fas fa-comment"></i> Observaciones
                            </label>
                        </div>
                        <div class="col-md-10">
                            <textarea class="form-control" id="observaciones" rows="3"
                                placeholder="Escriba aquí observaciones o notas adicionales sobre el arqueo de caja..."></textarea>
                            <small class="text-muted">Máximo 500 caracteres</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILA 1: Saldo Inicial, Saldo Movimientos y Saldo Total -->
            <div class="row" id="contenedorDatos" style="display: none;">
                <!-- Saldo Dejado Anteriormente - AMARILLO -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-warning text-white py-2">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-coins"></i> Saldo Dejado Anteriormente
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-center py-4" style="background-color: #f2f2f2;">
                                                <h2 class="text-warning mb-0" id="saldoRestante">S/ 0.00</h2>
                                                <input type="hidden" id="saldoInicial" value="0">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Saldo de Movimientos - AZUL -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-info text-white py-2">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-history"></i> Saldo de Movimientos
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-center py-4" style="background-color: #f2f2f2;">
                                                <h2 class="text-info mb-0" id="saldoMovimientosDisplay">S/ 0.00</h2>
                                                <input type="hidden" id="saldoMovimientosValor" value="0">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Saldo Total - VERDE -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-success text-white py-2">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-wallet"></i> Saldo Total
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-center py-4" style="background-color: #f2f2f2;">
                                                <h2 class="text-success mb-0" id="saldoTotalDisplay">S/ 0.00</h2>
                                                <input type="hidden" id="saldoTotal" value="0">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILA 2: Ingresos y Egresos -->
            <div class="row" id="contenedorMovimientos" style="display: none;">
                <!-- Ingresos (Reservaciones) -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white py-2">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-arrow-down"></i> Ingresos (Reservaciones)
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <tbody>
                                        <tr>
                                            <td>Transferencia</td>
                                            <td class="text-right text-success" id="ingresoTransferencia" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Plin</td>
                                            <td class="text-right text-success" id="ingresoPlin" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Yape</td>
                                            <td class="text-right text-success" id="ingresoYape" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Efectivo</td>
                                            <td class="text-right text-success" id="ingresoEfectivo" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Egresos (Devoluciones) -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-danger text-white py-2">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-arrow-up"></i> Egresos (Devoluciones)
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <tbody>
                                        <tr>
                                            <td>Transferencia</td>
                                            <td class="text-right text-danger" id="egresoTransferencia" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Plin</td>
                                            <td class="text-right text-danger" id="egresoPlin" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Yape</td>
                                            <td class="text-right text-danger" id="egresoYape" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Efectivo</td>
                                            <td class="text-right text-danger" id="egresoEfectivo" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen -->
            <div class="row mt-5" id="contenedorResumen" style="display: none;">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white py-2">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-list"></i> Resumen
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- Resumen detallado de ingresos -->
                            <h6 class="font-weight-bold mb-3"><i class="fas fa-arrow-down text-primary"></i> Detalle de Ingresos (Reservaciones)</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Tipo de ingreso</th>
                                            <th class="text-right">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Efectivo</td>
                                            <td class="text-right text-success" id="resumenEfectivo" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Transferencia</td>
                                            <td class="text-right text-success" id="resumenTransferencia" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Plin</td>
                                            <td class="text-right text-success" id="resumenPlin" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Yape</td>
                                            <td class="text-right text-success" id="resumenYape" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr class="bg-light">
                                            <td class="font-weight-bold">Total ingresos</td>
                                            <td class="text-right text-success font-weight-bold" id="resumenTotalIngresos" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Resumen detallado de egresos (devoluciones) -->
                            <h6 class="font-weight-bold mb-3"><i class="fas fa-arrow-up text-danger"></i> Detalle de Egresos (Devoluciones)</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Tipo de egreso</th>
                                            <th class="text-right">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Efectivo</td>
                                            <td class="text-right text-danger" id="resumenEgresoEfectivo" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Transferencia</td>
                                            <td class="text-right text-danger" id="resumenEgresoTransferencia" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Plin</td>
                                            <td class="text-right text-danger" id="resumenEgresoPlin" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Yape</td>
                                            <td class="text-right text-danger" id="resumenEgresoYape" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr class="bg-light">
                                            <td class="font-weight-bold">Total egresos</td>
                                            <td class="text-right text-danger font-weight-bold" id="resumenTotalEgresos" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Balance final -->
                            <h6 class="font-weight-bold mb-3"><i class="fas fa-balance-scale"></i> Balance Final</h6>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <tbody>
                                        <tr>
                                            <td>Saldo dejado anteriormente en efectivo</td>
                                            <td class="text-right" id="saldoAnterior" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Ingreso diario efectivo (Reservaciones)</td>
                                            <td class="text-right text-success" id="ingresoDiario" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Egreso diario efectivo (Devoluciones)</td>
                                            <td class="text-right text-danger" id="egresoDiario" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Saldo de movimientos</td>
                                            <td class="text-right" id="saldoMovimientos" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr class="table-active">
                                            <td class="font-weight-bold">Saldo total en caja</td>
                                            <td class="text-right font-weight-bold" id="totalEfectivoCaja" style="background-color: #f2f2f2;">S/ 0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Otros medios de pago</td>
                                            <td class="text-right text-muted" id="otrosAportes" style="background-color: #f2f2f2;">Transferencia, Plin, Yape</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="row mt-5 mb-4">
                <div class="col-md-12 text-center">
                    <button id="btnGuardarArqueo" class="btn btn-success btn-lg">
                        <i class="fas fa-door-open"></i> Abrir Caja
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JAVASCRIPT COMPLETAMENTE CORREGIDO con actualización automática -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Variables globales
        let ingresos = {
            transferencia: 0,
            plin: 0,
            yape: 0,
            efectivo: 0
        };
        let egresos = {
            transferencia: 0,
            plin: 0,
            yape: 0,
            efectivo: 0
        };
        let saldoInicial = 0;
        let saldoAnterior = 0;
        let saldoMovimientos = 0;
        let saldoTotal = 0;
        let idArqueoCajaActual = null;
        let horaUltimoCierre = null;

        // NUEVAS VARIABLES: Para actualización automática
        let intervaloActualizacion = null;
        let ultimaActualizacion = null;

        // Verificación de SweetAlert2
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 no está definido. Cargando desde CDN...');
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            script.async = true;
            script.onload = function() {
                console.log('SweetAlert2 cargado correctamente');
                inicializarEventos();
                cargarDatosIniciales();
            };
            document.head.appendChild(script);
        } else {
            console.log('SweetAlert2 ya está disponible');
            inicializarEventos();
            cargarDatosIniciales();
        }

        // NUEVA FUNCIÓN: Iniciar actualización automática
        function iniciarActualizacionAutomatica() {
            console.log("Iniciando actualización automática de movimientos...");
            
            // Detener cualquier intervalo previo
            if (intervaloActualizacion) {
                clearInterval(intervaloActualizacion);
            }
            
            // Crear nuevo intervalo que se ejecute cada 15 segundos
            intervaloActualizacion = setInterval(() => {
                if (idArqueoCajaActual) {
                    console.log("Verificando movimientos automáticamente...");
                    verificarYActualizarMovimientos();
                }
            }, 15000); // 15 segundos
            
            console.log("Actualización automática iniciada (cada 15 segundos)");
            mostrarIndicadorActualizacion();
        }

        // NUEVA FUNCIÓN: Detener actualización automática
        function detenerActualizacionAutomatica() {
            console.log("Deteniendo actualización automática...");
            
            if (intervaloActualizacion) {
                clearInterval(intervaloActualizacion);
                intervaloActualizacion = null;
                console.log("Actualización automática detenida");
            }
            ocultarIndicadorActualizacion();
        }

        // NUEVA FUNCIÓN: Verificar y actualizar movimientos automáticamente
        function verificarYActualizarMovimientos() {
            if (!idArqueoCajaActual) {
                console.log("No hay caja abierta, saltando actualización automática");
                return;
            }
            
            console.log("Verificando movimientos para arqueo ID:", idArqueoCajaActual);
            
            // Obtener la hora de apertura del arqueo actual
            const horaApertura = document.getElementById('horaInicio').value;
            
            if (!horaApertura) {
                console.log("No se encontró hora de apertura, saltando actualización");
                return;
            }
            
            // Cargar movimientos actualizados silenciosamente
            cargarMovimientosSilencioso(horaApertura);
        }

        // NUEVA FUNCIÓN: Cargar movimientos sin logs excesivos
        function cargarMovimientosSilencioso(horaFiltro) {
            const fechaActual = document.getElementById('fecha').value;
            
            // Cargar ingresos y egresos en paralelo
            Promise.all([
                cargarIngresosSilencioso(fechaActual, horaFiltro),
                cargarEgresosSilencioso(fechaActual, horaFiltro)
            ]).then(() => {
                // Actualizar resumen después de cargar ambos
                actualizarResumen();
                console.log("Movimientos actualizados automáticamente");
            }).catch(error => {
                console.error("Error en actualización automática:", error);
            });
        }

        // NUEVA FUNCIÓN: Cargar ingresos silenciosamente
        function cargarIngresosSilencioso(fecha, horaFiltro) {
            return new Promise((resolve, reject) => {
                let url = `../../controllers/arqueocaja.controller.php?op=listar_ingresos_reservaciones&fecha=${fecha}`;
                
                if (idArqueoCajaActual) {
                    url += `&idarqueo=${idArqueoCajaActual}`;
                }
                
                if (horaFiltro) {
                    url += `&hora_inicio=${horaFiltro}`;
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        // Resetear ingresos
                        ingresos = {
                            transferencia: 0,
                            plin: 0,
                            yape: 0,
                            efectivo: 0
                        };
                        
                        if (data.status && data.ingresos && data.ingresos.length > 0) {
                            data.ingresos.forEach(ingreso => {
                                const tipoPago = ingreso.tipopago ? ingreso.tipopago.toUpperCase() : '';
                                const total = parseFloat(ingreso.total || 0);
                                
                                switch (tipoPago) {
                                    case 'EFECTIVO':
                                        ingresos.efectivo = total;
                                        break;
                                    case 'TRANSFERENCIA':
                                    case 'TARJETA':
                                        ingresos.transferencia = total;
                                        break;
                                    case 'YAPE':
                                        ingresos.yape = total;
                                        break;
                                    case 'PLIN':
                                        ingresos.plin = total;
                                        break;
                                }
                            });
                        }
                        
                        // Actualizar interfaz de ingresos
                        document.getElementById('ingresoEfectivo').textContent = formatearMonto(ingresos.efectivo);
                        document.getElementById('ingresoTransferencia').textContent = formatearMonto(ingresos.transferencia);
                        document.getElementById('ingresoYape').textContent = formatearMonto(ingresos.yape);
                        document.getElementById('ingresoPlin').textContent = formatearMonto(ingresos.plin);
                        
                        resolve();
                    })
                    .catch(error => {
                        console.error('Error al cargar ingresos silenciosamente:', error);
                        reject(error);
                    });
            });
        }

        // NUEVA FUNCIÓN: Cargar egresos silenciosamente
        function cargarEgresosSilencioso(fecha, horaFiltro) {
            return new Promise((resolve, reject) => {
                let url = `../../controllers/arqueocaja.controller.php?op=listar_egresos_devoluciones&fecha=${fecha}`;
                
                if (idArqueoCajaActual) {
                    url += `&idarqueo=${idArqueoCajaActual}`;
                }
                
                if (horaFiltro) {
                    url += `&hora_inicio=${horaFiltro}`;
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        // Resetear egresos
                        egresos = {
                            transferencia: 0,
                            plin: 0,
                            yape: 0,
                            efectivo: 0
                        };
                        
                        if (data.status && data.egresos && data.egresos.length > 0) {
                            data.egresos.forEach(egreso => {
                                const tipoPago = egreso.tipopago ? egreso.tipopago.toUpperCase() : '';
                                const total = parseFloat(egreso.total || 0);
                                
                                switch (tipoPago) {
                                    case 'EFECTIVO':
                                        egresos.efectivo = total;
                                        break;
                                    case 'TRANSFERENCIA':
                                    case 'TARJETA':
                                        egresos.transferencia = total;
                                        break;
                                    case 'YAPE':
                                        egresos.yape = total;
                                        break;
                                    case 'PLIN':
                                        egresos.plin = total;
                                        break;
                                }
                            });
                        }
                        
                        // Actualizar interfaz de egresos
                        document.getElementById('egresoEfectivo').textContent = formatearMonto(egresos.efectivo);
                        document.getElementById('egresoTransferencia').textContent = formatearMonto(egresos.transferencia);
                        document.getElementById('egresoYape').textContent = formatearMonto(egresos.yape);
                        document.getElementById('egresoPlin').textContent = formatearMonto(egresos.plin);
                        
                        resolve();
                    })
                    .catch(error => {
                        console.error('Error al cargar egresos silenciosamente:', error);
                        reject(error);
                    });
            });
        }

        // NUEVA FUNCIÓN: Manejar visibilidad de la pestaña
        function manejarVisibilidadPestana() {
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    console.log("Pestaña oculta - manteniendo actualización automática");
                } else {
                    console.log("Pestaña visible - verificando movimientos inmediatamente");
                    if (idArqueoCajaActual) {
                        verificarYActualizarMovimientos();
                    }
                }
            });
        }

        // NUEVA FUNCIÓN: Mostrar indicador visual de actualización automática
        function mostrarIndicadorActualizacion() {
            const header = document.querySelector('.card-header');
            if (header && !document.getElementById('indicadorActualizacion')) {
                const indicador = document.createElement('div');
                indicador.id = 'indicadorActualizacion';
                indicador.innerHTML = `
                    <small class="text-success">
                        <i class="fas fa-sync-alt fa-spin"></i> 
                        Actualización automática activa
                    </small>
                `;
                indicador.style.fontSize = '0.8em';
                header.appendChild(indicador);
            }
        }

        // NUEVA FUNCIÓN: Ocultar indicador visual de actualización automática
        function ocultarIndicadorActualizacion() {
            const indicador = document.getElementById('indicadorActualizacion');
            if (indicador) {
                indicador.remove();
            }
        }

        // FUNCIÓN CORREGIDA: Bloquear interfaz y detener actualización automática
        function bloquearInterfaz() {
            console.log('Bloqueando interfaz - caja cerrada');
            document.getElementById('contenedorDatos').style.display = 'none';
            document.getElementById('contenedorMovimientos').style.display = 'none';
            document.getElementById('contenedorResumen').style.display = 'none';
            document.getElementById('horaInicioContainer').style.display = 'none';
            
            // Detener actualización automática cuando se cierra la caja
            detenerActualizacionAutomatica();
        }

        // FUNCIÓN CORREGIDA: Desbloquear interfaz e iniciar actualización automática
        function desbloquearInterfaz() {
            console.log('Desbloqueando interfaz - caja abierta');
            document.getElementById('contenedorDatos').style.display = '';
            document.getElementById('contenedorMovimientos').style.display = '';
            document.getElementById('contenedorResumen').style.display = '';
            document.getElementById('horaInicioContainer').style.display = '';
            
            // Iniciar actualización automática cuando se abre la caja
            iniciarActualizacionAutomatica();
        }

        // Inicializar eventos
        function inicializarEventos() {
            console.log('Inicializando eventos...');
            const btnGuardarArqueo = document.getElementById('btnGuardarArqueo');
            if (btnGuardarArqueo) {
                btnGuardarArqueo.addEventListener('click', manejarAccionArqueo);
                console.log('Evento click agregado a btnGuardarArqueo');
            } else {
                console.error('No se encontró el elemento btnGuardarArqueo');
            }

            // Obtener fecha actual del sistema mediante JavaScript
            establecerFechaActual();
            configurarEstilosIniciales();
            manejarVisibilidadPestana(); // NUEVO: Inicializar manejo de visibilidad
            console.log('Eventos inicializados correctamente');
        }

        // Función para manejar la acción del botón según el estado de la caja
        function manejarAccionArqueo() {
            console.log('Función manejarAccionArqueo ejecutada');
            console.log('Estado actual de la caja:', idArqueoCajaActual ? 'Abierta' : 'Cerrada');

            if (!idArqueoCajaActual) {
                console.log('Intentando abrir caja...');
                confirmarAbrirCaja();
            } else {
                console.log('Intentando cerrar caja...');
                confirmarCerrarCaja();
            }
        }

        // Función para confirmar apertura de caja
        function confirmarAbrirCaja() {
            console.log('Ejecutando confirmarAbrirCaja');
            if (typeof Swal === 'undefined') {
                console.error('SweetAlert2 no está disponible. Usando confirm nativo.');
                if (confirm('¿Desea abrir la caja? Se registrará la apertura de caja con la fecha y hora actual')) {
                    abrirCaja();
                }
                return;
            }

            Swal.fire({
                title: '¿Desea abrir la caja?',
                text: 'Se registrará la apertura de caja con la fecha y hora actual',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, abrir caja',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                console.log('Resultado de confirmación:', result);
                if (result.isConfirmed) {
                    console.log('Confirmación aceptada, procediendo a abrir caja');
                    abrirCaja();
                } else {
                    console.log('Apertura de caja cancelada por el usuario');
                }
            }).catch(error => {
                console.error('Error en SweetAlert:', error);
                if (confirm('¿Desea abrir la caja? Se registrará la apertura de caja con la fecha y hora actual')) {
                    abrirCaja();
                }
            });
        }

        // Modal de cierre mejorado
        function confirmarCerrarCaja() {
            console.log('Ejecutando confirmarCerrarCaja');
            const now = new Date();
            const horaActual = now.getHours().toString().padStart(2, '0') + ':' +
                now.getMinutes().toString().padStart(2, '0');

            if (typeof Swal === 'undefined') {
                console.error('SweetAlert2 no está disponible. Usando prompt nativo.');
                const horaCierre = prompt('Ingrese la hora de cierre (HH:MM):', horaActual);
                if (horaCierre) {
                    const dineroADejar = prompt('Ingrese el dinero a dejar para mañana:', '0');
                    if (dineroADejar !== null) {
                        cerrarCajaConDatos(horaCierre, parseFloat(dineroADejar || 0));
                    }
                }
                return;
            }

            Swal.fire({
                title: '¿Desea cerrar la caja?',
                html: `
                <p>Se registrará el cierre de caja con los datos actuales.</p>
                
                <div class="form-group text-left mb-3">
                    <label for="swal-hora-cierre" class="font-weight-bold">Hora de cierre:</label>
                    <input id="swal-hora-cierre" type="time" class="form-control" value="${horaActual}" readonly style="background-color: #f2f2f2;">
                </div>
                
                <div class="form-group text-left mb-3">
                    <label for="swal-monto-total" class="font-weight-bold">Monto total en caja:</label>
                    <input id="swal-monto-total" type="text" class="form-control" value="S/ ${saldoTotal.toFixed(2)}" readonly style="background-color: #f2f2f2;">
                </div>
                
                <div class="form-group text-left mb-3">
                    <label for="swal-dinero-dejar" class="font-weight-bold">Dinero a dejar para mañana:</label>
                    <input id="swal-dinero-dejar" type="number" class="form-control" placeholder="0.00" min="0" max="${saldoTotal}" step="0.01">
                    <small class="text-muted">Máximo: S/ ${saldoTotal.toFixed(2)}</small>
                </div>
            `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cerrar caja',
                cancelButtonText: 'Cancelar',
                width: '500px',
                preConfirm: () => {
                    const horaCierre = document.getElementById('swal-hora-cierre').value;
                    const dineroADejar = parseFloat(document.getElementById('swal-dinero-dejar').value || 0);

                    if (!horaCierre) {
                        Swal.showValidationMessage('Debe ingresar la hora de cierre');
                        return false;
                    }

                    if (dineroADejar < 0) {
                        Swal.showValidationMessage('El dinero a dejar no puede ser negativo');
                        return false;
                    }

                    if (dineroADejar > saldoTotal) {
                        Swal.showValidationMessage(`El dinero a dejar no puede ser mayor al monto total (S/ ${saldoTotal.toFixed(2)})`);
                        return false;
                    }

                    return {
                        horaCierre,
                        dineroADejar
                    };
                }
            }).then((result) => {
                console.log("Resultado de confirmación:", result);
                if (result.isConfirmed) {
                    console.log("Confirmación aceptada, procediendo a cerrar caja");
                    cerrarCajaConDatos(result.value.horaCierre, result.value.dineroADejar);
                } else {
                    console.log("Cierre de caja cancelado por el usuario");
                }
            }).catch(error => {
                console.error('Error en SweetAlert:', error);
                const horaCierre = prompt('Ingrese la hora de cierre (HH:MM):', horaActual);
                if (horaCierre) {
                    const dineroADejar = prompt('Ingrese el dinero a dejar para mañana:', '0');
                    if (dineroADejar !== null) {
                        cerrarCajaConDatos(horaCierre, parseFloat(dineroADejar || 0));
                    }
                }
            });
        }

        // FUNCIÓN CORREGIDA: Cerrar caja con datos adicionales y detener actualización
        function cerrarCajaConDatos(horaCierre, dineroADejar) {
            console.log("Cerrando caja con hora:", horaCierre, "y dinero a dejar:", dineroADejar);
            if (!idArqueoCajaActual) {
                mostrarAlerta('No hay un arqueo abierto para cerrar', 'warning');
                return;
            }

            const datos = {
                idarqueo: idArqueoCajaActual,
                fecha_cierre: document.getElementById('fecha').value,
                hora_cierre: horaCierre,
                saldo_real: saldoTotal,
                saldo_a_dejar: dineroADejar,
                diferencia: 0,
                observaciones: document.getElementById('observaciones').value
            };

            console.log("Datos para cerrar arqueo:", datos);

            fetch('../../controllers/arqueocaja.controller.php?op=cerrar_caja', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(datos)
                })
                .then(response => {
                    console.log("Respuesta recibida del servidor:", response);
                    return response.json();
                })
                .then(data => {
                    console.log("Respuesta al cerrar arqueo:", data);
                    if (data.status) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Caja cerrada exitosamente',
                                text: 'La caja ha sido cerrada correctamente. La actualización automática se ha detenido.',
                                icon: 'success',
                                confirmButtonColor: '#28a745',
                                timer: 3000
                            });
                        } else {
                            alert('Caja cerrada exitosamente');
                        }

                        horaUltimoCierre = horaCierre;
                        console.log("Nueva hora de cierre establecida para filtrado:", horaUltimoCierre);

                        idArqueoCajaActual = null;
                        actualizarBotonSegunEstado(false);
                        bloquearInterfaz(); // Esto incluye detener actualización automática
                        resetearTodosDatos();
                    } else {
                        mostrarAlerta(data.mensaje || 'Error al cerrar el arqueo', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error al cerrar arqueo:', error);
                    mostrarAlerta('Error al procesar la solicitud', 'error');
                });
        }

        // Función para actualizar el botón según estado de la caja
        function actualizarBotonSegunEstado(cajaAbierta) {
            console.log('Actualizando estado del botón:', cajaAbierta ? 'Caja Abierta' : 'Caja Cerrada');
            const btnGuardarArqueo = document.getElementById('btnGuardarArqueo');

            if (!btnGuardarArqueo) {
                console.error('No se encontró el elemento btnGuardarArqueo');
                return;
            }

            if (cajaAbierta) {
                btnGuardarArqueo.innerHTML = '<i class="fas fa-door-closed"></i> Guardar y Cerrar Caja';
                btnGuardarArqueo.classList.remove('btn-success');
                btnGuardarArqueo.classList.add('btn-danger');
                console.log('Botón actualizado a: Guardar y Cerrar Caja');
            } else {
                btnGuardarArqueo.innerHTML = '<i class="fas fa-door-open"></i> Abrir Caja';
                btnGuardarArqueo.classList.remove('btn-danger');
                btnGuardarArqueo.classList.add('btn-success');
                console.log('Botón actualizado a: Abrir Caja');
            }
        }

        // Configurar estilos iniciales simplificada
        function configurarEstilosIniciales() {
            const horaInicioInput = document.getElementById('horaInicio');
            const horaCierreInput = document.getElementById('horaCierre');

            if (!horaInicioInput || !horaCierreInput) {
                console.error('No se encontraron los elementos de hora');
                return;
            }

            aplicarEstiloCampoBloqueado(horaInicioInput);
            aplicarEstiloCampoDesbloqueado(horaCierreInput);
        }

        function aplicarEstiloCampoBloqueado(input) {
            if (!input) return;
            input.style.backgroundColor = '#f2f2f2';
            input.style.color = '#6c757d';
        }

        function aplicarEstiloCampoDesbloqueado(input) {
            if (!input) return;
            input.style.backgroundColor = '#ffffff';
            input.style.color = '#495057';
        }

        function establecerFechaActual() {
            const fechaHoy = new Date();
            const fechaInput = document.getElementById('fecha');

            if (!fechaInput) {
                console.error('No se encontró el elemento fecha');
                return null;
            }

            const año = fechaHoy.getFullYear();
            const mes = String(fechaHoy.getMonth() + 1).padStart(2, '0');
            const dia = String(fechaHoy.getDate()).padStart(2, '0');
            const fechaFormateada = `${año}-${mes}-${dia}`;

            console.log("Estableciendo fecha actual:", fechaFormateada);
            fechaInput.value = fechaFormateada;
            return fechaFormateada;
        }

        function establecerHoraActual() {
            const now = new Date();
            const horaActual = now.getHours().toString().padStart(2, '0') + ':' +
                now.getMinutes().toString().padStart(2, '0');

            const horaInicioInput = document.getElementById('horaInicio');
            if (horaInicioInput) {
                horaInicioInput.value = horaActual;
                console.log("Hora de inicio establecida a:", horaActual);
            }

            return horaActual;
        }

        function resetearTodosDatos() {
            console.log("Reseteando todos los datos");

            ingresos = {
                transferencia: 0,
                plin: 0,
                yape: 0,
                efectivo: 0
            };
            egresos = {
                transferencia: 0,
                plin: 0,
                yape: 0,
                efectivo: 0
            };
            saldoInicial = 0;
            saldoAnterior = 0;
            saldoMovimientos = 0;
            saldoTotal = 0;

            document.getElementById('saldoInicial').value = 0;
            document.getElementById('saldoRestante').textContent = formatearMonto(0);
            document.getElementById('saldoMovimientosValor').value = 0;
            document.getElementById('saldoMovimientosDisplay').textContent = formatearMonto(0);
            document.getElementById('saldoTotal').value = 0;
            document.getElementById('saldoTotalDisplay').textContent = formatearMonto(0);

            resetearIngresosEgresos();
            document.getElementById('observaciones').value = '';
        }

        function actualizarSaldoTotal() {
            const saldoDejadoAnterior = parseFloat(document.getElementById('saldoInicial').value || 0);
            const saldoMovimientos = parseFloat(document.getElementById('saldoMovimientosValor').value || 0);
            saldoTotal = saldoDejadoAnterior + saldoMovimientos;
            document.getElementById('saldoTotal').value = saldoTotal.toFixed(2);
            document.getElementById('saldoTotalDisplay').textContent = formatearMonto(saldoTotal);
        }

        function resetearIngresosEgresos() {
            ingresos = {
                transferencia: 0,
                plin: 0,
                yape: 0,
                efectivo: 0
            };
            egresos = {
                transferencia: 0,
                plin: 0,
                yape: 0,
                efectivo: 0
            };

            document.getElementById('ingresoEfectivo').textContent = formatearMonto(0);
            document.getElementById('ingresoTransferencia').textContent = formatearMonto(0);
            document.getElementById('ingresoYape').textContent = formatearMonto(0);
            document.getElementById('ingresoPlin').textContent = formatearMonto(0);

            document.getElementById('egresoEfectivo').textContent = formatearMonto(0);
            document.getElementById('egresoTransferencia').textContent = formatearMonto(0);
            document.getElementById('egresoYape').textContent = formatearMonto(0);
            document.getElementById('egresoPlin').textContent = formatearMonto(0);

            actualizarResumen();
        }

        function obtenerHoraUltimoCierre() {
            return new Promise((resolve, reject) => {
                fetch('../../controllers/arqueocaja.controller.php?op=obtener_hora_ultimo_cierre')
                    .then(response => response.json())
                    .then(data => {
                        console.log("Hora del último cierre:", data);
                        if (data.status && data.hora_cierre) {
                            horaUltimoCierre = data.hora_cierre;
                            resolve(data.hora_cierre);
                        } else {
                            horaUltimoCierre = null;
                            resolve(null);
                        }
                    })
                    .catch(error => {
                        console.error("Error al obtener hora del último cierre:", error);
                        horaUltimoCierre = null;
                        resolve(null);
                    });
            });
        }

        // FUNCIÓN CORREGIDA: Cargar datos iniciales con manejo de actualización automática
        function cargarDatosIniciales() {
            console.log("Cargando datos iniciales...");

            obtenerHoraUltimoCierre().then(() => {
                    return fetch('../../controllers/arqueocaja.controller.php?op=verificar_estado');
                })
                .then(response => {
                    console.log("Respuesta recibida:", response);
                    return response.json();
                })
                .then(data => {
                    console.log("Datos de verificación de estado:", data);

                    if (data.status && data.estado === 'ABIERTO') {
                        console.log("Arqueo abierto encontrado:", data);
                        idArqueoCajaActual = data.idarqueo;

                        saldoInicial = parseFloat(data.monto_inicial || 0);
                        saldoAnterior = parseFloat(data.saldo_anterior || 0);

                        console.log("Saldo inicial (dejado anteriormente):", saldoInicial);
                        console.log("Saldo anterior:", saldoAnterior);

                        document.getElementById('saldoInicial').value = saldoInicial;
                        document.getElementById('saldoRestante').textContent = formatearMonto(saldoInicial);
                        document.getElementById('horaInicio').value = data.hora_apertura;

                        actualizarBotonSegunEstado(true);
                        desbloquearInterfaz(); // Esto iniciará la actualización automática

                        console.log("CORREGIDO: Cargando movimientos desde la apertura de este arqueo:", data.hora_apertura);
                        cargarIngresos(data.hora_apertura);
                        cargarEgresos(data.hora_apertura);

                    } else {
                        console.log("No hay arqueo abierto");
                        actualizarBotonSegunEstado(false);
                        bloquearInterfaz();
                        resetearTodosDatos();
                        obtenerUltimoSaldoDisponible();
                    }
                })
                .catch(error => {
                    console.error('Error al verificar estado:', error);
                    mostrarAlerta('Error al cargar datos iniciales', 'error');
                    actualizarBotonSegunEstado(false);
                    bloquearInterfaz();
                    resetearTodosDatos();
                });
        }

        function obtenerUltimoSaldoDisponible() {
            fetch('../../controllers/arqueocaja.controller.php?op=obtener_ultimo_saldo')
                .then(response => response.json())
                .then(data => {
                    console.log("Datos de último saldo disponible:", data);
                    if (data.status) {
                        const ultimoSaldo = parseFloat(data.saldo_final || 0);
                        console.log("Último saldo disponible para próxima apertura:", ultimoSaldo);

                        document.getElementById('saldoRestante').textContent = formatearMonto(ultimoSaldo);

                        if (data.hora_cierre) {
                            horaUltimoCierre = data.hora_cierre;
                            console.log("Hora del último cierre almacenada:", horaUltimoCierre);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error al obtener último saldo disponible:', error);
                });
        }

        function cargarIngresos(horaFiltro = null) {
            console.log("Cargando ingresos...");
            const fechaActual = document.getElementById('fecha').value;
            const horaParaFiltrar = horaFiltro || horaUltimoCierre;
            console.log("Hora para filtrar ingresos:", horaParaFiltrar);

            let url = `../../controllers/arqueocaja.controller.php?op=listar_ingresos_reservaciones&fecha=${fechaActual}`;

            if (idArqueoCajaActual) {
                url += `&idarqueo=${idArqueoCajaActual}`;
            }

            if (horaParaFiltrar) {
                url += `&hora_inicio=${horaParaFiltrar}`;
                console.log("Filtrando ingresos desde la hora:", horaParaFiltrar);
            }

            console.log("URL para cargar ingresos:", url);

            fetch(url)
                .then(response => {
                    console.log("Respuesta de ingresos recibida");
                    return response.json();
                })
                .then(data => {
                    console.log("Datos de ingresos:", data);
                    ingresos = {
                        transferencia: 0,
                        plin: 0,
                        yape: 0,
                        efectivo: 0
                    };

                    document.getElementById('ingresoEfectivo').textContent = formatearMonto(0);
                    document.getElementById('ingresoTransferencia').textContent = formatearMonto(0);
                    document.getElementById('ingresoYape').textContent = formatearMonto(0);
                    document.getElementById('ingresoPlin').textContent = formatearMonto(0);

                    if (data.status && data.ingresos && data.ingresos.length > 0) {
                        data.ingresos.forEach(ingreso => {
                            const tipoPago = ingreso.tipopago ? ingreso.tipopago.toUpperCase() : '';
                            const total = parseFloat(ingreso.total || 0);

                            console.log(`Ingreso encontrado: ${tipoPago} - ${total}`);

                            switch (tipoPago) {
                                case 'EFECTIVO':
                                    ingresos.efectivo = total;
                                    document.getElementById('ingresoEfectivo').textContent = formatearMonto(total);
                                    break;
                                case 'TRANSFERENCIA':
                                case 'TARJETA':
                                    ingresos.transferencia = total;
                                    document.getElementById('ingresoTransferencia').textContent = formatearMonto(total);
                                    break;
                                case 'YAPE':
                                    ingresos.yape = total;
                                    document.getElementById('ingresoYape').textContent = formatearMonto(total);
                                    break;
                                case 'PLIN':
                                    ingresos.plin = total;
                                    document.getElementById('ingresoPlin').textContent = formatearMonto(total);
                                    break;
                            }
                        });
                    }

                    console.log("Ingresos actualizados:", ingresos);
                    actualizarResumen();
                })
                .catch(error => {
                    console.error('Error al cargar ingresos:', error);
                    document.getElementById('ingresoEfectivo').textContent = formatearMonto(0);
                    document.getElementById('ingresoTransferencia').textContent = formatearMonto(0);
                    document.getElementById('ingresoYape').textContent = formatearMonto(0);
                    document.getElementById('ingresoPlin').textContent = formatearMonto(0);
                    actualizarResumen();
                });
        }

        function cargarEgresos(horaFiltro = null) {
            console.log("Cargando egresos...");
            const fechaActual = document.getElementById('fecha').value;
            const horaParaFiltrar = horaFiltro || horaUltimoCierre;
            console.log("Hora para filtrar egresos:", horaParaFiltrar);

            let url = `../../controllers/arqueocaja.controller.php?op=listar_egresos_devoluciones&fecha=${fechaActual}`;

            if (idArqueoCajaActual) {
                url += `&idarqueo=${idArqueoCajaActual}`;
            }

            if (horaParaFiltrar) {
                url += `&hora_inicio=${horaParaFiltrar}`;
                console.log("Filtrando egresos desde la hora:", horaParaFiltrar);
            }

            console.log("URL para cargar egresos:", url);

            fetch(url)
                .then(response => {
                    console.log("Respuesta de egresos recibida");
                    return response.json();
                })
                .then(data => {
                    console.log("Datos de egresos:", data);
                    egresos = {
                        transferencia: 0,
                        plin: 0,
                        yape: 0,
                        efectivo: 0
                    };

                    document.getElementById('egresoEfectivo').textContent = formatearMonto(0);
                    document.getElementById('egresoTransferencia').textContent = formatearMonto(0);
                    document.getElementById('egresoYape').textContent = formatearMonto(0);
                    document.getElementById('egresoPlin').textContent = formatearMonto(0);

                    if (data.status && data.egresos && data.egresos.length > 0) {
                        data.egresos.forEach(egreso => {
                            const tipoPago = egreso.tipopago ? egreso.tipopago.toUpperCase() : '';
                            const total = parseFloat(egreso.total || 0);

                            console.log(`Egreso encontrado: ${tipoPago} - ${total}`);

                            switch (tipoPago) {
                                case 'EFECTIVO':
                                    egresos.efectivo = total;
                                    document.getElementById('egresoEfectivo').textContent = formatearMonto(total);
                                    break;
                                case 'TRANSFERENCIA':
                                case 'TARJETA':
                                    egresos.transferencia = total;
                                    document.getElementById('egresoTransferencia').textContent = formatearMonto(total);
                                    break;
                                case 'YAPE':
                                    egresos.yape = total;
                                    document.getElementById('egresoYape').textContent = formatearMonto(total);
                                    break;
                                case 'PLIN':
                                    egresos.plin = total;
                                    document.getElementById('egresoPlin').textContent = formatearMonto(total);
                                    break;
                            }
                        });
                    }

                    console.log("Egresos actualizados:", egresos);
                    actualizarResumen();
                })
                .catch(error => {
                    console.error('Error al cargar egresos:', error);
                    document.getElementById('egresoEfectivo').textContent = formatearMonto(0);
                    document.getElementById('egresoTransferencia').textContent = formatearMonto(0);
                    document.getElementById('egresoYape').textContent = formatearMonto(0);
                    document.getElementById('egresoPlin').textContent = formatearMonto(0);
                    actualizarResumen();
                });
        }

        function actualizarResumen() {
            console.log("Actualizando resumen con los siguientes datos:");
            console.log("Saldo inicial (dejado anteriormente):", saldoInicial);
            console.log("Ingresos:", ingresos);
            console.log("Egresos:", egresos);

            const totalIngresos = ingresos.efectivo + ingresos.transferencia + ingresos.yape + ingresos.plin;
            const totalEgresos = egresos.efectivo + egresos.transferencia + egresos.yape + egresos.plin;
            saldoMovimientos = totalIngresos - totalEgresos;

            const saldoDejadoAnterior = parseFloat(document.getElementById('saldoInicial').value || 0);
            saldoTotal = saldoDejadoAnterior + saldoMovimientos;

            document.getElementById('saldoMovimientosValor').value = saldoMovimientos.toFixed(2);
            document.getElementById('saldoMovimientosDisplay').textContent = formatearMonto(saldoMovimientos);
            document.getElementById('saldoTotal').value = saldoTotal.toFixed(2);
            document.getElementById('saldoTotalDisplay').textContent = formatearMonto(saldoTotal);

            console.log("Total ingresos:", totalIngresos);
            console.log("Total egresos:", totalEgresos);
            console.log("Saldo movimientos:", saldoMovimientos);
            console.log("Saldo total:", saldoTotal);

            document.getElementById('saldoAnterior').textContent = formatearMonto(saldoDejadoAnterior);
            document.getElementById('ingresoDiario').textContent = formatearMonto(ingresos.efectivo);
            document.getElementById('egresoDiario').textContent = formatearMonto(egresos.efectivo);
            document.getElementById('saldoMovimientos').textContent = formatearMonto(saldoMovimientos);
            document.getElementById('totalEfectivoCaja').textContent = formatearMonto(saldoTotal);

            const otrosAportes = ingresos.transferencia + ingresos.yape + ingresos.plin;
            if (otrosAportes > 0) {
                document.getElementById('otrosAportes').textContent = `Transferencia, Plin, Yape (S/ ${otrosAportes.toFixed(2)})`;
            } else {
                document.getElementById('otrosAportes').textContent = 'Transferencia, Plin, Yape';
            }

            document.getElementById('resumenEfectivo').textContent = formatearMonto(ingresos.efectivo);
            document.getElementById('resumenTransferencia').textContent = formatearMonto(ingresos.transferencia);
            document.getElementById('resumenPlin').textContent = formatearMonto(ingresos.plin);
            document.getElementById('resumenYape').textContent = formatearMonto(ingresos.yape);
            document.getElementById('resumenTotalIngresos').textContent = formatearMonto(totalIngresos);

            document.getElementById('resumenEgresoEfectivo').textContent = formatearMonto(egresos.efectivo);
            document.getElementById('resumenEgresoTransferencia').textContent = formatearMonto(egresos.transferencia);
            document.getElementById('resumenEgresoPlin').textContent = formatearMonto(egresos.plin);
            document.getElementById('resumenEgresoYape').textContent = formatearMonto(egresos.yape);
            document.getElementById('resumenTotalEgresos').textContent = formatearMonto(totalEgresos);
        }

        function crearArqueo() {
            console.log("Creando nuevo arqueo...");
            return new Promise((resolve, reject) => {
                const horaActual = establecerHoraActual();

                const datos = {
                    fecha_apertura: document.getElementById('fecha').value,
                    hora_apertura: horaActual,
                    observaciones: document.getElementById('observaciones').value
                };

                console.log("Datos para crear arqueo:", datos);

                fetch('../../controllers/arqueocaja.controller.php?op=abrir_caja', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(datos)
                    })
                    .then(response => {
                        console.log("Respuesta recibida del servidor:", response);
                        return response.json();
                    })
                    .then(data => {
                        console.log("Respuesta al crear arqueo:", data);
                        if (data.status) {
                            console.log("Arqueo creado exitosamente con ID:", data.idarqueo);

                            if (data.monto_inicial !== undefined) {
                                saldoInicial = parseFloat(data.monto_inicial);
                                saldoAnterior = parseFloat(data.saldo_anterior || data.monto_inicial);

                                document.getElementById('saldoInicial').value = saldoInicial;
                                document.getElementById('saldoRestante').textContent = formatearMonto(saldoInicial);

                                console.log("Saldo inicial establecido:", saldoInicial);
                            }

                            resolve(data.idarqueo);
                        } else {
                            console.error("Error al crear arqueo:", data.mensaje);
                            mostrarAlerta(data.mensaje || 'Error al crear el arqueo', 'error');
                            resolve(null);
                        }
                    })
                    .catch(error => {
                        console.error('Error al crear arqueo:', error);
                        mostrarAlerta('Error al procesar la solicitud', 'error');
                        resolve(null);
                    });
            });
        }

        // FUNCIÓN CORREGIDA: Abrir caja con actualización automática
        function abrirCaja() {
            console.log("Abriendo caja...");

            crearArqueo().then(id => {
                if (id) {
                    console.log("Nuevo arqueo creado con ID:", id);
                    idArqueoCajaActual = id;

                    actualizarBotonSegunEstado(true);
                    desbloquearInterfaz(); // Esto incluye iniciar actualización automática

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Caja abierta exitosamente',
                            text: 'La caja ha sido abierta correctamente. Los movimientos se actualizarán automáticamente.',
                            icon: 'success',
                            confirmButtonColor: '#28a745',
                            timer: 3000
                        });
                    } else {
                        alert('Caja abierta exitosamente');
                    }

                    const horaActual = document.getElementById('horaInicio').value;
                    console.log("CORREGIDO: Nueva caja abierta - cargando movimientos desde hora actual:", horaActual);
                    
                    cargarIngresos(horaActual);
                    cargarEgresos(horaActual);
                    actualizarSaldoTotal();
                } else {
                    console.error("No se pudo crear el arqueo");
                }
            }).catch(error => {
                console.error("Error en la promesa de crearArqueo:", error);
            });
        }

        function nuevoArqueo() {
            if (idArqueoCajaActual) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: 'Ya hay un arqueo abierto. Debes cerrarlo antes de crear uno nuevo.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Cerrar y crear nuevo',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            confirmarCerrarCaja();
                        }
                    });
                } else {
                    if (confirm('Ya hay un arqueo abierto. ¿Deseas cerrarlo antes de crear uno nuevo?')) {
                        confirmarCerrarCaja();
                    }
                }
            } else {
                establecerFechaActual();
                establecerHoraActual();
                document.getElementById('observaciones').value = '';
                obtenerUltimoSaldoDisponible();
            }
        }

        function formatearMonto(monto) {
            return `S/ ${parseFloat(monto).toFixed(2)}`;
        }

        function mostrarAlerta(mensaje, tipo) {
            console.log(`Mostrando alerta: ${mensaje} (${tipo})`);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: tipo,
                    title: tipo === 'error' ? 'Error' : (tipo === 'warning' ? 'Advertencia' : 'Éxito'),
                    text: mensaje,
                    timer: 3000,
                    timerProgressBar: true
                });
            } else {
                console.error('SweetAlert2 no está disponible. Usando alert nativo.');
                const titulo = tipo === 'error' ? 'Error' : (tipo === 'warning' ? 'Advertencia' : 'Éxito');
                alert(`${titulo}: ${mensaje}`);
            }
        }
    });
</script>

<?php
require_once '../include/footer.php';
?>