<?php /*RUTA: sistemaclinica/models/Cita.php*/ ?>
<?php
require_once 'Conexion.php';

class Cita
{
    private $pdo;

    public function __CONSTRUCT()
    {
        $this->pdo = (new Conexion())->getConexion();
    }

    /**
     * Registra una nueva cita médica
     * @param array $datos Datos completos de la cita
     * @return array Resultado de la operación
     */
    public function registrarCita($datos)
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Primero obtenemos el horario correcto para la fecha y hora
            $diaSemana = date('w', strtotime($datos['fecha']));
            $diasSemana = ['DOMINGO', 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];
            $nombreDia = $diasSemana[$diaSemana];

            $stmt = $this->pdo->prepare("
            SELECT h.idhorario 
            FROM horarios h
            INNER JOIN atenciones a ON h.idatencion = a.idatencion
            INNER JOIN contratos c ON a.idcontrato = c.idcontrato
            WHERE c.idcolaborador = ? AND a.diasemana = ?
            AND ? BETWEEN h.horainicio AND h.horafin
        ");
            $stmt->execute([$datos['iddoctor'], $nombreDia, $datos['hora']]);
            $horario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$horario) {
                return [
                    'status' => false,
                    'mensaje' => 'No se encontró un horario válido para la cita'
                ];
            }

