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
        right: 30px;
        font-size: 2rem;
        color: white;
        cursor: pointer;
    }

    #eliminar-imagen-link {
        margin-top: 1em;
        color: red;
        text-decoration: none;
        /* Hacer que se muestre debajo de la imagen */
        display: block;
    }

    .required::after {
        content: " *";
        color: red;
    }

    .note {
        color: red;
        text-align: center;
    }

    /* Mejorar contraste
    body {
        background-color: #f9f9f9;
        color: #333;
    } */

    input[type="submit"] {
        background-color: #007bff;
        color: #fff;
    }

    input[type="submit"]:hover {
        background-color: #0056b3;
    }

    @media screen and (max-width: 600px) {
        .nav-link {
            font-size: 0.9em;
        }

        .dropdown-content {
            min-width: 150px;
        }

        .enlace_cierre_sesion img {
            width: 20px;
            height: 20px;
        }
        .zoomed-container img {
            max-width: 80%;
            max-height: 80%;
        }

        /* Limitar el tamaño de los elementos del forulario */
        .form-group {
            max-width: 90%;
            margin: 0 auto;
        }
        .form-group input[type="text"],
        .form-group input[type="file"] {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        .form-group input[type="submit"] {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        .form-group input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .form-group label {
            font-size: 0.9em;
        }
        .form-group .note {
            font-size: 0.8em;
        }
        .form-group .required::after {
            font-size: 0.8em;
        }
    }
</style>