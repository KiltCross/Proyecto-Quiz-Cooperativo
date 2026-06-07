<?php
include '../includes/auth_admin.php';
include '../includes/conexion.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre      = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $id_admin    = $_SESSION['admin_id'];

    $sql = "INSERT INTO conjunto (nombre, descripcion, id_admin)
            VALUES ('$nombre', '$descripcion', $id_admin)";

    if (mysqli_query($conn, $sql)) {
        header("Location: conjuntos.php");
        exit;
    } else {
        $error = "Ese nombre ya existe. Elegí otro nombre.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo conjunto — Quiz Cooperativo</title>
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

        <h2>📚 Nuevo conjunto</h2>

        <?php if ($error != "") { ?>
            <p class="mensaje-error">❌ <?= $error ?></p>
        <?php } ?>

        <form action="conjunto_form.php" method="POST">

            <label for="nombre">Nombre del conjunto</label>
            <input type="text" id="nombre" name="nombre"
                   placeholder="Ej: Geografía Mundial"
                   required>

            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion"
                      placeholder="Ej: Preguntas sobre capitales y países"
                      rows="3"></textarea>

            <button type="submit" class="boton boton--primario">
                💾 GUARDAR CONJUNTO
            </button>

        </form>

    </section>

    <a href="conjuntos.php" class="enlace-volver">Volver a mis conjuntos</a>

</main>

</body>
</html>