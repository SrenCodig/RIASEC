<!--
 * Opciones.php - Menú de opciones de administración
 *
 * Este archivo muestra el menú principal de opciones para el usuario administrador.
 * Requiere que el usuario tenga una sesión activa y el rol de administrador.
 * Si el usuario no cumple con los requisitos, se deniega el acceso.
 *
 * Opciones disponibles en el menú:
 * - Gestionar Preguntas: Redirige a la gestión de preguntas.
 * - Gestionar Usuarios: Redirige a la gestión de usuarios.
 * - Ver Estadísticas: Muestra estadísticas del sistema.
 * - Ver Bitácoras: Muestra el registro de bitácoras.
 * - Volver al inicio: Redirige a la página principal.
 *
 * Requiere:
 * - '../../PHP/crud.php' para operaciones de base de datos.
 * - '/RIASEC/JAVASCRIPT/login.js' para funcionalidades relacionadas con el usuario.
-->

<!-- PARTE PHP -->

<?php
// Medida de seguridad del Panel de opciones (solo admin)
require_once '../../PHP/crud.php';
session_start();
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    die('<h2>Acceso denegado. Solo administradores.</h2>');
}
?>

<!-- PARTE HTML -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Opciones - Admin</title>
</head>
<body>
    <div id="user-menu" style="text-align:right;margin:1em;"></div>
    <main>
        <h1>Menú de Opciones (Admin)</h1>
        <div>
            <form action="Preguntas.php" method="get" style="display:inline-block;margin-right:1em;">
                <button type="submit">Gestionar Preguntas</button>
            </form>
            <form action="Usuarios.php" method="get" style="display:inline-block;margin-right:1em;">
                <button type="submit">Gestionar Usuarios</button>
            </form>
            <form action="Estadisticas.php" method="get" style="display:inline-block;margin-right:1em;">
                <button type="submit">Ver Estadísticas</button>
            </form>
            <form action="Bitacoras.php" method="get" style="display:inline-block;margin-right:1em;">
                <button type="submit">Ver Bitácoras</button>
            </form>
            <form action="/RIASEC/index.php" method="get" style="display:inline-block;margin-right:1em;">
                <button type="submit">Volver al inicio</button>
            </form>
        </div>
    </main>
    <script src="/RIASEC/JAVASCRIPT/login.js"></script>
</body>
</html>