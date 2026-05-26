<?php
// 1. Configuración de la base de datos
$host = 'localhost';
$dbname = 'bombaparts';
$user = 'root';
$pass = '';

// 2. Crear la conexión ($pdo)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// ── 1. Tab activo ────────────────────────────────────────────
$estados_validos = ['pendiente', 'aprobada', 'rechazada'];
$tab_activo      = $_GET['estado'] ?? 'pendiente';
if (!in_array($tab_activo, $estados_validos, true)) {
    $tab_activo = 'pendiente';
}

// ── 2. Conteos por estado (para badges en las pestañas) ──────
$conteos_raw = []; 

try {
    $sql_conteos = "SELECT estado_cotizacion, COUNT(*) AS total 
                    FROM cotizaciones 
                    GROUP BY estado_cotizacion";
    $stmt = $pdo->query($sql_conteos);
    
    if ($stmt) {
        $resultados = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        if (is_array($resultados)) {
            $conteos_raw = $resultados;
        }
    }
} catch (PDOException $e) {
    $conteos_raw = [];
}

$conteos = array_merge(
    ['pendiente' => 0, 'aprobada' => 0, 'rechazada' => 0],
    $conteos_raw
);
$total_piezas = array_sum($conteos);

// ── 3. Paginación ────────────────────────────────────────────
$por_pagina    = 10;
$pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
$offset        = ($pagina_actual - 1) * $por_pagina;

// ── 4. Consulta principal ────────────────────────────────────
try {
    $sql_total = "SELECT COUNT(*) FROM cotizaciones WHERE estado_cotizacion = :estado";
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->execute([':estado' => $tab_activo]);
    $total_filas   = (int)$stmt_total->fetchColumn();
    $total_paginas = (int)ceil($total_filas / $por_pagina);

      // 4. Consulta principal ────────────────────────────────────
    $sql_lista = "
        SELECT 
            cot.id,
            cot.codigo_cotizacion,
            cot.fecha_solicitud,
            cot.estado_cotizacion,
            cot.vigencia_dias,
            cot.total,
            cot.notas_web, /* <-- AHORA TRAEMOS LAS NOTAS */
            cli.nombre AS cliente_nombre,
            cli.organizacion,
            cli.email AS cliente_email,
            cli.tipo_cliente
        FROM cotizaciones cot
        INNER JOIN clientes cli ON cot.cliente_id = cli.id
        WHERE cot.estado_cotizacion = :estado
        ORDER BY cot.fecha_solicitud DESC
        LIMIT :limite OFFSET :offset
    ";

    $stmt = $pdo->prepare($sql_lista);
    $stmt->bindValue(':estado', $tab_activo,  PDO::PARAM_STR);
    $stmt->bindValue(':limite', $por_pagina,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,      PDO::PARAM_INT);
    $stmt->execute();
    $cotizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    

if (isset($_POST['action']) && $_POST['action'] === 'ver_detalles' && isset($_POST['id'])) {
    
    // Aquí sí capturamos el ID que nos manda el botón
    $cotizacion_id = $_POST['id']; 

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
    
    $stmt_detalles = $pdo->prepare($sql_detalles);
    $stmt_detalles->execute([$cotizacion_id]);
    $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

    // Limpiamos la salida y devolvemos los datos en formato JSON para que tu modal los lea
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'data' => $detalles]);
    exit;
}


    $stmt = $pdo->prepare($sql_lista);
    $stmt->bindValue(':estado', $tab_activo,  PDO::PARAM_STR);
    $stmt->bindValue(':limite', $por_pagina,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,      PDO::PARAM_INT);
    $stmt->execute();
    $cotizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $cotizaciones = [];
    $total_filas = 0;
    $total_paginas = 1;
}

