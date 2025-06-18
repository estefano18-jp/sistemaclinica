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

<form id="editarCredencialesForm" class="needs-validation" novalidate>
    <input type="hidden" name="idcolaborador" value="<?php echo $enfermero['idcolaborador']; ?>">
    <input type="hidden" name="idusuario" value="<?php echo $enfermero['idusuario']; ?>">

    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Actualice las credenciales de acceso del enfermero. Deje los campos de contraseña en blanco si no desea cambiarla.
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <label for="email" class="form-label">Correo Electrónico / Usuario <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($enfermero['email']); ?>" readonly>
            <div class="form-text text-muted">
                El correo electrónico se usa como nombre de usuario y no puede ser modificado desde aquí.
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <label for="nuevaPassword" class="form-label">Nueva Contraseña</label>
            <div class="input-group">
                <input type="password" class="form-control" id="nuevaPassword" name="nuevaPassword" minlength="6">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
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
            <label for="confirmarPassword" class="form-label">Confirmar Contraseña</label>
            <div class="input-group">
                <input type="password" class="form-control" id="confirmarPassword" name="confirmarPassword">
                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
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
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-info">
                <i class="fas fa-save me-1"></i> Actualizar Credenciales
            </button>
        </div>
    </div>
</form>

<script>
// Mostrar/ocultar contraseña
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('nuevaPassword');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    
    // Cambiar el ícono del botón
    this.querySelector('i').className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
});

document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('confirmarPassword');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    
    // Cambiar el ícono del botón
    this.querySelector('i').className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
});

// Validación de contraseñas
document.getElementById('nuevaPassword').addEventListener('input', function() {
    const confirmPasswordInput = document.getElementById('confirmarPassword');
    
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
    if (confirmPasswordInput.value) {
        if (confirmPasswordInput.value === this.value) {
            confirmPasswordInput.classList.add('is-valid');
            confirmPasswordInput.classList.remove('is-invalid');
        } else {
            confirmPasswordInput.classList.add('is-invalid');
            confirmPasswordInput.classList.remove('is-valid');
        }
    }
});

document.getElementById('confirmarPassword').addEventListener('input', function() {
    const passwordInput = document.getElementById('nuevaPassword');
    
    if (this.value === passwordInput.value) {
        this.classList.add('is-valid');
        this.classList.remove('is-invalid');
    } else {
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
    }
});
</script>