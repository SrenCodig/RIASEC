
<?php
// Preguntas.php - Vista de gestión de preguntas (solo admin)
require_once '../../PHP/crud.php';
session_start();
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    die('<h2>Acceso denegado. Solo administradores.</h2>');
}

$msg = '';
// Agregar o editar pregunta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texto = trim($_POST['texto'] ?? '');
    $categoria = $_POST['categoria'] ?? '';
    $id_edit = $_POST['id_edit'] ?? '';
    if ($texto && $categoria) {
        try {
            if ($id_edit) {
                if (actualizarPregunta($id_edit, $texto, $categoria)) {
                    $msg = 'Pregunta actualizada correctamente.';
                } else {
                    $msg = 'Error al actualizar la pregunta.';
                }
            } else {
                if (crearPregunta($texto, $categoria)) {
                    $msg = 'Pregunta agregada correctamente.';
                } else {
                    $msg = 'Error al agregar la pregunta.';
                }
            }
        } catch (Exception $e) {
            $msg = 'Error: ' . htmlspecialchars($e->getMessage());
        }
    } else {
        $msg = 'Faltan datos.';
    }
}
// Eliminar pregunta
if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    if (eliminarPregunta($id_eliminar)) {
        $msg = 'Pregunta eliminada correctamente.';
    } else {
        $msg = 'Error al eliminar la pregunta.';
    }
}

$preguntas = obtenerPreguntas();
// Agrupar por categoría
$porCategoria = ['R'=>[], 'I'=>[], 'A'=>[], 'S'=>[], 'E'=>[], 'C'=>[]];
foreach ($preguntas as $p) {
    $porCategoria[$p['categoria']][] = $p;
}
// Opciones para puntaje máximo
$opciones = obtenerOpciones();
$maxValor = 0;
foreach ($opciones as $o) {
    if ($o['valor'] > $maxValor) $maxValor = $o['valor'];
}
// Si se va a editar
$editando = false;
$preguntaEdit = null;
if (isset($_GET['editar'])) {
    $id_edit = $_GET['editar'];
    foreach ($preguntas as $p) {
        if ($p['id_pregunta'] == $id_edit) {
            $editando = true;
            $preguntaEdit = $p;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Preguntas</title>
</head>
<body>
    <div id="user-menu" style="text-align:right;margin:1em;"></div>
    <main>
        <h1>Gestión de Preguntas</h1>
        <?php if ($msg): ?><p><strong><?= htmlspecialchars($msg) ?></strong></p><?php endif; ?>
        <form method="post" style="margin-bottom:2em;">
            <fieldset>
                <legend><?= $editando ? 'Editar pregunta' : 'Agregar nueva pregunta' ?></legend>
                <input type="hidden" name="id_edit" value="<?= $editando ? htmlspecialchars($preguntaEdit['id_pregunta']) : '' ?>">
                <label for="texto">Texto de la pregunta:</label><br>
                <input type="text" id="texto" name="texto" maxlength="255" required value="<?= $editando ? htmlspecialchars($preguntaEdit['texto']) : '' ?>"><br>
                <label for="categoria">Categoría:</label>
                <select id="categoria" name="categoria" required>
                    <option value="">Seleccione...</option>
                    <?php foreach (['R','I','A','S','E','C'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= $editando && $preguntaEdit['categoria']==$cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select><br>
                <button type="submit"><?= $editando ? 'Actualizar' : 'Agregar' ?></button>
                <?php if ($editando): ?>
                    <a href="Preguntas.php">Cancelar</a>
                <?php endif; ?>
            </fieldset>
        </form>
        <h2>Preguntas por categoría</h2>
        <?php foreach ($porCategoria as $cat => $pregs): ?>
            <section style="margin-bottom:1.5em;">
                <h3>Categoría <?= $cat ?> (<?= count($pregs) ?> preguntas)</h3>
                <?php if (empty($pregs)): ?>
                    <p>No hay preguntas en esta categoría.</p>
                <?php else: ?>
                <table border="1" aria-label="Preguntas <?= $cat ?>">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Texto</th>
                            <th>Puntaje máximo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pregs as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['id_pregunta']) ?></td>
                            <td><?= htmlspecialchars($p['texto']) ?></td>
                            <td><?= $maxValor ?></td>
                            <td>
                                <a href="Preguntas.php?editar=<?= $p['id_pregunta'] ?>">Editar</a> |
                                <a href="Preguntas.php?eliminar=<?= $p['id_pregunta'] ?>" onclick="return confirm('¿Seguro que deseas eliminar esta pregunta?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
        <form action="Opciones.php" method="get" style="margin-top:2em;">
            <button type="submit">Volver a Opciones</button>
        </form>
    </main>
    <script src="/RIASEC/JAVASCRIPT/login.js"></script>
</body>
</html>
