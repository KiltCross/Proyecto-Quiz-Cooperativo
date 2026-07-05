<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sala de espera — Quiz Cooperativo</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body class="pagina-lobby">

<div class="lobby-card">

    <div class="lobby-header">
        <p class="lobby-meta">🎮 Quiz Cooperativo — Sala de espera</p>
        <h1 id="nombre-conjunto"></h1>
        <p class="lobby-meta">
            <span class="badge-modalidad" id="badge-modalidad"></span>
        </p>
        <p class="lobby-meta">Compartí este código con los jugadores:</p>
        <div class="lobby-codigo" id="codigo-sala"></div>
    </div>

    <div class="lobby-body">

        <div class="jugadores-titulo">
            <span>👥 Jugadores conectados</span>
            <span id="contador"></span>
        </div>

        <div class="jugadores-lista" id="lista-jugadores">
            <!-- la lista de jugadores se llena por JavaScript -->
        </div>

        <div id="zona-anfitrion" style="display:none">
            <button id="btn-iniciar" class="boton boton--verde" onclick="iniciarPartida()">
                ▶ INICIAR PARTIDA
            </button>

            <button class="boton boton--rojo" onclick="confirmarCerrar()">
                ✕ CERRAR SALA
            </button>
        </div>

        <p class="aviso" id="zona-jugador" style="display:none">
            <span class="spinner"></span>
            Esperando a que el anfitrión inicie la partida...
        </p>

        <a href="../index.php" class="boton boton--contorno" onclick="return salir()">
            ← Salir de la sala
        </a>

    </div>
</div>

<script>
/*
HTML puro, sin nada de PHP ni datos hardcodeados.
Esto lo conecta el websocket: traer el sala_dt (codigo, conjunto,
modalidad), la lista de jugadores en vivo, y avisar a todos cuando
el anfitrión inicia la partida.
*/

function iniciarPartida() {
    // falta conectar con el websocket (Empezar_Juego), por ahora redirige directo
    window.location.href = '../juego/juego.php';
}

function confirmarCerrar() {
    if (!confirm('¿Cerrar la sala? Todos serán desconectados.')) return;
    window.location.href = '../admin/dashboard.php';
}

function salir() {
    if (!confirm('¿Salir de la sala?')) return false;
    window.location.href = '../index.php';
    return false;
}
</script>

</body>
</html>
