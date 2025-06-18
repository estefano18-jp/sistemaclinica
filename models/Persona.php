<?php /*RUTA: sistemaclinica/models/Persona.php*/?>
<?php

require_once 'Conexion.php';

class Persona {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    /**
     * Busca una persona por su número de documento
     * @param string $nrodoc Número de documento a buscar
     * @return array|false Datos de la persona o false si no existe
     */
    public function buscarPersonaPorDocumento($nrodoc) {
        try {
            $pdo = $this->conexion->getConexion();
            $stmt = $pdo->prepare("CALL sp_buscar_persona_por_documento(?)");
            $stmt->execute([$nrodoc]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die("Error al buscar persona por documento: " . $e->getMessage());
        }
    }
}
?>