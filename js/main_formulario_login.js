document.getElementById('formulario').addEventListener('submit', (e) => {
    const login = document.getElementById('login').value.trim();
    const password = document.getElementById('password').value.trim();

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

    // Validar contraseña: no vacía
    if (!password) {
        e.preventDefault();
        alert('El campo de contraseña no puede estar vacío.');
        return;
    }

    // No permitir espacios al principio o al final de la contraseña
    if (password !== password.trim()) {
        e.preventDefault();
        alert('La contraseña no puede contener espacios al principio o al final.');
        return;
    }

    // Si todo está validado, permite el envío del formulario
});
