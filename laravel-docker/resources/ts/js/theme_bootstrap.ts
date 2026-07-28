(() => {
    const storageKey = "dark-mode";
    const legacyStorageKey = "darkmode";
    const cookieKey = "dark_mode";

    function escapeRegExp(value: string): string {
        return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    }

    function getCookieValue(name: string): string | null {
        const match = document.cookie.match(new RegExp(`(?:^|; )${escapeRegExp(name)}=([^;]*)`));

        return match?.[1] ? decodeURIComponent(match[1]) : null;
    }

    function getStorageValue(key: string): string | null {
        try {
            return window.localStorage.getItem(key);
        } catch {
            return null;
        }
    }

    function setStorageValue(key: string, value: string): void {
        try {
            window.localStorage.setItem(key, value);
        } catch {
            // El modo visual sigue funcionando aunque el navegador bloquee storage.
        }
    }

    function removeStorageValue(key: string): void {
        try {
            window.localStorage.removeItem(key);
        } catch {
            // El modo visual sigue funcionando aunque el navegador bloquee storage.
        }
    }

    function persistPreference(value: boolean): void {
        const serialized = String(value);
        setStorageValue(storageKey, serialized);
        removeStorageValue(legacyStorageKey);
        document.cookie = `${cookieKey}=${encodeURIComponent(serialized)}; Path=/; Max-Age=31536000; SameSite=Lax`;
    }

    function readStoredPreference(): boolean | null {
        const cookieValue = getCookieValue(cookieKey);
        if (cookieValue === "true" || cookieValue === "false") {
            setStorageValue(storageKey, cookieValue);
            removeStorageValue(legacyStorageKey);

            return cookieValue === "true";
        }

        const storageValue = getStorageValue(storageKey) ?? getStorageValue(legacyStorageKey);
        if (storageValue === "true" || storageValue === "false") {
            return storageValue === "true";
        }

        return null;
    }

    let isDark = readStoredPreference();
    if (isDark === null) {
        isDark = window.matchMedia?.("(prefers-color-scheme: dark)").matches ?? false;
        persistPreference(isDark);
    }

    document.documentElement.classList.toggle("dark-mode", isDark);
    document.documentElement.style.colorScheme = isDark ? "dark" : "light";
    document.body.classList.toggle("dark-mode", isDark);
})();
