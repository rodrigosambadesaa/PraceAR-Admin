<?php
declare(strict_types=1);

function validar_login(string $login): string
{
    // Recortar espacios al inicio y al final
    $trimmedLogin = trim($login);

    // Verificar que el login no está vacío
    if ($trimmedLogin === '') {
        throw new Exception('El campo de usuario no puede estar vacío.');
    }

    // Validar longitud (mínimo 3 caracteres, máximo 50 caracteres)
    if (strlen($trimmedLogin) < 3 || strlen($trimmedLogin) > 50) {
        throw new Exception('El nombre de usuario debe tener entre 3 y 50 caracteres.');
    }

    // Validar que solo contiene caracteres alfanuméricos, guiones, puntos y guiones bajos
    if (preg_match('/^[a-zA-Z0-9._-]+$/', $trimmedLogin) !== 1) {
        throw new Exception('El nombre de usuario solo puede contener letras, números, guiones, puntos y guiones bajos.');
    }

    // El nombre de usuario no puede comenzar por un número, un guion o un punto
    if (preg_match('/^[\d.-]/', $trimmedLogin) === 1) {
        throw new Exception('El nombre de usuario no puede comenzar por un número, un guion o un punto.');
    }

    // Si todo está correcto, devolver el login validado
    return $trimmedLogin;
}