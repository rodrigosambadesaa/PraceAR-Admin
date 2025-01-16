<?php
function limpiar_input($input)
{
  // Eliminar espacios innecesarios al inicio y final del input
  $input = trim($input);

  // Quitar barras invertidas (previene escape no deseado)
  $input = stripslashes($input);

  // Convertir caracteres especiales en entidades HTML (previene XSS)
  $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

  // Eliminar etiquetas HTML (adicional para prevenir XSS)
  $input = strip_tags($input);

  // Codificar caracteres UTF-8 para prevenir caracteres no deseados
  $input = mb_convert_encoding($input, 'UTF-8', 'UTF-8');

  return $input;
}