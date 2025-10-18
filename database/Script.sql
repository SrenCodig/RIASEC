-- ==========================================================
-- Tabla: roles
-- Esta tabla contiene los roles del sistema (por ejemplo: Administrador, Usuario).
-- Cada rol tiene un identificador único y un nombre que debe ser único.
-- ==========================================================
CREATE TABLE roles (
    -- Identificador del rol, entero autoincremental
    id_rol INT PRIMARY KEY AUTO_INCREMENT,
    -- Nombre legible del rol (único para evitar duplicados)
    nombre VARCHAR(50) NOT NULL UNIQUE
);


-- ==========================================================
-- Tabla: usuarios
-- Contiene los usuarios del sistema. La contraseña se almacena
-- como hash (campo `contrasena`) y no debe guardarse en claro.
-- Se referencia al rol mediante la clave foránea `id_rol`.
-- ==========================================================
CREATE TABLE usuarios (
    -- PK autoincremental para cada usuario
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    -- Nombre visible del usuario (puede ser su alias)
    nombre VARCHAR(100) NOT NULL,
    -- Correo electrónico (único para identificar y recuperar cuentas)
    correo VARCHAR(150) NOT NULL UNIQUE,
    -- Hash de la contraseña (usar password_hash en la aplicación)
    contrasena VARCHAR(255) NOT NULL,
    -- Rol asociado (por defecto 2 = Usuario)
    id_rol INT NOT NULL DEFAULT 2,
    -- Fecha de creación/registro automática
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Restricción: la columna id_rol debe existir en roles
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
);


-- ==========================================================
-- Tabla: preguntas
-- Almacena las preguntas de la prueba RIASEC. Cada pregunta
-- pertenece a una categoría R/I/A/S/E/C.
-- ==========================================================
CREATE TABLE preguntas (
    -- Identificador de la pregunta
    id_pregunta INT PRIMARY KEY AUTO_INCREMENT,
    -- Texto completo de la pregunta
    texto TEXT NOT NULL,
    -- Categoría RIASEC: R, I, A, S, E o C
    categoria ENUM('R','I','A','S','E','C') NOT NULL
);


-- ==========================================================
-- Tabla: opciones
-- Define las opciones de respuesta (por ejemplo: Nada de acuerdo ... Totalmente de acuerdo)
-- El campo `valor` es numérico y se utilizará para sumar puntajes.
-- ==========================================================
CREATE TABLE opciones (
    -- Identificador de la opción (autoincremental)
    id_opcion INT PRIMARY KEY AUTO_INCREMENT,
    -- Valor numérico de la opción (rango 0..5)
    valor TINYINT NOT NULL CHECK (valor BETWEEN 0 AND 5),
    -- Descripción legible de la opción
    descripcion VARCHAR(100)
);


-- ==========================================================
-- Tabla: resultados
-- Guarda el resultado de una prueba RIASEC para un usuario (o anónimo si id_usuario es NULL).
-- Cada columna puntaje_* almacena la suma de valores obtenidos por categoría.
-- ==========================================================
CREATE TABLE resultados (
    -- PK del resultado
    id_resultado INT PRIMARY KEY AUTO_INCREMENT,
    -- FK al usuario (puede ser NULL si la prueba fue anónima)
    id_usuario INT,
    -- Puntajes acumulados por cada categoría (valores enteros)
    puntaje_R INT NOT NULL DEFAULT 0,
    puntaje_I INT NOT NULL DEFAULT 0,
    puntaje_A INT NOT NULL DEFAULT 0,
    puntaje_S INT NOT NULL DEFAULT 0,
    puntaje_E INT NOT NULL DEFAULT 0,
    puntaje_C INT NOT NULL DEFAULT 0,
    -- Fecha en la que se registró el resultado
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Relación con usuarios
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- ==========================================================
-- Tabla: carreras
-- Lista de carreras/profesiones con un perfil ideal expresado
-- como porcentajes por cada letra RIASEC. Estos porcentajes
-- sirven para calcular afinidades con el perfil del usuario.
-- ==========================================================
CREATE TABLE `carreras` (
    -- PK autoincremental de la carrera
    `id_carrera` INT(11) NOT NULL AUTO_INCREMENT,
    -- Nombre de la carrera (collation para soportar UTF-8 y comparaciones)
    `nombre` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
    -- Porcentajes ideales por letra (pueden ser NULL si no definidos)
    `porcentaje_R` TINYINT(4) NULL DEFAULT NULL,
    `porcentaje_I` TINYINT(4) NULL DEFAULT NULL,
    `porcentaje_A` TINYINT(4) NULL DEFAULT NULL,
    `porcentaje_S` TINYINT(4) NULL DEFAULT NULL,
    `porcentaje_E` TINYINT(4) NULL DEFAULT NULL,
    `porcentaje_C` TINYINT(4) NULL DEFAULT NULL,
    -- Descripción larga de la carrera
    `descripcion` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
    PRIMARY KEY (`id_carrera`) USING BTREE
)
COLLATE='utf8mb4_uca1400_ai_ci'
ENGINE=InnoDB
AUTO_INCREMENT=101
;

-- ==========================================================
-- Datos iniciales (semillas mínimas para el sistema)
-- Insertar roles base
-- ==========================================================
INSERT INTO roles (nombre) VALUES ('Administrador'), ('Usuario');

-- Insertar opciones de respuesta estándar (valores y etiquetas)
INSERT INTO opciones (valor, descripcion) VALUES
    (0,'Nada de acuerdo'),
    (1,'Poco de acuerdo'),
    (2,'Algo de acuerdo'),
    (3,'Neutral'),
    (4,'De acuerdo'),
    (5,'Totalmente de acuerdo');