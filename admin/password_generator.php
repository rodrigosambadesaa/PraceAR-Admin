<?php

require_once HELPERS . 'clean_input.php';
require_once CONNECTION;

$app_id_config = require_once HELPERS . 'verify_strong_password.php';

$pepper_config = include 'pepper.php';
$pepper = $pepper_config['PASSWORD_PEPPER'] ?? '';

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
        throw new Exception("Password length must be a natural number between 16 and 1024.");
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
                $result = '<span style="color: red; text-align: center;">Password length must be a natural number between 16 and 1024.</span>';
            } else {
                $length = (int) $length;
                $length_range = (int) $length_range;

                if ($length < 16 || $length > 1024 || $length_range < 16 || $length_range > 1024) {
                    $result = '<span style="color: red; text-align: center;">Password length must be a natural number between 16 and 1024.</span>';
                } elseif ($length !== $length_range) {
                    throw new Exception("Do not alter the JavaScript code to set different password lengths.");
                } else {
                    try {
                        $password = generate_password($length);

                        // Verify the current password entered by the user
                        $contrasenha_actual = $_POST['contrasenha_actual'];

                        $smtp_select = "SELECT password FROM usuarios WHERE login = ?";
                        $stmt_select = $conexion->prepare($smtp_select);
                        $stmt_select->bind_param('s', $_SESSION['nombre_usuario']);
                        $stmt_select->execute();
                        $resultado_select = $stmt_select->get_result()->fetch_assoc();

                        if (!$resultado_select) {
                            throw new Exception("User not found in the database.");
                        }

                        if (!password_verify($contrasenha_actual . $pepper, $resultado_select['password'])) {
                            throw new Exception("The current password entered is incorrect.");
                        }

                        while (
                            contrasenha_similar_a_usuario($password, $_SESSION['nombre_usuario']) ||
                            contrasenha_similar_a_contrasenha_anterior($password, $_SESSION['nombre_usuario'])
                        ) {
                            $password = generate_password($length);
                        }

                        // Display the generated password
                        $result = '<div id="contrasena-generada" style="color: #1e90ff; text-align: center; font-size: 1.2rem;">' . htmlspecialchars($password) . '</div>';
                        $result .= '<div style="color: green; text-align: center;">Uppercase letters: ' . contar_mayusculas($password) . '</div>';
                        $result .= '<div style="color: green; text-align: center;">Lowercase letters: ' . contar_minusculas($password) . '</div>';
                        $result .= '<div style="color: green; text-align: center;">Digits: ' . contar_digitos($password) . '</div>';
                        $result .= '<div style="color: green; text-align: center;">Special characters: ' . contar_caracteres_especiales($password) . '</div>';
                        if ($length <= 177) {
                            $result .= '<div style="color: green; text-align: center;">Estimated brute-force resistance: ' . tiempo_estimado_resistencia_ataque_fuerza_bruta($password) . '</div>';
                        }
                        $result .= '<div style="color: green; text-align: center;">Password entropy: ' . entropia($password) . '</div>';
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
    <title>Admin - Password Generator</title>
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

    <h1 style="text-align: center;">Password Generator</h1>

    <div style="text-align: center; margin-bottom: 1rem;">
        <span id="password-text"><?= $result ?></span>
    </div>

    <?php if ($mostrar_boton): ?>
        <div style="text-align: center;">
            <button onclick="copyToClipboard()">Copy Password</button>
        </div>
    <?php endif; ?>

    <form method="POST" action="#" style="max-width: 400px; margin: 0 auto;" id="formulario-generacion-contrasena">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
        <label for="length-number">Password Length: <span class="required">*</span></label>
        <input required type="number" id="length-number" name="length" min="16" max="1024"
            value="<?= htmlspecialchars($length, ENT_QUOTES, 'UTF-8') ?>" oninput="syncInputs('number')">

        <label for="length-range">Password Length: <span class="required">*</span></label>
        <input required type="range" id="length-range" name="length_range" min="16" max="1024"
            value="<?= htmlspecialchars($length, ENT_QUOTES, 'UTF-8') ?>" oninput="syncInputs('range')">

        <output id="length-output" style="display: block; text-align: center; margin-top: -1.5rem;">
            <?= htmlspecialchars($length, ENT_QUOTES, 'UTF-8') ?>
        </output>

        <label for="contrasenha-actual">Current Password: <span class="required">*</span></label>
        <input required type="password" id="contrasenha-actual" name="contrasenha_actual">

        <input type="submit" value="Generate Password">
    </form>

    <div style="text-align: center; color: red; margin-top: 1rem;">
        <p id="parrafo-campos-obligatorios">Fields marked with <span class="required">*</span> are required.</p>
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
                successMessage.textContent = 'Password copied to clipboard';
                successMessage.style.color = 'green';
                successMessage.style.display = 'block';
                successMessage.style.textAlign = 'center';
                successMessage.style.marginTop = '1rem';
                document.getElementById('contrasena-generada').insertAdjacentElement('afterend', successMessage);
            }).catch(err => {
                console.error('Failed to copy password to clipboard: ', err);
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
                alert('Password length must be a natural number between 16 and 1024.');
                return;
            }
        });
    </script>
</body>

</html>