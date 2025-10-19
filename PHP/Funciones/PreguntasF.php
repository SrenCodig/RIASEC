<?php
// Archivo: PHP/Funciones/PreguntasF.php
// Lógica para VIEWS/ADMIN/Preguntas.php (listado, añadir, editar, eliminar)

require_once __DIR__ . '/../crud.php';
require_once __DIR__ . '/OpcionesF.php';
// Validación centralizada: exigir admin
validar_sesion_usuario(true);

// Mensaje de estado y definiciones de categorías
$msg = '';
$categorias = ['R' => 'Realista', 'I' => 'Investigador', 'A' => 'Artístico', 'S' => 'Social', 'E' => 'Emprendedor', 'C' => 'Convencional'];
$catActual = isset($_GET['categoria']) && isset($categorias[$_GET['categoria']]) ? $_GET['categoria'] : 'R';

// Cache local de preguntas para evitar llamadas repetidas a la BD
$preguntasCache = null;
function cargarPreguntas() {
    global $preguntasCache;
    if ($preguntasCache === null) $preguntasCache = obtenerPreguntas();
    return $preguntasCache;
}

// Filtrar preguntas por la categoría seleccionada y paginar
$todasPreguntas = array_filter(cargarPreguntas(), fn($p) => $p['categoria'] === $catActual);
$totalPreguntas = count($todasPreguntas);
$preguntasPorPagina = 10;
$paginaActual = isset($_GET['pag']) ? max(1, (int)$_GET['pag']) : 1;
$inicio = ($paginaActual - 1) * $preguntasPorPagina;
$preguntas = array_slice($todasPreguntas, $inicio, $preguntasPorPagina);

// Helper para redirigir con un mensaje en la query string (evita duplicar lógica)
function redirect_with_msg($url, $msg) {
    header('Location: ' . $url . (strpos($url, '?') === false ? '?' : '&') . 'msg=' . urlencode($msg));
    exit;
}

// Acción: eliminar una pregunta por id
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    if (eliminarPregunta($id)) {
        $msg = 'Pregunta eliminada correctamente.';
        $preguntasCache = null; // invalidar cache
    } else {
        $msg = 'Error al eliminar.';
    }
    redirect_with_msg('Preguntas.php?categoria=' . $catActual, $msg);
}

// Manejar formulario POST para agregar o editar preguntas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Agregar pregunta nueva
    if (isset($_POST['agregar_pregunta'])) {
        $texto = trim($_POST['texto_nueva'] ?? '');
        $categoria = $_POST['categoria_nueva'] ?? '';
        if ($texto && isset($categorias[$categoria])) {
            crearPregunta($texto, $categoria);
            $preguntasCache = null;
            redirect_with_msg('Preguntas.php?categoria=' . $categoria, 'Pregunta agregada correctamente.');
        } else {
            $msg = 'Error: Debe ingresar texto y categoría válida.';
        }
    }
    // Editar pregunta existente
    if (isset($_POST['editar_pregunta'])) {
        $id = (int)($_POST['id_editar'] ?? 0);
        $texto = trim($_POST['texto_editar'] ?? '');
        $categoria = $_POST['categoria_editar'] ?? '';
        if ($id && $texto && isset($categorias[$categoria])) {
            if (actualizarPregunta($id, $texto, $categoria)) {
                $preguntasCache = null;
                redirect_with_msg('Preguntas.php?categoria=' . $categoria, 'Pregunta editada correctamente.');
            } else {
                $msg = 'Error al actualizar pregunta.';
            }
        } else {
            $msg = 'Error: Debe ingresar texto y categoría válida.';
        }
    }
}

// Preparar edición individual si se solicitó
$editando = false;
$preguntaEdit = null;
if (isset($_GET['editar'])) {
    $idEdit = (int)$_GET['editar'];
    foreach ($todasPreguntas as $p) {
        if ($p['id_pregunta'] == $idEdit) {
            $editando = true;
            $preguntaEdit = $p;
            break;
        }
    }
}

// Variables exportadas para la vista: $msg, $categorias, $catActual, $preguntas, $preguntaEdit, $editando, $paginaActual, $preguntasPorPagina, $totalPreguntas

?>
