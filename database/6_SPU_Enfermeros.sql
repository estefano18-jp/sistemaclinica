USE clinicaDB;

-- 1. Procedimiento para verificar si un documento ya está registrado como enfermero
DELIMITER $$
CREATE PROCEDURE sp_verificar_documento_enfermero(
    IN p_nrodoc VARCHAR(20),
    OUT p_existe BOOLEAN
)
BEGIN
    DECLARE v_count INT DEFAULT 0;
    
    -- Verificamos si existe en colaboradores como enfermero
    SELECT COUNT(*) INTO v_count
    FROM personas p
    INNER JOIN colaboradores c ON p.idpersona = c.idpersona
    INNER JOIN contratos ct ON c.idcolaborador = ct.idcolaborador
    INNER JOIN usuarios u ON ct.idcontrato = u.idcontrato
    WHERE p.nrodoc = p_nrodoc AND u.rol = 'ENFERMERO';
    
    -- Si encontramos al menos un registro, el documento ya está registrado como enfermero
    SET p_existe = v_count > 0;
END $$
DELIMITER ;

-- 2. Procedimiento para registrar información personal del enfermero
DELIMITER //
CREATE PROCEDURE sp_registrar_enfermero_personal(
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
    OUT p_mensaje VARCHAR(255),
    OUT p_idpersona INT
)
BEGIN
    DECLARE v_existe BOOLEAN;
    
    -- Verificar si el documento ya está registrado como enfermero
    CALL sp_verificar_documento_enfermero(p_nrodoc, v_existe);
    
    IF v_existe THEN
        SET p_resultado = 0;
        SET p_mensaje = 'Este documento ya está registrado como enfermero';
        SET p_idpersona = NULL;
    ELSE
        -- Insertar datos personales
        INSERT INTO personas (apellidos, nombres, tipodoc, nrodoc, telefono, 
                             fechanacimiento, genero, direccion, email)
        VALUES (p_apellidos, p_nombres, p_tipodoc, p_nrodoc, p_telefono,
                p_fechanacimiento, p_genero, p_direccion, p_email);
        
        SET p_idpersona = LAST_INSERT_ID();
        SET p_resultado = 1;
        SET p_mensaje = 'Información personal del enfermero registrada correctamente';
    END IF;
END //
DELIMITER ;

-- 3. Procedimiento para listar enfermeros
DELIMITER //
CREATE PROCEDURE sp_listar_enfermeros()
BEGIN
    SELECT 	
        p.idpersona,
        c.idcolaborador,
        p.apellidos,
        p.nombres,
        CONCAT(p.apellidos, ', ', p.nombres) AS nombre_completo,
        p.tipodoc,
        p.nrodoc,
        p.telefono,
        p.genero,
        p.email,
        CASE 
            WHEN c.estado IS NULL THEN 'ACTIVO'
            ELSE c.estado
        END AS estado
    FROM 
        colaboradores c
    INNER JOIN 
        personas p ON c.idpersona = p.idpersona
    INNER JOIN 
        contratos ct ON c.idcolaborador = ct.idcolaborador
    INNER JOIN 
        usuarios u ON ct.idcontrato = u.idcontrato
    WHERE 
        u.rol = 'ENFERMERO'
    ORDER BY 
        p.apellidos, p.nombres;
END //
DELIMITER ;

-- Procedimiento para buscar enfermeros por número de documento
DELIMITER //
CREATE PROCEDURE sp_buscar_enfermero_por_documento(
    IN p_nrodoc VARCHAR(20)
)
BEGIN
    SELECT 
        p.idpersona,
        c.idcolaborador,
        p.apellidos,
        p.nombres,
        CONCAT(p.apellidos, ', ', p.nombres) AS nombre_completo,
        p.tipodoc,
        p.nrodoc,
        p.telefono,
        p.fechanacimiento,
        p.genero,
        p.direccion,
        p.email,
        CASE 
            WHEN c.estado IS NULL THEN 'ACTIVO'
            ELSE c.estado
        END AS estado
    FROM 
        colaboradores c
    INNER JOIN 
        personas p ON c.idpersona = p.idpersona
    INNER JOIN 
        contratos ct ON c.idcolaborador = ct.idcolaborador
    INNER JOIN 
        usuarios u ON ct.idcontrato = u.idcontrato
    WHERE 
        p.nrodoc = p_nrodoc 
        AND u.rol = 'ENFERMERO'
    LIMIT 1;
END //
DELIMITER ;

-- Procedimiento para cambiar el estado de un enfermero
DELIMITER //
CREATE PROCEDURE sp_cambiar_estado_enfermero(
    IN p_nrodoc VARCHAR(20),
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(255)
)
BEGIN
    DECLARE v_idcolaborador INT;
    DECLARE v_estado_actual VARCHAR(10);
    DECLARE v_nuevo_estado VARCHAR(10);
    
    -- Iniciar transacción para asegurar consistencia
    START TRANSACTION;
    
    -- Buscar el idcolaborador y estado actual
    SELECT 
        c.idcolaborador, 
        IFNULL(c.estado, 'ACTIVO') INTO v_idcolaborador, v_estado_actual
    FROM 
        colaboradores c
    INNER JOIN 
        personas p ON c.idpersona = p.idpersona
    INNER JOIN 
        contratos ct ON c.idcolaborador = ct.idcolaborador
    INNER JOIN 
        usuarios u ON ct.idcontrato = u.idcontrato
    WHERE 
        p.nrodoc = p_nrodoc
        AND u.rol = 'ENFERMERO'
    LIMIT 1;
    
    IF v_idcolaborador IS NULL THEN
        SET p_resultado = 0;
        SET p_mensaje = 'No se encontró un enfermero con el documento especificado';
        ROLLBACK;
    ELSE
        -- Cambiar al estado opuesto
        IF v_estado_actual = 'ACTIVO' THEN
            SET v_nuevo_estado = 'INACTIVO';
        ELSE
            SET v_nuevo_estado = 'ACTIVO';
        END IF;
        
        -- Actualizar el estado del colaborador
        UPDATE colaboradores SET estado = v_nuevo_estado 
        WHERE idcolaborador = v_idcolaborador;
        
        -- Actualizar también el estado de los contratos activos del enfermero
        UPDATE contratos 
        SET estado = v_nuevo_estado 
        WHERE idcolaborador = v_idcolaborador 
        AND (estado = 'ACTIVO' OR (v_nuevo_estado = 'ACTIVO' AND estado = 'INACTIVO'));
        
        -- Confirmar la transacción
        COMMIT;
        
        SET p_resultado = 1;
        SET p_mensaje = CONCAT('El estado del enfermero y sus contratos ha sido cambiado a ', v_nuevo_estado);
    END IF;
