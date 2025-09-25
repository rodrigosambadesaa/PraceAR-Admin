function sanitizeText(value: string): string {
    const trimmed = value.trim();
    const withoutBackslashes = trimmed.replace(/\\/g, "");
    const escapedHtml = withoutBackslashes
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    const withoutHtmlTags = escapedHtml.replace(/<\/?[^>]+(>|$)/g, "");
    return unescape(encodeURIComponent(withoutHtmlTags));
}

export function limpiarInput(input: string): string {
    return sanitizeText(input);
}

export function limpiarTextarea(textarea: string): string {
    return sanitizeText(textarea);
}
