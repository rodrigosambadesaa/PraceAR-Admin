<?php
include 'constants.php';

require_once 'constants.php';

$conn = null;

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);

    if (!$conn) {
        throw new Exception('No se pudo establecer la conexión con la base de datos.');
        $missingConfiguration = [];
    }

    if (!is_string($servidor_bd) || $servidor_bd === '') {
        $missingConfiguration[] = 'PRACEAR_DB_HOST';
    }

    if (!is_string($usuario) || $usuario === '') {
        $missingConfiguration[] = 'PRACEAR_DB_USER';
    }

    if (!is_string($bd) || $bd === '') {
        $missingConfiguration[] = 'PRACEAR_DB_NAME';
    }

    if (!empty($missingConfiguration)) {
        throw new RuntimeException('Faltan variables de entorno requeridas: ' . implode(', ', $missingConfiguration));
    }
} catch (Throwable $e) {
    error_log('[UNITY][DB_CONNECTION] ' . $e->getMessage());

    echo json_encode([
        'codigo' => 400,
        'mensaje' => 'connection.php: Error intentando conectar',
        'respuesta' => ''
    $password = $clave === null ? '' : (string) $clave;

    $conn = new mysqli($servidor_bd, $usuario, $password, $bd);
    $conn->set_charset('utf8mb4');
} catch (RuntimeException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode([
        'codigo' => 500,
        'mensaje' => 'La configuración de la base de datos no está completa.',
        'respuesta' => 'Contacte con la persona administradora para revisar la configuración.',
    ]);
    exit;
} catch (mysqli_sql_exception $exception) {
    error_log('Error al conectar con la base de datos: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode([
        'codigo' => 500,
        'mensaje' => 'No se ha podido establecer conexión con la base de datos.',
        'respuesta' => 'Inténtelo de nuevo más tarde.',
    ]);
    exit;
}
