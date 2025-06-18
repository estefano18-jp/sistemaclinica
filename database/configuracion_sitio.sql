-- Ejecuta este script en tu base de datos MySQL/MariaDB
USE clinicaDB;
CREATE TABLE IF NOT EXISTS `configuracion_sitio` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `clave` VARCHAR(100) NOT NULL UNIQUE,
    `valor` TEXT,
    `tipo` ENUM('texto', 'imagen', 'url', 'email', 'telefono') DEFAULT 'texto',
    `descripcion` VARCHAR(255),
    `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar valores por defecto
INSERT IGNORE INTO `configuracion_sitio` (`clave`, `valor`, `tipo`, `descripcion`) VALUES
('logo_principal', 'iconoClinica.jpg', 'imagen', 'Logo principal del sitio'),
('nombre_empresa', 'Clínica Médica', 'texto', 'Nombre de la empresa'),
('direccion', 'Av. Principal 123, Ciudad', 'texto', 'Dirección de la clínica'),
('telefono', '+12 345 6789', 'telefono', 'Teléfono principal'),
('email', 'contacto@clinica.com', 'email', 'Email de contacto'),
('horario_semana', 'Lunes - Viernes: 08:00 - 18:00', 'texto', 'Horario de lunes a viernes'),
('horario_sabado', 'Sábado: 09:00 - 14:00', 'texto', 'Horario de sábado'),
('horario_domingo', 'Domingo y feriados: Cerrado', 'texto', 'Horario de domingo'),
('facebook_url', 'https://facebook.com/clinica', 'url', 'URL de Facebook'),
('twitter_url', 'https://twitter.com/clinica', 'url', 'URL de Twitter'),
('instagram_url', 'https://instagram.com/clinica', 'url', 'URL de Instagram'),
('youtube_url', 'https://youtube.com/clinica', 'url', 'URL de YouTube'),
('whatsapp_numero', '123456789', 'telefono', 'Número de WhatsApp'),
('descripcion_empresa', 'Somos una clínica comprometida con su salud y bienestar. Contamos con los mejores especialistas médicos y la tecnología más avanzada para brindarle una atención personalizada y de calidad.', 'texto', 'Descripción de la empresa');