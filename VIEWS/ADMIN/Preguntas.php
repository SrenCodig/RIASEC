<!--
 * Vista de administración para la gestión de preguntas y opciones de respuesta del test RIASEC.
 
 * Funcionalidades principales:
 * - Seguridad: Solo accesible para usuarios con rol de administrador.

* - Gestión de preguntas:
 *   - Listado de preguntas agrupadas por categoría RIASEC (R, I, A, S, E, C).
 *   - Formulario para agregar o editar preguntas (texto y categoría).
 *   - Eliminación de preguntas.

 * - Gestión de opciones de respuesta:
 *   - Listado de opciones con valor numérico y descripción.
 *   - Formulario para agregar o editar opciones (valor entre 0 y 100, descripción).
 *   - Validaciones para el valor y la descripción de la opción.
 *   - Eliminación de opciones.

 * - Mensajes de estado para informar sobre el éxito o error de las acciones realizadas.
 * - Navegación: Enlace para volver al menú de opciones administrativas.
 *
 * Dependencias:
 * - Requiere el archivo '../../PHP/crud.php' para las funciones CRUD de preguntas y opciones.
 * - Utiliza sesiones para controlar el acceso.
 *
 * Seguridad:
 * - Verifica que el usuario esté autenticado y tenga rol de administrador antes de mostrar la vista.
 * - Sanitiza las entradas y salidas para evitar vulnerabilidades XSS.
 *
 * Estructura:
 * - Bloque de opciones de respuesta (gestión y listado).
 * - Bloque de preguntas del test (gestión y listado por categoría).
 * - Mensajes de estado y navegación.
-->


<!-- PARTE PHP -->

<?php
// Medida de seguridad, Vista de gestión de preguntas (solo admin)
require_once '../../PHP/crud.php';
session_start();
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    die('<h2>Acceso denegado. Solo administradores.</h2>');
}

// Opciones de administración de preguntas
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
// Gestión de opciones de respuesta
$opciones = obtenerOpciones();
$maxValor = 0;
foreach ($opciones as $o) {
    if ($o['valor'] > $maxValor) $maxValor = $o['valor'];
}

// Variables para edición de opción
$editandoOpcion = false;
$opcionEdit = null;

// Agregar/editar/eliminar opción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Agregar o editar opción
    if (isset($_POST['accion_opcion'])) {
        $id_opcion_edit = $_POST['id_opcion_edit'] ?? '';
        // Validaciones para el valor
        $errorOpcion = '';
        if ($descripcion === '') {
            $errorOpcion = 'La descripción no puede estar vacía.';
        } elseif (!is_numeric($_POST['valor'])) {
            $errorOpcion = 'El valor debe ser un número.';
        } elseif ($valor < 0) {
            $errorOpcion = 'El valor no puede ser negativo.';
        } elseif ($valor > 100) {
            $errorOpcion = 'El valor máximo permitido es 100.';
        } elseif (strlen((string)$valor) > 3) {
            $errorOpcion = 'El valor debe tener máximo 3 dígitos.';
        }
        if ($errorOpcion) {
            $msg = $errorOpcion;
        } else {
            if ($id_opcion_edit) {
                if (actualizarOpcion($id_opcion_edit, $valor, $descripcion)) {
                    $msg = 'Opción actualizada correctamente.';
                } else {
                    $msg = 'Error al actualizar la opción.';
                }
            } else {
                if (crearOpcion($valor, $descripcion)) {
                    $msg = 'Opción agregada correctamente.';
                } else {
                    $msg = 'Error al agregar la opción.';
                }
            }
        }
    }
}
// Eliminar opción
if (isset($_GET['eliminar_opcion'])) {
    $id_eliminar_opcion = $_GET['eliminar_opcion'];
    if (eliminarOpcion($id_eliminar_opcion)) {
        $msg = 'Opción eliminada correctamente.';
    } else {
        $msg = 'Error al eliminar la opción.';
    }
}
// Si se va a editar opción
if (isset($_GET['editar_opcion'])) {
    $id_edit_opcion = $_GET['editar_opcion'];
    foreach ($opciones as $o) {
        if ($o['id_opcion'] == $id_edit_opcion) {
            $editandoOpcion = true;
            $opcionEdit = $o;
            break;
        }
    }
}
// Recargar opciones después de cambios
$opciones = obtenerOpciones();
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

