import { limpiarTextarea } from "../../js/helpers/clean_input.js";

function getForm(): HTMLFormElement {
    const form = document.getElementById("formulario");

    if (!(form instanceof HTMLFormElement)) {
        throw new Error("No se encontró el formulario de traducciones.");
    }

    return form;
}

function getDescripcionTextarea(): HTMLTextAreaElement {
    const textarea = document.getElementById("descripcion");

    if (!(textarea instanceof HTMLTextAreaElement)) {
        throw new Error("No se encontró el campo de descripción.");
    }

    return textarea;
}

function getTipoInput(): HTMLInputElement {
    const input = document.getElementById("tipo");

    if (!(input instanceof HTMLInputElement)) {
        throw new Error("No se encontró el campo de tipo.");
    }

    return input;
}

const formulario = getForm();
const descripcionTextarea = getDescripcionTextarea();
const tipoInput = getTipoInput();

formulario.addEventListener("submit", (event: SubmitEvent) => {
    let errorMessages = "";
    let errorExist = false;

    const descripcion = limpiarTextarea(descripcionTextarea.value);
    const tipo = tipoInput.value;

    if (descripcion !== "" && descripcion.length > 450) {
        event.preventDefault();
        alert("La descripción, si se ha introducido, debe tener un máximo de 450 caracteres");
        errorMessages += "<li>La descripción, si se ha introducido, debe tener un máximo de 450 caracteres</li>";
        errorExist = true;
    }

    if (tipo.length > 50) {
        event.preventDefault();
        alert("El tipo no puede tener más de 50 caracteres");
        errorMessages += "<li>El tipo no puede tener más de 50 caracteres</li>";
        errorExist = true;
    }

    if (errorExist) {
        event.preventDefault();
        const div = document.createElement("div");
        div.innerHTML = `<ul class="admin-error-text">${errorMessages}</ul>`;
        formulario.insertAdjacentElement("afterend", div);
    }
});

// Cuando cargue la página, meter todas las etiquetas style en el head en una sola
window.addEventListener("load", () => {
    const styles = Array.from(document.querySelectorAll("style"));
    const head = document.head;
    if (styles.length > 0) {
        const combinedStyle = document.createElement("style");
        combinedStyle.textContent = styles.map(style => style.textContent).join("\n");
        head.appendChild(combinedStyle);
        styles.forEach(style => style.remove());
    }

});

export { };