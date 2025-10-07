function getFormElement() {
    const form = document.getElementById("formulario-busqueda");
    if (!(form instanceof HTMLFormElement)) {
        throw new Error("No se encontró el formulario de búsqueda de puestos.");
    }
    return form;
}
function getSearchInput() {
    const input = document.getElementById("input-busqueda");
    if (!(input instanceof HTMLInputElement)) {
        throw new Error("No se encontró el campo de búsqueda de puestos.");
    }
    return input;
}
const formulario = getFormElement();
const inputBusqueda = getSearchInput();
formulario.addEventListener("submit", (event) => {
    const busqueda = inputBusqueda.value.trim();
    if (busqueda && /[<>"'%]/.test(busqueda)) {
        event.preventDefault();
        alert("Por favor, elimine los caracteres no permitidos, que son: <, >, \", ', %");
    }
});
console.log("Código TypeScript de la página de administración de puestos cargado correctamente.");
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

const modal = document.getElementById("admin-modal");
const modalFrame = document.getElementById("admin-modal-frame");
const modalCloseButton = modal?.querySelector("[data-modal-close]");
const modalLoader = modal?.querySelector("[data-modal-loader]");
const modalTitle = document.getElementById("admin-modal-title");

if (modal instanceof HTMLElement && modalFrame instanceof HTMLIFrameElement && modalCloseButton instanceof HTMLButtonElement && modalLoader instanceof HTMLElement && modalTitle instanceof HTMLElement) {
    let lastFocusedElement = null;

    const setLoaderState = (isActive) => {
        modalLoader.classList.toggle("is-active", isActive);
        modalFrame.classList.toggle("is-loading", isActive);
    };

    const closeModal = () => {
        modal.classList.remove("is-visible");
        modal.setAttribute("aria-hidden", "true");
        document.body.classList.remove("admin-modal-open");
        modalFrame.src = "about:blank";
        setLoaderState(false);
        if (lastFocusedElement instanceof HTMLElement) {
            lastFocusedElement.focus();
        }
        lastFocusedElement = null;
    };

    const openModal = (url, trigger, type) => {
        if (!url) {
            return;
        }

        lastFocusedElement = trigger instanceof HTMLElement ? trigger : null;
        document.body.classList.add("admin-modal-open");
        modal.classList.add("is-visible");
        modal.setAttribute("aria-hidden", "false");
        modalTitle.textContent = type === "translation" ? "Editar traducción del puesto" : "Editar puesto";
        setLoaderState(true);
        modalFrame.src = url;
        modalCloseButton.focus();
    };

    modalCloseButton.addEventListener("click", closeModal);
    modal.addEventListener("click", (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && modal.classList.contains("is-visible")) {
            closeModal();
        }
    });

    modalFrame.addEventListener("load", () => {
        setLoaderState(false);
        modalFrame.focus();
    });

    const handleActivation = (element, type) => {
        const open = (event) => {
            const target = event.target;
            if (type === "translation" && target instanceof HTMLElement && target.closest("a[data-translation-link='true']")) {
                return;
            }

            event.preventDefault();
            const row = element.closest("tr");
            const url = row?.dataset[type === "translation" ? "translationUrl" : "editUrl"];
            openModal(url, element, type);
        };

        element.addEventListener("click", open);
        element.addEventListener("keydown", (event) => {
            if (event.key === "Enter" || event.key === " ") {
                open(event);
            }
        });
    };

    document.querySelectorAll('[data-edit-target="general"]').forEach((element) => {
        if (element instanceof HTMLElement) {
            handleActivation(element, "general");
        }
    });

    document.querySelectorAll('[data-edit-target="translation"]').forEach((element) => {
        if (element instanceof HTMLElement) {
            handleActivation(element, "translation");
        }
    });

    document.querySelectorAll('a[data-translation-link="true"]').forEach((link) => {
        if (link instanceof HTMLAnchorElement) {
            link.addEventListener("click", (event) => {
                event.preventDefault();
                const row = link.closest("tr");
                const url = row?.dataset.translationUrl ?? link.href;
                openModal(url, link, "translation");
            });
            link.addEventListener("keydown", (event) => {
                if (event.key === "Enter" || event.key === " ") {
                    event.preventDefault();
                    const row = link.closest("tr");
                    const url = row?.dataset.translationUrl ?? link.href;
                    openModal(url, link, "translation");
                }
            });
        }
    });
}
export {};