<!-- PARTE HTML -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Preguntas</title>
</head>
<body>
    <div id="user-menu" style="text-align:right;margin:1em;"></div>

    <main>
        <!-- ===================== BLOQUE: OPCIONES DE RESPUESTA ===================== -->
        <!-- Este bloque permite al administrador gestionar las opciones de respuesta que aparecen en el test. -->
        <h2>Opciones de respuesta</h2>
        <!-- Formulario para agregar o editar una opción de respuesta. -->
        <form method="post" style="margin-bottom:2em;">
            <fieldset>
                <legend><?= $editandoOpcion ? 'Editar opción' : 'Agregar nueva opción' ?></legend>
                <!-- ID oculto para edición de opción -->
                <input type="hidden" name="id_opcion_edit" value="<?= $editandoOpcion ? htmlspecialchars($opcionEdit['id_opcion']) : '' ?>">
                <!-- Bandera para distinguir acción de opción -->
                <input type="hidden" name="accion_opcion" value="1">
                <!-- Campo para el valor numérico de la opción (0-100, solo números, máx 3 dígitos) -->
                <label for="valor">Valor (0-100):</label>
                <input type="number" id="valor" name="valor" min="0" max="100" maxlength="3" placeholder="0-100" required pattern="^[0-9]{1,3}$" value="<?= $editandoOpcion ? htmlspecialchars($opcionEdit['valor']) : '' ?>">
                <br>
                <!-- Campo para la descripción textual de la opción -->
                <label for="descripcion">Descripción:</label>
                <input type="text" id="descripcion" name="descripcion" maxlength="100" required value="<?= $editandoOpcion ? htmlspecialchars($opcionEdit['descripcion']) : '' ?>">
                <br>
                <!-- Botón para guardar la opción -->
                <button type="submit"><?= $editandoOpcion ? 'Actualizar' : 'Agregar' ?></button>
                <!-- Enlace para cancelar la edición de opción -->
                <?php if ($editandoOpcion): ?>
                    <a href="Preguntas.php">Cancelar</a>
                <?php endif; ?>
            </fieldset>
        </form>
        <!-- Tabla que muestra todas las opciones de respuesta existentes -->
        <table border="1" aria-label="Opciones de respuesta" style="margin-bottom:2em;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Valor</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($opciones)): ?>
                    <tr><td colspan="4">No hay opciones registradas.</td></tr>
                <?php else: ?>
                    <?php foreach ($opciones as $o): ?>
                        <tr>
                            <td><?= htmlspecialchars($o['id_opcion']) ?></td>
                            <td><?= htmlspecialchars($o['valor']) ?></td>
                            <td><?= htmlspecialchars($o['descripcion']) ?></td>
                            <td>
                                <!-- Enlaces para editar o eliminar la opción -->
                                <a href="Preguntas.php?editar_opcion=<?= $o['id_opcion'] ?>">Editar</a>
                                |
                                <a href="Preguntas.php?eliminar_opcion=<?= $o['id_opcion'] ?>" onclick="return confirm('¿Seguro que deseas eliminar esta opción?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- ===================== BLOQUE: PREGUNTAS DEL TEST ===================== -->
        <!-- Este bloque permite al administrador gestionar las preguntas del test vocacional. -->
        <h1>Gestión de Preguntas</h1>

        <!-- Mensaje de estado (éxito o error) para cualquier acción -->
        <?php if ($msg): ?>
            <p><strong><?= htmlspecialchars($msg) ?></strong></p>
        <?php endif; ?>

        <!-- Formulario para agregar o editar una pregunta del test. -->
        <form method="post" style="margin-bottom:2em;">
            <fieldset>
                <legend><?= $editando ? 'Editar pregunta' : 'Agregar nueva pregunta' ?></legend>
                <!-- Campo oculto para el ID de la pregunta al editar -->
                <input type="hidden" name="id_edit" value="<?= $editando ? htmlspecialchars($preguntaEdit['id_pregunta']) : '' ?>">
                <!-- Campo de texto para el enunciado de la pregunta -->
                <label for="texto">Texto de la pregunta:</label><br>
                <input type="text" id="texto" name="texto" maxlength="255" required value="<?= $editando ? htmlspecialchars($preguntaEdit['texto']) : '' ?>"><br>
                <!-- Selector de categoría RIASEC -->
                <label for="categoria">Categoría:</label>
                <select id="categoria" name="categoria" required>
                    <option value="">Seleccione...</option>
                    <?php foreach (['R','I','A','S','E','C'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= $editando && $preguntaEdit['categoria']==$cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select><br>
                <!-- Botón para enviar el formulario de pregunta -->
                <button type="submit"><?= $editando ? 'Actualizar' : 'Agregar' ?></button>
                <!-- Enlace para cancelar la edición de pregunta -->
                <?php if ($editando): ?>
                    <a href="Preguntas.php">Cancelar</a>
                <?php endif; ?>
            </fieldset>
        </form>

        <!-- Sección que agrupa las preguntas por categoría RIASEC -->
        <h2>Preguntas por categoría</h2>
        <?php foreach ($porCategoria as $cat => $pregs): ?>
            <section style="margin-bottom:1.5em;">
                <!-- Título de la categoría y cantidad de preguntas -->
                <h3>Categoría <?= $cat ?> (<?= count($pregs) ?> preguntas)</h3>
                <!-- Si no hay preguntas en la categoría -->
                <?php if (empty($pregs)): ?>
                    <p>No hay preguntas en esta categoría.</p>
                <?php else: ?>
                    <!-- Tabla de preguntas de la categoría -->
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
                            <!-- Iterar sobre cada pregunta de la categoría -->
                            <?php foreach ($pregs as $p): ?>
                                <tr>
                                    <!-- ID de la pregunta -->
                                    <td><?= htmlspecialchars($p['id_pregunta']) ?></td>
                                    <!-- Texto de la pregunta -->
                                    <td><?= htmlspecialchars($p['texto']) ?></td>
                                    <!-- Puntaje máximo posible para la pregunta -->
                                    <td><?= $maxValor ?></td>
                                    <!-- Acciones disponibles: editar o eliminar la pregunta -->
                                    <td>
                                        <a href="Preguntas.php?editar=<?= $p['id_pregunta'] ?>">Editar</a>
                                        |
                                        <a href="Preguntas.php?eliminar=<?= $p['id_pregunta'] ?>" onclick="return confirm('¿Seguro que deseas eliminar esta pregunta?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>

        <!-- Bloque para volver al menú de opciones administrativas -->
        <form action="Opciones.php" method="get" style="margin-top:2em;">
            <button type="submit">Volver a Opciones</button>
        </form>
    </main>
    <script src="/RIASEC/JAVASCRIPT/login.js"></script>
</body>
</html>
