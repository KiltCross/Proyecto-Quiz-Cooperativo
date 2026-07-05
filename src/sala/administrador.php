<?php
namespace Servidor_Sala;


use Ratchet\ConnectionInterface;
#use Servidor_Sala\Sala;

class Administrador {
	private String $email;
	private ConnectionInterface $conexion;
	private $sala; //No se especifica el tipo para que no haya un error de depencencias circulares.

	public function __construct(string $email, ConnectionInterface $conexion){
		$this->email = $email;
		$this->conexion = $conexion;
	}
	public function __destruct(){
		$this->conexion->close();
	}

	public function Set_Conexion(ConnectionInterface $la_nueva_conexion){
		$this->conexion = $la_nueva_conexion;
	}

	public function Dar_Email() : string {
		
		return $this->email;

	}

	public function Dar_Conexion() : ConnectionInterface {

		return $this->conexion;
	}

	public function Eres_el_Administrador_de_Esta_Conexion(ConnectionInterface $conexionWS): bool{

		if (isset($this->conexion)){
			return $this->conexion == $conexionWS;
		} else {
			return false;
		}

	}

	public function Agregar_Sala( &$la_sala_a_agregar){
		
		$this->sala = &$la_sala_a_agregar;

	}

	public function Dar_Sala_Activa() : Array {

		$la_sala_dt = [];

		if (isset($this->sala)){
			$la_sala_dt = $this->sala->Dar_DT();

		}

		return $la_sala_dt;


	}

	public function Desactivar_Sala(){
		unset($this->sala);

	}

	public function Unlogin(){
		$this->conexion->close();
		unset($this->conexion);
	}

	public function Tiene_Sala_Activa() : bool {
		return isset($this->sala);
	}

	public function Tiene_Sala_Activa_y_Esta_en_Estado_Esperando() : bool{
		if (!isset($this->sala)){
			return false;
		}
		return $this->sala->Esta_en_Estado_Esperando();
	}

	public function Dar_las_Conexiones_de_los_Jugadors_de_la_Sala_Activa() : Array {
		return $this->sala->Dar_las_Conexiones_de_los_Jugadors();
	}

	public function Empezar_Juego(\mysqli $consulta_sql) : bool {
		return $this->sala->Empezar_Juego($consulta_sql , $this->conexion);
	}

	public function Dar_Pregunta_Actual_de_la_Sala_Activa() : Array {
		return $this->sala->Dar_Pregunta_Actual();
	}

	public function Esta_Logeado() : bool {
		return isset($this->conexion);
	}


}

?>
