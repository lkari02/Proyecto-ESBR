<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - BombaParts</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css\login.css">
</head>
<body>

<div class="split-layout">
    
    <div class="brand-panel">
        <div class="logo">
            <svg viewBox="0 0 24 24" fill="white" width="24" height="24">
                <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z"/>
            </svg>
            BOMBAPARTS
        </div>

        <div class="hero-text">
            <h1>Gestiona tus equipos con claridad</h1>
            <p>Herramientas precisas para el control de inventario, cotizaciones formales y seguimiento de clientes en tiempo real.</p>
        </div>

        <div class="footer-text">
            © 2026 BombaParts S.A.
        </div>
    </div>

    <div class="form-panel">
        <div class="auth-container">
            
            <div id="form-login">
                <div class="auth-header">
                    <h2>Iniciar sesión</h2>
                    <p>Ingresa tus credenciales para continuar</p>
                </div>

                <form id="loginForm" action="/Proyecto/app/controllers/procesar_login.php" method="POST">
                    <div class="input-group">
                        <label>Correo electrónico</label>
                        <div class="input-wrapper">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <input type="email" name="email" placeholder="nombre@empresa.com" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Contraseña</label>
                        <div class="input-wrapper">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <input type="password" name="password" id="login-password" placeholder="Introduce tu contraseña" required>
                            <button type="button" class="btn-eye" onclick="togglePassword('login-password', this)">
    <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
    <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
