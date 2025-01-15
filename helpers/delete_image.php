<?php

function deleteImage($caseta)
{
    $imagenPath = ASSETS . $caseta . ".jpg";
    if (file_exists($imagenPath)) {
        unlink($imagenPath);
        return true;
    } else {
        return false;
    }
}