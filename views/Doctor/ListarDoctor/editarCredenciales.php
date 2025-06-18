<?php /*RUTA: sistemasclinica/views/Doctor/ListarDoctor/editarCredenciales.php*/ ?>

<?php
// Verificar que se reciba el nrodoc
if (!isset($_GET['nrodoc']) || empty($_GET['nrodoc'])) {
    echo '<div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            No se ha especificado el número de documento del doctor.
          </div>';
    exit;
}

$nrodoc = $_GET['nrodoc'];

// Incluir modelos
require_once '../../../models/Doctor.php';
require_once '../../../models/Usuario.php';

$doctor = new Doctor();
$usuario = new Usuario();

// Obtener datos del doctor
$infoDoctor = $doctor->obtenerDoctorPorNroDoc($nrodoc);
if (!$infoDoctor) {
    echo '<div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Doctor no encontrado.
          </div>';
    exit;
}

// Obtener datos del usuario asociado al colaborador
$infoUsuario = $usuario->obtenerUsuarioPorColaborador($infoDoctor['idcolaborador'] ?? 0);

// Priorizar el email del doctor en lugar del nombre de usuario
$emailPredeterminado = '';
if (isset($infoDoctor['email']) && !empty($infoDoctor['email'])) {
    $emailPredeterminado = $infoDoctor['email'];
} elseif ($infoUsuario && isset($infoUsuario['nomuser']) && !empty($infoUsuario['nomuser'])) {
    $emailPredeterminado = $infoUsuario['nomuser'];
}
?>

<div id="mensajeResultadoCredenciales"></div>

<form id="editarCredencialesFormDoctor" class="needs-validation" novalidate>
    <input type="hidden" name="idcolaborador" value="<?php echo htmlspecialchars($infoDoctor['idcolaborador'] ?? ''); ?>">
    <input type="hidden" name="idusuario" value="<?php echo htmlspecialchars($infoUsuario['idusuario'] ?? ''); ?>">
    <input type="hidden" name="nrodoc" value="<?php echo htmlspecialchars($nrodoc); ?>">

    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Actualice las credenciales de acceso del doctor. Deje los campos de contraseña en blanco si no desea cambiarla.
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <label for="emailCredencialesDoctor" class="form-label">Correo Electrónico / Usuario <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" class="form-control" id="emailCredencialesDoctor" name="email"
                    value="<?php echo htmlspecialchars($emailPredeterminado); ?>" readonly required>
                <button type="button" class="btn btn-primary" id="btnEditarEmailDoctor">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </div>
            <div class="form-text text-muted">
                El correo electrónico se usa como nombre de usuario. Haga clic en "Editar" para modificarlo.
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <label for="nuevaPasswordDoctor" class="form-label">Nueva Contraseña</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" id="nuevaPasswordDoctor" name="nuevaPassword" minlength="6">
                <button class="btn btn-outline-secondary" type="button" id="togglePasswordDoctor">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <div class="form-text text-muted">
                La contraseña debe tener al menos 6 caracteres.
            </div>
            <div class="invalid-feedback">
                Por favor ingrese una contraseña de al menos 6 caracteres.
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <label for="confirmarPasswordDoctor" class="form-label">Confirmar Contraseña</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" id="confirmarPasswordDoctor" name="confirmarPassword">
                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPasswordDoctor">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <div class="invalid-feedback">
                Las contraseñas no coinciden.
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12 text-end">
            <button type="button" class="btn btn-secondary" id="btnCancelarCredencialesDoctor">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary" id="btnSubmitCredencialesDoctor">
                <i class="fas fa-save me-1"></i> Actualizar Credenciales
            </button>
        </div>
    </div>
</form>

