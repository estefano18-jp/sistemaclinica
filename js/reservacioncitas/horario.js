/**
 * sistemaclinica/js/reservacioncitas/horario.js
 */

// Archivo: js/reservacioncitas/horario.js

/**
 * Gestión de horarios disponibles
 */

// Obtener horarios disponibles para un doctor en una fecha específica
async function obtenerHorariosDisponibles(idDoctor, fecha, idEspecialidad) {
    try {
        if (!idDoctor || !fecha || !idEspecialidad) {
            return [];
        }
        
        const data = await fetchData(`../../../controllers/cita.controller.php?op=horas_disponibles&iddoctor=${idDoctor}&fecha=${fecha}&idespecialidad=${idEspecialidad}`);
        
        if (data.status && data.data) {
            return data.data;
        }
        
        return [];
    } catch (error) {
        console.error('Error en obtenerHorariosDisponibles:', error);
        return [];
    }
}

// Mostrar modal de selección de horarios
async function mostrarModalHorarios(idDoctor, fecha, idEspecialidad) {
    try {
        // Validar parámetros
        if (!idDoctor || !fecha || !idEspecialidad) {
            Swal.fire({
                icon: 'warning', 
                title: 'Datos incompletos',
                text: 'Por favor, seleccione doctor, especialidad y fecha.'
            });
            return;
        }
        
        console.log("Iniciando mostrarModalHorarios con corrección para aria-hidden...");
        
        // Obtener los horarios disponibles
        mostrarCargando();
        const horarios = await obtenerHorariosDisponibles(idDoctor, fecha, idEspecialidad);
        ocultarCargando();
        
        // Obtener y preparar el modal
        const modalElement = document.getElementById('modalHorarios');
        
        // ELIMINAR ARIA-HIDDEN DE MANERA AGRESIVA
        modalElement.removeAttribute('aria-hidden');
        modalElement.querySelectorAll('[aria-hidden]').forEach(el => {
            el.removeAttribute('aria-hidden');
        });
        
        // REDEFINIR COMPLETAMENTE EL MODAL PARA EVITAR PROBLEMAS DE BOOTSTRAP
        if (typeof jQuery !== 'undefined') {
            // Destruir cualquier instancia previa del modal
            $(modalElement).modal('dispose');
            
            // Eliminar cualquier listener anterior
            $(modalElement).off('.bs.modal');
            $(document).off('.bs.modal');
        }
        
        // Obtener el contenedor de horarios
        const contenedorHorarios = modalElement.querySelector('.btn-group-time');
        contenedorHorarios.innerHTML = '';
        
        // Variable global para rastrear el horario seleccionado
        window.horarioSeleccionado = null;
        
        // Verificar si hay horarios disponibles
        if (horarios.length === 0) {
            contenedorHorarios.style.display = 'flex';
            contenedorHorarios.style.justifyContent = 'center';
            contenedorHorarios.style.alignItems = 'center';
            contenedorHorarios.style.minHeight = '120px';
            
            contenedorHorarios.innerHTML = '<div style="background-color: #d1ecf1; color: #000; padding: 12px 15px; border-radius: 3px; font-size: 14px; white-space: nowrap;">No hay horarios disponibles para este día.</div>';
            
            modalElement.querySelector('#btn-confirmar-horario').disabled = true;
        } else {
            // Restaurar estilos originales
            contenedorHorarios.style.display = '';
            contenedorHorarios.style.justifyContent = '';
            contenedorHorarios.style.alignItems = '';
            contenedorHorarios.style.minHeight = '';
            
            // NUEVO: Filtrar horarios pasados si la fecha es hoy
            let horariosDisponibles = horarios;
            const hoy = new Date().toISOString().split('T')[0];
            
            if (fecha === hoy) {
                const horaActual = new Date();
                console.log(`Filtrando horarios pasados. Hora actual: ${horaActual.toTimeString()}`);
                
                horariosDisponibles = horarios.filter(horario => {
                    // Obtener la hora del horario en formato HH:MM:SS
                    const [horas, minutos, segundos] = horario.hora.split(':').map(Number);
                    
                    // Crear una fecha con la hora del horario para comparar
                    const horaHorario = new Date();
                    horaHorario.setHours(horas, minutos, segundos);
                    
                    // Verificar si la hora del horario es posterior a la hora actual
                    return horaHorario > horaActual;
                });
                
                console.log(`Horarios originales: ${horarios.length}, Horarios filtrados: ${horariosDisponibles.length}`);
            }
            
            // Crear botones para cada horario disponible
            if (horariosDisponibles.length === 0) {
                contenedorHorarios.style.display = 'flex';
                contenedorHorarios.style.justifyContent = 'center';
                contenedorHorarios.style.alignItems = 'center';
                contenedorHorarios.style.minHeight = '120px';
                
                contenedorHorarios.innerHTML = '<div style="background-color: #d1ecf1; color: #000; padding: 12px 15px; border-radius: 3px; font-size: 14px; white-space: nowrap;">No hay horarios disponibles para este día.</div>';
                
                modalElement.querySelector('#btn-confirmar-horario').disabled = true;
            } else {
                horariosDisponibles.forEach(horario => {
                    if (horario.disponible) {
                        const boton = document.createElement('button');
                        boton.type = 'button';
                        boton.className = 'btn btn-outline-primary hora-btn';
                        boton.textContent = formatearHora(horario.hora);
                        boton.dataset.hora = horario.hora;
                        
                        // Usar onclick en lugar de addEventListener para mayor compatibilidad
                        boton.onclick = function() {
                            // Quitar selección previa
                            document.querySelectorAll('.hora-btn.active').forEach(btn => {
                                btn.classList.remove('active');
                            });
                            
                            // Marcar como seleccionado
                            this.classList.add('active');
                            
                            // Guardar referencia global
                            window.horarioSeleccionado = {
                                hora: this.dataset.hora,
                                texto: this.textContent
                            };
                            
                            console.log('Horario seleccionado:', window.horarioSeleccionado);
                        };
                        
                        contenedorHorarios.appendChild(boton);
                    }
                });
                
                // Habilitar botón de confirmación
                modalElement.querySelector('#btn-confirmar-horario').disabled = false;
            }
        }
        
        // CONFIGURAR BOTÓN DE CONFIRMACIÓN PARA EVITAR PROBLEMAS DE FOCO
        const btnConfirmar = modalElement.querySelector('#btn-confirmar-horario');
        btnConfirmar.onclick = function(event) {
            console.log("Click en botón confirmar horario");
            
            // PREVENIMOS QUE SE ENFOQUE (CAUSA DEL PROBLEMA)
            if (event) event.preventDefault();
            
            if (!document.querySelector('.hora-btn.active') && !window.horarioSeleccionado) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selección de horario',
                    text: 'Por favor, seleccione un horario disponible.'
                });
                return;
            }
            
            // CERRAR MODAL ANTES DE LLAMAR A confirmarSeleccionHorario
            // Esto evita el problema de foco que causa la advertencia
            if (typeof jQuery !== 'undefined') {
                $(modalElement).modal('hide');
                setTimeout(() => {
                    confirmarSeleccionHorario();
                }, 300);
            } else {
                const bsModal = bootstrap.Modal.getInstance(modalElement);
                if (bsModal) {
                    bsModal.hide();
                    setTimeout(() => {
                        confirmarSeleccionHorario();
                    }, 300);
                } else {
                    confirmarSeleccionHorario();
                }
            }
        };
        
        // SOLUCIÓN EXTREMA: INTERCEPTAR MUTACIONES DOM PARA ELIMINAR ARIA-HIDDEN
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && 
                    mutation.attributeName === 'aria-hidden') {
                    mutation.target.removeAttribute('aria-hidden');
                }
            });
        });
        
        // Observar cambios en el modal y todos sus hijos
        observer.observe(modalElement, {
            attributes: true,
            attributeFilter: ['aria-hidden'],
            subtree: true
        });
        
        // MOSTRAR MODAL (MÉTODO ULTRASEGURO)
        if (typeof jQuery !== 'undefined') {
            // Usar jQuery para mayor compatibilidad
            $(modalElement).modal({
                backdrop: 'static',
                keyboard: false,
                focus: false // Clave: evitar que Bootstrap intente enfocar elementos
            });
            
            // Eliminar aria-hidden inmediatamente cuando el modal aparezca
            $(modalElement).on('shown.bs.modal', function() {
                this.removeAttribute('aria-hidden');
                $(this).find('[aria-hidden]').removeAttr('aria-hidden');
            });
            
            $(modalElement).modal('show');
        } else {
            // Usar API nativa de Bootstrap como respaldo
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: false,
                focus: false
            });
            
            // Eliminar atributos antes de mostrar
            modalElement.removeAttribute('aria-hidden');
            modal.show();
        }
        
        // SOLUCIÓN FINAL: TIMER PARA ELIMINAR CONTINUAMENTE ARIA-HIDDEN
        const limpiadorAriaHidden = setInterval(() => {
            // Verificar si el modal sigue visible
            if (!modalElement.classList.contains('show')) {
                clearInterval(limpiadorAriaHidden);
                observer.disconnect();
                return;
            }
            
            // Eliminar aria-hidden agresivamente
            if (modalElement.hasAttribute('aria-hidden')) {
                modalElement.removeAttribute('aria-hidden');
            }
            
            modalElement.querySelectorAll('[aria-hidden]').forEach(el => {
                el.removeAttribute('aria-hidden');
            });
        }, 20); // Revisar cada 20ms
    } catch (error) {
        console.error('Error en mostrarModalHorarios:', error);
        ocultarCargando();
        
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudieron cargar los horarios disponibles.'
        });
    }
}
// Confirmar selección de horario
function confirmarSeleccionHorario() {
    console.log("Intentando confirmar selección de horario...");
    
    // Verificar si hay un horario seleccionado usando la variable global
    if (!window.horarioSeleccionado) {
        console.log("No hay horario seleccionado (verificando botón con clase active)");
        
        // Intentar obtener el horario seleccionado a partir del botón con clase active
        const horarioSeleccionado = document.querySelector('.hora-btn.active');
        
        if (!horarioSeleccionado) {
            console.log("No se encontró ningún botón seleccionado");
            
            Swal.fire({
                icon: 'warning',
                title: 'Selección de horario',
                text: 'Por favor, seleccione un horario disponible.'
            });
            return;
        }
        
        // Si encontramos un horario seleccionado, guardamos sus datos
        window.horarioSeleccionado = {
            hora: horarioSeleccionado.dataset.hora,
            texto: horarioSeleccionado.textContent
        };
        
        console.log("Horario seleccionado encontrado por clase active:", window.horarioSeleccionado);
    }
    
    // Mostrar la hora seleccionada en el campo correspondiente
    document.getElementById('horario-seleccionado').value = window.horarioSeleccionado.texto;
    document.getElementById('hora-hidden').value = window.horarioSeleccionado.hora;
    
    // Activar el botón de proceder con el pago
    const btnProcederPago = document.getElementById('btn-proceder-pago');
    if (btnProcederPago) {
        btnProcederPago.classList.remove('d-none');
    }
    
    // Cerrar el modal
    const modalHorarios = bootstrap.Modal.getInstance(document.getElementById('modalHorarios'));
    if (modalHorarios) {
        modalHorarios.hide();
    } else {
        console.error("No se encontró instancia del modal de horarios");
    }
    
    // Verificar habilitación del botón de pago
    if (typeof verificarHabilitarBotonPago === 'function') {
        setTimeout(verificarHabilitarBotonPago, 500);
    }
    
    console.log("Selección de horario confirmada correctamente:", window.horarioSeleccionado);
}