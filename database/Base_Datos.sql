-- Creación de la base de datos
CREATE DATABASE IF NOT EXISTS clinicaDB;
USE clinicaDB;

-- Tabla especialidades
CREATE TABLE especialidades (
    idespecialidad 		INT AUTO_INCREMENT PRIMARY KEY,
    especialidad 		VARCHAR(100) NOT NULL,
    precioatencion 		DECIMAL(10,2) NOT NULL
);

-- Tabla diagnosticos
CREATE TABLE diagnosticos (
    iddiagnostico 		INT AUTO_INCREMENT PRIMARY KEY,
    nombre 				VARCHAR(200) NOT NULL,
    descripcion 		TEXT,
    codigo 				VARCHAR(50) NOT NULL
);

-- Tabla alergias
CREATE TABLE alergias (
    idalergia 			INT AUTO_INCREMENT PRIMARY KEY,
    tipoalergia 		VARCHAR(100) NOT NULL,
    alergia 			VARCHAR(200) NOT NULL
);

-- Tabla tiposervicio
CREATE TABLE tiposervicio (
    idtiposervicio 		INT AUTO_INCREMENT PRIMARY KEY,
    tiposervicio 		VARCHAR(100) NOT NULL,
    servicio 			VARCHAR(200) NOT NULL,
    precioservicio 		DECIMAL(10,2) NOT NULL
);

-- Tabla empresas
CREATE TABLE empresas (
    idempresa           INT AUTO_INCREMENT PRIMARY KEY,
    razonsocial         VARCHAR(200) NOT NULL,
    ruc                 VARCHAR(11) NOT NULL,
    direccion           VARCHAR(250) NOT NULL,
    nombrecomercial     VARCHAR(200),
    telefono            VARCHAR(20) NOT NULL,
    email               VARCHAR(100) NOT NULL,
    CONSTRAINT uk_ruc_empresa UNIQUE (ruc)
);

-- Tabla personas (con modificaciones incorporadas)
CREATE TABLE personas (
    idpersona 			INT AUTO_INCREMENT PRIMARY KEY,
    apellidos 			VARCHAR(100) NOT NULL,
    nombres 			VARCHAR(100) NOT NULL,
    tipodoc 			ENUM('DNI', 'PASAPORTE', 'CARNET DE EXTRANJERIA', 'OTRO') NOT NULL,
    nrodoc 				VARCHAR(20) NOT NULL,
    telefono 			VARCHAR(20) NULL DEFAULT NULL,
    fechanacimiento 	DATE NULL DEFAULT NULL,
    genero 				ENUM('M', 'F', 'OTRO') NULL DEFAULT NULL,
    direccion 			VARCHAR(250) NULL DEFAULT NULL,
    email 				VARCHAR(100) NULL DEFAULT NULL,
    CONSTRAINT uk_nrodoc_persona UNIQUE (nrodoc)
);

-- Tabla pacientes
CREATE TABLE pacientes (
    idpaciente 			INT AUTO_INCREMENT PRIMARY KEY,
    idpersona 			INT NOT NULL,
    fecharegistro 		DATE NOT NULL,
    FOREIGN KEY (idpersona) REFERENCES personas(idpersona)
);

