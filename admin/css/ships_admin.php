<style>
    .maps {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        padding: 20px;

        img {
            /* width: 100%; */
            height: auto;
            border-radius: 5px;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        h2 {
            grid-column: 1 / -1;
            text-align: center;
        }

        figure {
            margin: 0;
            padding: 0;
            text-align: center;
            position: relative;
            cursor: pointer;
        }

        figcaption {
            margin-top: 10px;
            font-size: .5rem;
        }
    }


    .zoom {
        transition: transform 0.2s;
    }

    /* Estilo para la imagen en zoom centrada */
    .zoomed-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.8);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        visibility: hidden;
        opacity: 0;
        transition: visibility 0s, opacity 0.3s ease;
        line-height: 0.003;
    }

    /* Hacemos la imagen más grande */
    .zoomed-container img {
        max-width: 95%;
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

    .zoomed-container figcaption {
        color: white;
        margin-top: 37px;
        font-size: 1.5rem;
        text-align: center;
    }

    .zoomed-container::before {
        content: '×';
        position: absolute;
        top: 10px;
        right: 30px; /* Separado hacia la izquierda del borde derecho */
        font-size: 2rem;
        color: white;
        cursor: pointer;
        line-height: 1;
    }

    @media screen and (max-width: 768px) {
        .maps {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
    }

    @media screen and (max-width: 480px) {
        .maps {
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        }
    }
</style>