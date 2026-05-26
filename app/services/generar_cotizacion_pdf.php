<?php
// Apagamos los errores en pantalla para que el PDF se genere limpio
error_reporting(0);
ini_set('display_errors', 0);

// Tu ruta exacta de XAMPP para la librería
require '../../fpdf/fpdf.php';

if (!isset($_GET['folio']) || empty($_GET['folio'])) {
    die("Error: No se proporcionó un folio de cotización.");
}

$folio = $_GET['folio'];

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'bombaparts';

$conn = new mysqli($host, $user, $password, $database);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Error de conexión a la base de datos.");
}

// 1. Obtener datos de la cotización y del cliente
$stmt = $conn->prepare("
    SELECT c.*, cli.nombre, cli.email, cli.telefono, cli.organizacion, cli.tipo_cliente, cli.ubicacion_ciudad, cli.pais 
    FROM cotizaciones c 
    INNER JOIN clientes cli ON c.cliente_id = cli.id 
    WHERE c.codigo_cotizacion = ?
");
$stmt->bind_param("s", $folio);
$stmt->execute();
$res_cot = $stmt->get_result();

if ($res_cot->num_rows === 0) {
    die("Error: Cotización no encontrada.");
}

$cotizacion = $res_cot->fetch_assoc();
$cotizacion_id = $cotizacion['id'];

// Función moderna para reemplazar a utf8_decode (Evita los errores Deprecated)
function txt($texto) {
    return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
}

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
        $this->Cell(60, 8, utf8_decode('SOLICITUD DE COTIZACIÓN'), 0, 1, 'R');

        $this->SetX(35);
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(255, 255, 255); // Rojo del acento
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
$pdf->SetAutoPageBreak(true, 25);

// ==========================================
// SECCIÓN 1: DATOS DEL DOCUMENTO Y CLIENTE
// ==========================================
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0, 45, 95);
$pdf->Cell(100, 8, txt('Datos del Cliente'), 0, 0, 'L');
$pdf->Cell(90, 8, txt('Detalles del Documento'), 0, 1, 'L');

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(50, 50, 50);

// Guardar la posición Y (Para hacer dos columnas)
$startY = $pdf->GetY();

// Columna Izquierda (Cliente)
$pdf->SetXY(10, $startY);
$pdf->Cell(25, 6, txt('Nombre:'), 0, 0);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(75, 6, txt($cotizacion['nombre']), 0, 1);

$pdf->SetFont('Arial', '', 9);
$pdf->Cell(25, 6, txt('Empresa:'), 0, 0);
$empresa = (!empty($cotizacion['organizacion'])) ? $cotizacion['organizacion'] : $cotizacion['tipo_cliente'];
$pdf->Cell(75, 6, txt($empresa), 0, 1);

$pdf->Cell(25, 6, txt('Correo:'), 0, 0);
$pdf->Cell(75, 6, txt($cotizacion['email']), 0, 1);

$pdf->Cell(25, 6, txt('Teléfono:'), 0, 0);
$pdf->Cell(75, 6, txt($cotizacion['telefono']), 0, 1);

$pdf->Cell(25, 6, txt('Ubicación:'), 0, 0);
$pdf->Cell(75, 6, txt($cotizacion['ubicacion_ciudad'] . ', ' . $cotizacion['pais']), 0, 1);

// Columna Derecha (Documento)
$pdf->SetXY(110, $startY);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(30, 6, txt('Folio:'), 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(0, 123, 255); // Azul brillante
$pdf->Cell(60, 6, txt($folio), 0, 1);

$pdf->SetTextColor(50, 50, 50);
$pdf->SetXY(110, $startY + 6);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(30, 6, txt('Fecha Solicitud:'), 0, 0);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(60, 6, txt($cotizacion['fecha_solicitud']), 0, 1);

$pdf->SetXY(110, $startY + 12);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(30, 6, txt('Estado:'), 0, 0);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(60, 6, txt('En Revisión por Asesor'), 0, 1);

$pdf->Ln(15);

// ==========================================
// SECCIÓN 2: TABLA DE REFACCIONES
// ==========================================
// Cabecera de la tabla
$pdf->SetFillColor(0, 45, 95);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(15, 8, txt('CANT.'), 1, 0, 'C', true);
$pdf->Cell(45, 8, txt('SKU / MARCA'), 1, 0, 'C', true);
$pdf->Cell(95, 8, txt('DESCRIPCIÓN DE LA PIEZA'), 1, 0, 'C', true);
$pdf->Cell(35, 8, txt('PRECIO UNITARIO'), 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(50, 50, 50);

// Obtener detalles de la BD
$stmt_det = $conn->prepare("
    SELECT cd.cantidad, p.sku, p.nombre AS pieza_nombre, m.nombre AS marca_nombre
    FROM cotizacion_detalles cd
    INNER JOIN piezas p ON cd.pieza_id = p.id
    LEFT JOIN marcas m ON p.marca_id = m.id
    WHERE cd.cotizacion_id = ?
");
$stmt_det->bind_param("i", $cotizacion_id);
$stmt_det->execute();
$res_det = $stmt_det->get_result();

$fill = false; // Alternar color de fila
$pdf->SetFillColor(245, 248, 250); // Gris muy claro

while ($item = $res_det->fetch_assoc()) {
    $marca = !empty($item['marca_nombre']) ? $item['marca_nombre'] : 'Genérica';
    $sku_marca = $item['sku'] . "\n" . $marca;
    
    // Altura calculada para celdas multilinea
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    
    // Verificamos si necesitamos salto de página
    if ($y > 250) {
        $pdf->AddPage();
        $y = $pdf->GetY();
    }

    $pdf->Cell(15, 10, $item['cantidad'], 'B', 0, 'C', $fill);
    
    $pdf->SetXY($x + 15, $y);
    $pdf->MultiCell(45, 5, txt($sku_marca), 'B', 'C', $fill);
    
    $pdf->SetXY($x + 60, $y);
    $pdf->MultiCell(95, 10, txt($item['pieza_nombre']), 'B', 'L', $fill);
    
    $pdf->SetXY($x + 155, $y);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(35, 10, txt('Sujeto a cotización'), 'B', 1, 'C', $fill);
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(50, 50, 50);
    $fill = !$fill; // Alternar color
}

$pdf->Ln(10);

// ==========================================
// SECCIÓN 3: MENSAJE DEL CLIENTE / NOTAS
// ==========================================
if (!empty($cotizacion['notas_web'])) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(0, 45, 95);
    $pdf->Cell(0, 8, txt('Notas Adicionales de la Solicitud:'), 0, 1, 'L');
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(80, 80, 80);
    $pdf->SetFillColor(245, 245, 245);
    // Limpiamos el texto
    $notas_limpias = strip_tags($cotizacion['notas_web']);
    $pdf->MultiCell(0, 6, txt($notas_limpias), 1, 'L', true);
}

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(0, 45, 95);
$pdf->Cell(0, 6, txt('Próximos Pasos:'), 0, 1, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(80, 80, 80);
$pdf->MultiCell(0, 5, txt("Un asesor de Equipos de Bombeo revisará los requerimientos y disponibilidad de stock de las refacciones enlistadas. Te enviaremos la cotización formal con precios y tiempos de entrega a la brevedad posible a través del método de contacto seleccionado."));

$conn->close();

// Salida del PDF al navegador
$pdf->Output('I', 'Solicitud_Cotizacion_' . $folio . '.pdf');
?>