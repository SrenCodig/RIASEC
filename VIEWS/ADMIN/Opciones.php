<?php
// Opciones.php - Menú de opciones de administración
require_once '../../PHP/crud.php';
session_start();
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    die('<h2>Acceso denegado. Solo administradores.</h2>');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Opciones - Admin</title>
    <link rel="stylesheet" href="/RIASEC/STYLE/admin.css">
</head>
<body>
    <div id="user-menu" style="text-align:right;margin:1em;"></div>
    <main>
        <h1>Menú de Opciones (Admin)</h1>
        <section style="margin-bottom:2em;">
            <form action="Preguntas.php" method="get" style="display:inline-block;margin-right:1em;">
                <button type="submit">Gestionar Preguntas</button>
            </form>
            <form action="Usuarios.php" method="get" style="display:inline-block;margin-right:1em;">
                <button type="submit">Gestionar Usuarios</button>
            </form>
        </section>
        <nav style="margin-top:1em;">
            <form action="/RIASEC/index.php" method="get" style="display:inline;">
                <button type="submit">Volver al inicio</button>
            </form>
            <form action="Estadisticas.php" method="get" style="display:inline;">
                <button type="submit">Ver Estadísticas</button>
            </form>
            <form action="Bitacoras.php" method="get" style="display:inline;">
                <button type="submit">Ver Bitácoras</button>
            </form>
        </nav>
    </main>
    <script src="/RIASEC/JAVASCRIPT/login.js"></script>
</body>
</html>