import { limpiarInput } from "../../js/helpers/limpiar_input.js";
import { verifyStrongPassword } from "../../js/helpers/verify_strong_password.js";

const formulario = document.getElementById('formulario');

formulario.addEventListener('submit', (e) => {
    e.preventDefault();

    let usuario = document.getElementById('login').value;
    let password = document.getElementById('old_password').value;
    let newPassword = document.getElementById('new_password').value;
    let newPasswordConfirmation = document.getElementById('confirm_password').value;

    // Limpiar inputs
    usuario = limpiarInput(usuario);
    password = limpiarInput(password);
    newPassword = limpiarInput(newPassword);
    newPasswordConfirmation = limpiarInput(newPasswordConfirmation);

    // Usuario, password, newPassword y newPasswordConfirmation son obligatorios
    if (usuario === '' || password === '' || newPassword === '' || newPasswordConfirmation === '') {
        alert('Usuario, password, newPassword y newPasswordConfirmation son obligatorios');
        return;
    }

    // La nueva contraseña debe ser segura
    if (!verifyStrongPassword(newPassword)) {
        alert('La nueva contraseña debe tener al menos 8 caracteres, un número, una letra minúscula, una letra mayúscula y un carácter especial');
        return;
    }

    // Las contraseñas no pueden ser iguales
    if (password === newPassword) {
        alert('Las contraseñas no pueden ser iguales');
        return;
    }

    // La nueva contraseña y su confirmación deben ser iguales
    if (newPassword !== newPasswordConfirmation) {
        alert('La nueva contraseña y su confirmación deben ser iguales');
        return;
    }

    // Enviar formulario
    formulario.submit();

});


