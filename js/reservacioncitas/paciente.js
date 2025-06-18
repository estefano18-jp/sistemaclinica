/**
 * sistemaclinica/js/reservacioncitas/paciente.js
 */

// Archivo: js/reservacioncitas/paciente.js

/**
 * Gestión de pacientes
 */

// Buscar paciente por número de documento
async function buscarPacientePorDocumento(numeroDocumento, tipoDocumento) {
    try {
        if (!numeroDocumento) {
            console.warn('Número de documento no proporcionado');
            return null;
        }
        
        if (!tipoDocumento) {
            console.warn('Tipo de documento no proporcionado');
            return null;
        }
        
        mostrarCargando();
        
        // Construir URL para la petición - Incluir TIPO de documento
        const url = `../../../controllers/paciente.controller.php?operacion=buscar_por_documento&nrodoc=${numeroDocumento}&tipodoc=${tipoDocumento}`;
        console.log('URL para buscar paciente:', url);
        
        // Realizar petición al servidor
        const data = await fetchData(url);
        console.log('Respuesta del servidor:', data);
        
        // Procesar resultado
        if (data && data.status && data.paciente) {
            // Verificar que el tipo de documento sea exactamente el mismo
            if (data.paciente.tipodoc === tipoDocumento) {
                // Paciente encontrado con tipo de documento correcto
                console.log('Paciente encontrado con tipo de documento correcto:', data.paciente);
                
                // Actualizar campos del formulario con datos reales
                document.getElementById('nombre').value = data.paciente.nombres || '';
                document.getElementById('apellido').value = data.paciente.apellidos || '';
                document.getElementById('idpaciente').value = data.paciente.idpaciente || '';
                
                // CRÍTICO: Almacenar género y fecha de nacimiento para validaciones
                if (!window.datosPaciente) {
                    window.datosPaciente = {};
                }
                
                window.datosPaciente.genero = data.paciente.genero || '';
                window.datosPaciente.fechaNacimiento = data.paciente.fechanacimiento || '';
                
                // Si no viene la edad calculada, calcularla aquí
                if (data.paciente.edad !== undefined) {
                    window.datosPaciente.edad = parseInt(data.paciente.edad);
                } else if (data.paciente.fechanacimiento) {
                    // Calcular edad
                    const fechaNac = new Date(data.paciente.fechanacimiento);
                    const hoy = new Date();
                    let edad = hoy.getFullYear() - fechaNac.getFullYear();
                    
                    // Ajustar edad si aún no ha cumplido años este año
                    const m = hoy.getMonth() - fechaNac.getMonth();
                    if (m < 0 || (m === 0 && hoy.getDate() < fechaNac.getDate())) {
                        edad--;
                    }
                    
                    window.datosPaciente.edad = edad;
                }
                
                console.log('Datos adicionales del paciente almacenados:', window.datosPaciente);
                
                // NUEVO: Verificar especialidad actual si ya está seleccionada
                const especialidadSelect = document.getElementById('especialidad');
                if (especialidadSelect && especialidadSelect.selectedIndex > 0) {
                    const especialidadOption = especialidadSelect.options[especialidadSelect.selectedIndex];
                    const nombreEspecialidad = especialidadOption.text || '';
                    
                    // Validar especialidad con los datos del paciente
                    const validacionEspecialidad = validarEspecialidadPorPaciente(nombreEspecialidad);
                    
                    if (!validacionEspecialidad.valido) {
                        // Mostrar mensaje de restricción
                        mostrarRestriccionEspecialidad(validacionEspecialidad.mensaje);
                        
                        // CORRECCIÓN CRÍTICA: Deshabilitar botón de pago de forma efectiva
                        const btnPago = document.getElementById('btn-proceder-pago');
                        if (btnPago) {
                            // IMPORTANTE: Agregar atributo 'disabled' para bloquear realmente el botón
                            btnPago.disabled = true;
                            // Sustituir completamente el botón por uno deshabilitado para garantizar que no sea clicable
                            btnPago.setAttribute('onclick', 'event.preventDefault(); return false;');
                            // Cambiar clases para estilo visual
                            btnPago.classList.add('disabled');
                            btnPago.classList.remove('btn-success');
                            btnPago.classList.add('btn-secondary');
                        }
                    } else {
                        // Habilitar botón de pago si cumple requisitos
                        const btnPago = document.getElementById('btn-proceder-pago');
                        if (btnPago) {
                            btnPago.disabled = false;
                            btnPago.removeAttribute('onclick');
                            btnPago.classList.remove('disabled');
                            btnPago.classList.add('btn-success');
                            btnPago.classList.remove('btn-secondary');
                        }
                    }
                }
                
                // Gestionar visibilidad de botones
                const btnRegistro = document.getElementById('btn-registrar-paciente');
                const btnPago = document.getElementById('btn-proceder-pago');
                
                if (btnRegistro) btnRegistro.classList.add('d-none');
                if (btnPago) btnPago.classList.remove('d-none');
                
                // Verificar si podemos habilitar el botón de pago
                verificarHabilitarBotonPago();
                
                // Mostrar alerta pequeña en la esquina
                Toast.fire({
                    icon: 'success',
                    title: 'Paciente encontrado'
                });
                
                return data.paciente;
            } else {
                // El paciente existe pero con un tipo diferente
                console.log('Paciente encontrado pero el tipo no coincide. Enviado:', tipoDocumento, 'Encontrado:', data.paciente.tipodoc);
                
                // Limpiar campos del formulario y datos almacenados
                document.getElementById('nombre').value = '';
                document.getElementById('apellido').value = '';
                document.getElementById('idpaciente').value = '';
                window.datosPaciente = null;
                
                // Mostrar botón de registro y ocultar botón de pago
                const btnRegistro = document.getElementById('btn-registrar-paciente');
                const btnPago = document.getElementById('btn-proceder-pago');
                
                if (btnRegistro) btnRegistro.classList.remove('d-none');
                if (btnPago) {
                    btnPago.classList.add('d-none');
                    btnPago.disabled = true;
                }
                
                // Mostrar alerta explicando el problema
                Swal.fire({
                    icon: 'warning',
                    title: 'Tipo de documento incorrecto',
                    text: `El número ${numeroDocumento} existe en la base de datos pero como ${data.paciente.tipodoc}, no como ${tipoDocumento}.`
                });
                
                return null;
            }
        } else {
            // Paciente no encontrado
            console.log('Paciente no encontrado en la base de datos');
            
            // Limpiar campos del formulario y datos almacenados
            document.getElementById('nombre').value = '';
            document.getElementById('apellido').value = '';
            document.getElementById('idpaciente').value = '';
            window.datosPaciente = null;
            
            // Gestionar visibilidad de botones:
            const btnRegistro = document.getElementById('btn-registrar-paciente');
            const btnPago = document.getElementById('btn-proceder-pago');
            
            if (btnRegistro) btnRegistro.classList.remove('d-none');
            if (btnPago) {
                btnPago.classList.add('d-none');
                btnPago.disabled = true;
            }
            
            // Mostrar alerta pequeña en la esquina
            Toast.fire({
                icon: 'error',
                title: 'Paciente no encontrado'
            });
            
            return null;
        }
    } catch (error) {
        console.error('Error en buscarPacientePorDocumento:', error);
        
        // Limpiar campos del formulario y datos almacenados
        document.getElementById('nombre').value = '';
        document.getElementById('apellido').value = '';
        document.getElementById('idpaciente').value = '';
        window.datosPaciente = null;
        
        // Gestionar visibilidad de botones en caso de error
        const btnRegistro = document.getElementById('btn-registrar-paciente');
        const btnPago = document.getElementById('btn-proceder-pago');
        
        if (btnRegistro) btnRegistro.classList.remove('d-none');
        if (btnPago) {
            btnPago.classList.add('d-none');
            btnPago.disabled = true;
        }
        
        // Mostrar alerta pequeña en la esquina
        Toast.fire({
            icon: 'error',
            title: 'Error al buscar paciente'
        });
        
        return null;
    } finally {
        ocultarCargando();
    }
}