-- Tabla colaboradores (con columna estado incorporada)
CREATE TABLE colaboradores (
    idcolaborador 		INT AUTO_INCREMENT PRIMARY KEY,
    idpersona 			INT NOT NULL,
    idespecialidad 		INT,
    estado              ENUM('ACTIVO', 'INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    FOREIGN KEY (idpersona) REFERENCES personas(idpersona),
    FOREIGN KEY (idespecialidad) REFERENCES especialidades(idespecialidad)
);

-- Tabla contratos (con columna estado incorporada)
CREATE TABLE contratos (
    idcontrato 			INT AUTO_INCREMENT PRIMARY KEY,
    idcolaborador 		INT NOT NULL,
    fechainicio 		DATE NOT NULL,
    fechafin 			DATE,
    tipocontrato 		VARCHAR(100) NOT NULL,
    estado              ENUM('ACTIVO', 'INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    FOREIGN KEY (idcolaborador) REFERENCES colaboradores(idcolaborador)
);

-- Tabla usuarios (con campo rol incorporado)
CREATE TABLE usuarios (
    idusuario 			INT AUTO_INCREMENT PRIMARY KEY,
    idcontrato 			INT,
    idpaciente          INT,
    nomuser 			VARCHAR(50) NOT NULL,
    passuser 			VARCHAR(255) NOT NULL,
    estado 				BOOLEAN DEFAULT TRUE,
    rol                 ENUM('ADMINISTRADOR', 'DOCTOR', 'ENFERMERO', 'PACIENTE') NOT NULL,
    FOREIGN KEY (idcontrato) REFERENCES contratos(idcontrato),
    FOREIGN KEY (idpaciente) REFERENCES pacientes(idpaciente),
    CONSTRAINT uk_nomuser_usuario UNIQUE (nomuser)
);

-- Tabla atenciones
CREATE TABLE atenciones (
    idatencion 			INT AUTO_INCREMENT PRIMARY KEY,
    idcontrato 			INT NOT NULL,
    diasemana 			ENUM('LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO') NOT NULL,
    FOREIGN KEY (idcontrato) REFERENCES contratos(idcontrato)
);

-- Tabla horarios
CREATE TABLE horarios (
    idhorario 			INT AUTO_INCREMENT PRIMARY KEY,
    idatencion 			INT NOT NULL,
    horainicio 			TIME NOT NULL,
    horafin 			TIME NOT NULL,
    FOREIGN KEY (idatencion) REFERENCES atenciones(idatencion)
);

-- Tabla clientes
CREATE TABLE clientes (
    idcliente 			INT AUTO_INCREMENT PRIMARY KEY,
    tipocliente 		ENUM('NATURAL', 'EMPRESA') NOT NULL,
    idempresa 			INT,
    idpersona 			INT,
    FOREIGN KEY (idempresa) REFERENCES empresas(idempresa),
    FOREIGN KEY (idpersona) REFERENCES personas(idpersona)
);

-- Tabla citas
CREATE TABLE citas (
    idcita 				INT AUTO_INCREMENT PRIMARY KEY,
    fecha 				DATE NOT NULL,
    hora 				TIME NOT NULL,
    estado 				ENUM('PROGRAMADA', 'CANCELADA', 'REALIZADA', 'NO ASISTIO') NOT NULL,
    observaciones 		TEXT,
    idpersona 			INT NOT NULL,
    FOREIGN KEY (idpersona) REFERENCES personas(idpersona)
);

-- Tabla consultas
CREATE TABLE consultas (
    idconsulta 			INT AUTO_INCREMENT PRIMARY KEY,
    fecha 				DATE NOT NULL,
    idhorario 			INT NOT NULL,
    horaprogramada 		TIME NOT NULL,
    horaatencion 		TIME,
    idpaciente 			INT NOT NULL,
    condicionpaciente 	VARCHAR(100),
    iddiagnostico 		INT,
    FOREIGN KEY (idhorario) REFERENCES horarios(idhorario),
    FOREIGN KEY (idpaciente) REFERENCES pacientes(idpaciente),
    FOREIGN KEY (iddiagnostico) REFERENCES diagnosticos(iddiagnostico)
);

-- Tabla tratamiento
CREATE TABLE tratamiento (
    idtratamiento 		INT AUTO_INCREMENT PRIMARY KEY,
    medicacion 			VARCHAR(200) NOT NULL,
    dosis 				VARCHAR(100) NOT NULL,
    frecuencia 			VARCHAR(100) NOT NULL,
    duracion 			VARCHAR(100) NOT NULL,
    idreceta 			INT NULL
);

-- Tabla historiaclinica
CREATE TABLE historiaclinica (
    idhistoriaclinica 	INT AUTO_INCREMENT PRIMARY KEY,
    enfermedadactual 	TEXT,
    examenfisico 		TEXT,
    evolucion 			TEXT,
    altamedica 			BOOLEAN DEFAULT FALSE,
    iddiagnostico 		INT,
    idtratamiento 		INT,
    FOREIGN KEY (iddiagnostico) REFERENCES diagnosticos(iddiagnostico),
    FOREIGN KEY (idtratamiento) REFERENCES tratamiento(idtratamiento)
);

-- Tabla recetas
CREATE TABLE recetas (
    idreceta 			INT AUTO_INCREMENT PRIMARY KEY,
    idconsulta 			INT NOT NULL,
    medicacion 			VARCHAR(255) NOT NULL,
    cantidad 			VARCHAR(50) NOT NULL,
    frecuencia 			VARCHAR(100) NOT NULL,
    FOREIGN KEY (idconsulta) REFERENCES consultas(idconsulta)
);

-- Actualizar la tabla tratamiento para agregar la clave foránea
ALTER TABLE tratamiento
ADD CONSTRAINT fk_tratamiento_receta
FOREIGN KEY (idreceta) REFERENCES recetas(idreceta);

-- Tabla triajes
CREATE TABLE triajes (
    idtriaje 			INT AUTO_INCREMENT PRIMARY KEY,
    idconsulta 			INT NOT NULL,
    idenfermera 		INT NOT NULL,
    hora 				TIME NOT NULL,
    temperatura 		DECIMAL(5,2),
    presionarterial 	VARCHAR(20),
    frecuenciacardiaca INT,
    saturacionoxigeno 	INT,
    peso 				DECIMAL(5,2),
    estatura 			DECIMAL(5,2),
    FOREIGN KEY (idconsulta) REFERENCES consultas(idconsulta),
    FOREIGN KEY (idenfermera) REFERENCES colaboradores(idcolaborador)
);

-- Tabla listaalergias
CREATE TABLE listaalergias (
    idlistaalergia 		INT AUTO_INCREMENT PRIMARY KEY,
    idpersona 			INT NOT NULL,
    idalergia 			INT NOT NULL,
    gravedad 			ENUM('LEVE', 'MODERADA', 'GRAVE') NOT NULL,
    FOREIGN KEY (idpersona) REFERENCES personas(idpersona),
    FOREIGN KEY (idalergia) REFERENCES alergias(idalergia)
);

-- Tabla serviciosrequeridos
CREATE TABLE serviciosrequeridos (
    idserviciorequerido INT AUTO_INCREMENT PRIMARY KEY,
    idconsulta 			INT NOT NULL,
    idtiposervicio 		INT NOT NULL,
    solicitud 			TEXT,
    fechaanalisis 		DATE,
    fechaprocesamiento 	DATE,
    fechaentrega 		DATE,
    FOREIGN KEY (idconsulta) REFERENCES consultas(idconsulta),
    FOREIGN KEY (idtiposervicio) REFERENCES tiposervicio(idtiposervicio)
);

-- Tabla resultados
CREATE TABLE resultados (
    idresultado 		INT AUTO_INCREMENT PRIMARY KEY,
    idserviciorequerido INT NOT NULL,
    caracteristicaevaluada VARCHAR(200) NOT NULL,
    condicion 			TEXT,
    rutaimagen 			VARCHAR(255),
    FOREIGN KEY (idserviciorequerido) REFERENCES serviciosrequeridos(idserviciorequerido)
);

-- Tabla ventas (con montopagado y tipopago ampliado incorporados)
CREATE TABLE ventas (
    idventa 			INT AUTO_INCREMENT PRIMARY KEY,
    idcliente 			INT NOT NULL,
    tipodoc 			ENUM('BOLETA', 'FACTURA') NOT NULL,
    nrodocumento 		VARCHAR(20) NOT NULL,
    fechaemision 		DATETIME NOT NULL,
    fecharegistro 		DATETIME NOT NULL,
    tipopago 			ENUM('EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'YAPE', 'PLIN') NOT NULL,
    idusuariocaja 		INT NOT NULL,
    montopagado         DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (idcliente) REFERENCES clientes(idcliente),
    FOREIGN KEY (idusuariocaja) REFERENCES usuarios(idusuario)
);

-- Tabla detalleventas
CREATE TABLE detalleventas (
    iddetalleventa 		INT AUTO_INCREMENT PRIMARY KEY,
    idventa 			INT NOT NULL,
    idconsulta 			INT,
    idserviciorequerido INT,
    precio 				DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (idventa) REFERENCES ventas(idventa),
    FOREIGN KEY (idconsulta) REFERENCES consultas(idconsulta),
    FOREIGN KEY (idserviciorequerido) REFERENCES serviciosrequeridos(idserviciorequerido)
);

-- NUEVA TABLA
-- Crear tabla devoluciones
CREATE TABLE IF NOT EXISTS devoluciones (
    iddevolucion        INT AUTO_INCREMENT PRIMARY KEY,
    idcita              INT NOT NULL,
    idventa             INT,
    monto               DECIMAL(10,2) NOT NULL,
    motivo              VARCHAR(100) NOT NULL,
    observaciones       TEXT,
    metodo              ENUM('EFECTIVO', 'YAPE', 'PLIN') NOT NULL,
    fecha_devolucion    DATETIME NOT NULL,
    idusuario           INT NOT NULL,
    numero_comprobante  VARCHAR(50) NOT NULL,
    FOREIGN KEY (idcita) REFERENCES citas(idcita),
    FOREIGN KEY (idventa) REFERENCES ventas(idventa),
    FOREIGN KEY (idusuario) REFERENCES usuarios(idusuario)
);

-- Índices para mejorar rendimiento
CREATE INDEX idx_devoluciones_cita ON devoluciones(idcita);
CREATE INDEX idx_devoluciones_venta ON devoluciones(idventa);
CREATE INDEX idx_devoluciones_fecha ON devoluciones(fecha_devolucion);



-- Tabla arqueocaja
CREATE TABLE IF NOT EXISTS arqueocaja (
    idarqueo INT AUTO_INCREMENT PRIMARY KEY,
    fecha_apertura DATE NOT NULL,
    hora_apertura TIME NOT NULL,
    fecha_cierre DATE NULL,
    hora_cierre TIME NULL,
    monto_inicial DECIMAL(10,2) NOT NULL DEFAULT 0,
    saldo_anterior DECIMAL(10,2) NOT NULL DEFAULT 0,
    saldo_real DECIMAL(10,2) NULL,
    saldo_a_dejar DECIMAL(10,2) DEFAULT 0,
    diferencia DECIMAL(10,2) NULL,
    estado ENUM('ABIERTO', 'CERRADO') NOT NULL DEFAULT 'ABIERTO',
    observaciones TEXT NULL,
    idusuario INT NOT NULL,
    FOREIGN KEY (idusuario) REFERENCES usuarios(idusuario)
);

SELECT * FROM arqueocaja;
