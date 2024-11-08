<?php
require_once HELPERS . "clean-input.php";
require_once HELPERS . "get-language.php";
require_once HELPERS . "save-image.php";
require_once HELPERS . "delete-image.php";

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    extract($_POST);

    // Comprobar si hay una imagen para subir en el formulario
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $isImagen = saveImage($_FILES['imagen'], $caseta);
        if (!$isImagen["success"]) {
            $conexion->close();
            $mensaje = $isImagen["message"];
            return;
        }
    }

    if (isset($eliminar_imagen) && $eliminar_imagen === 'eliminar') {
        $isImagen = deleteImage($caseta);
    }

    $activo = isset($activo) ? 1 : 0;

    $nombre = limpiarInput($nombre);

    // $imagen = limpiarInput($imagen);

    $contacto = limpiarInput($contacto);

    $telefono = limpiarInput($telefono);

    $update_caseta_padre = trim($caseta_padre) === '' ? "caseta_padre = NULL" : "caseta_padre = '" . trim($caseta_padre) . "'";

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

        header("Location: $protocolo/$servidor/$subdominio/?lang=" . getLanguage() . "#row_" . $_GET['id']);
    } else {
        $mensaje = '<span id="mensaje_error">Error al actualizar el puesto</span>';
    }

}