document.getElementById('formulario-cambio-contrasena').addEventListener('submit', (e) => {
    const oldPassword = document.getElementById('old_password').value.trim();
    const newPassword = document.getElementById('new_password').value.trim();
    const confirmPassword = document.getElementById('confirm_password').value.trim();

    // Validar que los campos no estén vacíos
    if (!oldPassword || !newPassword || !confirmPassword) {
        e.preventDefault();
        alert('Todos los campos son obligatorios.');
        return;
    }

    // Validar que las contraseñas nueva y confirmación coincidan
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Las contraseñas no coinciden.');
        return;
    }

    // Validar que la nueva contraseña sea segura
    const passwordRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{16,}$/;
    if (!passwordRegex.test(newPassword)) {
        e.preventDefault();
        alert('La nueva contraseña debe tener al menos 16 caracteres, incluir una letra mayúscula, una letra minúscula, un número y tres caracteres especiales distintos.');
        return;
    }

    // Si todas las validaciones pasan, permite el envío del formulario
});
