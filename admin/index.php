<?php
require_once(SECTIONS . 'header.php');
require_once(HELPERS . 'truncate-text.php');
require_once(HELPERS . 'clean-input.php');
require_once(CSS_ADMIN . 'index_admin.php');

$custom_lang = getLanguage();

$results_per_page = 50;
$current_page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int) $_GET['page'] : 1;
$start_from = ($current_page - 1) * $results_per_page;
$caseta = isset($_GET['caseta']) ? limpiarInput($_GET['caseta']) : '';

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
if ($resultados_encontrados):
    ?>
    <main>
        <table class="tabla_puestos">
            <caption id="cabeceraTabla">
                <span id="textoCabeceraTabla">Lista de puestos del Mercado de Abastos</span>
                <div id="contenedorSeparacion"></div>
                <search role="search">
                    <form id="formularioBusqueda" action="#" method="GET">
                        <input value="<?= $_GET['caseta'] ?? "" ?>" type="text" id="inputBusqueda"
                            placeholder="Código de caseta. P. ej. CE001, CO121, MC001, NA338, NC041" name="caseta">
                        <input type="hidden" name="lang" value="<?= getLanguage() ?>">
                        <input type="submit" value="Buscar">
                        <input id="inputReseteo" type="reset" value="Reiniciar">
                    </form>
                </search>
                <!-- Paginación superior -->
                <div class="pagination">
                    <?php if ($current_page > 1) {
                        $firstPage = 1;
                        ?>
                        <a href="?page=<?= $firstPage ?>&caseta=<?= $_GET['caseta'] ?? '' ?>&lang=<?= getLanguage() ?>">Primera
                            &laquo;</a>
                        <a href="?page=<?= $current_page - 1 ?>&caseta=<?= $_GET['caseta'] ?? '' ?>&lang=<?= getLanguage() ?>">&laquo;
                            Anterior</a>
                    <?php } ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                        <a class="<?= $i == $current_page ? 'active' : '' ?>"
                            href="?page=<?= $i ?>&caseta=<?= $_GET['caseta'] ?? '' ?>&lang=<?= getLanguage() ?>">
                            <?= $i ?>
                        </a>
                    <?php } ?>

                    <?php if ($current_page < $total_pages) {
                        $lastPage = $total_pages;
                        ?>
                        <a href="?page=<?= $current_page + 1 ?>&caseta=<?= $_GET['caseta'] ?? '' ?>&lang=<?= getLanguage() ?>">Siguiente
                            &raquo;</a>
                        <a href="?page=<?= $lastPage ?>&caseta=<?= $_GET['caseta'] ?? '' ?>&lang=<?= getLanguage() ?>">Última
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
                if ($result->num_rows > 0) {
                    $resultados_encontrados = true;
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr id="row_<?= $row['id'] ?>">
                            <td scope="row" data-label="Editar">
                                <a href="<?= "?page=edit&id=" . $row['id'] . "&lang=" . ($_REQUEST['lang'] ?? 'gl') ?>">
                                    <img loading="lazy" width='15' height='15' src="<?= PENCIL_IMAGE_URL ?>">
                                </a>
                            </td>
                            <td data-label="Activo">
                                <?= $row['activo'] ? "Sí" : "No" ?>
                            </td>
                            <td data-label="Imagen">
                                <?php
                                $imagenPath = "assets/" . $row["caseta"] . ".jpg";
                                if (file_exists($imagenPath)) {
                                    ?>
                                    <img loading="lazy" class="zoomable" src="<?= $imagenPath ?>" alt="Imagen del puesto">
                                <?php } ?>
                            </td>
                            <td data-label="Caseta"><?= $row['caseta'] ?></td>
                            <td data-label="Nombre"><?= $row['nombre'] ?></td>
                            <td data-label="Tipo Unity"><?= $row['tipo_unity'] ?></td>
                            <td data-label="Información de Contacto"><?= $row['contacto'] ?></td>
                            <td data-label="Teléfono"><?= $row['telefono'] ?></td>
                            <td data-label="Nave"><?= $row['nave'] ?></td>
                            <td data-label="Caseta padre"><?= $row["caseta_padre"] ?? "Ninguno" ?></td>
                            <td data-label="" id="celdaEspecialDato"></td>
                            <td data-label="Idioma de la traducción" class="different-background-color">
                                <a
                                    href="<?= "?page=language&codigo_idioma=" . getLanguage() . "&id=" . $row['id'] . "&lang=" . ($_REQUEST['lang'] ?? 'gl') ?>">
                                    <img id="imagenBandera" loading="lazy" width="15" height="15"
                                        src="<?= FLAG_IMAGES_URL . (getLanguage()) . ".png" ?>" alt="<?= getLanguage() ?>">
                                </a>
                            </td>
                            <td data-label="Tipo" class="different-background-color"><?= $row['tipo'] ?></td>
                            <td data-label="Descripción" class="different-background-color">
                                <?= $row['descripcion'] ? truncateText($row['descripcion'], 30) : '' ?>
                            </td>
                        </tr>
                        <?php
                    }
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
                $firstPage = 1;
                ?>
                <a href="?page=<?= $firstPage ?>&caseta=<?= $_GET['caseta'] ?? '' ?>&lang=<?= getLanguage() ?>">Primera
                    &laquo;</a>
                <a href="?page=<?= $current_page - 1 ?>&caseta=<?= $_GET['caseta'] ?? '' ?>&lang=<?= getLanguage() ?>">&laquo;
                    Anterior</a>
            <?php } ?>

            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <a class="<?= $i == $current_page ? 'active' : '' ?>"
                    href="?page=<?= $i ?>&caseta=<?= $_GET['caseta'] ?? '' ?>&lang=<?= getLanguage() ?>">
                    <?= $i ?>
                </a>
            <?php } ?>

            <?php if ($current_page < $total_pages) {
                $lastPage = $total_pages;
                ?>
                <a href="?page=<?= $current_page + 1 ?>&caseta=<?= $_GET['caseta'] ?? '' ?>&lang=<?= getLanguage() ?>">Siguiente
                    &raquo;</a>
                <a href="?page=<?= $lastPage ?>&caseta=<?= $_GET['caseta'] ?? '' ?>&lang=<?= getLanguage() ?>">Última
                    &raquo;</a>
            <?php } ?>
        </div>
    </main>

<?php else: ?>
    <h2>No se encontraron resultados</h2>
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