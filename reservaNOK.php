<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <title>Gestión de reserva del vehículo</title>
</head>

<body>
    <div class="container">
        <div class="title">
            <h1>Gestión de reserva del vehículo</h1>
        </div>
        <div class="main">
            <h2>Reserva no válida</h2>
            <?php
            session_start();
            $validos = $_SESSION['valido'];
            $errores = $_SESSION['error'];

            echo "<ul>";
            foreach ($validos as $key => $value) {
                echo "<li class='correcto'><strong>{$key}</strong>: {$value}</li>";
            }
            echo "</ul><ul>";
            foreach ($errores as $key => $value) {
                echo "<li class='error'><strong>{$key}</strong>: {$value}</li>";
            }
            echo "</ul>";
            ?>
        </div>
        <div class="footer">
            <!-- <button class="boton boton-color" onclick="window.location.href='index.php'">Volver</button> -->
            <a href="index.php" class="boton boton-color">Volver</a>
        </div>
    </div>
    <footer>
        <p>Sergio Moreno - smoreno@birt.eus | DWES - 2024
        </p>
    </footer>
</body>

</html>