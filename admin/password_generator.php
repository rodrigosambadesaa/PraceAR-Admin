<?php

require_once HELPERS . 'clean_input.php';
require_once CONNECTION;

$app_id_config = require_once HELPERS . 'verify_strong_password.php';

$pepper_config = include 'pepper2.php';

$today = date('Y-m-d');
for ($i = 0; $i < count($pepper_config); $i++) {
    // If the peper has expired, check the next one
    if ($pepper_config[$i]['last_used'] < $today) {
        continue;
    }

    $pepper = $pepper_config[$i]['PASSWORD_PEPPER'];
    break;
}

// El pepper debe tener entre 16 y 512 caracteres
if (strlen($pepper) < 16 || strlen($pepper) > 512) {
    throw new Exception("El pepper debe tener entre 16 y 512 caracteres.");
}

// El pepper no puede tener espacios al principio o al final
if (tiene_espacios_al_principio_o_al_final($pepper)) {
    throw new Exception("El pepper no puede tener espacios al principio o al final.");
}

// El pepper no puede tener secuencias alfabéticas inseguras
if (tiene_secuencias_alfabeticas_inseguras($pepper)) {
    throw new Exception("El pepper no puede tener secuencias alfabéticas inseguras.");
}

// El pepper no puede tener secuencias numéricas inseguras
if (tiene_secuencias_numericas_inseguras($pepper)) {
    throw new Exception("El pepper no puede tener secuencias numéricas inseguras.");
}

