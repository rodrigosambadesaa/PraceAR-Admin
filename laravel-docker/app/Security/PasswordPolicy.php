<?php

declare(strict_types=1);

namespace App\Security;

use InvalidArgumentException;
use RuntimeException;

final class PasswordPolicy
{
    public const MIN_LENGTH = 16;
    public const MAX_LENGTH = 1024;

    public static function validate(string $password, string $username = ''): void
    {
        $length = strlen($password);

        if ($length < self::MIN_LENGTH || $length > self::MAX_LENGTH) {
            throw new InvalidArgumentException('La contraseña debe tener entre 16 y 1024 caracteres.');
        }

        if (trim($password) !== $password) {
            throw new InvalidArgumentException('La contraseña no puede tener espacios al principio o al final.');
        }

        if (
            preg_match('/[A-Z]/', $password) !== 1
            || preg_match('/[a-z]/', $password) !== 1
            || preg_match('/[0-9]/', $password) !== 1
            || preg_match_all('/[^A-Za-z0-9\s]/', $password) < 3
        ) {
            throw new InvalidArgumentException('La contraseña debe incluir mayúsculas, minúsculas, números y al menos tres símbolos.');
        }

        $normalizedUsername = strtolower(preg_replace('/[^a-z0-9]/i', '', $username) ?? '');
        $normalizedPassword = strtolower(preg_replace('/[^a-z0-9]/i', '', $password) ?? '');

        if (strlen($normalizedUsername) >= 4 && str_contains($normalizedPassword, $normalizedUsername)) {
            throw new InvalidArgumentException('La contraseña no puede contener el nombre de usuario.');
        }

        foreach (['012345', '123456', '234567', '345678', '456789', '987654', '876543', '765432', '654321', 'abcdef', 'qwerty', 'password'] as $sequence) {
            if (str_contains(strtolower($password), $sequence)) {
                throw new InvalidArgumentException('La contraseña contiene una secuencia demasiado predecible.');
            }
        }
    }

    public static function generate(int $length, string $username = ''): string
    {
        $length = max(self::MIN_LENGTH, min(self::MAX_LENGTH, $length));
        $sets = [
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'abcdefghijklmnopqrstuvwxyz',
            '0123456789',
            '!@#$%^&*()-_=+[]{}:,.?',
        ];

        for ($attempt = 0; $attempt < 32; $attempt++) {
            $characters = [
                self::randomCharacter($sets[0]),
                self::randomCharacter($sets[1]),
                self::randomCharacter($sets[2]),
                self::randomCharacter($sets[3]),
                self::randomCharacter($sets[3]),
                self::randomCharacter($sets[3]),
            ];
            $all = implode('', $sets);

            while (count($characters) < $length) {
                $characters[] = self::randomCharacter($all);
            }

            for ($index = count($characters) - 1; $index > 0; $index--) {
                $swap = random_int(0, $index);
                [$characters[$index], $characters[$swap]] = [$characters[$swap], $characters[$index]];
            }

            $candidate = implode('', $characters);

            try {
                self::validate($candidate, $username);

                return $candidate;
            } catch (InvalidArgumentException) {
                // Generate another uniformly random candidate.
            }
        }

        throw new RuntimeException('No se pudo generar una contraseña segura.');
    }

    /**
     * @return array{uppercase:int, lowercase:int, digits:int, special:int, entropy:string, hashResistanceTime:string}
     */
    public static function statistics(string $password): array
    {
        $alphabet = 0;
        $alphabet += preg_match('/[a-z]/', $password) === 1 ? 26 : 0;
        $alphabet += preg_match('/[A-Z]/', $password) === 1 ? 26 : 0;
        $alphabet += preg_match('/[0-9]/', $password) === 1 ? 10 : 0;
        $alphabet += preg_match('/[^A-Za-z0-9\s]/', $password) === 1 ? 32 : 0;
        $entropy = $alphabet > 0 ? (int) floor(strlen($password) * log($alphabet, 2)) : 0;

        return [
            'uppercase' => preg_match_all('/[A-Z]/', $password),
            'lowercase' => preg_match_all('/[a-z]/', $password),
            'digits' => preg_match_all('/[0-9]/', $password),
            'special' => preg_match_all('/[^A-Za-z0-9\s]/', $password),
            'entropy' => $entropy . ' bits',
            'hashResistanceTime' => $entropy >= 100 ? 'Muy alta' : ($entropy >= 80 ? 'Alta' : 'Mejorable'),
        ];
    }

    private static function randomCharacter(string $characters): string
    {
        return $characters[random_int(0, strlen($characters) - 1)];
    }
}
