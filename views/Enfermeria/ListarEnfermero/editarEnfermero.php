<?php
// Verificar que se reciba el ID del enfermero
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            No se ha especificado el ID del enfermero.
          </div>';
    exit;
}

$idEnfermero = intval($_GET['id']);

// Incluir el modelo de enfermería
require_once '../../../models/Enfermeria.php';

// Obtener los datos del enfermero
$enfermeria = new Enfermeria();
$resultado = $enfermeria->obtenerEnfermeroPorId($idEnfermero);

if (!$resultado['status']) {
    echo '<div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            ' . $resultado['mensaje'] . '
          </div>';
    exit;
}

$enfermero = $resultado['data'];
?>

<form id="editarEnfermeroForm" class="needs-validation" novalidate>
    <input type="hidden" name="idcolaborador" value="<?php echo $enfermero['idcolaborador']; ?>">
    <input type="hidden" name="idpersona" value="<?php echo $enfermero['idpersona']; ?>">

    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Actualice la información del enfermero. Los campos marcados con <span class="text-danger">*</span> son obligatorios. Los campos sombreados no son editables.
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($enfermero['apellidos']); ?>" required>
            <div class="invalid-feedback">
                Por favor ingrese los apellidos.
            </div>
        </div>
        <div class="col-md-6">
            <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nombres" name="nombres" value="<?php echo htmlspecialchars($enfermero['nombres']); ?>" required>
            <div class="invalid-feedback">
                Por favor ingrese los nombres.
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label for="tipodoc" class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
            <select class="form-select bg-light" id="tipodoc" disabled>
                <option value="">Seleccione...</option>
                <option value="DNI" <?php echo ($enfermero['tipodoc'] == 'DNI') ? 'selected' : ''; ?>>DNI</option>
                <option value="PASAPORTE" <?php echo ($enfermero['tipodoc'] == 'PASAPORTE') ? 'selected' : ''; ?>>Pasaporte</option>
                <option value="CARNET DE EXTRANJERIA" <?php echo ($enfermero['tipodoc'] == 'CARNET DE EXTRANJERIA') ? 'selected' : ''; ?>>Carnet de Extranjería</option>
                <option value="OTRO" <?php echo ($enfermero['tipodoc'] == 'OTRO') ? 'selected' : ''; ?>>Otro</option>
            </select>
            <!-- Campo oculto para mantener el valor -->
            <input type="hidden" name="tipodoc" value="<?php echo htmlspecialchars($enfermero['tipodoc']); ?>">
        </div>
        <div class="col-md-4">
            <label for="nrodoc" class="form-label">Número de Documento <span class="text-danger">*</span></label>
            <input type="text" class="form-control bg-light" id="nrodoc" value="<?php echo htmlspecialchars($enfermero['nrodoc']); ?>" disabled>
            <!-- Campo oculto para mantener el valor -->
            <input type="hidden" name="nrodoc" value="<?php echo htmlspecialchars($enfermero['nrodoc']); ?>">
        </div>
        <div class="col-md-4">
            <label for="genero" class="form-label">Género <span class="text-danger">*</span></label>
            <select class="form-select bg-light" id="genero" disabled>
                <option value="">Seleccione...</option>
                <option value="M" <?php echo ($enfermero['genero'] == 'M') ? 'selected' : ''; ?>>Masculino</option>
                <option value="F" <?php echo ($enfermero['genero'] == 'F') ? 'selected' : ''; ?>>Femenino</option>
                <option value="OTRO" <?php echo ($enfermero['genero'] == 'OTRO') ? 'selected' : ''; ?>>Otro</option>
            </select>
            <!-- Campo oculto para mantener el valor -->
            <input type="hidden" name="genero" value="<?php echo htmlspecialchars($enfermero['genero']); ?>">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
            <input type="tel" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($enfermero['telefono']); ?>" required>
            <div class="invalid-feedback">
                Por favor ingrese un número de teléfono válido.
            </div>
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
            <input type="email" class="form-control bg-light" id="email" value="<?php echo htmlspecialchars($enfermero['email']); ?>" disabled>
            <!-- Campo oculto para mantener el valor -->
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($enfermero['email']); ?>">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="fechanacimiento" class="form-label">Fecha de Nacimiento</label>
            <input type="date" class="form-control bg-light" id="fechanacimiento" value="<?php echo $enfermero['fechanacimiento']; ?>" disabled>
            <!-- Campo oculto para mantener el valor -->
            <input type="hidden" name="fechanacimiento" value="<?php echo $enfermero['fechanacimiento']; ?>">
        </div>
        <div class="col-md-6">
            <label for="direccion" class="form-label">Dirección</label>
            <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo htmlspecialchars($enfermero['direccion'] ?? ''); ?>">
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12 text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="button" id="btnGuardarCambios" class="btn btn-success">
                <i class="fas fa-save me-1"></i> Guardar Cambios
            </button>
        </div>
    </div>
</form>

<script>
// Validación adicional para teléfono (solo números)
document.getElementById('telefono').addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '');
    if (this.value.length > 15) {
        this.value = this.value.slice(0, 15);
    }
});

// IMPORTANTE: Configurar el botón para enviar el formulario
document.getElementById('btnGuardarCambios').addEventListener('click', function() {
    // Validar formulario antes de enviar
    const form = document.getElementById('editarEnfermeroForm');
    
    if (!validarFormulario()) {
        return false;
    }
    
    // Mostrar loading
    Swal.fire({
        title: 'Procesando',
        text: 'Guardando información...',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Enviar formulario vía AJAX
    const formData = new FormData(form);
    
    $.ajax({
        url: 'procesarEdicion.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(data) {
            Swal.close();
            
            if (data.status) {
                // Cerrar el modal
                bootstrap.Modal.getInstance(document.getElementById('modalEditarEnfermero')).hide();
                
                // Mostrar mensaje de éxito
                Swal.fire({
                    icon: 'success',
                    title: 'Actualización exitosa',
                    text: data.mensaje || 'Información actualizada correctamente',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    // Recargar la tabla
                    if (typeof cargarEnfermeros === 'function') {
                        cargarEnfermeros();
                    } else {
                        window.location.reload();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.mensaje || "Error al actualizar la información.",
                    confirmButtonColor: '#3085d6'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.close();
            console.error("Error AJAX:", xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "Error al procesar la solicitud: " + error,
                confirmButtonColor: '#3085d6'
            });
        }
    });
});

function validarFormulario() {
    const form = document.getElementById('editarEnfermeroForm');
    const campos = ['apellidos', 'nombres', 'telefono'];
    let valido = true;
    
    campos.forEach(campo => {
        const elemento = form.elements[campo];
        if (elemento && !elemento.value.trim()) {
            elemento.classList.add('is-invalid');
            valido = false;
        } else if (elemento) {
            elemento.classList.remove('is-invalid');
            elemento.classList.add('is-valid');
        }
    });
    
    if (!valido) {
        Swal.fire({
            icon: 'error',
            title: 'Campos incompletos',
            text: "Por favor complete todos los campos obligatorios correctamente.",
            confirmButtonColor: '#3085d6'
        });
    }
    
    return valido;
}
</script>