// El pepper no puede tener secuencias de caracteres especiales inseguras
if (tiene_secuencias_caracteres_especiales_inseguras($pepper)) {
    throw new Exception("El pepper no puede tener secuencias de caracteres especiales inseguras.");
}

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/**
 * Generates a password of the specified length that meets the following criteria:
 * - Length between 16 and 128 characters.
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
    if ($length < 16 || $length > 1024) {
        throw new Exception("La longitud de la contraseña debe estar entre 16 y 1024 caracteres.");
    }

    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $special_chars = '!@#$%^&*()_+-=[]{}|;:,.<>?';

    $password_chars = [];

    // Ensure at least one uppercase, one lowercase, and one number
    $password_chars[] = $uppercase[random_int(0, strlen($uppercase) - 1)];
    $password_chars[] = $lowercase[random_int(0, strlen($lowercase) - 1)];
    $password_chars[] = $numbers[random_int(0, strlen($numbers) - 1)];

    // Ensure three distinct special characters
    $special_array = str_split($special_chars);
    shuffle($special_array);
    $password_chars[] = $special_array[0];
    $password_chars[] = $special_array[1];
    $password_chars[] = $special_array[2];

    // Fill the rest of the password with random characters
    $all_chars = "{$uppercase}{$lowercase}{$numbers}{$special_chars}";
    for ($i = 0; $i < $length - 6; $i++) {
        $password_chars[] = $all_chars[random_int(0, strlen($all_chars) - 1)];
    }

    // Shuffle the characters
    shuffle($password_chars);
    $password = implode('', $password_chars);

    // Validate the generated password
    $veces_necesarias_generar_nueva_contrasenha = 0;
    while (
        tiene_secuencias_numericas_inseguras($password) ||
        tiene_secuencias_alfabeticas_inseguras($password) ||
        tiene_secuencias_caracteres_especiales_inseguras($password) ||
        contrasenha_similar_a_usuario($password, $_SESSION['nombre_usuario']) ||
        ha_sido_filtrada_en_brechas_de_seguridad($password)
    ) {
        // echo "Contraseña generada no válida. Generando una nueva...<br>";
        shuffle($password_chars);
        $password = implode('', $password_chars);
        $veces_necesarias_generar_nueva_contrasenha++;
    }

    echo "Se han necesitado $veces_necesarias_generar_nueva_contrasenha intentos adicionales para generar una contraseña válida.<br>";

    return $password;
}

$result = '';
$passwords = [];
$mostrar_boton = false;
$length = 16;
$quantity = 1;

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    if (isset($_POST['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        if (isset($_POST['length']) && isset($_POST['quantity'])) {
            $length = limpiar_input($_POST['length']);
            $quantity = limpiar_input($_POST['quantity']);

            if (!is_numeric($length) || !is_numeric($quantity) || !ctype_digit($length) || !ctype_digit($quantity)) {
                $result = '<span style="color: red; text-align: center;">La longitud y la cantidad deben ser números naturales válidos.</span>';
            } else {
                $length = (int) $length;
                $quantity = (int) $quantity;

                if ($length < 16 || $length > 1024 || $quantity < 1 || $quantity > 10) {
                    $result = '<span style="color: red; text-align: center;">La longitud debe estar entre 16 y 1024 caracteres y la cantidad entre 1 y 10.</span>';
                } else {
                    try {
                        for ($i = 0; $i < $quantity; $i++) {
                            $passwords[] = generate_password($length);
                        }

                        $result = '<div id="contrasenas-generadas" style="color: #1e90ff; text-align: center; font-size: 1.2rem;">';
                        foreach ($passwords as $index => $password) {
                            $passwordId = "password-$index";
                            $result .= '<div id="' . $passwordId . '">' . htmlspecialchars($password) . '</div>';
                            $result .= '<button onclick="copyToClipboard(\'' . $passwordId . '\')">Copiar</button>';
                        }
                        $result .= '</div>';
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
    </style>
</head>

<body>
    <header>
        <?php require_once COMPONENT_ADMIN . 'sections' . DIRECTORY_SEPARATOR . 'header.php'; ?>
    </header>

    <h1 style="text-align: center;">Generador de Contraseñas</h1>

    <div style="text-align: center; margin-bottom: 1rem;">
        <span id="password-text"><?= $result ?></span>
    </div>

    <!-- <?php if ($mostrar_boton): ?>
        <div style="text-align: center;">
            <button onclick="copyToClipboard()">Copiar Contraseña</button>
        </div>
    <?php endif; ?> -->

    <form method="POST" action="#" style="max-width: 400px; margin: 0 auto;" id="formulario-generacion-contrasena">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
        <label for="length-number">Longitud de la Contraseña: <span class="required">*</span></label>
        <input required type="number" id="length-number" name="length" min="16" max="1024"
            value="<?= htmlspecialchars($length, ENT_QUOTES, 'UTF-8') ?>" oninput="syncInputs('number')">

        <label for="length-range">Longitud de la Contraseña: <span class="required">*</span></label>
        <input required type="range" id="length-range" name="length_range" min="16" max="1024"
            value="<?= htmlspecialchars($length, ENT_QUOTES, 'UTF-8') ?>" oninput="syncInputs('range')">

        <output id="length-output" style="display: block; text-align: center; margin-top: -1.5rem;">
            <?= htmlspecialchars($length, ENT_QUOTES, 'UTF-8') ?>
        </output>

        <label for="quantity-number">Cantidad de Contraseñas: <span class="required">*</span></label>
        <input required type="number" id="quantity-number" name="quantity" min="1" max="10"
            value="<?= htmlspecialchars($quantity, ENT_QUOTES, 'UTF-8') ?>">

        <input type="submit" value="Generar Contraseñas">
    </form>

    <div style="text-align: center; color: red; margin-top: 1rem;">
        <p id="parrafo-campos-obligatorios">Los campos marcados con <span class="required">*</span> son obligatorios.
        </p>
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
                successMessage.textContent = 'Contraseña copiada al portapapeles.';
                successMessage.style.color = 'green';
                successMessage.style.display = 'block';
                successMessage.style.textAlign = 'center';
                successMessage.style.marginTop = '1rem';
                document.getElementById(passwordId).insertAdjacentElement('afterend', successMessage);
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

            if (isNaN(longitudParsed) || isNaN(longitudRangeParsed) || longitudParsed < 16 || longitudParsed > 1024 || longitudRangeParsed < 16 || longitudRangeParsed > 1024 || longitudParsed !== longitudRangeParsed) {
                event.preventDefault();
                alert('La longitud de la contraseña debe estar entre 16 y 1024 caracteres.');
                return;
            }
        });
    </script>
</body>

</html>