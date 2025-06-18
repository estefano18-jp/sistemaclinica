USE clinicaDB;

-- Procedimiento para login de administrador
DELIMITER $$
CREATE PROCEDURE spu_usuario_login_administrador(
    IN p_nomuser VARCHAR(100),
    IN p_passuser VARCHAR(100)
)
BEGIN
    SELECT 
        u.idusuario, 
        p.nombres, 
        p.apellidos, 
        u.passuser,
        u.rol
    FROM usuarios u
    INNER JOIN contratos c ON u.idcontrato = c.idcontrato
    INNER JOIN colaboradores col ON c.idcolaborador = col.idcolaborador
    INNER JOIN personas p ON col.idpersona = p.idpersona
    WHERE 
        u.nomuser = p_nomuser
        AND u.rol = 'ADMINISTRADOR'
    LIMIT 1;
END $$ 
DELIMITER ;

-- Procedimiento para registrar un administrador
DELIMITER $$
CREATE PROCEDURE spu_usuario_registrar_administrador(
    IN _idpersona INT,
    IN _nomuser VARCHAR(50),
    IN _passuser VARCHAR(255),
    IN _estado BOOLEAN
)
BEGIN
    -- Crear colaborador (como administrador, sin especialidad)
    INSERT INTO colaboradores (idpersona, idespecialidad)
    VALUES (_idpersona, NULL);
    
    -- Obtener el ID del colaborador recién insertado
    SET @idcolaborador = LAST_INSERT_ID();
    
    -- Crear contrato para el colaborador
    INSERT INTO contratos (idcolaborador, fechainicio, fechafin, tipocontrato)
    VALUES (@idcolaborador, CURDATE(), NULL, 'INDEFINIDO');
    
    -- Obtener el ID del contrato recién insertado
    SET @idcontrato = LAST_INSERT_ID();
    
    -- Insertar usuario con rol ADMINISTRADOR
    INSERT INTO usuarios (idcontrato, nomuser, passuser, estado, rol)
    VALUES (@idcontrato, _nomuser, _passuser, _estado, 'ADMINISTRADOR');

    -- Retornar el ID del usuario insertado
    SELECT LAST_INSERT_ID() AS idusuario;
END $$
DELIMITER ;

-- Procedimiento para login de doctores
DELIMITER $$
CREATE PROCEDURE spu_usuario_login_doctor(
    IN p_email VARCHAR(100)
)
BEGIN
    SELECT 
        u.idusuario, 
        u.passuser, 
        p.idpersona, 
        p.apellidos, 
        p.nombres, 
        u.estado, 
        c.idcolaborador, 
        c.idespecialidad, 
        e.especialidad, 
        u.rol
    FROM usuarios u
    INNER JOIN contratos ct ON u.idcontrato = ct.idcontrato
    INNER JOIN colaboradores c ON ct.idcolaborador = c.idcolaborador
    INNER JOIN personas p ON c.idpersona = p.idpersona
    LEFT JOIN especialidades e ON c.idespecialidad = e.idespecialidad
    WHERE p.email = p_email 
    AND u.rol = 'DOCTOR'
    AND c.estado = 'ACTIVO' 
    AND u.estado = TRUE;
END $$
DELIMITER ;

-- Procedimiento para registrar un doctor
DELIMITER $$
CREATE PROCEDURE spu_usuario_registrar_doctor(
    IN p_idpersona INT,
    IN p_idespecialidad INT,
    IN p_nomuser VARCHAR(50),
    IN p_passuser VARCHAR(255)
)
BEGIN
    DECLARE v_idcolaborador INT;
    DECLARE v_idcontrato INT;
    
    -- Insertar colaborador (como doctor, con especialidad)
    INSERT INTO colaboradores (idpersona, idespecialidad)
    VALUES (p_idpersona, p_idespecialidad);
    
    SET v_idcolaborador = LAST_INSERT_ID();
    
    -- Insertar contrato
    INSERT INTO contratos (idcolaborador, fechainicio, fechafin, tipocontrato)
    VALUES (v_idcolaborador, CURDATE(), NULL, 'INDEFINIDO');
    
    SET v_idcontrato = LAST_INSERT_ID();
    
    -- Insertar usuario con rol DOCTOR
    INSERT INTO usuarios (idcontrato, nomuser, passuser, estado, rol)
    VALUES (v_idcontrato, p_nomuser, p_passuser, TRUE, 'DOCTOR');
    
    -- Retornar el ID del usuario insertado
    SELECT LAST_INSERT_ID() AS idusuario;
