<?php
include '../includes/auth_admin.php';
include '../includes/conexion.php';

$id_conjunto = (int) $_GET['id'];
$id_admin    = $_SESSION['admin_id'];

/*
verifico que el conjunto exista y pertenezca al admin logueado
*/

$sql_conjunto = "SELECT * FROM conjunto WHERE id = $id_conjunto AND id_admin = $id_admin";

$res_conjunto = mysqli_query($conn, $sql_conjunto);

if (mysqli_num_rows($res_conjunto) == 0) {
    die("Conjunto no válido");
}

$conjunto = mysqli_fetch_assoc($res_conjunto);

/*
traigo las preguntas del conjunto con sus opciones
*/

$sql_preguntas = "SELECT * FROM pregunta WHERE id_conjunto = $id_conjunto ORDER BY id ASC";

$res_preguntas = mysqli_query($conn, $sql_preguntas);

$preguntas = mysqli_fetch_all($res_preguntas, MYSQLI_ASSOC);

/*
para cada pregunta traigo sus opciones
*/

foreach ($preguntas as $i => $pregunta) {

    $sql_opciones = "SELECT * FROM opciones WHERE id_pregunta = {$pregunta['id']}";

    $res_opciones = mysqli_query($conn, $sql_opciones);

    $preguntas[$i]['opciones'] = mysqli_fetch_all($res_opciones, MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preguntas — Quiz Cooperativo</title>
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

        <h2>❓ Preguntas — <?= $conjunto['nombre'] ?></h2>

        <a href="pregunta_form.php?id=<?= $id_conjunto ?>" class="boton boton--primario boton--chico" style="width:auto">
            + Agregar pregunta
        </a>

        <?php if (count($preguntas) == 0) { ?>

            <p class="texto-vacio">No hay preguntas en este conjunto todavía.</p>

        <?php } else { ?>

            <?php foreach ($preguntas as $pregunta) { ?>

            <div class="tarjeta-pregunta">

                <div class="tarjeta-pregunta__cabecera">
                    <span class="tarjeta-pregunta__texto"><?= $pregunta['texto'] ?></span>
                    <div class="tarjeta-pregunta__meta">
                    
                        <span>⭐ <?= $pregunta['puntaje_base'] ?> pts</span>
                        <a href="eliminar_pregunta.php?id=<?= $pregunta['id'] ?>&conjunto=<?= $id_conjunto ?>"
                           class="boton boton--rojo boton--chico"
                           onclick="return confirm('¿Seguro que querés eliminar esta pregunta?')">
                            Eliminar
                        </a>
                    </div>
                </div>

                <div class="tarjeta-pregunta__opciones">
                    <?php foreach ($pregunta['opciones'] as $opcion) { ?>
                        <span class="opcion <?= $opcion['es_correcta'] ? 'opcion--correcta' : 'opcion--incorrecta' ?>">
                            <?= $opcion['es_correcta'] ? '✅' : '❌' ?>
                            <?= $opcion['texto'] ?>
                        </span>
                    <?php } ?>
                </div>

            </div>

            <?php } ?>

        <?php } ?>

    </section>

    <a href="conjuntos.php" class="enlace-volver">Volver a mis conjuntos</a>

</main>

</body>
</html>