<?php
require_once(HELPERS . 'get_language.php');
?>

<header class="admin-header" role="banner">
    <div class="admin-header__top">
        <h1 id="cabecera_pagina_edicion" tabindex="0">Admin: PraceAR
            <strong class="admin-header__language">Idioma actual:
                <img class="language-flag current-language-flag" width="15" height="15"
                    src="<?= FLAG_IMAGES_URL . (get_language()) . ".png" ?>" alt="<?= get_language() ?>" tabindex="0">
            </strong>
        </h1>
        <?php require_once(COMPONENT_ADMIN . "languages.php"); ?>
    </div>
    <div id="cabecera-menu-navegacion" class="admin-header__menu">
        <?php require_once(COMPONENT_ADMIN . "main_menu.php"); ?>
    </div>
    <div class="admin-header__darkmode">
        <button id="toggle-darkmode" aria-label="Cambiar modo oscuro"
            style="background:none;border:none;cursor:pointer;font-size:1.5rem;">
            <span id="darkmode-icon" title="Cambiar modo oscuro">🌙</span>
        </button>
    </div>
</header>
