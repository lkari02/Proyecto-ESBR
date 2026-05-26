<?php
// Importar las clases de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Si usaste Composer, descomenta la siguiente línea:
// require 'vendor/autoload.php';

// Si descargaste la carpeta manualmente, usa estas rutas (verifica que coincidan con tu estructura):
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// Verificar que la solicitud venga por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitizar y recoger los datos del formulario
    $nombre = htmlspecialchars(strip_tags(trim($_POST['name'])));
    $correo = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $asunto = htmlspecialchars(strip_tags(trim($_POST['subject'])));
    $mensaje = htmlspecialchars(strip_tags(trim($_POST['message'])));

    // Validación básica de campos vacíos o correo inválido
    if (empty($nombre) || empty($correo) || empty($mensaje) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Por favor, complete todos los campos obligatorios correctamente.'); window.history.back();</script>";
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Sustituye por tu servidor SMTP (ej. smtp.gmail.com)
        $mail->SMTPAuth   = true;
        $mail->Username   = 'benskywalker2001@gmail.com'; // Tu dirección de correo SMTP
        $mail->Password   = 'ypwh orad hrka wfxp'; // Tu contraseña o contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Usa ENCRYPTION_STARTTLS si el puerto es 587
        $mail->Port       = 465; // 465 para SSL, 587 para TLS
        $mail->CharSet    = 'UTF-8';

        // Configuración de Remitente y Destinatario
        // El correo sale desde tu cuenta SMTP, pero le pones el nombre de la página
        $mail->setFrom('benskywalker2001@gmail.com', 'Equipos de Bombeo Web');
        
        // El correo donde quieres recibir los mensajes de tus clientes
        $mail->addAddress('abelabdielcoronafranco2000@gmail.com'); 
        
        // Agregamos el correo del cliente en "Responder A" para contestarle directamente
        $mail->addReplyTo($correo, $nombre);

        // Contenido del Correo
        $mail->isHTML(true);
        $mail->Subject = 'Nuevo mensaje de contacto: ' . ($asunto ? $asunto : 'Sin asunto');
        
        // Cuerpo del mensaje usando HTML básico
        $cuerpo = "
        <div style='font-family: Arial, sans-serif; color: #333;'>
            <h2 style='color: #002d5f;'>Nuevo mensaje de contacto web</h2>
            <p><strong>Nombre:</strong> {$nombre}</p>
            <p><strong>Correo Electrónico:</strong> {$correo}</p>
            <p><strong>Asunto:</strong> {$asunto}</p>
            <hr>
            <p><strong>Mensaje:</strong></p>
            <p>" . nl2br($mensaje) . "</p>
        </div>
        ";

        $mail->Body    = $cuerpo;
        $mail->AltBody = strip_tags($cuerpo); // Versión de texto plano por si falla el HTML

        // Enviar el correo
        $mail->send();
        
        // Alerta de éxito y redirección usando JavaScript simple
        echo "<script>
                alert('¡Mensaje enviado con éxito! Nos pondremos en contacto pronto.');
                window.location.href = 'Contacto.html';
              </script>";
              
    } catch (Exception $e) {
        // Alerta de error si falla la configuración SMTP
        echo "<script>
                alert('Hubo un error al enviar el mensaje. Intente más tarde. Mailer Error: {$mail->ErrorInfo}');
                window.history.back();
              </script>";
    }
} else {
    // Si alguien intenta acceder directamente al PHP, regresarlo al formulario
    header("Location: Contacto.html");
    exit;
}
?>

