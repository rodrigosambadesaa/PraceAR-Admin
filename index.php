<?php
declare(strict_types=1);
ob_start();
session_start();

require_once('./constants.php');
require_once(CONNECTION);

// Verificar si el usuario ha iniciado sesión
if (isset($_SESSION['login'])) {

    // Capturar el valor de la página a través de la URL (si está presente) y establecer un valor predeterminado
    $page = $_REQUEST['page'] ?? 'index';

    // Capturar el número de página para la paginación si está presente en la URL
    $current_page = isset($_GET['page_number']) ? (int) $_GET['page_number'] : 1;

    // Switch para cargar la página correcta según la URL
    switch ($page) {
        case 'index':
            // Pasar la página actual como parámetro a la página index.php para mantener la paginación
            $_GET['page_number'] = $current_page;
            require_once(ADMIN . 'index.php');
            break;

        case 'ships':
            // Pasar el número de página actual a la página de naves
            $_GET['page_number'] = $current_page;
            require_once(ADMIN . 'ships.php');
            break;

        case 'change_password':
            // Pasar el número de página actual a la página de cambio de contraseña
            $_GET['page_number'] = $current_page;
            require_once(ADMIN . 'change_password.php');
            break;

        case 'password_generator':
            // Pasar el número de página actual a la página del generador de contraseñas
            $_GET['page_number'] = $current_page;
            require_once(ADMIN . 'password_generator.php');
            break;

        case 'edit':
            require_once(ADMIN . 'edit.php');
            break;

        case 'language':
            require_once(ADMIN . 'edit_translations.php');
            break;

        case 'logout':
            // Incluir el archivo de cierre de sesión y redirigir al usuario a la página de inicio
            require_once(ADMIN . 'logout_session.php');
            break;

        default:
            // Si no se encuentra la página, redirigir a la página de inicio
            require_once(ADMIN . 'index.php');
            break;
    }

} else {
    // Si no se ha iniciado sesión, redirigir al formulario de login
    require_once("./login.php");
}
?>