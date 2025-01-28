<style>
    /* Estilo para la imagen ampliada */
    .zoomed-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        visibility: hidden;
        opacity: 0;
        transition: visibility 0s, opacity 0.3s ease;
    }

    .zoomed-container img {
        max-width: 90%;
        max-height: 90%;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.8);
    }

    .zoomed-container.show {
        visibility: visible;
        opacity: 1;
    }

    .zoomed-container img:hover {
        transform: scale(1.05);
    }

    .zoomable {
        cursor: pointer;
    }

    .zoomed-container::before {
        content: '×';
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 2rem;
        color: white;
        cursor: pointer;
    }
</style>