const zoomedContainer = document.getElementById("zoomed-container");
const zoomedImage = document.getElementById("zoomed-image");
const zoomedCaption = document.getElementById("zoomed-caption");
const zoomedClose = document.getElementById("zoomed-close");

function isHTMLElement(element: Element | null): element is HTMLElement {
    return element instanceof HTMLElement;
}

function isHTMLImageElement(element: Element | null): element is HTMLImageElement {
    return element instanceof HTMLImageElement;
}

function openZoom(figure: HTMLElement): void {
    if (!isHTMLElement(zoomedContainer) || !isHTMLImageElement(zoomedImage) || !isHTMLElement(zoomedCaption)) {
        return;
    }

    const image = figure.querySelector("img");
    const caption = figure.querySelector("figcaption");

    if (!isHTMLImageElement(image) || !isHTMLElement(caption)) {
        return;
    }

    zoomedImage.src = image.src;
    zoomedImage.alt = image.alt;
    zoomedCaption.textContent = caption.textContent ?? "";

    zoomedContainer.classList.add("show");
    zoomedContainer.setAttribute("aria-hidden", "false");
}

function closeZoom(): void {
    if (!isHTMLElement(zoomedContainer) || !isHTMLImageElement(zoomedImage)) {
        return;
    }

    zoomedContainer.classList.remove("show");
    zoomedContainer.setAttribute("aria-hidden", "true");
    zoomedImage.src = "";
}

function attachFigureEvents(figure: HTMLElement): void {
    if (figure.dataset.zoomBound === "true") {
        return;
    }

    figure.addEventListener("click", () => {
        openZoom(figure);
    });

    figure.addEventListener("keydown", event => {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            openZoom(figure);
        }
    });

    figure.dataset.zoomBound = "true";
}

const figures = document.querySelectorAll<HTMLElement>(".maps-grid figure");
figures.forEach(attachFigureEvents);

if (isHTMLElement(zoomedContainer)) {
    zoomedContainer.addEventListener("click", event => {
        if (event.target === zoomedContainer || event.target === zoomedImage) {
            closeZoom();
        }
    });
}

if (zoomedClose instanceof HTMLButtonElement) {
    zoomedClose.addEventListener("click", () => {
        closeZoom();
    });
}

export { };
