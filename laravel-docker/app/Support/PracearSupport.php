<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

final class PracearSupport
{
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
        $availableLanguages = array_keys((array) config('pracear.languages', []));

        if (is_string($language) && in_array($language, $availableLanguages, true)) {
            return $language;
        }

        return 'gl';
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
            require_once self::projectRoot() . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'pepper.php';
            $pepperConfig = \pracear_pepper_entries_from_file(
                self::projectRoot() . DIRECTORY_SEPARATOR . 'pepper2.php'
            );
        }

        return $pepperConfig;
    }

    public static function currentPepper(): string
    {
        $entries = self::pepperConfig();

        return \pracear_pepper_current_secret($entries);
    }

    public static function matchingPepper(string $plainPassword, string $storedHash): ?string
    {
        $entries = self::pepperConfig();

        return \pracear_matching_pepper($plainPassword, $storedHash, $entries);
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
        return self::projectRoot() . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $caseta . '.jpg';
    }

    public static function imagePublicPath(string $caseta): string
    {
        return 'assets/' . $caseta . '.jpg';
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

        $image->move(dirname(self::imageDiskPath($caseta)), basename(self::imageDiskPath($caseta)));
    }
}
