<?php
// 1. Declaramos las variables primero
$host = "localhost";
$user = "root";       // Usuario por defecto en XAMPP
$password = "";       // Contraseña por defecto en XAMPP (vacía)
$database = "bombaparts"; // <-- ¡CAMBIA ESTO por el nombre real de tu BD!

// 2. Ahora sí hacemos la conexión (línea 8 aprox)
$conn = new mysqli($host, $user, $password, $database);

// 3. Verificamos si hay errores
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Opcional: Para que los caracteres especiales (ñ, acentos) se vean bien
$conn->set_charset("utf8");

// En tu computadora local usarás esta:
define('BASE_URL', 'http://localhost/Proyecto');

// Cuando subas el sitio al servidor real, solo comentas la de arriba y usas esta:
// define('BASE_URL', 'https://bombaparts.com.mx');
?>