<?php
// Este archivo carga el formulario de horarios de atención del doctor para edición o visualización

// Recuperar el número de documento de la URL
$nroDoc = isset($_GET['nrodoc']) ? $_GET['nrodoc'] : '';

// Validar que tenemos el número de documento
if (empty($nroDoc)) {
    echo '<div class="alert alert-danger">No se proporcionó un número de documento válido</div>';
    exit;
}

// Incluir archivo de conexión
require_once '../../../models/conexion.php';

// Obtener datos del doctor
$conexion = new Conexion();
$pdo = $conexion->getConexion();

$stmt = $pdo->prepare("
    SELECT 
        p.idpersona,
        p.apellidos,
        p.nombres,
        p.nrodoc,
        c.idcolaborador,
        c.idespecialidad,
        e.especialidad
    FROM 
        personas p
        INNER JOIN colaboradores c ON p.idpersona = c.idpersona
        INNER JOIN especialidades e ON c.idespecialidad = e.idespecialidad
    WHERE 
        p.nrodoc = ?
");
$stmt->execute([$nroDoc]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar que se encontró el doctor
if (!$doctor) {
    echo '<div class="alert alert-danger">No se encontró el doctor con el documento especificado</div>';
    exit;
}

// Obtener el contrato activo del doctor
$stmt = $pdo->prepare("
    SELECT 
        c.idcontrato, c.idcolaborador, c.estado
    FROM 
        contratos c
    WHERE 
        c.idcolaborador = ? 
        AND c.estado = 'ACTIVO'
    ORDER BY 
        c.fechainicio DESC
    LIMIT 1
");
$stmt->execute([$doctor['idcolaborador']]);
$contrato = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener los horarios actuales del doctor
$stmt = $pdo->prepare("
    SELECT 
        a.idatencion,
        a.diasemana,
        h.idhorario,
        h.horainicio,
        h.horafin
    FROM 
        atenciones a
        INNER JOIN horarios h ON a.idatencion = h.idatencion
    WHERE 
        a.idcontrato = ?
    ORDER BY 
        FIELD(a.diasemana, 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'),
        h.horainicio
");
$stmt->execute([$contrato['idcontrato']]);
$horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar los horarios por día para el resumen visual
$horariosPorDia = [];
foreach ($horarios as $horario) {
    $dia = $horario['diasemana'];
    if (!isset($horariosPorDia[$dia])) {
        $horariosPorDia[$dia] = [];
    }
    $horariosPorDia[$dia][] = [
        'inicio' => substr($horario['horainicio'], 0, 5),
        'fin' => substr($horario['horafin'], 0, 5)
    ];
}
?>

<!-- Estilos personalizados para el modal -->
<style>
    /* Hacer el modal más ancho */
    #modalHorarioAtencion .modal-dialog {
        max-width: 50%;
        margin: 1.75rem auto;
    }

    /* Estilos para los botones de acción */
    .btn-accion {
        width: auto;
        min-width: 100px;
        margin: 0 5px;
        font-weight: 500;
    }

    /* Mejora visualización de las tablas */
    .table th,
    .table td {
        vertical-align: middle;
    }

    /* Estilos para la vista semanal */
    .vista-semanal {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin-top: 0;
        position: relative;
    }

    .vista-semanal .cabecera {
        background: #343a40;
        color: white;
        padding: 10px;
        text-align: center;
        font-weight: bold;
    }

    .vista-semanal .dias-semana {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        text-align: center;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .vista-semanal .dia {
        padding: 10px;
        font-weight: bold;
        border-right: 1px solid #dee2e6;
    }

    .vista-semanal .dia:last-child {
        border-right: none;
    }

    .vista-semanal .horarios {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        min-height: 150px;
    }

    .vista-semanal .celda-dia {
        border-right: 1px solid #dee2e6;
        padding: 10px;
        min-height: 150px;
    }

    .vista-semanal .celda-dia:last-child {
        border-right: none;
    }

    .bloque-horario {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 8px;
        margin: 5px 0;
        border-radius: 4px;
        font-size: 14px;
        white-space: nowrap;
        /* Evitar saltos de línea */
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Arreglo para los botones en móviles */
    @media (max-width: 768px) {
        .btn-accion {
            margin-bottom: 5px;
            width: 100%;
        }
    }

    /* Estilos para notificaciones flotantes */
    .notificacion {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        align-items: center;
        padding: 12px 20px;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-100px);
        opacity: 0;
        transition: all 0.3s ease;
        max-width: 350px;
    }

    .notificacion.mostrar {
        transform: translateY(0);
        opacity: 1;
    }

    .notificacion-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }

    .notificacion-error {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .notificacion-icono {
        margin-right: 12px;
        font-size: 20px;
    }

    .notificacion-mensaje {
        font-size: 14px;
        font-weight: 500;
    }
</style>

<div class="container-fluid">
    <!-- Información del doctor -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-md me-2"></i>Información del Doctor</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-id-card me-2"></i>Nombre:</strong> <?php echo $doctor['nombres'] . ' ' . $doctor['apellidos']; ?></p>
                            <p><strong><i class="fas fa-passport me-2"></i>Documento:</strong> <?php echo $doctor['nrodoc']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-stethoscope me-2"></i>Especialidad:</strong> <?php echo $doctor['especialidad']; ?></p>
                            <p><strong><i class="fas fa-file-contract me-2"></i>Estado del contrato:</strong>
                                <span class="badge bg-<?php echo ($contrato && $contrato['estado'] == 'ACTIVO') ? 'success' : 'danger'; ?>">
                                    <?php echo ($contrato) ? $contrato['estado'] : 'SIN CONTRATO'; ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario para agregar nuevo horario -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Agregar Nuevo Horario</h5>
                </div>
                <div class="card-body">
                    <form id="formAgregarHorario" class="row g-3">
                        <input type="hidden" id="idcolaborador" name="idcolaborador" value="<?php echo $doctor['idcolaborador']; ?>">
                        <input type="hidden" id="idcontrato" name="idcontrato" value="<?php echo $contrato['idcontrato']; ?>">

                        <div class="col-md-4">
                            <label for="nuevoDia" class="form-label"><i class="fas fa-calendar-day me-1"></i>Día de Atención</label>
                            <select id="nuevoDia" name="diasemana" class="form-select" required>
                                <option value="">Seleccione un día</option>
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
                            <label for="nuevoHoraInicio" class="form-label"><i class="fas fa-hourglass-start me-1"></i>Hora de Inicio</label>
                            <input type="time" id="nuevoHoraInicio" name="horainicio" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label for="nuevoHoraFin" class="form-label"><i class="fas fa-hourglass-end me-1"></i>Hora de Fin</label>
                            <input type="time" id="nuevoHoraFin" name="horafin" class="form-control" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" id="btnAgregarHorario" class="btn btn-success w-100">
                                <i class="fas fa-plus-circle me-2"></i>Agregar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de horarios actuales - PRIMER CUADRO -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Horarios Actuales</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaHorarios">
                            <thead class="table-primary">
                                <tr>
                                    <th width="25%">Día</th>
                                    <th width="20%">Hora Inicio</th>
                                    <th width="20%">Hora Fin</th>
                                    <th width="35%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoTablaHorarios">
                                <?php if (empty($horarios)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>No hay horarios registrados
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($horarios as $horario): ?>
                                        <tr data-idatencion="<?php echo $horario['idatencion']; ?>" data-idhorario="<?php echo $horario['idhorario']; ?>">
                                            <td>
                                                <span class="texto-dia"><?php echo $horario['diasemana']; ?></span>
                                                <select class="form-select dia-edit" style="display:none;" disabled>
                                                    <option value="LUNES" <?php echo $horario['diasemana'] == 'LUNES' ? 'selected' : ''; ?>>Lunes</option>
                                                    <option value="MARTES" <?php echo $horario['diasemana'] == 'MARTES' ? 'selected' : ''; ?>>Martes</option>
                                                    <option value="MIERCOLES" <?php echo $horario['diasemana'] == 'MIERCOLES' ? 'selected' : ''; ?>>Miércoles</option>
                                                    <option value="JUEVES" <?php echo $horario['diasemana'] == 'JUEVES' ? 'selected' : ''; ?>>Jueves</option>
                                                    <option value="VIERNES" <?php echo $horario['diasemana'] == 'VIERNES' ? 'selected' : ''; ?>>Viernes</option>
                                                    <option value="SABADO" <?php echo $horario['diasemana'] == 'SABADO' ? 'selected' : ''; ?>>Sábado</option>
                                                    <option value="DOMINGO" <?php echo $horario['diasemana'] == 'DOMINGO' ? 'selected' : ''; ?>>Domingo</option>
                                                </select>
                                            </td>
                                            <td>
                                                <span class="texto-hora-inicio"><?php echo substr($horario['horainicio'], 0, 5); ?></span>
                                                <input type="time" class="form-control hora-inicio-edit" value="<?php echo substr($horario['horainicio'], 0, 5); ?>" style="display:none;" disabled>
                                            </td>
                                            <td>
                                                <span class="texto-hora-fin"><?php echo substr($horario['horafin'], 0, 5); ?></span>
                                                <input type="time" class="form-control hora-fin-edit" value="<?php echo substr($horario['horafin'], 0, 5); ?>" style="display:none;" disabled>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-start">
                                                    <button type="button" class="btn btn-warning btn-accion btn-editar-horario me-2">
                                                        <i class="fas fa-edit me-1"></i> Editar
                                                    </button>
                                                    <button type="button" class="btn btn-success btn-accion btn-guardar-horario me-2" style="display:none;">
                                                        <i class="fas fa-save me-1"></i> Guardar
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-accion btn-eliminar-horario">
                                                        <i class="fas fa-trash me-1"></i> Eliminar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Separación explícita entre cuadros -->
    <div class="separacion-cuadros"></div>

    <!-- Horario Semanal del Doctor - SEGUNDO CUADRO, SEPARADO -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="vista-semanal">
                <div class="cabecera">
                    <h6 class="mb-0">Horario Semanal del Dr. <?php echo $doctor['nombres'] . ' ' . $doctor['apellidos']; ?></h6>
                </div>
                <div class="dias-semana">
                    <div class="dia">LUNES</div>
                    <div class="dia">MARTES</div>
                    <div class="dia">MIÉRCOLES</div>
                    <div class="dia">JUEVES</div>
                    <div class="dia">VIERNES</div>
                    <div class="dia">SÁBADO</div>
                    <div class="dia">DOMINGO</div>
                </div>
                <div class="horarios">
                    <?php
                    $diasSemana = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'];

                    foreach ($diasSemana as $dia) {
                        echo '<div class="celda-dia">';
                        if (isset($horariosPorDia[$dia])) {
                            foreach ($horariosPorDia[$dia] as $horario) {
                                echo '<div class="bloque-horario">';
                                echo '<i class="fas fa-clock me-1"></i>';
                                echo $horario['inicio'] . '&nbsp;-&nbsp;' . $horario['fin']; // Uso de espacio no divisible
                                echo '</div>';
                            }
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="row mt-2 mb-3">
        <div class="col-md-12 text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="fas fa-times me-2"></i>Cerrar
            </button>
        </div>
    </div>
</div>

<!-- Script para el manejo de horarios -->
<script>
    $(document).ready(function() {
        // Variables globales
        window.idColaborador = <?php echo $doctor['idcolaborador']; ?>;
        window.idContrato = <?php echo isset($contrato['idcontrato']) ? $contrato['idcontrato'] : 'null'; ?>;
        window.nroDoc = "<?php echo $nroDoc; ?>";

        // Asignar eventos a los botones
        asignarEventosBotones();

        // Evento para botón de agregar horario
        $('#btnAgregarHorario').on('click', function() {
            // Obtener valores del formulario
            const dia = $('#nuevoDia').val();
            const horaInicio = $('#nuevoHoraInicio').val();
            const horaFin = $('#nuevoHoraFin').val();

            // Validar campos requeridos
            if (!dia || !horaInicio || !horaFin) {
                mostrarNotificacion('Por favor complete todos los campos', 'error');
                return;
            }

            // Validar que hora fin sea mayor a hora inicio
            if (horaInicio >= horaFin) {
                mostrarNotificacion('La hora de fin debe ser posterior a la hora de inicio', 'error');
                return;
            }

            // Registrar horario - sin mostrar Swal de carga
            $.ajax({
                url: '../../../controllers/horario.controller.php?op=registrar',
                type: 'POST',
                data: {
                    idcolaborador: idColaborador,
                    idcontrato: idContrato,
                    diasemana: dia,
                    horainicio: horaInicio,
                    horafin: horaFin
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        // Usar notificación flotante
                        mostrarNotificacion('Horario registrado correctamente', 'success');

                        // Limpiar formulario
                        $('#nuevoDia').val('');
                        $('#nuevoHoraInicio').val('');
                        $('#nuevoHoraFin').val('');

                        // Actualizar vista sin recargar la página
                        cargarHorarios();
                    } else {
                        mostrarNotificacion(response.mensaje || 'No se pudo registrar el horario', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    mostrarNotificacion('Error de conexión con el servidor', 'error');
                }
            });
        });
    });

    // Función para cargar horarios sin recargar la página
    function cargarHorarios() {
        $.ajax({
            url: '../../../controllers/horario.controller.php?op=horarios_doctor',
            type: 'GET',
            data: {
                idcolaborador: idColaborador
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    // En lugar de recargar la página, actualizamos solo las partes necesarias
                    $.ajax({
                        url: `editarHorarioAtencion.php?nrodoc=${nroDoc}`,
                        type: 'GET',
                        success: function(html) {
                            // Crear un elemento temporal para extraer partes del HTML
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = html;

                            // Actualizar la tabla de horarios
                            const nuevoContenidoTabla = $(tempDiv).find('#cuerpoTablaHorarios').html();
                            $('#cuerpoTablaHorarios').html(nuevoContenidoTabla);

                            // Actualizar la vista semanal
                            const nuevaVistaSemanal = $(tempDiv).find('.horarios').html();
                            $('.horarios').html(nuevaVistaSemanal);

                            // Volver a asignar eventos a los botones
                            asignarEventosBotones();
                        },
                        error: function(xhr, status, error) {
                            console.error("Error al actualizar vista:", error);
                            mostrarNotificacion('Error al actualizar la vista', 'error');
                        }
                    });
                } else {
                    console.error("Error al cargar horarios:", response.mensaje);
                    mostrarNotificacion(response.mensaje || 'No se pudieron cargar los horarios', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error de conexión:", error);
                mostrarNotificacion('Error de conexión con el servidor', 'error');
            }
        });
    }

    // Función para mostrar notificaciones flotantes sin cerrar el modal
    function mostrarNotificacion(mensaje, tipo) {
        // Crear el elemento de notificación
        const notificacion = document.createElement('div');
        notificacion.className = `notificacion notificacion-${tipo}`;
        notificacion.innerHTML = `
        <div class="notificacion-icono">
            <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        </div>
        <div class="notificacion-mensaje">${mensaje}</div>
    `;

        // Agregar al documento
        document.body.appendChild(notificacion);

        // Mostrar con animación
        setTimeout(() => {
            notificacion.classList.add('mostrar');
        }, 10);

        // Ocultar después de 3 segundos
        setTimeout(() => {
            notificacion.classList.remove('mostrar');
            setTimeout(() => {
                notificacion.remove();
            }, 300);
        }, 3000);
    }

    // Función para reasignar eventos a los botones
    function asignarEventosBotones() {
        // Evento para botón de editar horario
        $('.btn-editar-horario').off('click').on('click', function() {
            const fila = $(this).closest('tr');

            // Ocultar textos y mostrar campos de edición
            fila.find('.texto-dia').hide();
            fila.find('.texto-hora-inicio').hide();
            fila.find('.texto-hora-fin').hide();

            fila.find('.dia-edit').show().prop('disabled', false);
            fila.find('.hora-inicio-edit').show().prop('disabled', false);
            fila.find('.hora-fin-edit').show().prop('disabled', false);

            // Cambiar botones
            $(this).hide();
            fila.find('.btn-guardar-horario').show();
        });

        // Evento para botón de guardar horario
        $('.btn-guardar-horario').off('click').on('click', function() {
            const fila = $(this).closest('tr');
            const idAtencion = fila.data('idatencion');
            const idHorario = fila.data('idhorario');

            // Obtener valores editados
            const nuevoDia = fila.find('.dia-edit').val();
            const nuevoHoraInicio = fila.find('.hora-inicio-edit').val();
            const nuevoHoraFin = fila.find('.hora-fin-edit').val();

            // Validar que hora fin sea mayor a hora inicio
            if (nuevoHoraInicio >= nuevoHoraFin) {
                mostrarNotificacion('La hora de fin debe ser posterior a la hora de inicio', 'error');
                return;
            }

            // Guardar cambios - sin mostrar Swal de carga
            $.ajax({
                url: '../../../controllers/horario.controller.php?op=actualizar',
                type: 'POST',
                data: {
                    idatencion: idAtencion,
                    idhorario: idHorario,
                    diasemana: nuevoDia,
                    horainicio: nuevoHoraInicio,
                    horafin: nuevoHoraFin
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        mostrarNotificacion('Horario actualizado correctamente', 'success');

                        // Actualizar vista sin recargar la página
                        cargarHorarios();
                    } else {
                        mostrarNotificacion(response.mensaje || 'No se pudo actualizar el horario', 'error');

                        // Volver a mostrar los textos y ocultar campos de edición
                        fila.find('.texto-dia').show();
                        fila.find('.texto-hora-inicio').show();
                        fila.find('.texto-hora-fin').show();

                        fila.find('.dia-edit').hide().prop('disabled', true);
                        fila.find('.hora-inicio-edit').hide().prop('disabled', true);
                        fila.find('.hora-fin-edit').hide().prop('disabled', true);

                        // Cambiar botones
                        fila.find('.btn-guardar-horario').hide();
                        fila.find('.btn-editar-horario').show();
                    }
                },
                error: function(xhr, status, error) {
                    mostrarNotificacion('Error de conexión con el servidor', 'error');

                    // Volver a mostrar los textos y ocultar campos de edición
                    fila.find('.texto-dia').show();
                    fila.find('.texto-hora-inicio').show();
                    fila.find('.texto-hora-fin').show();

                    fila.find('.dia-edit').hide().prop('disabled', true);
                    fila.find('.hora-inicio-edit').hide().prop('disabled', true);
                    fila.find('.hora-fin-edit').hide().prop('disabled', true);

                    // Cambiar botones
                    fila.find('.btn-guardar-horario').hide();
                    fila.find('.btn-editar-horario').show();
                }
            });
        });

        // Evento para botón de eliminar horario
        $('.btn-eliminar-horario').off('click').on('click', function() {
            const fila = $(this).closest('tr');
            const idHorario = fila.data('idhorario');

            Swal.fire({
                title: '¿Está seguro?',
                text: 'Esta acción eliminará el horario seleccionado',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Eliminar horario - sin mostrar Swal de carga
                    $.ajax({
                        url: '../../../controllers/horario.controller.php?op=eliminar',
                        type: 'POST',
                        data: {
                            idhorario: idHorario
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                mostrarNotificacion('Horario eliminado correctamente', 'success');

                                // Actualizar vista sin recargar la página
                                cargarHorarios();
                            } else {
                                mostrarNotificacion(response.mensaje || 'No se pudo eliminar el horario', 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            mostrarNotificacion('Error de conexión con el servidor', 'error');
                        }
                    });
                }
            });
        });
    }
</script>