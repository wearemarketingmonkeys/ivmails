<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

if (empty(trim($_POST['fullName']))) {
    exit(0);
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.sendgrid.net';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'apikey';
    $mail->Password   = getenv('SENDGRID_API_KEY');
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('no-reply@ivhub.com', 'IVHUB Consent');
    $mail->addAddress(trim($_POST['email']));
    $mail->addBCC('dipesh.macair@gmail.com');
    // $mail->addReplyTo('hello@ivhub.com', 'IVHUB');
    // $mail->addBCC('hello@ivhub.com');
    // $mail->addBCC('riti@ivhub.com');
    // $mail->addBCC('desk@ivhub.com');

    $mail->isHTML(true);
    $mail->Subject = "Liposculpt Consent Form Submission | " . $_POST['fullName'];

    $body = "<html><body>";
    $body .= "<h2>Liposculpt Consent Submission</h2>";

    $fields = [
        "Full Name" => "fullName",
        "Date of Birth" => "dob",
        "Contact Number" => "contact",
        "Email Address" => "email",
        "Emergency Contact" => "emergencyContact",
        "Date of Procedure" => "procedureDate",
        "Treating Practitioner" => "practitioner",
        "Patient Name (e-signature)" => "patientName",
        "Date of Consent" => "consentDate"
    ];

    foreach ($fields as $label => $field) {
        if (!empty($_POST[$field])) {
            $value = htmlspecialchars($_POST[$field]);
            $body .= "<p><strong>$label:</strong> $value</p>";
        }
    }

    $checkboxGroups = [
        "Procedure Understanding" => ["understanding1", "understanding2", "understanding3"],
        "Risks & Side Effects" => "risks",
        "Aftercare" => "aftercare",
        "Final Declaration" => "declaration"
    ];

    foreach ($checkboxGroups as $groupLabel => $keys) {
        $body .= "<h3>$groupLabel</h3>";
        if (is_array($keys)) {
            foreach ($keys as $key) {
                if (!empty($_POST[$key])) {
                    $body .= "<p> " . htmlspecialchars($key) . "</p>";
                }
            }
        } else if (!empty($_POST[$keys]) && is_array($_POST[$keys])) {
            foreach ($_POST[$keys] as $val) {
                $body .= "<p> " . htmlspecialchars($val) . "</p>";
            }
        }
    }

    $body .= "<h3>Medical History</h3>";
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'condition-') === 0) {
            $condition = str_replace('condition-', '', $key);
            $body .= "<p><strong>$condition:</strong> " . htmlspecialchars($value) . "</p>";
        }
    }

    $textAreas = [
        "Explanation if conditions marked Yes" => "historyExplanation",
        "Medications and Supplements" => "medications",
        "Allergies" => "allergies"
    ];

    foreach ($textAreas as $label => $key) {
        if (!empty($_POST[$key])) {
            $body .= "<p><strong>$label:</strong> " . nl2br(htmlspecialchars($_POST[$key])) . "</p>";
        }
    }

    $booleans = [
        "Confirmed not pregnant or breastfeeding" => "altConsent",
        "Disclosed all relevant history" => "allDisclosed",
        "Understands this is elective" => "electiveConfirm",
        "Consents to medical photographs" => "allowPhoto",
        "Understands data protection terms" => "dataConsent"
    ];

    foreach ($booleans as $label => $key) {
        if (!empty($_POST[$key]) && $_POST[$key] === "true") {
            $body .= "<p>☑ $label</p>";
        }
    }

    if (!empty($_POST['allowMarketing'])) {
        $body .= "<p><strong>Marketing Photo Consent:</strong> " . htmlspecialchars($_POST['allowMarketing']) . "</p>";
    }

    $body .= "<h3>DISCLAIMER OF LIABILITY</h3>";
    $body .= "<p>I understand and agree that IV Wellness Lounge Clinic LLC, its medical practitioners, and associated staff shall not be held financially liable for:</p>
    <ul>
    <li>Any unsatisfactory or suboptimal result that may occur despite appropriate technique and materials used</li>
    <li>Any individual allergic or hypersensitive reaction, delayed response, or side effect that could not have been reasonably predicted or tested prior to treatment</li>
    <li>Any incompatibility or unsuitability of my skin or physiology for this treatment, including where the treatment fails to produce the expected or desired effect</li>
    <li>The need for further corrective procedures or medical management, which may incur additional cost</li>
    </ul>
    <p>☑ I have read and understood this disclaimer, and I agree to its terms without reservation.</p>";

    $body .= "<hr><p><strong>Submitted via Liposculpt Consent Form on IVHUB</strong></p>";
    $body .= "</body></html>";

    $mail->Body = $body;

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