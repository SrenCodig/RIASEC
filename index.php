<?php
require_once __DIR__ . '/PHP/crud.php';
session_start();

// Obtener preguntas y opciones desde la base de datos
$preguntas = [];
$opciones = [];
try {
    $preguntas = obtenerPreguntas();
    $opciones = obtenerOpciones();
} catch (Exception $e) {
    die('<h2>Error al cargar preguntas u opciones: ' . htmlspecialchars($e->getMessage()) . '</h2>');
}

// --- Lógica de navegación y validación de respuestas ---
$totalPreguntas = count($preguntas);
$preguntaActual = isset($_GET['q']) ? max(0, min($totalPreguntas-1, (int)$_GET['q'])) : 0;
if (!isset($_SESSION['respuestas_riasec'])) $_SESSION['respuestas_riasec'] = [];

$mensajeError = '';
$panelAdmin = '';
$verHistorial = '';

// Botón de opciones admin (izquierda)
if (isset($_SESSION['id_usuario']) && isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
    $panelAdmin = '<form action="VIEWS/ADMIN/Opciones.php" method="get" style="margin:0;">'
        . '<button type="submit">Panel de Administración</button>'
        . '</form>';
}

// Botón ver pruebas pasadas (derecha)
if (isset($_SESSION['id_usuario'])) {
    try {
        $id_usuario = $_SESSION['id_usuario'];
        $tienePruebas = false;
        $todosResultados = obtenerResultados();
        foreach ($todosResultados as $res) {
            if ($res['id_usuario'] == $id_usuario) {
                $tienePruebas = true;
                break;
            }
        }
        if ($tienePruebas) {
            $verHistorial = '<form action="VIEWS/USER/Resultados.php" method="get" style="margin:0;">'
                . '<button type="submit" name="ver_historial" value="1">Ver resultados anteriores</button>'
                . '</form>';
        }
    } catch (Exception $e) {
        // Si ocurre error, no mostrar botón
    }
}

// Procesar navegación y respuestas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = 'pregunta_' . $preguntas[$preguntaActual]['id_pregunta'];
    if (isset($_POST[$pid])) {
        $_SESSION['respuestas_riasec'][$pid] = $_POST[$pid];
    }

    // Botón siguiente
    if (isset($_POST['siguiente'])) {
        if (!isset($_SESSION['respuestas_riasec'][$pid])) {
            $mensajeError = 'Debes seleccionar una opción antes de continuar.';
        } else {
            header('Location: index.php?q=' . ($preguntaActual+1));
            exit;
        }
    }

    // Botón atrás
    if (isset($_POST['atras'])) {
        header('Location: index.php?q=' . ($preguntaActual-1));
        exit;
    }

    // Botón finalizar
    if (isset($_POST['finalizar'])) {
        // Validar que todas las preguntas estén respondidas
        $faltante = null;
        foreach ($preguntas as $i => $preg) {
            $pidCheck = 'pregunta_' . $preg['id_pregunta'];
            if (!isset($_SESSION['respuestas_riasec'][$pidCheck])) {
                $faltante = $i;
                break;
            }
        }
        if ($faltante !== null) {
            header('Location: index.php?q=' . $faltante . '&error=1');
            exit;
        } else {
            $puntajes = ['R'=>0,'I'=>0,'A'=>0,'S'=>0,'E'=>0,'C'=>0];
            foreach ($preguntas as $p) {
                $pidCheck = 'pregunta_' . $p['id_pregunta'];
                if (isset($_SESSION['respuestas_riasec'][$pidCheck]) && isset($puntajes[$p['categoria']])) {
                    $puntajes[$p['categoria']] += (int)$_SESSION['respuestas_riasec'][$pidCheck];
                }
            }
            $id_usuario = (isset($_SESSION['id_usuario'])) ? $_SESSION['id_usuario'] : null;
            crearResultado($id_usuario, $puntajes['R'], $puntajes['I'], $puntajes['A'], $puntajes['S'], $puntajes['E'], $puntajes['C']);
            $_SESSION['respuestas_riasec'] = $_POST;
            header('Location: VIEWS/USER/Resultados.php');
            exit;
        }
    }
}

