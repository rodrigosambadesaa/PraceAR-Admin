import { limpiarInput } from "../../js/helpers/limpiar_input.js";
import { verifyStrongPassword, haSidoFiltradaEnBrechas, contrasenhaSimilarAUsuario } from "../../js/helpers/verify_strong_password.js";

const formulario = document.getElementById('formulario');

formulario.addEventListener('submit', async (e) => {
    e.preventDefault();

    let usuario = document.getElementById('nombre_usuario').value;
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
        alert('La nueva contraseña debe tener al menos 16 caracteres, al menos una letra mayúscula, al menos una letra minúscula, al menos un número y al menos tres caracteres especiales distintos válidos: !@#$%^&*()-_=+[]{}|;:,.<> y un máximo de 255 caracteres');
        return;
    }

    if (await haSidoFiltradaEnBrechas(newPassword)) {
        alert('La nueva contraseña ha sido filtrada en brechas');
        return;
    }

    if (contrasenhaSimilarAUsuario(newPassword, usuario)) {
        alert('La nueva contraseña es similar al nombre de usuario');
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


