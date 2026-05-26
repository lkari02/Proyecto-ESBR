<?php
// Configuración de conexión
$host = "localhost";
$user = "root";
$pass = "";
$db   = "bombaparts";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);

$accion = $_POST['accion'] ?? '';

// --- ACCIÓN: OBTENER DATOS DE UNA PIEZA (Para editar) ---
// En acciones_piezas.php
if ($accion == 'obtener') {
    $id = intval($_POST['id']); // Validamos que sea un número
    $sql = "SELECT p.*, m.nombre as marca_nombre 
            FROM piezas p 
            JOIN marcas m ON p.marca_id = m.id 
            WHERE p.id = $id";
    
    $res = $conn->query($sql);
    if($res && $row = $res->fetch_assoc()){
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Pieza no encontrada"]);
    }
    exit;
}

// --- ACCIÓN: GUARDAR / ACTUALIZAR ---
if ($accion == 'guardar') {
    $id      = $_POST['id'] ?? null;
    $sku     = $_POST['sku'];
    $nombre  = $_POST['nombre_es'];
    $marca   = $_POST['marca_id'];
    $precio  = $_POST['precio'];
    $stock   = $_POST['stock'];
    $desc    = $_POST['desc_es'];
    $modelo  = $_POST['modelo_id']; // ID del modelo seleccionado

    if ($id) {
        // UPDATE
        $sql = "UPDATE piezas SET sku='$sku', nombre='$nombre', marca_id='$marca', 
                precio_unitario='$precio', stock='$stock', descripcion_tecnica='$desc' 
                WHERE id=$id";
    } else {
        // INSERT
        $sql = "INSERT INTO piezas (sku, nombre, marca_id, precio_unitario, stock, descripcion_tecnica) 
                VALUES ('$sku', '$nombre', '$marca', '$precio', '$stock', '$desc')";
    }

    if ($conn->query($sql)) {
        $pieza_id = $id ? $id : $conn->insert_id;
        // Actualizar relación con modelo
        $conn->query("DELETE FROM pieza_modelo WHERE pieza_id = $pieza_id");
        if($modelo) $conn->query("INSERT INTO pieza_modelo (pieza_id, modelo_id) VALUES ($pieza_id, $modelo)");
        echo "success";
    } else {
        echo "error: " . $conn->error;
    }
    exit;
}

// --- ACCIÓN: ELIMINAR (Lógico o Físico) ---
if ($accion == 'eliminar') {
    $id = $_POST['id'];
    // Marcamos como inactivo (mejor que borrar físicamente)
    $sql = "UPDATE piezas SET activo = 0 WHERE id = $id";
    if ($conn->query($sql)) echo "success";
    exit;
}
?>