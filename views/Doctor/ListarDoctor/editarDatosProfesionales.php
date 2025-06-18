<?php /*RUTA: sistemasclinica/views/Doctor/ListarDoctor/editarDatosProfesionales.php*/ ?>
<?php
// Este archivo carga el formulario para editar los datos profesionales del doctor (especialidad)

// Verificar que se haya proporcionado un número de documento
if (!isset($_GET['nrodoc']) || empty($_GET['nrodoc'])) {
    echo '<div class="alert alert-danger">Número de documento no proporcionado.</div>';
    exit;
}

$nrodoc = $_GET['nrodoc'];

// Incluir el modelo de Doctor y Especialidad
require_once '../../../models/Doctor.php';
require_once '../../../models/Especialidad.php';

$doctor = new Doctor();
$especialidad = new Especialidad();

// Obtener la información del doctor
$infoDoctor = $doctor->obtenerDoctorPorNroDoc($nrodoc);

if (!$infoDoctor) {
    echo '<div class="alert alert-danger">Doctor no encontrado.</div>';
    exit;
}

// Obtener todas las especialidades para el select
try {
    $especialidades = $especialidad->listarEspecialidades();
    if (!$especialidades) {
        $especialidades = [];
    }
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error al cargar especialidades: ' . $e->getMessage() . '</div>';
    $especialidades = [];
}
?>

<!-- Asegúrate de que Bootstrap esté cargado correctamente -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<p class="text-center mb-4">
    <span class="badge bg-primary fs-6">Doctor: <?= htmlspecialchars($infoDoctor['nombres'] . ' ' . $infoDoctor['apellidos']) ?></span>
</p>

