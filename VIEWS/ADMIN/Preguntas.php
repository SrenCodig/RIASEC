<?php
// Preguntas.php - Gestión avanzada de preguntas RIASEC
require_once '../../PHP/crud.php';
session_start();

// Seguridad: solo administradores
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header('Location: /RIASEC/index.php');
    exit;
}

// --- Variables de estado ---
$msg = '';
$categorias = ['R' => 'Realista', 'I' => 'Investigador', 'A' => 'Artístico', 'S' => 'Social', 'E' => 'Emprendedor', 'C' => 'Convencional'];
$catActual = isset($_GET['categoria']) && isset($categorias[$_GET['categoria']]) ? $_GET['categoria'] : 'R';
$todasPreguntas = array_filter(obtenerPreguntas(), fn($p) => $p['categoria'] === $catActual);
$totalPreguntas = count($todasPreguntas);
$preguntasPorPagina = 10;
$paginaActual = isset($_GET['pag']) ? max(1, (int)$_GET['pag']) : 1;
$inicio = ($paginaActual - 1) * $preguntasPorPagina;
$preguntas = array_slice($todasPreguntas, $inicio, $preguntasPorPagina);

// --- Acciones: eliminar, editar, agregar ---
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    eliminarPregunta($id);
    $msg = 'Pregunta eliminada correctamente.';
    header('Location: Preguntas.php?categoria=' . $catActual . '&msg=' . urlencode($msg));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Agregar pregunta
    if (isset($_POST['agregar_pregunta'])) {
        $texto = trim($_POST['texto_nueva'] ?? '');
        $categoria = $_POST['categoria_nueva'] ?? '';
        if ($texto && isset($categorias[$categoria])) {
            crearPregunta($texto, $categoria);
            $msg = 'Pregunta agregada correctamente.';
            header('Location: Preguntas.php?categoria=' . $categoria . '&msg=' . urlencode($msg));
            exit;
        } else {
            $msg = 'Error: Debe ingresar texto y categoría válida.';
        }
    }
    // Editar pregunta
    if (isset($_POST['editar_pregunta'])) {
        $id = (int)($_POST['id_editar'] ?? 0);
        $texto = trim($_POST['texto_editar'] ?? '');
        $categoria = $_POST['categoria_editar'] ?? '';
        if ($id && $texto && isset($categorias[$categoria])) {
            actualizarPregunta($id, $texto, $categoria);
            $msg = 'Pregunta editada correctamente.';
            header('Location: Preguntas.php?categoria=' . $categoria . '&msg=' . urlencode($msg));
            exit;
        } else {
            $msg = 'Error: Debe ingresar texto y categoría válida.';
        }
    }
}
// --- Edición individual ---
$editando = false;
$preguntaEdit = null;
if (isset($_GET['editar'])) {
    $idEdit = (int)$_GET['editar'];
    foreach ($preguntas as $p) {
        if ($p['id_pregunta'] == $idEdit) {
            $editando = true;
            $preguntaEdit = $p;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes">
    <title>Gestión de Preguntas RIASEC</title>
    <link rel="stylesheet" href="/RIASEC/STYLE/Base.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Emergente.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Formulario.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/DarkMode.css">
</head>
<body>
    <!-- Botón modo oscuro/claro adaptado de DarkMode -->
    <div class="dark-mode-switch" id="darkModeSwitch">
        <div class="circle">
            <span class="sun"><svg width="32" height="32" viewBox="0 0 32 32"><circle cx="16" cy="16" r="10" fill="#FCDE5B"/><g stroke="#FCDE5B" stroke-width="2"><line x1="16" y1="2" x2="16" y2="8"/><line x1="16" y1="24" x2="16" y2="30"/><line x1="2" y1="16" x2="8" y2="16"/><line x1="24" y1="16" x2="30" y2="16"/><line x1="6.34" y1="6.34" x2="10.49" y2="10.49"/><line x1="21.51" y1="21.51" x2="25.66" y2="25.66"/><line x1="6.34" y1="25.66" x2="10.49" y2="21.51"/><line x1="21.51" y1="10.49" x2="25.66" y2="6.34"/></g></svg></span>
            <span class="moon"><svg width="32" height="32" viewBox="0 0 32 32"><path d="M22 16a10 10 0 1 1-10-10c0 5.52 4.48 10 10 10z" fill="#fff"/></svg></span>
        </div>
    </div>
    <nav id="user-menu" class="user-menu-top"></nav>
    <main>
        <h1>Gestión de Preguntas RIASEC</h1>
        <?php if ($msg): ?>
            <div class="msg-status"> <?= htmlspecialchars($msg) ?> </div>
        <?php endif; ?>
        <div class="paginacion-categorias">
            <?php foreach ($categorias as $key => $nombre): ?>
                <button type="button" onclick="window.location='?categoria=<?= $key ?>'" class="btn-cat<?= $catActual == $key ? ' active' : '' ?>"> <?= $nombre ?> </button>
            <?php endforeach; ?>
        </div>
        <section>
            <h2>Preguntas de la categoría: <span class="categoria-seleccionada"> <?= $categorias[$catActual] ?> </span></h2>
            <table>
                <thead>
                    <tr>
                        <th>Texto</th>
                        <th>Categoría</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($preguntas as $p): ?>
                        <tr>
                            <td style="font-size:1.35em;font-weight:700;letter-spacing:.5px;"><?= htmlspecialchars($p['texto']) ?></td>
                            <td style="font-size:1.25em;font-weight:600;"><?= $categorias[$p['categoria']] ?></td>
                            <td style="text-align:center;">
                                <div style="display:flex;gap:.5em;justify-content:center;align-items:center;">
                                    <form method="get" style="display:inline;">
                                        <input type="hidden" name="categoria" value="<?= $catActual ?>">
                                        <input type="hidden" name="editar" value="<?= $p['id_pregunta'] ?>">
                                        <button type="submit" class="btn-accion" title="Editar" style="min-width:40px;max-width:40px;padding:.5em .5em;display:flex;align-items:center;justify-content:center;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M4 20h4.586a2 2 0 0 0 1.414-.586l9-9a2 2 0 0 0 0-2.828l-2.586-2.586a2 2 0 0 0-2.828 0l-9 9A2 2 0 0 0 4 15.414V20z"/><path stroke="currentColor" stroke-width="2" d="M14.5 7.5l2 2"/></svg>
                                        </button>
                                    </form>
                                    <form method="get" style="display:inline;" onsubmit="return confirm('¿Seguro que desea eliminar esta pregunta?');">
                                        <input type="hidden" name="categoria" value="<?= $catActual ?>">
                                        <input type="hidden" name="eliminar" value="<?= $p['id_pregunta'] ?>">
                                        <button type="submit" class="btn-accion" title="Eliminar" style="min-width:40px;max-width:40px;padding:.5em .5em;display:flex;align-items:center;justify-content:center;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M6 7h12M9 7V5a3 3 0 0 1 6 0v2m-9 0v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
            $totalPaginas = ceil($totalPreguntas / $preguntasPorPagina);
            if ($totalPaginas > 1): ?>
            <div style="display:flex;justify-content:center;gap:.5em;margin:2em 0;">
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <a href="?categoria=<?= $catActual ?>&pag=<?= $i ?>" class="btn-accion" style="min-width:36px;max-width:36px;padding:.4em .4em;<?= $paginaActual == $i ? 'background:#0a2342;color:#fff;' : '' ?>"> <?= $i ?> </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            <form action="Opciones.php" method="get" style="margin-top:2em; text-align:center;">
                <button type="submit" class="btn-accion">&#8592; Volver a opciones</button>
            </form>
        </section>
        <section>
            <h2>Agregar nueva pregunta</h2>
            <form method="post">
                <select name="categoria_nueva" class="select-categoria" required>
                    <option value="">Seleccione categoría</option>
                    <?php foreach ($categorias as $key => $nombre): ?>
                        <option value="<?= $key ?>"<?= $catActual == $key ? ' selected' : '' ?>><?= $nombre ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="texto_nueva" class="input-pregunta" placeholder="Texto de la pregunta" required>
                <button type="submit" name="agregar_pregunta" class="btn-accion">Agregar pregunta</button>
            </form>
        </section>
        <br>
        <?php if ($editando && $preguntaEdit): ?>
        <section>
            <hr>
            <h2>Editar pregunta</h2>
            <form method="post">
                <input type="hidden" name="id_editar" value="<?= $preguntaEdit['id_pregunta'] ?>">
                <select name="categoria_editar" class="select-categoria" required>
                    <?php foreach ($categorias as $key => $nombre): ?>
                        <option value="<?= $key ?>"<?= $preguntaEdit['categoria'] == $key ? ' selected' : '' ?>><?= $nombre ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="texto_editar" class="input-pregunta" value="<?= htmlspecialchars($preguntaEdit['texto']) ?>" required>
                <button type="submit" name="editar_pregunta" class="btn-accion">Guardar cambios</button>
            </form>
        </section>
        <?php endif; ?>
    </main>
    <script src="/RIASEC/JAVASCRIPT/Recursos.js"></script>
</body>
</html>
