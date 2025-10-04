<style>
     html, body {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background: #f8f9fa;
        }

        body {
            max-width: 80%;
            margin: 0 auto;
            padding: 1em;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }

        main.maps {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        main.maps h2 {
            text-align: center;
            margin-bottom: 2rem;
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
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border-radius: 16px;
            transition: transform 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #fff;
            overflow: hidden;
        }

        .maps-grid figure img {
            width: 100%;
            max-width: 350px;
            min-height: 220px;
            /* aspect-ratio: 16/10; */
            border-radius: 16px 16px 0 0;
            object-fit: cover;
            display: block;
        }

        .maps-grid figure figcaption {
            padding: 1rem 1rem;
            text-align: center;
            font-size: 1.1rem;
            background: #f8f9fa;
            border-radius: 0 0 16px 16px;
            width: 100%;
            box-sizing: border-box;
            color: #333;
        }

        .maps-grid figure:focus,
        .maps-grid figure:hover {
            outline: 2px solid #0078d4;
            transform: scale(1.04);
            cursor: pointer;
        }

        /* Zoomed container styles */
        .zoomed-container {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100vw;
            background: rgba(0, 0, 0, 0.8);
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
            background: #fff;
            box-shadow: 0 4px 32px rgba(0,0,0,0.25);
        }
        .zoomed-container figcaption {
            color: #fff;
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
            .maps-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            .maps-grid figure img {
                max-width: 98vw;
                min-height: 160px;
            }
        }

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