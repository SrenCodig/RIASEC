-- Roles
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

-- Usuarios
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    id_rol INT NOT NULL DEFAULT 2,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
);

-- Preguntas (categoría directamente como ENUM fijo)
CREATE TABLE preguntas (
    id_pregunta INT AUTO_INCREMENT PRIMARY KEY,
    texto TEXT NOT NULL,
    categoria ENUM('R','I','A','S','E','C') NOT NULL
);

-- Opciones (escala global 0-5)
CREATE TABLE opciones (
    id_opcion INT AUTO_INCREMENT PRIMARY KEY,
    valor TINYINT NOT NULL CHECK (valor BETWEEN 0 AND 5),
    descripcion VARCHAR(100)
);

-- Resultados (una fila por usuario, columnas fijas RIASEC)
CREATE TABLE resultados (
    id_resultado INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    puntaje_R INT NOT NULL DEFAULT 0,
    puntaje_I INT NOT NULL DEFAULT 0,
    puntaje_A INT NOT NULL DEFAULT 0,
    puntaje_S INT NOT NULL DEFAULT 0,
    puntaje_E INT NOT NULL DEFAULT 0,
    puntaje_C INT NOT NULL DEFAULT 0,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    UNIQUE KEY uq_usuario_resultado (id_usuario)
);

-- Bitácora
CREATE TABLE bitacora (
    id_bitacora INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    accion VARCHAR(255) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- Datos iniciales
INSERT INTO roles (nombre) VALUES ('Administrador'), ('Usuario');

INSERT INTO opciones (valor, descripcion) VALUES
(0,'Nada de acuerdo'),
(1,'Poco de acuerdo'),
(2,'Algo de acuerdo'),
(3,'Neutral'),
(4,'De acuerdo'),
(5,'Totalmente de acuerdo');