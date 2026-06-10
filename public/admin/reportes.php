<?php
session_start();

// El "Guardia de Seguridad" directo en el Dashboard
if (!isset($_SESSION['admin_logeado']) || $_SESSION['admin_logeado'] !== true) {
    // Si no está logeado, lo pateamos de vuelta al login
    header("Location: /Proyecto/public/admin/login.php");
    exit;
}
$host = '127.0.0.1'; $db = 'bombaparts'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (\PDOException $e) { die("Error de conexión"); }

// 1. Obtener Estadísticas Dinámicas (Adaptado a tu tabla actual)
$hoy = date('Y-m-d');
$stHoy = $pdo->query("SELECT COUNT(*) as total FROM historial_actividades WHERE DATE(fecha_movimiento) = '$hoy'")->fetch()['total'];
$stLogin = $pdo->query("SELECT COUNT(*) as total FROM historial_actividades WHERE UPPER(accion) = 'LOGIN'")->fetch()['total'];
$stEdit = $pdo->query("SELECT COUNT(*) as total FROM historial_actividades WHERE UPPER(accion) = 'EDITAR' AND modulo = 'Catálogo'")->fetch()['total'];
$stAprob = $pdo->query("SELECT COUNT(*) as total FROM historial_actividades WHERE UPPER(accion) = 'APROBADA' AND modulo = 'Cotizaciones'")->fetch()['total'];

// 2. Obtener los Registros
$logs = $pdo->query("SELECT * FROM historial_actividades ORDER BY fecha_movimiento DESC LIMIT 200")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historial de Movimientos - ESBR</title>
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

<!-- TOPBAR -->
<?php include 'includes/header.php'; ?>

<main class="main">
  <!-- HEADER -->
  <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:20px;">
    <div>
      <div class="page-title">HISTORIAL DE MOVIMIENTOS</div>
      <div class="page-sub">// equipos de bombeo · reportes · historial</div>
    </div>
    <button class="btn-primary" onclick="openPdfModal()" style="background-color: var(--accent);">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Generar PDF Profesional
    </button>
  </div>

  <!-- STATS CARDS -->

      <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Registros Hoy</div>
            <div class="stat-value val-white"><?php echo $stHoy; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Inicios de Sesión</div>
            <div class="stat-value val-success"><?php echo $stLogin; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Productos Editados</div>
            <div class="stat-value val-brand"><?php echo $stEdit; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Cotiz. Aprobadas</div>
            <div class="stat-value val-brand"><?php echo $stAprob; ?></div>
        </div>
    </div>
    
  <!-- FILTER BAR -->
  <div class="filter-bar" style="margin-bottom:16px;">
    <span class="filter-label">Filtrar:</span>
    <input class="filter-input" type="text" id="searchLog" placeholder="Buscar usuario o descripción..." oninput="filterLogs()">
    <div style="width:1px;height:28px;background:var(--border);"></div>
    <select class="filter-select" id="catFilter" onchange="filterLogs()">
      <option value="ALL">Todos los módulos</option>
      <option value="Catálogo">Catálogo</option>
      <option value="Cotizaciones">Cotizaciones</option>
      <option value="Usuarios">Usuarios</option>
    </select>
    <button class="btn-outline" onclick="clearLogFilters()">Limpiar</button>
  </div>

  <!-- TABLE -->
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width:140px">FECHA</th>
          <th style="width:130px">USUARIO</th>
          <th style="width:110px">ACCIÓN</th>
          <th style="width:130px">MÓDULO</th>
          <th>DESCRIPCIÓN DEL EVENTO</th>
        </tr>
      </thead>
      <tbody id="logTableBody">
        <?php foreach($logs as $l): ?>
        <tr>
          <td style="font-family:'IBM Plex Mono',monospace; font-size:12px;"><?php echo date('d/m/Y H:i', strtotime($l['fecha_movimiento'])); ?></td>
          <td style="font-weight:600;"><?php echo htmlspecialchars($l['usuario']); ?></td>
          <td>
            <?php 
                $accion = strtoupper($l['accion']);
                $color = 'var(--text-muted)';
                if(in_array($accion, ['CREAR', 'LOGIN', 'APROBADA'])) $color = 'var(--success)';
                if(in_array($accion, ['EDITAR'])) $color = 'var(--warning)';
                if(in_array($accion, ['ELIMINAR', 'RECHAZADA'])) $color = 'var(--danger)';
            ?>
            <span style="font-family:'Barlow Condensed',sans-serif; font-weight:700; color:<?php echo $color; ?>"><?php echo $accion; ?></span>
          </td>
          <td><span class="badge badge-brand"><?php echo htmlspecialchars($l['modulo']); ?></span></td>
          <td style="color:var(--text-secondary); font-size:13px;"><?php echo htmlspecialchars($l['detalle']); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

<!-- MODAL DESCARGA PDF -->
<div class="modal-overlay" id="pdfModal">
  <div class="modal-box" style="max-width: 500px;">
    <form action="/Proyecto/app/services/pdf_historial.php" method="POST" target="_blank" onsubmit="closePdfModal()">
        <div class="modal-header">
          <div class="modal-title">Configuración de Reporte PDF</div>
          <button type="button" class="modal-close" onclick="closePdfModal()">×</button>
        </div>
        <div class="modal-body" style="display:block; padding:24px;">
            <div class="field" style="margin-bottom:15px;">
              <label>Selecciona el módulo a descargar <span class="req">*</span></label>
              <select name="filtro_pdf" style="width:100%; padding:10px; border-radius:8px; background:var(--bg-base); border:1px solid var(--border); color:var(--text-primary);">
                <option value="ALL">Todo el Historial</option>
                <option value="Cotizaciones">Solo Cotizaciones</option>
                <option value="Catálogo">Solo Catálogo</option>
                <option value="Usuarios">Solo Usuarios</option>
              </select>
            </div>
            <p style="font-size:12px; color:var(--text-muted);">El reporte se generará en formato apaisado (Landscape) detallando acciones y fechas exactas.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-cancel" onclick="closePdfModal()">Cancelar</button>
          <button type="submit" class="btn-primary">Generar y Descargar</button>
        </div>
    </form>
  </div>
</div>

<script>
function openPdfModal() { document.getElementById('pdfModal').classList.add('open'); }
function closePdfModal() { document.getElementById('pdfModal').classList.remove('open'); }

function filterLogs() {
    const search = document.getElementById('searchLog').value.toLowerCase();
    const catText = document.getElementById('catFilter').value;
    const rows = document.querySelectorAll('#logTableBody tr');

    rows.forEach(row => {
        const usuario = row.querySelector('td:nth-child(2)').innerText.toLowerCase();
        const desc = row.querySelector('td:nth-child(5)').innerText.toLowerCase();
        const cat = row.querySelector('td:nth-child(4)').innerText;

        const matchesSearch = usuario.includes(search) || desc.includes(search);
        const matchesCat = (catText === 'ALL') || (cat === catText);

        row.style.display = (matchesSearch && matchesCat) ? '' : 'none';
    });
}

function clearLogFilters() {
    document.getElementById('searchLog').value = '';
    document.getElementById('catFilter').value = 'ALL';
    filterLogs();
}
</script>
<script src="js/script.js"></script>
<script src="js/script_color.js"></script>
</body>
</html>