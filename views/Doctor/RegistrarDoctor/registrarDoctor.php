<?php /*RUTA: sistemasclinica/views/Doctor/RegistrarDoctor/registrarDoctor.php*/ ?>
<?php require_once '../../include/header.administrador.php'; ?>

<head>
    <!-- Enlace al archivo CSS -->
    <link rel="stylesheet" href="../../../css/registrarDoctor.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2 class="text-center mb-3">Registro de Doctor</h2>
                <div id="doctorNameDisplay" class="text-center mb-3 d-none">
                    <span class="badge bg-primary p-3 fs-5">
                        <i class="fas fa-user-md me-2"></i> <span id="doctorNameText" style="font-size: 18px; font-weight: bold;">Nombre del doctor</span>
                    </span>
                </div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>

        <form id="doctorRegistrationForm" method="POST" action="procesarRegistroDoctor.php" enctype="multipart/form-data">
            <!-- Información Personal -->
            <div class="card" id="personalInfoCard">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-user-md me-2"></i> Información del Doctor
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2 mb-3">Datos Personales</h5>
                        </div>
                    </div>

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

                    <!-- Género, Teléfono, Email -->
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
                            <label for="email" class="form-label required-field">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="direccion" class="form-label required-field">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" required>
                        </div>
                    </div>

                    <!-- Datos Profesionales -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2 mb-3">Datos Profesionales</h5>
                        </div>
                    </div>

                    <!-- Especialidad (ahora ocupando todo el ancho) -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="especialidad" class="form-label required-field">Especialidad</label>
                            <div class="input-group">
                                <select class="form-select" id="especialidad" name="especialidad" required>
                                    <option value="">Seleccione...</option>
                                    <!-- Aquí se cargan dinámicamente las especialidades -->
                                </select>
                                <button type="button" class="btn btn-primary" id="btnAgregarEspecialidad" disabled>
                                    <i class="fas fa-plus"></i> Agregar Especialidad
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Añadir justo después del selector de especialidad -->
                    <input type="hidden" id="precioatencion" name="precioatencion" value="0">

                    <!-- Botones de navegación -->
                    <div class="row mb-0">
                        <div class="col-md-12">
                            <div class="action-buttons text-end">
                                <button type="button" id="btnSiguiente" class="btn btn-primary" onclick="showTab('contrato')">Siguiente <i class="fas fa-arrow-right ms-1"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Esta sección reemplazaría las tres tarjetas separadas (contratoCard, horarioCard y credencialesCard) -->
            <div class="card d-none" id="informacionComplementariaCard">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-file-contract me-2"></i> Información Complementaria del Doctor
                    </div>
                </div>
                <div class="card-body">
                    <!-- Sección de Contrato -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2 mb-3">Información de Contrato</h5>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="tipocontrato" class="form-label required-field">Tipo de Contrato</label>
                            <select class="form-select" id="tipocontrato" name="tipocontrato" required>
                                <option value="">Seleccione...</option>
                                <option value="INDEFINIDO">Indefinido</option>
                                <option value="PLAZO FIJO">Plazo Fijo</option>
                                <option value="TEMPORAL">Temporal</option>
                                <option value="EVENTUAL">Eventual</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="fechainicio" class="form-label required-field">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="fechainicio" name="fechainicio" required>
                        </div>
                        <div class="col-md-4">
                            <label for="fechafin" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="fechafin" name="fechafin">
                            <small class="text-muted">Opcional para contratos indefinidos</small>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-12 text-center">
                            <button type="button" class="btn btn-success mb-3" id="btnConfirmarContrato">
                                <i class="fas fa-check me-1"></i> Confirmar Contrato y Activar Horarios
                            </button>
                        </div>
                    </div>

                    <!-- Reemplazar la sección de horarios actual con este nuevo componente -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2 mb-3">Horario de Atención</h5>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i> Configuración de Horarios</h5>
                                <p>Establezca los días y horarios en que el doctor atenderá en la clínica.</p>
                                <p>Los horarios se muestran en una tabla semanal para una mejor visualización.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario para agregar horarios -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="horarioDia" class="form-label">Día de atención</label>
                            <select class="form-select" id="horarioDia" name="horarioDia">
                                <option value="">Seleccione día...</option>
                                <option value="LUNES">Lunes</option>
                                <option value="MARTES">Martes</option>
                                <option value="MIERCOLES">Miércoles</option>
                                <option value="JUEVES">Jueves</option>
                                <option value="VIERNES">Viernes</option>
                                <option value="SABADO">Sábado</option>
                                <option value="DOMINGO">Domingo</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="horarioInicio" class="form-label">Hora Inicio:</label>
                            <input type="time" class="form-control" id="horarioInicio" name="horarioInicio">
                        </div>
                        <div class="col-md-3">
                            <label for="horarioFin" class="form-label">Hora Fin:</label>
                            <input type="time" class="form-control" id="horarioFin" name="horarioFin">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label d-block">&nbsp;</label>
                            <button type="button" class="btn btn-primary w-100" id="btnAgregarHorario">
                                <i class="fas fa-plus-circle me-1"></i> Agregar Horario
                            </button>
                        </div>
                    </div>

                    <!-- Lista de horarios agregados -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light text-dark">
                                    <h6 class="mb-0"><i class="fas fa-list me-2"></i> Horarios Registrados</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group" id="listaHorariosAgregados">
                                        <!-- Aquí se mostrarán los horarios agregados dinámicamente -->
                                        <li class="list-group-item text-muted text-center" id="mensajeNoHorarios">
                                            No hay horarios registrados aún
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de visualización semanal de horarios -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light text-dark">
                                    <h6 class="mb-0"><i class="fas fa-list me-2"></i> Vista Semanal de Horarios</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="tablaHorarioSemanal">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="12%">Lunes</th>
                                                    <th width="12%">Martes</th>
                                                    <th width="12%">Miércoles</th>
                                                    <th width="12%">Jueves</th>
                                                    <th width="12%">Viernes</th>
                                                    <th width="12%">Sábado</th>
                                                    <th width="12%">Domingo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td id="LUNES-AM"></td>
                                                    <td id="MARTES-AM"></td>
                                                    <td id="MIERCOLES-AM"></td>
                                                    <td id="JUEVES-AM"></td>
                                                    <td id="VIERNES-AM"></td>
                                                    <td id="SABADO-AM"></td>
                                                    <td id="DOMINGO-AM"></td>
                                                </tr>
                                                <tr>
                                                    <td id="LUNES-PM"></td>
                                                    <td id="MARTES-PM"></td>
                                                    <td id="MIERCOLES-PM"></td>
                                                    <td id="JUEVES-PM"></td>
                                                    <td id="VIERNES-PM"></td>
                                                    <td id="SABADO-PM"></td>
                                                    <td id="DOMINGO-PM"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campo oculto para almacenar los horarios en formato JSON -->
                    <input type="hidden" id="horariosAgregados" name="horariosAgregados" value="[]">

                    <div class="row mb-0">
                        <div class="col-md-12">
                            <div class="action-buttons">
                                <button type="button" class="btn btn-secondary" onclick="showTab('personalInfo')"><i class="fas fa-arrow-left me-1"></i> Anterior</button>
                                <button type="button" class="btn btn-primary" onclick="showTab('confirmacion')">Siguiente <i class="fas fa-arrow-right ms-1"></i></button>
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

                    <div class="row mb-0">
                        <div class="col-md-12">
                            <div id="resumenRegistro" class="mb-4">
                                <!-- Aquí se mostrará el resumen de los datos ingresados -->
                            </div>
                            <div class="mt-3 text-end mb-3">
                                <button type="button" id="btnDiagnostico" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-stethoscope"></i> Diagnóstico rápido
                                </button>
                            </div>
                            <div class="action-buttons">
                                <button type="button" class="btn btn-secondary" onclick="showTab('credenciales')"><i class="fas fa-arrow-left me-1"></i> Anterior</button>
                                <button type="button" class="btn btn-danger me-2" onclick="resetForm()"><i class="fas fa-times me-1"></i> Cancelar</button>
                                <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- Modal para credenciales de acceso -->
    <div class="modal fade" id="modalCredenciales" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalCredencialesLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalCredencialesLabel"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                    </div>
                    <div class="mb-3">
                        <label for="nomuser" class="form-label required-field"></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="nomuser" name="nomuser" required>
                        </div>
                        <div class="invalid-feedback" id="help-nomuser"></div>
                    </div>
                    <div class="mb-3">
                        <label for="passuser" class="form-label required-field"></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="passuser" name="passuser" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="help-passuser"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></button>
                    <button type="button" class="btn btn-primary" id="btnGuardarCredenciales"></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar especialidad -->
    <div class="modal fade" id="modalEspecialidad" tabindex="-1" aria-labelledby="modalEspecialidadLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEspecialidadLabel">Agregar Nueva Especialidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEspecialidad">
                        <div class="mb-3">
                            <label for="nombreEspecialidad" class="form-label">Nombre de Especialidad</label>
                            <input type="text" class="form-control" id="nombreEspecialidad" required>
                        </div>
                        <div class="mb-3">
                            <label for="precioEspecialidad" class="form-label">Precio de Atención</label>
                            <div class="input-group">
                                <span class="input-group-text">S/.</span>
                                <input type="number" class="form-control" id="precioEspecialidad" step="0.01" min="0" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarEspecialidad">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Variables globales
            let currentTab = 'personalInfo';
            let horariosAgregados = [];

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
            // Actualizar etiquetas en el modal de credenciales
            const modalTitle = document.getElementById('modalCredencialesLabel');
            if (modalTitle) modalTitle.textContent = 'Credenciales de Acceso';

            const modalLabel = document.querySelector('#modalCredenciales label[for="nomuser"]');
            if (modalLabel) modalLabel.textContent = 'Correo Electrónico';

            const modalIcon = document.querySelector('#modalCredenciales .input-group-text i');
            if (modalIcon) {
                modalIcon.classList.remove('fa-user');
                modalIcon.classList.add('fa-envelope');
            }

            // Cambiar el tipo de input para validación de email
            const inputNomUser = document.getElementById('nomuser');
            if (inputNomUser) inputNomUser.type = 'email';

            // Deshabilitar inicialmente los controles de horario
            deshabilitarControlesHorario();
            // Los horarios solo se pueden agregar después de confirmar el contrato
            // Busca este bloque de código (alrededor de la línea 1170-1190)
            const btnAgregarHorario = document.getElementById('btnAgregarHorario');
            if (btnAgregarHorario) {
                // Asegurarnos que sea explícitamente de tipo button
                btnAgregarHorario.setAttribute('type', 'button');

                // Eliminar TODOS los listeners existentes (método más agresivo)
                btnAgregarHorario.outerHTML = btnAgregarHorario.outerHTML;

                // Obtener la nueva referencia después de reemplazar
                const newBtnAgregarHorario = document.getElementById('btnAgregarHorario');

                // Agregar el nuevo event listener con captura explícita del evento
                newBtnAgregarHorario.addEventListener('click', function(event) {
                    // Detener cualquier propagación y comportamiento por defecto
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();

                    // SOLO llamar a la función para agregar horario
                    agregarNuevoHorario();

                    // No permitir que el evento siga propagándose
                    return false;
                });

                console.log("Event listener para agregar horario reconfigurado correctamente");
            }
            const horarioDia = document.getElementById('horarioDia');
            const horarioInicio = document.getElementById('horarioInicio');
            if (horarioInicio) {
                // Remover listeners anteriores (por si acaso)
                const nuevoHorarioInicio = horarioInicio.cloneNode(true);
                horarioInicio.parentNode.replaceChild(nuevoHorarioInicio, horarioInicio);

                // Agregar nuevo listener
                nuevoHorarioInicio.addEventListener('input', function() {
                    console.log("Evento input en hora inicio:", this.value);
                    // Solo validar si el campo tiene valor
                    if (this.value) {
                        markFieldAsValid(this);
                        removeFieldHelpMessage(this);

                        // Si también hay valor en hora fin, validar la relación
                        const horarioFin = document.getElementById('horarioFin');
                        if (horarioFin && horarioFin.value) {
                            validarHoraFinPosterior();
                        }
                    } else {
                        // Si está vacío, quitar cualquier validación
                        this.classList.remove('is-valid', 'is-invalid');
                        removeFieldHelpMessage(this);
                    }
                });

                // También agregar listener para el evento change
                nuevoHorarioInicio.addEventListener('change', function() {
                    console.log("Evento change en hora inicio:", this.value);
                    if (this.value) {
                        markFieldAsValid(this);
                        removeFieldHelpMessage(this);

                        // Validar relación si hay hora fin
                        const horarioFin = document.getElementById('horarioFin');
                        if (horarioFin && horarioFin.value) {
                            validarHoraFinPosterior();
                        }
                    } else {
                        this.classList.remove('is-valid', 'is-invalid');
                        removeFieldHelpMessage(this);
                    }
                });
            }
            // Para la hora de fin
            const horarioFin = document.getElementById('horarioFin');
            if (horarioFin) {
                // Remover listeners anteriores (por si acaso)
                const nuevoHorarioFin = horarioFin.cloneNode(true);
                horarioFin.parentNode.replaceChild(nuevoHorarioFin, horarioFin);

                // Agregar nuevo listener
                nuevoHorarioFin.addEventListener('input', function() {
                    console.log("Evento input en hora fin:", this.value);
                    // Solo validar si el campo tiene valor
                    if (this.value) {
                        markFieldAsValid(this);
                        removeFieldHelpMessage(this);

                        // Si también hay valor en hora inicio, validar la relación
                        const horarioInicio = document.getElementById('horarioInicio');
                        if (horarioInicio && horarioInicio.value) {
                            validarHoraFinPosterior();
                        }
                    } else {
                        // Si está vacío, quitar cualquier validación
                        this.classList.remove('is-valid', 'is-invalid');
                        removeFieldHelpMessage(this);
                    }
                });

                // También agregar listener para el evento change
                nuevoHorarioFin.addEventListener('change', function() {
                    console.log("Evento change en hora fin:", this.value);
                    if (this.value) {
                        markFieldAsValid(this);
                        removeFieldHelpMessage(this);

                        // Validar relación si hay hora inicio
                        const horarioInicio = document.getElementById('horarioInicio');
                        if (horarioInicio && horarioInicio.value) {
                            validarHoraFinPosterior();
                        }
                    } else {
                        this.classList.remove('is-valid', 'is-invalid');
                        removeFieldHelpMessage(this);
                    }
                });
            }

            console.log("Eventos de validación configurados correctamente");
            // Deshabilitar inicialmente
            btnAgregarHorario.disabled = true;
            horarioDia.disabled = true;
            horarioInicio.disabled = true;
            horarioFin.disabled = true;
            // Función para actualizar la lista visual de horarios
            function actualizarListaHorarios() {
                const lista = document.getElementById('listaHorariosAgregados');
                const mensajeNoHorarios = document.getElementById('mensajeNoHorarios');

                // Mostrar u ocultar el mensaje de "No hay horarios"
                if (horariosAgregados.length === 0) {
                    mensajeNoHorarios.style.display = 'list-item';
                } else {
                    mensajeNoHorarios.style.display = 'none';
                }

                // Limpiar la lista actual (excepto el mensaje de "No hay horarios")
                const itemsExistentes = lista.querySelectorAll('li:not(#mensajeNoHorarios)');
                itemsExistentes.forEach(item => item.remove());

                // Ordenar horarios por día y hora de inicio
                const horariosOrdenados = [...horariosAgregados].sort((a, b) => {
                    // Mapeo para ordenar los días
                    const ordenDias = {
                        'LUNES': 1,
                        'MARTES': 2,
                        'MIERCOLES': 3,
                        'JUEVES': 4,
                        'VIERNES': 5,
                        'SABADO': 6,
                        'DOMINGO': 7
                    };

                    // Primero ordenar por día
                    if (ordenDias[a.dia] !== ordenDias[b.dia]) {
                        return ordenDias[a.dia] - ordenDias[b.dia];
                    }

                    // Si son el mismo día, ordenar por hora de inicio
                    return a.horaInicio.localeCompare(b.horaInicio);
                });

                // Agregar cada horario a la lista
                horariosOrdenados.forEach(horario => {
                    const item = document.createElement('li');
                    item.className = 'list-group-item d-flex justify-content-between align-items-center';
                    item.innerHTML = `
            <div>
                <span class="badge bg-primary me-2">${horario.diaNombre}</span>
                <span>${formatTime(horario.horaInicio)} - ${formatTime(horario.horaFin)}</span>
            </div>
            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarHorario(${horario.id})">
                <i class="fas fa-trash-alt"></i>
            </button>
        `;
                    lista.appendChild(item);
                });
            }

            // Función para eliminar un horario de la lista
            window.eliminarHorario = function(id) {
                const horarioIndex = horariosAgregados.findIndex(h => h.id === id);

                if (horarioIndex !== -1) {
                    const horarioBorrado = horariosAgregados[horarioIndex];

                    Swal.fire({
                        title: '¿Eliminar horario?',
                        text: `¿Está seguro de eliminar el horario de ${horarioBorrado.diaNombre} (${formatTime(horarioBorrado.horaInicio)} - ${formatTime(horarioBorrado.horaFin)})?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Eliminar el horario del array
                            horariosAgregados.splice(horarioIndex, 1);

                            // Actualizar el campo oculto
                            document.getElementById('horariosAgregados').value = JSON.stringify(horariosAgregados);

                            // Actualizar la interfaz
                            actualizarListaHorarios();
                            actualizarTablaHorarios();

                            // Mostrar notificación
                            showSuccessToast('Horario eliminado correctamente');
                        }
                    });
                }
            };

            // Función para actualizar la tabla semanal de horarios
            function actualizarTablaHorarios() {
                // Limpiar todas las celdas primero
                const diasSemana = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'];
                const periodos = ['AM', 'PM'];

                diasSemana.forEach(dia => {
                    periodos.forEach(periodo => {
                        const celda = document.getElementById(`${dia}-${periodo}`);
                        if (celda) {
                            celda.innerHTML = '';
                            celda.className = ''; // Limpiar clases adicionales
                        }
                    });
                });

                // Agregar los horarios a la tabla
                horariosAgregados.forEach(horario => {
                    // Convertir el nombre del día al formato de la tabla
                    const diaTabla = horario.dia.toUpperCase();

                    // Determinar si es AM o PM
                    const horaInicio = parseInt(horario.horaInicio.split(':')[0], 10);
                    const periodo = horaInicio < 12 ? 'AM' : 'PM';

                    // Obtener la celda correspondiente
                    const celda = document.getElementById(`${diaTabla}-${periodo}`);

                    if (celda) {
                        // Agregar este horario a la celda
                        const horarioHtml = document.createElement('div');
                        horarioHtml.className = 'horario-item mb-1 p-1 bg-light rounded';
                        horarioHtml.innerHTML = `${formatTime(horario.horaInicio)} - ${formatTime(horario.horaFin)}`;

                        celda.appendChild(horarioHtml);
                        celda.classList.add('bg-light-success');
                    }
                });
            }

            // Función para validar los horarios al pasar a la siguiente pestaña
            function validateHorarioInfo() {
                // Verificar que se haya agregado al menos un horario
                if (horariosAgregados.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Horario requerido',
                        text: 'Debe agregar al menos un horario de atención'
                    });
                    return false;
                }

                return true;
            }

            // Formatear hora para visualización (12:00 -> 12:00 h)
            function formatTime(timeString) {
                if (!timeString) return '';
                return `${timeString} h`;
            }
            // Inicializar progreso
            updateProgressBar(0);

            // Establecer DNI como valor por defecto para tipo de documento
            document.getElementById("tipodoc").value = "DNI";

            // Configurar maxlength para documento según el tipo por defecto (DNI = 8)
            const nrodocInput = document.getElementById("nrodoc");
            nrodocInput.setAttribute("maxlength", "8");

            // Establecer Masculino como valor por defecto para género
            document.getElementById("genero").value = "M";

            // Establecer fecha máxima para fecha de nacimiento (hoy)
            const today = new Date();
            const formattedDate = today.toISOString().split('T')[0];
            document.getElementById("fechanacimiento").setAttribute("max", formattedDate);

            // Bloquear todos los campos excepto tipo y número de documento
            bloquearCampos();

            // Cargar especialidades desde el servidor
            cargarEspecialidades();

            // Setup eventos de validación para todos los campos
            setupFieldValidations();
            // Asociar evento al select de especialidad para cargar precio automáticamente
            const selectEspecialidad = document.getElementById('especialidad');
            if (selectEspecialidad) {
                selectEspecialidad.addEventListener('change', function() {
                    if (this.value) {
                        console.log("Cambio de especialidad detectado:", this.value);
                        cargarPrecioEspecialidad(this.value);
                    } else {
                        // Si no hay especialidad seleccionada, establecer precio en cero
                        const precioInput = document.getElementById('precioatencion');
                        if (precioInput) {
                            precioInput.value = '0';
                        }
                    }
                });
            }

            function verificarPrecioAtencion() {
                const precioInput = document.getElementById('precioatencion');
                if (!precioInput || !precioInput.value || parseFloat(precioInput.value) <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'El precio de atención debe ser mayor a cero'
                    });
                    return false;
                }
                return true;
            }

            // Asociar evento al botón de agregar especialidad
            const btnAgregarEspecialidad = document.getElementById('btnAgregarEspecialidad');
            if (btnAgregarEspecialidad) {
                btnAgregarEspecialidad.addEventListener('click', function() {
                    // Mostrar modal
                    const modalEspecialidad = new bootstrap.Modal(document.getElementById('modalEspecialidad'));
                    modalEspecialidad.show();
                });
            }

            // Asociar evento al botón de guardar especialidad en el modal
            const btnGuardarEspecialidad = document.getElementById('btnGuardarEspecialidad');
            if (btnGuardarEspecialidad) {
                btnGuardarEspecialidad.addEventListener('click', function() {
                    guardarNuevaEspecialidad();
                });
            }
            // Añadir al final del script
            document.getElementById('btnDiagnostico').addEventListener('click', function() {
                Swal.fire({
                    title: 'Ejecutando diagnóstico...',
                    html: 'Verificando conexión y estructura de datos...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Probar conexión a la base de datos
                fetch('../../../controllers/diagnostico.php')
                    .then(response => response.json())
                    .then(data => {
                        Swal.close();

                        if (data.status) {
                            // Mostrar resultado del diagnóstico
                            Swal.fire({
                                icon: 'info',
                                title: 'Diagnóstico completado',
                                html: `
                            <div class="text-start">
                                <p><b>Conexión a la base de datos:</b> ${data.conexion ? '✅ Correcta' : '❌ Error'}</p>
                                <p><b>Tablas verificadas:</b> ${data.tablas_ok ? '✅ Correctas' : '❌ Faltan tablas'}</p>
                                ${data.tablas_ok ? '' : `<p class="text-danger">Tablas faltantes: ${data.tablas_faltantes.join(', ')}</p>`}
                                <p><b>Sugerencia:</b> ${data.recomendacion}</p>
                            </div>
                        `,
                                confirmButtonText: 'Entendido'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error en diagnóstico',
                                text: data.mensaje || 'No se pudo completar el diagnóstico'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error en diagnóstico:', error);
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo realizar el diagnóstico: ' + error.message
                        });
                    });
            });
            // Función para cargar el precio de la especialidad seleccionada
            function cargarPrecioEspecialidad(idEspecialidad) {
                if (!idEspecialidad) {
                    console.warn("No se proporcionó ID de especialidad");
                    return;
                }

                console.log("Cargando precio para especialidad ID:", idEspecialidad);

                // Asegurar que existe el campo precioatencion
                let precioInput = document.getElementById('precioatencion');
                if (!precioInput) {
                    precioInput = document.createElement('input');
                    precioInput.type = 'hidden';
                    precioInput.id = 'precioatencion';
                    precioInput.name = 'precioatencion';
                    document.getElementById('doctorRegistrationForm').appendChild(precioInput);
                }

                // Buscar la especialidad en el listado y obtener su precio
                fetch(`../../../controllers/especialidad.controller.php?op=obtener&id=${idEspecialidad}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Error en la respuesta del servidor: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log("Datos de especialidad recibidos:", data);

                        if (data.status && data.data) {
                            const precio = data.data.precioatencion || '0';
                            // Asignar el precio al campo
                            precioInput.value = precio;
                            console.log("Precio cargado automáticamente:", precio);

                            if (parseFloat(precio) <= 0) {
                                console.warn("El precio cargado no es mayor que cero:", precio);
                                showErrorToast("Advertencia: El precio de atención debe ser mayor a cero");
                            }
                        } else {
                            // Si hay error, asignar un valor por defecto
                            precioInput.value = '0';
                            console.error("No se pudo obtener el precio: ", data.mensaje || "Error desconocido");
                            showErrorToast("No se pudo obtener el precio de la especialidad");
                        }
                    })
                    .catch(error => {
                        console.error('Error al obtener precio de especialidad:', error);
                        // En caso de error, asignar un valor por defecto
                        precioInput.value = '0';
                        showErrorToast("Error al cargar el precio de la especialidad: " + error.message);
                    });
            }
            // Función para guardar una nueva especialidad
            function guardarNuevaEspecialidad() {
                const nombreEspecialidad = document.getElementById('nombreEspecialidad').value;
                const precioEspecialidad = document.getElementById('precioEspecialidad').value;

                if (!nombreEspecialidad || !precioEspecialidad) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Campos incompletos',
                        text: 'Por favor, complete todos los campos requeridos.'
                    });
                    return;
                }

                const formData = new FormData();
                formData.append('especialidad', nombreEspecialidad);
                formData.append('precioatencion', precioEspecialidad);

                // Mostrar loader
                Swal.fire({
                    title: 'Guardando...',
                    text: 'Por favor espere mientras se registra la especialidad.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('../../../controllers/especialidad.controller.php?op=registrar', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.close();

                        if (data.status) {
                            // Cerrar modal
                            bootstrap.Modal.getInstance(document.getElementById('modalEspecialidad')).hide();

                            // Limpiar formulario
                            document.getElementById('nombreEspecialidad').value = '';
                            document.getElementById('precioEspecialidad').value = '';

                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: 'Especialidad registrada',
                                text: 'La especialidad ha sido registrada correctamente.'
                            });

                            // Recargar lista de especialidades
                            cargarEspecialidades(data.idespecialidad);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al registrar',
                                text: data.mensaje || 'No se pudo registrar la especialidad.'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error al registrar especialidad:', error);
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo completar el registro de la especialidad.'
                        });
                    });
            }

            // Setup eventos para mostrar/ocultar contraseña
            document.getElementById("togglePassword").addEventListener("click", function() {
                const passwordInput = document.getElementById("passuser");
                const icon = this.querySelector("i");

                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    icon.classList.remove("fa-eye");
                    icon.classList.add("fa-eye-slash");
                } else {
                    passwordInput.type = "password";
                    icon.classList.remove("fa-eye-slash");
                    icon.classList.add("fa-eye");
                }
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

                // Limpiar y desbloquear el campo de número de documento
                nrodocInput.value = "";
                nrodocInput.disabled = false;
                nrodocInput.classList.remove('is-valid', 'is-invalid');
                removeFieldHelpMessage(nrodocInput);

                // CAMBIO: Limpiar todos los campos de datos personales y profesionales
                limpiarCamposCompletos();

                // Volver a bloquear otros campos y botones
                bloquearCampos();
            });

            function limpiarCamposCompletos() {
                // Campos personales a limpiar
                const camposPersonales = [
                    'apellidos', 'nombres', 'fechanacimiento', 'genero',
                    'telefono', 'email', 'direccion'
                ];

                // Campos profesionales a limpiar
                const camposProfesionales = [
                    'especialidad', 'precioatencion'
                ];

                // Limpiar campos personales
                camposPersonales.forEach(campo => {
                    const input = document.getElementById(campo);
                    if (input) {
                        input.value = '';
                        input.classList.remove('is-valid', 'is-invalid', 'border-danger');
                        removeFieldHelpMessage(input);
                    }
                });

                // Limpiar campos profesionales
                camposProfesionales.forEach(campo => {
                    const input = document.getElementById(campo);
                    if (input) {
                        if (campo === 'especialidad') {
                            input.selectedIndex = 0; // Seleccionar la primera opción
                        } else {
                            input.value = '';
                        }
                        input.classList.remove('is-valid', 'is-invalid', 'border-danger');
                        removeFieldHelpMessage(input);
                    }
                });

                // Restablecer valores por defecto
                document.getElementById("genero").value = "M";

                // Ocultar el nombre del doctor si está visible
                document.getElementById('doctorNameDisplay').classList.add('d-none');

                console.log("Todos los campos han sido limpiados");
            }
            nrodocInput.addEventListener("input", function() {
                // Al cambiar el número de documento, volver a bloquear campos
                bloquearCampos();

                // Validar formato según tipo de documento
                const tipodoc = document.getElementById('tipodoc').value;
                if (tipodoc && documentoConfig[tipodoc]) {
                    if (this.value && documentoConfig[tipodoc].pattern.test(this.value)) {
                        markFieldAsValid(this);
                        removeFieldHelpMessage(this);
                    } else if (this.value) {
                        markFieldAsInvalid(this);
                        addFieldHelpMessage(this, documentoConfig[tipodoc].message);
                    }
                }
            });

            // Setup botón de búsqueda de documento
            const btnBuscar = document.getElementById("btnBuscarDocumento");
            if (btnBuscar) {
                btnBuscar.addEventListener("click", function(e) {
                    e.preventDefault();
                    buscarDoctorPorDocumento();
                });
            }

            // Eliminar atributos onclick existentes y agregar event listeners
            const btnSiguiente = document.getElementById("btnSiguiente");
            if (btnSiguiente) {
                btnSiguiente.removeAttribute("onclick");
                btnSiguiente.addEventListener("click", async function() {
                    // Forzar la validación de todos los campos
                    const requiredFields = [
                        'tipodoc', 'nrodoc', 'apellidos', 'nombres', 'fechanacimiento',
                        'genero', 'telefono', 'email', 'direccion', 'especialidad', 'precioatencion'
                    ];
                    requiredFields.forEach(field => {
                        const input = document.getElementById(field);
                        if (input) {
                            if (!input.value.trim()) {
                                markFieldAsInvalid(input);
                                addFieldHelpMessage(input, 'Este campo es obligatorio');
                            }
                        }
                    });

                    // Realizar validación completa asíncrona
                    const personalValid = await validatePersonalInfo();
                    const professionalValid = validateProfessionalInfo();

                    if (personalValid && professionalValid) {
                        // Actualizar el nombre del doctor antes de cambiar de pestaña
                        const apellidos = document.getElementById('apellidos').value;
                        const nombres = document.getElementById('nombres').value;
                        if (apellidos && nombres) {
                            document.getElementById('doctorNameText').textContent = `${nombres} ${apellidos}`;
                        }

                        // Ahora vamos directamente a la pestaña combinada
                        showTab('contrato'); // Usamos 'contrato' para identificar la pestaña combinada
                    } else {
                        // Si hay campos incompletos, mostrar alerta pero no cambiar de pestaña
                        Swal.fire({
                            icon: 'warning',
                            title: 'Campos incompletos',
                            text: 'Por favor, complete todos los campos marcados en rojo para continuar.'
                        });

                        // Hacer scroll al primer campo con error
                        const firstInvalidField = document.querySelector('.is-invalid');
                        if (firstInvalidField) {
                            firstInvalidField.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            firstInvalidField.focus();
                        }
                    }
                });
            }
            // Buscar el botón Siguiente en la pestaña combinada
            const btnSiguienteComplementaria = document.querySelector('#informacionComplementariaCard .btn-primary');
            if (btnSiguienteComplementaria) {
                btnSiguienteComplementaria.removeAttribute('onclick');
                btnSiguienteComplementaria.addEventListener('click', function() {
                    // Validar contrato y horarios
                    if (validateContratoInfo() && validateHorarioInfo()) {
                        // Solo este botón debe mostrar la alerta de credenciales
                        mostrarAlertaCredenciales();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Información incompleta',
                            text: 'Por favor, complete correctamente todos los campos de contrato y horarios para continuar.'
                        });

                        // Hacer scroll al primer campo con error
                        const firstInvalidField = document.querySelector('#informacionComplementariaCard .is-invalid');
                        if (firstInvalidField) {
                            firstInvalidField.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            firstInvalidField.focus();
                        }
                    }
                });
            }

            // Buscar y corregir todos los otros botones de siguiente y anterior
            const navigationButtons = document.querySelectorAll('button[onclick^="showTab"]');
            navigationButtons.forEach(button => {
                const onclickValue = button.getAttribute('onclick');
                if (onclickValue) {
                    const match = onclickValue.match(/showTab\(['"](.+?)['"]\)/);
                    if (match && match[1]) {
                        const targetTab = match[1];
                        button.removeAttribute('onclick');

                        // Determinar si es botón anterior o siguiente basado en su texto
                        if (button.innerHTML.includes('Anterior')) {
                            button.addEventListener("click", function() {
                                showTab(targetTab);
                            });
                        } else if (button.innerHTML.includes('Siguiente')) {
                            // Determinar qué validación usar según la pestaña actual
                            button.addEventListener("click", function() {
                                let isValid = true;

                                // Determinar la pestaña actual por el botón
                                if (button.closest('.card').id === 'informacionProfesionalCard') {
                                    isValid = validateProfessionalInfo();
                                } else if (button.closest('.card').id === 'contratoCard') {
                                    isValid = validateContratoInfo();
                                } else if (button.closest('.card').id === 'horarioCard') {
                                    isValid = validateHorarioInfo();
                                } else if (button.closest('.card').id === 'credencialesCard') {
                                    isValid = validateCredenciales();
                                }

                                if (isValid) {
                                    showTab(targetTab);
                                }
                            });
                        }
                    }
                }
            });

            // Setup form submit
            document.getElementById("doctorRegistrationForm").addEventListener("submit", async function(e) {
                e.preventDefault(); // Siempre prevenir el envío por defecto

                // Solo permitir envío cuando explícitamente se hace clic en el botón de guardar
                if (e.submitter && e.submitter.type === "submit") {
                    console.log("Iniciando proceso de guardado del doctor...");
                    await guardarDoctor();
                }
            });

            // Setup botón de cancelar
            const btnCancelar = document.querySelector('button[onclick="resetForm()"]');
            if (btnCancelar) {
                btnCancelar.removeAttribute('onclick');
                btnCancelar.addEventListener("click", function() {
                    resetForm();
                });
            }

            // Asociar evento al botón de guardar precio en el modal
            const btnGuardarPrecio = document.getElementById('btnGuardarPrecio');
            if (btnGuardarPrecio) {
                btnGuardarPrecio.addEventListener('click', function() {
                    const nuevoPrecio = document.getElementById('nuevoPrecioAtencion').value;

                    if (!nuevoPrecio || parseFloat(nuevoPrecio) <= 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Precio inválido',
                            text: 'El precio debe ser mayor a cero'
                        });
                        return;
                    }

                    // Mostrar loader mientras se actualiza
                    Swal.fire({
                        title: 'Actualizando...',
                        text: 'Por favor espere mientras se actualiza el precio.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Obtener ID de la especialidad seleccionada
                    const idEspecialidad = document.getElementById('especialidad').value;
                });
            }

            const originalShowTab = window.showTab;
            // Función para mostrar la pestaña correspondiente
            window.showTab = function(tab) {
                console.log("Cambiando a pestaña:", tab);

                // Si estamos pasando a la pestaña de confirmación
                if (tab === 'confirmacion') {
                    // Validar que la información de contrato y horarios esté correcta
                    if (!validateContratoInfo() || !validateHorarioInfo()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Información incompleta',
                            text: 'Por favor, complete correctamente todos los campos de contrato y horarios para continuar.'
                        });
                        return; // Si no es válida, no continuamos
                    }

                    // Si es válida, mostrar alerta Swal para credenciales de acceso
                    mostrarAlertaCredenciales();

                    // No cambiamos de pestaña aún - eso se hará después de guardar las credenciales
                    return;
                }

                // Para cualquier otra pestaña, comportamiento normal
                // Ocultar todas las tarjetas
                const allCards = [
                    "personalInfoCard",
                    "informacionComplementariaCard", // Nueva tarjeta combinada
                    "confirmacionCard"
                ];

                allCards.forEach(card => {
                    const element = document.getElementById(card);
                    if (element) {
                        element.classList.add("d-none");
                    }
                });

                // Determinar el ID correcto de la tarjeta
                let cardId;
                switch (tab) {
                    case 'personalInfo':
                        cardId = 'personalInfoCard';
                        break;
                    case 'informacionProfesional': // Este caso redirige a personalInfoCard
                        cardId = 'personalInfoCard';
                        break;
                    case 'contrato':
                    case 'horario':
                    case 'credenciales':
                        // Todos estos casos ahora van a la misma tarjeta combinada
                        cardId = 'informacionComplementariaCard';
                        break;
                    case 'confirmacion':
                        cardId = 'confirmacionCard';
                        break;
                    default:
                        cardId = tab + 'Card';
                }

                // Mostrar la tarjeta seleccionada
                const targetCard = document.getElementById(cardId);
                if (targetCard) {
                    targetCard.classList.remove("d-none");
                    currentTab = tab;

                    // Actualizar barra de progreso
                    updateProgressBarForTab(tab);

                    // Si es la primera pestaña, ocultar el nombre del doctor
                    if (tab === 'personalInfo') {
                        document.getElementById('doctorNameDisplay').classList.add('d-none');
                    }
                    // Para cualquier otra pestaña, mostrar el nombre del doctor si ya está ingresado
                    else {
                        const apellidos = document.getElementById('apellidos').value;
                        const nombres = document.getElementById('nombres').value;

                        if (apellidos && nombres) {
                            document.getElementById('doctorNameText').textContent = `${nombres} ${apellidos}`;
                            document.getElementById('doctorNameDisplay').classList.remove('d-none');
                        }
                    }

                    // Si es la pestaña de confirmación, cargar los datos de resumen
                    if (tab === 'confirmacion') {
                        loadConfirmationData();
                    }
                } else {
                    console.error("No se encontró la tarjeta:", cardId);
                }
            };
            const btnGuardarCredenciales = document.getElementById('btnGuardarCredenciales');
            if (btnGuardarCredenciales) {
                btnGuardarCredenciales.addEventListener('click', function() {
                    // Validar credenciales
                    if (validateCredenciales()) {
                        // Ocultar el modal
                        bootstrap.Modal.getInstance(document.getElementById('modalCredenciales')).hide();

                        // Mostrar la pestaña de confirmación
                        const allCards = [
                            "personalInfoCard",
                            "informacionComplementariaCard",
                            "confirmacionCard"
                        ];

                        allCards.forEach(card => {
                            const element = document.getElementById(card);
                            if (element) {
                                element.classList.add("d-none");
                            }
                        });

                        // Mostrar la tarjeta de confirmación
                        document.getElementById('confirmacionCard').classList.remove("d-none");
                        currentTab = 'confirmacion';

                        // Actualizar barra de progreso
                        updateProgressBar(100);

                        // Cargar los datos de resumen
                        loadConfirmationData();
                    }
                });
            }

            // Conservar la configuración del toggle de contraseña para el modal
            const togglePassword = document.getElementById("togglePassword");
            if (togglePassword) {
                togglePassword.addEventListener("click", function() {
                    const passwordInput = document.getElementById("passuser");
                    const icon = this.querySelector("i");

                    if (passwordInput.type === "password") {
                        passwordInput.type = "text";
                        icon.classList.remove("fa-eye");
                        icon.classList.add("fa-eye-slash");
                    } else {
                        passwordInput.type = "password";
                        icon.classList.remove("fa-eye-slash");
                        icon.classList.add("fa-eye");
                    }
                });
            }
            // Función para mostrar la alerta de credenciales
            function mostrarAlertaCredenciales() {
                // Obtener el email del doctor del formulario principal
                const emailDoctor = document.getElementById('email').value;

                // Recuperar contraseña si ya fue ingresada previamente
                const prevPassword = document.getElementById('passuser').value || '';

                // Crear el modal de SweetAlert con las credenciales
                Swal.fire({
                    title: 'Credenciales de Acceso',
                    html: `
            <div class="text-start">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i> Complete las credenciales del doctor.
                </div>
                <div class="mb-3">
                    <label for="swal-nomuser" class="form-label text-start">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="swal-nomuser" value="${emailDoctor}" disabled>
                        <button class="btn btn-outline-secondary" type="button" id="swal-editUsername">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                    </div>
                    <div class="text-danger mt-1" id="help-swal-nomuser" style="display: none; font-size: 0.875rem;"></div>
                </div>
                <div class="mb-3">
                    <label for="swal-passuser" class="form-label text-start">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="swal-passuser" placeholder="Ingrese contraseña">
                        <button class="btn btn-outline-secondary" type="button" id="swal-togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="text-danger mt-1" id="help-swal-passuser" style="display: none; font-size: 0.875rem;"></div>
                </div>
            </div>
        `,
                    showCancelButton: true,
                    confirmButtonText: 'Guardar y Continuar',
                    confirmButtonColor: '#6f42c1',
                    cancelButtonText: 'Cancelar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showLoaderOnConfirm: true,
                    didOpen: () => {
                        // Configurar el botón de mostrar/ocultar contraseña
                        const togglePasswordBtn = document.getElementById('swal-togglePassword');
                        const passwordInput = document.getElementById('swal-passuser');

                        togglePasswordBtn.addEventListener('click', function() {
                            const type = passwordInput.type === 'password' ? 'text' : 'password';
                            passwordInput.type = type;
                            const icon = this.querySelector('i');

                            if (type === 'text') {
                                icon.classList.remove('fa-eye');
                                icon.classList.add('fa-eye-slash');
                            } else {
                                icon.classList.remove('fa-eye-slash');
                                icon.classList.add('fa-eye');
                            }
                        });

                        // Configurar el botón de editar correo electrónico
                        const editUsernameBtn = document.getElementById('swal-editUsername');
                        const usernameInput = document.getElementById('swal-nomuser');

                        editUsernameBtn.addEventListener('click', function() {
                            // Si está deshabilitado, habilitar y cambiar botón a "Bloquear"
                            if (usernameInput.disabled) {
                                usernameInput.disabled = false;
                                usernameInput.focus();
                                this.innerHTML = '<i class="fas fa-lock"></i> Bloquear';
                                usernameInput.select(); // Seleccionar todo el texto para facilitar la edición
                            } else {
                                // Si está habilitado, deshabilitar y cambiar botón a "Editar"
                                usernameInput.disabled = true;
                                this.innerHTML = '<i class="fas fa-edit"></i> Editar';
                            }
                        });

                        // Si ya se ingresó una contraseña anteriormente, recuperarla
                        if (prevPassword) {
                            document.getElementById('swal-passuser').value = prevPassword;
                        }

                        // Agregar validación cuando el usuario termine de editar el correo
                        usernameInput.addEventListener('blur', function() {
                            const emailValue = this.value.trim();
                            if (!emailValue) {
                                showFieldError('swal-nomuser', 'El correo electrónico es obligatorio');
                            } else if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(emailValue)) {
                                showFieldError('swal-nomuser', 'Ingrese un correo electrónico válido');
                            } else {
                                hideFieldError('swal-nomuser');
                            }
                        });

                        // También validar al cambiar el correo
                        usernameInput.addEventListener('input', function() {
                            // Ocultar error si se está editando
                            hideFieldError('swal-nomuser');
                        });

                        // Validar contraseña cuando cambia
                        passwordInput.addEventListener('input', function() {
                            hideFieldError('swal-passuser');
                        });
                    },
                    preConfirm: async (login) => {
                        // Capturar valores de los campos
                        const nomuser = document.getElementById('swal-nomuser').value;
                        const passuser = document.getElementById('swal-passuser').value;

                        // Validar correo electrónico
                        if (!nomuser) {
                            showFieldError('swal-nomuser', 'El correo electrónico es obligatorio');
                            Swal.hideLoading();
                            return false;
                        }

                        if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(nomuser)) {
                            showFieldError('swal-nomuser', 'Ingrese un correo electrónico válido');
                            Swal.hideLoading();
                            return false;
                        }

                        // Validar contraseña
                        if (!passuser) {
                            showFieldError('swal-passuser', 'La contraseña es obligatoria');
                            Swal.hideLoading();
                            return false;
                        }

                        if (passuser.length < 6) {
                            showFieldError('swal-passuser', 'La contraseña debe tener al menos 6 caracteres');
                            Swal.hideLoading();
                            return false;
                        }

                        try {
                            // Verificar disponibilidad del correo
                            const disponible = await verificarCorreoDisponible(nomuser);

                            if (!disponible) {
                                // Si el correo no está disponible, mostrar error solo debajo del campo
                                // y agregar texto en rojo debajo del correo
                                document.getElementById('swal-nomuser').classList.add('is-invalid');
                                const helpText = document.getElementById('help-swal-nomuser');
                                helpText.textContent = 'Este correo electrónico ya está en uso como usuario';
                                helpText.style.display = 'block';

                                // Habilitar campo de correo para editar
                                document.getElementById('swal-nomuser').disabled = false;
                                document.getElementById('swal-editUsername').innerHTML = '<i class="fas fa-lock"></i> Bloquear';

                                // Dar focus al campo
                                document.getElementById('swal-nomuser').focus();
                                document.getElementById('swal-nomuser').select();

                                // Importante: devolver false para evitar que el modal se cierre
                                Swal.hideLoading();
                                return false;
                            }

                            // ACTUALIZACIÓN CLAVE: Guardar el email como correo y como nombre de usuario
                            // Actualizamos el email en los datos personales
                            document.getElementById('email').value = nomuser;

                            // Y guardamos el mismo email como nombre de usuario para las credenciales
                            document.getElementById('nomuser').value = nomuser;
                            document.getElementById('passuser').value = passuser;

                            return true;
                        } catch (error) {
                            console.error("Error al verificar disponibilidad del correo:", error);
                            showFieldError('swal-nomuser', 'Error al verificar disponibilidad del correo');
                            Swal.hideLoading();
                            return false;
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // IMPORTANTE: Asegurarse de que se actualizó el correo en ambos lugares
                        const newEmail = document.getElementById('swal-nomuser').value;

                        // Actualizar correo en datos personales si fue editado
                        if (newEmail !== document.getElementById('email').value) {
                            document.getElementById('email').value = newEmail;
                            console.log("Email actualizado en datos personales:", newEmail);
                        }

                        // Actualizar correo en credenciales
                        document.getElementById('nomuser').value = newEmail;
                        console.log("Email actualizado como usuario:", newEmail);

                        // Continuar a la pantalla de confirmación
                        mostrarPantallaConfirmacion();
                    }
                });
            }


            // Función para mostrar error en un campo específico
            function showFieldError(fieldId, message) {
                const field = document.getElementById(fieldId);
                const helpText = document.getElementById(`help-${fieldId}`);

                if (field && helpText) {
                    // Marcar el campo como inválido
                    field.classList.add('is-invalid');
                    field.classList.remove('is-valid');

                    // Mostrar mensaje de error
                    helpText.textContent = message;
                    helpText.style.display = 'block';
                }
            }

            // Función para ocultar error de un campo específico
            function hideFieldError(fieldId) {
                const field = document.getElementById(fieldId);
                const helpText = document.getElementById(`help-${fieldId}`);

                if (field && helpText) {
                    // Quitar marca de inválido
                    field.classList.remove('is-invalid');

                    // Ocultar mensaje de error
                    helpText.style.display = 'none';
                }
            }

            // Función para verificar si el correo electrónico ya está en uso
            function verificarCorreoExistente() {
                const usernameInput = document.getElementById('swal-nomuser');
                const email = usernameInput.value.trim();

                // Limpiar cualquier estado previo
                usernameInput.classList.remove('is-valid', 'is-invalid');
                document.getElementById('help-swal-nomuser').style.display = 'none';
                usernameInput.dataset.errorShown = 'false';

                // Validar formato de correo antes de verificar
                if (!email) {
                    usernameInput.classList.add('is-invalid');
                    document.getElementById('help-swal-nomuser').textContent = 'El correo electrónico es obligatorio';
                    document.getElementById('help-swal-nomuser').style.display = 'block';
                    return;
                }

                if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) {
                    usernameInput.classList.add('is-invalid');
                    document.getElementById('help-swal-nomuser').textContent = 'Ingrese un correo electrónico válido';
                    document.getElementById('help-swal-nomuser').style.display = 'block';
                    return;
                }

                // Mostrar indicador de carga
                usernameInput.classList.add('is-loading');

                // Verificar disponibilidad del correo
                verificarCorreoDisponible(email).then(disponible => {
                    usernameInput.classList.remove('is-loading');

                    if (disponible) {
                        usernameInput.classList.add('is-valid');
                        usernameInput.classList.remove('is-invalid');
                        document.getElementById('help-swal-nomuser').style.display = 'none';
                        // Opcional: Mostrar mensaje de éxito
                        showSuccessToast('Correo electrónico disponible');
                    } else {
                        usernameInput.classList.add('is-invalid');
                        usernameInput.classList.remove('is-valid');
                        document.getElementById('help-swal-nomuser').textContent = 'Este correo electrónico ya está en uso';
                        document.getElementById('help-swal-nomuser').style.display = 'block';

                        // Mostrar notificación en la esquina solo si no se ha mostrado antes
                        if (usernameInput.dataset.errorShown !== 'true') {
                            showErrorToast('El correo electrónico ya está en uso como usuario');
                            usernameInput.dataset.errorShown = 'true';
                        }
                    }
                }).catch(error => {
                    usernameInput.classList.remove('is-loading');
                    console.error("Error al verificar correo:", error);

                    // Marcar el campo como inválido y mostrar mensaje de error
                    usernameInput.classList.add('is-invalid');
                    usernameInput.classList.remove('is-valid');
                    document.getElementById('help-swal-nomuser').textContent = 'Error al verificar disponibilidad del correo';
                    document.getElementById('help-swal-nomuser').style.display = 'block';

                    showErrorToast('Error al verificar disponibilidad del correo');
                });
            }

            // Función para verificar si el correo ya está registrado como usuario
            async function verificarCorreoDisponible(email) {
                try {
                    const formData = new FormData();
                    formData.append('email', email);

                    // Agregamos un timeout para evitar esperas indefinidas
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 segundos de timeout

                    const response = await fetch('../../../controllers/credencial.controller.php?op=verificar_usuario', {
                        method: 'POST',
                        body: formData,
                        signal: controller.signal
                    });

                    // Limpiamos el timeout si la petición finaliza normalmente
                    clearTimeout(timeoutId);

                    if (!response.ok) {
                        throw new Error(`Error en la respuesta del servidor: ${response.status}`);
                    }

                    const data = await response.json();

                    // Validación adicional para asegurar que la respuesta tiene el formato esperado
                    if (typeof data.disponible === 'undefined') {
                        console.error("Respuesta del servidor no contiene el campo 'disponible':", data);
                        throw new Error('Respuesta inválida del servidor');
                    }

                    return data.disponible === true; // Retorna true si está disponible, false si ya existe
                } catch (error) {
                    console.error("Error al verificar disponibilidad del correo:", error);
                    // Propagar el error para que sea manejado por el preConfirm
                    throw error;
                }
            }

            // Agregar estilo CSS para el indicador de carga
            const verificacionStyle = document.createElement('style');
            verificacionStyle.textContent = `
.is-loading {
    background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-loader"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>');
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 20px;
    transition: background 0.3s;
    animation: rotating 2s linear infinite;
}
@keyframes rotating {
    from {
        background-position: right 10px center;
    }
    to {
        background-position: right 10px center;
        transform: rotate(360deg);
    }
}
`;
            document.head.appendChild(verificacionStyle);

            // Función para mostrar la pantalla de confirmación
            function mostrarPantallaConfirmacion() {
                // Ocultar todas las tarjetas
                const allCards = [
                    "personalInfoCard",
                    "informacionComplementariaCard",
                    "confirmacionCard"
                ];

                allCards.forEach(card => {
                    const element = document.getElementById(card);
                    if (element) {
                        element.classList.add("d-none");
                    }
                });

                // Mostrar la tarjeta de confirmación
                document.getElementById('confirmacionCard').classList.remove("d-none");
                currentTab = 'confirmacion';

                // Actualizar barra de progreso
                updateProgressBar(100);

                // Cargar los datos de resumen
                loadConfirmationData();
            }


            // Función para actualizar la barra de progreso según la pestaña
            function updateProgressBarForTab(tab) {
                const progressValues = {
                    'personalInfo': 0,
                    'informacionProfesional': 0, // Mismo valor que personalInfo
                    'contrato': 50, // Ahora todas las pestañas intermedias valen 50%
                    'horario': 50,
                    'credenciales': 50,
                    'confirmacion': 100
                };

                updateProgressBar(progressValues[tab] || 0);
            }


            // Función para actualizar la barra de progreso
            function updateProgressBar(percentage) {
                const progressBar = document.querySelector(".progress-bar");
                progressBar.style.width = percentage + "%";
                progressBar.setAttribute("aria-valuenow", percentage);
            }

            // Función para verificar si todos los campos personales están completos
            function checkPersonalFields() {
                const requiredFields = [
                    'tipodoc', 'nrodoc', 'apellidos', 'nombres', 'fechanacimiento',
                    'genero', 'telefono', 'email', 'direccion'
                ];

                let allFilled = true;

                // Verificar cada campo requerido
                requiredFields.forEach(field => {
                    const input = document.getElementById(field);
                    if (!input || !input.value.trim()) {
                        allFilled = false;

                        // Marcar como inválido solo si ya se intentó validar
                        if (input && (input.classList.contains('is-invalid') || input.classList.contains('is-valid'))) {
                            markFieldAsInvalid(input);
                            addFieldHelpMessage(input, 'Este campo es obligatorio');
                        }
                    } else {
                        // Validaciones específicas por tipo de campo
                        let isValid = true;

                        // Validar formato del documento
                        if (field === 'nrodoc') {
                            const tipodoc = document.getElementById('tipodoc').value;
                            if (tipodoc && documentoConfig[tipodoc] && !documentoConfig[tipodoc].pattern.test(input.value)) {
                                isValid = false;
                                addFieldHelpMessage(input, documentoConfig[tipodoc].message);
                            }
                        }

                        // Validar fecha de nacimiento - AÑADIDO
                        if (field === 'fechanacimiento') {
                            const validation = validateFechaNacimiento(input.value);
                            if (!validation.isValid) {
                                isValid = false;
                                addFieldHelpMessage(input, validation.message);
                            }
                        }

                        // Validar teléfono
                        if (field === 'telefono' && !/^9\d{8}$/.test(input.value)) {
                            isValid = false;
                            addFieldHelpMessage(input, 'El teléfono debe tener 9 dígitos y comenzar con 9');
                        }

                        // Validar email
                        if (field === 'email' && !validateEmail(input.value)) {
                            isValid = false;
                            addFieldHelpMessage(input, 'Ingrese un correo electrónico válido');
                        }

                        if (isValid) {
                            markFieldAsValid(input);
                            removeFieldHelpMessage(input);
                        } else {
                            markFieldAsInvalid(input);
                            allFilled = false;
                        }
                    }
                });

                return allFilled;
            }
            // Función para validar la fecha de nacimiento
            function validateFechaNacimiento(fechaNacimiento) {
                try {
                    console.log("Fecha recibida para validar:", fechaNacimiento);

                    // Asegurar que la fecha esté en formato correcto
                    let fechaNacimientoDate;

                    // Manejo de diferentes formatos de fecha
                    if (fechaNacimiento.includes('/')) {
                        // Si viene en formato DD/MM/YYYY
                        const partes = fechaNacimiento.split('/');
                        if (partes.length === 3) {
                            fechaNacimientoDate = new Date(partes[2], partes[1] - 1, partes[0]);
                        } else {
                            fechaNacimientoDate = new Date(fechaNacimiento);
                        }
                    } else {
                        // Formato estándar YYYY-MM-DD (HTML5 date input)
                        fechaNacimientoDate = new Date(fechaNacimiento);
                    }

                    // Verificar si la fecha es válida
                    if (isNaN(fechaNacimientoDate.getTime())) {
                        console.error("Error: Fecha inválida:", fechaNacimiento);
                        return {
                            isValid: false,
                            needsConfirmation: false,
                            edad: 0,
                            message: 'La fecha de nacimiento no es válida'
                        };
                    }

                    // Extraer año explícitamente para depuración
                    const añoNacimiento = fechaNacimientoDate.getFullYear();
                    console.log("Año de nacimiento detectado:", añoNacimiento);

                    // Verificar explícitamente si el año es anterior a 1920
                    const añoMinimo = 1920;
                    if (añoNacimiento < añoMinimo) {
                        console.log(`Año ${añoNacimiento} es anterior a ${añoMinimo}`);
                        return {
                            isValid: false,
                            needsConfirmation: false,
                            edad: 0,
                            message: `El año de nacimiento no puede ser anterior a ${añoMinimo}`
                        };
                    }

                    // Obtener la fecha actual
                    const hoy = new Date();

                    // Calcular edad correctamente
                    let edad = hoy.getFullYear() - añoNacimiento;
                    const meses = hoy.getMonth() - fechaNacimientoDate.getMonth();
                    if (meses < 0 || (meses === 0 && hoy.getDate() < fechaNacimientoDate.getDate())) {
                        edad--;
                    }

                    console.log("Edad calculada:", edad);

                    // Verificar si la fecha es en el futuro
                    if (fechaNacimientoDate > hoy) {
                        return {
                            isValid: false,
                            needsConfirmation: false,
                            edad: 0,
                            message: 'La fecha de nacimiento no puede ser en el futuro'
                        };
                    }

                    // Verificar si es menor de edad
                    if (edad < 18) {
                        return {
                            isValid: false,
                            needsConfirmation: false,
                            edad: edad,
                            message: 'El doctor debe ser mayor de edad (al menos 18 años)'
                        };
                    }

                    // Verificar si está en el rango que necesita confirmación (18-23 años)
                    if (edad >= 18 && edad <= 23) {
                        return {
                            isValid: true,
                            needsConfirmation: true,
                            edad: edad,
                            message: `El doctor tiene ${edad} años. Se recomienda que los doctores tengan al menos 24 años. ¿Desea continuar con el registro?`
                        };
                    }

                    // Verificar si tiene una edad razonable para ser doctor (menos de 90 años)
                    if (edad > 90) {
                        return {
                            isValid: false,
                            needsConfirmation: false,
                            edad: edad,
                            message: 'La edad parece no ser válida para un doctor activo'
                        };
                    }

                    // Si llega aquí, la edad es válida (24-90 años)
                    return {
                        isValid: true,
                        needsConfirmation: false,
                        edad: edad,
                        message: ''
                    };
                } catch (error) {
                    console.error("Error en validación de fecha:", error);
                    return {
                        isValid: false,
                        needsConfirmation: false,
                        edad: 0,
                        message: 'Error al procesar la fecha de nacimiento'
                    };
                }
            }
            // Para integrar en el código existente
            document.getElementById('fechanacimiento').addEventListener('change', function() {
                const fechaNacimiento = this.value;
                console.log("Fecha seleccionada:", fechaNacimiento);

                if (fechaNacimiento) {
                    const validation = validateFechaNacimiento(fechaNacimiento);
                    console.log("Resultado de validación:", validation);

                    if (validation.isValid) {
                        // Si necesita confirmación (edad entre 18 y 23)
                        if (validation.needsConfirmation) {
                            Swal.fire({
                                title: 'Confirmar registro',
                                html: validation.message,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Sí, continuar',
                                cancelButtonText: 'No, usar otra fecha'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Si confirma, marcar como válido
                                    markFieldAsValid(this);
                                    removeFieldHelpMessage(this);
                                    // Guardar que ha sido confirmado
                                    this.setAttribute('data-age-confirmed', 'true');
                                    // Notificar al usuario
                                    showSuccessToast('Edad confirmada. Puede continuar con el registro.');
                                } else {
                                    // Si cancela, limpiar el campo
                                    this.value = '';
                                    markFieldAsInvalid(this);
                                    addFieldHelpMessage(this, 'Por favor, seleccione otra fecha de nacimiento');
                                }
                            });
                        } else {
                            // Edad válida (24 años o más)
                            markFieldAsValid(this);
                            removeFieldHelpMessage(this);
                            showSuccessToast('Fecha de nacimiento válida');
                        }
                    } else {
                        // Edad no válida (menor de 18 años u otro problema)
                        markFieldAsInvalid(this);
                        addFieldHelpMessage(this, validation.message);
                        // Mostrar notificación en la esquina
                        showErrorToast(validation.message);
                    }
                } else {
                    markFieldAsInvalid(this);
                    addFieldHelpMessage(this, 'La fecha de nacimiento es requerida');
                }
            });
            // Modifica la validación para que solo valide contrato y horario
            function validateInformacionComplementaria() {
                // Validar contrato
                const contratoValid = validateContratoInfo();

                // Validar horario
                const horarioValid = validateHorarioInfo();

                // Ya no validamos credenciales aquí
                return contratoValid && horarioValid;
            }
            // Función para validar la información personal
            async function validatePersonalInfo() {
                const requiredFields = [
                    'tipodoc', 'nrodoc', 'apellidos', 'nombres', 'fechanacimiento',
                    'genero', 'telefono', 'email', 'direccion'
                ];

                let isValid = true;
                let firstInvalidField = null;

                // Validar campos obligatorios
                for (const field of requiredFields) {
                    const input = document.getElementById(field);
                    if (!input || !input.value.trim()) {
                        markFieldAsInvalid(input);
                        addFieldHelpMessage(input, 'Este campo es obligatorio');
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = input;
                    } else {
                        // Validaciones específicas por tipo de campo
                        let fieldValid = true;

                        // Validación especial para fecha de nacimiento
                        if (field === 'fechanacimiento') {
                            // Esta validación puede ser asíncrona si hay confirmación
                            const fechaResult = await validateFechaNacimientoField(input);
                            if (!fechaResult) {
                                fieldValid = false;
                                if (!firstInvalidField) firstInvalidField = input;
                            }
                        }
                        // Otras validaciones específicas...
                        else if (field === 'nrodoc') {
                            const tipodoc = document.getElementById('tipodoc').value;
                            if (tipodoc && documentoConfig[tipodoc] && !documentoConfig[tipodoc].pattern.test(input.value)) {
                                fieldValid = false;
                                addFieldHelpMessage(input, documentoConfig[tipodoc].message);
                                if (!firstInvalidField) firstInvalidField = input;
                            }
                        } else if (field === 'telefono' && !/^9\d{8}$/.test(input.value)) {
                            fieldValid = false;
                            addFieldHelpMessage(input, 'El teléfono debe tener 9 dígitos y comenzar con 9');
                            if (!firstInvalidField) firstInvalidField = input;
                        } else if (field === 'email' && !validateEmail(input.value)) {
                            fieldValid = false;
                            addFieldHelpMessage(input, 'Ingrese un correo electrónico válido');
                            if (!firstInvalidField) firstInvalidField = input;
                        }

                        if (fieldValid) {
                            markFieldAsValid(input);
                            removeFieldHelpMessage(input);
                        } else {
                            markFieldAsInvalid(input);
                            isValid = false;
                        }
                    }
                }

                // Si hay campos inválidos, mostrar mensaje y hacer scroll
                if (!isValid && firstInvalidField) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Información personal incompleta',
                        text: 'Por favor, complete correctamente todos los campos marcados en rojo.'
                    });

                    firstInvalidField.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    firstInvalidField.focus();
                }

                return isValid;
            }

            // Función para validar la información profesional
            function validateProfessionalInfo() {
                // Para el campo de especialidad
                const especialidad = document.getElementById('especialidad');
                let isValid = true;

                // Validar especialidad seleccionada
                if (!especialidad || !especialidad.value) {
                    markFieldAsInvalid(especialidad);
                    addFieldHelpMessage(especialidad, 'Debe seleccionar una especialidad');
                    isValid = false;
                } else {
                    markFieldAsValid(especialidad);
                    removeFieldHelpMessage(especialidad);

                    // Asegurarse de que el campo oculto de precio tenga un valor
                    const precioInput = document.getElementById('precioatencion');
                    if (precioInput && (!precioInput.value || parseFloat(precioInput.value) <= 0)) {
                        // Si no tiene precio, actualizarlo automáticamente
                        cargarPrecioEspecialidad(especialidad.value);
                    }
                }

                if (!isValid) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Información profesional incompleta',
                        text: 'Por favor, seleccione una especialidad para continuar'
                    });
                }

                return isValid;
            }

            // Agregar evento para controlar el comportamiento según el tipo de contrato
            document.getElementById('tipocontrato').addEventListener('change', function() {
                // Limpiar campos de fecha
                const fechaInicio = document.getElementById('fechainicio');
                const fechaFin = document.getElementById('fechafin');

                if (fechaInicio) fechaInicio.value = '';
                if (fechaFin) fechaFin.value = '';

                // Si es tipo indefinido, deshabilitar fecha fin
                if (this.value === 'INDEFINIDO') {
                    if (fechaFin) {
                        fechaFin.disabled = true;
                        fechaFin.value = '';

                        // Agregar mensaje visual
                        const helpText = document.createElement('small');
                        helpText.id = 'helpTextFechaFin';
                        helpText.className = 'text-muted';
                        helpText.textContent = 'No aplica para contratos indefinidos';

                        // Eliminar mensaje previo si existe
                        const prevHelpText = document.getElementById('helpTextFechaFin');
                        if (prevHelpText) prevHelpText.remove();

                        // Agregar nuevo mensaje
                        if (fechaFin.parentNode) {
                            fechaFin.parentNode.appendChild(helpText);
                        }
                    }
                } else {
                    // Para otros tipos, habilitar fecha fin
                    if (fechaFin) {
                        fechaFin.disabled = false;

                        // Eliminar mensaje si existe
                        const helpText = document.getElementById('helpTextFechaFin');
                        if (helpText) helpText.remove();
                    }
                }

                // Resetear horarios
                horariosAgregados = [];
                document.getElementById('horariosAgregados').value = JSON.stringify(horariosAgregados);

                // Actualizar interfaces de horario
                actualizarListaHorarios();
                actualizarTablaHorarios();

                // Deshabilitar controles de horario
                const btnAgregarHorario = document.getElementById('btnAgregarHorario');
                const horarioDia = document.getElementById('horarioDia');
                if (horarioDia) {
                    // Deshabilitar inicialmente el selector de día
                    horarioDia.disabled = true;

                    // Remover listeners anteriores (por si acaso)
                    const nuevoHorarioDia = horarioDia.cloneNode(true);
                    horarioDia.parentNode.replaceChild(nuevoHorarioDia, horarioDia);

                    // Transferir el estado de deshabilitado al nuevo elemento
                    nuevoHorarioDia.disabled = true;

                    // Agregar nuevo listener
                    nuevoHorarioDia.addEventListener('change', function() {
                        console.log("Cambio de día de atención detectado:", this.value);

                        // Limpiar horas y validaciones
                        const horarioInicio = document.getElementById('horarioInicio');
                        const horarioFin = document.getElementById('horarioFin');

                        if (horarioInicio && horarioFin) {
                            // Si hay un día seleccionado, habilitar los campos de hora
                            if (this.value) {
                                horarioInicio.disabled = false;
                                horarioFin.disabled = false;

                                // Limpiar valores previos
                                horarioInicio.value = '';
                                horarioFin.value = '';

                                // Limpiar validaciones previas
                                horarioInicio.classList.remove('is-valid', 'is-invalid');
                                horarioFin.classList.remove('is-valid', 'is-invalid');
                                removeFieldHelpMessage(horarioInicio);
                                removeFieldHelpMessage(horarioFin);

                                // Dar foco al campo de hora de inicio
                                horarioInicio.focus();

                                console.log("Campos de hora habilitados para el día:", this.value);
                            } else {
                                // Si no hay día seleccionado, deshabilitar los campos de hora
                                horarioInicio.disabled = true;
                                horarioFin.disabled = true;

                                // Limpiar valores
                                horarioInicio.value = '';
                                horarioFin.value = '';

                                // Limpiar validaciones
                                horarioInicio.classList.remove('is-valid', 'is-invalid');
                                horarioFin.classList.remove('is-valid', 'is-invalid');
                                removeFieldHelpMessage(horarioInicio);
                                removeFieldHelpMessage(horarioFin);

                                console.log("Campos de hora deshabilitados - No hay día seleccionado");
                            }
                        }

                        // Mostrar notificación cuando se selecciona un día
                        if (this.value) {
                            showSuccessToast('Seleccione las horas para el día ' +
                                (this.options[this.selectedIndex] ? this.options[this.selectedIndex].text : this.value));
                        }
                    });

                    console.log("Evento para gestión de campos de hora configurado correctamente");
                } else {
                    console.error("No se encontró el elemento horarioDia");
                }
                const horarioInicio = document.getElementById('horarioInicio');
                const horarioFin = document.getElementById('horarioFin');

                btnAgregarHorario.disabled = true;
                horarioDia.disabled = true;
                horarioInicio.disabled = true;
                horarioFin.disabled = true;

                // Limpiar valores de horario
                horarioDia.value = '';
                horarioInicio.value = '';
                horarioFin.value = '';

                // Eliminar clases de validación
                const campos = [horarioDia, horarioInicio, horarioFin];
                campos.forEach(campo => {
                    if (campo) {
                        campo.classList.remove('is-valid', 'is-invalid');
                        removeFieldHelpMessage(campo);
                    }
                });

                // Limpiar mensajes de advertencia para evitar duplicación
                limpiarMensajesAdvertencia();

                // Agregar mensaje de advertencia sobre confirmar contrato
                deshabilitarSeccionHorarios();

                // Notificar al usuario
                showSuccessToast('Se han reiniciado los campos de contrato y horarios');
            });
            console.log("Configurando evento para reinicio de horas al cambiar día");
            document.getElementById('horarioDia').addEventListener('change', function() {
                // Limpiar horas y validaciones
                const horarioInicio = document.getElementById('horarioInicio');
                const horarioFin = document.getElementById('horarioFin');

                if (horarioInicio) {
                    horarioInicio.value = '';
                    horarioInicio.classList.remove('is-valid', 'is-invalid');
                    removeFieldHelpMessage(horarioInicio);
                }

                if (horarioFin) {
                    horarioFin.value = '';
                    horarioFin.classList.remove('is-valid', 'is-invalid');
                    removeFieldHelpMessage(horarioFin);
                }
            });
            console.log("Configurando eventos para validación de campos de hora");
            document.getElementById('fechainicio').addEventListener('change', function() {
                // Reiniciar los horarios
                resetearHorarios();

                // Deshabilitar controles de horarios
                deshabilitarControlesHorario();

                // Mostrar mensaje de que debe confirmar el contrato
                mostrarMensajeConfirmarContrato();

                // Actualizar la fecha mínima para fecha fin
                const fechaFinInput = document.getElementById('fechafin');
                if (fechaFinInput && this.value) {
                    fechaFinInput.min = this.value;
                }
            });
            // Agregar evento para validar la fecha fin en tiempo real
            document.getElementById('fechafin').addEventListener('change', function() {
                // Reiniciar los horarios
                resetearHorarios();

                // Deshabilitar controles de horarios
                deshabilitarControlesHorario();

                // Mostrar mensaje de que debe confirmar el contrato
                mostrarMensajeConfirmarContrato();
            });
            // Función para deshabilitar los controles de horario
            function deshabilitarControlesHorario() {
                const btnAgregarHorario = document.getElementById('btnAgregarHorario');
                const horarioDia = document.getElementById('horarioDia');
                const horarioInicio = document.getElementById('horarioInicio');
                const horarioFin = document.getElementById('horarioFin');

                btnAgregarHorario.disabled = true;
                horarioDia.disabled = true;
                horarioInicio.disabled = true;
                horarioFin.disabled = true;

                // Limpiar los campos
                horarioDia.value = '';
                horarioInicio.value = '';
                horarioFin.value = '';

                // Agregar clase de opacidad a la sección de horarios
                const tablaHorarios = document.querySelector('.table-responsive');
                if (tablaHorarios) {
                    tablaHorarios.classList.add('opacity-50');
                }
            }
            // Función para habilitar los controles de horario
            function habilitarControlesHorario() {
                const btnAgregarHorario = document.getElementById('btnAgregarHorario');
                const horarioDia = document.getElementById('horarioDia');
                const horarioInicio = document.getElementById('horarioInicio');
                const horarioFin = document.getElementById('horarioFin');

                btnAgregarHorario.disabled = false;
                horarioDia.disabled = false;
                horarioInicio.disabled = false;
                horarioFin.disabled = false;

                // Quitar clase de opacidad de la sección de horarios
                const tablaHorarios = document.querySelector('.table-responsive');
                if (tablaHorarios) {
                    tablaHorarios.classList.remove('opacity-50');
                }
            }
            // Función para mostrar mensaje de confirmar contrato
            function mostrarMensajeConfirmarContrato() {
                // Eliminar mensaje previo si existe
                const mensajeAnterior = document.getElementById('mensajeConfirmarContrato');
                if (mensajeAnterior) {
                    mensajeAnterior.remove();
                }

                // Eliminar mensaje de días habilitados si existe
                const mensajeDias = document.getElementById('mensajeDiasHabilitados');
                if (mensajeDias) {
                    mensajeDias.remove();
                }

                // Crear y mostrar nuevo mensaje
                const tablaHorarios = document.querySelector('.table-responsive');
                if (tablaHorarios) {
                    const mensajeInfo = document.createElement('div');
                    mensajeInfo.id = 'mensajeConfirmarContrato';
                    mensajeInfo.className = 'alert alert-warning mt-3 mb-3';
                    mensajeInfo.innerHTML = '<i class="fas fa-info-circle me-2"></i> Primero debe completar y confirmar la información del contrato para habilitar los horarios de atención.';

                    // Insertar antes de la tabla
                    tablaHorarios.parentNode.insertBefore(mensajeInfo, tablaHorarios);
                }
            }
            // Función para resetear los horarios
            function resetearHorarios() {
                // Limpiar el array de horarios
                horariosAgregados = [];
                document.getElementById('horariosAgregados').value = JSON.stringify(horariosAgregados);

                // Actualizar la interfaz
                actualizarListaHorarios();
                actualizarTablaHorarios();

                // Limpiar los días disponibles
                diasDisponibles.clear();
            }
            // Función para determinar los días disponibles según el contrato
            function habilitarDiasSegunFechasContrato() {
                const tipoContrato = document.getElementById('tipocontrato').value;
                const fechaInicioStr = document.getElementById('fechainicio').value;
                const fechaFinStr = document.getElementById('fechafin').value;

                if (!fechaInicioStr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Debe seleccionar una fecha de inicio para el contrato'
                    });
                    return false;
                }

                // Verificar si es contrato indefinido o si tiene fecha de fin válida
                if (tipoContrato !== 'INDEFINIDO' && !fechaFinStr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Debe seleccionar una fecha de fin para este tipo de contrato'
                    });
                    return false;
                }

                // Convertir fechas a objetos Date para validación
                const fechaInicio = new Date(fechaInicioStr);
                let fechaFin = null;
                if (fechaFinStr) {
                    fechaFin = new Date(fechaFinStr);
                    // Verificar que fechaFin no sea anterior a fechaInicio
                    if (fechaFin < fechaInicio) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error en fechas',
                            text: 'La fecha de fin no puede ser anterior a la fecha de inicio'
                        });

                        const fechaFinInput = document.getElementById('fechafin');
                        markFieldAsInvalid(fechaFinInput);
                        addFieldHelpMessage(fechaFinInput, 'La fecha de fin no puede ser anterior a la fecha de inicio');

                        return false;
                    }
                }

                // Obtener los días habilitados según el rango de fechas
                const diasHabilitados = obtenerDiasHabilitados(fechaInicioStr, fechaFinStr);

                // Actualizar el selector de días con los días habilitados
                actualizarSelectorDias(diasHabilitados);

                // Habilitar el selector de días pero mantener los campos de hora deshabilitados inicialmente
                const btnAgregarHorario = document.getElementById('btnAgregarHorario');
                const horarioDia = document.getElementById('horarioDia');
                const horarioInicio = document.getElementById('horarioInicio');
                const horarioFin = document.getElementById('horarioFin');

                btnAgregarHorario.disabled = false;
                horarioDia.disabled = false;

                // Mantener los campos de hora deshabilitados hasta que se seleccione un día
                horarioInicio.disabled = true;
                horarioFin.disabled = true;

                // Limpiar los valores
                horarioDia.value = '';
                horarioInicio.value = '';
                horarioFin.value = '';

                // Limpiar clases de validación
                horarioDia.classList.remove('is-valid', 'is-invalid');
                horarioInicio.classList.remove('is-valid', 'is-invalid');
                horarioFin.classList.remove('is-valid', 'is-invalid');

                // Limpiar mensajes de error
                removeFieldHelpMessage(horarioDia);
                removeFieldHelpMessage(horarioInicio);
                removeFieldHelpMessage(horarioFin);

                // Habilitar el botón Siguiente
                const btnSiguiente = document.querySelector('#informacionComplementariaCard .btn-primary');
                if (btnSiguiente) {
                    btnSiguiente.disabled = false;
                }

                // Quitar mensaje de advertencia
                const mensajeConfirmar = document.getElementById('mensajeConfirmarContrato');
                if (mensajeConfirmar) {
                    mensajeConfirmar.remove();
                }

                // Mostrar mensaje de contrato confirmado con los días disponibles
                const tablaHorarios = document.querySelector('.table-responsive');
                if (tablaHorarios) {
                    tablaHorarios.classList.remove('opacity-50');

                    // Crear mensaje para mostrar días habilitados
                    const mensajeInfo = document.createElement('div');
                    mensajeInfo.id = 'mensajeDiasHabilitados';
                    mensajeInfo.className = 'alert alert-success mt-3 mb-3';

                    // Formatear nombres de días para el mensaje
                    const nombresDias = {
                        'LUNES': 'Lunes',
                        'MARTES': 'Martes',
                        'MIERCOLES': 'Miércoles',
                        'JUEVES': 'Jueves',
                        'VIERNES': 'Viernes',
                        'SABADO': 'Sábado',
                        'DOMINGO': 'Domingo'
                    };

                    const diasFormateados = Array.from(diasHabilitados)
                        .map(dia => nombresDias[dia])
                        .sort((a, b) => {
                            const orden = {
                                'Lunes': 1,
                                'Martes': 2,
                                'Miércoles': 3,
                                'Jueves': 4,
                                'Viernes': 5,
                                'Sábado': 6,
                                'Domingo': 7
                            };
                            return orden[a] - orden[b];
                        });

                    // Crear mensaje según cantidad de días
                    let mensajeDias = '';
                    if (diasFormateados.length === 7) {
                        mensajeDias = 'todos los días de la semana';
                    } else if (diasFormateados.length === 1) {
                        mensajeDias = `únicamente el día <strong>${diasFormateados[0]}</strong>`;
                    } else {
                        mensajeDias = `los siguientes días: <strong>${diasFormateados.join(', ')}</strong>`;
                    }

                    // Crear mensaje con fechas de contrato
                    let mensajeContrato = '';
                    if (fechaFin) {
                        const fechaInicioFormateada = fechaInicio.toLocaleDateString('es-ES');
                        const fechaFinFormateada = fechaFin.toLocaleDateString('es-ES');
                        mensajeContrato = `Contrato confirmado desde ${fechaInicioFormateada} hasta ${fechaFinFormateada}. `;
                    } else {
                        const fechaInicioFormateada = fechaInicio.toLocaleDateString('es-ES');
                        mensajeContrato = `Contrato indefinido confirmado desde ${fechaInicioFormateada}. `;
                    }

                    // Mensaje completo
                    mensajeInfo.innerHTML = `<i class="fas fa-check-circle me-2"></i> ${mensajeContrato}Se han habilitado ${mensajeDias} para agregar horarios.`;

                    // Eliminar mensaje anterior si existe
                    const mensajeAnterior = document.getElementById('mensajeDiasHabilitados');
                    if (mensajeAnterior) {
                        mensajeAnterior.remove();
                    }

                    // Insertar antes de la tabla
                    tablaHorarios.parentNode.insertBefore(mensajeInfo, tablaHorarios);
                }

                // Mostrar notificación
                showSuccessToast('Contrato confirmado. Se han habilitado los días según el periodo seleccionado.');

                // Añadir desplazamiento automático más preciso, justo después del botón
                setTimeout(() => {
                    // Obtener referencia al botón confirmar contrato
                    const btnConfirmarContrato = document.getElementById('btnConfirmarContrato');
                    if (btnConfirmarContrato) {
                        // Desplazar a la posición justo después del botón
                        const targetPosition = btnConfirmarContrato.getBoundingClientRect().bottom + window.scrollY + 20;
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });

                        // Dar foco al selector de días
                        document.getElementById('horarioDia').focus();
                    }
                }, 150); // Reducido a 200ms para que sea más rápido

                return true;
            }
            // Función para actualizar el selector de días disponibles
            function actualizarSelectorDias(diasHabilitados) {
                const horarioDia = document.getElementById('horarioDia');
                if (!horarioDia) return;

                // Limpiar opciones actuales
                horarioDia.innerHTML = '<option value="">Seleccione día...</option>';

                // Nombres más amigables para mostrar
                const nombresDias = {
                    'LUNES': 'Lunes',
                    'MARTES': 'Martes',
                    'MIERCOLES': 'Miércoles',
                    'JUEVES': 'Jueves',
                    'VIERNES': 'Viernes',
                    'SABADO': 'Sábado',
                    'DOMINGO': 'Domingo'
                };

                // Orden de los días para mostrar
                const ordenDias = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'];

                // Filtrar solo los días habilitados y mantener el orden
                const diasOrdenados = ordenDias.filter(dia => diasHabilitados.has(dia));

                // Agregar opciones para los días habilitados
                diasOrdenados.forEach(dia => {
                    const option = document.createElement('option');
                    option.value = dia;
                    option.textContent = nombresDias[dia];
                    horarioDia.appendChild(option);
                });
            }
            // Función para mostrar mensaje con los días habilitados
            function mostrarMensajeDiasHabilitados() {
                // Eliminar mensaje anterior si existe
                const mensajeAnterior = document.getElementById('mensajeConfirmarContrato');
                if (mensajeAnterior) {
                    mensajeAnterior.remove();
                }

                // Formatear la lista de días disponibles para mostrar
                const nombresDias = {
                    'LUNES': 'Lunes',
                    'MARTES': 'Martes',
                    'MIERCOLES': 'Miércoles',
                    'JUEVES': 'Jueves',
                    'VIERNES': 'Viernes',
                    'SABADO': 'Sábado',
                    'DOMINGO': 'Domingo'
                };

                const diasArray = Array.from(diasDisponibles).map(dia => nombresDias[dia] || dia);
                diasArray.sort((a, b) => {
                    const orden = {
                        'Lunes': 1,
                        'Martes': 2,
                        'Miércoles': 3,
                        'Jueves': 4,
                        'Viernes': 5,
                        'Sábado': 6,
                        'Domingo': 7
                    };
                    return orden[a] - orden[b];
                });

                let mensaje = '';

                // Formatear el mensaje según la cantidad de días
                if (diasArray.length === 7) {
                    mensaje = 'Se han habilitado todos los días de la semana.';
                } else if (diasArray.length === 1) {
                    mensaje = `Se ha habilitado únicamente el día: <strong>${diasArray[0]}</strong>.`;
                } else {
                    mensaje = `Se han habilitado los siguientes días: <strong>${diasArray.join(', ')}</strong>.`;
                }

                // Crear y mostrar mensaje
                const tablaHorarios = document.querySelector('.table-responsive');
                if (tablaHorarios) {
                    const mensajeInfo = document.createElement('div');
                    mensajeInfo.id = 'mensajeDiasHabilitados';
                    mensajeInfo.className = 'alert alert-info mt-3 mb-3';
                    mensajeInfo.innerHTML = `<i class="fas fa-calendar-check me-2"></i> ${mensaje}`;

                    // Insertar antes de la tabla
                    tablaHorarios.parentNode.insertBefore(mensajeInfo, tablaHorarios);
                }
            }
            // Función para agregar un nuevo horario
            function agregarNuevoHorario() {
                console.log("Iniciando la función agregarNuevoHorario");

                // Obtener los valores del formulario
                const dia = document.getElementById('horarioDia').value;
                const horaInicio = document.getElementById('horarioInicio').value;
                const horaFin = document.getElementById('horarioFin').value;

                console.log("Valores capturados:", {
                    dia,
                    horaInicio,
                    horaFin
                });

                // Validar que se hayan ingresado todos los campos
                if (!dia || !horaInicio || !horaFin) {
                    console.log("Campos incompletos detectados");
                    Swal.fire({
                        icon: 'error',
                        title: 'Campos incompletos',
                        text: 'Por favor, complete todos los campos del horario.'
                    });
                    return;
                }

                // Validar que la hora fin sea posterior a la hora inicio
                const inicioTime = new Date(`2000-01-01T${horaInicio}`);
                const finTime = new Date(`2000-01-01T${horaFin}`);

                if (finTime <= inicioTime) {
                    console.log("Hora fin no es posterior a hora inicio");
                    Swal.fire({
                        icon: 'error',
                        title: 'Horario inválido',
                        text: 'La hora de fin debe ser posterior a la hora de inicio.'
                    });
                    return;
                }

                // Obtener el nombre del día para visualización
                const nombresDias = {
                    'LUNES': 'Lunes',
                    'MARTES': 'Martes',
                    'MIERCOLES': 'Miércoles',
                    'JUEVES': 'Jueves',
                    'VIERNES': 'Viernes',
                    'SABADO': 'Sábado',
                    'DOMINGO': 'Domingo'
                };

                const diaNombre = nombresDias[dia] || document.getElementById('horarioDia').options[document.getElementById('horarioDia').selectedIndex].text;
                console.log("Nombre del día:", diaNombre);

                // Buscar los horarios existentes para este día
                const horariosExistentes = horariosAgregados.filter(h => h.dia === dia);

                // Verificar el límite de 4 horarios por día
                if (horariosExistentes.length >= 4) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Límite de horarios alcanzado',
                        text: `Ya ha registrado el máximo de 4 horarios para el día ${diaNombre}.`
                    });
                    return;
                }

                // Verificar si el nuevo horario se cruza con alguno existente
                for (const horarioExistente of horariosExistentes) {
                    const existenteInicio = new Date(`2000-01-01T${horarioExistente.horaInicio}`);
                    const existenteFin = new Date(`2000-01-01T${horarioExistente.horaFin}`);

                    // Verificar si hay superposición de horarios
                    const seCruzan = (inicioTime < existenteFin && finTime > existenteInicio);

                    if (seCruzan) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Horarios superpuestos',
                            html: `El nuevo horario (${horaInicio} - ${horaFin}) se cruza con un horario existente (${horarioExistente.horaInicio} - ${horarioExistente.horaFin}) para el día ${diaNombre}.`
                        });
                        return;
                    }
                }

                // Si todo es válido, agregar el horario a la lista
                const nuevoHorario = {
                    id: Date.now(), // ID único para facilitar la eliminación
                    dia: dia,
                    horaInicio: horaInicio,
                    horaFin: horaFin,
                    diaNombre: diaNombre
                };

                console.log("Agregando nuevo horario:", nuevoHorario);
                horariosAgregados.push(nuevoHorario);

                // Actualizar el campo oculto con los horarios en formato JSON
                document.getElementById('horariosAgregados').value = JSON.stringify(horariosAgregados);
                console.log("Horarios guardados:", horariosAgregados);

                // Actualizar la lista visual de horarios
                actualizarListaHorarios();

                // Actualizar la tabla semanal
                actualizarTablaHorarios();

                // Resetear completamente el formulario de horarios
                const horarioDiaElement = document.getElementById('horarioDia');
                const horarioInicioElement = document.getElementById('horarioInicio');
                const horarioFinElement = document.getElementById('horarioFin');

                // Limpiar los campos del formulario
                horarioDiaElement.value = '';
                horarioInicioElement.value = '';
                horarioFinElement.value = '';

                // Deshabilitar los campos de hora ya que no hay día seleccionado
                horarioInicioElement.disabled = true;
                horarioFinElement.disabled = true;

                // Eliminar clases de validación
                horarioDiaElement.classList.remove('is-valid', 'is-invalid');
                horarioInicioElement.classList.remove('is-valid', 'is-invalid');
                horarioFinElement.classList.remove('is-valid', 'is-invalid');

                // Eliminar mensajes de error
                removeFieldHelpMessage(horarioDiaElement);
                removeFieldHelpMessage(horarioInicioElement);
                removeFieldHelpMessage(horarioFinElement);

                // Mostrar notificación de éxito usando toast
                showSuccessToast(`Horario para ${diaNombre} agregado correctamente`);
                console.log("Horario agregado exitosamente");

                // Devolver el foco al selector de días para mejorar la experiencia de usuario
                horarioDiaElement.focus();
            }
            // Función para actualizar la lista visual de horarios
            function actualizarListaHorarios() {
                const lista = document.getElementById('listaHorariosAgregados');
                const mensajeNoHorarios = document.getElementById('mensajeNoHorarios');

                // Mostrar u ocultar el mensaje de "No hay horarios"
                if (horariosAgregados.length === 0) {
                    mensajeNoHorarios.style.display = 'list-item';
                } else {
                    mensajeNoHorarios.style.display = 'none';
                }

                // Limpiar la lista actual (excepto el mensaje de "No hay horarios")
                const itemsExistentes = lista.querySelectorAll('li:not(#mensajeNoHorarios)');
                itemsExistentes.forEach(item => item.remove());

                // Agregar cada horario a la lista
                horariosAgregados.forEach(horario => {
                    const item = document.createElement('li');
                    item.className = 'list-group-item d-flex justify-content-between align-items-center';
                    item.innerHTML = `
            <div>
                <span class="badge bg-primary me-2">${horario.diaNombre}</span>
                <span>${horario.horaInicio} - ${horario.horaFin}</span>
            </div>
            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarHorario(${horario.id})">
                <i class="fas fa-trash-alt"></i>
            </button>
        `;
                    lista.appendChild(item);
                });
            }
            // Función para eliminar un horario de la lista
            window.eliminarHorario = function(id) {
                const horarioIndex = horariosAgregados.findIndex(h => h.id === id);

                if (horarioIndex !== -1) {
                    const horarioBorrado = horariosAgregados[horarioIndex];

                    Swal.fire({
                        title: '¿Eliminar horario?',
                        html: `¿Está seguro de eliminar el horario de <b>${horarioBorrado.diaNombre}</b> (${horarioBorrado.horaInicio} - ${horarioBorrado.horaFin})?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Eliminar el horario del array
                            horariosAgregados.splice(horarioIndex, 1);

                            // Actualizar el campo oculto
                            document.getElementById('horariosAgregados').value = JSON.stringify(horariosAgregados);

                            // Actualizar la interfaz
                            actualizarListaHorarios();
                            actualizarTablaHorarios();

                            // Mostrar notificación
                            showSuccessToast('Horario eliminado correctamente');
                        }
                    });
                }
            };
            // Función para actualizar la tabla semanal de horarios
            function actualizarTablaHorarios() {
                // Limpiar todas las celdas primero
                const diasSemana = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'];
                const periodos = ['AM', 'PM'];

                diasSemana.forEach(dia => {
                    periodos.forEach(periodo => {
                        const celda = document.getElementById(`${dia}-${periodo}`);
                        if (celda) {
                            celda.innerHTML = '';
                            celda.className = ''; // Limpiar clases adicionales
                        }
                    });
                });

                // Agregar los horarios a la tabla, agrupados por día
                diasSemana.forEach(dia => {
                    // Obtener todos los horarios para este día
                    const horariosDia = horariosAgregados.filter(horario => horario.dia === dia);

                    // Si hay horarios para este día
                    if (horariosDia.length > 0) {
                        // Separar horarios por periodos (AM/PM)
                        const horariosAM = horariosDia.filter(h => parseInt(h.horaInicio.split(':')[0], 10) < 12);
                        const horariosPM = horariosDia.filter(h => parseInt(h.horaInicio.split(':')[0], 10) >= 12);

                        // Mostrar horarios AM
                        const celdaAM = document.getElementById(`${dia}-AM`);
                        if (celdaAM && horariosAM.length > 0) {
                            horariosAM.sort((a, b) => a.horaInicio.localeCompare(b.horaInicio));
                            horariosAM.forEach(horario => {
                                const horarioHtml = document.createElement('div');
                                horarioHtml.className = 'horario-item mb-1 p-1 bg-light rounded';
                                horarioHtml.innerHTML = `${formatTime(horario.horaInicio)} - ${formatTime(horario.horaFin)}`;
                                celdaAM.appendChild(horarioHtml);
                            });
                            celdaAM.classList.add('bg-light-success');
                        }

                        // Mostrar horarios PM
                        const celdaPM = document.getElementById(`${dia}-PM`);
                        if (celdaPM && horariosPM.length > 0) {
                            horariosPM.sort((a, b) => a.horaInicio.localeCompare(b.horaInicio));
                            horariosPM.forEach(horario => {
                                const horarioHtml = document.createElement('div');
                                horarioHtml.className = 'horario-item mb-1 p-1 bg-light rounded';
                                horarioHtml.innerHTML = `${formatTime(horario.horaInicio)} - ${formatTime(horario.horaFin)}`;
                                celdaPM.appendChild(horarioHtml);
                            });
                            celdaPM.classList.add('bg-light-success');
                        }
                    }
                });
            }
            // Función para validar la fecha fin según el tipo de contrato
            function validarFechaFin() {
                const tipoContrato = document.getElementById('tipocontrato').value;
                const fechaInicioStr = document.getElementById('fechainicio').value;
                const fechaFinStr = document.getElementById('fechafin').value;
                const fechaFinInput = document.getElementById('fechafin');

                // Si es indefinido, no necesitamos validar
                if (tipoContrato === 'INDEFINIDO') {
                    return true;
                }

                // Si falta fecha inicio, no podemos validar correctamente
                if (!fechaInicioStr) {
                    markFieldAsInvalid(fechaFinInput);
                    addFieldHelpMessage(fechaFinInput, 'Primero debe seleccionar una fecha de inicio');
                    return false;
                }

                // Si no hay fecha fin, es inválido para todos los tipos excepto indefinido
                if (!fechaFinStr) {
                    markFieldAsInvalid(fechaFinInput);
                    addFieldHelpMessage(fechaFinInput, 'La fecha de fin es requerida para este tipo de contrato');
                    return false;
                }

                const fechaInicio = new Date(fechaInicioStr);
                const fechaFin = new Date(fechaFinStr);

                // Permitir que sean el mismo día
                if (fechaFin < fechaInicio) {
                    markFieldAsInvalid(fechaFinInput);
                    addFieldHelpMessage(fechaFinInput, 'La fecha de fin no puede ser anterior a la fecha de inicio');
                    return false;
                }

                // Calcular diferencia en días
                const diferenciaMs = fechaFin.getTime() - fechaInicio.getTime();
                const diferenciaDias = Math.floor(diferenciaMs / (1000 * 60 * 60 * 24));

                // Validaciones específicas por tipo de contrato
                switch (tipoContrato) {
                    case 'PLAZO FIJO':
                        // Validar que sea al menos 1 año (365 días)
                        if (diferenciaDias < 365) {
                            markFieldAsInvalid(fechaFinInput);
                            addFieldHelpMessage(fechaFinInput, 'Para contratos de plazo fijo, la duración mínima debe ser de 1 año');
                            return false;
                        }
                        break;

                    case 'PART TIME': // Temporal
                        // Validar que esté entre 1 y 6 meses (30 a 180 días)
                        if (diferenciaDias < 30 || diferenciaDias > 180) {
                            markFieldAsInvalid(fechaFinInput);
                            addFieldHelpMessage(fechaFinInput, 'Para contratos temporales, la duración debe ser entre 1 y 6 meses');
                            return false;
                        }
                        break;

                    case 'LOCACION': // Eventual
                        // Validar que esté entre 1 y 30 días
                        if (diferenciaDias < 1 || diferenciaDias > 30) {
                            markFieldAsInvalid(fechaFinInput);
                            addFieldHelpMessage(fechaFinInput, 'Para contratos eventuales, la duración debe ser entre 1 y 30 días');
                            return false;
                        }
                        break;
                }

                // Si llegamos aquí, la fecha es válida
                markFieldAsValid(fechaFinInput);
                removeFieldHelpMessage(fechaFinInput);
                return true;
            }

            // Función para la validación completa de contrato (para el botón Siguiente)
            function validateContratoInfo() {
                const tipoContratoInput = document.getElementById('tipocontrato');
                const fechaInicioInput = document.getElementById('fechainicio');
                let isValid = true;

                // Validar tipo de contrato
                if (!tipoContratoInput.value) {
                    markFieldAsInvalid(tipoContratoInput);
                    addFieldHelpMessage(tipoContratoInput, 'Debe seleccionar un tipo de contrato');
                    isValid = false;
                } else {
                    markFieldAsValid(tipoContratoInput);
                    removeFieldHelpMessage(tipoContratoInput);
                }

                // Validar fecha inicio
                if (!fechaInicioInput.value) {
                    markFieldAsInvalid(fechaInicioInput);
                    addFieldHelpMessage(fechaInicioInput, 'La fecha de inicio es requerida');
                    isValid = false;
                } else {
                    markFieldAsValid(fechaInicioInput);
                    removeFieldHelpMessage(fechaInicioInput);
                }

                // Validar fecha fin (solo si no es indefinido)
                if (tipoContratoInput.value !== 'INDEFINIDO') {
                    if (!validarFechaFin()) {
                        isValid = false;
                    }
                }

                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Información de contrato incompleta',
                        text: 'Por favor, complete correctamente todos los campos obligatorios.'
                    });

                    // Hacer scroll al primer campo inválido
                    const firstInvalidField = document.querySelector('#contratoCard .is-invalid');
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

            // Función para validar todos los horarios seleccionados
            // Función para validar los horarios al pasar a la siguiente pestaña
            function validateHorarioInfo() {
                // Verificar que se haya agregado al menos un horario
                if (horariosAgregados.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Horario requerido',
                        text: 'Debe agregar al menos un horario de atención'
                    });
                    return false;
                }

                return true;
            }

            // Función para validar las credenciales
            function validateCredenciales() {
                const requiredFields = ['nomuser', 'passuser'];
                let isValid = true;
                let firstInvalidField = null;

                requiredFields.forEach(field => {
                    const input = document.getElementById(field);
                    if (!input || !input.value.trim()) {
                        markFieldAsInvalid(input);
                        addFieldHelpMessage(input, 'Este campo es obligatorio');
                        isValid = false;
                        if (!firstInvalidField) firstInvalidField = input;
                    } else {
                        markFieldAsValid(input);
                        removeFieldHelpMessage(input);
                    }
                });

                // Validación adicional para nombre de usuario
                const nomuser = document.getElementById('nomuser');
                if (nomuser && nomuser.value && !/^[a-zA-Z0-9_]{4,20}$/.test(nomuser.value)) {
                    markFieldAsInvalid(nomuser);
                    addFieldHelpMessage(nomuser, 'El nombre de usuario debe tener entre 4 y 20 caracteres alfanuméricos');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = nomuser;
                }

                // Validación adicional para contraseña
                const passuser = document.getElementById('passuser');
                if (passuser && passuser.value && passuser.value.length < 6) {
                    markFieldAsInvalid(passuser);
                    addFieldHelpMessage(passuser, 'La contraseña debe tener al menos 6 caracteres');
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = passuser;
                }

                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Credenciales incompletas',
                        text: 'Por favor, complete correctamente las credenciales de acceso.'
                    });

                    // Hacer scroll al primer campo inválido
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

            // Función para validar todos los pasos
            async function validateAllSteps() {
                // Verificar campos vacíos
                const camposVacios = verificarCamposVaciosAntesDeEnviar();

                if (camposVacios.length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Campos incompletos',
                        html: `Por favor complete los siguientes campos:<br>
            <ul style="text-align:left;display:inline-block">
                ${camposVacios.map(campo => `<li>${campo}</li>`).join('')}
            </ul>`
                    });
                    return false;
                }

                // Validar cada sección de forma asíncrona
                const personalInfoValid = await validatePersonalInfo();
                if (!personalInfoValid) {
                    showTab('personalInfo');
                    updateProgressBar(0);
                    return false;
                }

                if (!validateProfessionalInfo()) {
                    showTab('personalInfo');
                    updateProgressBar(0);
                    return false;
                }

                if (!validateContratoInfo() || !validateHorarioInfo()) {
                    showTab('contrato');
                    updateProgressBar(50);
                    return false;
                }

                // Verificar si las credenciales ya están ingresadas
                const nomuser = document.getElementById('nomuser').value;
                const passuser = document.getElementById('passuser').value;

                if (!nomuser || !passuser || passuser.length < 6 || !/^[a-zA-Z0-9_]{4,20}$/.test(nomuser)) {
                    // Mostrar alerta para las credenciales
                    mostrarAlertaCredenciales();
                    return false;
                }

                return true;
            }

            function asegurarPrecioAtencion() {
                const especialidadSelect = document.getElementById('especialidad');
                let precioAtencionInput = document.getElementById('precioatencion');

                // Si no existe el campo precioatencion, crearlo
                if (!precioAtencionInput) {
                    console.log("Creando campo oculto para precio de atención");
                    precioAtencionInput = document.createElement('input');
                    precioAtencionInput.type = 'hidden';
                    precioAtencionInput.id = 'precioatencion';
                    precioAtencionInput.name = 'precioatencion';
                    precioAtencionInput.value = '0';
                    document.getElementById('doctorRegistrationForm').appendChild(precioAtencionInput);
                }

                // Si hay una especialidad seleccionada, cargar su precio
                if (especialidadSelect && especialidadSelect.value) {
                    console.log("Cargando precio para especialidad ID:", especialidadSelect.value);

                    // Hacer la solicitud para obtener el precio de la especialidad
                    return fetch(`../../../controllers/especialidad.controller.php?op=obtener&id=${especialidadSelect.value}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`Error en la respuesta del servidor: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log("Datos recibidos de especialidad:", data);
                            if (data.status && data.data && data.data.precioatencion) {
                                const precio = parseFloat(data.data.precioatencion);
                                if (precio > 0) {
                                    precioAtencionInput.value = precio.toString();
                                    console.log("Precio de atención establecido:", precio);
                                    return true;
                                } else {
                                    console.error("El precio recibido no es mayor que cero:", precio);
                                    return false;
                                }
                            } else {
                                console.error("No se pudo obtener el precio de la especialidad:", data);
                                return false;
                            }
                        })
                        .catch(error => {
                            console.error("Error al obtener precio de especialidad:", error);
                            return false;
                        });
                } else {
                    console.warn("No hay especialidad seleccionada para cargar precio");
                    return Promise.resolve(false);
                }
            }
            // Función para validar formato de email
            function validateEmail(email) {
                // Expresión regular más completa para validar correos electrónicos
                const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                return re.test(String(email).toLowerCase());
            }


            // Función para marcar campos como válidos
            function markFieldAsValid(field) {
                if (field) {
                    field.classList.add('is-valid');
                    field.classList.remove('is-invalid', 'border-danger');
                    console.log(`Campo marcado como válido: ${field.id}`);
                } else {
                    console.error("Intento de marcar como válido un campo que no existe");
                }
            }


            // Función para marcar campos como inválidos
            function markFieldAsInvalid(field) {
                if (field) {
                    field.classList.add('is-invalid');
                    field.classList.remove('is-valid');
                    console.log(`Campo marcado como inválido: ${field.id}`);
                } else {
                    console.error("Intento de marcar como inválido un campo que no existe");
                }
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
                if (typeof Swal !== 'undefined') {
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
                } else {
                    console.log('Éxito:', message);
                }
            }

            // Función para mostrar toast de error
            function showErrorToast(message) {
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 5000,
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
                } else {
                    console.error('Error de validación:', message);
                    alert(message);
                }
            }

            // Estas funciones deberían estar definidas globalmente si no están ya disponibles
            window.markFieldAsValid = window.markFieldAsValid || function(field) {
                if (field) {
                    field.classList.add('is-valid');
                    field.classList.remove('is-invalid');
                }
            };

            window.markFieldAsInvalid = window.markFieldAsInvalid || function(field) {
                if (field) {
                    field.classList.add('is-invalid');
                    field.classList.remove('is-valid');
                }
            };

            window.addFieldHelpMessage = window.addFieldHelpMessage || function(field, message) {
                if (!field) return;

                // Eliminar mensaje previo si existe
                window.removeFieldHelpMessage(field);

                // Crear nuevo mensaje
                const helpDiv = document.createElement('div');
                helpDiv.className = 'invalid-feedback';
                helpDiv.id = `help-${field.id}`;
                helpDiv.textContent = message;

                // Insertar después del campo
                if (field.parentNode) {
                    field.parentNode.appendChild(helpDiv);
                }
            };

            window.removeFieldHelpMessage = window.removeFieldHelpMessage || function(field) {
                if (!field) return;

                const helpDiv = document.getElementById(`help-${field.id}`);
                if (helpDiv && helpDiv.parentNode) {
                    helpDiv.parentNode.removeChild(helpDiv);
                }
            };

            // Función para cargar las especialidades desde el servidor
            function cargarEspecialidades(seleccionarId = null) {
                // Verificar que la URL sea correcta
                fetch('../../../controllers/colaborador.controller.php?op=especialidades')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log("Datos de especialidades recibidos:", data); // Para depuración

                        if (data.status) {
                            const selectEspecialidad = document.getElementById('especialidad');
                            if (selectEspecialidad) {
                                selectEspecialidad.innerHTML = '<option value="">Seleccione...</option>';

                                if (data.data && Array.isArray(data.data)) {
                                    data.data.forEach(especialidad => {
                                        const option = document.createElement('option');
                                        option.value = especialidad.idespecialidad;
                                        option.textContent = especialidad.especialidad;
                                        selectEspecialidad.appendChild(option);

                                        // Si hay un ID para seleccionar automáticamente
                                        if (seleccionarId && especialidad.idespecialidad == seleccionarId) {
                                            option.selected = true;
                                            // Cargar el precio automáticamente
                                            cargarPrecioEspecialidad(seleccionarId);
                                        }
                                    });
                                } else {
                                    console.error('El formato de datos de especialidades no es válido:', data);
                                }
                            }
                        } else {
                            console.error('Error al obtener especialidades:', data.mensaje || 'Error desconocido');
                            showErrorToast('No se pudieron cargar las especialidades');
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar especialidades:', error);
                        showErrorToast('No se pudieron cargar las especialidades: ' + error.message);
                    });
            }

            // Función específica para validar el campo de email
            function validateEmailField(input) {
                // Limpiar cualquier estilo o mensaje previo
                input.classList.remove('is-valid', 'is-invalid', 'border-danger');
                removeFieldHelpMessage(input);

                if (!input.value.trim()) {
                    // Si está vacío
                    markFieldAsInvalid(input);
                    addFieldHelpMessage(input, 'El correo electrónico es obligatorio');
                    return false;
                } else if (!validateEmail(input.value)) {
                    // Si el formato es incorrecto
                    markFieldAsInvalid(input);
                    addFieldHelpMessage(input, 'Ingrese un correo electrónico válido');
                    return false;
                } else {
                    // Si es válido
                    markFieldAsValid(input);
                    removeFieldHelpMessage(input);
                    // Asegurarse de que no tenga el borde rojo
                    input.classList.remove('border-danger');
                    return true;
                }
            }

            // Función para configurar validaciones de campos
            function setupFieldValidations() {
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
                        }

                        // Verificar si todos los campos están completos
                        checkPersonalFields();
                    });
                }
                // Validación específica para fecha de nacimiento
                const fechaNacimientoInput = document.getElementById('fechanacimiento');
                if (fechaNacimientoInput) {
                    fechaNacimientoInput.addEventListener('change', function() {
                        validateFechaNacimientoField(this);
                    });

                    // También validar en caso de input para capturar cambios manuales
                    fechaNacimientoInput.addEventListener('input', function() {
                        validateFechaNacimientoField(this);
                    });
                }

                // Agregar validación a todos los campos requeridos (sin incluir fecha de nacimiento)
                const requiredFields = [
                    'apellidos', 'nombres', 'genero',
                    'email', 'direccion'
                ];

                requiredFields.forEach(field => {
                    const input = document.getElementById(field);
                    if (input) {
                        input.addEventListener('blur', function() {
                            if (this.value.trim()) {
                                markFieldAsValid(this);
                                removeFieldHelpMessage(this);
                            } else {
                                markFieldAsInvalid(this);
                                addFieldHelpMessage(this, 'Este campo es obligatorio');
                            }

                            // Verificar campos personales
                            checkPersonalFields();
                        });

                        // Para campos de texto, validar al cambiar
                        if (input.type === 'text' || input.tagName === 'SELECT' || input.type === 'date' || input.type === 'email') {
                            input.addEventListener('input', function() {
                                if (this.value.trim()) {
                                    markFieldAsValid(this);
                                    removeFieldHelpMessage(this);
                                } else {
                                    markFieldAsInvalid(this);
                                    addFieldHelpMessage(this, 'Este campo es obligatorio');
                                }

                                // Verificar campos personales
                                checkPersonalFields();
                            });
                        }
                    }
                });
                const emailInput = document.getElementById('email');
                if (emailInput) {
                    emailInput.addEventListener('blur', function() {
                        validateEmailField(this);
                        // Verificar campos personales
                        checkPersonalFields();
                    });

                    emailInput.addEventListener('input', function() {
                        validateEmailField(this);
                        // Verificar campos personales
                        checkPersonalFields();
                    });
                }


                // Validar email
                const email = document.getElementById('email');
                if (email) {
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
                            markFieldAsInvalid(this);
                            addFieldHelpMessage(this, 'Este campo es obligatorio');
                        }

                        // Verificar si todos los campos están completos
                        checkPersonalFields();
                    });
                }

                // Setup para cambio de horarios
                const diasSemana = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];

                diasSemana.forEach(dia => {
                    // Agregar listeners para validación en tiempo real
                    const horaFin1 = document.getElementById(`horaFin${dia}1`);
                    const horaInicio2 = document.getElementById(`horaInicio${dia}2`);

                    if (horaFin1 && horaInicio2) {
                        horaFin1.addEventListener('input', function() {
                            const modalidad = document.getElementById(`modalidad${dia}`).value;
                            if (modalidad === 'tiempoCompleto') {
                                validarHorarioSecuencial(dia);
                            }
                        });

                        horaInicio2.addEventListener('input', function() {
                            validarHorarioSecuencial(dia);
                        });
                    }
                });

            }
            // 3. Agregar una función específica para validar el campo de fecha de nacimiento
            async function validateFechaNacimientoField(input) {
                if (!input.value.trim()) {
                    markFieldAsInvalid(input);
                    addFieldHelpMessage(input, 'Este campo es obligatorio');
                    return false;
                }

                console.log("Validando campo de fecha:", input.value);
                const validation = validateFechaNacimiento(input.value);
                console.log("Resultado de validación:", validation);

                if (validation.isValid) {
                    // Si necesita confirmación y no ha sido confirmado previamente
                    if (validation.needsConfirmation && input.getAttribute('data-age-confirmed') !== 'true') {
                        // Devolver una promesa para manejar la confirmación asíncrona
                        return new Promise((resolve) => {
                            Swal.fire({
                                title: 'Confirmar registro',
                                html: validation.message,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Sí, continuar',
                                cancelButtonText: 'No, usar otra fecha'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    markFieldAsValid(input);
                                    removeFieldHelpMessage(input);
                                    input.setAttribute('data-age-confirmed', 'true');
                                    showSuccessToast('Edad confirmada. Puede continuar con el registro.');
                                    resolve(true);
                                } else {
                                    markFieldAsInvalid(input);
                                    addFieldHelpMessage(input, 'Por favor, seleccione otra fecha de nacimiento');
                                    resolve(false);
                                }
                            });
                        });
                    }

                    // Si ya fue confirmado o no necesita confirmación
                    markFieldAsValid(input);
                    removeFieldHelpMessage(input);
                    showSuccessToast('Fecha de nacimiento válida');
                    return true;
                } else {
                    markFieldAsInvalid(input);
                    addFieldHelpMessage(input, validation.message);
                    showErrorToast(validation.message);
                    return false;
                }
            }
            // Agregar eventos para actualizar el nombre del doctor cuando cambia el nombre o apellido
            const apellidosInput = document.getElementById('apellidos');
            const nombresInput = document.getElementById('nombres');

            if (apellidosInput && nombresInput) {
                const updateDoctorName = function() {
                    const apellidos = apellidosInput.value.trim();
                    const nombres = nombresInput.value.trim();

                    if (apellidos && nombres && currentTab !== 'personalInfo') {
                        document.getElementById('doctorNameText').textContent = `${nombres} ${apellidos}`;
                        document.getElementById('doctorNameDisplay').classList.remove('d-none');
                    }
                };

                apellidosInput.addEventListener('input', updateDoctorName);
                nombresInput.addEventListener('input', updateDoctorName);
            }
            // Función para cargar los datos en la pantalla de confirmación
            function loadConfirmationData() {
                // Datos personales
                const datosPersonales = {
                    tipodoc: document.getElementById('tipodoc').value,
                    nrodoc: document.getElementById('nrodoc').value,
                    apellidos: document.getElementById('apellidos').value,
                    nombres: document.getElementById('nombres').value,
                    fechanacimiento: document.getElementById('fechanacimiento').value,
                    genero: document.getElementById('genero').value === 'M' ? 'Masculino' : (document.getElementById('genero').value === 'F' ? 'Femenino' : 'Otro'),
                    telefono: document.getElementById('telefono').value,
                    email: document.getElementById('email').value,
                    direccion: document.getElementById('direccion').value
                };

                // Datos profesionales
                const especialidadSelect = document.getElementById('especialidad');
                const especialidadText = especialidadSelect ? especialidadSelect.options[especialidadSelect.selectedIndex].text : '';
                const datosProfesionales = {
                    especialidad: especialidadText,
                    precioatencion: ''
                };

                // Datos de contrato
                const tipocontratoSelect = document.getElementById('tipocontrato');
                const tipocontratoText = tipocontratoSelect ? tipocontratoSelect.options[tipocontratoSelect.selectedIndex].text : '';
                const datosContrato = {
                    tipocontrato: tipocontratoText,
                    fechainicio: document.getElementById('fechainicio') ? document.getElementById('fechainicio').value : '',
                    fechafin: document.getElementById('fechafin') && document.getElementById('fechafin').value ? document.getElementById('fechafin').value : 'No especificado'
                };

                // Datos de horarios para visualización
                const horariosData = horariosAgregados.map(horario => ({
                    dia: horario.diaNombre,
                    horaInicio: horario.horaInicio,
                    horaFin: horario.horaFin
                }));

                // Datos de credenciales - USAR EL MISMO EMAIL
                const datosCredenciales = {
                    nomuser: document.getElementById('nomuser').value || datosPersonales.email
                };

                // Generar HTML de confirmación - MOSTRAR EL MISMO CORREO EN AMBOS LUGARES
                let html = `
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i> Información Personal</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Tipo de Documento:</strong> ${datosPersonales.tipodoc}</p>
                            <p><strong>Número de Documento:</strong> ${datosPersonales.nrodoc}</p>
                            <p><strong>Apellidos:</strong> ${datosPersonales.apellidos}</p>
                            <p><strong>Nombres:</strong> ${datosPersonales.nombres}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Fecha de Nacimiento:</strong> ${formatDate(datosPersonales.fechanacimiento)}</p>
                            <p><strong>Género:</strong> ${datosPersonales.genero}</p>
                            <p><strong>Teléfono:</strong> ${datosPersonales.telefono}</p>
                            <p><strong>Email:</strong> ${datosPersonales.email}</p>
                            <p><strong>Dirección:</strong> ${datosPersonales.direccion}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-user-md me-2"></i> Información Profesional</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p><strong>Especialidad:</strong> ${datosProfesionales.especialidad}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i> Información de Contrato</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Tipo de Contrato:</strong> ${datosContrato.tipocontrato}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Fecha de Inicio:</strong> ${formatDate(datosContrato.fechainicio)}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Fecha de Fin:</strong> ${datosContrato.fechafin === 'No especificado' ? datosContrato.fechafin : formatDate(datosContrato.fechafin)}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Horario de Atención</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Día</th>
                                    <th>Hora Inicio</th>
                                    <th>Hora Fin</th>
                                </tr>
                            </thead>
                            <tbody>`;

                if (horariosData.length > 0) {
                    horariosData.forEach(horario => {
                        html += `
                <tr>
                    <td>${horario.dia}</td>
                    <td>${horario.horaInicio}</td>
                    <td>${horario.horaFin}</td>
                </tr>`;
                    });
                } else {
                    html += `
                <tr>
                    <td colspan="3" class="text-center">No se ha configurado ningún horario</td>
                </tr>`;
                }

                html += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-key me-2"></i> Credenciales de Acceso</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Correo Electrónico:</strong> ${datosCredenciales.nomuser}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Contraseña:</strong> ********</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>`;

                const resumenRegistro = document.getElementById('resumenRegistro');
                if (resumenRegistro) {
                    resumenRegistro.innerHTML = html;
                }
            }
            // Función para buscar doctor por documento - Versión mejorada
            function buscarDoctorPorDocumento() {
                console.log("Función buscarDoctorPorDocumento iniciada");

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
            <p class="mt-2">Por favor espere mientras se busca la persona.</p>
        `,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Simular tiempo de carga mínimo para mostrar la animación (al menos 1 segundo)
                setTimeout(() => {
                    // *** PASO 1: Verificar si ya existe como doctor ***
                    const formData = new FormData();
                    formData.append('nrodoc', nrodoc);

                    fetch('../../../controllers/doctor.controller.php?op=verificar', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log("Respuesta verificación doctor:", data);

                            if (data.existe) {
                                // Ya existe como doctor - Mostrar mensaje y bloquear campos
                                Swal.close();

                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Doctor ya registrado',
                                    text: 'Este documento ya está registrado como doctor en el sistema.',
                                    showCancelButton: true,
                                    confirmButtonText: 'Ver detalles',
                                    cancelButtonText: 'Intentar otro documento'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // Redirigir a la vista de detalles del doctor
                                        window.location.href = '../ListarDoctor/listarDoctor.php';
                                    } else {
                                        // Limpiar el campo de documento para intentar con otro
                                        document.getElementById('nrodoc').value = '';
                                        document.getElementById('nrodoc').focus();
                                    }
                                });

                                // Mantener los campos bloqueados
                                bloquearCampos();
                                limpiarCampos(); // Limpiar campos al no encontrar datos

                                // Mostrar mensaje en toast
                                showErrorToast('Este documento ya está registrado como doctor');
                            } else {
                                // No existe como doctor, verificar si existe como persona/paciente
                                // *** PASO 2: Verificar si existe como persona en el sistema ***
                                fetch(`../../../controllers/persona.controller.php?op=buscar_por_documento&nrodoc=${nrodoc}`)
                                    .then(response => {
                                        // Verificar si la respuesta es válida
                                        if (!response.ok) {
                                            throw new Error('Error en la respuesta del servidor: ' + response.status);
                                        }
                                        return response.json();
                                    })
                                    .then(personaData => {
                                        Swal.close();
                                        console.log("Respuesta búsqueda persona:", personaData);

                                        // CAMBIO IMPORTANTE: Modificar la lógica para manejar diferentes respuestas
                                        if (personaData.status && personaData.persona) {
                                            // CORRECCIÓN: Aquí es donde la persona existe en el sistema
                                            // Verificar edad de la persona
                                            if (personaData.persona.fechanacimiento) {
                                                const fechaNacimiento = new Date(personaData.persona.fechanacimiento);
                                                const hoy = new Date();
                                                let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
                                                const meses = hoy.getMonth() - fechaNacimiento.getMonth();

                                                if (meses < 0 || (meses === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
                                                    edad--;
                                                }

                                                // Si es menor de 18 años, no permitir el registro como doctor
                                                if (edad < 18) {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'No se puede registrar',
                                                        text: 'La persona es menor de edad. No puede ser registrado como doctor.',
                                                        confirmButtonText: 'Entendido'
                                                    });

                                                    // Mostrar alerta en la esquina
                                                    showErrorToast('No se puede registrar como doctor: La persona es menor de edad (debe tener al menos 18 años)');

                                                    // Bloquear campos
                                                    bloquearCampos();
                                                    return;
                                                }
                                            }

                                            // Mostrar mensaje de éxito y cargar datos
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Persona encontrada',
                                                text: 'Se han cargado los datos de la persona automáticamente.',
                                                confirmButtonText: 'Continuar'
                                            });

                                            // Cargar datos de la persona en el formulario
                                            cargarDatosPersona(personaData.persona);

                                            // Bloquear campos de datos personales pero habilitar los profesionales
                                            bloquearCamposConDatos();

                                            // Habilitar botón siguiente para continuar con el registro como doctor
                                            const btnSiguiente = document.getElementById("btnSiguiente");
                                            if (btnSiguiente) {
                                                btnSiguiente.disabled = false;
                                                btnSiguiente.classList.remove("disabled");
                                            }

                                            // Notificación lateral
                                            showSuccessToast('Datos personales cargados correctamente. Complete la información profesional.');
                                        } else {
                                            // No existe en el sistema - Permitir el registro completo
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Documento disponible',
                                                text: 'Este documento no está registrado en el sistema. Puede continuar con el registro completo.',
                                                confirmButtonText: 'Continuar'
                                            });

                                            // Desbloquear los campos para el registro
                                            desbloquearCampos();
                                            limpiarCampos(); // Limpiar campos al no encontrar datos

                                            showSuccessToast('Documento disponible, puede continuar con el registro');
                                        }
                                    })
                                    .catch(error => {
                                        Swal.close();
                                        console.error('Error al buscar persona:', error);

                                        // En caso de error en la búsqueda, intentar registro normal
                                        Swal.fire({
                                            icon: 'info',
                                            title: 'Continuar con el registro',
                                            text: 'No se encontraron datos asociados a este documento. Puede proceder con el registro.',
                                            confirmButtonText: 'Continuar'
                                        });

                                        // Desbloquear los campos para el registro
                                        desbloquearCampos();
                                        limpiarCampos(); // Limpiar campos al no encontrar datos

                                        showSuccessToast('Complete los datos para el nuevo registro');
                                    });
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            console.error('Error al verificar doctor:', error);

                            Swal.fire({
                                icon: 'info',
                                title: 'Continuar con el registro',
                                text: 'No se pudo verificar si ya existe como doctor. Puede continuar con el registro.',
                                confirmButtonText: 'Continuar'
                            });

                            // Desbloquear los campos para el registro
                            desbloquearCampos();
                            limpiarCampos(); // Limpiar campos al no encontrar datos
                        });
                }, 800);
            }
            // Función para verificar y habilitar campos incompletos
            function verificarCamposIncompletos(persona) {
                console.log("Verificando campos incompletos para:", persona);

                // Definir los campos obligatorios para doctor
                const camposObligatorios = [
                    'apellidos',
                    'nombres',
                    'fechanacimiento',
                    'genero',
                    'telefono',
                    'email',
                    'direccion'
                ];

                // Verificar cada campo
                camposObligatorios.forEach(campo => {
                    const input = document.getElementById(campo);

                    // Si el campo existe en el formulario
                    if (input) {
                        // Verificar si está vacío o es null/undefined en los datos de la persona
                        if (!persona[campo] || persona[campo].trim() === '') {
                            console.log(`Campo incompleto detectado: ${campo}`);

                            // Habilitar el campo para edición
                            input.disabled = false;

                            // Marcar visualmente como requerido
                            input.classList.add('border-danger');

                            // Agregar mensaje indicando que debe completarse
                            addFieldHelpMessage(input, 'Complete este campo obligatorio');

                            // Marcar como inválido
                            markFieldAsInvalid(input);
                        } else {
                            // Si tiene datos, mantenerlo deshabilitado pero marcarlo como válido
                            input.disabled = true;
                            markFieldAsValid(input);
                        }
                    }
                });
                const emailInput = document.getElementById('email');
                if (emailInput) {
                    if (!persona.email || persona.email.trim() === '') {
                        // Si no tiene email, habilitar para edición
                        emailInput.disabled = false;
                        emailInput.classList.add('border-danger');
                        addFieldHelpMessage(emailInput, 'Complete este campo obligatorio');
                        markFieldAsInvalid(emailInput);
                    } else if (!validateEmail(persona.email)) {
                        // Si tiene email pero es inválido
                        emailInput.disabled = false;
                        emailInput.classList.add('border-danger');
                        addFieldHelpMessage(emailInput, 'El formato del correo es incorrecto');
                        markFieldAsInvalid(emailInput);
                    } else {
                        // Si el email es válido
                        emailInput.disabled = true;
                        markFieldAsValid(emailInput);
                        removeFieldHelpMessage(emailInput);
                        // Asegurar que no tenga clase border-danger
                        emailInput.classList.remove('border-danger');
                    }
                }

                // Si hay al menos un campo habilitado, mostrar mensaje informativo
                const camposHabilitados = document.querySelectorAll('input.border-danger, select.border-danger');
                if (camposHabilitados.length > 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Información incompleta',
                        text: 'Se han habilitado algunos campos que requieren completarse para el registro del doctor.',
                        confirmButtonText: 'Entendido'
                    });

                    // Hacer scroll al primer campo incompleto
                    camposHabilitados[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }

            // Función para cargar los datos de la persona en el formulario
            function cargarDatosPersona(persona) {
                console.log("Cargando datos de persona:", persona);

                // Verificar que el objeto persona contenga datos
                if (!persona) {
                    console.error("Error: No hay datos de persona para cargar");
                    return;
                }

                // Asegurar que todos los valores existan para evitar errores
                const datos = {
                    apellidos: persona.apellidos || '',
                    nombres: persona.nombres || '',
                    fechanacimiento: persona.fechanacimiento || '',
                    genero: persona.genero || '',
                    telefono: persona.telefono || '',
                    email: persona.email || '',
                    direccion: persona.direccion || ''
                };

                // Log para depuración
                console.log("Datos a cargar:", datos);

                // Cargar datos básicos en los campos correspondientes
                document.getElementById('apellidos').value = datos.apellidos;
                document.getElementById('nombres').value = datos.nombres;
                document.getElementById('fechanacimiento').value = datos.fechanacimiento;
                document.getElementById('genero').value = datos.genero;
                document.getElementById('telefono').value = datos.telefono;
                document.getElementById('email').value = datos.email;
                document.getElementById('direccion').value = datos.direccion;

                // Aplicar clases de validación para campos llenos
                Object.keys(datos).forEach(campo => {
                    const input = document.getElementById(campo);
                    if (input && datos[campo]) {
                        // Marcar campos con datos como válidos
                        markFieldAsValid(input);
                        removeFieldHelpMessage(input);
                    } else if (input) {
                        // Marcar campos vacíos como inválidos y con borde rojo
                        markFieldAsInvalid(input);
                        input.classList.add('border-danger');
                        addFieldHelpMessage(input, 'Este campo es obligatorio');
                    }
                });

                // Actualizar el nombre del doctor si no estamos en la pestaña de información personal
                if (currentTab !== 'personalInfo' && datos.nombres && datos.apellidos) {
                    document.getElementById('doctorNameText').textContent = `${datos.nombres} ${datos.apellidos}`;
                    document.getElementById('doctorNameDisplay').classList.remove('d-none');
                }

                // Validación específica para el correo electrónico
                const emailInput = document.getElementById('email');
                if (emailInput && datos.email) {
                    if (validateEmail(datos.email)) {
                        markFieldAsValid(emailInput);
                        removeFieldHelpMessage(emailInput);
                        emailInput.classList.remove('border-danger');
                    } else {
                        // Si el formato del correo es incorrecto
                        markFieldAsInvalid(emailInput);
                        addFieldHelpMessage(emailInput, 'El formato del correo es incorrecto');
                        emailInput.classList.add('border-danger');
                    }
                }

                console.log("Datos cargados en el formulario");
            }


            // Función para bloquear campos pero mostrando los datos (cuando se encuentra una persona)
            function bloquearCamposConDatos() {
                console.log("Bloqueando campos con datos...");

                const camposPersonales = [
                    'apellidos', 'nombres', 'fechanacimiento', 'genero', 'telefono', 'email', 'direccion'
                ];

                // Primero, revisar cada campo y ver si tiene datos
                camposPersonales.forEach(campo => {
                    const input = document.getElementById(campo);
                    if (!input) {
                        console.error(`Campo ${campo} no encontrado en el DOM`);
                        return;
                    }

                    // Verificar si el campo tiene valor
                    if (input.value && input.value.trim() !== '') {
                        // Si tiene valor, bloquearlo y marcarlo como válido
                        input.disabled = true;
                        markFieldAsValid(input);
                        removeFieldHelpMessage(input);
                        input.classList.remove('border-danger');
                        console.log(`Campo ${campo} bloqueado con valor: ${input.value}`);
                    } else {
                        // Si no tiene valor, dejarlo habilitado pero marcarlo como requerido
                        input.disabled = false;
                        markFieldAsInvalid(input);
                        input.classList.add('border-danger');
                        addFieldHelpMessage(input, 'Este campo es obligatorio');
                        console.log(`Campo ${campo} habilitado (vacío/requerido)`);
                    }
                });

                // Siempre habilitar campos profesionales
                document.getElementById('especialidad').disabled = false;

                // Habilitar botones relacionados con la especialidad
                const btnAgregarEspecialidad = document.getElementById('btnAgregarEspecialidad');
                if (btnAgregarEspecialidad) {
                    btnAgregarEspecialidad.disabled = false;
                }

                // Habilitar el botón siguiente
                const btnSiguiente = document.getElementById("btnSiguiente");
                if (btnSiguiente) {
                    btnSiguiente.disabled = false;
                    btnSiguiente.classList.remove("disabled");
                }

                console.log("Campos bloqueados/desbloqueados según corresponda");
            }
            // Función para limpiar todos los campos del formulario
            function limpiarCampos() {
                const camposALimpiar = [
                    'apellidos', 'nombres', 'fechanacimiento', 'genero', 'telefono', 'email', 'direccion'
                ];

                camposALimpiar.forEach(campo => {
                    const input = document.getElementById(campo);
                    if (input) {
                        input.value = '';
                        input.classList.remove('is-valid', 'is-invalid');
                        removeFieldHelpMessage(input);
                    }
                });

                // Restablecer valores por defecto
                document.getElementById("genero").value = "M";

                console.log("Campos limpiados correctamente");
            }

            // Función para bloquear todos los campos excepto tipo y número de documento
            function bloquearCampos() {
                const camposABloquear = [
                    'apellidos', 'nombres', 'fechanacimiento',
                    'genero', 'telefono', 'email', 'direccion',
                    'especialidad' // No incluir 'precioatencion' ya que es un campo oculto
                ];

                camposABloquear.forEach(campo => {
                    const input = document.getElementById(campo);
                    if (input) {
                        input.disabled = true;
                        input.classList.remove('is-valid', 'is-invalid');
                        removeFieldHelpMessage(input);
                    }
                });

                // Deshabilitar botones relacionados con la especialidad
                document.getElementById('btnAgregarEspecialidad').disabled = true;

                // Deshabilitar el botón siguiente
                const btnSiguiente = document.getElementById("btnSiguiente");
                if (btnSiguiente) {
                    btnSiguiente.disabled = true;
                    btnSiguiente.classList.add("disabled");
                }
            }

            // Función para desbloquear todos los campos
            function desbloquearCampos() {
                const camposADesbloquear = [
                    'apellidos', 'nombres', 'fechanacimiento',
                    'genero', 'telefono', 'email', 'direccion',
                    'especialidad' // No incluir 'precioatencion'
                ];

                camposADesbloquear.forEach(campo => {
                    const input = document.getElementById(campo);
                    if (input) {
                        input.disabled = false;
                        // Agregar evento para verificar campos en tiempo real
                        input.addEventListener('input', checkPersonalFields);
                        input.addEventListener('change', checkPersonalFields);
                    }
                });

                // El campo oculto de precio no necesita ser manipulado aquí

                // Habilitar botones relacionados con la especialidad
                document.getElementById('btnAgregarEspecialidad').disabled = false;

                // Ya no necesitamos habilitar btnEditarPrecio porque lo hemos eliminado
                // document.getElementById('btnEditarPrecio').disabled = false;

                // Habilitar el botón siguiente
                const btnSiguiente = document.getElementById("btnSiguiente");
                if (btnSiguiente) {
                    btnSiguiente.disabled = false;
                    btnSiguiente.classList.remove("disabled");
                }
            }
            // Reemplazar la función guardarDoctor() existente con esta versión:
            async function guardarDoctor() {
                console.log("Función guardarDoctor() iniciada");

                // Verificar precio antes de continuar
                const precioInput = document.getElementById('precioatencion');
                if (!precioInput || !precioInput.value || parseFloat(precioInput.value) <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al registrar doctor',
                        text: 'El precio de atención debe ser mayor a cero'
                    });
                    return;
                }

                // Si estamos en la pantalla de confirmación, omitir la validación completa 
                // y proceder directamente con el registro
                if (currentTab === 'confirmacion') {
                    console.log("En pantalla de confirmación, procediendo con el registro");

                    // Realizar una última validación rápida
                    if (!document.getElementById('especialidad').value ||
                        !document.getElementById('tipocontrato').value ||
                        !document.getElementById('fechainicio').value ||
                        !document.getElementById('nomuser').value ||
                        !document.getElementById('passuser').value) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Información incompleta',
                            text: 'Por favor, asegúrese de completar todos los campos requeridos antes de guardar.'
                        });
                        return;
                    }

                    // Mostrar alerta de carga estilo modal centrado como en la imagen
                    Swal.fire({
                        title: 'Guardando...',
                        html: 'Por favor espere mientras se registra el doctor.',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Proceder directamente con el registro
                    registrarDoctorCompleto();
                    return;
                }

                // Para cualquier otra pestaña, mantener la validación completa
                const isValid = await validateAllSteps();
                if (!isValid) {
                    console.log("Validación fallida, no se procede con el registro");
                    return;
                }

                // Mostrar alerta de carga estilo modal centrado como en la imagen
                Swal.fire({
                    title: 'Guardando...',
                    html: 'Por favor espere mientras se registra el doctor.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Proceso de registro unificado
                registrarDoctorCompleto();
            }


            function verificarCamposVaciosAntesDeEnviar() {
                const camposObligatorios = [{
                        id: 'apellidos',
                        nombre: 'Apellidos'
                    },
                    {
                        id: 'nombres',
                        nombre: 'Nombres'
                    },
                    {
                        id: 'tipodoc',
                        nombre: 'Tipo de documento'
                    },
                    {
                        id: 'nrodoc',
                        nombre: 'Número de documento'
                    },
                    {
                        id: 'fechanacimiento',
                        nombre: 'Fecha de nacimiento'
                    },
                    {
                        id: 'genero',
                        nombre: 'Género'
                    },
                    {
                        id: 'telefono',
                        nombre: 'Teléfono'
                    },
                    {
                        id: 'email',
                        nombre: 'Email'
                    },
                    {
                        id: 'direccion',
                        nombre: 'Dirección'
                    },
                    {
                        id: 'especialidad',
                        nombre: 'Especialidad'
                    },
                    {
                        id: 'tipocontrato',
                        nombre: 'Tipo de contrato'
                    },
                    {
                        id: 'fechainicio',
                        nombre: 'Fecha de inicio'
                    },
                    {
                        id: 'nomuser',
                        nombre: 'Nombre de usuario'
                    },
                    {
                        id: 'passuser',
                        nombre: 'Contraseña'
                    }
                ];

                const camposVacios = [];

                camposObligatorios.forEach(campo => {
                    const elemento = document.getElementById(campo.id);
                    if (elemento && (!elemento.value || elemento.value.trim() === '')) {
                        camposVacios.push(campo.nombre);
                    }
                });

                // Verificar fecha fin para contratos que no son indefinidos
                const tipoContrato = document.getElementById('tipocontrato').value;
                if (tipoContrato && tipoContrato !== 'INDEFINIDO') {
                    const fechaFin = document.getElementById('fechafin');
                    if (fechaFin && (!fechaFin.value || fechaFin.value.trim() === '')) {
                        camposVacios.push('Fecha de fin (requerida para el tipo de contrato seleccionado)');
                    }
                }

                return camposVacios;
            }
            // Función para registrar la información personal y profesional en un solo paso
            async function registrarDoctorCompleto() {
                try {
                    // Primero aseguramos que el precio esté cargado
                    await asegurarPrecioAtencion();

                    const formData = new FormData();
                    const precioatencion = document.getElementById('precioatencion').value;

                    // Verificar que el precio sea mayor que cero
                    if (!precioatencion || parseFloat(precioatencion) <= 0) {
                        throw new Error("El precio de atención debe ser mayor a cero");
                    }

                    // Datos personales
                    formData.append('apellidos', document.getElementById('apellidos').value);
                    formData.append('nombres', document.getElementById('nombres').value);
                    formData.append('tipodoc', document.getElementById('tipodoc').value);
                    formData.append('nrodoc', document.getElementById('nrodoc').value);
                    formData.append('telefono', document.getElementById('telefono').value);
                    formData.append('fechanacimiento', document.getElementById('fechanacimiento').value);
                    formData.append('genero', document.getElementById('genero').value);
                    formData.append('direccion', document.getElementById('direccion').value);
                    formData.append('email', document.getElementById('email').value);

                    // Datos profesionales
                    formData.append('idespecialidad', document.getElementById('especialidad').value);
                    formData.append('precioatencion', precioatencion);

                    // Log para verificar todos los datos enviados
                    const formDataObj = {};
                    formData.forEach((value, key) => {
                        formDataObj[key] = value;
                    });
                    console.log("Datos a enviar:", formDataObj);

                    // Enviar datos unificados
                    const response = await fetch('../../../controllers/doctor.controller.php?op=registrar', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error(`Error en la respuesta del servidor: ${response.status}`);
                    }

                    const data = await response.json();
                    console.log("Respuesta de registro doctor:", data);

                    if (data.status) {
                        // Extraer los IDs necesarios para los siguientes pasos
                        const idcolaborador = data.idcolaborador;
                        const idpersona = data.idpersona;

                        if (!idcolaborador) {
                            throw new Error("No se recibió el ID de colaborador necesario para continuar");
                        }

                        // Continuar con el registro del contrato
                        await registrarContrato(idcolaborador);
                    } else {
                        throw new Error(data.mensaje || 'Error en el registro del doctor');
                    }
                } catch (error) {
                    console.error('Error al registrar doctor:', error);
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al registrar doctor',
                        text: error.message || 'No se pudo completar el registro del doctor. Intente nuevamente.'
                    });
                }
            }
            // Función para deshabilitar inicialmente toda la sección de horarios
            function deshabilitarSeccionHorarios() {
                console.log("Deshabilitando sección de horarios");
                const diasSemana = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];

                // Deshabilitar todos los switches de días
                diasSemana.forEach(dia => {
                    const atiende = document.getElementById(`atiende${dia}`);
                    if (atiende) {
                        atiende.checked = false;
                        atiende.disabled = true;

                        // Asegurarse que los selectores de modalidad y campos de horario estén deshabilitados
                        const modalidad = document.getElementById(`modalidad${dia}`);
                        if (modalidad) modalidad.disabled = true;

                        const horaInicio1 = document.getElementById(`horaInicio${dia}1`);
                        const horaFin1 = document.getElementById(`horaFin${dia}1`);
                        const horaInicio2 = document.getElementById(`horaInicio${dia}2`);
                        const horaFin2 = document.getElementById(`horaFin${dia}2`);

                        if (horaInicio1) horaInicio1.disabled = true;
                        if (horaFin1) horaFin1.disabled = true;
                        if (horaInicio2) horaInicio2.disabled = true;
                        if (horaFin2) horaFin2.disabled = true;
                    }
                });

                // Eliminar cualquier mensaje previo sobre días habilitados
                const mensajeDiasHabilitados = document.getElementById('mensajeDiasHabilitados');
                if (mensajeDiasHabilitados) {
                    mensajeDiasHabilitados.remove();
                }

                // Agregar mensaje indicando que se debe confirmar el contrato primero
                const tablaHorarios = document.querySelector('.table-responsive');
                if (tablaHorarios) {
                    // Eliminar mensaje previo si existe
                    const mensajeConfirmarPrevio = document.getElementById('mensajeConfirmarContrato');
                    if (mensajeConfirmarPrevio) {
                        mensajeConfirmarPrevio.remove();
                    }

                    // Crear nuevo mensaje
                    const mensajeInfo = document.createElement('div');
                    mensajeInfo.id = 'mensajeConfirmarContrato';
                    mensajeInfo.className = 'alert alert-warning mt-3 mb-3';
                    mensajeInfo.innerHTML = '<i class="fas fa-info-circle me-2"></i> Primero debe completar y confirmar la información del contrato para habilitar los horarios de atención.';

                    // Insertar antes de la tabla
                    tablaHorarios.parentNode.insertBefore(mensajeInfo, tablaHorarios);

                    // También agregar un estilo visual para indicar que está deshabilitado
                    tablaHorarios.classList.add('opacity-50');
                }
            }
            // Función corregida para habilitar los días según las fechas del contrato
            window.habilitarDiasSegunFechasContrato = function() {
                const tipoContrato = document.getElementById('tipocontrato').value;
                const fechaInicioStr = document.getElementById('fechainicio').value;
                const fechaFinStr = document.getElementById('fechafin').value;

                if (!fechaInicioStr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Debe seleccionar una fecha de inicio para el contrato'
                    });
                    return false;
                }

                // Verificar si es contrato indefinido o si tiene fecha de fin válida
                if (tipoContrato !== 'INDEFINIDO' && !fechaFinStr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Debe seleccionar una fecha de fin para este tipo de contrato'
                    });
                    return false;
                }

                // Convertir fechas a objetos Date
                const fechaInicio = new Date(fechaInicioStr + 'T00:00:00');
                let fechaFin = null;

                if (fechaFinStr) {
                    fechaFin = new Date(fechaFinStr + 'T23:59:59');
                }

                // Verificar si la fecha fin es anterior a la fecha inicio
                if (fechaFin && fechaFin < fechaInicio) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error en fechas',
                        text: 'La fecha de fin no puede ser anterior a la fecha de inicio'
                    });

                    // Marcar visualmente el campo como inválido
                    const fechaFinInput = document.getElementById('fechafin');
                    markFieldAsInvalid(fechaFinInput);
                    addFieldHelpMessage(fechaFinInput, 'La fecha de fin no puede ser anterior a la fecha de inicio');

                    return false;
                }

                // Habilitar los controles de horario
                const btnAgregarHorario = document.getElementById('btnAgregarHorario');
                const horarioDia = document.getElementById('horarioDia');
                const horarioInicio = document.getElementById('horarioInicio');
                const horarioFin = document.getElementById('horarioFin');

                btnAgregarHorario.disabled = false;
                horarioDia.disabled = false;
                horarioInicio.disabled = false;
                horarioFin.disabled = false;

                // Quitar mensaje de advertencia
                const mensajeConfirmar = document.getElementById('mensajeConfirmarContrato');
                if (mensajeConfirmar) {
                    mensajeConfirmar.remove();
                }

                // Añadir mensaje de contrato confirmado
                const tablaHorarios = document.querySelector('.table-responsive');
                if (tablaHorarios) {
                    tablaHorarios.classList.remove('opacity-50');

                    // Añadir mensaje de confirmación
                    const mensajeInfo = document.createElement('div');
                    mensajeInfo.id = 'mensajeDiasHabilitados';
                    mensajeInfo.className = 'alert alert-success mt-3 mb-3';

                    if (fechaFin) {
                        const fechaInicioFormateada = fechaInicio.toLocaleDateString('es-ES');
                        const fechaFinFormateada = fechaFin.toLocaleDateString('es-ES');
                        mensajeInfo.innerHTML = `<i class="fas fa-check-circle me-2"></i> Contrato confirmado desde ${fechaInicioFormateada} hasta ${fechaFinFormateada}. Ya puede agregar horarios de atención.`;
                    } else {
                        const fechaInicioFormateada = fechaInicio.toLocaleDateString('es-ES');
                        mensajeInfo.innerHTML = `<i class="fas fa-check-circle me-2"></i> Contrato indefinido confirmado desde ${fechaInicioFormateada}. Ya puede agregar horarios de atención.`;
                    }

                    // Eliminar mensaje anterior si existe
                    const mensajeAnterior = document.getElementById('mensajeDiasHabilitados');
                    if (mensajeAnterior) {
                        mensajeAnterior.remove();
                    }

                    // Insertar antes de la tabla
                    tablaHorarios.parentNode.insertBefore(mensajeInfo, tablaHorarios);
                }

                // Mostrar notificación
                showSuccessToast('Contrato confirmado. Ahora puede agregar horarios de atención.');

                // Añadir desplazamiento automático a la sección de horarios
                setTimeout(() => {
                    // Buscar la mejor referencia para hacer scroll
                    const formHorario = document.getElementById('horarioDia').closest('.row');
                    if (formHorario) {
                        formHorario.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        // Dar focus al primer campo del formulario de horarios
                        document.getElementById('horarioDia').focus();
                    }
                }, 300); // Pequeño retraso para asegurar que todo está renderizado correctamente

                return true;
            };
            // Función para validar la relación entre hora inicio y hora fin
            function validarHoraFinPosterior() {
                console.log("Validando relación entre hora inicio y fin");
                const horarioInicio = document.getElementById('horarioInicio');
                const horarioFin = document.getElementById('horarioFin');

                // Verificar que ambos campos tengan valores
                if (!horarioInicio || !horarioFin) {
                    console.error("No se encontraron los elementos de hora");
                    return false;
                }

                // Si ambos campos tienen valores, validar la relación
                if (horarioInicio.value && horarioFin.value) {
                    console.log(`Validando: Inicio=${horarioInicio.value}, Fin=${horarioFin.value}`);

                    // Convertir a objetos Date para comparación
                    const inicioTime = new Date(`2000-01-01T${horarioInicio.value}`);
                    const finTime = new Date(`2000-01-01T${horarioFin.value}`);

                    if (finTime <= inicioTime) {
                        console.log("Validación fallida: Fin no es posterior a inicio");
                        markFieldAsInvalid(horarioFin);
                        addFieldHelpMessage(horarioFin, 'La hora de fin debe ser posterior a la hora de inicio');
                        markFieldAsInvalid(horarioInicio); // También marcar hora de inicio como inválida

                        // Mostrar notificación de error
                        showErrorToast('La hora de fin debe ser posterior a la hora de inicio');
                        return false;
                    } else {
                        console.log("Validación exitosa: Fin es posterior a inicio");
                        // Marcar AMBOS campos como válidos
                        markFieldAsValid(horarioFin);
                        markFieldAsValid(horarioInicio);
                        removeFieldHelpMessage(horarioFin);
                        removeFieldHelpMessage(horarioInicio);
                        return true;
                    }
                }

                // Si alguno de los campos está vacío, consideramos que no hay suficiente información para validar
                console.log("No hay suficiente información para validar: algún campo está vacío");
                return true;
            }


            // Añadir el evento para validación al campo de hora fin
            document.getElementById('horarioFin').addEventListener('change', validarHoraFinPosterior);
            document.getElementById('horarioFin').addEventListener('blur', validarHoraFinPosterior);

            // También validar cuando cambia la hora de inicio
            document.getElementById('horarioInicio').addEventListener('change', validarHoraFinPosterior);
            document.getElementById('horarioInicio').addEventListener('blur', validarHoraFinPosterior);
            // Configurar los inputs de hora para incrementos de 5 minutos
            function configurarInputsHora() {
                const horarioInicio = document.getElementById('horarioInicio');
                const horarioFin = document.getElementById('horarioFin');

                // Configurar incrementos de 5 minutos
                horarioInicio.setAttribute('step', '300'); // 300 segundos = 5 minutos
                horarioFin.setAttribute('step', '300');
            }

            configurarInputsHora();

            // Función corregida para obtener los días habilitados según las fechas
            function obtenerDiasHabilitados(fechaInicioStr, fechaFinStr) {
                const diasHabilitados = new Set();

                // Crear objetos Date para trabajar con las fechas
                const fechaInicio = new Date(fechaInicioStr + 'T00:00:00');
                let fechaFin;

                // Si hay fecha fin (contrato no indefinido), usar esa fecha
                if (fechaFinStr) {
                    fechaFin = new Date(fechaFinStr + 'T23:59:59');
                } else {
                    // Si es indefinido, habilitar todos los días de la semana
                    return new Set(['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO']);
                }

                // Mapeo de números de día a nombres de día como están en el select
                const diasSemana = [
                    'DOMINGO', // 0
                    'LUNES', // 1
                    'MARTES', // 2
                    'MIERCOLES', // 3
                    'JUEVES', // 4
                    'VIERNES', // 5
                    'SABADO' // 6
                ];

                // Iterar sobre cada día en el rango de fechas
                let fechaActual = new Date(fechaInicio);
                while (fechaActual <= fechaFin) {
                    // Obtener el nombre del día correspondiente
                    const diaSemana = diasSemana[fechaActual.getDay()];
                    diasHabilitados.add(diaSemana);

                    // Avanzar al siguiente día
                    fechaActual.setDate(fechaActual.getDate() + 1);
                }

                return diasHabilitados;
            }
            // Función para mostrar mensaje con los días habilitados
            function mostrarMensajeDiasHabilitados(diasHabilitados) {
                const diasArray = Array.from(diasHabilitados).sort((a, b) => {
                    const orden = {
                        'Lunes': 1,
                        'Martes': 2,
                        'Miercoles': 3,
                        'Jueves': 4,
                        'Viernes': 5,
                        'Sabado': 6,
                        'Domingo': 7
                    };
                    return orden[a] - orden[b];
                });

                let mensaje = '';

                if (diasArray.length === 7) {
                    mensaje = 'Se han habilitado todos los días de la semana.';
                } else {
                    mensaje = `Se han habilitado los siguientes días: <strong>${diasArray.join(', ')}</strong>.`;
                }

                // Eliminar mensaje anterior si existe
                const mensajeAnterior = document.getElementById('mensajeDiasHabilitados');
                if (mensajeAnterior) {
                    mensajeAnterior.remove();
                }

                // Añadir mensaje en la interfaz
                const tablaHorarios = document.querySelector('.table-responsive');
                if (tablaHorarios) {
                    // Crear nuevo mensaje
                    const mensajeInfo = document.createElement('div');
                    mensajeInfo.id = 'mensajeDiasHabilitados';
                    mensajeInfo.className = 'alert alert-info mt-3 mb-3';
                    mensajeInfo.innerHTML = `<i class="fas fa-calendar-check me-2"></i> ${mensaje}`;

                    // Insertar antes de la tabla
                    tablaHorarios.parentNode.insertBefore(mensajeInfo, tablaHorarios);
                }

                // También mostrar notificación
                showSuccessToast(`Horarios activados. ${diasArray.length === 7 ? 'Todos los días disponibles.' : `Días habilitados: ${diasArray.join(', ')}.`}`);
            }
            // Evento del botón confirmar contrato
            document.getElementById('btnConfirmarContrato').addEventListener('click', function() {
                // Validar los campos del contrato antes de confirmar
                if (validateContratoInfo()) {
                    habilitarDiasSegunFechasContrato();
                }
            });

            // Función para configurar el botón de confirmar contrato
            function setupConfirmarContratoButton() {
                const btnConfirmarContrato = document.getElementById('btnConfirmarContrato');
                if (btnConfirmarContrato) {
                    btnConfirmarContrato.addEventListener('click', function() {
                        // Validar los campos del contrato antes de confirmar
                        if (validateContratoInfo()) {
                            if (habilitarDiasSegunFechasContrato()) {
                                // Hacer scroll hasta la sección de horarios
                                const seccionHorarios = document.querySelector('.card .card-header:contains("Horario de Atención")');
                                if (seccionHorarios) {
                                    seccionHorarios.scrollIntoView({
                                        behavior: 'smooth',
                                        block: 'start'
                                    });
                                }
                            }
                        }
                    });
                }
            }
            // Asegurar que no haya mensajes de advertencia duplicados al inicio
            limpiarMensajesAdvertencia();

            // Deshabilitar inicialmente la sección de horarios (con un solo mensaje)
            deshabilitarSeccionHorarios();

            // Configurar el botón de confirmar contrato
            setupConfirmarContratoButton();

            // Agregar clases necesarias para los estilos
            // Preparar estilos CSS adicionales
            const style = document.createElement('style');
            style.textContent = `
.bg-light-success {
    background-color: rgba(25, 135, 84, 0.15) !important;
}
.bg-light-secondary {
    background-color: rgba(173, 181, 189, 0.15) !important;
}
.opacity-50 {
    opacity: 0.5;
}
.horario-item {
    margin-bottom: 2px;
    border-left: 3px solid #198754;
    padding-left: 5px;
}
`;
            document.head.appendChild(style);

            // Función para resetear el estado de los mensajes de horarios
            function resetearMensajesHorarios() {
                // Eliminar el mensaje "Se han habilitado todos los días de la semana"
                const mensajeDiasHabilitados = document.getElementById('mensajeDiasHabilitados');
                if (mensajeDiasHabilitados) {
                    mensajeDiasHabilitados.remove();
                }

                // Eliminar cualquier otro mensaje que pueda haber sido añadido
                const mensajeConfirmar = document.getElementById('mensajeConfirmarContrato');
                if (mensajeConfirmar) {
                    mensajeConfirmar.remove();
                }

                // Restaurar el mensaje de advertencia original
                const tablaHorarios = document.querySelector('.table-responsive');
                if (tablaHorarios) {
                    // Volver a añadir opacidad a la tabla
                    tablaHorarios.classList.add('opacity-50');

                    // Volver a añadir el mensaje de advertencia original si no existe
                    if (!document.getElementById('mensajeConfirmarContrato')) {
                        const mensajeInfo = document.createElement('div');
                        mensajeInfo.id = 'mensajeConfirmarContrato';
                        mensajeInfo.className = 'alert alert-warning mt-3 mb-3';
                        mensajeInfo.innerHTML = '<i class="fas fa-info-circle me-2"></i> Primero debe completar y confirmar la información del contrato para habilitar los horarios de atención.';

                        // Insertar antes de la tabla
                        tablaHorarios.parentNode.insertBefore(mensajeInfo, tablaHorarios);
                    }
                }
            }
            // Configurar evento de cambio de tipo de contrato para limpiar fechas
            const tipoContratoSelect = document.getElementById('tipocontrato');
            if (tipoContratoSelect) {
                tipoContratoSelect.addEventListener('change', function() {
                    // Limpiar campos de fecha
                    const fechaInicio = document.getElementById('fechainicio');
                    const fechaFin = document.getElementById('fechafin');

                    if (fechaInicio) fechaInicio.value = '';
                    if (fechaFin) fechaFin.value = '';

                    // Si es tipo indefinido, deshabilitar fecha fin
                    if (this.value === 'INDEFINIDO') {
                        if (fechaFin) {
                            fechaFin.disabled = true;
                            fechaFin.value = '';
                            // Agregar mensaje visual
                            const helpText = document.createElement('small');
                            helpText.id = 'helpTextFechaFin';
                            helpText.className = 'text-muted';
                            helpText.textContent = 'No aplica para contratos indefinidos';

                            // Eliminar mensaje previo si existe
                            const prevHelpText = document.getElementById('helpTextFechaFin');
                            if (prevHelpText) prevHelpText.remove();

                            // Agregar nuevo mensaje
                            if (fechaFin.parentNode) {
                                fechaFin.parentNode.appendChild(helpText);
                            }
                        }
                    } else {
                        // Para otros tipos, habilitar fecha fin
                        if (fechaFin) {
                            fechaFin.disabled = false;

                            // Eliminar mensaje si existe
                            const helpText = document.getElementById('helpTextFechaFin');
                            if (helpText) helpText.remove();
                        }
                    }

                    // Limpiar mensajes de advertencia para evitar duplicación
                    limpiarMensajesAdvertencia();

                    // Resetear los mensajes de horarios (ESTE ES EL CAMBIO)
                    resetearMensajesHorarios();

                    // Deshabilitar los horarios al cambiar el tipo de contrato (sin duplicar mensajes)
                    deshabilitarSeccionHorarios();
                });
            }
            // Eventos para cuando se cambian las fechas
            const fechaInicioInput = document.getElementById('fechainicio');
            const fechaFinInput = document.getElementById('fechafin');

            if (fechaInicioInput) {
                fechaInicioInput.addEventListener('change', function() {
                    // Limpiar mensajes de advertencia para evitar duplicación
                    limpiarMensajesAdvertencia();

                    // Deshabilitar los horarios al cambiar la fecha de inicio
                    deshabilitarSeccionHorarios();

                    // Establecer fecha mínima para fecha fin (1 día después)
                    if (fechaFinInput && this.value) {
                        const fechaInicio = new Date(this.value);
                        fechaInicio.setDate(fechaInicio.getDate() + 1);

                        // Formatear fecha para atributo min
                        const yyyy = fechaInicio.getFullYear();
                        const mm = String(fechaInicio.getMonth() + 1).padStart(2, '0');
                        const dd = String(fechaInicio.getDate()).padStart(2, '0');

                        fechaFinInput.min = `${yyyy}-${mm}-${dd}`;

                        // Si la fecha fin es anterior a la nueva fecha mínima, limpiarla
                        if (fechaFinInput.value && new Date(fechaFinInput.value) < fechaInicio) {
                            fechaFinInput.value = '';
                        }
                    }
                });
            }

            if (fechaFinInput) {
                fechaFinInput.addEventListener('change', function() {
                    // Limpiar mensajes de advertencia para evitar duplicación
                    limpiarMensajesAdvertencia();

                    // Deshabilitar los horarios al cambiar la fecha de fin
                    deshabilitarSeccionHorarios();
                });
            }

            // Función para limpiar todos los mensajes de advertencia existentes
            function limpiarMensajesAdvertencia() {
                const mensajesExistentes = document.querySelectorAll('.alert.alert-warning');
                mensajesExistentes.forEach(msg => {
                    if (msg.id === 'mensajeConfirmarContrato') {
                        msg.remove();
                    }
                });
            }

            // Función para registrar el contrato
            async function registrarContrato(idcolaborador) {
                if (!idcolaborador) {
                    console.error("Error: No se proporcionó un ID de colaborador válido");
                    Swal.fire({
                        icon: 'error',
                        title: 'Error en el registro',
                        text: 'No se pudo continuar con el registro del contrato. Información incompleta.'
                    });
                    return;
                }

                const formData = new FormData();
                formData.append('idcolaborador', idcolaborador);
                formData.append('tipocontrato', document.getElementById('tipocontrato').value);
                formData.append('fechainicio', document.getElementById('fechainicio').value);

                // Si es contrato indefinido, la fecha fin puede ser null
                const tipoContrato = document.getElementById('tipocontrato').value;
                const fechaFin = document.getElementById('fechafin').value;
                formData.append('fechafin', tipoContrato === 'INDEFINIDO' ? null : (fechaFin || null));

                // Log para depuración
                console.log("Enviando datos de contrato:", {
                    idcolaborador,
                    tipocontrato: document.getElementById('tipocontrato').value,
                    fechainicio: document.getElementById('fechainicio').value,
                    fechafin: tipoContrato === 'INDEFINIDO' ? null : (fechaFin || null)
                });

                try {
                    // Enviar datos del contrato
                    const response = await fetch('../../../controllers/contrato.controller.php?op=registrar', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error(`Error en la respuesta del servidor: ${response.status}`);
                    }

                    const data = await response.json();
                    console.log("Respuesta de registro de contrato:", data);

                    if (data.status) {
                        const idcontrato = data.idcontrato;

                        if (!idcontrato) {
                            throw new Error("No se recibió el ID de contrato necesario para continuar");
                        }

                        // Continuar con el registro de horarios
                        await registrarHorarios(idcolaborador, idcontrato);
                    } else {
                        throw new Error(data.mensaje || 'Error en el registro del contrato');
                    }
                } catch (error) {
                    console.error('Error al registrar contrato:', error);
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al registrar contrato',
                        text: error.message || 'No se pudo completar el registro del contrato. Intente nuevamente.'
                    });
                }
            }

            // Función para registrar horarios durante el proceso de guardado
            async function registrarHorarios(idcolaborador, idcontrato) {
                // Verificar si hay horarios para registrar
                if (!horariosAgregados || horariosAgregados.length === 0) {
                    console.log("No hay horarios para registrar, continuando con credenciales");

                    // Continuar con las credenciales
                    await registrarCredenciales(idcontrato);
                    return;
                }

                // Vamos a procesar los horarios de forma secuencial para evitar problemas de concurrencia
                let horariosRegistrados = 0;
                let horariosConError = [];
                const totalHorarios = horariosAgregados.length;

                try {
                    // Procesar cada horario secuencialmente
                    for (let i = 0; i < totalHorarios; i++) {
                        const horario = horariosAgregados[i];
                        console.log(`Procesando horario ${i+1}/${totalHorarios}: ${horario.diaNombre}`);

                        try {
                            // Paso 1: Registrar la atención (día)
                            const formDataAtencion = new FormData();
                            formDataAtencion.append('idcontrato', idcontrato);
                            formDataAtencion.append('diasemana', horario.dia);

                            const atencionResponse = await fetch('../../../controllers/atencion.controller.php?op=registrar', {
                                method: 'POST',
                                body: formDataAtencion
                            });

                            if (!atencionResponse.ok) {
                                throw new Error(`Error en la respuesta del servidor al registrar atención: ${atencionResponse.status}`);
                            }

                            const atencionData = await atencionResponse.json();
                            console.log(`Respuesta del registro de atención para ${horario.diaNombre}:`, atencionData);

                            if (!atencionData.status || !atencionData.idatencion) {
                                throw new Error(atencionData.mensaje || `Error al registrar atención para ${horario.diaNombre}`);
                            }

                            // Paso 2: Registrar el horario
                            const idatencion = atencionData.idatencion;
                            const formDataHorario = new FormData();
                            formDataHorario.append('idatencion', idatencion);
                            formDataHorario.append('horainicio', horario.horaInicio);
                            formDataHorario.append('horafin', horario.horaFin);

                            const horarioResponse = await fetch('../../../controllers/horario.controller.php?op=registrar', {
                                method: 'POST',
                                body: formDataHorario
                            });

                            if (!horarioResponse.ok) {
                                throw new Error(`Error en la respuesta del servidor al registrar horario: ${horarioResponse.status}`);
                            }

                            const horarioData = await horarioResponse.json();
                            console.log(`Respuesta del registro de horario para ${horario.diaNombre}:`, horarioData);

                            if (!horarioData.status) {
                                throw new Error(horarioData.mensaje || `Error al registrar horario para ${horario.diaNombre}`);
                            }

                            // Horario registrado correctamente
                            horariosRegistrados++;
                            console.log(`Horario de ${horario.diaNombre} registrado correctamente`);

                        } catch (error) {
                            console.error(`Error al registrar horario para ${horario.diaNombre}:`, error);
                            horariosConError.push(horario.diaNombre);
                        }
                    }

                    // Continuar con credenciales
                    await registrarCredenciales(idcontrato, horariosConError.length > 0);
                } catch (error) {
                    console.error('Error al procesar horarios:', error);
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al registrar horarios',
                        text: error.message || 'No se pudieron completar todos los horarios. Intente nuevamente.'
                    });
                }
            }

            // Función para registrar credenciales
            async function registrarCredenciales(idcontrato, huboErroresHorarios = false) {
                if (!idcontrato) {
                    console.error("Error: No se proporcionó un ID de contrato válido");
                    Swal.fire({
                        icon: 'error',
                        title: 'Error en el registro',
                        text: 'No se pudo continuar con el registro de credenciales. Información incompleta.'
                    });
                    return;
                }

                const emailUsuario = document.getElementById('nomuser').value;

                // Verificar si el correo ya está en uso antes de intentar registrar
                try {
                    const disponible = await verificarCorreoDisponible(emailUsuario);
                    if (!disponible) {
                        // Si el correo no está disponible, mostrar el modal de credenciales nuevamente
                        Swal.close();
                        setTimeout(() => {
                            mostrarAlertaCredenciales();
                        }, 300);
                        return;
                    }

                    const formData = new FormData();
                    formData.append('idcontrato', idcontrato);
                    formData.append('nomuser', emailUsuario);
                    formData.append('passuser', document.getElementById('passuser').value);
                    formData.append('rol', 'DOCTOR'); // Por defecto, rol de doctor

                    // Log para depuración
                    console.log("Enviando datos de credenciales:", {
                        idcontrato,
                        nomuser: emailUsuario,
                        // No mostrar la contraseña por seguridad
                        rol: 'DOCTOR'
                    });

                    // Mostrar alerta de carga estilo modal centrado
                    Swal.fire({
                        title: 'Guardando...',
                        html: 'Por favor espere mientras se registra el doctor.',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Enviar datos de credenciales
                    const response = await fetch('../../../controllers/credencial.controller.php?op=registrar', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error(`Error en la respuesta del servidor: ${response.status}`);
                    }

                    const data = await response.json();
                    console.log("Respuesta de registro de credenciales:", data);

                    // Añadir retraso para mostrar el mensaje final (mejor experiencia de usuario)
                    setTimeout(() => {
                        Swal.close();

                        if (data.status) {
                            // Registro completo exitoso (con o sin errores en horarios)
                            if (huboErroresHorarios) {
                                Swal.fire({
                                    icon: 'info',
                                    title: '¡Registro completado con advertencias!',
                                    html: 'El doctor ha sido registrado correctamente, pero algunos horarios no pudieron registrarse. <br><br>Puede editar los horarios más tarde desde la sección de administración.',
                                    confirmButtonText: 'Entendido'
                                }).then(() => {
                                    resetFormAndRedirect();
                                });
                            } else {
                                // Registro completamente exitoso
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Registro exitoso!',
                                    html: 'El doctor ha sido registrado correctamente con toda su información.',
                                    confirmButtonText: 'Aceptar'
                                }).then(() => {
                                    resetFormAndRedirect();
                                });
                            }
                        } else {
                            // Verificar si el error es porque el usuario ya existe
                            if (data.mensaje && (data.mensaje.includes("ya está en uso") || data.mensaje.includes("ya existe"))) {
                                // En lugar de mostrar un error y luego volver a abrir el modal,
                                // mostramos directamente el modal con un mensaje interno
                                Swal.close();
                                setTimeout(() => {
                                    mostrarAlertaCredenciales();
                                }, 300);
                            } else {
                                // Otro tipo de error en credenciales pero el resto está registrado
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Registro parcial',
                                    html: 'La información del doctor ha sido registrada, pero hubo un problema con las credenciales de acceso: <br><br>' + (data.mensaje || 'Error desconocido') + '<br><br>Puede configurar las credenciales más tarde desde la administración.',
                                    confirmButtonText: 'Entendido'
                                }).then(() => {
                                    resetFormAndRedirect();
                                });
                            }
                        }
                    }, 1000); // Retraso de 1 segundo para mejor experiencia
                } catch (error) {
                    console.error("Error al verificar disponibilidad del correo:", error);
                    Swal.close();

                    // Mostrar modal de credenciales nuevamente con mensaje de error
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de verificación',
                            text: 'No se pudo verificar la disponibilidad del correo. Por favor, inténtelo nuevamente.',
                            confirmButtonText: 'Reintentar'
                        }).then(() => {
                            mostrarAlertaCredenciales();
                        });
                    }, 300);
                }
            }
            // Función para mostrar mensaje de éxito
            function mostrarExito() {
                Swal.fire({
                    icon: 'success',
                    title: '¡Registro exitoso!',
                    text: 'El doctor ha sido registrado correctamente.',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    resetFormAndRedirect();
                });
            }

            // Función para resetear formulario y redirigir
            function resetFormAndRedirect() {
                // Guardar los datos del doctor registrado para referencia futura
                const doctorInfo = {
                    nrodoc: document.getElementById('nrodoc').value,
                    nombres: document.getElementById('nombres').value,
                    apellidos: document.getElementById('apellidos').value
                };

                // Almacenar en localStorage para potencial uso en la página de destino
                localStorage.setItem('lastRegisteredDoctor', JSON.stringify(doctorInfo));

                console.log("Registro completado. Redirigiendo a la lista de doctores...");

                // Limpiar formulario
                document.getElementById('doctorRegistrationForm').reset();

                // Restablecer valores por defecto
                document.getElementById("tipodoc").value = "DNI";
                document.getElementById("genero").value = "M";

                // Importante: Restablecer maxlength para DNI
                document.getElementById("nrodoc").setAttribute("maxlength", "8");

                // Bloquear campos y botones
                bloquearCampos();

                // Redirigir a la lista de doctores
                window.location.href = '../ListarDoctor/listarDoctor.php';
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
                        document.getElementById('doctorRegistrationForm').reset();

                        // Restablecer valores por defecto
                        document.getElementById("tipodoc").value = "DNI";
                        document.getElementById("genero").value = "M";

                        // Importante: Restablecer maxlength para DNI
                        document.getElementById("nrodoc").setAttribute("maxlength", "8");

                        // Limpiar clases de validación
                        const inputs = document.querySelectorAll('.form-control, .form-select');
                        inputs.forEach(input => {
                            input.classList.remove('is-invalid', 'is-valid');
                            removeFieldHelpMessage(input);
                        });

                        // Bloquear campos y botones
                        bloquearCampos();

                        // Restablecer horarios
                        const diasSemana = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];
                        diasSemana.forEach(dia => {
                            const checkbox = document.getElementById(`atiende${dia}`);
                            if (checkbox) {
                                checkbox.checked = false;
                            }
                        });

                        // Volver a la primera pestaña
                        showTab('personalInfo');
                        updateProgressBar(0);

                        // Mostrar mensaje
                        showSuccessToast('Formulario restablecido');
                    }
                });
            }

            // Función para formatear fechas
            function formatDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                return date.toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            }

            function formatTime(timeString) {
                if (!timeString) return '';
                return timeString;
            }
        });
    </script>

</body>

</html>