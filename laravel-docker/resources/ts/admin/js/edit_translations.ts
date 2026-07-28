import { limpiarTextarea } from "../../js/helpers/clean_input.js";
function getForm() {
    const form = document.getElementById("formulario");
    if (!(form instanceof HTMLFormElement)) {
        throw new Error("No se encontró el formulario de traducciones.");
    }
    return form;
}
function getDescripcionTextarea() {
    const textarea = document.getElementById("descripcion");
    if (!(textarea instanceof HTMLTextAreaElement)) {
        throw new Error("No se encontró el campo de descripción.");
    }
    return textarea;
}
function getTipoInput() {
    const input = document.getElementById("tipo");
    if (!(input instanceof HTMLInputElement)) {
        throw new Error("No se encontró el campo de tipo.");
    }
    return input;
}
const formulario = getForm();
const descripcionTextarea = getDescripcionTextarea();
const tipoInput = getTipoInput();
formulario.addEventListener("submit", (event) => {
    const errorMessages = [];
    let errorExist = false;
    const descripcion = limpiarTextarea(descripcionTextarea.value);
    const tipo = tipoInput.value;
    if (descripcion !== "" && descripcion.length > 450) {
        event.preventDefault();
        alert("La descripción, si se ha introducido, debe tener un máximo de 450 caracteres");
        errorMessages.push("La descripción, si se ha introducido, debe tener un máximo de 450 caracteres");
        errorExist = true;
    }
    if (tipo.length > 50) {
        event.preventDefault();
        alert("El tipo no puede tener más de 50 caracteres");
        errorMessages.push("El tipo no puede tener más de 50 caracteres");
        errorExist = true;
    }
    if (errorExist) {
        event.preventDefault();
        const div = document.createElement("div");
        const list = document.createElement("ul");
        list.className = "admin-error-text";
        errorMessages.forEach((message) => {
            const item = document.createElement("li");
            item.textContent = message;
            list.appendChild(item);
        });
        div.appendChild(list);
        formulario.insertAdjacentElement("afterend", div);
    }
});
// Cuando cargue la página, meter todas las etiquetas style en el head en una sola
window.addEventListener("load", () => {
    const styles = Array.from(document.querySelectorAll("style"));
    const head = document.head;
    if (styles.length > 0) {
        const combinedStyle = document.createElement("style");
        combinedStyle.textContent = styles
            .map((style) => style.textContent)
            .join("\n");
        head.appendChild(combinedStyle);
        styles.forEach((style) => style.remove());
    }
});
