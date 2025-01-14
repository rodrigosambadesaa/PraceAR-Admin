<?php
require_once(COMPONENT_ADMIN . 'sections' . DIRECTORY_SEPARATOR . 'header.php');
require_once 'conexion.php';
require_once(HELPERS . 'update_puestos_traducciones.php');

$codigo_idioma = filter_input(INPUT_GET, 'codigo_idioma', FILTER_SANITIZE_STRING);
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$sql_seleccion = "SELECT id, tipo, descripcion FROM puestos_traducciones WHERE codigo_idioma = ? AND puesto_id = ?";
$stmt = $conexion->prepare($sql_seleccion);
$stmt->bind_param('si', $codigo_idioma, $id);
$stmt->execute();
$resultado = $stmt->get_result();

$data = $resultado->fetch_assoc();
if (!$data) {
    die('No se encontraron datos');
}
?>

<h2>Traducción</h2>
<form class="pure-form" action="#" method="POST" id="formulario">
    <label for="tipo">Tipo</label>
    <input type="text" name="tipo" value="<?= htmlspecialchars($data['tipo'] ?? "") ?>">
    <label for="descripcion">Descripción</label>
    <textarea name="descripcion" id="descripcion" cols="30" rows="10"
        maxlength="450"><?= htmlspecialchars($data['descripcion'] ?? "") ?></textarea>
    <input type="hidden" name="id_traduccion" value="<?= htmlspecialchars($data['id'] ?? "") ?>">
    <input type="submit" name="submit" value="Actualizar">
</form>
<?= htmlspecialchars($mensaje ?? "") ?>
<script src="./js/editar_traducciones.js"></script>
</body>

</html>