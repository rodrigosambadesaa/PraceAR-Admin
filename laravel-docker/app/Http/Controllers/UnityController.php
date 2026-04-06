<?php

namespace App\Http\Controllers;

use App\Support\PracearSupport;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class UnityController extends Controller
{
    public function connection(): JsonResponse
    {
        return $this->jsonResponse(202, 'Conexión disponible.', '');
    }

    public function allStalls(): JsonResponse
    {
        try {
            $stalls = DB::table('puestos')
                ->whereNull('caseta_padre')
                ->where('activo', 1)
                ->get();

            if ($stalls->isEmpty()) {
                return $this->jsonResponse(203, 'No hay puestos en la BBDD.', '');
            }

            return $this->jsonResponse(202, 'Puestos listados correctamente.', $stalls);
        } catch (Throwable) {
            return $this->jsonResponse(400, '2. get_all_puestos.php: Error intentando conectar', '');
        }
    }

    public function stallInfo(Request $request): JsonResponse
    {
        $stallId = (int) $request->query('id', 0);

        if ($stallId <= 0) {
            return $this->jsonResponse(402, 'Faltan datos para ejecutar la acción solicitada', '');
        }

        try {
            $stall = DB::table('puestos')->where('id', $stallId)->get();

            if ($stall->isEmpty()) {
                return $this->jsonResponse(203, 'El puesto no existe en el sistema', '');
            }

            return $this->jsonResponse(202, 'El puesto existe en el sistema', $stall);
        } catch (Throwable) {
            return $this->jsonResponse(400, '2. get_info_puestos.php: Error intentando conectar', '');
        }
    }

    public function naveInfo(Request $request, string $endpoint): JsonResponse
    {
        $idNave = trim((string) $request->query('id_nave', ''));

        if ($idNave === '') {
            return $this->jsonResponse(402, 'Faltan datos para ejecutar la acción solicitada', '');
        }

        $endpointConfig = config('unity.nave_endpoints.' . $endpoint);

        if (!is_array($endpointConfig)) {
            return $this->jsonResponse(400, 'Error intentando conectar', '');
        }

        $language = PracearSupport::language((string) $request->input('language', 'gl'));

        try {
            $query = DB::table('puestos as p')
                ->join('puestos_traducciones as pt', 'p.id', '=', 'pt.puesto_id')
                ->select([
                    'p.id',
                    'p.caseta',
                    'pt.descripcion',
                    'p.contacto',
                    'p.telefono',
                    'p.id_nave',
                    'p.nombre',
                    'p.tipo_unity',
                    'pt.tipo',
                ])
                ->where('pt.codigo_idioma', $language)
                ->where('p.id_nave', $idNave)
                ->whereNull('p.caseta_padre')
                ->where('p.activo', 1);

            $this->applyEndpointConditions($query, (array) ($endpointConfig['conditions'] ?? []));

            $order = strtolower((string) ($endpointConfig['order'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
            $stalls = $query->orderBy('p.caseta', $order)->get();

            if ($stalls->isEmpty()) {
                return $this->jsonResponse(203, 'El puesto no existe en el sistema', '');
            }

            return $this->jsonResponse(202, 'El puesto existe en el sistema', $stalls);
        } catch (Throwable) {
            return $this->jsonResponse(400, 'Error intentando conectar', '');
        }
    }

    private function applyEndpointConditions(Builder $query, array $conditions): void
    {
        $query->where(function (Builder $nestedQuery) use ($conditions): void {
            foreach ($conditions as $condition) {
                if (isset($condition['between']) && is_array($condition['between']) && count($condition['between']) === 2) {
                    $nestedQuery->orWhereBetween('p.caseta', [$condition['between'][0], $condition['between'][1]]);
                }

                if (isset($condition['equals']) && is_string($condition['equals'])) {
                    $nestedQuery->orWhere('p.caseta', $condition['equals']);
                }
            }
        });
    }

    private function jsonResponse(int $code, string $message, mixed $payload): JsonResponse
    {
        return new JsonResponse([
            'codigo' => $code,
            'mensaje' => $message,
            'respuesta' => $payload,
        ]);
    }
}
