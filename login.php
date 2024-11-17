<?php
require_once(HELPERS . 'clean-input.php');

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    $login = limpiarInput($login);
    $password = limpiarInput($password);

    $password = md5($password);

    $sql = "SELECT * FROM usuarios WHERE login = ? AND password = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('ss', $login, $password);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $_SESSION['id'] = $resultado->fetch_assoc()['id'];
        $_SESSION['login'] = 'logueado';
        $_SESSION['nombre_usuario'] = $login;
        header("Location: $protocolo/$servidor/$subdominio");
        $err = '<p style="color: green">Inicio de sesión correcto</p>';
        exit;
    } else {
        $err = '<p style="color: red">Inicio de sesión incorrecto</p>';
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
    <main>
        <header>
            <h1>Inicio de sesión</h1>
        </header>

        <form method="POST" id="formulario">
            <div class="form-group">
                <label for="login">Usuario:</label>
                <input type="text" id="login" name="login" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <input id="actualizar" type="submit" value="Enviar">
            </div>
        </form>
        <?= $err ?>
    </main>
    <script type="module" src="./js/main_formulario_login.js"></script>
</body>

</html>