<?php
// Archivo: PHP/Funciones/OpcionesF.php
// Lógica mínima para la vista Opciones (seguridad)

require_once __DIR__ . '/../crud.php';

// Iniciar sesión si no está
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Valida que el usuario en sesión exista en la base de datos.
 * Si no existe, destruye la sesión y redirige al índice.
 * Si $enforce_admin es true, además exige que el usuario tenga rol 1 (administrador).
 *
 * @param bool $enforce_admin
 * @return void
 */
function validar_sesion_usuario(bool $enforce_admin = false): void {
    try {
        if (empty($_SESSION['id_usuario'])) {
            if ($enforce_admin) die('<h2>Acceso denegado. Solo administradores.</h2>');
            return; // nada que validar
        }

        $allUsers = obtenerUsuarios();
        $found = false;
        if (is_array($allUsers)) {
            foreach ($allUsers as $u) {
                if ($u['id_usuario'] == $_SESSION['id_usuario']) {
                    $found = true;
                    // Si se requiere admin, validar rol aquí
                    if ($enforce_admin && (!isset($u['id_rol']) || $u['id_rol'] != 1)) {
                        // Usuario no es admin: cerrar sesión y mostrar acceso denegado
                        $_SESSION = [];
                        if (ini_get('session.use_cookies')) {
                            $params = session_get_cookie_params();
                            setcookie(session_name(), '', time() - 42000,
                                $params['path'], $params['domain'], $params['secure'], $params['httponly']
                            );
                        }
                        session_destroy();
                        die('<h2>Acceso denegado. Solo administradores.</h2>');
                    }
                    break;
                }
            }
        }

        if (!$found) {
            // Usuario de la sesión ya no existe en la BD: cerrar sesión y redirigir
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params['path'], $params['domain'], $params['secure'], $params['httponly']
                );
            }
            session_destroy();
            header('Location: /RIASEC/index.php?msg=' . urlencode('Sesión cerrada: el usuario ya no existe.'));
            exit;
        }
    } catch (Exception $e) {
        // No hacemos nada en caso de error de BD; la página puede decidir manejarlo.
    }
}

// Por defecto, Opciones es una página admin: validar existencia y rol
validar_sesion_usuario(true);

?>
