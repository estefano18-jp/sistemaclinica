/**
 * sistemaclinica/js/reservacioncitas/procesarPago.js
 */

/**
 * procesarPago.js - Gestión completa del proceso de pago de citas médicas
 * Este archivo maneja todo el flujo desde la validación hasta el registro en la BD
 */

// ===============================================================
// FUNCIÓN PRINCIPAL: Procesa el pago y finaliza la reserva
// ===============================================================
async function procesarPago() {
  // Evitar procesamiento duplicado
  if (window.procesandoPago) {
    console.log("Ya hay un pago en proceso, ignorando esta solicitud");
    return;
  }

  // Marcar que estamos procesando un pago
  window.procesandoPago = true;

  try {
    // Mostrar indicador de carga
    mostrarCargando();

    console.log("Iniciando procesamiento de pago...");

    // ======================================================
    // 1. OBTENER Y VALIDAR DATOS BÁSICOS DE LA CITA
    // ======================================================
    const idpaciente = document.getElementById("idpaciente").value;

    // Obtener datos de la cita
    const doctor = document.getElementById("doctor").value;
    const especialidad = document.getElementById("especialidad").value;
    const fecha = document.getElementById("fecha-reserva").value;
    const hora = document.getElementById("hora-hidden").value;

    // NUEVO: Guardar datos de la cita para uso posterior en el comprobante
    window.datosCitaReservada = {
      fecha: fecha,
      hora: hora,
      iddoctor: doctor,
      idespecialidad: especialidad,
    };

    console.log(
      "Datos de cita guardados para el comprobante:",
      window.datosCitaReservada
    );

    // Validar campos requeridos de la cita
    if (!doctor || !especialidad || !fecha || !hora || !idpaciente) {
      ocultarCargando();
      Swal.fire({
        icon: "error",
        title: "Datos incompletos",
        text: "Faltan datos de la cita. Por favor complete todos los campos.",
      });
      window.procesandoPago = false;
      return;
    }

    // ======================================================
    // 2. OBTENER DATOS DEL PAGO
    // ======================================================
    const tipoCliente = document.getElementById("tipoCliente").value;

    // MEJORA CRÍTICA: Obtener y validar el tipo de pago de manera más robusta
    const tipoPagoSelect = document.getElementById("metodoPago");
    let tipoPago = "EFECTIVO"; // Valor por defecto seguro

    if (tipoPagoSelect && tipoPagoSelect.value) {
      // Obtener el valor seleccionado y mapearlo
      tipoPago = mapearMetodoPago(tipoPagoSelect.value);

      // Validación adicional para asegurar que tenemos un valor válido
      if (!tipoPago || tipoPago === "") {
        console.warn(
          "Tipo de pago inválido después del mapeo, usando EFECTIVO"
        );
        tipoPago = "EFECTIVO";
      }
    }

    console.log("Método de pago seleccionado:", tipoPagoSelect?.value);
    console.log("Método de pago mapeado para BD (VERIFICADO):", tipoPago);

    const tipoComprobante = document.getElementById("tipoComprobante").value;

    // CORRECCIÓN: Obtener monto pagado de manera más robusta
    let montoPagado =
      parseFloat(document.getElementById("montoPagado").value) || 0;

    // Obtener el precio de la especialidad
    const especialidadSelect = document.getElementById("especialidad");
    const especialidadOption =
      especialidadSelect.options[especialidadSelect.selectedIndex];
    let precio = 0;

    // MEJORA: Obtener precio de manera más robusta
    if (
      especialidadOption &&
      especialidadOption.dataset &&
      especialidadOption.dataset.precio
    ) {
      precio = parseFloat(especialidadOption.dataset.precio);
    } else {
      // Intentar obtener el precio del elemento en el DOM
      const precioElement = document.getElementById("precioConsulta");
      if (precioElement) {
        const precioText = precioElement.textContent || precioElement.innerText;
        const matches = precioText.match(/[\d,.]+/);
        if (matches && matches.length > 0) {
          precio = parseFloat(
            matches[0].replace(/[^0-9.]/g, "").replace(",", ".")
          );
        }
      }
    }

    console.log("Datos base de la cita validados:", {
      idpaciente,
      doctor,
      especialidad,
      fecha,
      hora,
      tipoCliente,
      tipoPago,
      tipoComprobante,
      montoPagado,
      precio,
    });

    // CORRECCIÓN: Para pagos no efectivo, asegurar que el monto pagado sea igual al precio
    if (tipoPago !== "EFECTIVO") {
      montoPagado = precio;
      console.log("Ajustando monto pagado para pago no-efectivo:", montoPagado);

      // Guardar en variable global para uso posterior
      if (typeof window.montoExactoPagado === "undefined") {
        window.montoExactoPagado = {};
      }
      window.montoExactoPagado.valor = precio;
      window.montoExactoPagado.metodo = tipoPago;
    }
    // Validar que el monto pagado sea suficiente cuando el método de pago es efectivo
    else if (tipoPago === "EFECTIVO") {
      // Si no se ingresó un monto
      if (montoPagado <= 0) {
        ocultarCargando();
        Swal.fire({
          icon: "error",
          title: "Monto inválido",
          text: "Por favor, ingrese el monto pagado por el cliente.",
        });
        window.procesandoPago = false;
        return;
      }

      // Si el monto es insuficiente
      if (montoPagado < precio) {
        ocultarCargando();
        Swal.fire({
          icon: "error",
          title: "Monto insuficiente",
          text: `El monto pagado (S/. ${montoPagado.toFixed(
            2
          )}) es menor que el precio total (S/. ${precio.toFixed(2)}).`,
        });
        window.procesandoPago = false;
        return;
      }
    }

    // ======================================================
    // 3. DETERMINAR TIPO DE CLIENTE Y REGISTRAR SI ES NECESARIO
    // ======================================================
    let idcliente = 0;
    // CORRECCIÓN IMPORTANTE: Declarar clienteFactura aquí, al inicio del procesamiento
    let clienteFactura = 0;

    // 3.1 CASO: Cliente es el mismo paciente
    if (tipoCliente === "personal") {
      try {
        // Obtener el idcliente del paciente
        idcliente = await obtenerIdCliente(idpaciente);
        console.log("Cliente (paciente) obtenido o registrado:", idcliente);

        // Asignar el mismo valor a clienteFactura inicialmente
        clienteFactura = idcliente;
      } catch (error) {
        console.error("Error al obtener cliente del paciente:", error);
        ocultarCargando();
        Swal.fire({
          icon: "error",
          title: "Error",
          text:
            "No se pudo obtener o registrar al paciente como cliente: " +
            error.message,
        });
        window.procesandoPago = false;
        return;
      }
    }
    // 3.2 CASO: Cliente es una empresa
    else if (tipoCliente === "empresa") {
      try {
        // Datos de la empresa
        const ruc = document.getElementById("ruc")?.value || "";
        const idempresa = document.getElementById("idempresaPago")?.value || "";
        const razonSocial = document.getElementById("razonSocial")?.value || "";
        const direccion =
          document.getElementById("direccionEmpresa")?.value || "";
        const nombreComercial =
          document.getElementById("nombreComercial")?.value || razonSocial;
        const telefono =
          document.getElementById("telefonoEmpresa")?.value || "";
        const email = document.getElementById("emailEmpresa")?.value || "";

        if (!ruc) {
          ocultarCargando();
          Swal.fire({
            icon: "error",
            title: "Datos incompletos",
            text: "Falta el RUC de la empresa",
          });
          window.procesandoPago = false;
          return;
        }

        // VALIDACIÓN MEJORADA: Verificar formato de RUC primero
        if (!validarRUC(ruc)) {
          ocultarCargando();
          Swal.fire({
            icon: "error",
            title: "RUC inválido",
            text: "El RUC debe tener 11 dígitos y comenzar con 10, 15, 17 o 20.",
          });
          window.procesandoPago = false;
          return;
        }

        if (!razonSocial) {
          ocultarCargando();
          Swal.fire({
            icon: "error",
            title: "Datos incompletos",
            text: "Falta la razón social de la empresa",
          });
          window.procesandoPago = false;
          return;
        }

        // Variable para almacenar ID de empresa
        let empresaId = null;

        // Verificar si la función actualizarEmpresa está definida
        if (typeof actualizarEmpresa !== "function") {
          // Definir la función si no existe
          window.actualizarEmpresa = async function (datos) {
            try {
              console.log("Actualizando empresa con datos:", datos);

              const formData = new FormData();

              // Agregar los campos a actualizar
              for (const key in datos) {
                // Excluir propiedades de control como _silentMode
                if (
                  datos[key] !== undefined &&
                  datos[key] !== null &&
                  key !== "_silentMode"
                ) {
                  formData.append(key, datos[key]);
                }
              }

              // Enviar datos al servidor
              mostrarCargando();
              const response = await fetchData(
                "/sistemaclinica/controllers/empresa.controller.php?op=actualizar",
                {
                  method: "POST",
                  body: formData,
                }
              );
              ocultarCargando();

              return response;
            } catch (error) {
              console.error("Error en actualizarEmpresa:", error);
              return {
                status: false,
                mensaje: "Error al actualizar empresa: " + error.message,
              };
            }
          };
        }

        // CASO 1: Si ya tenemos el ID de la empresa, actualizarla
        if (idempresa) {
          console.log("Actualizando empresa existente con ID:", idempresa);

          // Preparar datos para la actualización
          const datosActualizacion = {
            idempresa: idempresa,
            razonsocial: razonSocial,
            direccion: direccion,
            nombrecomercial: nombreComercial,
            telefono: telefono,
            email: email,
            _silentMode: true, // Para evitar alertas duplicadas
          };

          // Actualizar la empresa existente
          const resultadoActualizacion = await actualizarEmpresa(
            datosActualizacion
          );

          if (resultadoActualizacion && resultadoActualizacion.status) {
            console.log(
              "Empresa actualizada correctamente:",
              resultadoActualizacion
            );
            empresaId = idempresa;
          } else {
            console.error(
              "Error al actualizar empresa:",
              resultadoActualizacion
            );
            // Usar ID existente de todos modos
            empresaId = idempresa;
          }
        }
        // CASO 2: Sin ID, verificar si existe por RUC
        else {
          // Primero verificar si ya existe la empresa
          const empresaData = await buscarEmpresaPorRuc(ruc);

          if (empresaData) {
            console.log("Empresa encontrada por RUC:", empresaData);

            // Usar el ID encontrado
            empresaId = empresaData.idempresa;

            // Actualizar datos si hay cambios
            if (
              razonSocial !== empresaData.razonsocial ||
              direccion !== empresaData.direccion ||
              nombreComercial !== empresaData.nombrecomercial ||
              telefono !== empresaData.telefono ||
              email !== empresaData.email
            ) {
              console.log(
                "Detectados cambios en los datos de la empresa, actualizando..."
              );

              const datosActualizacion = {
                idempresa: empresaId,
                razonsocial: razonSocial,
                direccion: direccion,
                nombrecomercial: nombreComercial,
                telefono: telefono,
                email: email,
                _silentMode: true,
              };

              const resultadoActualizacion = await actualizarEmpresa(
                datosActualizacion
              );

              if (resultadoActualizacion && resultadoActualizacion.status) {
                console.log(
                  "Empresa actualizada correctamente:",
                  resultadoActualizacion
                );
              } else {
                console.error(
                  "Error al actualizar empresa:",
                  resultadoActualizacion
                );
                // Continuar con el ID encontrado
              }
            }
          } else {
            // Registrar nueva empresa
            const datosEmpresa = {
              ruc,
              razonsocial: razonSocial,
              direccion: direccion || "", // Permitir direcciones vacías
              nombrecomercial: nombreComercial,
              telefono: telefono || "", // Permitir teléfonos vacíos
              email: email || "", // Permitir emails vacíos
              _silentMode: true, // Evitar alertas duplicadas
            };

            // CORRECCIÓN IMPORTANTE: Manejar la respuesta incluso si la empresa ya existe
            const resultadoEmpresa = await registrarEmpresa(datosEmpresa);

            // CAMBIO CLAVE: Verificar si tenemos un ID de empresa, sin importar el status
            if (resultadoEmpresa && resultadoEmpresa.idempresa) {
              console.log(
                "Empresa registrada con ID:",
                resultadoEmpresa.idempresa
              );
              empresaId = resultadoEmpresa.idempresa;
            } else {
              console.error(
                "Error al registrar nueva empresa:",
                resultadoEmpresa
              );
              throw new Error(
                "No se pudo registrar la empresa: " +
                  (resultadoEmpresa?.mensaje || "Error desconocido")
              );
            }
          }
        }

        // Continuar con el flujo existente usando empresaId
        // Verificar si ya está registrado como cliente
        const clienteEmpresaData = await buscarClientePorEmpresa(empresaId);

        if (
          clienteEmpresaData &&
          clienteEmpresaData.status &&
          clienteEmpresaData.data
        ) {
          idcliente = clienteEmpresaData.data.idcliente;
          // También asignar a clienteFactura
          clienteFactura = idcliente;
          console.log("Cliente empresa existente encontrado:", idcliente);
        } else {
          console.log(
            "Empresa existente no registrada como cliente, registrando..."
          );
          // Registrar empresa existente como cliente
          const datosClienteFactura = {
            idempresa: empresaId,
            tipocliente: "EMPRESA",
          };
          console.log(
            "Datos para registro de cliente empresa:",
            datosClienteFactura
          );
          const resultadoClienteFactura =
            await registrarClienteEmpresaExistente(datosClienteFactura);

          if (resultadoClienteFactura && resultadoClienteFactura.status) {
            idcliente = resultadoClienteFactura.idcliente;
            // También asignar a clienteFactura
            clienteFactura = idcliente;
            console.log(
              "Empresa existente registrada como cliente para factura:",
              idcliente
            );
          } else {
            console.error(
              "Error al registrar cliente empresa para factura:",
              resultadoClienteFactura
            );
            throw new Error(
              "No se pudo registrar la empresa como cliente: " +
                (resultadoClienteFactura?.mensaje || "Error desconocido")
            );
          }
        }
      } catch (error) {
        console.error("Error general en procesamiento de empresa:", error);
        ocultarCargando();
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Error al procesar empresa: " + error.message,
        });
        window.procesandoPago = false;
        return;
      }
    }
    // 3.3 CASO: Cliente es un tercero
    else if (tipoCliente === "tercero") {
      try {
        // Obtener y validar datos del pagador
        const tipoDocPagadorUI =
          document.getElementById("tipoDocPagador")?.value || "dni";
        const tipoDocPagador = obtenerTipoDocumentoDB(tipoDocPagadorUI);
        const nroDocPagador =
          document.getElementById("documentoPagador")?.value || "";
        const nombresPagador =
          document.getElementById("nombresPagador")?.value || "";
        const apellidosPagador =
          document.getElementById("apellidosPagador")?.value || "";

        console.log("DATOS DEL PAGADOR:", {
          tipoDocUI: tipoDocPagadorUI,
          tipoDB: tipoDocPagador,
          nroDoc: nroDocPagador,
          nombres: nombresPagador,
          apellidos: apellidosPagador,
        });

        // Validar campos requeridos
        if (
          !tipoDocPagador ||
          !nroDocPagador ||
          !nombresPagador ||
          !apellidosPagador
        ) {
          ocultarCargando();
          Swal.fire({
            icon: "error",
            title: "Datos incompletos",
            text: "Faltan datos del pagador. Por favor complete tipo de documento, número, nombres y apellidos.",
          });
          window.procesandoPago = false;
          return;
        }

        // Validar formato del documento
        if (!validarDocumentoPagador(tipoDocPagadorUI, nroDocPagador)) {
          ocultarCargando();
          Swal.fire({
            icon: "error",
            title: "Documento inválido",
            text: obtenerMensajeValidacionDocumentoPagador(tipoDocPagadorUI),
          });
          window.procesandoPago = false;
          return;
        }

        // NUEVO: Variable para almacenar el ID de persona del pagador
        let idPersonaPagador = null;

        // Buscar cliente existente
        console.log(
          "Buscando cliente con documento:",
          tipoDocPagador,
          nroDocPagador
        );
        const clientePagadorData = await buscarClientePorDocumento(
          tipoDocPagador,
          nroDocPagador
        );

        if (clientePagadorData && clientePagadorData.idcliente) {
          idcliente = clientePagadorData.idcliente;
          // También asignar a clienteFactura
          clienteFactura = idcliente;
          console.log("Cliente pagador existente encontrado:", idcliente);

          // NUEVO: Guardar el ID de persona del cliente encontrado
          if (clientePagadorData.idpersona) {
            idPersonaPagador = clientePagadorData.idpersona;
            console.log(
              "ID de persona del pagador encontrado:",
              idPersonaPagador
            );
          }
        } else {
          // Registrar nuevo cliente pagador
          const datosPagador = {
            tipodoc: tipoDocPagador,
            nrodoc: nroDocPagador,
            nombres: nombresPagador,
            apellidos: apellidosPagador,
          };

          console.log(
            "Registrando nuevo cliente pagador con datos:",
            datosPagador
          );

          const resultadoPagador = await registrarClientePagador(datosPagador);

          console.log("Resultado del registro de pagador:", resultadoPagador);

          if (
            resultadoPagador &&
            resultadoPagador.status &&
            resultadoPagador.idcliente
          ) {
            idcliente = resultadoPagador.idcliente;
            // También asignar a clienteFactura
            clienteFactura = idcliente;
            console.log("Nuevo cliente pagador registrado con ID:", idcliente);

            // NUEVO: Guardar el ID de persona del nuevo cliente si está disponible
            if (resultadoPagador.idpersona) {
              idPersonaPagador = resultadoPagador.idpersona;
              console.log("ID de persona del nuevo pagador:", idPersonaPagador);
            }
          } else {
            console.error("Error en registro de pagador:", resultadoPagador);
            throw new Error(
              "No se pudo registrar al pagador como cliente: " +
                (resultadoPagador?.mensaje || "Error al procesar la solicitud")
            );
          }
        }

        // NUEVO: Si el comprobante es factura, necesitamos manejar la información de empresa
        if (tipoComprobante === "factura") {
          // Obtener los datos para la factura con validación robusta
          const rucFactura = document.getElementById("rucFactura")?.value || "";
          const razonSocialFactura =
            document.getElementById("razonSocialFactura")?.value || "";
          const direccionFactura =
            document.getElementById("direccionFactura")?.value || "";

          // Validar datos mínimos requeridos para factura
          if (!rucFactura || !razonSocialFactura) {
            ocultarCargando();
            Swal.fire({
              icon: "error",
              title: "Datos incompletos para factura",
              text: "Debe ingresar el RUC y Razón Social para emitir una factura",
            });
            window.procesandoPago = false;
            return;
          }

          // Validar formato del RUC
          if (!validarRUC(rucFactura)) {
            ocultarCargando();
            Swal.fire({
              icon: "error",
              title: "RUC inválido para factura",
              text: "El RUC debe tener 11 dígitos y comenzar con 10, 15, 17 o 20.",
            });
            window.procesandoPago = false;
            return;
          }

          // Verificar si hay ID de empresa existente o si necesitamos registrar una nueva
          let idempresaFactura =
            document.getElementById("idempresaFactura")?.value || "";

          try {
            console.log("Procesando factura con RUC:", rucFactura);

            if (!idempresaFactura) {
              // Verificar si la empresa ya existe por RUC
              const empresaExistente = await buscarEmpresaPorRuc(rucFactura);

              if (empresaExistente) {
                console.log(
                  "Empresa existente encontrada por RUC:",
                  empresaExistente
                );
                // Si la empresa ya existe, usar sus datos
                idempresaFactura = empresaExistente.idempresa;

                // SOLUCIÓN: Actualizar el cliente existente en lugar de crear uno nuevo
                console.log(
                  "Actualizando cliente existente con empresa:",
                  idempresaFactura
                );
                const resultadoActualizacion =
                  await actualizarClienteConEmpresa(
                    idcliente,
                    idempresaFactura
                  );

                if (resultadoActualizacion && resultadoActualizacion.status) {
                  // Usar el mismo cliente para la factura
                  clienteFactura = idcliente;
                  console.log(
                    "Cliente actualizado con empresa para factura:",
                    clienteFactura
                  );
                } else {
                  console.error(
                    "Error al actualizar cliente con empresa:",
                    resultadoActualizacion
                  );
                  throw new Error(
                    "No se pudo actualizar el cliente con la empresa: " +
                      (resultadoActualizacion?.mensaje || "Error desconocido")
                  );
                }
              } else {
                // Registrar nueva empresa
                const datosEmpresaFactura = {
                  ruc: rucFactura,
                  razonsocial: razonSocialFactura,
                  direccion: direccionFactura || "",
                  _silentMode: true,
                };

                console.log(
                  "Registrando empresa para factura:",
                  datosEmpresaFactura
                );
                const resultadoEmpresaFactura = await registrarEmpresa(
                  datosEmpresaFactura
                );

                if (
                  resultadoEmpresaFactura &&
                  resultadoEmpresaFactura.idempresa
                ) {
                  idempresaFactura = resultadoEmpresaFactura.idempresa;
                  console.log(
                    "Empresa registrada para factura con ID:",
                    idempresaFactura
                  );

                  // SOLUCIÓN: Actualizar el cliente existente en lugar de crear uno nuevo
                  console.log(
                    "Actualizando cliente existente con nueva empresa:",
                    idempresaFactura
                  );
                  const resultadoActualizacion =
                    await actualizarClienteConEmpresa(
                      idcliente,
                      idempresaFactura
                    );

                  if (resultadoActualizacion && resultadoActualizacion.status) {
                    // Usar el mismo cliente para la factura
                    clienteFactura = idcliente;
                    console.log(
                      "Cliente actualizado con nueva empresa para factura:",
                      clienteFactura
                    );
                  } else {
                    console.error(
                      "Error al actualizar cliente con nueva empresa:",
                      resultadoActualizacion
                    );
                    throw new Error(
                      "No se pudo actualizar el cliente con la empresa: " +
                        (resultadoActualizacion?.mensaje || "Error desconocido")
                    );
                  }
                } else {
                  console.error(
                    "Error al registrar nueva empresa para factura:",
                    resultadoEmpresaFactura
                  );
                  throw new Error(
                    "No se pudo registrar la empresa: " +
                      (resultadoEmpresaFactura?.mensaje || "Error desconocido")
                  );
                }
              }
            } else {
              console.log("Usando empresa existente con ID:", idempresaFactura);

              // SOLUCIÓN: Actualizar el cliente existente en lugar de crear uno nuevo
              console.log(
                "Actualizando cliente existente con empresa:",
                idempresaFactura
              );
              const resultadoActualizacion = await actualizarClienteConEmpresa(
                idcliente,
                idempresaFactura
              );

              if (resultadoActualizacion && resultadoActualizacion.status) {
                // Usar el mismo cliente para la factura
                clienteFactura = idcliente;
                console.log(
                  "Cliente actualizado con empresa para factura:",
                  clienteFactura
                );
              } else {
                console.error(
                  "Error al actualizar cliente con empresa:",
                  resultadoActualizacion
                );
                // CORRECCIÓN: Si falla, usar el cliente original como fallback
                clienteFactura = idcliente;
                console.warn(
                  "Usando cliente original como fallback:",
                  clienteFactura
                );
              }
            }
          } catch (error) {
            console.error("Error al procesar empresa para factura:", error);
            ocultarCargando();
            Swal.fire({
              icon: "error",
              title: "Error",
              text:
                "Error al procesar la empresa para factura: " + error.message,
            });
            window.procesandoPago = false;
            return;
          }
        }
      } catch (error) {
        console.error("Error completo al procesar pagador tercero:", error);
        ocultarCargando();
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Error al registrar al pagador como cliente: " + error.message,
        });
        window.procesandoPago = false;
        return;
      }
    }

    // VERIFICACIÓN CRÍTICA: Comprobar que tenemos un ID de cliente válido
    if (!idcliente) {
      console.error("No se obtuvo un ID de cliente válido");
      ocultarCargando();
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudo obtener un cliente válido para la venta",
      });
      window.procesandoPago = false;
      return;
    }

    console.log("Cliente validado con ID:", idcliente);

    // ======================================================
    // 4. REGISTRAR LA CITA
    // ======================================================
    const datosCita = {
      iddoctor: doctor,
      idespecialidad: especialidad,
      fecha: fecha,
      hora: hora,
      idpaciente: idpaciente,
      observaciones: "",
    };

    console.log("Registrando cita con datos:", datosCita);

    const resultadoCita = await registrarCita(datosCita);

    if (!resultadoCita || !resultadoCita.status) {
      console.error("Error al registrar cita:", resultadoCita);
      ocultarCargando();
      Swal.fire({
        icon: "error",
        title: "Error",
        text:
          "No se pudo registrar la cita: " +
          (resultadoCita?.mensaje || "Error al procesar la solicitud"),
      });
      window.procesandoPago = false;
      return;
    }

    console.log("Cita registrada correctamente:", resultadoCita);

    // ======================================================
    // 5. PROCESAR FACTURA SI ES NECESARIO
    // ======================================================
    // CORREGIDO: Ya no necesitamos volver a declarar clienteFactura aquí
    // porque lo declaramos al inicio de la función

    // Solo actualizar si aún no tiene valor y no estamos en los casos anteriores
    if (clienteFactura === 0) {
      clienteFactura = idcliente; // Por defecto, usar el mismo cliente
    }

    if (tipoComprobante === "factura" && tipoCliente !== "tercero") {
      // Obtener los datos para la factura con validación robusta
      const rucFactura = document.getElementById("rucFactura")?.value || "";
      const razonSocialFactura =
        document.getElementById("razonSocialFactura")?.value || "";
      const direccionFactura =
        document.getElementById("direccionFactura")?.value || "";

      // Validar datos mínimos requeridos para factura
      if (!rucFactura || !razonSocialFactura) {
        ocultarCargando();
        Swal.fire({
          icon: "error",
          title: "Datos incompletos para factura",
          text: "Debe ingresar el RUC y Razón Social para emitir una factura",
        });
        window.procesandoPago = false;
        return;
      }

      // Validar formato del RUC
      if (!validarRUC(rucFactura)) {
        ocultarCargando();
        Swal.fire({
          icon: "error",
          title: "RUC inválido para factura",
          text: "El RUC debe tener 11 dígitos y comenzar con 10, 15, 17 o 20.",
        });
        window.procesandoPago = false;
        return;
      }

      // Verificar si hay ID de empresa existente o si necesitamos registrar una nueva
      let idempresaFactura =
        document.getElementById("idempresaFactura")?.value || "";

      try {
        console.log("Procesando factura con RUC:", rucFactura);

        if (!idempresaFactura) {
          // Verificar si la empresa ya existe por RUC
          const empresaExistente = await buscarEmpresaPorRuc(rucFactura);

          if (empresaExistente) {
            console.log(
              "Empresa existente encontrada por RUC:",
              empresaExistente
            );
            // Si la empresa ya existe, usar sus datos
            idempresaFactura = empresaExistente.idempresa;

            // Verificar si esta empresa ya está registrada como cliente
            const clienteEmpresaData = await buscarClientePorEmpresa(
              idempresaFactura
            );

            if (
              clienteEmpresaData &&
              clienteEmpresaData.status &&
              clienteEmpresaData.data
            ) {
              // Usar este cliente para la factura
              clienteFactura = clienteEmpresaData.data.idcliente;
              console.log(
                "Cliente empresa existente para factura:",
                clienteFactura
              );

              // NUEVO: Si estamos en pago personal, actualizar el cliente para incluir idpersona
              if (tipoCliente === "personal" && window.idpersonaPaciente) {
                console.log(
                  "Actualizando cliente empresa para incluir persona del paciente:",
                  window.idpersonaPaciente
                );
                await actualizarClienteConPersona(
                  clienteFactura,
                  window.idpersonaPaciente
                );
              }
            } else {
              console.log(
                "Empresa existente no registrada como cliente, registrando..."
              );
              // Registrar empresa existente como cliente
              const datosClienteFactura = {
                idempresa: idempresaFactura,
                tipocliente: "EMPRESA", // CORRECCIÓN: Siempre en mayúsculas
                // NUEVO: Incluir idpersona del paciente si es pago personal
                ...(tipoCliente === "personal" && window.idpersonaPaciente
                  ? { idpersona: window.idpersonaPaciente }
                  : {}),
              };
              console.log(
                "Datos para registro de cliente factura:",
                datosClienteFactura
              );
              const resultadoClienteFactura =
                await registrarClienteEmpresaExistente(datosClienteFactura);

              if (resultadoClienteFactura && resultadoClienteFactura.status) {
                clienteFactura = resultadoClienteFactura.idcliente;
                console.log(
                  "Empresa existente registrada como cliente para factura:",
                  clienteFactura
                );
              } else {
                console.error(
                  "Error al registrar cliente empresa para factura:",
                  resultadoClienteFactura
                );
                throw new Error(
                  "No se pudo registrar la empresa como cliente: " +
                    (resultadoClienteFactura?.mensaje || "Error desconocido")
                );
              }
            }
          } else {
            // SOLUCIÓN: Registrar nueva empresa con todos los datos requeridos
            const datosEmpresaFactura = {
              ruc: rucFactura,
              razonsocial: razonSocialFactura,
              direccion: direccionFactura || "", // Permitir direcciones vacías
              _silentMode: true, // Evitar alertas duplicadas
            };

            console.log(
              "Registrando empresa para factura:",
              datosEmpresaFactura
            );

            // CORRECCIÓN: Manejar la respuesta incluso si la empresa ya existe
            const resultadoEmpresaFactura = await registrarEmpresa(
              datosEmpresaFactura
            );

            // CAMBIO CLAVE: Verificar idempresa sin importar el status (puede ser "ya existe")
            if (resultadoEmpresaFactura && resultadoEmpresaFactura.idempresa) {
              idempresaFactura = resultadoEmpresaFactura.idempresa;
              console.log(
                "Empresa registrada para factura con ID:",
                idempresaFactura
              );

              // Registrar como cliente de tipo empresa
              const datosClienteFactura = {
                idempresa: idempresaFactura,
                tipocliente: "EMPRESA",
                // NUEVO: Incluir idpersona del paciente si es pago personal
                ...(tipoCliente === "personal" && window.idpersonaPaciente
                  ? { idpersona: window.idpersonaPaciente }
                  : {}),
              };

              const resultadoClienteFactura =
                await registrarClienteEmpresaExistente(datosClienteFactura);

              if (resultadoClienteFactura && resultadoClienteFactura.status) {
                clienteFactura = resultadoClienteFactura.idcliente;
                console.log(
                  "Nueva empresa registrada como cliente para factura:",
                  clienteFactura
                );
              } else {
                console.error(
                  "Error al registrar nuevo cliente empresa para factura:",
                  resultadoClienteFactura
                );
                throw new Error(
                  "No se pudo registrar la empresa como cliente: " +
                    (resultadoClienteFactura?.mensaje || "Error desconocido")
                );
              }
            } else {
              console.error(
                "Error al registrar nueva empresa para factura:",
                resultadoEmpresaFactura
              );
              throw new Error(
                "No se pudo registrar la empresa: " +
                  (resultadoEmpresaFactura?.mensaje || "Error desconocido")
              );
            }
          }
        } else {
          console.log("Usando empresa existente con ID:", idempresaFactura);

          // Verificar si esta empresa ya está registrada como cliente
          const clienteEmpresaData = await buscarClientePorEmpresa(
            idempresaFactura
          );

          if (
            clienteEmpresaData &&
            clienteEmpresaData.status &&
            clienteEmpresaData.data
          ) {
            // Usar este cliente para la factura
            clienteFactura = clienteEmpresaData.data.idcliente;
            console.log(
              "Cliente empresa existente para factura:",
              clienteFactura
            );

            // NUEVO: Si estamos en pago personal, actualizar el cliente para incluir idpersona
            if (tipoCliente === "personal" && window.idpersonaPaciente) {
              console.log(
                "Actualizando cliente empresa para incluir persona del paciente:",
                window.idpersonaPaciente
              );
              await actualizarClienteConPersona(
                clienteFactura,
                window.idpersonaPaciente
              );
            }
          } else {
            console.log("Registrando empresa existente como cliente...");
            // Registrar empresa existente como cliente
            const datosClienteFactura = {
              idempresa: idempresaFactura,
              tipocliente: "EMPRESA",
              // NUEVO: Incluir idpersona del paciente si es pago personal
              ...(tipoCliente === "personal" && window.idpersonaPaciente
                ? { idpersona: window.idpersonaPaciente }
                : {}),
            };

            const resultadoClienteFactura =
              await registrarClienteEmpresaExistente(datosClienteFactura);

            if (
              resultadoClienteFactura &&
              (resultadoClienteFactura.status === true ||
                resultadoClienteFactura.status === "true")
            ) {
              // Asegurar que tengamos un idcliente
              clienteFactura =
                resultadoClienteFactura.idcliente ||
                resultadoClienteFactura.id ||
                idcliente; // Usar el original como fallback
              console.log(
                "Empresa existente registrada como cliente para factura:",
                clienteFactura
              );
            } else {
              console.error(
                "Error detallado al registrar cliente empresa:",
                resultadoClienteFactura
              );

              // CORRECCIÓN: Si falla, usar el cliente original como fallback en lugar de lanzar error
              clienteFactura = idcliente;
              console.warn(
                "Usando cliente original como fallback:",
                clienteFactura
              );
            }
          }
        }
      } catch (error) {
        console.error("Error al procesar empresa para factura:", error);
        ocultarCargando();
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Error al procesar la empresa para factura: " + error.message,
        });
        window.procesandoPago = false;
        return;
      }
    }

    // ======================================================
    // 6. REGISTRAR VENTA/PAGO
    // ======================================================

    // Datos de la venta (usando clienteFactura si aplica)
    const datosVenta = {
      idcliente: clienteFactura, // CAMBIO IMPORTANTE: Usar clienteFactura de factura
      idconsulta: resultadoCita.idconsulta,
      precio: precio,
      tipocomprobante: tipoComprobante.toUpperCase(),
      tipopago: tipoPago, // CORRECCIÓN: Usar el valor ya mapeado y validado
      montopagado: montoPagado, // CORRECCIÓN: Usar el valor ajustado
    };

    // VERIFICACIÓN FINAL CRÍTICA: Asegurar que tipopago tiene un valor válido
    if (!datosVenta.tipopago || datosVenta.tipopago === "") {
      console.warn(
        "TipoPago sigue siendo inválido antes de enviar, estableciendo EFECTIVO"
      );
      datosVenta.tipopago = "EFECTIVO";
    } else {
      // ADICIONAL: Verificar que sea un valor de los permitidos
      const valoresValidos = ["EFECTIVO", "TRANSFERENCIA", "YAPE", "PLIN"];
      if (!valoresValidos.includes(datosVenta.tipopago)) {
        console.warn(
          `Tipo de pago "${datosVenta.tipopago}" no es válido, usando EFECTIVO`
        );
        datosVenta.tipopago = "EFECTIVO";
      }
    }

    console.log("Registrando venta con datos finales:", datosVenta);

    // Registrar la venta con los datos de factura
    const resultadoVenta = await registrarVenta(datosVenta);

    if (!resultadoVenta || !resultadoVenta.status) {
      console.error("Error al registrar venta:", resultadoVenta);
      ocultarCargando();
      Swal.fire({
        icon: "error",
        title: "Error",
        text:
          "No se pudo registrar la venta: " +
          (resultadoVenta?.mensaje || "Error al procesar la solicitud"),
      });
      window.procesandoPago = false;
      return;
    }

    console.log("Venta registrada correctamente:", resultadoVenta);

    // ======================================================
    // 7. FINALIZAR PROCESO Y MOSTRAR MENSAJE
    // ======================================================
    ocultarCargando();

    // MODIFICADO: Evitar cierre al hacer clic fuera
    Swal.fire({
      icon: "success",
      title: "¡Pago procesado correctamente!",
      text: "La cita ha sido registrada y el pago procesado con éxito.",
      confirmButtonText: "Ver comprobante",
      allowOutsideClick: false, // NUEVO: Evitar cierre al hacer clic fuera
      allowEscapeKey: false, // NUEVO: Evitar cierre al presionar ESC
    }).then((result) => {
      if (result.isConfirmed) {
        // Obtener y mostrar el comprobante
        mostrarComprobante(resultadoVenta.idventa);
      }

      // NUEVO: Asegurar que el tipo de documento por defecto sea DNI
      setTimeout(() => {
        const tipoDocumento = document.getElementById("tipo-documento");
        if (tipoDocumento) {
          // Buscar y seleccionar la opción "DNI"
          for (let i = 0; i < tipoDocumento.options.length; i++) {
            if (tipoDocumento.options[i].value === "DNI") {
              tipoDocumento.selectedIndex = i;
              console.log(
                "Tipo de documento establecido a DNI después del pago"
              );
              break;
            }
          }
        }
      }, 500);
    });

    // Cerrar el modal de pago
    const modalPagoCita = bootstrap.Modal.getInstance(
      document.getElementById("modalPagoCita")
    );
    if (modalPagoCita) {
      modalPagoCita.hide();
    }

    // Limpiar formulario de reserva
    limpiarFormularioReserva();

    // MODIFICADO: Actualizar calendario usando nuestra nueva función
    if (typeof recargarSoloCalendario === "function") {
      recargarSoloCalendario();
    } else {
      // Fallback si la función no está disponible
      if (window.calendario) {
        window.calendario.refetchEvents();
      }
    }
  } catch (error) {
    console.error("Error completo al procesar pago:", error);

    ocultarCargando();

    Swal.fire({
      icon: "error",
      title: "Error al procesar el pago",
      text: error.message || "Ocurrió un error inesperado al procesar el pago",
    });
  } finally {
    // CORRECCIÓN: Siempre liberar el flag al terminar
    setTimeout(() => {
      window.procesandoPago = false;
    }, 500);
  }
}

