import { limpiarInput } from '../../../../js/helpers/clean_input.js';

const formulario = document.getElementById('formulario-generacion-contrasena');
const formularioCambioDeContrasenha = document.getElementById('formulario-cambio-contrasena');

formulario.addEventListener('submit', function (e) {
    e.preventDefault();

    const longitud = limpiarInput(document.getElementById('length-number').value);

    // La longitud debe ser un número natural
    if (isNaN(longitud) || longitud <= 0) {
        alert('La longitud debe ser un número natural');
        formularioCambioDeContrasenha.addEventListener('submit', function (e) {
            e.preventDefault();
        });
        return;
    }

    // La longitud debe ser un número natural entre 16 y 1024
    if (longitud < 16 || longitud > 1024) {
        alert('La longitud debe ser un número natural entre 16 y 1024');
        formularioCambioDeContrasenha.addEventListener('submit', function (e) {
            e.preventDefault();
        });
        return;
    }

    // Si no hay errores, se permite el envío del formulario
    formulario.submit();
});

