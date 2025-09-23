"use strict";

function verifyStrongPassword(password) {
    // Al menos 16 caracteres, al menos una letra mayúscula, al menos una letra minúscula, al menos un número y al menos tres caracteres especiales distintos, y un máximo de 1024 caracteres

    const minusculas = /[a-z]/;
    const mayusculas = /[A-Z]/;
    const numeros = /[0-9]/;
    // Caracteres especiales permitidos: !@#$%^&*()-_=+[]{}|;:,.<>/
    const especiales = /[!@#$%^&*()\-_=+\[\]{}|;:,.<>\/]/; // Added /

    if (password.length < 16 || password.length > 1024) {
        return false;
    }

    if (!minusculas.test(password)) {
        return false;
    }

    if (!mayusculas.test(password)) {
        return false;
    }

    if (!numeros.test(password)) {
        return false;
    }

    let caracteres_especiales = 0;
    for (let caracter of password) {
        if (especiales.test(caracter)) {
            caracteres_especiales++;
        }
    }

    if (caracteres_especiales < 3) {
        return false;
    }

    return true;
}

async function sha1Hash(password) {
    // Codificar la contraseña como UTF-8
    const encoder = new TextEncoder();
    const data = encoder.encode(password);

    // Generar el hash SHA-1 con Web Crypto API
    const hashBuffer = await window.crypto.subtle.digest('SHA-1', data);

    // Convertir el hash a cadena hexadecimal (mayúsculas para comparación uniforme)
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('').toUpperCase();
}

async function haSidoFiltradaEnBrechas(password) {
    // Generar el hash SHA-1 de la contraseña
    const hash = (await sha1Hash(password)).toUpperCase();
    const hash_prefix = hash.substring(0, 5); // Primeros 5 caracteres del hash
    const hash_suffix = hash.substring(5);       // Resto del hash

    const url = `https://api.pwnedpasswords.com/range/${hash_prefix}`;

    try {
        // Realizar la petición HTTP a la API de HIBP
        const response = await fetch(url);

        if (!response.ok) {
            console.error('Error al acceder a la API de brechas de seguridad.');
            return false;
        }

        // Leer y procesar la respuesta
        const data = await response.text();

        const hashes = data.split('\n');

        // Comparar hash_suffix con la respuesta (ambos en mayúsculas)
        const filtrada = hashes.some((hashLine) => {
            const [hashPart] = hashLine.split(':');
            return hashPart.trim() === hash_suffix;
        });

        return filtrada;

    } catch (error) {
        console.error('Error al comprobar la contraseña:', error);
        return false;
    }
}

function contrasenhaSimilarAUsuario(contrasenha, usuario) {
    // Aseguramos que todos los valores sean minúsculas para comparaciones insensibles a mayúsculas
    contrasenha = contrasenha.toLowerCase();

    // Si el nombre de usuario es un solo valor, lo convertimos en un array para mayor flexibilidad
    let usuarios = Array.isArray(usuario) ? usuario : [usuario];

    // Recorrer todos los nombres de usuario
    for (let nombre_usuario of usuarios) {
        nombre_usuario = nombre_usuario.toLowerCase();

        // Verificar si la contraseña contiene el nombre de usuario completo
        if (contrasenha.includes(nombre_usuario)) {
            return true;
        }

        // Verificar si la contraseña contiene una parte significativa del nombre de usuario
        let longitud = nombre_usuario.length;
        for (let i = 0; i <= longitud - 3; i++) {
            // Extraer substrings de al menos 3 caracteres
            let subcadena = nombre_usuario.substring(i, i + 3);
            if (contrasenha.includes(subcadena)) {
                return true;
            }
        }
    }

    return false;
}

function tieneSecuenciasNumericasInseguras(contrasenha) {
    const secuenciasNumericasInseguras = [];
    const numeros = "0123456789";
    const numerosReverso = numeros.split("").reverse().join("");
    // Secuencias en diagonal en el teclado numérico como 159, 951, 753, 357, 147, 741, 369, 963, etc.
    const secuenciasDiagonalesTeclado = ["159", "951", "753", "357", "147", "741", "369", "963", "258", "852"]; //Same as PHP

    for (let longitud = 2; longitud <= 5; longitud++) { // Changed to 5 to match PHP
        for (let i = 0; i <= numeros.length - longitud; i++) {
            secuenciasNumericasInseguras.push(numeros.substring(i, i + longitud));
            secuenciasNumericasInseguras.push(numerosReverso.substring(i, i + longitud));
        }
    }

    for (let secuencia of secuenciasDiagonalesTeclado) { // Added diagonal sequences
        secuenciasNumericasInseguras.push(secuencia);
    }

    for (let secuencia of secuenciasNumericasInseguras) {
        if (contrasenha.includes(secuencia)) {
            return true;
        }
    }

    return false;
}

function tieneSecuenciasAlfabeticasInseguras(contrasenha) {
    const alfabeto = "abcdefghijklmnopqrstuvwxyz";
    const alfabetoReverso = alfabeto.split("").reverse().join("");
    const filaSuperiorTecladoEspanol = "qwertyuiop";
    const filaMediaTecladoEspanol = "asdfghjklñ";
    const filaInferiorTecladoEspanol = "zxcvbnm";

    const filasTecladoEspanol = [filaSuperiorTecladoEspanol, filaMediaTecladoEspanol, filaInferiorTecladoEspanol];

    const secuenciasAlfabeticasInseguras = []; // Declare the variable here

    // Generar secuencias alfabéticas de longitud 2 a 5
    for (let longitud = 2; longitud <= 5; longitud++) {
        for (let i = 0; i <= alfabeto.length - longitud; i++) {
            secuenciasAlfabeticasInseguras.push(alfabeto.substring(i, i + longitud));
            secuenciasAlfabeticasInseguras.push(alfabetoReverso.substring(i, i + longitud));
        }
    }

    // Agregar secuencias de teclado español
    for (let fila of filasTecladoEspanol) {
        for (let longitud = 2; longitud <= fila.length; longitud++) {
            for (let i = 0; i <= fila.length - longitud; i++) {
                secuenciasAlfabeticasInseguras.push(fila.substring(i, i + longitud));
                secuenciasAlfabeticasInseguras.push(fila.split("").reverse().join("").substring(i, i + longitud));
            }
        }
    }

    // Agregar secuencias de teclado español en diagonal.  Same as PHP
    const secuenciasDiagonalesTecladoEspanol = [
        "qaz", "wsx", "edc", "rfv", "tgb", "yhn", "ujm",
        "qazwsx", "wsxedc", "edcrfv", "rfvtgb", "tgbnhy", "yhnujm"
    ];

    const secuenciasDiagonalesTecladoEspanolReverso = secuenciasDiagonalesTecladoEspanol.map(secuencia => secuencia.split("").reverse().join(""));
    secuenciasAlfabeticasInseguras.push(...secuenciasDiagonalesTecladoEspanol, ...secuenciasDiagonalesTecladoEspanolReverso);

    for (let secuencia of secuenciasAlfabeticasInseguras) {
        if (contrasenha.includes(secuencia)) {
            return true;
        }
    }

    return false;
}

/**
 * Función para verificar si una contraseña contiene secuencias de caracteres especiales inseguras.
 * @param {string} contrasenha Contraseña a verificar.
 * @returns {boolean} Verdadero si la contraseña contiene secuencias de caracteres especiales inseguras, falso en caso contrario.
 */
function tieneSecuenciasDeCaracteresEspecialesInseguras(contrasenha) {
    // Detectar secuencias de caracteres especiales basadas en la distribución de caracteres en el teclado español
    const secuenciasCaracteresEspecialesInseguras = [
        "!@#$%^&*()_+",
        "-=",
        // "~!@#$%^&*()_+",  // Removed ~
        "[]",
        ";'",
        ",./",
        "{}",
        ":\"",
        "<>?"
    ];

    const longitud = contrasenha.length;
    for (let secuencia of secuenciasCaracteresEspecialesInseguras) {
        const longitudSecuencia = secuencia.length;
        for (let i = 0; i <= longitud - longitudSecuencia; i++) {
            const subcadena = contrasenha.substring(i, i + longitudSecuencia);
            if (secuencia.includes(subcadena)) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Función para verificar si una contraseña tiene espacios al principio o al final.
 * @param {string} contrasenha Contraseña a verificar.
 * @returns {boolean} Verdadero si la contraseña tiene espacios al principio o al final, falso en caso contrario.
 */
function tieneEspaciosAlPrincipioOAlFinal(contrasenha) {
    // Verificar si la contraseña tiene espacios al principio o al final
    return contrasenha.startsWith(" ") || contrasenha.endsWith(" ");
}

// Exportar funciones usando ES Modules
export { verifyStrongPassword, haSidoFiltradaEnBrechas, contrasenhaSimilarAUsuario, tieneSecuenciasNumericasInseguras, tieneSecuenciasAlfabeticasInseguras, tieneSecuenciasDeCaracteresEspecialesInseguras, tieneEspaciosAlPrincipioOAlFinal };
