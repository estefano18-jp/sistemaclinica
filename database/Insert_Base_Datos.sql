-- Usando la base de datos
USE clinicaDB;

-- Insertando especialidades
INSERT INTO especialidades (especialidad, precioatencion) VALUES 
('Medicina General', 50.00),
('Cardiología', 120.00),
('Pediatría', 80.00),
('Ginecología', 100.00),
('Traumatología', 90.00),
('Dermatología', 85.00),
('Oftalmología', 75.00),
('Neurología', 150.00),
('Psiquiatría', 130.00),
('Odontología', 70.00);

-- Insertando diagnósticos
INSERT INTO diagnosticos (nombre, descripcion, codigo) VALUES 
('Hipertensión Arterial', 'Presión arterial alta sostenida', 'HTA001'),
('Diabetes Mellitus Tipo 2', 'Trastorno metabólico que causa niveles elevados de azúcar en sangre', 'DMT2001'),
('Asma Bronquial', 'Enfermedad crónica caracterizada por inflamación de las vías respiratorias', 'ASM001'),
('Gastritis Crónica', 'Inflamación persistente del revestimiento del estómago', 'GST001'),
('Migraña', 'Dolores de cabeza recurrentes moderados a intensos', 'MIG001'),
('Rinitis Alérgica', 'Inflamación de la mucosa nasal por alérgenos', 'RIN001'),
('Artrosis', 'Degeneración del cartílago articular', 'ART001'),
('Hipotiroidismo', 'Producción insuficiente de hormonas tiroideas', 'HPT001'),
('Depresión', 'Trastorno del estado de ánimo caracterizado por tristeza persistente', 'DEP001'),
('Ansiedad Generalizada', 'Preocupación y temor excesivos y persistentes', 'ANS001');

-- Insertando alergias
INSERT INTO alergias (tipoalergia, alergia) VALUES 
('Medicamento', 'Penicilina'),
('Medicamento', 'Aspirina'),
('Medicamento', 'Sulfas'),
('Alimento', 'Maní'),
('Alimento', 'Mariscos'),
('Alimento', 'Lácteos'),
('Ambiental', 'Polen'),
('Ambiental', 'Ácaros'),
('Ambiental', 'Pelo de Mascotas'),
('Picadura', 'Abeja');

-- Insertando tipos de servicios
INSERT INTO tiposervicio (tiposervicio, servicio, precioservicio) VALUES 
('Laboratorio', 'Hemograma Completo', 35.00),
('Laboratorio', 'Perfil Lipídico', 45.00),
('Laboratorio', 'Glucosa en Ayunas', 20.00),
('Laboratorio', 'Examen de Orina', 25.00),
('Imagen', 'Radiografía', 80.00),
('Imagen', 'Ecografía', 120.00),
('Imagen', 'Tomografía', 250.00),
('Procedimiento', 'Curaciones', 40.00),
('Procedimiento', 'Nebulización', 30.00),
('Procedimiento', 'Inyectables', 15.00);

-- Insertando empresas
INSERT INTO empresas (razonsocial, ruc, direccion, nombrecomercial, telefono, email) VALUES 
('SEGUROS RIMAC S.A.', '20100041953', 'Av. Paseo de la República 3505, San Isidro', 'RIMAC SEGUROS', '01-4111111', 'contacto@rimac.com.pe'),
('PACÍFICO SEGUROS', '20332970411', 'Av. Juan de Arona 830, San Isidro', 'PACÍFICO', '01-5135000', 'atencion@pacifico.com.pe'),
('MAPFRE PERÚ', '20202380621', 'Av. 28 de Julio 873, Miraflores', 'MAPFRE', '01-2137373', 'servicioalcliente@mapfre.com.pe'),
('INTERSEGURO COMPAÑÍA DE SEGUROS S.A.', '20382748566', 'Av. Pardo y Aliaga 634, San Isidro', 'INTERSEGURO', '01-6193900', 'servicioalcliente@interseguro.com.pe'),
('LA POSITIVA SEGUROS', '20100210909', 'Calle Francisco Masías 370, San Isidro', 'LA POSITIVA', '01-2110000', 'atencion@lapositiva.com.pe');

