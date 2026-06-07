REQUERIMIENTOS FUNCIONALES
==========================

Módulo de Autenticación
--------------------
### RF-01 — Registro del administrador

+El sistema permite registrar únicamente cuentas de tipo administrador.
+Los datos requeridos son: nombre, correo electrónico y contraseña.
+El correo electrónico debe ser único en el sistema.

### RF-02 — Inicio de sesión del administrador

+El administrador inicia sesión con correo electrónico y contraseña.
+El sistema crea una sesión PHP activa con sus datos y rol.
+Si los datos son incorrectos muestra un mensaje de error claro.

### RF-03 — Cierre de sesión

+El administrador puede cerrar sesión en cualquier momento.
+Al cerrar sesión se destruye completamente la sesión PHP.



Módulo de Jugadores Invitados
--------------------
### RF-04 — Ingreso a sala sin registro

+Un jugador puede unirse a una partida ingresando únicamente un código de sala y un nombre de jugador.
+No necesita cuenta, correo electrónico ni contraseña.
+El nombre del jugador debe tener entre 2 y 20 caracteres.
+No pueden existir dos jugadores con el mismo nombre dentro de la misma sala.

### RF-05 — Restricciones de ingreso

+Un jugador no puede unirse si la sala ya comenzó.
+Un jugador no puede unirse si la sala ya terminó o fue cerrada.
+Un jugador no puede unirse si el código no existe.


Módulo de Administrador
--------------------
### RF-06 — Panel del administrador

+Al iniciar sesión el administrador accede a su panel principal.
+Desde el panel puede gestionar conjuntos, preguntas y salas.
+Puede ver un resumen de sus conjuntos creados y partidas recientes.

### RF-07 — Gestión de conjuntos de preguntas

+El administrador puede crear un conjunto con nombre y descripción.
+El administrador puede editar nombre y descripción de un conjunto existente.
+El administrador puede eliminar un conjunto siempre que no tenga partidas activas en curso.
+El administrador puede listar todos sus conjuntos con la cantidad de preguntas que tiene cada uno.

### RF-08 — Gestión de preguntas

+El administrador puede agregar preguntas a un conjunto.
+Cada pregunta contiene: texto de la pregunta, tiempo límite en segundos y puntaje base.
+El administrador puede editar cualquier campo de una pregunta.
+El administrador puede eliminar una pregunta de un conjunto.
+El administrador puede listar todas las preguntas de un conjunto determinado.

### RF-09 — Gestión de opciones de respuesta

+Cada pregunta tiene entre 2 y 4 opciones de respuesta.
+El administrador define el texto de cada opción.
+El administrador marca cuál de las opciones es la correcta.
+Solo puede existir una opción correcta por pregunta.
+No se puede guardar una pregunta sin al menos 2 opciones y 1 marcada como correcta.


Módulo de Salas
--------------------
### RF-10 — Creación de sala

+Solo el administrador puede crear una sala.
+Al crear la sala elige: conjunto de preguntas y modalidad de juego (Competitiva o Cooperativa).
+El sistema genera automáticamente un código único de 6 caracteres alfanuméricos.
+El administrador que crea la sala es automáticamente el anfitrión.
+El anfitrión también participa como jugador en la partida.

### RF-11 — Sala de espera — Lobby

+El lobby muestra en tiempo real la lista de jugadores que se han unido.
+El lobby muestra el nombre del conjunto y la modalidad elegida.
+Solo el anfitrión puede iniciar la partida.
+El anfitrión puede expulsar jugadores del lobby antes de iniciar.
+Se requiere mínimo 2 jugadores para poder iniciar la partida.
+El código de sala es visible en el lobby para compartirlo fácilmente.


Módulo de Juego
--------------------
### RF-12 — Flujo general de la partida

+Al iniciar la partida todos los jugadores ven la misma pregunta al mismo tiempo.
+Cada pregunta tiene un temporizador visual que cuenta de forma regresiva.
+Cuando el tiempo se agota o todos responden, se avanza automáticamente a la siguiente pregunta.
+Entre pregunta y pregunta se muestra una pantalla de transición con la respuesta correcta.
+Al terminar todas las preguntas la partida finaliza y se redirige a resultados.

### RF-13 — Modalidad Competitiva

+Cada jugador responde de forma individual e independiente.
+El puntaje por pregunta se calcula así: sí se selecciona la opción correcta el puntaje incrementa según la cantidad de puntos asignados a la pregunta, pero se decrementa esa misma cantidad de puntos si se seleccionó una opción incorrecta.
+Entre preguntas se muestra un ranking parcial con los puntajes acumulados hasta ese momento.

### RF-14 — Modalidad Cooperativa

+Todos los jugadores forman un único equipo.
+El equipo tiene un puntaje compartido que comienza en cero.
+Si el 50% o más de los jugadores responde correctamente una pregunta, el equipo suma los puntos de esa pregunta.
+Si el 50% o más de los jugadores responde incorrectamente una pregunta, al equipo se le resta los puntos de esa pregunta.
+Cuando se acaben las preguntas del conjunto se termina el juego.
+Cuando se termina el juego se muestra el puntaje total.
+Entre preguntas se muestra el puntaje del equipo.

### RF-15 — Sincronización en tiempo real

+Todos los jugadores deben ver la misma pregunta al mismo tiempo.
+Todos los jugadores dejan de ver los resultados de la ronda anterior al mismo tiempo.
+Si un jugador pierde conexión y vuelve, se sincroniza automáticamente con el estado actual.


Módulo de Resultados
--------------------
### RF-16 — Pantalla de resultados — Modalidad Competitiva

+Se muestra el ranking final con: posición, nombre del jugador, puntaje total y cantidad de respuestas correctas.
+Se destaca al jugador ganador en el primer lugar.
+Se puede volver al inicio desde esta pantalla.

### RF-17 — Pantalla de resultados — Modalidad Cooperativa

+Se muestra si el equipo ganó o perdió.
+Se muestra: puntaje total del equipo y porcentaje de aciertos del equipo.
+Se muestra la contribución individual de cada jugador (cuántas respondió correctamente).
+Se puede volver al inicio desde esta pantalla.
