<?php
declare(strict_types=1);
require_once HELPERS . 'clean_input.php';
require_once HELPERS . 'validate_login.php';
require_once HELPERS . 'verify_strong_password.php';
require_once HELPERS . 'captcha.php';
require_once HELPERS . 'rate_limit.php';

$security_config = include __DIR__ . '/config/security.php';
$rate_limit_config = $security_config['rate_limit'];
$argon2_options = $security_config['argon2'];
$max_request_body_bytes = (int) $security_config['max_request_body_bytes'];
$large_password_threshold = (int) $security_config['logging']['large_password_threshold'];

if (function_exists('ini_set')) {
    foreach ($security_config['php_limits'] as $directive => $value) {
        ini_set($directive, $value);
    }
}

$captcha_key = 'login_form';

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
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $content_length = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : null;

    if ($content_length !== null && $content_length > $max_request_body_bytes) {
        http_response_code(413);
        $err = '<span style="color: red; text-align: center;">La solicitud supera el tamaño máximo permitido.</span>';
        rate_limit_register_failure($ip_address, null, $rate_limit_config);
        error_log(sprintf(
            'Solicitud rechazada: tamaño de cuerpo %d bytes supera el límite de %d bytes para %s.',
            $content_length,
            $max_request_body_bytes,
            $ip_address
        ));
    }

    if ($err === '') {
        try {
            rate_limit_assert_can_attempt($ip_address, null, $rate_limit_config);
        } catch (RateLimitException $exception) {
            http_response_code(429);
            header('Retry-After: ' . $exception->getRetryAfter());
            $err = '<span style="color: red; text-align: center;">Se superó el límite de intentos. Espere ' . htmlspecialchars((string) $exception->getRetryAfter(), ENT_QUOTES, 'UTF-8') . ' segundos antes de volver a intentarlo.</span>';
            error_log(sprintf(
                'Control de velocidad activado para la IP %s. Reintentar en %d segundos.',
                $ip_address,
                $exception->getRetryAfter()
            ));
        }
    }

    $validated_login = null;

    if ($err === '') {
        try {
            // Verificar CSRF token
            if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
                throw new Exception("CSRF token no válido");
            }

            if (!captcha_validate($captcha_key, $_POST['captcha_answer'] ?? null)) {
                throw new Exception("La verificación captcha no es correcta.");
            }

            $login = validar_login($_POST['login']);
            $validated_login = $login;

            rate_limit_assert_can_attempt($ip_address, $login, $rate_limit_config);

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

            if (strlen($password) > $large_password_threshold) {
                error_log(sprintf(
                    'Intento de inicio de sesión con contraseña inusualmente larga (%d caracteres) para el usuario "%s" desde %s.',
                    strlen($password),
                    $login,
                    $ip_address
                ));
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
                        $new_hash = password_hash("{$password}{$pepper}", PASSWORD_ARGON2ID, $argon2_options);
                        $update_sql = "UPDATE usuarios SET password = ? WHERE id = ?";
                        $update_stmt = $conexion->prepare($update_sql);
                        $update_stmt->bind_param('si', $new_hash, $usuario['id']);
                        $update_stmt->execute();

                        session_regenerate_id(true); // Regenerar el ID de sesión tras login exitoso

                        $_SESSION['id'] = $usuario['id'];
                        $_SESSION['login'] = 'logueado';
                        $_SESSION['nombre_usuario'] = $login;
                        $_SESSION['csrf'] = bin2hex(random_bytes(32));

                        // Insertamos el inicio de sesión en la tabla accesos
                        $insert_sql = "INSERT INTO accesos (id, id_usuario, ip, user_agent, fecha, tipo) VALUES (NULL, ?, ?, ?, ?, ?)";
                        $insert_stmt = $conexion->prepare($insert_sql);
                        $user_agent = $_SERVER['HTTP_USER_AGENT'];
                        $date = date('Y-m-d H:i:s');
                        $tipo = 'acceso';
                        $insert_stmt->bind_param('sssss', $usuario['id'], $ip_address, $user_agent, $date, $tipo);
                        $insert_stmt->execute();
                        rate_limit_register_success($ip_address, $login, $rate_limit_config);
                        header("Location: $protocolo/$servidor/$subdominio");
                        exit;
                    }
                }

                // Verificar la contraseña y actualizar el hash si es necesario
                if (password_verify("{$password}{$pepper}", $stored_password)) {
                    // Check if the hash needs to be rehashed to Argon2
                    if (password_needs_rehash($stored_password, PASSWORD_ARGON2ID, $argon2_options)) {
                        $new_hash = password_hash("{$password}{$pepper}", PASSWORD_ARGON2ID, $argon2_options);
                        $update_sql = "UPDATE usuarios SET password = ? WHERE id = ?";
                        $update_stmt = $conexion->prepare($update_sql);
                        $update_stmt->bind_param('si', $new_hash, $usuario['id']);
                        $update_stmt->execute();
                    }

                    session_regenerate_id(true); // Regenerar el ID de sesión tras login exitoso

                    echo "Inicio de sesión correcto";
                    $_SESSION['id'] = $usuario['id'];
                    $_SESSION['login'] = 'logueado';
                    $_SESSION['nombre_usuario'] = $login;
                    $_SESSION['csrf'] = bin2hex(random_bytes(32));

                    // Insertamos el inicio de sesión en la tabla accesos
                    $insert_sql = "INSERT INTO accesos (id, id_usuario, ip, user_agent, fecha, tipo) VALUES (NULL, ?, ?, ?, ?, ?)";
                    $insert_stmt = $conexion->prepare($insert_sql);
                    // Obtener la dirección IP del usuario
                    $user_agent = $_SERVER['HTTP_USER_AGENT'];
                    $date = date('Y-m-d H:i:s');
                    $tipo = 'acceso';
                    $insert_stmt->bind_param('sssss', $usuario['id'], $ip_address, $user_agent, $date, $tipo);
                    $insert_stmt->execute();
                    rate_limit_register_success($ip_address, $login, $rate_limit_config);
                    header("Location: $protocolo/$servidor/$subdominio");
                    exit;
                } else {
                    throw new Exception("Inicio de sesión incorrecto");
                }
            } else {
                throw new Exception("Usuario no encontrado");
            }
        } catch (RateLimitException $exception) {
            http_response_code(429);
            header('Retry-After: ' . $exception->getRetryAfter());
            $err = '<span style="color: red; text-align: center;">Se superó el límite de intentos. Espere ' . htmlspecialchars((string) $exception->getRetryAfter(), ENT_QUOTES, 'UTF-8') . ' segundos antes de volver a intentarlo.</span>';
            error_log(sprintf(
                'Control de velocidad activado para el usuario "%s" desde la IP %s. Reintentar en %d segundos.',
                $validated_login ?? 'desconocido',
                $ip_address,
                $exception->getRetryAfter()
            ));
        } catch (Exception $e) {
            rate_limit_register_failure($ip_address, $validated_login, $rate_limit_config);
            $err = '<span style="color: red; text-align: center;">' . htmlspecialchars($e->getMessage()) . '</span>';
            error_log(sprintf(
                'Intento de inicio de sesión fallido para el usuario "%s" desde %s: %s',
                $validated_login ?? 'desconocido',
                $ip_address,
                $e->getMessage()
            ));
        }
    }
}

