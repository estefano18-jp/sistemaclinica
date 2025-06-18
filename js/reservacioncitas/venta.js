/**
 * sistemaclinica/js/reservacioncitas/venta.js
 */

/**
 * Gestión de ventas y pagos
 */

// Registrar nueva venta
async function registrarVenta(datos) {
  try {
    // Validar datos mínimos
    if (!datos.idcliente || !datos.idconsulta || !datos.precio || !datos.tipocomprobante || !datos.tipopago) {
      Swal.fire({
        icon: 'warning',
        title: 'Datos incompletos',
        text: 'Por favor, complete todos los campos obligatorios.'
      });
      return null;
    }
    
    // CORRECCIÓN IMPORTANTE: Verificar y normalizar el tipo de pago
    if (!datos.tipopago || datos.tipopago === '') {
      console.warn('Tipo de pago no válido, asignando EFECTIVO por defecto');
      datos.tipopago = 'EFECTIVO';
    } else {
      // Asegurar que el valor esté en mayúsculas como espera la BD
      datos.tipopago = datos.tipopago.toUpperCase();
      
      // Verificar que sea un valor permitido en la BD
      const valoresPermitidos = ['EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'YAPE', 'PLIN'];
      if (!valoresPermitidos.includes(datos.tipopago)) {
        console.warn(`Tipo de pago "${datos.tipopago}" no reconocido, usando EFECTIVO`);
        datos.tipopago = 'EFECTIVO';
      }
    }
    
    console.log('Registrando venta con datos completos:', datos);
    const formData = new FormData();
    
    // Agregar datos al formulario
    for (const key in datos) {
      formData.append(key, datos[key]);
    }
    
    // CORRECCIÓN: Verificación explícita del tipopago antes de enviar
    const tipoPagoFinal = formData.get('tipopago');
    console.log('Tipo de pago a registrar (VERIFICACIÓN FINAL):', tipoPagoFinal);
    
    // Si aún así el valor no es válido, establecerlo explícitamente
    if (!tipoPagoFinal || tipoPagoFinal === '') {
      formData.set('tipopago', 'EFECTIVO');
      console.log('Se estableció EFECTIVO como valor final por seguridad');
    }
    
    // Enviar datos al servidor
    const response = await fetchData('../../../controllers/venta.controller.php?op=registrar', {
      method: 'POST',
      body: formData
    });
    
    return response;
  } catch (error) {
    console.error('Error en registrarVenta:', error);
    return {
      status: false,
      mensaje: 'Error al registrar venta: ' + error.message
    };
  }
}

// Obtener datos del comprobante
async function obtenerComprobante(idventa) {
  try {
    if (!idventa) {
      console.error('ID de venta no proporcionado');
      return null;
    }
    
    console.log('Obteniendo comprobante para venta ID:', idventa);
    
    // MEJORA: Obtener el tipo de pago actual y el monto pagado antes de hacer la petición
    const metodoPago = document.getElementById('metodoPago');
    let tipoPagoActual = 'EFECTIVO';
    
    if (metodoPago) {
      // CORRECCIÓN CRÍTICA: Usar la función global de normalización
      tipoPagoActual = normalizarTipoPago(metodoPago.value);
      console.log('Tipo de pago normalizado para comprobante:', tipoPagoActual);
    }
    
    // MEJORA: Obtener el monto pagado de manera más robusta
    let montoPagadoActual = 0;
    
    // Primero, intentar usar el valor global guardado
    if (window.montoExactoPagado && window.montoExactoPagado.valor && tipoPagoActual !== 'EFECTIVO') {
      montoPagadoActual = window.montoExactoPagado.valor;
      console.log('Usando monto exacto global:', montoPagadoActual);
    } else {
      // Luego, intentar obtener del campo input
      const montoPagadoInput = document.getElementById('montoPagado');
      if (montoPagadoInput) {
        montoPagadoActual = parseFloat(montoPagadoInput.value) || 0;
        console.log('Usando monto desde input:', montoPagadoActual);
      }
    }
    
    // Usar URL absoluta para evitar problemas de rutas relativas
    const url = `/sistemaclinica/controllers/venta.controller.php?op=comprobante&idventa=${idventa}`;
    
    mostrarCargando();
    const response = await fetchData(url);
    ocultarCargando();
    
    if (response.status && response.data) {
      console.log('Datos del comprobante obtenidos correctamente:', response.data);
      
      // NUEVO: Verificar si tenemos datos de cita reservada
      console.log('Datos de cita reservada:', window.datosCitaReservada);
      
      // Obtener precio de manera robusta
      let precioComprobante = 0;
      
      // Intentar obtener de los datos de respuesta
      if (response.data.precio) {
        precioComprobante = parseFloat(response.data.precio);
      } else {
        // Si no está en la respuesta, obtener del DOM
      const precioTotal = obtenerPrecioTotalComprobante();
      precioComprobante = precioTotal;
    }
    
    console.log('Precio del comprobante:', precioComprobante);
    
    // CORRECCIÓN IMPORTANTE: Si el pago no es en efectivo, usar el precio exacto como monto pagado
    if (tipoPagoActual !== 'EFECTIVO' && montoPagadoActual <= 0) {
      montoPagadoActual = precioComprobante;
      console.log('Ajustando monto pagado al precio exacto:', montoPagadoActual);
    }
    
    // CORRECCIÓN IMPORTANTE: Usar la fecha de la cita reservada
    // Esto es clave para solucionar el problema principal
    let fechaConsultaCorrecta = null;
    let horaConsultaCorrecta = null;
    
    // Prioridad 1: Usar datos de la ventana global
    if (window.datosCitaReservada && window.datosCitaReservada.fecha) {
      fechaConsultaCorrecta = window.datosCitaReservada.fecha;
      horaConsultaCorrecta = window.datosCitaReservada.hora;
      console.log("Usando fecha de reserva guardada:", fechaConsultaCorrecta);
    } 
    // Prioridad 2: Usar datos del formulario
    else if (document.getElementById("fecha-reserva") && document.getElementById("fecha-reserva").value) {
      fechaConsultaCorrecta = document.getElementById("fecha-reserva").value;
      horaConsultaCorrecta = document.getElementById("horario-seleccionado").value || 
                          document.getElementById("hora-hidden").value || 
                          response.data.horaprogramada;
      console.log("Usando fecha del formulario:", fechaConsultaCorrecta);
    }
    // Prioridad 3: Usar datos de la respuesta
    else if (response.data.fecha_consulta) {
      fechaConsultaCorrecta = response.data.fecha_consulta;
      horaConsultaCorrecta = response.data.horaprogramada;
      console.log("Usando fecha de la respuesta:", fechaConsultaCorrecta);
    }
    
    // CORRECCIÓN IMPORTANTE: Forzar el tipopago actual al comprobante
    // Esto asegura que se use el método de pago seleccionado por el usuario
    const comprobanteCompleto = {
      ...response.data,
      // Asegurar que estos campos existan con valores por defecto si están ausentes
      tipodoc: response.data.tipodoc || 'BOLETA',
      nrodocumento: response.data.nrodocumento || 'B001-00000001',
      // ASIGNAR EXPLÍCITAMENTE el tipo de pago actual
      tipopago: tipoPagoActual,
      precio: precioComprobante,
      // ASIGNAR EXPLÍCITAMENTE el monto pagado actual
      montopagado: montoPagadoActual,
      fechaemision: response.data.fechaemision || new Date().toISOString(),
      
      // CORRECCIÓN PARA LA FECHA: Asignar la fecha correcta de la cita
      fecha_consulta: fechaConsultaCorrecta || response.data.fecha_consulta || new Date().toISOString().split('T')[0],
      horaprogramada: horaConsultaCorrecta || response.data.horaprogramada || '08:00:00',
      
      // Datos del cliente 
      tipocliente: response.data.tipocliente || 'NATURAL',
      cliente_natural: response.data.cliente_natural || '',
      cliente_tipodoc: response.data.cliente_tipodoc || 'DNI',
      cliente_nrodoc: response.data.cliente_nrodoc || '',
      cliente_direccion: response.data.cliente_direccion || '',
      
      // CORRECCIÓN IMPORTANTE: Datos del cliente empresa para factura
      cliente_empresa: response.data.cliente_empresa || '',
      cliente_ruc: response.data.cliente_ruc || '',
      cliente_empresa_direccion: response.data.cliente_empresa_direccion || '',
      // Campos alternativos para empresa
      razonsocial: response.data.razonsocial || response.data.cliente_empresa || '',
      ruc: response.data.ruc || response.data.cliente_ruc || '',
      direccion: response.data.direccion || response.data.cliente_empresa_direccion || '',
      
      // Datos del paciente
      paciente: response.data.paciente || '',
      paciente_tipodoc: response.data.paciente_tipodoc || 'DNI',
      paciente_nrodoc: response.data.paciente_nrodoc || '',
      
      // Datos de la consulta
      especialidad: response.data.especialidad || 'Medicina General',
      doctor: response.data.doctor || '',
      
      // Datos del cajero
      usuario_venta: response.data.usuario_venta || 'ADMIN',
      id_cajero: response.data.id_cajero || '1'
    };
    
    // NUEVO: Log para verificar que se está usando la fecha correcta
    console.log('Fecha de consulta en el comprobante:', comprobanteCompleto.fecha_consulta);
    console.log('Hora programada en el comprobante:', comprobanteCompleto.horaprogramada);
    
    return comprobanteCompleto;
  } else {
    console.error('Error al obtener comprobante:', response.mensaje || 'No se obtuvo respuesta válida del servidor');
    
    // Mostrar alerta al usuario
    Swal.fire({
      icon: 'warning',
      title: 'Advertencia',
      text: 'No se pudieron obtener todos los datos del comprobante del servidor. Se utilizarán datos por defecto.',
      confirmButtonText: 'Continuar'
    });
    
    // Obtener precio de manera robusta
    const precioTotal = obtenerPrecioTotalComprobante();
    
    // CORRECCIÓN: Si el pago no es en efectivo, usar el precio exacto como monto pagado
    if (tipoPagoActual !== 'EFECTIVO' && montoPagadoActual <= 0) {
      montoPagadoActual = precioTotal;
    }
    
    // CORRECCIÓN: Usar fecha de reserva si está disponible
    let fechaConsultaFallback = new Date().toISOString().split('T')[0];
    let horaConsultaFallback = '08:00:00';
    
    if (window.datosCitaReservada && window.datosCitaReservada.fecha) {
      fechaConsultaFallback = window.datosCitaReservada.fecha;
      horaConsultaFallback = window.datosCitaReservada.hora || '08:00:00';
      console.log("Fallback: Usando fecha de reserva guardada:", fechaConsultaFallback);
    } else if (document.getElementById("fecha-reserva") && document.getElementById("fecha-reserva").value) {
      fechaConsultaFallback = document.getElementById("fecha-reserva").value;
      horaConsultaFallback = document.getElementById("horario-seleccionado").value || 
                          document.getElementById("hora-hidden").value || 
                          '08:00:00';
      console.log("Fallback: Usando fecha del formulario:", fechaConsultaFallback);
    }
    
    // Crear un comprobante básico con datos mínimos
    return {
      tipodoc: 'BOLETA',
      nrodocumento: 'B001-00000001',
      fechaemision: new Date().toISOString(),
      // CORRECCIÓN: Usar el tipo de pago seleccionado
      tipopago: tipoPagoActual,
      montopagado: montoPagadoActual,
      precio: precioTotal,
      tipocliente: 'NATURAL',
      cliente_natural: 'Cliente',
      cliente_tipodoc: 'DNI',
      cliente_nrodoc: '00000000',
      paciente: 'Paciente',
      paciente_tipodoc: 'DNI',
      paciente_nrodoc: '00000000',
      especialidad: 'Medicina General',
      doctor: 'Doctor',
      // CORRECCIÓN PRINCIPAL: Usar la fecha correcta de reserva
      fecha_consulta: fechaConsultaFallback,
      horaprogramada: horaConsultaFallback,
      usuario_venta: 'ADMIN',
      id_cajero: '1'
    };
  }
} catch (error) {
  ocultarCargando();
  console.error('Error en obtenerComprobante:', error);
  
  // Mostrar alerta al usuario
  Swal.fire({
    icon: 'error',
    title: 'Error',
    text: 'No se pudieron obtener los datos del comprobante. Se utilizarán datos por defecto.',
    confirmButtonText: 'Continuar'
  });
  
  // CORRECCIÓN: Obtener el tipo de pago actual para el valor por defecto
  const metodoPago = document.getElementById('metodoPago');
  let tipoPagoActual = 'EFECTIVO';
  let montoPagadoActual = 0;
  
  if (metodoPago) {
    // CORRECCIÓN: Usar mapearMetodoPago en lugar de un mapa simple
    tipoPagoActual = normalizarTipoPago(metodoPago.value);
    
    // Si el pago no es efectivo, obtener el precio como monto pagado
    if (tipoPagoActual !== 'EFECTIVO') {
      montoPagadoActual = obtenerPrecioTotalComprobante();
    } else {
      montoPagadoActual = parseFloat(document.getElementById('montoPagado')?.value || 0);
    }
  }
  
  // CORRECCIÓN: Usar fecha de reserva si está disponible
  let fechaConsultaError = new Date().toISOString().split('T')[0];
  let horaConsultaError = '08:00:00';
  
  if (window.datosCitaReservada && window.datosCitaReservada.fecha) {
    fechaConsultaError = window.datosCitaReservada.fecha;
    horaConsultaError = window.datosCitaReservada.hora || '08:00:00';
    console.log("Error fallback: Usando fecha de reserva guardada:", fechaConsultaError);
  } else if (document.getElementById("fecha-reserva") && document.getElementById("fecha-reserva").value) {
    fechaConsultaError = document.getElementById("fecha-reserva").value;
    horaConsultaError = document.getElementById("horario-seleccionado").value || 
                      document.getElementById("hora-hidden").value || 
                      '08:00:00';
    console.log("Error fallback: Usando fecha del formulario:", fechaConsultaError);
  }
  
  // Crear un comprobante básico por defecto en caso de error
  return {
    tipodoc: 'BOLETA',
    nrodocumento: 'B001-00000001',
    fechaemision: new Date().toISOString(),
    tipopago: tipoPagoActual,
    montopagado: montoPagadoActual,
    precio: obtenerPrecioTotalComprobante(),
    tipocliente: 'NATURAL',
    cliente_natural: 'Cliente',
    cliente_tipodoc: 'DNI',
    cliente_nrodoc: '00000000',
    paciente: 'Paciente',
    paciente_tipodoc: 'DNI',
    paciente_nrodoc: '00000000',
    especialidad: 'Medicina General',
    doctor: 'Doctor',
    // CORRECCIÓN PRINCIPAL: Usar la fecha correcta
    fecha_consulta: fechaConsultaError,
    horaprogramada: horaConsultaError,
    usuario_venta: 'ADMIN',
    id_cajero: '1'
  };
}
}


// Mostrar modal de comprobante
function mostrarModalComprobante(datosComprobante) {
  try {
    console.log("Mostrando modal comprobante con datos:", datosComprobante);

    if (!datosComprobante) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudieron obtener los datos del comprobante."
      });
      return;
    }
    
    // Guardar datos para impresión
    window.datosComprobanteOriginal = datosComprobante;

    // CORRECCIÓN: Verificación robusta del tipo de documento
    const esFactura =
      datosComprobante.tipodoc === "FACTURA" ||
      (datosComprobante.nrodocumento &&
       datosComprobante.nrodocumento.startsWith("F"));

    console.log(`Tipo de documento detectado: ${esFactura ? "FACTURA" : "BOLETA"}`);
    console.log("Tipo de pago recibido:", datosComprobante.tipopago);

    // CAMBIO IMPORTANTE: Elegir la plantilla según el tipo de comprobante
    if (esFactura) {
      // Si es FACTURA, usar plantilla especializada para facturas
      mostrarModalFactura(datosComprobante);
    } else {
      // Si es BOLETA u otro, usar la plantilla para boletas
      mostrarModalBoleta(datosComprobante);
    }
  } catch (error) {
    console.error("Error en mostrarModalComprobante:", error);

    Swal.fire({
      icon: "error",
      title: "Error",
      text: "No se pudo generar el comprobante. Inténtelo de nuevo."
    });
  }
}



