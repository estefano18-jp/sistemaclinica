<?php
// Este archivo carga el contenido del formulario de información personal para mostrarlo en un modal

require_once '../../../models/Paciente.php';

// Verificar que se haya proporcionado un ID de paciente
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<div class="alert alert-danger">ID de paciente no proporcionado.</div>';
    exit;
}

$idPaciente = $_GET['id'];
$paciente = new Paciente();

// Obtener la información del paciente
$infoPaciente = $paciente->obtenerPacientePorId($idPaciente);

if (!$infoPaciente) {
    echo '<div class="alert alert-danger">Paciente no encontrado.</div>';
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
    <span class="badge bg-primary fs-6">Paciente: <?= htmlspecialchars($infoPaciente['apellidos'] . ', ' . $infoPaciente['nombres']) ?></span>
</p>

<form id="editarInformacionPersonalForm" method="POST">
    <input type="hidden" name="id" value="<?= htmlspecialchars($idPaciente) ?>">
    <input type="hidden" name="operacion" value="actualizar">
    <input type="hidden" name="idpaciente" value="<?= htmlspecialchars($idPaciente) ?>">
    <!-- Campos ocultos para enviar los valores originales de los campos bloqueados -->
    <input type="hidden" name="nrodoc" value="<?= htmlspecialchars($infoPaciente['nrodoc'] ?? '') ?>">
    <input type="hidden" name="fechanacimiento" value="<?= htmlspecialchars($infoPaciente['fechanacimiento'] ?? '') ?>">
    <input type="hidden" name="genero" value="<?= htmlspecialchars($infoPaciente['genero'] ?? '') ?>">
    <input type="hidden" name="email" value="<?= htmlspecialchars($infoPaciente['email'] ?? '') ?>">

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="apellidos" class="form-label">Apellidos</label>
            <input type="text" class="form-control" id="apellidos" name="apellidos"
                value="<?= htmlspecialchars($infoPaciente['apellidos'] ?? '') ?>" required>
        </div>
        <div class="col-md-6">
            <label for="nombres" class="form-label">Nombres</label>
            <input type="text" class="form-control" id="nombres" name="nombres"
                value="<?= htmlspecialchars($infoPaciente['nombres'] ?? '') ?>" required>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label for="tipodoc" class="form-label">Tipo de Documento</label>
            <input type="text" class="form-control" id="tipodoc_text" 
                value="<?= htmlspecialchars($infoPaciente['tipodoc'] ?? '') ?>" readonly>
            <!-- Mantener el valor original en un campo oculto -->
            <input type="hidden" name="tipodoc" id="tipodoc" 
                value="<?= htmlspecialchars($infoPaciente['tipodoc'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <label for="nrodoc_display" class="form-label">Número de Documento</label>
            <!-- Campo de solo lectura para mostrar el número de documento -->
            <input type="text" class="form-control" id="nrodoc_display"
                value="<?= htmlspecialchars($infoPaciente['nrodoc'] ?? '') ?>" readonly>
        </div>
        <div class="col-md-4">
            <label for="fechanacimiento_display" class="form-label">Fecha de Nacimiento</label>
            <!-- Campo de solo lectura para mostrar la fecha de nacimiento -->
            <input type="text" class="form-control" id="fechanacimiento_display"
                value="<?= formatearFecha($infoPaciente['fechanacimiento'] ?? '') ?>" readonly>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label for="genero_display" class="form-label">Género</label>
            <!-- Campo de solo lectura para mostrar el género -->
            <input type="text" class="form-control" id="genero_display"
                value="<?= obtenerTextoGenero($infoPaciente['genero'] ?? '') ?>" readonly>
        </div>
        <div class="col-md-4">
            <label for="telefono" class="form-label">Teléfono</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                <input type="tel" class="form-control" id="telefono" name="telefono"
                    value="<?= htmlspecialchars($infoPaciente['telefono'] ?? '') ?>"
                    pattern="^9\d{8}$" maxlength="9" title="El teléfono debe tener 9 dígitos y comenzar con 9" required>
            </div>
        </div>
        <div class="col-md-4">
            <label for="email_display" class="form-label">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <!-- Campo de solo lectura para mostrar el email -->
                <input type="text" class="form-control" id="email_display"
                    value="<?= htmlspecialchars($infoPaciente['email'] ?? '') ?>" readonly>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <label for="direccion" class="form-label">Dirección</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                <input type="text" class="form-control" id="direccion" name="direccion"
                    value="<?= htmlspecialchars($infoPaciente['direccion'] ?? '') ?>" required>
            </div>
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
        // Configurar validación de teléfono
        setupPhoneValidation();
    });

    // Configurar validación de teléfono
    function setupPhoneValidation() {
        const telefonoInput = document.getElementById('telefono');

        telefonoInput.addEventListener('input', function() {
            // Eliminar cualquier carácter que no sea un número
            this.value = this.value.replace(/\D/g, '');

            // Asegurar que comience con 9
            if (this.value.length > 0 && this.value.charAt(0) !== '9') {
                this.value = '9' + this.value.substring(1);
            }

            // Validar el patrón
            if (this.value.length === 9 && this.value.charAt(0) === '9') {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else if (this.value.length > 0) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.remove('is-invalid');
            }
        });
    }
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
</style>