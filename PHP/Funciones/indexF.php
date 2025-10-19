<!--
	Archivo: PHP/Funciones/indexF.php
	Contiene la lógica para preparar datos y procesar el formulario de la prueba RIASEC.
-->

<?php
// Archivo: PHP/Funciones/indexF.php
// Contiene la lógica para preparar datos y procesar el formulario de la prueba RIASEC.

require_once __DIR__ . '/../crud.php';

// Iniciar la sesión si aún no existe. session_status() devuelve el estado actual de la sesión.
if (session_status() === PHP_SESSION_NONE) {
	session_start(); // Abre o reanuda la sesión para acceder a $_SESSION
}

// Inicializar contenedores para preguntas y opciones.
$preguntas = [];
$opciones = [];
try {
	// Obtener preguntas y opciones desde las funciones de CRUD (conexión a la BD en crud.php)
	$preguntas = obtenerPreguntas();
	$opciones  = obtenerOpciones();
} catch (Exception $e) {
	// Si ocurre un error al cargar datos, devolvemos un 500 y mostramos mensaje seguro.
	http_response_code(500);
	echo '<h2>Error al cargar preguntas u opciones: ' . htmlspecialchars($e->getMessage()) . '</h2>';
	exit; // Detener ejecución para evitar comportamiento indefinido sin datos.
}

// Valores por defecto y control de índices
$totalPreguntas = count($preguntas); // número total de preguntas disponibles
// Índice de la pregunta actual pasado por GET (q). Aseguramos que esté en rango [0, total-1].
$preguntaActual = isset($_GET['q']) ? max(0, min(max(0, $totalPreguntas-1), (int)$_GET['q'])) : 0;

// Asegurar que en la sesión exista el array de respuestas; si no, inicializarlo.
if (!isset($_SESSION['respuestas_riasec']) || !is_array($_SESSION['respuestas_riasec'])) {
	$_SESSION['respuestas_riasec'] = [];
}

// Variables auxiliares expuestas a la vista
$mensajeError = '';
$panelAdmin = '';
$verHistorial = '';

// Construir botón de acceso al panel de administración si el usuario es administrador (rol == 1)
if (!empty($_SESSION['id_usuario']) && !empty($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) {
	$panelAdmin = '<form action="VIEWS/ADMIN/Opciones.php" method="get" style="margin:0;">'
				. '<button type="submit">Panel de Administración</button>'
				. '</form>';
}

// Si el usuario está logueado, comprobar si tiene resultados previos para mostrar botón "Ver historial"
if (!empty($_SESSION['id_usuario'])) {
	try {
		$id_usuario = $_SESSION['id_usuario'];
		$tienePruebas = false; // flag para indicar si encontró resultados del usuario
		$todosResultados = obtenerResultados(); // traer todos los resultados (función del CRUD)
		foreach ($todosResultados as $res) {
			if (isset($res['id_usuario']) && $res['id_usuario'] == $id_usuario) {
				$tienePruebas = true;
				break; // salir una vez encontrado al menos uno
			}
		}
		if ($tienePruebas) {
			// Formulario simple que redirige a la vista de resultados
			$verHistorial = '<form action="VIEWS/USER/Resultados.php" method="get" style="margin:0;">'
						 . '<button type="submit" name="ver_historial" value="1">Ver resultados anteriores</button>'
						 . '</form>';
		}
	} catch (Exception $e) {
		// Silenciar errores aquí para no romper la experiencia si falla la comprobación del historial.
	}
}

// Procesar formulario POST (navegación entre preguntas y envío final)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Si la pregunta actual existe, construir el identificador que usamos en los inputs
	if (isset($preguntas[$preguntaActual])) {
		$pid = 'pregunta_' . $preguntas[$preguntaActual]['id_pregunta'];
		// Si en POST viene una respuesta para esta pregunta, guardarla en la sesión
		if (isset($_POST[$pid])) {
			$_SESSION['respuestas_riasec'][$pid] = $_POST[$pid];
		}

		// Acciones de navegación
		// Botón "Siguiente": validar que la pregunta actual tenga respuesta antes de avanzar
		if (isset($_POST['siguiente'])) {
			if (!isset($_SESSION['respuestas_riasec'][$pid])) {
				$mensajeError = 'Debes seleccionar una opción antes de continuar.'; // mensaje para la vista
			} else {
				header('Location: index.php?q=' . ($preguntaActual + 1));
				exit;
			}
		}

		// Botón "Atrás": retroceder una pregunta
		if (isset($_POST['atras'])) {
			header('Location: index.php?q=' . ($preguntaActual - 1));
			exit;
		}

		// Botón "Finalizar": validar todas las respuestas y registrar resultado
		if (isset($_POST['finalizar'])) {
			$faltante = null;
			// Buscar la primera pregunta sin respuesta
			foreach ($preguntas as $i => $preg) {
				$pidCheck = 'pregunta_' . $preg['id_pregunta'];
				if (!isset($_SESSION['respuestas_riasec'][$pidCheck])) {
					$faltante = $i;
					break;
				}
			}
			if ($faltante !== null) {
				// Redirigir a la primera pregunta sin responder y mostrar error
				header('Location: index.php?q=' . $faltante . '&error=1');
				exit;
			} else {
				// Calcular puntajes por cada categoría RIASEC sumando los valores de las respuestas
				$puntajes = ['R' => 0, 'I' => 0, 'A' => 0, 'S' => 0, 'E' => 0, 'C' => 0];
				foreach ($preguntas as $p) {
					$pidCheck = 'pregunta_' . $p['id_pregunta'];
					if (isset($_SESSION['respuestas_riasec'][$pidCheck]) && isset($puntajes[$p['categoria']])) {
						// Cast a int por seguridad antes de sumar
						$puntajes[$p['categoria']] += (int)$_SESSION['respuestas_riasec'][$pidCheck];
					}
				}
				// Si el usuario está logueado, asociar resultado al id; si no, null
				$id_usuario = (!empty($_SESSION['id_usuario'])) ? $_SESSION['id_usuario'] : null;
				// Crear resultado en BD (función definida en crud.php)
				crearResultado($id_usuario, $puntajes['R'], $puntajes['I'], $puntajes['A'], $puntajes['S'], $puntajes['E'], $puntajes['C']);
				// Mantener respuestas del POST en sesión para conveniencia en la pantalla de resultados
				$_SESSION['respuestas_riasec'] = $_POST;
				// Redirigir a la vista de resultados
				header('Location: VIEWS/USER/Resultados.php');
				exit;
			}
		}
	}
}

// Si vino un error por GET, preparar mensaje de error legible para la vista
if (isset($_GET['error']) && $_GET['error'] == 1) {
	$mensajeError = 'Debes responder todas las preguntas antes de finalizar. Te hemos llevado a la primera pregunta sin responder.';
}

// Preparar variables que usará la vista
$p = isset($preguntas[$preguntaActual]) ? $preguntas[$preguntaActual] : null; // pregunta actual o null
$todasRespondidas = true; // flag que indica si todas las preguntas ya tienen respuesta
foreach ($preguntas as $preg) {
	$pidCheck = 'pregunta_' . $preg['id_pregunta'];
	if (!isset($_SESSION['respuestas_riasec'][$pidCheck])) {
		$todasRespondidas = false;
		break;
	}
}
?>