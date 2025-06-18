<?php /*RUTA: sistemaclinica/models/Conexion.php*/?>
<?php 

class Conexion {

    // Almacenamos los datos de conexión
    private $servidor = "localhost";  // Dirección del servidor de base de datos
    private $puerto = "3306";  // Puerto de conexión (por defecto es 3306 para MySQL)
    private $baseDatos = "clinicaDB";  // Nombre de la base de datos
    private $usuario = "root";  // Usuario para la base de datos
    private $clave = "";  // Contraseña para la base de datos

    // Método para obtener la conexión
    public function getConexion() {
        $pdo = null;
        try {
            // Establecer la conexión utilizando PDO
            $pdo = new PDO(
                "mysql:host={$this->servidor};port={$this->puerto};dbname={$this->baseDatos};charset=UTF8", 
                $this->usuario, 
                $this->clave
            );
            // Configurar el modo de error para lanzar excepciones
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;  // Retorna la conexión PDO
        } catch (Exception $e) {
            // Si ocurre un error en la conexión, lo mostramos
            die("Error en la conexión: " . $e->getMessage());
        } finally {
            // Aunque no es necesario, aquí podemos cerrar la conexión si lo deseamos
            if ($pdo) {
                $pdo = null;
            }
        }
    }
}
?>
