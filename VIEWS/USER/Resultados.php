<?php
// Vista de resultados de la prueba RIASEC
require_once __DIR__ . '/../../PHP/crud.php';
session_start();
$preguntas = [];
$opciones = [];
$mapaOpciones = [];
$resultadosUsuario = null;
$usuarioRegistrado = false;

try {
    $preguntas = obtenerPreguntas();
    $opciones = obtenerOpciones();
    foreach ($opciones as $o) {
        $mapaOpciones[$o['valor']] = $o['descripcion'];
    }

    // Verificar si el usuario está registrado (por ejemplo, si hay id_usuario en sesión)
    if (isset($_SESSION['id_usuario'])) {
        $usuarioRegistrado = true;
        // Obtener todos los resultados del usuario
        $id_usuario = $_SESSION['id_usuario'];
        $todosResultados = obtenerResultados();
        $resultadosUsuario = [];
        foreach ($todosResultados as $res) {
            if ($res['id_usuario'] == $id_usuario) {
                $resultadosUsuario[] = $res;
            }
        }
    }



} catch (Exception $e) {
    die('<h2>Error al cargar preguntas u opciones: ' . htmlspecialchars($e->getMessage()) . '</h2>');
}
?>
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
        <?php if ($usuarioRegistrado): ?>
            <h2>Historial de resultados guardados</h2>
            <?php if (empty($resultadosUsuario)): ?>
                <p>No tienes resultados guardados.</p>
            <?php else: ?>
                <table border="1" aria-label="Resultados guardados">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>R</th><th>I</th><th>A</th><th>S</th><th>E</th><th>C</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($resultadosUsuario as $res): ?>
                        <tr>
                            <td><?= htmlspecialchars($res['fecha']) ?></td>
                            <td><?= htmlspecialchars($res['puntaje_R']) ?></td>
                            <td><?= htmlspecialchars($res['puntaje_I']) ?></td>
                            <td><?= htmlspecialchars($res['puntaje_A']) ?></td>
                            <td><?= htmlspecialchars($res['puntaje_S']) ?></td>
                            <td><?= htmlspecialchars($res['puntaje_E']) ?></td>
                            <td><?= htmlspecialchars($res['puntaje_C']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <hr>
            <h2>Resultados de la prueba actual</h2>
        <?php endif; ?>

        <?php if (!empty($_SESSION['respuestas_riasec'])): ?>
            <?php
            // Calcular puntajes totales de la prueba actual
            $puntajes = ['R'=>0,'I'=>0,'A'=>0,'S'=>0,'E'=>0,'C'=>0];
            foreach ($preguntas as $p) {
                $pid = 'pregunta_' . $p['id_pregunta'];
                if (isset($_SESSION['respuestas_riasec'][$pid]) && isset($puntajes[$p['categoria']])) {
                    $puntajes[$p['categoria']] += (int)$_SESSION['respuestas_riasec'][$pid];
                }
            }
            ?>
            <h3>Puntajes totales de esta prueba:</h3>
            <ul>
                <li>R: <?= $puntajes['R'] ?></li>
                <li>I: <?= $puntajes['I'] ?></li>
                <li>A: <?= $puntajes['A'] ?></li>
                <li>S: <?= $puntajes['S'] ?></li>
                <li>E: <?= $puntajes['E'] ?></li>
                <li>C: <?= $puntajes['C'] ?></li>
            </ul>
            <?php unset($_SESSION['respuestas_riasec']); ?>
        <?php else: ?>
            <p>No se recibieron respuestas. <a href="../../index.php">Volver a la prueba</a></p>
        <?php endif; ?>
        <form action="../../index.php" method="get" style="margin-top:2em;">
            <button type="submit">Volver al inicio</button>
        </form>
    </main>
    <script src="/RIASEC/JAVASCRIPT/login.js"></script>
</body>
</html>