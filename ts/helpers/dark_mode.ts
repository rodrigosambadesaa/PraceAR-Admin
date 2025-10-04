(function () {
    // Helper to safely get an element by ID and assert its type
    function getElementByIdOrThrow<T extends HTMLElement>(id: string): T {
        const el = document.getElementById(id);
        if (!el) throw new Error(`Element with id "${id}" not found`);
        return el as T;
    }

    function setDarkMode(on: boolean): void {
        document.body.classList.toggle('dark-mode', on);
        const icon = getElementByIdOrThrow<HTMLElement>('darkmode-icon');
        icon.textContent = on ? '☀️' : '🌙';
    }

    const darkPref = localStorage.getItem('dark-mode');
    if (darkPref === null) {
        // Auto by hour: dark from 19h to 7h
        const hour = new Date().getHours();
        setDarkMode(hour >= 19 || hour < 7);
    } else {
        setDarkMode(darkPref === 'true');
    }

    const toggleBtn = getElementByIdOrThrow<HTMLElement>('toggle-darkmode');
    toggleBtn.addEventListener('click', () => {
        const isDark = !document.body.classList.contains('dark-mode');
        setDarkMode(isDark);
        localStorage.setItem('dark-mode', String(isDark));
    });
})();