END $$ 
DELIMITER ;

-- Procedimiento para login de enfermeros
DELIMITER $$
CREATE PROCEDURE spu_usuario_login_enfermero(
    IN p_email VARCHAR(100)
)
BEGIN
    SELECT 
        u.idusuario, 
        u.passuser, 
        p.idpersona, 
        p.apellidos, 
        p.nombres, 
        u.estado, 
        c.idcolaborador, 
        u.rol
    FROM usuarios u
    INNER JOIN contratos ct ON u.idcontrato = ct.idcontrato
    INNER JOIN colaboradores c ON ct.idcolaborador = c.idcolaborador
    INNER JOIN personas p ON c.idpersona = p.idpersona
    WHERE p.email = p_email 
    AND u.rol = 'ENFERMERO'
    AND c.estado = 'ACTIVO' 
    AND u.estado = TRUE;
END $$
DELIMITER ;

-- Procedimiento para registrar un enfermero
DELIMITER $$
CREATE PROCEDURE spu_usuario_registrar_enfermero(
    IN p_idpersona INT,
    IN p_nomuser VARCHAR(50),
    IN p_passuser VARCHAR(255)
)
BEGIN
    DECLARE v_idcolaborador INT;
    DECLARE v_idcontrato INT;
    
    -- Insertar colaborador (como enfermero, sin especialidad)
    INSERT INTO colaboradores (idpersona, idespecialidad)
    VALUES (p_idpersona, NULL);
    
    SET v_idcolaborador = LAST_INSERT_ID();
    
    -- Insertar contrato
    INSERT INTO contratos (idcolaborador, fechainicio, fechafin, tipocontrato)
    VALUES (v_idcolaborador, CURDATE(), NULL, 'INDEFINIDO');
    
    SET v_idcontrato = LAST_INSERT_ID();
    
    -- Insertar usuario con rol ENFERMERO
    INSERT INTO usuarios (idcontrato, nomuser, passuser, estado, rol)
    VALUES (v_idcontrato, p_nomuser, p_passuser, TRUE, 'ENFERMERO');
    
    -- Retornar el ID del usuario insertado
    SELECT LAST_INSERT_ID() AS idusuario;
END $$ 
DELIMITER ;

-- Procedimiento para login de pacientes
DELIMITER $$
CREATE PROCEDURE spu_usuario_login_paciente(
    IN p_nrodoc VARCHAR(20)
)
BEGIN
    SELECT 
        u.idusuario, 
        u.passuser, 
        p.idpersona, 
        pa.idpaciente,
        p.apellidos, 
        p.nombres, 
        u.estado, 
        u.rol
    FROM usuarios u
    INNER JOIN pacientes pa ON u.idpaciente = pa.idpaciente
    INNER JOIN personas p ON pa.idpersona = p.idpersona
    WHERE p.nrodoc = p_nrodoc 
    AND u.rol = 'PACIENTE'
    AND u.estado = TRUE;
END $$
DELIMITER ;

