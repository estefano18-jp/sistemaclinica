<?php
// Este archivo carga el formulario para editar la información de contrato del doctor

// Verificar que se haya proporcionado un número de documento
if (!isset($_GET['nrodoc']) || empty($_GET['nrodoc'])) {
    echo '<div class="alert alert-danger">Número de documento no proporcionado.</div>';
    exit;
}

$nrodoc = $_GET['nrodoc'];

// Incluir el modelo de Doctor y Contrato
require_once '../../../models/Doctor.php';
require_once '../../../models/Contrato.php';

$doctor = new Doctor();
$contrato = new Contrato();

// Obtener la información del doctor
$infoDoctor = $doctor->obtenerDoctorPorNroDoc($nrodoc);

if (!$infoDoctor) {
    echo '<div class="alert alert-danger">Doctor no encontrado.</div>';
    exit;
}

// Obtener información del contrato
$infoContrato = []; // Inicializar como array vacío
$contratos = $contrato->obtenerContratosPorColaborador($infoDoctor['idcolaborador']);
if ($contratos && count($contratos) > 0) {
    // Tomamos el contrato más reciente
    $infoContrato = $contratos[0];
} else {
    // Si no hay información de contrato, crear valores por defecto
    $infoContrato = [
        'idcontrato' => 0,
        'tipocontrato' => '',
        'fechainicio' => date('Y-m-d'),
        'fechafin' => null,
        'estado' => 'ACTIVO'
    ];
}

// Obtener la fecha actual para establecer límites
$fechaHoy = date('Y-m-d');
?>

