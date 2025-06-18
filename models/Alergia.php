<?php /*RUTA: sistemaclinica/models/Alergia.php*/?>
<?php

require_once 'Conexion.php';

class Alergia extends Conexion
{
    private $pdo;

    public function __CONSTRUCT()
    {
        $this->pdo = parent::getConexion();
    }

    /**
     * Método para registrar una nueva alergia
     * @param array $params Parámetros de la alergia (tipoalergia, alergia)
     * @return int ID de la alergia registrada o -1 en caso de error
     */
    public function registrar($params = []): int
    {
        $idalergia = -1;
        try {
            // Validar parámetros
            if (empty($params['tipoalergia']) || empty($params['alergia'])) {
                return -1;
            }
            
            // Normalizar los datos
            $tipoAlergia = strtoupper(trim($params['tipoalergia']));
            $alergia = trim($params['alergia']);
            
            // Primero verificamos si ya existe la alergia
            $queryVerificar = $this->pdo->prepare("
                SELECT idalergia 
                FROM alergias 
                WHERE UPPER(tipoalergia) = UPPER(?) AND UPPER(alergia) = UPPER(?)
            ");
            $queryVerificar->execute([$tipoAlergia, $alergia]);
            $resultado = $queryVerificar->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                // Si ya existe, retornar ese ID
                return $resultado['idalergia'];
            }
            
            // Si no existe, registrarla
            $query = $this->pdo->prepare("CALL spu_alergias_registrar(?, ?)");
            $query->execute([$tipoAlergia, $alergia]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($result && isset($result['idalergia'])) {
                $idalergia = $result['idalergia'];
            }
        } catch (Exception $e) {
            error_log('Error al registrar alergia: ' . $e->getMessage());
            $idalergia = -1;
        }
        return $idalergia;
    }

    /**
     * Método para registrar una alergia a un paciente
     * @param array $params Parámetros (idpersona, idalergia, gravedad)
     * @return int ID de la relación registrada o -1 en caso de error
     */
    public function registrarAlergiaAPaciente($params = []): int
    {
        $idlistaalergia = -1;
        try {
            // Validar parámetros
            if (!isset($params['idpersona']) || !isset($params['idalergia']) || !isset($params['gravedad'])) {
                return -1;
            }
            
            // Verificar si ya existe esta relación
            if ($this->verificarRelacionAlergiaPersonaExistente($params['idpersona'], $params['idalergia'])) {
                return -2; // Código especial para indicar duplicado
            }
            
            // Normalizar la gravedad
            $gravedad = strtoupper(trim($params['gravedad']));
            
            // Validar que la gravedad sea uno de los valores aceptados
            $gravedadesPermitidas = ['LEVE', 'MODERADA', 'GRAVE'];
            if (!in_array($gravedad, $gravedadesPermitidas)) {
                return -1;
            }
            
            $query = $this->pdo->prepare("CALL spu_paciente_alergia_registrar(?, ?, ?)");
            $query->execute([
                $params['idpersona'],
                $params['idalergia'],
                $gravedad
            ]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($result && isset($result['idlistaalergia'])) {
                $idlistaalergia = $result['idlistaalergia'];
            }
        } catch (Exception $e) {
            error_log('Error al registrar alergia a paciente: ' . $e->getMessage());
            $idlistaalergia = -1;
        }
        return $idlistaalergia;
    }

    /**
     * Verifica si ya existe una relación entre una persona y una alergia
     * @param int $idpersona ID de la persona
     * @param int $idalergia ID de la alergia
     * @return bool True si ya existe la relación, False en caso contrario
     */
    private function verificarRelacionAlergiaPersonaExistente($idpersona, $idalergia)
    {
        try {
            // Validar parámetros
            if (empty($idpersona) || empty($idalergia)) {
                return false;
            }
            
            $query = $this->pdo->prepare("
                SELECT COUNT(*) AS total 
                FROM listaalergias 
                WHERE idpersona = ? AND idalergia = ?
            ");
            $query->execute([$idpersona, $idalergia]);
            $resultado = $query->fetch(PDO::FETCH_ASSOC);
            
            return (int)$resultado['total'] > 0;
        } catch (Exception $e) {
            error_log('Error al verificar relación alergia-persona: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Método para listar todas las alergias
     * @return array Lista de alergias
     */
    public function listar(): array
    {
        try {
            $query = $this->pdo->prepare("CALL spu_alergias_listar()");
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error al listar alergias: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Método para listar alergias por tipo
     * @param string $tipoalergia Tipo de alergia a buscar
     * @return array Lista de alergias del tipo especificado
     */
    public function listarPorTipo($tipoalergia): array
    {
        try {
            // Normalizar el tipo de alergia
            $tipoAlergia = strtoupper(trim($tipoalergia));
            
            $query = $this->pdo->prepare("CALL spu_alergias_listar_por_tipo(?)");
            $query->execute([$tipoAlergia]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error al listar alergias por tipo: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Método para listar alergias de un paciente específico
     * @param int $idpersona ID de la persona/paciente
     * @return array Lista de alergias del paciente
     */
    public function listarAlergiasPaciente($idpersona): array
    {
        try {
            if (empty($idpersona)) {
                return [];
            }
            
            $query = $this->pdo->prepare("CALL spu_paciente_alergias_listar(?)");
            $query->execute([$idpersona]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error al listar alergias de paciente: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Método para obtener una alergia por su ID
     * @param int $idalergia ID de la alergia
     * @return array Datos de la alergia o array vacío si no se encuentra
     */
    public function obtenerPorId($idalergia): array
    {
        try {
            if (empty($idalergia)) {
                return [];
            }
            
            $query = $this->pdo->prepare("CALL spu_alergias_obtener_por_id(?)");
            $query->execute([$idalergia]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (Exception $e) {
            error_log('Error al obtener alergia por ID: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Método para actualizar una alergia
     * @param array $params Parámetros de la alergia (idalergia, tipoalergia, alergia)
     * @return int ID de la alergia actualizada o -1 en caso de error
     */
    public function actualizar($params = []): int
    {
        try {
            // Validar parámetros
            if (!isset($params['idalergia']) || empty($params['tipoalergia']) || empty($params['alergia'])) {
                return -1;
            }
            
            // Normalizar los datos
            $tipoAlergia = strtoupper(trim($params['tipoalergia']));
            $alergia = trim($params['alergia']);
            
            $query = $this->pdo->prepare("CALL spu_alergias_actualizar(?, ?, ?)");
            $query->execute([
                $params['idalergia'],
                $tipoAlergia,
                $alergia
            ]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($result && isset($result['idalergia'])) {
                return $result['idalergia'];
            }
            
            return -1;
        } catch (Exception $e) {
            error_log('Error al actualizar alergia: ' . $e->getMessage());
            return -1;
        }
    }

    /**
     * Método para actualizar la gravedad de una alergia de un paciente
     * @param int $idlistaalergia ID de la relación entre paciente y alergia
     * @param string $gravedad Nueva gravedad (LEVE, MODERADA, GRAVE)
     * @return int ID de la relación actualizada o -1 en caso de error
     */
    public function actualizarGravedad($idlistaalergia, $gravedad): int
    {
        try {
            // Validar parámetros
            if (empty($idlistaalergia) || empty($gravedad)) {
                return -1;
            }
            
            // Normalizar la gravedad
            $gravedad = strtoupper(trim($gravedad));
            
            // Validar que la gravedad sea uno de los valores aceptados
            $gravedadesPermitidas = ['LEVE', 'MODERADA', 'GRAVE'];
            if (!in_array($gravedad, $gravedadesPermitidas)) {
                return -1;
            }
            
            $query = $this->pdo->prepare("CALL spu_paciente_alergia_actualizar(?, ?)");
            $query->execute([$idlistaalergia, $gravedad]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($result && isset($result['idlistaalergia'])) {
                return $result['idlistaalergia'];
            }
            
            return -1;
        } catch (Exception $e) {
            error_log('Error al actualizar gravedad: ' . $e->getMessage());
            return -1;
        }
    }

    /**
     * Método para eliminar una alergia
     * @param int $idalergia ID de la alergia a eliminar
     * @return array Resultado de la operación (eliminado, mensaje)
     */
    public function eliminar($idalergia): array
    {
        try {
            // Validar parámetros
            if (empty($idalergia)) {
                return [
                    'eliminado' => 0,
                    'mensaje' => 'ID de alergia no válido'
                ];
            }
            
            $query = $this->pdo->prepare("CALL spu_alergias_eliminar(?)");
            $query->execute([$idalergia]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result;
            }
            
            return [
                'eliminado' => 0,
                'mensaje' => 'Error al eliminar la alergia'
            ];
        } catch (Exception $e) {
            error_log('Error al eliminar alergia: ' . $e->getMessage());
            return [
                'eliminado' => 0,
                'mensaje' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Método para eliminar una alergia de un paciente
     * @param int $idlistaalergia ID de la relación a eliminar
     * @return array Resultado de la operación (eliminado, mensaje)
     */
    public function eliminarAlergiaPaciente($idlistaalergia): array
    {
        try {
            // Validar parámetros
            if (empty($idlistaalergia)) {
                return [
                    'eliminado' => 0,
                    'mensaje' => 'ID de lista de alergia no válido'
                ];
            }
            
            $query = $this->pdo->prepare("CALL spu_paciente_alergia_eliminar(?)");
            $query->execute([$idlistaalergia]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result;
            }
            
            return [
                'eliminado' => 0,
                'mensaje' => 'Error al eliminar la alergia del paciente'
            ];
        } catch (Exception $e) {
            error_log('Error al eliminar alergia de paciente: ' . $e->getMessage());
            return [
                'eliminado' => 0,
                'mensaje' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Método para buscar alergias por nombre o tipo
     * @param string $busqueda Término de búsqueda
     * @return array Lista de alergias que coinciden con la búsqueda
     */
    public function buscar($busqueda): array
    {
        try {
            // Validar parámetros
            if (empty($busqueda)) {
                return [];
            }
            
            // Normalizar la búsqueda
            $busquedaNormalizada = trim($busqueda);
            
            $query = $this->pdo->prepare("CALL spu_alergias_buscar(?)");
            $query->execute([$busquedaNormalizada]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error al buscar alergias: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Elimina todas las alergias asociadas a una persona
     * @param int $idpersona ID de la persona
     * @return int Número de registros eliminados
     */
    public function eliminarAlergiasPorPersona($idpersona)
    {
        try {
            // Validar parámetros
            if (empty($idpersona)) {
                return 0;
            }
            
            $query = $this->pdo->prepare("
                DELETE FROM listaalergias 
                WHERE idpersona = ?
            ");

            $query->execute([$idpersona]);
            return $query->rowCount();
        } catch (Exception $e) {
            error_log('Error al eliminar alergias por persona: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Método para eliminar todas las alergias de un paciente
     * @param int $idpersona ID de la persona
     * @return array Resultado de la operación
     */
    public function eliminarTodasAlergiasPaciente($idpersona)
    {
        try {
            // Validar parámetros
            if (empty($idpersona)) {
                return [
                    'status' => false,
                    'mensaje' => 'ID de persona no válido'
                ];
            }
            
            // Primero eliminamos todas las relaciones de este paciente con alergias
            $consulta = $this->pdo->prepare("CALL spu_paciente_alergias_eliminar_todas(?)");
            $consulta->execute([$idpersona]);

            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

            // Luego limpiamos las alergias huérfanas
            $this->limpiarAlergiasHuerfanas();

            if ($resultado) {
                return [
                    'status' => true,
                    'eliminados' => $resultado['eliminados'] ?? 0,
                    'mensaje' => $resultado['mensaje'] ?? 'Alergias del paciente eliminadas correctamente'
                ];
            }
            
            return [
                'status' => true,
                'eliminados' => 0,
                'mensaje' => 'Alergias del paciente eliminadas correctamente'
            ];
        } catch (PDOException $e) {
            error_log('Error al eliminar todas las alergias de paciente: ' . $e->getMessage());
            return [
                'status' => false,
                'mensaje' => 'Error al eliminar las alergias del paciente: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verifica si una alergia ya existe para una persona específica
     * @param int $idpersona ID de la persona
     * @param string $tipoalergia Tipo de alergia
     * @param string $alergia Nombre de la alergia
     * @return bool True si la alergia ya existe, False en caso contrario
     */
    public function verificarAlergiaExistente($idpersona, $tipoalergia, $alergia)
    {
        try {
            // Validar parámetros
            if (empty($idpersona) || empty($tipoalergia) || empty($alergia)) {
                return false;
            }
            
            // Normalizar los datos para evitar problemas de case-sensitivity
            $tipoalergia = strtoupper(trim($tipoalergia));
            $alergia = trim($alergia);
            
            $query = $this->pdo->prepare("
                SELECT COUNT(*) AS total 
                FROM listaalergias la
                INNER JOIN alergias a ON la.idalergia = a.idalergia
                WHERE la.idpersona = ? AND UPPER(a.tipoalergia) = ? AND UPPER(a.alergia) = UPPER(?)
            ");

            $query->execute([$idpersona, $tipoalergia, $alergia]);
            $resultado = $query->fetch(PDO::FETCH_ASSOC);

            return (int)$resultado['total'] > 0;
        } catch (Exception $e) {
            error_log('Error al verificar alergia existente: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Método para limpiar alergias huérfanas (que no están asociadas a ningún paciente)
     * @return array Resultado de la operación
     */
    public function limpiarAlergiasHuerfanas()
    {
        try {
            $consulta = $this->pdo->prepare("CALL spu_alergias_limpiar_huerfanas()");
            $consulta->execute();

            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                return [
                    "status" => true,
                    "eliminadas" => $resultado['eliminadas'] ?? 0,
                    "mensaje" => $resultado['mensaje'] ?? 'Alergias huérfanas eliminadas correctamente'
                ];
            }
            
            return [
                "status" => true,
                "eliminadas" => 0,
                "mensaje" => 'Alergias huérfanas eliminadas correctamente'
            ];
        } catch (Exception $e) {
            error_log('Error al limpiar alergias huérfanas: ' . $e->getMessage());
            return [
                "status" => false,
                "mensaje" => "Error al limpiar alergias huérfanas: " . $e->getMessage()
            ];
        }
    }

    /**
     * Método para eliminar completamente una alergia (relación y registro)
     * @param int $idlistaalergia ID de la relación a eliminar
     * @return array Resultado de la operación
     */
    public function eliminarAlergiaCompleta($idlistaalergia): array
    {
        try {
            // Validar parámetros
            if (empty($idlistaalergia)) {
                return [
                    'eliminado' => 0,
                    'mensaje' => 'ID de lista de alergia no válido'
                ];
            }
            
            $query = $this->pdo->prepare("CALL spu_eliminar_alergia_completa(?)");
            $query->execute([$idlistaalergia]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result;
            }
            
            return [
                'eliminado' => 0,
                'mensaje' => 'Error al eliminar la alergia'
            ];
        } catch (Exception $e) {
            error_log('Error al eliminar alergia completa: ' . $e->getMessage());
            return [
                'eliminado' => 0,
                'mensaje' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ];
        }
    }
     
}
?>