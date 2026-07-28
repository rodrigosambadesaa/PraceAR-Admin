<?php

declare(strict_types=1);

namespace App\Security;

use Illuminate\Support\Facades\Http;
use RuntimeException;

final class PwnedPasswordChecker
{
    public function isCompromised(string $password): bool
    {
        if (!(bool) config('security.password_breach_check.enabled', true)) {
            return false;
        }

        $hash = strtoupper(sha1($password));
        $prefix = substr($hash, 0, 5);
        $suffix = substr($hash, 5);
        $endpoint = rtrim((string) config(
            'security.password_breach_check.endpoint',
            'https://api.pwnedpasswords.com/range',
        ), '/');

        try {
            $response = Http::accept('text/plain')
                ->withHeaders([
                    'Add-Padding' => 'true',
                    'User-Agent' => 'PraceAR-Admin password-security-check',
                ])
                ->connectTimeout((int) config('security.password_breach_check.connect_timeout_seconds', 3))
                ->timeout((int) config('security.password_breach_check.timeout_seconds', 5))
                ->retry(2, 150, throw: false)
                ->get($endpoint . '/' . $prefix);
        } catch (\Throwable $throwable) {
            throw new RuntimeException('No se pudo comprobar de forma segura si la contraseña aparece en filtraciones.', 0, $throwable);
        }

        if (!$response->successful()) {
            throw new RuntimeException('No se pudo comprobar de forma segura si la contraseña aparece en filtraciones.');
        }

        foreach (preg_split('/\r\n|\r|\n/', $response->body()) ?: [] as $line) {
            [$candidate] = array_pad(explode(':', trim($line), 2), 2, '');

            if (strlen($candidate) === 35 && hash_equals($suffix, strtoupper($candidate))) {
                return true;
            }
        }

        return false;
    }
}
