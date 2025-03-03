<?php

$app_id_config = require_once HELPERS . 'verify_strong_password.php';

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
/**
 * Genera una contraseña de la longitud indicada que cumple con:
 * - Longitud entre 16 y 1024 caracteres.
 * - Al menos 1 letra mayúscula.
 * - Al menos 1 letra minúscula.
 * - Al menos 1 dígito.
 * - Al menos 3 caracteres especiales (distintos) válidos.
 *
 * @param int $length Longitud deseada de la contraseña.
 * @return string La contraseña generada.
 * @throws Exception Si la longitud está fuera del rango permitido.
 */
function generate_password(int $length)
{
    if ($length < 16 || $length > 1024) {
        throw new Exception("La longitud de la contraseña debe ser un número natural entre 16 y 1024.");
    }

    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $special_chars = '!@#$%^&*()_+-=[]{}|;:,.<>?';

    $password_chars = [];

    // Asegurar al menos una mayúscula, una minúscula y un número:
    $password_chars[] = $uppercase[random_int(0, strlen($uppercase) - 1)];
    $password_chars[] = $lowercase[random_int(0, strlen($lowercase) - 1)];
    $password_chars[] = $numbers[random_int(0, strlen($numbers) - 1)];

    // Para garantizar tres caracteres especiales distintos:
    $special_array = str_split($special_chars);
    shuffle($special_array);
    $password_chars[] = $special_array[0];
    $password_chars[] = $special_array[1];
    $password_chars[] = $special_array[2];

    // Completar el resto de la contraseña con caracteres aleatorios.
    $all_chars = "{$uppercase}{$lowercase}{$numbers}{$special_chars}";
    for ($i = 0; $i < $length - 6; $i++) {
        $password_chars[] = $all_chars[random_int(0, strlen($all_chars) - 1)];
    }

    // Mezclar los caracteres
    shuffle($password_chars);
    $password = implode('', $password_chars);

    // Validar la contraseña generada
    while (tiene_secuencias_numericas_inseguras($password) || tiene_secuencias_alfabeticas_inseguras($password) || tiene_secuencias_caracteres_especiales_inseguras($password) || contrasenha_similar_a_usuario($password, $_SESSION['nombre_usuario']) || ha_sido_filtrada_en_brechas_de_seguridad($password)) {
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
            require_once 'helpers/clean_input.php';
            // Descartar cualquier entrada que no sea un número natural

            $length = limpiar_input($_POST['length']);
            $length_range = limpiar_input($_POST['length_range']);

            if (!is_numeric($length) || !is_numeric($length_range) || !ctype_digit($length) || !ctype_digit($length_range)) {
                $result = '<span style="color: red; text-align: center;">La longitud de la contraseña debe ser un número natural entre 16 y 1024.</span>';
            } else {
                $length = (int) $length;
                $length_range = (int) $length_range;

                if ($length < 16 || $length > 1024 || $length_range < 16 || $length_range > 1024) {
                    $result = '<span style="color: red; text-align: center;">La longitud de la contraseña debe ser un número natural entre 16 y 1024.</span>';
                } elseif ($length !== $length_range) {
                    throw new Exception("No se permite alterar el código JavaScript de la página para establecer una longitud de contraseña diferente en uno de los controles");
                } else {
                    try {
                        $password = generate_password($length);
                        $result = '<div id="contrasena-generada" style="color: #1e90ff; text-align: center; font-size: 1.2rem;">' . htmlspecialchars($password) . '</div>';
                        if ($length <= 177) {
                            $result .= '<div style="color: green; text-align: center;">Tiempo estimado de resistencia a un ataque de fuerza bruta: ' . tiempo_estimado_resistencia_ataque_fuerza_bruta($password) . '</div>';
                        }
                        $result .= '<div style="color: green; text-align: center;">Entropía de la contraseña: ' . entropia($password) . '</div>';
                        $mostrar_boton = true;
                        // Actualizar el valor del input type range en el formulario para futuras generaciones
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
    <title>Admin - PraceAR - Generador de contraseñas - Página de administración</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel='icon' href='./img/favicon.png' type='image/png'>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">

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

    <h1 style="text-align: center;">Generador de contraseñas</h1>

    <div style="text-align: center; margin-bottom: 1rem;">
        <span id="password-text"><?= $result ?></span>
    </div>

    <?php if ($mostrar_boton): ?>
        <div style="text-align: center;">
            <button onclick="copyToClipboard()">Copiar contraseña</button>
        </div>
    <?php endif; ?>

    <form method="POST" action="#" style="max-width: 400px; margin: 0 auto;" id="formulario-generacion-contrasena">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
        <label for="length">Longitud de la contraseña: <span class="required">*</span></label>

        <!-- Campo de número -->
        <input required type="number" id="length-number" name="length" min="16" max="1024"
            value="<?= htmlspecialchars($length, ENT_QUOTES, 'UTF-8') ?>" oninput="syncInputs('number')">

        <!-- Control deslizante (range) -->
        <label for="length-range">Longitud de la contraseña: <span class="required">*</span></label>
        <input required type="range" id="length-range" name="length_range" min="16" max="1024"
            value="<?= htmlspecialchars($length, ENT_QUOTES, 'UTF-8') ?>" oninput="syncInputs('range')">

        <output id="length-output" style="display: block; text-align: center; margin-top: -1.5rem;">
            <?= htmlspecialchars($length, ENT_QUOTES, 'UTF-8') ?>
        </output>

        <input type="submit" value="Generar contraseña">
    </form>

    <div style="text-align: center; color: red; margin-top: 1rem;">
        <p id="parrafo-campos-obligatorios">Los campos marcados con <span class="required">*</span> son obligatorios.
        </p>
    </div>

    <script type="module" src="<?= JS_ADMIN . '/helpers/password_generator.js' ?>"></script>
    <script>
        function copyToClipboard() {
            const passwordText = document.getElementById('contrasena-generada').innerText;
            navigator.clipboard.writeText(passwordText).then(() => {
                // Verificar si ya existe un mensaje de éxito y eliminarlo
                const existingMessage = document.getElementById('success-message');
                if (existingMessage) {
                    existingMessage.remove();
                }
                // Crear un span debajo de la contraseña para mostrar un mensaje de éxito
                const successMessage = document.createElement('span');
                successMessage.id = 'success-message';
                successMessage.textContent = 'Contraseña copiada al portapapeles';
                successMessage.style.color = 'green';
                successMessage.style.display = 'block';
                successMessage.style.textAlign = 'center';
                successMessage.style.marginTop = '1rem';
                document.getElementById('contrasena-generada').insertAdjacentElement('afterend', successMessage);
            }).catch(err => {
                console.error('No se pudo copiar la contraseña al portapapeles: ', err);
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
    </script>
    <script>
        const formulario = document.getElementById('formulario-generacion-contrasena');

        formulario.addEventListener('submit', function (event) {
            event.preventDefault();
            const longitud = document.getElementById('length-number').value;
            const longitudRange = document.getElementById('length-range').value;

            if (longitud < 16 || longitud > 1024 || longitudRange < 16 || longitudRange > 1024 || longitud !== longitudRange) {
                alert('La longitud de la contraseña debe ser un número natural entre 16 y 1024.');
                return;
            }

            formulario.removeEventListener('submit', this);
            formulario.submit();
        });
    </script>
</body>

</html>