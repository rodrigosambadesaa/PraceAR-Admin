<nav class="language-selector" aria-label="Selector de idioma">
    <ul class="flags">
        <?php foreach (LANGUAGES as $key_language => $text_language): ?>
            <li>
                <?php
                $page = isset($_REQUEST['page']) ? "&page=" . $_REQUEST['page'] : '';
                $caseta = isset($_REQUEST['caseta']) ? "&caseta=" . $_REQUEST['caseta'] : '';
                $codigo_idioma = isset($_REQUEST['codigo_idioma']) ? "&codigo_idioma=" . $_REQUEST['codigo_idioma'] : '';
                $id = isset($_REQUEST['id']) ? "&id=" . $_REQUEST['id'] : '';
                ?>
                <a href="<?= "?lang=$key_language$caseta$page$id$codigo_idioma" ?>" aria-label="<?= $text_language ?>"
                    tabindex="0">
                    <img class="language-flag" width="15" height="15"
                        src="<?= FLAG_IMAGES_URL . "$key_language.png" ?>" alt="<?= $text_language ?>">
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
