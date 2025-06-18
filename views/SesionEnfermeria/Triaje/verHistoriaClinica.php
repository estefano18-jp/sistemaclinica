<?php
// Incluir el encabezado de enfermería
include_once "../../include/header.enfermeria.php";

// Verificar que se recibió el ID de consulta
if (!isset($_GET['idconsulta']) || empty($_GET['idconsulta'])) {
    header('Location: registrarTriaje.php');
    exit;
}

$idconsulta = intval($_GET['idconsulta']);
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Detalles del Triaje</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= $host ?>/views/include/dashboard.enfermeria.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="registrarTriaje.php">Triaje</a></li>
                            <li class="breadcrumb-item active">Ver Triaje</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <button class="btn btn-secondary" onclick="window.close()">
                        <i class="fas fa-times me-1"></i>Cerrar
                    </button>
                    <button class="btn btn-primary" id="btn-imprimir">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor del triaje -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow" id="triaje-content">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-heartbeat me-2"></i>Registro de Triaje - Signos Vitales
                    </h5>
                </div>
                
                <div class="card-body" id="datos-triaje">
                    <!-- Los datos se cargarán aquí dinámicamente -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando datos del triaje...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        
        
        body {
            font-size: 12px;
        }
    }
    
    .vital-sign-card {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
        background-color: #f8f9fa;
    }
    
    .vital-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #495057;
    }
    
    .vital-label {
        font-size: 0.875rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .vital-unit {
        font-size: 1rem;
        color: #6c757d;
        margin-left: 0.25rem;
    }
    
    .priority-badge {
        font-size: 1rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
    }
    
    .priority-baja { background-color: #d1e7dd; color: #0f5132; }
    .priority-media { background-color: #fff3cd; color: #664d03; }
    .priority-alta { background-color: #f8d7da; color: #721c24; }
    .priority-critica { background-color: #f5c6cb; color: #721c24; font-weight: bold; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const idconsulta = <?= $idconsulta ?>;
    const datosTriajeContainer = document.getElementById('datos-triaje');
    const btnImprimir = document.getElementById('btn-imprimir');
    
    // Cargar datos del triaje
    cargarDatosTriaje();
    
    // Event listener para imprimir
    btnImprimir.addEventListener('click', function() {
        window.print();
    });
    
    function cargarDatosTriaje() {
        Promise.all([
            fetch(`<?= $host ?>/controllers/consulta.controller.php?op=obtener&id=${idconsulta}`),
            fetch(`<?= $host ?>/controllers/consulta.controller.php?op=obtener_triaje&idconsulta=${idconsulta}`)
        ])
        .then(responses => Promise.all(responses.map(response => response.json())))
        .then(([consultaData, triajeData]) => {
            if (consultaData.status && triajeData.status) {
                mostrarDatosTriaje(consultaData.data, triajeData.triaje);
            } else {
                mostrarError('No se pudieron cargar los datos del triaje');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al cargar los datos');
        });
    }
    
    function mostrarDatosTriaje(consulta, triaje) {
        const imc = calcularIMC(triaje.peso, triaje.estatura);
        const edad = calcularEdad(consulta.fechanacimiento);
        
        const html = `
            <!-- Información del paciente -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="bg-light p-3 rounded">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="text-primary mb-2">${consulta.paciente_nombres} ${consulta.paciente_apellidos}</h4>
                                <p class="mb-1"><strong>Documento:</strong> ${consulta.paciente_tipodoc} ${consulta.paciente_nrodoc}</p>
                                <p class="mb-1"><strong>Edad:</strong> ${edad} años</p>
                                <p class="mb-0"><strong>Género:</strong> ${consulta.genero}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Fecha:</strong> ${formatearFecha(consulta.fecha)}</p>
                                <p class="mb-1"><strong>Hora del triaje:</strong> ${triaje.hora.substring(0, 5)}</p>
                                <p class="mb-1"><strong>Doctor:</strong> ${consulta.doctor_nombres} ${consulta.doctor_apellidos}</p>
                                <p class="mb-0"><strong>Enfermera:</strong> ${triaje.enfermera_nombre}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Signos vitales -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="mb-3"><i class="fas fa-heartbeat text-danger me-2"></i>Signos Vitales</h5>
                </div>
                <div class="col-md-3">
                    <div class="vital-sign-card text-center">
                        <div class="vital-label">Temperatura</div>
                        <div class="vital-value">${triaje.temperatura || '--'}<span class="vital-unit">°C</span></div>
                        <small class="text-muted">Normal: 36.1 - 37.2°C</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="vital-sign-card text-center">
                        <div class="vital-label">Frecuencia Cardíaca</div>
                        <div class="vital-value">${triaje.frecuenciacardiaca || '--'}<span class="vital-unit">lpm</span></div>
                        <small class="text-muted">Normal: 60 - 100 lpm</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="vital-sign-card text-center">
                        <div class="vital-label">Presión Arterial</div>
                        <div class="vital-value">${triaje.presionarterial || '--'}<span class="vital-unit">mmHg</span></div>
                        <small class="text-muted">Normal: 120/80</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="vital-sign-card text-center">
                        <div class="vital-label">Sat. Oxígeno</div>
                        <div class="vital-value">${triaje.saturacionoxigeno || '--'}<span class="vital-unit">%</span></div>
                        <small class="text-muted">Normal: ≥ 95%</small>
                    </div>
                </div>
            </div>
            
            <!-- Medidas antropométricas -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="mb-3"><i class="fas fa-weight text-success me-2"></i>Medidas Antropométricas</h5>
                </div>
                <div class="col-md-3">
                    <div class="vital-sign-card text-center">
                        <div class="vital-label">Peso</div>
                        <div class="vital-value">${triaje.peso || '--'}<span class="vital-unit">kg</span></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="vital-sign-card text-center">
                        <div class="vital-label">Estatura</div>
                        <div class="vital-value">${triaje.estatura || '--'}<span class="vital-unit">m</span></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="vital-sign-card text-center">
                        <div class="vital-label">Índice de Masa Corporal (IMC)</div>
                        <div class="vital-value">${imc.valor}<span class="vital-unit">kg/m²</span></div>
                        <div><span class="badge ${imc.colorClass}">${imc.categoria}</span></div>
                    </div>
                </div>
            </div>
            
            <!-- Evaluación clínica -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="fas fa-clipboard-list text-warning me-2"></i>Evaluación Clínica</h5>
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Motivo de Consulta:</strong>
                                <p class="mb-2">${triaje.motivo_consulta || 'No especificado'}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Nivel de Prioridad:</strong>
                                <div class="mt-1">
                                    <span class="priority-badge priority-${triaje.prioridad.toLowerCase()}">
                                        ${getPriorityIcon(triaje.prioridad)} ${triaje.prioridad}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="fas fa-notes-medical text-info me-2"></i>Observaciones</h5>
                    <div class="card">
                        <div class="card-body">
                            <p class="mb-0">${triaje.observaciones || 'Sin observaciones registradas'}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        datosTriajeContainer.innerHTML = html;
    }
    
    function mostrarError(mensaje) {
        datosTriajeContainer.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5>${mensaje}</h5>
                <button class="btn btn-primary mt-3" onclick="cargarDatosTriaje()">
                    <i class="fas fa-redo me-1"></i>Reintentar
                </button>
            </div>
        `;
    }
    
    function calcularIMC(peso, estatura) {
        if (peso && estatura && peso > 0 && estatura > 0) {
            const imc = peso / (estatura * estatura);
            let categoria = '';
            let colorClass = '';
            
            if (imc < 18.5) {
                categoria = 'Bajo peso';
                colorClass = 'bg-info text-white';
            } else if (imc < 25) {
                categoria = 'Normal';
                colorClass = 'bg-success text-white';
            } else if (imc < 30) {
                categoria = 'Sobrepeso';
                colorClass = 'bg-warning text-dark';
            } else {
                categoria = 'Obesidad';
                colorClass = 'bg-danger text-white';
            }
            
            return {
                valor: imc.toFixed(1),
                categoria: categoria,
                colorClass: colorClass
            };
        }
        
        return {
            valor: '--',
            categoria: 'No calculado',
            colorClass: 'bg-secondary text-white'
        };
    }
    
    function calcularEdad(fechaNacimiento) {
        const hoy = new Date();
        const nacimiento = new Date(fechaNacimiento);
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();
        
        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--;
        }
        
        return edad;
    }
    
    function formatearFecha(fecha) {
        const date = new Date(fecha);
        return date.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
    
    function getPriorityIcon(prioridad) {
        switch(prioridad) {
            case 'BAJA': return '🟢';
            case 'MEDIA': return '🟡';
            case 'ALTA': return '🟠';
            case 'CRITICA': return '🔴';
            default: return '⚪';
        }
    }
});
</script>

<?php
include_once "../../include/footer.php";
?>