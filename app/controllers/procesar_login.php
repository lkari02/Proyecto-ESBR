<?php
// procesar_login.php
session_start();

// --- FUNCIÓN PARA ALERTAS BONITAS ---
function mostrarAlerta($titulo, $mensaje, $icono, $redireccion) {
    echo "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Aviso</title>
        <link href='https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600&family=Barlow:wght@400;500&display=swap' rel='stylesheet'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <style>body { background-color: #f4f6f9; font-family: 'Barlow', sans-serif; }</style>
    </head>
    <body>
        <script>
            Swal.fire({
                title: '$titulo',
                text: '$mensaje',
                icon: '$icono',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Entendido'
            }).then(() => {
                if ('$redireccion' === 'back') {
                    window.history.back();
                } else {
                    window.location.href = '$redireccion';
                }
            });
        </script>
    </body>
    </html>
    ";
    exit;
}
// ------------------------------------

$host = '127.0.0.1'; $db = 'bombaparts'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { 
    die("Error de conexión"); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, nombre, password, estado, rol FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password'])) {
        
        if ($usuario['estado'] === 'pendiente') {
            mostrarAlerta('Cuenta en Revisión', 'Tu cuenta aún está en revisión. Espera a que el administrador la apruebe.', 'info', '/Proyecto/public/admin/login.php');
        } elseif ($usuario['estado'] === 'denegado') {
            mostrarAlerta('Acceso Denegado', 'El acceso ha sido denegado para esta cuenta.', 'error', '/Proyecto/public/admin/login.php');
        }

        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_nombre'] = $usuario['nombre'];
        $_SESSION['user_rol'] = $usuario['rol'];

        if ($usuario['rol'] === 'admin') {
            $detalle = "Inicio de sesión: " . $usuario['nombre'];
            $sql_log = "INSERT INTO historial_actividades (accion, modulo, detalle) VALUES ('APROBADA', 'Sistema', ?)";
            $stmt_log = $pdo->prepare($sql_log);
            $stmt_log->execute([$detalle]);
        }

        header("Location: /Proyecto/public/admin/Dashboard.php");
        exit;

    } else {
        mostrarAlerta('Error de Acceso', 'Correo electrónico o contraseña incorrectos.', 'error', 'back');
    }
}
?>