/**
 * sistemaclinica/js/reservacioncitas/citas.js
 */

// Registrar nueva cita - modificar para asegurar que devuelve una promesa
async function registrarCita(datos) {
  try {
    // Validar datos mínimos
    if (
      !datos.iddoctor ||
      !datos.idpaciente ||
      !datos.fecha ||
      !datos.hora ||
      !datos.idespecialidad
    ) {
      console.error("Datos incompletos para registrar cita");
      return {
        status: false,
        mensaje: "Datos incompletos para registrar cita",
      };
    }

    const formData = new FormData();

    // Agregar datos al formulario
    for (const key in datos) {
      formData.append(key, datos[key]);
    }

    // Enviar datos al servidor
    const response = await fetchData(
      "../../../controllers/cita.controller.php?op=registrar",
      {
        method: "POST",
        body: formData,
      }
    );

    return response;
  } catch (error) {
    console.error("Error en registrarCita:", error);
    return {
      status: false,
      mensaje: "Error al registrar cita: " + error.message,
    };
  }
}

// Cargar citas en el calendario
async function cargarCitasCalendario(calendario, filtros = {}) {
  try {
    console.log("Cargando citas con filtros:", filtros);
    
    // Mostrar indicador de carga
    mostrarCargandoCalendario('Cargando citas...');
    
    // Construir URL con los filtros
    let url = '/sistemaclinica/controllers/cita.controller.php?op=calendario';
    
    // Agregar parámetros de inicio y fin si no están definidos
    if (!filtros.fechaInicio && !filtros.fechaFin) {
      const view = calendario.view;
      filtros.fechaInicio = view.activeStart.toISOString().substring(0, 10);
      filtros.fechaFin = view.activeEnd.toISOString().substring(0, 10);
    }
    
    // Agregar filtros a la URL
    if (filtros.idespecialidad) {
      url += `&idespecialidad=${filtros.idespecialidad}`;
    }
    
    if (filtros.iddoctor) {
      url += `&iddoctor=${filtros.iddoctor}`;
    }
    
    if (filtros.fechaInicio) {
      url += `&start=${filtros.fechaInicio}`;
    }
    
    if (filtros.fechaFin) {
      url += `&end=${filtros.fechaFin}`;
    }
    
    console.log("URL para carga de citas CORREGIDA:", url);
    
    // Realizar la petición al servidor
    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`Error HTTP: ${response.status}`);
    }
    
    const citas = await response.json();
    
    // Verificar si se recibieron datos válidos
    if (!Array.isArray(citas)) {
      console.error("Formato de respuesta incorrecto:", citas);
      throw new Error("El servidor no devolvió un formato válido de citas");
    }
    
    console.log(`Se obtuvieron ${citas.length} citas del servidor CORREGIDAS:`, citas);
    
    // DEBUGGING MEJORADO: Mostrar detalles de cada cita
    citas.forEach((cita, index) => {
      console.log(`Cita ${index + 1}:`, {
        id: cita.id,
        titulo: cita.title,
        doctor: cita.extendedProps?.doctor,
        paciente: cita.extendedProps?.paciente,
        especialidad: cita.extendedProps?.especialidad,
        iddoctor: cita.extendedProps?.iddoctor,
        idconsulta: cita.extendedProps?.idconsulta
      });
    });
    
    // OPTIMIZACIÓN: Mejorar formato de citas Y aplicar visualización en UNA SOLA OPERACIÓN
    const citasFormateadas = citas.map(cita => {
      // Asegurarnos que los datos extendedProps están presentes
      const extendedProps = cita.extendedProps || {};
      
      // DEBUGGING: Log de cada cita formateada
      console.log(`Formateando cita ${cita.id}:`, {
        doctorOriginal: extendedProps.doctor,
        pacienteOriginal: extendedProps.paciente,
        especialidadOriginal: extendedProps.especialidad
      });
      
      // Asegurarnos que tengamos datos consistentes para mostrar
      return {
        ...cita,
        title: formatearTituloCita(cita),
        extendedProps: {
          ...extendedProps,
          doctor: extendedProps.doctor || 'Sin asignar',
          especialidad: extendedProps.especialidad || 'Consulta general',
          paciente: extendedProps.paciente || 'Paciente',
          estado: extendedProps.estado || 'PROGRAMADA',
          iddoctor: extendedProps.iddoctor || 0,
          idconsulta: extendedProps.idconsulta || 0
        }
      };
    });
    
    // MEJORA: Sistema de detección de duplicados más específico
    const citasUnicas = [];
    const citasVistas = new Map();
    
    citasFormateadas.forEach(cita => {
      // SOLUCIÓN: Crear clave única MÁS ESPECÍFICA que incluya el ID de la consulta
      const claveUnica = `${cita.id}-${cita.start}-${cita.extendedProps.idconsulta}-${cita.extendedProps.iddoctor}`;
      
      if (!citasVistas.has(claveUnica)) {
        citasVistas.set(claveUnica, true);
        citasUnicas.push(cita);
        console.log(`Cita única agregada: ${claveUnica}`, {
          doctor: cita.extendedProps.doctor,
          paciente: cita.extendedProps.paciente
        });
      } else {
        console.log(`Cita duplicada eliminada: ${claveUnica}`);
      }
    });
    
    console.log(`Se procesaron ${citasUnicas.length} citas únicas CORREGIDAS`);
    
    // IMPORTANTE: Asegurarnos de limpiar completamente el calendario antes de agregar nuevos eventos
    calendario.removeAllEvents();
    
    // Pequeña pausa para asegurar que todos los eventos sean eliminados
    await new Promise(resolve => setTimeout(resolve, 50));
    
    // Añadir los eventos filtrados
    calendario.addEventSource(citasUnicas);
    
    // INTEGRACIÓN CLAVE: Aplicar mejoras de visualización INMEDIATAMENTE
    aplicarVisualizacionMejoradaIntegrada(calendario);
    
    // Guardar filtros aplicados para referencia futura
    window.filtrosCalendario = { ...filtros };
    
    // Ocultar indicador de carga
    ocultarCargandoCalendario();
    
    return citasUnicas;
  } catch (error) {
    console.error('Error en cargarCitasCalendario CORREGIDA:', error);
    
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudieron cargar las citas. Inténtelo de nuevo.'
    });
    
    ocultarCargandoCalendario();
    throw error;
  }
}
function aplicarVisualizacionMejoradaIntegrada(calendario) {
  console.log("Aplicando visualización mejorada integrada (SIN cargas adicionales)");

  // 1. Configurar cómo se muestran los eventos en el calendario
  calendario.setOption("eventContent", function(info) {
    // Obtener información de la especialidad para determinar el color del punto
    const especialidad = info.event.extendedProps.especialidad || '';
    const dotColor = obtenerColorEspecialidad(especialidad);
    
    // Crear el contenido personalizado para cada cita
    const horaInicio = info.event.start 
      ? new Date(info.event.start).toLocaleTimeString('es-ES', {
          hour: '2-digit',
          minute: '2-digit',
          hour12: false
        })
      : '';
    
    // Formato de evento para vistas de mes y semana (ambas usan dayGrid)
    if (calendario.view.type === 'dayGridMonth' || calendario.view.type === 'dayGridWeek') {
      return {
        html: `
          <div class="fc-event-compact" style="font-size: 0.8em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding: 2px 4px; background-color: white; color: #333; display: flex; align-items: center;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: ${dotColor}; margin-right: 4px; flex-shrink: 0;"></span>
            <span style="font-weight: bold;">${horaInicio}</span> | ${especialidad}
          </div>
        `
      };
    } 
    // Para vista de día, mostrar formato detallado
    else {
      return {
        html: `
          <div class="fc-event-detail" style="padding: 3px 5px; overflow: hidden; background-color: white; color: #333; border-left: 3px solid ${dotColor};">
            <div style="font-weight: bold; margin-bottom: 2px; display: flex; align-items: center;">
              <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: ${dotColor}; margin-right: 4px;"></span>
              ${horaInicio} | ${especialidad}
            </div>
            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 0.9em;">
              <i class="fas fa-user-md" style="margin-right: 3px;"></i>${info.event.extendedProps.doctor || ''}
            </div>
            <div style="font-size: 0.85em; color: #666;">
              <i class="fas fa-user" style="margin-right: 3px;"></i>${info.event.extendedProps.paciente || ''}
            </div>
          </div>
        `
      };
    }
  });

  // 2. Personalizar el aspecto visual de los eventos
  calendario.setOption("eventDidMount", function(info) {
    // Determinar color según especialidad para el punto
    const especialidad = info.event.extendedProps.especialidad || '';
    const dotColor = obtenerColorEspecialidad(especialidad);
    
    // Aplicar estilos al evento - fondo blanco
    info.el.style.backgroundColor = 'white';
    info.el.style.borderColor = '#ddd';
    info.el.style.color = '#333';
    info.el.style.borderRadius = '4px';
    info.el.style.fontSize = '0.85em';
    
    // Añadir el borde izquierdo con el color de la especialidad
    info.el.style.borderLeft = `3px solid ${dotColor}`;
    
    // Añadir tooltip con información completa
    const tooltipContent = `
      Paciente: ${info.event.extendedProps.paciente || 'N/A'} - Doctor: ${info.event.extendedProps.doctor || 'N/A'} - Especialidad: ${info.event.extendedProps.especialidad || 'N/A'} - Estado: ${info.event.extendedProps.estado || "PROGRAMADA"} - Hora: ${new Date(info.event.start).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
      })}
    `;
    
    // Crear tooltip utilizando title
    info.el.title = tooltipContent;
    
    // Añadir clase para identificar eventos de citas
    info.el.classList.add('cita-evento');
  });

  // 3. Mejorar respuesta a clic en evento
  calendario.setOption("eventClick", function(info) {
    // Mostrar detalles de la cita al hacer clic
    mostrarDetallesCita(info.event);
    // Prevenir comportamiento por defecto
    info.jsEvent.preventDefault();
  });

  // 4. Añadir indicadores complementarios para días con citas
  calendario.setOption("dayCellDidMount", function(info) {
    // Obtener todas las citas para este día
    const fechaCelda = info.date.toISOString().split("T")[0];
    const citas = calendario.getEvents().filter((evento) => {
      const fechaEvento = evento.start.toISOString().split("T")[0];
      return fechaEvento === fechaCelda;
    });

    // Si hay citas para este día, añadir un indicador visual
    if (citas.length > 0) {
      // Añadir un indicador en la esquina superior para una mejor visualización
      const indicador = document.createElement("div");
      indicador.className = "dia-con-citas-indicador";
      indicador.style.position = "absolute";
      indicador.style.top = "2px";
      indicador.style.right = "2px";
      indicador.style.width = "8px";
      indicador.style.height = "8px";
      indicador.style.backgroundColor = "#0d6efd";
      indicador.style.borderRadius = "50%";
      indicador.style.zIndex = "5";
      
      // Añadir el indicador a la celda del día
      info.el.appendChild(indicador);
      
      // Aplicar un sutil fondo para destacar el día 
      info.el.style.backgroundColor = "rgba(13, 110, 253, 0.03)";
    }
  });

  // 5. Mejorar la vista cuando no hay eventos
  calendario.setOption("noEventsContent", function() {
    return {
      html: `<div style="padding: 8px; color: #666;">No hay citas programadas para este período</div>`
    };
  });

  // 6. Añadir estilos personalizados SOLO UNA VEZ
  if (!document.getElementById('estilos-calendario-mejorado')) {
    const estilos = document.createElement("style");
    estilos.id = 'estilos-calendario-mejorado';
    estilos.textContent = `
      /* Estilos para eventos en el calendario */
      .fc-event.cita-evento {
        cursor: pointer !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        background-color: white !important;
        color: #333 !important;
        border-color: #ddd !important;
      }
      
      .fc-event.cita-evento:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 3px 5px rgba(0,0,0,0.2) !important;
      }
      
      /* Estilos para días con citas */
      .fc-daygrid-day:hover .dia-con-citas-indicador {
        transform: scale(1.2);
      }
      
      /* Mejora en la altura mínima de las celdas de día para mostrar eventos */
      .fc-daygrid-day-frame {
        min-height: 80px !important;
      }
      
      /* NUEVO: Estilos específicos para la vista semanal */
      .fc-dayGridWeek-view .fc-daygrid-day-frame {
        min-height: 100px !important;
      }
      
      /* Asegurar que el enlace "+more" sea claramente visible */
      .fc-daygrid-more-link {
        margin: 2px 0;
        padding: 2px 4px;
        background-color: #f8f9fa;
        border-radius: 3px;
        font-size: 0.75em;
        color: #0d6efd;
        text-align: center;
        display: block;
      }
      
      /* Mejorar la visualización en dispositivos móviles */
      @media (max-width: 768px) {
        .fc-event-compact {
          font-size: 0.7em !important;
        }
      }
    `;
    document.head.appendChild(estilos);
  }

  console.log("Visualización del calendario integrada aplicada correctamente");
}


