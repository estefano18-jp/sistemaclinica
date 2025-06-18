USE clinicaDB;

-- 1. Procedimiento para verificar si un documento ya está registrado como paciente
DELIMITER //
DROP PROCEDURE IF EXISTS sp_verificar_documento_paciente//
CREATE PROCEDURE sp_verificar_documento_paciente(
    IN p_nrodoc VARCHAR(20),
    OUT p_existe BOOLEAN
)
BEGIN
    DECLARE v_count INT DEFAULT 0;
    
    -- Verificar si el documento existe en personas y si esa persona es un paciente
    SELECT COUNT(*) INTO v_count
    FROM personas p
    INNER JOIN pacientes pac ON p.idpersona = pac.idpersona
    WHERE p.nrodoc = p_nrodoc;
    
    -- Establecer el resultado
    IF v_count > 0 THEN
        SET p_existe = TRUE;
    ELSE
        SET p_existe = FALSE;
    END IF;
END //
DELIMITER ;

-- 2. Procedimiento para registrar un nuevo paciente
DELIMITER //
DROP PROCEDURE IF EXISTS sp_registrar_paciente//
CREATE PROCEDURE sp_registrar_paciente(
    IN p_apellidos VARCHAR(100),
    IN p_nombres VARCHAR(100),
    IN p_tipodoc ENUM('DNI', 'PASAPORTE', 'CARNET DE EXTRANJERIA', 'OTRO'),
    IN p_nrodoc VARCHAR(20),
    IN p_telefono VARCHAR(20),
    IN p_fechanacimiento DATE,
    IN p_genero ENUM('M', 'F', 'OTRO'),
    IN p_direccion VARCHAR(250),
    IN p_email VARCHAR(100),
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(255)
)
BEGIN
    DECLARE v_idpersona INT;
    DECLARE v_existe BOOLEAN;
    DECLARE v_fecha_actual DATE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_resultado = 0;
        SET p_mensaje = 'Error al registrar el paciente';
    END;
    
    -- Obtener la fecha actual
    SET v_fecha_actual = CURDATE();
    
    -- Verificar si el documento ya está registrado
    CALL sp_verificar_documento_paciente(p_nrodoc, v_existe);
    
    -- Si ya existe, devolver mensaje de error
    IF v_existe THEN
        SET p_resultado = 0;
        SET p_mensaje = CONCAT('El documento ', p_nrodoc, ' ya está registrado como paciente');
    ELSE
        -- Iniciar transacción
        START TRANSACTION;
        
        -- Verificar si la persona ya existe en la tabla personas
        SELECT idpersona INTO v_idpersona FROM personas WHERE nrodoc = p_nrodoc LIMIT 1;
        
        -- Si la persona no existe, insertarla
        IF v_idpersona IS NULL THEN
            INSERT INTO personas (
                apellidos, nombres, tipodoc, nrodoc, telefono, 
                fechanacimiento, genero, direccion, email
            ) VALUES (
                p_apellidos, p_nombres, p_tipodoc, p_nrodoc, p_telefono,
                p_fechanacimiento, p_genero, p_direccion, p_email
            );
            
            -- Obtener el ID de la persona recién insertada
            SET v_idpersona = LAST_INSERT_ID();
        END IF;
        
        -- Registrar a la persona como paciente
        INSERT INTO pacientes (idpersona, fecharegistro)
        VALUES (v_idpersona, v_fecha_actual);
        
        -- Si todo está bien, confirmar la transacción
        COMMIT;
        
        SET p_resultado = 1;
        SET p_mensaje = CONCAT('Paciente ', p_nombres, ' ', p_apellidos, ' registrado correctamente');
    END IF;
END //
DELIMITER ;

