<?php
include './db/usuarios_y_coches.php';
include 'reserva.php';

// ----------- USUARIOS ------------

/**
 * Calcula la letra de control correspondiente a un DNI (NIF sin letra).
 *
 * @param string $dni Número de 8 dígitos correspondiente al NIF.
 * @return string La letra de control correspondiente al DNI.
 */
function letra_nif($dni): string
{
    return substr("TRWAGMYFPDXBNJZSQVHLCKE", strtr($dni, "XYZ", "012") % 23, 1);
}

/**
 * Verifica si un NIF estructuralmente es correcto
 *
 * @param string $nif número dni más letra
 * @return bool
 */
function validarNIF($nif): bool
{
    // Comprobar que el nif es de 9 caracteres
    if (strlen($nif) != 9) {
        return false;
    }

    //obtener los números del nif = dni
    $dni = substr($nif, 0, 8);
    // obtener la letra del nif (que está en la última posición) y lo convertimos a mayus.
    $letra = strtoupper(substr($nif, -1, 1));

    // validar la letra usando el algoritmo del módulo 23.
    return $letra === letra_nif($dni);
}

/**
 * Verifica que el usuario se encuentra en el array de usuarios.
 * No discrimina entre mayúsculas y minúsculas.
 *
 * @param array $usuarios array de usuarios
 * @param array $usuarioForm array asociativo del usuario a verificar
 * @return bool
 */
function userExist($usuarios, $inputForm): bool
{
    foreach ($usuarios as $usuario) {
        // Comparación de nombre y apellido sin tener en cuenta si se ha escrito en mayus o minus.
        if (
            $usuario["dni"] === trim($inputForm["dni"]) &&
            mb_strtoupper($usuario["nombre"], 'UTF-8') === mb_strtoupper(trim($inputForm["nombre"]), 'UTF-8') &&
            mb_strtoupper($usuario["apellido"], 'UTF-8') === mb_strtoupper(trim($inputForm["apellido"]), 'UTF-8')
        ) {
            return true;
        }
    }
    return false;
}

// -------------- RESERVA -------------

/**
 * Verifica que la fecha dada es posterior a la actual en formato Y-m-d
 *
 * @param date $fecha
 * @return bool
 */
function validarFecha($fecha): bool
{
    return $fecha > date("Y-m-d");
}

/**
 * Verifica que los días dados, estén entre 1 y 30.
 *
 * @param int $dias
 * @return bool
 */
function validarDias($dias): bool
{
    return $dias >= 1 && $dias <= 30;
}

/**
 * Verifica que el coche elegido se encuentra libre para 
 * las fechas y días seleccionados.
 * @param  array $coches
 * @param  array $inputForm
 * @return bool
 */
function validarCocheLibre($coches, $inputForm): bool
{

    $dias = $inputForm["dias"];
    $fechaReservaInicio = new DateTime($inputForm['fecha']);

    // Calcular la fecha de fin de reserva a partir de la fecha 
    // de inicio + los días de reserva:
    // clonamos la fecha y la modificamos con los días
    $fechaReservaFin = clone $fechaReservaInicio;
    $fechaReservaFin->modify("+$dias days");

    foreach ($coches as $coche) {
        if ($coche['id'] == $inputForm['idVehiculo']) {

            if ($coche['disponible']) {
                return true;
            } else {
                $fechaInicio = new DateTime($coche['fecha_inicio']);
                $fechaFin = new DateTime($coche['fecha_fin']);

                if ($fechaReservaFin < $fechaInicio || $fechaReservaInicio > $fechaFin) {
                    return true;
                }
            }
        }
    }
    return false;
}

/**
 * Devuelve un array multidimensional con las validaciones
 * necesarias (correctas y erroneas).
 * @param  array $coches
 * @param  array $inputForm
 * @return array array asociativo multidimensional "correcto" y "error"
 */
