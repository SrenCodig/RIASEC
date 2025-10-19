<!--
    Archivo: PHP/Funciones/EstadisticasF.php
    Lógica para VIEWS/ADMIN/Estadisticas.php
-->
<?php
// Archivo: PHP/Funciones/EstadisticasF.php
// Lógica para VIEWS/ADMIN/Estadisticas.php

require_once __DIR__ . '/../crud.php';

// Iniciar sesión si es necesario
if (session_status() === PHP_SESSION_NONE) session_start();

// Definir las letras usadas en el perfil RIASEC
$letras = ['R','I','A','S','E','C'];

// Control de acceso: solo administradores pueden ver estadísticas
if (!isset($_SESSION['id_usuario'], $_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    echo "<p style='color:red'><strong>Acceso denegado. Solo administradores.</strong></p>";
    exit;
}

// Helper que calcula cuántas preguntas hay por letra y el puntaje máximo posible
function obtenerDatosPreguntasOpciones($letras) {
    $preguntas = obtenerPreguntas(); // lista de preguntas
    $opciones  = obtenerOpciones();  // lista de opciones con su valor
    $maxValor  = $opciones ? max(array_column($opciones, 'valor')) : 0; // valor máximo por opción

    // Inicializar contador de preguntas por letra
    $numPreguntas = array_fill_keys($letras, 0);
    foreach ($preguntas as $p) $numPreguntas[$p['categoria']]++;

    // Puntaje máximo por letra = número de preguntas * valor máximo por respuesta
    $puntajeMax = array_map(fn($n) => $n * $maxValor, $numPreguntas);
    return [$numPreguntas, $puntajeMax];
}

// Cálculo de estadísticas a partir de los resultados almacenados
try {
    $resultados = obtenerResultados(); // todos los resultados de la BD
    // Inicializar arrays donde guardaremos los puntajes por letra y las dominancias
    $valores = array_fill_keys($letras, []);
    $dominancia = array_fill_keys($letras, []);

    // Recorrer cada resultado y distribuir sus puntajes por letra
    foreach ($resultados as $r) {
        // Extraer los puntajes como enteros para las letras en el mismo orden
        $puntajesR = array_map(fn($l) => (int)$r['puntaje_' . $l], $letras);
        $maxVal = max($puntajesR); // puntaje máximo en ese resultado (letra dominante)
        foreach ($letras as $i => $l) {
            $punt = $puntajesR[$i];
            $valores[$l][] = $punt; // almacenar para cálculo de media/mediana/desv
            if ($punt === $maxVal) $dominancia[$l][] = 1; // marcar dominancia si fue la letra mayor
        }
    }

    $n = count($resultados); // cantidad de resultados totales
    $estadisticas = [];
    foreach ($letras as $l) {
        $arr = $valores[$l];
        if ($n > 1) sort($arr); // ordenar para mediana
        $media = $n ? array_sum($arr) / $n : 0;
        $mediana = 0;
        if ($n) {
            if ($n % 2 == 1) {
                $mediana = $arr[floor($n / 2)];
            } else {
                $mediana = ($arr[$n / 2 - 1] + $arr[$n / 2]) / 2;
            }
        }
        // Desviación típica (poblacional) como raíz de la varianza
        $desv = $n ? sqrt(array_sum(array_map(fn($v) => pow($v - $media, 2), $arr)) / $n) : 0;
        $estadisticas[$l] = [
            'promedio' => round($media, 2),
            'mediana'  => round($mediana, 2),
            'desv'     => round($desv, 2),
            'porcDominancia' => $n ? round(count($dominancia[$l]) / $n * 100, 2) : 0
        ];
    }

    // Obtener también número de preguntas por letra y puntaje máximo para normalizaciones en la vista
    list($numPreguntas, $puntajeMax) = obtenerDatosPreguntasOpciones($letras);
} catch (Exception $e) {
    echo "<p style='color:red'><strong>Error al cargar resultados: " . htmlspecialchars($e->getMessage()) . "</strong></p>";
    exit;
}

// Variables exportadas para la vista: $letras, $estadisticas, $numPreguntas, $puntajeMax

?>
