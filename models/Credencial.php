<?php /*RUTA: sistemaclinica/models/Credencial.php*/ ?>
<?php

require_once 'Conexion.php';

class Credencial
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    /**
     * Registra credenciales de acceso para un usuario
     * @param array $datos Datos de las credenciales
     * @return array Resultado de la operación y mensaje
     */
    public function registrarCredenciales($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Verificar si el usuario ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) AS existe FROM usuarios WHERE nomuser = ?");
            $stmt->execute([$datos['nomuser']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['existe'] > 0) {
                return [
                    'status' => false,
                    'mensaje' => 'El correo electrónico ya está en uso',
                    'idusuario' => null
                ];
            }

            // Asegurarnos que siempre se use el rol proporcionado o DOCTOR por defecto
            $rol = isset($datos['rol']) && !empty($datos['rol']) ? $datos['rol'] : 'DOCTOR';

            // Insertar usuario directamente con el rol especificado
            $passHash = password_hash($datos['passuser'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("
            INSERT INTO usuarios (idcontrato, nomuser, passuser, estado, rol) 
            VALUES (?, ?, ?, TRUE, ?)
        ");
            $stmt->execute([$datos['idcontrato'], $datos['nomuser'], $passHash, $rol]);

            $idusuario = $pdo->lastInsertId();

            return [
                'status' => true,
                'mensaje' => 'Credenciales de acceso registradas correctamente',
                'idusuario' => $idusuario
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'mensaje' => "Error al registrar credenciales: " . $e->getMessage(),
                'idusuario' => null
            ];
        }
    }

    /**
     * Actualiza las credenciales de un usuario existente
     * @param array $datos Datos actualizados de las credenciales
     * @return array Resultado de la operación
     */
    public function actualizarCredenciales($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // Obtener el idpersona asociado al colaborador
            $stmtPersona = $pdo->prepare("
            SELECT p.idpersona 
            FROM colaboradores c
            INNER JOIN personas p ON c.idpersona = p.idpersona
            WHERE c.idcolaborador = ?
        ");
            $stmtPersona->execute([$datos['idcolaborador']]);
            $idpersona = $stmtPersona->fetchColumn();

            if ($idpersona) {
                // Actualizar el email en la tabla personas
                $stmtEmailPersona = $pdo->prepare("UPDATE personas SET email = ? WHERE idpersona = ?");
                $stmtEmailPersona->execute([$datos['emailusuario'], $idpersona]);
            }

            // Obtener el ID del contrato asociado al colaborador
            $stmtContrato = $pdo->prepare("
            SELECT idcontrato FROM contratos WHERE idcolaborador = ? AND estado = 'ACTIVO' LIMIT 1
        ");
            $stmtContrato->execute([$datos['idcolaborador']]);
            $idContrato = $stmtContrato->fetchColumn();

            // Si no se encontró el usuario, buscar por el número de documento
            if (!$datos['idusuario'] && !empty($datos['nrodoc'])) {
                $stmtUsuario = $pdo->prepare("
                SELECT u.idusuario
                FROM usuarios u
                INNER JOIN contratos c ON u.idcontrato = c.idcontrato
                INNER JOIN colaboradores co ON c.idcolaborador = co.idcolaborador
                INNER JOIN personas p ON co.idpersona = p.idpersona
                WHERE p.nrodoc = ?
                LIMIT 1
            ");
                $stmtUsuario->execute([$datos['nrodoc']]);
                $datos['idusuario'] = $stmtUsuario->fetchColumn();
            }

            if ($datos['idusuario']) {
                // Actualizar el usuario existente
                if (!empty($datos['contrasena'])) {
                    // Si se proporcionó nueva contraseña, actualizar email y contraseña
                    $passHash = password_hash($datos['contrasena'], PASSWORD_BCRYPT);
                    $stmtUsuario = $pdo->prepare("
                    UPDATE usuarios 
                    SET nomuser = ?, passuser = ? 
                    WHERE idusuario = ?
                ");
                    $stmtUsuario->execute([$datos['emailusuario'], $passHash, $datos['idusuario']]);
                } else {
                    // Si no se proporcionó contraseña, solo actualizar email
                    $stmtUsuario = $pdo->prepare("
                    UPDATE usuarios 
                    SET nomuser = ? 
                    WHERE idusuario = ?
                ");
                    $stmtUsuario->execute([$datos['emailusuario'], $datos['idusuario']]);
                }
            } else if ($idContrato) {
                // Si no existe el usuario pero sí el contrato, crear un nuevo usuario
                if (empty($datos['contrasena'])) {
                    $pdo->rollBack();
                    return [
                        'status' => false,
                        'mensaje' => 'Debe proporcionar una contraseña para crear el nuevo usuario'
                    ];
                }

                $passHash = password_hash($datos['contrasena'], PASSWORD_BCRYPT);
                $stmtNuevoUsuario = $pdo->prepare("
                INSERT INTO usuarios (idcontrato, nomuser, passuser, estado, rol) 
                VALUES (?, ?, ?, TRUE, 'DOCTOR')
            ");
                $stmtNuevoUsuario->execute([$idContrato, $datos['emailusuario'], $passHash]);
                $datos['idusuario'] = $pdo->lastInsertId();
            } else {
                // No se encontró contrato activo
                $pdo->rollBack();
                return [
                    'status' => false,
                    'mensaje' => 'No se encontró un contrato activo para este doctor'
                ];
            }

            $pdo->commit();

            $mensaje = 'Credenciales actualizadas correctamente';
            if (empty($datos['contrasena'])) {
                $mensaje = 'Correo electrónico actualizado correctamente. La contraseña no se modificó.';
            }

            return [
                'status' => true,
                'mensaje' => $mensaje,
                'idusuario' => $datos['idusuario']
            ];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return [
                'status' => false,
                'mensaje' => "Error al actualizar credenciales: " . $e->getMessage()
            ];
        }
    }
    /**
     * Verifica si un correo electrónico ya está siendo usado por otro usuario
     * @param string $email Correo electrónico a verificar
     * @param int $idusuario ID del usuario actual (para excluirlo de la verificación)
     * @return bool True si el correo ya existe para otro usuario, False en caso contrario
     */
    public function verificarCorreoDuplicado($email, $idusuario)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $stmt = $pdo->prepare("SELECT COUNT(*) AS existe FROM usuarios WHERE nomuser = ? AND idusuario != ?");
            $stmt->execute([$email, $idusuario]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['existe'] > 0;
        } catch (Exception $e) {
            // Por seguridad, ante cualquier error asumimos que existe
            return true;
        }
    }

    /**
     * Verifica si un procedimiento almacenado existe en la base de datos
     * @param string $nombreProcedimiento Nombre del procedimiento a verificar
     * @return boolean True si existe, False en caso contrario
     */
    private function verificarProcedimientoExiste($nombreProcedimiento)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $stmt = $pdo->prepare("
                SELECT COUNT(*) AS existe
                FROM information_schema.ROUTINES
                WHERE ROUTINE_SCHEMA = 'clinicaDB'
                AND ROUTINE_NAME = ?
            ");
            $stmt->execute([$nombreProcedimiento]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['existe'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Verifica si un correo electrónico ya existe como usuario
     * @param string $email Correo electrónico a verificar
     * @return boolean True si el usuario existe, False en caso contrario
     */
    public function verificarUsuarioExistente($email)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $stmt = $pdo->prepare("SELECT COUNT(*) AS existe FROM usuarios WHERE nomuser = ?");
            $stmt->execute([$email]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['existe'] > 0;
        } catch (Exception $e) {
            // En caso de error, por seguridad retornamos true (asumiendo que existe)
            return true;
        }
    }

    /**
     * Obtiene la información de usuario por ID
     * @param int $idUsuario ID del usuario
     * @return array Datos del usuario
     */
    public function obtenerUsuarioPorId($idUsuario)
    {
        try {
            $pdo = $this->conexion->getConexion();

            if ($this->verificarProcedimientoExiste('sp_obtener_usuario_por_id')) {
                $stmt = $pdo->prepare("CALL sp_obtener_usuario_por_id(?)");
                $stmt->execute([$idUsuario]);

                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                $stmt->closeCursor();

                return $usuario;
            } else {
                $stmt = $pdo->prepare("
                    SELECT 
                        u.idusuario,
                        u.idcontrato,
                        u.nomuser,
                        u.estado,
                        u.rol,
                        c.idcolaborador,
                        CONCAT(p.apellidos, ', ', p.nombres) AS colaborador,
                        e.especialidad
                    FROM 
                        usuarios u
                    INNER JOIN 
                        contratos c ON u.idcontrato = c.idcontrato
                    INNER JOIN 
                        colaboradores co ON c.idcolaborador = co.idcolaborador
                    INNER JOIN 
                        personas p ON co.idpersona = p.idpersona
                    LEFT JOIN 
                        especialidades e ON co.idespecialidad = e.idespecialidad
                    WHERE 
                        u.idusuario = ?
                ");
                $stmt->execute([$idUsuario]);

                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            die("Error al obtener usuario: " . $e->getMessage());
        }
    }

    /**
     * Obtiene la lista de usuarios por contrato
     * @param int $idContrato ID del contrato
     * @return array Lista de usuarios
     */
    public function obtenerUsuariosPorContrato($idContrato)
    {
        try {
            $pdo = $this->conexion->getConexion();

            if ($this->verificarProcedimientoExiste('sp_obtener_usuarios_por_contrato')) {
                $stmt = $pdo->prepare("CALL sp_obtener_usuarios_por_contrato(?)");
                $stmt->execute([$idContrato]);

                $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();

                return $usuarios;
            } else {
                $stmt = $pdo->prepare("
                    SELECT 
                        u.idusuario,
                        u.idcontrato,
                        u.nomuser,
                        u.estado,
                        u.rol,
                        CONCAT(p.apellidos, ', ', p.nombres) AS colaborador,
                        e.especialidad
                    FROM 
                        usuarios u
                    INNER JOIN 
                        contratos c ON u.idcontrato = c.idcontrato
                    INNER JOIN 
                        colaboradores co ON c.idcolaborador = co.idcolaborador
                    INNER JOIN 
                        personas p ON co.idpersona = p.idpersona
                    LEFT JOIN 
                        especialidades e ON co.idespecialidad = e.idespecialidad
                    WHERE 
                        u.idcontrato = ?
                    ORDER BY 
                        u.idusuario
                ");
                $stmt->execute([$idContrato]);

                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            die("Error al obtener usuarios: " . $e->getMessage());
        }
    }

    /**
     * Cambia el estado de un usuario (activar/desactivar)
     * @param int $idUsuario ID del usuario
     * @param boolean $estado Nuevo estado
     * @return array Resultado de la operación
     */
    public function cambiarEstadoUsuario($idUsuario, $estado)
    {
        try {
            $pdo = $this->conexion->getConexion();

            if ($this->verificarProcedimientoExiste('sp_cambiar_estado_usuario')) {
                $stmt = $pdo->prepare("CALL sp_cambiar_estado_usuario(?, ?, @resultado, @mensaje)");
                $stmt->execute([$idUsuario, $estado]);
                $stmt->closeCursor();

                $result = $pdo->query("SELECT @resultado AS resultado, @mensaje AS mensaje")->fetch(PDO::FETCH_ASSOC);
                return [
                    'status' => (bool)$result['resultado'],
                    'mensaje' => $result['mensaje']
                ];
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET estado = ? WHERE idusuario = ?");
                $stmt->execute([$estado, $idUsuario]);
                $filasAfectadas = $stmt->rowCount();

                return [
                    'status' => $filasAfectadas > 0,
                    'mensaje' => $filasAfectadas > 0 ?
                        'Estado de usuario actualizado correctamente' :
                        'No se pudo actualizar el estado o el usuario no existe'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => false,
                'mensaje' => "Error al cambiar estado de usuario: " . $e->getMessage()
            ];
        }
    }
}
