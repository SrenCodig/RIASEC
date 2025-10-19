<?php
// Archivo: PHP/Funciones/UsuariosF.php
// Lógica para la vista de administración `VIEWS/ADMIN/Usuarios.php`

require_once __DIR__ . '/OpcionesF.php';
// Validación centralizada: exigir admin
validar_sesion_usuario(true);

// Mensaje informativo mostrado en la vista y flags de edición
$msg = '';
$editando = false;
$usuarioEdit = null;

// Cache local para evitar múltiples llamadas repetidas a obtenerUsuarios()
$usuariosCache = null;
function cargarUsuarios() {
    global $usuariosCache;
    if ($usuariosCache === null) $usuariosCache = obtenerUsuarios();
    return $usuariosCache;
}

// Acción: eliminar usuario por GET
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    // Evitar que el administrador se elimine a sí mismo
    if ($id == $_SESSION['id_usuario']) {
        $msg = 'No puedes eliminarte a ti mismo.';
    } else {
        if (eliminarUsuario($id)) {
            $msg = 'Usuario eliminado correctamente.';
            // invalidar cache para que refleje el cambio
            $usuariosCache = null;
        } else {
            $msg = 'Error al eliminar usuario.';
        }
    }
}

// Acción: cambiar rol (promover/demover) vía GET
if (isset($_GET['cambiar_rol'])) {
    $id = (int)$_GET['cambiar_rol'];
    $usuarios = cargarUsuarios();
    foreach ($usuarios as $u) {
        if ($u['id_usuario'] == $id) {
            // Alternar rol entre 1 y 2
            $nuevo_rol = $u['id_rol'] == 1 ? 2 : 1;
            if (actualizarUsuario($u['id_usuario'], $u['nombre'], $u['correo'], $u['contrasena'], $nuevo_rol)) {
                $msg = 'Rol actualizado.';
                $usuariosCache = null; // invalidar cache local
            } else {
                $msg = 'Error al actualizar rol.';
            }
            break;
        }
    }
}

// Acción: preparar edición (mostrar formulario de edición)
if (isset($_GET['editar'])) {
    $id_edit = (int)$_GET['editar'];
    $usuarios = cargarUsuarios();
    foreach ($usuarios as $u) {
        if ($u['id_usuario'] == $id_edit) {
            $editando = true;
            $usuarioEdit = $u;
            break;
        }
    }
}

// Guardar cambios enviados por POST desde el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_edit'])) {
    $id = (int)$_POST['id_edit'];
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $usuarios = cargarUsuarios();
    $contrasena = '';
    $rol = 2;
    // Recuperar la contraseña y rol actuales para no sobrescribirlos si el formulario no los envía
    foreach ($usuarios as $u) {
        if ($u['id_usuario'] == $id) {
            $contrasena = $u['contrasena'];
            $rol = $u['id_rol'];
            break;
        }
    }
    // Actualizar usuario usando la función existente
    if ($contrasena && actualizarUsuario($id, $nombre, $correo, $contrasena, $rol)) {
        $msg = 'Usuario actualizado correctamente.';
        $usuariosCache = null;
    } else {
        $msg = 'Error al actualizar usuario.';
    }
    $editando = false; // salir del modo edición
}

// Búsqueda y paginación para la lista de usuarios
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$usuarios = cargarUsuarios();
if ($busqueda !== '') {
    $b = mb_strtolower($busqueda);
    // Filtrar por nombre o correo que contengan la cadena de búsqueda (case-insensitive)
    $usuarios = array_filter($usuarios, function ($u) use ($b) {
        return mb_strpos(mb_strtolower($u['nombre']), $b) !== false || mb_strpos(mb_strtolower($u['correo']), $b) !== false;
    });
}

// Preparar entrada para paginación (convertir a array indexado)
$usuariosArray = array_values($usuarios);
$totalUsuarios = count($usuariosArray);
$usuariosPorPagina = 25;
$paginaActual = isset($_GET['pag']) ? max(1, (int)$_GET['pag']) : 1;
$inicio = ($paginaActual - 1) * $usuariosPorPagina;
$usuariosPagina = array_slice($usuariosArray, $inicio, $usuariosPorPagina);
$totalPaginas = $usuariosPorPagina > 0 ? ceil($totalUsuarios / $usuariosPorPagina) : 0;

// Variables exportadas para la vista
// $msg, $editando, $usuarioEdit, $busqueda, $usuariosPagina, $totalPaginas, $paginaActual

?>
