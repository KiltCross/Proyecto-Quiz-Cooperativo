<?php
namespace Servidor_Sala\Sala;

use Ratchet\ConnectionInterface;
use Servidor_Sala\Pregunta;
use Servidor_Sala\Jugador;
use Servidor_Sala\Administrador;

include "./enums.php";

class Sala {
	private String $codigo_acceso;
	private Estado $estado;
	private Modalidad $modalidad;
	private Int  $numero_pregunta_actual;
	private Int $puntaje_colectivo;
	private String $nombre_conjunto;

	private $el_administador; 
	private Array $los_jugadores;
	private Array $las_preguntas;

	public function __construct(mysqli $conexion_bd, Modalidad $modalidad, string $nombre_conjunto, &$el_administrador){ 
		$this->modalidad = $modalidad;
		$this->estado = Estado::Esperando;
		$this->puntaje_colectivo = 0;
		$this->numero_pregunta_actual = 0;
		$this->nombre_conjunto = $nombre_conjunto;
		$this->el_administador = &$el_administador;
		$this->codigo_acceso = uniqid('', true);

		$el_email_del_administrador = $el_administador->Dar_Email();

		$sql_consulta_obtener_preguntas = "SELECT * FROM pregunta WHERE id_conjunto IN (SELECT id FROM conjunto WHERE nombre=$nombre_conjunto and id_admin IN (SELECT id FROM administradore where email=$el_email_del_administrador))";
		
		$sql_consulta_para_registar_ESTO_ = "INSERT INTO sala (codigo_acceso , estado, modalidad , id_admin , id_conjunto ) VALUES ('".$this->codigo_acceso."' , 'esperando', '".$modalidad->value."', (SELECT id FROM administrador where email=$el_email_del_administrador) , (SELECT id FROM conjunto WHERE nombre=$nombre_conjunto))";

		$_las_unidades_de_divertidos_extraidos_de_la_bodega = mysqli_query($conexion_bd , $sql_consulta_obtener_preguntas);

		$cuantas_diverciones = 1;	
		while ($una_divertidilla = mysqli_fetch_assoc($_las_unidades_de_divertidos_extraidos_de_la_bodega)){
			$this->las_preguntas[] = Pregunta($conexion_bd, $cuantas_diverciones , $una_divertidilla["texto"] , $una_divertidilla["puntaje_base"], $nombre_conjunto , $el_email_del_administrador);

			$cuantas_diverciones++;
		}

		mysqli_query($conexion_bd, $sql_consulta_para_registar_ESTO_);

		$el_administador->Agregar_Sala(&$this);



	}


	public function __destruct(){
		for ($i=0;$i<count($this->las_preguntas); $i++){
			$this->las_preguntas[$i]->__destruct();
		}

		for ($i=0;$i<count($this->los_jugadores); $i++){
			$this->los_jugadores[$i]->__destruct();
		}
		
		$this->el_administador->Desactivar_Sala();

		unset($this->el_administador);

	}

	public function Dar_DT() : Array {
		$el_returno =  [
			"codigo_acceso" => $this->codigo_acceso,
			"estado" => $this->estado,
			"modalidad" => $this->modalidad,
			"numero_pregunta_actual" => $this->numero_pregunta_actual,
			"puntaje_colectivo" => $this->puntaje_colectivo,
			"nombre_conjunto" => $this->nombre_conjunto,
			"numero_de_jugadores_conectados" => count($this->los_jugadores)
		];

		return $el_returno;

	}


	public function Se_Termino_Ronda() : boolean {

		$se_termino_ronda = true;

		foreach($this->los_jugadores as $un_jugador){
			$se_termino_ronda = $se_termino_ronda and $un_jugador->Ya_Respondio();
		}

		return $se_termino_ronda;
	}

	public function Terminar_Ronda() : Array {
		foreach ($this->los_jugadores as $un_jugador){
			
			$un_jugador->Cambiar_Ronda();

		}

		$retroalimentacion_para_el_jugador = [
			
			"respuesta_correcta" => $this->las_preguntas[$this->numero_pregunta_actual]->Dar_Respuesta_Correcta(),
			"puntos_de_la_pregunta" => $this->las_preguntas["$this->numero_pregunta_actual"]->Dar_Puntos()

		];

			
		$siguiente_pregunta = []

			if ( ($this->numero_pregunta_actual + 1) === count($this->las_preguntas) ){
				$this->numero_pregunta_actual++;
				$siguiente_pregunta = $this->las_preguntas[$this->numero_pregunta_actual]->Dar_DT();
			}


				return ["retroalimentacion_para_el_jugador" => $retroalimentacion_para_el_jugador , "siguiente_pregunta" => $siguiente_pregunta];


	}

	public function Se_Termino_El_Juego() : boolean {
		
		return ($this->numero_pregunta_actual +1) === count($this->las_preguntas);
	}

