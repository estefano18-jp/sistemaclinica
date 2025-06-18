USE clinicaDB;

-- 1. Procedimiento para verificar si un documento ya está registrado como doctor
DELIMITER $$
CREATE PROCEDURE sp_verificar_documento_doctor(
    IN p_nrodoc VARCHAR(20),
    OUT p_existe BOOLEAN
)
BEGIN
    DECLARE v_count INT DEFAULT 0;
    
    -- Verificamos si existe en colaboradores como doctor (con una especialidad asignada)
    SELECT COUNT(*) INTO v_count
    FROM personas p
    INNER JOIN colaboradores c ON p.idpersona = c.idpersona
    WHERE p.nrodoc = p_nrodoc AND c.idespecialidad IS NOT NULL;
    
    -- Si encontramos al menos un registro, el documento ya está registrado como doctor
    SET p_existe = v_count > 0;
END $$
DELIMITER ;

-- 2. Procedimiento para registrar información personal del doctor
DELIMITER //
CREATE PROCEDURE sp_registrar_doctor_personal(
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
    
    -- Verificar si el documento ya está registrado como doctor
    CALL sp_verificar_documento_doctor(p_nrodoc, v_existe);
    
    IF v_existe THEN
        SET p_resultado = 0;
        SET p_mensaje = 'Este documento ya está registrado como doctor';
        SET p_idpersona = NULL;
    ELSE
        -- Insertar datos personales
        INSERT INTO personas (apellidos, nombres, tipodoc, nrodoc, telefono, 
                             fechanacimiento, genero, direccion, email)
        VALUES (p_apellidos, p_nombres, p_tipodoc, p_nrodoc, p_telefono,
                p_fechanacimiento, p_genero, p_direccion, p_email);
        
        SET p_idpersona = LAST_INSERT_ID();
        SET p_resultado = 1;
        SET p_mensaje = 'Información personal del doctor registrada correctamente';
    END IF;
END //
DELIMITER ;

-- 3. Procedimiento para listar doctores
DELIMITER //
CREATE PROCEDURE sp_listar_doctores()
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
        p.genero, -- Campo genero añadido aquí
        e.especialidad,
        e.precioatencion,
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
        especialidades e ON c.idespecialidad = e.idespecialidad
    ORDER BY 
        p.apellidos, p.nombres;
END //
DELIMITER ;

-- Procedimiento para buscar doctores por número de documento
DELIMITER //
CREATE PROCEDURE sp_buscar_doctor_por_documento(
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
        e.especialidad,
        e.precioatencion,
        -- Incluir campo estado
        CASE 
            WHEN c.estado IS NULL THEN 'ACTIVO'
            ELSE c.estado
        END AS estado
    FROM 
        colaboradores c
    INNER JOIN 
        personas p ON c.idpersona = p.idpersona
    INNER JOIN 
        especialidades e ON c.idespecialidad = e.idespecialidad
    WHERE 
        p.nrodoc = p_nrodoc 
    LIMIT 1; -- Limitar a un resultado en caso de múltiples registros
END //
DELIMITER ;

DELIMITER //
DROP PROCEDURE IF EXISTS sp_cambiar_estado_doctor //

CREATE PROCEDURE sp_cambiar_estado_doctor(
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
    WHERE 
        p.nrodoc = p_nrodoc
    LIMIT 1;
    
    IF v_idcolaborador IS NULL THEN
        SET p_resultado = 0;
        SET p_mensaje = 'No se encontró un doctor con el documento especificado';
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
        
        -- Actualizar también el estado de los contratos activos del doctor
        UPDATE contratos 
        SET estado = v_nuevo_estado 
        WHERE idcolaborador = v_idcolaborador 
        AND (estado = 'ACTIVO' OR (v_nuevo_estado = 'ACTIVO' AND estado = 'INACTIVO'));
        
        -- Confirmar la transacción
        COMMIT;
        
        SET p_resultado = 1;
        SET p_mensaje = CONCAT('El estado del doctor y sus contratos ha sido cambiado a ', v_nuevo_estado);
    END IF;
END //
DELIMITER ;

SHOW CREATE PROCEDURE sp_cambiar_estado_doctor;

DELIMITER //
DROP PROCEDURE IF EXISTS sp_actualizar_contrato //

CREATE PROCEDURE sp_actualizar_contrato(
    IN p_idcontrato INT,
    IN p_idcolaborador INT,
    IN p_fechainicio DATE,
    IN p_fechafin DATE,
    IN p_tipocontrato VARCHAR(50),
    IN p_estado ENUM('ACTIVO', 'INACTIVO'),
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(255),
    OUT p_nuevo_idcontrato INT
)
BEGIN
    DECLARE v_existe INT;
    
    -- Iniciar transacción para asegurar consistencia
    START TRANSACTION;
    
    -- Validar parámetros
    IF p_idcolaborador IS NULL OR p_idcolaborador <= 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'El ID del colaborador no es válido';
        SET p_nuevo_idcontrato = NULL;
        ROLLBACK;
    ELSEIF p_fechainicio IS NULL THEN
        SET p_resultado = 0;
        SET p_mensaje = 'La fecha de inicio es requerida';
        SET p_nuevo_idcontrato = NULL;
        ROLLBACK;
    ELSEIF p_tipocontrato IS NULL OR p_tipocontrato = '' THEN
        SET p_resultado = 0;
        SET p_mensaje = 'El tipo de contrato es requerido';
        SET p_nuevo_idcontrato = NULL;
        ROLLBACK;
    ELSEIF p_tipocontrato != 'INDEFINIDO' AND p_fechafin IS NULL THEN
        SET p_resultado = 0;
        SET p_mensaje = 'La fecha de fin es requerida para contratos que no son indefinidos';
        SET p_nuevo_idcontrato = NULL;
        ROLLBACK;
    ELSE
        -- Si es un nuevo contrato
        IF p_idcontrato IS NULL OR p_idcontrato <= 0 THEN
            -- Insertar nuevo contrato
            INSERT INTO contratos (
                idcolaborador, fechainicio, fechafin, tipocontrato, estado
            ) VALUES (
                p_idcolaborador, p_fechainicio, p_fechafin, p_tipocontrato, p_estado
            );
            
            SET p_nuevo_idcontrato = LAST_INSERT_ID();
            SET p_mensaje = 'Contrato registrado correctamente';
        ELSE
            -- Verificar si el contrato existe
            SELECT COUNT(*) INTO v_existe FROM contratos WHERE idcontrato = p_idcontrato;
            
            IF v_existe = 0 THEN
                SET p_resultado = 0;
                SET p_mensaje = 'El contrato especificado no existe';
                SET p_nuevo_idcontrato = NULL;
                ROLLBACK;
            ELSE
                -- Actualizar contrato existente
                UPDATE contratos 
                SET fechainicio = p_fechainicio,
                    fechafin = p_fechafin,
                    tipocontrato = p_tipocontrato,
                    estado = p_estado
                WHERE idcontrato = p_idcontrato;
                
                SET p_nuevo_idcontrato = p_idcontrato;
                SET p_mensaje = 'Contrato actualizado correctamente';
            END IF;
        END IF;
        
        -- Actualizar también el estado del doctor/colaborador
        UPDATE colaboradores 
        SET estado = p_estado 
        WHERE idcolaborador = p_idcolaborador;
        
        -- Confirmar la transacción
        COMMIT;
        SET p_resultado = 1;
        SET p_mensaje = CONCAT(p_mensaje, '. El estado del doctor también ha sido actualizado a ', p_estado);
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE sp_eliminar_doctor(
    IN p_nrodoc VARCHAR(20),
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(255)
)
BEGIN
    DECLARE v_idpersona INT;
    DECLARE v_idcolaborador INT;
    DECLARE v_tiene_consultas INT DEFAULT 0;
    DECLARE v_tiene_triajes INT DEFAULT 0;
    DECLARE v_tiene_contratos INT DEFAULT 0;
    DECLARE v_tiene_alergias INT DEFAULT 0;
    DECLARE v_tiene_citas INT DEFAULT 0;
    
    -- Iniciar transacción
    START TRANSACTION;
    
    -- Buscar IDs del doctor basado en el número de documento
    SELECT p.idpersona, c.idcolaborador INTO v_idpersona, v_idcolaborador
    FROM personas p
    INNER JOIN colaboradores c ON p.idpersona = c.idpersona
    WHERE p.nrodoc = p_nrodoc AND c.idespecialidad IS NOT NULL
    LIMIT 1;
    
    IF v_idcolaborador IS NULL THEN
        SET p_resultado = 0;
        SET p_mensaje = 'No se encontró un doctor con el documento especificado';
        ROLLBACK;
    ELSE
        -- Verificar si tiene consultas asociadas (como médico)
        SELECT COUNT(*) INTO v_tiene_consultas
        FROM consultas co
        INNER JOIN horarios h ON co.idhorario = h.idhorario
        INNER JOIN atenciones a ON h.idatencion = a.idatencion
        INNER JOIN contratos ct ON a.idcontrato = ct.idcontrato
        WHERE ct.idcolaborador = v_idcolaborador;
        
        -- Verificar si tiene triajes asociados (como enfermera)
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
        IF v_tiene_consultas > 0 OR v_tiene_triajes > 0 OR v_tiene_alergias > 0 OR v_tiene_citas > 0 THEN
            -- En lugar de eliminar, marcar como inactivo
            UPDATE colaboradores SET estado = 'INACTIVO' WHERE idcolaborador = v_idcolaborador;
            
            SET p_resultado = 2;
            SET p_mensaje = CONCAT('El doctor tiene registros asociados (consultas: ', v_tiene_consultas, 
                                  ', triajes: ', v_tiene_triajes, 
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
            SET p_mensaje = 'Doctor eliminado correctamente';
            COMMIT;
        END IF;
    END IF;
END //
DELIMITER ;
CALL sp_eliminar_doctor('75891431', @resultado, @mensaje);
SELECT * FROM personas;
SELECT * FROM usuarios;


