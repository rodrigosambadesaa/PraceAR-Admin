function getFormElement(): HTMLFormElement {
    const form = document.getElementById("formulario-busqueda");

    if (!(form instanceof HTMLFormElement)) {
        throw new Error("No se encontró el formulario de búsqueda de puestos.");
    }

    return form;
}

function getSearchInput(): HTMLInputElement {
    const input = document.getElementById("input-busqueda");

    if (!(input instanceof HTMLInputElement)) {
        throw new Error("No se encontró el campo de búsqueda de puestos.");
    }

    return input;
}

const formulario = getFormElement();
const inputBusqueda = getSearchInput();

formulario.addEventListener("submit", (event: SubmitEvent) => {
    const busqueda = inputBusqueda.value.trim();

    if (busqueda && /[<>"'%]/.test(busqueda)) {
        event.preventDefault();
        alert("Por favor, elimine los caracteres no permitidos, que son: <, >, \", ', %");
    }
});

// Cuando cargue la página, meter todas las etiquetas style en el head en una sola
window.addEventListener("load", () => {
    const styles = Array.from(document.querySelectorAll("style"));
    const head = document.head;

    if (styles.length > 0) {
        const combinedStyle = document.createElement("style");
        combinedStyle.textContent = styles.map(style => style.textContent ?? "").join("\n");
        head.appendChild(combinedStyle);

        styles.forEach(style => style.remove());
    }
});

type QuickEditMessageType = "info" | "error" | "success";

interface QuickEditResponse {
    success: boolean;
    msg: string;
}

interface QuickEditTargetInfo {
    cell: HTMLElement;
    field: string;
    id: string;
    codigoIdioma?: string;
}

const QUICK_EDIT_COLORS: Record<QuickEditMessageType, string> = {
    info: "#333333",
    error: "#b00020",
    success: "#1b5e20"
};

const modalElements = {
    container: document.getElementById("modal-edicion") as HTMLElement | null,
    body: document.getElementById("modal-body") as HTMLElement | null,
    closeButton: document.getElementById("modal-close") as HTMLButtonElement | null
};

const zoomElements = {
    container: document.getElementById("zoomed-image-container") as HTMLElement | null,
    image: document.getElementById("zoomed-image") as HTMLImageElement | null,
    name: document.getElementById("zoomed-name") as HTMLElement | null,
    closeButton: document.querySelector<HTMLButtonElement>(".zoomed-container .close-button")
};

function openModalWithLoading(): void {
    if (!modalElements.container || !modalElements.body) {
        return;
    }

    modalElements.body.innerHTML = "<div style=\"text-align:center;\">Cargando...</div>";
    modalElements.container.style.display = "flex";
}

function closeModal(): void {
    if (!modalElements.container) {
        return;
    }

    modalElements.container.style.display = "none";
}

function closeZoom(): void {
    if (!zoomElements.container) {
        return;
    }

    zoomElements.container.classList.remove("show");
    zoomElements.container.setAttribute("aria-hidden", "true");
}

function openZoom(image: HTMLImageElement): void {
    if (!zoomElements.container || !zoomElements.image || !zoomElements.name) {
        return;
    }

    // Usa la ruta src del <img> original, sin modificarla
    zoomElements.image.src = image.src;

    const row = image.closest("tr");
    const nameCell = row?.querySelector<HTMLElement>("td:nth-child(5)");
    zoomElements.name.textContent = nameCell?.textContent ?? "";

    zoomElements.container.classList.add("show");
    zoomElements.container.setAttribute("aria-hidden", "false");
}

function attachZoomableImage(image: HTMLImageElement): void {
    if (image.dataset.zoomBound === "true") {
        return;
    }

    image.addEventListener("click", event => {
        event.stopPropagation();
        openZoom(image);
    });

    image.dataset.zoomBound = "true";
}

function initializeZoomableImages(): void {
    const zoomableImages = document.querySelectorAll<HTMLImageElement>(".zoomable");
    zoomableImages.forEach(attachZoomableImage);
}

function setQuickEditMessage(message: string, type: QuickEditMessageType = "info"): void {
    const messageElement = modalElements.body?.querySelector<HTMLElement>("#quick-edit-msg");

    if (!messageElement) {
        return;
    }

    messageElement.textContent = message;
    messageElement.style.color = QUICK_EDIT_COLORS[type];
}

function ensureCsrfToken(form: HTMLFormElement): void {
    const hasCsrf = form.querySelector<HTMLInputElement>("[name='csrf']");

    if (hasCsrf) {
        return;
    }

    const csrfSource = document.getElementById("csrf");

    if (csrfSource instanceof HTMLInputElement && csrfSource.value) {
        const csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = "csrf";
        csrfInput.value = csrfSource.value;
        form.appendChild(csrfInput);
    }
}

function getTrimmedValue(form: HTMLFormElement, selector: string): string {
    const element = form.querySelector<HTMLInputElement | HTMLTextAreaElement>(selector);
    return element ? element.value.trim() : "";
}

function validateQuickEditForm(form: HTMLFormElement, field: string): { valid: boolean; message: string } {
    let message = "";

    switch (field) {
        case "nombre":
        case "contacto": {
            const value = getTrimmedValue(form, "[name='value']");
            if (!value) {
                message = `El campo ${field} es obligatorio.`;
            } else if (value.length > 150) {
                message = "El texto no puede superar los 150 caracteres.";
            }
            break;
        }
        case "telefono": {
            const value = getTrimmedValue(form, "[name='value']");
            const telefonoRegex = /^[0-9+\s().-]{3,20}$/;
            if (!value) {
                message = "El teléfono es obligatorio.";
            } else if (!telefonoRegex.test(value)) {
                message = "Introduce un teléfono válido (solo números, espacios y los símbolos +, -, ( )).";
            }
            break;
        }
        case "caseta_padre": {
            const value = getTrimmedValue(form, "[name='value']");
            if (value && !/^[A-Za-z0-9_-]{2,15}$/.test(value)) {
                message = "La caseta padre debe tener entre 2 y 15 caracteres alfanuméricos, guiones o guiones bajos.";
            }
            break;
        }
        case "traduccion": {
            const tipo = getTrimmedValue(form, "[name='tipo']");
            const descripcion = getTrimmedValue(form, "[name='descripcion']");
            if (!tipo) {
                message = "El tipo es obligatorio.";
            } else if (tipo.length > 150) {
                message = "El tipo no puede superar los 150 caracteres.";
            } else if (descripcion.length > 450) {
                message = "La descripción no puede superar los 450 caracteres.";
            }
            break;
        }
        case "imagen": {
            const fileInput = form.querySelector<HTMLInputElement>("input[type='file'][name='imagen']");
            const deleteChecked = form.querySelector<HTMLInputElement>("input[name='eliminar_imagen']")?.checked ?? false;
            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                const file = fileInput.files[0];
                if (!/^image\/jpeg$/i.test(file.type)) {
                    message = "Solo se permiten imágenes en formato JPG.";
                } else if (file.size > 4 * 1024 * 1024) {
                    message = "La imagen no puede superar los 4 MB.";
                }
            } else if (!deleteChecked) {
                message = "Selecciona una imagen JPG o marca la opción de eliminar.";
            }
            break;
        }
        default:
            break;
    }

    return {
        valid: message === "",
        message
    };
}

