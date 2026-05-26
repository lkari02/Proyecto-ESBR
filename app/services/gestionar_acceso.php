<?php
// gestionar_acceso.php

// 1. IMPORTAR PHPMAILER (Para avisarle al usuario su resultado)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/Exception.php';
require '../../PHPMailer/PHPMailer.php';
require '../../PHPMailer/SMTP.php';

// --- FUNCIÓN PARA ALERTAS BONITAS ---
function mostrarAlerta($titulo, $mensaje, $icono) {
    echo "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Gestión de Acceso</title>
        <link href='https://fonts.googleapis.com/css2?family=Barlow:wght@400;500&display=swap' rel='stylesheet'>
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
                confirmButtonText: 'Ir al Panel'
            }).then(() => {
                window.location.href = '/Proyecto/public/admin/login.php';
            });
        </script>
    </body>
    </html>
    ";
    exit;
}

// 2. CONEXIÓN A LA BD
$host = '127.0.0.1'; $db = 'bombaparts'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
} catch (PDOException $e) { die("Error de conexión"); }

// 3. CAPTURAR DATOS DE LA URL
if (!isset($_GET['id']) || !isset($_GET['accion'])) {
    mostrarAlerta('Error', 'Faltan parámetros para procesar la solicitud.', 'error');
}

$id_usuario = (int)$_GET['id'];
$accion = $_GET['accion']; // 'aprobar' o 'denegar'
$nuevo_estado = ($accion === 'aprobar') ? 'activo' : 'denegado';

// 4. OBTENER DATOS DEL USUARIO ANTES DE ACTUALIZAR
$stmt = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    mostrarAlerta('No encontrado', 'El usuario solicitado no existe.', 'warning');
}

// 5. ACTUALIZAR ESTADO EN LA BASE DE DATOS
$sql_update = "UPDATE usuarios SET estado = ? WHERE id = ?";
$stmt_update = $pdo->prepare($sql_update);

if ($stmt_update->execute([$nuevo_estado, $id_usuario])) {
    
    // ======================================================
    // 6. NOTIFICAR AL USUARIO POR CORREO (PHPMAILER)
    // ======================================================
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // ---> USA TUS MISMAS CREDENCIALES QUE EN REGISTRO <---
        $mail->Username   = 'tu_correo_real@gmail.com'; 
        $mail->Password   = 'tu_contraseña_app_16_letras'; 
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('tu_correo_real@gmail.com', 'Sistema BombaParts');
        $mail->addAddress($usuario['email'], $usuario['nombre']);

        $mail->isHTML(true);
        if ($accion === 'aprobar') {
            $mail->Subject = "¡Cuenta Activada! - BombaParts";
            $mail->Body    = "<h3>Hola {$usuario['nombre']},</h3>
                              <p>Tu solicitud de acceso ha sido <b>APROBADA</b>.</p>
                              <p>Ya puedes entrar al sistema con tu correo y contraseña.</p>
                              <br><a href='http://localhost/tu_proyecto/login.php' style='padding:10px; background:#2563eb; color:white; text-decoration:none; border-radius:5px;'>Iniciar Sesión</a>";
        } else {
            $mail->Subject = "Estado de tu solicitud - BombaParts";
            $mail->Body    = "<h3>Hola {$usuario['nombre']},</h3>
                              <p>Lamentamos informarte que tu solicitud de acceso ha sido <b>DENEGADA</b> por el administrador.</p>";
        }

        $mail->send();
    } catch (Exception $e) {
        // Si falla el correo de aviso, igual mostramos el éxito de la BD
    }

    // Mostrar mensaje final al Admin
    $msg_admin = ($accion === 'aprobar') ? "El usuario ha sido activado." : "El acceso ha sido denegado.";
    mostrarAlerta('¡Hecho!', $msg_admin, 'success');

} else {
    mostrarAlerta('Error', 'No se pudo actualizar el estado en la base de datos.', 'error');
}