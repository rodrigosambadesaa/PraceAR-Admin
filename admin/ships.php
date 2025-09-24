<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - PraceAR - Mapas de las Ameas, Naves y Murallones</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel='icon' href='./img/favicon.png' type='image/png'>
    <link rel="apple-touch-icon" sizes="180x180" href="./img/apple-touch-icon-180x180.png">
    <link rel="apple-touch-icon" sizes="152x152" href="./img/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="120x120" href="./img/apple-touch-icon-120x120.png">
    <link rel="icon" sizes="192x192" href="icon-192x192.png">
    <link rel="manifest" href="/manifest.json">

    <style>
        <?php require_once(CSS_ADMIN . 'header.css'); ?>

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
    </style>
    <?php require_once(CSS_ADMIN . 'ships_admin.php'); ?>
</head>

<body>
    <?php require_once(COMPONENT_ADMIN . 'sections' . DIRECTORY_SEPARATOR . 'header.php'); ?>

    <main class="maps">
        <h2>Mapas de las Ameas, Naves y Murallones</h2>
        <div class="maps-grid">
            <?php
            // Recoger todas las imágenes en un solo array (máximo 12)
            $imagenes = [];
            foreach (NAVES['ameas'] as $amea) {
                $imagenes[] = [
                    'src' => './img/amea' . $amea['indice'] . '.jpg',
                    'alt' => 'Imagen de Amea ' . $amea['indice'],
                    'caption' => 'Amea ' . $amea['indice'] . ' / ' . implode("-", $amea['range'])
                ];
            }
            foreach (NAVES['naves'] as $nave) {
                $imagenes[] = [
                    'src' => './img/nave' . $nave['indice'] . '.jpg',
                    'alt' => 'Imagen de Nave ' . $nave['indice'],
                    'caption' => 'Nave ' . $nave['indice'] . ' / ' . implode("-", $nave['range'])
                ];
            }
            foreach (NAVES['murallones'] as $murallon) {
                $imagenes[] = [
                    'src' => './img/murallon' . $murallon['indice'] . '.jpg',
                    'alt' => 'Imagen de Murallón ' . $murallon['indice'],
                    'caption' => 'Murallón ' . $murallon['indice'] . ' / ' . implode("-", $murallon['range'])
                ];
            }
            $imagenes = array_slice($imagenes, 0, 12); // Solo 12 imágenes
            foreach ($imagenes as $img): ?>
                <figure class="zoom" tabindex="0" role="button" aria-label="Ampliar <?= htmlspecialchars($img['alt']) ?>">
                    <img loading="lazy" src="<?= htmlspecialchars($img['src']) ?>" alt="<?= htmlspecialchars($img['alt']) ?>">
                    <figcaption><?= htmlspecialchars($img['caption']) ?></figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    </main>

    <div id="zoomed-container" class="zoomed-container" role="dialog" aria-hidden="true"
        aria-labelledby="zoomed-caption">
        <img id="zoomed-image" src="" alt="">
        <figcaption id="zoomed-caption"></figcaption>
    </div>

    <script>
        const figures = document.querySelectorAll('.maps-grid figure');
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
                zoomedContainer.setAttribute('aria-hidden', 'false');
            });
        });

        zoomedContainer.addEventListener('click', function (event) {
            if (event.target === zoomedContainer || event.target === zoomedImage) {
                zoomedContainer.classList.remove('show');
                zoomedContainer.setAttribute('aria-hidden', 'true');
            }
        });
    </script>

</body>

</html>
