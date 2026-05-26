<?php
// generar_pdf_reporte.php
require '../../fpdf/fpdf.php';

$host = '127.0.0.1'; $db = 'bombaparts'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
} catch (\PDOException $e) { die("Error de conexión"); }

// 1. Recibir Filtro del Formulario Modal
$filtro = $_POST['filtro_pdf'] ?? 'ALL';

// 2. Configurar Consulta SQL basada en el Filtro
if($filtro === 'ALL') {
    $sql = "SELECT * FROM historial_actividades ORDER BY fecha_movimiento DESC LIMIT 300";
    $titulo_filtro = "TODOS LOS MÓDULOS";
} else {
    $sql = "SELECT * FROM historial_actividades WHERE modulo = ? ORDER BY fecha_movimiento DESC LIMIT 300";
    $titulo_filtro = "FILTRO: MÓDULO " . strtoupper($filtro);
}

// 3. Crear Clase FPDF Personalizada (Orientación Horizontal 'L')
class PDF extends FPDF {
    function Header() {
        global $titulo_filtro;
        
        // --- DISEÑO DEL ENCABEZADO BASADO EN TU IMAGEN ---
        // 1. Fondo Azul Marino Profundo
        $this->SetFillColor(0, 48, 87); 
        $this->Rect(0, 0, 297, 28, 'F'); // 297 es el ancho A4 Landscape

        // 2. Línea de Acento Roja debajo del encabezado
        $this->SetFillColor(180, 25, 35); 
        $this->Rect(0, 28, 297, 1.5, 'F');

        // 3. Insertar Logo (AJUSTA LA RUTA DE TU IMAGEN AQUÍ)
        // Parámetros: Ruta, X, Y, Ancho. (Asegúrate de que FPDF soporte el formato, .png o .jpg)
        if(file_exists('../../public/assets/img/logo2.png')) {
            $this->Image('../../public/assets/img/logo2.png', 12, 5, 18);
        }

        // 4. Títulos del Reporte (Recorridos a la derecha para no tapar el logo)
        $this->SetY(8);
        $this->SetX(35); // Movemos el cursor a la derecha del logo
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(150, 8, utf8_decode('EQUIPOS DE BOMBEO'), 0, 0, 'L');
        
        // Fecha a la derecha
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(200, 200, 200);
        $this->Cell(92, 8, 'Generado el: ' . date('d/m/Y H:i'), 0, 1, 'R');

        // Subtítulo
        $this->SetX(35);
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(180, 25, 35); // Rojo del acento
        $this->Cell(0, 5, utf8_decode('REPORTE OFICIAL DE AUDITORÍA Y MOVIMIENTOS'), 0, 1, 'L');
        $this->Ln(8);

        // --- ENCABEZADOS DE LA TABLA ---
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(240, 243, 247); // Gris muy claro para encabezados
        $this->SetTextColor(0, 48, 87); // Texto Azul Marino
        $this->SetDrawColor(205, 213, 224); // Bordes grises
        
        $this->Cell(35, 8, 'FECHA', 'B', 0, 'C', true);
        $this->Cell(40, 8, 'USUARIO', 'B', 0, 'L', true);
        $this->Cell(30, 8, utf8_decode('ACCIÓN'), 'B', 0, 'C', true);
        $this->Cell(30, 8, utf8_decode('MÓDULO'), 'B', 0, 'C', true);
        $this->Cell(142, 8, utf8_decode('DESCRIPCIÓN DEL EVENTO'), 'B', 1, 'L', true);
    }

    function Footer() {
        // Posición a 15 mm del final
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(126, 148, 173);
        // Frase de copyright
        $this->Cell(0, 10, utf8_decode('BombaParts © 2026 - Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// 4. Inicializar y Rellenar PDF
$pdf = new PDF('L', 'mm', 'A4'); // L = Landscape
$pdf->AliasNbPages();
$pdf->AddPage();

$stmt = $pdo->prepare($sql);
if($filtro !== 'ALL') $stmt->execute([$filtro]); else $stmt->execute();
$logs = $stmt->fetchAll();

$pdf->SetFont('Arial', '', 8);

$fill = false; // Alternador de color de filas
foreach($logs as $row) {
    // Color de las filas cebra (Blanco puro / Gris extra claro)
    $pdf->SetFillColor(247, 249, 252);
    
    $fecha = date('d/m/Y H:i', strtotime($row['fecha_movimiento']));
    $desc = strlen($row['detalle']) > 95 ? substr($row['detalle'], 0, 95) . '...' : $row['detalle'];
    $accion = strtoupper($row['accion']);

    $pdf->SetTextColor(40, 40, 40); // Texto normal gris oscuro
    $pdf->Cell(35, 7, $fecha, 'B', 0, 'C', $fill);
    $pdf->Cell(40, 7, utf8_decode($row['usuario']), 'B', 0, 'L', $fill);
    
    // Colores dinámicos para las acciones
    if(in_array($accion, ['CREAR', 'LOGIN', 'APROBADA'])) {
        $pdf->SetTextColor(22, 163, 74); // Verde
    } elseif($accion == 'EDITAR') {
        $pdf->SetTextColor(217, 119, 6); // Naranja
    } elseif(in_array($accion, ['ELIMINAR', 'RECHAZADA', 'LOGOUT'])) {
        $pdf->SetTextColor(220, 38, 38); // Rojo
    } else {
        $pdf->SetTextColor(40, 40, 40);
    }
    
    $pdf->Cell(30, 7, utf8_decode($accion), 'B', 0, 'C', $fill);
    
    $pdf->SetTextColor(40, 40, 40); 
    $pdf->Cell(30, 7, utf8_decode($row['modulo']), 'B', 0, 'C', $fill);
    $pdf->Cell(142, 7, utf8_decode($desc), 'B', 1, 'L', $fill);

    $fill = !$fill; // Intercambia el fondo para la siguiente fila
}

// Limpiar buffers de salida antes de generar el PDF
ob_end_clean();
$pdf->Output('I', 'Reporte_Equipos_Bombeo.pdf');
?>