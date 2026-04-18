<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (isset($_GET['__rewritten_path'])) {
    $rewrittenPath = '/' . ltrim((string) $_GET['__rewritten_path'], '/');

    $queryParams = $_GET;
    unset($queryParams['__rewritten_path']);

    $queryString = http_build_query($queryParams);

    $_GET = $queryParams;
    $_REQUEST = array_merge($_REQUEST, $queryParams);
    unset($_REQUEST['__rewritten_path']);

    $_SERVER['REQUEST_URI'] = $rewrittenPath . ($queryString !== '' ? '?' . $queryString : '');
    $_SERVER['PATH_INFO'] = $rewrittenPath;
    $_SERVER['QUERY_STRING'] = $queryString;

    $publicRoot = realpath(__DIR__);
    $candidate = realpath(__DIR__ . $rewrittenPath);

    // En Vercel todas las rutas pasan por index.php; si la ruta corresponde a un archivo estático válido,
    // lo servimos directamente para evitar 404 de CSS/JS/imagenes.
    if (
        $publicRoot !== false
        && $candidate !== false
        && str_starts_with($candidate, $publicRoot)
        && is_file($candidate)
        && pathinfo($candidate, PATHINFO_EXTENSION) !== 'php'
    ) {
        $extension = strtolower((string) pathinfo($candidate, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'css' => 'text/css; charset=UTF-8',
            'js', 'mjs' => 'application/javascript; charset=UTF-8',
            'json' => 'application/json; charset=UTF-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            default => (function_exists('mime_content_type') ? mime_content_type($candidate) : false) ?: 'application/octet-stream',
        };

        header('Content-Type: ' . $mimeType);
        header('Cache-Control: public, max-age=31536000, immutable');
        readfile($candidate);
        exit;
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__ . '/../bootstrap/app.php')
    ->handleRequest(Request::capture());
