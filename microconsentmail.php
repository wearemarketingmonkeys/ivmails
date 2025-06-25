<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// CORS whitelist
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
    // SMTP settings
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
    $mail->addAddress('desk@ivhub.com');
    $mail->addAddress('riti@ivhub.com');

    $mail->isHTML(true);
    $mail->Subject = "Microneedling Consent Form Submission | " . $_POST['fullName'];

    // Build HTML body
    $body = "<html><body>";
    $body .= "<h2>Microneedling Consent Submission</h2>";

    $fields = [
        "Full Name" => "fullName",
        "Emirates ID / Passport" => "emiratesId",
        "Date of Birth" => "dob",
        "Gender" => "gender",
        "Contact Number" => "contact",
        "Email Address" => "email",
        "Type of Microneedling" => "typeof",
        "Treatment Area(s)" => "treatmentArea",
        "Known Allergies / Sensitivities" => "allergyExplanation",
        "Patient Name" => "patientName",
        "Date of Consent" => "consentDate",
        "Practitioner Name" => "practitionerName"
    ];

    foreach ($fields as $label => $field) {
        if (!empty($_POST[$field])) {
            $value = htmlspecialchars($_POST[$field]);
            $body .= "<p><strong>$label:</strong> $value</p>";
        }
    }

    // Medical conditions list
    if (!empty($_POST['medicalConditions'])) {
        $conditions = is_array($_POST['medicalConditions']) ? $_POST['medicalConditions'] : [$_POST['medicalConditions']];
        $body .= "<p><strong>Medical Conditions:</strong><br>" . implode("<br>", array_map('htmlspecialchars', $conditions)) . "</p>";
    }

    $body .= "<hr><p><strong>Submitted via Microneedling Consent Form on IVHUB</strong></p>";
    $body .= "</body></html>";

    $mail->Body = $body;

    // Signature attachment
    if (isset($_FILES['patientSignature']) && $_FILES['patientSignature']['error'] === UPLOAD_ERR_OK) {
        $mail->addAttachment(
            $_FILES['patientSignature']['tmp_name'],
            $_FILES['patientSignature']['name']
        );
    }

    $mail->send();
    http_response_code(200);
    echo json_encode(["message" => "Form submitted successfully."]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Form submission failed: {$mail->ErrorInfo}"]);
}
?>