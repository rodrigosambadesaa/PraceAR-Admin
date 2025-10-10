<?php
declare(strict_types=1);

require_once __DIR__ . '/verify_strong_password.php';

/**
 * Genera una contraseña segura cumpliendo los requisitos establecidos.
 *
 * @param int $length      Longitud deseada de la contraseña.
 * @param string|null $username Nombre de usuario para validar contraseñas antiguas o similares.
 *
 * @throws InvalidArgumentException Si la longitud no está permitida.
 * @throws Exception                Si no se puede generar una contraseña válida tras múltiples intentos.
 */
function generate_secure_password(int $length, ?string $username = null): string
{
    if ($length < 16 || $length > 835) {
        throw new InvalidArgumentException('La longitud de la contraseña debe estar entre 16 y 835 caracteres.');
    }

    $uppercaseChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercaseChars = 'abcdefghijklmnopqrstuvwxyz';
    $numberChars = '0123456789';
    $specialChars = '!@#$%^&*()_+-=[]{}|;:,.<>?';

    $allChars = $uppercaseChars . $lowercaseChars . $numberChars . $specialChars;
    $maxAttempts = 50;
    $attempt = 0;
    $normalizedUsername = $username === null ? '' : (string)$username;

    while ($attempt < $maxAttempts) {
        $attempt++;

        $passwordPieces = [];

        $passwordPieces[] = $uppercaseChars[random_int(0, strlen($uppercaseChars) - 1)];
        $passwordPieces[] = $lowercaseChars[random_int(0, strlen($lowercaseChars) - 1)];
        $passwordPieces[] = $numberChars[random_int(0, strlen($numberChars) - 1)];

        $specialArray = str_split($specialChars);
        shuffle($specialArray);

        $passwordPieces[] = $specialArray[0];
        unset($specialArray[array_search($passwordPieces[3], $specialArray, true)]);
        $specialArray = array_values($specialArray);
        $passwordPieces[] = $specialArray[0];
        unset($specialArray[array_search($passwordPieces[4], $specialArray, true)]);
        $specialArray = array_values($specialArray);
        $passwordPieces[] = $specialArray[0];

        $remainingLength = $length - count($passwordPieces);
        $passwordBody = '';

        for ($i = 0; $i < $remainingLength; $i++) {
            $passwordBody .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        $password = str_shuffle(implode('', $passwordPieces) . $passwordBody);

        if (tiene_secuencias_numericas_inseguras($password)
            || tiene_secuencias_alfabeticas_inseguras($password)
            || tiene_secuencias_caracteres_especiales_inseguras($password)
            || contrasenha_similar_a_usuario($password, $normalizedUsername)
            || !es_contrasenha_fuerte($password)
        ) {
            continue;
        }

        if ($normalizedUsername !== '' && es_contrasenha_antigua($password, $normalizedUsername)) {
            continue;
        }

        if (ha_sido_filtrada_en_brechas_de_seguridad($password)) {
            continue;
        }

        return $password;
    }

    throw new Exception('No se pudo generar una contraseña segura. Inténtelo de nuevo.');
}

