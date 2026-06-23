<?php

namespace Servidor_Sala\Jugador;

use Ratchet\ConnectionInterface;


class Jugador {

  
    private string $nombre;
    private int $puntuacion;
    private ConnectionInterface $conexion;

    /*
    indica si el jugador ya respondio la pregunta actual
    se resetea a false cada vez que empieza una nueva ronda
    */
    private bool $ha_respondido_la_pregunta_actual;


    public function __construct(string $nombre, ConnectionInterface $conexion) {
        $this->nombre = $nombre;
        $this->puntuacion = 0;
        $this->conexion = $conexion;
        $this->ha_respondido_la_pregunta_actual = false;
    }


    /*
    se llama cuando arranca una nueva ronda/pregunta
    resetea el flag de "ya respondio" para que pueda volver a responder
    */
    public function Cambio_de_Ronda(): void {
        $this->ha_respondido_la_pregunta_actual = false;
    }


    /*
    devuelve true si el jugador ya contesto la pregunta actual
    sirve para que la Sala sepa si tiene que esperar su respuesta o no
    */
    public function Ya_Respondio(): bool {
        return $this->ha_respondido_la_pregunta_actual;
    }


    /*
    suma puntos al jugador y marca que ya respondio esta ronda
    se llama despues de que Pregunta.Responder() devuelve los puntos ganados
    */
    public function Cambiar_Puntaje(int $puntos): void {
        $this->puntuacion += $puntos;
        $this->ha_respondido_la_pregunta_actual = true;
    }


    /*
    compara la conexion que llega por parametro con la conexion de este jugador
    sirve para identificar quien mando un mensaje al servidor websocket
    */
    public function Eres_El_Jugador_con_Esta_Conexion(ConnectionInterface $conexion): bool {
        return $this->conexion === $conexion;
    }


    public function Dar_Conexion(): ConnectionInterface {
        return $this->conexion;
    }


    /*
    devuelve el nombre del jugador
    */
    public function Dar_Nombre(): string {
        return $this->nombre;
    }


 
    public function Dar_Puntaje(): int {
        return $this->puntuacion;
    }
}
