USE clinicaDB;

-- 1. Procedimiento para registrar información profesional del doctor
DELIMITER //
CREATE PROCEDURE sp_registrar_doctor_profesional(
    IN p_idpersona INT,
    IN p_idespecialidad INT,
    IN p_precioatencion DECIMAL(10,2),
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(255),
    OUT p_idcolaborador INT
)
BEGIN
    -- Registrar como colaborador con su especialidad y precio
    INSERT INTO colaboradores (idpersona, idespecialidad, precioatencion)
    VALUES (p_idpersona, p_idespecialidad, p_precioatencion);
    
    SET p_idcolaborador = LAST_INSERT_ID();
    SET p_resultado = 1;
    SET p_mensaje = 'Información profesional del doctor registrada correctamente';
END //
DELIMITER ;

-- 2. Procedimiento para obtener información detallada de un colaborador por ID
DELIMITER //
CREATE PROCEDURE sp_obtener_colaborador_por_id(
    IN p_idcolaborador INT
)
BEGIN
    SELECT 
        c.idcolaborador, 
        c.idpersona, 
        c.idespecialidad, 
        e.especialidad, 
        c.precioatencion, 
        p.apellidos, 
        p.nombres, 
        CONCAT(p.apellidos, ', ', p.nombres) AS nombre_completo, 
        p.tipodoc, 
        p.nrodoc, 
        p.telefono, 
        p.fechanacimiento, 
        p.genero, 
        p.direccion, 
        p.email 
    FROM 
        colaboradores c 
    INNER JOIN 
        personas p ON c.idpersona = p.idpersona 
    LEFT JOIN 
        especialidades e ON c.idespecialidad = e.idespecialidad 
    WHERE 
        c.idcolaborador = p_idcolaborador;
END //
DELIMITER ;