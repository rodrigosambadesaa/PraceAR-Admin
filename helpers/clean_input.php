<?php
declare(strict_types=1);

function limpiar_input(string $input): string
{
    $sanitized = trim($input);
    $sanitized = stripslashes($sanitized);
    $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
    $sanitized = strip_tags($sanitized);

    return mb_convert_encoding($sanitized, 'UTF-8', 'UTF-8');
}
