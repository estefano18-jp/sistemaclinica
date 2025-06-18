<?php /*RUTA: sistemaclinica/models/Contrato.php*/?>
<?php

require_once 'Conexion.php';

class Contrato {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    /**
     * Registra un contrato para un colaborador
     * @param array $datos Datos del contrato
     * @return array Resultado de la operación y mensaje
     */
    public function registrarContrato($datos) {
        try {
            $pdo = $this->conexion->getConexion();
            $stmt = $pdo->prepare("CALL sp_registrar_contrato(?, ?, ?, ?, @resultado, @mensaje, @idcontrato)");
            
            $stmt->execute([
                $datos['idcolaborador'],
                $datos['tipocontrato'],
                $datos['fechainicio'],
                $datos['fechafin']
            ]);
            $stmt->closeCursor();
            
            $result = $pdo->query("SELECT @resultado AS resultado, @mensaje AS mensaje, @idcontrato AS idcontrato")->fetch(PDO::FETCH_ASSOC);
            return [
                'status' => (bool)$result['resultado'],
                'mensaje' => $result['mensaje'],
                'idcontrato' => $result['idcontrato']
            ];
        } catch (Exception $e) {
            error_log("Error al registrar contrato: " . $e->getMessage());
            return [
                'status' => false,
                'mensaje' => 'Error al registrar contrato: ' . $e->getMessage(),
                'idcontrato' => null
            ];
        }
    }

    /**
     * Obtiene los contratos de un colaborador específico
     * @param int $idColaborador ID del colaborador
     * @return array Lista de contratos
     */
    public function obtenerContratosPorColaborador($idColaborador) {
        try {
            $pdo = $this->conexion->getConexion();
            
            // Si existe el procedimiento almacenado, usarlo
            try {
                $stmt = $pdo->prepare("CALL sp_obtener_contratos_por_colaborador(?)");
                $stmt->execute([$idColaborador]);
                $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                
                return $contratos;
            } catch (Exception $e) {
                // Si el procedimiento falla, usar consulta SQL directa
                $sql = "
                    SELECT 
                        c.idcontrato,
                        c.idcolaborador,
                        c.tipocontrato,
                        c.fechainicio,
                        c.fechafin,
                        CASE WHEN c.fechafin IS NULL OR c.fechafin >= CURDATE() THEN 'Activo' ELSE 'Finalizado' END AS estado
                    FROM 
                        contratos c
                    WHERE 
                        c.idcolaborador = ?
                    ORDER BY 
                        c.fechainicio DESC
                ";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$idColaborador]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log("Error al obtener contratos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene información de un contrato específico
     * @param int $idContrato ID del contrato
     * @return array|null Datos del contrato o null si no existe
     */
    public function obtenerContratoPorId($idContrato) {
        try {
            $pdo = $this->conexion->getConexion();
            
            // Si existe el procedimiento almacenado, usarlo
            try {
                $stmt = $pdo->prepare("CALL sp_obtener_contrato_por_id(?)");
                $stmt->execute([$idContrato]);
                $contrato = $stmt->fetch(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                
                return $contrato;
            } catch (Exception $e) {
                // Si el procedimiento falla, usar consulta SQL directa
                $sql = "
                    SELECT 
                        c.idcontrato,
                        c.idcolaborador,
                        c.tipocontrato,
                        c.fechainicio,
                        c.fechafin,
                        CASE WHEN c.fechafin IS NULL OR c.fechafin >= CURDATE() THEN 'Activo' ELSE 'Finalizado' END AS estado,
                        col.idpersona,
                        col.idespecialidad
                    FROM 
                        contratos c
                    INNER JOIN
                        colaboradores col ON c.idcolaborador = col.idcolaborador
                    WHERE 
                        c.idcontrato = ?
                ";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$idContrato]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log("Error al obtener contrato: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtiene el contrato activo de un colaborador
     * @param int $idColaborador ID del colaborador
     * @return array|null Datos del contrato activo o null si no existe
     */
    public function obtenerContratoActivo($idColaborador) {
        try {
            $pdo = $this->conexion->getConexion();
            
            $sql = "
                SELECT 
                    c.idcontrato,
                    c.idcolaborador,
                    c.tipocontrato,
                    c.fechainicio,
                    c.fechafin,
                    'Activo' AS estado
                FROM 
                    contratos c
                WHERE 
                    c.idcolaborador = ? AND
                    (c.fechafin IS NULL OR c.fechafin >= CURDATE())
                ORDER BY 
                    c.fechainicio DESC
                LIMIT 1
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idColaborador]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener contrato activo: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Lista todos los contratos con filtros opcionales
     * @param array $filtros Filtros para la búsqueda (estado, idcolaborador)
     * @return array Lista de contratos
     */
    public function listarContratos($filtros = []) {
        try {
            $pdo = $this->conexion->getConexion();
            
            $sql = "
                SELECT 
                    c.idcontrato,
                    c.idcolaborador,
                    c.tipocontrato,
                    c.fechainicio,
                    c.fechafin,
                    CASE WHEN c.fechafin IS NULL OR c.fechafin >= CURDATE() THEN 'Activo' ELSE 'Finalizado' END AS estado
                FROM 
                    contratos c
                WHERE 1=1
            ";
            
            $params = [];
            
            // Aplicar filtros si existen
            if (isset($filtros['estado']) && !empty($filtros['estado'])) {
                if ($filtros['estado'] == 'Activo') {
                    $sql .= " AND (c.fechafin IS NULL OR c.fechafin >= CURDATE())";
                } elseif ($filtros['estado'] == 'Finalizado') {
                    $sql .= " AND c.fechafin < CURDATE()";
                }
            }
            
            if (isset($filtros['idcolaborador']) && !empty($filtros['idcolaborador'])) {
                $sql .= " AND c.idcolaborador = ?";
                $params[] = $filtros['idcolaborador'];
            }
            
            $sql .= " ORDER BY c.fechainicio DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al listar contratos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Actualiza un contrato existente
     * @param array $datos Datos actualizados del contrato
     * @return array Resultado de la operación
     */
    public function actualizarContrato($datos) {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();
            
            $sql = "UPDATE contratos SET ";
            $params = [];
            $setStatements = [];
            
            if (isset($datos['tipocontrato']) && !empty($datos['tipocontrato'])) {
                $setStatements[] = "tipocontrato = ?";
                $params[] = $datos['tipocontrato'];
            }
            
            if (isset($datos['fechainicio']) && !empty($datos['fechainicio'])) {
                $setStatements[] = "fechainicio = ?";
                $params[] = $datos['fechainicio'];
            }
            
            if (isset($datos['fechafin'])) {
                if (empty($datos['fechafin'])) {
                    $setStatements[] = "fechafin = NULL";
                } else {
                    $setStatements[] = "fechafin = ?";
                    $params[] = $datos['fechafin'];
                }
            }
            
            if (empty($setStatements)) {
                return [
                    'status' => false,
                    'mensaje' => 'No hay datos para actualizar'
                ];
            }
            
            $sql .= implode(", ", $setStatements);
            $sql .= " WHERE idcontrato = ?";
            $params[] = $datos['idcontrato'];
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $pdo->commit();
            
            return [
                'status' => true,
                'mensaje' => 'Contrato actualizado correctamente'
            ];
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            error_log("Error al actualizar contrato: " . $e->getMessage());
            
            return [
                'status' => false,
                'mensaje' => 'Error al actualizar contrato: ' . $e->getMessage()
            ];
        }
    }
}
?>