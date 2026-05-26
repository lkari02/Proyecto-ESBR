
<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recoger y sanitizar datos
    $name    = strip_tags(trim($_POST["name"]));
    $email   = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST["subject"]));
    $message = htmlspecialchars(trim($_POST["message"]));

    // 2. Configuración del correo
    $recipient = "abelabdielcoronafranco2000@gmail.com.com"; // Tu correo real
    $email_subject = "Nuevo mensaje de Equipos de Bombeo: $subject";
    
    // 3. Construcción del cuerpo del mensaje
    $email_content = "Nombre: $name\n";
    $email_content .= "Email: $email\n\n";
    $email_content .= "Mensaje:\n$message\n";

    // 4. Cabeceras
    $headers = "From: BombaParts Web <noreply@equiposdebombeo.com>\r\n";
    $headers .= "Reply-To: $email\r\n";

    // 5. Envío
    if (mail($recipient, $email_subject, $email_content, $headers)) {
        echo json_encode(["status" => "success", "message" => "Enviado"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al enviar el correo."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido"]);
}
?>