USE clinicaDB;

CREATE TABLE promociones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    imagen VARCHAR(255) NOT NULL,
    precio_regular DECIMAL(10,2) NOT NULL,
    precio_oferta DECIMAL(10,2) NOT NULL,
    porcentaje_descuento INT,
    texto_boton VARCHAR(100) DEFAULT 'Reservar Cita',
    enlace_boton VARCHAR(255) DEFAULT 'reservar.php',
    estado TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar algunas promociones de ejemplo
INSERT INTO promociones (titulo, descripcion, imagen, precio_regular, precio_oferta, porcentaje_descuento) VALUES 
('Chequeo Médico Completo', 'Incluye análisis de sangre completo, electrocardiograma, evaluación de signos vitales, consulta con médico general y revisión de resultados. Ideal para mantener un control preventivo de su salud.', 'promocion-checkup.jpg', 350.00, 262.50, 25),
('Limpieza Dental Profesional', 'Incluye evaluación, eliminación de placa y sarro, pulido dental y aplicación de flúor. Una limpieza profesional para mantener sus dientes sanos y su sonrisa radiante.', 'promocion-dental.jpg', 180.00, 126.00, 30);