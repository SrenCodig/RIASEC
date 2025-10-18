<?php require_once __DIR__ . '/../../PHP/Funciones/EstadisticasF.php'; ?>

<!-- === ESTRUCTURA HTML === -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas</title>
    <!-- Estilos principales -->
    <link rel="stylesheet" href="/RIASEC/STYLE/Base.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Emergente.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Formulario.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/EstadisticasVisual.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/DarkMode.css">
</head>

<body>
    <!-- Switch modo oscuro -->
    <div class="dark-mode-switch" id="darkModeSwitch">
        <div class="circle">
            <span class="sun">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="16" cy="16" r="10" fill="#FCDE5B"/>
                    <g stroke="#FCDE5B" stroke-width="2">
                        <line x1="16" y1="2" x2="16" y2="8"/>
                        <line x1="16" y1="24" x2="16" y2="30"/>
                        <line x1="2" y1="16" x2="8" y2="16"/>
                        <line x1="24" y1="16" x2="30" y2="16"/>
                        <line x1="6.34" y1="6.34" x2="10.49" y2="10.49"/>
                        <line x1="21.51" y1="21.51" x2="25.66" y2="25.66"/>
                        <line x1="6.34" y1="25.66" x2="10.49" y2="21.51"/>
                        <line x1="21.51" y1="10.49" x2="25.66" y2="6.34"/>
                    </g>
                </svg>
            </span>
            <span class="moon">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 16a10 10 0 1 1-10-10c0 5.52 4.48 10 10 10z" fill="#fff"/>
                </svg>
            </span>
        </div>
    </div>
    <!-- === MENÚ SUPERIOR DEL USUARIO === -->
    <div id="user-menu" class="user-menu-top"></div>

    <main>
        <!-- Mensaje de retroalimentación -->
        <?php if (!empty($_GET['msg'])): ?>
            <p class="info"><strong><?= htmlspecialchars($_GET['msg']) ?></strong></p>
        <?php endif; ?>

        <!-- === SECCIÓN PRINCIPAL DE ESTADÍSTICAS === -->
        <h1 class="titulo-principal">Estadísticas de Resultados RIASEC</h1>

        <section>
            <!-- === TABLA DE ESTADÍSTICAS POR LETRA === -->
            <h2 class="subtitulo">Estadísticas por letra</h2>

            <div class="tabla-responsive">
                <table class="estadisticas-table" aria-label="Estadísticas por letra">
                    <thead>
                        <tr>
                            <th>Letra</th>
                            <th>Preguntas</th>
                            <th>Puntaje máx.</th>
                            <th>Promedio</th>
                            <th>Mediana</th>
                            <th>Desviación estándar</th>
                            <th>% Dominancia</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        // Determinar dominancias máximas y mínimas
                        $maxDom = $minDom = null;
                        if ($estadisticas) {
                            $dominancias = array_map(fn($l)=>$estadisticas[$l]['porcDominancia'],$letras);
                            $maxDom = max($dominancias);
                            $minDom = min($dominancias);
                        }

                        // Generar filas dinámicas por cada letra RIASEC
                        foreach ($letras as $l):
                            $dom = $estadisticas[$l]['porcDominancia'];
                            $claseBarra = ($dom==$maxDom) ? 'dominante' : (($dom==$minDom) ? 'bajo' : 'intermedio');
                        ?>
                        <tr>
                            <td class="col-letra"><?= $l ?></td>
                            <td class="col-preguntas"><?= $numPreguntas[$l] ?></td>
                            <td class="col-max"><?= $puntajeMax[$l] ?></td>
                            <td class="col-promedio"><?= $estadisticas[$l]['promedio'] ?></td>
                            <td class="col-mediana"><?= $estadisticas[$l]['mediana'] ?></td>
                            <td class="col-desv"><?= $estadisticas[$l]['desv'] ?></td>
                            <td class="col-dominancia">
                                <div class="dominancia-bar">
                                    <div class="dominancia-bar-inner <?= $claseBarra ?>" style="width:<?= $dom ?>%;"></div>
                                </div>
                                <div class="dominancia-bar-porcentaje"><?= $dom ?>%</div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- === GRÁFICA DE PROMEDIOS === -->
            <h2 class="subtitulo">Promedio de Puntaje</h2>

            <div class="tabla-responsive">
                <div class="grafica-barras-encapsulado">
                    <?php
                    // Tipos de gráfica disponibles
                    $tipos = [
                        'promedio' => 'Promedio',
                        'mediana' => 'Mediana',
                        'desv' => 'Desviación estándar'
                    ];
                    $tipoSel = isset($_GET['tipo']) && isset($tipos[$_GET['tipo']]) ? $_GET['tipo'] : 'promedio';

                    // Generar controles de tipo de gráfica
                    echo '<form method="get" class="grafica-barras-controles">';
                    foreach ($tipos as $key => $label) {
                        $active = ($tipoSel==$key) ? 'active' : '';
                        echo '<button type="submit" name="tipo" value="'.$key.'" class="'.$active.'">'.$label.'</button>';
                    }
                    echo '</form>';

                    // Generar gráfica visual
                    $valores = array_map(fn($l)=>$estadisticas[$l][$tipoSel], $letras);
                    $max = max($valores);
                    $min = min($valores);
                    echo '<div class="grafica-barras">';
                    foreach ($letras as $i => $l) {
                        $valor = $valores[$i];
                        $clase = ($valor==$max) ? 'dominante' : (($valor==$min) ? 'bajo' : 'intermedio');
                        $altura = 30 + $valor;
                        echo '<div class="barra-letra '.$clase.'" style="height:'.$altura.'px;">';
                        echo '<span class="barra-valor">'.$valor.'</span><span class="barra-label">'.$l.'</span>';
                        echo '</div>';
                    }
                    echo '</div>';
                    ?>
                </div>
            </div>
        </section>

        <!-- === BOTÓN VOLVER === -->
        <form action="Opciones.php" method="get" class="volver-form">
            <button type="submit" class="btn-pag">Volver a Opciones</button>
        </form>
    </main>

    <!-- Script principal -->
    <script src="/RIASEC/JAVASCRIPT/Recursos.js"></script>
</body>
</html>
