<?php

require_once('./constants.php');

$conexion = "Sin conexión";


try {
    // Habilitar excepciones para errores de mysqli
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Intentar la conexión
    $conexion = new mysqli($servidor_bd, $usuario, $clave, $bd);


    // Si la conexión es exitosa
    // echo "Conexión exitosa a la base de datos";

} catch (mysqli_sql_exception $e) {
    // Manejo de la excepción
    echo "Error en la conexión: " . $e->getMessage();
    // Aquí podrías registrar el error en un archivo de logs o realizar otra acción.
} finally {
    // Cerrar la conexión si fue exitosa
    // if (isset($conexion) && $conexion->connect_error == null) {
    //     $conexion->close();
    // }
}

