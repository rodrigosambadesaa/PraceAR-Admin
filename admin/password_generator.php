<?php

// ini_set('memory_limit', '2048M'); // o más, si es necesario

require_once HELPERS . 'clean_input.php';
require_once CONNECTION;

$app_id_config = require_once HELPERS . 'verify_strong_password.php';
$pepper_config = include 'pepper2.php';

$today = date('Y-m-d');
$pepper = null;
for ($i = 0; $i < count($pepper_config); $i++) {
    if ($pepper_config[$i]['last_used'] < $today) {
        continue;
    }
    $pepper = $pepper_config[$i]['PASSWORD_PEPPER'];
    break;
}

if ($pepper === null) {
    throw new Exception("No se pudo determinar un pepper válido.");
}

if (strlen($pepper) < 16 || strlen($pepper) > 1024) {
    throw new Exception("El pepper debe tener entre 16 y 1024 caracteres.");
}
if (tiene_espacios_al_principio_o_al_final($pepper)) {
    throw new Exception("El pepper no puede tener espacios al principio o al final.");
}
if (tiene_secuencias_alfabeticas_inseguras($pepper)) {
    throw new Exception("El pepper no puede tener secuencias alfabeticas inseguras.");
}
if (tiene_secuencias_numericas_inseguras($pepper)) {
    throw new Exception("El pepper no puede tener secuencias numericas inseguras.");
}
if (tiene_secuencias_caracteres_especiales_inseguras($pepper)) {
    throw new Exception("El pepper no puede tener secuencias de caracteres especiales inseguras.");
}

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/**
 * Generates a password of the specified length that meets the criteria:
 * - Length between 16 and 835 characters.
 * - At least 1 uppercase letter.
 * - At least 1 lowercase letter.
 * - At least 1 digit.
 * - At least 3 distinct special characters.
 *
 * @param int $length Desired password length.
 * @return string The generated password.
 * @throws Exception If the length is out of the allowed range.
 */
function generate_password(int $length)
{
    if ($length < 16 || $length > 835) {
        throw new Exception("La longitud de la contraseña debe estar entre 16 y 835 caracteres.");
    }

    $uppercase_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase_chars = 'abcdefghijklmnopqrstuvwxyz';
    $number_chars = '0123456789';
    $special_chars = '!@#$%^&*()_+-=[]{}|;:,.<>?';

    $password_chars = [];
    $password = '';

    // Ensure at least one uppercase, one lowercase, and one number
    $password_chars[] = $uppercase_chars[random_int(0, strlen($uppercase_chars) - 1)];
    $password_chars[] = $lowercase_chars[random_int(0, strlen($lowercase_chars) - 1)];
    $password_chars[] = $number_chars[random_int(0, strlen($number_chars) - 1)];

    // Ensure three distinct special characters
    $special_array = str_split($special_chars);
    shuffle($special_array);
    $password_chars[] = $special_array[0];
    unset($special_array[array_search($password_chars[3], $special_array)]);
    $special_array = array_values($special_array);
    $password_chars[] = $special_array[0];
    unset($special_array[array_search($password_chars[4], $special_array)]);
    $special_array = array_values($special_array);
    $password_chars[] = $special_array[0];

    $remaining_length = $length - 6;
    $all_chars = "{$uppercase_chars}{$lowercase_chars}{$number_chars}{$special_chars}";

    for ($i = 0; $i < $remaining_length; $i++) {
        $password .= $all_chars[random_int(0, strlen($all_chars) - 1)];
    }
    $password = str_shuffle(implode('', $password_chars) . $password);

    // Validate password
    if (
        tiene_secuencias_numericas_inseguras($password) ||
        tiene_secuencias_alfabeticas_inseguras($password) ||
        tiene_secuencias_caracteres_especiales_inseguras($password) ||
        contrasenha_similar_a_usuario($password, $_SESSION['nombre_usuario']) ||
        ha_sido_filtrada_en_brechas_de_seguridad($password) ||
        !es_contrasenha_fuerte($password) || es_contrasenha_antigua($password, $_SESSION['id'])
    ) {
        return generate_password($length);
    }

    return $password;
}