// SOLUCIÓN RADICAL PARA EL PROBLEMA DE LAS 2 PÁGINAS
// Modificar la función imprimirComprobante agregando el vuelto en la plantilla

function imprimirComprobante() {
try {
  // Obtener los datos para el comprobante
  const datosComprobante = obtenerDatosComprobante();
  
  // Crear un iframe invisible para la impresión controlada
  let frameImpresion = document.getElementById('frameImpresion');
  if (frameImpresion) {
    frameImpresion.remove();
  }
  
  // Crear nuevo iframe oculto
  frameImpresion = document.createElement('iframe');
  frameImpresion.id = 'frameImpresion';
  frameImpresion.style.width = '0';
  frameImpresion.style.height = '0';
  frameImpresion.style.position = 'absolute';
  frameImpresion.style.visibility = 'hidden';
  document.body.appendChild(frameImpresion);
  
  // Escribir el contenido HTML con las dimensiones exactas del comprobante
  const frameDoc = frameImpresion.contentDocument || frameImpresion.contentWindow.document;
  
  // HTML para vuelto (si el pago es en efectivo y hay vuelto)
  let vueltoHTML = '';
  if (datosComprobante.formaPago === 'EFECTIVO' && parseFloat(datosComprobante.montoPagado) > parseFloat(datosComprobante.precio)) {
    vueltoHTML = `
      <div class="info-pago-item">
        <strong>Monto Pagado:</strong>
        <span>S/ ${parseFloat(datosComprobante.montoPagado).toFixed(2)}</span>
      </div>
      <div class="info-pago-item">
        <strong>Vuelto:</strong>
        <span>S/ ${datosComprobante.vuelto}</span>
      </div>
    `;
  } else if (datosComprobante.formaPago === 'EFECTIVO' && parseFloat(datosComprobante.montoPagado) > 0) {
    vueltoHTML = `
      <div class="info-pago-item">
        <strong>Monto Pagado:</strong>
        <span>S/ ${parseFloat(datosComprobante.montoPagado).toFixed(2)}</span>
      </div>
    `;
  }
  
  frameDoc.open();
  frameDoc.write(`
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <title>Impresión de Comprobante</title>
      <style>
        /* Reset de estilos para control total */
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }
        
        /* Control de tamaño de página */
        @page {
          size: 210mm 297mm; /* A4 */
          margin: 0;
        }
        
        body {
          font-family: Arial, sans-serif;
          font-size: 11pt;
          line-height: 1.3;
          margin: 0;
          padding: 0;
          background: white;
        }
        
        /* Contenedor principal con dimensiones específicas */
        .comprobante {
          width: 176mm; /* Ancho específico como en la imagen */
          margin: 10mm auto; /* Centrado con margen superior e inferior */
          padding: 0;
          border: 1px solid #ddd;
          box-shadow: 0 0 5px rgba(0,0,0,0.1);
          position: relative;
          background: white;
        }
        
        /* Título principal */
        .titulo-principal {
          text-align: center;
          font-size: 12pt;
          font-weight: bold;
          margin: 5mm 0;
        }
        
        /* Cabecera del comprobante */
        .cabecera {
          display: flex;
          justify-content: space-between;
          padding: 0 5mm;
        }
        
        /* Estilo del logo */
        .logo {
          border: 1px solid #000;
          width: 30mm;
          height: 30mm;
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: bold;
          margin-bottom: 2mm;
        }
        
        /* Datos de la empresa */
        .datos-empresa {
          font-size: 9pt;
          line-height: 1.2;
        }
        
        /* Número de comprobante */
        .numero-comprobante {
          text-align: right;
        }
        
        .numero-documento {
          border: 1px solid #000;
          padding: 2mm 4mm;
          display: inline-block;
          margin-bottom: 2mm;
          font-weight: bold;
        }
        
        /* Línea separadora */
        .separador {
          border-top: 1px dashed #000;
          margin: 3mm 0;
        }
        
        /* Sección de información */
        .seccion {
          padding: 0 5mm;
          margin-bottom: 3mm;
        }
        
        .seccion-titulo {
          font-weight: bold;
          margin-bottom: 2mm;
          text-transform: uppercase;
          font-size: 9pt;
        }
        
        /* Tabla de datos cliente/paciente */
        .tabla-info {
          width: 100%;
          font-size: 9pt;
          border-collapse: collapse;
        }
        
        .tabla-info td {
          padding: 1mm 2mm;
        }
        
        .tabla-info td:first-child {
          font-weight: bold;
          width: 25%;
        }
        
        /* Tabla de servicios */
        .tabla-servicios {
          width: 100%;
          border-collapse: collapse;
          margin: 3mm 0;
        }
        
        .tabla-servicios th, 
        .tabla-servicios td {
          padding: 2mm;
          text-align: left;
          font-size: 9pt;
        }
        
        .tabla-servicios th {
          font-weight: bold;
          border-bottom: 1px solid #000;
        }
        
        .tabla-servicios td {
          border-bottom: 1px solid #eee;
        }
        
        .tabla-servicios th:last-child,
        .tabla-servicios td:last-child {
          text-align: right;
        }
        
        .detalle-servicio {
          color: #555;
          font-size: 8pt;
        }
        
        /* Totales */
        .tabla-totales {
          width: 100%;
          margin-top: 2mm;
        }
        
        .tabla-totales td {
          padding: 1mm 2mm;
          font-size: 9pt;
        }
        
        .tabla-totales td:first-child {
          text-align: right;
          font-weight: bold;
        }
        
        .tabla-totales td:last-child {
          text-align: right;
          width: 20%;
        }
        
        .tabla-totales tr:last-child td {
          border-top: 1px solid #000;
          font-weight: bold;
        }
        
        /* Información de pago */
        .info-pago {
          padding: 2mm 5mm;
        }
        
        .info-pago-item {
          display: flex;
          justify-content: space-between;
          margin-bottom: 1mm;
          font-size: 9pt;
        }
        
        .info-pago-item strong {
          font-weight: bold;
        }
        
        /* Pie de página */
        .pie-pagina {
          display: flex;
          justify-content: space-between;
          padding: 3mm 5mm;
          border-top: 1px dashed #000;
          font-size: 8pt;
        }
        
        .pie-izquierda, .pie-derecha {
          width: 48%;
        }
        
        .codigo-qr {
          border: 1px solid #ddd;
          width: 20mm;
          height: 20mm;
          margin-left: auto;
          display: flex;
          align-items: center;
          justify-content: center;
        }
        
        .info-legal {
          font-size: 7pt;
          text-align: center;
          color: #555;
          margin-top: 3mm;
          padding: 0 5mm 5mm;
        }
        
        @media print {
          /* Ajustes para impresión */
          body {
            margin: 0 !important;
            padding: 0 !important;
          }
          
          /* Asegurar que el comprobante ocupe exactamente el espacio indicado */
          .comprobante {
            page-break-after: always;
            page-break-inside: avoid;
            margin: 0 auto !important; /* Centrado horizontal */
          }
        }
      </style>
    </head>
    <body>
      <div class="comprobante">
        <!-- Título del documento -->
        <div class="titulo-principal">${datosComprobante.tipoDocumento}</div>
        
        <!-- Cabecera: Empresa y Número -->
        <div class="cabecera">
          <div class="empresa">
            <div class="logo">CLÍNICA MÉDICA</div>
            <div class="datos-empresa">
              <div>RUC: 20123456789</div>
              <div>Av. Principal 123, Lima</div>
              <div>Tel: (01) 555-1234</div>
            </div>
          </div>
          <div class="numero-comprobante">
            <div class="numero-documento">${datosComprobante.numeroDocumento}</div>
            <div>Fecha: ${datosComprobante.fecha}</div>
            <div>Hora: ${datosComprobante.hora}</div>
          </div>
        </div>
        
        <!-- Línea separadora -->
        <div class="separador"></div>
        
        <!-- Información del cliente y paciente -->
        <div class="seccion">
          <div class="seccion-titulo">INFORMACIÓN DEL CLIENTE Y PACIENTE</div>
          <table class="tabla-info">
            <tr>
              <td>${datosComprobante.tipoDocCliente}:</td>
              <td>${datosComprobante.nroDocCliente}</td>
            </tr>
            <tr>
              <td>Cliente:</td>
              <td>${datosComprobante.nombreCliente}</td>
            </tr>
            <tr>
              <td>${datosComprobante.tipoDocPaciente}:</td>
              <td>${datosComprobante.nroDocPaciente}</td>
            </tr>
            <tr>
              <td>Paciente:</td>
              <td>${datosComprobante.nombrePaciente}</td>
            </tr>
          </table>
        </div>
        
        <!-- Descripción del servicio -->
        <div class="seccion">
          <table class="tabla-servicios">
            <thead>
              <tr>
                <th>Descripción</th>
                <th>Precio</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <div>Consulta Médica - ${datosComprobante.especialidad}</div>
                  <div class="detalle-servicio">Doctor: ${datosComprobante.doctor}</div>
                  <div class="detalle-servicio">Fecha: ${datosComprobante.fechaConsulta} / Hora: ${datosComprobante.horaConsulta}</div>
                </td>
                <td>S/ ${datosComprobante.precio}</td>
              </tr>
            </tbody>
          </table>
          
          <!-- Totales -->
          <table class="tabla-totales">
            <tr>
              <td>SUBTOTAL:</td>
              <td>S/ ${datosComprobante.subtotal}</td>
            </tr>
            <tr>
              <td>IGV (18%):</td>
              <td>S/ ${datosComprobante.igv}</td>
            </tr>
            <tr>
              <td>TOTAL:</td>
              <td>S/ ${datosComprobante.precio}</td>
            </tr>
          </table>
        </div>
        
        <!-- Línea separadora -->
        <div class="separador"></div>
        
        <!-- Información de pago -->
        <div class="info-pago">
          <div class="info-pago-item">
            <strong>Forma de Pago:</strong>
            <span>${datosComprobante.formaPago}</span>
          </div>
          <div class="info-pago-item">
            <strong>SON:</strong>
            <span>${datosComprobante.precioTexto}</span>
          </div>
          ${vueltoHTML}
        </div>
        
        <!-- Línea separadora -->
        <div class="separador"></div>
        
        <!-- Pie de página -->
        <div class="pie-pagina">
          <div class="pie-izquierda">
            <div>Forma de Pago: ${datosComprobante.formaPago}</div>
            <div>Atendido por: ${datosComprobante.cajero || 'Admin'}</div>
            <div>Caja: ${datosComprobante.caja || '1'}</div>
          </div>
          <div class="pie-derecha">
            <div class="codigo-qr">Código QR</div>
          </div>
        </div>
        
        <!-- Información legal -->
        <div class="info-legal">
          Autorizado mediante Resolución de Intendencia N° 034-005-0005429/SUNAT<br>
          Representación impresa de la ${datosComprobante.tipoDocumento}<br>
          Puede consultar este documento en www.clinicamedica.com/consulta
        </div>
      </div>
    </body>
    </html>
  `);
  frameDoc.close();
  
  // Esperar a que el contenido esté completamente cargado y luego imprimir
  setTimeout(() => {
    try {
      // Imprimir directamente desde el iframe
      frameImpresion.contentWindow.focus();
      frameImpresion.contentWindow.print();
      
      // Eliminar el iframe después de la impresión
      setTimeout(() => {
        document.body.removeChild(frameImpresion);
      }, 1000);
    } catch (e) {
      console.error("Error en la impresión:", e);
      Swal.fire({
        icon: 'error',
        title: 'Error de impresión',
        text: 'Ha ocurrido un problema al intentar imprimir. Intente nuevamente.'
      });
    }
  }, 500);
  
} catch (error) {
  console.error('Error global en imprimirComprobante:', error);
  Swal.fire({
    icon: 'error',
    title: 'Error',
    text: 'No se pudo generar el comprobante para impresión.'
  });
}
}
// La función obtenerDatosComprobante permanece igual
function obtenerDatosComprobante() {
  // Objeto para almacenar todos los datos
  const datos = {};
  
  try {
    // 1. Tipo de documento (Boleta/Factura)
    const tituloElemento = document.querySelector('.modal-title') || 
                           document.querySelector('.comprobante-titulo');
    datos.tipoDocumento = tituloElemento ? tituloElemento.textContent.trim() : 'BOLETA DE VENTA ELECTRÓNICA';
    
    // 2. Número de documento
    const numeroElemento = document.querySelector('.comprobante-numero') || 
                           document.querySelector('.modal-body h5.border');
    datos.numeroDocumento = numeroElemento ? numeroElemento.textContent.trim() : 'B001-00000001';
    
    // 3. Fecha y hora
    const fechaElementos = document.querySelectorAll('.modal-body p, .comprobante-fecha div');
    fechaElementos.forEach(el => {
        const texto = el.textContent.trim();
        if (texto.includes('Fecha:')) {
            datos.fecha = texto.replace('Fecha:', '').trim();
        } else if (texto.includes('Hora:')) {
            datos.hora = texto.replace('Hora:', '').trim();
        }
    });
    
    // Valores por defecto si no se encuentran
    datos.fecha = datos.fecha || formatearFecha(new Date());
    datos.hora = datos.hora || formatearHora(new Date());
    
    // 4. Datos del cliente y paciente
    const tipoPaciente = document.getElementById('tipoPaciente');
    const tipoPacienteValue = tipoPaciente ? tipoPaciente.value : 'dni';
    
    const docPaciente = document.getElementById('documentoPaciente');
    const nroDocPaciente = docPaciente ? docPaciente.value : '';
    
    const nombrePaciente = document.getElementById('nombrePaciente');
    const nombre = nombrePaciente ? nombrePaciente.value : '';
    
    const apellidoPaciente = document.getElementById('apellidoPaciente');
    const apellido = apellidoPaciente ? apellidoPaciente.value : '';
    
    // Mapeo de tipo de documento
    const tipoDocMap = {
        'dni': 'DNI',
        'pasaporte': 'PASAPORTE',
        'carnet': 'CARNET DE EXTRANJERIA',
        'otro': 'OTRO'
    };
    
    datos.tipoDocPaciente = tipoDocMap[tipoPacienteValue] || 'DNI';
    datos.nroDocPaciente = nroDocPaciente;
    datos.nombrePaciente = `${apellido || ''} ${nombre || ''}`.trim();
    
    // Si el cliente es el mismo paciente
    const tipoCliente = document.getElementById('tipoCliente');
    const tipoClienteValue = tipoCliente ? tipoCliente.value : 'personal';
    
    // CORRECCIÓN PRINCIPAL: Cuando el tipo de cliente es empresa, usar datos del paciente
    if (tipoClienteValue === 'empresa') {
        // NUEVO: Si es empresa, pero los datos del cliente deben ser del paciente
        datos.tipoDocCliente = datos.tipoDocPaciente;
        datos.nroDocCliente = datos.nroDocPaciente;
        datos.nombreCliente = datos.nombrePaciente;
    } else if (tipoClienteValue === 'personal') {
        datos.tipoDocCliente = datos.tipoDocPaciente;
        datos.nroDocCliente = datos.nroDocPaciente;
        datos.nombreCliente = datos.nombrePaciente;
    } else if (tipoClienteValue === 'tercero') {
        // Si es tercero
        const tipoDocPagador = document.getElementById('tipoDocPagador');
        const tipoDocPagadorValue = tipoDocPagador ? tipoDocPagador.value : 'dni';
        
        datos.tipoDocCliente = tipoDocMap[tipoDocPagadorValue] || 'DNI';
        datos.nroDocCliente = document.getElementById('documentoPagador')?.value || '';
        datos.nombreCliente = `${document.getElementById('apellidosPagador')?.value || ''} ${document.getElementById('nombresPagador')?.value || ''}`.trim();
    }
    
    // 5. Datos del servicio
    const especialidad = document.getElementById('especialidadConsulta');
    datos.especialidad = especialidad ? especialidad.textContent.trim() : 'Medicina General';
    
    // 6. Datos del doctor (usamos un valor por defecto)
    datos.doctor = 'Dr. Asignado';
    
    // 7. Fecha y hora de la consulta - MODIFICADO PARA USAR FECHA CORRECTA
    // Priorizar datos guardados de la reserva
    if (window.datosCitaReservada) {
      console.log("Usando fecha de reserva guardada para impresión:", window.datosCitaReservada.fecha);
      datos.fechaConsulta = formatearFecha(window.datosCitaReservada.fecha);
      datos.horaConsulta = formatearHora(window.datosCitaReservada.hora);
    } else if (window.datosComprobanteOriginal && window.datosComprobanteOriginal.fecha_consulta) {
      // Usar datos del comprobante original si están disponibles
      console.log("Usando fecha del comprobante original para impresión:", window.datosComprobanteOriginal.fecha_consulta);
      datos.fechaConsulta = formatearFecha(window.datosComprobanteOriginal.fecha_consulta);
      datos.horaConsulta = formatearHora(window.datosComprobanteOriginal.horaprogramada);
    } else {
      // Fallback a fecha actual
      console.log("Usando fecha actual como fallback para impresión");
      datos.fechaConsulta = formatearFecha(new Date());
      datos.horaConsulta = formatearHora(new Date());
    }
    
    // 8. Precios
    const precioTexto = document.getElementById('precioTotal')?.textContent || 'S/. 0.00';
    const precioNumero = parseFloat(precioTexto.replace(/[^0-9,.]/g, '').replace(',', '.'));
    
    datos.precio = precioNumero.toFixed(2);
    datos.subtotal = (precioNumero / 1.18).toFixed(2);
    datos.igv = (precioNumero - precioNumero / 1.18).toFixed(2);
    
    // CORRECCIÓN: Obtener correctamente el método de pago
    // 9. Datos de pago - Usar el valor original de tipopago si existe
    if (window.datosComprobanteOriginal && window.datosComprobanteOriginal.tipopago) {
      // Usar el valor original de tipopago
      datos.formaPago = window.datosComprobanteOriginal.tipopago;
    } else {
      // Fallback a obtener del DOM
      const metodoPago = document.getElementById('metodoPago');
      const metodoPagoValue = metodoPago ? metodoPago.value : 'efectivo';
      
      const metodoPagoMap = {
        'efectivo': 'EFECTIVO',
        'transferencia': 'TRANSFERENCIA',
        'yape': 'YAPE',
        'plin': 'PLIN'
      };
      
      datos.formaPago = metodoPagoMap[metodoPagoValue] || 'EFECTIVO';
    }
    
    // CORRECCIÓN: Obtener el monto pagado de manera más robusta
    // Primero del objeto original
    if (window.datosComprobanteOriginal && window.datosComprobanteOriginal.montopagado) {
      datos.montoPagado = parseFloat(window.datosComprobanteOriginal.montopagado);
      console.log('Obteniendo monto pagado de datos originales:', datos.montoPagado);
    } 
    // Luego de la variable global
    else if (window.montoExactoPagado && window.montoExactoPagado.valor && datos.formaPago !== 'EFECTIVO') {
      datos.montoPagado = window.montoExactoPagado.valor;
      console.log('Obteniendo monto pagado de variable global:', datos.montoPagado);
    } 
    // Finalmente del DOM
    else {
      datos.montoPagado = parseFloat(document.getElementById('montoPagado')?.value || 0);
      console.log('Obteniendo monto pagado del DOM:', datos.montoPagado);
    }
    
    // CORRECCIÓN: Si el método de pago no es efectivo, el monto debe ser igual al precio
    if (datos.formaPago !== 'EFECTIVO' && (!datos.montoPagado || datos.montoPagado <= 0)) {
      datos.montoPagado = precioNumero;
      console.log('Ajustando monto pagado al precio exacto:', datos.montoPagado);
    }
    
    datos.vuelto = datos.montoPagado > precioNumero ? (datos.montoPagado - precioNumero).toFixed(2) : '0.00';
    
    // 10. Datos del cajero
    datos.cajero = 'Admin';
    datos.caja = '1';
    
    // 11. Monto en texto
    datos.precioTexto = numeroALetras(Math.floor(precioNumero)) + 
                       ' CON ' + 
                       (precioNumero % 1 === 0 ? 'CERO' : numeroALetras(Math.round((precioNumero % 1) * 100))) + 
                       ' CENTAVOS';
    
    // Log para depuración
    console.log("Datos completos del comprobante para impresión:", datos);
  } catch (error) {
    console.error('Error al extraer datos del comprobante:', error);
    // Datos por defecto
    datos.tipoDocumento = 'BOLETA DE VENTA ELECTRÓNICA';
    datos.numeroDocumento = 'B001-00000001';
    datos.fecha = formatearFecha(new Date());
    datos.hora = formatearHora(new Date());
    datos.tipoDocPaciente = 'DNI';
    datos.nroDocPaciente = '00000000';
    datos.nombrePaciente = 'PACIENTE';
    datos.tipoDocCliente = 'DNI';
    datos.nroDocCliente = '00000000';
    datos.nombreCliente = 'CLIENTE';
    datos.especialidad = 'CONSULTA MÉDICA';
    datos.doctor = 'DOCTOR';
    
    // FALLBACK: Intentar usar datos guardados incluso en caso de error
    if (window.datosCitaReservada) {
      datos.fechaConsulta = formatearFecha(window.datosCitaReservada.fecha);
      datos.horaConsulta = formatearHora(window.datosCitaReservada.hora);
    } else {
      datos.fechaConsulta = formatearFecha(new Date());
      datos.horaConsulta = formatearHora(new Date());
    }
    
    datos.precio = '0.00';
    datos.subtotal = '0.00';
    datos.igv = '0.00';
    datos.formaPago = 'EFECTIVO';
    datos.montoPagado = 0;
    datos.vuelto = '0.00';
    datos.cajero = 'Admin';
    datos.caja = '1';
    datos.precioTexto = 'CERO CON CERO CENTAVOS';
  }
  
  return datos;
}