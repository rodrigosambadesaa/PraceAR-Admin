<nav class="main-nav" role="navigation" aria-label="Menú principal">
    <a href="./?lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link" aria-label="Ir a Inicio">Inicio</a>
    <a href="./?page=market_sections&lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link" aria-label="Ir a Naves">Naves</a>

    <a href="./?page=change_password&lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link" aria-label="Cambiar contraseña">Cambiar contraseña</a>
    <a href="./?page=password_generator&lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link" aria-label="Ir a Generador de contraseñas">Generador de contraseñas</a>

    <a href="./?page=logout&lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link enlace_cierre_sesion"
        aria-label="Cerrar sesión">
        <img src="./img/logout_icon.png" alt="Cerrar sesión" title="Cerrar sesión">
    </a>
</nav>
