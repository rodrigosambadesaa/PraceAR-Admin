function verifyStrongPassword(password) {
    // La contraseña debe tener al menos 12 caracteres, un número, una letra minúscula, una letra mayúscula y un carácter especial y un máximo de 255 caracteres
    const strongPassword = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z0-9]).{12,255}$/;
    return strongPassword.test(password);
}

export { verifyStrongPassword };