</button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label><input type="checkbox" name="recordar"> Recordar sesión</label>
                        <a onclick="showForgot()" style="cursor: pointer;">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="btn-primary">Acceder</button>
                </form>

                <div class="auth-switch">
                    ¿No tienes cuenta? <button onclick="toggleForm()">Crear cuenta</button>
                </div>
            </div>

            <div id="form-register" class="hidden-form">
                <div class="auth-header">
                    <h2>Crear cuenta</h2>
                    <p>Regístrate para acceder a la plataforma</p>
                </div>

                <form id="registroForm" action="../../app/controllers/procesar_registro.php" method="POST">
                    <div class="input-group">
                        <label>Nombre completo</label>
                        <div class="input-wrapper">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <input type="text" name="nombre" placeholder="Ej. Abel Corona" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Correo electrónico</label>
                        <div class="input-wrapper">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <input type="email" name="email" placeholder="nombre@empresa.com" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Contraseña</label>
                        <div class="input-wrapper">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <input type="password" name="password" id="reg-password" placeholder="Crea una contraseña segura" required>
                            
                            <button type="button" class="btn-eye" onclick="togglePassword('reg-password', this)">
                                <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                            </button>
                        </div>
                    </div>

                    <ul class="pass-reqs">
                        <li id="req-len">Mínimo 8 caracteres</li>
                        <li id="req-up">Al menos una letra mayúscula</li>
                        <li id="req-num">Al menos un número</li>
                        <li id="req-sp">Al menos un carácter especial (!@#$%)</li>
                    </ul>

                    <div class="input-group">
                        <label>Confirmar Contraseña</label>
                        <div class="input-wrapper">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <input type="password" name="password_confirm" id="reg-password-confirm" placeholder="Repite tu contraseña" required>
                            
                            <button type="button" class="btn-eye" onclick="togglePassword('reg-password-confirm', this)">
                                <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                            </button>
                        </div>
                        <span id="error-match" style="color: #dc2626; font-size: 12px; display: none; margin-top: 4px;">Las contraseñas no coinciden</span>
                    </div>

                    <button type="submit" id="btn-registrar" class="btn-primary" style="margin-top: 10px;" disabled>Solicitar Registro</button>
                </form>

                <div class="auth-switch">
                    ¿Ya tienes cuenta? <button onclick="toggleForm()">Iniciar sesión</button>
                </div>
            </div>

            <div id="form-forgot" class="hidden-form">
                <div class="auth-header">
                    <h2>Recuperar cuenta</h2>
                    <p>Ingresa tu correo y te enviaremos un enlace seguro para crear una nueva contraseña.</p>
                </div>

                <form action="/Proyecto/app/controllers/solicitar_recuperacion.php" method="POST">
                    <div class="input-group">
                        <label>Correo electrónico</label>
                        <div class="input-wrapper">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <input type="email" name="email_recuperar" placeholder="nombre@empresa.com" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" style="margin-top: 10px;">Enviar enlace de recuperación</button>
                </form>

                <div class="auth-switch">
                    <button onclick="showLogin()">Volver a Iniciar sesión</button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // 1. Alternar Formularios y Limpiar Datos
    function toggleForm() {
        // Cambiar la vista
        document.getElementById('form-login').classList.toggle('hidden-form');
        document.getElementById('form-register').classList.toggle('hidden-form');
        
        // Limpiar los textos de ambos formularios
        document.getElementById('loginForm').reset();
        document.getElementById('registroForm').reset();
        
        // Reiniciar los colores verdes de la contraseña
        document.querySelectorAll('.pass-reqs li').forEach(li => li.className = '');
        
        // Bloquear el botón de registro nuevamente
        const btnRegistrar = document.getElementById('btn-registrar');
        if (btnRegistrar) {
            btnRegistrar.disabled = true;
            btnRegistrar.style.opacity = '0.5';
        }
        
        // Ocultar mensaje de error de contraseñas
        const errorMatch = document.getElementById('error-match');
        if (errorMatch) errorMatch.style.display = 'none';
    }

    // 2. Mostrar/Ocultar Contraseña MEJORADO (El Ojo con cambio de icono)
        function togglePassword(inputId, buttonEl) {
            const input = document.getElementById(inputId);
            if (!input || !buttonEl) return;

            if (input.type === "password") {
                input.type = "text";
                buttonEl.classList.add('toggled');
            } else {
                input.type = "password";
                buttonEl.classList.remove('toggled');
            }
        }

    // 3. Validación de Contraseña en Tiempo Real
    const passInput = document.getElementById('reg-password');
    const confirmInput = document.getElementById('reg-password-confirm');
    const btnRegistrar = document.getElementById('btn-registrar');
    const errorMatch = document.getElementById('error-match');

    // Requisitos
    const reqLen = document.getElementById('req-len');
    const reqUp = document.getElementById('req-up');
    const reqNum = document.getElementById('req-num');
    const reqSp = document.getElementById('req-sp');

    let isValidPassword = false;

    passInput.addEventListener('input', function() {
        const val = this.value;
        
        // Validar Longitud
        const hasLen = val.length >= 8;
        reqLen.className = hasLen ? 'valid' : '';

        // Validar Mayúscula
        const hasUp = /[A-Z]/.test(val);
        reqUp.className = hasUp ? 'valid' : '';

        // Validar Número
        const hasNum = /[0-9]/.test(val);
        reqNum.className = hasNum ? 'valid' : '';

        // Validar Especial
        const hasSp = /[!@#$%^&*(),.?":{}|<>]/.test(val);
        reqSp.className = hasSp ? 'valid' : '';

        isValidPassword = hasLen && hasUp && hasNum && hasSp;
        checkFormReady();
    });

    confirmInput.addEventListener('input', checkFormReady);

    function checkFormReady() {
        const match = passInput.value === confirmInput.value && confirmInput.value !== '';
        
        if (confirmInput.value !== '' && !match) {
            errorMatch.style.display = 'block';
        } else {
            errorMatch.style.display = 'none';
        }

        // Solo activa el botón si la contraseña es segura Y coinciden
        if (isValidPassword && match) {
            btnRegistrar.disabled = false;
            btnRegistrar.style.opacity = '1';
        } else {
            btnRegistrar.disabled = true;
            btnRegistrar.style.opacity = '0.5';
        }
    }
    // Mostrar formulario de recuperar contraseña
    function showForgot() {
        document.getElementById('form-login').classList.add('hidden-form');
        document.getElementById('form-register').classList.add('hidden-form');
        document.getElementById('form-forgot').classList.remove('hidden-form');
    }

    // Volver al login
    function showLogin() {
        document.getElementById('form-forgot').classList.add('hidden-form');
        document.getElementById('form-register').classList.add('hidden-form');
        document.getElementById('form-login').classList.remove('hidden-form');
    }
</script>
</body>
</html>