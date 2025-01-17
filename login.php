<?php
require_once HELPERS . 'clean_input.php';
require_once HELPERS . 'validar_login.php';

$pepper_config = include 'pepper.php';
$pepper = $pepper_config['PASSWORD_PEPPER'] ?? '';

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $login = validar_login($_POST['login']);
        $password = trim($_POST['password']); // Eliminar espacios al principio y al final, pero conservar internos

        // Verificar que la contraseña no está vacía después de trim
        if (empty($password)) {
            throw new Exception("La contraseña no puede estar vacía.");
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

            // Verificar la contraseña directamente con bcrypt
            if (password_verify("{$password}{$pepper}", $stored_password)) {
                echo "Inicio de sesión correcto";
                $_SESSION['id'] = $usuario['id'];
                $_SESSION['login'] = 'logueado';
                $_SESSION['nombre_usuario'] = $login;

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

<body class="container" style="display: grid; place-content: center; min-height: 100vh; max-width: 600px;">
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
            <input type="submit" value="Iniciar sesión">
        </div>
    </form>
    <?= $err ?>
    <script type="module" src="./js/main_formulario_login.js"></script>
</body>

</html>