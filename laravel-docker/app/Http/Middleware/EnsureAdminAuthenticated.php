<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\PracearSupport;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        // The shared check also protects legacy root aliases and destroys
        // expired/deleted-user sessions; never maintain separate auth rules.
        if (!PracearSupport::isAuthenticated($request)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesión caducada.'], 401);
            }

            return redirect()->route('login.show')->withErrors(['login' => 'La sesión ha caducado. Vuelva a iniciar sesión.']);
        }

        return $next($request);
    }
}
