<?php
function save_image($files, $new_file_name)
{
    // Tamaño máximo permitido: 2 MB
    $status = [
        'success' => false,
        'message' => ''
    ];

    $max_file_size = 2 * 1024 * 1024;

    // Verificar si realmente se ha subido un archivo
    if (!isset($files['tmp_name']) || empty($files['tmp_name'])) {
        // No se subió ninguna imagen, devolver éxito ya que no se requiere subida.
        $status['success'] = true;
        return $status;
    }

    // Obtener información del archivo
    $file_tmp_path = $files['tmp_name'];
    $file_name = $files['name'];
    $file_size = $files['size']; // Tamaño del archivo
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Verificar que la extensión sea jpg o jpeg
    $allowed_extensions = array('jpg', 'jpeg');
    if (in_array($file_extension, $allowed_extensions)) {

        // Verificar que el archivo no exceda el tamaño máximo permitido
        if ($file_size > $max_file_size) {
            $status['message'] = '<span id="mensaje_error">El archivo es demasiado grande. El tamaño máximo permitido es 2 MB.</span>';
            return $status; // Si el tamaño es demasiado grande, devolver mensaje de error
        }

        // Asegurarse de que el archivo sea una imagen válida
        $check = getimagesize($file_tmp_path);
        if ($check !== false) {

            // Limpiar y crear el nombre de archivo basado en el nombre de la caseta        
            $new_file_name = $new_file_name . '.jpg';

            // Definir la ruta para guardar el archivo               
            $dest_path = ASSETS . $new_file_name;

            // Mover el archivo a la carpeta assets
            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $status['success'] = true;
                $status['message'] = '<span id="mensaje_exito">El archivo se ha subido correctamente.</span>';
            } else {
                $status['message'] = '<span id="mensaje_error">Ha ocurrido un error al subir el archivo.</span>';
            }
        } else {
            $status['message'] = '<span id="mensaje_error">El archivo no es una imagen válida.</span>';
        }
    } else {
        $status['message'] = '<span id="mensaje_error">Solo se permiten archivos con extensión .jpg o .jpeg.</span>';
    }

    return $status;
}
