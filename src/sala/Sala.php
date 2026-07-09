<?php
namespace Servidor_Sala;

require dirname(__DIR__). "/sala/Pregunta.php";
require dirname(__DIR__). "/sala/Jugador.php";
require_once dirname(__DIR__). "/sala/administrador.php";
use Ratchet\ConnectionInterface;
use Servidor_Sala\Pregunta;
use Servidor_Sala\Jugador;
use Servidor_Sala\Administrador;
use Servidor_Sala\Estado;
use Servidor_Sala\Modalidad;

#include "./enums.php";

class Sala {
	private String $codigo_acceso;
	private String $estado;
	private String $modalidad;
	private Int  $numero_pregunta_actual;
	private Int $puntaje_colectivo;
	private String $nombre_conjunto;

	private $el_administrador; 
	private Array $los_jugadores;
	private Array $las_preguntas;
	private int $ultima_respuesta_colectiva;

	public function __construct(\mysqli $conexion_bd, String $modalidad, string $nombre_conjunto, $el_administrador){ 
		$this->modalidad = $modalidad;
		$this->estado = 'esperando';
		$this->puntaje_colectivo = 0;
		$this->numero_pregunta_actual = 0;
		$this->nombre_conjunto = $nombre_conjunto;
		$this->el_administrador = &$el_administrador;
		$this->codigo_acceso = uniqid('', true);
		$this->codigo_acceso = str_split($this->codigo_acceso , 6)[0];
		#$this->codigo_acceso = "6a4373";
		$this->los_jugadores = [];
		$this->las_preguntas = [];
		$this->ultima_respuesta_colectiva = -1;

		$el_email_del_administrador = $el_administrador->Dar_Email();

		$get_admin_id = "select id from administrador where email='".$el_email_del_administrador."'";

		$get_admin_pasador = mysqli_query($conexion_bd , $get_admin_id);

		$admin_id = mysqli_fetch_assoc($get_admin_pasador)["id"];

		#echo "Variable 'admin_id' : $admin_id \n";

		$get_conjunto = "select id from conjunto where nombre='".$nombre_conjunto."' and id_admin=".$admin_id;

		$get_conjunto_id_pasador = mysqli_query($conexion_bd , $get_conjunto);

		$conjunto_id = mysqli_fetch_assoc($get_conjunto_id_pasador)["id"];

		$sql_consulta_obtener_preguntas = "SELECT * FROM pregunta WHERE id_conjunto=".$conjunto_id;
		
		$sql_consulta_para_registar_ESTO_ = "INSERT INTO sala (codigo_acceso , estado, modalidad , id_admin , id_conjunto ) VALUES ('".$this->codigo_acceso."' , 'esperando', '".$modalidad."', $admin_id, $conjunto_id)";

		echo "Variable 'sql_consulta_obtener_preguntas' : "  . $sql_consulta_obtener_preguntas . "\n";
		#echo "Variable 'sql_consulta_para_registar_ESTO_' : " . $sql_consulta_para_registar_ESTO_ . "\n";

		$_las_unidades_de_divertidos_extraidos_de_la_bodega = mysqli_query($conexion_bd , $sql_consulta_obtener_preguntas);
		#mysqli_fetch_assoc($_las_unidades_de_divertidos_extraidos_de_la_bodega);
		#echo "Antes V2\n";
		echo "Las Preguntas obtenidas al crear una Sala: \n";
		$cuantas_diverciones = 1;	
		while ($una_divertidilla = mysqli_fetch_assoc($_las_unidades_de_divertidos_extraidos_de_la_bodega)){
			var_dump($una_divertidilla);
			echo "\n";
			$this->las_preguntas[] = new Pregunta($conexion_bd, $cuantas_diverciones , $una_divertidilla["texto"] , $una_divertidilla["puntaje_base"], $una_divertidilla["id"]);

			var_dump($this->las_preguntas);
			echo "\n";
			$cuantas_diverciones++;
		}


		#echo "Antes D V2\n";

		#mysqli_query($conexion_bd, $sql_consulta_para_registar_ESTO_);

		$el_administrador->Agregar_Sala($this);



	}


