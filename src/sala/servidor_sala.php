<?php
namespace quiz_cooperativo\Sala;
require dirname(__DIR__) . '/../vendor/autoload.php';
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\WebSocket\MessageComponentInterface as WebSocketMessageComponentInterface;

//Los objetos que terminan con "_json" deben de ser string en fomrato json.
//
//Array pueden ser pasado a formado json a través de json_encode().
//Aunque no se si esta función es recursiva.
//Ej: [1 , "algo" , ["1 dentro" , "2 dentro"] , "otra cosa más", 3]

class Servidor_Sala implements WebSocketMessageComponentInterface {

	protected $salas;
	private $administradores;

	private static $instancia;


	private $conexion_sql;

	protected final function __construct(){

		$this->administradores = [];	

		$this->conexion_sql = mysqli_connect("mysql", "tecnologo", "tecnologo", "quiz_cooperativo", 3306);

		if (!$this->conexion_sql) {
			die("Error de conexión: " . mysqli_connect_error());
		}

		mysqli_set_charset($this->conexion_sql, "utf8");

		$las_salas_en_la_bd = $this->conexion_sql->query("SELECT * FROM sala");

		if (!$las_salas_en_la_bd){
			return;
		}
		



		

	}

	public static function Correr()  {

		if (!isset(self::$instancia)){

			#self::$instancia = new Sala();

			self::$instancia = IoServer::factory(
			new HttpServer(
				new WsServer(
					new Sala()
				)
			),8083
		);

			self::$instancia->run();
		}

		return self::$instancia;
	}



