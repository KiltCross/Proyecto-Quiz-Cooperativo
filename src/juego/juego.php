<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

/*
Pantalla de juego: pregunta + retroalimentacion + resultados,
todo en un solo archivo (se muestran y ocultan bloques con JS).

Esta hardcodeada con una sola ronda de prueba. Falta conectar
con el websocket (avanzar de pregunta en pregunta, puntaje en
vivo de todos los jugadores, etc.)
*/

if (!isset($_SESSION['admin_id']) && !isset($_SESSION['jugador_nombre'])) {
    header("Location: ../../index.php");
    exit;
}

require '../includes/conexion.php';

$modalidad       = $_SESSION['modalidad']        ?? 'cooperativa';
$nombre_conjunto = $_SESSION['nombre_conjunto']  ?? null;

/*
leo las preguntas reales del conjunto elegido en crear.php
hardcodeado: siempre muestro la primera pregunta del conjunto,
falta lo del websocket para ir cambiando de pregunta en pregunta
*/
$preguntas = [];

if ($nombre_conjunto) {

    $sql = "SELECT p.id, p.texto, p.puntaje_base AS puntaje,
                   o.id AS id_opcion, o.texto AS opcion, o.es_correcta
            FROM conjunto c
            JOIN pregunta p ON p.id_conjunto = c.id
            JOIN opciones o ON o.id_pregunta = p.id
            WHERE c.nombre = '$nombre_conjunto'
            ORDER BY p.id ASC, o.id ASC";

    $res  = mysqli_query($conn, $sql);
    $filas = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];

    foreach ($filas as $fila) {
        $id = $fila['id'];
        if (!isset($preguntas[$id])) {
            $preguntas[$id] = [
                'texto'              => $fila['texto'],
                'puntaje'            => (int) $fila['puntaje'],
                'opciones'           => [],
                'respuesta_correcta' => 0,
            ];
        }
        if ($fila['es_correcta']) {
            $preguntas[$id]['respuesta_correcta'] = count($preguntas[$id]['opciones']);
        }
        $preguntas[$id]['opciones'][] = $fila['opcion'];
    }
    $preguntas = array_values($preguntas);
}

$primera_pregunta = $preguntas[0] ?? null;

$pregunta_dt = [
    'numero'   => 1,
    'total'    => count($preguntas),
    'texto'    => $primera_pregunta['texto']   ?? 'Sin preguntas cargadas',
    'opciones' => $primera_pregunta['opciones'] ?? [],
];

$retroalimentacion_al_jugador_dt = [
    'respuesta_correcta'        => $primera_pregunta['respuesta_correcta'] ?? 0,
    'puntuacion_de_la_pregunta' => $primera_pregunta['puntaje']            ?? 0,
];

$puntaje_colectivo = 0; // hardcodeado en 0, esto lo actualizaria el websocket
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pregunta — Quiz Cooperativo</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body class="pagina-juego">

