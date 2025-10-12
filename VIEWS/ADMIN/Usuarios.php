<?php
/* ============================================================
PANEL DE GESTIÓN DE USUARIOS (solo administradores)
- Control de acceso, CRUD, búsqueda y paginación
============================================================ */

require_once '../../PHP/crud.php';
session_start();

// ==== SEGURIDAD ====
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    die('<h2>Acceso denegado. Solo administradores.</h2>');
}

// ==== VARIABLES ====
$msg = '';
$editando = false;
$usuarioEdit = null;

// ==== ELIMINAR USUARIO ====
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    if ($id == $_SESSION['id_usuario']) {
        $msg = 'No puedes eliminarte a ti mismo.';
    } elseif (eliminarUsuario($id)) {
        $msg = 'Usuario eliminado correctamente.';
    } else {
        $msg = 'Error al eliminar usuario.';
    }
}

// ==== CAMBIAR ROL ====
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

// ==== EDITAR USUARIO ====
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

// ==== GUARDAR CAMBIOS ====
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

// ==== BÚSQUEDA ====
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$usuarios = obtenerUsuarios();
if ($busqueda !== '') {
    $usuarios = array_filter($usuarios, function ($u) use ($busqueda) {
        $b = mb_strtolower($busqueda);
        return mb_strpos(mb_strtolower($u['nombre']), $b) !== false ||
            mb_strpos(mb_strtolower($u['correo']), $b) !== false;
    });
}

// ==== PAGINACIÓN ====
$usuariosArray = array_values($usuarios);
$totalUsuarios = count($usuariosArray);
$usuariosPorPagina = 25;
$paginaActual = isset($_GET['pag']) ? max(1, (int)$_GET['pag']) : 1;
$inicio = ($paginaActual - 1) * $usuariosPorPagina;
$usuariosPagina = array_slice($usuariosArray, $inicio, $usuariosPorPagina);
$totalPaginas = ceil($totalUsuarios / $usuariosPorPagina);
?>

<!-- ====================== PARTE HTML ====================== -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="/RIASEC/STYLE/Base.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Emergente.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Formulario.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/DarkMode.css">
</head>

