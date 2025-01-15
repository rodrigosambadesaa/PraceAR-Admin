<?php
require_once HELPERS . 'clean_input.php';

$pepper_config = include 'pepper.php';
$pepper = $pepper_config['PASSWORD_PEPPER'] ?? '';

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = limpiarInput($_POST['login']);
    $password = limpiarInput($_POST['password']);

    // El login debe ser un string
    if (!is_string($login)) {
        $err = '<span style="color: red;">Inicio de sesión incorrecto</span>';
        exit;
    }

    // La contraseña debe ser un string de un mínimo de 16 caracteres y un máximo de 255, con una letra mayúscula, una minúscula, un número y al menos tres caracteres especiales válidos distintos
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{16,255}$/', $password)) {
        $err = '<span style="color: red;">Inicio de sesión incorrecto</span>';
        exit;
    }

    // Consulta para obtener los datos del usuario
    $sql = "SELECT * FROM usuarios WHERE login = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $login);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if ($result->num_rows === 1) {
        $salt = $usuario['salt'] ?? '';

        $stored_password = $usuario['password'];

        // Caso 1: Contraseñas almacenadas en MD5
        if (strlen($usuario['password']) === 32 && ctype_xdigit($usuario['password'])) {
            // Contraseña almacenada en MD5
            if (md5($password) === $usuario['password']) {
                // Migrar a bcrypt con salt y pepper
                $new_salt = bin2hex(random_bytes(16));
                $new_hashed_password = password_hash($password . $new_salt . $pepper, PASSWORD_BCRYPT, ['cost' => 12]);

                // Actualizar hash y salt en la base de datos
                $update_sql = "UPDATE usuarios SET password = ?, salt = ? WHERE id = ?";
                $update_stmt = $conexion->prepare($update_sql);
                $update_stmt->bind_param('ssi', $new_hashed_password, $new_salt, $usuario['id']);
                $update_stmt->execute();

                echo "Inicio de sesión correcto";

                $_SESSION['id'] = $usuario['id'];
                $_SESSION['login'] = 'logueado';
                $_SESSION['nombre_usuario'] = $login;

                $err = '<span style="color: green;">Inicio de sesión correcto</span>';
                header("Location: $protocolo/$servidor/$subdominio");
                exit;
            } else {
                $err = '<span style="color: red;">Inicio de sesión incorrecto</span>';
            }

        }// Caso 2: Contraseña en bcrypt sin salt y pepper  
        elseif (empty($salt) && password_verify($password, $stored_password)) {
            // Migrar a bcrypt con salt y pepper
            $new_salt = bin2hex(random_bytes(16));
            $new_hashed_password = password_hash($password . $new_salt . $pepper, PASSWORD_BCRYPT, ['cost' => 12]);

            // Actualizar hash y salt en la base de datos
            $update_sql = "UPDATE usuarios SET password = ?, salt = ? WHERE id = ?";
            $update_stmt = $conexion->prepare($update_sql);
            $update_stmt->bind_param('ssi', $new_hashed_password, $new_salt, $usuario['id']);
            $update_stmt->execute();

            // Inicio de sesión correcto
            echo "Inicio de sesión correcto";
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['login'] = 'logueado';
            $_SESSION['nombre_usuario'] = $login;

            $err = '<p style="color: green;">Inicio de sesión correcto</p>';
            header("Location: $protocolo/$servidor/$subdominio");
            exit;

        } elseif (empty($salt) && !password_verify($password, $stored_password)) {
            $err = '<span style="color: red;">Inicio de sesión incorrecto</span>';
        }

        // Caso 3: Contraseña en bcrypt con salt y pepper 
        else {
            // Caso 1: Verificación con pepper válido

            if (!empty($salt) && password_verify($password . $salt . $pepper, $stored_password)) {
                // Inicio de sesión correcto
                echo "Inicio de sesión correcto";
                $_SESSION['id'] = $usuario['id'];
                $_SESSION['login'] = 'logueado';
                $_SESSION['nombre_usuario'] = $login;

                $err = '<p style="color: green;">Inicio de sesión correcto</p>';
                header("Location: $protocolo/$servidor/$subdominio");
                exit;
            } // Caso 2: Verificación con pepper vacío (migración)
            elseif (!empty($salt) && password_verify($password . $salt, $stored_password)) {
                // Migrar a bcrypt con salt y pepper
                $new_salt = bin2hex(random_bytes(16));
                $new_hashed_password = password_hash($password . $new_salt . $pepper, PASSWORD_BCRYPT, ['cost' => 12]);

                // Actualizar hash y salt en la base de datos
                $update_sql = "UPDATE usuarios SET password = ?, salt = ? WHERE id = ?";
                $update_stmt = $conexion->prepare($update_sql);
                $update_stmt->bind_param('ssi', $new_hashed_password, $new_salt, $usuario['id']);
                $update_stmt->execute();

                // Inicio de sesión correcto después de migración
                echo "Inicio de sesión correcto";
                $_SESSION['id'] = $usuario['id'];
                $_SESSION['login'] = 'logueado';
                $_SESSION['nombre_usuario'] = $login;

                $err = '<p style="color: green;">Inicio de sesión correcto</p>';
                header("Location: $protocolo/$servidor/$subdominio");
                exit;
            } else {
                $err = '<span style="color: red;">Inicio de sesión incorrecto</span>';
            }
        }
    } else {
        $err = '<span style="color: red;">Inicio de sesión incorrecto</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de inicio de sesión</title>
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

<body class="container" style="diplay: grid; place-content: center;min-height: 100vh;max-width: 600px;">
    <h1 style="text-align: center;">Inicio de sesión</h1>
    <form method="POST" id="formulario">
        <div id="form-group">
            <label for="login">Usuario:</label>
            <input type="text" name="login" id="login" required>
        </div>
        <div id="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div id="form-group">
            <button type="submit">Iniciar sesión</button>
        </div>
    </form>
    <?= $err ?>
    <script type="module" src="./js/main_formulario_login.js"></script>
</body>

</html>