const minusculasRegex = /[a-z]/;
const mayusculasRegex = /[A-Z]/;
const numerosRegex = /[0-9]/;
// Caracteres especiales permitidos: !@#$%^&*()-_=+[]{}|;:,.<>/
const caracteresEspecialesRegex = /[!@#$%^&*()\-_=+\[\]{}|;:,.<>\/]/;
const tecladoEspanolFilas = ["qwertyuiop", "asdfghjklñ", "zxcvbnm"];
const secuenciasDiagonalesTecladoEspanol = [
    "qaz",
    "wsx",
    "edc",
    "rfv",
    "tgb",
    "yhn",
    "ujm",
    "qazwsx",
    "wsxedc",
    "edcrfv",
    "rfvtgb",
    "tgbnhy",
    "yhnujm",
];
const secuenciasCaracteresEspecialesInseguras = [
    "!@#",
    "@#$",
    "#$%",
    "$%^",
    "%^&",
    "^&*",
    "&*(",
    "*()",
    "()_",
    "-=",
    "[]",
    "{}",
    ";:",
    ",.",
    "./",
    "<>",
    "password",
];
const numeros = "0123456789";
const numerosReverso = numeros.split("").reverse().join("");
const secuenciasDiagonalesTecladoNumerico = [
    "159",
    "951",
    "753",
    "357",
    "147",
    "741",
    "369",
    "963",
    "258",
    "852",
];
export function verifyStrongPassword(password) {
    if (password.length < 16 || password.length > 1024) {
        return false;
    }
    if (!minusculasRegex.test(password)) {
        return false;
    }
    if (!mayusculasRegex.test(password)) {
        return false;
    }
    if (!numerosRegex.test(password)) {
        return false;
    }
    let caracteresEspeciales = 0;
    for (const caracter of password) {
        if (caracteresEspecialesRegex.test(caracter)) {
            caracteresEspeciales += 1;
        }
    }
    if (caracteresEspeciales < 3) {
        return false;
    }
    return true;
}
export function contrasenhaSimilarAUsuario(contrasenha, usuario) {
    const contrasenhaMinusculas = contrasenha.toLowerCase().trim();
    if (!contrasenhaMinusculas) {
        return false;
    }
    const usuarios = Array.isArray(usuario) ? usuario : [usuario];
    for (const nombreUsuario of usuarios) {
        const nombreUsuarioMinusculas = String(nombreUsuario ?? "")
            .toLowerCase()
            .trim();
        if (!nombreUsuarioMinusculas) {
            continue;
        }
        if (nombreUsuarioMinusculas.length >= 4 &&
            contrasenhaMinusculas.includes(nombreUsuarioMinusculas)) {
            return true;
        }
        const fragmentos = nombreUsuarioMinusculas
            .split(/[^a-z0-9ñ]+/u)
            .filter((fragmento) => fragmento.length >= 4);
        for (const fragmento of fragmentos) {
            if (contrasenhaMinusculas.includes(fragmento)) {
                return true;
            }
        }
        const usuarioSoloAlnum = nombreUsuarioMinusculas.replace(/[^a-z0-9ñ]/gu, "");
        if (usuarioSoloAlnum.length < 6) {
            continue;
        }
        for (let i = 0; i <= usuarioSoloAlnum.length - 4; i += 1) {
            const subcadena = usuarioSoloAlnum.substring(i, i + 4);
            if (contrasenhaMinusculas.includes(subcadena)) {
                return true;
            }
        }
    }
    return false;
}
export function tieneSecuenciasNumericasInseguras(contrasenha) {
    const secuenciasNumericasInseguras = [];
    for (let longitud = 3; longitud <= 6; longitud += 1) {
        for (let i = 0; i <= numeros.length - longitud; i += 1) {
            secuenciasNumericasInseguras.push(numeros.substring(i, i + longitud));
            secuenciasNumericasInseguras.push(numerosReverso.substring(i, i + longitud));
        }
    }
    for (const secuencia of secuenciasDiagonalesTecladoNumerico) {
        secuenciasNumericasInseguras.push(secuencia);
    }
    return secuenciasNumericasInseguras.some((secuencia) => contrasenha.includes(secuencia));
}
export function tieneSecuenciasAlfabeticasInseguras(contrasenha) {
    const alfabeto = "abcdefghijklmnopqrstuvwxyz";
    const alfabetoReverso = alfabeto.split("").reverse().join("");
    const secuenciasAlfabeticasInseguras = [];
    for (let longitud = 3; longitud <= 6; longitud += 1) {
        for (let i = 0; i <= alfabeto.length - longitud; i += 1) {
            secuenciasAlfabeticasInseguras.push(alfabeto.substring(i, i + longitud));
            secuenciasAlfabeticasInseguras.push(alfabetoReverso.substring(i, i + longitud));
        }
    }
    for (const fila of tecladoEspanolFilas) {
        for (let longitud = 3; longitud <= Math.min(6, fila.length); longitud += 1) {
            const filaReverso = fila.split("").reverse().join("");
            for (let i = 0; i <= fila.length - longitud; i += 1) {
                secuenciasAlfabeticasInseguras.push(fila.substring(i, i + longitud));
                secuenciasAlfabeticasInseguras.push(filaReverso.substring(i, i + longitud));
            }
        }
    }
    const secuenciasDiagonalesTecladoEspanolReverso = secuenciasDiagonalesTecladoEspanol.map((secuencia) => secuencia.split("").reverse().join(""));
    secuenciasAlfabeticasInseguras.push(...secuenciasDiagonalesTecladoEspanol, ...secuenciasDiagonalesTecladoEspanolReverso);
    return secuenciasAlfabeticasInseguras.some((secuencia) => contrasenha.includes(secuencia));
}
export function tieneSecuenciasDeCaracteresEspecialesInseguras(contrasenha) {
    return secuenciasCaracteresEspecialesInseguras.some((secuencia) =>
        contrasenha.includes(secuencia),
    );
}
export function tieneEspaciosAlPrincipioOAlFinal(contrasenha) {
    return contrasenha.startsWith(" ") || contrasenha.endsWith(" ");
}
