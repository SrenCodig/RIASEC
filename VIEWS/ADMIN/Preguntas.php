<?php require_once __DIR__ . '/../../PHP/Funciones/PreguntasF.php'; ?>
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
                                        <input type="hidden" name="pag" value="<?= $paginaActual ?>">
                                        <button type="submit" class="btn-accion" title="Editar" style="min-width:40px;max-width:40px;padding:.5em .5em;display:flex;align-items:center;justify-content:center;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M4 20h4.586a2 2 0 0 0 1.414-.586l9-9a2 2 0 0 0 0-2.828l-2.586-2.586a2 2 0 0 0-2.828 0l-9 9A2 2 0 0 0 4 15.414V20z"/><path stroke="currentColor" stroke-width="2" d="M14.5 7.5l2 2"/></svg>
                                        </button>
                                    </form>
                                    <form method="get" style="display:inline;" onsubmit="return confirm('¿Seguro que desea eliminar esta pregunta?');">
                                        <input type="hidden" name="categoria" value="<?= $catActual ?>">
                                        <input type="hidden" name="eliminar" value="<?= $p['id_pregunta'] ?>">
                                        <input type="hidden" name="pag" value="<?= $paginaActual ?>">
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
                <?php
                $maxBotones = 3;
                $inicio = max(1, $paginaActual - 1);
                $fin = min($totalPaginas, $inicio + $maxBotones - 1);
                if ($totalPaginas > 10) {
                    // [<<] [<]
                    if ($paginaActual > 1) {
                        echo '<form method="get" style="display:inline;"><input type="hidden" name="categoria" value="' . $catActual . '"><input type="hidden" name="pag" value="1"><button type="submit" class="btn-accion" title="Primera página">&lt;&lt;</button></form> ';
                        echo '<form method="get" style="display:inline;"><input type="hidden" name="categoria" value="' . $catActual . '"><input type="hidden" name="pag" value="' . ($paginaActual - 1) . '"><button type="submit" class="btn-accion" title="Anterior">&lt;</button></form> ';
                    }
                    // [n] [n+1] [n+2]
                    for ($i = $inicio; $i <= $fin; $i++) {
                        if ($i == $paginaActual) {
                            echo '<button class="btn-accion" style="background:#0a2342;color:#fff;" disabled>' . $i . '</button> ';
                        } else {
                            echo '<form method="get" style="display:inline;"><input type="hidden" name="categoria" value="' . $catActual . '"><input type="hidden" name="pag" value="' . $i . '"><button type="submit" class="btn-accion">' . $i . '</button></form> ';
                        }
                    }
                    // [>] [>>]
                    if ($paginaActual < $totalPaginas) {
                        echo '<form method="get" style="display:inline;"><input type="hidden" name="categoria" value="' . $catActual . '"><input type="hidden" name="pag" value="' . ($paginaActual + 1) . '"><button type="submit" class="btn-accion" title="Siguiente">&gt;</button></form> ';
                        echo '<form method="get" style="display:inline;"><input type="hidden" name="categoria" value="' . $catActual . '"><input type="hidden" name="pag" value="' . $totalPaginas . '"><button type="submit" class="btn-accion" title="Última página">&gt;&gt;</button></form> ';
                    }
                } else {
                    // Paginación simple
                    for ($i = 1; $i <= $totalPaginas; $i++) {
                        if ($i == $paginaActual) {
                            echo '<button class="btn-accion" style="background:#0a2342;color:#fff;" disabled>' . $i . '</button> ';
                        } else {
                            echo '<form method="get" style="display:inline;"><input type="hidden" name="categoria" value="' . $catActual . '"><input type="hidden" name="pag" value="' . $i . '"><button type="submit" class="btn-accion">' . $i . '</button></form> ';
                        }
                    }
                }
                ?>
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