$captcha_question = captcha_get_question($captcha_key);
?>

<!DOCTYPE html>
<html lang="es">
    
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin - PraceAR - Formulario de Inicio de Sesión</title>
        <link rel="stylesheet" href="./css/header.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
        <link rel='icon' href='./img/favicon.png' type='image/png'>
        
        <!-- Iconos para dispositivos Apple -->
        <link rel="apple-touch-icon" sizes="180x180" href="./img/apple-touch-icon-180x180.png">
        <link rel="apple-touch-icon" sizes="152x152" href="./img/apple-touch-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="120x120" href="./img/apple-touch-icon-120x120.png">
        
        <!-- Icono para Android (PWA) -->
        <link rel="icon" sizes="192x192" href="icon-192x192.png">
        
        <!-- Manifesto Web (PWA) -->
        <link rel="manifest" href="/appventurers/manifest.json">
        
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
            
            /* Mejorar contraste */
            body {
                background-color: #f9f9f9;
                color: #333;
            }
            
            input[type="submit"] {
                background-color: #007bff;
                color: #fff;
            }
            
            input[type="submit"]:hover {
                background-color: #0056b3;
            }
            </style>
    <link rel="stylesheet" href="./css/darkmode_login.css">
    <script type="module" src="./js/login.js" defer></script>
</head>

<body class="container"
    style='display: grid; place-content: center; min-height: 100vh; max-width: 100%; font-family: "Inter", sans-serif !important;'>
    <?php require_once "components/sections/header.php"; ?>
    <h2 style="text-align: center;" id="form-title">Inicio de sesión</h2>
    <?php

    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    ?>

    <form method="POST" id="formulario" aria-labelledby="form-title" novalidate>
        <input type="hidden" name="csrf" value="<?= isset($_SESSION['csrf']) ? $_SESSION['csrf'] : '' ?>">
        <div id="form-group">
            <label for="login" class="required"><strong>Usuario:</strong></label>
            <input type="text" name="login" id="login" required aria-required="true" aria-describedby="login-help">
            <small id="login-help">Ingrese su nombre de usuario registrado.</small>
        </div>
        <div id="form-group">
            <label for="password" class="required"><strong>Contraseña:</strong></label>
            <input type="password" name="password" id="password" required aria-required="true"
                aria-describedby="password-help">
            <small id="password-help">Ingrese su contraseña. Debe tener entre 16 y 1024 caracteres.</small>
        </div>
        <div id="form-group">
            <label for="captcha" class="required"><strong>Verificación humana:</strong></label>
            <p id="captcha-question" style="margin-bottom: .5rem;">
                <?= htmlspecialchars($captcha_question) ?>
            </p>
            <input type="text" name="captcha_answer" id="captcha" required aria-required="true"
                aria-describedby="captcha-help" inputmode="numeric" pattern="[0-9]+">
            <small id="captcha-help">Responda con el resultado numérico de la pregunta.</small>
        </div>
        <div id="form-group">
            <input type="submit" value="Iniciar sesión" aria-label="Iniciar sesión">
        </div>
    </form>
    <p class="note" role="alert" aria-live="polite">Los campos marcados con * son obligatorios</p>
    <?= $err ?>
    <script src="./js/helpers/dark_mode.js" defer></script>
</body>

</html>