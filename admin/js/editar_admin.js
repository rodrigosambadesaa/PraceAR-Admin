import { limpiarInput } from "../../js/helpers/limpiar_input.js";
import { UNITY_TYPE } from "../../js/constants.js";

const formulario = document.getElementById('formulario');

formulario.addEventListener('submit', (e) => {
    e.preventDefault();

    // Campos obligatorios
    let caseta = document.getElementById('caseta').value;
    let tipoUnity = document.getElementById('tipo_unity').value;
    let idNave = document.getElementById('id_nave').value;

    // Limpiar inputs
    caseta = limpiarInput(caseta);

    // Caseta, tipoUnity e idNave son obligatorios
    if (caseta === '' || tipoUnity === '' || idNave === '') {
        alert('Caseta, tipoUnity e idNave son obligatorios');
        return;
    }

    // Caseta debe ser una cadena de exactamente 5 caracteres
    if (caseta.length !== 5) {
        alert('Caseta debe ser una cadena de exactamente 5 caracteres');
        return;
    }

    // Las dos primeras letras de caseta deben ser "CE", "CO", "MC", "NA", o "NC", y las tres últimas un número entre 1 y 370. Los números se cuentan 001, 002, 003, ..., 370
    const letras = caseta.substring(0, 2);
    const numero = caseta.substring(2);

    if (!['CE', 'CO', 'MC', 'NA', 'NC'].includes(letras) || isNaN(numero) || numero < 1 || numero > 370) {
        alert('Las dos primeras letras de caseta deben ser "CE", "CO", "MC", "NA", o "NC", y las tres últimas un número entre 1 y 370. Los números se cuentan 001, 002, 003, ..., 370');
        return;
    }

    // Tipo de unidad debe ser uno de los valores de UNITY_TYPE
    if (!Object.values(UNITY_TYPE).includes(tipoUnity)) {
        alert('Tipo de unidad debe ser uno de los valores de UNITY_TYPE');
        return;
    }

    // El value de ID de nave debe estar entre 1 y 12
    if (isNaN(idNave) || idNave < 1 || idNave > 12) {
        alert('El value de ID de nave debe estar entre 1 y 12');
        return;
    }

    // Campos opcionales
    let nombre = document.getElementById('nombre').value;
    let eliminar_imagen = document.getElementById('eliminar_imagen').value;
    let contacto = document.getElementById('contacto').value;
    let telefono = document.getElementById('telefono').value;
    let caseta_padre = document.getElementById('caseta_padre').value;

    // Limpiar inputs
    nombre = limpiarInput(nombre);
    eliminar_imagen = limpiarInput(eliminar_imagen);
    contacto = limpiarInput(contacto);
    telefono = limpiarInput(telefono);
    caseta_padre = limpiarInput(caseta_padre);

    // Enviar formulario
    formulario.submit();

});