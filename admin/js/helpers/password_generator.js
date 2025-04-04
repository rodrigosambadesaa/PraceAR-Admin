import { limpiarInput } from '../../../../js/helpers/clean_input.js';

const formulario = document.getElementById('formulario-generacion-contrasena');

formulario.addEventListener('submit', function (e) {
    e.preventDefault();

    const longitud = limpiarInput(document.getElementById('length-number').value);
    const cantidad = limpiarInput(document.getElementById('quantity-number').value);

    // Validar que la longitud sea un número natural entre 16 y 1024
    if (isNaN(longitud) || longitud < 16 || longitud > 1024) {
        alert('La longitud debe ser un número natural entre 16 y 1024');
        return;
    }

    // Validar que la cantidad sea un número natural entre 1 y 10
    if (isNaN(cantidad) || cantidad < 1 || cantidad > 10) {
        alert('La cantidad debe ser un número natural entre 1 y 10');
        return;
    }

    // Si no hay errores, se permite el envío del formulario
    formulario.submit();
});

function syncInputs(inputType) {
    const numberInput = document.getElementById('length-number');
    const rangeInput = document.getElementById('length-range');
    const output = document.getElementById('length-output');

    if (inputType === 'number') {
        rangeInput.value = numberInput.value;
    } else if (inputType === 'range') {
        numberInput.value = rangeInput.value;
    }

    output.textContent = rangeInput.value;
}

function copyToClipboard(passwordId) {
    const passwordText = document.getElementById(passwordId).innerText;
    navigator.clipboard.writeText(passwordText).then(() => {
        alert('Contraseña copiada al portapapeles.');
    }).catch(err => {
        console.error('Fallo al copiar la contraseña al portapapeles:', err);
    });
}