function formatearTituloCita(cita) {
  // Extraer datos relevantes
  const extendedProps = cita.extendedProps || {};
  const paciente = extendedProps.paciente || 'Paciente';
  const especialidad = extendedProps.especialidad || 'Consulta general';
  const doctor = extendedProps.doctor || '';
  
  // Formatear hora de inicio
  let horaFormateada = '';
  if (cita.start) {
    const fecha = new Date(cita.start);
    horaFormateada = fecha.toLocaleTimeString('es-ES', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: false
    });
  }
  
  // Crear título formateado según el formato solicitado
  return `${horaFormateada} | ${especialidad} | ${doctor.split(',')[0]}`;
}

// Mostrar detalles de una cita
function mostrarDetallesCita(infoCita) {
  try {
    // Verificar si tenemos un objeto de cita válido
    if (!infoCita) {
      console.error("No se recibieron datos de la cita");
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudieron cargar los detalles de la cita"
      });
      return;
    }
    
    // Log para depuración
    console.log("Mostrando detalles de cita:", infoCita);
    console.log("Datos extendidos:", infoCita.extendedProps);
    
    // Calcular duración de la cita en minutos si tenemos hora de inicio y fin
    let duracion = '';
    if (infoCita.start && infoCita.end) {
      const inicio = new Date(infoCita.start);
      const fin = new Date(infoCita.end);
      const minutos = Math.round((fin - inicio) / 60000);
      duracion = `${minutos} minutos`;
    }
    
    
    // Obtener estado de la cita
    const estado = infoCita.extendedProps?.estado || "PROGRAMADA";
    
    // Formatear fechas
    const fechaFormateada = formatearFecha(infoCita.start);
    const horaInicio = formatearHora(infoCita.start);
    const horaFin = infoCita.end ? formatearHora(infoCita.end) : '';
    
    // Obtener datos adicionales
    const paciente = infoCita.extendedProps?.paciente || 'Sin información';
    const doctor = infoCita.extendedProps?.doctor || 'Sin asignar';
    const especialidad = infoCita.extendedProps?.especialidad || 'No especificada';
    
    // Crear contenido para el modal con diseño mejorado
    let contenido = `
      <div class="modal-header bg-${obtenerClaseEstado(estado)} text-white">
        <h5 class="modal-title">
          <i class="${obtenerIconoEstado(estado)} me-2"></i>
          Detalles de la Cita
        </h5>
        <button type="button" class="btn-close btn-close-white" onclick="cerrarModalCompletamente('modalDetallesCita')" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
        <!-- Encabezado de la cita (mejorado sin separadores) -->
        <div class="card mb-4 border-0">
          <div class="card-body text-center p-3" style="background-color: #f8f9fa;">
            <!-- Horario y duración -->
            <div class="mb-2">
              <span class="d-inline-flex align-items-center me-2">
                <i class="far fa-clock text-primary me-2"></i>
                <span style="font-weight: 600; font-size: 1.1rem;">${horaInicio} - ${horaFin || horaInicio}</span>
              </span>
            </div>
            
            <!-- Especialidad -->
            <div class="mb-2">
              <span class="d-inline-flex align-items-center me-2">
                <i class="fas fa-stethoscope text-primary me-2"></i>
                <span style="font-weight: 600; font-size: 1.1rem;">${especialidad}</span>
              </span>
            </div>
            
            <!-- Doctor -->
            <div class="mb-3">
              <span class="d-inline-flex align-items-center">
                <i class="fas fa-user-md text-primary me-2"></i>
                <span style="font-weight: 600; font-size: 1.1rem;">Dr. ${doctor.split(',')[0]}</span>
              </span>
            </div>
            
            <!-- Estado de la cita -->
            <span class="badge bg-${obtenerClaseEstado(estado)} px-3 py-2">
              ${estado}
            </span>
          </div>
        </div>
        
        <!-- Datos de la cita (sin cambios) -->
        <div class="row g-3">
          <!-- Columna de fecha y hora -->
          <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body">
                <h6 class="card-title">
                  <i class="far fa-calendar-alt me-2 text-primary"></i>
                  Información de la Cita
                </h6>
                <hr>
                
                <div class="mb-2">
                  <div class="text-muted small">Fecha</div>
                  <div class="fw-bold">${fechaFormateada}</div>
                </div>
                
                <div class="mb-2">
                  <div class="text-muted small">Hora</div>
                  <div class="fw-bold">${horaInicio}${horaFin ? ` - ${horaFin}` : ''}</div>
                </div>
                
                ${duracion ? `
                <div class="mb-2">
                  <div class="text-muted small">Duración</div>
                  <div class="fw-bold">${duracion}</div>
                </div>
                ` : ''}
                
                <div class="mb-2">
                  <div class="text-muted small">Especialidad</div>
                  <div class="fw-bold">${especialidad}</div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Columna de personas -->
          <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body">
                <h6 class="card-title">
                  <i class="far fa-user me-2 text-primary"></i>
                  Información de Contacto
                </h6>
                <hr>
                
                <div class="mb-2">
                  <div class="text-muted small">Paciente</div>
                  <div class="fw-bold">${paciente}</div>
                </div>
                
                <div class="mb-2">
                  <div class="text-muted small">Doctor</div>
                  <div class="fw-bold">${doctor}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="cerrarModalCompletamente('modalDetallesCita')">Cerrar</button>
      </div>
    `;

    // Verificar si existe un modal abierto y cerrarlo primero
    const modalActual = bootstrap.Modal.getInstance(document.getElementById("modalDetallesCita"));
    if (modalActual) {
      modalActual.dispose();
    }

    // Eliminar cualquier backdrop existente antes de abrir el nuevo modal
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
    document.body.classList.remove('modal-open');

    // Actualizar contenido del modal
    document.getElementById("contenidoModalDetallesCita").innerHTML = contenido;

    // Mostrar el modal con configuración explícita
    const modalElement = document.getElementById("modalDetallesCita");
    modalElement.setAttribute('data-bs-backdrop', 'true');
    modalElement.setAttribute('data-bs-keyboard', 'true');
    
    const modalDetalles = new bootstrap.Modal(modalElement, {
      backdrop: true,
      keyboard: true,
      focus: true
    });
    
    // Guardar referencia al modal actual
    window.modalDetallesCitaActual = modalDetalles;
    
    // Mostrar el modal
    modalDetalles.show();
  } catch (error) {
    console.error("Error al mostrar detalles de cita:", error);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "No se pudieron mostrar los detalles de la cita. Inténtelo de nuevo."
    });
  }
}
function obtenerClaseEstado(estado) {
  switch (estado) {
    case "PROGRAMADA":
      return "primary";
    case "REALIZADA":
      return "success";
    case "CANCELADA":
      return "danger";
    case "NO ASISTIO":
      return "warning";
    default:
      return "secondary";
  }
}
/**
 * Obtiene el icono FontAwesome según el estado de la cita
 * @param {string} estado Estado de la cita
 * @returns {string} Clase CSS del icono
 */
