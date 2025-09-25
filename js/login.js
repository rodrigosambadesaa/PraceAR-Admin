import { tieneSecuenciasAlfabeticasInseguras, tieneSecuenciasDeCaracteresEspecialesInseguras, tieneSecuenciasNumericasInseguras, } from "./helpers/verify_strong_password.js";
function obtenerElementoFormulario(id, tipo) {
    const element = document.getElementById(id);
    if (!element) {
        throw new Error(`No se encontró el elemento con id "${id}".`);
    }
    if (!(element instanceof tipo)) {
        throw new Error(`El elemento con id "${id}" no es del tipo esperado.`);
    }
    return element;
}
function obtenerInputsFormulario() {
    const login = obtenerElementoFormulario("login", HTMLInputElement);
    const password = obtenerElementoFormulario("password", HTMLInputElement);
    return { login, password };
}
const formulario = document.getElementById("formulario");
if (!(formulario instanceof HTMLFormElement)) {
    throw new Error("No se encontró el formulario de autenticación.");
}
formulario.addEventListener("submit", (event) => {
    const { login, password } = obtenerInputsFormulario();
    const loginValue = login.value.trim();
    const passwordValue = password.value.trim();
    const passwordOriginal = password.value;
    if (typeof loginValue !== "string" || typeof passwordValue !== "string") {
        event.preventDefault();
        alert("Error en el formulario.");
        return;
    }
    if (passwordOriginal !== passwordOriginal.trim()) {
        event.preventDefault();
        alert("La contraseña no puede contener espacios al principio o al final.");
        return;
    }
    const loginRegex = /^[a-zA-Z0-9._-]{3,50}$/;
    if (!loginValue) {
        event.preventDefault();
        alert("El campo de usuario no puede estar vacío.");
        return;
    }
    if (!loginRegex.test(loginValue)) {
        event.preventDefault();
        alert("El nombre de usuario debe tener entre 3 y 50 caracteres y solo puede contener letras, números, puntos, guiones y guiones bajos.");
        return;
    }
    if (/^[\d._-]/.test(loginValue)) {
        event.preventDefault();
        alert("El nombre de usuario no puede comenzar por un número, guion, guion bajo o punto.");
        return;
    }
    if (!passwordValue) {
        event.preventDefault();
        alert("El campo de contraseña no puede estar vacío.");
        return;
    }
    if (passwordValue.length < 16 || passwordValue.length > 1024) {
        event.preventDefault();
        alert("La contraseña debe tener entre 16 y 1024 caracteres.");
        return;
    }
    let numeroCaracteresEspeciales = 0;
    let numeroMayusculas = 0;
    let numeroMinusculas = 0;
    let numeroNumeros = 0;
    const regexCaracteresEspeciales = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/;
    const regexMinusculas = /[a-z]/;
    const regexMayusculas = /[A-Z]/;
    const regexNumeros = /[0-9]/;
    for (const caracter of passwordValue) {
        if (regexNumeros.test(caracter)) {
            numeroNumeros += 1;
        }
        if (regexMinusculas.test(caracter)) {
            numeroMinusculas += 1;
        }
        if (regexMayusculas.test(caracter)) {
            numeroMayusculas += 1;
        }
        if (regexCaracteresEspeciales.test(caracter)) {
            numeroCaracteresEspeciales += 1;
        }
    }
    if (numeroMayusculas < 1 || numeroMinusculas < 1 || numeroNumeros < 1 || numeroCaracteresEspeciales < 3) {
        event.preventDefault();
        alert("La contraseña debe tener al menos una letra mayúscula, una letra minúscula, un número y tres caracteres especiales distintos.");
        return;
    }
    if (tieneSecuenciasAlfabeticasInseguras(passwordValue)
        || tieneSecuenciasDeCaracteresEspecialesInseguras(passwordValue)
        || tieneSecuenciasNumericasInseguras(passwordValue)) {
        event.preventDefault();
        alert("La contraseña no puede contener secuencias alfabéticas, de caracteres especiales o numéricas inseguras como \"abc\", \"qwerty\", \"qaz\", \"123\", \"147\", \"159\"");
    }
});
