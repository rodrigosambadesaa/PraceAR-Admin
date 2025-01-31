<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de contraseñas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel='icon' href='./img/favicon.png' type='image/png'>

    <!-- Iconos para dispositivos Apple -->
    <link rel="apple-touch-icon" sizes="180x180" href="./img/apple-touch-icon-180x180.png">
    <link rel="apple-touch-icon" sizes="152x152" href="./img/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="120x120" href="./img/apple-touch-icon-120x120.png">

    <!-- Icono para Android (PWA) -->
    <link rel="icon" sizes="192x192" href="icon-192x192.png">

    <!-- Manifesto Web (PWA) -->
    <link rel="manifest" href="/manifest.json">
</head>

<body>
    <?php

    function generate_password(int $length)
    {
        if ($length < 16 || $length > 255) {
            throw new Exception("La longitud de la contraseña debe ser un número natural entre 16 y 255.");
        }

        // Generar una contraseña de la longitud especificada, con una mayúscula, una minúscula, un número y tres caracteres especiales válidos
        $password = '';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special_chars = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        $all_chars = $uppercase . $lowercase . $numbers . $special_chars;

        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];

        for ($i = 0; $i < $length - 3; $i++) {
            $password .= $special_chars[random_int(0, strlen($special_chars) - 1)];
        }

        $password = str_shuffle($password);
        return $password;
    }

    require_once HELPERS . "clean_input.php";
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        if (isset($_POST['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
            if (isset($_POST['length'])) {
                $length = limpiar_input($_POST['length']);
                // Validar que la longitud sea un número natural entre 16 y 255
                if (!is_integer($length) || $length < 16 || $length > 255) {
                    echo '<span style="color: red; text-align: center;">La longitud de la contraseña debe ser un número natural entre 16 y 255.</span>';
                } else {
                    try {
                        $password = generate_password($length);
                        echo '<span style="color: green; text-align: center;">Contraseña generada: ' . $password . '</span>';
                    } catch (Exception $e) {
                        echo '<span style="color: red; text-align: center;">' . $e->getMessage() . '</span>';
                    }
                }
            }
        }
    }

    require_once COMPONENT_ADMIN . 'header.php';
    ?>

    <h1 style="text-align: center;">Generador de contraseñas</h1>
    <form method="POST" action="#">
        <input type="hidden" name="csrf" value="<?= isset($_SESSION['csrf']) ? $_SESSION['csrf'] : '' ?>">
        <label for="length">Longitud de la contraseña:</label>
        <input type="number" id="length" name="length" min="1" max="128" required>
        <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>">
        <input type="submit" value="Generar contraseña">
    </form>
    <!-- <span id="contrasenha-generada"></span> -->
    <script src="<?= JS_ADMIN . 'password_generator.js' ?>"></script>
</body>

</html>