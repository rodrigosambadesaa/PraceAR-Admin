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
                ? "<img src='" . e(PracearSupport::imageUrl($caseta)) . "' style='max-width:200px;max-height:200px;'>"
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

}
