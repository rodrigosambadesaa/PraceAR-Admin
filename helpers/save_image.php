<?php
function saveImage($files, $newFileName)
{
    // Tamaño máximo permitido: 2 MB
    $status = [
        'success' => false,
        'message' => ''
    ];

    $maxFileSize = 2 * 1024 * 1024;

    // Verificar si realmente se ha subido un archivo
    if (!isset($files['tmp_name']) || empty($files['tmp_name'])) {
        // No se subió ninguna imagen, devolver éxito ya que no se requiere subida.
        $status['success'] = true;
        return $status;
    }

    // Obtener información del archivo
    $fileTmpPath = $files['tmp_name'];
    $fileName = $files['name'];
    $fileSize = $files['size']; // Tamaño del archivo
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Verificar que la extensión sea jpg o jpeg
    $allowedExtensions = array('jpg', 'jpeg');
    if (in_array($fileExtension, $allowedExtensions)) {

        // Verificar que el archivo no exceda el tamaño máximo permitido
        if ($fileSize > $maxFileSize) {
            $status['message'] = '<span id="mensaje_error">El archivo es demasiado grande. El tamaño máximo permitido es 2 MB.</span>';
            return $status; // Si el tamaño es demasiado grande, devolver mensaje de error
        }

        // Asegurarse de que el archivo sea una imagen válida
        $check = getimagesize($fileTmpPath);
        if ($check !== false) {

            // Limpiar y crear el nombre de archivo basado en el nombre de la caseta        
            $newFileName = $newFileName . '.jpg';

            // Definir la ruta para guardar el archivo               
            $dest_path = ASSETS . $newFileName;

            // Mover el archivo a la carpeta assets
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
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
