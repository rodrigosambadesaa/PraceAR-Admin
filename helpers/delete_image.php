<?php
declare(strict_types=1);

function delete_image(string $caseta): bool
{
    $imagenPath = ASSETS . $caseta . '.jpg';
    if (file_exists($imagenPath)) {
        return unlink($imagenPath);
    }

    return false;
}