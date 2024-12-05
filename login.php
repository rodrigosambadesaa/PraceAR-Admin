<?php
require_once HELPERS . 'clean-input.php';

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = limpiarInput($_POST['login']);
    $password = limpiarInput($_POST['password']);

    // Consulta para obtener los datos del usuario
    $sql = "SELECT * FROM usuarios WHERE login = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $login);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    var_dump($usuario);

    if ($result->num_rows === 1) {
        // Verificar si la contraseña está almacenada en MD5 o en Bcrypt y si está en MD5, convertirla a Bcrypt

        if (strlen($usuario['password']) === 32 && ctype_xdigit($usuario['password'])) {
            // Contraseña almacenada en MD5
            if (md5($password) === $usuario['password']) {
                // Convertir la contraseña a Bcrypt
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                $update_sql = "UPDATE usuarios SET password = ? WHERE id = ?";
                $update_stmt = $conexion->prepare($update_sql);
                $update_stmt->bind_param('si', $hashed_password, $usuario['id']);
                $update_stmt->execute();

                echo "Inicio de sesión correcto";

                $_SESSION['id'] = $usuario['id'];
                var_dump($_SESSION['id']);
                $_SESSION['login'] = 'logueado';
                $_SESSION['nombre_usuario'] = $login;

                header("Location: $protocolo/$servidor/$subdominio");
                $err = '<p style="color: green;">Inicio de sesión correcto</p>';
                exit;

            } else {
                $err = '<p style="color: red;">Inicio de sesión incorrecto</p>';
            }
        } else {
            // Contraseña en bcrypt
            if (password_verify($password, $usuario['password'])) {
                echo "Inicio de sesión correcto";
                $_SESSION['id'] = $usuario['id'];
                $_SESSION['login'] = 'logueado';
                $_SESSION['nombre_usuario'] = $login;
                header("Location: $protocolo/$servidor/$subdominio");
                $err = '<p style="color: green;">Inicio de sesión correcto</p>';
                exit;
            } else {
                $err = '<p style="color: red;">Inicio de sesión incorrecto</p>';
            }
        }
    } else {
        $err = '<p style="color: red;">Inicio de sesión incorrecto</p>';
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
</head>

<body class="container" style="diplay: grid; place-content: center;min-height: 100vh;max-width: 600px;">
    <form method="POST">
        <div id="form-group">
            <label for="login">Usuario:</label>
            <input type="text" name="login" required>
        </div>
        <div id="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" name="password" required>
        </div>
        <div id="form-group">
            <button type="submit">Iniciar sesión</button>
        </div>
    </form>
    <?= $err ?>
</body>

</html>