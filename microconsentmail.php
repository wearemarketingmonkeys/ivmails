<?php
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

$to = "hello@ivhub.com, desk@ivhub.com, riti@ivhub.com";
$subject = "Microneedling Consent Form Submission";

// Build HTML body
$body = "<h2>Microneedling Consent Submission</h2>";
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

// Loop through fields
foreach ($fields as $label => $field) {
    if (!empty($_POST[$field])) {
        $value = htmlspecialchars($_POST[$field]);
        $body .= "<p><strong>$label:</strong> $value</p>";
    }
}

// Medical conditions (checkbox array)
if (!empty($_POST['medicalConditions'])) {
    $conditions = is_array($_POST['medicalConditions']) ? $_POST['medicalConditions'] : [$_POST['medicalConditions']];
    $body .= "<p><strong>Medical Conditions:</strong><br>" . implode("<br>", array_map('htmlspecialchars', $conditions)) . "</p>";
}

// Email headers for HTML
$boundary = md5(time());
$headers = "From: IVHUB Consent <no-reply@ivhub.com>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

// Email message with HTML body
$message = "--$boundary\r\n";
$message .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$message .= $body . "\r\n";

// Handle signature attachment
if (isset($_FILES['patientSignature']) && $_FILES['patientSignature']['error'] === UPLOAD_ERR_OK) {
    $sigTmp = $_FILES['patientSignature']['tmp_name'];
    $sigName = $_FILES['patientSignature']['name'];
    $sigData = chunk_split(base64_encode(file_get_contents($sigTmp)));
    $sigMime = mime_content_type($sigTmp);

    $message .= "--$boundary\r\n";
    $message .= "Content-Type: $sigMime; name=\"$sigName\"\r\n";
    $message .= "Content-Disposition: attachment; filename=\"$sigName\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $message .= $sigData . "\r\n";
}

$message .= "--$boundary--";

// Send the email
if (mail($to, $subject, $message, $headers)) {
    http_response_code(200);
    echo json_encode(["message" => "Form submitted successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Form submission failed."]);
}
?>