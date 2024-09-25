<style>
    /* Estilos generales */
    body {
        font-family: "Open Sans", sans-serif;
        line-height: 1.25;
        margin: 0;
        padding: 0;
    }

    /* Estilos de tablas */
    table {
        border: 1px solid #ccc;
        border-collapse: collapse;
        margin: 0;
        padding: 0;
        width: 100%;
        table-layout: fixed;
    }

    table caption {
        font-size: 1.5em;
        margin: .5em 0 .75em;
    }

    table tr {
        background-color: #f8f8f8;
        border: 1px solid #ddd;
        padding: .35em;
    }

    table th,
    table td {
        padding: .625em;
        text-align: center;
    }

    table th {
        font-size: .85em;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    @media screen and (max-width: 600px) {
        table {
            border: 0;
        }

        table caption {
            font-size: 1.3em;
        }

        table thead {
            border: none;
            clip: rect(0 0 0 0);
            height: 1px;
            margin: -1px;
            overflow: hidden;
            padding: 0;
            position: absolute;
            width: 1px;
        }

        table tr {
            border-bottom: 3px solid #ddd;
            display: block;
            margin-bottom: .625em;
        }

        table td {
            border-bottom: 1px solid #ddd;
            display: block;
            font-size: .8em;
            text-align: right;
            position: relative;
            padding-left: 50%;
        }

        table td::before {
            /*
            * aria-label has no advantage, it won't be read inside a table
            content: attr(aria-label);
            */
            content: attr(data-label);
            position: absolute;
            left: 0;
            width: 45%;
            padding-left: .625em;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
        }

        table td:last-child {
            border-bottom: 0;
        }
    }

    /* Estilos del contenedor flotante para la imagen y nombre ampliados */
    .zoomed-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow-y: auto;
        background-color: rgba(0, 0, 0, 0.8);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
        box-sizing: border-box;
        visibility: hidden;
        opacity: 0;
        transition: visibility 0s, opacity 0.3s ease;
        z-index: 9999;
    }

    .zoomed-container.show {
        visibility: visible;
        opacity: 1;
    }

    .zoomed-container img {
        max-width: 100%;
        max-height: 80vh;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
        transition: transform 0.3s ease;
    }

    .zoomed-container img:hover {
        transform: scale(1.05);
    }

    .zoomed-container p {
        color: white;
        font-size: 1.5rem;
        margin-top: 15px;
        text-align: center;
        max-width: 90%;
    }

    .zoomable {
        cursor: pointer;
    }

    .zoomed-container .close-button {
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 2rem;
        color: white;
        cursor: pointer;
    }

    @media (max-width: 600px) {
        .zoomed-container p {
            font-size: 1.2rem;
        }

        .zoomed-container .close-button {
            font-size: 1.5rem;
            top: 5px;
            right: 10px;
        }
    }

    /* Estilos de paginación */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 20px;
        flex-wrap: wrap;
        /* Permite que los elementos se envuelvan en pantallas pequeñas */
    }

    .pagination a {
        padding: 8px 12px;
        border: 1px solid #1e7dbd;
        text-decoration: none;
        color: #1e7dbd;
        border-radius: 4px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .pagination a:hover {
        background-color: #1e7dbd;
        color: white;
    }

    .pagination a.active {
        background-color: #1e7dbd;
        color: white;
        border-color: #1e7dbd;
        font-weight: bold;
        border-radius: 5px;
    }

    @media (max-width: 600px) {
        .pagination {
            gap: 5px;
        }

        .pagination a {
            padding: 6px 8px;
            font-size: 0.9em;
        }
    }

    /* Estilos del menú de navegación (banderas de selección de idioma) */
    .nav-menu {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding: 10px 20px;
        background-color: #f0f0f0;
    }

    .nav-menu ul {
        list-style: none;
        display: flex;
        gap: 10px;
        margin: 0;
        padding: 0;
    }

    .nav-menu li {
        display: flex;
    }

    .nav-menu a {
        display: block;
        width: 32px;
        height: 32px;
    }

    .nav-menu img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 4px;
        transition: transform 0.3s ease;
    }

    .nav-menu img:hover {
        transform: scale(1.1);
    }

    @media (max-width: 600px) {
        .nav-menu {
            justify-content: center;
            padding: 10px;
        }

        .nav-menu ul {
            flex-wrap: wrap;
            gap: 8px;
        }

        .nav-menu a {
            width: 28px;
            height: 28px;
        }

        .nav-menu img:hover {
            transform: scale(1.05);
        }
    }

    /* Estilos adicionales para tablas específicas */
    .tabla_puestos {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .tabla_puestos caption {
        font-size: 1.75rem;
        font-weight: bold;
    }

    .tabla_puestos thead {
        font-size: .95em;
    }

    .tabla_puestos tbody {
        font-size: .9em;
    }

    .tabla_puestos th,
    .tabla_puestos td {
        padding: 10px;
        text-align: center;
    }


    .different-background-color {
        background-color: lightblue;
        color: black;
    }
</style>