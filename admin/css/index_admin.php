<?php
// Source: https://codepen.io/AllThingsSmitty/pen/MyqmdM
?>

<style>
    /* Estilos generales */
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

    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 20px;
    }

    .pagination a {
        padding: 5px 10px;
        border: 1px solid #1e7dbd;
        text-decoration: none;
        color: #1e7dbd;
    }

    .pagination a.active {
        background-color: #1e7dbd;
        color: white;
        border-color: #1e7dbd;
        font-weight: bold;
        border-radius: 5px;
    }

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