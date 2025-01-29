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

    $pepper_config = include 'pepper.php';
    $pepper = $pepper_config['PASSWORD_PEPPER'] ?? '';

    $err = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $user_id = $_SESSION['id'];
            $old_password = trim($_POST['old_password']); // Recortar espacios al principio y al final
            $new_password = trim($_POST['new_password']);
            $confirm_password = trim($_POST['confirm_password']);

            // Validar que los campos no estén vacíos
            if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
                throw new Exception("Todos los campos son obligatorios.");
            }

            // Validar que todos los campos sean strings
            if (!is_string($old_password) || !is_string($new_password) || !is_string($confirm_password)) {
                throw new Exception("Todos los campos deben ser cadenas de texto.");
            }

            // Verificar que las contraseñas nueva y confirmación coincidan
            if ($new_password !== $confirm_password) {
                throw new Exception("Las contraseñas no coinciden.");
            }

            if (tiene_espacios_al_principio_o_al_final($_POST['new_password']) || tiene_espacios_al_principio_o_al_final($_POST['confirm_password']) || tiene_espacios_al_principio_o_al_final($_POST['old_password'])) {
                throw new Exception("Las contraseñas no pueden tener espacios al principio o al final.");
            }

            // Validar que la nueva contraseña sea fuerte
            if (!es_contrasenha_fuerte($new_password)) {
                throw new Exception("La nueva contraseña no cumple con los requisitos de seguridad. Debe tener al menos 16 caracteres, una letra mayúscula, una letra minúscula, un número y tres caracteres especiales distintos.");
            }

            // Validar que la nueva contraseña no haya sido filtrada en brechas
            if (ha_sido_filtrada_en_brechas_de_seguridad($new_password)) {
                throw new Exception("La nueva contraseña ha sido filtrada en brechas de seguridad. Por favor, elige una contraseña más segura.");
            }

            // Validar que la nueva contraesña no sea similar al nombre de usuario
            if (contrasenha_similar_a_usuario($new_password, $_SESSION['nombre_usuario'])) {
                throw new Exception("La nueva contraseña no puede ser similar al nombre de usuario.");
            }

            // Consulta para obtener la contraseña actual del usuario
            $sql = "SELECT password FROM usuarios WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $usuario = $result->fetch_assoc();
                $stored_password = $usuario['password'];

                // Verificar la contraseña actual
                if (!password_verify("{$old_password}{$pepper}", $stored_password)) {
                    throw new Exception("La contraseña actual es incorrecta.");
                }

                // Generar el hash de la nueva contraseña
                $hashed_password = password_hash("{$new_password}{$pepper}", PASSWORD_BCRYPT);

                // Actualizar la contraseña en la base de datos
                $update_sql = "UPDATE usuarios SET password = ? WHERE id = ?";
                $update_stmt = $conexion->prepare($update_sql);
                $update_stmt->bind_param('si', $hashed_password, $user_id);
                $update_stmt->execute();

                echo "<div style='color: green;'>Contraseña cambiada correctamente.</div>";
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

                /* Esperar un minuto y meido antes de redirigir para que le dé tiempo a leer los consejos
                sleep(90);
                header("refresh:2;url=$protocolo/$servidor/$subdominio"); */
                exit;
            } else {
                throw new Exception("Usuario no encontrado.");
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
        <title>Cambiar contraseña</title>
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
        <h1 style="text-align: center;">Cambiar contraseña</h1>
        <form method="POST" id="formulario-cambio-contrasena">
            <div id="form-group">
                <label for="nombre_usuario">Nombre de usuario:</label>
                <input type="text" name="nombre_usuario" id="nombre_usuario" value="<?= $_SESSION['nombre_usuario'] ?>"
                    disabled>
            </div>
            <div id="form-group">
                <label for="old_password">Contraseña actual:</label>
                <input type="password" name="old_password" id="old_password" required>
            </div>
            <div id="form-group">
                <label for="new_password">Nueva contraseña:</label>
                <input type="password" name="new_password" id="new_password" required>
            </div>
            <div id="form-group">
                <label for="confirm_password">Confirmar nueva contraseña:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <div id="form-group">
                <input type="submit" value="Cambiar contraseña">
            </div>
        </form>
        <?= $err ?>
        <script type="module" src="<?= JS_ADMIN ?>change_password.js"></script>
    </body>

    </html>