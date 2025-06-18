/**
 * sistemaclinica/js/reservacioncitas/main.js
 */

/**
 * Script principal para el sistema de reservación de citas
 */

document.addEventListener("DOMContentLoaded", function () {
  console.log("Iniciando sistema de reservación de citas...");

  // NUEVO: Asegurar que el tipo de documento del paciente sea DNI por defecto
  const tipoDocumento = document.getElementById("tipo-documento");
  if (tipoDocumento) {
    // Buscar y seleccionar la opción "DNI"
    for (let i = 0; i < tipoDocumento.options.length; i++) {
      if (tipoDocumento.options[i].value === "DNI") {
        tipoDocumento.selectedIndex = i;
        console.log("Tipo de documento establecido a DNI al iniciar");
        break;
      }
    }
  }

  // Inicializar componentes (el calendario se inicializa en el HTML)

  // Cargar especialidades
  console.log("Cargando especialidades...");
  cargarEspecialidades("especialidad");
  cargarEspecialidades("especialidad-top");

  // Configurar eventos
  configurarEventos();

  // NUEVO: Aplicar restricciones al documento del paciente
  if (
    document.getElementById("tipo-documento") &&
    document.getElementById("numero-documento")
  ) {
    aplicarRestriccionesDocumento("numero-documento", "tipo-documento");
  }

  // NUEVO: Mejorar inicialización del calendario con configuración óptima
  if (window.calendario) {
    inicializarCalendarioMejorado(window.calendario);
  } else {
    // Si el calendario aún no está inicializado, esperar y luego aplicar
    const waitForCalendar = setInterval(() => {
      if (window.calendario) {
        clearInterval(waitForCalendar);
        inicializarCalendarioMejorado(window.calendario);
        console.log("Calendario inicializado con configuración óptima");
      }
    }, 100);
  }
});
/**
 * Inicializa manejadores de eventos para resolver problemas de modales
 */
function inicializarManejoModalesAvanzado() {
  console.log("Inicializando manejo avanzado de modales");

  // Agregamos un manejador a nivel de documento para asegurar limpieza
  document.addEventListener("hidden.bs.modal", function (event) {
    console.log("Evento hidden.bs.modal detectado");

    // Verificar si hay modales activos
    const modalesActivos = document.querySelectorAll(".modal.show");
    if (modalesActivos.length === 0) {
      console.log("No hay modales activos, limpiando...");

      // Remover todos los backdrops
      document.querySelectorAll(".modal-backdrop").forEach((backdrop) => {
        console.log("Removiendo backdrop");
        backdrop.remove();
      });

      // Limpiar clases del body
      document.body.classList.remove("modal-open");
      document.body.style.overflow = "";
      document.body.style.paddingRight = "";
    }
  });

  // Función para observar cambios en la DOM
  const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (
        mutation.type === "childList" &&
        mutation.addedNodes.length > 0 &&
        document.querySelectorAll(".modal-backdrop").length > 1
      ) {
        // Si hay múltiples backdrops, mantener solo uno
        const backdrops = document.querySelectorAll(".modal-backdrop");
        for (let i = 1; i < backdrops.length; i++) {
          backdrops[i].remove();
        }
      }
    });
  });

  // Iniciar observación
  observer.observe(document.body, {
    childList: true,
    subtree: true,
  });
}

// Ejecutar al final del DOMContentLoaded
document.addEventListener("DOMContentLoaded", function () {
  // Asegurarnos que se ejecuta después de todo lo demás
  setTimeout(inicializarManejoModalesAvanzado, 500);
});
function inicializarCalendarioMejorado(calendario) {
    // Guardar los filtros actuales en una variable global
    if (!window.filtrosCalendario) {
        window.filtrosCalendario = {};
    }

    // Configurar opciones mejoradas del calendario
    calendario.setOption("height", "auto");
    calendario.setOption("contentHeight", "auto");

    // Mejorar aspecto visual
    calendario.setOption("aspectRatio", 1.5);

    // NUEVO: Mostrar solo las semanas necesarias para el mes actual
    calendario.setOption("fixedWeekCount", false);

    // Permitir mostrar más eventos
    calendario.setOption("dayMaxEvents", 2);

    // NUEVO: Configurar vistas específicamente
    calendario.setOption("views", {
        dayGridMonth: {
            dayMaxEvents: 2,
            fixedWeekCount: false, // Asegurar que se aplique en la vista mensual
        },
        dayGridWeek: {
            dayMaxEvents: 2,
        },
        timeGridDay: {
            dayMaxEvents: 5, // Más eventos en vista diaria
        },
    });

    // SOLUCIÓN: Limpiar el calendario antes de la carga inicial
    if (calendario.getEvents().length > 0) {
        console.log("Limpiando eventos existentes en la inicialización...");
        calendario.removeAllEvents();
    }

    // CAMBIO PRINCIPAL: Una sola carga inicial optimizada
    recargarSoloCalendario();

    // MODIFICADO: Configurar eventos para cuando cambia la vista - MANTENER FILTROS
    calendario.on("datesSet", function (info) {
        console.log("Cambio de vista de calendario a:", info.view.title);
        console.log("Aplicando filtros guardados:", window.filtrosCalendario);

        // IMPORTANTE: Si hay filtros guardados, actualizarlos con fechas de la nueva vista
        if (window.filtrosCalendario) {
            // Actualizar solo las fechas para la nueva vista manteniendo los otros filtros
            const filtrosActualizados = { ...window.filtrosCalendario };

            // No sobrescribir fechaInicio/fechaFin si fueron explícitamente seleccionadas
            if (!window.fechaManualmenteSeleccionada) {
                // Fechas de la vista actual
                if (info.view && info.view.activeStart && info.view.activeEnd) {
                    filtrosActualizados.fechaInicio = info.view.activeStart
                        .toISOString()
                        .substring(0, 10);
                    filtrosActualizados.fechaFin = info.view.activeEnd
                        .toISOString()
                        .substring(0, 10);
                }
            }

            // Guardar los filtros actualizados
            window.filtrosCalendario = filtrosActualizados;
            
            // CAMBIO PRINCIPAL: Una sola llamada optimizada
            recargarSoloCalendario(true);
        } else {
            // Si no hay filtros, cargar todas las citas para la vista actual
            recargarSoloCalendario(false);
        }
    });

    // Configurar eventos para click en el día
    calendario.on("dateClick", function (info) {
        // Ahora funciona tanto para vista mensual como semanal
        if (
            calendario.view.type === "dayGridMonth" ||
            calendario.view.type === "dayGridWeek"
        ) {
            console.log("Click en día:", info.dateStr);

            // Mostrar citas de ese día en un modal
            mostrarCitasPorDiaDoctor(new Date(info.dateStr));
        }
    });
}

// Configurar eventos para filtros superiores
function configurarEventos() {
  // Configurar filtros superiores
  configurarFiltrosSuperiores();

  // Configurar formulario de reserva
  configurarFormularioReserva();

  // Configurar modal de horarios
  configurarModalHorarios();

  // Configurar botón de proceder con el pago
  configurarProcederPago();

  // NO configuramos búsqueda de paciente aquí - se maneja en paciente.js
}
/**
 * Función para asegurar que el botón de pago esté visible pero deshabilitado
 */
