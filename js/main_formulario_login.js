import { limpiarInput } from './helpers/limpiar_input.js';

const formulario = document.getElementById('formulario');

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
        return;
    }

    // Enviar formulario
    formulario.submit();
});