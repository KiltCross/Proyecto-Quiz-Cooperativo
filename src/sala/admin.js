let admin_email= "";
let admin_contrasenia="";
const socket = new WebSocket("ws://localhost:8083?tipo_usuario=administrador&email=${admin_email}&contrasenia=${admin_contrasenia}");

socket.onopen = function (event) {
	socket.send('{"accion" : "obtener_sala_activa"}');
}
socket.onmessage = function (event){
	const el_mensaje= JSON.parse(event.data);
	switch (el_mensaje.accion){
		case "dar_sala_activa":
			break;
		case 'error':
			break;
		case 'error_fatal':
			break;

	}
}
