<?php
require_once(COMPONENT_ADMIN . 'sections' . DIRECTORY_SEPARATOR . 'header.php');
require_once 'conexion.php';
require_once(HELPERS . 'update_puestos_traducciones.php');
extract($_GET);

$sql_seleccion = "SELECT id, tipo, descripcion FROM puestos_traducciones WHERE codigo_idioma = ? AND puesto_id = ?";
$stmt = $conexion->prepare($sql_seleccion);
$stmt->bind_param('si', $codigo_idioma, $id);
$stmt->execute();
$resultado = $stmt->get_result();

$data = $resultado->fetch_assoc();
if (!$data)
    die('No se encontraron datos');
?>

<h2>Traducción</h2>
<form class="pure-form" action="#" method="POST">
    <label for="tipo">Tipo</label>
    <input type="text" name="tipo" value="<?= $data['tipo'] ?? "" ?>">
    <label for="descripcion">Descripción</label>
    <textarea name="descripcion" id="" cols="30" rows="10" maxlength="450"><?= $data['descripcion'] ?? "" ?></textarea>
    <input type="hidden" name="id_traduccion" value="<?= $data['id'] ?? "" ?>">
    <input type="submit" name="submit" value="Actualizar">
</form>
<?= $mensaje ?>
</body>

</html>