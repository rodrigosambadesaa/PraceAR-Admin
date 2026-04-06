<?php

namespace App\Http\Controllers;

use App\Support\PracearSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

class AdminAjaxController extends Controller
{
    public function quickEditForm(Request $request)
    {
        if (!PracearSupport::isAuthenticated($request)) {
            return new Response('<div class="admin-error-text">Sesión caducada</div>', 401, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        $payload = $request->json()->all();
        $id = (int) ($payload['id'] ?? 0);
        $field = (string) ($payload['field'] ?? '');
        $translationLanguage = PracearSupport::language($payload['codigo_idioma'] ?? null);
        $csrf = csrf_token();

        if ($id <= 0 || $field === '') {
            return new Response('<div class="admin-error-text">Petición inválida</div>', 422, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        if (in_array($field, ['nombre', 'contacto', 'telefono', 'caseta_padre'], true)) {
            $row = DB::table('puestos')->select($field)->where('id', $id)->first();
            $value = $row?->{$field} ?? '';
            $html = "<form id='quick-edit-form' enctype='multipart/form-data'>
                <input type='hidden' name='csrf' value='" . e($csrf) . "'>
                <input type='hidden' name='id' value='" . $id . "'>
                <input type='hidden' name='field' value='" . e($field) . "'>
                <label for='quick-edit-input'>" . e($field) . "</label>
                <input type='text' id='quick-edit-input' name='value' value='" . e((string) $value) . "' required>
                <button type='submit'>Guardar</button>
            </form>
            <div id='quick-edit-msg'></div>";

            return new Response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        if ($field === 'imagen') {
            $caseta = (string) DB::table('puestos')->where('id', $id)->value('caseta');
            $imageExists = $caseta !== '' && is_file(PracearSupport::imageDiskPath($caseta));
            $imageHtml = $imageExists
                ? "<img src='/" . e(PracearSupport::imagePublicPath($caseta)) . "' style='max-width:200px;max-height:200px;'>"
                : '<div>No hay imagen</div>';

            $html = "<form id='quick-edit-img-form' enctype='multipart/form-data'>
                <input type='hidden' name='csrf' value='" . e($csrf) . "'>
                <input type='hidden' name='id' value='" . $id . "'>
                <input type='hidden' name='field' value='imagen'>
                " . $imageHtml . "<br>
                <label for='quick-edit-img'>Reemplazar imagen (.jpg):</label>
                <input type='file' id='quick-edit-img' name='imagen' accept='.jpg,.jpeg'><br>
                <label><input type='checkbox' name='eliminar_imagen' value='1'> Eliminar imagen</label><br>
                <button type='submit'>Guardar</button>
            </form>
            <div id='quick-edit-msg'></div>";

            return new Response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        if (in_array($field, ['tipo', 'descripcion', 'traduccion'], true)) {
            $translation = DB::table('puestos_traducciones')
                ->select(['tipo', 'descripcion'])
                ->where('puesto_id', $id)
                ->where('codigo_idioma', $translationLanguage)
                ->first();

            if (!$translation) {
                return new Response('<div class="admin-error-text">No se encontró la traducción</div>', 404, ['Content-Type' => 'text/html; charset=utf-8']);
            }

            $html = "<form id='quick-edit-trad-form'>
                <input type='hidden' name='csrf' value='" . e($csrf) . "'>
                <input type='hidden' name='id' value='" . $id . "'>
                <input type='hidden' name='field' value='traduccion'>
                <input type='hidden' name='codigo_idioma' value='" . e($translationLanguage) . "'>
                <label for='quick-edit-tipo'>Tipo</label>
                <input type='text' id='quick-edit-tipo' name='tipo' value='" . e((string) $translation->tipo) . "' required><br>
                <label for='quick-edit-descripcion'>Descripción</label>
                <textarea id='quick-edit-descripcion' name='descripcion' maxlength='450'>" . e((string) ($translation->descripcion ?? '')) . "</textarea><br>
                <button type='submit'>Guardar</button>
            </form>
            <div id='quick-edit-msg'></div>";

            return new Response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        return new Response('<div class="admin-error-text">Campo no editable</div>', 422, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function quickEditSave(Request $request): JsonResponse
    {
        if (!PracearSupport::isAuthenticated($request)) {
            return new JsonResponse(['success' => false, 'msg' => 'Sesión caducada'], 401);
        }

        $id = (int) $request->input('id');
        $field = (string) $request->input('field', '');

        if ($id <= 0 || $field === '') {
            return new JsonResponse(['success' => false, 'msg' => 'Petición inválida'], 422);
        }

        if (!hash_equals((string) $request->session()->token(), (string) $request->input('csrf', ''))) {
            return new JsonResponse(['success' => false, 'msg' => 'Petición no válida (CSRF)'], 419);
        }

        if (in_array($field, ['nombre', 'contacto', 'telefono', 'caseta_padre'], true)) {
            $value = PracearSupport::cleanText($request->input('value'));
            DB::table('puestos')->where('id', $id)->update([$field => $value]);

            return new JsonResponse(['success' => true, 'msg' => 'Actualizado correctamente']);
        }

        if ($field === 'imagen') {
            $caseta = (string) DB::table('puestos')->where('id', $id)->value('caseta');

            if ($caseta === '') {
                return new JsonResponse(['success' => false, 'msg' => 'No se encontró la caseta del puesto'], 404);
            }

            try {
                if ($request->boolean('eliminar_imagen')) {
                    PracearSupport::deleteImage($caseta);

                    return new JsonResponse(['success' => true, 'msg' => 'Imagen eliminada']);
                }

                if ($request->hasFile('imagen')) {
                    PracearSupport::saveVerifiedImage($caseta, $request->file('imagen'));

                    return new JsonResponse(['success' => true, 'msg' => 'Imagen actualizada']);
                }
            } catch (Throwable $throwable) {
                return new JsonResponse(['success' => false, 'msg' => $throwable->getMessage()], 422);
            }

            return new JsonResponse(['success' => false, 'msg' => 'No se envió imagen ni se pidió eliminar'], 422);
        }

        if (in_array($field, ['traduccion', 'tipo', 'descripcion'], true)) {
            $translationLanguage = PracearSupport::language($request->input('codigo_idioma'));
            DB::table('puestos_traducciones')
                ->where('puesto_id', $id)
                ->where('codigo_idioma', $translationLanguage)
                ->update([
                    'tipo' => PracearSupport::cleanText($request->input('tipo')),
                    'descripcion' => PracearSupport::cleanText($request->input('descripcion')),
                ]);

            return new JsonResponse(['success' => true, 'msg' => 'Traducción actualizada']);
        }

        return new JsonResponse(['success' => false, 'msg' => 'Campo no editable'], 422);
    }

    public function suggestions(Request $request): JsonResponse
    {
        if (!PracearSupport::isAuthenticated($request)) {
            return new JsonResponse([]);
        }

        $caseta = trim((string) $request->query('caseta', ''));
        $language = PracearSupport::language($request->query('lang'));

        if (strlen($caseta) < 2) {
            return new JsonResponse([]);
        }

        $suggestions = DB::table('puestos as p')
            ->join('puestos_traducciones as pt', function ($join) use ($language): void {
                $join->on('p.id', '=', 'pt.puesto_id')
                    ->where('pt.codigo_idioma', '=', $language);
            })
            ->where('p.caseta', 'like', '%' . $caseta . '%')
            ->orderBy('p.caseta')
            ->limit(10)
            ->pluck('p.caseta');

        return new JsonResponse($suggestions->values()->all());
    }

    public function generatePassword(Request $request): JsonResponse
    {
        if (!PracearSupport::isAuthenticated($request)) {
            return new JsonResponse(['success' => false, 'message' => 'Sesión caducada.'], 401);
        }

        PracearSupport::loadStrengthHelpers();

        $length = max(16, min(1024, (int) $request->input('length', 16)));
        $username = PracearSupport::username($request);

        try {
            $password = $this->buildSecurePassword($length, $username);

            return new JsonResponse([
                'success' => true,
                'password' => $password,
                'stats' => $this->buildPasswordStats($password),
            ]);
        } catch (Throwable $throwable) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error interno: ' . $throwable->getMessage(),
            ], 500);
        }
    }

    public function verifyMaliciousPhoto(Request $request): JsonResponse
    {
        PracearSupport::loadVirusTotalHelper();

        if (!$request->hasFile('imagen')) {
            return new JsonResponse([
                'success' => false,
                'is_malicious' => false,
                'message' => 'No se ha recibido ningún archivo válido para analizar.',
            ], 400);
        }

        $result = check_virus_total($request->file('imagen')->getRealPath());
        $statusCode = (int) ($result['http_status'] ?? (($result['success'] ?? false) ? 200 : 502));

        return new JsonResponse($result, $statusCode);
    }

    private function buildSecurePassword(int $length, ?string $username = null): string
    {
        PracearSupport::loadStrengthHelpers();

        $chunkCount = $this->resolveChunkCount($length);
        $chunkLengths = $this->splitLengthIntoChunks($length, $chunkCount);

        for ($attempt = 0; $attempt < 24; $attempt++) {
            $chunks = [];

            foreach ($chunkLengths as $chunkLength) {
                $chunks[] = $this->buildSecureChunk($chunkLength, $username);
            }

            $candidate = implode('', $chunks);

            if ($this->isSecurePassword($candidate, $username)) {
                return $candidate;
            }
        }

        throw new \RuntimeException('No se pudo generar una contraseña segura.');
    }

    private function buildSecureChunk(int $length, ?string $username = null): string
    {
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $digits = '0123456789';
        $special = '!@#$%^&*()-_=+[]{}|;:,.<>?';

        $safeLength = max(16, min(1024, $length));

        for ($attempt = 0; $attempt < 16; $attempt++) {
            $chars = $this->randomCharsFrom($upper, $safeLength);

            $specialPositions = $this->pickNonAdjacentPositions($safeLength, 3);
            foreach ($specialPositions as $position) {
                $chars[$position] = $this->randomCharFrom($special);
            }

            $reserved = array_fill_keys($specialPositions, true);
            $lowerPosition = $this->pickAvailablePosition($safeLength, $reserved);
            $reserved[$lowerPosition] = true;
            $digitPosition = $this->pickAvailablePosition($safeLength, $reserved);

            $chars[$lowerPosition] = $this->randomCharFrom($lower);
            $chars[$digitPosition] = $this->randomCharFrom($digits);

            $candidate = implode('', $chars);
            if ($this->isSecurePassword($candidate, $username)) {
                return $candidate;
            }
        }

        throw new \RuntimeException('No se pudo generar un bloque de contraseña segura.');
    }

    private function pickNonAdjacentPositions(int $length, int $count): array
    {
        $positions = [];
        $used = [];

        for ($attempt = 0; $attempt < 300 && count($positions) < $count; $attempt++) {
            $position = random_int(1, $length - 2);

            if (isset($used[$position]) || isset($used[$position - 1]) || isset($used[$position + 1])) {
                continue;
            }

            $positions[] = $position;
            $used[$position] = true;
        }

        if (count($positions) !== $count) {
            throw new \RuntimeException('No se pudieron reservar posiciones especiales seguras.');
        }

        return $positions;
    }

    private function pickAvailablePosition(int $length, array $reserved): int
    {
        for ($attempt = 0; $attempt < 200; $attempt++) {
            $position = random_int(1, $length - 2);
            if (!isset($reserved[$position])) {
                return $position;
            }
        }

        throw new \RuntimeException('No se pudo reservar una posición segura en el bloque.');
    }

    private function isSecurePassword(string $password, ?string $username = null): bool
    {
        return es_contrasenha_fuerte($password)
            && !tiene_secuencias_alfabeticas_inseguras($password)
            && !tiene_secuencias_numericas_inseguras($password)
            && !tiene_secuencias_caracteres_especiales_inseguras($password)
            && !contrasenha_similar_a_usuario($password, $username ?? '')
            && !tiene_espacios_al_principio_o_al_final($password);
    }

    private function resolveChunkCount(int $length): int
    {
        if ($length >= 96) {
            return 3;
        }

        if ($length >= 48) {
            return 2;
        }

        return 1;
    }

    private function splitLengthIntoChunks(int $length, int $chunkCount): array
    {
        $normalizedCount = max(1, min(3, $chunkCount));
        $base = intdiv($length, $normalizedCount);
        $remainder = $length % $normalizedCount;
        $chunks = [];

        for ($i = 0; $i < $normalizedCount; $i++) {
            $chunks[] = $base + ($i < $remainder ? 1 : 0);
        }

        return $chunks;
    }

    private function randomCharFrom(string $charset): string
    {
        $bytes = random_bytes(1);
        $index = ord($bytes[0]) % strlen($charset);

        return $charset[$index];
    }

    private function randomCharsFrom(string $charset, int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $bytes = random_bytes($count);
        $chars = [];
        $charsetLength = strlen($charset);

        for ($i = 0; $i < $count; $i++) {
            $chars[] = $charset[ord($bytes[$i]) % $charsetLength];
        }

        return $chars;
    }

    private function shuffleString(string $value): string
    {
        $chars = str_split($value);
        $count = count($chars);

        for ($i = $count - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            $tmp = $chars[$i];
            $chars[$i] = $chars[$j];
            $chars[$j] = $tmp;
        }

        return implode('', $chars);
    }

    private function buildPasswordStats(string $password): array
    {
        $stats = [
            'uppercase' => 0,
            'lowercase' => 0,
            'digits' => 0,
            'special' => 0,
            'entropy' => '-',
            'hashResistanceTime' => 'No disponible',
        ];

        try {
            $stats['uppercase'] = contar_mayusculas($password);
            $stats['lowercase'] = contar_minusculas($password);
            $stats['digits'] = contar_digitos($password);
            $stats['special'] = contar_caracteres_especiales($password);
        } catch (Throwable $throwable) {
        }

        try {
            $stats['entropy'] = entropia($password);
        } catch (Throwable $throwable) {
        }

        try {
            $stats['hashResistanceTime'] = tiempo_estimado_resistencia_ataque_fuerza_bruta($password);
        } catch (Throwable $throwable) {
        }

        return $stats;
    }
}
