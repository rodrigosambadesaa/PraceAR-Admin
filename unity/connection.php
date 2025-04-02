<?php
include 'constants.php';
$conn = null;

try {
    $conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
    if (!$conn) {
        throw new Exception("Conexión fallida: " . mysqli_connect_error());
    }

} catch (Exception $e) {
    echo json_encode([
        "codigo" => 400,
        "mensaje" => "connection.php: Error intentando conectar",
        "respuesta" => $e->getMessage()
    ]);
    exit;
}
