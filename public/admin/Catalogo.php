<?php
session_start();

// El "Guardia de Seguridad" directo en el Dashboard
if (!isset($_SESSION['admin_logeado']) || $_SESSION['admin_logeado'] !== true) {
    // Si no está logeado, lo pateamos de vuelta al login
    header("Location: /Proyecto/public/admin/login.php");
    exit;
}

// 1. CONFIGURACIÓN DE CONEXIÓN Y LOGS
$host = '127.0.0.1';
$db   = 'bombaparts';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

function registrarActividad($pdo, $accion, $modulo, $detalle) {
    $sql = "INSERT INTO historial_actividades (usuario, accion, modulo, detalle, fecha_movimiento) 
            VALUES ('Admin', :accion, :modulo, :detalle, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'accion'  => $accion,
        'modulo'  => $modulo,
        'detalle' => $detalle
    ]);
}

// 2. PROCESAMIENTO DE ACCIONES (POST/AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // -- ELIMINAR PIEZA --
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $stmtInfo = $pdo->prepare("SELECT sku FROM piezas WHERE id = ?");
        $stmtInfo->execute([$id]);
        $piezaLog = $stmtInfo->fetch();

        $stmt = $pdo->prepare("DELETE FROM piezas WHERE id = ?");
        if ($stmt->execute([$id])) {
            registrarActividad($pdo, 'ELIMINAR', 'Catálogo', "Se eliminó la pieza con SKU: " . $piezaLog['sku']);
            echo json_encode(['status' => 'success']);
            exit;
        }
    }

      // -- GUARDAR / EDITAR PIEZA --
    if (isset($_POST['action']) && $_POST['action'] === 'save') {
        ob_start(); 
        try {
            $id = !empty($_POST['fId']) ? $_POST['fId'] : null;
            $sku = $_POST['fSku'] ?? '';
            $marca_id = !empty($_POST['fMarca']) ? $_POST['fMarca'] : null; 
            $precio = $_POST['fPrecio'] ?? 0;
            $stock = $_POST['fStock'] ?? 0;
            
            // Textos traducibles
            $nombre_es = $_POST['fNombre_es'] ?? '';
            $desc_es = $_POST['fDesc_es'] ?? '';
            $nombre_en = $_POST['fNombre_en'] ?? '';
            $desc_en = $_POST['fDesc_en'] ?? '';

            if (!$marca_id) throw new Exception("Error: No se seleccionó una marca válida.");

            if ($id) {
                // EDITAR
                $sql = "UPDATE piezas SET sku=?, marca_id=?, precio_unitario=?, stock=?, nombre=?, descripcion_tecnica=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$sku, $marca_id, $precio, $stock, $nombre_es, $desc_es, $id]);
                $pieza_id = $id;
                registrarActividad($pdo, 'EDITAR', 'Catálogo', "Se editó la pieza SKU: $sku");
            } else {
                // INSERTAR NUEVA
                $sql = "INSERT INTO piezas (sku, marca_id, precio_unitario, stock, nombre, descripcion_tecnica) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$sku, $marca_id, $precio, $stock, $nombre_es, $desc_es]);
                $pieza_id = $pdo->lastInsertId();
                registrarActividad($pdo, 'CREAR', 'Catálogo', "Se registró nueva pieza SKU: $sku");
            }

            // 2. Preparar la consulta SQL para traducciones
            $sqlTrad = "INSERT INTO piezas_traducciones (pieza_id, idioma, nombre, descripcion_tecnica) VALUES (?, ?, ?, ?)";
            $stmtTrad = $pdo->prepare($sqlTrad);

            if (!empty($nombre_es)) {
                $stmtTrad->execute([$pieza_id, 'es', $nombre_es, $desc_es]);
            }
            if (!empty($nombre_en)) {
                $stmtTrad->execute([$pieza_id, 'en', $nombre_en, $desc_en]);
            }

            // === CÓDIGO PARA GUARDAR IMÁGENES FÍSICAMENTE ===
            if (!empty($_FILES['imagenes']['name'][0])) {
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Proyecto/public/admin/uploads/piezas/';
                
                if (!is_dir($uploadDir)) { 
                    mkdir($uploadDir, 0777, true); 
                }

                $pdo->prepare("DELETE FROM piezas_imagenes WHERE pieza_id = ?")->execute([$pieza_id]);

                $totalFiles = count($_FILES['imagenes']['name']);
                for ($i = 0; $i < $totalFiles; $i++) {
                    $tmpName = $_FILES['imagenes']['tmp_name'][$i];
                    $fileName = $_FILES['imagenes']['name'][$i];
                    
                    if ($tmpName) {
                        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
                        
                        if (!in_array($ext, $extensionesPermitidas)) {
                            continue; 
                        }

                        $newName = $pieza_id . '_' . time() . '_' . $i . '.' . $ext;
                        $targetPath = $uploadDir . $newName;

                        if (move_uploaded_file($tmpName, $targetPath)) {
                            $stmtImg = $pdo->prepare("INSERT INTO piezas_imagenes (pieza_id, ruta_imagen, orden) VALUES (?, ?, ?)");
                            // AQUI ESTÁ LA RUTA CORREGIDA LISTA PARA FUNCIONAR
                            $stmtImg->execute([$pieza_id, '/Proyecto/public/admin/uploads/piezas/' . $newName, $i]); 
                        }
                    }
                }
            }

            ob_clean(); 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
            
        } catch (\Throwable $e) { 
            ob_clean(); 
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
} // <--- ESTA ERA LA LLAVE QUE FALTABA

// 3. CONSULTA DE DATOS PARA LA VISTA
$resResumen = $pdo->query("SELECT COUNT(*) as total, SUM(stock) as stock_total FROM piezas")->fetch(PDO::FETCH_ASSOC);
$resLow = $pdo->query("SELECT COUNT(*) as bajo FROM piezas WHERE stock > 0 AND stock < 10")->fetch(PDO::FETCH_ASSOC);
$resOut = $pdo->query("SELECT COUNT(*) as agotado FROM piezas WHERE stock = 0")->fetch(PDO::FETCH_ASSOC);
$marcas = $pdo->query("SELECT id, nombre FROM marcas WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC);

// Obtenemos piezas
$piezas = $pdo->query("SELECT p.*, m.nombre as marca_nombre FROM piezas p JOIN marcas m ON p.marca_id = m.id ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);

foreach ($piezas as $key => $p) {
    $stmtImg = $pdo->prepare("SELECT id, ruta_imagen FROM piezas_imagenes WHERE pieza_id = ? ORDER BY orden ASC");
    $stmtImg->execute([$p['id']]);
    $piezas[$key]['imagenes'] = $stmtImg->fetchAll(PDO::FETCH_ASSOC);
}
// Obtenemos piezas
$piezas = $pdo->query("SELECT p.*, m.nombre as marca_nombre FROM piezas p JOIN marcas m ON p.marca_id = m.id ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);

foreach ($piezas as $key => $p) {
    // 1. Buscar Imágenes (esto ya lo tenías)
    $stmtImg = $pdo->prepare("SELECT id, ruta_imagen FROM piezas_imagenes WHERE pieza_id = ? ORDER BY orden ASC");
    $stmtImg->execute([$p['id']]);
    $piezas[$key]['imagenes'] = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

    // 2. Buscar Traducciones (ESTO ES LO NUEVO)
    $stmtTrad = $pdo->prepare("SELECT idioma, nombre, descripcion_tecnica FROM piezas_traducciones WHERE pieza_id = ?");
    $stmtTrad->execute([$p['id']]);
    $traducciones = $stmtTrad->fetchAll(PDO::FETCH_ASSOC);

    // Inicializamos las variables vacías dentro del arreglo de esta pieza
    $piezas[$key]['nombre_es'] = '';
    $piezas[$key]['desc_es']   = '';
    $piezas[$key]['nombre_en'] = '';
    $piezas[$key]['desc_en']   = '';

    // Asignamos los valores si existen
    foreach ($traducciones as $trad) {
        if ($trad['idioma'] == 'es') {
            $piezas[$key]['nombre_es'] = $trad['nombre'];
            $piezas[$key]['desc_es']   = $trad['descripcion_tecnica'];
        } elseif ($trad['idioma'] == 'en') {
            $piezas[$key]['nombre_en'] = $trad['nombre'];
            $piezas[$key]['desc_en']   = $trad['descripcion_tecnica'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Catálogo - ESBR</title>
    <link rel="icon" type="image/png" href="assets/img/logo2.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;500;600;700&family=Barlow:wght@400;500&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/catalogo_cat.css">
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

<!-- ===== TOPBAR ===== -->
<?php include 'includes/header.php'; ?>

<main class="main">

  <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:20px;">
    <div>
      <div class="page-title">Catálogo de Piezas</div>
      <div class="page-sub">// equipos de bombeo · servicio · refacciones</div>
    </div>
    <div style="display:flex; gap:10px;">
        <!-- Botón de Exportar -->
        <a href="/Proyecto/app/services/generar_pdf.php" target="_blank" class="btn-outline" style="display:flex; align-items:center; gap:8px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Exportar Catálogo
        </a>
        <button class="btn-primary" onclick="openModal()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Agregar nueva pieza
        </button>
    </div>
  </div>

  <!-- Stats Dinámicos -->
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;">
    <div class="stat-card">
      <div class="stat-label">Total piezas</div>
      <div class="stat-value" style="color:var(--accent)"><?php echo $resResumen['total']; ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">En stock</div>
      <div class="stat-value" style="color:var(--success)"><?php echo $resResumen['stock_total'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Stock bajo</div>
      <div class="stat-value" style="color:var(--warning)"><?php echo $resLow['bajo']; ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Agotadas</div>
      <div class="stat-value" style="color:var(--danger)"><?php echo $resOut['agotado']; ?></div>
    </div>
  </div>

  <div class="filter-bar" style="margin-bottom:16px;">
    <span class="filter-label">Filtrar:</span>
    <input class="filter-input" type="text" id="searchInput" placeholder="Buscar por nombre o SKU…" oninput="renderTable()">
    <div style="width:1px;height:28px;background:var(--border);"></div>
    <select class="filter-select" id="marcaFilter" onchange="renderTable()">
      <option value="">Todas las marcas</option>
      <?php foreach($marcas as $m): ?>
        <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nombre']); ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn-outline" onclick="clearFilters()">Limpiar</button>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width:100px">SKU</th>
          <th style="width:auto">Nombre de la pieza</th>
          <th style="width:110px">Marca</th>
          <th style="width:100px">Precio</th>
          <th style="width:80px">Stock</th>
          <th style="width:120px;text-align:center">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($piezas as $p): ?>
        <tr>
          <td><span class="sku-cell"><?php echo htmlspecialchars($p['sku']); ?></span></td>
          <td>
              <div style="font-weight:500;"><?php echo htmlspecialchars($p['nombre']); ?></div>
          </td>
          <td><span class="badge badge-brand"><?php echo htmlspecialchars($p['marca_nombre']); ?></span></td>
          <td class="price-cell">$<?php echo number_format($p['precio_unitario'], 2); ?></td>
          <td>
              <?php 
                $stockClass = ($p['stock'] <= 0) ? 'stock-out' : (($p['stock'] < 10) ? 'stock-low' : 'stock-ok');
                echo "<span class='$stockClass'>{$p['stock']}</span>";
              ?>
          </td>
          <td style="text-align:center">
              <div style="display:flex; gap:6px; justify-content:center;">
                  <button class="act-btn edit" onclick="editPieza(<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8'); ?>)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                  <button class="act-btn del" onclick="deletePieza(<?php echo $p['id']; ?>)">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
              </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

<!-- ===== MODAL PIEZA ===== -->
<div class="modal-overlay" id="mainModal">
  <div class="modal-box">
    <form action="Catalogo.php" method="POST" id="piezaForm" enctype="multipart/form-data" onsubmit="guardarPieza(event)">
        <input type="hidden" name="fId" id="fId">
        <div class="modal-header">
          <div class="modal-title" id="modalTitleText">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            Registrar nueva pieza
          </div>
          <button type="button" class="modal-close" onclick="closeModal()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <div class="modal-body">
          <div class="modal-left">
            <div class="section-divider">Identificación</div>
            <div class="field-group">
              <div class="field">
                <label>SKU <span class="req">*</span></label>
                <input type="text" name="fSku" id="fSku" required placeholder="BP-GRU-001">
              </div>
              <div class="field">
                <label>Marca <span class="req">*</span></label>
                <select name="fMarca" id="fMarca" required>
                  <option value="">Seleccionar…</option>
                  <?php foreach($marcas as $m): ?>
                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nombre']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="lang-tabs-wrap">
              <div class="lang-tabs-header">
                <button type="button" class="lang-tab-btn active" id="tab-es" onclick="switchLangTab('es')">
                  <span class="flag">🇲🇽</span> Español
                </button>
                <button type="button" class="lang-tab-btn" id="tab-en" onclick="switchLangTab('en')">
                  <span class="flag">🇺🇸</span> English
                </button>
              </div>
<div class="lang-tab-panel active" id="panel-es">
                <div class="field">
                  <label>Nombre de la pieza <span class="req">*</span></label>
                  <input type="text" name="fNombre_es" id="fNombre_es" required maxlength="200">
                </div>
                <div class="field">
                  <label>Descripción técnica <span class="req">*</span></label>
                  <textarea name="fDesc_es" id="fDesc_es" maxlength="1000" rows="4"></textarea>
                </div>
              </div>
              
              
              <div class="lang-tab-panel" id="panel-en">
                <div class="field">
                  <label>Part name <span class="opt">(optional)</span></label>
                  <input type="text" name="fNombre_en" id="fNombre_en" >
                </div>
                <div class="field">
                  <label>Technical description <span class="opt">(optional)</span></label>
                  <textarea name="fDesc_en" id="fDesc_en" rows="4"></textarea>
                </div>
              </div>
            </div>

                <div class="section-divider" style="margin-top:4px;">Compatibilidad</div>
            <div class="field">
            <label>Modelos de bomba compatibles</label>
            <select id="fModelo">
                <option value="">Seleccionar modelo…</option>
                <option value="cm-3-5">Grundfos CM 3-5</option>
                <option value="sp-5a-18">Grundfos SP 5A-18</option>
                <option value="lcc-100-250">Xylem LCC 100-250</option>
                <option value="f-32-160a">Pedrollo F 32/160A</option>
                <option value="cam-80-00">Pentax CAM 80/00</option>
            </select>
            </div>

            <div class="section-divider" style="margin-top:4px;">Precio y stock</div>
            <div class="field-group">
              <div class="field">
                <label>Precio unitario (MXN)</label>
                <input type="number" name="fPrecio" id="fPrecio" required step="0.01">
              </div>
              <div class="field">
                <label>Stock inicial</label>
                <input type="number" name="fStock" id="fStock" required min="0">
              </div>
            </div>
          </div>

        <div class="modal-right">
            <!-- Quick preview card -->
            <div>
                <div class="preview-card">
                    <div class="preview-card-header">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Vista rápida
                    </div>
                    <div class="preview-card-body">
                        <div class="preview-thumb-main" id="prevThumb">
                            <div class="no-img">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity=".4"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </div>
                        </div>
                        <div class="preview-card-meta">
                            <div class="preview-product-name empty" id="prevName">Nombre de la pieza</div>
                            <div class="preview-detail">
                                <span id="prevSku">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                    <span style="color:var(--text-muted)">SKU: —</span>
                                </span>
                                <span id="prevStock">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
                                    <span style="color:var(--text-muted)">Stock: 0 · $0.00</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="drop-zone-label">
                <span>Imágenes del producto</span>
                <span class="img-counter empty" id="imgCounter">0 / 5</span>
            </div>

            <div class="img-rules" style="margin-bottom:10px;">
                <div class="img-rule min">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Mínimo 3 imágenes
                </div>
                <div class="img-rule max">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Máximo 5 imágenes
                </div>
            </div>

            <div class="drop-zone" id="dropZone"
                ondragover="handleDragOver(event)"
                ondragleave="handleDragLeave(event)"
                ondrop="handleDrop(event)"
                onclick="document.getElementById('fileInput').click()">
                <div class="drop-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                </div>
                <div class="drop-text"><strong>Haz clic o arrastra</strong> para subir imágenes</div>
                <div class="drop-hint">JPG</div>
            </div>
            
            <!-- Agregado name="imagenes[]" para procesar multiples archivos -->
            <input type="file" id="fileInput" name="imagenes[]" multiple accept="image/*" onchange="handleFiles(event.target.files)" style="display:none;">
            
            <div>
                <div class="thumbs-label">Imágenes añadidas:</div>
                <div class="thumbs-strip" id="thumbsStrip">
                    <div class="thumb-add-btn" onclick="document.getElementById('fileInput').click()" id="thumbAddBtn" title="Añadir imagen">+</div>
                </div>
            </div>

            <!-- Validation bar -->
            <div class="validation-bar empty" id="validationBar">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span id="validationMsg">Añade al menos 6 imágenes para continuar</span>
            </div>

            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary" id="saveBtn" style="opacity:.5; cursor:not-allowed;" disabled>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Guardar pieza
                </button>
            </div> <!-- Cierre faltante del modal-footer -->
        </div> <!-- Cierre faltante del modal-right -->
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/script_color.js"></script>
<script src="js/script.js"></script>
<script src="js/script_catalogo.js"></script>
</body>
</html>