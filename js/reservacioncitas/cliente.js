/**
 * sistemaclinica/js/reservacioncitas/cliente.js
 */

/**
 * Busca un cliente por documento
 * @param {string} tipoDoc Tipo de documento
 * @param {string} nroDoc Número de documento
 * @returns {Promise} Objeto con datos del cliente o null
 */
async function buscarClientePorDocumento(tipoDoc, nroDoc) {
  try {
    if (!tipoDoc || !nroDoc) {
      console.warn("Búsqueda de cliente sin tipo o número de documento");
      return null;
    }

    // Normalizar el tipo de documento para asegurar el formato correcto
    const tipoDocNormalizado = normalizarTipoDocumento(tipoDoc);

    // Validar cantidad exacta de dígitos según tipo de documento
    // (Esto es validación redundante, ya se validó en la interfaz pero es buena práctica)
    if (!validarDocumentoPorTipo(tipoDocNormalizado, nroDoc)) {
      console.warn(`Documento inválido: ${tipoDoc} - ${nroDoc}`);
      return null;
    }

    mostrarCargando();

    // Construir URL para la petición usando ruta absoluta
    const url = `/sistemaclinica/controllers/cliente.controller.php?op=buscar&tipodoc=${encodeURIComponent(tipoDocNormalizado)}&nrodoc=${encodeURIComponent(nroDoc)}`;
    console.log("URL para buscar cliente:", url);

    // Realizar petición al servidor
    const data = await fetchData(url);
    console.log("Respuesta del servidor buscar cliente:", data);

    if (data && data.status && data.data) {
      return data.data;
    }

    // Si no encontramos al cliente directamente, intentamos buscar como persona/paciente
    // Esto es útil porque podría existir como paciente pero no como cliente
    console.log("Cliente no encontrado directamente, verificando como paciente o persona...");
    
    try {
      const personaUrl = `/sistemaclinica/controllers/paciente.controller.php?operacion=buscar_por_documento&nrodoc=${encodeURIComponent(nroDoc)}&tipodoc=${encodeURIComponent(tipoDocNormalizado)}`;
      const personaData = await fetchData(personaUrl);
      
      if (personaData && personaData.status && personaData.paciente) {
        console.log("Paciente encontrado:", personaData.paciente);
        
        // Si encontramos un paciente, necesitamos obtener más datos para formar un cliente
        return {
          // No asignamos idcliente porque no existe como cliente
          idpersona: personaData.paciente.idpersona,
          nombres: personaData.paciente.nombres,
          apellidos: personaData.paciente.apellidos,
          tipodoc: personaData.paciente.tipodoc,
          nrodoc: personaData.paciente.nrodoc
        };
      }
    } catch (personaError) {
      console.error("Error al verificar existencia como paciente:", personaError);
    }

    console.log("No se encontró el cliente ni como paciente");
    return null;
  } catch (error) {
    console.error("Error en buscarClientePorDocumento:", error);
    return null;
  } finally {
    ocultarCargando();
  }
}

/**
 * Normaliza el tipo de documento para asegurar formato correcto
 * @param {string} tipoDoc Tipo de documento (dni, pasaporte, etc.)
 * @returns {string} Tipo de documento normalizado (DNI, PASAPORTE, etc.)
 */
function normalizarTipoDocumento(tipoDoc) {
  // Si no hay tipo de documento
  if (!tipoDoc) return 'DNI';
  
  // Convertir a mayúsculas y normalizar
  const tipo = tipoDoc.toUpperCase().trim();
  
  // Mapeo de tipos posibles
  const mapeoTipos = {
    'DNI': 'DNI',
    'PASAPORTE': 'PASAPORTE',
    'CARNET': 'CARNET DE EXTRANJERIA',
    'CARNET DE EXTRANJERIA': 'CARNET DE EXTRANJERIA',
    'OTRO': 'OTRO'
  };
  
  return mapeoTipos[tipo] || 'DNI';
}

