<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.sendgrid.net';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'apikey'; // literal 'apikey'
    $mail->Password   = getenv('SENDGRID_API_KEY'); // replace this with actual key
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('noreply@ivhub.com', 'IVHUB Test');
    $mail->addAddress('dipesh.macair@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'Test Email from IVHUB using SendGrid';
    $mail->Body    = '<h2>This is a test email</h2><p>Sent via SendGrid SMTP + PHPMailer on Render</p>';

    $mail->send();
    echo json_encode(["message" => "Email sent successfully via SendGrid!"]);
} catch (Exception $e) {
    echo json_encode(["message" => "Email failed: {$mail->ErrorInfo}"]);
}
?>
