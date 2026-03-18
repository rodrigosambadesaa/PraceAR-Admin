<?php

declare(strict_types=1);

namespace App\Core;

use App\Http\Request;

final class Router
{
    /** @var array<string, callable(Request): void> */
    private array $routes = [];

    private string $defaultRoute = 'index';

    /** @param callable(Request): void $handler */
    public function add(string $route, callable $handler): void
    {
        $this->routes[$route] = $handler;
    }

    public function setDefault(string $route): void
    {
        $this->defaultRoute = $route;
    }

    public function dispatch(string $route, Request $request): void
    {
        $handler = $this->routes[$route] ?? $this->routes[$this->defaultRoute] ?? null;
        if ($handler === null) {
            http_response_code(500);
            echo 'No hay rutas configuradas.';
            return;
        }

        $handler($request);
    }
}
