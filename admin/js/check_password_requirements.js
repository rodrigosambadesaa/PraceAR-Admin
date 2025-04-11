import { verifyStrongPassword, haSidoFiltradaEnBrechas, contrasenhaSimilarAUsuario, tieneSecuenciasNumericasInseguras, tieneSecuenciasAlfabeticasInseguras, tieneSecuenciasDeCaracteresEspecialesInseguras, tieneEspaciosAlPrincipioOAlFinal } from "../../js/helpers/verify_strong_password.js";
document.addEventListener('DOMContentLoaded', function () {
    window.checkPasswordRequirements = async function () {
        const newPassword = document.getElementById('new-password').value;
        const formulario = document.getElementById('formulario-cambio-contrasena');

        // Eliminamos los mensajes de error anteriores
        const mensajesError = document.querySelectorAll('span[style="color: red;"]');
        mensajesError.forEach(mensaje => mensaje.remove());

        if (!verifyStrongPassword(newPassword)) {
            // Mensaje de error debajo del input
            const inputNuevaContraseha = document.getElementById('new-password');
            const spanError = document.createElement('span');
            spanError.style.color = 'red';
            spanError.textContent = 'La nueva contraseña no cumple con los requisitos de seguridad. Debe tener al menos 16 caracteres, una letra mayúscula, una letra minúscula, un número y tres caracteres especiales distintos.';
            inputNuevaContraseha.insertAdjacentElement('afterend', spanError);
            // Prrevenir el envío del formulario
            formulario.addEventListener('submit', function (e) {
                e.preventDefault();
            });
        }

        if (tieneEspaciosAlPrincipioOAlFinal(newPassword)) {
            // Mensaje de error debajo del input
            const inputNuevaContraseha = document.getElementById('new-password');
            const spanError = document.createElement('span');
            spanError.style.color = 'red';
            spanError.textContent = 'La nueva contraseña no puede tener espacios al principio o al final.';
            inputNuevaContraseha.insertAdjacentElement('afterend', spanError);
            // Prevenir el envío del formulario
            formulario.addEventListener('submit', function (e) {
                e.preventDefault();
            });
        }

        // Sin secuencias alfabéticas inseguras
        if (tieneSecuenciasAlfabeticasInseguras(newPassword)) {
            // Mensaje de error debajo del input
            const inputNuevaContraseha = document.getElementById('new-password');
            const spanError = document.createElement('span');
            spanError.style.color = 'red';
            spanError.textContent = 'La nueva contraseña no puede contener secuencias alfabéticas inseguras como abc, cba, ni en vertical como qwe';
            inputNuevaContraseha.insertAdjacentElement('afterend', spanError);
            // Prevenir el envío del formulario
            formulario.addEventListener('submit', function (e) {
                e.preventDefault();
            });
        }

        // Sin secuencias numéricas inseguras
        if (tieneSecuenciasNumericasInseguras(newPassword)) {
            // Mensaje de error debajo del input
            const inputNuevaContraseha = document.getElementById('new-password');
            const spanError = document.createElement('span');
            spanError.style.color = 'red';
            spanError.textContent = 'La nueva contraseña no puede tener secuencias numéricas inseguras como 123, 987, ni en vertical como 147, ni en diagonal como 159 y 753';
            inputNuevaContraseha.insertAdjacentElement('afterend', spanError);
            // Prevenir el envío del formulario
            formulario.addEventListener('submit', function (e) {
                e.preventDefault();
            });
        }

        if (tieneSecuenciasDeCaracteresEspecialesInseguras(newPassword)) {
            // Mensaje de error debajo del input
            const inputNuevaContraseha = document.getElementById('new-password');
            const spanError = document.createElement('span');
            spanError.style.color = 'red';
            spanError.textContent = 'La nueva contraseña no puede contener secuencias de caracteres especiales inseguras como ()';
            inputNuevaContraseha.insertAdjacentElement('afterend', spanError);
            // Prevenir el envío del formulario
            formulario.addEventListener('submit', function (e) {
                e.preventDefault();
            });
        }

        // La contraseña no puede ser similar al nombre de usuario
        if (contrasenhaSimilarAUsuario(newPassword, document.getElementById('nombre-usuario').value)) {
            // Mensaje de error debajo del input
            const inputNuevaContraseha = document.getElementById('new-password');
            const spanError = document.createElement('span');
            spanError.style.color = 'red';
            spanError.textContent = 'La nueva contraseña no puede ser similar al nombre de usuario.';
            inputNuevaContraseha.insertAdjacentElement('afterend', spanError);
            // Prevenir el envío del formulario
            formulario.addEventListener('submit', function (e) {
                e.preventDefault();
            });
        }

        // La contraseña no puede haber sido filtrada en brechas
        if (await haSidoFiltradaEnBrechas(newPassword)) {
            // Mensaje de error debajo del input
            const inputNuevaContraseha = document.getElementById('new-password');
            const spanError = document.createElement('span');
            spanError.style.color = 'red';
            spanError.textContent = 'La nueva contraseña ha sido filtrada en brechas de seguridad. Por favor, elige una contraseña más segura.';
            inputNuevaContraseha.insertAdjacentElement('afterend', spanError);
            // Prevenir el envío del formulario
            formulario.addEventListener('submit', function (e) {
                e.preventDefault();
            });
        }
    }
});

// export { checkPasswordRequirements };