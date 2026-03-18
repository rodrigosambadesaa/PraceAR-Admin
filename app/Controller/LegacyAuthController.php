<?php

declare(strict_types=1);

namespace App\Controller;

final class LegacyAuthController
{
    public function __construct(private readonly string $projectRoot) {}

    public function login(): void
    {
        $conexion = $GLOBALS['conexion'] ?? null;
        require_once $this->projectRoot . DIRECTORY_SEPARATOR . 'login.php';
    }
}
