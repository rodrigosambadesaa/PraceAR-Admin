<?php
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    extract($_REQUEST);
    // Limpiar intros, tabulaciones y que haya más de un espacio en blanco,
    $descripcion = preg_replace('/\s\s+/', ' ', trim($descripcion));

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
        header("Location: $protocolo/$servidor/$subdominio/?lang=" . getLanguage() . "#row_" . $_GET['id']);
    } else {
        $mensaje = '<span id="mensaje_error">Error en la conexión</span>';
    }
}
