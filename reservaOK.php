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
            session_start();
            $modelo = $_SESSION['modeloCoche'];
            $nombre = $_SESSION['valido']['Nombre'];
            $apellido = $_SESSION['valido']['Apellido'];
            $idCoche = $_SESSION['vehiculo']['id'];
            $modeloCoche = $_SESSION['vehiculo']['modelo'];

            $pintar = "<div class='info-reserva'>";
            $pintar .= "<h2>Reserva válida</h2>";
            $pintar .= "<p>{$nombre} {$apellido}</p>";
            $pintar .= "<img src='./img/{$idCoche}.png' title='{$modeloCoche}' alt='{$modeloCoche}'>";
            $pintar .= "</div>";

            echo $pintar;
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