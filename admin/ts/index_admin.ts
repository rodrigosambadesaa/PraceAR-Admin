function getFormElement(): HTMLFormElement {
    const form = document.getElementById("formulario-busqueda");

    if (!(form instanceof HTMLFormElement)) {
        throw new Error("No se encontró el formulario de búsqueda de puestos.");
    }

    return form;
}

function getSearchInput(): HTMLInputElement {
    const input = document.getElementById("input-busqueda");

    if (!(input instanceof HTMLInputElement)) {
        throw new Error("No se encontró el campo de búsqueda de puestos.");
    }

    return input;
}

const formulario = getFormElement();
const inputBusqueda = getSearchInput();

formulario.addEventListener("submit", (event: SubmitEvent) => {
    const busqueda = inputBusqueda.value.trim();

    if (busqueda && /[<>"'%]/.test(busqueda)) {
        event.preventDefault();
        alert("Por favor, elimine los caracteres no permitidos, que son: <, >, \", ', %");
    }
});

console.log("Código TypeScript de la página de administración de puestos cargado correctamente.");

export { };

