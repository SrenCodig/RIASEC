<?php
// auth.php: login, registro, logout y status en un solo endpoint (con funciones)
require_once __DIR__ . '/crud.php';
session_start();
header('Content-Type: application/json');

function login() {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['nombre']) || empty($data['contrasena'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
        exit;
    }
    $nombre = trim($data['nombre']);
    $contrasena = $data['contrasena'];
    $usuarios = obtenerUsuarios();
    $usuario = null;
    foreach ($usuarios as $u) {
        if (strcasecmp($u['nombre'], $nombre) === 0) {
            $usuario = $u;
            break;
        }
    }
    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
        exit;
    }
    if (!password_verify($contrasena, $usuario['contrasena'])) {
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta.']);
        exit;
    }
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['nombre_usuario'] = $usuario['nombre'];
    $_SESSION['id_rol'] = $usuario['id_rol'];
    echo json_encode(['success' => true]);
}

function register() {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['nombre']) || empty($data['correo']) || empty($data['contrasena'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
        exit;
    }
    $nombre = trim($data['nombre']);
    $correo = trim($data['correo']);
    $contrasena = $data['contrasena'];
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
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
    $res = crearUsuario($nombre, $correo, $hash);
    if ($res) {
        // Autologin tras registro
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

function logout() {
    $_SESSION = array();
    session_destroy();
    echo json_encode(['success' => true]);
}

function status() {
    if (isset($_SESSION['id_usuario'])) {
        $nombre = '';
        if (isset($_SESSION['nombre_usuario']) && trim($_SESSION['nombre_usuario']) !== '') {
            $nombre = $_SESSION['nombre_usuario'];
        } else {
            // Si el nombre no está en sesión, lo buscamos en la base de datos
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
        echo json_encode([
            'logged' => false
        ]);
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$action) {
    $json = json_decode(file_get_contents('php://input'), true);
    if (isset($json['action'])) $action = $json['action'];
}

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
// Eliminar cuenta del usuario autenticado
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

// Cambiar contraseña del usuario autenticado
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
    $res = actualizarUsuario($id, $usuario['nombre'], $usuario['correo'], $hash, $usuario['id_rol']);
    echo json_encode(['success' => $res]);
}
