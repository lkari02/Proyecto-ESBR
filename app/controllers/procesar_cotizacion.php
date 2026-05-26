<?php
// procesar_cotizacion.php

// 1. Forzar a que la respuesta sea estrictamente JSON
header('Content-Type: application/json');
ob_start(); // Iniciar buffer para evitar basura en la respuesta

$host = '127.0.0.1';
$dbname = 'bombaparts';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la BD']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        // ========================================================
        // ACCIÓN 1: ACTUALIZAR SOLO LA VIGENCIA (Botón Confirmar)
        // ========================================================
        if ($action === 'actualizar_vigencia') {
            $codigo = $_POST['codigo'];
            $vigencia = (int)$_POST['vigencia'];

            $stmt = $pdo->prepare("UPDATE cotizaciones SET vigencia_dias = :v WHERE codigo_cotizacion = :c");
            $stmt->execute([':v' => $vigencia, ':c' => $codigo]);

            // Registrar en historial
            $logSql = "INSERT INTO historial_actividades (usuario, accion, detalle, modulo, fecha_movimiento) VALUES ('Admin', 'EDITAR', ?, 'Cotizaciones', NOW())";
            $pdo->prepare($logSql)->execute(["Se actualizó la vigencia a $vigencia días para la cotización $codigo"]);

            ob_clean();
            echo json_encode(['status' => 'success']);
            exit;
        }

        // ========================================================
        // ACCIÓN 2: APROBAR O RECHAZAR LA COTIZACIÓN COMPLETA
        // ========================================================
        if ($action === 'procesar_cotizacion') {
            $codigo = $_POST['id']; 
            $estado = strtolower($_POST['estado']); // 'aprobada' o 'rechazada'
            $vigencia = (int)$_POST['vigencia_dias'];
            
            // Actualizar estado y vigencia en la BD
            $stmt = $pdo->prepare("UPDATE cotizaciones SET estado_cotizacion = :e, vigencia_dias = :v WHERE codigo_cotizacion = :c");
            $stmt->execute([':e' => $estado, ':v' => $vigencia, ':c' => $codigo]);

            // Registrar en tu historial
            $accionLog = strtoupper($estado); // 'APROBADA' o 'RECHAZADA'
            $logSql = "INSERT INTO historial_actividades (usuario, accion, detalle, modulo, fecha_movimiento) VALUES ('Admin', ?, ?, 'Cotizaciones', NOW())";
            $pdo->prepare($logSql)->execute([$accionLog, "Se marcó como $estado la cotización $codigo"]);

            // Nota: Si necesitas guardar los precios actualizados de los productos, 
            // leerías $_POST['productos'] aquí y harías el UPDATE a la tabla correspondiente.

            ob_clean();
            echo json_encode(['status' => 'success']);
            exit;
        }

        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
        
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Error SQL: ' . $e->getMessage()]);
    }
}
?>