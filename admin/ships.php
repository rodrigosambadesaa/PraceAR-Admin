<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - PraceAR - Mapas de las Ameas, Naves y Murallones</title>
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
</head>

<body>
    <header>
        <?php
        require_once(COMPONENT_ADMIN . 'sections' . DIRECTORY_SEPARATOR . 'header.php');
        require_once(CSS_ADMIN . 'ships_admin.php');
        ?>
    </header>

    <main class="maps">
        <h2>Mapas de las Ameas, Naves y Murallones</h2>
        <!-- Sección Ameas -->
        <?php foreach (NAVES['ameas'] as $amea): ?>
            <?php
            $amea_range = $amea['range'];
            $amea_title = $amea['title'];
            $amea_indice = $amea['indice'];
            ?>
            <figure class="zoom">
                <img loading="lazy" src='./img/amea<?= $amea_indice ?>.jpg' alt='Amea <?= $amea_indice ?>'>
                <figcaption>Amea <?= $amea_indice ?> / <?= implode("-", $amea_range) ?></figcaption>
            </figure>
        <?php endforeach; ?>

        <!-- Sección Naves -->
        <?php foreach (NAVES['naves'] as $nave): ?>
            <?php
            $nave_range = $nave['range'];
            $nave_title = $nave['title'];
            $nave_indice = $nave['indice'];
            ?>
            <figure class="zoom">
                <img loading="lazy" src='./img/nave<?= $nave_indice ?>.jpg' alt='Nave <?= $nave_indice ?>'>
                <figcaption>Nave <?= $nave_indice ?> / <?= implode("-", $nave_range) ?></figcaption>
            </figure>
        <?php endforeach; ?>

        <!-- Sección Murallones -->
        <?php foreach (NAVES['murallones'] as $murallon): ?>
            <?php
            $murallon_range = $murallon['range'];
            $murallon_title = $murallon['title'];
            $murallon_indice = $murallon['indice'];
            ?>
            <figure class="zoom">
                <img loading="lazy" src='./img/murallon<?= $murallon_indice ?>.jpg' alt='Murallón <?= $murallon_indice ?>'>
                <figcaption>Murallón <?= $murallon_indice ?> / <?= implode("-", $murallon_range) ?></figcaption>
            </figure>
        <?php endforeach; ?>
    </main>

    <!-- Contenedor para mostrar la imagen ampliada y el texto -->
    <div id="zoomed-container" class="zoomed-container">
        <img id="zoomed-image" src="" alt="">
        <figcaption id="zoomed-caption"></figcaption>
    </div>

    <script>
        const figures = document.querySelectorAll('figure');
        const zoomedContainer = document.getElementById('zoomed-container');
        const zoomedImage = document.getElementById('zoomed-image');
        const zoomedCaption = document.getElementById('zoomed-caption');

        figures.forEach(figure => {
            figure.addEventListener('click', function () {
                const img = figure.querySelector('img');
                const caption = figure.querySelector('figcaption');
                zoomedImage.src = img.src;
                zoomedCaption.textContent = caption.textContent;
                zoomedContainer.classList.add('show');
            });
        });

        zoomedContainer.addEventListener('click', function () {
            zoomedContainer.classList.remove('show');
        });
    </script>

</body>

</html>