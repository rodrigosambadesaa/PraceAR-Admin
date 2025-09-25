const minusculasRegex = /[a-z]/;
const mayusculasRegex = /[A-Z]/;
const numerosRegex = /[0-9]/;
// Caracteres especiales permitidos: !@#$%^&*()-_=+[]{}|;:,.<>/
const caracteresEspecialesRegex = /[!@#$%^&*()\-_=+\[\]{}|;:,.<>\/]/;

const tecladoEspanolFilas = [
    "qwertyuiop",
    "asdfghjklñ",
    "zxcvbnm",
];

const secuenciasDiagonalesTecladoEspanol = [
    "qaz", "wsx", "edc", "rfv", "tgb", "yhn", "ujm",
    "qazwsx", "wsxedc", "edcrfv", "rfvtgb", "tgbnhy", "yhnujm",
];

const secuenciasCaracteresEspecialesInseguras = [
    "!@#$%^&*()_+",
    "-=",
    "[]",
    ";'",
    ",./",
    "{}",
    ":\"",
    "<>?",
];

const numeros = "0123456789";
const numerosReverso = numeros.split("").reverse().join("");
const secuenciasDiagonalesTecladoNumerico = [
    "159", "951", "753", "357", "147", "741", "369", "963", "258", "852",
];

export function verifyStrongPassword(password: string): boolean {
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

async function sha1Hash(password: string): Promise<string> {
    const encoder = new TextEncoder();
    const data = encoder.encode(password);
    const hashBuffer = await window.crypto.subtle.digest("SHA-1", data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map((b) => b.toString(16).padStart(2, "0")).join("").toUpperCase();
}

export async function haSidoFiltradaEnBrechas(password: string): Promise<boolean> {
    const hash = (await sha1Hash(password)).toUpperCase();
    const hashPrefix = hash.substring(0, 5);
    const hashSuffix = hash.substring(5);
    const url = `https://api.pwnedpasswords.com/range/${hashPrefix}`;

    try {
        const response = await fetch(url);

        if (!response.ok) {
            console.error("Error al acceder a la API de brechas de seguridad.");
            return false;
        }

        const data = await response.text();
        const hashes = data.split("\n");

        return hashes.some((hashLine) => {
            const [hashPart] = hashLine.split(":");
            return hashPart.trim() === hashSuffix;
        });
    } catch (error) {
        console.error("Error al comprobar la contraseña:", error);
        return false;
    }
}

export function contrasenhaSimilarAUsuario(contrasenha: string, usuario: string | string[]): boolean {
    const contrasenhaMinusculas = contrasenha.toLowerCase();
    const usuarios = Array.isArray(usuario) ? usuario : [usuario];

    for (const nombreUsuario of usuarios) {
        const nombreUsuarioMinusculas = nombreUsuario.toLowerCase();

        if (contrasenhaMinusculas.includes(nombreUsuarioMinusculas)) {
            return true;
        }

        const longitud = nombreUsuarioMinusculas.length;
        for (let i = 0; i <= longitud - 3; i += 1) {
            const subcadena = nombreUsuarioMinusculas.substring(i, i + 3);
            if (contrasenhaMinusculas.includes(subcadena)) {
                return true;
            }
        }
    }

    return false;
}

export function tieneSecuenciasNumericasInseguras(contrasenha: string): boolean {
    const secuenciasNumericasInseguras: string[] = [];

    for (let longitud = 2; longitud <= 5; longitud += 1) {
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

export function tieneSecuenciasAlfabeticasInseguras(contrasenha: string): boolean {
    const alfabeto = "abcdefghijklmnopqrstuvwxyz";
    const alfabetoReverso = alfabeto.split("").reverse().join("");

    const secuenciasAlfabeticasInseguras: string[] = [];

    for (let longitud = 2; longitud <= 5; longitud += 1) {
        for (let i = 0; i <= alfabeto.length - longitud; i += 1) {
            secuenciasAlfabeticasInseguras.push(alfabeto.substring(i, i + longitud));
            secuenciasAlfabeticasInseguras.push(alfabetoReverso.substring(i, i + longitud));
        }
    }

    for (const fila of tecladoEspanolFilas) {
        for (let longitud = 2; longitud <= fila.length; longitud += 1) {
            const filaReverso = fila.split("").reverse().join("");
            for (let i = 0; i <= fila.length - longitud; i += 1) {
                secuenciasAlfabeticasInseguras.push(fila.substring(i, i + longitud));
                secuenciasAlfabeticasInseguras.push(filaReverso.substring(i, i + longitud));
            }
        }
    }

    const secuenciasDiagonalesTecladoEspanolReverso = secuenciasDiagonalesTecladoEspanol.map(
        (secuencia) => secuencia.split("").reverse().join("")
    );
    secuenciasAlfabeticasInseguras.push(
        ...secuenciasDiagonalesTecladoEspanol,
        ...secuenciasDiagonalesTecladoEspanolReverso,
    );

    return secuenciasAlfabeticasInseguras.some((secuencia) => contrasenha.includes(secuencia));
}

export function tieneSecuenciasDeCaracteresEspecialesInseguras(contrasenha: string): boolean {
    const longitud = contrasenha.length;

    for (const secuencia of secuenciasCaracteresEspecialesInseguras) {
        const longitudSecuencia = secuencia.length;
        for (let i = 0; i <= longitud - longitudSecuencia; i += 1) {
            const subcadena = contrasenha.substring(i, i + longitudSecuencia);
            if (secuencia.includes(subcadena)) {
                return true;
            }
        }
    }

    return false;
}

export function tieneEspaciosAlPrincipioOAlFinal(contrasenha: string): boolean {
    return contrasenha.startsWith(" ") || contrasenha.endsWith(" ");
}
