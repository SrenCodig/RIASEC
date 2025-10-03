<!--
Panel de estadísticas y gestión de resultados RIASEC para administradores.

Funcionalidad principal:
 * - Verifica acceso de usuario administrador mediante sesión.
 * - Obtiene y muestra estadísticas generales por letra RIASEC:
 *   - Número de preguntas por letra.
 *   - Puntaje máximo posible por letra.
 *   - Promedio, mediana, desviación estándar y porcentaje de dominancia por letra.
 * - Presenta explicaciones de cada letra del modelo RIASEC.
 * - Permite visualizar, editar y eliminar resultados individuales de usuarios:
 *   - Ordenar resultados por fecha o puntaje de cada letra.
 *   - Ver detalle de un resultado, incluyendo perfil vocacional dominante y narrativa explicativa.
 *   - Editar puntajes de un resultado individual.
 *   - Eliminar resultados con confirmación.
 * - Navegación hacia otras opciones administrativas.
 *
 * Estructura:
 * - PHP: Lógica de acceso, obtención de datos, cálculo de estadísticas, manejo de acciones CRUD.
 * - HTML: Presentación de estadísticas, explicaciones, resultados individuales y formularios de acción.
 *
 * Requiere:
 * - Funciones definidas en '../../PHP/crud.php': obtenerPreguntas(), obtenerOpciones(), obtenerResultados(), eliminarResultado(), actualizarResultado().
 * - Sesión iniciada y rol de administrador.
 *
 * Seguridad:
 * - Acceso restringido a administradores.
 * - Validación de datos en edición y eliminación.
-->

<!-- PARTE PHP -->
<?php
require_once '../../PHP/crud.php';
session_start();
$letras = ['R','I','A','S','E','C'];
// Explicaciones de cada letra
$explicaciones = [
    'R'=>'Realista: Prefieres actividades prácticas, trabajo físico y el uso de herramientas o maquinaria.',
    'I'=>'Investigador: Te atraen las actividades analíticas, científicas y el aprendizaje intelectual.',
    'A'=>'Artístico: Disfrutas de la creatividad, el arte, la música y la autoexpresión.',
    'S'=>'Social: Te gusta ayudar, enseñar, orientar y trabajar con personas.',
    'E'=>'Emprendedor: Prefieres liderar, persuadir, vender y asumir riesgos.',
    'C'=>'Convencional: Te atraen las tareas organizativas, administrativas y el trabajo con datos.'
];

// Verifica que el usuario sea administrador
function mostrarMensaje($msg, $tipo = 'info') {
    $clase = $tipo === 'error' ? 'color:red;' : 'color:green;';
    echo "<p style='$clase'><strong>".htmlspecialchars($msg)."</strong></p>";
}
// Solo permite acceso a administradores
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    mostrarMensaje('Acceso denegado. Solo administradores.','error');
    exit;
}

// Obtiene número de preguntas y puntaje máximo por letra
function obtenerDatosPreguntasOpciones($letras) {
    $preguntas = function_exists('obtenerPreguntas') ? obtenerPreguntas() : [];
    $opciones = function_exists('obtenerOpciones') ? obtenerOpciones() : [];
    $maxValor = $opciones ? max(array_column($opciones,'valor')) : 0;
    $numPreguntas = $puntajeMax = [];
    foreach ($letras as $letra) {
        $numPreguntas[$letra] = count(array_filter($preguntas,fn($p)=>$p['categoria']===$letra));
        $puntajeMax[$letra] = $numPreguntas[$letra]*$maxValor;
    }
    return [$numPreguntas, $puntajeMax];
}

