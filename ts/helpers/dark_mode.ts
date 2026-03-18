(function () {
  const STORAGE_KEY = "dark-mode";
  const LEGACY_STORAGE_KEY = "darkmode";
  const COOKIE_KEY = "dark_mode";

  const darkModeIcon = document.getElementById("darkmode-icon") as
    | HTMLElement
    | null;
  const toggleDarkModeButton = document.getElementById("toggle-darkmode") as
    | HTMLElement
    | null;
  const darkMediaQuery = window.matchMedia("(prefers-color-scheme: dark)");

  function getCookieValue(name: string): string | null {
    const match = document.cookie.match(
      new RegExp("(?:^|; )" + name.replace(/[.$?*|{}()\\[\\]\\/+^]/g, "\\$&") + "=([^;]*)")
    );
    return match ? decodeURIComponent(match[1]) : null;
  }

  function setCookieValue(name: string, value: string): void {
    document.cookie =
      `${name}=${encodeURIComponent(value)}; Path=/; Max-Age=31536000; SameSite=Lax`;
  }

  function setDarkMode(on: boolean): void {
    document.body.classList.toggle("dark-mode", on);
    if (darkModeIcon) {
      darkModeIcon.textContent = on ? "☀️" : "🌙";
    }
  }

  function readStoredPreference(): boolean | null {
    const cookieValue = getCookieValue(COOKIE_KEY);
    if (cookieValue === "true") {
      localStorage.setItem(STORAGE_KEY, "true");
      localStorage.removeItem(LEGACY_STORAGE_KEY);
      return true;
    }
    if (cookieValue === "false") {
      localStorage.setItem(STORAGE_KEY, "false");
      localStorage.removeItem(LEGACY_STORAGE_KEY);
      return false;
    }

    const value =
      localStorage.getItem(STORAGE_KEY) ??
      localStorage.getItem(LEGACY_STORAGE_KEY);

    if (value === "true") {
      return true;
    }
    if (value === "false") {
      return false;
    }
    return null;
  }

  function applyDarkModePreference(): void {
    const storedPreference = readStoredPreference();
    if (storedPreference === null) {
      setDarkMode(darkMediaQuery.matches);
      return;
    }
    setDarkMode(storedPreference);
  }

  function persistPreference(isDarkModeOn: boolean): void {
    localStorage.setItem(STORAGE_KEY, String(isDarkModeOn));
    localStorage.removeItem(LEGACY_STORAGE_KEY);
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