-- Insertando personas (administradores, doctores, enfermeras, pacientes)
-- Administradores
INSERT INTO personas (apellidos, nombres, tipodoc, nrodoc, telefono, fechanacimiento, genero, direccion, email) VALUES 
('Rodríguez Silva', 'Carlos Alberto', 'DNI', '45678912', '987654321', '1985-06-15', 'M', 'Av. Arequipa 1250, Lince', 'carlos.rodriguez@clinica.com'),
('Mendoza Huamán', 'María Elena', 'DNI', '40123456', '987123456', '1980-03-22', 'F', 'Calle Los Pinos 450, Miraflores', 'maria.mendoza@clinica.com');

-- Doctores (incluyendo uno para cada especialidad)
INSERT INTO personas (apellidos, nombres, tipodoc, nrodoc, telefono, fechanacimiento, genero, direccion, email) VALUES 
-- Médicos Generales
('Sánchez Torres', 'Juan Carlos', 'DNI', '30456789', '999888777', '1975-09-20', 'M', 'Av. La Marina 1050, San Miguel', 'juan.sanchez@clinica.com'),
('Torres Ramos', 'Luisa Fernanda', 'DNI', '36543210', '988777666', '1988-05-12', 'F', 'Jr. Los Halcones 320, Surquillo', 'luisa.torres@clinica.com'),
('Ramos Quispe', 'Juana María', 'DNI', '35432109', '988666555', '1990-03-08', 'F', 'Av. Venezuela 780, Breña', 'juana.ramos@clinica.com'),

-- Especialistas
('Pérez García', 'Ana María', 'DNI', '29876543', '999777666', '1978-11-05', 'F', 'Calle Las Flores 240, San Borja', 'ana.perez@clinica.com'),
('Fernández Castro', 'Roberto José', 'DNI', '31234567', '999666555', '1982-04-18', 'M', 'Av. Brasil 890, Jesús María', 'roberto.fernandez@clinica.com'),
('López Díaz', 'Carmen Rosa', 'DNI', '28765432', '999555444', '1980-07-25', 'F', 'Calle Los Olivos 180, Surco', 'carmen.lopez@clinica.com'),
('García Mendoza', 'Pedro Raúl', 'DNI', '27654321', '999444333', '1976-12-30', 'M', 'Jr. Huallaga 450, Centro de Lima', 'pedro.garcia@clinica.com'),
('Velasco Fuentes', 'Laura Melissa', 'DNI', '32567890', '998765432', '1983-05-17', 'F', 'Av. Salaverry 320, Jesús María', 'laura.velasco@clinica.com'),
('Ortega Pacheco', 'Martín Eduardo', 'DNI', '33456789', '997654321', '1977-11-23', 'M', 'Calle Los Cipreses 567, La Molina', 'martin.ortega@clinica.com'),
('Herrera Vargas', 'Sofía Alejandra', 'DNI', '34567890', '996543210', '1979-08-12', 'F', 'Av. Javier Prado 890, San Isidro', 'sofia.herrera@clinica.com'),
('Delgado Miranda', 'Alberto Javier', 'DNI', '35678901', '995432109', '1981-03-26', 'M', 'Jr. Libertad 234, Magdalena', 'alberto.delgado@clinica.com'),
('Campos Urbina', 'Diana Carolina', 'DNI', '36789012', '994321098', '1984-10-05', 'F', 'Calle Las Palmeras 456, Miraflores', 'diana.campos@clinica.com');

-- Enfermeras (nuevas)
INSERT INTO personas (apellidos, nombres, tipodoc, nrodoc, telefono, fechanacimiento, genero, direccion, email) VALUES 
('Gutiérrez Silva', 'Patricia Cecilia', 'DNI', '37890123', '993210987', '1989-07-18', 'F', 'Av. Aviación 890, San Borja', 'patricia.gutierrez@clinica.com'),
('Morales Chávez', 'Mónica Beatriz', 'DNI', '38901234', '992109876', '1987-02-25', 'F', 'Jr. Puno 567, Cercado de Lima', 'monica.morales@clinica.com');

