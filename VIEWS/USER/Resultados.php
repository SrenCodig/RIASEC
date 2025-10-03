<!--
 * Vista de resultados de la prueba RIASEC.
 *
 * Este archivo muestra los resultados de la prueba vocacional RIASEC para el usuario actual.
 * - Si el usuario está autenticado, se muestra su historial de pruebas y el resultado más reciente.
 * - Si el usuario no está registrado, se muestra solo el resultado de la prueba actual (puntajes en sesión).
 *
 * Funcionalidades principales:
 * - Obtención de resultados desde la base de datos usando funciones de acceso a datos.
 * - Visualización de puntajes por cada categoría RIASEC (R, I, A, S, E, C).
 * - Presentación de las tres letras dominantes y su explicación.
 * - Perfil narrativo personalizado según los intereses vocacionales del usuario.
 *
 * Variables principales:
 * - $resultadosUsuario: array con el historial de resultados del usuario.
 * - $usuarioRegistrado: booleano que indica si el usuario está autenticado.
 * - $resultadoActual: array con los puntajes de la prueba más reciente o actual.
 * - $explicaciones: array asociativo con la descripción de cada letra RIASEC.
 * - $mostrarDetalle, $detalleResultado: controlan la visualización de detalles de pruebas pasadas.
 *
 * Requiere:
 * - Funciones de acceso a datos definidas en 'crud.php'.
 * - Sesión iniciada para identificar al usuario y sus respuestas.
 *
 * Salida:
 * - HTML con tablas, listas y perfil narrativo de resultados.
 * - Botón para volver al inicio.
-->


<!-- PARTE PHP -->

<?php
// ---------------------------------------------
// Vista de resultados de la prueba RIASEC
// ---------------------------------------------

// Incluimos funciones de acceso a datos y sesión
require_once __DIR__ . '/../../PHP/crud.php';
session_start();

// Inicializamos el historial y el estado de usuario

$resultadosUsuario = [];
$usuarioRegistrado = false;
$resultadoActual = null;

// Explicaciones de cada letra RIASEC (para mostrar el perfil)
$explicaciones = [
    'R' => 'Realista: Prefieres actividades prácticas, trabajo físico y el uso de herramientas o maquinaria.',
    'I' => 'Investigador: Te atraen las actividades analíticas, científicas y el aprendizaje intelectual.',
    'A' => 'Artístico: Disfrutas de la creatividad, el arte, la música y la autoexpresión.',
    'S' => 'Social: Te gusta ayudar, enseñar, orientar y trabajar con personas.',
    'E' => 'Emprendedor: Prefieres liderar, persuadir, vender y asumir riesgos.',
    'C' => 'Convencional: Te atraen las tareas organizativas, administrativas y el trabajo con datos.'
];


