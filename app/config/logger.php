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
?>