-- 3. Procedimiento para listar pacientes con filtros opcionales
DELIMITER //
DROP PROCEDURE IF EXISTS sp_listar_pacientes//
CREATE PROCEDURE sp_listar_pacientes(
    IN p_busqueda VARCHAR(100),
    IN p_estado VARCHAR(20)
)
BEGIN
    SELECT 
        p.idpersona,
        pac.idpaciente,
        p.apellidos,
        p.nombres,
        CONCAT(p.apellidos, ', ', p.nombres) AS nombre_completo,
        p.tipodoc,
        p.nrodoc,
        p.telefono,
        p.fechanacimiento,
        TIMESTAMPDIFF(YEAR, p.fechanacimiento, CURDATE()) AS edad,
        p.genero,
        p.direccion,
        p.email,
        pac.fecharegistro,
        CASE WHEN hc.altamedica IS NULL THEN 'Sin alta médica' 
             WHEN hc.altamedica = 0 THEN 'En tratamiento'
             ELSE 'Alta médica' END AS estado_medico
    FROM 
        pacientes pac
    INNER JOIN 
        personas p ON pac.idpersona = p.idpersona
    LEFT JOIN 
        historiaclinica hc ON pac.idpaciente = hc.idhistoriaclinica
    WHERE 
        (p_busqueda IS NULL 
         OR p.apellidos LIKE CONCAT('%', p_busqueda, '%')
         OR p.nombres LIKE CONCAT('%', p_busqueda, '%')
         OR p.nrodoc LIKE CONCAT('%', p_busqueda, '%')
         OR p.telefono LIKE CONCAT('%', p_busqueda, '%')
        )
        AND (p_estado IS NULL 
             OR (p_estado = 'alta' AND hc.altamedica = 1)
             OR (p_estado = 'tratamiento' AND hc.altamedica = 0)
             OR (p_estado = 'sin_historia' AND hc.idhistoriaclinica IS NULL)
            )
    ORDER BY 
        p.apellidos, p.nombres;
END //
DELIMITER ;

-- 4. Procedimiento para actualizar los datos de un paciente existente
DELIMITER //
DROP PROCEDURE IF EXISTS sp_actualizar_paciente//
CREATE PROCEDURE sp_actualizar_paciente(
    IN p_idpaciente INT,
    IN p_apellidos VARCHAR(100),
    IN p_nombres VARCHAR(100),
    IN p_tipodoc ENUM('DNI', 'PASAPORTE', 'CARNET DE EXTRANJERIA', 'OTRO'),
    IN p_nrodoc VARCHAR(20),
    IN p_telefono VARCHAR(20),
    IN p_fechanacimiento DATE,
    IN p_genero ENUM('M', 'F', 'OTRO'),
    IN p_direccion VARCHAR(250),
    IN p_email VARCHAR(100),
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(255)
)
proc: BEGIN
    DECLARE v_idpersona INT;
    DECLARE v_nrodoc_actual VARCHAR(20);
    DECLARE v_existe_otro BOOLEAN DEFAULT FALSE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_resultado = 0;
        SET p_mensaje = 'Error al actualizar el paciente';
    END;
    
    -- Obtener el ID de persona del paciente
    SELECT idpersona INTO v_idpersona 
    FROM pacientes 
    WHERE idpaciente = p_idpaciente;
    
    -- Verificar que el paciente existe
    IF v_idpersona IS NULL THEN
        SET p_resultado = 0;
        SET p_mensaje = 'El paciente no existe';
    ELSE
        -- Obtener el número de documento actual
        SELECT nrodoc INTO v_nrodoc_actual 
        FROM personas 
        WHERE idpersona = v_idpersona;
        
        -- Verificar si el nuevo número de documento ya existe en otro paciente
        IF p_nrodoc != v_nrodoc_actual THEN
            SELECT COUNT(*) > 0 INTO v_existe_otro
            FROM personas p
            INNER JOIN pacientes pac ON p.idpersona = pac.idpersona
            WHERE p.nrodoc = p_nrodoc AND p.idpersona != v_idpersona;
            
            IF v_existe_otro THEN
                SET p_resultado = 0;
                SET p_mensaje = CONCAT('El documento ', p_nrodoc, ' ya está registrado para otro paciente');
                LEAVE proc;
            END IF;
        END IF;
        
        -- Iniciar transacción
        START TRANSACTION;
        
        -- Actualizar los datos en la tabla personas
        UPDATE personas
        SET 
            apellidos = p_apellidos,
            nombres = p_nombres,
            tipodoc = p_tipodoc,
            nrodoc = p_nrodoc,
            telefono = p_telefono,
            fechanacimiento = p_fechanacimiento,
            genero = p_genero,
            direccion = p_direccion,
            email = p_email
        WHERE idpersona = v_idpersona;
        
        -- Confirmar la transacción
        COMMIT;
        
        SET p_resultado = 1;
        SET p_mensaje = CONCAT('Paciente ', p_nombres, ' ', p_apellidos, ' actualizado correctamente');
    END IF;
