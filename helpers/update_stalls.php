<?php
require_once HELPERS . "clean_input.php";
require_once HELPERS . "get_language.php";
require_once HELPERS . "save_image.php";
require_once HELPERS . "delete_image.php";
require_once HELPERS . "verify_malicious_photo.php";

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        $mensaje = '<span id="mensaje_error">CSRF token no válido</span>';
        return;
    }

    extract($_POST);

    // Comprobar si hay una imagen para subir en el formulario
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Primero, verificar que realmente sea una imagen .jpg o .jpeg de un tamaño máximo de 2MB
        $permitidos = ['image/jpeg', 'image/jpg'];
        $limite_kb = 2048;

        if (!in_array($_FILES['imagen']['type'], $permitidos) || $_FILES['imagen']['size'] > $limite_kb * 1024) {
            $mensaje = '<span id="mensaje_error">La imagen debe ser .jpg o .jpeg y no debe pesar más de 2MB</span>';
            return;
        }

        // Verificar si la imagen es maliciosa
        $maliciosa = check_virus_total($_FILES['imagen']['tmp_name']);

        if ($maliciosa) {
            $mensaje = '<span id="mensaje_error">La imagen es maliciosa. Desinféctela o pida ayuda para hacerlo o capture otra imagen tras desinfectar su dispositivo.</span>';
            return;
        }

        $is_imagen = save_image($_FILES['imagen'], $caseta);
        if (!$is_imagen["success"]) {
            $conexion->close();
            $mensaje = $is_imagen["message"];
            return;
        }
    }

    if (isset($eliminar_imagen) && $eliminar_imagen == 1 && is_string($eliminar_imagen)) {
        $is_imagen = delete_image($caseta);
    }

    $activo = isset($activo) ? 1 : 0;

    // El valor de activo solo puede ser un número natural que sea 0 o 1
    if (!is_int($activo) || ($activo != 0 && $activo != 1)) {
        $mensaje = '<span id="mensaje_error">El valor de activo debe ser 0 o 1</span>';
        return;
    }

    $nombre = limpiar_input($nombre);

    // El nombre debe ser un string
    if (!is_string($nombre)) {
        $mensaje = '<span id="mensaje_error">El nombre debe ser un texto</span>';
        return;
    }

    if (!empty($nombre) && strlen($nombre) > 50) {
        $mensaje = '<span id="mensaje_error">El nombre no puede tener más de 50 caracteres</span>';
        return;
    }

    // $imagen = limpiarInput($imagen);

    $contacto = limpiar_input($contacto);

    // El contacto debe ser un string
    if (!is_string($contacto)) {
        $mensaje = '<span id="mensaje_error">El contacto debe ser un texto</span>';
        return;
    }

    if (!empty($contacto) && strlen($contacto) > 250) {
        $mensaje = '<span id="mensaje_error">El contacto no puede tener más de 250 caracteres</span>';
        return;
    }

    $telefono = limpiar_input($telefono);

    // El teléfono debe ser un string
    if (!is_string($telefono)) {
        $mensaje = '<span id="mensaje_error">El teléfono debe ser un texto</span>';
        return;
    }

    if (!empty($telefono) && strlen($telefono) > 15) {
        $mensaje = '<span id="mensaje_error">El teléfono no puede tener más de 15 caracteres</span>';
        return;
    }

    $update_caseta_padre = trim($caseta_padre) === '' ? "caseta_padre = NULL" : "caseta_padre = '" . trim($caseta_padre) . "'";

    // La caseta padre, si se ha especificado, debe ser un string de exactamente cinco caracteres
    if (!empty($caseta_padre) && (!is_string($caseta_padre) || strlen($caseta_padre) !== 5)) {
        $mensaje = '<span id="mensaje_error">La caseta padre debe ser un texto de exactamente cinco caracteres</span>';
        return;
    }

    // La caseta padre, si se ha especificado, debe cumplir los siguientes requisitos: Las dos primeras letras de caseta padre, si se ha introducido, deben ser "CE", "CO", "MC", "NA", o "NC", y las tres últimas un número entre 1 y 370. Los números se cuentan 001, 002, 003, ..., 370
    if (!empty($caseta_padre) && !preg_match('/^(CE|CO|MC|NA|NC)([0-9]{3})$/', $caseta_padre)) {
        $mensaje = '<span id="mensaje_error">La caseta padre debe tener el formato correcto</span>';
        return;
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
        $mensaje = '<span id="mensaje_error">Error al actualizar el puesto</span>';
    }

}