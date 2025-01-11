import { limpiarInput } from './helpers/limpiar_input.js';

const formulario = document.getElementById('formulario');
let errorExist = false;
let errorMessages = '';

formulario.addEventListener('submit', (e) => {
    e.preventDefault();
    let usuario = document.getElementById('login').value;
    let password = document.getElementById('password').value;

    // Limpiar inputs
    usuario = limpiarInput(usuario);
    password = limpiarInput(password);

    // Usuario y password son obligatorios
    if (usuario === '' || password === '') {
        alert('Usuario y password son obligatorios');
        errorMessages += '<ul style="color: red;"><li>Usuario y password son obligatorios</li></ul>';
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