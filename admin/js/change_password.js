const formulario = document.getElementById('formulario-cambio-contrasena');

formulario.addEventListener('submit', (e) => {
    e.preventDefault();

    const fieldOldPassword = document.getElementById('old_password');
    const fieldNewPassword = document.getElementById('new_password');
    const fieldConfirmPassword = document.getElementById('confirm_password');

    const oldPassword = fieldOldPassword.value.trim();
    const oldPasswordOriginal = fieldOldPassword.value;
    const newPassword = fieldNewPassword.value.trim();
    const newPasswordOriginal = fieldNewPassword.value;
    const confirmPassword = fieldConfirmPassword.value.trim();
    const confirmPasswordOriginal = fieldConfirmPassword.value;

    console.log("Old password: ", oldPassword);
    console.log("Old password original: ", oldPasswordOriginal);
    console.log("New password: ", newPassword);
    console.log("New password original: ", newPasswordOriginal);
    console.log("Confirm password: ", confirmPassword);
    console.log("Confirm password original: ", confirmPasswordOriginal);

    // Verificar que ninguna de las contraseñas tenga espacios al inicio o al final
    if (newPassword !== newPasswordOriginal || confirmPassword !== confirmPasswordOriginal || oldPassword !== oldPasswordOriginal) {
        alert('Las contraseñas no pueden tener espacios al inicio o al final.');
        return;
    }

    // Validar que los campos no estén vacíos
    if (!oldPassword || !newPassword || !confirmPassword) {
        alert('Todos los campos son obligatorios.');
        return;
    }

    // Validar que las contraseñas nueva y confirmación coincidan
    if (newPassword !== confirmPassword) {
        alert('Las contraseñas no coinciden.');
        return;
    }

    // Validar que la nueva contraseña tenga al menos 16 caracteres, una letra mayúscula, una letra minúscula, un número y tres caracteres especiales distintos.");
    const regex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{16,}$/;
    if (!regex.test(newPassword)) {
        alert('La nueva contraseña debe tener al menos 16 caracteres, una letra mayúscula, una letra minúscula, un número y tres caracteres especiales distintos.');
        return;
    }

    // Si todas las validaciones pasan, permite el envío del formulario
    formulario.submit();
});