<div class="juego-card">

    <div class="juego-header">
        <div class="juego-info">
            <strong>Quiz Cooperativo</strong>
            <?php if ($modalidad === 'cooperativa') { ?>
                <span class="badge-coop">🤝 Cooperativa</span>
            <?php } else { ?>
                <span class="badge-comp">🏆 Competitiva</span>
            <?php } ?>
        </div>
        <div class="juego-info juego-info--derecha">
            <strong id="num-pregunta">
                Pregunta <?= $pregunta_dt['numero'] ?>/<?= $pregunta_dt['total'] ?>
            </strong>
            <?php if ($modalidad === 'cooperativa') { ?>
                <span class="pts-equipo-header">
                    ⭐ <span id="pts-equipo"><?= $puntaje_colectivo ?></span> pts
                </span>
            <?php } ?>
        </div>
    </div>

    <div class="progreso-bar">
        <div class="progreso-fill" id="progreso-fill"
             style="width:<?= ($pregunta_dt['numero'] / $pregunta_dt['total'] * 100) ?>%"></div>
    </div>

    <!-- BLOQUE 1: pregunta en curso, visible mientras se espera que respondas -->
    <div class="juego-body" id="bloque-pregunta">

        <p class="pregunta-num" id="pregunta-num">
            PREGUNTA <?= $pregunta_dt['numero'] ?> DE <?= $pregunta_dt['total'] ?>
        </p>

        <p class="pregunta-texto" id="pregunta-texto">
            <?= htmlspecialchars($pregunta_dt['texto']) ?>
        </p>

        <div class="opciones-grid" id="opciones-grid">
            <?php
            $letras = ['A', 'B', 'C', 'D'];
            foreach ($pregunta_dt['opciones'] as $indice => $texto_opcion) {
            ?>
            <button class="opcion-btn"
                    id="opcion-<?= $indice ?>"
                    onclick="responder(<?= $indice ?>)">
                <span class="opcion-letra"><?= $letras[$indice] ?>) </span>
                <?= htmlspecialchars($texto_opcion) ?>
            </button>
            <?php } ?>
        </div>

        <p class="estado-resp" id="estado-resp" style="display:none">
            <span class="spinner"></span>
            Esperando a los demás...
        </p>

        <?php if ($modalidad === 'cooperativa') { ?>
        <div class="puntaje-equipo">
            <span>⭐ Puntaje del equipo</span>
            <span id="puntaje-equipo-barra"><?= $puntaje_colectivo ?> pts</span>
        </div>
        <?php } ?>

    </div>

    <!-- BLOQUE 2: retroalimentacion de la ronda (empieza oculto) -->
    <div class="juego-body" id="bloque-retroalimentacion" style="display:none">

        <div class="resultado-box">
            <div class="resultado-emoji" id="res-emoji">✅</div>
            <div class="resultado-titulo" id="res-titulo">¡Correcto!</div>
            <div class="resultado-puntos" id="res-puntos">+100 pts</div>
            <div class="resultado-correcto" id="res-correcto">
                Respuesta correcta: <span id="res-correcto-texto">—</span>
            </div>
            <p class="espera-anfitrion">
                <span class="spinner"></span> Siguiente pregunta en unos segundos...
            </p>
        </div>

    </div>

    <!-- BLOQUE 3: resultados finales (empieza oculto) -->
    <div class="juego-body" id="bloque-resultados" style="display:none">

        <div class="resultado-box">
            <div class="resultado-emoji">🏆</div>
            <div class="resultado-titulo">¡Partida terminada!</div>

            <?php if ($modalidad === 'cooperativa') { ?>

                <!-- en cooperativa todos comparten un solo puntaje, no hay ranking individual -->
                <p id="resultados-resumen">
                    El equipo terminó con <strong><?= $puntaje_colectivo ?> pts</strong>
                </p>

            <?php } else { ?>

                <!-- en competitiva cada jugador tiene su puntaje, por eso va el ranking con medallas -->
                <p id="resultados-resumen">Revisá el ranking final con tu puntaje</p>

                <table class="tabla" id="tabla-ranking">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Jugador</th>
                            <th>Puntaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- hardcodeado, ranking de prueba -->
                        <tr><td>🥇</td><td>Juan</td><td>+300 pts</td></tr>
                        <tr><td>🥈</td><td>María</td><td>+250 pts</td></tr>
                        <tr><td>🥉</td><td>Tu nombre</td><td>+200 pts</td></tr>
                    </tbody>
                </table>

            <?php } ?>

            <a href="../../index.php" class="boton boton--primario">🏠 VOLVER AL INICIO</a>
        </div>

    </div>

</div>

<script>
/*
Hardcodeado todo este JS, falta conectar con el websocket
para que sea en tiempo real con el resto de los jugadores.
*/

// dato real que vino de PHP (antes esto estaba fijo en el JS, ya quedó conectado)
const respuestaCorrecta = <?= (int) $retroalimentacion_al_jugador_dt['respuesta_correcta'] ?>;
const puntosPregunta    = <?= (int) $retroalimentacion_al_jugador_dt['puntuacion_de_la_pregunta'] ?>;
const opcionesPregunta  = <?= json_encode($pregunta_dt['opciones']) ?>;

let yaRespondio = false;

// hardcodeado: simulo la respuesta del servidor con un setTimeout
function responder(indiceOpcion) {
    if (yaRespondio) return;
    yaRespondio = true;

    const botones = document.querySelectorAll('.opcion-btn');
    botones.forEach(b => b.disabled = true);
    document.getElementById('opcion-' + indiceOpcion).classList.add('seleccionada');
    document.getElementById('estado-resp').style.display = '';

    // hardcodeado: aca iria lo del websocket (mandar la respuesta y esperar al resto)
    setTimeout(function () {
        mostrarRetroalimentacion(indiceOpcion);
    }, 1500);
}

function mostrarRetroalimentacion(indiceElegido) {

    const puntosGanados = (indiceElegido === respuestaCorrecta) ? puntosPregunta : 0;

    document.getElementById('opcion-' + respuestaCorrecta).classList.add('correcta');

    if (indiceElegido !== respuestaCorrecta) {
        document.getElementById('opcion-' + indiceElegido).classList.add('incorrecta');
        document.getElementById('opcion-' + indiceElegido).classList.remove('seleccionada');
    }

    document.getElementById('res-emoji').textContent  = puntosGanados > 0 ? '✅' : '❌';
    document.getElementById('res-titulo').textContent = puntosGanados > 0 ? '¡Correcto!' : 'Incorrecto';
    document.getElementById('res-puntos').textContent = '+' + puntosGanados + ' pts';
    document.getElementById('res-correcto-texto').textContent = opcionesPregunta[respuestaCorrecta];

    document.getElementById('bloque-pregunta').style.display = 'none';
    document.getElementById('bloque-retroalimentacion').style.display = '';

    // hardcodeado: siempre termina el juego despues de esta ronda,
    // falta lo del websocket para pasar a la siguiente pregunta en vez de terminar
    setTimeout(function () {
        mostrarResultadosFinales();
    }, 3000);
}

function mostrarResultadosFinales() {
    document.getElementById('bloque-retroalimentacion').style.display = 'none';
    document.getElementById('bloque-resultados').style.display = '';
}
</script>

</body>
</html>
