const form = document.getElementById("formulario-generacion-contrasena");
const numberInput = document.getElementById("length-number");
const rangeInput = document.getElementById("length-range");
const outputElement = document.getElementById("length-output");
const requiredFieldsParagraph = document.getElementById("parrafo-campos-obligatorios");
const passwordsContainer = document.getElementById("contrasenas-generadas");

function isHTMLInputElement(element: Element | null): element is HTMLInputElement {
    return element instanceof HTMLInputElement;
}

function isHTMLFormElement(element: Element | null): element is HTMLFormElement {
    return element instanceof HTMLFormElement;
}

function isHTMLElement(element: Element | null): element is HTMLElement {
    return element instanceof HTMLElement;
}

function getResistanceElement(): HTMLDivElement {
    const existingElement = document.getElementById("resistencia-output");

    if (existingElement instanceof HTMLDivElement) {
        return existingElement;
    }

    const newElement = document.createElement("div");
    newElement.id = "resistencia-output";
    newElement.className = "password-strength";
    return newElement;
}

function calculateResistance(length: number): string {
    if (length >= 16 && length <= 20) {
        return "Resistente a atacantes individuales.";
    }

    if (length > 20 && length <= 30) {
        return "Resistente a grupos pequeños de atacantes.";
    }

    if (length > 30 && length <= 50) {
        return "Resistente a empresas con recursos moderados.";
    }

    if (length > 50 && length <= 70) {
        return "Resistente a grandes empresas.";
    }

    if (length > 70) {
        return "Resistente a gobiernos y organizaciones con recursos avanzados.";
    }

    return "Longitud insuficiente para garantizar resistencia.";
}

function updateResistance(): void {
    if (!isHTMLInputElement(numberInput) || !outputElement) {
        return;
    }

    const resistanceElement = getResistanceElement();
    const parsedLength = Number.parseInt(numberInput.value, 10);

    if (outputElement.parentElement && !resistanceElement.parentElement) {
        outputElement.insertAdjacentElement("afterend", resistanceElement);
    }

    if (Number.isNaN(parsedLength)) {
        resistanceElement.textContent = "Introduce una longitud válida.";
        return;
    }

    resistanceElement.textContent = calculateResistance(parsedLength);
}

function updateResistanceAfterPasswords(): void {
    if (!passwordsContainer) {
        return;
    }

    const resistanceElement = getResistanceElement();
    const parsedLength = isHTMLInputElement(numberInput)
        ? Number.parseInt(numberInput.value, 10)
        : Number.NaN;

    resistanceElement.textContent = Number.isNaN(parsedLength)
        ? "Introduce una longitud válida."
        : calculateResistance(parsedLength);

    passwordsContainer.insertAdjacentElement("afterend", resistanceElement);
}

function updateOutput(): void {
    if (!isHTMLInputElement(rangeInput) || !outputElement) {
        return;
    }

    outputElement.textContent = rangeInput.value;
    updateResistance();
}

function hideFormIfPasswordsGenerated(): void {
    if (!passwordsContainer || !isHTMLFormElement(form)) {
        return;
    }

    form.style.display = "none";
    if (isHTMLElement(requiredFieldsParagraph)) {
        requiredFieldsParagraph.style.display = "none";
    }

    updateResistanceAfterPasswords();
}

function syncInputsFromNumber(): void {
    if (!isHTMLInputElement(numberInput) || !isHTMLInputElement(rangeInput)) {
        return;
    }

    rangeInput.value = numberInput.value;
    updateOutput();
}

function syncInputsFromRange(): void {
    if (!isHTMLInputElement(numberInput) || !isHTMLInputElement(rangeInput)) {
        return;
    }

    numberInput.value = rangeInput.value;
    updateOutput();
}

function validateLengthValues(): boolean {
    if (!isHTMLInputElement(numberInput) || !isHTMLInputElement(rangeInput)) {
        return false;
    }

    const numberValue = Number.parseInt(numberInput.value, 10);
    const rangeValue = Number.parseInt(rangeInput.value, 10);

    if (
        Number.isNaN(numberValue) ||
        Number.isNaN(rangeValue) ||
        numberValue < 16 ||
        numberValue > 500 ||
        rangeValue < 16 ||
        rangeValue > 500 ||
        numberValue !== rangeValue
    ) {
        alert("La longitud de la contraseña debe estar entre 16 y 500 caracteres.");
        return false;
    }

    return true;
}

function handleFormSubmission(event: SubmitEvent): void {
    if (!validateLengthValues()) {
        event.preventDefault();
    }
}

function removeExistingFeedback(): void {
    const feedbackMessages = document.querySelectorAll<HTMLElement>(".copy-feedback");
    feedbackMessages.forEach(message => {
        message.remove();
    });
}

function handleCopyButtonClick(event: MouseEvent): void {
    if (!(event.currentTarget instanceof HTMLButtonElement)) {
        return;
    }

    const { passwordId } = event.currentTarget.dataset;

    if (!passwordId) {
        return;
    }

    const passwordElement = document.getElementById(passwordId);

    if (!isHTMLElement(passwordElement) || !passwordElement.textContent) {
        return;
    }

    navigator.clipboard
        .writeText(passwordElement.textContent)
        .then(() => {
            removeExistingFeedback();

            const successMessage = document.createElement("span");
            successMessage.id = "success-message";
            successMessage.textContent =
                "Contraseña copiada al portapapeles. Se borrará automáticamente en 5 minutos.";
            successMessage.classList.add("admin-success-text", "copy-feedback");
            passwordElement.insertAdjacentElement("afterend", successMessage);

            window.setTimeout(() => {
                const latestPasswordElement = document.getElementById(passwordId);
                if (!isHTMLElement(latestPasswordElement)) {
                    return;
                }

                latestPasswordElement.textContent = "Contraseña borrada por seguridad.";

                removeExistingFeedback();
                const clearMessage = document.createElement("span");
                clearMessage.textContent = "La contraseña ha sido borrada automáticamente.";
                clearMessage.classList.add("admin-error-text", "copy-feedback");
                latestPasswordElement.insertAdjacentElement("afterend", clearMessage);
            }, 5 * 60 * 1000);
        })
        .catch(error => {
            console.error("Fallo al copiar la contraseña al portapapeles:", error);
        });
}

function initializeCopyButtons(): void {
    const buttons = document.querySelectorAll<HTMLButtonElement>(".copy-password-button");
    buttons.forEach(button => {
        button.addEventListener("click", handleCopyButtonClick);
    });
}

if (isHTMLInputElement(numberInput) && isHTMLInputElement(rangeInput)) {
    numberInput.addEventListener("input", syncInputsFromNumber);
    rangeInput.addEventListener("input", syncInputsFromRange);
}

if (isHTMLFormElement(form)) {
    form.addEventListener("submit", handleFormSubmission);
}

initializeCopyButtons();
updateOutput();
hideFormIfPasswordsGenerated();
updateResistance();
updateResistanceAfterPasswords();

export { };
