<?php
declare(strict_types=1);

require_once HELPERS . "clean_input.php";
require_once HELPERS . "get_language.php";
require_once HELPERS . "save_image.php";
require_once HELPERS . "delete_image.php";
require_once HELPERS . "verify_malicious_photo.php";

$mensaje = "";
$fila = null;

// Obtener el ID del puesto a editar
if (isset($_GET["id"])) {
    $id = (int) $_GET["id"];
    $sql = "SELECT * FROM puestos WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();
}

// Procesar el formulario de actualización
// Procesar el formulario de actualización
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_GET["id"])) {
    if (
        !isset($_SESSION["csrf"]) ||
        !isset($_POST["csrf"]) ||
        !hash_equals($_SESSION["csrf"], $_POST["csrf"])
    ) {
        die("Token CSRF inválido.");
    }

    $id = (int) $_GET["id"];

    // Recoger y validar datos
    $nombre = limpiar_input($_POST["nombre"] ?? "");
    $contacto = limpiar_input($_POST["contacto"] ?? "");
    $telefono = limpiar_input($_POST["telefono"] ?? "");
    $tipo_unity = limpiar_input($_POST["tipo_unity"] ?? "");
    $id_nave = (int) $_POST["id_nave"]; // Validación básica, idealmente verificar contra DB
    $caseta_padre = limpiar_input($_POST["caseta_padre"] ?? "");
    $caseta_padre_param = $caseta_padre === "" ? null : $caseta_padre;

    $activo_filtrado = filter_input(
        INPUT_POST,
        "activo",
        FILTER_SANITIZE_NUMBER_INT,
    );
    $activo =
        $activo_filtrado !== null &&
        $activo_filtrado !== false &&
        intval($activo_filtrado) === 1
            ? 1
            : 0;

    // Obtener caseta actual para manejo de imágenes
    $sql_caseta = "SELECT caseta FROM puestos WHERE id = ?";
    $stmt_caseta = $conexion->prepare($sql_caseta);
    $stmt_caseta->bind_param("i", $id);
    $stmt_caseta->execute();
    $res_caseta = $stmt_caseta->get_result();
    $row_caseta = $res_caseta->fetch_assoc();
    $caseta_codigo = $row_caseta["caseta"] ?? "";

    try {
        // Validaciones
        if (!is_string($nombre) || (!empty($nombre) && strlen($nombre) > 50)) {
            throw new Exception(
                "El nombre no puede tener más de 50 caracteres.",
            );
        }
        if (
            !is_string($contacto) ||
            (!empty($contacto) && strlen($contacto) > 250)
        ) {
            throw new Exception(
                "El contacto no puede tener más de 250 caracteres.",
            );
        }
        if (
            !is_string($telefono) ||
            (!empty($telefono) && strlen($telefono) > 15)
        ) {
            throw new Exception(
                "El teléfono no puede tener más de 15 caracteres.",
            );
        }
        if (!empty($caseta_padre)) {
            if (!preg_match('/^(CE|CO|MC|NA|NC)([0-9]{3})$/', $caseta_padre)) {
                throw new Exception(
                    "La caseta padre debe tener el formato correcto (ej: CE001).",
                );
            }
            $numero_caseta_padre = intval(substr($caseta_padre, 2));
            if ($numero_caseta_padre < 1 || $numero_caseta_padre > 370) {
                throw new Exception(
                    "El número de caseta padre debe estar entre 001 y 370.",
                );
            }
        }

        // Manejo de Imagen
        // Manejo de Imagen
        $eliminar_imagen = $_POST["eliminar_imagen"] ?? null;
        if ($eliminar_imagen && intval($eliminar_imagen) === 1) {
            // Asegurar que tenemos el código de caseta
            if (empty($caseta_codigo)) {
                throw new Exception(
                    "No se pudo obtener el código de la caseta para eliminar la imagen.",
                );
            }

            if (!delete_image($caseta_codigo)) {
                // Si falla, verificar si el archivo realmente existe para dar un error preciso
                if (file_exists(ASSETS . $caseta_codigo . ".jpg")) {
                    throw new Exception(
                        "Error al eliminar la imagen del servidor. Verifique permisos.",
                    );
                }
                // Si no existe, no es un error crítico, ya se eliminó o nunca existió
            }
        }

        if (
            isset($_FILES["imagen"]) &&
            $_FILES["imagen"]["error"] === UPLOAD_ERR_OK
        ) {
            $permitidos = ["image/jpeg", "image/jpg"];
            $limite_kb = 2048;
            if (
                !in_array($_FILES["imagen"]["type"], $permitidos) ||
                $_FILES["imagen"]["size"] > $limite_kb * 1024
            ) {
                throw new Exception(
                    "El archivo no es una imagen válida o es demasiado grande (máx 2MB, .jpg/.jpeg).",
                );
            }

            $scanResult = check_virus_total($_FILES["imagen"]["tmp_name"]);
            if (!$scanResult["success"]) {
                throw new Exception($scanResult["message"]);
            }
            if ($scanResult["is_malicious"]) {
                throw new Exception(
                    $scanResult["message"] ?: "Imagen maliciosa detectada.",
                );
            }

            $is_imagen = save_image($_FILES["imagen"], $caseta_codigo);
            if (!$is_imagen["success"]) {
                throw new Exception($is_imagen["message"]);
            }
        }

        $sql_update = "UPDATE puestos SET 
                        activo = ?, 
                        nombre = ?, 
                        contacto = ?, 
                        telefono = ?, 
                        id_nave = ?, 
                        tipo_unity = ?, 
                        caseta_padre = ? 
                        WHERE id = ?";

        $stmt_update = $conexion->prepare($sql_update);
        if (!$stmt_update) {
            throw new Exception("Error preparando la consulta.");
        }

        $stmt_update->bind_param(
            "isssissi",
            $activo,
            $nombre,
            $contacto,
            $telefono,
            $id_nave,
            $tipo_unity,
            $caseta_padre_param,
            $id,
        );

        if ($stmt_update->execute()) {
            $mensaje = "Datos actualizados correctamente.";
            // Redirigir al índice de administración
            // Construimos la URL usando BASE_URL y admin/index.php para ser explícitos
            header("Location: " . BASE_URL . "?lang=" . get_language());
            exit();
        } else {
            throw new Exception("Error SQL: " . $conexion->error);
        }
    } catch (Exception $e) {
        $mensaje = "Error: " . $e->getMessage();
    }
}

