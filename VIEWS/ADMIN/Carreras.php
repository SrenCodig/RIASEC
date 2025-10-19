<!--
    Vista de gestión de carreras para administradores en RIASEC.
    Permite agregar, editar, eliminar y paginar carreras.
    Incluye formularios para agregar nuevas carreras y editar existentes.
-->

<?php require_once __DIR__ . '/../../PHP/Funciones/CarrerasF.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes">
    <title>Gestión de Carreras</title>
    <link rel="stylesheet" href="/RIASEC/STYLE/Base.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Emergente.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Formulario.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/DarkMode.css">
</head>
<body>
    <!-- === Switch de modo oscuro / claro === -->
    <div class="dark-mode-switch" id="darkModeSwitch">
        <div class="circle">
            <span class="sun"><svg width="32" height="32" viewBox="0 0 32 32"><circle cx="16" cy="16" r="10" fill="#FCDE5B"/><g stroke="#FCDE5B" stroke-width="2"><line x1="16" y1="2" x2="16" y2="8"/><line x1="16" y1="24" x2="16" y2="30"/><line x1="2" y1="16" x2="8" y2="16"/><line x1="24" y1="16" x2="30" y2="16"/><line x1="6.34" y1="6.34" x2="10.49" y2="10.49"/><line x1="21.51" y1="21.51" x2="25.66" y2="25.66"/><line x1="6.34" y1="25.66" x2="10.49" y2="21.51"/><line x1="21.51" y1="10.49" x2="25.66" y2="6.34"/></g></svg></span>
            <span class="moon"><svg width="32" height="32" viewBox="0 0 32 32"><path d="M22 16a10 10 0 1 1-10-10c0 5.52 4.48 10 10 10z" fill="#fff"/></svg></span>
        </div>
    </div>
        <!-- === MENÚ SUPERIOR DEL USUARIO === -->
    <nav id="user-menu" class="user-menu-top"></nav>
    <main>
        <h1>Gestión de Carreras</h1>
        <div style="margin-bottom:1.5em;"> <!-- Botón para volver a Opciones -->
            <form action="Opciones.php" method="get" style="display:inline;"> <!-- Formulario para volver a Opciones -->
                <button type="submit" class="opcion-btn" style="padding:.7em 2em;font-size:1.1em;">&larr; Volver a Opciones</button> <!-- Botón de volver -->
            </form>
        </div>
        <?php if ($msg): ?> <!-- Mostrar mensaje de estado si existe -->
            <div class="msg-status"> <?= htmlspecialchars($msg) ?> </div>
        <?php endif; ?>
        <section>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>R</th>
                        <th>I</th>
                        <th>A</th>
                        <th>S</th>
                        <th>E</th>
                        <th>C</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($carrerasPagina as $c): ?> <!-- Iterar sobre las carreras de la página actual -->
                        <tr>
                            <td><?= htmlspecialchars($c['nombre']) ?></td>
                            <td><?= htmlspecialchars($c['descripcion'] ?? '') ?></td>
                            <td><?= (int)$c['porcentaje_R'] ?></td>
                            <td><?= (int)$c['porcentaje_I'] ?></td>
                            <td><?= (int)$c['porcentaje_A'] ?></td>
                            <td><?= (int)$c['porcentaje_S'] ?></td>
                            <td><?= (int)$c['porcentaje_E'] ?></td>
                            <td><?= (int)$c['porcentaje_C'] ?></td>
                            <td style="text-align:center;">
                                <div style="display:flex;gap:.5em;justify-content:center;align-items:center;"> <!-- Botones de editar y eliminar -->
                                    <form method="get" style="display:inline;">
                                        <input type="hidden" name="editar" value="<?= $c['id_carrera'] ?>">
                                        <input type="hidden" name="pag" value="<?= $paginaActual ?>">
                                        <button type="submit" class="btn-accion" title="Editar" style="min-width:40px;max-width:40px;padding:.5em .5em;display:flex;align-items:center;justify-content:center;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M4 20h4.586a2 2 0 0 0 1.414-.586l9-9a2 2 0 0 0 0-2.828l-2.586-2.586a2 2 0 0 0-2.828 0l-9 9A2 2 0 0 0 4 15.414V20z"/><path stroke="currentColor" stroke-width="2" d="M14.5 7.5l2 2"/></svg>
                                        </button>
                                    </form>
                                    <form method="get" style="display:inline;" onsubmit="return confirm('¿Seguro que desea eliminar esta carrera?');">
                                        <input type="hidden" name="eliminar" value="<?= $c['id_carrera'] ?>">
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
            <!-- Paginación -->
            <?php 
            $totalPaginas = ceil($totalCarreras / $carrerasPorPagina); // Calcular total de páginas
            if ($totalPaginas > 1): ?> <!-- Mostrar paginación solo si hay más de una página -->    
            <div style="display:flex;justify-content:center;gap:.5em;margin:2em 0;">
                <?php
                $maxBotones = 3; // Máximo de botones de página a mostrar
                $inicioPag = max(1, $paginaActual - 1); // Página de inicio
                $finPag = min($totalPaginas, $inicioPag + $maxBotones - 1); // Página de fin
                if ($totalPaginas > 5) { // Si hay muchas páginas, ajustar inicio y fin
                    if ($paginaActual > 1) { // Si no es la primera página
                        echo '<form method="get" style="display:inline;"><input type="hidden" name="pag" value="1"><button type="submit" class="btn-accion" title="Primera página">&lt;&lt;</button></form> ';
                        echo '<form method="get" style="display:inline;"><input type="hidden" name="pag" value="' . ($paginaActual - 1) . '"><button type="submit" class="btn-accion" title="Anterior">&lt;</button></form> ';
                    }
                    for ($i = $inicioPag; $i <= $finPag; $i++) { // Botones de página
                        if ($i == $paginaActual) { // Página actual
                            echo '<button class="btn-accion" style="background:#0a2342;color:#fff;" disabled>' . $i . '</button> ';
                        } else { // Otras páginas
                            echo '<form method="get" style="display:inline;"><input type="hidden" name="pag" value="' . $i . '"><button type="submit" class="btn-accion">' . $i . '</button></form> ';
                        }
                    }
                    if ($paginaActual < $totalPaginas) { // Si no es la última página
                        echo '<form method="get" style="display:inline;"><input type="hidden" name="pag" value="' . ($paginaActual + 1) . '"><button type="submit" class="btn-accion" title="Siguiente">&gt;</button></form> ';
                        echo '<form method="get" style="display:inline;"><input type="hidden" name="pag" value="' . $totalPaginas . '"><button type="submit" class="btn-accion" title="Última página">&gt;&gt;</button></form> ';
                    }
                } else {
                    for ($i = 1; $i <= $totalPaginas; $i++) { // Mostrar todos los botones si pocas páginas
                        if ($i == $paginaActual) { // Página actual
                            echo '<button class="btn-accion" style="background:#0a2342;color:#fff;" disabled>' . $i . '</button> ';
                        } else { // Otras páginas
                            echo '<form method="get" style="display:inline;"><input type="hidden" name="pag" value="' . $i . '"><button type="submit" class="btn-accion">' . $i . '</button></form> ';
                        }
                    }
                }
                ?>
            </div>
            <?php endif; ?>
        </section>
        <section>
            <h2>Agregar nueva carrera</h2>
            <form method="post" id="form-carrera" autocomplete="off"> <!-- Formulario para agregar carrera -->
                <input type="text" name="nombre" class="input-pregunta" placeholder="Nombre de la carrera" required> <!-- Campo de nombre -->
                <input type="text" name="descripcion" class="input-pregunta" placeholder="Descripción"> <!-- Campo de descripción -->
                <div style="display:flex;gap:1em;flex-wrap:wrap;margin:1em 0;">
                    <?php $letras = ['R','I','A','S','E','C']; foreach ($letras as $letra): ?> <!-- Campos de porcentaje por letra -->
                        <div style="display:flex;flex-direction:column;align-items:center;">
                            <label for="porcentaje_<?= $letra ?>" style="font-weight:700;"><?= $letra ?></label> <!-- Etiqueta de letra -->
                            <input type="number" name="porcentaje_<?= $letra ?>" id="porcentaje_<?= $letra ?>" min="0" max="100" inputmode="numeric" pattern="[0-9]*" style="width:70px;text-align:center;appearance:textfield;-webkit-appearance:textfield;" required> <!-- Campo de porcentaje -->
                        </div>
                    <?php endforeach; ?> <!-- Fin de campos de porcentaje por letra -->
                </div>
                <button type="submit" name="agregar_carrera" class="btn-accion">Agregar carrera</button> <!-- Botón de agregar -->
                <div id="error-carrera" style="color:red;font-weight:700;margin-top:1em;display:none;"></div> <!-- Div para mostrar errores -->
            </form>
        </section>
        <br>
        <?php if ($editando && $carreraEdit): ?> <!-- Sección de edición si se está editando -->
        <section>
            <hr>
            <h2>Editar carrera</h2>
            <form method="post" id="form-carrera-edit" autocomplete="off"> <!-- Formulario para editar carrera -->
                <input type="hidden" name="id_edit" value="<?= $carreraEdit['id_carrera'] ?>"> <!-- Campo oculto con ID de carrera -->
                <input type="text" name="nombre_edit" class="input-pregunta" value="<?= htmlspecialchars($carreraEdit['nombre']) ?>" required> <!-- Campo de nombre -->
                <input type="text" name="descripcion_edit" class="input-pregunta" value="<?= htmlspecialchars($carreraEdit['descripcion'] ?? '') ?>"> <!-- Campo de descripción -->
                <div style="display:flex;gap:1em;flex-wrap:wrap;margin:1em 0;">
                    <?php foreach ($letras as $letra): ?> <!-- Campos de porcentaje por letra -->
                        <div style="display:flex;flex-direction:column;align-items:center;">
                            <label for="porcentaje_<?= $letra ?>_edit" style="font-weight:700;"><?= $letra ?></label> <!-- Etiqueta de letra -->
                            <input type="number" name="porcentaje_<?= $letra ?>_edit" id="porcentaje_<?= $letra ?>_edit" min="0" max="100" inputmode="numeric" pattern="[0-9]*" style="width:70px;text-align:center;appearance:textfield;-webkit-appearance:textfield;" value="<?= (int)$carreraEdit['porcentaje_' . $letra] ?>" required> <!-- Campo de porcentaje -->
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="editar_carrera" class="btn-accion">Guardar cambios</button> <!-- Botón de guardar cambios -->
                <div id="error-carrera-edit" style="color:red;font-weight:700;margin-top:1em;display:none;"></div> <!-- Div para mostrar errores -->
            </form>
        </section>
        <?php endif; ?>
    </main>
    <style> 
    /* Ocultar flechas de los inputs number */
    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
    </style>
    <script src="/RIASEC/JAVASCRIPT/Recursos.js"></script>
    
</body>
</html>
