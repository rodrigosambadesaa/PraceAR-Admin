<?php

function delete_image($caseta)
{
    $ruta_a_imagen = ASSETS . $caseta . ".jpg";
    if (file_exists($ruta_a_imagen)) {
        unlink($ruta_a_imagen);
        return true;
    } else {
        return false;
    }
}