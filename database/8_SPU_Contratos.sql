USE clinicaDB;

-- 1. Procedimiento para registrar contrato (común para doctores y enfermeras)
DELIMITER //
CREATE PROCEDURE sp_registrar_contrato(
    IN p_idcolaborador INT,
    IN p_tipocontrato VARCHAR(50),
    IN p_fechainicio DATE,
    IN p_fechafin DATE,
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(255),
    OUT p_idcontrato INT
)
BEGIN
    -- Registrar contrato
    INSERT INTO contratos (idcolaborador, tipocontrato, fechainicio, fechafin)
    VALUES (p_idcolaborador, p_tipocontrato, p_fechainicio, p_fechafin);
    
    SET p_idcontrato = LAST_INSERT_ID();
    SET p_resultado = 1;
    SET p_mensaje = 'Contrato registrado correctamente';
END //
DELIMITER ;

-- 2. Procedimiento para obtener información detallada de un contrato por ID
DELIMITER //
CREATE PROCEDURE sp_obtener_contrato_por_id(
    IN p_idcontrato INT
)
BEGIN
    SELECT 
        c.idcontrato,
        c.idcolaborador,
        c.tipocontrato,
        c.fechainicio,
        c.fechafin,
        CONCAT(p.apellidos, ', ', p.nombres) AS colaborador,
        co.idespecialidad,
        e.especialidad
    FROM 
        contratos c
    INNER JOIN 
        colaboradores co ON c.idcolaborador = co.idcolaborador
    INNER JOIN 
        personas p ON co.idpersona = p.idpersona
    LEFT JOIN 
        especialidades e ON co.idespecialidad = e.idespecialidad
    WHERE 
        c.idcontrato = p_idcontrato;
END //
DELIMITER ;

-- 3. Procedimiento para obtener contratos por colaborador
DELIMITER //
CREATE PROCEDURE sp_obtener_contratos_por_colaborador(
    IN p_idcolaborador INT
)
BEGIN
    SELECT 
        c.idcontrato,
        c.idcolaborador,
        c.tipocontrato,
        c.fechainicio,
        c.fechafin,
        CONCAT(p.apellidos, ', ', p.nombres) AS colaborador,
        CASE WHEN c.fechafin IS NULL OR c.fechafin >= CURDATE() THEN 'Activo' ELSE 'Finalizado' END AS estado
    FROM 
        contratos c
    INNER JOIN 
        colaboradores co ON c.idcolaborador = co.idcolaborador
    INNER JOIN 
        personas p ON co.idpersona = p.idpersona
    WHERE 
        c.idcolaborador = p_idcolaborador
    ORDER BY 
        c.fechainicio DESC;
END //
DELIMITER ;
