/**
 * sistemaclinica/js/reservacioncitas/utils.js
 */

/**
 * Utilidades generales para el sistema de citas
 */

// Formatear fecha a formato DD/MM/YYYY
function formatearFecha(fecha) {
    if (!fecha) return '';
    
    try {
        // Si la fecha es un string en formato YYYY-MM-DD
        if (typeof fecha === 'string' && fecha.match(/^\d{4}-\d{2}-\d{2}$/)) {
            const [year, month, day] = fecha.split('-');
            return `${day}/${month}/${year}`;
        }
        
        // Si la fecha ya tiene formato DD/MM/YYYY, devolverla directamente
        if (typeof fecha === 'string' && fecha.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
            return fecha;
        }
        
        // Para cualquier otro formato, intentar crear un objeto Date
        const f = new Date(fecha);
        
        // Verificar si la fecha es válida
        if (isNaN(f.getTime())) {
            console.error('Fecha inválida:', fecha);
            return '';
        }
        
        // Formatear usando toLocaleDateString
        return f.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    } catch (error) {
        console.error('Error al formatear fecha:', error, fecha);
        return '';
    }
}
/**
 * Valida si el paciente puede acceder a la especialidad seleccionada
 * @param {string} nombreEspecialidad Nombre de la especialidad a validar
 * @returns {Object} Objeto con resultado de validación y mensaje
 */
function validarEspecialidadPorPaciente(nombreEspecialidad) {
    // Si no hay datos de paciente cargados, no podemos validar
    if (!window.datosPaciente) {
        console.warn('No hay datos de paciente para validar');
        return { 
            valido: false, 
            mensaje: 'Debe seleccionar un paciente primero'
        };
    }
    
    // Normalizar nombre de especialidad para comparación
    const especialidad = nombreEspecialidad.toLowerCase().trim();
    
    // Validación para Ginecología - Solo mujeres
    if (especialidad.includes('ginecolog')) {
        console.log('Validando especialidad Ginecología. Género del paciente:', window.datosPaciente.genero);
        if (window.datosPaciente.genero !== 'F') {
            return {
                valido: false,
                mensaje: 'La especialidad de Ginecología está disponible solo para pacientes mujeres',
                tipo: 'genero'
            };
        }
    }
    
    // Validación para Pediatría - Solo niños (menores de 15 años)
    if (especialidad.includes('pediatr')) {
        // Calcular edad si solo tenemos fecha de nacimiento
        let edad = window.datosPaciente.edad;
        
        if (!edad && window.datosPaciente.fechaNacimiento) {
            const fechaNac = new Date(window.datosPaciente.fechaNacimiento);
            const hoy = new Date();
            edad = hoy.getFullYear() - fechaNac.getFullYear();
            
            // Ajustar edad si aún no ha cumplido años este año
            const m = hoy.getMonth() - fechaNac.getMonth();
            if (m < 0 || (m === 0 && hoy.getDate() < fechaNac.getDate())) {
                edad--;
            }
        }
        
        console.log('Validando especialidad Pediatría. Edad del paciente:', edad);
        
        // Considerar niños a pacientes menores de 15 años
        if (edad >= 15) {
            return {
                valido: false,
                mensaje: 'La especialidad de Pediatría está disponible solo para pacientes menores de 15 años',
                tipo: 'edad'
            };
        }
    }
    
    // Si pasa todas las validaciones
    return {
        valido: true,
        mensaje: ''
    };
}


/**
 * Muestra mensaje de restricción de especialidad
 * @param {string} mensaje Mensaje a mostrar
 */
function mostrarRestriccionEspecialidad(mensaje) {
    // Crear elemento de alerta si no existe
    let alertaContainer = document.getElementById('alerta-restriccion-especialidad');
    
    if (!alertaContainer) {
        alertaContainer = document.createElement('div');
        alertaContainer.id = 'alerta-restriccion-especialidad';
        alertaContainer.className = 'position-fixed top-0 end-0 p-3';
        alertaContainer.style.zIndex = '9999';
        document.body.appendChild(alertaContainer);
    }
    
    // Crear el contenido HTML de la alerta
    const alertaHTML = `
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white">
                <strong class="me-auto">Restricción de especialidad</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <div class="d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-circle-fill text-danger me-2" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                    </svg>
                    <span>${mensaje}</span>
                </div>
            </div>
        </div>
    `;
    
    alertaContainer.innerHTML = alertaHTML;
    
    // Configurar cierre automático después de 5 segundos
    setTimeout(() => {
        const toastElement = alertaContainer.querySelector('.toast');
        if (toastElement) {
            // Si está usando Bootstrap 5
            if (typeof bootstrap !== 'undefined') {
                const toast = new bootstrap.Toast(toastElement);
                toast.hide();
            } else {
                // Fallback si no hay Bootstrap disponible
                toastElement.classList.remove('show');
                setTimeout(() => {
                    if (alertaContainer.parentNode) {
                        alertaContainer.parentNode.removeChild(alertaContainer);
                    }
                }, 500);
            }
        }
    }, 3000);
}
/**
 * Bloquea el botón de pago de forma efectiva
 * @param {boolean} bloquear Si se debe bloquear o desbloquear el botón
 * @param {string} mensaje Mensaje opcional para mostrar en tooltip
 */
