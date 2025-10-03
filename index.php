<!-- 
 * Página principal del test vocacional RIASEC.
 *
 * Este archivo gestiona la visualización y procesamiento del formulario del test RIASEC.
 * 
 * Funcionalidades principales:
 * - Obtiene preguntas y opciones desde la base de datos usando funciones de PHP/crud.php.
 * - Procesa el formulario enviado por el usuario, calcula los puntajes por categoría (R, I, A, S, E, C)
 *   y guarda el resultado en la base de datos.
 * - Guarda las respuestas en la sesión para mostrar resultados posteriormente.
 * - Redirige al usuario a la página de resultados tras enviar el formulario.
 * - Muestra opciones administrativas si el usuario tiene rol de administrador.
 * - Permite al usuario ver su historial de pruebas si está autenticado.
 * - Muestra un mensaje de error si no hay preguntas u opciones disponibles.
 *
 * Requiere:
 * - Sesión iniciada.
 * - Funciones obtenerPreguntas(), obtenerOpciones(), crearResultado() definidas en PHP/crud.php.
 *
 * Estructura HTML:
 * - Encabezado y título del test.
 * - Formulario dinámico con preguntas y opciones.
 * - Botones para administración y ver historial según el rol del usuario.
 * - Inclusión de script de login.
-->

<!-- PARTE PHP -->

<?php
// Página principal del test vocacional RIASEC
require_once __DIR__ . '/PHP/crud.php';
session_start();

// Procesar formulario y guardar resultado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // obetenemos las preguntas para calcular puntajes
    require_once __DIR__ . '/PHP/crud.php';
    $preguntas = obtenerPreguntas();
    $puntajes = ['R'=>0,'I'=>0,'A'=>0,'S'=>0,'E'=>0,'C'=>0];
    // Calcular puntajes por categoría
    foreach ($preguntas as $p) {
        $pid = 'pregunta_' . $p['id_pregunta'];
        if (isset($_POST[$pid]) && isset($puntajes[$p['categoria']])) {
            $puntajes[$p['categoria']] += (int)$_POST[$pid];
        }
    }
    // Si el usuario no está registrado, id_usuario será null
    $id_usuario = (isset($_SESSION['id_usuario'])) ? $_SESSION['id_usuario'] : null;
    // Guardar respuestas para mostrar en Resultados.php
    crearResultado($id_usuario, $puntajes['R'], $puntajes['I'], $puntajes['A'], $puntajes['S'], $puntajes['E'], $puntajes['C']);
    $_SESSION['respuestas_riasec'] = $_POST;
    header('Location: VIEWS/USER/Resultados.php');
    exit;
}

// Obtener preguntas y opciones desde la base de datos
$preguntas = [];
$opciones = [];
try {
    $preguntas = obtenerPreguntas();
    $opciones = obtenerOpciones();
} catch (Exception $e) {
    die('<h2>Error al cargar preguntas u opciones: ' . htmlspecialchars($e->getMessage()) . '</h2>');
}
?>

<!-- PARTE HTML -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba Vocacional RIASEC</title>
</head>
<body>
    <div id="user-menu" style="text-align:right;margin:1em;"></div>
    <main>
        <h1>Test Vocacional RIASEC</h1>

        <!-- Mostrar botón de admin si corresponde -->

        <?php
        // Botón de opciones admin (comentario PHP)
        if (isset($_SESSION['id_usuario']) && isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
            echo '<form action="VIEWS/ADMIN/Opciones.php" method="get" style="margin-bottom:1em;">';
            echo '<button type="submit">Ir a Opciones (Admin)</button>';
            echo '</form>';
        }
        ?>

        <!-- Botón para ver historial de pruebas solo si el usuario tiene pruebas realizadas -->
        <?php
        if (isset($_SESSION['id_usuario'])) {
            // Comprobar si el usuario tiene pruebas realizadas
            try {
                $id_usuario = $_SESSION['id_usuario'];
                $tienePruebas = false;
                $todosResultados = obtenerResultados();
                // Buscar si hay resultados para este usuario
                foreach ($todosResultados as $res) {
                    if ($res['id_usuario'] == $id_usuario) {
                        $tienePruebas = true;
                        break;
                    }
                }
                if ($tienePruebas) {
                    echo '<form action="VIEWS/USER/Resultados.php" method="get" style="margin-bottom:1em;">';
                    echo '<button type="submit" name="ver_historial" value="1">Ver pruebas pasadas</button>';
                    echo '</form>';
                }
            } catch (Exception $e) {
                // Si hay error, no mostrar el botón
            }
        }
        ?>

        <!-- Si no hay preguntas u opciones, mostrar mensaje de error -->

        <?php if (empty($preguntas) || empty($opciones)): ?>
            <!-- Mensaje de error si la base de datos no tiene preguntas u opciones -->
            <p style="color:red;">
                No hay preguntas u opciones disponibles. Contacte al administrador.
            </p>
        <?php else: ?>
            <!-- Formulario principal del test RIASEC -->
            <form method="post" action="index.php" aria-label="Formulario de test RIASEC">
                <?php foreach ($preguntas as $p): ?>
                    <!-- Cada pregunta se muestra en un fieldset para separar visualmente -->
                    <fieldset style="margin-bottom:1em;">
                        <!-- Título de la pregunta -->
                        <legend>
                            <strong><?= htmlspecialchars($p['texto']) ?></strong>
                        </legend>
                        <?php foreach ($opciones as $o): ?>
                            <!-- Opciones de respuesta como botones de radio -->
                            <label>
                                <input
                                    type="radio"
                                    name="pregunta_<?= $p['id_pregunta'] ?>"
                                    value="<?= $o['valor'] ?>"
                                    required
                                >
                                <?= htmlspecialchars($o['descripcion']) ?>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                <?php endforeach; ?>
                <!-- Botón para enviar todas las respuestas del test -->
                <button type="submit">Enviar respuestas</button>
            </form>
        <?php endif; ?>
        <!-- Fin del formulario -->

    </main>
    <script src="/RIASEC/JAVASCRIPT/login.js"></script>
</body>
</html>