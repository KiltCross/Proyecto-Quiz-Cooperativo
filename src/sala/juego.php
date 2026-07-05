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
            <span class="badge-coop" id="badge-modalidad"></span>
        </div>
        <div class="juego-info juego-info--derecha">
            <strong id="num-pregunta"></strong>
            <span class="pts-equipo-header" id="zona-pts-equipo" style="display:none">
                ⭐ <span id="pts-equipo">0</span> pts
            </span>
        </div>
    </div>

    <div class="progreso-bar">
        <div class="progreso-fill" id="progreso-fill" style="width:0%"></div>
    </div>

    <!-- BLOQUE 1: pregunta en curso, visible mientras se espera que respondas -->
    <div class="juego-body" id="bloque-pregunta">

        <p class="pregunta-num" id="pregunta-num"></p>

        <p class="pregunta-texto" id="pregunta-texto"></p>

        <div class="opciones-grid" id="opciones-grid">
            <!-- las opciones se llenan por JavaScript -->
        </div>

        <p class="estado-resp" id="estado-resp" style="display:none">
            <span class="spinner"></span>
            Esperando a los demás...
        </p>

        <div class="puntaje-equipo" id="zona-puntaje-equipo-barra" style="display:none">
            <span>⭐ Puntaje del equipo</span>
            <span id="puntaje-equipo-barra">0 pts</span>
        </div>

    </div>

    <!-- BLOQUE 2: retroalimentacion de la ronda (empieza oculto) -->
    <div class="juego-body" id="bloque-retroalimentacion" style="display:none">

        <div class="resultado-box">
            <div class="resultado-emoji" id="res-emoji"></div>
            <div class="resultado-titulo" id="res-titulo"></div>
            <div class="resultado-puntos" id="res-puntos"></div>
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

            <!-- en cooperativa todos comparten un solo puntaje, no hay ranking individual -->
            <p id="resumen-cooperativa" style="display:none">
                El equipo terminó con <strong><span id="puntaje-final-equipo">0</span> pts</strong>
            </p>

            <!-- en competitiva cada jugador tiene su puntaje, por eso va el ranking con medallas -->
            <div id="resumen-competitiva" style="display:none">
                <p>Revisá el ranking final con tu puntaje</p>

                <table class="tabla" id="tabla-ranking">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Jugador</th>
                            <th>Puntaje</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-ranking-cuerpo">
                        <!-- el ranking se llena por JavaScript -->
                    </tbody>
                </table>
            </div>

            <a href="../index.php" class="boton boton--primario">🏠 VOLVER AL INICIO</a>
        </div>

    </div>

</div>

<script>
/*
HTML puro, sin nada de PHP ni datos hardcodeados.
Esto lo conecta el websocket: traer el pregunta_dt de cada ronda,
mandar Responder_Pregunta al elegir una opcion, mostrar la
retroalimentacion (respuesta_correcta y puntuacion_de_la_pregunta),
y al final mostrar resultados (puntaje de equipo o ranking, según
la modalidad).
*/
</script>

</body>
</html>
