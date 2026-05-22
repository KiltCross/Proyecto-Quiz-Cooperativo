<?php
include '../includes/auth_admin.php';
include '../includes/conexion.php';

$id_admin = $_SESSION['admin_id'];

/*
traigo los conjuntos del admin con cantidad de preguntas
*/

$sql_conjuntos = "SELECT c.id, c.nombre, COUNT(p.id) AS total_preguntas
                  FROM conjunto c
                  LEFT JOIN pregunta p ON p.id_conjunto = c.id
                  WHERE c.id_admin = $id_admin
                  GROUP BY c.id, c.nombre
                  ORDER BY c.nombre ASC";

$res_conjuntos = mysqli_query($conn, $sql_conjuntos);

$conjuntos = mysqli_fetch_all($res_conjuntos, MYSQLI_ASSOC);

/*
verifico si hay una sala activa del admin
*/

$sql_sala = "SELECT codigo_acceso, estado, modalidad
             FROM sala
             WHERE id_admin = $id_admin
             AND estado IN ('esperando', 'jugando')
             LIMIT 1";

$res_sala = mysqli_query($conn, $sql_sala);

$sala_activa = mysqli_fetch_assoc($res_sala);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Quiz Cooperativo</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body class="pagina-dashboard">

<header class="barra-superior">
    <span>🎮 Quiz Cooperativo</span>
    <span>👤 <?= $_SESSION['admin_nombre'] ?></span>
    <a href="../logout.php" class="boton boton--contorno boton--chico">Salir</a>
</header>

<main class="dashboard">

    <section class="panel">

        <h2>📚 Mis conjuntos</h2>

        <?php if (count($conjuntos) == 0) { ?>

            <p class="texto-vacio">No tenés conjuntos creados todavía.</p>

        <?php } else { ?>

            <table class="tabla">
                <tr>
                    <th>Nombre</th>
                    <th>Preguntas</th>
                    <th>Acciones</th>
                </tr>

                <?php foreach ($conjuntos as $conjunto) { ?>
                <tr>
                    <td><?= $conjunto['nombre'] ?></td>
                    <td><?= $conjunto['total_preguntas'] ?></td>
                    <td>
                        <a href="preguntas.php?id=<?= $conjunto['id'] ?>"
                           class="boton boton--contorno boton--chico">
                            Ver preguntas
                        </a>
                    </td>
                </tr>
                <?php } ?>

            </table>

        <?php } ?>

        <a href="conjuntos.php" class="boton boton--primario">📚 MIS CONJUNTOS</a>

    </section>

    <section class="panel">

        <h2>🎮 Sala activa</h2>

        <?php if ($sala_activa) { ?>

            <p>
                <strong><?= $sala_activa['codigo_acceso'] ?></strong>
                — <?= $sala_activa['modalidad'] ?>
                — <?= $sala_activa['estado'] == 'esperando' ? '⏳ Esperando' : '▶ Jugando' ?>
            </p>

            <a href="../sala/lobby.php" class="boton boton--primario">Ir a la sala</a>

        <?php } else { ?>

            <p class="texto-vacio">No hay ninguna sala activa.</p>

            <a href="../sala/crear.php" class="boton boton--verde">🎮 CREAR SALA</a>

        <?php } ?>

    </section>

</main>

</body>
</html>