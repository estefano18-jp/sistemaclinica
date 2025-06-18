<?php
// Verificar que se proporcione un número de documento
if (!isset($_GET['nrodoc']) || empty($_GET['nrodoc'])) {
    echo '<div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Número de documento no proporcionado.
          </div>';
    exit;
}

$nrodoc = $_GET['nrodoc'];

// Incluir los modelos necesarios
require_once '../../../models/Doctor.php';
require_once '../../../models/Especialidad.php';
require_once '../../../models/Horario.php';
require_once '../../../models/Usuario.php';
require_once '../../../models/Contrato.php';

// Instanciar los modelos
$doctor = new Doctor();
$especialidad = new Especialidad();
$horario = new Horario();
$usuario = new Usuario();
$contrato = new Contrato();

// Obtener la información del doctor
$infoDoctor = $doctor->obtenerDoctorPorNroDoc($nrodoc);

if (!$infoDoctor) {
    echo '<div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Doctor no encontrado.
          </div>';
    exit;
}

// Obtener información adicional
$infoEspecialidad = isset($infoDoctor['idespecialidad']) ? $especialidad->obtenerEspecialidadPorId($infoDoctor['idespecialidad']) : null;
$infoHorarios = isset($infoDoctor['idcolaborador']) ? $horario->obtenerHorariosPorColaborador($infoDoctor['idcolaborador']) : [];
$infoUsuario = isset($infoDoctor['idcolaborador']) ? $usuario->obtenerUsuarioPorColaborador($infoDoctor['idcolaborador']) : null;
$infoContratos = isset($infoDoctor['idcolaborador']) ? $contrato->obtenerContratosPorColaborador($infoDoctor['idcolaborador']) : [];

// Obtener el contrato activo
$contratoActivo = !empty($infoContratos) ? $infoContratos[0] : null;

// Formatear datos
$nombres = isset($infoDoctor['nombres']) ? $infoDoctor['nombres'] : '';
$apellidos = isset($infoDoctor['apellidos']) ? $infoDoctor['apellidos'] : '';
$documento = isset($infoDoctor['tipodoc']) ? $infoDoctor['tipodoc'] . ': ' . $infoDoctor['nrodoc'] : 'No registrado';
$fechaNacimiento = isset($infoDoctor['fechanacimiento']) ? date('d/m/Y', strtotime($infoDoctor['fechanacimiento'])) : 'No registrada';
$genero = isset($infoDoctor['genero']) ? ($infoDoctor['genero'] == 'M' ? 'Masculino' : ($infoDoctor['genero'] == 'F' ? 'Femenino' : $infoDoctor['genero'])) : 'No registrado';
$telefono = isset($infoDoctor['telefono']) ? $infoDoctor['telefono'] : 'No registrado';
$email = isset($infoDoctor['email']) ? $infoDoctor['email'] : 'No registrado';
$direccion = isset($infoDoctor['direccion']) ? $infoDoctor['direccion'] : 'No registrada';
$especialidadNombre = isset($infoEspecialidad['especialidad']) ? $infoEspecialidad['especialidad'] : 'No registrada';
$precioAtencion = isset($infoDoctor['precioatencion']) ? 'S/. ' . number_format($infoDoctor['precioatencion'], 2) : 'No registrado';

// Calcular edad
$edad = '';
if (isset($infoDoctor['fechanacimiento']) && !empty($infoDoctor['fechanacimiento'])) {
    $fechaNac = new DateTime($infoDoctor['fechanacimiento']);
    $hoy = new DateTime();
    $edad = $fechaNac->diff($hoy)->y . ' años';
}

