<?php

namespace App\Support;

use App\Security\PepperedPasswordHasher;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final class PracearSupport
{
    public static function projectRoot(): string
    {
        return base_path();
    }

    public static function language(?string $language): string
    {
        $availableLanguages = array_keys(self::languages());

        if (is_string($language) && in_array($language, $availableLanguages, true)) {
            return $language;
        }

        return 'gl';
    }

    /**
     * Obtiene los idiomas disponibles priorizando la tabla de traducciones.
     * Si la consulta falla o no hay datos, usa la configuración estática.
     *
     * @return array<string, string>
     */
    public static function languages(): array
    {
        static $languages;

        if ($languages !== null) {
            return $languages;
        }

        $configured = (array) config('pracear.languages', []);
        $resolved = [];

        try {
            $rows = DB::table('puestos_traducciones')
                ->select(['codigo_idioma', 'nombre_idioma'])
                ->whereNotNull('codigo_idioma')
                ->where('codigo_idioma', '!=', '')
                ->distinct()
                ->orderBy('codigo_idioma')
                ->get();

            foreach ($rows as $row) {
                $code = strtolower(trim((string) ($row->codigo_idioma ?? '')));

                if ($code === '') {
                    continue;
                }

                $label = trim((string) ($row->nombre_idioma ?? ''));
                $resolved[$code] = $label !== '' ? $label : ($configured[$code] ?? strtoupper($code));
            }
        } catch (Throwable) {
            // En caso de error de base de datos mantenemos el fallback de configuración.
        }

        if ($resolved === []) {
            $resolved = $configured;
        }

        if ($resolved === []) {
            $resolved = ['gl' => 'Gallego'];
        }

        $languages = $resolved;

        return $languages;
    }

    public static function baseUrl(): string
    {
        return rtrim(url('/'), '/') . '/';
    }

    public static function isAuthenticated(Request $request): bool
    {
        return $request->session()->has('pracear.user_id');
    }

    public static function userId(Request $request): int
    {
        return (int) $request->session()->get('pracear.user_id');
    }

    public static function username(Request $request): string
    {
        return (string) $request->session()->get('pracear.username', '');
    }

    public static function validateLoginName(string $login): string
    {
        $normalizedLogin = trim($login);

        if ($normalizedLogin === '') {
            throw new InvalidArgumentException('El campo de usuario no puede estar vacío.');
        }

        if (strlen($normalizedLogin) < 3 || strlen($normalizedLogin) > 50) {
            throw new InvalidArgumentException('El nombre de usuario debe tener entre 3 y 50 caracteres.');
        }

        if (preg_match('/^[a-zA-Z0-9._-]+$/', $normalizedLogin) !== 1) {
            throw new InvalidArgumentException('El nombre de usuario solo puede contener letras, números, guiones, puntos y guiones bajos.');
        }

        if (preg_match('/^[\d._-]/', $normalizedLogin) === 1) {
            throw new InvalidArgumentException('El nombre de usuario no puede comenzar por un número, guion, guion bajo o punto.');
        }

        return $normalizedLogin;
    }

    public static function cleanText(?string $value): string
    {
        $normalizedValue = trim((string) $value);
        $normalizedValue = strip_tags($normalizedValue);

        return preg_replace('/\s\s+/', ' ', $normalizedValue) ?? '';
    }

    public static function securityConfig(): array
    {
        return (array) config('security', []);
    }

    public static function argonOptions(): array
    {
        return (array) (self::securityConfig()['argon2'] ?? []);
    }

    public static function currentPepper(): string
    {
        return PepperedPasswordHasher::currentPepper();
    }

    public static function matchingPepper(string $plainPassword, string $storedHash): ?string
    {
        return PepperedPasswordHasher::matchingPepper($plainPassword, $storedHash);
    }

    public static function hashPassword(string $plainPassword, ?string $pepper = null): string
    {
        return PepperedPasswordHasher::hash($plainPassword);
    }

    public static function passwordNeedsRehash(string $storedHash, ?string $matchedPepper = null): bool
    {
        return PepperedPasswordHasher::needsRehash($storedHash, $matchedPepper ?? self::currentPepper());
    }

    public static function imageDiskPath(string $caseta): string
    {
        return self::imageStorageDirectory() . DIRECTORY_SEPARATOR . $caseta . '.jpg';
    }

    public static function imagePublicPath(string $caseta): string
    {
        $publicDirectory = trim((string) config('pracear.public_assets_directory', 'assets'), '/');

        return $publicDirectory . '/' . $caseta . '.jpg';
    }

    public static function imageUrl(string $caseta): string
    {
        return rtrim(self::baseUrl(), '/') . '/' . ltrim(self::imagePublicPath($caseta), '/');
    }

    public static function imageStorageDirectory(): string
    {
        static $directory;

        if ($directory !== null) {
            return $directory;
        }

        $configuredDirectory = trim((string) env('PRACEAR_ASSETS_PATH', ''));
        if ($configuredDirectory !== '') {
            $directory = rtrim($configuredDirectory, DIRECTORY_SEPARATOR);

            return $directory;
        }

        $directory = public_path(trim((string) config('pracear.public_assets_directory', 'assets'), '/'));

        return $directory;
    }

    public static function deleteImage(string $caseta): void
    {
        $path = self::imageDiskPath($caseta);

        if (is_file($path)) {
            unlink($path);
        }
    }

    public static function saveVerifiedImage(string $caseta, UploadedFile $image): void
    {
        app(ImageUploadService::class)->storeJpeg($caseta, $image, self::imageDiskPath($caseta));
    }
}
