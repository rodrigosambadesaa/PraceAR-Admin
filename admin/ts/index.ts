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

type EditTarget = "general" | "translation";

const modal = document.getElementById("admin-modal");
const modalTitle = document.getElementById("admin-modal-title");
const modalFrame = document.getElementById("admin-modal-frame") as HTMLIFrameElement | null;
const closeButtons = Array.from(document.querySelectorAll<HTMLElement>("[data-close-modal]"));

let lastFocusedElement: HTMLElement | null = null;

function openModal(url: string, title: string) {
    if (!(modal instanceof HTMLElement) || !modalFrame || !(modalTitle instanceof HTMLElement)) {
        window.location.href = url;
        return;
    }

    lastFocusedElement = document.activeElement instanceof HTMLElement ? document.activeElement : null;

    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
    modalTitle.textContent = title;
    modalFrame.src = url;

    const closeButton = modal.querySelector<HTMLElement>(".admin-modal__close");
    closeButton?.focus();
}

function closeModal() {
    if (!(modal instanceof HTMLElement) || !modalFrame) {
        return;
    }

    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
    modalFrame.src = "about:blank";

    if (lastFocusedElement) {
        lastFocusedElement.focus();
        lastFocusedElement = null;
    }
}

closeButtons.forEach(button => {
    button.addEventListener("click", () => {
        closeModal();
    });
});

document.addEventListener("keydown", (event: KeyboardEvent) => {
    if (event.key === "Escape" && modal?.classList.contains("is-open")) {
        event.preventDefault();
        closeModal();
    }
});

function resolveEditUrl(target: EditTarget, trigger: HTMLElement): string | null {
    const row = trigger.closest<HTMLTableRowElement>("tr[data-edit-url]");

    if (!row) {
        return null;
    }

    if (target === "translation") {
        return row.dataset.translationUrl || null;
    }

    return row.dataset.editUrl || null;
}

function getModalTitle(target: EditTarget, trigger: HTMLElement): string {
    const row = trigger.closest<HTMLTableRowElement>("tr[data-edit-url]");
    const caseta = row?.dataset.caseta;
    const nombre = row?.dataset.nombre;
    const baseTitle = target === "translation" ? "Editar traducción" : "Editar puesto";
    const details = [caseta, nombre].filter(Boolean).join(" · ");

    return details ? `${baseTitle} · ${details}` : baseTitle;
}

function handleModalTrigger(trigger: HTMLElement, target: EditTarget) {
    const url = resolveEditUrl(target, trigger);

    if (!url) {
        return;
    }

    openModal(url, getModalTitle(target, trigger));
}

const editableCells = Array.from(document.querySelectorAll<HTMLElement>(".admin-table-cell--editable"));

editableCells.forEach(cell => {
    const target = cell.dataset.editTarget as EditTarget | undefined;

    if (!target) {
        return;
    }

    cell.addEventListener("click", (event: MouseEvent) => {
        const targetElement = event.target instanceof HTMLElement ? event.target : null;

        if (targetElement?.closest("a, button")) {
            return;
        }

        handleModalTrigger(cell, target);
    });

    cell.addEventListener("keydown", (event: KeyboardEvent) => {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            handleModalTrigger(cell, target);
        }
    });
});

// Cuando cargue la página, meter todas las etiquetas style en el head en una sola
window.addEventListener("load", () => {
    const styles = Array.from(document.querySelectorAll("style"));
    const head = document.head;

    if (styles.length > 0) {
        const combinedStyle = document.createElement("style");
        combinedStyle.textContent = styles.map(style => style.textContent).join("\n");
        head.appendChild(combinedStyle);

        styles.forEach(style => style.remove());
    }
});

console.log("Código TypeScript de la página de administración de puestos cargado correctamente.");

export { };

