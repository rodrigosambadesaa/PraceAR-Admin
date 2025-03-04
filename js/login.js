document.getElementById('formulario').addEventListener('submit', function (e) {
    const login = document.getElementById('login').value.trim();
    const password = document.getElementById('password').value.trim();
    const passwordOriginal = document.getElementById('password').value;

    // El login y la contraseña deben ser strings
    if (typeof login !== 'string' || typeof password !== 'string') {
        e.preventDefault();
        alert('Error en el formulario.');
        return;
    }

    // Verificar que la contraseña original no contiene espacios al principio o al final
    if (passwordOriginal !== passwordOriginal.trim()) {
        e.preventDefault();
        alert('La contraseña no puede contener espacios al principio o al final.');
        return;
    }

    // Expresión regular para validar el login
    const loginRegex = /^[a-zA-Z0-9._-]{3,50}$/;

    // Validar login: no vacío, longitud y caracteres válidos
    if (!login) {
        e.preventDefault();
        alert('El campo de usuario no puede estar vacío.');
        return;
    }

    if (!loginRegex.test(login)) {
        e.preventDefault();
        alert('El nombre de usuario debe tener entre 3 y 50 caracteres y solo puede contener letras, números, puntos, guiones y guiones bajos.');
        return;
    }

    // Validar que el login no comience por número, guion o guion bajo o punto
    if (/^[\d._-]/.test(login)) {
        e.preventDefault();
        alert('El nombre de usuario no puede comenzar por un número, guion, guion bajo o punto.');
        return;
    }

    // Validar contraseña: no vacía
    if (!password) {
        e.preventDefault();
        alert('El campo de contraseña no puede estar vacío.');
        return;
    }

    // La longitud de la contraseña debe estar entre 16 y 1024 caracteres
    if (password.length < 16 || password.length > 1024) {
        e.preventDefault();
        alert('La contraseña debe tener entre 16 y 1024 caracteres.');
        return;
    }
});
