<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "connect-src 'self' https://www.virustotal.com",
            "font-src 'self' data:",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "img-src 'self' data: blob:",
            "media-src 'self'",
            "object-src 'none'",
            "script-src 'self'",
            "style-src 'self'",
            "style-src-attr 'unsafe-inline'",
            "style-src-elem 'self'",
            "worker-src 'self'",
        ];

        if ($request->isSecure()) {
            $directives[] = 'upgrade-insecure-requests';
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $directives));
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), browsing-topics=()');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');

        if ($request->isSecure() && app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload');
        }

        if (!$request->is('up') && !$request->is('unity/*')) {
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        $response->headers->remove('X-Powered-By');

        return $response;
    }
}
