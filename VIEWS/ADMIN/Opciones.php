<!--
    Vista de opciones administrativas para RIASEC.
    Permite a los administradores navegar a diferentes secciones de gestión.
    Incluye opciones para gestionar carreras, preguntas, usuarios y ver estadísticas.
-->

<?php require_once __DIR__ . '/../../PHP/Funciones/OpcionesF.php'; ?>

<!-- PARTE HTML -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes">
    <title>Opciones - Admin</title>
    <link rel="stylesheet" href="/RIASEC/STYLE/Base.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Emergente.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/Formulario.css">
    <link rel="stylesheet" href="/RIASEC/STYLE/DarkMode.css">
</head>
<body>
    <!-- Switch de modo oscuro / claro -->
    <div class="dark-mode-switch" id="darkModeSwitch"> 
        <div class="circle">
            <span class="sun"><svg width="32" height="32" viewBox="0 0 32 32"><circle cx="16" cy="16" r="10" fill="#FCDE5B"/><g stroke="#FCDE5B" stroke-width="2"><line x1="16" y1="2" x2="16" y2="8"/><line x1="16" y1="24" x2="16" y2="30"/><line x1="2" y1="16" x2="8" y2="16"/><line x1="24" y1="16" x2="30" y2="16"/><line x1="6.34" y1="6.34" x2="10.49" y2="10.49"/><line x1="21.51" y1="21.51" x2="25.66" y2="25.66"/><line x1="6.34" y1="25.66" x2="10.49" y2="21.51"/><line x1="21.51" y1="10.49" x2="25.66" y2="6.34"/></g></svg></span>
            <span class="moon"><svg width="32" height="32" viewBox="0 0 32 32"><path d="M22 16a10 10 0 1 1-10-10c0 5.52 4.48 10 10 10z" fill="#fff"/></svg></span>
        </div>
    </div>
    <!-- Menú de usuario en la parte superior -->
    <div id="user-menu" class="user-menu-top"></div> <!-- Menú de usuario -->
    <main>
        <h1>Menú de Opciones (Admin)</h1>
        <div class="opciones-admin-menu">
            <form action="Carreras.php" method="get" class="opcion-form">
                <button type="submit" class="opcion-btn">Gestionar Carreras</button> <!-- Botón de gestionar carreras -->
            </form>
            <form action="Preguntas.php" method="get" class="opcion-form">
                <button type="submit" class="opcion-btn">Gestionar Preguntas</button> <!-- Botón de gestionar preguntas -->
            </form>
            <form action="Usuarios.php" method="get" class="opcion-form">
                <button type="submit" class="opcion-btn">Gestionar Usuarios</button> <!-- Botón de gestionar usuarios -->
            </form>
            <form action="Estadisticas.php" method="get" class="opcion-form">
                <button type="submit" class="opcion-btn">Ver Estadísticas</button> <!-- Botón de ver estadísticas -->
            </form>
            <form action="/RIASEC/index.php" method="get" class="opcion-form">
                <button type="submit" class="opcion-btn">Volver al inicio</button> <!-- Botón de volver al inicio -->
            </form>
        </div>
    </main>
    <script src="/RIASEC/JAVASCRIPT/Recursos.js"></script>
</body>
</html>