function validarReserva($coches, $inputForm): array
{
    $validaciones = [
        "correcto" => [],
        "error" => []
    ];

    // Validar el nombre
    if (empty($inputForm['nombre'])) {
        $validaciones["error"][] = "El campo 'nombre' no puede estar vácio.";
    } else {
        $validaciones["correcto"][] = "Nombre: " . ucfirst(mb_strtolower($inputForm["nombre"], "UTF-8"));
    }

    // Validar el apellido
    if (empty($inputForm['apellido'])) {
        $validaciones["error"][] = "El campo 'apellido' no puede estar vácio";
    } else {
        $validaciones["correcto"][] = "Apellido: " . ucfirst(mb_strtolower($inputForm["apellido"], "UTF-8"));
    }

    // Validar el DNI
    if (validarNIF($inputForm['dni'])) {
        $validaciones["correcto"][] = "DNI: " . $inputForm['dni'];
    } else {
        $validaciones["error"][] = "El DNI no es válido";
    }

    // Validar usuario en la base de datos
    if (userExist(USUARIOS, $inputForm)) {
        $validaciones["correcto"][] = "Usuario validado";
    } else {
        $validaciones["error"][] = "El usuario no se encuentra en la Base de Datos.";
    }

    // Validar la fecha
    if (validarFecha($inputForm['fecha'])) {
        $validaciones["correcto"][] = "Fecha: " . date("d-m-Y", strtotime($inputForm['fecha']));
    } else {
        $validaciones["error"][] = "La fecha debe ser posterior a la actual";
    }

    // Validar los días de reserva
    if (validarDias($inputForm['dias'])) {
        $validaciones["correcto"][] = "Días: " . $inputForm['dias'];
    } else {
        $validaciones["error"][] = "Lo días de reserva debe ser un número entre el 1 y el 30.";
    }

    // Validar disponibilidad del vehículo
    if (validarFecha($inputForm['fecha']) && validarDias($inputForm['dias'])) {
        if (validarCocheLibre($coches, $inputForm)) {
            $validaciones["correcto"][] = "Vehículo disponible.";
        } else {
            $validaciones["error"][] = "El vehículo no se encuentra disponible para esas fechas.";
        }
    }

    return $validaciones;
}

/**
 * Devuelve en nombre del modelo correspondiente
 * al ID dado.
 *
 * @param number $id
 * @param array $coches
 * @return string
 */
function encontrarModeloPorID($id, $coches): string
{
    foreach ($coches as $coche) {
        if ($coche['id'] == $id) {
            return $coche['modelo'];
        }
    }
    return null;
}

/**
 * Pinta en pantalla las validaciones (correctas y erroneas)
 * @param  array $validaciones array asociativo multidimensiona
 * @return void
 */
function pintarReservaNoValida($validaciones): void
{
    echo "<h2>Reserva no válida</h2>";
    foreach ($validaciones as $tipoValidacion => $validacion) {
        $colorMensaje = $tipoValidacion === "error" ? "error" : "correcto";
        echo "<ul>";
        foreach ($validacion as $mensaje) {
            echo "<li class='{$colorMensaje}'>{$mensaje}</li>";
        }
        echo "</ul>";
    }
}

/**
 * Pinta en pantalla los datos de la reserva realizada
 *
 * @param array $inputForm
 * @param array $coches
 * @return void
 */
function pintarReservaValida($inputForm, $coches): void
{
    $id = $inputForm["idVehiculo"];
    $modelo = encontrarModeloPorID($id, $coches);
    $nombre = ucfirst(mb_strtolower($inputForm["nombre"], "UTF-8"));
    $apellido = ucfirst(mb_strtolower($inputForm["apellido"], "UTF-8"));
    $extension = "png";

    $pintar = "<div class='info-reserva'>";
    $pintar .= "<h2>Reserva válida</h2>";
    $pintar .= "<p>{$nombre} {$apellido}</p>";
    $pintar .= "<img src='./img/{$id}.{$extension}' title='{$modelo}' alt='{$modelo}'>";
    $pintar .= "</div>";

    echo $pintar;
}
