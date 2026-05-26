<?php
error_reporting(0);
ini_set('display_errors', 0);

require '../../fpdf/fpdf.php'; 

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'bombaparts';

$conn = new mysqli($host, $user, $password, $database);
$conn->set_charset("utf8mb4");

// 1. Detectar el idioma solicitado por la URL (por defecto español)
$lang = isset($_GET['lang']) && $_GET['lang'] === 'en' ? 'en' : 'es';

// 2. Textos estáticos según idioma
$txt_title = $lang === 'en' ? 'Parts & Equipment Catalog' : 'Catálogo de Refacciones y Equipos';
$txt_subtitle = $lang === 'en' ? 'BombaParts - Client Catalog (Prices subject to formal quote)' : 'BombaParts - Catálogo para Clientes (Precios sujetos a cotización formal)';
$txt_brand = $lang === 'en' ? 'Brand: ' : 'Marca: ';
$txt_no_parts = $lang === 'en' ? 'No active parts found.' : 'No se encontraron piezas activas.';
$txt_page = $lang === 'en' ? 'Page ' : 'Página ';
$txt_no_img = $lang === 'en' ? 'No Image' : 'Sin Imagen';

class PDF extends FPDF {
    public $estado;
    function Header() {
        // --- DISEÑO DEL ENCABEZADO CORPORATIVO ---
        // 1. Fondo Azul Marino Profundo
        $this->SetFillColor(0, 48, 87);
        $this->Rect(0, 0, 210, 32, 'F'); // 210 es el ancho A4 Portrait

        // 2. Línea de Acento Roja
        $this->SetFillColor(180, 25, 35);
        $this->Rect(0, 32, 210, 1.5, 'F');

        // 3. Insertar Logo (AJUSTA LA RUTA DE TU IMAGEN AQUÍ)
        if(file_exists('../../public/assets/img/logo2.png')) {
            $this->Image('../../public/assets/img/logo2.png', 12, 6, 20);
        }

        // 4. Textos del Encabezado
        $this->SetY(10);
        $this->SetX(35);
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(100, 8, utf8_decode('EQUIPOS DE BOMBEO'), 0, 0, 'L');
        
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(200, 200, 200);
        $this->Cell(60, 8, utf8_decode('Catalogo'), 0, 1, 'R');

        $this->SetX(35);
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(180, 25, 35); // Rojo del acento
        $this->Cell(100, 5, utf8_decode('SERVICIO & REFACCIONES'), 0, 0, 'L');
        $this->Ln(18);
    }

    function Footer() {
        // Posición a 20 mm del final para dar espacio al contacto
        $this->SetY(-20);
        
        // Datos de Contacto
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(0, 48, 87);
        $this->Cell(0, 5, utf8_decode('ventas@equiposbombeo.com.mx  |  +52 55 1904 3197'), 0, 1, 'C');
        
        // Paginación
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(126, 148, 173);
        $this->Cell(0, 6, utf8_decode('Este documento es oficial. Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->titleTxt = $txt_title;
$pdf->subtitleTxt = $txt_subtitle;
$pdf->pageTxt = $txt_page;

$pdf->AliasNbPages();
$pdf->AddPage();

// 3. Consulta incluyendo traducciones y previniendo duplicados
$sql = "SELECT 
            p.sku, p.nombre AS nombre_base, p.descripcion_tecnica AS desc_base,
            m.nombre AS marca_nombre, img.ruta_imagen,
            t.nombre AS trad_nombre, t.descripcion_tecnica AS trad_desc
        FROM piezas p
        LEFT JOIN marcas m ON p.marca_id = m.id
        LEFT JOIN piezas_imagenes img ON p.id = img.pieza_id AND img.es_principal = 1
        LEFT JOIN piezas_traducciones t ON p.id = t.pieza_id AND t.idioma = '$lang'
        WHERE p.activo = 1
        GROUP BY p.id
        ORDER BY p.nombre ASC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        
        // Determinar textos finales (usar traducción si existe, si no, el original)
        $finalName = (!empty($row['trad_nombre']) && !str_starts_with($row['trad_nombre'], 'Sin ')) ? $row['trad_nombre'] : $row['nombre_base'];
        $finalDesc = (!empty($row['trad_desc']) && !str_starts_with($row['trad_desc'], 'Sin ')) ? $row['trad_desc'] : $row['desc_base'];

        // Control de salto de página
        if($pdf->GetY() > 240) {
            $pdf->AddPage();
        }

        $startY = $pdf->GetY();
        $imgSize = 35; // Tamaño de la imagen 35x35mm
        
        // --- COLUMNA IZQUIERDA: IMAGEN ---
        // Usamos __DIR__ para obtener la ruta física exacta desde donde está el PHP
        $imgPath = __DIR__ . '/uploads/piezas/' . basename($row['ruta_imagen']);
        
        if (!empty($row['ruta_imagen']) && file_exists($imgPath)) {
            // Imagen encontrada
            $pdf->Image($imgPath, 10, $startY, $imgSize, $imgSize, 'PNG');
        } else {
            // Cuadro de "Sin imagen" profesional
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->SetFillColor(245, 245, 245);
            $pdf->Rect(10, $startY, $imgSize, $imgSize, 'DF');
            $pdf->SetXY(10, $startY + 15);
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->SetTextColor(150, 150, 150);
            $pdf->Cell($imgSize, 5, utf8_decode($txt_no_img), 0, 0, 'C');
        }

        // --- COLUMNA DERECHA: TEXTOS ---
        $textX = 10 + $imgSize + 5; // Posición X después de la imagen
        $pdf->SetXY($textX, $startY);

        // Nombre del Producto
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(0, 45, 95);
        $pdf->Cell(0, 6, utf8_decode(strtoupper($finalName)), 0, 1, 'L');

        // SKU y Marca (Con fondo sutil)
        $pdf->SetXY($textX, $pdf->GetY() + 1);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetFillColor(240, 244, 248);
        $pdf->Cell(0, 6, utf8_decode(' SKU: ' . $row['sku'] . '  |  ' . $txt_brand . ($row['marca_nombre'] ?? 'Genérica') . ' '), 0, 1, 'L', true);

        // Descripción Técnica
        $pdf->SetXY($textX, $pdf->GetY() + 3);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(100, 100, 100);
        
        $descClean = strip_tags($finalDesc);
        $descClean = (strlen($descClean) > 280) ? substr($descClean, 0, 280) . '...' : $descClean;
        
        $pdf->MultiCell(0, 4.5, utf8_decode($descClean));

        // --- LÍNEA SEPARADORA INFERIOR ---
        // Calculamos cuál columna fue más alta para no encimar el siguiente producto
        $endY = max($pdf->GetY(), $startY + $imgSize) + 5;
        $pdf->SetY($endY);
        $pdf->SetDrawColor(220, 220, 220);
        $pdf->Line(10, $endY, 200, $endY);
        $pdf->Ln(5);
    }
} else {
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, utf8_decode($txt_no_parts), 0, 1, 'C');
}

$conn->close();

$pdf->Output('I', 'Catalogo_BombaParts.pdf');
?>