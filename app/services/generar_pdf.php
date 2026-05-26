<?php
require '../../fpdf/fpdf.php'; 

// 1. CONEXIÓN
$host = '127.0.0.1'; $db = 'bombaparts'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (\PDOException $e) { die("Error: " . $e->getMessage()); }

// 2. DISEÑO DEL PDF (UI/UX PRO)
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
        $this->Cell(60, 8, utf8_decode('CATALOGO'), 0, 1, 'R');

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

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// 3. CONSULTA DE PIEZAS
$piezas = $pdo->query("SELECT p.*, m.nombre as marca_nombre FROM piezas p JOIN marcas m ON p.marca_id = m.id WHERE p.activo = 1 ORDER BY p.creado_en DESC")->fetchAll();

// 4. GENERAR TARJETAS CON GALERÍA
foreach($piezas as $p) {
    if ($pdf->GetY() > 220) { $pdf->AddPage(); } // Salto de página si no cabe
    
    $y_start = $pdf->GetY();

    // Fondo y borde de la tarjeta (Tarjeta Alta de 65mm para que quepan las fotos abajo)
    $pdf->SetFillColor(250, 251, 252);
    $pdf->SetDrawColor(205, 213, 224);
    $pdf->Rect(10, $y_start, 190, 65, 'DF'); 

    // --- ZONA TEXTO (Arriba) ---
    $pdf->SetXY(15, $y_start + 5);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(30, 42, 56);
    $pdf->Cell(130, 8, utf8_decode($p['nombre']), 0, 1);

    $pdf->SetXY(15, $y_start + 14);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(37, 99, 235);
    $pdf->Cell(45, 5, 'SKU: ' . utf8_decode($p['sku']), 0, 0);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(126, 148, 173);
    $pdf->Cell(45, 5, 'Marca: ' . utf8_decode($p['marca_nombre']), 0, 1);

    // Descripción
    $pdf->SetXY(15, $y_start + 22);
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(95, 120, 148);
    $desc = strlen($p['descripcion_tecnica']) > 150 ? substr($p['descripcion_tecnica'], 0, 150) . '...' : $p['descripcion_tecnica'];
    $pdf->MultiCell(130, 4.5, utf8_decode($desc), 0, 'L');

    // Precio y Stock (Esquina superior derecha)
    $pdf->SetXY(155, $y_start + 8);
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetTextColor(30, 42, 56);
    $pdf->Cell(40, 8, '$' . number_format($p['precio_unitario'], 2), 0, 1, 'R');

    $pdf->SetXY(155, $y_start + 18);
    $pdf->SetFont('Arial', 'B', 11);
    if($p['stock'] > 10) $pdf->SetTextColor(22, 163, 74); 
    elseif($p['stock'] > 0) $pdf->SetTextColor(217, 119, 6); 
    else $pdf->SetTextColor(220, 38, 38); 
    $pdf->Cell(40, 6, 'Stock: ' . $p['stock'] . ' UND', 0, 1, 'R');

    // --- ZONA GALERÍA DE IMÁGENES (Abajo) ---
    // Trazamos una línea divisoria suave
    $pdf->SetDrawColor(220, 225, 230);
    $pdf->Line(15, $y_start + 35, 195, $y_start + 35);

    // Buscamos todas las imágenes de ESTA pieza en la Base de Datos
    $stmtImg = $pdo->prepare("SELECT ruta_imagen FROM piezas_imagenes WHERE pieza_id = ? ORDER BY orden ASC");
    $stmtImg->execute([$p['id']]);
    $imagenes = $stmtImg->fetchAll();

    $img_x = 15; // Coordenada X inicial
    $img_y = $y_start + 38; // Coordenada Y (debajo de la línea)
    $img_size = 22; // Tamaño de cada imagen (22x22 mm)

    if (count($imagenes) > 0) {
        foreach($imagenes as $img) {
            $path = $img['ruta_imagen'];
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            // Si la imagen existe físicamente en el servidor y es JPG/PNG, la dibuja
            if (file_exists($path) && in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $pdf->Image($path, $img_x, $img_y, $img_size, $img_size);
            } else {
                // Caja Gris si el archivo no existe o es .webp
                $pdf->SetFillColor(228, 233, 240);
                $pdf->Rect($img_x, $img_y, $img_size, $img_size, 'F');
                $pdf->SetXY($img_x, $img_y + 9);
                $pdf->SetFont('Arial', 'B', 6);
                $pdf->SetTextColor(168, 183, 202);
                $pdf->Cell($img_size, 4, 'NO DISP.', 0, 0, 'C');
            }
            $img_x += ($img_size + 4); // Espaciamos 4mm entre cada imagen
        }
    } else {
        $pdf->SetXY(15, $img_y + 8);
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->SetTextColor(168, 183, 202);
        $pdf->Cell(100, 5, 'Sin imagenes registradas en la galeria.', 0, 0, 'L');
    }

    $pdf->SetY($y_start + 70); // Espacio antes de la siguiente tarjeta
}

$pdf->Output('I', 'Catalogo.pdf');
?>