// Redirigir a la página de registro de paciente
function redirigirARegistroPaciente() {
  // Obtener los valores actuales del tipo y número de documento
  const tipoDoc = document.getElementById("tipo-documento").value;
  const nroDoc = document.getElementById("numero-documento").value;

  // Redirigir a la página de registro con los parámetros
  window.location.href = `../../../views/Paciente/RegistrarPaciente/registrarPaciente.php?tipodoc=${tipoDoc}&nrodoc=${nroDoc}`;
}

// Definir configuración para Toast (alertas pequeñas en la esquina)
const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener("mouseenter", Swal.stopTimer);
    toast.addEventListener("mouseleave", Swal.resumeTimer);
  },
});

// Validar número de documento según su tipo
function validarDocumentoPorTipo(input) {
  const tipoDocumento = document.getElementById("tipo-documento").value;
  let valor = input.value.replace(/[^0-9a-zA-Z]/g, ""); // Eliminar caracteres no alfanuméricos
  let maxLength = 20; // Valor por defecto

  switch (tipoDocumento) {
    case "DNI":
      valor = valor.replace(/\D/g, ""); // Solo números
      maxLength = 8;
      break;
    case "PASAPORTE":
      maxLength = 12;
      break;
    case "CARNET DE EXTRANJERIA":
      maxLength = 12;
      break;
    case "OTRO":
      maxLength = 15;
      break;
  }

  // Limitar longitud
  if (valor.length > maxLength) {
    valor = valor.substring(0, maxLength);
  }

  // Actualizar valor del campo
  input.value = valor;

  // Actualizar contador si existe
  const contador = document.getElementById("contador-documento");
  if (contador) {
    contador.textContent = `${valor.length}/${maxLength}`;
  }
}

