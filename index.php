<?php
session_start();
include 'includes/conexion.php';
$resultado = mysqli_query($conn, "SELECT * FROM administrador");
$total_admins = mysqli_num_rows($resultado);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Cooperativo</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>


    <header class="encabezado">
        <span class="encabezado__icono">🎮</span>
        <div class="encabezado__titulo">
            <span class="encabezado__quiz">QUIZ</span>
            <span class="encabezado__coop">COOPERATIVO</span>
        </div>
        <p class="encabezado__subtitulo">👥 Jugá en tiempo real con amigos</p>
    </header>

    <main class="contenido">

        <section class="seccion-sala">
            <h2>👥 Unite a una sala</h2>

            <form action="index.php" method="POST">
                <label for="codigo">Código de sala</label>
                <input type="text" id="codigo" name="codigo"
                       maxlength="6" placeholder="Ej: ABC123" required>

                <label for="nombre">Tu nombre</label>
                <input type="text" id="nombre" name="nombre"
                       minlength="2" maxlength="20" placeholder="Ej: Juan Pérez" required>

                <button type="submit" class="boton boton--primario">UNIRSE A LA SALA</button>
            </form>
        </section>

        <?php if ($total_admins === 0): ?>
        <section class="seccion-admin seccion-admin--nueva">
            <h2>⭐ Primer acceso al sistema</h2>
            <p>Para comenzar, creá una cuenta administradora</p>
            <a href="registro.php" class="boton boton--verde">👥 CREAR ADMINISTRADOR</a>
        </section>
        <?php else: ?>
        <section class="seccion-admin">
            <h2>🛡 Administración</h2>
            <p>¿Sos administrador?</p>
            <a href="login.php" class="boton boton--contorno">👥 INICIAR SESIÓN ADMINISTRADOR</a>
        </section>
        <?php endif; ?>

    </main>


</body>
</html>