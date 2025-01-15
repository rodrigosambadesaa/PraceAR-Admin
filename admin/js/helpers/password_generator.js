function generatePassword() {

    // Random length between 16 and 255
    const length = Math.floor(Math.random() * 240) + 16;

    const lowerCase = 'abcdefghijklmnopqrstuvwxyz';
    const upperCase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const numbers = '0123456789';
    const specialChars = '!@#$%^&*()-_=+[]{}|;:,.<>?';

    function getRandomChar(charSet) {
        return charSet[Math.floor(Math.random() * charSet.length)];
    }

    let password = '';
    password += getRandomChar(lowerCase);
    password += getRandomChar(upperCase);
    password += getRandomChar(numbers);

    const usedSpecialChars = new Set();
    while (usedSpecialChars.size < 3) {
        const char = getRandomChar(specialChars);
        if (!usedSpecialChars.has(char)) {
            password += char;
            usedSpecialChars.add(char);
        }
    }

    const allChars = lowerCase + upperCase + numbers + specialChars;
    while (password.length < length) {
        password += getRandomChar(allChars);
    }

    // Verificar que la contraseña generada cumple con los requisitos y, si no, volver a generarla
    if (password.match(/[a-z]/) && password.match(/[A-Z]/) && password.match(/[0-9]/) && password.match(/[!@#$%^&*()-_=+[]{}|;:,.<>?]/)) {
        return password;
    } else {
        return generatePassword();
    }
}

console.log(generatePassword(16)); // Ejemplo de uso

export { generatePassword }; // Exportar para poder usar la función en otros archivos