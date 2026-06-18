<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class PracearSupport
{
    /**
     * @return array<int, array{PASSWORD_PEPPER_NAME:string, PASSWORD_PEPPER:string, CURRENT:bool}>
     */
    private static function envPepperConfig(): array
    {
        $currentPepper = trim((string) env('PASSWORD_PEPPER_CURRENT', ''));
        $legacyRaw = env('PASSWORD_PEPPER_LEGACY_JSON', '[]');
        $entries = [];

        if ($currentPepper !== '') {
            $entries[] = [
                'PASSWORD_PEPPER_NAME' => 'current',
                'PASSWORD_PEPPER' => $currentPepper,
                'CURRENT' => true,
            ];
        }

        if (!is_string($legacyRaw) || trim($legacyRaw) === '') {
            return $entries;
        }

        $decoded = json_decode($legacyRaw, true);
        if (!is_array($decoded)) {
            return $entries;
        }

        foreach ($decoded as $index => $legacyPepper) {
            $secret = trim((string) $legacyPepper);

            if ($secret === '') {
                continue;
            }

            $entries[] = [
                'PASSWORD_PEPPER_NAME' => 'legacy_' . ($index + 1),
                'PASSWORD_PEPPER' => $secret,
                'CURRENT' => false,
            ];
        }

        return $entries;
    }

    public static function projectRoot(): string
    {
        $configuredRoot = (string) config('pracear.project_root', dirname(base_path()));
        $securityPath = $configuredRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'security.php';

        if (is_file($securityPath)) {
            return $configuredRoot;
        }

        return base_path();
    }

    public static function loadStrengthHelpers(): void
    {
        require_once self::projectRoot() . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'verify_strong_password.php';
    }

    public static function loadVirusTotalHelper(): void
    {
        require_once self::projectRoot() . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'verify_malicious_photo.php';
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
        $configuredBaseUrl = (string) config('app.url', url('/'));

        return rtrim($configuredBaseUrl, '/') . '/';
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

        if (preg_match('/^[\d.-]/', $normalizedLogin) === 1) {
            throw new InvalidArgumentException('El nombre de usuario no puede comenzar por un número, un guion o un punto.');
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
        static $config;

        if ($config === null) {
            $config = require self::projectRoot() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'security.php';
        }

        return $config;
    }

    public static function argonOptions(): array
    {
        return (array) (self::securityConfig()['argon2'] ?? []);
    }

    public static function pepperConfig(): array
    {
        static $pepperConfig;

        if ($pepperConfig === null) {
            $envPepperConfig = self::envPepperConfig();

            if ($envPepperConfig !== []) {
                $pepperConfig = $envPepperConfig;
            } else {
                require_once self::projectRoot() . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'pepper.php';
                $pepperConfig = \pracear_pepper_entries_from_file(
                    self::projectRoot() . DIRECTORY_SEPARATOR . 'pepper2.php'
                );
            }
        }

        return $pepperConfig;
    }

    public static function currentPepper(): string
    {
        $entries = self::pepperConfig();

        foreach ($entries as $entry) {
            if (($entry['CURRENT'] ?? false) === true && trim((string) ($entry['PASSWORD_PEPPER'] ?? '')) !== '') {
                return (string) $entry['PASSWORD_PEPPER'];
            }
        }

        if (function_exists('pracear_pepper_current_secret')) {
            return \pracear_pepper_current_secret($entries);
        }

        throw new RuntimeException('No se ha configurado un pepper actual válido.');
    }

    public static function matchingPepper(string $plainPassword, string $storedHash): ?string
    {
        $entries = self::pepperConfig();

        foreach ($entries as $entry) {
            $pepper = (string) ($entry['PASSWORD_PEPPER'] ?? '');

            if ($pepper !== '' && password_verify($plainPassword . $pepper, $storedHash)) {
                return $pepper;
            }
        }

        if (function_exists('pracear_matching_pepper')) {
            return \pracear_matching_pepper($plainPassword, $storedHash, $entries);
        }

        return null;
    }

    public static function hashPassword(string $plainPassword, ?string $pepper = null): string
    {
        $effectivePepper = $pepper ?? self::currentPepper();

        return password_hash($plainPassword . $effectivePepper, PASSWORD_ARGON2ID, self::argonOptions());
    }

    public static function passwordNeedsRehash(string $storedHash): bool
    {
        return password_needs_rehash($storedHash, PASSWORD_ARGON2ID, self::argonOptions());
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

        $laravelPublicDirectory = public_path(trim((string) config('pracear.public_assets_directory', 'assets'), '/'));
        if (is_dir($laravelPublicDirectory) || !is_dir(self::projectRoot() . DIRECTORY_SEPARATOR . 'assets')) {
            $directory = $laravelPublicDirectory;

            return $directory;
        }

        $directory = self::projectRoot() . DIRECTORY_SEPARATOR . 'assets';

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
        self::loadVirusTotalHelper();

        $mimeType = strtolower((string) $image->getMimeType());
        if (!in_array($mimeType, ['image/jpeg', 'image/jpg'], true)) {
            throw new InvalidArgumentException('La imagen debe ser un archivo .jpg o .jpeg válido.');
        }

        $scanResult = check_virus_total($image->getRealPath());

        if (!($scanResult['success'] ?? false)) {
            throw new RuntimeException((string) ($scanResult['message'] ?? 'No se pudo analizar la imagen.'));
        }

        if ($scanResult['is_malicious'] ?? false) {
            throw new RuntimeException((string) ($scanResult['message'] ?? 'La imagen se ha marcado como potencialmente maliciosa.'));
        }

        $targetDirectory = dirname(self::imageDiskPath($caseta));
        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true) && !is_dir($targetDirectory)) {
            throw new RuntimeException('No se pudo preparar el directorio de imágenes para el despliegue.');
        }

        $image->move($targetDirectory, basename(self::imageDiskPath($caseta)));
    }
}
