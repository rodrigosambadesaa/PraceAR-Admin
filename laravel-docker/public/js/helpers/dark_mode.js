"use strict";
(function () {
    const STORAGE_KEY = "dark-mode";
    const LEGACY_STORAGE_KEY = "darkmode";
    const COOKIE_KEY = "dark_mode";
    const storage = (() => {
        try {
            return window.localStorage;
        }
        catch {
            return null;
        }
    })();
    const darkModeIcon = document.getElementById("darkmode-icon");
    const toggleDarkModeButton = document.getElementById("toggle-darkmode");
    const darkMediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
    function escapeRegExp(value) {
        return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    }
    function getCookieValue(name) {
        const match = document.cookie.match(new RegExp("(?:^|; )" + escapeRegExp(name) + "=([^;]*)"));
        return match ? decodeURIComponent(match[1]) : null;
    }
    function setCookieValue(name, value) {
        document.cookie =
            `${name}=${encodeURIComponent(value)}; Path=/; Max-Age=31536000; SameSite=Lax`;
    }
    function getStorageValue(key) {
        return storage?.getItem(key) ?? null;
    }
    function setStorageValue(key, value) {
        storage?.setItem(key, value);
    }
    function removeStorageValue(key) {
        storage?.removeItem(key);
    }
    function setDarkMode(on) {
        document.documentElement.classList.toggle("dark-mode", on);
        document.documentElement.style.colorScheme = on ? "dark" : "light";
        document.body.classList.toggle("dark-mode", on);
        if (toggleDarkModeButton) {
            toggleDarkModeButton.setAttribute("aria-pressed", on ? "true" : "false");
        }
        if (darkModeIcon) {
            darkModeIcon.textContent = on ? "☀️" : "🌙";
        }
    }
    function readStoredPreference() {
        const cookieValue = getCookieValue(COOKIE_KEY);
        if (cookieValue === "true") {
            setStorageValue(STORAGE_KEY, "true");
            removeStorageValue(LEGACY_STORAGE_KEY);
            return true;
        }
        if (cookieValue === "false") {
            setStorageValue(STORAGE_KEY, "false");
            removeStorageValue(LEGACY_STORAGE_KEY);
            return false;
        }
        const value = getStorageValue(STORAGE_KEY) ?? getStorageValue(LEGACY_STORAGE_KEY);
        if (value === "true") {
            return true;
        }
        if (value === "false") {
            return false;
        }
        return null;
    }
    function resolvePreferredDarkMode() {
        const storedPreference = readStoredPreference();
        if (storedPreference !== null) {
            return storedPreference;
        }
        const systemPreference = darkMediaQuery.matches;
        persistPreference(systemPreference);
        return systemPreference;
    }
    function applyDarkModePreference() {
        setDarkMode(resolvePreferredDarkMode());
    }
    function persistPreference(isDarkModeOn) {
        setStorageValue(STORAGE_KEY, String(isDarkModeOn));
        removeStorageValue(LEGACY_STORAGE_KEY);
        setCookieValue(COOKIE_KEY, String(isDarkModeOn));
    }
    applyDarkModePreference();
    if (toggleDarkModeButton) {
        toggleDarkModeButton.addEventListener("click", () => {
            const isDarkModeOn = !document.body.classList.contains("dark-mode");
            setDarkMode(isDarkModeOn);
            persistPreference(isDarkModeOn);
        });
    }
    const onSystemThemeChange = (event) => {
        if (readStoredPreference() === null) {
            setDarkMode(event.matches);
        }
    };
    if (typeof darkMediaQuery.addEventListener === "function") {
        darkMediaQuery.addEventListener("change", onSystemThemeChange);
    }
    else {
        darkMediaQuery.addListener(onSystemThemeChange);
    }
    window.addEventListener("storage", (event) => {
        if (event.key === STORAGE_KEY || event.key === LEGACY_STORAGE_KEY) {
            applyDarkModePreference();
        }
    });
})();
