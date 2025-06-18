<?php /*RUTA: sistemaclinica/models/DetalleVenta.php*/?>
<?php
require_once 'Conexion.php';

class DetalleVenta {
    private $pdo;

    public function __CONSTRUCT() {
        $this->pdo = (new Conexion())->getConexion();
    }

    /**
     * Registra un nuevo detalle de venta
     * @param array $datos Datos del detalle de venta
     * @return bool True si se registró correctamente, False en caso contrario
     */
    public function registrarDetalleVenta($datos) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO detalleventas (
                    idventa, idconsulta, idserviciorequerido, precio
                ) VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $datos['idventa'],
                $datos['idconsulta'],
                $datos['idserviciorequerido'] ?? null,
                $datos['precio']
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error al registrar detalle de venta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el estado de la cita asociada a un detalle de venta
     * @param int $idconsulta ID de la consulta
     * @return bool True si se actualizó correctamente, False en caso contrario
     */
    public function actualizarEstadoCita($idconsulta) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE citas c
                INNER JOIN consultas con ON c.fecha = con.fecha AND c.hora = con.horaprogramada
                SET c.estado = 'PROGRAMADA'
                WHERE con.idconsulta = ?
            ");
            $stmt->execute([$idconsulta]);
            return true;
        } catch (Exception $e) {
            error_log("Error al actualizar estado de cita: " . $e->getMessage());
            return false;
        }
    }
}