<?php /*RUTA: sistemaclinica/models/Enfermeria.php*/?>
<?php
require_once 'Conexion.php';

class Enfermeria
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    /**
     * Busca un enfermero por su número de documento
     * @param string $nrodoc Número de documento a buscar
     * @return array Resultado con datos del enfermero o mensaje de error
     */
    public function buscarEnfermeroPorDocumento($nrodoc)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Verificar si existe un enfermero con este documento
            $stmt = $pdo->prepare("
                SELECT 
                    p.idpersona, 
                    c.idcolaborador,
                    p.apellidos,
                    p.nombres,
                    p.tipodoc,
                    p.nrodoc,
                    p.telefono,
                    p.fechanacimiento,
                    p.genero,
                    p.direccion,
                    p.email,
                    c.estado
                FROM 
                    personas p
                INNER JOIN 
                    colaboradores c ON p.idpersona = c.idpersona
                INNER JOIN 
                    contratos ct ON c.idcolaborador = ct.idcolaborador
                INNER JOIN 
                    usuarios u ON ct.idcontrato = u.idcontrato
                WHERE 
                    p.nrodoc = ? AND u.rol = 'ENFERMERO'
                LIMIT 1
            ");
            $stmt->execute([$nrodoc]);
            $enfermero = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($enfermero) {
                return [
                    'status' => true,
                    'enfermero' => $enfermero
                ];
            } else {
                return [
                    'status' => false,
                    'mensaje' => 'No se encontró enfermero con el documento proporcionado'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => false,
                'mensaje' => 'Error al buscar enfermero: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Registra un nuevo enfermero en el sistema
     * @param array $datos Datos del enfermero a registrar
     * @return array Resultado de la operación
     */
    public function registrarEnfermero($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // 1. Verificar si la persona ya existe
            $stmtPersona = $pdo->prepare("CALL sp_buscar_persona_por_documento(?)");
            $stmtPersona->execute([$datos['nrodoc']]);
            $persona = $stmtPersona->fetch(PDO::FETCH_ASSOC);
            $stmtPersona->closeCursor();

            $idpersona = null;

            // 2. Si la persona no existe, la registramos
            if (!$persona) {
                $stmtInsertPersona = $pdo->prepare("
                    INSERT INTO personas (
                        apellidos, nombres, tipodoc, nrodoc, 
                        telefono, fechanacimiento, genero, direccion, email
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmtInsertPersona->execute([
                    $datos['apellidos'],
                    $datos['nombres'],
                    $datos['tipodoc'],
                    $datos['nrodoc'],
                    $datos['telefono'],
                    $datos['fechanacimiento'] ?: null,
                    $datos['genero'],
                    $datos['direccion'],
                    $datos['email']
                ]);

                $idpersona = $pdo->lastInsertId();
            } else {
                $idpersona = $persona['idpersona'];
            }

            // 3. Registrar como colaborador (enfermero, sin especialidad)
            $stmtColaborador = $pdo->prepare("
                INSERT INTO colaboradores (idpersona, idespecialidad, estado) 
                VALUES (?, NULL, 'ACTIVO')
            ");
            $stmtColaborador->execute([$idpersona]);
            $idcolaborador = $pdo->lastInsertId();

            // 4. Crear contrato para el colaborador
            $stmtContrato = $pdo->prepare("
                INSERT INTO contratos (idcolaborador, fechainicio, fechafin, tipocontrato, estado)
                VALUES (?, CURDATE(), NULL, 'INDEFINIDO', 'ACTIVO')
            ");
            $stmtContrato->execute([$idcolaborador]);
            $idcontrato = $pdo->lastInsertId();

            // 5. Crear usuario con rol ENFERMERO
            $stmtUsuario = $pdo->prepare("
                INSERT INTO usuarios (idcontrato, nomuser, passuser, estado, rol)
                VALUES (?, ?, ?, TRUE, 'ENFERMERO')
            ");

            // Encriptar contraseña
            $passHash = password_hash($datos['passuser'], PASSWORD_BCRYPT);

            $stmtUsuario->execute([
                $idcontrato,
                $datos['email'],
                $passHash
            ]);

            $idusuario = $pdo->lastInsertId();

            // Confirmar la transacción
            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Enfermero registrado correctamente',
                'idusuario' => $idusuario
            ];
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'status' => false,
                'mensaje' => 'Error al registrar enfermero: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lista todos los enfermeros registrados en el sistema
     * @param string $busqueda Término de búsqueda opcional
     * @param string $estado Estado para filtrar (ACTIVO, INACTIVO, todos)
     * @param string $fechaRegistro Fecha de registro para filtrar (formato YYYY-MM-DD)
     * @return array Lista de enfermeros
     */
    public function listarEnfermeros($busqueda = null, $estado = null, $fechaRegistro = null)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Construir la consulta base
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
                    c.estado,
                    ct.fechainicio AS fecha_registro
                FROM 
                    colaboradores c
                INNER JOIN 
                    personas p ON c.idpersona = p.idpersona
                INNER JOIN 
                    contratos ct ON c.idcolaborador = ct.idcolaborador
                INNER JOIN 
                    usuarios u ON ct.idcontrato = u.idcontrato
                WHERE 
                    u.rol = 'ENFERMERO'
            ";

            // Agregar condiciones de búsqueda
            $params = [];

            if ($busqueda) {
                $sql .= " AND (p.apellidos LIKE ? OR p.nombres LIKE ? OR p.nrodoc LIKE ? OR p.email LIKE ?)";
                $params[] = "%$busqueda%";
                $params[] = "%$busqueda%";
                $params[] = "%$busqueda%";
                $params[] = "%$busqueda%";
            }

            if ($estado && in_array($estado, ['ACTIVO', 'INACTIVO'])) {
                $sql .= " AND c.estado = ?";
                $params[] = $estado;
            }

            // Agregar filtro por fecha de registro
            if ($fechaRegistro) {
                $sql .= " AND DATE(ct.fechainicio) = ?";
                $params[] = $fechaRegistro;
            }

            // Ordenar por ID de colaborador descendente (el más reciente primero)
            $sql .= " ORDER BY c.idcolaborador DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $enfermeros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'status' => true,
                'data' => $enfermeros
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'mensaje' => 'Error al listar enfermeros: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Cambia el estado de un enfermero (ACTIVO/INACTIVO)
     * @param string $nrodoc Número de documento del enfermero
     * @return array Resultado de la operación
     */
    public function cambiarEstadoEnfermero($nrodoc)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Verificar si existe el procedimiento almacenado para cambiar estado
            $stmtCheck = $pdo->prepare("
                SELECT COUNT(*) as existe
                FROM information_schema.ROUTINES
                WHERE ROUTINE_SCHEMA = 'clinicaDB'
                AND ROUTINE_NAME = 'sp_cambiar_estado_enfermero'
            ");
            $stmtCheck->execute();
            $procedimientoExiste = $stmtCheck->fetch(PDO::FETCH_ASSOC)['existe'] > 0;

            if ($procedimientoExiste) {
                // Usar el procedimiento almacenado
                $stmt = $pdo->prepare("CALL sp_cambiar_estado_enfermero(?, @resultado, @mensaje)");
                $stmt->execute([$nrodoc]);
                $stmt->closeCursor();

                $resultado = $pdo->query("SELECT @resultado AS resultado, @mensaje AS mensaje")->fetch(PDO::FETCH_ASSOC);

                return [
                    'status' => (bool)$resultado['resultado'],
                    'mensaje' => $resultado['mensaje']
                ];
            } else {
                // Implementación alternativa sin procedimiento almacenado
                $pdo->beginTransaction();

                // Buscar al enfermero
                $stmtBuscar = $pdo->prepare("
                    SELECT 
                        c.idcolaborador, 
                        c.estado
                    FROM 
                        colaboradores c
                    INNER JOIN 
                        personas p ON c.idpersona = p.idpersona
                    INNER JOIN 
                        contratos ct ON c.idcolaborador = ct.idcolaborador
                    INNER JOIN 
                        usuarios u ON ct.idcontrato = u.idcontrato
                    WHERE 
                        p.nrodoc = ? AND u.rol = 'ENFERMERO'
                    LIMIT 1
                ");
                $stmtBuscar->execute([$nrodoc]);
                $enfermero = $stmtBuscar->fetch(PDO::FETCH_ASSOC);

                if (!$enfermero) {
                    return [
                        'status' => false,
                        'mensaje' => 'No se encontró un enfermero con el documento especificado'
                    ];
                }

                $nuevoEstado = $enfermero['estado'] == 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';

                // Actualizar estado del colaborador
                $stmtActualizar = $pdo->prepare("
                    UPDATE colaboradores
                    SET estado = ?
                    WHERE idcolaborador = ?
                ");
                $stmtActualizar->execute([$nuevoEstado, $enfermero['idcolaborador']]);

                // Actualizar estado de los contratos
                $stmtContratos = $pdo->prepare("
                    UPDATE contratos
                    SET estado = ?
                    WHERE idcolaborador = ?
                ");
                $stmtContratos->execute([$nuevoEstado, $enfermero['idcolaborador']]);

                $pdo->commit();

                return [
                    'status' => true,
                    'mensaje' => "El estado del enfermero ha sido cambiado a $nuevoEstado"
                ];
            }
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'status' => false,
                'mensaje' => 'Error al cambiar estado del enfermero: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina un enfermero del sistema
     * @param string $nrodoc Número de documento del enfermero
     * @return array Resultado de la operación
     */
    public function eliminarEnfermero($nrodoc)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // Buscar al enfermero por su número de documento
            $stmtBuscar = $pdo->prepare("
                SELECT 
                    c.idcolaborador,
                    p.idpersona
                FROM 
                    personas p
                INNER JOIN 
                    colaboradores c ON p.idpersona = c.idpersona
                INNER JOIN 
                    contratos ct ON c.idcolaborador = ct.idcolaborador
                INNER JOIN 
                    usuarios u ON ct.idcontrato = u.idcontrato
                WHERE 
                    p.nrodoc = ? AND u.rol = 'ENFERMERO'
                LIMIT 1
            ");
            $stmtBuscar->execute([$nrodoc]);
            $enfermero = $stmtBuscar->fetch(PDO::FETCH_ASSOC);

            if (!$enfermero) {
                return [
                    'status' => false,
                    'mensaje' => 'No se encontró un enfermero con el documento especificado'
                ];
            }

            $idcolaborador = $enfermero['idcolaborador'];
            $idpersona = $enfermero['idpersona'];

            // 1. Eliminar triajes asociados (si existen)
            $stmtTriajes = $pdo->prepare("DELETE FROM triajes WHERE idenfermera = ?");
            $stmtTriajes->execute([$idcolaborador]);

            // 2. Eliminar alergias asociadas (si existen)
            $stmtAlergias = $pdo->prepare("DELETE FROM listaalergias WHERE idpersona = ?");
            $stmtAlergias->execute([$idpersona]);

            // 3. Eliminar citas asociadas (si existen)
            $stmtCitas = $pdo->prepare("DELETE FROM citas WHERE idpersona = ?");
            $stmtCitas->execute([$idpersona]);

            // 4. Eliminar usuarios asociados a los contratos
            $stmtUsuarios = $pdo->prepare("
                DELETE FROM usuarios 
                WHERE idcontrato IN (SELECT idcontrato FROM contratos WHERE idcolaborador = ?)
            ");
            $stmtUsuarios->execute([$idcolaborador]);

            // 5. Eliminar atenciones y horarios asociados a los contratos
            $stmtHorarios = $pdo->prepare("
                DELETE FROM horarios 
                WHERE idatencion IN (
                    SELECT idatencion FROM atenciones 
                    WHERE idcontrato IN (SELECT idcontrato FROM contratos WHERE idcolaborador = ?)
                )
            ");
            $stmtHorarios->execute([$idcolaborador]);

            $stmtAtenciones = $pdo->prepare("
                DELETE FROM atenciones 
                WHERE idcontrato IN (SELECT idcontrato FROM contratos WHERE idcolaborador = ?)
            ");
            $stmtAtenciones->execute([$idcolaborador]);

            // 6. Eliminar contratos
            $stmtContratos = $pdo->prepare("DELETE FROM contratos WHERE idcolaborador = ?");
            $stmtContratos->execute([$idcolaborador]);

            // 7. Eliminar colaborador
            $stmtColaborador = $pdo->prepare("DELETE FROM colaboradores WHERE idcolaborador = ?");
            $stmtColaborador->execute([$idcolaborador]);

            // 8. Eliminar persona (si no es un paciente o cliente)
            $stmtPersona = $pdo->prepare("
                DELETE FROM personas 
                WHERE idpersona = ?
                AND NOT EXISTS (SELECT 1 FROM pacientes WHERE idpersona = ?)
                AND NOT EXISTS (SELECT 1 FROM clientes WHERE idpersona = ?)
            ");
            $stmtPersona->execute([$idpersona, $idpersona, $idpersona]);

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Enfermero eliminado correctamente'
            ];
        } catch (Exception $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'status' => false,
                'mensaje' => 'Error al eliminar enfermero: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene la información detallada de un enfermero por su ID
     * @param int $idcolaborador ID del colaborador (enfermero)
     * @return array Datos del enfermero
     */
    public function obtenerEnfermeroPorId($idcolaborador)
    {
        try {
            $pdo = $this->conexion->getConexion();

            $stmt = $pdo->prepare("
                SELECT 
                    p.idpersona,
                    c.idcolaborador,
                    p.apellidos,
                    p.nombres,
                    CONCAT(p.apellidos, ', ', p.nombres) AS nombre_completo,
                    p.tipodoc,
                    p.nrodoc,
                    p.telefono,
                    p.fechanacimiento,
                    p.genero,
                    p.direccion,
                    p.email,
                    c.estado,
                    u.idusuario,
                    u.nomuser,
                    ct.fechainicio AS fecha_registro
                FROM 
                    colaboradores c
                INNER JOIN 
                    personas p ON c.idpersona = p.idpersona
                INNER JOIN 
                    contratos ct ON c.idcolaborador = ct.idcolaborador
                INNER JOIN 
                    usuarios u ON ct.idcontrato = u.idcontrato
                WHERE 
                    c.idcolaborador = ? AND u.rol = 'ENFERMERO'
                LIMIT 1
            ");
            $stmt->execute([$idcolaborador]);

            $enfermero = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($enfermero) {
                return [
                    'status' => true,
                    'data' => $enfermero
                ];
            } else {
                return [
                    'status' => false,
                    'mensaje' => 'No se encontró el enfermero solicitado'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => false,
                'mensaje' => 'Error al obtener información del enfermero: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualiza la información de un enfermero
     * @param array $datos Datos del enfermero a actualizar
     * @return array Resultado de la operación
     */
    public function actualizarEnfermero($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // Actualizar información de la persona
            $stmtPersona = $pdo->prepare("
            UPDATE personas 
            SET apellidos = ?, nombres = ?, tipodoc = ?, nrodoc = ?, 
                telefono = ?, fechanacimiento = ?, genero = ?, direccion = ?, email = ?
            WHERE idpersona = ?
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
                $datos['email'],
                $datos['idpersona']
            ]);

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Información del enfermero actualizada correctamente'
            ];
        } catch (Exception $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'status' => false,
                'mensaje' => 'Error al actualizar la información: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualiza las credenciales de acceso de un enfermero
     * @param array $datos Datos para actualizar las credenciales
     * @return array Resultado de la operación
     */
    public function actualizarCredenciales($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // Generar hash de la contraseña
            $passwordHash = password_hash($datos['nuevaPassword'], PASSWORD_BCRYPT);

            // Actualizar contraseña del usuario
            $stmtUsuario = $pdo->prepare("
            UPDATE usuarios 
            SET passuser = ?
            WHERE idusuario = ?
        ");

            $stmtUsuario->execute([
                $passwordHash,
                $datos['idusuario']
            ]);

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Credenciales actualizadas correctamente'
            ];
        } catch (Exception $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'status' => false,
                'mensaje' => 'Error al actualizar las credenciales: ' . $e->getMessage()
            ];
        }
    }
}
?>