<?php
include '../includes/auth_admin.php';
include '../includes/conexion.php';

$id_conjunto = (int) $_GET['id'];
$error       = "";

/*
verifico que el conjunto exista y pertenezca al admin
*/

$sql_conjunto = "SELECT * FROM conjunto WHERE id = $id_conjunto AND id_admin = {$_SESSION['admin_id']}";

$res_conjunto = mysqli_query($conn, $sql_conjunto);

if (mysqli_num_rows($res_conjunto) == 0) {
    header("Location: conjuntos.php");
    exit;
}

$conjunto = mysqli_fetch_assoc($res_conjunto);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $texto    = $_POST['texto'];
    $puntaje  = (int) $_POST['puntaje_base'];
    $correcta = (int) $_POST['correcta'];

    /*
    calculo el orden de la nueva pregunta
    */

    $sql_orden = "SELECT COUNT(*) as total FROM pregunta WHERE id_conjunto = $id_conjunto";

    $res_orden = mysqli_query($conn, $sql_orden);

    $fila_orden = mysqli_fetch_assoc($res_orden);

    $orden = $fila_orden['total'] + 1;

    /*
    inserto la pregunta
    */

    $sql_pregunta = "INSERT INTO pregunta (texto, puntaje_base, id_conjunto)
                     VALUES ('$texto', $puntaje, $id_conjunto)";

    if (mysqli_query($conn, $sql_pregunta)) {

        $id_pregunta = mysqli_insert_id($conn);

        /*
        inserto las opciones
        */

        foreach ($_POST['opciones'] as $i => $texto_op) {

            $texto_op    = $texto_op;
            $es_correcta = ($i == $correcta) ? 1 : 0; /* if abreviado  */

            mysqli_query($conn, "INSERT INTO opciones (texto, es_correcta, id_pregunta)
                                 VALUES ('$texto_op', $es_correcta, $id_pregunta)");
        }

        header("Location: preguntas.php?id=$id_conjunto");
        exit;

    } else {
        $error = "Error al guardar la pregunta.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva pregunta — Quiz Cooperativo</title>
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

        <h2>❓ Nueva pregunta — <?= $conjunto['nombre'] ?></h2>

        <?php if ($error != "") { ?>
            <p class="mensaje-error">❌ <?= $error ?></p>
        <?php } ?>

        <form action="pregunta_form.php?id=<?= $id_conjunto ?>" method="POST">

            <label for="texto">Pregunta</label>
            <textarea id="texto" name="texto"
                      placeholder="Ej: ¿Cuál es la capital de Francia?"
                      rows="3" required></textarea>

            <br>

            

            <label for="puntaje_base">Puntaje base</label>
            <input type="number" id="puntaje_base" name="puntaje_base"
                   value="100" min="10" max="1000" required>

            <br>

            <label>Opciones — marcá cuál es la correcta</label>

            <?php for ($i = 0; $i < 4; $i++) { ?>

                <div class="fila-opcion">
                    <input type="radio" name="correcta" value="<?= $i ?>" required>
                    <input type="text" name="opciones[<?= $i ?>]"
                           placeholder="Opción <?= $i + 1 ?>" required>
                </div>

            <?php } ?>

            <br>

            <button type="submit" class="boton boton--primario">
                💾 GUARDAR PREGUNTA
            </button>

        </form>

    </section>

    <a href="preguntas.php?id=<?= $id_conjunto ?>" class="enlace-volver">Volver a preguntas</a>

</main>

</body>
</html>