/**
 * Valida el documento según su tipo
 * @param {string} tipoDoc Tipo de documento
 * @param {string} nroDoc Número de documento
 * @returns {boolean} True si el documento es válido
 */
function validarDocumentoPorTipo(tipoDoc, nroDoc) {
  if (!nroDoc) return false;
  
  switch (tipoDoc) {
    case 'DNI':
      return /^\d{8}$/.test(nroDoc); // Exactamente 8 dígitos numéricos
    case 'PASAPORTE':
      return /^[A-Za-z0-9]{6,12}$/.test(nroDoc); // Entre 6 y 12 caracteres alfanuméricos
    case 'CARNET DE EXTRANJERIA':
      return /^[A-Za-z0-9]{9}$/.test(nroDoc); // Exactamente 9 caracteres alfanuméricos
    case 'OTRO':
      return nroDoc.length >= 1; // Al menos 1 carácter
    default:
      return false;
  }
}
/**
 * Función para verificar si existe como paciente
 * @param {string} tipoDoc Tipo de documento
 * @param {string} nroDoc Número de documento
 * @returns {Promise} Datos del paciente o null
 */
async function verificarExistenciaPaciente(tipoDoc, nroDoc) {
  try {
    // URL para buscar paciente
    const url = `/sistemaclinica/controllers/paciente.controller.php?operacion=buscar_por_documento&nrodoc=${nroDoc}`;
    console.log("Verificando existencia como paciente:", url);

    const response = await fetchData(url);
    console.log("Respuesta búsqueda paciente:", response);

    return response;
  } catch (error) {
    console.error("Error al verificar paciente:", error);
    return null;
  }
}
/**
 * Actualiza un cliente existente para asociarlo con una empresa
 * @param {number} idcliente ID del cliente
 * @param {number} idempresa ID de la empresa
 * @returns {Promise} Resultado de la operación
 */
async function actualizarClienteConEmpresa(idcliente, idempresa) {
  try {
    if (!idcliente || !idempresa) {
      console.error("Faltan datos para actualizar cliente con empresa");
      return {
        status: false,
        mensaje: "Faltan datos para actualizar cliente con empresa"
      };
    }
    
    console.log(`Actualizando cliente ${idcliente} con empresa ${idempresa}`);
    
    const formData = new FormData();
    formData.append("idcliente", idcliente);
    formData.append("idempresa", idempresa);
    
    const response = await fetchData(
      "/sistemaclinica/controllers/cliente.controller.php?op=actualizar_empresa",
      {
        method: "POST",
        body: formData
      }
    );
    
    console.log("Respuesta de actualización cliente-empresa:", response);
    return response;
  } catch (error) {
    console.error("Error en actualizarClienteConEmpresa:", error);
    return {
      status: false,
      mensaje: "Error al actualizar cliente: " + error.message
    };
  }
}
/**
 * Función para verificar si existe como doctor
 * @param {string} tipoDoc Tipo de documento
 * @param {string} nroDoc Número de documento
 * @returns {Promise} Datos del doctor o null
 */
async function verificarExistenciaDoctor(tipoDoc, nroDoc) {
  try {
    // URL para buscar doctor
    const url = `/sistemaclinica/controllers/doctor.controller.php?op=buscar&nrodoc=${nroDoc}`;
    console.log("Verificando existencia como doctor:", url);

    const response = await fetchData(url);
    console.log("Respuesta búsqueda doctor:", response);

    return response;
  } catch (error) {
    console.error("Error al verificar doctor:", error);
    return null;
  }
}

/**
 * Valida la cantidad exacta de dígitos según tipo de documento
 * MODIFICADO: Ahora es más estricto en la validación
 * @param {string} tipoDoc Tipo de documento
 * @param {string} nroDoc Número de documento
 * @returns {boolean} True si el documento es válido, false en caso contrario
 */