function bloquearBotonPago(bloquear, mensaje = '') {
    const btnPago = document.getElementById('btn-proceder-pago');
    if (!btnPago) return;
    
    console.log(`${bloquear ? 'Bloqueando' : 'Desbloqueando'} botón de pago. Mensaje: ${mensaje}`);
    
    if (bloquear) {
        // Modificar propiedades del botón SIN REEMPLAZARLO
        btnPago.disabled = true;
        btnPago.setAttribute('aria-disabled', 'true');
        btnPago.classList.add('disabled');
        btnPago.classList.remove('btn-success');
        btnPago.classList.add('btn-secondary');
        
        if (mensaje) {
            btnPago.setAttribute('title', mensaje);
            btnPago.setAttribute('data-bs-toggle', 'tooltip');
            btnPago.setAttribute('data-bs-placement', 'top');
        }
    } else {
        // Restaurar propiedades del botón SIN REEMPLAZARLO
        btnPago.disabled = false;
        btnPago.removeAttribute('aria-disabled');
        btnPago.classList.remove('disabled');
        btnPago.classList.remove('btn-secondary');
        btnPago.classList.add('btn-success');
        btnPago.removeAttribute('title');
        btnPago.removeAttribute('data-bs-toggle');
        btnPago.removeAttribute('data-bs-placement');
    }
}

