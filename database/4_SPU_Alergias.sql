USE clinicaDB;

-- Procedimiento para registrar una nueva alergia
DELIMITER $$
CREATE PROCEDURE spu_alergias_registrar(
    IN p_tipoalergia VARCHAR(100),
    IN p_alergia VARCHAR(200)
)
BEGIN
    INSERT INTO alergias (tipoalergia, alergia)
    VALUES (p_tipoalergia, p_alergia);
    
    SELECT LAST_INSERT_ID() AS idalergia;
END $$
DELIMITER ;

-- Procedimiento para registrar una alergia a un paciente
DELIMITER $$
CREATE PROCEDURE spu_paciente_alergia_registrar(
    IN p_idpersona INT,
    IN p_idalergia INT,
    IN p_gravedad ENUM('LEVE', 'MODERADA', 'GRAVE')
)
BEGIN
    INSERT INTO listaalergias (idpersona, idalergia, gravedad)
    VALUES (p_idpersona, p_idalergia, p_gravedad);
    
    SELECT LAST_INSERT_ID() AS idlistaalergia;
END $$
DELIMITER ;

-- Procedimiento para listar todas las alergias
DELIMITER $$
CREATE PROCEDURE spu_alergias_listar()
BEGIN
    SELECT 
        idalergia,
        tipoalergia,
        alergia
    FROM alergias
    ORDER BY tipoalergia, alergia;
END $$
DELIMITER ;

-- Procedimiento para listar las alergias por tipo
DELIMITER $$
CREATE PROCEDURE spu_alergias_listar_por_tipo(
    IN p_tipoalergia VARCHAR(100)
)
BEGIN
    SELECT 
        idalergia,
        tipoalergia,
        alergia
    FROM alergias
    WHERE tipoalergia = p_tipoalergia
    ORDER BY alergia;
END $$
DELIMITER ;

-- Procedimiento para obtener las alergias de un paciente específico
DELIMITER $$
CREATE PROCEDURE spu_paciente_alergias_listar(
    IN p_idpersona INT
)
BEGIN
    SELECT 
        la.idlistaalergia,
        a.idalergia,
        a.tipoalergia,
        a.alergia,
        la.gravedad
    FROM listaalergias la
    INNER JOIN alergias a ON la.idalergia = a.idalergia
    WHERE la.idpersona = p_idpersona
    ORDER BY a.tipoalergia, a.alergia;
END $$
DELIMITER ;

-- Procedimiento para obtener una alergia específica por su ID
DELIMITER $$
CREATE PROCEDURE spu_alergias_obtener_por_id(
    IN p_idalergia INT
)
BEGIN
    SELECT 
        idalergia,
        tipoalergia,
        alergia
    FROM alergias
    WHERE idalergia = p_idalergia;
END $$
DELIMITER ;

-- Procedimiento para actualizar una alergia
DELIMITER $$
CREATE PROCEDURE spu_alergias_actualizar(
    IN p_idalergia INT,
    IN p_tipoalergia VARCHAR(100),
    IN p_alergia VARCHAR(200)
)
BEGIN
    UPDATE alergias
    SET 
        tipoalergia = p_tipoalergia,
        alergia = p_alergia
    WHERE idalergia = p_idalergia;
    
    SELECT p_idalergia AS idalergia;
END $$
DELIMITER ;

-- Procedimiento para actualizar la gravedad de una alergia de un paciente
DELIMITER $$
CREATE PROCEDURE spu_paciente_alergia_actualizar(
    IN p_idlistaalergia INT,
    IN p_gravedad ENUM('LEVE', 'MODERADA', 'GRAVE')
)
BEGIN
    UPDATE listaalergias
    SET gravedad = p_gravedad
    WHERE idlistaalergia = p_idlistaalergia;
    
    SELECT p_idlistaalergia AS idlistaalergia;
END $$
DELIMITER ;

-- Procedimiento para eliminar una alergia
DELIMITER $$
CREATE PROCEDURE spu_alergias_eliminar(
    IN p_idalergia INT
)
BEGIN
    -- Primero verificar si la alergia está asociada a algún paciente
    DECLARE alergia_en_uso INT;
    
    SELECT COUNT(*) INTO alergia_en_uso
    FROM listaalergias
    WHERE idalergia = p_idalergia;
    
    IF alergia_en_uso = 0 THEN
        DELETE FROM alergias WHERE idalergia = p_idalergia;
        SELECT 1 AS eliminado, 'Alergia eliminada correctamente' AS mensaje;
    ELSE
        SELECT 0 AS eliminado, 'No se puede eliminar la alergia porque está asociada a uno o más pacientes' AS mensaje;
    END IF;
END $$
DELIMITER ;

-- Procedimiento para eliminar una alergia de un paciente
DELIMITER $$
CREATE PROCEDURE spu_paciente_alergia_eliminar(
    IN p_idlistaalergia INT
)
BEGIN
    DELETE FROM listaalergias
    WHERE idlistaalergia = p_idlistaalergia;
    
    SELECT 1 AS eliminado, 'Alergia del paciente eliminada correctamente' AS mensaje;
END $$
DELIMITER ;

-- Procedimiento para buscar alergias por nombre o tipo
DELIMITER $$
CREATE PROCEDURE spu_alergias_buscar(
    IN p_busqueda VARCHAR(200)
)
BEGIN
    SELECT 
        idalergia,
        tipoalergia,
        alergia
    FROM alergias
    WHERE 
        tipoalergia LIKE CONCAT('%', p_busqueda, '%') OR
        alergia LIKE CONCAT('%', p_busqueda, '%')
    ORDER BY tipoalergia, alergia;
END $$
DELIMITER ;

SELECT * FROM pacientes;