-- Procedimiento para registrar un paciente como usuario
DELIMITER $$
CREATE PROCEDURE spu_usuario_registrar_paciente(
    IN p_idpaciente INT,
    IN p_passuser VARCHAR(255)
)
BEGIN
    DECLARE v_nrodoc VARCHAR(20);
    
    -- Obtener el número de documento del paciente para usarlo como nombre de usuario
    SELECT p.nrodoc INTO v_nrodoc
    FROM pacientes pa
    INNER JOIN personas p ON pa.idpersona = p.idpersona
    WHERE pa.idpaciente = p_idpaciente;
    
    -- Insertar usuario con rol PACIENTE
    INSERT INTO usuarios (idpaciente, nomuser, passuser, estado, rol)
    VALUES (p_idpaciente, v_nrodoc, p_passuser, TRUE, 'PACIENTE');
    
    -- Retornar el ID del usuario insertado
    SELECT LAST_INSERT_ID() AS idusuario;
END $$ 
DELIMITER ;

-- Procedimiento para obtener datos de un usuario por su ID
DELIMITER $$
CREATE PROCEDURE spu_usuarios_obtener_datos(
    IN p_idusuario INT
)
BEGIN
    -- Primero determinamos el rol del usuario
    DECLARE v_rol VARCHAR(20);
    
    SELECT rol INTO v_rol FROM usuarios WHERE idusuario = p_idusuario;
    
    -- Según el rol, obtenemos los datos correspondientes
    IF v_rol IN ('ADMINISTRADOR', 'DOCTOR', 'ENFERMERO') THEN
        SELECT 
            p.idpersona, 
            p.nombres, 
            p.apellidos, 
            p.tipodoc,
            p.nrodoc, 
            p.telefono, 
            p.fechanacimiento,
            p.genero,
            p.direccion,
            p.email,
            u.rol
        FROM personas p 
        INNER JOIN colaboradores c ON p.idpersona = c.idpersona
        INNER JOIN contratos ct ON c.idcolaborador = ct.idcolaborador
        INNER JOIN usuarios u ON ct.idcontrato = u.idcontrato
        WHERE u.idusuario = p_idusuario;
    ELSEIF v_rol = 'PACIENTE' THEN
        SELECT 
            p.idpersona, 
            p.nombres, 
            p.apellidos, 
            p.tipodoc,
            p.nrodoc, 
            p.telefono, 
            p.fechanacimiento,
            p.genero,
            p.direccion,
            p.email,
            u.rol
        FROM personas p 
        INNER JOIN pacientes pa ON p.idpersona = pa.idpersona
        INNER JOIN usuarios u ON pa.idpaciente = u.idpaciente
        WHERE u.idusuario = p_idusuario;
    END IF;
END $$
DELIMITER ;

-- Procedimiento para verificar si existe un nombre de usuario
DELIMITER $$
CREATE PROCEDURE spu_usuarios_verificar_nombre(
    IN p_nomuser VARCHAR(50)
)
BEGIN
    SELECT COUNT(*) as existe
    FROM usuarios
    WHERE nomuser = p_nomuser;
END $$
DELIMITER ;

-- Procedimiento para verificar si existe una persona por su número de documento
DELIMITER $$
CREATE PROCEDURE spu_personas_verificar_documento(
    IN p_nrodoc VARCHAR(20)
)
BEGIN
    SELECT idpersona
    FROM personas
    WHERE nrodoc = p_nrodoc;
END $$
DELIMITER ;

-- Procedimiento para verificar si existe un usuario asociado a un número de documento
DELIMITER $$
CREATE PROCEDURE spu_usuarios_verificar_documento(
    IN p_nrodoc VARCHAR(20)
)
BEGIN
    -- Verificar para colaboradores (admin, doctor, enfermero)
    SELECT u.idusuario, u.rol
    FROM usuarios u
    INNER JOIN contratos ct ON u.idcontrato = ct.idcontrato
    INNER JOIN colaboradores c ON ct.idcolaborador = c.idcolaborador
    INNER JOIN personas p ON c.idpersona = p.idpersona
    WHERE p.nrodoc = p_nrodoc
    
    UNION
    
    -- Verificar para pacientes
    SELECT u.idusuario, u.rol
    FROM usuarios u
    INNER JOIN pacientes pa ON u.idpaciente = pa.idpaciente
    INNER JOIN personas p ON pa.idpersona = p.idpersona
    WHERE p.nrodoc = p_nrodoc;