function asegurarVisibilidadBotonPago() {
    const btnPago = document.getElementById("btn-proceder-pago");
    if (btnPago) {
        console.log("Asegurando visibilidad del botón de pago");
        btnPago.classList.remove("d-none");
        btnPago.disabled = true;
        btnPago.classList.remove("btn-success");
        btnPago.classList.add("btn-secondary");
    }
}
function configurarFiltrosSuperiores() {
  const especialidadTop = document.getElementById("especialidad-top");
  const doctorTop = document.getElementById("doctor-top");
  const fechaTop = document.getElementById("fecha-top");

  if (especialidadTop) {
    especialidadTop.addEventListener("change", function () {
      const idEspecialidad = this.value;

      // Cargar doctores por especialidad
      cargarDoctoresPorEspecialidad(idEspecialidad, "doctor-top");

      // Actualizar calendario con filtro de especialidad INMEDIATAMENTE
      actualizarFiltrosCalendario();
    });
  }

  if (doctorTop) {
    doctorTop.addEventListener("change", function () {
      // Actualizar calendario con filtro de doctor INMEDIATAMENTE
      actualizarFiltrosCalendario();
    });
  }

  if (fechaTop) {
    fechaTop.addEventListener("change", function () {
      // Actualizar calendario con filtro de fecha
      actualizarFiltrosCalendario();
    });
  }
}
// Actualizar filtros del calendario
function actualizarFiltrosCalendario() {
  if (!window.calendario) return;

  // ELIMINADO: mostrarCargando() global - ESTE ERA EL PROBLEMA PRINCIPAL

  const idEspecialidad =
    document.getElementById("especialidad-top")?.value || null;
  const idDoctor = document.getElementById("doctor-top")?.value || null;
  const fecha = document.getElementById("fecha-top")?.value || null;

  // Preparar filtros
  const filtros = {};

  // Si hay especialidad seleccionada (incluso "Todas")
  if (idEspecialidad) {
    // Solo añadir al filtro si no es "0" o "todos" (todas las especialidades)
    if (idEspecialidad !== "0" && idEspecialidad !== "todos") {
      filtros.idespecialidad = idEspecialidad;
    }
  }

  // Si hay doctor seleccionado (incluso "Todos")
  if (idDoctor) {
    // Solo añadir al filtro si no es "0" o "todos" (todos los doctores)
    if (idDoctor !== "0" && idDoctor !== "todos") {
      filtros.iddoctor = idDoctor;
    }
  }

  if (fecha) {
    // Si se seleccionó una fecha, mostrar solo ese día
    filtros.fechaInicio = fecha;
    filtros.fechaFin = fecha;

    // Indicar que la fecha fue seleccionada manualmente
    window.fechaManualmenteSeleccionada = true;

    // Cambiar vista del calendario a día
    window.calendario.changeView("timeGridDay", fecha);
  } else {
    // Si no hay fecha seleccionada, usar la vista actual
    window.fechaManualmenteSeleccionada = false;

    // Obtener fechas de la vista actual
    if (window.calendario.view) {
      filtros.fechaInicio = window.calendario.view.activeStart
        .toISOString()
        .substring(0, 10);
      filtros.fechaFin = window.calendario.view.activeEnd
        .toISOString()
        .substring(0, 10);
    }
  }

  // IMPORTANTE: Guardar los filtros actuales para mantenerlos al navegar
  window.filtrosCalendario = { ...filtros };

  console.log("Actualizando calendario con filtros:", filtros);
  console.log("Filtros guardados:", window.filtrosCalendario);

  // CAMBIO PRINCIPAL: Una sola llamada optimizada
  if (typeof cargarCitasCalendario === "function") {
    cargarCitasCalendario(window.calendario, filtros)
      .then(() => {
        console.log("Calendario actualizado con filtros (OPTIMIZADO)");
        // Ya no es necesario llamar a funciones adicionales
      })
      .catch((error) => {
        console.error("Error al cargar citas:", error);
      });
  } else {
    console.log("La función cargarCitasCalendario no está definida");
  }
}

// Configurar formulario de reserva
function configurarFormularioReserva() {
  const especialidad = document.getElementById("especialidad");
  const doctor = document.getElementById("doctor");
  const btnAgregarHorario = document.getElementById("btn-agregar-horario");
  const fechaReserva = document.getElementById("fecha-reserva");

  // NUEVA VALIDACIÓN DE FECHA: Impedir seleccionar fechas anteriores
  if (fechaReserva) {
      // Establecer fecha mínima como hoy
      const hoy = new Date().toISOString().split('T')[0];
      fechaReserva.setAttribute("min", hoy);
      
      // Validar al iniciar
      if (fechaReserva.value && fechaReserva.value < hoy) {
          fechaReserva.value = hoy;
      }
      
      // Validar al cambiar fecha
      fechaReserva.addEventListener("change", function() {
          const fechaSeleccionada = this.value;
          if (fechaSeleccionada < hoy) {
              Swal.fire({
                  icon: 'warning',
                  title: 'Fecha no válida',
                  text: 'No puede seleccionar una fecha anterior al día de hoy.'
              });
              this.value = hoy;
          }
          
          // NUEVO: Guardar la fecha seleccionada para uso posterior
          if (!window.datosCitaReservada) {
              window.datosCitaReservada = {};
          }
          window.datosCitaReservada.fecha = this.value;
          console.log("Fecha de reserva guardada:", window.datosCitaReservada.fecha);
          
          // Verificar estado del botón de pago
          if (typeof verificarHabilitarBotonPago === "function") {
              verificarHabilitarBotonPago();
          }
      });
  }

  // Código existente para especialidad, doctor, etc.
if (especialidad) {
      especialidad.addEventListener("change", function () {
          const idEspecialidad = this.value;
          console.log("Especialidad seleccionada:", idEspecialidad);

          // CRÍTICO: Validar si el paciente puede acceder a esta especialidad
          // Solo validar si ya hay un paciente seleccionado
          const especialidadOption = this.options[this.selectedIndex];
          const nombreEspecialidad = especialidadOption.textContent || '';
          
          // Si hay un paciente seleccionado y los datos están cargados, validar la especialidad
          if (window.datosPaciente && document.getElementById('idpaciente').value) {
              console.log("Validando especialidad con paciente ya seleccionado:", window.datosPaciente);
              
              const validacionEspecialidad = validarEspecialidadPorPaciente(nombreEspecialidad);
              
              if (!validacionEspecialidad.valido) {
                  console.log("Validación fallida. Mostrando mensaje y deshabilitando botón de pago");
                  
                  // Mostrar mensaje de restricción
                  mostrarRestriccionEspecialidad(validacionEspecialidad.mensaje);
                  
                  // CORRECCIÓN CRÍTICA: Bloquear efectivamente el botón con la nueva función
                  bloquearBotonPago(true, validacionEspecialidad.mensaje);
              } else {
                  // Si la validación es exitosa, habilitar el botón de pago
                  bloquearBotonPago(false);
              }
          }

          // Cargar doctores por especialidad
          cargarDoctoresPorEspecialidad(idEspecialidad, "doctor");

          // Limpiar campos dependientes
          document.getElementById("hora-hidden").value = "";
          document.getElementById("horario-seleccionado").value = "";
          document.querySelector(".example-days").innerHTML =
              '<p class="mb-1"><strong>Días de atención:</strong></p><p class="mb-0 ps-3">Seleccione un doctor para ver sus días de atención</p>';

          // Verificar estado del botón de pago
          if (typeof verificarHabilitarBotonPago === "function") {
              verificarHabilitarBotonPago();
          }
      });
  }

  // Código existente para doctor
  if (doctor) {
    doctor.addEventListener("change", function () {
      const idDoctor = this.value;
      console.log("Doctor seleccionado:", idDoctor);

      // Mostrar días de atención del doctor
      mostrarDiasAtencionDoctor(idDoctor);

      // Limpiar campos dependientes
      document.getElementById("hora-hidden").value = "";
      document.getElementById("horario-seleccionado").value = "";

      // Verificar estado del botón de pago
      if (typeof verificarHabilitarBotonPago === "function") {
        verificarHabilitarBotonPago();
      }
    });
  }

  // Código existente para btnAgregarHorario
  if (btnAgregarHorario) {
    btnAgregarHorario.addEventListener("click", function () {
      const idDoctor = document.getElementById("doctor").value;
      const fecha = document.getElementById("fecha-reserva").value;
      const idEspecialidad = document.getElementById("especialidad").value;

      if (!idDoctor || !fecha || !idEspecialidad) {
        Swal.fire({
          icon: "warning",
          title: "Datos incompletos",
          text: "Por favor, seleccione doctor, especialidad y fecha.",
        });
        return;
      }

      // Mostrar modal de horarios
      mostrarModalHorarios(idDoctor, fecha, idEspecialidad);
    });
  }
}

// Configurar modal de horarios
function configurarModalHorarios() {
  const btnConfirmarHorario = document.getElementById("btn-confirmar-horario");

  if (btnConfirmarHorario) {
    btnConfirmarHorario.addEventListener("click", function () {
      confirmarSeleccionHorario();

      // Verificar estado del botón de pago después de seleccionar horario
      if (typeof verificarHabilitarBotonPago === "function") {
        setTimeout(verificarHabilitarBotonPago, 500); // Pequeño retraso para asegurar que se actualicen los valores
      }
    });
  }
}
/**
 * Recarga solo el calendario sin recargar toda la página
 * @param {boolean} mantenerFiltros - Si se deben mantener los filtros actuales
 */