// Datos de contrato
$tipoContrato = isset($contratoActivo['tipocontrato']) ? $contratoActivo['tipocontrato'] : 'No registrado';
$fechaInicio = isset($contratoActivo['fechainicio']) ? date('d/m/Y', strtotime($contratoActivo['fechainicio'])) : 'No registrada';
$fechaFin = 'No aplica';
if (isset($contratoActivo['fechafin']) && !empty($contratoActivo['fechafin']) && $tipoContrato !== 'INDEFINIDO') {
    $fechaFin = date('d/m/Y', strtotime($contratoActivo['fechafin']));
}
$estadoContrato = isset($contratoActivo['estado']) ? $contratoActivo['estado'] : 'No registrado';

// Fecha de registro
$fechaRegistro = isset($infoDoctor['fecharegistro']) ? date('d/m/Y', strtotime($infoDoctor['fecharegistro'])) : 'No registrada';

// Arreglo para traducir los días de la semana
$diasSemana = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    7 => 'Domingo'
];

// Ordenar horarios por día de la semana
if (!empty($infoHorarios)) {
    usort($infoHorarios, function($a, $b) {
        // Si dia es string (LUNES, MARTES, etc), convertir a número
        $diaA = isset($a['dia']) ? $a['dia'] : 
               (isset($a['diasemana']) ? array_search($a['diasemana'], ['', 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO']) : 0);
        
        $diaB = isset($b['dia']) ? $b['dia'] : 
               (isset($b['diasemana']) ? array_search($b['diasemana'], ['', 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO']) : 0);
        
        return $diaA - $diaB;
    });
}
?>

<div class="detalles-doctor">
    <!-- Información Personal -->
    <div class="seccion">
        <div class="seccion-titulo">
            <i class="fas fa-user-md me-2"></i> Información Personal
        </div>
        <div class="seccion-contenido">
            <div class="fila">
                <div class="etiqueta">Apellidos:</div>
                <div class="valor"><?= htmlspecialchars($apellidos) ?></div>
            </div>
            <div class="fila">
                <div class="etiqueta">Nombres:</div>
                <div class="valor"><?= htmlspecialchars($nombres) ?></div>
            </div>
            <div class="fila">
                <div class="etiqueta">Documento:</div>
                <div class="valor"><?= htmlspecialchars($documento) ?></div>
            </div>
            <div class="fila">
                <div class="etiqueta">Fecha de Nacimiento:</div>
                <div class="valor"><?= htmlspecialchars($fechaNacimiento) ?> <?= !empty($edad) ? "($edad)" : '' ?></div>
            </div>
            <div class="fila">
                <div class="etiqueta">Género:</div>
                <div class="valor"><?= htmlspecialchars($genero) ?></div>
            </div>
            <div class="fila">
                <div class="etiqueta">Teléfono:</div>
                <div class="valor">
                    <a href="tel:<?= htmlspecialchars($telefono) ?>" class="link-info">
                        <i class="fas fa-phone-alt me-1"></i><?= htmlspecialchars($telefono) ?>
                    </a>
                </div>
            </div>
            <div class="fila">
                <div class="etiqueta">Email:</div>
                <div class="valor">
                    <a href="mailto:<?= htmlspecialchars($email) ?>" class="link-info">
                        <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($email) ?>
                    </a>
                </div>
            </div>
            <div class="fila">
                <div class="etiqueta">Dirección:</div>
                <div class="valor">
                    <i class="fas fa-map-marker-alt me-1 text-danger"></i><?= htmlspecialchars($direccion) ?>
                </div>
            </div>
            <div class="fila">
                <div class="etiqueta">Fecha de Registro:</div>
                <div class="valor"><?= htmlspecialchars($fechaRegistro) ?></div>
            </div>
        </div>
    </div>

    <!-- Información Profesional -->
    <div class="seccion">
        <div class="seccion-titulo">
            <i class="fas fa-stethoscope me-2"></i> Información Profesional
        </div>
        <div class="seccion-contenido">
            <div class="fila">
                <div class="etiqueta">Especialidad:</div>
                <div class="valor">
                    <span class="badge bg-success"><?= htmlspecialchars($especialidadNombre) ?></span>
                </div>
            </div>
            <div class="fila">
                <div class="etiqueta">Precio de Atención:</div>
                <div class="valor">
                    <span class="badge bg-primary"><?= htmlspecialchars($precioAtencion) ?></span>
                </div>
            </div>
            <?php if (isset($infoDoctor['estado'])): ?>
            <div class="fila">
                <div class="etiqueta">Estado:</div>
                <div class="valor">
                    <span class="badge <?= ($infoDoctor['estado'] == 'ACTIVO') ? 'bg-success' : 'bg-danger' ?>">
                        <?= htmlspecialchars($infoDoctor['estado']) ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Información de Contrato -->
    <div class="seccion">
        <div class="seccion-titulo">
            <i class="fas fa-file-contract me-2"></i> Información de Contrato
        </div>
        <div class="seccion-contenido">
            <?php if ($contratoActivo): ?>
                <div class="fila">
                    <div class="etiqueta">Tipo de Contrato:</div>
                    <div class="valor"><?= htmlspecialchars($tipoContrato) ?></div>
                </div>
                <div class="fila">
                    <div class="etiqueta">Fecha de Inicio:</div>
                    <div class="valor"><?= htmlspecialchars($fechaInicio) ?></div>
                </div>
                <div class="fila">
                    <div class="etiqueta">Fecha de Fin:</div>
                    <div class="valor"><?= htmlspecialchars($fechaFin) ?></div>
                </div>
                <div class="fila">
                    <div class="etiqueta">Estado:</div>
                    <div class="valor">
                        <span class="badge <?= ($estadoContrato == 'ACTIVO') ? 'bg-success' : 'bg-danger' ?>">
                            <?= htmlspecialchars($estadoContrato) ?>
                        </span>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No hay información de contrato registrada para este doctor.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Horarios de Atención -->
    <div class="seccion">
        <div class="seccion-titulo">
            <i class="fas fa-calendar-alt me-2"></i> Horarios de Atención
        </div>
        <div class="seccion-contenido">
            <?php if (!empty($infoHorarios)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th>Día</th>
                                <th>Hora Inicio</th>
                                <th>Hora Fin</th>
                                <th>Disponibilidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($infoHorarios as $h): 
                                // Determinar el día
                                $diaNombre = isset($h['dia_nombre']) && !empty($h['dia_nombre']) ? 
                                           $h['dia_nombre'] : 
                                           (isset($h['dia']) && isset($diasSemana[$h['dia']]) ? $diasSemana[$h['dia']] : 
                                           (isset($h['diasemana']) ? $h['diasemana'] : 'No especificado'));
                                
                                // Formatear horas para mejor visualización
                                $horaInicio = isset($h['horainicio']) ? substr($h['horainicio'], 0, 5) : '--:--';
                                $horaFin = isset($h['horafin']) ? substr($h['horafin'], 0, 5) : '--:--';
                                $estado = isset($h['estado']) ? $h['estado'] : 'ACTIVO';
                            ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?= htmlspecialchars($diaNombre) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($horaInicio) ?></td>
                                    <td><?= htmlspecialchars($horaFin) ?></td>
                                    <td>
                                        <span class="badge <?= ($estado == 'ACTIVO') ? 'bg-success' : 'bg-warning text-dark' ?>">
                                            <?= $estado == 'ACTIVO' ? 'Disponible' : 'No Disponible' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 text-muted small">
                    <i class="fas fa-info-circle me-1"></i> Los horarios pueden estar sujetos a cambios. Se recomienda confirmar disponibilidad.
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Información:</strong> No hay horarios de atención registrados para este doctor.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Credenciales de Acceso -->
    <div class="seccion">
        <div class="seccion-titulo">
            <i class="fas fa-key me-2"></i> Credenciales de Acceso
        </div>
        <div class="seccion-contenido">
            <?php if ($infoUsuario): ?>
                <div class="fila">
                    <div class="etiqueta">Usuario:</div>
                    <div class="valor"><?= htmlspecialchars($infoUsuario['nomuser']) ?></div>
                </div>
                <div class="fila">
                    <div class="etiqueta">Estado:</div>
                    <div class="valor">
                        <span class="badge <?= ($infoUsuario['estado'] ? 'bg-success' : 'bg-danger') ?>">
                            <?= $infoUsuario['estado'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </div>
                </div>
                <div class="mt-2 text-info small">
                    <i class="fas fa-info-circle me-1"></i> Por razones de seguridad, no se muestra la contraseña. Para cambiarla, utilice la opción "Editar Credenciales".
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Advertencia:</strong> Este doctor no tiene credenciales de acceso configuradas.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="aviso-pie">
        Esta información se muestra con fines administrativos. Para realizar cambios, use las opciones de edición.
    </div>
</div>

<style>
    .detalles-doctor {
        font-family: Arial, sans-serif;
        color: #333;
        max-width: 800px;
        margin: 0 auto;
    }

    .seccion {
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .seccion-titulo {
        background-color: #007bff;
        color: white;
        padding: 12px 15px;
        font-weight: bold;
        font-size: 1.1rem;
    }

    .seccion-contenido {
        padding: 15px;
    }

    .fila {
        display: flex;
        border-bottom: 1px solid #eee;
        padding: 10px 0;
    }

    .fila:last-child {
        border-bottom: none;
    }

    .etiqueta {
        width: 180px;
        font-weight: bold;
        color: #555;
    }

    .valor {
        flex: 1;
    }

    .valor a {
        text-decoration: none;
    }
    
    .valor a:hover {
        text-decoration: underline;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .table th {
        background-color: #f5f5f5;
        padding: 8px;
        text-align: left;
        border: 1px solid #ddd;
        font-weight: 600;
    }

    .table td {
        padding: 8px;
        border: 1px solid #ddd;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.02);
    }

    .table-primary {
        background-color: #cfe2ff;
    }

    .table-danger {
        background-color: #f8d7da;
    }

    .alert {
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 15px;
    }
    
    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
    
    .alert-warning {
        background-color: #fff3cd;
        border-color: #ffeeba;
        color: #856404;
    }

    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.85em;
        font-weight: 600;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }

    .bg-success {
        background-color: #28a745 !important;
        color: white;
    }

    .bg-danger {
        background-color: #dc3545 !important;
        color: white;
    }

    .bg-warning {
        background-color: #ffc107 !important;
    }
    
    .bg-primary {
        background-color: #007bff !important;
        color: white;
    }

    .text-dark {
        color: #343a40 !important;
    }
    
    .text-danger {
        color: #dc3545 !important;
    }
    
    .text-info {
        color: #17a2b8 !important;
    }
    
    .text-muted {
        color: #6c757d !important;
    }

    .aviso-pie {
        margin-top: 15px;
        margin-bottom: 15px;
        font-size: 12px;
        font-style: italic;
        color: #666;
        text-align: center;
    }

    /* Mejoras responsivas */
    @media (max-width: 767.98px) {
        .fila {
            flex-direction: column;
            padding: 8px 0;
        }
        
        .etiqueta {
            width: 100%;
            margin-bottom: 4px;
        }
        
        .valor {
            width: 100%;
        }
    }

    /* Estilos para impresión */
    @media print {
        .seccion {
            box-shadow: none;
            border: 1px solid #000;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        
        .seccion-titulo {
            background-color: #f0f0f0 !important;
            color: #000 !important;
            border-bottom: 1px solid #000;
        }
        
        .badge {
            border: 1px solid #000;
            padding: 2px 5px;
        }
        
        .bg-success, .bg-danger, .bg-warning, .bg-primary {
            background-color: #ffffff !important;
            color: #000 !important;
            border: 1px solid #000;
        }
        
        .table th {
            background-color: #f0f0f0 !important;
            color: #000 !important;
        }
        
        .alert {
            border: 1px solid #000;
            background-color: #fff !important;
            color: #000 !important;
        }
    }
</style>