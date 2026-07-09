<?php

$codigo_acceso = $_POST['codigo'];
$nombre_jugador = $_POST['nombre'];

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

        <p class="estado-resp" id="estado-resp" style="display:in-line">
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

<div class="zona-mensaje-error">
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
var codigo_acceso = <?php echo $codigo_acceso; ?>;
var nombre_jugador= <?php echo $nombre_jugador; ?>;
var ultima_respuesta = -1;
var ultima_pregunta;
var modo_de_juego;

			p_numero_pregunta = document.getElementById('pregunta-num');
			p_texto_pregunta = document.getElementById('pregunta-texto');
			div_opciones = document.getElementById('opciones-grid');
			div_resultado_titulo = document.getElementById('res-titulo');
			div_resultado_puntos = document.getElementById('res-puntos');
			div_resultado_correcto_texto = document.getElementById('res-correcto-texto');
			div_zona_mensaje_error = document.getElementById('zona-mensaje-error');
			div_bloque_retroalimentacion = document.getElementById('bloque-retroalimentacion');
			div_bloque_pregunta = document.getElementById('bloque-pregunta');

const socket = new WebSocket("ws://localhost:8083?tipo_usuario=jugador?codigo_acceso="+codigo_acceso+"&nombre="+nombre_jugador);

socket.onopen = function (event){}

socket.onmessage = function (event){
	const el_mensaje = JSON.parse(event.data);
	switch(el_mensaje.accion){
	case 'avisar_comienzo_del_juego':
		ultima_pregunta = el_mensaje.primera_pregunta;

		modo_de_juego = el_mensaje.modo_de_juego;


		document.getElementById('estado-res').style.display="none";
			p_numero_pregunta.innerHTML = `el_mensaje.primera_pregunta.numero`;
			p_texto_pregunta.innerHTML = `el_mensaje.primera_pregunta.texto`;

			div_opciones.innerHTML= ``;

			var numero_opcion =1;
			for (const una_opcion of el_mensaje.primera_pregunta.opciones){
				div_opciones.innerHTML += `<button class="boton" id="boton-opcion-${numero_opcion}" onclick="Responder(${numero_opcion})">${una_opcion}</button>`;
				numero_opcion++;
			}

		break;
	case 'cambio_de_pregunta':
		div_bloque_pregunta.style.display="none";
		div_bloque_retroalimentacion.style.display="block";

		div_resultado_titulo.innerHTML =
			`<p>${ultima_pregunta.opciones[el_mensaje.resultado_de_la_respuesta.la_respuesta_correcta]}</p>`;
		div_resultado_puntos.innerHTML = 
			`<p>${el_mensaje.resultado_de_la_respuesta.puntaje_de_la_pregunta_contestada}</p>`;
		div_resultado_correcto_texto.innerHTML =
			`${el_mensaje.resultado_de_la_respuesta.la_respuesta_correcta}`;

		ultima_pregunta=el_mensaje.siguiente_pregunta;
		p_numero_pregunta.innerHTML= el_mensaje.siguiente_pregunta.numero;
		p_texto_pregunta.innerHTML = el_mensaje.siguiente_pregunta.texto;
		div_opciones.innerHTML = ``;
				for (const una_opcion of el_mensaje.siguiente_pregunta.opciones){
				div_opciones.innerHTML += `<p>${una_opcion}</p>`;
				}
		setTimeout(()=>{
		div_bloque_pregunta.style.display="block";
		div_bloque_retroalimentacion.style.display="none";
				}, 3000);

		break;

	case 'terminar_juego':
		div_bloque_pregunta.style.display="none";
		document.getElementById('bloque-respuesta').style.display="block";
		if (modo_de_juego=='cooperativa'){
			document.getElementById('puntaje-final-equipo').innerHTML=`${el_mensaje.resultado_de_la_respuesta.puntaje_colectivo}`;
		} else if (modo_de_juego=='competitiva'){
			tbody_tabla_ranking_cuerpo = document.getElementById('tabla-ranking-cuerpo');
			for(const un_puntaje of el_mensaje.resultado_de_la_respuesta.tabla_de_puntajes){
				tbody_tabla_ranking_cuerpo.innerHTML += `<tr><th>*</th><th>${un_puntaje[0]}</th><th>${un_puntaje[1]}</th></tr>`;
			}
		}
		break;

	case 'error':
		div_zona_mensaje_error.innerHTML = `<p>${el_mensaje.mensaje}</p>`;
		break;
	case 'error_fatal':
		div_zona_mensaje_error.innerHTML = `<p>${el_mensaje.mensaje}</p>`;
		break;



	}
}

function Responder(respuesta){
	socket.send("\"accion\" : \"responder_pregunta_actual\", \"respuesta\" : {\"id\": "+respuesta+"}");
}
</script>

</body>
</html>
