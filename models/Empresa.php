<?php /*RUTA: sistemaclinica/models/Empresa.php*/?>
<?php
require_once 'Conexion.php';

class Empresa
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    /**
     * Busca una empresa por su RUC
     * @param string $ruc RUC de la empresa
     * @return array|null Datos de la empresa o null si no existe
     */
    public function buscarPorRuc($ruc)
    {
        try {
            $pdo = $this->conexion->getConexion();

            $sql = "
                SELECT 
                    idempresa,
                    razonsocial,
                    ruc,
                    direccion,
                    nombrecomercial,
                    telefono,
                    email
                FROM 
                    empresas
                WHERE 
                    ruc = ?
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ruc]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al buscar empresa: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Registra una nueva empresa
     * @param array $datos Datos de la empresa
     * @return array Resultado de la operación
     */
    public function registrar($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Verificar si la empresa ya existe
            $stmt = $pdo->prepare("SELECT idempresa FROM empresas WHERE ruc = ?");
            $stmt->execute([$datos['ruc']]);
            $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($empresa) {
                // CORRECCIÓN: Devolver un mensaje específico e incluir el idempresa
                // para que el frontend pueda manejarlo adecuadamente
                return [
                    'status' => false,
                    'mensaje' => 'La empresa ya está registrada',
                    'idempresa' => $empresa['idempresa'],
                    'existe' => true // Bandera específica para indicar que existe
                ];
            }

            // SOLUCIÓN: Construir la consulta SQL dinámicamente con solo los campos proporcionados
            $campos = [];
            $valores = [];
            $placeholders = [];

            // Agregar campos obligatorios
            $campos[] = 'razonsocial';
            $valores[] = $datos['razonsocial'];
            $placeholders[] = '?';

            $campos[] = 'ruc';
            $valores[] = $datos['ruc'];
            $placeholders[] = '?';

            // Dirección es un campo que podría ser vacío
            $campos[] = 'direccion';
            $valores[] = isset($datos['direccion']) ? $datos['direccion'] : '';
            $placeholders[] = '?';

            // Agregar campos opcionales solo si están definidos Y tienen un valor no vacío
            if (isset($datos['nombrecomercial']) && is_string($datos['nombrecomercial']) && trim($datos['nombrecomercial']) !== '') {
                $campos[] = 'nombrecomercial';
                $valores[] = $datos['nombrecomercial'];
                $placeholders[] = '?';
            }

            if (isset($datos['telefono']) && is_string($datos['telefono']) && trim($datos['telefono']) !== '') {
                $campos[] = 'telefono';
                $valores[] = $datos['telefono'];
                $placeholders[] = '?';
            }

            if (isset($datos['email']) && is_string($datos['email']) && trim($datos['email']) !== '') {
                $campos[] = 'email';
                $valores[] = $datos['email'];
                $placeholders[] = '?';
            }

            // Crear consulta SQL dinámica
            $sql = "INSERT INTO empresas (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $placeholders) . ")";

            // Registrar para depuración
            error_log("SQL dinámico para inserción de empresa: " . $sql);
            error_log("Valores para inserción: " . print_r($valores, true));

            $stmt = $pdo->prepare($sql);
            $stmt->execute($valores);

            $idempresa = $pdo->lastInsertId();

            return [
                'status' => true,
                'mensaje' => 'Empresa registrada correctamente',
                'idempresa' => $idempresa
            ];
        } catch (Exception $e) {
            error_log("Error al registrar empresa: " . $e->getMessage());

            return [
                'status' => false,
                'mensaje' => 'Error al registrar empresa: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Actualiza los datos de una empresa existente
     * @param int $idempresa ID de la empresa a actualizar
     * @param array $datos Datos actualizados de la empresa
     * @return array Resultado de la operación
     */
    public function actualizar($idempresa, $datos)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Construir la consulta SQL dinámicamente
            $campos = [];
            $valores = [];

            // Agregar campos a actualizar solo si están definidos
            if (isset($datos['razonsocial']) && is_string($datos['razonsocial']) && trim($datos['razonsocial']) !== '') {
                $campos[] = 'razonsocial = ?';
                $valores[] = $datos['razonsocial'];
            }

            if (isset($datos['direccion'])) {
                $campos[] = 'direccion = ?';
                $valores[] = $datos['direccion'];
            }

            if (isset($datos['nombrecomercial']) && is_string($datos['nombrecomercial']) && trim($datos['nombrecomercial']) !== '') {
                $campos[] = 'nombrecomercial = ?';
                $valores[] = $datos['nombrecomercial'];
            }

            if (isset($datos['telefono']) && is_string($datos['telefono']) && trim($datos['telefono']) !== '') {
                $campos[] = 'telefono = ?';
                $valores[] = $datos['telefono'];
            }

            if (isset($datos['email']) && is_string($datos['email']) && trim($datos['email']) !== '') {
                $campos[] = 'email = ?';
                $valores[] = $datos['email'];
            }

            // Si no hay campos para actualizar, retornar éxito
            if (empty($campos)) {
                return [
                    'status' => true,
                    'mensaje' => 'No hay cambios para actualizar',
                    'idempresa' => $idempresa
                ];
            }

            // Agregar ID de empresa para la cláusula WHERE
            $valores[] = $idempresa;

            // Crear consulta SQL
            $sql = "UPDATE empresas SET " . implode(', ', $campos) . " WHERE idempresa = ?";

            error_log("SQL actualizar empresa: " . $sql);
            error_log("Valores para actualizar: " . print_r($valores, true));

            $stmt = $pdo->prepare($sql);
            $stmt->execute($valores);

            // Verificar si se realizó algún cambio
            if ($stmt->rowCount() > 0) {
                return [
                    'status' => true,
                    'mensaje' => 'Empresa actualizada correctamente',
                    'idempresa' => $idempresa
                ];
            } else {
                return [
                    'status' => true,
                    'mensaje' => 'No se realizaron cambios en la empresa',
                    'idempresa' => $idempresa
                ];
            }
        } catch (Exception $e) {
            error_log("Error al actualizar empresa: " . $e->getMessage());

            return [
                'status' => false,
                'mensaje' => 'Error al actualizar empresa: ' . $e->getMessage()
            ];
        }
    }
}