/**
 * Función global para garantizar coherencia en el mapeo de tipos de pago
 * Esta función debe ser usada en TODOS los puntos donde se procesa el tipo de pago
 * @param {string} metodoPago - El valor del método de pago a normalizar
 * @return {string} - El valor normalizado para la base de datos
 */
function normalizarTipoPago(metodoPago) {
  if (!metodoPago) return "EFECTIVO";

  // Normalizar valor (trim y lowercase)
  const metodoNormalizado = metodoPago.trim().toLowerCase();

  // Mapeo único y centralizado
  const TIPOS_PAGO = {
    efectivo: "EFECTIVO",
    transferencia: "TRANSFERENCIA",
    yape: "YAPE",
    plin: "PLIN",
  };

  // Usar el valor mapeado o el valor por defecto
  const tipoPagoNormalizado = TIPOS_PAGO[metodoNormalizado] || "EFECTIVO";

  // Log informativo para debugging
  console.log(
    `Tipo de pago normalizado: "${metodoPago}" → "${tipoPagoNormalizado}"`
  );

  return tipoPagoNormalizado;
}

/**
 * Muestra el comprobante de pago
 * @param {number} idventa ID de la venta
 */
async function mostrarComprobante(idventa) {
  try {
    if (!idventa) {
      throw new Error("ID de venta no proporcionado");
    }

    mostrarCargando();

    // Obtener datos del comprobante
    const datosComprobante = await obtenerComprobante(idventa);

    ocultarCargando();

    if (datosComprobante) {
      // Usar la nueva función mejorada para mostrar el comprobante
      // Esta función manejará la diferenciación entre boletas y facturas
      mostrarModalComprobante(datosComprobante);
    } else {
      throw new Error("No se pudieron obtener los datos del comprobante");
    }
  } catch (error) {
    console.error("Error al mostrar comprobante:", error);

    ocultarCargando();

    Swal.fire({
      icon: "error",
      title: "Error",
      text: error.message || "No se pudo mostrar el comprobante",
    });
  }
}

