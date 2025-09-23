<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - PraceAR - Editar Datos Generales de Puesto - Página de administración</title>
    <link rel="stylesheet" href="./css/header.css">
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

    <?php
    require_once(CSS_ADMIN . 'edit_admin.php'); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">

</head>

<body>

    <?php
    $imagen_eliminada = false;
    require_once(HELPERS . "update_stalls.php");
    require_once(COMPONENT_ADMIN . 'sections' . DIRECTORY_SEPARATOR . 'header.php');

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    $conexion = new mysqli($servidor_bd, $usuario, $clave, $bd);

    if ($conexion->connect_error) {
        die('Error en la conexión: ' . htmlspecialchars($conexion->connect_error));
    }

    $sql = "SELECT * FROM puestos WHERE id = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();

    if (!$fila) {
        die('<h2 style="text-align: center;">Error al obtener los datos del puesto. <a href="index.php">Volver</a></h2>');
    }
    ?>

    <form id="formulario-editar" action="#" method="post" enctype="multipart/form-data"
        aria-labelledby="cabecera-tabla">
        <input type="hidden" name="csrf" value="<?= isset($_SESSION['csrf']) ? $_SESSION['csrf'] : '' ?>">
        <h2 id="cabecera-tabla" style="text-align: center;">Datos del puesto <span
                style="color: #1e7dbd"><?= htmlspecialchars($fila["nombre"]) ?></span>
        </h2>
        <div style="display:flex; align-items: center; gap: .5em;">
            <label for="activo">Activo <span style="color: red;">*</span></label>
            <?php
            $activo = $fila["activo"];
            ?>
            <input type="checkbox" id="activo" name="activo" value="<?= $activo ?>" <?= $activo == 1 ? "checked" : "" ?>
                aria-label="Activo">
        </div>
        <div>
            <label for="caseta">Caseta</label>
            <input type="text" id="caseta" disabled required value="<?= htmlspecialchars($fila["caseta"]) ?>"
                aria-describedby="caseta-desc">
            <input type="hidden" name="caseta" value="<?= htmlspecialchars($fila["caseta"]) ?>"
                placeholder="Código de caseta">
            <span id="caseta-desc" class="visually-hidden">Código único de la caseta</span>
        </div>
        <div>
            <label for="nombre">Nombre</label>
            <input id="nombre" type="text" name="nombre" value="<?= htmlspecialchars($fila["nombre"]) ?>"
                placeholder="Nombre del puesto. Por ejemplo: 'Bibi Handmades'" aria-required="true">
        </div>
        <div>
            <?php
            $ruta_a_imagen = "assets/" . htmlspecialchars($fila["caseta"]) . ".jpg";
            $imagen_encontrada = false;
            if (file_exists($ruta_a_imagen)) {
                $imagen_encontrada = true;
            }
            ?>
            <?php if ($imagen_encontrada) { ?>
                <span>Imagen</span>
                <div style="display: flex; flex-direction: column; align-items: center;">
                    <img src="<?= htmlspecialchars($ruta_a_imagen) ?>"
                        alt="Imagen del puesto <?= htmlspecialchars($fila["nombre"]) ?>" class="zoomable"
                        style="object-fit: cover; height: 140px; display: block; margin: 0 auto;">
                    <a href="#" id="eliminar-imagen-link"
                        style="margin-top: 1em; color: red; text-decoration: none; text-align: center; display: block;"
                        aria-label="Eliminar imagen">Eliminar</a>
                    <script>
                        document.getElementById('eliminar-imagen-link').addEventListener('click', function (event) {
                            event.preventDefault(); // Previene la acción predeterminada del enlace
                            if (confirm('¿Estás seguro de que deseas eliminar esta imagen?')) {
                                document.getElementById('eliminar-imagen').checked = true; // Activa el checkbox oculto para eliminar la imagen
                                document.getElementById('formulario-editar').submit(); // Envía el formulario
                                // Volvemos a esta página, con URL de ejemplo: http://localhost/appventurers/index.php?page=edit&id=1&lang=gl
                                <?php $imagen_eliminada = true; ?>
                            }
                        });
                    </script>
                    <input type="checkbox" id="eliminar-imagen" name="eliminar_imagen" value="1" style="display: none;">
                </div>
            <?php } else { ?>
                <label for="imagen">Imagen</label>
                <input type="file" id="imagen" name="imagen" accept=".jpg, .jpeg" aria-label="Subir imagen">
            <?php } ?>
        </div>

        <div>
            <label for="contacto">Contacto</label>
            <input type="text" id="contacto" name="contacto" value="<?= htmlspecialchars($fila["contacto"]) ?>"
                placeholder="Información de contacto del puesto." aria-label="Contacto del puesto">
        </div>
        <div>
            <label for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($fila["telefono"]) ?>"
                placeholder="Teléfono de contacto. Por ejemplo: '981 123 456'" aria-label="Teléfono de contacto">
        </div>
        <div>
            <label for="tipo-unity">Tipo en Unity <span style="color: red;">*</span></label>
            <select name="tipo_unity" id="tipo-unity" aria-required="true">
                <?php foreach (UNITY_TYPE as $key => $value) { ?>
                    <option value="<?= $key ?>" <?= $fila["tipo_unity"] == $key ? "selected" : "" ?>>
                        <?= $value ?>
                    <?php } ?>
            </select>
        </div>
        <div>
            <label for="id-nave">ID Nave <span style="color: red;">*</span></label>
            <select required id="id-nave" name="id_nave" aria-required="true">
                <?php
                $sql_naves = "SELECT * FROM naves";
                $resultado_naves = $conexion->query($sql_naves);
                while ($fila_naves = $resultado_naves->fetch_assoc()) { ?>
                    <option value="<?= htmlspecialchars($fila_naves["id"]) ?>" <?= $fila["id_nave"] == $fila_naves["id"] ? "selected" : "" ?>>
                        <?= htmlspecialchars($fila_naves["tipo"]) ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div>
            <label for="caseta-padre">Caseta padre</label>
            <input name="caseta_padre" type="text" id="caseta-padre"
                value="<?= htmlspecialchars($fila["caseta_padre"]) ?>" placeholder="Código de caseta padre"
                aria-label="Caseta padre">
        </div>
        <div id="div-botones">
            <input id="actualizar" type="submit" value="Actualizar" aria-label="Actualizar datos del puesto">
        </div>
    </form>

    <p class="note" style="color: red; text-align: center;">Los campos marcados con <span style="color: red;">*</span>
        son
        obligatorios</p>

    <div id="zoomed-image-container" class="zoomed-container" role="dialog" aria-hidden="true">
        <img id="zoomed-image" src="" alt="">
    </div>

    <div id="mensaje" role="alert"><?= htmlspecialchars($mensaje) ?></div>

    <script>
        const zoomableImage = document.querySelector('.zoomable');
        const zoomedContainer = document.getElementById('zoomed-image-container');
        const zoomedImage = document.getElementById('zoomed-image');

        if (zoomableImage) {
            zoomableImage.addEventListener('click', function () {
                zoomedImage.src = this.src;
                zoomedContainer.classList.add('show');
                zoomedContainer.setAttribute('aria-hidden', 'false');
            });
        }

        zoomedContainer.addEventListener('click', function () {
            zoomedContainer.classList.remove('show');
            zoomedContainer.setAttribute('aria-hidden', 'true');
        });

    </script>

    <?php
    if ($fila) {
        ?>
        <script type="module" src="<?= htmlspecialchars(JS_ADMIN) ?>edit_admin.js"></script>
        <?php
    }

    ?>

</body>

</html>