import { tieneSecuenciasAlfabeticasInseguras, tieneSecuenciasDeCaracteresEspecialesInseguras, tieneSecuenciasNumericasInseguras, } from "../../js/helpers/verify_strong_password.js";
function getPasswordInput(id) {
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
const csrfInput = formulario.querySelector('input[name="csrf"]');
const generatorToggleButton = document.getElementById("toggle-password-generator");
const generatorPanel = document.getElementById("password-generator-panel");
const lengthNumberInput = document.getElementById("password-length-number");
const lengthRangeInput = document.getElementById("password-length-range");
const lengthOutput = document.getElementById("password-length-output");
const generateButton = document.getElementById("generate-password-button");
const generatorFeedback = document.getElementById("password-generator-feedback");
const generatedPasswordContainer = document.getElementById("generated-password-container");
const generatedPasswordValue = document.getElementById("generated-password-value");
const copyButton = document.getElementById("copy-generated-password");
const copyFeedback = document.getElementById("password-copy-feedback");
const resistanceElement = document.getElementById("password-length-resistance");
const statsElements = {
    uppercase: document.getElementById("password-stat-uppercase"),
    lowercase: document.getElementById("password-stat-lowercase"),
    digits: document.getElementById("password-stat-digits"),
    special: document.getElementById("password-stat-special"),
    hashResistance: document.getElementById("password-stat-hash"),
    entropy: document.getElementById("password-stat-entropy"),
};
function setGeneratorFeedback(message, type = "info") {
    if (!generatorFeedback)
        return;
    generatorFeedback.textContent = message;
    generatorFeedback.classList.remove("admin-error-text", "admin-success-text", "admin-info-text");
    if (type === "error") {
        generatorFeedback.classList.add("admin-error-text");
    }
    else if (type === "success") {
        generatorFeedback.classList.add("admin-success-text");
    }
    else {
        generatorFeedback.classList.add("admin-info-text");
    }
}
function calculateLengthResistance(length) {
    if (length >= 16 && length <= 20)
        return "Resistente a atacantes individuales.";
    if (length > 20 && length <= 30)
        return "Resistente a grupos pequeños de atacantes.";
    if (length > 30 && length <= 50)
        return "Resistente a empresas con recursos moderados.";
    if (length > 50 && length <= 70)
        return "Resistente a grandes empresas.";
    if (length > 70)
        return "Resistente a gobiernos y organizaciones con recursos avanzados.";
    return "Longitud insuficiente para garantizar resistencia.";
}
function updateResistance() {
    if (!resistanceElement || !(lengthNumberInput instanceof HTMLInputElement))
        return;
    const lengthValue = Number.parseInt(lengthNumberInput.value, 10);
    if (Number.isNaN(lengthValue)) {
        resistanceElement.textContent = "Selecciona una longitud válida.";
        return;
    }
    resistanceElement.textContent = calculateLengthResistance(lengthValue);
}
function updateLengthOutput(value) {
    if (lengthOutput)
        lengthOutput.textContent = value;
    updateResistance();
}
function syncLengthFromNumber() {
    if (!(lengthNumberInput instanceof HTMLInputElement) || !(lengthRangeInput instanceof HTMLInputElement))
        return;
    lengthRangeInput.value = lengthNumberInput.value;
    updateLengthOutput(lengthNumberInput.value);
}
function syncLengthFromRange() {
    if (!(lengthNumberInput instanceof HTMLInputElement) || !(lengthRangeInput instanceof HTMLInputElement))
        return;
    lengthNumberInput.value = lengthRangeInput.value;
    updateLengthOutput(lengthRangeInput.value);
}
function toggleGeneratorPanel() {
    if (!(generatorPanel instanceof HTMLElement) || !(generatorToggleButton instanceof HTMLButtonElement))
        return;
    const isHidden = generatorPanel.hasAttribute("hidden");
    if (isHidden) {
        generatorPanel.removeAttribute("hidden");
    }
    else {
        generatorPanel.setAttribute("hidden", "");
    }
    const expanded = generatorPanel.hasAttribute("hidden") ? "false" : "true";
    generatorToggleButton.setAttribute("aria-expanded", expanded);
    if (!generatorPanel.hasAttribute("hidden"))
        updateResistance();
}
function resetGeneratedPassword() {
    if (generatedPasswordContainer instanceof HTMLElement) {
        generatedPasswordContainer.setAttribute("hidden", "");
    }
    if (generatedPasswordValue instanceof HTMLElement) {
        generatedPasswordValue.textContent = "";
    }
    if (copyFeedback instanceof HTMLElement) {
        copyFeedback.textContent = "";
        copyFeedback.classList.remove("admin-error-text", "admin-success-text", "admin-info-text");
    }
}
async function handleGenerateClick() {
    if (!(lengthNumberInput instanceof HTMLInputElement) || !(generateButton instanceof HTMLButtonElement))
        return;
    const lengthValue = Number.parseInt(lengthNumberInput.value, 10);
    if (Number.isNaN(lengthValue)) {
        setGeneratorFeedback("La longitud debe ser un número natural válido.", "error");
        resetGeneratedPassword();
        return;
    }
    if (lengthValue < 16 || lengthValue > 1024) {
        setGeneratorFeedback("La longitud debe estar entre 16 y 1024 caracteres.", "error");
        resetGeneratedPassword();
        return;
    }
    const csrfToken = csrfInput instanceof HTMLInputElement ? csrfInput.value : "";
    if (!csrfToken) {
        setGeneratorFeedback("No se pudo validar la petición (token CSRF no disponible).", "error");
        resetGeneratedPassword();
        return;
    }
    try {
        generateButton.disabled = true;
        setGeneratorFeedback("Generando contraseña segura…", "info");
        const length = lengthNumberInput.value;
        const formData = new FormData();
        formData.append('length', length);
        const response = await fetch('/appventurers/admin/ajax/generate_password.php', {
            method: 'POST',
            body: formData
        });
        if (!response.ok)
            throw new Error(`Respuesta inesperada (${response.status})`);
        const data = await response.json();
        if (!data || data.success !== true) {
            const message = data && typeof data.message === "string"
                ? data.message
                : "No se pudo generar una contraseña segura.";
            setGeneratorFeedback(message, "error");
            resetGeneratedPassword();
            return;
        }
        if (!(generatedPasswordContainer instanceof HTMLElement)
            || !(generatedPasswordValue instanceof HTMLElement)) {
            return;
        }
        const password = typeof data.password === "string" ? data.password : "";
        if (!password) {
            setGeneratorFeedback("No se recibió ninguna contraseña generada.", "error");
            resetGeneratedPassword();
            return;
        }
        generatedPasswordValue.textContent = password;
        generatedPasswordContainer.removeAttribute("hidden");
        if (campoNuevaContrasena instanceof HTMLInputElement) {
            campoNuevaContrasena.value = password;
        }
        if (campoConfirmacion instanceof HTMLInputElement) {
            campoConfirmacion.value = password;
        }
        const stats = data.stats && typeof data.stats === "object" ? data.stats : {};
        if (statsElements.uppercase instanceof HTMLElement) {
            const value = Object.prototype.hasOwnProperty.call(stats, "uppercase") ? stats.uppercase : 0;
            statsElements.uppercase.textContent = String(value);
        }
        if (statsElements.lowercase instanceof HTMLElement) {
            const value = Object.prototype.hasOwnProperty.call(stats, "lowercase") ? stats.lowercase : 0;
            statsElements.lowercase.textContent = String(value);
        }
        if (statsElements.digits instanceof HTMLElement) {
            const value = Object.prototype.hasOwnProperty.call(stats, "digits") ? stats.digits : 0;
            statsElements.digits.textContent = String(value);
        }
        if (statsElements.special instanceof HTMLElement) {
            const value = Object.prototype.hasOwnProperty.call(stats, "special") ? stats.special : 0;
            statsElements.special.textContent = String(value);
        }
        if (statsElements.hashResistance instanceof HTMLElement) {
            const value = Object.prototype.hasOwnProperty.call(stats, "hashResistanceTime") ? stats.hashResistanceTime : "-";
            statsElements.hashResistance.textContent = String(value);
        }
        if (statsElements.entropy instanceof HTMLElement) {
            const value = Object.prototype.hasOwnProperty.call(stats, "entropy") ? stats.entropy : "-";
            statsElements.entropy.textContent = String(value);
        }
        setGeneratorFeedback("Contraseña generada y aplicada al formulario.", "success");
        updateResistance();
    }
    catch (error) {
        console.error("Error al generar la contraseña:", error);
        setGeneratorFeedback("No se pudo generar una contraseña segura. Inténtelo de nuevo.", "error");
        resetGeneratedPassword();
    }
    finally {
        if (generateButton instanceof HTMLButtonElement) {
            generateButton.disabled = false;
        }
    }
}
function resetCopyFeedback() {
    if (!(copyFeedback instanceof HTMLElement))
        return;
    copyFeedback.textContent = "";
    copyFeedback.classList.remove("admin-error-text", "admin-success-text", "admin-info-text");
}
function handleCopyPassword() {
    if (!(generatedPasswordValue instanceof HTMLElement) || !generatedPasswordValue.textContent)
        return;
    navigator.clipboard.writeText(generatedPasswordValue.textContent)
        .then(() => {
        if (copyFeedback instanceof HTMLElement) {
            resetCopyFeedback();
            copyFeedback.textContent = "Contraseña copiada al portapapeles.";
            copyFeedback.classList.add("admin-success-text");
        }
    })
        .catch((error) => {
        console.error("Fallo al copiar la contraseña al portapapeles:", error);
        if (copyFeedback instanceof HTMLElement) {
            resetCopyFeedback();
            copyFeedback.textContent = "No se pudo copiar la contraseña.";
            copyFeedback.classList.add("admin-error-text");
        }
    });
}
formulario.addEventListener("submit", (event) => {
    const contrasenaActual = campoContrasenaActual.value.trim();
    const contrasenaActualOriginal = campoContrasenaActual.value;
    const nuevaContrasena = campoNuevaContrasena.value.trim();
    const nuevaContrasenaOriginal = campoNuevaContrasena.value;
    const confirmacion = campoConfirmacion.value.trim();
    const confirmacionOriginal = campoConfirmacion.value;
    if (typeof contrasenaActual !== "string"
        || typeof nuevaContrasena !== "string"
        || typeof confirmacion !== "string") {
        event.preventDefault();
        alert("Error en los datos introducidos.");
        return;
    }
    if (contrasenaActualOriginal !== contrasenaActual
        || nuevaContrasenaOriginal !== nuevaContrasena
        || confirmacionOriginal !== confirmacion) {
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
    const longitudInvalida = (valor) => valor.length < 16 || valor.length > 1024;
    if (longitudInvalida(contrasenaActual)
        || longitudInvalida(nuevaContrasena)
        || longitudInvalida(confirmacion)) {
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
        }
        else if (regexMayusculas.test(caracter)) {
            numeroMayusculas += 1;
        }
        else if (regexMinusculas.test(caracter)) {
            numeroMinusculas += 1;
        }
        else if (regexNumeros.test(caracter)) {
            numeroNumeros += 1;
        }
    }
    if (numeroMayusculas < 1
        || numeroMinusculas < 1
        || numeroNumeros < 1
        || numeroCaracteresEspeciales < 3) {
        event.preventDefault();
        alert("La nueva contraseña debe tener al menos una letra mayúscula, una letra minúscula, un número y tres caracteres especiales distintos.");
        return;
    }
    if (tieneSecuenciasAlfabeticasInseguras(nuevaContrasena)
        || tieneSecuenciasDeCaracteresEspecialesInseguras(nuevaContrasena)
        || tieneSecuenciasNumericasInseguras(nuevaContrasena)) {
        event.preventDefault();
        alert('La nueva contraseña no puede contener secuencias alfabéticas, de caracteres especiales o numéricas inseguras como "abc", "qwerty", "qaz", "123", "147", "159"');
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
if (generatorToggleButton instanceof HTMLButtonElement) {
    generatorToggleButton.addEventListener("click", toggleGeneratorPanel);
}
if (lengthNumberInput instanceof HTMLInputElement) {
    lengthNumberInput.addEventListener("input", syncLengthFromNumber);
}
if (lengthRangeInput instanceof HTMLInputElement) {
    lengthRangeInput.addEventListener("input", syncLengthFromRange);
}
if (generateButton instanceof HTMLButtonElement) {
    generateButton.addEventListener("click", handleGenerateClick);
}
if (copyButton instanceof HTMLButtonElement) {
    copyButton.addEventListener("click", handleCopyPassword);
}
if (lengthNumberInput instanceof HTMLInputElement) {
    updateLengthOutput(lengthNumberInput.value);
}