// ===============================================================
// FUNCIONES AUXILIARES: Validación y procesamiento de datos
// ===============================================================
/**
 * Registra un cliente usando una persona existente
 * @param {Object} datos Datos del cliente
 * @return {Promise} Resultado del registro
 */
async function registrarClienteExistente(datos) {
  try {
    const formData = new FormData();
    formData.append("idpersona", datos.idpersona);
    formData.append("tipocliente", datos.tipocliente);

    if (datos.idempresa) {
      formData.append("idempresa", datos.idempresa);
    }

    const response = await fetchData(
      "../../../controllers/cliente.controller.php?op=registrar_existente",
      {
        method: "POST",
        body: formData,
      }
    );

    return response;
  } catch (error) {
    console.error("Error en registrarClienteExistente:", error);
    return null;
  }
}
/**
 * Registra un cliente empresa usando una empresa existente
 * @param {Object} datos Datos del cliente empresa (idempresa, tipocliente)
 * @return {Promise} Resultado del registro
 */
async function registrarClienteEmpresaExistente(datos) {
  try {
    // Validación de datos de entrada
    if (!datos.idempresa) {
      console.error(
        "Error: ID de empresa no proporcionado para registrar cliente"
      );
      return {
        status: false,
        mensaje: "ID de empresa no proporcionado",
      };
    }

    // Logs para depuración
    console.log("Registrando cliente para empresa ID:", datos.idempresa);
    if (datos.idpersona) {
      console.log("Incluyendo persona ID:", datos.idpersona);
    }

    // Crear FormData con TODOS los campos requeridos
    const formData = new FormData();

    // CORRECCIÓN CRÍTICA: Asegurar que tipocliente siempre esté en MAYÚSCULAS
    formData.append("idempresa", datos.idempresa);
    formData.append("tipocliente", "EMPRESA"); // FIJO en mayúsculas, como lo espera el controlador

    // NUEVO: Incluir idpersona si está presente
    if (datos.idpersona) {
      formData.append("idpersona", datos.idpersona);
    }

    // Logs para depuración
    console.log("Datos enviados:", {
      idempresa: datos.idempresa,
      tipocliente: "EMPRESA",
      idpersona: datos.idpersona || undefined,
    });

    // CORRECCIÓN: Especificar explícitamente todas las opciones de fetch
    const options = {
      method: "POST",
      body: formData,
      cache: "no-cache",
      redirect: "follow",
    };

    // Llamada al servidor
    const response = await fetchData(
      "/sistemaclinica/controllers/cliente.controller.php?op=registrar_existente",
      options
    );

    // Análisis detallado de la respuesta
    console.log("Respuesta de registro de cliente empresa:", response);

    // Si la respuesta es exitosa pero no tiene idcliente, agregarlo
    if (response && response.status && !response.idcliente && response.id) {
      response.idcliente = response.id; // Asegurar compatibilidad
    }

    return response;
  } catch (error) {
    console.error("Error en registrarClienteEmpresaExistente:", error);
    return {
      status: false,
      mensaje: "Error al registrar cliente: " + error.message,
    };
  }
}

async function buscarClientePorPersona(idpersona) {
  try {
    if (!idpersona) return null;

    const response = await fetchData(
      `../../../controllers/cliente.controller.php?op=buscar_por_persona&idpersona=${idpersona}`
    );

    return response;
  } catch (error) {
    console.error("Error en buscarClientePorPersona:", error);
    return null;
  }
}

/**
 * Busca un cliente por ID de empresa
 * @param {number} idempresa ID de la empresa
 * @return {Promise} Datos del cliente o null
 */
async function buscarClientePorEmpresa(idempresa) {
  try {
    if (!idempresa) {
      console.warn("ID de empresa no proporcionado en buscarClientePorEmpresa");
      return null;
    }

    console.log("Buscando cliente por ID de empresa:", idempresa);

    // URL para buscar cliente por empresa
    const url = `/sistemaclinica/controllers/cliente.controller.php?op=buscar_por_empresa&idempresa=${idempresa}`;

    mostrarCargando();
    const response = await fetchData(url);
    ocultarCargando();

    console.log("Respuesta de búsqueda por empresa:", response);

    if (response && response.status && response.data) {
      return response;
    }

    return null;
  } catch (error) {
    ocultarCargando();
    console.error("Error en buscarClientePorEmpresa:", error);
    return null;
  }
}
/**
 * Valida que todos los datos necesarios para el pago estén completos
 */
function validarDatosPago(datosReserva, datosPago) {
  // Validar datos de reserva
  if (
    !datosReserva.iddoctor ||
    !datosReserva.idespecialidad ||
    !datosReserva.fecha ||
    !datosReserva.hora ||
    !datosReserva.idpaciente
  ) {
    Swal.fire({
      icon: "warning",
      title: "Datos incompletos",
      text: "Por favor, complete todos los campos de la reserva",
    });
    return false;
  }

  // Validar datos específicos según el tipo de cliente
  if (datosPago.tipoCliente === "empresa") {
    const ruc = document.getElementById("ruc").value;
    const razonSocial = document.getElementById("razonSocial").value;

    if (!ruc || !razonSocial) {
      Swal.fire({
        icon: "warning",
        title: "Datos incompletos",
        text: "Por favor, complete los datos de la empresa",
      });
      return false;
    }

    if (!validarRUC(ruc)) {
      Swal.fire({
        icon: "warning",
        title: "RUC inválido",
        text: "Por favor, ingrese un RUC válido",
      });
      return false;
    }
  } else if (datosPago.tipoCliente === "tercero") {
    const tipoDoc = document.getElementById("tipoDocPagador").value;
    const documento = document.getElementById("documentoPagador").value;
    const nombres = document.getElementById("nombresPagador").value;
    const apellidos = document.getElementById("apellidosPagador").value;

    if (!documento || !nombres || !apellidos) {
      Swal.fire({
        icon: "warning",
        title: "Datos incompletos",
        text: "Por favor, complete los datos del pagador",
      });
      return false;
    }

    // Validar formato del documento según tipo
    if (!validarDocumentoPagador(tipoDoc, documento)) {
      Swal.fire({
        icon: "warning",
        title: "Documento inválido",
        text: obtenerMensajeValidacionDocumentoPagador(tipoDoc),
      });
      return false;
    }
  }

  // Validar que el monto pagado sea suficiente cuando el método es efectivo
  if (datosPago.metodoPago === "efectivo") {
    const montoPagado =
      parseFloat(document.getElementById("montoPagado").value) || 0;
    const precioTotal = obtenerPrecioConsulta();

    if (montoPagado < precioTotal) {
      Swal.fire({
        icon: "warning",
        title: "Monto insuficiente",
        text: `El monto pagado (S/ ${montoPagado.toFixed(
          2
        )}) debe ser igual o mayor al precio de la atención (S/ ${precioTotal.toFixed(
          2
        )})`,
      });
      return false;
    }
  }

  return true;
}

async function obtenerIdCliente(idpaciente) {
  try {
    // Obtener información del paciente
    console.log(`Obteniendo información del paciente con ID: ${idpaciente}`);
    const response = await fetchData(
      `../../../controllers/paciente.controller.php?operacion=obtener&idpaciente=${idpaciente}`
    );

    if (!response || !response.status || !response.paciente) {
      console.error("Error al obtener paciente:", response);
      throw new Error("No se pudo obtener información del paciente");
    }

    const idpersona = response.paciente.idpersona;
    console.log(`ID de persona del paciente obtenido: ${idpersona}`);

    // NUEVO: Guardar el idpersona en una variable global para uso posterior
    window.idpersonaPaciente = idpersona;

    // Buscar si la persona ya es cliente
    console.log(`Verificando si la persona ya es cliente...`);
    const responseCliente = await fetchData(
      `../../../controllers/cliente.controller.php?op=buscar_por_persona&idpersona=${idpersona}`
    );

    if (
      responseCliente &&
      responseCliente.status &&
      responseCliente.data &&
      responseCliente.data.idcliente
    ) {
      console.log(
        `Cliente existente encontrado con ID: ${responseCliente.data.idcliente}`
      );
      return responseCliente.data.idcliente;
    }

    // Si no es cliente, registrarla como cliente
    console.log(
      `Persona no registrada como cliente, procediendo a registrar...`
    );
    const formData = new FormData();
    formData.append("idpersona", idpersona);
    formData.append("tipocliente", "NATURAL");

    // Registrar explícitamente los datos enviados para debug
    console.log("Datos enviados para registrar cliente:", {
      idpersona: idpersona,
      tipocliente: "NATURAL",
    });

    const resultadoRegistro = await fetchData(
      "../../../controllers/cliente.controller.php?op=registrar_existente",
      {
        method: "POST",
        body: formData,
      }
    );

    console.log("Respuesta de registro de cliente:", resultadoRegistro);

    if (!resultadoRegistro || !resultadoRegistro.status) {
      const mensajeError = resultadoRegistro?.mensaje || "Error desconocido";
      console.error(`Error al registrar cliente: ${mensajeError}`);
      throw new Error(
        `No se pudo registrar al paciente como cliente: ${mensajeError}`
      );
    }

    if (!resultadoRegistro.idcliente) {
      console.warn(
        "No se recibió ID de cliente en la respuesta, intentando usar otras propiedades"
      );
      // Intenta buscar el ID en diferentes propiedades (por compatibilidad)
      const idcliente = resultadoRegistro.idcliente || resultadoRegistro.id;

      if (!idcliente) {
        throw new Error("No se pudo obtener ID del cliente registrado");
      }

      return idcliente;
    }

    console.log(
      `Cliente registrado con éxito con ID: ${resultadoRegistro.idcliente}`
    );
    return resultadoRegistro.idcliente;
  } catch (error) {
    console.error("Error en obtenerIdCliente:", error);
    throw new Error(
      "No se pudo obtener o registrar al paciente como cliente: " +
        error.message
    );
  }
}
/**
 * Mapea el método de pago del formulario al formato de la base de datos
 */
function mapearMetodoPago(metodoPago) {
  if (!metodoPago) return "EFECTIVO";

  // Normalizar valor de entrada (eliminar espacios, a minúsculas)
  const metodoNormalizado = metodoPago.trim().toLowerCase();

  // Mapeo explícito y garantizado para cada valor posible
  const mapeo = {
    efectivo: "EFECTIVO",
    transferencia: "TRANSFERENCIA",
    yape: "YAPE", // Ahora la BD acepta este valor
    plin: "PLIN", // Ahora la BD acepta este valor
  };

  // Si existe en el mapeo, devolver el valor correspondiente
  if (mapeo[metodoNormalizado]) {
    console.log(
      `Método de pago "${metodoPago}" mapeado a "${mapeo[metodoNormalizado]}"`
    );
    return mapeo[metodoNormalizado];
  }

  // Verificar si ya está en mayúsculas (formato BD)
  const valoresValidos = ["EFECTIVO", "TRANSFERENCIA", "YAPE", "PLIN"];
  if (valoresValidos.includes(metodoPago.toUpperCase())) {
    return metodoPago.toUpperCase();
  }

  // Si no hay coincidencia, retornar valor por defecto
  console.warn(
    `Método de pago "${metodoPago}" no reconocido, usando EFECTIVO como valor por defecto`
  );
  return "EFECTIVO";
}

/**
 * Mapea el tipo de documento UI al formato de la base de datos
 */
function obtenerTipoDocumentoDB(tipoUI) {
  // Si el valor es nulo o indefinido, usar DNI por defecto
  if (!tipoUI) {
    console.warn("Tipo de documento no proporcionado, usando DNI por defecto");
    return "DNI";
  }

  // Normalizar el valor (quitar espacios extra, convertir a minúsculas)
  const tipoNormalizado = tipoUI.trim().toLowerCase();

  // Mapa de conversión de UI a DB
  const mapeo = {
    dni: "DNI",
    pasaporte: "PASAPORTE",
    carnet: "CARNET DE EXTRANJERIA",
    otro: "OTRO",
  };

  // Si existe en el mapeo, retornar el valor correspondiente
  if (mapeo[tipoNormalizado]) {
    return mapeo[tipoNormalizado];
  }

  // Verificar si ya está en el formato de la BD
  const valoresDB = ["DNI", "PASAPORTE", "CARNET DE EXTRANJERIA", "OTRO"];
  const tipoUpperCase = tipoUI.toUpperCase();
  if (valoresDB.includes(tipoUpperCase)) {
    return tipoUpperCase;
  }

  // Si no se encontró una conversión válida, usar DNI por defecto
  console.warn(
    `Tipo de documento '${tipoUI}' no reconocido, usando DNI por defecto`
  );
  return "DNI";
}
/**
 * Función para validar el documento del pagador según tipo
 * MODIFICADO: Ahora valida exactamente el formato requerido sin flexibilidad
 */
function validarDocumentoPagador(tipoDoc, nroDoc) {
  if (!nroDoc) return false;

  // Normalizar el tipo de documento
  const tipo = tipoDoc.toLowerCase().trim();

  switch (tipo) {
    case "dni":
      return /^\d{8}$/.test(nroDoc); // Exactamente 8 dígitos numéricos
    case "pasaporte":
      return /^[A-Za-z0-9]{6,12}$/.test(nroDoc); // Entre 6 y 12 caracteres alfanuméricos
    case "carnet":
      return /^[A-Za-z0-9]{9}$/.test(nroDoc); // Exactamente 9 caracteres alfanuméricos
    case "otro":
      return nroDoc.length >= 1; // Al menos 1 carácter
    default:
      return false;
  }
}
/**
 * Función para obtener mensajes de validación según tipo de documento del pagador
 */
function obtenerMensajeValidacionDocumentoPagador(tipoDoc) {
  const tipo = tipoDoc.toLowerCase().trim();

  switch (tipo) {
    case "dni":
      return "El DNI debe tener exactamente 8 dígitos numéricos";
    case "pasaporte":
      return "El pasaporte debe tener entre 6 y 12 caracteres alfanuméricos";
    case "carnet":
      return "El carnet de extranjería debe tener exactamente 9 caracteres alfanuméricos";
    case "otro":
      return "El documento debe tener al menos 1 carácter";
    default:
      return "Formato de documento inválido";
  }
}

/**
 * Obtiene el precio de la consulta del formulario de manera robusta
 * @returns {number} Precio de la consulta
 */
function obtenerPrecioConsulta() {
  // ESTRATEGIA 1: Obtener precio desde el elemento precioConsulta
  const precioElement = document.getElementById("precioConsulta");
  if (precioElement) {
    const precioTexto = precioElement.textContent || precioElement.innerText;
    // Extraer solo los números y puntos decimales
    const matches = precioTexto.match(/[\d,.]+/);
    if (matches && matches.length > 0) {
      // Asegurar formato correcto para parseFloat
      const precioLimpio = matches[0].replace(/[^0-9.]/g, "").replace(",", ".");
      // Convertir a número
      const precio = parseFloat(precioLimpio);
      if (!isNaN(precio) && precio > 0) {
        return precio;
      }
    }
  }

  // ESTRATEGIA 2: Obtener precio del select de especialidad
  const especialidadSelect = document.getElementById("especialidad");
  if (especialidadSelect && especialidadSelect.selectedIndex >= 0) {
    const selectedOption =
      especialidadSelect.options[especialidadSelect.selectedIndex];
    if (selectedOption && selectedOption.dataset.precio) {
      // Parsear el precio asegurando que sea un número
      const precio = parseFloat(selectedOption.dataset.precio);
      if (!isNaN(precio) && precio > 0) {
        return precio;
      }
    }
  }

  // ESTRATEGIA 3: Obtener precio del elemento precioTotal
  const precioTotalElement = document.getElementById("precioTotal");
  if (precioTotalElement) {
    const precioTexto =
      precioTotalElement.textContent || precioTotalElement.innerText;
    const matches = precioTexto.match(/[\d,.]+/);
    if (matches && matches.length > 0) {
      const precioLimpio = matches[0].replace(/[^0-9.]/g, "").replace(",", ".");
      const precio = parseFloat(precioLimpio);
      if (!isNaN(precio) && precio > 0) {
        return precio;
      }
    }
  }

  // Si no se encontró un precio válido, revisar por nombre de especialidad
  const especialidadConsulta = document.getElementById("especialidadConsulta");
  if (especialidadConsulta) {
    const especialidadText = especialidadConsulta.textContent;
    // Tabla de precios por especialidad como fallback
    const preciosPorEspecialidad = {
      "Medicina General": 50.0,
      Cardiología: 120.0,
      Pediatría: 80.0,
      Ginecología: 100.0,
      Traumatología: 90.0,
      Dermatología: 85.0,
      Oftalmología: 75.0,
      Neurología: 150.0,
      Psiquiatría: 130.0,
      Odontología: 70.0,
    };

    for (const [nombre, precio] of Object.entries(preciosPorEspecialidad)) {
      if (especialidadText && especialidadText.includes(nombre)) {
        return precio;
      }
    }
  }

  // Valor por defecto si todo falla
  return 0;
}

