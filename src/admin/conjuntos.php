<?php
include '../includes/auth_admin.php';
include '../includes/conexion.php';

$id_admin = $_SESSION['admin_id'];

$resultado = mysqli_query($conn,
    "SELECT c.id, c.nombre, c.descripcion, COUNT(p.id) AS total_preguntas
     FROM conjunto c
     LEFT JOIN pregunta p ON p.id_conjunto = c.id
     WHERE c.id_admin = $id_admin
     GROUP BY c.id
     ORDER BY c.id DESC");

$conjuntos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis conjuntos — Quiz Cooperativo</title>
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

            <a href="conjunto_form.php" class="boton boton--primario boton--chico" style="width:auto">
                + Nuevo conjunto
            </a>

            <?php if (count($conjuntos) === 0): ?>
                <p class="texto-vacio">No tenés conjuntos creados todavía.</p>
            <?php else: ?>
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Preguntas</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($conjuntos as $conjunto): ?>
                        <tr>
                            <td><?= $conjunto['nombre']?></td>
                            <td><?= $conjunto['descripcion'] ?></td>
                            <td><?= $conjunto['total_preguntas'] ?></td>
                            <td>
                                <a href="preguntas.php?id=<?= $conjunto['id'] ?>"
                                   class="boton boton--primario boton--chico">
                                    Gestionar preguntas
                                </a>
                                <a href="eliminar_conjunto.php?id=<?= $conjunto['id'] ?>"
                                   class="boton boton--rojo boton--chico"
                                   onclick="return confirm('¿Seguro que querés eliminar este conjunto?')">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        </section>

        <a href="dashboard.php" class="enlace-volver"> Volver al dashboard</a>

    </main>

</body>
</html>