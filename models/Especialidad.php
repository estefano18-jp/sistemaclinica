<?php

require_once 'Conexion.php';

class Especialidad
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    /**
     * Registra una nueva especialidad
     * @param array $datos Datos de la especialidad
     * @return array Resultado de la operación y mensaje
     */
    public function registrarEspecialidad($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Verificar si la especialidad ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) AS existe FROM especialidades WHERE especialidad = ?");
            $stmt->execute([$datos['especialidad']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['existe'] > 0) {
                return [
                    'status' => false,
                    'mensaje' => 'Esta especialidad ya está registrada',
                    'idespecialidad' => null
                ];
            }

            // Insertar nueva especialidad
            $stmt = $pdo->prepare("
                INSERT INTO especialidades (especialidad, precioatencion, estado)
                VALUES (?, ?, 'ACTIVO')
            ");

            $stmt->execute([
                $datos['especialidad'],
                $datos['precioatencion']
            ]);

            $idespecialidad = $pdo->lastInsertId();

            return [
                'status' => true,
                'mensaje' => 'Especialidad registrada correctamente',
                'idespecialidad' => $idespecialidad
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'mensaje' => "Error al registrar especialidad: " . $e->getMessage(),
                'idespecialidad' => null
            ];
        }
    }

    /**
     * Obtiene información de una especialidad específica
     * @param int $idEspecialidad ID de la especialidad
     * @return array|null Datos de la especialidad o null si no existe
     */
    public function obtenerEspecialidadPorId($idEspecialidad)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $stmt = $pdo->prepare("
                SELECT idespecialidad, especialidad, precioatencion, estado
                FROM especialidades
                WHERE idespecialidad = ?
            ");

            $stmt->execute([$idEspecialidad]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("Error al obtener especialidad: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene la lista de todas las especialidades
     * @return array Lista de especialidades
     */
    public function listarEspecialidades()
    {
        try {
            $pdo = $this->conexion->getConexion();
            
            // Intentar usar el procedimiento almacenado
            try {
                $stmt = $pdo->prepare("CALL spu_especialidades_listar()");
                $stmt->execute();
                $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                return $especialidades;
            } catch (Exception $e) {
                // Si falla el procedimiento, usar consulta directa
                $stmt = $pdo->query("
                    SELECT idespecialidad, especialidad, precioatencion, estado
                    FROM especialidades
                    ORDER BY especialidad
                ");
                
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log("Error al listar especialidades: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Actualiza el precio de atención de una especialidad
     * @param int $idEspecialidad ID de la especialidad
     * @param float $precioAtencion Nuevo precio de atención
     * @return array Resultado de la operación
     */
    public function actualizarPrecioEspecialidad($idEspecialidad, $precioAtencion)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Verificar si la especialidad existe
            $stmt = $pdo->prepare("SELECT COUNT(*) AS existe FROM especialidades WHERE idespecialidad = ?");
            $stmt->execute([$idEspecialidad]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['existe'] == 0) {
                return [
                    'status' => false,
                    'mensaje' => 'La especialidad no existe'
                ];
            }

            // Actualizar precio
            $stmt = $pdo->prepare("
                UPDATE especialidades 
                SET precioatencion = ? 
                WHERE idespecialidad = ?
            ");

            $stmt->execute([$precioAtencion, $idEspecialidad]);

            return [
                'status' => true,
                'mensaje' => 'Precio de atención actualizado correctamente'
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'mensaje' => "Error al actualizar precio: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Elimina una especialidad
     * @param int $idEspecialidad ID de la especialidad a eliminar
     * @return array Resultado de la operación
     */
    public function eliminarEspecialidad($idEspecialidad)
    {
        try {
            $pdo = $this->conexion->getConexion();
            
            // Verificar si la especialidad está en uso
            $stmt = $pdo->prepare("
                SELECT COUNT(*) AS en_uso 
                FROM colaboradores 
                WHERE idespecialidad = ?
            ");
            $stmt->execute([$idEspecialidad]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['en_uso'] > 0) {
                return [
                    'eliminado' => false,
                    'mensaje' => 'No se puede eliminar la especialidad porque está asignada a uno o más colaboradores.'
                ];
            }
            
            // Eliminar la especialidad
            $stmt = $pdo->prepare("DELETE FROM especialidades WHERE idespecialidad = ?");
            $stmt->execute([$idEspecialidad]);
            
            if ($stmt->rowCount() > 0) {
                return [
                    'eliminado' => true,
                    'mensaje' => 'Especialidad eliminada correctamente'
                ];
            } else {
                return [
                    'eliminado' => false,
                    'mensaje' => 'No se encontró la especialidad para eliminar'
                ];
            }
        } catch (Exception $e) {
            error_log("Error al eliminar especialidad: " . $e->getMessage());
            return [
                'eliminado' => false,
                'mensaje' => 'Error al eliminar la especialidad: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Cambia el estado de una especialidad
     * @param int $idEspecialidad ID de la especialidad
     * @param string $estado Nuevo estado (ACTIVO/INACTIVO)
     * @return array Resultado de la operación
     */
    public function cambiarEstadoEspecialidad($idEspecialidad, $estado)
    {
        try {
            $pdo = $this->conexion->getConexion();
            
            // Si estamos inactivando, verificar si la especialidad está en uso
            if ($estado === 'INACTIVO') {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) AS en_uso 
                    FROM colaboradores 
                    WHERE idespecialidad = ?
                ");
                $stmt->execute([$idEspecialidad]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result['en_uso'] > 0) {
                    return [
                        'status' => false,
                        'mensaje' => 'No se puede inactivar la especialidad porque está asignada a uno o más colaboradores.',
                        'en_uso' => true
                    ];
                }
            }
            
            // Intentar usar el procedimiento almacenado
            try {
                $stmt = $pdo->prepare("CALL spu_especialidades_cambiar_estado(?, ?)");
                $stmt->execute([$idEspecialidad, $estado]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                
                if ($result && $result['actualizado']) {
                    return [
                        'status' => true,
                        'mensaje' => 'Estado de la especialidad actualizado correctamente'
                    ];
                } else {
                    return [
                        'status' => false,
                        'mensaje' => 'No se pudo actualizar el estado de la especialidad'
                    ];
                }
            } catch (Exception $e) {
                // Si falla el procedimiento, usar consulta directa
                $stmt = $pdo->prepare("
                    UPDATE especialidades 
                    SET estado = ? 
                    WHERE idespecialidad = ?
                ");
                $stmt->execute([$estado, $idEspecialidad]);
                
                if ($stmt->rowCount() > 0) {
                    return [
                        'status' => true,
                        'mensaje' => 'Estado de la especialidad actualizado correctamente'
                    ];
                } else {
                    return [
                        'status' => false,
                        'mensaje' => 'No se pudo actualizar el estado de la especialidad'
                    ];
                }
            }
        } catch (Exception $e) {
            error_log("Error al cambiar estado de especialidad: " . $e->getMessage());
            return [
                'status' => false,
                'mensaje' => 'Error al cambiar el estado de la especialidad: ' . $e->getMessage()
            ];
        }
    }
}