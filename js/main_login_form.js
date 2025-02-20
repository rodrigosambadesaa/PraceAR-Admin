document.getElementById('formulario').addEventListener('submit', function handleSubmit(e) {
    const login = document.getElementById('login').value.trim();
    const password = document.getElementById('password').value.trim();
    const passwordOriginal = document.getElementById('password').value;

    // El login y la contraseña deben ser strings
    if (typeof login !== 'string' || typeof password !== 'string') {
        e.preventDefault();
        // Mostrar ventana modal con mensaje de error
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

    // La longitud de la contraseña debe estar entre 16 y 1024 caracteres
    if (password.length < 16 || password.length > 1024) {
        e.preventDefault();
        alert('La contraseña debe tener entre 16 y 1024 caracteres.');
        return;
    }

    // Si todo está validado, permite el envío del formulario
    formulario.removeEventListener('submit', handleSubmit);
    formulario.submit();
});
