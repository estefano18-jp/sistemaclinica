-- Crear las tablas para las especialidades del sitio web
USE clinicaDB;

-- Tabla para las especialidades del sitio web (separada de la tabla especialidades existente)
CREATE TABLE web_especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    icono VARCHAR(50) NOT NULL,
    descripcion_corta TEXT NOT NULL,
    descripcion_larga TEXT,
    imagen VARCHAR(255) DEFAULT 'default-specialty.jpg',
    estado TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
    orden INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    idespecialidad INT,
    FOREIGN KEY (idespecialidad) REFERENCES especialidades(idespecialidad) ON DELETE SET NULL
    -- La clave foránea es opcional, permite relacionar con la tabla especialidades existente
);

-- Tabla para los servicios de cada especialidad web
CREATE TABLE web_especialidades_servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    web_especialidad_id INT NOT NULL,
    servicio VARCHAR(255) NOT NULL,
    FOREIGN KEY (web_especialidad_id) REFERENCES web_especialidades(id) ON DELETE CASCADE
);

-- Insertar datos en la tabla web_especialidades
INSERT INTO web_especialidades (nombre, icono, descripcion_corta, descripcion_larga, imagen, orden) VALUES
('Cardiología', 'fas fa-heartbeat', 'Diagnóstico y tratamiento integral de enfermedades cardiovasculares con equipos de última generación.', 'Nuestro departamento de Cardiología está equipado con la última tecnología para el diagnóstico y tratamiento de enfermedades cardiovasculares. Contamos con especialistas certificados que brindan atención personalizada a cada paciente. Ofrecemos pruebas de esfuerzo, ecocardiografías, monitoreo Holter y procedimientos intervencionistas.', 'cardiologia.jpg', 1),
('Neurología', 'fas fa-brain', 'Evaluación y tratamiento de trastornos del sistema nervioso por médicos altamente especializados.', 'El servicio de Neurología de nuestra clínica ofrece diagnóstico y tratamiento de enfermedades que afectan al cerebro, la médula espinal y el sistema nervioso periférico. Nuestro equipo de neurólogos altamente capacitados utiliza técnicas avanzadas para evaluar y tratar condiciones neurológicas complejas.', 'neurologia.jpg', 2),
('Pediatría', 'fas fa-baby', 'Cuidados especializados para la salud y el desarrollo de bebés, niños y adolescentes.', 'Nuestro departamento de Pediatría ofrece atención integral a pacientes desde el nacimiento hasta la adolescencia. Nos enfocamos en el crecimiento saludable, prevención de enfermedades, diagnóstico oportuno y tratamiento de condiciones pediátricas. Nuestros pediatras están dedicados a crear un ambiente amigable para que los niños se sientan cómodos durante sus visitas.', 'pediatria.jpg', 3),
('Odontología', 'fas fa-tooth', 'Servicios odontológicos completos para toda la familia, desde prevención hasta tratamientos específicos.', 'El servicio de Odontología de nuestra clínica ofrece tratamientos dentales de alta calidad para pacientes de todas las edades. Contamos con odontólogos especializados y tecnología moderna para garantizar el cuidado óptimo de su salud bucal. Desde limpiezas preventivas hasta procedimientos restaurativos complejos, nos enfocamos en brindar atención personalizada.', 'odontologia.jpg', 4),
('Traumatología', 'fas fa-bone', 'Tratamiento de lesiones y afecciones que afectan al sistema músculo-esquelético.', 'Nuestro departamento de Traumatología se especializa en el diagnóstico, tratamiento y rehabilitación de lesiones y enfermedades del sistema músculo-esquelético. Contamos con traumatólogos experimentados que utilizan técnicas modernas para el manejo de fracturas, lesiones deportivas, problemas articulares y condiciones degenerativas.', 'traumatologia.jpg', 5),
('Ginecología', 'fas fa-venus', 'Atención integral para la salud de la mujer, incluyendo control de embarazo y tratamientos especializados.', 'El servicio de Ginecología de nuestra clínica ofrece atención médica especializada para todas las etapas de la vida de la mujer. Desde la adolescencia hasta la post-menopausia, nuestros ginecólogos brindan cuidados preventivos, diagnóstico y tratamiento de condiciones ginecológicas, así como seguimiento durante el embarazo y parto.', 'ginecologia.jpg', 6),
('Dermatología', 'fas fa-allergies', 'Diagnóstico y tratamiento de afecciones de la piel, cabello y uñas con tecnología avanzada.', 'Nuestro departamento de Dermatología ofrece diagnóstico y tratamiento de enfermedades que afectan la piel, el cabello y las uñas. Con dermatólogos certificados y equipamiento moderno, brindamos atención para condiciones como acné, eczema, psoriasis, infecciones cutáneas y otros problemas dermatológicos. También realizamos procedimientos estéticos y detección de cáncer de piel.', 'dermatologia.jpg', 7),
('Oftalmología', 'fas fa-eye', 'Evaluación y tratamiento de problemas visuales y enfermedades oculares para todas las edades.', 'El servicio de Oftalmología de nuestra clínica ofrece evaluación y tratamiento completo para todas las condiciones oculares. Nuestros oftalmólogos utilizan tecnología de vanguardia para diagnosticar y tratar problemas de visión, enfermedades oculares y realizar cirugías cuando sea necesario. Atendemos pacientes de todas las edades, desde niños hasta adultos mayores.', 'oftalmologia.jpg', 8),
('Psicología', 'fas fa-brain', 'Atención profesional para el bienestar emocional y mental, con terapias personalizadas.', 'Nuestro departamento de Psicología ofrece apoyo para el bienestar emocional y mental de nuestros pacientes. Contamos con psicólogos experimentados que brindan evaluación psicológica, terapia individual, terapia de pareja y familiar, y tratamiento para diversos trastornos mentales. Trabajamos con un enfoque personalizado para cada paciente.', 'psicologia.jpg', 9),
('Nutrición', 'fas fa-apple-alt', 'Asesoramiento nutricional personalizado para mejorar la salud y calidad de vida mediante la alimentación.', 'El servicio de Nutrición de nuestra clínica ofrece asesoramiento profesional para mejorar la salud a través de la alimentación. Nuestros nutricionistas desarrollan planes alimenticios personalizados considerando las necesidades específicas, condiciones médicas y objetivos de cada paciente. Brindamos educación nutricional y seguimiento para garantizar resultados óptimos.', 'nutricion.jpg', 10);