function recargarSoloCalendario(mantenerFiltros = true) {
    if (window.calendario) {
        console.log("Recargando solo el calendario (OPTIMIZADO)...");
        
        // Aplicar filtros existentes si los hay y si se deben mantener
        let filtros = {};
        if (mantenerFiltros && window.filtrosCalendario) {
            filtros = { ...window.filtrosCalendario };
            console.log("Aplicando filtros guardados:", filtros);
        }
        
        // SOLUCIÓN: Asegurarnos de que el calendario está limpio antes de cargar nuevos datos
        if (window.calendario.getEvents().length > 0) {
            console.log("Limpiando eventos existentes antes de recargar...");
            window.calendario.removeAllEvents();
        }
        
        // CAMBIO PRINCIPAL: Una sola llamada que incluye carga + visualización
        // La función cargarCitasCalendario ya usa mostrarCargandoCalendario internamente
        cargarCitasCalendario(window.calendario, filtros)
            .then(() => {
                console.log("Calendario recargado exitosamente (OPTIMIZADO)");
                // Ya no es necesario llamar a mejorarVisualizacionCalendario por separado
            })
            .catch(error => {
                console.error("Error al recargar calendario:", error);
                
                // Mostrar mensaje de error amigable
                Swal.fire({
                    icon: 'warning',
                    title: 'Problema al cargar el calendario',
                    text: 'Hubo un problema al actualizar las citas. Por favor, intente nuevamente.',
                    timer: 3000
                });
            });
    } else {
        console.error("No se pudo recargar el calendario: objeto calendario no disponible");
    }
}
// Configurar proceder con el pago
function configurarProcederPago() {
    const btnProcederPago = document.getElementById("btn-proceder-pago");
    
    if (btnProcederPago) {
        console.log("Configurando evento click para el botón de pago");
        
        // Eliminar cualquier evento click existente para evitar duplicaciones
        const nuevoBtn = btnProcederPago.cloneNode(true);
        btnProcederPago.parentNode.replaceChild(nuevoBtn, btnProcederPago);
        
        // Agregar el evento click al botón limpio
        document.getElementById("btn-proceder-pago").addEventListener("click", function(event) {
            console.log("Botón de pago clickeado");
            event.preventDefault();
            
            // Si el botón está deshabilitado, no continuar
            if (this.disabled || this.classList.contains('disabled')) {
                console.log("Botón deshabilitado, no se puede proceder con el pago");
                
                // Mostrar mensaje explicando por qué no se puede proceder
                const especialidadSelect = document.getElementById("especialidad");
                const nombreEspecialidad = especialidadSelect ? 
                    especialidadSelect.options[especialidadSelect.selectedIndex].text : '';
                
                if (window.datosPaciente) {
                    const validacionEspecialidad = validarEspecialidadPorPaciente(nombreEspecialidad);
                    if (!validacionEspecialidad.valido) {
                        mostrarRestriccionEspecialidad(validacionEspecialidad.mensaje);
                    }
                }
                
                return false;
            }
            
            // Validar que todos los campos estén completos
            const idDoctor = document.getElementById("doctor").value;
            const idEspecialidad = document.getElementById("especialidad").value;
            const fecha = document.getElementById("fecha-reserva").value;
            const hora = document.getElementById("hora-hidden").value;
            const idPaciente = document.getElementById("idpaciente").value;

            if (!idDoctor || !idEspecialidad || !fecha || !hora || !idPaciente) {
                Swal.fire({
                  icon: "warning",
                  title: "Datos incompletos",
                  text: "Por favor, complete todos los campos requeridos para la cita.",
                });
                return false;
            }

            // Validación y carga del modal
            console.log("Procediendo a cargar el modal de pago");
            cargarModalPago();
        });
    } else {
        console.error("Botón de proceder pago no encontrado en el DOM");
    }
}

// Verificar si todos los campos necesarios están completos para habilitar el botón de pago
function verificarHabilitarBotonPago() {
    const btnPago = document.getElementById('btn-proceder-pago');
    if (!btnPago) {
        console.error("No se encontró el botón de pago en el DOM");
        return;
    }
    
    console.log("Verificando si se debe habilitar el botón de pago...");
    
    // Verificar campos de información de la cita
    const especialidad = document.getElementById('especialidad')?.value;
    const doctor = document.getElementById('doctor')?.value;
    const fecha = document.getElementById('fecha-reserva')?.value;
    const hora = document.getElementById('hora-hidden')?.value;
    
    // Verificar campos del paciente
    const idpaciente = document.getElementById('idpaciente')?.value;
    const nombre = document.getElementById('nombre')?.value;
    const apellido = document.getElementById('apellido')?.value;
    
    // Verificar si todos los campos requeridos tienen valor
    const todosLosCamposCompletos = 
        especialidad && especialidad !== 'Seleccione Especialidad' && 
        doctor && doctor !== 'Seleccione Doctor' && 
        fecha && 
        hora && 
        idpaciente && 
        nombre && 
        apellido;
    
    console.log("Verificación de campos:", {
        especialidad, doctor, fecha, hora, idpaciente, nombre, apellido,
        todosLosCamposCompletos
    });
    
    // CRÍTICO: Verificar adicionalmente validación de especialidad
    let especialidadValida = true;
    
    // Solo verificar especialidad si hay paciente y especialidad seleccionados
    if (window.datosPaciente && especialidad && especialidad !== 'Seleccione Especialidad') {
        // Obtener nombre de la especialidad
        const especialidadSelect = document.getElementById('especialidad');
        const nombreEspecialidad = especialidadSelect.options[especialidadSelect.selectedIndex].text;
        
        // Validar especialidad para el paciente
        const validacion = validarEspecialidadPorPaciente(nombreEspecialidad);
        especialidadValida = validacion.valido;
        
        // Si no es válida, mostrar mensaje y deshabilitar botón
        if (!validacion.valido) {
            // Mostrar la restricción si no se ha mostrado ya
            const alertaExistente = document.getElementById('alerta-restriccion-especialidad');
            if (!alertaExistente || !alertaExistente.querySelector('.toast.show')) {
                mostrarRestriccionEspecialidad(validacion.mensaje);
            }
            
            // Bloquear botón con la nueva función
            btnPago.disabled = true;
            btnPago.classList.add('disabled');
            btnPago.classList.remove('btn-success');
            btnPago.classList.add('btn-secondary');
            
            if (validacion.mensaje) {
                btnPago.setAttribute('title', validacion.mensaje);
            }
            
            console.log("Botón deshabilitado por restricción de especialidad:", validacion.mensaje);
            return; // No continuar con más validaciones
        }
    }
    
    // Si todos los campos están completos Y la especialidad es válida para el paciente
    if (todosLosCamposCompletos && especialidadValida) {
        // Habilitar botón directamente sin usar bloquearBotonPago()
        btnPago.disabled = false;
        btnPago.classList.remove('disabled');
        btnPago.classList.add('btn-success');
        btnPago.classList.remove('btn-secondary');
        btnPago.removeAttribute('title');
        console.log("Botón de pago HABILITADO ✓");
    } else {
        // Deshabilitar botón
        btnPago.disabled = true;
        btnPago.classList.add('disabled');
        btnPago.classList.remove('btn-success');
        btnPago.classList.add('btn-secondary');
        console.log("Botón de pago deshabilitado - Campos incompletos");
    }
}
/**
 * Función para configurar eventos del modal de pago
 */
