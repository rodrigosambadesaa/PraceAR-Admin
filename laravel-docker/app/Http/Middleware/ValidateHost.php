<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ValidateHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $configured = (array) config('security.allowed_hosts', []);
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (is_string($appHost) && $appHost !== '') {
            $configured[] = strtolower($appHost);
        }

        if (app()->environment(['local', 'testing'])) {
            $configured = array_merge($configured, ['localhost', '127.0.0.1', '::1']);
        }

        $host = strtolower($request->getHost());

        if ($configured === [] || !in_array($host, array_unique($configured), true)) {
            abort(400, 'Host no permitido.');
        }

        return $next($request);
    }
}
