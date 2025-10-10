<?php
declare(strict_types=1);
header('Content-Type: application/json');
ini_set('display_errors', 0);

// Incluye la conexión a la base de datos
require_once(__DIR__ . '/../connection.php');

$caseta = $_GET['caseta'] ?? '';
$lang = $_GET['lang'] ?? 'gl';

if (strlen($caseta) < 2) {
    echo json_encode([]);
    exit;
}

if (!isset($conexion) || !$conexion) {
    echo json_encode(['error' => 'No hay conexión a la base de datos']);
    exit;
}

try {
    $sql = "SELECT p.caseta FROM puestos p
            RIGHT JOIN puestos_traducciones pt ON p.id = pt.puesto_id
            WHERE pt.codigo_idioma = ? AND p.caseta LIKE ?
            ORDER BY p.caseta LIMIT 10";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Error en la consulta']);
        exit;
    }
    $like = "%$caseta%";
    $stmt->bind_param('ss', $lang, $like);
    $stmt->execute();
    $res = $stmt->get_result();
    $sugerencias = [];
    while ($row = $res->fetch_assoc()) {
        $sugerencias[] = $row['caseta'];
    }
    echo json_encode($sugerencias);
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}