-- Pacientes
INSERT INTO personas (apellidos, nombres, tipodoc, nrodoc, telefono, fechanacimiento, genero, direccion, email) VALUES 
('Flores Morales', 'Miguel Ángel', 'DNI', '10293847', '976543210', '1990-08-25', 'M', 'Calle Los Nogales 123, La Molina', 'miguel.flores@gmail.com'),
('Castro Lara', 'Lucía Beatriz', 'DNI', '20394857', '976432109', '1988-04-15', 'F', 'Av. Benavides 456, Miraflores', 'lucia.castro@gmail.com'),
('Díaz Romero', 'José Manuel', 'DNI', '30485967', '976321098', '1975-11-20', 'M', 'Jr. Unión 789, Barranco', 'jose.diaz@gmail.com'),
('Romero Silva', 'Claudia Patricia', 'DNI', '40586978', '976210987', '1982-06-10', 'F', 'Calle Las Begonias 234, San Isidro', 'claudia.romero@gmail.com'),
('Silva Paredes', 'Ricardo Antonio', 'DNI', '50687989', '976109876', '1978-09-05', 'M', 'Av. Javier Prado 567, San Borja', 'ricardo.silva@gmail.com'),
('Paredes Ríos', 'Mariana Isabel', 'DNI', '60788990', '976098765', '1995-03-28', 'F', 'Jr. Huáscar 890, Pueblo Libre', 'mariana.paredes@gmail.com'),
('Ríos Vargas', 'Daniel Eduardo', 'DNI', '70889001', '976987654', '1992-12-15', 'M', 'Calle Los Eucaliptos 123, La Molina', 'daniel.rios@gmail.com'),
('Vargas Rojas', 'Gabriela Sofía', 'DNI', '80990112', '976876543', '1985-07-22', 'F', 'Av. Angamos 456, Surquillo', 'gabriela.vargas@gmail.com'),
('Rojas Medina', 'Fernando José', 'DNI', '91001223', '976765432', '1970-05-18', 'M', 'Jr. Cusco 789, Magdalena', 'fernando.rojas@gmail.com'),
('Medina Chávez', 'Patricia Elena', 'DNI', '10112334', '976654321', '1980-10-30', 'F', 'Calle Los Jazmines 234, San Miguel', 'patricia.medina@gmail.com');

-- Insertando colaboradores
-- Administradores
INSERT INTO colaboradores (idpersona, idespecialidad) VALUES 
(1, NULL),  -- Carlos Rodríguez (Administrador)
(2, NULL);  -- María Mendoza (Administradora)

-- Doctores
INSERT INTO colaboradores (idpersona, idespecialidad) VALUES 
-- Médicos Generales
(3, 1),  -- Juan Sánchez (Médico General)
(4, 1),  -- Luisa Torres (Médico General) - Antes era enfermera
(5, 1),  -- Juana Ramos (Médico General) - Antes era enfermera

-- Especialistas
(6, 4),  -- Ana Pérez (Ginecóloga)
(7, 2),  -- Roberto Fernández (Cardiólogo)
(8, 3),  -- Carmen López (Pediatra)
(9, 5),  -- Pedro García (Traumatólogo)
(10, 6),  -- Laura Velasco (Dermatóloga)
(11, 7),  -- Martín Ortega (Oftalmólogo)
(12, 8), -- Sofía Herrera (Neuróloga)
(13, 9), -- Alberto Delgado (Psiquiatra)
(14, 10); -- Diana Campos (Odontóloga)

-- Enfermeras (nuevas)
INSERT INTO colaboradores (idpersona, idespecialidad) VALUES 
(15, NULL),  -- Patricia Gutiérrez (Enfermera)
(16, NULL);  -- Mónica Morales (Enfermera)

-- Insertando contratos
INSERT INTO contratos (idcolaborador, fechainicio, fechafin, tipocontrato) VALUES 
(1, '2022-01-01', NULL, 'INDEFINIDO'),  -- Carlos Rodríguez
(2, '2022-01-01', NULL, 'INDEFINIDO'),  -- María Mendoza
(3, '2022-02-01', NULL, 'INDEFINIDO'),  -- Juan Sánchez
(4, '2022-02-10', NULL, 'INDEFINIDO'),  -- Luisa Torres (Médico General)
(5, '2022-02-20', NULL, 'INDEFINIDO'),  -- Juana Ramos (Médico General)
(6, '2022-02-15', NULL, 'INDEFINIDO'),  -- Ana Pérez
(7, '2022-03-01', NULL, 'INDEFINIDO'),  -- Roberto Fernández
(8, '2022-03-15', NULL, 'INDEFINIDO'),  -- Carmen López
(9, '2022-04-01', NULL, 'INDEFINIDO'),  -- Pedro García
(10, '2022-04-15', NULL, 'INDEFINIDO'),  -- Laura Velasco
(11, '2022-05-01', NULL, 'INDEFINIDO'),  -- Martín Ortega
(12, '2022-05-15', NULL, 'INDEFINIDO'), -- Sofía Herrera
(13, '2022-06-01', NULL, 'INDEFINIDO'), -- Alberto Delgado
(14, '2022-06-15', NULL, 'INDEFINIDO'), -- Diana Campos
(15, '2022-07-01', NULL, 'INDEFINIDO'), -- Patricia Gutiérrez (Enfermera)
(16, '2022-07-15', NULL, 'INDEFINIDO'); -- Mónica Morales (Enfermera)