/**
 * Actualiza el calendario después del pago exitoso
 */
function actualizarCalendarioDespuesDePago() {
  if (window.calendario) {
    const filtros = {};

    // Mantener filtros existentes
    const especialidadTop = document.getElementById("especialidad-top");
    const doctorTop = document.getElementById("doctor-top");
    const fechaTop = document.getElementById("fecha-top");

    if (especialidadTop && especialidadTop.value) {
      filtros.idespecialidad = especialidadTop.value;
    }

    if (doctorTop && doctorTop.value) {
      filtros.iddoctor = doctorTop.value;
    }

    if (fechaTop && fechaTop.value) {
      filtros.fechaInicio = fechaTop.value;
      filtros.fechaFin = fechaTop.value;
    }

    // Recargar calendario con filtros usando ruta absoluta
    setTimeout(() => {
      try {
        console.log("Actualizando calendario con filtros:", filtros);
        cargarCitasCalendario(window.calendario, filtros);
      } catch (error) {
        console.error("Error al actualizar calendario:", error);
      }
    }, 1000);
  }
}

/**
 * Registra un cliente pagador (tercero)
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
        // CLAVE: Incluir también el idpersona si está disponible
        idpersona: clienteExistente.idpersona || null,
      };
    }

    // Si no existe, registrar nuevo cliente
    const formData = new FormData();

    // CORRECCIÓN IMPORTANTE: Asegurarse de que los nombres de los campos coincidan exactamente
    // con lo que espera el controlador PHP
    formData.append("tipodoc", datos.tipodoc); // Debe coincidir exactamente con el controlador
    formData.append("nrodoc", datos.nrodoc); // Debe coincidir exactamente con el controlador
    formData.append("nombres", datos.nombres);
    formData.append("apellidos", datos.apellidos);

    // Agregar campos opcionales solo si están presentes y no están vacíos
    if (datos.telefono && datos.telefono.trim() !== "") {
      formData.append("telefono", datos.telefono);
    }

    if (datos.email && datos.email.trim() !== "") {
      formData.append("email", datos.email);
    }

    if (datos.direccion && datos.direccion.trim() !== "") {
      formData.append("direccion", datos.direccion);
    }

    console.log(
      "Enviando solicitud de registro con formData configurado correctamente"
    );

    // Enviar datos al servidor
    const response = await fetchData(
      "../../../controllers/cliente.controller.php?op=registrar",
      {
        method: "POST",
        body: formData,
      }
    );

    console.log("Respuesta del servidor para registro de cliente:", response);

    // Verificar explícitamente si la respuesta contiene el ID del cliente
    if (response && response.status) {
      return {
        status: true,
        mensaje: response.mensaje || "Cliente registrado correctamente",
        idcliente: response.idcliente,
        // CLAVE: Incluir también el idpersona devuelto por el servidor
        idpersona: response.idpersona || null,
      };
    } else {
      // Si la respuesta no tiene el formato esperado, retornar un error
      return {
        status: false,
        mensaje:
          response.mensaje ||
          "Error al registrar cliente: formato de respuesta incorrecto",
      };
    }
  } catch (error) {
    console.error("Error en registrarClientePagador:", error);
    return {
      status: false,
      mensaje: "Error al registrar cliente: " + error.message,
    };
  }
}
/**
 * Actualiza un cliente para asociarlo con una persona
 * Función auxiliar para manejar casos donde necesitamos actualizar un cliente existente
 * @param {number} idcliente ID del cliente a actualizar
 * @param {number} idpersona ID de la persona a asociar
 * @returns {Promise} Resultado de la operación
 */
async function actualizarClienteConPersona(idcliente, idpersona) {
  try {
    if (!idcliente || !idpersona) {
      console.error("Faltan datos para actualizar cliente con persona");
      return {
        status: false,
        mensaje: "Faltan datos para actualizar cliente con persona",
      };
    }

    console.log(`Actualizando cliente ${idcliente} con persona ${idpersona}`);

    const formData = new FormData();
    formData.append("idcliente", idcliente);
    formData.append("idpersona", idpersona);

    const response = await fetchData(
      "/sistemaclinica/controllers/cliente.controller.php?op=actualizar_persona",
      {
        method: "POST",
        body: formData,
      }
    );

    console.log("Respuesta de actualización:", response);
    return response;
  } catch (error) {
    console.error("Error en actualizarClienteConPersona:", error);
    return {
      status: false,
      mensaje: "Error al actualizar cliente: " + error.message,
    };
  }
}
/**
 * Valida el formato del RUC
 * @param {string} ruc RUC a validar
 * @returns {boolean} True si el RUC es válido
 */
function validarRUC(ruc) {
  // Si no hay RUC, es inválido
  if (!ruc) return false;

  // Eliminar espacios y caracteres no numéricos
  ruc = ruc.replace(/\s+/g, "").replace(/[^0-9]/g, "");

  // RUC peruano: 11 dígitos, debe comenzar con 10, 15, 17 o 20
  if (!/^\d{11}$/.test(ruc)) return false;

  const dosPrimeros = ruc.substring(0, 2);
  if (!["10", "15", "17", "20"].includes(dosPrimeros)) return false;

  return true;
}

// ===============================================================
// FUNCIONES PARA GESTIÓN DEL MONTOPAGADO Y VUELTO
// ===============================================================

// Evento DOMContentLoaded para inicializar eventos relacionados con el pago
document.addEventListener("DOMContentLoaded", function () {
  const montoPagadoInput = document.getElementById("montoPagado");
  if (montoPagadoInput) {
    montoPagadoInput.addEventListener("input", function () {
      // Reemplazar coma por punto para formato decimal
      this.value = this.value.replace(",", ".");

      // Eliminar caracteres no numéricos excepto el punto decimal
      this.value = this.value.replace(/[^0-9.]/g, "");

      // Asegurar que solo haya un punto decimal
      const decimalCount = (this.value.match(/\./g) || []).length;
      if (decimalCount > 1) {
        this.value = this.value.replace(
          /\./g,
          function (match, offset, string) {
            return offset === string.indexOf(".") ? "." : "";
          }
        );
      }

      // Calcular y mostrar el vuelto si corresponde
      calcularVuelto();
    });
  }

  // Inicializar evento para método de pago
  const metodoPago = document.getElementById("metodoPago");
  if (metodoPago) {
    metodoPago.addEventListener("change", function () {
      const metodo = this.value;
      const montoClienteContainer = document.getElementById(
        "montoClienteContainer"
      );

      if (montoClienteContainer) {
        if (metodo === "efectivo") {
          montoClienteContainer.style.display = "block";
          calcularVuelto();
        } else {
          montoClienteContainer.style.display = "none";
          const vueltoContainer = document.getElementById("vueltoContainer");
          if (vueltoContainer) {
            vueltoContainer.style.display = "none";
          }
        }
      }
    });
  }
});

// Función para calcular y mostrar el vuelto
function calcularVuelto() {
  const montoPagado =
    parseFloat(document.getElementById("montoPagado").value) || 0;
  const precioTotal = obtenerPrecioConsulta();
  const vueltoContainer = document.getElementById("vueltoContainer");
  const vueltoMonto = document.getElementById("vueltoMonto");
  const btnProcesarPago = document.getElementById("btnProcesarPago");

  if (vueltoContainer && vueltoMonto) {
    if (montoPagado >= precioTotal) {
      const vuelto = montoPagado - precioTotal;
      vueltoMonto.textContent = `S/ ${vuelto.toFixed(2)}`;
      vueltoContainer.style.display = "block";

      if (btnProcesarPago) {
        btnProcesarPago.disabled = false;
      }
    } else {
      vueltoContainer.style.display = "none";

      if (
        btnProcesarPago &&
        document.getElementById("metodoPago").value === "efectivo"
      ) {
        btnProcesarPago.disabled = true;
      }
    }
  }
}

// ===============================================================
// FUNCIONES PARA EL COMPROBANTE
// ===============================================================

/**
 * Muestra el modal del comprobante con los datos correctos
 * @param {Object} datosComprobante Datos del comprobante obtenidos del servidor
 */
/**
 * Muestra el modal del comprobante con los datos correctos
 * @param {Object} datosComprobante Datos del comprobante obtenidos del servidor
 */
function mostrarModalComprobante(datosComprobante) {
  try {
    console.log("Mostrando modal comprobante con datos:", datosComprobante);

    if (!datosComprobante) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No se pudieron obtener los datos del comprobante.",
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

    console.log(
      `Tipo de documento detectado: ${esFactura ? "FACTURA" : "BOLETA"}`
    );
    console.log(
      "Tipo de pago a mostrar en comprobante:",
      datosComprobante.tipopago
    );

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
      text: "No se pudo generar el comprobante. Inténtelo de nuevo.",
    });
  }
}

/**
 * Muestra el modal de BOLETA con formato optimizado
 * @param {Object} datosComprobante Datos del comprobante
 */
function mostrarModalBoleta(datosComprobante) {
  try {
    // IMPORTANTE: Log para depuración
    console.log("Mostrando boleta con datos:", datosComprobante);
    console.log("Tipo de pago a mostrar en boleta:", datosComprobante.tipopago);
    console.log(
      "Fecha de consulta a mostrar:",
      datosComprobante.fecha_consulta
    );
    console.log("Hora programada a mostrar:", datosComprobante.horaprogramada);

    // Determinar tipo de comprobante
    const tipoComprobante = "BOLETA DE VENTA ELECTRÓNICA";

    // Calcular vuelto si es pago en efectivo
    const precio = parseFloat(datosComprobante.precio);
    const montoPagado = parseFloat(datosComprobante.montopagado || 0);
    const vuelto =
      datosComprobante.tipopago === "EFECTIVO" && montoPagado > precio
        ? montoPagado - precio
        : 0;

    // Convertir el precio a texto
    const precioEntero = Math.floor(precio);
    const precioDecimales = Math.round((precio - precioEntero) * 100);
    const precioTexto =
      numeroALetras(precioEntero).toUpperCase() +
      " CON " +
      (precioDecimales === 0
        ? "CERO"
        : numeroALetras(precioDecimales).toUpperCase()) +
      " CENTAVOS";

    // Obtener tipos de documento
    const tipoDocCliente = obtenerTipoDocumentoTexto(
      datosComprobante.cliente_tipodoc || "DNI"
    );
    const tipoDocPaciente = obtenerTipoDocumentoTexto(
      datosComprobante.paciente_tipodoc || "DNI"
    );

    // CORRECCIÓN: Determinar qué secciones mostrar según método de pago usando tabla
    let seccionPagoHTML = "";

    if (datosComprobante.tipopago === "EFECTIVO") {
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

    // CORRECCIÓN: Asegurar que mostramos la fecha correcta de la consulta
    const fechaConsultaFormateada = formatearFecha(
      datosComprobante.fecha_consulta
    );
    const horaConsultaFormateada = formatearHora(
      datosComprobante.horaprogramada
    );

    // Crear contenido para el modal de BOLETA con estilos en blanco y negro
    let contenido = `
            <style>
                /* Estilos para impresión en blanco y negro */
                @media print {
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
                    }
                    
                    .modal-header, .btn-close, .modal-footer, .no-print {
                        display: none !important;
                    }
                }
                
                /* Estilos para visualización en pantalla */
                .comprobante-contenedor {
                    font-family: Arial, sans-serif;
                    max-width: 800px;
                    margin: 0 auto;
                    color: #000;
                    background-color: #fff;
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
            <div class="modal-header bg-light text-dark no-print">
                <h5 class="modal-title">${tipoComprobante}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
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
                            <div class="numero-documento">${
                              datosComprobante.nrodocumento
                            }</div>
                            <div class="fecha-documento">
                                <div>Fecha: ${formatearFecha(
                                  datosComprobante.fechaemision
                                )}</div>
                                <div>Hora: ${formatearHora(
                                  datosComprobante.fechaemision
                                )}</div>
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
                            <td>${tipoDocCliente} Cliente:</td>
                            <td>${
                              datosComprobante.cliente_nrodoc ||
                              datosComprobante.cliente_ruc ||
                              "-"
                            }</td>
                        </tr>
                        <tr>
                            <td>Cliente:</td>
                            <td>${
                              datosComprobante.cliente_natural ||
                              datosComprobante.cliente_empresa ||
                              "-"
                            }</td>
                        </tr>
                        <tr>
                            <td>${tipoDocPaciente} Paciente:</td>
                            <td>${datosComprobante.paciente_nrodoc || "-"}</td>
                        </tr>
                        <tr>
                            <td>Paciente:</td>
                            <td>${datosComprobante.paciente || "-"}</td>
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
                                <div>Consulta Médica - ${
                                  datosComprobante.especialidad
                                }</div>
                                <div style="font-size: 0.9em; color: #000;">Doctor: ${
                                  datosComprobante.doctor
                                }</div>
                                <div style="font-size: 0.9em; color: #000;">Fecha: ${formatearFecha(
                                  datosComprobante.fecha_consulta
                                )} / Hora: ${formatearHora(
      datosComprobante.horaprogramada
    )}</div>
                            </div>
                            <div class="precio-consulta">S/ ${precio.toFixed(
                              2
                            )}</div>
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
                            <td>${datosComprobante.tipopago}</td>
                        </tr>
                        ${seccionPagoHTML}
                    </table>
                    
                    <!-- Línea punteada -->
                    <div class="linea-punteada"></div>
                    
                    <!-- Pie de página -->
                    <div class="pie-pagina">
                        <div class="info-adicional">
                            <div><strong>Forma de Pago:</strong> ${
                              datosComprobante.tipopago
                            }</div>
                            <div><strong>Atendido por:</strong> ${
                              datosComprobante.usuario_venta || "Administrador"
                            }</div>
                            <div><strong>Caja:</strong> ${
                              datosComprobante.id_cajero || "1"
                            }</div>
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
            </div>
            <div class="modal-footer no-print">
                <button type="button" class="btn btn-primary" onclick="imprimirComprobante()">
                    <i class="fas fa-print me-2"></i>Imprimir
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        `;

    // Actualizar contenido y mostrar con opciones para evitar cierre
    document.getElementById("contenidoModalComprobante").innerHTML = contenido;

    // MODIFICADO: Agregar opciones para evitar cierre al hacer clic fuera
    const modalElement = document.getElementById("modalComprobante");

    // Establecer atributos para evitar cierre al hacer clic fuera
    modalElement.setAttribute("data-bs-backdrop", "static");
    modalElement.setAttribute("data-bs-keyboard", "false");

    // NUEVO: Agregar evento para cuando se cierra el modal
    // Remover eventos anteriores para evitar duplicación
    modalElement.removeEventListener("hidden.bs.modal", handleModalHidden);
    modalElement.addEventListener("hidden.bs.modal", handleModalHidden);

    const bsModal = new bootstrap.Modal(modalElement);
    bsModal.show();
  } catch (error) {
    console.error("Error en mostrarModalBoleta:", error);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "No se pudo generar el comprobante. Inténtelo de nuevo.",
    });
  }
}

/**
 * FUNCIÓN CORREGIDA: Muestra el modal de FACTURA
 * @param {Object} datosComprobante Datos del comprobante obtenidos del servidor
 */
