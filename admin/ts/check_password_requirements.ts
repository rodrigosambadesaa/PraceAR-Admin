import {
    verifyStrongPassword,
    haSidoFiltradaEnBrechas,
    contrasenhaSimilarAUsuario,
    tieneSecuenciasNumericasInseguras,
    tieneSecuenciasAlfabeticasInseguras,
    tieneSecuenciasDeCaracteresEspecialesInseguras,
    tieneEspaciosAlPrincipioOAlFinal,
} from "../../js/helpers/verify_strong_password.js";

declare global {
    interface Window {
        checkPasswordRequirements: () => Promise<void>;
    }
}

function getForm(): HTMLFormElement {
    const form = document.getElementById("formulario-cambio-contrasena");

    if (!(form instanceof HTMLFormElement)) {
        throw new Error("No se encontró el formulario de cambio de contraseña.");
    }

    return form;
}

function getNewPasswordInput(): HTMLInputElement {
    const input = document.getElementById("new-password");

    if (!(input instanceof HTMLInputElement)) {
        throw new Error("No se encontró el campo de nueva contraseña.");
    }

    return input;
}

function getUsernameInput(): HTMLInputElement {
    const input = document.getElementById("nombre-usuario");

    if (!(input instanceof HTMLInputElement)) {
        throw new Error("No se encontró el campo de nombre de usuario.");
    }

    return input;
}

document.addEventListener("DOMContentLoaded", () => {
    const formulario = getForm();
    const inputNuevaContrasena = getNewPasswordInput();
    const inputNombreUsuario = getUsernameInput();

    function crearMensajeError(texto: string): void {
        const spanError = document.createElement("span");
        spanError.style.color = "red";
        spanError.textContent = texto;
        inputNuevaContrasena.insertAdjacentElement("afterend", spanError);
    }

    function bloquearEnvio(): void {
        formulario.addEventListener(
            "submit",
            (event: SubmitEvent) => {
                event.preventDefault();
            },
            { once: true },
        );
    }

    window.checkPasswordRequirements = async () => {
        const nuevaContrasena = inputNuevaContrasena.value;
        const mensajesError = formulario.querySelectorAll<HTMLSpanElement>('span[style="color: red;"]');
        mensajesError.forEach((mensaje) => mensaje.remove());

        if (!verifyStrongPassword(nuevaContrasena)) {
            crearMensajeError(
                "La nueva contraseña no cumple con los requisitos de seguridad. Debe tener al menos 16 caracteres, una letra mayúscula, una letra minúscula, un número y tres caracteres especiales distintos.",
            );
            bloquearEnvio();
        }

        if (tieneEspaciosAlPrincipioOAlFinal(nuevaContrasena)) {
            crearMensajeError("La nueva contraseña no puede tener espacios al principio o al final.");
            bloquearEnvio();
        }

        if (tieneSecuenciasAlfabeticasInseguras(nuevaContrasena)) {
            crearMensajeError(
                "La nueva contraseña no puede contener secuencias alfabéticas inseguras como abc, cba, ni en vertical como qwe",
            );
            bloquearEnvio();
        }

        if (tieneSecuenciasNumericasInseguras(nuevaContrasena)) {
            crearMensajeError(
                "La nueva contraseña no puede tener secuencias numéricas inseguras como 123, 987, ni en vertical como 147, ni en diagonal como 159 y 753",
            );
            bloquearEnvio();
        }

        if (tieneSecuenciasDeCaracteresEspecialesInseguras(nuevaContrasena)) {
            crearMensajeError(
                "La nueva contraseña no puede contener secuencias de caracteres especiales inseguras como ()",
            );
            bloquearEnvio();
        }

        if (contrasenhaSimilarAUsuario(nuevaContrasena, inputNombreUsuario.value)) {
            crearMensajeError("La nueva contraseña no puede ser similar al nombre de usuario.");
            bloquearEnvio();
        }

        if (await haSidoFiltradaEnBrechas(nuevaContrasena)) {
            crearMensajeError(
                "La nueva contraseña ha sido filtrada en brechas de seguridad. Por favor, elige una contraseña más segura.",
            );
            bloquearEnvio();
        }
    };
});

export {};
