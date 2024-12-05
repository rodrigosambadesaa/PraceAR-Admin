<?php

function esContrasenhaFuerte($contrasenha)
{
    // Al menos 12 caracteres, al menos una letra mayúscula, al menos una letra minúscula, al menos un número y al menos un caracter especial, y un máximo de 255 caracteres

    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).{12,255}$/', $contrasenha);
}