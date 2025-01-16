import { limpiarTextarea } from '../../js/helpers/limpiar_input.js';

const formulario = document.getElementById('formulario');

let errorExist = false;
let errorMessages = '';

formulario.addEventListener('submit', (e) => {
    e.preventDefault();  // Evitar el envío del formulario de forma predeterminada

    let descripcion = limpiarTextarea(document.getElementById('descripcion').value);

    // La descripción, si se ha introducido, debe tener un máximo de 450 caracteres
    if (descripcion !== '' && descripcion.length > 450) {
        alert('La descripción, si se ha introducido, debe tener un máximo de 450 caracteres');
        errorMessages += '<li>La descripción, si se ha introducido, debe tener un máximo de 450 caracteres</li>';
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

    // Si no hay errores, enviar el formulario
    formulario.submit();  // Enviar el formulario manualmente
});