// Inicializar eventos relacionados con pacientes
document.addEventListener("DOMContentLoaded", function () {
  // Evento para el botón de registrar paciente
  const btnRegistrarPaciente = document.getElementById(
    "btn-registrar-paciente"
  );
  if (btnRegistrarPaciente) {
    console.log("Configurando evento para botón de registrar paciente");
    btnRegistrarPaciente.addEventListener("click", function () {
      redirigirARegistroPaciente();
    });
  }
  // Evento para el botón de buscar paciente
  const btnBuscar = document.getElementById("btn-buscar");
  if (btnBuscar) {
    console.log("Configurando evento para botón de búsqueda");
    btnBuscar.addEventListener("click", function () {
      const tipoDocumento = document.getElementById("tipo-documento").value;
      const numeroDocumento = document.getElementById("numero-documento").value;

      if (!tipoDocumento || !numeroDocumento) {
        Toast.fire({
          icon: "warning",
          title: "Ingrese tipo y número de documento",
        });
        return;
      }

      // Validar longitud según tipo de documento
      let esValido = true;
      let mensajeError = "";

      switch (tipoDocumento) {
        case "DNI":
          if (numeroDocumento.length !== 8) {
            esValido = false;
            mensajeError = "El DNI debe tener 8 dígitos";
          }
          break;
        case "PASAPORTE":
          if (numeroDocumento.length < 6) {
            esValido = false;
            mensajeError = "El pasaporte debe tener al menos 6 caracteres";
          }
          break;
        case "CARNET DE EXTRANJERIA":
          if (numeroDocumento.length < 9) {
            esValido = false;
            mensajeError =
              "El carnet de extranjería debe tener al menos 9 caracteres";
          }
          break;
      }

      if (!esValido) {
        Toast.fire({
          icon: "warning",
          title: mensajeError,
        });
        return;
      }

      console.log(
        "Iniciando búsqueda de paciente con tipo:",
        tipoDocumento,
        "y número:",
        numeroDocumento
      );
      // PASAR AMBOS PARÁMETROS: número y tipo
      buscarPacientePorDocumento(numeroDocumento, tipoDocumento);
    });
  }

  // Evento para el campo de número de documento (buscar al presionar Enter)
  const inputNumeroDoc = document.getElementById("numero-documento");
  if (inputNumeroDoc) {
    console.log("Configurando evento para campo de número de documento");
    inputNumeroDoc.addEventListener("keyup", function (event) {
      if (event.key === "Enter") {
        const tipoDocumento = document.getElementById("tipo-documento").value;
        const numeroDocumento = inputNumeroDoc.value;

        if (!tipoDocumento || !numeroDocumento) {
          Toast.fire({
            icon: "warning",
            title: "Ingrese tipo y número de documento",
          });
          return;
        }

        console.log(
          "Iniciando búsqueda de paciente (Enter) con tipo:",
          tipoDocumento
        );
        // PASAR AMBOS PARÁMETROS: número y tipo
        buscarPacientePorDocumento(numeroDocumento, tipoDocumento);
      }
    });
  }
});
