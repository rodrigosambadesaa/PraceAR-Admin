<?php

include 'connection.php';

try {
	
    if (!$conn) {
        echo json_encode(["codigo" => 400, "mensaje" => "1. get_all_puestos.php: Error intentando conectar", "respuesta" => ""]);
    } else {
		$sql = "SELECT * FROM `puestos` WHERE caseta_padre is NULL AND activo = 1;";
        $resultado = $conn->query($sql);

        if ($resultado->num_rows > 0) {
            $puestos = [];
            while ($row = $resultado->fetch_assoc()) {
                $puestos[] = $row;
            }
            echo json_encode(["codigo" => 202, "mensaje" => "Puestos listados correctamente.", "respuesta" => $puestos]);
        } else {
            echo json_encode(["codigo" => 203, "mensaje" => "No hay puestos en la BBDD.", "respuesta" => ""]);
        }
		
    }
} catch (Exception $e) {
    echo json_encode(["codigo" => 400, "mensaje" => "2. get_all_puestos.php: Error intentando conectar", "respuesta" => ""]);
}
