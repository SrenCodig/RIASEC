<!--
	Archivo de utilidad para operaciones CRUD contra la base de datos.
	Aquí se definen funciones reutilizables para roles, usuarios, preguntas, opciones, resultados y carreras.
-->

<?php
// Archivo de utilidad para operaciones CRUD contra la base de datos.
// Aquí se definen funciones reutilizables para roles, usuarios, preguntas, opciones, resultados y carreras.

require_once __DIR__ . '/config.php';

/**
 * Establece y devuelve una conexión MySQLi usando las credenciales definidas en `config.php`.
 * Lanza una excepción si la conexión falla.
 *
 * @throws Exception Si no se puede conectar a la base de datos.
 * @return mysqli Conexión abierta (charset utf8 ya aplicado).
 */
function conectar() {
	global $host, $user, $pass, $db;
	// Crear instancia de MySQLi
	$conn = new mysqli($host, $user, $pass, $db);
	// Si hay error en la conexión, registrar y lanzar excepción para que el llamador maneje
	if ($conn->connect_error) {
		error_log('Error de conexión: ' . $conn->connect_error);
		throw new Exception('No se pudo conectar a la base de datos.');
	}
	// Asegurar codificación UTF-8 para evitar problemas con acentos
	$conn->set_charset('utf8');
	return $conn;
}

// ------------------- CRUD USUARIOS -------------------

/**
 * Crea un nuevo usuario.
 * @param string $nombre
 * @param string $correo
 * @param string $contrasena (sin hashear, se hashea dentro)
 * @param int $id_rol (opcional, por defecto 2)
 * @return bool TRUE si se creó, FALSE si hubo error
 */
