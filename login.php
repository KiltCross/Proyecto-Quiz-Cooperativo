<?php
session_start();
include 'includes/conexion.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit;
}

$error = "";
$exito = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email      = mysqli_real_escape_string($conn, trim($_POST['email']));
    $contrasena = $_POST['contrasena'];

    $resultado = mysqli_query($conn,
        "SELECT * FROM administrador WHERE email = '$email'");

    if (mysqli_num_rows($resultado) == 1) {

        $admin = mysqli_fetch_assoc($resultado);

        if (password_verify($contrasena, $admin['password'])) {

            $_SESSION['admin_id']     = $admin['id'];
            $_SESSION['admin_nombre'] = $admin['nombre'];

            header("Location: admin/dashboard.php");
            exit;

        } else {
            $error = "Contraseña incorrecta.";
        }

    } else {
        $error = "No existe una cuenta con ese email.";
    }
}

if (isset($_GET['registro']) && $_GET['registro'] === 'ok') {
    $exito = "Cuenta creada correctamente. Ya podés iniciar sesión.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — Quiz Cooperativo</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>

    <header class="encabezado">
        <span class="encabezado__icono">🎮</span>
        <div class="encabezado__titulo">
            <span class="encabezado__quiz">QUIZ</span>
            <span class="encabezado__coop">COOPERATIVO</span>
        </div>
        <p class="encabezado__subtitulo">👤 Acceso administrador</p>
    </header>

    <main class="contenido">

        <section class="seccion-sala">
            <h2>🔒 Iniciar sesión</h2>

            <?php if ($exito !== ""): ?>
                <p class="mensaje-exito">✅ <?= htmlspecialchars($exito) ?></p>
            <?php endif; ?>

            <?php if ($error !== ""): ?>
                <p class="mensaje-error">❌ <?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form action="login.php" method="POST">

                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       placeholder="Ej: admin@quiz.com"
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                       required autocomplete="email">

                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena"
                       placeholder="Tu contraseña"
                       required autocomplete="current-password">

                <button type="submit" class="boton boton--primario">
                    ➡ INICIAR SESIÓN
                </button>

            </form>
        </section>

        <section class="seccion-admin">
            <h2>🔙 Volver</h2>
            <p>¿No sos administrador?</p>
            <a href="index.php" class="boton boton--contorno">← VOLVER AL INICIO</a>
        </section>

    </main>

</body>
</html>