function obtenerIconoEstado(estado) {
  switch (estado) {
    case "PROGRAMADA":
      return "far fa-clock";
    case "REALIZADA":
      return "fas fa-check-circle";
    case "CANCELADA":
      return "fas fa-ban";
    case "NO ASISTIO":
      return "fas fa-user-times";
    default:
      return "fas fa-calendar-check";
  }
}
// Obtener clase de color según estado de la cita
function obtenerColorEstado(estado) {
  switch (estado) {
    case "PROGRAMADA":
      return "#0d6efd"; // Azul - Primary
    case "REALIZADA":
      return "#198754"; // Verde - Success
    case "CANCELADA":
      return "#dc3545"; // Rojo - Danger
    case "NO ASISTIO":
      return "#ffc107"; // Amarillo - Warning
    default:
      return "#6c757d"; // Gris - Secondary
  }
}
/**
 * Mejora la visualización del calendario mostrando un indicador compacto
 * @param {Object} calendario Instancia de FullCalendar
 */
function mejorarVisualizacionCalendario(calendario) {
  console.log("Aplicando visualización mejorada al calendario con citas detalladas");

  // 1. Configurar cómo se muestran los eventos en el calendario
  calendario.setOption("eventContent", function(info) {
    // Obtener información de la especialidad para determinar el color del punto
    const especialidad = info.event.extendedProps.especialidad || '';
    const dotColor = obtenerColorEspecialidad(especialidad);
    
    // Crear el contenido personalizado para cada cita
    const horaInicio = info.event.start 
      ? new Date(info.event.start).toLocaleTimeString('es-ES', {
          hour: '2-digit',
          minute: '2-digit',
          hour12: false
        })
      : '';
    
    // Formato de evento para vistas de mes y semana (ambas usan dayGrid)
    if (calendario.view.type === 'dayGridMonth' || calendario.view.type === 'dayGridWeek') {
      return {
        html: `
          <div class="fc-event-compact" style="font-size: 0.8em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding: 2px 4px; background-color: white; color: #333; display: flex; align-items: center;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: ${dotColor}; margin-right: 4px; flex-shrink: 0;"></span>
            <span style="font-weight: bold;">${horaInicio}</span> | ${especialidad}
          </div>
        `
      };
    } 
    // Para vista de día, mostrar formato detallado
    else {
      return {
        html: `
          <div class="fc-event-detail" style="padding: 3px 5px; overflow: hidden; background-color: white; color: #333; border-left: 3px solid ${dotColor};">
            <div style="font-weight: bold; margin-bottom: 2px; display: flex; align-items: center;">
              <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: ${dotColor}; margin-right: 4px;"></span>
              ${horaInicio} | ${especialidad}
            </div>
            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 0.9em;">
              <i class="fas fa-user-md" style="margin-right: 3px;"></i>${info.event.extendedProps.doctor || ''}
            </div>
            <div style="font-size: 0.85em; color: #666;">
              <i class="fas fa-user" style="margin-right: 3px;"></i>${info.event.extendedProps.paciente || ''}
            </div>
          </div>
        `
      };
    }
  });

  // 2. Personalizar el aspecto visual de los eventos
  calendario.setOption("eventDidMount", function(info) {
    // Determinar color según especialidad para el punto
    const especialidad = info.event.extendedProps.especialidad || '';
    const dotColor = obtenerColorEspecialidad(especialidad);
    
    // Aplicar estilos al evento - fondo blanco
    info.el.style.backgroundColor = 'white';
    info.el.style.borderColor = '#ddd';
    info.el.style.color = '#333';
    info.el.style.borderRadius = '4px';
    info.el.style.fontSize = '0.85em';
    
    // Añadir el borde izquierdo con el color de la especialidad
    info.el.style.borderLeft = `3px solid ${dotColor}`;
    
    // Añadir tooltip con información completa
    const tooltipContent = `
      <strong>Paciente:</strong> ${info.event.extendedProps.paciente || 'N/A'}<br>
      <strong>Doctor:</strong> ${info.event.extendedProps.doctor || 'N/A'}<br>
      <strong>Especialidad:</strong> ${info.event.extendedProps.especialidad || 'N/A'}<br>
      <strong>Estado:</strong> ${info.event.extendedProps.estado || "PROGRAMADA"}<br>
      <strong>Hora:</strong> ${new Date(info.event.start).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
      })}
    `;
    
    // Crear tooltip utilizando title
    info.el.title = tooltipContent.replace(/<br>/g, ' - ').replace(/<[^>]*>/g, '');
    
    // Añadir clase para identificar eventos de citas
    info.el.classList.add('cita-evento');
  });

  // 3. Mejorar respuesta a clic en evento
  calendario.setOption("eventClick", function(info) {
    // Mostrar detalles de la cita al hacer clic
    mostrarDetallesCita(info.event);
    // Prevenir comportamiento por defecto
    info.jsEvent.preventDefault();
  });

  // 4. Añadir indicadores complementarios para días con citas
  calendario.setOption("dayCellDidMount", function(info) {
    // Obtener todas las citas para este día
    const fechaCelda = info.date.toISOString().split("T")[0];
    const citas = calendario.getEvents().filter((evento) => {
      const fechaEvento = evento.start.toISOString().split("T")[0];
      return fechaEvento === fechaCelda;
    });

    // Si hay citas para este día, añadir un indicador visual
    if (citas.length > 0) {
      // Añadir un indicador en la esquina superior para una mejor visualización
      const indicador = document.createElement("div");
      indicador.className = "dia-con-citas-indicador";
      indicador.style.position = "absolute";
      indicador.style.top = "2px";
      indicador.style.right = "2px";
      indicador.style.width = "8px";
      indicador.style.height = "8px";
      indicador.style.backgroundColor = "#0d6efd";
      indicador.style.borderRadius = "50%";
      indicador.style.zIndex = "5";
      
      // Añadir el indicador a la celda del día
      info.el.appendChild(indicador);
      
      // Aplicar un sutil fondo para destacar el día 
      info.el.style.backgroundColor = "rgba(13, 110, 253, 0.03)";
    }
  });

  // 5. Mejorar la vista cuando no hay eventos
  calendario.setOption("noEventsContent", function() {
    return {
      html: `<div style="padding: 8px; color: #666;">No hay citas programadas para este período</div>`
    };
  });

  // 6. Añadir estilos personalizados para mejorar la visualización
  const estilos = document.createElement("style");
  estilos.textContent = `
    /* Estilos para eventos en el calendario */
    .fc-event.cita-evento {
      cursor: pointer !important;
      box-shadow: 0 1px 3px rgba(0,0,0,0.12) !important;
      transition: transform 0.2s ease, box-shadow 0.2s ease !important;
      background-color: white !important;
      color: #333 !important;
      border-color: #ddd !important;
    }
    
    .fc-event.cita-evento:hover {
      transform: translateY(-1px) !important;
      box-shadow: 0 3px 5px rgba(0,0,0,0.2) !important;
    }
    
    /* Estilos para días con citas */
    .fc-daygrid-day:hover .dia-con-citas-indicador {
      transform: scale(1.2);
    }
    
    /* Mejora en la altura mínima de las celdas de día para mostrar eventos */
    .fc-daygrid-day-frame {
      min-height: 80px !important;
    }
    
    /* NUEVO: Estilos específicos para la vista semanal */
    .fc-dayGridWeek-view .fc-daygrid-day-frame {
      min-height: 100px !important;
    }
    
    /* Asegurar que el enlace "+more" sea claramente visible */
    .fc-daygrid-more-link {
      margin: 2px 0;
      padding: 2px 4px;
      background-color: #f8f9fa;
      border-radius: 3px;
      font-size: 0.75em;
      color: #0d6efd;
      text-align: center;
      display: block;
    }
    
    /* Mejorar la visualización en dispositivos móviles */
    @media (max-width: 768px) {
      .fc-event-compact {
        font-size: 0.7em !important;
      }
    }
  `;
  document.head.appendChild(estilos);

  // 7. Refrescar los eventos para aplicar todos los cambios
  calendario.refetchEvents();
  
  console.log("Visualización del calendario mejorada aplicada correctamente");
}

