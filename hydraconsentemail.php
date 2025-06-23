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

$mail = new PHPMailer(true);

try {
    // SMTP setup
    $mail->isSMTP();
    $mail->Host       = 'smtp.sendgrid.net';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'apikey'; // Always this literal string
    $mail->Password   = getenv('SENDGRID_API_KEY');
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('no-reply@ivhub.com', 'IVHUB Consent');
    $mail->addAddress('hello@ivhub.com');
    $mail->addAddress('riti@ivhub.com');
    $mail->addAddress('desk@ivhub.com');
    $mail->addAddress('dipesh.macair@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = "HydraFacial Consent Form Submission | " . $_POST['fullName'];

    // Build the email body
    $body = "<html><body>";
    $body .= "<h2>HydraFacial Consent Form Submission</h2>";

    $fields = [
        "Full Name" => "fullName",
        "Emirates ID / Passport" => "emiratesId",
        "Date of Birth" => "dob",
        "Gender" => "gender",
        "Contact Number" => "contact",
        "Email Address" => "email",
        "Allergies / Medical Conditions" => "allergiesDescription",
        "Photography Consent" => "photographyConsent",
        "Patient Name" => "patientName",
        "Consent Date" => "consentDate",
        "Practitioner Name" => "practitionerName"
    ];

    foreach ($fields as $label => $key) {
        $value = isset($_POST[$key]) ? nl2br(htmlspecialchars($_POST[$key])) : 'Not provided';
        $body .= "<p><strong>$label:</strong> $value</p>";
    }

    // Medical Conditions List
    if (!empty($_POST['medicalConditions'])) {
        $body .= "<p><strong>Disclosed Medical Conditions:</strong></p><ul>";
        foreach ($_POST['medicalConditions'] as $item) {
            $body .= "<li>" . htmlspecialchars($item) . "</li>";
        }
        $body .= "</ul>";
    }

    $body .= "<hr><p><strong>Submission received via IVHUB HydraFacial form.</strong></p>";
    $body .= "</body></html>";

    $mail->Body = $body;

    // Attachment: Patient Signature
    if (isset($_FILES['patientSignature']) && $_FILES['patientSignature']['error'] === UPLOAD_ERR_OK) {
        $mail->addAttachment(
            $_FILES['patientSignature']['tmp_name'],
            $_FILES['patientSignature']['name']
        );
    }

    // Send
    $mail->send();
    http_response_code(200);
    echo json_encode(["message" => "HydraFacial form submitted successfully!"]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Submission failed: {$mail->ErrorInfo}"]);
}
?>
