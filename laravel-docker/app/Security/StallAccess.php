<?php

declare(strict_types=1);

namespace App\Security;

use App\Support\PracearSupport;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Object-level authorization for the administrative stall catalogue.
 *
 * A valid session is not enough to operate on an arbitrary stall identifier.
 * Existing administrators are migrated as superadmins; newly created users
 * require an explicit nave grant unless deliberately promoted.
 */
final class StallAccess
{
    public static function isSuperAdmin(Request $request): bool
    {
        $userId = PracearSupport::userId($request);

        return $userId > 0
            && DB::table('usuarios')
                ->where('id', $userId)
                ->where('es_superadmin', true)
                ->exists();
    }

    public static function allowedNaveIds(Request $request): Builder
    {
        return DB::table('usuario_naves')
            ->select('nave_id')
            ->where('usuario_id', PracearSupport::userId($request));
    }

    public static function scope(Builder $query, Request $request, string $alias = 'puestos'): Builder
    {
        if (self::isSuperAdmin($request)) {
            return $query;
        }

        return $query->whereIn($alias . '.id_nave', self::allowedNaveIds($request));
    }

    public static function canManageNave(Request $request, int $naveId): bool
    {
        if ($naveId < 1 || !DB::table('naves')->where('id', $naveId)->exists()) {
            return false;
        }

        return self::isSuperAdmin($request)
            || self::allowedNaveIds($request)->where('nave_id', $naveId)->exists();
    }

    public static function availableNaves(Request $request): Builder
    {
        $query = DB::table('naves');

        if (!self::isSuperAdmin($request)) {
            $query->whereIn('id', self::allowedNaveIds($request));
        }

        return $query;
    }
}
