<?php
// Reporte estricto: Si MySQL falla, mostrará el error exacto
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'No se recibieron datos desde el formulario.']);
    exit;
}

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'bombaparts';

try {
    $conn = new mysqli($host, $user, $password, $database);
    $conn->set_charset("utf8mb4");

    $cliente = $data['cliente'];
    $carrito = $data['carrito'];

    // --- TRADUCIR EL TIPO DE CLIENTE ---
    $tipo_recibido = $cliente['tipo_cliente'];
    $tipo_db = ($tipo_recibido === 'Cliente final') ? 'Persona' : 'Empresa';

    // INICIAMOS TRANSACCIÓN (Para guardar en 3 tablas de forma segura)
    $conn->begin_transaction();

    // ==========================================
    // PASO 1: GUARDAR O BUSCAR AL CLIENTE
    // ==========================================
    $email = $cliente['email'];
    $stmt_check = $conn->prepare("SELECT id FROM clientes WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows > 0) {
        // El cliente ya existe, solo tomamos su ID
        $cliente_id = $res_check->fetch_assoc()['id'];
    } else {
        // Es un cliente nuevo, lo insertamos en la tabla 'clientes'
        $stmt_cli = $conn->prepare("INSERT INTO clientes (nombre, email, telefono, organizacion, tipo_cliente, pais, ubicacion_ciudad) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_cli->bind_param("sssssss", $cliente['nombre'], $email, $cliente['telefono'], $cliente['organizacion'], $tipo_db, $cliente['pais'], $cliente['ubicacion']);
        $stmt_cli->execute();
        $cliente_id = $stmt_cli->insert_id; // Obtenemos el ID nuevo
    }

    // ==========================================
    // PASO 2: GUARDAR LA CABECERA DE LA COTIZACIÓN
    // ==========================================
    $codigo_cotizacion = 'BP-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4));
    $fecha_solicitud = date('Y-m-d');
    
    // Calculamos el total
    $total_final = 0;
    foreach ($carrito as $item) {
        $precio = isset($item['precio']) ? floatval($item['precio']) : 0;
        $total_final += ($precio * intval($item['quantity']));
    }

    $pref_contacto = isset($cliente['preferencia']) ? $cliente['preferencia'] : 'No especificado';
    $notas_web = "Preferencia: " . $pref_contacto . "\nMensaje: " . $cliente['mensaje'];

    $stmt_cot = $conn->prepare("INSERT INTO cotizaciones (cliente_id, codigo_cotizacion, fecha_solicitud, notas_web, total) VALUES (?, ?, ?, ?, ?)");
    $stmt_cot->bind_param("isssd", $cliente_id, $codigo_cotizacion, $fecha_solicitud, $notas_web, $total_final);
    $stmt_cot->execute();
    $cotizacion_id = $stmt_cot->insert_id; // Obtenemos el ID de esta cotización

    // ==========================================
    // PASO 3: GUARDAR LAS PIEZAS EN DETALLES
    // ==========================================
    $stmt_det = $conn->prepare("INSERT INTO cotizacion_detalles (cotizacion_id, pieza_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($carrito as $item) {
        $pieza_id = intval($item['id']);
        $cantidad = intval($item['quantity']);
        $precio_unit = isset($item['precio']) ? floatval($item['precio']) : 0;
        $subtotal = $precio_unit * $cantidad;
        
        // Insertamos cada pieza ligada a la cotización
        $stmt_det->bind_param("iiidd", $cotizacion_id, $pieza_id, $cantidad, $precio_unit, $subtotal);
        $stmt_det->execute();
    }

    // SI TODO SALIÓ BIEN, GUARDAMOS DEFINITIVAMENTE
    $conn->commit();

    echo json_encode(['success' => true, 'codigo' => $codigo_cotizacion]);

} catch (Exception $e) {
    // Si algo falló, cancelamos todo para que no queden datos a medias
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
?>