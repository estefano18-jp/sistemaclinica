<?php /*RUTA: sistemaclinica/views/Citas/GestionarCita/verComprobante.php*/ ?>
<?php
require_once '../../include/header.administrador.php';

// Verificar si se ha proporcionado un ID de cita
$idcita = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$idcita) {
    echo '<div class="alert alert-danger">ID de cita no proporcionado.</div>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comprobante de Pago</title>

    <!-- CSS de Bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        body {
            background-color: #f4f6f9;
        }

        .card {
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            border-bottom: 1px solid #eee;
            background-color: #fff;
        }

        /* Estilo mejorado para el comprobante con borde y sombra */
        .comprobante-contenedor {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        /* Estilos para impresión optimizados para una sola página */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.5cm;
            }

            html,
            body {
                width: 100%;
                height: auto;
                margin: 0;
                padding: 0;
                background-color: #fff !important;
            }

            body * {
                visibility: hidden;
                color: #000 !important;
                background-color: #fff !important;
            }

            #contenidoComprobante,
            #contenidoComprobante * {
                visibility: visible;
                color: #000 !important;
                background-color: #fff !important;
            }

            #contenidoComprobante {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: auto;
                margin: 0;
                padding: 5mm;
                box-sizing: border-box;
            }

            .comprobante-contenedor {
                box-shadow: none;
                border: 1px solid #000;
                padding: 10mm;
                max-width: 190mm;
                margin: 0 auto;
            }

            .no-print,
            .btn,
            .navbar,
            .footer,
            header,
            footer,
            .card-header {
                display: none !important;
            }

            /* Ajustes adicionales para asegurar que quepa en una página */
            .titulo-principal {
                font-size: 14pt !important;
                margin-bottom: 10px !important;
            }

            .logo-clinica {
                width: 80px !important;
                height: 80px !important;
            }

            .datos-empresa {
                font-size: 8pt !important;
            }

            .tabla-datos td {
                padding: 2px 0 !important;
                font-size: 9pt !important;
            }

            .servicio-item {
                padding: 5px 0 !important;
            }

            .servicio-descripcion div {
                margin-bottom: 2px !important;
                font-size: 9pt !important;
            }

            .pie-pagina {
                margin-top: 15px !important;
            }

            .info-legal {
                font-size: 7pt !important;
                margin-top: 15px !important;
            }

            .codigo-qr {
                width: 80px !important;
                height: 80px !important;
            }

            /* Eliminar elementos que no deben aparecer en la impresión */
            .modal-header,
            .modal-footer,
            .btn-close,
            .no-print {
                display: none !important;
            }
        }

        .titulo-principal {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .cabecera {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .logo-clinica {
            width: 120px;
            height: 120px;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .datos-empresa {
            margin-top: 10px;
            font-size: 0.9rem;
        }

        .numero-documento {
            border: 2px solid #000;
            padding: 10px 20px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .fecha-documento {
            text-align: right;
        }

        .linea-punteada {
            border-top: 1px dashed #000;
            margin: 15px 0;
        }

        .linea-continua {
            border-top: 1px solid #000;
            margin: 15px 0;
        }

        .seccion-titulo {
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .tabla-datos {
            width: 100%;
            margin-bottom: 15px;
        }

        .tabla-datos td {
            padding: 5px 0;
        }

        .tabla-datos td:first-child {
            width: 35%;
            font-weight: bold;
        }

        .servicio-cabecera {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            padding-bottom: 5px;
            border-bottom: 1px solid #000;
        }

        .servicio-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .servicio-descripcion div {
            margin-bottom: 5px;
        }

        .total-fila {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }

        .total-etiqueta {
            font-weight: bold;
        }

        .total-final {
            font-weight: bold;
            font-size: 1.1rem;
            padding-top: 5px;
            border-top: 1px solid #000;
        }

        .codigo-qr {
            width: 100px;
            height: 100px;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24pt;
            margin-left: auto;
        }

        .info-legal {
            font-size: 0.8rem;
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0"><i class="fas fa-receipt me-2"></i>Comprobante de Pago</h2>
                        <div>
                            <button id="btn-imprimir" class="btn btn-primary no-print">
                                <i class="fas fa-print me-2"></i>Imprimir
                            </button>
                            <a href="gestionarCita.php" class="btn btn-secondary no-print">
                                <i class="fas fa-arrow-left me-2"></i>Volver
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="contenidoComprobante">
                            <!-- Aquí se cargará el comprobante -->
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2">Cargando comprobante...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Función para mostrar el indicador de carga
        function mostrarCargando() {
            document.getElementById('contenidoComprobante').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando comprobante...</p>
                </div>
            `;
        }

        // FUNCIÓN CORREGIDA: Función mejorada para formatear fecha (DD/MM/YYYY)
        function formatearFecha(fechaStr) {
            try {
                // Si la cadena está vacía o es nula, devolver cadena vacía
                if (!fechaStr) return '';

                // Verificar si es solo una cadena de tiempo (contiene : pero no -)
                if (fechaStr.includes(':') && !fechaStr.includes('-') && !fechaStr.includes('/')) {
                    return '';
                }

                // Eliminar cualquier parte de hora si existe
                if (fechaStr.includes(' ')) {
                    fechaStr = fechaStr.split(' ')[0];
                }

                // Intentar analizar la fecha según diferentes formatos
                let fecha;

                // Para formato YYYY-MM-DD
                if (fechaStr.includes('-')) {
                    const parts = fechaStr.split('-');
                    if (parts.length === 3) {
                        // Si la primera parte tiene 4 dígitos, asumimos YYYY-MM-DD
                        if (parts[0].length === 4) {
                            return `${parts[2].padStart(2, '0')}/${parts[1].padStart(2, '0')}/${parts[0]}`;
                        }
                    }
                }

                // Para formato DD/MM/YYYY
                if (fechaStr.includes('/')) {
                    const parts = fechaStr.split('/');
                    if (parts.length === 3) {
                        // Asegurarnos de que el formato sea consistente
                        return `${parts[0].padStart(2, '0')}/${parts[1].padStart(2, '0')}/${parts[2]}`;
                    }
                }

                // Intentar crear un objeto Date como último recurso
                fecha = new Date(fechaStr);
                if (!isNaN(fecha.getTime())) {
                    const dia = fecha.getDate().toString().padStart(2, '0');
                    const mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
                    const anio = fecha.getFullYear();
                    return `${dia}/${mes}/${anio}`;
                }

                // Si todo falla, devolver la cadena original
                console.log("No se pudo formatear la fecha:", fechaStr);
                return fechaStr;
            } catch (error) {
                console.error("Error al formatear fecha:", error, fechaStr);
                return fechaStr || '';
            }
        }

        // FUNCIÓN CORREGIDA: Función mejorada para formatear hora (HH:MM)
        function formatearHora(horaStr) {
            try {
                // Si la cadena está vacía o es nula, devolver cadena vacía
                if (!horaStr) return '';

                let horas, minutos, periodo;

                // Verificar si es una fecha ISO o timestamp completo
                if (horaStr.includes('T') || (horaStr.includes('-') && horaStr.includes(':'))) {
                    // Es una fecha ISO o similar, intentar crear un objeto Date
                    const fecha = new Date(horaStr);
                    if (!isNaN(fecha.getTime())) {
                        horas = fecha.getHours();
                        minutos = fecha.getMinutes();

                        // Determinar AM/PM
                        periodo = (horas >= 12) ? 'p. m.' : 'a. m.';

                        // Convertir a formato 12 horas
                        horas = (horas > 12) ? horas - 12 : (horas === 0) ? 12 : horas;

                        return `${horas.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')} ${periodo}`;
                    }
                }

                // Si contiene dos puntos, podría ser un formato de hora HH:MM o HH:MM:SS
                if (typeof horaStr === 'string' && horaStr.includes(':')) {
                    let parts = horaStr.split(':');

                    // Si es un formato como "2023-06-04 13:46:00", extraer solo la parte de la hora
                    if (horaStr.includes(' ') && horaStr.includes('-')) {
                        const timePart = horaStr.split(' ')[1];
                        if (timePart && timePart.includes(':')) {
                            parts = timePart.split(':');
                        }
                    }

                    if (parts.length >= 2) {
                        horas = parseInt(parts[0], 10);
                        minutos = parseInt(parts[1], 10);

                        // Si la hora parece ser un año (>23), es probable que sea un formato incorrecto
                        if (horas > 23) {
                            // Probablemente es una fecha mal parseada, intentar de nuevo con Date
                            const fecha = new Date(horaStr);
                            if (!isNaN(fecha.getTime())) {
                                horas = fecha.getHours();
                                minutos = fecha.getMinutes();
                            } else {
                                console.error("Formato de hora no reconocido:", horaStr);
                                return "00:00 a. m."; // Valor predeterminado seguro
                            }
                        }

                        // Determinar AM/PM
                        periodo = (horas >= 12) ? 'p. m.' : 'a. m.';

                        // Convertir a formato 12 horas
                        horas = (horas > 12) ? horas - 12 : (horas === 0) ? 12 : horas;

                        return `${horas.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')} ${periodo}`;
                    }
                }

                // Intentar como último recurso
                const fecha = new Date(horaStr);
                if (!isNaN(fecha.getTime())) {
                    horas = fecha.getHours();
                    minutos = fecha.getMinutes();

                    // Determinar AM/PM
                    periodo = (horas >= 12) ? 'p. m.' : 'a. m.';

                    // Convertir a formato 12 horas
                    horas = (horas > 12) ? horas - 12 : (horas === 0) ? 12 : horas;

                    return `${horas.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')} ${periodo}`;
                }

                // Si todo falla, devolver un valor predeterminado
                console.warn("No se pudo formatear la hora:", horaStr);
                return "00:00 a. m.";
            } catch (error) {
                console.error("Error al formatear hora:", error, horaStr);
                return "00:00 a. m.";
            }
        }

        // Función para obtener texto de tipo de documento
        function obtenerTipoDocumentoTexto(tipoDoc) {
            if (!tipoDoc) return "DNI";
            const tipo = tipoDoc.toUpperCase();
            const tipos = {
                'DNI': 'DNI',
                'PASAPORTE': 'Pasaporte',
                'CARNET DE EXTRANJERIA': 'Carnet',
                'OTRO': 'Doc'
            };
            return tipos[tipo] || tipo;
        }

        // FUNCIÓN CORREGIDA: Función para cargar y mostrar el comprobante
        async function cargarComprobante(idcita) {
            try {
                mostrarCargando();

                // Construir URL para la petición
                const url = `../../../controllers/venta.controller.php?op=comprobante_por_cita&idcita=${idcita}`;
                console.log("Obteniendo comprobante con URL:", url);

                // Realizar petición al servidor
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }

                const data = await response.json();
                console.log("Respuesta del servidor:", data);

                if (data.status && data.data) {
                    // Datos del comprobante
                    const comprobante = data.data;

                    // IMPORTANTE: Loguear los datos críticos para depuración
                    console.log("Datos críticos para el comprobante:");
                    console.log("Fecha consulta:", comprobante.fecha_consulta);
                    console.log("Hora programada:", comprobante.horaprogramada);
                    console.log("Tipo de pago:", comprobante.tipopago);
                    console.log("Tipo de cliente:", comprobante.tipocliente);

                    // Guardar los datos para uso en la función de impresión
                    window.datosComprobanteOriginal = comprobante;

                    // Determinar si es factura o boleta
                    const esFactura = comprobante.tipodoc === 'FACTURA' ||
                        (comprobante.nrodocumento && comprobante.nrodocumento.startsWith('F'));

                    // Preparar HTML según tipo de comprobante
                    let contenidoHTML;

                    if (esFactura) {
                        contenidoHTML = generarHTMLFactura(comprobante);
                    } else {
                        contenidoHTML = generarHTMLBoleta(comprobante);
                    }

                    // Añadir el contenedor principal que mantiene el comprobante en un cuadro
                    const contenedorFinal = `
                <div class="card-body p-0">
                    <div class="container-fluid py-3">
                        ${contenidoHTML}
                    </div>
                </div>
            `;

                    // Actualizar contenido del comprobante
                    document.getElementById('contenidoComprobante').innerHTML = contenedorFinal;

                } else {
                    // Error al obtener datos del comprobante
                    document.getElementById('contenidoComprobante').innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No se pudo obtener el comprobante: ${data.mensaje || 'Error desconocido'}
                </div>
            `;
                }
            } catch (error) {
                console.error("Error al cargar comprobante:", error);

                document.getElementById('contenidoComprobante').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                Error al obtener el comprobante: ${error.message}
                <div class="mt-3">
                    <a href="gestionarCita.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Volver al listado
                    </a>
                </div>
            </div>
        `;
            }
        }

        // FUNCIÓN CORREGIDA: Generar HTML de BOLETA
        function generarHTMLBoleta(comprobante) {
            try {
                // IMPORTANTE: Log para depuración
                console.log("Mostrando boleta con datos:", comprobante);
                console.log("Tipo de pago a mostrar en boleta:", comprobante.tipopago);
                console.log("Fecha de consulta a mostrar:", comprobante.fecha_consulta);
                console.log("Hora programada a mostrar:", comprobante.horaprogramada);
                console.log("Tipo de cliente:", comprobante.tipocliente);

                // Determinar tipo de comprobante
                const tipoComprobante = "BOLETA DE VENTA ELECTRÓNICA";

                // Usar comprobante en lugar de datosComprobante
                const precio = parseFloat(comprobante.precio || 0);
                const montoPagado = parseFloat(comprobante.montopagado || 0);
                const vuelto = comprobante.tipopago === "EFECTIVO" && montoPagado > precio ? montoPagado - precio : 0;

                // Convertir el precio a texto
                const precioEntero = Math.floor(precio);
                const precioDecimales = Math.round((precio - precioEntero) * 100);
                const precioTexto = numeroALetras(precioEntero).toUpperCase() +
                    " CON " +
                    (precioDecimales === 0 ? "CERO" : numeroALetras(precioDecimales).toUpperCase()) +
                    " CENTAVOS";

                // CORRECIÓN: Determinar si hay información de empresa para mostrar
                const hayEmpresa = comprobante.cliente_empresa && comprobante.cliente_ruc;

                // CORRECCIÓN PRINCIPAL: Determinar correctamente los documentos y nombres del cliente
                let clienteNombre, clienteTipoDoc, clienteNumDoc;

                // SOLUCIÓN CLAVE: Priorizar datos de persona asociada independientemente del tipo de cliente
                // 1. Si hay datos de persona, usarlos siempre (prioridad máxima)
                if (comprobante.cliente_natural && comprobante.cliente_nrodoc) {
                    console.log("Cliente PERSONA detectado:", comprobante.cliente_natural);
                    clienteNombre = comprobante.cliente_natural;
                    clienteTipoDoc = obtenerTipoDocumentoTexto(comprobante.cliente_tipodoc || "DNI");
                    clienteNumDoc = comprobante.cliente_nrodoc;
                }
                // 2. Si es una empresa sin persona asociada, usar datos de empresa
                else if (comprobante.tipocliente === 'EMPRESA' || hayEmpresa) {
                    console.log("Cliente EMPRESA sin persona asociada detectado:", comprobante.cliente_empresa);
                    clienteNombre = comprobante.cliente_empresa || 'EMPRESA';
                    clienteTipoDoc = 'RUC';
                    clienteNumDoc = comprobante.cliente_ruc || '-';
                }
                // 3. Por defecto, usar datos del paciente (paciente es el cliente)
                else {
                    console.log("Cliente es el PACIENTE:", comprobante.paciente);
                    clienteNombre = comprobante.paciente || '-';
                    clienteTipoDoc = obtenerTipoDocumentoTexto(comprobante.paciente_tipodoc || "DNI");
                    clienteNumDoc = comprobante.paciente_nrodoc || '-';
                }

                // Información del paciente (siempre persona natural)
                const pacienteNombre = comprobante.paciente || '-';
                const pacienteTipoDoc = obtenerTipoDocumentoTexto(comprobante.paciente_tipodoc || "DNI");
                const pacienteNumDoc = comprobante.paciente_nrodoc || '-';

                // Determinar qué secciones mostrar según método de pago usando tabla
                let seccionPagoHTML = "";

                if (comprobante.tipopago === "EFECTIVO") {
                    // Para EFECTIVO mostrar monto pagado y vuelto
                    seccionPagoHTML = `
                <tr>
                    <td>SON:</td>
                    <td>${precioTexto}</td>
                </tr>
                <tr>
                    <td>Monto Pagado:</td>
                    <td>S/ ${montoPagado.toFixed(2)}</td>
                </tr>
                <tr>
                    <td>Vuelto:</td>
                    <td>S/ ${vuelto.toFixed(2)}</td>
                </tr>
            `;
                } else {
                    // Para otros métodos, mostrar solo monto pagado
                    seccionPagoHTML = `
                <tr>
                    <td>SON:</td>
                    <td>${precioTexto}</td>
                </tr>
                <tr>
                    <td>Monto Pagado:</td>
                    <td>S/ ${precio.toFixed(2)}</td>
                </tr>
            `;
                }

                // CORRECCIÓN: Asegurar que mostramos la fecha y hora correctamente
                const fechaEmision = formatearFecha(comprobante.fechaemision);
                const horaEmision = formatearHora(comprobante.fechaemision);
                const fechaConsultaFormateada = formatearFecha(comprobante.fecha_consulta);
                const horaConsultaFormateada = formatearHora(comprobante.horaprogramada);

                // Crear contenido para la boleta con estilos en blanco y negro
                let contenido = `
        <style>
            /* Estilos para impresión en blanco y negro - OPTIMIZADOS PARA UNA SOLA PÁGINA */
            @media print {
                @page {
                    size: A4 portrait;
                    margin: 0.5cm;
                }
                
                body * {
                    visibility: hidden;
                    color: #000 !important;
                    background-color: #fff !important;
                }
                
                #contenidoModalComprobante, #contenidoModalComprobante * {
                    visibility: visible;
                    color: #000 !important;
                    background-color: #fff !important;
                }
                
                #contenidoModalComprobante {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    padding: 0.5cm;
                }
                
                .modal-header, .btn-close, .modal-footer, .no-print {
                    display: none !important;
                }
                
                /* Ajustar el tamaño del comprobante para una sola página */
                .comprobante-contenedor {
                    width: 100% !important;
                    max-width: 100% !important;
                    font-size: 10pt !important;
                    margin: 0 !important;
                    padding: 0.5cm !important;
                }
                
                /* Reducir tamaños para ajustar a una página */
                .titulo-principal {
                    font-size: 14pt !important;
                    margin-bottom: 0.3cm !important;
                }
                
                .logo-clinica {
                    width: 2.5cm !important;
                    height: 2.5cm !important;
                }
                
                .cabecera {
                    margin-bottom: 0.3cm !important;
                }
                
                .linea-punteada, .linea-continua {
                    margin: 0.2cm 0 !important;
                }
                
                .tabla-datos td {
                    padding: 0.1cm 0 !important;
                    font-size: 9pt !important;
                }
                
                .seccion-titulo {
                    margin-bottom: 0.2cm !important;
                    font-size: 10pt !important;
                }
                
                .total-fila {
                    padding: 0.1cm 0 !important;
                }
                
                .codigo-qr {
                    width: 2cm !important;
                    height: 2cm !important;
                }
                
                .info-legal {
                    font-size: 7pt !important;
                    margin-top: 0.3cm !important;
                }
            }
            
            /* Estilos para visualización en pantalla */
            .comprobante-contenedor {
                font-family: Arial, sans-serif;
                max-width: 800px;
                margin: 0 auto;
                color: #000;
                background-color: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
                padding: 30px;
            }
            
            .titulo-principal {
                text-align: center;
                font-size: 16pt;
                font-weight: bold;
                margin-bottom: 20px;
                color: #000;
            }
            
            .cabecera {
                display: flex;
                justify-content: space-between;
                margin-bottom: 20px;
                color: #000;
            }
            
            .logo-clinica {
                width: 120px;
                height: 120px;
                border: 1px solid #000;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                color: #000;
                background-color: #fff;
            }
            
            .info-empresa {
                margin-top: 10px;
                font-size: 10pt;
                color: #000;
            }
            
            .numero-documento {
                border: 2px solid #000;
                padding: 10px 20px;
                text-align: center;
                font-weight: bold;
                margin-bottom: 10px;
                color: #000;
                background-color: #fff;
            }
            
            .fecha-documento {
                text-align: right;
                color: #000;
            }
            
            .linea-punteada {
                border-top: 1px dashed #000;
                margin: 15px 0;
            }
            
            .linea-continua {
                border-top: 1px solid #000;
                margin: 15px 0;
            }
            
            .seccion-titulo {
                font-weight: bold;
                margin-bottom: 10px;
                text-transform: uppercase;
                color: #000;
            }
            
            .tabla-datos {
                width: 100%;
                margin-bottom: 15px;
                color: #000;
            }
            
            .tabla-datos td {
                padding: 5px 0;
                color: #000;
            }
            
            .tabla-datos td:first-child {
                width: 35%;
                font-weight: bold;
                color: #000;
            }
            
            .detalle-servicio {
                margin: 15px 0;
                color: #000;
            }
            
            .servicio-cabecera {
                display: flex;
                justify-content: space-between;
                font-weight: bold;
                padding-bottom: 5px;
                border-bottom: 1px solid #000;
                color: #000;
            }
            
            .servicio-item {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                border-bottom: 1px solid #eee;
                color: #000;
            }
            
            .servicio-descripcion {
                color: #000;
            }
            
            .servicio-descripcion div {
                margin-bottom: 5px;
                color: #000;
            }
            
            .precio-consulta {
                color: #000;
            }
            
            .totales-seccion {
                margin: 15px 0;
                color: #000;
            }
            
            .total-fila {
                display: flex;
                justify-content: space-between;
                padding: 5px 0;
                color: #000;
            }
            
            .total-etiqueta {
                font-weight: bold;
                color: #000;
            }
            
            .total-final {
                font-weight: bold;
                font-size: 14pt;
                padding-top: 5px;
                border-top: 1px solid #000;
                color: #000;
            }
            
            .codigo-qr {
                width: 100px;
                height: 100px;
                border: 1px solid #000;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24pt;
                margin-left: auto;
                color: #000;
                background-color: #fff;
            }
            
            .pie-pagina {
                display: flex;
                justify-content: space-between;
                margin-top: 30px;
                color: #000;
            }
            
            .info-adicional {
                width: 60%;
                font-size: 9pt;
                color: #000;
            }
            
            .info-legal {
                font-size: 8pt;
                text-align: center;
                margin-top: 30px;
                color: #000;
            }
        </style>
        <div class="comprobante-contenedor">
            <!-- Título principal -->
            <div class="titulo-principal">${tipoComprobante}</div>
            
            <!-- Cabecera con logo y número de boleta -->
            <div class="cabecera">
                <div>
                    <div class="logo-clinica">CLÍNICA MÉDICA</div>
                    <div class="info-empresa">
                        <div>RUC: 20123456789</div>
                        <div>Av. Principal 123, Lima</div>
                        <div>Tel: (01) 555-1234</div>
                    </div>
                </div>
                <div>
                    <div class="numero-documento">${comprobante.nrodocumento}</div>
                    <div class="fecha-documento">
                        <div>Fecha: ${fechaEmision}</div>
                        <div>Hora: ${horaEmision}</div>
                    </div>
                </div>
            </div>
            
            <!-- Línea punteada -->
            <div class="linea-punteada"></div>
            
            <!-- INFORMACIÓN DEL CLIENTE Y PACIENTE -->
            <div class="seccion-titulo">INFORMACIÓN DEL CLIENTE Y PACIENTE</div>
            
            <div class="linea-punteada"></div>
            
            <table class="tabla-datos">
                <tr>
                    <td>${clienteTipoDoc} Cliente:</td>
                    <td>${clienteNumDoc}</td>
                </tr>
                <tr>
                    <td>Cliente:</td>
                    <td>${clienteNombre}</td>
                </tr>
                <tr>
                    <td>${pacienteTipoDoc} Paciente:</td>
                    <td>${pacienteNumDoc}</td>
                </tr>
                <tr>
                    <td>Paciente:</td>
                    <td>${pacienteNombre}</td>
                </tr>
            </table>
            
            <!-- Línea punteada -->
            <div class="linea-punteada"></div>
            
            <!-- DETALLE DE SERVICIOS -->
            <div class="detalle-servicio">
                <div class="servicio-cabecera">
                    <div>Descripción</div>
                    <div>Precio</div>
                </div>
                <div class="servicio-item">
                    <div class="servicio-descripcion">
                        <div>Consulta Médica - ${comprobante.especialidad || 'Medicina General'}</div>
                        <div style="font-size: 0.9em; color: #000;">Doctor: ${comprobante.doctor || '-'}</div>
                        <div style="font-size: 0.9em; color: #000;">Fecha: ${fechaConsultaFormateada} / Hora: ${horaConsultaFormateada}</div>
                    </div>
                    <div class="precio-consulta">S/ ${precio.toFixed(2)}</div>
                </div>
            </div>
            
            <!-- Línea continua -->
            <div class="linea-continua"></div>
            
            <!-- SUBTOTAL, IGV, TOTAL -->
            <div class="totales-seccion">
                <div class="total-fila">
                    <div class="total-etiqueta">SUBTOTAL:</div>
                    <div>S/ ${(precio / 1.18).toFixed(2)}</div>
                </div>
                <div class="total-fila">
                    <div class="total-etiqueta">IGV (18%):</div>
                    <div>S/ ${(precio - precio / 1.18).toFixed(2)}</div>
                </div>
                <div class="total-fila total-final">
                    <div class="total-etiqueta">TOTAL:</div>
                    <div>S/ ${precio.toFixed(2)}</div>
                </div>
            </div>
            
            <!-- Línea punteada -->
            <div class="linea-punteada"></div>
            
            <!-- INFORMACIÓN DE PAGO - CORREGIDA CON MISMO FORMATO QUE INFORMACIÓN DE CLIENTE -->
            <div class="seccion-titulo">INFORMACIÓN DE PAGO</div>
            
            <div class="linea-punteada"></div>
            
            <table class="tabla-datos">
                <tr>
                    <td>Forma de Pago:</td>
                    <td>${comprobante.tipopago || 'EFECTIVO'}</td>
                </tr>
                ${seccionPagoHTML}
            </table>
            
            <!-- Línea punteada -->
            <div class="linea-punteada"></div>
            
            <!-- Pie de página -->
            <div class="pie-pagina">
                <div class="info-adicional">
                    <div><strong>Forma de Pago:</strong> ${comprobante.tipopago || "EFECTIVO"}</div>
                    <div><strong>Atendido por:</strong> ${comprobante.usuario_venta || "Administrador"}</div>
                    <div><strong>Caja:</strong> ${comprobante.id_cajero || "1"}</div>
                </div>
                <div class="codigo-qr">
                    ■ ■<br>
                    ■ ■
                </div>
            </div>
            
            <!-- Información legal -->
            <div class="info-legal">
                Autorizado mediante Resolución de Intendencia N° 034-005-0005429/SUNAT<br>
                Representación impresa de la BOLETA DE VENTA ELECTRÓNICA<br>
                Puede consultar este documento en www.clinicamedica.com/consulta
            </div>
        </div>
        `;

                return contenido;
            } catch (error) {
                console.error("Error en generarHTMLBoleta:", error);
                throw new Error("No se pudo generar la boleta: " + error.message);
            }
        }
        // FUNCIÓN CORREGIDA: Generar HTML de FACTURA
        function generarHTMLFactura(datosComprobante) {
            try {
                console.log("Generando factura con datos:", datosComprobante);

                const tipoComprobante = "FACTURA ELECTRÓNICA";

                const precio = parseFloat(datosComprobante.precio || 0);
                const montoPagado = parseFloat(datosComprobante.montopagado || 0);
                const vuelto = datosComprobante.tipopago === "EFECTIVO" && montoPagado > precio ? montoPagado - precio : 0;

                const precioEntero = Math.floor(precio);
                const precioDecimales = Math.round((precio - precioEntero) * 100);
                const precioTexto = numeroALetras(precioEntero).toUpperCase() +
                    " CON " +
                    (precioDecimales === 0 ? "CERO" : numeroALetras(precioDecimales).toUpperCase()) +
                    " CENTAVOS";

                // ✅ CORRECCIÓN PRINCIPAL: Obtener datos de empresa de manera robusta
                let empresaRUC = "-";
                let empresaNombre = "-";
                let empresaDireccion = "-";

                // Obtener datos de empresa de los datos del comprobante
                if (datosComprobante.cliente_ruc && datosComprobante.cliente_ruc.trim() !== "") {
                    empresaRUC = datosComprobante.cliente_ruc;
                }

                if (datosComprobante.cliente_empresa && datosComprobante.cliente_empresa.trim() !== "") {
                    empresaNombre = datosComprobante.cliente_empresa;
                }

                if (datosComprobante.cliente_empresa_direccion && datosComprobante.cliente_empresa_direccion.trim() !== "") {
                    empresaDireccion = datosComprobante.cliente_empresa_direccion;
                }

                console.log("Datos de empresa para factura:", {
                    empresaRUC,
                    empresaNombre,
                    empresaDireccion
                });

                // ✅ SOLUCIÓN CLAVE: Priorizar datos de persona asociada si existe
                let clienteDoc, clienteNombre;

                // LÓGICA CORREGIDA: Priorizar la persona asociada al cliente, independientemente del tipo
                if (datosComprobante.cliente_natural && datosComprobante.cliente_nrodoc) {
                    // Si hay una persona asociada, usar esos datos (prioridad 1)
                    clienteDoc = datosComprobante.cliente_nrodoc;
                    clienteNombre = datosComprobante.cliente_natural;
                    console.log("Cliente PERSONA asociada detectada para factura:", clienteNombre);
                } else if (datosComprobante.tipocliente === 'EMPRESA') {
                    // Si es empresa sin persona asociada, usar datos de empresa (prioridad 2)
                    clienteDoc = empresaRUC;
                    clienteNombre = empresaNombre;
                    console.log("Cliente EMPRESA sin persona asociada para factura:", clienteNombre);
                } else {
                    // Si no hay datos claros, usar paciente como respaldo (prioridad 3)
                    clienteDoc = datosComprobante.paciente_nrodoc || "-";
                    clienteNombre = datosComprobante.paciente || "-";
                    console.log("Usando PACIENTE como cliente (respaldo) para factura:", clienteNombre);
                }

                // Información del paciente (siempre persona natural)
                const pacienteNombre = datosComprobante.paciente || '-';
                const pacienteNumDoc = datosComprobante.paciente_nrodoc || '-';

                // Determinar qué secciones mostrar según método de pago
                let seccionPagoHTML = "";

                if (datosComprobante.tipopago === "EFECTIVO") {
                    seccionPagoHTML = `
                <tr>
                    <td>Son:</td>
                    <td>${precioTexto}</td>
                </tr>
                <tr>
                    <td>Monto Pagado:</td>
                    <td>S/ ${montoPagado.toFixed(2)}</td>
                </tr>
                <tr>
                    <td>Vuelto:</td>
                    <td>S/ ${vuelto.toFixed(2)}</td>
                </tr>
            `;
                } else {
                    seccionPagoHTML = `
                <tr>
                    <td>Son:</td>
                    <td>${precioTexto}</td>
                </tr>
                <tr>
                    <td>Monto Pagado:</td>
                    <td>S/ ${precio.toFixed(2)}</td>
                </tr>
            `;
                }

                // CORRECCIÓN: Asegurar que mostramos la fecha y hora correctamente
                const fechaEmision = formatearFecha(datosComprobante.fechaemision);
                const horaEmision = formatearHora(datosComprobante.fechaemision);
                const fechaConsultaFormateada = formatearFecha(datosComprobante.fecha_consulta);
                const horaConsultaFormateada = formatearHora(datosComprobante.horaprogramada);

                // Crear contenido para la factura
                let contenido = `
            <style>
                /* Estilos para impresión en blanco y negro - OPTIMIZADOS PARA UNA SOLA PÁGINA */
                @media print {
                    @page {
                        size: A4 portrait;
                        margin: 0.5cm;
                    }
                    
                    body * {
                        visibility: hidden;
                        color: #000 !important;
                        background-color: #fff !important;
                    }
                    
                    #contenidoModalComprobante, #contenidoModalComprobante * {
                        visibility: visible;
                        color: #000 !important;
                        background-color: #fff !important;
                    }
                    
                    #contenidoModalComprobante {
                        position: absolute;
                        left: 0;
                        top: 0;
                        width: 100%;
                        padding: 0.5cm;
                    }
                    
                    .modal-header, .btn-close, .modal-footer, .no-print {
                        display: none !important;
                    }
                    
                    .comprobante-contenedor {
                        width: 100% !important;
                        max-width: 100% !important;
                        font-size: 10pt !important;
                        margin: 0 !important;
                        padding: 0.5cm !important;
                    }
                    
                    .titulo-principal {
                        font-size: 14pt !important;
                        margin-bottom: 0.3cm !important;
                    }
                    
                    .logo-clinica {
                        width: 2.5cm !important;
                        height: 2.5cm !important;
                    }
                    
                    .cabecera {
                        margin-bottom: 0.3cm !important;
                    }
                    
                    .linea-punteada, .linea-continua {
                        margin: 0.2cm 0 !important;
                    }
                    
                    .tabla-datos td {
                        padding: 0.1cm 0 !important;
                        font-size: 9pt !important;
                    }
                    
                    .seccion-titulo {
                        margin-bottom: 0.2cm !important;
                        font-size: 10pt !important;
                    }
                    
                    .total-fila {
                        padding: 0.1cm 0 !important;
                    }
                    
                    .codigo-qr {
                        width: 2cm !important;
                        height: 2cm !important;
                    }
                    
                    .info-legal {
                        font-size: 7pt !important;
                        margin-top: 0.3cm !important;
                    }
                }
                
                /* Estilos para visualización en pantalla */
                .comprobante-contenedor {
                    font-family: Arial, sans-serif;
                    max-width: 800px;
                    margin: 0 auto;
                    color: #000;
                    background-color: #fff;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
                    padding: 30px;
                }
                
                .titulo-principal {
                    text-align: center;
                    font-size: 16pt;
                    font-weight: bold;
                    margin-bottom: 20px;
                    color: #000;
                }
                
                .cabecera {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 20px;
                    color: #000;
                }
                
                .logo-clinica {
                    width: 120px;
                    height: 120px;
                    border: 1px solid #000;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    color: #000;
                    background-color: #fff;
                }
                
                .info-empresa {
                    margin-top: 10px;
                    font-size: 10pt;
                    color: #000;
                }
                
                .numero-documento {
                    border: 2px solid #000;
                    padding: 10px 20px;
                    text-align: center;
                    font-weight: bold;
                    margin-bottom: 10px;
                    color: #000;
                    background-color: #fff;
                }
                
                .fecha-documento {
                    text-align: right;
                    color: #000;
                }
                
                .linea-punteada {
                    border-top: 1px dashed #000;
                    margin: 15px 0;
                }
                
                .linea-continua {
                    border-top: 1px solid #000;
                    margin: 15px 0;
                }
                
                .seccion-titulo {
                    font-weight: bold;
                    margin-bottom: 10px;
                    text-transform: uppercase;
                    color: #000;
                }
                
                .tabla-datos {
                    width: 100%;
                    margin-bottom: 15px;
                    color: #000;
                }
                
                .tabla-datos td {
                    padding: 5px 0;
                    color: #000;
                }
                
                .tabla-datos td:first-child {
                    width: 35%;
                    font-weight: bold;
                    color: #000;
                }
                
                .detalle-servicio {
                    margin: 15px 0;
                    color: #000;
                }
                
                .servicio-cabecera {
                    display: flex;
                    justify-content: space-between;
                    font-weight: bold;
                    padding-bottom: 5px;
                    border-bottom: 1px solid #000;
                    color: #000;
                }
                
                .servicio-item {
                    display: flex;
                    justify-content: space-between;
                    padding: 10px 0;
                    border-bottom: 1px solid #eee;
                    color: #000;
                }
                
                .servicio-descripcion {
                    color: #000;
                }
                
                .servicio-descripcion div {
                    margin-bottom: 5px;
                    color: #000;
                }
                
                .precio-consulta {
                    color: #000;
                }
                
                .totales-seccion {
                    margin: 15px 0;
                    color: #000;
                }
                
                .total-fila {
                    display: flex;
                    justify-content: space-between;
                    padding: 5px 0;
                    color: #000;
                }
                
                .total-etiqueta {
                    font-weight: bold;
                    color: #000;
                }
                
                .total-final {
                    font-weight: bold;
                    font-size: 14pt;
                    padding-top: 5px;
                    border-top: 1px solid #000;
                    color: #000;
                }
                
                .codigo-qr {
                    width: 100px;
                    height: 100px;
                    border: 1px solid #000;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 24pt;
                    margin-left: auto;
                    color: #000;
                    background-color: #fff;
                }
                
                .pie-pagina {
                    display: flex;
                    justify-content: space-between;
                    margin-top: 30px;
                    color: #000;
                }
                
                .info-adicional {
                    width: 60%;
                    font-size: 9pt;
                    color: #000;
                }
                
                .info-legal {
                    font-size: 8pt;
                    text-align: center;
                    margin-top: 30px;
                    color: #000;
                }
            </style>
            <div class="comprobante-contenedor">
                <!-- Título principal -->
                <div class="titulo-principal">${tipoComprobante}</div>
                
                <!-- Cabecera con logo y número de factura -->
                <div class="cabecera">
                    <div>
                        <div class="logo-clinica">CLÍNICA MÉDICA</div>
                        <div class="info-empresa">
                            <div>RUC: 20123456789</div>
                            <div>Av. Principal 123, Lima</div>
                            <div>Tel: (01) 555-1234</div>
                        </div>
                    </div>
                    <div>
                        <div class="numero-documento">${datosComprobante.nrodocumento}</div>
                        <div class="fecha-documento">
                            <div>Fecha: ${fechaEmision}</div>
                            <div>Hora: ${horaEmision}</div>
                        </div>
                    </div>
                </div>
                
                <!-- SECCIÓN: INFORMACIÓN DE LA EMPRESA (SIEMPRE SE MUESTRA) -->
                <div class="linea-punteada"></div>
                <div class="seccion-titulo">INFORMACIÓN DE LA EMPRESA</div>
                
                <div class="linea-punteada"></div>
                
                <table class="tabla-datos">
                    <tr>
                        <td>RUC:</td>
                        <td>${empresaRUC}</td>
                    </tr>
                    <tr>
                        <td>Razón Social:</td>
                        <td>${empresaNombre}</td>
                    </tr>
                    <tr>
                        <td>Dirección:</td>
                        <td>${empresaDireccion}</td>
                    </tr>
                </table>
                
                <!-- INFORMACIÓN DEL CLIENTE Y PACIENTE -->
                <div class="linea-punteada"></div>
                <div class="seccion-titulo">INFORMACIÓN DEL CLIENTE Y PACIENTE</div>
                
                <div class="linea-punteada"></div>
                
                <table class="tabla-datos">
                    <tr>
                        <td>DNI Cliente:</td>
                        <td>${clienteDoc}</td>
                    </tr>
                    <tr>
                        <td>Cliente:</td>
                        <td>${clienteNombre}</td>
                    </tr>
                    <tr>
                        <td>DNI Paciente:</td>
                        <td>${pacienteNumDoc}</td>
                    </tr>
                    <tr>
                        <td>Paciente:</td>
                        <td>${pacienteNombre}</td>
                    </tr>
                </table>
                
                <!-- Línea punteada -->
                <div class="linea-punteada"></div>
                
                <!-- DETALLE DE SERVICIOS -->
                <div class="detalle-servicio">
                    <div class="servicio-cabecera">
                        <div>Descripción</div>
                        <div>Precio</div>
                    </div>
                    <div class="servicio-item">
                        <div class="servicio-descripcion">
                            <div>Consulta Médica - ${datosComprobante.especialidad || 'Medicina General'}</div>
                            <div style="font-size: 0.9em; color: #000;">Doctor: ${datosComprobante.doctor || '-'}</div>
                            <div style="font-size: 0.9em; color: #000;">Fecha: ${fechaConsultaFormateada} / Hora: ${horaConsultaFormateada}</div>
                        </div>
                        <div class="precio-consulta">S/ ${precio.toFixed(2)}</div>
                    </div>
                </div>
                
                <!-- Línea continua -->
                <div class="linea-continua"></div>
                
                <!-- SUBTOTAL, IGV, TOTAL -->
                <div class="totales-seccion">
                    <div class="total-fila">
                        <div class="total-etiqueta">VALOR VENTA:</div>
                        <div>S/ ${(precio / 1.18).toFixed(2)}</div>
                    </div>
                    <div class="total-fila">
                        <div class="total-etiqueta">IGV (18%):</div>
                        <div>S/ ${(precio - precio / 1.18).toFixed(2)}</div>
                    </div>
                    <div class="total-fila total-final">
                        <div class="total-etiqueta">TOTAL:</div>
                        <div>S/ ${precio.toFixed(2)}</div>
                    </div>
                </div>
                
                <!-- Línea punteada -->
                <div class="linea-punteada"></div>
                
                <!-- INFORMACIÓN DE PAGO -->
                <div class="seccion-titulo">INFORMACIÓN DE PAGO</div>
                
                <div class="linea-punteada"></div>
                
                <table class="tabla-datos">
                    <tr>
                        <td>Forma de Pago:</td>
                        <td>${datosComprobante.tipopago}</td>
                    </tr>
                    ${seccionPagoHTML}
                </table>
                
                <!-- Línea punteada -->
                <div class="linea-punteada"></div>
                
                <!-- Pie de página -->
                <div class="pie-pagina">
                    <div class="info-adicional">
                        <div><strong>Forma de Pago:</strong> ${datosComprobante.tipopago}</div>
                        <div><strong>Atendido por:</strong> ${datosComprobante.usuario_venta || "Administrador"}</div>
                        <div><strong>Caja:</strong> ${datosComprobante.id_cajero || "1"}</div>
                    </div>
                    <div class="codigo-qr">
                        ■ ■<br>
                        ■ ■
                    </div>
                </div>
                
                <!-- Información legal -->
                <div class="info-legal">
                    Autorizado mediante Resolución de Intendencia N° 034-005-0005429/SUNAT<br>
                    Representación impresa de la FACTURA ELECTRÓNICA<br>
                    Puede consultar este documento en www.clinicamedica.com/consulta<br>
                    Bienes transferidos en la Amazonía - Ley N° 27037
                </div>
            </div>
        `;

                return contenido;
            } catch (error) {
                console.error("Error en generarHTMLFactura:", error);
                throw new Error("No se pudo generar la factura: " + error.message);
            }
        }
        // FUNCIÓN CORREGIDA: Función optimizada para imprimir en una sola hoja
        function imprimirComprobante() {
            try {
                // Obtener los datos para el comprobante
                const datosComprobante = window.datosComprobanteOriginal;

                if (!datosComprobante) {
                    throw new Error("No se encuentran los datos del comprobante para impresión");
                }

                console.log("Imprimiendo comprobante con datos:", datosComprobante);

                // Crear iframe para impresión
                let frameImpresion = document.getElementById("frameImpresion");
                if (frameImpresion) {
                    document.body.removeChild(frameImpresion);
                }

                frameImpresion = document.createElement("iframe");
                frameImpresion.id = "frameImpresion";
                frameImpresion.style.width = "0";
                frameImpresion.style.height = "0";
                frameImpresion.style.position = "absolute";
                frameImpresion.style.visibility = "hidden";
                document.body.appendChild(frameImpresion);

                // Estilos específicos para optimizar impresión en una sola página
                const estilosOptimizados = `
            @page {
                size: A4 portrait;
                margin: 0.5cm;
            }
            
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                font-size: 10pt;
                line-height: 1.2;
            }
            
            .comprobante {
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding: 0.5cm;
                page-break-inside: avoid;
                box-sizing: border-box;
                border: 1px solid #000;
                background-color: #fff;
            }
            
            .comprobante-header {
                display: flex;
                justify-content: space-between;
                margin-bottom: 0.3cm;
            }
            
            .titulo {
                text-align: center;
                font-size: 12pt;
                font-weight: bold;
                margin-bottom: 0.3cm;
            }
            
            .logo {
                width: 2.5cm;
                height: 2cm;
                border: 1px solid #000;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                text-align: center;
                padding: 5px;
                font-size: 9pt;
                line-height: 1.4;
            }
            
            .datos-empresa {
                font-size: 7pt;
                margin-top: 0.2cm;
            }
            
            .numero-comprobante {
                border: 1px solid #000;
                padding: 0.2cm;
                text-align: center;
                font-weight: bold;
                margin-bottom: 0.2cm;
                font-size: 10pt;
            }
            
            .fecha-hora {
                font-size: 8pt;
                text-align: right;
            }
            
            .separador {
                border-top: 1px dashed #000;
                margin: 0.2cm 0;
            }
            
            .separador-solido {
                border-top: 1px solid #000;
                margin: 0.2cm 0;
            }
            
            .seccion-titulo {
                font-weight: bold;
                font-size: 9pt;
                margin: 0.1cm 0;
                text-transform: uppercase;
            }
            
            .info-tabla {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 0.2cm;
            }
            
            .info-tabla td {
                padding: 0.1cm 0;
                font-size: 8pt;
            }
            
            .info-tabla td:first-child {
                width: 35%;
                font-weight: bold;
            }
            
            .servicio-detalle {
                margin: 0.2cm 0;
            }
            
            .servicio-header {
                display: flex;
                justify-content: space-between;
                font-weight: bold;
                padding-bottom: 0.1cm;
                border-bottom: 1px solid #000;
                font-size: 8pt;
            }
            
            .servicio-item {
                display: flex;
                justify-content: space-between;
                padding: 0.1cm 0;
                border-bottom: 1px solid #eee;
                font-size: 8pt;
            }
            
            .servicio-desc {
                width: 70%;
            }
            
            .servicio-precio {
                width: 30%;
                text-align: right;
            }
            
            .servicio-info {
                font-size: 7pt;
                color: #333;
                margin-top: 0.1cm;
            }
            
            /* Estilo para totales */
            .totales-seccion {
                width: 100%;
                margin-top: 0.2cm;
            }
            
            .total-fila {
                display: flex;
                justify-content: space-between;
                padding: 0.1cm 0;
                font-size: 8pt;
            }
            
            .total-etiqueta {
                font-weight: bold;
                text-align: left;
            }
            
            .total-valor {
                text-align: right;
                min-width: 80px;
            }
            
            .total-final {
                font-weight: bold;
                font-size: 9pt;
                border-top: 1px solid #000;
                padding-top: 0.1cm;
            }
            
            .pie-comprobante {
                display: flex;
                justify-content: space-between;
                margin-top: 0.2cm;
                font-size: 7pt;
            }
            
            .info-pie {
                width: 65%;
            }
            
            .qr-code {
                width: 1.8cm;
                height: 1.8cm;
                border: 1px solid #000;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12pt;
            }
            
            .nota-legal {
                font-size: 6pt;
                text-align: center;
                margin-top: 0.2cm;
            }
        `;

                // Determinar si es factura o boleta
                const esFactura = datosComprobante.tipodoc === "FACTURA" ||
                    (datosComprobante.nrodocumento && datosComprobante.nrodocumento.startsWith("F"));

                const tituloComprobante = esFactura ? "FACTURA ELECTRÓNICA" : "BOLETA DE VENTA ELECTRÓNICA";

                const precio = parseFloat(datosComprobante.precio || 0);
                const subtotal = (precio / 1.18).toFixed(2);
                const igv = (precio - precio / 1.18).toFixed(2);

                const montoPagado = parseFloat(datosComprobante.montopagado || 0);
                const vuelto = datosComprobante.tipopago === "EFECTIVO" && montoPagado > precio ?
                    (montoPagado - precio).toFixed(2) : "0.00";

                // Calcular el monto en letras
                const precioEntero = Math.floor(precio);
                const precioDecimales = Math.round((precio - precioEntero) * 100);
                const precioTexto = numeroALetras(precioEntero).toUpperCase() +
                    " CON " +
                    (precioDecimales === 0 ? "CERO" : numeroALetras(precioDecimales).toUpperCase()) +
                    " CENTAVOS";

                // CORRECCIÓN: Datos del cliente con prioridad a persona asociada si existe
                let clienteNombre, clienteTipoDoc, clienteNumDoc;

                // Priorizar datos de persona asociada si existe
                if (datosComprobante.cliente_natural && datosComprobante.cliente_nrodoc) {
                    clienteNombre = datosComprobante.cliente_natural;
                    clienteTipoDoc = obtenerTipoDocumentoTexto(datosComprobante.cliente_tipodoc || "DNI");
                    clienteNumDoc = datosComprobante.cliente_nrodoc;
                }
                // Si es empresa sin persona asociada
                else if (datosComprobante.tipocliente === 'EMPRESA' && datosComprobante.cliente_empresa) {
                    clienteNombre = datosComprobante.cliente_empresa || 'EMPRESA SIN NOMBRE';
                    clienteTipoDoc = 'RUC';
                    clienteNumDoc = datosComprobante.cliente_ruc || '-';
                }
                // Por defecto, usar datos del paciente
                else {
                    clienteNombre = datosComprobante.paciente || '-';
                    clienteTipoDoc = obtenerTipoDocumentoTexto(datosComprobante.paciente_tipodoc || "DNI");
                    clienteNumDoc = datosComprobante.paciente_nrodoc || '-';
                }

                // Información de empresa para facturas
                let seccionEmpresa = "";
                if (esFactura && datosComprobante.cliente_empresa && datosComprobante.cliente_ruc) {
                    seccionEmpresa = `
                <div class="seccion-titulo">INFORMACIÓN DE LA EMPRESA</div>
                <div class="separador"></div>
                <table class="info-tabla">
                    <tr>
                        <td>RUC:</td>
                        <td>${datosComprobante.cliente_ruc || "-"}</td>
                    </tr>
                    <tr>
                        <td>Razón Social:</td>
                        <td>${datosComprobante.cliente_empresa || "-"}</td>
                    </tr>
                    <tr>
                        <td>Dirección:</td>
                        <td>${datosComprobante.cliente_empresa_direccion || datosComprobante.cliente_direccion || "-"}</td>
                    </tr>
                </table>
                <div class="separador"></div>
            `;
                }

                // Información del paciente
                const pacienteNombre = datosComprobante.paciente || '-';
                const pacienteTipoDoc = obtenerTipoDocumentoTexto(datosComprobante.paciente_tipodoc || "DNI");
                const pacienteNumDoc = datosComprobante.paciente_nrodoc || '-';

                // Sección de pago variable según método
                let seccionPago = "";

                if (datosComprobante.tipopago === "EFECTIVO" && montoPagado > precio) {
                    seccionPago = `
                <tr>
                    <td>SON:</td>
                    <td>${precioTexto}</td>
                </tr>
                <tr>
                    <td>Monto Pagado:</td>
                    <td>S/ ${montoPagado.toFixed(2)}</td>
                </tr>
                <tr>
                    <td>Vuelto:</td>
                    <td>S/ ${vuelto}</td>
                </tr>
            `;
                } else {
                    seccionPago = `
                <tr>
                    <td>SON:</td>
                    <td>${precioTexto}</td>
                </tr>
                <tr>
                    <td>Monto Pagado:</td>
                    <td>S/ ${precio.toFixed(2)}</td>
                </tr>
            `;
                }

                // CORRECCIÓN: Formatear fechas y horas correctamente
                const fechaEmision = formatearFecha(datosComprobante.fechaemision);
                const horaEmision = formatearHora(datosComprobante.fechaemision);

                const fechaConsulta = formatearFecha(datosComprobante.fecha_consulta);
                const horaConsulta = formatearHora(datosComprobante.horaprogramada);

                // Preparar HTML optimizado para el comprobante
                const frameDoc = frameImpresion.contentDocument || frameImpresion.contentWindow.document;
                frameDoc.open();
                frameDoc.write(`
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <title>Comprobante de Pago</title>
                <style>${estilosOptimizados}</style>
            </head>
            <body>
                <div class="comprobante">
                    <!-- Título -->
                    <div class="titulo">${tituloComprobante}</div>
                    
                    <!-- Cabecera -->
                    <div class="comprobante-header">
                        <div>
                            <div class="logo">CLÍNICA<br>MÉDICA</div>
                            <div class="datos-empresa">
                                <div>RUC: 20123456789</div>
                                <div>Av. Principal 123, Lima</div>
                                <div>Tel: (01) 555-1234</div>
                            </div>
                        </div>
                        <div>
                            <div class="numero-comprobante">${datosComprobante.nrodocumento}</div>
                            <div class="fecha-hora">
                                <div>Fecha: ${fechaEmision}</div>
                                <div>Hora: ${horaEmision}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Separador -->
                    <div class="separador"></div>
                    
                    <!-- Sección Empresa (solo para facturas) -->
                    ${seccionEmpresa}
                    
                    <!-- Información del cliente y paciente -->
                    <div class="seccion-titulo">INFORMACIÓN DEL CLIENTE Y PACIENTE</div>
                    <div class="separador"></div>
                    <table class="info-tabla">
                        <tr>
                            <td>${clienteTipoDoc} Cliente:</td>
                            <td>${clienteNumDoc}</td>
                        </tr>
                        <tr>
                            <td>Cliente:</td>
                            <td>${clienteNombre}</td>
                        </tr>
                        <tr>
                            <td>${pacienteTipoDoc} Paciente:</td>
                            <td>${pacienteNumDoc}</td>
                        </tr>
                        <tr>
                            <td>Paciente:</td>
                            <td>${pacienteNombre}</td>
                        </tr>
                    </table>
                    
                    <!-- Separador -->
                    <div class="separador"></div>
                    
                    <!-- Detalle del servicio -->
                    <div class="servicio-detalle">
                        <div class="servicio-header">
                            <div>Descripción</div>
                            <div>Precio</div>
                        </div>
                        <div class="servicio-item">
                            <div class="servicio-desc">
                                <div>Consulta Médica - ${datosComprobante.especialidad || "Medicina General"}</div>
                                <div class="servicio-info">Doctor: ${datosComprobante.doctor || "-"}</div>
                                <div class="servicio-info">Fecha: ${fechaConsulta} / Hora: ${horaConsulta}</div>
                            </div>
                            <div class="servicio-precio">S/ ${precio.toFixed(2)}</div>
                        </div>
                    </div>
                    
                    <!-- Separador sólido -->
                    <div class="separador-solido"></div>
                    
                    <!-- Totales con mejor alineación -->
                    <div class="totales-seccion">
                        <div class="total-fila">
                            <div class="total-etiqueta">${esFactura ? 'VALOR DE VENTA:' : 'SUBTOTAL:'}</div>
                            <div class="total-valor">S/ ${subtotal}</div>
                        </div>
                        <div class="total-fila">
                            <div class="total-etiqueta">IGV (18%):</div>
                            <div class="total-valor">S/ ${igv}</div>
                        </div>
                        <div class="total-fila total-final">
                            <div class="total-etiqueta">TOTAL:</div>
                            <div class="total-valor">S/ ${precio.toFixed(2)}</div>
                        </div>
                    </div>
                    
                    <!-- Separador -->
                    <div class="separador"></div>
                    
                    <!-- Información de pago -->
                    <div class="seccion-titulo">INFORMACIÓN DE PAGO</div>
                    <div class="separador"></div>
                    <table class="info-tabla">
                        <tr>
                            <td>Forma de Pago:</td>
                            <td>${datosComprobante.tipopago || "EFECTIVO"}</td>
                        </tr>
                        ${seccionPago}
                    </table>
                    
                    <!-- Separador -->
                    <div class="separador"></div>
                    
                    <!-- Pie de página -->
                    <div class="pie-comprobante">
                        <div class="info-pie">
                            <div>Forma de Pago: ${datosComprobante.tipopago || "EFECTIVO"}</div>
                            <div>Atendido por: ${datosComprobante.usuario_venta || "Administrador"}</div>
                            <div>Caja: ${datosComprobante.id_cajero || "1"}</div>
                        </div>
                        <div class="qr-code">
                            ■ ■<br>
                            ■ ■
                        </div>
                    </div>
                    
                    <!-- Información legal -->
                    <div class="nota-legal">
                        Autorizado mediante Resolución de Intendencia N° 034-005-0005429/SUNAT<br>
                        Representación impresa de ${tituloComprobante}<br>
                        Puede consultar este documento en www.clinicamedica.com/consulta
                    </div>
                </div>
            </body>
            </html>
        `);
                frameDoc.close();

                // Imprimir después de que el contenido esté cargado
                setTimeout(() => {
                    frameImpresion.contentWindow.focus();
                    frameImpresion.contentWindow.print();

                    // Eliminar el iframe después de la impresión
                    setTimeout(() => {
                        document.body.removeChild(frameImpresion);
                    }, 1000);
                }, 500);
            } catch (error) {
                console.error("Error al imprimir comprobante:", error);

                Swal.fire({
                    icon: "error",
                    title: "Error de impresión",
                    text: "Ha ocurrido un problema al imprimir. Por favor, inténtelo nuevamente."
                });
            }
        }

        // Función para convertir números a texto
        function numeroALetras(numero) {
            const unidades = [
                "",
                "uno",
                "dos",
                "tres",
                "cuatro",
                "cinco",
                "seis",
                "siete",
                "ocho",
                "nueve"
            ];
            const decenas = [
                "",
                "diez",
                "veinte",
                "treinta",
                "cuarenta",
                "cincuenta",
                "sesenta",
                "setenta",
                "ochenta",
                "noventa"
            ];
            const especiales = [
                "diez",
                "once",
                "doce",
                "trece",
                "catorce",
                "quince",
                "dieciséis",
                "diecisiete",
                "dieciocho",
                "diecinueve"
            ];
            const centenas = [
                "",
                "ciento",
                "doscientos",
                "trescientos",
                "cuatrocientos",
                "quinientos",
                "seiscientos",
                "setecientos",
                "ochocientos",
                "novecientos"
            ];

            if (numero === 0) return "cero";
            if (numero < 0) return "menos " + numeroALetras(Math.abs(numero));

            let resultado = "";

            // Para miles
            if (numero >= 1000) {
                if (Math.floor(numero / 1000) === 1) {
                    resultado += "mil ";
                } else {
                    resultado += numeroALetras(Math.floor(numero / 1000)) + " mil ";
                }
                numero %= 1000;
            }

            // Para centenas
            if (numero >= 100) {
                if (numero === 100) {
                    resultado += "cien";
                } else {
                    resultado += centenas[Math.floor(numero / 100)] + " ";
                }
                numero %= 100;
            }

            // Para decenas y unidades
            if (numero > 0) {
                if (numero < 10) {
                    resultado += unidades[numero];
                } else if (numero < 20) {
                    resultado += especiales[numero - 10];
                } else {
                    const unidad = numero % 10;
                    if (unidad === 0) {
                        resultado += decenas[Math.floor(numero / 10)];
                    } else {
                        resultado += decenas[Math.floor(numero / 10)] + " y " + unidades[unidad];
                    }
                }
            }

            return resultado.trim();
        }

        // Inicializar la página
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener ID de cita de la URL
            const idcita = <?php echo $idcita; ?>;

            if (idcita) {
                // Cargar el comprobante
                cargarComprobante(idcita);

                // Configurar botón de impresión
                document.getElementById('btn-imprimir').addEventListener('click', function() {
                    imprimirComprobante();
                });
            } else {
                document.getElementById('contenidoComprobante').innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No se ha proporcionado un ID de cita válido.
                    <div class="mt-3">
                        <a href="gestionarCita.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Volver al listado
                        </a>
                    </div>
                </div>
            `;
            }
        });
    </script>
</body>

</html>