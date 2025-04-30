<?php
// require_once "../connection.php";
// session_start();

// Insertamos el cierre de sesión en la tabla accesos
$insert_sql = "INSERT INTO accesos (id, id_usuario, ip, user_agent, fecha, tipo) VALUES (NULL, ?, ?, ?, ?, ?)";
$insert_stmt = $conexion->prepare($insert_sql);
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$date = date('Y-m-d H:i:s');
$tipo = 'desconexion';
$insert_stmt->bind_param('sssss', $_SESSION['id'], $ip_address, $user_agent, $date, $tipo);
$insert_stmt->execute();

// Destruir la sesión
// Eliminar todas las variables de sesión
$_SESSION = [];

// Si se desea destruir la sesión completamente, también se debe eliminar la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
session_destroy();

// Limpiar el buffer de salida y enviar encabezados
ob_end_flush();
header("Location: index.php");
exit();