<script>
    (function() {
        'use strict';

        // Variable para evitar múltiples inicializaciones
        let credencialesIniciado = false;

        // Función para inicializar credenciales solo una vez
        function initializeCredencialesDoctor() {
            // Evitar múltiples inicializaciones
            if (credencialesIniciado) {
                return;
            }
            credencialesIniciado = true;

            console.log('Inicializando credenciales del doctor...');

            // BOTÓN CANCELAR - Cerrar modal
            const btnCancelar = document.getElementById('btnCancelarCredencialesDoctor');
            if (btnCancelar) {
                btnCancelar.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Evitar propagación del evento
                    console.log('Cancelar clickeado');
                    cerrarModal();
                });
            }

            // BOTÓN EDITAR EMAIL
            const btnEditarEmail = document.getElementById('btnEditarEmailDoctor');
            const emailInput = document.getElementById('emailCredencialesDoctor');

            if (btnEditarEmail && emailInput) {
                btnEditarEmail.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Evitar propagación del evento
                    console.log('Editar email clickeado');

                    try {
                        if (emailInput.readOnly) {
                            emailInput.readOnly = false;
                            emailInput.focus();
                            emailInput.select();
                            this.innerHTML = '<i class="fas fa-lock"></i> Bloquear';
                            this.classList.remove('btn-primary');
                            this.classList.add('btn-danger');
                        } else {
                            emailInput.readOnly = true;
                            this.innerHTML = '<i class="fas fa-edit"></i> Editar';
                            this.classList.remove('btn-danger');
                            this.classList.add('btn-primary');
                        }
                    } catch (error) {
                        console.error('Error al editar email:', error);
                        mostrarMensaje('Error al modificar el campo de email', 'danger');
                    }
                });
            }

            // MOSTRAR/OCULTAR CONTRASEÑA
            const togglePassword = document.getElementById('togglePasswordDoctor');
            const passwordInput = document.getElementById('nuevaPasswordDoctor');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Evitar propagación del evento
                    console.log('Toggle password clickeado');

                    try {
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);

                        const icon = this.querySelector('i');
                        if (icon) {
                            icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
                        }
                    } catch (error) {
                        console.error('Error al mostrar/ocultar contraseña:', error);
                    }
                });
            }

            // MOSTRAR/OCULTAR CONFIRMAR CONTRASEÑA
            const toggleConfirmPassword = document.getElementById('toggleConfirmPasswordDoctor');
            const confirmPasswordInput = document.getElementById('confirmarPasswordDoctor');

            if (toggleConfirmPassword && confirmPasswordInput) {
                toggleConfirmPassword.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Evitar propagación del evento
                    console.log('Toggle confirm password clickeado');

                    try {
                        const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        confirmPasswordInput.setAttribute('type', type);

                        const icon = this.querySelector('i');
                        if (icon) {
                            icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
                        }
                    } catch (error) {
                        console.error('Error al mostrar/ocultar confirmación:', error);
                    }
                });
            }

            // VALIDACIÓN DE CONTRASEÑAS EN TIEMPO REAL
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const confirmInput = document.getElementById('confirmarPasswordDoctor');

                    if (this.value.length >= 6 || this.value.length === 0) {
                        this.classList.remove('is-invalid');
                        if (this.value.length >= 6) {
                            this.classList.add('is-valid');
                        } else {
                            this.classList.remove('is-valid');
                        }
                    } else {
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                    }

                    // Validar confirmación si ya tiene valor
                    if (confirmInput && confirmInput.value) {
                        if (confirmInput.value === this.value) {
                            confirmInput.classList.add('is-valid');
                            confirmInput.classList.remove('is-invalid');
                        } else {
                            confirmInput.classList.add('is-invalid');
                            confirmInput.classList.remove('is-valid');
                        }
                    }
                });
            }

            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('input', function() {
                    const passwordInput = document.getElementById('nuevaPasswordDoctor');

                    if (this.value === passwordInput.value) {
                        this.classList.add('is-valid');
                        this.classList.remove('is-invalid');
                    } else {
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                    }
                });
            }

            // ENVÍO DEL FORMULARIO - MANEJADOR ÚNICO
            const form = document.getElementById('editarCredencialesFormDoctor');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Evitar propagación del evento
                    console.log('Formulario de credenciales enviado');
                    enviarFormularioCredenciales();
                });
            }
        }

        function cerrarModal() {
            try {
                if (window.parent && window.parent.bootstrap) {
                    const modal = window.parent.bootstrap.Modal.getInstance(
                        window.parent.document.getElementById('modalCredenciales')
                    );
                    if (modal) modal.hide();
                }
            } catch (error) {
                console.error('Error al cerrar modal:', error);
            }
        }

        function validarEmail(email) {
            const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            return re.test(email);
        }

        function mostrarMensaje(mensaje, tipo) {
            const mensajeDiv = document.getElementById('mensajeResultadoCredenciales');
            if (mensajeDiv) {
                mensajeDiv.innerHTML = `<div class="alert alert-${tipo} alert-dismissible fade show">
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
            }
        }

        // ✅ FUNCIÓN CORREGIDA - ESTA ES LA CLAVE DEL CAMBIO
        function enviarFormularioCredenciales() {
            try {
                console.log('Iniciando envío de formulario de credenciales...');

                // Validar campos
                const emailInput = document.getElementById('emailCredencialesDoctor');
                const passwordInput = document.getElementById('nuevaPasswordDoctor');
                const confirmPasswordInput = document.getElementById('confirmarPasswordDoctor');

                if (!emailInput || !emailInput.value.trim()) {
                    mostrarMensaje('El correo electrónico es obligatorio', 'danger');
                    if (emailInput) emailInput.focus();
                    return false;
                }

                if (!validarEmail(emailInput.value.trim())) {
                    mostrarMensaje('Por favor ingrese un correo electrónico válido', 'danger');
                    emailInput.focus();
                    return false;
                }

                // Si se ingresó una contraseña, validarla
                if (passwordInput && passwordInput.value.trim()) {
                    if (passwordInput.value.trim().length < 6) {
                        mostrarMensaje('La contraseña debe tener al menos 6 caracteres', 'danger');
                        passwordInput.focus();
                        return false;
                    }

                    if (passwordInput.value !== confirmPasswordInput.value) {
                        mostrarMensaje('Las contraseñas no coinciden', 'danger');
                        confirmPasswordInput.focus();
                        return false;
                    }
                }

                // Preparar FormData
                const form = document.getElementById('editarCredencialesFormDoctor');
                const formData = new FormData(form);

                // Deshabilitar botón de submit para evitar múltiples envíos
                const submitBtn = document.getElementById('btnSubmitCredencialesDoctor');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Guardando...';

                // Limpiar mensajes previos
                mostrarMensaje('', 'info');

                console.log('Enviando datos al servidor...');

                // Crear AbortController para cancelar solicitudes duplicadas
                if (window.credencialesAbortController) {
                    window.credencialesAbortController.abort();
                }
                window.credencialesAbortController = new AbortController();

                fetch('../../../controllers/credencial.controller.php?op=actualizar_credenciales', {
                        method: 'POST',
                        body: formData,
                        signal: window.credencialesAbortController.signal
                    })
                    .then(response => {
                        console.log('Respuesta recibida:', response);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Datos procesados:', data);

                        // Rehabilitar botón
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;

                        if (data.status) {
                            // ✅ SOLUCIÓN: Cerrar modal inmediatamente y mostrar SweetAlert desde ventana padre
                            cerrarModal();

                            // Mostrar SweetAlert desde la ventana padre
                            if (window.parent && window.parent.Swal) {
                                window.parent.Swal.fire({
                                    icon: 'success',
                                    title: 'Credenciales Actualizadas',
                                    text: data.mensaje || 'Las credenciales se actualizaron correctamente',
                                    timer: 3000,
                                    timerProgressBar: true,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Entendido'
                                }).then(() => {
                                    // Actualizar lista en la ventana padre después de cerrar la alerta
                                    if (window.parent && typeof window.parent.cargarDoctores === 'function') {
                                        window.parent.cargarDoctores();
                                    }
                                });
                            } else {
                                // Fallback si no está disponible SweetAlert en ventana padre
                                if (window.parent) {
                                    window.parent.alert(data.mensaje || 'Credenciales actualizadas correctamente');
                                    if (typeof window.parent.cargarDoctores === 'function') {
                                        window.parent.cargarDoctores();
                                    }
                                }
                            }
                        } else {
                            mostrarMensaje(data.mensaje, 'danger');
                        }
                    })
                    .catch(error => {
                        // Solo mostrar error si no fue cancelado por AbortController
                        if (error.name !== 'AbortError') {
                            console.error('Error en la solicitud:', error);

                            // Rehabilitar botón
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;

                            mostrarMensaje('Error al conectar con el servidor: ' + error.message, 'danger');
                        }
                    });

            } catch (error) {
                console.error('Error al enviar formulario:', error);
                mostrarMensaje('Ocurrió un error inesperado', 'danger');
            }
        }

        // Inicializar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeCredencialesDoctor);
        } else {
            // Si el DOM ya está listo, inicializar inmediatamente
            initializeCredencialesDoctor();
        }

    })();
</script>