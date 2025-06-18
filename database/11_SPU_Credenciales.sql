USE clinicaDB;

-- 1. Procedimiento para registrar credenciales de acceso
DELIMITER //
CREATE PROCEDURE sp_registrar_credenciales(
    IN p_idcontrato INT,
    IN p_nomuser VARCHAR(50),
    IN p_passuser VARCHAR(255),
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(255),
    OUT p_idusuario INT
)
BEGIN
    DECLARE v_existe_username INT DEFAULT 0;
    
    -- Verificar si el nombre de usuario ya existe
    SELECT COUNT(*) INTO v_existe_username FROM usuarios WHERE nomuser = p_nomuser;
    
    IF v_existe_username > 0 THEN
        SET p_resultado = 0;
        SET p_mensaje = 'El nombre de usuario ya está en uso';
        SET p_idusuario = NULL;
    ELSE
        -- Insertar credenciales
        INSERT INTO usuarios (idcontrato, nomuser, passuser, estado)
        VALUES (p_idcontrato, p_nomuser, p_passuser, TRUE);
        
        SET p_idusuario = LAST_INSERT_ID();
        SET p_resultado = 1;
        SET p_mensaje = 'Credenciales de acceso registradas correctamente';
    END IF;
END //
DELIMITER ;