END //
DELIMITER ;

-- 5. Procedimiento para obtener un paciente por su ID
DELIMITER //
DROP PROCEDURE IF EXISTS sp_obtener_paciente_por_id//
CREATE PROCEDURE sp_obtener_paciente_por_id(
    IN p_idpaciente INT
)
BEGIN
    SELECT 
        p.idpersona,
        pac.idpaciente,
        p.apellidos,
        p.nombres,
        CONCAT(p.apellidos, ', ', p.nombres) AS nombre_completo,
        p.tipodoc,
        p.nrodoc,
        p.telefono,
        p.fechanacimiento,
        TIMESTAMPDIFF(YEAR, p.fechanacimiento, CURDATE()) AS edad,
        p.genero,
        p.direccion,
        p.email,
        pac.fecharegistro,
        CASE WHEN hc.altamedica IS NULL THEN 'Sin alta médica' 
             WHEN hc.altamedica = 0 THEN 'En tratamiento'
             ELSE 'Alta médica' END AS estado_medico
    FROM 
        pacientes pac
    INNER JOIN 
        personas p ON pac.idpersona = p.idpersona
    LEFT JOIN 
        historiaclinica hc ON pac.idpaciente = hc.idhistoriaclinica
    WHERE 
        pac.idpaciente = p_idpaciente;
END //
DELIMITER ;

-- 6. Procedimiento para buscar pacientes por número de documento
DELIMITER //
DROP PROCEDURE IF EXISTS sp_buscar_paciente_por_documento//
CREATE PROCEDURE sp_buscar_paciente_por_documento(
    IN p_nrodoc VARCHAR(20)
)
BEGIN
    SELECT 
        p.idpersona,
        pac.idpaciente,
        p.apellidos,
        p.nombres,
        CONCAT(p.apellidos, ', ', p.nombres) AS nombre_completo,
        p.tipodoc,
        p.nrodoc,
        p.telefono,
        p.fechanacimiento,
        TIMESTAMPDIFF(YEAR, p.fechanacimiento, CURDATE()) AS edad,
        p.genero,
        p.direccion,
        p.email,
        pac.fecharegistro,
        CASE WHEN hc.altamedica IS NULL THEN 'Sin alta médica' 
             WHEN hc.altamedica = 0 THEN 'En tratamiento'
             ELSE 'Alta médica' END AS estado_medico
    FROM 
        pacientes pac
    INNER JOIN 
        personas p ON pac.idpersona = p.idpersona
    LEFT JOIN 
        historiaclinica hc ON pac.idpaciente = hc.idhistoriaclinica
    WHERE 
        p.nrodoc = p_nrodoc;
END //
DELIMITER ;

