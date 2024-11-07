<?php
require './db/usuarios_y_coches.php';

session_start();
unset($_SESSION['valido']);
unset($_SESSION['error']);
unset($_SESSION['vehiculo']);


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
 * No discrimina entre letra de control mayúscula o minúscula.
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
 * No discrimina entre mayúsculas y minúsculas del nombre,
 * apellido o DNI.
 *
 * @param array $usuarios array de usuarios
 * @param array $usuarioForm array asociativo del usuario a verificar
 * @return bool
 */
function userExist($usuarios, $inputForm): bool
{
    $dni = mb_strtoupper(trim($inputForm["dni"]), 'UTF-8');
    $nombre = mb_strtoupper(trim($inputForm["nombre"]), 'UTF-8');
    $apellido = mb_strtoupper(trim($inputForm["apellido"]), 'UTF-8');

    foreach ($usuarios as $usuario) {
        // Comparación de nombre y apellido sin tener en cuenta si se ha escrito en mayus o minus.

        if (
            $usuario["dni"] === $dni &&
            mb_strtoupper($usuario["nombre"], 'UTF-8') === $nombre &&
            mb_strtoupper($usuario["apellido"], 'UTF-8') === $apellido
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
 * Devuelve un array multidimensional con las validaciones
 * necesarias (correctas y erroneas).
 * @param  array $coches
 * @param  array $inputForm
 */
function validarReserva($coches, $inputForm): void
{
    $valido = [];
    $error = [];

    // Validar el nombre
    if (empty($inputForm['nombre'])) {
        $error["Nombre"] = "El campo no puede estar vácio.";
    } else {
        $valido["Nombre"] = ucfirst(mb_strtolower($inputForm["nombre"], "UTF-8"));
    }

    // Validar el apellido
    if (empty($inputForm['apellido'])) {
        $error["Apellido"] = "El campo no puede estar vácio.";
    } else {
        $valido["Apellido"] = ucfirst(mb_strtolower($inputForm["apellido"], "UTF-8"));
    }

    // Validar el DNI
    if (validarNIF($inputForm['dni'])) {
        $valido["DNI"] = $inputForm['dni'];
    } else {
        $error["DNI"] = "No es válido.";
    }

    // Validar usuario en la base de datos
    if (userExist(USUARIOS, $inputForm)) {
        $valido["Usuario"] = "Se encuentra en la base de datos.";
    } else {
        $error["Usuario"] = "No se encuentra en la base de datos.";
    }

    // Validar la fecha
    if (validarFecha($inputForm['fecha'])) {
        $valido["Fecha"] = date("d-m-Y", strtotime($inputForm['fecha']));
    } else {
        $error["Fecha"] = "Debe ser posterior a la actual.";
    }

    // Validar los días de reserva
    if (validarDias($inputForm['dias'])) {
        $valido["Días"] = $inputForm['dias'];
    } else {
        $error["Días"] = "Lo días de reserva debe ser un número entre el 1 y el 30.";
    }

    // Validar disponibilidad del vehículo
    if (validarFecha($inputForm['fecha']) && validarDias($inputForm['dias'])) {
        if (validarCocheLibre($coches, $inputForm)) {
            $valido["Vehículo"] = "Disponible.";
        } else {
            $error["Vehículo"] = "No se encuentra disponible para esas fechas.";
        }
    }

    $_SESSION['valido'] = $valido;
    $_SESSION['error'] = $error;

    // si no hay errores redirigimos a la página de reserva OK:
    if (count($error) != 0) {
        header("Location: reservaNOK.php");
        exit;
    } else {
        $_SESSION['vehiculo']['id'] = $inputForm['idVehiculo'];
        $_SESSION['vehiculo']['modelo'] = encontrarModeloPorID($inputForm['idVehiculo'], $coches);
        header("Location: reservaOK.php");
        exit;
    }
}

validarReserva($coches, $_POST);
