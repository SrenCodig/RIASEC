
<?php
// Usuarios.php - Panel de gestión de usuarios (solo admin)
require_once '../../PHP/crud.php';
session_start();
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
	die('<h2>Acceso denegado. Solo administradores.</h2>');
}

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_edit'])) {
	$id = (int)$_POST['id_edit'];
	$nombre = trim($_POST['nombre']);
	$correo = trim($_POST['correo']);
	$usuarios = obtenerUsuarios();
	$contrasena = '';
	foreach ($usuarios as $u) {
		if ($u['id_usuario'] == $id) {
			$contrasena = $u['contrasena'];
			$rol = $u['id_rol'];
			break;
		}
	}
	if ($contrasena && actualizarUsuario($id, $nombre, $correo, $contrasena, $rol)) {
		$msg = 'Usuario actualizado correctamente.';
	} else {
		$msg = 'Error al actualizar usuario.';
	}
	$editando = false;
}
$usuarios = obtenerUsuarios();
?>
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

		<table border="1" aria-label="Usuarios">
			<thead>
				<tr>
					<th>ID</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Acciones</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($usuarios as $u): ?>
				<tr>
					<td><?= htmlspecialchars($u['id_usuario']) ?></td>
					<td><?= htmlspecialchars($u['nombre']) ?></td>
					<td><?= htmlspecialchars($u['correo']) ?></td>
					<td><?= $u['id_rol'] == 1 ? 'Admin' : 'Usuario' ?></td>
					<td>
						<a href="Usuarios.php?editar=<?= $u['id_usuario'] ?>">Editar</a>
						<?php if ($u['id_usuario'] != $_SESSION['id_usuario']): ?> |
						<a href="Usuarios.php?eliminar=<?= $u['id_usuario'] ?>" onclick="return confirm('¿Eliminar usuario?');">Eliminar</a> |
						<a href="Usuarios.php?cambiar_rol=<?= $u['id_usuario'] ?>" onclick="return confirm('¿Cambiar rol de este usuario?');">Cambiar rol</a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<form action="Opciones.php" method="get" style="margin-top:2em;">
			<button type="submit">Volver a Opciones</button>
		</form>
	</main>
	<script src="/RIASEC/JAVASCRIPT/login.js"></script>
</body>
</html>
