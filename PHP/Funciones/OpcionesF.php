<!--
    Archivo: PHP/Funciones/OpcionesF.php
    Lógica mínima para la vista Opciones (seguridad)
-->
<?php
// Archivo: PHP/Funciones/OpcionesF.php
// Lógica mínima para la vista Opciones (seguridad)

require_once __DIR__ . '/../crud.php';

// Iniciar sesión si no está
if (session_status() === PHP_SESSION_NONE) session_start();

// Acceso restringido: solo administradores (rol = 1)
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    die('<h2>Acceso denegado. Solo administradores.</h2>');
}

?>
