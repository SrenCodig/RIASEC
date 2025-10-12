<?php
// Carreras.php - Gestión avanzada de carreras
require_once '../../PHP/crud.php';
session_start();

// Seguridad: solo administradores
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header('Location: /RIASEC/index.php');
    exit;
}

// --- Variables de estado ---
$msg = '';
$carreras = function_exists('obtenerCarreras') ? obtenerCarreras() : [];
if (!$carreras) {
    $conn = conectar();
    $carreras = [];
    $res = $conn->query('SELECT * FROM carreras ORDER BY id_carrera ASC');
    while ($row = $res->fetch_assoc()) $carreras[] = $row;
    $conn->close();
}
$totalCarreras = count($carreras);
$carrerasPorPagina = 10;
$paginaActual = isset($_GET['pag']) ? max(1, (int)$_GET['pag']) : 1;
$inicio = ($paginaActual - 1) * $carrerasPorPagina;
$carrerasPagina = array_slice($carreras, $inicio, $carrerasPorPagina);

// --- Acciones: eliminar, editar, agregar ---
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    if (function_exists('eliminarCarrera')) {
        eliminarCarrera($id);
    } else {
        $conn = conectar();
        $stmt = $conn->prepare('DELETE FROM carreras WHERE id_carrera=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
    $msg = 'Carrera eliminada correctamente.';
    header('Location: Carreras.php?msg=' . urlencode($msg));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Agregar carrera
    if (isset($_POST['agregar_carrera'])) {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $porcentaje_R = (int)($_POST['porcentaje_R'] ?? 0);
        $porcentaje_I = (int)($_POST['porcentaje_I'] ?? 0);
        $porcentaje_A = (int)($_POST['porcentaje_A'] ?? 0);
        $porcentaje_S = (int)($_POST['porcentaje_S'] ?? 0);
        $porcentaje_E = (int)($_POST['porcentaje_E'] ?? 0);
        $porcentaje_C = (int)($_POST['porcentaje_C'] ?? 0);
        if ($nombre !== '' && $descripcion !== '') {
            if (function_exists('crearCarrera')) {
                crearCarrera($nombre, $porcentaje_R, $porcentaje_I, $porcentaje_A, $porcentaje_S, $porcentaje_E, $porcentaje_C, $descripcion);
            } else {
                $conn = conectar();
                $stmt = $conn->prepare('INSERT INTO carreras (nombre, porcentaje_R, porcentaje_I, porcentaje_A, porcentaje_S, porcentaje_E, porcentaje_C, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('siiiiiis', $nombre, $porcentaje_R, $porcentaje_I, $porcentaje_A, $porcentaje_S, $porcentaje_E, $porcentaje_C, $descripcion);
                $stmt->execute();
                $stmt->close();
                $conn->close();
            }
            $msg = 'Carrera agregada correctamente.';
            header('Location: Carreras.php?msg=' . urlencode($msg));
            exit;
        }
    }
    // Editar carrera
        if (isset($_POST['editar_carrera'])) {
            $id = (int)$_POST['id_edit'];
            // Recuperar valores actuales si el input está vacío
            $carreraActual = null;
            foreach ($carreras as $c) {
                if ($c['id_carrera'] == $id) {
                    $carreraActual = $c;
                    break;
                }
            }
            // Si no se encuentra, no editar
            if (!$carreraActual) {
                $msg = 'Error: carrera no encontrada.';
            } else {
                $nombre = trim($_POST['nombre_edit'] ?? $carreraActual['nombre']);
                $descripcion = trim($_POST['descripcion_edit'] ?? $carreraActual['descripcion']);
                // Si el input está vacío, usar el valor actual
                $porcentaje_R = ($_POST['porcentaje_R_edit'] !== '' && isset($_POST['porcentaje_R_edit'])) ? (int)$_POST['porcentaje_R_edit'] : (int)$carreraActual['porcentaje_R'];
                $porcentaje_I = ($_POST['porcentaje_I_edit'] !== '' && isset($_POST['porcentaje_I_edit'])) ? (int)$_POST['porcentaje_I_edit'] : (int)$carreraActual['porcentaje_I'];
                $porcentaje_A = ($_POST['porcentaje_A_edit'] !== '' && isset($_POST['porcentaje_A_edit'])) ? (int)$_POST['porcentaje_A_edit'] : (int)$carreraActual['porcentaje_A'];
                $porcentaje_S = ($_POST['porcentaje_S_edit'] !== '' && isset($_POST['porcentaje_S_edit'])) ? (int)$_POST['porcentaje_S_edit'] : (int)$carreraActual['porcentaje_S'];
                $porcentaje_E = ($_POST['porcentaje_E_edit'] !== '' && isset($_POST['porcentaje_E_edit'])) ? (int)$_POST['porcentaje_E_edit'] : (int)$carreraActual['porcentaje_E'];
                $porcentaje_C = ($_POST['porcentaje_C_edit'] !== '' && isset($_POST['porcentaje_C_edit'])) ? (int)$_POST['porcentaje_C_edit'] : (int)$carreraActual['porcentaje_C'];
                // Validación básica (puedes agregar más)
                if ($nombre === '' || $descripcion === '') {
                    $msg = 'Nombre y descripción son obligatorios.';
                } else {
                    if (function_exists('actualizarCarrera')) {
                        actualizarCarrera($nombre, $porcentaje_R, $porcentaje_I, $porcentaje_A, $porcentaje_S, $porcentaje_E, $porcentaje_C, $descripcion, $id);
                    } else {
                        $conn = conectar();
                        $stmt = $conn->prepare('UPDATE carreras SET nombre=?, porcentaje_R=?, porcentaje_I=?, porcentaje_A=?, porcentaje_S=?, porcentaje_E=?, porcentaje_C=?, descripcion=? WHERE id_carrera=?');
                        $stmt->bind_param('siiiiiiisi', $nombre, $porcentaje_R, $porcentaje_I, $porcentaje_A, $porcentaje_S, $porcentaje_E, $porcentaje_C, $descripcion, $id);
                        $stmt->execute();
                        $stmt->close();
                        $conn->close();
                    }
                    $msg = 'Carrera actualizada correctamente.';
                    header('Location: Carreras.php?msg=' . urlencode($msg));
                    exit;
                }
            }
    }
}
// --- Edición individual ---
$editando = false;
$carreraEdit = null;
if (isset($_GET['editar'])) {
    $idEdit = (int)$_GET['editar'];
    foreach ($carreras as $c) {
        if ($c['id_carrera'] == $idEdit) {
            $editando = true;
            $carreraEdit = $c;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes">
    <title>Gestión de Carreras</title>
    <link rel="stylesheet" href="/RIASEC/STYLE/Base.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Emergente.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Formulario.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/DarkMode.css">
</head>
<body>
    <div class="dark-mode-switch" id="darkModeSwitch">
        <div class="circle">
            <span class="sun"><svg width="32" height="32" viewBox="0 0 32 32"><circle cx="16" cy="16" r="10" fill="#FCDE5B"/><g stroke="#FCDE5B" stroke-width="2"><line x1="16" y1="2" x2="16" y2="8"/><line x1="16" y1="24" x2="16" y2="30"/><line x1="2" y1="16" x2="8" y2="16"/><line x1="24" y1="16" x2="30" y2="16"/><line x1="6.34" y1="6.34" x2="10.49" y2="10.49"/><line x1="21.51" y1="21.51" x2="25.66" y2="25.66"/><line x1="6.34" y1="25.66" x2="10.49" y2="21.51"/><line x1="21.51" y1="10.49" x2="25.66" y2="6.34"/></g></svg></span>
            <span class="moon"><svg width="32" height="32" viewBox="0 0 32 32"><path d="M22 16a10 10 0 1 1-10-10c0 5.52 4.48 10 10 10z" fill="#fff"/></svg></span>
        </div>
    </div>
    <nav id="user-menu" class="user-menu-top"></nav>
    <main>
        <h1>Gestión de Carreras</h1>
        <?php if ($msg): ?>
            <div class="msg-status"> <?= htmlspecialchars($msg) ?> </div>
        <?php endif; ?>
        <section>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>R</th>
                        <th>I</th>
                        <th>A</th>
                        <th>S</th>
                        <th>E</th>
                        <th>C</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($carrerasPagina as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['nombre']) ?></td>
                            <td><?= htmlspecialchars($c['descripcion'] ?? '') ?></td>
                            <td><?= (int)$c['porcentaje_R'] ?></td>
                            <td><?= (int)$c['porcentaje_I'] ?></td>
                            <td><?= (int)$c['porcentaje_A'] ?></td>
                            <td><?= (int)$c['porcentaje_S'] ?></td>
                            <td><?= (int)$c['porcentaje_E'] ?></td>
                            <td><?= (int)$c['porcentaje_C'] ?></td>
                            <td style="text-align:center;">
                                <div style="display:flex;gap:.5em;justify-content:center;align-items:center;">
                                    <form method="get" style="display:inline;">
                                        <input type="hidden" name="editar" value="<?= $c['id_carrera'] ?>">
                                        <input type="hidden" name="pag" value="<?= $paginaActual ?>">
                                        <button type="submit" class="btn-accion" title="Editar" style="min-width:40px;max-width:40px;padding:.5em .5em;display:flex;align-items:center;justify-content:center;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M4 20h4.586a2 2 0 0 0 1.414-.586l9-9a2 2 0 0 0 0-2.828l-2.586-2.586a2 2 0 0 0-2.828 0l-9 9A2 2 0 0 0 4 15.414V20z"/><path stroke="currentColor" stroke-width="2" d="M14.5 7.5l2 2"/></svg>
                                        </button>
                                    </form>
                                    <form method="get" style="display:inline;" onsubmit="return confirm('¿Seguro que desea eliminar esta carrera?');">
                                        <input type="hidden" name="eliminar" value="<?= $c['id_carrera'] ?>">
                                        <input type="hidden" name="pag" value="<?= $paginaActual ?>">
                                        <button type="submit" class="btn-accion" title="Eliminar" style="min-width:40px;max-width:40px;padding:.5em .5em;display:flex;align-items:center;justify-content:center;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M6 7h12M9 7V5a3 3 0 0 1 6 0v2m-9 0v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V7"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
            $totalPaginas = ceil($totalCarreras / $carrerasPorPagina);
            if ($totalPaginas > 1): ?>
            <div style="display:flex;justify-content:center;gap:.5em;margin:2em 0;">
                <?php
                $maxBotones = 3;
                $inicioPag = max(1, $paginaActual - 1);
                $finPag = min($totalPaginas, $inicioPag + $maxBotones - 1);
                if ($totalPaginas > 5) {
                    if ($paginaActual > 1) {
                        echo '<form method="get" style="display:inline;"><input type="hidden" name="pag" value="1"><button type="submit" class="btn-accion" title="Primera página">&lt;&lt;</button></form> ';
                        echo '<form method="get" style="display:inline;"><input type="hidden" name="pag" value="' . ($paginaActual - 1) . '"><button type="submit" class="btn-accion" title="Anterior">&lt;</button></form> ';
                    }
                    for ($i = $inicioPag; $i <= $finPag; $i++) {
                        if ($i == $paginaActual) {
                            echo '<button class="btn-accion" style="background:#0a2342;color:#fff;" disabled>' . $i . '</button> ';
                        } else {
                            echo '<form method="get" style="display:inline;"><input type="hidden" name="pag" value="' . $i . '"><button type="submit" class="btn-accion">' . $i . '</button></form> ';
                        }
                    }
                    if ($paginaActual < $totalPaginas) {
                        echo '<form method="get" style="display:inline;"><input type="hidden" name="pag" value="' . ($paginaActual + 1) . '"><button type="submit" class="btn-accion" title="Siguiente">&gt;</button></form> ';
                        echo '<form method="get" style="display:inline;"><input type="hidden" name="pag" value="' . $totalPaginas . '"><button type="submit" class="btn-accion" title="Última página">&gt;&gt;</button></form> ';
                    }
                } else {
                    for ($i = 1; $i <= $totalPaginas; $i++) {
                        if ($i == $paginaActual) {
                            echo '<button class="btn-accion" style="background:#0a2342;color:#fff;" disabled>' . $i . '</button> ';
                        } else {
                            echo '<form method="get" style="display:inline;"><input type="hidden" name="pag" value="' . $i . '"><button type="submit" class="btn-accion">' . $i . '</button></form> ';
                        }
                    }
                }
                ?>
            </div>
            <?php endif; ?>
        </section>
        <section>
            <h2>Agregar nueva carrera</h2>
            <form method="post" id="form-carrera" autocomplete="off">
                <input type="text" name="nombre" class="input-pregunta" placeholder="Nombre de la carrera" required>
                <input type="text" name="descripcion" class="input-pregunta" placeholder="Descripción">
                <div style="display:flex;gap:1em;flex-wrap:wrap;margin:1em 0;">
                    <?php $letras = ['R','I','A','S','E','C']; foreach ($letras as $letra): ?>
                        <div style="display:flex;flex-direction:column;align-items:center;">
                            <label for="porcentaje_<?= $letra ?>" style="font-weight:700;"><?= $letra ?></label>
                            <input type="number" name="porcentaje_<?= $letra ?>" id="porcentaje_<?= $letra ?>" min="0" max="100" inputmode="numeric" pattern="[0-9]*" style="width:70px;text-align:center;appearance:textfield;-webkit-appearance:textfield;" required>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="agregar_carrera" class="btn-accion">Agregar carrera</button>
                <div id="error-carrera" style="color:red;font-weight:700;margin-top:1em;display:none;"></div>
            </form>
        </section>
        <br>
        <?php if ($editando && $carreraEdit): ?>
        <section>
            <hr>
            <h2>Editar carrera</h2>
            <form method="post" id="form-carrera-edit" autocomplete="off">
                <input type="hidden" name="id_edit" value="<?= $carreraEdit['id_carrera'] ?>">
                <input type="text" name="nombre_edit" class="input-pregunta" value="<?= htmlspecialchars($carreraEdit['nombre']) ?>" required>
                <input type="text" name="descripcion_edit" class="input-pregunta" value="<?= htmlspecialchars($carreraEdit['descripcion'] ?? '') ?>">
                <div style="display:flex;gap:1em;flex-wrap:wrap;margin:1em 0;">
                    <?php foreach ($letras as $letra): ?>
                        <div style="display:flex;flex-direction:column;align-items:center;">
                            <label for="porcentaje_<?= $letra ?>_edit" style="font-weight:700;"><?= $letra ?></label>
                            <input type="number" name="porcentaje_<?= $letra ?>_edit" id="porcentaje_<?= $letra ?>_edit" min="0" max="100" inputmode="numeric" pattern="[0-9]*" style="width:70px;text-align:center;appearance:textfield;-webkit-appearance:textfield;" value="<?= (int)$carreraEdit['porcentaje_' . $letra] ?>" required>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="editar_carrera" class="btn-accion">Guardar cambios</button>
                <div id="error-carrera-edit" style="color:red;font-weight:700;margin-top:1em;display:none;"></div>
            </form>
        </section>
        <?php endif; ?>
    </main>
    <style>
    /* Ocultar flechas de los inputs number */
    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
    </style>
    <script src="/RIASEC/JAVASCRIPT/Recursos.js"></script>
    <script>
    // Validación JS para los formularios de carrera
    function validarPorcentajes(letras, formId, errorId) {
        let valido = true;
        let error = '';
        let total = 0;
        letras.forEach(function(letra) {
            let input = document.getElementById('porcentaje_' + letra + (formId === 'form-carrera-edit' ? '_edit' : ''));
            let val = parseInt(input.value, 10);
            if (isNaN(val) || val < 0 || val > 100) {
                valido = false;
                error = 'Todos los porcentajes deben ser números entre 0 y 100.';
            }
            total += val;
        });
        if (valido && total > 100*letras.length) {
            valido = false;
            error = 'La suma total de porcentajes no debe exceder ' + (100*letras.length) + '.';
        }
        document.getElementById(errorId).style.display = valido ? 'none' : 'block';
        document.getElementById(errorId).textContent = error;
        return valido;
    }
    document.getElementById('form-carrera')?.addEventListener('submit', function(e) {
        let letras = ['R','I','A','S','E','C'];
        if (!validarPorcentajes(letras, 'form-carrera', 'error-carrera')) e.preventDefault();
    });
    document.getElementById('form-carrera-edit')?.addEventListener('submit', function(e) {
        let letras = ['R','I','A','S','E','C'];
        if (!validarPorcentajes(letras, 'form-carrera-edit', 'error-carrera-edit')) e.preventDefault();
    });
    </script>
</body>
</html>
