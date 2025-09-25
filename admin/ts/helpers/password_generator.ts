import { limpiarInput } from "../../../../js/helpers/clean_input.js";

declare global {
    interface Window {
        syncInputs: (inputType: "number" | "range") => void;
        copyToClipboard: (passwordId: string) => Promise<void>;
    }
}

function getForm(): HTMLFormElement {
    const form = document.getElementById("formulario-generacion-contrasena");

    if (!(form instanceof HTMLFormElement)) {
        throw new Error("No se encontró el formulario de generación de contraseñas.");
    }

    return form;
}

function getNumberInput(): HTMLInputElement {
    const input = document.getElementById("length-number");

    if (!(input instanceof HTMLInputElement)) {
        throw new Error("No se encontró el campo numérico de longitud.");
    }

    return input;
}

function getRangeInput(): HTMLInputElement {
    const input = document.getElementById("length-range");

    if (!(input instanceof HTMLInputElement)) {
        throw new Error("No se encontró el control deslizante de longitud.");
    }

    return input;
}

function getOutputElement(): HTMLOutputElement {
    const output = document.getElementById("length-output");

    if (!(output instanceof HTMLOutputElement)) {
        throw new Error("No se encontró el elemento de salida de longitud.");
    }

    return output;
}

const formulario = getForm();
const numberInput = getNumberInput();
const rangeInput = getRangeInput();
const output = getOutputElement();

function actualizarSalida(): void {
    output.textContent = rangeInput.value;
}

window.syncInputs = (inputType: "number" | "range") => {
    if (inputType === "number") {
        rangeInput.value = numberInput.value;
    } else {
        numberInput.value = rangeInput.value;
    }

    actualizarSalida();
};

window.copyToClipboard = async (passwordId: string) => {
    const passwordText = document.getElementById(passwordId);

    if (!(passwordText instanceof HTMLElement)) {
        throw new Error(`No se encontró el elemento con id "${passwordId}".`);
    }

    const text = passwordText.innerText;

    try {
        await navigator.clipboard.writeText(text);
        alert("Contraseña copiada al portapapeles.");
    } catch (error) {
        console.error("Fallo al copiar la contraseña al portapapeles:", error);
    }
};

formulario.addEventListener("submit", (event: SubmitEvent) => {
    const longitudSanitizada = limpiarInput(numberInput.value);
    const longitudNumero = Number.parseInt(longitudSanitizada, 10);
    const longitudRango = Number.parseInt(rangeInput.value, 10);

    if (
        Number.isNaN(longitudNumero)
        || Number.isNaN(longitudRango)
        || longitudNumero < 16
        || longitudNumero > 500
        || longitudRango < 16
        || longitudRango > 500
        || longitudNumero !== longitudRango
    ) {
        event.preventDefault();
        alert("La longitud debe ser un número natural entre 16 y 500");
    }
});

actualizarSalida();

export {};
