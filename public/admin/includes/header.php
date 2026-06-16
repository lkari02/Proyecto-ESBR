<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluimos tu archivo de conexión
include_once '../../app/config/conexion.php'; 

$pagina_actual = basename($_SERVER['PHP_SELF']);
$nombre_usuario = "Usuario";
$correo_usuario = "";

// Usamos la clave 'user_id'
if (isset($_SESSION['user_id']) && isset($conn)) {
    $id_logueado = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id_logueado);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($fila = $resultado->fetch_assoc()) {
            $nombre_usuario = $fila['nombre'];
            $correo_usuario = $fila['email'];
        }
        $stmt->close();
    }
}
?>

<header class="topbar">
  <div style="display:flex;align-items:center;gap:16px;">
    
    <button class="hamburger-btn" onclick="toggleMobileMenu(event)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
      </svg>
    </button>

    <div>
      <img src="/Proyecto/public/assets/img/logo2.png" alt="Equipos de Bombeo" class="logo-img">
    </div>
    
    <div class="brand-wrap">
      <div>
        <div class="brand-name">Equipos de Bombeo</div>
        <div class="brand-name">Servicio &amp; Refacciones</div>
      </div>
    </div>
  </div>
  
  <nav class="desktop-nav" id="mainNav">
    <a href="Dashboard.php" class="nav-item <?php echo ($pagina_actual == 'Dashboard.php') ? 'active' : ''; ?>">Inicio</a>
    <a href="catalogo.php" class="nav-item <?php echo ($pagina_actual == 'catalogo.php') ? 'active' : ''; ?>">Catálogo</a>
    
   <div class="nav-item-dropdown">
        <a href="javascript:void(0);" class="nav-item nav-link-dropdown" onclick="toggleCotizaciones(event)">Cotizaciones ▾</a>
        
        <div id="menuCotizaciones" class="dropdown-content">
            <a href="/Proyecto/public/admin/cotizaciones.php">Cotizaciones</a>
            <a href="/Proyecto/public/admin/Seguimiento.php">Seguimiento de cotizaciones</a>
        </div>
    </div>

    <a href="clientes.php" class="nav-item <?php echo ($pagina_actual == 'clientes.php') ? 'active' : ''; ?>">Clientes</a>
    <a href="reportes.php" class="nav-item <?php echo ($pagina_actual == 'reportes.php') ? 'active' : ''; ?>">Reportes</a>
  </nav>
  
  <div style="display:flex;align-items:center;gap:8px;">
    <button class="theme-toggle" id="themeToggle" title="Cambiar tema" onclick="toggleTheme()">
      <svg id="iconSun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      <svg id="iconMoon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
    </button>
    
    <div class="profile-container">
      <div class="profile-trigger" id="profileBtn" onclick="toggleProfileMenu(event)">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
        </svg>
      </div>
      
      <div class="dropdown-menu" id="profileMenu">
        <h3 class="profile-form-title">Mi Perfil</h3>
        
        <?php if(isset($_GET['profile_success'])): ?>
            <div class="profile-alert alert-success">Cambios guardados con éxito</div>
            <script>document.addEventListener('DOMContentLoaded', () => { document.getElementById('profileMenu').classList.add('show'); });</script>
        <?php endif; ?>
        <?php if(isset($_GET['profile_error'])): ?>
            <div class="profile-alert alert-error">Hubo un error al guardar</div>
            <script>document.addEventListener('DOMContentLoaded', () => { document.getElementById('profileMenu').classList.add('show'); });</script>
        <?php endif; ?>
        
        <form action="../../app/controllers/actualizar_perfil.php" method="POST">
          <div class="form-group">
            <label for="nombre">Nombre Completo</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre_usuario); ?>" required>
          </div>
          
          <div class="form-group">
            <label for="correo">Correo Electrónico</label>
            <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($correo_usuario); ?>" required>
          </div>
          
          <div class="form-group">
            <label for="password">Nueva Contraseña</label>
            <input type="password" id="password" name="password" placeholder="Dejar en blanco para no cambiar">
          </div>
          
          <button type="submit" class="btn-save">Guardar Cambios</button>
        </form>

        <a href="../../app/config/logger.php" class="btn-logout">Cerrar Sesión</a>
      </div>
    </div>
  </div>
</header>

<script>
  // Controla el menú del perfil
  function toggleProfileMenu(event) {
    event.stopPropagation();
    document.getElementById("profileMenu").classList.toggle("show");
    document.getElementById("mainNav").classList.remove("show-mobile");
    document.getElementById("menuCotizaciones").classList.remove("mostrar-dropdown"); // Cierra el otro menú
  }

  // Controla el menú hamburguesa (Móvil)
  function toggleMobileMenu(event) {
    event.stopPropagation();
    document.getElementById("mainNav").classList.toggle("show-mobile");
    document.getElementById("profileMenu").classList.remove("show");
  }

  // Controla el submenú de Cotizaciones
  function toggleCotizaciones(event) {
      event.stopPropagation();
      document.getElementById("menuCotizaciones").classList.toggle("mostrar-dropdown");
      document.getElementById("profileMenu").classList.remove("show"); // Cierra perfil si estaba abierto
  }

  // Evita que el menú de perfil se cierre al escribir en el formulario
  document.getElementById("profileMenu").addEventListener('click', function(event) {
    event.stopPropagation();
  });

  // Cierra cualquier menú desplegable al dar clic en otra parte de la pantalla
  window.addEventListener('click', function(event) {
    if (!event.target.closest('.profile-container')) {
      document.getElementById("profileMenu").classList.remove('show');
    }
    if (!event.target.closest('.hamburger-btn') && !event.target.closest('.desktop-nav')) {
      document.getElementById("mainNav").classList.remove('show-mobile');
    }
    if (!event.target.matches('.nav-link-dropdown')) {
        document.getElementById("menuCotizaciones").classList.remove('mostrar-dropdown');
    }
  });
</script>
<style>
/* Unifica el color usando los IDs exactos (Máxima prioridad) */
#mainNav .nav-item, 
#mainNav .nav-link-dropdown,
#menuCotizaciones a {
    color: #a0a4ab !important; /* Tono grisáceo base */
    transition: color 0.2s ease;
}

#mainNav .nav-item:hover, 
#mainNav .nav-link-dropdown:hover,
#menuCotizaciones a:hover {
    color: #ffffff !important; /* Blanco al pasar el mouse */
}

/* Modo Claro */
html:not(.dark) #mainNav .nav-item,
html:not(.dark) #mainNav .nav-link-dropdown,
html:not(.dark) #menuCotizaciones a {
    color: #666666 !important; 
}

html:not(.dark) #mainNav .nav-item:hover,
html:not(.dark) #mainNav .nav-link-dropdown:hover,
html:not(.dark) #menuCotizaciones a:hover {
    color: #1a1a1a !important;
}
</style>