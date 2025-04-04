<?php
require_once HELPERS . 'clean_input.php';
require_once HELPERS . 'validate_login.php';
require_once HELPERS . 'verify_strong_password.php';

$pepper_config = include 'pepper2.php';

$today = date('Y-m-d');

for ($i = 0; $i < count($pepper_config); $i++) {
    if ($pepper_config[$i]['last_used'] < $today) {
        continue;
    }

    $pepper = $pepper_config[$i]['PASSWORD_PEPPER'];
    break;
}

// El pepper debe ser un string
if (!is_string($pepper)) {
    throw new Exception("El pepper debe ser un texto.");
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

// El pepper no puede tener secuencias de caracteres especiales inseguras
if (tiene_secuencias_caracteres_especiales_inseguras($pepper)) {
    throw new Exception("El pepper no puede tener secuencias de caracteres especiales inseguras.");
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verificar CSRF token
        if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
            throw new Exception("CSRF token no válido");
        }

        $login = validar_login($_POST['login']);
        $password = trim($_POST['password']); // Eliminar espacios al principio y al final, pero conservar internos

        // La contraseña debe ser un string
        if (!is_string($password)) {
            throw new Exception("La contraseña debe ser un texto.");
        }

        // Verificar que la contraseña no está vacía después de trim
        if (empty($password)) {
            throw new Exception("La contraseña no puede estar vacía.");
        }

        if (tiene_espacios_al_principio_o_al_final($_POST['password'])) {
            throw new Exception("La contraseña no puede tener espacios al principio o al final.");
        }

        if (strlen($password) < 16 || strlen($password) > 1024) {
            throw new Exception("La contraseña debe tener entre 16 y 1024 caracteres.");
        }

        // Consulta para obtener los datos del usuario
        $sql = "SELECT * FROM usuarios WHERE login = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('s', $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            $stored_password = $usuario['password'];

            // Verificar si la contraseña se ha hasheado con un pepper anterior y, si es así, actualizar el hash
            for ($i = 0; $i < count($pepper_config); $i++) {
                if (password_verify("{$password}{$pepper_config[$i]['PASSWORD_PEPPER']}", $stored_password)) {
                    $new_hash = password_hash("{$password}{$pepper}", PASSWORD_ARGON2ID);
                    $update_sql = "UPDATE usuarios SET password = ? WHERE id = ?";
                    $update_stmt = $conexion->prepare($update_sql);
                    $update_stmt->bind_param('si', $new_hash, $usuario['id']);
                    $update_stmt->execute();

                    $_SESSION['id'] = $usuario['id'];
                    $_SESSION['login'] = 'logueado';
                    $_SESSION['nombre_usuario'] = $login;
                    $_SESSION['csrf'] = bin2hex(random_bytes(32));
                    header("Location: $protocolo/$servidor/$subdominio");
                    exit;
                }
            }

            // Verificar la contraseña y actualizar el hash si es necesario
            if (password_verify("{$password}{$pepper}", $stored_password)) {
                // Check if the hash needs to be rehashed to Argon2
                if (password_needs_rehash($stored_password, PASSWORD_ARGON2ID)) {
                    $new_hash = password_hash("{$password}{$pepper}", PASSWORD_ARGON2ID);
                    $update_sql = "UPDATE usuarios SET password = ? WHERE id = ?";
                    $update_stmt = $conexion->prepare($update_sql);
                    $update_stmt->bind_param('si', $new_hash, $usuario['id']);
                    $update_stmt->execute();
                }

                echo "Inicio de sesión correcto";
                $_SESSION['id'] = $usuario['id'];
                $_SESSION['login'] = 'logueado';
                $_SESSION['nombre_usuario'] = $login;
                $_SESSION['csrf'] = bin2hex(random_bytes(32));

                header("Location: $protocolo/$servidor/$subdominio");
                exit;
            } else {
                throw new Exception("Inicio de sesión incorrecto");
            }
        } else {
            throw new Exception("Usuario no encontrado");
        }
    } catch (Exception $e) {
        $err = '<span style="color: red; text-align: center;">' . htmlspecialchars($e->getMessage()) . '</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - PraceAR - Formulario de Inicio de Sesión</title>
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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">

    <style>
        .required::after {
            content: " *";
            color: red;
        }

        .note {
            color: red;
            text-align: center;
        }
    </style>
</head>

<body class="container"
    style='display: grid; place-content: center; min-height: 100vh; max-width: 600px; font-family: "Inter", sans-serif !important;'>
    <?php require_once "components/sections/header.php"; ?>
    <h2 style="text-align: center;">Inicio de sesión</h2>
    <?php

    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    ?>

    <form method="POST" id="formulario">
        <input type="hidden" name="csrf" value="<?= isset($_SESSION['csrf']) ? $_SESSION['csrf'] : '' ?>">
        <div id="form-group">
            <label for="login" class="required"><strong>Usuario:</strong></label>
            <input type="text" name="login" id="login" required>
        </div>
        <div id="form-group">
            <label for="password" class="required"><strong>Contraseña:</strong></label>
            <input type="password" name="password" id="password" required>
        </div>
        <div id="form-group">
            <input type="submit" value="Iniciar sesión">
        </div>
    </form>
    <p class="note">Los campos marcados con * son obligatorios</p>
    <?= $err ?>
    <script type="module" src="./js/login.js"></script>
</body>

</html>