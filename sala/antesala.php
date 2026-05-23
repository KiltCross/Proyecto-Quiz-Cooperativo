<?php
include "../includes/conexion.php";

$codigo_acceso =$_POST['codigo_acceso'];
$nombre_jugador = $_POST['nombre_jugador'];

$sql_existe_sala = "select * from sala where codigo_acceso=\"$nombre_jugador\"";
$sql_existe_nombre_jugador_en_sala = "SELECT COUNT(DISTINCT jugador.nombre) AS total FROM se_une INNER JOIN jugador ON se_une.id_jugador=jugador.id where se_une.id_sala=".$sala['id']." and jugador.nombre=\'".$nombre_jugador."\'";
$sql_agregar_jugador_a_bd = "INSERT INTO jugador (nombre) values ($nombre_jugador)";
$sql_obenter_id_del_nuevo_jugador = "SELECT jugador.id AS id FROM se_une INNER JOIN jugador ON se_une.id_jugador=jugador.id WHERE se_une.id_sala=".$sala['id']." and jugador.nombre=\'$nombre_jugador\'";
$sql_agregar_jugador_a_se_une = "INSERT INTO se_une (id_sala , id_jugador) VALUES (".$sala['id'].", $id_jugador)";

$resultado_consulta_existe_sala = mysqli_query($conn,$sql_existe_sala);

$existe_sala = mysqli_num_rows($resultado_consulta_existe_sala);

if (!$existe_sala){
		
	header("Location: http://localhost/Quiz_Cooperativo");
	die();

}else {
	$sala = mysqli_fetch_assoc($resultado_consulta_existe_sala);

	$resultado_consulta_existe_nombre_jugador_en_sala = mysqli_query($conn,$sql_existe_nombre_jugador_en_sala);
	$numero_de_jugadores_con_el_mismo_nombre_que_se_escogio = mysqli_fetch_assoc($resultado_consulta_existe_nombre_jugador_en_sala)['total'];

	if (!$numero_de_jugadores_con_el_mismo_nombre_que_se_escogio){
		
		?>
		<p>Lo siento, ese nombre ya fue escogido</p>
		<a href[="../index.php">Inicio</a>

<?php	

	}else {


	switch ($sala['estado']){

	case 'esperando':

		?>
		<p>Espere, por favor.</p>
<?php
		mysqli_query($conn,$sql_existe_nombre_jugador_en_sala);

		$resutado_consulta_obtener_id_del_nuevo_jugador = mysqli_query($conn,$sql_obenter_id_del_nuevo_jugador);
		$id_jugador = mysqli_fetch_assoc($resutado_consulta_obtener_id_del_nuevo_jugador)['id'];

		mysqli_query($conn,$sql_agregar_jugador_a_se_une);

		break;
	case 'jugando':
		?>
		<p>Lo siento, sala llena</p>

<?php
		break;
	case 'finalizada':
		?>
		<p>Lo siento, el juego ya finalizó</p>

<?php
		break;
}
	}
}
?>