function truncateText(value: string, maxLength = 30): string {
    return value.length > maxLength ? `${value.substring(0, maxLength)}...` : value;
}

function updateCellAfterSuccess(context: QuickEditTargetInfo, form: HTMLFormElement): void {
    const { cell, field, id } = context;

    switch (field) {
        case "nombre":
        case "contacto":
        case "telefono":
        case "caseta_padre": {
            const valueInput = form.querySelector<HTMLInputElement>("[name='value']");
            if (valueInput) {
                cell.textContent = valueInput.value.trim();
            }
            break;
        }
        case "tipo": {
            const tipoInput = form.querySelector<HTMLInputElement>("[name='tipo']");
            if (tipoInput) {
                cell.textContent = tipoInput.value.trim();
            }
            break;
        }
        case "descripcion": {
            const descripcionInput = form.querySelector<HTMLTextAreaElement>("[name='descripcion']");
            if (descripcionInput) {
                cell.textContent = truncateText(descripcionInput.value.trim());
            }
            break;
        }
        case "traduccion": {
            const row = cell.closest("tr");
            const tipoCell = row?.querySelector<HTMLElement>("[data-field='tipo']");
            const descripcionCell = row?.querySelector<HTMLElement>("[data-field='descripcion']");
            const tipoInput = form.querySelector<HTMLInputElement>("[name='tipo']");
            const descripcionInput = form.querySelector<HTMLTextAreaElement>("[name='descripcion']");
            if (tipoCell && tipoInput) {
                tipoCell.textContent = tipoInput.value.trim();
            }
            if (descripcionCell && descripcionInput) {
                descripcionCell.textContent = truncateText(descripcionInput.value.trim());
            }
            break;
        }
        case "imagen": {
            const deleteCheckbox = form.querySelector<HTMLInputElement>("input[name='eliminar_imagen']");
            const isDeleting = Boolean(deleteCheckbox?.checked);
            const existingImage = cell.querySelector<HTMLImageElement>("img.zoomable");

            if (isDeleting) {
                cell.innerHTML = "";
                const placeholder = document.createElement("div");
                placeholder.textContent = "Subir imagen";
                placeholder.className = "editable-image-blank";
                placeholder.dataset.editable = "true";
                placeholder.dataset.field = "imagen";
                placeholder.dataset.id = id;
                placeholder.style.height = "40px";
                placeholder.style.display = "flex";
                placeholder.style.alignItems = "center";
                placeholder.style.justifyContent = "center";
                placeholder.style.cursor = "pointer";
                placeholder.style.color = "#888";
                placeholder.style.fontSize = "0.9em";
                cell.appendChild(placeholder);
                attachEditableElement(placeholder);
                break;
            }

            if (existingImage) {
                const baseSrc = existingImage.src.split("?")[0];
                existingImage.src = `${baseSrc}?${Date.now()}`;
                attachZoomableImage(existingImage);
                attachEditableElement(existingImage);
                break;
            }

            const fileInput = form.querySelector<HTMLInputElement>("input[type='file'][name='imagen']");
            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                const row = cell.closest("tr");
                const casetaCell = row?.querySelector<HTMLElement>("[data-field='caseta']");
                const caseta = casetaCell?.textContent?.trim();
                if (caseta) {
                    cell.innerHTML = "";
                    const newImage = document.createElement("img");
                    newImage.loading = "lazy";
                    newImage.alt = "Imagen del puesto";
                    newImage.className = "zoomable editable-image";
                    newImage.dataset.editable = "true";
                    newImage.dataset.field = "imagen";
                    newImage.dataset.id = id;
                    newImage.src = `/appventurers/assets/${caseta}.jpg?${Date.now()}`;
                    cell.appendChild(newImage);
                    attachZoomableImage(newImage);
                    attachEditableElement(newImage);
                }
            }
            break;
        }
        default:
            break;
    }
}

