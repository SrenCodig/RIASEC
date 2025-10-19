<!--
    Vista de resultados del test vocacional RIASEC.
    Muestra los puntajes, perfil y recomendaciones basadas en las respuestas del usuario.
    Incluye historial de pruebas pasadas para usuarios registrados.
-->
<?php
require_once __DIR__ . '/../../PHP/Funciones/ResultadosF.php';
?>
<!-- ============================================================
PARTE HTML (todo el renderizado visual)
============================================================ -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de la Prueba</title>
    <link rel="stylesheet" href="/RIASEC/STYLE/Base.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Emergente.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Formulario.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/DarkMode.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Carrusel.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes">
</head>

<body>
    <nav id="user-menu" class="user-menu-top"></nav>
    <header class="resultados-header">
        <div class="dark-mode-switch" id="darkModeSwitch">
            <div class="circle">
                <span class="sun"><svg width="32" height="32" viewBox="0 0 32 32"><circle cx="16" cy="16" r="10" fill="#FCDE5B"/><g stroke="#FCDE5B" stroke-width="2"><line x1="16" y1="2" x2="16" y2="8"/><line x1="16" y1="24" x2="16" y2="30"/><line x1="2" y1="16" x2="8" y2="16"/><line x1="24" y1="16" x2="30" y2="16"/><line x1="6.34" y1="6.34" x2="10.49" y2="10.49"/><line x1="21.51" y1="21.51" x2="25.66" y2="25.66"/><line x1="6.34" y1="25.66" x2="10.49" y2="21.51"/><line x1="21.51" y1="10.49" x2="25.66" y2="6.34"/></g></svg></span>
                <span class="moon"><svg width="32" height="32" viewBox="0 0 32 32"><path d="M22 16a10 10 0 1 1-10-10c0 5.52 4.48 10 10 10z" fill="#fff"/></svg></span>
            </div>
        </div>
        <h1 class="titulo-principal">Resultados de tu Prueba</h1>
    </header>

    <main class="resultados-main">
        <?php if ($usuarioRegistrado): ?> <!-- Si el usuario está registrado -->
        <section class="historial-section"> <!-- Sección de historial -->
            <h2 class="subtitulo">Historial de pruebas realizadas</h2> 
            <?php if (!$resultadosUsuario): ?> <!-- Si no hay resultados -->
                <p class="info">No tienes pruebas guardadas.</p>
            <?php else: ?>
            <div class="carrusel-container"> <!-- Contenedor del carrusel -->
                <div class="carrusel" id="historial-carrusel">
                    <?php foreach ($resultadosUsuario as $index => $res): ?> <!-- Itera sobre los resultados del usuario -->
                    <div class="carrusel-item<?= $index === 0 ? ' active' : '' ?>"> <!-- Marca el primer ítem como activo -->
                        <div class="carrusel-card"> <!-- Tarjeta del resultado -->
                            <span class="carrusel-fecha">Fecha: <?= htmlspecialchars($res['fecha']) ?></span> <!-- Muestra la fecha del resultado -->
                            <?php if ($index === 0): ?> <!-- Si es el más reciente -->
                                <span class="carrusel-badge badge-ultima">Última realizada</span> <!-- Etiqueta de última realizada -->
                            <?php elseif ($index === count($resultadosUsuario)-1): ?> <!-- Si es el más antiguo -->
                                <span class="carrusel-badge badge-primera">Primera realizada</span> <!-- Etiqueta de primera realizada -->
                            <?php endif; ?>
                            <table class="tabla-historial" aria-label="Puntajes de la prueba"> <!-- Tabla de puntajes -->
                                <thead><tr><th>R</th><th>I</th><th>A</th><th>S</th><th>E</th><th>C</th></tr></thead>
                                <tbody>
                                    <tr>
                                        <td><?= htmlspecialchars($res['puntaje_R']) ?></td>
                                        <td><?= htmlspecialchars($res['puntaje_I']) ?></td>
                                        <td><?= htmlspecialchars($res['puntaje_A']) ?></td>
                                        <td><?= htmlspecialchars($res['puntaje_S']) ?></td>
                                        <td><?= htmlspecialchars($res['puntaje_E']) ?></td>
                                        <td><?= htmlspecialchars($res['puntaje_C']) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                            <form method="get" class="detalle-form"> <!-- Formulario para ver detalle -->
                                <input type="hidden" name="id_resultado" value="<?= htmlspecialchars($res['id_resultado']) ?>"> <!-- ID del resultado -->
                                <button type="submit" class="btn-pag">Ver detalle</button> <!-- Botón para ver detalle -->
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="carrusel-prev" aria-label="Anterior">&#10094;</button> <!-- Botón anterior -->
                <button class="carrusel-next" aria-label="Siguiente">&#10095;</button> <!-- Botón siguiente -->
            </div>
            <?php endif; ?>
        </section>
        <hr>
        <?php
        if ($mostrarDetalle && $detalleResultado) { // Si se muestra detalle de un resultado específico
            echo '<h2 class="subtitulo">Estás viendo una prueba pasada</h2>'; // Título de prueba pasada
        } else {
            echo '<h2 class="subtitulo">Resultado de tu prueba más reciente</h2>'; // Título de prueba más reciente
        }
        ?>
        <?php else: ?>
        <h2 class="subtitulo">Resultado de tu prueba</h2>
        <?php endif; ?>

        <?php
        // Obtener el resultado a mostrar (detalle o actual)
        $resultadoParaMostrar = $mostrarDetalle && $detalleResultado ? $detalleResultado : $resultadoActual;
        if ($resultadoParaMostrar):
            // Usar la función centralizada para calcular puntajes, perfil y afinidades
            $calc = calcularPerfilYAfinidades($resultadoParaMostrar);
            $puntajes = $calc['puntajes']; 
            $perfilUsuario = $calc['perfilUsuario'];
            $afinidades = $calc['afinidades'];
            $carrerasRecomendadas = array_slice($afinidades, 0, 3);
            // Determinar las 3 letras dominantes
            $ordenadas = $perfilUsuario;
            arsort($ordenadas);
            $dominantes = array_slice(array_keys($ordenadas), 0, 3);
            // Construir el texto narrativo del perfil
            $perfil = "Tu perfil vocacional dominante es: <strong>" . implode(", ", $dominantes) . "</strong>.<br>Esto significa que tienes una combinación de intereses y habilidades en los siguientes ámbitos:<ul>";
            foreach ($dominantes as $letra) $perfil .= "<li><strong>$letra</strong>: {$explicaciones[$letra]}</li>";
            $perfil .= "</ul>Personas con este perfil suelen destacar en áreas donde se combinan estas características. Te recomendamos explorar carreras y ocupaciones que integren estos intereses.";
        ?>
        <section class="resultado-section"> <!-- Sección de resultado -->
            <article class="puntajes-article"> <!-- Artículo de puntajes -->
                <h3 class="subtitulo">Puntajes totales (ordenados):</h3> <!-- Artículo de puntajes -->
                <ul class="puntajes-lista">
                    <?php foreach ($puntajes as $letra => $valor): ?> <!-- Itera sobre los puntajes -->
                    <li class="puntaje-item">
                        <span class="puntaje-circulo"> <!-- Círculo del puntaje -->
                            <span class="puntaje-letra"><?= $letra ?></span>
                            <span class="puntaje-numero"><?= $valor ?></span>
                        </span>
                        <span class="explicacion"><?= $explicaciones[$letra] ?></span> <!-- Explicación del puntaje -->
                    </li>
                    <?php endforeach; ?>
                </ul>
            </article>

            <article class="dominantes-article"> <!-- Artículo de letras dominantes y carreras -->
                <h3 class="subtitulo">Tus 3 letras dominantes:</h3>
                <ul class="dominantes-lista">
                    <?php foreach ($dominantes as $letra): ?> <!-- Itera sobre las letras dominantes -->
                    <li class="dominante-item">
                        <span class="letra-dominante"><span class="letra-dominante-texto"><?= $letra ?></span></span>
                        <span class="explicacion-dominante"><?= $explicaciones[$letra] ?></span> <!-- Explicación de la letra dominante -->
                    </li>
                    <?php endforeach; ?>
                </ul>

                <h3 class="subtitulo">Tus 3 mejores carreras recomendadas:</h3>
                <ul class="carreras-nombres-lista">
                    <?php foreach ($carrerasRecomendadas as $carrera): ?> <!-- Itera sobre las carreras recomendadas -->
                    <li class="carrera-nombre-item">
                        <span class="carrera-nombre-texto"><?= htmlspecialchars($carrera['nombre']) ?></span> 
                    </li>
                    <?php endforeach; ?>
                    <li style="list-style:none;margin-top:1em;text-align:center;">
                        <form method="post" action="">
                            <input type="hidden" name="descargar_detalles" value="1"> 
                            <button type="submit" class="btn-pag">Ver detalles</button> <!-- Botón para ver detalles -->
                        </form>
                    </li>
                </ul>
            </article>

            <article class="perfil-article">
                <h3 class="subtitulo">Perfil narrativo:</h3> <!-- Artículo de perfil narrativo -->
                <div class="perfil-narrativo perfil-narrativo-simple">
                    <div class="perfil-narrativo-texto"><?= $perfil ?></div>
                    <section class="carreras-descripcion-section perfil-narrativo-carreras">
                        <h4 class="carreras-descripcion-titulo">Tus 3 carreras recomendadas y sus descripciones:</h4>
                        <ul class="carreras-descripcion-lista">
                            <?php foreach ($carrerasRecomendadas as $carrera): ?> <!-- Itera sobre las carreras recomendadas -->
                            <li class="carrera-descripcion-item">
                                <strong class="carrera-descripcion-nombre"><?= htmlspecialchars($carrera['nombre']) ?>:</strong>
                                <span class="carrera-descripcion-texto"> <?= htmlspecialchars($carrera['descripcion']) ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                </div>
            </article>
        </section>
        <?php else: ?> 
            <p class="info">No se recibieron respuestas.</p> <!-- Mensaje si no hay resultado -->
        <?php endif; ?>

        <footer class="resultados-footer">
            <form action="../../index.php" method="get" class="volver-form">
                <button type="submit" class="btn-pag">Volver al inicio</button> <!-- Botón para volver al inicio -->
            </form>
        </footer>
    </main>

    <script src="/RIASEC/JAVASCRIPT/Recursos.js"></script>
</body>
</html>