/**
 * Obtiene el color según la especialidad médica
 * @param {string} especialidad Nombre de la especialidad
 * @returns {string} Código de color en formato hexadecimal
 */
function obtenerColorEspecialidad(especialidad) {
  // Mapa de especialidades a colores
  const coloresEspecialidad = {
    'Medicina General': '#0d6efd', // Azul primario
    'Cardiología': '#dc3545',      // Rojo 
    'Neurología': '#fd7e14',       // Naranja
    'Endocrinología': '#198754',   // Verde
    'Pediatría': '#6f42c1',        // Púrpura
    'Ginecología': '#e83e8c',      // Rosa
    'Dermatología': '#20c997',     // Verde azulado
    'Psiquiatría': '#ffc107',      // Amarillo
    'Psicología': '#0dcaf0',       // Azul info
    'Traumatología': '#6c757d',    // Gris
    'Oftalmología': '#198754',     // Verde
    'Odontología': '#6610f2'       // Índigo
  };

  // Color por defecto si no se encuentra la especialidad
  return coloresEspecialidad[especialidad] || '#0d6efd';
}
/**
 * Muestra un modal con todas las citas de un día específico
 * @param {Date} fecha Fecha a consultar
 * @param {string} idDoctor ID del doctor (opcional)
 * @param {string} idEspecialidad ID de la especialidad (opcional)
 */
