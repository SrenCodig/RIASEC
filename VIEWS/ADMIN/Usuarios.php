<!--
 * Gestión de Usuarios - Panel de administración
 * Este archivo permite a los administradores gestionar usuarios del sistema.

 * Funcionalidades principales:
 * - Seguridad: Solo accesible para usuarios con rol de administrador (id_rol == 1).
 * - Eliminar usuario: Permite eliminar usuarios, excepto a sí mismo.
 * - Editar usuario: Permite editar nombre y correo de un usuario.
 * - Filtro de búsqueda: Permite buscar usuarios por nombre o correo.
 * - Listado: Muestra una tabla con todos los usuarios y acciones disponibles.
 *
 * Variables principales:
 * - $msg: Mensaje de estado para mostrar al usuario.
 * - $editando: Indica si se está editando un usuario.
 * - $usuarioEdit: Datos del usuario en edición.
 * - $busqueda: Texto de búsqueda para filtrar usuarios.
 * - $usuarios: Lista de usuarios obtenida de la base de datos.
 *
 * Requiere:
 * - Funciones de CRUD definidas en '../../PHP/crud.php':
 *   - obtenerUsuarios()
 *   - eliminarUsuario($id)
 *   - actualizarUsuario($id, $nombre, $correo, $contrasena, $rol)
 *
 * HTML:
 * - Formulario de búsqueda.
 * - Formulario de edición de usuario.
 * - Tabla de usuarios con acciones (editar, eliminar, cambiar rol).
 * - Botón para volver al panel de opciones administrativas.
 *
 * Seguridad:
 * - Verifica sesión y rol antes de permitir acceso.
 * - Previene que el administrador se elimine a sí mismo.
 *
 * Uso:
 * - Acceder como administrador para gestionar usuarios.
-->

<!-- PARTE PHP -->

<?php
//Medida de segurdad de Panel de gestión de usuarios (solo admin)
require_once '../../PHP/crud.php';
session_start();
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
	die('<h2>Acceso denegado. Solo administradores.</h2>');
}
// OPCIONES DE ADMINISTRACIÓN DE USUARIOS
$msg = '';
// Eliminar usuario
if (isset($_GET['eliminar'])) {
	$id = (int)$_GET['eliminar'];
	if ($id == $_SESSION['id_usuario']) {
		$msg = 'No puedes eliminarte a ti mismo.';
	} else if (eliminarUsuario($id)) {
		$msg = 'Usuario eliminado correctamente.';
	} else {
		$msg = 'Error al eliminar usuario.';
	}
}
// Cambiar rol
if (isset($_GET['cambiar_rol'])) {
	$id = (int)$_GET['cambiar_rol'];
	$usuarios = obtenerUsuarios();
	foreach ($usuarios as $u) {
		if ($u['id_usuario'] == $id) {
			$nuevo_rol = $u['id_rol'] == 1 ? 2 : 1;
			actualizarUsuario($u['id_usuario'], $u['nombre'], $u['correo'], $u['contrasena'], $nuevo_rol);
			$msg = 'Rol actualizado.';
			break;
		}
	}
}
// Editar usuario (solo nombre y correo, no contraseña ni rol aquí)
$editando = false;
$usuarioEdit = null;
if (isset($_GET['editar'])) {
	$id_edit = (int)$_GET['editar'];
	$usuarios = obtenerUsuarios();
	foreach ($usuarios as $u) {
		if ($u['id_usuario'] == $id_edit) {
			$editando = true;
			$usuarioEdit = $u;
			break;
		}
	}
}
// Guardar cambios de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_edit'])) {
	$id = (int)$_POST['id_edit'];
	$nombre = trim($_POST['nombre']);
	$correo = trim($_POST['correo']);
	$usuarios = obtenerUsuarios();
	$contrasena = ''; // Mantener la contraseña actual
	foreach ($usuarios as $u) {
		if ($u['id_usuario'] == $id) {
			$contrasena = $u['contrasena'];
			$rol = $u['id_rol'];
			break;
		}
	}
	// Mensaje de respuesta
	if ($contrasena && actualizarUsuario($id, $nombre, $correo, $contrasena, $rol)) {
		$msg = 'Usuario actualizado correctamente.';
	} else {
		$msg = 'Error al actualizar usuario.';
	}
	$editando = false;
}

