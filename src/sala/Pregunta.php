<?php


class Pregunta {

  
    private int $id;
    private string $texto;
    private int $puntaje;
    private array $opciones;

    /*
    indice (dentro de $opciones) que corresponde a la respuesta correcta
    */
    private int $respuesta_correcta;


    public function __construct(int $id, string $texto, int $puntaje, array $opciones, int $respuesta_correcta) {
        $this->id = $id;
        $this->texto = $texto;
        $this->puntaje = $puntaje;
        $this->opciones = $opciones;
        $this->respuesta_correcta = $respuesta_correcta;
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
}