// Formatear hora a formato HH:MM AM/PM
function formatearHora(hora) {
    if (!hora) return '';
    
    // Si solo viene la hora (HH:MM:SS)
    if (hora.length <= 8) {
        const [h, m] = hora.split(':');
        const fecha = new Date();
        fecha.setHours(h);
        fecha.setMinutes(m);
        
        return fecha.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }
    
    // Si es fecha completa (YYYY-MM-DD HH:MM:SS)
    const fecha = new Date(hora);
    return fecha.toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

// Mostrar mensaje de carga
function mostrarCargandoCalendario(mensaje = 'Cargando citas...') {
    console.log('Mostrando carga específica del calendario');
    
    // Buscar si ya existe el overlay del calendario
    let overlayCalendario = document.getElementById('calendar-loading-overlay');
    
    if (!overlayCalendario) {
        // Crear overlay específico para el calendario
        overlayCalendario = document.createElement('div');
        overlayCalendario.id = 'calendar-loading-overlay';
        overlayCalendario.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            border-radius: 8px;
        `;
        
        overlayCalendario.innerHTML = `
            <div style="text-align: center; color: #0d6efd;">
                <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <div style="margin-top: 10px; font-size: 14px; font-weight: 500;">${mensaje}</div>
            </div>
        `;
        
        // Agregar al contenedor del calendario
        const calendarioContainer = document.querySelector('.calendar-card .card-body');
        if (calendarioContainer) {
            calendarioContainer.style.position = 'relative';
            calendarioContainer.appendChild(overlayCalendario);
        }
    } else {
        // Si ya existe, actualizar el mensaje y mostrarlo
        const mensajeElement = overlayCalendario.querySelector('div > div:last-child');
        if (mensajeElement) {
            mensajeElement.textContent = mensaje;
        }
        overlayCalendario.style.display = 'flex';
    }
}

// Ocultar mensaje de carga
function ocultarCargandoCalendario() {
    console.log('Ocultando carga específica del calendario');
    
    const overlayCalendario = document.getElementById('calendar-loading-overlay');
    if (overlayCalendario) {
        overlayCalendario.style.display = 'none';
    }
}

// Mostrar mensaje de carga GLOBAL (solo para operaciones no relacionadas con calendario)
function mostrarCargando(mensaje = 'Cargando...') {
    // Solo mostrar si NO es una operación del calendario
    if (mensaje.toLowerCase().includes('cita') || mensaje.toLowerCase().includes('calendario')) {
        console.warn('Usando mostrarCargando para operación de calendario. Use mostrarCargandoCalendario en su lugar.');
        mostrarCargandoCalendario(mensaje);
        return;
    }
    
    const overlay = document.getElementById('loading-overlay');
    if (overlay) {
        overlay.style.display = 'flex';
    }
}
function ocultarCargando() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

/**
 * Cierra completamente un modal asegurando que se eliminen todos los residuos
 * @param {string} modalId - ID del modal a cerrar
 */
/**
 * Cierra completamente un modal sin dejar rastros de backdrop o clases
 * @param {string} modalId - ID del modal a cerrar
 */
function cerrarModalCompletamente(modalId) {
    console.log("Cerrando modal completamente:", modalId);
    
    // Obtener elemento del modal
    const modalElement = document.getElementById(modalId);
    if (!modalElement) {
      console.warn("Modal no encontrado:", modalId);
      return;
    }
    
    try {
      // Intentar usar el método de Bootstrap primero
      const modalInstance = bootstrap.Modal.getInstance(modalElement);
      if (modalInstance) {
        modalInstance.hide();
      }
    } catch (e) {
      console.warn("Error al usar hide de Bootstrap:", e);
    }
    
    // Limpieza manual como respaldo
    setTimeout(() => {
      try {
        // Ocultar el modal
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        modalElement.setAttribute('aria-hidden', 'true');
        modalElement.removeAttribute('aria-modal');
        modalElement.removeAttribute('role');
        
        // Eliminar backdrop
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 0) {
          backdrops.forEach(backdrop => backdrop.remove());
        }
        
        // Resetear clases del body
        if (!document.querySelector('.modal.show')) {
          document.body.classList.remove('modal-open');
          document.body.style.overflow = '';
          document.body.style.paddingRight = '';
        }
        
        // NUEVO: Recargar solo el calendario
        if (typeof recargarSoloCalendario === "function") {
            recargarSoloCalendario(true);
        } else {
            // Fallback si la función no está disponible
            if (window.calendario) {
                window.calendario.refetchEvents();
            }
        }
        
        // NUEVO: Asegurar visibilidad del botón de pago
        const btnPago = document.getElementById("btn-proceder-pago");
        if (btnPago) {
            console.log("Asegurando visibilidad del botón de pago");
            btnPago.classList.remove("d-none");
            btnPago.disabled = true;
            btnPago.classList.remove("btn-success");
            btnPago.classList.add("btn-secondary");
        }
      } catch (e) {
        console.error("Error durante limpieza manual del modal:", e);
      }
    }, 50);
    
    // Verificación final de seguridad - eliminar todos los backdrops si no hay modales abiertos
    setTimeout(() => {
      if (!document.querySelector('.modal.show')) {
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
      }
    }, 300);
}
// Realizar petición fetch con manejo de carga y errores mejorado
async function fetchData(url, options = {}) {
    try {
        console.log(`Haciendo petición a: ${url}`);
        
        // IMPORTANTE: NO mostrar carga global para peticiones del calendario
        const esOperacionCalendario = url.includes('cita.controller') || 
                                     url.includes('calendario') || 
                                     url.includes('horario');
        
        // Timeout para la petición después de 15 segundos
        const controller = new AbortController();
        const signal = controller.signal;
        const timeout = setTimeout(() => controller.abort(), 15000);
        
        // Agregar signal a las opciones
        const fetchOptions = { ...options, signal };
        
        const response = await fetch(url, fetchOptions);
        clearTimeout(timeout);
        
        // Si la respuesta no es satisfactoria
        if (!response.ok) {
            throw new Error(`Error de servidor: ${response.status} ${response.statusText}`);
        }
        
        // Intentar parsear como JSON
        try {
            const data = await response.json();
            console.log(`Respuesta recibida de ${url}:`, data);
            return data;
        } catch (parseError) {
            console.error('Error al parsear JSON:', parseError);
            
            // Obtener el texto de la respuesta para depuración
            const text = await response.text();
            console.log('Respuesta como texto:', text);
            
            // Intentar extraer mensaje de error si existe
            let errorMessage = 'La respuesta no es un JSON válido';
            try {
                // A veces PHP devuelve errores con HTML
                if (text.includes('Fatal error') || text.includes('Parse error')) {
                    errorMessage = 'Error en el servidor: ' + text.split('<b>')[1].split('</b>')[0];
                }
            } catch (e) {}
            
            throw new Error(errorMessage);
        }
    } catch (error) {
        // Mensaje específico para errores de timeout
        if (error.name === 'AbortError') {
            console.error('Timeout en la petición a:', url);
            return { 
                status: false, 
                mensaje: 'Tiempo de espera agotado. La conexión con el servidor está tardando demasiado.'
            };
        }
        
        // Mensaje para errores de red
        console.error(`Error en fetchData (${url}):`, error);
        return { 
            status: false, 
            mensaje: 'Error de conexión: ' + error.message
        };
    }
}

// Validar si un campo está vacío
function campoVacio(valor) {
    return valor === undefined || valor === null || valor.trim() === '';
}

// Validar campos requeridos
function validarCamposRequeridos(campos) {
    for (const campo of campos) {
        if (campoVacio(campo.valor)) {
            return {
                valido: false,
                campo: campo.nombre
            };
        }
    }
    return {
        valido: true
    };
}

// Función para obtener el precio total del comprobante
function obtenerPrecioTotalComprobante() {
    // Estrategia 1: Obtener del elemento 'precioTotal'
    const precioTotalElement = document.getElementById('precioTotal');
    if (precioTotalElement) {
        const precioTexto = precioTotalElement.textContent || precioTotalElement.innerText;
        // Extraer números y puntos decimales, convertir comas a puntos
        const matches = precioTexto.match(/[\d,.]+/);
        if (matches && matches.length > 0) {
            const precioLimpio = matches[0].replace(/[^\d,.]/g, '').replace(',', '.');
            const precio = parseFloat(precioLimpio);
            if (!isNaN(precio)) {
                console.log(`Precio obtenido de 'precioTotal': ${precio}`);
                return precio;
            }
        }
    }

    // Estrategia 2: Obtener del elemento 'precioConsulta'
    const precioConsultaElement = document.getElementById('precioConsulta');
    if (precioConsultaElement) {
        const precioTexto = precioConsultaElement.textContent || precioConsultaElement.innerText;
        const matches = precioTexto.match(/[\d,.]+/);
        if (matches && matches.length > 0) {
            const precioLimpio = matches[0].replace(/[^\d,.]/g, '').replace(',', '.');
            const precio = parseFloat(precioLimpio);
            if (!isNaN(precio)) {
                console.log(`Precio obtenido de 'precioConsulta': ${precio}`);
                return precio;
            }
        }
    }

    // Estrategia 3: Obtener del dataset de especialidad seleccionada
    const especialidadSelect = document.getElementById('especialidad');
    if (especialidadSelect && especialidadSelect.selectedIndex >= 0) {
        const selectedOption = especialidadSelect.options[especialidadSelect.selectedIndex];
        if (selectedOption && selectedOption.dataset && selectedOption.dataset.precio) {
            const precio = parseFloat(selectedOption.dataset.precio);
            if (!isNaN(precio)) {
                console.log(`Precio obtenido de 'dataset.precio': ${precio}`);
                return precio;
            }
        }
    }

    // Si todo falla, devolver 0 para evitar errores
    console.warn("No se pudo obtener el precio, devolviendo 0 como valor seguro");
    return 0;
}

// Validar formato de RUC peruano
function validarRUC(ruc) {
    if (!ruc) return false;
    
    // RUC debe tener 11 dígitos
    if (!/^\d{11}$/.test(ruc)) return false;
    
    // Los dos primeros dígitos deben ser 10, 15, 17 o 20
    const dosPrimeros = parseInt(ruc.substring(0, 2));
    if (![10, 15, 17, 20].includes(dosPrimeros)) return false;
    
    return true;
}

// Validar formato de DNI peruano
function validarDNI(dni) {
    return /^\d{8}$/.test(dni);
}

// Limitar caracteres a solo números
function soloNumeros(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
}

// Limitar caracteres a solo letras y espacios
function soloLetras(input) {
    input.value = input.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
}

// Convertir primera letra de cada palabra a mayúscula
function capitalizarPalabras(texto) {
    return texto.replace(/\b\w/g, l => l.toUpperCase());
}

// Agregar evento a múltiples elementos
function agregarEventoMultiple(elementos, evento, callback) {
    elementos.forEach(elemento => {
        elemento.addEventListener(evento, callback);
    });
}

// Formatear monto a formato de moneda
function formatearMoneda(monto, moneda = 'PEN') {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: moneda,
        minimumFractionDigits: 2
    }).format(monto);
}