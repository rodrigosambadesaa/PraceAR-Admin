<?php
declare(strict_types=1);

/**
 * @return array<int, array<string, string>>
 */
function get_languages(mysqli $conexion): array
{
    $sql = 'SELECT DISTINCT codigo_idioma, nombre_idioma FROM puestos_traducciones';

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    $resultado = $stmt->get_result();

    return $resultado->fetch_all(MYSQLI_ASSOC);
}