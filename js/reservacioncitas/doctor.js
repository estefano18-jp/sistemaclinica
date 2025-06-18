/**
 * sistemaclinica/js/reservacioncitas/doctor.js
 */

/**
 * Gestión de doctores
 */

// Cargar doctores por especialidad
async function cargarDoctoresPorEspecialidad(idEspecialidad, selectId) {
  try {
    const select = document.getElementById(selectId);
    if (!select) return;
    
    // Mostrar carga
    mostrarCargando();
    
    // Limpiar opciones actuales
    select.innerHTML = '<option selected disabled>Seleccione Doctor</option>';
    
    // Determinar la URL del endpoint según si queremos todos los doctores o por especialidad
    let url;
    
    // Si idEspecialidad es "0", "todos", null, undefined o cadena vacía = cargar todos
    if (!idEspecialidad || idEspecialidad === "0" || idEspecialidad === "todos") {
      // URL para obtener todos los doctores
      url = "../../../controllers/doctor.controller.php?op=listar";
      console.log("Cargando TODOS los doctores disponibles");
    } else {
      // URL para obtener doctores por especialidad
      url = `../../../controllers/doctor.controller.php?op=listar_por_especialidad&idespecialidad=${idEspecialidad}`;
      console.log(`Cargando doctores para especialidad ID: ${idEspecialidad}`);
    }
    
    // Cargar doctores desde el servidor - usando ruta absoluta
    const data = await fetchData(url);
    
    if (data.status && data.data) {
      // Filtrar solo doctores activos
      const doctoresActivos = data.data.filter(doctor => doctor.estado === 'ACTIVO');
      
      // MODIFICADO: Agregar opción "Todos los doctores" SOLO para filtros
      if (selectId.includes("-top") && (!idEspecialidad || idEspecialidad === "0" || idEspecialidad === "todos")) {
        const optionTodos = document.createElement('option');
        optionTodos.value = "0";
        optionTodos.textContent = "Todos los doctores";
        select.appendChild(optionTodos);
      }
      
      // Agregar opciones al select
      doctoresActivos.forEach(doctor => {
        const option = document.createElement('option');
        option.value = doctor.idcolaborador || doctor.id_colaborador || doctor.id;
        option.textContent = doctor.nombre_completo || `${doctor.apellidos || ''}, ${doctor.nombres || ''}`;
        option.dataset.idespecialidad = doctor.idespecialidad || doctor.id_especialidad || "";
        select.appendChild(option);
      });
      
      // Si no hay doctores disponibles
      if (doctoresActivos.length === 0) {
        const option = document.createElement('option');
        option.disabled = true;
        option.textContent = 'No hay doctores disponibles';
        select.appendChild(option);
      }

      console.log(`Doctores cargados: ${doctoresActivos.length}`);
    } else {
      console.error('Error al cargar doctores:', data.mensaje || 'No se recibieron datos correctos');
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudieron cargar los doctores.'
      });
    }
  } catch (error) {
    console.error('Error en cargarDoctoresPorEspecialidad:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error de conexión',
      text: 'No se pudo conectar con el servidor para cargar los doctores.'
    });
  } finally {
    ocultarCargando();
  }
}

// Obtener horarios de atención de un doctor
async function obtenerHorariosDoctor(idDoctor) {
    try {
        const data = await fetchData(`../../../controllers/horario.controller.php?op=horarios_doctor&idcolaborador=${idDoctor}`);
        
        if (data.status && data.data) {
            return data.data;
        }
        
        return {};
    } catch (error) {
        console.error('Error en obtenerHorariosDoctor:', error);
        return {};
    }
}

// Mostrar días de atención de un doctor
async function mostrarDiasAtencionDoctor(idDoctor) {
    try {
        const ejemploDias = document.querySelector('.example-days');
        if (!ejemploDias) return;
        
        // Mensaje de carga
        ejemploDias.innerHTML = '<p class="mb-1"><strong>Días de atención:</strong></p><p class="mb-0 ps-3">Cargando horarios...</p>';
        
        // Obtener horarios
        mostrarCargando();
        const horarios = await obtenerHorariosDoctor(idDoctor);
        ocultarCargando();
        
        if (Object.keys(horarios).length === 0) {
            ejemploDias.innerHTML = '<p class="mb-1"><strong>Días de atención:</strong></p><p class="mb-0 ps-3">No hay horarios definidos para este doctor</p>';
            return;
        }
        
        // Crear contenido HTML con los horarios
        let contenidoHTML = '<p class="mb-1"><strong>Días de atención:</strong></p>';
        
        // Orden de los días de la semana
        const ordenDias = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'];
        
        // Ordenar los días según el orden establecido
        const diasOrdenados = ordenDias.filter(dia => horarios[dia]);
        
        // Generar el contenido HTML con los días ordenados
        diasOrdenados.forEach(dia => {
            const horariosDelDia = horarios[dia];
            const horariosFormateados = horariosDelDia.map(h => {
                const inicio = formatearHora(h.inicio);
                const fin = formatearHora(h.fin);
                return `${inicio} - ${fin}`;
            }).join(', ');
            
            contenidoHTML += `<p class="mb-0 ps-3"><strong>${dia}:</strong> ${horariosFormateados}</p>`;
        });
        
        ejemploDias.innerHTML = contenidoHTML;
    } catch (error) {
        console.error('Error en mostrarDiasAtencionDoctor:', error);
        const ejemploDias = document.querySelector('.example-days');
        if (ejemploDias) {
            ejemploDias.innerHTML = '<p class="mb-1"><strong>Días de atención:</strong></p><p class="mb-0 ps-3">Error al cargar horarios</p>';
        }
        ocultarCargando();
    }
}