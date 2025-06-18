<?php /*RUTA: sistemaclinica/models/Colaborador.php*/ ?>
<?php

require_once 'Conexion.php';

class Colaborador
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    /**
     * Registra la información profesional de un doctor/colaborador
     * @param array $datos Datos profesionales del colaborador
     * @return array Resultado de la operación y mensaje
     */
    /**
     * Registra la información profesional de un doctor/colaborador
     * @param array $datos Datos profesionales del colaborador
     * @return array Resultado de la operación y mensaje
     */
    public function registrarColaboradorProfesional($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Verificar si ya existe como colaborador
            $stmtVerificar = $pdo->prepare("
            SELECT idcolaborador FROM colaboradores WHERE idpersona = ?
        ");
            $stmtVerificar->execute([$datos['idpersona']]);
            $colaboradorExistente = $stmtVerificar->fetch(PDO::FETCH_ASSOC);

            if ($colaboradorExistente) {
                // Actualizar colaborador existente
                $stmt = $pdo->prepare("
                UPDATE colaboradores 
                SET idespecialidad = ?
                WHERE idcolaborador = ?
            ");

                $stmt->execute([
                    $datos['idespecialidad'],
                    $colaboradorExistente['idcolaborador']
                ]);

                return [
                    'status' => true,
                    'mensaje' => 'Información profesional del doctor actualizada correctamente',
                    'idcolaborador' => $colaboradorExistente['idcolaborador']
                ];
            } else {
                // Realizar la inserción directamente en la tabla colaboradores
                $stmt = $pdo->prepare("
                INSERT INTO colaboradores (idpersona, idespecialidad)
                VALUES (?, ?)
            ");

                $stmt->execute([
                    $datos['idpersona'],
                    $datos['idespecialidad']
                ]);

                $idcolaborador = $pdo->lastInsertId();

                return [
                    'status' => true,
                    'mensaje' => 'Información profesional del doctor registrada correctamente',
                    'idcolaborador' => $idcolaborador
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => false,
                'mensaje' => "Error al registrar información profesional: " . $e->getMessage(),
                'idcolaborador' => null
            ];
        }
    }

    /**
     * Obtiene las especialidades disponibles para asignar a un colaborador
     * @return array Lista de especialidades
     */
    public function obtenerEspecialidades()
    {
        try {
            $pdo = $this->conexion->getConexion();
            // Solo obtener especialidades activas
            $stmt = $pdo->query("SELECT idespecialidad, especialidad FROM especialidades WHERE estado = 'ACTIVO'");

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener especialidades: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene información de un colaborador específico
     * @param int $idColaborador ID del colaborador
     * @return array Datos del colaborador
     */
    public function obtenerColaboradorPorId($idColaborador)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $stmt = $pdo->prepare("CALL sp_obtener_colaborador_por_id(?)");
            $stmt->execute([$idColaborador]);

            $colaborador = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $colaborador;
        } catch (Exception $e) {
            die("Error al obtener colaborador: " . $e->getMessage());
        }
    }
}
