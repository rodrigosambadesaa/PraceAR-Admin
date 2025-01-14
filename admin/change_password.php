<?php
require_once COMPONENT_ADMIN . 'sections' . DIRECTORY_SEPARATOR . 'header.php';
require_once HELPERS . 'clean-input.php';
require_once HELPERS . 'verify-strong-password.php';

// Importar pepper de forma segura
$pepper_config = include dirname(__FILE__) . '/../pepper.php'; // Ruta ajustada
$pepper = $pepper_config['PASSWORD_PEPPER'] ?? '';
if (empty($pepper)) {
    die("<span style='color: red;'>Error: No se ha encontrado el archivo pepper.php</span>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['id'];
    $old_password = limpiarInput($_POST['old_password']);
    $new_password = limpiarInput($_POST['new_password']);
    $new_password_confirm = limpiarInput($_POST['confirm_password']);
    $nombre_usuario = $_SESSION['nombre_usuario'];

    // La contraesña antigua, la nueva y la confirmación de la nueva contraseña deben ser strings de entre 16 y 255 caracteres y con una letra mayúscula, una minúscula, un número y al menos tres caracteres especiales válidos distintos
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{16,255}$/', $old_password) || !preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{16,255}$/', $new_password) || !preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{16,255}$/', $new_password_confirm)) {
        echo "<span style='color: red;'>Las contraseñas deben tener entre 16 y 255 caracteres, con al menos una letra mayúscula, una letra minúscula, un número y tres caracteres especiales.</span>";
    }

    if ($new_password !== $new_password_confirm) {
        echo "<span style='color: red;'>Las contraseñas no coinciden.</span>";
        exit;
    }

    if (!esContrasenhaFuerte($new_password)) {
        echo "<span style='color: red;'>La nueva contraseña no cumple con los requisitos mínimos de seguridad. La contraseña debe tener al menos 16 caracteres, una letra mayúscula, una letra minúscula, un número y tres caracteres especiales.</span>";
        exit;
    }

    if (haSidoFiltradaEnBrechasDeSeguridad($new_password)) {
        echo "<span style='color: red;'>La nueva contraseña ha sido filtrada en brechas de seguridad. Por favor, elige una contraseña diferente.</span>";
        exit;
    }

    if (contrasenhaSimilarAUsuario($new_password, $nombre_usuario)) {
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

            echo "<span style='color: green;'>Contraseña cambiada correctamente.</span><br>
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

            echo "<span style='color: green;'>Contraseña cambiada correctamente.</span><br>
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

            echo "<span style='color: green;'>Contraseña cambiada correctamente.</span><br>
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

                echo "<span style='color: green;'>Contraseña cambiada correctamente.</span><br>
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

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cambiar Contraseña</title>
</head>

<header>
    <h2 style="text-align: center;">Cambiar contraseña</h2>
</header>

<body>
    <form method="POST" id="formulario">
        <label for="nombre_usuario">Nombre de usuario:</label>
        <input disabled type="text" id="nombre_usuario" name="nombre_usuario"
            value="<?= htmlspecialchars($_SESSION['nombre_usuario']) ?>">
        <label for="old_password">Contraseña actual:</label>
        <input type="password" id="old_password" name="old_password" required>
        <label for="generar_contrasenha_sugerida">Generar contraseña sugerida</label>
        <button type="button" id="generar_contrasenha_sugerida">Generar</button>
        <div id="contrasenha_sugerida"></div>
        <label for="new_password">Nueva contraseña:</label>
        <input type="password" id="new_password" name="new_password" required>
        <label for="confirm_password">Confirmar nueva contraseña:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        <button type="submit">Cambiar contraseña</button>
    </form>

    <?= htmlspecialchars($err ?? '') ?>
    <script type="module" src="<?= htmlspecialchars(JS_ADMIN) ?>change_password.js"></script>
    <script>
        document.getElementById("generar_contrasenha_sugerida").addEventListener("click", () => {
            const caracteres = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+[]{}|;:,.<>?";
            const longitudMinima = 16;
            const longitudMaxima = 255;
            const longitud = Math.floor(Math.random() * (longitudMaxima - longitudMinima + 1)) + longitudMinima;
            let contrasenha = "";
            let tieneMayuscula = false;
            let tieneMinuscula = false;
            let tieneNumero = false;
            let tieneEspecial = false;

            while (!(tieneMayuscula && tieneMinuscula && tieneNumero && tieneEspecial)) {
                contrasenha = "";
                tieneMayuscula = false;
                tieneMinuscula = false;
                tieneNumero = false;
                tieneEspecial = false;

                for (let i = 0; i < longitud; i++) {
                    const caracter = caracteres.charAt(Math.floor(Math.random() * caracteres.length));
                    contrasenha += caracter;

                    if (/[A-Z]/.test(caracter)) tieneMayuscula = true;
                    if (/[a-z]/.test(caracter)) tieneMinuscula = true;
                    if (/[0-9]/.test(caracter)) tieneNumero = true;
                    if (/[!@#$%^&*()\-_=+\[\]{}|;:,.<>?]/.test(caracter)) tieneEspecial = true;
                }
            }

            const divContrasenhaSugerida = document.getElementById("contrasenha_sugerida");
            divContrasenhaSugerida.textContent = `Contraseña sugerida: ${contrasenha}`;
            divContrasenhaSugerida.style.marginTop = "10px";
            divContrasenhaSugerida.style.fontWeight = "bold";
        });
    </script>
</body>

</html>