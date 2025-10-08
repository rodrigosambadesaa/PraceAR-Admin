<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/helpers/clean_input.php';
require_once dirname(__DIR__) . '/helpers/get_language.php';
require_once dirname(__DIR__) . '/helpers/save_image.php';
require_once dirname(__DIR__) . '/helpers/delete_image.php';
require_once dirname(__DIR__) . '/helpers/verify_malicious_photo.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $conexion;
    if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        throw new Exception("Token CSRF inválido.");
    }

    $stall_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($stall_id === null || $stall_id === false) {
        $stall_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    }
    if ($stall_id === null || $stall_id === false) {
        throw new Exception("Identificador de puesto no válido.");
    }

    $caseta = $_POST['caseta'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $contacto = $_POST['contacto'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $tipo_unity = $_POST['tipo_unity'] ?? '';
    $caseta_padre = $_POST['caseta_padre'] ?? '';
    $eliminar_imagen = $_POST['eliminar_imagen'] ?? null;

    $id_nave = filter_input(INPUT_POST, 'id_nave', FILTER_VALIDATE_INT);
    if ($id_nave === null || $id_nave === false) {
        throw new Exception("Debe seleccionar una nave válida.");
    }

    $activo_filtrado = filter_input(INPUT_POST, 'activo', FILTER_SANITIZE_NUMBER_INT);
    if ($activo_filtrado !== null && $activo_filtrado !== false) {
        $activo_filtrado = intval($activo_filtrado);
        if ($activo_filtrado !== 0 && $activo_filtrado !== 1) {
            throw new Exception("El valor de activo debe ser 0 o 1.");
        }
        $activo = 1;
    } else {
        $activo = 0;
    }

    // Comprobar si hay una imagen para subir en el formulario
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Primero, verificar que realmente sea una imagen .jpg o .jpeg de un tamaño máximo de 2MB
        $permitidos = ['image/jpeg', 'image/jpg'];
        $limite_kb = 2048;

        if (!in_array($_FILES['imagen']['type'], $permitidos) || $_FILES['imagen']['size'] > $limite_kb * 1024) {
            throw new Exception("El archivo no es una imagen válida o es demasiado grande. Por favor, suba un archivo con extensión .jpg o .jpeg y un tamaño máximo de 2MB.");
        }

        // Verificar si la imagen es maliciosa
        $scanResult = check_virus_total($_FILES['imagen']['tmp_name']);

        if (!$scanResult['success']) {
            throw new Exception($scanResult['message']);
        }

        if ($scanResult['is_malicious']) {
            throw new Exception($scanResult['message'] ?: "La imagen subida es maliciosa. Por favor, desinféctela y suba una imagen válida. Puede ser necesario desinfectar el dispositivo desde el que se capturó o el dispositivo desde el que se subió la imagen.");
        }

        $is_imagen = save_image($_FILES['imagen'], $caseta);
        if (!$is_imagen["success"]) {
            $conexion->close();
            $mensaje = $is_imagen["message"];
            return;
        }
    } elseif (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Si hay un error diferente a "no file uploaded", manejarlo
        throw new Exception("Error al subir la imagen. Código de error: " . $_FILES['imagen']['error']);
    }
    // Si no hay imagen subida ($_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE), no hacer nada

    if (isset($eliminar_imagen) && is_string($eliminar_imagen)) {
        try {
            $eliminar_imagen = intval($eliminar_imagen);
            if ($eliminar_imagen === 1) {
                $is_imagen = delete_image($caseta);
            }
        } catch (Exception $e) {
            throw new Exception("Error al convertir eliminar_imagen a entero: " . $e->getMessage());
        }
    }

    // El valor de activo solo puede ser un número natural que sea 0 o 1
    if (!in_array($activo, [0, 1], true)) {
        throw new Exception("El valor de activo debe ser 0 o 1.");
    }

    $nombre = limpiar_input($nombre);

    // El nombre debe ser un string
    if (!is_string($nombre)) {
        throw new Exception("El nombre debe ser un texto");
    }

    if (!empty($nombre) && strlen($nombre) > 50) {
        throw new Exception("El nombre no puede tener más de 50 caracteres");
    }

    // $imagen = limpiarInput($imagen);

    $contacto = limpiar_input($contacto);

    // El contacto debe ser un string
    if (!is_string($contacto)) {
        throw new Exception("El contacto debe ser un texto");
    }

    if (!empty($contacto) && strlen($contacto) > 250) {
        throw new Exception("El contacto no puede tener más de 250 caracteres");
    }

    $telefono = limpiar_input($telefono);
    $tipo_unity = limpiar_input($tipo_unity);

    // El teléfono debe ser un string
    if (!is_string($telefono)) {
        throw new Exception("El teléfono debe ser un texto");
    }

    if (!empty($telefono) && strlen($telefono) > 15) {
        throw new Exception("El teléfono no puede tener más de 15 caracteres");
    }

    $caseta_padre = limpiar_input($caseta_padre);
    $caseta_padre_param = $caseta_padre === '' ? null : $caseta_padre;

    // La caseta padre, si se ha especificado, debe ser un string de exactamente cinco caracteres
    if (!empty($caseta_padre) && (!is_string($caseta_padre) || strlen($caseta_padre) !== 5)) {
        throw new Exception("La caseta padre debe ser un texto de exactamente cinco caracteres");
    }

    // La caseta padre, si se ha especificado, debe cumplir los siguientes requisitos: Sus dos primeras letras deben ser "CE", "CO", "MC", "NA", o "NC", y las tres últimas un número entre 001 y 370
    if (!empty($caseta_padre)) {
        if (!preg_match('/^(CE|CO|MC|NA|NC)([0-9]{3})$/', $caseta_padre)) {
            throw new Exception("La caseta padre debe tener el formato correcto. El formato correcto es dos letras seguidas de tres números. Las dos letras deben ser 'CE', 'CO', 'MC', 'NA', o 'NC', y los tres números deben estar entre 001 y 370.");
        }
        $numero_caseta_padre = intval(substr($caseta_padre, 2));
        if ($numero_caseta_padre < 1 || $numero_caseta_padre > 370) {
            throw new Exception("El número de caseta padre debe estar entre 001 y 370.");
        }
    }

    $sql_actualizacion = "UPDATE puestos SET
                    activo = ?,
                    nombre = ?,
                    contacto = ?,
                    telefono = ?,
                    id_nave = ?,
                    tipo_unity = ?,
                    caseta_padre = ?
                    WHERE id = ?";

    $stmt = $conexion->prepare($sql_actualizacion);

    if (!$stmt) {
        throw new Exception("No se pudo preparar la actualización del puesto.");
    }

    if (!$stmt->bind_param(
        "isssissi",
        $activo,
        $nombre,
        $contacto,
        $telefono,
        $id_nave,
        $tipo_unity,
        $caseta_padre_param,
        $stall_id
    )) {
        throw new Exception("No se pudieron vincular los datos del puesto.");
    }

    if ($stmt->execute()) {
        $mensaje = "<span id='mensaje_correcto'>Puesto actualizado correctamente</span>";
        $stmt->close();
        $conexion->close();

        header("Location: $protocolo/$servidor/$subdominio/?lang=" . get_language() . "#row_" . $stall_id);
    } else {
        $stmt->close();
        throw new Exception("Error al actualizar el puesto.");
    }

}