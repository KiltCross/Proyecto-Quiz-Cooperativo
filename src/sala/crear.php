<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear sala — Quiz Cooperativo</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body class="pagina-dashboard">

<header class="barra-superior">
    <span>🎮 Quiz Cooperativo</span>
    <span id="nombre-admin">👤</span>
    <a href="../logout.php" class="boton boton--contorno boton--chico">Salir</a>
</header>

<main class="dashboard">

    <section class="panel">

        <a href="../admin/dashboard.php" class="enlace-volver">← Volver al panel</a>

        <h2>🎮 Crear sala</h2>

        <p class="mensaje-error" id="mensaje-error" style="display:none"></p>

        <p class="texto-vacio" id="sin-conjuntos" style="display:none">
            No tenés conjuntos con preguntas todavía.
            <a href="../admin/conjuntos.php">Crear un conjunto →</a>
        </p>

        <form id="form-crear-sala">

            <label for="id_conjunto">Conjunto de preguntas</label>
            <select name="id_conjunto" id="id_conjunto" required>
                <option value="">— Elegí un conjunto —</option>
                <!-- las opciones del select se llenan por JavaScript -->
            </select>

            <label>Modalidad de juego</label>

            <div class="fila-modalidad">
                <label class="opcion-modalidad">
                    <input type="radio" name="modalidad" value="cooperativa" required>
                    🤝 Cooperativa
                </label>
                <label class="opcion-modalidad">
                    <input type="radio" name="modalidad" value="competitiva">
                    🏆 Competitiva
                </label>
            </div>

            <button type="submit" class="boton boton--verde">
                🎮 CREAR SALA
            </button>

        </form>

    </section>

</main>

<script>
/*
HTML puro, sin nada de PHP ni datos hardcodeados.
Esto lo conecta el websocket: cargar conjuntos del admin,
mostrar nombre del admin, y mandar Crear_Sala al enviar el form.
*/
</script>

</body>
</html>