function focusFirstEditableField(form: HTMLFormElement): void {
    const firstField = form.querySelector<HTMLInputElement | HTMLTextAreaElement>("input:not([type='hidden']), textarea");
    if (firstField) {
        firstField.focus();
        if (firstField instanceof HTMLInputElement) {
            firstField.select();
        }
    }
}

function overrideQuickEditSubmit(form: HTMLFormElement, context: QuickEditTargetInfo): void {
    form.onsubmit = event => {
        event.preventDefault();

        const fieldForValidation = form.querySelector<HTMLInputElement>("[name='field']")?.value ?? context.field;
        const validation = validateQuickEditForm(form, fieldForValidation);

        if (!validation.valid) {
            setQuickEditMessage(validation.message, "error");
            return;
        }

        setQuickEditMessage("Guardando...", "info");
        const formData = new FormData(form);

        fetch("admin/ajax_quick_edit_save.php", {
            method: "POST",
            body: formData
        })
            .then(async response => {
                const rawText = await response.text();
                let data: QuickEditResponse;

                try {
                    data = JSON.parse(rawText) as QuickEditResponse;
                } catch (error) {
                    setQuickEditMessage(`Error inesperado: ${rawText}`, "error");
                    return;
                }

                setQuickEditMessage(data.msg, data.success ? "success" : "error");
                if (data.success) {
                    updateCellAfterSuccess(context, form);
                    window.setTimeout(() => {
                        setQuickEditMessage("", "info");
                        closeModal();
                    }, 800);
                }
            })
            .catch(error => {
                const message = error instanceof Error ? error.message : String(error);
                setQuickEditMessage(`Error al guardar: ${message}`, "error");
            });
    };
}

function attachEditableElement(element: Element): void {
    if (!(element instanceof HTMLElement)) {
        return;
    }

    if (element.dataset.quickEditBound === "true") {
        return;
    }

    element.addEventListener("click", event => {
        event.stopPropagation();
        handleEditableClick(element);
    });

    element.dataset.quickEditBound = "true";
}

function handleEditableClick(element: HTMLElement): void {
    const field = element.dataset.field;
    const id = element.dataset.id;
    const codigoIdioma = element.dataset.codigo_idioma;

    if (!field || !id) {
        return;
    }

    openModalWithLoading();

    fetch("admin/ajax_quick_edit.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id, field, codigo_idioma: codigoIdioma })
    })
        .then(response => response.text())
        .then(html => {
            if (!modalElements.body) {
                return;
            }

            modalElements.body.innerHTML = html;
            const form = modalElements.body.querySelector<HTMLFormElement>("form");

            if (!form) {
                return;
            }

            ensureCsrfToken(form);
            focusFirstEditableField(form);

            const context: QuickEditTargetInfo = {
                cell: element,
                field,
                id,
                codigoIdioma
            };

            overrideQuickEditSubmit(form, context);
        })
        .catch(error => {
            const message = error instanceof Error ? error.message : String(error);
            if (modalElements.body) {
                modalElements.body.innerHTML = `<div class="admin-error-text">Error al cargar el formulario: ${message}</div>`;
            }
        });
}

function initializeQuickEdit(): void {
    const editableElements = document.querySelectorAll<HTMLElement>("[data-editable='true']");
    editableElements.forEach(attachEditableElement);
}

if (modalElements.closeButton) {
    modalElements.closeButton.addEventListener("click", closeModal);
}

if (modalElements.container) {
    modalElements.container.addEventListener("click", event => {
        if (event.target === modalElements.container) {
            closeModal();
        }
    });
}

if (zoomElements.closeButton) {
    zoomElements.closeButton.addEventListener("click", closeZoom);
}

if (zoomElements.container) {
    zoomElements.container.addEventListener("click", event => {
        if (event.target === zoomElements.container) {
            closeZoom();
        }
    });
}

initializeZoomableImages();
initializeQuickEdit();

export { };
