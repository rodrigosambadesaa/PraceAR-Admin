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

// El pepper debe tener entre 16 y 1024 caracteres
if (strlen($pepper) < 16 || strlen($pepper) > 1024) {
    throw new Exception("El pepper debe tener entre 16 y 1024 caracteres.");
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

// // El pepper no puede tener secuencias de caracteres especiales inseguras
// if (tiene_secuencias_caracteres_especiales_inseguras($pepper)) {
//     throw new Exception("El pepper no puede tener secuencias de caracteres especiales inseguras.");
// }

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/**
 * Generates a password of the specified length that meets the following criteria:
 * - Length between 16 and 1024 characters.
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
    }

    return $password;
}

$result = '';
$password = '';
$mostrar_boton = false;
$length = 16;

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    if (isset($_POST['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        if (isset($_POST['length'])) {
            $length = limpiar_input($_POST['length']);
            $length_range = limpiar_input($_POST['length_range']);

            if (!is_numeric($length) || !is_numeric($length_range) || !ctype_digit($length) || !ctype_digit($length_range)) {
                $result = '<span style="color: red; text-align: center;">La longitud de la contraseña debe ser un número natural entre 16 y 1024.</span>';
            } else {
                $length = (int) $length;
                $length_range = (int) $length_range;

                if ($length < 16 || $length > 1024 || $length_range < 16 || $length_range > 1024) {
                    $result = '<span style="color: red; text-align: center;">La longitud de la contraseña debe estar entre 16 y 1024 caracteres.</span>';
                } elseif ($length !== $length_range) {
                    throw new Exception("No alteres el código JavaScript para cambiar la longitud de la contraseña.");
                } else {
                    try {
                        $password = generate_password($length);

                        // Verify the current password entered by the user
                        $sentencia_select_contrasenha_actual = $conexion->prepare("SELECT password FROM usuarios WHERE login = ?");
                        $sentencia_select_contrasenhas_anteriores = $conexion->prepare("SELECT password FROM old_passwords WHERE id_usuario = ?");
                        $sentencia_select_contrasenha_actual->bind_param('s', $_SESSION['nombre_usuario']);
                        $sentencia_select_contrasenhas_anteriores->bind_param('i', $_SESSION['id_usuario']);

                        $sentencia_select_contrasenha_actual->execute();
                        $resultado_contrasenha_actual = $sentencia_select_contrasenha_actual->get_result();
                        $contrasenha_actual = $resultado_contrasenha_actual->fetch_assoc()['password'];

                        $sentencia_select_contrasenhas_anteriores->execute();
                        $resultado_contrasenhas_anteriores = $sentencia_select_contrasenhas_anteriores->get_result();
                        $contrasenhas_anteriores = $resultado_contrasenhas_anteriores->fetch_all(MYSQLI_ASSOC);

                        for ($i = 0; $i < count($pepper_config); $i++) {
                            $pepper = $pepper_config[$i]['PASSWORD_PEPPER'];
                            $password_hash = password_hash($password . $pepper, PASSWORD_DEFAULT);

                            if (password_verify($password . $pepper, $contrasenha_actual)) {
                                // Regenerar la contraseña si es igual a la actual hasta que sea diferente y hasta que sea diferente a cualquier contraseña anterior
                                throw new Exception("La contraseña generada es igual a la contraseña actual. Por favor, vuelva a enviar el formulario.");
                            }

                            foreach ($contrasenhas_anteriores as $contrasenha_anterior) {
                                if (password_verify($password . $pepper, $contrasenha_anterior['password'])) {
                                    throw new Exception("La contraseña generada es igual a una contraseña anterior. Por favor, vuelva a enviar el formulario.");
                                }
                            }
                        }

                        while (
                            contrasenha_similar_a_usuario($password, $_SESSION['nombre_usuario']) ||
                            contrasenha_similar_a_contrasenha_anterior($password, $_SESSION['nombre_usuario'])
                        ) {
                            shuffle($password_chars);
                            $password = implode('', $password_chars);
                        }

                        // Display the generated password
                        $result = '<div id="contrasena-generada" style="color: #1e90ff; text-align: center; font-size: 1.2rem;">' . htmlspecialchars($password) . '</div>';
                        $result .= '<div style="color: green; text-align: center;">Letras mayúsculas: ' . contar_mayusculas($password) . '</div>';
                        $result .= '<div style="color: green; text-align: center;">Letras minúsculas: ' . contar_minusculas($password) . '</div>';
                        $result .= '<div style="color: green; text-align: center;">Dígitos: ' . contar_digitos($password) . '</div>';
                        $result .= '<div style="color: green; text-align: center;">Caracteres especiales: ' . contar_caracteres_especiales($password) . '</div>';
                        $result .= '<div style="color: green; text-align: center;">Tiempo estimado de resistencia a un ataque de fuerza bruta: ' . tiempo_estimado_resistencia_ataque_fuerza_bruta($password) . '</div>';
                        $result .= '<div style="color: green; text-align: center;">Entropía: ' . entropia($password) . '</div>';
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

    <?php if ($mostrar_boton): ?>
        <div style="text-align: center;">
            <button onclick="copyToClipboard()">Copiar Contraseña</button>
        </div>
    <?php endif; ?>

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

        <input type="submit" value="Generar Contraseña">
    </form>

    <div style="text-align: center; color: red; margin-top: 1rem;">
        <p id="parrafo-campos-obligatorios">Los campos marcados con <span class="required">*</span> son obligatorios.
        </p>
    </div>

    <script>
        function copyToClipboard() {
            const passwordText = document.getElementById('contrasena-generada').innerText;
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
                document.getElementById('contrasena-generada').insertAdjacentElement('afterend', successMessage);
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