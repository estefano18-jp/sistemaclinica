<!-- Modal para Pago de Cita -->
<div class="modal fade" id="modalPagoCita" tabindex="-1" aria-labelledby="modalPagoCitaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header bg-gradient-primary border-0 text-white rounded-top-3 py-3">
                <h5 class="modal-title" id="modalPagoCitaLabel">
                    <i class="fas fa-credit-card me-2"></i>Método de Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <style>
                    /* Estilos personalizados para el modal mejorado */
                    #modalPagoCita {
                        --primary-color: #0d6efd;
                        --primary-light: #e7f1ff;
                        --secondary-color: #6c757d;
                        --accent-color: #3a86ff;
                        --success-color: #198754;
                        --light-bg: #f8f9fa;
                    }

                    .bg-gradient-primary {
                        background: linear-gradient(135deg, var(--primary-color), #0a58ca);
                    }

                    #formPagoCita .form-control,
                    #formPagoCita .form-select,
                    #formPagoCita .input-group {
                        height: 42px;
                        border-radius: 8px;
                        border: 1px solid #dee2e6;
                        transition: all 0.3s ease;
                    }

                    #formPagoCita .form-control:focus,
                    #formPagoCita .form-select:focus {
                        border-color: var(--accent-color);
                        box-shadow: 0 0 0 0.25rem rgba(58, 134, 255, 0.25);
                    }

                    #formPagoCita .input-group .form-control {
                        border-top-right-radius: 0;
                        border-bottom-right-radius: 0;
                    }

                    #formPagoCita .input-group .btn {
                        border-top-left-radius: 0;
                        border-bottom-left-radius: 0;
                        border-top-right-radius: 8px;
                        border-bottom-right-radius: 8px;
                    }

                    #formPagoCita label {
                        margin-bottom: 0.4rem;
                        font-weight: 500;
                        color: #495057;
                    }

                    #formPagoCita .section-title {
                        color: var(--primary-color);
                        font-weight: 600;
                        margin-top: 1rem;
                        margin-bottom: 1rem;
                        padding-bottom: 0.5rem;
                        border-bottom: 2px solid var(--primary-light);
                    }

                    #formPagoCita .card-datos {
                        border-radius: 10px;
                        background-color: var(--light-bg);
                        border: none;
                        padding: 1.25rem;
                        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
                        margin-bottom: 1.5rem;
                    }

                    #formPagoCita .table {
                        border-radius: 8px;
                        overflow: hidden;
                        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
                    }

                    #formPagoCita .table thead {
                        background-color: var(--primary-light);
                    }

                    #formPagoCita .table-hover tbody tr:hover {
                        background-color: rgba(13, 110, 253, 0.05);
                    }

                    #formPagoCita tfoot {
                        background-color: var(--light-bg);
                        font-weight: 600;
                    }

                    #btnProcesarPago {
                        background: linear-gradient(135deg, var(--primary-color), #0a58ca);
                        border: none;
                        padding: 8px 20px;
                        border-radius: 8px;
                        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.2);
                        transition: all 0.3s ease;
                    }

                    #btnProcesarPago:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.2);
                    }

                    .badge-pill {
                        padding: 0.5em 0.75em;
                        border-radius: 50rem;
                        font-weight: 500;
                        font-size: 0.75rem;
                    }

                    .badge-primary {
                        background-color: var(--primary-light);
                        color: var(--primary-color);
                    }

                    .alert-custom-info {
                        background-color: var(--primary-light);
                        border-left: 4px solid var(--primary-color);
                        border-radius: 8px;
                        padding: 1rem;
                    }

                    .btn-outline-secondary {
                        border-radius: 8px;
                        transition: all 0.3s ease;
                    }

                    /* Animaciones */
                    @keyframes fadeIn {
                        from {
                            opacity: 0;
                            transform: translateY(10px);
                        }

                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }

                    .animate-fade-in {
                        animation: fadeIn 0.3s ease forwards;
                    }

                    /* Responsive adjustments */
                    @media (max-width: 768px) {
                        .modal-body {
                            padding: 1rem;
                        }
                    }
                </style>

                <div class="progress" style="height: 8px; margin-bottom: 20px;">
                    <div class="progress-bar bg-gradient-primary" role="progressbar" style="width: 30%;" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <form id="formPagoCita">
                    <!-- Tipo de Cliente -->
                    <div class="mb-4">
                        <label for="tipoCliente" class="form-label fw-bold">
                            <i class="fas fa-user-tag me-2 text-primary"></i>Tipo de Cliente
                        </label>
                        <select class="form-select shadow-sm" id="tipoCliente">
                            <option value="personal" selected>Pago Personal</option>
                            <option value="empresa">Pago por Empresa</option>
                            <option value="tercero">Pago por Tercero</option>
                        </select>
                    </div>

                    <!-- Datos del Paciente -->
                    <div class="card-datos animate-fade-in">
                        <h6 class="section-title">
                            <i class="fas fa-user-injured me-2"></i>Datos del Paciente
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="tipoPaciente" class="form-label">Tipo de Documento</label>
                                <select class="form-select shadow-sm" id="tipoPaciente" disabled>
                                    <option disabled>Seleccione...</option>
                                    <option value="dni" selected>DNI</option>
                                    <option value="pasaporte">Pasaporte</option>
                                    <option value="carnet">Carnet de Extranjería</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="documentoPaciente" class="form-label">Número de Documento</label>
                                <input type="text" class="form-control shadow-sm" id="documentoPaciente" placeholder="Número de documento" readonly value="12345678">
                            </div>
                            <div class="col-md-6">
                                <label for="nombrePaciente" class="form-label">Nombre</label>
                                <input type="text" class="form-control shadow-sm" id="nombrePaciente" placeholder="Nombre" readonly value="Juan">
                            </div>
                            <div class="col-md-6">
                                <label for="apellidoPaciente" class="form-label">Apellido</label>
                                <input type="text" class="form-control shadow-sm" id="apellidoPaciente" placeholder="Apellido" readonly value="Pérez">
                            </div>
                        </div>
                    </div>

                    <!-- Datos de la Empresa - Inicialmente oculto -->
                    <div class="card-datos animate-fade-in d-none" id="datosEmpresaContainer">
                        <h6 class="section-title">
                            <i class="fas fa-building me-2"></i>Datos de la Empresa
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="ruc" class="form-label">RUC</label>
                                <div class="input-group shadow-sm">
                                    <input type="text" class="form-control" id="ruc" placeholder="Ej. 20549872536">
                                    <button class="btn btn-outline-primary" type="button" id="buscarRuc">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="razonSocial" class="form-label">Razón Social</label>
                                <input type="text" class="form-control shadow-sm" id="razonSocial" placeholder="Razón Social">
                            </div>
                            <div class="col-md-6">
                                <label for="direccionEmpresa" class="form-label">Dirección</label>
                                <input type="text" class="form-control shadow-sm" id="direccionEmpresa" placeholder="Dirección">
                            </div>
                            <div class="col-md-6">
                                <label for="nombreComercial" class="form-label">Nombre Comercial</label>
                                <input type="text" class="form-control shadow-sm" id="nombreComercial" placeholder="Nombre Comercial">
                            </div>
                            <div class="col-md-6">
                                <label for="telefonoEmpresa" class="form-label">Teléfono</label>
                                <input type="text" class="form-control shadow-sm" id="telefonoEmpresa" placeholder="Teléfono">
                            </div>
                            <div class="col-md-6">
                                <label for="emailEmpresa" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control shadow-sm" id="emailEmpresa" placeholder="Correo Electrónico">
                            </div>
                        </div>
                    </div>

                    <!-- Datos del Pagador (Tercero) - Inicialmente oculto -->
                    <div class="card-datos animate-fade-in d-none" id="datosPagadorContainer">
                        <h6 class="section-title">
                            <i class="fas fa-user-tie me-2"></i>Datos del Pagador
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="tipoDocPagador" class="form-label">Tipo de Documento</label>
                                <select class="form-select shadow-sm" id="tipoDocPagador">
                                    <option disabled>Seleccione...</option>
                                    <option value="dni" selected>DNI</option>
                                    <option value="pasaporte">Pasaporte</option>
                                    <option value="carnet">Carnet de Extranjería</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="documentoPagador" class="form-label">Número de Documento</label>
                                <div class="input-group shadow-sm">
                                    <input type="text" class="form-control" id="documentoPagador" placeholder="Número de Documento">
                                    <button class="btn btn-outline-primary" type="button" id="buscarDocPagador">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="nombresPagador" class="form-label">Nombres</label>
                                <input type="text" class="form-control shadow-sm" id="nombresPagador" placeholder="Nombres">
                            </div>
                            <div class="col-md-6">
                                <label for="apellidosPagador" class="form-label">Apellidos</label>
                                <input type="text" class="form-control shadow-sm" id="apellidosPagador" placeholder="Apellidos">
                            </div>
                        </div>
                    </div>

                    <!-- Detalle de Servicios -->
                    <div class="card-datos animate-fade-in">
                        <h6 class="section-title">
                            <i class="fas fa-clipboard-list me-2"></i>Detalle de Servicios
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Descripción</th>
                                        <th class="text-end">Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div>Consulta Médica</div>
                                            <div class="badge badge-pill badge-primary mt-1" id="especialidadConsulta">Cardiología</div>
                                        </td>
                                        <td class="text-end" id="precioConsulta">S/. 120.00</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td>Total</td>
                                        <td class="text-end" id="precioTotal">S/. 120.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Método de Pago (SIMPLIFICADO) -->
                    <div class="mb-4">
                        <label for="metodoPago" class="form-label fw-bold">
                            <i class="fas fa-money-bill-wave me-2 text-primary"></i>Método de Pago
                        </label>
                        <select class="form-select shadow-sm" id="metodoPago">
                            <option value="efectivo" selected>Pago en Efectivo</option>
                            <option value="transferencia">Transferencia Bancaria</option>
                            <option value="yape">Yape</option>
                            <option value="plin">Plin</option>
                        </select>
                    </div>
                    <!-- Monto Pagado por el Cliente -->
                    <div class="mb-4" id="montoClienteContainer">
                        <label for="montoPagado" class="form-label fw-bold">
                            <i class="fas fa-money-bill-wave me-2 text-primary"></i>Monto Pagado por el Cliente
                        </label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text">S/</span>
                            <input type="number" class="form-control" id="montoPagado" placeholder="Ingrese el monto" min="0" step="1">
                        </div>
                        <div class="mt-2" id="vueltoContainer" style="display: none;">
                            <div class="alert alert-success p-2">
                                <div class="d-flex justify-content-between">
                                    <span><i class="fas fa-coins me-2"></i>Vuelto:</span>
                                    <span id="vueltoMonto" class="fw-bold">S/ 0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tipo de Comprobante -->
                    <div class="mb-4">
                        <label for="tipoComprobante" class="form-label fw-bold">
                            <i class="fas fa-file-invoice me-2 text-primary"></i>Tipo de Comprobante
                        </label>
                        <select class="form-select shadow-sm" id="tipoComprobante">
                            <option value="boleta" selected>Boleta</option>
                            <option value="factura">Factura</option>
                        </select>
                    </div>
                    <!-- Datos para Factura - Inicialmente oculto -->
                    <div class="card-datos animate-fade-in d-none" id="datosFacturaContainer">
                        <h6 class="section-title">
                            <i class="fas fa-building me-2"></i>Datos de la Empresa para Factura
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="rucFactura" class="form-label">RUC</label>
                                <div class="input-group shadow-sm">
                                    <input type="text" class="form-control" id="rucFactura" placeholder="Ej. 20549872536" maxlength="11">
                                    <button class="btn btn-outline-primary" type="button" id="buscarRucFactura">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="razonSocialFactura" class="form-label">Razón Social</label>
                                <input type="text" class="form-control shadow-sm" id="razonSocialFactura" placeholder="Razón Social" readonly>
                            </div>
                            <div class="col-md-12">
                                <label for="direccionFactura" class="form-label">Dirección</label>
                                <input type="text" class="form-control shadow-sm" id="direccionFactura" placeholder="Dirección" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Campo oculto para almacenar el ID de empresa para factura -->
                    <input type="hidden" id="idempresaFactura" value="">
                </form>
            </div>
            <div class="modal-footer border-0 py-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btnProcesarPago">
                    <i class="fas fa-check-circle me-2"></i>Procesar Pago
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Script para controlar la visualización de secciones según selección -->
<script>
    /**
     * Configura los eventos del modal de pago
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Configurar tipo de cliente para mostrar/ocultar secciones
        const tipoCliente = document.getElementById('tipoCliente');
        const datosEmpresaContainer = document.getElementById('datosEmpresaContainer');
        const datosPagadorContainer = document.getElementById('datosPagadorContainer');

        if (tipoCliente && datosEmpresaContainer && datosPagadorContainer) {
            console.log('Configurando evento de cambio para tipo de cliente');

            tipoCliente.addEventListener('change', function() {
                // Ocultar todos los contenedores primero
                datosEmpresaContainer.classList.add('d-none');
                datosPagadorContainer.classList.add('d-none');

                // Mostrar contenedor según la selección
                const seleccion = this.value;
                console.log('Cambio de tipo de cliente:', seleccion);

                if (seleccion === 'empresa') {
                    datosEmpresaContainer.classList.remove('d-none');
                } else if (seleccion === 'tercero') {
                    datosPagadorContainer.classList.remove('d-none');
                }
            });
        } else {
            console.error('No se encontraron los elementos necesarios para configurar tipo de cliente');
        }

        // Configurar botón de búsqueda de RUC
        const btnBuscarRuc = document.getElementById('buscarRuc');
        if (btnBuscarRuc) {
            console.log('Configurando evento para búsqueda de RUC');

            btnBuscarRuc.addEventListener('click', async function() {
                const ruc = document.getElementById('ruc').value;
                if (!ruc || !validarRUC(ruc)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'RUC inválido',
                        text: 'Por favor, ingrese un RUC válido'
                    });
                    return;
                }

                mostrarCargando();
                try {
                    // Buscar empresa por RUC
                    const empresa = await buscarEmpresaPorRuc(ruc);
                    if (empresa) {
                        // Llenar formulario con datos de la empresa
                        document.getElementById('razonSocial').value = empresa.razonsocial || '';
                        document.getElementById('direccionEmpresa').value = empresa.direccion || '';
                        document.getElementById('nombreComercial').value = empresa.nombrecomercial || '';
                        document.getElementById('telefonoEmpresa').value = empresa.telefono || '';
                        document.getElementById('emailEmpresa').value = empresa.email || '';
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'Empresa no encontrada',
                            text: 'La empresa no está registrada. Por favor, complete los datos.'
                        });
                    }
                } catch (error) {
                    console.error('Error al buscar empresa:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al buscar empresa'
                    });
                } finally {
                    ocultarCargando();
                }
            });
        }

        // Configurar botón de búsqueda de documento para pagador tercero
        const btnBuscarDocPagador = document.getElementById('buscarDocPagador');
        if (btnBuscarDocPagador) {
            console.log('Configurando evento para búsqueda de pagador');

            btnBuscarDocPagador.addEventListener('click', async function() {
                const tipoDoc = document.getElementById('tipoDocPagador').value;
                const nroDoc = document.getElementById('documentoPagador').value;

                if (!nroDoc) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Documento inválido',
                        text: 'Por favor, ingrese un número de documento'
                    });
                    return;
                }

                mostrarCargando();
                try {
                    // Convertir tipo de documento al formato de la BD
                    const tipoDB = obtenerTipoDocumentoDB(tipoDoc);

                    // Buscar cliente por documento
                    const cliente = await buscarClientePorDocumento(tipoDB, nroDoc);
                    if (cliente) {
                        // Llenar formulario con datos del cliente
                        document.getElementById('nombresPagador').value = cliente.nombres || '';
                        document.getElementById('apellidosPagador').value = cliente.apellidos || '';
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'Cliente no encontrado',
                            text: 'El cliente no está registrado. Por favor, complete los datos.'
                        });
                    }
                } catch (error) {
                    console.error('Error al buscar cliente:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al buscar cliente'
                    });
                } finally {
                    ocultarCargando();
                }
            });
        }

        // CORRECCIÓN: Configurar botón de procesar pago (con protección para evitar duplicados)
        
        if (btnProcesarPago) {
            console.log('Configurando evento para procesar pago');

            // Eliminar cualquier event listener previo para evitar registro múltiple
            if (btnProcesarPago.onclickListenerSet) {
                btnProcesarPago.removeEventListener("click", btnProcesarPago.onclickHandler);
            }

            // Definir el handler una sola vez
            btnProcesarPago.onclickHandler = function() {
                console.log('Ejecutando procesamiento de pago...');

                // CORRECCIÓN: Deshabilitar el botón para evitar múltiples clics
                btnProcesarPago.disabled = true;

                // La función procesarPago se define en el archivo procesarPago.js
                if (typeof procesarPago === 'function') {
                    procesarPago();

                    // Habilitar el botón después de un tiempo
                    setTimeout(() => {
                        btnProcesarPago.disabled = false;
                    }, 2000);
                } else {
                    console.error('La función procesarPago no está definida');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo procesar el pago. Error interno.'
                    });
                    btnProcesarPago.disabled = false;
                }
            };

            // Agregar el evento una sola vez
            btnProcesarPago.addEventListener("click", btnProcesarPago.onclickHandler);
            btnProcesarPago.onclickListenerSet = true;
        }
    });

    /**
     * Convierte el tipo de documento del UI al formato de la base de datos
     */
    function obtenerTipoDocumentoDB(tipoUI) {
        const mapeo = {
            'dni': 'DNI',
            'pasaporte': 'PASAPORTE',
            'carnet': 'CARNET DE EXTRANJERIA',
            'otro': 'OTRO'
        };

        return mapeo[tipoUI] || 'DNI';
    }

    // Calcular vuelto al cambiar el monto pagado
    const montoPagadoInput = document.getElementById('montoPagado');
    const vueltoContainer = document.getElementById('vueltoContainer');
    const vueltoMonto = document.getElementById('vueltoMonto');
    const metodoPago = document.getElementById('metodoPago');
    const btnProcesarPago = document.getElementById('btnProcesarPago');

    if (montoPagadoInput && vueltoContainer && vueltoMonto && metodoPago) {
        // Mostrar/ocultar campo de monto pagado según método de pago
        metodoPago.addEventListener('change', function() {
            const metodo = this.value;
            const montoClienteContainer = document.getElementById('montoClienteContainer');

            if (metodo === 'efectivo') {
                montoClienteContainer.style.display = 'block';
                // Validar monto al cambiar el método de pago a efectivo
                const montoPagado = parseFloat(montoPagadoInput.value) || 0;
                const precioTotal = obtenerPrecioTotalComprobante();
                btnProcesarPago.disabled = montoPagado < precioTotal;
            } else {
                montoClienteContainer.style.display = 'none';
                vueltoContainer.style.display = 'none';
                btnProcesarPago.disabled = false; // Habilitar botón para otros métodos de pago
            }
        });

        // Calcular vuelto y validar monto cuando cambia el monto pagado
        montoPagadoInput.addEventListener('input', function() {
            const montoPagado = parseFloat(this.value) || 0;
            const precioTotal = obtenerPrecioTotalComprobante();

            if (montoPagado >= precioTotal) {
                const vuelto = montoPagado - precioTotal;
                vueltoMonto.textContent = `S/ ${vuelto.toFixed(2)}`;
                vueltoContainer.style.display = 'block';
                btnProcesarPago.disabled = false; // Habilitar botón
            } else {
                vueltoContainer.style.display = 'none';
                btnProcesarPago.disabled = true; // Deshabilitar botón
            }
        });

        // Inicializar estado del botón al cargar la página
        if (metodoPago.value === 'efectivo') {
            const montoPagado = parseFloat(montoPagadoInput.value) || 0;
            const precioTotal = obtenerPrecioTotalComprobante();
            btnProcesarPago.disabled = montoPagado < precioTotal;
        }
    }

</script>