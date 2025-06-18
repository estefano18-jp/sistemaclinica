<?php /*RUTA: sistemasclinica/views/Doctor/ListarDoctor/editarInfoDoctor.php*/ ?>
<?php
// Este archivo carga el formulario de información personal del doctor para mostrarlo en un modal

require_once '../../../models/Doctor.php';

// Verificar que se haya proporcionado un número de documento
if (!isset($_GET['nrodoc']) || empty($_GET['nrodoc'])) {
    echo '<div class="alert alert-danger">Número de documento no proporcionado.</div>';
    exit;
}

$nrodoc = $_GET['nrodoc'];

$doctor = new Doctor();

// Obtener la información del doctor
$infoDoctor = $doctor->obtenerDoctorPorNroDoc($nrodoc);

if (!$infoDoctor) {
    echo '<div class="alert alert-danger">Doctor no encontrado.</div>';
    exit;
}

// Obtener la fecha actual para establecer la fecha máxima
$fechaHoy = date('Y-m-d');

// Función para formatear la fecha de yyyy-mm-dd a dd/mm/yyyy para mostrar
function formatearFecha($fecha) {
    if (empty($fecha)) return '';
    $partes = explode('-', $fecha);
    if (count($partes) !== 3) return $fecha;
    return $partes[2] . '/' . $partes[1] . '/' . $partes[0];
}

// Obtener texto de género para mostrar
function obtenerTextoGenero($genero) {
    if ($genero == 'M') return 'Masculino';
    if ($genero == 'F') return 'Femenino';
    if ($genero == 'OTRO') return 'Otro';
    return '';
}
?>

<p class="text-center mb-4">
    <span class="badge bg-primary fs-6">Doctor: <?= htmlspecialchars($infoDoctor['apellidos'] . ', ' . $infoDoctor['nombres']) ?></span>
</p>

<form id="editarInformacionPersonalForm" method="POST">
    <input type="hidden" name="idpersona" value="<?= htmlspecialchars($infoDoctor['idpersona'] ?? '') ?>">
    <input type="hidden" name="idcolaborador" value="<?= htmlspecialchars($infoDoctor['idcolaborador'] ?? '') ?>">
    <input type="hidden" name="operacion" value="actualizar_doctor">
    <!-- Campos ocultos para enviar los valores originales de los campos bloqueados -->
    <input type="hidden" name="nrodoc" value="<?= htmlspecialchars($infoDoctor['nrodoc'] ?? '') ?>">
    <input type="hidden" name="fechanacimiento" value="<?= htmlspecialchars($infoDoctor['fechanacimiento'] ?? '') ?>">
    <input type="hidden" name="genero" value="<?= htmlspecialchars($infoDoctor['genero'] ?? '') ?>">
    <input type="hidden" name="email" value="<?= htmlspecialchars($infoDoctor['email'] ?? '') ?>">

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="apellidos" class="form-label">Apellidos</label>
            <input type="text" class="form-control" id="apellidos" name="apellidos"
                value="<?= htmlspecialchars($infoDoctor['apellidos'] ?? '') ?>" required>
            <div class="invalid-feedback" id="apellidos-error"></div>
        </div>
        <div class="col-md-6">
            <label for="nombres" class="form-label">Nombres</label>
            <input type="text" class="form-control" id="nombres" name="nombres"
                value="<?= htmlspecialchars($infoDoctor['nombres'] ?? '') ?>" required>
            <div class="invalid-feedback" id="nombres-error"></div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label for="tipodoc" class="form-label">Tipo de Documento</label>
            <input type="text" class="form-control" id="tipodoc_text" 
                value="<?= htmlspecialchars($infoDoctor['tipodoc'] ?? '') ?>" readonly>
            <!-- Mantener el valor original en un campo oculto -->
            <input type="hidden" name="tipodoc" id="tipodoc" 
                value="<?= htmlspecialchars($infoDoctor['tipodoc'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <label for="nrodoc_display" class="form-label">Número de Documento</label>
            <!-- Campo de solo lectura para mostrar el número de documento -->
            <input type="text" class="form-control" id="nrodoc_display"
                value="<?= htmlspecialchars($infoDoctor['nrodoc'] ?? '') ?>" readonly>
        </div>
        <div class="col-md-4">
            <label for="fechanacimiento_display" class="form-label">Fecha de Nacimiento</label>
            <!-- Campo de solo lectura para mostrar la fecha de nacimiento -->
            <input type="text" class="form-control" id="fechanacimiento_display"
                value="<?= formatearFecha($infoDoctor['fechanacimiento'] ?? '') ?>" readonly>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label for="genero_display" class="form-label">Género</label>
            <!-- Campo de solo lectura para mostrar el género -->
            <input type="text" class="form-control" id="genero_display"
                value="<?= obtenerTextoGenero($infoDoctor['genero'] ?? '') ?>" readonly>
        </div>
        <div class="col-md-4">
            <label for="telefono" class="form-label">Teléfono</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                <input type="tel" class="form-control" id="telefono" name="telefono"
                    value="<?= htmlspecialchars($infoDoctor['telefono'] ?? '') ?>"
                    pattern="^9\d{8}$" maxlength="9" title="El teléfono debe tener 9 dígitos y comenzar con 9" required>
            </div>
            <div class="invalid-feedback" id="telefono-error"></div>
        </div>
        <div class="col-md-4">
            <label for="email_display" class="form-label">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <!-- Campo de solo lectura para mostrar el email -->
                <input type="text" class="form-control" id="email_display"
                    value="<?= htmlspecialchars($infoDoctor['email'] ?? '') ?>" readonly>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <label for="direccion" class="form-label">Dirección</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                <input type="text" class="form-control" id="direccion" name="direccion"
                    value="<?= htmlspecialchars($infoDoctor['direccion'] ?? '') ?>" required>
            </div>
            <div class="invalid-feedback" id="direccion-error"></div>
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

