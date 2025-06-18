USE clinicaDB;

DELIMITER $$
CREATE PROCEDURE sp_buscar_persona_por_documento(
    IN p_nrodoc VARCHAR(20)
)
BEGIN
    SELECT * FROM personas WHERE nrodoc = p_nrodoc;
END $$
DELIMITER ;

CALL sp_buscar_persona_por_documento('47583565');

SELECT * FROM personas;
SELECT * FROM especialidades;
SELECT * FROM empresas;