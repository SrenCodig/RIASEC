<!--
Vista de administración para la gestión de la bitácora del sistema RIASEC.
 * 
 * Funcionalidad principal:
 * - Restringe el acceso solo a usuarios administradores.
 * - Permite registrar manualmente acciones administrativas en la bitácora.
 * - Registra automáticamente acciones relevantes del sistema (usuarios, preguntas, opciones, resultados).
 * - Muestra una tabla con todos los registros de la bitácora, incluyendo ID, usuario, acción y fecha.
 * - Proporciona mensajes de estado tras registrar acciones.
 * - Incluye opción para regresar al menú principal de administración.

 * Dependencias:
 * - Requiere el archivo '../../PHP/crud.php' para funciones de acceso a la bitácora.
 * - Utiliza la función global crearBitacora() para registrar acciones.
 * - Utiliza la función obtenerBitacora() para obtener los registros.
 * 
 * Seguridad:
 * - Verifica sesión y rol de usuario antes de permitir el acceso.
 * 
 * Estructura:
 * - Bloque PHP para lógica de registro y obtención de bitácora.
 * - Bloque HTML para formulario de registro manual, tabla de registros y navegación.
-->

<!-- PARTE PHP -->
<?php
// ----------- SEGURIDAD Y DEPENDENCIAS -----------
require_once '../../PHP/crud.php';
session_start();
// Solo permite acceso a administradores
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    die('<h2>Acceso denegado. Solo administradores.</h2>');
}

// ----------- FUNCIÓN PARA REGISTRAR EN BITÁCORA -----------
// Registra una acción en la bitácora si existe la función global
function registrarBitacoraLocal($accion) {
    if (function_exists('crearBitacora')) {
        $id_usuario = $_SESSION['id_usuario'] ?? null;
        crearBitacora($id_usuario, $accion);
    }
}

// ----------- MENSAJE DE ESTADO -----------
$msg = '';

// ----------- REGISTRO MANUAL DE ACCIONES -----------
// Permite al admin registrar manualmente una acción en la bitácora
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_bitacora'])) {
    $accion = trim($_POST['accion_bitacora']);
    if ($accion) {
        registrarBitacoraLocal($accion);
        $msg = 'Acción registrada en la bitácora.';
    } else {
        $msg = 'Debes escribir una acción.';
    }
}

// ----------- REGISTRO AUTOMÁTICO DE ACCIONES DEL SISTEMA -----------
// Registra automáticamente acciones administrativas relevantes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- USUARIOS ---
    if (isset($_POST['crear_usuario'])) {
        registrarBitacoraLocal('Creó un usuario: ' . ($_POST['nombre'] ?? ''));
    }
    if (isset($_POST['editar_usuario'])) {
        registrarBitacoraLocal('Editó el usuario ID: ' . ($_POST['id_edit'] ?? ''));
    }
    if (isset($_POST['eliminar_usuario'])) {
        registrarBitacoraLocal('Eliminó el usuario ID: ' . ($_POST['eliminar_usuario']));
    }
    if (isset($_POST['cambiar_rol'])) {
        registrarBitacoraLocal('Cambió el rol del usuario ID: ' . ($_POST['cambiar_rol']));
    }
    // --- PREGUNTAS ---
    if (isset($_POST['crear_pregunta'])) {
        registrarBitacoraLocal('Creó una pregunta: ' . ($_POST['texto'] ?? ''));
    }
    if (isset($_POST['editar_pregunta'])) {
        registrarBitacoraLocal('Editó la pregunta ID: ' . ($_POST['id_edit'] ?? ''));
    }
    if (isset($_POST['eliminar_pregunta'])) {
        registrarBitacoraLocal('Eliminó la pregunta ID: ' . ($_POST['eliminar_pregunta']));
    }
    // --- OPCIONES ---
    if (isset($_POST['crear_opcion'])) {
        registrarBitacoraLocal('Creó una opción: ' . ($_POST['descripcion'] ?? ''));
    }
    if (isset($_POST['editar_opcion'])) {
        registrarBitacoraLocal('Editó la opción ID: ' . ($_POST['id_edit'] ?? ''));
    }
    if (isset($_POST['eliminar_opcion'])) {
        registrarBitacoraLocal('Eliminó la opción ID: ' . ($_POST['eliminar_opcion']));
    }
    // --- RESULTADOS ---
    if (isset($_POST['crear_resultado'])) {
        registrarBitacoraLocal('Creó un resultado para usuario ID: ' . ($_POST['id_usuario'] ?? ''));
    }
    if (isset($_POST['eliminar_resultado'])) {
        registrarBitacoraLocal('Eliminó el resultado ID: ' . ($_POST['eliminar_resultado']));
    }
}

// ----------- OBTENER REGISTROS DE LA BITÁCORA -----------
$bitacora = [];
try {
    $bitacora = obtenerBitacora();
} catch (Exception $e) {
    die('<h2>Error al cargar la bitácora: ' . htmlspecialchars($e->getMessage()) . '</h2>');
}
?>

<!-- PARTE HTML -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bitácora</title>
</head>
<body>
    <div id="user-menu" style="text-align:right;margin:1em;"></div>
    <main>
        <!-- ===================== BLOQUE: REGISTRO MANUAL EN BITÁCORA ===================== -->
        <!-- Permite al administrador registrar manualmente cualquier acción relevante. -->
        <h1>Bitácora</h1>
        <?php if ($msg): ?><p><strong><?= htmlspecialchars($msg) ?></strong></p><?php endif; ?>
        <form method="post" style="margin-bottom:2em;">
            <fieldset>
                <legend>Registrar nueva acción</legend>
                <!-- Campo para escribir la acción realizada -->
                <input type="text" name="accion_bitacora" required placeholder="Describe la acción realizada" style="width:60%;">
                <button type="submit">Registrar</button>
            </fieldset>
        </form>

        <!-- ===================== BLOQUE: TABLA DE REGISTROS DE BITÁCORA ===================== -->
        <!-- Muestra todos los registros de acciones administrativas realizadas en el sistema. -->
        <?php if (empty($bitacora)): ?>
            <p>No hay registros en la bitácora.</p>
        <?php else: ?>
        <table border="1" aria-label="Tabla de bitácora">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID Usuario</th>
                    <th>Acción</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($bitacora as $row): ?>
                <tr>
                    <!-- ID del registro de bitácora -->
                    <td><?= htmlspecialchars($row['id_bitacora']) ?></td>
                    <!-- ID del usuario que realizó la acción -->
                    <td><?= htmlspecialchars($row['id_usuario']) ?></td>
                    <!-- Descripción de la acción realizada -->
                    <td><?= htmlspecialchars($row['accion']) ?></td>
                    <!-- Fecha y hora de la acción -->
                    <td><?= htmlspecialchars($row['fecha']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- ===================== BLOQUE: VOLVER AL MENÚ DE OPCIONES ===================== -->
        <!-- Botón para regresar al panel principal de administración. -->
        <form action="Opciones.php" method="get">
            <button type="submit">Volver a Opciones</button>
        </form>
    </main>
    <script src="/RIASEC/JAVASCRIPT/login.js"></script>
</body>
</html>