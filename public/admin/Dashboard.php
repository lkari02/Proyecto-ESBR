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
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Error de conexión"); }

// 1. CONSULTAS PARA LOS WIDGETS
$total_productos = $pdo->query("SELECT COUNT(*) FROM piezas")->fetchColumn();
$pendientes      = $pdo->query("SELECT COUNT(*) FROM cotizaciones WHERE estado_cotizacion = 'pendiente'")->fetchColumn();
$total_clientes  = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
// Suma de ventas aprobadas (Ingresos Reales)
$total_ventas    = $pdo->query("SELECT SUM(total) FROM cotizaciones WHERE estado_cotizacion = 'aprobada'")->fetchColumn() ?: 0;

// 2. CONSULTA PARA LA ACTIVIDAD RECIENTE (Simulando visitas o movimientos)
// Si tienes una tabla de visitas puedes usarla, si no, usaremos el historial de actividades
$actividades = $pdo->query("SELECT * FROM historial_actividades ORDER BY fecha_movimiento DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - ESBR</title>
    <link rel="icon" type="image/png" href="assets/img/logo2.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700&family=Barlow:wght@400;500&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<main class="main">
    <div class="welcome-header">
        <h1 class="page-title">Bienvenido de nuevo</h1>
        <p class="page-sub">// Resumen operativo de la plataforma</p>
    </div>

    <div class="dashboard-grid">
        <div class="dash-card">
            <div class="card-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div>
            <div class="card-info">
                <span class="card-label">Productos en Catálogo</span>
                <span class="card-value"><?= $total_productos ?></span>
            </div>
        </div>
        <div class="dash-card">
            <div class="card-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
            <div class="card-info">
                <span class="card-label">Cotizaciones Pendientes</span>
                <span class="card-value"><?= $pendientes ?></span>
            </div>
        </div>
        <div class="dash-card">
            <div class="card-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
            <div class="card-info">
                <span class="card-label">Clientes Registrados</span>
                <span class="card-value"><?= $total_clientes ?></span>
            </div>
        </div>
        
    </div>

    <div class="dashboard-content">
        <div class="content-box">
            <div class="box-header">
                <h3>Monitoreo de Actividad Reciente</h3>
                <span class="badge badge-brand">En vivo</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Acción</th>
                            <th>Módulo</th>
                            <th>Detalle</th>
                            <th>Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($actividades as $a): ?>
                        <tr>
                            <td><span class="badge <?= $a['accion'] == 'ELIMINAR' ? 'badge-rejected' : 'badge-approved' ?>"><?= $a['accion'] ?></span></td>
                            <td style="font-weight: 500;"><?= $a['modulo'] ?></td>
                            <td style="color: var(--text-muted); font-size: 13px;"><?= $a['detalle'] ?></td>
                            <td style="font-family: 'IBM Plex Mono'; font-size: 12px;"><?= date('d/m H:i', strtotime($a['fecha_movimiento'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-box side-box">
            <h3>Accesos Rápidos</h3>
            <div class="quick-actions">
                <a href="catalogo.php" class="action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Nueva Refacción
                </a>
                <a href="Cotizaciones.php?estado=pendiente" class="action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Revisar Pendientes
                </a>
                <button class="action-btn" onclick="alert('Generando Reporte General...')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Descargar Reporte
                </button>
            </div>
        </div>
    </div>
</main>

<script src="js/script_color.js"></script>
</body>
</html>