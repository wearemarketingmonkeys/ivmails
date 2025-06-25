<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// CORS Origin Whitelist
$allowed_origins = [
    "https://ivhubnew.onrender.com",
    "https://ivhub.com",
    "https://www.ivhub.com"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if(empty(trim($_POST['fullName']))){
    exit(0);
}

$mail = new PHPMailer(true);

try {
    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.sendgrid.net';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'apikey'; // literal string
    $mail->Password   = getenv('SENDGRID_API_KEY');
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('no-reply@ivhub.com', 'IVHUB Consent');
    $mail->addAddress('hello@ivhub.com');
    $mail->addAddress('riti@ivhub.com');
    $mail->addAddress('desk@ivhub.com');

    $mail->isHTML(true);
    $mail->Subject = "Laser Hair Removal Consent Form Submission | " . $_POST['fullName'];

    // Build HTML Body
    $body = "<html><body>";
    $body .= "<h2>Patient Information</h2>";
    $body .= "<p><strong>Full Name:</strong> " . $_POST['fullName'] . "</p>";
    $body .= "<p><strong>Emirates ID / Passport:</strong> " . $_POST['emiratesId'] . "</p>";
    $body .= "<p><strong>Date of Birth:</strong> " . $_POST['dob'] . "</p>";
    $body .= "<p><strong>Gender:</strong> " . $_POST['gender'] . "</p>";
    $body .= "<p><strong>Contact Number:</strong> " . $_POST['contact'] . "</p>";
    $body .= "<p><strong>Email Address:</strong> " . $_POST['email'] . "</p>";
    $body .= "<p><strong>Area:</strong> " . $_POST['area'] . "</p>";
    $body .= "<p><strong>Skin Type (Fitzpatrick):</strong> " . $_POST['skinType'] . "</p>";

    $body .= "<h2>Medical History & Safety Screening</h2>";
    if (!empty($_POST['medicalConditions'])) {
        foreach ($_POST['medicalConditions'] as $condition) {
            $body .= "<p>☑ " . htmlspecialchars($condition) . "</p>";
        }
    }
    $body .= "<p><strong>Allergies or Sensitivities:</strong> " . nl2br(htmlspecialchars($_POST['allergyDetails'])) . "</p>";

    $body .= "<h2>Consent Acknowledgment</h2>";
    $body .= "<p><strong>Patient Name:</strong> " . $_POST['patientName'] . "</p>";
    $body .= "<p><strong>Date of Consent:</strong> " . $_POST['consentDate'] . "</p>";
    $body .= "<p><strong>Practitioner Name:</strong> " . $_POST['practitionerName'] . "</p>";
    $body .= "<hr><p><strong>Submitted via Laser Hair Removal Consent Form on IVHUB</strong></p>";
    $body .= "</body></html>";

    $mail->Body = $body;

    // Attachment: Patient Signature
    if (isset($_FILES['patientSignature']) && $_FILES['patientSignature']['error'] === UPLOAD_ERR_OK) {
        $mail->addAttachment(
            $_FILES['patientSignature']['tmp_name'],
            $_FILES['patientSignature']['name']
        );
    }

    $mail->send();
    http_response_code(200);
    echo json_encode(["message" => "Email sent!"]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Email failed: {$mail->ErrorInfo}"]);
}
?>