$result = '';
$passwords = [];
$mostrar_boton = false;
$length = 16;
$quantity = 1; // Fijar la cantidad de contraseñas a 1

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    if (isset($_POST['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        if (isset($_POST['length'])) { // Eliminar referencia a 'quantity'
            $length = limpiar_input($_POST['length']);
            $length_range = limpiar_input($_POST['length_range']);

            if (!is_numeric($length) || !ctype_digit($length)) {
                $result = '<span style="color: red; text-align: center;">La longitud debe ser un número natural válido.</span>';
            } else {
                $length = (int) $length;
                $length_range = (int) $length_range;

                if ($length < 16 || $length > 500 || $length_range < 16 || $length_range > 500) {
                    $result = '<span style="color: red; text-align: center;">La longitud debe estar entre 16 y 500 caracteres.</span>';
                } elseif ($length !== $length_range) {
                    $result = '<span style="color: red; text-align: center;">No se permite modificar el código Javascript que sincroniza los inputs.</span>';
                } else {
                    try {
                        for ($i = 0; $i < $quantity; $i++) { // $quantity siempre será 1
                            $passwords[] = generate_password($length);
                        }

                        $result = '<div id="contrasenas-generadas" style="color: #1e90ff; text-align: center; font-size: 1.2rem;">';
                        foreach ($passwords as $index => $password) {
                            $passwordId = "password-$index";
                            $result .= '<div style="font-size: 1.8rem; color: black;">Contraseña ' . ($index + 1) . ':</div>';
                            $result .= '<div id="' . $passwordId . '" style="font-size: 1.3rem;">' . htmlspecialchars($password) . '</div>';
                            $result .= '<button onclick="copyToClipboard(\'' . $passwordId . '\')">Copiar</button>';
                            $result .= '<div style="margin-top: 0.5rem; color: #000080; font-size: 0.75rem;"> Número de mayúsculas: ' . contar_mayusculas($password) . '</div>';
                            $result .= '<div style="margin-top: 0.5rem; color: #000080; font-size: 0.75rem;"> Número de minúsculas: ' . contar_minusculas($password) . '</div>';
                            $result .= '<div style="margin-top: 0.5rem; color: #000080; font-size: 0.75rem;"> Número de dígitos: ' . contar_digitos($password) . '</div>';
                            $result .= '<div style="margin-top: 0.5rem; color: #000080; font-size: 0.75rem;"> Número de caracteres especiales: ' . contar_caracteres_especiales($password) . '</div>';
                            $result .= '<div style="margin-top: 0.5rem; color: #000080; font-size: 0.75rem;"> Tiempo estimado de resistencia del hash: ' . tiempo_estimado_resistencia_ataque_fuerza_bruta($password) . '</div>';
                            $result .= '<div style="margin-top: 0.5rem; color: #000080; font-size: 0.75rem;"> Entropía: ' . entropia($password) . '</div>';
                            $result .= '<div style="margin-top: 0.5rem;">';
                        }
                        $result .= '</div>';
                        // Ocultar el formulario tras la generación de contraseñas, forzando al usuario a volver a cargar la página para generar nuevas contraseñas
                        ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                document.getElementById('formulario-generacion-contrasena').style.display = 'none';
                                document.getElementById('parrafo-campos-obligatorios').style.display = 'none';
                            }); 
                        </script>
                        <?php
                        $mostrar_boton = true;
                    } catch (Exception $e) {
                        $result = '<span style="color: red; text-align: center;">' . $e->getMessage() . '</span>';
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - PraceAR - Generador de Contraseñas</title>
     <style>
        <?php require_once(CSS_ADMIN . 'header.css'); ?>
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="icon" href="./img/favicon.png" type="image/png">
    <style>
        .required {
            color: red;
        }

        #parrafo-campos-obligatorios {
            color: red;
            text-align: center;
        }

        /* Estilos responsive para el formulario */
        @media (max-width: 600px) {
            form {
                width: 90%;
                margin: 0 auto;
            }

            input[type="number"],
            input[type="range"] {
                width: 100%;
            }
        }   

        button {
            margin-top: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 1rem;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
        }

        button:hover {
            background-color: #45a049;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }

        #success-message {
            color: green;
            display: block;
            text-align: center;
            margin-top: 1rem;
        }

        #resistencia-output {
            color: blue;
            text-align: center;
            margin-top: 0.75rem;
            font-size: 1.2rem;
            font-weight: bold;
            display: block;
            margin-bottom: 1rem;
            transition: all 0.3s ease-in-out;
        }

        #contrasenas-generadas div {
            margin-top: 0.5rem;
        }

        #contrasenas-generadas {
            margin-top: 1rem;
        }
    </style>