function crearUsuario($nombre, $correo, $contrasena, $id_rol = 2) {
	$conn = conectar();
	// Hash seguro de contraseña
	$hash = password_hash($contrasena, PASSWORD_DEFAULT);
	$stmt = $conn->prepare('INSERT INTO usuarios (nombre, correo, contrasena, id_rol) VALUES (?, ?, ?, ?)');
	$stmt->bind_param('sssi', $nombre, $correo, $hash, $id_rol);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

/**
 * Obtiene todos los usuarios.
 * @return array Lista de usuarios
 */
function obtenerUsuarios() {
	$conn = conectar();
	$res = $conn->query('SELECT * FROM usuarios');
	$usuarios = $res->fetch_all(MYSQLI_ASSOC);
	$res->close(); $conn->close();
	return $usuarios;
}


/**
 * Actualiza un usuario existente.
 * @param int $id
 * @param string $nombre
 * @param string $correo
 * @param string $contrasena (sin hashear, se hashea dentro)
 * @param int $id_rol
 * @return bool TRUE si se actualizó, FALSE si hubo error
 */
function actualizarUsuario($id, $nombre, $correo, $contrasena, $id_rol) {
	$conn = conectar();
	// Hash seguro de contraseña
	$hash = password_hash($contrasena, PASSWORD_DEFAULT);
	$stmt = $conn->prepare('UPDATE usuarios SET nombre=?, correo=?, contrasena=?, id_rol=? WHERE id_usuario=?');
	$stmt->bind_param('sssii', $nombre, $correo, $hash, $id_rol, $id);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

/**
 * Elimina un usuario por ID.
 * @param int $id
 * @return bool TRUE si se eliminó, FALSE si hubo error
 */
function eliminarUsuario($id) {
	$conn = conectar();
	$stmt = $conn->prepare('DELETE FROM usuarios WHERE id_usuario=?');
	$stmt->bind_param('i', $id);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

// ------------------- CRUD PREGUNTAS -------------------
/**
 * Crea una nueva pregunta.
 * @param string $texto
 * @param string $categoria (R, I, A, S, E, C)
 * @return bool TRUE si se creó, FALSE si hubo error
 */
function crearPregunta($texto, $categoria) {
	$conn = conectar();
	$stmt = $conn->prepare('INSERT INTO preguntas (texto, categoria) VALUES (?, ?)');
	$stmt->bind_param('ss', $texto, $categoria);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

/**
 * Obtiene todas las preguntas.
 * @return array Lista de preguntas
 */
function obtenerPreguntas() {
	$conn = conectar();
	$res = $conn->query('SELECT * FROM preguntas');
	$preguntas = $res->fetch_all(MYSQLI_ASSOC);
	$res->close(); $conn->close();
	return $preguntas;
}

/**
 * Actualiza una pregunta existente.
 * @param int $id
 * @param string $texto
 * @param string $categoria
 * @return bool TRUE si se actualizó, FALSE si hubo error
 */
function actualizarPregunta($id, $texto, $categoria) {
	$conn = conectar();
	$stmt = $conn->prepare('UPDATE preguntas SET texto=?, categoria=? WHERE id_pregunta=?');
	$stmt->bind_param('ssi', $texto, $categoria, $id);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

/**
 * Elimina una pregunta por ID.
 * @param int $id
 * @return bool TRUE si se eliminó, FALSE si hubo error
 */
function eliminarPregunta($id) {
	$conn = conectar();
	$stmt = $conn->prepare('DELETE FROM preguntas WHERE id_pregunta=?');
	$stmt->bind_param('i', $id);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

 // ------------------- CRUD OPCIONES -------------------

/**
 * Obtiene todas las opciones de respuesta.
 * @return array Lista de opciones
 */
function obtenerOpciones() {
	$conn = conectar();
	$res = $conn->query('SELECT * FROM opciones');
	$opciones = $res->fetch_all(MYSQLI_ASSOC);
	$res->close(); $conn->close();
	return $opciones;
}

// ------------------- CRUD RESULTADOS -------------------
/**
 * Crea un resultado de usuario.
 * @param int $id_usuario
 * @param int $r
 * @param int $i
 * @param int $a
 * @param int $s
 * @param int $e
 * @param int $c
 * @return bool TRUE si se creó, FALSE si hubo error
 */
function crearResultado($id_usuario, $r, $i, $a, $s, $e, $c) {
	$conn = conectar();
	$stmt = $conn->prepare('INSERT INTO resultados (id_usuario, puntaje_R, puntaje_I, puntaje_A, puntaje_S, puntaje_E, puntaje_C) VALUES (?, ?, ?, ?, ?, ?, ?)');
	$stmt->bind_param('iiiiiii', $id_usuario, $r, $i, $a, $s, $e, $c);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

/**
 * Obtiene todos los resultados.
 * @return array Lista de resultados
 */
function obtenerResultados() {
	$conn = conectar();
	$res = $conn->query('SELECT * FROM resultados');
	$resultados = $res->fetch_all(MYSQLI_ASSOC);
	$res->close(); $conn->close();
	return $resultados;
}

// ------------------- CRUD CARRERAS -------------------
/**
 * Crea una nueva carrera.
 * @param string $nombre
 * @param int|null $porcentajeR
 * @param int|null $porcentajeI
 * @param int|null $porcentajeA
 * @param int|null $porcentajeS
 * @param int|null $porcentajeE
 * @param int|null $porcentajeC
 * @param string $descripcion
 * @return bool TRUE si se creó, FALSE si hubo error
 */
function crearCarrera($nombre, $porcentajeR, $porcentajeI, $porcentajeA, $porcentajeS, $porcentajeE, $porcentajeC, $descripcion) {
	$conn = conectar();
	$stmt = $conn->prepare('INSERT INTO carreras (nombre, porcentaje_R, porcentaje_I, porcentaje_A, porcentaje_S, porcentaje_E, porcentaje_C, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
	$stmt->bind_param('siiiiiis', $nombre, $porcentajeR, $porcentajeI, $porcentajeA, $porcentajeS, $porcentajeE, $porcentajeC, $descripcion);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

/**
 * Obtiene todas las carreras.
 * @return array Lista de carreras
 */
function obtenerCarreras() {
	$conn = conectar();
	$res = $conn->query('SELECT * FROM carreras');
	$carreras = $res->fetch_all(MYSQLI_ASSOC);
	$res->close(); $conn->close();
	return $carreras;
}

/**
 * Actualiza una carrera existente.
 * @param int $id
 * @param string $nombre
 * @param int|null $r
 * @param int|null $i
 * @param int|null $a
 * @param int|null $s
 * @param int|null $e
 * @param int|null $c
 * @param string $descripcion
 * @return bool TRUE si se actualizó, FALSE si hubo error
 */
function actualizarCarrera($nombre, $r, $i, $a, $s, $e, $c, $descripcion, $id) {
	$conn = conectar();
	$stmt = $conn->prepare('UPDATE carreras SET nombre=?, porcentaje_R=?, porcentaje_I=?, porcentaje_A=?, porcentaje_S=?, porcentaje_E=?, porcentaje_C=?, descripcion=? WHERE id_carrera=?');
	$stmt->bind_param('siiiiiisi', $nombre, $r, $i, $a, $s, $e, $c, $descripcion, $id);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

/**
 * Elimina una carrera por ID.
 * @param int $id
 * @return bool TRUE si se eliminó, FALSE si hubo error
 */
function eliminarCarrera($id) {
	$conn = conectar();
	$stmt = $conn->prepare('DELETE FROM carreras WHERE id_carrera=?');
	$stmt->bind_param('i', $id);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

?>