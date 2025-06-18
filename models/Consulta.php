<?php /*RUTA: sistemaclinica/models/Consulta.php*/?>
<?php

require_once 'Conexion.php';

class Consulta
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    /**
     * Registra una nueva consulta médica
     * @param array $datos Datos de la consulta
     * @return array Resultado de la operación
     */
    public function registrarConsulta($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // Registrar consulta
            $sql = "INSERT INTO consultas (
                fecha, 
                idhorario, 
                horaprogramada, 
                horaatencion, 
                idpaciente, 
                condicionpaciente, 
                iddiagnostico
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $datos['fecha'],
                $datos['idhorario'],
                $datos['horaprogramada'],
                $datos['horaatencion'] ?? null,
                $datos['idpaciente'],
                $datos['condicionpaciente'] ?? 'ESTABLE',
                $datos['iddiagnostico'] ?? null
            ]);

            $idconsulta = $pdo->lastInsertId();

            // Si hay triaje, registrarlo
            if (isset($datos['triaje']) && !empty($datos['triaje'])) {
                $sqlTriaje = "INSERT INTO triajes (
                    idconsulta, 
                    idenfermera, 
                    hora, 
                    temperatura, 
                    presionarterial, 
                    frecuenciacardiaca, 
                    saturacionoxigeno, 
                    peso, 
                    estatura
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmtTriaje = $pdo->prepare($sqlTriaje);
                $stmtTriaje->execute([
                    $idconsulta,
                    $datos['triaje']['idenfermera'],
                    $datos['triaje']['hora'],
                    $datos['triaje']['temperatura'] ?? null,
                    $datos['triaje']['presionarterial'] ?? null,
                    $datos['triaje']['frecuenciacardiaca'] ?? null,
                    $datos['triaje']['saturacionoxigeno'] ?? null,
                    $datos['triaje']['peso'] ?? null,
                    $datos['triaje']['estatura'] ?? null
                ]);
            }

            // Si hay receta, registrarla
            if (isset($datos['receta']) && !empty($datos['receta'])) {
                $sqlReceta = "INSERT INTO recetas (
                    idconsulta, 
                    medicacion, 
                    cantidad, 
                    frecuencia
                ) VALUES (?, ?, ?, ?)";

                $stmtReceta = $pdo->prepare($sqlReceta);
                $stmtReceta->execute([
                    $idconsulta,
                    $datos['receta']['medicacion'],
                    $datos['receta']['cantidad'],
                    $datos['receta']['frecuencia']
                ]);

                $idreceta = $pdo->lastInsertId();

                // Si hay tratamiento, registrarlo
                if (isset($datos['tratamiento']) && !empty($datos['tratamiento'])) {
                    $sqlTratamiento = "INSERT INTO tratamiento (
                        medicacion, 
                        dosis, 
                        frecuencia, 
                        duracion, 
                        idreceta
                    ) VALUES (?, ?, ?, ?, ?)";

                    $stmtTratamiento = $pdo->prepare($sqlTratamiento);
                    $stmtTratamiento->execute([
                        $datos['tratamiento']['medicacion'],
                        $datos['tratamiento']['dosis'],
                        $datos['tratamiento']['frecuencia'],
                        $datos['tratamiento']['duracion'],
                        $idreceta
                    ]);
                }
            }

            // Si hay servicios requeridos, registrarlos
            if (isset($datos['servicios']) && !empty($datos['servicios'])) {
                foreach ($datos['servicios'] as $servicio) {
                    $sqlServicio = "INSERT INTO serviciosrequeridos (
                        idconsulta, 
                        idtiposervicio, 
                        solicitud, 
                        fechaanalisis, 
                        fechaprocesamiento, 
                        fechaentrega
                    ) VALUES (?, ?, ?, ?, ?, ?)";

                    $stmtServicio = $pdo->prepare($sqlServicio);
                    $stmtServicio->execute([
                        $idconsulta,
                        $servicio['idtiposervicio'],
                        $servicio['solicitud'] ?? null,
                        $servicio['fechaanalisis'] ?? null,
                        $servicio['fechaprocesamiento'] ?? null,
                        $servicio['fechaentrega'] ?? null
                    ]);
                }
            }

            // Actualizar el estado de la cita correspondiente si existe
            if (isset($datos['idcita']) && !empty($datos['idcita'])) {
                $sqlCita = "UPDATE citas SET estado = 'REALIZADA' WHERE idcita = ?";
                $stmtCita = $pdo->prepare($sqlCita);
                $stmtCita->execute([$datos['idcita']]);
            }

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Consulta registrada correctamente',
                'idconsulta' => $idconsulta
            ];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return [
                'status' => false,
                'mensaje' => 'Error al registrar consulta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza los datos de una consulta existente
     * @param array $datos Datos actualizados de la consulta
     * @return array Resultado de la operación
     */
    public function actualizarConsulta($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // Validar que la consulta exista
            if (!isset($datos['idconsulta']) || empty($datos['idconsulta'])) {
                return [
                    'status' => false,
                    'mensaje' => 'ID de consulta no proporcionado'
                ];
            }

            // Actualizar datos básicos de la consulta
            $sqlConsulta = "UPDATE consultas SET 
                fecha = COALESCE(?, fecha),
                horaatencion = COALESCE(?, horaatencion),
                condicionpaciente = COALESCE(?, condicionpaciente),
                iddiagnostico = COALESCE(?, iddiagnostico)
            WHERE idconsulta = ?";

            $stmtConsulta = $pdo->prepare($sqlConsulta);
            $stmtConsulta->execute([
                $datos['fecha'] ?? null,
                $datos['horaatencion'] ?? null,
                $datos['condicionpaciente'] ?? null,
                $datos['iddiagnostico'] ?? null,
                $datos['idconsulta']
            ]);

            // Actualizar triaje si existe
            if (isset($datos['triaje']) && !empty($datos['triaje'])) {
                // Verificar si ya existe un triaje para esta consulta
                $sqlCheckTriaje = "SELECT idtriaje FROM triajes WHERE idconsulta = ?";
                $stmtCheckTriaje = $pdo->prepare($sqlCheckTriaje);
                $stmtCheckTriaje->execute([$datos['idconsulta']]);
                $triajeExistente = $stmtCheckTriaje->fetch(PDO::FETCH_ASSOC);

                if ($triajeExistente) {
                    // Actualizar triaje existente
                    $sqlTriaje = "UPDATE triajes SET 
                        idenfermera = COALESCE(?, idenfermera),
                        hora = COALESCE(?, hora),
                        temperatura = COALESCE(?, temperatura),
                        presionarterial = COALESCE(?, presionarterial),
                        frecuenciacardiaca = COALESCE(?, frecuenciacardiaca),
                        saturacionoxigeno = COALESCE(?, saturacionoxigeno),
                        peso = COALESCE(?, peso),
                        estatura = COALESCE(?, estatura)
                    WHERE idconsulta = ?";

                    $stmtTriaje = $pdo->prepare($sqlTriaje);
                    $stmtTriaje->execute([
                        $datos['triaje']['idenfermera'] ?? null,
                        $datos['triaje']['hora'] ?? null,
                        $datos['triaje']['temperatura'] ?? null,
                        $datos['triaje']['presionarterial'] ?? null,
                        $datos['triaje']['frecuenciacardiaca'] ?? null,
                        $datos['triaje']['saturacionoxigeno'] ?? null,
                        $datos['triaje']['peso'] ?? null,
                        $datos['triaje']['estatura'] ?? null,
                        $datos['idconsulta']
                    ]);
                } else {
                    // Crear nuevo triaje
                    $sqlTriaje = "INSERT INTO triajes (
                        idconsulta, 
                        idenfermera, 
                        hora, 
                        temperatura, 
                        presionarterial, 
                        frecuenciacardiaca, 
                        saturacionoxigeno, 
                        peso, 
                        estatura
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmtTriaje = $pdo->prepare($sqlTriaje);
                    $stmtTriaje->execute([
                        $datos['idconsulta'],
                        $datos['triaje']['idenfermera'],
                        $datos['triaje']['hora'],
                        $datos['triaje']['temperatura'] ?? null,
                        $datos['triaje']['presionarterial'] ?? null,
                        $datos['triaje']['frecuenciacardiaca'] ?? null,
                        $datos['triaje']['saturacionoxigeno'] ?? null,
                        $datos['triaje']['peso'] ?? null,
                        $datos['triaje']['estatura'] ?? null
                    ]);
                }
            }

            // Actualizar o crear receta
            if (isset($datos['receta']) && !empty($datos['receta'])) {
                // Verificar si ya existe una receta para esta consulta
                $sqlCheckReceta = "SELECT idreceta FROM recetas WHERE idconsulta = ?";
                $stmtCheckReceta = $pdo->prepare($sqlCheckReceta);
                $stmtCheckReceta->execute([$datos['idconsulta']]);
                $recetaExistente = $stmtCheckReceta->fetch(PDO::FETCH_ASSOC);

                if ($recetaExistente) {
                    // Actualizar receta existente
                    $sqlReceta = "UPDATE recetas SET 
                        medicacion = COALESCE(?, medicacion),
                        cantidad = COALESCE(?, cantidad),
                        frecuencia = COALESCE(?, frecuencia)
                    WHERE idconsulta = ?";

                    $stmtReceta = $pdo->prepare($sqlReceta);
                    $stmtReceta->execute([
                        $datos['receta']['medicacion'] ?? null,
                        $datos['receta']['cantidad'] ?? null,
                        $datos['receta']['frecuencia'] ?? null,
                        $datos['idconsulta']
                    ]);

                    $idreceta = $recetaExistente['idreceta'];
                } else {
                    // Crear nueva receta
                    $sqlReceta = "INSERT INTO recetas (
                        idconsulta, 
                        medicacion, 
                        cantidad, 
                        frecuencia
                    ) VALUES (?, ?, ?, ?)";

                    $stmtReceta = $pdo->prepare($sqlReceta);
                    $stmtReceta->execute([
                        $datos['idconsulta'],
                        $datos['receta']['medicacion'],
                        $datos['receta']['cantidad'],
                        $datos['receta']['frecuencia']
                    ]);

                    $idreceta = $pdo->lastInsertId();
                }

                // Actualizar o crear tratamiento
                if (isset($datos['tratamiento']) && !empty($datos['tratamiento'])) {
                    // Verificar si ya existe un tratamiento para esta receta
                    $sqlCheckTratamiento = "SELECT idtratamiento FROM tratamiento WHERE idreceta = ?";
                    $stmtCheckTratamiento = $pdo->prepare($sqlCheckTratamiento);
                    $stmtCheckTratamiento->execute([$idreceta]);
                    $tratamientoExistente = $stmtCheckTratamiento->fetch(PDO::FETCH_ASSOC);

                    if ($tratamientoExistente) {
                        // Actualizar tratamiento existente
                        $sqlTratamiento = "UPDATE tratamiento SET 
                            medicacion = COALESCE(?, medicacion),
                            dosis = COALESCE(?, dosis),
                            frecuencia = COALESCE(?, frecuencia),
                            duracion = COALESCE(?, duracion)
                        WHERE idreceta = ?";

                        $stmtTratamiento = $pdo->prepare($sqlTratamiento);
                        $stmtTratamiento->execute([
                            $datos['tratamiento']['medicacion'] ?? null,
                            $datos['tratamiento']['dosis'] ?? null,
                            $datos['tratamiento']['frecuencia'] ?? null,
                            $datos['tratamiento']['duracion'] ?? null,
                            $idreceta
                        ]);
                    } else {
                        // Crear nuevo tratamiento
                        $sqlTratamiento = "INSERT INTO tratamiento (
                            medicacion, 
                            dosis, 
                            frecuencia, 
                            duracion, 
                            idreceta
                        ) VALUES (?, ?, ?, ?, ?)";

                        $stmtTratamiento = $pdo->prepare($sqlTratamiento);
                        $stmtTratamiento->execute([
                            $datos['tratamiento']['medicacion'],
                            $datos['tratamiento']['dosis'],
                            $datos['tratamiento']['frecuencia'],
                            $datos['tratamiento']['duracion'],
                            $idreceta
                        ]);
                    }
                }
            }

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Consulta actualizada correctamente',
                'idconsulta' => $datos['idconsulta']
            ];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return [
                'status' => false,
                'mensaje' => 'Error al actualizar consulta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene los detalles de una consulta específica
     * @param int $idconsulta ID de la consulta
     * @return array Detalles de la consulta
     */
    public function obtenerConsultaPorId($idconsulta)
    {
        try {
            $pdo = $this->conexion->getConexion();
            
            // Consulta principal
            $sql = "SELECT 
                c.idconsulta,
                c.fecha,
                c.idhorario,
                c.horaprogramada,
                c.horaatencion,
                c.idpaciente,
                c.condicionpaciente,
                c.iddiagnostico,
                
                p.idpersona,
                p.apellidos as paciente_apellidos,
                p.nombres as paciente_nombres,
                p.tipodoc as paciente_tipodoc,
                p.nrodoc as paciente_nrodoc,
                p.fechanacimiento,
                p.genero,
                
                diag.nombre as diagnostico,
                diag.descripcion as diagnostico_descripcion,
                diag.codigo as diagnostico_codigo,
                
                h.horainicio,
                h.horafin,
                
                a.diasemana,
                
                ct.idcolaborador as iddoctor,
                
                doc_per.apellidos as doctor_apellidos,
                doc_per.nombres as doctor_nombres,
                
                esp.idespecialidad,
                esp.especialidad,
                esp.precioatencion
                
            FROM consultas c
            INNER JOIN pacientes pac ON c.idpaciente = pac.idpaciente
            INNER JOIN personas p ON pac.idpersona = p.idpersona
            LEFT JOIN diagnosticos diag ON c.iddiagnostico = diag.iddiagnostico
            INNER JOIN horarios h ON c.idhorario = h.idhorario
            INNER JOIN atenciones a ON h.idatencion = a.idatencion
            INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
            INNER JOIN colaboradores col ON ct.idcolaborador = col.idcolaborador
            INNER JOIN personas doc_per ON col.idpersona = doc_per.idpersona
            INNER JOIN especialidades esp ON col.idespecialidad = esp.idespecialidad
            WHERE c.idconsulta = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idconsulta]);
            $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$consulta) {
                return null;
            }
            
            // Obtener triaje si existe
            $sqlTriaje = "SELECT 
                t.idtriaje,
                t.idenfermera,
                t.hora,
                t.temperatura,
                t.presionarterial,
                t.frecuenciacardiaca,
                t.saturacionoxigeno,
                t.peso,
                t.estatura,
                CONCAT(p.apellidos, ', ', p.nombres) as enfermera_nombre
            FROM triajes t
            INNER JOIN colaboradores c ON t.idenfermera = c.idcolaborador
            INNER JOIN personas p ON c.idpersona = p.idpersona
            WHERE t.idconsulta = ?";
            
            $stmtTriaje = $pdo->prepare($sqlTriaje);
            $stmtTriaje->execute([$idconsulta]);
            $consulta['triaje'] = $stmtTriaje->fetch(PDO::FETCH_ASSOC) ?: null;
            
            // Calcular IMC si existe información de peso y estatura
            if ($consulta['triaje'] && $consulta['triaje']['peso'] && $consulta['triaje']['estatura']) {
                $estatura_metros = $consulta['triaje']['estatura'] / 100; // Convertir a metros
                $consulta['triaje']['imc'] = round($consulta['triaje']['peso'] / ($estatura_metros * $estatura_metros), 2);
                
                // Clasificación del IMC
                if ($consulta['triaje']['imc'] < 18.5) {
                    $consulta['triaje']['clasificacion_imc'] = 'Bajo peso';
                } else if ($consulta['triaje']['imc'] < 25) {
                    $consulta['triaje']['clasificacion_imc'] = 'Peso normal';
                } else if ($consulta['triaje']['imc'] < 30) {
                    $consulta['triaje']['clasificacion_imc'] = 'Sobrepeso';
                } else {
                    $consulta['triaje']['clasificacion_imc'] = 'Obesidad';
                }
            }
            
            // Obtener receta si existe
            $sqlReceta = "SELECT 
                r.idreceta,
                r.medicacion,
                r.cantidad,
                r.frecuencia
            FROM recetas r
            WHERE r.idconsulta = ?";
            
            $stmtReceta = $pdo->prepare($sqlReceta);
            $stmtReceta->execute([$idconsulta]);
            $consulta['receta'] = $stmtReceta->fetch(PDO::FETCH_ASSOC) ?: null;
            
            // Si hay receta, obtener tratamiento
            if ($consulta['receta']) {
                $sqlTratamiento = "SELECT 
                    t.idtratamiento,
                    t.medicacion,
                    t.dosis,
                    t.frecuencia,
                    t.duracion
                FROM tratamiento t
                WHERE t.idreceta = ?";
                
                $stmtTratamiento = $pdo->prepare($sqlTratamiento);
                $stmtTratamiento->execute([$consulta['receta']['idreceta']]);
                $consulta['tratamiento'] = $stmtTratamiento->fetch(PDO::FETCH_ASSOC) ?: null;
            }
            
            // Obtener servicios requeridos
            $sqlServicios = "SELECT 
                sr.idserviciorequerido,
                sr.idtiposervicio,
                sr.solicitud,
                sr.fechaanalisis,
                sr.fechaprocesamiento,
                sr.fechaentrega,
                ts.tiposervicio,
                ts.servicio,
                ts.precioservicio
            FROM serviciosrequeridos sr
            INNER JOIN tiposervicio ts ON sr.idtiposervicio = ts.idtiposervicio
            WHERE sr.idconsulta = ?";
            
            $stmtServicios = $pdo->prepare($sqlServicios);
            $stmtServicios->execute([$idconsulta]);
            $consulta['servicios'] = $stmtServicios->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
            // Para cada servicio, obtener sus resultados
            foreach ($consulta['servicios'] as $key => $servicio) {
                $sqlResultados = "SELECT 
                    r.idresultado,
                    r.caracteristicaevaluada,
                    r.condicion,
                    r.rutaimagen
                FROM resultados r
                WHERE r.idserviciorequerido = ?";
                
                $stmtResultados = $pdo->prepare($sqlResultados);
                $stmtResultados->execute([$servicio['idserviciorequerido']]);
                $consulta['servicios'][$key]['resultados'] = $stmtResultados->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }
            
            // Calcular la edad
            if (isset($consulta['fechanacimiento'])) {
                $nacimiento = new DateTime($consulta['fechanacimiento']);
                $hoy = new DateTime();
                $edad = $hoy->diff($nacimiento);
                $consulta['edad'] = $edad->y;
            }
            
            return $consulta;
        } catch (PDOException $e) {
            error_log('Error al obtener consulta: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene la lista de consultas con filtros opcionales
     * @param array $filtros Filtros de búsqueda opcionales
     * @return array Lista de consultas
     */
    public function listarConsultas($filtros = [])
    {
        try {
            $pdo = $this->conexion->getConexion();
            
            $sql = "SELECT 
                c.idconsulta,
                c.fecha,
                TIME_FORMAT(c.horaprogramada, '%H:%i') as horaprogramada,
                TIME_FORMAT(c.horaatencion, '%H:%i') as horaatencion,
                c.condicionpaciente,
                c.idpaciente,
                
                CONCAT(p.apellidos, ', ', p.nombres) as paciente_nombre,
                p.nrodoc as paciente_documento,
                p.genero,
                TIMESTAMPDIFF(YEAR, p.fechanacimiento, CURDATE()) as edad,
                
                d.nombre as diagnostico_nombre,
                
                CONCAT(doc.apellidos, ', ', doc.nombres) as doctor_nombre,
                
                e.especialidad
                
            FROM consultas c
            INNER JOIN pacientes pac ON c.idpaciente = pac.idpaciente
            INNER JOIN personas p ON pac.idpersona = p.idpersona
            LEFT JOIN diagnosticos d ON c.iddiagnostico = d.iddiagnostico
            INNER JOIN horarios h ON c.idhorario = h.idhorario
            INNER JOIN atenciones a ON h.idatencion = a.idatencion
            INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
            INNER JOIN colaboradores col ON ct.idcolaborador = col.idcolaborador
            INNER JOIN personas doc ON col.idpersona = doc.idpersona
            INNER JOIN especialidades e ON col.idespecialidad = e.idespecialidad
            WHERE 1=1";
            
            $parametros = [];
            
            // Filtro por fecha
            if (isset($filtros['fecha']) && !empty($filtros['fecha'])) {
                $sql .= " AND c.fecha = ?";
                $parametros[] = $filtros['fecha'];
            }
            
            // Filtro por rango de fechas
            if (isset($filtros['fecha_inicio']) && !empty($filtros['fecha_inicio'])) {
                $sql .= " AND c.fecha >= ?";
                $parametros[] = $filtros['fecha_inicio'];
            }
            
            if (isset($filtros['fecha_fin']) && !empty($filtros['fecha_fin'])) {
                $sql .= " AND c.fecha <= ?";
                $parametros[] = $filtros['fecha_fin'];
            }
            
            // Filtro por especialidad
            if (isset($filtros['idespecialidad']) && !empty($filtros['idespecialidad'])) {
                $sql .= " AND e.idespecialidad = ?";
                $parametros[] = $filtros['idespecialidad'];
            }
            
            // Filtro por doctor
            if (isset($filtros['iddoctor']) && !empty($filtros['iddoctor'])) {
                $sql .= " AND col.idcolaborador = ?";
                $parametros[] = $filtros['iddoctor'];
            }
            
            // Filtro por paciente
            if (isset($filtros['idpaciente']) && !empty($filtros['idpaciente'])) {
                $sql .= " AND c.idpaciente = ?";
                $parametros[] = $filtros['idpaciente'];
            }
            
            // Filtro por documento del paciente
            if (isset($filtros['documento_paciente']) && !empty($filtros['documento_paciente'])) {
                $sql .= " AND p.nrodoc LIKE ?";
                $parametros[] = '%' . $filtros['documento_paciente'] . '%';
            }
            
            // Filtro por nombre o apellido del paciente
            if (isset($filtros['nombre_paciente']) && !empty($filtros['nombre_paciente'])) {
                $sql .= " AND (p.nombres LIKE ? OR p.apellidos LIKE ?)";
                $parametros[] = '%' . $filtros['nombre_paciente'] . '%';
                $parametros[] = '%' . $filtros['nombre_paciente'] . '%';
            }
            
            // Ordenar por fecha y hora
            $sql .= " ORDER BY c.fecha DESC, c.horaprogramada DESC";
            
            // Límite y offset para paginación
            if (isset($filtros['limite']) && is_numeric($filtros['limite'])) {
                $sql .= " LIMIT ?";
                $parametros[] = (int)$filtros['limite'];
                
                if (isset($filtros['offset']) && is_numeric($filtros['offset'])) {
                    $sql .= " OFFSET ?";
                    $parametros[] = (int)$filtros['offset'];
                }
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($parametros);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al listar consultas: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Registra o actualiza la receta médica de una consulta
     * @param array $datos Datos de la receta
     * @return array Resultado de la operación
     */
    public function gestionarReceta($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();
            
            // Validar datos requeridos
            if (!isset($datos['idconsulta']) || empty($datos['idconsulta'])) {
                return [
                    'status' => false,
                    'mensaje' => 'ID de consulta no proporcionado'
                ];
            }
            
            if (!isset($datos['medicacion']) || empty($datos['medicacion'])) {
                return [
                    'status' => false,
                    'mensaje' => 'La medicación es requerida'
                ];
            }
            
            // Verificar si ya existe una receta para esta consulta
            $sqlCheck = "SELECT idreceta FROM recetas WHERE idconsulta = ?";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([$datos['idconsulta']]);
            $recetaExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($recetaExistente) {
                // Actualizar receta existente
                $sql = "UPDATE recetas SET 
                    medicacion = ?,
                    cantidad = ?,
                    frecuencia = ?
                WHERE idconsulta = ?";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $datos['medicacion'],
                    $datos['cantidad'],
                    $datos['frecuencia'],
                    $datos['idconsulta']
                ]);
                
                $idreceta = $recetaExistente['idreceta'];
                $mensaje = 'Receta actualizada correctamente';
            } else {
                // Crear nueva receta
                $sql = "INSERT INTO recetas (
                    idconsulta,
                    medicacion,
                    cantidad,
                    frecuencia
                ) VALUES (?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $datos['idconsulta'],
                    $datos['medicacion'],
                    $datos['cantidad'],
                    $datos['frecuencia']
                ]);
                
                $idreceta = $pdo->lastInsertId();
                $mensaje = 'Receta creada correctamente';
            }
            
            // Gestionar tratamiento si se proporciona
            if (isset($datos['tratamiento']) && !empty($datos['tratamiento'])) {
                // Verificar si ya existe un tratamiento para esta receta
                $sqlCheckTratamiento = "SELECT idtratamiento FROM tratamiento WHERE idreceta = ?";
                $stmtCheckTratamiento = $pdo->prepare($sqlCheckTratamiento);
                $stmtCheckTratamiento->execute([$idreceta]);
                $tratamientoExistente = $stmtCheckTratamiento->fetch(PDO::FETCH_ASSOC);
                
                if ($tratamientoExistente) {
                    // Actualizar tratamiento existente
                    $sqlTratamiento = "UPDATE tratamiento SET 
                        medicacion = ?,
                        dosis = ?,
                        frecuencia = ?,
                        duracion = ?
                    WHERE idreceta = ?";
                    
                    $stmtTratamiento = $pdo->prepare($sqlTratamiento);
                    $stmtTratamiento->execute([
                        $datos['tratamiento']['medicacion'],
                        $datos['tratamiento']['dosis'],
                        $datos['tratamiento']['frecuencia'],
                        $datos['tratamiento']['duracion'],
                        $idreceta
                    ]);
                } else {
                    // Crear nuevo tratamiento
                    $sqlTratamiento = "INSERT INTO tratamiento (
                        medicacion,
                        dosis,
                        frecuencia,
                        duracion,
                        idreceta
                    ) VALUES (?, ?, ?, ?, ?)";
                    
                    $stmtTratamiento = $pdo->prepare($sqlTratamiento);
                    $stmtTratamiento->execute([
                        $datos['tratamiento']['medicacion'],
                        $datos['tratamiento']['dosis'],
                        $datos['tratamiento']['frecuencia'],
                        $datos['tratamiento']['duracion'],
                        $idreceta
                    ]);
                }
            }
            
            $pdo->commit();
            
            return [
                'status' => true,
                'mensaje' => $mensaje,
                'idreceta' => $idreceta
            ];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return [
                'status' => false,
                'mensaje' => 'Error al gestionar receta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Registra un triaje para una consulta
     * @param array $datos Datos del triaje
     * @return array Resultado de la operación
     */
    public function registrarTriaje($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();
            
            // Validar datos requeridos
            if (!isset($datos['idconsulta']) || empty($datos['idconsulta'])) {
                return [
                    'status' => false,
                    'mensaje' => 'ID de consulta no proporcionado'
                ];
            }
            
            if (!isset($datos['idenfermera']) || empty($datos['idenfermera'])) {
                return [
                    'status' => false,
                    'mensaje' => 'ID de enfermera no proporcionado'
                ];
            }
            
            // Verificar si ya existe un triaje para esta consulta
            $sqlCheck = "SELECT idtriaje FROM triajes WHERE idconsulta = ?";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([$datos['idconsulta']]);
            $triajeExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($triajeExistente) {
                // Actualizar triaje existente
                $sql = "UPDATE triajes SET 
                    idenfermera = ?,
                    hora = ?,
                    temperatura = ?,
                    presionarterial = ?,
                    frecuenciacardiaca = ?,
                    saturacionoxigeno = ?,
                    peso = ?,
                    estatura = ?
                WHERE idconsulta = ?";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $datos['idenfermera'],
                    $datos['hora'],
                    $datos['temperatura'] ?? null,
                    $datos['presionarterial'] ?? null,
                    $datos['frecuenciacardiaca'] ?? null,
                    $datos['saturacionoxigeno'] ?? null,
                    $datos['peso'] ?? null,
                    $datos['estatura'] ?? null,
                    $datos['idconsulta']
                ]);
                
                $idtriaje = $triajeExistente['idtriaje'];
                $mensaje = 'Triaje actualizado correctamente';
            } else {
                // Crear nuevo triaje
                $sql = "INSERT INTO triajes (
                    idconsulta,
                    idenfermera,
                    hora,
                    temperatura,
                    presionarterial,
                    frecuenciacardiaca,
                    saturacionoxigeno,
                    peso,
                    estatura
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $datos['idconsulta'],
                    $datos['idenfermera'],
                    $datos['hora'],
                    $datos['temperatura'] ?? null,
                    $datos['presionarterial'] ?? null,
                    $datos['frecuenciacardiaca'] ?? null,
                    $datos['saturacionoxigeno'] ?? null,
                    $datos['peso'] ?? null,
                    $datos['estatura'] ?? null
                ]);
                
                $idtriaje = $pdo->lastInsertId();
                $mensaje = 'Triaje registrado correctamente';
            }
            
            $pdo->commit();
            
            return [
                'status' => true,
                'mensaje' => $mensaje,
                'idtriaje' => $idtriaje
            ];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return [
                'status' => false,
                'mensaje' => 'Error al registrar triaje: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Asigna un diagnóstico a una consulta
     * @param array $datos Datos del diagnóstico
     * @return array Resultado de la operación
     */
    public function asignarDiagnostico($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            
            // Validar datos requeridos
            if (!isset($datos['idconsulta']) || empty($datos['idconsulta'])) {
                return [
                    'status' => false,
                    'mensaje' => 'ID de consulta no proporcionado'
                ];
            }
            
            if (!isset($datos['iddiagnostico']) || empty($datos['iddiagnostico'])) {
                return [
                    'status' => false,
                    'mensaje' => 'ID de diagnóstico no proporcionado'
                ];
            }
            
            // Actualizar la consulta con el diagnóstico
            $sql = "UPDATE consultas SET iddiagnostico = ? WHERE idconsulta = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$datos['iddiagnostico'], $datos['idconsulta']]);
            
            return [
                'status' => true,
                'mensaje' => 'Diagnóstico asignado correctamente',
                'idconsulta' => $datos['idconsulta']
            ];
        } catch (PDOException $e) {
            return [
                'status' => false,
                'mensaje' => 'Error al asignar diagnóstico: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Solicita un servicio para una consulta (laboratorio, imagen, etc.)
     * @param array $datos Datos del servicio
     * @return array Resultado de la operación
     */
    public function solicitarServicio($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            
            // Validar datos requeridos
            if (!isset($datos['idconsulta']) || empty($datos['idconsulta'])) {
                return [
                    'status' => false,
                    'mensaje' => 'ID de consulta no proporcionado'
                ];
            }
            
            if (!isset($datos['idtiposervicio']) || empty($datos['idtiposervicio'])) {
                return [
                    'status' => false,
                    'mensaje' => 'ID de tipo de servicio no proporcionado'
                ];
            }
            
            // Insertar solicitud de servicio
            $sql = "INSERT INTO serviciosrequeridos (
                idconsulta,
                idtiposervicio,
                solicitud,
                fechaanalisis,
                fechaprocesamiento,
                fechaentrega
            ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $datos['idconsulta'],
                $datos['idtiposervicio'],
                $datos['solicitud'] ?? null,
                $datos['fechaanalisis'] ?? null,
                $datos['fechaprocesamiento'] ?? null,
                $datos['fechaentrega'] ?? null
            ]);
            
            $idserviciorequerido = $pdo->lastInsertId();
            
            return [
                'status' => true,
                'mensaje' => 'Servicio solicitado correctamente',
                'idserviciorequerido' => $idserviciorequerido
            ];
        } catch (PDOException $e) {
            return [
                'status' => false,
                'mensaje' => 'Error al solicitar servicio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Registra resultados para un servicio requerido
     * @param array $datos Datos del resultado
     * @return array Resultado de la operación
     */
    public function registrarResultadoServicio($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            
            // Validar datos requeridos
            if (!isset($datos['idserviciorequerido']) || empty($datos['idserviciorequerido'])) {
                return [
                    'status' => false,
                    'mensaje' => 'ID de servicio requerido no proporcionado'
                ];
            }
            
            if (!isset($datos['caracteristicaevaluada']) || empty($datos['caracteristicaevaluada'])) {
                return [
                    'status' => false,
                    'mensaje' => 'Característica evaluada no proporcionada'
                ];
            }
            
            // Insertar resultado
            $sql = "INSERT INTO resultados (
                idserviciorequerido,
                caracteristicaevaluada,
                condicion,
                rutaimagen
            ) VALUES (?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $datos['idserviciorequerido'],
                $datos['caracteristicaevaluada'],
                $datos['condicion'] ?? null,
                $datos['rutaimagen'] ?? null
            ]);
            
            $idresultado = $pdo->lastInsertId();
            
            return [
                'status' => true,
                'mensaje' => 'Resultado registrado correctamente',
                'idresultado' => $idresultado
            ];
        } catch (PDOException $e) {
            return [
                'status' => false,
                'mensaje' => 'Error al registrar resultado: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene las consultas de un paciente específico
     * @param int $idpaciente ID del paciente
     * @return array Lista de consultas del paciente
     */
    public function consultasPorPaciente($idpaciente)
    {
        try {
            $pdo = $this->conexion->getConexion();
            
            $sql = "SELECT 
                c.idconsulta,
                c.fecha,
                TIME_FORMAT(c.horaprogramada, '%H:%i') as horaprogramada,
                TIME_FORMAT(c.horaatencion, '%H:%i') as horaatencion,
                c.condicionpaciente,
                
                d.nombre as diagnostico_nombre,
                
                CONCAT(doc.apellidos, ', ', doc.nombres) as doctor_nombre,
                
                e.especialidad
                
            FROM consultas c
            LEFT JOIN diagnosticos d ON c.iddiagnostico = d.iddiagnostico
            INNER JOIN horarios h ON c.idhorario = h.idhorario
            INNER JOIN atenciones a ON h.idatencion = a.idatencion
            INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
            INNER JOIN colaboradores col ON ct.idcolaborador = col.idcolaborador
            INNER JOIN personas doc ON col.idpersona = doc.idpersona
            INNER JOIN especialidades e ON col.idespecialidad = e.idespecialidad
            WHERE c.idpaciente = ?
            ORDER BY c.fecha DESC, c.horaprogramada DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idpaciente]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener consultas por paciente: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el historial médico completo de un paciente
     * @param int $idpaciente ID del paciente
     * @return array Historial médico del paciente
     */
    public function obtenerHistorialMedico($idpaciente)
    {
        try {
            $pdo = $this->conexion->getConexion();
            
            // Datos básicos del paciente
            $sqlPaciente = "SELECT 
                p.idpersona,
                p.apellidos,
                p.nombres,
                p.tipodoc,
                p.nrodoc,
                p.telefono,
                p.fechanacimiento,
                p.genero,
                p.direccion,
                p.email,
                pac.fecharegistro,
                TIMESTAMPDIFF(YEAR, p.fechanacimiento, CURDATE()) as edad
            FROM pacientes pac
            INNER JOIN personas p ON pac.idpersona = p.idpersona
            WHERE pac.idpaciente = ?";
            
            $stmtPaciente = $pdo->prepare($sqlPaciente);
            $stmtPaciente->execute([$idpaciente]);
            $paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);
            
            if (!$paciente) {
                return null;
            }
            
            // Alergias del paciente
            $sqlAlergias = "SELECT 
                la.idlistaalergia,
                la.gravedad,
                a.tipoalergia,
                a.alergia
            FROM listaalergias la
            INNER JOIN alergias a ON la.idalergia = a.idalergia
            WHERE la.idpersona = ?";
            
            $stmtAlergias = $pdo->prepare($sqlAlergias);
            $stmtAlergias->execute([$paciente['idpersona']]);
            $paciente['alergias'] = $stmtAlergias->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
            // Historial de consultas
            $sqlConsultas = "SELECT 
                c.idconsulta,
                c.fecha,
                TIME_FORMAT(c.horaprogramada, '%H:%i') as horaprogramada,
                TIME_FORMAT(c.horaatencion, '%H:%i') as horaatencion,
                c.condicionpaciente,
                
                d.nombre as diagnostico_nombre,
                d.descripcion as diagnostico_descripcion,
                
                CONCAT(doc.apellidos, ', ', doc.nombres) as doctor_nombre,
                
                e.especialidad
                
            FROM consultas c
            LEFT JOIN diagnosticos d ON c.iddiagnostico = d.iddiagnostico
            INNER JOIN horarios h ON c.idhorario = h.idhorario
            INNER JOIN atenciones a ON h.idatencion = a.idatencion
            INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
            INNER JOIN colaboradores col ON ct.idcolaborador = col.idcolaborador
            INNER JOIN personas doc ON col.idpersona = doc.idpersona
            INNER JOIN especialidades e ON col.idespecialidad = e.idespecialidad
            WHERE c.idpaciente = ?
            ORDER BY c.fecha DESC, c.horaprogramada DESC";
            
            $stmtConsultas = $pdo->prepare($sqlConsultas);
            $stmtConsultas->execute([$idpaciente]);
            $consultas = $stmtConsultas->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
            // Para cada consulta, obtener triaje, receta y servicios
            foreach ($consultas as $key => $consulta) {
                // Triaje
                $sqlTriaje = "SELECT 
                    t.idtriaje,
                    t.temperatura,
                    t.presionarterial,
                    t.frecuenciacardiaca,
                    t.saturacionoxigeno,
                    t.peso,
                    t.estatura
                FROM triajes t
                WHERE t.idconsulta = ?";
                
                $stmtTriaje = $pdo->prepare($sqlTriaje);
                $stmtTriaje->execute([$consulta['idconsulta']]);
                $consultas[$key]['triaje'] = $stmtTriaje->fetch(PDO::FETCH_ASSOC) ?: null;
                
                // Receta
                $sqlReceta = "SELECT 
                    r.idreceta,
                    r.medicacion,
                    r.cantidad,
                    r.frecuencia
                FROM recetas r
                WHERE r.idconsulta = ?";
                
                $stmtReceta = $pdo->prepare($sqlReceta);
                $stmtReceta->execute([$consulta['idconsulta']]);
                $consultas[$key]['receta'] = $stmtReceta->fetch(PDO::FETCH_ASSOC) ?: null;
                
                // Servicios
                $sqlServicios = "SELECT 
                    sr.idserviciorequerido,
                    ts.tiposervicio,
                    ts.servicio,
                    sr.solicitud
                FROM serviciosrequeridos sr
                INNER JOIN tiposervicio ts ON sr.idtiposervicio = ts.idtiposervicio
                WHERE sr.idconsulta = ?";
                
                $stmtServicios = $pdo->prepare($sqlServicios);
                $stmtServicios->execute([$consulta['idconsulta']]);
                $consultas[$key]['servicios'] = $stmtServicios->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }
            
            // Incluir las consultas en el resultado
            $paciente['consultas'] = $consultas;
            
            // Historia clínica si existe
            $sqlHistoria = "SELECT 
                hc.idhistoriaclinica,
                hc.antecedentepersonales,
                hc.enfermedadactual,
                hc.examenfisico,
                hc.evolucion,
                hc.altamedica,
                d.nombre as diagnostico_nombre
            FROM historiaclinica hc
            LEFT JOIN diagnosticos d ON hc.iddiagnostico = d.iddiagnostico
            WHERE hc.idhistoriaclinica = ?";
            
            $stmtHistoria = $pdo->prepare($sqlHistoria);
            $stmtHistoria->execute([$idpaciente]);
            $paciente['historiaclinica'] = $stmtHistoria->fetch(PDO::FETCH_ASSOC) ?: null;
            
            return $paciente;
        } catch (PDOException $e) {
            error_log('Error al obtener historial médico: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene estadísticas de consultas según diferentes criterios
     * @param array $filtros Filtros para las estadísticas
     * @return array Estadísticas de consultas
     */
    public function estadisticasConsultas($filtros = [])
    {
        try {
            $pdo = $this->conexion->getConexion();
            $resultado = [];
            
            // Consultas por especialidad
            $sqlEspecialidad = "SELECT 
                e.especialidad,
                COUNT(c.idconsulta) as total
            FROM consultas c
            INNER JOIN horarios h ON c.idhorario = h.idhorario
            INNER JOIN atenciones a ON h.idatencion = a.idatencion
            INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
            INNER JOIN colaboradores col ON ct.idcolaborador = col.idcolaborador
            INNER JOIN especialidades e ON col.idespecialidad = e.idespecialidad
            WHERE 1=1";
            
            $params = [];
            
            // Aplicar filtros de fecha
            if (isset($filtros['fecha_inicio']) && !empty($filtros['fecha_inicio'])) {
                $sqlEspecialidad .= " AND c.fecha >= ?";
                $params[] = $filtros['fecha_inicio'];
            }
            
            if (isset($filtros['fecha_fin']) && !empty($filtros['fecha_fin'])) {
                $sqlEspecialidad .= " AND c.fecha <= ?";
                $params[] = $filtros['fecha_fin'];
            }
            
            $sqlEspecialidad .= " GROUP BY e.especialidad ORDER BY total DESC";
            
            $stmtEspecialidad = $pdo->prepare($sqlEspecialidad);
            $stmtEspecialidad->execute($params);
            $resultado['por_especialidad'] = $stmtEspecialidad->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
            // Consultas por doctor
            $sqlDoctor = "SELECT 
                CONCAT(p.apellidos, ', ', p.nombres) as doctor,
                COUNT(c.idconsulta) as total
            FROM consultas c
            INNER JOIN horarios h ON c.idhorario = h.idhorario
            INNER JOIN atenciones a ON h.idatencion = a.idatencion
            INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
            INNER JOIN colaboradores col ON ct.idcolaborador = col.idcolaborador
            INNER JOIN personas p ON col.idpersona = p.idpersona
            WHERE 1=1";
            
            // Aplicar los mismos filtros
            $sqlDoctor .= str_replace("1=1", "1=1", $sqlEspecialidad);
            $sqlDoctor = preg_replace("/GROUP BY.*/", "GROUP BY p.idpersona ORDER BY total DESC", $sqlDoctor);
            
            $stmtDoctor = $pdo->prepare($sqlDoctor);
            $stmtDoctor->execute($params);
            $resultado['por_doctor'] = $stmtDoctor->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
            // Consultas por día de la semana
            $sqlDiaSemana = "SELECT 
                DAYNAME(c.fecha) as dia_semana,
                COUNT(c.idconsulta) as total
            FROM consultas c
            WHERE 1=1";
            
            if (isset($filtros['fecha_inicio']) && !empty($filtros['fecha_inicio'])) {
                $sqlDiaSemana .= " AND c.fecha >= ?";
            }
            
            if (isset($filtros['fecha_fin']) && !empty($filtros['fecha_fin'])) {
                $sqlDiaSemana .= " AND c.fecha <= ?";
            }
            
            $sqlDiaSemana .= " GROUP BY dia_semana ORDER BY DAYOFWEEK(c.fecha)";
            
            $stmtDiaSemana = $pdo->prepare($sqlDiaSemana);
            $stmtDiaSemana->execute($params);
            $resultado['por_dia_semana'] = $stmtDiaSemana->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
            // Diagnósticos más frecuentes
            $sqlDiagnosticos = "SELECT 
                d.nombre as diagnostico,
                COUNT(c.idconsulta) as total
            FROM consultas c
            INNER JOIN diagnosticos d ON c.iddiagnostico = d.iddiagnostico
            WHERE c.iddiagnostico IS NOT NULL";
            
            if (isset($filtros['fecha_inicio']) && !empty($filtros['fecha_inicio'])) {
                $sqlDiagnosticos .= " AND c.fecha >= ?";
            }
            
            if (isset($filtros['fecha_fin']) && !empty($filtros['fecha_fin'])) {
                $sqlDiagnosticos .= " AND c.fecha <= ?";
            }
            
            $sqlDiagnosticos .= " GROUP BY d.iddiagnostico ORDER BY total DESC LIMIT 10";
            
            $stmtDiagnosticos = $pdo->prepare($sqlDiagnosticos);
            $stmtDiagnosticos->execute($params);
            $resultado['diagnosticos_frecuentes'] = $stmtDiagnosticos->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
            // Total de consultas en el período
            $sqlTotal = "SELECT COUNT(*) as total FROM consultas c WHERE 1=1";
            
            if (isset($filtros['fecha_inicio']) && !empty($filtros['fecha_inicio'])) {
                $sqlTotal .= " AND c.fecha >= ?";
            }
            
            if (isset($filtros['fecha_fin']) && !empty($filtros['fecha_fin'])) {
                $sqlTotal .= " AND c.fecha <= ?";
            }
            
            $stmtTotal = $pdo->prepare($sqlTotal);
            $stmtTotal->execute($params);
            $totalConsultas = $stmtTotal->fetch(PDO::FETCH_ASSOC);
            
            $resultado['total_consultas'] = $totalConsultas['total'] ?? 0;
            
            return $resultado;
        } catch (PDOException $e) {
            error_log('Error al obtener estadísticas: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualiza el estado de una historia clínica (alta médica)
     * @param int $idhistoriaclinica ID de la historia clínica
     * @param bool $altamedica Estado de alta médica
     * @return array Resultado de la operación
     */
    public function actualizarAltaMedica($idhistoriaclinica, $altamedica)
    {
        try {
            $pdo = $this->conexion->getConexion();
            
            $sql = "UPDATE historiaclinica SET altamedica = ? WHERE idhistoriaclinica = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$altamedica ? 1 : 0, $idhistoriaclinica]);
            
            return [
                'status' => true,
                'mensaje' => 'Estado de alta médica actualizado correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'status' => false,
                'mensaje' => 'Error al actualizar estado de alta médica: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Crea o actualiza una historia clínica para un paciente
     * @param array $datos Datos de la historia clínica
     * @return array Resultado de la operación
     */
    public function gestionarHistoriaClinica($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();
            
            // Verificar si ya existe una historia clínica para este paciente
            $sqlCheck = "SELECT idhistoriaclinica FROM historiaclinica WHERE idhistoriaclinica = ?";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([$datos['idpaciente']]);
            $historiaExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($historiaExistente) {
                // Actualizar historia existente
                $sql = "UPDATE historiaclinica SET 
                    antecedentepersonales = COALESCE(?, antecedentepersonales),
                    enfermedadactual = COALESCE(?, enfermedadactual),
                    examenfisico = COALESCE(?, examenfisico),
                    evolucion = COALESCE(?, evolucion),
                    altamedica = COALESCE(?, altamedica),
                    iddiagnostico = COALESCE(?, iddiagnostico),
                    idtratamiento = COALESCE(?, idtratamiento)
                WHERE idhistoriaclinica = ?";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $datos['antecedentepersonales'] ?? null,
                    $datos['enfermedadactual'] ?? null,
                    $datos['examenfisico'] ?? null,
                    $datos['evolucion'] ?? null,
                    $datos['altamedica'] ?? null,
                    $datos['iddiagnostico'] ?? null,
                    $datos['idtratamiento'] ?? null,
                    $datos['idpaciente']
                ]);
                
                $mensaje = 'Historia clínica actualizada correctamente';
            } else {
                // Crear nueva historia clínica
                $sql = "INSERT INTO historiaclinica (
                    idhistoriaclinica,
                    antecedentepersonales,
                    enfermedadactual,
                    examenfisico,
                    evolucion,
                    altamedica,
                    iddiagnostico,
                    idtratamiento
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $datos['idpaciente'],
                    $datos['antecedentepersonales'] ?? null,
                    $datos['enfermedadactual'] ?? null,
                    $datos['examenfisico'] ?? null,
                    $datos['evolucion'] ?? null,
                    $datos['altamedica'] ?? false,
                    $datos['iddiagnostico'] ?? null,
                    $datos['idtratamiento'] ?? null
                ]);
                
                $mensaje = 'Historia clínica creada correctamente';
            }
            
            $pdo->commit();
            
            return [
                'status' => true,
                'mensaje' => $mensaje,
                'idhistoriaclinica' => $datos['idpaciente']
            ];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return [
                'status' => false,
                'mensaje' => 'Error al gestionar historia clínica: ' . $e->getMessage()
            ];
        }
    }
}