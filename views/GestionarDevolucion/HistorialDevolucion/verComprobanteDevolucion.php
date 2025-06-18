<?php
require_once '../../include/header.administrador.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comprobante de Devolución</title>

    <!-- CSS de Bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            background-color: #f4f6f9;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }

        /* Estilo para el recibo */
        .receipt-container {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            background-color: #fff;
            max-width: 800px;
            margin: 0 auto;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
        }

        .receipt-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .receipt-table td {
            padding: 8px 0;
        }

        .receipt-table .receipt-label {
            font-weight: 500;
            color: #666;
            width: 40%;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #777;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }

        /* Monto resaltado */
        .monto-devolucion {
            font-size: 1.8rem;
            font-weight: bold;
            color: #28a745;
        }

        /* Adaptación para impresión */
        @media print {
            body {
                background-color: #fff;
                margin: 0;
                padding: 0;
            }

            .container {
                width: 100%;
                max-width: none;
            }

            .card {
                box-shadow: none;
                border: none;
            }

            .card-header, .no-print, header, footer {
                display: none !important;
            }

            .receipt-container {
                border: none;
                padding: 0;
            }

            .btn, .btn-group {
                display: none !important;
            }
        }

        /* Spinner de carga */
        .loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 300px;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0"><i class="fas fa-receipt me-2"></i>Comprobante de Devolución</h2>
                        <div>
                            <a href="historialDevolucion.php" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left me-2"></i>Volver al Historial
                            </a>
                            <button id="btn-imprimir" class="btn btn-primary">
                                <i class="fas fa-print me-2"></i>Imprimir
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Contenedor de carga mientras se obtienen los datos -->
                        <div id="loading-container" class="loading-container">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="text-muted">Cargando comprobante de devolución...</p>
                        </div>

                        <!-- Contenedor para el comprobante (inicialmente oculto) -->
                        <div id="comprobante-container" class="d-none">
                            <!-- El comprobante se cargará dinámicamente aquí -->
                        </div>

                        <!-- Mensaje de error (inicialmente oculto) -->
                        <div id="error-container" class="alert alert-danger d-none">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <span id="error-message">Error al cargar el comprobante</span>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

    <script>
        $(document).ready(function() {
            // Función para formatear fecha (yyyy-mm-dd a dd/mm/yyyy)
            function formatearFecha(fechaStr) {
                if (!fechaStr) return '';

                try {
                    // Manejar diferentes formatos de fecha
                    // Si la fecha incluye hora (formato MySQL: "2023-06-15 14:30:00")
                    if (fechaStr.includes(' ')) {
                        fechaStr = fechaStr.split(' ')[0]; // Tomar solo la parte de la fecha
                    }

                    const partes = fechaStr.split('-');
                    if (partes.length !== 3) return fechaStr;

                    return `${partes[2]}/${partes[1]}/${partes[0]}`;
                } catch (e) {
                    console.error("Error al formatear fecha:", e);
                    return fechaStr;
                }
            }

            // Función para formatear hora (HH:MM:SS a HH:MM AM/PM)
            function formatearHora(horaStr) {
                if (!horaStr) return '';

                try {
                    const partes = horaStr.split(':');
                    if (partes.length < 2) return horaStr;

                    let horas = parseInt(partes[0], 10);
                    const minutos = partes[1].padStart(2, '0');

                    const periodo = horas >= 12 ? 'PM' : 'AM';

                    if (horas > 12) {
                        horas -= 12;
                    } else if (horas === 0) {
                        horas = 12;
                    }

                    return `${horas}:${minutos} ${periodo}`;
                } catch (e) {
                    console.error("Error al formatear hora:", e);
                    return horaStr;
                }
            }

            // Función para formatear motivo de devolución
            function formatearMotivo(motivo) {
                if (!motivo) return 'No especificado';

                const motivosMap = {
                    'SOLICITUD_PACIENTE': 'Solicitud del paciente',
                    'EMERGENCIA_MEDICA': 'Emergencia médica',
                    'PROBLEMA_HORARIO': 'Problema de horario',
                    'CANCELACION_DOCTOR': 'Cancelación por parte del doctor',
                    'OTRO': 'Otro motivo'
                };

                return motivosMap[motivo] || motivo;
            }

            // Obtener el ID de la devolución desde la URL
            function obtenerIdDevolucion() {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get('id');
            }

            // FUNCIÓN MODIFICADA: Cargar los datos del comprobante con debug
            function cargarComprobante() {
                const idDevolucion = obtenerIdDevolucion();
                
                if (!idDevolucion) {
                    mostrarError('No se especificó un ID de devolución válido');
                    return;
                }

                console.log("Cargando comprobante de devolución ID:", idDevolucion);

                // Mostrar loader
                $('#loading-container').removeClass('d-none');
                $('#comprobante-container').addClass('d-none');
                $('#error-container').addClass('d-none');

                // Realizar solicitud al servidor con URL más detallada para debugging
                const url = `../../../controllers/devolucion.controller.php?op=obtener&id=${idDevolucion}`;
                console.log("URL de solicitud:", url);

                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Error HTTP: ${response.status}`);
                        }
                        return response.text();
                    })
                    .then(rawText => {
                        try {
                            // Intentar parsear como JSON con manejo de errores mejorado
                            const data = JSON.parse(rawText);
                            console.log("Datos completos recibidos:", data);
                            
                            // Ocultar loader
                            $('#loading-container').addClass('d-none');
                            
                            if (data && data.status === true && data.data) {
                                // Mostrar comprobante
                                renderizarComprobante(data.data);
                            } else {
                                mostrarError('No se encontraron datos para el comprobante solicitado');
                            }
                        } catch (e) {
                            console.error("Error al parsear JSON:", e);
                            console.log("Texto recibido:", rawText);
                            mostrarError(`Error al procesar la respuesta: ${e.message}`);
                        }
                    })
                    .catch(error => {
                        console.error("Error al cargar comprobante:", error);
                        $('#loading-container').addClass('d-none');
                        mostrarError(`Error al cargar el comprobante: ${error.message}`);
                    });
            }

            // Mostrar mensaje de error
            function mostrarError(mensaje) {
                $('#error-message').text(mensaje);
                $('#error-container').removeClass('d-none');
                $('#comprobante-container').addClass('d-none');
            }

            // FUNCIÓN MODIFICADA: Renderizar el comprobante con los datos recibidos
            function renderizarComprobante(datos) {
                try {
                    console.log("Renderizando comprobante con datos:", datos);
                    
                    // Guardar los datos originales para la impresión
                    window.datosComprobanteOriginal = datos;
                    
                    // Formatear fecha y hora
                    const fechaHora = datos.fecha_devolucion ? new Date(datos.fecha_devolucion) : new Date();
                    const fechaHoraStr = fechaHora.toLocaleString('es-ES');
                    
                    // Formatear motivo
                    const motivoFormateado = formatearMotivo(datos.motivo);
                    
                    // CORRECCIÓN PRINCIPAL: Obtener correctamente el nombre del usuario autorizador
                    let nombreUsuario = '';
                    
                    // Datos del objeto usuario si viene como estructura completa
                    if (datos.usuario) {
                        if (typeof datos.usuario === 'object') {
                            if (datos.usuario.apellidos && datos.usuario.nombres) {
                                nombreUsuario = `${datos.usuario.apellidos}, ${datos.usuario.nombres}`;
                            } else if (datos.usuario.nomuser) {
                                nombreUsuario = datos.usuario.nomuser;
                            } else {
                                nombreUsuario = JSON.stringify(datos.usuario);
                            }
                        } else {
                            nombreUsuario = datos.usuario;
                        }
                    } 
                    // Buscar en diferentes campos donde podría estar la información
                    else if (datos.nombres && datos.apellidos) {
                        nombreUsuario = `${datos.apellidos}, ${datos.nombres}`;
                    } 
                    else if (datos.usuario_nombres && datos.usuario_apellidos) {
                        nombreUsuario = `${datos.usuario_apellidos}, ${datos.usuario_nombres}`;
                    } 
                    else if (datos.nombre_usuario && datos.apellido_usuario) {
                        nombreUsuario = `${datos.apellido_usuario}, ${datos.nombre_usuario}`;
                    } 
                    else if (datos.usuario_nombre_apellido) {
                        nombreUsuario = datos.usuario_nombre_apellido;
                    } 
                    else if (datos.usuario_registro) {
                        // Si solo tenemos el usuario, lo usamos tal cual
                        nombreUsuario = datos.usuario_registro;
                    } 
                    else if (datos.idusuario) {
                        // Si solo tenemos ID, mostrar algo mejor que "No especificado"
                        nombreUsuario = `Usuario ID: ${datos.idusuario}`;
                    } 
                    else {
                        nombreUsuario = 'No especificado';
                    }

                    // Mostrar en consola lo que encontramos para debugging
                    console.log("Nombre de usuario encontrado:", nombreUsuario);
                    
                    // Construir HTML del comprobante
                    const comprobanteHTML = `
                        <div class="receipt-container">
                            <div class="receipt-header">
                                <h4>Comprobante de Devolución</h4>
                                <p class="mb-0">Clínica Médica</p>
                                <p>${fechaHoraStr}</p>
                            </div>

                            <div class="receipt-body">
                                <table class="receipt-table">
                                    <tr>
                                        <td class="receipt-label">N° de Comprobante:</td>
                                        <td>${datos.numero_comprobante || datos.iddevolucion || 'No especificado'}</td>
                                    </tr>
                                    <tr>
                                        <td class="receipt-label">Paciente:</td>
                                        <td>${datos.nombre_paciente || 'No especificado'}</td>
                                    </tr>
                                    <tr>
                                        <td class="receipt-label">Documento:</td>
                                        <td>${datos.tipo_documento_paciente || ''}: ${datos.numero_documento_paciente || 'No especificado'}</td>
                                    </tr>
                                    <tr>
                                        <td class="receipt-label">Cita Cancelada:</td>
                                        <td>
                                            ${datos.fecha_cita ? formatearFecha(datos.fecha_cita) : 'No especificado'} 
                                            ${datos.hora_cita ? '- ' + formatearHora(datos.hora_cita) : ''}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="receipt-label">Monto Devuelto:</td>
                                        <td class="monto-devolucion">S/. ${parseFloat(datos.monto || 0).toFixed(2)}</td>
                                    </tr>
                                    <tr>
                                        <td class="receipt-label">Método de Devolución:</td>
                                        <td>${datos.metodo || 'No especificado'}</td>
                                    </tr>
                                    <tr>
                                        <td class="receipt-label">Motivo:</td>
                                        <td>${motivoFormateado}</td>
                                    </tr>
                                    <tr>
                                        <td class="receipt-label">Autorizado por:</td>
                                        <td>${nombreUsuario}</td>
                                    </tr>
                                    <tr>
                                        <td class="receipt-label">Observaciones:</td>
                                        <td>${datos.observaciones || 'Sin observaciones'}</td>
                                    </tr>
                                </table>
                            </div>

                            <div class="receipt-footer">
                                <p>Gracias por su comprensión</p>
                                <p class="mb-0">Este comprobante es un documento válido de devolución</p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center mt-4 no-print">
                            <button type="button" class="btn btn-primary" id="btn-imprimir-interno">
                                <i class="fas fa-print me-2"></i>Imprimir Comprobante
                            </button>
                        </div>
                    `;
                    
                    // Mostrar el comprobante
                    $('#comprobante-container').html(comprobanteHTML).removeClass('d-none');
                    
                    // Configurar evento de impresión interno
                    $('#btn-imprimir-interno').on('click', function() {
                        imprimirComprobanteDevolucion(window.datosComprobanteOriginal);
                    });
                    
                } catch (error) {
                    console.error("Error al renderizar comprobante:", error);
                    mostrarError(`Error al mostrar el comprobante: ${error.message}`);
                }
            }

            // NUEVA FUNCIÓN: Impresión optimizada del comprobante de devolución
            function imprimirComprobanteDevolucion(datos) {
                try {
                    console.log("Generando comprobante de devolución para impresión:", datos);
                    
                    // Verificar si tenemos datos válidos
                    if (!datos) {
                        throw new Error("No se encontraron datos para generar el comprobante");
                    }
                    
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
                    
                    // Estilos específicos para impresión del comprobante
                    const estilosOptimizados = `
                        @page {
                            size: A4 portrait;
                            margin: 1cm;
                        }
                        
                        body {
                            margin: 0;
                            padding: 0;
                            font-family: Arial, sans-serif;
                            font-size: 10pt;
                            line-height: 1.3;
                        }
                        
                        .comprobante {
                            width: 100%;
                            max-width: 19cm;
                            margin: 0 auto;
                            padding: 0.5cm;
                            border: 1px solid #ccc;
                            box-shadow: 0 0 5px rgba(0,0,0,0.1);
                            box-sizing: border-box;
                            page-break-inside: avoid;
                        }
                        
                        .comprobante-header {
                            text-align: center;
                            margin-bottom: 0.5cm;
                        }
                        
                        .comprobante-header h1 {
                            font-size: 14pt;
                            margin: 0 0 0.2cm 0;
                            font-weight: bold;
                        }
                        
                        .comprobante-header p {
                            margin: 0.1cm 0;
                            font-size: 9pt;
                        }
                        
                        .separador {
                            border-top: 1px dashed #ccc;
                            margin: 0.5cm 0;
                        }
                        
                        .comprobante-datos {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        
                        .comprobante-datos td {
                            padding: 0.2cm 0;
                            vertical-align: top;
                        }
                        
                        .etiqueta {
                            font-weight: bold;
                            color: #555;
                            width: 35%;
                        }
                        
                        .monto-devolucion {
                            font-size: 14pt;
                            font-weight: bold;
                            color: #28a745;
                        }
                        
                        .comprobante-footer {
                            text-align: center;
                            margin-top: 0.5cm;
                            padding-top: 0.3cm;
                            border-top: 1px dashed #ccc;
                            font-size: 8pt;
                            color: #666;
                        }
                    `;
                    
                    // Formatear fecha y hora
                    const fechaHora = datos.fecha_devolucion ? new Date(datos.fecha_devolucion) : new Date();
                    const fechaHoraStr = fechaHora.toLocaleString('es-ES');
                    
                    // Formatear motivo
                    const motivoFormateado = formatearMotivo(datos.motivo);
                    
                    // Obtener nombre del usuario autorizador
                    let nombreUsuario = '';
                    
                    // Priorizar el nuevo campo usuario_nombre_apellido
                    if (datos.usuario_nombre_apellido) {
                        nombreUsuario = datos.usuario_nombre_apellido;
                    }
                    // Alternativa: Construir con apellidos y nombres individuales
                    else if (datos.usuario_apellidos && datos.usuario_nombres) {
                        nombreUsuario = `${datos.usuario_apellidos}, ${datos.usuario_nombres}`;
                    }
                    else if (datos.usuario && typeof datos.usuario === 'object') {
                        if (datos.usuario.apellidos && datos.usuario.nombres) {
                            nombreUsuario = `${datos.usuario.apellidos}, ${datos.usuario.nombres}`;
                        } else if (datos.usuario.nomuser) {
                            nombreUsuario = datos.usuario.nomuser;
                        }
                    }
                    else if (datos.usuario_registro) {
                        nombreUsuario = datos.usuario_registro;
                    }
                    else if (datos.idusuario) {
                        nombreUsuario = `Usuario ID: ${datos.idusuario}`;
                    }
                    else {
                        nombreUsuario = 'No especificado';
                    }
                    
                    // Preparar documento para impresión
                    const frameDoc = frameImpresion.contentDocument || frameImpresion.contentWindow.document;
                    frameDoc.open();
                    frameDoc.write(`
                        <!DOCTYPE html>
                        <html lang="es">
                        <head>
                            <meta charset="UTF-8">
                            <title>Comprobante de Devolución</title>
                            <style>${estilosOptimizados}</style>
                        </head>
                        <body>
                            <div class="comprobante">
                                <div class="comprobante-header">
                                    <h1>Comprobante de Devolución</h1>
                                    <p>Clínica Médica</p>
                                    <p>${fechaHoraStr}</p>
                                </div>
                                
                                <div class="separador"></div>
                                
                                <table class="comprobante-datos">
                                    <tr>
                                        <td class="etiqueta">N° de Comprobante:</td>
                                        <td>${datos.numero_comprobante || datos.iddevolucion || 'No especificado'}</td>
                                    </tr>
                                    <tr>
                                        <td class="etiqueta">Paciente:</td>
                                        <td>${datos.nombre_paciente || 'No especificado'}</td>
                                    </tr>
                                    <tr>
                                        <td class="etiqueta">Documento:</td>
                                        <td>${datos.tipo_documento_paciente || ''}: ${datos.numero_documento_paciente || 'No especificado'}</td>
                                    </tr>
                                    <tr>
                                        <td class="etiqueta">Cita Cancelada:</td>
                                        <td>
                                            ${datos.fecha_cita ? formatearFecha(datos.fecha_cita) : 'No especificado'} 
                                            ${datos.hora_cita ? '- ' + formatearHora(datos.hora_cita) : ''}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="etiqueta">Monto Devuelto:</td>
                                        <td class="monto-devolucion">S/. ${parseFloat(datos.monto || 0).toFixed(2)}</td>
                                    </tr>
                                    <tr>
                                        <td class="etiqueta">Método de Devolución:</td>
                                        <td>${datos.metodo || 'No especificado'}</td>
                                    </tr>
                                    <tr>
                                        <td class="etiqueta">Motivo:</td>
                                        <td>${motivoFormateado}</td>
                                    </tr>
                                    <tr>
                                        <td class="etiqueta">Autorizado por:</td>
                                        <td>${nombreUsuario}</td>
                                    </tr>
                                    <tr>
                                        <td class="etiqueta">Observaciones:</td>
                                        <td>${datos.observaciones || 'Sin observaciones'}</td>
                                    </tr>
                                </table>
                                
                                <div class="separador"></div>
                                
                                <div class="comprobante-footer">
                                    <p>Gracias por su comprensión</p>
                                    <p>Este comprobante es un documento válido de devolución</p>
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
                    console.error("Error al imprimir comprobante de devolución:", error);
                    
                    // Mostrar mensaje de error amigable
                    Swal.fire({
                        icon: "error",
                        title: "Error de impresión",
                        text: "Ha ocurrido un problema al imprimir. Por favor, inténtelo nuevamente."
                    });
                }
            }

            // Configurar eventos
            $('#btn-imprimir').on('click', function() {
                const datos = window.datosComprobanteOriginal;
                if (datos) {
                    imprimirComprobanteDevolucion(datos);
                } else {
                    Swal.fire({
                        icon: "warning",
                        title: "Espere un momento",
                        text: "Espere a que se cargue el comprobante para poder imprimirlo"
                    });
                }
            });

            // Cargar comprobante al iniciar
            cargarComprobante();
        });
    </script>
</body>

</html>