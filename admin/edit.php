<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Datos Generales de Puesto - Página de administración</title>
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

</head>

<body>

    <?php
    require_once(HELPERS . "update_stalls.php");
    require_once(COMPONENT_ADMIN . 'sections' . DIRECTORY_SEPARATOR . 'header.php');
    ?>

    <form id="formulario-editar" action="#" method="post" enctype="multipart/form-data">
        <?php
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
        $puesto_encontrado = false;

        if ($resultado->num_rows <= 0) {
            echo "<h2 style='text-align: center;'>Error al obtener los datos del puesto. <a href='index.php'>Volver</a></h2>";
            exit;
        }

        $puesto_encontrado = true;
        $fila = $resultado->fetch_assoc();
        ?>
        <h2 id="cabecera-tabla" style="text-align: center;">Datos del puesto <?= htmlspecialchars($fila["id"]) ?></h2>
        <div style="display:flex; align-items: center; gap: .5em;">
            <label for="activo">Activo</label>
            <?php
            $activo = $fila["activo"];
            ?>
            <input type="checkbox" id="activo" name="activo" value="<?= $activo ?>" <?= $activo == 1 ? "checked" : "" ?>>
        </div>
        <div>
            <label for="caseta">Caseta</label>
            <input type="text" id="caseta" disabled value="<?= htmlspecialchars($fila["caseta"]) ?>">
            <input type="hidden" name="caseta" value="<?= htmlspecialchars($fila["caseta"]) ?>">
        </div>
        <div>
            <label for="nombre">Nombre</label>
            <input id="nombre" type="text" name="nombre" value="<?= htmlspecialchars($fila["nombre"]) ?>">
        </div>
        <div>
            <label for="imagen">Imagen</label>
            <?php
            $ruta_a_imagen = "assets/" . htmlspecialchars($fila["caseta"]) . ".jpg";
            if (file_exists($ruta_a_imagen)) {
                ?>
                <div style="display: flex; flex-direction: column; align-items: center;">
                    <img src="<?= htmlspecialchars($ruta_a_imagen) ?>" alt="Imagen del puesto" class="zoomable"
                        style="object-fit: cover; height: 100px;">
                    <a href="#" id="eliminar-imagen-link" style="margin-top: 1em; color: red; text-decoration: none;">
                        Eliminar imagen
                    </a>
                </div>
                <script>
                    document.getElementById('eliminar-imagen-link').addEventListener('click', function (event) {
                        event.preventDefault(); // Previene la acción predeterminada del enlace
                        if (confirm('¿Estás seguro de que deseas eliminar esta imagen?')) {
                            document.getElementById('eliminar-imagen').checked = true; // Activa el checkbox oculto para eliminar la imagen
                            document.getElementById('formulario-editar').submit(); // Envía el formulario
                        }
                    });
                </script>
                <input type="checkbox" id="eliminar-imagen" name="eliminar_imagen" value="1" style="display: none;">
            <?php } else { ?>
                <input type="file" id="imagen" name="imagen" accept=".jpg, .jpeg">
            <?php } ?>
        </div>

        <div>
            <label for="contacto">Contacto</label>
            <input type="text" id="contacto" name="contacto" value="<?= htmlspecialchars($fila["contacto"]) ?>">
        </div>
        <div>
            <label for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($fila["telefono"]) ?>">
        </div>
        <div>
            <label for="tipo-unity">Tipo en Unity</label>
            <select name="tipo_unity" id="tipo-unity">
                <?php foreach (UNITY_TYPE as $key => $value) { ?>
                    <?php $selected = $key === $fila["tipo_unity"] ? "selected" : ""; ?>
                    <option value="<?= htmlspecialchars($key) ?>" <?= $selected ?>><?= htmlspecialchars($value) ?></option>
                <?php } ?>
            </select>
        </div>
        <div>
            <label for="id-nave">ID Nave</label>
            <select id="id-nave" name="id_nave">
                <?php
                $sql_naves = "SELECT * FROM naves";
                $resultado_naves = $conexion->query($sql_naves);
                while ($fila_naves = $resultado_naves->fetch_assoc()) {
                    if ($fila["id_nave"] == $fila_naves["id"]) {
                        ?>
                        <option value="<?= htmlspecialchars($fila_naves["id"]) ?>" selected>
                            <?= htmlspecialchars($fila_naves["tipo"]) ?>
                        </option>
                    <?php } else { ?>
                        <option value="<?= htmlspecialchars($fila_naves["id"]) ?>"><?= htmlspecialchars($fila_naves["tipo"]) ?>
                        </option>
                    <?php }
                } ?>
            </select>
        </div>
        <div>
            <label for="caseta-padre">Caseta padre</label>
            <input name="caseta_padre" type="text" id="caseta-padre"
                value="<?= htmlspecialchars($fila["caseta_padre"]) ?>">
        </div>
        <div id="div-botones">
            <input id="actualizar" type="submit" value="Actualizar">
        </div>
    </form>

    <div id="zoomed-image-container" class="zoomed-container">
        <img id="zoomed-image" src="" alt="">
    </div>

    <div id="mensaje"><?= htmlspecialchars($mensaje) ?></div>

    <script>
        const zoomableImage = document.querySelector('.zoomable');
        const zoomedContainer = document.getElementById('zoomed-image-container');
        const zoomedImage = document.getElementById('zoomed-image');

        if (zoomableImage) {
            zoomableImage.addEventListener('click', function () {
                zoomedImage.src = this.src;
                zoomedContainer.classList.add('show');
            });
        }

        zoomedContainer.addEventListener('click', function () {
            zoomedContainer.classList.remove('show');
        });

    </script>

    <?php
    if ($puesto_encontrado) {
        ?>
        <script type="module" src="<?= htmlspecialchars(JS_ADMIN) ?>edit_admin.js"></script>
        <?php
    }
    ?>

</body>

</html>