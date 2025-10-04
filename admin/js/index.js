function getFormElement() {
    const form = document.getElementById("formulario-busqueda");
    if (!(form instanceof HTMLFormElement)) {
        throw new Error("No se encontró el formulario de búsqueda de puestos.");
    }
    return form;
}
function getSearchInput() {
    const input = document.getElementById("input-busqueda");
    if (!(input instanceof HTMLInputElement)) {
        throw new Error("No se encontró el campo de búsqueda de puestos.");
    }
    return input;
}
const formulario = getFormElement();
const inputBusqueda = getSearchInput();
formulario.addEventListener("submit", (event) => {
    const busqueda = inputBusqueda.value.trim();
    if (busqueda && /[<>"'%]/.test(busqueda)) {
        event.preventDefault();
        alert("Por favor, elimine los caracteres no permitidos, que son: <, >, \", ', %");
    }
});
console.log("Código TypeScript de la página de administración de puestos cargado correctamente.");
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
export {};
