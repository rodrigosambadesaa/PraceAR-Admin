<?php
declare(strict_types=1);
// admin/ajax_quick_edit_save.php
session_start();
require_once(dirname(__DIR__) . '/helpers/clean_input.php');
require_once(dirname(__DIR__) . '/helpers/verify_malicious_photo.php');
require_once(dirname(__DIR__) . '/config/env_loader.php');
require_once(dirname(__DIR__) . '/connection.php');

header('Content-Type: application/json; charset=utf-8');

function response(bool $success, string $msg): void {
    echo json_encode(['success' => $success, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

// CSRF
if (!isset($_SESSION['csrf']) || !isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
    response(false, 'Petición no válida (CSRF)');
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$field = isset($_POST['field']) ? limpiar_input($_POST['field']) : '';

if (!$id || !$field) {
    response(false, 'Petición inválida');
}

if (in_array($field, ['nombre', 'contacto', 'telefono', 'caseta_padre'])) {
    $value = isset($_POST['value']) ? limpiar_input($_POST['value']) : '';
    $sql = "UPDATE puestos SET `$field` = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('si', $value, $id);
    if ($stmt->execute()) {
        response(true, 'Actualizado correctamente');
    } else {
        response(false, 'Error al actualizar');
    }
}

if ($field === 'imagen') {
    // Obtener caseta
    $sql = "SELECT caseta FROM puestos WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $caseta = $row['caseta'] ?? '';
    $ruta = dirname(__DIR__) . "/assets/$caseta.jpg";
    // Eliminar imagen
    if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] == '1') {
        if (file_exists($ruta)) {
            unlink($ruta);
            response(true, 'Imagen eliminada');
        } else {
            response(false, 'No se encontró la imagen');
        }
    }
    // Subir nueva imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['imagen']['tmp_name'];
        $mime = mime_content_type($tmp);
        if ($mime !== 'image/jpeg') {
            response(false, 'Solo se permite imagen JPG');
        }
        $check = check_virus_total($tmp);
        if (!$check['success']) {
            response(false, 'Error al analizar la imagen: ' . $check['message']);
        }
        if ($check['is_malicious']) {
            response(false, 'La imagen es potencialmente maliciosa: ' . $check['message']);
        }
        if (move_uploaded_file($tmp, $ruta)) {
            response(true, 'Imagen actualizada');
        } else {
            response(false, 'Error al guardar la imagen');
        }
    }
    response(false, 'No se envió imagen ni se pidió eliminar');
}

if ($field === 'traduccion' || $field === 'tipo' || $field === 'descripcion') {
    $codigo_idioma = isset($_POST['codigo_idioma']) ? limpiar_input($_POST['codigo_idioma']) : '';
    $tipo = isset($_POST['tipo']) ? limpiar_input($_POST['tipo']) : '';
    $descripcion = isset($_POST['descripcion']) ? limpiar_input($_POST['descripcion']) : '';
    $sql = "UPDATE puestos_traducciones SET tipo = ?, descripcion = ? WHERE codigo_idioma = ? AND puesto_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('sssi', $tipo, $descripcion, $codigo_idioma, $id);
    if ($stmt->execute()) {
        response(true, 'Traducción actualizada');
    } else {
        response(false, 'Error al actualizar traducción');
    }
}

response(false, 'Campo no editable');
