import { limpiarInput } from "../../js/helpers/limpiar_input.js";
import { UNITY_TYPE } from "../../js/constants.js";
import { verifyMaliciousPhoto } from "./helpers/verify_malicious_photo.js";

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
    // Si existe el campo eliminar_imagen, se recoge su valor
    let eliminar_imagen = document.getElementById('eliminar_imagen') ? document.getElementById('eliminar_imagen').value : '';
    let contacto = document.getElementById('contacto').value;
    let telefono = document.getElementById('telefono').value;
    let caseta_padre = document.getElementById('caseta_padre').value;
    // Verificar si existe el campo <input type="file" id="imagen" name="imagen" accept=".jpg, .jpeg"> y, en caso de que se haya subido una foto, recogerla
    // let foto = document.getElementById('imagen') ? document.getElementById('imagen').files[0] : '';

    // Limpiar inputs
    nombre = limpiarInput(nombre);
    eliminar_imagen = limpiarInput(eliminar_imagen);
    contacto = limpiarInput(contacto);
    telefono = limpiarInput(telefono);
    caseta_padre = limpiarInput(caseta_padre);

    console.log('Caseta padre:', caseta_padre);
    console.log(caseta_padre === '');

    // Caseta padre, si se ha introducido, debe ser una cadena de exactamente 5 caracteres
    if (caseta_padre !== '' && caseta_padre.length !== 5) {
        alert('Caseta padre, si se ha introducido, debe ser una cadena de exactamente 5 caracteres');
        return;
    }

    // Las dos primeras letras de caseta padre, si se ha introducido, deben ser "CE", "CO", "MC", "NA", o "NC", y las tres últimas un número entre 1 y 370. Los números se cuentan 001, 002, 003, ..., 370
    const letrasPadre = caseta_padre.substring(0, 2);
    const numeroPadre = caseta_padre.substring(2);

    if (caseta_padre !== '' && (!['CE', 'CO', 'MC', 'NA', 'NC'].includes(letrasPadre) || isNaN(numeroPadre) || numeroPadre < 1 || numeroPadre > 370)) {
        alert('Las dos primeras letras de caseta padre, si se ha introducido, deben ser "CE", "CO", "MC", "NA", o "NC", y las tres últimas un número entre 1 y 370. Los números se cuentan 001, 002, 003, ..., 370');
        return;
    }

    // Comprobar si la foto, si se ha subido, es maliciosa
    /*if (foto !== '') {
        const esMaliciosa = verifyMaliciousPhoto(foto);
        if (esMaliciosa) {
            alert('La foto es maliciosa');
            return;
        }
    }*/

    // Enviar formulario
    formulario.submit();

});