-- Insertando usuarios
-- Contraseñas en este ejemplo son las primeras 6 letras del nombre en minúsculas con '123' al final
-- En un entorno real, se utilizaría una función de hash adecuada como bcrypt o Argon2
INSERT INTO usuarios (idcontrato, idpaciente, nomuser, passuser, estado, rol) VALUES 
(1, NULL, 'admin.carlos', '$2y$10$5I8VF.z1V0iCJc/WN.1QT.HFZ9KXF5E2QJUijVZXPpKW3BBUj5ZEG', TRUE, 'ADMINISTRADOR'),  -- carlos123
(2, NULL, 'admin.maria', '$2y$10$FDR2gHnwDO4jr0wU1ZKiMegxiMr4rKbxUi0hnGwGqA.ZgbOq5zM0O', TRUE, 'ADMINISTRADOR'),   -- maria123
(3, NULL, 'doctor.juan', '$2y$10$PWuAdHvT5.fbQ/TDaJEwnOmGrfK9ezvgAxl6zTwzxo0aLGrB2wNT.', TRUE, 'DOCTOR'),   -- juanca123
(4, NULL, 'doctora.luisa', '$2y$10$8WJXw/0D1PnqKUE/GHoYoe3Yjxw/i.eYZBhYPQxZv8R4tFm6XEWJS', TRUE, 'DOCTOR'), -- luisaf123
(5, NULL, 'doctora.juana', '$2y$10$0Wq5c2zZTZ9rQzx7J/2mUOZbXlQ0pGAGHftC5STc6YRrq7B9sCvVO', TRUE, 'DOCTOR'), -- juanam123
(6, NULL, 'doctora.ana', '$2y$10$JdgA3QF/FNPnBWsArcKgzeZA1qLnXYm5GQgM4lD4FvY9q7nWvQIry', TRUE, 'DOCTOR'),   -- anamar123
(7, NULL, 'doctor.roberto', '$2y$10$kvdEiF8gw/DGvr3.Qkr8t.rPcL5QqfgBvz3VD5pJNd2.oBdcXBfV.', TRUE, 'DOCTOR'), -- robert123
(8, NULL, 'doctora.carmen', '$2y$10$9w6a.RqbLZnJwA/uQY0bseIaE.JdGa8BO.mOXK4xQEZJJAXwbYBwC', TRUE, 'DOCTOR'), -- carmen123
(9, NULL, 'doctor.pedro', '$2y$10$ZT9PQRGkxaKdKbIWOaKzfO0WLMQTYJGEzBQfS/r6KTQOYKqWwU2O.', TRUE, 'DOCTOR'),   -- pedrora123
(10, NULL, 'doctora.laura', '$2y$10$JF3DkfGx7TzL5pK1QZfk6O8ZxELQK.IXyX3.1y5QVR7EZXm0FgRJq', TRUE, 'DOCTOR'),   -- laura123
(11, NULL, 'doctor.martin', '$2y$10$8Kc5X3YtXJ7QJxGzKx1z4uXYIvFgQ3.pzQDM3mCfX/MgKfT2Kc0b2', TRUE, 'DOCTOR'),   -- martin123
(12, NULL, 'doctora.sofia', '$2y$10$DKJz2jVL5F5hQfMgRYxzMOqg/GmL8KLzQ7ZgX7F7YfJLJfK5QxFqq', TRUE, 'DOCTOR'),   -- sofia123
(13, NULL, 'doctor.alberto', '$2y$10$7RzQmH5fGxKVyL.KfJxg9uEbXLgZ6gQzJX1JxXgK5F5x1QJjxKfK2', TRUE, 'DOCTOR'),   -- albert123
(14, NULL, 'doctora.diana', '$2y$10$pKFJz7HyJkLzQJXGkzKf8OxQJz5xKfFJxGfJxGJfKjFKJxGzKjXGK', TRUE, 'DOCTOR'),   -- dianac123
(15, NULL, 'enfermera.patricia', '$2y$10$GKJz2jVL5F5hQfMgRYxzMOqg/GmL8KLzQ7ZgX7F7YfJLJfK5QxFqq', TRUE, 'ENFERMERO'),   -- patric123
(16, NULL, 'enfermera.monica', '$2y$10$HKFJz7HyJkLzQJXGkzKf8OxQJz5xKfFJxGfJxGJfKjFKJxGzKjXGK', TRUE, 'ENFERMERO');   -- monica123

