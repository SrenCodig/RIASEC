<?php
// Página principal del test vocacional RIASEC
require_once __DIR__ . '/PHP/crud.php';
session_start();

// Procesar formulario y guardar resultado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/PHP/crud.php';
    $preguntas = obtenerPreguntas();
    $puntajes = ['R'=>0,'I'=>0,'A'=>0,'S'=>0,'E'=>0,'C'=>0];
    foreach ($preguntas as $p) {
        $pid = 'pregunta_' . $p['id_pregunta'];
        if (isset($_POST[$pid]) && isset($puntajes[$p['categoria']])) {
            $puntajes[$p['categoria']] += (int)$_POST[$pid];
        }
    }
    $id_usuario = (isset($_SESSION['id_usuario'])) ? $_SESSION['id_usuario'] : null;
    crearResultado($id_usuario, $puntajes['R'], $puntajes['I'], $puntajes['A'], $puntajes['S'], $puntajes['E'], $puntajes['C']);
    // Guardar respuestas para mostrar en Resultados.php
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
        <?php
        // Mostrar botón de admin si corresponde
        if (isset($_SESSION['id_usuario']) && isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
            echo '<form action="VIEWS/ADMIN/Opciones.php" method="get" style="margin-bottom:1em;">';
            echo '<button type="submit">Ir a Opciones (Admin)</button>';
            echo '</form>';
        }
        ?>
        <?php if (empty($preguntas) || empty($opciones)): ?>
            <p style="color:red;">No hay preguntas u opciones disponibles. Contacte al administrador.</p>
        <?php else: ?>
    <form method="post" action="index.php" aria-label="Formulario de test RIASEC">
            <?php foreach ($preguntas as $p): ?>
                <fieldset style="margin-bottom:1em;">
                    <legend><strong><?= htmlspecialchars($p['texto']) ?></strong></legend>
                    <?php foreach ($opciones as $o): ?>
                        <label>
                            <input type="radio" name="pregunta_<?= $p['id_pregunta'] ?>" value="<?= $o['valor'] ?>" required>
                            <?= htmlspecialchars($o['descripcion']) ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>
            <button type="submit">Enviar respuestas</button>
        </form>
        <?php endif; ?>
    </main>
    <script src="/RIASEC/JAVASCRIPT/login.js"></script>
</body>
</html>