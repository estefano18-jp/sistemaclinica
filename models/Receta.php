<?php /*RUTA: sistemaclinica/models/Receta.php*/?>
<?php

require_once 'Conexion.php';

class Receta
{
    private $pdo;

    public function __CONSTRUCT()
    {
        $this->pdo = (new Conexion())->getConexion();
    }

    /**
     * Registra una nueva receta médica
     * @param array $datos Datos de la receta
     * @return array Resultado de la operación
     */
    public function registrar($datos)
    {
        try {
            $this->pdo->beginTransaction();
            
            // 1. Crear la receta principal
            $stmt = $this->pdo->prepare("
                INSERT INTO recetas (
                    idconsulta, 
                    medicacion, 
                    cantidad, 
                    frecuencia, 
                    observaciones
                ) VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $datos['idconsulta'],
                $datos['medicacion'],
                $datos['cantidad'],
                $datos['frecuencia'],
                $datos['observaciones'] ?? ''
            ]);
            
            $idreceta = $this->pdo->lastInsertId();
            
            // 2. Si hay tratamientos adicionales, insertarlos
            if (!empty($datos['tratamientos']) && is_array($datos['tratamientos'])) {
                $stmtTratamiento = $this->pdo->prepare("
                    INSERT INTO tratamiento (
                        medicacion, 
                        dosis, 
                        frecuencia, 
                        duracion, 
                        idreceta
                    ) VALUES (?, ?, ?, ?, ?)
                ");
                
                foreach ($datos['tratamientos'] as $tratamiento) {
                    $stmtTratamiento->execute([
                        $tratamiento['medicacion'],
                        $tratamiento['dosis'],
                        $tratamiento['frecuencia'],
                        $tratamiento['duracion'],
                        $idreceta
                    ]);
                }
            }
            
            $this->pdo->commit();
            
            return [
                'status' => true,
                'mensaje' => 'Receta registrada correctamente',
                'idreceta' => $idreceta
            ];
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            
            error_log("Error al registrar receta: " . $e->getMessage());
            
            return [
                'status' => false,
                'mensaje' => 'Error al registrar la receta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene las recetas por ID de paciente
     * @param int $idpaciente ID del paciente
     * @return array Lista de recetas del paciente
     */
    public function obtenerPorPaciente($idpaciente)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    r.idreceta,
                    r.medicacion,
                    r.cantidad,
                    r.frecuencia,
                    r.observaciones,
                    c.fecha AS fecha_consulta,
                    c.horaprogramada
                FROM 
                    recetas r
                INNER JOIN 
                    consultas c ON r.idconsulta = c.idconsulta
                WHERE 
                    c.idpaciente = ?
                ORDER BY 
                    c.fecha DESC, c.horaprogramada DESC
            ");
            
            $stmt->execute([$idpaciente]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener recetas por paciente: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene la receta por ID de consulta
     * @param int $idconsulta ID de la consulta
     * @return array|null Datos de la receta o null si no existe
     */
    public function obtenerPorConsulta($idconsulta)
    {
        try {
            // Obtener receta principal
            $stmt = $this->pdo->prepare("
                SELECT 
                    r.idreceta,
                    r.medicacion,
                    r.cantidad,
                    r.frecuencia,
                    r.observaciones
                FROM 
                    recetas r
                WHERE 
                    r.idconsulta = ?
            ");
            
            $stmt->execute([$idconsulta]);
            $receta = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$receta) {
                return null;
            }
            
            // Obtener tratamientos adicionales
            $stmt = $this->pdo->prepare("
                SELECT 
                    idtratamiento,
                    medicacion,
                    dosis,
                    frecuencia,
                    duracion
                FROM 
                    tratamiento
                WHERE 
                    idreceta = ?
            ");
            
            $stmt->execute([$receta['idreceta']]);
            $tratamientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Combinar receta principal con tratamientos
            $receta['tratamientos'] = $tratamientos;
            
            return $receta;
        } catch (Exception $e) {
            error_log("Error al obtener receta por consulta: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica si un medicamento está relacionado con alergias del paciente
     * @param int $idpaciente ID del paciente
     * @param string $medicamento Nombre del medicamento
     * @return array Resultado de la verificación (esAlergico, alergias)
     */
    public function verificarAlergiaMedicamento($idpaciente, $medicamento)
    {
        try {
            // 1. Obtener el ID de persona del paciente
            $stmtPersona = $this->pdo->prepare("
                SELECT idpersona FROM pacientes WHERE idpaciente = ?
            ");
            $stmtPersona->execute([$idpaciente]);
            $resultadoPersona = $stmtPersona->fetch(PDO::FETCH_ASSOC);
            
            if (!$resultadoPersona) {
                return [
                    'esAlergico' => false,
                    'alergias' => []
                ];
            }
            
            $idpersona = $resultadoPersona['idpersona'];
            
            // 2. Buscar alergias que coincidan con el medicamento
            $stmt = $this->pdo->prepare("
                SELECT 
                    a.idalergia,
                    a.tipoalergia,
                    a.alergia,
                    la.gravedad
                FROM 
                    listaalergias la
                INNER JOIN 
                    alergias a ON la.idalergia = a.idalergia
                WHERE 
                    la.idpersona = ? AND (
                        a.tipoalergia LIKE '%MEDICAMENTOS%' OR
                        a.tipoalergia LIKE '%FARMACO%' OR
                        a.tipoalergia LIKE '%MEDICINA%'
                    ) AND (
                        UPPER(a.alergia) LIKE UPPER(?) OR
                        UPPER(?) LIKE CONCAT('%', UPPER(a.alergia), '%')
                    )
            ");
            
            $medicamentoParam = "%{$medicamento}%";
            $stmt->execute([$idpersona, $medicamentoParam, $medicamento]);
            $alergias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'esAlergico' => count($alergias) > 0,
                'alergias' => $alergias
            ];
        } catch (Exception $e) {
            error_log("Error al verificar alergia a medicamento: " . $e->getMessage());
            return [
                'esAlergico' => false,
                'alergias' => []
            ];
        }
    }
}
?>