<body>
    <!-- SWITCH MODO OSCURO -->
    <div class="dark-mode-switch" id="darkModeSwitch">
        <div class="circle">
            <span class="sun"><svg width="32" height="32" viewBox="0 0 32 32"><circle cx="16" cy="16" r="10" fill="#FCDE5B"/><g stroke="#FCDE5B" stroke-width="2"><line x1="16" y1="2" x2="16" y2="8"/><line x1="16" y1="24" x2="16" y2="30"/><line x1="2" y1="16" x2="8" y2="16"/><line x1="24" y1="16" x2="30" y2="16"/><line x1="6.34" y1="6.34" x2="10.49" y2="10.49"/><line x1="21.51" y1="21.51" x2="25.66" y2="25.66"/><line x1="6.34" y1="25.66" x2="10.49" y2="21.51"/><line x1="21.51" y1="10.49" x2="25.66" y2="6.34"/></g></svg></span>
            <span class="moon"><svg width="32" height="32" viewBox="0 0 32 32"><path d="M22 16a10 10 0 1 1-10-10c0 5.52 4.48 10 10 10z" fill="#fff"/></svg></span>
        </div>
    </div>

    <div id="user-menu" class="user-menu-top"></div>
    <main>
        <h1>Gestión de Usuarios</h1>

        <?php if ($msg): ?><p class="info"><strong><?= htmlspecialchars($msg) ?></strong></p><?php endif; ?>

        <!-- BÚSQUEDA Y RETORNO -->
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:2em;margin-bottom:1.5em;">
            <form method="get" action="Usuarios.php" style="display:flex;align-items:center;gap:1em;flex:1;min-width:320px;max-width:600px;">
                <input type="text" name="busqueda" class="pregunta-indicador" placeholder="Buscar por nombre o correo" value="<?= htmlspecialchars($busqueda) ?>">
                <button type="submit" class="btn-pag" title="Buscar" style="height:64px;width:85px;display:flex;align-items:center;justify-content:center;padding:0;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><line x1="17" y1="17" x2="23" y2="23" stroke="currentColor" stroke-width="2"/></svg>
                </button>
                <?php if ($busqueda !== ''): ?>
                    <button type="button" class="btn-pag" onclick="window.location='Usuarios.php'" title="Limpiar búsqueda" style="height:48px;width:48px;display:flex;align-items:center;justify-content:center;padding:0;">
                        <svg width="22" height="22" viewBox="0 0 22 22" fill="none"><line x1="6" y1="6" x2="16" y2="16" stroke="currentColor" stroke-width="2"/><line x1="16" y1="6" x2="6" y2="16" stroke="currentColor" stroke-width="2"/></svg>
                    </button>
                <?php endif; ?>
            </form>
            <form action="Opciones.php" method="get">
                <button type="submit" class="btn-retorno" title="Volver a Opciones" style="display:flex;align-items:center;">
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none" style="margin-right:.5em;"><polyline points="15,4 7,11 15,18" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                    Volver a Opciones
                </button>
            </form>
        </div>

        <!-- FORMULARIO DE EDICIÓN -->
        <?php if ($editando && $usuarioEdit): ?>
        <form method="post" style="max-width:1000px;margin:0 auto 2em;">
            <fieldset>
                <legend>Editar usuario</legend>
                <input type="hidden" name="id_edit" value="<?= $usuarioEdit['id_usuario'] ?>">
                <label>Nombre:<input type="text" name="nombre" required value="<?= htmlspecialchars($usuarioEdit['nombre']) ?>"></label>
                <label>Correo:<input type="email" name="correo" required value="<?= htmlspecialchars($usuarioEdit['correo']) ?>"></label>
                <div style="display:flex;gap:1em;justify-content:center;">
                    <button type="submit" class="btn-accion" title="Guardar cambios" style="display:flex;align-items:center;gap:.5em;">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" style="margin-right:.5em;vertical-align:middle;"><path d="M3 10l4 4 8-8" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                        <span style="vertical-align:middle;">Guardar</span>
                    </button>
                    <button type="button" class="btn-retorno" onclick="window.location='Usuarios.php'" title="Cancelar edición" style="display:flex;align-items:center;gap:.5em;">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" style="margin-right:.5em;vertical-align:middle;"><line x1="5" y1="5" x2="15" y2="15" stroke="currentColor" stroke-width="2"/><line x1="15" y1="5" x2="5" y2="15" stroke="currentColor" stroke-width="2"/></svg>
                        <span style="vertical-align:middle;">Cancelar</span>
                    </button>
                </div>
            </fieldset>
        </form>
        <?php endif; ?>

        <!-- TABLA DE USUARIOS -->
        <div class="tabla-responsive">
            <table class="tabla-historial" aria-label="Usuarios">
                <thead>
                    <tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($usuariosPagina)): ?>
                        <tr><td colspan="5">No se encontraron usuarios.</td></tr>
                    <?php else: foreach ($usuariosPagina as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['id_usuario']) ?></td>
                            <td><?= htmlspecialchars($u['nombre']) ?></td>
                            <td><?= htmlspecialchars($u['correo']) ?></td>
                            <td><?= $u['id_rol'] == 1 ? 'Admin' : 'Usuario' ?></td>
                            <td style="display:flex;gap:.5em;justify-content:center;">
                                <form method="get" action="Usuarios.php">
                                    <input type="hidden" name="editar" value="<?= $u['id_usuario'] ?>">
                                    <button type="submit" class="btn-accion" title="Editar usuario">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M4 13.5V16h2.5l7.1-7.1a1 1 0 0 0 0-1.4l-2.1-2.1a1 1 0 0 0-1.4 0L4 13.5z" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                                    </button>
                                </form>
                                <?php if ($u['id_usuario'] != $_SESSION['id_usuario']): ?>
                                <form method="get" action="Usuarios.php" onsubmit="return confirm('¿Eliminar usuario?');">
                                    <input type="hidden" name="eliminar" value="<?= $u['id_usuario'] ?>">
                                    <button type="submit" class="btn-accion" title="Eliminar usuario">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="5" y="7" width="10" height="8" rx="2" stroke="currentColor" stroke-width="2" fill="none"/><path d="M3 7h14" stroke="currentColor" stroke-width="2"/><path d="M8 7V5a2 2 0 0 1 4 0v2" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                                    </button>
                                </form>
                                <form method="get" action="Usuarios.php" onsubmit="return confirm('¿Cambiar rol de este usuario?');">
                                    <input type="hidden" name="cambiar_rol" value="<?= $u['id_usuario'] ?>">
                                    <button type="submit" class="btn-accion" title="Cambiar rol">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M7 7l-4 3.5L7 14" stroke="currentColor" stroke-width="2" fill="none"/><path d="M13 7l4 3.5L13 14" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <?php if ($totalPaginas > 1): ?>
            <div style="margin:2em 0;text-align:center;">
                <?php
                $maxBotones = 3;
                $inicio = max(1, $paginaActual - 1);
                $fin = min($totalPaginas, $inicio + $maxBotones - 1);
                if ($totalPaginas > 10) {
                    // [<<] [<]
                    if ($paginaActual > 1) {
                        echo "<a href='Usuarios.php?pag=1" . ($busqueda ? "&busqueda=" . urlencode($busqueda) : "") . "' class='btn-pag' title='Primera página'>&lt;&lt;</a> ";
                        echo "<a href='Usuarios.php?pag=" . ($paginaActual - 1) . ($busqueda ? "&busqueda=" . urlencode($busqueda) : "") . "' class='btn-pag' title='Anterior'>&lt;</a> ";
                    }
                    // [n] [n+1] [n+2]
                    for ($i = $inicio; $i <= $fin; $i++) {
                        if ($i == $paginaActual) {
                            echo "<a href='#' class='btn-pag' style='background:#0a2342;color:#fff;' disabled>$i</a> ";
                        } else {
                            echo "<a href='Usuarios.php?pag=$i" . ($busqueda ? "&busqueda=" . urlencode($busqueda) : "") . "' class='btn-pag'>$i</a> ";
                        }
                    }
                    // [>] [>>]
                    if ($paginaActual < $totalPaginas) {
                        echo "<a href='Usuarios.php?pag=" . ($paginaActual + 1) . ($busqueda ? "&busqueda=" . urlencode($busqueda) : "") . "' class='btn-pag' title='Siguiente'>&gt;</a> ";
                        echo "<a href='Usuarios.php?pag=$totalPaginas" . ($busqueda ? "&busqueda=" . urlencode($busqueda) : "") . "' class='btn-pag' title='Última página'>&gt;&gt;</a> ";
                    }
                } else {
                    // Paginación simple
                    for ($i = 1; $i <= $totalPaginas; $i++) {
                        if ($i == $paginaActual) {
                            echo "<a href='#' class='btn-pag' style='background:#0a2342;color:#fff;' disabled>$i</a> ";
                        } else {
                            echo "<a href='Usuarios.php?pag=$i" . ($busqueda ? "&busqueda=" . urlencode($busqueda) : "") . "' class='btn-pag'>$i</a> ";
                        }
                    }
                }
                ?>
            </div>
        <?php endif; ?>
    </main>

    <script src="/RIASEC/JAVASCRIPT/Recursos.js"></script>
</body>
</html>