-- Insertar servicios para cada especialidad
-- Cardiología
INSERT INTO web_especialidades_servicios (web_especialidad_id, servicio) VALUES
(1, 'Electrocardiograma (ECG)'),
(1, 'Ecocardiograma'),
(1, 'Prueba de esfuerzo'),
(1, 'Monitoreo Holter 24 horas'),
(1, 'Cateterismo cardíaco'),
(1, 'Rehabilitación cardíaca');

-- Neurología
INSERT INTO web_especialidades_servicios (web_especialidad_id, servicio) VALUES
(2, 'Electroencefalograma (EEG)'),
(2, 'Estudios de conducción nerviosa'),
(2, 'Electromiografía (EMG)'),
(2, 'Punción lumbar'),
(2, 'Tratamiento de cefaleas y migrañas'),
(2, 'Manejo de enfermedades neurodegenerativas');

-- Pediatría
INSERT INTO web_especialidades_servicios (web_especialidad_id, servicio) VALUES
(3, 'Control de niño sano'),
(3, 'Vacunación completa'),
(3, 'Evaluación del desarrollo'),
(3, 'Atención de enfermedades comunes'),
(3, 'Nutrición pediátrica'),
(3, 'Psicología infantil');

-- Odontología
INSERT INTO web_especialidades_servicios (web_especialidad_id, servicio) VALUES
(4, 'Odontología preventiva'),
(4, 'Limpiezas dentales'),
(4, 'Restauraciones (empastes)'),
(4, 'Endodoncia (tratamiento de conducto)'),
(4, 'Prótesis dentales'),
(4, 'Ortodoncia'),
(4, 'Blanqueamiento dental');

-- Traumatología
INSERT INTO web_especialidades_servicios (web_especialidad_id, servicio) VALUES
(5, 'Tratamiento de fracturas'),
(5, 'Artroscopias'),
(5, 'Reemplazos articulares'),
(5, 'Cirugía de columna'),
(5, 'Rehabilitación tras lesiones'),
(5, 'Medicina deportiva');

-- Ginecología
INSERT INTO web_especialidades_servicios (web_especialidad_id, servicio) VALUES
(6, 'Exámenes ginecológicos preventivos'),
(6, 'Papanicolaou'),
(6, 'Colposcopía'),
(6, 'Ultrasonido pélvico y transvaginal'),
(6, 'Control prenatal'),
(6, 'Planificación familiar'),
(6, 'Tratamiento de problemas hormonales');

-- Dermatología
INSERT INTO web_especialidades_servicios (web_especialidad_id, servicio) VALUES
(7, 'Consulta dermatológica general'),
(7, 'Biopsias de piel'),
(7, 'Crioterapia'),
(7, 'Tratamiento de acné'),
(7, 'Detección de cáncer de piel'),
(7, 'Tratamientos para caída del cabello'),
(7, 'Dermatología estética');

-- Oftalmología
INSERT INTO web_especialidades_servicios (web_especialidad_id, servicio) VALUES
(8, 'Examen ocular completo'),
(8, 'Medición de la presión ocular'),
(8, 'Evaluación de la retina'),
(8, 'Cirugía de cataratas'),
(8, 'Tratamiento de glaucoma'),
(8, 'Corrección de problemas refractivos'),
(8, 'Tratamiento de ojo seco');

-- Psicología
INSERT INTO web_especialidades_servicios (web_especialidad_id, servicio) VALUES
(9, 'Evaluación psicológica'),
(9, 'Terapia individual'),
(9, 'Terapia de pareja'),
(9, 'Terapia familiar'),
(9, 'Tratamiento para ansiedad y depresión'),
(9, 'Manejo del estrés'),
(9, 'Terapia para niños y adolescentes');

-- Nutrición
INSERT INTO web_especialidades_servicios (web_especialidad_id, servicio) VALUES
(10, 'Evaluación nutricional completa'),
(10, 'Planes de alimentación personalizados'),
(10, 'Control de peso'),
(10, 'Nutrición deportiva'),
(10, 'Nutrición para embarazo y lactancia'),
(10, 'Manejo nutricional de enfermedades crónicas'),
(10, 'Reeducación alimentaria');

-- Procedimiento almacenado para relacionar las tablas web_especialidades con especialidades
-- Esto es opcional, pero puede ser útil si quieres vincular ambas tablas
DELIMITER //
CREATE PROCEDURE VincularEspecialidadesWeb()
BEGIN
    -- Actualiza la referencia en la tabla web_especialidades basándose en el nombre similar
    UPDATE web_especialidades we
    JOIN especialidades e ON LOWER(we.nombre) LIKE CONCAT('%', LOWER(e.especialidad), '%')
    SET we.idespecialidad = e.idespecialidad
    WHERE we.idespecialidad IS NULL;
END //
DELIMITER ;