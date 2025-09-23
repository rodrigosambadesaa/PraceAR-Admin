<?php

require_once __DIR__ . '/../config/env_loader.php';

// Detectar la ruta raíz del proyecto dinámicamente
define('DIRNAME', dirname(__FILE__)); // Directorio donde está este archivo
$projectRoot = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', DIRNAME)); // Ruta relativa

// Detectar el protocolo
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

// Detectar el servidor
$servidor = $_SERVER['HTTP_HOST'];

// Construir la URL base del proyecto dinámicamente
define('BASE_URL', $protocolo . $servidor . $projectRoot . '/');

// Definir constantes para rutas absolutas
define('ADMIN', DIRNAME . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR);
define('ASSETS', DIRNAME . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR);
define('HELPERS', DIRNAME . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR);
define('COMPONENT_ADMIN', DIRNAME . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR);
define('CONNECTION', DIRNAME . DIRECTORY_SEPARATOR . 'connection.php');
define('INDEX_ADMIN', DIRNAME . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'index.php');
define('SECTIONS', DIRNAME . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'sections' . DIRECTORY_SEPARATOR);
define('CSS_ADMIN', DIRNAME . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR);

// Definir constantes para URLs dinámicas
define('FLAG_IMAGES_URL', BASE_URL . 'img/flags/');
define('PENCIL_IMAGE_URL', BASE_URL . 'img/pencil.png');
define('JS', BASE_URL . 'js/');
define('JS_ADMIN', BASE_URL . 'admin/js/');

// Idiomas
define('LANGUAGES', [
    "es" => "Español",
    "en" => "Inglés",
    "gl" => "Gallego",
    "fr" => "Francés",
]);

// Definición de naves
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
    ],
]);

// Tipos de unidad
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
    "restaurants" => "Restauración e produtos elaborados: retaurants",
]);

// Datos de conexión a la BBDD
$isLocalEnvironment = isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] === 'localhost';

if (!function_exists('unity_getenv_or_default')) {
    /**
     * Obtener una variable de entorno o usar un valor por defecto.
     */
    function unity_getenv_or_default(string $key, $default)
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
                return $_ENV[$key];
            }

            return $default;
        }

        return $value;
    }
}

if (!defined('DB_SERVER')) {
    define('DB_SERVER', unity_getenv_or_default('DB_SERVER', $isLocalEnvironment ? 'localhost' : 'db5016239277.hosting-data.io'));
}

if (!defined('DB_USER')) {
    define('DB_USER', unity_getenv_or_default('DB_USER', $isLocalEnvironment ? 'root' : 'dbu2777657'));
}

if (!defined('DB_PASS')) {
    define('DB_PASS', unity_getenv_or_default('DB_PASS', $isLocalEnvironment ? '' : 'apdtmMdp27042304()'));
}

if (!defined('DB_DATABASE')) {
    define('DB_DATABASE', unity_getenv_or_default('DB_DATABASE', 'dbs13217995'));
}

$envVariables = load_project_env(dirname(__DIR__));

$servidor_bd = get_env_value('PRACEAR_DB_HOST', $envVariables);
$usuario = get_env_value('PRACEAR_DB_USER', $envVariables);
$clave = get_env_value('PRACEAR_DB_PASSWORD', $envVariables);
$bd = get_env_value('PRACEAR_DB_NAME', $envVariables);

if (!defined('DB_CONFIG')) {
    define('DB_CONFIG', [
        'host' => $servidor_bd,
        'user' => $usuario,
        'password' => $clave,
        'database' => $bd,
    ]);
}
