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

// Todo debe ocurrir dentro de este bloque POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        // ========================================================
        // ACCIÓN 1: ACTUALIZAR SOLO LA VIGENCIA (Botón Confirmar)
        // ========================================================
        if ($action === 'actualizar_vigencia') {
            $codigo = trim($_POST['codigo']);
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
            $codigo = trim($_POST['id']); 
            $estado = strtolower(trim($_POST['estado'])); // 'aprobada' o 'rechazada'
            $vigencia = (int)$_POST['vigencia_dias'];
            
            // Actualizar estado y vigencia en la BD
            $stmt = $pdo->prepare("UPDATE cotizaciones SET estado_cotizacion = :e, vigencia_dias = :v WHERE codigo_cotizacion = :c");
            $stmt->execute([':e' => $estado, ':v' => $vigencia, ':c' => $codigo]);

            // Registrar en tu historial
            $accionLog = strtoupper($estado); // 'APROBADA' o 'RECHAZADA'
            $logSql = "INSERT INTO historial_actividades (usuario, accion, detalle, modulo, fecha_movimiento) VALUES ('Admin', ?, ?, 'Cotizaciones', NOW())";
            $pdo->prepare($logSql)->execute([$accionLog, "Se marcó como $estado la cotización $codigo"]);

            ob_clean();
            echo json_encode(['status' => 'success']);
            exit;
        }

        // ========================================================
        // ACCIÓN 3: ACTUALIZAR PRECIOS (ANTES DE GENERAR PDF)
        // ========================================================
        if ($action === 'actualizar_precios') {
            $codigo_cotizacion = trim($_POST['id'] ?? ''); 
            $productos_json = $_POST['productos'] ?? '[]';
            $productos = json_decode($productos_json, true);

            if (!$codigo_cotizacion || !is_array($productos)) {
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
                exit;
            }

            // 1. Obtener el ID numérico de la cotización
            $stmt_cot = $pdo->prepare("SELECT id FROM cotizaciones WHERE codigo_cotizacion = :codigo LIMIT 1");
            $stmt_cot->execute([':codigo' => $codigo_cotizacion]);
            $cotizacion_id = $stmt_cot->fetchColumn();

            if (!$cotizacion_id) {
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'Cotización no encontrada']);
                exit;
            }

            // 2. Guardado directo usando el ID de la pieza (Evitamos JOINs en el UPDATE)
            foreach ($productos as $prod) {
                $precio_limpio = floatval($prod['precio_unitario']);
                $sku_limpio = trim($prod['sku']); // Evitamos fallos por espacios en blanco

                // A. Buscamos el ID interno de la pieza usando su SKU
                $stmt_pieza = $pdo->prepare("SELECT id FROM piezas WHERE sku = :sku LIMIT 1");
                $stmt_pieza->execute([':sku' => $sku_limpio]);
                $pieza_id = $stmt_pieza->fetchColumn();

                if ($pieza_id) {
                    // B. Si existe la pieza, actualizamos directamente con los dos IDs
                    $stmt_upd = $pdo->prepare("
                        UPDATE cotizacion_detalles 
                        SET precio_unitario = :precio, subtotal = (cantidad * :precio)
                        WHERE cotizacion_id = :cot_id AND pieza_id = :pieza_id
                    ");
                    $stmt_upd->execute([
                        ':precio' => $precio_limpio,
                        ':cot_id' => $cotizacion_id,
                        ':pieza_id' => $pieza_id
                    ]);
                }
            }

            // 3. Actualizar el total general de la cotización
            $sql_total = "
                UPDATE cotizaciones 
                SET total = (
                    SELECT COALESCE(SUM(subtotal), 0)
                    FROM cotizacion_detalles 
                    WHERE cotizacion_id = :cot_id
                )
                WHERE id = :cot_id
            ";
            $stmt_total = $pdo->prepare($sql_total);
            $stmt_total->execute([':cot_id' => $cotizacion_id]);

            ob_clean();
            echo json_encode(['status' => 'success']);
            exit;
        }

        // ========================================================
        // FALLBACK: SI MANDA UNA ACCIÓN INVENTADA O VACÍA
        // ========================================================
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
        exit; // <- Este exit faltaba en tu código original
        
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Error SQL: ' . $e->getMessage()]);
        exit;
    }
}
?>