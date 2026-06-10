<?php
session_start();

// 1. Configuración de la conexión a la base de datos
$host = 'localhost';
$dbname = 'bombaparts';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$codigo = $_GET['codigo'] ?? '';
$mensaje_accion = '';

// 2. Procesar la acción del cliente (Aceptar / Rechazar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['cotizacion_id'])) {
    $id = $_POST['cotizacion_id'];
    
    // Si el cliente acepta, el estado cambia a 'confirmada'
    $nuevo_estado = ($_POST['accion'] === 'aceptar') ? 'confirmada' : 'no_aprobada';

    // Obtenemos la fecha actual para la columna nueva
    date_default_timezone_set('America/Mexico_City');
    $fecha_actual = date('Y-m-d H:i:s');

    // Actualizamos el estado y la fecha_respuesta en la base de datos
    $stmt_update = $pdo->prepare("UPDATE cotizaciones SET estado_cotizacion = ?, fecha_respuesta = ? WHERE id = ?");
    if ($stmt_update->execute([$nuevo_estado, $fecha_actual, $id])) {
        $mensaje_accion = "La cotización ha sido <strong>{$nuevo_estado}</strong> exitosamente. Nuestro equipo le dará seguimiento.";
    }
}

// 3. Consultar la información de la cotización
$esta_caducada = false;

