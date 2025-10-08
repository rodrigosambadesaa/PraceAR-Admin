<?php
// admin/ajax_quick_edit.php
session_start();
require_once(dirname(__DIR__) . '/helpers/clean_input.php');
require_once(dirname(__DIR__) . '/helpers/verify_malicious_photo.php');
require_once(dirname(__DIR__) . '/config/env_loader.php');
require_once(dirname(__DIR__) . '/connection.php');

header('Content-Type: text/html; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int)$input['id'] : 0;
$field = isset($input['field']) ? limpiar_input($input['field']) : '';
$codigo_idioma = isset($input['codigo_idioma']) ? limpiar_input($input['codigo_idioma']) : '';

if (!$id || !$field) {
    echo '<div class="admin-error-text">Petición inválida</div>';
    exit;
}

// CSRF
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

// Renderizar el formulario según el campo
if (in_array($field, ['nombre', 'contacto', 'telefono', 'caseta_padre'])) {
    // Campo de texto
    $sql = "SELECT `$field` FROM puestos WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $valor = $row[$field] ?? '';
    echo "<form id='quick-edit-form' enctype='multipart/form-data'>
        <input type='hidden' name='csrf' value='$csrf'>
        <input type='hidden' name='id' value='$id'>
        <input type='hidden' name='field' value='$field'>
        <label for='quick-edit-input'>$field</label>
        <input type='text' id='quick-edit-input' name='value' value='" . htmlspecialchars($valor) . "' required>
        <button type='submit'>Guardar</button>
    </form>
    <div id='quick-edit-msg'></div>
    <script>
    document.getElementById('quick-edit-form').onsubmit = function(e) {
        e.preventDefault();
        const form = e.target;
        fetch('admin/ajax_quick_edit_save.php', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('quick-edit-msg').textContent = data.msg;
            if (data.success) setTimeout(() => window.location.reload(), 700);
        });
    };
    </script>";
    exit;
}

if ($field === 'imagen') {
    // Imagen
    $sql = "SELECT caseta FROM puestos WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $caseta = $row['caseta'] ?? '';
    $ruta = "assets/$caseta.jpg";
    $imgHtml = file_exists(dirname(__DIR__) . "/$ruta") ? "<img src='/$ruta' style='max-width:200px;max-height:200px;'>" : "<div>No hay imagen</div>";
    echo "<form id='quick-edit-img-form' enctype='multipart/form-data'>
        <input type='hidden' name='csrf' value='$csrf'>
        <input type='hidden' name='id' value='$id'>
        <input type='hidden' name='field' value='imagen'>
        $imgHtml<br>
        <label for='quick-edit-img'>Reemplazar imagen (.jpg):</label>
        <input type='file' id='quick-edit-img' name='imagen' accept='.jpg,.jpeg'><br>
        <label><input type='checkbox' name='eliminar_imagen' value='1'> Eliminar imagen</label><br>
        <button type='submit'>Guardar</button>
    </form>
    <div id='quick-edit-msg'></div>
    <script>
    document.getElementById('quick-edit-img-form').onsubmit = function(e) {
        e.preventDefault();
        const form = e.target;
        fetch('admin/ajax_quick_edit_save.php', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('quick-edit-msg').textContent = data.msg;
            if (data.success) setTimeout(() => window.location.reload(), 700);
        });
    };
    </script>";
    exit;
}

if (in_array($field, ['tipo', 'descripcion', 'traduccion'])) {
    // Traducción
    $sql = "SELECT id, tipo, descripcion FROM puestos_traducciones WHERE codigo_idioma = ? AND puesto_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('si', $codigo_idioma, $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if (!$row) {
        echo '<div class="admin-error-text">No se encontró la traducción</div>';
        exit;
    }
    echo "<form id='quick-edit-trad-form'>
        <input type='hidden' name='csrf' value='$csrf'>
        <input type='hidden' name='id' value='$id'>
        <input type='hidden' name='field' value='traduccion'>
        <input type='hidden' name='codigo_idioma' value='" . htmlspecialchars($codigo_idioma) . "'>
        <label for='quick-edit-tipo'>Tipo</label>
        <input type='text' id='quick-edit-tipo' name='tipo' value='" . htmlspecialchars($row['tipo']) . "' required><br>
        <label for='quick-edit-descripcion'>Descripción</label>
        <textarea id='quick-edit-descripcion' name='descripcion' maxlength='450'>" . htmlspecialchars($row['descripcion']) . "</textarea><br>
        <button type='submit'>Guardar</button>
    </form>
    <div id='quick-edit-msg'></div>
    <script>
    document.getElementById('quick-edit-trad-form').onsubmit = function(e) {
        e.preventDefault();
        const form = e.target;
        fetch('admin/ajax_quick_edit_save.php', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('quick-edit-msg').textContent = data.msg;
            if (data.success) setTimeout(() => window.location.reload(), 700);
        });
    };
    </script>";
    exit;
}

echo '<div class="admin-error-text">Campo no editable</div>';
exit;
