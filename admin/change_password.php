<?php
require_once(COMPONENT_ADMIN . 'sections' . DIRECTORY_SEPARATOR . 'header.php');
require_once(HELPERS . 'clean-input.php');
require_once(HELPERS . 'verify-strong-password.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['id'];
    // var_dump($user_id);
    // echo $user_id;
    $old_password = limpiarInput($_POST['old_password']);
    $new_password = limpiarInput($_POST['new_password']);
    $new_password_confirm = limpiarInput($_POST['confirm_password']);
    $nombre_usuario = $_SESSION['nombre_usuario'];

    // Verificar que la contraseña no contenga información del nombre de usuario


    if ($new_password !== $new_password_confirm) {
        echo "<p style='color: red;'>Las contraseñas no coinciden.</p>";
        exit;
    }

    if (!esContrasenhaFuerte($new_password)) {
        echo "<p style='color: red;'>La nueva contraseña no cumple con los requisitos mínimos de seguridad. La contraseña debe tener al menos 12 caracteres, una letra mayúscula, una letra minúscula, un número y un carácter especial.</p>";
        exit;
    }

    if (haSidoFiltradaEnBrechasDeSeguridad($new_password)) {
        echo "<p style='color: red;'>La nueva contraseña ha sido filtrada en brechas de seguridad. Por favor, elige una contraseña más segura.</p>";
        exit;
    }

    if (contrasenhaSimilarAUsuario($new_password, $nombre_usuario)) {
        echo "<p style='color: red;'>La contraseña no puede contener información del nombre de usuario.</p>";
        exit;
    }

    // Verificar la contraseña actual
    $sql = "SELECT password FROM usuarios WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    // var_dump($usuario);

    if ($usuario) {
        $stored_password = $usuario['password'];

        if (strlen($stored_password) === 32 && ctype_xdigit($stored_password)) {
            // Contraseña almacenada en MD5
            if (md5($old_password) === $stored_password) {
                $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);

                $update_sql = "UPDATE usuarios SET password = ? WHERE id = ?";
                $update_stmt = $conexion->prepare($update_sql);
                $update_stmt->bind_param('si', $hashed_new_password, $user_id);
                $update_stmt->execute();

                echo "<p style='color: green;'>Contraseña actualizada correctamente.</p>";
            } else {
                echo "<p style='color: red;'>La contraseña actual no es correcta.</p>";
            }
        } else {
            // Contraseña en bcrypt
            if (password_verify($old_password, $stored_password)) {
                $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);

                $update_sql = "UPDATE usuarios SET password = ? WHERE id = ?";
                $update_stmt = $conexion->prepare($update_sql);
                $update_stmt->bind_param('si', $hashed_new_password, $user_id);
                $update_stmt->execute();

                echo "<p style='color: green;'>Contraseña actualizada correctamente.</p>";
            } else {
                echo "<p style='color: red;'>La contraseña actual no es correcta.</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>No se ha encontrado el usuario.</p>";
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
    <form method="POST">
        <label for="nombre_usuario">Nombre de usuario:</label>
        <input disabled type="text" name="nombre_usuario" value="<?= $_SESSION['nombre_usuario'] ?>">
        <label for="old_password">Contraseña actual:</label>
        <input type="password" name="old_password" required>
        <label for="new_password">Nueva contraseña:</label>
        <input type="password" name="new_password" required>
        <label for="confirm_password">Confirmar nueva contraseña:</label>
        <input type="password" name="confirm_password" required>
        <button type="submit">Cambiar contraseña</button>
    </form>

    <?= $err ?? '' ?>
</body>

</html>