<?php /*RUTA: sistemaclinica/models/Doctor.php*/?>
<?php

require_once 'Conexion.php';

class Doctor
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    /**
     * Verifica si un número de documento ya está registrado como doctor
     * @param string $nroDoc Número de documento a verificar
     * @return boolean True si el documento existe, False en caso contrario
     */
    public function verificarDocumentoDoctor($nroDoc)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $stmt = $pdo->prepare("CALL sp_verificar_documento_doctor(?, @existe)");
            $stmt->execute([$nroDoc]);
            $stmt->closeCursor();

            $result = $pdo->query("SELECT @existe AS existe")->fetch(PDO::FETCH_ASSOC);
            return (bool)$result['existe'];
        } catch (Exception $e) {
            die("Error al verificar documento: " . $e->getMessage());
        }
    }

    /**
     * Registra la información personal y profesional de un doctor en un solo proceso
     * @param array $datos Datos personales y profesionales del doctor
     * @return array Resultado de la operación y mensaje
     */
    public function registrarDoctor($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // Verificar si la persona ya existe
            $stmtVerificar = $pdo->prepare("SELECT idpersona FROM personas WHERE nrodoc = ?");
            $stmtVerificar->execute([$datos['nrodoc']]);
            $personaExistente = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
            $idpersona = null;

            if ($personaExistente) {
                // La persona ya existe, usar su ID
                $idpersona = $personaExistente['idpersona'];
            } else {
                // Insertar nueva persona
                $stmtPersona = $pdo->prepare("
                    INSERT INTO personas (
                        apellidos, nombres, tipodoc, nrodoc, 
                        telefono, fechanacimiento, genero, 
                        direccion, email
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmtPersona->execute([
                    $datos['apellidos'],
                    $datos['nombres'],
                    $datos['tipodoc'],
                    $datos['nrodoc'],
                    $datos['telefono'],
                    $datos['fechanacimiento'],
                    $datos['genero'],
                    $datos['direccion'],
                    $datos['email']
                ]);

                $idpersona = $pdo->lastInsertId();
            }

            // Verificar si ya existe como colaborador
            $stmtVerificarColaborador = $pdo->prepare("
                SELECT idcolaborador FROM colaboradores WHERE idpersona = ?
            ");
            $stmtVerificarColaborador->execute([$idpersona]);
            $colaboradorExistente = $stmtVerificarColaborador->fetch(PDO::FETCH_ASSOC);
            $idcolaborador = null;

            if ($colaboradorExistente) {
                // Actualizar colaborador existente
                $stmtColaborador = $pdo->prepare("
                    UPDATE colaboradores 
                    SET idespecialidad = ?
                    WHERE idcolaborador = ?
                ");

                $stmtColaborador->execute([
                    $datos['idespecialidad'],
                    $colaboradorExistente['idcolaborador']
                ]);

                $idcolaborador = $colaboradorExistente['idcolaborador'];
            } else {
                // Insertar nuevo colaborador
                $stmtColaborador = $pdo->prepare("
                    INSERT INTO colaboradores (idpersona, idespecialidad)
                    VALUES (?, ?)
                ");

                $stmtColaborador->execute([
                    $idpersona,
                    $datos['idespecialidad']
                ]);

                $idcolaborador = $pdo->lastInsertId();
            }

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Doctor registrado correctamente',
                'idpersona' => $idpersona,
                'idcolaborador' => $idcolaborador
            ];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'status' => false,
                'mensaje' => "Error al registrar doctor: " . $e->getMessage(),
                'idpersona' => null,
                'idcolaborador' => null
            ];
        }
    }

    /**
     * Actualiza la información de contrato de un doctor
     * @param array $datos Datos del contrato
     * @return array Resultado de la operación
     */
    public function actualizarInfoContrato($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // Si el ID de contrato es 0, es un nuevo contrato
            if ($datos['idcontrato'] <= 0) {
                // Insertar nuevo contrato
                $sqlInsert = "
                INSERT INTO contratos (
                    idcolaborador, fechainicio, fechafin, tipocontrato, estado
                ) VALUES (?, ?, ?, ?, ?)
            ";

                $stmtInsert = $pdo->prepare($sqlInsert);
                $stmtInsert->execute([
                    $datos['idcolaborador'],
                    $datos['fechainicio'],
                    $datos['fechafin'],
                    $datos['tipocontrato'],
                    $datos['estado']
                ]);

                $idcontrato = $pdo->lastInsertId();
            } else {
                // Actualizar contrato existente
                $sqlUpdate = "
                UPDATE contratos 
                SET fechainicio = ?,
                    fechafin = ?,
                    tipocontrato = ?,
                    estado = ?
                WHERE idcontrato = ?
            ";

                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->execute([
                    $datos['fechainicio'],
                    $datos['fechafin'],
                    $datos['tipocontrato'],
                    $datos['estado'],
                    $datos['idcontrato']
                ]);

                $idcontrato = $datos['idcontrato'];
            }

            // Actualizar también el estado del colaborador/doctor si estamos modificando un contrato
            // que coincide con el estado del colaborador (normalmente el contrato activo actual)
            $sqlUpdateColaborador = "
            UPDATE colaboradores 
            SET estado = ? 
            WHERE idcolaborador = ?
        ";

            $stmtUpdateColaborador = $pdo->prepare($sqlUpdateColaborador);
            $stmtUpdateColaborador->execute([
                $datos['estado'],
                $datos['idcolaborador']
            ]);

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Información de contrato y estado del doctor actualizados correctamente',
                'idcontrato' => $idcontrato
            ];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'status' => false,
                'mensaje' => 'Error al actualizar información de contrato: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina un doctor por su número de documento
     * @param string $nrodoc Número de documento del doctor
     * @return array Resultado de la operación
     */
    public function eliminarDoctor($nrodoc)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // 1. Obtener los IDs necesarios
            $stmtIds = $pdo->prepare("
            SELECT p.idpersona, c.idcolaborador
            FROM personas p
            INNER JOIN colaboradores c ON p.idpersona = c.idpersona
            WHERE p.nrodoc = ?
        ");
            $stmtIds->execute([$nrodoc]);
            $ids = $stmtIds->fetch(PDO::FETCH_ASSOC);

            if (!$ids) {
                return [
                    'status' => false,
                    'mensaje' => 'No se encontró el doctor con el documento especificado'
                ];
            }

            // 2. Verificar dependencias (por ejemplo, si el doctor tiene consultas)
            $stmtConsultas = $pdo->prepare("
            SELECT COUNT(*) as total FROM consultas c
            INNER JOIN horarios h ON c.idhorario = h.idhorario
            INNER JOIN atenciones a ON h.idatencion = a.idatencion
            INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
            WHERE ct.idcolaborador = ?
        ");
            $stmtConsultas->execute([$ids['idcolaborador']]);
            $totalConsultas = $stmtConsultas->fetch(PDO::FETCH_ASSOC)['total'];

            if ($totalConsultas > 0) {
                return [
                    'status' => false,
                    'mensaje' => 'No se puede eliminar el doctor porque tiene consultas registradas. Considere inactivarlo en su lugar.'
                ];
            }

            // 3. Eliminar dependencias en cascada
            // Eliminar credenciales (usuarios)
            $stmtUsuarios = $pdo->prepare("
            DELETE u FROM usuarios u
            INNER JOIN contratos c ON u.idcontrato = c.idcontrato
            WHERE c.idcolaborador = ?
        ");
            $stmtUsuarios->execute([$ids['idcolaborador']]);

            // Eliminar horarios y atenciones
            $stmtHorarios = $pdo->prepare("
            DELETE h FROM horarios h
            INNER JOIN atenciones a ON h.idatencion = a.idatencion
            INNER JOIN contratos c ON a.idcontrato = c.idcontrato
            WHERE c.idcolaborador = ?
        ");
            $stmtHorarios->execute([$ids['idcolaborador']]);

            $stmtAtenciones = $pdo->prepare("
            DELETE a FROM atenciones a
            INNER JOIN contratos c ON a.idcontrato = c.idcontrato
            WHERE c.idcolaborador = ?
        ");
            $stmtAtenciones->execute([$ids['idcolaborador']]);

            // Eliminar contratos
            $stmtContratos = $pdo->prepare("
            DELETE FROM contratos WHERE idcolaborador = ?
        ");
            $stmtContratos->execute([$ids['idcolaborador']]);

            // Eliminar colaborador
            $stmtColaborador = $pdo->prepare("
            DELETE FROM colaboradores WHERE idcolaborador = ?
        ");
            $stmtColaborador->execute([$ids['idcolaborador']]);

            // No eliminamos la persona porque podría tener relación con otras entidades
            // En su lugar, actualizamos el estado o marcamos como eliminado
            $stmtPersona = $pdo->prepare("
            UPDATE personas SET estado = 'INACTIVO' WHERE idpersona = ?
        ");
            $stmtPersona->execute([$ids['idpersona']]);

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Doctor eliminado correctamente'
            ];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'status' => false,
                'mensaje' => 'Error al eliminar doctor: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina completamente a un doctor y todos sus registros asociados
     * @param string $nrodoc Número de documento del doctor
     * @return array Resultado de la operación
     */
    public function eliminarDoctorCompleto($nrodoc)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // 1. Obtener los IDs necesarios
            $stmtIds = $pdo->prepare("
            SELECT p.idpersona, c.idcolaborador
            FROM personas p
            INNER JOIN colaboradores c ON p.idpersona = c.idpersona
            WHERE p.nrodoc = ?
        ");
            $stmtIds->execute([$nrodoc]);
            $ids = $stmtIds->fetch(PDO::FETCH_ASSOC);

            if (!$ids) {
                return [
                    'status' => false,
                    'mensaje' => 'No se encontró el doctor con el documento especificado'
                ];
            }

            // 2. Identificar todas las consultas asociadas al doctor
            $stmtConsultas = $pdo->prepare("
            SELECT c.idconsulta
            FROM consultas c
            INNER JOIN horarios h ON c.idhorario = h.idhorario
            INNER JOIN atenciones a ON h.idatencion = a.idatencion
            INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
            WHERE ct.idcolaborador = ?
        ");
            $stmtConsultas->execute([$ids['idcolaborador']]);
            $consultas = $stmtConsultas->fetchAll(PDO::FETCH_COLUMN);

            // 3. Eliminar registros de detalleventas asociados a las consultas
            if (!empty($consultas)) {
                $placeholders = str_repeat('?,', count($consultas) - 1) . '?';
                $stmtEliminarDetalleVentas = $pdo->prepare("
                DELETE FROM detalleventas 
                WHERE idconsulta IN ($placeholders)
            ");
                $stmtEliminarDetalleVentas->execute($consultas);
            }

            // 4. Eliminar registros en triajes
            $stmtEliminarTriajes = $pdo->prepare("
            DELETE FROM triajes 
            WHERE idenfermera = ? OR idconsulta IN (
                SELECT c.idconsulta
                FROM consultas c
                INNER JOIN horarios h ON c.idhorario = h.idhorario
                INNER JOIN atenciones a ON h.idatencion = a.idatencion
                INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
                WHERE ct.idcolaborador = ?
            )
        ");
            $stmtEliminarTriajes->execute([$ids['idcolaborador'], $ids['idcolaborador']]);

            // 5. Eliminar tratamientos y recetas asociados a las consultas
            if (!empty($consultas)) {
                $placeholders = str_repeat('?,', count($consultas) - 1) . '?';

                // 5.1 Identificar recetas asociadas a las consultas
                $stmtRecetas = $pdo->prepare("
                SELECT idreceta 
                FROM recetas 
                WHERE idconsulta IN ($placeholders)
            ");
                $stmtRecetas->execute($consultas);
                $recetas = $stmtRecetas->fetchAll(PDO::FETCH_COLUMN);

                // 5.2 Identificar tratamientos asociados a esas recetas
                if (!empty($recetas)) {
                    $placeholdersRecetas = str_repeat('?,', count($recetas) - 1) . '?';
                    $stmtTratamientos = $pdo->prepare("
                    SELECT idtratamiento
                    FROM tratamiento
                    WHERE idreceta IN ($placeholdersRecetas)
                ");
                    $stmtTratamientos->execute($recetas);
                    $tratamientos = $stmtTratamientos->fetchAll(PDO::FETCH_COLUMN);

                    // 5.3 Eliminar historias clínicas que referencian a esos tratamientos
                    if (!empty($tratamientos)) {
                        $placeholdersTratamientos = str_repeat('?,', count($tratamientos) - 1) . '?';
                        $stmtEliminarHistoriaClinica = $pdo->prepare("
                        UPDATE historiaclinica
                        SET idtratamiento = NULL
                        WHERE idtratamiento IN ($placeholdersTratamientos)
                    ");
                        $stmtEliminarHistoriaClinica->execute($tratamientos);
                    }

                    // 5.4 Eliminar tratamientos
                    $stmtEliminarTratamientos = $pdo->prepare("
                    DELETE FROM tratamiento 
                    WHERE idreceta IN ($placeholdersRecetas)
                ");
                    $stmtEliminarTratamientos->execute($recetas);
                }

                // 5.5 Eliminar recetas
                $stmtEliminarRecetas = $pdo->prepare("
                DELETE FROM recetas 
                WHERE idconsulta IN ($placeholders)
            ");
                $stmtEliminarRecetas->execute($consultas);
            }

            // 6. Eliminar servicios requeridos y sus resultados asociados
            if (!empty($consultas)) {
                // 6.1 Identificar servicios requeridos
                $placeholders = str_repeat('?,', count($consultas) - 1) . '?';
                $stmtServiciosRequeridos = $pdo->prepare("
                SELECT idserviciorequerido 
                FROM serviciosrequeridos 
                WHERE idconsulta IN ($placeholders)
            ");
                $stmtServiciosRequeridos->execute($consultas);
                $serviciosRequeridos = $stmtServiciosRequeridos->fetchAll(PDO::FETCH_COLUMN);

                // 6.2 Eliminar resultados de servicios
                if (!empty($serviciosRequeridos)) {
                    $placeholdersServicios = str_repeat('?,', count($serviciosRequeridos) - 1) . '?';
                    $stmtEliminarResultados = $pdo->prepare("
                    DELETE FROM resultados 
                    WHERE idserviciorequerido IN ($placeholdersServicios)
                ");
                    $stmtEliminarResultados->execute($serviciosRequeridos);
                }

                // 6.3 Eliminar detalleventas asociados a servicios requeridos
                if (!empty($serviciosRequeridos)) {
                    $placeholdersServicios = str_repeat('?,', count($serviciosRequeridos) - 1) . '?';
                    $stmtEliminarDetalleVentasServicios = $pdo->prepare("
                    DELETE FROM detalleventas 
                    WHERE idserviciorequerido IN ($placeholdersServicios)
                ");
                    $stmtEliminarDetalleVentasServicios->execute($serviciosRequeridos);
                }

                // 6.4 Eliminar servicios requeridos
                $stmtEliminarServiciosRequeridos = $pdo->prepare("
                DELETE FROM serviciosrequeridos 
                WHERE idconsulta IN ($placeholders)
            ");
                $stmtEliminarServiciosRequeridos->execute($consultas);
            }

            // 7. Eliminar consultas
            if (!empty($consultas)) {
                $placeholders = str_repeat('?,', count($consultas) - 1) . '?';
                $stmtEliminarConsultas = $pdo->prepare("
                DELETE FROM consultas 
                WHERE idconsulta IN ($placeholders)
            ");
                $stmtEliminarConsultas->execute($consultas);
            }

            // 8. Eliminar usuarios asociados a contratos
            $stmtEliminarUsuarios = $pdo->prepare("
            DELETE FROM usuarios 
            WHERE idcontrato IN (
                SELECT idcontrato 
                FROM contratos 
                WHERE idcolaborador = ?
            )
        ");
            $stmtEliminarUsuarios->execute([$ids['idcolaborador']]);

            // 9. Eliminar horarios asociados a atenciones del doctor
            $stmtEliminarHorarios = $pdo->prepare("
            DELETE FROM horarios 
            WHERE idatencion IN (
                SELECT a.idatencion 
                FROM atenciones a
                INNER JOIN contratos c ON a.idcontrato = c.idcontrato
                WHERE c.idcolaborador = ?
            )
        ");
            $stmtEliminarHorarios->execute([$ids['idcolaborador']]);

            // 10. Eliminar atenciones asociadas a contratos
            $stmtEliminarAtenciones = $pdo->prepare("
            DELETE FROM atenciones 
            WHERE idcontrato IN (
                SELECT idcontrato 
                FROM contratos 
                WHERE idcolaborador = ?
            )
        ");
            $stmtEliminarAtenciones->execute([$ids['idcolaborador']]);

            // 11. Eliminar contratos
            $stmtEliminarContratos = $pdo->prepare("
            DELETE FROM contratos 
            WHERE idcolaborador = ?
        ");
            $stmtEliminarContratos->execute([$ids['idcolaborador']]);

            // 12. Eliminar colaborador
            $stmtEliminarColaborador = $pdo->prepare("
            DELETE FROM colaboradores 
            WHERE idcolaborador = ?
        ");
            $stmtEliminarColaborador->execute([$ids['idcolaborador']]);

            // 13. Eliminar listaalergias asociadas al doctor (si existen)
            $stmtEliminarAlergias = $pdo->prepare("
            DELETE FROM listaalergias 
            WHERE idpersona = ?
        ");
            $stmtEliminarAlergias->execute([$ids['idpersona']]);

            // 14. Eliminar cliente asociado (si existe)
            $stmtEliminarCliente = $pdo->prepare("
            DELETE FROM clientes 
            WHERE idpersona = ?
        ");
            $stmtEliminarCliente->execute([$ids['idpersona']]);

            // 15. Eliminar paciente asociado (si existe)
            $stmtEliminarPaciente = $pdo->prepare("
            DELETE FROM pacientes 
            WHERE idpersona = ?
        ");
            $stmtEliminarPaciente->execute([$ids['idpersona']]);

            // 16. Eliminar citas asociadas
            $stmtEliminarCitas = $pdo->prepare("
            DELETE FROM citas 
            WHERE idpersona = ?
        ");
            $stmtEliminarCitas->execute([$ids['idpersona']]);

            // 17. Finalmente, eliminar a la persona
            $stmtEliminarPersona = $pdo->prepare("
            DELETE FROM personas 
            WHERE idpersona = ?
        ");
            $stmtEliminarPersona->execute([$ids['idpersona']]);

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Doctor eliminado completamente del sistema con todos sus registros asociados',
                'tipoOperacion' => 'eliminado_completo'
            ];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'status' => false,
                'mensaje' => 'Error al eliminar doctor: ' . $e->getMessage(),
                'codigoError' => $e->getCode()
            ];
        }
    }

    /**
     * Actualiza la información personal de un doctor
     * @param array $datos Datos actualizados del doctor
     * @return array Resultado de la operación
     */
    public function actualizarInfoPersonalDoctor($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // Actualizar datos de la persona
            $sqlPersona = "
                UPDATE personas 
                SET apellidos = ?, 
                    nombres = ?, 
                    telefono = ?, 
                    fechanacimiento = ?, 
                    genero = ?, 
                    direccion = ?, 
                    email = ?
                WHERE idpersona = ?
            ";

            $stmtPersona = $pdo->prepare($sqlPersona);
            $stmtPersona->execute([
                $datos['apellidos'],
                $datos['nombres'],
                $datos['telefono'],
                $datos['fechanacimiento'],
                $datos['genero'],
                $datos['direccion'],
                $datos['email'],
                $datos['idpersona']
            ]);

            // No necesitamos actualizar el colaborador aquí, solo los datos personales

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Información personal del doctor actualizada correctamente'
            ];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'status' => false,
                'mensaje' => 'Error al actualizar información personal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene la lista de todos los doctores
     * @return array Lista de doctores
     */
    public function listarDoctores()
    {
        try {
            $pdo = $this->conexion->getConexion();
            $stmt = $pdo->prepare("CALL sp_listar_doctores()");
            $stmt->execute();

            $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $doctores;
        } catch (Exception $e) {
            die("Error al listar doctores: " . $e->getMessage());
        }
    }



    /**
     * Busca un doctor por su número de documento
     * @param string $nroDoc Número de documento
     * @return array|null Datos del doctor o null si no existe
     */
    public function buscarDoctorPorDocumento($nroDoc)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $stmt = $pdo->prepare("CALL sp_buscar_doctor_por_documento(?)");
            $stmt->execute([$nroDoc]);

            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $doctor ?: null;
        } catch (Exception $e) {
            die("Error al buscar doctor: " . $e->getMessage());
        }
    }
    /**
     * Obtiene la información completa de un doctor por su número de documento
     * @param string $nroDoc Número de documento del doctor
     * @return array|null Información del doctor o null si no existe
     */
    public function obtenerDoctorPorNroDoc($nroDoc)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Consulta para obtener datos completos del doctor incluyendo especialidad
            $sql = "
                SELECT 
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
                    c.idcolaborador,
                    c.idespecialidad,
                    c.estado,
                    e.especialidad,
                    e.precioatencion
                FROM personas p
                INNER JOIN colaboradores c ON p.idpersona = c.idpersona
                INNER JOIN especialidades e ON c.idespecialidad = e.idespecialidad
                WHERE p.nrodoc = ?
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nroDoc]);

            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

            return $doctor ? $doctor : null;
        } catch (Exception $e) {
            // En producción, es mejor loguear el error que mostrarlo directamente
            error_log("Error al obtener doctor por nro doc: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Edita la información de un doctor existente
     * @param array $datos Datos actualizados del doctor
     * @return array Resultado de la operación
     */
    public function editarDoctor($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // 1. Primero obtenemos los IDs necesarios para la actualización
            $stmtIds = $pdo->prepare("
                SELECT p.idpersona, c.idcolaborador, c.idespecialidad
                FROM personas p
                INNER JOIN colaboradores c ON p.idpersona = c.idpersona
                WHERE p.nrodoc = ?
            ");
            $stmtIds->execute([$datos['nrodoc']]);
            $ids = $stmtIds->fetch(PDO::FETCH_ASSOC);

            if (!$ids) {
                return [
                    'status' => false,
                    'mensaje' => 'No se encontró el doctor con el documento especificado'
                ];
            }

            // 2. Actualizar datos de la persona
            $stmtPersona = $pdo->prepare("
                UPDATE personas 
                SET nombres = ?, 
                    apellidos = ?, 
                    telefono = ?, 
                    fechanacimiento = ?, 
                    genero = ?, 
                    direccion = ?, 
                    email = ?
                WHERE idpersona = ?
            ");

            $stmtPersona->execute([
                $datos['nombres'],
                $datos['apellidos'],
                $datos['telefono'],
                $datos['fechanac'],
                $datos['genero'],
                $datos['direccion'],
                $datos['email'],
                $ids['idpersona']
            ]);

            // 3. Actualizar datos del colaborador (incluido el estado)
            $stmtColaborador = $pdo->prepare("
                UPDATE colaboradores 
                SET idespecialidad = ?, 
                    estado = ?
                WHERE idcolaborador = ?
            ");

            // Obtener el ID de la especialidad (puede ser el ID o el nombre dependiendo del form)
            $idespecialidad = $datos['especialidad'];
            // Si no es un número, buscar el ID por nombre
            if (!is_numeric($idespecialidad)) {
                $stmtEsp = $pdo->prepare("SELECT idespecialidad FROM especialidades WHERE especialidad = ?");
                $stmtEsp->execute([$idespecialidad]);
                $espRow = $stmtEsp->fetch(PDO::FETCH_ASSOC);
                $idespecialidad = $espRow ? $espRow['idespecialidad'] : $ids['idespecialidad'];
            }

            $stmtColaborador->execute([
                $idespecialidad,
                $datos['estado'],
                $ids['idcolaborador']
            ]);

            // 4. Actualizar datos adicionales si existen (credenciales, biografía)
            // Esto podría estar en una tabla separada o en campos adicionales de colaboradores

            // 5. Actualizar foto si se ha proporcionado una nueva
            if (isset($datos['foto'])) {
                // Aquí iría el código para actualizar la foto en la base de datos
                // Por ejemplo:
                $stmtFoto = $pdo->prepare("
                    UPDATE colaboradores 
                    SET foto = ? 
                    WHERE idcolaborador = ?
                ");
                $stmtFoto->execute([$datos['foto'], $ids['idcolaborador']]);
            }

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Doctor actualizado correctamente'
            ];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'status' => false,
                'mensaje' => 'Error al actualizar doctor: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Actualiza los datos profesionales de un doctor
     * @param int $idcolaborador ID del colaborador a actualizar
     * @param int $idespecialidad ID de la especialidad
     * @param float $precioatencion Precio de atención
     * @return array Resultado de la operación
     */
    public function actualizarDatosProfesionales($idcolaborador, $idespecialidad, $precioatencion = null)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // Actualizar especialidad del colaborador
            $sql = "UPDATE colaboradores SET idespecialidad = ? WHERE idcolaborador = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idespecialidad, $idcolaborador]);

            // Si se proporciona un precio de atención personalizado, actualizarlo
            if ($precioatencion !== null) {
                // Aquí podría ir la lógica para manejar precios personalizados
                // por doctor que pueden diferir de los precios estándar de la especialidad
                // Por ejemplo, podría haber una tabla precios_doctores
            }

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Datos profesionales actualizados correctamente'
            ];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'status' => false,
                'mensaje' => 'Error al actualizar datos profesionales: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Actualiza los datos personales y profesionales de un doctor
     * @param array $datos Datos actualizados del doctor
     * @return array Resultado de la operación
     */
    public function actualizarDoctorCompleto($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // 1. Actualizar datos personales
            $sqlPersona = "
                UPDATE personas 
                SET apellidos = ?, 
                    nombres = ?, 
                    telefono = ?, 
                    fechanacimiento = ?, 
                    genero = ?, 
                    direccion = ?, 
                    email = ?
                WHERE idpersona = ?
            ";

            $stmtPersona = $pdo->prepare($sqlPersona);
            $stmtPersona->execute([
                $datos['apellidos'],
                $datos['nombres'],
                $datos['telefono'],
                $datos['fechanacimiento'],
                $datos['genero'],
                $datos['direccion'],
                $datos['email'],
                $datos['idpersona']
            ]);

            // 2. Actualizar datos profesionales
            $sqlColaborador = "
                UPDATE colaboradores 
                SET idespecialidad = ?
                WHERE idcolaborador = ?
            ";

            $stmtColaborador = $pdo->prepare($sqlColaborador);
            $stmtColaborador->execute([
                $datos['idespecialidad'],
                $datos['idcolaborador']
            ]);

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Doctor actualizado correctamente'
            ];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'status' => false,
                'mensaje' => 'Error al actualizar doctor: ' . $e->getMessage()
            ];
        }
    }

    public function cambiarEstado($nrodoc) {
        try {
            $conexion = $this->conexion->getConexion();
            $query = "CALL sp_cambiar_estado_doctor(?, @p_resultado, @p_mensaje)";
            
            $stmt = $conexion->prepare($query);
            $stmt->bindParam(1, $nrodoc, PDO::PARAM_STR);
            $stmt->execute();
            
            // Obtener variables de salida
            $stmt = $conexion->query("SELECT @p_resultado AS resultado, @p_mensaje AS mensaje");
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'status' => $resultado['resultado'] == 1,
                'mensaje' => $resultado['mensaje']
            ];
        } catch (PDOException $e) {
            return [
                'status' => false,
                'mensaje' => 'Error al cambiar el estado del doctor: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cambia el estado de un doctor (ACTIVO/INACTIVO) por número de documento
     * También actualiza el estado de sus contratos activos
     * @param string $nrodoc Número de documento del doctor
     * @return array Resultado de la operación
     */
    public function cambiarEstadoDoctor($nrodoc)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // Buscar el idcolaborador y estado actual
            $stmt = $pdo->prepare("
            SELECT c.idcolaborador, c.estado
            FROM colaboradores c
            INNER JOIN personas p ON c.idpersona = p.idpersona
            WHERE p.nrodoc = ?
            LIMIT 1
        ");
            $stmt->execute([$nrodoc]);
            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$doctor) {
                return [
                    'status' => false,
                    'mensaje' => 'No se encontró un doctor con el documento especificado'
                ];
            }

            // Determinar el nuevo estado (inverso al actual)
            $estadoActual = $doctor['estado'] ?: 'ACTIVO'; // Si es NULL, asumimos ACTIVO
            $nuevoEstado = ($estadoActual === 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';

            // Actualizar el estado del doctor
            $stmt = $pdo->prepare("UPDATE colaboradores SET estado = ? WHERE idcolaborador = ?");
            $stmt->execute([$nuevoEstado, $doctor['idcolaborador']]);

            // Actualizar también los contratos del doctor
            $stmt = $pdo->prepare("UPDATE contratos SET estado = ? WHERE idcolaborador = ?");
            $stmt->execute([$nuevoEstado, $doctor['idcolaborador']]);

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'El estado del doctor y sus contratos ha sido cambiado a ' . $nuevoEstado,
                'nuevoEstado' => $nuevoEstado
            ];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'status' => false,
                'mensaje' => 'Error al cambiar el estado del doctor: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Obtiene la lista de doctores filtrados por especialidad
     * @param int $idespecialidad ID de la especialidad
     * @return array Lista de doctores de esa especialidad
     */
    public function listarDoctoresPorEspecialidad($idespecialidad)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Consulta para obtener doctores de una especialidad específica
            $sql = "
            SELECT 
                p.idpersona,
                c.idcolaborador,
                p.apellidos,
                p.nombres,
                CONCAT(p.apellidos, ', ', p.nombres) AS nombre_completo,
                p.tipodoc,
                p.nrodoc,
                p.telefono,
                p.genero,
                p.email,
                e.idespecialidad,
                e.especialidad,
                e.precioatencion,
                c.estado
            FROM 
                colaboradores c
            INNER JOIN 
                personas p ON c.idpersona = p.idpersona
            INNER JOIN 
                especialidades e ON c.idespecialidad = e.idespecialidad
            WHERE 
                c.idespecialidad = ?
            ORDER BY 
                p.apellidos, p.nombres
        ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idespecialidad]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al listar doctores por especialidad: " . $e->getMessage());
            return [];
        }
    }
}