function mostrarModalFactura(datosComprobante) {
  try {
    console.log("Mostrando factura con datos:", datosComprobante);
    console.log(
      "Tipo de pago a mostrar en factura:",
      datosComprobante.tipopago
    );
    console.log(
      "Fecha de consulta a mostrar:",
      datosComprobante.fecha_consulta
    );
    console.log("Hora programada a mostrar:", datosComprobante.horaprogramada);

    // Calcular vuelto si es pago en efectivo
    const precio = parseFloat(datosComprobante.precio);
    const montoPagado = parseFloat(datosComprobante.montopagado || 0);
    const vuelto =
      datosComprobante.tipopago === "EFECTIVO" && montoPagado > precio
        ? montoPagado - precio
        : 0;

    // Convertir el precio a texto
    const precioEntero = Math.floor(precio);
    const precioDecimales = Math.round((precio - precioEntero) * 100);
    const precioTexto =
      numeroALetras(precioEntero).toUpperCase() +
      " CON " +
      (precioDecimales === 0
        ? "CERO"
        : numeroALetras(precioDecimales).toUpperCase()) +
      " CENTAVOS";

    // ESTRATEGIA MEJORADA: Obtener datos de empresa con múltiples fuentes posibles
    let empresaRUC = "-";
    let empresaNombre = "-";
    let empresaDireccion = "-";

    // Múltiples estrategias para obtener el RUC
    if (
      datosComprobante.cliente_ruc &&
      datosComprobante.cliente_ruc.trim() !== ""
    ) {
      empresaRUC = datosComprobante.cliente_ruc;
    } else if (datosComprobante.ruc && datosComprobante.ruc.trim() !== "") {
      empresaRUC = datosComprobante.ruc;
    } else {
      // Intentar obtener del formulario como último recurso
      const rucFacturaInput = document.getElementById("rucFactura");
      if (rucFacturaInput && rucFacturaInput.value.trim() !== "") {
        empresaRUC = rucFacturaInput.value;
      }
    }

    // Múltiples estrategias para obtener la Razón Social
    if (
      datosComprobante.cliente_empresa &&
      datosComprobante.cliente_empresa.trim() !== ""
    ) {
      empresaNombre = datosComprobante.cliente_empresa;
    } else if (
      datosComprobante.razonsocial &&
      datosComprobante.razonsocial.trim() !== ""
    ) {
      empresaNombre = datosComprobante.razonsocial;
    } else {
      // Intentar obtener del formulario como último recurso
      const razonSocialInput = document.getElementById("razonSocialFactura");
      if (razonSocialInput && razonSocialInput.value.trim() !== "") {
        empresaNombre = razonSocialInput.value;
      }
    }

    // Múltiples estrategias para obtener la Dirección
    if (
      datosComprobante.cliente_empresa_direccion &&
      datosComprobante.cliente_empresa_direccion.trim() !== ""
    ) {
      empresaDireccion = datosComprobante.cliente_empresa_direccion;
    } else if (
      datosComprobante.direccion &&
      datosComprobante.direccion.trim() !== ""
    ) {
      empresaDireccion = datosComprobante.direccion;
    } else {
      // Intentar obtener del formulario como último recurso
      const direccionInput = document.getElementById("direccionFactura");
      if (direccionInput && direccionInput.value.trim() !== "") {
        empresaDireccion = direccionInput.value;
      }
    }

    console.log("Valores recuperados para empresa:", {
      ruc: empresaRUC,
      razonSocial: empresaNombre,
      direccion: empresaDireccion,
    });

    // Obtener tipos de documento para el paciente
    const tipoDocPaciente = obtenerTipoDocumentoTexto(
      datosComprobante.paciente_tipodoc || "DNI"
    );

    // IMPORTANTE: Verificar el tipo de cliente
    const tipoClienteVal =
      document.getElementById("tipoCliente")?.value || "empresa";
    console.log("Tipo de cliente para factura:", tipoClienteVal);

    // CORRECCIÓN PRINCIPAL: Siempre usar datos del paciente como cliente cuando el tipo es empresa
    let clienteDoc, clienteNombre;

    if (tipoClienteVal === "empresa") {
      // NUEVO: Si es empresa, usar datos del PACIENTE como CLIENTE
      clienteDoc = datosComprobante.paciente_nrodoc || "-";
      clienteNombre = datosComprobante.paciente || "-";
      console.log(
        "Usando paciente como cliente para factura con empresa:",
        clienteDoc,
        clienteNombre
      );
    } else if (tipoClienteVal === "personal") {
      // Si es pago personal, el PACIENTE es el CLIENTE (se mantiene igual)
      clienteDoc = datosComprobante.paciente_nrodoc || "-";
      clienteNombre = datosComprobante.paciente || "-";
    } else if (tipoClienteVal === "tercero") {
      // Para pagadores terceros, obtener datos directamente del formulario
      const docPagador =
        document.getElementById("documentoPagador")?.value || "-";
      const nombresPagador =
        document.getElementById("nombresPagador")?.value || "";
      const apellidosPagador =
        document.getElementById("apellidosPagador")?.value || "";

      clienteDoc = docPagador;
      clienteNombre = `${apellidosPagador}, ${nombresPagador}`.trim();
    } else {
      // Fallback para otros casos
      clienteDoc = datosComprobante.cliente_nrodoc || "-";
      clienteNombre = datosComprobante.cliente_natural || "-";
    }

    // CORRECCIÓN: Determinar qué secciones mostrar según método de pago - MISMO FORMATO QUE INFO CLIENTE
    let seccionPagoHTML = "";

    if (datosComprobante.tipopago === "EFECTIVO") {
      // Para EFECTIVO mostrar monto pagado y vuelto
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
      // Para otros métodos, mostrar solo monto pagado
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

    // Crear contenido para el modal de FACTURA
    let contenido = `
            <style>
                /* Estilos para impresión en blanco y negro */
                @media print {
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
                    }
                    
                    .modal-header, .btn-close, .modal-footer, .no-print {
                        display: none !important;
                    }
                }
                
                /* Estilos base */
                .invoice-container {
                    font-family: Arial, sans-serif;
                    color: #000;
                    background-color: #fff;
                    max-width: 800px;
                    margin: 0 auto;
                }
                
                /* Estilos específicos de factura */
                .invoice-title {
                    text-align: center;
                    font-size: 1.5rem;
                    font-weight: bold;
                    margin-bottom: 20px;
                    color: #000;
                }
                
                .invoice-header {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 20px;
                }
                
                .invoice-logo {
                    width: 150px;
                    height: 120px;
                    border: 1px solid #000;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    text-align: center;
                    padding: 5px;
                    font-size: 14px;
                    line-height: 1.4;
                    color: #000;
                    background-color: #fff;
                }
                
                .invoice-company-info {
                    margin-top: 5px;
                    font-size: 0.9rem;
                    color: #000;
                }
                
                .invoice-number-container {
                    text-align: right;
                }
                
                .invoice-number {
                    border: 2px solid #000;
                    border-radius: 4px;
                    padding: 10px 20px;
                    font-weight: bold;
                    font-size: 1.2rem;
                    display: inline-block;
                    margin-bottom: 10px;
                    color: #000;
                    background-color: #fff;
                }
                
                .invoice-date {
                    text-align: right;
                    font-size: 0.9rem;
                    color: #000;
                }
                
                .divider {
                    border-top: 1px dashed #000;
                    margin: 15px 0;
                }
                
                .solid-divider {
                    border-top: 1px solid #000;
                    margin: 15px 0;
                }
                
                .section-title {
                    font-weight: bold;
                    font-size: 1rem;
                    margin-bottom: 10px;
                    text-transform: uppercase;
                    color: #000;
                }
                
                /* Estilos para la sección de empresa */
                .company-details {
                    margin-bottom: 20px;
                    background-color: #fff;
                    padding: 15px;
                    border-radius: 5px;
                    border-left: 4px solid #000;
                    color: #000;
                }
                
                .company-details h3 {
                    margin-top: 0;
                    color: #000;
                    font-size: 1.1rem;
                    margin-bottom: 10px;
                }
                
                .company-detail-row {
                    display: flex;
                    margin-bottom: 5px;
                    color: #000;
                }
                
                .company-detail-label {
                    width: 120px;
                    font-weight: bold;
                    color: #000;
                }
                
                /* Estilos para datos del cliente y paciente */
                .patient-info {
                    margin-bottom: 20px;
                }
                
                .data-table {
                    width: 100%;
                    border-collapse: collapse;
                    color: #000;
                }
                
                .data-table td {
                    padding: 5px 0;
                    color: #000;
                }
                
                .data-table td:first-child {
                    width: 180px;
                    font-weight: bold;
                    color: #000;
                }
                
                /* Estilos para detalle del servicio */
                .service-detail {
                    margin: 15px 0;
                }
                
                .service-header {
                    display: flex;
                    justify-content: space-between;
                    font-weight: bold;
                    padding-bottom: 5px;
                    border-bottom: 1px solid #000;
                    color: #000;
                }
                
                .service-item {
                    display: flex;
                    justify-content: space-between;
                    padding: 10px 0;
                    border-bottom: 1px solid #eee;
                    color: #000;
                }
                
                .service-description span {
                    display: block;
                    color: #000;
                    font-size: 0.85rem;
                    margin-top: 3px;
                }
                
                /* Estilos para totales */
                .totals-section {
                    margin: 15px 0;
                }
                
                .total-row {
                    display: flex;
                    justify-content: space-between;
                    padding: 5px 0;
                    color: #000;
                }
                
                .total-label {
                    font-weight: bold;
                    text-align: right;
                    color: #000;
                }
                
                .grand-total {
                    font-weight: bold;
                    font-size: 1.1rem;
                    padding-top: 5px;
                    border-top: 1px solid #000;
                    color: #000;
                }
                
                /* Información adicional */
                .payment-info {
                    margin: 15px 0;
                    color: #000;
                }
                
                .payment-info td {
                    padding: 5px 0;
                    color: #000;
                }
                
                .payment-info td:first-child {
                    width: 150px;
                    font-weight: bold;
                    color: #000;
                }
                
                .footer-section {
                    display: flex;
                    justify-content: space-between;
                    margin-top: 30px;
                    font-size: 0.9rem;
                    color: #000;
                }
                
                .footer-info {
                    width: 60%;
                    color: #000;
                }
                
                .qr-code {
                    width: 100px;
                    height: 100px;
                    border: 1px solid #000;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 20pt;
                    color: #000;
                    background-color: #fff;
                }
                
                .legal-info {
                    font-size: 0.8rem;
                    text-align: center;
                    margin-top: 30px;
                    color: #000;
                }
            </style>
            <div class="modal-header bg-light text-dark no-print">
                <h5 class="modal-title">Factura Electrónica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="invoice-container">
                    <!-- Título de la factura -->
                    <div class="invoice-title">FACTURA ELECTRÓNICA</div>
                    
                    <!-- Cabecera: Logo, datos clínica y número de factura -->
                    <div class="invoice-header">
                        <div>
                            <div class="invoice-logo">
                                CLÍNICA MÉDICA<br>
                                CENTRO DE SALUD ESPECIALIZADO
                            </div>
                            <div class="invoice-company-info">
                                <p class="mb-1">RUC: 20123456789</p>
                                <p class="mb-1">Av. Principal 123, Lima</p>
                                <p>Tel: (01) 555-1234</p>
                            </div>
                        </div>
                        <div class="invoice-number-container">
                            <div class="invoice-number">${
                              datosComprobante.nrodocumento
                            }</div>
                            <div class="invoice-date">
                                <p class="mb-1">Fecha: ${formatearFecha(
                                  datosComprobante.fechaemision
                                )}</p>
                                <p>Hora: ${formatearHora(
                                  datosComprobante.fechaemision
                                )}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- SECCIÓN: INFORMACIÓN DE LA EMPRESA -->
                    <div class="divider"></div>
                    <div class="section-title">INFORMACIÓN DE LA EMPRESA</div>
                    
                    <div class="divider"></div>
                    
                    <table class="data-table">
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
                    
                    <!-- SECCIÓN: INFORMACIÓN DEL CLIENTE Y PACIENTE -->
                    <div class="divider"></div>
                    <div class="section-title">INFORMACIÓN DEL CLIENTE Y PACIENTE</div>
                    
                    <div class="divider"></div>
                    
                    <table class="data-table">
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
                            <td>${datosComprobante.paciente_nrodoc || "-"}</td>
                        </tr>
                        <tr>
                            <td>Paciente:</td>
                            <td>${datosComprobante.paciente || "-"}</td>
                        </tr>
                    </table>
                    
                    <!-- Descripción del servicio -->
                    <div class="divider"></div>
                    <div class="section-title">DETALLE DE SERVICIOS</div>
                    
                    <div class="divider"></div>
                    
                    <div class="service-detail">
                        <div class="service-header">
                            <div>Descripción</div>
                            <div>Precio</div>
                        </div>
                        <div class="service-item">
                            <div class="service-description">
                                <div>Consulta Médica - ${
                                  datosComprobante.especialidad
                                }</div>
                                <span>Doctor: ${datosComprobante.doctor}</span>
                                <span>Fecha: ${formatearFecha(
                                  datosComprobante.fecha_consulta
                                )} / Hora: ${formatearHora(
      datosComprobante.horaprogramada
    )}</span>
                            </div>
                            <div>S/ ${precio.toFixed(2)}</div>
                        </div>
                    </div>
                    
                    <!-- Línea divisoria sólida -->
                    <div class="solid-divider"></div>
                    
                    <!-- Sección de totales -->
                    <div class="totals-section">
                        <div class="total-row">
                            <div class="total-label">VALOR VENTA:</div>
                            <div>S/ ${(precio / 1.18).toFixed(2)}</div>
                        </div>
                        <div class="total-row">
                            <div class="total-label">IGV (18%):</div>
                            <div>S/ ${(precio - precio / 1.18).toFixed(2)}</div>
                        </div>
                        <div class="total-row grand-total">
                            <div class="total-label">TOTAL A PAGAR:</div>
                            <div>S/ ${precio.toFixed(2)}</div>
                        </div>
                    </div>
                    
                    <!-- Línea divisoria -->
                    <div class="divider"></div>
                    
                    <!-- CORRECCIÓN: Información de pago - Usar mismo formato que secciones anteriores -->
                    <div class="section-title">INFORMACIÓN DE PAGO</div>
                    
                    <div class="divider"></div>
                    
                    <table class="data-table">
                        <tr>
                            <td>Forma de Pago:</td>
                            <td>${datosComprobante.tipopago}</td>
                        </tr>
                        ${seccionPagoHTML}
                    </table>
                    
                    <!-- Línea divisoria -->
                    <div class="divider"></div>
                    
                    <!-- Pie de página con observaciones, atendido por y cajero -->
                    <div class="footer-section">
                        <div class="footer-info">
                            <p><strong>Observaciones:</strong> El presente documento es una representación impresa de la Factura Electrónica autorizada.</p>
                            <p><strong>Atendido por:</strong> ${
                              datosComprobante.usuario_venta || "Administrador"
                            }</p>
                            <p><strong>Cajero:</strong> ${
                              datosComprobante.id_cajero || "1"
                            }</p>
                        </div>
                        <div>
                            <div class="qr-code">
                                ■ ■<br>
                                ■ ■
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información legal -->
                    <div class="legal-info">
                        Autorizado mediante Resolución de Intendencia N° 034-005-0005429/SUNAT<br>
                        Representación impresa de la FACTURA ELECTRÓNICA<br>
                        Puede consultar este documento en www.clinicamedica.com/consulta<br>
                        Bienes transferidos en la Amazonía - Ley N° 27037
                    </div>
                </div>
            </div>
            <div class="modal-footer no-print">
                <button type="button" class="btn btn-primary" onclick="imprimirComprobante()">
                    <i class="fas fa-print me-2"></i>Imprimir
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        `;

    // Actualizar el contenido del modal y mostrarlo
    document.getElementById("contenidoModalComprobante").innerHTML = contenido;

    // MODIFICADO: Agregar opciones para evitar cierre al hacer clic fuera
    const modalElement = document.getElementById("modalComprobante");

    // Establecer atributos para evitar cierre al hacer clic fuera
    modalElement.setAttribute("data-bs-backdrop", "static");
    modalElement.setAttribute("data-bs-keyboard", "false");

    // NUEVO: Agregar evento para cuando se cierra el modal
    // Remover eventos anteriores para evitar duplicación
    modalElement.removeEventListener("hidden.bs.modal", handleModalHidden);
    modalElement.addEventListener("hidden.bs.modal", handleModalHidden);

    const bsModal = new bootstrap.Modal(modalElement);
    bsModal.show();
  } catch (error) {
    console.error("Error en mostrarModalFactura:", error);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "No se pudo generar el comprobante. Inténtelo de nuevo.",
    });
  }
}
// Función manejadora común para el evento hidden.bs.modal
function handleModalHidden() {
  console.log("Modal de comprobante cerrado, recargando solo el calendario");

  // Recargar solo el calendario
  if (typeof recargarSoloCalendario === "function") {
    recargarSoloCalendario(true);
  } else {
    // Fallback si la función no está disponible
    if (window.calendario) {
      window.calendario.refetchEvents();
    }
  }

  // Asegurar que el botón de pago esté visible pero deshabilitado
  const btnPago = document.getElementById("btn-proceder-pago");
  if (btnPago) {
    btnPago.classList.remove("d-none");
    btnPago.disabled = true;
    btnPago.classList.remove("btn-success");
    btnPago.classList.add("btn-secondary");
  }
}
/**
 * NUEVA FUNCIÓN: Muestra el modal de FACTURA
 * @param {Object} datosComprobante Datos del comprobante obtenidos del servidor
 */
