<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - PraceAR - Cambiar Contraseña</title>
     <style>
        <?php require_once(CSS_ADMIN . 'header.css'); ?>
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel='icon' href='./img/favicon.png' type='image/png'>

    <link rel="apple-touch-icon" sizes="180x180" href="./img/apple-touch-icon-180x180.png">
    <link rel="apple-touch-icon" sizes="152x152" href="./img/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="120x120" href="./img/apple-touch-icon-120x120.png">

    <link rel="icon" sizes="192x192" href="icon-192x192.png">

    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">
    <style>
        .error-message {
            color: red;
            text-align: center;
        }

        .success-message {
            color: green;
            text-align: center;
        }
    </style>
</head>

<body>

    <?php
    // Incluimos los archivos necesarios.  Es importante usar rutas absolutas o relativas correctas.
    // require_once 'config.php'; // Asegúrate de que este archivo define las constantes COMPONENT_ADMIN, HELPERS, etc.
    require_once COMPONENT_ADMIN . 'sections' . DIRECTORY_SEPARATOR . 'header.php';
    require_once HELPERS . 'clean_input.php';
    require_once HELPERS . 'verify_strong_password.php';
    require_once HELPERS . 'captcha.php';

    $captcha_key = 'change_password_form';

    $pepper_config = include 'pepper2.php';  // Incluimos la configuración del pepper.
    
    $today = date('Y-m-d');
    $pepper = null;
    for ($i = 0; $i < count($pepper_config); $i++) {
        if ($pepper_config[$i]['last_used'] >= $today) {
            $pepper = $pepper_config[$i]['PASSWORD_PEPPER'];
            break;
        }
    }

    if ($pepper === null) {
        throw new Exception("No se pudo determinar un pepper válido.");
    }

    // Validaciones del pepper (las dejamos, aunque en el código original ya estaban)
    if (!is_string($pepper)) {
        throw new Exception("El pepper debe ser un string.");
    }

    if (strlen($pepper) < 16 || strlen($pepper) > 1024) {
        throw new Exception("El pepper debe tener entre 16 y 1024 caracteres.");
    }

    if (tiene_espacios_al_principio_o_al_final($pepper)) {
        throw new Exception("El pepper no puede tener espacios al principio o al final.");
    }

    if (tiene_secuencias_alfabeticas_inseguras($pepper)) {
        throw new Exception("El pepper no puede tener secuencias alfabéticas inseguras.");
    }

    if (tiene_secuencias_numericas_inseguras($pepper)) {
        throw new Exception("El pepper no puede tener secuencias numéricas inseguras.");
    }

    if (tiene_secuencias_caracteres_especiales_inseguras($pepper)) {
        throw new Exception("El pepper no puede tener secuencias de caracteres especiales inseguras.");
    }

    $err = ''; // Inicializamos la variable de error.
    $success_message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
            throw new Exception("Token CSRF inválido.");
        }

        try {
            if (!captcha_validate($captcha_key, $_POST['captcha_answer'] ?? null)) {
                throw new Exception("La verificación captcha no es correcta.");
            }

            $user_id = $_SESSION['id'];
            $old_password = trim($_POST['old_password']);
            $new_password = trim($_POST['new_password']);
            $confirm_password = trim($_POST['confirm_password']);

            // Validaciones de campos (las dejamos, el código original ya las incluía)
            if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
                throw new Exception("Todos los campos son obligatorios.");
            }

            if (!is_string($old_password) || !is_string($new_password) || !is_string($confirm_password)) {
                throw new Exception("Todos los campos deben ser cadenas de texto.");
            }

            if ($new_password !== $confirm_password) {
                throw new Exception("Las contraseñas no coinciden.");
            }

            if (tiene_espacios_al_principio_o_al_final($_POST['new_password']) || tiene_espacios_al_principio_o_al_final($_POST['confirm_password']) || tiene_espacios_al_principio_o_al_final($_POST['old_password'])) {
                throw new Exception("Las contraseñas no pueden tener espacios al principio o al final.");
            }

            if (strlen($new_password) < 16 || strlen($new_password) > 1024) {
                throw new Exception("La nueva contraseña debe tener entre 16 y 1024 caracteres.");
            }

            if (!es_contrasenha_fuerte($new_password)) {
                throw new Exception("La nueva contraseña no cumple con los requisitos de seguridad. Debe tener al menos 16 caracteres, una letra mayúscula, una letra minúscula, un número y tres caracteres especiales distintos.");
            }

            if (ha_sido_filtrada_en_brechas_de_seguridad($new_password)) {
                throw new Exception("La nueva contraseña ha sido filtrada en brechas de seguridad. Por favor, elige una contraseña más segura.");
            }

            if (contrasenha_similar_a_usuario($new_password, $_SESSION['nombre_usuario'])) {
                throw new Exception("La nueva contraseña no puede ser similar al nombre de usuario.");
            }

            if (tiene_secuencias_numericas_inseguras($new_password)) {
                throw new Exception("La nueva contraseña no puede tener secuencias numéricas inseguras como '1234', '12345', '123456', '1234567', '12345678', '123456789', '987654321', '87654321', '7654321', '654321', '54321', '4321', '321', '21', '147', '258', '369', '159', '357'");
            }

            if (tiene_secuencias_alfabeticas_inseguras($new_password)) {
                throw new Exception("La nueva contraseña no puede contener secuencias alfabéticas inseguras como 'abc', 'qwert', 'asdf', 'zxcv', 'poiuy', 'lkjh', 'mnbv'");
            }

            if (tiene_secuencias_caracteres_especiales_inseguras($new_password)) {
                throw new Exception("La nueva contraseña no puede contener secuencias de caracteres especiales inseguras como '()'");
            }

            $contrasenha_vieja_a_insertar_en_old_passwords_encriptada = ''; // Inicializar aquí
    
            // Para todos los pepper, verificar si la contraseña coincide con alguna de las contraseñas antiguas o si es similar a alguna de ellas
            $password_coincide = false; // Variable para controlar si la contraseña antigua coincide
            for ($i = 0; $i < count($pepper_config); $i++) {
                $pepper_usado = $pepper_config[$i]['PASSWORD_PEPPER']; // Usar una variable diferente aquí
    
                // Consulta para obtener la contraseña actual del usuario
                $sql = "SELECT password FROM usuarios WHERE id = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $usuario = $result->fetch_assoc();
                    $stored_password = $usuario['password'];

                    if (password_verify("{$old_password}{$pepper_usado}", $stored_password)) {
                        $password_coincide = true; // La contraseña antigua coincide
                        break; // Importante: Salir del bucle for si la contraseña coincide
                    }
                }
            }

            if (!$password_coincide) {
                throw new Exception("La contraseña actual es incorrecta.");
            }

            $sql = "SELECT password FROM old_passwords WHERE id_usuario = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $old_password_from_db = $row['password'];
                    for ($i = 0; $i < count($pepper_config); $i++) {
                        $pepper_usado = $pepper_config[$i]['PASSWORD_PEPPER'];
                        if (password_verify($new_password . $pepper_usado, $old_password_from_db)) {
                            // Mostrar el mensaje de error si la nueva contraseña coincide con alguna de las antiguas en el class .error-message
                            echo "<div class='error-message'>La nueva contraseña no puede ser igual a una de las contraseñas antiguas.</div>";
                            // Finalizar la ejecución del script para evitar que se inserte la contraseña antigua en la base de datos
                            exit;
                        }
                        if (contrasenha_similar_a_contrasenha_anterior($new_password, $old_password_from_db)) {
                            // Mostrar el mensaje de error si la nueva contraseña es similar a alguna de las antiguas en el class .error-message
                            echo "<div class='error-message'>La nueva contraseña no puede ser similar a una de las contraseñas antiguas.</div>";
                            // Finalizar la ejecución del script para evitar que se inserte la contraseña antigua en la base de datos
                            exit;
                        }
                    }
                }
            }
            // Volvemos a obtener la contraseña de la base de datos para el rehash
            $sql_select = "SELECT password FROM usuarios WHERE id = ?";
            $stmt_select = $conexion->prepare($sql_select);
            $stmt_select->bind_param('i', $user_id);
            $stmt_select->execute();
            $result_select = $stmt_select->get_result();

            if ($result_select->num_rows === 1) {
                $usuario = $result_select->fetch_assoc();
                $stored_password = $usuario['password'];

                // Verificar la contraseña actual para todos los pepper
                for ($i = 0; $i < count($pepper_config); $i++) {
                    $pepper_usado = $pepper_config[$i]['PASSWORD_PEPPER'];
                    if (password_verify("{$old_password}{$pepper_usado}", $stored_password)) {
                        $pepper = $pepper_usado;  // Guardar el pepper que sí funcionó
                        break;
                    }
                }
                // Rehash
                if (password_needs_rehash($stored_password, PASSWORD_ARGON2ID)) {
                    $stored_password = password_hash("{$old_password}{$pepper}", PASSWORD_ARGON2ID);
                    $update_sql = "UPDATE usuarios SET password = ? WHERE id = ?";
                    $update_stmt = $conexion->prepare($update_sql);
                    $update_stmt->bind_param('si', $stored_password, $user_id);
                    $update_stmt->execute();
                }
            }

            // Generar el hash de la nueva contraseña
            $hashed_password = password_hash("{$new_password}{$pepper}", PASSWORD_ARGON2ID);

            // Actualizar la contraseña en la base de datos
            $update_sql = "UPDATE usuarios SET password = ? WHERE id = ?";
            $update_stmt = $conexion->prepare($update_sql);
            $update_stmt->bind_param('si', $hashed_password, $user_id);
            $update_stmt->execute();

            $success_message = "Contraseña cambiada correctamente.";
            echo "<div class='success-message'>Contraseña cambiada correctamente.</div>";
            echo "<span><strong>Consejos para mantener tus contraseñas seguras:</strong></span>
                    <ul>
                        <li>Utiliza una contraseña única para cada cuenta.</li>
                        <li>La longitud mínima de la contraseña debe ser de 16 caracteres, con al menos una letra mayúscula, una letra minúscula, un número y tres caracteres especiales.</li>
                        <li>No compartas tu contraseña con nadie.</li>
                        <li>No guardes tus contraseñas en un lugar visible o de fácil acceso, como en un post-it en tu escritorio o pegado a tu monitor.</li>
                        <li>No uses información personal en tu contraseña, como tu nombre, fecha de nacimiento, nombre de tu mascota, DNI, etc, ni de tus amigos o familiares o información que hayas compartido en redes sociales o en otro lugar público de Internet o de fuera de Internet.</li>
                        <li>No uses contraseñas comunes o fáciles de adivinar, como '123456', 'password', 'qwerty', 'abc123', 'admin', 'root', '1234', 'letmein', 'welcome', 'login', 'princess', 'sunshine'.</li>
                        <li><strong>En este sitio se verifica la fortaleza de la contraseña y se comprueba si ha sido filtrada en brechas de seguridad. Pero esto no indica que se haga en otros sitios, por lo que es importante que sigas estos consejos en todos los sitios donde tengas una cuenta.</strong></li>
                        <li>Utiliza un gestor de contraseñas para almacenar tus contraseñas de forma segura. Asegúrate de que la contraseña maestra cumpla los mismos requisitos de seguridad.</li>
                    </ul>";

            // Insertar la vieja contraseña validada en la tabla de contraseñas antiguas
            $contrasenha_vieja_a_insertar_en_old_passwords_encriptada = password_hash("{$old_password}{$pepper}", PASSWORD_ARGON2ID);
            $sql = "INSERT INTO old_passwords (id, id_usuario, password, date) VALUES (NULL, ?, ?, CURRENT_TIMESTAMP)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('is', $user_id, $contrasenha_vieja_a_insertar_en_old_passwords_encriptada);
            $stmt->execute();

            /* Esperar un minuto y meido antes de redirigir para que le dé tiempo a leer los consejos  
            sleep(90);
            header("refresh:2;url=$protocolo/$servidor/$subdominio"); */
            exit;
        } catch (Exception $e) {
            $err = '<span style="color: red; text-align: center;">' . htmlspecialchars($e->getMessage() ?? 'Error desconocido') . '</span>';
        }
    }

    $captcha_question = captcha_get_question($captcha_key);
    ?>

    <h1 style="text-align: center;">Cambiar contraseña</h1>
    <?php
    if (!isset($_SESSION['csrf'])) {
        echo '<input type="hidden" name="csrf" value="' . ($_SESSION['csrf'] ?? '') . '">';
    }
    ?>

    <form method="POST" id="formulario-cambio-contrasena" aria-labelledby="formulario-titulo">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?? '' ?>">
        <div id="form-group">
            <label for="nombre-usuario">Nombre de usuario: </label>
            <input type="text" name="nombre_usuario" id="nombre-usuario" value="<?= $_SESSION['nombre_usuario'] ?>"
                disabled aria-disabled="true">
        </div>
        <div id="form-group">
            <label for="old-password">Contraseña actual: <span style="color: red;" aria-hidden="true">*</span></label>
            <input type="password" name="old_password" id="old-password" required aria-describedby="password-help">
            <p id="password-help" class="sr-only">Introduce tu contraseña actual.</p>
        </div>
        <div id="form-group">
            <label for="new-password">Nueva contraseña: <span style="color: red;" aria-hidden="true">*</span></label>
            <input type="password" name="new_password" id="new-password" required oninput="checkPasswordRequirements()"
                aria-describedby="new-password-help">
            <p id="new-password-help" class="sr-only">Introduce una nueva contraseña que cumpla con los requisitos.</p>
        </div>
        <div id="form-group">
            <label for="confirm-password">Confirmar nueva contraseña: <span style="color: red;"
                    aria-hidden="true">*</span></label>
            <input type="password" name="confirm_password" id="confirm-password" required
                aria-describedby="confirm-password-help">
            <p id="confirm-password-help" class="sr-only">Introduce nuevamente la nueva contraseña para confirmarla.</p>
        </div>
        <div id="password-requirements" aria-live="polite">
            <span>Requisitos de la nueva contraseña</span>
            <ul>
                <li>Longitud entre 16 y 1024 caracteres</li>
                <li>Una letra mayúscula</li>
                <li>Una letra minúscula</li>
                <li>Un número</li>
                <li>Tres caracteres especiales distintos, como:
                    <strong>! " # $ % & ' ( ) * + , - . / : ; <=> ? @ [ \ ] ^ _ ` { | } ~</strong>
                </li>
                <li>Sin espacios al principio o al final</li>
                <li>Sin secuencias numéricas inseguras como 123, 987, ni en vertical como 147, ni en diagonal como 159 y
                    753</li>
                <li>Sin secuencias alfabéticas inseguras como abc, cba, ni en horizontal como qwe</li>
                <li>Sin secuencias de caracteres especiales inseguras como ()</li>
            </ul>
        </div>
        <div id="form-group">
            <label for="captcha" class="required" aria-hidden="false">Verificación humana: <span style="color: red;"
                    aria-hidden="true">*</span></label>
            <p id="captcha-question" style="margin-bottom: .5rem;">
                <?= htmlspecialchars($captcha_question) ?>
            </p>
            <input type="text" name="captcha_answer" id="captcha" required aria-required="true"
                aria-describedby="captcha-help" inputmode="numeric" pattern="[0-9]+">
            <p id="captcha-help" class="sr-only">Responda con el resultado numérico de la pregunta para continuar.</p>
        </div>
        <div id="form-group">
            <input type="submit" value="Cambiar contraseña" aria-label="Cambiar contraseña">
        </div>
    </form>
    <p style="color: red; text-align: center;">Los campos marcados con <span style="color: red;">*</span> son
        obligatorios</p>
    <span style="color: blue;">¿Necesita ayuda? Le recomendamos que use un navegador con gestor y generador de
        contraseñas
        integrados, como Google
        Chrome o Mozilla Firefox, con la sesión iniciada en su cuenta de Google o Firefox, respectivamente. De esta
        forma, podrá guardar la nueva contraseña en su gestor de contraseñas. El problema es que las contraseñas
        generadas por esos gestores suelen
        ser de 15 caracteres, por lo que puede usar una extensión como 1Password o <a
            href="./?page=password_generator&lang=<?= $_REQUEST['lang'] ?? 'gl' ?>">nuestro generador de contraseñas</a>
        para generar una contraseña de 16 caracteres o más.</span>
    <?= $err ?>
    <script type="module" src="<?= JS_ADMIN ?>check_password_requirements.js"></script>
    <script type="module" src="<?= JS_ADMIN ?>change_password.js"></script>
</body>

</html>