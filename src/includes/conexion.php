<?php

#$host = "localhost";
#$host = "host.docker.internal";
$host = "localhost";
$user = "tecnologo";
$pass = "tecnologo";
$db   = "quiz_cooperativo";
$port = 3306;

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