            // 2. Registrar la cita en la tabla citas
            $stmt = $this->pdo->prepare("
            INSERT INTO citas (fecha, hora, estado, observaciones, idpersona)
            SELECT ?, ?, 'PROGRAMADA', ?, p.idpersona 
            FROM pacientes pac
            INNER JOIN personas p ON pac.idpersona = p.idpersona
            WHERE pac.idpaciente = ?
        ");
            $stmt->execute([
                $datos['fecha'],
                $datos['hora'],
                // IMPORTANTE: Se establece "Control" como valor predeterminado
                !empty($datos['observaciones']) ? $datos['observaciones'] : 'Control',
                $datos['idpaciente']
            ]);
            $idcita = $this->pdo->lastInsertId();

            // 3. Registrar la consulta asociada a la cita
            $stmt = $this->pdo->prepare("
            INSERT INTO consultas (
                fecha, idhorario, horaprogramada, 
                idpaciente, condicionpaciente
            ) VALUES (?, ?, ?, ?, ?)
        ");
            $stmt->execute([
                $datos['fecha'],
                $horario['idhorario'],
                $datos['hora'],
                $datos['idpaciente'],
                $datos['condicionpaciente'] ?? 'NUEVO'
            ]);
            $idconsulta = $this->pdo->lastInsertId();

            $this->pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Cita registrada correctamente',
                'idcita' => $idcita,
                'idconsulta' => $idconsulta
            ];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error al registrar cita: " . $e->getMessage());
            return [
                'status' => false,
                'mensaje' => 'Error al registrar la cita: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lista citas con filtros opcionales
     */
    public function listarCitas($fechaInicio, $fechaFin, $iddoctor = null, $idespecialidad = null, $estado = null, $paciente = null, $nrodoc = null)
    {
        try {
            // Consulta simplificada con mejor manejo de JOIN y menos ambigüedad
            $sql = "
        SELECT 
            c.idcita,
            con.idconsulta,
            con.fecha,
            con.horaprogramada AS hora,
            COALESCE(c.estado, 'PROGRAMADA') AS estado,
            p_pac.nombres AS paciente_nombre,
            p_pac.apellidos AS paciente_apellido,
            p_pac.tipodoc AS documento_tipo,
            p_pac.nrodoc AS documento_numero,
            col.idcolaborador AS iddoctor,
            p_doc.nombres AS doctor_nombre,
            p_doc.apellidos AS doctor_apellido,
            e.idespecialidad,
            e.especialidad,
            COALESCE(dv.precio, 0) AS monto_pagado
        FROM 
            consultas con
        INNER JOIN 
            pacientes pac ON con.idpaciente = pac.idpaciente
        INNER JOIN 
            personas p_pac ON pac.idpersona = p_pac.idpersona
        INNER JOIN 
            horarios h ON con.idhorario = h.idhorario
        INNER JOIN 
            atenciones a ON h.idatencion = a.idatencion
        INNER JOIN 
            contratos cont ON a.idcontrato = cont.idcontrato
        INNER JOIN 
            colaboradores col ON cont.idcolaborador = col.idcolaborador
        INNER JOIN 
            personas p_doc ON col.idpersona = p_doc.idpersona
        INNER JOIN 
            especialidades e ON col.idespecialidad = e.idespecialidad
        LEFT JOIN 
            citas c ON c.fecha = con.fecha AND c.hora = con.horaprogramada AND c.idpersona = p_pac.idpersona
        LEFT JOIN 
            detalleventas dv ON dv.idconsulta = con.idconsulta
        WHERE 
            DATE(con.fecha) BETWEEN ? AND ?
        ";

            $params = [$fechaInicio, $fechaFin];

            // Añadir filtros adicionales
            if ($iddoctor !== null) {
                $sql .= " AND col.idcolaborador = ?";
                $params[] = $iddoctor;
            }

            if ($idespecialidad !== null) {
                $sql .= " AND e.idespecialidad = ?";
                $params[] = $idespecialidad;
            }

            if ($estado !== null) {
                $sql .= " AND COALESCE(c.estado, 'PROGRAMADA') = ?";
                $params[] = $estado;
            }

            // AÑADIDO: Filtro por nombre o apellido del paciente
            if ($paciente !== null) {
                $sql .= " AND (p_pac.nombres LIKE ? OR p_pac.apellidos LIKE ?)";
                $params[] = "%$paciente%";
                $params[] = "%$paciente%";
            }

            // AÑADIDO: Filtro por número de documento
            if ($nrodoc !== null) {
                $sql .= " AND p_pac.nrodoc LIKE ?";
                $params[] = "%$nrodoc%";
            }

            // Evitar duplicados explícitamente
            $sql .= " GROUP BY con.idconsulta, c.idcita";

            // Ordenamiento - Modificado para mejor visualización
            $sql .= " ORDER BY con.fecha DESC, con.horaprogramada ASC";

            // Log para depuración
            error_log("SQL listarCitas optimizado: " . $sql);
            error_log("Parámetros: " . json_encode($params));

            // Ejecutar consulta
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Log del resultado
            error_log("Citas encontradas: " . count($resultados));
            if (count($resultados) > 0) {
                error_log("Primera cita encontrada (muestra): " . json_encode($resultados[0]));
            }

            return $resultados;
        } catch (Exception $e) {
            error_log("Error al listar citas: " . $e->getMessage());
            error_log("Traza: " . $e->getTraceAsString());

            // Retornar array vacío en caso de error para evitar fallos en la interfaz
            return [];
        }
    }
    /**
     * Obtiene los detalles de una cita específica
     * @param int $idcita ID de la cita
     * @return array|false Datos de la cita o false si no se encuentra
     */
    public function obtenerCita($idcita)
    {
        try {
            // CORREGIDO: Consulta simplificada y mejorada
            $sql = "
            SELECT 
                c.idcita,
                c.fecha,
                c.hora,
                c.estado,
                c.observaciones,
                p.nombres AS paciente_nombre,
                p.apellidos AS paciente_apellido,
                p.tipodoc AS documento_tipo,
                p.nrodoc AS documento_numero,
                p.telefono,
                p.email,
                col.idcolaborador AS iddoctor,
                pdr.nombres AS doctor_nombre,
                pdr.apellidos AS doctor_apellido,
                e.idespecialidad,
                e.especialidad,
                con.idconsulta,
                COALESCE(dv.precio, 0) AS monto_pagado
            FROM 
                citas c
            INNER JOIN 
                personas p ON c.idpersona = p.idpersona
            INNER JOIN 
                consultas con ON c.fecha = con.fecha AND c.hora = con.horaprogramada
            LEFT JOIN 
                pacientes pac ON con.idpaciente = pac.idpaciente
            LEFT JOIN 
                horarios h ON con.idhorario = h.idhorario
            LEFT JOIN 
                atenciones a ON h.idatencion = a.idatencion
            LEFT JOIN 
                contratos cont ON a.idcontrato = cont.idcontrato
            LEFT JOIN 
                colaboradores col ON cont.idcolaborador = col.idcolaborador
            LEFT JOIN 
                personas pdr ON col.idpersona = pdr.idpersona
            LEFT JOIN 
                especialidades e ON col.idespecialidad = e.idespecialidad
            LEFT JOIN 
                detalleventas dv ON con.idconsulta = dv.idconsulta
            WHERE 
                c.idcita = ?
        ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$idcita]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Log para depuración
            error_log("Resultado de consulta para cita ID $idcita: " . ($result ? "Encontrada" : "No encontrada"));
            if ($result) {
                error_log("Datos de la cita: " . json_encode($result));
            }

            return $result;
        } catch (Exception $e) {
            error_log("Error al obtener cita: " . $e->getMessage());
            error_log("Traza: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Actualiza una cita existente
     * @param array $datos Datos de la cita a actualizar
     * @return array Resultado de la operación
     */
    public function actualizarCita($datos)
    {
        try {
            $this->pdo->beginTransaction();

            // Actualizar la tabla citas
            $stmt = $this->pdo->prepare("
            UPDATE citas 
            SET fecha = ?, hora = ?, estado = ?, observaciones = ?
            WHERE idcita = ?
        ");
            $stmt->execute([
                $datos['fecha'],
                $datos['hora'],
                $datos['estado'],
                $datos['observaciones'],
                $datos['idcita']
            ]);

            // Obtener la consulta relacionada para actualizarla
            $stmt = $this->pdo->prepare("
            SELECT con.idconsulta 
            FROM citas c
            INNER JOIN consultas con ON c.fecha = con.fecha AND c.hora = con.horaprogramada
            WHERE c.idcita = ?
        ");
            $stmt->execute([$datos['idcita']]);
            $consulta = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($consulta) {
                // Actualizar la consulta relacionada
                $stmt = $this->pdo->prepare("
                UPDATE consultas 
                SET fecha = ?, horaprogramada = ?
                WHERE idconsulta = ?
            ");
                $stmt->execute([
                    $datos['fecha'],
                    $datos['hora'],
                    $consulta['idconsulta']
                ]);
            }

            $this->pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Cita actualizada correctamente'
            ];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error al actualizar cita: " . $e->getMessage());
            return [
                'status' => false,
                'mensaje' => 'Error al actualizar la cita: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cancela una cita existente
     * @param int $idcita ID de la cita a cancelar
     * @return array Resultado de la operación
     */
    public function cancelarCita($idcita)
    {
        try {
            $this->pdo->beginTransaction();

            // Actualizar estado a CANCELADA
            $stmt = $this->pdo->prepare("
            UPDATE citas 
            SET estado = 'CANCELADA'
            WHERE idcita = ?
        ");
            $stmt->execute([$idcita]);

            $this->pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Cita cancelada correctamente'
            ];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error al cancelar cita: " . $e->getMessage());
            return [
                'status' => false,
                'mensaje' => 'Error al cancelar la cita: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene las citas para mostrar en el calendario sin duplicados
     * @param string $fechaInicio Fecha de inicio (formato Y-m-d)
     * @param string $fechaFin Fecha de fin (formato Y-m-d)
     * @param int|null $iddoctor ID del doctor (filtro opcional)
     * @param int|null $idespecialidad ID de la especialidad (filtro opcional)
     * @return array Citas formateadas para FullCalendar
     */
    public function obtenerCitasCalendario($fechaInicio, $fechaFin, $iddoctor = null, $idespecialidad = null)
    {
        try {
            // Log para depuración
            error_log("Obteniendo citas para calendario: $fechaInicio a $fechaFin, doctor: $iddoctor, especialidad: $idespecialidad");

            // SOLUCIÓN CORREGIDA: Consulta SQL mejorada con relación más específica
            $sql = "
        SELECT DISTINCT
            c.idcita,
            c.fecha,
            c.hora,
            c.estado,
            CONCAT(p.apellidos, ', ', p.nombres) AS nombre_paciente,
            col.idcolaborador AS iddoctor,
            CONCAT(pdr.apellidos, ', ', pdr.nombres) AS nombre_doctor,
            e.idespecialidad,
            e.especialidad,
            con.idconsulta
        FROM 
            citas c
        INNER JOIN 
            personas p ON c.idpersona = p.idpersona
        INNER JOIN 
            pacientes pac ON pac.idpersona = p.idpersona
        INNER JOIN 
            consultas con ON c.fecha = con.fecha 
            AND c.hora = con.horaprogramada 
            AND con.idpaciente = pac.idpaciente
        INNER JOIN 
            horarios h ON con.idhorario = h.idhorario
        INNER JOIN 
            atenciones a ON h.idatencion = a.idatencion
        INNER JOIN 
            contratos cont ON a.idcontrato = cont.idcontrato
        INNER JOIN 
            colaboradores col ON cont.idcolaborador = col.idcolaborador
        INNER JOIN 
            personas pdr ON col.idpersona = pdr.idpersona
        INNER JOIN 
            especialidades e ON col.idespecialidad = e.idespecialidad
        WHERE 
            c.fecha BETWEEN ? AND ?
        ";

            // Resto del código permanece igual
            if ($iddoctor !== null) {
                $sql .= " AND col.idcolaborador = ?";
            }

            if ($idespecialidad !== null) {
                $sql .= " AND e.idespecialidad = ?";
            }

            // CRÍTICO: Cambiar el GROUP BY para ser más específico
            $sql .= " ORDER BY c.fecha, c.hora, c.idcita";

            error_log("SQL CORREGIDO: " . $sql);

            $params = [$fechaInicio, $fechaFin];

            if ($iddoctor !== null) {
                $params[] = $iddoctor;
            }

            if ($idespecialidad !== null) {
                $params[] = $idespecialidad;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Resultados de la consulta CORREGIDA: " . count($resultados));

            $citas = [];
            foreach ($resultados as $row) {
                // Asignar color según estado
                $color = '#3788d8'; // Azul por defecto (programada)

                switch ($row['estado']) {
                    case 'CANCELADA':
                        $color = '#dc3545'; // Rojo
                        break;
                    case 'REALIZADA':
                        $color = '#28a745'; // Verde
                        break;
                    case 'NO ASISTIO':
                        $color = '#ffc107'; // Amarillo
                        break;
                }

                // Calcular hora de fin según duración de especialidad
                $fechaHoraInicio = $row['fecha'] . ' ' . $row['hora'];
                $duracion = $this->obtenerDuracionEspecialidad($row['idespecialidad']);
                $fechaHoraFin = date('Y-m-d H:i:s', strtotime($fechaHoraInicio . " +{$duracion} minutes"));

                $citas[] = [
                    'id' => $row['idcita'],
                    'title' => $row['nombre_paciente'] . ' - ' . $row['especialidad'],
                    'start' => $row['fecha'] . 'T' . $row['hora'],
                    'end' => $fechaHoraFin,
                    'color' => $color,
                    'extendedProps' => [
                        'doctor' => $row['nombre_doctor'],
                        'especialidad' => $row['especialidad'],
                        'estado' => $row['estado'],
                        'paciente' => $row['nombre_paciente'],
                        'iddoctor' => $row['iddoctor'], // AÑADIDO para debugging
                        'idconsulta' => $row['idconsulta'] // AÑADIDO para debugging
                    ]
                ];
            }

            error_log("Citas formateadas CORREGIDAS: " . count($citas));
            if (count($citas) > 0) {
                error_log("Primera cita CORREGIDA: " . json_encode($citas[0]));
            }

            return $citas;
        } catch (Exception $e) {
            error_log("Error al obtener citas para calendario: " . $e->getMessage());
            error_log("Traza: " . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Obtiene las horas disponibles para un doctor en una fecha específica
     * @param int $iddoctor ID del doctor
     * @param string $fecha Fecha en formato Y-m-d
     * @param int $idespecialidad ID de la especialidad
     * @return array Lista de horas disponibles
     */
    public function obtenerHorasDisponibles($iddoctor, $fecha, $idespecialidad)
    {
        try {
            // 1. Obtener duración de la cita según la especialidad
            $duracionMinutos = $this->obtenerDuracionEspecialidad($idespecialidad);

            // 2. Determinar día de la semana de la fecha seleccionada
            $diaSemana = date('w', strtotime($fecha));
            $diasSemana = ['DOMINGO', 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];
            $nombreDia = $diasSemana[$diaSemana];

            // 3. Obtener los horarios de atención del doctor para ese día
            $stmt = $this->pdo->prepare("
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
            ");

            $stmt->execute([$iddoctor, $nombreDia]);
            $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($horarios)) {
                return []; // No hay horarios definidos para ese día
            }

            // 4. Obtener las citas ya programadas para ese doctor en esa fecha
            $stmt = $this->pdo->prepare("
                SELECT 
                    con.horaprogramada
                FROM 
                    consultas con
                INNER JOIN 
                    horarios h ON con.idhorario = h.idhorario
                INNER JOIN 
                    atenciones a ON h.idatencion = a.idatencion
                INNER JOIN 
                    contratos c ON a.idcontrato = c.idcontrato
                WHERE 
                    c.idcolaborador = ? AND 
                    con.fecha = ?
                ORDER BY 
                    con.horaprogramada
            ");

            $stmt->execute([$iddoctor, $fecha]);
            $citasExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // 5. Generar slots disponibles según la duración de la especialidad
            $horasDisponibles = [];

            foreach ($horarios as $horario) {
                $horaInicio = strtotime($horario['horainicio']);
                $horaFin = strtotime($horario['horafin']);

                // Iterar en intervalos de la duración de la cita
                for ($hora = $horaInicio; $hora <= ($horaFin - $duracionMinutos * 60); $hora += $duracionMinutos * 60) {
                    $horaStr = date('H:i:s', $hora);

                    // Verificar si ya hay una cita en esta hora
                    if (!in_array($horaStr, $citasExistentes)) {
                        // También verificar que no haya superposición con otras citas
                        $hayConflicto = false;

                        foreach ($citasExistentes as $citaHora) {
                            $citaInicioTime = strtotime($citaHora);
                            $citaFinTime = $citaInicioTime + ($duracionMinutos * 60);

                            $nuevoInicioTime = $hora;
                            $nuevoFinTime = $hora + ($duracionMinutos * 60);

                            // Comprobar superposición
                            if (
                                ($nuevoInicioTime >= $citaInicioTime && $nuevoInicioTime < $citaFinTime) ||
                                ($nuevoFinTime > $citaInicioTime && $nuevoFinTime <= $citaFinTime) ||
                                ($nuevoInicioTime <= $citaInicioTime && $nuevoFinTime >= $citaFinTime)
                            ) {
                                $hayConflicto = true;
                                break;
                            }
                        }

                        if (!$hayConflicto) {
                            $horaFinStr = date('H:i:s', $hora + $duracionMinutos * 60);

                            $horasDisponibles[] = [
                                'hora' => $horaStr,
                                'horaFin' => $horaFinStr,
                                'disponible' => true,
                                'duracion' => $duracionMinutos
                            ];
                        }
                    }
                }
            }

            return $horasDisponibles;
        } catch (Exception $e) {
            error_log("Error al obtener horas disponibles: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Obtiene las citas para un día específico y un doctor específico
     * @param string $fecha Fecha en formato Y-m-d
     * @param int $iddoctor ID del doctor (opcional)
     * @return array Lista de citas del día
     */
    public function obtenerCitasPorDia($fecha, $horaActual, $iddoctor = null, $estado = null)
    {
        try {
            // Construir consulta base
            $sql = "
        SELECT 
            c.idcita,
            c.fecha,
            c.hora,
            c.estado,
            c.observaciones,
            CONCAT(p.apellidos, ', ', p.nombres) AS nombre_paciente,
            p.tipodoc,
            p.nrodoc,
            p.telefono,
            con.idconsulta,
            con.idpaciente,
            pac.idpaciente,
            (SELECT COUNT(*) > 0 FROM recetas r WHERE r.idconsulta = con.idconsulta) AS tiene_receta
        FROM 
            citas c
        INNER JOIN 
            personas p ON c.idpersona = p.idpersona
        INNER JOIN 
            consultas con ON c.fecha = con.fecha AND c.hora = con.horaprogramada
        INNER JOIN 
            pacientes pac ON con.idpaciente = pac.idpaciente
        INNER JOIN 
            horarios h ON con.idhorario = h.idhorario
        INNER JOIN 
            atenciones a ON h.idatencion = a.idatencion
        INNER JOIN 
            contratos cont ON a.idcontrato = cont.idcontrato
        INNER JOIN 
            colaboradores col ON cont.idcolaborador = col.idcolaborador
        WHERE 
            DATE(c.fecha) = DATE(:fecha)
        ";

            $params = [':fecha' => $fecha];

            // Añadir filtro por doctor si se especifica
            if ($iddoctor !== null) {
                $sql .= " AND col.idcolaborador = :iddoctor";
                $params[':iddoctor'] = $iddoctor;
            }

            // Añadir filtro de estado si se especifica
            if ($estado !== null) {
                $sql .= " AND c.estado = :estado";
                $params[':estado'] = $estado;
            }

            // Ordenar por hora
            $sql .= " ORDER BY c.hora ASC";

            // Log para depuración
            error_log("SQL obtenerCitasPorDia: " . $sql);
            error_log("Parámetros: " . json_encode($params));

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Log del número de resultados
            error_log("Resultados obtenidos: " . count($resultados));

            return $resultados;
        } catch (Exception $e) {
            error_log("Error al obtener citas por día: " . $e->getMessage());
            error_log("Traza: " . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Busca citas con múltiples filtros
     * @param array $filtros Filtros para la búsqueda (fecha, estado, tipodoc, nrodoc, paciente)
     * @param int $iddoctor ID del doctor (opcional)
     * @return array Lista de citas que cumplen los filtros
     */
    public function buscarCitasConFiltros($filtros = [], $iddoctor = null)
    {
        try {
            // Construir consulta base
            $sql = "
            SELECT 
                c.idcita,
                c.fecha,
                c.hora,
                c.estado,
                c.observaciones,
                CONCAT(p.apellidos, ', ', p.nombres) AS nombre_paciente,
                p.tipodoc,
                p.nrodoc,
                p.telefono,
                con.idconsulta,
                con.idpaciente,
                pac.idpaciente,
                col.idcolaborador AS iddoctor,
                p_doc.nombres AS doctor_nombre,
                p_doc.apellidos AS doctor_apellido,
                e.idespecialidad,
                e.especialidad,
                (SELECT COUNT(*) > 0 FROM recetas r WHERE r.idconsulta = con.idconsulta) AS tiene_receta
            FROM 
                citas c
            INNER JOIN 
                personas p ON c.idpersona = p.idpersona
            INNER JOIN 
                consultas con ON c.fecha = con.fecha AND c.hora = con.horaprogramada
            INNER JOIN 
                pacientes pac ON con.idpaciente = pac.idpaciente
            INNER JOIN 
                horarios h ON con.idhorario = h.idhorario
            INNER JOIN 
                atenciones a ON h.idatencion = a.idatencion
            INNER JOIN 
                contratos cont ON a.idcontrato = cont.idcontrato
            INNER JOIN 
                colaboradores col ON cont.idcolaborador = col.idcolaborador
            INNER JOIN 
                personas p_doc ON col.idpersona = p_doc.idpersona
            INNER JOIN 
                especialidades e ON col.idespecialidad = e.idespecialidad
            WHERE 1=1
        ";

            $params = [];

            // Añadir filtros si existen
            if (!empty($filtros['fecha'])) {
                $sql .= " AND c.fecha = :fecha";
                $params[':fecha'] = $filtros['fecha'];
            }

            if (!empty($filtros['estado'])) {
                $sql .= " AND c.estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }

            if (!empty($filtros['tipodoc'])) {
                $sql .= " AND p.tipodoc = :tipodoc";
                $params[':tipodoc'] = $filtros['tipodoc'];
            }

            if (!empty($filtros['nrodoc'])) {
                $sql .= " AND p.nrodoc LIKE :nrodoc";
                $params[':nrodoc'] = '%' . $filtros['nrodoc'] . '%';
            }

            if (!empty($filtros['paciente'])) {
                $sql .= " AND (p.apellidos LIKE :paciente OR p.nombres LIKE :paciente)";
                $params[':paciente'] = '%' . $filtros['paciente'] . '%';
            }

            // Añadir filtro por doctor si se especifica
            if ($iddoctor !== null) {
                $sql .= " AND col.idcolaborador = :iddoctor";
                $params[':iddoctor'] = $iddoctor;
            }

            // Ordenar por fecha y hora
            $sql .= " ORDER BY c.fecha, c.hora ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al buscar citas con filtros: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualiza el estado de una cita
     * @param int $idcita ID de la cita
     * @param string $estado Nuevo estado (PROGRAMADA, REALIZADA, CANCELADA, NO ASISTIO)
     * @return bool True si se actualizó correctamente, False en caso contrario
     */
    public function actualizarEstadoCita($idcita, $estado)
    {
        try {
            // Validar que el estado sea válido
            $estadosValidos = ['PROGRAMADA', 'REALIZADA', 'CANCELADA', 'NO ASISTIO'];
            if (!in_array($estado, $estadosValidos)) {
                return false;
            }

            $stmt = $this->pdo->prepare("
            UPDATE citas 
            SET estado = :estado 
            WHERE idcita = :idcita
        ");

            $stmt->execute([
                ':estado' => $estado,
                ':idcita' => $idcita
            ]);

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error al actualizar estado de cita: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza automáticamente el estado de citas pasadas a NO ASISTIO
     * @return int Número de citas actualizadas
     */
    public function actualizarCitasNoAsistidas($fechaHoy, $horaActual)
    {
        try {
            // Consulta simplificada que solo verifica si ya pasó el tiempo de la cita + 20 minutos
            $query = "
        UPDATE citas c
        SET estado = 'NO ASISTIO' 
        WHERE c.fecha = :fecha 
        AND c.estado = 'PROGRAMADA'
        AND TIMESTAMPADD(MINUTE, 20, CONCAT(c.fecha, ' ', c.hora)) < :fechaHoraActual
        ";

            $stmt = $this->pdo->prepare($query);
            $fechaHoraActual = $fechaHoy . ' ' . $horaActual;
            $stmt->bindParam(':fecha', $fechaHoy);
            $stmt->bindParam(':fechaHoraActual', $fechaHoraActual);
            $stmt->execute();

            // Registrar información para depuración
            error_log("Actualizando citas no asistidas: fecha=$fechaHoy, hora=$horaActual, actualizadas=" . $stmt->rowCount());

            // Devolver número de filas afectadas
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Error al actualizar citas no asistidas: " . $e->getMessage());
            error_log("Traza: " . $e->getTraceAsString());
            return 0;
        }
    }

    /**
     * Obtiene la duración en minutos para una especialidad
     * @param int $idespecialidad ID de la especialidad
     * @return int Duración en minutos
     */
    private function obtenerDuracionEspecialidad($idespecialidad)
    {
        // Duraciones de consulta por especialidad según los requisitos
        $duraciones = [
            1 => 20, // Medicina General / Familiar
            2 => 20, // Cardiología
            3 => 30, // Neurología
            4 => 20, // Endocrinología
            5 => 20, // Pediatría
            6 => 20, // Ginecología / Obstetricia
            7 => 20, // Dermatología
            8 => 45, // Psiquiatría
            9 => 45, // Psicología
            10 => 20, // Traumatología / Ortopedia
            11 => 20, // Oftalmología
            12 => 30  // Odontología
        ];

        // Retornar duración para la especialidad solicitada, o 20 min por defecto
        return $duraciones[$idespecialidad] ?? 20;
    }
}
