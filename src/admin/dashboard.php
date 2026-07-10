<?php
include '../includes/auth_admin.php';
include '../includes/conexion.php';

$id_admin = $_SESSION['admin_id'];

$email_admin = $_SESSION['admin_email'];
$password_admin =  $_SESSION['admin_password_hash'];
/*
traigo los conjuntos del admin con cantidad de preguntas
*/

$sql_conjuntos = "SELECT c.id, c.nombre, COUNT(p.id) AS total_preguntas
                  FROM conjunto c
                  LEFT JOIN pregunta p ON p.id_conjunto = c.id
                  WHERE c.id_admin = $id_admin
                  GROUP BY c.id, c.nombre
		  ORDER BY c.nombre ASC";


$res_conjuntos = mysqli_query($conn, $sql_conjuntos);

$conjuntos = mysqli_fetch_all($res_conjuntos, MYSQLI_ASSOC);

/*
verifico si hay una sala activa del admin
*/

$sql_sala = "SELECT codigo_acceso, estado, modalidad
             FROM sala
             WHERE id_admin = $id_admin
             AND estado IN ('esperando', 'jugando')
             LIMIT 1";

$res_sala = mysqli_query($conn, $sql_sala);

$sala_activa = mysqli_fetch_assoc($res_sala);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Quiz Cooperativo</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body class="pagina-dashboard">

<header class="barra-superior">
    <span>🎮 Quiz Cooperativo</span>
    <span>👤 <?= $_SESSION['admin_nombre'] ?></span>
    <a href="../logout.php" class="boton boton--contorno boton--chico">Salir</a>
</header>

<main class="dashboard">

    <section class="panel">

        <h2>📚 Mis conjuntos</h2>

        <?php if (count($conjuntos) == 0) { ?>

            <p class="texto-vacio">No tenés conjuntos creados todavía.</p>

        <?php } else { ?>

            <table class="tabla">
                <tr>
                    <th>Nombre</th>
                    <th>Preguntas</th>
                    <th>Acciones</th>
                </tr>

                <?php foreach ($conjuntos as $conjunto) { ?>
                <tr>
                    <td><?= $conjunto['nombre'] ?></td>
                    <td><?= $conjunto['total_preguntas'] ?></td>
                    <td>
                        <a href="preguntas.php?id=<?= $conjunto['id'] ?>"
                           class="boton boton--contorno boton--chico">
                            Ver preguntas
                        </a>
                    </td>
                </tr>
                <?php } ?>

            </table>

        <?php } ?>

        <a href="conjuntos.php" class="boton boton--primario">📚 MIS CONJUNTOS</a>

    </section>

    <section class="panel">

        <h2>🎮 Sala activa</h2>

        <p class="mensaje-error" id="mensaje-error-sala" style="display:none"></p>

        <div id="sala"></div>

        <!--
        Plantilla del formulario de "crear sala" (conjunto + modalidad).
        Está oculta y en JS se clona/muestra dentro de #sala cuando
        el servidor avisa que no hay sala activa.
        No tiene nada de WebSocket todavía: solo lee lo que el admin
        elige y lo deja disponible en el objeto `datos_para_crear_sala`.
        -->
        <template id="plantilla-form-crear-sala">
            <form id="form-crear-sala">

                <label for="nombre_conjunto">Conjunto de preguntas</label>
                <select name="nombre_conjunto" id="nombre_conjunto" required>
                    <option value="">— Elegí un conjunto —</option>
                    <?php foreach ($conjuntos as $conjunto) { ?>
                        <option value="<?= $conjunto['nombre'] ?>">
                            <?= $conjunto['nombre'] ?> (<?= $conjunto['total_preguntas'] ?> preguntas)
                        </option>
                    <?php } ?>
                </select>

                <label>Modalidad de juego</label>

                <div class="fila-modalidad">
                    <label class="opcion-modalidad">
                        <input type="radio" name="modalidad" value="cooperativa" required>
                        🤝 Cooperativa
                    </label>
                    <label class="opcion-modalidad">
                        <input type="radio" name="modalidad" value="competitiva">
                        🏆 Competitiva
                    </label>
                </div>

                <button type="submit" class="boton boton--verde">
                    🎮 CREAR SALA
                </button>

            </form>
        </template>

    </section>