END //
DELIMITER ;

-- Procedimiento para eliminar un enfermero
DELIMITER //
CREATE PROCEDURE sp_eliminar_enfermero(
    IN p_nrodoc VARCHAR(20),
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(255)
)
BEGIN
    DECLARE v_idpersona INT;
    DECLARE v_idcolaborador INT;
    DECLARE v_tiene_triajes INT DEFAULT 0;
    DECLARE v_tiene_contratos INT DEFAULT 0;
    DECLARE v_tiene_alergias INT DEFAULT 0;
    DECLARE v_tiene_citas INT DEFAULT 0;
    
    -- Iniciar transacción
    START TRANSACTION;
    
    -- Buscar IDs del enfermero basado en el número de documento
    SELECT p.idpersona, c.idcolaborador INTO v_idpersona, v_idcolaborador
    FROM personas p
    INNER JOIN colaboradores c ON p.idpersona = c.idpersona
    INNER JOIN contratos ct ON c.idcolaborador = ct.idcolaborador
    INNER JOIN usuarios u ON ct.idcontrato = u.idcontrato
    WHERE p.nrodoc = p_nrodoc AND u.rol = 'ENFERMERO'
    LIMIT 1;
    
    IF v_idcolaborador IS NULL THEN
        SET p_resultado = 0;
        SET p_mensaje = 'No se encontró un enfermero con el documento especificado';
        ROLLBACK;
    ELSE
        -- Verificar si tiene triajes asociados
        SELECT COUNT(*) INTO v_tiene_triajes
        FROM triajes
        WHERE idenfermera = v_idcolaborador;
        
        -- Verificar si tiene contratos
        SELECT COUNT(*) INTO v_tiene_contratos
        FROM contratos
        WHERE idcolaborador = v_idcolaborador;
        
        -- Verificar si tiene alergias registradas
        SELECT COUNT(*) INTO v_tiene_alergias
        FROM listaalergias
        WHERE idpersona = v_idpersona;
        
        -- Verificar si tiene citas
        SELECT COUNT(*) INTO v_tiene_citas
        FROM citas
        WHERE idpersona = v_idpersona;
        
        -- Si tiene registros relacionados críticos, no podemos eliminar físicamente
        IF v_tiene_triajes > 0 OR v_tiene_alergias > 0 OR v_tiene_citas > 0 THEN
            -- En lugar de eliminar, marcar como inactivo
            UPDATE colaboradores SET estado = 'INACTIVO' WHERE idcolaborador = v_idcolaborador;
            
            SET p_resultado = 2;
            SET p_mensaje = CONCAT('El enfermero tiene registros asociados (triajes: ', v_tiene_triajes, 
                                  ', alergias: ', v_tiene_alergias, 
                                  ', citas: ', v_tiene_citas, 
                                  '). Se ha marcado como INACTIVO en lugar de eliminarse.');
            COMMIT;
        ELSE
            -- Si tiene contratos pero no otras dependencias críticas
            IF v_tiene_contratos > 0 THEN
                -- Verificar y eliminar usuarios asociados al contrato
                DELETE FROM usuarios 
                WHERE idcontrato IN (SELECT idcontrato FROM contratos WHERE idcolaborador = v_idcolaborador);
                
                -- Eliminar atenciones y horarios asociados al contrato
                DELETE FROM horarios 
                WHERE idatencion IN (
                    SELECT idatencion FROM atenciones 
                    WHERE idcontrato IN (SELECT idcontrato FROM contratos WHERE idcolaborador = v_idcolaborador)
                );
                
                DELETE FROM atenciones 
                WHERE idcontrato IN (SELECT idcontrato FROM contratos WHERE idcolaborador = v_idcolaborador);
                
                -- Eliminar contratos
                DELETE FROM contratos WHERE idcolaborador = v_idcolaborador;
            END IF;
            
            -- Ahora eliminar al colaborador y persona
            DELETE FROM colaboradores WHERE idcolaborador = v_idcolaborador;
            
            -- Verificar si la persona es también un cliente
            DELETE FROM clientes WHERE idpersona = v_idpersona;
            
            -- Verificar si la persona es también un paciente
            DELETE FROM pacientes WHERE idpersona = v_idpersona;
            
            -- Finalmente eliminar a la persona
            DELETE FROM personas WHERE idpersona = v_idpersona;
            
            SET p_resultado = 1;
            SET p_mensaje = 'Enfermero eliminado correctamente';
            COMMIT;
        END IF;
    END IF;
END //
DELIMITER ;