</head>

<body>
    <?php require_once COMPONENT_ADMIN . 'sections' . DIRECTORY_SEPARATOR . 'header.php'; ?>

    <h1 style="text-align: center;" tabindex="0">Generador de Contraseñas</h1>

    <div style="text-align: center; margin-bottom: 1rem;">
        <span id="password-text"><?= $result ?></span>
    </div>

    <form method="POST" action="#" style="max-width: 400px; margin: 0 auto;" id="formulario-generacion-contrasena"
        aria-labelledby="formulario-titulo">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
        <label for="length-number">Longitud de la Contraseña: <span class="required" aria-hidden="true">*</span></label>
        <input required type="number" id="length-number" name="length" min="16" max="500"
            value="<?= htmlspecialchars($length, ENT_QUOTES, 'UTF-8') ?>" oninput="syncInputs('number')"
            aria-describedby="length-help">
        <p id="length-help" class="sr-only">Introduce un número entre 16 y 500.</p>

        <label for="length-range">Longitud de la Contraseña: <span class="required" aria-hidden="true">*</span></label>
        <input required type="range" id="length-range" name="length_range" min="16" max="500"
            value="<?= htmlspecialchars($length, ENT_QUOTES, 'UTF-8') ?>" oninput="syncInputs('range')"
            aria-describedby="length-help">

        <output id="length-output" style="display: block; text-align: center; margin-top: -1.5rem;" aria-live="polite">
            <?= htmlspecialchars($length, ENT_QUOTES, 'UTF-8') ?>
        </output>

        <input type="submit" value="Generar Contraseña" aria-label="Generar contraseña">
    </form>

    <div style="text-align: center; color: red; margin-top: .125rem;">
        <span id="parrafo-campos-obligatorios" aria-live="polite">Los campos marcados con <span
                class="required">*</span> son obligatorios.</span>
    </div>

    <script>
        function copyToClipboard(passwordId) {
            const passwordText = document.getElementById(passwordId).innerText;
            navigator.clipboard.writeText(passwordText).then(() => {
                const existingMessage = document.getElementById('success-message');
                if (existingMessage) {
                    existingMessage.remove();
                }
                const successMessage = document.createElement('span');
                successMessage.id = 'success-message';
                successMessage.textContent = 'Contraseña copiada al portapapeles. Se borrará automáticamente en 5 minutos.';
                successMessage.style.color = 'green';
                successMessage.style.display = 'block';
                successMessage.style.textAlign = 'center';
                successMessage.style.marginTop = '1rem';
                document.getElementById(passwordId).insertAdjacentElement('afterend', successMessage);

                // Set a timer to clear the password after 5 minutes
                setTimeout(() => {
                    const passwordElement = document.getElementById(passwordId);
                    if (passwordElement) {
                        passwordElement.innerText = 'Contraseña borrada por seguridad.';
                        const clearMessage = document.createElement('span');
                        clearMessage.textContent = 'La contraseña ha sido borrada automáticamente.';
                        clearMessage.style.color = 'red';
                        clearMessage.style.display = 'block';
                        clearMessage.style.textAlign = 'center';
                        clearMessage.style.marginTop = '1rem';
                        passwordElement.insertAdjacentElement('afterend', clearMessage);
                    }
                }, 5 * 60 * 1000); // 5 minutes in milliseconds
            }).catch(err => {
                console.error('Fallo al copiar la contraseña al portapapeles:', err);
            });
        }

        function syncInputs(inputType) {
            const numberInput = document.getElementById('length-number');
            const rangeInput = document.getElementById('length-range');
            const output = document.getElementById('length-output');

            if (inputType === 'number') {
                rangeInput.value = numberInput.value;
            } else if (inputType === 'range') {
                numberInput.value = rangeInput.value;
            }

            output.textContent = rangeInput.value;
        }

        const formulario = document.getElementById('formulario-generacion-contrasena');
        formulario.addEventListener('submit', function (event) {
            const longitud = document.getElementById('length-number').value;
            const longitudRange = document.getElementById('length-range').value;

            const longitudParsed = parseInt(longitud);
            const longitudRangeParsed = parseInt(longitudRange);

            if (isNaN(longitudParsed) || isNaN(longitudRangeParsed) || longitudParsed < 16 || longitudParsed > 500 || longitudRangeParsed < 16 || longitudRangeParsed > 500 || longitudParsed !== longitudRangeParsed) {
                event.preventDefault();
                alert('La longitud de la contraseña debe estar entre 16 y 500 caracteres.');
                return;
            }
        });

        function calcularResistencia(longitud) {
            let resistencia = '';
            if (longitud >= 16 && longitud <= 20) {
                resistencia = 'Resistente a atacantes individuales.';
            } else if (longitud > 20 && longitud <= 30) {
                resistencia = 'Resistente a grupos pequeños de atacantes.';
            } else if (longitud > 30 && longitud <= 50) {
                resistencia = 'Resistente a empresas con recursos moderados.';
            } else if (longitud > 50 && longitud <= 70) {
                resistencia = 'Resistente a grandes empresas.';
            } else if (longitud > 70) {
                resistencia = 'Resistente a gobiernos y organizaciones con recursos avanzados.';
            } else {
                resistencia = 'Longitud insuficiente para garantizar resistencia.';
            }
            return resistencia;
        }

        function actualizarResistencia() {
            const longitud = parseInt(document.getElementById('length-number').value);
            // Creamos el elemento de salida para la resistencia y lo añadimos debajo del output de longitud con texto de color adecuado
            let resistenciaOutput = document.getElementById('resistencia-output') || document.createElement('div');
            resistenciaOutput.id = 'resistencia-output';
            resistenciaOutput.style.color = 'blue';
            resistenciaOutput.style.textAlign = 'center';
            resistenciaOutput.style.marginTop = '0.75rem';
            resistenciaOutput.style.fontSize = '1.2rem';
            resistenciaOutput.style.fontWeight = 'bold';
            resistenciaOutput.style.display = 'block';
            resistenciaOutput.style.marginBottom = '1rem';
            resistenciaOutput.style.transition = 'all 0.3s ease-in-out';

            if (!resistenciaOutput.parentNode) {
                document.getElementById('length-output').insertAdjacentElement('afterend', resistenciaOutput);
            }

            if (!isNaN(longitud)) {
                resistenciaOutput.textContent = calcularResistencia(longitud);
            } else {
                resistenciaOutput.textContent = 'Introduce una longitud válida.';
            }
        }

        document.getElementById('length-number').addEventListener('input', actualizarResistencia);
        document.getElementById('length-range').addEventListener('input', actualizarResistencia);

        // Mostrar la resistencia al cargar la página con el valor por defecto
        document.addEventListener('DOMContentLoaded', () => {
            actualizarResistencia();

            // Asegurarse de que la resistencia se muestre tras el envío del formulario
            const formulario = document.getElementById('formulario-generacion-contrasena');
            formulario.addEventListener('submit', function (event) {
                setTimeout(() => {
                    actualizarResistencia();

                    // Mostrar la resistencia debajo del texto de la contraseña generada
                    const contrasenasGeneradas = document.getElementById('contrasenas-generadas');
                    if (contrasenasGeneradas) {
                        const longitud = parseInt(document.getElementById('length-number').value);
                        let resistenciaOutput = document.getElementById('resistencia-output') || document.createElement('div');
                        resistenciaOutput.id = 'resistencia-output';
                        resistenciaOutput.style.color = 'blue';
                        resistenciaOutput.style.textAlign = 'center';
                        resistenciaOutput.style.marginTop = '0.75rem';
                        resistenciaOutput.style.fontSize = '1.2rem';
                        resistenciaOutput.style.fontWeight = 'bold';
                        resistenciaOutput.style.display = 'block';
                        resistenciaOutput.style.marginBottom = '1rem';
                        resistenciaOutput.style.transition = 'all 0.3s ease-in-out';

                        resistenciaOutput.textContent = calcularResistencia(longitud);
                        contrasenasGeneradas.insertAdjacentElement('afterend', resistenciaOutput);
                    }
                }, 0);
            });
        });
    </script>
</body>

</html>