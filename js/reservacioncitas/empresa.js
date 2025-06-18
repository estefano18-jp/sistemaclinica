/**
 * sistemaclinica/js/reservacioncitas/empresa.js
 */

/**
 * Gestión de empresas
 */

// Buscar empresa por RUC
async function buscarEmpresaPorRuc(ruc) {
    try {
        if (!ruc) {
            return null;
        }
        
        // Validar formato de RUC
        if (!validarRUC(ruc)) {
            Swal.fire({
                icon: 'warning',
                title: 'RUC inválido',
                text: 'El RUC debe tener 11 dígitos y comenzar con 10, 15, 17 o 20.'
            });
            return null;
        }
        
        // Registrar petición para depuración
        console.log(`Buscando empresa con RUC: ${ruc}`);
        
        mostrarCargando();
        const response = await fetchData(`../../../controllers/empresa.controller.php?op=buscar&ruc=${ruc}`);
        ocultarCargando();
        
        // Verificar que la respuesta sea válida antes de continuar
        if (response && response.status === true && response.data) {
            // Validar que todos los campos esperados existan para evitar errores
            const empresaValidada = {
                idempresa: response.data.idempresa || '',
                razonsocial: typeof response.data.razonsocial === 'string' ? response.data.razonsocial : '',
                ruc: typeof response.data.ruc === 'string' ? response.data.ruc : '',
                direccion: typeof response.data.direccion === 'string' ? response.data.direccion : '',
                nombrecomercial: typeof response.data.nombrecomercial === 'string' ? response.data.nombrecomercial : '',
                telefono: typeof response.data.telefono === 'string' ? response.data.telefono : '',
                email: typeof response.data.email === 'string' ? response.data.email : ''
            };
            
            console.log("Empresa encontrada y validada:", empresaValidada);
            return empresaValidada;
        }
        
        console.log("No se encontró empresa con RUC:", ruc);
        return null;
    } catch (error) {
        ocultarCargando();
        console.error("Error en buscarEmpresaPorRuc:", error);
        return null;
    }
}

// Registrar nueva empresa
async function registrarEmpresa(datos) {
    try {
        // Validar datos mínimos obligatorios
        if (!datos.ruc || !datos.razonsocial) {
            Swal.fire({
                icon: 'warning',
                title: 'Datos incompletos',
                text: 'Por favor, complete al menos RUC y Razón Social.'
            });
            return {
                status: false,
                mensaje: 'Faltan datos obligatorios: RUC y Razón Social'
            };
        }
        
        // Validar formato de RUC
        if (!validarRUC(datos.ruc)) {
            Swal.fire({
                icon: 'warning',
                title: 'RUC inválido',
                text: 'El RUC debe tener 11 dígitos y comenzar con 10, 15, 17 o 20.'
            });
            return {
                status: false,
                mensaje: 'Formato de RUC inválido'
            };
        }
        
        console.log("Enviando datos para registro de empresa:", datos);
        
        const formData = new FormData();
        
        // Agregar solo los campos proporcionados
        for (const key in datos) {
            // Solo agregar si el valor no es undefined, null o string vacío
            if (datos[key] !== undefined && datos[key] !== null && datos[key] !== '') {
                formData.append(key, datos[key]);
                console.log(`Agregando campo ${key}:`, datos[key]);
            }
        }
        
        // Enviar datos al servidor
        mostrarCargando();
        const response = await fetchData('../../../controllers/empresa.controller.php?op=registrar', {
            method: 'POST',
            body: formData
        });
        ocultarCargando();
        
        // MODIFICACIÓN CLAVE: Manejar el caso de empresa ya existente como un éxito
        if (response.status === false && response.mensaje && response.mensaje.includes("ya está registrada") && response.idempresa) {
            console.log("Empresa ya registrada, utilizando ID existente:", response.idempresa);
            
            // Convertir a un formato de "éxito" para el resto del flujo
            return {
                status: true,
                mensaje: 'Empresa existente encontrada',
                idempresa: response.idempresa
            };
        }
        
        if (response.status) {
            console.log("Empresa registrada exitosamente:", response);
            
            // Solo mostrar mensaje si no estamos en el flujo de pago
            if (!datos._silentMode) {
                Swal.fire({
                    icon: 'success',
                    title: 'Empresa registrada',
                    text: 'La empresa se ha registrado correctamente.'
                });
            }
        } else {
            console.error("Error al registrar empresa:", response);
            
            if (!datos._silentMode) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.mensaje || 'No se pudo registrar la empresa.'
                });
            }
        }
        
        return response;
    } catch (error) {
        ocultarCargando();
        console.error('Error en registrarEmpresa:', error);
        
        if (!datos._silentMode) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo registrar la empresa. Error: ' + error.message
            });
        }
        
        return {
            status: false,
            mensaje: 'Error al registrar empresa: ' + error.message
        };
    }
}
/**
 * Actualiza los datos de una empresa existente
 * @param {Object} datos Datos de la empresa a actualizar
 * @returns {Promise<Object>} Resultado de la operación
 */
async function actualizarEmpresa(datos) {
    try {
        // Validar datos mínimos obligatorios
        if (!datos.idempresa) {
            console.error('Falta ID de empresa para actualizar');
            return {
                status: false,
                mensaje: 'Falta ID de empresa para actualizar'
            };
        }
        
        console.log("Actualizando empresa con datos:", datos);
        
        const formData = new FormData();
        
        // Agregar los campos a actualizar
        for (const key in datos) {
            // Excluir propiedades de control como _silentMode
            if (datos[key] !== undefined && datos[key] !== null && key !== '_silentMode') {
                formData.append(key, datos[key]);
                console.log(`Agregando campo ${key}:`, datos[key]);
            }
        }
        
        // Enviar datos al servidor
        mostrarCargando();
        const response = await fetchData('/sistemaclinica/controllers/empresa.controller.php?op=actualizar', {
            method: 'POST',
            body: formData
        });
        ocultarCargando();
        
        if (response.status) {
            console.log("Empresa actualizada exitosamente:", response);
            
            // Solo mostrar mensaje si no estamos en el flujo de pago
            if (!datos._silentMode) {
                Swal.fire({
                    icon: 'success',
                    title: 'Empresa actualizada',
                    text: 'Los datos de la empresa han sido actualizados correctamente.'
                });
            }
        } else {
            console.error("Error al actualizar empresa:", response);
            
            if (!datos._silentMode) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.mensaje || 'No se pudo actualizar la empresa.'
                });
            }
        }
        
        return response;
    } catch (error) {
        ocultarCargando();
        console.error('Error en actualizarEmpresa:', error);
        
        if (!datos._silentMode) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo actualizar la empresa. Error: ' + error.message
            });
        }
        
        return {
            status: false,
            mensaje: 'Error al actualizar empresa: ' + error.message
        };
    }
}