function configurarEventosModalPago() {
  console.log("Configurando eventos del modal de pago...");

  // Configurar cambio de tipo de cliente
  const tipoCliente = document.getElementById("tipoCliente");
  const datosEmpresaContainer = document.getElementById(
    "datosEmpresaContainer"
  );
  const datosPagadorContainer = document.getElementById(
    "datosPagadorContainer"
  );

  // NUEVO: Referencia al tipo de comprobante
  const tipoComprobante = document.getElementById("tipoComprobante");
  const datosFacturaContainer = document.getElementById(
    "datosFacturaContainer"
  );

  // NUEVO: Configurar validación del teléfono de empresa
  const telefonoEmpresa = document.getElementById("telefonoEmpresa");
  if (telefonoEmpresa) {
    telefonoEmpresa.addEventListener("input", function () {
      // Eliminar caracteres no numéricos
      this.value = this.value.replace(/[^0-9]/g, "");

      // Forzar que empiece con 9
      if (this.value.length > 0 && this.value[0] !== "9") {
        this.value = "9" + this.value.substring(this.value.length > 1 ? 1 : 0);
      }

      // Limitar a exactamente 9 dígitos
      if (this.value.length > 9) {
        this.value = this.value.substring(0, 9);
      }

      // Mostrar mensaje de validación
      const telefonoError =
        document.getElementById("telefonoEmpresaError") ||
        document.createElement("div");
      telefonoError.id = "telefonoEmpresaError";
      telefonoError.className = "text-danger small mt-1";

      if (this.value.length > 0 && this.value.length < 9) {
        telefonoError.textContent = "El teléfono debe tener 9 dígitos";
        if (!document.getElementById("telefonoEmpresaError")) {
          this.parentNode.appendChild(telefonoError);
        }
      } else {
        if (document.getElementById("telefonoEmpresaError")) {
          telefonoError.remove();
        }
      }
    });
  }

  // NUEVO: Asegurar que el tipo de documento del pagador sea DNI por defecto
  const tipoDocPagador = document.getElementById("tipoDocPagador");
  if (tipoDocPagador) {
    console.log(
      "Estableciendo DNI como valor por defecto en tipo de documento"
    );
    tipoDocPagador.value = "dni";
  }

  if (tipoCliente && datosEmpresaContainer && datosPagadorContainer) {
    console.log("Elementos encontrados, configurando listener...");

    tipoCliente.addEventListener("change", function () {
      // Guardar selección actual
      const seleccion = this.value;
      console.log("Cambio de tipo de cliente:", seleccion);

      // NUEVO: Limpiar campos que no corresponden al tipo seleccionado
      if (seleccion !== "empresa") {
        // Si cambia de empresa a otro tipo, limpiar campos de empresa
        limpiarCamposEspecificos([
          "ruc",
          "razonSocial",
          "direccionEmpresa",
          "nombreComercial",
          "telefonoEmpresa",
          "emailEmpresa",
          "idempresaPago",
        ]);
      }

      if (seleccion !== "tercero") {
        // Si cambia de tercero a otro tipo, limpiar campos de pagador
        limpiarCamposEspecificos([
          "documentoPagador",
          "nombresPagador",
          "apellidosPagador",
          "tipoDocPagador",
        ]);
      }

      // IMPORTANTE: Siempre limpiar los campos de factura cuando se cambia de tipo cliente
      // para evitar que se filtren datos
      limpiarCamposEspecificos([
        "rucFactura",
        "razonSocialFactura",
        "direccionFactura",
        "idempresaFactura",
      ]);

      // Ocultar ambos contenedores primero
      datosEmpresaContainer.classList.add("d-none");
      datosPagadorContainer.classList.add("d-none");

      // Mostrar el contenedor correspondiente según la selección
      // NUEVO: Gestionar automáticamente el tipo de comprobante
      if (tipoComprobante) {
        if (seleccion === "empresa") {
          // Si es empresa, seleccionar automáticamente "factura" y deshabilitar
          tipoComprobante.value = "factura";
          tipoComprobante.disabled = true;

          // Como es factura, mostrar los datos de factura pero ocultarlos
          // porque son redundantes con los datos de empresa
          if (datosFacturaContainer) {
            // Ocultar los campos de factura para evitar duplicidad
            datosFacturaContainer.classList.add("d-none");
          }
        } else {
          // Si no es empresa, habilitar la selección de tipo de comprobante
          tipoComprobante.disabled = false;

          // Resetear a boleta por defecto
          tipoComprobante.value = "boleta";

          // Ocultar datos de factura
          if (datosFacturaContainer) {
            datosFacturaContainer.classList.add("d-none");
          }
        }

        // Disparar el evento change para que se actualicen los campos visibles
        const event = new Event("change");
        tipoComprobante.dispatchEvent(event);
      }

      if (seleccion === "empresa") {
        console.log("Mostrando contenedor de empresa");
        datosEmpresaContainer.classList.remove("d-none");

        // CORRECCIÓN: Bloquear todos los campos de empresa excepto el RUC inicialmente
        const camposEmpresa = [
          "razonSocial",
          "direccionEmpresa",
          "nombreComercial",
          "telefonoEmpresa",
          "emailEmpresa",
        ];

        // Bloquear todos los campos
        camposEmpresa.forEach((campo) => {
          const input = document.getElementById(campo);
          if (input) {
            input.readOnly = true;
          }
        });

        // Asegurar que el RUC sea editable
        const rucInput = document.getElementById("ruc");
        if (rucInput) {
          rucInput.readOnly = false;

          // Establecer foco en el campo RUC
          setTimeout(() => {
            rucInput.focus();
          }, 100);
        }

        // NUEVO: Aplicar restricciones al RUC cuando se muestra este contenedor
        setTimeout(() => {
          const rucInput = document.getElementById("ruc");
          if (rucInput) {
            // Establecer restricciones del RUC: exactamente 11 dígitos
            rucInput.maxLength = 11;
            rucInput.pattern = "[0-9]*";
            rucInput.inputMode = "numeric";

            // Validar solo números en el RUC
            rucInput.addEventListener("input", function () {
              this.value = this.value.replace(/[^0-9]/g, "");
              if (this.value.length > 11) {
                this.value = this.value.substring(0, 11);
              }
            });
          }
        }, 100);
      } else if (seleccion === "tercero") {
        console.log("Mostrando contenedor de pagador");
        datosPagadorContainer.classList.remove("d-none");

        // Asegurarse de que los campos nombres y apellidos estén deshabilitados inicialmente
        document.getElementById("nombresPagador").readOnly = true;
        document.getElementById("apellidosPagador").readOnly = true;

        // NUEVO: Aplicar restricciones al documento del pagador y ESTABLECER FOCO
        setTimeout(() => {
          // Asegurarse de que el campo de tipo de documento del pagador tiene DNI seleccionado
          const tipoDocPagador = document.getElementById("tipoDocPagador");
          if (tipoDocPagador) {
            tipoDocPagador.value = "dni";
          }

          // Establecer foco en el campo de documento del pagador
          const documentoPagador = document.getElementById("documentoPagador");
          if (documentoPagador) {
            documentoPagador.value = ""; // Limpiar el campo por seguridad
            documentoPagador.focus(); // PUNTO CLAVE: Establecer el foco automáticamente
            console.log("Foco establecido en campo de documento del pagador");

            // Configurar restricciones iniciales según el tipo de documento
            aplicarRestriccionesDocumento("documentoPagador", "tipoDocPagador");
          }
        }, 100);
      }
    });
  } else {
    console.error("No se encontraron los elementos necesarios:");
    console.error("tipoCliente:", tipoCliente);
    console.error("datosEmpresaContainer:", datosEmpresaContainer);
    console.error("datosPagadorContainer:", datosPagadorContainer);
  }

  // NUEVO: Configurar cambio de tipo de comprobante
  if (tipoComprobante && datosFacturaContainer) {
    console.log("Configurando evento para tipo de comprobante");

    tipoComprobante.addEventListener("change", function () {
      // Limpiar campos de factura al cambiar tipo de comprobante
      limpiarCamposEspecificos([
        "rucFactura",
        "razonSocialFactura",
        "direccionFactura",
        "idempresaFactura",
      ]);

      // Mostrar/ocultar contenedor según la selección
      const seleccion = this.value;
      console.log("Cambio de tipo de comprobante:", seleccion);

      if (seleccion === "factura") {
        console.log("Mostrando contenedor de datos para factura");

        // Si el tipo de cliente es empresa, no mostrar los campos de factura
        const tipoClienteActual = document.getElementById("tipoCliente")?.value;
        if (tipoClienteActual === "empresa") {
          datosFacturaContainer.classList.add("d-none");
        } else {
          datosFacturaContainer.classList.remove("d-none");

          // Aplicar restricciones al RUC cuando se muestra este contenedor
          setTimeout(() => {
            const rucFacturaInput = document.getElementById("rucFactura");
            if (rucFacturaInput) {
              // Limpiar valor previo
              rucFacturaInput.value = "";
              rucFacturaInput.readOnly = false;

              // Establecer restricciones del RUC: exactamente 11 dígitos
              rucFacturaInput.maxLength = 11;
              rucFacturaInput.pattern = "[0-9]*";
              rucFacturaInput.inputMode = "numeric";

              // Validar solo números en el RUC
              rucFacturaInput.addEventListener("input", function () {
                this.value = this.value.replace(/[^0-9]/g, "");
                if (this.value.length > 11) {
                  this.value = this.value.substring(0, 11);
                }
              });
            }

            // CORRECCIÓN: Limpiar y bloquear campos de factura
            const facturaFields = ["razonSocialFactura", "direccionFactura"];
            facturaFields.forEach((field) => {
              const input = document.getElementById(field);
              if (input) {
                input.value = "";
                input.readOnly = true; // IMPORTANTE: Inicialmente bloqueados
              }
            });
          }, 100);
        }
      } else {
        console.log("Ocultando contenedor de datos para factura");
        datosFacturaContainer.classList.add("d-none");
      }
    });
  } else {
    console.error(
      "No se encontraron los elementos para el tipo de comprobante:"
    );
    console.error("tipoComprobante:", tipoComprobante);
    console.error("datosFacturaContainer:", datosFacturaContainer);
  }

  // Configurar botón de búsqueda de documento para pagador tercero
  const btnBuscarDocPagador = document.getElementById("buscarDocPagador");
  if (btnBuscarDocPagador) {
    console.log("Configurando evento para búsqueda de pagador - Nuevo método");

    btnBuscarDocPagador.addEventListener("click", async function () {
      // Obtener valores de los campos
      const tipoDocSelect = document.getElementById("tipoDocPagador");
      const tipoDoc = tipoDocSelect.value;
      const nroDoc = document.getElementById("documentoPagador").value;
      const nombresInput = document.getElementById("nombresPagador");
      const apellidosInput = document.getElementById("apellidosPagador");

      console.log("Búsqueda iniciada con tipo:", tipoDoc, "y número:", nroDoc);

      // Validaciones básicas
      if (!nroDoc) {
        Swal.fire({
          icon: "warning",
          title: "Documento inválido",
          text: "Por favor, ingrese un número de documento",
        });
        return;
      }

      // Validar formato del documento según tipo
      if (!validarDocumentoPagador(tipoDoc, nroDoc)) {
        Swal.fire({
          icon: "warning",
          title: "Formato de documento inválido",
          text: obtenerMensajeValidacionDocumentoPagador(tipoDoc),
        });
        return;
      }

      // Convertir tipo de documento del UI al formato de la BD
      const tipoDB = obtenerTipoDocumentoDB(tipoDoc);

      mostrarCargando();
      try {
        // Buscar cliente usando la función de cliente.js
        console.log(
          "Buscando cliente con tipo:",
          tipoDB,
          "y documento:",
          nroDoc
        );
        const cliente = await buscarClientePorDocumento(tipoDB, nroDoc);

        // Si se encuentra el cliente
        if (cliente) {
          console.log("Cliente encontrado:", cliente);
          // Llenar y bloquear campos
          nombresInput.value = cliente.nombres || "";
          apellidosInput.value = cliente.apellidos || "";

          nombresInput.readOnly = true;
          apellidosInput.readOnly = true;

          // Mostrar mensaje de éxito
          Swal.fire({
            icon: "success",
            title: "Cliente encontrado",
            text: "Los datos del cliente han sido cargados",
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
          });
        } else {
          console.log("Cliente no encontrado, habilitando campos");
          // Limpiar campos y habilitarlos para ingreso manual
          nombresInput.value = "";
          apellidosInput.value = "";

          nombresInput.readOnly = false;
          apellidosInput.readOnly = false;

          // Mostrar mensaje informativo
          Swal.fire({
            icon: "info",
            title: "Cliente no encontrado",
            text: "Por favor, complete los datos para registrar un nuevo cliente",
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
          });
        }
      } catch (error) {
        console.error("Error al buscar cliente:", error);

        // En caso de error, habilitar campos y mostrar mensaje
        nombresInput.readOnly = false;
        apellidosInput.readOnly = false;

        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Error al buscar cliente: " + error.message,
        });
      } finally {
        ocultarCargando();
      }
    });
  } else {
    console.error("No se encontró el botón de búsqueda de pagador");
  }

  // NUEVO: Configurar botón de búsqueda de RUC para factura
  const btnBuscarRucFactura = document.getElementById("buscarRucFactura");
  if (btnBuscarRucFactura) {
    console.log("Configurando evento para búsqueda de RUC para factura");

    btnBuscarRucFactura.addEventListener("click", async function () {
      const ruc = document.getElementById("rucFactura").value;

      // Validar formato de RUC
      if (!validarRUC(ruc)) {
        Swal.fire({
          icon: "warning",
          title: "RUC inválido",
          text: "El RUC debe tener 11 dígitos y comenzar con 10, 15, 17 o 20",
        });
        return;
      }

      mostrarCargando();
      try {
        // Buscar empresa
        const empresa = await buscarEmpresaPorRuc(ruc);

        if (empresa) {
          // IMPORTANTE: Asegurarse de NO transferir datos entre secciones
          // Usar solo los datos específicos para esta sección (factura)

          // Llenar y bloquear campos
          document.getElementById("razonSocialFactura").value =
            empresa.razonsocial || "";
          document.getElementById("direccionFactura").value =
            empresa.direccion || "";
          document.getElementById("idempresaFactura").value =
            empresa.idempresa || "";

          // CORRECCIÓN: Siempre bloquear los campos cuando se encuentra una empresa
          document.getElementById("razonSocialFactura").readOnly = true;
          document.getElementById("direccionFactura").readOnly = true;

          Swal.fire({
            icon: "success",
            title: "Empresa encontrada",
            text: "Los datos de la empresa han sido cargados",
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
          });
        } else {
          // Limpiar y habilitar campos para ingreso manual
          document.getElementById("razonSocialFactura").value = "";
          document.getElementById("direccionFactura").value = "";
          document.getElementById("idempresaFactura").value = "";

          // CORRECTO: Habilitar edición solo cuando no se encuentra empresa
          document.getElementById("razonSocialFactura").readOnly = false;
          document.getElementById("direccionFactura").readOnly = false;

          Swal.fire({
            icon: "info",
            title: "Empresa no encontrada",
            text: "Por favor, complete los datos para registrar una nueva empresa",
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
          });
        }
      } catch (error) {
        console.error("Error al buscar empresa para factura:", error);

        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Error al buscar empresa: " + error.message,
        });
      } finally {
        ocultarCargando();
      }
    });
  } else {
    console.error("No se encontró el botón de búsqueda de RUC para factura");
  }

  // Evento para cambiar validación cuando cambia el tipo de documento del pagador
  if (tipoDocPagador) {
    tipoDocPagador.addEventListener("change", function () {
      console.log("Cambio en tipo de documento del pagador:", this.value);
      // Limpiar campo de documento
      const docInput = document.getElementById("documentoPagador");
      if (docInput) {
        docInput.value = "";
      }

      // Limpiar y bloquear nombres y apellidos
      const nombresInput = document.getElementById("nombresPagador");
      const apellidosInput = document.getElementById("apellidosPagador");

      if (nombresInput && apellidosInput) {
        nombresInput.value = "";
        apellidosInput.value = "";
        nombresInput.readOnly = true;
        apellidosInput.readOnly = true;
      }
    });
  }

  // CORRECCIÓN: Configurar el botón de procesar pago y verificar su estado inicial
  const btnProcesarPago = document.getElementById("btnProcesarPago");
  const metodoPago = document.getElementById("metodoPago");
  const montoPagadoInput = document.getElementById("montoPagado");
  const vueltoContainer = document.getElementById("vueltoContainer");
  const vueltoMonto = document.getElementById("vueltoMonto");

  if (btnProcesarPago && metodoPago && montoPagadoInput) {
    console.log("Configurando eventos para el botón Procesar Pago");

    // FUNCIÓN MEJORADA: Verificar estado del botón de procesar pago
    const verificarEstadoBoton = () => {
      const metodo = metodoPago.value;
      const montoPagado = parseFloat(montoPagadoInput.value) || 0;
      const precioTotal = obtenerPrecioTotalComprobante();

      console.log(
        `Verificando estado del botón: método=${metodo}, monto=${montoPagado}, precio=${precioTotal}`
      );

      if (metodo === "efectivo") {
        // Para método efectivo, verificar que el monto sea suficiente
        if (montoPagado >= precioTotal) {
          btnProcesarPago.disabled = false;

          // Calcular y mostrar vuelto
          const vuelto = montoPagado - precioTotal;
          if (vueltoContainer && vueltoMonto) {
            vueltoMonto.textContent = `S/ ${vuelto.toFixed(2)}`;
            vueltoContainer.style.display = "block";
          }
        } else {
          btnProcesarPago.disabled = true;
          if (vueltoContainer) {
            vueltoContainer.style.display = "none";
          }
        }
      } else {
        // Para otros métodos de pago, habilitar siempre
        btnProcesarPago.disabled = false;
        if (vueltoContainer) {
          vueltoContainer.style.display = "none";
        }
      }
    };

    // Configurar eventos
    metodoPago.addEventListener("change", function () {
      const metodo = this.value;
      const montoClienteContainer = document.getElementById(
        "montoClienteContainer"
      );

      if (metodo === "efectivo") {
        if (montoClienteContainer)
          montoClienteContainer.style.display = "block";
      } else {
        if (montoClienteContainer) montoClienteContainer.style.display = "none";
        if (vueltoContainer) vueltoContainer.style.display = "none";
      }

      // Verificar estado del botón después del cambio
      verificarEstadoBoton();
    });

    montoPagadoInput.addEventListener("input", verificarEstadoBoton);

    // Configurar click en el botón
    btnProcesarPago.addEventListener("click", function () {
      console.log("Procesando pago...");
      procesarPago();
    });

    // SOLUCIÓN CLAVE: Verificar estado inicial del botón al cargar
    setTimeout(verificarEstadoBoton, 300);
  }

  // NUEVO: Buscar RUC de empresa cuando se hace clic en el botón
  const btnBuscarRuc = document.getElementById("buscarRuc");
  if (btnBuscarRuc) {
    btnBuscarRuc.addEventListener("click", async function () {
      const rucInput = document.getElementById("ruc");
      if (!rucInput) {
        console.error("No se encontró el campo de RUC");
        return;
      }

      const ruc = rucInput.value;

      // Validar formato de RUC
      if (!validarRUC(ruc)) {
        Swal.fire({
          icon: "warning",
          title: "RUC inválido",
          text: "El RUC debe tener 11 dígitos y comenzar con 10, 15, 17 o 20.",
        });
        return;
      }

      mostrarCargando();
      try {
        // Buscar empresa
        const empresa = await buscarEmpresaPorRuc(ruc);

        if (empresa) {
          // CORRECCIÓN: Usar nueva función para cargar datos bloqueando solo campos con datos
          cargarDatosEmpresaConBloqueo(empresa);

          // También cargar los datos automáticamente en los campos de factura aunque estén ocultos
          cargarDatosFacturaConBloqueo(empresa);

          Swal.fire({
            icon: "success",
            title: "Empresa encontrada",
            text: "Los datos de la empresa han sido cargados",
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
          });
        } else {
          // CORRECCIÓN: Habilitar todos los campos para ingreso manual
          habilitarCamposEmpresa();

          // Limpiar campos excepto RUC
          limpiarCamposEmpresaExceptoRUC();

          Swal.fire({
            icon: "info",
            title: "Empresa no encontrada",
            text: "Por favor, complete los datos para registrar una nueva empresa",
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
          });
        }
      } catch (error) {
        console.error("Error al buscar empresa:", error);

        // CORRECCIÓN: Mostrar error como notificación en esquina en lugar de popup central
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Error al buscar empresa: " + error.message,
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 3000,
        });

        // Habilitar campos para ingreso manual en caso de error
        habilitarCamposEmpresa();
      } finally {
        ocultarCargando();
      }
    });
  }

  // NUEVO: MODIFICACIÓN PARA BLOQUEAR CAMPO DE MONTO PAGADO SEGÚN MÉTODO DE PAGO
  const metodoPagoInput = document.getElementById("metodoPago");
  const montoPagadoElement = document.getElementById("montoPagado");
  const montoClienteContainer = document.getElementById(
    "montoClienteContainer"
  );

  if (metodoPagoInput && montoPagadoElement && montoClienteContainer) {
    console.log(
      "Configurando evento de cambio de método de pago para bloquear monto"
    );

    // NUEVA FUNCIÓN: Para establecer el valor del monto pagado según el precio total
    const establecerMontoPagadoAutomatico = () => {
      const precioTotal = obtenerPrecioTotalComprobante();
      console.log(
        "Estableciendo monto pagado automático:",
        precioTotal.toFixed(2)
      );

      // Establecer el valor en el input
      montoPagadoElement.value = precioTotal.toFixed(2);

      // IMPORTANTE: Almacenar también en una variable global para acceso posterior
      if (typeof window.montoExactoPagado === "undefined") {
        window.montoExactoPagado = {};
      }
      window.montoExactoPagado.valor = precioTotal;
      window.montoExactoPagado.metodo = metodoPagoInput.value.toUpperCase();
      console.log("Almacenado monto exacto global:", window.montoExactoPagado);

      // Forzar evento de input para recalcular vuelto y validar botón
      const inputEvent = new Event("input", { bubbles: true });
      montoPagadoElement.dispatchEvent(inputEvent);
    };

    // Disparar el evento change inicialmente para configurar correctamente
    setTimeout(() => {
      // Verificar si el botón necesita estar habilitado
      verificarHabilitarBotonProcesarPago();
    }, 300);
  }
}

