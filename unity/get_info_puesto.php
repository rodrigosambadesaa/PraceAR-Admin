<?php

require_once 'connection.php';

try {
    if (!$conn) {
        echo json_encode(["codigo" => 400, "mensaje" => "1. get_info_puestos.php: Error intentando conectar", "respuesta" => ""]);
    } else {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $sql = "SELECT * FROM `puestos` WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $puesto = [];
                while ($row = $resultado->fetch_assoc()) {
                    $puesto[] = $row;
                }
                echo json_encode(["codigo" => 202, "mensaje" => "El puesto existe en el sistema", "respuesta" => $puesto]);
            } else {
                echo json_encode(["codigo" => 203, "mensaje" => "El puesto no existe en el sistema", "respuesta" => ""]);
            }
            $stmt->close();
        } else {
            echo json_encode(["codigo" => 402, "mensaje" => "Faltan datos para ejecutar la acción solicitada", "respuesta" => ""]);
        }
    }
} catch (Exception $e) {
    echo json_encode(["codigo" => 400, "mensaje" => "2. get_info_puestos.php: Error intentando conectar", "respuesta" => ""]);
}
