<!DOCTYPE html>

<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - PraceAR - Editar Traducciones de Puesto - Página de administración</title>
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

    <h2 style="text-align: center;">Traducción del puesto<span style="color: #1e7dbd;">
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

    <form class="pure-form" action="#" method="POST" id="formulario">
        <input type="hidden" name="csrf" value="<?= isset($_SESSION['csrf']) ? $_SESSION['csrf'] : '' ?>">
        <label for="tipo">Tipo <span style="color: red;">*</span></label>
        <input type="text" id="tipo" name="tipo" value="<?= htmlspecialchars($data['tipo'] ?? "") ?>"
            placeholder="Tipo de puesto. Por ejemplo: 'Bisutería'" required>
        <label for="descripcion">Descripción</label>
        <textarea name="descripcion" id="descripcion" cols="10" rows="10"
            placeholder="Descripción del puesto. Por ejemplo: 'Bisutería hecha a mano'."
            maxlength="450"><?= htmlspecialchars($data['descripcion'] ?? "") ?></textarea>
        <input type="hidden" name="id_traduccion" value="<?= htmlspecialchars($data['id'] ?? "") ?>">
        <input type="submit" value="Actualizar">
    </form>
    <p style="color: red; text-align: center;">Los campos marcados con <span style="color: red;">*</span> son
        obligatorios</p>
    <?= htmlspecialchars($mensaje ?? ""); ?>
    <?php if ($puesto_encontrado) { ?>
        <script type="module" src="<?= JS_ADMIN . 'edit_translations.js' ?>"></script>
    <?php } ?>
</body>

</html>