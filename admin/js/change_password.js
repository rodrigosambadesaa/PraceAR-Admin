import { limpiarInput } from "../../js/helpers/limpiar_input.js";
import { verifyStrongPassword, haSidoFiltradaEnBrechas, contrasenhaSimilarAUsuario } from "../../js/helpers/verify_strong_password.js";

const formulario = document.getElementById('formulario');
let errorExist = false;
let errorMessages = '';

formulario.addEventListener('submit', async (e) => {
    e.preventDefault();

    let usuario = document.getElementById('nombre-usuario').value;
    let password = document.getElementById('old-password').value;
    let newPassword = document.getElementById('new-password').value;
    let newPasswordConfirmation = document.getElementById('confirm-password').value;

    // Limpiar inputs
    usuario = limpiarInput(usuario);
    password = limpiarInput(password);
    newPassword = limpiarInput(newPassword);
    newPasswordConfirmation = limpiarInput(newPasswordConfirmation);

    // Usuario, password, newPassword y newPasswordConfirmation son obligatorios
    if (usuario === '' || password === '' || newPassword === '' || newPasswordConfirmation === '') {
        alert('Usuario, password, newPassword y newPasswordConfirmation son obligatorios');
        errorMessages += '<ul style="color: red;"><li>Usuario, password, newPassword y newPasswordConfirmation son obligatorios</li>';
        errorExist = true;
        return;
    }

    // La nueva contraseña debe ser segura
    if (!verifyStrongPassword(newPassword)) {
        alert('La nueva contraseña debe tener al menos 16 caracteres, al menos una letra mayúscula, al menos una letra minúscula, al menos un número y al menos tres caracteres especiales distintos válidos: !@#$%^&*()-_=+[]{}|;:,.<> y un máximo de 255 caracteres');
        errorMessages += '<li>La nueva contraseña debe tener al menos 16 caracteres, al menos una letra mayúscula, al menos una letra minúscula, al menos un número y al menos tres caracteres especiales distintos válidos: !@#$%^&*()-_=+[]{}|;:,.<> y un máximo de 255 caracteres</li>';
        errorExist = true;
        return;
    }

    if (await haSidoFiltradaEnBrechas(newPassword)) {
        alert('La nueva contraseña ha sido filtrada en brechas');
        errorMessages += '<li>La nueva contraseña ha sido filtrada en brechas</li>';
        errorExist = true;
        return;
    }

    if (contrasenhaSimilarAUsuario(newPassword, usuario)) {
        alert('La nueva contraseña es similar al nombre de usuario');
        errorMessages += '<li>La nueva contraseña es similar al nombre de usuario</li>';
        errorExist = true;
        return;
    }

    // Las contraseñas no pueden ser iguales
    if (password === newPassword) {
        alert('Las contraseñas no pueden ser iguales');
        errorMessages += '<li>Las contraseñas no pueden ser iguales</li>';
        errorExist = true;
        return;
    }

    // La nueva contraseña y su confirmación deben ser iguales
    if (newPassword !== newPasswordConfirmation) {
        alert('La nueva contraseña y su confirmación deben ser iguales');
        errorMessages += '<li>La nueva contraseña y su confirmación deben ser iguales</li></ul>';
        errorExist = true;
        return;
    }

    // Si hay errores, no enviar formulario y mostrar mensajes de error creando un div con los mensajes
    if (errorExist) {
        const div = document.createElement('div');
        div.innerHTML = errorMessages;
        formulario.insertAdjacentElement('afterend', div);
        return;
    }

    // Enviar formulario
    formulario.submit();

});


