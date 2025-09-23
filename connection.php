<?php

require_once('./constants.php');

$conexion = null;

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $missingConfiguration = [];

    if (!is_string($servidor_bd) || $servidor_bd === '') {
        $missingConfiguration[] = 'PRACEAR_DB_HOST';
        echo 'Falta servidor';
    }

    if (!is_string($usuario) || $usuario === '') {
        $missingConfiguration[] = 'PRACEAR_DB_USER';
        echo 'Falta usuario';
    }

    if (!is_string($bd) || $bd === '') {
        $missingConfiguration[] = 'PRACEAR_DB_NAME';
        echo 'Falta base de datos';
    }

    if (!empty($missingConfiguration)) {
        echo 'Faltan variables de entorno';
        throw new RuntimeException('Faltan variables de entorno requeridas: ' . implode(', ', $missingConfiguration));
    }

    $password = $clave === null ? '' : (string) $clave;

    $conexion = new mysqli($servidor_bd, $usuario, $password, $bd);
    $conexion->set_charset('utf8mb4');
} catch (RuntimeException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    exit('La configuración de la base de datos no está completa. Póngase en contacto con la persona administradora.');
} catch (mysqli_sql_exception $exception) {
    error_log('Error al conectar con la base de datos: ' . $exception->getMessage());
    http_response_code(500);
    exit('No se ha podido establecer conexión con la base de datos. Inténtelo de nuevo más tarde.');
}

