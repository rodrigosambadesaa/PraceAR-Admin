<nav class="main-nav" role="navigation" aria-label="Menú principal">
    <a href="./?lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link" aria-label="Ir a Inicio">Inicio</a>
    <a href="./?page=ships&lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link" aria-label="Ir a Naves">Naves</a>

    <div class="dropdown" role="menu" aria-label="Opciones de contraseña">
        <a href="./?page=change_password&lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link"
            aria-haspopup="true" aria-expanded="false">Cambiar contraseña</a>
        <div class="dropdown-content" role="menu">
            <a href="./?page=password_generator&lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link"
                role="menuitem" aria-label="Ir a Generador de contraseñas">Generador de contraseñas</a>
        </div>
    </div>

    <a href="./?page=logout&lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link enlace_cierre_sesion"
        aria-label="Cerrar sesión">
        <img src="./img/logout_icon.png" alt="Cerrar sesión" title="Cerrar sesión">
    </a>
</nav>
