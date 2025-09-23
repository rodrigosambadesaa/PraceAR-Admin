"use strict";

import { limpiarInput } from '../../../../js/helpers/clean_input.js';

const formulario = document.getElementById('formulario-generacion-contrasena');

formulario.addEventListener('submit', function (e) {

    const longitud = limpiarInput(document.getElementById('length-number').value);

    // Validar que la longitud sea un número natural entre 16 y 500
    if (isNaN(longitud) || longitud < 16 || longitud > 500) {
        alert('La longitud debe ser un número natural entre 16 y 500');
        e.preventDefault(); // Previene el envío del formulario
        return;
    }
});

const lengthNumberInput = document.getElementById('length-number');

lengthNumberInput.addEventListener('input', function () {
    // No se necesita lógica para la cantidad de contraseñas
});

/**
 * Synchronizes the values of a number input, a range input, and updates the displayed output.
 *
 * @param {string} inputType - The type of input that triggered the synchronization. 
 *                             Accepts either 'number' or 'range'.
 *
 * Elements involved:
 * - An input element with the ID 'length-number' (number input).
 * - An input element with the ID 'length-range' (range input).
 * - An element with the ID 'length-output' (output display).
 */
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

