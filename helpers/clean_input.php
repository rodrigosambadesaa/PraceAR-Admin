<?php
declare(strict_types=1);

/**
 * Normaliza un valor proveniente de formularios o peticiones HTTP para que pueda ser procesado de forma segura.
 *
 * @param null|string|int|float $input Valor recibido directamente desde la entrada del usuario.
 */
function limpiar_input(null|string|int|float $input): string
{
    if ($input === null) {
        return '';
    }

    if (!is_string($input)) {
        $input = (string) $input;
    }

    $sanitized = trim($input);
    $sanitized = stripslashes($sanitized);
    $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
    $sanitized = strip_tags($sanitized);

    return mb_convert_encoding($sanitized, 'UTF-8', 'UTF-8');
}
