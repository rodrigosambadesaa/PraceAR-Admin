import {
    tieneSecuenciasAlfabeticasInseguras,
    tieneSecuenciasDeCaracteresEspecialesInseguras,
    tieneSecuenciasNumericasInseguras,
} from "../../js/helpers/verify_strong_password.js";

function getPasswordInput(id: string): HTMLInputElement {
    const element = document.getElementById(id);

    if (!(element instanceof HTMLInputElement)) {
        throw new Error(`No se encontró el campo de contraseña con id "${id}".`);
    }

    return element;
}

const formulario = document.getElementById("formulario-cambio-contrasena");

if (!(formulario instanceof HTMLFormElement)) {
    throw new Error("No se encontró el formulario de cambio de contraseña.");
}

const campoContrasenaActual = getPasswordInput("old-password");
const campoNuevaContrasena = getPasswordInput("new-password");
const campoConfirmacion = getPasswordInput("confirm-password");

formulario.addEventListener("submit", (event: SubmitEvent) => {
    const contrasenaActual = campoContrasenaActual.value.trim();
    const contrasenaActualOriginal = campoContrasenaActual.value;
    const nuevaContrasena = campoNuevaContrasena.value.trim();
    const nuevaContrasenaOriginal = campoNuevaContrasena.value;
    const confirmacion = campoConfirmacion.value.trim();
    const confirmacionOriginal = campoConfirmacion.value;

    if (
        typeof contrasenaActual !== "string"
        || typeof nuevaContrasena !== "string"
        || typeof confirmacion !== "string"
    ) {
        event.preventDefault();
        alert("Error en los datos introducidos.");
        return;
    }

    if (
        contrasenaActualOriginal !== contrasenaActual
        || nuevaContrasenaOriginal !== nuevaContrasena
        || confirmacionOriginal !== confirmacion
    ) {
        event.preventDefault();
        alert("Las contraseñas no pueden tener espacios al inicio o al final.");
        return;
    }

    if (!contrasenaActual || !nuevaContrasena || !confirmacion) {
        event.preventDefault();
        alert("Todos los campos son obligatorios.");
        return;
    }

    if (nuevaContrasena !== confirmacion) {
        event.preventDefault();
        alert("Las contraseñas no coinciden.");
        return;
    }

    const longitudInvalida = (valor: string) => valor.length < 16 || valor.length > 1024;

    if (
        longitudInvalida(contrasenaActual)
        || longitudInvalida(nuevaContrasena)
        || longitudInvalida(confirmacion)
    ) {
        event.preventDefault();
        alert("Las contraseñas deben tener entre 16 y 1024 caracteres.");
        return;
    }

    let numeroCaracteresEspeciales = 0;
    let numeroMayusculas = 0;
    let numeroMinusculas = 0;
    let numeroNumeros = 0;

    const regexCaracteresEspeciales = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/;
    const regexMayusculas = /[A-Z]/;
    const regexMinusculas = /[a-z]/;
    const regexNumeros = /[0-9]/;

    for (const caracter of nuevaContrasena) {
        if (regexCaracteresEspeciales.test(caracter)) {
            numeroCaracteresEspeciales += 1;
        } else if (regexMayusculas.test(caracter)) {
            numeroMayusculas += 1;
        } else if (regexMinusculas.test(caracter)) {
            numeroMinusculas += 1;
        } else if (regexNumeros.test(caracter)) {
            numeroNumeros += 1;
        }
    }

    if (
        numeroMayusculas < 1
        || numeroMinusculas < 1
        || numeroNumeros < 1
        || numeroCaracteresEspeciales < 3
    ) {
        event.preventDefault();
        alert("La nueva contraseña debe tener al menos una letra mayúscula, una letra minúscula, un número y tres caracteres especiales distintos.");
        return;
    }

    if (
        tieneSecuenciasAlfabeticasInseguras(nuevaContrasena)
        || tieneSecuenciasDeCaracteresEspecialesInseguras(nuevaContrasena)
        || tieneSecuenciasNumericasInseguras(nuevaContrasena)
    ) {
        event.preventDefault();
        alert("La nueva contraseña no puede contener secuencias alfabéticas, de caracteres especiales o numéricas inseguras como \"abc\", \"qwerty\", \"qaz\", \"123\", \"147\", \"159\"");
    }
});

// Cuando cargue la página, meter todas las etiquetas style en el head en una sola
window.addEventListener("load", () => {
    const styles = Array.from(document.querySelectorAll("style"));
    const head = document.head;

    if (styles.length > 0) {
        const combinedStyle = document.createElement("style");
        combinedStyle.textContent = styles.map((style) => style.textContent).join("\n");
        head.appendChild(combinedStyle);
        styles.forEach((style) => style.remove());
    }
});
