<?php
// Vista de estadísticas de resultados
require_once '../../PHP/crud.php';
session_start();
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    die('<h2>Acceso denegado. Solo administradores.</h2>');
}
$resumen = ['R'=>0,'I'=>0,'A'=>0,'S'=>0,'E'=>0,'C'=>0];
try {
    $resultados = obtenerResultados();
    $resumen = ['R'=>0,'I'=>0,'A'=>0,'S'=>0,'E'=>0,'C'=>0];
    $valores = ['R'=>[],'I'=>[],'A'=>[],'S'=>[],'E'=>[],'C'=>[]];
    $dominancia = ['R'=>0,'I'=>0,'A'=>0,'S'=>0,'E'=>0,'C'=>0];
    foreach ($resultados as $r) {
        foreach (['R','I','A','S','E','C'] as $letra) {
            $puntaje = (int)$r['puntaje_' . $letra];
            $resumen[$letra] += $puntaje;
            $valores[$letra][] = $puntaje;
        }
        // Dominancia: la letra con mayor puntaje en este resultado
        $max = max((int)$r['puntaje_R'], (int)$r['puntaje_I'], (int)$r['puntaje_A'], (int)$r['puntaje_S'], (int)$r['puntaje_E'], (int)$r['puntaje_C']);
        foreach (['R','I','A','S','E','C'] as $letra) {
            if ((int)$r['puntaje_' . $letra] === $max) {
                $dominancia[$letra]++;
            }
        }
    }
    $n = count($resultados);
    $promedio = $mediana = $desv = $porcDominancia = [];
    foreach (['R','I','A','S','E','C'] as $letra) {
        // Promedio
        $promedio[$letra] = $n > 0 ? round(array_sum($valores[$letra])/$n,2) : 0;
        // Mediana
        $arr = $valores[$letra];
        sort($arr);
        $count = count($arr);
        if ($count === 0) {
            $mediana[$letra] = 0;
        } elseif ($count % 2) {
            $mediana[$letra] = $arr[floor($count/2)];
        } else {
            $mediana[$letra] = round(($arr[$count/2-1]+$arr[$count/2])/2,2);
        }
        // Desviación estándar
        $media = $promedio[$letra];
        $sum = 0;
        foreach ($valores[$letra] as $v) $sum += pow($v-$media,2);
        $desv[$letra] = $n > 0 ? round(sqrt($sum/$n),2) : 0;
        // Porcentaje de dominancia
        $porcDominancia[$letra] = $n > 0 ? round(($dominancia[$letra]/$n)*100,2) : 0;
    }
} catch (Exception $e) {
    die('<h2>Error al cargar resultados: ' . htmlspecialchars($e->getMessage()) . '</h2>');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas</title>
</head>
<body>
    <div id="user-menu" style="text-align:right;margin:1em;"></div>
    <main>
        <h1>Estadísticas de Resultados</h1>
        <section>
            <h2>Resumen de puntajes totales</h2>
            <ul>
                <li>R: <?= $resumen['R'] ?> | Promedio: <?= $promedio['R'] ?> | Mediana: <?= $mediana['R'] ?> | Desv. estándar: <?= $desv['R'] ?> | % Dominancia: <?= $porcDominancia['R'] ?>%</li>
                <li>I: <?= $resumen['I'] ?> | Promedio: <?= $promedio['I'] ?> | Mediana: <?= $mediana['I'] ?> | Desv. estándar: <?= $desv['I'] ?> | % Dominancia: <?= $porcDominancia['I'] ?>%</li>
                <li>A: <?= $resumen['A'] ?> | Promedio: <?= $promedio['A'] ?> | Mediana: <?= $mediana['A'] ?> | Desv. estándar: <?= $desv['A'] ?> | % Dominancia: <?= $porcDominancia['A'] ?>%</li>
                <li>S: <?= $resumen['S'] ?> | Promedio: <?= $promedio['S'] ?> | Mediana: <?= $mediana['S'] ?> | Desv. estándar: <?= $desv['S'] ?> | % Dominancia: <?= $porcDominancia['S'] ?>%</li>
                <li>E: <?= $resumen['E'] ?> | Promedio: <?= $promedio['E'] ?> | Mediana: <?= $mediana['E'] ?> | Desv. estándar: <?= $desv['E'] ?> | % Dominancia: <?= $porcDominancia['E'] ?>%</li>
                <li>C: <?= $resumen['C'] ?> | Promedio: <?= $promedio['C'] ?> | Mediana: <?= $mediana['C'] ?> | Desv. estándar: <?= $desv['C'] ?> | % Dominancia: <?= $porcDominancia['C'] ?>%</li>
            </ul>
        </section>
        <form action="Opciones.php" method="get" style="margin-top:2em;">
            <button type="submit">Volver a Opciones</button>
        </form>

        <section>
            <h2>Ver todas las pruebas individuales</h2>
            <form method="get" style="margin-bottom:1em;">
                <label for="ordenar">Ordenar por:</label>
                <select name="ordenar" id="ordenar">
                    <option value="fecha" <?= (isset($_GET['ordenar']) && $_GET['ordenar']==='fecha') ? 'selected' : '' ?>>Fecha</option>
                    <option value="puntaje_R" <?= (isset($_GET['ordenar']) && $_GET['ordenar']==='puntaje_R') ? 'selected' : '' ?>>R</option>
                    <option value="puntaje_I" <?= (isset($_GET['ordenar']) && $_GET['ordenar']==='puntaje_I') ? 'selected' : '' ?>>I</option>
                    <option value="puntaje_A" <?= (isset($_GET['ordenar']) && $_GET['ordenar']==='puntaje_A') ? 'selected' : '' ?>>A</option>
                    <option value="puntaje_S" <?= (isset($_GET['ordenar']) && $_GET['ordenar']==='puntaje_S') ? 'selected' : '' ?>>S</option>
                    <option value="puntaje_E" <?= (isset($_GET['ordenar']) && $_GET['ordenar']==='puntaje_E') ? 'selected' : '' ?>>E</option>
                    <option value="puntaje_C" <?= (isset($_GET['ordenar']) && $_GET['ordenar']==='puntaje_C') ? 'selected' : '' ?>>C</option>
                </select>
                <button type="submit">Filtrar</button>
            </form>

            <?php
            // Mostrar tabla de resultados individuales
            if (!empty($resultados)) {
                // Ordenar resultados según filtro
                $ordenar = $_GET['ordenar'] ?? 'fecha';
                usort($resultados, function($a, $b) use ($ordenar) {
                    if ($ordenar === 'fecha') {
                        return strtotime($b['fecha']) - strtotime($a['fecha']); // más reciente primero
                    }
                    return $b[$ordenar] <=> $a[$ordenar]; // descendente
                });
                echo '<table border="1" aria-label="Resultados individuales">';
                echo '<thead><tr><th>Usuario</th><th>Fecha</th><th>R</th><th>I</th><th>A</th><th>S</th><th>E</th><th>C</th></tr></thead><tbody>';
                foreach ($resultados as $r) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($r['id_usuario']) . '</td>';
                    echo '<td>' . htmlspecialchars($r['fecha']) . '</td>';
                    echo '<td>' . htmlspecialchars($r['puntaje_R']) . '</td>';
                    echo '<td>' . htmlspecialchars($r['puntaje_I']) . '</td>';
                    echo '<td>' . htmlspecialchars($r['puntaje_A']) . '</td>';
                    echo '<td>' . htmlspecialchars($r['puntaje_S']) . '</td>';
                    echo '<td>' . htmlspecialchars($r['puntaje_E']) . '</td>';
                    echo '<td>' . htmlspecialchars($r['puntaje_C']) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p>No hay resultados individuales registrados.</p>';
            }
            ?>
        </section>
    </main>
    <script src="/RIASEC/JAVASCRIPT/login.js"></script>
</body>
</html>