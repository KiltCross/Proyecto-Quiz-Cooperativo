<?php
session_start();
include 'includes/conexion.php';

// Si ya hay admin, nadie puede registrarse
$resultado    = mysqli_query($conn, "SELECT COUNT(*) FROM administrador");
$total_admins = mysqli_num_rows($resultado);

if ($total_admins > 0) {
    header("Location: login.php");
    exit;
}

// Si ya está logueado, ir al dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre     = $_POST['nombre'];
    $email      = $_POST['email'];
    $contrasena = $_POST['contrasena'];
    $confirmar  = $_POST['confirmar'];

    if (empty($nombre) || empty($email) || empty($contrasena)) {
        $error = "Todos los campos son obligatorios.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El email no tiene un formato válido.";

    } elseif (strlen($contrasena) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";

    } elseif ($contrasena !== $confirmar) {
        $error = "Las contraseñas no coinciden.";

    } else {
        $hash = password_hash($contrasena, PASSWORD_BCRYPT);
        mysqli_query($conn,
            "INSERT INTO administrador (nombre, email, password)
             VALUES ('$nombre', '$email', '$hash')");

        header("Location: login.php?registro=ok");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta — Quiz Cooperativo</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>

    <header class="encabezado">
        <span class="encabezado__icono">🎮</span>
        <div class="encabezado__titulo">
            <span class="encabezado__quiz">QUIZ</span>
            <span class="encabezado__coop">COOPERATIVO</span>
        </div>
        <p class="encabezado__subtitulo">⭐ Crear cuenta administrador</p>
    </header>

    <main class="contenido">

        <section class="seccion-sala">
            <h2>👤 Registrarse</h2>

            <?php if ($error !== ""): ?>
                <p class="mensaje-error">❌ <?= $error ?></p>
            <?php endif; ?>

            <form action="registro.php" method="POST">

                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre"
                       minlength="2" maxlength="100"
                       placeholder="Ej: Juan García"
                       value="<?= isset($_POST['nombre']) ? $_POST['nombre'] : '' ?>"
                       required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       placeholder="Ej: admin@quiz.com"
                       value="<?= isset($_POST['email']) ? $_POST['email'] : '' ?>"
                       required>

                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena"
                       minlength="6"
                       placeholder="Mínimo 6 caracteres"
                       required>

                <label for="confirmar">Confirmar contraseña</label>
                <input type="password" id="confirmar" name="confirmar"
                       minlength="6"
                       placeholder="Repetí tu contraseña"
                       required>

                <button type="submit" class="boton boton--verde">
                    👤 CREAR CUENTA
                </button>

            </form>
        </section>

        <section class="seccion-admin">
            <h2>🔒 ¿Ya tenés cuenta?</h2>
            <p>Iniciá sesión como administrador</p>
            <a href="login.php" class="boton boton--contorno">
                ➡ INICIAR SESIÓN
            </a>
        </section>

    </main>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const contrasena = document.getElementById('contrasena').value;
            const confirmar  = document.getElementById('confirmar').value;
            if (contrasena !== confirmar) {
                e.preventDefault();
                alert('Las contraseñas no coinciden.');
            }
        });
    </script>

</body>
</html>