// Filtro de búsqueda
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$usuarios = obtenerUsuarios();
if ($busqueda !== '') {
	$usuarios = array_filter($usuarios, function($u) use ($busqueda) {
		$b = mb_strtolower($busqueda);
		return mb_strpos(mb_strtolower($u['nombre']), $b) !== false || mb_strpos(mb_strtolower($u['correo']), $b) !== false;
	});
}
?>

<!-- PARTE HTML -->

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Gestión de Usuarios</title>
</head>
<body>

	<div id="user-menu" style="text-align:right;margin:1em;"></div>
	<main>
		<h1>Gestión de Usuarios</h1>
		<?php if ($msg): ?><p><strong><?= htmlspecialchars($msg) ?></strong></p><?php endif; ?>

		<!-- Barra de búsqueda -->
		<form method="get" action="Usuarios.php" style="margin-bottom:1em;">
			<input type="text" name="busqueda" placeholder="Buscar por nombre o correo" value="<?= htmlspecialchars($busqueda) ?>" style="width:220px;">
			<button type="submit">Buscar</button>
			<?php if ($busqueda !== ''): ?>
				<a href="Usuarios.php">Limpiar</a>
			<?php endif; ?>
		</form>

		<!-- Formulario de edición -->
		<?php if ($editando && $usuarioEdit): ?>
		<form method="post" style="margin-bottom:2em;">
			<fieldset>
				<legend>Editar usuario</legend>
				<input type="hidden" name="id_edit" value="<?= $usuarioEdit['id_usuario'] ?>">
				<label>Nombre:<br><input type="text" name="nombre" required value="<?= htmlspecialchars($usuarioEdit['nombre']) ?>"></label><br>
				<label>Correo:<br><input type="email" name="correo" required value="<?= htmlspecialchars($usuarioEdit['correo']) ?>"></label><br>
				<button type="submit">Guardar</button>
				<a href="Usuarios.php">Cancelar</a>
			</fieldset>
		</form>
		<?php endif; ?>

		<!-- Tabla de usuarios: muestra la lista de usuarios y sus acciones administrativas -->
		<table border="1" aria-label="Usuarios">
			<thead>
				<tr>
					<!-- Encabezados de la tabla -->
					<th>ID</th>
					<th>Nombre</th>
					<th>Correo</th>
					<th>Rol</th>
					<th>Acciones</th>
				</tr>
			</thead>
			<tbody>
				<!-- Si no hay usuarios, mostrar mensaje -->
				<?php if (empty($usuarios)): ?>
					<tr>
						<td colspan="5">No se encontraron usuarios.</td>
					</tr>
				<?php else: ?>
					<!-- Iterar sobre cada usuario y mostrar sus datos -->
					<?php foreach ($usuarios as $u): ?>
						<tr>
							<!-- Partes del Usuario -->
							<td><?= htmlspecialchars($u['id_usuario']) ?></td>
							<td><?= htmlspecialchars($u['nombre']) ?></td>
							<td><?= htmlspecialchars($u['correo']) ?></td>
							<td><?= $u['id_rol'] == 1 ? 'Admin' : 'Usuario' ?></td>

							<!-- Acciones disponibles para el usuario -->
							<td>
								<!-- Enlace para editar usuario -->
								<a href="Usuarios.php?editar=<?= $u['id_usuario'] ?>">Editar</a>
								<?php if ($u['id_usuario'] != $_SESSION['id_usuario']): ?>
									<!-- Separador de acciones -->
									|
									<!-- Enlace para eliminar usuario -->
									<a href="Usuarios.php?eliminar=<?= $u['id_usuario'] ?>" onclick="return confirm('¿Eliminar usuario?');">Eliminar</a>
									|
									<!-- Enlace para cambiar el rol del usuario -->
									<a href="Usuarios.php?cambiar_rol=<?= $u['id_usuario'] ?>" onclick="return confirm('¿Cambiar rol de este usuario?');">Cambiar rol</a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<!-- Botón para volver al panel de opciones administrativas -->
		<form action="Opciones.php" method="get" style="margin-top:2em;">
			<button type="submit">Volver a Opciones</button>
		</form>
	</main>
	<script src="/RIASEC/JAVASCRIPT/login.js"></script>
</body>
</html>
