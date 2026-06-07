<?php

#$host = "localhost";
#$host = "host.docker.internal";
$host = "mysql";
$user = "tecnologo";
$pass = "tecnologo";
$db   = "quiz_cooperativo";
$port = 3306;

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
