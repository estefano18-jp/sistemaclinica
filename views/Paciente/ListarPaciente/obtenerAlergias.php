<?php
require_once '../../../models/Paciente.php';
require_once '../../../models/Alergia.php';

// Verificar que se haya proporcionado un ID de paciente
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<div class="alert alert-danger">ID de paciente no proporcionado.</div>';
    exit;
}

$idPaciente = $_GET['id'];
$paciente = new Paciente();

// Obtener información básica del paciente
$infoPaciente = $paciente->obtenerPacientePorId($idPaciente);

if (!$infoPaciente) {
    echo '<div class="alert alert-danger">Paciente no encontrado.</div>';
    exit;
}

// Obtener las alergias del paciente
$alergias = $paciente->obtenerAlergiasPorId($idPaciente);
?>

<p class="text-center mb-4">
    <span class="badge bg-primary fs-6">Paciente: <?= htmlspecialchars($infoPaciente['apellidos'] . ', ' . $infoPaciente['nombres']) ?></span>
</p>

<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-primary" onclick="abrirModalAgregarAlergia()">
        <i class="fas fa-plus me-1"></i> Agregar Alergia
    </button>
</div>

<div id="alergiasContainer">
    <?php if (empty($alergias)): ?>
        <div class="alert alert-info" id="noAlergias">
            <i class="fas fa-info-circle me-2"></i> No se han registrado alergias para este paciente.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>Tipo</th>
                        <th>Alergia</th>
                        <th>Gravedad</th>
                        <th width="150px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alergias as $alergia): ?>
                        <tr>
                            <td><?= ucfirst(strtolower(htmlspecialchars($alergia['tipoalergia']))) ?></td>
                            <td><?= htmlspecialchars($alergia['alergia']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= ($alergia['gravedad'] == 'LEVE') ? 'success' : (($alergia['gravedad'] == 'MODERADA') ? 'warning' : 'danger') ?>">
                                    <?= htmlspecialchars($alergia['gravedad']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Editar" onclick="editarAlergia(
                                    <?= $alergia['id'] ?>,
                                    '<?= htmlspecialchars($alergia['tipoalergia']) ?>',
                                    '<?= htmlspecialchars($alergia['alergia']) ?>',
                                    '<?= htmlspecialchars($alergia['gravedad']) ?>'
                                )">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Eliminar" onclick="eliminarAlergiaConfirm(<?= $alergia['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para agregar alergia (anidado dentro del modal principal) -->
<div class="modal fade" id="agregarAlergiaModal" tabindex="-1" aria-labelledby="agregarAlergiaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="agregarAlergiaModalLabel"><i class="fas fa-plus-circle me-2"></i> Agregar Alergia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAgregarAlergia">
                    <input type="hidden" name="operacion" value="registrar_paciente">
                    <input type="hidden" name="idpersona" value="<?= htmlspecialchars($infoPaciente['idpersona']) ?>">

                    <div class="mb-3">
                        <label for="tipoalergia" class="form-label">Tipo de Alergia</label>
                        <select class="form-select" id="tipoalergia" name="tipoalergia" required>
                            <option value="">Seleccione...</option>
                            <option value="MEDICAMENTO">Medicamento</option>
                            <option value="ALIMENTO">Alimento</option>
                            <option value="AMBIENTE">Ambiental</option>
                            <option value="OTRO">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="alergia" class="form-label">Alergia</label>
                        <input type="text" class="form-control" id="alergia" name="alergia" required>
                    </div>
                    <div class="mb-3">
                        <label for="gravedad" class="form-label">Gravedad</label>
                        <select class="form-select" id="gravedad" name="gravedad" required>
                            <option value="">Seleccione...</option>
                            <option value="LEVE">Leve</option>
                            <option value="MODERADA">Moderada</option>
                            <option value="GRAVE">Grave</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarAlergia()">Agregar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar alergia (anidado dentro del modal principal) -->
<div class="modal fade" id="editarAlergiaModal" tabindex="-1" aria-labelledby="editarAlergiaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editarAlergiaModalLabel"><i class="fas fa-edit me-2"></i> Editar Alergia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarAlergia">
                    <input type="hidden" id="editIdAlergia" name="idlistaalergia">
                    <div class="mb-3">
                        <label for="editTipoAlergia" class="form-label">Tipo de Alergia</label>
                        <select class="form-select" id="editTipoAlergia" name="tipoalergia" disabled>
                            <option value="">Seleccione...</option>
                            <option value="MEDICAMENTO">Medicamento</option>
                            <option value="ALIMENTO">Alimento</option>
                            <option value="AMBIENTE">Ambiental</option>
                            <option value="OTRO">Otro</option>
                        </select>
                        <small class="text-muted">El tipo de alergia no se puede modificar</small>
                    </div>
                    <div class="mb-3">
                        <label for="editAlergia" class="form-label">Alergia</label>
                        <input type="text" class="form-control" id="editAlergia" name="alergia" disabled>
                        <small class="text-muted">El nombre de la alergia no se puede modificar</small>
                    </div>
                    <div class="mb-3">
                        <label for="editGravedad" class="form-label">Gravedad</label>
                        <select class="form-select" id="editGravedad" name="gravedad" required>
                            <option value="">Seleccione...</option>
                            <option value="LEVE">Leve</option>
                            <option value="MODERADA">Moderada</option>
                            <option value="GRAVE">Grave</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="guardarEdicionAlergia()">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Inicializar tooltips de Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Variable para almacenar el ID del paciente 
    const idPaciente = <?= $idPaciente ?>;

    function abrirModalAgregarAlergia() {
        // Reiniciar formulario
        document.getElementById('formAgregarAlergia').reset();

        // Abrir modal
        const agregarAlergiaModal = new bootstrap.Modal(document.getElementById('agregarAlergiaModal'));
        agregarAlergiaModal.show();
    }

    function guardarAlergia() {
        const form = document.getElementById('formAgregarAlergia');

        // Validar que todos los campos requeridos estén completos
        if (!form.tipoalergia.value || !form.alergia.value || !form.gravedad.value) {
            Swal.fire({
                icon: 'error',
                title: 'Campos incompletos',
                text: 'Por favor complete todos los campos requeridos.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Mostrar loading
        Swal.fire({
            title: 'Procesando',
            text: 'Verificando alergia...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Verificar si la alergia ya existe para este paciente
        const idPersona = form.idpersona.value;
        const tipoAlergia = encodeURIComponent(form.tipoalergia.value);
        const nombreAlergia = encodeURIComponent(form.alergia.value);

        // Consultar si la alergia ya existe para este paciente
        fetch(`../../../controllers/alergia.controller.php?operacion=verificar_existe_alergia&idpersona=${idPersona}&tipoalergia=${tipoAlergia}&alergia=${nombreAlergia}`)
            .then(response => response.json())
            .then(data => {
                // Si la alergia ya existe para este paciente, mostrar mensaje de error
                if (data.existe) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Alergia duplicada',
                        text: 'Esta alergia ya está registrada para este paciente.',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                // Si no existe, continuar con el registro
                Swal.update({
                    title: 'Procesando',
                    text: 'Registrando alergia...'
                });

                // Primero, registrar la alergia si no existe
                const formDataAlergia = new FormData();
                formDataAlergia.append('operacion', 'registrar');
                formDataAlergia.append('tipoalergia', form.tipoalergia.value);
                formDataAlergia.append('alergia', form.alergia.value);

                fetch('../../../controllers/alergia.controller.php', {
                        method: 'POST',
                        body: formDataAlergia
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            // Luego, asociar la alergia al paciente
                            const formDataAsociar = new FormData();
                            formDataAsociar.append('operacion', 'registrar_paciente');
                            formDataAsociar.append('idpersona', idPersona);
                            formDataAsociar.append('idalergia', data.idalergia);
                            formDataAsociar.append('gravedad', form.gravedad.value);

                            return fetch('../../../controllers/alergia.controller.php', {
                                method: 'POST',
                                body: formDataAsociar
                            });
                        } else {
                            Swal.close();
                            throw new Error(data.mensaje || 'Error al registrar la alergia');
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.close();

                        if (data.status) {
                            // Cerrar modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('agregarAlergiaModal'));
                            if (modal) {
                                modal.hide();
                            }

                            // Recargar contenido
                            recargarContenidoAlergias();

                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: 'Alergia registrada',
                                text: 'La alergia se ha registrado correctamente.',
                                confirmButtonColor: '#3085d6'
                            });
                        } else {
                            throw new Error(data.mensaje || 'Error al asociar la alergia al paciente');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'No se pudo guardar la alergia.',
                            confirmButtonColor: '#3085d6'
                        });
                    });
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo verificar la alergia.',
                    confirmButtonColor: '#3085d6'
                });
            });
    }

    function editarAlergia(idAlergia, tipoAlergia, nombreAlergia, gravedad) {
        // Llenar el formulario con los datos actuales
        document.getElementById('editIdAlergia').value = idAlergia;
        document.getElementById('editTipoAlergia').value = tipoAlergia;
        document.getElementById('editAlergia').value = nombreAlergia;
        document.getElementById('editGravedad').value = gravedad;

        // Abrir modal
        const editarAlergiaModal = new bootstrap.Modal(document.getElementById('editarAlergiaModal'));
        editarAlergiaModal.show();
    }

    function guardarEdicionAlergia() {
        const form = document.getElementById('formEditarAlergia');

        // Validar que todos los campos requeridos estén completos
        if (!form.gravedad.value) {
            Swal.fire({
                icon: 'error',
                title: 'Campos incompletos',
                text: 'Por favor seleccione la gravedad de la alergia.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Mostrar loading
        Swal.fire({
            title: 'Procesando',
            text: 'Actualizando alergia...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('operacion', 'actualizar_gravedad');
        formData.append('idlistaalergia', form.idlistaalergia.value);
        formData.append('gravedad', form.gravedad.value);

        fetch('../../../controllers/alergia.controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();

                if (data.status) {
                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editarAlergiaModal'));
                    if (modal) {
                        modal.hide();
                    }

                    // Recargar contenido
                    recargarContenidoAlergias();

                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualización exitosa',
                        text: 'La alergia se ha actualizado correctamente.',
                        confirmButtonColor: '#3085d6'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.mensaje || 'No se pudo actualizar la alergia.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'No se pudo actualizar la alergia.',
                    confirmButtonColor: '#3085d6'
                });
            });
    }

    function eliminarAlergiaConfirm(idAlergia) {
        Swal.fire({
            title: "¿Está seguro?",
            text: "¿Realmente desea eliminar esta alergia?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarAlergia(idAlergia);
            }
        });
    }

    function eliminarAlergia(idAlergia) {
        // Mostrar loading
        Swal.fire({
            title: 'Procesando',
            text: 'Eliminando alergia...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Opción 1: Utilizar el endpoint de eliminación completa
        fetch('../../../controllers/alergia.controller.php?operacion=eliminar_paciente&idlistaalergia=' + idAlergia)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                Swal.close();

                if (data.status) {
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: data.mensaje || 'Alergia eliminada correctamente',
                        confirmButtonColor: '#3085d6'
                    });

                    // Recargar contenido
                    recargarContenidoAlergias();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.mensaje || 'No se pudo eliminar la alergia.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.close();
                
                // Intentar con el método alternativo si el primero falla
                fetch('../../../controllers/alergia.controller.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'operacion=eliminar_paciente&idlistaalergia=' + idAlergia
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        // Mostrar mensaje de éxito
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: data.mensaje || 'Alergia eliminada correctamente',
                            confirmButtonColor: '#3085d6'
                        });

                        // Recargar contenido
                        recargarContenidoAlergias();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.mensaje || 'No se pudo eliminar la alergia.',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                })
                .catch(err => {
                    console.error('Error en el segundo intento:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar la alergia. Por favor, inténtelo de nuevo.',
                        confirmButtonColor: '#3085d6'
                    });
                });
            });
    }

    function recargarContenidoAlergias() {
        // Obtener el contenedor de alergias directamente
        fetch(`obtenerAlergias.php?id=${idPaciente}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al recargar alergias: ' + response.status);
                }
                return response.text();
            })
            .then(html => {
                // Reemplazar todo el contenido del modal con el nuevo HTML
                document.getElementById('contenidoAlergias').innerHTML = html;

                // Reinicializar los tooltips después de actualizar el contenido
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            })
            .catch(error => {
                console.error('Error al recargar alergias:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo actualizar la lista de alergias.',
                    confirmButtonColor: '#3085d6'
                });
            });
    }
</script>

<style>
    .badge {
        font-size: 0.9rem;
        padding: 6px 10px;
    }

    .btn-sm {
        width: 35px;
        height: 35px;
        padding: 5px 0;
        margin: 0 3px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
    }

    .table th,
    .table td {
        vertical-align: middle;
    }
</style>