<script>
    // Configuración para validaciones
    document.addEventListener('DOMContentLoaded', function() {
        // Referencias a los campos del formulario
        const apellidosInput = document.getElementById('apellidos');
        const nombresInput = document.getElementById('nombres');
        const telefonoInput = document.getElementById('telefono');
        const direccionInput = document.getElementById('direccion');
        
        // Función para validar campo
        function validarCampo(input, condicion, mensaje) {
            if (!condicion) {
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
                const errorElement = document.getElementById(`${input.id}-error`);
                if (errorElement) {
                    errorElement.textContent = mensaje;
                    errorElement.style.display = 'block';
                }
                return false;
            } else {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                const errorElement = document.getElementById(`${input.id}-error`);
                if (errorElement) {
                    errorElement.style.display = 'none';
                }
                return true;
            }
        }
        
        // Validación de teléfono
        function validarTelefono() {
            const patron = /^9\d{8}$/;
            return validarCampo(telefonoInput, patron.test(telefonoInput.value), 
                'El teléfono debe tener 9 dígitos y comenzar con 9');
        }
        
        // Configurar eventos de validación
        
        // Configurar validación de teléfono
        telefonoInput.addEventListener('input', function() {
            // Eliminar cualquier carácter que no sea un número
            this.value = this.value.replace(/\D/g, '');
            
            // Asegurar que comience con 9
            if (this.value.length > 0 && this.value.charAt(0) !== '9') {
                this.value = '9' + this.value.substring(1);
            }
            
            // Limitado a 9 dígitos
            if (this.value.length > 9) {
                this.value = this.value.substring(0, 9);
            }
        });
        
        telefonoInput.addEventListener('blur', validarTelefono);
        
        // Validar apellidos y nombres
        apellidosInput.addEventListener('blur', function() {
            validarCampo(apellidosInput, apellidosInput.value.trim() !== '', 'Los apellidos son obligatorios');
        });
        
        nombresInput.addEventListener('blur', function() {
            validarCampo(nombresInput, nombresInput.value.trim() !== '', 'Los nombres son obligatorios');
        });
        
        // Validar dirección
        direccionInput.addEventListener('blur', function() {
            validarCampo(direccionInput, direccionInput.value.trim() !== '', 'La dirección es obligatoria');
        });
        
        // Configurar envío del formulario
        const form = document.getElementById('editarInformacionPersonalForm');
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar todos los campos
            let isValid = true;
            
            // Validar apellidos
            isValid = validarCampo(apellidosInput, apellidosInput.value.trim() !== '', 
                'Los apellidos son obligatorios') && isValid;
            
            // Validar nombres
            isValid = validarCampo(nombresInput, nombresInput.value.trim() !== '', 
                'Los nombres son obligatorios') && isValid;
            
            // Validar teléfono
            isValid = validarTelefono() && isValid;
            
            // Validar dirección
            isValid = validarCampo(direccionInput, direccionInput.value.trim() !== '', 
                'La dirección es obligatoria') && isValid;
            
            if (!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: 'Por favor, corrija los errores en el formulario antes de continuar.'
                });
                return;
            }
            
            // Mostrar indicador de carga
            Swal.fire({
                title: 'Guardando...',
                text: 'Actualizando información del doctor',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Crear un objeto FormData con los datos del formulario
            const formData = new FormData(this);
            
            // Realizar la solicitud AJAX
            fetch('../../../controllers/doctor.controller.php?op=actualizar', {
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
                Swal.close();
                
                if (data.status) {
                    // Éxito
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: data.mensaje || 'Información del doctor actualizada correctamente',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Cerrar el modal si existe
                        if (window.parent && window.parent.bootstrap) {
                            const modal = window.parent.bootstrap.Modal.getInstance(window.parent.document.getElementById('modalInfoDoctor'));
                            if (modal) {
                                modal.hide();
                            }
                        }
                        
                        // Recargar la información
                        if (window.parent && typeof window.parent.cargarDoctores === 'function') {
                            window.parent.cargarDoctores();
                        } else {
                            // Si no hay función de recarga, recargar la página
                            window.parent.location.reload();
                        }
                    });
                } else {
                    // Error
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.mensaje || 'Error al actualizar la información del doctor',
                        confirmButtonText: 'Entendido'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo procesar la solicitud: ' + error.message,
                    confirmButtonText: 'Entendido'
                });
            });
        });
    });
</script>

<style>
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

    /* Estilo para campos de solo lectura */
    input[readonly] {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }
    
    .invalid-feedback {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }
    
    .form-label {
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .badge {
        padding: 0.5em 0.8em;
        font-size: 0.875rem;
    }
    
    .input-group {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        width: 100%;
    }
    
    .input-group-text {
        display: flex;
        align-items: center;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #212529;
        text-align: center;
        white-space: nowrap;
        background-color: #e9ecef;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
</style>