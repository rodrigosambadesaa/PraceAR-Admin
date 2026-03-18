<?php

declare(strict_types=1);

namespace App\Core;

final class Bootstrap
{
    public static function initialize(string $projectRoot): void
    {
        error_reporting(E_ALL);

        require_once $projectRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env_loader.php';
        $bootstrapEnv = load_project_env($projectRoot);
        $bootstrapAppEnv = get_env_value('APP_ENV', $bootstrapEnv) ?? 'production';
        $isDevelopment = $bootstrapAppEnv === 'development';

        ini_set('display_errors', $isDevelopment ? '1' : '0');
        ini_set('display_startup_errors', $isDevelopment ? '1' : '0');

        require_once $projectRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'security_headers.php';
        $securityConfig = include $projectRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'security.php';
        apply_security_headers($securityConfig);

        if (ob_get_level() === 0) {
            ob_start();
        }

        require_once $projectRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'session.php';
        start_secure_session();

        require_once $projectRoot . DIRECTORY_SEPARATOR . 'constants.php';
        require_once CONNECTION;

        if (isset($conexion)) {
            $GLOBALS['conexion'] = $conexion;
        }
    }
}