// ── 5. Helper: clase CSS según estado ───────────────────────
function clase_estado(string $estado): string {
    return match($estado) {
        'aprobada'  => 'badge-approved',
        'rechazada' => 'badge-rejected',
        default     => 'badge-pending',
    };
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cotizaciones - Equipos de Bombeo</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;500;600;700&family=Barlow:wght@400;500&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        display: ['Barlow Condensed', 'sans-serif'],
        body: ['Barlow', 'sans-serif'],
        mono: ['IBM Plex Mono', 'monospace'],
      },
      colors: {
        steel: { 50: '#f4f6f9', 100: '#e8ecf2', 200: '#cdd5e0', 300: '#a8b7ca', 400: '#7e94ad', 500: '#5f7894', 600: '#4a6080', 700: '#3c4f69', 800: '#344358', 900: '#1e2a38', 950: '#111824' },
        ember: { 400: '#f97316', 500: '#ea6a0a', 600: '#c2540a' },
        cobalt: { 400: '#3b82f6', 500: '#2563eb', 600: '#1d4ed8' }
      }
    }
  }
}
</script>
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="main">

  <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
    <div>
      <h1 class="page-title">Cotizaciones</h1>
      <p class="page-sub">// equipos de bombeo · servicio · refacciones</p>
    </div>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-label">Total Generales</div>
      <div class="stat-value val-white"><?= $total_piezas ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Pendientes</div>
      <div class="stat-value val-warning"><?= $conteos['pendiente'] ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Aprobadas</div>
      <div class="stat-value val-success"><?= $conteos['aprobada'] ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Rechazadas</div>
      <div class="stat-value val-danger"><?= $conteos['rechazada'] ?></div>
    </div>
  </div>

  <div class="filter-bar" style="margin-bottom: 24px;">
    <div style="display: flex; gap: 8px;">
      <a href="?estado=pendiente" class="nav-item <?= $tab_activo === 'pendiente' ? 'active' : '' ?>" style="text-decoration: none;">Pendientes (<?= $conteos['pendiente'] ?>)</a>
      <a href="?estado=aprobada" class="nav-item <?= $tab_activo === 'aprobada' ? 'active' : '' ?>" style="text-decoration: none;">Aprobadas (<?= $conteos['aprobada'] ?>)</a>
      <a href="?estado=rechazada" class="nav-item <?= $tab_activo === 'rechazada' ? 'active' : '' ?>" style="text-decoration: none;">Rechazadas (<?= $conteos['rechazada'] ?>)</a>
    </div>
    
    <input type="search" id="buscador-cotizaciones" class="filter-input" placeholder="Buscar por ID, cliente u organización…" style="margin-left: auto;">
  </div>

  <?php if (empty($cotizaciones)): ?>
    <div style="padding: 40px; text-align: center; color: var(--text-muted); background: var(--bg-surface); border: 1px solid var(--border); border-radius: 10px;">
      No hay cotizaciones <?= htmlspecialchars($tab_activo) ?>s.
    </div>
    <?php else: ?>

    <!-- ========================================= -->
    <!-- TABLA PARA COMPUTADORA (DESKTOP)          -->
    <!-- ========================================= -->
    <div class="table-wrap desktop-only">
      <table>
        <thead>
          <tr>
            <th>ID Cotización</th>
            <th>Cliente</th>
            <th>Organización</th>
            <th>Fecha</th>
            <th>Vigencia</th>
            <th>Total</th>
            <th>Estado</th>
            <th style="text-align: center;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cotizaciones as $c): ?>
          <tr>
            <td class="sku-cell"><?= htmlspecialchars($c['codigo_cotizacion']) ?></td>
            <td style="font-weight: 500;"><?= htmlspecialchars($c['cliente_nombre']) ?></td>
            <td style="color: var(--text-muted);"><?= htmlspecialchars($c['organizacion'] ?? '—') ?></td>
            <td style="font-family: 'IBM Plex Mono', monospace; font-size: 13px; color: var(--text-secondary);"><?= htmlspecialchars($c['fecha_solicitud']) ?></td>
            <td style="font-family: 'IBM Plex Mono', monospace; font-size: 13px;"><?= (int)$c['vigencia_dias'] ?> días</td>
            <td style="font-family: 'IBM Plex Mono', monospace; font-size: 13px; font-weight: 500;">$<?= number_format((float)$c['total'], 2) ?></td>
            <td>
              <span class="badge <?= clase_estado($c['estado_cotizacion']) ?>"><?= ucfirst($c['estado_cotizacion']) ?></span>
            </td>
            <td style="text-align: center;">
              <!-- BOTÓN DESKTOP CON TODOS LOS DATOS -->
                  <button class="act-btn edit btn-ver-detalle" 
    data-id="<?= htmlspecialchars($c['id'] ?? '') ?>"
    data-codigo="<?= htmlspecialchars($c['codigo_cotizacion'] ?? '') ?>"
    data-fecha="<?= htmlspecialchars($c['fecha_solicitud'] ?? '') ?>"
    data-vigencia="<?= htmlspecialchars($c['vigencia_dias'] ?? '30') ?>"
    data-cliente="<?= htmlspecialchars($c['cliente_nombre'] ?? '') ?>"
    data-org="<?= htmlspecialchars($c['organizacion'] ?? '—') ?>"
    data-email="<?= htmlspecialchars($c['cliente_email'] ?? '') ?>"
    data-total="<?= htmlspecialchars($c['total'] ?? '0') ?>"
    data-estado="<?= htmlspecialchars($c['estado_cotizacion'] ?? 'pendiente') ?>"
    data-raw="<?= htmlspecialchars($c['notas_web'] ?? '') ?>"  title="Ver Detalles">
                 <svg style="width:16px; height:16px; pointer-events: none;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Paginación Desktop -->
      <?php if ($total_paginas > 1): ?>
      <div class="pagination">
        <div class="pag-info">Mostrando <?= $offset + 1 ?>–<?= min($offset + $por_pagina, $total_filas) ?> de <?= $total_filas ?> resultados</div>
        <div style="display: flex; gap: 6px;">
          <?php if ($pagina_actual > 1): ?>
            <a href="?estado=<?= $tab_activo ?>&pagina=<?= $pagina_actual - 1 ?>" class="pag-btn" style="text-decoration:none;">‹</a>
          <?php endif; ?>
          
          <?php for ($p = 1; $p <= $total_paginas; $p++): ?>
            <a href="?estado=<?= $tab_activo ?>&pagina=<?= $p ?>" class="pag-btn <?= ($p === $pagina_actual) ? 'active' : '' ?>" style="text-decoration:none;"><?= $p ?></a>
          <?php endfor; ?>
          
          <?php if ($pagina_actual < $total_paginas): ?>
            <a href="?estado=<?= $tab_activo ?>&pagina=<?= $pagina_actual + 1 ?>" class="pag-btn" style="text-decoration:none;">›</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- ========================================= -->
    <!-- TARJETAS PARA CELULARES (MOBILE)          -->
    <!-- ========================================= -->
    <div class="mobile-only">
      <div class="mobile-cards-list">
        <?php foreach ($cotizaciones as $c): ?>
        <div class="mobile-card">
          <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <span class="sku-cell"><?= htmlspecialchars($c['codigo_cotizacion']) ?></span>
            <span class="badge <?= clase_estado($c['estado_cotizacion']) ?>"><?= ucfirst($c['estado_cotizacion']) ?></span>
          </div>
          <div style="font-weight: 600; margin-bottom: 2px;"><?= htmlspecialchars($c['cliente_nombre']) ?></div>
          <div style="color: var(--text-muted); font-size: 13px; margin-bottom: 12px;"><?= htmlspecialchars($c['organizacion'] ?? '—') ?></div>
          <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 12px;">
            <span style="font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: var(--text-secondary);"><?= htmlspecialchars($c['fecha_solicitud']) ?></span>
            
            <!-- BOTÓN MOBILE CON TODOS LOS DATOS -->
            <button class="act-btn edit btn-ver-detalle" 
    data-id="<?= htmlspecialchars($c['id'] ?? '') ?>"
    data-codigo="<?= htmlspecialchars($c['codigo_cotizacion'] ?? '') ?>"
    data-fecha="<?= htmlspecialchars($c['fecha_solicitud'] ?? '') ?>"
    data-vigencia="<?= htmlspecialchars($c['vigencia_dias'] ?? '30') ?>"
    data-cliente="<?= htmlspecialchars($c['cliente_nombre'] ?? '') ?>"
    data-org="<?= htmlspecialchars($c['organizacion'] ?? '—') ?>"
    data-email="<?= htmlspecialchars($c['cliente_email'] ?? '') ?>"
    data-total="<?= htmlspecialchars($c['total'] ?? '0') ?>"
    data-estado="<?= htmlspecialchars($c['estado_cotizacion'] ?? 'pendiente') ?>"
    data-raw="<?= htmlspecialchars($c['notas_web'] ?? '') ?>"  title="Ver Detalles">
              <svg style="width:16px; height:16px; pointer-events: none;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</main>
