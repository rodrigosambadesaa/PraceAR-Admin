<?php
declare(strict_types=1);
?>
<!DOCTYPE html>

<html lang="es">
    
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin - PraceAR - Editar Traducciones de Puesto - Página de administración</title>
        <style>
        <?php
            require_once(CSS_ADMIN . 'theme.css');
            require_once(CSS_ADMIN . 'header.css');
        ?>
        /* Máximo tamaño del body */
        body {
            max-width: 90vw;
            margin: 0 auto;
            padding: 1em;
            font-family: Arial, sans-serif;
            background-color: var(--admin-bg);
            color: var(--admin-text);
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        /* Formulario y contenido centrado */
        form {
            width: 100%;
            max-width: 600px;
            margin: 2em auto 0 auto;
            padding: 1.5em;
            background-color: var(--admin-surface);
            border-radius: 8px;
            box-shadow: var(--admin-card-shadow);
            border: 1px solid var(--admin-border);
            display: flex;
            flex-direction: column;
            gap: 1em;
        }

        label {
            display: block;
            margin-bottom: 0.5em;
            font-weight: bold;
            color: var(--admin-text-muted);
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 0.5em;
            margin-bottom: 1em;
            border: 1px solid var(--admin-border);
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 1em;
            background-color: var(--admin-surface-muted);
            color: var(--admin-text);
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 3px var(--admin-primary-soft);
        }

        h2 {
            text-align: center;
            margin-top: 1.5em;
            margin-bottom: 1em;
            color: var(--admin-heading);
        }

        p[style] {
            text-align: center !important;
        }

        @media (max-width: 700px) {
            body {
                max-width: 100vw;
                padding: 0.5em;
            }
            form {
                max-width: 100%;
                padding: 1em;
            }
            h2 {
                font-size: 1.2em;
            }
        }

        @media (max-width: 480px) {
            form {
                padding: 0.5em;
            }
            input[type="text"], textarea {
                font-size: 0.95em;
            }
        }

    </style>
    <link rel="stylesheet" href="./css/darkmode.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel='icon' href='./img/favicon.png' type='image/png'>

    <!-- Iconos para dispositivos Apple -->
    <link rel="apple-touch-icon" sizes="180x180" href="./img/apple-touch-icon-180x180.png">
    <link rel="apple-touch-icon" sizes="152x152" href="./img/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="120x120" href="./img/apple-touch-icon-120x120.png">

    <!-- Icono para Android (PWA) -->
    <link rel="icon" sizes="192x192" href="icon-192x192.png">

    <!-- Manifesto Web (PWA) -->
    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">

    <style>
        #descripcion {
            min-height: 1em;
            max-height: 7.75em;
            height: auto;
            letter-spacing: 2.97px;
        }

        @media screen and (max-width: 600px) {
            #descripcion {
                min-height: 1em;
                max-height: 7.75em;
                height: auto;
                letter-spacing: 2.97px;
            }

            .pure-form input[type="text"],
            .pure-form input[type="file"] {
                width: 100%;
            }

            .pure-form input[type="submit"] {
                width: 100%;
            }

        }
    </style>
</head>

<body>
    <?php
    require_once(COMPONENT_ADMIN . 'sections' . DIRECTORY_SEPARATOR . 'header.php');
    require_once 'connection.php';
    require_once(HELPERS . 'update_stalls_translations.php');

    $codigo_idioma = filter_input(INPUT_GET, 'codigo_idioma', FILTER_SANITIZE_STRING);
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    $sql_seleccion = "SELECT id, tipo, descripcion FROM puestos_traducciones WHERE codigo_idioma = ? AND puesto_id = ?";
    $stmt = $conexion->prepare($sql_seleccion);
    $stmt->bind_param('si', $codigo_idioma, $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $puesto_encontrado = false;

    $data = $resultado->fetch_assoc();
    if (!$data) {
        die('<h2 style="text-align: center;">No se encontró la traducción. <a href="index.php">Volver</a></h2>');
    }

    $puesto_encontrado = true;

    ?>

    <h2 style="text-align: center;">Traducción del puesto<span class="admin-accent">
            <?php
            // Obtener el nombre del puesto
            $sql_nombre_puesto = "SELECT nombre FROM puestos WHERE id = ?";
            $stmt_nombre_puesto = $conexion->prepare($sql_nombre_puesto);
            $stmt_nombre_puesto->bind_param('i', $id);
            $stmt_nombre_puesto->execute();

            $resultado_nombre_puesto = $stmt_nombre_puesto->get_result();
            $nombre_puesto = $resultado_nombre_puesto->fetch_assoc();

            echo htmlspecialchars($nombre_puesto['nombre']);
            ?>
        </span>
    </h2>
    <?php

    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    ?>

    <form class="pure-form" action="#" method="POST" id="formulario" aria-labelledby="formulario-titulo">
        <input type="hidden" name="csrf" value="<?= isset($_SESSION['csrf']) ? $_SESSION['csrf'] : '' ?>">
        <label for="tipo">Tipo <span class="admin-required" aria-hidden="true">*</span></label>
        <input type="text" id="tipo" name="tipo" value="<?= htmlspecialchars($data['tipo'] ?? "") ?>"
            placeholder="Tipo de puesto. Por ejemplo: 'Bisutería'" required aria-required="true"
            aria-describedby="tipo-descripcion">
        <span id="tipo-descripcion" class="visually-hidden">Campo obligatorio. Introduzca el tipo de puesto.</span>

        <label for="descripcion">Descripción</label>
        <textarea name="descripcion" id="descripcion" cols="10" rows="10"
            placeholder="Descripción del puesto. Por ejemplo: 'Bisutería hecha a mano'." maxlength="450"
            aria-describedby="descripcion-descripcion"><?= htmlspecialchars($data['descripcion'] ?? "") ?></textarea>
        <span id="descripcion-descripcion" class="visually-hidden">Introduzca una descripción del puesto, máximo 450
            caracteres.</span>

        <input type="hidden" name="id_traduccion" value="<?= htmlspecialchars($data['id'] ?? "") ?>">
        <input type="submit" value="Actualizar">
    </form>
    <p class="admin-error-text" style="text-align: center;">Los campos marcados con <span class="admin-required"
            aria-hidden="true">*</span> son
        obligatorios</p>
    <?= htmlspecialchars($mensaje ?? ""); ?>
    <?php if ($puesto_encontrado) { ?>
        <script type="module" src="<?= JS_ADMIN . 'edit_translations.js' ?>"></script>
    <?php } ?>
    <script src="<?= JS . '/helpers/dark_mode.js' ?>"></script>

</body>

</html>