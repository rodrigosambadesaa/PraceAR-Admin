<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PraceAR - Cambiar Contraseña</title>
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
    require_once COMPONENT_ADMIN . 'sections' . DIRECTORY_SEPARATOR . 'header.php';
    require_once HELPERS . 'clean_input.php';
    require_once HELPERS . 'verify_strong_password.php';

    // Importar pepper de forma segura
    $pepper_config = include dirname(__FILE__) . '/../pepper.php'; // Ruta ajustada
    $pepper = $pepper_config['PASSWORD_PEPPER'] ?? '';
    if (empty($pepper)) {
        die("<span style='color: red;'>Error: No se ha encontrado el archivo pepper.php</span>");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_SESSION['id'];
        $old_password = limpiar_input($_POST['old_password']);
        $new_password = limpiar_input($_POST['new_password']);
        $new_password_confirm = limpiar_input($_POST['confirm_password']);
        $nombre_usuario = $_SESSION['nombre_usuario'];

        if (empty($old_password) || empty($new_password) || empty($new_password_confirm) || empty($nombre_usuario)) {
            echo "<span style='color: red;'>Por favor, rellena todos los campos.</span>";
            exit;
        }

        if ($new_password !== $new_password_confirm) {
            echo "<span style='color: red;'>Las contraseñas no coinciden.</span>";
            exit;
        }

        if (!es_contrasenha_fuerte($new_password)) {
            echo "<span style='color: red;'>La nueva contraseña no cumple con los requisitos mínimos de seguridad. La contraseña debe tener al menos 16 caracteres, una letra mayúscula, una letra minúscula, un número y tres caracteres especiales.</span>";
            exit;
        }

        if (ha_sido_filtrada_en_brechas_de_seguridad($new_password)) {
            echo "<span style='color: red;'>La nueva contraseña ha sido filtrada en brechas de seguridad. Por favor, elige una contraseña diferente.</span>";
            exit;
        }

        if (contrasenha_similar_a_usuario($new_password, $nombre_usuario)) {
            echo "<span style='color: red;'>La nueva contraseña es similar a tu nombre de usuario. Por favor, elige una contraseña diferente.</span>";
            exit;
        }

        // Check if the new password has been used before
        $sql = "SELECT * FROM old_passwords WHERE user_id = ? AND password = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('is', $user_id, $new_password);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            echo "<span style='color: red;'>La nueva contraseña ha sido utilizada anteriormente. Por favor, elige una contraseña diferente.</span>";
            exit;
        }

        $sql = "SELECT password, salt FROM usuarios WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();

        if ($usuario) {
            $stored_password = $usuario['password'];
            $salt = $usuario['salt'];

            // Verificar password con salt y pepper
            if (password_verify("{$old_password}{$salt}{$pepper}", $stored_password)) {
                $new_salt = bin2hex(random_bytes(16));
                $new_hashed_password = password_hash("{$new_password}{$new_salt}{$pepper}", PASSWORD_BCRYPT, ['cost' => 12]);

                $update_sql = "UPDATE usuarios SET password = ?, salt = ? WHERE id = ?";
                $update_stmt = $conexion->prepare($update_sql);
                $update_stmt->bind_param('ssi', $new_hashed_password, $new_salt, $user_id);
                $update_stmt->execute();

                echo "<div style=\"color: green; padding: 10px; border: 1px solid green; border-radius: 5px; background-color: #e6ffe6; margin-bottom: 20px;\">
                <strong>¡Éxito!</strong> Contraseña cambiada correctamente.
            </div>
            <span><strong>Consejos para mantener tus contraseñas seguras:</strong></span>
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

                // Update table old passwords
                $insert_sql = "INSERT INTO old_passwords (user_id, password, salt) VALUES (?, ?, ?)";
                $insert_stmt = $conexion->prepare($insert_sql);
                $insert_stmt->bind_param('iss', $user_id, $stored_password, $salt);
                $insert_stmt->execute();

            } // Caso especial: contraseña antigua en bcrypt con salt pero sin pepper (migración)
            elseif (password_verify("{$old_password}{$salt}", $stored_password)) {
                $new_salt = bin2hex(random_bytes(16));
                $new_hashed_password = password_hash("{$new_password}{$new_salt}{$pepper}", PASSWORD_BCRYPT, ['cost' => 12]);

                $update_sql = "UPDATE usuarios SET password = ?, salt = ? WHERE id = ?";
                $update_stmt = $conexion->prepare($update_sql);
                $update_stmt->bind_param('ssi', $new_hashed_password, $new_salt, $user_id);
                $update_stmt->execute();

                echo "<div style=\"color: green; padding: 10px; border: 1px solid green; border-radius: 5px; background-color: #e6ffe6; margin-bottom: 20px;\">
                <strong>¡Éxito!</strong> Contraseña cambiada correctamente.
            </div>
            <span><strong>Consejos para mantener tus contraseñas seguras:</strong></span>
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

                // Update table old passwords
                $insert_sql = "INSERT INTO old_passwords (user_id, password, salt) VALUES (?, ?, ?)";
                $insert_stmt = $conexion->prepare($insert_sql);
                $insert_stmt->bind_param('iss', $user_id, $stored_password, $salt);
                $insert_stmt->execute();

            }
            // Caso 2: contraseña antigua en bcrypt sin salt y pepper
            elseif (empty($salt) && password_verify($old_password, $stored_password)) {
                $new_salt = bin2hex(random_bytes(16));
                $new_hashed_password = password_hash("{$new_password}{$new_salt}{$pepper}", PASSWORD_BCRYPT, ['cost' => 12]);

                $update_sql = "UPDATE usuarios SET password = ?, salt = ? WHERE id = ?";
                $update_stmt = $conexion->prepare($update_sql);
                $update_stmt->bind_param('ssi', $new_hashed_password, $new_salt, $user_id);
                $update_stmt->execute();

                echo "<div style=\"color: green; padding: 10px; border: 1px solid green; border-radius: 5px; background-color: #e6ffe6; margin-bottom: 20px;\">
                <strong>¡Éxito!</strong> Contraseña cambiada correctamente.
            </div>
            <span><strong>Consejos para mantener tus contraseñas seguras:</strong></span>
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

                // Update table old passwords
                $insert_sql = "INSERT INTO old_passwords (user_id, password, salt) VALUES (?, ?, ?)";
                $insert_stmt = $conexion->prepare($insert_sql);
                $insert_stmt->bind_param('iss', $user_id, $stored_password, $salt);
                $insert_stmt->execute();
            }
            // Caso 3: Contraseña antigua en MD5
            elseif (strlen($old_password) === 32 && ctype_xdigit($old_password)) {
                if (md5($old_password) == $stored_password) {
                    $new_salt = bin2hex(random_bytes(16));
                    $new_hashed_password = password_hash($new_password . $new_salt . $pepper, PASSWORD_BCRYPT, ['cost' => 12]);

                    $update_sql = "UPDATE usuarios SET password = ?, salt = ? WHERE id = ?";
                    $update_stmt = $conexion->prepare($update_sql);
                    $update_stmt->bind_param('ssi', $new_hashed_password, $new_salt, $user_id);
                    $update_stmt->execute();

                    echo "<div style=\"color: green; padding: 10px; border: 1px solid green; border-radius: 5px; background-color: #e6ffe6; margin-bottom: 20px;\">
                <strong>¡Éxito!</strong> Contraseña cambiada correctamente.
            </div>
            <span><strong>Consejos para mantener tus contraseñas seguras:</strong></span>
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

                    // Update table old passwords
                    $insert_sql = "INSERT INTO old_passwords (user_id, password, salt) VALUES (?, ?, ?)";
                    $insert_stmt = $conexion->prepare($insert_sql);
                    $insert_stmt->bind_param('iss', $user_id, $stored_password, $salt);
                    $insert_stmt->execute();
                } else {
                    echo "<span style='color: red;'>La contraseña actual es incorrecta.</span>";
                }
            } else {
                echo "<span style='color: red;'>La contraseña actual es incorrecta.</span>";
            }
        }
    }
    ?>

    <header>
        <h2 style="text-align: center;">Cambiar contraseña</h2>
    </header>

    <form method="POST" id="formulario-cambio-contrasena">
        <label for="nombre-usuario-input">Nombre de usuario:</label>
        <input disabled type="text" id="nombre-usuario-input" name="nombre_usuario"
            value="<?= htmlspecialchars($_SESSION['nombre_usuario']) ?>">
        <label for="contrasena-actual-input">Contraseña actual:</label>
        <input type="password" id="contrasena-actual-input" name="old_password" required>
        <label for="generar-contrasena-sugerida-btn">Generar contraseña sugerida</label>
        <button type="button" id="generar-contrasena-sugerida-btn">Generar</button>
        <div id="contrasena-sugerida"></div>
        <label for="copiar-contrasena-sugerida-btn">Copiar contraseña sugerida</label>
        <button type="button" id="copiar-contrasena-sugerida-btn">Copiar</button>
        <span id="texto-copiar-contrasena-sugerida"></span>
        <label for="nueva-contrasena-input">Nueva contraseña:</label>
        <input type="password" id="nueva-contrasena-input" name="new_password" required>
        <label for="confirmar-nueva-contrasena-input">Confirmar nueva contraseña:</label>
        <input type="password" id="confirmar-nueva-contrasena-input" name="confirm_password" required>
        <input type="submit" value="Cambiar contraseña">
    </form>


    <?= htmlspecialchars($err ?? '') ?>
    <script type="module" src="<?= htmlspecialchars(JS_ADMIN) ?>change_password.js"></script>
    <script type="module">
        import { generatePassword } from "<?= htmlspecialchars(JS_ADMIN) ?>/helpers/password_generator.js";

        document.getElementById("generar-contrasena-sugerida-btn").addEventListener("click", async () => {
            // console.log("Generando contraseña...");

            try {
                // Esperar que la Promise de generatePassword se resuelva
                const contrasenaSugerida = generatePassword();
                // console.log("Contraseña generada:", contrasenaSugerida);  // Depuración

                // Verificar que la contraseña es válida antes de mostrarla
                if (contrasenaSugerida) {
                    document.getElementById("contrasena-sugerida").innerHTML = `<strong>${contrasenaSugerida}</strong>`;
                } else {
                    console.log("Error: No se generó una contraseña válida.");
                    document.getElementById("contrasena-sugerida").innerHTML = "<span style='color: red;'>Error generando la contraseña.</span>";
                }
            } catch (error) {
                console.error("Error generando la contraseña:", error);
                document.getElementById("contrasena-sugerida").innerHTML = "<span style='color: red;'>Error generando la contraseña.</span>";
            }
        });

        document.getElementById("copiar-contrasena-sugerida-btn").addEventListener("click", () => {
            const contrasenaSugerida = document.getElementById("contrasena-sugerida").innerText;
            if (contrasenaSugerida) {
                navigator.clipboard.writeText(contrasenaSugerida)
                    .then(() => {
                        // Mostrar mensaje de éxito en verde
                        document.getElementById("texto-copiar-contrasena-sugerida").innerHTML = "<span style='color: green;'>Contraseña copiada al portapapeles.</span>";
                    })
                    .catch((error) => {
                        document.getElementById("texto-copiar-contrasena-sugerida").innerHTML = "<span style='color: red;'>Error copiando la contraseña al portapapeles.</span>";
                        console.error("Error copiando la contraseña al portapapeles:", error);
                    });
            } else {
                console.log("Error: No hay una contraseña sugerida para copiar.");
                document.getElementById("texto-copiar-contrasena-sugerida").innerHTML = "<span style='color: red;'>Primero debes generar una contraseña sugerida.</span>";
            }
        });

    </script>



</body>

</html>