</main>
<script>

const socket = new WebSocket("ws://localhost:8083?tipo_usuario=administrador&email=<?php echo $email_admin; ?>&contrasenia=<?php echo $password_admin; ?>");

socket.onopen = function (event) {

	//socket.send("{\"accion\": \"obtener_sala_activa\"}");
}

		const div_sala = document.getElementById('sala');
socket.onmessage = function (event) {

	el_mensaje = JSON.parse(event.data);

	switch (el_mensaje.accion){
	case "dar_sala_activa":
		if (el_mensaje.sala){

			div_sala.innerHTML = `<p id="sala-activa-texto">${el_mensaje.sala.codigo_acceso} - ${el_mensaje.sala.modalidad} - ${el_mensaje.sala.estado}</p>`;
			//button_empezar_juego =`<button class="boton" id="empezar_juego_boton">Empezar Juego</button>`;
			const button_empezar_juego = document.createElement('button');
			button_empezar_juego.classList.add("boton");
			button_empezar_juego.id="boton_empezar_juego";
			button_empezar_juego.innerHTML = `Empezar Juego`;
			button_empezar_juego.addEventListener('click', () =>{socket.send("{\"accion\" : \"empezar_juego\"}"); console.log("Funciona el condenado boton");});
			div_sala.appendChild(button_empezar_juego);
		} else {
			Mostrar_Formulario_Crear_Sala();
		}
		break;
		case 'error':
		case 'error_fatal':
			div_sala.innerHTML += `<p id="mensaje de error">${el_mensaje.mensaje}</p>`;
			break;

	// Falta agregar acá la respuesta de "crear_sala" del servidor
	// (por ejemplo un case "sala_creada" que redirija o actualice #sala).
	}

}

function Empezar_Juego(){
	socket.send("\"accion\" : \"empezar_juego\"");
}

// Muestra el formulario de crear sala dentro de #sala, clonando la plantilla.
function Mostrar_Formulario_Crear_Sala(){

	const div_sala = document.getElementById('sala');
	const plantilla = document.getElementById('plantilla-form-crear-sala');

	div_sala.innerHTML = "";
	div_sala.appendChild(plantilla.content.cloneNode(true));

	const form = document.getElementById('form-crear-sala');
	form.addEventListener('submit', function (evento) {
		evento.preventDefault();
		Crear_Sala();
	});
}

// Lee lo que el admin eligió en el formulario y arma los datos listos
// para mandar por WebSocket.
// Falta tomar estos datos y mandar el mensaje "crear_sala" por el
// socket (reemplaza lo que hacía el Crear_Sala() viejo, que tenía
// los valores hardcodeados).
function Crear_Sala(){

	const nombre_conjunto_seleccionado = document.getElementById('nombre_conjunto').value;
	const modalidad_seleccionada = document.querySelector('input[name="modalidad"]:checked')?.value;

	const mensaje_error = document.getElementById('mensaje-error-sala');

	if (!nombre_conjunto_seleccionado || !modalidad_seleccionada) {
		mensaje_error.textContent = "Elegí un conjunto y una modalidad antes de crear la sala.";
		mensaje_error.style.display = "block";
		return;
	}

	mensaje_error.style.display = "none";

	const datos_para_crear_sala = {
		accion: "crear_sala",
		sala: {
			nombre_conjunto: nombre_conjunto_seleccionado,
			modalidad: modalidad_seleccionada
		}
	};

	// Falta descomentar esto cuando el servidor esté listo para recibirlo.
	 socket.send(JSON.stringify(datos_para_crear_sala));

	console.log("Listo para mandar por WebSocket:", datos_para_crear_sala);
}

</script>


</body>
</html>