<form id="formEditarDatosProfesionales" method="POST">
    <input type="hidden" name="operacion" value="actualizar_profesional">
    <input type="hidden" name="nrodoc" value="<?= htmlspecialchars($nrodoc) ?>">
    <input type="hidden" name="idcolaborador" value="<?= htmlspecialchars($infoDoctor['idcolaborador'] ?? '') ?>">
    <input type="hidden" name="idpersona" value="<?= htmlspecialchars($infoDoctor['idpersona'] ?? '') ?>">

    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Actualización de Datos Profesionales</strong>
                <p class="mb-0">Desde aquí puede modificar la especialidad del doctor.</p>
            </div>
        </div>
    </div>

    <!-- Especialidad -->
    <div class="row mb-4">
        <div class="col-md-12">
            <label for="especialidad" class="form-label required-field">Especialidad</label>
            <select class="form-select" id="especialidad" name="idespecialidad" required>
                <option value="">Seleccione...</option>
                <?php if(!empty($especialidades)): ?>
                    <?php foreach($especialidades as $esp): ?>
                        <option value="<?= htmlspecialchars($esp['idespecialidad']) ?>" 
                                <?= (isset($infoDoctor['idespecialidad']) && $infoDoctor['idespecialidad'] == $esp['idespecialidad']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($esp['especialidad']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>No hay especialidades disponibles</option>
                <?php endif; ?>
            </select>
            <div id="help-especialidad" class="form-text">Seleccione la especialidad médica del doctor.</div>
            <div class="invalid-feedback" id="especialidad-error"></div>
            
            <!-- Campo oculto para almacenar precio de atención -->
            <input type="hidden" id="precioatencion" name="precioatencion" value="<?= htmlspecialchars($infoDoctor['precioatencion'] ?? '0') ?>">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <label for="precio_display" class="form-label">Precio de Atención</label>
            <div class="input-group">
                <span class="input-group-text">S/.</span>
                <input type="text" class="form-control" id="precio_display" value="<?= htmlspecialchars(number_format((float)($infoDoctor['precioatencion'] ?? 0), 2, '.', '')) ?>" readonly>
            </div>
            <div class="form-text">Este precio se aplicará a las consultas con este doctor.</div>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Asegurar que Bootstrap esté cargado
        function asegurarBootstrap() {
            if (typeof bootstrap === 'undefined') {
                console.warn('Bootstrap no está definido. Intentando cargar bootstrap.bundle.min.js');
                
                // Crear elemento script y añadirlo al head
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js';
                script.onload = function() {
                    console.log('Bootstrap cargado correctamente');
                    // Reinicializar los eventos después de cargar Bootstrap
                    inicializarEventos();
                };
                script.onerror = function() {
                    console.error('Error al cargar Bootstrap');
                    alert('Error al cargar componentes necesarios. Por favor, actualice la página.');
                };
                
                document.head.appendChild(script);
                return false;
            }
            return true;
        }

        // Función para inicializar todos los eventos
        function inicializarEventos() {
            // Referencias a elementos
            const especialidadSelect = document.getElementById('especialidad');
            const precioatencionInput = document.getElementById('precioatencion');
            const precioDisplayInput = document.getElementById('precio_display');
            
            // Función para validar la especialidad
            function validateEspecialidad(select) {
                if (!select.value) {
                    markFieldAsInvalid(select);
                    document.getElementById('especialidad-error').textContent = 'Debe seleccionar una especialidad';
                    document.getElementById('especialidad-error').style.display = 'block';
                    return false;
                } else {
                    markFieldAsValid(select);
                    document.getElementById('especialidad-error').textContent = '';
                    document.getElementById('especialidad-error').style.display = 'none';
                    return true;
                }
            }
            
            // Validar especialidad cuando cambie
            especialidadSelect.addEventListener('change', function() {
                validateEspecialidad(this);
                
                // Cargar precio automático si se selecciona una especialidad
                if (this.value) {
                    cargarPrecioEspecialidad(this.value);
                }
            });
            
            // Función para cargar precio de especialidad
            function cargarPrecioEspecialidad(idEspecialidad) {
                fetch(`../../../controllers/especialidad.controller.php?op=obtener&id=${idEspecialidad}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Error en la respuesta del servidor: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status && data.data) {
                            const precio = data.data.precioatencion || '0';
                            precioatencionInput.value = precio;
                            precioDisplayInput.value = parseFloat(precio).toFixed(2);
                            console.log("Precio actualizado:", precio);
                        } else {
                            console.error("Error al obtener precio:", data.mensaje || "Error desconocido");
                            showErrorToast("No se pudo obtener el precio de la especialidad");
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showErrorToast("Error al cargar el precio: " + error.message);
                    });
            }

            // Validar el formulario al enviar
            const formDatosProfesionales = document.getElementById('formEditarDatosProfesionales');
            if (formDatosProfesionales) {
                formDatosProfesionales.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    let isValid = true;
                    
                    // Validar especialidad
                    if (!validateEspecialidad(document.getElementById('especialidad'))) {
                        isValid = false;
                    }
                    
                    // Validar que haya un precio válido
                    const precio = parseFloat(precioatencionInput.value);
                    if (isNaN(precio) || precio <= 0) {
                        isValid = false;
                        showErrorToast('El precio de atención debe ser mayor a cero');
                    }
                    
                    if (!isValid) {
                        showErrorToast('Por favor, corrija los errores en el formulario');
                        return false;
                    }
                    
                    // Mostrar loader
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Guardando...',
                            text: 'Por favor espere mientras se actualizan los datos.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    }
                    
                    // Enviar formulario mediante AJAX
                    const formData = new FormData(this);
                    
                    // Asegurar que se envíe el precio
                    formData.append('precioatencion', precioatencionInput.value);
                    
                    fetch('../../../controllers/doctor.controller.php?op=actualizar', {
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
                        if (typeof Swal !== 'undefined') Swal.close();
                        
                        if (data.status) {
                            // Cerrar modal primero
                            if (window.parent && window.parent.bootstrap) {
                                const modal = window.parent.bootstrap.Modal.getInstance(window.parent.document.getElementById('modalDatosProfesionales'));
                                if (modal) {
                                    modal.hide();
                                }
                            }
                            
                            // Mostrar alerta temporal sin botón OK que se cierre automáticamente
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: 'Datos profesionales actualizados correctamente',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    timerProgressBar: true
                                }).then(() => {
                                    // Recargar la lista de doctores
                                    if (window.parent && typeof window.parent.cargarDoctores === 'function') {
                                        window.parent.cargarDoctores();
                                    }
                                });
                            } else {
                                // Si SweetAlert no está disponible, mostrar mensaje y recargar
                                alert('Los datos profesionales han sido actualizados correctamente.');
                                if (window.parent && typeof window.parent.cargarDoctores === 'function') {
                                    window.parent.cargarDoctores();
                                } else {
                                    window.parent.location.reload();
                                }
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.mensaje || 'No se pudieron guardar los cambios.'
                                });
                            } else {
                                alert('Error: ' + (data.mensaje || 'No se pudieron guardar los cambios.'));
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (typeof Swal !== 'undefined') {
                            Swal.close();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de conexión',
                                text: 'No se pudo conectar con el servidor: ' + error.message
                            });
                        } else {
                            alert('Error de conexión: ' + error.message);
                        }
                    });
                });
            }
            
            // Funciones de utilidad para marcar campos como válidos/inválidos
            function markFieldAsValid(field) {
                field.classList.add('is-valid');
                field.classList.remove('is-invalid');
            }

            function markFieldAsInvalid(field) {
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
            }

            // Funciones para mostrar notificaciones
            window.showSuccessToast = function(message) {
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.onmouseenter = Swal.stopTimer;
                            toast.onmouseleave = Swal.resumeTimer;
                        }
                    });

                    Toast.fire({
                        icon: 'success',
                        title: message
                    });
                } else {
                    console.log('Éxito:', message);
                    alert('Éxito: ' + message);
                }
            }

            window.showErrorToast = function(message) {
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.onmouseenter = Swal.stopTimer;
                            toast.onmouseleave = Swal.resumeTimer;
                        }
                    });

                    Toast.fire({
                        icon: 'error',
                        title: message
                    });
                } else {
                    console.error('Error:', message);
                    alert('Error: ' + message);
                }
            }
            
            // Validar la especialidad inicialmente
            validateEspecialidad(especialidadSelect);
        }

        // Verificar si SweetAlert está disponible
        if (typeof Swal === 'undefined') {
            console.warn('SweetAlert no está definido. Los mensajes se mostrarán como alertas normales.');
        }

        // Verificar Bootstrap y luego inicializar eventos
        if (asegurarBootstrap()) {
            inicializarEventos();
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
</style>