<div class="container mt-4">
    <p class="text-center mb-4">
        <span class="badge bg-primary fs-6">Doctor: <?= htmlspecialchars($infoDoctor['nombres'] . ' ' . $infoDoctor['apellidos']) ?></span>
    </p>

    <form id="formEditarInfoContrato" method="POST">
        <input type="hidden" name="operacion" value="actualizar_contrato">
        <input type="hidden" name="nrodoc" value="<?= htmlspecialchars($nrodoc) ?>">
        <input type="hidden" name="idcolaborador" value="<?= htmlspecialchars($infoDoctor['idcolaborador'] ?? '') ?>">
        <input type="hidden" name="idcontrato" value="<?= htmlspecialchars($infoContrato['idcontrato'] ?? 0) ?>">
        <input type="hidden" name="estado" value="<?= htmlspecialchars($infoContrato['estado'] ?? 'ACTIVO') ?>">

        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Información de Contrato</strong>
                    <p class="mb-0">Desde aquí puede modificar la información del contrato del doctor.</p>
                </div>
            </div>
        </div>

        <!-- Tipo de Contrato -->
        <div class="row mb-4">
            <div class="col-md-12">
                <label for="tipocontrato" class="form-label required-field">Tipo de Contrato</label>
                <select class="form-select" id="tipocontrato" name="tipocontrato" required>
                    <option value="">Seleccione...</option>
                    <option value="INDEFINIDO" <?= ($infoContrato['tipocontrato'] == 'INDEFINIDO') ? 'selected' : '' ?>>Indefinido</option>
                    <option value="PLAZO FIJO" <?= ($infoContrato['tipocontrato'] == 'PLAZO FIJO') ? 'selected' : '' ?>>Plazo Fijo</option>
                    <option value="TEMPORAL" <?= ($infoContrato['tipocontrato'] == 'TEMPORAL') ? 'selected' : '' ?>>Temporal</option>
                    <option value="EVENTUAL" <?= ($infoContrato['tipocontrato'] == 'EVENTUAL') ? 'selected' : '' ?>>Eventual</option>
                </select>
                <div class="invalid-feedback" id="tipocontrato-error"></div>
            </div>
        </div>

        <!-- Fechas de Inicio y Fin -->
        <div class="row mb-4">
            <div class="col-md-6">
                <label for="fechainicio" class="form-label required-field">Fecha de Inicio</label>
                <input type="date" class="form-control" id="fechainicio" name="fechainicio"
                    value="<?= htmlspecialchars($infoContrato['fechainicio'] ?? $fechaHoy) ?>"
                    max="<?= $fechaHoy ?>" required>
                <div class="invalid-feedback" id="fechainicio-error"></div>
            </div>
            <div class="col-md-6">
                <label for="fechafin" class="form-label">Fecha de Fin</label>
                <input type="date" class="form-control" id="fechafin" name="fechafin"
                    value="<?= htmlspecialchars($infoContrato['fechafin'] ?? '') ?>"
                    <?= ($infoContrato['tipocontrato'] == 'INDEFINIDO') ? 'disabled' : '' ?>>
                <small class="text-muted" id="fechaFinHelp">Opcional para contratos indefinidos</small>
                <div class="invalid-feedback" id="fechafin-error"></div>
            </div>
        </div>

        <!-- Estado del contrato -->
        <div class="row mb-4">
            <div class="col-md-12">
                <label class="form-label required-field">Estado del Contrato</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="estado" id="estadoActivo" value="ACTIVO"
                        <?= ($infoContrato['estado'] == 'ACTIVO' || !isset($infoContrato['estado'])) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="estadoActivo">
                        <span class="badge bg-success">Activo</span>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="estado" id="estadoInactivo" value="INACTIVO"
                        <?= ($infoContrato['estado'] == 'INACTIVO') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="estadoInactivo">
                        <span class="badge bg-danger">Inactivo</span>
                    </label>
                </div>
                <div class="alert alert-info mt-2">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Nota importante:</strong> El estado del contrato está vinculado con el estado del doctor en el sistema.
                    Si cambia el estado del contrato, también cambiará el estado del doctor automáticamente.
                </div>

                <?php if (isset($infoDoctor['estado']) && $infoDoctor['estado'] != $infoContrato['estado']): ?>
                    <div class="alert alert-warning mt-2">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>¡Atención!</strong> Actualmente el estado del doctor es <span class="badge bg-<?= $infoDoctor['estado'] == 'ACTIVO' ? 'success' : 'danger' ?>"><?= $infoDoctor['estado'] ?></span> mientras que el estado del contrato es <span class="badge bg-<?= $infoContrato['estado'] == 'ACTIVO' ? 'success' : 'danger' ?>"><?= $infoContrato['estado'] ?></span>.
                        Al guardar los cambios, ambos estados se sincronizarán.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Manejar el cambio en tipo de contrato para habilitar/deshabilitar fecha fin
        const tipoContratoSelect = document.getElementById('tipocontrato');
        const fechaFinInput = document.getElementById('fechafin');
        const fechaFinHelp = document.getElementById('fechaFinHelp');
        const fechaInicioInput = document.getElementById('fechainicio');

        // Configurar comportamiento inicial para fecha fin
        actualizarEstadoFechaFin();

        tipoContratoSelect.addEventListener('change', function() {
            actualizarEstadoFechaFin();
        });

        // Función para actualizar el estado del campo fecha fin según el tipo de contrato
        function actualizarEstadoFechaFin() {
            const tipoContrato = tipoContratoSelect.value;

            if (tipoContrato === 'INDEFINIDO') {
                // Para contratos indefinidos, deshabilitar y limpiar fecha fin
                fechaFinInput.disabled = true;
                fechaFinInput.value = '';
                fechaFinInput.required = false;
                fechaFinInput.classList.remove('is-invalid', 'is-valid');
                fechaFinHelp.textContent = 'No aplica para contratos indefinidos';
                fechaFinHelp.classList.add('text-muted');
                fechaFinHelp.classList.remove('text-danger');
                document.getElementById('fechafin-error').style.display = 'none';
            } else {
                // Para otros tipos, habilitar fecha fin y hacerla requerida
                fechaFinInput.disabled = false;
                fechaFinHelp.textContent = 'Requerido para este tipo de contrato';
                fechaFinHelp.classList.add('text-danger');
                fechaFinHelp.classList.remove('text-muted');
                fechaFinInput.required = true;

                // Establecer la fecha mínima para fechaFin según el tipo de contrato
                if (fechaInicioInput.value) {
                    const fechaInicio = new Date(fechaInicioInput.value);
                    fechaInicio.setDate(fechaInicio.getDate() + 1); // Mínimo siguiente día

                    // Formatear como YYYY-MM-DD
                    const yyyy = fechaInicio.getFullYear();
                    const mm = String(fechaInicio.getMonth() + 1).padStart(2, '0');
                    const dd = String(fechaInicio.getDate()).padStart(2, '0');

                    fechaFinInput.min = `${yyyy}-${mm}-${dd}`;
                }
            }
        }

        // Validar fechas de contrato
        fechaInicioInput.addEventListener('change', function() {
            // Limpiar validaciones previas
            this.classList.remove('is-invalid', 'is-valid');
            document.getElementById('fechainicio-error').style.display = 'none';

            const fechaInicio = new Date(this.value);
            const fechaHoy = new Date();
            fechaHoy.setHours(0, 0, 0, 0); // Normalizar a inicio del día

            // Validar que fecha inicio no sea futura
            if (fechaInicio > fechaHoy) {
                this.classList.add('is-invalid');
                document.getElementById('fechainicio-error').textContent = 'La fecha de inicio no puede ser futura';
                document.getElementById('fechainicio-error').style.display = 'block';
            } else {
                this.classList.add('is-valid');

                // Actualizar fecha mínima para la fecha fin
                if (fechaFinInput && !fechaFinInput.disabled) {
                    const fechaInicio = new Date(this.value);
                    fechaInicio.setDate(fechaInicio.getDate() + 1); // Mínimo siguiente día

                    // Formatear como YYYY-MM-DD
                    const yyyy = fechaInicio.getFullYear();
                    const mm = String(fechaInicio.getMonth() + 1).padStart(2, '0');
                    const dd = String(fechaInicio.getDate()).padStart(2, '0');

                    fechaFinInput.min = `${yyyy}-${mm}-${dd}`;

                    // Si hay una fecha fin anterior, limpiarla
                    if (fechaFinInput.value && new Date(fechaFinInput.value) <= new Date(this.value)) {
                        fechaFinInput.value = '';
                        fechaFinInput.classList.remove('is-valid');
                    }
                }
            }
        });

        fechaFinInput.addEventListener('change', function() {
            // Solo validar si no está deshabilitado
            if (!this.disabled) {
                validarFechaFin();
            }
        });

        // Función para validar la fecha fin según tipo de contrato
        function validarFechaFin() {
            // Limpiar validaciones previas
            fechaFinInput.classList.remove('is-invalid', 'is-valid');
            document.getElementById('fechafin-error').style.display = 'none';

            const tipoContrato = tipoContratoSelect.value;

            // Si es indefinido, no necesitamos validar
            if (tipoContrato === 'INDEFINIDO') {
                return true;
            }

            // Si falta fecha inicio, no podemos validar correctamente
            if (!fechaInicioInput.value) {
                fechaFinInput.classList.add('is-invalid');
                document.getElementById('fechafin-error').textContent = 'Primero debe seleccionar una fecha de inicio';
                document.getElementById('fechafin-error').style.display = 'block';
                return false;
            }

            // Si no hay fecha fin, es inválido para todos los tipos excepto indefinido
            if (!fechaFinInput.value) {
                fechaFinInput.classList.add('is-invalid');
                document.getElementById('fechafin-error').textContent = 'La fecha de fin es requerida para este tipo de contrato';
                document.getElementById('fechafin-error').style.display = 'block';
                return false;
            }

            const fechaInicio = new Date(fechaInicioInput.value);
            const fechaFin = new Date(fechaFinInput.value);

            // Convertir a días para comparar correctamente sin problemas de zonas horarias
            const fechaInicioSinHora = new Date(fechaInicio.getFullYear(), fechaInicio.getMonth(), fechaInicio.getDate());
            const fechaFinSinHora = new Date(fechaFin.getFullYear(), fechaFin.getMonth(), fechaFin.getDate());

            // Verificar que la fecha fin sea posterior a la fecha inicio
            if (fechaFinSinHora <= fechaInicioSinHora) {
                fechaFinInput.classList.add('is-invalid');
                document.getElementById('fechafin-error').textContent = 'La fecha de fin debe ser posterior a la fecha de inicio';
                document.getElementById('fechafin-error').style.display = 'block';
                return false;
            }

            // Calcular diferencia en días
            const diferenciaMs = fechaFinSinHora.getTime() - fechaInicioSinHora.getTime();
            const diferenciaDias = Math.floor(diferenciaMs / (1000 * 60 * 60 * 24));

            // Validaciones específicas por tipo de contrato
            switch (tipoContrato) {
                case 'PLAZO FIJO':
                    // Validar que sea al menos 3 meses (90 días)
                    if (diferenciaDias < 90) {
                        fechaFinInput.classList.add('is-invalid');
                        document.getElementById('fechafin-error').textContent = 'Para contratos de plazo fijo, la duración mínima debe ser de 3 meses (90 días)';
                        document.getElementById('fechafin-error').style.display = 'block';
                        return false;
                    }
                    break;

                case 'TEMPORAL':
                    // Validar que esté entre 1 y 6 meses (30 a 180 días)
                    if (diferenciaDias < 30 || diferenciaDias > 180) {
                        fechaFinInput.classList.add('is-invalid');
                        document.getElementById('fechafin-error').textContent = 'Para contratos temporales, la duración debe ser entre 1 y 6 meses (30 a 180 días)';
                        document.getElementById('fechafin-error').style.display = 'block';
                        return false;
                    }
                    break;

                case 'EVENTUAL':
                    // Validar que esté entre 1 y 30 días
                    if (diferenciaDias < 1 || diferenciaDias > 30) {
                        fechaFinInput.classList.add('is-invalid');
                        document.getElementById('fechafin-error').textContent = 'Para contratos eventuales, la duración debe ser entre 1 y 30 días';
                        document.getElementById('fechafin-error').style.display = 'block';
                        return false;
                    }
                    break;
            }

            // Si llegamos aquí, la fecha es válida
            fechaFinInput.classList.add('is-valid');
            return true;
        }

        // Validar el tipo de contrato
        tipoContratoSelect.addEventListener('change', function() {
            if (!this.value) {
                this.classList.add('is-invalid');
                document.getElementById('tipocontrato-error').textContent = 'Debe seleccionar un tipo de contrato';
                document.getElementById('tipocontrato-error').style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                document.getElementById('tipocontrato-error').style.display = 'none';

                // Actualizar estado de fecha fin
                actualizarEstadoFechaFin();
            }
        });

        // Validar el formulario al enviar
        const form = document.getElementById('formEditarInfoContrato');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            let isValid = true;

            // Validar tipo de contrato
            if (!tipoContratoSelect.value) {
                tipoContratoSelect.classList.add('is-invalid');
                document.getElementById('tipocontrato-error').textContent = 'Debe seleccionar un tipo de contrato';
                document.getElementById('tipocontrato-error').style.display = 'block';
                isValid = false;
            } else {
                tipoContratoSelect.classList.remove('is-invalid');
                tipoContratoSelect.classList.add('is-valid');
                document.getElementById('tipocontrato-error').style.display = 'none';
            }

            // Validar fecha inicio
            if (!fechaInicioInput.value) {
                fechaInicioInput.classList.add('is-invalid');
                document.getElementById('fechainicio-error').textContent = 'La fecha de inicio es requerida';
                document.getElementById('fechainicio-error').style.display = 'block';
                isValid = false;
            } else {
                const fechaInicio = new Date(fechaInicioInput.value);
                const fechaHoy = new Date();
                fechaHoy.setHours(0, 0, 0, 0); // Normalizar a inicio del día

                if (fechaInicio > fechaHoy) {
                    fechaInicioInput.classList.add('is-invalid');
                    document.getElementById('fechainicio-error').textContent = 'La fecha de inicio no puede ser futura';
                    document.getElementById('fechainicio-error').style.display = 'block';
                    isValid = false;
                } else {
                    fechaInicioInput.classList.remove('is-invalid');
                    fechaInicioInput.classList.add('is-valid');
                    document.getElementById('fechainicio-error').style.display = 'none';
                }
            }

            // Validar fecha fin según tipo de contrato
            if (tipoContratoSelect.value !== 'INDEFINIDO') {
                if (!validarFechaFin()) {
                    isValid = false;
                }
            }

            if (!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el formulario',
                    text: 'Por favor, corrija los errores en el formulario antes de continuar.'
                });
                return;
            }

            // Mostrar loader
            Swal.fire({
                title: 'Guardando...',
                text: 'Por favor espere mientras se actualizan los datos del contrato.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Preparar los datos del formulario
            const formData = new FormData(this);

            // Asegurar que se mande NULL en fechafin si es contrato indefinido
            if (tipoContratoSelect.value === 'INDEFINIDO') {
                formData.set('fechafin', '');
            }

            // Enviar formulario mediante AJAX
            fetch('../../../controllers/contrato.controller.php?op=registrar', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error en la respuesta del servidor: ${response.status}`);
                    }
                    return response.json();
                })
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
                            text: data.mensaje || 'La información del contrato ha sido actualizada correctamente.'
                        }).then(() => {
                            // Recargar la lista de doctores o la información actualizada
                            if (window.parent && typeof window.parent.cargarDoctores === 'function') {
                                window.parent.cargarDoctores();
                            } else {
                                // Si no hay función de recarga, recargar la página completa
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
        });

        // Validar estado inicial de los campos
        if (tipoContratoSelect.value) {
            tipoContratoSelect.classList.add('is-valid');
        }

        if (fechaInicioInput.value) {
            fechaInicioInput.classList.add('is-valid');
        }

        // Solo validar fecha fin si no es contrato indefinido y tiene valor
        if (tipoContratoSelect.value !== 'INDEFINIDO' && fechaFinInput.value) {
            validarFechaFin();
        }
    });
</script>

<style>
    .required-field::after {
        content: " *";
        color: #dc3545;
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
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    /* Estilos para los radio buttons */
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .form-check-input:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .form-check-label {
        margin-bottom: 0;
    }
</style>