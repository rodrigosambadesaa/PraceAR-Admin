<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - PraceAR - Página Principal del Panel de Administración</title>
    <?php require_once(CSS_ADMIN . 'index_admin.php'); ?>
    <link rel='icon' href='./img/favicon.png' type='image/png'>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
</head>

<body>
    <?php 
        require_once(SECTIONS . 'header.php');
        require_once(HELPERS . 'truncate_text.php');
        require_once(HELPERS . 'clean_input.php');
    ?>

    <?php
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (!isset($_SESSION['csrf'])) {
                $_SESSION['csrf'] = bin2hex(random_bytes(32)); // Generar un nuevo token CSRF
            }

            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
                die("Petición no válida");
            }
        }

        $custom_lang = get_language();

        $results_per_page = 50;
        $busqueda_hecha = false;
        $current_page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int) $_GET['page'] : 1;

        if ($current_page < 1) {
            $current_page = 1;
        }

        $start_from = ($current_page - 1) * $results_per_page;
        $caseta = '';

        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['caseta'])) {
            $caseta = limpiar_input($_POST['caseta']);
            // Redirigir a la misma página con GET para mantener la búsqueda
            header("Location: ?page=1&caseta=" . urlencode($caseta) . "&lang=" . urlencode(get_language()));
            exit;
        } elseif (isset($_GET['caseta'])) {
            $caseta = limpiar_input($_GET['caseta']);
        }

        $sql_total = "SELECT COUNT(p.id) as total FROM puestos p 
                      RIGHT JOIN puestos_traducciones pt ON p.id = pt.puesto_id
                      INNER JOIN naves n on p.id_nave = n.id
                      WHERE pt.codigo_idioma = ?";

        $params = [$custom_lang];

        if (!empty($caseta)) {
            $sql_total .= " AND p.caseta LIKE ?";
            $params[] = "%$caseta%";
            $busqueda_hecha = true;
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
            $busqueda_hecha = true;
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

    <main role="main">
        <?php if ($resultados_encontrados): ?>
            <table id="tabla-puestos" role="table" aria-label="Lista de puestos del Mercado de Abastos">
                <caption id="cabecera-tabla">
                    <h2 id="texto-cabecera-tabla">Lista de puestos del Mercado de Abastos</h2>
                    <div id="contenedor-separacion"></div>
                    <search role="search">
                        <form id="formulario-busqueda" action="?page=1" method="POST">
                            <input value="<?= htmlspecialchars($caseta) ?>" type="text" id="input-busqueda" placeholder="Código de caseta. P. ej. CE001, CO121, MC001, NA338, NC041" name="caseta" autofocus>
                            <input type="hidden" name="lang" id="lang" value="<?= htmlspecialchars(get_language()) ?>">
                            <input type="submit" value="Buscar">
                            <input id="input-reseteo" name="input_reseteo" type="reset" value="Reiniciar">
                            <input id="input-deshacer-busqueda" type="button" value="Deshacer" onclick="window.location.href='?lang=<?= htmlspecialchars(get_language()) ?>'">
                            <input type="hidden" name="csrf" id="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                        </form>
                    </search>
                    <?php require_once(SECTIONS . 'pagination.php'); ?>
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
                        <th scope="col" id="celda-especial"></th>
                        <th scope="col">Editar Traducción</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr id="row-<?= htmlspecialchars($row['id']) ?>">
                            <td scope="row" data-label="Editar">
                                <a href="<?= "?page=edit&id=" . htmlspecialchars($row['id']) . "&lang=" . htmlspecialchars($_REQUEST['lang'] ?? 'gl') ?>" aria-label="Editar puesto <?= htmlspecialchars($row['caseta']) ?>">
                                    <img loading="lazy" width='15' height='15' src="<?= htmlspecialchars(PENCIL_IMAGE_URL) ?>" alt="Editar">
                                </a>
                            </td>
                            <td data-label="Activo">
                                <?= htmlspecialchars($row['activo'] ? "Sí" : "No") ?>
                            </td>
                            <td data-label="Imagen">
                                <?php
                                $ruta_a_imagen = "assets/" . htmlspecialchars($row["caseta"]) . ".jpg";
                                if (file_exists($ruta_a_imagen)): ?>
                                    <img loading="lazy" class="zoomable" src="<?= htmlspecialchars($ruta_a_imagen) ?>" alt="Imagen del puesto <?= htmlspecialchars($row['caseta']) ?>">
                                <?php endif; ?>
                            </td>
                            <td data-label="Caseta"><?= htmlspecialchars($row['caseta']) ?></td>
                            <td data-label="Nombre"><?= htmlspecialchars($row['nombre']) ?></td>
                            <td data-label="Tipo Unity"><?= htmlspecialchars($row['tipo_unity']) ?></td>
                            <td data-label="Información de Contacto"><?= htmlspecialchars($row['contacto']) ?></td>
                            <td data-label="Teléfono"><?= htmlspecialchars($row['telefono']) ?></td>
                            <td data-label="Nave"><?= htmlspecialchars($row['nave']) ?></td>
                            <td data-label="Caseta padre"><?= htmlspecialchars($row["caseta_padre"] ?? "Ninguno") ?></td>
                            <td data-label="" id="celda-especial-dato"></td>
                            <td data-label="Idioma de la traducción" class="fondo-color-diferente">
                                <a href="<?= "?page=language&codigo_idioma=" . htmlspecialchars(get_language()) . "&id=" . htmlspecialchars($row['id']) . "&lang=" . htmlspecialchars($_REQUEST['lang'] ?? 'gl') ?>" aria-label="Editar traducción del puesto <?= htmlspecialchars($row['caseta']) ?>">
                                    <img class="imagen-bandera" loading="lazy" width="15" height="15" src="<?= htmlspecialchars(FLAG_IMAGES_URL . (get_language()) . ".png") ?>" alt="Idioma <?= htmlspecialchars(get_language()) ?>">
                                </a>
                            </td>
                            <td data-label="Tipo" class="fondo-color-diferente"><?= htmlspecialchars($row['tipo']) ?></td>
                            <td data-label="Descripción" class="fondo-color-diferente">
                                <?= htmlspecialchars($row['descripcion'] ? truncate_text($row['descripcion'], 30) : '') ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div id="zoomed-image-container" class="zoomed-container" role="dialog" aria-labelledby="zoomed-name" aria-hidden="true">
                <button class="close-button" aria-label="Cerrar vista ampliada">&times;</button>
                <img id="zoomed-image" src="" alt="Imagen ampliada del puesto">
                <p id="zoomed-name"></p>
            </div>
        <?php else: ?>
            <h2 style="text-align: center;">No se encontraron resultados. Configure la base de datos</h2>
        <?php endif; ?>
    </main>

    <footer role="contentinfo">
        <?php require(SECTIONS . 'pagination.php'); ?>
    </footer>

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
                zoomedContainer.setAttribute('aria-hidden', 'false');
            });
        });

        closeButton.addEventListener('click', function () {
            zoomedContainer.classList.remove('show');
            zoomedContainer.setAttribute('aria-hidden', 'true');
        });

        zoomedContainer.addEventListener('click', function (e) {
            if (e.target === this) {
                zoomedContainer.classList.remove('show');
                zoomedContainer.setAttribute('aria-hidden', 'true');
            }
        });
    </script>

    <?php if ($resultados_encontrados): ?>
        <script type="module" src="<?= JS_ADMIN . 'index_admin.js' ?>"></script>
        <script>
            <?php if ($busqueda_hecha): ?>
                const inputReseteo = document.getElementById('input-reseteo');
                inputReseteo.disabled = true;
                inputReseteo.style.display = 'none';
            <?php else: ?>
                const inputDeshacer = document.getElementById('input-deshacer-busqueda');
                inputDeshacer.disabled = true;
                inputDeshacer.style.display = 'none';
            <?php endif; ?>
        </script>
    <?php else: ?>
        <h2 style="text-align: center;">No se encontraron resultados. Configure la base de datos</h2>
    <?php endif; ?>
</body>

</html>
