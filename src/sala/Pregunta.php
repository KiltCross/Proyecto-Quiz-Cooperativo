<?php
namespace Servidor_Sala;

class Pregunta {

  
    private int $numero;
    private string $texto;
    private int $puntaje;
    private array $opciones;

    /*
    indice (dentro de $opciones) que corresponde a la respuesta correcta
    */
    private int $respuesta_correcta;


    /*
    public function __construct(int $id, string $texto, int $puntaje, array $opciones, int $respuesta_correcta) {
        $this->id = $id;
        $this->texto = $texto;
        $this->puntaje = $puntaje;
        $this->opciones = $opciones;
        $this->respuesta_correcta = $respuesta_correcta;
    }
     */

    public function __construct(\mysqli $conexion_bd, int $numero, string $texto, int $puntaje, int $id_pregunta) {
        $this->numero = $numero;
        $this->texto = $texto;
	$this->puntaje = $puntaje;
	$this->opciones = [];
	$this->respuesta_correcta = -1;


	$consulta_sql = "SELECT * FROM opciones  WHERE id_pregunta=$id_pregunta";

	echo "La consulta utilizadad para obtener las opciones al crear una instancia de Pregunta; es: \n";
	echo $consulta_sql;
	echo "\n";

	$contador_de_opciones = 1;	

	$las_opciones_extraidas_de_la_bd = mysqli_query($conexion_bd,$consulta_sql);
	
	echo "Las Opciones de la Pregunta ".$this->numero.": \n";
	while ($una_opcion = mysqli_fetch_assoc($las_opciones_extraidas_de_la_bd)){
		echo "+";
		var_dump($una_opcion);
		echo "\n";
		$this->opciones[] = $una_opcion["texto"];
		if ($una_opcion["es_correcta"] === '1'){
			$this->respuesta_correcta = $contador_de_opciones;
		}
		$contador_de_opciones++;

	}
	
    }



    /*
    recibe el indice de la opcion que elige el jugador
    devuelve los puntos ganados: el puntaje completo si acerto, 0 si no
    */
    public function Responder(int $respuesta): int {
        if ($respuesta === $this->respuesta_correcta) {
            return $this->puntaje;
	} else {

		return $this->puntaje * -1;
	}
    }


    /*
    devuelve el indice de la opcion correcta
    se usa al terminar la ronda para mandarle la retroalimentacion al jugador
    */
    public function Dar_Respuesta_Correcta(): int {
        return $this->respuesta_correcta;
    }


 
    public function Dar_Puntos(): int {
        return $this->puntaje;
    }


    /*
    devuelve las opciones de respuesta de la pregunta
    */
    public function Dar_Opciones(): array {
        return $this->opciones;
    }


    public function Dar_Id(): int {
        return $this->id;
    }


    /*
    devuelve el texto de la pregunta
    */
    public function Dar_Texto(): string {
        return $this->texto;
    }

    public function Dar_DT() : Array {

	    $los_datos = [
			"texto" => $this->texto,
			"numero" => $this->numero,
			"puntos" => $this->puntaje,
			"opciones" => $this->opciones
	    ];

	    return $los_datos;
    }
}
