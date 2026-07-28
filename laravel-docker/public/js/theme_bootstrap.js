"use strict";
(() => {
    const storageKey = "dark-mode";
    const legacyStorageKey = "darkmode";
    const cookieKey = "dark_mode";
    function escapeRegExp(value) {
        return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    }
    function getCookieValue(name) {
        const match = document.cookie.match(new RegExp(`(?:^|; )${escapeRegExp(name)}=([^;]*)`));
        return match?.[1] ? decodeURIComponent(match[1]) : null;
    }
    function getStorageValue(key) {
        try {
            return window.localStorage.getItem(key);
        }
        catch {
            return null;
        }
    }
    function setStorageValue(key, value) {
        try {
            window.localStorage.setItem(key, value);
        }
        catch {
            // El modo visual sigue funcionando aunque el navegador bloquee storage.
        }
    }
    function removeStorageValue(key) {
        try {
            window.localStorage.removeItem(key);
        }
        catch {
            // El modo visual sigue funcionando aunque el navegador bloquee storage.
        }
    }
    function persistPreference(value) {
        const serialized = String(value);
        setStorageValue(storageKey, serialized);
        removeStorageValue(legacyStorageKey);
        document.cookie = `${cookieKey}=${encodeURIComponent(serialized)}; Path=/; Max-Age=31536000; SameSite=Lax`;
    }
    function readStoredPreference() {
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
