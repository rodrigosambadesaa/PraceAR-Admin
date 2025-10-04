"use strict";
(function () {
    if (window.__adminDarkModeInitialized) {
        return;
    }
    window.__adminDarkModeInitialized = true;
    // Helper to safely get an element by ID and assert its type
    function getElementByIdOrThrow(id) {
        const el = document.getElementById(id);
        if (!el)
            throw new Error(`Element with id "${id}" not found`);
        return el;
    }
    function setDarkMode(on) {
        document.body.classList.toggle('dark-mode', on);
        const icon = getElementByIdOrThrow('darkmode-icon');
        icon.textContent = on ? '☀️' : '🌙';
    }
    const darkPref = localStorage.getItem('dark-mode');
    if (darkPref === null) {
        // Auto by hour: dark from 19h to 7h
        const hour = new Date().getHours();
        setDarkMode(hour >= 19 || hour < 7);
    }
    else {
        setDarkMode(darkPref === 'true');
    }
    const toggleBtn = getElementByIdOrThrow('toggle-darkmode');
    toggleBtn.addEventListener('click', () => {
        const isDark = !document.body.classList.contains('dark-mode');
        setDarkMode(isDark);
        localStorage.setItem('dark-mode', String(isDark));
    });
})();
