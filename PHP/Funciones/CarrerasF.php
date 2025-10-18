<?php
// Archivo: PHP/Funciones/CarrerasF.php
// Lógica para VIEWS/ADMIN/Carreras.php

require_once __DIR__ . '/../crud.php';

// Iniciar sesión si es necesario
if (session_status() === PHP_SESSION_NONE) session_start();

// Seguridad: solo administradores
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header('Location: /RIASEC/index.php');
    exit;
}

$msg = '';

// Cache local de carreras para evitar múltiples consultas a la BD
$carrerasCache = null;
function cargarCarreras() {
    global $carrerasCache;
    if ($carrerasCache === null) {
        // Preferir la función helper si existe
        $carrerasCache = function_exists('obtenerCarreras') ? obtenerCarreras() : [];
        if (!$carrerasCache) {
            // Si no hay helper o no devolvió resultados, usar consulta directa
            $conn = conectar();
            $carrerasCache = [];
            $res = $conn->query('SELECT * FROM carreras ORDER BY id_carrera ASC');
            while ($row = $res->fetch_assoc()) $carrerasCache[] = $row;
            $conn->close();
        }
    }
    return $carrerasCache;
}

$carreras = cargarCarreras();
$totalCarreras = count($carreras);
$carrerasPorPagina = 10;
$paginaActual = isset($_GET['pag']) ? max(1, (int)$_GET['pag']) : 1;
$inicio = ($paginaActual - 1) * $carrerasPorPagina;
$carrerasPagina = array_slice($carreras, $inicio, $carrerasPorPagina);

// Eliminar carrera (GET)
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    if (function_exists('eliminarCarrera')) {
        eliminarCarrera($id);
    } else {
        // Borrado seguro con prepared statement
        $conn = conectar();
        $stmt = $conn->prepare('DELETE FROM carreras WHERE id_carrera=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
    $carrerasCache = null; // invalidar cache
    $msg = 'Carrera eliminada correctamente.';
    header('Location: Carreras.php?msg=' . urlencode($msg));
    exit;
}

// Manejo de formulario POST para agregar o editar carreras
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Agregar nueva carrera
    if (isset($_POST['agregar_carrera'])) {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $porcentajes = [];
        foreach (['R','I','A','S','E','C'] as $letra) $porcentajes[$letra] = (int)($_POST['porcentaje_' . $letra] ?? 0);
        if ($nombre !== '' && $descripcion !== '') {
            if (function_exists('crearCarrera')) {
                // Usar helper si existe
                crearCarrera($nombre, $porcentajes['R'], $porcentajes['I'], $porcentajes['A'], $porcentajes['S'], $porcentajes['E'], $porcentajes['C'], $descripcion);
            } else {
                // Insert direct con prepared statement
                $conn = conectar();
                $stmt = $conn->prepare('INSERT INTO carreras (nombre, porcentaje_R, porcentaje_I, porcentaje_A, porcentaje_S, porcentaje_E, porcentaje_C, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('siiiiiis', $nombre, $porcentajes['R'], $porcentajes['I'], $porcentajes['A'], $porcentajes['S'], $porcentajes['E'], $porcentajes['C'], $descripcion);
                $stmt->execute();
                $stmt->close();
                $conn->close();
            }
            $carrerasCache = null;
            $msg = 'Carrera agregada correctamente.';
            header('Location: Carreras.php?msg=' . urlencode($msg));
            exit;
        }
    }
    // Editar carrera existente
    if (isset($_POST['editar_carrera'])) {
        $id = (int)$_POST['id_edit'];
        $carreras = cargarCarreras();
        $carreraActual = null;
        foreach ($carreras as $c) if ($c['id_carrera'] == $id) { $carreraActual = $c; break; }
        if (!$carreraActual) {
            $msg = 'Error: carrera no encontrada.';
        } else {
            $nombre = trim($_POST['nombre_edit'] ?? $carreraActual['nombre']);
            $descripcion = trim($_POST['descripcion_edit'] ?? $carreraActual['descripcion']);
            $porcentajes = [];
            foreach (['R','I','A','S','E','C'] as $letra) {
                $key = 'porcentaje_' . $letra . '_edit';
                $porcentajes[$letra] = (isset($_POST[$key]) && $_POST[$key] !== '') ? (int)$_POST[$key] : (int)$carreraActual['porcentaje_' . $letra];
            }
            if ($nombre === '' || $descripcion === '') {
                $msg = 'Nombre y descripción son obligatorios.';
            } else {
                if (function_exists('actualizarCarrera')) {
                    // Usar helper si existe
                    actualizarCarrera($nombre, $porcentajes['R'], $porcentajes['I'], $porcentajes['A'], $porcentajes['S'], $porcentajes['E'], $porcentajes['C'], $descripcion, $id);
                } else {
                    // Prepared statement para actualizar
                    $conn = conectar();
                    $stmt = $conn->prepare('UPDATE carreras SET nombre=?, porcentaje_R=?, porcentaje_I=?, porcentaje_A=?, porcentaje_S=?, porcentaje_E=?, porcentaje_C=?, descripcion=? WHERE id_carrera=?');
                    $stmt->bind_param('siiiiiiisi', $nombre, $porcentajes['R'], $porcentajes['I'], $porcentajes['A'], $porcentajes['S'], $porcentajes['E'], $porcentajes['C'], $descripcion, $id);
                    $stmt->execute();
                    $stmt->close();
                    $conn->close();
                }
                $carrerasCache = null;
                $msg = 'Carrera actualizada correctamente.';
                header('Location: Carreras.php?msg=' . urlencode($msg));
                exit;
            }
        }
    }
}

// Preparar edición individual para la vista si se solicitó
$editando = false;
$carreraEdit = null;
if (isset($_GET['editar'])) {
    $idEdit = (int)$_GET['editar'];
    foreach ($carreras as $c) {
        if ($c['id_carrera'] == $idEdit) {
            $editando = true;
            $carreraEdit = $c;
            break;
        }
    }
}

?>
