<?php /*RUTA: sistemaclinica/models/Horario.php*/ ?>
<?php

require_once 'Conexion.php';

class Horario
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    /**
     * Obtiene los horarios de un colaborador específico
     * @param int $idColaborador ID del colaborador
     * @return array Lista de horarios
     */
    public function obtenerHorariosPorColaborador($idColaborador)
    {
        try {
            // Verificar primero si existe la tabla horarios_atencion
            if ($this->verificarTablaHorariosAtencion()) {
                $pdo = $this->conexion->getConexion();
                $stmt = $pdo->prepare("CALL sp_obtener_horarios_por_colaborador(?)");
                $stmt->execute([$idColaborador]);

                $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();

                return $horarios;
            } else {
                // Usar un enfoque alternativo si no existe la tabla horarios_atencion
                return $this->obtenerHorariosAlternativos($idColaborador);
            }
        } catch (Exception $e) {
            // Registrar el error para depuración
            error_log("Error al obtener horarios: " . $e->getMessage());

            // Devolver un array vacío en caso de error
            return [];
        }
    }

    /**
     * Obtiene información de un horario específico
     * @param int $idHorario ID del horario
     * @return array Datos del horario
     */
    public function obtenerHorarioPorId($idHorario)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $stmt = $pdo->prepare("CALL sp_obtener_horario_por_id(?)");
            $stmt->execute([$idHorario]);

            $horario = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $horario;
        } catch (Exception $e) {
            die("Error al obtener horario: " . $e->getMessage());
        }
    }
    /**
     * Verifica si existe la tabla horarios_atencion en la base de datos
     * @return boolean True si existe la tabla, False en caso contrario
     */
    public function verificarTablaHorariosAtencion()
    {
        try {
            $pdo = $this->conexion->getConexion();
            $stmt = $pdo->query("SHOW TABLES LIKE 'horarios_atencion'");
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            die("Error al verificar tabla: " . $e->getMessage());
        }
    }

    /**
     * Registra un horario de atención para un colaborador
     * @param array $datos Datos del horario
     * @return array Resultado de la operación y mensaje
     */
    public function registrarHorario($datos)
    {
        // Verificar si existe la tabla horarios_atencion
        if ($this->verificarTablaHorariosAtencion()) {
            // Usar el procedimiento almacenado para horarios_atencion
            try {
                $pdo = $this->conexion->getConexion();
                $stmt = $pdo->prepare("CALL sp_registrar_horario(?, ?, ?, ?, @resultado, @mensaje)");

                $stmt->execute([
                    $datos['idcolaborador'],
                    $datos['dia'],
                    $datos['horainicio'],
                    $datos['horafin']
                ]);
                $stmt->closeCursor();

                $result = $pdo->query("SELECT @resultado AS resultado, @mensaje AS mensaje")->fetch(PDO::FETCH_ASSOC);
                return [
                    'status' => (bool) $result['resultado'],
                    'mensaje' => $result['mensaje']
                ];
            } catch (Exception $e) {
                die("Error al registrar horario: " . $e->getMessage());
            }
        } else {
            // Usar el método alternativo para atenciones/horarios
            return $this->registrarHorarioAlternativo($datos);
        }
    }
    /**
     * Registra un horario de forma simple
     * @param array $datos Datos del horario
     * @return array Resultado de la operación
     */
    public function registrarHorarioSimple($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Insertar horario
            $stmt = $pdo->prepare("
            INSERT INTO horarios (idatencion, horainicio, horafin)
            VALUES (?, ?, ?)
        ");
            $stmt->execute([
                $datos['idatencion'],
                $datos['horainicio'],
                $datos['horafin']
            ]);

            $idhorario = $pdo->lastInsertId();

            return [
                'status' => true,
                'mensaje' => 'Horario registrado correctamente',
                'idhorario' => $idhorario
            ];
        } catch (Exception $e) {
            error_log("Error al registrar horario: " . $e->getMessage());

            return [
                'status' => false,
                'mensaje' => 'Error al registrar horario: ' . $e->getMessage(),
                'idhorario' => null
            ];
        }
    }
    /**
     * Registra un horario usando las tablas atenciones y horarios
     * @param array $datos Datos del horario
     * @return array Resultado de la operación y mensaje
     */
    public function registrarHorarioAlternativo($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // 1. Verificar que exista el colaborador
            $stmt = $pdo->prepare("SELECT idcolaborador FROM colaboradores WHERE idcolaborador = ?");
            $stmt->execute([$datos['idcolaborador']]);
            $colaborador = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$colaborador) {
                return [
                    'status' => false,
                    'mensaje' => 'El colaborador no existe'
                ];
            }

            // 2. Obtener el contrato activo del colaborador
            $stmt = $pdo->prepare("
            SELECT idcontrato
            FROM contratos
            WHERE idcolaborador = ?
            AND (fechafin IS NULL OR fechafin >= CURDATE())
            ORDER BY fechainicio DESC
            LIMIT 1
        ");
            $stmt->execute([$datos['idcolaborador']]);
            $contrato = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$contrato) {
                // Si no hay contrato activo, verificar si hay algún contrato
                $stmt = $pdo->prepare("
                SELECT idcontrato
                FROM contratos
                WHERE idcolaborador = ?
                ORDER BY fechainicio DESC
                LIMIT 1
            ");
                $stmt->execute([$datos['idcolaborador']]);
                $contratoAlternativo = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$contratoAlternativo) {
                    return [
                        'status' => false,
                        'mensaje' => 'No se encontró ningún contrato para el colaborador'
                    ];
                }

                $idcontrato = $contratoAlternativo['idcontrato'];
            } else {
                $idcontrato = $contrato['idcontrato'];
            }

            // 3. NUEVO: Verificar si hay horarios que se crucen
            $stmt = $pdo->prepare("
            SELECT h.horainicio, h.horafin 
            FROM horarios h
            INNER JOIN atenciones a ON h.idatencion = a.idatencion
            WHERE a.idcontrato = ? AND a.diasemana = ?
        ");
            $stmt->execute([$idcontrato, $datos['dia']]);
            $horariosExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Validar que no haya cruces de horarios
            foreach ($horariosExistentes as $horario) {
                $inicioExistente = strtotime($horario['horainicio']);
                $finExistente = strtotime($horario['horafin']);
                $inicioNuevo = strtotime($datos['horainicio']);
                $finNuevo = strtotime($datos['horafin']);

                if (
                    ($inicioNuevo >= $inicioExistente && $inicioNuevo < $finExistente) || // Inicio nuevo dentro de existente
                    ($finNuevo > $inicioExistente && $finNuevo <= $finExistente) ||      // Fin nuevo dentro de existente
                    ($inicioNuevo <= $inicioExistente && $finNuevo >= $finExistente)     // Nuevo cubre al existente
                ) {
                    return [
                        'status' => false,
                        'mensaje' => 'El horario se cruza con otro horario existente para este día'
                    ];
                }
            }

            // 4. Verificar si hay una atención para ese día
            $stmt = $pdo->prepare("
            SELECT idatencion 
            FROM atenciones 
            WHERE idcontrato = ? AND diasemana = ?
        ");
            $stmt->execute([$idcontrato, $datos['dia']]);
            $atencionExistente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($atencionExistente) {
                // Usar la atención existente pero SIEMPRE crear un nuevo horario
                $idatencion = $atencionExistente['idatencion'];

                $stmt = $pdo->prepare("
                INSERT INTO horarios (idatencion, horainicio, horafin) 
                VALUES (?, ?, ?)
            ");
                $stmt->execute([$idatencion, $datos['horainicio'], $datos['horafin']]);
                $idhorario = $pdo->lastInsertId();
            } else {
                // Crear nueva atención y horario
                $stmt = $pdo->prepare("
                INSERT INTO atenciones (idcontrato, diasemana) 
                VALUES (?, ?)
            ");
                $stmt->execute([$idcontrato, $datos['dia']]);
                $idatencion = $pdo->lastInsertId();

                $stmt = $pdo->prepare("
                INSERT INTO horarios (idatencion, horainicio, horafin) 
                VALUES (?, ?, ?)
            ");
                $stmt->execute([$idatencion, $datos['horainicio'], $datos['horafin']]);
                $idhorario = $pdo->lastInsertId();
            }

            $pdo->commit();
            return [
                'status' => true,
                'mensaje' => 'Horario registrado correctamente',
                'idatencion' => $idatencion,
                'idhorario' => $idhorario
            ];
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("Error al registrar horario: " . $e->getMessage());
            return [
                'status' => false,
                'mensaje' => 'Error al registrar horario: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Obtiene los horarios usando un enfoque alternativo
     * @param int $idColaborador ID del colaborador
     * @return array Lista de horarios
     */
    private function obtenerHorariosAlternativos($idColaborador)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Consulta para obtener horarios de las tablas atenciones y horarios
            $stmt = $pdo->prepare("
            SELECT 
                a.diasemana AS dia,
                h.horainicio,
                h.horafin,
                COALESCE(h.intervalo, 30) AS intervalo
            FROM 
                atenciones a
            INNER JOIN 
                horarios h ON a.idatencion = h.idatencion
            INNER JOIN 
                contratos c ON a.idcontrato = c.idcontrato
            WHERE 
                c.idcolaborador = ?
        ");

            $stmt->execute([$idColaborador]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener horarios alternativos: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Obtiene los días y horarios de atención de un doctor
     * @param int $idColaborador ID del doctor
     * @return array Array asociativo con días de la semana y sus horarios
     */
    public function obtenerHorariosPorDoctor($idColaborador)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Obtener los días de atención del doctor
            $sql = "
            SELECT 
                a.diasemana,
                h.horainicio,
                h.horafin
            FROM 
                atenciones a
            INNER JOIN 
                horarios h ON a.idatencion = h.idatencion
            INNER JOIN 
                contratos c ON a.idcontrato = c.idcontrato
            WHERE 
                c.idcolaborador = ?
            ORDER BY 
                FIELD(a.diasemana, 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'),
                h.horainicio
        ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idColaborador]);

            $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Organizar los horarios por día
            $horariosPorDia = [];

            foreach ($horarios as $horario) {
                $dia = $horario['diasemana'];

                if (!isset($horariosPorDia[$dia])) {
                    $horariosPorDia[$dia] = [];
                }

                $horariosPorDia[$dia][] = [
                    'inicio' => $horario['horainicio'],
                    'fin' => $horario['horafin']
                ];
            }

            return $horariosPorDia;
        } catch (Exception $e) {
            error_log("Error al obtener horarios por doctor: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las horas disponibles para un doctor en una fecha específica
     * @param int $idColaborador ID del doctor
     * @param string $fecha Fecha en formato Y-m-d
     * @return array Lista de horarios disponibles
     */
    public function obtenerHorasDisponiblesPorFecha($idColaborador, $fecha)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Obtener el nombre del día de la semana de la fecha
            $diaSemana = date('w', strtotime($fecha));
            $diasSemana = ['DOMINGO', 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];
            $nombreDia = $diasSemana[$diaSemana];

            // Obtener los horarios del doctor para ese día
            $sql = "
            SELECT 
                h.horainicio,
                h.horafin
            FROM 
                atenciones a
            INNER JOIN 
                horarios h ON a.idatencion = h.idatencion
            INNER JOIN 
                contratos c ON a.idcontrato = c.idcontrato
            WHERE 
                c.idcolaborador = ? AND 
                a.diasemana = ?
            ORDER BY 
                h.horainicio
        ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idColaborador, $nombreDia]);

            $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($horarios)) {
                return []; // No hay horarios definidos para ese día
            }

            // Generar horas disponibles en intervalos de 30 minutos
            $horasDisponibles = [];

            foreach ($horarios as $horario) {
                $horaInicio = strtotime($horario['horainicio']);
                $horaFin = strtotime($horario['horafin']);

                // Intervalo de 30 minutos
                $intervalo = 30 * 60;

                for ($hora = $horaInicio; $hora < $horaFin; $hora += $intervalo) {
                    $horaStr = date('H:i:s', $hora);

                    // Verificar si ya hay citas programadas en esta hora
                    $sqlCitas = "
                    SELECT COUNT(*) AS total
                    FROM consultas
                    WHERE 
                        fecha = ? AND 
                        horaprogramada = ? AND 
                        idhorario IN (
                            SELECT h.idhorario
                            FROM horarios h
                            INNER JOIN atenciones a ON h.idatencion = a.idatencion
                            INNER JOIN contratos c ON a.idcontrato = c.idcontrato
                            WHERE c.idcolaborador = ?
                        )
                ";

                    $stmtCitas = $pdo->prepare($sqlCitas);
                    $stmtCitas->execute([$fecha, $horaStr, $idColaborador]);
                    $resultado = $stmtCitas->fetch(PDO::FETCH_ASSOC);

                    $disponible = ($resultado['total'] == 0);

                    $horasDisponibles[] = [
                        'hora' => $horaStr,
                        'disponible' => $disponible
                    ];
                }
            }

            return $horasDisponibles;
        } catch (Exception $e) {
            error_log("Error al obtener horas disponibles: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Actualiza un horario existente
     * @param array $datos Datos actualizados del horario
     * @return array Resultado de la operación
     */
    public function actualizarHorario($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // Primero obtenemos datos del horario actual para conocer la atención y el día
            $stmt = $pdo->prepare("
            SELECT h.idhorario, a.idatencion, a.diasemana, a.idcontrato
            FROM horarios h
            INNER JOIN atenciones a ON h.idatencion = a.idatencion
            WHERE h.idhorario = ?
        ");
            $stmt->execute([$datos['idhorario']]);
            $horarioActual = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$horarioActual) {
                return [
                    'status' => false,
                    'mensaje' => 'No se encontró el horario a actualizar'
                ];
            }

            // Determinar qué día vamos a usar (el actual o el nuevo)
            $diaSemana = !empty($datos['diasemana']) ? $datos['diasemana'] : $horarioActual['diasemana'];
            $idContrato = $horarioActual['idcontrato'];
            $idAtencion = $horarioActual['idatencion'];

            // Verificar si hay horarios que se crucen, excluyendo el horario actual
            $stmt = $pdo->prepare("
            SELECT h.horainicio, h.horafin 
            FROM horarios h
            INNER JOIN atenciones a ON h.idatencion = a.idatencion
            WHERE a.idcontrato = ? 
              AND a.diasemana = ?
              AND h.idhorario != ?
        ");
            $stmt->execute([$idContrato, $diaSemana, $datos['idhorario']]);
            $horariosExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Validar que no haya cruces de horarios
            $inicioNuevo = strtotime($datos['horainicio']);
            $finNuevo = strtotime($datos['horafin']);

            foreach ($horariosExistentes as $horario) {
                $inicioExistente = strtotime($horario['horainicio']);
                $finExistente = strtotime($horario['horafin']);

                if (
                    ($inicioNuevo >= $inicioExistente && $inicioNuevo < $finExistente) || // Inicio nuevo dentro de existente
                    ($finNuevo > $inicioExistente && $finNuevo <= $finExistente) ||      // Fin nuevo dentro de existente
                    ($inicioNuevo <= $inicioExistente && $finNuevo >= $finExistente)     // Nuevo cubre al existente
                ) {
                    return [
                        'status' => false,
                        'mensaje' => 'El horario se cruza con otro horario existente para este día'
                    ];
                }
            }

            // Si no hay cruces, actualizar horario
            $stmt = $pdo->prepare("
            UPDATE horarios 
            SET horainicio = ?, horafin = ? 
            WHERE idhorario = ?
        ");
            $stmt->execute([
                $datos['horainicio'],
                $datos['horafin'],
                $datos['idhorario']
            ]);

            // Si se cambió el día, actualizar atención
            if (!empty($datos['diasemana']) && $datos['idatencion'] > 0) {
                $stmt = $pdo->prepare("
                UPDATE atenciones 
                SET diasemana = ? 
                WHERE idatencion = ?
            ");
                $stmt->execute([
                    $datos['diasemana'],
                    $datos['idatencion']
                ]);
            }

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Horario actualizado correctamente'
            ];
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("Error al actualizar horario: " . $e->getMessage());

            return [
                'status' => false,
                'mensaje' => 'Error al actualizar horario: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Elimina un horario
     * @param int $idHorario ID del horario
     * @return array Resultado de la operación
     */
    public function eliminarHorario($idHorario)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // Primero obtener el ID de atención asociado
            $stmt = $pdo->prepare("
            SELECT idatencion 
            FROM horarios 
            WHERE idhorario = ?
        ");
            $stmt->execute([$idHorario]);
            $horario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$horario) {
                return [
                    'status' => false,
                    'mensaje' => 'El horario no existe'
                ];
            }

            $idAtencion = $horario['idatencion'];

            // Eliminar el horario
            $stmt = $pdo->prepare("DELETE FROM horarios WHERE idhorario = ?");
            $stmt->execute([$idHorario]);

            // Verificar si quedan otros horarios para esta atención
            $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM horarios 
            WHERE idatencion = ?
        ");
            $stmt->execute([$idAtencion]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si no quedan más horarios, eliminar también la atención
            if ($resultado['total'] == 0) {
                $stmt = $pdo->prepare("DELETE FROM atenciones WHERE idatencion = ?");
                $stmt->execute([$idAtencion]);
            }

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Horario eliminado correctamente'
            ];
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("Error al eliminar horario: " . $e->getMessage());

            return [
                'status' => false,
                'mensaje' => 'Error al eliminar horario: ' . $e->getMessage()
            ];
        }
    }
}
