body {
    max-width: 80%;
    margin: 0 auto;
    padding: 1em;
    font-family: Arial, sans-serif;
    background-color: var(--admin-bg);
    color: var(--admin-text);
}

main.maps {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem 1rem;
    max-width: 1200px;
    margin: 0 auto;
    gap: 1.5rem;
}

main.maps h2 {
    text-align: center;
    margin-bottom: 0.5rem;
    color: var(--admin-heading);
}

.maps-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2.5rem;
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
}

.maps-grid figure {
    margin: 0;
    border-radius: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    background: var(--admin-map-card-bg);
    overflow: hidden;
    box-shadow: var(--admin-map-card-shadow);
    border: 1px solid var(--admin-border);
}

.maps-grid figure img {
    width: 100%;
    max-width: 350px;
    min-height: 220px;
    border-radius: 16px 16px 0 0;
    object-fit: cover;
    display: block;
}

.maps-grid figure figcaption {
    padding: 1rem;
    text-align: center;
    font-size: 1.1rem;
    background: var(--admin-map-caption-bg);
    width: 100%;
    box-sizing: border-box;
    color: var(--admin-text);
    border-top: 1px solid var(--admin-border);
}

.maps-grid figure:focus,
.maps-grid figure:hover {
    outline: 2px solid var(--admin-map-outline);
    transform: scale(1.04);
    cursor: pointer;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.18);
}

.zoomed-container {
    display: none;
    position: fixed;
    z-index: 1000;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: var(--admin-overlay-background);
    justify-content: center;
    align-items: center;
    padding: 1rem;
    box-sizing: border-box;
    overflow: auto;
}

.zoomed-container.show {
    display: flex;
}

.zoomed-container img {
    max-width: 96vw;
    max-height: 90vh;
    border-radius: 16px;
    background: var(--admin-surface);
    box-shadow: var(--admin-overlay-shadow);
}

.zoomed-container figcaption {
    color: var(--admin-overlay-text);
    margin-top: 1rem;
    font-size: 1.3rem;
    text-align: center;
}

@media (max-width: 1100px) {
    .maps-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }
}

@media (max-width: 700px) {
    body {
        max-width: 100%;
        padding: 0.75rem;
    }

    .maps-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .maps-grid figure img {
        max-width: 98vw;
        min-height: 200px;
    }
}
