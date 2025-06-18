/**
 * sistemaclinica/js/reservacioncitas/especialidad.js
 */

/**
 * Gestión de especialidades médicas
 */

// Cargar lista de especialidades - CORREGIDO
async function cargarEspecialidades(selectId) {
  try {
    const select = document.getElementById(selectId);
    if (!select) return;

    // Mostrar indicador de carga
    mostrarCargando();

    // Limpiar opciones actuales
    select.innerHTML =
      "<option selected disabled>Seleccione Especialidad</option>";

    // Añadir opción "Todas las especialidades" SOLO para filtros
    if (selectId.includes("-top")) {
      const optionTodas = document.createElement("option");
      optionTodas.value = "0";
      optionTodas.textContent = "Todas las especialidades";
      select.appendChild(optionTodas);
    }

    // Usar ruta absoluta y mejorar manejo de errores
    const data = await fetchData(
      "/sistemaclinica/controllers/especialidad.controller.php?op=listar"
    );

    if (data.status && data.data && Array.isArray(data.data)) {
      // Agregar opciones al select
      data.data.forEach((especialidad) => {
        // Verificar y validar el precio antes de asignarlo
        const precioAtencion = parseFloat(especialidad.precioatencion);

        const option = document.createElement("option");
        option.value = especialidad.idespecialidad;
        option.textContent = especialidad.especialidad;

        // IMPORTANTE: Guardar el nombre original de la especialidad para validación
        option.dataset.nombreOriginal = especialidad.especialidad;

        // Asignar el precio como atributo data y asegurar que sea numérico
        option.dataset.precio = isNaN(precioAtencion)
          ? "0.00"
          : precioAtencion.toFixed(2);

        // También guardar el precio como una propiedad para mayor seguridad
        option.setAttribute(
          "data-precio",
          isNaN(precioAtencion) ? "0.00" : precioAtencion.toFixed(2)
        );

        // Loguear para depuración
        console.log(
          `Especialidad: ${especialidad.especialidad}, Precio: ${option.dataset.precio}`
        );

        select.appendChild(option);
      });
      console.log(`Especialidades cargadas: ${data.data.length}`);
    } else {
      console.error(
        "Error al cargar especialidades:",
        data.mensaje || "No se recibieron datos correctos"
      );
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudieron cargar las especialidades. Por favor, intente nuevamente.",
      });
    }
  } catch (error) {
    console.error("Error en cargarEspecialidades:", error);
    Swal.fire({
      icon: "error",
      title: "Error de conexión",
      text: "No se pudo conectar con el servidor. Verifique su conexión a internet.",
    });
  } finally {
    ocultarCargando();
  }
}

// Obtener precio de atención por ID de especialidad
async function obtenerPrecioEspecialidad(idEspecialidad) {
  try {
    // CORRECCIÓN: Mejor manejo de errores y validación
    if (!idEspecialidad || idEspecialidad <= 0) {
      console.warn("ID de especialidad inválido:", idEspecialidad);
      return 0;
    }

    const data = await fetchData(
      `../../../controllers/especialidad.controller.php?op=obtener&id=${idEspecialidad}`
    );

    if (data.status && data.data && data.data.precioatencion) {
      // CORRECCIÓN: Validar que sea un número
      const precio = parseFloat(data.data.precioatencion);
      return isNaN(precio) ? 0 : precio;
    }

    console.warn("No se pudo obtener el precio. Usando valor predeterminado 0");
    return 0;
  } catch (error) {
    console.error("Error en obtenerPrecioEspecialidad:", error);
    return 0;
  }
}

// Obtener duración de consulta según especialidad (en minutos)
function obtenerDuracionEspecialidad(idEspecialidad) {
  const duraciones = {
    1: 20, // Medicina General / Familiar
    2: 20, // Cardiología
    3: 30, // Neurología
    4: 20, // Endocrinología
    5: 20, // Pediatría
    6: 20, // Ginecología / Obstetricia
    7: 20, // Dermatología
    8: 45, // Psiquiatría
    9: 45, // Psicología
    10: 20, // Traumatología / Ortopedia
    11: 20, // Oftalmología
    12: 30, // Odontología
  };

  return duraciones[idEspecialidad] || 20; // 20 minutos por defecto
}
