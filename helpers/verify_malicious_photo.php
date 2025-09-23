<?php

if (!defined('VIRUSTOTAL_API_KEY_FILE')) {
    define(
        'VIRUSTOTAL_API_KEY_FILE',
        dirname(__DIR__) . DIRECTORY_SEPARATOR . 'virustotal_api_key.php'
    );
}

if (defined('VIRUSTOTAL_API_KEY_FILE') && is_readable(VIRUSTOTAL_API_KEY_FILE)) {
    require_once VIRUSTOTAL_API_KEY_FILE;
}

/**
 * Verifica si un archivo subido es potencialmente malicioso utilizando la API de VirusTotal.
 *
 * El archivo debe estar accesible localmente (por ejemplo, el `tmp_name` recibido en `$_FILES`).
 * La función envía el archivo al endpoint de análisis de VirusTotal y devuelve un resumen manejable
 * que indica si la operación tuvo éxito y si el archivo presenta detecciones positivas conocidas.
 *
 * @param string $filePath Ruta local del archivo que se desea analizar.
 *
 * @return array{
 *     success: bool,
 *     is_malicious: bool,
 *     message: string,
 *     data?: array
 * }
 */
function check_virus_total(string $filePath): array
{
    if (!is_readable($filePath)) {
        return [
            'success' => false,
            'is_malicious' => false,
            'message' => 'No se pudo acceder al archivo temporal que se quiere analizar.',
            'http_status' => 500,
        ];
    }

    if (!defined('VIRUSTOTAL_API_KEY_FILE') || !is_readable(VIRUSTOTAL_API_KEY_FILE)) {
        return [
            'success' => false,
            'is_malicious' => false,
            'message' => 'No se encontró la configuración de la clave de la API de VirusTotal.',
            'http_status' => 500,
        ];
    }

    if (!defined('VIRUSTOTAL_API_KEY') || trim((string) VIRUSTOTAL_API_KEY) === '') {
        return [
            'success' => false,
            'is_malicious' => false,
            'message' => 'No se ha configurado la clave de la API de VirusTotal.',
            'http_status' => 500,
        ];
    }

    $apiKey = VIRUSTOTAL_API_KEY;
    $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
    $fileName = basename($filePath);

    $postFields = [
        'apikey' => $apiKey,
        'file' => curl_file_create($filePath, $mimeType, $fileName)
    ];

    $ch = curl_init('https://www.virustotal.com/vtapi/v2/file/scan');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_USERAGENT => 'PraceAR-Admin/1.0 (+https://virustotal.com)',
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $result = curl_exec($ch);

    if ($result === false) {
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'success' => false,
            'is_malicious' => false,
            'message' => 'No se pudo contactar con el servicio de análisis: ' . $error,
            'http_status' => 502,
        ];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return [
            'success' => false,
            'is_malicious' => false,
            'message' => "El servicio de análisis devolvió un código inesperado ($httpCode).",
            'http_status' => 502,
        ];
    }

    $decoded = json_decode($result, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'is_malicious' => false,
            'message' => 'La respuesta del servicio no es un JSON válido.',
            'http_status' => 502,
        ];
    }

    $positives = isset($decoded['positives']) ? (int) $decoded['positives'] : 0;
    $isMalicious = $positives > 0;

    $message = $decoded['verbose_msg'] ?? (
        $isMalicious
            ? 'La imagen presenta detecciones positivas conocidas.'
            : 'La imagen se envió correctamente a VirusTotal para su análisis.'
    );

    return [
        'success' => true,
        'is_malicious' => $isMalicious,
        'message' => $message,
        'data' => $decoded,
        'http_status' => 200,
    ];
}

if (
    php_sapi_name() !== 'cli'
    && isset($_SERVER['REQUEST_METHOD'])
    && $_SERVER['REQUEST_METHOD'] === 'POST'
    && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')
) {
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'is_malicious' => false,
            'message' => 'No se ha recibido ningún archivo válido para analizar.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result = check_virus_total($_FILES['file']['tmp_name']);

    http_response_code($result['http_status'] ?? ($result['success'] ? 200 : 502));

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}
