<?php /*RUTA: sistemaclinica/models/Atencio.php*/?>
<?php

require_once 'Conexion.php';

class Atencion
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    /**
     * Obtiene una atención específica por ID
     * @param int $idatencion ID de la atención
     * @return array|null Datos de la atención o null si no existe
     */
    public function obtenerAtencionPorId($idatencion)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $stmt = $pdo->prepare("
                SELECT * FROM atenciones WHERE idatencion = ?
            ");
            $stmt->execute([$idatencion]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener atención por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene las atenciones de un contrato específico
     * @param int $idcontrato ID del contrato
     * @return array Lista de atenciones
     */
    public function obtenerAtencionesPorContrato($idcontrato)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $stmt = $pdo->prepare("
                SELECT * FROM atenciones WHERE idcontrato = ?
                ORDER BY 
                    CASE diasemana
                        WHEN 'LUNES' THEN 1
                        WHEN 'MARTES' THEN 2
                        WHEN 'MIERCOLES' THEN 3
                        WHEN 'JUEVES' THEN 4
                        WHEN 'VIERNES' THEN 5
                        WHEN 'SABADO' THEN 6
                        WHEN 'DOMINGO' THEN 7
                    END
            ");
            $stmt->execute([$idcontrato]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener atenciones por contrato: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una atención por contrato y día de semana
     * @param int $idcontrato ID del contrato
     * @param string $diasemana Día de la semana (LUNES, MARTES, etc.)
     * @return array|null Lista de atenciones encontradas
     */
    public function obtenerAtencionPorContratoYDia($idcontrato, $diasemana)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $stmt = $pdo->prepare("
                SELECT * FROM atenciones 
                WHERE idcontrato = ? AND diasemana = ?
            ");
            $stmt->execute([$idcontrato, $diasemana]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener atención por contrato y día: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Registra una nueva atención
     * @param array $datos Datos de la atención
     * @return array Resultado de la operación
     */
    public function registrarAtencion($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            
            // Validar datos obligatorios
            if (!isset($datos['idcontrato']) || !isset($datos['diasemana'])) {
                return [
                    'status' => false,
                    'mensaje' => 'Faltan datos obligatorios: idcontrato y diasemana son requeridos',
                    'idatencion' => null
                ];
            }
            
            // Verificar si ya existe una atención para este contrato y día
            $stmt = $pdo->prepare("
                SELECT idatencion FROM atenciones 
                WHERE idcontrato = ? AND diasemana = ?
            ");
            $stmt->execute([$datos['idcontrato'], $datos['diasemana']]);
            $atencionExistente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($atencionExistente) {
                return [
                    'status' => true,
                    'mensaje' => 'La atención ya existe para este contrato y día',
                    'idatencion' => $atencionExistente['idatencion']
                ];
            }
            
            // Insertar nueva atención
            $stmt = $pdo->prepare("
                INSERT INTO atenciones (idcontrato, diasemana)
                VALUES (?, ?)
            ");
            $stmt->execute([
                $datos['idcontrato'],
                $datos['diasemana']
            ]);
            
            $idatencion = $pdo->lastInsertId();
            
            return [
                'status' => true,
                'mensaje' => 'Atención registrada correctamente',
                'idatencion' => $idatencion
            ];
        } catch (Exception $e) {
            error_log("Error al registrar atención: " . $e->getMessage());
            
            return [
                'status' => false,
                'mensaje' => 'Error al registrar atención: ' . $e->getMessage(),
                'idatencion' => null
            ];
        }
    }

    /**
     * Elimina una atención específica
     * @param int $idatencion ID de la atención a eliminar
     * @return array Resultado de la operación
     */
    public function eliminarAtencion($idatencion)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();
            
            // Primero verificar si tiene horarios asociados
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total FROM horarios 
                WHERE idatencion = ?
            ");
            $stmt->execute([$idatencion]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado['total'] > 0) {
                // Primero eliminar los horarios asociados
                $stmt = $pdo->prepare("
                    DELETE FROM horarios 
                    WHERE idatencion = ?
                ");
                $stmt->execute([$idatencion]);
            }
            
            // Luego eliminar la atención
            $stmt = $pdo->prepare("
                DELETE FROM atenciones 
                WHERE idatencion = ?
            ");
            $stmt->execute([$idatencion]);
            
            $pdo->commit();
            
            return [
                'status' => true,
                'mensaje' => 'Atención eliminada correctamente'
            ];
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            error_log("Error al eliminar atención: " . $e->getMessage());
            
            return [
                'status' => false,
                'mensaje' => 'Error al eliminar atención: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza una atención existente
     * @param array $datos Datos actualizados de la atención
     * @return array Resultado de la operación
     */
    public function actualizarAtencion($datos)
    {
        try {
            // Validar datos obligatorios
            if (!isset($datos['idatencion']) || $datos['idatencion'] <= 0) {
                return [
                    'status' => false,
                    'mensaje' => 'ID de atención no válido'
                ];
            }
            
            if (!isset($datos['diasemana']) || empty($datos['diasemana'])) {
                return [
                    'status' => false,
                    'mensaje' => 'El día de semana es requerido'
                ];
            }
            
            $pdo = $this->conexion->getConexion();
            
            // Actualizar atención
            $stmt = $pdo->prepare("
                UPDATE atenciones 
                SET diasemana = ?
                WHERE idatencion = ?
            ");
            $stmt->execute([
                $datos['diasemana'],
                $datos['idatencion']
            ]);
            
            return [
                'status' => true,
                'mensaje' => 'Atención actualizada correctamente'
            ];
        } catch (Exception $e) {
            error_log("Error al actualizar atención: " . $e->getMessage());
            
            return [
                'status' => false,
                'mensaje' => 'Error al actualizar atención: ' . $e->getMessage()
            ];
        }
    }
}