async function mostrarCitasPorDiaDoctor(fecha, idDoctor, idEspecialidad) {
  try {
    // CORREGIDO: Asegurar que fecha sea un objeto Date válido
    if (!(fecha instanceof Date) || isNaN(fecha.getTime())) {
      console.error("Fecha inválida recibida en mostrarCitasPorDiaDoctor:", fecha);
      fecha = new Date(); // Usar fecha actual como respaldo
    }
    
    console.log("Procesando citas para fecha:", fecha);
    console.log("Día:", fecha.getDate(), "Mes:", fecha.getMonth() + 1, "Año:", fecha.getFullYear());
    
    // CAMBIO IMPORTANTE: Usar mostrarCargandoCalendario en lugar de mostrarCargando
    mostrarCargandoCalendario('Cargando citas del día...');

    const fechaFormateada = fecha.toISOString().split("T")[0];
    let filtros = {
      fecha: fechaFormateada,
    };

    // Obtener filtros actuales desde los selectores si no se proporcionaron
    if (!idDoctor) {
      const doctorSelect = document.getElementById("doctor-top");
      if (doctorSelect && doctorSelect.value && doctorSelect.value !== "Seleccione Doctor") {
        filtros.iddoctor = doctorSelect.value;
      }
    } else if (idDoctor !== "Seleccione Doctor") {
      filtros.iddoctor = idDoctor;
    }

    if (!idEspecialidad) {
      const especialidadSelect = document.getElementById("especialidad-top");
      if (especialidadSelect && especialidadSelect.value && especialidadSelect.value !== "Seleccione Especialidad") {
        filtros.idespecialidad = especialidadSelect.value;
      }
    } else if (idEspecialidad !== "Seleccione Especialidad") {
      filtros.idespecialidad = idEspecialidad;
    }

    // Construir URL para obtener citas del día
    let url = `/sistemaclinica/controllers/cita.controller.php?op=calendario&start=${fechaFormateada}&end=${fechaFormateada}`;

    if (filtros.iddoctor) {
      url += `&iddoctor=${filtros.iddoctor}`;
    }

    if (filtros.idespecialidad) {
      url += `&idespecialidad=${filtros.idespecialidad}`;
    }

    console.log("URL para obtener citas del día:", url);

    // Obtener citas
    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`Error HTTP: ${response.status}`);
    }

    const citas = await response.json();
    
    // CAMBIO IMPORTANTE: Usar ocultarCargandoCalendario
    ocultarCargandoCalendario();

    if (!Array.isArray(citas) || citas.length === 0) {
      Swal.fire({
        icon: "info",
        title: "Sin citas",
        text: "No hay citas programadas para este día con los filtros seleccionados.",
      });
      return;
    }

    // SOLUCIÓN: Usar Map para identificación de duplicados más robusta
    const citasUnicas = [];
    const citasVistas = new Map();

    citas.forEach(cita => {
      // MEJORA: Clave más específica que incluye ID + hora + doctor + paciente
      const claveUnica = `${cita.id}-${cita.start}-${cita.extendedProps?.doctor || ''}-${cita.extendedProps?.paciente || ''}`;
      
      if (!citasVistas.has(claveUnica)) {
        citasVistas.set(claveUnica, true);
        citasUnicas.push(cita);
      } else {
        console.log("Cita duplicada eliminada:", claveUnica);
      }
    });

    console.log(`Mostrando ${citasUnicas.length} citas únicas para el ${fechaFormateada}`);
    
    // NUEVO: Ordenar citas por hora
    citasUnicas.sort((a, b) => {
      const horaA = new Date(a.start).getTime();
      const horaB = new Date(b.start).getTime();
      return horaA - horaB;
    });
    
    // IMPORTANTE: Pasar LA MISMA FECHA que usamos para la petición al modal
    // para mantener coherencia entre título y contenido
    const fechaModal = new Date(fechaFormateada);
    console.log("Fecha que se pasará al modal:", fechaModal);
    
    // Mostrar modal mejorado con las citas
    mostrarModalMejoradoCitas(citasUnicas, fechaModal);
  } catch (error) {
    // CAMBIO IMPORTANTE: Usar ocultarCargandoCalendario
    ocultarCargandoCalendario();
    console.error("Error en mostrarCitasPorDiaDoctor:", error);

    Swal.fire({
      icon: "error",
      title: "Error",
      text: "No se pudieron cargar las citas del día. Inténtelo nuevamente.",
    });
  }
}

/**
 * Muestra un modal mejorado con lista de citas para un día específico
 * @param {Array} citas Lista de citas a mostrar
 * @param {Date} fecha Fecha de las citas
 */
