<?php
namespace Servidor_Sala\Servidor_Sala;
require dirname(__DIR__) . '/../vendor/autoload.php';
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\WebSocket\MessageComponentInterface as WebSocketMessageComponentInterface;

//Los objetos que terminan con "_json" deben de ser string en formato json.
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
		$this->salas = [];

		$this->conexion_sql = mysqli_connect("mysql", "tecnologo", "tecnologo", "quiz_cooperativo", 3306);

		if (!$this->conexion_sql) {
			die("Error de conexión: " . mysqli_connect_error());
		}

		mysqli_set_charset($this->conexion_sql, "utf8");




		

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
		parse_str($parametros_string, $parametros_array);

		/* $parametros_array : son los parametros que el cliente le pasa al sevidor para conectarse.
		 *
		 * $parametros_array["tipo_usuario"] : es el tipo de usuario (administrador o jugador); la opción por defecto es jugador
		 *
		 *
		 * Si el usuario es administrador $parametros_array tiene ademas:
		 *
		 *	$parametros_array["contrasenia"] : la contrasenia del administrador
		 *	$parametros_array["email"] : el email del administrador
		 *
		 * Pero si es un jugador tiene:
		 *
		 * 	$parametros_array["codigo_acceso"] : el codigo de acceso de la sala
		 *	$parametros_array["nombre"] : el nombre del usuario
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

		$usuario_es_jugador = true;
		if (isset($parametros_array["tipo_usuario"])){
			$usuario_es_jugador =  $parametros_array["tipo_usuario"] === 'administrador';
		}

	




		//Primero van los filtros para en caso de que el usuario sea administrador

		if ($usuario_es_jugador){

			if (!isset($parametros_array["nombre"])){
				$this->Enviar_Error_y_Cerrar_Conexion($conexion , "No se pasó el nombre del Jugador.");
				return;
			}

			if (!isset($parametros_array["codigo_acceso"])){
				$this->Enviar_Error_y_Cerrar_Conexion($conexion , "No se pasó el codigó de acceso de la Sala.");
				return;
			}
			if (!isset($this->salas[$parametros_array["codigo_acceso"]])){
				$this->Enviar_Error_y_Cerrar_Conexion($conexion , 
					"No existe la sala con codigó de acceso : '".$parametros_array["codigo_acceso"]."' al que el Jugador con nombre: '".$parametros_array["nombre"]."' se quiere unir."
				);
				return;
			}
			$se_pudo_agregar_el_jugador = $this->salas[$parametros_array["codigo_acceso"]]->Agregar_Jugador($parametros_array["nombre"] , $conexion);
			if (!$se_pudo_agregar_el_jugador) {
				$this->Enviar_Error_y_Cerrar_Conexion($conexion , 
					"No se pudo agregar el Jugador: '".$parametros_array["nombre"]."' a la Sala: '".$parametros_array["codigo_acceso"]."'."
				);
				return;
			}

			$la_sala_a_la_que_se_unio_el_jugador_json = json_encode($this->salas["codigo_acceso"]->Dar_DT());
			$estado_de_la_sala_a_la_que_se_unio_el_jugador = $this->sala["codigo_acceso"]->Dar_Estado()->valor;
			if ($estado_de_la_sala_a_la_que_se_unio_el_jugador == "esperando"){
				$conexion->send(json_encode(["accion" => "informar_acceso_a_sala", "estado_sala" => $estado_de_la_sala_a_la_que_se_unio_el_jugador , "sala" => $la_sala_a_la_que_se_unio_el_jugador_json]);
				return
			}
		
			$conexion->send(json_encode(["accion" => "informar_acceso_a_sala", "estado_sala" => $estado_de_la_sala_a_la_que_se_unio_el_jugador);

		} else {
			if (!isset($parametros_array["email"])){
				$this->Enviar_Error_y_Cerrar_Conexion($conexion , "No se pasó el email del Administrador");
				return;
			}
			if (!isset($parametros_array["contrasenia"])){
				$this->Enviar_Error_y_Cerrar_Conexion($conexion , "No se pasó la contrasenia del Administrador");
				return;
			}

			//$login: true: el login salio bien; false: el login salio mal
			$login = true;
			if ($login){

				//Se agrega el admin a la lista de admins activos.

				if(!isset($this->administradores[$parametros_array["email"]])){
					$this->administradores[] = [$parametros_array["email"] => Administrador($parametros_array["email"] , $conexion)];
				}
				

				$conexion->send(json_encode(["accion" => "dar_sala_activa", "sala" => false]))

			}
			return;
		}

			


	}

	public function onClose(ConnectionInterface $conexion){
		if (count($this->salas) === 0){
			return;
		}

		//Borra el jugador almacinado en memoria (memoria volatil, no en la base de datos)
		//Ó elimina el ConnectionInterface del administrador y se remueve el administador de la lista de administrador conectados.

		$es_administrador = false;

		foreach($this->administradores as $un_administrador){
			if ($un_administrador->Eres_el_Administrador_de_Esta_Conexion($conexion)){
				$un_administrador->Unlogin();
				break;
			}
		}

		if ($es_administrador){
			return;
		}
		
		foreach($this->salas as $una_sala){
			if ($una_sala->Borrar_Jugador_con_Esta_Conexion($conexion)){
				return;
			}
		}


	}

	public function onMessage(ConnectionInterface $conexion, $msg){
		
		$el_mensaje = json_decode($msg, true);

		if (!isset($el_mensaje["accion"])){
			return;
		}
		switch ($el_mensaje["accion"]){
		case "obtener_sala_activa":
			//$la_sala_activa_json : es la activa del administrador cuya ConnectionInterface sea igual a $conexion

			$la_sala_activa_json = false;

			foreach ($this->administradores as $un_administrador){
				if ($un_administrador->Eres_el_Administrador_de_Esta_Conexion($conexion)){
					$la_sala_activa_json = json_encode($un_administrador->Dar_Sala_Activa());
				}
			}
			//Si no hay sala activa alguna, entonces $la_sala_activa_json es igual a false
			$conexion->send(json_encode(["accion"=> "dar_sala_activa", "sala" => $la_sala_activa_json]));
				break;
			;;
		case "crear_sala":

			
			//$el_mensaje["sala"]["nombre_conjunto"] : es el nombre del conjunto seleccionado.
			//$el_mensaje["sala"]["modalidad"] : es la modalidad seleccionada.

			if (!isset($el_mensaje["sala"])){
				return;
			}
			if (!isset($el_mensaje["sala"]["nombre_conjunto"])){
				return;
			}
			if (!isset($el_mensaje["sala"]["modalidad"])){
				return;
			}

			$el_administrador;

			$se_encontro_el_administrador=false;

			foreach($this->administradores as $un_administrador){
				if ($un_administrador->Eres_el_Administrador_de_Esta_Conexion($conexion)){
					$el_administrador = $un_administrador;
					$se_encontro_el_administrador = true;
				}
			}
			if (!$se_encontro_el_administrador){
				return;
			}
			
			$la_nueva_sala = Sala($this->conexion_sql , $el_mensaje["sala"]["modalidad"], $el_mensaje["sala"]["nombre_conjunto"], &$el_administrador);

			$la_nueva_sala_json = json_encode($la_nueva_sala->Dar_DT()); 

			//Se agrega la sala a la lista de salas del sevidor
	
			$this->salas[] = [ $la_nueva_sala->Dar_Codigo_Acceso() => $la_nueva_sala];



			$conexion->send(json_encode(["accion" => "dar_sala_activa", "sala" => $la_nueva_sala_json]));

			break;
			;;
		case "responder_pregunta_actual":

			//el_mensaje["respuesta"]["id"] : el numero de la respuesta; 0..* : respuestas; -1 : tiempo fuera.
			//
			//Se sabe cual es el jugador debida a que el ConnectionInterface de un jugador guardado es igual a $conexion

			Sala $la_sala;
			foreach ($this->salas as $una_sala){
				if ($una_sala->Responder_Pregunta($this->conexion_sql, $conexion , $el_mensaje["respuesta"]["id"])){
					$la_sala = $una_sala;
					break;
				}
			}
			if (!isset($la_sala)){
				return;
			}
			if (!$la_sala->Se_Termino_Ronda()){
				return;
			}
			
			$tabla_de_puntajes_y_conexiones_array = $la_sala->Dar_Tabla_de_Puntajes_y_Conexiones();

			$tabla_de_puntajes_array = [];

			$las_conexiones_de_los_jugadores = [];

			foreach($tabla_de_puntajes_y_conexiones_array as $nombre_jugador => $puntaje_y_conexion){
				$tabla_de_puntajes_array[] = [$nombre_jugador => $puntaje_y_conexion[0]];
				$las_conexiones_de_los_jugadores[] = $puntaje_y_conexion[1];
			}

			$tabla_de_puntajes_json = json_encode($tabla_de_puntajes_array);

			$numero_de_la_pregunta_actual = $la_sala->Dar_Numero_Pregunta_Actual();

			$datos_para_cambio_de_ronda_array = $la_sala->Terminar_Ronda();


			$la_respuesta_correcta = $datos_para_cambio_de_ronda_array["retroalimentacion_para_el_jugador"]["respuesta_correcta"];

			$puntaje_de_la_pregunta_contestada = $datos_para_cambio_de_ronda_array["retroalimentacion_para_el_jugador"]["puntos_de_la_pregunta"];
			
			$la_siguiente_pregunta_json = json_encode($datos_para_cambio_de_ronda_array["siguiente_pregunta"]);

			$se_termino_el_juego = $la_sala->Se_Termino_El_Juego();



			$modo_de_juego = $la_sala->Dar_Estado->value;


			$resultado_de_la_respuesta_json = json_encode([
						"numero_de_la_pregunta_actual" => $numero_de_la_pregunta_actual,
						"la_respuesta_correcta" => $la_respuesta_correcta,
						"puntaje_de_la_pregunta_contestada" => $puntaje_de_la_pregunta_conetestada,
						"tabla_de_puntajes" => $tabla_de_puntajes_json,
						"modo_de_juego" => $modo_de_juego
					]
);
				
			//Si la pregunta actual es la última, entonces $la_siguiente_pregunta_json es igual a false

			if (!$se_termino_el_juego) {
				foreach ($las_conexiones_de_los_jugadores as $una_conexion){
					$una_conexion->send(json_encode(["accion" => "cambio_de_pregunta",
						"resultado_de_la_respuesta" => $resultado_de_la_respuesta_json,
						"siguiente_pregunta" => $la_siguiente_pregunta_json
					]));
				}

			}else {
				foreach($las_conexiones_de_los_jugadores as $una_conexion){
					$una_conexion->send(json_encode(["accion" => "terminar_juego",
						"resultade_de_la_respuesta" => $resultado_de_la_respuesta_json
					]));

				}

			}
			break;
			;;

		case "empezar_juego":

			//Esta accion es dada por un admin, la sala en la cual se empieza a jugar es la sala asignada al admin.
			//El admin es el cual su ConnectionInterface is igual a $conexion

			$el_administrador_que_inicia_el_juego;

			foreach($this->administradores as $un_administrador) {
				if ($un_administrador->Eres_el_Administrador_de_Esta_Conexion($conexion)){
					$el_administrador_que_inicia_el_juego = $un_administrador;
					break;
				}
			}

			if (!isset($el_administrador_que_inicia_el_juego)){
				$this->Enviar_Error_y_Conservar_Conexion($conexion , "Este administrador no está logeado.");
				return;
			}
			
			if (!$el_administrador_que_inicia_el_juego->Tiene_Sala_Activa()){
				$this->Enviar_Error_y_Conservar_Conexion($conexion , "Este administrador no tiene sala activa");
				return;
			}

			if (!$el_administrador_que_inicia_el_juego->Tiene_Sala_Activa_y_Esta_en_Estado_Esperando()){
				$this->Enviar_Error_y_Conservar_Conexion($conexion , "La activa de este administrador no está en estado 'Esperando'");
				return;
			}

			if (!$el_administrador_que_inicia_el_juego->Empezar_Juego()){
				$this->Enviar_Error_y_Conservar_Conexion($conexion , "No se pudo emezar el juego");
				return;
			}

		

	
			$la_sala_json = json_encode($el_administrador_que_inicia_el_juego->Dar_Sala_Activa());


			//Lista conteniendo los ConnectionInterface del los jugadores en la sala; es un array no asociativo
			$las_conexiones_de_los_jugadores = $el_administrador_que_inicia_el_juego->Dar_las_Conexiones_de_los_Jugadors_de_la_Sala_Activa();


			$la_primera_pregunta_json = json_encode($el_administrador_que_inicia_el_juego->Dar_Pregunta_Actual_de_la_Sala_Activa());

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
	protected function Enviar_Error_y_Conservar_Conexion(ConnectionInterface $conexion, string $mensaje) {
		#echo $mensaje;
		$conexion->send(json_encode(["accion" => "error", "mensaje" => $mensaje]));
	}

	


}

Servidor_Sala::Correr();

?>