// Mostrar mensaje de error si viene por GET
if (isset($_GET['error']) && $_GET['error'] == 1) {
    $mensajeError = 'Debes responder todas las preguntas antes de finalizar. Te hemos llevado a la primera pregunta sin responder.';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba Vocacional RIASEC</title>
    <link rel="stylesheet" href="/RIASEC/STYLE/Base.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Emergente.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Formulario.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/DarkMode.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes">
</head>
<body>
    <!-- Botón modo oscuro/claro adaptado de DarkMode -->
    <div class="dark-mode-switch" id="darkModeSwitch">
        <div class="circle">
            <span class="sun"><svg width="32" height="32" viewBox="0 0 32 32"><circle cx="16" cy="16" r="10" fill="#FCDE5B"/><g stroke="#FCDE5B" stroke-width="2"><line x1="16" y1="2" x2="16" y2="8"/><line x1="16" y1="24" x2="16" y2="30"/><line x1="2" y1="16" x2="8" y2="16"/><line x1="24" y1="16" x2="30" y2="16"/><line x1="6.34" y1="6.34" x2="10.49" y2="10.49"/><line x1="21.51" y1="21.51" x2="25.66" y2="25.66"/><line x1="6.34" y1="25.66" x2="10.49" y2="21.51"/><line x1="21.51" y1="10.49" x2="25.66" y2="6.34"/></g></svg></span>
            <span class="moon"><svg width="32" height="32" viewBox="0 0 32 32"><path d="M22 16a10 10 0 1 1-10-10c0 5.52 4.48 10 10 10z" fill="#fff"/></svg></span>
        </div>
    </div>
    <div id="user-menu" class="user-menu-top"></div>
    <main>
    <h1>Descubre tus intereses profesionales</h1>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1em;">
        <?= $panelAdmin ?: '<span></span>' ?>
        <?= $verHistorial ?: '<span></span>' ?>
    </div>
    <?php if (empty($preguntas) || empty($opciones)): ?>
        <p>No hay preguntas u opciones disponibles. Contacte al administrador.</p>
    <?php else:
        $p = $preguntas[$preguntaActual];
    ?>
    <form method="post" action="index.php?q=<?= $preguntaActual ?>" aria-label="Pregunta RIASEC">
        <fieldset>
            <legend><strong><?= htmlspecialchars($p['texto']) ?></strong></legend>
            <div class="opciones-contenedor">
                <?php foreach ($opciones as $o): ?>
                    <label class="card-option">
                        <input type="radio" name="pregunta_<?= $p['id_pregunta'] ?>" value="<?= $o['valor'] ?>"
                            <?php if (isset($_SESSION['respuestas_riasec']['pregunta_' . $p['id_pregunta']]) && $_SESSION['respuestas_riasec']['pregunta_' . $p['id_pregunta']] == $o['valor']) echo 'checked'; ?> >
                        <div class="card-radio"></div>
                        <span class="card-text"><?= htmlspecialchars($o['descripcion']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <?php if ($mensajeError): ?>
            <p style="color:#c00;font-weight:bold;text-align:center;"><?= $mensajeError ?></p>
        <?php endif; ?>
        <div style="display:flex;justify-content:space-between;margin-top:1em;">
            <?php if ($preguntaActual > 0): ?>
                <button type="submit" name="atras" value="1" class="btn-pag">&lt; Atrás</button>
            <?php else: ?>
                <span></span>
            <?php endif; ?>
            <span class="pregunta-indicador">Pregunta <?= ($preguntaActual+1) ?> de <?= $totalPreguntas ?></span>
            <?php if ($preguntaActual < $totalPreguntas-1): ?>
                <button type="submit" name="siguiente" value="1" class="btn-pag">Continuar ➜</button>
            <?php else:
                $todasRespondidas = true;
                foreach ($preguntas as $preg) {
                    $pidCheck = 'pregunta_' . $preg['id_pregunta'];
                    if (!isset($_SESSION['respuestas_riasec'][$pidCheck])) {
                        $todasRespondidas = false;
                        break;
                    }
                }
            ?>
                <button type="submit" name="finalizar" value="1" class="btn-pag"<?= ($todasRespondidas ? '' : ' disabled') ?>>Finalizar</button>
            <?php endif; ?>
        </div>
    </form>
    <?php endif; ?>
    </main>
    <script src="/RIASEC/JAVASCRIPT/Recursos.js"></script>
</body>
</html>