<?php
// Recipient(s)
$to = "dipesh.macair@gmail.com";

// Email subject
$subject = "Test Email from IVHUB Render Server";

// Static HTML content for body
$body = "<h2>This is a test email</h2>";
$body .= "<p>Testing PHP mail functionality on Render server.</p>";

// Email headers and MIME boundary
$boundary = md5(time());
$headers = "From: IVHUB Test <no-reply@ivhub.com>\r\n";
$headers .= "Reply-To: no-reply@ivhub.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

// MIME message
$message = "--$boundary\r\n";
$message .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$message .= $body . "\r\n";
$message .= "--$boundary--";

// Send the email and respond
if (mail($to, $subject, $message, $headers)) {
    http_response_code(200);
    echo json_encode(["message" => "✅ Test email sent successfully!"]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "❌ Failed to send test email."]);
}
