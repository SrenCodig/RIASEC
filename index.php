<?php
require_once __DIR__ . '/PHP/Funciones/indexF.php';
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
    <?php else: ?>
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