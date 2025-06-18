<?php /*RUTA: sistemaclinica/models/Historialclinica.php*/?>
<?php

require_once 'Conexion.php';

class HistoriaClinica
{
    private $pdo;

    public function __CONSTRUCT()
    {
        $this->pdo = (new Conexion())->getConexion();
    }

    /**
     * Registra una historia clínica para una consulta
     * @param array $datos Datos de la historia clínica
     * @return array Resultado de la operación
     */
    public function registrarConsultaHistoria($datos)
    {
        try {
            $this->pdo->beginTransaction();
            
            // Primero verificamos si ya existe una historia clínica para este paciente
            $stmtVerificar = $this->pdo->prepare("
                SELECT idhistoriaclinica 
                FROM historiaclinica 
                WHERE idconsulta = ?
            ");
            $stmtVerificar->execute([$datos['idconsulta']]);
            $historiaExistente = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
            
            $altamedica = isset($datos['altamedica']) && $datos['altamedica'] == '1' ? true : false;
            
            if ($historiaExistente) {
                // Si ya existe, actualizamos
                $stmt = $this->pdo->prepare("
                    UPDATE historiaclinica 
                    SET 
                        enfermedadactual = ?,
                        examenfisico = ?,
                        evolucion = ?,
                        altamedica = ?,
                        iddiagnostico = ?
                    WHERE idhistoriaclinica = ?
                ");
                
                $stmt->execute([
                    $datos['enfermedadactual'],
                    $datos['examenfisico'],
                    $datos['evolucion'],
                    $altamedica,
                    $datos['iddiagnostico'],
                    $historiaExistente['idhistoriaclinica']
                ]);
                
                $idhistoriaclinica = $historiaExistente['idhistoriaclinica'];
            } else {
                // Si no existe, la creamos
                $stmt = $this->pdo->prepare("
                    INSERT INTO historiaclinica (
                        idconsulta, 
                        enfermedadactual, 
                        examenfisico, 
                        evolucion, 
                        altamedica, 
                        iddiagnostico
                    ) VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $datos['idconsulta'],
                    $datos['enfermedadactual'],
                    $datos['examenfisico'],
                    $datos['evolucion'],
                    $altamedica,
                    $datos['iddiagnostico']
                ]);
                
                $idhistoriaclinica = $this->pdo->lastInsertId();
            }
            
            // Actualizamos la hora de atención en la consulta
            $stmt = $this->pdo->prepare("
                UPDATE consultas
                SET horaatencion = CURRENT_TIME
                WHERE idconsulta = ?
            ");
            $stmt->execute([$datos['idconsulta']]);
            
            $this->pdo->commit();
            
            return [
                'status' => true,
                'mensaje' => 'Historia clínica registrada correctamente',
                'idhistoriaclinica' => $idhistoriaclinica
            ];
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            
            error_log("Error al registrar historia clínica: " . $e->getMessage());
            
            return [
                'status' => false,
                'mensaje' => 'Error al registrar la historia clínica: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene la historia clínica para una consulta específica
     * @param int $idconsulta ID de la consulta
     * @return array|null Datos de la historia clínica o null si no existe
     */
    public function obtenerHistoriaPorConsulta($idconsulta)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    hc.idhistoriaclinica,
                    hc.enfermedadactual,
                    hc.examenfisico,
                    hc.evolucion,
                    hc.altamedica,
                    hc.iddiagnostico,
                    d.nombre AS diagnostico_nombre,
                    d.codigo AS diagnostico_codigo,
                    d.descripcion AS diagnostico_descripcion
                FROM 
                    historiaclinica hc
                LEFT JOIN 
                    diagnosticos d ON hc.iddiagnostico = d.iddiagnostico
                WHERE 
                    hc.idconsulta = ?
            ");
            
            $stmt->execute([$idconsulta]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener historia clínica: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene el historial médico completo de un paciente
     * @param int $idpaciente ID del paciente
     * @return array Lista de historiales clínicos del paciente
     */
    public function obtenerHistorialPaciente($idpaciente)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    hc.idhistoriaclinica,
                    hc.enfermedadactual,
                    hc.examenfisico,
                    hc.evolucion,
                    hc.altamedica,
                    c.fecha,
                    c.horaprogramada,
                    c.horaatencion,
                    d.nombre AS diagnostico_nombre,
                    d.codigo AS diagnostico_codigo,
                    CONCAT(p.apellidos, ', ', p.nombres) AS doctor_nombre,
                    e.especialidad
                FROM 
                    historiaclinica hc
                INNER JOIN 
                    consultas c ON hc.idconsulta = c.idconsulta
                LEFT JOIN 
                    diagnosticos d ON hc.iddiagnostico = d.iddiagnostico
                LEFT JOIN 
                    horarios h ON c.idhorario = h.idhorario
                LEFT JOIN 
                    atenciones a ON h.idatencion = a.idatencion
                LEFT JOIN 
                    contratos co ON a.idcontrato = co.idcontrato
                LEFT JOIN 
                    colaboradores col ON co.idcolaborador = col.idcolaborador
                LEFT JOIN 
                    personas p ON col.idpersona = p.idpersona
                LEFT JOIN 
                    especialidades e ON col.idespecialidad = e.idespecialidad
                WHERE 
                    c.idpaciente = ?
                ORDER BY 
                    c.fecha DESC, c.horaprogramada DESC
            ");
            
            $stmt->execute([$idpaciente]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener historial del paciente: " . $e->getMessage());
            return [];
        }
    }
}
?>