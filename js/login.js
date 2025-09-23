"use strict";

import { tieneSecuenciasAlfabeticasInseguras, tieneSecuenciasDeCaracteresEspecialesInseguras, tieneSecuenciasNumericasInseguras } from './helpers/verify_strong_password.js';

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

    // Validar que la contraseña tenga al menos una letra mayúscula, una letra minúscula, un número y tres caracteres especiales distintos
    let numeroCaracteresEspeciales = 0, numeroMayusculas = 0, numeroMinusculas = 0, numeroNumeros = 0;
    const regexCaracteresEspeciales = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/;
    const regexMinusculas = /[a-z]/;
    const regexMayusculas = /[A-Z]/;
    const regexNumeros = /[0-9]/;

    for (let i = 0; i < password.length; i++) {
        if (regexNumeros.test(password[i])) {
            numeroNumeros++;
        }

        if (regexMinusculas.test(password[i])) {
            numeroMinusculas++;
        }

        if (regexMayusculas.test(password[i])) {
            numeroMayusculas++;
        }

        if (regexCaracteresEspeciales.test(password[i])) {
            numeroCaracteresEspeciales++;
        }
    }

    if (numeroMayusculas < 1 || numeroMinusculas < 1 || numeroNumeros < 1 || numeroCaracteresEspeciales < 3) {
        e.preventDefault();
        alert('La contraseña debe tener al menos una letra mayúscula, una letra minúscula, un número y tres caracteres especiales distintos.');
        return;
    }

    // Validar que la contraseña no contenga secuencias alfabéticas, de caracteres especiales o numéricas inseguras
    if (tieneSecuenciasAlfabeticasInseguras(password) || tieneSecuenciasDeCaracteresEspecialesInseguras(password) || tieneSecuenciasNumericasInseguras(password)) {
        e.preventDefault();
        alert('La contraseña no puede contener secuencias alfabéticas, de caracteres especiales o numéricas inseguras como "abc", "qwerty", "qaz", "123", "147", "159"');
        return;
    }
});
