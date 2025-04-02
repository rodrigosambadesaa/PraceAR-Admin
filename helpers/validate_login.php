<?php
function validar_login($login)
{
    // Recortar espacios al inicio y al final
    $login = trim($login);

    // Verificar que el login no está vacío
    if (empty($login)) {
        throw new Exception("El campo de usuario no puede estar vacío.");
    }

    // El login debe ser un string
    if (!is_string($login)) {
        throw new Exception("El nombre de usuario debe ser un texto.");
    }

    // Validar longitud (mínimo 3 caracteres, máximo 50 caracteres)
    if (strlen($login) < 3 || strlen($login) > 50) {
        throw new Exception("El nombre de usuario debe tener entre 3 y 50 caracteres.");
    }

    // Validar que solo contiene caracteres alfanuméricos, guiones, puntos y guiones bajos
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $login)) {
        throw new Exception("El nombre de usuario solo puede contener letras, números, guiones, puntos y guiones bajos.");
    }

    //El nombre de usuario no puede comenzar por un número, un guion o un punto
    if (preg_match('/^[\d.-]/', $login)) {
        throw new Exception("El nombre de usuario no puede comenzar por un número, un guion o un punto.");
    }

    // Si todo está correcto, devolver el login validado
    return $login;
}