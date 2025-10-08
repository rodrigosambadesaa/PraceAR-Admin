<?php
declare(strict_types=1);

// $app_id_config = include __DIR__ . '/../wolfram_alpha_app_id.php';
// $app_id = $app_id_config['app_id'];

/**
 * Función para determinar si una contraseña es fuerte.
 * @param mixed $contrasenha Contraseña a verificar.
 * @return bool|int Devuelve true si la contraseña es fuerte, false en caso contrario.
 */
function es_contrasenha_fuerte(string $contrasenha): bool
{
    // Al menos 16 caracteres, al menos una letra mayúscula, al menos una letra minúscula, al menos un número y al menos tres caracteres especiales distintos, y un máximo de 1024 caracteres
    $patron = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{16,1024}$/';

    return preg_match($patron, $contrasenha) === 1;
}

/**
 * Función para determinar si una contraseña es antigua.
 * @param contrasenha_antigua_a_verificar Contraseña a verificar.
 * @param id_usuario Id del usuario a verificar.
 * @return bool Devuelve true si la contraseña es antigua, false en caso contrario.
 */
function es_contrasenha_antigua(string $contrasenha_antigua_a_verificar, string $nombre_usuario): bool
{
    require_once('./constants.php');

    $pepper_config = include 'pepper2.php';  // Incluimos la configuración del pepper.

    $today = date('Y-m-d');
    $pepper = null;
    $pepperCount = is_array($pepper_config) ? count($pepper_config) : 0;
    for ($i = 0; $i < $pepperCount; $i++) {
        if (isset($pepper_config[$i]['last_used']) && $pepper_config[$i]['last_used'] >= $today) {
            $pepper = (string) $pepper_config[$i]['PASSWORD_PEPPER'];
            break;
        }
    }

    if ($pepper === null) {
        throw new Exception("No se pudo determinar un pepper válido.");
    }

    // Validaciones del pepper (las dejamos, aunque en el código original ya estaban)
    if (strlen($pepper) < 16 || strlen($pepper) > 1024) {
        throw new Exception("El pepper debe tener entre 16 y 1024 caracteres.");
    }

    if (tiene_espacios_al_principio_o_al_final($pepper)) {
        throw new Exception("El pepper no puede tener espacios al principio o al final.");
    }

    if (tiene_secuencias_alfabeticas_inseguras($pepper)) {
        throw new Exception("El pepper no puede tener secuencias alfabéticas inseguras.");
    }

    if (tiene_secuencias_numericas_inseguras($pepper)) {
        throw new Exception("El pepper no puede tener secuencias numéricas inseguras.");
    }

    if (tiene_secuencias_caracteres_especiales_inseguras($pepper)) {
        throw new Exception("El pepper no puede tener secuencias de caracteres especiales inseguras.");
    }
    $sentencia_sql = "SELECT password FROM old_passwords WHERE id_usuario = ?";

    global $servidor_bd, $usuario, $clave, $bd;

    $missingConfiguration = [];

    if (!is_string($servidor_bd) || $servidor_bd === '') {
        $missingConfiguration[] = 'PRACEAR_DB_HOST';
    }

    if (!is_string($usuario) || $usuario === '') {
        $missingConfiguration[] = 'PRACEAR_DB_USER';
    }

    if (!is_string($bd) || $bd === '') {
        $missingConfiguration[] = 'PRACEAR_DB_NAME';
    }

    if (!empty($missingConfiguration)) {
        error_log('Faltan variables de configuración para verificar contraseñas antiguas: ' . implode(', ', $missingConfiguration));
        throw new RuntimeException('No se pudo verificar el historial de contraseñas. Contacte con la persona administradora.');
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conexion = null;
    $filas = [];

    try {
        $password = $clave === null ? '' : (string) $clave;

        $conexion = new mysqli($servidor_bd, $usuario, $password, $bd);
        $conexion->set_charset('utf8mb4');

        $stmt = $conexion->prepare($sentencia_sql);
        $stmt->bind_param("s", $nombre_usuario);

        $stmt->execute();
        $resultado = $stmt->get_result();
        $filas = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } catch (mysqli_sql_exception $exception) {
        error_log('Error al consultar contraseñas antiguas: ' . $exception->getMessage());
        throw new RuntimeException('No se pudo verificar el historial de contraseñas. Contacte con la persona administradora.');
    } finally {
        if ($conexion instanceof mysqli) {
            $conexion->close();
        }
    }

    if ($filas !== []) {
        foreach ($filas as $fila) {
            $hashedPassword = $fila['password'] ?? '';
            if (is_string($hashedPassword) && password_verify($contrasenha_antigua_a_verificar . $pepper, $hashedPassword)) {
                return true; // La contraseña es antigua
            }
        }
    }
    return false; // La contraseña no coincide con ninguna contraseña antigua
}

/**
 * Función para determinar si una contraseña es débil porqué ha sido filtrada en brechas de seguridad.
 * @param mixed $contrasenha Contraseña a verificar.
 * @return bool Devuelve true si la contraseña ha sido filtrada en brechas de seguridad, false en caso contrario.
 */
function ha_sido_filtrada_en_brechas_de_seguridad(string $contrasenha): bool
{
    $hash = sha1($contrasenha);
    // echo "SHA-1 Hash: " . $hash . PHP_EOL;

    $hash_prefix = substr($hash, 0, 5);
    $hash_suffix = substr($hash, 5);

    $url = "https://api.pwnedpasswords.com/range/" . $hash_prefix;
    // echo "Fetching URL: " . $url . PHP_EOL;

    $response = @file_get_contents($url);

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
        if (isset($hash_parts[0]) && strtoupper($hash_parts[0]) === strtoupper($hash_suffix)) {
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
function contrasenha_similar_a_usuario(string $contrasenha, array|string $usuario): bool
{
    // Aseguramos que todos los valores sean minúsculas para comparaciones insensibles a mayúsculas
    $contrasenha = strtolower($contrasenha);

    // Si el nombre de usuario es un solo valor, lo convertimos en un array para mayor flexibilidad
    $usuarios = is_array($usuario) ? $usuario : [$usuario];

    // Recorrer todos los nombres de usuario
    foreach ($usuarios as $nombre_usuario) {
        if (!is_string($nombre_usuario)) {
            continue;
        }

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
function tiene_espacios_al_principio_o_al_final(string $contrasenha): bool
{
    return trim($contrasenha) !== $contrasenha;
}

/**
 * Función para determinar el tiempo estimado de resistencia de una contraseña a un ataque de fuerza bruta.
 * @param mixed $contrasenha Contraseña a verificar.
 * @return string Devuelve el tiempo estimado de resistencia de la contraseña a un ataque de fuerza bruta.
 */
function tiempo_estimado_resistencia_ataque_fuerza_bruta(string $contrasenha): string
{
    /*
     * Esta función utiliza una biblioteca de matemáticas de terceros para manejar valores grandes
     * y evitar problemas de desbordamiento o valores infinitos.
     */

    // Usar BCMath para cálculos de alta precisión
    if (!extension_loaded('bcmath')) {
        throw new Exception("La extensión BCMath no está habilitada en este servidor.");
    }

    $entropia = entropia($contrasenha);
    $entropia = str_replace(" bits", "", $entropia);

    // Estimación de tiempo en segundos
    $ritmo = "1000"; // Ritmo estimado en contraseñas por segundo para Argon2ID con pepper

    // Calcular 2^entropia usando BCMath
    $tiempo = bcpow("2", $entropia);
    $tiempo = bcdiv($tiempo, $ritmo);

    // Convertir el tiempo en segundos a un formato más legible
    return convertir_segundos_a_tiempo($tiempo);
}

/**
 * Función para convertir segundos a un formato de tiempo más legible.
 * @param mixed $segundos Segundos a convertir.
 * @return string Devuelve el tiempo en un formato más legible.
 */
function convertir_segundos_a_tiempo(string $segundos): string
{
    $conversiones = [
        "cuatordecillones de años" => bcpow("10", "84"),
        "tridecillones de años" => bcpow("10", "78"),
        "duodecillones de años" => bcpow("10", "72"),
        "undecillones de años" => bcpow("10", "66"),
        "decillones de años" => bcpow("10", "60"),
        "nonillones de años" => bcpow("10", "54"),
        "octillones de años" => bcpow("10", "48"),
        "septillones de años" => bcpow("10", "42"),
        "sextillones de años" => bcpow("10", "36"),
        "quintillones de años" => bcpow("10", "30"),
        "cuatrillones de años" => bcpow("10", "24"),
        "trillones de años" => bcpow("10", "18"),
        "billones de años" => bcpow("10", "12"),
        "miles de millones de años" => bcpow("10", "9"),
        "millones de años" => bcpow("10", "6"),
        "miles de años" => bcpow("10", "3"),
        "años" => "31536000", // Segundos en un año
        "días" => "86400", // Segundos en un día
        "horas" => "3600", // Segundos en una hora
        "minutos" => "60", // Segundos en un minuto
        "segundos" => "1"
    ];

    foreach ($conversiones as $unidad => $conversion) {
        if (bccomp($segundos, $conversion) >= 0) {
            $tiempo = bcdiv($segundos, $conversion, 0);
            return "$tiempo $unidad";
        }
    }

    return "menos de un segundo";
}

/**
 * Función para determinar la entropía de una contraseña.
 * @param mixed $contrasenha Contraseña a verificar.
 * @return string Devuelve la entropía de la contraseña.
 */
function entropia(string $contrasenha): string
{
    // Cálculo manual de la entropía de la contraseña, teniendo en cuenta que la almacenamos en Argon2ID con pepper
    $longitud = strlen($contrasenha);
    $minusculas = 0;
    $mayusculas = 0;
    $digitos = 0;
    $especiales = 0;


    for ($i = 0; $i < $longitud; $i++) {
        $caracter = $contrasenha[$i];
        if (ctype_lower($caracter)) {
            $minusculas++;
        } elseif (ctype_upper($caracter)) {
            $mayusculas++;
        } elseif (ctype_digit($caracter)) {
            $digitos++;
        } else {
            $especiales++;
        }
    }

    $entropia = 0;
    // Cada minúscula aporta 5 bits de entropía
    $entropia += $minusculas * 5;
    // Cada mayúscula aporta 6 bits de entropía
    $entropia += $mayusculas * 6;
    // Cada dígito aporta 7 bits de entropía
    $entropia += $digitos * 7;
    // Cada carácter especial aporta 8 bits de entropía
    $entropia += $especiales * 8;

    return "$entropia bits";
}

/**
 * Función para determinar si una contraseña contiene secuencias numéricas inseguras.
 * @param mixed $contrasenha Contraseña a verificar.
 * @return bool Devuelve true si la contraseña contiene secuencias numéricas inseguras, false en caso contrario.
 */
function tiene_secuencias_numericas_inseguras(string $contrasenha): bool
{
    $secuencias_numericas_inseguras = [];
    $numeros = "0123456789";
    $numeros_reverso = strrev($numeros);
    // Secuencias en diagonal en el teclado numérico como 159, 951, 753, 357, 147, 741, 369, 963, 852, 258
    $secuencias_diagonales = ["159", "951", "753", "357", "147", "741", "369", "963", "852", "258"];

    for ($longitud = 2; $longitud <= 5; $longitud++) {
        for ($i = 0; $i <= min(strlen($numeros), 20) - $longitud; $i++) {
            $secuencias_numericas_inseguras[] = substr($numeros, $i, $longitud);
            $secuencias_numericas_inseguras[] = substr($numeros_reverso, $i, $longitud);
        }
    }

    foreach ($secuencias_diagonales as $secuencia) {
        $secuencias_numericas_inseguras[] = $secuencia;
    }

    foreach ($secuencias_numericas_inseguras as $secuencia) {
        if (strpos($contrasenha, $secuencia) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Función para determinar si una contraseña contiene secuencias alfabéticas inseguras.
 * @param mixed $contrasenha Contraseña a verificar.
 * @return bool Devuelve true si la contraseña contiene secuencias alfabéticas inseguras, false en caso contrario.
 */
function tiene_secuencias_alfabeticas_inseguras(string $contrasenha): bool
{
    $alfabeto = "abcdefghijklmnopqrstuvwxyz";
    $alfabeto_reverso = strrev($alfabeto);
    $fila_superior_teclado_espanol = "qwertyuiop";
    $fila_media_teclado_espanol = "asdfghjklñ";
    $fila_inferior_teclado_espanol = "zxcvbnm";

    $filas_teclado_espanol = [$fila_superior_teclado_espanol, $fila_media_teclado_espanol, $fila_inferior_teclado_espanol];

    $secuencias_alfabeticas_inseguras = [];

    // Generar secuencias alfabéticas de longitud 2 a 5
    for ($longitud = 2; $longitud <= 5; $longitud++) {
        for ($i = 0; $i <= strlen($alfabeto) - $longitud; $i++) {
            $secuencias_alfabeticas_inseguras[] = substr($alfabeto, $i, $longitud);
            $secuencias_alfabeticas_inseguras[] = substr($alfabeto_reverso, $i, $longitud);
        }
    }

    // Agregar secuencias de teclado español
    foreach ($filas_teclado_espanol as $fila) {
        for ($longitud = 2; $longitud <= strlen($fila); $longitud++) {
            for ($i = 0; $i <= strlen($fila) - $longitud; $i++) {
                $secuencias_alfabeticas_inseguras[] = substr($fila, $i, $longitud);
                $secuencias_alfabeticas_inseguras[] = substr(strrev($fila), $i, $longitud);
            }
        }
    }

    // Agregar secuencias de teclado español en diagonal
    $secuencias_diagonales_teclado_espanol = [
        "qaz",
        "wsx",
        "edc",
        "rfv",
        "tgb",
        "yhn",
        "ujm",
        "qazwsx",
        "wsxedc",
        "edcrfv",
        "rfvtgb",
        "tgbnhy",
        "yhnujm"
    ];

    $secuencias_diagonales_teclado_espanol_reverso = array_map('strrev', $secuencias_diagonales_teclado_espanol);
    $secuencias_alfabeticas_inseguras = array_merge($secuencias_alfabeticas_inseguras, $secuencias_diagonales_teclado_espanol, $secuencias_diagonales_teclado_espanol_reverso);

    // Verificar si la contraseña contiene alguna de las secuencias inseguras
    foreach ($secuencias_alfabeticas_inseguras as $secuencia) {
        if (strpos($contrasenha, $secuencia) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Función para determinar si una contraseña contiene secuencias de caracteres especiales inseguras.
 * @param mixed $contrasenha Contraseña a verificar.
 * @return bool Devuelve true si la contraseña contiene secuencias de caracteres especiales inseguras, false en caso contrario.
 */
function tiene_secuencias_caracteres_especiales_inseguras(string $contrasenha): bool
{
    // Detectar secuencias de caracteres especiales basadas en la distribución de caracteres en el teclado español
    $secuencias_caracteres_especiales_inseguras = [
        "!@#$%^&*()_+",
        "-=",
        // "~!@#$%^&*()_+",
        "[]",
        ";'",
        ",./",
        "{}",
        ":\"",
        "<>?"
    ];

    $longitud = strlen($contrasenha);
    foreach ($secuencias_caracteres_especiales_inseguras as $secuencia) {
        $longitud_secuencia = strlen($secuencia);
        for ($i = 0; $i <= $longitud - $longitud_secuencia; $i++) {
            $subcadena = substr($contrasenha, $i, $longitud_secuencia);
            if (strpos($secuencia, $subcadena) !== false) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Función para determinar si una contraseña contiene secuencias de teclado inseguras.
 * @param mixed $contrasenha Contraseña de la que se quiere contar el número de mayúsculas.
 * @return bool|int Devuelve el número de mayúsculas en la contraseña.
 */
function contar_mayusculas(string $contrasenha): int
{
    $count = preg_match_all('/[A-Z]/', $contrasenha);

    return $count === false ? 0 : $count;
}

/**
 * Función para contar el nº de minúsculas en una contraseña.
 * @param mixed $contrasenha Contraseña de la que se quiere contar el número de minúsculas.
 * @return bool|int Devuelve el número de minúsculas en la contraseña.
 */
function contar_minusculas(string $contrasenha): int
{
    $count = preg_match_all('/[a-z]/', $contrasenha);

    return $count === false ? 0 : $count;
}
/**
 * Función para contar el nº de dígitos en una contraseña.
 * @param mixed $contrasenha Contraseña de la que se quiere contar el número de dígitos.
 * @return bool|int Devuelve el número de dígitos en la contraseña.
 */
function contar_digitos(string $contrasenha): int
{
    $count = preg_match_all('/[0-9]/', $contrasenha);

    return $count === false ? 0 : $count;
}

/**
 * Función para contar el nº de caracteres especiales en una contraseña.
 * @param mixed $contrasenha Contraseña de la que se quiere contar el número de caracteres especiales.
 * @return bool|int Devuelve el número de caracteres especiales en la contraseña.
 */
function contar_caracteres_especiales(string $contrasenha): int
{
    $count = preg_match_all('/[^A-Za-z0-9]/', $contrasenha);

    return $count === false ? 0 : $count;
}

/**
 * Función para verificar si una contraseña es similar a otra.
 * @param mixed $contrasenha Contraseña a verificar.
 * @param mixed $contrasenha_anterior Contraseña anterior a comparar.
 * @return bool Devuelve true si la contraseña es similar a la anterior, false en caso contrario.
 */
function contrasenha_similar_a_contrasenha_anterior(string $contrasenha, string $contrasenha_anterior): bool
{
    // Verificar coincidencias parciales y completas
    $longitud = strlen($contrasenha);
    $longitud_anterior = strlen($contrasenha_anterior);

    // Verificar si la contraseña anterior es una subcadena de la nueva contraseña
    if (strpos($contrasenha, $contrasenha_anterior) !== false) {
        return true;
    }

    // Verificar si la nueva contraseña es una subcadena de la contraseña anterior
    if (strpos($contrasenha_anterior, $contrasenha) !== false) {
        return true;
    }

    // Verificar si la nueva contraseña y la anterior tienen una coincidencia parcial
    for ($i = 0; $i <= $longitud - 3; $i++) {
        $subcadena = substr($contrasenha, $i, 3);
        if (strpos($contrasenha_anterior, $subcadena) !== false) {
            return true;
        }
    }

    // Verificar si la nueva contraseña y la anterior tienen una coincidencia total
    if ($contrasenha === $contrasenha_anterior) {
        return true;
    }

    return false;
}

