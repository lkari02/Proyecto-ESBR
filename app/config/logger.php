<?php
/**
 * Función centralizada para registrar movimientos en el sistema.
 * 
 * EJEMPLOS DE USO:
 * registrarHistorial($pdo, 1, 'Abel Corona', 'CREAR', 'Catálogo', 'Se agregó Bomba Sumergible', 'Inventario', 'BP-GRU-002');
 * registrarHistorial($pdo, 1, 'Abel Corona', 'LOGIN', 'Sistema', 'Inicio de sesión exitoso', 'Acceso', null);
 */
function registrarHistorial($pdo, $id_usuario, $nombre_usuario, $tipo_accion, $categoria_evento, $descripcion, $modulo, $id_referencia = null) {
    try {
        $sql = "INSERT INTO log_historial (id_usuario, nombre_usuario, tipo_accion, categoria_evento, descripcion_detallada, modulo, id_referencia) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id_usuario, 
            $nombre_usuario, 
            $tipo_accion, 
            $categoria_evento, 
            $descripcion, 
            $modulo, 
            $id_referencia
        ]);
        return true;
    } catch (\PDOException $e) {
        // En producción, podrías escribir este error en un archivo .log de texto
        error_log("Error al guardar historial: " . $e->getMessage());
        return false;
    }
}

session_start();

// Opcional: Si quieres registrar en el historial que el usuario cerró sesión, 
// hazlo AQUÍ ANTES de destruir las variables.
// require_once 'ruta_a_tu_conexion.php';
// require_once 'ruta_a_tu_archivo_de_historial.php';
// if(isset($_SESSION['user_id'])) {
//     registrarHistorial($pdo, $_SESSION['user_id'], $_SESSION['user_nombre'], 'LOGOUT', 'Sistema', 'Cierre de sesión', 'Acceso', null);
// }

// 1. Vaciamos todas las variables de sesión (le quitamos las llaves al guardia)
$_SESSION = array();

// 2. Destruimos la sesión en el servidor
session_destroy();

// 3. Redirección ABSOLUTA y directa a tu login
header("Location: /Proyecto/public/admin/login.php");
exit;
?>