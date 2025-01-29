<?php
require_once HELPERS . "clean_input.php";

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        $mensaje = '<span id="mensaje_error">CSRF token no válido</span>';
        return;
    }

    extract($_REQUEST);
    // Limpiar intros, tabulaciones y que haya más de un espacio en blanco,
    $descripcion = htmlspecialchars(preg_replace('/\s\s+/', ' ', trim($descripcion)), ENT_QUOTES, 'UTF-8');
    $tipo = limpiar_input($tipo);

    // El tipo, si se proporciona, debe ser un string de un máximo de 50 caracteres
    if (!empty($tipo) && strlen($tipo) > 50) {
        $mensaje = '<span id="mensaje_error">El tipo no puede tener más de 50 caracteres</span>';
        return;
    }

    // La descripción, si se proporciona, debe ser un string de un máximo de 450 caracteres
    if (!empty($descripcion) && strlen($descripcion) > 450) {
        $mensaje = '<span id="mensaje_error">La descripción no puede tener más de 450 caracteres</span>';
        return;
    }

    $sql_actualizacion = "UPDATE puestos_traducciones 
                          SET tipo = ?, 
                          descripcion = ? 
                          WHERE id = ?";

    $stmt = $conexion->prepare($sql_actualizacion);
    $stmt->bind_param("ssi", $tipo, $descripcion, $id_traduccion);

    if ($stmt->execute()) {
        //$mensaje = "<span id='mensaje_correcto'>Puesto actualizado correctamente</span>";
        $stmt->close();
        $conexion->close();
        header("Location: $protocolo/$servidor/$subdominio/?lang=" . get_language() . "#row_" . $_GET['id']);
    } else {
        $mensaje = '<span id="mensaje_error">Error en la conexión</span>';
    }
}