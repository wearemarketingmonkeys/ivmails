<?php
$to = "riti@ivhub.com,desk@ivhub.com";
$subject = "Laser Hair Removal Consent Form Submission";

// Build the HTML body
$body = "<h2>Patient Information</h2>";
$body .= "<p>Test Submission</p>";

// Email headers
$boundary = md5(time());
$headers = "From: IVHUB Consent <no-reply@ivhub.com>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

// Email body start
$message = "--$boundary\r\n";
$message .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$message .= $body . "\r\n";



$message .= "--$boundary--";

// Send the email
if (mail($to, $subject, $message, $headers)) {
    http_response_code(200);
    echo '{"message":"email sent!"}';
} else {
    http_response_code(500);
    echo '{"message":"email not sent!"}';
}
?>
