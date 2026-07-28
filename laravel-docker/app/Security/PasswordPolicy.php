<?php

declare(strict_types=1);

namespace App\Security;

use InvalidArgumentException;
use RuntimeException;

final class PasswordPolicy
{
    public const MIN_LENGTH = 16;
    public const MAX_LENGTH = 1024;

    private const GENERATION_SETS = [
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'abcdefghijklmnopqrstuvwxyz',
        '0123456789',
        '!@#$%^&*()-_=+[]{}:,.?',
    ];

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

        if (strlen($normalizedUsername) >= 6) {
            for ($offset = 0; $offset <= strlen($normalizedUsername) - 4; $offset++) {
                if (str_contains($normalizedPassword, substr($normalizedUsername, $offset, 4))) {
                    throw new InvalidArgumentException('La contraseña no puede contener fragmentos del nombre de usuario.');
                }
            }
        }

        if (self::containsPredictableSequence(strtolower($password))) {
            throw new InvalidArgumentException('La contraseña contiene una secuencia demasiado predecible.');
        }
    }

    public static function generate(int $length, string $username = ''): string
    {
        if ($length < self::MIN_LENGTH || $length > self::MAX_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                'La longitud debe estar entre %d y %d caracteres.',
                self::MIN_LENGTH,
                self::MAX_LENGTH,
            ));
        }

        $sets = self::GENERATION_SETS;

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
     * @return array{uppercase:int, lowercase:int, digits:int, special:int, entropy:string, hashResistanceTime:string, alphabetSize:int}
     */
    public static function statistics(string $password): array
    {
        $alphabet = strlen(implode('', self::GENERATION_SETS));
        $entropy = $alphabet > 0 ? (int) floor(strlen($password) * log($alphabet, 2)) : 0;

        return [
            'uppercase' => preg_match_all('/[A-Z]/', $password),
            'lowercase' => preg_match_all('/[a-z]/', $password),
            'digits' => preg_match_all('/[0-9]/', $password),
            'special' => preg_match_all('/[^A-Za-z0-9\s]/', $password),
            'entropy' => $entropy . ' bits',
            'hashResistanceTime' => $entropy >= 100 ? 'Muy alta' : ($entropy >= 80 ? 'Alta' : 'Mejorable'),
            'alphabetSize' => $alphabet,
        ];
    }

    private static function randomCharacter(string $characters): string
    {
        return $characters[random_int(0, strlen($characters) - 1)];
    }

    private static function containsPredictableSequence(string $password): bool
    {
        $sequenceSources = [
            '0123456789',
            '9876543210',
            'abcdefghijklmnopqrstuvwxyz',
            'zyxwvutsrqponmlkjihgfedcba',
            'qwertyuiop',
            'poiuytrewq',
            'asdfghjklñ',
            'ñlkjhgfdsa',
            'zxcvbnm',
            'mnbvcxz',
        ];

        foreach ($sequenceSources as $source) {
            for ($length = 3; $length <= min(6, strlen($source)); $length++) {
                for ($offset = 0; $offset <= strlen($source) - $length; $offset++) {
                    if (str_contains($password, substr($source, $offset, $length))) {
                        return true;
                    }
                }
            }
        }

        foreach ([
            '147', '741', '258', '852', '369', '963', '159', '951', '357', '753',
            'qaz', 'zaq', 'wsx', 'xsw', 'edc', 'cde', 'rfv', 'vfr', 'tgb', 'bgt',
            'yhn', 'nhy', 'ujm', 'mju', '!@#', '@#$', '#$%', '$%^', '%^&', '^&*',
            '&*(', '*()', '()_', '-=', '[]', '{}', ';:', ',.', './', '<>', 'password',
        ] as $sequence) {
            if (str_contains($password, $sequence)) {
                return true;
            }
        }

        return false;
    }
}
