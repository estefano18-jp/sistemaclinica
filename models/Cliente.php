<?php /*RUTA: sistemaclinica/models/Cliente.php*/ ?>
<?php
require_once 'Conexion.php';

class Cliente
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    /**
     * Busca un cliente por su número de documento
     * @param string $tipoDoc Tipo de documento
     * @param string $nroDoc Número de documento
     * @return array|null Datos del cliente o null si no existe
     */
    public function buscarPorDocumento($tipoDoc, $nroDoc)
    {
        try {
            $pdo = $this->conexion->getConexion();

            $sql = "
                SELECT 
                    c.idcliente,
                    c.tipocliente,
                    c.idempresa,
                    c.idpersona,
                    p.apellidos,
                    p.nombres,
                    p.tipodoc,
                    p.nrodoc,
                    p.telefono,
                    p.email,
                    p.direccion
                FROM 
                    clientes c
                INNER JOIN 
                    personas p ON c.idpersona = p.idpersona
                WHERE 
                    p.tipodoc = ? AND p.nrodoc = ?
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tipoDoc, $nroDoc]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al buscar cliente: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Registra un nuevo cliente
     * CORRECCIÓN COMPLETA: Asegura que solo se insertan los campos proporcionados
     * @param array $datos Datos del cliente
     * @return array Resultado de la operación
     */
    /**
     * Registra un nuevo cliente
     * Versión completamente reescrita para máxima robustez
     * @param array $datos Datos del cliente
     * @return array Resultado de la operación
     */
    public function registrar($datos)
    {
        $pdo = null;
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // Logging detallado para diagnóstico
            error_log("MODELO - Registrando cliente natural con datos: " . print_r($datos, true));

            // CORRECCIÓN: Validar nuevamente que los datos obligatorios estén presentes
            if (
                empty($datos['nombres']) || empty($datos['apellidos']) ||
                empty($datos['tipodoc']) || empty($datos['nrodoc'])
            ) {
                throw new Exception("Faltan datos obligatorios para registrar cliente");
            }

            // Verificar si la persona ya existe
            $stmt = $pdo->prepare("SELECT idpersona FROM personas WHERE tipodoc = ? AND nrodoc = ?");
            $stmt->execute([$datos['tipodoc'], $datos['nrodoc']]);
            $persona = $stmt->fetch(PDO::FETCH_ASSOC);

            $idpersona = null;

            if (!$persona) {
                // CORRECCIÓN CRUCIAL: Crear la persona con una consulta SQL más simple y segura
                // Primero, determinar los campos y valores a insertar
                $camposPersona = ['apellidos', 'nombres', 'tipodoc', 'nrodoc'];
                $valoresPersona = [
                    $datos['apellidos'],
                    $datos['nombres'],
                    $datos['tipodoc'],
                    $datos['nrodoc']
                ];

                // Agregar campos opcionales si existen
                $camposOpcionales = ['telefono', 'fechanacimiento', 'genero', 'direccion', 'email'];
                foreach ($camposOpcionales as $campo) {
                    if (!empty($datos[$campo])) {
                        $camposPersona[] = $campo;
                        $valoresPersona[] = $datos[$campo];
                    }
                }

                // Construir placeholders para la consulta
                $placeholders = rtrim(str_repeat('?,', count($valoresPersona)), ',');

                // Construir la consulta SQL
                $sql = "INSERT INTO personas (" . implode(',', $camposPersona) . ") VALUES (" . $placeholders . ")";

                error_log("SQL persona: " . $sql);
                error_log("Valores persona: " . print_r($valoresPersona, true));

                try {
                    $stmt = $pdo->prepare($sql);
                    $result = $stmt->execute($valoresPersona);

                    if (!$result) {
                        $errorInfo = $stmt->errorInfo();
                        error_log("Error SQL al insertar persona: " . json_encode($errorInfo));
                        throw new Exception("Error al insertar persona: " . ($errorInfo[2] ?? "Error desconocido"));
                    }

                    $idpersona = $pdo->lastInsertId();
                    error_log("Persona creada con ID: " . $idpersona);

                    // Establecer campos nulos explícitamente si es necesario
                    $this->establecerCamposNulos($idpersona);
                } catch (Exception $e) {
                    error_log("Excepción al insertar persona: " . $e->getMessage());
                    throw new Exception("Error al registrar persona: " . $e->getMessage());
                }
            } else {
                $idpersona = $persona['idpersona'];
                error_log("Persona existente encontrada con ID: " . $idpersona);
            }

            // Verificar si ya existe como cliente
            $stmt = $pdo->prepare("SELECT idcliente FROM clientes WHERE idpersona = ?");
            $stmt->execute([$idpersona]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cliente) {
                // CORRECCIÓN: Usar el tipo de cliente proporcionado o NATURAL por defecto
                $tipocliente = isset($datos['tipocliente']) ? $datos['tipocliente'] : 'NATURAL';

                try {
                    $stmt = $pdo->prepare("INSERT INTO clientes (tipocliente, idpersona) VALUES (?, ?)");

                    $result = $stmt->execute([$tipocliente, $idpersona]);

                    if (!$result) {
                        $errorInfo = $stmt->errorInfo();
                        error_log("Error SQL al insertar cliente: " . json_encode($errorInfo));
                        throw new Exception("Error al insertar cliente: " . ($errorInfo[2] ?? "Error desconocido"));
                    }

                    $idcliente = $pdo->lastInsertId();
                    error_log("Cliente $tipocliente creado con ID: " . $idcliente);
                } catch (Exception $e) {
                    error_log("Excepción al insertar cliente: " . $e->getMessage());
                    throw new Exception("Error al registrar cliente: " . $e->getMessage());
                }
            } else {
                $idcliente = $cliente['idcliente'];
                error_log("Cliente existente encontrado con ID: " . $idcliente);
            }

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Cliente registrado correctamente',
                'idcliente' => $idcliente,
                'idpersona' => $idpersona
            ];
        } catch (Exception $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("ERROR COMPLETO al registrar cliente: " . $e->getMessage());
            error_log("Traza completa: " . $e->getTraceAsString());

            return [
                'status' => false,
                'mensaje' => 'Error al registrar cliente: ' . $e->getMessage()
            ];
        }
    }


    /**
     * MÉTODO NUEVO: Establece explícitamente como NULL los campos no proporcionados
     * Esto es necesario porque algunos campos pueden tener valores predeterminados en la BD
     * @param int $idpersona ID de la persona a actualizar
     */
    private function establecerCamposNulos($idpersona)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Obtener los campos actuales de la persona
            $stmt = $pdo->prepare("SELECT * FROM personas WHERE idpersona = ?");
            $stmt->execute([$idpersona]);
            $persona = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar si hay campos que deben ser NULL o vacíos
            $camposAActualizar = [];
            $valores = [];

            // Lista de campos que podrían tener valores por defecto no deseados
            $camposParaVerificar = [
                'fechanacimiento' => '0000-00-00',
                'genero' => 'M',
                'telefono' => '000000000',
                'direccion' => '',
                'email' => ''
            ];

            foreach ($camposParaVerificar as $campo => $valorNoDeseado) {
                // Si el campo está establecido a un valor por defecto no deseado
                if (isset($persona[$campo]) && ($persona[$campo] == $valorNoDeseado || $persona[$campo] === '0000-00-00')) {
                    $camposAActualizar[] = "$campo = NULL";
                    error_log("Campo $campo tiene valor por defecto, se establecerá como NULL");
                }
            }

            // Si hay campos para actualizar, ejecutar la consulta
            if (!empty($camposAActualizar)) {
                $sql = "UPDATE personas SET " . implode(', ', $camposAActualizar) . " WHERE idpersona = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$idpersona]);

                error_log("Campos actualizados a NULL para persona ID: $idpersona");
            }
        } catch (Exception $e) {
            error_log("Error al establecer campos nulos: " . $e->getMessage());
            // No lanzamos la excepción para que no interrumpa el flujo principal
        }
    }

    /**
     * Registra un cliente empresa
     * @param array $datos Datos de la empresa
     * @return array Resultado de la operación
     */
    public function registrarEmpresa($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // Verificar si la empresa ya existe
            $stmt = $pdo->prepare("SELECT idempresa FROM empresas WHERE ruc = ?");
            $stmt->execute([$datos['ruc']]);
            $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

            $idempresa = null;

            if (!$empresa) {
                // Insertar nueva empresa
                $stmt = $pdo->prepare("
                    INSERT INTO empresas (
                        razonsocial, ruc, direccion, 
                        nombrecomercial, telefono, email
                    ) VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $datos['razonsocial'],
                    $datos['ruc'],
                    $datos['direccion'],
                    $datos['nombrecomercial'] ?? $datos['razonsocial'],
                    $datos['telefono'],
                    $datos['email']
                ]);

                $idempresa = $pdo->lastInsertId();
            } else {
                $idempresa = $empresa['idempresa'];
            }

            // Verificar si ya existe como cliente
            $stmt = $pdo->prepare("SELECT idcliente FROM clientes WHERE idempresa = ?");
            $stmt->execute([$idempresa]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cliente) {
                // Registrar como cliente
                $stmt = $pdo->prepare("
                    INSERT INTO clientes (tipocliente, idempresa)
                    VALUES ('EMPRESA', ?)
                ");

                $stmt->execute([$idempresa]);
                $idcliente = $pdo->lastInsertId();
            } else {
                $idcliente = $cliente['idcliente'];
            }

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Cliente empresa registrado correctamente',
                'idcliente' => $idcliente,
                'idempresa' => $idempresa
            ];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("Error al registrar cliente empresa: " . $e->getMessage());

            return [
                'status' => false,
                'mensaje' => 'Error al registrar cliente empresa: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Actualiza un cliente para asociarlo con una persona
     * Agregar este método a la clase Cliente
     * @param int $idcliente ID del cliente
     * @param int $idpersona ID de la persona
     * @return array Resultado de la operación
     */
    public function actualizarClientePersona($idcliente, $idpersona)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Verificar que el cliente exista
            $stmt = $pdo->prepare("SELECT idcliente FROM clientes WHERE idcliente = ?");
            $stmt->execute([$idcliente]);
            if (!$stmt->fetch()) {
                return [
                    'status' => false,
                    'mensaje' => 'Cliente no encontrado'
                ];
            }

            // Verificar que la persona exista
            $stmt = $pdo->prepare("SELECT idpersona FROM personas WHERE idpersona = ?");
            $stmt->execute([$idpersona]);
            if (!$stmt->fetch()) {
                return [
                    'status' => false,
                    'mensaje' => 'Persona no encontrada'
                ];
            }

            // Actualizar el cliente
            $stmt = $pdo->prepare("UPDATE clientes SET idpersona = ? WHERE idcliente = ?");
            $stmt->execute([$idpersona, $idcliente]);

            return [
                'status' => true,
                'mensaje' => 'Cliente actualizado correctamente'
            ];
        } catch (Exception $e) {
            error_log("Error al actualizar cliente con persona: " . $e->getMessage());

            return [
                'status' => false,
                'mensaje' => 'Error al actualizar cliente: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Busca un cliente por su ID de persona
     * @param int $idpersona ID de la persona
     * @return array|null Datos del cliente o null si no existe
     */
    public function buscarPorPersona($idpersona)
    {
        try {
            $pdo = $this->conexion->getConexion();

            $sql = "
                SELECT 
                    c.idcliente,
                    c.tipocliente,
                    c.idempresa,
                    c.idpersona,
                    p.apellidos,
                    p.nombres,
                    p.tipodoc,
                    p.nrodoc,
                    p.telefono,
                    p.email,
                    p.direccion
                FROM 
                    clientes c
                INNER JOIN 
                    personas p ON c.idpersona = p.idpersona
                WHERE 
                    c.idpersona = ?
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idpersona]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al buscar cliente por persona: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Registra un cliente con una persona o empresa existente - VERSIÓN CORREGIDA
     * @param array $datos Datos del cliente
     * @return array Resultado de la operación
     */
    public function registrarConPersonaExistente($datos)
    {
        try {
            $pdo = $this->conexion->getConexion();
            $pdo->beginTransaction();

            // Log detallado para depuración
            error_log("Modelo - registrarConPersonaExistente - Datos: " . print_r($datos, true));

            // MODIFICACIÓN CLAVE: Permitir idpersona aunque el tipocliente sea EMPRESA
            // para casos de facturación donde necesitamos ambos datos

            // Validaciones básicas
            if (!isset($datos['tipocliente']) || empty($datos['tipocliente'])) {
                throw new Exception("Se requiere especificar el tipo de cliente");
            }

            if ($datos['tipocliente'] === 'EMPRESA' && (!isset($datos['idempresa']) || empty($datos['idempresa']))) {
                throw new Exception("Para clientes tipo EMPRESA se requiere un ID de empresa");
            }

            if ($datos['tipocliente'] === 'NATURAL' && (!isset($datos['idpersona']) || empty($datos['idpersona']))) {
                throw new Exception("Para clientes tipo NATURAL se requiere un ID de persona");
            }

            // Construir la consulta base
            $sql = "INSERT INTO clientes (tipocliente";
            $valores = [$datos['tipocliente']];
            $placeholders = "?";

            // Siempre agregar idempresa si está presente, independientemente del tipo
            if (isset($datos['idempresa']) && !empty($datos['idempresa'])) {
                $sql .= ", idempresa";
                $valores[] = $datos['idempresa'];
                $placeholders .= ", ?";
            }

            // CAMBIO FUNDAMENTAL: Siempre agregar idpersona si está presente, independientemente del tipo
            // Esto permitirá mantener la relación con la persona incluso para clientes EMPRESA
            if (isset($datos['idpersona']) && !empty($datos['idpersona'])) {
                $sql .= ", idpersona";
                $valores[] = $datos['idpersona'];
                $placeholders .= ", ?";
            }

            // Completar SQL
            $sql .= ") VALUES (" . $placeholders . ")";

            error_log("SQL a ejecutar: " . $sql);
            error_log("Valores: " . print_r($valores, true));

            // Ejecutar la consulta
            $stmt = $pdo->prepare($sql);
            $stmt->execute($valores);

            $idcliente = $pdo->lastInsertId();

            $pdo->commit();

            return [
                'status' => true,
                'mensaje' => 'Cliente registrado correctamente',
                'idcliente' => $idcliente
            ];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("Error al registrar cliente con persona existente: " . $e->getMessage());

            return [
                'status' => false,
                'mensaje' => 'Error al registrar cliente: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Busca un cliente por su ID de empresa
     * @param int $idempresa ID de la empresa
     * @return array|null Datos del cliente o null si no existe
     */
    public function buscarPorEmpresa($idempresa)
    {
        try {
            $pdo = $this->conexion->getConexion();

            $sql = "
            SELECT 
                c.idcliente,
                c.tipocliente,
                c.idempresa,
                c.idpersona,
                e.razonsocial,
                e.ruc,
                e.direccion,
                e.nombrecomercial,
                e.telefono,
                e.email
            FROM 
                clientes c
            INNER JOIN 
                empresas e ON c.idempresa = e.idempresa
            WHERE 
                c.idempresa = ?
                AND c.tipocliente = 'EMPRESA'
        ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idempresa]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al buscar cliente por empresa: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Actualiza un cliente existente para asociarlo con una empresa
     * @param int $idcliente ID del cliente a actualizar
     * @param int $idempresa ID de la empresa a asociar
     * @return array Resultado de la operación
     */
    public function actualizarClienteEmpresa($idcliente, $idempresa)
    {
        try {
            $pdo = $this->conexion->getConexion();

            // Verificar que el cliente exista
            $stmt = $pdo->prepare("SELECT idcliente, idpersona FROM clientes WHERE idcliente = ?");
            $stmt->execute([$idcliente]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$cliente) {
                return [
                    'status' => false,
                    'mensaje' => 'Cliente no encontrado'
                ];
            }

            // Verificar que la empresa exista
            $stmt = $pdo->prepare("SELECT idempresa FROM empresas WHERE idempresa = ?");
            $stmt->execute([$idempresa]);
            if (!$stmt->fetch()) {
                return [
                    'status' => false,
                    'mensaje' => 'Empresa no encontrada'
                ];
            }

            // Actualizar el cliente con la empresa y cambiar a tipo EMPRESA
            $stmt = $pdo->prepare("UPDATE clientes SET idempresa = ?, tipocliente = 'EMPRESA' WHERE idcliente = ?");
            $stmt->execute([$idempresa, $idcliente]);

            return [
                'status' => true,
                'mensaje' => 'Cliente actualizado correctamente',
                'idcliente' => $idcliente,
                'idpersona' => $cliente['idpersona']
            ];
        } catch (Exception $e) {
            error_log("Error al actualizar cliente con empresa: " . $e->getMessage());

            return [
                'status' => false,
                'mensaje' => 'Error al actualizar cliente: ' . $e->getMessage()
            ];
        }
    }
}
