document.getElementById("formulario-busqueda").addEventListener("submit", function (event) {
    const busqueda = document.getElementById("input-busqueda").value.trim();

    // Si el campo no está vacío, verifica que no contiene caracteres peligrosos
    if (busqueda && /[<>\"\'%]/.test(busqueda)) {
        alert("Por favor, elimine los caracteres no permitidos, que son: <, >, \", ', %");
        event.preventDefault(); // Previene el envío del formulario
    }
});
