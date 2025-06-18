<?php /*RUTA: sistemaclinica/mdeols/Venta.php*/ ?>
<?php
require_once 'Conexion.php';

class Venta
{
    private $pdo;

    public function __CONSTRUCT()
    {
        $this->pdo = (new Conexion())->getConexion();
    }

    /**
     * Registra una nueva venta
     * @param array $datos Datos de la venta
     * @return array Resultado de la operación
     */
    public function registrarVenta($datos)
    {
        try {
            $this->pdo->beginTransaction();

            // Generar número de documento (puedes personalizar esto según tu lógica)
            $prefijo = ($datos['tipocomprobante'] == 'FACTURA') ? 'F001-' : 'B001-';
            $secuencia = $this->obtenerSiguienteSecuencia($datos['tipocomprobante']);
            $nroDocumento = $prefijo . str_pad($secuencia, 8, '0', STR_PAD_LEFT);

            // Preparar query para insertar venta
            $stmt = $this->pdo->prepare("
            INSERT INTO ventas (
                idcliente, tipodoc, nrodocumento, fechaemision, 
                fecharegistro, tipopago, idusuariocaja, montopagado
            ) VALUES (
                :idcliente, :tipodoc, :nrodocumento, NOW(), 
                NOW(), :tipopago, :idusuariocaja, :montopagado
            )
        ");

            // Ejecutar query
            $stmt->execute([
                'idcliente' => $datos['idcliente'],
                'tipodoc' => $datos['tipocomprobante'],
                'nrodocumento' => $nroDocumento,
                'tipopago' => $datos['tipopago'],
                'idusuariocaja' => $datos['idusuariocaja'],
                'montopagado' => $datos['montopagado']
            ]);

            // Obtener ID de la venta insertada
            $idventa = $this->pdo->lastInsertId();

            // Insertar detalle de venta
            $stmt = $this->pdo->prepare("
            INSERT INTO detalleventas (
                idventa, idconsulta, precio
            ) VALUES (
                :idventa, :idconsulta, :precio
            )
        ");

            $stmt->execute([
                'idventa' => $idventa,
                'idconsulta' => $datos['idconsulta'],
                'precio' => $datos['precio']
            ]);

            // IMPORTANTE: Se elimina o comenta el código que actualizaba el estado a REALIZADA
            // para mantener el estado "PROGRAMADA" original
            /*
            $stmt = $this->pdo->prepare("
                UPDATE citas c
                INNER JOIN consultas co ON c.fecha = co.fecha AND c.hora = co.horaprogramada
                SET c.estado = 'REALIZADA'
                WHERE co.idconsulta = :idconsulta
            ");
            
            $stmt->execute(['idconsulta' => $datos['idconsulta']]);
            */

            $this->pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Venta registrada correctamente',
                'idventa' => $idventa,
                'nrodocumento' => $nroDocumento
            ];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return [
                'status' => false,
                'mensaje' => 'Error al registrar la venta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene el siguiente número de secuencia para el tipo de documento
     * @param string $tipoDocumento Tipo de documento (BOLETA o FACTURA)
     * @return int Siguiente número de secuencia
     */
    private function obtenerSiguienteSecuencia($tipoDocumento)
    {
        $stmt = $this->pdo->prepare("
            SELECT MAX(CAST(SUBSTRING(nrodocumento, 6) AS UNSIGNED)) as ultimo
            FROM ventas
            WHERE tipodoc = :tipodoc
        ");

        $stmt->execute(['tipodoc' => $tipoDocumento]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($resultado['ultimo'] ?? 0) + 1;
    }

    /**
     * Obtiene los datos del comprobante
     * @param int $idventa ID de la venta
     * @return array|false Datos del comprobante o false si no existe
     */
    public function obtenerDatosComprobante($idventa)
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT 
                v.idventa,
                v.tipodoc,
                v.nrodocumento,
                v.fechaemision,
                v.tipopago,
                v.montopagado,
                dv.precio,
                -- Datos del cliente
                cl.tipocliente,
                cl.idpersona as cliente_idpersona,
                -- CAMBIO CLAVE: Manejar correctamente el caso de clientes EMPRESA con persona asociada
                CASE 
                    WHEN cl.tipocliente = 'EMPRESA' AND cl.idpersona IS NOT NULL THEN CONCAT(per_persona.apellidos, ', ', per_persona.nombres)
                    WHEN cl.idpersona IS NOT NULL THEN CONCAT(per_cl.apellidos, ', ', per_cl.nombres)
                    ELSE e.razonsocial
                END AS cliente_natural,
                CASE 
                    WHEN cl.tipocliente = 'EMPRESA' AND cl.idpersona IS NOT NULL THEN per_persona.tipodoc
                    WHEN cl.idpersona IS NOT NULL THEN per_cl.tipodoc
                    ELSE 'RUC'
                END AS cliente_tipodoc,
                CASE 
                    WHEN cl.tipocliente = 'EMPRESA' AND cl.idpersona IS NOT NULL THEN per_persona.nrodoc
                    WHEN cl.idpersona IS NOT NULL THEN per_cl.nrodoc
                    ELSE e.ruc
                END AS cliente_nrodoc,
                CASE 
                    WHEN cl.tipocliente = 'EMPRESA' AND cl.idpersona IS NOT NULL THEN per_persona.direccion
                    WHEN cl.idpersona IS NOT NULL THEN per_cl.direccion
                    ELSE e.direccion
                END AS cliente_direccion,
                -- Datos de empresa (siempre presentes para facturas)
                e.razonsocial AS cliente_empresa,
                e.ruc AS cliente_ruc,
                e.direccion AS cliente_empresa_direccion,
                -- Datos del paciente
                CONCAT(per_pac.apellidos, ', ', per_pac.nombres) AS paciente,
                per_pac.tipodoc AS paciente_tipodoc,
                per_pac.nrodoc AS paciente_nrodoc,
                -- Datos de la consulta
                esp.especialidad,
                CONCAT(per_doc.apellidos, ', ', per_doc.nombres) AS doctor,
                con.fecha AS fecha_consulta,
                con.horaprogramada,
                -- Datos del cajero
                CONCAT(per_user.apellidos, ', ', per_user.nombres) AS usuario_venta,
                v.idusuariocaja AS id_cajero
            FROM 
                ventas v
            LEFT JOIN 
                detalleventas dv ON v.idventa = dv.idventa
            LEFT JOIN 
                clientes cl ON v.idcliente = cl.idcliente
            LEFT JOIN 
                personas per_cl ON cl.idpersona = per_cl.idpersona
            LEFT JOIN 
                empresas e ON cl.idempresa = e.idempresa
            -- NUEVO: Join adicional para persona asociada a cliente EMPRESA
            LEFT JOIN 
                personas per_persona ON cl.idpersona = per_persona.idpersona
            LEFT JOIN 
                consultas con ON dv.idconsulta = con.idconsulta
            LEFT JOIN 
                pacientes pac ON con.idpaciente = pac.idpaciente
            LEFT JOIN 
                personas per_pac ON pac.idpersona = per_pac.idpersona
            LEFT JOIN 
                horarios h ON con.idhorario = h.idhorario
            LEFT JOIN 
                atenciones a ON h.idatencion = a.idatencion
            LEFT JOIN 
                contratos c ON a.idcontrato = c.idcontrato
            LEFT JOIN 
                colaboradores col ON c.idcolaborador = col.idcolaborador
            LEFT JOIN 
                personas per_doc ON col.idpersona = per_doc.idpersona
            LEFT JOIN 
                especialidades esp ON col.idespecialidad = esp.idespecialidad
            LEFT JOIN 
                usuarios u ON v.idusuariocaja = u.idusuario
            LEFT JOIN 
                contratos c_user ON u.idcontrato = c_user.idcontrato
            LEFT JOIN 
                colaboradores col_user ON c_user.idcolaborador = col_user.idcolaborador
            LEFT JOIN 
                personas per_user ON col_user.idpersona = per_user.idpersona
            WHERE 
                v.idventa = :idventa
        ");

            $stmt->execute(['idventa' => $idventa]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            // Registrar para depuración
            if ($resultado) {
                error_log("Datos del comprobante obtenidos: " . json_encode($resultado));

                // Verificar si tenemos un cliente persona asociado a un cliente empresa
                if ($resultado['tipocliente'] === 'EMPRESA' && !empty($resultado['cliente_idpersona'])) {
                    error_log("IMPORTANTE: Cliente EMPRESA con persona asociada detectado (ID: " . $resultado['cliente_idpersona'] . ")");
                }
            }

            return $resultado;
        } catch (Exception $e) {
            error_log("Error al obtener datos de comprobante: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Obtiene los datos del comprobante a partir del ID de cita
     * @param int $idcita ID de la cita
     * @return array|false Datos del comprobante o false si no existe
     */
    public function obtenerDatosComprobantePorCita($idcita)
    {
        try {
            // Validar el ID de cita
            if (!is_numeric($idcita) || $idcita <= 0) {
                error_log("ID de cita inválido: $idcita");
                return false;
            }

            // Mejorar el logging para diagnóstico
            error_log("Buscando comprobante para cita ID: $idcita");

            // Primero buscar la consulta asociada a la cita
            $stmt = $this->pdo->prepare("
            SELECT con.idconsulta 
            FROM citas c
            JOIN consultas con ON c.fecha = con.fecha AND c.hora = con.horaprogramada
            WHERE c.idcita = ?
        ");
            $stmt->execute([$idcita]);
            $consulta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$consulta) {
                error_log("No se encontró consulta para la cita ID: $idcita");
                return false; // No se encontró consulta para esta cita
            }

            error_log("Consulta encontrada para cita ID: $idcita - Consulta ID: {$consulta['idconsulta']}");

            // Buscar el detalle de venta para esta consulta
            $stmt = $this->pdo->prepare("
            SELECT dv.idventa
            FROM detalleventas dv
            WHERE dv.idconsulta = ?
        ");
            $stmt->execute([$consulta['idconsulta']]);
            $detalleVenta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$detalleVenta) {
                error_log("No se encontró venta para la consulta ID: {$consulta['idconsulta']}");
                return false; // No hay venta para esta consulta
            }

            error_log("Venta encontrada ID: {$detalleVenta['idventa']} para cita ID: $idcita");

            // CONSULTA CORREGIDA: Obtiene correctamente los datos de cliente, empresa, paciente y doctor
            $stmt = $this->pdo->prepare("
            SELECT 
                v.idventa,
                v.tipodoc,
                v.nrodocumento,
                v.fechaemision,
                v.tipopago,
                v.montopagado,
                dv.precio,
                cl.tipocliente,
                cl.idpersona AS cliente_idpersona,
                cl.idempresa AS cliente_idempresa,
                
                -- Datos de cliente natural (persona)
                per_cl.apellidos AS cliente_apellidos,
                per_cl.nombres AS cliente_nombres,
                per_cl.tipodoc AS cliente_tipodoc,
                per_cl.nrodoc AS cliente_nrodoc,
                per_cl.direccion AS cliente_direccion,
                
                -- Datos completos para cliente natural
                CONCAT(per_cl.apellidos, ', ', per_cl.nombres) AS cliente_natural,
                
                -- Datos de empresa
                e.razonsocial AS cliente_empresa,
                e.ruc AS cliente_ruc,
                e.direccion AS cliente_empresa_direccion,
                
                -- Datos del paciente
                per_pac.apellidos AS paciente_apellido,
                per_pac.nombres AS paciente_nombre,
                CONCAT(per_pac.apellidos, ', ', per_pac.nombres) AS paciente,
                per_pac.tipodoc AS paciente_tipodoc,
                per_pac.nrodoc AS paciente_nrodoc,
                
                -- Datos de la consulta y doctor
                esp.especialidad,
                per_doc.apellidos AS doctor_apellido,
                per_doc.nombres AS doctor_nombre,
                CONCAT(per_doc.apellidos, ', ', per_doc.nombres) AS doctor,
                con.fecha AS fecha_consulta,
                con.horaprogramada,
                
                -- Datos del usuario de venta
                u.idusuario AS idusuariocaja,
                (SELECT CONCAT(p.apellidos, ', ', p.nombres) 
                 FROM personas p 
                 JOIN colaboradores c ON p.idpersona = c.idpersona 
                 JOIN contratos con ON c.idcolaborador = con.idcolaborador 
                 JOIN usuarios us ON con.idcontrato = us.idcontrato 
                 WHERE us.idusuario = v.idusuariocaja) AS usuario_venta,
                v.idusuariocaja AS id_cajero
            FROM 
                ventas v
            JOIN 
                detalleventas dv ON v.idventa = dv.idventa
            JOIN 
                consultas con ON dv.idconsulta = con.idconsulta
            JOIN 
                pacientes pac ON con.idpaciente = pac.idpaciente
            JOIN 
                personas per_pac ON pac.idpersona = per_pac.idpersona
            JOIN 
                clientes cl ON v.idcliente = cl.idcliente
            LEFT JOIN 
                personas per_cl ON cl.idpersona = per_cl.idpersona
            LEFT JOIN 
                empresas e ON cl.idempresa = e.idempresa
            LEFT JOIN 
                horarios h ON con.idhorario = h.idhorario
            LEFT JOIN 
                atenciones a ON h.idatencion = a.idatencion
            LEFT JOIN 
                contratos c ON a.idcontrato = c.idcontrato
            LEFT JOIN 
                colaboradores col ON c.idcolaborador = col.idcolaborador
            LEFT JOIN 
                personas per_doc ON col.idpersona = per_doc.idpersona
            LEFT JOIN 
                especialidades esp ON col.idespecialidad = esp.idespecialidad
            LEFT JOIN 
                usuarios u ON v.idusuariocaja = u.idusuario
            WHERE 
                v.idventa = ?
        ");

            $stmt->execute([$detalleVenta['idventa']]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            // Mejorar el logging de resultados
            if ($resultado) {
                error_log("Datos del comprobante obtenidos correctamente para venta ID: {$detalleVenta['idventa']}");

                // Logging adicional para diagnosticar los campos específicos
                error_log("Tipo de cliente: " . $resultado['tipocliente']);
                error_log("Cliente natural: " . ($resultado['cliente_natural'] ?? 'No disponible'));
                error_log("Cliente empresa: " . ($resultado['cliente_empresa'] ?? 'No disponible'));
                error_log("Paciente: " . ($resultado['paciente'] ?? 'No disponible'));
                error_log("Doctor: " . ($resultado['doctor'] ?? 'No disponible'));
            } else {
                error_log("No se pudieron obtener datos del comprobante para venta ID: {$detalleVenta['idventa']}");
            }

            return $resultado;
        } catch (Exception $e) {
            error_log("Error en obtenerDatosComprobantePorCita: " . $e->getMessage());
            error_log("Traza: " . $e->getTraceAsString());
            return false;
        }
    }
}