-- Insertando pacientes
INSERT INTO pacientes (idpersona, fecharegistro) VALUES 
(17, '2023-01-10'),  -- Miguel Flores
(18, '2023-01-15'),  -- Lucía Castro
(19, '2023-02-05'),  -- José Díaz
(20, '2023-02-20'),  -- Claudia Romero
(21, '2023-03-08'),  -- Ricardo Silva
(22, '2023-03-25'),  -- Mariana Paredes
(23, '2023-04-12'),  -- Daniel Ríos
(24, '2023-04-30'),  -- Gabriela Vargas
(25, '2023-05-15'),  -- Fernando Rojas
(26, '2023-05-28');  -- Patricia Medina

-- Insertando clientes
INSERT INTO clientes (tipocliente, idempresa, idpersona) VALUES 
('NATURAL', NULL, 17),  -- Miguel Flores
('NATURAL', NULL, 18),  -- Lucía Castro
('NATURAL', NULL, 19),  -- José Díaz
('NATURAL', NULL, 20),  -- Claudia Romero
('EMPRESA', 1, NULL),   -- Rímac Seguros
('EMPRESA', 2, NULL);   -- Pacífico Seguros

-- HORARIOS PARA TODOS LOS DOCTORES (todos atienden los jueves)

-- 1. MEDICINA GENERAL: Dr. Juan Sánchez (Médico General)
INSERT INTO atenciones (idcontrato, diasemana) VALUES 
(3, 'LUNES'),
(3, 'MIERCOLES'),
(3, 'JUEVES'),
(3, 'VIERNES');

INSERT INTO horarios (idatencion, horainicio, horafin) VALUES 
(1, '08:00:00', '13:00:00'),  -- Lunes mañana
(2, '14:00:00', '20:00:00'),  -- Miércoles tarde
(3, '16:00:00', '20:00:00'),  -- Jueves tarde
(4, '08:00:00', '13:00:00');  -- Viernes mañana

-- 2. MEDICINA GENERAL: Dra. Luisa Torres (Médico General)
INSERT INTO atenciones (idcontrato, diasemana) VALUES 
(4, 'LUNES'),
(4, 'JUEVES'),
(4, 'SABADO');

INSERT INTO horarios (idatencion, horainicio, horafin) VALUES 
(5, '14:00:00', '20:00:00'),  -- Lunes tarde
(6, '09:00:00', '14:00:00'),  -- Jueves mañana
(7, '08:00:00', '13:00:00');  -- Sábado mañana

-- 3. MEDICINA GENERAL: Dra. Juana Ramos (Médico General)
INSERT INTO atenciones (idcontrato, diasemana) VALUES 
(5, 'MARTES'),
(5, 'JUEVES'),
(5, 'VIERNES');

INSERT INTO horarios (idatencion, horainicio, horafin) VALUES 
(8, '08:00:00', '13:00:00'),  -- Martes mañana
(9, '15:00:00', '20:00:00'),  -- Jueves tarde
(10, '14:00:00', '20:00:00'); -- Viernes tarde

-- 4. CARDIOLOGÍA: Dr. Roberto Fernández (Cardiólogo)
INSERT INTO atenciones (idcontrato, diasemana) VALUES 
(7, 'LUNES'),
(7, 'JUEVES');

