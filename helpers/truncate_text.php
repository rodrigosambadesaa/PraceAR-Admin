<?php
function truncateText(string $texto, int $limite = 50)
{
    // Si el texto es mayor que el límite, lo cortamos
    if (strlen($texto) > $limite) {
        // Utilizamos substr para recortar el texto al tamaño definido por el límite
        return substr($texto, 0, $limite) . '...';
    } else {
        // Si el texto no supera el límite, lo devolvemos tal cual
        return $texto;
    }
}

