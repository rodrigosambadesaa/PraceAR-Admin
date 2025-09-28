import { limpiarInput } from "../../js/helpers/clean_input.js";
import { UNITY_TYPE } from "../../js/constants.js";
import { verifyMaliciousPhoto } from "./helpers/verify_malicious_photo.js";

type VerificationResult = Awaited<ReturnType<typeof verifyMaliciousPhoto>>;

type UnityValues = (typeof UNITY_TYPE)[keyof typeof UNITY_TYPE];

function getForm(): HTMLFormElement {
    const form = document.getElementById("formulario-editar");

    if (!(form instanceof HTMLFormElement)) {
        throw new Error("No se encontró el formulario de edición de puestos.");
    }

    return form;
}

function getInputElement(id: string): HTMLInputElement {
    const element = document.getElementById(id);

    if (!(element instanceof HTMLInputElement)) {
        throw new Error(`No se encontró el elemento de formulario con id "${id}".`);
    }

    return element;
}

function getSelectElement(id: string): HTMLSelectElement {
    const element = document.getElementById(id);

    if (!(element instanceof HTMLSelectElement)) {
        throw new Error(`No se encontró el elemento de selección con id "${id}".`);
    }

    return element;
}

function getOptionalInput(id: string): HTMLInputElement | null {
    const element = document.getElementById(id);

    if (element === null) {
        return null;
    }

    if (!(element instanceof HTMLInputElement)) {
        throw new Error(`El elemento con id "${id}" no es un campo de texto válido.`);
    }

    return element;
}

function validateCaseta(caseta: string): string | null {
    if (!caseta) {
        return "Caseta, tipoUnity e idNave son obligatorios";
    }

    if (caseta.length !== 5) {
        return "Caseta debe ser una cadena de exactamente 5 caracteres";
    }

    const letras = caseta.substring(0, 2).toUpperCase();
    const numero = caseta.substring(2);

    if (!/^[0-9]{3}$/.test(numero)) {
        return "Las tres últimas posiciones de caseta deben ser números en el rango 001-370";
    }

    const numeroValor = Number.parseInt(numero, 10);
    const letrasValidas = new Set(["CE", "CO", "MC", "NA", "NC"]);

    if (!letrasValidas.has(letras) || numeroValor < 1 || numeroValor > 370) {
        return "Las dos primeras letras de caseta deben ser \"CE\", \"CO\", \"MC\", \"NA\", o \"NC\", y las tres últimas un número entre 001 y 370";
    }

    return null;
}

function validateCasetaPadre(casetaPadre: string): string | null {
    if (casetaPadre === "") {
        return null;
    }

    if (casetaPadre.length !== 5) {
        return "Caseta padre, si se ha introducido, debe ser una cadena de exactamente 5 caracteres";
    }

    const letrasPadre = casetaPadre.substring(0, 2).toUpperCase();
    const numeroPadre = casetaPadre.substring(2);

    if (!/^[0-9]{3}$/.test(numeroPadre)) {
        return "Las tres últimas posiciones de caseta padre deben ser números con el formato 001-370";
    }

    const numeroPadreValor = Number.parseInt(numeroPadre, 10);
    const letrasValidas = new Set(["CE", "CO", "MC", "NA", "NC"]);

    if (!letrasValidas.has(letrasPadre) || numeroPadreValor < 1 || numeroPadreValor > 370) {
        return "Las dos primeras letras de caseta padre deben ser \"CE\", \"CO\", \"MC\", \"NA\", o \"NC\" y las tres últimas un número entre 001 y 370";
    }

    return null;
}

async function verifyImage(file: File): Promise<VerificationResult> {
    return verifyMaliciousPhoto(file);
}

const formulario = getForm();
const casetaInput = getInputElement("caseta");
const tipoUnitySelect = getSelectElement("tipo-unity");
const idNaveSelect = getSelectElement("id-nave");
const nombreInput = getOptionalInput("nombre");
const eliminarImagenInput = getOptionalInput("eliminar-imagen");
const contactoInput = getOptionalInput("contacto");
const telefonoInput = getOptionalInput("telefono");
const casetaPadreInput = getOptionalInput("caseta-padre");
const imagenInput = getOptionalInput("imagen");

function limpiarMensajesPrevios(): void {
    const siguienteElemento = formulario.nextElementSibling;

    if (siguienteElemento instanceof HTMLDivElement && siguienteElemento.classList.contains("form-errors")) {
        siguienteElemento.remove();
    }
}

