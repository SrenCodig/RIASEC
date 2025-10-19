<!--
    Archivo: PHP/Funciones/ResultadosF.php
    Lógica para preparar datos usados por VIEWS/USER/Resultados.php
-->
<?php
// Archivo: PHP/Funciones/ResultadosF.php
// Lógica para preparar datos usados por VIEWS/USER/Resultados.php

require_once __DIR__ . '/../crud.php';

// Asegurar que la sesión está iniciada para leer id de usuario y demás
if (session_status() === PHP_SESSION_NONE) session_start();

// Variables iniciales que la vista esperará
$usuarioRegistrado = isset($_SESSION['id_usuario']); // true si hay usuario en sesión
$resultadosUsuario = []; // listado de resultados del usuario
$resultadoActual = null;  // resultado más reciente o calculado
$mostrarDetalle = false;  // flag si se está viendo un resultado histórico
$detalleResultado = null; // detalle seleccionado

// Explicaciones por letra (texto de ayuda para la vista)
$explicaciones = [
    'R' => 'Realista: Prefieres actividades prácticas, trabajo físico y el uso de herramientas o maquinaria.',
    'I' => 'Investigador: Te atraen las actividades analíticas, científicas y el aprendizaje intelectual.',
    'A' => 'Artístico: Disfrutas de la creatividad, el arte, la música y la autoexpresión.',
    'S' => 'Social: Te gusta ayudar, enseñar, orientar y trabajar con personas.',
    'E' => 'Emprendedor: Prefieres liderar, persuadir, vender y asumir riesgos.',
    'C' => 'Convencional: Te atraen las tareas organizativas, administrativas y el trabajo con datos.'
];

// Intentar cargar resultados: si el usuario está registrado tomamos de la BD,
// si no, pero hay respuestas en sesión, calculamos un resultado temporal.
try {
    if ($usuarioRegistrado) {
        $id_usuario = $_SESSION['id_usuario'];
        // Filtrar resultados por el id del usuario actual
        foreach (obtenerResultados() as $res) {
            if ($res['id_usuario'] == $id_usuario) $resultadosUsuario[] = $res;
        }
        // Ordenar por fecha descendente y tomar el más reciente
        if ($resultadosUsuario) {
            usort($resultadosUsuario, fn($a, $b) => strtotime($b['fecha']) - strtotime($a['fecha']));
            $resultadoActual = $resultadosUsuario[0];
        }
    } elseif (!empty($_SESSION['respuestas_riasec'])) {
        // Calcular puntajes a partir de las respuestas guardadas en sesión
        $respuestas = $_SESSION['respuestas_riasec'];
        $puntajes = array_fill_keys(['R','I','A','S','E','C'], 0);
        foreach (obtenerPreguntas() as $p) {
            $pid = 'pregunta_' . $p['id_pregunta'];
            if (isset($respuestas[$pid])) $puntajes[$p['categoria']] += (int)$respuestas[$pid];
        }
        // Crear un array similar al que proviene de la BD para mostrar en la vista
        $resultadoActual = [
            'fecha' => date('Y-m-d H:i:s'),
            'puntaje_R' => $puntajes['R'],
            'puntaje_I' => $puntajes['I'],
            'puntaje_A' => $puntajes['A'],
            'puntaje_S' => $puntajes['S'],
            'puntaje_E' => $puntajes['E'],
            'puntaje_C' => $puntajes['C'],
        ];
    }
} catch (Exception $e) {
    // Si ocurre error fatal al obtener datos, detener con mensaje amigable
    die('<h2 class="error">Error al cargar resultados: ' . htmlspecialchars($e->getMessage()) . '</h2>');
}

// Si se solicitó ver un resultado histórico por id_resultado, buscar su detalle
if ($usuarioRegistrado && isset($_GET['id_resultado'])) {
    foreach ($resultadosUsuario as $res) {
        if ($res['id_resultado'] == $_GET['id_resultado']) {
            $detalleResultado = $res;
            $mostrarDetalle = true;
            break;
        }
    }
}

