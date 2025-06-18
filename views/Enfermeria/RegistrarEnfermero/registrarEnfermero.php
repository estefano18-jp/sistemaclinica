<?php /*RUTA: sistemasclinica/views/Enfermero/RegistrarEnfermero/registrarEnfermero.php*/ ?>
<?php
require_once '../../include/header.administrador.php'; ?>

<head>
    <!-- Estilos CSS idénticos a los de registrarPaciente.php pero con mejoras -->
    <style>
        /* Mantenemos todas las variables y estilos originales */
        :root {
            --primary-color: #3498db;
            --secondary-color: #1dcc1d8f;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
        }

        /* Estilos generales para el body */
        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Modificamos el contenedor principal para que se expanda */
        .container {
            width: 95% !important;
            max-width: 95% !important;
            padding-left: 12px !important;
            padding-right: 12px !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }

        /* El resto del CSS permanece exactamente igual */
        .main-content {
            width: 100%;
            padding: 0;
            margin: 0;
        }

        /* Ajuste del título central */
        .form-header {
            text-align: center;
            border-bottom: 2px solid #3498db;
            padding: 10px 0;
            margin: 0;
        }

        .form-header h2 {
            font-size: 1.4rem;
            margin: 0 0 15px 0;
            font-weight: 500;
        }

        /* Barra de progreso */
        .progress {
            height: 5px;
            margin: 0 auto;
            width: 50%;
            border-radius: 0;
            background-color: #f0f0f0;
        }

        .progress-bar {
            background-color: #3498db;
        }

        /* Estilos para las tarjetas */
        .card {
            border: none;
            box-shadow: none;
            margin-bottom: 0;
            border-radius: 0;
        }

        .card-header {
            background-color: #3498db;
            color: white;
            border-radius: 0;
            padding: 0.7rem 1rem;
            display: flex;
            align-items: center;
        }

        .card-body {
            padding: 1.5rem;
            background-color: #f8f9fa;
        }

        /* Estilos para formularios - AJUSTADOS para campos más grandes */
        .form-control,
        .form-select {
            border-radius: 4px;
            padding: 0.65rem 0.75rem;
            /* Aumentado el padding vertical */
            border: 1px solid #ced4da;
            min-height: 42px;
            /* Aumentado la altura mínima */
        }

        .row.mb-3 {
            margin-bottom: 0.8rem !important;
            /* Reducido el margen entre filas */
        }

        .btn {
            border-radius: 4px;
            padding: 0.5rem 1rem;
        }

        .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
            padding: 0.75rem;
            margin-bottom: 0;
            border-radius: 4px;
        }

        /* Campos requeridos */
        .required-field::after {
            content: " *";
            color: red;
        }

        /* Campos deshabilitados */
        .disabled {
            opacity: 0.65;
            pointer-events: none;
        }

        input:disabled,
        select:disabled {
            background-color: #e9ecef !important;
            cursor: not-allowed;
        }

        /* Validación de formularios */
        .is-valid {
            border-color: #198754 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .is-invalid {
            border-color: #dc3545 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }

        /* Estilos para el modal de credenciales */
        .modal-credenciales .modal-header {
            border-bottom: none;
            padding: 1.5rem 1.5rem 0.5rem;
        }

        .modal-credenciales .modal-title {
            font-size: 1.5rem;
            font-weight: 500;
            color: #333;
            text-align: center;
            width: 100%;
        }

        .modal-credenciales .modal-body {
            padding: 1rem 1.5rem;
        }

        .modal-credenciales .info-box {
            background-color: #d1ecf1;
            border-radius: 4px;
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            color: #0c5460;
        }

        .modal-credenciales .modal-footer {
            border-top: none;
            padding: 0.75rem 1.5rem 1.5rem;
            justify-content: center;
        }

        .modal-credenciales .btn-primary {
            background-color: #6f42c1;
            border-color: #6f42c1;
            padding: 0.5rem 1.5rem;
        }

        .modal-credenciales .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            padding: 0.5rem 1.5rem;
        }

        .modal-credenciales .field-icon {
            width: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-right: none;
            border-radius: 4px 0 0 4px;
        }

        .modal-credenciales .field-input {
            border-left: none;
            border-radius: 0 4px 4px 0;
        }

        /* Estilos adicionales para el modal de credenciales */
        .field-action {
            width: auto !important;
            padding: 0 15px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
            background-color: #f8f9fa !important;
            border: 1px solid #ced4da !important;
            border-left: none !important;
            border-radius: 0 4px 4px 0 !important;
            font-weight: 400 !important;
            font-size: 14px !important;
        }

        .field-action i {
            margin-right: 5px;
        }

        /* Estilos para el estado normal */
        .email-normal .field-input {
            border-color: #ced4da !important;
        }

        .email-normal .field-action {
            color: #6c757d !important;
        }

        /* Estilos para el estado de error */
        .email-error .field-input {
            border-color: #dc3545 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            padding-right: calc(1.5em + 0.75rem) !important;
        }

        .email-error .field-action {
            background-color: #f8f9fa !important;
            border-color: #dc3545 !important;
            color: #6c757d !important;
        }

        /* Estilos para el estado de edición */
        .email-edit .field-input {
            border-color: #0d6efd !important;
            background-image: none !important;
        }

        .email-edit .field-action {
            background-color: #f8f9fa !important;
            border-color: #0d6efd !important;
            color: #6c757d !important;
        }

        /* Adaptación específica para alinear con las líneas rojas */
        @media (min-width: 768px) {

            /* Asegurarse que el contenedor ocupe todo el ancho disponible en pantallas medianas y grandes */
            .container,
            .container-md,
            .container-lg,
            .container-xl,
            .container-xxl {
                max-width: 100% !important;
            }
        }

        /* Estilos mejorados para el botón de toggle-password */
        .toggle-password {
            cursor: pointer !important;
            z-index: 10 !important;
            pointer-events: auto !important;
            user-select: none !important;
            background-color: #f8f9fa !important;
            border: 1px solid #ced4da !important;
            border-left: none !important;
            border-radius: 0 4px 4px 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 15px !important;
            transition: background-color 0.15s ease-in-out !important;
        }

        .toggle-password:hover {
            background-color: #e9ecef !important;
        }

        .toggle-password:active {
            background-color: #dde0e3 !important;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div class="container py-4">
        <div class="row mb-3">
            <div class="col-md-12">
                <h2 class="text-center mb-2">Registro de Enfermero</h2>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>

        <form id="nurseRegistrationForm" method="POST">
            <!-- Información Personal -->
            <div class="card" id="personalInfoCard">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-user-nurse me-2"></i> Información Personal
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tipo y Número de Documento -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="tipodoc" class="form-label required-field">Tipo de Documento</label>
                            <select class="form-select" id="tipodoc" name="tipodoc" required>
                                <option value="">Seleccione...</option>
                                <option value="DNI" selected>DNI</option>
                                <option value="PASAPORTE">Pasaporte</option>
                                <option value="CARNET DE EXTRANJERIA">Carnet de Extranjería</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="nrodoc" class="form-label required-field">Número de Documento</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="nrodoc" name="nrodoc" required autofocus>
                                <button type="button" class="btn btn-primary" id="btnBuscarPersona">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="fechanacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="fechanacimiento" name="fechanacimiento">
                        </div>
                    </div>

                    <!-- Nombres y Apellidos -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="apellidos" class="form-label required-field">Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nombres" class="form-label required-field">Nombres</label>
                            <input type="text" class="form-control" id="nombres" name="nombres" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="genero" class="form-label">Género</label>
                            <select class="form-select" id="genero" name="genero">
                                <option value="">Seleccione...</option>
                                <option value="M" selected>Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="telefono" class="form-label required-field">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" required>
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label required-field">Email (será el usuario)</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="action-buttons text-end">
                                <button type="button" id="btnSiguiente" class="btn btn-primary">Siguiente <i class="fas fa-arrow-right ms-1"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmación -->
            <div class="card d-none" id="confirmacionCard">
                <div class="card-header">
                    <i class="fas fa-check-circle me-2"></i> Confirmación
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-success">
                                <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i> Revisar Información</h5>
                                <p>Por favor, revise que todos los datos ingresados sean correctos antes de guardar.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contenido de confirmación -->
                    <div id="confirmationContent">
                        <!-- Contenido dinámico -->
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="action-buttons text-end">
                                <button type="button" id="btnAnterior" class="btn btn-secondary me-2"><i class="fas fa-arrow-left me-1"></i> Anterior</button>
                                <button type="button" id="btnCancelar" class="btn btn-danger me-2"><i class="fas fa-times me-1"></i> Cancelar</button>
                                <button type="button" id="btnGuardar" class="btn btn-success"><i class="fas fa-save me-1"></i> Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal de Credenciales de Acceso -->
    <div class="modal fade modal-credenciales" id="credencialesModal" tabindex="-1" aria-labelledby="credencialesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="credencialesModalLabel">Credenciales de Acceso</h5>
                </div>
                <div class="modal-body">
                    <div class="info-box">
                        <i class="fas fa-info-circle me-2"></i> Complete las credenciales del enfermero.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico</label>
                        <div id="email-container">
                            <div class="input-group">
                                <div class="field-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <input type="text" class="form-control field-input" id="emailConfirmacion" readonly>
                                <div class="field-action" id="btnEditarEmail">
                                    <i class="fas fa-edit"></i> Editar
                                </div>
                            </div>
                            <div id="email-error-message" class="text-danger mt-1" style="display: none;">
                                Este correo electrónico ya está en uso como usuario
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group">
                            <div class="field-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" class="form-control field-input" id="passuser" placeholder="Ingrese contraseña">
                            <div class="field-action toggle-password" data-target="passuser">
                                <i class="fas fa-eye"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirmar Contraseña</label>
                        <div class="input-group">
                            <div class="field-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" class="form-control field-input" id="confirmarpassuser" placeholder="Confirme contraseña">
                            <div class="field-action toggle-password" data-target="confirmarpassuser">
                                <i class="fas fa-eye"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="btnGuardarCredenciales">Guardar y Continuar</button>
                    <button type="button" class="btn btn-secondary" id="btnCancelarModal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Variables para el estado actual
            let credencialesCompletadas = false;
            // Variables de estado para el modal de credenciales
            let emailEditMode = false;
            let emailOriginal = '';
            // Variable para el modal de credenciales
            let credencialesModal = null;

            // Agregar estilos CSS para los campos válidos e inválidos y para la animación del ícono
            const style = document.createElement('style');
            style.textContent = `
        .form-select.is-valid,
        .form-control.is-valid {
            border-color: #198754 !important;
            background-image: none !important;
            padding-right: 0.75rem !important;
        }
        .form-control.is-invalid {
            border-color: #dc3545 !important;
            background-image: none !important;
            padding-right: 0.75rem !important;
        }
        
        /* Animaciones para el ícono de éxito */
        @keyframes bounceIn {
            from, 20%, 40%, 60%, 80%, to {
                animation-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000);
            }
            0% {
                opacity: 0;
                transform: scale3d(0.3, 0.3, 0.3);
            }
            20% {
                transform: scale3d(1.1, 1.1, 1.1);
            }
            40% {
                transform: scale3d(0.9, 0.9, 0.9);
            }
            60% {
                opacity: 1;
                transform: scale3d(1.03, 1.03, 1.03);
            }
            80% {
                transform: scale3d(0.97, 0.97, 0.97);
            }
            to {
                opacity: 1;
                transform: scale3d(1, 1, 1);
            }
        }
        
        .animate__bounceIn {
            animation-duration: 0.75s;
            animation-name: bounceIn;
        }
        
        .animate__animated {
            animation-duration: 1s;
            animation-fill-mode: both;
        }
    `;
            document.head.appendChild(style);

            // Función para validar y marcar todos los campos del formulario
            function validarCamposFormulario() {
                // NO validar campos automáticamente al cargar la página
                // Esta función solo se usará después de la búsqueda por documento
            }

            // Agregar validación para el campo password y confirmar password
            const passuserElement = document.getElementById('passuser');
            const confirmarpassuserElement = document.getElementById('confirmarpassuser');

            if (passuserElement) {
                passuserElement.addEventListener('input', function() {
                    if (this.value.trim()) {
                        if (this.value.length >= 6) {
                            markFieldAsValid(this);
                            removeFieldHelpMessage(this);
                        } else {
                            markFieldAsInvalid(this);
                            addFieldHelpMessage(this, 'La contraseña debe tener al menos 6 caracteres');
                        }
                    } else {
                        this.classList.remove('is-valid', 'is-invalid');
                        removeFieldHelpMessage(this);
                    }

                    // Validar confirmación si ya tiene valor
                    if (confirmarpassuserElement && confirmarpassuserElement.value.trim()) {
                        if (this.value === confirmarpassuserElement.value) {
                            markFieldAsValid(confirmarpassuserElement);
                            removeFieldHelpMessage(confirmarpassuserElement);
                        } else {
                            markFieldAsInvalid(confirmarpassuserElement);
                            addFieldHelpMessage(confirmarpassuserElement, 'Las contraseñas no coinciden');
                        }
                    }
                });
            }

            if (confirmarpassuserElement) {
                confirmarpassuserElement.addEventListener('input', function() {
                    if (this.value.trim() && passuserElement) {
                        if (this.value === passuserElement.value) {
                            markFieldAsValid(this);
                            removeFieldHelpMessage(this);
                        } else {
                            markFieldAsInvalid(this);
                            addFieldHelpMessage(this, 'Las contraseñas no coinciden');
                        }
                    } else {
                        this.classList.remove('is-valid', 'is-invalid');
                        removeFieldHelpMessage(this);
                    }
                });
            }

            // Inicializar el modal de Bootstrap
            const credencialesModalElement = document.getElementById('credencialesModal');
            if (credencialesModalElement) {
                credencialesModal = new bootstrap.Modal(credencialesModalElement);
            } else {
                console.error("Elemento del modal de credenciales no encontrado");
            }

            // Configurar el evento para los botones de toggle-password
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);

                    if (input && input.type === 'password') {
                        input.type = 'text';
                        this.querySelector('i').classList.remove('fa-eye');
                        this.querySelector('i').classList.add('fa-eye-slash');
                    } else if (input) {
                        input.type = 'password';
                        this.querySelector('i').classList.remove('fa-eye-slash');
                        this.querySelector('i').classList.add('fa-eye');
                    }
                });
            });

            // Asegurarse que los eventos de toggle se configuren cuando se muestra el modal
            if (credencialesModalElement) {
                credencialesModalElement.addEventListener('shown.bs.modal', function() {
                    document.querySelectorAll('.toggle-password').forEach(button => {
                        // Eliminar eventos previos para evitar duplicados
                        const newButton = button.cloneNode(true);
                        button.parentNode.replaceChild(newButton, button);

                        newButton.addEventListener('click', function() {
                            const targetId = this.getAttribute('data-target');
                            const input = document.getElementById(targetId);

                            if (input && input.type === 'password') {
                                input.type = 'text';
                                this.querySelector('i').classList.remove('fa-eye');
                                this.querySelector('i').classList.add('fa-eye-slash');
                            } else if (input) {
                                input.type = 'password';
                                this.querySelector('i').classList.remove('fa-eye-slash');
                                this.querySelector('i').classList.add('fa-eye');
                            }
                        });
                    });
                });
            }

            // Verificar que los elementos existan antes de usarlos
            const tipodocElement = document.getElementById("tipodoc");
            if (tipodocElement) {
                // Establecer DNI como valor por defecto para tipo de documento
                tipodocElement.value = "DNI";
                // NO marcar como válido hasta después de la búsqueda
            } else {
                console.error("Elemento tipodoc no encontrado");
            }

            // IMPORTANTE: Configurar maxlength para documento según el tipo por defecto (DNI = 8)
            const nrodocInput = document.getElementById("nrodoc");
            if (nrodocInput) {
                nrodocInput.setAttribute("maxlength", "8"); // Aseguramos que sea 8 para DNI (valor inicial)

                // Validar el documento cuando cambie
                nrodocInput.addEventListener('input', function() {
                    if (this.value.trim() && tipodocElement) {
                        const tipodoc = tipodocElement.value;
                        if (documentoConfig[tipodoc] && documentoConfig[tipodoc].pattern.test(this.value)) {
                            markFieldAsValid(this);
                            removeFieldHelpMessage(this);
                        } else {
                            markFieldAsInvalid(this);
                            if (documentoConfig[tipodoc]) {
                                addFieldHelpMessage(this, documentoConfig[tipodoc].message);
                            }
                        }
                    } else {
                        this.classList.remove('is-valid', 'is-invalid');
                        removeFieldHelpMessage(this);
                    }
                });
            } else {
                console.error("Elemento nrodoc no encontrado");
            }

            // Establecer Masculino como valor por defecto para género
            const generoElement = document.getElementById("genero");
            if (generoElement) {
                generoElement.value = "M";
                // NO marcar como válido hasta después de la búsqueda
            } else {
                console.error("Elemento genero no encontrado");
            }

            // Establecer fecha máxima para fecha de nacimiento (hoy)
            const fechaNacimientoElement = document.getElementById("fechanacimiento");
            if (fechaNacimientoElement) {
                const today = new Date();
                const formattedDate = today.toISOString().split('T')[0];
                fechaNacimientoElement.setAttribute("max", formattedDate);

                // Agregar atributo required para marcar como obligatorio
                fechaNacimientoElement.setAttribute("required", "required");

                // Agregar placeholder más descriptivo
                fechaNacimientoElement.setAttribute("placeholder", "DD/MM/AAAA (Obligatorio)");

                // Agregar evento para validación en tiempo real
                fechaNacimientoElement.addEventListener('change', function() {
                    // Validar la fecha ingresada
                    const resultado = validarFechaNacimiento(this, true);

                    // Si está vacío, marcar como inválido
                    if (!this.value.trim()) {
                        markFieldAsInvalid(this);
                        addFieldHelpMessage(this, 'Este campo es obligatorio');
                        return;
                    }

                    // Si es válida, verificar la edad
                    if (resultado.esValida) {
                        const edad = resultado.edad;
                        // Marcar como válido
                        markFieldAsValid(this);

                        // Si tiene entre 18 y 21 años, mostrar alerta de confirmación
                        if (edad >= 18 && edad < 22) {
                            Swal.fire({
                                title: 'Confirmar registro',
                                html: `El enfermero tiene ${edad} años. Se recomienda que los enfermeros tengan al menos 22 años. ¿Desea continuar con el registro?`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Sí, continuar',
                                cancelButtonText: 'No, usar otra fecha'
                            }).then((result) => {
                                if (!result.isConfirmed) {
                                    // Si elige "No, usar otra fecha", enfocar el campo para cambiar la fecha
                                    this.value = '';
                                    this.focus();
                                    removeFieldHelpMessage(this);
                                    this.classList.remove('is-valid', 'is-invalid');
                                }
                            });
                        }
                    }
                });

                // También validar al escribir (para cuando el usuario use el teclado)
                fechaNacimientoElement.addEventListener('input', function() {
                    if (this.value) {
                        const resultado = validarFechaNacimiento(this, false);
                        if (resultado.esValida) {
                            markFieldAsValid(this);
                        } else {
                            markFieldAsInvalid(this);
                        }
                    } else {
                        markFieldAsInvalid(this);
                        addFieldHelpMessage(this, 'Este campo es obligatorio');
                    }
                });

                // Validar inicialmente si tiene valor
                if (fechaNacimientoElement.value) {
                    const resultado = validarFechaNacimiento(fechaNacimientoElement, false);
                    if (resultado.esValida) {
                        markFieldAsValid(fechaNacimientoElement);
                    }
                }
            } else {
                console.error("Elemento fechanacimiento no encontrado");
            }

            // Bloquear todos los campos excepto tipo y número de documento
            bloquearCampos();

            // Bloquear botón Siguiente inicialmente
            const btnSiguienteElement = document.getElementById("btnSiguiente");
            if (btnSiguienteElement) {
                btnSiguienteElement.disabled = true;
                btnSiguienteElement.classList.add("disabled");
            } else {
                console.error("Elemento btnSiguiente no encontrado");
            }

            // Función para establecer el estado del email en el modal
            function setEmailState(state) {
                const emailContainer = document.getElementById('email-container');
                const emailInput = document.getElementById('emailConfirmacion');
                const emailError = document.getElementById('email-error-message');
                const btnEditarEmail = document.getElementById('btnEditarEmail');

                if (!emailContainer || !emailInput || !emailError || !btnEditarEmail) {
                    console.error("Elementos del email no encontrados");
                    return;
                }

                // Eliminar todas las clases de estado
                emailContainer.querySelector('.input-group').classList.remove('email-normal', 'email-error', 'email-edit');

                switch (state) {
                    case 'normal':
                        // Estado normal
                        emailContainer.querySelector('.input-group').classList.add('email-normal');
                        emailInput.setAttribute('readonly', 'readonly');
                        emailError.style.display = 'none';
                        btnEditarEmail.innerHTML = '<i class="fas fa-edit"></i> Editar';
                        emailEditMode = false;

                        // Si el email es válido, marcarlo como válido
                        const validacion = validateEmail(emailInput.value);
                        if (validacion.isValid) {
                            markFieldAsValid(emailInput);
                        }
                        break;

                    case 'error':
                        // Estado de error (email ya en uso)
                        emailContainer.querySelector('.input-group').classList.add('email-error');
                        emailInput.removeAttribute('readonly');
                        emailError.style.display = 'block';
                        btnEditarEmail.innerHTML = '<i class="fas fa-lock"></i> Bloquear';
                        emailEditMode = true;
                        markFieldAsInvalid(emailInput);
                        break;

                    case 'edit':
                        // Estado de edición
                        emailContainer.querySelector('.input-group').classList.add('email-edit');
                        emailInput.removeAttribute('readonly');
                        emailError.style.display = 'none';
                        btnEditarEmail.innerHTML = '<i class="fas fa-lock"></i> Bloquear';
                        emailEditMode = true;
                        emailInput.focus();

                        // Validar en tiempo real
                        const validacionEdit = validateEmail(emailInput.value);
                        if (validacionEdit.isValid) {
                            markFieldAsValid(emailInput);
                        } else {
                            markFieldAsInvalid(emailInput);
                        }
                        break;
                }
            }

            // Mostrar modal de credenciales
            function mostrarModalCredenciales() {
                // Copiar el email al campo del modal
                const emailInput = document.getElementById('email');
                const emailConfirmacion = document.getElementById('emailConfirmacion');

                if (emailInput && emailConfirmacion) {
                    emailConfirmacion.value = emailInput.value;
                    emailOriginal = emailInput.value; // Guardar el email original

                    // Validar inmediatamente el correo para mostrar si es válido o no
                    const validacion = validateEmail(emailConfirmacion.value);
                    if (validacion.isValid) {
                        markFieldAsValid(emailConfirmacion);
                        removeFieldHelpMessage(emailConfirmacion);
                    } else {
                        markFieldAsInvalid(emailConfirmacion);
                        addFieldHelpMessage(emailConfirmacion, validacion.message);
                        // No mostrar alerta, solo el mensaje junto al campo
                    }
                }

                // Establecer estado inicial normal
                setEmailState('normal');

                // Limpiar campos de contraseña
                const passuserElement = document.getElementById('passuser');
                const confirmarpassuserElement = document.getElementById('confirmarpassuser');

                if (passuserElement) passuserElement.value = '';
                if (confirmarpassuserElement) confirmarpassuserElement.value = '';

                // Asegurarse que los íconos del ojo estén en estado inicial
                const toggleIcons = document.querySelectorAll('.toggle-password i');
                toggleIcons.forEach(icon => {
                    icon.className = 'fas fa-eye';
                });

                // Mostrar modal de credenciales
                if (credencialesModal) {
                    credencialesModal.show();
                } else {
                    console.error("Modal de credenciales no inicializado");
                }
            }

            // Inicializar botones de navegación
            if (btnSiguienteElement) {
                btnSiguienteElement.addEventListener("click", function() {
                    try {
                        // Ejecutar todas las validaciones de los campos requeridos
                        const requiredFields = [
                            'tipodoc', 'nrodoc', 'apellidos', 'nombres', 'telefono', 'email', 'fechanacimiento'
                        ];

                        let isValid = true;
                        let firstInvalidField = null;

                        // Validar cada campo requerido
                        requiredFields.forEach(field => {
                            const input = document.getElementById(field);
                            if (!input) {
                                console.error(`Elemento ${field} no encontrado durante la validación`);
                                isValid = false;
                                return;
                            }

                            if (!input.value.trim()) {
                                markFieldAsInvalid(input);
                                addFieldHelpMessage(input, 'Este campo es obligatorio');
                                isValid = false;
                                if (!firstInvalidField) firstInvalidField = input;
                            } else {
                                // Si tiene contenido, marcar como válido si no hay validación específica
                                if (field !== 'email' && field !== 'telefono' && field !== 'nrodoc' && field !== 'fechanacimiento') {
                                    markFieldAsValid(input);
                                    removeFieldHelpMessage(input);
                                }
                            }
                        });

                        // Validar correo electrónico si tiene contenido
                        const emailField = document.getElementById('email');
                        if (emailField && emailField.value.trim()) {
                            const validacionEmail = validateEmail(emailField.value);
                            if (!validacionEmail.isValid) {
                                markFieldAsInvalid(emailField);
                                addFieldHelpMessage(emailField, validacionEmail.message);
                                isValid = false;
                                if (!firstInvalidField) firstInvalidField = emailField;
                            } else {
                                markFieldAsValid(emailField);
                                removeFieldHelpMessage(emailField);
                            }
                        }

                        // Validar teléfono si tiene contenido
                        const telefonoField = document.getElementById('telefono');
                        if (telefonoField && telefonoField.value.trim()) {
                            if (!/^9\d{8}$/.test(telefonoField.value)) {
                                markFieldAsInvalid(telefonoField);
                                addFieldHelpMessage(telefonoField, 'El teléfono debe tener 9 dígitos y comenzar con 9');
                                isValid = false;
                                if (!firstInvalidField) firstInvalidField = telefonoField;
                            } else {
                                markFieldAsValid(telefonoField);
                                removeFieldHelpMessage(telefonoField);
                            }
                        }

                        // Validar documento según tipo
                        const tipodoc = document.getElementById('tipodoc')?.value;
                        const nrodoc = document.getElementById('nrodoc');

                        if (tipodoc && nrodoc && nrodoc.value && documentoConfig[tipodoc]) {
                            const config = documentoConfig[tipodoc];
                            if (!config.pattern.test(nrodoc.value)) {
                                markFieldAsInvalid(nrodoc);
                                addFieldHelpMessage(nrodoc, config.message);
                                isValid = false;
                                if (!firstInvalidField) firstInvalidField = nrodoc;
                            } else {
                                markFieldAsValid(nrodoc);
                                removeFieldHelpMessage(nrodoc);
                            }
                        }

                        // Validar fecha de nacimiento
                        const fechaNacimiento = document.getElementById('fechanacimiento');
                        if (fechaNacimiento && fechaNacimiento.value) {
                            const resultado = validarFechaNacimiento(fechaNacimiento, false);
                            if (!resultado.esValida) {
                                isValid = false;
                                if (!firstInvalidField) firstInvalidField = fechaNacimiento;
                            } else {
                                markFieldAsValid(fechaNacimiento);
                                removeFieldHelpMessage(fechaNacimiento);
                            }
                        }

                        // Si hay errores, mostrar mensaje y enfocar el primer campo con error
                        if (!isValid) {
                            // Mostrar toast en lugar de alerta modal
                            showErrorToast('Por favor, complete todos los campos obligatorios correctamente');

                            // Hacer scroll al primer campo con error
                            if (firstInvalidField) {
                                firstInvalidField.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                                firstInvalidField.focus();
                            }
                            return;
                        }

                        // Si todo es válido, mostrar el modal de credenciales
                        mostrarModalCredenciales();

                    } catch (error) {
                        console.error("Error al hacer clic en el botón siguiente:", error);
                        showErrorToast('Ha ocurrido un error al procesar su solicitud. Por favor, inténtelo de nuevo.');
                    }
                });
            }

            const btnAnteriorElement = document.getElementById("btnAnterior");
            if (btnAnteriorElement) {
                btnAnteriorElement.addEventListener("click", function() {
                    const personalInfoCard = document.getElementById("personalInfoCard");
                    const confirmacionCard = document.getElementById("confirmacionCard");

                    if (personalInfoCard && confirmacionCard) {
                        personalInfoCard.classList.remove("d-none");
                        confirmacionCard.classList.add("d-none");
                    }
                });
            } else {
                console.error("Elemento btnAnterior no encontrado");
            }

            // Función para buscar persona por documento - ACTUALIZADA PARA USAR NOTIFICACIONES EN LA ESQUINA
            function buscarPersonaPorDocumento() {
                // Verificar que los elementos existan
                const tipodocElement = document.getElementById('tipodoc');
                const nrodocElement = document.getElementById('nrodoc');

                if (!tipodocElement || !nrodocElement) {
                    console.error("Elementos de documento no encontrados");
                    return;
                }

                const tipodoc = tipodocElement.value;
                const nrodoc = nrodocElement.value;

                if (!tipodoc || !nrodoc) {
                    showErrorToast('Seleccione un tipo de documento e ingrese un número de documento para buscar.');
                    return;
                }

                // Validar formato según tipo de documento
                const config = documentoConfig[tipodoc];
                if (config && !config.pattern.test(nrodoc)) {
                    markFieldAsInvalid(nrodocElement);
                    addFieldHelpMessage(nrodocElement, config.message);
                    showErrorToast(config.message);
                    return;
                }

                // Mostrar loader con efecto de búsqueda (igual que en registro de pacientes)
                Swal.fire({
                    title: 'Buscando...',
                    html: `
        <p>Verificando documento ${tipodoc}: ${nrodoc}</p>
        <div class="progress mt-3" style="height: 20px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                 role="progressbar" 
                 style="width: 100%" 
                 aria-valuenow="100" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
            </div>
        </div>
        <p class="mt-2">Por favor espere mientras se busca el enfermero.</p>
    `,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Simular tiempo de carga mínimo para mostrar la animación
                setTimeout(() => {
                    // PASO 1: Verificar si ya existe como enfermero
                    fetch(`../../../controllers/enfermeria.controller.php?op=buscar_por_documento&nrodoc=${nrodoc}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status) {
                                // Ya existe como enfermero - Mostrar notificación en la esquina en vez de alerta
                                Swal.close();

                                // Usar función personalizada para mostrar notificación similar a la imagen 2
                                showCustomErrorToast('Este documento ya está registrado como enfermero');

                                // Mantener los campos bloqueados
                                bloquearCampos();

                                const btnSiguienteElement = document.getElementById("btnSiguiente");
                                if (btnSiguienteElement) {
                                    btnSiguienteElement.disabled = true;
                                    btnSiguienteElement.classList.add("disabled");
                                }
                            } else {
                                // No existe como enfermero, buscar si existe como persona
                                fetch(`../../../controllers/persona.controller.php?op=buscar_por_documento&nrodoc=${nrodoc}`)
                                    .then(response => response.json())
                                    .then(personaData => {
                                        Swal.close();

                                        if (personaData.status && personaData.persona) {
                                            // Existe como persona pero no como enfermero
                                            Swal.fire({
                                                icon: 'info',
                                                title: 'Persona encontrada',
                                                text: 'Esta persona ya está registrada en el sistema. Se cargarán sus datos personales.',
                                                confirmButtonText: 'Continuar'
                                            });

                                            // Cargar datos de la persona
                                            cargarDatosPersona(personaData.persona, true);

                                            // Habilitar botón siguiente
                                            const btnSiguienteElement = document.getElementById("btnSiguiente");
                                            if (btnSiguienteElement) {
                                                btnSiguienteElement.disabled = false;
                                                btnSiguienteElement.classList.remove("disabled");
                                            }

                                            // También marcar como válido el campo de tipo de documento
                                            markFieldAsValid(tipodocElement);
                                            markFieldAsValid(nrodocElement);

                                            showSuccessToast('Datos personales cargados. Puede continuar con el registro.');
                                        } else {
                                            // No existe en el sistema
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Documento disponible',
                                                text: 'Este documento no está registrado en el sistema. Puede continuar con el registro completo.',
                                                confirmButtonText: 'Continuar'
                                            });

                                            // Limpiar campos
                                            limpiarCampos();

                                            // Desbloquear campos
                                            desbloquearCampos();

                                            // Marcar como válidos los campos de documento
                                            markFieldAsValid(tipodocElement);
                                            markFieldAsValid(nrodocElement);

                                            // Habilitar botón siguiente
                                            const btnSiguienteElement = document.getElementById("btnSiguiente");
                                            if (btnSiguienteElement) {
                                                btnSiguienteElement.disabled = false;
                                                btnSiguienteElement.classList.remove("disabled");
                                            }

                                            showSuccessToast('Documento disponible, puede continuar con el registro');
                                        }
                                    })
                                    .catch(error => {
                                        Swal.close();
                                        console.error('Error al buscar persona:', error);

                                        showErrorToast('No se pudo verificar si la persona existe. Puede continuar con el registro.');

                                        // Limpiar campos
                                        limpiarCampos();

                                        // Desbloquear campos
                                        desbloquearCampos();

                                        // Marcar como válidos los campos de documento
                                        markFieldAsValid(tipodocElement);
                                        markFieldAsValid(nrodocElement);

                                        // Habilitar botón siguiente
                                        const btnSiguienteElement = document.getElementById("btnSiguiente");
                                        if (btnSiguienteElement) {
                                            btnSiguienteElement.disabled = false;
                                            btnSiguienteElement.classList.remove("disabled");
                                        }
                                    });
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            console.error('Error al verificar enfermero:', error);

                            showErrorToast('No se pudo verificar el documento. Intente nuevamente.');

                            // Mantener los campos bloqueados en caso de error
                            bloquearCampos();

                            const btnSiguienteElement = document.getElementById("btnSiguiente");
                            if (btnSiguienteElement) {
                                btnSiguienteElement.disabled = true;
                                btnSiguienteElement.classList.add("disabled");
                            }
                        });
                }, 600); // Tiempo de espera simulado de 0.6 segundos
            }

            const btnCancelarElement = document.getElementById("btnCancelar");
            if (btnCancelarElement) {
                btnCancelarElement.addEventListener("click", function() {
                    resetForm();
                });
            } else {
                console.error("Elemento btnCancelar no encontrado");
            }

            // Implementar funcionalidad del botón editar/bloquear email
            const btnEditarEmailElement = document.getElementById("btnEditarEmail");
            if (btnEditarEmailElement) {
                btnEditarEmailElement.addEventListener("click", function() {
                    if (emailEditMode) {
                        // Si está en modo edición, cambiar a normal (bloquear)
                        setEmailState('normal');
                    } else {
                        // Si está en modo normal, cambiar a edición
                        setEmailState('edit');
                    }
                });
            } else {
                console.error("Elemento btnEditarEmail no encontrado");
            }

            // Validar el correo cuando se modifica
            const emailConfirmacionElement = document.getElementById("emailConfirmacion");
            if (emailConfirmacionElement) {
                emailConfirmacionElement.addEventListener("blur", function() {
                    if (this.value.trim()) {
                        const validacion = validateEmail(this.value);
                        if (validacion.isValid) {
                            markFieldAsValid(this);
                            removeFieldHelpMessage(this);
                            // Mostrar toast de éxito
                            showSuccessToast('Correo electrónico válido');
                        } else {
                            markFieldAsInvalid(this);
                            addFieldHelpMessage(this, validacion.message);
                            // Mostrar toast de error en lugar de alerta modal
                            showErrorToast(validacion.message);
                        }
                    }
                });

                // Validación en tiempo real
                emailConfirmacionElement.addEventListener('input', function() {
                    if (this.value.trim() && this.value.includes('@')) {
                        const validacion = validateEmail(this.value);
                        if (validacion.isValid) {
                            markFieldAsValid(this);
                            removeFieldHelpMessage(this);
                        } else {
                            markFieldAsInvalid(this);
                            addFieldHelpMessage(this, validacion.message);
                        }
                    } else if (!this.value.trim()) {
                        // Si está vacío, quitar validaciones
                        this.classList.remove('is-valid', 'is-invalid');
                        removeFieldHelpMessage(this);
                    }
                });
            } else {
                console.error("Elemento emailConfirmacion no encontrado");
            }

            // Verificar correo electrónico al guardar credenciales (sin mostrar alerta de carga)
            const btnGuardarCredencialesElement = document.getElementById("btnGuardarCredenciales");
            if (btnGuardarCredencialesElement) {
                btnGuardarCredencialesElement.addEventListener("click", function() {
                    try {
                        if (validateCredenciales()) {
                            const emailConfirmacion = document.getElementById('emailConfirmacion');
                            if (!emailConfirmacion) {
                                console.error("Elemento emailConfirmacion no encontrado");
                                return;
                            }

                            const email = emailConfirmacion.value;

                            // Mostrar toast de carga en lugar de alerta modal
                            showSuccessToast('Verificando disponibilidad del correo...');

                            // Realizar la verificación sin mostrar la alerta de carga
                            verificarCorreoExistente(email)
                                .then(data => {
                                    if (data.disponible) {
                                        // Correo disponible, proceder con el registro
                                        credencialesCompletadas = true;

                                        if (credencialesModal) {
                                            credencialesModal.hide();
                                        }

                                        // Actualizar el email en el formulario principal
                                        const emailElement = document.getElementById('email');
                                        if (emailElement) {
                                            emailElement.value = email;
                                        }

                                        // Mostrar pantalla de confirmación
                                        const personalInfoCard = document.getElementById("personalInfoCard");
                                        const confirmacionCard = document.getElementById("confirmacionCard");

                                        if (personalInfoCard && confirmacionCard) {
                                            personalInfoCard.classList.add("d-none");
                                            confirmacionCard.classList.remove("d-none");

                                            // Cargar datos de confirmación
                                            loadConfirmationData();
                                        } else {
                                            console.error("Elementos de tarjetas no encontrados");
                                        }
                                    } else {
                                        // Correo ya en uso, mostrar error y permitir edición
                                        setEmailState('error');

                                        // Mostrar mensaje de error con toast en lugar de alerta
                                        showErrorToast('Este correo electrónico ya está en uso como usuario');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error al verificar correo:', error);

                                    // Usar toast en lugar de alerta
                                    showErrorToast('No se pudo verificar la disponibilidad del correo. Intente nuevamente.');
                                });
                        }
                    } catch (error) {
                        console.error("Error al guardar credenciales:", error);
                        showErrorToast('Ha ocurrido un error al verificar las credenciales. Por favor, inténtelo de nuevo.');
                    }
                });
            } else {
                console.error("Elemento btnGuardarCredenciales no encontrado");
            }

            const btnGuardarElement = document.getElementById("btnGuardar");
            if (btnGuardarElement) {
                btnGuardarElement.addEventListener("click", function(e) {
                    e.preventDefault();
                    if (credencialesCompletadas) {
                        guardarEnfermero();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Credenciales incompletas',
                            text: 'Debe completar las credenciales de acceso antes de guardar.'
                        });
                    }
                });
            } else {
                console.error("Elemento btnGuardar no encontrado");
            }

            // IMPORTANTE: Asegurar que el botón de búsqueda está correctamente vinculado
            const btnBuscar = document.getElementById("btnBuscarPersona");
            if (btnBuscar) {
                btnBuscar.addEventListener("click", function(e) {
                    e.preventDefault(); // Prevenir comportamiento por defecto
                    buscarPersonaPorDocumento();
                });
            } else {
                console.error("Elemento btnBuscarPersona no encontrado");
            }

            // Controladores de eventos para cambios en tipo y número de documento
            if (tipodocElement) {
                tipodocElement.addEventListener("change", function() {
                    const tipodoc = this.value;
                    // Configurar validación según tipo de documento seleccionado
                    if (tipodoc && documentoConfig[tipodoc] && nrodocInput) {
                        const maxLength = documentoConfig[tipodoc].length;
                        nrodocInput.setAttribute("maxlength", maxLength);
                        console.log(`Tipo de documento cambiado a ${tipodoc}. Longitud máxima: ${maxLength}`);

                        // Desbloquear el campo de número de documento
                        nrodocInput.disabled = false;
                        // Restablecer el número de documento
                        nrodocInput.value = "";
                    }

                    // Volver a bloquear otros campos y botones
                    bloquearCampos();

                    if (btnSiguienteElement) {
                        btnSiguienteElement.disabled = true;
                        btnSiguienteElement.classList.add("disabled");
                    }

                    // Limpiar mensajes de error
                    if (nrodocInput) {
                        removeFieldHelpMessage(nrodocInput);
                    }
                });
            }

            if (nrodocInput) {
                nrodocInput.addEventListener("input", function() {
                    // Al cambiar el número de documento, volver a bloquear campos y botones
                    bloquearCampos();

                    if (btnSiguienteElement) {
                        btnSiguienteElement.disabled = true;
                        btnSiguienteElement.classList.add("disabled");
                    }

                    // Validar formato según tipo de documento
                    const tipodoc = tipodocElement ? tipodocElement.value : '';
                    if (tipodoc && documentoConfig[tipodoc]) {
                        // Validar el patrón si hay valor
                        if (this.value && documentoConfig[tipodoc].pattern.test(this.value)) {
                            markFieldAsValid(this);
                            removeFieldHelpMessage(this);
                        } else if (this.value) {
                            markFieldAsInvalid(this);
                            addFieldHelpMessage(this, documentoConfig[tipodoc].message);
                        } else {
                            removeFieldHelpMessage(this);
                        }
                    }
                });
            }

            // Configurar validación de teléfono
            const telefono = document.getElementById('telefono');
            if (telefono) {
                telefono.setAttribute('maxlength', 9);
                telefono.addEventListener('input', function() {
                    // Eliminar cualquier carácter que no sea un número
                    this.value = this.value.replace(/\D/g, '');

                    // Asegurar que comience con 9
                    if (this.value.length > 0 && this.value.charAt(0) !== '9') {
                        this.value = '9' + this.value.substring(1);
                    }

                    // Validar el formato del teléfono
                    if (this.value.length === 9 && this.value.charAt(0) === '9') {
                        markFieldAsValid(this);
                        removeFieldHelpMessage(this);
                    } else if (this.value.length > 0) {
                        markFieldAsInvalid(this);
                        addFieldHelpMessage(this, 'El teléfono debe tener 9 dígitos y comenzar con 9');
                    } else {
                        removeFieldHelpMessage(this);
                    }
                });
            } else {
                console.error("Elemento telefono no encontrado");
            }

            // Validar email
            const email = document.getElementById('email');
            if (email) {
                email.addEventListener('blur', function() {
                    if (this.value.trim()) {
                        const validacion = validateEmail(this.value);
                        if (validacion.isValid) {
                            markFieldAsValid(this);
                            removeFieldHelpMessage(this);
                        } else {
                            markFieldAsInvalid(this);
                            addFieldHelpMessage(this, validacion.message);
                            showErrorToast(validacion.message);
                        }
                    } else {
                        markFieldAsInvalid(this);
                        addFieldHelpMessage(this, 'El correo electrónico es obligatorio');
                    }
                });

                // Validación en tiempo real mientras se escribe
                email.addEventListener('input', function() {
                    if (this.value.trim()) {
                        // Validar solo cuando tenga @
                        if (this.value.includes('@')) {
                            const validacion = validateEmail(this.value);
                            if (validacion.isValid) {
                                markFieldAsValid(this);
                                removeFieldHelpMessage(this);
                            } else {
                                markFieldAsInvalid(this);
                                addFieldHelpMessage(this, validacion.message);
                            }
                        }
                    } else {
                        // Si está vacío, quitar validaciones
                        this.classList.remove('is-valid', 'is-invalid');
                        removeFieldHelpMessage(this);
                    }
                });
            } else {
                console.error("Elemento email no encontrado");
            }

            // Configuración de validaciones por tipo de documento
            const documentoConfig = {
                'DNI': {
                    length: 8,
                    pattern: /^\d{8}$/,
                    message: 'El DNI debe tener 8 dígitos numéricos'
                },
                'PASAPORTE': {
                    length: 12,
                    pattern: /^[A-Z0-9]{6,12}$/,
                    message: 'El pasaporte debe tener entre 6 y 12 caracteres alfanuméricos'
                },
                'CARNET DE EXTRANJERIA': {
                    length: 9,
                    pattern: /^[A-Z0-9]{9}$/,
                    message: 'El carnet de extranjería debe tener 9 caracteres alfanuméricos'
                },
                'OTRO': {
                    length: 15,
                    pattern: /^.{1,15}$/,
                    message: 'El documento puede tener hasta 15 caracteres'
                }
            };

            // Función para verificar si el correo electrónico ya existe
            function verificarCorreoExistente(email) {
                return new Promise((resolve, reject) => {
                    const formData = new FormData();
                    formData.append('email', email);

                    fetch('../../../controllers/credencial.controller.php?op=verificar_usuario', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Error en la respuesta del servidor: ' + response.status);
                            }
                            return response.json();
                        })
                        .then(data => {
                            resolve(data);
                        })
                        .catch(error => {
                            console.error('Error al verificar correo:', error);
                            reject(error);
                        });
                });
            }

            // Función para validar la información personal
            function validatePersonalInfo() {
                const requiredFields = [
                    'tipodoc', 'nrodoc', 'apellidos', 'nombres', 'telefono', 'email'
                ];

                let isValid = true;
                let firstInvalidField = null;

                requiredFields.forEach(field => {
                    const input = document.getElementById(field);
                    if (!input) {
                        console.error(`Elemento ${field} no encontrado durante la validación`);
                        isValid = false;
                        return;
                    }

                    if (!input.value.trim()) {
                        markFieldAsInvalid(input);
                        addFieldHelpMessage(input, 'Este campo es obligatorio');
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = input;
                    } else {
                        markFieldAsValid(input);
                        removeFieldHelpMessage(input);
                    }
                });

                // Validar fecha de nacimiento - solo verificamos si es válida, no mostramos confirmación aquí
                // ya que eso se maneja en el evento 'change' del campo
                const fechaNacimiento = document.getElementById('fechanacimiento');
                if (fechaNacimiento && fechaNacimiento.value) {
                    const resultado = validarFechaNacimiento(fechaNacimiento, false);
                    if (!resultado.esValida) {
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = fechaNacimiento;
                    }
                }

                // Validar formato del documento según el tipo
                const tipodoc = tipodocElement ? tipodocElement.value : '';
                const nrodoc = document.getElementById('nrodoc');

                if (tipodoc && nrodoc && nrodoc.value) {
                    const config = documentoConfig[tipodoc];
                    if (config && !config.pattern.test(nrodoc.value)) {
                        markFieldAsInvalid(nrodoc);
                        addFieldHelpMessage(nrodoc, config.message);
                        showErrorToast(config.message);
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = nrodoc;
                    }
                }

                // Validar teléfono (debe tener 9 dígitos y comenzar con 9)
                if (telefono && telefono.value && !/^9\d{8}$/.test(telefono.value)) {
                    markFieldAsInvalid(telefono);
                    addFieldHelpMessage(telefono, 'El teléfono debe tener 9 dígitos y comenzar con 9');
                    showErrorToast('El teléfono debe tener 9 dígitos y comenzar con 9');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = telefono;
                }

                // Validar email en validatePersonalInfo
                if (email && email.value) {
                    const validacionEmail = validateEmail(email.value);
                    if (!validacionEmail.isValid) {
                        markFieldAsInvalid(email);
                        addFieldHelpMessage(email, validacionEmail.message);
                        showErrorToast(validacionEmail.message);
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = email;
                    }
                }

                if (!isValid) {
                    // Reemplazar alerta modal por un toast
                    showErrorToast('Por favor, complete todos los campos obligatorios');

                    // Hacer scroll al primer campo con error
                    if (firstInvalidField) {
                        firstInvalidField.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        firstInvalidField.focus();
                    }
                }

                return isValid;
            }

            // Función para validar fecha de nacimiento
            function validarFechaNacimiento(inputFecha, mostrarToast = false) {
                // Objeto que retornaremos con el resultado de la validación
                const resultado = {
                    esValida: true,
                    edad: 0
                };

                if (!inputFecha.value) {
                    // Si el campo está vacío, lo dejamos neutro
                    inputFecha.classList.remove('is-valid', 'is-invalid');
                    removeFieldHelpMessage(inputFecha);
                    return resultado;
                }

                const fechaNacimiento = new Date(inputFecha.value);
                const hoy = new Date();
                const anioMinimo = 1920;

                // Validar año mínimo (1920)
                if (fechaNacimiento.getFullYear() < anioMinimo) {
                    markFieldAsInvalid(inputFecha);
                    addFieldHelpMessage(inputFecha, `El año de nacimiento no puede ser anterior a ${anioMinimo}`);
                    if (mostrarToast) {
                        showErrorToast(`El año de nacimiento no puede ser anterior a ${anioMinimo}`);
                    }
                    resultado.esValida = false;
                    return resultado;
                }

                // Validar que no sea fecha futura
                if (fechaNacimiento > hoy) {
                    markFieldAsInvalid(inputFecha);
                    addFieldHelpMessage(inputFecha, 'La fecha de nacimiento no puede ser en el futuro');
                    if (mostrarToast) {
                        showErrorToast('La fecha de nacimiento no puede ser en el futuro');
                    }
                    resultado.esValida = false;
                    return resultado;
                }

                // Calcular edad
                const edad = calcularEdad(fechaNacimiento);
                resultado.edad = edad;

                // Validar edad mínima (18 años)
                if (edad < 18) {
                    markFieldAsInvalid(inputFecha);
                    addFieldHelpMessage(inputFecha, 'El enfermero debe ser mayor de edad (al menos 18 años)');
                    if (mostrarToast) {
                        showErrorToast('El enfermero debe ser mayor de edad (al menos 18 años)');
                    }
                    resultado.esValida = false;
                    return resultado;
                }

                // Marcar como válido
                markFieldAsValid(inputFecha);
                removeFieldHelpMessage(inputFecha);
                if (mostrarToast) {
                    showSuccessToast('Fecha de nacimiento válida');
                }

                return resultado;
            }

            // Función para calcular edad
            function calcularEdad(fechaNacimiento) {
                const hoy = new Date();
                let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
                const mes = hoy.getMonth() - fechaNacimiento.getMonth();

                // Ajustar edad si aún no ha cumplido años en el año actual
                if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
                    edad--;
                }

                return edad;
            }

            // Función para validar las credenciales en el modal
            function validateCredenciales() {
                const passuser = document.getElementById('passuser');
                const confirmarpassuser = document.getElementById('confirmarpassuser');
                const emailConfirmacion = document.getElementById('emailConfirmacion');

                // Verificar que los elementos existan
                if (!passuser || !confirmarpassuser || !emailConfirmacion) {
                    console.error("Elementos de credenciales no encontrados");
                    return false;
                }

                let isValid = true;

                // Validar que el correo tenga formato válido
                const validacionEmail = validateEmail(emailConfirmacion.value);
                if (!validacionEmail.isValid) {
                    // Marcar campo como inválido en lugar de mostrar alerta
                    markFieldAsInvalid(emailConfirmacion);
                    addFieldHelpMessage(emailConfirmacion, validacionEmail.message);
                    showErrorToast(validacionEmail.message);
                    isValid = false;
                }

                // Validar que haya ingresado contraseña
                if (!passuser.value) {
                    markFieldAsInvalid(passuser);
                    addFieldHelpMessage(passuser, 'Debe ingresar una contraseña');
                    showErrorToast('Debe ingresar una contraseña');
                    isValid = false;
                } else if (passuser.value.length < 6) {
                    // Validar longitud de contraseña
                    markFieldAsInvalid(passuser);
                    addFieldHelpMessage(passuser, 'La contraseña debe tener al menos 6 caracteres');
                    showErrorToast('La contraseña debe tener al menos 6 caracteres');
                    isValid = false;
                } else {
                    markFieldAsValid(passuser);
                    removeFieldHelpMessage(passuser);
                }

                // Validar que las contraseñas coincidan
                if (passuser.value && confirmarpassuser.value && passuser.value !== confirmarpassuser.value) {
                    markFieldAsInvalid(confirmarpassuser);
                    addFieldHelpMessage(confirmarpassuser, 'Las contraseñas no coinciden');
                    showErrorToast('Las contraseñas no coinciden');
                    isValid = false;
                } else if (confirmarpassuser.value) {
                    markFieldAsValid(confirmarpassuser);
                    removeFieldHelpMessage(confirmarpassuser);
                } else {
                    markFieldAsInvalid(confirmarpassuser);
                    addFieldHelpMessage(confirmarpassuser, 'Debe confirmar la contraseña');
                    showErrorToast('Debe confirmar la contraseña');
                    isValid = false;
                }

                return isValid;
            }

            // Función para validar formato de email
            function validateEmail(email) {
                // Validación básica de formato
                const basicRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!basicRegex.test(email)) {
                    return {
                        isValid: false,
                        message: 'El formato del correo electrónico es inválido. Debe ser usuario@dominio.extensión'
                    };
                }

                // Lista de dominios de correo válidos
                const dominiosValidos = [
                    'gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.com', 'yahoo.es',
                    'live.com', 'msn.com', 'icloud.com', 'me.com', 'aol.com',
                    'protonmail.com', 'pm.me', 'zoho.com', 'yandex.com',
                    'gmx.com', 'gmx.net', 'mail.com', 'inbox.com',
                    'mail.ru', 'tutanota.com', 'tutanota.de', 'tutamail.com',
                    'outlook.es', 'hotmail.es'
                ];

                // Verificar si el dominio es válido
                const dominio = email.split('@')[1].toLowerCase();

                // Si es un correo corporativo o institucional (pueden tener dominios personalizados)
                if (dominio.includes('.pe') || dominio.includes('.edu') || dominio.includes('.org') ||
                    dominio.includes('.gob') || dominio.includes('.net') || dominio.includes('.mil') ||
                    dominio.includes('.int') || dominio.includes('.gov')) {
                    return {
                        isValid: true,
                        message: ''
                    };
                }

                // Verificar si está en la lista de dominios válidos
                if (dominiosValidos.includes(dominio)) {
                    return {
                        isValid: true,
                        message: ''
                    };
                }

                return {
                    isValid: false,
                    message: `Dominio de correo '${dominio}' no reconocido. Por favor utilice un proveedor de correo conocido como gmail.com, hotmail.com, etc.`
                };
            }

            // Función para marcar campos como válidos
            function markFieldAsValid(field) {
                if (!field) {
                    console.error("Intento de marcar como válido un campo nulo");
                    return;
                }
                field.classList.add('is-valid');
                field.classList.remove('is-invalid');
            }

            // Función para marcar campos como inválidos
            function markFieldAsInvalid(field) {
                if (!field) {
                    console.error("Intento de marcar como inválido un campo nulo");
                    return;
                }
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
            }

            // Función para añadir mensaje de ayuda junto al campo
            function addFieldHelpMessage(field, message) {
                if (!field) {
                    console.error("Intento de añadir mensaje de ayuda a un campo nulo");
                    return;
                }

                // Eliminar mensaje previo si existe
                removeFieldHelpMessage(field);

                // Crear nuevo mensaje
                const helpDiv = document.createElement('div');
                helpDiv.className = field.classList.contains('is-invalid') ? 'invalid-feedback' : 'valid-feedback';
                helpDiv.id = `help-${field.id}`;
                helpDiv.textContent = message;
                helpDiv.style.display = 'block'; // Asegurar que siempre sea visible

                // Insertar después del campo
                if (field.parentNode) {
                    field.parentNode.appendChild(helpDiv);
                } else {
                    console.error("El campo no tiene un nodo padre para adjuntar el mensaje");
                }
            }

            // Función para eliminar mensaje de ayuda
            function removeFieldHelpMessage(field) {
                if (!field) {
                    console.error("Intento de eliminar mensaje de ayuda de un campo nulo");
                    return;
                }

                const helpDiv = document.getElementById(`help-${field.id}`);
                if (helpDiv && helpDiv.parentNode) {
                    helpDiv.parentNode.removeChild(helpDiv);
                }
            }

            // Función para mostrar toast de error personalizado como en la imagen 2
            function showCustomErrorToast(message, icon = 'error') {
                // Crear el elemento de toast
                const toastElement = document.createElement('div');
                toastElement.className = 'custom-toast';
                toastElement.innerHTML = `
            <div class="custom-toast-content">
                <div class="custom-toast-icon">
                    ${icon === 'error' ? '<i class="fas fa-times-circle" style="color: #f27474;"></i>' : '<i class="fas fa-exclamation-circle"></i>'}
                </div>
                <div class="custom-toast-message">${message}</div>
            </div>
        `;

                // Establecer estilos para el toast
                Object.assign(toastElement.style, {
                    position: 'fixed',
                    top: '20px',
                    right: '20px',
                    zIndex: '9999',
                    backgroundColor: 'white',
                    borderRadius: '4px',
                    boxShadow: '0 2px 10px rgba(0,0,0,0.1)',
                    padding: '10px 15px',
                    maxWidth: '350px',
                    animation: 'fadeIn 0.5s ease'
                });

                // Estilos para el contenido del toast
                const contentElement = toastElement.querySelector('.custom-toast-content');
                Object.assign(contentElement.style, {
                    display: 'flex',
                    alignItems: 'center'
                });

                // Estilos para el icono
                const iconElement = toastElement.querySelector('.custom-toast-icon');
                Object.assign(iconElement.style, {
                    marginRight: '12px',
                    fontSize: '24px'
                });

                // Añadir la animación al documento si no existe
                if (!document.getElementById('toast-animations')) {
                    const styleElement = document.createElement('style');
                    styleElement.id = 'toast-animations';
                    styleElement.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(-20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                @keyframes fadeOut {
                    from { opacity: 1; transform: translateY(0); }
                    to { opacity: 0; transform: translateY(-20px); }
                }
            `;
                    document.head.appendChild(styleElement);
                }

                // Añadir el toast al documento
                document.body.appendChild(toastElement);

                // Configurar el temporizador para ocultar el toast
                setTimeout(() => {
                    toastElement.style.animation = 'fadeOut 0.5s ease';
                    setTimeout(() => {
                        document.body.removeChild(toastElement);
                    }, 500);
                }, 4000);
            }

            // Función para cargar los datos en la pantalla de confirmación
            function loadConfirmationData() {
                // Verificar que los elementos existan
                const tipodocElement = document.getElementById('tipodoc');
                const nrodocElement = document.getElementById('nrodoc');
                const apellidosElement = document.getElementById('apellidos');
                const nombresElement = document.getElementById('nombres');
                const fechanacimientoElement = document.getElementById('fechanacimiento');
                const generoElement = document.getElementById('genero');
                const telefonoElement = document.getElementById('telefono');
                const emailElement = document.getElementById('email');
                const direccionElement = document.getElementById('direccion');
                const confirmationContentElement = document.getElementById('confirmationContent');

                if (!confirmationContentElement) {
                    console.error("Elemento confirmationContent no encontrado");
                    return;
                }

                const datosEnfermero = {
                    tipodoc: tipodocElement ? tipodocElement.value : '',
                    nrodoc: nrodocElement ? nrodocElement.value : '',
                    apellidos: apellidosElement ? apellidosElement.value : '',
                    nombres: nombresElement ? nombresElement.value : '',
                    fechanacimiento: fechanacimientoElement ? fechanacimientoElement.value : '',
                    genero: generoElement ?
                        (generoElement.value === 'M' ? 'Masculino' :
                            (generoElement.value === 'F' ? 'Femenino' : 'Otro')) : '',
                    telefono: telefonoElement ? telefonoElement.value : '',
                    email: emailElement ? emailElement.value : '',
                    direccion: (direccionElement && direccionElement.value) ? direccionElement.value : 'No especificado'
                };

                let htmlDatosPersonales = `
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user-nurse me-2"></i> Información Personal</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Tipo de Documento:</strong> ${datosEnfermero.tipodoc}</p>
                        <p><strong>Número de Documento:</strong> ${datosEnfermero.nrodoc}</p>
                        <p><strong>Apellidos:</strong> ${datosEnfermero.apellidos}</p>
                        <p><strong>Nombres:</strong> ${datosEnfermero.nombres}</p>
                        <p><strong>Fecha de Nacimiento:</strong> ${formatDate(datosEnfermero.fechanacimiento)}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Género:</strong> ${datosEnfermero.genero}</p>
                        <p><strong>Teléfono:</strong> ${datosEnfermero.telefono}</p>
                        <p><strong>Email:</strong> ${datosEnfermero.email}</p>
                        <p><strong>Dirección:</strong> ${datosEnfermero.direccion}</p>
                    </div>
                </div>
            </div>
        </div>
        `;

                let htmlDatosAcceso = `
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-key me-2"></i> Datos de Acceso</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <p><strong>Usuario:</strong> ${datosEnfermero.email}</p>
                        <p><strong>Contraseña:</strong> ********</p>
                    </div>
                </div>
            </div>
        </div>
        `;

                // Actualizar el contenido de confirmación
                confirmationContentElement.innerHTML = htmlDatosPersonales + htmlDatosAcceso;
            }

            // Función para mostrar toast de éxito
            function showSuccessToast(message) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });

                Toast.fire({
                    icon: "success",
                    title: message
                });
            }

            // Función para mostrar toast de error
            function showErrorToast(message) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });

                Toast.fire({
                    icon: "error",
                    title: message
                });
            }

            // Función para cargar los datos de la persona en el formulario
            function cargarDatosPersona(persona, mantenerBloqueados = false) {
                // Verificar que los elementos existan
                const apellidosElement = document.getElementById('apellidos');
                const nombresElement = document.getElementById('nombres');
                const fechanacimientoElement = document.getElementById('fechanacimiento');
                const generoElement = document.getElementById('genero');
                const telefonoElement = document.getElementById('telefono');
                const emailElement = document.getElementById('email');
                const direccionElement = document.getElementById('direccion');

                // Cargar datos básicos en los campos correspondientes
                if (apellidosElement) apellidosElement.value = persona.apellidos || '';
                if (nombresElement) nombresElement.value = persona.nombres || '';
                if (fechanacimientoElement) fechanacimientoElement.value = persona.fechanacimiento || '';
                if (generoElement) generoElement.value = persona.genero || 'M';
                if (telefonoElement) telefonoElement.value = persona.telefono || '';
                if (emailElement) emailElement.value = persona.email || '';
                if (direccionElement) direccionElement.value = persona.direccion || '';

                // Marcar todos los campos como válidos después de cargar los datos
                const campos = ['apellidos', 'nombres', 'fechanacimiento', 'genero', 'telefono', 'email', 'direccion'];
                campos.forEach(campo => {
                    const input = document.getElementById(campo);
                    if (input && input.value) {
                        markFieldAsValid(input);
                        removeFieldHelpMessage(input);
                        // Asegurarnos de que no haya atributos que impidan enviar el valor
                        if (input.hasAttribute('readonly')) {
                            input.removeAttribute('readonly');
                        }
                    }
                });

                // Validar específicamente el correo electrónico si existe
                if (emailElement && emailElement.value) {
                    const validacion = validateEmail(emailElement.value);
                    if (validacion.isValid) {
                        markFieldAsValid(emailElement);
                    } else {
                        markFieldAsInvalid(emailElement);
                        addFieldHelpMessage(emailElement, validacion.message);
                    }
                }

                // Validar fecha de nacimiento si existe
                if (fechanacimientoElement && fechanacimientoElement.value) {
                    const resultado = validarFechaNacimiento(fechanacimientoElement, false);
                    if (!resultado.esValida) {
                        markFieldAsInvalid(fechanacimientoElement);
                    }
                }

                // Si se debe mantener bloqueados, asegurarse de que están deshabilitados
                // pero permitiendo que los valores se envíen en el formulario
                if (mantenerBloqueados) {
                    campos.forEach(campo => {
                        const input = document.getElementById(campo);
                        if (input) {
                            // Usar readonly en lugar de disabled para permitir enviar valores
                            input.setAttribute('readonly', 'readonly');
                            input.style.backgroundColor = '#e9ecef';
                            input.style.cursor = 'not-allowed';
                        }
                    });
                }
            }

            // Función para bloquear todos los campos excepto tipo y número de documento
            function bloquearCampos() {
                const camposABloquear = [
                    'apellidos', 'nombres', 'fechanacimiento',
                    'genero', 'telefono', 'email', 'direccion'
                ];

                camposABloquear.forEach(campo => {
                    const input = document.getElementById(campo);
                    if (!input) {
                        console.error(`Elemento ${campo} no encontrado en bloquearCampos`);
                        return;
                    }

                    input.disabled = true;

                    // Limpiar validaciones visuales
                    input.classList.remove('is-invalid', 'is-valid');
                    removeFieldHelpMessage(input);
                });
            }

            // Función para desbloquear todos los campos
            function desbloquearCampos() {
                const camposADesbloquear = [
                    'apellidos', 'nombres', 'fechanacimiento',
                    'genero', 'telefono', 'email', 'direccion'
                ];

                camposADesbloquear.forEach(campo => {
                    const input = document.getElementById(campo);
                    if (!input) {
                        console.error(`Elemento ${campo} no encontrado en desbloquearCampos`);
                        return;
                    }

                    input.disabled = false;

                    // Asegurarnos de que el valor pueda ser enviado en el formulario
                    if (input.hasAttribute('readonly')) {
                        input.removeAttribute('readonly');
                    }
                });
            }

            // Función para limpiar todos los campos del formulario
            function limpiarCampos() {
                // Verificar que los elementos existan
                const apellidosElement = document.getElementById('apellidos');
                const nombresElement = document.getElementById('nombres');
                const fechanacimientoElement = document.getElementById('fechanacimiento');
                const generoElement = document.getElementById('genero');
                const telefonoElement = document.getElementById('telefono');
                const emailElement = document.getElementById('email');
                const direccionElement = document.getElementById('direccion');
                const passuserElement = document.getElementById('passuser');
                const confirmarpassuserElement = document.getElementById('confirmarpassuser');

                // Limpiar datos personales
                if (apellidosElement) apellidosElement.value = '';
                if (nombresElement) nombresElement.value = '';
                if (fechanacimientoElement) fechanacimientoElement.value = '';
                if (generoElement) generoElement.value = 'M'; // Valor por defecto
                if (telefonoElement) telefonoElement.value = '';
                if (emailElement) emailElement.value = '';
                if (direccionElement) direccionElement.value = '';
                if (passuserElement) passuserElement.value = '';
                if (confirmarpassuserElement) confirmarpassuserElement.value = '';

                // Eliminar clases de validación
                const campos = ['apellidos', 'nombres', 'fechanacimiento', 'genero', 'telefono', 'email', 'direccion', 'passuser', 'confirmarpassuser'];
                campos.forEach(campo => {
                    const input = document.getElementById(campo);
                    if (input) {
                        input.classList.remove('is-valid', 'is-invalid');
                        removeFieldHelpMessage(input);
                    }
                });
            }

            // Función para resetear el formulario
            function resetForm() {
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "Se perderán todos los datos ingresados.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'No, continuar editando'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formElement = document.getElementById('nurseRegistrationForm');
                        if (formElement) formElement.reset();

                        limpiarCampos();

                        // Ocultar pantalla de confirmación si estuviera visible
                        const personalInfoCard = document.getElementById("personalInfoCard");
                        const confirmacionCard = document.getElementById("confirmacionCard");

                        if (personalInfoCard && confirmacionCard) {
                            personalInfoCard.classList.remove("d-none");
                            confirmacionCard.classList.add("d-none");
                        }

                        // Restablecer valores por defecto
                        const tipodocElement = document.getElementById("tipodoc");
                        const generoElement = document.getElementById("genero");
                        const nrodocElement = document.getElementById("nrodoc");

                        if (tipodocElement) tipodocElement.value = "DNI";
                        if (generoElement) generoElement.value = "M";

                        // Importante: Restablecer maxlength para DNI
                        if (nrodocElement) nrodocElement.setAttribute("maxlength", "8");

                        // Bloquear campos y botones
                        bloquearCampos();

                        const btnSiguienteElement = document.getElementById("btnSiguiente");
                        if (btnSiguienteElement) {
                            btnSiguienteElement.disabled = true;
                            btnSiguienteElement.classList.add("disabled");
                        }

                        // Reiniciar estado de credenciales
                        credencialesCompletadas = false;
                    }
                });
            }

            // Función para guardar enfermero - ACTUALIZADA CON ALERTA DE ÉXITO CON BOTÓN ACEPTAR
            function guardarEnfermero() {
                try {
                    // Mostrar loader con efecto de guardado
                    Swal.fire({
                        title: 'Guardando...',
                        html: `
                <p>Registrando enfermero</p>
                <div class="progress mt-3" style="height: 20px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: 100%" 
                         aria-valuenow="100" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                </div>
                <p class="mt-2">Por favor espere mientras se completa el registro.</p>
            `,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Preparar datos del formulario
                    const formData = new FormData();

                    // Validar que los elementos existan antes de acceder a sus valores
                    const campos = ['apellidos', 'nombres', 'tipodoc', 'nrodoc', 'telefono',
                        'fechanacimiento', 'genero', 'direccion', 'email',
                        'passuser', 'confirmarpassuser'
                    ];

                    for (const campo of campos) {
                        const elemento = document.getElementById(campo);
                        if (elemento) {
                            formData.append(campo, elemento.value || '');
                        } else {
                            console.warn(`El elemento ${campo} no existe en el DOM`);
                            formData.append(campo, '');
                        }
                    }

                    // Enviar datos del enfermero
                    fetch('../../../controllers/enfermeria.controller.php?op=registrar_enfermero', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Error en la respuesta del servidor: ' + response.status);
                            }
                            return response.json().catch(e => {
                                // Si no podemos parsear como JSON, mostrar el texto
                                return response.text().then(text => {
                                    throw new Error('Respuesta no JSON: ' + text);
                                });
                            });
                        })
                        .then(data => {
                            if (data.status) {
                                // Cerrar el loader
                                Swal.close();

                                // Mostrar alerta de éxito CON botón de aceptar (como en la imagen 1)
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Registro exitoso!',
                                    text: 'El enfermero ha sido registrado correctamente.',
                                    showConfirmButton: true,
                                    confirmButtonText: 'Aceptar',
                                    confirmButtonColor: '#6f42c1',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        // Aplicar animación al icono
                                        const iconElement = Swal.getIcon();
                                        if (iconElement) {
                                            iconElement.classList.add('animate__animated', 'animate__bounceIn');
                                        }
                                    }
                                }).then(() => {
                                    // Redireccionar a la página de listado cuando haga clic en Aceptar
                                    window.location.href = '../ListarEnfermero/listarEnfermero.php';
                                });
                            } else {
                                // Mostrar mensaje de error en forma de toast
                                Swal.close();
                                showErrorToast(data.mensaje || 'No se pudo registrar el enfermero');
                            }
                        })
                        .catch(error => {
                            console.error('Error al registrar enfermero:', error);
                            Swal.close();
                            showErrorToast('No se pudo completar el registro: ' + error.message);
                        });
                } catch (error) {
                    console.error('Error en la función guardarEnfermero:', error);
                    showErrorToast('Ocurrió un error al procesar el formulario: ' + error.message);
                }
            }

            // Función para formatear fecha
            function formatDate(dateString) {
                if (!dateString) return 'No especificado';

                const date = new Date(dateString);
                return date.toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            }
        });
    </script>

</body>