<div id="modal-cotizacion" class="modal-overlay">
  <div class="modal-box" style="max-width: 850px;">
    
    <div class="modal-header">
      <div class="modal-title">DETALLE DE COTIZACIÓN</div>
      <button id="modal-cerrar" class="modal-close">✕</button>
    </div>

    <div id="modal-loading" class="hidden" style="padding: 40px; text-align: center; color: var(--text-muted); font-family: 'IBM Plex Mono', monospace;">
      Cargando información...
    </div>
    <div id="modal-error" class="hidden" style="padding: 40px; text-align: center; color: var(--danger);"></div>

    <div class="modal-body hidden" id="cotizacion-detalles" style="grid-template-columns: 1fr;">
      <div class="modal-left" style="border: none; padding: 24px;">
        
        <div class="field-group">
          <div class="field">
            <label>ID Cotización</label>
            <div id="d-codigo" class="sku-cell" style="font-size: 15px;">-</div>
          </div>
          <div class="field">
            <label>Estado</label>
            <div><span id="d-estado" class="badge"></span></div>
          </div>
          <div class="field">
            <label>Fecha Solicitud</label>
            <div id="d-fecha" style="font-family: 'IBM Plex Mono', monospace; font-size: 14px; color: var(--text-primary);">-</div>
          </div>
          <div class="field">
            <label>Vigencia (Días)</label>
            <div style="display:flex; align-items:center; gap:6px; font-family: 'IBM Plex Mono', monospace; font-size: 14px; color: var(--text-primary);">
                <input type="number" id="input-vigencia" class="editable-input" style="width: 70px;" value="30">
                <span style="color: var(--text-secondary);">días</span>
                <!-- NUEVO BOTÓN DE CONFIRMAR VIGENCIA -->
                <button type="button" id="btn-confirm-vigencia" class="btn-outline" style="padding: 4px 8px; font-size: 11px;" onclick="actualizarVigencia()">Confirmar</button>
            </div>
          </div>
        </div>

        <div class="section-divider" style="margin-top: 24px;">Datos del Cliente</div>
        
        <div class="field-group">
          <div class="field field-full">
            <label>Cliente</label>
            <div id="d-cliente" style="font-size: 15px; font-weight: 500; color: var(--text-primary);">-</div>
          </div>
          <div class="field">
            <label>Organización</label>
            <div id="d-organizacion" style="font-size: 14px; color: var(--text-secondary);">-</div>
          </div>
          <div class="field">
            <label>Email</label>
            <div id="d-email" style="font-size: 14px; color: var(--text-secondary);">-</div>
          </div>
          <!-- NUEVO: Preferencia de contacto -->
          <div class="field">
            <label>Preferencia Contacto</label>
            <div id="d-pref-contacto" style="font-size: 14px; color: var(--accent); font-weight: 500;">-</div>
          </div>
        </div>

        <div class="field field-full" style="grid-column: 1 / -1; margin-top: 10px;">
            <label>Notas del Cliente / Mensaje</label>
            <div id="d-notas" style="font-size: 14px; color: var(--text-secondary); background: var(--bg-surface2); padding: 12px; border-radius: 6px; white-space: pre-wrap; border: 1px solid var(--border); min-height: 40px;">-</div>
          </div>

        <div class="section-divider" style="margin-top: 24px;">Productos Solicitados</div>
        
        <div class="table-wrap" style="margin-top: 12px; border-color: var(--border-strong); box-shadow: none;">
          <table style="table-layout: auto;">
            <thead style="background: var(--bg-surface2);">
              <tr>
                <th style="width: 45%;">PRODUCTO (SKU)</th>
                <th style="text-align: center; width: 10%;">CANTIDAD</th>
                <th style="text-align: right; width: 20%;">PRECIO UNIT.</th>
                <th style="text-align: right; width: 25%;">SUBTOTAL</th>
              </tr>
            </thead>
            <!-- El JS llenará esto. Debe usar class="input-precio editable-input" para editar -->
            <tbody id="tabla-productos-body"></tbody>
          </table>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border);">
          <div style="font-family: 'Barlow Condensed', sans-serif; font-size: 22px; font-weight: 700; color: var(--text-primary);">
            Total Cotización: <span id="d-total" style="color: var(--text-primary); margin-left: 6px;">$0.00</span>
          </div>
        </div>

      </div>
    </div>

    <!-- FOOTER MODIFICADO PARA EL FLUJO DE ACCIONES -->
    <div class="modal-footer hidden" id="detalles-acciones" style="flex-direction: column;">
      
      <!-- Fila 1: Botones iniciales (Aprobar/Rechazar) -->
      <div id="acciones-iniciales" style="display: flex; justify-content: flex-end; gap: 10px; width: 100%;">
          <button type="button" id="btn-rechazar" class="btn-cancel" style="border-color: var(--danger); color: var(--danger);" onclick="procesarCotizacion('rechazada')">Rechazar</button>
          <button type="button" id="btn-aprobar" class="btn-primary" style="background: var(--success); box-shadow: none;" onclick="procesarCotizacion('aprobada')">Aprobar Cotización</button>
      </div>

      <!-- Fila 2: Botones Post-Confirmación (Ocultos inicialmente) -->
      <div id="acciones-post" style="display: none; justify-content: flex-end; gap: 10px; width: 100%;">
          <button class="btn-outline" id="btn-pdf">
            <svg style="width:14px; height:14px; display:inline-block; vertical-align:middle; margin-right:4px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
            Generar PDF
          </button>
          <button class="btn-primary" id="btn-enviar" onclick="toggleCanalesContacto()" style="background: var(--accent);">
            <svg style="width:14px; height:14px; display:inline-block; vertical-align:middle; margin-right:4px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            Enviar a Cliente
          </button>
          <!-- MODAL ENVIAR A CLIENTE -->
            <div id="modal-enviar" class="modal-overlay hidden" style="z-index: 1000; align-items: center; justify-content: center;">
              
              <!-- Contenedor Aislado para evitar conflictos CSS -->
              <div style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 14px; width: 100%; max-width: 420px; padding: 24px; display: flex; flex-direction: column; box-shadow: var(--shadow-lg); position: relative;">
                
                <!-- Botón Cerrar -->
                <button onclick="cerrarModalEnviar()" style="position: absolute; top: 16px; right: 16px; background:transparent; border:none; color:var(--text-muted); font-size: 20px; cursor: pointer; line-height: 1;">✕</button>
                
                <!-- Cabecera e Icono -->
                <div style="text-align: center; margin-bottom: 24px; margin-top: 10px;">
                  <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.5" style="margin: 0 auto 12px;">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                  </svg>
                  <h3 style="color: var(--text-primary); font-family: 'Barlow Condensed', sans-serif; font-size: 26px; margin: 0 0 4px 0; line-height: 1;">Contactar Cliente</h3>
                  <p style="color: var(--text-muted); font-size: 14px; margin: 0 0 12px 0;">Elige el canal para enviar la cotización a <strong id="envio-nombre-cliente"></strong></p>
                  
                  <!-- Insignia de Preferencia -->
                  <div id="envio-preferencia-texto" style="display:inline-block; background: var(--bg-surface2); border: 1px solid var(--border); padding: 6px 12px; border-radius: 6px; font-size: 13px; color: var(--text-secondary);">
                      Preferencia: <strong style="color: var(--accent);">Ambos</strong>
                  </div>
                </div>
                
                <!-- Botones 100% Ancho -->
                <div style="display: flex; flex-direction: column; gap: 10px; width: 100%;">
                  <button onclick="abrirWhatsApp()" style="width: 100%; background: #25D366; color: white; border: none; padding: 12px; border-radius: 8px; font-family: 'Barlow Condensed', sans-serif; font-size: 16px; font-weight: 600; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px;">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326z"/></svg>
                    Enviar por WhatsApp
                  </button>
                  <button onclick="abrirEmail()" style="width: 100%; background: #EA4335; color: white; border: none; padding: 12px; border-radius: 8px; font-family: 'Barlow Condensed', sans-serif; font-size: 16px; font-weight: 600; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px;">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586l-1.239-.757Zm3.436-.586L16 11.801V4.697l-5.803 3.546Z"/></svg>
                    Enviar por Correo
                  </button>
                </div>

              </div>
            </div>
      </div>

      <!-- Fila 3: Sub-menú de envío (Oculto inicialmente) -->
      <div id="contact-channels" class="contact-channels">
          <button class="btn-whatsapp">
              <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326z"/></svg>
              WhatsApp
          </button>
          <button class="btn-gmail">
              <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586l-1.239-.757Zm3.436-.586L16 11.801V4.697l-5.803 3.546Z"/></svg>
              Gmail
          </button>
          <button class="btn-ambos">Ambos</button>
      </div>

    </div>

  </div>
</div>
<script src="js/script.js"></script>
<script src="js/script_color.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/Cotizaciones.js"></script>

</body>
</html>
