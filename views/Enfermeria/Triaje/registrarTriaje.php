<?php
include_once "../../include/header.enfermeria.php";

// Verificar si se recibió un ID de consulta
$idConsulta = isset($_GET['idconsulta']) ? intval($_GET['idconsulta']) : 0;

// Si hay un ID de consulta, obtener datos del paciente
$datosPaciente = null;
if ($idConsulta > 0) {
    require_once "../../../models/Conexion.php";
    $conexion = new Conexion();
    $pdo = $conexion->getConexion();
    
    $query = "SELECT 
                c.idconsulta, 
                c.fecha, 
                c.horaprogramada,
                pac.idpaciente,
                p.idpersona,
                p.nombres,
                p.apellidos,
                p.nrodoc,
                TIMESTAMPDIFF(YEAR, p.fechanacimiento, CURDATE()) AS edad,
                p.genero,
                e.especialidad,
                CONCAT(doc.nombres, ' ', doc.apellidos) as doctor_nombre
            FROM consultas c
            INNER JOIN pacientes pac ON c.idpaciente = pac.idpaciente
            INNER JOIN personas p ON pac.idpersona = p.idpersona
            INNER JOIN horarios h ON c.idhorario = h.idhorario
            INNER JOIN atenciones a ON h.idatencion = a.idatencion
            INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
            INNER JOIN colaboradores col ON ct.idcolaborador = col.idcolaborador
            INNER JOIN personas doc ON col.idpersona = doc.idpersona
            INNER JOIN especialidades e ON col.idespecialidad = e.idespecialidad
            WHERE c.idconsulta = :idconsulta";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':idconsulta', $idConsulta);
    $stmt->execute();
    $datosPaciente = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Registro de Triaje</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= $host ?>/views/include/dashboard.enfermeria.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Registro de Triaje</li>
    </ol>

    <div class="row">
        <!-- Selección de consulta si no se recibió una -->
        <?php if (!$datosPaciente): ?>
            <div class="col-12">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <i class="fas fa-calendar-check me-1"></i>
                        Seleccionar Consulta
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="fechaConsulta" class="form-label">Fecha de Consulta</label>
                                <input type="date" class="form-control" id="fechaConsulta" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="d-block">&nbsp;</label>
                                <button type="button" class="btn btn-primary" id="btnBuscarConsultas">
                                    <i class="fas fa-search me-1"></i> Buscar Consultas
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="tablaConsultas">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hora</th>
                                        <th>Paciente</th>
                                        <th>Documento</th>
                                        <th>Especialidad</th>
                                        <th>Doctor</th>
                                        <th>Estado</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Se carga dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulario de triaje si hay un paciente seleccionado -->
        <?php if ($datosPaciente): ?>
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-user-injured me-1"></i>
                        Datos del Paciente
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="avatar avatar-lg bg-primary rounded-circle text-white">
                                <?= strtoupper(substr($datosPaciente['nombres'], 0, 1)) ?>
                            </div>
                        </div>
                        <h5 class="text-center"><?= $datosPaciente['nombres'] . ' ' . $datosPaciente['apellidos'] ?></h5>
                        <p class="text-center text-muted"><?= $datosPaciente['nrodoc'] ?></p>
                        
                        <hr>
                        
                        <div class="mb-2">
                            <small class="text-muted d-block">Edad:</small>
                            <strong><?= $datosPaciente['edad'] ?> años</strong>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted d-block">Género:</small>
                            <strong><?= $datosPaciente['genero'] === 'M' ? 'Masculino' : ($datosPaciente['genero'] === 'F' ? 'Femenino' : 'Otro') ?></strong>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted d-block">Fecha de Consulta:</small>
                            <strong><?= date('d/m/Y', strtotime($datosPaciente['fecha'])) ?></strong>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted d-block">Hora Programada:</small>
                            <strong><?= date('H:i', strtotime($datosPaciente['horaprogramada'])) ?></strong>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted d-block">Especialidad:</small>
                            <strong><?= $datosPaciente['especialidad'] ?></strong>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted d-block">Doctor/a:</small>
                            <strong><?= $datosPaciente['doctor_nombre'] ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-heartbeat me-1"></i>
                        Registro de Signos Vitales
                    </div>
                    <div class="card-body">
                        <form id="formTriaje">
                            <input type="hidden" id="idconsulta" name="idconsulta" value="<?= $datosPaciente['idconsulta'] ?>">
                            <input type="hidden" id="idenfermera" name="idenfermera" value="<?= $_SESSION['usuario']['idcolaborador'] ?>">
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="temperatura" class="form-label">Temperatura (°C) *</label>
                                    <input type="number" class="form-control" id="temperatura" name="temperatura" step="0.1" min="35" max="42" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="presionarterial" class="form-label">Presión Arterial (mmHg) *</label>
                                    <input type="text" class="form-control" id="presionarterial" name="presionarterial" placeholder="Ej: 120/80" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="frecuenciacardiaca" class="form-label">Frecuencia Cardíaca (lpm) *</label>
                                    <input type="number" class="form-control" id="frecuenciacardiaca" name="frecuenciacardiaca" min="40" max="200" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="saturacionoxigeno" class="form-label">Saturación O2 (%) *</label>
                                    <input type="number" class="form-control" id="saturacionoxigeno" name="saturacionoxigeno" min="70" max="100" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="peso" class="form-label">Peso (kg) *</label>
                                    <input type="number" class="form-control" id="peso" name="peso" step="0.1" min="0" max="200" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="estatura" class="form-label">Estatura (m) *</label>
                                    <input type="number" class="form-control" id="estatura" name="estatura" step="0.01" min="0" max="2.5" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="hora" class="form-label">Hora de Triaje</label>
                                <input type="time" class="form-control" id="hora" name="hora" value="<?= date('H:i') ?>">
                            </div>
                            
                            <div class="d-flex justify-content-center mt-4">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save me-1"></i> Guardar Triaje
                                </button>
                                <a href="<?= $host ?>/views/Enfermeria/ConsultasPendientes/consultasPendientes.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!$datosPaciente): ?>
        // Inicializar DataTable
        const tablaConsultas = $('#tablaConsultas').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            },
            responsive: true,
            order: [[0, 'asc']]
        });

        // Cargar consultas pendientes al cargar la página
        cargarConsultasPendientes();

        // Botón de búsqueda de consultas
        document.getElementById('btnBuscarConsultas').addEventListener('click', function() {
            cargarConsultasPendientes();
        });

        function cargarConsultasPendientes() {
            const fecha = document.getElementById('fechaConsulta').value;
            
            // Mostrar carga
            Swal.fire({
                title: 'Cargando...',
                text: 'Buscando consultas pendientes',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`<?= $host ?>/controllers/enfermeria.controller.php?op=consultas_pendientes&fecha=${fecha}`)
                .then(response => response.json())
                .then(data => {
                    Swal.close();

                    // Limpiar tabla
                    tablaConsultas.clear();

                    if (data.status && data.data && data.data.length > 0) {
                        // Agregar datos a la tabla
                        data.data.forEach(consulta => {
                            const estadoClass = consulta.estado_triaje === 'PENDIENTE' 
                                ? 'badge bg-warning text-dark' 
                                : 'badge bg-success';
                            
                            tablaConsultas.row.add([
                                `<span>${consulta.horaprogramada}</span>`,
                                `<span>${consulta.nombre_paciente}</span>`,
                                `<span>${consulta.nrodoc}</span>`,
                                `<span>${consulta.especialidad}</span>`,
                                `<span>${consulta.nombre_doctor}</span>`,
                                `<span class="${estadoClass}">${consulta.estado_triaje}</span>`,
                                `<a href="<?= $host ?>/views/Enfermeria/Triaje/registrarTriaje.php?idconsulta=${consulta.idconsulta}" 
                                    class="btn btn-sm btn-primary ${consulta.estado_triaje !== 'PENDIENTE' ? 'disabled' : ''}">
                                    <i class="fas fa-heartbeat me-1"></i> Realizar Triaje
                                </a>`
                            ]);
                        });
                    } else {
                        // Si no hay datos, mostrar mensaje
                        Swal.fire({
                            icon: 'info',
                            title: 'Sin consultas pendientes',
                            text: 'No hay consultas pendientes de triaje para la fecha seleccionada'
                        });
                    }

                    // Dibujar tabla
                    tablaConsultas.draw();
                })
                .catch(error => {
                    console.error("Error:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor'
                    });
                });
        }
        <?php endif; ?>

        <?php if ($datosPaciente): ?>
        // Envío del formulario de triaje
        document.getElementById('formTriaje').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar campos
            const temperatura = parseFloat(document.getElementById('temperatura').value);
            const presionarterial = document.getElementById('presionarterial').value;
            const frecuenciacardiaca = parseInt(document.getElementById('frecuenciacardiaca').value);
            const saturacionoxigeno = parseInt(document.getElementById('saturacionoxigeno').value);
            const peso = parseFloat(document.getElementById('peso').value);
            const estatura = parseFloat(document.getElementById('estatura').value);
            
            if (!temperatura || !presionarterial || !frecuenciacardiaca || 
                !saturacionoxigeno || !peso || !estatura) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos incompletos',
                    text: 'Por favor complete todos los campos obligatorios'
                });
                return;
            }
            
            // Validar formato de presión arterial
            const presionRegex = /^\d{2,3}\/\d{2,3}$/;
            if (!presionRegex.test(presionarterial)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Formato incorrecto',
                    text: 'La presión arterial debe tener el formato 120/80'
                });
                return;
            }
            
            const formData = new FormData(this);
            formData.append('op', 'registrar_triaje');

            // Mostrar carga
            Swal.fire({
                title: 'Guardando...',
                text: 'Registrando triaje',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('<?= $host ?>/controllers/enfermeria.controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Triaje registrado',
                        text: data.mensaje,
                        confirmButtonText: 'Ver consultas pendientes',
                        showCancelButton: true,
                        cancelButtonText: 'Registrar otro triaje'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '<?= $host ?>/views/Enfermeria/ConsultasPendientes/consultasPendientes.php';
                        } else {
                            window.location.href = '<?= $host ?>/views/Enfermeria/Triaje/registrarTriaje.php';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.mensaje
                    });
                }
            })
            .catch(error => {
                console.error("Error:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor'
                });
            });
        });
        <?php endif; ?>
    });
</script>

<?php
include_once "../../include/footer.php";
?>