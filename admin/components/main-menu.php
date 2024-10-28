<nav style="display: flex; justify-content: space-around; align-items: center;">
    <a href="./?lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link"
        style="text-align: center; margin-right: 10px;">Inicio</a>
    <a href="./?page=ships&lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link"
        style="text-align: center; margin-right: 10px;">Naves</a>
    <a href="./?page=change_password&lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link"
        style="text-align: center; margin-right: 10px;">Cambiar contraseña</a>
    <a href="./admin/logout_session.php" class="enlace_cierre_sesion" style="display: flex; align-items: center;"
        title="Cerrar sesión">
        <img id="imagenBotonCierreSesion" src="./img/logout_icon.png" alt="Cerrar sesión">
        <span class="texto-azul" style="margin-left: 5px;"></span>
    </a>
</nav>

<style>
    .nav-link {
        text-decoration: none;
        color: inherit;
        position: relative;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 2px;
        bottom: -2px;
        left: 0;
        background-color: currentColor;
        visibility: hidden;
        transform: scaleX(0);
        transition: all 0.3s ease-in-out;
    }

    .nav-link:hover::after {
        visibility: visible;
        transform: scaleX(1);
    }

    .enlace_cierre_sesion img {
        width: 24px;
        height: 24px;
    }

    .texto-azul {
        color: blue;
    }
</style>