END $$
DELIMITER ;

-- Insertando personas
INSERT INTO personas (apellidos, nombres, tipodoc, nrodoc, telefono, fechanacimiento, genero, direccion, email) 
VALUES ('Alvarez', 'Dyer', 'DNI', '47583565', '968540455', '2004-10-11', 'M', 'Av. Siempre Viva 123', 'dyer@email.com'),
       ('Olivos', 'Edu', 'DNI', '49853642', '990568456', '2004-02-15', 'M', 'Calle Ficticia 456', 'edu@email.com'),
       ('Sánchez', 'Guilio', 'DNI', '54321678', '909576462', '1990-05-20', 'M', 'Calle Ejemplo 789', 'guilio@email.com');
       
-- Insertando colaboradores (como administradores, sin especialidad)
INSERT INTO colaboradores (idpersona, idespecialidad) 
VALUES (27, NULL), 
       (28, NULL), 
       (29, NULL);
              
-- Insertando contratos
INSERT INTO contratos (idcolaborador, fechainicio, fechafin, tipocontrato) 
VALUES (17, CURDATE(), NULL, 'INDEFINIDO'),
       (18, CURDATE(), NULL, 'INDEFINIDO'),
       (19, CURDATE(), NULL, 'INDEFINIDO');
       
-- Insertando usuarios con contraseñas encriptadas
INSERT INTO usuarios (idcontrato, nomuser, passuser, estado) 
VALUES (17, 'dayer', '', TRUE),
       (18, 'edu', '', TRUE),
       (19, 'guilio', '', TRUE);

-- Contraseñas encriptadas

-- Administradores:
UPDATE usuarios SET passuser = '' WHERE idusuario = 1;  -- admin.carlos
UPDATE usuarios SET passuser = '' WHERE idusuario = 2;  -- admin.maria

-- Doctores:
UPDATE usuarios SET passuser = '' WHERE idusuario = 3;  -- doctor.juan
UPDATE usuarios SET passuser = '' WHERE idusuario = 4;  -- doctora.ana
UPDATE usuarios SET passuser = '' WHERE idusuario = 5;  -- doctor.roberto
UPDATE usuarios SET passuser = '' WHERE idusuario = 6;  -- doctora.carmen
UPDATE usuarios SET passuser = '' WHERE idusuario = 7;  -- doctor.pedro

-- Enfermeras:
UPDATE usuarios SET passuser = '' WHERE idusuario = 8;  -- enfermera.luisa
UPDATE usuarios SET passuser = '' WHERE idusuario = 9;  -- enfermera.juana

-- Nuevo Administrador (recien insertados):
UPDATE usuarios SET passuser = '$2y$10$KAvzdeZhgX4zpKf.Vj97YOpUmCoXBLnNRZx1ZZ9Vyqsx.Qc7Q0AJ2' WHERE idusuario = 17; -- dayer (Dyer Alvarez)
UPDATE usuarios SET passuser = '$2y$10$qgoWoXqgNMILNy01xmzWa.iJDRp9UdksJ7WfLA0tk9/kL9lGm7HJW' WHERE idusuario = 18; -- edu (Edu Olivos)
UPDATE usuarios SET passuser = '$2y$10$b6PcVFsSTXJBlI2uQKzNGO9YLgEFsyezYQuhXZ9NWzgrVW3qUkcjm' WHERE idusuario = 19; -- guilio (Guilio Sánchez)

SELECT * FROM pacientes;
SELECT * FROM personas;
SELECT * FROM colaboradores;
SELECT * FROM contratos;
SELECT * FROM usuarios;
SELECT * FROM ventas;
SELECT * FROM empresas;
SELECT * FROM citas;
SELECT * FROM devoluciones;
SELECT * FROM detalleventas;
SELECT * FROM clientes;
SELECT * FROM especialidades;