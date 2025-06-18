USE clinicaDB;

CREATE TABLE carrusel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    imagen VARCHAR(255) NOT NULL,
    descripcion TEXT,
    titulo VARCHAR(255),
    texto VARCHAR(255),
    boton_texto VARCHAR(100),
    boton_enlace VARCHAR(100)
);
SELECT * FROM carrusel;
SELECT * FROM carrusel ORDER BY id DESC LIMIT 3;
SELECT * FROM carrusel;

INSERT INTO carrusel (imagen, descripcion, titulo, texto, boton_texto, boton_enlace) VALUES
('imagenCarousel01.jpg', 'Primera imagen de prueba', 'Atención médica de primera calidad', 'Nuestros especialistas están comprometidos con su bienestar y salud', 'Nuestros Servicios', '#servicios'),
('imagenCarousel02.jpg', 'Segunda imagen de prueba', 'Tecnología médica de vanguardia', 'Equipamiento moderno para diagnósticos precisos y tratamientos efectivos', 'Conoce nuestra tecnología', '#tecnologia'),
('imagenCarousel03.jpg', 'Tercera imagen de prueba', 'Profesionales altamente cualificados', 'Nuestro equipo médico cuenta con amplia experiencia y formación especializada', 'Conoce a nuestros especialistas', '#equipo');