if (!$fila) {
    echo "<div class='container'><p class='admin-error-text'>No se encontró el puesto especificado.</p></div>";
    return;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - PraceAR - Editar Datos Generales de Puesto - Página de administración</title>
     <style>
        <?php
        require_once CSS_ADMIN . "theme.css";
        require_once CSS_ADMIN . "header.css";
        require_once CSS_ADMIN . "edit_admin.css";
        ?>
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel='icon' href='./img/favicon.png' type='image/png'>
    <link rel="stylesheet" href="./css/darkmode.css">

    <!-- Iconos para dispositivos Apple -->
    <link rel="apple-touch-icon" href="./img/favicon.png">
</head>

<body class="admin-edit">
    <?php require_once SECTIONS . "header.php"; ?>

    <main class="container">
        <h1 id="cabecera_pagina_edicion">Editar Datos Generales de Puesto</h1>
        
        <article>
            <form action="?page=edit&id=<?= htmlspecialchars(
                (string) $id,
            ) ?>" method="POST" class="form-group" id="formulario-editar" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?= $_SESSION["csrf"] ??
                    "" ?>">
                
                <div class="grid">
                    <div>
                        <label for="activo">Activo</label>
                        <input type="checkbox" id="activo" name="activo" value="1" <?= $fila[
                            "activo"
                        ]
                            ? "checked"
                            : "" ?>>
                    </div>
                    <div>
                         <!-- Espacio vacío para alinear si es necesario, o poner otro campo aquí -->
                    </div>
                </div>

                <?php
                $ruta_a_imagen =
                    "assets/" . htmlspecialchars($fila["caseta"]) . ".jpg";
                $imagen_encontrada = file_exists($ruta_a_imagen);
                ?>
                
                <?php if ($imagen_encontrada) { ?>
                    <span>Imagen actual</span>
                    <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 1rem;">
                        <img src="<?= htmlspecialchars($ruta_a_imagen) ?>"
                            alt="Imagen del puesto <?= htmlspecialchars(
                                $fila["nombre"],
                            ) ?>" class="zoomable"
                            style="object-fit: cover; height: 300px; width: 300px; display: block; margin: 0 auto;">
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="imagen">Reemplazar imagen (.jpg, máx 2MB)</label>
                        <input type="file" id="imagen" name="imagen" accept=".jpg, .jpeg" aria-label="Reemplazar imagen">
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                         <label for="eliminar-imagen">
                            <input type="checkbox" id="eliminar-imagen" name="eliminar_imagen" value="1">
                            Eliminar imagen actual
                         </label>
                    </div>
                <?php } else { ?>
                    <label for="imagen">Subir Imagen (.jpg, máx 2MB)</label>
                    <input type="file" id="imagen" name="imagen" accept=".jpg, .jpeg" aria-label="Subir imagen">
                <?php } ?>

                <div class="grid">
                    <div>
                        <label for="caseta">Caseta</label>
                        <input type="text" id="caseta" name="caseta" value="<?= htmlspecialchars(
                            $fila["caseta"] ?? "",
                        ) ?>" readonly aria-label="Código de caseta">
                    </div>
                    
                    <div>
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars(
                            $fila["nombre"] ?? "",
                        ) ?>" 
                            placeholder="Nombre del puesto" aria-label="Nombre del puesto">
                    </div>
                </div>

                <div class="grid">
                    <div>
                        <label for="contacto">Información de Contacto</label>
                        <input type="text" id="contacto" name="contacto" aria-label="Información de contacto" value="<?= htmlspecialchars(
                            $fila["contacto"] ?? "",
                        ) ?>">
                    </div>

                    <div>
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars(
                            $fila["telefono"] ?? "",
                        ) ?>"
                            placeholder="Teléfono de contacto. Por ejemplo: '981 123 456'" aria-label="Teléfono de contacto">
                    </div>
                </div>

                <div class="grid">
                    <div>
                        <label for="tipo-unity">Tipo en Unity <span class="admin-required" aria-hidden="true">*</span></label>
                        <select name="tipo_unity" id="tipo-unity" aria-required="true">
                            <?php foreach (UNITY_TYPE as $key => $value) { ?>
                                <option value="<?= $key ?>" <?= ($fila[
    "tipo_unity"
] ??
    "") ==
$key
    ? "selected"
    : "" ?>>
                                    <?= $value ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div>
                        <label for="id-nave">ID Nave <span class="admin-required" aria-hidden="true">*</span></label>
                        <select required id="id-nave" name="id_nave" aria-required="true">
                            <?php
                            $sql_naves = "SELECT * FROM naves";
                            $resultado_naves = $conexion->query($sql_naves);
                            while (
                                $fila_naves = $resultado_naves->fetch_assoc()
                            ) { ?>
                                <option value="<?= htmlspecialchars(
                                    (string) $fila_naves["id"],
                                ) ?>" <?= $fila["id_nave"] == $fila_naves["id"]
    ? "selected"
    : "" ?>>
                                    <?= htmlspecialchars($fila_naves["tipo"]) ?>
                                </option>
                            <?php }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label for="caseta-padre">Caseta padre</label>
                    <input name="caseta_padre" type="text" id="caseta-padre"
                        value="<?= htmlspecialchars(
                            $fila["caseta_padre"] ?? "",
                        ) ?>" placeholder="Código de caseta padre"
                        aria-label="Caseta padre">
                </div>
                
                <div id="div-botones">
                    <input id="actualizar" type="submit" value="Actualizar" aria-label="Actualizar datos del puesto">
                </div>
            </form>
        </article>

        <p class="note admin-error-text" style="text-align: center;">Los campos marcados con <span class="admin-required" aria-hidden="true">*</span>
            son
            obligatorios</p>

        <div id="zoomed-image-container" class="zoomed-container" role="dialog" aria-hidden="true">
            <button id="zoomed-close" class="zoomed-close" aria-label="Cerrar imagen ampliada">&times;</button>
            <img id="zoomed-image" src="" alt="">
        </div>

        <div id="mensaje" role="alert"><?= htmlspecialchars($mensaje) ?></div>

        <?php if ($fila) { ?>
            <script type="module" src="<?= htmlspecialchars(
                JS_ADMIN,
            ) ?>edit_stall.js"></script>
            <?php } ?>

        <script>
            window.BASE_URL = "<?= BASE_URL ?>";
        </script>
        <script src="<?= JS . "/helpers/dark_mode.js" ?>"></script>
    </main>
</body>

</html>