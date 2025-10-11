<!-- PARTE PHP -->

<?php
require_once __DIR__ . '/../../PHP/crud.php';
session_start();

$resultadosUsuario = [];
$usuarioRegistrado = isset($_SESSION['id_usuario']);
$resultadoActual = null;
$explicaciones = [
    'R' => 'Realista: Prefieres actividades prácticas, trabajo físico y el uso de herramientas o maquinaria.',
    'I' => 'Investigador: Te atraen las actividades analíticas, científicas y el aprendizaje intelectual.',
    'A' => 'Artístico: Disfrutas de la creatividad, el arte, la música y la autoexpresión.',
    'S' => 'Social: Te gusta ayudar, enseñar, orientar y trabajar con personas.',
    'E' => 'Emprendedor: Prefieres liderar, persuadir, vender y asumir riesgos.',
    'C' => 'Convencional: Te atraen las tareas organizativas, administrativas y el trabajo con datos.'
];

try {
    if ($usuarioRegistrado) {
        $id_usuario = $_SESSION['id_usuario'];
        foreach (obtenerResultados() as $res) {
            if ($res['id_usuario'] == $id_usuario) $resultadosUsuario[] = $res;
        }
        if ($resultadosUsuario) {
            usort($resultadosUsuario, fn($a, $b) => strtotime($b['fecha']) - strtotime($a['fecha']));
            $resultadoActual = $resultadosUsuario[0];
        }
    } elseif (isset($_SESSION['respuestas_riasec'])) {
        $respuestas = $_SESSION['respuestas_riasec'];
        $puntajes = array_fill_keys(['R','I','A','S','E','C'],0);
        foreach (obtenerPreguntas() as $p) {
            $pid = 'pregunta_' . $p['id_pregunta'];
            if (isset($respuestas[$pid])) $puntajes[$p['categoria']] += (int)$respuestas[$pid];
        }
        $resultadoActual = [
            'fecha' => date('Y-m-d H:i:s'),
            'puntaje_R' => $puntajes['R'],
            'puntaje_I' => $puntajes['I'],
            'puntaje_A' => $puntajes['A'],
            'puntaje_S' => $puntajes['S'],
            'puntaje_E' => $puntajes['E'],
            'puntaje_C' => $puntajes['C'],
        ];
    }
} catch (Exception $e) {
    die('<h2 class="error">Error al cargar resultados: ' . htmlspecialchars($e->getMessage()) . '</h2>');
}

$mostrarDetalle = false;
$detalleResultado = null;
if (isset($_GET['id_resultado']) && $usuarioRegistrado) {
    foreach ($resultadosUsuario as $res) {
        if ($res['id_resultado'] == $_GET['id_resultado']) {
            $detalleResultado = $res;
            $mostrarDetalle = true;
            break;
        }
    }
}
?>

