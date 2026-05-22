<?php
include '../includes/auth_admin.php';
include '../includes/conexion.php';

$id_pregunta = (int) $_GET['id'];
$id_conjunto = (int) $_GET['conjunto'];

/*
elimino la pregunta — las opciones se borran solas por el CASCADE del SQL
*/

$sql = "DELETE FROM pregunta WHERE id = $id_pregunta";

mysqli_query($conn, $sql);

/*
vuelvo a la lista de preguntas del conjunto
*/

header("Location: preguntas.php?id=$id_conjunto");
exit;