function mostrarModalMejoradoCitas(citas, fecha) {
  try {
    // DEBUGGING MEJORADO: Log de todas las citas recibidas
    console.log("=== DEBUGGING MODAL CITAS ===");
    console.log("Total de citas recibidas:", citas.length);
    citas.forEach((cita, index) => {
      console.log(`Cita ${index + 1} para modal:`, {
        id: cita.id,
        doctor: cita.extendedProps?.doctor,
        paciente: cita.extendedProps?.paciente,
        especialidad: cita.extendedProps?.especialidad,
        hora: cita.start,
        iddoctor: cita.extendedProps?.iddoctor,
        idconsulta: cita.extendedProps?.idconsulta
      });
    });
    console.log("=== FIN DEBUGGING ===");
    
    // CORREGIDO: Asegurar que fecha sea un objeto Date válido
    if (!(fecha instanceof Date) || isNaN(fecha.getTime())) {
      console.error("Fecha inválida recibida en mostrarModalMejoradoCitas:", fecha);
      fecha = new Date(); // Usar fecha actual como respaldo
    }
    
    console.log("Procesando citas para fecha:", fecha);
    console.log("Día:", fecha.getDate(), "Mes:", fecha.getMonth() + 1, "Año:", fecha.getFullYear());
    
    // Formatear la fecha para mostrar
    const fechaFormateada = formatearFecha(fecha);
    const diasSemana = [
      "Domingo",
      "Lunes",
      "Martes",
      "Miércoles",
      "Jueves",
      "Viernes",
      "Sábado",
    ];
    const diaSemana = diasSemana[fecha.getDay()];

    // IMPORTANTE: Verificar desde dónde viene la primera cita para asegurar coherencia
    let fechaCitas = fecha;
    if (citas && citas.length > 0 && citas[0].start) {
      // Si tenemos citas, usar la fecha de la primera cita para mayor precisión
      fechaCitas = new Date(citas[0].start);
      console.log("Fecha de primera cita:", fechaCitas);
    }

    // Agrupar citas por estado
    const citasPorEstado = {
      PROGRAMADA: [],
      REALIZADA: [],
      CANCELADA: [],
      "NO ASISTIO": [],
    };

    citas.forEach((cita) => {
      const estado = cita.extendedProps.estado || "PROGRAMADA";
      if (!citasPorEstado[estado]) {
        citasPorEstado[estado] = [];
      }
      citasPorEstado[estado].push(cita);
    });

    // Contadores por estado
    const contadores = {
      PROGRAMADA: citasPorEstado["PROGRAMADA"].length,
      REALIZADA: citasPorEstado["REALIZADA"].length,
      CANCELADA: citasPorEstado["CANCELADA"].length,
      "NO ASISTIO": citasPorEstado["NO ASISTIO"].length,
      TOTAL: citas.length,
    };

    // NUEVO: Generar listado de citas mejorado con formato alineado al solicitado
    let citasHTML = "";

    // Función para generar tarjetas de cita con el formato solicitado - CORREGIDA
    const generarTarjetasCitas = (estado) => {
      let html = "";
      
      if (citasPorEstado[estado] && citasPorEstado[estado].length > 0) {
        // Ordenar por hora
        citasPorEstado[estado].sort((a, b) => {
          return new Date(a.start).getTime() - new Date(b.start).getTime();
        });
        
        citasPorEstado[estado].forEach((cita) => {
          // DEBUGGING: Log de cada cita que se va a mostrar
          console.log(`Generando tarjeta para cita ${cita.id}:`, {
            doctor: cita.extendedProps?.doctor,
            paciente: cita.extendedProps?.paciente,
            especialidad: cita.extendedProps?.especialidad,
            iddoctor: cita.extendedProps?.iddoctor,
            idconsulta: cita.extendedProps?.idconsulta
          });
          
          // Formatear hora en el formato solicitado (HH:MMam - HH:MMam)
          const horaInicio = new Date(cita.start);
          let horaFin;
          
          if (cita.end) {
            horaFin = new Date(cita.end);
          } else {
            // Si no hay hora de fin, calcular 30 minutos después del inicio (predeterminado)
            horaFin = new Date(horaInicio);
            horaFin.setMinutes(horaFin.getMinutes() + 30);
          }
          
          // Formatear horas como HH:MMam
          const horaInicioFormateada = horaInicio.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
          }).toLowerCase();
          
          const horaFinFormateada = horaFin.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
          }).toLowerCase();
          
          // Obtener datos extendidos - CORREGIDO
          const doctor = cita.extendedProps.doctor || 'Doctor no asignado';
          const especialidad = cita.extendedProps.especialidad || 'Especialidad no especificada';
          const paciente = cita.extendedProps.paciente || 'Paciente no identificado';
          
          // DEBUGGING: Verificar que los datos sean correctos
          console.log(`Datos finales para mostrar en cita ${cita.id}:`, {
            doctor: doctor,
            especialidad: especialidad,
            paciente: paciente
          });
          
          // MODIFICACIÓN: Obtener color según especialidad en lugar del estado
          const colorEspecialidad = obtenerColorEspecialidad(especialidad);
          
          // Formato exactamente como solicitado: "8:20am - 8:40am | Medicina General | Doc. Dyer Edu"
          const formatoTitulo = `${horaInicioFormateada} - ${horaFinFormateada} | ${especialidad} | Doc. ${doctor.split(',')[0]}`;
          
          // Formatear el estado para las clases CSS
          const estadoCSS = estado.toLowerCase().replace(' ', '-');
    
          html += `
            <div class="card mb-3 border-0 shadow-sm">
              <div class="card-body p-3" style="border-left: 4px solid ${colorEspecialidad}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="card-title fw-bold mb-0">${formatoTitulo}</h6>
                  <span class="badge bg-${estadoCSS === 'programada' ? 'primary' : 
                        estadoCSS === 'realizada' ? 'success' : 
                        estadoCSS === 'cancelada' ? 'danger' : 'warning'}">
                    ${estado}
                  </span>
                </div>
                <div>
                  <p class="card-text mb-1">
                    <i class="fas fa-user me-2"></i>
                    <strong>Paciente:</strong> ${paciente}
                  </p>
                  
                  <div class="d-flex justify-content-end mt-2">
                    <button class="btn btn-sm btn-outline-primary" 
                            onclick="mostrarDetallesCita(${JSON.stringify(cita).replace(/"/g, '&quot;')})">
                      <i class="fas fa-info-circle me-1"></i> Detalles
                    </button>
                  </div>
                </div>
              </div>
            </div>
          `;
        });
      }
      
      return html;
    };

    // Generar HTML para cada sección de estado
    const estadosOrdenados = [
      "PROGRAMADA",
      "REALIZADA",
      "CANCELADA",
      "NO ASISTIO",
    ];

    estadosOrdenados.forEach((estado) => {
      if (citasPorEstado[estado] && citasPorEstado[estado].length > 0) {
        citasHTML += `
          <div class="mb-4">
            <h6 class="d-flex align-items-center mb-3">
              <span class="badge bg-${estado === 'PROGRAMADA' ? 'primary' : 
                    estado === 'REALIZADA' ? 'success' : 
                    estado === 'CANCELADA' ? 'danger' : 'warning'} me-2">
                ${citasPorEstado[estado].length}
              </span>
              ${getEstadoIcon(estado)} ${estado}
            </h6>
            <div class="citas-container">
              ${generarTarjetasCitas(estado)}
            </div>
          </div>
        `;
      }
    });

    // Si no hay citas para algún estado, mostrar mensaje
    if (citasHTML === "") {
      citasHTML = `
        <div class="alert alert-info">
          No hay citas para este día con los filtros seleccionados.
        </div>
      `;
    }

    // CORRECCIÓN EN EL TÍTULO: Usar fechaCitas para asegurar coherencia
    const contenidoModal = `
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="fas fa-calendar-day me-2"></i>
          Citas del ${diasSemana[fechaCitas.getDay()]} ${formatearFecha(fechaCitas)}
        </h5>
        <button type="button" class="btn-close btn-close-white" onclick="cerrarModalCompletamente('modalDetallesCita')" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Contador de citas por estado -->
        <div class="card mb-4 border-0 shadow-sm">
          <div class="card-body p-3">
            <div class="row text-center">
              <div class="col">
                <div class="h4 mb-0 text-dark">${contadores.TOTAL}</div>
                <small class="text-muted">Total</small>
              </div>
              <div class="col">
                <div class="h4 mb-0 text-primary">${contadores.PROGRAMADA}</div>
                <small class="text-muted">Programadas</small>
              </div>
              <div class="col">
                <div class="h4 mb-0 text-success">${contadores.REALIZADA}</div>
                <small class="text-muted">Realizadas</small>
              </div>
              <div class="col">
                <div class="h4 mb-0 text-danger">${contadores.CANCELADA}</div>
                <small class="text-muted">Canceladas</small>
              </div>
              <div class="col">
                <div class="h4 mb-0 text-warning">${contadores["NO ASISTIO"]}</div>
                <small class="text-muted">No Asistió</small>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Citas agrupadas por estado -->
        <div class="citas-container">
          ${citasHTML}
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="cerrarModalCompletamente('modalDetallesCita')">Cerrar</button>
      </div>
    `;

    // Verificar si existe un modal abierto y cerrarlo primero
    const modalActual = bootstrap.Modal.getInstance(document.getElementById("modalDetallesCita"));
    if (modalActual) {
      modalActual.dispose();
    }

    // Eliminar cualquier backdrop existente antes de abrir el nuevo modal
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
    document.body.classList.remove('modal-open');

    // Actualizar el contenido del modal
    document.getElementById("contenidoModalDetallesCita").innerHTML = contenidoModal;

    // Configurar y abrir el modal con configuración explícita
    const modalElement = document.getElementById("modalDetallesCita");
    modalElement.setAttribute('data-bs-backdrop', 'true');
    modalElement.setAttribute('data-bs-keyboard', 'true');
    
    const modalDetalles = new bootstrap.Modal(modalElement, {
      backdrop: true,
      keyboard: true,
      focus: true
    });
    
    // Guardar referencia al modal actual
    window.modalDetallesCitaActual = modalDetalles;
    
    // Mostrar el modal
    modalDetalles.show();
  } catch (error) {
    console.error("Error en mostrarModalMejoradoCitas CORREGIDA:", error);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "No se pudieron cargar las citas del día. Inténtelo nuevamente.",
    });
  }
}
/**
 * Obtiene el ícono HTML para un estado de cita
 * @param {string} estado Estado de la cita
 * @return {string} HTML del ícono correspondiente
 */
