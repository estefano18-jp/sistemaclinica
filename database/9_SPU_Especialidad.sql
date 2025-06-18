-- Agregar columna de estado a la tabla especialidades si no existe
ALTER TABLE especialidades 
ADD COLUMN estado ENUM('ACTIVO', 'INACTIVO') NOT NULL DEFAULT 'ACTIVO'
AFTER precioatencion;

-- Procedimiento almacenado para cambiar el estado de una especialidad
DELIMITER $$
CREATE PROCEDURE spu_especialidades_cambiar_estado(
    IN p_idespecialidad INT,
    IN p_estado ENUM('ACTIVO', 'INACTIVO')
)
BEGIN
    UPDATE especialidades
    SET 
        estado = p_estado
    WHERE 
        idespecialidad = p_idespecialidad;
    
    SELECT ROW_COUNT() > 0 AS actualizado;
END $$
DELIMITER ;

-- Modificar el procedimiento para listar especialidades incluyendo el estado
DROP PROCEDURE IF EXISTS spu_especialidades_listar;
DELIMITER $$
CREATE PROCEDURE spu_especialidades_listar()
BEGIN
    SELECT 
        idespecialidad,
        especialidad,
        precioatencion,
        estado
    FROM 
        especialidades
    ORDER BY 
        especialidad;
END $$
DELIMITER ;

-- Modificar el procedimiento para obtener especialidad por ID incluyendo estado
DROP PROCEDURE IF EXISTS spu_especialidades_obtener_por_id;
DELIMITER $$
CREATE PROCEDURE spu_especialidades_obtener_por_id(
    IN p_idespecialidad INT
)
BEGIN
    SELECT 
        idespecialidad,
        especialidad,
        precioatencion,
        estado
    FROM 
        especialidades
    WHERE 
        idespecialidad = p_idespecialidad;
END $$
DELIMITER ;

-- Modificar el procedimiento de búsqueda para incluir estado
DROP PROCEDURE IF EXISTS spu_especialidades_buscar;
DELIMITER $$
CREATE PROCEDURE spu_especialidades_buscar(
    IN p_termino VARCHAR(100)
)
BEGIN
    SELECT 
        idespecialidad,
        especialidad,
        precioatencion,
        estado
    FROM 
        especialidades
    WHERE 
        especialidad LIKE CONCAT('%', p_termino, '%')
    ORDER BY 
        especialidad;
END $$
DELIMITER ;