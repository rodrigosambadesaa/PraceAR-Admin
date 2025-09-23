"use strict";

import { limpiarTextarea } from '../../js/helpers/clean_input.js';

const formulario = document.getElementById('formulario');

let errorExist = false;
let errorMessages = '';

formulario.addEventListener('submit', function handleSubmit(e) {

    /**
     * Cleans the value of the 'descripcion' textarea element by applying the 
     * `limpiarTextarea` function to its content.
     *
     * @type {string} descripcion - The cleaned value of the 'descripcion' textarea.
     */
    let descripcion = limpiarTextarea(document.getElementById('descripcion').value);

    // La descripción, si se ha introducido, debe tener un máximo de 450 caracteres
    if (descripcion !== '' && descripcion.length > 450) {
        e.preventDefault();
        alert('La descripción, si se ha introducido, debe tener un máximo de 450 caracteres');
        errorMessages += '<li>La descripción, si se ha introducido, debe tener un máximo de 450 caracteres</li>';
        errorExist = true;
        return;
    }

    // El tipo no puede tener más de 50 caracteres
    if (document.getElementById('tipo').value.length > 50) {
        e.preventDefault();
        alert('El tipo no puede tener más de 50 caracteres');
        errorMessages += '<li>El tipo no puede tener más de 50 caracteres</li>';
        errorExist = true;
        return;
    }

    // Si hay errores, no enviar formulario y mostrar mensajes de error creando un div con los mensajes
    if (errorExist) {
        e.preventDefault();
        const div = document.createElement('div');
        div.innerHTML = errorMessages;
        formulario.insertAdjacentElement('afterend', div);
        return;
    }
});