<!-- PARTE HTML -->

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
        <?php if ($usuarioRegistrado): ?>
        <section class="historial-section">
            <h2 class="subtitulo">Historial de pruebas realizadas</h2>
            <?php if (!$resultadosUsuario): ?>
                <p class="info">No tienes pruebas guardadas.</p>
            <?php else: ?>
                <div class="carrusel-container">
                    <div class="carrusel" id="historial-carrusel">
                        <?php foreach ($resultadosUsuario as $index => $res): ?>
                        <div class="carrusel-item<?= $index === 0 ? ' active' : '' ?>">
                            <div class="carrusel-card">
                                <!-- Fecha en la esquina superior izquierda -->
                                <span class="carrusel-fecha">Fecha: <?= htmlspecialchars($res['fecha']) ?></span>
                                <!-- Badge en la esquina superior derecha -->
                                <?php if ($index === 0): ?>
                                    <span class="carrusel-badge badge-ultima">Última realizada</span>
                                <?php elseif ($index === count($resultadosUsuario)-1): ?>
                                    <span class="carrusel-badge badge-primera">Primera realizada</span>
                                <?php endif; ?>
                                <table class="tabla-historial" aria-label="Puntajes de la prueba">
                                    <thead>
                                        <tr>
                                            <th>R</th><th>I</th><th>A</th><th>S</th><th>E</th><th>C</th>
                                        </tr>
                                    </thead>
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
                                <form method="get" class="detalle-form">
                                    <input type="hidden" name="id_resultado" value="<?= $res['id_resultado'] ?>">
                                    <button type="submit" class="btn-pag">Ver detalle</button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carrusel-prev" aria-label="Anterior">&#10094;</button>
                    <button class="carrusel-next" aria-label="Siguiente">&#10095;</button>
                </div>
            <?php endif; ?>
        </section>
        <hr>
        <h2 class="subtitulo">Resultado de tu prueba más reciente</h2>
        <?php else: ?>
        <h2 class="subtitulo">Resultado de tu prueba</h2>
        <?php endif; ?>

        <?php
        $resultadoParaMostrar = $mostrarDetalle && $detalleResultado ? $detalleResultado : $resultadoActual;
        if ($resultadoParaMostrar):
            $puntajes = [
                'R' => $resultadoParaMostrar['puntaje_R'],
                'I' => $resultadoParaMostrar['puntaje_I'],
                'A' => $resultadoParaMostrar['puntaje_A'],
                'S' => $resultadoParaMostrar['puntaje_S'],
                'E' => $resultadoParaMostrar['puntaje_E'],
                'C' => $resultadoParaMostrar['puntaje_C']
            ];
            arsort($puntajes);
            $dominantes = array_slice(array_keys($puntajes), 0, 3);
            $perfil = "Tu perfil vocacional dominante es: <strong>" . implode(", ", $dominantes) . "</strong>.<br>";
            $perfil .= "Esto significa que tienes una combinación de intereses y habilidades en los siguientes ámbitos:<ul>";
            foreach ($dominantes as $letra) {
                $perfil .= "<li><strong>$letra</strong>: " . $explicaciones[$letra] . "</li>";
            }
            $perfil .= "</ul>Personas con este perfil suelen destacar en áreas donde se combinan estas características. Te recomendamos explorar carreras y ocupaciones que integren estos intereses para potenciar tu desarrollo profesional y personal.";
        ?>
        <section class="resultado-section">
            <article class="puntajes-article">
                <h3 class="subtitulo">Puntajes totales de esta prueba (ordenados):</h3>
                <ul class="puntajes-lista">
                    <?php foreach ($puntajes as $letra => $valor): ?>
                        <li class="puntaje-item">
                            <span class="puntaje-circulo">
                                <span class="puntaje-letra"><?= $letra ?></span>
                                <span class="puntaje-numero"><?= $valor ?></span>
                            </span>
                            <span class="explicacion"><?= $explicaciones[$letra] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </article>
            <article class="dominantes-article">
                <h3 class="subtitulo">Tus 3 letras dominantes:</h3>
                <ul class="dominantes-lista">
                    <?php foreach ($dominantes as $letra): ?>
                        <li class="dominante-item">
                            <span class="letra-dominante"><span class="letra-dominante-texto" style="width:100%;text-align:center;"><?= $letra ?></span></span>
                            <span class="explicacion-dominante"><?= $explicaciones[$letra] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </article>
            <article class="perfil-article">
                <h3 class="subtitulo">Perfil narrativo:</h3>
                <div class="perfil-narrativo">
                    <?= $perfil ?>
                </div>
            </article>
        </section>
        <?php else: ?>
            <p class="info">No se recibieron respuestas.</p>
        <?php endif; ?>
        <footer class="resultados-footer">
            <form action="../../index.php" method="get" class="volver-form">
                <button type="submit" class="btn-pag">Volver al inicio</button>
            </form>
        </footer>
    </main>
    <script src="/RIASEC/JAVASCRIPT/Recursos.js"></script>
</body>
</html>