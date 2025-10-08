<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/helpers/clean_input.php';

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        throw new Exception("Token CSRF inválido.");
    }

    extract($_REQUEST);
    // Limpiar intros, tabulaciones y que haya más de un espacio en blanco,
    $descripcion = htmlspecialchars(preg_replace('/\s\s+/', ' ', trim($descripcion)), ENT_QUOTES, 'UTF-8');
    $tipo = limpiar_input($tipo);

    // El tipo, si se proporciona, debe ser un string de un máximo de 50 caracteres
    if (!empty($tipo) && strlen($tipo) > 50) {
        throw new Exception("El tipo no puede tener más de 50 caracteres");
    }

    // La descripción, si se proporciona, debe ser un string de un máximo de 450 caracteres
    if (!empty($descripcion) && strlen($descripcion) > 450) {
        throw new Exception("La descripción no puede tener más de 450 caracteres");
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
        throw new Exception("Error en la conexión a la base de datos. Por favor, inténtelo de nuevo.");
    }
}