/**
 * NUEVA FUNCIÓN: Carga datos de empresa y bloquea solo los campos con datos
 * @param {Object} empresa Datos de la empresa
 */
function cargarDatosEmpresaConBloqueo(empresa) {
  if (!empresa) return;

  // Depuración
  console.log("Cargando datos de empresa con bloqueo selectivo:", empresa);

  // Mapeo de campos del formulario con propiedades de la empresa
  const camposEmpresa = [
    { campo: "razonSocial", propiedad: "razonsocial" },
    { campo: "direccionEmpresa", propiedad: "direccion" },
    { campo: "nombreComercial", propiedad: "nombrecomercial" },
    { campo: "telefonoEmpresa", propiedad: "telefono" },
    { campo: "emailEmpresa", propiedad: "email" },
    { campo: "idempresaPago", propiedad: "idempresa" },
  ];

  // Procesar cada campo
  camposEmpresa.forEach((item) => {
    const campo = document.getElementById(item.campo);
    if (campo) {
      // Obtener valor actual del campo (lo que ingresó el usuario)
      const valorActual = campo.value.trim();

      // Obtener valor desde los datos de empresa, asegurando que sea string
      let valorBD = empresa[item.propiedad];

      // Validar que el valor sea un string y no sea null/undefined
      if (valorBD === null || valorBD === undefined) {
        valorBD = "";
      }

      // Convertir a string de manera segura
      valorBD = String(valorBD).trim();

      // Si el usuario ya ingresó un valor y el campo no tiene dato en BD,
      // mantener el valor ingresado por el usuario
      if (
        valorActual !== "" &&
        valorBD === "" &&
        item.campo !== "idempresaPago"
      ) {
        console.log(
          `Manteniendo valor ingresado por usuario en ${item.campo}: "${valorActual}"`
        );
        campo.readOnly = false;
      } else {
        // Establecer el valor en el campo
        campo.value = valorBD;

        // Bloquear solo si tiene datos (no está vacío después de eliminar espacios)
        if (valorBD !== "" && item.campo !== "idempresaPago") {
          campo.readOnly = true;
          console.log(
            `Campo ${item.campo} bloqueado porque tiene valor: "${valorBD}"`
          );
        } else if (item.campo !== "idempresaPago") {
          campo.readOnly = false; // Habilitar si está vacío
          console.log(`Campo ${item.campo} habilitado porque está vacío`);
        }
      }
    }
  });

  // Siempre mantener el campo RUC editable para posibles correcciones
  const rucInput = document.getElementById("ruc");
  if (rucInput) {
    rucInput.readOnly = false;
    console.log("Campo RUC siempre se mantiene editable");
  }
}

