<?php
// generar_pdf_cotizacion.php
require '../../fpdf/fpdf.php';

$host = '127.0.0.1'; $db = 'bombaparts'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
} catch (\PDOException $e) { die("Error de conexión"); }

if(!isset($_GET['codigo'])) die("No se proporcionó código de cotización.");
$codigo = $_GET['codigo'];

// 1. Traer datos de la Cotización UNIDOS con el Cliente
$sql_cabecera = "
    SELECT 
        cot.*, 
        cli.nombre AS cliente_nombre, 
        cli.organizacion, 
        cli.email AS cliente_email 
    FROM cotizaciones cot
    INNER JOIN clientes cli ON cot.cliente_id = cli.id
    WHERE cot.codigo_cotizacion = ?
";
$stmt = $pdo->prepare($sql_cabecera);
$stmt->execute([$codigo]);
$cotizacion = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$cotizacion) die("Cotización no encontrada.");

// 2. Traer los productos de esta cotización
$sql_detalles = "
    SELECT 
        cd.cantidad, 
        cd.precio_unitario, 
        cd.subtotal, 
        p.sku, 
        p.nombre AS nombre_pieza
    FROM cotizacion_detalles cd
    INNER JOIN piezas p ON cd.pieza_id = p.id
    WHERE cd.cotizacion_id = ?
";
$stmt_det = $pdo->prepare($sql_detalles);
$stmt_det->execute([$cotizacion['id']]);
$productos = $stmt_det->fetchAll(PDO::FETCH_ASSOC);

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
        $this->Cell(60, 8, utf8_decode('COTIZACIÓN'), 0, 1, 'R');

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
$pdf->AliasNbPages();
$pdf->AddPage();

// 3. Información del Documento y Cliente
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(30, 42, 56);
$pdf->Cell(100, 6, 'DATOS DEL CLIENTE', 0, 0, 'L');
$pdf->Cell(90, 6, 'DETALLES DEL DOCUMENTO', 0, 1, 'R');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(74, 96, 128);

$cliente = utf8_decode($cotizacion['cliente_nombre']);
$org = utf8_decode($cotizacion['organizacion'] ?: 'N/A');
$email = utf8_decode($cotizacion['cliente_email']);

$fecha = date('d/m/Y', strtotime($cotizacion['fecha_solicitud']));
$vigencia = $cotizacion['vigencia_dias'] . ' dias';
$estado = strtoupper($cotizacion['estado_cotizacion']);

$pdf->Cell(100, 5, "Cliente: $cliente", 0, 0, 'L');
$pdf->Cell(90, 5, "Folio: " . $cotizacion['codigo_cotizacion'], 0, 1, 'R');

$pdf->Cell(100, 5, "Organizacion: $org", 0, 0, 'L');
$pdf->Cell(90, 5, "Fecha Emision: $fecha", 0, 1, 'R');

$pdf->Cell(100, 5, "Correo: $email", 0, 0, 'L');
$pdf->Cell(90, 5, "Vigencia: $vigencia", 0, 1, 'R');

$pdf->Ln(10);

// 4. Tabla de Productos Real
$pdf->SetFillColor(240, 243, 247);
$pdf->SetDrawColor(205, 213, 224);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(30, 42, 56);

// Cabeceras de la tabla
$pdf->Cell(90, 8, 'PRODUCTO / SKU', 'B', 0, 'L', true);
$pdf->Cell(25, 8, 'CANTIDAD', 'B', 0, 'C', true);
$pdf->Cell(35, 8, 'PRECIO UNIT.', 'B', 0, 'R', true);
$pdf->Cell(40, 8, 'SUBTOTAL', 'B', 1, 'R', true);

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(74, 96, 128);

// Iterar sobre cada producto
if (empty($productos)) {
    $pdf->Cell(190, 10, 'No hay productos registrados en esta cotizacion.', 'B', 1, 'C');
} else {
    foreach ($productos as $p) {
        // Formateamos el nombre para que no rompa los acentos
        $nombre_producto = utf8_decode($p['nombre_pieza'] . " (" . $p['sku'] . ")");
        
        $pdf->Cell(90, 8, $nombre_producto, 'B', 0, 'L');
        $pdf->Cell(25, 8, $p['cantidad'], 'B', 0, 'C');
        $pdf->Cell(35, 8, '$' . number_format($p['precio_unitario'], 2), 'B', 0, 'R');
        $pdf->Cell(40, 8, '$' . number_format($p['subtotal'], 2), 'B', 1, 'R');
    }
}

// Fila del TOTAL
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(30, 42, 56);
$pdf->Cell(150, 10, 'TOTAL COTIZACION:', 0, 0, 'R');
$pdf->Cell(40, 10, '$' . number_format($cotizacion['total'], 2), 0, 1, 'R');

$pdf->Ln(15);

// Sello de Aprobado o Rechazado (Si aplica)
if($estado === 'APROBADA') {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(22, 163, 74); // Verde
    $pdf->Cell(0, 10, '--- COTIZACION APROBADA ---', 0, 1, 'C');
} elseif($estado === 'RECHAZADA') {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(220, 38, 38); // Rojo
    $pdf->Cell(0, 10, '--- COTIZACION RECHAZADA ---', 0, 1, 'C');
}

$pdf->Output('I', 'Cotizacion_' . $cotizacion['codigo_cotizacion'] . '.pdf');
?>