<?php
declare(strict_types=1);
?>
<nav class="language-selector" aria-label="Selector de idioma">
    <ul class="flags">
        <?php
        $current_file = basename($_SERVER['SCRIPT_NAME']);
        $current_path = $_SERVER['PHP_SELF'];
        foreach (LANGUAGES as $key_language => $text_language): ?>
            <li>
                <?php
                $params = [];
                // Usar la ruta principal index.php para el router
                if ($current_file === 'index.php') {
                    $url_file = $current_path;
                    $params['lang'] = $key_language;
                    if (isset($_REQUEST['page'])) $params['page'] = $_REQUEST['page'];
                    if (isset($_REQUEST['caseta'])) $params['caseta'] = $_REQUEST['caseta'];
                    if (isset($_REQUEST['id'])) $params['id'] = $_REQUEST['id'];
                    if (isset($_REQUEST['codigo_idioma'])) $params['codigo_idioma'] = $key_language;
                    // Si estamos en la edición de traducción desde index.php, redirige a index.php?page=language
                    if (isset($_REQUEST['page']) && $_REQUEST['page'] === 'language' && isset($_REQUEST['id'])) {
                        $url_file = '/appventurers/index.php';
                        $params = [
                            'page' => 'language',
                            'id' => $_REQUEST['id'],
                            'codigo_idioma' => $key_language
                        ];
                    }
                } elseif ($current_file === 'edit.php') {
                    $url_file = $current_path;
                    $params['lang'] = $key_language;
                    if (isset($_REQUEST['id'])) $params['id'] = $_REQUEST['id'];
                } elseif ($current_file === 'edit_translations.php') {
                    // Siempre redirige a index.php?page=language para edición de traducciones
                    $url_file = '/appventurers/index.php';
                    $params['page'] = 'language';
                    $params['id'] = $_REQUEST['id'] ?? '';
                    $params['codigo_idioma'] = $key_language;
                }
                $url = $url_file;
                if (!empty($params)) {
                    $url .= '?' . http_build_query($params);
                }
                ?>
                <a href="<?= $url ?>" aria-label="<?= $text_language ?>" tabindex="0">
                    <img class="language-flag" width="15" height="15"
                        src="<?= FLAG_IMAGES_URL . "$key_language.png" ?>" alt="<?= $text_language ?>">
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
