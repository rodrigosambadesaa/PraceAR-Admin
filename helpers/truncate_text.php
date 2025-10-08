<?php
declare(strict_types=1);

function truncate_text(string $texto, int $limite = 50): string
{
    // Si el texto es mayor que el límite, lo cortamos
    if (strlen($texto) > $limite) {
        // Utilizamos substr para recortar el texto al tamaño definido por el límite
        return substr($texto, 0, $limite) . '...';
    }

    // Si el texto no supera el límite, lo devolvemos tal cual
    return $texto;
}

