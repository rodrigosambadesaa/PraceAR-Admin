export function getBaseUrl(): string {
    const configured = document.body?.dataset.baseUrl;
    const base = configured && configured.trim() !== "" ? configured : "/";

    return new URL(base, window.location.origin).toString();
}

export function resolveAppUrl(path: string): string {
    return new URL(path, getBaseUrl()).toString();
}