/**
 * NUEVA FUNCIÓN: Carga datos de empresa en campos de factura y bloquea solo los que tienen datos
 * @param {Object} empresa Datos de la empresa
 */
function cargarDatosFacturaConBloqueo(empresa) {
  if (!empresa) return;

  console.log("Cargando datos para factura:", empresa);

  // Mapeo de campos de factura con propiedades de la empresa
  const camposFactura = [
    { campo: "rucFactura", propiedad: "ruc" },
    { campo: "razonSocialFactura", propiedad: "razonsocial" },
    { campo: "direccionFactura", propiedad: "direccion" },
    { campo: "idempresaFactura", propiedad: "idempresa" },
  ];

  // Procesar cada campo
  camposFactura.forEach((item) => {
    const campo = document.getElementById(item.campo);
    if (campo) {
      // Obtener valor actual del campo (lo que ingresó el usuario)
      const valorActual = campo.value.trim();

      // Obtener valor desde los datos de empresa
      let valorBD = empresa[item.propiedad];

      // Validar que el valor sea un string y no sea null/undefined
      if (valorBD === null || valorBD === undefined) {
        valorBD = "";
      }

      // Convertir a string de manera segura
      valorBD = String(valorBD).trim();

      // Si el usuario ya ingresó un valor y el campo no tiene dato en BD,
      // mantener el valor ingresado por el usuario
      if (
        valorActual !== "" &&
        valorBD === "" &&
        item.campo !== "idempresaFactura"
      ) {
        console.log(
          `Manteniendo valor ingresado por usuario en ${item.campo}: "${valorActual}"`
        );
        campo.readOnly = false;
      } else {
        // Establecer el valor en el campo
        campo.value = valorBD;

        // Bloquear solo si tiene datos (no está vacío después de eliminar espacios)
        if (valorBD !== "" && item.campo !== "idempresaFactura") {
          campo.readOnly = true;
          console.log(
            `Campo de factura ${item.campo} bloqueado porque tiene valor: "${valorBD}"`
          );
        } else if (item.campo !== "idempresaFactura") {
          campo.readOnly = false; // Habilitar si está vacío
          console.log(
            `Campo de factura ${item.campo} habilitado porque está vacío`
          );
        }
      }
    }
  });
}
/**
 * Limpia todos los campos de un conjunto específico
 * @param {Array} campos Lista de IDs de campos a limpiar
 */
