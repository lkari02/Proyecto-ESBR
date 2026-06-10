<?php
session_start();

// 1. Protección de la ruta (Guardia de Seguridad)
if (!isset($_SESSION['admin_logeado']) || $_SESSION['admin_logeado'] !== true) {
    header("Location: /Proyecto/public/admin/login.php");
    exit;
}

// 2. Configuración de la base de datos
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

// ── TAB ACTIVO (Pestaña por defecto) ─────────────────────────
$estados_validos = ['confirmada', 'no_aprobada', 'finalizada'];
$tab_activo      = $_GET['estado'] ?? 'confirmada';
if (!in_array($tab_activo, $estados_validos, true)) {
    $tab_activo = 'confirmada';
}

// ── SOLUCIÓN AL BOTÓN "VER DETALLES" (Procesador AJAX) ───────
if (isset($_POST['action']) && $_POST['action'] === 'ver_detalles' && isset($_POST['id'])) {
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

    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'data' => $detalles]);
    exit;
}

// ── ACCIÓN: MARCAR COMO FINALIZADA ───────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'marcar_finalizada' && isset($_POST['id'])) {
    $id_cotizacion = $_POST['id'];
    try {
        $stmt_fin = $pdo->prepare("UPDATE cotizaciones SET estado_cotizacion = 'finalizada' WHERE id = ?");
        $stmt_fin->execute([$id_cotizacion]);
    } catch (PDOException $e) {
        // Manejo silencioso de errores
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?estado=finalizada");
    exit;
}

// ── CONTEOS PARA LOS BADGES Y TARJETAS ───────────────────────
$conteos_raw = []; 
try {
    $sql_conteos = "SELECT estado_cotizacion, COUNT(*) AS total 
                    FROM cotizaciones 
                    WHERE estado_cotizacion IN ('confirmada', 'no_aprobada', 'finalizada')
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
    ['confirmada' => 0, 'no_aprobada' => 0, 'finalizada' => 0],
    $conteos_raw
);

$total_seguimiento = array_sum($conteos);

// ── PAGINACIÓN ───────────────────────────────────────────────
$por_pagina    = 10;
$pagina_actual = max(1, (int)($_GET['pagina'] ?? 1));
$offset        = ($pagina_actual - 1) * $por_pagina;