if ($codigo) {
    $sql_cotizacion = "
        SELECT cot.*, cli.nombre AS cliente_nombre, cli.organizacion 
        FROM cotizaciones cot
        INNER JOIN clientes cli ON cot.cliente_id = cli.id
        WHERE cot.codigo_cotizacion = ?
    ";
    $stmt = $pdo->prepare($sql_cotizacion);
    $stmt->execute([$codigo]);
    $cotizacion = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si existe la cotización, consultamos los detalles y calculamos caducidad
    if ($cotizacion) {
        $sql_detalles = "
            SELECT cd.*, p.nombre AS nombre_pieza, p.sku 
            FROM cotizacion_detalles cd
            INNER JOIN piezas p ON cd.pieza_id = p.id
            WHERE cd.cotizacion_id = ?
        ";
        $stmt_det = $pdo->prepare($sql_detalles);
        $stmt_det->execute([$cotizacion['id']]);
        $detalles = $stmt_det->fetchAll(PDO::FETCH_ASSOC);

        // --- LÓGICA DE CADUCIDAD ---
        $fecha_solicitud_dt = new DateTime($cotizacion['fecha_solicitud']);
        $fecha_vencimiento_dt = clone $fecha_solicitud_dt;
        $fecha_vencimiento_dt->modify("+" . (int)$cotizacion['vigencia_dias'] . " days");
        
        $hoy = new DateTime();
        
        // Caduca solo si sigue esperando al cliente (aprobada) y la fecha ya pasó
        if ($cotizacion['estado_cotizacion'] === 'aprobada' && $hoy > $fecha_vencimiento_dt) {
            $esta_caducada = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización <?= htmlspecialchars($codigo) ?> - ESBR</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;500;600;700;800&family=Barlow:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { blue: '#002855', red: '#cc0000', light: '#f4f6f9' }
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --blue-deep: #002855;
            --red-accent: #cc0000;
            --white: #ffffff;
            --font-display: 'Barlow Condensed', sans-serif;
            --font-body: 'Barlow', sans-serif;
            --radius-sm: 4px;
            --transition: 0.3s ease;
        }

        body { font-family: var(--font-body); background-color: #f4f6f9; }
        h1, h2, h3 { font-family: var(--font-display); }
        .table-font { font-family: 'IBM Plex Mono', monospace; }

        /* HEADER CSS */
        #main-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--blue-deep);
            box-shadow: 0 2px 16px rgba(0,0,0,0.25);
            border-bottom: 4px solid var(--red-accent); 
        }
        #main-header .header-inner {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .brand-container {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            min-width: 0;
            max-width: calc(100% - 60px);
        }
        .brand-text {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0;
        }
        .brand-title, .brand-subtitle {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        #main-header .brand {
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 1.8rem;
            color: var(--white);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            line-height: 1.1;
        }
        .brand-subtitle {
            font-family: var(--font-body);
            font-size: 0.85rem;
            color: rgba(255,255,255,0.82);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 600;
        }
        /* AJUSTES RESPONSIVOS */
        @media (max-width: 768px) {
            #main-header .brand { font-size: 1.25rem; line-height: 1.1; }
            .brand-subtitle { font-size: 0.7rem; margin-top: 2px; }
            .brand-title, .brand-subtitle {
                white-space: normal;
                text-overflow: clip;
            }
            .brand-container { gap: 8px; max-width: 100%; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="text-gray-800 antialiased">

    <header id="main-header">
        <div class="header-inner px-4 md:pl-12 md:pr-4 py-4 md:py-5 container mx-auto">
            <div class="brand-container">
                <img src="uploads/logo2.png" alt="Logo ESBR" class="h-10 md:h-14 w-auto object-contain">
                <div class="brand-text">
                    <div class="brand-title brand">Equipos de Bombeo</div>
                    <div class="brand-subtitle">Servicio & Refacciones</div>
                </div>
            </div>
            <?php if (!empty($cotizacion)): ?>
                <div class="flex-shrink-0 ml-4">
                    <a href="/Proyecto/app/services/generar_pdf_cotizacion.php?codigo=<?= urlencode($codigo) ?>" 
                       target="_blank" 
                       class="bg-brand-red text-white hover:bg-red-700 font-bold px-3 py-2 md:px-5 md:py-2.5 rounded-lg text-xs md:text-sm uppercase tracking-wider flex items-center gap-2 transition shadow-md hover:shadow-lg"
                       title="Descargar PDF Oficial">
                        
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"></path>
                        </svg>
                        
                        <span class="hidden sm:inline">Descargar PDF</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="container mx-auto px-4 py-10 max-w-5xl">
        <?php if (empty($cotizacion)): ?>
            <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-200">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <h2 class="text-3xl font-bold text-gray-700">Cotización no encontrada</h2>
                <p class="mt-2 text-gray-500">El código proporcionado no es válido o la cotización ya no existe.</p>
            </div>
        <?php else: ?>

            <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
                
                <div class="p-8 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center bg-gray-50">
                    <div>
                        <h2 class="text-4xl font-bold text-brand-blue uppercase">Detalle de Cotización</h2>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="bg-brand-blue text-white px-3 py-1 rounded text-sm font-semibold tracking-wider table-font">
                                <?= htmlspecialchars($cotizacion['codigo_cotizacion']) ?>
                            </span>
                            <?php 
                                $color_estado = match($cotizacion['estado_cotizacion']) {
                                    'confirmada' => 'bg-green-100 text-green-800 border-green-200',
                                    'aprobada' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'rechazada' => 'bg-red-100 text-red-800 border-red-200',
                                    default => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                };
                            ?>
                            <span class="px-3 py-1 rounded text-sm font-semibold uppercase border <?= $color_estado ?>">
                                <?= htmlspecialchars($cotizacion['estado_cotizacion']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-6 md:mt-0 text-left md:text-right text-sm text-gray-600 space-y-1">
                        <p><span class="font-semibold text-gray-800">Fecha de emisión:</span> <span class="table-font"><?= htmlspecialchars($cotizacion['fecha_solicitud']) ?></span></p>
                        <p><span class="font-semibold text-gray-800">Vigencia:</span> <span class="table-font text-brand-red font-semibold"><?= (int)$cotizacion['vigencia_dias'] ?> días</span></p>
                    </div>
                </div>

                <div class="p-8 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-3">Preparado para:</h3>
                    <p class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($cotizacion['cliente_nombre']) ?></p>
                    <?php if(!empty($cotizacion['organizacion'])): ?>
                        <p class="text-gray-600"><?= htmlspecialchars($cotizacion['organizacion']) ?></p>
                    <?php endif; ?>
                </div>

                <?php if ($mensaje_accion): ?>
                    <div class="mx-8 mt-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-800 rounded flex items-center gap-3">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <p><?= $mensaje_accion ?></p>
                    </div>
                <?php endif; ?>

                <div class="p-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Productos Solicitados</h3>
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                                    <th class="p-4 font-semibold">SKU / Producto</th>
                                    <th class="p-4 font-semibold text-center">Cant.</th>
                                    <th class="p-4 font-semibold text-right">Precio Unit.</th>
                                    <th class="p-4 font-semibold text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if(!empty($detalles)): foreach ($detalles as $item): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="p-4">
                                            <span class="block text-xs font-semibold text-brand-blue table-font mb-1"><?= htmlspecialchars($item['sku']) ?></span>
                                            <span class="text-gray-800"><?= htmlspecialchars($item['nombre_pieza']) ?></span>
                                        </td>
                                        <td class="p-4 text-center table-font text-gray-700"><?= (int)$item['cantidad'] ?></td>
                                        <td class="p-4 text-right table-font text-gray-700">$<?= number_format($item['precio_unitario'], 2) ?></td>
                                        <td class="p-4 text-right table-font font-semibold text-gray-900">$<?= number_format($item['subtotal'], 2) ?></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="p-4 text-right font-bold text-gray-700 uppercase tracking-wider">Total Cotización:</td>
                                    <td class="p-4 text-right font-bold text-2xl text-brand-blue table-font">$<?= number_format($cotizacion['total'], 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <?php if ($cotizacion['estado_cotizacion'] === 'aprobada'): ?>
                    
                    <?php if ($esta_caducada): ?>
                        <div class="p-8 bg-red-50 border-t-4 border-brand-red text-center rounded-b-xl">
                            <svg class="w-12 h-12 text-brand-red mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <h4 class="text-2xl font-bold text-brand-red uppercase tracking-wide mb-2">Cotización Caducada</h4>
                            <p class="text-gray-700">El tiempo de vigencia de <strong><?= (int)$cotizacion['vigencia_dias'] ?> días</strong> ha expirado. Por favor, contacta a tu asesor para solicitar una actualización.</p>
                        </div>
                    <?php else: ?>
                        <div class="p-8 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row justify-end gap-4 items-center rounded-b-xl">
                            <p class="text-sm text-gray-500 mr-auto hidden sm:block">Por favor, revisa los detalles antes de tomar una decisión.</p>
                            
                            <form id="form-accion-cotizacion" method="POST" action="" class="w-full sm:w-auto flex gap-4">
                                <input type="hidden" name="cotizacion_id" value="<?= $cotizacion['id'] ?>">
                                <input type="hidden" id="input-accion" name="accion" value="">
                                
                                <button type="button" onclick="confirmarAccion('rechazar')" class="w-full sm:w-auto px-6 py-3 border-2 border-brand-red text-brand-red font-bold uppercase tracking-wider rounded-lg hover:bg-red-50 transition flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Rechazar
                                </button>
                                
                                <button type="button" onclick="confirmarAccion('aceptar')" class="w-full sm:w-auto px-8 py-3 bg-brand-blue text-white font-bold uppercase tracking-wider rounded-lg hover:bg-blue-900 transition shadow-lg flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Aprobar Cotización
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                <?php elseif ($cotizacion['estado_cotizacion'] === 'confirmada'): ?>
                    <div class="p-8 bg-green-50 border-t-4 border-green-500 text-center rounded-b-xl">
                        <svg class="w-12 h-12 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h4 class="text-2xl font-bold text-green-700 uppercase tracking-wide mb-2">Cotización Aceptada</h4>
                        <p class="text-green-800">Has aprobado esta cotización exitosamente. Nuestro equipo se pondrá en contacto contigo en breve para continuar con el proceso.</p>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>
    </main>
<script>
        function confirmarAccion(tipo) {
            let titulo, texto, icono, colorBoton, textoBoton;

            // Configuramos los colores y textos según el botón que presionó
            if (tipo === 'aceptar') {
                titulo = '¿Aprobar Cotización?';
                texto = 'Al confirmar, nuestro equipo comenzará a procesar tu solicitud.';
                icono = 'success'; // Dibuja la palomita verde
                colorBoton = '#002855'; // Tu azul corporativo
                textoBoton = 'Sí, aprobar';
            } else {
                titulo = '¿Rechazar Cotización?';
                texto = 'Esta acción no se puede deshacer. ¿Estás seguro?';
                icono = 'warning'; // Dibuja el signo de advertencia
                colorBoton = '#cc0000'; // Tu rojo corporativo
                textoBoton = 'Sí, rechazar';
            }

            // Lanzamos la alerta bonita con SweetAlert2
            Swal.fire({
                title: titulo,
                text: texto,
                icon: icono,
                showCancelButton: true,
                confirmButtonColor: colorBoton,
                cancelButtonColor: '#6b7280', // Color gris para el botón cancelar
                confirmButtonText: textoBoton,
                cancelButtonText: 'Cancelar',
                // Esta línea usa la fuente de tu proyecto en la alerta
                customClass: {
                    title: 'font-display tracking-wide', 
                }
            }).then((result) => {
                // Si el usuario da clic en el botón de confirmación
                if (result.isConfirmed) {
                    // Asignamos el valor al input oculto
                    document.getElementById('input-accion').value = tipo;
                    // Enviamos el formulario
                    document.getElementById('form-accion-cotizacion').submit();
                }
            });
        }
    </script>
</body>
</html>