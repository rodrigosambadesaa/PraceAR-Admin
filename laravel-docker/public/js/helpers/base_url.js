export function getBaseUrl() {
    const configured = document.body?.dataset.baseUrl;
    const base = configured && configured.trim() !== "" ? configured : "/";
    return new URL(base, window.location.origin).toString();
}
export function resolveAppUrl(path) {
    return new URL(path, getBaseUrl()).toString();
}