function getEstadoIcon(estado) {
  switch (estado) {
    case "PROGRAMADA":
      return '<i class="fas fa-clock"></i>';
    case "REALIZADA":
      return '<i class="fas fa-check-circle"></i>';
    case "CANCELADA":
      return '<i class="fas fa-ban"></i>';
    case "NO ASISTIO":
      return '<i class="fas fa-user-times"></i>';
    default:
      return '<i class="fas fa-calendar"></i>';
  }
}
/**
 * Muestra un modal con la lista de citas para un día específico
 * @param {Array} citas Lista de citas a mostrar
 * @param {Date} fecha Fecha de las citas
 */
function mostrarModalCitasDia(citas, fecha) {
  // NUEVO: Eliminar citas duplicadas
  const citasUnicas = [];
  const citasVistas = new Map(); // Usamos un Map para mejor rendimiento

  citas.forEach(cita => {
    // Crear una clave única considerando múltiples propiedades
    const claveUnica = `${cita.start}-${cita.extendedProps?.paciente}-${cita.extendedProps?.doctor}`;

    if (!citasVistas.has(claveUnica)) {
      citasVistas.set(claveUnica, true);
      citasUnicas.push(cita);
    }
  });

  // Formatear la fecha para mostrar
  const fechaFormateada = formatearFecha(fecha);
  const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
  const nombreDia = diasSemana[fecha.getDay()];
  
  // Agrupar citas por estado
  const citasPorEstado = {
    'PROGRAMADA': [],
    'REALIZADA': [],
    'CANCELADA': [],
    'NO ASISTIO': []
  };
  
  citasUnicas.forEach(cita => {
    const estado = cita.extendedProps.estado || 'PROGRAMADA';
    if (!citasPorEstado[estado]) {
      citasPorEstado[estado] = [];
    }
    citasPorEstado[estado].push(cita);
  });
  
  // Contadores por estado
  const contadores = {
    'PROGRAMADA': citasPorEstado['PROGRAMADA'].length,
    'REALIZADA': citasPorEstado['REALIZADA'].length,
    'CANCELADA': citasPorEstado['CANCELADA'].length,
    'NO ASISTIO': citasPorEstado['NO ASISTIO'].length,
    'TOTAL': citasUnicas.length
  };
  
  // Generar HTML para cada sección de estado
  let contenidoHTML = '';
  
  // Función para generar las tarjetas de citas
  function generarTarjetasCitas(citasEstado, estado) {
    // Normalizar el estado para las clases CSS
    const estadoCSS = estado.toLowerCase().replace(' ', '-');
    
    let html = '';
    
    if (citasEstado.length === 0) {
      return '<p class="text-muted">No hay citas con este estado.</p>';
    }
    
    // Ordenar citas por hora
    citasEstado.sort((a, b) => {
      const horaA = a.start ? new Date(a.start).getTime() : 0;
      const horaB = b.start ? new Date(b.start).getTime() : 0;
      return horaA - horaB;
    });
    
    // Crear tarjetas para cada cita
    citasEstado.forEach(cita => {
      const horaInicio = cita.start ? 
        new Date(cita.start).toLocaleTimeString('es-ES', {
          hour: '2-digit',
          minute: '2-digit',
          hour12: true
        }) : '';
      
      html += `
        <div class="card cita-card cita-${estadoCSS} mb-2 shadow-sm">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="card-title m-0 d-flex align-items-center">
                <i class="far fa-clock me-2"></i> ${horaInicio}
              </h6>
              <span class="badge-estado badge-${estadoCSS}">
                ${getIconoEstado(estado)} ${estado}
              </span>
            </div>
            <div class="row">
              <div class="col-md-9">
                <p class="card-text mb-1">
                  <i class="fas fa-user me-2"></i>
                  <strong>${cita.extendedProps.paciente}</strong>
                </p>
                <p class="card-text mb-1">
                  <i class="fas fa-user-md me-2"></i>
                  ${cita.extendedProps.doctor}
                </p>
                <p class="card-text mb-0">
                  <i class="fas fa-stethoscope me-2"></i>
                  ${cita.extendedProps.especialidad}
                </p>
              </div>
              <div class="col-md-3 d-flex align-items-center justify-content-end">
                <button class="btn btn-sm btn-outline-primary" 
                        onclick="verDetalleCita(${JSON.stringify(cita).replace(/"/g, '&quot;')})">
                  <i class="fas fa-info-circle me-1"></i> Ver
                </button>
              </div>
            </div>
          </div>
        </div>
      `;
    });
    
    return html;
  }
  
  // Generar secciones para cada estado (solo si hay citas)
  const estadosOrden = ['PROGRAMADA', 'REALIZADA', 'CANCELADA', 'NO ASISTIO'];
  
  estadosOrden.forEach(estado => {
    if (citasPorEstado[estado].length > 0) {
      const estadoCSS = estado.toLowerCase().replace(' ', '-');
      
      contenidoHTML += `
        <div class="citas-estado-seccion">
          <h6 class="citas-estado-titulo d-flex align-items-center">
            <span class="badge-estado badge-${estadoCSS} me-2">
              ${citasPorEstado[estado].length}
            </span>
            ${getIconoEstado(estado)} ${estado}
          </h6>
          ${generarTarjetasCitas(citasPorEstado[estado], estado)}
        </div>
      `;
    }
  });
  
  // Si no hay citas, mostrar mensaje
  if (citasUnicas.length === 0) {
    contenidoHTML = `
      <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        No hay citas programadas para este día con los filtros seleccionados.
      </div>
    `;
  }
  
  // Crear contenido completo del modal
  const modalContent = `
    <div class="modal-header bg-primary text-white">
      <h5 class="modal-title">
        <i class="fas fa-calendar-day me-2"></i>
        Citas del ${nombreDia} ${fechaFormateada}
      </h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
      <!-- Resumen de citas -->
      <div class="card mb-3">
        <div class="card-body p-3">
          <div class="row text-center">
            <div class="col">
              <div class="h4 mb-0">${contadores.TOTAL}</div>
              <small class="text-muted">Total</small>
            </div>
            <div class="col">
              <div class="h4 mb-0 text-primary">${contadores.PROGRAMADA}</div>
              <small class="text-muted">Programadas</small>
            </div>
            <div class="col">
              <div class="h4 mb-0 text-success">${contadores.REALIZADA}</div>
              <small class="text-muted">Realizadas</small>
            </div>
            <div class="col">
              <div class="h4 mb-0 text-danger">${contadores.CANCELADA}</div>
              <small class="text-muted">Canceladas</small>
            </div>
            <div class="col">
              <div class="h4 mb-0 text-warning">${contadores['NO ASISTIO']}</div>
              <small class="text-muted">No Asistió</small>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Lista de citas agrupadas por estado -->
      ${contenidoHTML}
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
    </div>
  `;
  
  // Actualizar y mostrar el modal
  document.getElementById('contenidoModalDetallesCita').innerHTML = modalContent;
  const modalDetalles = new bootstrap.Modal(document.getElementById('modalDetallesCita'));
  modalDetalles.show();
}
/**
 * Muestra el detalle de una cita específica
 * @param {Object} cita Objeto con los datos de la cita
 */