function mostrarModalFactura(datosComprobante) {
  // IMPORTANTE: Log para depuración
  console.log("Mostrando factura con datos:", datosComprobante);
  console.log("Tipo de pago a mostrar en factura:", datosComprobante.tipopago);

  // Calcular vuelto si es pago en efectivo
  const precio = parseFloat(datosComprobante.precio);
  const montoPagado = parseFloat(datosComprobante.montopagado || 0);
  const vuelto =
    datosComprobante.tipopago === "EFECTIVO" && montoPagado > precio
      ? montoPagado - precio
      : 0;

  // Convertir el precio a texto
  const precioEntero = Math.floor(precio);
  const precioDecimales = Math.round((precio - precioEntero) * 100);
  const precioTexto =
    numeroALetras(precioEntero).toUpperCase() +
    " CON " +
    (precioDecimales === 0
      ? "CERO"
      : numeroALetras(precioDecimales).toUpperCase()) +
    " CENTAVOS";

  // ESTRATEGIA MEJORADA: Obtener datos de empresa con múltiples fuentes posibles
  let empresaRUC = "-";
  let empresaNombre = "-";
  let empresaDireccion = "-";

  // Múltiples estrategias para obtener el RUC
  if (
    datosComprobante.cliente_ruc &&
    datosComprobante.cliente_ruc.trim() !== ""
  ) {
    empresaRUC = datosComprobante.cliente_ruc;
  } else if (datosComprobante.ruc && datosComprobante.ruc.trim() !== "") {
    empresaRUC = datosComprobante.ruc;
  } else {
    // Intentar obtener del formulario como último recurso
    const rucFacturaInput = document.getElementById("rucFactura");
    if (rucFacturaInput && rucFacturaInput.value.trim() !== "") {
      empresaRUC = rucFacturaInput.value;
    }
  }

  // Múltiples estrategias para obtener la Razón Social
  if (
    datosComprobante.cliente_empresa &&
    datosComprobante.cliente_empresa.trim() !== ""
  ) {
    empresaNombre = datosComprobante.cliente_empresa;
  } else if (
    datosComprobante.razonsocial &&
    datosComprobante.razonsocial.trim() !== ""
  ) {
    empresaNombre = datosComprobante.razonsocial;
  } else {
    // Intentar obtener del formulario como último recurso
    const razonSocialInput = document.getElementById("razonSocialFactura");
    if (razonSocialInput && razonSocialInput.value.trim() !== "") {
      empresaNombre = razonSocialInput.value;
    }
  }

  // Múltiples estrategias para obtener la Dirección
  if (
    datosComprobante.cliente_empresa_direccion &&
    datosComprobante.cliente_empresa_direccion.trim() !== ""
  ) {
    empresaDireccion = datosComprobante.cliente_empresa_direccion;
  } else if (
    datosComprobante.direccion &&
    datosComprobante.direccion.trim() !== ""
  ) {
    empresaDireccion = datosComprobante.direccion;
  } else {
    // Intentar obtener del formulario como último recurso
    const direccionInput = document.getElementById("direccionFactura");
    if (direccionInput && direccionInput.value.trim() !== "") {
      empresaDireccion = direccionInput.value;
    }
  }

  console.log("Valores recuperados para empresa:", {
    ruc: empresaRUC,
    razonSocial: empresaNombre,
    direccion: empresaDireccion,
  });

  // Obtener tipos de documento para el paciente
  const tipoDocPaciente = obtenerTipoDocumentoTexto(
    datosComprobante.paciente_tipodoc || "DNI"
  );

  // IMPORTANTE: Verificar el tipo de cliente
  const tipoClienteVal =
    document.getElementById("tipoCliente")?.value || "empresa";
  console.log("Tipo de cliente para factura:", tipoClienteVal);

  // CORRECCIÓN PRINCIPAL: Siempre usar datos del paciente como cliente cuando el tipo es empresa
  let clienteDoc, clienteNombre;

  if (tipoClienteVal === "empresa") {
    // NUEVO: Si es empresa, usar datos del PACIENTE como CLIENTE
    clienteDoc = datosComprobante.paciente_nrodoc || "-";
    clienteNombre = datosComprobante.paciente || "-";
    console.log(
      "Usando paciente como cliente para factura con empresa:",
      clienteDoc,
      clienteNombre
    );
  } else if (tipoClienteVal === "personal") {
    // Si es pago personal, el PACIENTE es el CLIENTE (se mantiene igual)
    clienteDoc = datosComprobante.paciente_nrodoc || "-";
    clienteNombre = datosComprobante.paciente || "-";
  } else if (tipoClienteVal === "tercero") {
    // Para pagadores terceros, obtener datos directamente del formulario
    const docPagador =
      document.getElementById("documentoPagador")?.value || "-";
    const nombresPagador =
      document.getElementById("nombresPagador")?.value || "";
    const apellidosPagador =
      document.getElementById("apellidosPagador")?.value || "";

    clienteDoc = docPagador;
    clienteNombre = `${apellidosPagador}, ${nombresPagador}`.trim();
  } else {
    // Fallback para otros casos
    clienteDoc = datosComprobante.cliente_nrodoc || "-";
    clienteNombre = datosComprobante.cliente_natural || "-";
  }

  // CORRECCIÓN: Determinar qué secciones mostrar según método de pago
  let seccionVuelto = "";
  let seccionMontoPagado = "";

  if (datosComprobante.tipopago === "EFECTIVO") {
    // Para EFECTIVO mostrar monto pagado y vuelto
    seccionMontoPagado = `
      <div class="row mb-2">
        <div class="col-4 fw-bold">Monto Pagado:</div>
        <div class="col-8">S/ ${montoPagado.toFixed(2)}</div>
      </div>
    `;

    seccionVuelto = `
      <div class="row mb-2">
        <div class="col-4 fw-bold">Vuelto:</div>
        <div class="col-8">S/ ${vuelto.toFixed(2)}</div>
      </div>
    `;
  } else {
    // Para otros métodos, mostrar solo monto pagado
    seccionMontoPagado = `
      <div class="row mb-2">
        <div class="col-4 fw-bold">Monto Pagado:</div>
        <div class="col-8">S/ ${precio.toFixed(2)}</div>
      </div>
    `;
  }

  // Crear contenido para el modal de FACTURA
  let contenido = `
    <style>
      /* Estilos para impresión en blanco y negro */
      @media print {
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
        }
        
        .modal-header, .btn-close, .modal-footer, .no-print {
          display: none !important;
        }
      }
      
      /* Estilos base */
      .invoice-container {
        font-family: Arial, sans-serif;
        color: #000;
        background-color: #fff;
        max-width: 800px;
        margin: 0 auto;
      }
      
      /* Estilos específicos de factura */
      .invoice-title {
        text-align: center;
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 20px;
        color: #000;
      }
      
      .invoice-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
      }
      
      .invoice-logo {
        width: 150px;
        height: 120px;
        border: 1px solid #000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        text-align: center;
        padding: 5px;
        font-size: 14px;
        line-height: 1.4;
        color: #000;
        background-color: #fff;
      }
      
      .invoice-company-info {
        margin-top: 5px;
        font-size: 0.9rem;
        color: #000;
      }
      
      .invoice-number-container {
        text-align: right;
      }
      
      .invoice-number {
        border: 2px solid #000;
        border-radius: 4px;
        padding: 10px 20px;
        font-weight: bold;
        font-size: 1.2rem;
        display: inline-block;
        margin-bottom: 10px;
        color: #000;
        background-color: #fff;
      }
      
      .invoice-date {
        text-align: right;
        font-size: 0.9rem;
        color: #000;
      }
      
      .divider {
        border-top: 1px dashed #000;
        margin: 15px 0;
      }
      
      .solid-divider {
        border-top: 1px solid #000;
        margin: 15px 0;
      }
      
      .section-title {
        font-weight: bold;
        font-size: 1rem;
        margin-bottom: 10px;
        text-transform: uppercase;
        color: #000;
      }
      
      /* Estilos para la sección de empresa */
      .company-details {
        margin-bottom: 20px;
        background-color: #fff;
        padding: 15px;
        border-radius: 5px;
        border-left: 4px solid #000;
        color: #000;
      }
      
      .company-details h3 {
        margin-top: 0;
        color: #000;
        font-size: 1.1rem;
        margin-bottom: 10px;
      }
      
      .company-detail-row {
        display: flex;
        margin-bottom: 5px;
        color: #000;
      }
      
      .company-detail-label {
        width: 120px;
        font-weight: bold;
        color: #000;
      }
      
      /* Estilos para datos del cliente y paciente */
      .patient-info {
        margin-bottom: 20px;
      }
      
      .data-table {
        width: 100%;
        border-collapse: collapse;
        color: #000;
      }
      
      .data-table td {
        padding: 5px 0;
        color: #000;
      }
      
      .data-table td:first-child {
        width: 180px;
        font-weight: bold;
        color: #000;
      }
      
      /* Estilos para detalle del servicio */
      .service-detail {
        margin: 15px 0;
      }
      
      .service-header {
        display: flex;
        justify-content: space-between;
        font-weight: bold;
        padding-bottom: 5px;
        border-bottom: 1px solid #000;
        color: #000;
      }
      
      .service-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
        color: #000;
      }
      
      .service-description span {
        display: block;
        color: #000;
        font-size: 0.85rem;
        margin-top: 3px;
      }
      
      /* Estilos para totales */
      .totals-section {
        margin: 15px 0;
      }
      
      .total-row {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        color: #000;
      }
      
      .total-label {
        font-weight: bold;
        text-align: right;
        color: #000;
      }
      
      .grand-total {
        font-weight: bold;
        font-size: 1.1rem;
        padding-top: 5px;
        border-top: 1px solid #000;
        color: #000;
      }
      
      /* Información adicional */
      .payment-info {
        margin: 15px 0;
        color: #000;
      }
      
      .payment-info td {
        padding: 5px 0;
        color: #000;
      }
      
      .payment-info td:first-child {
        width: 150px;
        font-weight: bold;
        color: #000;
      }
      
      .footer-section {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        font-size: 0.9rem;
        color: #000;
      }
      
      .footer-info {
        width: 60%;
        color: #000;
      }
      
      .qr-code {
        width: 100px;
        height: 100px;
        border: 1px solid #000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20pt;
        color: #000;
        background-color: #fff;
      }
      
      .legal-info {
        font-size: 0.8rem;
        text-align: center;
        margin-top: 30px;
        color: #000;
      }
    </style>
    <div class="modal-header bg-light text-dark no-print">
      <h5 class="modal-title">Factura Electrónica</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body p-4">
      <div class="invoice-container">
        <!-- Título de la factura -->
        <div class="invoice-title">FACTURA ELECTRÓNICA</div>
        
        <!-- Cabecera: Logo, datos clínica y número de factura -->
        <div class="invoice-header">
          <div>
            <div class="invoice-logo">
              CLÍNICA MÉDICA<br>
              CENTRO DE SALUD ESPECIALIZADO
            </div>
            <div class="invoice-company-info">
              <p class="mb-1">RUC: 20123456789</p>
              <p class="mb-1">Av. Principal 123, Lima</p>
              <p>Tel: (01) 555-1234</p>
            </div>
          </div>
          <div class="invoice-number-container">
            <div class="invoice-number">${datosComprobante.nrodocumento}</div>
            <div class="invoice-date">
              <p class="mb-1">Fecha: ${formatearFecha(
                datosComprobante.fechaemision
              )}</p>
              <p>Hora: ${formatearHora(datosComprobante.fechaemision)}</p>
            </div>
          </div>
        </div>
        
        <!-- SECCIÓN: INFORMACIÓN DE LA EMPRESA -->
        <div class="divider"></div>
        <div class="section-title">INFORMACIÓN DE LA EMPRESA</div>
        
        <div class="company-details">
          <div class="company-detail-row">
            <div class="company-detail-label">RUC:</div>
            <div>${empresaRUC}</div>
          </div>
          <div class="company-detail-row">
            <div class="company-detail-label">Razón Social:</div>
            <div>${empresaNombre}</div>
          </div>
          <div class="company-detail-row">
            <div class="company-detail-label">Dirección:</div>
            <div>${empresaDireccion}</div>
          </div>
        </div>
        
        <!-- SECCIÓN: INFORMACIÓN DEL CLIENTE Y PACIENTE -->
        <div class="divider"></div>
        <div class="section-title">INFORMACIÓN DEL CLIENTE Y PACIENTE</div>
        
        <div class="patient-info">
          <table class="data-table">
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
              <td>${datosComprobante.paciente_nrodoc || "-"}</td>
            </tr>
            <tr>
              <td>Paciente:</td>
              <td>${datosComprobante.paciente || "-"}</td>
            </tr>
          </table>
        </div>
        
        <!-- Descripción del servicio -->
        <div class="divider"></div>
        <div class="section-title">DETALLE DE SERVICIOS</div>
        
        <div class="service-detail">
          <div class="service-header">
            <div>Descripción</div>
            <div>Precio</div>
          </div>
          <div class="service-item">
            <div class="service-description">
              <div>Consulta Médica - ${datosComprobante.especialidad}</div>
              <span>Doctor: ${datosComprobante.doctor}</span>
              <span>Fecha: ${formatearFecha(
                datosComprobante.fecha_consulta
              )} / Hora: ${formatearHora(
    datosComprobante.horaprogramada
  )}</span>
            </div>
            <div>S/ ${precio.toFixed(2)}</div>
          </div>
        </div>
        
        <!-- Línea divisoria sólida -->
        <div class="solid-divider"></div>
        
        <!-- Sección de totales -->
        <div class="totals-section">
          <div class="total-row">
            <div class="total-label">VALOR VENTA:</div>
            <div>S/ ${(precio / 1.18).toFixed(2)}</div>
          </div>
          <div class="total-row">
            <div class="total-label">IGV (18%):</div>
            <div>S/ ${(precio - precio / 1.18).toFixed(2)}</div>
          </div>
          <div class="total-row grand-total">
            <div class="total-label">TOTAL A PAGAR:</div>
            <div>S/ ${precio.toFixed(2)}</div>
          </div>
        </div>
        
        <!-- Línea divisoria -->
        <div class="divider"></div>
        
        <!-- CORRECCIÓN: Información de pago - Mostrar correctamente el tipo de pago -->
        <div class="section-title">INFORMACIÓN DE PAGO</div>
        
        <div class="row mb-2">
          <div class="col-4 fw-bold">Forma de Pago:</div>
          <div class="col-8">${datosComprobante.tipopago}</div>
        </div>
        
        <div class="row mb-2">
          <div class="col-4 fw-bold">Son:</div>
          <div class="col-8">${precioTexto}</div>
        </div>
        
        ${seccionMontoPagado}
        ${seccionVuelto}
        
        <!-- Línea divisoria -->
        <div class="divider"></div>
        
        <!-- Pie de página con observaciones, atendido por y cajero -->
        <div class="footer-section">
          <div class="footer-info">
            <p><strong>Observaciones:</strong> El presente documento es una representación impresa de la Factura Electrónica autorizada.</p>
            <p><strong>Atendido por:</strong> ${
              datosComprobante.usuario_venta || "Administrador"
            }</p>
            <p><strong>Cajero:</strong> ${datosComprobante.id_cajero || "1"}</p>
          </div>
          <div>
            <div class="qr-code">
              ■ ■<br>
              ■ ■
            </div>
          </div>
        </div>
        
        <!-- Información legal -->
        <div class="legal-info">
          Autorizado mediante Resolución de Intendencia N° 034-005-0005429/SUNAT<br>
          Representación impresa de la FACTURA ELECTRÓNICA<br>
          Puede consultar este documento en www.clinicamedica.com/consulta<br>
          Bienes transferidos en la Amazonía - Ley N° 27037
        </div>
      </div>
    </div>
    <div class="modal-footer no-print">
      <button type="button" class="btn btn-primary" onclick="imprimirComprobante()">
        <i class="fas fa-print me-2"></i>Imprimir
      </button>
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
    </div>
  `;

  // Actualizar el contenido del modal y mostrarlo
  document.getElementById("contenidoModalComprobante").innerHTML = contenido;

  // MODIFICADO: Agregar opciones para evitar cierre al hacer clic fuera
  const modalElement = document.getElementById("modalComprobante");

  // Establecer atributos para evitar cierre al hacer clic fuera
  modalElement.setAttribute("data-bs-backdrop", "static");
  modalElement.setAttribute("data-bs-keyboard", "false");

  const bsModal = new bootstrap.Modal(modalElement);
  bsModal.show();
}

/**
 * FUNCIÓN MEJORADA: Imprime el comprobante adaptando según el tipo (BOLETA o FACTURA)
 */
/**
 * FUNCIÓN MEJORADA: Imprime el comprobante adaptando según el tipo (BOLETA o FACTURA)
 */
