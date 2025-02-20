<?php

$app_id_config = include __DIR__ . '/../wolfram_alpha_app_id.php';
$app_id = $app_id_config['app_id'];

/**
 * Función para determinar si una contraseña es fuerte.
 * @param mixed $contrasenha Contraseña a verificar.
 * @return bool|int Devuelve true si la contraseña es fuerte, false en caso contrario.
 */
function es_contrasenha_fuerte($contrasenha)
{
    // Al menos 16 caracteres, al menos una letra mayúscula, al menos una letra minúscula, al menos un número y al menos tres caracteres especiales distintos, y un máximo de 1024 caracteres
    $patron = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{16,1024}$/';

    return preg_match($patron, $contrasenha);
}

/**
 * Función para determinar si una contraseña es débil porqué ha sido filtrada en brechas de seguridad.
 * @param mixed $contrasenha Contraseña a verificar.
 * @return bool Devuelve true si la contraseña ha sido filtrada en brechas de seguridad, false en caso contrario.
 */
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

/**
 * Función para determinar si una contraseña es débil porqué es similar al nombre de usuario.
 * @param mixed $contrasenha Contraseña a verificar.
 * @param mixed $usuario Nombre de usuario a verificar.
 * @return bool Devuelve true si la contraseña es similar al nombre de usuario, false en caso contrario.
 */
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

/**
 * Función para determinar si una contraseña es inválida porque contiene espacios al principio o al final.
 * @param mixed $contrasenha Contraseña a verificar.
 * @return bool Devuelve true si la contraseña contiene espacios al principio o al final, false en caso contrario.
 */
function tiene_espacios_al_principio_o_al_final($contrasenha)
{
    return trim($contrasenha) !== $contrasenha;
}

/**
 * Función para determinar el tiempo estimado de crackeo de una contraseña, asumiendo que a un atacante solo le es viable realizar ataques a hashes de contraseñas porque el equipo de Bastionado de la organización es muy competente y ha implementado todo de forma segura
 * @param mixed $contrasenha Contraseña a verificar.
 * @return string Devuelve el tiempo estimado de crackeo de la contraseña.
 */
function tiempo_estimado_crackeo($contrasenha)
{
    // Recoger el resultado de esta entrada de Wolfram|Alpha: "password cracking time #WD[{]Ga9K0folfnK!Hm*{t-WLrp#R*_Up3;*{h}RD49s10u}ME]Bi*SE>+F0O" donde la contraseña es un ejemplo de contraseña fuerte, y la URL correspondiente a la entrada es https://www.wolframalpha.com/input?i=password+cracking+time+%23WD%5B%7B%5DGa9K0folfnK%21Hm*%7Bt-WLrp%23R*_Up3%3B*%7Bh%7DRD49s10u%7DME%5DBi*SE%3E%2BF0O&lang=es
// URL de la API de Wolfram|Alpha
    $url = "https://api.wolframalpha.com/v2/query?input=password+cracking+time+" . urlencode($contrasenha) . "&format=plaintext&output=JSON&appid=YOUR_APP_ID&lang=es";

    // Sustituir YOUR_APP_ID por tu ID de aplicación de Wolfram|Alpha
    $url = str_replace("YOUR_APP_ID", $GLOBALS['app_id'], $url);

    // echo "Fetching URL: " . $url . PHP_EOL;

    $response = file_get_contents($url);

    if ($response === false) {
        // echo "Error: Unable to fetch API response." . PHP_EOL;
        return "Desconocido";
    }

    // echo "API Response: " . $response . PHP_EOL;

    $json = json_decode($response, true);

    if ($json === null) {
        // echo "Error: Unable to parse JSON response." . PHP_EOL;
        return "Desconocido";
    }

    $tiempo = $json['queryresult']['pods'][1]['subpods'][0]['plaintext'];

    return $tiempo;
}

/**
 * Función para determinar la entropía de una contraseña.
 * @param mixed $contrasenha Contraseña a verificar.
 * @return string Devuelve la entropía de la contraseña.
 */
function entropia($contrasenha)
{
    // Recoger el resultado de esta URL de Wolfram|Alpha: https://www.wolframalpha.com/input?i=password+l.0ZB*%5D%3D7fVl%7C%7D2r Está en el tercer elemento del quinto pod
    // URL de la API de Wolfram|Alpha
    $url = "https://api.wolframalpha.com/v2/query?input=password+" . urlencode($contrasenha) . "&format=plaintext&output=JSON&appid=YOUR_APP_ID";

    // Sustituir YOUR_APP_ID por tu ID de aplicación de Wolfram|Alpha
    $url = str_replace("YOUR_APP_ID", $GLOBALS['app_id'], $url);

    // echo "Fetching URL: " . $url . PHP_EOL;

    $response = file_get_contents($url);

    if ($response === false) {
        // echo "Error: Unable to fetch API response." . PHP_EOL;
        return "Desconocido";
    }

    // echo "API Response: " . $response . PHP_EOL;

    $json = json_decode($response, true);

    if ($json === null) {
        // echo "Error: Unable to parse JSON response." . PHP_EOL;
        return "Desconocido";
    }

    $entropia = isset($json['queryresult']['pods'][4]['subpods'][2]['plaintext']) ? $json['queryresult']['pods'][4]['subpods'][2]['plaintext'] : "Desconocido";

    return $entropia;

}
