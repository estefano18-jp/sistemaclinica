<?php
require_once 'Conexion.php';

class ArqueoCaja
{
    private $pdo;

    public function __CONSTRUCT()
    {
        $this->pdo = (new Conexion())->getConexion();
    }

    /**
     * Verifica si hay un arqueo abierto (sin importar la fecha)
     * @return array Datos del arqueo abierto o estado false si no hay
     */
    public function verificarEstado()
    {
        try {
            // CORREGIDO: Buscar cualquier arqueo abierto, sin filtrar por fecha
            $stmt = $this->pdo->prepare("
            SELECT 
                a.idarqueo,
                a.fecha_apertura,
                a.hora_apertura,
                a.monto_inicial,
                a.saldo_anterior,
                a.saldo_a_dejar,
                a.estado
            FROM 
                arqueocaja a
            WHERE 
                a.estado = 'ABIERTO'
            ORDER BY 
                a.idarqueo DESC
            LIMIT 1
        ");
            $stmt->execute(); // Sin parámetros de fecha
            $arqueoCaja = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($arqueoCaja) {
                return [
                    'status' => true,
                    'estado' => $arqueoCaja['estado'],
                    'idarqueo' => $arqueoCaja['idarqueo'],
                    'monto_inicial' => $arqueoCaja['monto_inicial'],
                    'saldo_anterior' => $arqueoCaja['saldo_anterior'],
                    'saldo_a_dejar' => $arqueoCaja['saldo_a_dejar'],
                    'hora_apertura' => $arqueoCaja['hora_apertura'],
                    'fecha_apertura' => $arqueoCaja['fecha_apertura']
                ];
            } else {
                return [
                    'status' => false,
                    'mensaje' => 'No hay un arqueo abierto'
                ];
            }
        } catch (Exception $e) {
            error_log("Error al verificar estado de arqueo: " . $e->getMessage());
            return [
                'status' => false,
                'mensaje' => 'Error al verificar estado de arqueo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * FUNCIÓN CORREGIDA: Obtiene la hora del último cierre de caja del día actual
     * @return string|null Hora del último cierre o null si no hay
     */
    public function obtenerHoraUltimoCierre()
    {
        try {
            $fechaActual = date('Y-m-d');

            $stmt = $this->pdo->prepare("
                SELECT 
                    hora_cierre
                FROM 
                    arqueocaja 
                WHERE 
                    fecha_apertura = ? 
                    AND estado = 'CERRADO'
                    AND hora_cierre IS NOT NULL
                ORDER BY 
                    hora_cierre DESC
                LIMIT 1
            ");
            $stmt->execute([$fechaActual]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                error_log("Hora del último cierre encontrada: " . $resultado['hora_cierre']);
                return $resultado['hora_cierre'];
            } else {
                error_log("No se encontró hora de último cierre para la fecha: " . $fechaActual);
                return null;
            }
        } catch (Exception $e) {
            error_log("Error al obtener hora del último cierre: " . $e->getMessage());
            return null;
        }
    }

    /**
     * FUNCIÓN TOTALMENTE CORREGIDA: Obtiene el saldo final del último arqueo cerrado del día actual
     * @return array Saldo final del último arqueo o 0 si no hay
     */
    public function obtenerUltimoSaldo()
    {
        try {
            $fechaActual = date('Y-m-d');

            error_log("Obteniendo último saldo para la fecha: " . $fechaActual);

            // CORREGIDO: Obtener el último arqueo cerrado del día actual
            $stmt = $this->pdo->prepare("
                SELECT 
                    a.saldo_a_dejar AS saldo_final,
                    a.hora_cierre,
                    a.idarqueo
                FROM 
                    arqueocaja a
                WHERE 
                    a.estado = 'CERRADO'
                    AND a.fecha_apertura = ?
                    AND a.hora_cierre IS NOT NULL
                ORDER BY 
                    a.hora_cierre DESC, a.idarqueo DESC
                LIMIT 1
            ");
            $stmt->execute([$fechaActual]);
            $resultadoHoy = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultadoHoy) {
                error_log("Saldo encontrado del último cierre del día actual: " . $resultadoHoy['saldo_final'] . " (Arqueo ID: " . $resultadoHoy['idarqueo'] . ")");
                return [
                    'status' => true,
                    'saldo_final' => floatval($resultadoHoy['saldo_final']),
                    'es_del_dia_actual' => true,
                    'hora_cierre' => $resultadoHoy['hora_cierre'],
                    'idarqueo' => $resultadoHoy['idarqueo']
                ];
            } else {
                // Si no hay saldo de hoy, obtener el último saldo general (día anterior)
                $stmt = $this->pdo->prepare("
                    SELECT 
                        a.saldo_a_dejar AS saldo_final,
                        a.fecha_apertura,
                        a.hora_cierre,
                        a.idarqueo
                    FROM 
                        arqueocaja a
                    WHERE 
                        a.estado = 'CERRADO'
                        AND a.hora_cierre IS NOT NULL
                    ORDER BY 
                        a.fecha_apertura DESC, a.hora_cierre DESC, a.idarqueo DESC
                    LIMIT 1
                ");
                $stmt->execute();
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($resultado) {
                    error_log("Saldo encontrado de día anterior: " . $resultado['saldo_final'] . " (Fecha: " . $resultado['fecha_apertura'] . ")");
                    return [
                        'status' => true,
                        'saldo_final' => floatval($resultado['saldo_final']),
                        'fecha_ultimo_arqueo' => $resultado['fecha_apertura'],
                        'es_del_dia_actual' => false,
                        'hora_cierre' => $resultado['hora_cierre'],
                        'idarqueo' => $resultado['idarqueo']
                    ];
                } else {
                    error_log("No se encontró ningún saldo anterior, usando 0");
                    return [
                        'status' => true,
                        'saldo_final' => 0,
                        'es_del_dia_actual' => false,
                        'hora_cierre' => null,
                        'idarqueo' => null
                    ];
                }
            }
        } catch (Exception $e) {
            error_log("Error al obtener último saldo: " . $e->getMessage());
            return [
                'status' => false,
                'mensaje' => 'Error al obtener último saldo: ' . $e->getMessage(),
                'saldo_final' => 0,
                'es_del_dia_actual' => false,
                'hora_cierre' => null,
                'idarqueo' => null
            ];
        }
    }

    /**
     * Cuenta el número de arqueos realizados en un día específico
     * @param string $fecha Fecha en formato Y-m-d
     * @return int Número de arqueos
     */
    public function contarArqueosPorDia($fecha)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM arqueocaja
                WHERE fecha_apertura = ?
            ");
            $stmt->execute([$fecha]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return intval($resultado['total']);
        } catch (Exception $e) {
            error_log("Error al contar arqueos por día: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * FUNCIÓN CORREGIDA: Abre un nuevo arqueo de caja
     * @param array $datos Datos del arqueo
     * @return array Resultado de la operación
     */
    public function abrirCaja($datos)
    {
        try {
            // Verificar si ya hay un arqueo abierto (cualquier fecha)
            $verificacion = $this->verificarEstado();
            if ($verificacion['status']) {
                return [
                    'status' => false,
                    'mensaje' => 'Ya hay un arqueo abierto desde ' . $verificacion['fecha_apertura'] . ' a las ' . $verificacion['hora_apertura']
                ];
            }

            $this->pdo->beginTransaction();

            // CORREGIDO: Obtener el saldo del último cierre para usarlo como monto inicial
            $ultimoSaldo = $this->obtenerUltimoSaldo();
            $montoInicial = $ultimoSaldo['saldo_final'];

            error_log("Abriendo caja con monto inicial (último saldo): " . $montoInicial);

            $stmt = $this->pdo->prepare("
            INSERT INTO arqueocaja (
                fecha_apertura,
                hora_apertura,
                monto_inicial,
                saldo_anterior,
                saldo_a_dejar,
                estado,
                observaciones,
                idusuario
            ) VALUES (
                :fecha_apertura,
                :hora_apertura,
                :monto_inicial,
                :saldo_anterior,
                :saldo_a_dejar,
                'ABIERTO',
                :observaciones,
                :idusuario
            )
        ");

            $stmt->execute([
                'fecha_apertura' => $datos['fecha_apertura'],
                'hora_apertura' => $datos['hora_apertura'],
                'monto_inicial' => $montoInicial, // CORREGIDO: Usar el último saldo como monto inicial
                'saldo_anterior' => $montoInicial, // CORREGIDO: También como saldo anterior
                'saldo_a_dejar' => 0, // Por defecto 0
                'observaciones' => $datos['observaciones'],
                'idusuario' => $_SESSION['usuario']['idusuario']
            ]);

            $idarqueo = $this->pdo->lastInsertId();

            $this->pdo->commit();

            error_log("Arqueo abierto correctamente con ID: " . $idarqueo . " y monto inicial: " . $montoInicial);

            return [
                'status' => true,
                'mensaje' => 'Arqueo abierto correctamente',
                'idarqueo' => $idarqueo,
                'monto_inicial' => $montoInicial,
                'saldo_anterior' => $montoInicial
            ];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error al abrir caja: " . $e->getMessage());
            return [
                'status' => false,
                'mensaje' => 'Error al abrir caja: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza un arqueo existente
     * @param array $datos Datos del arqueo
     * @return array Resultado de la operación
     */
    public function actualizarArqueo($datos)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE arqueocaja 
                SET 
                    saldo_a_dejar = :saldo_a_dejar,
                    observaciones = :observaciones
                WHERE 
                    idarqueo = :idarqueo
                    AND estado = 'ABIERTO'
            ");

            $stmt->execute([
                'saldo_a_dejar' => $datos['saldo_a_dejar'],
                'observaciones' => $datos['observaciones'],
                'idarqueo' => $datos['idarqueo']
            ]);

            if ($stmt->rowCount() > 0) {
                return [
                    'status' => true,
                    'mensaje' => 'Arqueo actualizado correctamente'
                ];
            } else {
                return [
                    'status' => false,
                    'mensaje' => 'No se pudo actualizar el arqueo o no existe'
                ];
            }
        } catch (Exception $e) {
            error_log("Error al actualizar arqueo: " . $e->getMessage());
            return [
                'status' => false,
                'mensaje' => 'Error al actualizar arqueo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * FUNCIÓN CORREGIDA: Cierra un arqueo existente
     * @param array $datos Datos del cierre
     * @return array Resultado de la operación
     */
    public function cerrarCaja($datos)
    {
        try {
            $this->pdo->beginTransaction();

            // CORREGIDO: Incluir saldo_a_dejar en la actualización
            $stmt = $this->pdo->prepare("
                UPDATE arqueocaja 
                SET 
                    fecha_cierre = :fecha_cierre,
                    hora_cierre = :hora_cierre,
                    saldo_real = :saldo_real,
                    saldo_a_dejar = :saldo_a_dejar,
                    diferencia = :diferencia,
                    estado = 'CERRADO',
                    observaciones = :observaciones
                WHERE 
                    idarqueo = :idarqueo
                    AND estado = 'ABIERTO'
            ");

            $resultado = $stmt->execute([
                'fecha_cierre' => $datos['fecha_cierre'],
                'hora_cierre' => $datos['hora_cierre'],
                'saldo_real' => $datos['saldo_real'],
                'saldo_a_dejar' => $datos['saldo_a_dejar'],
                'diferencia' => $datos['diferencia'],
                'observaciones' => $datos['observaciones'],
                'idarqueo' => $datos['idarqueo']
            ]);

            error_log("Resultado de ejecución SQL: " . ($resultado ? 'true' : 'false'));
            error_log("Filas afectadas: " . $stmt->rowCount());

            if ($stmt->rowCount() > 0) {
                $this->pdo->commit();
                error_log("Arqueo cerrado exitosamente con saldo_a_dejar: " . $datos['saldo_a_dejar']);
                return [
                    'status' => true,
                    'mensaje' => 'Arqueo cerrado correctamente',
                    'idarqueo' => $datos['idarqueo'],
                    'hora_cierre' => $datos['hora_cierre'],
                    'saldo_a_dejar' => $datos['saldo_a_dejar']
                ];
            } else {
                $this->pdo->rollBack();
                error_log("No se encontró el arqueo a cerrar o ya estaba cerrado");
                return [
                    'status' => false,
                    'mensaje' => 'No se pudo cerrar el arqueo o no existe'
                ];
            }
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error al cerrar caja: " . $e->getMessage());
            error_log("Datos recibidos: " . json_encode($datos));
            return [
                'status' => false,
                'mensaje' => 'Error al cerrar caja: ' . $e->getMessage()
            ];
        }
    }

    /**
     * FUNCIÓN CORREGIDA: Lista los ingresos por reservaciones para una fecha y periodo de tiempo específico
     * @param string $fecha Fecha a consultar
     * @param int|null $idarqueo ID del arqueo (opcional)
     * @param string|null $horaInicio Hora de inicio (opcional, formato HH:MM:SS)
     * @return array Lista de ingresos
     */
    public function listarIngresosReservaciones($fecha, $idarqueo = null, $horaInicio = null)
    {
        try {
            error_log("Listando ingresos - Fecha: $fecha, Arqueo ID: $idarqueo, Hora inicio: $horaInicio");

            // CORREGIDO: Si tenemos el idarqueo, usar SIEMPRE la hora de apertura de ESE arqueo específico
            if ($idarqueo) {
                $stmt = $this->pdo->prepare("
                    SELECT fecha_apertura, hora_apertura
                    FROM arqueocaja
                    WHERE idarqueo = ?
                ");
                $stmt->execute([$idarqueo]);
                $arqueo = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($arqueo) {
                    $fecha = $arqueo['fecha_apertura'];
                    // CORREGIDO: SIEMPRE usar la hora de apertura del arqueo específico
                    // Esto garantiza que solo se muestren movimientos de ESTA caja
                    $horaInicio = $arqueo['hora_apertura'];
                    error_log("CORREGIDO: Usando hora de apertura del arqueo específico: $horaInicio");
                }
            } else {
                // Solo si NO hay arqueo específico, usar la hora proporcionada o la del último cierre
                if (!$horaInicio) {
                    $horaInicio = $this->obtenerHoraUltimoCierre();
                    error_log("No hay arqueo específico, usando hora del último cierre: $horaInicio");
                }
            }

            // Consulta base para ingresos
            $sql = "
                SELECT 
                    v.tipopago,
                    SUM(dv.precio) AS total
                FROM 
                    ventas v
                JOIN 
                    detalleventas dv ON v.idventa = dv.idventa
                WHERE 
                    DATE(v.fechaemision) = ?
            ";

            $params = [$fecha];

            // CORREGIDO: Solo filtrar por hora si hay una hora de inicio definida
            if ($horaInicio) {
                error_log("Filtrando ingresos desde la hora: $horaInicio");
                $sql .= " AND TIME(v.fechaemision) >= ?";  // CORREGIDO: Usar >= para incluir movimientos desde la hora exacta
                $params[] = $horaInicio;
            } else {
                error_log("No hay hora de filtro, obteniendo todos los ingresos del día");
            }

            // Agrupar por tipo de pago
            $sql .= " GROUP BY v.tipopago";

            error_log("SQL de ingresos: " . $sql);
            error_log("Parámetros: " . json_encode($params));

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Registrar para depuración
            error_log("Ingresos encontrados para fecha $fecha desde hora $horaInicio: " . json_encode($ingresos));

            return [
                'status' => true,
                'ingresos' => $ingresos
            ];
        } catch (Exception $e) {
            error_log("Error al listar ingresos por reservaciones: " . $e->getMessage());
            return [
                'status' => false,
                'mensaje' => 'Error al listar ingresos por reservaciones: ' . $e->getMessage(),
                'ingresos' => []
            ];
        }
    }

    /**
     * FUNCIÓN CORREGIDA: Lista los egresos por devoluciones para una fecha y periodo de tiempo específico
     * @param string $fecha Fecha a consultar
     * @param int|null $idarqueo ID del arqueo (opcional)
     * @param string|null $horaInicio Hora de inicio (opcional, formato HH:MM:SS)
     * @return array Lista de egresos
     */
    public function listarEgresosDevoluciones($fecha, $idarqueo = null, $horaInicio = null)
    {
        try {
            error_log("Listando egresos - Fecha: $fecha, Arqueo ID: $idarqueo, Hora inicio: $horaInicio");

            // CORREGIDO: Si tenemos el idarqueo, usar SIEMPRE la hora de apertura de ESE arqueo específico
            if ($idarqueo) {
                $stmt = $this->pdo->prepare("
                SELECT fecha_apertura, hora_apertura
                FROM arqueocaja
                WHERE idarqueo = ?
            ");
                $stmt->execute([$idarqueo]);
                $arqueo = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($arqueo) {
                    $fecha = $arqueo['fecha_apertura'];
                    // CORREGIDO: SIEMPRE usar la hora de apertura del arqueo específico
                    // Esto garantiza que solo se muestren movimientos de ESTA caja
                    $horaInicio = $arqueo['hora_apertura'];
                    error_log("CORREGIDO: Usando hora de apertura del arqueo específico: $horaInicio");
                }
            } else {
                // Solo si NO hay arqueo específico, usar la hora proporcionada o la del último cierre
                if (!$horaInicio) {
                    $horaInicio = $this->obtenerHoraUltimoCierre();
                    error_log("No hay arqueo específico, usando hora del último cierre: $horaInicio");
                }
            }

            // Consulta base para egresos
            $sql = "
            SELECT 
                metodo AS tipopago,
                SUM(monto) AS total
            FROM 
                devoluciones
            WHERE 
                DATE(fecha_devolucion) = ?
        ";

            $params = [$fecha];

            // CORREGIDO: Solo filtrar por hora si hay una hora de inicio definida
            if ($horaInicio) {
                error_log("Filtrando egresos desde la hora: $horaInicio");
                $sql .= " AND TIME(fecha_devolucion) >= ?";  // CORREGIDO: Usar >= para incluir movimientos desde la hora exacta
                $params[] = $horaInicio;
            } else {
                error_log("No hay hora de filtro, obteniendo todos los egresos del día");
            }

            // Agrupar por método
            $sql .= " GROUP BY metodo";

            error_log("SQL de egresos: " . $sql);
            error_log("Parámetros: " . json_encode($params));

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $egresos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Registrar para depuración
            error_log("Egresos encontrados para fecha $fecha desde hora $horaInicio: " . json_encode($egresos));

            return [
                'status' => true,
                'egresos' => $egresos
            ];
        } catch (Exception $e) {
            error_log("Error al listar egresos por devoluciones: " . $e->getMessage());
            return [
                'status' => false,
                'mensaje' => 'Error al listar egresos por devoluciones: ' . $e->getMessage(),
                'egresos' => []
            ];
        }
    }

    /**
     * Obtiene datos para el PDF de arqueo
     * @param int $idarqueo ID del arqueo
     * @return array Datos del arqueo
     */
    public function obtenerDatosArqueo($idarqueo)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    a.*,
                    u.nomuser,
                    CONCAT(p.nombres, ' ', p.apellidos) AS nombre_usuario
                FROM 
                    arqueocaja a
                JOIN 
                    usuarios u ON a.idusuario = u.idusuario
                LEFT JOIN 
                    contratos c ON u.idcontrato = c.idcontrato
                LEFT JOIN 
                    colaboradores col ON c.idcolaborador = col.idcolaborador
                LEFT JOIN 
                    personas p ON col.idpersona = p.idpersona
                WHERE 
                    a.idarqueo = ?
            ");
            $stmt->execute([$idarqueo]);
            $arqueo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$arqueo) {
                return [
                    'status' => false,
                    'mensaje' => 'Arqueo no encontrado'
                ];
            }

            // Obtener ingresos y egresos desde la hora de apertura de este arqueo
            $ingresos = $this->listarIngresosReservaciones($arqueo['fecha_apertura'], $idarqueo, $arqueo['hora_apertura']);
            $egresos = $this->listarEgresosDevoluciones($arqueo['fecha_apertura'], $idarqueo, $arqueo['hora_apertura']);

            return [
                'status' => true,
                'data' => $arqueo,
                'movimientos' => array_merge(
                    array_map(function($ingreso) {
                        return [
                            'tipo' => 'INGRESO',
                            'forma_pago' => $ingreso['tipopago'],
                            'monto' => $ingreso['total']
                        ];
                    }, $ingresos['ingresos'] ?? []),
                    array_map(function($egreso) {
                        return [
                            'tipo' => 'EGRESO',
                            'forma_pago' => $egreso['tipopago'],
                            'monto' => $egreso['total']
                        ];
                    }, $egresos['egresos'] ?? [])
                )
            ];
        } catch (Exception $e) {
            error_log("Error al obtener datos de arqueo: " . $e->getMessage());
            return [
                'status' => false,
                'mensaje' => 'Error al obtener datos de arqueo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * NUEVA FUNCIÓN: Lista todos los arqueos con filtros opcionales
     * @param string|null $fechaDesde Fecha desde (formato Y-m-d)
     * @param string|null $fechaHasta Fecha hasta (formato Y-m-d)
     * @param string|null $estado Estado del arqueo (ABIERTO, CERRADO)
     * @param int|null $idusuario ID del usuario
     * @return array Lista de arqueos con resumen
     */
    public function listarArqueos($fechaDesde = null, $fechaHasta = null, $estado = null, $idusuario = null)
    {
        try {
            // Construir la consulta base
            $sql = "
                SELECT 
                    a.idarqueo,
                    a.fecha_apertura,
                    a.hora_apertura,
                    a.fecha_cierre,
                    a.hora_cierre,
                    a.monto_inicial,
                    a.saldo_anterior,
                    a.saldo_real,
                    a.saldo_a_dejar,
                    a.diferencia,
                    a.estado,
                    a.observaciones,
                    a.idusuario,
                    u.nomuser,
                    CONCAT(COALESCE(p.nombres, ''), ' ', COALESCE(p.apellidos, '')) AS usuario_nombre
                FROM 
                    arqueocaja a
                JOIN 
                    usuarios u ON a.idusuario = u.idusuario
                LEFT JOIN 
                    contratos c ON u.idcontrato = c.idcontrato
                LEFT JOIN 
                    colaboradores col ON c.idcolaborador = col.idcolaborador
                LEFT JOIN 
                    personas p ON col.idpersona = p.idpersona
                WHERE 1=1
            ";

            $params = [];

            // Agregar filtros según los parámetros
            if ($fechaDesde) {
                $sql .= " AND a.fecha_apertura >= ?";
                $params[] = $fechaDesde;
            }

            if ($fechaHasta) {
                $sql .= " AND a.fecha_apertura <= ?";
                $params[] = $fechaHasta;
            }

            if ($estado) {
                $sql .= " AND a.estado = ?";
                $params[] = $estado;
            }

            if ($idusuario) {
                $sql .= " AND a.idusuario = ?";
                $params[] = $idusuario;
            }

            // Ordenar por fecha y hora de apertura descendente
            $sql .= " ORDER BY a.fecha_apertura DESC, a.hora_apertura DESC";

            error_log("SQL para listar arqueos: " . $sql);
            error_log("Parámetros: " . json_encode($params));

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $arqueos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calcular resumen
            $resumen = $this->calcularResumenArqueos($arqueos);

            return [
                'status' => true,
                'data' => $arqueos,
                'resumen' => $resumen
            ];
        } catch (Exception $e) {
            error_log("Error al listar arqueos: " . $e->getMessage());
            return [
                'status' => false,
                'mensaje' => 'Error al listar arqueos: ' . $e->getMessage(),
                'data' => [],
                'resumen' => [
                    'total_arqueos' => 0,
                    'total_cerrados' => 0,
                    'total_abiertos' => 0,
                    'total_saldo' => 0
                ]
            ];
        }
    }

    /**
     * NUEVA FUNCIÓN: Calcula el resumen de arqueos
     * @param array $arqueos Lista de arqueos
     * @return array Resumen calculado
     */
    private function calcularResumenArqueos($arqueos)
    {
        $totalArqueos = count($arqueos);
        $totalCerrados = 0;
        $totalAbiertos = 0;
        $totalSaldo = 0;

        foreach ($arqueos as $arqueo) {
            if ($arqueo['estado'] === 'CERRADO') {
                $totalCerrados++;
                $totalSaldo += floatval($arqueo['saldo_a_dejar']);
            } else {
                $totalAbiertos++;
            }
        }

        return [
            'total_arqueos' => $totalArqueos,
            'total_cerrados' => $totalCerrados,
            'total_abiertos' => $totalAbiertos,
            'total_saldo' => $totalSaldo
        ];
    }

    /**
     * NUEVA FUNCIÓN: Actualiza solo las observaciones de un arqueo
     * @param array $datos Datos con idarqueo y observaciones
     * @return array Resultado de la operación
     */
    public function actualizarObservaciones($datos)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE arqueocaja 
                SET observaciones = :observaciones
                WHERE idarqueo = :idarqueo
            ");

            $stmt->execute([
                'observaciones' => $datos['observaciones'],
                'idarqueo' => $datos['idarqueo']
            ]);

            if ($stmt->rowCount() > 0) {
                return [
                    'status' => true,
                    'mensaje' => 'Observaciones actualizadas correctamente'
                ];
            } else {
                return [
                    'status' => false,
                    'mensaje' => 'No se pudo actualizar las observaciones o el arqueo no existe'
                ];
            }
        } catch (Exception $e) {
            error_log("Error al actualizar observaciones: " . $e->getMessage());
            return [
                'status' => false,
                'mensaje' => 'Error al actualizar observaciones: ' . $e->getMessage()
            ];
        }
    }
}