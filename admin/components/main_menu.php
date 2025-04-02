<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Principal</title>
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

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 200px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown-content a {
            display: block;
            padding: 10px;
            text-align: left;
            color: #1e7dbd;
            text-decoration: none;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .enlace_cierre_sesion img {
            width: 24px;
            height: 24px;
        }

        .texto-azul {
            color: blue;
        }
    </style>
</head>

<body>
    <nav
        style="text-align: center; max-width: 1100px; justify-content: center; margin: 0 auto; padding: 10px 0; display: flex; gap: 10px; flex-wrap: wrap; font-size: 1.15em; color: #1e7dbd; font-weight: bold;">
        <a href="./?lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link"
            style="text-align: center; margin-right: 10px;">Inicio</a>
        <a href="./?page=ships&lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link"
            style="text-align: center; margin-right: 10px;">Naves</a>

        <div class="dropdown">
            <a href="./?page=change_password&lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link">Cambiar
                contraseña</a>
            <div class="dropdown-content">
                <a href="./?page=password_generator&lang=<?= $_REQUEST['lang'] ?? 'gl' ?>" class="nav-link">Generador de
                    contraseñas</a>
            </div>
        </div>

        <a href="./admin/logout_session.php" class="enlace_cierre_sesion" style="display: flex; align-items: center;"
            title="Cerrar sesión">
            <img id="imagen-boton-cierre-sesion" src="./img/logout_icon.png" alt="Cerrar sesión">
            <span class="texto-azul" style="margin-left: 5px;"></span>
        </a>
    </nav>
</body>

</html>