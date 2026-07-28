<?php

namespace App\Http\Controllers;

use App\Security\PasswordPolicy;
use App\Security\PwnedPasswordChecker;
use App\Support\PracearSupport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class AdminController extends Controller
{
    public function __construct(private readonly PwnedPasswordChecker $pwnedPasswordChecker)
    {
    }

    public function index(Request $request): View|RedirectResponse
    {
        if (($redirect = $this->requireAuthentication($request)) instanceof RedirectResponse) {
            return $redirect;
        }

        $currentLanguage = PracearSupport::language($request->query('lang'));
        $search = trim((string) $request->query('caseta', ''));
        $currentPage = max(1, (int) $request->query('page', 1));

        if (strlen($search) > 10 || ($search !== '' && preg_match('/^[A-Za-z0-9-]+$/', $search) !== 1)) {
            return redirect()->to($this->rootUrl(['lang' => $currentLanguage]))
                ->withErrors(['caseta' => 'La búsqueda solo admite hasta 10 letras, números o guiones.']);
        }

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
            'languages' => PracearSupport::languages(),
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

        $search = trim((string) $request->input('caseta', ''));
        if (strlen($search) > 10 || ($search !== '' && preg_match('/^[A-Za-z0-9-]+$/', $search) !== 1)) {
            return back()->withErrors(['caseta' => 'La búsqueda solo admite hasta 10 letras, números o guiones.']);
        }

        return redirect()->to($this->rootUrl([
            'page' => 1,
            'caseta' => $search,
            'lang' => PracearSupport::language($request->input('lang')),
        ]));
    }

    public function marketSections(Request $request): View|RedirectResponse
    {
        if (($redirect = $this->requireAuthentication($request)) instanceof RedirectResponse) {
            return $redirect;
        }

        $baseUrl = rtrim(PracearSupport::baseUrl(), '/');
        $images = [];

        foreach ((array) config('pracear.sections.ameas', []) as $section) {
            $images[] = [
                'src' => $baseUrl . '/img/amea' . $section['indice'] . '.jpg',
                'alt' => 'Imagen de Amea ' . $section['indice'],
                'caption' => 'Amea ' . $section['indice'] . ' / ' . implode('-', $section['range']),
            ];
        }

        foreach ((array) config('pracear.sections.naves', []) as $section) {
            $images[] = [
                'src' => $baseUrl . '/img/nave' . $section['indice'] . '.jpg',
                'alt' => 'Imagen de Nave ' . $section['indice'],
                'caption' => 'Nave ' . $section['indice'] . ' / ' . implode('-', $section['range']),
            ];
        }

        foreach ((array) config('pracear.sections.murallones', []) as $section) {
            $images[] = [
                'src' => $baseUrl . '/img/murallon' . $section['indice'] . '.jpg',
                'alt' => 'Imagen de Murallón ' . $section['indice'],
                'caption' => 'Murallón ' . $section['indice'] . ' / ' . implode('-', $section['range']),
            ];
        }

        return view('admin.market_sections', [
            'baseUrl' => PracearSupport::baseUrl(),
            'currentLang' => PracearSupport::language($request->query('lang')),
            'languages' => PracearSupport::languages(),
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
            'languages' => PracearSupport::languages(),
            'username' => PracearSupport::username($request),
        ]);
    }

    public function changePasswordUpdate(Request $request): RedirectResponse
    {
        if (($redirect = $this->requireAuthentication($request)) instanceof RedirectResponse) {
            return $redirect;
        }

        $userId = PracearSupport::userId($request);
        $username = PracearSupport::username($request);
        $oldPassword = (string) $request->input('old_password', '');
        $newPassword = (string) $request->input('new_password', '');
        $confirmPassword = (string) $request->input('confirm_password', '');

        if ($oldPassword === '' || $newPassword === '' || $confirmPassword === '') {
            return back()->withErrors(['old_password' => 'Todos los campos son obligatorios.']);
        }

        if (strlen($oldPassword) > PasswordPolicy::MAX_LENGTH) {
            return back()->withErrors(['old_password' => 'La contraseña actual supera la longitud permitida.']);
        }

        if ($newPassword !== $confirmPassword) {
            return back()->withErrors(['confirm_password' => 'Las contraseñas no coinciden.']);
        }

        try {
            PasswordPolicy::validate($newPassword, $username);
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['new_password' => $exception->getMessage()]);
        }

        try {
            if ($this->pwnedPasswordChecker->isCompromised($newPassword)) {
                return back()->withErrors(['new_password' => 'La nueva contraseña aparece en filtraciones conocidas. Elija una distinta.']);
            }
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['new_password' => $exception->getMessage()]);
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
            if (PracearSupport::matchingPepper($newPassword, (string) $oldHash) !== null) {
                return back()->withErrors(['new_password' => 'La nueva contraseña no puede ser igual a una de las contraseñas antiguas.']);
            }
        }

        DB::transaction(function () use ($userId, $user, $newPassword): void {
            DB::table('old_passwords')->insert([
                'id_usuario' => $userId,
                'password' => (string) $user->password,
                'date' => now(),
            ]);

            DB::table('usuarios')
                ->where('id', $userId)
                ->update(['password' => PracearSupport::hashPassword($newPassword)]);

            $obsoleteIds = DB::table('old_passwords')
                ->where('id_usuario', $userId)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->skip(10)
                ->take(1000)
                ->pluck('id');

            if ($obsoleteIds->isNotEmpty()) {
                DB::table('old_passwords')->whereIn('id', $obsoleteIds)->delete();
            }
        });

        $request->session()->regenerate();
        $request->session()->put('pracear.issued_at', time());
        $request->session()->put('pracear.last_activity', time());

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
            'languages' => PracearSupport::languages(),
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

        if ($telefono !== '' && preg_match('/^[0-9+\s().-]{3,15}$/', $telefono) !== 1) {
            return back()->withErrors(['telefono' => 'El teléfono solo puede contener números, espacios y los símbolos +, -, ( y ).']);
        }

        if ($casetaPadre !== '' && !$this->validStallCode($casetaPadre)) {
            return back()->withErrors(['caseta_padre' => 'La caseta padre debe tener un código válido entre 001 y 370 (ej: CE001).']);
        }

        if ($casetaPadre !== '' && $casetaPadre === (string) $stall->caseta) {
            return back()->withErrors(['caseta_padre' => 'Un puesto no puede ser su propia caseta padre.']);
        }

        if ($casetaPadre !== '' && !DB::table('puestos')->where('caseta', $casetaPadre)->exists()) {
            return back()->withErrors(['caseta_padre' => 'La caseta padre seleccionada no existe.']);
        }

        if (!array_key_exists($tipoUnity, (array) config('pracear.unity_types', []))) {
            return back()->withErrors(['tipo_unity' => 'El tipo de Unity seleccionado no es válido.']);
        }

        if (!DB::table('naves')->where('id', $idNave)->exists()) {
            return back()->withErrors(['id_nave' => 'La nave seleccionada no es válida.']);
        }

        if ($request->boolean('eliminar_imagen') && $request->hasFile('imagen')) {
            return back()->withErrors(['imagen' => 'No se puede eliminar y reemplazar la imagen en la misma operación.']);
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
            'languages' => PracearSupport::languages(),
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

        if ($tipo === '') {
            return back()->withErrors(['tipo' => 'El tipo es obligatorio.']);
        }

        if (strlen($tipo) > 50) {
            return back()->withErrors(['tipo' => 'El tipo no puede tener más de 50 caracteres.']);
        }

        if (strlen($descripcion) > 450) {
            return back()->withErrors(['descripcion' => 'La descripción no puede tener más de 450 caracteres.']);
        }

        if (!DB::table('puestos_traducciones')
            ->where('puesto_id', $id)
            ->where('codigo_idioma', $translationLanguage)
            ->exists()) {
            return back()->withErrors(['language' => 'No se encontró la traducción solicitada.']);
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
            try {
                DB::table('accesos')->insert([
                    'id_usuario' => PracearSupport::userId($request),
                    'ip' => (string) $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                    'fecha' => now(),
                    'tipo' => 'desconexion',
                ]);
            } catch (Throwable) {
                // La sesión debe poder cerrarse incluso si falla el registro de auditoría.
            }
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
        $baseUrl = rtrim(PracearSupport::baseUrl(), '/');

        if ($params === []) {
            return $baseUrl . '/';
        }

        return $baseUrl . '?' . http_build_query($params);
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
