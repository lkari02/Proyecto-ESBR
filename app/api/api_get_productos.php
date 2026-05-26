<?php
// 1. Iniciamos el buffer para atrapar cualquier error o texto basura
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

$host     = 'localhost';
$user     = 'root';
$password = ''; 
$database = 'bombaparts'; 

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    ob_end_clean(); // Limpiamos la basura
    header('Content-Type: application/json');
    echo json_encode(["error" => "Error de conexión"]);
    exit;
}
$conn->set_charset("utf8mb4");

// 2. Consulta de piezas, traducciones y compatibilidad
$sql = "SELECT 
            p.id, p.sku, p.precio_unitario, p.nombre AS nombre_base, p.descripcion_tecnica AS desc_base,
            m.nombre AS marca_nombre,
            img.ruta_imagen, img.es_principal,
            t.idioma, t.nombre AS trad_nombre, t.descripcion_tecnica AS trad_desc,
            mb.nombre AS modelo_compatible
        FROM piezas p
        LEFT JOIN marcas m ON p.marca_id = m.id
        LEFT JOIN piezas_imagenes img ON p.id = img.pieza_id
        LEFT JOIN piezas_traducciones t ON p.id = t.pieza_id
        LEFT JOIN pieza_modelo pm ON p.id = pm.pieza_id
        LEFT JOIN modelos_bomba mb ON pm.modelo_id = mb.id
        WHERE p.activo = 1";

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $busqueda = $conn->real_escape_string($_GET['q']);
    $sql .= " AND (p.sku LIKE '%$busqueda%' 
              OR p.nombre LIKE '%$busqueda%'
              OR t.nombre LIKE '%$busqueda%' 
              OR t.descripcion_tecnica LIKE '%$busqueda%')";
}

$result = $conn->query($sql);
$productos_raw = [];

if ($result) {
    while($row = $result->fetch_assoc()) {
        $id = $row['id'];
        
        if (!isset($productos_raw[$id])) {
            $productos_raw[$id] = [
                'id'             => (int)$id,
                'sku'            => $row['sku'],
                'name'           => $row['nombre_base'] ?? 'Sin nombre',
                'desc'           => $row['desc_base'] ?? 'Sin descripción',
                'marca'          => $row['marca_nombre'] ?? 'Genérica',
                'precio'         => (float)$row['precio_unitario'],
                'img'            => 'https://placehold.co/400x300/002d5f/ffffff?text=Sin+Imagen', 
                'allImages'      => [],
                'compatibilidad' => [],
                'traducciones'   => [
                    'es' => ['nombre' => '', 'desc' => ''],
                    'en' => ['nombre' => '', 'desc' => '']
                ]
            ];
        }

        // Traducciones
        if (!empty($row['idioma'])) {
            $lang = $row['idioma'];
            $productos_raw[$id]['traducciones'][$lang]['nombre'] = $row['trad_nombre'];
            $productos_raw[$id]['traducciones'][$lang]['desc'] = $row['trad_desc'];
        }

        // Compatibilidad
        if (!empty($row['modelo_compatible']) && !in_array($row['modelo_compatible'], $productos_raw[$id]['compatibilidad'])) {
            $productos_raw[$id]['compatibilidad'][] = $row['modelo_compatible'];
        }

        // Imágenes
        if (!empty($row['ruta_imagen'])) {
            $nombre_archivo = basename($row['ruta_imagen']);
            $fullPath = '/proyecto_claud/uploads/piezas/' . $nombre_archivo;
            
            if (!in_array($fullPath, $productos_raw[$id]['allImages'])) {
                $productos_raw[$id]['allImages'][] = $fullPath;
            }

            if ($row['es_principal'] == 1 || strpos($productos_raw[$id]['img'], 'placehold') !== false) {
                $productos_raw[$id]['img'] = $fullPath;
            }
        }
    }
}

// Limitar a 5 imágenes
foreach ($productos_raw as &$prod) {
    if (count($prod['allImages']) > 5) {
        $prod['allImages'] = array_slice($prod['allImages'], 0, 5);
    }
}

$productos_final = array_values($productos_raw);
$conn->close();

// 3. Destruimos cualquier texto de error atrapado antes de imprimir el JSON
ob_end_clean(); 
header('Content-Type: application/json; charset=utf-8');
echo json_encode($productos_final, JSON_UNESCAPED_UNICODE);
exit;
?>