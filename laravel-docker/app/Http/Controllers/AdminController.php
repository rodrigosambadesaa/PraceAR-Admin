<?php

namespace App\Http\Controllers;

use App\Support\PracearSupport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class AdminController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (($redirect = $this->requireAuthentication($request)) instanceof RedirectResponse) {
            return $redirect;
        }

        $currentLanguage = PracearSupport::language($request->query('lang'));
        $search = trim((string) $request->query('caseta', ''));
        $currentPage = max(1, (int) $request->query('page', 1));

        $query = DB::table('puestos as p')
            ->join('puestos_traducciones as pt', function ($join) use ($currentLanguage): void {
                $join->on('p.id', '=', 'pt.puesto_id')
                    ->where('pt.codigo_idioma', '=', $currentLanguage);
            })
            ->join('naves as n', 'p.id_nave', '=', 'n.id')
            ->select([
                'p.id',
                'p.caseta',
                'p.imagen',
                'p.nombre',
                'pt.descripcion',
                'p.contacto',
                'p.telefono',
                'pt.tipo',
                'p.tipo_unity',
                'p.id_nave',
                'n.tipo as nave',
                'p.caseta_padre',
                'p.activo',
            ])
            ->orderBy('p.caseta');

        if ($search !== '') {
            $query->where('p.caseta', 'like', '%' . $search . '%');
        }

        $stalls = $query->paginate(
            (int) config('pracear.results_per_page', 50),
            ['*'],
            'page',
            $currentPage,
        )->withQueryString();

        return view('admin.index', [
            'baseUrl' => PracearSupport::baseUrl(),
            'currentLang' => $currentLanguage,
            'languages' => config('pracear.languages', []),
            'stalls' => $stalls,
            'search' => $search,
            'searchPerformed' => $search !== '',
            'resultsFound' => $stalls->count() > 0,
        ]);
    }

    public function search(Request $request): RedirectResponse
    {
        if (($redirect = $this->requireAuthentication($request)) instanceof RedirectResponse) {
            return $redirect;
        }

        return redirect()->to($this->rootUrl([
            'page' => 1,
            'caseta' => trim((string) $request->input('caseta', '')),
            'lang' => PracearSupport::language($request->input('lang')),
        ]));
    }

    public function marketSections(Request $request): View|RedirectResponse
    {
        if (($redirect = $this->requireAuthentication($request)) instanceof RedirectResponse) {
            return $redirect;
        }

        $images = [];

        foreach ((array) config('pracear.sections.ameas', []) as $section) {
            $images[] = [
                'src' => '/img/amea' . $section['indice'] . '.jpg',
                'alt' => 'Imagen de Amea ' . $section['indice'],
                'caption' => 'Amea ' . $section['indice'] . ' / ' . implode('-', $section['range']),
            ];
        }

        foreach ((array) config('pracear.sections.naves', []) as $section) {
            $images[] = [
                'src' => '/img/nave' . $section['indice'] . '.jpg',
                'alt' => 'Imagen de Nave ' . $section['indice'],
                'caption' => 'Nave ' . $section['indice'] . ' / ' . implode('-', $section['range']),
            ];
        }

        foreach ((array) config('pracear.sections.murallones', []) as $section) {
            $images[] = [
                'src' => '/img/murallon' . $section['indice'] . '.jpg',
                'alt' => 'Imagen de Murallón ' . $section['indice'],
                'caption' => 'Murallón ' . $section['indice'] . ' / ' . implode('-', $section['range']),
            ];
        }

        return view('admin.market_sections', [
            'baseUrl' => PracearSupport::baseUrl(),
            'currentLang' => PracearSupport::language($request->query('lang')),
            'languages' => config('pracear.languages', []),
            'images' => array_slice($images, 0, 12),
        ]);
    }

    public function changePasswordForm(Request $request): View|RedirectResponse
    {
        if (($redirect = $this->requireAuthentication($request)) instanceof RedirectResponse) {
            return $redirect;
        }

        return view('admin.change_password', [
            'baseUrl' => PracearSupport::baseUrl(),
            'currentLang' => PracearSupport::language($request->query('lang')),
            'languages' => config('pracear.languages', []),
            'username' => PracearSupport::username($request),
        ]);
    }

    public function changePasswordUpdate(Request $request): RedirectResponse
    {
        if (($redirect = $this->requireAuthentication($request)) instanceof RedirectResponse) {
            return $redirect;
        }

        PracearSupport::loadStrengthHelpers();

        $userId = PracearSupport::userId($request);
        $username = PracearSupport::username($request);
        $oldPassword = trim((string) $request->input('old_password', ''));
        $newPassword = trim((string) $request->input('new_password', ''));
        $confirmPassword = trim((string) $request->input('confirm_password', ''));

        if ($oldPassword === '' || $newPassword === '' || $confirmPassword === '') {
            return back()->withErrors(['old_password' => 'Todos los campos son obligatorios.']);
        }

        if ($newPassword !== $confirmPassword) {
            return back()->withErrors(['confirm_password' => 'Las contraseñas no coinciden.']);
        }

        if (tiene_espacios_al_principio_o_al_final((string) $request->input('new_password', '')) || tiene_espacios_al_principio_o_al_final((string) $request->input('confirm_password', '')) || tiene_espacios_al_principio_o_al_final((string) $request->input('old_password', ''))) {
            return back()->withErrors(['new_password' => 'Las contraseñas no pueden tener espacios al principio o al final.']);
        }

        if (!es_contrasenha_fuerte($newPassword)) {
            return back()->withErrors(['new_password' => 'La nueva contraseña no cumple con los requisitos de seguridad.']);
        }

        if (tiene_secuencias_numericas_inseguras($newPassword) || tiene_secuencias_alfabeticas_inseguras($newPassword) || tiene_secuencias_caracteres_especiales_inseguras($newPassword) || contrasenha_similar_a_usuario($newPassword, $username)) {
            return back()->withErrors(['new_password' => 'La nueva contraseña no cumple con los requisitos de seguridad.']);
        }

        $user = DB::table('usuarios')->where('id', $userId)->first();

        if (!$user) {
            return back()->withErrors(['old_password' => 'No se encontró la cuenta administradora.']);
        }

        $matchedPepper = PracearSupport::matchingPepper($oldPassword, (string) $user->password);

        if ($matchedPepper === null) {
            return back()->withErrors(['old_password' => 'La contraseña actual es incorrecta.']);
        }

        $oldHashes = DB::table('old_passwords')
            ->where('id_usuario', $userId)
            ->pluck('password');

        foreach ($oldHashes as $oldHash) {
            foreach (PracearSupport::pepperConfig() as $pepperData) {
                $pepper = (string) ($pepperData['PASSWORD_PEPPER'] ?? '');

                if ($pepper !== '' && password_verify($newPassword . $pepper, (string) $oldHash)) {
                    return back()->withErrors(['new_password' => 'La nueva contraseña no puede ser igual a una de las contraseñas antiguas.']);
                }
            }
        }

        DB::table('old_passwords')->insert([
            'id_usuario' => $userId,
            'password' => PracearSupport::hashPassword($oldPassword, $matchedPepper),
            'date' => now(),
        ]);

        DB::table('usuarios')
            ->where('id', $userId)
            ->update(['password' => PracearSupport::hashPassword($newPassword)]);

        return back()->with('status', 'Contraseña cambiada correctamente.');
    }

    public function editForm(Request $request): View|RedirectResponse
    {
        if (($redirect = $this->requireAuthentication($request)) instanceof RedirectResponse) {
            return $redirect;
        }

        $id = (int) $request->query('id');
        $stall = DB::table('puestos')->where('id', $id)->first();

        if (!$stall) {
            return redirect()->to($this->rootUrl(['lang' => PracearSupport::language($request->query('lang'))]))
                ->withErrors(['edit' => 'No se encontró el puesto especificado.']);
        }

        return view('admin.edit', [
            'baseUrl' => PracearSupport::baseUrl(),
            'currentLang' => PracearSupport::language($request->query('lang')),
            'languages' => config('pracear.languages', []),
            'stall' => $stall,
            'naves' => DB::table('naves')->orderBy('id')->get(),
            'unityTypes' => config('pracear.unity_types', []),
            'imageExists' => is_file(PracearSupport::imageDiskPath((string) $stall->caseta)),
        ]);
    }

    public function editUpdate(Request $request): RedirectResponse
    {
        if (($redirect = $this->requireAuthentication($request)) instanceof RedirectResponse) {
            return $redirect;
        }

        $id = (int) $request->query('id');
        $stall = DB::table('puestos')->where('id', $id)->first();

        if (!$stall) {
            return back()->withErrors(['edit' => 'No se encontró el puesto especificado.']);
        }

        $nombre = PracearSupport::cleanText($request->input('nombre'));
        $contacto = PracearSupport::cleanText($request->input('contacto'));
        $telefono = PracearSupport::cleanText($request->input('telefono'));
        $casetaPadre = strtoupper(PracearSupport::cleanText($request->input('caseta_padre')));
        $tipoUnity = (string) $request->input('tipo_unity', 'default');
        $idNave = (int) $request->input('id_nave');

        if ($nombre !== '' && strlen($nombre) > 50) {
            return back()->withErrors(['nombre' => 'El nombre no puede tener más de 50 caracteres.']);
        }

        if ($contacto !== '' && strlen($contacto) > 250) {
            return back()->withErrors(['contacto' => 'El contacto no puede tener más de 250 caracteres.']);
        }

        if ($telefono !== '' && strlen($telefono) > 15) {
            return back()->withErrors(['telefono' => 'El teléfono no puede tener más de 15 caracteres.']);
        }

        if ($casetaPadre !== '' && preg_match('/^(CE|CO|MC|NA|NC)([0-9]{3})$/', $casetaPadre) !== 1) {
            return back()->withErrors(['caseta_padre' => 'La caseta padre debe tener el formato correcto (ej: CE001).']);
        }

        if (!array_key_exists($tipoUnity, (array) config('pracear.unity_types', []))) {
            return back()->withErrors(['tipo_unity' => 'El tipo de Unity seleccionado no es válido.']);
        }

        if (!DB::table('naves')->where('id', $idNave)->exists()) {
            return back()->withErrors(['id_nave' => 'La nave seleccionada no es válida.']);
        }

        try {
            if ($request->boolean('eliminar_imagen')) {
                PracearSupport::deleteImage((string) $stall->caseta);
            }

            if ($request->hasFile('imagen')) {
                PracearSupport::saveVerifiedImage((string) $stall->caseta, $request->file('imagen'));
            }
        } catch (Throwable $throwable) {
            return back()->withErrors(['imagen' => $throwable->getMessage()]);
        }

        DB::table('puestos')
            ->where('id', $id)
            ->update([
                'activo' => $request->boolean('activo'),
                'nombre' => $nombre,
                'contacto' => $contacto,
                'telefono' => $telefono,
                'id_nave' => $idNave,
                'tipo_unity' => $tipoUnity,
                'caseta_padre' => $casetaPadre !== '' ? $casetaPadre : null,
            ]);

        return redirect()->to($this->rootUrl([
            'lang' => PracearSupport::language($request->input('lang', $request->query('lang'))),
        ]) . '#row_' . $id)->with('status', 'Datos actualizados correctamente.');
    }

    public function translationsForm(Request $request): View|RedirectResponse
    {
        if (($redirect = $this->requireAuthentication($request)) instanceof RedirectResponse) {
            return $redirect;
        }

        $id = (int) $request->query('id');
        $translationLanguage = PracearSupport::language($request->query('codigo_idioma'));
        $translation = DB::table('puestos_traducciones')
            ->where('puesto_id', $id)
            ->where('codigo_idioma', $translationLanguage)
            ->first();

        if (!$translation) {
            return redirect()->to($this->rootUrl(['lang' => $translationLanguage]))
                ->withErrors(['language' => 'No se encontró la traducción solicitada.']);
        }

        $stallName = DB::table('puestos')->where('id', $id)->value('nombre');

        return view('admin.translations', [
            'baseUrl' => PracearSupport::baseUrl(),
            'currentLang' => $translationLanguage,
            'languages' => config('pracear.languages', []),
            'translation' => $translation,
            'stallId' => $id,
            'stallName' => $stallName,
        ]);
    }

    public function translationsUpdate(Request $request): RedirectResponse
    {
        if (($redirect = $this->requireAuthentication($request)) instanceof RedirectResponse) {
            return $redirect;
        }

        $id = (int) $request->query('id');
        $translationLanguage = PracearSupport::language($request->query('codigo_idioma'));
        $tipo = PracearSupport::cleanText($request->input('tipo'));
        $descripcion = PracearSupport::cleanText($request->input('descripcion'));

        if (strlen($tipo) > 50) {
            return back()->withErrors(['tipo' => 'El tipo no puede tener más de 50 caracteres.']);
        }

        if (strlen($descripcion) > 450) {
            return back()->withErrors(['descripcion' => 'La descripción no puede tener más de 450 caracteres.']);
        }

        DB::table('puestos_traducciones')
            ->where('puesto_id', $id)
            ->where('codigo_idioma', $translationLanguage)
            ->update([
                'tipo' => $tipo,
                'descripcion' => $descripcion,
            ]);

        return redirect()->to($this->rootUrl([
            'lang' => $translationLanguage,
        ]) . '#row_' . $id)->with('status', 'Traducción actualizada correctamente.');
    }

    public function logout(Request $request): RedirectResponse
    {
        if (PracearSupport::isAuthenticated($request)) {
            DB::table('accesos')->insert([
                'id_usuario' => PracearSupport::userId($request),
                'ip' => (string) $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'fecha' => now(),
                'tipo' => 'desconexion',
            ]);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to($this->rootUrl());
    }

    private function requireAuthentication(Request $request): ?RedirectResponse
    {
        if (!PracearSupport::isAuthenticated($request)) {
            return redirect()->to($this->rootUrl(['lang' => PracearSupport::language($request->query('lang'))]));
        }

        return null;
    }

    private function rootUrl(array $params = []): string
    {
        $query = $params !== [] ? '?' . http_build_query($params) : '';

        return url('/') . $query;
    }
}
