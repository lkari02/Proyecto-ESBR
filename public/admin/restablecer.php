<?php
// restablecer.php
session_start();

$host = '127.0.0.1'; $db = 'bombaparts'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Error de conexión"); }

// --- FUNCIÓN PARA ALERTAS BONITAS ---
function mostrarAlerta($titulo, $mensaje, $icono, $redireccion) {
    echo "<!DOCTYPE html><html lang='es'><head><title>Aviso</title><link href='https://fonts.googleapis.com/css2?family=Barlow:wght@400;500&display=swap' rel='stylesheet'><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script><style>body{background:#f4f6f9;font-family:'Barlow',sans-serif;}</style></head><body><script>Swal.fire({title:'$titulo',text:'$mensaje',icon:'$icono',confirmButtonColor:'#2563eb',confirmButtonText:'Entendido'}).then(()=>{window.location.href='$redireccion';});</script></body></html>";
    exit;
}

// 1. VALIDAR EL TOKEN DE LA URL
if (!isset($_GET['token'])) {
    mostrarAlerta('Acceso Inválido', 'No se proporcionó un token de recuperación.', 'error', 'login.php');
}

$token = $_GET['token'];

// Buscar si el token existe y si no ha expirado
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND token_expiracion > NOW()");
$stmt->execute([$token]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    mostrarAlerta('Enlace Expirado', 'El enlace de recuperación es inválido o ha caducado (recuerda que solo dura 1 hora).', 'warning', 'login.php');
}

// 2. PROCESAR EL CAMBIO DE CONTRASEÑA
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nueva_pass = $_POST['password'];
    
    // Encriptar la nueva contraseña
    $pass_hashed = password_hash($nueva_pass, PASSWORD_DEFAULT);

    // Actualizar la contraseña y BORRAR el token para que no se pueda usar de nuevo
    $sql_update = "UPDATE usuarios SET password = ?, reset_token = NULL, token_expiracion = NULL WHERE id = ?";
    $stmt_up = $pdo->prepare($sql_update);
    
    if ($stmt_up->execute([$pass_hashed, $usuario['id']])) {
        mostrarAlerta('¡Actualizado!', 'Tu contraseña ha sido cambiada con éxito. Ya puedes iniciar sesión.', 'success', 'login.php');
    } else {
        mostrarAlerta('Error', 'No se pudo actualizar la contraseña.', 'error', 'login.php');
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - BombaParts</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css"> </head>
<body style="background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0;">

    <div class="auth-container" style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 400px;">
        <div class="auth-header">
            <h2>Nueva Contraseña</h2>
            <p>Establece tu nueva credencial de acceso para BombaParts.</p>
        </div>

        <form id="resetForm" method="POST">
            <div class="input-group">
                <label>Nueva Contraseña</label>
                <div class="input-wrapper">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <input type="password" name="password" id="reg-password" placeholder="Mínimo 8 caracteres" required>
                    <button type="button" class="btn-eye" onclick="togglePassword('reg-password', this)">
                        <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </button>
                </div>
            </div>

            <ul class="pass-reqs">
                <li id="req-len">Mínimo 8 caracteres</li>
                <li id="req-up">Al menos una mayúscula</li>
                <li id="req-num">Al menos un número</li>
            </ul>

            <div class="input-group">
                <label>Confirmar Contraseña</label>
                <div class="input-wrapper">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <input type="password" id="reg-password-confirm" placeholder="Repite la contraseña" required>
                    <button type="button" class="btn-eye" onclick="togglePassword('reg-password-confirm', this)">
                        <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </button>
                </div>
                <span id="error-match" style="color: #dc2626; font-size: 12px; display: none; margin-top: 5px;">Las contraseñas no coinciden</span>
            </div>

            <button type="submit" id="btn-save" class="btn-primary" style="margin-top: 20px;" disabled>Guardar nueva contraseña</button>
        </form>
    </div>

    <script>
        // Lógica del ojo
        function togglePassword(inputId, buttonEl) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                buttonEl.classList.add('toggled');
            } else {
                input.type = "password";
                buttonEl.classList.remove('toggled');
            }
        }

        // Validación en tiempo real
        const passInput = document.getElementById('reg-password');
        const confirmInput = document.getElementById('reg-password-confirm');
        const btnSave = document.getElementById('btn-save');
        
        passInput.addEventListener('input', function() {
            const val = this.value;
            document.getElementById('req-len').className = val.length >= 8 ? 'valid' : '';
            document.getElementById('req-up').className = /[A-Z]/.test(val) ? 'valid' : '';
            document.getElementById('req-num').className = /[0-9]/.test(val) ? 'valid' : '';
            validateForm();
        });

        confirmInput.addEventListener('input', validateForm);

        function validateForm() {
            const match = passInput.value === confirmInput.value && confirmInput.value !== '';
            const secure = passInput.value.length >= 8 && /[A-Z]/.test(passInput.value) && /[0-9]/.test(passInput.value);
            
            document.getElementById('error-match').style.display = (confirmInput.value !== '' && !match) ? 'block' : 'none';
            
            btnSave.disabled = !(match && secure);
            btnSave.style.opacity = (match && secure) ? '1' : '0.5';
        }
    </script>
</body>
</html>