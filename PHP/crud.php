
<?php
require_once __DIR__ . '/config.php';

function conectar() {
	global $host, $user, $pass, $db;
	$conn = new mysqli($host, $user, $pass, $db);
	if ($conn->connect_error) {
		error_log('Error de conexión: ' . $conn->connect_error);
		throw new Exception('No se pudo conectar a la base de datos.');
	}
	$conn->set_charset('utf8');
	return $conn;
}

// ------------------- CRUD ROLES -------------------

/**
 * Crea un nuevo rol.
 * @param string $nombre Nombre del rol
 * @return bool TRUE si se creó correctamente, FALSE si hubo error
 */
function crearRol($nombre) {
	$conn = conectar();
	$stmt = $conn->prepare('INSERT INTO roles (nombre) VALUES (?)');
	$stmt->bind_param('s', $nombre);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

/**
 * Obtiene todos los roles.
 * @return array Lista de roles
 */
function obtenerRoles() {
	$conn = conectar();
	$res = $conn->query('SELECT * FROM roles');
	$roles = $res->fetch_all(MYSQLI_ASSOC);
	$res->close(); $conn->close();
	return $roles;
}

/**
 * Actualiza un rol existente.
 * @param int $id ID del rol
 * @param string $nombre Nuevo nombre
 * @return bool TRUE si se actualizó, FALSE si hubo error
 */
function actualizarRol($id, $nombre) {
	$conn = conectar();
	$stmt = $conn->prepare('UPDATE roles SET nombre=? WHERE id_rol=?');
	$stmt->bind_param('si', $nombre, $id);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

/**
 * Elimina un rol por ID.
 * @param int $id ID del rol
 * @return bool TRUE si se eliminó, FALSE si hubo error
 */
function eliminarRol($id) {
	$conn = conectar();
	$stmt = $conn->prepare('DELETE FROM roles WHERE id_rol=?');
	$stmt->bind_param('i', $id);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
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
 * Crea una nueva opción de respuesta.
 * @param int $valor
 * @param string $descripcion
 * @return bool TRUE si se creó, FALSE si hubo error
 */
function crearOpcion($valor, $descripcion) {
	$conn = conectar();
	$stmt = $conn->prepare('INSERT INTO opciones (valor, descripcion) VALUES (?, ?)');
	$stmt->bind_param('is', $valor, $descripcion);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

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

/**
 * Actualiza una opción existente.
 * @param int $id
 * @param int $valor
 * @param string $descripcion
 * @return bool TRUE si se actualizó, FALSE si hubo error
 */
function actualizarOpcion($id, $valor, $descripcion) {
	$conn = conectar();
	$stmt = $conn->prepare('UPDATE opciones SET valor=?, descripcion=? WHERE id_opcion=?');
	$stmt->bind_param('isi', $valor, $descripcion, $id);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

/**
 * Elimina una opción por ID.
 * @param int $id
 * @return bool TRUE si se eliminó, FALSE si hubo error
 */
function eliminarOpcion($id) {
	$conn = conectar();
	$stmt = $conn->prepare('DELETE FROM opciones WHERE id_opcion=?');
	$stmt->bind_param('i', $id);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
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

/**
 * Actualiza un resultado existente.
 * @param int $id
 * @param int $r
 * @param int $i
 * @param int $a
 * @param int $s
 * @param int $e
 * @param int $c
 * @return bool TRUE si se actualizó, FALSE si hubo error
 */
function actualizarResultado($id, $r, $i, $a, $s, $e, $c) {
	$conn = conectar();
	$stmt = $conn->prepare('UPDATE resultados SET puntaje_R=?, puntaje_I=?, puntaje_A=?, puntaje_S=?, puntaje_E=?, puntaje_C=? WHERE id_resultado=?');
	$stmt->bind_param('iiiiiii', $r, $i, $a, $s, $e, $c, $id);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

/**
 * Elimina un resultado por ID.
 * @param int $id
 * @return bool TRUE si se eliminó, FALSE si hubo error
 */
function eliminarResultado($id) {
	$conn = conectar();
	$stmt = $conn->prepare('DELETE FROM resultados WHERE id_resultado=?');
	$stmt->bind_param('i', $id);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}


// ------------------- CRUD BITACORA -------------------
/**
 * Crea un registro en la bitácora.
 * @param int|null $id_usuario
 * @param string $accion
 * @return bool TRUE si se creó, FALSE si hubo error
 */
function crearBitacora($id_usuario, $accion) {
	$conn = conectar();
	if ($id_usuario === null) {
		$stmt = $conn->prepare('INSERT INTO bitacora (id_usuario, accion) VALUES (NULL, ?)');
		$stmt->bind_param('s', $accion);
	} else {
		$stmt = $conn->prepare('INSERT INTO bitacora (id_usuario, accion) VALUES (?, ?)');
		$stmt->bind_param('is', $id_usuario, $accion);
	}
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

/**
 * Obtiene todos los registros de la bitácora.
 * @return array Lista de registros
 */
function obtenerBitacora() {
	$conn = conectar();
	$res = $conn->query('SELECT * FROM bitacora');
	$bitacora = $res->fetch_all(MYSQLI_ASSOC);
	$res->close(); $conn->close();
	return $bitacora;
}

/**
 * Elimina un registro de la bitácora por ID.
 * @param int $id
 * @return bool TRUE si se eliminó, FALSE si hubo error
 */
function eliminarBitacora($id) {
	$conn = conectar();
	$stmt = $conn->prepare('DELETE FROM bitacora WHERE id_bitacora=?');
	$stmt->bind_param('i', $id);
	$res = $stmt->execute();
	$stmt->close(); $conn->close();
	return $res;
}

?>
