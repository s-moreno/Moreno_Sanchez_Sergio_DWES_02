<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <title>Gestión de Vehículos</title>
</head>

<body>
    <div class="container">
        <div class="title">
            <h1>Gestión de reserva del vehículo</h1>
        </div>
        <form action="validarReserva.php" method="post">
            <div class="main">
                <p>Nombre: <input type="text" name="nombre" /></p>
                <p>Apellido: <input type="text" name="apellido" /></p>
                <p>DNI: <input type="text" name="dni" /></p>
                <p>Vehículo:
                    <select name="idVehiculo" id="vehiculo">
                        <?php
                        include './db/usuarios_y_coches.php';
                        foreach ($coches as $coche) {
                            echo "<option value='{$coche["id"]}'>{$coche["modelo"]}</option>";
                        }
                        ?>
                    </select>
                </p>
                <p>Fecha de inicio: <input type="date" name="fecha" /></p>
                <p>Días: <input type="number" name="dias" /></p>
            </div>
            <div class="footer">
                <input class="boton boton-color" type="submit" value="Enviar">
            </div>
        </form>
    </div>
    <footer>
        <p>Sergio Moreno - smoreno@birt.eus | DWES - 2024
        </p>
    </footer>
</body>

</html>