function validarDocumentoPorTipo(tipoDoc, nroDoc) {
  switch (tipoDoc) {
    case "DNI":
      return /^\d{8}$/.test(nroDoc); // Exactamente 8 dígitos numéricos
    case "PASAPORTE":
      return /^[A-Za-z0-9]{6,12}$/.test(nroDoc); // Entre 6 y 12 caracteres alfanuméricos
    case "CARNET DE EXTRANJERIA":
      return /^[A-Za-z0-9]{9}$/.test(nroDoc); // Exactamente 9 caracteres alfanuméricos
    case "OTRO":
      return nroDoc.length >= 1; // Al menos 1 carácter
    default:
      return false;
  }
}

/**
 * Obtiene mensaje de validación según tipo de documento
 * @param {string} tipoDoc Tipo de documento
 * @returns {string} Mensaje de validación
 */
function obtenerMensajeValidacionDocumento(tipoDoc) {
  switch (tipoDoc) {
    case "DNI":
      return "El DNI debe tener exactamente 8 dígitos numéricos";
    case "PASAPORTE":
      return "El pasaporte debe tener entre 6 y 12 caracteres alfanuméricos";
    case "CARNET DE EXTRANJERIA":
      return "El carnet de extranjería debe tener exactamente 9 caracteres alfanuméricos";
    case "OTRO":
      return "El documento debe tener al menos 1 carácter";
    default:
      return "Tipo de documento no válido";
  }
}

/**
 * Busca una empresa por su RUC
 * @param {string} ruc RUC de la empresa
 * @returns {Promise<Object>} Datos de la empresa o null
 */
async function buscarEmpresaPorRuc(ruc) {
  try {
    if (!ruc) {
      return null;
    }

    // Validar formato de RUC
    if (!validarRUC(ruc)) {
      console.warn("RUC inválido en buscarEmpresaPorRuc:", ruc);
      return null;
    }

    mostrarCargando();
    const response = await fetchData(`../../../controllers/empresa.controller.php?op=buscar&ruc=${ruc}`);
    ocultarCargando();

    if (response.status && response.data) {
      console.log("Empresa encontrada por RUC:", response.data);
      return response.data;
    }

    console.log("No se encontró empresa con RUC:", ruc);
    return null;
  } catch (error) {
    ocultarCargando();
    console.error("Error en buscarEmpresaPorRuc:", error);
    return null;
  }
}

// Cargar datos del cliente en el formulario de pago
function cargarDatosClientePagador(cliente) {
  if (!cliente) return;

  // Cargar datos según el tipo de cliente
  if (cliente.tipocliente === "NATURAL") {
    document.getElementById("nombresPagador").value = cliente.nombres || "";
    document.getElementById("apellidosPagador").value = cliente.apellidos || "";

    // Datos ocultos
    document.getElementById("idclientePagador").value = cliente.idcliente || "";
  }
}

// Cargar datos de la empresa en el formulario de pago
function cargarDatosEmpresa(empresa) {
  if (!empresa) return;

  document.getElementById("razonSocial").value = empresa.razonsocial || "";
  document.getElementById("direccionEmpresa").value = empresa.direccion || "";
  document.getElementById("nombreComercial").value =
    empresa.nombrecomercial || "";
  document.getElementById("telefonoEmpresa").value = empresa.telefono || "";
  document.getElementById("emailEmpresa").value = empresa.email || "";

  // Datos ocultos
  document.getElementById("idempresaPago").value = empresa.idempresa || "";
}

/**
 * Registra un nuevo cliente (pagador tercero)
 * @param {Object} datos Datos del cliente
 * @returns {Promise} Resultado de la operación
 */
