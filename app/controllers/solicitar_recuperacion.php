<?php
// solicitar_recuperacion.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/Exception.php';
require '../../PHPMailer/PHPMailer.php';
require '../../PHPMailer/SMTP.php';

function mostrarAlerta($titulo, $mensaje, $icono) {
    echo "<!DOCTYPE html><html lang='es'><head><title>Aviso</title><link href='https://fonts.googleapis.com/css2?family=Barlow:wght@400;500&display=swap' rel='stylesheet'><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script><style>body{background:#f4f6f9;font-family:'Barlow',sans-serif;}</style></head><body><script>Swal.fire({title:'$titulo',text:'$mensaje',icon:'$icono',confirmButtonColor:'#2563eb',confirmButtonText:'Entendido'}).then(()=>{window.location.href='/Proyecto/public/admin/login.php';});</script></body></html>";
    exit;
}

$host = '127.0.0.1'; $db = 'bombaparts'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
} catch (PDOException $e) { die("Error de conexión"); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email_recuperar']);

    // Buscar usuario
    $stmt = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Generar un token único y seguro, y fecha de expiración (+1 hora)
        $token = bin2hex(random_bytes(32));
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Guardar token en la BD
        $stmt_token = $pdo->prepare("UPDATE usuarios SET reset_token = ?, token_expiracion = ? WHERE id = ?");
        $stmt_token->execute([$token, $expira, $usuario['id']]);

        // Enviar Correo
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'benskywalker2001@gmail.com'; // CAMBIA ESTO
            $mail->Password = 'ypwh orad hrka wfxp'; // CAMBIA ESTO
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom('benskywalker2001@gmail.com', 'Seguridad BombaParts');
            $mail->addAddress($email, $usuario['nombre']);

            // Aquí creamos el enlace que el usuario va a clickear
            $url_reset = "http://localhost/proyecto/public/admin/restablecer.php?token=$token";

            $mail->isHTML(true);
            $mail->Subject = "Recupera tu contrasena - BombaParts";
            $mail->Body = "
                <h3>Hola {$usuario['nombre']},</h3>
                <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
                <p>Haz clic en el siguiente enlace para crear una nueva (este enlace caduca en 1 hora):</p>
                <br>
                <a href='$url_reset' style='padding:10px 15px; background-color:#2563eb; color:white; text-decoration:none; border-radius:5px;'>Restablecer mi Contraseña</a>
                <br><br>
                <p style='font-size: 12px; color: #5f7894;'>Si no solicitaste esto, puedes ignorar este correo.</p>
            ";

            $mail->send();
            mostrarAlerta('Correo Enviado', 'Si el correo existe en nuestro sistema, hemos enviado un enlace de recuperación. Revisa tu bandeja de entrada.', 'success');

        } catch (Exception $e) {
            mostrarAlerta('Error del Sistema', 'No se pudo enviar el correo de recuperación.', 'error');
        }
    } else {
        // Por seguridad, si el correo NO existe, damos EL MISMO MENSAJE (Evita que hackers adivinen correos)
        mostrarAlerta('Correo Enviado', 'Si el correo existe en nuestro sistema, hemos enviado un enlace de recuperación. Revisa tu bandeja de entrada.', 'success');
    }
}
?>