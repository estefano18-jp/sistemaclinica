<?php
require_once '../../include/header.administrador.php'; ?>

<head>
    <!-- Enlace al archivo CSS -->
    <link rel="stylesheet" href="../../../css/registrarPaciente.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2 class="text-center mb-3">Registro de Paciente</h2>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>

        <form id="patientRegistrationForm" method="POST">
            <!-- Información Personal -->
            <div class="card" id="personalInfoCard">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-user me-2"></i> Información Personal
                    </div>
                    <div>
                        <button type="button" id="btnAgregarAlergia" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i> Agregar Alergia
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tipo y Número de Documento -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="tipodoc" class="form-label required-field">Tipo de Documento</label>
                            <select class="form-select" id="tipodoc" name="tipodoc" required>
                                <option value="">Seleccione...</option>
                                <option value="DNI">DNI</option>
                                <option value="PASAPORTE">Pasaporte</option>
                                <option value="CARNET DE EXTRANJERIA">Carnet de Extranjería</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="nrodoc" class="form-label required-field">Número de Documento</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="nrodoc" name="nrodoc" required autofocus>
                                <button type="button" class="btn btn-primary" id="btnBuscarDocumento">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="fechanacimiento" class="form-label required-field">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="fechanacimiento" name="fechanacimiento" required>
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
                            <label for="genero" class="form-label required-field">Género</label>
                            <select class="form-select" id="genero" name="genero" required>
                                <option value="">Seleccione...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="telefono" class="form-label required-field">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" required>
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="direccion" class="form-label required-field">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" required>
                        </div>
                    </div>

                    <!-- Lista de Alergias -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h5>Alergias</h5>
                            <div id="alergiasContainer" class="mb-3">
                                <div class="alert alert-info" id="noAlergias">
                                    No se han registrado alergias.
                                </div>
                                <div id="listaAlergias">
                                    <!-- Las alergias se agregarán aquí dinámicamente -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-0">
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

    <!-- Modal para agregar alergia -->
    <div class="modal fade" id="agregarAlergiaModal" tabindex="-1" aria-labelledby="agregarAlergiaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="agregarAlergiaModalLabel">Agregar Alergia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tipoalergia" class="form-label required-field">Tipo de Alergia</label>
                        <select class="form-select" id="tipoalergia" required>
                            <option value="">Seleccione...</option>
                            <option value="Alimentos">Alimentos</option>
                            <option value="Medicamentos">Medicamentos</option>
                            <option value="Ambiente">Ambiente</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="alergia" class="form-label required-field">Alergia</label>
                        <input type="text" class="form-control" id="alergia" required>
                    </div>
                    <div class="mb-3">
                        <label for="gravedad" class="form-label required-field">Gravedad</label>
                        <select class="form-select" id="gravedad" required>
                            <option value="">Seleccione...</option>
                            <option value="Leve">Leve</option>
                            <option value="Moderada">Moderada</option>
                            <option value="Grave">Grave</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarAlergia">Agregar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Variables para manejo de alergias
            let alergias = [];
            let currentTab = 'personalInfo';

            // NUEVO: Leer parámetros de la URL
            const urlParams = new URLSearchParams(window.location.search);
            const tipodocParam = urlParams.get('tipodoc');
            const nrodocParam = urlParams.get('nrodoc');

            console.log("Parámetros URL detectados:", {
                tipodoc: tipodocParam,
                nrodoc: nrodocParam
            });

            // Pre-completar el tipo de documento si está presente en la URL
            if (tipodocParam) {
                const tipodocSelect = document.getElementById('tipodoc');
                for (let i = 0; i < tipodocSelect.options.length; i++) {
                    if (tipodocSelect.options[i].value === tipodocParam) {
                        tipodocSelect.selectedIndex = i;
                        console.log(`Tipo de documento establecido a ${tipodocParam}`);

                        // Configurar maxlength y otras propiedades según el tipo
                        const nrodocInput = document.getElementById("nrodoc");
                        if (nrodocInput) {
                            if (tipodocParam === "DNI") {
                                nrodocInput.setAttribute("maxlength", "8");
                            } else if (tipodocParam === "PASAPORTE") {
                                nrodocInput.setAttribute("maxlength", "12");
                            } else if (tipodocParam === "CARNET DE EXTRANJERIA") {
                                nrodocInput.setAttribute("maxlength", "9");
                            } else {
                                nrodocInput.setAttribute("maxlength", "15");
                            }
                        }
                        break;
                    }
                }
            }

            // Pre-completar número de documento si está presente en la URL
            if (nrodocParam) {
                const nrodocInput = document.getElementById('nrodoc');
                if (nrodocInput) {
                    nrodocInput.value = nrodocParam;
                    console.log(`Número de documento pre-completado: ${nrodocParam}`);

                    // Simular clic en botón de búsqueda para validar de inmediato
                    setTimeout(() => {
                        const btnBuscar = document.getElementById('btnBuscarDocumento');
                        if (btnBuscar) {
                            console.log("Iniciando búsqueda automática de documento");
                            btnBuscar.click();
                        }
                    }, 300); // Pequeño retraso para asegurar que todo esté cargado
                }
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

            // Inicializar progreso
            updateProgressBar(50);

            // Establecer DNI como valor por defecto para tipo de documento
            document.getElementById("tipodoc").value = "DNI";

            // IMPORTANTE: Configurar maxlength para documento según el tipo por defecto (DNI = 8)
            const nrodocInput = document.getElementById("nrodoc");
            nrodocInput.setAttribute("maxlength", "8"); // Aseguramos que sea 8 para DNI (valor inicial)

            // Establecer Masculino como valor por defecto para género
            document.getElementById("genero").value = "M";

            // Establecer fecha máxima para fecha de nacimiento (hoy)
            const today = new Date();
            const formattedDate = today.toISOString().split('T')[0];
            document.getElementById("fechanacimiento").setAttribute("max", formattedDate);

            // Bloquear todos los campos excepto tipo y número de documento
            bloquearCampos();

            // Bloquear botones inicialmente
            document.getElementById("btnAgregarAlergia").disabled = true;
            document.getElementById("btnAgregarAlergia").classList.add("disabled");
            document.getElementById("btnSiguiente").disabled = true;
            document.getElementById("btnSiguiente").classList.add("disabled");

            // Inicializar botones de navegación
            document.getElementById("btnSiguiente").addEventListener("click", function() {
                if (validatePersonalInfo()) {
                    showTab('confirmacion');
                    updateProgressBar(100);
                    loadConfirmationData();
                }
            });

            document.getElementById("btnAnterior").addEventListener("click", function() {
                showTab('personalInfo');
                updateProgressBar(50);
            });

            document.getElementById("btnAgregarAlergia").addEventListener("click", function() {
                // Mostrar modal de alergia
                const alergiaModal = new bootstrap.Modal(document.getElementById('agregarAlergiaModal'));
                alergiaModal.show();
            });

            document.getElementById("btnGuardarAlergia").addEventListener("click", function() {
                agregarAlergia();
            });

            // IMPORTANTE: Asegurar que el botón de búsqueda está correctamente vinculado
            const btnBuscar = document.getElementById("btnBuscarDocumento");
            if (btnBuscar) {
                btnBuscar.addEventListener("click", function(e) {
                    e.preventDefault(); // Prevenir comportamiento por defecto
                    buscarPacientePorDocumento();
                });
            } else {
                console.error("El botón de búsqueda no existe en el DOM");
            }

            document.getElementById("btnGuardar").addEventListener("click", function(e) {
                e.preventDefault();
                guardarPaciente();
            });

            document.getElementById("btnCancelar").addEventListener("click", function() {
                resetForm();
            });

            // Controladores de eventos para cambios en tipo y número de documento
            document.getElementById("tipodoc").addEventListener("change", function() {
                const tipodoc = this.value;
                // Configurar validación según tipo de documento seleccionado
                if (tipodoc && documentoConfig[tipodoc]) {
                    const maxLength = documentoConfig[tipodoc].length;
                    nrodocInput.setAttribute("maxlength", maxLength);
                    console.log(`Tipo de documento cambiado a ${tipodoc}. Longitud máxima: ${maxLength}`);
                }

                // Desbloquear el campo de número de documento
                nrodocInput.disabled = false;
                // Restablecer el número de documento
                nrodocInput.value = "";
                // Volver a bloquear otros campos y botones
                bloquearCampos();
                document.getElementById("btnAgregarAlergia").disabled = true;
                document.getElementById("btnAgregarAlergia").classList.add("disabled");
                document.getElementById("btnSiguiente").disabled = true;
                document.getElementById("btnSiguiente").classList.add("disabled");

                // Limpiar mensajes de error
                removeFieldHelpMessage(nrodocInput);
            });

            nrodocInput.addEventListener("input", function() {
                // Al cambiar el número de documento, volver a bloquear campos y botones
                bloquearCampos();
                document.getElementById("btnAgregarAlergia").disabled = true;
                document.getElementById("btnAgregarAlergia").classList.add("disabled");
                document.getElementById("btnSiguiente").disabled = true;
                document.getElementById("btnSiguiente").classList.add("disabled");

                // Validar formato según tipo de documento
                const tipodoc = document.getElementById('tipodoc').value;
                if (tipodoc && documentoConfig[tipodoc]) {
                    // No necesitamos truncar manualmente ya que maxlength lo hace por nosotros en HTML5
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

            // Configurar validación de teléfono
            const telefono = document.getElementById('telefono');
            telefono.setAttribute('maxlength', 9);
            telefono.addEventListener('input', function() {
                // Eliminar cualquier carácter que no sea un número
                this.value = this.value.replace(/\D/g, '');

                // Asegurar que comience con 9
                if (this.value.length > 0 && this.value.charAt(0) !== '9') {
                    this.value = '9' + this.value.substring(1);
                }

                // Limitar a 9 dígitos (aunque maxlength ya lo hace)
                if (this.value.length > 9) {
                    this.value = this.value.substring(0, 9);
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

            // Agregar validación a todos los campos requeridos
            const requiredFields = [
                'apellidos', 'nombres', 'fechanacimiento',
                'genero', 'telefono', 'direccion'
            ];

            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                input.addEventListener('blur', function() {
                    if (this.value.trim()) {
                        markFieldAsValid(this);
                        removeFieldHelpMessage(this);
                    } else {
                        markFieldAsInvalid(this);
                        addFieldHelpMessage(this, 'Este campo es obligatorio');
                    }
                });

                // Para campos de texto, validar al cambiar
                if (input.type === 'text' || input.tagName === 'SELECT' || input.type === 'tel') {
                    input.addEventListener('input', function() {
                        if (this.value.trim()) {
                            markFieldAsValid(this);
                            removeFieldHelpMessage(this);
                        } else {
                            markFieldAsInvalid(this);
                            addFieldHelpMessage(this, 'Este campo es obligatorio');
                        }
                    });
                }
            });

            // Validar email (no requerido pero con formato)
            const email = document.getElementById('email');
            email.addEventListener('blur', function() {
                if (this.value.trim()) {
                    if (validateEmail(this.value)) {
                        markFieldAsValid(this);
                        removeFieldHelpMessage(this);
                    } else {
                        markFieldAsInvalid(this);
                        addFieldHelpMessage(this, 'Ingrese un correo electrónico válido');
                    }
                } else {
                    // Email vacío es válido (no es obligatorio)
                    this.classList.remove('is-invalid');
                    this.classList.remove('is-valid');
                    removeFieldHelpMessage(this);
                }
            });

            // Función para mostrar la pestaña correspondiente
            window.showTab = function(tab) {
                // Ocultar todas las tarjetas
                document.getElementById("personalInfoCard").classList.add("d-none");
                document.getElementById("confirmacionCard").classList.add("d-none");

                // Mostrar la tarjeta seleccionada
                document.getElementById(tab + "Card").classList.remove("d-none");
                currentTab = tab;
            }

            // Función para actualizar la barra de progreso
            function updateProgressBar(percentage) {
                const progressBar = document.querySelector(".progress-bar");
                progressBar.style.width = percentage + "%";
                progressBar.setAttribute("aria-valuenow", percentage);
            }

            // Función para validar la información personal
            function validatePersonalInfo() {
                const requiredFields = [
                    'tipodoc', 'nrodoc', 'apellidos', 'nombres', 'fechanacimiento',
                    'genero', 'telefono', 'direccion'
                ];

                let isValid = true;
                let firstInvalidField = null;

                requiredFields.forEach(field => {
                    const input = document.getElementById(field);
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

                // Validar que la fecha de nacimiento no sea en el futuro
                const fechaNacimiento = document.getElementById('fechanacimiento');
                if (fechaNacimiento.value) {
                    const fechaNacimientoDate = new Date(fechaNacimiento.value);
                    const hoy = new Date();

                    if (fechaNacimientoDate > hoy) {
                        markFieldAsInvalid(fechaNacimiento);
                        addFieldHelpMessage(fechaNacimiento, 'La fecha de nacimiento no puede ser en el futuro');
                        showErrorToast('La fecha de nacimiento no puede ser en el futuro');
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = fechaNacimiento;
                    }
                }

                // Validar formato del documento según el tipo
                const tipodoc = document.getElementById('tipodoc').value;
                const nrodoc = document.getElementById('nrodoc');

                if (tipodoc && nrodoc.value) {
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
                const telefono = document.getElementById('telefono');
                if (telefono.value && !/^9\d{8}$/.test(telefono.value)) {
                    markFieldAsInvalid(telefono);
                    addFieldHelpMessage(telefono, 'El teléfono debe tener 9 dígitos y comenzar con 9');
                    showErrorToast('El teléfono debe tener 9 dígitos y comenzar con 9');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = telefono;
                }

                // Validar email si está completo
                const email = document.getElementById('email');
                if (email.value && !validateEmail(email.value)) {
                    markFieldAsInvalid(email);
                    addFieldHelpMessage(email, 'El formato del correo electrónico es inválido');
                    showErrorToast('El formato del correo electrónico es inválido');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = email;
                }

                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Campos incompletos',
                        text: 'Por favor, complete todos los campos obligatorios.'
                    });

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

            // Función para validar formato de email
            function validateEmail(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }

            // Función para marcar campos como válidos
            function markFieldAsValid(field) {
                field.classList.add('is-valid');
                field.classList.remove('is-invalid');
            }

            // Función para marcar campos como inválidos
            function markFieldAsInvalid(field) {
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
            }

            // Función para añadir mensaje de ayuda junto al campo
            function addFieldHelpMessage(field, message) {
                if (!field) return;

                // Eliminar mensaje previo si existe
                removeFieldHelpMessage(field);

                // Crear nuevo mensaje
                const helpDiv = document.createElement('div');
                helpDiv.className = 'invalid-feedback';
                helpDiv.id = `help-${field.id}`;
                helpDiv.textContent = message;

                // Insertar después del campo
                if (field.parentNode) {
                    field.parentNode.appendChild(helpDiv);
                }
            }

            // Función para eliminar mensaje de ayuda
            function removeFieldHelpMessage(field) {
                if (!field) return;

                const helpDiv = document.getElementById(`help-${field.id}`);
                if (helpDiv && helpDiv.parentNode) {
                    helpDiv.parentNode.removeChild(helpDiv);
                }
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
                    timer: 3000,
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

            // Función para cargar los datos en la pantalla de confirmación
            function loadConfirmationData() {
                const datosPaciente = {
                    tipodoc: document.getElementById('tipodoc').value,
                    nrodoc: document.getElementById('nrodoc').value,
                    apellidos: document.getElementById('apellidos').value,
                    nombres: document.getElementById('nombres').value,
                    fechanacimiento: document.getElementById('fechanacimiento').value,
                    genero: document.getElementById('genero').value === 'M' ? 'Masculino' : (document.getElementById('genero').value === 'F' ? 'Femenino' : 'Otro'),
                    telefono: document.getElementById('telefono').value,
                    email: document.getElementById('email').value || 'No especificado',
                    direccion: document.getElementById('direccion').value
                };

                let htmlDatosPaciente = `
<div class="card mb-3">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-user me-2"></i> Información Personal</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Tipo de Documento:</strong> ${datosPaciente.tipodoc}</p>
                <p><strong>Número de Documento:</strong> ${datosPaciente.nrodoc}</p>
                <p><strong>Apellidos:</strong> ${datosPaciente.apellidos}</p>
                <p><strong>Nombres:</strong> ${datosPaciente.nombres}</p>
                <p><strong>Fecha de Nacimiento:</strong> ${formatDate(datosPaciente.fechanacimiento)}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Género:</strong> ${datosPaciente.genero}</p>
                <p><strong>Teléfono:</strong> ${datosPaciente.telefono}</p>
                <p><strong>Email:</strong> ${datosPaciente.email}</p>
                <p><strong>Dirección:</strong> ${datosPaciente.direccion}</p>
            </div>
        </div>
    </div>
</div>
`;

                // Mostrar datos de alergias si existen
                let htmlAlergias = '';
                if (alergias.length > 0) {
                    htmlAlergias = `
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-allergies me-2"></i> Alergias Registradas</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Alergia</th>
                            <th>Gravedad</th>
                        </tr>
                    </thead>
                    <tbody>
`;

                    alergias.forEach(alergia => {
                        htmlAlergias += `
        <tr>
            <td>${alergia.tipoalergia}</td>
            <td>${alergia.alergia}</td>
            <td>
                <span class="badge bg-${alergia.gravedad === 'Leve' ? 'success' : (alergia.gravedad === 'Moderada' ? 'warning' : 'danger')}">
                    ${alergia.gravedad}
                </span>
            </td>
        </tr>
    `;
                    });

                    htmlAlergias += `
                </tbody>
            </table>
        </div>
    </div>
</div>
`;
                } else {
                    htmlAlergias = `
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-allergies me-2"></i> Alergias Registradas</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-0">
                No se han registrado alergias para este paciente.
            </div>
        </div>
    </div>
`;
                }

                // Actualizar el contenido de confirmación
                document.getElementById('confirmationContent').innerHTML = htmlDatosPaciente + htmlAlergias;
            }

            // Función para agregar alergia
            window.agregarAlergia = function() {
                const tipoalergia = document.getElementById('tipoalergia').value;
                const alergia = document.getElementById('alergia').value;
                const gravedad = document.getElementById('gravedad').value;

                if (!tipoalergia || !alergia || !gravedad) {
                    showErrorToast('Por favor, complete todos los campos de la alergia.');

                    // Mostrar mensajes de error en los campos del modal
                    if (!tipoalergia) {
                        document.getElementById('tipoalergia').classList.add('is-invalid');
                        addFieldHelpMessage(document.getElementById('tipoalergia'), 'Seleccione un tipo de alergia');
                    }
                    if (!alergia) {
                        document.getElementById('alergia').classList.add('is-invalid');
                        addFieldHelpMessage(document.getElementById('alergia'), 'Ingrese la alergia');
                    }
                    if (!gravedad) {
                        document.getElementById('gravedad').classList.add('is-invalid');
                        addFieldHelpMessage(document.getElementById('gravedad'), 'Seleccione la gravedad');
                    }

                    return;
                }

                // Verificar si la alergia ya existe en la lista
                const alergiaExistente = alergias.find(item =>
                    item.tipoalergia === tipoalergia &&
                    item.alergia.toLowerCase() === alergia.toLowerCase()
                );

                if (alergiaExistente) {
                    showErrorToast('Esta alergia ya ha sido agregada a la lista.');
                    return;
                }

                // Agregar alergia al array
                alergias.push({
                    tipoalergia,
                    alergia,
                    gravedad
                });

                // Actualizar la visualización de alergias
                updateAlergiasDisplay();

                // Cerrar el modal
                const alergiaModal = bootstrap.Modal.getInstance(document.getElementById('agregarAlergiaModal'));
                alergiaModal.hide();

                // Limpiar campos del modal y mensajes de error
                document.getElementById('tipoalergia').value = '';
                document.getElementById('tipoalergia').classList.remove('is-invalid');
                removeFieldHelpMessage(document.getElementById('tipoalergia'));

                document.getElementById('alergia').value = '';
                document.getElementById('alergia').classList.remove('is-invalid');
                removeFieldHelpMessage(document.getElementById('alergia'));

                document.getElementById('gravedad').value = '';
                document.getElementById('gravedad').classList.remove('is-invalid');
                removeFieldHelpMessage(document.getElementById('gravedad'));

                // Mostrar notificación
                showSuccessToast('La alergia ha sido agregada correctamente.');
            }

            // Función para eliminar alergia
            window.eliminarAlergia = function(index) {
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "¿Desea eliminar esta alergia?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        alergias.splice(index, 1);
                        updateAlergiasDisplay();

                        showSuccessToast('La alergia ha sido eliminada correctamente.');
                    }
                });
            }

            // Función para actualizar la visualización de alergias
            function updateAlergiasDisplay() {
                const alergiasContainer = document.getElementById('listaAlergias');
                const noAlergiasMsg = document.getElementById('noAlergias');

                if (alergias.length === 0) {
                    noAlergiasMsg.classList.remove('d-none');
                    alergiasContainer.innerHTML = '';
                    return;
                }

                noAlergiasMsg.classList.add('d-none');

                let html = '';
                alergias.forEach((alergia, index) => {
                    html += `
    <div class="card mb-2 alergia-item">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center">
                <div class="me-auto">
                    <span class="badge bg-${alergia.gravedad === 'Leve' ? 'success' : (alergia.gravedad === 'Moderada' ? 'warning' : 'danger')} me-2">
                        ${alergia.gravedad}
                    </span>
                    <strong>${alergia.tipoalergia}:</strong> ${alergia.alergia}
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarAlergia(${index})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
`;
                });

                alergiasContainer.innerHTML = html;
            }

            // Función para resetear el formulario
            window.resetForm = function() {
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
                        document.getElementById('patientRegistrationForm').reset();
                        alergias = [];
                        updateAlergiasDisplay();
                        showTab('personalInfo');
                        updateProgressBar(50);

                        // Limpiar clases de validación y mensajes de ayuda
                        const inputs = document.querySelectorAll('.form-control, .form-select');
                        inputs.forEach(input => {
                            input.classList.remove('is-invalid');
                            input.classList.remove('is-valid');
                            removeFieldHelpMessage(input);
                        });

                        // Restablecer valores por defecto
                        document.getElementById("tipodoc").value = "DNI";
                        document.getElementById("genero").value = "M";

                        // Importante: Restablecer maxlength para DNI
                        document.getElementById("nrodoc").setAttribute("maxlength", "8");

                        // Bloquear campos y botones
                        bloquearCampos();
                        document.getElementById("btnAgregarAlergia").disabled = true;
                        document.getElementById("btnAgregarAlergia").classList.add("disabled");
                        document.getElementById("btnSiguiente").disabled = true;
                        document.getElementById("btnSiguiente").classList.add("disabled");
                    }
                });
            }

            // Función para limpiar todos los campos del formulario
            function limpiarCampos() {
                // Limpiar datos personales
                document.getElementById('apellidos').value = '';
                document.getElementById('nombres').value = '';
                document.getElementById('fechanacimiento').value = '';
                document.getElementById('genero').value = 'M'; // Valor por defecto
                document.getElementById('telefono').value = '';
                document.getElementById('email').value = '';
                document.getElementById('direccion').value = '';

                // Eliminar clases de validación
                const campos = ['apellidos', 'nombres', 'fechanacimiento', 'genero', 'telefono', 'email', 'direccion'];
                campos.forEach(campo => {
                    const input = document.getElementById(campo);
                    input.classList.remove('is-valid', 'is-invalid');
                    removeFieldHelpMessage(input);
                });

                // Limpiar alergias
                alergias = [];
                updateAlergiasDisplay();
            }

            // Función para buscar paciente por documento - Versión corregida
            function buscarPacientePorDocumento() {
                console.log("Función buscarPacientePorDocumento iniciada");

                const tipodoc = document.getElementById('tipodoc').value;
                const nrodoc = document.getElementById('nrodoc').value;

                if (!tipodoc || !nrodoc) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Seleccione un tipo de documento e ingrese un número de documento para buscar.'
                    });
                    return;
                }

                // Validar formato según tipo de documento
                const config = documentoConfig[tipodoc];
                if (config && !config.pattern.test(nrodoc)) {
                    markFieldAsInvalid(document.getElementById('nrodoc'));
                    addFieldHelpMessage(document.getElementById('nrodoc'), config.message);
                    showErrorToast(config.message);
                    return;
                }

                console.log(`Buscando documento ${tipodoc}: ${nrodoc}`);

                // Mostrar loader con efecto de búsqueda
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
            <p class="mt-2">Por favor espere mientras se busca el paciente.</p>
        `,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Simular tiempo de carga mínimo para mostrar la animación (al menos 1.5 segundos)
                setTimeout(() => {
                    // *** PASO 1: Verificar si ya existe como paciente ***
                    fetch(`../../../controllers/paciente.controller.php?operacion=verificar_documento&nrodoc=${nrodoc}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log("Respuesta verificación paciente:", data);

                            if (data.existe) {
                                // Ya existe como paciente - Mostrar mensaje y bloquear campos
                                Swal.close();

                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Paciente ya registrado',
                                    text: 'Este documento ya está registrado como paciente en el sistema.',
                                    showCancelButton: true,
                                    confirmButtonText: 'Ver detalles',
                                    cancelButtonText: 'Intentar otro documento'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // TODO: Implementar redireccionamiento a detalles del paciente
                                        Swal.fire({
                                            icon: 'info',
                                            title: 'Redirección',
                                            text: 'Funcionalidad de ver detalles aún no implementada.'
                                        });
                                    } else {
                                        // Limpiar el campo de documento para intentar con otro
                                        document.getElementById('nrodoc').value = '';
                                        document.getElementById('nrodoc').focus();
                                    }
                                });

                                // Mantener los campos bloqueados
                                bloquearCampos();
                                document.getElementById("btnAgregarAlergia").disabled = true;
                                document.getElementById("btnAgregarAlergia").classList.add("disabled");
                                document.getElementById("btnSiguiente").disabled = true;
                                document.getElementById("btnSiguiente").classList.add("disabled");

                                // Mostrar mensaje en toast
                                showErrorToast('Este documento ya está registrado como paciente');
                            } else {
                                // No existe como paciente, pero podría estar registrado como persona
                                // *** PASO 2: Buscar datos de persona si existe ***
                                fetch(`../../../controllers/persona.controller.php?op=buscar_por_documento&nrodoc=${nrodoc}`)
                                    .then(response => response.json())
                                    .then(personaData => {
                                        Swal.close();
                                        console.log("Respuesta búsqueda persona:", personaData);

                                        if (personaData.status && personaData.persona) {
                                            // Existe como persona pero no como paciente - Mostrar sus datos en los campos
                                            Swal.fire({
                                                icon: 'info',
                                                title: 'Persona encontrada',
                                                text: 'Esta persona ya está registrada en el sistema. Se cargarán sus datos personales.',
                                                confirmButtonText: 'Continuar'
                                            });

                                            // Limpiar los campos antes de cargar los datos de la persona
                                            limpiarCampos();

                                            // Cargar datos de la persona en el formulario (usando readonly en lugar de disabled)
                                            cargarDatosPersona(personaData.persona, true);

                                            // Habilitar únicamente botón siguiente
                                            document.getElementById("btnSiguiente").disabled = false;
                                            document.getElementById("btnSiguiente").classList.remove("disabled");

                                            // Habilitar botón de agregar alergia
                                            document.getElementById("btnAgregarAlergia").disabled = false;
                                            document.getElementById("btnAgregarAlergia").classList.remove("disabled");

                                            showSuccessToast('Datos personales cargados. Puede continuar con el registro.');
                                        } else {
                                            // No existe en el sistema - Permitir el registro completo
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Documento disponible',
                                                text: 'Este documento no está registrado en el sistema. Puede continuar con el registro completo.',
                                                confirmButtonText: 'Continuar'
                                            });

                                            // IMPORTANTE: Primero limpiar todos los campos
                                            limpiarCampos();

                                            // Desbloquear los campos para el registro
                                            desbloquearCampos();

                                            // Habilitar botón de siguiente y agregar alergia
                                            document.getElementById("btnSiguiente").disabled = false;
                                            document.getElementById("btnSiguiente").classList.remove("disabled");
                                            document.getElementById("btnAgregarAlergia").disabled = false;
                                            document.getElementById("btnAgregarAlergia").classList.remove("disabled");

                                            showSuccessToast('Documento disponible, puede continuar con el registro');
                                        }
                                    })
                                    .catch(error => {
                                        Swal.close();
                                        console.error('Error al buscar persona:', error);

                                        // En caso de error en la búsqueda, permitir el registro normal
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Error en la búsqueda',
                                            text: 'No se pudo verificar si la persona existe. Puede continuar con el registro.',
                                            confirmButtonText: 'Continuar'
                                        });

                                        // IMPORTANTE: Primero limpiar todos los campos
                                        limpiarCampos();

                                        // Desbloquear los campos para el registro
                                        desbloquearCampos();

                                        // Habilitar botón de siguiente y agregar alergia
                                        document.getElementById("btnSiguiente").disabled = false;
                                        document.getElementById("btnSiguiente").classList.remove("disabled");
                                        document.getElementById("btnAgregarAlergia").disabled = false;
                                        document.getElementById("btnAgregarAlergia").classList.remove("disabled");
                                    });
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            console.error('Error al verificar paciente:', error);

                            Swal.fire({
                                icon: 'error',
                                title: 'Error de conexión',
                                text: 'No se pudo verificar el documento. Intente nuevamente.'
                            });

                            // Mantener los campos bloqueados en caso de error
                            bloquearCampos();
                            document.getElementById("btnAgregarAlergia").disabled = true;
                            document.getElementById("btnAgregarAlergia").classList.add("disabled");
                            document.getElementById("btnSiguiente").disabled = true;
                            document.getElementById("btnSiguiente").classList.add("disabled");
                        });
                }, 1000);
            }

            // Función para cargar los datos de la persona en el formulario
            function cargarDatosPersona(persona, mantenerBloqueados = false) {
                // Cargar datos básicos en los campos correspondientes
                document.getElementById('apellidos').value = persona.apellidos || '';
                document.getElementById('nombres').value = persona.nombres || '';
                document.getElementById('fechanacimiento').value = persona.fechanacimiento || '';
                document.getElementById('genero').value = persona.genero || 'M';
                document.getElementById('telefono').value = persona.telefono || '';
                document.getElementById('email').value = persona.email || '';
                document.getElementById('direccion').value = persona.direccion || '';

                // Marcar todos los campos como válidos
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

                console.log("Datos de persona cargados en el formulario", mantenerBloqueados ? "(campos bloqueados)" : "(campos habilitados)");
            }

            // Función para bloquear todos los campos excepto tipo y número de documento
            function bloquearCampos() {
                const camposABloquear = [
                    'apellidos', 'nombres', 'fechanacimiento',
                    'genero', 'telefono', 'email', 'direccion'
                ];

                camposABloquear.forEach(campo => {
                    const input = document.getElementById(campo);
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
                    if (input) {
                        input.disabled = false;
                        // Asegurarnos de que el valor pueda ser enviado en el formulario
                        if (input.hasAttribute('readonly')) {
                            input.removeAttribute('readonly');
                        }
                    }
                });

                // Habilitar botones
                document.getElementById("btnAgregarAlergia").disabled = false;
                document.getElementById("btnAgregarAlergia").classList.remove("disabled");
                document.getElementById("btnSiguiente").disabled = false;
                document.getElementById("btnSiguiente").classList.remove("disabled");
            }

            // Función para guardar paciente
            function guardarPaciente() {
                // Revalidar información personal
                if (!validatePersonalInfo()) {
                    showTab('personalInfo');
                    updateProgressBar(50);
                    return;
                }

                // Mostrar loader
                Swal.fire({
                    title: 'Guardando...',
                    html: 'Por favor espere mientras se registra el paciente.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Preparar datos del formulario
                const formData = new FormData();

                // Agregar manualmente todos los campos obligatorios
                formData.append('operacion', 'registrar');
                formData.append('apellidos', document.getElementById('apellidos').value);
                formData.append('nombres', document.getElementById('nombres').value);
                formData.append('tipodoc', document.getElementById('tipodoc').value);
                formData.append('nrodoc', document.getElementById('nrodoc').value);
                formData.append('telefono', document.getElementById('telefono').value);
                formData.append('fechanacimiento', document.getElementById('fechanacimiento').value);
                formData.append('genero', document.getElementById('genero').value);
                formData.append('direccion', document.getElementById('direccion').value || '');
                formData.append('email', document.getElementById('email').value || '');

                // Log para depuración
                console.log("Datos a enviar:", {
                    apellidos: document.getElementById('apellidos').value,
                    nombres: document.getElementById('nombres').value,
                    tipodoc: document.getElementById('tipodoc').value,
                    nrodoc: document.getElementById('nrodoc').value,
                    telefono: document.getElementById('telefono').value,
                    fechanacimiento: document.getElementById('fechanacimiento').value,
                    genero: document.getElementById('genero').value
                });

                // Enviar datos del paciente
                fetch('../../../controllers/paciente.controller.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Respuesta del registro de paciente:", data);

                        if (data.status) {
                            // Ejecutar una consulta adicional para obtener el idpersona
                            const nrodoc = document.getElementById('nrodoc').value;
                            console.log("Buscando idpersona para el documento:", nrodoc);

                            fetch(`../../../controllers/paciente.controller.php?operacion=buscar_por_documento&nrodoc=${nrodoc}`)
                                .then(response => response.json())
                                .then(dataPaciente => {
                                    console.log("Datos del paciente obtenidos:", dataPaciente);

                                    if (dataPaciente.status && dataPaciente.paciente && dataPaciente.paciente.idpersona) {
                                        const idpersona = dataPaciente.paciente.idpersona;
                                        console.log("ID de persona obtenido:", idpersona);

                                        // Si hay alergias, registrarlas usando el idpersona
                                        if (alergias.length > 0) {
                                            registrarAlergias(idpersona);
                                        } else {
                                            // Si no hay alergias, mostrar éxito directamente
                                            mostrarExito();
                                        }
                                    } else {
                                        console.error("No se pudo obtener el idpersona del paciente");
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Registro parcial',
                                            text: 'El paciente se registró pero no se pudieron registrar las alergias.'
                                        }).then(() => {
                                            resetFormAndRedirect();
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error("Error al obtener el idpersona:", error);
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Registro parcial',
                                        text: 'El paciente se registró pero no se pudieron registrar las alergias.'
                                    }).then(() => {
                                        resetFormAndRedirect();
                                    });
                                });
                        } else {
                            Swal.close();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al registrar',
                                text: data.mensaje || 'No se pudo registrar el paciente.'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error al registrar paciente:', error);
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo completar el registro. Intente nuevamente.'
                        });
                    });
            }
            // Función para registrar alergias
            function registrarAlergias(idpaciente) {
                let pendingAlergias = alergias.length;
                let errors = 0;

                // Registrar cada alergia
                alergias.forEach(alergia => {
                    // Primero registramos la alergia
                    const formDataAlergia = new FormData();
                    formDataAlergia.append('operacion', 'registrar');
                    formDataAlergia.append('tipoalergia', alergia.tipoalergia);
                    formDataAlergia.append('alergia', alergia.alergia);

                    // Mostrar en consola para depuración
                    console.log("Registrando alergia:", alergia.tipoalergia, alergia.alergia);

                    fetch('../../../controllers/alergia.controller.php', {
                            method: 'POST',
                            body: formDataAlergia
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log("Respuesta al registrar alergia:", data);
                            if (data.status && data.idalergia) {
                                // Una vez registrada la alergia, asociamos al paciente
                                const formDataPacienteAlergia = new FormData();
                                formDataPacienteAlergia.append('operacion', 'registrar_paciente');
                                formDataPacienteAlergia.append('idpersona', idpaciente);
                                formDataPacienteAlergia.append('idalergia', data.idalergia);
                                formDataPacienteAlergia.append('gravedad', alergia.gravedad.toUpperCase());

                                console.log("Asociando alergia al paciente:", idpaciente, data.idalergia, alergia.gravedad.toUpperCase());

                                return fetch('../../../controllers/alergia.controller.php', {
                                    method: 'POST',
                                    body: formDataPacienteAlergia
                                });
                            } else {
                                errors++;
                                console.error("Error al registrar alergia:", data.mensaje || "Error desconocido");
                                throw new Error("No se pudo registrar la alergia");
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log("Respuesta al asociar alergia al paciente:", data);
                            pendingAlergias--;

                            if (!data.status) {
                                errors++;
                                console.error("Error al asociar alergia al paciente:", data.mensaje || "Error desconocido");
                            }

                            // Cuando se han procesado todas las alergias
                            if (pendingAlergias === 0) {
                                if (errors > 0) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Registro parcial',
                                        text: `El paciente se registró correctamente, pero hubo problemas al registrar ${errors} alergia(s).`
                                    }).then(() => {
                                        resetFormAndRedirect();
                                    });
                                } else {
                                    mostrarExito();
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error completo:', error);
                            pendingAlergias--;
                            errors++;

                            // Cuando se han procesado todas las alergias
                            if (pendingAlergias === 0) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Registro parcial',
                                    text: `El paciente se registró correctamente, pero hubo problemas al registrar ${errors} alergia(s).`
                                }).then(() => {
                                    resetFormAndRedirect();
                                });
                            }
                        });
                });
            }

            // Función para mostrar mensaje de éxito
            function mostrarExito() {
                Swal.fire({
                    icon: 'success',
                    title: '¡Registro exitoso!',
                    text: 'El paciente ha sido registrado correctamente.',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    resetFormAndRedirect();
                });
            }

            // Función para resetear formulario y redirigir
            function resetFormAndRedirect() {
                document.getElementById('patientRegistrationForm').reset();
                alergias = [];
                updateAlergiasDisplay();

                // Restablecer valores por defecto
                document.getElementById("tipodoc").value = "DNI";
                document.getElementById("genero").value = "M";

                // Importante: Restablecer maxlength para DNI
                document.getElementById("nrodoc").setAttribute("maxlength", "8");

                // Bloquear campos y botones
                bloquearCampos();
                document.getElementById("btnAgregarAlergia").disabled = true;
                document.getElementById("btnAgregarAlergia").classList.add("disabled");
                document.getElementById("btnSiguiente").disabled = true;
                document.getElementById("btnSiguiente").classList.add("disabled");

                // Redirigir a la lista de pacientes
                window.location.href = '../ListarPaciente/listarPaciente.php';
            }

            // Función para formatear fecha
            function formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            }

            // Agregar CSS para elementos deshabilitados
            const style = document.createElement('style');
            style.textContent = `
        .disabled {
            opacity: 0.65;
            pointer-events: none;
        }
        
        input:disabled, select:disabled {
            background-color: #e9ecef !important;
            cursor: not-allowed;
        }

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

        body {
            font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", Helvetica, Arial, sans-serif; 
        }
    `;
            document.head.appendChild(style);
        });
    </script>

</body>

</html>