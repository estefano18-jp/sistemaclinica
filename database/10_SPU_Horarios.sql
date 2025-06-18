USE clinicaDB;

-- 1. Procedimiento para registrar horario de atención (principalmente para doctores)
DELIMITER //
CREATE PROCEDURE sp_registrar_horario(
    IN p_idcolaborador INT,
    IN p_dia VARCHAR(20),
    IN p_horainicio TIME,
    IN p_horafin TIME,
    OUT p_resultado INT,
    OUT p_mensaje VARCHAR(255)
)
BEGIN
    -- Registrar horario
    INSERT INTO horarios_atencion (idcolaborador, dia, horainicio, horafin)
    VALUES (p_idcolaborador, p_dia, p_horainicio, p_horafin);
    
    SET p_resultado = 1;
    SET p_mensaje = CONCAT('Horario para el día ', p_dia, ' registrado correctamente');
END //
DELIMITER ;

-- 2. Procedimiento para obtener horario por ID
DELIMITER //
CREATE PROCEDURE sp_obtener_horario_por_id(
    IN p_idhorario INT
)
BEGIN
    SELECT 
        h.idhorario,
        h.idcolaborador,
        h.dia,
        h.horainicio,
        h.horafin,
        CONCAT(p.apellidos, ', ', p.nombres) AS colaborador,
        e.especialidad
    FROM 
        horarios_atencion h
    INNER JOIN 
        colaboradores c ON h.idcolaborador = c.idcolaborador
    INNER JOIN 
        personas p ON c.idpersona = p.idpersona
    LEFT JOIN 
        especialidades e ON c.idespecialidad = e.idespecialidad
    WHERE 
        h.idhorario = p_idhorario;
END //
DELIMITER ;

-- 3. Procedimiento para obtener horarios por colaborador
DELIMITER //
CREATE PROCEDURE sp_obtener_horarios_por_colaborador(
    IN p_idcolaborador INT
)
BEGIN
    SELECT 
        h.idhorario,
        h.idcolaborador,
        h.dia,
        h.horainicio,
        h.horafin,
        CONCAT(p.apellidos, ', ', p.nombres) AS colaborador,
        e.especialidad
    FROM 
        horarios_atencion h
    INNER JOIN 
        colaboradores c ON h.idcolaborador = c.idcolaborador
    INNER JOIN 
        personas p ON c.idpersona = p.idpersona
    LEFT JOIN 
        especialidades e ON c.idespecialidad = e.idespecialidad
    WHERE 
        h.idcolaborador = p_idcolaborador
    ORDER BY 
        FIELD(h.dia, 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'),
        h.horainicio;
END //
DELIMITER ;


---
DELIMITER //
CREATE PROCEDURE sp_consultar_horarios_por_documento(
    IN p_nro_documento VARCHAR(20) -- Número de documento del doctor
)
BEGIN
    SELECT 
        p.nrodoc AS 'Número de Documento',
        p.tipodoc AS 'Tipo de Documento',
        CONCAT(p.apellidos, ', ', p.nombres) AS 'Nombre del Doctor',
        e.especialidad AS 'Especialidad',
        a.diasemana AS 'Día',
        h.horainicio AS 'Hora de Inicio',
        h.horafin AS 'Hora de Fin'
    FROM 
        horarios h
        INNER JOIN atenciones a ON h.idatencion = a.idatencion
        INNER JOIN contratos c ON a.idcontrato = c.idcontrato
        INNER JOIN colaboradores col ON c.idcolaborador = col.idcolaborador
        INNER JOIN personas p ON col.idpersona = p.idpersona
        INNER JOIN especialidades e ON col.idespecialidad = e.idespecialidad
    WHERE 
        p.nrodoc = p_nro_documento -- Filtro por número de documento
        AND col.idespecialidad IS NOT NULL -- Para asegurar que solo sea personal médico
    ORDER BY 
        FIELD(a.diasemana, 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'),
        h.horainicio;
END //
DELIMITER ;

CALL sp_listar_doctores();
CALL sp_buscar_doctor_por_documento('45645435');  
CALL sp_consultar_horarios_por_documento('34534535'); -- Reemplaza con el número de documento del doctor
