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
            <?php
            $validaciones = validarReserva($coches, $_POST);
            if (count($validaciones["error"]) != 0) {
                pintarReservaNoValida($validaciones);
            } else {
                pintarReservaValida($_POST, $coches);
            }
            ?>
        </div>
        <div class="footer">
            <button class="boton boton-color" onclick="window.location.href='index.php'">Volver</button>
        </div>
    </div>
    <footer>
        <p>Sergio Moreno - smoreno@birt.eus | DWES - 2024
        </p>
    </footer>
</body>

</html>