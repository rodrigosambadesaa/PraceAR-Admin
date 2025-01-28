<!DOCTYPE html>

<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Traducciones de Puesto - Página de administración</title>
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

    $data = $resultado->fetch_assoc();
    if (!$data) {
        die('No se encontraron datos');
    }
    ?>

    <h2 style="text-align: center;">Traducción del puesto
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
    </h2>
    <form class="pure-form" action="#" method="POST" id="formulario">
        <label for="tipo">Tipo</label>
        <input type="text" id="tipo" name="tipo" value="<?= htmlspecialchars($data['tipo'] ?? "") ?>">
        <label for="descripcion">Descripción</label>
        <textarea name="descripcion" id="descripcion" cols="30" rows="10"
            maxlength="450"><?= htmlspecialchars($data['descripcion'] ?? "") ?></textarea>
        <input type="hidden" name="id_traduccion" value="<?= htmlspecialchars($data['id'] ?? "") ?>">
        <input type="submit" value="Actualizar">
    </form>
    <?= htmlspecialchars($mensaje ?? "") ?>
    <script type="module" src="<?= JS_ADMIN . 'edit_translations.js' ?>"></script>
</body>

</html>