// Obtiene todos los resultados
try {
    $resultados = obtenerResultados();
    $valores = $dominancia = array_fill_keys($letras, []);
    // Recorre resultados para calcular estadísticas
    foreach ($resultados as $r) {
        $max = max(array_map(fn($l)=> (int)$r['puntaje_'.$l], $letras));
        foreach ($letras as $letra) {
            $puntaje = (int)$r['puntaje_'.$letra];
            $valores[$letra][] = $puntaje;
            if ($puntaje === $max) $dominancia[$letra][] = 1;
        }
    }

    // Calcula estadísticas por letra
    $n = count($resultados);
    $estadisticas = [];
    foreach ($letras as $letra) {
        $arr = $valores[$letra];
        if ($n > 1) sort($arr);
        $media = $n ? array_sum($arr)/$n : 0;
        $estadisticas[$letra] = [
            'promedio' => round($media,2),
            'mediana' => $n ? ($n%2 ? $arr[floor($n/2)] : round(($arr[$n/2-1]+$arr[$n/2])/2,2)) : 0,
            'desv' => $n ? round(sqrt(array_sum(array_map(fn($v)=>pow($v-$media,2),$arr))/$n),2) : 0,
            'porcDominancia' => $n ? round(count($dominancia[$letra])/$n*100,2) : 0
        ];
    }

    // Manejo de acciones individuales (ver, editar, eliminar)
    $detalleResultado = $editando = $msg = null;
    list($numPreguntas, $puntajeMax) = obtenerDatosPreguntasOpciones($letras);
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        if (isset($_POST['eliminar']) && function_exists('eliminarResultado')) {
            if (!is_numeric($_POST['eliminar'])) {
                mostrarMensaje('ID inválido para eliminar','error');
            } else {
                $msg = eliminarResultado((int)$_POST['eliminar']) ? 'Resultado eliminado correctamente.' : 'Error al eliminar el resultado.';
                header('Location: Estadisticas.php?msg='.urlencode($msg)); exit;
            }
        }
        if (isset($_POST['guardar_edicion']) && function_exists('actualizarResultado')) {
            $idEdit = (int)$_POST['id_edit'];
            $puntajes = [];
            $valido = true;
            foreach ($letras as $letra) {
                $valor = $_POST['puntaje_'.$letra];
                if (!is_numeric($valor) || $valor < 0 || $valor > 100) $valido = false;
                $puntajes[] = (int)$valor;
            }
            if (!$valido) {
                mostrarMensaje('Puntajes inválidos. Deben ser números entre 0 y 100','error');
            } else {
                $msg = actualizarResultado($idEdit, ...$puntajes) ? 'Resultado actualizado correctamente.' : 'Error al actualizar el resultado.';
                header('Location: Estadisticas.php?msg='.urlencode($msg)); exit;
            }
        }
    }

    // Manejo de vistas individuales
    if (isset($_GET['ver'])) $detalleResultado = current(array_filter($resultados, fn($res)=>$res['id_resultado']==(int)$_GET['ver']));
    if (isset($_GET['editar'])) {$detalleResultado = current(array_filter($resultados, fn($res)=>$res['id_resultado']==(int)$_GET['editar'])); $editando=true;}
} catch (Exception $e) {
    mostrarMensaje('Error al cargar resultados: '.$e->getMessage(),'error');
    exit;
}
?>