-- 7. Procedimiento para eliminar pacientes con todas sus dependencias
DELIMITER //
DROP PROCEDURE IF EXISTS spu_eliminar_paciente_completo_con_dependencias//
CREATE PROCEDURE spu_eliminar_paciente_completo_con_dependencias(
    IN p_idpaciente INT 
)
BEGIN
    DECLARE v_idpersona INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 0 AS resultado, 'Error al eliminar el paciente' AS mensaje;
    END;
    
    -- Obtener el idpersona del paciente
    SELECT idpersona INTO v_idpersona 
    FROM pacientes 
    WHERE idpaciente = p_idpaciente;
    
    -- Verificar que el paciente existe
    IF v_idpersona IS NULL THEN
        SELECT 0 AS resultado, 'El paciente no existe' AS mensaje;
    ELSE
        -- Iniciar transacción
        START TRANSACTION;
        
        -- 1. Eliminar registros en listaalergias
        DELETE FROM listaalergias WHERE idpersona = v_idpersona;
        
        -- 2. Eliminar registros de resultados relacionados con servicios requeridos de consultas del paciente
        DELETE r FROM resultados r
        INNER JOIN serviciosrequeridos sr ON r.idserviciorequerido = sr.idserviciorequerido
        INNER JOIN consultas c ON sr.idconsulta = c.idconsulta
        WHERE c.idpaciente = p_idpaciente;
        
        -- 3. Eliminar servicios requeridos de consultas del paciente
        DELETE sr FROM serviciosrequeridos sr
        INNER JOIN consultas c ON sr.idconsulta = c.idconsulta
        WHERE c.idpaciente = p_idpaciente;
        
        -- 4. Eliminar detalleventas relacionados con las consultas del paciente
        DELETE dv FROM detalleventas dv
        INNER JOIN consultas c ON dv.idconsulta = c.idconsulta
        WHERE c.idpaciente = p_idpaciente;
        
        -- 5. Eliminar triajes relacionados con consultas del paciente
        DELETE t FROM triajes t
        INNER JOIN consultas c ON t.idconsulta = c.idconsulta
        WHERE c.idpaciente = p_idpaciente;
        
        -- 6. Eliminar recetas y tratamientos relacionados
        DELETE t FROM tratamiento t
        INNER JOIN recetas r ON t.idreceta = r.idreceta
        INNER JOIN consultas c ON r.idconsulta = c.idconsulta
        WHERE c.idpaciente = p_idpaciente;
        
        DELETE r FROM recetas r
        INNER JOIN consultas c ON r.idconsulta = c.idconsulta
        WHERE c.idpaciente = p_idpaciente;
        
        -- 7. Eliminar las consultas del paciente
        DELETE FROM consultas WHERE idpaciente = p_idpaciente;
        
        -- 8. Eliminar posibles registros en historiaclinica
        DELETE FROM historiaclinica WHERE idhistoriaclinica = p_idpaciente;
        
        -- 9. Eliminar las citas asociadas a la persona
        DELETE FROM citas WHERE idpersona = v_idpersona;
        
        -- 10. Eliminar el registro de paciente
        DELETE FROM pacientes WHERE idpaciente = p_idpaciente;
        
        -- 11. Eliminar registros de cliente si existen
        DELETE FROM clientes WHERE idpersona = v_idpersona;
        
        -- 12. Finalmente eliminar la persona
        DELETE FROM personas WHERE idpersona = v_idpersona;
        
        -- Confirmar la transacción
        COMMIT;
        
        SELECT 1 AS resultado, 'Paciente eliminado correctamente con todos sus registros asociados' AS mensaje;
    END IF;
END //
DELIMITER ;

-- 8. Procedimiento simplificado para eliminar paciente con alergias (llama al procedimiento completo)
DELIMITER //
DROP PROCEDURE IF EXISTS sp_eliminar_paciente_con_alergias//
CREATE PROCEDURE sp_eliminar_paciente_con_alergias(
    IN p_idpaciente INT
)
BEGIN
    DECLARE v_idpersona INT;
    DECLARE v_existe INT;
    
    -- Verificar si el paciente existe
    SELECT COUNT(*) INTO v_existe FROM pacientes WHERE idpaciente = p_idpaciente;
    
    IF v_existe = 0 THEN
        SELECT 0 AS resultado, 'El paciente no existe' AS mensaje;
    ELSE
        -- Obtener el idpersona
        SELECT idpersona INTO v_idpersona FROM pacientes WHERE idpaciente = p_idpaciente;
        
        BEGIN
            DECLARE EXIT HANDLER FOR SQLEXCEPTION
            BEGIN
                ROLLBACK;
                SELECT 0 AS resultado, 'Error al eliminar el paciente' AS mensaje;
            END;
            
            START TRANSACTION;
            
            -- Eliminar las alergias
            DELETE FROM listaalergias WHERE idpersona = v_idpersona;
            
            -- Eliminar el paciente
            DELETE FROM pacientes WHERE idpaciente = p_idpaciente;
            
            -- Opcionalmente eliminar la persona si no está referenciada en otras tablas
            -- Esta sección podría omitirse si la persona puede existir en otras tablas
            /*
            DELETE FROM personas 
            WHERE idpersona = v_idpersona 
            AND NOT EXISTS (SELECT 1 FROM otros_datos WHERE idpersona = v_idpersona);
            */
            
            COMMIT;
            
            SELECT 1 AS resultado, 'Paciente eliminado correctamente' AS mensaje;
        END;
    END IF;
END //
DELIMITER ;


SELECT * FROM personas;
SELECT * FROM contratos;
SELECT * FROM usuarios;
SELECT * FROM pacientes;
SELECT * FROM empresas;
SELECT * FROM alergias;