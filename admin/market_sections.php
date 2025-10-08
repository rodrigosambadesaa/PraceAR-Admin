<?php
declare(strict_types=1);
require_once __DIR__ . '/../constants.php';
?>
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
    <link rel="manifest" href="/appventurers/manifest.json">

    <style>
        <?php
            require_once(CSS_ADMIN . 'theme.css');
            require_once(CSS_ADMIN . 'header.css');
            require_once(CSS_ADMIN . 'market_sections.php');
        ?>

        /* ...existing code... */

        /* Botón de cerrar imagen ampliada */
        .zoomed-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 2.5rem;
            color: #fff;
            background: rgba(0,0,0,0.5);
            border: none;
            border-radius: 50%;
            width: 2.5em;
            height: 2.5em;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            transition: background 0.2s, color 0.2s;
        }

        .zoomed-close:hover,
        .zoomed-close:focus {
            background: #e53935;
            color: #fff;
            outline: none;
        }

        /* Modo claro: asegúrate que la cruz sea visible */
        body:not(.darkmode) .zoomed-close {
            color: #222;
            background: rgba(255,255,255,0.85);
        }

        body:not(.darkmode) .zoomed-close:hover,
        body:not(.darkmode) .zoomed-close:focus {
            background: #e53935;
            color: #fff;
        }
/* ...existing code... */
    </style>
    <link rel="stylesheet" href="./css/darkmode.css">
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
        <button id="zoomed-close" class="zoomed-close" aria-label="Cerrar imagen ampliada">&times;</button>
        <figure>
            <img id="zoomed-image" src="" alt="">
            <figcaption id="zoomed-caption"></figcaption>
        </figure>
    </div>

    <script type="module" src="<?= JS_ADMIN . '/market_sections.js' ?>"></script>
    <script src="<?= JS . '/helpers/dark_mode.js' ?>" defer></script>

</body>

</html>