	public function __destruct(){
		if (isset($this->las_preguntas)){
			for ($i=0;$i<count($this->las_preguntas); $i++){
				if ($this->las_preguntas[$i]){
					unset($this->las_preguntas[$i]);
				}
		}
		}

		if (isset($this->los_jugadores)){
			foreach ($this->los_jugadores as $un_jugador){
					$un_jugador->__destruct();
		}
		}

		if ($this->el_administrador){
			$this->el_administrador->Desactivar_Sala();
		}

		unset($this->el_administrador);

	

	}
	public function Terminar_Juego(\mysqli $conexion_sql){
		echo "Se Termina el Juego.\n";
			if (isset($this->las_preguntas)){
				echo "Se Borran las preguntas al terminar el juego.\n";
		for ($i=0;$i<count($this->las_preguntas); $i++){
				if ($this->las_preguntas[$i]){
					unset($this->las_preguntas[$i]);
				}
		}
		}

		if (isset($this->los_jugadores)){
			echo "Se borran los jugadores al terminar el juego.\n";
			foreach ($this->los_jugadores as $un_jugador){
					$un_jugador->__destruct();
		}
		}

		
		if ($this->el_administrador){
			echo "Se le pide al administador que desactive su sala al terminar el juego.\n";
			$this->el_administrador->Desactivar_Sala();

		}
		//unset($this->el_administrador);

		$consulta_sql = "update sala set estado='finalizada' where codigo_acceso=".$this->codigo_acceso;

		#mysqli_query($conexion_bd , $consulta_sql);
		$this->estado="finalizada";

	}

	public function Dar_DT() : Array {
		$el_returno =  [
			"codigo_acceso" => $this->codigo_acceso,
			"estado" => $this->estado,
			"modalidad" => $this->modalidad,
			"numero_pregunta_actual" => $this->numero_pregunta_actual,
			"puntaje_colectivo" => $this->puntaje_colectivo,
			"nombre_conjunto" => $this->nombre_conjunto,
			"numero_de_jugadores_conectados" => count($this->los_jugadores),
			"numero_de_preguntas" => count($this->las_preguntas)
		];

		return $el_returno;

	}


	public function Se_Termino_Ronda() : bool {

		$se_termino_ronda = true;

		echo "Los Jugadores que han respondido: \n";

		foreach($this->los_jugadores as $un_jugador){
			$se_termino_ronda = $se_termino_ronda && $un_jugador->Ya_Respondio();

			echo "+ '".$un_jugador->Dar_Nombre()."' ; ". $un_jugador->Ya_Respondio()." ; ".$se_termino_ronda."\n";


		}

		return $se_termino_ronda;
	}

	public function Terminar_Ronda() : Array {

		if ($this->modalidad === 'cooperativa'){
		$la_opcion_respondida= array(0,-1);;
		$las_respuestas = [];
		foreach ($this->los_jugadores as $un_jugador){
			
			$un_jugador->Cambiar_Ronda();

			if(!isset($las_respuestas[$un_jugador->Dar_Opcion_Respondida()]) ){
				$las_respuestas[$un_jugador->Dar_Opcion_Respondida()] = 1;
			} else {
				$las_respuestas[$un_jugador->Dar_Opcion_Respondida()]++;
			}

		}
		foreach($las_respuestas as $id_respuesta => $numero_de_instancias){
			if ($la_opcion_respondida[1]< $numero_de_instancias){
				$la_opcion_respondida = [$id_respuesta, $numero_de_instancias];
			}
		}
		echo "Las respuestas al terminar la ronda en Modalidad cooperativa: \n";
		var_dump($las_respuestas);
		echo "\n";
		echo "La opcion respondida al terminar la ronda en Modalidad cooperativa: \n";
		var_dump($la_opcion_respondida);
		echo "\n";

		echo "'numero_pregunta_actual' en 'Terminar_Ronda()': ".$this->numero_pregunta_actual." \n";
		echo "Número de pregutnas en 'Terminar_Ronda()': ".count($this->las_preguntas)." \n";

		$this->ultima_respuesta_colectiva = $la_opcion_respondida[0];

		$cambio_de_puntaje = $this->las_preguntas[$this->numero_pregunta_actual]->Responder($la_opcion_respondida[0]);

		$this->puntaje_colectivo += $cambio_de_puntaje;
		
		} else {
			foreach($this->los_jugadores as $un_jugador){
				$un_jugador->Cambiar_Ronda();
			}
		}


		$retroalimentacion_para_el_jugador = [
			
			"respuesta_correcta" => $this->las_preguntas[$this->numero_pregunta_actual]->Dar_Respuesta_Correcta(),
			"puntos_de_la_pregunta" => $this->las_preguntas[$this->numero_pregunta_actual]->Dar_Puntos()

		];

			
		$siguiente_pregunta = [];

				$this->numero_pregunta_actual++;
			if ( ($this->numero_pregunta_actual) < count($this->las_preguntas) ){
				$siguiente_pregunta = $this->las_preguntas[$this->numero_pregunta_actual]->Dar_DT();
			} 


				return ["retroalimentacion_para_el_jugador" => $retroalimentacion_para_el_jugador , "siguiente_pregunta" => $siguiente_pregunta];


	}

