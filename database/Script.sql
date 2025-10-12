-- Tabla: roles
CREATE TABLE roles (
    id_rol INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL UNIQUE
);


-- Tabla: usuarios
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    id_rol INT NOT NULL DEFAULT 2,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
);


-- Tabla: preguntas
CREATE TABLE preguntas (
    id_pregunta INT PRIMARY KEY AUTO_INCREMENT,
    texto TEXT NOT NULL,
    categoria ENUM('R','I','A','S','E','C') NOT NULL
);


-- Tabla: opciones
CREATE TABLE opciones (
    id_opcion INT PRIMARY KEY AUTO_INCREMENT,
    valor TINYINT NOT NULL CHECK (valor BETWEEN 0 AND 5),
    descripcion VARCHAR(100)
);


-- Tabla: resultados
CREATE TABLE resultados (
    id_resultado INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT,
    puntaje_R INT NOT NULL DEFAULT 0,
    puntaje_I INT NOT NULL DEFAULT 0,
    puntaje_A INT NOT NULL DEFAULT 0,
    puntaje_S INT NOT NULL DEFAULT 0,
    puntaje_E INT NOT NULL DEFAULT 0,
    puntaje_C INT NOT NULL DEFAULT 0,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- Tabla: carreras
CREATE TABLE `carreras` (
	`id_carrera` INT(11) NOT NULL AUTO_INCREMENT,
	`nombre` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`porcentaje_R` TINYINT(4) NULL DEFAULT NULL,
	`porcentaje_I` TINYINT(4) NULL DEFAULT NULL,
	`porcentaje_A` TINYINT(4) NULL DEFAULT NULL,
	`porcentaje_S` TINYINT(4) NULL DEFAULT NULL,
	`porcentaje_E` TINYINT(4) NULL DEFAULT NULL,
	`porcentaje_C` TINYINT(4) NULL DEFAULT NULL,
	`descripcion` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	PRIMARY KEY (`id_carrera`) USING BTREE
)
COLLATE='utf8mb4_uca1400_ai_ci'
ENGINE=InnoDB
AUTO_INCREMENT=101
;

-- Datos iniciales
INSERT INTO roles (nombre) VALUES ('Administrador'), ('Usuario');

INSERT INTO opciones (valor, descripcion) VALUES
(0,'Nada de acuerdo'),
(1,'Poco de acuerdo'),
(2,'Algo de acuerdo'),
(3,'Neutral'),
(4,'De acuerdo'),
(5,'Totalmente de acuerdo');