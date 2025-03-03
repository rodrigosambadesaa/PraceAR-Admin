import { tieneSecuenciasAlfabeticasInseguras, tieneSecuenciasDeCaracteresEspecialesInseguras, tieneSecuenciasNumericasInseguras } from "../../js/helpers/verify_strong_password.js";

const formulario = document.getElementById('formulario-cambio-contrasena');

formulario.addEventListener('submit', function handleSubmit(e) {

    const fieldOldPassword = document.getElementById('old-password');
    const fieldNewPassword = document.getElementById('new-password');
    const fieldConfirmPassword = document.getElementById('confirm-password');

    const oldPassword = fieldOldPassword.value.trim();
    const oldPasswordOriginal = fieldOldPassword.value;
    const newPassword = fieldNewPassword.value.trim();
    const newPasswordOriginal = fieldNewPassword.value;
    const confirmPassword = fieldConfirmPassword.value.trim();
    const confirmPasswordOriginal = fieldConfirmPassword.value;

    // Verificar que todos los campos sean strings
    if (typeof oldPassword !== 'string' || typeof newPassword !== 'string' || typeof confirmPassword !== 'string') {
        e.preventDefault();
        alert('Error en los datos introducidos.');
        return;
    }

    // Verificar que ninguna de las contraseñas tenga espacios al inicio o al final
    if (newPassword !== newPasswordOriginal || confirmPassword !== confirmPasswordOriginal || oldPassword !== oldPasswordOriginal) {
        e.preventDefault();
        alert('Las contraseñas no pueden tener espacios al inicio o al final.');
        return;
    }

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

    // Validar que la longitud de las contraseñas esté entre 16 y 1024 caracteres
    if (newPassword.length < 16 || newPassword.length > 1024 || confirmPassword.length < 16 || confirmPassword.length > 1024 || oldPassword.length < 16 || oldPassword.length > 1024) {
        e.preventDefault();
        alert('Las contraseñas deben tener entre 16 y 1024 caracteres.');
        return;
    }

    // Validar que la nueva contraseña tenga al menos 16 caracteres, una letra mayúscula, una letra minúscula, un número y tres caracteres especiales distintos.
    const regex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{16,}$/;
    if (!regex.test(newPassword)) {
        e.preventDefault();
        alert('La nueva contraseña debe tener al menos 16 caracteres, una letra mayúscula, una letra minúscula, un número y tres caracteres especiales distintos.');
        return;
    }

    if (tieneSecuenciasAlfabeticasInseguras(newPassword) || tieneSecuenciasDeCaracteresEspecialesInseguras(newPassword) || tieneSecuenciasNumericasInseguras(newPassword)) {
        e.preventDefault();
        alert('La nueva contraseña no puede contener secuencias alfabéticas, de caracteres especiales o numéricas.');
        return;
    }
});