async function registrarClientePagador(datos) {
  try {
    // Verificar campos mínimos
    if (!datos.tipodoc || !datos.nrodoc || !datos.nombres || !datos.apellidos) {
      console.error("Datos incompletos para registrar cliente pagador:", datos);
      return {
        status: false,
        mensaje: "Faltan datos obligatorios para registrar cliente",
      };
    }

    console.log("Intentando registrar cliente pagador con datos:", datos);

    // Verificar si el cliente ya existe por documento
    const clienteExistente = await buscarClientePorDocumento(
      datos.tipodoc,
      datos.nrodoc
    );

    if (clienteExistente && clienteExistente.idcliente) {
      console.log("Cliente existente encontrado:", clienteExistente);
      return {
        status: true,
        mensaje: "Cliente existente encontrado",
        idcliente: clienteExistente.idcliente,
      };
    }

    // Si no existe, preparar para registrar nuevo cliente
    const formData = new FormData();

    // CORRECCIÓN CRÍTICA: Normalizar datos para evitar problemas con espacios y caracteres especiales
    const nombresProcesados = datos.nombres.trim();
    const apellidosProcesados = datos.apellidos.trim();
    const tipoDocProcesado = datos.tipodoc.toUpperCase().trim();
    const nroDocProcesado = datos.nrodoc.trim();

    // Verificar nuevamente que los datos procesados no estén vacíos
    if (!nombresProcesados || !apellidosProcesados || !tipoDocProcesado || !nroDocProcesado) {
      return {
        status: false,
        mensaje: "Datos inválidos después de procesar",
      };
    }

    // CORRECCIÓN CRÍTICA: Asegurarse de que los nombres de los campos coincidan exactamente
    formData.append("tipodoc", tipoDocProcesado);
    formData.append("nrodoc", nroDocProcesado);
    formData.append("nombres", nombresProcesados);
    formData.append("apellidos", apellidosProcesados);
    
    // CORRECCIÓN CRÍTICA: Forzar tipo de cliente como NATURAL siempre para terceros
    formData.append("tipocliente", "NATURAL");

    // Logging adicional para depuración
    console.log("FormData preparado para enviar:", {
      tipodoc: tipoDocProcesado,
      nrodoc: nroDocProcesado,
      nombres: nombresProcesados,
      apellidos: apellidosProcesados,
      tipocliente: "NATURAL"
    });

    // Enviar datos al servidor con URL absoluta para evitar problemas de rutas
    const response = await fetchData(
      "/sistemaclinica/controllers/cliente.controller.php?op=registrar",
      {
        method: "POST",
        body: formData,
      }
    );

    console.log("Respuesta del servidor para registro de cliente:", response);

    if (response && response.status && response.idcliente) {
      return {
        status: true,
        mensaje: response.mensaje || "Cliente registrado correctamente",
        idcliente: response.idcliente,
      };
    } else {
      return {
        status: false,
        mensaje: response.mensaje || "Error al registrar cliente: respuesta incompleta",
      };
    }
  } catch (error) {
    console.error("Error detallado en registrarClientePagador:", error);
    return {
      status: false,
      mensaje: "Error al registrar cliente: " + error.message,
    };
  }
}

// Registrar nueva empresa
async function registrarEmpresa(datos) {
  try {
    const formData = new FormData();

    // Agregar datos al formulario
    for (const key in datos) {
      formData.append(key, datos[key]);
    }

    // Enviar datos al servidor
    const response = await fetchData(
      "../../../controllers/empresa.controller.php?op=registrar",
      {
        method: "POST",
        body: formData,
      }
    );

    return response;
  } catch (error) {
    console.error("Error en registrarEmpresa:", error);
    return {
      status: false,
      mensaje: "Error al registrar empresa: " + error.message,
    };
  }
}

/**
 * Valida formato de RUC
 * @param {string} ruc Número de RUC a validar
 * @returns {boolean} True si el RUC es válido, false en caso contrario
 */
function validarRUC(ruc) {
  // RUC peruano: 11 dígitos, debe comenzar con 10, 15, 17 o 20
  return /^(10|15|17|20)\d{9}$/.test(ruc);
}
