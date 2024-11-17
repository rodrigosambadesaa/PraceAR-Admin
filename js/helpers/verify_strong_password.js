function verifyStrongPassword(password) {
    // La contraseña debe tener al menos 8 caracteres, un número, una letra minúscula, una letra mayúscula y un carácter especial	
    const strongPassword = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/;
    return strongPassword.test(password);
}

export { verifyStrongPassword };