	public function Dar_Codigo_Acceso() : string{
		return $this->codigo_acceso;
	}

	public function Dar_Estado() : Estado{

		return $this->estado;
	}

	public function Dar_Modalidad(): Modalidad{

		return $this->modalidad;
	}

	public function Dar_Numero_Pregunta_Actual() : integer {

		return $this->numero_pregunta_actual;
	}

	public function Dar_Puntaje_Colectivo() : integer {

		return $this->puntaje_colectivo;
	}

	public function Dar_Nombre_Conjunto() : string {

		return $this->nombre_conjunto;

	}

	public function Set_Estado(Estado el_nuevo_estado){
		$this->estado = el_nuevo_estado;
	} 

	public function Set_Modalidad(Modalidad la_nueva_modalidad){
		$this->modalidad = la_nueva_modalidad;
	}

	public function Set_Numero_Pregunta_Actual(integer nuevo_numero_de_la_pregunta_actual){
		$this->numero_pregunta_actual = nuevo_numero_de_la_pregunta_actual;
	}

	public function Set_Puntaje_Colectivo(integer el_nuevo_puntaje_colectivo){
		$this->puntaje_colectivo = el_nuevo_puntaje_colectivo;
	}


	public function Responder_Pregunta(ConnectionInterface $la_conexion_del_jugador, integer $la_respuesta) : boolean {
		$es_el_jugador = false;

		$el_jugador;

		foreach ($this->los_jugadores as $un_jugador){
			if ($un_jugador->Eres_El_Jugador_con_Esta_Conexion($la_conexion_del_jugador)){
				$es_el_jugador=true;
				$el_jugador=$un_jugador;
				break;
			}
		}

		if ($es_el_jugador){
			$cambio_de_puntaje = $this->las_preguntas[$this->numero_pregunta_actual]->Responder($la_respuesta);

			switch ($this->modalidad){

			case Modalidad::Cooperativo:
				$this->puntaje_colectivo += $cambio_de_puntaje;

			case Modalidad::Competitivo:
				$el_jugador->Cambiar_Puntaje($cambio_de_puntaje);
				break;
			}
		}
		return $es_el_jugador;

	}


	public function Dar_Tabla_de_Puntajes_y_Conexiones() : Array {

		$retorno = []

			foreach ($this->los_jugadores as $nombre_jugador => $un_jugador){

				$retorno[] = [$nombre_jugador => [$un_jugador->Dar_Puntaje() , $un_jugador->Dar_Conexion()]];
			}
		return $retorno;

	}


	public function Empezar_Juego(ConnectionInterface $conexion_administador) : boolean {

		if ($this->el_administador->Eres_El_Jugador_con_Esta_Conexion($conexion_administador) && $this->estado == Estado::Esperando) {

			$this->estado = Estado::Jugando;
			return true;
		}
		return false;
	}

	public function Dar_las_Conexiones_de_los_Jugadors() : Array {

		$las_conexiones = [];

		foreach ($this->los_jugadores as $un_jugador){
			$las_conexiones[] = $un_jugador->Dar_Conexion();
		}

		return $las_conexiones;
	}

	public function Borrar_Jugador_con_Esta_Conexion(ConnectionInterface $la_conexion_del_jugador) : boolean {
		$es_el_jugador = false;

		$contador_de_jugadores = 0;
		
		foreach ($this->los_jugadores as $un_jugador) {
			$es_el_jugador = $un_jugador->Eres_El_Jugador_con_Esta_Conexion($la_conexion_del_jugador);
			$contador_de_jugadores++;
			if ($es_el_jugador){
				array_splice($this->los_jugadores,$contador_de_jugadores,1);
				break;
			}
		}

		return $es_el_jugador;


	}

	public function Agregar_Jugador(mysqli $la_conexion_bd, String $nombre_jugador , ConnectionInterface $la_conexion_ws) {

		$sql_consulta = "INSERT INTO jugador (nombre) values ($nombre_jugador)";
		if (!isset($this->los_jugadores[$nombre_jugador])){
			$this->los_jugadores[] = [$nombre_jugador => Jugador($nombre_jugador , $la_conexion_ws)];
			mysqli_query($la_conexion_bd , $sql_consulta);
		}

	}

	public function Dar_DT() : Array {
		$el_dt_de_retorno = [
			"codigo_acceso" => $this->codigo_acceso,
			"estado" => $this->estado,
			"modalidad" => $this->modalidad,
			"numero_pregunta_actual" => $this->numero_pregunta_actual,
			"puntaje_colectivo" => $this->puntaje_colectivo,
			"nombre_conjunto" => $this->nombre_conjunto,
			"numero_de_jugadores_conectados" => count($this->los_jugadores)
		];

		return $el_dt_de_retorno;
	}

	public function Se_Termino_El_Juego() : boolean {
		return (count($this->los_jugadores) - 1) == $this->numero_pregunta_actual;
	}
}


?>
