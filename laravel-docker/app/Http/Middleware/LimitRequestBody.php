<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class LimitRequestBody
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->isMethodSafe()) {
            $maximum = 64 * 1024;

            if ($request->is('/', 'index.php', 'login', 'login.php')) {
                $maximum = 16 * 1024;
            } elseif (
                $request->is('admin/edit.php')
                || $request->is('admin/ajax_quick_edit_save.php')
                || $request->is('helpers/verify_malicious_photo.php')
            ) {
                $maximum = (int) config('security.max_request_body_bytes', 5 * 1024 * 1024);
            }

            $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
            if ($contentLength > $maximum) {
                abort(413, 'La petición supera el tamaño permitido.');
            }
        }

        return $next($request);
    }
}