function imprimirComprobante() {
  try {
    // Obtener los datos para el comprobante
    const datosComprobante = window.datosComprobanteOriginal;

    if (!datosComprobante) {
      throw new Error(
        "No se encuentran los datos del comprobante para impresión"
      );
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

    // Preparar datos básicos para la impresión
    const precio = parseFloat(datosComprobante.precio || 0);
    const montoPagado = parseFloat(datosComprobante.montopagado || 0);
    const vuelto =
      datosComprobante.tipopago === "EFECTIVO" && montoPagado > precio
        ? montoPagado - precio
        : 0;

    // CORRECCIÓN: Validar y asegurar que tipopago existe
    if (!datosComprobante.tipopago) {
      // Obtener del formulario si no está en los datos originales
      const metodoPago = document.getElementById("metodoPago");
      if (metodoPago) {
        const metodoPagoValue = metodoPago.value;
        const metodoPagoMap = {
          efectivo: "EFECTIVO",
          transferencia: "TRANSFERENCIA",
          yape: "YAPE",
          plin: "PLIN",
        };
        datosComprobante.tipopago =
          metodoPagoMap[metodoPagoValue] || "EFECTIVO";
      } else {
        datosComprobante.tipopago = "EFECTIVO"; // Valor por defecto
      }
    }

    // Detectar si es factura o boleta
    const esFactura =
      datosComprobante.tipodoc === "FACTURA" ||
      (datosComprobante.nrodocumento &&
        datosComprobante.nrodocumento.startsWith("F")) ||
      document.getElementById("tipoComprobante")?.value === "factura";

    console.log(
      `Imprimiendo comprobante de tipo: ${esFactura ? "FACTURA" : "BOLETA"}`
    );
    console.log(`Método de pago para impresión: ${datosComprobante.tipopago}`);

    // Llamar a la función correcta según el tipo de documento
    if (esFactura) {
      imprimirFactura(
        frameImpresion,
        datosComprobante,
        precio,
        montoPagado,
        vuelto
      );
    } else {
      imprimirBoleta(
        frameImpresion,
        datosComprobante,
        precio,
        montoPagado,
        vuelto
      );
    }
  } catch (error) {
    console.error("Error en imprimirComprobante:", error);
    Swal.fire({
      title: "Error",
      text: "No se pudo generar el comprobante para impresión.",
    });
  }
}

/**
 * NUEVA FUNCIÓN: Imprime una BOLETA
 */
function imprimirBoleta(
  frameImpresion,
  datosComprobante,
  precio,
  montoPagado,
  vuelto
) {
  try {
    console.log("Imprimiendo boleta con formato exacto del modelo...");
    console.log("Tipo de pago a imprimir:", datosComprobante.tipopago);

    // Convertir el precio a texto
    const precioEntero = Math.floor(precio);
    const precioDecimales = Math.round((precio - precioEntero) * 100);
    const precioTexto =
      numeroALetras(precioEntero).toUpperCase() +
      " CON " +
      (precioDecimales === 0
        ? "CERO"
        : numeroALetras(precioDecimales).toUpperCase()) +
      " CENTAVOS";

    // Obtener tipos de documento
    const tipoDocCliente = obtenerTipoDocumentoTexto(
      datosComprobante.cliente_tipodoc || "DNI"
    );
    const tipoDocPaciente = obtenerTipoDocumentoTexto(
      datosComprobante.paciente_tipodoc || "DNI"
    );

    // CORRECCIÓN: Determinar qué secciones mostrar según método de pago - SIN ÍCONOS
    let seccionPagoHTML = "";

    if (datosComprobante.tipopago === "EFECTIVO") {
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
      // Para otros métodos, mostrar solo monto pagado igual al precio
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

    // Escribir el documento del iframe
    const frameDoc =
      frameImpresion.contentDocument || frameImpresion.contentWindow.document;
    frameDoc.open();
    frameDoc.write(`
      <!DOCTYPE html>
      <html lang="es">
      <head>
        <meta charset="UTF-8">
        <title>Boleta de Venta Electrónica</title>
        <style>
          /* Eliminar todos los márgenes y elementos del navegador */
          @page {
            size: 21cm 29.7cm;
            margin: 0;
          }
          
          body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
            background-color: #fff;
          }
          
          /* Reset básico para eliminar márgenes y paddings */
          * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
          }
          
          /* Contenedor principal */
          .comprobante {
            width: 19cm;
            margin: 0.5cm auto;
            padding: 0.5cm;
            border: 1px solid #000;
            background-color: #fff;
          }
          
          /* Título */
          .titulo {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 0.5cm;
          }
          
          /* Encabezado */
          .encabezado {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5cm;
          }
          
          .logo {
            border: 1px solid #000;
            width: 3cm;
            height: 2.5cm;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            text-align: center;
            font-size: 12pt;
          }
          
          .datos-empresa {
            margin-top: 0.2cm;
            font-size: 9pt;
          }
          
          .numero-documento {
            border: 1px solid #000;
            padding: 0.3cm;
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
          }
          
          .fecha-hora {
            text-align: right;
            font-size: 9pt;
            margin-top: 0.2cm;
          }
          
          /* Líneas separadoras */
          .linea-punteada {
            border-top: 1px dashed #000;
            margin: 0.3cm 0;
          }
          
          .linea-solida {
            border-top: 1px solid #000;
            margin: 0.3cm 0;
          }
          
          /* Secciones */
          .seccion-titulo {
            font-weight: bold;
            margin: 0.3cm 0 0.2cm 0;
            text-transform: uppercase;
          }
          
          /* Tabla de datos - CORREGIDA para el formato exacto como en la imagen */
          .tabla-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.3cm;
          }
          
          .tabla-info td {
            padding: 0.1cm 0;
            font-size: 10pt;
            vertical-align: top;
          }
          
          .tabla-info td:first-child {
            width: 3.5cm;
            font-weight: bold;
          }
          
          /* Servicio */
          .servicio {
            margin-top: 0.2cm;
          }
          
          .servicio-detalle {
            padding: 0.2cm 0;
            border-bottom: 1px solid #eee;
          }
          
          .servicio-desc {
            font-size: 10pt;
          }
          
          .servicio-adicional {
            font-size: 9pt;
            color: #333;
            margin-top: 0.1cm;
          }
          
          /* Totales */
          .totales {
            width: 100%;
            margin-top: 0.3cm;
          }
          
          .fila-total {
            display: flex;
            justify-content: space-between;
            padding: 0.1cm 0;
          }
          
          .etiqueta-total {
            font-weight: bold;
            text-align: left;
          }
          
          .valor-total {
            text-align: right;
          }
          
          /* Información de pago - IMPORTANTE: CORREGIDA PARA FORMATO COMO EN LA IMAGEN */
          .info-pago {
            width: 100%;
            margin: 0.3cm 0;
          }
          
          /* Pie de página */
          .pie-pagina {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5cm;
            font-size: 9pt;
          }
          
          .info-pie {
            width: 70%;
          }
          
          .codigo-qr {
            border: 1px solid #000;
            width: 2.5cm;
            height: 2.5cm;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16pt;
          }
          
          /* Nota legal */
          .nota-legal {
            font-size: 8pt;
            text-align: center;
            margin-top: 0.5cm;
          }
        </style>
      </head>
      <body>
        <div class="comprobante">
          <!-- Título -->
          <div class="titulo">BOLETA DE VENTA ELECTRÓNICA</div>
          
          <!-- Encabezado -->
          <div class="encabezado">
            <div>
              <div class="logo">CLÍNICA<br>MÉDICA</div>
              <div class="datos-empresa">
                <div>RUC: 20123456789</div>
                <div>Av. Principal 123, Lima</div>
                <div>Tel: (01) 555-1234</div>
              </div>
            </div>
            <div>
              <div class="numero-documento">${
                datosComprobante.nrodocumento || "B001-00000001"
              }</div>
              <div class="fecha-hora">
                <div>Fecha: ${formatearFecha(
                  datosComprobante.fechaemision
                )}</div>
                <div>Hora: ${formatearHora(datosComprobante.fechaemision)}</div>
              </div>
            </div>
          </div>
          
          <!-- Línea punteada -->
          <div class="linea-punteada"></div>
          
          <!-- INFORMACIÓN DEL CLIENTE Y PACIENTE - Exactamente como en la imagen -->
          <div class="seccion-titulo">INFORMACIÓN DEL CLIENTE Y PACIENTE</div>
          
          <div class="linea-punteada"></div>
          
          <table class="tabla-info">
            <tr>
              <td>${tipoDocCliente} Cliente:</td>
              <td>${datosComprobante.cliente_nrodoc || "-"}</td>
            </tr>
            <tr>
              <td>Cliente:</td>
              <td>${datosComprobante.cliente_natural || "-"}</td>
            </tr>
            <tr>
              <td>${tipoDocPaciente} Paciente:</td>
              <td>${datosComprobante.paciente_nrodoc || "-"}</td>
            </tr>
            <tr>
              <td>Paciente:</td>
              <td>${datosComprobante.paciente || "-"}</td>
            </tr>
          </table>
          
          <div class="linea-punteada"></div>
          
          <!-- Detalle del servicio - Exactamente como en la imagen -->
          <table style="width: 100%;">
            <tr>
              <td style="width: 70%; font-weight: bold;">Descripción</td>
              <td style="width: 30%; text-align: right; font-weight: bold;">Precio</td>
            </tr>
          </table>
          
          <div class="servicio">
            <div class="servicio-detalle" style="display: flex; justify-content: space-between;">
              <div style="width: 70%">
                <div class="servicio-desc">Consulta Médica - ${
                  datosComprobante.especialidad || "Medicina General"
                }</div>
                <div class="servicio-adicional">Doctor: ${
                  datosComprobante.doctor || "-"
                }</div>
                <div class="servicio-adicional">Fecha: ${formatearFecha(
                  datosComprobante.fecha_consulta
                )} / Hora: ${formatearHora(
      datosComprobante.horaprogramada
    )}</div>
              </div>
              <div style="width: 30%; text-align: right;">S/ ${precio.toFixed(
                2
              )}</div>
            </div>
          </div>
          
          <!-- Línea sólida -->
          <div class="linea-solida"></div>
          
          <!-- Totales - Exactamente como en la imagen -->
          <div style="width: 100%;">
            <table style="width: 100%;">
              <tr>
                <td style="width: 85%; text-align: left; font-weight: bold; padding: 3px 0;">SUBTOTAL:</td>
                <td style="width: 15%; text-align: right; padding: 3px 0;">S/ ${(
                  precio / 1.18
                ).toFixed(2)}</td>
              </tr>
              <tr>
                <td style="width: 85%; text-align: left; font-weight: bold; padding: 3px 0;">IGV (18%):</td>
                <td style="width: 15%; text-align: right; padding: 3px 0;">S/ ${(
                  precio -
                  precio / 1.18
                ).toFixed(2)}</td>
              </tr>
              <tr style="border-top: 1px solid #000;">
                <td style="width: 85%; text-align: left; font-weight: bold; padding: 5px 0 3px 0;">TOTAL:</td>
                <td style="width: 15%; text-align: right; padding: 5px 0 3px 0;">S/ ${precio.toFixed(
                  2
                )}</td>
              </tr>
            </table>
          </div>
          
          <!-- Línea punteada -->
          <div class="linea-punteada"></div>
          
          <!-- INFORMACIÓN DE PAGO - CORREGIDA PARA FORMATO EXACTO COMO EN LA IMAGEN -->
          <div class="seccion-titulo">INFORMACIÓN DE PAGO</div>
          
          <!-- IMPORTANTE: Este es el formato correcto que debe coincidir con la imagen -->
          <table class="tabla-info">
            <tr>
              <td>Forma de Pago:</td>
              <td>${datosComprobante.tipopago || "EFECTIVO"}</td>
            </tr>
            ${seccionPagoHTML}
          </table>
          
          <!-- Línea punteada -->
          <div class="linea-punteada"></div>
          
          <!-- Pie de página -->
          <div class="pie-pagina">
            <div class="info-pie">
              <div>Forma de Pago: ${
                datosComprobante.tipopago || "EFECTIVO"
              }</div>
              <div>Atendido por: ${
                datosComprobante.usuario_venta || "Admin"
              }</div>
              <div>Caja: ${datosComprobante.id_cajero || "1"}</div>
            </div>
            <div class="codigo-qr">
              ■ ■<br>
              ■ ■
            </div>
          </div>
          
          <!-- Nota legal -->
          <div class="nota-legal">
            Autorizado mediante Resolución de Intendencia N° 034-005-0005429/SUNAT<br>
            Representación impresa de la BOLETA DE VENTA ELECTRÓNICA<br>
            Puede consultar este documento en www.clinicamedica.com/consulta
          </div>
        </div>
      </body>
      </html>
    `);
    frameDoc.close();

    // Esperar a que el contenido se cargue y luego imprimir
    setTimeout(() => {
      try {
        frameImpresion.contentWindow.focus();
        frameImpresion.contentWindow.print();

        // Eliminar el iframe después de imprimir
        setTimeout(() => {
          document.body.removeChild(frameImpresion);
        }, 1000);
      } catch (error) {
        console.error("Error al imprimir boleta:", error);
        Swal.fire({
          icon: "error",
          title: "Error de impresión",
          text: "Ocurrió un problema al imprimir. Intente nuevamente.",
        });
      }
    }, 500);
  } catch (error) {
    console.error("Error global en imprimirBoleta:", error);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "No se pudo generar la boleta para impresión.",
    });
  }
}

/**
 * FUNCIÓN CORREGIDA: Imprime una FACTURA
 * @param {Object} frameImpresion - Frame de impresión
 * @param {Object} datosComprobante - Datos del comprobante
 * @param {number} precio - Precio total
 * @param {number} montoPagado - Monto pagado
 * @param {number} vuelto - Vuelto calculado
 */
function imprimirFactura(
  frameImpresion,
  datosComprobante,
  precio,
  montoPagado,
  vuelto
) {
  try {
    console.log("Imprimiendo factura...");
    console.log("Tipo de pago para factura:", datosComprobante.tipopago);

    // Convertir el precio a texto
    const precioEntero = Math.floor(precio);
    const precioDecimales = Math.round((precio - precioEntero) * 100);
    const precioTexto =
      numeroALetras(precioEntero).toUpperCase() +
      " CON " +
      (precioDecimales === 0
        ? "CERO"
        : numeroALetras(precioDecimales).toUpperCase()) +
      " CENTAVOS";

    // Obtener datos de empresa de manera robusta
    let empresaRUC = "-";
    let empresaNombre = "-";
    let empresaDireccion = "-";

    // Múltiples estrategias para obtener el RUC
    if (
      datosComprobante.cliente_ruc &&
      datosComprobante.cliente_ruc.trim() !== ""
    ) {
      empresaRUC = datosComprobante.cliente_ruc;
    } else if (datosComprobante.ruc && datosComprobante.ruc.trim() !== "") {
      empresaRUC = datosComprobante.ruc;
    }

    // Múltiples estrategias para obtener la Razón Social
    if (
      datosComprobante.cliente_empresa &&
      datosComprobante.cliente_empresa.trim() !== ""
    ) {
      empresaNombre = datosComprobante.cliente_empresa;
    } else if (
      datosComprobante.razonsocial &&
      datosComprobante.razonsocial.trim() !== ""
    ) {
      empresaNombre = datosComprobante.razonsocial;
    }

    // Múltiples estrategias para obtener la Dirección
    if (
      datosComprobante.cliente_empresa_direccion &&
      datosComprobante.cliente_empresa_direccion.trim() !== ""
    ) {
      empresaDireccion = datosComprobante.cliente_empresa_direccion;
    } else if (
      datosComprobante.direccion &&
      datosComprobante.direccion.trim() !== ""
    ) {
      empresaDireccion = datosComprobante.direccion;
    }

    // Obtener tipos de documento para el paciente
    const tipoDocPaciente = obtenerTipoDocumentoTexto(
      datosComprobante.paciente_tipodoc || "DNI"
    );

    // NUEVO: Definir variables para el cliente
    let clienteDoc, clienteNombre;

    // IMPORTANTE: Verificar el tipo de cliente
    const tipoClienteVal =
      document.getElementById("tipoCliente")?.value || "empresa";

    if (tipoClienteVal === "empresa") {
      // SOLUCIÓN CLAVE: Si es empresa, usar datos del PACIENTE como CLIENTE
      clienteDoc = datosComprobante.paciente_nrodoc || "-";
      clienteNombre = datosComprobante.paciente || "-";
    } else if (tipoClienteVal === "personal") {
      // Si es pago personal, el PACIENTE es el CLIENTE
      clienteDoc = datosComprobante.paciente_nrodoc || "-";
      clienteNombre = datosComprobante.paciente || "-";
    } else if (tipoClienteVal === "tercero") {
      // Para pagadores terceros
      clienteDoc = datosComprobante.cliente_nrodoc || "-";
      clienteNombre = datosComprobante.cliente_natural || "-";
    } else {
      // Fallback
      clienteDoc = datosComprobante.cliente_nrodoc || "-";
      clienteNombre = datosComprobante.cliente_natural || "-";
    }

    // CORRECCIÓN: Determinar qué secciones mostrar según método de pago - SIN ÍCONOS
    let seccionPagoHTML = "";

    if (datosComprobante.tipopago === "EFECTIVO") {
      // Para EFECTIVO mostrar monto pagado y vuelto
      seccionPagoHTML = `
              <tr>
                <td style="width: 20%; font-weight: bold; padding: 3px 0;">Son:</td>
                <td style="padding: 3px 0;">${precioTexto}</td>
              </tr>
              <tr>
                <td style="width: 20%; font-weight: bold; padding: 3px 0;">Monto Pagado:</td>
                <td style="padding: 3px 0;">S/ ${montoPagado.toFixed(2)}</td>
              </tr>
              <tr>
                <td style="width: 20%; font-weight: bold; padding: 3px 0;">Vuelto:</td>
                <td style="padding: 3px 0;">S/ ${vuelto.toFixed(2)}</td>
              </tr>
      `;
    } else {
      // Para otros métodos, mostrar solo monto pagado
      seccionPagoHTML = `
              <tr>
                <td style="width: 20%; font-weight: bold; padding: 3px 0;">Son:</td>
                <td style="padding: 3px 0;">${precioTexto}</td>
              </tr>
              <tr>
                <td style="width: 20%; font-weight: bold; padding: 3px 0;">Monto Pagado:</td>
                <td style="padding: 3px 0;">S/ ${precio.toFixed(2)}</td>
              </tr>
      `;
    }

    // Escribir el documento del iframe con los cambios solicitados
    const frameDoc =
      frameImpresion.contentDocument || frameImpresion.contentWindow.document;
    frameDoc.open();
    frameDoc.write(`
      <!DOCTYPE html>
      <html lang="es">
      <head>
        <meta charset="UTF-8">
        <title>Impresión de Factura</title>
        <style>
          @page {
            size: 21cm 29.7cm;
            margin: 0;
          }
          
          body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #000;
            background-color: #fff;
          }
          
          /* Contenedor principal */
          .factura-contenedor {
            width: 18cm;
            margin: 0.5cm auto;
            padding: 0.5cm;
            border: 1px solid #000;
            color: #000;
            background-color: #fff;
          }
          
          /* Título principal */
          .titulo-principal {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 0.3cm;
            color: #000;
          }
          
          /* Cabecera */
          .cabecera {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.3cm;
          }
          
          /* Logo y datos de la clínica */
          .logo-clinica {
            width: 3cm;
            height: 2.5cm;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            text-align: center;
            font-size: 12pt;
            line-height: 1.4;
            color: #000;
            background-color: #fff;
          }
          
          .info-clinica {
            margin-top: 0.2cm;
            font-size: 8pt;
            color: #000;
          }
          
          /* Número de factura */
          .factura-numero {
            border: 2px solid #000;
            padding: 0.3cm;
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 0.2cm;
            color: #000;
            background-color: #fff;
          }
          
          .factura-fecha {
            text-align: right;
            font-size: 8pt;
            color: #000;
          }
          
          /* Líneas separadoras */
          .linea-divisoria {
            border-top: 1px dashed #000;
            margin: 0.3cm 0;
          }
          
          .linea-solida {
            border-top: 1px solid #000;
            margin: 0.3cm 0;
          }
          
          /* Títulos de secciones */
          .seccion-titulo {
            font-weight: bold;
            font-size: 11pt;
            margin: 0.2cm 0;
            text-transform: uppercase;
            color: #000;
          }
          
          /* Tabla de datos */
          .tabla-datos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.3cm;
            color: #000;
          }
          
          .tabla-datos td {
            padding: 0.1cm 0;
            font-size: 9pt;
            color: #000;
          }
          
          .tabla-datos td:first-child {
            width: 4cm;
            font-weight: bold;
            color: #000;
          }
          
          /* Detalle del servicio */
          .detalle-servicio {
            margin-bottom: 0.3cm;
            color: #000;
          }
          
          .cabecera-detalle {
            display: flex;
            justify-content: space-between;
            padding-bottom: 0.1cm;
            border-bottom: 1px solid #000;
            font-weight: bold;
            color: #000;
          }
          
          .item-servicio {
            display: flex;
            justify-content: space-between;
            padding: 0.1cm 0;
            border-bottom: 1px solid #eee;
            color: #000;
          }
          
          .desc-servicio span {
            display: block;
            font-size: 8pt;
            color: #000;
            margin-top: 0.1cm;
          }
         
          /* Información adicional y QR */
          .seccion-pie {
            display: flex;
            justify-content: space-between;
            margin-top: 0.3cm;
            color: #000;
          }
          
          .info-adicional {
            width: 70%;
            font-size: 8pt;
            color: #000;
          }
          
          .info-adicional p {
            margin: 0.1cm 0;
          }
          
          .codigo-qr {
            width: 2.5cm;
            height: 2.5cm;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16pt;
            color: #000;
            background-color: #fff;
          }
          
          /* Información legal */
          .info-legal {
            font-size: 7pt;
            text-align: center;
            margin-top: 0.3cm;
            color: #000;
          }
        </style>
      </head>
      <body>
        <div class="factura-contenedor">
          <!-- Título -->
          <div class="titulo-principal">FACTURA ELECTRÓNICA</div>
          
          <!-- Cabecera con logo y número -->
          <div class="cabecera">
            <div>
              <div class="logo-clinica">CLÍNICA<br>MÉDICA</div>
              <div class="info-clinica">
                <div>RUC: 20123456789</div>
                <div>Av. Principal 123, Lima</div>
                <div>Tel: (01) 555-1234</div>
              </div>
            </div>
            <div>
              <div class="factura-numero">${datosComprobante.nrodocumento}</div>
              <div class="factura-fecha">
                <div>Fecha: ${formatearFecha(
                  datosComprobante.fechaemision
                )}</div>
                <div>Hora: ${formatearHora(datosComprobante.fechaemision)}</div>
              </div>
            </div>
          </div>
          
          <!-- IMPORTANTE: REORDENADO - INFORMACIÓN GENERAL AHORA APARECE PRIMERO -->
          <div class="linea-divisoria"></div>
          <div class="seccion-titulo">INFORMACIÓN DE LA EMPRESA</div>
          <div class="linea-divisoria"></div>
          
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
          
          <!-- DESPUÉS DE LA INFORMACIÓN GENERAL, SIGUE LA INFORMACIÓN DEL CLIENTE Y PACIENTE -->
          <div class="linea-divisoria"></div>
          <div class="seccion-titulo">INFORMACIÓN DEL CLIENTE Y PACIENTE</div>
          <div class="linea-divisoria"></div>
          
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
              <td>${datosComprobante.paciente_nrodoc || "-"}</td>
            </tr>
            <tr>
              <td>Paciente:</td>
              <td>${datosComprobante.paciente || "-"}</td>
            </tr>
          </table>
          
          <!-- SECCIÓN DE DETALLE DE SERVICIOS -->
          <div class="linea-divisoria"></div>
          <div class="seccion-titulo">DETALLE DE SERVICIOS</div>
          <div class="linea-divisoria"></div>
          
          <table style="width: 100%;">
            <tr>
              <td style="font-weight: bold; width: 70%;">Descripción</td>
              <td style="font-weight: bold; width: 30%; text-align: right;">Precio</td>
            </tr>
          </table>
          
          <div style="width: 100%; display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
            <div style="width: 70%;">
              <div>Consulta Médica - ${datosComprobante.especialidad}</div>
              <div style="font-size: 9pt; margin-top: 3px; color: #555;">Doctor: ${
                datosComprobante.doctor
              }</div>
              <div style="font-size: 9pt; margin-top: 3px; color: #555;">Fecha: ${formatearFecha(
                datosComprobante.fecha_consulta
              )} / Hora: ${formatearHora(datosComprobante.horaprogramada)}</div>
            </div>
            <div style="width: 30%; text-align: right;">S/ ${precio.toFixed(
              2
            )}</div>
          </div>
          
          <!-- Línea sólida -->
          <div class="linea-solida"></div>
          
          <!-- Sección de totales EXACTAMENTE COMO EN LA IMAGEN 2 -->
          <table style="width: 100%; border-collapse: collapse;">
            <tr>
              <td style="text-align: left; font-weight: bold; padding: 3px 0;">VALOR VENTA:</td>
              <td style="text-align: right; padding: 3px 0;">S/ ${(
                precio / 1.18
              ).toFixed(2)}</td>
            </tr>
            <tr>
              <td style="text-align: left; font-weight: bold; padding: 3px 0;">IGV (18%):</td>
              <td style="text-align: right; padding: 3px 0;">S/ ${(
                precio -
                precio / 1.18
              ).toFixed(2)}</td>
            </tr>
            <tr style="border-top: 1px solid #000;">
              <td style="text-align: left; font-weight: bold; padding: 5px 0 3px 0;">TOTAL A PAGAR:</td>
              <td style="text-align: right; padding: 5px 0 3px 0;">S/ ${precio.toFixed(
                2
              )}</td>
            </tr>
          </table>
          
          <!-- Línea divisoria -->
          <div class="linea-divisoria"></div>
          
          <!-- CORRECCIÓN: Información de pago - Mostrar correctamente el tipo de pago -->
          <div class="seccion-titulo">INFORMACIÓN DE PAGO</div>
          <div class="linea-divisoria"></div>
          
          <table class="tabla-datos">
            <tr>
              <td>Forma de Pago:</td>
              <td>${datosComprobante.tipopago}</td>
            </tr>
            ${seccionPagoHTML}
          </table>
          
          <!-- Línea divisoria -->
          <div class="linea-divisoria"></div>
          
          <!-- Pie de página con CORRECCIÓN: "Atendido por" y "Cajero" en líneas separadas -->
          <div class="seccion-pie">
            <div class="info-adicional">
              <p><strong>Observaciones:</strong> El presente documento es una representación impresa de la Factura Electrónica autorizada.</p>
              <p><strong>Atendido por:</strong> ${
                datosComprobante.usuario_venta || "Administrador"
              }</p>
              <p><strong>Cajero:</strong> ${
                datosComprobante.id_cajero || "1"
              }</p>
            </div>
            <div>
              <div class="codigo-qr">
                ■ ■<br>
                ■ ■
              </div>
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
      </body>
      </html>
    `);
    frameDoc.close();

    // Esperar a que el contenido se cargue y luego imprimir
    setTimeout(() => {
      try {
        frameImpresion.contentWindow.focus();
        frameImpresion.contentWindow.print();

        // Eliminar el iframe después de imprimir
        setTimeout(() => {
          document.body.removeChild(frameImpresion);
        }, 1000);
      } catch (error) {
        console.error("Error al imprimir factura:", error);
        Swal.fire({
          icon: "error",
          title: "Error de impresión",
          text: "Ocurrió un problema al imprimir. Intente nuevamente.",
        });
      }
    }, 500);
  } catch (error) {
    console.error("Error global en imprimirFactura:", error);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "No se pudo generar la factura para impresión.",
    });
  }
}
/**
 * NUEVA FUNCIÓN: Imprime una FACTURA
 */
