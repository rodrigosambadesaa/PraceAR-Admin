<?php
include 'constants.php';

$conn = null;

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);

    if (!$conn) {
        throw new Exception('No se pudo establecer la conexión con la base de datos.');
    }
} catch (Throwable $e) {
    error_log('[UNITY][DB_CONNECTION] ' . $e->getMessage());

    echo json_encode([
        'codigo' => 400,
        'mensaje' => 'connection.php: Error intentando conectar',
        'respuesta' => ''
    ]);
    exit;
}
