<?php

namespace App\Http\Controllers;

use App\Security\PasswordPolicy;
use App\Services\VirusTotalScanner;
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
                ? "<img class='quick-edit-preview' src='" . e(PracearSupport::imageUrl($caseta)) . "' alt='Vista previa del puesto'>"
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

            $maximums = ['nombre' => 50, 'contacto' => 250, 'telefono' => 15, 'caseta_padre' => 5];
            if (strlen($value) > $maximums[$field]) {
                return new JsonResponse(['success' => false, 'msg' => 'El valor supera la longitud permitida.'], 422);
            }

            if ($field === 'nombre' && $value === '') {
                return new JsonResponse(['success' => false, 'msg' => 'El nombre es obligatorio.'], 422);
            }

            if ($field === 'telefono' && $value !== '' && preg_match('/^[0-9+\s().-]{3,15}$/', $value) !== 1) {
                return new JsonResponse(['success' => false, 'msg' => 'El teléfono no tiene un formato válido.'], 422);
            }

            if ($field === 'caseta_padre') {
                $value = strtoupper($value);
                if ($value !== '' && !$this->validStallCode($value)) {
                    return new JsonResponse(['success' => false, 'msg' => 'La caseta padre no tiene un formato válido.'], 422);
                }
            }

            $stall = DB::table('puestos')->select(['id', 'caseta'])->where('id', $id)->first();
            if (!$stall) {
                return new JsonResponse(['success' => false, 'msg' => 'No se encontró el puesto.'], 404);
            }

            if ($field === 'caseta_padre' && $value !== '') {
                if ($value === (string) $stall->caseta) {
                    return new JsonResponse(['success' => false, 'msg' => 'Un puesto no puede ser su propia caseta padre.'], 422);
                }

                if (!DB::table('puestos')->where('caseta', $value)->exists()) {
                    return new JsonResponse(['success' => false, 'msg' => 'La caseta padre seleccionada no existe.'], 422);
                }
            }

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
            $type = PracearSupport::cleanText($request->input('tipo'));
            $description = PracearSupport::cleanText($request->input('descripcion'));

            if ($type === '') {
                return new JsonResponse(['success' => false, 'msg' => 'El tipo es obligatorio.'], 422);
            }

            if (strlen($type) > 50 || strlen($description) > 450) {
                return new JsonResponse(['success' => false, 'msg' => 'La traducción supera la longitud permitida.'], 422);
            }

            $translation = DB::table('puestos_traducciones')
                ->where('puesto_id', $id)
                ->where('codigo_idioma', $translationLanguage);

            if (!$translation->exists()) {
                return new JsonResponse(['success' => false, 'msg' => 'No se encontró la traducción.'], 404);
            }

            $translation
                ->update([
                    'tipo' => $type,
                    'descripcion' => $description,
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

        if (
            strlen($caseta) < 2
            || strlen($caseta) > 10
            || preg_match('/^[A-Za-z0-9-]+$/', $caseta) !== 1
        ) {
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

        $length = filter_var(
            $request->input('length', 20),
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => PasswordPolicy::MIN_LENGTH,
                    'max_range' => PasswordPolicy::MAX_LENGTH,
                ],
            ],
        );

        if ($length === false) {
            return new JsonResponse([
                'success' => false,
                'message' => sprintf(
                    'La longitud debe estar entre %d y %d caracteres.',
                    PasswordPolicy::MIN_LENGTH,
                    PasswordPolicy::MAX_LENGTH,
                ),
            ], 422);
        }

        $username = PracearSupport::username($request);

        try {
            $password = PasswordPolicy::generate($length, $username);

            return new JsonResponse([
                'success' => true,
                'password' => $password,
                'stats' => PasswordPolicy::statistics($password),
            ]);
        } catch (Throwable $throwable) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No se pudo generar una contraseña en este momento.',
            ], 500);
        }
    }

    public function verifyMaliciousPhoto(Request $request, VirusTotalScanner $scanner): JsonResponse
    {
        if (!$request->hasFile('imagen')) {
            return new JsonResponse([
                'success' => false,
                'is_malicious' => false,
                'message' => 'No se ha recibido ningún archivo válido para analizar.',
            ], 400);
        }

        try {
            $image = $request->file('imagen');
            if (!$image->isValid() || $image->getSize() === false || $image->getSize() > 4_194_304) {
                return new JsonResponse(['success' => false, 'is_malicious' => false, 'message' => 'La imagen no es válida o supera 4 MB.'], 422);
            }

            $result = $scanner->scan((string) $image->getRealPath());

            return new JsonResponse($result, 200);
        } catch (Throwable $throwable) {
            return new JsonResponse([
                'success' => false,
                'is_malicious' => false,
                'message' => $throwable->getMessage(),
            ], 422);
        }
    }

    private function validStallCode(string $code): bool
    {
        if (preg_match('/^(?:CE|CO|MC|NA|NC)([0-9]{3})$/', $code, $matches) !== 1) {
            return false;
        }

        $number = (int) $matches[1];

        return $number >= 1 && $number <= 370;
    }

}