	public function Dar_Ultima_Respuesta_Colectiva() : int {
		return $this->ultima_respuesta_colectiva;
	}
	public function Set_Ultima_Respuesta_Colectiva(int $la_nueva_respuesta){
		$this->ultima_respuesta_colectiva=$la_nueva_respuesta;
	}		

	public function Se_Termino_El_Juego() : bool {

		echo "Numero de Preguntas en 'Se_Termino_El_Juego()' : ".count($this->las_preguntas)." \n";
		echo "Número de la Pregunta Actual en 'Se_Termino_El_Juego()' : ".$this->numero_pregunta_actual." \n";

		if (($this->numero_pregunta_actual) == count($this->las_preguntas)){
			echo "Se_Termino_El_Juego(): Se Terminó el Juego.\n";
		} else {
			echo "Se_Termino_El_Juego(): El Juego continua.\n";
		}
		
		return ($this->numero_pregunta_actual) == count($this->las_preguntas);
	}

	public function Dar_Codigo_Acceso() : string{
		return $this->codigo_acceso;
	}

	public function Dar_Estado() : string{

		return $this->estado;
	}

	public function Dar_Modalidad(): string{

		return $this->modalidad;
	}

	public function Dar_Numero_Pregunta_Actual() : int {

		return $this->numero_pregunta_actual;
	}

	public function Dar_Puntaje_Colectivo() : int {

		return $this->puntaje_colectivo;
	}

	public function Dar_Nombre_Conjunto() : string {

		return $this->nombre_conjunto;

	}

	public function Set_Estado(string $el_nuevo_estado){
		$this->estado = $el_nuevo_estado;
	} 

	public function Set_Modalidad(string $la_nueva_modalidad){
		$this->modalidad = $la_nueva_modalidad;
	}

	public function Set_Numero_Pregunta_Actual(int $nuevo_numero_de_la_pregunta_actual){
		$this->numero_pregunta_actual = $nuevo_numero_de_la_pregunta_actual;
	}

	public function Set_Puntaje_Colectivo(int $el_nuevo_puntaje_colectivo){
		$this->puntaje_colectivo = $el_nuevo_puntaje_colectivo;
	}


	public function Responder_Pregunta(\mysqli $conexion_bd , ConnectionInterface $la_conexion_del_jugador, int $la_respuesta) : bool {
		$jugador_encontrado = false;


		$el_jugador;

		echo "Punto dos\n";

		foreach ($this->los_jugadores as $un_jugador){
			if ($un_jugador->Eres_El_Jugador_con_Esta_Conexion($la_conexion_del_jugador)){
				$jugador_encontrado=true;
				$el_jugador=$un_jugador;
			}

		}




		if ($jugador_encontrado){
			if (!$el_jugador->Ya_Respondio()){

			$cambio_de_puntaje = $this->las_preguntas[$this->numero_pregunta_actual]->Responder($la_respuesta);
				$el_jugador->Respondio($la_respuesta);

			switch ($this->modalidad){

			case 'cooperativa':
					break;
			case 'competitiva':
				$el_jugador->Cambiar_Puntaje($cambio_de_puntaje);
				break;
			}
			}
		}

		$consulta_sql = "UPDATE sala SET pregunta_actual=".$this->numero_pregunta_actual." where codigo_acceso='".$this->codigo_acceso."'";
		#mysqli_query($conexion_bd , $consulta_sql);

		return $jugador_encontrado;

	}


