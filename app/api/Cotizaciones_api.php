<?php
// ============================================================
//  cotizaciones_api.php  —  Endpoint AJAX (JSON)
//  Acciones: get_detalle | aprobar | rechazar | actualizar_vigencia
// ============================================================
declare(strict_types=1);

// Evita que PHP escupa errores HTML que rompen el JSON
ini_set('display_errors', '0'); 
error_reporting(E_ALL);

require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json; charset=utf-8');

// ── Helpers ──────────────────────────────────────────────────
function responder(bool $ok, array $datos = [], string $mensaje = ''): never {
    echo json_encode([
        'ok'      => $ok,
        'mensaje' => $mensaje,
        'datos'   => $datos,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function clase_estado(string $estado): string {
    return match($estado) {
        'aprobada'  => 'status-approved',
        'rechazada' => 'status-rejected',
        default     => 'status-pending',
    };
}

// ── Entrada ───────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
$id     = isset($_GET['id'])  ? (int)$_GET['id']  : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if (!$accion) {
    responder(false, [], 'Acción no especificada.');
}

// ============================================================
//  GET: get_detalle
// ============================================================
if ($method === 'GET' && $accion === 'get_detalle') {
    if ($id < 1) {
        responder(false, [], 'ID inválido.');
    }

    try {
        // 1. Buscamos los datos generales
        $stmt = $pdo->prepare(
            "SELECT
                id, codigo_cotizacion, cliente_nombre, cliente_email,
                cliente_telefono, organizacion, tipo_cliente,
                ubicacion_ciudad, pais, estado_republica,
                fecha_solicitud, vigencia_dias, notas_web,
                estado_cotizacion, total, creado_en
             FROM cotizaciones
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            responder(false, [], 'Cotización no encontrada.');
        }

        // 2. Buscamos los productos (Protegido por try-catch)
        try {
            $stmt_prod = $pdo->prepare(
                "SELECT id, producto_sku, producto_nombre, cantidad, precio_unitario, subtotal 
                 FROM cotizacion_detalles 
                 WHERE cotizacion_id = :id"
            );
            $stmt_prod->execute([':id' => $id]);
            $row['productos'] = $stmt_prod->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $row['productos'] = [];
        }

        $row['clase_estado'] = clase_estado($row['estado_cotizacion']);
        $row['estado_label'] = ucfirst($row['estado_cotizacion']);

        responder(true, $row);

    } catch (PDOException $e) {
        responder(false, [], "Error SQL: " . $e->getMessage());
    }
}

// ============================================================
//  POST: aprobar | rechazar
// ============================================================
if ($method === 'POST' && in_array($accion, ['aprobar', 'rechazar'], true)) {
    if ($id < 1) {
        responder(false, [], 'ID inválido.');
    }

    try {
        $nuevo_estado = ($accion === 'aprobar') ? 'aprobada' : 'rechazada';

        $check = $pdo->prepare(
            "SELECT estado_cotizacion, codigo_cotizacion FROM cotizaciones WHERE id = :id LIMIT 1"
        );
        $check->execute([':id' => $id]);
        $row_check = $check->fetch(PDO::FETCH_ASSOC);

        if (!$row_check) {
            responder(false, [], 'Cotización no encontrada.');
        }
        
        $actual = $row_check['estado_cotizacion'];
        $codigo_cotizacion = $row_check['codigo_cotizacion'];

        if ($actual !== 'pendiente') {
            responder(false, [], "La cotización ya fue {$actual}. No se puede modificar.");
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "UPDATE cotizaciones
             SET estado_cotizacion = :estado
             WHERE id = :id"
        );
        $stmt->execute([
            ':estado' => $nuevo_estado,
            ':id'     => $id,
        ]);

        // ============================================================
        //  AQUÍ ESTÁ LA SOLUCIÓN DEL HISTORIAL
        // ============================================================
        $modulo = 'Cotizaciones';
        $accion_historial = ucfirst($nuevo_estado);
        $texto_detalle = "Se ha marcado como " . $nuevo_estado . " la cotización " . $codigo_cotizacion;

        // Se usa la columna 'detalle' explícitamente para evitar el error 1048
        $stmt_historial = $pdo->prepare(
            "INSERT INTO historial_actividades (modulo, accion, detalle) 
             VALUES (:modulo, :accion, :detalle)"
        );
        
        $stmt_historial->execute([
            ':modulo'  => $modulo,
            ':accion'  => $accion_historial,
            ':detalle' => $texto_detalle
        ]);

        $pdo->commit();

        responder(true, [
            'nuevo_estado' => $nuevo_estado,
            'clase_estado' => clase_estado($nuevo_estado),
            'estado_label' => ucfirst($nuevo_estado),
        ], "Cotización {$nuevo_estado} correctamente.");

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        responder(false, [], "Error de Base de Datos: " . $e->getMessage());
    }
}

// ============================================================
//  POST: actualizar_vigencia
// ============================================================
if ($method === 'POST' && $accion === 'actualizar_vigencia') {
    if ($id < 1) {
        responder(false, [], 'ID inválido.');
    }

    $vigencia = isset($_POST['vigencia_dias']) ? (int)$_POST['vigencia_dias'] : 0;

    if ($vigencia < 1 || $vigencia > 365) {
        responder(false, [], 'Vigencia debe estar entre 1 y 365 días.');
    }

    try {
        $stmt = $pdo->prepare(
            "UPDATE cotizaciones
             SET vigencia_dias = :vigencia
             WHERE id = :id"
        );
        $stmt->execute([
            ':vigencia' => $vigencia,
            ':id'       => $id,
        ]);

        responder(true, ['vigencia_dias' => $vigencia], 'Vigencia actualizada.');
    } catch (PDOException $e) {
        responder(false, [], "Error SQL: " . $e->getMessage());
    }
}

// ── Acción no reconocida ──────────────────────────────────────
responder(false, [], "Acción '{$accion}' no reconocida.");