<?php
session_start();

// El "Guardia de Seguridad" directo en el Dashboard
if (!isset($_SESSION['admin_logeado']) || $_SESSION['admin_logeado'] !== true) {
    // Si no está logeado, lo pateamos de vuelta al login
    header("Location: /Proyecto/public/admin/login.php");
    exit;
}
// 1. CONFIGURACIÓN DE CONEXIÓN
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

// 2. CONSULTAS PARA ESTADÍSTICAS
$total_clientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$total_empresas = $pdo->query("SELECT COUNT(*) FROM clientes WHERE tipo_cliente = 'Empresa'")->fetchColumn();
$total_personas = $pdo->query("SELECT COUNT(*) FROM clientes WHERE tipo_cliente = 'Persona'")->fetchColumn();

// 3. OBTENER LISTADO DE CLIENTES
$clientes = $pdo->query("SELECT * FROM clientes ORDER BY nombre ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - ESBR</title>
      <link rel="icon" type="image/png" href="assets/img/logo2.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700&family=Barlow:wght@400;500&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/clientes.css">
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
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
        <div>
            <h1 class="page-title">Directorio de Clientes</h1>
            <p class="page-sub">// gestión de contactos y organizaciones</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Clientes</div>
            <div class="stat-value val-white"><?= $total_clientes ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Empresas</div>
            <div class="stat-value val-success"><?= $total_empresas ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Personas Físicas</div>
            <div class="stat-value val-brand"><?= $total_personas ?></div>
        </div>
    </div>

    <div class="filter-bar">
        <div class="search-wrapper">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="search" id="busqueda-clientes" class="search-input" placeholder="Buscar por nombre, organización o email...">
        </div>
    </div>

    <div class="table-wrap desktop-only">
        <table>
            <thead>
                <tr>
                    <th>Nombre / Razón Social</th>
                    <th>Tipo</th>
                    <th>Organización</th>
                    <th>Contacto</th>
                    <th>Ubicación</th>
                    <th style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-clientes-body">
                <?php foreach ($clientes as $c): ?>
                <tr class="cliente-fila">
                    <td style="font-weight: 500;"><?= htmlspecialchars($c['nombre']) ?></td>
                    <td>
                        <span class="badge <?= $c['tipo_cliente'] == 'Empresa' ? 'badge-approved' : 'badge-model' ?>">
                            <?= $c['tipo_cliente'] ?>
                        </span>
                    </td>
                    <td style="color: var(--text-muted);"><?= htmlspecialchars($c['organizacion'] ?: '—') ?></td>
                    <td>
                        <div style="font-size: 13px;"><?= htmlspecialchars($c['email']) ?></div>
                        <div style="font-family: 'IBM Plex Mono'; font-size: 12px; color: var(--text-secondary);"><?= htmlspecialchars($c['telefono'] ?: 'S/N') ?></div>
                    </td>
                    <td>
                        <div style="font-size: 13px;"><?= htmlspecialchars($c['ubicacion_ciudad']) ?></div>
                        <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($c['pais']) ?></div>
                    </td>
                    <td style="text-align: center;">
                        <button class="act-btn edit btn-ver-cliente" 
                            data-nombre="<?= htmlspecialchars($c['nombre']) ?>"
                            data-tipo="<?= htmlspecialchars($c['tipo_cliente']) ?>"
                            data-org="<?= htmlspecialchars($c['organizacion'] ?: 'Sin organización registrada') ?>"
                            data-email="<?= htmlspecialchars($c['email']) ?>"
                            data-telefono="<?= htmlspecialchars($c['telefono'] ?: 'No registrado') ?>"
                            data-ubicacion="<?= htmlspecialchars($c['ubicacion_ciudad'] ?: 'No especificada') ?>"
                            data-pais="<?= htmlspecialchars($c['pais']) ?>"
                            data-fecha="<?= date('d/m/Y', strtotime($c['creado_en'])) ?>"
                            title="Ver Detalles">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="pointer-events: none;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
                            <div id="modal-cliente" class="modal-overlay">
  <div class="modal-box" style="max-width: 600px;">
    
    <div class="modal-header">
      <div class="modal-title">EXPEDIENTE DEL CLIENTE</div>
      <button id="btn-cerrar-cliente" class="modal-close">✕</button>
    </div>

    <div class="modal-body">
      
      <div class="cliente-seccion">
        <div class="section-label">Información Principal</div>
        <div class="info-card">
          <div class="field field-full">
            <label>Nombre / Razón Social</label>
            <div id="md-nombre" style="font-size: 18px; font-weight: 600; color: var(--text-primary);">-</div>
          </div>
          <div class="field">
            <label>Tipo de Perfil</label>
            <div><span id="md-tipo" class="badge">-</span></div>
          </div>
          <div class="field">
            <label>Cliente desde</label>
            <div id="md-fecha" style="font-family: 'IBM Plex Mono', monospace; font-size: 14px; color: var(--text-secondary);">-</div>
          </div>
        </div>
      </div>

      <div class="cliente-seccion">
        <div class="section-label">Datos de Contacto</div>
        <div class="info-card">
          <div class="field field-full">
            <label>Correo Electrónico</label>
            <div id="md-email" style="font-size: 14px; color: var(--text-primary);">-</div>
          </div>
          <div class="field">
            <label>Teléfono Principal</label>
            <div id="md-telefono" style="font-family: 'IBM Plex Mono', monospace; font-size: 14px; color: var(--text-primary);">-</div>
          </div>
        </div>
      </div>

      <div class="cliente-seccion">
        <div class="section-label">Empresa y Ubicación</div>
        <div class="info-card">
          <div class="field field-full">
            <label>Organización / Empresa</label>
            <div id="md-org" style="font-size: 14px; color: var(--text-primary);">-</div>
          </div>
          <div class="field">
            <label>Ciudad</label>
            <div id="md-ubicacion" style="font-size: 14px; color: var(--text-primary);">-</div>
          </div>
          <div class="field">
            <label>País</label>
            <div id="md-pais" style="font-size: 14px; color: var(--text-primary);">-</div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
</main>

<script src="js/script_color.js"></script>
<script src="js/clientes.js"></script>
</body>
</html>