function verDetalleCita(cita) {
  // Formatear hora
  const horaInicio = cita.start
    ? new Date(cita.start).toLocaleTimeString("es-ES", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
      })
    : "";

  const horaFin = cita.end
    ? new Date(cita.end).toLocaleTimeString("es-ES", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
      })
    : "";

  // Formatear fecha
  const fecha = cita.start ? formatearFecha(new Date(cita.start)) : "";

  // Determinar color según estado
  const estado = cita.extendedProps.estado || "PROGRAMADA";
  const estadoCSS = estado.toLowerCase().replace(" ", "-");

  // Preparar modal con detalles
  Swal.fire({
    title: "Detalles de la Cita",
    html: `
              <div class="card cita-${estadoCSS} border-top-0 border-end-0 border-bottom-0" style="border-left-width: 4px;">
                  <div class="card-body p-0">
                      <div class="text-center mb-3">
                          <span class="badge-estado badge-${estadoCSS}">
                              ${getIconoEstado(estado)} ${estado}
                          </span>
                      </div>
                      <table class="table table-borderless table-sm">
                          <tr>
                              <th><i class="fas fa-calendar-alt me-2"></i>Fecha:</th>
                              <td>${fecha}</td>
                          </tr>
                          <tr>
                              <th><i class="far fa-clock me-2"></i>Hora:</th>
                              <td>${horaInicio}${
      horaFin ? ` - ${horaFin}` : ""
    }</td>
                          </tr>
                          <tr>
                              <th><i class="fas fa-user me-2"></i>Paciente:</th>
                              <td>${cita.extendedProps.paciente}</td>
                          </tr>
                          <tr>
                              <th><i class="fas fa-user-md me-2"></i>Doctor:</th>
                              <td>${cita.extendedProps.doctor}</td>
                          </tr>
                          <tr>
                              <th><i class="fas fa-stethoscope me-2"></i>Especialidad:</th>
                              <td>${cita.extendedProps.especialidad}</td>
                          </tr>
                      </table>
                  </div>
              </div>
          `,
    confirmButtonText: "Cerrar",
    width: "32em",
  });
}

/**
 * Devuelve el icono HTML correspondiente a un estado de cita
 * @param {string} estado El estado de la cita
 * @returns {string} Código HTML del icono
 */
function getIconoEstado(estado) {
  switch (estado) {
    case "PROGRAMADA":
      return '<i class="far fa-clock me-1"></i>';
    case "REALIZADA":
      return '<i class="fas fa-check-circle me-1"></i>';
    case "CANCELADA":
      return '<i class="fas fa-ban me-1"></i>';
    case "NO ASISTIO":
      return '<i class="fas fa-user-times me-1"></i>';
    default:
      return '<i class="fas fa-calendar-check me-1"></i>';
  }
}
