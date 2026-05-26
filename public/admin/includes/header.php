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

<style>
  /* =========================================
     ESTILOS BASE (Menú, Perfil, Formularios)
     ========================================= */
  .topbar {
    position: relative; /* Importante para que el menú móvil se posicione bien */
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    /* Ajusta el color de fondo de tu topbar según lo tengas en tu CSS general */
  }

  .nav-item { text-decoration: none !important; }
  .profile-container { position: relative; display: inline-block; }
  
  .profile-trigger {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    background-color: #3a3b3c;
    color: #e4e6eb;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid transparent;
    transition: border 0.2s, background-color 0.2s;
  }
  .profile-trigger:hover {
    border: 2px solid var(--accent, #3b82f6);
    background-color: #4e4f50;
  }

  .dropdown-menu {
    display: none; 
    position: absolute;
    top: 50px;
    right: 0;
    width: 300px;
    background-color: #242526;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    padding: 20px;
    z-index: 1000;
    color: #e4e6eb;
    font-family: system-ui, -apple-system, sans-serif;
  }
  .dropdown-menu.show { display: block; }

  .profile-form-title {
    margin: 0 0 15px 0;
    font-size: 16px;
    font-weight: 600;
    border-bottom: 1px solid #3e4042;
    padding-bottom: 10px;
    text-align: center;
  }

  .form-group { margin-bottom: 12px; }
  .form-group label { display: block; font-size: 12px; color: #b0b3b8; margin-bottom: 4px; }
  
  .form-group input {
    width: 100%;
    padding: 8px 10px;
    border-radius: 6px;
    border: 1px solid #3e4042;
    background-color: #3a3b3c;
    color: #e4e6eb;
    font-size: 14px;
    box-sizing: border-box;
  }
  .form-group input:focus { outline: none; border-color: var(--accent, #3b82f6); }

  .btn-save {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    background-color: var(--accent, #3b82f6);
    color: white;
    border: none;
    font-weight: 600;
    cursor: pointer;
    margin-top: 10px;
  }
  .btn-save:hover { opacity: 0.9; }

  .btn-logout {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    background-color: transparent;
    color: #ff5252;
    border: 1px solid #ff5252;
    font-weight: 600;
    cursor: pointer;
    margin-top: 10px;
    text-align: center;
    text-decoration: none;
    display: block;
    box-sizing: border-box;
  }
  .btn-logout:hover { background-color: #ff5252; color: white; }
  
  .profile-alert {
    padding: 8px;
    border-radius: 4px;
    font-size: 12px;
    margin-bottom: 10px;
    text-align: center;
  }
  .alert-success { background-color: #2e7d32; color: white; }
  .alert-error { background-color: #c62828; color: white; }

  /* =========================================
     ESTILOS DEL LOGO
     ========================================= */
  .logo-img {
    height: 36px; /* Altura ideal para un header de escritorio */
    width: auto;
    object-fit: contain; /* Evita que la imagen se deforme */
  }

  /* =========================================
     ESTILOS RESPONSIVOS Y MENÚ HAMBURGUESA
     ========================================= */
  .hamburger-btn {
    display: none; /* Oculto en PC */
    background: none;
    border: none;
    color: currentColor; /* Se adapta al modo claro/oscuro automáticamente */
    cursor: pointer;
    padding: 4px;
  }

  .desktop-nav {
    display: flex;
    align-items: center;
    gap: 4px;
  }

  @media (max-width: 768px) {
    .hamburger-btn {
      display: block; /* Mostramos la hamburguesa */
    }
    .brand-sub {
      display: none; /* Ocultamos el subtítulo para ganar espacio */
    }
    
    /* Transformamos el <nav> horizontal en un menú vertical */
    .desktop-nav {
      display: none; /* Oculto por defecto en móvil */
      flex-direction: column;
      position: absolute;
      top: 100%; /* Justo debajo del header */
      left: 0;
      width: 100%;
      background-color: #242526; /* Color de fondo oscuro */
      padding: 10px 0;
      box-shadow: 0 4px 12px rgba(0,0,0,0.4);
      z-index: 990;
    }
    
    .desktop-nav.show-mobile {
      display: flex !important;
    }
    
    .desktop-nav a.nav-item {
      width: 100%;
      padding: 12px 20px;
      text-align: left;
      border-radius: 0;
    }
    
    .desktop-nav a.nav-item:hover {
      background-color: #3a3b3c;
    }
  }

  /* Posicionamiento del contenedor principal */
.nav-item-dropdown {
    position: relative;
    display: inline-block;
}

/* Estilo del botón para que parezca un enlace normal de tu menú */
.nav-link-dropdown {
    background: none;
    border: none;
    color: #6b7280; /* Color gris/azulado de tu menú */
    font-weight: 600;
    cursor: pointer;
    padding: 10px 15px;
    font-family: inherit;
    text-transform: uppercase;
}

/* El submenú flotante (Oculto por defecto) */
.dropdown-content {
    display: none; /* Esto es lo que lo esconde */
    position: absolute;
    background-color: #ffffff;
    min-width: 250px; /* Ancho del submenú */
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.1); /* Sombra elegante */
    z-index: 1000;
    border-radius: 8px;
    top: 100%;
    left: 0;
    margin-top: 5px;
}

/* Los enlaces dentro del submenú */
.dropdown-content a {
    color: #4b5563;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    font-size: 14px;
}

/* Efecto al pasar el ratón por las opciones del submenú */
.dropdown-content a:hover {
    background-color: #f3f4f6; /* Fondo gris claro */
    border-radius: 8px;
}

/* Esta es la clase que JavaScript agregará para hacerlo visible */
.mostrar-dropdown {
    display: block;
}

  /* =========================================
     ESTILOS PARA EL MODO CLARO (Quiet Luxury)
     ========================================= */
  html:not(.dark) .dropdown-menu {
    background-color: #fdfbf7;
    color: #333333;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08); 
    border: 1px solid #e8d5d1;
  }

  html:not(.dark) .profile-form-title {
    border-bottom: 1px solid #e8d5d1; 
    color: #1a1a1a;
  }

  html:not(.dark) .form-group label { color: #666666; }

  html:not(.dark) .form-group input {
    background-color: #ffffff;
    border: 1px solid #d1d1d1;
    color: #333333;
  }

  html:not(.dark) .form-group input:focus {
    border-color: #c49a94;
    box-shadow: 0 0 0 2px rgba(196, 154, 148, 0.15);
  }

  html:not(.dark) .profile-trigger { background-color: #f5f0ec; color: #333333; }
  html:not(.dark) .profile-trigger:hover { background-color: #e8d5d1; border-color: #c49a94; }
  
  html:not(.dark) .btn-save { background-color: #2b2b2b; color: #ffffff; }
  html:not(.dark) .btn-save:hover { background-color: #1a1a1a; }

  /* Estilos del menú móvil en Modo Claro */
  @media (max-width: 768px) {
    html:not(.dark) .desktop-nav {
      background-color: #fdfbf7;
      border-top: 1px solid #e8d5d1;
      border-bottom: 1px solid #e8d5d1;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }
    html:not(.dark) .desktop-nav a.nav-item:hover {
      background-color: #f5f0ec;
    }
  }
</style>

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
    <button class="nav-link-dropdown" onclick="toggleCotizaciones()">Cotizaciones ▾</button>
    
    <div id="menuCotizaciones" class="dropdown-content">
        <a href="/Proyecto/public/admin/cotizaciones.php">Cotizaciones</a>
        <a href="/Proyecto/public/admin/seguimiento.php">Seguimiento de cotizaciones</a>
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
    // Cerramos el menú móvil si está abierto para evitar conflictos visuales
    document.getElementById("mainNav").classList.remove("show-mobile");
  }

  // Controla el menú hamburguesa (Móvil)
  function toggleMobileMenu(event) {
    event.stopPropagation();
    document.getElementById("mainNav").classList.toggle("show-mobile");
    // Cerramos el menú de perfil si está abierto
    document.getElementById("profileMenu").classList.remove("show");
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
  });


// Función para mostrar/ocultar el menú al hacer clic en "COTIZACIONES"
function toggleCotizaciones() {
    document.getElementById("menuCotizaciones").classList.toggle("mostrar-dropdown");
}

// Función de seguridad: Cierra el menú si el usuario hace clic en cualquier otro lado de la pantalla
window.onclick = function(event) {
    if (!event.target.matches('.nav-link-dropdown')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('mostrar-dropdown')) {
                openDropdown.classList.remove('mostrar-dropdown');
            }
        }
    }
}
</script>