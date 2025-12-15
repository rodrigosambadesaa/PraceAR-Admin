<?php
declare(strict_types=1);

require_once(HELPERS . 'clean_input.php');

$mensaje = "";
$fila = null;

// Obtener el ID del puesto a editar
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM puestos WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();
}

// Procesar el formulario de actualización
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $telefono = limpiar_input($_POST['telefono'] ?? '');
    $tipo_unity = limpiar_input($_POST['tipo_unity'] ?? '');
    $id_nave = (int)$_POST['id_nave'];
    $caseta_padre = !empty($_POST['caseta_padre']) ? limpiar_input($_POST['caseta_padre']) : null;

    $sql_update = "UPDATE puestos SET telefono = ?, tipo_unity = ?, id_nave = ?, caseta_padre = ? WHERE id = ?";
    $stmt_update = $conexion->prepare($sql_update);
    $stmt_update->bind_param("ssisi", $telefono, $tipo_unity, $id_nave, $caseta_padre, $id);

    if ($stmt_update->execute()) {
        $mensaje = "Datos actualizados correctamente.";
        // Refrescar los datos
        $sql = "SELECT * FROM puestos WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
    } else {
        $mensaje = "Error al actualizar los datos: " . $conexion->error;
    }
}

if (!$fila) {
    echo "<div class='container'><p class='admin-error-text'>No se encontró el puesto especificado.</p></div>";
    return;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - PraceAR - Editar Datos Generales de Puesto - Página de administración</title>
     <style>
        <?php
            require_once(CSS_ADMIN . 'theme.css');
            require_once(CSS_ADMIN . 'header.css');
            require_once(CSS_ADMIN . 'edit_admin.css');
        ?>
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel='icon' href='./img/favicon.png' type='image/png'>
    <link rel="stylesheet" href="./css/darkmode.css">

    <!-- Iconos para dispositivos Apple -->
    <link rel="apple-touch-icon" href="./img/favicon.png">
</head>

<body class="admin-edit">
    <?php require_once(SECTIONS . 'header.php'); ?>

    <main class="container">
        <h1 id="cabecera_pagina_edicion">Editar Datos Generales de Puesto</h1>
        
        <form action="?page=edit&id=<?= htmlspecialchars((string)$id) ?>" method="POST" class="form-group" id="formulario-editar">
            <div>
                <label for="caseta">Caseta</label>
                <input type="text" id="caseta" name="caseta" value="<?= htmlspecialchars($fila['caseta'] ?? '') ?>" readonly aria-label="Código de caseta">
            </div>
            <div>
                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($fila["telefono"] ?? '') ?>"
                    placeholder="Teléfono de contacto. Por ejemplo: '981 123 456'" aria-label="Teléfono de contacto">
            </div>
            <div>
                <label for="tipo-unity">Tipo en Unity <span class="admin-required" aria-hidden="true">*</span></label>
                <select name="tipo_unity" id="tipo-unity" aria-required="true">
                    <?php foreach (UNITY_TYPE as $key => $value) { ?>
                        <option value="<?= $key ?>" <?= ($fila["tipo_unity"] ?? '') == $key ? "selected" : "" ?>>
                            <?= $value ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label for="id-nave">ID Nave <span class="admin-required" aria-hidden="true">*</span></label>
                <select required id="id-nave" name="id_nave" aria-required="true">
                    <?php
                    $sql_naves = "SELECT * FROM naves";
                    $resultado_naves = $conexion->query($sql_naves);
                    while ($fila_naves = $resultado_naves->fetch_assoc()) { ?>
                        <option value="<?= htmlspecialchars((string)$fila_naves["id"]) ?>" <?= $fila["id_nave"] == $fila_naves["id"] ? "selected" : "" ?>>
                            <?= htmlspecialchars($fila_naves["tipo"]) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label for="caseta-padre">Caseta padre</label>
                <input name="caseta_padre" type="text" id="caseta-padre"
                    value="<?= htmlspecialchars($fila["caseta_padre"] ?? "") ?>" placeholder="Código de caseta padre"
                    aria-label="Caseta padre">
            </div>
            <div id="div-botones">
                <input id="actualizar" type="submit" value="Actualizar" aria-label="Actualizar datos del puesto">
            </div>
        </form>

        <p class="note admin-error-text" style="text-align: center;">Los campos marcados con <span class="admin-required" aria-hidden="true">*</span>
            son
            obligatorios</p>

        <div id="zoomed-image-container" class="zoomed-container" role="dialog" aria-hidden="true">
            <button id="zoomed-close" class="zoomed-close" aria-label="Cerrar imagen ampliada">&times;</button>
            <img id="zoomed-image" src="" alt="">
        </div>

        <div id="mensaje" role="alert"><?= htmlspecialchars($mensaje) ?></div>

        <?php
        if ($fila) {
            ?>
            <script type="module" src="<?= htmlspecialchars(JS_ADMIN) ?>edit_stall.js"></script>
            <?php
        }
        ?>

        <script>
            window.BASE_URL = "<?= BASE_URL ?>";
        </script>
        <script src="<?= JS . '/helpers/dark_mode.js' ?>"></script>
    </main>
</body>

</html>