INSERT INTO horarios (idatencion, horainicio, horafin) VALUES 
(11, '14:00:00', '20:00:00'),  -- Lunes tarde
(12, '08:00:00', '13:00:00');  -- Jueves mañana

-- 5. PEDIATRÍA: Dra. Carmen López (Pediatra)
INSERT INTO atenciones (idcontrato, diasemana) VALUES 
(8, 'MARTES'),
(8, 'JUEVES'),
(8, 'VIERNES');

INSERT INTO horarios (idatencion, horainicio, horafin) VALUES 
(13, '14:00:00', '20:00:00'),  -- Martes tarde
(14, '14:00:00', '18:00:00'),  -- Jueves tarde
(15, '08:00:00', '13:00:00');  -- Viernes mañana

-- 6. GINECOLOGÍA: Dra. Ana Pérez (Ginecóloga)
INSERT INTO atenciones (idcontrato, diasemana) VALUES 
(6, 'MARTES'),
(6, 'JUEVES');

INSERT INTO horarios (idatencion, horainicio, horafin) VALUES 
(16, '08:00:00', '13:00:00'),  -- Martes mañana
(17, '14:00:00', '20:00:00');  -- Jueves tarde

-- 7. TRAUMATOLOGÍA: Dr. Pedro García (Traumatólogo)
INSERT INTO atenciones (idcontrato, diasemana) VALUES 
(9, 'MIERCOLES'),
(9, 'JUEVES'),
(9, 'SABADO');

INSERT INTO horarios (idatencion, horainicio, horafin) VALUES 
(18, '08:00:00', '13:00:00'), -- Miércoles mañana
(19, '08:00:00', '12:00:00'), -- Jueves mañana
(20, '09:00:00', '14:00:00'); -- Sábado

-- 8. DERMATOLOGÍA: Dra. Laura Velasco (Dermatóloga)
INSERT INTO atenciones (idcontrato, diasemana) VALUES 
(10, 'LUNES'),
(10, 'JUEVES');

INSERT INTO horarios (idatencion, horainicio, horafin) VALUES 
(21, '08:00:00', '13:00:00'), -- Lunes mañana
(22, '14:00:00', '19:00:00'); -- Jueves tarde

-- 9. OFTALMOLOGÍA: Dr. Martín Ortega (Oftalmólogo)
INSERT INTO atenciones (idcontrato, diasemana) VALUES 
(11, 'MARTES'),
(11, 'JUEVES'),
(11, 'VIERNES');

INSERT INTO horarios (idatencion, horainicio, horafin) VALUES 
(23, '14:00:00', '20:00:00'), -- Martes tarde
(24, '08:00:00', '12:00:00'), -- Jueves mañana
(25, '08:00:00', '13:00:00'); -- Viernes mañana

-- 10. NEUROLOGÍA: Dra. Sofía Herrera (Neuróloga)
INSERT INTO atenciones (idcontrato, diasemana) VALUES 
(12, 'MIERCOLES'),
(12, 'JUEVES'),
(12, 'VIERNES');

INSERT INTO horarios (idatencion, horainicio, horafin) VALUES 
(26, '08:00:00', '13:00:00'), -- Miércoles mañana
(27, '16:00:00', '20:00:00'), -- Jueves tarde
(28, '14:00:00', '19:00:00'); -- Viernes tarde

-- 11. PSIQUIATRÍA: Dr. Alberto Delgado (Psiquiatra)
INSERT INTO atenciones (idcontrato, diasemana) VALUES 
(13, 'JUEVES'),
(13, 'SABADO');

INSERT INTO horarios (idatencion, horainicio, horafin) VALUES 
(29, '14:00:00', '20:00:00'), -- Jueves tarde
(30, '08:00:00', '13:00:00'); -- Sábado mañana

-- 12. ODONTOLOGÍA: Dra. Diana Campos (Odontóloga)
INSERT INTO atenciones (idcontrato, diasemana) VALUES 
(14, 'LUNES'),
(14, 'MIERCOLES'),
(14, 'JUEVES');

INSERT INTO horarios (idatencion, horainicio, horafin) VALUES 
(31, '14:00:00', '20:00:00'), -- Lunes tarde
(32, '08:00:00', '13:00:00'), -- Miércoles mañana
(33, '12:00:00', '16:00:00'); -- Jueves mediodía