<!-- PARTE HTML -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas</title>
</head>
<body>
    <div id="user-menu" style="text-align:right;margin:1em;"></div>
    <main>
        <!--Mostrar mensaje si viene por GET -->
        <?php if (isset($_GET['msg']) && $_GET['msg']): ?>
            <p><strong><?= htmlspecialchars($_GET['msg']) ?></strong></p>
        <?php endif; ?>

        <!-- ==================== ESTADÍSTICAS GENERALES ===================== -->
        <h1>Estadísticas de Resultados RIASEC</h1>
        <section>
            <h2>Estadísticas por letra</h2>
            <table border="1">
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
                <?php foreach ($letras as $letra): ?>
                    <tr>
                        <td><?= $letra ?></td>
                        <td><?= $numPreguntas[$letra] ?></td>
                        <td><?= $puntajeMax[$letra] ?></td>
                        <td><?= $estadisticas[$letra]['promedio'] ?></td>
                        <td><?= $estadisticas[$letra]['mediana'] ?></td>
                        <td><?= $estadisticas[$letra]['desv'] ?></td>
                        <td><?= $estadisticas[$letra]['porcDominancia'] ?>%</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- ==================== EXPLICACIONES DE CADA LETRA ===================== -->
        <section>
            <h2>Explicaciones de cada letra</h2>
            <ul>
            <?php foreach ($letras as $letra): ?>
                <li><strong><?= $letra ?>:</strong> <?= $explicaciones[$letra] ?></li>
            <?php endforeach; ?>
            </ul>
        </section>

        <!-- ==================== RESULTADOS INDIVIDUALES ===================== -->
        <section>
            <h2>Resultados individuales</h2>
            <form method="get" style="margin-bottom:1em;">
                <label for="ordenar">Ordenar por:</label>
                <select name="ordenar" id="ordenar">
                    <?php foreach ([
                        'fecha'=>'Fecha',
                        'puntaje_R'=>'R',
                        'puntaje_I'=>'I',
                        'puntaje_A'=>'A',
                        'puntaje_S'=>'S',
                        'puntaje_E'=>'E',
                        'puntaje_C'=>'C'] as $key=>$label): ?>
                        <option value="<?= $key ?>" <?= (isset($_GET['ordenar']) && $_GET['ordenar']===$key) ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Filtrar</button>
            </form>

            <!-- Vista detallada o edición de un resultado -->
            <?php
            if ($detalleResultado && !$editando):
                $puntajes = array_combine($letras, array_map(fn($l)=>$detalleResultado['puntaje_'.$l]??0, $letras));
                arsort($puntajes);
                $dominantes = array_slice(array_keys($puntajes),0,3);
                $perfil = "El perfil vocacional dominante es: <strong>".implode(", ",$dominantes)."</strong>.<br>";
                $perfil .= "Esto significa que la persona tiene una combinación de intereses y habilidades en los siguientes ámbitos:<br><ul>";
                foreach ($dominantes as $letra) $perfil .= "<li><strong>$letra</strong>: {$explicaciones[$letra]}</li>";
                $perfil .= "</ul>Personas con este perfil suelen destacar en áreas donde se combinan estas características. Se recomienda explorar carreras y ocupaciones que integren estos intereses para potenciar el desarrollo profesional y personal.";
            ?>

            <!-- Detalle del resultado seleccionado -->
            <h3>Puntajes totales de esta prueba (ordenados):</h3>
            <ul>
                <?php foreach ($puntajes as $letra => $valor): ?>
                    <li><strong><?= $letra ?></strong>: <?= $valor ?> - <?= $explicaciones[$letra] ?></li>
                <?php endforeach; ?>
            </ul>
            <h3>3 letras dominantes:</h3>
            <p>
                <?php foreach ($dominantes as $letra): ?>
                    <span style="font-weight:bold;"><?= $letra ?></span> <?= $explicaciones[$letra] ?><br>
                <?php endforeach; ?>
            </p>
            <h3>Perfil narrativo:</h3>
            <div><?= $perfil ?></div>
            <form action="Estadisticas.php" method="get" style="margin-top:2em;">
                <button type="submit">Volver a la lista</button>
            </form>

            <!-- Edición del resultado seleccionado -->
            <?php elseif ($editando && $detalleResultado): ?>
                <h3>Editar resultado</h3>
                <form method="post" action="Estadisticas.php">
                    <input type="hidden" name="id_edit" value="<?= $detalleResultado['id_resultado'] ?>">
                    <?php foreach ($letras as $letra): ?>
                        <label><?= $letra ?>: <input type="number" name="puntaje_<?= $letra ?>" value="<?= $detalleResultado['puntaje_'.$letra] ?>" min="0" max="100"></label><br>
                    <?php endforeach; ?>
                    <button type="submit" name="guardar_edicion">Guardar</button>
                    <a href="Estadisticas.php">Cancelar</a>
                </form>
            <?php else:
                if ($resultados):
                    $ordenar = $_GET['ordenar'] ?? 'fecha';
                    usort($resultados, fn($a,$b)=> $ordenar==='fecha' ? strtotime($b['fecha'])-strtotime($a['fecha']) : $b[$ordenar]<=>$a[$ordenar]);
            ?>

            <!-- Tabla de todos los resultados individuales -->
            <table border="1" aria-label="Resultados individuales">
                <thead><tr><th>ID</th><th>Usuario</th><th>Fecha</th><?php foreach ($letras as $letra): ?><th><?= $letra ?></th><?php endforeach; ?><th>Acciones</th></tr></thead>
                    <tbody>
                        <?php foreach ($resultados as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['id_resultado']) ?></td>
                                <td><?= htmlspecialchars($r['id_usuario']) ?></td>
                                <td><?= htmlspecialchars($r['fecha']) ?></td>
                                <?php foreach ($letras as $letra): ?><td><?= htmlspecialchars($r['puntaje_'.$letra]) ?></td><?php endforeach; ?>
                                <td>
                                    <form method="get" action="Estadisticas.php" style="display:inline;"><input type="hidden" name="ver" value="<?= $r['id_resultado'] ?>"><button type="submit">Ver</button></form>
                                    <form method="get" action="Estadisticas.php" style="display:inline;"><input type="hidden" name="editar" value="<?= $r['id_resultado'] ?>"><button type="submit">Editar</button></form>
                                    <form method="post" action="Estadisticas.php" style="display:inline;" onsubmit="return confirm('¿Eliminar este resultado?');"><input type="hidden" name="eliminar" value="<?= $r['id_resultado'] ?>"><button type="submit">Eliminar</button></form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
            </table>
            <?php else: ?><p>No hay resultados individuales registrados.</p><?php endif; endif; ?>
        </section>

        <!-- ==================== VOLVER A OPCIONES ===================== -->
        <form action="Opciones.php" method="get" style="margin-top:2em;">
            <button type="submit">Volver a Opciones</button>
        </form>

    </main>
    <script src="/RIASEC/JAVASCRIPT/login.js"></script>
</body>
</html>