	public function onOpen(ConnectionInterface $conexion){
		
		if (count($this->salas) === 0){
			echo "Error al ingresar a la sala: No hay salas.\n";
			return;
		}

		$parametros_string = $conexion->httpRequest->getUri()->getQuery();
		parse_str($parametros_string, $parametros_json);

		/* $parametros_json : son los parametros que el cliente le pasa al sevidor para conectarse.
		 *
		 * $parametros_json["tipo_usuario"] : es el tipo de usuario (administrador o jugador); la opción por defecto es jugador
		 *
		 * $parametros_json["nombre"] : el nombre del usuario
		 *
		 * Si el usuario es administrador $parametros_json tiene ademas:
		 *
		 *	$parametros_json["contrasenia"] : la contrasenia del administrador
		 *
		 * Pero si es un jugador tiene:
		 *
		 * 	$parametros_json["codigo_acceso"] : el codigo de acceso de la sala
		 *
		 *
		 * */

		//Luegos vienen una serie de filtros con el siquiene formato:
		
		$condicion_filtro = false;

		if ($condicion_filtro) {
			$mensaje_de_error = "Mensaje de Error";
			$this->Enviar_Error_y_Cerrar_Conexion($conexion, $mensaje_de_error);
			return;

		}

		//Primero van los filtros para en caso de que el usuario sea administrador
	
		if (isset($parametros_json["tipo_usuario"])) {
		if ($parametros_json["tipo_usuario"]=='administrador'){

			//$login: true: el login salio bien; false: el login salio mal
			$login = true;
			if ($login){

				//Se agrega el admin a la lista de admins activos.
				

				$conexion->send(json_encode(["accion" => "dar_sala_activa", "sala" => false]))

			}
			return;
		}
		}

			
		//Luego los filtros por si el usuario es jugador

		$la_sala_a_la_que_se_unio_el_jugador_json = [];

		$estado_de_la_sala_a_la_que_se_unio_el_jugador = "esperado";

		
		if ($estado_de_la_sala_a_la_que_se_unio_el_jugador == "esperando"){
			$conexion->send(json_encode(["accion" => "informar_acceso_a_sala", "estado_sala" => $estado_de_la_sala_a_la_que_se_unio_el_jugador , "sala" => $la_sala_a_la_que_se_unio_el_jugador_json]);
			return
		}
		
			$conexion->send(json_encode(["accion" => "informar_acceso_a_sala", "estado_sala" => $estado_de_la_sala_a_la_que_se_unio_el_jugador);

		#echo "Conexión exitosa\n";



	}

	public function onClose(ConnectionInterface $conexion){
		if (count($this->salas) === 0){
			return;
		}

		//Borra el jugador almacinado en memoria (memoria volatil, no en la base de datos)
		//Ó elimina el ConnectionINterface del administrador y se remueve el administador de la lista de administrador conectados.

		$conexion->close();

	}

	public function onMessage(ConnectionInterface $conexion, $msg){
		
		$el_mensaje = json_decode($msg, true);

		if (!isset($el_mensaje["accion"])){
			return;
		}
		switch ($el_mensaje["accion"]){
		case "obtener_sala_activa":
			//$la_sala_activa_json : es la activa del administrador cuya ConnectionInterface sea igual a $conexion (Ej: $this->salas["algo"]["Administrador"] == $conexion)
			$la_sala_activa_json = [];
			//Si no hay sala activa alguna, entonces $la_sala_activa_json es igual a false
			$conexion->send(json_encode(["accion"=> "dar_sala_activa", "sala" => $la_sala_activa_json]));
				break;
			;;
		case "crear_sala":

			
			//$el_mensaje["sala"]["nombre_conjunto"] : es el nombre del conjunto seleccionado
			//$el_mensaje["sala"]["modalidad"] : es la modalidad seleccionada

			$la_nueva_sala_json = []; 

			//Se agrega la sala a la lista de salas del sevidor

			//Se inserta la nueva sala en la base de datos

			$conexion->send(json_encode(["accion" => "dar_sala_activa", "sala" => $la_nueva_sala_json]));

			break;
			;;
		case "responder_pregunta_actual":

			//el_mensaje["respuesta"]["id"] : el numero de la respuesta; 0..* : respuestas; -1 : tiempo fuera.
			//
			//Se sabe cual es el jugador debida a que el ConnectionInterface de un jugador guardado es igual a $conexion

			$numero_de_la_pregunta_actual = 5;

			$la_respuesta_correcta = 3;

			$puntaje_de_la_pregunta_contestada = 100;
			
			$tabla_de_puntajes_json = "";

			$se_termino_el_juego = false;

			$se_termino_la_ronda = true;

			$la_siguiente_pregunta_json = "";

			$las_conexiones_de_los_jugadores = [];

			$resultado_de_la_respuesta_json = json_encode([
						"numero_de_la_pregunta_actual" => $numero_de_la_pregunta_actual,
						"la_respuesta_correcta" => $la_respuesta_correcta,
						"puntaje_de_la_pregunta_contestada" => $puntaje_de_la_pregunta_conetestada,
						"tabla_de_puntajes" => $tabla_de_puntajes_json
					]
);
				
			//Si la pregunta actual es la última, entonces $la_siguiente_pregunta_json es igual a false

			if ($se_termino_la_ronda) {
				foreach ($las_conexiones_de_los_jugadores as $una_conexion){
					$una_conexion->send(json_encode(["accion" => "cambio_de_pregunta",
						"resultado_de_la_respuesta" => $resultado_de_la_respuesta_json,
						"siguiente_pregunta" => $la_siguiente_pregunta_json
					]));
				}

			}
			if ($se_termino_el_juego){
				foreach($las_conexiones_de_los_jugadores as $una_conexion){
					$una_conexion->send(json_encode(["accion" => "terminar_juego"]));

				}

			}
			break;
			;;

		case "empezar_juego":

			//Esta accion es dada por un admin, la sala en la cual se empieza a jugar es la sala asignada al admin.
			//El admin es el cual su ConnectionInterface is igual a $conexion

	
			$la_sala_json = [];


			//Lista conteniendo los ConnectionInterface del los jugadores en la sala; es un array no asociativo
			$las_conexiones_de_los_jugadores = [];


			$la_primera_pregunta_json = [];

			foreach ($las_conexiones_de_los_jugadores as $una_conexion_de_un_jugador){
				
				$una_conexion_de_un_jugador->send(json_encode(["accion" => "avisar_comienzo_del_juego" , "primera_pregunta" => $la_primera_pregunta_json]));

			}

			//Se cambia el estado de la sala a "jugando"
			

			$conexion->send(json_encode(["accion" => "dar_sala_activa" , "sala" => $la_sala_json]));



		}
	}

	public function onError(ConnectionInterface $conexion, \Exception $e) {
		echo "Error: {$e->getMessage()}\n";
		$this->Enviar_Error_y_Cerrar_Conexion($e->getMessage(());
	}
	
	protected function Enviar_Error_y_Cerrar_Conexion(ConnectionInterface $conexion, string $mensaje) {
		#echo $mensaje;
		$conexion->send(json_encode(["accion" => "error", "mensaje" => $mensaje]))
		$conexion->close();

	}
	


}

Servidor_Sala::Correr();

?>
