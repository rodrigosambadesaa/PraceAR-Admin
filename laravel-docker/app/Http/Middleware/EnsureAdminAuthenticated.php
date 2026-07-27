<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\PracearSupport;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = PracearSupport::userId($request);
        $issuedAt = (int) $request->session()->get('pracear.issued_at', 0);
        $lastActivity = (int) $request->session()->get('pracear.last_activity', 0);
        $now = time();
        $idleTimeout = (int) config('security.session.idle_timeout_seconds', 1800);
        $absoluteTimeout = (int) config('security.session.absolute_timeout_seconds', 28800);

        $expired = $userId <= 0
            || $issuedAt <= 0
            || $lastActivity <= 0
            || ($now - $lastActivity) > $idleTimeout
            || ($now - $issuedAt) > $absoluteTimeout;

        if (!$expired) {
            $expired = !DB::table('usuarios')->where('id', $userId)->exists();
        }

        if ($expired) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesión caducada.'], 401);
            }

            return redirect()->route('login.show')->withErrors(['login' => 'La sesión ha caducado. Vuelva a iniciar sesión.']);
        }

        $request->session()->put('pracear.last_activity', $now);

        return $next($request);
    }
}
