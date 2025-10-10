<?php
declare(strict_types=1);

session_start();

require_once dirname(__DIR__, 2) . '/constants.php';
require_once HELPERS . 'clean_input.php';
require_once HELPERS . 'password_generator.php';

header('Content-Type: application/json; charset=utf-8');

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'success' => false,
        'message' => 'Método no permitido.',
    ], 405);
}

$rawInput = file_get_contents('php://input') ?: '';
$data = json_decode($rawInput, true);

if (!is_array($data)) {
    $data = $_POST;
}

$csrf = isset($data['csrf']) ? (string)$data['csrf'] : '';

if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
    json_response([
        'success' => false,
        'message' => 'Petición no válida (CSRF).',
    ], 403);
}

$lengthValue = $data['length'] ?? null;

if (!is_string($lengthValue) && !is_numeric($lengthValue)) {
    json_response([
        'success' => false,
        'message' => 'La longitud debe ser un número natural válido.',
    ], 400);
}

$length = (int)limpiar_input((string)$lengthValue);

if ($length < 16 || $length > 500) {
    json_response([
        'success' => false,
        'message' => 'La longitud debe estar entre 16 y 500 caracteres.',
    ], 400);
}

try {
    $password = generate_secure_password($length, $_SESSION['nombre_usuario'] ?? null);

    $stats = [
        'uppercase' => contar_mayusculas($password),
        'lowercase' => contar_minusculas($password),
        'digits' => contar_digitos($password),
        'special' => contar_caracteres_especiales($password),
        'entropy' => entropia($password),
        'hashResistanceTime' => tiempo_estimado_resistencia_ataque_fuerza_bruta($password),
    ];

    json_response([
        'success' => true,
        'password' => $password,
        'stats' => $stats,
    ]);
} catch (Throwable $exception) {
    error_log('Error al generar contraseña: ' . $exception->getMessage());
    json_response([
        'success' => false,
        'message' => 'No se pudo generar una contraseña segura. Inténtelo de nuevo más tarde.',
    ], 500);
}

