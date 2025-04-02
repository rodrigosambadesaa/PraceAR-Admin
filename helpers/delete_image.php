<?php

function delete_image($caseta)
{
    $imagenPath = ASSETS . $caseta . ".jpg";
    if (file_exists($imagenPath)) {
        unlink($imagenPath);
        return true;
    } else {
        return false;
    }
}