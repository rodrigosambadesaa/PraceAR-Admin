<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - PraceAR - Página Principal del Panel de Administración</title>
    <style>
        <?php require_once(CSS_ADMIN . 'theme.css'); ?>
        <?php require_once(CSS_ADMIN . 'header.css'); ?>
        <?php require_once(CSS_ADMIN . 'index_admin.css'); ?>
    </style>
    <link rel='icon' href='./img/favicon.png' type='image/png'>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="./css/darkmode.css">
</head>

<body>
    <?php
    require_once(SECTIONS . 'header.php');
    require_once(HELPERS . 'truncate_text.php');
    require_once(HELPERS . 'clean_input.php');
    ?>

    <?php
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32)); // Generar un nuevo token CSRF
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
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

    // Manejo de búsqueda por caseta
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['caseta'])) {
        $caseta = limpiar_input($_POST['caseta']);
        // Redirigir usando GET para mantener la búsqueda en la URL
        $params = [
            'page' => 1,
            'caseta' => $caseta,
            'lang' => get_language()
        ];
        header("Location: ?" . http_build_query($params));
        exit;
    } elseif (isset($_GET['caseta'])) {
        // Si hay parámetro GET 'caseta', lo limpiamos
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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <main role="main">
        <?php if ($resultados_encontrados): ?>
            <table id="tabla-puestos" role="table" aria-label="Lista de puestos del Mercado de Abastos">
                <caption id="cabecera-tabla">
                    <h2 id="texto-cabecera-tabla">Lista de puestos del Mercado de Abastos</h2>
                    <div id="contenedor-separacion"></div>
                    <search role="search">
                        <form id="formulario-busqueda" action="?page=1" method="POST">
                            <input value="<?= htmlspecialchars($caseta) ?>" type="text" id="input-busqueda"
                                placeholder="Código de caseta. P. ej. CE001, CO121, MC001, NA338, NC041" name="caseta"
                                <?php if (!$busqueda_hecha) echo 'autofocus'; ?>>
                            <input type="hidden" name="lang" id="lang" value="<?= htmlspecialchars(get_language()) ?>">
                            <input type="submit" value="Buscar">
                            <input id="input-reseteo" name="input_reseteo" type="reset" value="Reiniciar">
                            <input id="input-deshacer-busqueda" type="button" value="Deshacer"
                                onclick="window.location.href='?lang=<?= htmlspecialchars(get_language()) ?>'">
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
                                <a href="<?= "?page=edit&id=" . htmlspecialchars($row['id']) . "&lang=" . htmlspecialchars($_REQUEST['lang'] ?? 'gl') ?>"
                                    aria-label="Editar puesto <?= htmlspecialchars($row['caseta']) ?>">
                                    <img loading="lazy" width='15' height='15' src="<?= htmlspecialchars(PENCIL_IMAGE_URL) ?>"
                                        alt="Editar">
                                </a>
                            </td>
                            <td data-label="Activo">
                                <?= htmlspecialchars($row['activo'] ? "Sí" : "No") ?>
                            </td>
                            <td data-label="Imagen">
                                <?php
                                $ruta_a_imagen = "assets/" . htmlspecialchars($row["caseta"]) . ".jpg";
                                if (file_exists($ruta_a_imagen)) {
                                    echo '<img loading="lazy" class="zoomable editable-image" src="' . htmlspecialchars($ruta_a_imagen) . '" alt="Imagen del puesto ' . htmlspecialchars($row['caseta']) . '" data-editable="true" data-field="imagen" data-id="' . htmlspecialchars($row['id']) . '">';
                                } else {
                                    // Celda vacía pero editable para subir imagen
                                    echo '<div class="editable-image-blank" data-editable="true" data-field="imagen" data-id="' . htmlspecialchars($row['id']) . '" style="height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#888;font-size:0.9em;">Subir imagen</div>';
                                }
                                ?>
                            </td>
                                <td data-label="Caseta" data-editable="true" data-field="caseta" data-id="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['caseta']) ?></td>
                                <td data-label="Nombre" data-editable="true" data-field="nombre" data-id="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['nombre']) ?></td>
                            <td data-label="Tipo Unity"><?= htmlspecialchars($row['tipo_unity']) ?></td>
                                <td data-label="Información de Contacto" data-editable="true" data-field="contacto" data-id="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['contacto']) ?></td>
                                <td data-label="Teléfono" data-editable="true" data-field="telefono" data-id="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['telefono']) ?></td>
                            <td data-label="Nave"><?= htmlspecialchars($row['nave']) ?></td>
                                <td data-label="Caseta padre" data-editable="true" data-field="caseta_padre" data-id="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row["caseta_padre"] ?? "Ninguno") ?></td>
                            <td data-label="" class="celda-especial-dato"></td>
                                <td data-label="Idioma de la traducción" class="fondo-color-diferente">
                                    <a href="?page=language&codigo_idioma=<?= htmlspecialchars(get_language()) ?>&id=<?= htmlspecialchars($row['id']) ?>&lang=<?= htmlspecialchars($_REQUEST['lang'] ?? 'gl') ?>"
                                        aria-label="Editar traducción del puesto <?= htmlspecialchars($row['caseta']) ?>">
                                        <img class="imagen-bandera" loading="lazy" width="15" height="15"
                                            src="<?= htmlspecialchars(FLAG_IMAGES_URL . (get_language()) . ".png") ?>"
                                            alt="Idioma <?= htmlspecialchars(get_language()) ?>">
                                    </a>
                                </td>
                                <td data-label="Tipo" class="fondo-color-diferente editable-tipo" data-editable="true" data-field="tipo" data-id="<?= htmlspecialchars($row['id']) ?>" data-codigo_idioma="<?= htmlspecialchars(get_language()) ?>"><?= htmlspecialchars($row['tipo']) ?></td>
                                <td data-label="Descripción" class="fondo-color-diferente editable-descripcion" data-editable="true" data-field="descripcion" data-id="<?= htmlspecialchars($row['id']) ?>" data-codigo_idioma="<?= htmlspecialchars(get_language()) ?>">
                                    <?= htmlspecialchars($row['descripcion'] ? truncate_text($row['descripcion'], 30) : '') ?>
                                </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        <?php else: ?>
            <h2 style="text-align: center;">No se encontraron resultados. Configure la base de datos</h2>
        <?php endif; ?>
    </main>

    <footer role="contentinfo">
        <?php require(SECTIONS . 'pagination.php'); ?>
    </footer>

        <!-- Modal para edición rápida -->
        <div id="modal-edicion" class="modal-edicion" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
            <div id="modal-content" style="background:#fff; border-radius:10px; padding:2em; min-width:300px; max-width:90vw; box-shadow:0 2px 20px rgba(0,0,0,0.2); position:relative;">
                <button id="modal-close" style="position:absolute; top:1em; right:1em; font-size:1.5em; background:none; border:none; cursor:pointer;">&times;</button>
                <div id="modal-body"></div>
            </div>
        </div>

    <div id="zoomed-image-container" class="zoomed-container" role="dialog" aria-labelledby="zoomed-name"
        aria-hidden="true">
        <button class="close-button" aria-label="Cerrar vista ampliada">&times;</button>
        <img id="zoomed-image" src="" alt="Imagen ampliada del puesto">
        <p id="zoomed-name"></p>
    </div>

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

            // Edición rápida en modal
            document.querySelectorAll('[data-editable="true"]').forEach(cell => {
                cell.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const field = cell.getAttribute('data-field');
                    const id = cell.getAttribute('data-id');
                    const codigo_idioma = cell.getAttribute('data-codigo_idioma');
                    let modalBody = document.getElementById('modal-body');
                    modalBody.innerHTML = '<div style="text-align:center;">Cargando...</div>';
                    document.getElementById('modal-edicion').style.display = 'flex';
                    // AJAX para obtener el formulario de edición rápida
                    fetch('admin/ajax_quick_edit.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id, field, codigo_idioma })
                    })
                    .then(res => res.text())
                    .then(html => {
                        // Insert CSRF token if missing
                        modalBody.innerHTML = html;
                        setTimeout(() => {
                            const quickForm = modalBody.querySelector('form');
                            if (quickForm && !quickForm.querySelector('[name="csrf"]')) {
                                const csrfInput = document.createElement('input');
                                csrfInput.type = 'hidden';
                                csrfInput.name = 'csrf';
                                csrfInput.value = document.getElementById('csrf').value;
                                quickForm.appendChild(csrfInput);
                            }
                        }, 50);
                        // Interceptar el submit para actualizar la celda sin recargar
                        setTimeout(() => {
                            const quickForm = modalBody.querySelector('form');
                            if (quickForm) {
                                quickForm.onsubmit = function(ev) {
                                    ev.preventDefault();
                                    const formData = new FormData(quickForm);
                                    fetch('admin/ajax_quick_edit_save.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(async res => {
                                        const rawText = await res.text();
                                        let data;
                                        try {
                                            data = JSON.parse(rawText);
                                        } catch (err) {
                                            modalBody.querySelector('#quick-edit-msg').innerHTML = '<span style="color:red">Error inesperado:<br>' + rawText + '</span>';
                                            return;
                                        }
                                        modalBody.querySelector('#quick-edit-msg').textContent = data.msg;
                                        if (data.success) {
                                            // Actualizar la celda editada
                                            if (field === 'nombre' || field === 'contacto' || field === 'telefono' || field === 'caseta_padre') {
                                                cell.textContent = quickForm.querySelector('[name="value"]').value;
                                            } else if (field === 'tipo') {
                                                cell.textContent = quickForm.querySelector('[name="tipo"]').value;
                                            } else if (field === 'descripcion') {
                                                cell.textContent = quickForm.querySelector('[name="descripcion"]').value.substring(0, 30) + (quickForm.querySelector('[name="descripcion"]').value.length > 30 ? '...' : '');
                                            } else if (field === 'traduccion') {
                                                // Actualizar tipo y descripción
                                                const row = cell.closest('tr');
                                                row.querySelector('[data-field="tipo"]').textContent = quickForm.querySelector('[name="tipo"]').value;
                                                row.querySelector('[data-field="descripcion"]').textContent = quickForm.querySelector('[name="descripcion"]').value.substring(0, 30) + (quickForm.querySelector('[name="descripcion"]').value.length > 30 ? '...' : '');
                                            } else if (field === 'imagen') {
                                                // Para imagen, recargar solo la imagen o mostrar opción de subir si se elimina
                                                const img = cell.querySelector('img');
                                                if (img && quickForm.querySelector('[name="delete"]') && quickForm.querySelector('[name="delete"]').checked) {
                                                    img.remove();
                                                    cell.innerHTML = '<div class="editable-image-blank" data-editable="true" data-field="imagen" data-id="' + id + '" style="height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#888;font-size:0.9em;">Subir imagen</div>';
                                                } else if (img) {
                                                    img.src = img.src + '?' + new Date().getTime();
                                                } else {
                                                    cell.innerHTML = '<div class="editable-image-blank" data-editable="true" data-field="imagen" data-id="' + id + '" style="height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#888;font-size:0.9em;">Subir imagen</div>';
                                                }
                                                // Reasignar el event listener para el nuevo div si se elimina la imagen
                                                setTimeout(() => {
                                                    cell.querySelectorAll('[data-editable="true"]').forEach(newCell => {
                                                        newCell.addEventListener('click', function(e) {
                                                            e.stopPropagation();
                                                            const field = newCell.getAttribute('data-field');
                                                            const id = newCell.getAttribute('data-id');
                                                            const codigo_idioma = newCell.getAttribute('data-codigo_idioma');
                                                            let modalBody = document.getElementById('modal-body');
                                                            modalBody.innerHTML = '<div style="text-align:center;">Cargando...</div>';
                                                            document.getElementById('modal-edicion').style.display = 'flex';
                                                            fetch('admin/ajax_quick_edit.php', {
                                                                method: 'POST',
                                                                headers: { 'Content-Type': 'application/json' },
                                                                body: JSON.stringify({ id, field, codigo_idioma })
                                                            })
                                                            .then(res => res.text())
                                                            .then(html => {
                                                                modalBody.innerHTML = html;
                                                                setTimeout(() => {
                                                                    const quickForm = modalBody.querySelector('form');
                                                                    if (quickForm) {
                                                                        quickForm.onsubmit = function(ev) {
                                                                            ev.preventDefault();
                                                                            const formData = new FormData(quickForm);
                                                                            fetch('admin/ajax_quick_edit_save.php', {
                                                                                method: 'POST',
                                                                                body: formData
                                                                            })
                                                                            .then(async res => {
                                                                                const rawText = await res.text();
                                                                                let data;
                                                                                try {
                                                                                    data = JSON.parse(rawText);
                                                                                } catch (err) {
                                                                                    modalBody.querySelector('#quick-edit-msg').innerHTML = '<span style="color:red">Error inesperado:<br>' + rawText + '</span>';
                                                                                    return;
                                                                                }
                                                                                modalBody.querySelector('#quick-edit-msg').textContent = data.msg;
                                                                                if (data.success) {
                                                                                    // Actualizar la celda editada
                                                                                    cell.innerHTML = '<img loading="lazy" class="zoomable editable-image" src="assets/' + quickForm.querySelector('[name="caseta"]').value + '.jpg?' + new Date().getTime() + '" alt="Imagen del puesto" data-editable="true" data-field="imagen" data-id="' + id + '">';
                                                                                    // Reasignar event listener para la nueva imagen
                                                                                    const newImg = cell.querySelector('img');
                                                                                    if (newImg) {
                                                                                        newImg.addEventListener('click', function (e) {
                                                                                            e.stopPropagation();
                                                                                            zoomedImage.src = newImg.src;
                                                                                            const tr = newImg.closest('tr');
                                                                                            if (tr) {
                                                                                                zoomedName.textContent = tr.querySelector('td:nth-child(5)').textContent;
                                                                                            }
                                                                                            zoomedContainer.classList.add('show');
                                                                                            zoomedContainer.setAttribute('aria-hidden', 'false');
                                                                                        });
                                                                                    }
                                                                                    // Reasignar edición rápida para la nueva imagen
                                                                                    cell.querySelectorAll('[data-editable="true"]').forEach(newCell => {
                                                                                        newCell.addEventListener('click', function(e) {
                                                                                            e.stopPropagation();
                                                                                            const field = newCell.getAttribute('data-field');
                                                                                            const id = newCell.getAttribute('data-id');
                                                                                            const codigo_idioma = newCell.getAttribute('data-codigo_idioma');
                                                                                            let modalBody = document.getElementById('modal-body');
                                                                                            modalBody.innerHTML = '<div style="text-align:center;">Cargando...</div>';
                                                                                            document.getElementById('modal-edicion').style.display = 'flex';
                                                                                            fetch('admin/ajax_quick_edit.php', {
                                                                                                method: 'POST',
                                                                                                headers: { 'Content-Type': 'application/json' },
                                                                                                body: JSON.stringify({ id, field, codigo_idioma })
                                                                                            })
                                                                                            .then(res => res.text())
                                                                                            .then(html => {
                                                                                                modalBody.innerHTML = html;
                                                                                                setTimeout(() => {
                                                                                                    const quickForm = modalBody.querySelector('form');
                                                                                                    if (quickForm) {
                                                                                                        quickForm.onsubmit = function(ev) {
                                                                                                            ev.preventDefault();
                                                                                                            const formData = new FormData(quickForm);
                                                                                                            fetch('admin/ajax_quick_edit_save.php', {
                                                                                                                method: 'POST',
                                                                                                                body: formData
                                                                                                            })
                                                                                                            .then(async res => {
                                                                                                                const rawText = await res.text();
                                                                                                                let data;
                                                                                                                try {
                                                                                                                    data = JSON.parse(rawText);
                                                                                                                } catch (err) {
                                                                                                                    modalBody.querySelector('#quick-edit-msg').innerHTML = '<span style="color:red">Error inesperado:<br>' + rawText + '</span>';
                                                                                                                    return;
                                                                                                                }
                                                                                                                modalBody.querySelector('#quick-edit-msg').textContent = data.msg;
                                                                                                                if (data.success) {
                                                                                                                    if (quickForm.querySelector('[name="delete"]') && quickForm.querySelector('[name="delete"]').checked) {
                                                                                                                        cell.innerHTML = '<div class="editable-image-blank" data-editable="true" data-field="imagen" data-id="' + id + '" style="height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#888;font-size:0.9em;">Subir imagen</div>';
                                                                                                                    } else {
                                                                                                                        cell.innerHTML = '<img loading="lazy" class="zoomable editable-image" src="assets/' + quickForm.querySelector('[name="caseta"]').value + '.jpg?' + new Date().getTime() + '" alt="Imagen del puesto" data-editable="true" data-field="imagen" data-id="' + id + '">';
                                                                                                                    }
                                                                                                                    // Reasignar event listeners para el nuevo contenido
                                                                                                                    setTimeout(() => {
                                                                                                                        cell.querySelectorAll('[data-editable="true"]').forEach(newCell2 => {
                                                                                                                            newCell2.addEventListener('click', function(e) {
                                                                                                                                e.stopPropagation();
                                                                                                                                const field = newCell2.getAttribute('data-field');
                                                                                                                                const id = newCell2.getAttribute('data-id');
                                                                                                                                const codigo_idioma = newCell2.getAttribute('data-codigo_idioma');
                                                                                                                                let modalBody = document.getElementById('modal-body');
                                                                                                                                modalBody.innerHTML = '<div style="text-align:center;">Cargando...</div>';
                                                                                                                                document.getElementById('modal-edicion').style.display = 'flex';
                                                                                                                                fetch('admin/ajax_quick_edit.php', {
                                                                                                                                    method: 'POST',
                                                                                                                                    headers: { 'Content-Type': 'application/json' },
                                                                                                                                    body: JSON.stringify({ id, field, codigo_idioma })
                                                                                                                                })
                                                                                                                                .then(res => res.text())
                                                                                                                                .then(html => {
                                                                                                                                    modalBody.innerHTML = html;
                                                                                                                                    // ...continúa el ciclo de edición rápida...
                                                                                                                                });
                                                                                                                            });
                                                                                                                        });
                                                                                                                        // Si es imagen, reasignar zoom
                                                                                                                        const newImg2 = cell.querySelector('img');
                                                                                                                        if (newImg2) {
                                                                                                                            newImg2.addEventListener('click', function (e) {
                                                                                                                                e.stopPropagation();
                                                                                                                                zoomedImage.src = newImg2.src;
                                                                                                                                const tr = newImg2.closest('tr');
                                                                                                                                if (tr) {
                                                                                                                                    zoomedName.textContent = tr.querySelector('td:nth-child(5)').textContent;
                                                                                                                                }
                                                                                                                                zoomedContainer.classList.add('show');
                                                                                                                                zoomedContainer.setAttribute('aria-hidden', 'false');
                                                                                                                            });
                                                                                                                        }
                                                                                                                    }, 100);
                                                                                                                    setTimeout(() => {
                                                                                                                        document.getElementById('modal-edicion').style.display = 'none';
                                                                                                                    }, 800);
                                                                                                                }
                                                                                                            });
                                                                                                        };
                                                                                                    }
                                                                                                }, 100);
                                                                                            });
                                                                                        });
                                                                                    }, 100);
                                                                                    setTimeout(() => {
                                                                                        document.getElementById('modal-edicion').style.display = 'none';
                                                                                    }, 800);
                                                                                }
                                                                            });
                                                                        };
                                                                    }
                                                                }, 100);
                                                            });
                                                        });
                                                    });
                                                }, 100);
                                            }
                                            setTimeout(() => {
                                                document.getElementById('modal-edicion').style.display = 'none';
                                            }, 800);
                                        }
                                    });
                                };
                            }
                        }, 100);
                    });
                });
            });
            document.getElementById('modal-close').addEventListener('click', function() {
                document.getElementById('modal-edicion').style.display = 'none';
            });
            document.getElementById('modal-edicion').addEventListener('click', function(e) {
                if (e.target === this) {
                    document.getElementById('modal-edicion').style.display = 'none';
                }
            });
    </script>


    <?php if ($resultados_encontrados): ?>
        <script type="module" src="<?= JS_ADMIN . 'index.js' ?>"></script>
        <script>
            <?php if ($busqueda_hecha): ?>
                const inputReseteo = document.getElementById('input-reseteo');
                inputReseteo.disabled = true;
                inputReseteo.style.display = 'none';
            <?php else: ?>
                const inputDeshacer = document.getElementById('input-deshacer-busqueda');
                inputDeshacer.disabled = true;
                inputDeshacer.style.display = 'none';
                // Si no hay una búsqueda hecha, cada click en el botón de reiniciar formulario debe volver a poner el focus en el input de búsqueda para que el usuario pueda escribir otro término de búsqueda
                const inputReseteo = document.getElementById('input-reseteo');
                inputReseteo.addEventListener('click', function () {
                    document.getElementById('input-busqueda').focus();
                });
            <?php endif; ?>
        </script>
    <?php else: ?>
        <h2 style="text-align: center;">No se encontraron resultados. Configure la base de datos</h2>
    <?php endif; ?>

    <script src="<?= JS . '/helpers/dark_mode.js' ?>"></script>
</body>

</html>