function imprimirFactura(
  frameImpresion,
  datosComprobante,
  precio,
  montoPagado,
  vuelto
) {
  try {
    console.log("Imprimiendo factura...");
    console.log("Tipo de pago para factura:", datosComprobante.tipopago);

    // Convertir el precio a texto
    const precioEntero = Math.floor(precio);
    const precioDecimales = Math.round((precio - precioEntero) * 100);
    const precioTexto =
      numeroALetras(precioEntero).toUpperCase() +
      " CON " +
      (precioDecimales === 0
        ? "CERO"
        : numeroALetras(precioDecimales).toUpperCase()) +
      " CENTAVOS";

    // Obtener datos de empresa de manera robusta
    let empresaRUC = "-";
    let empresaNombre = "-";
    let empresaDireccion = "-";

    // Múltiples estrategias para obtener el RUC
    if (
      datosComprobante.cliente_ruc &&
      datosComprobante.cliente_ruc.trim() !== ""
    ) {
      empresaRUC = datosComprobante.cliente_ruc;
    } else if (datosComprobante.ruc && datosComprobante.ruc.trim() !== "") {
      empresaRUC = datosComprobante.ruc;
    }

    // Múltiples estrategias para obtener la Razón Social
    if (
      datosComprobante.cliente_empresa &&
      datosComprobante.cliente_empresa.trim() !== ""
    ) {
      empresaNombre = datosComprobante.cliente_empresa;
    } else if (
      datosComprobante.razonsocial &&
      datosComprobante.razonsocial.trim() !== ""
    ) {
      empresaNombre = datosComprobante.razonsocial;
    }

    // Múltiples estrategias para obtener la Dirección
    if (
      datosComprobante.cliente_empresa_direccion &&
      datosComprobante.cliente_empresa_direccion.trim() !== ""
    ) {
      empresaDireccion = datosComprobante.cliente_empresa_direccion;
    } else if (
      datosComprobante.direccion &&
      datosComprobante.direccion.trim() !== ""
    ) {
      empresaDireccion = datosComprobante.direccion;
    }

    // Obtener tipos de documento para el paciente
    const tipoDocPaciente = obtenerTipoDocumentoTexto(
      datosComprobante.paciente_tipodoc || "DNI"
    );

    // CORRECCIÓN PRINCIPAL: Determinar cliente en base al tipo de cliente
    const tipoClienteVal =
      document.getElementById("tipoCliente")?.value || "empresa";

    // NUEVO: Definir variables para el cliente
    let clienteDoc, clienteNombre;

    if (tipoClienteVal === "empresa") {
      // SOLUCIÓN CLAVE: Si es empresa, usar datos del PACIENTE como CLIENTE
      clienteDoc = datosComprobante.paciente_nrodoc || "-";
      clienteNombre = datosComprobante.paciente || "-";
    } else if (tipoClienteVal === "personal") {
      // Si es pago personal, el PACIENTE es el CLIENTE
      clienteDoc = datosComprobante.paciente_nrodoc || "-";
      clienteNombre = datosComprobante.paciente || "-";
    } else if (tipoClienteVal === "tercero") {
      // Para pagadores terceros
      clienteDoc = datosComprobante.cliente_nrodoc || "-";
      clienteNombre = datosComprobante.cliente_natural || "-";
    } else {
      // Fallback
      clienteDoc = datosComprobante.cliente_nrodoc || "-";
      clienteNombre = datosComprobante.cliente_natural || "-";
    }

    // CORRECCIÓN: Determinar qué secciones mostrar según método de pago
    let seccionPagoHTML = "";

    if (datosComprobante.tipopago === "EFECTIVO") {
      // Para EFECTIVO mostrar monto pagado y vuelto
      seccionPagoHTML = `
              <tr>
                <td style="width: 20%; font-weight: bold; padding: 3px 0;">Son:</td>
                <td style="padding: 3px 0;">${precioTexto}</td>
              </tr>
              <tr>
                <td style="width: 20%; font-weight: bold; padding: 3px 0;">Monto Pagado:</td>
                <td style="padding: 3px 0;">S/ ${montoPagado.toFixed(2)}</td>
              </tr>
              <tr>
                <td style="width: 20%; font-weight: bold; padding: 3px 0;">Vuelto:</td>
                <td style="padding: 3px 0;">S/ ${vuelto.toFixed(2)}</td>
              </tr>
      `;
    } else {
      // Para otros métodos, mostrar solo monto pagado
      seccionPagoHTML = `
              <tr>
                <td style="width: 20%; font-weight: bold; padding: 3px 0;">Son:</td>
                <td style="padding: 3px 0;">${precioTexto}</td>
              </tr>
              <tr>
                <td style="width: 20%; font-weight: bold; padding: 3px 0;">Monto Pagado:</td>
                <td style="padding: 3px 0;">S/ ${precio.toFixed(2)}</td>
              </tr>
      `;
    }

    // Escribir el documento del iframe con los cambios solicitados
    const frameDoc =
      frameImpresion.contentDocument || frameImpresion.contentWindow.document;
    frameDoc.open();
    frameDoc.write(`
      <!DOCTYPE html>
      <html lang="es">
      <head>
        <meta charset="UTF-8">
        <title>Impresión de Factura</title>
        <style>
          @page {
            size: 21cm 29.7cm;
            margin: 0;
          }
          
          body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #000;
            background-color: #fff;
          }
          
          /* Contenedor principal */
          .factura-contenedor {
            width: 18cm;
            margin: 0.5cm auto;
            padding: 0.5cm;
            border: 1px solid #000;
            color: #000;
            background-color: #fff;
          }
          
          /* Título principal */
          .titulo-principal {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 0.3cm;
            color: #000;
          }
          
          /* Cabecera */
          .cabecera {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.3cm;
          }
          
          /* Logo y datos de la clínica */
          .logo-clinica {
            width: 3cm;
            height: 2.5cm;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            text-align: center;
            font-size: 12pt;
            line-height: 1.4;
            color: #000;
            background-color: #fff;
          }
          
          .info-clinica {
            margin-top: 0.2cm;
            font-size: 8pt;
            color: #000;
          }
          
          /* Número de factura */
          .factura-numero {
            border: 2px solid #000;
            padding: 0.3cm;
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 0.2cm;
            color: #000;
            background-color: #fff;
          }
          
          .factura-fecha {
            text-align: right;
            font-size: 8pt;
            color: #000;
          }
          
          /* Líneas separadoras */
          .linea-divisoria {
            border-top: 1px dashed #000;
            margin: 0.3cm 0;
          }
          
          .linea-solida {
            border-top: 1px solid #000;
            margin: 0.3cm 0;
          }
          
          /* Títulos de secciones */
          .seccion-titulo {
            font-weight: bold;
            font-size: 11pt;
            margin: 0.2cm 0;
            text-transform: uppercase;
            color: #000;
          }
          
          /* Tabla de datos */
          .tabla-datos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.3cm;
            color: #000;
          }
          
          .tabla-datos td {
            padding: 0.1cm 0;
            font-size: 9pt;
            color: #000;
          }
          
          .tabla-datos td:first-child {
            width: 4cm;
            font-weight: bold;
            color: #000;
          }
          
          /* Detalle del servicio */
          .detalle-servicio {
            margin-bottom: 0.3cm;
            color: #000;
          }
          
          .cabecera-detalle {
            display: flex;
            justify-content: space-between;
            padding-bottom: 0.1cm;
            border-bottom: 1px solid #000;
            font-weight: bold;
            color: #000;
          }
          
          .item-servicio {
            display: flex;
            justify-content: space-between;
            padding: 0.1cm 0;
            border-bottom: 1px solid #eee;
            color: #000;
          }
          
          .desc-servicio span {
            display: block;
            font-size: 8pt;
            color: #000;
            margin-top: 0.1cm;
          }
         
          /* Información adicional y QR */
          .seccion-pie {
            display: flex;
            justify-content: space-between;
            margin-top: 0.3cm;
            color: #000;
          }
          
          .info-adicional {
            width: 70%;
            font-size: 8pt;
            color: #000;
          }
          
          .info-adicional p {
            margin: 0.1cm 0;
          }
          
          .codigo-qr {
            width: 2.5cm;
            height: 2.5cm;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16pt;
            color: #000;
            background-color: #fff;
          }
          
          /* Información legal */
          .info-legal {
            font-size: 7pt;
            text-align: center;
            margin-top: 0.3cm;
            color: #000;
          }
        </style>
      </head>
      <body>
        <div class="factura-contenedor">
          <!-- Título -->
          <div class="titulo-principal">FACTURA ELECTRÓNICA</div>
          
          <!-- Cabecera con logo y número -->
          <div class="cabecera">
            <div>
              <div class="logo-clinica">CLÍNICA<br>MÉDICA</div>
              <div class="info-clinica">
                <div>RUC: 20123456789</div>
                <div>Av. Principal 123, Lima</div>
                <div>Tel: (01) 555-1234</div>
              </div>
            </div>
            <div>
              <div class="factura-numero">${datosComprobante.nrodocumento}</div>
              <div class="factura-fecha">
                <div>Fecha: ${formatearFecha(
                  datosComprobante.fechaemision
                )}</div>
                <div>Hora: ${formatearHora(datosComprobante.fechaemision)}</div>
              </div>
            </div>
          </div>
          
          <!-- IMPORTANTE: REORDENADO - INFORMACIÓN GENERAL AHORA APARECE PRIMERO -->
          <div class="linea-divisoria"></div>
          <div class="seccion-titulo">INFORMACIÓN DE LA EMPRESA</div>
          <div class="linea-divisoria"></div>
          
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
          
          <!-- DESPUÉS DE LA INFORMACIÓN GENERAL, SIGUE LA INFORMACIÓN DEL CLIENTE Y PACIENTE -->
          <div class="linea-divisoria"></div>
          <div class="seccion-titulo">INFORMACIÓN DEL CLIENTE Y PACIENTE</div>
          <div class="linea-divisoria"></div>
          
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
              <td>${datosComprobante.paciente_nrodoc || "-"}</td>
            </tr>
            <tr>
              <td>Paciente:</td>
              <td>${datosComprobante.paciente || "-"}</td>
            </tr>
          </table>
          
          <!-- SECCIÓN DE DETALLE DE SERVICIOS -->
          <div class="linea-divisoria"></div>
          <div class="seccion-titulo">DETALLE DE SERVICIOS</div>
          <div class="linea-divisoria"></div>
          
          <table style="width: 100%;">
            <tr>
              <td style="font-weight: bold; width: 70%;">Descripción</td>
              <td style="font-weight: bold; width: 30%; text-align: right;">Precio</td>
            </tr>
          </table>
          
          <div style="width: 100%; display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
            <div style="width: 70%;">
              <div>Consulta Médica - ${datosComprobante.especialidad}</div>
              <div style="font-size: 9pt; margin-top: 3px; color: #555;">Doctor: ${
                datosComprobante.doctor
              }</div>
              <div style="font-size: 9pt; margin-top: 3px; color: #555;">Fecha: ${formatearFecha(
                datosComprobante.fecha_consulta
              )} / Hora: ${formatearHora(datosComprobante.horaprogramada)}</div>
            </div>
            <div style="width: 30%; text-align: right;">S/ ${precio.toFixed(
              2
            )}</div>
          </div>
          
          <!-- Línea sólida -->
          <div class="linea-solida"></div>
          
          <!-- Sección de totales EXACTAMENTE COMO EN LA IMAGEN 2 -->
          <table style="width: 100%; border-collapse: collapse;">
            <tr>
              <td style="text-align: left; font-weight: bold; padding: 3px 0;">VALOR VENTA:</td>
              <td style="text-align: right; padding: 3px 0;">S/ ${(
                precio / 1.18
              ).toFixed(2)}</td>
            </tr>
            <tr>
              <td style="text-align: left; font-weight: bold; padding: 3px 0;">IGV (18%):</td>
              <td style="text-align: right; padding: 3px 0;">S/ ${(
                precio -
                precio / 1.18
              ).toFixed(2)}</td>
            </tr>
            <tr style="border-top: 1px solid #000;">
              <td style="text-align: left; font-weight: bold; padding: 5px 0 3px 0;">TOTAL A PAGAR:</td>
              <td style="text-align: right; padding: 5px 0 3px 0;">S/ ${precio.toFixed(
                2
              )}</td>
            </tr>
          </table>
          
          <!-- Línea divisoria -->
          <div class="linea-divisoria"></div>
          
          <!-- CORRECCIÓN: Información de pago - Mostrar correctamente el tipo de pago -->
          <div style="width: 100%;">
            <div style="font-weight: bold; margin: 0.3cm 0 0.2cm 0; text-transform: uppercase;">
              Información de Pago
            </div>
            
            <table style="width: 100%; border-collapse: collapse;">
              <tr>
                <td style="width: 20%; font-weight: bold; padding: 3px 0;">Forma de Pago:</td>
                <td style="padding: 3px 0;">${datosComprobante.tipopago}</td>
              </tr>
              ${seccionPagoHTML}
            </table>
          </div>
          
          <!-- Línea divisoria -->
          <div class="linea-divisoria"></div>
          
          <!-- Pie de página con CORRECCIÓN: "Atendido por" y "Cajero" en líneas separadas -->
          <div class="seccion-pie">
            <div class="info-adicional">
              <p><strong>Observaciones:</strong> El presente documento es una representación impresa de la Factura Electrónica autorizada.</p>
              <p><strong>Atendido por:</strong> ${
                datosComprobante.usuario_venta || "Administrador"
              }</p>
              <p><strong>Cajero:</strong> ${
                datosComprobante.id_cajero || "1"
              }</p>
            </div>
            <div>
              <div class="codigo-qr">
                ■ ■<br>
                ■ ■
              </div>
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
      </body>
      </html>
    `);
    frameDoc.close();

    // Esperar a que el contenido se cargue y luego imprimir
    setTimeout(() => {
      try {
        frameImpresion.contentWindow.focus();
        frameImpresion.contentWindow.print();

        // Eliminar el iframe después de imprimir
        setTimeout(() => {
          document.body.removeChild(frameImpresion);
        }, 1000);
      } catch (error) {
        console.error("Error al imprimir factura:", error);
        Swal.fire({
          icon: "error",
          title: "Error de impresión",
          text: "Ocurrió un problema al imprimir. Intente nuevamente.",
        });
      }
    }, 500);
  } catch (error) {
    console.error("Error global en imprimirFactura:", error);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "No se pudo generar la factura para impresión.",
    });
  }
}

/**
 * NUEVA FUNCIÓN: Obtiene el texto descriptivo para un tipo de documento
 * @param {string} tipoDoc Tipo de documento en el sistema
 * @return {string} Texto descriptivo para mostrar
 */
function obtenerTipoDocumentoTexto(tipoDoc) {
  if (!tipoDoc) return "DNI";
  const tipo = tipoDoc.toUpperCase();
  const tipos = {
    DNI: "DNI",
    PASAPORTE: "Pasaporte",
    "CARNET DE EXTRANJERIA": "Carnet",
    OTRO: "Doc",
  };
  return tipos[tipo] || tipo;
}

/**
 * FUNCIÓN COMPLEMENTARIA: Convierte documentos de API/BD al formato UI
 * @param {string} tipoDoc Tipo de documento en formato de BD
 * @return {string} Tipo de documento en formato UI
 */
function convertirTipoDocumentoParaUI(tipoDoc) {
  if (!tipoDoc) return "dni";

  const tipo = tipoDoc.toUpperCase();

  const mapeo = {
    DNI: "dni",
    PASAPORTE: "pasaporte",
    "CARNET DE EXTRANJERIA": "carnet",
    OTRO: "otro",
  };

  return mapeo[tipo] || "dni";
}

// Añade esta función para convertir números a texto en español
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
    "nueve",
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
    "noventa",
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
    "diecinueve",
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
    "novecientos",
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
        resultado +=
          decenas[Math.floor(numero / 10)] + " y " + unidades[unidad];
      }
    }
  }

  return resultado.trim();
}
