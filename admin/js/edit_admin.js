"use strict";

import { limpiarInput } from "../../js/helpers/clean_input.js";
import { UNITY_TYPE } from "../../js/constants.js";
import { verifyMaliciousPhoto } from "./helpers/verify_malicious_photo.js";

const formulario = document.getElementById('formulario-editar');
let errorExist = false;
let errorMessages = '';

formulario.addEventListener('submit', async function (e) {
    e.preventDefault();

    errorExist = false;
    errorMessages = '';

    // Campos obligatorios
    let caseta = document.getElementById('caseta').value;
    let tipoUnity = document.getElementById('tipo-unity').value;
    let idNave = document.getElementById('id-nave').value;

    // Limpiar inputs
    caseta = limpiarInput(caseta);

    // Caseta, tipoUnity e idNave son obligatorios
    if (caseta === '' || tipoUnity === '' || idNave === '') {
        e.preventDefault();
        alert('Caseta, tipoUnity e idNave son obligatorios');
        errorMessages += '<ul style="color: red;"><li>Caseta, tipoUnity e idNave son obligatorios</li>';
        errorExist = true;
        return;
    }

    // Caseta debe ser una cadena de exactamente 5 caracteres
    if (caseta.length !== 5) {
        e.preventDefault();
        alert('Caseta debe ser una cadena de exactamente 5 caracteres');
        errorMessages += '<li>Caseta debe ser una cadena de exactamente 5 caracteres</li>';
        errorExist = true;
        return;
    }

    // Las dos primeras letras de caseta deben ser "CE", "CO", "MC", "NA", o "NC", y las tres últimas un número entre 1 y 370. Los números se cuentan 001, 002, 003, ..., 370
    const letras = caseta.substring(0, 2);
    const numero = caseta.substring(2);

    if (!['CE', 'CO', 'MC', 'NA', 'NC'].includes(letras) || isNaN(numero) || numero < 1 || numero > 370) {
        e.preventDefault();
        alert('Las dos primeras letras de caseta deben ser "CE", "CO", "MC", "NA", o "NC", y las tres últimas un número entre 1 y 370. Los números se cuentan 001, 002, 003, ..., 370');
        errorMessages += '<li>Las dos primeras letras de caseta deben ser "CE", "CO", "MC", "NA", o "NC", y las tres últimas un número entre 1 y 370. Los números se cuentan 001, 002, 003, ..., 370</li>';
        errorExist = true;
        return;
    }

    // Tipo de unidad debe ser uno de los valores de UNITY_TYPE
    if (!Object.values(UNITY_TYPE).includes(tipoUnity)) {
        e.preventDefault();
        alert('Tipo de unidad debe ser uno de los valores de UNITY_TYPE');
        errorMessages += '<li>Tipo de unidad debe ser uno de los valores de UNITY_TYPE</li>';
        errorExist = true;
        return;
    }

    // El value de ID de nave debe estar entre 1 y 12
    if (isNaN(idNave) || idNave < 1 || idNave > 12) {
        e.preventDefault();
        alert('El value de ID de nave debe estar entre 1 y 12');
        errorMessages += '<li>El value de ID de nave debe estar entre 1 y 12</li>';
        errorExist = true;
        return;
    }

    // Campos opcionales
    let nombre = document.getElementById('nombre').value;
    // Si existe el campo eliminar_imagen, se recoge su valor
    /**
     * Stores the value of the element with the ID 'eliminar-imagen' if it exists, 
     * otherwise assigns an empty string.
     * 
     * @type {string}
     */
    let eliminar_imagen = document.getElementById('eliminar-imagen') ? document.getElementById('eliminar-imagen').value : '';
    let contacto = document.getElementById('contacto').value;
    let telefono = document.getElementById('telefono').value;
    let caseta_padre = document.getElementById('caseta-padre').value;
    // Verificar si existe el campo <input type="file" id="imagen" name="imagen" accept=".jpg, .jpeg"> y, en caso de que se haya subido una foto, recogerla
    // let foto = document.getElementById('imagen') ? document.getElementById('imagen').files[0] : '';

    // Limpiar inputs
    nombre = limpiarInput(nombre);
    eliminar_imagen = limpiarInput(eliminar_imagen);
    contacto = limpiarInput(contacto);
    telefono = limpiarInput(telefono);
    caseta_padre = limpiarInput(caseta_padre);
    console.log('Caseta padre:', caseta_padre);

    // El nombre, si se ha introducido, debe ser una cadena de máximo 50 caracteres
    if (nombre !== '' && nombre.length > 50) {
        e.preventDefault();
        alert('El nombre, si se ha introducido, debe ser una cadena de máximo 50 caracteres');
        errorMessages += '<li>El nombre, si se ha introducido, debe ser una cadena de máximo 50 caracteres</li>';
        errorExist = true;
        return;
    }

    // Eliminar imagen debe ser un entero con valor 0 o 1
    if (eliminar_imagen !== '' && (isNaN(eliminar_imagen) || (eliminar_imagen !== '0' && eliminar_imagen !== '1'))) {
        e.preventDefault();
        alert('Eliminar imagen debe ser un entero con valor 0 o 1');
        errorMessages += '<li>Eliminar imagen debe ser un entero con valor 0 o 1</li>';
        errorExist = true;
        return;
    }

    // Contacto, si se ha introducido, debe ser una cadena de máximo 250 caracteres
    if (contacto !== '' && contacto.length > 250) {
        e.preventDefault();
        alert('Contacto, si se ha introducido, debe ser una cadena de máximo 250 caracteres');
        errorMessages += '<li>Contacto, si se ha introducido, debe ser una cadena de máximo 250 caracteres</li>';
        errorExist = true;
        return;
    }

    // Teléfono, si se ha introducido, debe ser una cadena de máximo 15 caracteres
    if (telefono !== '' && telefono.length > 15) {
        e.preventDefault();
        alert('Teléfono, si se ha introducido, debe ser una cadena de máximo 15 caracteres');
        errorMessages += '<li>Teléfono, si se ha introducido, debe ser una cadena de máximo 15 caracteres</li>';
        errorExist = true;
        return;
    }

    // Caseta padre, si se ha introducido, debe ser una cadena de exactamente 5 caracteres
    if (caseta_padre !== '' && caseta_padre.length !== 5) {
        e.preventDefault();
        alert('Caseta padre, si se ha introducido, debe ser una cadena de exactamente 5 caracteres');
        errorMessages += '<li>Caseta padre, si se ha introducido, debe ser una cadena de exactamente 5 caracteres</li>';
        errorExist = true;
        return;
    }

    // Las dos primeras letras de caseta padre, si se ha introducido, deben ser "CE", "CO", "MC", "NA", o "NC", y las tres últimas un número entre 1 y 370. Los números se cuentan 001, 002, 003, ..., 370
    const letrasPadre = caseta_padre.substring(0, 2);
    const numeroPadre = caseta_padre.substring(2);

    if (caseta_padre !== '' && (!['CE', 'CO', 'MC', 'NA', 'NC'].includes(letrasPadre) || isNaN(numeroPadre) || numeroPadre.length !== 3 || numeroPadre < 1 || numeroPadre > 370)) {
        e.preventDefault();
        alert('Las dos primeras letras de caseta padre deben ser "CE", "CO", "MC", "NA", o "NC", y las tres últimas un número entre 1 y 370. Los números deben ser introducidos con el formato 001, 002, 003, ..., 370');
        errorMessages += '<li>Las dos primeras letras de caseta padre deben ser "CE", "CO", "MC", "NA", o "NC", y las tres últimas un número entre 1 y 370. Los números deben ser introdocidos en el formato 001, 002, 003, ..., 370</li></ul>';
        errorExist = true;
        return;
    }

    // Verificar si se ha subido una foto y, en caso de que se haya subido, si es maliciosa
    const foto = document.getElementById('imagen') ? document.getElementById('imagen').files[0] : null;

    // Solo verificar la imagen si realmente se ha seleccionado un archivo
    if (foto && foto.size > 0) {
        // Verificar que sea un archivo de imagen válido (.jpg o .jpeg)
        const allowedTypes = ['image/jpeg', 'image/jpg'];
        if (!allowedTypes.includes(foto.type)) {
            e.preventDefault();
            alert('La imagen debe ser un archivo .jpg o .jpeg válido.');
            errorMessages += '<li>La imagen debe ser un archivo .jpg o .jpeg válido</li>';
            errorExist = true;
            return;
        }

        // Verificar tamaño máximo (2MB)
        const maxSize = 2048 * 1024; // 2MB en bytes
        if (foto.size > maxSize) {
            e.preventDefault();
            alert('La imagen no puede ser mayor a 2MB.');
            errorMessages += '<li>La imagen no puede ser mayor a 2MB</li>';
            errorExist = true;
            return;
        }

        // Verificar si la imagen es maliciosa
        try {
            const verification = await verifyMaliciousPhoto(foto);

            if (!verification.success) {
                e.preventDefault();
                alert(verification.message || 'No se pudo verificar la imagen.');
                return;
            }

            if (verification.isMalicious) {
                e.preventDefault();
                alert(verification.message || 'La foto es maliciosa. Por favor, desinfecte el archivo o pida ayuda para desinfectarlo o saque una foto nueva tras desinfectar el dispositivo.');
                return;
            }
        } catch (error) {
            e.preventDefault();
            alert('Error al verificar la imagen. Por favor, inténtelo de nuevo.');
            console.error('Error en verificación de imagen:', error);
            return;
        }
    }
    // Si no hay imagen seleccionada, continuar normalmente (la imagen es opcional)

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