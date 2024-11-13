<?php
require_once(COMPONENT_ADMIN . 'sections' . DIRECTORY_SEPARATOR . 'header.php');
require_once(HELPERS . 'clean-input.php');
require_once(HELPERS . 'verify-strong-password.php');

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    $login = limpiarInput($login);
    $oldPassword = limpiarInput($oldPassword);
    $newPassword = limpiarInput($newPassword);
    $confirmPassword = limpiarInput($confirmPassword);

    $oldPassword = md5($oldPassword);

    if (esContrasenhaFuerte($newPassword)) {
        if ($newPassword === $confirmPassword) {
            $newPassword = md5($newPassword);

            $sql = "SELECT * FROM usuarios WHERE login = ? AND password = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('ss', $login, $oldPassword);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                // Contraseña antigua correcta, actualiza la contraseña en la base de datos
                $updateSql = "UPDATE usuarios SET password = ? WHERE login = ?";
                $updateStmt = $conexion->prepare($updateSql);
                $updateStmt->bind_param('ss', $newPassword, $login);
                $updateResult = $updateStmt->execute();

                if ($updateResult) {
                    $err = '<p style="color: green">Contraseña actualizada correctamente</p>';
                } else {
                    $err = '<p style="color: red">Error al actualizar la contraseña</p>';
                }
            } else {
                $err = '<p style="color: red">Contraseña antigua incorrecta</p>';
            }
        } else {
            $err = '<p style="color: red">La confirmación de la contraseña no coincide</p>';
        }
    } else {
        $err = '<p style="color: red">La contraseña no cumple con los requisitos mínimos:</p>';
        $err .= '<ul style="padding-left: 0; text-align: left">';
        $err .= '<li>Al menos 8 caracteres</li>';
        $err .= '<li>Al menos una letra mayúscula</li>';
        $err .= '<li>Al menos una letra minúscula</li>';
        $err .= '<li>Al menos un número</li>';
        $err .= '<li>Al menos un caracter especial</li>';
        $err .= '</ul>';
    }

    // Destruir las variables de contraseñas por motivos de seguridad
    unset($oldPassword);
    unset($newPassword);
    unset($confirmPassword);
}
?>

<main>
    <header>
        <h2 style="text-align: center;">Cambiar contraseña</h2>
    </header>

    <form method="POST">
        <div class="form-group">
            <label for="login">Usuario:</label>
            <input type="text" id="login" name="login" value="<?= $_SESSION['nombre_usuario'] ?>" readonly>
        </div>
        <div class="form-group">
            <label for="old_password">Contraseña antigua:</label>
            <input type="password" id="old_password" name="old_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">Nueva contraseña:</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirmar nueva contraseña:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <div class="form-group">
            <input id="actualizar" type="submit" value="Cambiar contraseña">
        </div>
    </form>
    <div style="text-align: center;">
        <?= $err ?>
    </div>
</main>
</body>

</html>