function limpiarCamposEspecificos(campos) {
  campos.forEach((campo) => {
    const input = document.getElementById(campo);
    if (input) {
      input.value = "";
      input.readOnly = false;
    }
  });
  console.log(`Campos limpiados: ${campos.join(", ")}`);
}

/**
 * NUEVA FUNCIÓN: Habilita todos los campos de empresa para edición
 */
function habilitarCamposEmpresa() {
  const camposEmpresa = [
    "razonSocial",
    "direccionEmpresa",
    "nombreComercial",
    "telefonoEmpresa",
    "emailEmpresa",
  ];

  camposEmpresa.forEach((campo) => {
    const input = document.getElementById(campo);
    if (input) {
      input.readOnly = false;
    }
  });
}

/**
 * NUEVA FUNCIÓN: Limpia todos los campos de empresa excepto el RUC
 */
function limpiarCamposEmpresaExceptoRUC() {
  const camposEmpresa = [
    "razonSocial",
    "direccionEmpresa",
    "nombreComercial",
    "telefonoEmpresa",
    "emailEmpresa",
    "idempresaPago",
  ];

  camposEmpresa.forEach((campo) => {
    const input = document.getElementById(campo);
    if (input) {
      input.value = "";
    }
  });
}
/**
 * Verifica y habilita/deshabilita el botón de Procesar Pago según las validaciones
 */
function verificarHabilitarBotonProcesarPago() {
  // Obtener elementos
  const metodoPago = document.getElementById("metodoPago");
  const montoPagado = document.getElementById("montoPagado");
  const btnProcesarPago = document.getElementById("btnProcesarPago");
  const vueltoContainer = document.getElementById("vueltoContainer");
  const vueltoMonto = document.getElementById("vueltoMonto");

  // Si no existen los elementos, salir
  if (!metodoPago || !btnProcesarPago) return;

  // Obtener el precio total como número
  const precioTotal = obtenerPrecioTotalComprobante();

  // CLAVE DE SOLUCIÓN: Imprimir valores para depuración
  console.log("Verificando botón con valores:", {
    metodoPago: metodoPago.value,
    montoPagado: montoPagado ? parseFloat(montoPagado.value) || 0 : 0,
    precioTotal: precioTotal,
  });

  // Habilitar por defecto
  btnProcesarPago.disabled = false;

  // Si el método de pago es efectivo, verificar que el monto sea suficiente
  if (metodoPago.value === "efectivo") {
    // Mostrar campo de monto
    const montoClienteContainer = document.getElementById(
      "montoClienteContainer"
    );
    if (montoClienteContainer) {
      montoClienteContainer.style.display = "block";
    }

    // Convertir a número para asegurar cálculo correcto
    const montoIngresado = parseFloat(montoPagado?.value || "0");

    if (montoIngresado < precioTotal) {
      btnProcesarPago.disabled = true;
      console.log("Botón deshabilitado: monto insuficiente");

      // Ocultar vuelto si es insuficiente
      if (vueltoContainer) {
        vueltoContainer.style.display = "none";
      }
    } else {
      btnProcesarPago.disabled = false;
      console.log("Botón habilitado: monto suficiente");

      // CORRECCIÓN IMPORTANTE: Calcular y mostrar vuelto correctamente
      const vuelto = montoIngresado - precioTotal;

      if (vueltoContainer && vueltoMonto) {
        vueltoMonto.textContent = `S/ ${vuelto.toFixed(2)}`;
        vueltoContainer.style.display = "block";
        console.log("Vuelto calculado y mostrado:", vuelto.toFixed(2));
      }
    }
  } else {
    // Para otros métodos de pago, ocultar campo de monto
    const montoClienteContainer = document.getElementById(
      "montoClienteContainer"
    );
    if (montoClienteContainer) {
      montoClienteContainer.style.display = "none";
    }

    // Ocultar vuelto
    if (vueltoContainer) {
      vueltoContainer.style.display = "none";
    }

    // SIEMPRE habilitar para métodos que no son efectivo
    btnProcesarPago.disabled = false;
    console.log("Botón habilitado: método no es efectivo");
  }

  console.log(
    "Estado final del botón:",
    btnProcesarPago.disabled ? "Deshabilitado" : "Habilitado"
  );
}

