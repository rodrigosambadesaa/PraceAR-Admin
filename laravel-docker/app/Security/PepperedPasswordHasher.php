<?php

declare(strict_types=1);

namespace App\Security;

use RuntimeException;

final class PepperedPasswordHasher
{
    /**
     * @return list<string>
     */
    public static function peppers(): array
    {
        $current = trim((string) config('pepper.current', ''));
        $legacyRaw = config('pepper.legacy', '[]');
        $legacy = is_string($legacyRaw) ? json_decode($legacyRaw, true) : [];
        $peppers = $current !== '' ? [$current] : [];

        if (is_array($legacy)) {
            foreach ($legacy as $pepper) {
                $pepper = trim((string) $pepper);
                if ($pepper !== '' && !in_array($pepper, $peppers, true)) {
                    $peppers[] = $pepper;
                }
            }
        }

        return $peppers;
    }

    public static function currentPepper(): string
    {
        $pepper = trim((string) config('pepper.current', ''));

        if (strlen($pepper) < 32) {
            throw new RuntimeException('PASSWORD_PEPPER_CURRENT debe contener al menos 32 caracteres aleatorios.');
        }

        return $pepper;
    }

    public static function hash(string $password): string
    {
        $hash = password_hash(
            hash_hmac('sha384', $password, self::currentPepper(), true),
            PASSWORD_ARGON2ID,
            (array) config('security.argon2', []),
        );

        if (!is_string($hash)) {
            throw new RuntimeException('No se pudo proteger la contraseña.');
        }

        return $hash;
    }

    public static function matchingPepper(string $password, string $storedHash): ?string
    {
        foreach (self::peppers() as $pepper) {
            $preHash = hash_hmac('sha384', $password, $pepper, true);

            if (password_verify($preHash, $storedHash)) {
                return $pepper;
            }

            // Compatibility with the previous concatenation implementation.
            if (password_verify($password . $pepper, $storedHash)) {
                return $pepper;
            }
        }

        return null;
    }

    public static function needsRehash(string $storedHash, string $matchedPepper): bool
    {
        return !hash_equals(self::currentPepper(), $matchedPepper)
            || password_needs_rehash($storedHash, PASSWORD_ARGON2ID, (array) config('security.argon2', []));
    }
}
