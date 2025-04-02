<?php
require_once HELPERS . "clean_input.php";
require_once HELPERS . "get_language.php";
require_once HELPERS . "save_image.php";
require_once HELPERS . "delete_image.php";
require_once HELPERS . "verify_malicious_photo.php";

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        throw new Exception("Token CSRF inválido.");
    }

    extract($_POST);

    // Comprobar si hay una imagen para subir en el formulario
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Primero, verificar que realmente sea una imagen .jpg o .jpeg de un tamaño máximo de 2MB
        $permitidos = ['image/jpeg', 'image/jpg'];
        $limite_kb = 2048;

        if (!in_array($_FILES['imagen']['type'], $permitidos) || $_FILES['imagen']['size'] > $limite_kb * 1024) {
            throw new Exception("El archivo no es una imagen válida o es demasiado grande. Por favor, suba un archivo con extensión .jpg o .jpeg y un tamaño máximo de 2MB.");
        }

        // Verificar si la imagen es maliciosa
        $maliciosa = check_virus_total($_FILES['imagen']['tmp_name']);

        if ($maliciosa) {
            throw new Exception("La imagen subida es maliciosa. Por favor, desinféctela y suba una imagen válida. Puede ser necesario desinfectar el dispositivo desde el que se capturó o el dispositivo desde el que se subió la imagen.");
        }

        $is_imagen = save_image($_FILES['imagen'], $caseta);
        if (!$is_imagen["success"]) {
            $conexion->close();
            $mensaje = $is_imagen["message"];
            return;
        }
    }

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

    $activo = isset($activo) ? 1 : 0;

    // El valor de activo solo puede ser un número natural que sea 0 o 1
    if (!is_int($activo) || ($activo != 0 && $activo != 1)) {
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

    // El teléfono debe ser un string
    if (!is_string($telefono)) {
        throw new Exception("El teléfono debe ser un texto");
    }

    if (!empty($telefono) && strlen($telefono) > 15) {
        throw new Exception("El teléfono no puede tener más de 15 caracteres");
    }

    $caseta_padre = limpiar_input($caseta_padre);
    $update_caseta_padre = $caseta_padre === '' ? "caseta_padre = NULL" : "caseta_padre = '" . $caseta_padre . "'";

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
                    activo = $activo,
                    nombre = '$nombre',
                    contacto = '$contacto',
                    telefono = '$telefono',
                    id_nave = $id_nave,
                    tipo_unity = '$tipo_unity',
                    $update_caseta_padre
                    WHERE id =" . $_GET['id'];

    // echo $sql_actualizacion;

    if ($conexion->query($sql_actualizacion) === TRUE) {
        $mensaje = "<span id='mensaje_correcto'>Puesto actualizado correctamente</span>";
        $conexion->close();

        header("Location: $protocolo/$servidor/$subdominio/?lang=" . get_language() . "#row_" . $_GET['id']);
    } else {
        throw new Exception("Error al actualizar el puesto.");
    }

}