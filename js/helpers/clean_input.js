function cleanInput(input) {
    // Eliminar espacios innecesarios al inicio y final del input
    input = input.trim();

    // Quitar barras invertidas (previene escape no deseado)
    input = input.replace(/\\/g, '');

    // Convertir caracteres especiales en entidades HTML (previene XSS)
    input = input
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

    // Eliminar etiquetas HTML (adicional para prevenir XSS)
    input = input.replace(/<\/?[^>]+(>|$)/g, "");

    // Codificar caracteres UTF-8 para prevenir caracteres no deseados
    input = unescape(encodeURIComponent(input));

    return input;
}

function limpiarTextarea(textarea) {
    // Eliminar espacios innecesarios al inicio y final del textarea
    textarea = textarea.trim();

    // Quitar barras invertidas (previene escape no deseado)
    textarea = textarea.replace(/\\/g, '');

    // Convertir caracteres especiales en entidades HTML (previene XSS)
    textarea = textarea
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

    // Eliminar etiquetas HTML (adicional para prevenir XSS)
    textarea = textarea.replace(/<\/?[^>]+(>|$)/g, "");

    // Codificar caracteres UTF-8 para prevenir caracteres no deseados
    textarea = unescape(encodeURIComponent(textarea));

    return textarea;
}

export { cleanInput as limpiarInput, limpiarTextarea };
