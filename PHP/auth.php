<!--
    auth.php: login, registro, logout y status en un solo endpoint (con funciones).
    Maneja autenticación de usuarios para la aplicación RIASEC.
-->
<?php
// auth.php: login, registro, logout y status en un solo endpoint (con funciones)
// Comentarios añadidos para explicar cada sección y variable sin cambiar lógica.

// Incluir funciones CRUD para acceso a usuarios y demás tablas
require_once __DIR__ . '/crud.php';

// Iniciar/continuar la sesión para mantener estado de autenticación
session_start();

// Devolver JSON en todas las respuestas de este endpoint
header('Content-Type: application/json');

// ----------------------- FUNCIONES PÚBLICAS -----------------------

// Maneja la autenticación: verifica credenciales y crea variables de sesión
function login() {
    // Leer JSON enviado en el cuerpo de la petición
    $data = json_decode(file_get_contents('php://input'), true);
    // Validar campos obligatorios
    if (!$data || empty($data['nombre']) || empty($data['contrasena'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
        exit;
    }
    // Normalizar nombre y obtener contraseña tal cual
    $nombre = trim($data['nombre']);
    $contrasena = $data['contrasena'];
    // Obtener lista de usuarios desde la capa de datos
    $usuarios = obtenerUsuarios();
    $usuario = null;
    // Buscar usuario por nombre (case-insensitive)
    foreach ($usuarios as $u) {
        if (strcasecmp($u['nombre'], $nombre) === 0) {
            $usuario = $u;
            break;
        }
    }
    // Si no existe el usuario, devolver error
    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
        exit;
    }
    // Verificar contraseña con password_verify (hash seguro almacenado en BD)
    if (!password_verify($contrasena, $usuario['contrasena'])) {
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta.']);
        exit;
    }
    // Guardar datos relevantes en la sesión para otras páginas
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['nombre_usuario'] = $usuario['nombre'];
    $_SESSION['id_rol'] = $usuario['id_rol'];
    // Responder éxito
    echo json_encode(['success' => true]);
}

// Registro de nuevo usuario: valida unicidad, crea registro y auto-login
function register() {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['nombre']) || empty($data['correo']) || empty($data['contrasena'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
        exit;
    }
    $nombre = trim($data['nombre']);
    $correo = trim($data['correo']);
    $contrasena = $data['contrasena'];
    // Comprobar que no exista usuario con mismo nombre o correo
    $usuarios = obtenerUsuarios();
    foreach ($usuarios as $u) {
        if (strcasecmp($u['nombre'], $nombre) === 0) {
            echo json_encode(['success' => false, 'message' => 'El usuario ya existe.']);
            exit;
        }
        if (strcasecmp($u['correo'], $correo) === 0) {
            echo json_encode(['success' => false, 'message' => 'El correo ya está registrado.']);
            exit;
        }
    }
    // Hashear contraseña y crear usuario usando la función del CRUD
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
    $res = crearUsuario($nombre, $correo, $hash);
    if ($res) {
        // Autologin: buscar el usuario recién creado y poblar la sesión
        $usuarios = obtenerUsuarios();
        foreach ($usuarios as $u) {
            if (strcasecmp($u['nombre'], $nombre) === 0) {
                $_SESSION['id_usuario'] = $u['id_usuario'];
                $_SESSION['nombre_usuario'] = $u['nombre'];
                $_SESSION['id_rol'] = $u['id_rol'];
                break;
            }
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar usuario.']);
    }
}

// Cierra la sesión del usuario
function logout() {
    $_SESSION = array();
    session_destroy();
    echo json_encode(['success' => true]);
}

// Devuelve el estado de autenticación y nombre del usuario si está logueado
function status() {
    if (isset($_SESSION['id_usuario'])) {
        $nombre = '';
        // Preferir nombre guardado en sesión
        if (isset($_SESSION['nombre_usuario']) && trim($_SESSION['nombre_usuario']) !== '') {
            $nombre = $_SESSION['nombre_usuario'];
        } else {
            // Si no está, buscar en la BD por id_usuario
            require_once __DIR__ . '/crud.php';
            $usuarios = obtenerUsuarios();
            foreach ($usuarios as $u) {
                if ($u['id_usuario'] == $_SESSION['id_usuario']) {
                    $nombre = $u['nombre'];
                    break;
                }
            }
        }
        echo json_encode([
            'logged' => true,
            'nombre' => $nombre
        ]);
    } else {
        // No autenticado
        echo json_encode([
            'logged' => false
        ]);
    }
}

// ----------------------- ENRUTADOR SIMPLE -----------------------

$action = $_GET['action'] ?? $_POST['action'] ?? null;
// Si es POST y no hay acción en QUERY, leer JSON del body para acción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$action) {
    $json = json_decode(file_get_contents('php://input'), true);
    if (isset($json['action'])) $action = $json['action'];
}

// Disparar la función correspondiente según el parámetro 'action'
if ($action === 'login') {
    login();
} elseif ($action === 'register') {
    register();
} elseif ($action === 'logout') {
    logout();
} elseif ($action === 'status') {
    status();
} elseif ($action === 'delete_account') {
    delete_account();
} elseif ($action === 'change_password') {
    change_password();
} else {
    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
}

// ----------------------- UTILIDADES --------------------------------

// Eliminar la cuenta del usuario autenticado (usa eliminarUsuario del CRUD)
function delete_account() {
    if (!isset($_SESSION['id_usuario'])) {
        echo json_encode(['success' => false, 'error' => 'No autenticado']);
        return;
    }
    $id = $_SESSION['id_usuario'];
    require_once __DIR__ . '/crud.php';
    $res = eliminarUsuario($id);
    $_SESSION = array();
    session_destroy();
    echo json_encode(['success' => $res]);
}

// Cambiar contraseña del usuario autenticado: valida entrada y actualiza hash
function change_password() {
    if (!isset($_SESSION['id_usuario'])) {
        echo json_encode(['success' => false, 'error' => 'No autenticado']);
        return;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['nueva_contrasena'])) {
        echo json_encode(['success' => false, 'error' => 'Faltan datos']);
        return;
    }
    $id = $_SESSION['id_usuario'];
    $nueva = $data['nueva_contrasena'];
    $hash = password_hash($nueva, PASSWORD_DEFAULT);
    require_once __DIR__ . '/crud.php';
    $usuarios = obtenerUsuarios();
    $usuario = null;
    // Buscar el usuario actual para conservar nombre y correo al actualizar
    foreach ($usuarios as $u) {
        if ($u['id_usuario'] == $id) {
            $usuario = $u;
            break;
        }
    }
    if (!$usuario) {
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
        return;
    }
    // Llamar a actualizarUsuario con la nueva contraseña ya hasheada
    $res = actualizarUsuario($id, $usuario['nombre'], $usuario['correo'], $hash, $usuario['id_rol']);
    echo json_encode(['success' => $res]);
}
