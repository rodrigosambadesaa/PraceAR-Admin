(function () {
  const STORAGE_KEY = "dark-mode";
  const LEGACY_STORAGE_KEY = "darkmode";

  const darkModeIcon = document.getElementById("darkmode-icon") as
    | HTMLElement
    | null;
  const toggleDarkModeButton = document.getElementById("toggle-darkmode") as
    | HTMLElement
    | null;
  const darkMediaQuery = window.matchMedia("(prefers-color-scheme: dark)");

  function setDarkMode(on: boolean): void {
    document.body.classList.toggle("dark-mode", on);
    if (darkModeIcon) {
      darkModeIcon.textContent = on ? "☀️" : "🌙";
    }
  }

  function readStoredPreference(): boolean | null {
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
