<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PraceAR - Panel de Administración</title>
    <?php require_once(CSS_ADMIN . 'index_admin.php'); ?>
</head>

<body>
    <?php require_once(SECTIONS . 'header.php');
    require_once(HELPERS . 'truncate_text.php');
    require_once(HELPERS . 'clean_input.php');
    ?>

    <?php

    $custom_lang = get_language();

    $results_per_page = 50;
    $current_page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int) $_GET['page'] : 1;
    $start_from = ($current_page - 1) * $results_per_page;
    $caseta = isset($_GET['caseta']) ? limpiar_input($_GET['caseta']) : '';

    $sql_total = "SELECT COUNT(p.id) as total FROM puestos p 
              RIGHT JOIN puestos_traducciones pt ON p.id = pt.puesto_id
              INNER JOIN naves n on p.id_nave = n.id
              WHERE pt.codigo_idioma = ?";

    $params = [$custom_lang];

    if (!empty($caseta)) {
        $sql_total .= " AND p.caseta LIKE ?";
        $params[] = "%$caseta%";
    }

    $stmt_total = $conexion->prepare($sql_total);
    $stmt_total->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $row_total = $result_total->fetch_assoc();
    $total_results = $row_total['total'];

    $total_pages = ceil($total_results / $results_per_page);

    $sql = "SELECT p.id, p.caseta, p.imagen, p.nombre, pt.descripcion, p.contacto, p.telefono, pt.tipo, p.tipo_unity, p.id_nave, n.tipo as nave, p.caseta_padre, p.activo
        FROM puestos p 
        RIGHT JOIN puestos_traducciones pt ON p.id = pt.puesto_id
        INNER JOIN naves n on p.id_nave = n.id
        WHERE pt.codigo_idioma = ?";

    $params = [$custom_lang];

    if (!empty($caseta)) {
        $sql .= " AND p.caseta LIKE ?";
        $params[] = "%$caseta%";
    }

    $sql .= " ORDER BY p.caseta LIMIT ?, ?";
    $params[] = $start_from;
    $params[] = $results_per_page;

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($params) - 2) . 'ii', ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $resultados_encontrados = $result->num_rows > 0;
    ?>

    <main>
        <?php
        if ($resultados_encontrados):
            ?>
            <table class="tabla_puestos">
                <caption id="cabeceraTabla">
                    <h2 id="textoCabeceraTabla">Lista de puestos del Mercado de Abastos</h2>
                    <div id="contenedorSeparacion"></div>
                    <search role="search">
                        <form id="formularioBusqueda" action="#" method="GET">
                            <input value="<?= htmlspecialchars($_GET['caseta'] ?? "") ?>" type="text" id="inputBusqueda"
                                placeholder="Código de caseta. P. ej. CE001, CO121, MC001, NA338, NC041" name="caseta">
                            <input type="hidden" name="lang" value="<?= htmlspecialchars(get_language()) ?>">
                            <input type="submit" value="Buscar">
                            <input id="inputReseteo" type="reset" value="Reiniciar">
                        </form>
                    </search>
                    <!-- Paginación superior -->
                    <div class="pagination">
                        <?php if ($current_page > 1) {
                            $first_page = 1;
                            ?>
                            <a
                                href="?page=<?= $first_page ?>&caseta=<?= htmlspecialchars($_GET['caseta'] ?? '') ?>&lang=<?= htmlspecialchars(get_language()) ?>">Primera
                                &laquo;</a>
                            <a
                                href="?page=<?= $current_page - 1 ?>&caseta=<?= htmlspecialchars($_GET['caseta'] ?? '') ?>&lang=<?= htmlspecialchars(get_language()) ?>">&laquo;
                                Anterior</a>
                        <?php } ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                            <a class="<?= $i == $current_page ? 'active' : '' ?>"
                                href="?page=<?= $i ?>&caseta=<?= htmlspecialchars($_GET['caseta'] ?? '') ?>&lang=<?= htmlspecialchars(get_language()) ?>">
                                <?= $i ?>
                            </a>
                        <?php } ?>

                        <?php if ($current_page < $total_pages) {
                            $last_page = $total_pages;
                            ?>
                            <a
                                href="?page=<?= $current_page + 1 ?>&caseta=<?= htmlspecialchars($_GET['caseta'] ?? '') ?>&lang=<?= htmlspecialchars(get_language()) ?>">Siguiente
                                &raquo;</a>
                            <a
                                href="?page=<?= $last_page ?>&caseta=<?= htmlspecialchars($_GET['caseta'] ?? '') ?>&lang=<?= htmlspecialchars(get_language()) ?>">Última
                                &raquo;</a>
                        <?php } ?>
                    </div>
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Editar</th>
                        <th scope="col">Activo</th>
                        <th scope="col">Imagen</th>
                        <th scope="col">Caseta</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Tipo Unity</th>
                        <th scope="col">Contacto</th>
                        <th scope="col">Teléfono</th>
                        <th scope="col">ID Nave</th>
                        <th scope="col">Puesto padre</th>
                        <th scope="col" id="celdaEspecial"></th>
                        <th scope="col">Editar Traducción</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr id="row_<?= htmlspecialchars($row['id']) ?>">
                            <td scope="row" data-label="Editar">
                                <a
                                    href="<?= "?page=edit&id=" . htmlspecialchars($row['id']) . "&lang=" . htmlspecialchars($_REQUEST['lang'] ?? 'gl') ?>">
                                    <img loading="lazy" width='15' height='15' src="<?= htmlspecialchars(PENCIL_IMAGE_URL) ?>">
                                </a>
                            </td>
                            <td data-label="Activo">
                                <?= htmlspecialchars($row['activo'] ? "Sí" : "No") ?>
                            </td>
                            <td data-label="Imagen">
                                <?php
                                $ruta_de_imagen = "assets/" . htmlspecialchars($row["caseta"]) . ".jpg";
                                if (file_exists($ruta_de_imagen)) {
                                    ?>
                                    <img loading="lazy" class="zoomable" src="<?= htmlspecialchars($ruta_de_imagen) ?>"
                                        alt="Imagen del puesto">
                                <?php } ?>
                            </td>
                            <td data-label="Caseta"><?= htmlspecialchars($row['caseta']) ?></td>
                            <td data-label="Nombre"><?= htmlspecialchars($row['nombre']) ?></td>
                            <td data-label="Tipo Unity"><?= htmlspecialchars($row['tipo_unity']) ?></td>
                            <td data-label="Información de Contacto"><?= htmlspecialchars($row['contacto']) ?></td>
                            <td data-label="Teléfono"><?= htmlspecialchars($row['telefono']) ?></td>
                            <td data-label="Nave"><?= htmlspecialchars($row['nave']) ?></td>
                            <td data-label="Caseta padre"><?= htmlspecialchars($row["caseta_padre"] ?? "Ninguno") ?></td>
                            <td data-label="" id="celdaEspecialDato"></td>
                            <td data-label="Idioma de la traducción" class="different-background-color">
                                <a
                                    href="<?= "?page=language&codigo_idioma=" . htmlspecialchars(get_language()) . "&id=" . htmlspecialchars($row['id']) . "&lang=" . htmlspecialchars($_REQUEST['lang'] ?? 'gl') ?>">
                                    <img id="imagenBandera" loading="lazy" width="15" height="15"
                                        src="<?= htmlspecialchars(FLAG_IMAGES_URL . (get_language()) . ".png") ?>"
                                        alt="<?= htmlspecialchars(get_language()) ?>">
                                </a>
                            </td>
                            <td data-label="Tipo" class="different-background-color"><?= htmlspecialchars($row['tipo']) ?></td>
                            <td data-label="Descripción" class="different-background-color">
                                <?= htmlspecialchars($row['descripcion'] ? htmlspecialchars(truncar_texto($row['descripcion'], 30)) : '') ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>

            <!-- Contenedor para mostrar la imagen ampliada y el nombre del puesto -->
            <div id="zoomed-image-container" class="zoomed-container">
                <span class="close-button">&times;</span>
                <img id="zoomed-image" src="" alt="Imagen del puesto ampliada">
                <p id="zoomed-name"></p>
            </div>

            <!-- Paginación inferior -->
            <div class="pagination">
                <?php if ($current_page > 1) {
                    $first_page = 1;
                    ?>
                    <a
                        href="?page=<?= $first_page ?>&caseta=<?= htmlspecialchars($_GET['caseta'] ?? '') ?>&lang=<?= htmlspecialchars(get_language()) ?>">Primera
                        &laquo;</a>
                    <a
                        href="?page=<?= $current_page - 1 ?>&caseta=<?= htmlspecialchars($_GET['caseta'] ?? '') ?>&lang=<?= htmlspecialchars(get_language()) ?>">&laquo;
                        Anterior</a>
                <?php } ?>

                <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                    <a class="<?= $i == $current_page ? 'active' : '' ?>"
                        href="?page=<?= $i ?>&caseta=<?= htmlspecialchars($_GET['caseta'] ?? '') ?>&lang=<?= htmlspecialchars(get_language()) ?>">
                        <?= $i ?>
                    </a>
                <?php } ?>

                <?php if ($current_page < $total_pages) {
                    $last_page = $total_pages;
                    ?>
                    <a
                        href="?page=<?= $current_page + 1 ?>&caseta=<?= htmlspecialchars($_GET['caseta'] ?? '') ?>&lang=<?= htmlspecialchars(get_language()) ?>">Siguiente
                        &raquo;</a>
                    <a
                        href="?page=<?= $last_page ?>&caseta=<?= htmlspecialchars($_GET['caseta'] ?? '') ?>&lang=<?= htmlspecialchars(get_language()) ?>">Última
                        &raquo;</a>
                <?php } ?>
            </div>
        </main>

    <?php else: ?>
        <h2>No se encontraron resultados</h2>
        </main>
    <?php endif; ?>

    <script>
        const zoomableImages = document.querySelectorAll('.zoomable');
        const zoomedContainer = document.getElementById('zoomed-image-container');
        const zoomedImage = document.getElementById('zoomed-image');
        const zoomedName = document.getElementById('zoomed-name');
        const closeButton = document.querySelector('.zoomed-container .close-button');

        zoomableImages.forEach(image => {
            image.addEventListener('click', function (e) {
                e.stopPropagation();
                zoomedImage.src = this.src;
                zoomedName.textContent = this.closest('tr').querySelector('td:nth-child(5)').textContent;
                zoomedContainer.classList.add('show');
            });
        });

        closeButton.addEventListener('click', function () {
            zoomedContainer.classList.remove('show');
        });

        zoomedContainer.addEventListener('click', function (e) {
            if (e.target === this) {
                zoomedContainer.classList.remove('show');
            }
        });
    </script>

</body>

</html>