<?php /*RUTA: sistemaclinica/models/Devolucion.php*/?>
<?php
require_once 'Conexion.php';

class Devolucion
{
    private $pdo;

    public function __CONSTRUCT()
    {
        $this->pdo = (new Conexion())->getConexion();
    }

    /**
     * Registra una nueva devolución
     * @param array $datos Datos de la devolución
     * @return array Resultado de la operación
     */
    public function registrar($datos)
    {
        try {
            // Log para depuración
            error_log("Modelo Devolucion->registrar: Datos recibidos: " . json_encode($datos));

            // Validar datos obligatorios
            if (!isset($datos['idcita']) || !isset($datos['monto']) || !isset($datos['motivo']) || !isset($datos['metodo'])) {
                error_log("Modelo Devolucion->registrar: Faltan datos obligatorios");
                return [
                    'status' => false,
                    'mensaje' => 'Faltan datos obligatorios para registrar la devolución'
                ];
            }

            $this->pdo->beginTransaction();

            // Generar número de comprobante de devolución
            $numeroDevolucion = 'DEV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Verificar si ya existe una devolución para esta cita
            $stmtCheck = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM devoluciones
                WHERE idcita = :idcita
            ");
            $stmtCheck->execute([':idcita' => $datos['idcita']]);
            $existeDevolucion = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existeDevolucion['total'] > 0) {
                $this->pdo->rollBack();
                error_log("Modelo Devolucion->registrar: Ya existe una devolución para esta cita");
                return [
                    'status' => false,
                    'mensaje' => 'Ya existe una devolución registrada para esta cita'
                ];
            }

            // Consulta para insertar devolución
            $stmt = $this->pdo->prepare("
                INSERT INTO devoluciones (
                    idcita, 
                    idventa, 
                    monto, 
                    motivo, 
                    observaciones, 
                    metodo, 
                    fecha_devolucion,
                    idusuario,
                    numero_comprobante
                ) VALUES (
                    :idcita, 
                    :idventa, 
                    :monto, 
                    :motivo, 
                    :observaciones, 
                    :metodo, 
                    NOW(),
                    :idusuario,
                    :numero_comprobante
                )
            ");

            $parametros = [
                ':idcita' => $datos['idcita'],
                ':idventa' => $datos['idventa'],
                ':monto' => $datos['monto'],
                ':motivo' => $datos['motivo'],
                ':observaciones' => $datos['observaciones'],
                ':metodo' => $datos['metodo'],
                ':idusuario' => $datos['idusuario'],
                ':numero_comprobante' => $numeroDevolucion
            ];

            // Log para depuración
            error_log("Modelo Devolucion->registrar: Ejecutando insert con parámetros: " . json_encode($parametros));

            $stmt->execute($parametros);
            $iddevolucion = $this->pdo->lastInsertId();

            error_log("Modelo Devolucion->registrar: Insert exitoso, ID: " . $iddevolucion);

            // Si hay una venta asociada, actualizar el monto pagado en la venta
            if ($datos['idventa']) {
                $stmtVenta = $this->pdo->prepare("
                    UPDATE ventas 
                    SET montopagado = montopagado - :monto
                    WHERE idventa = :idventa
                ");

                $stmtVenta->execute([
                    ':monto' => $datos['monto'],
                    ':idventa' => $datos['idventa']
                ]);

                error_log("Modelo Devolucion->registrar: Venta actualizada correctamente");
            }

            $this->pdo->commit();

            error_log("Modelo Devolucion->registrar: Transacción completada con éxito");

            return [
                'status' => true,
                'mensaje' => 'Devolución registrada correctamente',
                'iddevolucion' => $iddevolucion,
                'numero_comprobante' => $numeroDevolucion
            ];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            error_log("Error al registrar devolución: " . $e->getMessage());
            error_log("Traza: " . $e->getTraceAsString());

            return [
                'status' => false,
                'mensaje' => 'Error al registrar la devolución: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene los datos de una devolución específica
     * @param int $iddevolucion ID de la devolución
     * @return array|false Datos de la devolución o false si no existe
     */
    public function obtenerPorId($iddevolucion)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    d.*,
                    c.fecha as fecha_cita,
                    c.hora as hora_cita,
                    c.estado as estado_cita,
                    v.tipodoc as tipo_comprobante,
                    v.nrodocumento as numero_comprobante,
                    CONCAT(p.apellidos, ', ', p.nombres) as nombre_paciente,
                    p.tipodoc as tipo_documento_paciente,
                    p.nrodoc as numero_documento_paciente,
                    CONCAT(u.nomuser) as usuario_registro
                FROM 
                    devoluciones d
                LEFT JOIN 
                    citas c ON d.idcita = c.idcita
                LEFT JOIN 
                    ventas v ON d.idventa = v.idventa
                LEFT JOIN 
                    personas p ON c.idpersona = p.idpersona
                LEFT JOIN 
                    usuarios u ON d.idusuario = u.idusuario
                WHERE 
                    d.iddevolucion = :iddevolucion
            ");

            $stmt->execute([':iddevolucion' => $iddevolucion]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener devolución: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista las devoluciones con filtros opcionales
     * @param string $fechaInicio Fecha de inicio para el filtro (YYYY-MM-DD)
     * @param string $fechaFin Fecha de fin para el filtro (YYYY-MM-DD)
     * @param string|null $documento Número de documento del paciente (opcional)
     * @return array Lista de devoluciones
     */
    public function listar($fechaInicio, $fechaFin, $documento = null, $metodo = null, $motivo = null)
    {
        try {
            // Log para depuración
            error_log("Modelo Devolucion->listar: Iniciando con filtros - fechaInicio: $fechaInicio, fechaFin: $fechaFin, documento: $documento, metodo: $metodo, motivo: $motivo");

            $sql = "
        SELECT 
            d.iddevolucion,
            d.numero_comprobante,
            d.fecha_devolucion,
            d.monto,
            d.motivo,
            d.metodo,
            c.idcita,
            c.fecha as fecha_cita,
            c.hora as hora_cita,
            v.nrodocumento as comprobante_venta,
            CONCAT(p.apellidos, ', ', p.nombres) as nombre_paciente,
            p.nrodoc as documento_paciente,
            d.idusuario,
            u.nomuser as usuario_nombre,
            -- Obtener nombre completo del usuario a través de las relaciones
            CONCAT(per_user.apellidos, ', ', per_user.nombres) as usuario_nombre_apellido,
            per_user.apellidos as usuario_apellidos,
            per_user.nombres as usuario_nombres
        FROM 
            devoluciones d
        LEFT JOIN 
            citas c ON d.idcita = c.idcita
        LEFT JOIN 
            ventas v ON d.idventa = v.idventa
        LEFT JOIN 
            personas p ON c.idpersona = p.idpersona
        -- JOINs para obtener información completa del usuario que autorizó
        LEFT JOIN 
            usuarios u ON d.idusuario = u.idusuario
        LEFT JOIN 
            contratos cont_user ON u.idcontrato = cont_user.idcontrato
        LEFT JOIN 
            colaboradores col_user ON cont_user.idcolaborador = col_user.idcolaborador
        LEFT JOIN 
            personas per_user ON col_user.idpersona = per_user.idpersona
        WHERE 
            DATE(d.fecha_devolucion) BETWEEN :fechaInicio AND :fechaFin
        ";

            $params = [
                ':fechaInicio' => $fechaInicio,
                ':fechaFin' => $fechaFin
            ];

            // Añadir filtro por documento si se proporciona
            if ($documento) {
                $sql .= " AND p.nrodoc LIKE :documento";
                $params[':documento'] = "%$documento%";
            }

            // Añadir filtro por método de devolución si se proporciona
            if ($metodo) {
                $sql .= " AND d.metodo = :metodo";
                $params[':metodo'] = $metodo;
            }

            // Añadir filtro por motivo de devolución si se proporciona
            if ($motivo) {
                $sql .= " AND d.motivo = :motivo";
                $params[':motivo'] = $motivo;
            }

            // Ordenar por fecha más reciente
            $sql .= " ORDER BY d.fecha_devolucion DESC";

            // Log para depuración
            error_log("SQL para listar devoluciones: " . $sql);
            error_log("Parámetros: " . json_encode($params));

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Log para depuración del resultado
            error_log("Devoluciones encontradas: " . count($resultados));

            return $resultados;
        } catch (Exception $e) {
            error_log("Error al listar devoluciones: " . $e->getMessage());
            error_log("Traza: " . $e->getTraceAsString());
            return [];
        }
    }
    /**
     * Verifica si una cita ya tiene una devolución asociada
     * @param int $idcita ID de la cita
     * @return bool True si ya existe una devolución, False en caso contrario
     */
    public function existeDevolucionParaCita($idcita)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM devoluciones
                WHERE idcita = :idcita
            ");

            $stmt->execute([':idcita' => $idcita]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado['total'] > 0;
        } catch (Exception $e) {
            error_log("Error al verificar devolución: " . $e->getMessage());
            return false;
        }
    }
}
