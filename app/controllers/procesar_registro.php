<?php
// procesar_registro.php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/Exception.php';
require '../../PHPMailer/PHPMailer.php';
require '../../PHPMailer/SMTP.php';

// --- FUNCIÓN PARA ALERTAS BONITAS ---
function mostrarAlerta($titulo, $mensaje, $icono, $redireccion) {
    echo "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
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
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        mostrarAlerta('Atención', 'Este correo ya está registrado en el sistema.', 'warning', 'back');
    }

    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nombre, email, password, estado) VALUES (?, ?, ?, 'pendiente')";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$nombre, $email, $password_hashed])) {
        $nuevo_id = $pdo->lastInsertId();

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();                                            
            $mail->Host       = 'smtp.gmail.com';                     
            $mail->SMTPAuth   = true;                                   
            $mail->Username   = 'benskywalker2001@gmail.com';                     
            $mail->Password   = 'ypwh orad hrka wfxp'; // Cambia por tu contraseña de app
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
            $mail->Port       = 465;                                    

            $mail->setFrom('benskywalker2001@gmail.com', 'Sistema BombaParts');
            $mail->addAddress('benskywalker2001@gmail.com', 'Admin BombaParts');

            $url_base = "http://localhost/proyecto/app/services/gestionar_acceso.php"; 
            $link_aprobar = "$url_base?id=$nuevo_id&accion=aprobar";
            $link_denegar = "$url_base?id=$nuevo_id&accion=denegar";

            $mail->isHTML(true);                                  
            $mail->Subject = "NUEVO REGISTRO PENDIENTE: $nombre";
            $mail->Body    = "
                <h3>Nuevo usuario registrado en BombaParts</h3>
                <p><strong>Nombre:</strong> $nombre</p>
                <p><strong>Correo:</strong> $email</p>
                <p>El usuario está esperando tu autorización para entrar al sistema.</p>
                <br>
                <a href='$link_aprobar' style='padding: 10px 15px; background-color: #16a34a; color: white; text-decoration: none; border-radius: 5px;'>APROBAR ACCESO</a>
                &nbsp;&nbsp;&nbsp;
                <a href='$link_denegar' style='padding: 10px 15px; background-color: #dc2626; color: white; text-decoration: none; border-radius: 5px;'>DENEGAR ACCESO</a>
            ";

            $mail->send();
            
            mostrarAlerta('¡Registro exitoso!', 'Tu cuenta está siendo revisada por el administrador. Recibirás un aviso cuando sea aprobada.', 'success', '/Proyecto/public/admin/login.php');

        } catch (Exception $e) {
            mostrarAlerta('Aviso de sistema', 'Registro exitoso en la base de datos, pero hubo un problema al enviar el correo al administrador.', 'warning', '/Proyecto/public/admin/login.php');
        }

    } else {
        mostrarAlerta('Error', 'Ocurrió un problema al guardar tus datos en la base de datos.', 'error', 'back');
    }
}
?>