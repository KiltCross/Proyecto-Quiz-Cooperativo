<?php
include '../includes/auth_admin.php';
include '../includes/conexion.php';

$id       = (int) $_GET['id'];
$id_admin = $_SESSION['admin_id'];

// Verificar que no tenga salas activas
$activas = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM sala
     WHERE id_conjunto = $id
     AND estado IN ('esperando', 'jugando')"))['total'];

if ($activas > 0) {
    header("Location: conjuntos.php?error=tiene_sala_activa");
    exit;
}

// Verificar que el conjunto pertenece a este admin antes de borrar
mysqli_query($conn,
    "DELETE FROM conjunto
     WHERE id = $id AND id_admin = $id_admin");

header("Location: conjuntos.php");
exit;