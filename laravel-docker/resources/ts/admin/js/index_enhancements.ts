import { resolveAppUrl } from "../../js/helpers/base_url.js";

function initializeImageZoom(): void {
    const container = document.getElementById("zoomed-image-container");
    const zoomedImage = document.getElementById("zoomed-image");
    const zoomedName = document.getElementById("zoomed-name");

    if (
        !(container instanceof HTMLElement)
        || !(zoomedImage instanceof HTMLImageElement)
        || !(zoomedName instanceof HTMLElement)
    ) {
        return;
    }

    document.querySelectorAll<HTMLImageElement>(".zoomable.editable-image").forEach((image) => {
        image.addEventListener("click", () => {
            if (image.dataset.exists !== "1") {
                return;
            }

            zoomedImage.src = image.src;
            zoomedName.textContent = image.alt;
            container.classList.add("show");
            container.setAttribute("aria-hidden", "false");
        });
    });
}

function initializeSearchSuggestions(): void {
    const input = document.getElementById("input-busqueda");
    const languageSelector = document.getElementById("table-language-selector");

    if (!(input instanceof HTMLInputElement)) {
        return;
    }

    const form = input.closest("form");
    const parent = input.parentElement;
    if (!(form instanceof HTMLFormElement) || !(parent instanceof HTMLElement)) {
        return;
    }

    const suggestions = document.createElement("div");
    suggestions.id = "sugerencias-busqueda";
    suggestions.hidden = true;
    parent.classList.add("search-suggestions-host");
    parent.append(suggestions);

    let requestController: AbortController | null = null;

    input.addEventListener("input", () => {
        const term = input.value.trim();
        const selectedLanguage = languageSelector instanceof HTMLSelectElement
            ? languageSelector.value
            : "es";

        requestController?.abort();
        suggestions.hidden = true;
        suggestions.replaceChildren();

        if (term.length < 2 || term.length > 10 || !/^[A-Za-z0-9-]+$/.test(term)) {
            return;
        }

        requestController = new AbortController();
        const endpoint = new URL(resolveAppUrl("admin/ajax_sugerencias.php"));
        endpoint.searchParams.set("caseta", term);
        endpoint.searchParams.set("lang", selectedLanguage);

        void fetch(endpoint, {
            credentials: "same-origin",
            headers: { Accept: "application/json" },
            signal: requestController.signal,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`Respuesta inesperada (${response.status})`);
                }

                return response.json();
            })
            .then((data: unknown) => {
                suggestions.replaceChildren();
                const values = Array.isArray(data)
                    ? data.filter((value): value is string => typeof value === "string")
                    : [];

                if (values.length === 0) {
                    const empty = document.createElement("div");
                    empty.className = "search-suggestions__empty";
                    empty.textContent = "Sin sugerencias";
                    suggestions.append(empty);
                } else {
                    for (const value of values) {
                        const item = document.createElement("button");
                        item.type = "button";
                        item.className = "search-suggestions__item";
                        item.textContent = value;
                        item.addEventListener("click", () => {
                            input.value = value;
                            suggestions.hidden = true;
                            form.requestSubmit();
                        });
                        suggestions.append(item);
                    }
                }

                suggestions.hidden = false;
            })
            .catch((error: unknown) => {
                if (!(error instanceof DOMException && error.name === "AbortError")) {
                    console.error("No se pudieron cargar las sugerencias:", error);
                }
            });
    });

    input.addEventListener("blur", () => {
        window.setTimeout(() => {
            suggestions.hidden = true;
        }, 120);
    });

    input.addEventListener("focus", () => {
        if (suggestions.childElementCount > 0) {
            suggestions.hidden = false;
        }
    });
}

initializeImageZoom();
initializeSearchSuggestions();
