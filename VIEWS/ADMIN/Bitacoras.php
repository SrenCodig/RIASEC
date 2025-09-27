<?php
// Vista de bitácora de acciones administrativas
require_once '../../PHP/crud.php';
session_start();
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    die('<h2>Acceso denegado. Solo administradores.</h2>');
}
$bitacora = [];
try {
    $bitacora = obtenerBitacora();
} catch (Exception $e) {
    die('<h2>Error al cargar la bitácora: ' . htmlspecialchars($e->getMessage()) . '</h2>');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bitácora</title>
</head>
<body>
    <div id="user-menu" style="text-align:right;margin:1em;"></div>
    <main>
        <h1>Bitácora</h1>
        <?php if (empty($bitacora)): ?>
            <p>No hay registros en la bitácora.</p>
        <?php else: ?>
        <table border="1" aria-label="Tabla de bitácora">
            <thead>
                <tr><th>ID</th><th>ID Usuario</th><th>Acción</th><th>Fecha</th></tr>
            </thead>
            <tbody>
            <?php foreach ($bitacora as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id_bitacora']) ?></td>
                    <td><?= htmlspecialchars($row['id_usuario']) ?></td>
                    <td><?= htmlspecialchars($row['accion']) ?></td>
                    <td><?= htmlspecialchars($row['fecha']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        <form action="Opciones.php" method="get" style="margin-top:2em;">
            <button type="submit">Volver a Opciones</button>
        </form>
    </main>
    <script src="/RIASEC/JAVASCRIPT/login.js"></script>
</body>
</html>