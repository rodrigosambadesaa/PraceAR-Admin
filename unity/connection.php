<?php
declare(strict_types=1);

// Usamos la configuración de la raíz para asegurar consistencia
require_once __DIR__ . '/../constants.php';

$conn = null;

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    // Verificamos si las variables de la raíz están definidas
    // En el archivo de constants.php raíz, las variables son $servidor_bd, $usuario, $clave, $bd
    $missingConfiguration = [];

    if (!isset($servidor_bd) || $servidor_bd === '') {
        $missingConfiguration[] = 'PRACEAR_DB_HOST';
    }

    if (!isset($usuario) || $usuario === '') {
        $missingConfiguration[] = 'PRACEAR_DB_USER';
    }

    if (!isset($bd) || $bd === '') {
        $missingConfiguration[] = 'PRACEAR_DB_NAME';
    }

    if (!empty($missingConfiguration)) {
        throw new RuntimeException('Faltan variables de entorno requeridas (root config): ' . implode(', ', $missingConfiguration));
    }
    
    // Convertimos clave nula a string vacío por seguridad
    $password = (isset($clave) && $clave !== null) ? (string) $clave : '';

    $conn = new mysqli($servidor_bd, $usuario, $password, $bd);
    $conn->set_charset('utf8mb4');

} catch (Throwable $e) {
    // Mantener logs para depuración interna
    error_log('[UNITY][DB_CONNECTION] ' . $e->getMessage());

    // Respuesta JSON para el cliente Unity
    header('Content-Type: application/json');
    http_response_code(500); 
    
    // El formato esperado por Unity parece ser { codigo, mensaje, respuesta }
    echo json_encode([
        'codigo' => 500,
        'mensaje' => 'Error de conexión con la base de datos.',
        'respuesta' => 'Contacte con la administración.'
    ]);
    exit;
}
