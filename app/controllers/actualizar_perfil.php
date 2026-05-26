<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificamos usando la clave correcta 'user_id'
if (!isset($_SESSION['user_id'])) {
    header("Location: /Proyecto/public/admin/login.php");
    exit;
}

// Conexión a la base de datos
require_once '../config/conexion.php'; 

$id_usuario = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['correo']);
    $password = trim($_POST['password']);

    if (empty($nombre) || empty($email)) {
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?profile_error=1");
        exit;
    }

    if (!empty($password)) {
        // Si cambió contraseña, la encriptamos y actualizamos todo
        $password_encriptada = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE usuarios SET nombre = ?, email = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nombre, $email, $password_encriptada, $id_usuario);
    } else {
        // Si no cambió contraseña, solo actualizamos nombre y correo
        $sql = "UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nombre, $email, $id_usuario);
    }

    if ($stmt->execute()) {
        // ACTUALIZACIÓN DE SESIÓN: Modificamos el nombre en la sesión activa
        // para que se actualice de inmediato en las pantallas donde lo uses.
        $_SESSION['user_nombre'] = $nombre;
        
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?profile_success=1");
    } else {
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?profile_error=1");
    }
    
    $stmt->close();
    $conn->close();
}
?>