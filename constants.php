<?php

declare(strict_types=1);

require_once __DIR__ . '/config/env_loader.php';

define('DIRNAME', dirname(__FILE__));
define('ADMIN', DIRNAME . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR);
define('ASSETS', DIRNAME . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR);
define('HELPERS', DIRNAME . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR);
define('COMPONENT_ADMIN', DIRNAME . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR);
define('CONNECTION', DIRNAME . DIRECTORY_SEPARATOR . 'connection.php');
define('INDEX_ADMIN', DIRNAME . DIRECTORY_SEPARATOR . '/admin/index.php');
define('SECTIONS', DIRNAME . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'sections' . DIRECTORY_SEPARATOR);
define('CSS_ADMIN', DIRNAME . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR);
define('LANGUAGES', [
    "es" => "Español",
    "en" => "Inglés",
    "gl" => "Gallego",
    "fr" => "Francés",
]);

define('NAVES', [
    "ameas" => [
        ["range" => ["CE001", "CE018"], "title" => "Amea1", "indice" => 1],
        ["range" => ["CE019", "CE037"], "title" => "Amea2", "indice" => 2],
    ],
    "naves" => [
        ["range" => ["NC038", "NC077"], "title" => "Nave1", "indice" => 1],
        ["range" => ["NC120", "NC151", "MC001", "MC002"], "title" => "Nave2", "indice" => 2],
        ["range" => ["NC186", "NC217", "MC002", "MC003"], "title" => "Nave3", "indice" => 3],
        ["range" => ["NC252", "NC291"], "title" => "Nave4", "indice" => 4],
        ["range" => ["NC078", "NC119"], "title" => "Nave5", "indice" => 5],
        ["range" => ["NC152", "NC185", "MC005", "MC006"], "title" => "Nave6", "indice" => 6],
        ["range" => ["NC218", "NC251", "MC004", "MC005"], "title" => "Nave7", "indice" => 7],
        ["range" => ["NC292", "NC333"], "title" => "Nave8", "indice" => 8],
    ],
    "murallones" => [
        ["range" => ["NA334", "NA351"], "title" => "Murallón1", "indice" => 1],
        ["range" => ["NA352", "NA370"], "title" => "Murallón2", "indice" => 2],
    ]
]);

define('UNITY_TYPE', [
    "default" => "Por defecto: default",
    "handicraft" => "Artesanía: handicraft",
    "butcher" => "Carnicería: butcher",
    "delicatessen" => "Chacinería: delicatessen",
    "fish-seafood" => "Peixe e marisco: fish-seafood",
    "cod-frozen" => "Bacallau e conxelados: cod-frozen",
    "poultry-eggs" => "Aves e ovos: poultry-eggs",
    "vegetables-organics" => "Froitas e verduras e ecolóxicos: vegetables-organics",
    "bread-sweets" => "Pan e doces: bread-sweets",
    "flowers" => "Flores: flowers",
    "restaurants" => "Restauración e produtos elaborados: retaurants"

]);

// Datos de conexión y configuración desde .env / variables de entorno
$envVariables = load_project_env(DIRNAME);

// Detectar el protocolo
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];

// Permite forzar la URL base (útil en Docker/reverse proxy) y mantiene fallback histórico.
$configuredBaseUrl = get_env_value('APP_BASE_URL', $envVariables);
if (is_string($configuredBaseUrl) && $configuredBaseUrl !== '') {
    $normalizedBaseUrl = rtrim($configuredBaseUrl, '/') . '/';
    define('BASE_URL', $normalizedBaseUrl);
} else {
    // Fallback histórico para despliegues en subcarpeta.
    $projectDir = basename(__DIR__);
    define('BASE_URL', $protocolo . $host . '/' . $projectDir . '/');
}

define('FLAG_IMAGES_URL', BASE_URL . 'img/flags/');
define('PENCIL_IMAGE_URL', BASE_URL . 'img/pencil.png');

define('JS', BASE_URL . 'js/');
define('JS_ADMIN', BASE_URL . 'admin/js/');
if (!defined('VIRUSTOTAL_API_KEY_FILE')) {
    define('VIRUSTOTAL_API_KEY_FILE', DIRNAME . DIRECTORY_SEPARATOR . 'virustotal_api_key.php');
}

// Detectar el servidor
$servidor = $_SERVER['HTTP_HOST'];

// Construir la URL completa
$url = $protocolo . $servidor . '/';

// Subdominio (compatibilidad con lógica existente)
$subdominio = basename(__DIR__);


// Datos de conexión a la BBDD
// Prioriza APP_ENV; si no está, comprueba varios nombres/IP locales
$appEnv = get_env_value('APP_ENV', $envVariables);
$host = $_SERVER['SERVER_NAME'] ?? ($_SERVER['HTTP_HOST'] ?? '');

// Local detection depends ONLY on the hostname, not on the APP_ENV mode
$isLocal = in_array($host, ['localhost', '127.0.0.1', '::1'], true);

// var_dump($isLocal);
// var_dump($appEnv);
// var_dump($host);

if (!defined('APP_ENV')) {
    // If APP_ENV is set in .env, use it. Otherwise, default to 'development' if local, 'production' if remote.
    define('APP_ENV', $appEnv ?? ($isLocal ? 'development' : 'production'));
}

if ($isLocal) {
    $servidor_bd = get_env_value('PRACEAR_DB_HOST_LOCAL', $envVariables) ?? get_env_value('PRACEAR_DB_HOST', $envVariables);
    $usuario = get_env_value('PRACEAR_DB_USER_LOCAL', $envVariables) ?? get_env_value('PRACEAR_DB_USER', $envVariables);
    $clave = get_env_value('PRACEAR_DB_PASSWORD_LOCAL', $envVariables) ?? get_env_value('PRACEAR_DB_PASSWORD', $envVariables);
    $bd = get_env_value('PRACEAR_DB_NAME_LOCAL', $envVariables) ?? get_env_value('PRACEAR_DB_NAME', $envVariables);
} else {
    // En producción (Dinahosting), intentamos leer las variables estándar o las de PROD
    $servidor_bd = get_env_value('PRACEAR_DB_HOST', $envVariables) ?? get_env_value('PRACEAR_DB_HOST_PROD', $envVariables);
    $usuario = get_env_value('PRACEAR_DB_USER', $envVariables) ?? get_env_value('PRACEAR_DB_USER_PROD', $envVariables);
    $clave = get_env_value('PRACEAR_DB_PASSWORD', $envVariables) ?? get_env_value('PRACEAR_DB_PASSWORD_PROD', $envVariables);
    $bd = get_env_value('PRACEAR_DB_NAME', $envVariables) ?? get_env_value('PRACEAR_DB_NAME_PROD', $envVariables);
}

define('DB_CONFIG', [
    'host' => $servidor_bd,
    'user' => $usuario,
    'password' => $clave,
    'database' => $bd,
]);
