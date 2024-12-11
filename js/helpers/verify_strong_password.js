import crypto from 'crypto';
import fetch from 'node-fetch';

function verifyStrongPassword(password) {
    // Al menos 16 caracteres, al menos una letra mayúscula, al menos una letra minúscula, al menos un número y al menos tres caracteres especiales distintos, y un máximo de 255 caracteres

    const minusculas = /[a-z]/;
    const mayusculas = /[A-Z]/;
    const numeros = /[0-9]/;
    // Caracteres especiales permitidos: !@#$%^&*()-_=+[]{}|;:,.<>

    if (password.length < 16 || password.length > 255) {
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
    const especiales = /[!@#$%^&*()\-_=+\[\]{}|;:,.<>]/;
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

    // Generar el hash SHA-1
    const hashBuffer = await window.crypto.subtle.digest('SHA-1', data);

    // Convertir el hash a formato hexadecimal
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}

async function haSidoFiltradaEnBrechas(password) {
    const hash = await sha1Hash(password);
    const hash_prefix = hash.substring(0, 5);
    const hash_suffix = hash.substring(5).toUpperCase();

    const url = `https://api.pwnedpasswords.com/range/${hash_prefix}`;
    try {
        const response = await fetch(url);

        if (!response.ok) {
            console.error('Error al acceder a la API de brechas de seguridad.');
            return false;
        }

        const data = await response.text();
        const hashes = data.split('\n');

        // Verificar si el sufijo del hash aparece en la respuesta
        return hashes.some((hashLine) => {
            const [hashPart] = hashLine.split(':');
            return hashPart.trim() === hash_suffix;
        });
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

// Exportar funciones usando ES Modules
export { verifyStrongPassword, haSidoFiltradaEnBrechas, contrasenhaSimilarAUsuario };
