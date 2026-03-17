<?php

declare(strict_types=1);

$currentLang = isset($_REQUEST["lang"]) ? (string) $_REQUEST["lang"] : "gl";
$currentLang = htmlspecialchars($currentLang, ENT_QUOTES, "UTF-8");
?>
<nav class="main-nav" role="navigation" aria-label="Menú principal">
    <a href="<?= BASE_URL .
        "?lang=" .
        $currentLang ?>" class="nav-link" aria-label="Ir a Inicio">Inicio</a>
    <a href="<?= BASE_URL .
        "?page=market_sections&lang=" .
        $currentLang ?>" class="nav-link" aria-label="Ir a Naves">Naves</a>

    <a href="<?= BASE_URL .
        "?page=change_password&lang=" .
        $currentLang ?>" class="nav-link" aria-label="Cambiar contraseña">Cambiar contraseña</a>
    <!-- <a href="<?= BASE_URL .
        "?page=password_generator&lang=" .
        $currentLang ?>" class="nav-link" aria-label="Ir a Generador de contraseñas">Generador de contraseñas</a> -->

    <a href="<?= BASE_URL .
        "?page=logout&lang=" .
        $currentLang ?>" class="nav-link enlace_cierre_sesion"
        aria-label="Cerrar sesión">
        <img src="<?= BASE_URL .
            "img/logout_icon.png" ?>" alt="Cerrar sesión" title="Cerrar sesión">
    </a>
</nav>