<?php
include "../includes/conexion.php";

if (isset($_POST['codigo_acceso'])){
	$codigo_acceso =$_POST['codigo_acceso'];
}
if (isset($_POST['nombre_jugador'])){
	$nombre_jugador = $_POST['nombre_jugador'];
}


		?> <div id="principal">

</div>
<script>

	const socket = new WebSocket("ws://localhost:8083?codigo_acceso=<?php echo $codigo_acceso ?? null;?>&nombre_jugador=<?php echo $nombre_jugador ?? null;?>");

	socket.onopen = function (event){
		alert("¡Te has conectado al servidor!");

	}

	socket.onmessage = function (event) {

		
		el_mensaje = JSON.parse(event.data);
		
		switch (el_mensaje["Estado"]){

			case 'Esperando':
				Injectar_Espere();
				break;

			case 'Jugando':
				Injectar_Jugando();
				break;

			case 'Finalizado':
				Injectar_Finalizado();
				break;

		}
		 

            // Get the output div element
            const outputDiv = document
                .getElementById('principal');
            // Append a paragraph with the
            //  received message to the output div
            outputDiv.innerHTML += `<p>Received <b>"${el_mensaje.Estado}"</b> from server.</p>`;
		    //.innerHTML += `<p>Received <b>"${event.data}"</b> from server.</p>`;
        };

function Injectar_Espere(){
	const principal_div = document.getElementById('principal');

	principal_div.innerHTML += `<p>Espere a entrar.</p>`;

}

function Injectar_Jugando(){

	const principal_div = document.getElementById('principal');

	principal_div.innerHTML += `<p>Lo siento, el juago ya empezó.</p>`;

}

function Injectar_Finalizado(){

	const principal_div = document.getElementById('principal');

	principal_div.innerHTML += `<p>Lo siento, el juago ya finalizó.</p>`;

}
</script>
