<?php

function es_contrasenha_fuerte($contrasenha)
{
    // Al menos 16 caracteres, al menos una letra mayúscula, al menos una letra minúscula, al menos un número y al menos tres caracteres especiales distintos, y un máximo de 255 caracteres
    $patron = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{16,255}$/';

    return preg_match($patron, $contrasenha);
}

function ha_sido_filtrada_en_brechas_de_seguridad($contrasenha)
{
    $hash = sha1($contrasenha);
    // echo "SHA-1 Hash: " . $hash . PHP_EOL;

    $hash_prefix = substr($hash, 0, 5);
    $hash_suffix = substr($hash, 5);

    $url = "https://api.pwnedpasswords.com/range/" . $hash_prefix;
    // echo "Fetching URL: " . $url . PHP_EOL;

    $response = file_get_contents($url);

    if ($response === false) {
        // echo "Error: Unable to fetch API response." . PHP_EOL;
        return false;
    }

    // echo "API Response: " . $response . PHP_EOL;

    $hashes = explode("\n", $response);

    // echo "Hash Suffix: " . strtoupper($hash_suffix) . PHP_EOL;
    foreach ($hashes as $hash) {
        $hash_parts = explode(":", $hash);
        // echo "Comparing: " . $hash_parts[0] . " with " . strtoupper($hash_suffix) . PHP_EOL;
        if (strtoupper($hash_parts[0]) === strtoupper($hash_suffix)) {
            return true;
        }
    }

    return false;
}

function contrasenha_similar_a_usuario($contrasenha, $usuario)
{
    // Aseguramos que todos los valores sean minúsculas para comparaciones insensibles a mayúsculas
    $contrasenha = strtolower($contrasenha);

    // Si el nombre de usuario es un solo valor, lo convertimos en un array para mayor flexibilidad
    $usuarios = is_array($usuario) ? $usuario : [$usuario];

    // Recorrer todos los nombres de usuario
    foreach ($usuarios as $nombre_usuario) {
        $nombre_usuario = strtolower($nombre_usuario);

        // Verificar si la contraseña contiene el nombre de usuario completo
        if (strpos($contrasenha, $nombre_usuario) !== false) {
            return true;
        }

        // Verificar si la contraseña contiene una parte significativa del nombre de usuario
        $longitud = strlen($nombre_usuario);
        for ($i = 0; $i <= $longitud - 3; $i++) {
            // Extraer substrings de al menos 3 caracteres
            $subcadena = substr($nombre_usuario, $i, 3);
            if (strpos($contrasenha, $subcadena) !== false) {
                return true;
            }
        }
    }

    return false;
}

function tiene_espacios_al_principio_o_al_final($contrasenha)
{
    return trim($contrasenha) !== $contrasenha;
}

