<?php
require_once(HELPERS . 'get_language.php');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de administración</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel='icon' href='./img/favicon.png' type='image/png'>

    <!-- Iconos para dispositivos Apple -->
    <link rel="apple-touch-icon" sizes="180x180" href="./img/apple-touch-icon-180x180.png">
    <link rel="apple-touch-icon" sizes="152x152" href="./img/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="120x120" href="./img/apple-touch-icon-120x120.png">

    <!-- Icono para Android (PWA) -->
    <link rel="icon" sizes="192x192" href="icon-192x192.png">

    <!-- Manifesto Web (PWA) -->
    <link rel="manifest" href="/manifest.json">

    <style>
        #cabecera_pagina_edicion {
            font-size: 2.5rem;
            margin: 0;
            padding: 10px;
        }
    </style>
</head>

<body class="container">
    <header style="display:flex; justify-content: space-around">
        <h1 id="cabecera_pagina_edicion">Admin: PraceAR
            <strong style="font-size: 0.95rem">Idioma actual: <img style="box-shadow: 0 0 2px 1px black;" width="15"
                    height="15" src="<?= FLAG_IMAGES_URL . (get_language()) . ".png" ?>"
                    alt="<?= get_language() ?>"></strong>
        </h1>
        <?php
        require_once(COMPONENT_ADMIN . "languages.php");
        ?>
    </header>
    <header>
        <?php
        require_once(COMPONENT_ADMIN . "main_menu.php");
        ?>
    </header>
</body>