// Modificar ventaCita.php para añadir los eventos necesarios
function configurarEventosVentaCita() {
  // Configurar el cambio de método de pago para habilitar/deshabilitar el botón
  const metodoPago = document.getElementById("metodoPago");
  const montoPagado = document.getElementById("montoPagado");

  if (metodoPago) {
    metodoPago.addEventListener("change", function () {
      verificarHabilitarBotonProcesarPago();
    });
  }

  if (montoPagado) {
    montoPagado.addEventListener("input", function () {
      verificarHabilitarBotonProcesarPago();
    });
  }

  // Inicializar estado del botón
  verificarHabilitarBotonProcesarPago();
}
// NUEVA FUNCIÓN: Aplicar restricciones a los campos de documento según su tipo
function aplicarRestriccionesDocumento(inputId, tipoDocId) {
  const inputDocumento = document.getElementById(inputId);
  const selectTipoDoc = document.getElementById(tipoDocId);

  if (!inputDocumento || !selectTipoDoc) return;

  // Función para obtener la configuración de restricción según tipo de documento
  function obtenerConfiguracionDocumento(tipoDoc) {
    // Para UI (documentoPagador, tipoDocPagador)
    if (tipoDoc === "dni") {
      return {
        maxLength: 8,
        pattern: "[0-9]*",
        inputMode: "numeric",
      };
    } else if (tipoDoc === "pasaporte") {
      return {
        maxLength: 12,
        pattern: "[A-Za-z0-9]*",
        inputMode: "text",
      };
    } else if (tipoDoc === "carnet") {
      return {
        maxLength: 9,
        pattern: "[A-Za-z0-9]*",
        inputMode: "text",
      };
    } else if (tipoDoc === "otro") {
      return {
        maxLength: 15,
        pattern: null,
        inputMode: "text",
      };
    }
    // Para BD (numero-documento, tipo-documento)
    else if (tipoDoc === "DNI") {
      return {
        maxLength: 8,
        pattern: "[0-9]*",
        inputMode: "numeric",
      };
    } else if (tipoDoc === "PASAPORTE") {
      return {
        maxLength: 12,
        pattern: "[A-Za-z0-9]*",
        inputMode: "text",
      };
    } else if (tipoDoc === "CARNET DE EXTRANJERIA") {
      return {
        maxLength: 9,
        pattern: "[A-Za-z0-9]*",
        inputMode: "text",
      };
    } else if (tipoDoc === "OTRO") {
      return {
        maxLength: 15,
        pattern: null,
        inputMode: "text",
      };
    }

    // Valor predeterminado
    return {
      maxLength: 8,
      pattern: "[0-9]*",
      inputMode: "numeric",
    };
  }

  // Aplicar restricciones iniciales
  const aplicarRestricciones = () => {
    const tipoDoc = selectTipoDoc.value;
    const config = obtenerConfiguracionDocumento(tipoDoc);

    // Establecer atributos
    inputDocumento.maxLength = config.maxLength;

    if (config.pattern) {
      inputDocumento.pattern = config.pattern;
    } else {
      inputDocumento.removeAttribute("pattern");
    }

    inputDocumento.inputMode = config.inputMode;

    // Si el valor actual excede el nuevo máximo, truncarlo
    if (inputDocumento.value.length > config.maxLength) {
      inputDocumento.value = inputDocumento.value.substring(
        0,
        config.maxLength
      );
    }

    // Actualizar contador si existe
    actualizarContadorDocumento(inputId, tipoDoc);
  };

  // Escuchar cambio en tipo de documento
  selectTipoDoc.addEventListener("change", function () {
    // Limpiar el campo cuando cambia el tipo
    inputDocumento.value = "";
    aplicarRestricciones();

    // Forzar foco en el campo de documento para mejor UX
    setTimeout(() => inputDocumento.focus(), 100);
  });

  // Validar entrada en tiempo real para restringir caracteres no permitidos
  inputDocumento.addEventListener("input", function (e) {
    const tipoDoc = selectTipoDoc.value;
    const config = obtenerConfiguracionDocumento(tipoDoc);

    // Aplicar restricciones según el tipo
    if (tipoDoc === "dni" || tipoDoc === "DNI") {
      // Solo permitir dígitos
      this.value = this.value.replace(/[^0-9]/g, "");
    } else if (
      tipoDoc === "pasaporte" ||
      tipoDoc === "PASAPORTE" ||
      tipoDoc === "carnet" ||
      tipoDoc === "CARNET DE EXTRANJERIA"
    ) {
      // Solo permitir letras y números
      this.value = this.value.replace(/[^A-Za-z0-9]/g, "");
    }

    // Limitar longitud
    if (this.value.length > config.maxLength) {
      this.value = this.value.substring(0, config.maxLength);
    }

    // Actualizar contador si existe
    actualizarContadorDocumento(inputId, tipoDoc);
  });

  // Restringir pegar texto más largo
  inputDocumento.addEventListener("paste", function (e) {
    const tipoDoc = selectTipoDoc.value;
    const config = obtenerConfiguracionDocumento(tipoDoc);

    setTimeout(() => {
      // Aplicar restricciones según el tipo
      if (tipoDoc === "dni" || tipoDoc === "DNI") {
        // Solo permitir dígitos
        this.value = this.value.replace(/[^0-9]/g, "");
      } else if (
        tipoDoc === "pasaporte" ||
        tipoDoc === "PASAPORTE" ||
        tipoDoc === "carnet" ||
        tipoDoc === "CARNET DE EXTRANJERIA"
      ) {
        // Solo permitir letras y números
        this.value = this.value.replace(/[^A-Za-z0-9]/g, "");
      }

      // Limitar longitud
      if (this.value.length > config.maxLength) {
        this.value = this.value.substring(0, config.maxLength);
      }

      // Actualizar contador
      actualizarContadorDocumento(inputId, tipoDoc);
    }, 0);
  });

  // Aplicar restricciones iniciales
  aplicarRestricciones();
}

/**
 * Actualiza el contador de caracteres para un campo de documento
 * @param {string} inputId - ID del campo de entrada
 * @param {string} tipoDoc - Tipo de documento seleccionado
 */
function actualizarContadorDocumento(inputId, tipoDoc) {
  const input = document.getElementById(inputId);
  if (!input) return;

  // Determinar el ID del contador basado en el input
  let contadorId;
  if (inputId === "numero-documento") {
    contadorId = "contador-documento";
  } else if (inputId === "documentoPagador") {
    contadorId = "contador-documento-pagador";
  } else {
    // Para otros campos, construir ID
    contadorId = "contador-" + inputId;
  }

  // Buscar el contador
  const contador = document.getElementById(contadorId);
  if (!contador) return;

  // Determinar longitud máxima según tipo
  let maxLength = 8; // Por defecto para DNI

  if (tipoDoc === "dni" || tipoDoc === "DNI") {
    maxLength = 8;
  } else if (tipoDoc === "pasaporte" || tipoDoc === "PASAPORTE") {
    maxLength = 12;
  } else if (tipoDoc === "carnet" || tipoDoc === "CARNET DE EXTRANJERIA") {
    maxLength = 9;
  } else if (tipoDoc === "otro" || tipoDoc === "OTRO") {
    maxLength = 15;
  }

  // Actualizar texto del contador
  contador.textContent = `${input.value.length}/${maxLength}`;

  // Cambiar color si alcanza el máximo
  if (input.value.length >= maxLength) {
    contador.classList.add("text-success");
    contador.classList.remove("text-muted");
  } else {
    contador.classList.remove("text-success");
    contador.classList.add("text-muted");
  }
}

// Limpiar formulario de reserva
function limpiarFormularioReserva() {
    // Limpiar campos
    document.getElementById("especialidad").selectedIndex = 0;
    document.getElementById("doctor").innerHTML =
        "<option selected disabled>Seleccione Doctor</option>";
    document.getElementById("fecha-reserva").value = "";
    document.getElementById("hora-hidden").value = "";
    document.getElementById("horario-seleccionado").value = "";

    // CORRECCIÓN: Establecer DNI como tipo de documento por defecto
    const tipoDocumento = document.getElementById("tipo-documento");
    if (tipoDocumento) {
        // Buscar y seleccionar la opción "DNI"
        for (let i = 0; i < tipoDocumento.options.length; i++) {
            if (tipoDocumento.options[i].value === "DNI") {
                tipoDocumento.selectedIndex = i;
                console.log("Tipo de documento establecido a DNI");
                break;
            }
        }
        // Si no se encuentra la opción "DNI", seleccionar la primera opción no deshabilitada
        if (
            tipoDocumento.selectedIndex === -1 ||
            (tipoDocumento.options[tipoDocumento.selectedIndex] &&
                tipoDocumento.options[tipoDocumento.selectedIndex].disabled)
        ) {
            for (let i = 0; i < tipoDocumento.options.length; i++) {
                if (!tipoDocumento.options[i].disabled) {
                    tipoDocumento.selectedIndex = i;
                    break;
                }
            }
        }
    }

    document.getElementById("numero-documento").value = "";
    document.getElementById("nombre").value = "";
    document.getElementById("apellido").value = "";
    document.getElementById("idpaciente").value = "";

    // Restablecer ejemplo de días
    document.querySelector(".example-days").innerHTML =
        '<p class="mb-1"><strong>Días de atención:</strong></p><p class="mb-0 ps-3">Seleccione un doctor para ver sus días de atención</p>';

    // Ocultar el botón de registro (si estuviera visible)
    const btnRegistro = document.getElementById("btn-registrar-paciente");
    if (btnRegistro) btnRegistro.classList.add("d-none");

    // CORRECCIÓN PRINCIPAL: NO ocultar el botón de pago, solo deshabilitarlo
    const btnPago = document.getElementById("btn-proceder-pago");
    if (btnPago) {
        btnPago.disabled = true;
        // ELIMINAR: btnPago.classList.add("d-none");
        
        // Cambiar apariencia para que se vea deshabilitado pero visible
        btnPago.classList.remove("btn-success");
        btnPago.classList.add("btn-secondary");
    }

    // Actualizar contador de documento
    const contador = document.getElementById("contador-documento");
    if (contador) contador.textContent = "0/8";
}