formulario.addEventListener("submit", async (event: SubmitEvent) => {
    event.preventDefault();
    limpiarMensajesPrevios();

    const errores: string[] = [];

    const caseta = limpiarInput(casetaInput.value);
    const tipoUnity = tipoUnitySelect.value as UnityValues | string;
    const idNaveValor = Number.parseInt(idNaveSelect.value, 10);

    if (!caseta || !tipoUnity || Number.isNaN(idNaveValor)) {
        errores.push("Caseta, tipoUnity e idNave son obligatorios");
    }

    const errorCaseta = validateCaseta(caseta);
    if (errorCaseta) {
        errores.push(errorCaseta);
    }

    const unityValues = Object.values(UNITY_TYPE) as readonly string[];
    if (!unityValues.includes(tipoUnity)) {
        errores.push("Tipo de unidad debe ser uno de los valores de UNITY_TYPE");
    }

    if (Number.isNaN(idNaveValor) || idNaveValor < 1 || idNaveValor > 12) {
        errores.push("El value de ID de nave debe estar entre 1 y 12");
    }

    const nombre = nombreInput ? limpiarInput(nombreInput.value) : "";
    const eliminarImagen = eliminarImagenInput ? limpiarInput(eliminarImagenInput.value) : "";
    const contacto = contactoInput ? limpiarInput(contactoInput.value) : "";
    const telefono = telefonoInput ? limpiarInput(telefonoInput.value) : "";
    const casetaPadre = casetaPadreInput ? limpiarInput(casetaPadreInput.value) : "";

    if (nombre && nombre.length > 50) {
        errores.push("El nombre, si se ha introducido, debe ser una cadena de máximo 50 caracteres");
    }

    if (eliminarImagen && (eliminarImagen !== "0" && eliminarImagen !== "1")) {
        errores.push("Eliminar imagen debe ser un entero con valor 0 o 1");
    }

    if (contacto && contacto.length > 250) {
        errores.push("Contacto, si se ha introducido, debe ser una cadena de máximo 250 caracteres");
    }

    if (telefono && telefono.length > 15) {
        errores.push("Teléfono, si se ha introducido, debe ser una cadena de máximo 15 caracteres");
    }

    const errorCasetaPadre = validateCasetaPadre(casetaPadre);
    if (errorCasetaPadre) {
        errores.push(errorCasetaPadre);
    }

    const archivo = imagenInput?.files?.[0] ?? null;

    const validFileExtensions = [".jpg", ".jpeg"];

    function validateJpgFile(oForm: HTMLFormElement): boolean {
        const arrInputs = oForm.getElementsByTagName("input");
        for (let i = 0; i < arrInputs.length; i++) {
            const oInput = arrInputs[i];
            if (oInput.type === "file") {
                const sFileName = oInput.value;
                if (sFileName.length > 0) {
                    let isValid = false;
                    for (let j = 0; j < validFileExtensions.length; j++) {
                        const ext = validFileExtensions[j];
                        if (sFileName.toLowerCase().endsWith(ext)) {
                            isValid = true;
                            break;
                        }
                    }
                    if (!isValid) {
                        alert("Solo se permiten archivos .jpg o .jpeg");
                        return false;
                    }
                }
            }
        }
        return true;
    }

    if (!validateJpgFile(formulario)) {
        return;
    }

    if (archivo && archivo.size > 0) {
        const allowedTypes = new Set(["image/jpeg", "image/jpg"]);
        const maxSize = 2048 * 1024; // 2MB en bytes

        if (!allowedTypes.has(archivo.type)) {
            errores.push("La imagen debe ser un archivo .jpg o .jpeg válido");
        } else if (archivo.size > maxSize) {
            errores.push("La imagen no puede ser mayor a 2MB");
        } else {
            try {
                const verification = await verifyImage(archivo);

                if (!verification.success) {
                    errores.push(verification.message || "No se pudo verificar la imagen.");
                } else if (verification.isMalicious) {
                    errores.push(
                        verification.message
                        || "La foto es maliciosa. Por favor, desinfecte el archivo o cargue una nueva imagen segura.",
                    );
                }
            } catch (error) {
                console.error("Error en verificación de imagen:", error);
                errores.push("Error al verificar la imagen. Por favor, inténtelo de nuevo.");
            }
        }
    }

    if (errores.length > 0) {
        alert(errores[0]);

        const div = document.createElement("div");
        div.classList.add("form-errors");
        div.innerHTML = `<ul style="color: red;">${errores.map((mensaje) => `<li>${mensaje}</li>`).join("")}</ul>`;
        formulario.insertAdjacentElement("afterend", div);
        return;
    }

    formulario.submit();
});
