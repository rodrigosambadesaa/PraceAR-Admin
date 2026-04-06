(function () {
  const STORAGE_KEY = "dark-mode";
  const LEGACY_STORAGE_KEY = "darkmode";
  const COOKIE_KEY = "dark_mode";
  const storage = (() => {
    try {
      return window.localStorage;
    } catch {
      return null;
    }
  })();

  const darkModeIcon = document.getElementById("darkmode-icon") as
    | HTMLElement
    | null;
  const toggleDarkModeButton = document.getElementById("toggle-darkmode") as
    | HTMLElement
    | null;
  const darkMediaQuery = window.matchMedia("(prefers-color-scheme: dark)");

  function escapeRegExp(value: string): string {
    return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  function getCookieValue(name: string): string | null {
    const match = document.cookie.match(
      new RegExp("(?:^|; )" + escapeRegExp(name) + "=([^;]*)")
    );
    return match ? decodeURIComponent(match[1]) : null;
  }

  function setCookieValue(name: string, value: string): void {
    document.cookie =
      `${name}=${encodeURIComponent(value)}; Path=/; Max-Age=31536000; SameSite=Lax`;
  }

  function getStorageValue(key: string): string | null {
    return storage?.getItem(key) ?? null;
  }

  function setStorageValue(key: string, value: string): void {
    storage?.setItem(key, value);
  }

  function removeStorageValue(key: string): void {
    storage?.removeItem(key);
  }

  function setDarkMode(on: boolean): void {
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

  function readStoredPreference(): boolean | null {
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

  function resolvePreferredDarkMode(): boolean {
    const storedPreference = readStoredPreference();
    if (storedPreference !== null) {
      return storedPreference;
    }

    const systemPreference = darkMediaQuery.matches;
    persistPreference(systemPreference);
    return systemPreference;
  }

  function applyDarkModePreference(): void {
    setDarkMode(resolvePreferredDarkMode());
  }

  function persistPreference(isDarkModeOn: boolean): void {
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

  const onSystemThemeChange = (event: MediaQueryListEvent): void => {
    if (readStoredPreference() === null) {
      setDarkMode(event.matches);
    }
  };

  if (typeof darkMediaQuery.addEventListener === "function") {
    darkMediaQuery.addEventListener("change", onSystemThemeChange);
  } else {
    darkMediaQuery.addListener(onSystemThemeChange);
  }

  window.addEventListener("storage", (event: StorageEvent) => {
    if (event.key === STORAGE_KEY || event.key === LEGACY_STORAGE_KEY) {
      applyDarkModePreference();
    }
  });
})();