// ── CONSULTA PRINCIPAL ───────────────────────────────────────
try {
    $sql_total = "SELECT COUNT(*) FROM cotizaciones WHERE estado_cotizacion = :estado";
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->execute([':estado' => $tab_activo]);
    $total_filas   = (int)$stmt_total->fetchColumn();
    $total_paginas = (int)ceil($total_filas / $por_pagina);

    $sql_lista = "
        SELECT 
            cot.id,
            cot.codigo_cotizacion,
            cot.fecha_solicitud,
            cot.fecha_respuesta,
            cot.estado_cotizacion,
            cot.vigencia_dias,
            cot.total,
            cot.notas_web,
            cli.nombre AS cliente_nombre,
            cli.organizacion,
            cli.email AS cliente_email,
            cli.telefono AS cliente_telefono
        FROM cotizaciones cot
        INNER JOIN clientes cli ON cot.cliente_id = cli.id
        WHERE cot.estado_cotizacion = :estado
        ORDER BY cot.fecha_respuesta DESC, cot.fecha_solicitud DESC
        LIMIT :limite OFFSET :offset
    ";

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

function clase_estado(string $estado): string {
    return match($estado) {
        'confirmada' => 'badge-success',
        'finalizada' => 'badge-approved',
        default      => 'badge-rejected',
    };
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Cotizaciones - ESBR</title>
    <link rel="icon" type="image/png" href="assets/img/logo2.png">
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
      <h1 class="page-title">Seguimiento de Cotizaciones</h1>
      <p class="page-sub">// seguimiento con clientes · respuestas · cierre de ventas</p>
    </div>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-label">Total en Seguimiento</div>
      <div class="stat-value val-white"><?= $total_seguimiento ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Aceptadas (Cliente)</div>
      <div class="stat-value val-success"><?= $conteos['confirmada'] ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">No Aceptadas</div>
      <div class="stat-value val-danger"><?= $conteos['no_aprobada'] ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Finalizadas</div>
      <div class="stat-value val-warning" style="color: #3b82f6;"><?= $conteos['finalizada'] ?></div>
    </div>
  </div>

  <div class="filter-bar" style="margin-bottom: 24px;">
    <div style="display: flex; gap: 8px;">
      <a href="?estado=confirmada" class="nav-item <?= $tab_activo === 'confirmada' ? 'active' : '' ?>" style="text-decoration: none;">Aceptadas (<?= $conteos['confirmada'] ?>)</a>
      <a href="?estado=no_aprobada" class="nav-item <?= $tab_activo === 'no_aprobada' ? 'active' : '' ?>" style="text-decoration: none;">No Aceptadas (<?= $conteos['no_aprobada'] ?>)</a>
      <a href="?estado=finalizada" class="nav-item <?= $tab_activo === 'finalizada' ? 'active' : '' ?>" style="text-decoration: none;">Finalizadas (<?= $conteos['finalizada'] ?>)</a>
    </div>
    <input type="search" id="buscador-seguimiento" class="filter-input" placeholder="Buscar por ID, cliente u organización…" style="margin-left: auto;">
  </div>

  <?php if (empty($cotizaciones)): ?>
    <div style="padding: 40px; text-align: center; color: var(--text-muted); background: var(--bg-surface); border: 1px solid var(--border); border-radius: 10px;">
      No hay cotizaciones con estado <strong><?= htmlspecialchars($tab_activo === 'confirmada' ? 'aceptada' : $tab_activo) ?></strong>.
    </div>
  <?php else: ?>

    <div class="table-wrap desktop-only">
      <table>
        <thead>
          <tr>
            <th>ID Cotización</th>
            <th>Cliente</th>
            <th>Organización</th>
            <th>Fecha Respuesta</th>
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
            <td style="font-family: 'IBM Plex Mono', monospace; font-size: 13px; color: var(--text-secondary);">
                <?= !empty($c['fecha_respuesta']) ? htmlspecialchars($c['fecha_respuesta']) : '—' ?>
            </td>
            <td style="font-family: 'IBM Plex Mono', monospace; font-size: 13px; font-weight: 500;">$<?= number_format((float)$c['total'], 2) ?></td>
            <td>
              <span class="badge <?= clase_estado($c['estado_cotizacion']) ?>">
                  <?= $c['estado_cotizacion'] === 'confirmada' ? 'Aceptada' : ucfirst($c['estado_cotizacion']) ?>
              </span>
            </td>
            <td style="text-align: center;">
                <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                    <button class="act-btn edit btn-ver-detalle" 
                        data-id="<?= htmlspecialchars($c['id'] ?? '') ?>"
                        data-codigo="<?= htmlspecialchars($c['codigo_cotizacion'] ?? '') ?>"
                        data-fecha="<?= htmlspecialchars($c['fecha_solicitud'] ?? '') ?>"
                        data-vigencia="<?= htmlspecialchars($c['vigencia_dias'] ?? '30') ?>"
                        data-cliente="<?= htmlspecialchars($c['cliente_nombre'] ?? '') ?>"
                        data-org="<?= htmlspecialchars($c['organizacion'] ?? '—') ?>"
                        data-email="<?= htmlspecialchars($c['cliente_email'] ?? '') ?>"
                        data-telefono="<?= htmlspecialchars($c['cliente_telefono'] ?? '') ?>"
                        data-total="<?= htmlspecialchars($c['total'] ?? '0') ?>"
                        data-estado="<?= htmlspecialchars($c['estado_cotizacion'] ?? '') ?>"
                        data-raw="<?= htmlspecialchars($c['notas_web'] ?? '') ?>" title="Ver Detalles">
                        <svg style="width:16px; height:16px; pointer-events: none;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>

                    <?php if ($c['estado_cotizacion'] === 'confirmada'): ?>
                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('¿Deseas marcar esta cotización como FINALIZADA (Pedido cerrado)?');">
                            <input type="hidden" name="action" value="marcar_finalizada">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button type="submit" class="act-btn" style="background: #2563eb; color: white;" title="Finalizar Pedido">
                                <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                                </svg>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
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
    <div class="modal-footer hidden" id="detalles-acciones"></div>
  </div>
</div>

<script src="js/script.js"></script>
<script src="js/script_color.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/Seguimiento.js"></script>

</body>
</html>