// Manejo de descarga de detalles: genera un .txt con perfil y afinidades
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['descargar_detalles'])) {
    $resultadoParaMostrar = $mostrarDetalle && $detalleResultado ? $detalleResultado : $resultadoActual;
    if ($resultadoParaMostrar) {
        // Usar la nueva función para obtener perfil y afinidades
        $calc = calcularPerfilYAfinidades($resultadoParaMostrar);

        // Construir texto plano con la información
        $detalles = "PERFIL DEL USUARIO (porcentaje por letra):\n";
        foreach ($calc['perfilUsuario'] as $letra => $porc) {
            $detalles .= "$letra: $porc% ";
        }
        $detalles .= "\n\nLISTA COMPLETA DE CARRERAS (ordenadas por afinidad):\n";
        foreach ($calc['afinidades'] as $i => $carrera) {
            $detalles .= ($i + 1) . ". " . $carrera['nombre'] . "\n";
            $detalles .= "   Afinidad: " . $carrera['afinidad'] . "%\n";
            $detalles .= "   Perfil ideal: ";
            foreach ($carrera['perfil'] as $letra => $porc) {
                $detalles .= "$letra: $porc% ";
            }
            $detalles .= "\n   Descripción: " . $carrera['descripcion'] . "\n\n";
        }
        $detalles .= "Las 3 mejores carreras para ti son las que aparecen primero en la lista porque tienen la mayor afinidad con tu perfil vocacional, es decir, la menor diferencia entre tu perfil y el perfil ideal de la carrera.\n";

        // Forzar descarga como archivo de texto
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="detalles.txt"');
        echo $detalles;
        exit;
    }
}

// Nueva función: calcula perfil en porcentaje y afinidades para un resultado dado
function calcularPerfilYAfinidades(array $resultadoParaMostrar): array {
    $letras = ['R','I','A','S','E','C'];
    $preguntas = obtenerPreguntas();
    $opciones  = obtenerOpciones();
    $maxValor  = $opciones ? max(array_column($opciones, 'valor')) : 0; // valor máximo de opción

    // Contar cuántas preguntas por letra y calcular puntaje máximo por letra
    $numPreguntas = array_fill_keys($letras, 0);
    foreach ($preguntas as $p) $numPreguntas[$p['categoria']]++;
    $puntajeMax = array_map(fn($n) => $n * $maxValor, $numPreguntas);

    // Extraer puntajes del resultado
    $puntajes = [
        'R' => $resultadoParaMostrar['puntaje_R'],
        'I' => $resultadoParaMostrar['puntaje_I'],
        'A' => $resultadoParaMostrar['puntaje_A'],
        'S' => $resultadoParaMostrar['puntaje_S'],
        'E' => $resultadoParaMostrar['puntaje_E'],
        'C' => $resultadoParaMostrar['puntaje_C']
    ];

    // Perfil en porcentaje: puntaje / puntajeMax * 100 (redondeado)
    $perfilUsuario = [];
    foreach ($letras as $l) {
        $perfilUsuario[$l] = $puntajeMax[$l] > 0 ? round($puntajes[$l] / $puntajeMax[$l] * 100) : 0;
    }

    // Calcular afinidad de este perfil con cada carrera existente
    $todasCarreras = obtenerCarreras();
    $afinidades = [];
    foreach ($todasCarreras as $carrera) {
        // Construir perfil ideal de la carrera
        $perfilCarrera = [];
        foreach ($letras as $l) {
            $perfilCarrera[$l] = isset($carrera['porcentaje_' . $l]) ? (int)$carrera['porcentaje_' . $l] : 0;
        }
        // Distancia absoluta entre perfiles (suma de diferencias)
        $distancia = 0;
        foreach ($letras as $l) {
            $distancia += abs($perfilUsuario[$l] - $perfilCarrera[$l]);
        }
        // Afinidad como 100 - (distancia normalizada)
        $afinidad = 100 - round($distancia / (count($letras) * 100) * 100);
        $afinidades[] = [
            'nombre' => $carrera['nombre'],
            'descripcion' => $carrera['descripcion'],
            'perfil' => $perfilCarrera,
            'afinidad' => $afinidad
        ];
    }
    // Ordenar de mayor a menor afinidad
    usort($afinidades, fn($a, $b) => $b['afinidad'] <=> $a['afinidad']);

    return [
        'puntajes' => $puntajes,
        'perfilUsuario' => $perfilUsuario,
        'afinidades' => $afinidades,
    ];
}

// Las variables definidas aquí ($resultadoActual, $resultadosUsuario, etc.) quedarán disponibles para la vista que incluya este archivo.

?>
