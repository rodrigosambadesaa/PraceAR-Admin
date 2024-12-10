const crypto = require('crypto');
const fetch = require('node-fetch');

function verifyStrongPassword(password) {
    // La contraseña debe tener al menos 12 caracteres, un número, una letra minúscula, una letra mayúscula y un carácter especial y un máximo de 255 caracteres
    const strongPassword = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z0-9]).{12,255}$/;
    return strongPassword.test(password);
}

async function haSidoFiltradaEnBrechas(password) {
    const hash = crypto.createHash('sha1').update(password).digest('hex');
    const hash_prefix = hash.substring(0, 5);
    const hash_suffix = hash.substring(5);

    const url = `https://api.pwnedpasswords.com/range/${hash_prefix}`;
    const response = await fetch(url);

    if (!response.ok) {
        return false;
    }

    const data = await response.text();
    const hashes = data.split('\n');

    for (let hash of hashes) {
        const [hashPart, count] = hash.split(':');
        if (hashPart.toUpperCase() === hash_suffix.toUpperCase()) {
            return true;
        }
    }

    return false;
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

module.exports = {
    verifyStrongPassword,
    haSidoFiltradaEnBrechas,
    contrasenhaSimilarAUsuario
};