try {
    // Si el usuario está autenticado, obtenemos su historial de resultados
    if (isset($_SESSION['id_usuario'])) {
        $usuarioRegistrado = true;
        $id_usuario = $_SESSION['id_usuario'];
        $todosResultados = obtenerResultados();
        // Filtramos solo los resultados del usuario actual
        foreach ($todosResultados as $res) {
            if ($res['id_usuario'] == $id_usuario) {
                $resultadosUsuario[] = $res;
            }
        }
        // El resultado actual es el más reciente del usuario
        if (!empty($resultadosUsuario)) {
            // Ordenar por fecha descendente y tomar el primero
            usort($resultadosUsuario, function($a, $b) {
                return strtotime($b['fecha']) - strtotime($a['fecha']);
            });
            $resultadoActual = $resultadosUsuario[0];
        }
    } else {
        // Usuario no registrado: mostrar solo el resultado de la prueba actual
        // Si se acaba de hacer la prueba, los puntajes están en $_SESSION['respuestas_riasec']
        if (isset($_SESSION['respuestas_riasec'])) {
            // Simular resultado actual con los puntajes de la sesión
            $respuestas = $_SESSION['respuestas_riasec'];
            // Obtener preguntas para mapear categorías
            $preguntas = obtenerPreguntas();
            $puntajes = ['R'=>0,'I'=>0,'A'=>0,'S'=>0,'E'=>0,'C'=>0];
            foreach ($preguntas as $p) {
                $pid = 'pregunta_' . $p['id_pregunta'];
                if (isset($respuestas[$pid]) && isset($puntajes[$p['categoria']])) {
                    $puntajes[$p['categoria']] += (int)$respuestas[$pid];
                }
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
    }
} catch (Exception $e) {
    // Si hay error al cargar resultados, mostramos mensaje
    die('<h2>Error al cargar resultados: ' . htmlspecialchars($e->getMessage()) . '</h2>');
}


// Si el usuario seleccionó una prueba pasada, buscamos el detalle
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
</head>
<body>
    <div id="user-menu" style="text-align:right;margin:1em;"></div>
    <main>
        <h1>Resultados de tu Prueba</h1>
        <!-- Si el usuario está registrado, mostramos su historial -->
        <?php if ($usuarioRegistrado): ?>
            <!-- Historial de pruebas del usuario -->
            <h2>Historial de pruebas realizadas</h2>
            <?php if (empty($resultadosUsuario)): ?>
                <!-- Si no hay pruebas guardadas -->
                <p>No tienes pruebas guardadas.</p>
            <?php else: ?>
                <!-- Tabla con todas las pruebas del usuario -->
                <table border="1" aria-label="Historial de pruebas">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>R</th><th>I</th><th>A</th><th>S</th><th>E</th><th>C</th>
                            <th>Ver detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($resultadosUsuario as $res): ?>
                        <tr>
                            <!-- Mostramos fecha y puntajes -->
                            <td><?= htmlspecialchars($res['fecha']) ?></td>
                            <td><?= htmlspecialchars($res['puntaje_R']) ?></td>
                            <td><?= htmlspecialchars($res['puntaje_I']) ?></td>
                            <td><?= htmlspecialchars($res['puntaje_A']) ?></td>
                            <td><?= htmlspecialchars($res['puntaje_S']) ?></td>
                            <td><?= htmlspecialchars($res['puntaje_E']) ?></td>
                            <td><?= htmlspecialchars($res['puntaje_C']) ?></td>
                            <td>
                                <!-- Botón para ver el detalle de la prueba -->
                                <form method="get" style="display:inline;">
                                    <input type="hidden" name="id_resultado" value="<?= $res['id_resultado'] ?>">
                                    <button type="submit">Ver</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <hr>
            <h2>Resultado de tu prueba más reciente</h2>
        <?php else: ?>
            <h2>Resultado de tu prueba</h2>
        <?php endif; ?>

        <?php
        // ---------------------------------------------
        // Mostrar detalle de prueba seleccionada o resultado actual
        // ---------------------------------------------
        $resultadoParaMostrar = null;
        if ($mostrarDetalle && $detalleResultado) {
            $resultadoParaMostrar = $detalleResultado;
        } elseif ($resultadoActual) {
            $resultadoParaMostrar = $resultadoActual;
        }

        if ($resultadoParaMostrar) {
            // Extraemos los puntajes de la prueba seleccionada o actual
            $puntajes = [
                'R' => $resultadoParaMostrar['puntaje_R'],
                'I' => $resultadoParaMostrar['puntaje_I'],
                'A' => $resultadoParaMostrar['puntaje_A'],
                'S' => $resultadoParaMostrar['puntaje_S'],
                'E' => $resultadoParaMostrar['puntaje_E'],
                'C' => $resultadoParaMostrar['puntaje_C']
            ];
            // Ordenamos los puntajes de mayor a menor
            arsort($puntajes);
            // Obtenemos las 3 letras dominantes
            $dominantes = array_slice(array_keys($puntajes), 0, 3);
            // Armamos el perfil narrativo
            $perfil = "Tu perfil vocacional dominante es: <strong>" . implode(", ", $dominantes) . "</strong>.<br>";
            $perfil .= "Esto significa que tienes una combinación de intereses y habilidades en los siguientes ámbitos:<br><ul>";
            foreach ($dominantes as $letra) {
                $perfil .= "<li><strong>$letra</strong>: " . $explicaciones[$letra] . "</li>";
            }
            $perfil .= "</ul>";
            $perfil .= "Personas con este perfil suelen destacar en áreas donde se combinan estas características. Te recomendamos explorar carreras y ocupaciones que integren estos intereses para potenciar tu desarrollo profesional y personal.";
        ?>
            <!-- Mostramos los puntajes y el perfil narrativo -->
            <h3>Puntajes totales de esta prueba (ordenados):</h3>
            <ul>
                <?php foreach ($puntajes as $letra => $valor): ?>
                    <li><strong><?= $letra ?></strong>: <?= $valor ?> - <?= $explicaciones[$letra] ?></li>
                <?php endforeach; ?>
            </ul>
            <h3>Tus 3 letras dominantes:</h3>
            <p>
                <?php foreach ($dominantes as $letra): ?>
                    <span style="font-weight:bold;"><?= $letra ?></span>
                    <?= $explicaciones[$letra] ?><br>
                <?php endforeach; ?>
            </p>
            <h3>Perfil narrativo:</h3>
            <div style="background:#f4f4f4;padding:1em;border-radius:8px;">
                <?= $perfil ?>
            </div>
        <?php
        } else {
            // Si no hay resultado para mostrar
            ?>
            <p>No se recibieron respuestas.</p>
        <?php } ?>
        <form action="../../index.php" method="get" style="margin-top:2em;">
            <button type="submit">Volver al inicio</button>
        </form>
    </main>
    <script src="/RIASEC/JAVASCRIPT/login.js"></script>
</body>
</html>