	public function Dar_Tabla_de_Puntajes_y_Conexiones() : Array {

		$retorno = [];

			foreach ($this->los_jugadores as $nombre_jugador => $un_jugador){

				$retorno[$nombre_jugador] = [$un_jugador->Dar_Puntaje() , $un_jugador->Dar_Conexion()];
			}
		return $retorno;

	}


	public function Empezar_Juego(\mysqli $conexion_bd, ConnectionInterface $conexion_administrador) : bool {

		if ($this->el_administrador->Eres_el_Administrador_de_Esta_Conexion($conexion_administrador) && $this->estado == 'esperando') {

			$this->estado = 'jugando';
			return true;
		}
		$consulta_sql = "update sala set estado='jugando' where codigo_acceso=".$this->codigo_acceso;
		#mysqli_query($conexion_bd , $consulta_sql);
		return false;
	}

	public function Dar_las_Conexiones_de_los_Jugadors() : Array {

		$las_conexiones = [];

		foreach ($this->los_jugadores as $nombre_jugador => $un_jugador){
			$las_conexiones[] = $un_jugador->Dar_Conexion();
		}

		return $las_conexiones;
	}

	public function Borrar_Jugador_con_Esta_Conexion(ConnectionInterface $la_conexion_del_jugador) : bool {
		$es_el_jugador = false;

		$contador_de_jugadores = 0;
		
		foreach ($this->los_jugadores as $un_jugador) {
			$es_el_jugador = $un_jugador->Eres_El_Jugador_con_Esta_Conexion($la_conexion_del_jugador);
			$contador_de_jugadores++;
			if ($es_el_jugador){
				array_splice($this->los_jugadores,$contador_de_jugadores,1);
				$la_conexion_del_jugador->close();
				break;
			}
		}

		return $es_el_jugador;


	}

	public function Agregar_Jugador(\mysqli  $la_conexion_bd, String $nombre_jugador , ConnectionInterface $la_conexion_ws) : bool {

		if ($this->estado !== 'esperando'){
			return false;
		}

		$sql_consulta = "INSERT INTO jugador (nombre) values ('$nombre_jugador')";
		if (!isset($this->los_jugadores[$nombre_jugador])){
			$this->los_jugadores[$nombre_jugador] = new Jugador($nombre_jugador , $la_conexion_ws);
			#mysqli_query($la_conexion_bd , $sql_consulta);
			return true;
		}
		return false;

	}

	/*
	public function Se_Termino_El_Juego() : bool {
		return (count($this->los_jugadores) - 1) === $this->numero_pregunta_actual;
	}
	 */
	public function Esta_en_Estado_Esperando() : bool {
		return $this->estado === 'esperando';
	}

	public function Dar_Pregunta_Actual() : Array {
		echo "Preguntas Guadadas Cuando 'Dar_Pregunta_Actual()': \n";
		var_dump($this->las_preguntas);
		echo "\n";
		return $this->las_preguntas[$this->numero_pregunta_actual]->Dar_DT();
	}
	public function Dar_Numero_de_Preguntas() :int {

		return count($this->las_preguntas);
	}

	public function Existe_Jugador_con_Esta_Conexion(ConnectionInterface $la_conexion) : bool{
		if (!isset($this->los_jugadores)){
			return false;
		}
		if (count($this->los_jugadores)===0){
			return false;
		}
		$existe = true;

		foreach($this->los_jugadores as $un_jugador){
			$existe = $existe && $un_jugador->Eres_El_Jugador_con_Esta_Conexion($la_conexion);
		}
		return $existe;

	}

	public function Existe_Jugador_con_Este_Nombre(string $el_nombre_del_jugador){
		return isset($this->los_jugadores[$el_nombre_del_jugador]);
	}
}


?>
